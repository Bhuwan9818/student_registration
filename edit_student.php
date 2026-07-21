<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT s.*, un.name as university_name FROM students s
                        LEFT JOIN universities un ON un.id = s.university_id
                        WHERE s.id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    flash('error', 'Registration not found.');
    redirect('admin_students.php');
}

$academicLevels = academicLevelLabels();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name']);
    $parts = preg_split('/\s+/', $fullName, 2);
    $firstName = $parts[0] ?? '';
    $lastName = $parts[1] ?? '';

    $photoPath = handleUpload('photo', 'photos', ['jpg','jpeg','png']);

    $sql = "UPDATE students SET
                enrollment_no = ?, status = ?,
                first_name = ?, last_name = ?, father_name = ?, mother_name = ?, dob = ?, gender = ?, category = ?,
                employment_status = ?, marital_status = ?, religion = ?, nationality = ?, aadhar_no = ?, abc_id = ?, deb_id = ?,
                mobile = ?, alt_mobile = ?, email = ?, alt_email = ?, address = ?, city = ?, district = ?, state = ?, pincode = ?, guardian_mobile = ?,
                course_id = ?, specialization = ?, session_id = ?, semester_no = ?
                " . ($photoPath ? ", photo_path = ?" : "") . "
            WHERE id = ?";

    $params = [
        trim($_POST['enrollment_no']) ?: null, $_POST['status'],
        $firstName, $lastName, trim($_POST['father_name']), trim($_POST['mother_name']), $_POST['dob'] ?: null, $_POST['gender'], $_POST['category'],
        $_POST['employment_status'], $_POST['marital_status'], $_POST['religion'], trim($_POST['nationality']), trim($_POST['aadhar_no']), trim($_POST['abc_id']), trim($_POST['deb_id']),
        trim($_POST['mobile']), trim($_POST['alt_mobile']), trim($_POST['email']), trim($_POST['alt_email']), trim($_POST['address']), trim($_POST['city']), trim($_POST['district']), trim($_POST['state']), trim($_POST['pincode']), trim($_POST['guardian_mobile']),
        $_POST['course_id'], trim($_POST['specialization']), $_POST['session_id'], (int)$_POST['semester_no']
    ];
    if ($photoPath) { $params[] = $photoPath; }
    $params[] = $id;

    $upd = $pdo->prepare($sql);
    $upd->execute($params);

    // Update academic history rows
    foreach ($academicLevels as $levelKey => $levelLabel) {
        $row = $_POST['academics'][$levelKey] ?? [];
        $marksheetPath = handleUpload("marksheet_$levelKey", 'marksheets', ['jpg','jpeg','png','pdf']);

        $existing = $pdo->prepare("SELECT id, marksheet_path FROM student_academics WHERE student_id = ? AND level = ?");
        $existing->execute([$id, $levelKey]);
        $existingRow = $existing->fetch();

        $hasData = !empty($row['institution_board']) || !empty($row['year_of_passing']) || !empty($row['percentage']);
        $finalMarksheet = $marksheetPath ?: ($existingRow['marksheet_path'] ?? null);

        if ($existingRow) {
            if ($hasData) {
                $u = $pdo->prepare("UPDATE student_academics SET institution_board=?, year_of_passing=?, percentage=?, marksheet_path=? WHERE id = ?");
                $u->execute([$row['institution_board'] ?? '', $row['year_of_passing'] ?? '', $row['percentage'] ?? '', $finalMarksheet, $existingRow['id']]);
            } else {
                $pdo->prepare("DELETE FROM student_academics WHERE id = ?")->execute([$existingRow['id']]);
            }
        } elseif ($hasData) {
            $ins = $pdo->prepare("INSERT INTO student_academics (student_id, level, institution_board, year_of_passing, percentage, marksheet_path) VALUES (?,?,?,?,?,?)");
            $ins->execute([$id, $levelKey, $row['institution_board'] ?? '', $row['year_of_passing'] ?? '', $row['percentage'] ?? '', $finalMarksheet]);
        }
    }

    logActivity($pdo, $_SESSION['user_id'], 'registration', 'Edited registration for ' . $firstName . ' ' . $lastName, $id);
    flash('success', 'Registration updated successfully.');
    redirect('student_detail.php?id=' . $id);
}

