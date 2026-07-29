<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$pageTitle = 'Sessions / Years';

// ---- Add new session ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $name = trim($_POST['name']);
    $ins = $pdo->prepare("INSERT INTO sessions_years (year_label) VALUES (?)");
    $ins->execute([$name]);
    flash('success', 'Session added successfully.');
    redirect('admin_sessions.php');
}

// ---- Edit an existing session ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $upd = $pdo->prepare("UPDATE sessions_years SET year_label = ? WHERE id = ?");
    $upd->execute([$name, $id]);
    flash('success', 'Session updated successfully.');
    redirect('admin_sessions.php');
}

// ---- Delete a session (blocked if students are registered under it) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $id = (int)$_POST['id'];
    $inUse = $pdo->prepare("SELECT COUNT(*) FROM students WHERE session_id = ?");
    $inUse->execute([$id]);
    if ($inUse->fetchColumn() > 0) {
        flash('error', 'Cannot delete this session — students are already registered under it. Disable it instead.');
    } else {
        $pdo->prepare("DELETE FROM sessions_years WHERE id = ?")->execute([$id]);
        flash('success', 'Session deleted.');
    }
    redirect('admin_sessions.php');
}

// ---- Toggle active/inactive ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_item'])) {
    $id = (int)$_POST['id'];
    $pdo->prepare("UPDATE sessions_years SET status = IF(status='active','inactive','active') WHERE id = ?")->execute([$id]);
    flash('success', 'Status updated.');
    redirect('admin_sessions.php');
}

// ---- Filters ----
$where = [];
$params = [];
if (!empty($_GET['status'])) {
    $where[] = 'status = ?';
    $params[] = $_GET['status'];
}
if (!empty($_GET['q'])) {
    $where[] = 'year_label LIKE ?';
    $params[] = '%' . $_GET['q'] . '%';
}
$sql = "SELECT * FROM sessions_years" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY year_label DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sessionsYrs = $stmt->fetchAll();

// Student count per session (quick at-a-glance column)
$studentCounts = [];
foreach ($pdo->query("SELECT session_id, COUNT(*) AS cnt FROM students GROUP BY session_id") as $row) {
    $studentCounts[$row['session_id']] = $row['cnt'];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Administration &middot; Master Data</span>
    <h4>Sessions / Years</h4>
  </div>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSessionModal"><i class="fa-solid fa-plus"></i> Add Session</button>
</div>

<form method="GET" class="table-card p-3 mb-3">
  <div class="row g-2 align-items-end">
    <div class="col-md-4">
      <label class="form-label small text-muted mb-1">Search</label>
      <input type="text" name="q" class="form-control form-control-sm" placeholder="e.g. 2027-2028" value="<?= e($_GET['q'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label small text-muted mb-1">Status</label>
      <select name="status" class="form-select form-select-sm">
        <option value="">All Status</option>
        <option value="active" <?= (($_GET['status'] ?? '') == 'active') ? 'selected' : '' ?>>Active</option>
        <option value="inactive" <?= (($_GET['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Inactive</option>
      </select>
    </div>
    <div class="col-md-2">
      <button class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-filter"></i> Filter</button>
    </div>
    <div class="col-md-2">
      <a href="admin_sessions.php" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
    </div>
  </div>
</form>

<div class="table-card p-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <span class="text-muted small"><?= count($sessionsYrs) ?> session(s) found</span>
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-ledger align-middle">
      <thead>
        <tr>
          <th>Year Label</th>
          <th>Students</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sessionsYrs as $sy): ?>
        <tr>
          <td><?= e($sy['year_label']) ?></td>
          <td class="small text-muted"><?= $studentCounts[$sy['id']] ?? 0 ?></td>
          <td>
            <form method="POST" class="d-inline">
              <input type="hidden" name="id" value="<?= $sy['id'] ?>">
              <button type="submit" name="toggle_item" value="1" class="badge border-0 bg-<?= $sy['status']=='active'?'success':'secondary' ?>"><?= ucfirst($sy['status']) ?></button>
            </form>
          </td>
          <td class="text-nowrap">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editSessionModal<?= $sy['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></button>
            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this session? This cannot be undone.');">
              <input type="hidden" name="id" value="<?= $sy['id'] ?>">
              <button type="submit" name="delete_item" value="1" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
            </form>
          </td>
        </tr>

        <!-- Edit Session Modal -->
        <div class="modal fade" id="editSessionModal<?= $sy['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
          <form method="POST">
            <div class="modal-header"><h6 class="modal-title">Edit Session/Year</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <input type="hidden" name="id" value="<?= $sy['id'] ?>">
              <label class="form-label">Year Label</label>
              <input type="text" name="name" class="form-control" value="<?= e($sy['year_label']) ?>" required>
            </div>
            <div class="modal-footer"><button type="submit" name="edit_item" value="1" class="btn btn-primary btn-sm">Save Changes</button></div>
          </form>
        </div></div></div>
        <?php endforeach; ?>
        <?php if (!$sessionsYrs): ?>
          <tr><td colspan="4" class="text-center text-muted py-4">No sessions match the selected filters.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Session Modal -->
<div class="modal fade" id="addSessionModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form method="POST">
    <div class="modal-header"><h6 class="modal-title">Add Session/Year</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <label class="form-label">Year Label (e.g. 2027-2028)</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="modal-footer"><button type="submit" name="add_item" value="1" class="btn btn-primary btn-sm">Add</button></div>
  </form>
</div></div></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
