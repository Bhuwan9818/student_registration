<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$pageTitle = 'Change Password';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current, $user['password'])) {
        flash('error', 'Current password is incorrect.');
    } elseif (strlen($new) < 6) {
        flash('error', 'New password must be at least 6 characters.');
    } elseif ($new !== $confirm) {
        flash('error', 'New password and confirmation do not match.');
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $upd->execute([$hash, $_SESSION['user_id']]);
        flash('success', 'Password updated successfully.');
    }
    redirect('change_password.php');
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Account</span>
    <h4>Change Password</h4>
  </div>
</div>

<div class="table-card p-4" style="max-width:480px;">
  <form method="POST">
    <div class="mb-3">
      <label class="form-label small fw-semibold">Current Password</label>
      <input type="password" name="current_password" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label small fw-semibold">New Password</label>
      <input type="password" name="new_password" class="form-control" minlength="6" required>
    </div>
    <div class="mb-3">
      <label class="form-label small fw-semibold">Confirm New Password</label>
      <input type="password" name="confirm_password" class="form-control" minlength="6" required>
    </div>
    <button type="submit" class="btn btn-primary">Update Password</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
