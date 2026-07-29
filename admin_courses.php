<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$pageTitle = 'Courses';
$activeUni = getActiveUniversity($pdo);

// All universities, for the dropdown that drives this page
$allUniversities = $pdo->query("SELECT * FROM universities ORDER BY name")->fetchAll();

// ---- Add new course ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $universityId = (int)($_POST['university_id'] ?? 0);
    $name = trim($_POST['name']);
    $duration = (int)($_POST['duration_years'] ?? 1);
    $seats = !empty($_POST['total_seats']) ? (int)$_POST['total_seats'] : null;

    if (!$universityId || $name === '') {
        flash('error', 'University and course name are required.');
    } else {
        $ins = $pdo->prepare("INSERT INTO courses (university_id, name, duration_years, total_seats) VALUES (?, ?, ?, ?)");
        $ins->execute([$universityId, $name, $duration, $seats]);
        flash('success', 'Course added successfully.');
    }
    redirect('admin_courses.php?' . http_build_query(['university_id' => $universityId]));
}

// ---- Edit an existing course ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item'])) {
    $id = (int)$_POST['id'];
    $universityId = (int)($_POST['university_id'] ?? 0);
    $name = trim($_POST['name']);
    $duration = (int)($_POST['duration_years'] ?? 1);
    $seats = $_POST['total_seats'] !== '' ? (int)$_POST['total_seats'] : null;

    $upd = $pdo->prepare("UPDATE courses SET university_id = ?, name = ?, duration_years = ?, total_seats = ? WHERE id = ?");
    $upd->execute([$universityId, $name, $duration, $seats, $id]);
    flash('success', 'Course updated successfully.');
    redirect('admin_courses.php?' . http_build_query(['university_id' => $universityId]));
}

// ---- Delete a course (blocked if students are registered under it) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $id = (int)$_POST['id'];
    $universityId = (int)($_POST['university_id'] ?? 0);

    $inUse = $pdo->prepare("SELECT COUNT(*) FROM students WHERE course_id = ?");
    $inUse->execute([$id]);
    if ($inUse->fetchColumn() > 0) {
        flash('error', 'Cannot delete this course — students are already registered under it. Disable it instead.');
    } else {
        $pdo->prepare("DELETE FROM course_fees WHERE course_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM sub_courses WHERE course_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM courses WHERE id = ?")->execute([$id]);
        flash('success', 'Course deleted.');
    }
    redirect('admin_courses.php?' . http_build_query(['university_id' => $universityId]));
}

// ---- Toggle active/inactive ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_item'])) {
    $id = (int)$_POST['id'];
    $universityId = (int)($_POST['university_id'] ?? 0);
    $pdo->prepare("UPDATE courses SET status = IF(status='active','inactive','active') WHERE id = ?")->execute([$id]);
    flash('success', 'Status updated.');
    redirect('admin_courses.php?' . http_build_query(['university_id' => $universityId]));
}

// ---- Determine which university this page is showing (the "dynamic" part) ----
// Priority: explicit ?university_id= in the URL -> the globally active university -> first university on the list.
if (isset($_GET['university_id']) && $_GET['university_id'] !== '') {
    $selectedUniId = (int)$_GET['university_id'];
} elseif ($activeUni) {
    $selectedUniId = (int)$activeUni['id'];
} elseif (!empty($allUniversities)) {
    $selectedUniId = (int)$allUniversities[0]['id'];
} else {
    $selectedUniId = 0;
}
$selectedUni = null;
foreach ($allUniversities as $u) {
    if ((int)$u['id'] === $selectedUniId) { $selectedUni = $u; break; }
}

