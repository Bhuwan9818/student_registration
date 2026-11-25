<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$courseId = (int)($_GET['course_id'] ?? 0);
$stmt = $pdo->prepare("SELECT c.*, u.name as university_name FROM courses c JOIN universities u ON u.id = c.university_id WHERE c.id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    flash('error', 'Course not found.');
    redirect('admin_courses.php');
}

$totalSemesters = courseTotalSemesters($course);
$feeFrequency = getCourseFeeFrequency($pdo, $courseId);
$periods = getCourseFeePeriods($pdo, $courseId);

$pageTitle = 'Fee Structure - ' . $course['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $registrationFee = (float)($_POST['registration_fee'] ?? 0);
    $examFee = (float)($_POST['exam_fee'] ?? 0);
    $pdo->prepare("UPDATE courses SET registration_fee = ?, exam_fee = ? WHERE id = ?")
        ->execute([$registrationFee, $examFee, $courseId]);

    // Save fees for each period (year or semester)
    foreach ($periods as $p) {
        $amount = (float)($_POST['period'][$p['number']] ?? 0);
        $stmt = $pdo->prepare("INSERT INTO course_fees (course_id, semester_no, amount) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE amount = VALUES(amount)");
        $stmt->execute([$courseId, $p['number'], $amount]);
    }
    flash('success', 'Fee structure updated for ' . $course['name'] . '.');
    redirect('course_fees.php?course_id=' . $courseId);
}

$existing = [];
$feeStmt = $pdo->prepare("SELECT semester_no, amount FROM course_fees WHERE course_id = ?");
$feeStmt->execute([$courseId]);
foreach ($feeStmt->fetchAll() as $row) {
    $existing[$row['semester_no']] = $row['amount'];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow"><?= e($course['university_name']) ?></span>
    <h4>Fee Structure — <?= e($course['name']) ?></h4>
  </div>
  <a href="admin_courses.php?university_id=<?= $course['university_id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Courses</a>
</div>

<div class="table-card p-4" style="max-width:700px;">
  <p class="text-muted small mb-3">
    Set the fee structure for this <?= $course['duration_years'] ?>-year course
    (<?= count($periods) ?> <?= $feeFrequency === 'yearly' ? 'years' : 'semesters' ?> total, <?= getFeeFrequencyLabel($feeFrequency) ?>).
    These amounts appear automatically when staff submit a student's fee for the matching period.
  </p>
  <form method="POST">

    <div class="row g-3 mb-4 pb-3" style="border-bottom:1px solid var(--border);">
      <div class="col-md-6">
        <label class="form-label">Registration Fee <small class="text-muted">(one-time, at admission)</small></label>
        <div class="input-group">
          <span class="input-group-text">₹</span>
          <input type="number" step="0.01" min="0" name="registration_fee" class="form-control"
                 value="<?= e($course['registration_fee'] ?? 0) ?>" placeholder="0.00">
        </div>
        <div class="form-text">Charged once, only in the first period.</div>
      </div>
      <div class="col-md-6">
        <label class="form-label">Exam Fee <small class="text-muted">(every period)</small></label>
        <div class="input-group">
          <span class="input-group-text">₹</span>
          <input type="number" step="0.01" min="0" name="exam_fee" class="form-control"
                 value="<?= e($course['exam_fee'] ?? 0) ?>" placeholder="0.00">
        </div>
        <div class="form-text">Same amount, added to every period's expected fee.</div>
      </div>
    </div>

    <label class="form-label mb-2 d-block"><?= $feeFrequency === 'yearly' ? 'Yearly Fee' : 'Semester-wise Fee' ?></label>
    <div class="row g-3">
      <?php foreach ($periods as $p): ?>
      <div class="col-md-6">
        <label class="form-label"><?= e($p['label']) ?></label>
        <div class="input-group">
          <span class="input-group-text">₹</span>
          <input type="number" step="0.01" min="0" name="period[<?= $p['number'] ?>]" class="form-control"
                 value="<?= e($existing[$p['number']] ?? '') ?>" placeholder="0.00">
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <button type="submit" class="btn btn-primary mt-4"><i class="fa-solid fa-check"></i> Save Fee Structure</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>