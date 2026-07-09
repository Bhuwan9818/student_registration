<?php
require_once __DIR__ . '/config/config.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin_dashboard.php' : 'staff_dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] !== 'active') {
            $error = 'Your account has been disabled. Contact admin.';
        } else {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            if (empty($_SESSION['active_university_id'])) {
                redirect('choose_university.php');
            }
            redirect($user['role'] === 'admin' ? 'admin_dashboard.php' : 'staff_dashboard.php');
        }
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - VS Academy Admission Portal</title>
<link rel="icon" type="image/png" href="assets/img/logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="login-wrapper">
  <div class="login-card">
    <div class="text-center mb-4">
      <img src="assets/img/logo.png" alt="VS Academy" style="width:90px; height:auto; margin-bottom:8px;">
      <h4 class="mt-1 mb-0">VS Academy</h4>
      <div style="font-size:.7rem; text-transform:uppercase; letter-spacing:.1em; color:#C79A42; font-weight:600;">Admission Portal</div>
      <small class="text-muted d-block mt-1">Sign in to manage admissions</small>
    </div>
    <?php if ($error): ?>
      <div class="alert alert-danger py-2 small"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="mb-3">
        <label class="form-label small fw-semibold">Username</label>
        <input type="text" name="username" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label small fw-semibold">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100 py-2">Sign In</button>
    </form>
    <p class="text-center text-muted mt-3 small mb-0">Default admin: admin / Admin@123</p>
  </div>
</div>
</body>
</html>
