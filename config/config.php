<?php
// ============================================================
// Core configuration: DB connection, session, helper functions
// ============================================================

// ---- Edit these to match your MySQL setup ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'u677586028_admission_db');
define('DB_USER', 'u677586028_vsacademy');
define('DB_PASS', 'Bhuwan.9818');
// -----------------------------------------------

define('BASE_URL', ''); // e.g. '/admission-portal' if hosted in a subfolder

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

// Returns the currently active university row, or null if none is selected
function getActiveUniversity($pdo) {
    if (empty($_SESSION['active_university_id'])) return null;
    $stmt = $pdo->prepare("SELECT * FROM universities WHERE id = ?");
    $stmt->execute([$_SESSION['active_university_id']]);
    return $stmt->fetch() ?: null;
}

// Ensures a university is selected before letting the user proceed;
// otherwise sends them to the picker (which returns them here afterward).
function requireUniversity($pdo) {
    if (empty($_SESSION['active_university_id'])) {
        $return = urlencode($_SERVER['REQUEST_URI'] ?? '');
        redirect('choose_university.php?return=' . $return);
    }
    // Guard against a stale/deleted university id lingering in session
    $stmt = $pdo->prepare("SELECT id FROM universities WHERE id = ? AND status = 'active'");
    $stmt->execute([$_SESSION['active_university_id']]);
    if (!$stmt->fetch()) {
        unset($_SESSION['active_university_id']);
        $return = urlencode($_SERVER['REQUEST_URI'] ?? '');
        redirect('choose_university.php?return=' . $return);
    }
}

// Total semesters for a course, based on its duration (standard 2 semesters/year)
function courseTotalSemesters($course) {
    return max(1, (int)$course['duration_years']) * 2;
}

// Fee amount defined for a specific course + semester, or null if not set
function getSemesterFee($pdo, $courseId, $semesterNo) {
    $stmt = $pdo->prepare("SELECT amount FROM course_fees WHERE course_id = ? AND semester_no = ?");
    $stmt->execute([$courseId, $semesterNo]);
    $val = $stmt->fetchColumn();
    return $val !== false ? $val : null;
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

// Total verified fees collected by a given center/sub-center user, split online vs offline
function getFeeTotals($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT
        COALESCE(SUM(CASE WHEN f.mode IN ('Online','UPI','Card') THEN f.amount ELSE 0 END), 0) as online_total,
        COALESCE(SUM(CASE WHEN f.mode IN ('Cash','Cheque') THEN f.amount ELSE 0 END), 0) as offline_total
        FROM fees f
        JOIN students s ON s.id = f.student_id
        WHERE s.created_by = ? AND f.status = 'verified'");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function isOnlineMode($mode) {
    return in_array($mode, ['Online', 'UPI', 'Card']);
}

// Deletes a student registration along with its dependent records
// (fees, activity log references, and any re-registration links back to it).
// Document type keys used in student_documents.doc_type, with display labels
function documentTypeLabels() {
    return [
        'photo'                 => 'Photo',
        'aadhaar'                => 'Aadhaar Card',
        'student_signature'     => "Student's Signature",
        'parent_signature'      => "Parent's Signature",
        'migration_certificate' => 'Migration Certificate',
        'affidavit'             => 'Affidavit',
        'other_certificate'     => 'Other Certificates',
        'abc_document'          => 'ABC ID Proof',
        'deb_document'          => 'DEB ID Proof',
    ];
}

// Academic level keys used in student_academics.level, with display labels
function academicLevelLabels() {
    return [
        '10th' => 'High School (10th)',
        '12th' => 'Intermediate (12th)',
        'UG'   => 'Undergraduate (UG)',
        'PG'   => 'Postgraduate (PG)',
    ];
}

function deleteStudentRecord($pdo, $studentId) {
    $pdo->prepare("DELETE FROM fees WHERE student_id = ?")->execute([$studentId]);
    $pdo->prepare("DELETE FROM student_academics WHERE student_id = ?")->execute([$studentId]);
    $pdo->prepare("DELETE FROM student_documents WHERE student_id = ?")->execute([$studentId]);
    $pdo->prepare("UPDATE activity_log SET student_id = NULL WHERE student_id = ?")->execute([$studentId]);
    $pdo->prepare("UPDATE students SET parent_student_id = NULL WHERE parent_student_id = ?")->execute([$studentId]);
    $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$studentId]);
}

// Available university-specific print form templates (key => display label)
function printFormTemplateOptions() {
    return [
        ''             => 'Default (generic slip)',
        'sgvu'         => 'Suresh Gyan Vihar University',
        'amity'        => 'Amity University',
        'mangalayatan' => 'Mangalayatan University',
        'svsu'         => 'Swami Vivekanand Subharti University',
    ];
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
