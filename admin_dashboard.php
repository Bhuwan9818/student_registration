<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();
requireUniversity($pdo);

$activeUni = getActiveUniversity($pdo);
$univId = $activeUni['id'];
$pageTitle = 'Admin Dashboard';

$totalStudents   = $pdo->prepare("SELECT COUNT(*) c FROM students WHERE university_id = ?");
$totalStudents->execute([$univId]); $totalStudents = $totalStudents->fetch()['c'];

$approved        = $pdo->prepare("SELECT COUNT(*) c FROM students WHERE university_id = ? AND status='approved'");
$approved->execute([$univId]); $approved = $approved->fetch()['c'];

$pendingReview   = $pdo->prepare("SELECT COUNT(*) c FROM students WHERE university_id = ? AND status='submitted'");
$pendingReview->execute([$univId]); $pendingReview = $pendingReview->fetch()['c'];

$pendingFees     = $pdo->prepare("SELECT COUNT(*) c FROM fees f JOIN students s ON s.id = f.student_id WHERE s.university_id = ? AND f.status='pending'");
$pendingFees->execute([$univId]); $pendingFees = $pendingFees->fetch()['c'];

$totalCollected  = $pdo->prepare("SELECT COALESCE(SUM(f.amount),0) s FROM fees f JOIN students s ON s.id = f.student_id WHERE s.university_id = ? AND f.status='verified'");
$totalCollected->execute([$univId]); $totalCollected = $totalCollected->fetch()['s'];

$totalStaff      = $pdo->query("SELECT COUNT(*) c FROM users WHERE role='staff'")->fetch()['c'];

