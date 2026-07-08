<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$pageTitle = 'New Student Registration';

// Initialize wizard session storage
if (!isset($_SESSION['wizard'])) {
    $_SESSION['wizard'] = [];
}

$step = (int)($_GET['step'] ?? 1);
if ($step < 1 || $step > 5) { $step = 1; }

// ---- Handle form submission for each step ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($step === 1) {
        $_SESSION['wizard']['first_name'] = trim($_POST['first_name']);
        $_SESSION['wizard']['last_name']  = trim($_POST['last_name']);
        $_SESSION['wizard']['dob']        = $_POST['dob'];
        $_SESSION['wizard']['gender']     = $_POST['gender'];
        $_SESSION['wizard']['category']   = $_POST['category'];
        $_SESSION['wizard']['aadhar_no']  = trim($_POST['aadhar_no']);

        $photoPath = handleUpload('photo', 'photos', ['jpg','jpeg','png']);
        if ($photoPath) { $_SESSION['wizard']['photo_path'] = $photoPath; }

        redirect('register_student.php?step=2');
    }

    if ($step === 2) {
        $_SESSION['wizard']['mobile']     = trim($_POST['mobile']);
        $_SESSION['wizard']['alt_mobile'] = trim($_POST['alt_mobile']);
        $_SESSION['wizard']['email']      = trim($_POST['email']);
        $_SESSION['wizard']['address']    = trim($_POST['address']);
        $_SESSION['wizard']['city']       = trim($_POST['city']);
        $_SESSION['wizard']['state']      = trim($_POST['state']);
        $_SESSION['wizard']['pincode']    = trim($_POST['pincode']);
        redirect('register_student.php?step=3');
    }

    if ($step === 3) {
        $_SESSION['wizard']['father_name']     = trim($_POST['father_name']);
        $_SESSION['wizard']['mother_name']     = trim($_POST['mother_name']);
        $_SESSION['wizard']['guardian_mobile'] = trim($_POST['guardian_mobile']);
        redirect('register_student.php?step=4');
    }

    if ($step === 4) {
        $_SESSION['wizard']['last_qualification'] = trim($_POST['last_qualification']);
        $_SESSION['wizard']['board_university']   = trim($_POST['board_university']);
        $_SESSION['wizard']['passing_year']       = trim($_POST['passing_year']);
        $_SESSION['wizard']['percentage']         = trim($_POST['percentage']);

        $marksheetPath = handleUpload('marksheet', 'marksheets', ['jpg','jpeg','png','pdf']);
        if ($marksheetPath) { $_SESSION['wizard']['marksheet_path'] = $marksheetPath; }

        redirect('register_student.php?step=5');
    }

    if ($step === 5) {
        $_SESSION['wizard']['university_id'] = $_POST['university_id'];
        $_SESSION['wizard']['course_id']     = $_POST['course_id'];
        $_SESSION['wizard']['session_id']    = $_POST['session_id'];

        // Final submit -> insert into DB
        if (isset($_POST['final_submit'])) {
            $w = $_SESSION['wizard'];
            $regNo = generateRegistrationNo($pdo);

            $sql = "INSERT INTO students (
                        registration_no, registration_type, created_by, first_name, last_name, dob, gender, category, aadhar_no, photo_path,
                        mobile, alt_mobile, email, address, city, state, pincode,
                        father_name, mother_name, guardian_mobile,
                        last_qualification, board_university, passing_year, percentage, marksheet_path,
                        university_id, course_id, session_id
                    ) VALUES (?,?,?,?,?,?,?,?,?,?, ?,?,?,?,?,?,?, ?,?,?, ?,?,?,?,?, ?,?,?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $regNo, 'fresh', $_SESSION['user_id'], $w['first_name'], $w['last_name'], $w['dob'] ?: null, $w['gender'], $w['category'], $w['aadhar_no'], $w['photo_path'] ?? null,
                $w['mobile'], $w['alt_mobile'], $w['email'], $w['address'], $w['city'], $w['state'], $w['pincode'],
                $w['father_name'], $w['mother_name'], $w['guardian_mobile'],
                $w['last_qualification'], $w['board_university'], $w['passing_year'], $w['percentage'], $w['marksheet_path'] ?? null,
                $w['university_id'], $w['course_id'], $w['session_id']
            ]);

            $newStudentId = $pdo->lastInsertId();
            unset($_SESSION['wizard']); // clear wizard data

            logActivity($pdo, $_SESSION['user_id'], 'registration',
                'New registration for ' . $w['first_name'] . ' ' . $w['last_name'], $newStudentId);

            flash('success', "Registration submitted successfully! Reg No: $regNo. You can now submit the fee.");
            redirect('submit_fee.php?student_id=' . $newStudentId);
        }
    }
}

