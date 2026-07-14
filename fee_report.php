<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$scope   = ($_GET['scope'] ?? 'center') === 'subcenter' ? 'subcenter' : 'center';
$channel = ($_GET['channel'] ?? 'online') === 'offline' ? 'offline' : 'online';

$onlineModes  = "'Online','UPI','Card'";
$offlineModes = "'Cash','Cheque'";
$modeFilter = $channel === 'online' ? $onlineModes : $offlineModes;
$scopeFilter = $scope === 'center' ? 'IS NULL' : 'IS NOT NULL';

$where = ["u.parent_user_id $scopeFilter", "f.mode IN ($modeFilter)"];
$params = [];

if (!empty($_GET['date_from'])) { $where[] = 'DATE(f.submitted_at) >= ?'; $params[] = $_GET['date_from']; }
if (!empty($_GET['date_to']))   { $where[] = 'DATE(f.submitted_at) <= ?'; $params[] = $_GET['date_to']; }
if (!empty($_GET['status']))    { $where[] = 'f.status = ?'; $params[] = $_GET['status']; }
if (!empty($_GET['q'])) {
    $where[] = "(s.registration_no LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ? OR u.full_name LIKE ?)";
    $like = '%' . $_GET['q'] . '%';
    array_push($params, $like, $like, $like, $like);
}
$whereSql = 'WHERE ' . implode(' AND ', $where);

$sql = "SELECT f.*, s.registration_no, s.first_name, s.last_name,
               u.full_name as owner_name, u.id as owner_id, pu.full_name as center_name
        FROM fees f
        JOIN students s ON s.id = f.student_id
        JOIN users u ON u.id = s.created_by
        LEFT JOIN users pu ON pu.id = u.parent_user_id
        $whereSql
        ORDER BY f.submitted_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$fees = $stmt->fetchAll();

$total = array_sum(array_column($fees, 'amount'));

// ---- CSV export ----
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $scope . '_' . $channel . '_fees_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    $headerRow = ['Reg No', 'Student', $scope === 'subcenter' ? 'Sub-Center' : 'Center', 'Amount', 'Mode', 'UTR/Reference', 'Status', 'Date'];
    if ($scope === 'subcenter') { $headerRow[] = 'Under Center'; }
    fputcsv($out, $headerRow);
    foreach ($fees as $f) {
        $row = [
            $f['registration_no'], $f['first_name'] . ' ' . $f['last_name'], $f['owner_name'],
            $f['amount'], $f['mode'], $f['utr_no'], $f['status'], $f['submitted_at']
        ];
        if ($scope === 'subcenter') { $row[] = $f['center_name']; }
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

$scopeLabel = $scope === 'center' ? 'Center' : 'Sub-Center';
$pageTitle = ucfirst($channel) . ' Fee — ' . $scopeLabel . 's';

// Preserve current filters for export link
$exportQs = $_GET;
$exportQs['export'] = 'csv';

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow"><?= e($scopeLabel) ?>s</span>
    <h4><?= ucfirst($channel) ?> Fee</h4>
  </div>
  <a href="?<?= http_build_query($exportQs) ?>" class="btn btn-gold btn-sm"><i class="fa-solid fa-file-arrow-down"></i> Export CSV</a>
</div>

<form method="GET" class="table-card p-3 mb-3">
  <input type="hidden" name="scope" value="<?= e($scope) ?>">
  <input type="hidden" name="channel" value="<?= e($channel) ?>">
  <div class="row g-2">
    <div class="col-md-3">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name / reg no / <?= strtolower($scopeLabel) ?>" value="<?= e($_GET['q'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <select name="status" class="form-select form-select-sm">
        <option value="">All Status</option>
        <option value="pending" <?= (($_GET['status'] ?? '') == 'pending') ? 'selected' : '' ?>>Pending</option>
        <option value="verified" <?= (($_GET['status'] ?? '') == 'verified') ? 'selected' : '' ?>>Verified</option>
        <option value="rejected" <?= (($_GET['status'] ?? '') == 'rejected') ? 'selected' : '' ?>>Rejected</option>
      </select>
    </div>
    <div class="col-md-2">
      <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($_GET['date_from'] ?? '') ?>" title="From date">
    </div>
    <div class="col-md-2">
      <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($_GET['date_to'] ?? '') ?>" title="To date">
    </div>
    <div class="col-md-1">
      <button class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-filter"></i></button>
    </div>
    <div class="col-md-2">
      <a href="?scope=<?= e($scope) ?>&channel=<?= e($channel) ?>" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
    </div>
  </div>
</form>

<div class="table-card p-3">
  <div class="d-flex justify-content-between mb-2">
    <span class="text-muted small"><?= count($fees) ?> record(s)</span>
    <span class="small">Total: <strong>₹<?= number_format($total, 2) ?></strong></span>
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-ledger align-middle">
      <thead>
        <tr>
          <th>Reg No</th><th>Student</th><th><?= e($scopeLabel) ?></th>
          <?php if ($scope === 'subcenter'): ?><th>Under Center</th><?php endif; ?>
          <th>Amount</th><th>Mode</th><th>Reference</th><th>Status</th><th>Date</th><th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($fees as $f): ?>
        <tr>
          <td class="reg-no"><?= e($f['registration_no']) ?></td>
          <td><?= e($f['first_name'] . ' ' . $f['last_name']) ?></td>
          <td>
            <?php if ($scope === 'center'): ?>
              <a href="center_detail.php?id=<?= $f['owner_id'] ?>"><?= e($f['owner_name']) ?></a>
            <?php else: ?>
              <a href="subcenter_detail.php?id=<?= $f['owner_id'] ?>"><?= e($f['owner_name']) ?></a>
            <?php endif; ?>
          </td>
          <?php if ($scope === 'subcenter'): ?><td><?= e($f['center_name']) ?></td><?php endif; ?>
          <td>₹<?= number_format($f['amount'], 2) ?></td>
          <td><?= e($f['mode']) ?></td>
          <td><?= e($f['utr_no'] ?: '-') ?></td>
          <td><?= statusBadge($f['status']) ?></td>
          <td><?= date('d M Y', strtotime($f['submitted_at'])) ?></td>
          <td><a href="student_detail.php?id=<?= $f['student_id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$fees): ?>
          <tr><td colspan="<?= $scope === 'subcenter' ? 10 : 9 ?>" class="text-center text-muted py-4">No <?= $channel ?> fee records found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
