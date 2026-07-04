<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$pageTitle = 'Master Data';

// Add new master record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $type = $_POST['type'];
    $name = trim($_POST['name']);

    if ($type === 'course') {
        $duration = (int)($_POST['duration_years'] ?? 1);
        $ins = $pdo->prepare("INSERT INTO courses (name, duration_years) VALUES (?, ?)");
        $ins->execute([$name, $duration]);
    } elseif ($type === 'university') {
        $ins = $pdo->prepare("INSERT INTO universities (name) VALUES (?)");
        $ins->execute([$name]);
    } elseif ($type === 'session') {
        $ins = $pdo->prepare("INSERT INTO sessions_years (year_label) VALUES (?)");
        $ins->execute([$name]);
    }
    flash('success', ucfirst($type) . ' added successfully.');
    redirect('admin_master.php');
}

// Toggle active/inactive for a master record
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

$courses      = $pdo->query("SELECT * FROM courses ORDER BY name")->fetchAll();
$universities = $pdo->query("SELECT * FROM universities ORDER BY name")->fetchAll();
$sessionsYrs  = $pdo->query("SELECT * FROM sessions_years ORDER BY year_label DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<h4 class="mb-3">Master Data</h4>

<div class="row g-3">
  <!-- Courses -->
  <div class="col-lg-4">
    <div class="table-card bg-white p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Courses</h6>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">+ Add</button>
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($courses as $c): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
          <span><?= e($c['name']) ?> <small class="text-muted">(<?= $c['duration_years'] ?> yr)</small></span>
          <form method="POST" class="d-inline">
            <input type="hidden" name="type" value="course">
            <input type="hidden" name="id" value="<?= $c['id'] ?>">
            <button type="submit" name="toggle_item" value="1" class="badge border-0 bg-<?= $c['status']=='active'?'success':'secondary' ?>"><?= ucfirst($c['status']) ?></button>
          </form>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Universities -->
  <div class="col-lg-4">
    <div class="table-card bg-white p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Universities</h6>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUniversityModal">+ Add</button>
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($universities as $u): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
          <span><?= e($u['name']) ?></span>
          <form method="POST" class="d-inline">
            <input type="hidden" name="type" value="university">
            <input type="hidden" name="id" value="<?= $u['id'] ?>">
            <button type="submit" name="toggle_item" value="1" class="badge border-0 bg-<?= $u['status']=='active'?'success':'secondary' ?>"><?= ucfirst($u['status']) ?></button>
          </form>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Sessions -->
  <div class="col-lg-4">
    <div class="table-card bg-white p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Sessions / Years</h6>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSessionModal">+ Add</button>
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($sessionsYrs as $sy): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
          <span><?= e($sy['year_label']) ?></span>
          <form method="POST" class="d-inline">
            <input type="hidden" name="type" value="session">
            <input type="hidden" name="id" value="<?= $sy['id'] ?>">
            <button type="submit" name="toggle_item" value="1" class="badge border-0 bg-<?= $sy['status']=='active'?'success':'secondary' ?>"><?= ucfirst($sy['status']) ?></button>
          </form>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form method="POST">
    <div class="modal-header"><h6 class="modal-title">Add Course</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input type="hidden" name="type" value="course">
      <label class="form-label">Course Name</label>
      <input type="text" name="name" class="form-control mb-2" required>
      <label class="form-label">Duration (Years)</label>
      <input type="number" name="duration_years" class="form-control" min="1" max="6" value="3">
    </div>
    <div class="modal-footer"><button type="submit" name="add_item" value="1" class="btn btn-primary btn-sm">Add</button></div>
  </form>
</div></div></div>

<!-- Add University Modal -->
<div class="modal fade" id="addUniversityModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form method="POST">
    <div class="modal-header"><h6 class="modal-title">Add University</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input type="hidden" name="type" value="university">
      <label class="form-label">University Name</label>
      <input type="text" name="name" class="form-control" required>
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
