<?php
/**
 * Suresh Gyan Vihar University — Admission Form
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
 *   $academics = ... (rows from student_academics for this student)
 */

require_once __DIR__ . '/../shared/helpers.php';

if (!isset($student)) {
    // Sample data so this file can be previewed standalone
    $student = [
        'registration_no'   => 'REG-2026-000123',
        'enrollment_no'     => '',
        'course_name'       => 'MBA',
        'specialization'    => 'Marketing',
        'first_name'        => 'RAHUL',
        'last_name'         => 'SHARMA',
        'father_name'       => 'SURESH SHARMA',
        'mother_name'       => 'GEETA SHARMA',
        'gender'            => 'Male',
        'dob'               => '2000-08-15',
        'address'           => '123 Milan Colony',
        'city'              => 'Delhi',
        'state'             => 'Delhi',
        'pincode'           => '110002',
        'district'          => 'Central Delhi',
        'alt_mobile'        => '011-23456789',
        'mobile'            => '9818404944',
        'email'             => 'rahul@example.com',
        'alt_email'         => '',
        'guardian_mobile'   => '',
        'nationality'       => 'Indian',
        'category'          => 'General',
        'employment_status' => 'Unemployed',
        'photo_path'        => '',
    ];
    $academics = [
        ['level' => '10th', 'institution_board' => 'CBSE Board', 'year_of_passing' => '2016', 'percentage' => '88'],
        ['level' => '12th', 'institution_board' => 'CBSE Board', 'year_of_passing' => '2018', 'percentage' => '82'],
    ];
}
$academics = $academics ?? [];

