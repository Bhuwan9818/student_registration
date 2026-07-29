<?php
// Master Data has been split into separate pages: admin_courses.php,
// admin_universities.php, and admin_sessions.php. This file is kept only
// so old bookmarks/links to admin_master.php still land somewhere useful.
require_once __DIR__ . '/config/config.php';
requireAdmin();
redirect('admin_courses.php');
