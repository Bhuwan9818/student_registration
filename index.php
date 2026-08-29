<?php
require_once __DIR__ . '/config/config.php';

// Already signed in? Go straight to the right dashboard — the homepage
// below is only for signed-out visitors.
// if (isLoggedIn()) {
//     redirect(isAdmin() ? 'admin_dashboard.php' : 'staff_dashboard.php');
// }

// Live counters for the stats strip. Wrapped defensively so a fresh
// install with an empty/partial DB still renders the page (just as 0s)
// instead of a fatal error. Deliberately public-facing numbers only —
// no internal org-structure counts (centers/sub-centers) here.
$stats = ['universities' => 0, 'students' => 0, 'courses' => 0];
try {
    $stats['universities'] = (int) $pdo->query("SELECT COUNT(*) FROM universities WHERE status = 'active'")->fetchColumn();
    $stats['students']     = (int) $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $stats['courses']      = (int) $pdo->query("SELECT COUNT(*) FROM courses WHERE status = 'active'")->fetchColumn();
} catch (\Throwable $ex) {
    // Leave stats at 0 — page still renders fine.
}

// A handful of active university partners to showcase.
$partners = [];
try {
    $partners = $pdo->query("SELECT name FROM universities WHERE status = 'active' ORDER BY created_at DESC LIMIT 8")->fetchAll();
} catch (\Throwable $ex) {
    // Leave partners empty — the section shows a friendly placeholder.
}

$year = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>VS Academy — Connecting Students with the Right Universities</title>
<meta name="description" content="VS Academy helps students discover, apply to, and get admitted into leading universities — with verified programs, guided registration, and dedicated support.">
<link rel="icon" type="image/png" href="assets/img/logo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
<!-- Homepage-only stylesheet — completely separate from assets/css/style.css
     (the internal app/login stylesheet), so nothing here can affect login,
     the dashboard, or any other page. -->
<link href="assets/css/home.css" rel="stylesheet">
</head>
<body class="vsa-home">

<!-- ================= NAVBAR ================= -->
<nav class="vsa-nav">
  <div class="vsa-container vsa-nav-row">
    <a href="index.php" class="vsa-nav-brand">
      <img src="assets/img/logo.png" alt="VS Academy">
      <span>VS Academy</span>
    </a>
    <div class="vsa-nav-links" id="vsaNavLinks">
      <a href="#how-it-works">How We Help</a>
      <a href="#gallery">Campus</a>
      <a href="#features">Why Us</a>
      <a href="#partners">Universities</a>
      <a href="#contact">Contact</a>
      <a href="login.php" class="vsa-nav-login"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
    </div>
    <button class="vsa-nav-toggle" type="button" id="vsaNavToggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
  </div>
</nav>

<!-- ================= HERO (full-bleed photo) ================= -->
<header class="vsa-hero">
  <div class="vsa-hero-bg"></div>
  <div class="vsa-container vsa-hero-inner">
    <div class="vsa-hero-copy">
      <div class="vsa-eyebrow"><span class="vsa-dot"></span> Student &amp; University Admission Partner</div>
      <h1>Helping students find their <em>right university</em> — every step of the way.</h1>
      <p class="vsa-hero-sub">
        VS Academy partners with leading universities to guide students smoothly from exploring programs
        to confirmed admission — with verified courses, transparent guidance, and support you can count on.
      </p>
      <div class="vsa-hero-actions">
        <a href="#partners" class="vsa-btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Explore Universities</a>
        <a href="#contact" class="vsa-btn-ghost">Get in touch <i class="fa-solid fa-arrow-right"></i></a>
      </div>
      <div class="vsa-hero-note"><i class="fa-solid fa-shield-heart"></i> Verified university partners. Guided registration. Real support, from application to enrollment.</div>
    </div>

    <div class="vsa-hero-visual">
      <div class="vsa-hierarchy">
        <div class="vsa-hierarchy-title">Why Families Trust Us</div>
        <div class="vsa-h-node">
          <div class="vsa-h-icon"><i class="fa-solid fa-building-columns"></i></div>
          <div>
            <div class="vsa-h-title">Verified Universities</div>
            <div class="vsa-h-sub">Every partner institution is vetted before we work with them</div>
          </div>
        </div>
        <div class="vsa-h-connector"></div>
        <div class="vsa-h-node">
          <div class="vsa-h-icon"><i class="fa-solid fa-hands-holding-circle"></i></div>
          <div>
            <div class="vsa-h-title">Guided Admissions</div>
            <div class="vsa-h-sub">Step-by-step support through registration and paperwork</div>
          </div>
        </div>
        <div class="vsa-h-connector"></div>
        <div class="vsa-h-node">
          <div class="vsa-h-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
          <div>
            <div class="vsa-h-title">Transparent Process</div>
            <div class="vsa-h-sub">Clear fee information and real-time status updates</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="vsa-scroll-cue"><i class="fa-solid fa-chevron-down"></i></div>