$dobDay = $dobMonth = $dobYear = '';
if (!empty($student['dob'])) {
    [$dobYear, $dobMonth, $dobDay] = array_pad(explode('-', $student['dob']), 3, '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admission Form - Suresh Gyan Vihar University</title>
<link rel="stylesheet" href="../shared/common.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="no-print">
  <button onclick="window.print()">Print / Save as PDF</button>
</div>

<div class="sheet">

  <!-- ================= HEADER ================= -->
  <table class="header-table">
    <tr>
      <td class="header-left">
        <h1 class="uni-name">Suresh Gyan Vihar University</h1>
        <div class="uni-sub">
          Established under UGC U/S 22 of UGC Act 1956 through its notification no. F.9-38/2008(CPP-I)<br>
          dated 1 April 2009.
        </div>
      </td>
      <td class="header-right">
        <img src="assets/logo.png" alt="SGVU Logo" class="uni-logo">
      </td>
    </tr>
  </table>

  <div class="form-title">ADMISSION FORM</div>

  <div class="instructions">
    All entries must be filled by the candidate himself/ herself in capital letters. Put (tick) for Yes, &times; for NO and "NA"
    where not applicable in the box. The application form consists of two pages
  </div>

  <!-- ================= ENROLMENT / COURSE / PROGRAMME ================= -->
  <table class="top-grid">
    <tr>
      <td class="top-grid-fields">

        <div class="field-row">
          <div class="field-label two-line">ENROLMENT N0.<br>(LEAVE BLANK)</div>
          <?= charBoxes($student['enrollment_no'] ?? '', 20) ?>
        </div>

        <div class="field-row">
          <div class="field-label">COURSE CODE</div>
          <?= charBoxes('', 10) ?>
          <div class="field-label inline-label">PROGRAMME</div>
          <div class="dotted-fill"><?= v($student['course_name']) ?></div>
        </div>

        <div class="field-row">
          <div class="field-label">SPECIALIZATION</div>
          <div class="dotted-fill wide"><?= v($student['specialization']) ?></div>
        </div>

      </td>
      <td class="photo-cell">
        <div class="photo-box" style="width:100px;height:120px;">
          <?php if (!empty($student['photo_path'])): ?>
            <img src="<?= v($student['photo_path']) ?>" alt="Photo">
          <?php else: ?>
            Paste box-sized photograph of candidate, duly attached by head of the institution. Do not use pin or stapler.
          <?php endif; ?>
        </div>
        <div class="photo-note">Please enclose four identical photographs along with the application form</div>
        <div class="signature-box" style="width:100px;height:34px;">
          <?php if (!empty($student['signature_path'])): ?>
            <img src="<?= v($student['signature_path']) ?>" alt="Signature">
          <?php endif; ?>
        </div>
        <div class="photo-note">Signature of candidate (in full)</div>
      </td>
    </tr>
  </table>

  <!-- ================= MAX / PLUS / PRO TABLE ================= -->
  <div class="mba-note">For MBA - MAX, PLUS, Pro - Select and One Option Bellow</div>
  <table class="form-table mba-table">
    <tr>
      <th style="width:22%;">MAX</th>
      <th style="width:6%;"></th>
      <th style="width:22%;">PLUS</th>
      <th style="width:6%;"></th>
      <th>PRO (Any one Below)</th>
    </tr>
    <tr>
      <td><?= checkBox() ?> LIFE SKILS</td>
      <td class="or-cell">OR</td>
      <td><?= checkBox() ?> PGP IN STRATEGIC MANAGEMENT</td>
      <td class="or-cell">OR</td>
      <td class="pro-list">
        <?php foreach ([
            'PRO-CERTIFICATE IN HRM','PRO-CERTIFICATE IN MARKETING','PRO-CERTIFICATE IN FINANCE',
            'PRO-CERTIFICATE IN OPERATIONS','PRO-CERTIFICATE IN HEALTHCARE','PRO-CERTIFICATE IN INFORMATION TECHNOLOGY',
            'PRO-CERTIFICATE IN PROJECT MANAGEMENT','PRO-CERTIFICATE IN SUPPLY CHAIN MANAGEMENT'
        ] as $opt): ?>
          <div><?= checkBox() ?> <?= $opt ?></div>
        <?php endforeach; ?>
      </td>
    </tr>
  </table>

  <div class="secondary-note">(As entered in Secondary/ Senior Secondary Certificate)</div>

  <!-- ================= NAME / FATHER / MOTHER ================= -->
  <div class="field-row">
    <div class="field-label wide-label">NAME OF<br>CANDIDATE:</div>
    <?= charBoxes($student['first_name'] . ' ' . $student['last_name'], 32) ?>
  </div>
  <div class="field-row">
    <div class="field-label wide-label">FATHER'S NAME:</div>
    <?= charBoxes($student['father_name'] ?? '', 32) ?>
  </div>
  <div class="field-row">
    <div class="field-label wide-label">MOTHER'S NAME:</div>
    <?= charBoxes($student['mother_name'] ?? '', 32) ?>
  </div>

  <!-- ================= GENDER / DOB ================= -->
  <div class="field-row gender-row">
    <div class="field-label">GENDER:</div>
    <span class="inline-check">Male <?= checkBox($student['gender'] === 'Male') ?></span>
    <span class="inline-check">Female <?= checkBox($student['gender'] === 'Female') ?></span>
    <div class="field-label">DATE OF BIRTH</div>
    <span class="small-note">DD/ MM/ YY</span>
    <?= charBoxes($dobDay, 2) ?>
    <?= charBoxes($dobMonth, 2) ?>
    <?= charBoxes($dobYear, 4) ?>
  </div>

  <!-- ================= ADDRESS BLOCK ================= -->
  <table class="address-grid">
    <tr>
      <td class="address-col">
        <div class="field-label">PERMANENT<br>ADDRESS:</div>
        <?= charBoxes($student['address'] ?? '', 30) ?>
        <?= charBoxes('', 30) ?>
        <div class="field-row pincode-row">
          <?= charBoxes($student['pincode'] ?? '', 8) ?>
          <span class="inline-label">PIN CODE</span>
        </div>
        <div class="underline-row">CITY <span class="dotted-fill"><?= v($student['city']) ?></span> STATE<span class="dotted-fill"><?= v($student['state']) ?></span></div>
        <div class="underline-row">STD CODE <span class="dotted-fill"><?= v('') ?></span></div>
        <div class="underline-row">PH. No. <span class="dotted-fill"><?= v($student['alt_mobile']) ?></span> MOB. No.<span class="dotted-fill"><?= v($student['mobile']) ?></span></div>
        <div class="underline-row">E-MAIL: <span class="dotted-fill"><?= v($student['email']) ?></span></div>
      </td>
      <td class="address-col">
        <div class="field-label">MAILING<br>ADDRESS:</div>
        <?= charBoxes($student['address'] ?? '', 30) ?>
        <?= charBoxes('', 30) ?>
        <div class="field-row pincode-row">
          <?= charBoxes($student['pincode'] ?? '', 8) ?>
          <span class="inline-label">PIN CODE</span>
        </div>
        <div class="underline-row">CITY <span class="dotted-fill"><?= v($student['city']) ?></span> STATE<span class="dotted-fill"><?= v($student['state']) ?></span></div>
        <div class="underline-row">STD CODE <span class="dotted-fill"><?= v($student['district']) ?></span></div>
        <div class="underline-row">PH. No. <span class="dotted-fill"><?= v('') ?></span> MOB. No.<span class="dotted-fill"><?= v($student['guardian_mobile']) ?></span></div>
        <div class="underline-row">E-MAIL: <span class="dotted-fill"><?= v($student['alt_email']) ?></span></div>
      </td>
    </tr>
  </table>
  <div class="secondary-note center">(Any changes in address should be immediately communicated to the University)</div>

  <!-- ================= NATIONALITY / CATEGORY ================= -->
  <div class="field-row">
    <div class="field-label">NATIONALITY</div>
    <span class="inline-check">INDIAN <?= checkBox(strtolower($student['nationality'] ?? '') === 'indian') ?></span>
    <span class="inline-check">OTHERS <?= checkBox(strtolower($student['nationality'] ?? '') !== 'indian' && !empty($student['nationality'])) ?></span>
    <span class="small-note">(specify the name of the country)</span>
  </div>
  <div class="field-row">
    <div class="field-label">CATEGORY</div>
    <?php foreach (['GENERAL','SC','ST','OBC','PH','EX-SERVICEMAN','EMPLOYED','UNEMPLOYED','OTHERS'] as $cat): ?>
      <?php $match = strtoupper($student['category'] ?? '') === $cat || (in_array($cat, ['EMPLOYED','UNEMPLOYED']) && strtoupper($student['employment_status'] ?? '') === $cat); ?>
      <span class="inline-check"><?= $cat ?> <?= checkBox($match) ?></span>
    <?php endforeach; ?>
  </div>
  <div class="field-row">
    <span>HAVE YOU EVER BEEN DEBARRED BY ANY UNIVERSITY/BOARD?</span>
    <span class="inline-check">NO <?= checkBox(true) ?></span>
    <span class="inline-check">YES <?= checkBox(false) ?></span>
    <span class="small-note">If yes, give details</span>
    <span class="dotted-fill"></span>
  </div>

  <!-- ================= PREVIOUS EXAMINATIONS TABLE ================= -->
  <table class="form-table exam-table">
    <tr>
      <th colspan="6" class="exam-title">
        DETAILS OF PREVIOUS EXAMINATIONS PASSED FROM OTHER UNIVERSITY
        <div class="exam-subtitle">(Enclose Duly Attested/ Notarized, Self Attested Photocopies of the previous Mark card/ documents/certifcates)</div>
      </th>
    </tr>
    <tr>
      <th style="width:5%;">S. No.</th>
      <th style="width:25%;">NAME OF EXAM</th>
      <th style="width:14%;">ROLL No.</th>
      <th style="width:14%;">YEAR OF PASSING</th>
      <th style="width:14%;">PERCENT/ GRADE</th>
      <th>NAME OF UNIVERSITY/ BOARD</th>
    </tr>
    <?php
      $levelLabels = ['10th' => 'High School (10th)', '12th' => 'Intermediate (12th)', 'UG' => 'Undergraduate', 'PG' => 'Postgraduate'];
      $rows = array_filter($academics, fn($a) => !empty($a['institution_board']));
      $sn = 1;
      foreach ($rows as $a):
    ?>
      <tr>
        <td><?= $sn++ ?></td>
        <td><?= v($levelLabels[$a['level']] ?? $a['level']) ?></td>
        <td>&nbsp;</td>
        <td><?= v($a['year_of_passing']) ?></td>
        <td><?= v($a['percentage']) ?>%</td>
        <td><?= v($a['institution_board']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php for ($i = count($rows); $i < 4; $i++): ?>
      <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <?php endfor; ?>
  </table>

</div>
</body>
</html>
