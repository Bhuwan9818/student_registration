<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$pageTitle = 'Master Data';
$activeUni = getActiveUniversity($pdo);

// ---- Add new master record ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $type = $_POST['type'];
    $name = trim($_POST['name']);

    if ($type === 'course') {
        if (!$activeUni) {
            flash('error', 'Select a university first (top-right) before adding a course.');
            redirect('admin_master.php');
        }
        $duration = (int)($_POST['duration_years'] ?? 1);
        $seats = !empty($_POST['total_seats']) ? (int)$_POST['total_seats'] : null;
        $ins = $pdo->prepare("INSERT INTO courses (university_id, name, duration_years, total_seats) VALUES (?, ?, ?, ?)");
        $ins->execute([$activeUni['id'], $name, $duration, $seats]);
    } elseif ($type === 'university') {
        $logoPath = handleUpload('logo', 'university_logos', ['jpg','jpeg','png','svg','webp']);
        $ins = $pdo->prepare("INSERT INTO universities (name, logo_path) VALUES (?, ?)");
        $ins->execute([$name, $logoPath]);
    } elseif ($type === 'session') {
        $ins = $pdo->prepare("INSERT INTO sessions_years (year_label) VALUES (?)");
        $ins->execute([$name]);
    }
    flash('success', ucfirst($type) . ' added successfully.');
    redirect('admin_master.php');
}

// ---- Edit an existing master record ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item'])) {
    $type = $_POST['type'];
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);

    if ($type === 'course') {
        $duration = (int)($_POST['duration_years'] ?? 1);
        $seats = $_POST['total_seats'] !== '' ? (int)$_POST['total_seats'] : null;
        $upd = $pdo->prepare("UPDATE courses SET name = ?, duration_years = ?, total_seats = ? WHERE id = ?");
        $upd->execute([$name, $duration, $seats, $id]);
    } elseif ($type === 'university') {
        $logoPath = handleUpload('logo', 'university_logos', ['jpg','jpeg','png','svg','webp']);
        if ($logoPath) {
            $upd = $pdo->prepare("UPDATE universities SET name = ?, logo_path = ? WHERE id = ?");
            $upd->execute([$name, $logoPath, $id]);
        } else {
            $upd = $pdo->prepare("UPDATE universities SET name = ? WHERE id = ?");
            $upd->execute([$name, $id]);
        }
    } elseif ($type === 'session') {
        $upd = $pdo->prepare("UPDATE sessions_years SET year_label = ? WHERE id = ?");
        $upd->execute([$name, $id]);
    }
    flash('success', ucfirst($type) . ' updated successfully.');
    redirect('admin_master.php');
}

// ---- Delete a master record (blocked if something still depends on it) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $type = $_POST['type'];
    $id = (int)$_POST['id'];

    if ($type === 'course') {
        $inUse = $pdo->prepare("SELECT COUNT(*) FROM students WHERE course_id = ?");
        $inUse->execute([$id]);
        if ($inUse->fetchColumn() > 0) {
            flash('error', 'Cannot delete this course — students are already registered under it. Disable it instead.');
        } else {
            $pdo->prepare("DELETE FROM course_fees WHERE course_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM courses WHERE id = ?")->execute([$id]);
            flash('success', 'Course deleted.');
        }
    } elseif ($type === 'university') {
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
    } elseif ($type === 'session') {
        $inUse = $pdo->prepare("SELECT COUNT(*) FROM students WHERE session_id = ?");
        $inUse->execute([$id]);
        if ($inUse->fetchColumn() > 0) {
            flash('error', 'Cannot delete this session — students are already registered under it. Disable it instead.');
        } else {
            $pdo->prepare("DELETE FROM sessions_years WHERE id = ?")->execute([$id]);
            flash('success', 'Session deleted.');
        }
    }
    redirect('admin_master.php');
}

// ---- Toggle active/inactive for a master record ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_item'])) {
    $type = $_POST['type'];
    $id = (int)$_POST['id'];
    $table = ['course' => 'courses', 'university' => 'universities', 'session' => 'sessions_years'][$type] ?? null;
    if ($table) {
        $pdo->prepare("UPDATE $table SET status = IF(status='active','inactive','active') WHERE id = ?")->execute([$id]);
        flash('success', 'Status updated.');
    }
    redirect('admin_master.php');
}