</header>

<!-- ================= STATS ================= -->
<section class="vsa-stats">
  <div class="vsa-container vsa-stats-row">
    <div class="vsa-stat">
      <div class="vsa-stat-value"><span><?= $stats['universities'] ?>+</span></div>
      <div class="vsa-stat-label">Partner Universities</div>
    </div>
    <div class="vsa-stat">
      <div class="vsa-stat-value"><span><?= number_format($stats['students']) ?></span></div>
      <div class="vsa-stat-label">Students Guided</div>
    </div>
    <div class="vsa-stat">
      <div class="vsa-stat-value"><span><?= $stats['courses'] ?>+</span></div>
      <div class="vsa-stat-label">Programs Offered</div>
    </div>
    <div class="vsa-stat">
      <div class="vsa-stat-value"><span>100%</span></div>
      <div class="vsa-stat-label">Verified Partners</div>
    </div>
  </div>
</section>

<!-- ================= ABOUT SPLIT (photo + copy) ================= -->
<section class="vsa-section">
  <div class="vsa-container">
    <div class="vsa-split">
      <div class="vsa-split-media">
        <img src="https://images.unsplash.com/photo-1591123120675-6f7f1aae0e5b?auto=format&fit=crop&w=1000&q=80" alt="University campus building" loading="lazy">
        <div class="vsa-split-media-badge">
          <div class="vsa-badge-icon"><i class="fa-solid fa-award"></i></div>
          <div>
            <div class="vsa-badge-value"><?= $stats['universities'] ?>+</div>
            <div class="vsa-badge-label">University Partners</div>
          </div>
        </div>
      </div>
      <div class="vsa-split-copy">
        <span class="vsa-tag">Our Mission</span>
        <h2>Making admissions simple — for students and universities alike.</h2>
        <p>Choosing and getting into the right university shouldn't be confusing. VS Academy works closely with a growing network of universities to give students clear options, honest guidance, and hands-on support — while helping our university partners connect with students who are genuinely ready to enrol.</p>
        <ul class="vsa-check-list">
          <li><i class="fa-solid fa-circle-check"></i> A wide, verified network of partner universities across disciplines</li>
          <li><i class="fa-solid fa-circle-check"></i> Step-by-step guidance from application through enrollment</li>
          <li><i class="fa-solid fa-circle-check"></i> Transparent fees and honest updates at every stage</li>
        </ul>
        <a href="#how-it-works" class="vsa-btn-ghost vsa-split-link">See how it works <i class="fa-solid fa-arrow-right"></i></a>
      </div>
    </div>
  </div>
</section>

<!-- ================= HOW WE HELP (student journey) ================= -->
<section class="vsa-section vsa-alt" id="how-it-works">
  <div class="vsa-container">
    <div class="vsa-head">
      <span class="vsa-tag">How We Help</span>
      <h2>From exploring options to walking into your first class</h2>
      <p>A simple, guided path — so applying to university feels straightforward, not overwhelming.</p>
    </div>

    <div class="vsa-how-grid">
      <div class="vsa-how-card">
        <div class="vsa-how-photo">
          <img src="https://images.unsplash.com/photo-1663162550932-f67b561e656f?auto=format&fit=crop&w=700&q=80" alt="Students exploring programs together" loading="lazy">
          <div class="vsa-how-index">01 / DISCOVER</div>
          <div class="vsa-how-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
        </div>
        <div class="vsa-how-body">
          <h3>Explore programs &amp; universities</h3>
          <p>Browse verified courses and institutions that match your interests, goals, and eligibility.</p>
        </div>
      </div>
      <div class="vsa-how-card">
        <div class="vsa-how-photo">
          <img src="https://images.unsplash.com/photo-1606761568499-6d2451b23c66?auto=format&fit=crop&w=700&q=80" alt="Completing an application with guidance" loading="lazy">
          <div class="vsa-how-index">02 / APPLY</div>
          <div class="vsa-how-icon"><i class="fa-solid fa-file-signature"></i></div>
        </div>
        <div class="vsa-how-body">
          <h3>Apply with guided support</h3>
          <p>Our team helps you complete your registration and documentation accurately and on time.</p>
        </div>
      </div>
      <div class="vsa-how-card">
        <div class="vsa-how-photo">
          <img src="https://images.unsplash.com/photo-1627556704290-2b1f5853ff78?auto=format&fit=crop&w=700&q=80" alt="Graduation and enrollment" loading="lazy">
          <div class="vsa-how-index">03 / ENROLL</div>
          <div class="vsa-how-icon"><i class="fa-solid fa-graduation-cap"></i></div>
        </div>
        <div class="vsa-how-body">
          <h3>Get admitted &amp; begin</h3>
          <p>Receive your confirmed admission and start your academic journey with support all the way through.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ================= CAMPUS & LEARNING GALLERY ================= -->
