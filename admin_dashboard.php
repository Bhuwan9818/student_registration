<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$pageTitle = 'Admin Dashboard';

$totalStudents   = $pdo->query("SELECT COUNT(*) c FROM students")->fetch()['c'];
$approved        = $pdo->query("SELECT COUNT(*) c FROM students WHERE status='approved'")->fetch()['c'];
$pendingReview   = $pdo->query("SELECT COUNT(*) c FROM students WHERE status='submitted'")->fetch()['c'];
$pendingFees     = $pdo->query("SELECT COUNT(*) c FROM fees WHERE status='pending'")->fetch()['c'];
$totalCollected  = $pdo->query("SELECT COALESCE(SUM(amount),0) s FROM fees WHERE status='verified'")->fetch()['s'];
$totalStaff      = $pdo->query("SELECT COUNT(*) c FROM users WHERE role='staff'")->fetch()['c'];

$recent = $pdo->query("SELECT s.*, u.full_name as staff_name, c.name as course_name
                        FROM students s
                        LEFT JOIN users u ON u.id = s.created_by
                        LEFT JOIN courses c ON c.id = s.course_id
                        ORDER BY s.created_at DESC LIMIT 8")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<h4 class="mb-4">Dashboard Overview</h4>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="text-muted small">Total Registrations</div>
      <div class="stat-value"><?= $totalStudents ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="text-muted small">Pending Review</div>
      <div class="stat-value text-warning"><?= $pendingReview ?></div>
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
      <div class="text-muted small">Fees Pending Verification</div>
      <div class="stat-value text-danger"><?= $pendingFees ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="text-muted small">Fee Collected (Verified)</div>
      <div class="stat-value">₹<?= number_format($totalCollected, 2) ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="text-muted small">Active Staff</div>
      <div class="stat-value"><?= $totalStaff ?></div>
    </div>
  </div>
</div>

<div class="table-card bg-white p-3">
  <h6 class="mb-3">Recent Registrations</h6>
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead class="table-light">
        <tr><th>Reg No</th><th>Name</th><th>Course</th><th>Staff</th><th>Status</th><th>Date</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($recent as $r): ?>
        <tr>
          <td><?= e($r['registration_no']) ?></td>
          <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
          <td><?= e($r['course_name'] ?? '-') ?></td>
          <td><?= e($r['staff_name'] ?? '-') ?></td>
          <td><?= statusBadge($r['status']) ?></td>
          <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
          <td><a href="student_detail.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$recent): ?>
          <tr><td colspan="7" class="text-center text-muted py-3">No registrations yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
