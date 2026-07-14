<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();
requireUniversity($pdo);

$activeUni = getActiveUniversity($pdo);
$pageTitle = 'Student Ledger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registerUnder = (int)$_POST['register_under'];
    // Validate the chosen account is a real center/sub-center (or fall back to the admin's own id)
    if ($registerUnder) {
        $chk = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'staff'");
        $chk->execute([$registerUnder]);
        if (!$chk->fetch()) { $registerUnder = $_SESSION['user_id']; }
    } else {
        $registerUnder = $_SESSION['user_id'];
    }

    $regNo = generateRegistrationNo($pdo);
    $status = in_array($_POST['status'], ['submitted', 'approved']) ? $_POST['status'] : 'submitted';

    $sql = "INSERT INTO students (
                registration_no, registration_type, created_by,
                first_name, last_name, dob, gender, category,
                mobile, alt_mobile, email, address, city, state, pincode,
                father_name, mother_name, guardian_mobile,
                last_qualification, board_university, passing_year, percentage,
                university_id, course_id, session_id, semester_no, status
            ) VALUES (?,'fresh',?, ?,?,?,?,?, ?,?,?,?,?,?,?, ?,?,?, ?,?,?,?, ?,?,?,1,?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $regNo, $registerUnder,
        trim($_POST['first_name']), trim($_POST['last_name']), $_POST['dob'] ?: null, $_POST['gender'] ?: null, $_POST['category'] ?: null,
        trim($_POST['mobile']), trim($_POST['alt_mobile']), trim($_POST['email']), trim($_POST['address']), trim($_POST['city']), trim($_POST['state']), trim($_POST['pincode']),
        trim($_POST['father_name']), trim($_POST['mother_name']), trim($_POST['guardian_mobile']),
        trim($_POST['last_qualification']), trim($_POST['board_university']), trim($_POST['passing_year']), trim($_POST['percentage']),
        $activeUni['id'], $_POST['course_id'], $_POST['session_id'], $status
    ]);

    $newId = $pdo->lastInsertId();
    logActivity($pdo, $_SESSION['user_id'], 'registration', 'Manually ledgered student: ' . $_POST['first_name'] . ' ' . $_POST['last_name'], $newId);
    flash('success', "Student ledgered successfully. Reg No: $regNo.");
    redirect('submit_fee.php?student_id=' . $newId);
}

$courses = $pdo->prepare("SELECT * FROM courses WHERE status='active' AND university_id = ? ORDER BY name");
$courses->execute([$activeUni['id']]);
$courses = $courses->fetchAll();
$sessionsYrs = $pdo->query("SELECT * FROM sessions_years WHERE status='active' ORDER BY year_label DESC")->fetchAll();