$courses = [];
if ($activeUni) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE university_id = ? ORDER BY name");
    $stmt->execute([$activeUni['id']]);
    $courses = $stmt->fetchAll();
}
$universities = $pdo->query("SELECT * FROM universities ORDER BY name")->fetchAll();
$sessionsYrs  = $pdo->query("SELECT * FROM sessions_years ORDER BY year_label DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Administration</span>
    <h4>Master Data</h4>
  </div>
</div>

<div class="row g-3">
  <!-- Courses (scoped to the active university) -->
  <div class="col-lg-4">
    <div class="table-card p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
          <h6 class="mb-0">Courses</h6>
          <div class="text-muted small"><?= $activeUni ? e($activeUni['name']) : 'No university selected' ?></div>
        </div>
        <?php if ($activeUni): ?>
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">+ Add</button>
        <?php endif; ?>
      </div>

      <?php if (!$activeUni): ?>
        <p class="text-muted small mb-0">Select a university from the top-right button to manage its courses.</p>
      <?php else: ?>
      <ul class="list-group list-group-flush">
        <?php foreach ($courses as $c): ?>
        <?php [$filled, $total] = courseSeatUsage($pdo, $c['id']); $pct = $total ? min(100, round($filled / $total * 100)) : 0; ?>
        <li class="list-group-item px-0">
          <div class="d-flex justify-content-between align-items-center">
            <span><?= e($c['name']) ?> <small class="text-muted">(<?= $c['duration_years'] ?> yr / <?= courseTotalSemesters($c) ?> sem)</small></span>
            <span class="d-flex align-items-center gap-1">
              <button type="button" class="btn btn-sm btn-link p-0 text-muted" data-bs-toggle="modal" data-bs-target="#editCourseModal<?= $c['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></button>
              <form method="POST" class="d-inline">
                <input type="hidden" name="type" value="course">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <button type="submit" name="toggle_item" value="1" class="badge border-0 bg-<?= $c['status']=='active'?'success':'secondary' ?>"><?= ucfirst($c['status']) ?></button>
              </form>
              <form method="POST" class="d-inline" onsubmit="return confirm('Delete this course? This cannot be undone.');">
                <input type="hidden" name="type" value="course">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <button type="submit" name="delete_item" value="1" class="btn btn-sm btn-link p-0 text-danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
              </form>
            </span>
          </div>
          <?php if ($total): ?>
            <div class="small text-muted mt-1"><?= $filled ?> / <?= $total ?> seats filled</div>
            <div class="seat-bar"><div class="seat-bar-fill <?= $pct >= 100 ? 'full' : ($pct >= 80 ? 'near' : '') ?>" style="width: <?= $pct ?>%"></div></div>
          <?php else: ?>
            <div class="small text-muted mt-1">No seat limit set</div>
          <?php endif; ?>
          <a href="course_fees.php?course_id=<?= $c['id'] ?>" class="small"><i class="fa-solid fa-sack-dollar"></i> Manage semester fees</a>
        </li>

        <!-- Edit Course Modal -->
        <div class="modal fade" id="editCourseModal<?= $c['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
          <form method="POST">
            <div class="modal-header"><h6 class="modal-title">Edit Course</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <input type="hidden" name="type" value="course">
              <input type="hidden" name="id" value="<?= $c['id'] ?>">
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
          <li class="list-group-item px-0 text-muted small">No courses yet for this university.</li>
        <?php endif; ?>
      </ul>
      <?php endif; ?>
    </div>
  </div>

  <!-- Universities -->
  <div class="col-lg-4">
    <div class="table-card p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Universities</h6>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUniversityModal">+ Add</button>
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($universities as $u): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
          <span class="d-flex align-items-center gap-2">
            <?php if ($u['logo_path']): ?>
              <img src="<?= e($u['logo_path']) ?>" alt="" style="width:28px; height:28px; object-fit:contain; border-radius:4px; border:1px solid var(--border); background:#fff;">
            <?php else: ?>
              <span style="width:28px; height:28px; border-radius:4px; background:var(--canvas); border:1px solid var(--border); display:inline-flex; align-items:center; justify-content:center;"><i class="fa-solid fa-building-columns text-muted" style="font-size:.7rem;"></i></span>
            <?php endif; ?>
            <?= e($u['name']) ?> <?php if ($activeUni && $activeUni['id'] == $u['id']): ?><span class="badge bg-primary">Active</span><?php endif; ?>
          </span>
          <span class="d-flex align-items-center gap-1">
            <button type="button" class="btn btn-sm btn-link p-0 text-muted" data-bs-toggle="modal" data-bs-target="#editUniversityModal<?= $u['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></button>
            <form method="POST" class="d-inline">
              <input type="hidden" name="type" value="university">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button type="submit" name="toggle_item" value="1" class="badge border-0 bg-<?= $u['status']=='active'?'success':'secondary' ?>"><?= ucfirst($u['status']) ?></button>
            </form>
            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this university? This cannot be undone.');">
              <input type="hidden" name="type" value="university">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button type="submit" name="delete_item" value="1" class="btn btn-sm btn-link p-0 text-danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
            </form>
          </span>
        </li>

        <!-- Edit University Modal -->
        <div class="modal fade" id="editUniversityModal<?= $u['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
          <form method="POST" enctype="multipart/form-data">
            <div class="modal-header"><h6 class="modal-title">Edit University</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <input type="hidden" name="type" value="university">
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
              <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.svg,.webp">
            </div>
            <div class="modal-footer"><button type="submit" name="edit_item" value="1" class="btn btn-primary btn-sm">Save Changes</button></div>
          </form>
        </div></div></div>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Sessions -->
  <div class="col-lg-4">
    <div class="table-card p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Sessions / Years</h6>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSessionModal">+ Add</button>
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($sessionsYrs as $sy): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
          <span><?= e($sy['year_label']) ?></span>
          <span class="d-flex align-items-center gap-1">
            <button type="button" class="btn btn-sm btn-link p-0 text-muted" data-bs-toggle="modal" data-bs-target="#editSessionModal<?= $sy['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></button>
            <form method="POST" class="d-inline">
              <input type="hidden" name="type" value="session">
              <input type="hidden" name="id" value="<?= $sy['id'] ?>">
              <button type="submit" name="toggle_item" value="1" class="badge border-0 bg-<?= $sy['status']=='active'?'success':'secondary' ?>"><?= ucfirst($sy['status']) ?></button>
            </form>
            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this session? This cannot be undone.');">
              <input type="hidden" name="type" value="session">
              <input type="hidden" name="id" value="<?= $sy['id'] ?>">
              <button type="submit" name="delete_item" value="1" class="btn btn-sm btn-link p-0 text-danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
            </form>
          </span>
        </li>

        <!-- Edit Session Modal -->
        <div class="modal fade" id="editSessionModal<?= $sy['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
          <form method="POST">
            <div class="modal-header"><h6 class="modal-title">Edit Session/Year</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <input type="hidden" name="type" value="session">
              <input type="hidden" name="id" value="<?= $sy['id'] ?>">
              <label class="form-label">Year Label</label>
              <input type="text" name="name" class="form-control" value="<?= e($sy['year_label']) ?>" required>
            </div>
            <div class="modal-footer"><button type="submit" name="edit_item" value="1" class="btn btn-primary btn-sm">Save Changes</button></div>
          </form>
        </div></div></div>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form method="POST">
    <div class="modal-header"><h6 class="modal-title">Add Course to <?= e($activeUni['name'] ?? '') ?></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input type="hidden" name="type" value="course">
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

<!-- Add University Modal -->
<div class="modal fade" id="addUniversityModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form method="POST" enctype="multipart/form-data">
    <div class="modal-header"><h6 class="modal-title">Add University</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input type="hidden" name="type" value="university">
      <label class="form-label">University Name</label>
      <input type="text" name="name" class="form-control mb-2" required>
      <label class="form-label">University Logo <small class="text-muted">(optional)</small></label>
      <input type="file" name="logo" class="form-control" accept=".jpg,.jpeg,.png,.svg,.webp">
    </div>
    <div class="modal-footer"><button type="submit" name="add_item" value="1" class="btn btn-primary btn-sm">Add</button></div>
  </form>
</div></div></div>

<!-- Add Session Modal -->
<div class="modal fade" id="addSessionModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form method="POST">
    <div class="modal-header"><h6 class="modal-title">Add Session/Year</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input type="hidden" name="type" value="session">
      <label class="form-label">Year Label (e.g. 2027-2028)</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="modal-footer"><button type="submit" name="add_item" value="1" class="btn btn-primary btn-sm">Add</button></div>
  </form>
</div></div></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
