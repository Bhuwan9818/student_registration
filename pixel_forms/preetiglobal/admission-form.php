<?php
/**
 * Dr. Preeti Global University, Dinara, Dist. Shivpuri (M.P.) — Registration Form
 * Pixel-accurate recreation of the official paper form.
 *
 * $student is expected to be an associative array with keys matching
 * the admission_portal `students` table (plus joined course/university
 * names). Replace the sample array below with your real DB fetch, e.g.:
 *
 *   $stmt = $pdo->prepare("SELECT s.*, c.name AS course_name, un.name AS university_name
 *                          FROM students s
 *                          LEFT JOIN courses c ON c.id = s.course_id
 *                          LEFT JOIN universities un ON un.id = s.university_id
 *                          WHERE s.id = ?");
 *   $stmt->execute([$_GET['id']]);
 *   $student = $stmt->fetch(PDO::FETCH_ASSOC);
 */

require_once __DIR__ . '/../shared/helpers.php';

if (!isset($student)) {
    // Sample data so this file can be previewed standalone
    $student = [
        's_no'              => '',
        'course_name'       => 'B.A.',
        'session'           => '2026-27',
        'reg_date'          => '',
        'first_name'        => 'RAHUL',
        'last_name'         => 'SHARMA',
        'name_hindi'        => '',
        'dob'               => '2005-08-15',
        'aadhar_no'         => '',
        'father_name'       => 'SURESH SHARMA',
        'occupation'        => 'Business',
        'mother_name'       => 'GEETA SHARMA',
        'religion'          => 'Hindu',
        'address'           => '123 Milan Colony, Central Delhi',
        'mobile'            => '9818404944',
        'alt_mobile'        => '',
        'email'             => 'rahul@example.com',
        'branch_choice_1'   => '',
        'branch_choice_2'   => '',
        'branch_choice_3'   => '',
        'last_class'        => '12th',
        'gender'            => 'Male',
        'pct_10th'          => '',
        'pct_12th'          => '',
        'pct_graduate'      => '',
        'pct_postgraduate'  => '',
        'pct_other'         => '',
        'category'          => 'General',
        'registration_amount' => '',
        'r_no'              => '',
        'r_date'            => '',
        'admission_fees'    => '',
        'enrollment_fees'   => '',
        'exam_fees'         => '',
        'fees_details'      => '',
        'remark'            => '',
        'doc_high_school'   => false,
        'doc_inter'         => false,
        'doc_other_mark'    => false,
        'doc_tc_cc'         => false,
        'doc_migration'     => false,
        'doc_aadhar'        => false,
        'doc_income'        => false,
        'doc_domicile'      => false,
        'doc_caste'         => false,
        'registered_by'     => '',
        'photo_path'        => '',
    ];
}
$candidateName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Registration Form - Dr. Preeti Global University</title>
<link rel="stylesheet" href="../shared/common.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="no-print">
  <button onclick="window.print()">Print / Save as PDF</button>
</div>

