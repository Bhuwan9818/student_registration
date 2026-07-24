<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT s.*, c.name AS course_name, un.name AS university_name, un.form_template
                        FROM students s
                        LEFT JOIN courses c ON c.id = s.course_id
                        LEFT JOIN universities un ON un.id = s.university_id
                        WHERE s.id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) { die('Record not found.'); }
if (!isAdmin() && $student['created_by'] != $_SESSION['user_id']) { die('You do not have permission to view this record.'); }

$academicsStmt = $pdo->prepare("SELECT * FROM student_academics WHERE student_id = ?");
$academicsStmt->execute([$id]);
$academics = $academicsStmt->fetchAll();

// Re-registrations often don't re-collect academics — fall back to the original record's, same as print_slip.php
if (!$academics && $student['parent_student_id']) {
    $academicsStmt->execute([$student['parent_student_id']]);
    $academics = $academicsStmt->fetchAll();
}

// SVSU is the only layout with a fee-payment section
$fee = null;
if (($student['form_template'] ?? '') === 'svsu') {
    $feeStmt = $pdo->prepare("SELECT * FROM fees WHERE student_id = ? ORDER BY id DESC LIMIT 1");
    $feeStmt->execute([$id]);
    $fee = $feeStmt->fetch();
}

// Only these four university layouts have a pixel-accurate recreation
$pixelForms = ['sgvu', 'amity', 'mangalayatan', 'svsu'];
$folder = in_array($student['form_template'] ?? '', $pixelForms, true) ? $student['form_template'] : null;

if (!$folder) {
    die("No pixel-accurate form is set up for this student's university yet. Set one under Master Data &rarr; Printed Admission Form Layout, or use the regular Print Slip.");
}

// Each admission-form.php is a standalone HTML page that links its CSS/logo with paths relative
// to its own folder (style.css, ../shared/common.css, assets/logo.png) so it can be opened directly
// while previewing. Since this controller lives at the project root, capture its output and inject
// a <base> tag so those relative paths resolve against pixel_forms/<folder>/ instead of the root.
ob_start();
include __DIR__ . '/pixel_forms/' . $folder . '/admission-form.php';
$html = ob_get_clean();

$baseHref = BASE_URL . '/pixel_forms/' . $folder . '/';
$html = preg_replace('/<head>/i', '<head><base href="' . htmlspecialchars($baseHref, ENT_QUOTES) . '">', $html, 1);

echo $html;
