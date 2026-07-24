<?php
/**
 * Swami Vivekanand Subharti University — Application Form for
 * Admission (Directorate of Distance Education) — 2 pages,
 * pixel-accurate recreation.
 */

require_once __DIR__ . '/../shared/helpers.php';

if (!isset($student)) {
    $student = [
        'registration_no'   => 'REG-2026-000123',
        'enrollment_no'     => '',
        'course_name'       => 'MBA',
        'specialization'    => 'Marketing',
        'session_label'     => '2026-2027',
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
        'alt_mobile'        => '',
        'mobile'            => '9818404944',
        'email'             => 'rahul@example.com',
        'nationality'       => 'Indian',
        'category'          => 'General',
        'employment_status' => 'Unemployed',
        'photo_path'        => '',
    ];
    $fee = ['mode' => 'UPI', 'utr_no' => '', 'amount' => 50000, 'submitted_at' => date('Y-m-d')];
    $academics = [
        ['level' => '10th', 'institution_board' => 'CBSE Board', 'year_of_passing' => '2016', 'percentage' => '88'],
        ['level' => '12th', 'institution_board' => 'CBSE Board', 'year_of_passing' => '2018', 'percentage' => '82'],
    ];
}
$academics = $academics ?? [];
$fee = $fee ?? null;

$dobDay = $dobMonth = $dobYear = '';
if (!empty($student['dob'])) {
    [$dobYear, $dobMonth, $dobDay] = array_pad(explode('-', $student['dob']), 3, '');
}

