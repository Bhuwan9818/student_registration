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

// ============================================================
// Fee Frequency Helpers (yearly | semester)
// ============================================================

// Resolve the effective fee frequency for a course
// Returns 'yearly' or 'semester' - course overrides university, defaults to 'semester'
function getCourseFeeFrequency($pdo, $courseId) {
    $stmt = $pdo->prepare("
        SELECT c.fee_frequency as course_freq, u.fee_frequency as uni_freq
        FROM courses c
        JOIN universities u ON u.id = c.university_id
        WHERE c.id = ?
    ");
    $stmt->execute([$courseId]);
    $row = $stmt->fetch();
    if (!$row) return 'semester';

    // Course-level setting takes precedence
    if (!empty($row['course_freq'])) return $row['course_freq'];
    // Fall back to university setting
    return $row['uni_freq'] ?? 'semester';
}

// Get fee periods for a course based on its frequency
// Returns array of period numbers (1..N) and labels
function getCourseFeePeriods($pdo, $courseId) {
    $course = getCourse($pdo, $courseId);
    if (!$course) return [];

    $frequency = getCourseFeeFrequency($pdo, $courseId);
    $durationYears = max(1, (int)$course['duration_years']);

    if ($frequency === 'yearly') {
        $periods = [];
        for ($y = 1; $y <= $durationYears; $y++) {
            $periods[] = [
                'number' => $y,
                'label' => "Year $y",
                'year' => $y,
                'semester' => null,
            ];
        }
        return $periods;
    }

    // Semester-based (default)
    $totalSemesters = $durationYears * 2;
    $periods = [];
    for ($s = 1; $s <= $totalSemesters; $s++) {
        $periods[] = [
            'number' => $s,
            'label' => "Semester $s",
            'year' => (int)ceil($s / 2),
            'semester' => $s,
        ];
    }
    return $periods;
}

// Get fee amount for a course + period (works for both yearly and semester)
// For yearly: $periodNo = year number; For semester: $periodNo = semester number
function getCoursePeriodFee($pdo, $courseId, $periodNo) {
    $frequency = getCourseFeeFrequency($pdo, $courseId);

    if ($frequency === 'yearly') {
        $stmt = $pdo->prepare("SELECT amount FROM course_fees WHERE course_id = ? AND semester_no = ?");
        // For yearly, we store fee with semester_no = year_number * 2 - 1 (first semester of year)
        // But we'll use a more explicit approach: store yearly fees with semester_no = year_number
        // Let's use semester_no as the period identifier
        $stmt->execute([$courseId, $periodNo]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : null;
    }

    // Semester-based
    return getSemesterFee($pdo, $courseId, $periodNo);
}

// Get total expected fee for a course (sum of all periods)
function getCourseTotalFee($pdo, $courseId) {
    $periods = getCourseFeePeriods($pdo, $courseId);
    $total = 0;
    foreach ($periods as $p) {
        $fee = getCoursePeriodFee($pdo, $courseId, $p['number']);
        if ($fee !== null) $total += $fee;
    }
    return $total > 0 ? $total : null;
}

// Get label for fee frequency
function getFeeFrequencyLabel($frequency) {
    return $frequency === 'yearly' ? 'Yearly' : 'Semester-wise';
}

// Get period label for display
function getPeriodLabel($pdo, $courseId, $periodNo) {
    $frequency = getCourseFeeFrequency($pdo, $courseId);
    if ($frequency === 'yearly') {
        return "Year $periodNo";
    }
    return "Semester $periodNo";
}

// Helper to get course details
function getCourse($pdo, $courseId) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);
    return $stmt->fetch();
}

// Get university fee frequency
function getUniversityFeeFrequency($pdo, $universityId) {
    $stmt = $pdo->prepare("SELECT fee_frequency FROM universities WHERE id = ?");
    $stmt->execute([$universityId]);
    $val = $stmt->fetchColumn();
    return $val ?? 'semester';
}

