<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();
requireUniversity($pdo);

$activeUni = getActiveUniversity($pdo);
$pageTitle = 'All Registrations';

// ---- Delete a single registration ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    $info = $pdo->prepare("SELECT registration_no, first_name, last_name FROM students WHERE id = ?");
    $info->execute([$delId]);
    $delStudent = $info->fetch();
    if ($delStudent) {
        deleteStudentRecord($pdo, $delId);
        logActivity($pdo, $_SESSION['user_id'], 'delete', "Deleted registration " . $delStudent['registration_no'] . " for " . $delStudent['first_name'] . ' ' . $delStudent['last_name']);
        flash('success', 'Registration ' . $delStudent['registration_no'] . ' deleted.');
    }
    redirect('admin_students.php');
}

// ---- Bulk approve / reject / delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action']) && !empty($_POST['ids'])) {
    $ids = array_map('intval', $_POST['ids']);

    if ($_POST['bulk_action'] === 'delete') {
        foreach ($ids as $sid) {
            $info = $pdo->prepare("SELECT registration_no FROM students WHERE id = ?");
            $info->execute([$sid]);
            $regNo = $info->fetchColumn();
            if ($regNo) {
                deleteStudentRecord($pdo, $sid);
                logActivity($pdo, $_SESSION['user_id'], 'delete', "Deleted registration $regNo (bulk action)");
            }
        }
        flash('success', count($ids) . ' registration(s) deleted.');
        redirect('admin_students.php');
    }

    $newStatus = $_POST['bulk_action'] === 'approve' ? 'approved' : 'rejected';
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $upd = $pdo->prepare("UPDATE students SET status = ? WHERE id IN ($placeholders)");
    $upd->execute(array_merge([$newStatus], $ids));

    foreach ($ids as $sid) {
        logActivity($pdo, $_SESSION['user_id'], $newStatus === 'approved' ? 'approve' : 'reject',
            "Bulk $newStatus action applied", $sid);
    }

    flash('success', count($ids) . " registration(s) marked as $newStatus.");
    redirect('admin_students.php');
}

// ---- Build filter conditions dynamically (always scoped to the active university) ----
$where  = ['s.university_id = ?'];
$params = [$activeUni['id']];

if (!empty($_GET['course_id']))     { $where[] = 's.course_id = ?'; $params[] = $_GET['course_id']; }
if (!empty($_GET['session_id']))    { $where[] = 's.session_id = ?'; $params[] = $_GET['session_id']; }
if (!empty($_GET['status']))        { $where[] = 's.status = ?'; $params[] = $_GET['status']; }
if (!empty($_GET['reg_type']))      { $where[] = 's.registration_type = ?'; $params[] = $_GET['reg_type']; }
if (!empty($_GET['staff_id']))      { $where[] = 's.created_by = ?'; $params[] = $_GET['staff_id']; }
if (!empty($_GET['gender']))        { $where[] = 's.gender = ?'; $params[] = $_GET['gender']; }
if (!empty($_GET['date_from']))     { $where[] = 'DATE(s.created_at) >= ?'; $params[] = $_GET['date_from']; }
if (!empty($_GET['date_to']))       { $where[] = 'DATE(s.created_at) <= ?'; $params[] = $_GET['date_to']; }
if (!empty($_GET['q'])) {
    $where[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.registration_no LIKE ? OR s.mobile LIKE ?)";
    $like = '%' . $_GET['q'] . '%';
    array_push($params, $like, $like, $like, $like);
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

$sql = "SELECT s.*, u.full_name as staff_name, pu.full_name as center_name, c.name as course_name, un.name as university_name,
               un.form_template, sy.year_label,
               (SELECT status FROM fees f WHERE f.student_id = s.id ORDER BY f.id DESC LIMIT 1) as fee_status
        FROM students s
        LEFT JOIN users u ON u.id = s.created_by
        LEFT JOIN users pu ON pu.id = u.parent_user_id
        LEFT JOIN courses c ON c.id = s.course_id
        LEFT JOIN universities un ON un.id = s.university_id
        LEFT JOIN sessions_years sy ON sy.id = s.session_id
        $whereSql
        ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

// ---- CSV export (uses the same filters) ----
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="registrations_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Reg No', 'Enrollment No', 'Type', 'Name', 'Mobile', 'Email', 'Course', 'University', 'Session', 'Staff', 'Status', 'Fee Status', 'Date']);
    foreach ($students as $s) {
        fputcsv($out, [
            $s['registration_no'], $s['enrollment_no'] ?? '', ucfirst($s['registration_type']), $s['first_name'] . ' ' . $s['last_name'], $s['mobile'], $s['email'],
            $s['course_name'], $s['university_name'], $s['year_label'], $s['staff_name'],
            $s['status'], $s['fee_status'] ?? 'not paid', $s['created_at']
        ]);
    }
    fclose($out);
    exit;
}

