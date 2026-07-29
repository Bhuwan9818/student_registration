<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$pageTitle = 'Universities';
$activeUni = getActiveUniversity($pdo);

// ---- Add new university ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $name = trim($_POST['name']);
    $logoPath = handleUpload('logo', 'university_logos', ['jpg','jpeg','png','svg','webp']);
    $formTemplate = $_POST['form_template'] ?: null;
    $ins = $pdo->prepare("INSERT INTO universities (name, logo_path, form_template) VALUES (?, ?, ?)");
    $ins->execute([$name, $logoPath, $formTemplate]);
    flash('success', 'University added successfully.');
    redirect('admin_universities.php');
}

// ---- Edit an existing university ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $logoPath = handleUpload('logo', 'university_logos', ['jpg','jpeg','png','svg','webp']);
    $formTemplate = $_POST['form_template'] ?: null;
    if ($logoPath) {
        $upd = $pdo->prepare("UPDATE universities SET name = ?, logo_path = ?, form_template = ? WHERE id = ?");
        $upd->execute([$name, $logoPath, $formTemplate, $id]);
    } else {
        $upd = $pdo->prepare("UPDATE universities SET name = ?, form_template = ? WHERE id = ?");
        $upd->execute([$name, $formTemplate, $id]);
    }
    flash('success', 'University updated successfully.');
    redirect('admin_universities.php');
}

// ---- Delete a university (blocked if it still has courses/students) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $id = (int)$_POST['id'];
    $courseCount = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE university_id = ?");
    $courseCount->execute([$id]);
    $studentCount = $pdo->prepare("SELECT COUNT(*) FROM students WHERE university_id = ?");
    $studentCount->execute([$id]);
    if ($courseCount->fetchColumn() > 0 || $studentCount->fetchColumn() > 0) {
        flash('error', 'Cannot delete this university — it still has courses or registrations under it. Disable it instead, or remove its courses first.');
    } else {
        $pdo->prepare("DELETE FROM universities WHERE id = ?")->execute([$id]);
        if (($_SESSION['active_university_id'] ?? null) == $id) { unset($_SESSION['active_university_id']); }
        flash('success', 'University deleted.');
    }
    redirect('admin_universities.php');
}

// ---- Toggle active/inactive ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_item'])) {
    $id = (int)$_POST['id'];
    $pdo->prepare("UPDATE universities SET status = IF(status='active','inactive','active') WHERE id = ?")->execute([$id]);
    flash('success', 'Status updated.');
    redirect('admin_universities.php');
}

// ---- Filters ----
$where = [];
$params = [];
if (!empty($_GET['status'])) {
    $where[] = 'status = ?';
    $params[] = $_GET['status'];
}
if (!empty($_GET['q'])) {
    $where[] = 'name LIKE ?';
    $params[] = '%' . $_GET['q'] . '%';
}
$sql = "SELECT * FROM universities" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$universities = $stmt->fetchAll();

