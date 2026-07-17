<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$pageTitle = 'Change University';
$return = $_GET['return'] ?? '';
// Only allow relative redirects within the app (avoid open-redirect)
if (!preg_match('#^/?[a-zA-Z0-9_\-]+\.php#', ltrim(parse_url($return, PHP_URL_PATH) ?? '', '/'))) {
    $return = '';
}

// ---- Handle selection ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['university_id'])) {
    $uid = (int)$_POST['university_id'];
    $check = $pdo->prepare("SELECT id, name FROM universities WHERE id = ? AND status = 'active'");
    $check->execute([$uid]);
    $uni = $check->fetch();

    if ($uni) {
        $_SESSION['active_university_id'] = $uni['id'];
        flash('success', 'Now working with ' . $uni['name'] . '.');
    } else {
        flash('error', 'That university is not available.');
    }

    $dest = $_POST['return'] ?: (isAdmin() ? 'admin_dashboard.php' : 'staff_dashboard.php');
    redirect($dest);
}

$universities = $pdo->query("SELECT u.*,
                              (SELECT COUNT(*) FROM courses c WHERE c.university_id = u.id AND c.status='active') as course_count
                              FROM universities u WHERE u.status = 'active' ORDER BY u.name")->fetchAll();

$noUniversitiesAtAll = $pdo->query("SELECT COUNT(*) FROM universities")->fetchColumn() == 0;

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Session Context</span>
    <h4>Select a University</h4>
  </div>
</div>

<?php if ($noUniversitiesAtAll): ?>
  <div class="table-card p-4">
    <p class="mb-3">No universities have been set up yet.</p>
    <?php if (isAdmin()): ?>
      <a href="admin_master.php" class="btn btn-primary btn-sm">Go to Master Data to add one</a>
    <?php else: ?>
      <p class="text-muted small mb-0">Please ask your admin to add a university before you can register students.</p>
    <?php endif; ?>
  </div>
<?php else: ?>

<p class="text-muted small mb-3">Everything you see next — courses, seat availability, dashboard figures, and the registration form — will reflect whichever university you pick here.</p>

<div class="row g-3">
  <?php foreach ($universities as $u): ?>
  <div class="col-6 col-md-4 col-lg-3">
    <form method="POST" class="h-100">
      <input type="hidden" name="university_id" value="<?= $u['id'] ?>">
      <input type="hidden" name="return" value="<?= e($return) ?>">
      <button type="submit" class="uni-pick-card">
        <?php if (($_SESSION['active_university_id'] ?? null) == $u['id']): ?>
          <span class="badge bg-success uni-pick-current">Current</span>
        <?php endif; ?>
        <?php if ($u['logo_path']): ?>
          <img src="<?= e($u['logo_path']) ?>" alt="" class="uni-pick-logo">
        <?php else: ?>
          <span class="uni-pick-logo uni-pick-logo-fallback"><i class="fa-solid fa-building-columns"></i></span>
        <?php endif; ?>
        <div class="uni-pick-name"><?= e($u['name']) ?></div>
        <div class="uni-pick-meta"><?= $u['course_count'] ?> active course(s)</div>
      </button>
    </form>
  </div>
  <?php endforeach; ?>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