$levelLabels = ['10th' => 'High School', '12th' => 'Intermediate', 'UG' => 'Graduation', 'PG' => 'Post Graduation'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Application Form for Admission - Swami Vivekanand Subharti University</title>
<link rel="stylesheet" href="../shared/common.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="no-print">
  <button onclick="window.print()">Print / Save as PDF</button>
</div>

<!-- ================================================================ -->
<!-- PAGE 1                                                           -->
<!-- ================================================================ -->
<div class="sheet">

  <table class="top-row-table">
    <tr>
      <td class="appno-cell">
        <span class="field-label">Application No.</span>
        <span class="boxed-fill">DDE-<?= v($student['registration_no']) ?></span>
      </td>
      <td class="afcode-cell">
        <span class="field-label">A.F. Code</span>
        <span class="boxed-fill small">SVSU-</span>
      </td>
      <td class="session-cell">
        <div class="session-label">Session</div>
        <div class="boxed-fill session-box"><?= v($student['session_label'] ?? '') ?></div>
      </td>
    </tr>
  </table>

  <table class="header-table">
    <tr>
      <td class="logo-cell"><img src="assets/svsu-logo.jpeg" alt="SVSU" class="uni-logo"></td>
      <td class="title-cell">
        <div class="dept-title">DIRECTORATE OF DISTANCE EDUCATION</div>
        <div class="uni-title">SWAMI VIVEKANAND SUBHARTI UNIVERSITY</div>
        <div class="uni-addr">Meerut (U.P) - 250005</div>
        <div class="form-title">APPLICATION FORM FOR ADMISSION</div>
        <div class="form-note">(To be filled by candidate in his / her own handwriting legibly in capital letters in English)</div>
      </td>
      <td class="photo-cell">
        <div class="photo-box" style="width:95px;height:105px;">
          <?php if (!empty($student['photo_path'])): ?>
            <img src="<?= v($student['photo_path']) ?>" alt="Photo">
          <?php else: ?>
            Affix recent Passport Size Photograph
          <?php endif; ?>
        </div>
        <div class="photo-note">Do not pin or staple</div>
      </td>
    </tr>
  </table>

  <div class="field-row">
    <span class="field-label">Specimen Signature of the Candidate (Inside the box)</span>
    <span class="signature-box" style="height:26px;">
      <?php if (!empty($student['signature_path'])): ?><img src="<?= v($student['signature_path']) ?>" alt="Signature"><?php endif; ?>
    </span>
  </div>

  <div class="field-row">
    <div class="field-label two-line">ENROLMENT NUMBER<br><span class="small-note">(For office use only)</span></div>
    <?= charBoxes($student['enrollment_no'] ?? '', 11) ?>
  </div>

  <div class="field-row">
    <div class="field-label two-line">PROGRAMME APPLIED FOR<br><span class="small-note">(Including Subject/ Specialization)</span></div>
    <span class="boxed-fill flex"><?= v($student['course_name']) ?><?= !empty($student['specialization']) ? ' - ' . v($student['specialization']) : '' ?></span>
  </div>

  <!-- Applicant / Father / Mother name blocks -->
  <div class="named-box">
    <div class="named-box-title">1. Applicant's Name : {as per Matriculation Certificate}</div>
    <div class="named-box-row">In English (In Capital Letters)</div>
    <div class="named-box-fill"><?= v($student['first_name'] . ' ' . $student['last_name']) ?></div>
    <div class="named-box-row hindi">परीक्षार्थी का पूरा नाम (हिन्दी में)</div>
  </div>

  <div class="named-box">
    <div class="named-box-title">2. Father's Name : {All the candidates including married women will mention Name of Father}</div>
    <div class="named-box-row">In English (In Capital Letters)</div>
    <div class="named-box-fill"><?= v($student['father_name']) ?></div>
    <div class="named-box-row hindi">पिता का नाम (हिन्दी में)</div>
  </div>

  <div class="named-box">
    <div class="named-box-title">3. Mother's Name :</div>
    <div class="named-box-row">In English (In Capital Letters)</div>
    <div class="named-box-fill"><?= v($student['mother_name']) ?></div>
    <div class="named-box-row hindi">माता का नाम (हिन्दी में)</div>
  </div>

  <div class="field-row">
    <span class="field-label">4. Sex :(&#10003; Tick)</span>
    <span class="inline-check">Male <?= checkBox($student['gender'] === 'Male') ?></span>
    <span class="inline-check">Female <?= checkBox($student['gender'] === 'Female') ?></span>
    <span class="field-label">5. Date of Birth :</span>
    <span class="dob-group">
      <?= charBoxes($dobDay, 2) ?><span class="dob-caption">Date</span>
      <?= charBoxes($dobMonth, 2) ?><span class="dob-caption">Month</span>
      <?= charBoxes($dobYear, 4) ?><span class="dob-caption">Year</span>
    </span>
  </div>

  <div class="field-label">6. Address for Correspondence (do not repeat name)</div>
  <?= charBoxes($student['address'] . ', ' . $student['city'] . ', ' . $student['state'], 32) ?>
  <div class="field-row pincode-row">
    <span class="field-label">Pin Code</span>
    <?= charBoxes($student['pincode'] ?? '', 6) ?>
  </div>

  <table class="contact-table">
    <tr>
      <th>Phone No. with STD Code</th>
      <th>Mobile No</th>
      <th>E-mail</th>
    </tr>
    <tr>
      <td><?= v($student['alt_mobile'] ?? '') ?></td>
      <td><?= v($student['mobile'] ?? '') ?></td>
      <td><?= v($student['email'] ?? '') ?></td>
    </tr>
  </table>

  <div class="fee-note">
    Please ensure that you have enclosed the DD for the prescribed fees in full and other certificates as indicated in prospectus.
  </div>

  <div class="field-label">7. Details of Fee Payment :</div>
  <div class="fee-note">Demand Draft drawn in favour of SVSU, Distance Education, Payable at Meerut</div>

  <div class="two-col-row">
    <div class="col"><span class="field-label">Cash/Demand Draft No./RTGS</span><span class="dotted-fill"><?= v($fee['utr_no'] ?? ($fee['mode'] ?? '')) ?></span></div>
    <div class="col"><span class="field-label">Date</span><span class="dotted-fill"><?= $fee ? v(date('d-m-Y', strtotime($fee['submitted_at']))) : '' ?></span></div>
  </div>
  <div class="two-col-row">
    <div class="col"><span class="field-label">Bank</span><span class="dotted-fill"></span></div>
    <div class="col"><span class="field-label">Amount</span><span class="dotted-fill"><?= $fee ? '₹' . number_format($fee['amount'], 2) : '' ?></span></div>
  </div>
  <div class="field-row"><span class="field-label">Amount in words</span><span class="dotted-fill"></span></div>

  <!-- <div class="coordinator-sign">Seal &amp; Signature of Coordinator</div> -->

        <div class="two-col-row top-gap">
    <div class="col">
      <span class="field-label">8. Nationality</span>
      <div class="boxed-fill block"><?= v($student['nationality'] ?? '') ?></div>
    </div>
    <div class="col category-col">
      <span class="field-label">9. Category (tick mark whichever is applicable</span>
      <div class="field-sub">(Please attach category certificate if applicable)</div>
      <div class="category-row">
        <?php foreach (['Gen.' => 'General', 'OBC' => 'OBC', 'SC' => 'SC', 'ST' => 'ST', 'Others' => 'Other'] as $label => $key): ?>
          <span class="cat-item"><?= $label ?><br><?= checkBox($student['category'] === $key) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="field-row">
    <span class="field-label">10. Employment Status</span>
  </div>
  <div class="boxed-fill block"><?= v($student['employment_status'] ?? '') ?></div>

  <div class="field-label section-gap">11. Details of Educational Qualifications (From Matriculation onwards) :</div>
  <table class="form-table qual-table">
    <tr>
      <th>Name of the Examination</th>
      <th>Subject</th>
      <th style="width:12%;">Year of Passing</th>
      <th>Name of University/ Board</th>
      <th style="width:12%;">Division/ Grade</th>
    </tr>
    <?php
      $rows = array_filter($academics, fn($a) => !empty($a['institution_board']));
      foreach ($rows as $a):
    ?>
      <tr>
        <td><?= v($levelLabels[$a['level']] ?? $a['level']) ?></td>
        <td><?= v($student['specialization'] ?? '-') ?></td>
        <td><?= v($a['year_of_passing']) ?></td>
        <td><?= v($a['institution_board']) ?></td>
        <td><?= v($a['percentage']) ?>%</td>
      </tr>
    <?php endforeach; ?>
    <?php for ($i = count($rows); $i < 6; $i++): ?>
      <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <?php endfor; ?>
  </table>

  

</div>

<!-- ================================================================ -->
<!-- PAGE 2                                                           -->
<!-- ================================================================ -->
<div class="sheet">

  

  <div class="declaration-title">DECLARATION</div>
  <div class="declaration-text">
    I hereby declare that the information furnished herein above is true and correct to the best of my knowledge and
    belief. I further declare that the attested photocopies of the certificates submitted by me at the time of admission are
    the true copies of the originals. I have read the prospectus and the rules and regulations of the University. In case any
    information is found incorrect, at any stage, I agree to forego the fee deposited and also the claim for admission.
  </div>

  <div class="two-col-row sign-row">
    <div class="col">Place &amp; Date :</div>
    <div class="col text-right">Signature of the Applicant</div>
  </div>

  <table class="form-table office-table">
    <tr>
      <th colspan="4">For A.F. use only</th>
    </tr>
    <tr>
      <td class="office-label">Eligible : (&#10003; Tick)</td>
      <td class="office-yn">Yes <span class="tick-box"></span></td>
      <td class="office-label">Course Fee paid in Full</td>
      <td class="office-yn">Yes <span class="tick-box"></span> No <span class="tick-box"></span></td>
    </tr>
    <tr>
      <td class="office-label">Fee Receipt Issued</td>
      <td class="office-yn">Yes <span class="tick-box"></span> No <span class="tick-box"></span></td>
      <td class="office-label">Originals Verified</td>
      <td class="office-yn">Yes <span class="tick-box"></span> No <span class="tick-box"></span></td>
    </tr>
  </table>

  <div class="field-row">Granted provisional admission subject to ratification by University.</div>
  <div class="coordinator-sign">Seal &amp; Signature of Coordinator</div>

  <div class="cut-line">- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -</div>
  <div class="office-fill-title">(To be filled by the Office)</div>

  <table class="office-checklist-table">
    <tr>
      <td class="checklist-col">
        <div><?= checkBox() ?> Photocopy of High School Mark sheet &amp; Certificate</div>
        <div><?= checkBox() ?> Photocopy of Intermediate Mark sheet &amp; Certificate</div>
        <div><?= checkBox() ?> Photocopy of Graduation I, II, III Year Mark sheet &amp; Certificate<br><span class="indent">(only applicable for PG Courses)</span></div>
        <div><?= checkBox() ?> Photocopy of Previous year mark sheet (in case of Credit Transfer)</div>
        <div><?= checkBox() ?> Photocopy of required Degree/Diploma (in case of Lateral Entry)</div>
      </td>
      <td class="eligible-col">
        <table class="form-table eligible-table">
          <tr><th colspan="3">Eligible for the Course:</th></tr>
          <tr><td>1. U.G.</td><td>Yes <span class="tick-box"></span></td><td>No <span class="tick-box"></span></td></tr>
          <tr><td>2. P.G.</td><td>Yes <span class="tick-box"></span></td><td>No <span class="tick-box"></span></td></tr>
          <tr><td>3. C.T</td><td>Yes <span class="tick-box"></span></td><td>No <span class="tick-box"></span></td></tr>
          <tr><td>4. L.E.</td><td>Yes <span class="tick-box"></span></td><td>No <span class="tick-box"></span></td></tr>
        </table>
      </td>
    </tr>
  </table>

  <div class="field-row">Recommendation of Checking Officer <span class="dotted-fill"></span></div>
  <div class="field-row">This is to certify that the candidate is eligible for admission. Enrollment no. may be allotted.</div>
  <div class="field-row">Enrollment No. <span class="dotted-fill"><?= v($student['enrollment_no'] ?? '') ?></span></div>
  <div class="field-row">Checked by <span class="dotted-fill"></span></div>

  <div class="two-col-row sign-row">
    <div class="col">Date : <span class="dotted-fill short"></span></div>
    <div class="col text-right">
      Signature<br>(Sanctioning Authority)
    </div>
  </div>

</div>
</body>
</html>
