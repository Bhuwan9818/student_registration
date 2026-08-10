<?php
/**
 * Sikkim Manipal University (SMU) — Directorate of Distance Education
 * "Application for Transcript / Bonafide cum Migration Certificate"
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
 *   $academics = ... (rows from student_academics for this student, used as semester rows)
 */

require_once __DIR__ . '/../shared/helpers.php';

if (!isset($student)) {
    // Sample data so this file can be previewed standalone
    $student = [
        'first_name'          => 'RAHUL',
        'last_name'           => 'SHARMA',
        'address'             => '123 Milan Colony, Central Delhi',
        'pincode'             => '110002',
        'alt_mobile'          => '011-23456789',
        'mobile'              => '9818404944',
        'email'               => 'rahul@example.com',
        'roll_no'             => 'SMU2026001',
        'course_code'         => 'MBA-DE',
        'transcript_copies'   => '2',
        'bonafide_migration'  => 'Yes',
        'reason'              => 'For higher studies abroad',
        'eligibility_verified'=> '',
        'dd_no'               => '',
        'dd_date'             => '',
        'dd_amount'           => '',
        'bank_name'           => '',
        'checklist_marksheets'=> false,
        'checklist_dd'        => false,
        'send_name'           => 'RAHUL SHARMA',
        'send_address'        => '123 Milan Colony, Central Delhi',
        'send_pincode'        => '110002',
    ];
    // Semester rows for Academic Details
    $academics = [
        ['semester' => '1', 'month_year' => 'June 2019', 'total' => '480/700', 'grade' => 'B+', 'remarks' => ''],
        ['semester' => '2', 'month_year' => 'Dec 2019',  'total' => '495/700', 'grade' => 'A',  'remarks' => ''],
    ];
}
$academics = $academics ?? [];
$candidateName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Application for Transcript / Bonafide cum Migration Certificate - Sikkim Manipal University</title>
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
      <td class="header-logo"><img src="assets/smu-logo.jpeg" alt="SMU Logo" class="uni-logo"></td>
      <td class="header-name">
        <div class="uni-name">SMU</div>
        <div class="uni-sub-name">SIKKIM<br>MANIPAL<br>UNIVERSITY</div>
        <div class="uni-directorate">DIRECTORATE OF DISTANCE EDUCATION</div>
      </td>
      <td class="header-title">Application for Transcript / Bonafide cum Migration Certificate</td>
    </tr>
  </table>

  <!-- ================= TOP INFO BLOCK (Name/Address vs Office Use) ================= -->
  <table class="form-table top-info-table">
    <tr>
      <td class="label-cell">1. Name :</td>
      <td class="value-cell"><?= v($candidateName) ?></td>
      <td class="office-header" rowspan="1">For Office Use Only</td>
    </tr>
    <tr>
      <td class="label-cell" rowspan="3">Address:</td>
      <td class="value-cell address-line"><?= v($student['address'] ?? '') ?></td>
      <td class="office-field">Eligibility Verified (Yes/ NO) <span class="dotted-fill short"><?= v($student['eligibility_verified'] ?? '') ?></span></td>
    </tr>
    <tr>
      <td class="value-cell address-line">&nbsp;</td>
      <td class="office-blank" rowspan="2"><span class="office-signature">Signature</span></td>
    </tr>
    <tr>
      <td class="value-cell address-line">&nbsp;</td>
    </tr>
    <tr>
      <td class="label-cell">Pin Code:</td>
      <td class="value-cell"><?= v($student['pincode'] ?? '') ?></td>
      <td class="office-blank"></td>
    </tr>
  </table>

  <!-- ================= PHONE / MOBILE / EMAIL ================= -->
  <table class="form-table triple-row-table">
    <tr>
      <td class="label-cell">*Ph.(R)</td>
      <td class="value-cell"><?= v($student['alt_mobile'] ?? '') ?></td>
      <td class="label-cell">*Mobile:</td>
      <td class="value-cell"><?= v($student['mobile'] ?? '') ?></td>
      <td class="label-cell">*Email ID:</td>
      <td class="value-cell"><?= v($student['email'] ?? '') ?></td>
    </tr>
  </table>

  <table class="form-table pair-row-table">
    <tr>
      <td class="label-cell">2. Roll No:</td>
      <td class="value-cell"><?= v($student['roll_no'] ?? '') ?></td>
      <td class="label-cell">3. Course Code:</td>
      <td class="value-cell"><?= v($student['course_code'] ?? '') ?></td>
    </tr>
    <tr>
      <td class="label-cell">4. No of Transcript copies requested</td>
      <td class="value-cell"><?= v($student['transcript_copies'] ?? '') ?></td>
      <td class="label-cell">5. Bonafide cum Migration Certificate (Yes / No)</td>
      <td class="value-cell"><?= v($student['bonafide_migration'] ?? '') ?></td>
    </tr>
  </table>

  <table class="form-table reason-table">
    <tr>
      <td class="reason-label">5. Briefly indicate the reason for obtaining the above certificate:</td>
    </tr>
    <tr>
      <td class="reason-value"><?= v($student['reason'] ?? '') ?></td>
    </tr>
  </table>

  <!-- ================= ACADEMIC DETAILS ================= -->
  <div class="section-note">6. Academic Details (Enclose Xerox copy of the Marks Card of semesters):</div>
  <table class="form-table academic-table">
    <tr>
      <th style="width:14%;">Semester</th>
      <th style="width:26%;">Month &amp; year of passing</th>
      <th style="width:18%;">Semester Total</th>
      <th style="width:18%;">Semester Grade</th>
      <th>Remarks</th>
    </tr>
    <?php
      $rows = array_filter($academics, fn($a) => !empty($a['semester']) || !empty($a['month_year']));
      foreach ($rows as $a):
    ?>
      <tr>
        <td><?= v($a['semester'] ?? '') ?></td>
        <td><?= v($a['month_year'] ?? '') ?></td>
        <td><?= v($a['total'] ?? '') ?></td>
        <td><?= v($a['grade'] ?? '') ?></td>
        <td><?= v($a['remarks'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
    <?php for ($i = count($rows); $i < 5; $i++): ?>
      <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <?php endfor; ?>
  </table>

  <!-- ================= FEES PAID ================= -->
  <div class="section-note">7. Details of fees paid (Refer to the Instructions)</div>
  <table class="form-table fees-table">
    <tr>
      <th>DD NO</th>
      <th>DD Date</th>
      <th>DD Amount(&#8377;)</th>
    </tr>
    <tr>
      <td><?= v($student['dd_no'] ?? '') ?></td>
      <td><?= v($student['dd_date'] ?? '') ?></td>
      <td><?= v($student['dd_amount'] ?? '') ?></td>
    </tr>
  </table>
  <table class="form-table bank-table">
    <tr>
      <td class="bank-label">Bank Name:</td>
      <td class="bank-value"><?= v($student['bank_name'] ?? '') ?></td>
    </tr>
    <tr>
      <td colspan="2" class="dd-note">Demand Draft (DD) in favour of Sikkim Manipal University, payable at Gangtok</td>
    </tr>
  </table>

  <!-- ================= CERTIFICATION ================= -->
  <div class="certify-note">Certified that information given above is correct.</div>
  <div class="processing-note"><span class="underline-bold">Processing time:</span> 15 days from the date of receipt of application.</div>
  <div class="place-date-row">
    <div class="place-date-left">
      <div>Place:</div>
      <div>Date:</div>
    </div>
    <div class="place-date-right">Signature of the candidate</div>
  </div>

  <!-- ================= CHECKLIST / MAILING ADDRESS ================= -->
  <table class="form-table checklist-table">
    <tr>
      <td class="checklist-col">
        <div class="checklist-title">8. Checklist (put tick mark in appropriate box)</div>
        <table class="inner-table">
          <tr><td>1) Xerox copies of all Marks Cards</td><td class="tick-cell"><?= checkBox(!empty($student['checklist_marksheets'])) ?></td></tr>
          <tr><td>2) Demand Draft</td><td class="tick-cell"><?= checkBox(!empty($student['checklist_dd'])) ?></td></tr>
          <tr><td colspan="2" class="mandatory-note">* Information is mandatory</td></tr>
        </table>
      </td>
      <td class="mailing-col">
        <div class="mailing-title">8. Applicant's address to which the certificate to be sent</div>
        <table class="inner-table">
          <tr><td class="mailing-label">Name:</td><td><?= v($student['send_name'] ?? '') ?></td></tr>
          <tr><td class="mailing-label">Address:</td><td class="mailing-address"><?= v($student['send_address'] ?? '') ?></td></tr>
          <tr><td class="mailing-blank">&nbsp;</td><td>&nbsp;</td></tr>
          <tr><td class="mailing-label">Pin Code:</td><td><?= v($student['send_pincode'] ?? '') ?></td></tr>
        </table>
      </td>
    </tr>
  </table>

</div>
</body>
</html>