$recent = $pdo->prepare("SELECT s.*, u.full_name as staff_name, c.name as course_name
                        FROM students s
                        LEFT JOIN users u ON u.id = s.created_by
                        LEFT JOIN courses c ON c.id = s.course_id
                        WHERE s.university_id = ?
                        ORDER BY s.created_at DESC LIMIT 8");
$recent->execute([$univId]);
$recent = $recent->fetchAll();

// Registrations by course (for chart) — this university only
$byCourse = $pdo->prepare("SELECT c.name, COUNT(s.id) as cnt
                          FROM courses c LEFT JOIN students s ON s.course_id = c.id
                          WHERE c.status='active' AND c.university_id = ? GROUP BY c.id ORDER BY cnt DESC");
$byCourse->execute([$univId]);
$byCourse = $byCourse->fetchAll();

// Registrations trend, last 14 days — this university only
$trend = $pdo->prepare("SELECT DATE(created_at) d, COUNT(*) c FROM students
                       WHERE university_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
                       GROUP BY DATE(created_at)");
$trend->execute([$univId]);
$trend = $trend->fetchAll();
$trendMap = [];
foreach ($trend as $t) { $trendMap[$t['d']] = $t['c']; }
$trendLabels = []; $trendData = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $trendLabels[] = date('d M', strtotime($d));
    $trendData[] = $trendMap[$d] ?? 0;
}

// Recent activity for the sidebar feed (global — spans all universities)
$recentActivity = $pdo->query("SELECT a.*, u.full_name as user_name, s.registration_no
                                FROM activity_log a
                                LEFT JOIN users u ON u.id = a.user_id
                                LEFT JOIN students s ON s.id = a.student_id
                                ORDER BY a.created_at DESC LIMIT 6")->fetchAll();

$iconMap = [
    'registration' => 'fa-user-plus', 'approve' => 'fa-circle-check', 'reject' => 'fa-circle-xmark',
    'fee_submit' => 'fa-money-bill-wave', 'fee_verify' => 'fa-check-double', 'fee_reject' => 'fa-ban',
    'staff_create' => 'fa-user-shield', 'delete' => 'fa-trash',
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Overview</span>
    <h4>Admission Dashboard</h4>
  </div>
  <div class="d-flex gap-2">
    <a href="register_student.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-user-plus"></i> Apply Fresh</a>
    <a href="re_registration.php" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-rotate"></i> Re-Registration</a>
    <a href="admin_students.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-users"></i> All Applications</a>
  </div>
</div>

<div class="alert alert-light border small mb-3 d-flex justify-content-between align-items-center">
  <span><i class="fa-solid fa-building-columns text-muted me-1"></i> Showing figures for <strong><?= e($activeUni['name']) ?></strong></span>
  <a href="choose_university.php?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="small">Change university</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-2">
    <div class="stat-card">
      <i class="fa-solid fa-users stat-icon"></i>
      <div class="stat-label">Total</div>
      <div class="stat-value"><?= $totalStudents ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="stat-card">
      <i class="fa-solid fa-hourglass-half stat-icon"></i>
      <div class="stat-label">Pending Review</div>
      <div class="stat-value"><?= $pendingReview ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="stat-card">
      <i class="fa-solid fa-circle-check stat-icon"></i>
      <div class="stat-label">Approved</div>
      <div class="stat-value"><?= $approved ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="stat-card">
      <i class="fa-solid fa-money-check-dollar stat-icon"></i>
      <div class="stat-label">Fees Pending</div>
      <div class="stat-value"><?= $pendingFees ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="stat-card">
      <i class="fa-solid fa-sack-dollar stat-icon"></i>
      <div class="stat-label">Collected</div>
      <div class="stat-value" style="font-size:1.5rem;">₹<?= number_format($totalCollected, 0) ?></div>
    </div>
  </div>
  <div class="col-6 col-lg-2">
    <div class="stat-card">
      <i class="fa-solid fa-user-shield stat-icon"></i>
      <div class="stat-label">Active Staff</div>
      <div class="stat-value"><?= $totalStaff ?></div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-7">
    <div class="table-card p-3 h-100">
      <div class="section-title mb-3">Registration Trend (14 Days)</div>
      <canvas id="trendChart" height="130"></canvas>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="table-card p-3 h-100">
      <div class="section-title mb-3">Registrations by Course</div>
      <canvas id="courseChart" height="130"></canvas>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="table-card p-3">
      <div class="section-title mb-3">Recent Registrations</div>
      <div class="table-responsive">
        <table class="table table-sm table-ledger align-middle">
          <thead>
            <tr><th>Reg No</th><th>Name</th><th>Course</th><th>Staff</th><th>Status</th><th>Date</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $r): ?>
            <tr>
              <td class="reg-no"><?= e($r['registration_no']) ?></td>
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
  </div>

  <div class="col-lg-4">
    <div class="table-card p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="section-title mb-0">Recent Activity</div>
        <a href="admin_activity.php" class="small">View all</a>
      </div>
      <?php foreach ($recentActivity as $a): ?>
        <div class="activity-item">
          <div class="activity-dot"><i class="fa-solid <?= $iconMap[$a['action']] ?? 'fa-circle-dot' ?>"></i></div>
          <div>
            <div class="desc"><?= e($a['description']) ?></div>
            <div class="time"><?= date('d M, h:i A', strtotime($a['created_at'])) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$recentActivity): ?>
        <p class="text-muted small mb-0">No activity yet.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
const navy = '#16305C', gold = '#B8912F', border = '#E7E9F1';

new Chart(document.getElementById('trendChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($trendLabels) ?>,
    datasets: [{
      label: 'Registrations',
      data: <?= json_encode($trendData) ?>,
      borderColor: navy,
      backgroundColor: 'rgba(22,48,92,0.08)',
      fill: true,
      tension: 0.35,
      pointRadius: 2,
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false } },
      y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: border } }
    }
  }
});

new Chart(document.getElementById('courseChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_column($byCourse, 'name')) ?>,
    datasets: [{
      data: <?= json_encode(array_map('intval', array_column($byCourse, 'cnt'))) ?>,
      backgroundColor: gold,
      borderRadius: 6,
      maxBarThickness: 34,
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false } },
      y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: border } }
    }
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