$courses      = $pdo->prepare("SELECT * FROM courses WHERE status='active' AND university_id = ? ORDER BY name");
$courses->execute([$activeUni['id']]);
$courses      = $courses->fetchAll();
$sessionsYrs  = $pdo->query("SELECT * FROM sessions_years WHERE status='active' ORDER BY year_label DESC")->fetchAll();
$staffList    = $pdo->query("SELECT u.id, u.full_name, pu.full_name as center_name
                              FROM users u LEFT JOIN users pu ON pu.id = u.parent_user_id
                              WHERE u.role='staff' ORDER BY pu.full_name IS NULL DESC, pu.full_name, u.full_name")->fetchAll();

require_once __DIR__ . '/includes/header.php';

// Build query string for export link (preserve current filters)
$exportQs = $_GET;
$exportQs['export'] = 'csv';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Admissions</span>
    <h4>All Registrations</h4>
  </div>
  <a href="?<?= http_build_query($exportQs) ?>" class="btn btn-gold btn-sm"><i class="fa-solid fa-file-arrow-down"></i> Export CSV</a>
</div>

<div class="alert alert-light border small mb-3 d-flex justify-content-between align-items-center">
  <span><i class="fa-solid fa-building-columns text-muted me-1"></i> Showing <strong><?= e($activeUni['name']) ?></strong> registrations</span>
  <a href="choose_university.php?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="small">Change university</a>
</div>

<form method="GET" class="table-card p-3 mb-3">
  <div class="row g-2">
    <div class="col-md-3">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name / reg no / mobile" value="<?= e($_GET['q'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <select name="course_id" class="form-select form-select-sm">
        <option value="">All Courses</option>
        <?php foreach ($courses as $c): ?>
          <option value="<?= $c['id'] ?>" <?= (($_GET['course_id'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="session_id" class="form-select form-select-sm">
        <option value="">All Sessions</option>
        <?php foreach ($sessionsYrs as $sy): ?>
          <option value="<?= $sy['id'] ?>" <?= (($_GET['session_id'] ?? '') == $sy['id']) ? 'selected' : '' ?>><?= e($sy['year_label']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="status" class="form-select form-select-sm">
        <option value="">All Status</option>
        <option value="submitted" <?= (($_GET['status'] ?? '') == 'submitted') ? 'selected' : '' ?>>Submitted</option>
        <option value="approved" <?= (($_GET['status'] ?? '') == 'approved') ? 'selected' : '' ?>>Approved</option>
        <option value="rejected" <?= (($_GET['status'] ?? '') == 'rejected') ? 'selected' : '' ?>>Rejected</option>
      </select>
    </div>
    <div class="col-md-1">
      <button class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-filter"></i></button>
    </div>
  </div>
  <div class="row g-2 mt-1">
    <div class="col-md-2">
      <select name="reg_type" class="form-select form-select-sm">
        <option value="">Fresh + Re-Reg</option>
        <option value="fresh" <?= (($_GET['reg_type'] ?? '') == 'fresh') ? 'selected' : '' ?>>Fresh Only</option>
        <option value="re-registration" <?= (($_GET['reg_type'] ?? '') == 're-registration') ? 'selected' : '' ?>>Re-Registration Only</option>
      </select>
    </div>
    <div class="col-md-2">
      <select name="staff_id" class="form-select form-select-sm">
        <option value="">All Centers/Sub-Centers</option>
        <?php foreach ($staffList as $st): ?>
          <option value="<?= $st['id'] ?>" <?= (($_GET['staff_id'] ?? '') == $st['id']) ? 'selected' : '' ?>>
            <?= $st['center_name'] ? '↳ ' . e($st['full_name']) . ' (' . e($st['center_name']) . ')' : e($st['full_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="gender" class="form-select form-select-sm">
        <option value="">All Genders</option>
        <option value="Male" <?= (($_GET['gender'] ?? '') == 'Male') ? 'selected' : '' ?>>Male</option>
        <option value="Female" <?= (($_GET['gender'] ?? '') == 'Female') ? 'selected' : '' ?>>Female</option>
        <option value="Other" <?= (($_GET['gender'] ?? '') == 'Other') ? 'selected' : '' ?>>Other</option>
      </select>
    </div>
    <div class="col-md-2">
      <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($_GET['date_from'] ?? '') ?>" title="From date">
    </div>
    <div class="col-md-2">
      <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($_GET['date_to'] ?? '') ?>" title="To date">
    </div>
    <div class="col-md-2">
      <a href="admin_students.php" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
    </div>
  </div>
</form>

<form method="POST" id="bulkForm">
  <div class="table-card p-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="text-muted small"><?= count($students) ?> record(s) found</span>

      <div id="bulkActionBar" class="d-none d-flex gap-2 align-items-center">
        <span class="small text-muted"><span id="bulkCount">0</span> selected</span>
        <button type="submit" name="bulk_action" value="approve" class="btn btn-sm btn-success">Approve Selected</button>
        <button type="submit" name="bulk_action" value="reject" class="btn btn-sm btn-danger">Reject Selected</button>
        <button type="submit" name="bulk_action" value="delete" class="btn btn-sm btn-outline-danger" onclick="return confirm('Permanently delete the selected registration(s)? This cannot be undone.');">Delete Selected</button>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-sm table-ledger align-middle">
        <thead>
          <tr>
            <th style="width:32px;"><input type="checkbox" id="selectAllRows"></th>
            <th>Reg No</th><th>Enrollment No</th><th>Type</th><th>Name</th><th>Mobile</th><th>Course</th>
            <th>Session</th><th>Staff</th><th>Status</th><th>Fee</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $s): ?>
          <tr>
            <td><input type="checkbox" class="row-check" name="ids[]" value="<?= $s['id'] ?>"></td>
            <td class="reg-no"><?= e($s['registration_no']) ?></td>
            <td class="reg-no"><?= $s['enrollment_no'] ? e($s['enrollment_no']) : '<span class="text-muted">—</span>' ?></td>
            <td><span class="badge bg-<?= $s['registration_type'] == 'fresh' ? 'primary' : 'info' ?>"><?= $s['registration_type'] == 'fresh' ? 'Fresh' : 'Re-Reg' ?></span></td>
            <td><?= e($s['first_name'] . ' ' . $s['last_name']) ?></td>
            <td><?= e($s['mobile']) ?></td>
            <td><?= e($s['course_name'] ?? '-') ?></td>
            <td><?= e($s['year_label'] ?? '-') ?></td>
            <td>
              <?= e($s['staff_name'] ?? '-') ?>
              <?php if ($s['center_name']): ?>
                <div class="text-muted" style="font-size:.7rem;">under <?= e($s['center_name']) ?></div>
              <?php endif; ?>
            </td>
            <td><?= statusBadge($s['status']) ?></td>
            <td><?= $s['fee_status'] ? statusBadge($s['fee_status']) : '<span class="badge bg-light text-dark border">Not Paid</span>' ?></td>
            <td>
              <a href="student_detail.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
              <a href="edit_student.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-pen"></i></a>
              <!-- <a href="print_slip.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fa-solid fa-print"></i></a> -->
              <?php if (in_array($s['form_template'] ?? '', ['sgvu', 'amity', 'mangalayatan', 'svsu'], true)): ?>
                <a href="print_pixel.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fa-solid fa-file-lines"></i></a>
              <?php endif; ?>
              <button type="submit" name="delete_id" value="<?= $s['id'] ?>" formnovalidate class="btn btn-sm btn-outline-danger" onclick="return confirm('Permanently delete this registration? This cannot be undone.');"><i class="fa-solid fa-trash"></i></button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$students): ?>
            <tr><td colspan="12" class="text-center text-muted py-4">No records match the selected filters.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
