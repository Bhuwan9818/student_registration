<?php
/**
 * Amity University — Directorate of Distance & Online Education
 * Registration cum Enrolment Form — pixel-accurate recreation.
 *
 * $student: associative array, see sgvu/admission-form.php header
 * comment for the expected shape. $academics: rows from
 * student_academics for this student.
 */

require_once __DIR__ . '/../shared/helpers.php';

if (!isset($student)) {
    $student = [
        'course_name'     => 'MBA',
        'specialization'  => 'Marketing',
        'session_label'   => '2026-2027',
        'first_name'      => 'RAHUL',
        'last_name'       => 'SHARMA',
        'father_name'     => 'SURESH SHARMA',
        'mother_name'     => 'GEETA SHARMA',
        'nationality'     => 'Indian',
        'state'           => 'Delhi',
        'dob'             => '2000-08-15',
        'gender'          => 'Male',
        'email'           => 'rahul@example.com',
        'mobile'          => '9818404944',
        'address'         => '123 Milan Colony, Delhi',
        'district'        => 'Central Delhi',
        'pincode'         => '110002',
        'photo_path'      => '',
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

// Amity's form splits the name into Last / Middle / First — our schema
// only stores first_name + last_name, so Middle stays blank unless supplied.
$lastName = $student['last_name'] ?? '';
$middleName = $student['middle_name'] ?? '';
$firstName = $student['first_name'] ?? '';

$levelLabels = ['10th' => 'High School (10th)', '12th' => 'Intermediate (12th)', 'UG' => 'Graduation', 'PG' => 'Post Graduation'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Registration cum Enrolment Form - Amity University</title>
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
      <td class="logo-cell">
        <img src="assets/amity-logo.png" alt="Amity University" class="uni-logo">
      </td>
      <td class="divider-cell">|</td>
      <td class="dept-cell">
        <div class="dept-name">DIRECTORATE OF DISTANCE &amp;<br>ONLINE EDUCATION</div>
      </td>
    </tr>
  </table>
  <div class="header-bar"></div>

  <div class="form-title-block">
    <div class="form-title">Registration cum Enrolment Form</div>
    <div class="session-line">Academic session<span class="dots">..................</span> <?= v($student['session_label'] ?? '') ?></div>
  </div>

  <div class="photo-box top-photo">
    <?php if (!empty($student['photo_path'])): ?>
      <img src="<?= v($student['photo_path']) ?>" alt="Photo">
    <?php else: ?>
      Space for passport size photograph (To be accompanied by a govt. issued photo ID)
    <?php endif; ?>
  </div>

  <div class="field-row">
    <span class="field-label">Program Name:</span>
    <span class="dotted-fill"><?= v($student['course_name']) ?><?= !empty($student['specialization']) ? ' - ' . v($student['specialization']) : '' ?></span>
    <span class="field-label">Program Code:</span>
    <span class="dotted-fill"></span>
  </div>

  <div class="section-line"></div>

  <!-- ================= FULL NAME ================= -->
  <div class="field-row name-row">
    <div class="name-label-block">
      <div class="field-label">Full Name of Student:</div>
      <div class="field-sub">(In Block Letters as per School Certificates)</div>
    </div>
    <div class="mr-mrs-block">
      <div><?= checkBox($student['gender'] === 'Male') ?> Mr.</div>
      <div><?= checkBox($student['gender'] === 'Female') ?> Mrs.</div>
    </div>
    <div class="name-parts">
      <div class="name-part"><span class="dotted-fill"><?= v($lastName) ?></span><span class="name-part-label">Last Name</span></div>
      <div class="name-part"><span class="dotted-fill"><?= v($middleName) ?></span><span class="name-part-label">Middle Name</span></div>
      <div class="name-part"><span class="dotted-fill"><?= v($firstName) ?></span><span class="name-part-label">First Name</span></div>
    </div>
  </div>

  <div class="two-col-row">
    <div class="col">
      <span class="field-label">Father's Name:</span>
      <span class="dotted-fill"><?= v($student['father_name']) ?></span>
      <div class="field-sub">(As per School Certificate)</div>
    </div>
    <div class="col">
      <span class="field-label">Mother's Name:</span>
      <span class="dotted-fill"><?= v($student['mother_name']) ?></span>
      <div class="field-sub">(As per School Certificate)</div>
    </div>
  </div>

  <div class="two-col-row">
    <div class="col"><span class="field-label">Nationality:</span> <span class="dotted-fill"><?= v($student['nationality']) ?></span></div>
    <div class="col"><span class="field-label">State of Domicile:</span> <span class="dotted-fill"><?= v($student['state']) ?></span></div>
  </div>

  <div class="two-col-row">
    <div class="col">
      <span class="field-label">Date of Birth:</span>
      <?= charBoxes($dobDay, 2) ?> <?= charBoxes($dobMonth, 2) ?> <?= charBoxes($dobYear, 4) ?>
      <div class="dob-sub"><span>Date</span><span>Month</span><span>Year</span></div>
    </div>
    <div class="col">
      <span class="field-label">Sex:</span>
      <span class="inline-check">Male <?= checkBox($student['gender'] === 'Male') ?></span>
      <span class="inline-check">Female <?= checkBox($student['gender'] === 'Female') ?></span>
    </div>
  </div>

  <div class="two-col-row">
    <div class="col"><span class="field-label">E-mail Address:</span> <span class="dotted-fill"><?= v($student['email']) ?></span></div>
    <div class="col"><span class="field-label">Contact No.:</span> <span class="dotted-fill"><?= v($student['mobile']) ?></span></div>
  </div>

  <div class="field-row">
    <span class="field-label">Correspondence Address:</span>
    <span class="dotted-fill"><?= v($student['address']) ?><?= !empty($student['district']) ? ', ' . v($student['district']) : '' ?> - <?= v($student['pincode']) ?></span>
  </div>
  <div class="field-row"><span class="dotted-fill">&nbsp;</span></div>

  <!-- ================= SOURCE OF INFORMATION ================= -->
  <div class="field-row">
    <span class="field-label">Source of Information:</span>
    <span class="inline-check">Web Search <?= checkBox() ?></span>
    <span class="inline-check">Friends Recommendation <?= checkBox() ?></span>
    <span class="inline-check">Newspaper <?= checkBox() ?></span>
    <span class="inline-check">Visual Add <?= checkBox() ?></span>
  </div>
  <div class="field-row"><span class="field-label">Any other source</span><span class="boxed-fill"></span></div>

  <!-- ================= EMPLOYMENT HISTORY ================= -->
  <div class="field-row">
    <div>
      <div class="field-label">Employment History:</div>
      <div class="field-sub">(For last 5 years, If applicable)</div>
    </div>
    <table class="form-table history-table">
      <tr>
        <th>Name &amp; Address of the Organization</th>
        <th style="width:18%;">Designation</th>
        <th style="width:14%;">From</th>
        <th style="width:14%;">To</th>
      </tr>
      <?php for ($i = 0; $i < 4; $i++): ?>
        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
      <?php endfor; ?>
    </table>
  </div>

  <!-- ================= EDUCATIONAL QUALIFICATIONS ================= -->
  <div class="field-row">
    <div>
      <div class="field-label">Educational Qualifications:</div>
      <div class="field-sub">(including 12 yrs of formal schooling)</div>
    </div>
    <table class="form-table qual-table">
      <tr>
        <th>Name of School/ University</th>
        <th style="width:14%;">Year of Passing</th>
        <th style="width:20%;">Board/ College/ University</th>
        <th style="width:16%;">Main Subject</th>
        <th style="width:16%;">Aggregate of Marks</th>
      </tr>
      <?php
        $rows = array_filter($academics, fn($a) => !empty($a['institution_board']));
        foreach ($rows as $a):
      ?>
        <tr>
          <td><?= v($levelLabels[$a['level']] ?? $a['level']) ?></td>
          <td><?= v($a['year_of_passing']) ?></td>
          <td><?= v($a['institution_board']) ?></td>
          <td><?= v($student['specialization']) ?></td>
          <td><?= v($a['percentage']) ?>%</td>
        </tr>
      <?php endforeach; ?>
      <?php for ($i = count($rows); $i < 3; $i++): ?>
        <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
      <?php endfor; ?>
    </table>
  </div>

  <div class="footer-block">
    Amity Directorate of Distance &amp; Online Education, F-2 Block, Second Floor, Sector 125 Campus, NOIDA 201303 (UP), INDIA<br>
    <strong>Contact No.:</strong> 0120 4735650 / 889
  </div>

</div>
</body>
</html>
