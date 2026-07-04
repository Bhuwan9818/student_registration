<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$pageTitle = 'Fee Verification';

// Handle verify/reject action (also called from student_detail.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fee_action'])) {
    $feeId = (int)$_POST['fee_id'];
    $newStatus = $_POST['fee_action'] === 'verify' ? 'verified' : 'rejected';
    $upd = $pdo->prepare("UPDATE fees SET status = ?, verified_by = ?, verified_at = NOW() WHERE id = ?");
    $upd->execute([$newStatus, $_SESSION['user_id'], $feeId]);
    flash('success', "Fee marked as $newStatus.");
    redirect($_POST['redirect_to'] ?? 'admin_fees.php');
}

$filterStatus = $_GET['status'] ?? 'pending';
$where = '';
$params = [];
if ($filterStatus !== 'all') {
    $where = 'WHERE f.status = ?';
    $params[] = $filterStatus;
}

$stmt = $pdo->prepare("SELECT f.*, s.registration_no, s.first_name, s.last_name, u.full_name as submitted_by_name
                        FROM fees f
                        JOIN students s ON s.id = f.student_id
                        LEFT JOIN users u ON u.id = f.submitted_by
                        $where
                        ORDER BY f.submitted_at DESC");
$stmt->execute($params);
$fees = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Fee Verification</h4>
  <div class="btn-group btn-group-sm">
    <a href="?status=pending" class="btn btn-outline-primary <?= $filterStatus == 'pending' ? 'active' : '' ?>">Pending</a>
    <a href="?status=verified" class="btn btn-outline-primary <?= $filterStatus == 'verified' ? 'active' : '' ?>">Verified</a>
    <a href="?status=rejected" class="btn btn-outline-primary <?= $filterStatus == 'rejected' ? 'active' : '' ?>">Rejected</a>
    <a href="?status=all" class="btn btn-outline-primary <?= $filterStatus == 'all' ? 'active' : '' ?>">All</a>
  </div>
</div>

<div class="table-card bg-white p-3">
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead class="table-light">
        <tr>
          <th>Reg No</th><th>Student</th><th>Amount</th><th>Mode</th><th>Type</th>
          <th>UTR / Proof</th><th>Submitted By</th><th>Status</th><th>Date</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($fees as $f): ?>
        <tr>
          <td><?= e($f['registration_no']) ?></td>
          <td><?= e($f['first_name'] . ' ' . $f['last_name']) ?></td>
          <td>₹<?= number_format($f['amount'], 2) ?></td>
          <td><?= e($f['mode']) ?></td>
          <td><?= e(ucfirst($f['entry_type'])) ?></td>
          <td>
            <?php if ($f['utr_no']): ?><?= e($f['utr_no']) ?><?php endif; ?>
            <?php if ($f['proof_path']): ?> <a href="<?= e($f['proof_path']) ?>" target="_blank">[Proof]</a><?php endif; ?>
            <?php if (!$f['utr_no'] && !$f['proof_path']): ?>-<?php endif; ?>
          </td>
          <td><?= e($f['submitted_by_name']) ?></td>
          <td><?= statusBadge($f['status']) ?></td>
          <td><?= date('d M Y', strtotime($f['submitted_at'])) ?></td>
          <td>
            <?php if ($f['status'] === 'pending'): ?>
            <form method="POST" class="d-flex gap-1">
              <input type="hidden" name="fee_id" value="<?= $f['id'] ?>">
              <input type="hidden" name="redirect_to" value="admin_fees.php?status=<?= e($filterStatus) ?>">
              <button type="submit" name="fee_action" value="verify" class="btn btn-success btn-sm">Verify</button>
              <button type="submit" name="fee_action" value="reject" class="btn btn-danger btn-sm">Reject</button>
            </form>
            <?php else: ?>
              <a href="student_detail.php?id=<?= $f['student_id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$fees): ?>
          <tr><td colspan="10" class="text-center text-muted py-4">No fee records found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
