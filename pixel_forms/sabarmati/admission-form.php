<?php
/**
 * Sabarmati University (formerly Calorx Teachers' University), Ahmedabad
 * — Regular Admission Form (4 pages)
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
 *   $academics    = ... (rows from student_academics for this student)
 *   $organizations = ... (rows of {company, address, duration, designation, salary} — optional)
 */

require_once __DIR__ . '/../shared/helpers.php';

if (!isset($student)) {
    // Sample data so this file can be previewed standalone
    $student = [
        'temp_no'           => '',
        'course_name'       => 'B.Tech Computer Science',
        'session_from'      => '26',
        'session_to'        => '27',
        'first_name'        => 'RAHUL',
        'last_name'         => 'SHARMA',
        'father_name'       => 'SURESH SHARMA',
        'mother_name'       => 'GEETA SHARMA',
        'dob'               => '2007-08-15',
        'place_of_birth'    => 'Delhi',
        'gender'            => 'Male',            // Male | Female | Transgender
        'category'          => 'Gen',              // Gen | SC | ST | OBC | EWS | PH
        'category_others'   => '',
        'marital_status'    => 'Single',
        'religion'          => 'Hindu',
        'nationality'       => 'Indian',
        'physically_challenged' => 'No',           // Yes | No
        'sports'            => '',
        'photo_path'        => '',
        'signature_path'    => '',

        'present_address'   => '123 Milan Colony, Central Delhi',
        'present_pincode'   => '110002',
        'permanent_address' => '123 Milan Colony, Central Delhi',
        'permanent_pincode' => '110002',
        'landline'          => '',
        'mobile'            => '9818404944',
        'email'             => 'rahul@example.com',
        'guardian_mobile'   => '9811122233',
        'alt_mobile'        => '',
        'awards'            => '',
        'extracurricular'   => '',
        'sports_achievement' => '',

        'declarant_relation' => 'son',             // son | daughter
        'declarant_father'   => 'SURESH SHARMA',
        'undertaking_name'   => 'RAHUL SHARMA',
        'declaration_date'   => '',
    ];
    $academics = [
        ['exam' => '10th', 'board' => 'CBSE Board', 'year' => '2023', 'subject' => '', 'total' => '500', 'obtained' => '410', 'aggregate' => '82%'],
        ['exam' => '12th', 'board' => 'CBSE Board', 'year' => '2025', 'subject' => 'PCM', 'total' => '500', 'obtained' => '390', 'aggregate' => '78%'],
    ];
    $organizations = [];
}
$academics = $academics ?? [];
$organizations = $organizations ?? [];
$candidateName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));

