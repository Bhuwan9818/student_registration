<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT u.*, p.full_name as center_name, p.id as center_id
                        FROM users u LEFT JOIN users p ON p.id = u.parent_user_id
                        WHERE u.id = ? AND u.role = 'staff' AND u.parent_user_id IS NOT NULL");
$stmt->execute([$id]);
$subcenter = $stmt->fetch();

if (!$subcenter) {
    flash('error', 'Sub-center not found.');
    redirect('admin_subcenters.php');
}

$regStmt = $pdo->prepare("SELECT s.*, c.name as course_name, un.name as university_name, sy.year_label,
                           (SELECT status FROM fees f WHERE f.student_id = s.id ORDER BY f.id DESC LIMIT 1) as fee_status,
                           (SELECT amount FROM fees f WHERE f.student_id = s.id ORDER BY f.id DESC LIMIT 1) as fee_amount,
                           (SELECT mode FROM fees f WHERE f.student_id = s.id ORDER BY f.id DESC LIMIT 1) as fee_mode
                           FROM students s
                           LEFT JOIN courses c ON c.id = s.course_id
                           LEFT JOIN universities un ON un.id = s.university_id
                           LEFT JOIN sessions_years sy ON sy.id = s.session_id
                           WHERE s.created_by = ?
                           ORDER BY s.created_at DESC");
$regStmt->execute([$id]);
$registrations = $regStmt->fetchAll();

$feeTotals = getFeeTotals($pdo, $id);

// ---- CSV export ----
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="subcenter_' . preg_replace('/[^a-z0-9]+/i', '_', $subcenter['full_name']) . '_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Reg No', 'Type', 'Name', 'Mobile', 'Course', 'University', 'Session', 'Status', 'Fee Amount', 'Fee Mode', 'Fee Channel', 'Fee Status', 'Date']);
    foreach ($registrations as $r) {
        fputcsv($out, [
            $r['registration_no'], ucfirst($r['registration_type']), $r['first_name'] . ' ' . $r['last_name'], $r['mobile'],
            $r['course_name'], $r['university_name'], $r['year_label'], $r['status'],
            $r['fee_amount'] !== null ? $r['fee_amount'] : '', $r['fee_mode'] ?? '',
            $r['fee_mode'] ? (isOnlineMode($r['fee_mode']) ? 'Online' : 'Offline') : '',
            $r['fee_status'] ?? 'not paid', $r['created_at']
        ]);
    }
    fclose($out);
    exit;
}

$pageTitle = 'Sub-Center Report - ' . $subcenter['full_name'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Sub-Centers</span>
    <h4><?= e($subcenter['full_name']) ?></h4>
  </div>
  <div class="d-flex gap-2">
    <a href="?id=<?= $id ?>&export=csv" class="btn btn-gold btn-sm"><i class="fa-solid fa-file-arrow-down"></i> Download Report (CSV)</a>
    <a href="admin_subcenters.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Sub-Centers</a>
  </div>
</div>

<div class="alert alert-light border small mb-3">
  <i class="fa-solid fa-sitemap text-muted me-1"></i> Working under
  <a href="center_detail.php?id=<?= $subcenter['center_id'] ?>"><strong><?= e($subcenter['center_name']) ?></strong></a>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-4">
    <div class="stat-card">
      <i class="fa-solid fa-users stat-icon"></i>
      <div class="stat-label">Total Registrations</div>
      <div class="stat-value"><?= count($registrations) ?></div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card">
      <i class="fa-solid fa-wifi stat-icon"></i>
      <div class="stat-label">Online Fees</div>
      <div class="stat-value" style="font-size:1.5rem;">₹<?= number_format($feeTotals['online_total'], 2) ?></div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-card">
      <i class="fa-solid fa-money-bill-wave stat-icon"></i>
      <div class="stat-label">Offline Fees</div>
      <div class="stat-value" style="font-size:1.5rem;">₹<?= number_format($feeTotals['offline_total'], 2) ?></div>
    </div>
  </div>
</div>

<div class="table-card p-3">
  <div class="section-title mb-3">All Registrations by <?= e($subcenter['full_name']) ?></div>
  <div class="table-responsive">
    <table class="table table-sm table-ledger align-middle">
      <thead>
        <tr><th>Reg No</th><th>Type</th><th>Name</th><th>Course</th><th>Status</th><th>Fee</th><th>Channel</th><th>Date</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($registrations as $r): ?>
        <tr>
          <td class="reg-no"><?= e($r['registration_no']) ?></td>
          <td><span class="badge bg-<?= $r['registration_type'] == 'fresh' ? 'primary' : 'info' ?>"><?= $r['registration_type'] == 'fresh' ? 'Fresh' : 'Re-Reg' ?></span></td>
          <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
          <td><?= e($r['course_name'] ?? '-') ?></td>
          <td><?= statusBadge($r['status']) ?></td>
          <td><?= $r['fee_status'] ? statusBadge($r['fee_status']) : '<span class="badge bg-light text-dark border">Not Paid</span>' ?></td>
          <td><?= $r['fee_mode'] ? (isOnlineMode($r['fee_mode']) ? 'Online' : 'Offline') : '-' ?></td>
          <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
          <td><a href="student_detail.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$registrations): ?>
          <tr><td colspan="9" class="text-center text-muted py-4">No registrations yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