// ---- Build the course list for the selected university, with status + search filters ----
$courses = [];
if ($selectedUniId) {
    $where = ['university_id = ?'];
    $params = [$selectedUniId];

    if (!empty($_GET['status'])) {
        $where[] = 'status = ?';
        $params[] = $_GET['status'];
    }
    if (!empty($_GET['q'])) {
        $where[] = 'name LIKE ?';
        $params[] = '%' . $_GET['q'] . '%';
    }

    $sql = "SELECT * FROM courses WHERE " . implode(' AND ', $where) . " ORDER BY name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $courses = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Administration &middot; Master Data</span>
    <h4>Courses</h4>
  </div>
  <?php if ($selectedUniId): ?>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCourseModal"><i class="fa-solid fa-plus"></i> Add Course</button>
  <?php endif; ?>
</div>

<!-- University picker + filters. Changing the University dropdown reloads the -->
<!-- course list for that university — courses are always shown per-university. -->
<form method="GET" class="table-card p-3 mb-3">
  <div class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="form-label small text-muted mb-1">University</label>
      <select name="university_id" class="form-select form-select-sm" onchange="this.form.submit()">
        <?php if (empty($allUniversities)): ?>
          <option value="">No universities yet</option>
        <?php endif; ?>
        <?php foreach ($allUniversities as $u): ?>
          <option value="<?= $u['id'] ?>" <?= $selectedUniId == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?><?= $u['status'] === 'inactive' ? ' (inactive)' : '' ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label small text-muted mb-1">Search</label>
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Course name" value="<?= e($_GET['q'] ?? '') ?>">
    </div>
    <div class="col-md-2">
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
      <a href="admin_courses.php?university_id=<?= $selectedUniId ?>" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
    </div>
  </div>
</form>

<?php if (!$allUniversities): ?>
  <div class="table-card p-4 text-center text-muted">
    No universities yet. <a href="admin_universities.php">Add a university</a> first, then come back to add its courses.
  </div>
<?php elseif (!$selectedUniId): ?>
  <div class="table-card p-4 text-center text-muted">Select a university above to view its courses.</div>
<?php else: ?>
  <div class="table-card p-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="text-muted small"><?= count($courses) ?> course(s) for <strong><?= e($selectedUni['name'] ?? '') ?></strong></span>
    </div>
    <div class="table-responsive">
      <table class="table table-sm table-ledger align-middle">
        <thead>
          <tr>
            <th>Course</th>
            <th>Duration</th>
            <th>Seats</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($courses as $c): ?>
          <?php [$filled, $total] = courseSeatUsage($pdo, $c['id']); $pct = $total ? min(100, round($filled / $total * 100)) : 0; ?>
          <tr>
            <td><?= e($c['name']) ?></td>
            <td><?= $c['duration_years'] ?> yr / <?= courseTotalSemesters($c) ?> sem</td>
            <td style="min-width:140px;">
              <?php if ($total): ?>
                <div class="small text-muted"><?= $filled ?> / <?= $total ?> filled</div>
                <div class="seat-bar"><div class="seat-bar-fill <?= $pct >= 100 ? 'full' : ($pct >= 80 ? 'near' : '') ?>" style="width: <?= $pct ?>%"></div></div>
              <?php else: ?>
                <span class="text-muted small">Unlimited</span>
              <?php endif; ?>
            </td>
            <td>
              <form method="POST" class="d-inline">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <input type="hidden" name="university_id" value="<?= $selectedUniId ?>">
                <button type="submit" name="toggle_item" value="1" class="badge border-0 bg-<?= $c['status']=='active'?'success':'secondary' ?>"><?= ucfirst($c['status']) ?></button>
              </form>
            </td>
            <td class="text-nowrap">
              <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCourseModal<?= $c['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></button>
              <a href="course_fees.php?course_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Semester fees"><i class="fa-solid fa-sack-dollar"></i></a>
              <a href="sub_courses.php?course_id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Sub-courses"><i class="fa-solid fa-diagram-project"></i></a>
              <form method="POST" class="d-inline" onsubmit="return confirm('Delete this course? This cannot be undone.');">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <input type="hidden" name="university_id" value="<?= $selectedUniId ?>">
                <button type="submit" name="delete_item" value="1" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>

          <!-- Edit Course Modal -->
          <div class="modal fade" id="editCourseModal<?= $c['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
            <form method="POST">
              <div class="modal-header"><h6 class="modal-title">Edit Course</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <label class="form-label">University</label>
                <select name="university_id" class="form-select mb-2" required>
                  <?php foreach ($allUniversities as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $u['id'] == $c['university_id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                  <?php endforeach; ?>
                </select>
                <label class="form-label">Course Name</label>
                <input type="text" name="name" class="form-control mb-2" value="<?= e($c['name']) ?>" required>
                <label class="form-label">Duration (Years)</label>
                <input type="number" name="duration_years" class="form-control mb-2" min="1" max="6" value="<?= e($c['duration_years']) ?>">
                <label class="form-label">Total Seats <small class="text-muted">(blank = unlimited)</small></label>
                <input type="number" name="total_seats" class="form-control" min="1" value="<?= e($c['total_seats']) ?>">
              </div>
              <div class="modal-footer"><button type="submit" name="edit_item" value="1" class="btn btn-primary btn-sm">Save Changes</button></div>
            </form>
          </div></div></div>
          <?php endforeach; ?>
          <?php if (!$courses): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No courses match the selected filters.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form method="POST">
    <div class="modal-header"><h6 class="modal-title">Add Course</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <label class="form-label">University</label>
      <select name="university_id" class="form-select mb-2" required>
        <?php foreach ($allUniversities as $u): ?>
          <option value="<?= $u['id'] ?>" <?= $selectedUniId == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <label class="form-label">Course Name</label>
      <input type="text" name="name" class="form-control mb-2" required>
      <label class="form-label">Duration (Years)</label>
      <input type="number" name="duration_years" class="form-control mb-2" min="1" max="6" value="3">
      <label class="form-label">Total Seats <small class="text-muted">(optional)</small></label>
      <input type="number" name="total_seats" class="form-control" min="1" placeholder="Leave blank for unlimited">
    </div>
    <div class="modal-footer"><button type="submit" name="add_item" value="1" class="btn btn-primary btn-sm">Add</button></div>
  </form>
</div></div></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
