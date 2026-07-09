<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$courseId = (int)($_GET['course_id'] ?? 0);
$stmt = $pdo->prepare("SELECT c.*, u.name as university_name FROM courses c JOIN universities u ON u.id = c.university_id WHERE c.id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    flash('error', 'Course not found.');
    redirect('admin_master.php');
}

$totalSemesters = courseTotalSemesters($course);
$pageTitle = 'Fee Structure - ' . $course['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($sem = 1; $sem <= $totalSemesters; $sem++) {
        $amount = (float)($_POST['semester'][$sem] ?? 0);
        $stmt = $pdo->prepare("INSERT INTO course_fees (course_id, semester_no, amount) VALUES (?, ?, ?)
                                ON DUPLICATE KEY UPDATE amount = VALUES(amount)");
        $stmt->execute([$courseId, $sem, $amount]);
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
  <a href="admin_master.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Master Data</a>
</div>

<div class="table-card p-4" style="max-width:600px;">
  <p class="text-muted small mb-3">
    Set the fee amount for each semester of this <?= $course['duration_years'] ?>-year course
    (<?= $totalSemesters ?> semesters total). These amounts appear automatically
    when staff submit a student's fee for the matching semester.
  </p>
  <form method="POST">
    <div class="row g-3">
      <?php for ($sem = 1; $sem <= $totalSemesters; $sem++): ?>
      <div class="col-md-6">
        <label class="form-label">Semester <?= $sem ?></label>
        <div class="input-group">
          <span class="input-group-text">₹</span>
          <input type="number" step="0.01" min="0" name="semester[<?= $sem ?>]" class="form-control"
                 value="<?= e($existing[$sem] ?? '') ?>" placeholder="0.00">
        </div>
      </div>
      <?php endfor; ?>
    </div>
    <button type="submit" class="btn btn-primary mt-4"><i class="fa-solid fa-check"></i> Save Fee Structure</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
