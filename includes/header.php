<?php
// Expects $pageTitle to be set by the including page
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

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <i class="fa-solid fa-graduation-cap"></i>
      <span>Admission Portal</span>
    </div>
    <nav class="sidebar-nav">
      <?php if (isAdmin()): ?>
        <a href="<?= BASE_URL ?>/admin_dashboard.php" class="<?= active('admin_dashboard.php') ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="<?= BASE_URL ?>/admin_students.php" class="<?= active('admin_students.php') ?>"><i class="fa-solid fa-users"></i> All Registrations</a>
        <a href="<?= BASE_URL ?>/admin_fees.php" class="<?= active('admin_fees.php') ?>"><i class="fa-solid fa-money-check-dollar"></i> Fee Verification</a>
        <a href="<?= BASE_URL ?>/admin_users.php" class="<?= active('admin_users.php') ?>"><i class="fa-solid fa-user-shield"></i> Manage Staff</a>
        <a href="<?= BASE_URL ?>/admin_master.php" class="<?= active('admin_master.php') ?>"><i class="fa-solid fa-sliders"></i> Master Data</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/staff_dashboard.php" class="<?= active('staff_dashboard.php') ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
        <a href="<?= BASE_URL ?>/register_student.php" class="<?= active('register_student.php') ?>"><i class="fa-solid fa-user-plus"></i> New Registration</a>
        <a href="<?= BASE_URL ?>/my_students.php" class="<?= active('my_students.php') ?>"><i class="fa-solid fa-list-check"></i> My Submissions</a>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>/logout.php" class="text-danger"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
  </aside>

  <!-- Main content -->
  <div class="main-content">
    <header class="topbar">
      <button id="sidebarToggle" class="btn btn-sm btn-outline-secondary d-md-none"><i class="fa-solid fa-bars"></i></button>
      <div class="ms-auto d-flex align-items-center gap-2">
        <span class="text-muted small"><?= e($_SESSION['full_name'] ?? '') ?></span>
        <span class="badge bg-primary text-uppercase"><?= e($_SESSION['role'] ?? '') ?></span>
      </div>
    </header>
    <main class="page-content">
      <?php if ($msg = flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      <?php endif; ?>
      <?php if ($msg = flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= e($msg) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
      <?php endif; ?>