<section class="vsa-section" id="gallery">
  <div class="vsa-container">
    <div class="vsa-head">
      <span class="vsa-tag">Campus &amp; Learning</span>
      <h2>Every admission starts with a place like this</h2>
      <p>A glimpse of the campuses, libraries, and classrooms our students are headed to.</p>
    </div>

    <div class="vsa-gallery">
      <div class="vsa-gallery-item vsa-g-big">
        <img src="https://images.unsplash.com/photo-1627556704290-2b1f5853ff78?auto=format&fit=crop&w=1000&q=80" alt="Graduation ceremony" loading="lazy">
        <span class="vsa-gallery-cap">Graduation Day</span>
      </div>
      <div class="vsa-gallery-item vsa-g-tall">
        <img src="https://images.unsplash.com/photo-1498243691581-b145c3f54a5a?auto=format&fit=crop&w=700&q=80" alt="University library" loading="lazy">
        <span class="vsa-gallery-cap">Libraries</span>
      </div>
      <div class="vsa-gallery-item">
        <img src="https://images.unsplash.com/photo-1663162550932-f67b561e656f?auto=format&fit=crop&w=700&q=80" alt="Students studying together outdoors" loading="lazy">
        <span class="vsa-gallery-cap">Study Groups</span>
      </div>
      <div class="vsa-gallery-item">
        <img src="https://images.unsplash.com/photo-1536925155833-43e9c2b2f499?auto=format&fit=crop&w=700&q=80" alt="Lecture hall" loading="lazy">
        <span class="vsa-gallery-cap">Lecture Halls</span>
      </div>
    </div>
  </div>
</section>

<!-- ================= QUOTE / PHOTO BANNER ================= -->
<section class="vsa-quote-banner">
  <div class="vsa-quote-bg"></div>
  <div class="vsa-quote-content">
    <div class="vsa-quote-mark">&ldquo;</div>
    <h2>Every student deserves a university that fits their ambitions — and every university deserves students who are ready to thrive. We make that connection.</h2>
    <div class="vsa-quote-cite">The VS Academy Promise</div>
  </div>
</section>

<!-- ================= FOR STUDENTS / FOR UNIVERSITIES + FEATURES ================= -->
<section class="vsa-section vsa-alt" id="features">
  <div class="vsa-container">
    <div class="vsa-head">
      <span class="vsa-tag">Who We Serve</span>
      <h2>Built around students, backed by universities</h2>
      <p>Two sides of the same mission — a smoother path for students, a stronger pipeline for our university partners.</p>
    </div>

    <div class="vsa-spotlight-grid">
      <div class="vsa-spotlight-card">
        <div class="vsa-spotlight-bg" style="background-image:url('https://images.unsplash.com/photo-1663162550932-f67b561e656f?auto=format&fit=crop&w=900&q=80');"></div>
        <div class="vsa-spotlight-content">
          <div class="vsa-spotlight-icon"><i class="fa-solid fa-user-graduate"></i></div>
          <h3>For Students</h3>
          <p>Explore verified programs, get personalized guidance, and secure admission to a university that genuinely fits your goals.</p>
        </div>
      </div>
      <div class="vsa-spotlight-card">
        <div class="vsa-spotlight-bg" style="background-image:url('https://images.unsplash.com/photo-1591123120675-6f7f1aae0e5b?auto=format&fit=crop&w=900&q=80');"></div>
        <div class="vsa-spotlight-content">
          <div class="vsa-spotlight-icon"><i class="fa-solid fa-building-columns"></i></div>
          <h3>For Universities</h3>
          <p>Reach motivated, well-prepared students through a trusted admission network and a simplified enrollment process.</p>
        </div>
      </div>
    </div>

    <div class="vsa-feature-grid">
      <div class="vsa-feature-card">
        <div class="vsa-f-icon"><i class="fa-solid fa-building-columns"></i></div>
        <h3>Verified University Network</h3>
        <p>Every partner institution is vetted, so you can explore and apply with confidence.</p>
      </div>
      <div class="vsa-feature-card">
        <div class="vsa-f-icon"><i class="fa-solid fa-user-check"></i></div>
        <h3>Guided Registration</h3>
        <p>Real, step-by-step support through documentation and the application process.</p>
      </div>
      <div class="vsa-feature-card">
        <div class="vsa-f-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
        <h3>Transparent Fee Guidance</h3>
        <p>Clear, upfront information on program fees — no hidden surprises later.</p>
      </div>
      <div class="vsa-feature-card">
        <div class="vsa-f-icon"><i class="fa-solid fa-headset"></i></div>
        <h3>Dedicated Support</h3>
        <p>Real people to help you at every step, from your first question to enrollment day.</p>
      </div>
    </div>
  </div>
