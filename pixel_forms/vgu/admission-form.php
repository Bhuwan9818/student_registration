<?php
/**
 * Vivekananda Global University (VGU), Jaipur — Profile Form
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
 *   $qualifications = ... (rows of educational qualifications for this student)
 *   $experience     = ... (rows of work experience for this student)
 */

require_once __DIR__ . '/../shared/helpers.php';

/** Renders a round (radio-style) tick box used for the UG/PG/NET/M.Phil/Ph.D row. */
function circleBoxVgu(bool $checked = false): string
{
    return '<span class="circle-box">' . ($checked ? '&#9679;' : '') . '</span>';
}

if (!isset($student)) {
    // Sample data so this file can be previewed standalone
    $student = [
        'unique_no'       => '',
        'level'           => 'PG',                 // UG | PG | NET | M.Phil | Ph.D
        'post_applied'    => 'Assistant Professor',
        'department'      => 'Computer Science',
        'title'           => 'Mr.',                 // Prof. | Dr. | Mr. | Ms.
        'first_name'      => 'RAHUL',
        'last_name'       => 'SHARMA',
        'dob'             => '1990-08-15',
        'email'           => 'rahul@example.com',
        'mobile'          => '9818404944',
        'mobile2'         => '',
        'address'         => '123 Milan Colony, Central Delhi, Delhi - 110002',
        'last_salary'     => '',
        'expected_salary' => '',
        'photo_path'      => '',
    ];
    $qualifications = [
        ['qualification' => 'M.Tech', 'subject' => 'Computer Science', 'board_university' => 'Delhi University', 'school_college' => 'DTU', 'percentage' => '82', 'year' => '2015'],
        ['qualification' => 'B.Tech', 'subject' => 'Computer Science', 'board_university' => 'Delhi University', 'school_college' => 'DTU', 'percentage' => '78', 'year' => '2013'],
    ];
    $experience = [
        ['institute' => 'ABC Institute of Technology', 'position' => 'Lecturer', 'from' => '2016', 'to' => '2020', 'duration' => '4'],
    ];
}
$qualifications = $qualifications ?? [];
$experience = $experience ?? [];
$candidateName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Profile Form - Vivekananda Global University</title>
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
      <td class="header-logo-left"><img src="assets/vgu-logo.jpeg" alt="VGU Logo" class="uni-logo"></td>
      <td class="header-center">
        <div class="uni-establish">(Established by Rajasthan State Legislature vide Act. No. 11/2012 and covered u/s 2(f) of UGC Act 1956)</div>
      </td>
      <td class="header-logo-right"><img src="assets/vgu-logo.jpeg" alt="VGU Logo" class="uni-logo"></td>
    </tr>
  </table>

  <!-- ================= TOP ROW: UNIQUE NO / TITLE / LEVEL ================= -->
  <div class="top-row">
    <div class="unique-no">Unique No. <span class="dotted-fill short"></span></div>
    <div class="form-title">Profile Form</div>
    <div class="level-row">
      <?php foreach (['UG','PG','NET','M.Phil','Ph.D'] as $lvl): ?>
        <span class="inline-check"><?= circleBoxVgu($student['level'] === $lvl) ?> <?= $lvl ?></span>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ================= MAIN FIELDS + PHOTO ================= -->
  <table class="top-grid">
    <tr>
      <td class="top-grid-fields">

        <div class="field-row">
          <div class="field-label wide-label">Post Applied for</div>
          <div class="dotted-fill"><?= v($student['post_applied'] ?? '') ?></div>
        </div>
        <div class="field-row note-row">
          <span class="small-note">(Mention all posts if applied for multiple posts)</span>
        </div>

        <div class="field-row">
          <div class="field-label wide-label">Department</div>
          <div class="dotted-fill"><?= v($student['department'] ?? '') ?></div>
        </div>

        <div class="field-row">
          <div class="field-label wide-label">Name: (Prof./Dr./Mr./Ms.)</div>
          <div class="dotted-fill"><?= v(trim(($student['title'] ?? '') . ' ' . $candidateName)) ?></div>
        </div>

        <div class="field-row">
          <div class="field-label">Date of Birth:</div>
          <div class="dotted-fill"><?= v($student['dob'] ?? '') ?></div>
          <div class="field-label inline-label">Email:</div>
          <div class="dotted-fill wide"><?= v($student['email'] ?? '') ?></div>
        </div>

        <div class="field-row">
          <div class="field-label">Mobile No. :</div>
          <span class="small-note">1.</span>
          <div class="dotted-fill"><?= v($student['mobile'] ?? '') ?></div>
          <span class="small-note">2.</span>
          <div class="dotted-fill"><?= v($student['mobile2'] ?? '') ?></div>
        </div>

        <div class="field-row">
          <div class="field-label wide-label">Address for Correspondence:</div>
          <div class="dotted-fill"><?= v($student['address'] ?? '') ?></div>
        </div>
        <div class="field-row">
          <div class="dotted-fill full-width">&nbsp;</div>
        </div>

      </td>
      <td class="photo-cell">
        <div class="photo-box" style="width:110px;height:130px;">
          <?php if (!empty($student['photo_path'])): ?>
            <img src="<?= v($student['photo_path']) ?>" alt="Photo">
          <?php else: ?>
            Paste your recent photograph
          <?php endif; ?>
        </div>
      </td>
    </tr>
  </table>

  <!-- ================= EDUCATIONAL QUALIFICATIONS ================= -->
  <div class="section-note">EDUCATIONAL QUALIFICATIONS:</div>
  <table class="form-table qual-table">
    <tr>
      <th style="width:6%;">S. No.</th>
      <th style="width:16%;">Qualification</th>
      <th style="width:20%;">Subject/Specialization</th>
      <th style="width:18%;">Board/ University</th>
      <th style="width:22%;">Name of School/ College</th>
      <th style="width:8%;">%</th>
      <th>Year</th>
    </tr>
    <?php
      $qRows = array_filter($qualifications, fn($q) => !empty($q['qualification']));
      $sn = 1;
      foreach ($qRows as $q):
    ?>
      <tr>
        <td><?= $sn++ ?></td>
        <td><?= v($q['qualification'] ?? '') ?></td>
        <td><?= v($q['subject'] ?? '') ?></td>
        <td><?= v($q['board_university'] ?? '') ?></td>
        <td><?= v($q['school_college'] ?? '') ?></td>
        <td><?= v($q['percentage'] ?? '') ?></td>
        <td><?= v($q['year'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
    <?php for ($i = count($qRows); $i < 5; $i++): ?>
      <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <?php endfor; ?>
  </table>

  <!-- ================= EXPERIENCE ================= -->
  <div class="section-note">EXPERIENCE: <span class="section-note-normal">(Fresher's to give details of summer trainings).</span></div>
  <table class="form-table exp-table">
    <tr>
      <th style="width:6%;">S. No.</th>
      <th style="width:38%;">Name of Institute/ Organization</th>
      <th style="width:20%;">Position Held</th>
      <th style="width:12%;">From</th>
      <th style="width:12%;">To</th>
      <th>Duration in Year</th>
    </tr>
    <?php
      $eRows = array_filter($experience, fn($e) => !empty($e['institute']));
      $sn = 1;
      foreach ($eRows as $e):
    ?>
      <tr>
        <td><?= $sn++ ?></td>
        <td><?= v($e['institute'] ?? '') ?></td>
        <td><?= v($e['position'] ?? '') ?></td>
        <td><?= v($e['from'] ?? '') ?></td>
        <td><?= v($e['to'] ?? '') ?></td>
        <td><?= v($e['duration'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
    <?php for ($i = count($eRows); $i < 4; $i++): ?>
      <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <?php endfor; ?>
  </table>

  <!-- ================= SALARY ================= -->
  <div class="field-row salary-row">
    <div class="field-label">Last Drawn Salary:</div>
    <div class="dotted-fill"><?= v($student['last_salary'] ?? '') ?></div>
    <span class="small-note">(Attach Proof)</span>
    <div class="field-label inline-label">Expected Salary:</div>
    <div class="dotted-fill"><?= v($student['expected_salary'] ?? '') ?></div>
  </div>
  <div class="small-note">(Please bring last 3 months salary slip/Bank Statement)</div>

  <!-- ================= DATE / SIGNATURE ================= -->
  <div class="field-row date-sign-row">
    <div class="field-label">Date:</div>
    <div class="dotted-fill"></div>
    <div class="field-label inline-label">SIGNATURE:</div>
    <div class="dotted-fill"></div>
  </div>

  <div class="pto-note">P.T.O.</div>

</div>
</body>
</html>