// Get courses with their effective fee frequency for a university
function getCoursesWithFrequency($pdo, $universityId) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.fee_frequency as uni_fee_freq
        FROM courses c
        JOIN universities u ON u.id = c.university_id
        WHERE c.university_id = ? AND c.status = 'active'
        ORDER BY c.name
    ");
    $stmt->execute([$universityId]);
    $courses = $stmt->fetchAll();

    foreach ($courses as &$c) {
        $c['effective_fee_frequency'] = $c['fee_frequency'] ?? $c['uni_fee_freq'];
    }
    return $courses;
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
        'dypatil'      => 'DY Patil Deemed to be University',
        'smu'          => 'Sikkim Manipal University (Transcript/Migration)',
        'vgu'          => 'Vivekananda Global University (Profile Form)',
        'preetiglobal' => 'Dr. Preeti Global University',
        'lpu_international' => 'Lovely Professional University (International Applicant Form)',
        'manipal_jaipur'     => 'Manipal University Jaipur (Registration Form UG/PG)',
        'bosse'              => 'Board of Open Schooling & Skill Education (BOSSE)',
        'chandigarh'         => 'Chandigarh University',
        'sabarmati'          => 'Sabarmati University',
        'gla'                => 'GLA University, Mathura',
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

// ============================================================
// Center Fee Sharing Helpers (Revised)
// ============================================================
// sharing_percentage = % discount the center gets on course fee
// e.g., 60% sharing = center pays 40% of course fee

// Get sharing percentage for a center (optionally for a specific course)
// Returns array with sharing_percentage (discount for center)
function getCenterSharing($pdo, $centerId, $courseId = null) {
    if ($courseId) {
        // Try course-specific first
        $stmt = $pdo->prepare("SELECT sharing_percentage FROM center_sharing WHERE center_id = ? AND course_id = ? AND is_active = 1");
        $stmt->execute([$centerId, $courseId]);
        $row = $stmt->fetch();
        if ($row) return ['sharing_percentage' => $row['sharing_percentage']];
    }
    // Fall back to center default (course_id IS NULL)
    $stmt = $pdo->prepare("SELECT sharing_percentage FROM center_sharing WHERE center_id = ? AND course_id IS NULL AND is_active = 1");
    $stmt->execute([$centerId]);
    $row = $stmt->fetch();
    return $row ?: ['sharing_percentage' => 0.00];
}