$dobDay = $dobMonth = $dobYear = '';
if (!empty($student['dob'])) {
    [$dobYear, $dobMonth, $dobDay] = array_pad(explode('-', $student['dob']), 3, '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admission Form - Sabarmati University</title>
<link rel="stylesheet" href="../shared/common.css?v=20260807">
<link rel="stylesheet" href="style.css?v=20260807">
</head>
<body>

<div class="no-print">
  <button onclick="window.print()">Print / Save as PDF</button>
</div>

<!-- ============================================================ -->
<!-- PAGE 1 — Admission Form / Personal Information                -->
<!-- ============================================================ -->
<div class="sheet">

  <table class="header-table">
    <tr>
      <td class="header-logo"><img src="assets/sabarmati-logo.png" alt="Sabarmati University Logo" class="uni-logo"></td>
      <td class="header-center">
        <div class="uni-name">SABARMATI UNIVERSITY</div>
        <div class="uni-sub">(FORMERLY, CALORX TEACHERS' UNIVERSITY)</div>
        <div class="uni-city">AHMEDABAD</div>
        <div class="uni-act">(State Private University Established by State Legislature of Gujarat Act No 8 of 2009 &amp; Further Amended with Act No.20 of 2019)</div>
      </td>
    </tr>
  </table>

  <div class="form-title-banner">ADMISSION FORM</div>

  <div class="programme-row">
    <span class="field-label">Application for admission in Programme</span>
    <span class="dotted-fill"><?= v($student['course_name'] ?? '') ?></span>
  </div>
  <div class="programme-row">
    <span class="field-label">ACADEMIC SESSION 20<span class="mini-fill"><?= v($student['session_from'] ?? '') ?></span> to 20 <span class="mini-fill"><?= v($student['session_to'] ?? '') ?></span></span>
  </div>

  <table class="top-grid">
    <tr>
      <td class="instructions-cell">
        <div class="instructions-box">
          <div class="instructions-title">INSTRUCTIONS:</div>
          <ul class="instructions-list">
            <li>Please read the form carefully before filling it</li>
            <li>Form should be filled in Capital Letter in English</li>
            <li>Attach the required documents as mentioned in educational qualification</li>
            <li>Incomplete application will be rejected</li>
            <li>Filling up of Application form does not guarantee an admission.</li>
          </ul>
        </div>
      </td>
      <td class="photo-cell">
        <div class="photo-instruction">Please Ensure That You Sign Within Box Enclosure</div>
        <div class="photo-box" style="width:120px;height:135px;">
          <?php if (!empty($student['photo_path'])): ?>
            <img src="<?= v($student['photo_path']) ?>" alt="Photo">
          <?php else: ?>
            Photo
          <?php endif; ?>
        </div>
        <div class="signature-box" style="width:120px;height:26px;">
          <?php if (!empty($student['signature_path'])): ?>
            <img src="<?= v($student['signature_path']) ?>" alt="Signature">
          <?php endif; ?>
        </div>
        <div class="signature-caption">STUDENT SIGNATURE</div>
      </td>
    </tr>
  </table>

  <div class="temp-no-row">Temp No. <span class="dotted-fill grow"><?= v($student['temp_no'] ?? '') ?></span></div>

  <div class="section-divider"></div>

  <div class="field-label">Name of Candidate (English in Block Letter)</div>
  <?= charBoxes($candidateName, 26) ?>
  <div class="small-note">(as per 10<sup>th</sup> Marksheet )</div>

  <div class="field-label">Name of Father / Spouse (English in Block Letter)</div>
  <?= charBoxes($student['father_name'] ?? '', 26) ?>

  <div class="field-label">Name of Mother (English in Block Letter)</div>
  <?= charBoxes($student['mother_name'] ?? '', 26) ?>

  <div class="dob-row">
    <div class="field-label">Date of Birth :</div>
    <div class="dob-group"><div class="dob-caption">DD</div><?= charBoxes($dobDay, 2) ?></div>
    <div class="dob-group"><div class="dob-caption">MM</div><?= charBoxes($dobMonth, 2) ?></div>
    <div class="dob-group"><div class="dob-caption">Year</div><?= charBoxes($dobYear, 4) ?></div>
  </div>

  <div class="line-field-row">
    <span class="field-label">Place of Birth :</span>
    <span class="boxed-fill grow"><?= v($student['place_of_birth'] ?? '') ?></span>
  </div>

  <div class="check-row">
    <span class="field-label">Gender :</span>
    <span class="inline-check">Male <?= checkBox($student['gender'] === 'Male') ?></span>
    <span class="inline-check">Female <?= checkBox($student['gender'] === 'Female') ?></span>
    <span class="inline-check">Transgender <?= checkBox($student['gender'] === 'Transgender') ?></span>
  </div>

  <div class="check-row">
    <span class="field-label">Category :</span>
    <span class="inline-check">Gen <?= checkBox($student['category'] === 'Gen') ?></span>
    <span class="inline-check">SC <?= checkBox($student['category'] === 'SC') ?></span>
    <span class="inline-check">ST <?= checkBox($student['category'] === 'ST') ?></span>
    <span class="inline-check">OBC <?= checkBox($student['category'] === 'OBC') ?></span>
    <span class="inline-check">EWS <?= checkBox($student['category'] === 'EWS') ?></span>
    <span class="inline-check">PH <?= checkBox($student['category'] === 'PH') ?></span>
  </div>
  <div class="check-row indent">
    <span class="field-label">OTHERS</span>
    <span class="boxed-fill"><?= v($student['category_others'] ?? '') ?></span>
  </div>

  <div class="line-field-row">
    <span class="field-label">Marital Status :</span>
    <span class="boxed-fill"><?= v($student['marital_status'] ?? '') ?></span>
  </div>

  <div class="line-field-row">
    <span class="field-label">Religion</span>
    <span class="boxed-fill"><?= v($student['religion'] ?? '') ?></span>
    <span class="field-label">Nationality</span>
    <span class="boxed-fill"><?= v($student['nationality'] ?? '') ?></span>
  </div>

  <div class="check-row">
    <span class="field-label">Physically Challenged :</span>
    <span class="inline-check">Yes <?= checkBox(strtolower($student['physically_challenged'] ?? '') === 'yes') ?></span>
    <span class="inline-check">No <?= checkBox(strtolower($student['physically_challenged'] ?? 'no') === 'no') ?></span>
  </div>

  <div class="line-field-row">
    <span class="field-label">Sports :</span>
    <span class="boxed-fill"><?= v($student['sports'] ?? '') ?></span>
  </div>

</div>

<!-- ============================================================ -->
<!-- PAGE 2 — Address / Contact / Organization Details             -->
<!-- ============================================================ -->
<div class="sheet">

  <div class="section-heading">Address for Communication</div>
  <div class="sub-heading">Present Address (Local Address)</div>
  <?= charBoxes($student['present_address'] ?? '', 30) ?>
  <?= charBoxes('', 30) ?>
  <div class="field-row pincode-row">
    <?= charBoxes('', 22) ?>
    <span class="pincode-label">PIN Code</span>
    <?= charBoxes($student['present_pincode'] ?? '', 6) ?>
  </div>

  <div class="sub-heading spaced">Permanent Address</div>
  <?= charBoxes($student['permanent_address'] ?? '', 30) ?>
  <?= charBoxes('', 30) ?>
  <div class="field-row pincode-row">
    <?= charBoxes('', 22) ?>
    <span class="pincode-label">PIN Code</span>
    <?= charBoxes($student['permanent_pincode'] ?? '', 6) ?>
  </div>

  <div class="contact-field-row">
    <span class="field-label wide">Landline with STD Code</span>
    <?= charBoxes($student['landline'] ?? '', 11) ?>
  </div>
  <div class="contact-field-row">
    <span class="field-label wide">Student Mobile No.</span>
    <?= charBoxes($student['mobile'] ?? '', 10) ?>
  </div>
  <div class="contact-field-row">
    <span class="field-label wide">Student Mail ID</span>
    <?= charBoxes($student['email'] ?? '', 32) ?>
  </div>
  <div class="contact-field-row">
    <span class="field-label wide">Parents Mobile No.</span>
    <?= charBoxes($student['guardian_mobile'] ?? '', 10) ?>
  </div>
  <div class="contact-field-row">
    <span class="field-label wide">Alternative Mobile no.</span>
    <?= charBoxes($student['alt_mobile'] ?? '', 10) ?>
  </div>

  <div class="line-field-row block">
    <span class="field-label wide">Award and Achievements<br>(if Any) :</span>
    <span class="boxed-fill grow tall"><?= v($student['awards'] ?? '') ?></span>
  </div>
  <div class="line-field-row block">
    <span class="field-label wide">Participation in<br>Extracurricular activities :</span>
    <span class="boxed-fill grow tall"><?= v($student['extracurricular'] ?? '') ?></span>
  </div>

  <div class="section-heading spaced">Organization / Company Details</div>
  <table class="form-table org-table">
    <tr>
      <th style="width:24%;">Name of the Company</th>
      <th style="width:30%;">Address</th>
      <th style="width:16%;">Duration</th>
      <th style="width:16%;">Designation</th>
      <th>Salary Drawn</th>
    </tr>
    <?php
      $orgRows = array_filter($organizations, fn($o) => !empty($o['company']));
      foreach ($orgRows as $o):
    ?>
      <tr>
        <td><?= v($o['company'] ?? '') ?></td>
        <td><?= v($o['address'] ?? '') ?></td>
        <td><?= v($o['duration'] ?? '') ?></td>
        <td><?= v($o['designation'] ?? '') ?></td>
        <td><?= v($o['salary'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
    <?php for ($i = count($orgRows); $i < 2; $i++): ?>
      <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <?php endfor; ?>
  </table>

  <div class="note-block">
    <strong>NOTE:</strong>
    <ol>
      <li>All documents must be self attested.</li>
    </ol>
  </div>

</div>

<!-- ============================================================ -->
<!-- PAGE 3 — Academic Record / Rules & Regulations (1-10)         -->
<!-- ============================================================ -->
<div class="sheet">

  <div class="section-heading">Academic Record</div>
  <table class="form-table academic-table">
    <tr>
      <th style="width:14%;">Name of<br>Examination</th>
      <th style="width:24%;">Name of<br>Board/University</th>
      <th style="width:10%;">Year of<br>Passing</th>
      <th style="width:16%;">Subject<br>Offered</th>
      <th style="width:12%;">Total<br>Marks</th>
      <th style="width:12%;">Marks<br>Obtained</th>
      <th>Aggregate<br>% of Marks</th>
    </tr>
    <?php
      $levels = ['10th' => '10<sup>th</sup>', '12th' => '12<sup>th</sup>', 'Diploma' => 'Diploma<br><span class="cell-note">(If any)</span>', 'Graduation' => 'Graduation', 'Post Graduation' => 'Post<br>Graduation', 'Other' => 'Other<br><span class="cell-note">(Specify)&nbsp;....</span>'];
      $byLevel = [];
      foreach ($academics as $a) { $byLevel[$a['exam'] ?? ''] = $a; }
      foreach ($levels as $key => $label):
        $a = $byLevel[$key] ?? [];
    ?>
      <tr class="tall-row">
        <td class="exam-label"><?= $label ?></td>
        <td><?= v($a['board'] ?? '') ?></td>
        <td><?= v($a['year'] ?? '') ?></td>
        <td><?= v($a['subject'] ?? '') ?></td>
        <td><?= v($a['total'] ?? '') ?></td>
        <td><?= v($a['obtained'] ?? '') ?></td>
        <td><?= v($a['aggregate'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <div class="sports-line">1. Sports Achivement (National / State / District ) <span class="dotted-fill grow"><?= v($student['sports_achievement'] ?? '') ?></span></div>

  <div class="rules-title">RULES AND REGULATIONS</div>
  <ol class="rules-list">
    <li>Admission is subject to clearing the qualifying examination, group discussion and interview.</li>
    <li>The scholarship (if any) in subsequent semesters will be subject to the candidate acquiring the prescribed percentage of marks as well as on his/her conduct in the University.</li>
    <li>The admission of a candidate shall be cancelled if it is found that the admission has been secured on the basis of furnishing false documents/incorrect information to the University.</li>
    <li>Any change in name/address is required to be intimated to the University authorities immediately in writing so that correct information is maintained in the office records of the University.</li>
    <li>Sabarmati University reserves the right to revise its fee structure.</li>
    <li>A candidate found indulging in drug, abuse, violence or improper behavior and who does not adhere to the rules and regulation as are relevant from time to time, will face disciplinary action and he/she may be rusticated depending on the recommended action of the disciplinary committee.</li>
    <li>Activities that have the effect or intention of interfering with education, pursuit of knowledge, or fair evaluation of a student's performance are prohibited.</li>
    <li>The University will display important notices pertaining to fees payment, examination dates, projects, seminars, guest lectures, and industrial visits etc from time to time on the notice board. Students are advised to read the notice board regularly as no excuse of non compliance shall be entertained.</li>
    <li>The admission offer letter, academic bulletin, information brochure and website of the University are the correct and only guide to the terms and conditions of admission and the rules and regulations of the institution. Students are advised to refer to the same for clarification. No other form of communication other than those mentioned, whether written or verbal is there between the University and the students.</li>
    <li>Fees once paid is not refundable for any reason whatsoever. Only Caution Money will be refunded, if paid.</li>
  </ol>

</div>

<!-- ============================================================ -->
<!-- PAGE 4 — Rules 11-15 / Declaration / Undertaking               -->
<!-- ============================================================ -->
<div class="sheet">

  <ol class="rules-list" start="11">
    <li>Sabarmati University reserves the right to cancel the admission of any candidate under any of the following circumstances :
      <ol class="sub-list" type="a">
        <li>If the fees is not deposited within the stipulated date.</li>
        <li>If the candidate does not join the particular programme within the stipulated date even though the fee has been deposited.</li>
      </ol>
    </li>
    <li>All admissions are entirely at the discretion of the management of Sabarmati University and no explanation will be given in case of refusal.</li>
    <li>The acceptance of an offer of admission implies the consent of the student to the rules and regulations of Sabarmati University.</li>
    <li>Incase of any serious Medical issue, please submit the appropriate record of it.</li>
    <li>Incase of any criminal history, please submit the appropriate documents.</li>
  </ol>

  <div class="declaration-title">Declaration:</div>
  <div class="declaration-by">By Students:</div>
  <div class="declaration-text">
    I<span class="underline-fill"><?= v($candidateName) ?></span><?= v($student['declarant_relation'] ?? 'son') ?> / daughter of<span class="underline-fill"><?= v($student['declarant_father'] ?? '') ?></span>have read &amp; hereby certify
    that the information given in the application is complete and accurate to the best of my Knowledge.
  </div>
  <div class="declaration-text">
    I understand all the rules and regulations laid down by the University and agree that misrepresentation or omission of
    facts will justify the denial of admission, cancellation of admission or expulsion. The fees paid is non-refundable under
    any circumstance. In case I am not in position to join/continue the course even after submission of fees, I will not claim
    refund of fees. I am not entitled to pursue any course from any other educational group while enrolling with this
    University.
  </div>

  <div class="sign-date-row">
    <div class="sign-col">
      <div class="sign-line"></div>
      <div class="sign-caption">Signature</div>
    </div>
    <div class="sign-col">
      <div class="sign-line"></div>
      <div class="sign-caption">Date <?= v($student['declaration_date'] ?? '') ?></div>
    </div>
  </div>

  <div class="undertaking-box">
    <div class="undertaking-title">Undertaking</div>
    <div class="declaration-text">
      I,<span class="underline-fill"><?= v($student['undertaking_name'] ?? $candidateName) ?></span>(Name), bind myself to abide by the University's rules and
      regulations as per details given below :
    </div>
    <ol class="undertaking-list">
      <li>To be punctual in the class and the activities related to my course. I will submit reports to my professors of visits &amp; practical as per the prescribed format.</li>
      <li>I will not indulge myself in any form of teasing or any other activities which may harm the reputation of SU.</li>
      <li>I am not registered in any other regular course of any other University.</li>
      <li>I am not employed by any Government or Non-Government Organizations.</li>
      <li>I will seek approval of the University before joining any other part time/online distance mode courses during the period of my study at the University.</li>
      <li>I have understood the fees structure and I am also aware that the fee once paid is not refundable. Incase of withdrawal, refund is admissible as per admission policy.</li>
      <li>I will maintain minimum 75% attendance as per attendance policy.</li>
    </ol>

    <div class="thereby-title">THEREBY GIVE THE FOLLOWING UNDERTAKING :</div>
    <ol class="undertaking-list">
      <li>That I am not involved in any Civil / Criminal / Case / Proceeding / Charges / Enquiry at present.</li>
      <li>That I am not involved in any indisciplinary / Malpractices / or any other charges / proceedings / enquiry / case pending against me in any University or any other education authority / Institution prior to joining SU.</li>
    </ol>

    <div class="sign-date-row">
      <div class="sign-col">
        <div class="field-label">Date :</div>
        <div class="sign-line"></div>
      </div>
      <div class="sign-col">
        <div class="sign-line"></div>
        <div class="sign-caption">Signature</div>
      </div>
    </div>
  </div>

  <div class="footnote">*SU stands for Sabarmati University</div>

</div>

</body>
</html>