// Accounts admin can attribute this entry to: all Centers + Sub-Centers, hierarchy labeled
$accounts = $pdo->query("SELECT u.id, u.full_name, pu.full_name as center_name
                          FROM users u LEFT JOIN users pu ON pu.id = u.parent_user_id
                          WHERE u.role='staff' ORDER BY pu.full_name IS NULL DESC, pu.full_name, u.full_name")->fetchAll();

// Recent entries for quick confirmation
$recent = $pdo->prepare("SELECT s.*, c.name as course_name FROM students s
                          LEFT JOIN courses c ON c.id = s.course_id
                          WHERE s.university_id = ? ORDER BY s.created_at DESC LIMIT 10");
$recent->execute([$activeUni['id']]);
$recent = $recent->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Sub-Centers</span>
    <h4>Student Ledger</h4>
  </div>
</div>

<div class="alert alert-light border small mb-3 d-flex justify-content-between align-items-center">
  <span><i class="fa-solid fa-building-columns text-muted me-1"></i> Ledgering for <strong><?= e($activeUni['name']) ?></strong></span>
  <a href="choose_university.php?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="small">Change university</a>
</div>

<p class="text-muted small mb-3">Use this when you need to add a student's record directly — bypassing the step-by-step form — and attribute it to whichever Center or Sub-Center it belongs to (or leave it under your own admin account).</p>

<div class="table-card p-4 mb-3">
  <form method="POST">
    <h6 class="mb-3">Personal Details</h6>
    <div class="row g-3 mb-3">
      <div class="col-md-4"><label class="form-label">First Name *</label><input type="text" name="first_name" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Date of Birth</label><input type="date" name="dob" class="form-control"></div>
      <div class="col-md-4">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select"><option value="">—</option><?php foreach (['Male','Female','Other'] as $g): ?><option value="<?= $g ?>"><?= $g ?></option><?php endforeach; ?></select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Category</label>
        <select name="category" class="form-select"><option value="">—</option><?php foreach (['General','OBC','SC','ST','EWS','Other'] as $c): ?><option value="<?= $c ?>"><?= $c ?></option><?php endforeach; ?></select>
      </div>
    </div>

    <h6 class="mb-3">Contact & Address</h6>
    <div class="row g-3 mb-3">
      <div class="col-md-4"><label class="form-label">Mobile *</label><input type="text" name="mobile" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">Alt Mobile</label><input type="text" name="alt_mobile" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
      <div class="col-md-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
      <div class="col-md-4"><label class="form-label">City</label><input type="text" name="city" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">State</label><input type="text" name="state" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Pincode</label><input type="text" name="pincode" class="form-control"></div>
    </div>

    <h6 class="mb-3">Guardian & Academic <small class="text-muted">(optional)</small></h6>
    <div class="row g-3 mb-3">
      <div class="col-md-4"><label class="form-label">Father's Name</label><input type="text" name="father_name" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Mother's Name</label><input type="text" name="mother_name" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Guardian Mobile</label><input type="text" name="guardian_mobile" class="form-control"></div>
      <div class="col-md-3"><label class="form-label">Last Qualification</label><input type="text" name="last_qualification" class="form-control"></div>
      <div class="col-md-3"><label class="form-label">Board/University</label><input type="text" name="board_university" class="form-control"></div>
      <div class="col-md-3"><label class="form-label">Passing Year</label><input type="text" name="passing_year" class="form-control"></div>
      <div class="col-md-3"><label class="form-label">Percentage</label><input type="text" name="percentage" class="form-control"></div>
    </div>

    <h6 class="mb-3">Course & Attribution</h6>
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label">Course *</label>
        <select name="course_id" class="form-select" required>
          <option value="">Select Course</option>
          <?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Session *</label>
        <select name="session_id" class="form-select" required>
          <option value="">Select Session</option>
          <?php foreach ($sessionsYrs as $sy): ?><option value="<?= $sy['id'] ?>"><?= e($sy['year_label']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="submitted">Submitted</option>
          <option value="approved">Approved</option>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Register Under *</label>
        <select name="register_under" class="form-select" required>
          <option value="<?= $_SESSION['user_id'] ?>">Admin (Direct)</option>
          <?php foreach ($accounts as $a): ?>
            <option value="<?= $a['id'] ?>"><?= $a['center_name'] ? '↳ ' . e($a['full_name']) . ' (' . e($a['center_name']) . ')' : e($a['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save to Ledger</button>
  </form>
</div>

<div class="table-card p-3">
  <div class="section-title mb-3">Recent Registrations in <?= e($activeUni['name']) ?></div>
  <div class="table-responsive">
    <table class="table table-sm table-ledger align-middle">
      <thead><tr><th>Reg No</th><th>Name</th><th>Course</th><th>Status</th><th>Date</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($recent as $r): ?>
        <tr>
          <td class="reg-no"><?= e($r['registration_no']) ?></td>
          <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
          <td><?= e($r['course_name'] ?? '-') ?></td>
          <td><?= statusBadge($r['status']) ?></td>
          <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
          <td><a href="student_detail.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$recent): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No registrations yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
