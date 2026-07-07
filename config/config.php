<?php
// ============================================================
// Core configuration: DB connection, session, helper functions
// ============================================================

// ---- Edit these to match your MySQL setup ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'admission_portal');
define('DB_USER', 'root');
define('DB_PASS', '');
// -----------------------------------------------

define('BASE_URL', '/admission-portal'); // e.g. '/admission-portal' if hosted in a subfolder

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ---------------- Auth helpers ----------------

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/staff_dashboard.php');
        exit;
    }
}

function requireStaff() {
    requireLogin();
    if (isAdmin()) {
        header('Location: ' . BASE_URL . '/admin_dashboard.php');
        exit;
    }
}

// ---------------- General helpers ----------------

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function flash($key, $msg = null) {
    if ($msg !== null) {
        $_SESSION['flash'][$key] = $msg;
        return;
    }
    if (isset($_SESSION['flash'][$key])) {
        $val = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $val;
    }
    return null;
}

// Generates a unique registration number like REG-2026-000123
function generateRegistrationNo($pdo) {
    $year = date('Y');
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM students WHERE registration_no LIKE 'REG-$year-%'");
    $count = $stmt->fetch()['cnt'] + 1;
    return sprintf('REG-%s-%06d', $year, $count);
}

// Handles a single file upload; returns relative path or null
function handleUpload($fileKey, $destFolder, $allowedExt = ['jpg','jpeg','png','pdf']) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $ext = strtolower(pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        return null;
    }
    $newName = uniqid('doc_', true) . '.' . $ext;
    $destPath = __DIR__ . '/../uploads/' . $destFolder . '/' . $newName;
    if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $destPath)) {
        return 'uploads/' . $destFolder . '/' . $newName;
    }
    return null;
}

function active($page) {
    return (basename($_SERVER['PHP_SELF']) === $page) ? 'active' : '';
}

// Records an entry in the activity feed (shown on the admin dashboard)
function logActivity($pdo, $userId, $action, $description, $studentId = null) {
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, student_id, action, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $studentId, $action, $description]);
}

// Returns [filled, total] seats for a course, or [filled, null] if no cap is set
function courseSeatUsage($pdo, $courseId) {
    $stmt = $pdo->prepare("SELECT total_seats FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);
    $total = $stmt->fetchColumn();

    $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM students WHERE course_id = ? AND status != 'rejected'");
    $stmt2->execute([$courseId]);
    $filled = $stmt2->fetchColumn();

    return [$filled, $total !== false ? $total : null];
}

function statusBadge($status) {
    $map = [
        'submitted' => 'secondary',
        'approved'  => 'success',
        'rejected'  => 'danger',
        'pending'   => 'warning',
        'verified'  => 'success',
    ];
    $color = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . ucfirst($status) . '</span>';
}
