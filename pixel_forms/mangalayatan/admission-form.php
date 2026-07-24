<?php
/**
 * Mangalayatan University — Application Form (with full Program
 * Applying For checklist) — pixel-accurate recreation.
 */

require_once __DIR__ . '/../shared/helpers.php';

if (!isset($student)) {
    $student = [
        'registration_no' => 'REG-2026-000123',
        'course_name'      => 'B.C.A.',
        'first_name'       => 'RAHUL',
        'last_name'        => 'SHARMA',
        'father_name'      => 'SURESH SHARMA',
        'photo_path'       => '',
    ];
}

// The three checklist columns, exactly as printed on the original form.
// type: 'institute' (navy bar) or 'dept' (orange bar) or 'note' (italic, no checkbox) or 'item' (checkbox)
$columns = [
    // Column 1
    [
        ['type' => 'institute', 'text' => 'Institute of Engineering &amp; Technology'],
        ['type' => 'item', 'text' => 'B.Tech. (ME/CE/ECE/CSE)'],
        ['type' => 'item', 'text' => 'B.Tech. + M.Tech (ME/CE/ECE/CSE)'],
        ['type' => 'item', 'text' => 'B.Tech. (ME/CE/ECE/CSE)-Lateral Entry'],
        ['type' => 'item', 'text' => 'M.Tech. (Production Engg./Environmental Engg./ Communication Systems/Computer Science Engg./Hydrolic Structure)'],
        ['type' => 'institute', 'text' => 'University Polytechnic'],
        ['type' => 'item', 'text' => 'Diploma in Engg.(ME/CE/EE/CSE/ Electronics Engg/Automobile Engg./ Heating, Ventilation &amp; Air Conditioning )'],
        ['type' => 'item', 'text' => 'Diploma in Engg.(ME/CE/EE/CSE/Electronics Engg/Automobile Engg./Heating, Ventilation &amp; Air Conditioning )-Lateral Entry'],
        ['type' => 'dept', 'text' => 'Department of Computer Applications'],
        ['type' => 'item', 'text' => 'B.C.A.'],
        ['type' => 'dept', 'text' => 'Department of Agriculture'],
        ['type' => 'item', 'text' => 'Diploma in Agriculture'],
        ['type' => 'item', 'text' => 'B.Sc. (Agriculture)'],
        ['type' => 'item', 'text' => 'M.Sc. (Agriculture) (Agronomy/Horticulture/ Animal Husbandry)'],
        ['type' => 'institute', 'text' => 'Institute of Applied Sciences'],
        ['type' => 'item', 'text' => 'B.Sc. (Hons.) (Physics/Chemistry/Mathematics)'],
        ['type' => 'item', 'text' => 'B.Sc. (PCM/ZBC/PSM/IT)'],
        ['type' => 'item', 'text' => 'M.Sc. (Physics/Chemistry/Mathematics/IT)'],
        ['type' => 'institute', 'text' => 'Institute of Bio-medical Education &amp; Research'],
        ['type' => 'dept', 'text' => 'Department of Pharmacy'],
        ['type' => 'item', 'text' => 'D. Pharm.'],
        ['type' => 'item', 'text' => 'B. Pharm.'],
        ['type' => 'item', 'text' => 'M. Pharm.(Pharmaceutical Chemistry/ Pharmaceutics)'],
        ['type' => 'dept', 'text' => 'Department of Biotechnology &amp; Life Sciences'],
        ['type' => 'item', 'text' => 'B.Sc. (Biotechnology)'],
        ['type' => 'item', 'text' => 'M.Sc. (Biotechnology)'],
    ],
    // Column 2
    [
        ['type' => 'institute', 'text' => 'Institute of Nursing &amp; Para Medical Sciences'],
        ['type' => 'item', 'text' => 'B.Sc. Nursing'],
        ['type' => 'item', 'text' => 'Diploma in X-ray Technician'],
        ['type' => 'item', 'text' => 'Diploma in Operation Theatre'],
        ['type' => 'item', 'text' => 'Diploma in Lab Technician'],
        ['type' => 'note', 'text' => '(Programs are Subject to Approval)'],
        ['type' => 'institute', 'text' => 'Institute of Business Management'],
        ['type' => 'dept', 'text' => 'Department of Management'],
        ['type' => 'item', 'text' => 'B.B.A.'],
        ['type' => 'item', 'text' => 'M.B.A.'],
        ['type' => 'dept', 'text' => 'Department of Commerce'],
        ['type' => 'item', 'text' => 'B.Com. (Hons)'],
        ['type' => 'item', 'text' => 'M.Com.'],
        ['type' => 'dept', 'text' => 'Department of Tourism &amp; Hotel Management'],
        ['type' => 'item', 'text' => 'Diploma in Hotel Management'],
        ['type' => 'item', 'text' => 'Bachelor in Hotel Management (BHM)'],
        ['type' => 'item', 'text' => 'Diploma in Tourism &amp; Hospitality /Culinary Arts'],
        ['type' => 'item', 'text' => 'Advanced Diploma in Tourism &amp; Hospitality / Culinary Arts'],
        ['type' => 'item', 'text' => 'B. Voc. in Tourism &amp; Hospitality /Culinary Arts'],
        ['type' => 'institute', 'text' => 'Institute of Legal Studies &amp; Research'],
        ['type' => 'item', 'text' => 'B.A.LL.B.'],
        ['type' => 'item', 'text' => 'LL.B.'],
        ['type' => 'item', 'text' => 'LL.M.(Intellectual Property Rights/ Criminal Law/ Corporate Law)'],
        ['type' => 'institute', 'text' => 'Faculty of Humanities'],
        ['type' => 'dept', 'text' => 'Institute of Education &amp; Research'],
        ['type' => 'item', 'text' => 'B.Ed. (Innovative)'],
        ['type' => 'item', 'text' => 'Integrated B.A.B.Ed.'],
        ['type' => 'item', 'text' => 'Integrated B.Sc.B.Ed.'],
        ['type' => 'item', 'text' => 'B.El.Ed.'],
        ['type' => 'item', 'text' => 'M.Ed.'],
    ],
    // Column 3
    [
        ['type' => 'dept', 'text' => 'Department of Physical Education'],
        ['type' => 'item', 'text' => 'B.P.E.S.'],
        ['type' => 'dept', 'text' => 'Department of Arts'],
        ['type' => 'item', 'text' => 'B.A.'],
        ['type' => 'item', 'text' => 'B.A. (Hons.) (English/History/Economics/ Political Science)'],
        ['type' => 'item', 'text' => 'M.A. (English/History/Economics/ Political Science)'],
        ['type' => 'dept', 'text' => 'Department of Journalism &amp; Mass Communication'],
        ['type' => 'item', 'text' => 'B.A. (Mass Communication)'],
        ['type' => 'item', 'text' => 'M.A. (Mass Communication)'],
        ['type' => 'dept', 'text' => 'Department of Library &amp; Information Science'],
        ['type' => 'item', 'text' => 'B.L.I.S.'],
        ['type' => 'item', 'text' => 'M.L.I.S.'],
        ['type' => 'dept', 'text' => 'Department of Visual &amp; Performing Arts'],
        ['type' => 'item', 'text' => 'Diploma in Fine Arts'],
        ['type' => 'item', 'text' => 'Diploma in Animation'],
        ['type' => 'item', 'text' => 'Diploma in Digital Arts'],
        ['type' => 'item', 'text' => 'Bridge Course in Fine Arts'],
        ['type' => 'item', 'text' => 'B.F.A. (Painting/ Applied Arts/ Instrumental Music/Vocal Music)'],
        ['type' => 'item', 'text' => 'M.F.A. (Painting/ Applied Arts/ Instrumental Music/Vocal Music)'],
        ['type' => 'item', 'text' => 'M.Phil.'],
        ['type' => 'institute', 'text' => 'Centre of Philosophical Sciences'],
        ['type' => 'item', 'text' => 'B.A. (Jain Philosophy)'],
        ['type' => 'item', 'text' => 'M.A. (Jain Philosophy)'],
        ['type' => 'institute', 'text' => 'Research Programs'],
        ['type' => 'item', 'text' => 'Ph.D. (Full Time/Part Time) (All Disciplines)'],
        ['type' => 'dept', 'text' => 'Other Program'],
        ['type' => 'item', 'text' => '&nbsp;'],
    ],
];

