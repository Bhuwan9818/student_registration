<?php
require_once __DIR__ . '/config/config.php';
requireStaff();

$pageTitle = 'Staff Dashboard';
$uid = $_SESSION['user_id'];

$total    = $pdo->prepare("SELECT COUNT(*) c FROM students WHERE created_by = ?");
$total->execute([$uid]); $total = $total->fetch()['c'];

$approved = $pdo->prepare("SELECT COUNT(*) c FROM students WHERE created_by = ? AND status='approved'");
$approved->execute([$uid]); $approved = $approved->fetch()['c'];

$pending  = $pdo->prepare("SELECT COUNT(*) c FROM students WHERE created_by = ? AND status='submitted'");
$pending->execute([$uid]); $pending = $pending->fetch()['c'];

$feeDue = $pdo->prepare("SELECT COUNT(*) c FROM students s WHERE s.created_by = ?
                          AND NOT EXISTS (SELECT 1 FROM fees f WHERE f.student_id = s.id)");
$feeDue->execute([$uid]); $feeDue = $feeDue->fetch()['c'];

$recent = $pdo->prepare("SELECT s.*, c.name as course_name FROM students s
                          LEFT JOIN courses c ON c.id = s.course_id
                          WHERE s.created_by = ? ORDER BY s.created_at DESC LIMIT 8");
$recent->execute([$uid]);
$recent = $recent->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Overview</span>
    <h4>Welcome, <?= e($_SESSION['full_name']) ?></h4>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="text-muted small">Total Submitted</div>
      <div class="stat-value"><?= $total ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="text-muted small">Pending Review</div>
      <div class="stat-value text-warning"><?= $pending ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="text-muted small">Approved</div>
      <div class="stat-value text-success"><?= $approved ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="text-muted small">Fee Not Submitted</div>
      <div class="stat-value text-danger"><?= $feeDue ?></div>
    </div>
  </div>
</div>

<div class="mb-3">
  <a href="register_student.php" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> New Student Registration</a>
</div>

<div class="table-card p-3">
  <h6 class="mb-3">Your Recent Submissions</h6>
  <div class="table-responsive">
    <table class="table table-sm table-ledger align-middle">
      <thead class="table-light">
        <tr><th>Reg No</th><th>Name</th><th>Course</th><th>Status</th><th>Date</th><th></th></tr>
      </thead>
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
          <tr><td colspan="6" class="text-center text-muted py-3">No submissions yet. Click "New Student Registration" to get started.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
