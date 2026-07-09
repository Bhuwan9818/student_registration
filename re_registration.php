<?php
require_once __DIR__ . '/config/config.php';
requireLogin();
requireUniversity($pdo);

$activeUni = getActiveUniversity($pdo);
$pageTitle = 'Re-Registration';

$courses = $pdo->prepare("SELECT * FROM courses WHERE status='active' AND university_id = ? ORDER BY name");
$courses->execute([$activeUni['id']]);
$courses = $courses->fetchAll();
$sessionsYrs  = $pdo->query("SELECT * FROM sessions_years WHERE status='active' ORDER BY year_label DESC")->fetchAll();

// ---- Step: finalize re-registration ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reregister'])) {
    $oldId = (int)$_POST['old_student_id'];

    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$oldId]);
    $old = $stmt->fetch();

    if (!$old) {
        flash('error', 'Original student record not found.');
        redirect('re_registration.php');
    }

    $newSessionId = $_POST['session_id'];
    $newCourseId  = $_POST['course_id'] ?: $old['course_id'];
    $newSemesterNo = (int)$old['semester_no'] + 1;
    $regNo = generateRegistrationNo($pdo);

    $sql = "INSERT INTO students (
                registration_no, registration_type, parent_student_id, created_by,
                first_name, last_name, dob, gender, category, aadhar_no, photo_path,
                mobile, alt_mobile, email, address, city, state, pincode,
                father_name, mother_name, guardian_mobile,
                last_qualification, board_university, passing_year, percentage, marksheet_path,
                university_id, course_id, session_id, semester_no
            ) VALUES (?,'re-registration',?,?, ?,?,?,?,?,?,?, ?,?,?,?,?,?,?, ?,?,?, ?,?,?,?,?, ?,?,?,?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $regNo, $oldId, $_SESSION['user_id'],
        $old['first_name'], $old['last_name'], $old['dob'], $old['gender'], $old['category'], $old['aadhar_no'], $old['photo_path'],
        $old['mobile'], $old['alt_mobile'], $old['email'], $old['address'], $old['city'], $old['state'], $old['pincode'],
        $old['father_name'], $old['mother_name'], $old['guardian_mobile'],
        $old['last_qualification'], $old['board_university'], $old['passing_year'], $old['percentage'], $old['marksheet_path'],
        $activeUni['id'], $newCourseId, $newSessionId, $newSemesterNo
    ]);

    $newId = $pdo->lastInsertId();
    logActivity($pdo, $_SESSION['user_id'], 'registration',
        'Re-registration for ' . $old['first_name'] . ' ' . $old['last_name'], $newId);

    flash('success', "Re-registration submitted successfully! New Reg No: $regNo. You can now submit the fee.");
    redirect('submit_fee.php?student_id=' . $newId);
}

// ---- Step: search for the existing student ----
$results = [];
$searched = false;
if (!empty($_GET['q'])) {
    $searched = true;
    $like = '%' . $_GET['q'] . '%';
    $stmt = $pdo->prepare("SELECT s.*, c.name as course_name, sy.year_label
                            FROM students s
                            LEFT JOIN courses c ON c.id = s.course_id
                            LEFT JOIN sessions_years sy ON sy.id = s.session_id
                            WHERE (s.registration_no LIKE ? OR s.mobile LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)
                            AND s.status = 'approved' AND s.university_id = ?
                            ORDER BY s.created_at DESC LIMIT 20");
    $stmt->execute([$like, $like, $like, $like, $activeUni['id']]);
    $results = $stmt->fetchAll();
}

// ---- Step: show the confirm form for a chosen student ----
$selected = null;
if (!empty($_GET['student_id'])) {
    $stmt = $pdo->prepare("SELECT s.*, c.name as course_name, sy.year_label
                            FROM students s
                            LEFT JOIN courses c ON c.id = s.course_id
                            LEFT JOIN sessions_years sy ON sy.id = s.session_id
                            WHERE s.id = ?");
    $stmt->execute([$_GET['student_id']]);
    $selected = $stmt->fetch();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Admissions</span>
    <h4>Re-Registration</h4>
  </div>
</div>

<div class="alert alert-light border small mb-3 d-flex justify-content-between align-items-center">
  <span><i class="fa-solid fa-building-columns text-muted me-1"></i> Working within <strong><?= e($activeUni['name']) ?></strong></span>
  <a href="choose_university.php?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="small">Change university</a>
</div>

<?php if (!$selected): ?>

  <div class="table-card p-4 mb-3">
    <p class="text-muted small mb-3">Search for an existing <strong>approved</strong> student in <?= e($activeUni['name']) ?> by registration number, mobile number, or name to register them for a new session.</p>
    <form method="GET" class="d-flex gap-2">
      <input type="text" name="q" class="form-control" placeholder="Registration No / Mobile / Name" value="<?= e($_GET['q'] ?? '') ?>" required>
      <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    </form>
  </div>

  <?php if ($searched): ?>
  <div class="table-card p-3">
    <div class="table-responsive">
      <table class="table table-sm table-ledger align-middle">
        <thead><tr><th>Reg No</th><th>Name</th><th>Mobile</th><th>Course</th><th>Session</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($results as $r): ?>
          <tr>
            <td class="reg-no"><?= e($r['registration_no']) ?></td>
            <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
            <td><?= e($r['mobile']) ?></td>
            <td><?= e($r['course_name'] ?? '-') ?></td>
            <td><?= e($r['year_label'] ?? '-') ?></td>
            <td><a href="?student_id=<?= $r['id'] ?>" class="btn btn-sm btn-primary">Re-Register</a></td>
          </tr>
          <?php endforeach; ?>
          <?php if (!$results): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No approved student found matching that search.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

<?php else: ?>

  <div class="table-card p-4" style="max-width:650px;">
    <div class="alert alert-light border small mb-4">
      <strong><?= e($selected['first_name'] . ' ' . $selected['last_name']) ?></strong> —
      <span class="reg-no"><?= e($selected['registration_no']) ?></span><br>
      Currently in <?= e($selected['course_name'] ?? '-') ?>, Session <?= e($selected['year_label'] ?? '-') ?>.
      Personal, contact, and academic details will be carried forward automatically.
    </div>

    <form method="POST">
      <input type="hidden" name="old_student_id" value="<?= $selected['id'] ?>">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <label class="form-label">Course</label>
          <select name="course_id" class="form-select">
            <?php foreach ($courses as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $selected['course_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">New Session / Year *</label>
          <select name="session_id" class="form-select" required>
            <option value="">Select Session</option>
            <?php foreach ($sessionsYrs as $sy): ?>
              <option value="<?= $sy['id'] ?>"><?= e($sy['year_label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="d-flex justify-content-between">
        <a href="re_registration.php" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" name="confirm_reregister" value="1" class="btn btn-success"><i class="fa-solid fa-check"></i> Confirm Re-Registration</button>
      </div>
    </form>
  </div>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
