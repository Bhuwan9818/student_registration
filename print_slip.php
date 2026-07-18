<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT s.*, c.name as course_name, un.name as university_name, un.logo_path as university_logo,
                               un.form_template, sy.year_label
                        FROM students s
                        LEFT JOIN courses c ON c.id = s.course_id
                        LEFT JOIN universities un ON un.id = s.university_id
                        LEFT JOIN sessions_years sy ON sy.id = s.session_id
                        WHERE s.id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) { die('Record not found.'); }
if (!isAdmin() && $student['created_by'] != $_SESSION['user_id']) { die('You do not have permission to view this record.'); }

$feeStmt = $pdo->prepare("SELECT * FROM fees WHERE student_id = ? ORDER BY id DESC LIMIT 1");
$feeStmt->execute([$id]);
$fee = $feeStmt->fetch();

$academicLevels = academicLevelLabels();
$academicsStmt = $pdo->prepare("SELECT * FROM student_academics WHERE student_id = ?");
$academicsStmt->execute([$id]);
$academics = $academicsStmt->fetchAll();
$academicsByLevel = [];
foreach ($academics as $a) { $academicsByLevel[$a['level']] = $a; }

$documentTypes = documentTypeLabels();
$documentsStmt = $pdo->prepare("SELECT * FROM student_documents WHERE student_id = ?");
$documentsStmt->execute([$id]);
$documents = $documentsStmt->fetchAll();
$documentsByType = [];
foreach ($documents as $d) { $documentsByType[$d['doc_type']] = $d; }

// Re-registrations often don't re-collect academics/documents — fall back to the original record's
if (!$academics && !$documents && $student['parent_student_id']) {
    $academicsStmt->execute([$student['parent_student_id']]);
    $academics = $academicsStmt->fetchAll();
    foreach ($academics as $a) { $academicsByLevel[$a['level']] = $a; }
    $documentsStmt->execute([$student['parent_student_id']]);
    $documents = $documentsStmt->fetchAll();
    foreach ($documents as $d) { $documentsByType[$d['doc_type']] = $d; }
}

// Pick the right print template for this university, falling back to the generic slip
$templateKey = $student['form_template'] ?? null;
$templateFile = $templateKey ? __DIR__ . '/templates/print_forms/' . basename($templateKey) . '.php' : null;
if (!$templateFile || !file_exists($templateFile)) {
    $templateFile = __DIR__ . '/templates/print_forms/default.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admission Form - <?= e($student['registration_no']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body style="background:#eef0f5; padding:30px 0;">

<div class="text-center mb-3 no-print">
  <button onclick="window.print()" class="btn btn-primary"><i class="fa-solid fa-print"></i> Print This Form</button>
</div>

<?php include $templateFile; ?>

</body>
</html>