$w = $_SESSION['wizard']; // shorthand for pre-filling fields on back navigation

$courses      = $pdo->query("SELECT * FROM courses WHERE status='active' ORDER BY name")->fetchAll();
$universities = $pdo->query("SELECT * FROM universities WHERE status='active' ORDER BY name")->fetchAll();
$sessionsYrs  = $pdo->query("SELECT * FROM sessions_years WHERE status='active' ORDER BY year_label DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="table-card p-4">

  <ul class="wizard-steps">
    <li class="<?= $step==1?'active':($step>1?'done':'') ?>"><div class="circle">1</div>Personal</li>
    <li class="<?= $step==2?'active':($step>2?'done':'') ?>"><div class="circle">2</div>Contact</li>
    <li class="<?= $step==3?'active':($step>3?'done':'') ?>"><div class="circle">3</div>Guardian</li>
    <li class="<?= $step==4?'active':($step>4?'done':'') ?>"><div class="circle">4</div>Academic</li>
    <li class="<?= $step==5?'active':'' ?>"><div class="circle">5</div>Course & Submit</li>
  </ul>

  <?php if ($step === 1): ?>
  <form method="POST" enctype="multipart/form-data">
    <h5 class="mb-3">Step 1: Personal Details</h5>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">First Name *</label>
        <input type="text" name="first_name" class="form-control" value="<?= e($w['first_name'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Last Name</label>
        <input type="text" name="last_name" class="form-control" value="<?= e($w['last_name'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Date of Birth</label>
        <input type="date" name="dob" class="form-control" value="<?= e($w['dob'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select">
          <?php foreach (['Male','Female','Other'] as $g): ?>
            <option value="<?= $g ?>" <?= (($w['gender'] ?? '') == $g) ? 'selected' : '' ?>><?= $g ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Category</label>
        <select name="category" class="form-select">
          <?php foreach (['General','OBC','SC','ST','EWS','Other'] as $c): ?>
            <option value="<?= $c ?>" <?= (($w['category'] ?? '') == $c) ? 'selected' : '' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Aadhar Number</label>
        <input type="text" name="aadhar_no" class="form-control" maxlength="14" value="<?= e($w['aadhar_no'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Student Photo</label>
        <input type="file" name="photo" class="form-control" accept=".jpg,.jpeg,.png">
      </div>
    </div>
    <div class="mt-4 text-end">
      <button type="submit" class="btn btn-primary">Next <i class="fa-solid fa-arrow-right"></i></button>
    </div>
  </form>

  <?php elseif ($step === 2): ?>
  <form method="POST">
    <h5 class="mb-3">Step 2: Contact & Address</h5>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Mobile Number *</label>
        <input type="text" name="mobile" class="form-control" value="<?= e($w['mobile'] ?? '') ?>" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Alternate Mobile</label>
        <input type="text" name="alt_mobile" class="form-control" value="<?= e($w['alt_mobile'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= e($w['email'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Pincode</label>
        <input type="text" name="pincode" class="form-control" value="<?= e($w['pincode'] ?? '') ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="2"><?= e($w['address'] ?? '') ?></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">City</label>
        <input type="text" name="city" class="form-control" value="<?= e($w['city'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">State</label>
        <input type="text" name="state" class="form-control" value="<?= e($w['state'] ?? '') ?>">
      </div>
    </div>
    <div class="mt-4 d-flex justify-content-between">
      <a href="?step=1" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
      <button type="submit" class="btn btn-primary">Next <i class="fa-solid fa-arrow-right"></i></button>
    </div>
  </form>

  <?php elseif ($step === 3): ?>
  <form method="POST">
    <h5 class="mb-3">Step 3: Guardian Details</h5>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Father's Name</label>
        <input type="text" name="father_name" class="form-control" value="<?= e($w['father_name'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Mother's Name</label>
        <input type="text" name="mother_name" class="form-control" value="<?= e($w['mother_name'] ?? '') ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Guardian Mobile</label>
        <input type="text" name="guardian_mobile" class="form-control" value="<?= e($w['guardian_mobile'] ?? '') ?>">
      </div>
    </div>
    <div class="mt-4 d-flex justify-content-between">
      <a href="?step=2" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
      <button type="submit" class="btn btn-primary">Next <i class="fa-solid fa-arrow-right"></i></button>
    </div>
  </form>

  <?php elseif ($step === 4): ?>
  <form method="POST" enctype="multipart/form-data">
    <h5 class="mb-3">Step 4: Academic Background</h5>
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Last Qualification</label>
        <input type="text" name="last_qualification" class="form-control" value="<?= e($w['last_qualification'] ?? '') ?>" placeholder="e.g. 12th, Bachelor's">
      </div>
      <div class="col-md-6">
        <label class="form-label">Board / University</label>
        <input type="text" name="board_university" class="form-control" value="<?= e($w['board_university'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Passing Year</label>
        <input type="text" name="passing_year" class="form-control" value="<?= e($w['passing_year'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Percentage / CGPA</label>
        <input type="text" name="percentage" class="form-control" value="<?= e($w['percentage'] ?? '') ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Marksheet Upload</label>
        <input type="file" name="marksheet" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
      </div>
    </div>
    <div class="mt-4 d-flex justify-content-between">
      <a href="?step=3" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
      <button type="submit" class="btn btn-primary">Next <i class="fa-solid fa-arrow-right"></i></button>
    </div>
  </form>

  <?php elseif ($step === 5): ?>
  <form method="POST">
    <h5 class="mb-3">Step 5: Course Selection & Review</h5>
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label">University *</label>
        <select name="university_id" class="form-select" required>
          <option value="">Select University</option>
          <?php foreach ($universities as $u): ?>
            <option value="<?= $u['id'] ?>" <?= (($w['university_id'] ?? '') == $u['id']) ? 'selected' : '' ?>><?= e($u['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Course *</label>
        <select name="course_id" id="courseSelect" class="form-select" required>
          <option value="">Select Course</option>
          <?php foreach ($courses as $c): ?>
            <?php [$filled, $total] = courseSeatUsage($pdo, $c['id']); ?>
            <option value="<?= $c['id'] ?>"
              data-filled="<?= $filled ?>" data-total="<?= $total ?? '' ?>"
              <?= (($w['course_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
              <?= e($c['name']) ?><?= $total ? " ($filled/$total seats)" : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div id="seatNote" class="small mt-1"></div>
      </div>
      <div class="col-md-4">
        <label class="form-label">Session *</label>
        <select name="session_id" class="form-select" required>
          <option value="">Select Session</option>
          <?php foreach ($sessionsYrs as $sy): ?>
            <option value="<?= $sy['id'] ?>" <?= (($w['session_id'] ?? '') == $sy['id']) ? 'selected' : '' ?>><?= e($sy['year_label']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="alert alert-light border">
      <h6>Review Summary</h6>
      <p class="mb-1 small"><strong>Name:</strong> <?= e(($w['first_name'] ?? '') . ' ' . ($w['last_name'] ?? '')) ?></p>
      <p class="mb-1 small"><strong>Mobile:</strong> <?= e($w['mobile'] ?? '') ?> | <strong>Email:</strong> <?= e($w['email'] ?? '') ?></p>
      <p class="mb-1 small"><strong>Father's Name:</strong> <?= e($w['father_name'] ?? '') ?></p>
      <p class="mb-0 small"><strong>Last Qualification:</strong> <?= e($w['last_qualification'] ?? '') ?> (<?= e($w['percentage'] ?? '') ?>%)</p>
    </div>

    <div class="mt-4 d-flex justify-content-between">
      <a href="?step=4" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Back</a>
      <button type="submit" name="final_submit" value="1" class="btn btn-success"><i class="fa-solid fa-check"></i> Submit Registration</button>
    </div>
  </form>
  <?php endif; ?>

</div>

<?php if ($step === 5): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const sel = document.getElementById('courseSelect');
  const note = document.getElementById('seatNote');
  function updateNote() {
    const opt = sel.options[sel.selectedIndex];
    const total = opt.dataset.total;
    const filled = opt.dataset.filled;
    if (!total) { note.innerHTML = ''; return; }
    const remaining = total - filled;
    if (remaining <= 0) {
      note.innerHTML = '<span class="text-danger"><i class="fa-solid fa-triangle-exclamation"></i> This course is at full capacity.</span>';
    } else if (remaining <= Math.ceil(total * 0.2)) {
      note.innerHTML = '<span class="text-warning"><i class="fa-solid fa-circle-exclamation"></i> Only ' + remaining + ' seat(s) left.</span>';
    } else {
      note.innerHTML = '<span class="text-muted">' + remaining + ' seat(s) available.</span>';
    }
  }
  sel.addEventListener('change', updateNote);
  updateNote();
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