// Get all sharing configurations for a center (for management UI)
function getAllCenterSharing($pdo, $centerId) {
    $stmt = $pdo->prepare("SELECT cs.*, c.name as course_name FROM center_sharing cs
                            LEFT JOIN courses c ON c.id = cs.course_id
                            WHERE cs.center_id = ? AND cs.is_active = 1
                            ORDER BY cs.course_id IS NULL DESC, c.name");
    $stmt->execute([$centerId]);
    return $stmt->fetchAll();
}

// Save/update sharing configuration for a center
function saveCenterSharing($pdo, $centerId, $sharingPercentage, $courseId = null) {
    // Validate percentage (0-100)
    $sharingPercentage = max(0, min(100, (float)$sharingPercentage));

    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM center_sharing WHERE center_id = ? AND course_id <=> ?");
    $stmt->execute([$centerId, $courseId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $upd = $pdo->prepare("UPDATE center_sharing SET sharing_percentage = ?, updated_at = NOW() WHERE id = ?");
        $upd->execute([$sharingPercentage, $existing['id']]);
    } else {
        $ins = $pdo->prepare("INSERT INTO center_sharing (center_id, course_id, sharing_percentage) VALUES (?, ?, ?)");
        $ins->execute([$centerId, $courseId, $sharingPercentage]);
    }
    return true;
}

// Delete a sharing configuration
function deleteCenterSharing($pdo, $sharingId) {
    $stmt = $pdo->prepare("UPDATE center_sharing SET is_active = 0 WHERE id = ?");
    $stmt->execute([$sharingId]);
    return $stmt->rowCount() > 0;
}

// Calculate the fee amount a center should pay for a student
// Returns array with: course_fee, sharing_percentage, discount_amount (center's discount), center_payable_amount
function calculateCenterFee($pdo, $studentId) {
    // Get student details
    $stmt = $pdo->prepare("SELECT s.*, c.name as course_name FROM students s
                            LEFT JOIN courses c ON c.id = s.course_id
                            WHERE s.id = ?");
    $stmt->execute([$studentId]);
    $student = $stmt->fetch();

    if (!$student) {
        return ['error' => 'Student not found'];
    }

    // Determine the center (parent center if sub-center)
    $centerId = $student['created_by'];
    if (!empty($student['parent_user_id'])) {
        $stmt2 = $pdo->prepare("SELECT parent_user_id FROM users WHERE id = ?");
        $stmt2->execute([$student['created_by']]);
        $parent = $stmt2->fetch();
        if ($parent) $centerId = $parent['parent_user_id'];
    }

    // Get course fee for this student's period (year or semester based on frequency)
    $courseFee = getCoursePeriodFee($pdo, $student['course_id'], $student['semester_no']);
    if ($courseFee === null) {
        $frequency = getCourseFeeFrequency($pdo, $student['course_id']);
        $periodLabel = $frequency === 'yearly' ? 'year' : 'semester';
        return [
            'course_fee' => 0,
            'sharing_percentage' => 0,
            'discount_amount' => 0,
            'center_payable_amount' => 0,
            'error' => "Course fee not set for {$periodLabel} {$student['semester_no']}"
        ];
    }

    // Get sharing percentage for this center/course
    $sharing = getCenterSharing($pdo, $centerId, $student['course_id']);
    $sharingPct = (float)($sharing['sharing_percentage'] ?? 0);

    // Calculate: center pays (100 - sharing%) of course fee
    $discountAmount = round($courseFee * $sharingPct / 100, 2);  // This is the center's discount
    $centerPayableAmount = round($courseFee - $discountAmount, 2);

    $frequency = getCourseFeeFrequency($pdo, $student['course_id']);
    $periodLabel = $frequency === 'yearly' ? 'Year' : 'Semester';

    return [
        'course_fee' => $courseFee,
        'sharing_percentage' => $sharingPct,
        'discount_amount' => $discountAmount,
        'center_payable_amount' => $centerPayableAmount,
        'course_name' => $student['course_name'],
        'period_no' => $student['semester_no'],
        'period_label' => $periodLabel,
        'fee_frequency' => $frequency,
    ];
}

// Calculate fee breakdown for a fee record (for reports)
function calculateFeeSharing($pdo, $feeRecord, $centerId = null) {
    // If centerId not provided, try to get from fee record's student's created_by
    if (!$centerId && !empty($feeRecord['student_id'])) {
        $stmt = $pdo->prepare("SELECT created_by FROM students WHERE id = ?");
        $stmt->execute([$feeRecord['student_id']]);
        $student = $stmt->fetch();
        if ($student) {
            $centerId = $student['created_by'];
            // If this is a sub-center, get the parent center
            if (!empty($student['parent_user_id'])) {
                $stmt2 = $pdo->prepare("SELECT parent_user_id FROM users WHERE id = ?");
                $stmt2->execute([$student['created_by']]);
                $parent = $stmt2->fetch();
                if ($parent) $centerId = $parent['parent_user_id'];
            }
        }
    }

    if (!$centerId) {
        return [
            'course_fee' => $feeRecord['amount'],
            'sharing_percentage' => 0,
            'discount_amount' => 0,
            'center_payable_amount' => $feeRecord['amount'],
            'submitted_amount' => $feeRecord['amount'],
            'period_no' => 0,
            'period_label' => 'Period',
            'fee_frequency' => 'semester',
        ];
    }

    // Get student course/semester
    $stmt = $pdo->prepare("SELECT course_id, semester_no FROM students WHERE id = ?");
    $stmt->execute([$feeRecord['student_id']]);
    $student = $stmt->fetch();

    if (!$student) {
        return [
            'course_fee' => $feeRecord['amount'],
            'sharing_percentage' => 0,
            'discount_amount' => 0,
            'center_payable_amount' => $feeRecord['amount'],
            'submitted_amount' => $feeRecord['amount'],
            'period_no' => 0,
            'period_label' => 'Period',
            'fee_frequency' => 'semester',
        ];
    }

    // Get course fee (expected) using fee frequency
    $courseFee = getCoursePeriodFee($pdo, $student['course_id'], $student['semester_no']);
    if ($courseFee === null) {
        $courseFee = $feeRecord['amount']; // fallback
    }

    $frequency = getCourseFeeFrequency($pdo, $student['course_id']);
    $periodLabel = $frequency === 'yearly' ? 'Year' : 'Semester';

    $sharing = getCenterSharing($pdo, $centerId, $student['course_id']);
    $sharingPct = (float)($sharing['sharing_percentage'] ?? 0);

    $discountAmount = round($courseFee * $sharingPct / 100, 2);
    $centerPayableAmount = round($courseFee - $discountAmount, 2);

    return [
        'course_fee' => $courseFee,
        'sharing_percentage' => $sharingPct,
        'discount_amount' => $discountAmount,
        'center_payable_amount' => $centerPayableAmount,
        'submitted_amount' => $feeRecord['amount'],
        'period_no' => $student['semester_no'],
        'period_label' => $periodLabel,
        'fee_frequency' => $frequency,
    ];
}

// Get fee totals with sharing calculations for a center
function getFeeTotalsWithSharing($pdo, $centerId) {
    // Get all verified fees for students created by this center (and its sub-centers)
    $stmt = $pdo->prepare("SELECT f.*, s.course_id, s.semester_no, s.created_by as student_created_by
                            FROM fees f
                            JOIN students s ON s.id = f.student_id
                            WHERE (s.created_by = ? OR s.created_by IN (SELECT id FROM users WHERE parent_user_id = ?))
                            AND f.status = 'verified'");
    $stmt->execute([$centerId, $centerId]);
    $fees = $stmt->fetchAll();

    $onlineTotal = 0;
    $offlineTotal = 0;
    $discountTotal = 0;
    $netTotal = 0;
    $grossTotal = 0;

    foreach ($fees as $fee) {
        $calc = calculateFeeSharing($pdo, $fee, $centerId);
        $netAmt = $calc['center_payable_amount'];
        $grossTotal += $calc['course_fee'];
        $discountTotal += $calc['discount_amount'];
        $netTotal += $netAmt;

        if (isOnlineMode($fee['mode'])) {
            $onlineTotal += $netAmt;
        } else {
            $offlineTotal += $netAmt;
        }
    }

    return [
        'online_total' => $onlineTotal,
        'offline_total' => $offlineTotal,
        'discount_total' => $discountTotal,
        'net_total' => $netTotal,
        'gross_total' => $grossTotal,
    ];
}

// Get center's sub-centers
function getSubCenters($pdo, $centerId) {
    $stmt = $pdo->prepare("SELECT id, full_name, username, email, status FROM users WHERE parent_user_id = ? ORDER BY full_name");
    $stmt->execute([$centerId]);
    return $stmt->fetchAll();
}

// Get courses for a university (for sharing config dropdown)
function getCoursesForSharing($pdo, $universityId) {
    $stmt = $pdo->prepare("SELECT id, name FROM courses WHERE university_id = ? AND status = 'active' ORDER BY name");
    $stmt->execute([$universityId]);
    return $stmt->fetchAll();
}