// Matches the applicant's course against the checklist so the right box is ticked.
function isSelectedProgram(string $itemText, string $courseName): bool
{
    $clean = fn($s) => strtolower(preg_replace('/[^a-z0-9]/i', '', $s));
    return $courseName !== '' && str_contains($clean($itemText), $clean($courseName));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Application Form - Mangalayatan University</title>
<link rel="stylesheet" href="../shared/common.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="no-print">
  <button onclick="window.print()">Print / Save as PDF</button>
</div>

<div class="sheet">

  <!-- ================= TOP BAR ================= -->
  <table class="topbar-table">
    <tr>
      <td class="topbar-remarks">Remarks, if any (Official Use Only)</td>
      <td class="topbar-remarks-box"></td>
      <td class="topbar-appno">Application Number</td>
      <td class="topbar-appno-box"><?= v($student['registration_no'] ?? '') ?></td>
    </tr>
  </table>

  <!-- ================= HEADER ================= -->
  <table class="header-table">
    <tr>
      <td class="logo-cell"><img src="assets/mangalayatan-logo.png" alt="Mangalayatan University" class="uni-logo"></td>
      <td class="title-cell"><div class="form-title">APPLICATION FORM</div></td>
      <!-- <td class="seal-cell"><img src="assets/mangalayatan-seal.png" alt="" class="uni-seal"></td> -->
    </tr>
  </table>
  <div class="contact-line">
    Extended NCR, Aligarh-Mathura Highway,33<sup>rd</sup> Milestone, Beswan, Aligarh - 202145 (UP), India<br>
    Email: admissions@mangalayatan.edu.in &nbsp;|&nbsp; Web: www.mangalayatan.in<br>
    <strong>Toll Free - 1800 274 4000</strong>
  </div>
  <div class="header-rule"></div>

  <!-- ================= INSTRUCTIONS + PHOTO ================= -->
  <table class="instructions-table">
    <tr>
      <td class="instructions-cell">
        <div class="instructions-title">INSTRUCTIONS</div>
        <ol class="instructions-list">
          <li>Read the Prospectus/Information Booklet carefully for Admission Procedure, Scholarships and Refund Policy.</li>
          <li>Use only Blue or Black pen to fill up the Form in English using CAPITAL/BLOCK LETTERS only.</li>
          <li>Please keep a photocopy of the Form, before submitting, as a ready reference.</li>
          <li>Incomplete Form will not be considered.</li>
          <li>Put Tick (&#10003;) mark on applied course specialization.</li>
        </ol>
      </td>
      <td class="instructions-photo-cell">
        <div class="photo-box" style="width:105px;height:110px;">
          <?php if (!empty($student['photo_path'])): ?>
            <img src="<?= v($student['photo_path']) ?>" alt="Photo">
          <?php else: ?>
            Affix latest Passport size Color Photograph
          <?php endif; ?>
        </div>
      </td>
    </tr>
  </table>

  <!-- ================= PROGRAM APPLYING FOR ================= -->
  <div class="program-title">PROGRAM APPLYING FOR</div>
  <table class="program-columns">
    <tr>
      <?php foreach ($columns as $col): ?>
        <td class="program-col">
          <?php foreach ($col as $row): ?>
            <?php if ($row['type'] === 'institute'): ?>
              <div class="group-header navy"><?= $row['text'] ?></div>
            <?php elseif ($row['type'] === 'dept'): ?>
              <div class="group-header orange"><?= $row['text'] ?></div>
            <?php elseif ($row['type'] === 'note'): ?>
              <div class="program-note"><?= $row['text'] ?></div>
            <?php else: ?>
              <div class="program-item">
                <?= checkBox(isSelectedProgram($row['text'], $student['course_name'] ?? '')) ?>
                <span><?= $row['text'] ?></span>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        </td>
      <?php endforeach; ?>
    </tr>
  </table>

  <!-- ================= CUT LINE ================= -->
  <div class="cut-line">&#9587; &nbsp;- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -</div>

  <!-- ================= FORM RECEIPT ================= -->
  <table class="receipt-table">
    <tr>
      <td class="receipt-logo-cell"><img src="assets/mangalayatan-logo.png" alt="Mangalayatan University" class="uni-logo small"></td>
      <td class="receipt-title-cell"><div class="receipt-title">Form Receipt</div></td>
      <!-- <td class="receipt-seal-cell"><img src="assets/mangalayatan-seal.png" alt="" class="uni-seal small"></td> -->
    </tr>
  </table>
  <div class="field-row">
    <span class="field-label">Date</span><span class="dotted-fill short"><?= date('d-m-Y') ?></span>
    <span class="field-label">Application No.</span><span class="boxed-fill"><?= v($student['registration_no'] ?? '') ?></span>
  </div>
  <div class="field-row">
    <span class="field-label">Name</span><span class="dotted-fill"><?= v(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></span>
    <span class="field-label">Father's Name</span><span class="dotted-fill"><?= v($student['father_name'] ?? '') ?></span>
  </div>
  <div class="field-row">
    <span class="field-label">Program Applied</span><span class="dotted-fill"><?= v($student['course_name'] ?? '') ?></span>
  </div>
  <div class="field-row signature-line">
    <span class="sign-label">Sign of Student</span>
    <span class="sign-label">Sign of Authorized Signatory</span>
  </div>

</div>
</body>
</html>
