<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$pageTitle = 'Activity Log';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 40;
$offset = ($page - 1) * $perPage;

$total = $pdo->query("SELECT COUNT(*) FROM activity_log")->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

$stmt = $pdo->prepare("SELECT a.*, u.full_name as user_name, s.registration_no
                        FROM activity_log a
                        LEFT JOIN users u ON u.id = a.user_id
                        LEFT JOIN students s ON s.id = a.student_id
                        ORDER BY a.created_at DESC
                        LIMIT $perPage OFFSET $offset");
$stmt->execute();
$logs = $stmt->fetchAll();

$iconMap = [
    'registration' => 'fa-user-plus',
    'approve'      => 'fa-circle-check',
    'reject'       => 'fa-circle-xmark',
    'fee_submit'   => 'fa-money-bill-wave',
    'fee_verify'   => 'fa-check-double',
    'fee_reject'   => 'fa-ban',
    'staff_create' => 'fa-user-shield',
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Audit Trail</span>
    <h4>Activity Log</h4>
  </div>
</div>

<div class="table-card p-3">
  <?php foreach ($logs as $l): ?>
    <div class="activity-item">
      <div class="activity-dot"><i class="fa-solid <?= $iconMap[$l['action']] ?? 'fa-circle-dot' ?>"></i></div>
      <div class="flex-fill">
        <div class="desc">
          <?= e($l['description']) ?>
          <?php if ($l['registration_no']): ?>
            — <a href="student_detail.php?id=<?= $l['student_id'] ?>" class="reg-no mono"><?= e($l['registration_no']) ?></a>
          <?php endif; ?>
        </div>
        <div class="time"><?= e($l['user_name'] ?? 'System') ?> · <?= date('d M Y, h:i A', strtotime($l['created_at'])) ?></div>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$logs): ?>
    <p class="text-muted text-center py-4 mb-0">No activity recorded yet.</p>
  <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-3">
  <ul class="pagination pagination-sm justify-content-center">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <li class="page-item <?= $i == $page ? 'active' : '' ?>">
        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
