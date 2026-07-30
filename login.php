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
<div class="login-shell<?= $error ? ' is-open' : '' ?>" id="authShell">

  <!-- Sign-in form (revealed as the overlay slides aside) -->
  <div class="auth-form-panel">
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

  <!-- Colored overlay: illustration + welcome copy, slides aside on toggle -->
  <div class="auth-overlay">
    <!-- smooth organic edge, shared by the overlay shape below -->
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
      <defs>
        <!-- default: flush edge (panel spans the full screen, nothing to wave against) -->
        <clipPath id="authEdgeFlush" clipPathUnits="objectBoundingBox">
          <path d="M1,0 L1,1 L0,1 L0,0 Z"/>
        </clipPath>
        <!-- once the panel shrinks aside, its inner edge gets one gentle flowing curve -->
        <clipPath id="authEdgeWave" clipPathUnits="objectBoundingBox">
          <path d="M1,0 L1,1 L0,1 C0,0.78 0.11,0.71 0.11,0.5 C0.11,0.29 0,0.22 0,0 Z"/>
        </clipPath>
      </defs>
    </svg>

    <div class="auth-overlay-shape"></div>

    <div class="auth-overlay-inner">
      <div class="auth-brand">
        <img src="assets/img/logo.png" alt="VS Academy">
        <div class="auth-brand-name">VS Academy<small>Admission Portal</small></div>
      </div>

      <div class="auth-overlay-content">
        <div class="auth-illustration">
          <svg viewBox="0 0 520 560" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
              <linearGradient id="gownGrad" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#173d7a"/>
                <stop offset="1" stop-color="#0A1F47"/>
              </linearGradient>
            </defs>

            <ellipse cx="260" cy="522" rx="150" ry="16" fill="#0A1F47" opacity=".18"/>

            <g>
              <rect x="55" y="472" width="130" height="20" rx="5" fill="#C79A42" transform="rotate(-2 120 482)"/>
              <rect x="68" y="450" width="108" height="20" rx="5" fill="#2E5FA6" transform="rotate(3 122 460)"/>
              <rect x="78" y="429" width="88" height="19" rx="5" fill="#F5F1E6" stroke="#0A1F47" stroke-width="1.5" transform="rotate(-2 122 438)"/>
            </g>

            <g transform="translate(378,278) rotate(-8)">
              <path d="M0,0 L-58,20 L-58,64 L0,46 Z" fill="#F5F1E6" stroke="#0A1F47" stroke-width="2"/>
              <path d="M0,0 L58,20 L58,64 L0,46 Z" fill="#ffffff" stroke="#0A1F47" stroke-width="2"/>
              <line x1="-46" y1="30" x2="-10" y2="22" stroke="#C79A42" stroke-width="2"/>
              <line x1="-46" y1="40" x2="-10" y2="32" stroke="#C79A42" stroke-width="2" opacity=".6"/>
              <line x1="10" y1="22" x2="46" y2="30" stroke="#C79A42" stroke-width="2"/>
              <line x1="10" y1="32" x2="46" y2="40" stroke="#C79A42" stroke-width="2" opacity=".6"/>
            </g>

            <g transform="translate(420,60)">
              <line x1="0" y1="0" x2="0" y2="140" stroke="#E8C568" stroke-width="3"/>
              <path d="M0,4 L64,26 L0,48 Z" fill="#C79A42"/>
            </g>

            <g fill="#E8C568">
              <path d="M120 90 l5 14 14 5 -14 5 -5 14 -5-14 -14-5 14-5 z"/>
              <path d="M440 200 l4 10 10 4 -10 4 -4 10 -4-10 -10-4 10-4 z"/>
              <path d="M70 260 l3 8 8 3 -8 3 -3 8 -3-8 -8-3 8-3 z"/>
              <circle cx="200" cy="60" r="4"/>
              <circle cx="460" cy="340" r="5"/>
              <circle cx="40" cy="150" r="3.5"/>
            </g>

            <g transform="translate(105,150)">
              <circle r="30" fill="#E8C568"/>
              <circle r="30" fill="none" stroke="#0A1F47" stroke-width="2" stroke-dasharray="3 3"/>
              <path d="M-10,26 L-16,58 L0,48 L16,58 L10,26 Z" fill="#0A1F47"/>
              <path d="M0,-12 l4,9 10,1 -7,7 2,10 -9,-5 -9,5 2,-10 -7,-7 10,-1 z" fill="#0A1F47"/>
            </g>

            <g>
              <path d="M244,380 L232,510 L268,510 L262,384 Z" fill="#0A1F47"/>
              <path d="M276,384 L288,510 L252,516 L246,388 Z" fill="#173d7a"/>
              <path d="M226,506 h48 l6,14 h-60 z" fill="#C79A42"/>
              <path d="M248,512 h48 l6,14 h-60 z" fill="#A17F29"/>

              <path d="M232,230
                       C190,250 176,340 168,420
                       C164,452 176,470 200,478
                       L320,478
                       C346,470 358,450 352,418
                       C344,338 330,250 288,230
                       Z" fill="url(#gownGrad)"/>

              <line x1="260" y1="248" x2="260" y2="470" stroke="#E8C568" stroke-width="2.5" opacity=".85"/>
              <circle cx="260" cy="288" r="3" fill="#E8C568"/>
              <circle cx="260" cy="330" r="3" fill="#E8C568"/>
              <circle cx="260" cy="372" r="3" fill="#E8C568"/>
              <circle cx="260" cy="414" r="3" fill="#E8C568"/>

              <path d="M232,232 L260,268 L288,232 Z" fill="#ffffff"/>

              <path d="M292,246
                       C330,224 356,182 372,142
                       C378,128 392,120 402,124
                       C394,132 384,146 378,160
                       C364,196 338,232 304,262
                       Z" fill="#173d7a"/>
              <circle cx="398" cy="120" r="14" fill="#e0a878"/>

              <path d="M228,250
                       C202,266 188,296 186,330
                       C185,340 190,348 200,350
                       C206,326 216,296 236,272
                       Z" fill="#0A1F47"/>
              <circle cx="188" cy="336" r="13" fill="#e0a878"/>
              <g transform="translate(178,338) rotate(-18)">
                <rect x="-6" y="-34" width="12" height="60" rx="6" fill="#F5F1E6" stroke="#C79A42" stroke-width="2"/>
                <rect x="-6" y="-6" width="12" height="10" fill="#C79A42"/>
              </g>

              <circle cx="260" cy="204" r="34" fill="#e9b98c"/>
              <path d="M228,196 C230,172 246,158 260,158 C276,158 292,172 292,196
                       C292,184 282,178 260,178 C238,178 228,186 228,196 Z" fill="#1c2333"/>

              <g transform="translate(400,96) rotate(-18)">
                <polygon points="-38,0 0,-16 38,0 0,16" fill="#0A1F47"/>
                <rect x="-9" y="-2" width="18" height="14" fill="#173d7a"/>
                <line x1="0" y1="-2" x2="22" y2="26" stroke="#E8C568" stroke-width="2.5"/>
                <circle cx="22" cy="28" r="4" fill="#E8C568"/>
              </g>
            </g>
          </svg>
        </div>

        <div class="auth-overlay-text">
          <div class="auth-state-closed">
            <h1 class="auth-headline">Welcome Back</h1>
            <p class="auth-sub">To keep connected with us, please sign in with your admin or staff account.</p>
            <button type="button" class="auth-toggle-btn" id="authOpenBtn">Sign In</button>
          </div>
          <div class="auth-state-open">
            <h1 class="auth-headline">Hello, friend!</h1>
            <p class="auth-sub">Manage universities, courses and student admissions — all in one registry.</p>
            <button type="button" class="auth-back-btn" id="authCloseBtn"><i class="fa-solid fa-arrow-left me-1"></i> Back</button>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
(function () {
  var shell = document.getElementById('authShell');
  var openBtn = document.getElementById('authOpenBtn');
  var closeBtn = document.getElementById('authCloseBtn');

  if (openBtn) {
    openBtn.addEventListener('click', function () {
      shell.classList.add('is-open');
      var u = document.getElementById('username');
      if (u) setTimeout(function () { u.focus(); }, 420);
    });
  }
  if (closeBtn) {
    closeBtn.addEventListener('click', function () {
      shell.classList.remove('is-open');
    });
  }
})();
</script>
</body>
</html>
