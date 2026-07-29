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
<body class="login-page">
<div class="login-shell">

  <!-- Illustrated brand panel -->
  <div class="login-aside">
    <div class="login-aside-top">
      <img src="assets/img/logo.png" alt="VS Academy">
      <div class="login-aside-brand">VS Academy<small>Admission Portal</small></div>
    </div>

    <div class="login-aside-mid">
      <svg class="login-seal" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <defs>
          <path id="sealRing" d="M 100,100 m -80,0 a 80,80 0 1,1 160,0 a 80,80 0 1,1 -160,0" />
        </defs>
        <circle cx="100" cy="100" r="96" fill="none" stroke="#E8C568" stroke-opacity=".55" stroke-width="1"/>
        <circle cx="100" cy="100" r="80" fill="none" stroke="#E8C568" stroke-opacity=".8" stroke-width="1" stroke-dasharray="1 5"/>
        <text font-family="'IBM Plex Mono', monospace" font-size="10.5" letter-spacing="3" fill="#E8C568" fill-opacity=".85">
          <textPath href="#sealRing" startOffset="0%">VS ACADEMY • ADMISSION REGISTRY • EST. PORTAL • </textPath>
        </text>
        <g transform="translate(100,100)" fill="none" stroke="#E8C568" stroke-width="1.3">
          <polygon points="0,-36 8.5,-8.5 36,0 8.5,8.5 0,36 -8.5,8.5 -36,0 -8.5,-8.5"/>
          <circle r="5" fill="#E8C568" stroke="none"/>
        </g>
      </svg>
      <div>
        <h1 class="login-aside-headline">Admissions, handled with <em>precision</em>.</h1>
        <p class="login-aside-sub">One registry for every university, course, and student file your team manages — sign in to continue.</p>
      </div>
    </div>

    <div class="login-aside-bottom">
      <span>Multi-University Registry</span>
      <span class="dot"></span>
      <span>Staff &amp; Admin Access</span>
    </div>
  </div>

  <!-- Sign-in form -->
  <div class="login-main">
    <div class="login-card">
      <div class="text-center mb-4">
        <div class="login-mark"><i class="fa-solid fa-graduation-cap"></i></div>
        <span class="eyebrow-gold">Admission Portal</span>
        <h4 class="mt-1 mb-0">Welcome back</h4>
        <small class="text-muted d-block mt-1">Sign in to manage admissions</small>
      </div>
      <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><?= e($error) ?></div>
      <?php endif; ?>
      <form method="POST">
        <div class="login-field">
          <label for="username">Username</label>
          <div class="login-input-group">
            <i class="fa-solid fa-user"></i>
            <input type="text" id="username" name="username" required autofocus autocomplete="username">
          </div>
        </div>
        <div class="login-field">
          <label for="password">Password</label>
          <div class="login-input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" id="password" name="password" required autocomplete="current-password">
          </div>
        </div>
        <button type="submit" class="login-submit">Sign In</button>
      </form>
      <div class="login-foot">Access is limited to authorized registrar staff.</div>
    </div>
  </div>

</div>
</body>
</html>
