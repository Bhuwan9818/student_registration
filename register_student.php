<?php
require_once __DIR__ . '/config/config.php';
requireLogin();
requireUniversity($pdo);

error_reporting(E_ALL);
ini_set('display_errors', 1);

$activeUni = getActiveUniversity($pdo);
$pageTitle = 'New Student Registration';

if (!isset($_SESSION['wizard'])) {
    $_SESSION['wizard'] = [];
}

$step = (int)($_GET['step'] ?? 1);
if ($step < 1 || $step > 5) { $step = 1; }

$academicLevels = academicLevelLabels();
$documentTypes  = documentTypeLabels();

// ---- Handle form submission for each step ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($step === 1) {
        $fullName = trim($_POST['full_name'] ?? '');
        if ($fullName === '' || empty($_POST['course_id']) || empty($_POST['session_id'])) {
            flash('error', 'Please fill in all required fields.');
            redirect('register_student.php?step=1');
        }
        $parts = preg_split('/\s+/', $fullName, 2);
        $_SESSION['wizard']['first_name'] = $parts[0];
        $_SESSION['wizard']['last_name']  = $parts[1] ?? '';
        $_SESSION['wizard']['full_name']  = $fullName;

        $_SESSION['wizard']['session_id']      = $_POST['session_id'];
        $_SESSION['wizard']['course_id']       = $_POST['course_id'];
        $_SESSION['wizard']['specialization']  = trim($_POST['specialization'] ?? '');
        $_SESSION['wizard']['semester_no']     = (int)($_POST['semester_no'] ?? 1);

        $_SESSION['wizard']['father_name']       = trim($_POST['father_name'] ?? '');
        $_SESSION['wizard']['mother_name']       = trim($_POST['mother_name'] ?? '');
        $_SESSION['wizard']['abc_id']            = trim($_POST['abc_id'] ?? '');
        $_SESSION['wizard']['deb_id']            = trim($_POST['deb_id'] ?? '');
        $_SESSION['wizard']['dob']               = $_POST['dob'] ?? '';
        $_SESSION['wizard']['gender']            = $_POST['gender'] ?? '';
        $_SESSION['wizard']['category']          = $_POST['category'] ?? '';
        $_SESSION['wizard']['employment_status'] = $_POST['employment_status'] ?? '';
        $_SESSION['wizard']['marital_status']    = $_POST['marital_status'] ?? '';
        $_SESSION['wizard']['religion']          = $_POST['religion'] ?? '';
        $_SESSION['wizard']['aadhar_no']         = trim($_POST['aadhar_no'] ?? '');
        $_SESSION['wizard']['nationality']       = trim($_POST['nationality'] ?? 'Indian');

        redirect('register_student.php?step=2');
    }

    if ($step === 2) {
        if (empty($_POST['mobile']) || empty($_POST['address']) || empty($_POST['city']) || empty($_POST['state']) || empty($_POST['pincode'])) {
            flash('error', 'Please fill in all required fields.');
            redirect('register_student.php?step=2');
        }
        $_SESSION['wizard']['email']           = trim($_POST['email'] ?? '');
        $_SESSION['wizard']['alt_email']       = trim($_POST['alt_email'] ?? '');
        $_SESSION['wizard']['mobile']          = trim($_POST['mobile']);
        $_SESSION['wizard']['alt_mobile']      = trim($_POST['alt_mobile'] ?? '');
        $_SESSION['wizard']['address']         = trim($_POST['address']);
        $_SESSION['wizard']['pincode']         = trim($_POST['pincode']);
        $_SESSION['wizard']['city']            = trim($_POST['city']);
        $_SESSION['wizard']['district']        = trim($_POST['district'] ?? '');
        $_SESSION['wizard']['state']           = trim($_POST['state']);
        $_SESSION['wizard']['guardian_mobile'] = trim($_POST['guardian_mobile'] ?? '');

        redirect('register_student.php?step=3');
    }

    if ($step === 3) {
        $tenth = $_POST['academics']['10th'] ?? [];
        if (empty($tenth['institution_board']) || empty($tenth['year_of_passing'])) {
            flash('error', 'High School (10th) details are required.');
            redirect('register_student.php?step=3');
        }

        $academicsData = $_POST['academics'] ?? [];
        $existingAcademics = $_SESSION['wizard']['academics'] ?? [];

        foreach ($academicLevels as $levelKey => $levelLabel) {
            $row = $academicsData[$levelKey] ?? [];
            $hasData = !empty($row['institution_board']) || !empty($row['year_of_passing']) || !empty($row['percentage']);
            $priorMarksheet = $existingAcademics[$levelKey]['marksheet_path'] ?? null;

            $uploadedPath = handleUpload("marksheet_$levelKey", 'marksheets', ['jpg','jpeg','png','pdf']);
            $finalPath = $uploadedPath ?: $priorMarksheet;

            if ($hasData) {
                if (!$finalPath) {
                    flash('error', $levelLabel . ' marksheet is required since you entered ' . $levelLabel . ' details.');
                    redirect('register_student.php?step=3');
                }
                $row['marksheet_path'] = $finalPath;
            }
            $academicsData[$levelKey] = $row;
        }

        $_SESSION['wizard']['academics'] = $academicsData;
        redirect('register_student.php?step=4');
    }

    if ($step === 4) {
        $existingDocs = $_SESSION['wizard']['documents'] ?? [];

        foreach (array_keys($documentTypes) as $docKey) {
            $path = handleUpload($docKey, 'documents', ['jpg','jpeg','png','pdf']);
            if ($path) { $existingDocs[$docKey] = $path; }
        }
        $_SESSION['wizard']['documents'] = $existingDocs;

        $missingRequired = empty($existingDocs['photo']) || empty($existingDocs['aadhaar']) || empty($existingDocs['student_signature']);

        if ($missingRequired) {
            flash('error', 'Photo, Aadhaar, and Student\'s Signature are required documents.');
            redirect('register_student.php?step=4');
        }

        redirect('register_student.php?step=5');
    }

    if ($step === 5 && isset($_POST['final_submit'])) {
        $w = $_SESSION['wizard'];
        $regNo = generateRegistrationNo($pdo);

        $sql = "INSERT INTO students (
                    registration_no, registration_type, created_by,
                    first_name, last_name, father_name, mother_name, dob, gender, category,
                    employment_status, marital_status, religion, nationality, aadhar_no, abc_id, deb_id, photo_path,
                    mobile, alt_mobile, email, alt_email, address, city, district, state, pincode, guardian_mobile,
                    university_id, course_id, specialization, session_id, semester_no
                ) VALUES (?,'fresh',?, ?,?,?,?,?,?,?, ?,?,?,?,?,?,?,?, ?,?,?,?,?,?,?,?,?,?, ?,?,?,?,?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $regNo, $_SESSION['user_id'],
            $w['first_name'], $w['last_name'], $w['father_name'], $w['mother_name'], $w['dob'] ?: null, $w['gender'], $w['category'],
            $w['employment_status'], $w['marital_status'], $w['religion'], $w['nationality'], $w['aadhar_no'], $w['abc_id'], $w['deb_id'], $w['documents']['photo'] ?? null,
            $w['mobile'], $w['alt_mobile'], $w['email'], $w['alt_email'], $w['address'], $w['city'], $w['district'], $w['state'], $w['pincode'], $w['guardian_mobile'],
            $activeUni['id'], $w['course_id'], $w['specialization'], $w['session_id'], $w['semester_no']
        ]);

        $newStudentId = $pdo->lastInsertId();

        // Academic history rows
        foreach ($academicLevels as $levelKey => $levelLabel) {
            $row = $w['academics'][$levelKey] ?? [];
            if (!empty($row['institution_board']) || !empty($row['year_of_passing']) || !empty($row['percentage'])) {
                $ins = $pdo->prepare("INSERT INTO student_academics (student_id, level, institution_board, year_of_passing, percentage, marksheet_path) VALUES (?,?,?,?,?,?)");
                $ins->execute([$newStudentId, $levelKey, $row['institution_board'] ?? '', $row['year_of_passing'] ?? '', $row['percentage'] ?? '', $row['marksheet_path'] ?? null]);
            }
        }

        // Document rows (photo already stored on students.photo_path too, but log it here as well for a complete document list)
        foreach ($w['documents'] ?? [] as $docKey => $path) {
            $ins = $pdo->prepare("INSERT INTO student_documents (student_id, doc_type, file_path) VALUES (?,?,?)");
            $ins->execute([$newStudentId, $docKey, $path]);
        }

        unset($_SESSION['wizard']);

        logActivity($pdo, $_SESSION['user_id'], 'registration',
            'New registration for ' . $w['full_name'], $newStudentId);

        flash('success', "Registration submitted successfully! Reg No: $regNo. You can now submit the fee.");
        redirect('submit_fee.php?student_id=' . $newStudentId);
    }
}