$courses = $pdo->prepare("SELECT * FROM courses WHERE university_id = ? ORDER BY name");
$courses->execute([$student['university_id']]);
$courses = $courses->fetchAll();
$sessionsYrs = $pdo->query("SELECT * FROM sessions_years ORDER BY year_label DESC")->fetchAll();

$academicsStmt = $pdo->prepare("SELECT * FROM student_academics WHERE student_id = ?");
$academicsStmt->execute([$id]);
$academicsByLevel = [];
foreach ($academicsStmt->fetchAll() as $a) { $academicsByLevel[$a['level']] = $a; }

$pageTitle = 'Edit Registration - ' . $student['registration_no'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow"><?= e($student['university_name']) ?></span>
    <h4>Edit Registration — <?= e($student['first_name'] . ' ' . $student['last_name']) ?></h4>
  </div>
  <a href="student_detail.php?id=<?= $id ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Registration</a>
</div>

<form method="POST" enctype="multipart/form-data">
  <div class="table-card p-4 mb-3">
    <div class="section-title mb-3">Admin Only</div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Registration No.</label>
        <input type="text" class="form-control mono" value="<?= e($student['registration_no']) ?>" disabled>
      </div>
      <div class="col-md-4">
        <label class="form-label">Enrollment Number <small class="text-muted">(assign once processed)</small></label>
        <input type="text" name="enrollment_no" class="form-control" value="<?= e($student['enrollment_no']) ?>" placeholder="e.g. VSA2026001234">
      </div>
      <div class="col-md-4">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="submitted" <?= $student['status'] == 'submitted' ? 'selected' : '' ?>>Submitted</option>
          <option value="approved" <?= $student['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
          <option value="rejected" <?= $student['status'] == 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
      </div>
    </div>
  </div>

  <div class="table-card p-4 mb-3">
    <div class="section-title mb-3">Basic Details</div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Full Name *</label>
        <input type="text" name="full_name" class="form-control" value="<?= e($student['first_name'] . ' ' . $student['last_name']) ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Father's Name</label>
        <input type="text" name="father_name" class="form-control" value="<?= e($student['father_name']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Mother's Name</label>
        <input type="text" name="mother_name" class="form-control" value="<?= e($student['mother_name']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">ABC ID</label>
        <input type="text" name="abc_id" class="form-control" value="<?= e($student['abc_id']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">DEB ID</label>
        <input type="text" name="deb_id" class="form-control" value="<?= e($student['deb_id']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="dob" class="form-control" value="<?= e($student['dob']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Aadhar</label>
        <input type="text" name="aadhar_no" class="form-control" value="<?= e($student['aadhar_no']) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select">
          <?php foreach (['Male','Female','Other'] as $g): ?>
            <option value="<?= $g ?>" <?= $student['gender'] == $g ? 'selected' : '' ?>><?= $g ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Category</label>
        <select name="category" class="form-select">
          <?php foreach (['General','OBC','SC','ST','EWS','Other'] as $c): ?>
            <option value="<?= $c ?>" <?= $student['category'] == $c ? 'selected' : '' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Employment Status</label>
        <select name="employment_status" class="form-select">
          <?php foreach (['Unemployed','Employed','Self-Employed','Student'] as $es): ?>
            <option value="<?= $es ?>" <?= $student['employment_status'] == $es ? 'selected' : '' ?>><?= $es ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Marital Status</label>
        <select name="marital_status" class="form-select">
          <?php foreach (['Unmarried','Married'] as $ms): ?>
            <option value="<?= $ms ?>" <?= $student['marital_status'] == $ms ? 'selected' : '' ?>><?= $ms ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Religion</label>
        <select name="religion" class="form-select">
          <?php foreach (['Hindu','Muslim','Christian','Sikh','Buddhist','Jain','Other'] as $r): ?>
            <option value="<?= $r ?>" <?= $student['religion'] == $r ? 'selected' : '' ?>><?= $r ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Nationality</label>
        <input type="text" name="nationality" class="form-control" value="<?= e($student['nationality']) ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Replace Photo <small class="text-muted">(optional)</small></label>
        <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
      </div>
    </div>
  </div>

  <div class="table-card p-4 mb-3">
    <div class="section-title mb-3">Contact & Address</div>
    <div class="row g-3">
      <div class="col-md-4"><label class="form-label">Mobile *</label><input type="text" name="mobile" class="form-control" value="<?= e($student['mobile']) ?>" required></div>
      <div class="col-md-4"><label class="form-label">Alt Mobile</label><input type="text" name="alt_mobile" class="form-control" value="<?= e($student['alt_mobile']) ?>"></div>
      <div class="col-md-4"><label class="form-label">Guardian Mobile</label><input type="text" name="guardian_mobile" class="form-control" value="<?= e($student['guardian_mobile']) ?>"></div>
      <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($student['email']) ?>"></div>
      <div class="col-md-6"><label class="form-label">Alt Email</label><input type="email" name="alt_email" class="form-control" value="<?= e($student['alt_email']) ?>"></div>
      <div class="col-md-8"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= e($student['address']) ?>"></div>
      <div class="col-md-4"><label class="form-label">Pincode</label><input type="text" name="pincode" class="form-control" value="<?= e($student['pincode']) ?>"></div>
      <div class="col-md-4"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="<?= e($student['city']) ?>"></div>
      <div class="col-md-4"><label class="form-label">District</label><input type="text" name="district" class="form-control" value="<?= e($student['district']) ?>"></div>
      <div class="col-md-4"><label class="form-label">State</label><input type="text" name="state" class="form-control" value="<?= e($student['state']) ?>"></div>
    </div>
  </div>

  <div class="table-card p-4 mb-3">
    <div class="section-title mb-3">Course & Session</div>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Course</label>
        <select name="course_id" class="form-select">
          <?php foreach ($courses as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $student['course_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Specialization / Sub Course</label>
        <input type="text" name="specialization" class="form-control" value="<?= e($student['specialization']) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Session</label>
        <select name="session_id" class="form-select">
          <?php foreach ($sessionsYrs as $sy): ?>
            <option value="<?= $sy['id'] ?>" <?= $student['session_id'] == $sy['id'] ? 'selected' : '' ?>><?= e($sy['year_label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Semester</label>
        <select name="semester_no" class="form-select">
          <?php for ($i = 1; $i <= 12; $i++): ?>
            <option value="<?= $i ?>" <?= $student['semester_no'] == $i ? 'selected' : '' ?>><?= $i ?></option>
          <?php endfor; ?>
        </select>
      </div>
    </div>
  </div>

  <div class="table-card p-4 mb-3">
    <div class="section-title mb-3">Academic Background</div>
    <?php foreach ($academicLevels as $levelKey => $levelLabel): ?>
      <?php $row = $academicsByLevel[$levelKey] ?? []; ?>
      <h6 class="mt-3 mb-2"><?= e($levelLabel) ?></h6>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Board / University / Institution</label>
          <input type="text" name="academics[<?= $levelKey ?>][institution_board]" class="form-control" value="<?= e($row['institution_board'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">Year of Passing</label>
          <input type="text" name="academics[<?= $levelKey ?>][year_of_passing]" class="form-control" value="<?= e($row['year_of_passing'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Percentage / CGPA</label>
          <input type="text" name="academics[<?= $levelKey ?>][percentage]" class="form-control" value="<?= e($row['percentage'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Marksheet <small class="text-muted">(replace)</small></label>
          <input type="file" name="marksheet_<?= $levelKey ?>" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
          <?php if (!empty($row['marksheet_path'])): ?><div class="small text-success mt-1"><i class="fa-solid fa-check"></i> On file</div><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
    <p class="text-muted small mt-2 mb-0">Clearing a level's Board/Year/Percentage and saving will remove that level from the record.</p>
  </div>

  <div class="d-flex justify-content-end gap-2 mb-4">
    <a href="student_detail.php?id=<?= $id ?>" class="btn btn-outline-secondary">Cancel</a>
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Save Changes</button>
  </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