<div class="sheet preeti-sheet">
  <div class="watermark"></div>

  <!-- ================= HEADER ================= -->
  <table class="header-table">
    <tr>
      <td class="header-logo"><img src="assets/preeti-logo.jpeg" alt="Dr. Preeti Global University Logo" class="uni-logo"></td>
      <td class="header-center">
        <div class="uni-name">DR. PREETI GLOBAL <span class="uni-name-blue">UNIVERSITY</span></div>
        <div class="uni-sub">DINARA, DISTRICT - SHIVPURI (M.P.)</div>
      </td>
      <td class="header-photo">
        <div class="photo-box">
          <?php if (!empty($student['photo_path'])): ?>
            <img src="<?= v($student['photo_path']) ?>" alt="Photo">
          <?php endif; ?>
        </div>
      </td>
    </tr>
  </table>

  <div class="form-title">REGISTRATION FORM</div>

  <!-- ================= S.NO / COURSE / SESSION / DATE ================= -->
  <div class="field-row">
    <span class="field-label">S. No :</span><span class="dotted-fill short"><?= v($student['s_no'] ?? '') ?></span>
    <span class="field-label">Course :</span><span class="dotted-fill"><?= v($student['course_name'] ?? '') ?></span>
    <span class="field-label">Session :</span><span class="dotted-fill"><?= v($student['session'] ?? '') ?></span>
    <span class="field-label">Date :</span><span class="dotted-fill"><?= v($student['reg_date'] ?? '') ?></span>
  </div>

  <div class="field-row">
    <span class="field-label">NAME :</span><span class="dotted-fill wide"><?= v($candidateName) ?></span>
    <span class="field-label">DOB :</span><span class="dotted-fill"><?= v($student['dob'] ?? '') ?></span>
  </div>

  <div class="field-row">
    <span class="field-label">NAME IN HINDI:</span><span class="dotted-fill wide"><?= v($student['name_hindi'] ?? '') ?></span>
    <span class="field-label">AADHAR No :</span><span class="dotted-fill"><?= v($student['aadhar_no'] ?? '') ?></span>
  </div>

  <div class="field-row">
    <span class="field-label">FATHER'S NAME:</span><span class="dotted-fill wide"><?= v($student['father_name'] ?? '') ?></span>
    <span class="field-label">OCCUPATION :</span><span class="dotted-fill"><?= v($student['occupation'] ?? '') ?></span>
  </div>

  <div class="field-row">
    <span class="field-label">MOTHER'S NAME:</span><span class="dotted-fill wide"><?= v($student['mother_name'] ?? '') ?></span>
    <span class="field-label">RELIGION :</span><span class="dotted-fill"><?= v($student['religion'] ?? '') ?></span>
  </div>

  <div class="field-row">
    <span class="field-label">ADDRESS :</span><span class="dotted-fill full"><?= v($student['address'] ?? '') ?></span>
  </div>

  <div class="field-row">
    <span class="field-label">CONTACT No.</span>
    <span class="small-note">1.</span><span class="dotted-fill"><?= v($student['mobile'] ?? '') ?></span>
    <span class="small-note">2.)</span><span class="dotted-fill"><?= v($student['alt_mobile'] ?? '') ?></span>
  </div>

  <div class="field-row">
    <span class="field-label">EMAIL- ID:</span><span class="dotted-fill full"><?= v($student['email'] ?? '') ?></span>
  </div>

  <div class="field-row">
    <span class="field-label">BRANCH CHOICE</span>
    <span class="small-note">1.</span><span class="dotted-fill"><?= v($student['branch_choice_1'] ?? '') ?></span>
    <span class="small-note">2.</span><span class="dotted-fill"><?= v($student['branch_choice_2'] ?? '') ?></span>
    <span class="small-note">3.</span><span class="dotted-fill"><?= v($student['branch_choice_3'] ?? '') ?></span>
  </div>

  <div class="field-row">
    <span class="field-label">LAST CLASS :</span><span class="dotted-fill"><?= v($student['last_class'] ?? '') ?></span>
    <span class="field-label gender-label">GENDER :</span>
    <span class="inline-check">MALE <?= checkBox($student['gender'] === 'Male') ?></span>
    <span class="inline-check">FEMALE <?= checkBox($student['gender'] === 'Female') ?></span>
  </div>

  <div class="field-row">
    <span class="field-label">10<sup>th</sup>% :</span><span class="dotted-fill xshort"><?= v($student['pct_10th'] ?? '') ?></span>
    <span class="field-label">12<sup>th</sup>% :</span><span class="dotted-fill xshort"><?= v($student['pct_12th'] ?? '') ?></span>
    <span class="field-label">Graduate % :</span><span class="dotted-fill xshort"><?= v($student['pct_graduate'] ?? '') ?></span>
    <span class="field-label">Post Graduate % :</span><span class="dotted-fill xshort"><?= v($student['pct_postgraduate'] ?? '') ?></span>
    <span class="field-label">Other % :</span><span class="dotted-fill xshort"><?= v($student['pct_other'] ?? '') ?></span>
  </div>

  <div class="field-row">
    <span class="field-label">CATEGORY :</span>
    <?php foreach (['General','OBC','SC','ST','PH'] as $cat): ?>
      <span class="inline-check"><?= $cat ?> <?= checkBox(strtolower($student['category'] ?? '') === strtolower($cat)) ?></span>
    <?php endforeach; ?>
  </div>

  <div class="field-row">
    <span class="field-label">REGISTRATION AMOUNT :</span><span class="dotted-fill"><?= v($student['registration_amount'] ?? '') ?></span>
    <span class="field-label">R.No. :</span><span class="dotted-fill xshort"><?= v($student['r_no'] ?? '') ?></span>
    <span class="field-label">Date :</span><span class="dotted-fill"><?= v($student['r_date'] ?? '') ?></span>
  </div>

  <div class="field-row">
    <span class="field-label">ADMISSION FEES :</span><span class="dotted-fill"><?= v($student['admission_fees'] ?? '') ?></span>
    <span class="field-label">ENROLLMENT FEES :</span><span class="dotted-fill"><?= v($student['enrollment_fees'] ?? '') ?></span>
    <span class="field-label">EXAM FEES :</span><span class="dotted-fill"><?= v($student['exam_fees'] ?? '') ?></span>
  </div>

  <div class="field-row">
    <span class="field-label">FEES DETAILS :</span><span class="dotted-fill full"><?= v($student['fees_details'] ?? '') ?></span>
  </div>

  <div class="field-row">
    <span class="field-label">REMARK (IF ANY) :</span><span class="dotted-fill full"><?= v($student['remark'] ?? '') ?></span>
  </div>

  <!-- ================= DOCUMENTS SUBMITTED ================= -->
  <div class="section-line"></div>
  <div class="documents-title">DOCUMENTS SUBMITTED</div>
  <div class="documents-grid">
    <span class="doc-item">1). High School Mark Sheet : <?= checkBox(!empty($student['doc_high_school'])) ?></span>
    <span class="doc-item">2). Inter Mark Sheet : <?= checkBox(!empty($student['doc_inter'])) ?></span>
    <span class="doc-item">3). Other Mark Sheet : <?= checkBox(!empty($student['doc_other_mark'])) ?></span>
    <span class="doc-item">4). T.C. &amp; C.C. (Original) : <?= checkBox(!empty($student['doc_tc_cc'])) ?></span>
    <span class="doc-item">5). Migration (Original) : <?= checkBox(!empty($student['doc_migration'])) ?></span>
    <span class="doc-item">6). Aadhar Card : <?= checkBox(!empty($student['doc_aadhar'])) ?></span>
    <span class="doc-item">7). Income Certificate : <?= checkBox(!empty($student['doc_income'])) ?></span>
    <span class="doc-item">8). Domicile Certificate : <?= checkBox(!empty($student['doc_domicile'])) ?></span>
    <span class="doc-item">9). Caste Certificate : <?= checkBox(!empty($student['doc_caste'])) ?></span>
  </div>

  <!-- ================= FOOTER SIGNATURES ================= -->
  <div class="section-line"></div>
  <div class="signature-row">
    <div class="signature-col">REGISTERED BY<span class="dotted-fill"><?= v($student['registered_by'] ?? '') ?></span></div>
    <div class="signature-col">STUDENT SIGNATURE</div>
    <div class="signature-col">PARENT SIGNATURE</div>
  </div>

</div>
</body>
</html>