</section>

<!-- ================= PARTNERS ================= -->
<section class="vsa-section" id="partners">
  <div class="vsa-container">
    <div class="vsa-head">
      <span class="vsa-tag">Partner Universities</span>
      <h2>Students are getting admitted to institutions like these</h2>
      <p>Every partner university's programs and admission process are available right through us.</p>
    </div>

    <?php if ($partners): ?>
      <div class="vsa-partner-grid">
        <?php foreach ($partners as $p): ?>
          <div class="vsa-partner-card">
            <span class="vsa-pc-avatar"><?= e(strtoupper(substr($p['name'], 0, 1))) ?></span>
            <div>
              <div class="vsa-pc-name"><?= e($p['name']) ?></div>
              <!-- <div class="vsa-pc-tag">Partner University</div> -->
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="vsa-partner-empty">Our partner universities will appear here shortly.</p>
    <?php endif; ?>
  </div>
</section>

<!-- ================= CTA (photo background) ================= -->
<section class="vsa-section vsa-tight vsa-alt" id="contact">
  <div class="vsa-container">
    <div class="vsa-cta">
      <div class="vsa-cta-bg"></div>
      <div class="vsa-cta-text">
        <h2>Ready to begin your admission journey?</h2>
        <p>Whether you're a student exploring your options or a university looking to reach more of them, we'd love to help. Get in touch and let's get started.</p>
      </div>
      <div class="vsa-cta-actions">
        <a href="mailto:info@vsacademyonline.com" class="vsa-btn-primary"><i class="fa-solid fa-envelope"></i> Contact Us</a>
        <a href="tel:+918750113364" class="vsa-btn-ghost vsa-cta-call"><i class="fa-solid fa-phone"></i> +91 8750113364</a>
      </div>
    </div>
  </div>
</section>

<!-- ================= FOOTER ================= -->
<footer class="vsa-footer">
  <div class="vsa-container">
    <div class="vsa-footer-top">
      <div class="vsa-footer-col">
        <div class="vsa-footer-brand">
          <img src="assets/img/logo.png" alt="VS Academy">
          <span>VS Academy</span>
        </div>
        <p>Connecting students with the right universities — with verified programs, guided admissions, and support you can trust.</p>
      </div>
      <div class="vsa-footer-links">
        <h4>Explore</h4>
        <ul>
          <li><a href="#how-it-works">How We Help</a></li>
          <li><a href="#gallery">Campus</a></li>
          <li><a href="#features">Why Us</a></li>
          <li><a href="#partners">Universities</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
      </div>
      <div class="vsa-footer-links">
        <h4>Get In Touch</h4>
        <ul>
          <li><a href="tel:+918750113364"><i class="fa-solid fa-phone"></i> +91 8750113364</a></li>
          <li><a href="mailto:info@vsacademyonline.com"><i class="fa-solid fa-envelope"></i> info@vsacademyonline.com</a></li>
        </ul>
      </div>
    </div>
    <div class="vsa-footer-bottom">
      <span>&copy; <?= e($year) ?> VS Academy. All rights reserved.</span>
      <span>Guiding students to the right university, together with our partners.</span>
    </div>
  </div>
</footer>

<script>
  document.getElementById('vsaNavToggle').addEventListener('click', function () {
    document.getElementById('vsaNavLinks').classList.toggle('vsa-mobile-open');
  });
</script>
</body>
</html>
