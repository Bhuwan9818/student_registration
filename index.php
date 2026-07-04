<?php
require_once __DIR__ . '/config/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}
redirect(isAdmin() ? 'admin_dashboard.php' : 'staff_dashboard.php');
