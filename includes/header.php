<?php
// Expects $pageTitle to be set by the including page
$initials = '';
foreach (explode(' ', trim($_SESSION['full_name'] ?? '')) as $part) {
    $initials .= strtoupper(substr($part, 0, 1));
}
$initials = substr($initials, 0, 2) ?: 'U';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Admission Portal') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="app-wrapper">

  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="mark"><i class="fa-solid fa-graduation-cap"></i></div>
      <div>
        <span class="subtitle">Registrar's Office</span>
        <div class="title">Admission Portal</div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <?php if (isAdmin()): ?>
        <span class="sidebar-section-label">Overview</span>
        <a href="<?= BASE_URL ?>/admin_dashboard.php" class="<?= active('admin_dashboard.php') ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>

        <span class="sidebar-section-label">Admissions</span>
        <a href="<?= BASE_URL ?>/admin_students.php" class="<?= active('admin_students.php') ?>"><i class="fa-solid fa-users"></i> All Registrations</a>
        <a href="<?= BASE_URL ?>/admin_fees.php" class="<?= active('admin_fees.php') ?>"><i class="fa-solid fa-money-check-dollar"></i> Fee Verification</a>
        <a href="<?= BASE_URL ?>/admin_activity.php" class="<?= active('admin_activity.php') ?>"><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</a>

        <span class="sidebar-section-label">Administration</span>
        <a href="<?= BASE_URL ?>/admin_users.php" class="<?= active('admin_users.php') ?>"><i class="fa-solid fa-user-shield"></i> Manage Staff</a>
        <a href="<?= BASE_URL ?>/admin_master.php" class="<?= active('admin_master.php') ?>"><i class="fa-solid fa-sliders"></i> Master Data</a>
      <?php else: ?>
        <span class="sidebar-section-label">Overview</span>
        <a href="<?= BASE_URL ?>/staff_dashboard.php" class="<?= active('staff_dashboard.php') ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>

        <span class="sidebar-section-label">Admissions</span>
        <a href="<?= BASE_URL ?>/register_student.php" class="<?= active('register_student.php') ?>"><i class="fa-solid fa-user-plus"></i> New Registration</a>
        <a href="<?= BASE_URL ?>/my_students.php" class="<?= active('my_students.php') ?>"><i class="fa-solid fa-list-check"></i> My Submissions</a>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>/logout.php" class="logout-link"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
  </aside>

  <!-- Main content -->
  <div class="main-content">
    <header class="topbar">
      <button id="sidebarToggle" class="sidebar-toggle-btn"><i class="fa-solid fa-bars"></i></button>

      <form class="topbar-search" method="GET" action="<?= BASE_URL ?>/<?= isAdmin() ? 'admin_students.php' : 'my_students.php' ?>">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="q" placeholder="Search name, reg no, mobile…">
      </form>

      <div class="ms-auto d-flex align-items-center gap-3">
        <div class="dropdown">
          <button class="btn p-0 border-0 bg-transparent user-chip dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <div class="user-avatar"><?= e($initials) ?></div>
            <div class="d-none d-sm-block text-start">
              <div class="small fw-semibold lh-1"><?= e($_SESSION['full_name'] ?? '') ?></div>
              <div class="text-muted" style="font-size:.72rem;"><?= e(ucfirst($_SESSION['role'] ?? '')) ?></div>
            </div>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/change_password.php"><i class="fa-solid fa-key me-2"></i> Change Password</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </header>
    <main class="page-content">
      <?php if ($msg = flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      <?php endif; ?>
      <?php if ($msg = flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      <?php endif; ?>
