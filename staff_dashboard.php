<?php
require_once __DIR__ . '/config/config.php';
requireStaff();
requireUniversity($pdo);

$activeUni = getActiveUniversity($pdo);
$pageTitle = 'Staff Dashboard';
$uid = $_SESSION['user_id'];
$univId = $activeUni['id'];

$total    = $pdo->prepare("SELECT COUNT(*) c FROM students WHERE created_by = ? AND university_id = ?");
$total->execute([$uid, $univId]); $total = $total->fetch()['c'];

$approved = $pdo->prepare("SELECT COUNT(*) c FROM students WHERE created_by = ? AND university_id = ? AND status='approved'");
$approved->execute([$uid, $univId]); $approved = $approved->fetch()['c'];

$pending  = $pdo->prepare("SELECT COUNT(*) c FROM students WHERE created_by = ? AND university_id = ? AND status='submitted'");
$pending->execute([$uid, $univId]); $pending = $pending->fetch()['c'];

$feeDue = $pdo->prepare("SELECT COUNT(*) c FROM students s WHERE s.created_by = ? AND s.university_id = ?
                          AND NOT EXISTS (SELECT 1 FROM fees f WHERE f.student_id = s.id)");
$feeDue->execute([$uid, $univId]); $feeDue = $feeDue->fetch()['c'];

$recent = $pdo->prepare("SELECT s.*, c.name as course_name FROM students s
                          LEFT JOIN courses c ON c.id = s.course_id
                          WHERE s.created_by = ? AND s.university_id = ? ORDER BY s.created_at DESC LIMIT 8");
$recent->execute([$uid, $univId]);
$recent = $recent->fetchAll();

$courses = $pdo->prepare("SELECT * FROM courses WHERE university_id = ? AND status='active' ORDER BY name");
$courses->execute([$univId]);
$courses = $courses->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Overview</span>
    <h4>Welcome, <?= e($_SESSION['full_name']) ?></h4>
  </div>
</div>

<div class="alert alert-light border small mb-3 d-flex justify-content-between align-items-center">
  <span><i class="fa-solid fa-building-columns text-muted me-1"></i> Working with <strong><?= e($activeUni['name']) ?></strong> — <?= count($courses) ?> active course(s)</span>
  <a href="choose_university.php?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="small">Change university</a>
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

<div class="mb-3 d-flex gap-2">
  <a href="register_student.php" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Apply Fresh</a>
  <a href="re_registration.php" class="btn btn-outline-primary"><i class="fa-solid fa-rotate"></i> Re-Registration</a>
</div>

<div class="row g-3">
  <div class="col-lg-8">
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
              <tr><td colspan="6" class="text-center text-muted py-3">No submissions yet for <?= e($activeUni['name']) ?>. Click "Apply Fresh" to get started.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="table-card p-3">
      <h6 class="mb-3">Courses at <?= e($activeUni['name']) ?></h6>
      <?php foreach ($courses as $c): ?>
        <?php [$filled, $total] = courseSeatUsage($pdo, $c['id']); $pct = $total ? min(100, round($filled / $total * 100)) : 0; ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between small">
            <span><?= e($c['name']) ?></span>
            <span class="text-muted"><?= $total ? "$filled/$total" : 'Unlimited' ?></span>
          </div>
          <?php if ($total): ?>
            <div class="seat-bar"><div class="seat-bar-fill <?= $pct >= 100 ? 'full' : ($pct >= 80 ? 'near' : '') ?>" style="width: <?= $pct ?>%"></div></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <?php if (!$courses): ?>
        <p class="text-muted small mb-0">No active courses set up yet for this university.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