$w = $_SESSION['wizard'];

$courses = $pdo->prepare("SELECT * FROM courses WHERE status='active' AND university_id = ? ORDER BY name");
$courses->execute([$activeUni['id']]);
$courses = $courses->fetchAll();
$sessionsYrs = $pdo->query("SELECT * FROM sessions_years WHERE status='active' ORDER BY year_label DESC")->fetchAll();

$courseNameMap = [];
foreach ($courses as $c) { $courseNameMap[$c['id']] = $c['name']; }
$sessionLabelMap = [];
foreach ($sessionsYrs as $sy) { $sessionLabelMap[$sy['id']] = $sy['year_label']; }

// Sub-courses grouped by course, for the dependent dropdown in Step 1
$subCoursesByCourse = [];
if ($courses) {
    $courseIds = array_column($courses, 'id');
    $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
    $scStmt = $pdo->prepare("SELECT * FROM sub_courses WHERE status='active' AND course_id IN ($placeholders) ORDER BY name");
    $scStmt->execute($courseIds);
    foreach ($scStmt->fetchAll() as $sc) {
        $subCoursesByCourse[$sc['course_id']][] = $sc['name'];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="alert alert-light border small mb-3 d-flex justify-content-between align-items-center">
  <span><i class="fa-solid fa-building-columns text-muted me-1"></i> Registering for <strong><?= e($activeUni['name']) ?></strong></span>
  <a href="choose_university.php?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="small">Change university</a>
</div>

<div class="table-card p-4">

  <ul class="wizard-steps">
    <li class="<?= $step==1?'active':($step>1?'done':'') ?>"><div class="circle">1</div>Basic Details</li>
    <li class="<?= $step==2?'active':($step>2?'done':'') ?>"><div class="circle">2</div>Personal Details</li>
    <li class="<?= $step==3?'active':($step>3?'done':'') ?>"><div class="circle">3</div>Academics</li>
    <li class="<?= $step==4?'active':($step>4?'done':'') ?>"><div class="circle">4</div>Documents</li>
    <li class="<?= $step==5?'active':'' ?>"><div class="circle">5</div>Application Form</li>
  </ul>

  <?php if ($step === 1): ?>
  <form method="POST">
    <h5 class="mb-3">Applying For</h5>
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label">Admission Session *</label>
        <select name="session_id" class="form-select" required>
          <option value="">Select Session</option>
          <?php foreach ($sessionsYrs as $sy): ?>
            <option value="<?= $sy['id'] ?>" <?= (($w['session_id'] ?? '') == $sy['id']) ? 'selected' : '' ?>><?= e($sy['year_label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Admission Type</label>
        <input type="text" class="form-control" value="Fresh" disabled>
      </div>
      <div class="col-md-4">
        <label class="form-label">Course *</label>
        <select name="course_id" id="courseSelect" class="form-select" required>
          <option value="">Select Course</option>
          <?php foreach ($courses as $c): ?>
            <?php [$filled, $total] = courseSeatUsage($pdo, $c['id']); ?>
            <option value="<?= $c['id'] ?>" <?= (($w['course_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
              <?= e($c['name']) ?><?= $total ? " ($filled/$total seats)" : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (!$courses): ?>
          <div class="small text-danger mt-1">No active courses found for <?= e($activeUni['name']) ?>. Ask an admin to add one in Master Data.</div>
        <?php endif; ?>
      </div>
      <div class="col-md-4">
        <label class="form-label">Specialization / Sub Course</label>
        <select name="specialization_select" id="subCourseSelect" class="form-select mb-2" style="display:none;"></select>
        <input type="text" name="specialization" id="subCourseText" class="form-control" value="<?= e($w['specialization'] ?? '') ?>" placeholder="e.g. English, Computer Science">
        <div id="subCourseHint" class="small text-muted mt-1"></div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Semester</label>
        <select name="semester_no" class="form-select">
          <?php for ($i = 1; $i <= 12; $i++): ?>
            <option value="<?= $i ?>" <?= (($w['semester_no'] ?? 1) == $i) ? 'selected' : '' ?>><?= $i ?></option>
          <?php endfor; ?>
        </select>
      </div>
    </div>

    <h5 class="mb-3">Basic Details</h5>
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Full Name *</label>
        <input type="text" name="full_name" class="form-control" value="<?= e($w['full_name'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Father's Name *</label>
        <input type="text" name="father_name" class="form-control" value="<?= e($w['father_name'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Mother's Name *</label>
        <input type="text" name="mother_name" class="form-control" value="<?= e($w['mother_name'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">ABC ID</label>
        <input type="text" name="abc_id" class="form-control" value="<?= e($w['abc_id'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">DEB ID</label>
        <input type="text" name="deb_id" class="form-control" value="<?= e($w['deb_id'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Date of Birth *</label>
        <input type="date" name="dob" class="form-control" value="<?= e($w['dob'] ?? '') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Gender *</label>
        <select name="gender" class="form-select" required>
          <option value="">Select</option>
          <?php foreach (['Male','Female','Other'] as $g): ?>
            <option value="<?= $g ?>" <?= (($w['gender'] ?? '') == $g) ? 'selected' : '' ?>><?= $g ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Category *</label>
        <select name="category" class="form-select" required>
          <option value="">Select</option>
          <?php foreach (['General','OBC','SC','ST','EWS','Other'] as $c): ?>
            <option value="<?= $c ?>" <?= (($w['category'] ?? '') == $c) ? 'selected' : '' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Employment Status *</label>
        <select name="employment_status" class="form-select" required>
          <option value="">Select</option>
          <?php foreach (['Unemployed','Employed','Self-Employed','Student'] as $es): ?>
            <option value="<?= $es ?>" <?= (($w['employment_status'] ?? '') == $es) ? 'selected' : '' ?>><?= $es ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Marital Status *</label>
        <select name="marital_status" class="form-select" required>
          <option value="">Select</option>
          <?php foreach (['Unmarried','Married'] as $ms): ?>
            <option value="<?= $ms ?>" <?= (($w['marital_status'] ?? '') == $ms) ? 'selected' : '' ?>><?= $ms ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Religion *</label>
        <select name="religion" class="form-select" required>
          <option value="">Select</option>
          <?php foreach (['Hindu','Muslim','Christian','Sikh','Buddhist','Jain','Other'] as $r): ?>
            <option value="<?= $r ?>" <?= (($w['religion'] ?? '') == $r) ? 'selected' : '' ?>><?= $r ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Aadhar *</label>
        <input type="text" name="aadhar_no" class="form-control" maxlength="14" value="<?= e($w['aadhar_no'] ?? '') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Nationality *</label>
        <input type="text" name="nationality" class="form-control" value="<?= e($w['nationality'] ?? 'Indian') ?>" required>
      </div>
    </div>

    <div class="mt-4 text-end">
      <button type="submit" class="btn btn-primary">Next <i class="fa-solid fa-arrow-right"></i></button>
    </div>
  </form>

  <?php elseif ($step === 2): ?>
  <form method="POST">
    <h5 class="mb-3">Contact Details</h5>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= e($w['email'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Alternate Email</label>
        <input type="email" name="alt_email" class="form-control" value="<?= e($w['alt_email'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Mobile *</label>
        <input type="text" name="mobile" class="form-control" value="<?= e($w['mobile'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Alternate Mobile</label>
        <input type="text" name="alt_mobile" class="form-control" value="<?= e($w['alt_mobile'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Guardian Mobile</label>
        <input type="text" name="guardian_mobile" class="form-control" value="<?= e($w['guardian_mobile'] ?? '') ?>">
      </div>
    </div>

    <h5 class="mb-3 mt-4">Address</h5>
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Address *</label>
        <input type="text" name="address" class="form-control" value="<?= e($w['address'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Pincode *</label>
        <input type="text" name="pincode" class="form-control" value="<?= e($w['pincode'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">City *</label>
        <input type="text" name="city" class="form-control" value="<?= e($w['city'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">District</label>
        <input type="text" name="district" class="form-control" value="<?= e($w['district'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">State *</label>
        <input type="text" name="state" class="form-control" value="<?= e($w['state'] ?? '') ?>" required>
      </div>
    </div>

    <div class="mt-4 d-flex justify-content-between">
      <a href="?step=1" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
      <button type="submit" class="btn btn-primary">Next <i class="fa-solid fa-arrow-right"></i></button>
    </div>
  </form>

  <?php elseif ($step === 3): ?>
  <form method="POST" enctype="multipart/form-data">
    <h5 class="mb-3">Academic Background</h5>
    <p class="text-muted small">High School (10th) is required. Add whichever other levels apply — if you fill in a level's details, its marksheet upload becomes required too.</p>

    <?php foreach ($academicLevels as $levelKey => $levelLabel): ?>
      <?php $row = $w['academics'][$levelKey] ?? []; $required = $levelKey === '10th'; $hasMarksheet = !empty($row['marksheet_path']); ?>
      <h6 class="mt-4 mb-2"><?= e($levelLabel) ?><?= $required ? ' *' : '' ?></h6>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Board / University / Institution<?= $required ? ' *' : '' ?></label>
          <input type="text" name="academics[<?= $levelKey ?>][institution_board]" class="form-control" value="<?= e($row['institution_board'] ?? '') ?>" <?= $required ? 'required' : '' ?>>
        </div>
        <div class="col-md-2">
          <label class="form-label">Year of Passing<?= $required ? ' *' : '' ?></label>
          <input type="text" name="academics[<?= $levelKey ?>][year_of_passing]" class="form-control" value="<?= e($row['year_of_passing'] ?? '') ?>" <?= $required ? 'required' : '' ?>>
        </div>
        <div class="col-md-3">
          <label class="form-label">Percentage / CGPA</label>
          <input type="text" name="academics[<?= $levelKey ?>][percentage]" class="form-control" value="<?= e($row['percentage'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label"><?= e($levelLabel) ?> Marksheet<?= $required ? ' *' : '' ?></label>
          <input type="file" name="marksheet_<?= $levelKey ?>" class="form-control" accept=".jpg,.jpeg,.png,.pdf" <?= ($required && !$hasMarksheet) ? 'required' : '' ?>>
          <?php if ($hasMarksheet): ?><div class="small text-success mt-1"><i class="fa-solid fa-check"></i> Uploaded</div><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="mt-4 d-flex justify-content-between">
      <a href="?step=2" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
      <button type="submit" class="btn btn-primary">Next <i class="fa-solid fa-arrow-right"></i></button>
    </div>
  </form>

  <?php elseif ($step === 4): ?>
  <form method="POST" enctype="multipart/form-data">
    <h5 class="mb-3">Documents</h5>
    <div class="row g-3">
      <?php foreach ($documentTypes as $docKey => $docLabel): ?>
        <?php
          $required = in_array($docKey, ['photo', 'aadhaar', 'student_signature']);
          $showAbc = $docKey === 'abc_document' && empty($w['abc_id']);
          $showDeb = $docKey === 'deb_document' && empty($w['deb_id']);
          if ($showAbc || $showDeb) continue; // skip ID-proof uploads when no ID was entered
          $already = $w['documents'][$docKey] ?? null;
        ?>
        <div class="col-md-3">
          <label class="form-label"><?= e($docLabel) ?><?= $required ? ' *' : '' ?></label>
          <input type="file" name="<?= $docKey ?>" class="form-control" accept=".jpg,.jpeg,.png,.pdf" <?= ($required && !$already) ? 'required' : '' ?>>
          <?php if ($already): ?><div class="small text-success mt-1"><i class="fa-solid fa-check"></i> Uploaded</div><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="mt-4 d-flex justify-content-between">
      <a href="?step=3" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
      <button type="submit" class="btn btn-primary">Next <i class="fa-solid fa-arrow-right"></i></button>
    </div>
  </form>

  <?php elseif ($step === 5): ?>
  <form method="POST">
    <h5 class="mb-3">Application Form — Review & Submit</h5>

    <div class="alert alert-light border">
      <h6 class="text-primary">Applying For</h6>
      <div class="row small mb-3">
        <div class="col-md-4"><strong>Course:</strong> <?= e($courseNameMap[$w['course_id']] ?? '-') ?></div>
        <div class="col-md-4"><strong>Specialization:</strong> <?= e($w['specialization'] ?: '-') ?></div>
        <div class="col-md-4"><strong>Session:</strong> <?= e($sessionLabelMap[$w['session_id']] ?? '-') ?> | Semester <?= e($w['semester_no']) ?></div>
      </div>

      <h6 class="text-primary">Basic Details</h6>
      <div class="row small mb-3">
        <div class="col-md-4"><strong>Name:</strong> <?= e($w['full_name']) ?></div>
        <div class="col-md-4"><strong>Father's Name:</strong> <?= e($w['father_name']) ?></div>
        <div class="col-md-4"><strong>Mother's Name:</strong> <?= e($w['mother_name']) ?></div>
        <div class="col-md-4"><strong>DOB:</strong> <?= e($w['dob']) ?></div>
        <div class="col-md-4"><strong>Gender:</strong> <?= e($w['gender']) ?></div>
        <div class="col-md-4"><strong>Category:</strong> <?= e($w['category']) ?></div>
        <div class="col-md-4"><strong>Aadhar:</strong> <?= e($w['aadhar_no']) ?></div>
        <div class="col-md-4"><strong>Nationality:</strong> <?= e($w['nationality']) ?></div>
      </div>

      <h6 class="text-primary">Contact & Address</h6>
      <div class="row small mb-3">
        <div class="col-md-4"><strong>Mobile:</strong> <?= e($w['mobile']) ?></div>
        <div class="col-md-4"><strong>Email:</strong> <?= e($w['email'] ?: '-') ?></div>
        <div class="col-md-4"><strong>Address:</strong> <?= e($w['address']) ?>, <?= e($w['city']) ?>, <?= e($w['state']) ?> - <?= e($w['pincode']) ?></div>
      </div>

      <h6 class="text-primary">Academics</h6>
      <div class="row small mb-3">
        <?php foreach ($academicLevels as $levelKey => $levelLabel): ?>
          <?php $row = $w['academics'][$levelKey] ?? []; if (empty($row['institution_board'])) continue; ?>
          <div class="col-md-6"><strong><?= e($levelLabel) ?>:</strong> <?= e($row['institution_board']) ?> (<?= e($row['year_of_passing'] ?? '-') ?>, <?= e($row['percentage'] ?? '-') ?>%) <?= !empty($row['marksheet_path']) ? '<span class="text-success">— Marksheet uploaded</span>' : '' ?></div>
        <?php endforeach; ?>
      </div>

      <h6 class="text-primary">Documents</h6>
      <div class="row small">
        <?php foreach ($w['documents'] ?? [] as $docKey => $path): ?>
          <div class="col-md-4 mb-1"><i class="fa-solid fa-file text-muted"></i> <?= e($documentTypes[$docKey] ?? $docKey) ?> — <span class="text-success">Uploaded</span></div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="mt-4 d-flex justify-content-between">
      <a href="?step=4" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
      <button type="submit" name="final_submit" value="1" class="btn btn-success"><i class="fa-solid fa-check"></i> Submit Registration</button>
    </div>
  </form>
  <?php endif; ?>

</div>

<?php if ($step === 1): ?>
<script>
const subCoursesByCourse = <?= json_encode($subCoursesByCourse) ?>;
const currentSpecialization = <?= json_encode($w['specialization'] ?? '') ?>;

document.addEventListener('DOMContentLoaded', function () {
  const courseSelect = document.getElementById('courseSelect');
  const subSelect = document.getElementById('subCourseSelect');
  const subText = document.getElementById('subCourseText');
  const hint = document.getElementById('subCourseHint');

  function refreshSubCourses() {
    const options = subCoursesByCourse[courseSelect.value] || [];
    if (options.length > 0) {
      subSelect.innerHTML = '<option value="">Select Sub Course</option>' +
        options.map(name => `<option value="${name}"${name === currentSpecialization ? ' selected' : ''}>${name}</option>`).join('');
      subSelect.style.display = '';
      subText.style.display = 'none';
      subText.removeAttribute('name');
      subSelect.setAttribute('name', 'specialization');
      hint.textContent = '';
    } else {
      subSelect.style.display = 'none';
      subSelect.removeAttribute('name');
      subText.style.display = '';
      subText.setAttribute('name', 'specialization');
      hint.textContent = courseSelect.value ? 'No sub-courses set up for this course — type one in if needed.' : '';
    }
  }

  if (courseSelect) {
    courseSelect.addEventListener('change', refreshSubCourses);
    refreshSubCourses();
  }
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