// Course count per university (for a quick at-a-glance column)
$courseCounts = [];
foreach ($pdo->query("SELECT university_id, COUNT(*) AS cnt FROM courses GROUP BY university_id") as $row) {
    $courseCounts[$row['university_id']] = $row['cnt'];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Administration &middot; Master Data</span>
    <h4>Universities</h4>
  </div>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUniversityModal"><i class="fa-solid fa-plus"></i> Add University</button>
</div>

<form method="GET" class="table-card p-3 mb-3">
  <div class="row g-2 align-items-end">
    <div class="col-md-4">
      <label class="form-label small text-muted mb-1">Search</label>
      <input type="text" name="q" class="form-control form-control-sm" placeholder="University name" value="<?= e($_GET['q'] ?? '') ?>">
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
      <a href="admin_universities.php" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
    </div>
  </div>
</form>

<div class="table-card p-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <span class="text-muted small"><?= count($universities) ?> universit<?= count($universities) == 1 ? 'y' : 'ies' ?> found</span>
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-ledger align-middle">
      <thead>
        <tr>
          <th>Logo</th>
          <th>University</th>
          <th>Print Template</th>
          <th>Courses</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($universities as $u): ?>
        <tr>
          <td>
            <?php if ($u['logo_path']): ?>
              <img src="<?= e($u['logo_path']) ?>" alt="" style="width:32px; height:32px; object-fit:contain; border-radius:4px; border:1px solid var(--border); background:#fff;">
            <?php else: ?>
              <span style="width:32px; height:32px; border-radius:4px; background:var(--canvas); border:1px solid var(--border); display:inline-flex; align-items:center; justify-content:center;"><i class="fa-solid fa-building-columns text-muted" style="font-size:.7rem;"></i></span>
            <?php endif; ?>
          </td>
          <td>
            <?= e($u['name']) ?>
            <?php if ($activeUni && $activeUni['id'] == $u['id']): ?><span class="badge bg-primary">Active selection</span><?php endif; ?>
          </td>
          <td class="small text-muted"><?= e(printFormTemplateOptions()[$u['form_template'] ?? ''] ?? 'Default (generic slip)') ?></td>
          <td>
            <a href="admin_courses.php?university_id=<?= $u['id'] ?>" class="small"><?= $courseCounts[$u['id']] ?? 0 ?> course(s)</a>
          </td>
          <td>
            <form method="POST" class="d-inline">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button type="submit" name="toggle_item" value="1" class="badge border-0 bg-<?= $u['status']=='active'?'success':'secondary' ?>"><?= ucfirst($u['status']) ?></button>
            </form>
          </td>
          <td class="text-nowrap">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editUniversityModal<?= $u['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></button>
            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this university? This cannot be undone.');">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button type="submit" name="delete_item" value="1" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
            </form>
          </td>
        </tr>

        <!-- Edit University Modal -->
        <div class="modal fade" id="editUniversityModal<?= $u['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
          <form method="POST" enctype="multipart/form-data">
            <div class="modal-header"><h6 class="modal-title">Edit University</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <label class="form-label">University Name</label>
              <input type="text" name="name" class="form-control mb-3" value="<?= e($u['name']) ?>" required>
              <label class="form-label d-block">Current Logo</label>
              <?php if ($u['logo_path']): ?>
                <img src="<?= e($u['logo_path']) ?>" alt="" style="width:48px; height:48px; object-fit:contain; border-radius:6px; border:1px solid var(--border); background:#fff; padding:4px;" class="mb-2">
              <?php else: ?>
                <div class="text-muted small mb-2">No logo uploaded yet.</div>
              <?php endif; ?>
              <label class="form-label">Replace Logo <small class="text-muted">(optional)</small></label>
              <input type="file" name="logo" class="form-control mb-3" accept=".jpg,.jpeg,.png,.svg,.webp">
              <label class="form-label">Printed Admission Form Layout</label>
              <select name="form_template" class="form-select">
                <?php foreach (printFormTemplateOptions() as $key => $label): ?>
                  <option value="<?= e($key) ?>" <?= ($u['form_template'] ?? '') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="modal-footer"><button type="submit" name="edit_item" value="1" class="btn btn-primary btn-sm">Save Changes</button></div>
          </form>
        </div></div></div>
        <?php endforeach; ?>
        <?php if (!$universities): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No universities match the selected filters.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add University Modal -->
<div class="modal fade" id="addUniversityModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form method="POST" enctype="multipart/form-data">
    <div class="modal-header"><h6 class="modal-title">Add University</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <label class="form-label">University Name</label>
      <input type="text" name="name" class="form-control mb-2" required>
      <label class="form-label">University Logo <small class="text-muted">(optional)</small></label>
      <input type="file" name="logo" class="form-control mb-2" accept=".jpg,.jpeg,.png,.svg,.webp">
      <label class="form-label">Printed Admission Form Layout</label>
      <select name="form_template" class="form-select">
        <?php foreach (printFormTemplateOptions() as $key => $label): ?>
          <option value="<?= e($key) ?>"><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="small text-muted mt-1">Controls how the admission form looks when staff/admin print a student's registration for this university.</div>
    </div>
    <div class="modal-footer"><button type="submit" name="add_item" value="1" class="btn btn-primary btn-sm">Add</button></div>
  </form>
</div></div></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
