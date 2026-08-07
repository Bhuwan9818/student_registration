<?php
/**
 * Board of Open Schooling & Skill Education (BOSSE), Sikkim — Admission Form
 * Pixel-accurate recreation of the official paper form (2 pages).
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
 *   $subjects = ... (rows of {label, subject, code} — optional; falls back
 *                     to the standard Language/Vocational/Other rows below)
 *
 * NOTE: This recreates the two core pages of the printed form (Admission
 * Form + Contact/Examination/Payment/Declaration). The optional annexure
 * pages ("Subjects of TOC" / "Subjects of Part Admission" and the "Format
 * for Self Certificate of Literacy") are conditional/static handouts, not
 * tied to student data — ask if you'd like those added as extra pages too.
 */

require_once __DIR__ . '/../shared/helpers.php';

if (!isset($student)) {
    // Sample data so this file can be previewed standalone
    $student = [
        'application_no'    => '',
        'remarks'           => '',
        'course_applied'    => 'Secondary',        // Secondary | Senior Secondary
        'admission_type'    => 'Full Subjects',    // Full Subjects | Transfer of Credit | Part Admission
        'application_date'  => '',
        'gender'            => 'Male',             // Male | Female | Transgender
        'dob'               => '2009-08-15',
        'first_name'        => 'RAHUL',
        'last_name'         => 'SHARMA',
        'father_name'       => 'SURESH SHARMA',
        'mother_name'       => 'GEETA SHARMA',
        'aadhar_no'         => '',
        'email'             => 'rahul@example.com',
        'marital_status'    => 'Un-Married',       // Married | Un-Married | Divorced | Widowed
        'category'          => 'GEN',              // SC | ST | OBC | GEN
        'minority'          => false,
        'minority_specify'  => '',
        'nationality'       => 'Indian',
        'domicile'          => 'Sikkim',           // Sikkim | Other
        'domicile_specify'  => '',
        'religion'          => 'Hindu',
        'differently_abled' => 'No',               // Yes | No
        'differently_abled_specify' => '',

        'address'           => '123 Milan Colony, Central Delhi',
        'city'              => 'Delhi',
        'state'             => 'Delhi',
        'pincode'           => '110002',
        'mobile'            => '9818404944',
        'current_address'   => '',
        'guardian_name'     => 'Suresh Sharma',
        'guardian_occupation' => 'Business',
        'guardian_mobile'   => '9811122233',
        'guardian_email'    => 'suresh@example.com',

        'last_class'        => '10th',
        'last_marks'        => '410/500',
        'last_percentage'   => '82%',
        'last_school_board' => 'CBSE Board',
        'division'          => 'First',

        'medium_of_instruction' => 'English',      // English | Hindi | Nepali

        'payment_mode'      => 'Online',           // DD | Online
        'payment_date'      => '',
        'payment_amount'    => '',
        'payment_reference' => '',
        'payment_bank'      => '',
    ];
    $subjects = [
        ['label' => 'Language Subject', 'subject' => 'English', 'code' => ''],
        ['label' => 'Vocational Subject', 'subject' => '', 'code' => ''],
    ];
}
$candidateName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
$subjects = $subjects ?? [];

$dobDay = $dobMonth = $dobYear = '';
if (!empty($student['dob'])) {
    [$dobYear, $dobMonth, $dobDay] = array_pad(explode('-', $student['dob']), 3, '');
    $dobYear = substr($dobYear, -2); // form uses YY
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admission Form - Board of Open Schooling &amp; Skill Education (BOSSE)</title>
<link rel="stylesheet" href="../shared/common.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="no-print">
  <button onclick="window.print()">Print / Save as PDF</button>
</div>

<!-- ============================================================ -->
<!-- PAGE 1                                                        -->
<!-- ============================================================ -->
<div class="sheet bosse-sheet">
  <div class="top-bar-row">
    <div class="top-bar-cell">
      <div class="top-bar-label">Remarks, if any (Official Use Only)</div>
      <div class="top-bar-value"><?= v($student['remarks'] ?? '') ?></div>
    </div>
    <div class="top-bar-cell">
      <div class="top-bar-label">Application Number</div>
      <div class="top-bar-value"><?= v($student['application_no'] ?? '') ?></div>
    </div>
  </div>

  <!-- ================= HEADER ================= -->
  <table class="header-table">
    <tr>
      <td class="header-logo"><img src="assets/bosse-logo.jpeg" alt="BOSSE Logo" class="uni-logo"></td>
      <td class="header-center">
        <div class="uni-name">BOARD OF OPEN SCHOOLING &amp; SKILL EDUCATION</div>
        <div class="uni-sub">Established by the Govt. of Sikkim</div>
        <div class="uni-address">Address : Near Indira Bypass, NH-10, Gangtok, East Sikkim- 737102 &nbsp;|&nbsp; Ph: 03592-295335</div>
        <div class="uni-address">E-mail: admission@bosse.ac.in &nbsp;|&nbsp; www.bosse.ac.in</div>
      </td>
    </tr>
  </table>

  <div class="form-title">ADMISSION FORM</div>

  <table class="top-grid">
    <tr>
      <td class="top-grid-fields">

        <div class="section-heading">INSTRUCTIONS</div>
        <ol class="instructions-list">
          <li>Please read the form carefully before filling it.</li>
          <li>Use only Blue or Black Pen to fill up the Form in English using CAPITAL/BLOCK LETTERS only.</li>
          <li>Please keep a photocopy of the Form, before submitting, as a ready reference.</li>
          <li>Incomplete Form will not be considered.</li>
        </ol>

      </td>
      <td class="photo-cell">
        <div class="photo-box" style="width:110px;height:120px;">
          <?php if (!empty($student['photo_path'])): ?>
            <img src="<?= v($student['photo_path']) ?>" alt="Photo">
          <?php else: ?>
            Affix latest Passport size Color Photograph
          <?php endif; ?>
        </div>
      </td>
    </tr>
  </table>

  <div class="section-heading">COURSE / PROGRAMME DETAILS:</div>
  <div class="course-line"><strong>Course Applied For:</strong> Secondary <?= checkBox($student['course_applied'] === 'Secondary') ?> / Senior Secondary <?= checkBox($student['course_applied'] === 'Senior Secondary') ?> <span class="tick-note">(Tick Mark)</span></div>
  <div class="course-line"><strong>Full Subjects</strong> <?= checkBox($student['admission_type'] === 'Full Subjects') ?> / <strong>Transfer of Credit</strong> <?= checkBox($student['admission_type'] === 'Transfer of Credit') ?> / <strong>Part Admission</strong> <?= checkBox($student['admission_type'] === 'Part Admission') ?> <span class="tick-note">(Tick Mark)</span></div>
  <div class="course-line"><strong>Date of Application:</strong> <span class="underline-fill"><?= v($student['application_date'] ?? '') ?></span></div>

  <div class="section-heading">PERSONAL INFORMATION <span class="section-note">(IN BLOCK LETTERS)</span></div>

  <div class="pi-row">
    <span class="pi-num">1.</span> <span class="pi-label">Gender (tick)</span>
    <span class="inline-check"><?= checkBox($student['gender'] === 'Male') ?> Male</span>
    <span class="inline-check"><?= checkBox($student['gender'] === 'Female') ?> Female</span>
    <span class="inline-check"><?= checkBox($student['gender'] === 'Transgender') ?> Transgender</span>
    <span class="dob-caption-inline">Date of Birth</span>
    <div class="dob-boxes">
      <div class="dob-group"><div class="dob-caption">DD</div><?= charBoxes($dobDay, 2) ?></div>
      <span class="dob-dash">-</span>
      <div class="dob-group"><div class="dob-caption">MM</div><?= charBoxes($dobMonth, 2) ?></div>
      <span class="dob-dash">-</span>
      <div class="dob-group"><div class="dob-caption">YY</div><?= charBoxes($dobYear, 2) ?></div>
    </div>
  </div>

  <div class="pi-row">
    <span class="pi-num">2.</span> <span class="pi-label">Name of Applicant</span>
    <?= charBoxes($candidateName, 20) ?>
  </div>
  <div class="pi-row">
    <span class="pi-num">3.</span> <span class="pi-label">Father's Name</span>
    <?= charBoxes($student['father_name'] ?? '', 20) ?>
  </div>
  <div class="pi-row">
    <span class="pi-num">4.</span> <span class="pi-label">Mother's Name</span>
    <?= charBoxes($student['mother_name'] ?? '', 20) ?>
  </div>
  <div class="pi-row">
    <span class="pi-num">5.</span> <span class="pi-label">Aadhar No.</span>
    <?= charBoxes($student['aadhar_no'] ?? '', 20) ?>
  </div>
  <div class="pi-row">
    <span class="pi-num">6.</span> <span class="pi-label">E-mail Id</span>
    <?= charBoxes($student['email'] ?? '', 20) ?>
  </div>

  <div class="pi-row">
    <span class="pi-num">7.</span> <span class="pi-label">Marital Status</span>
    <span class="inline-check"><?= checkBox($student['marital_status'] === 'Married') ?> Married</span>
    <span class="inline-check"><?= checkBox($student['marital_status'] === 'Un-Married') ?> Un-Married</span>
    <span class="inline-check"><?= checkBox($student['marital_status'] === 'Divorced') ?> Divorced</span>
    <span class="inline-check"><?= checkBox($student['marital_status'] === 'Widowed') ?> Widowed</span>
  </div>

  <div class="pi-row">
    <span class="pi-num">8.</span> <span class="pi-label">Category</span>
    <span class="inline-check">SC <?= checkBox($student['category'] === 'SC') ?></span>
    <span class="inline-check">ST <?= checkBox($student['category'] === 'ST') ?></span>
    <span class="inline-check">OBC <?= checkBox($student['category'] === 'OBC') ?></span>
    <span class="inline-check">GEN <?= checkBox($student['category'] === 'GEN') ?></span>
    <span class="pi-label">Minority</span>
    <?= checkBox(!empty($student['minority'])) ?>
    <span class="pi-label">Specify</span>
    <span class="box-fill"><?= v($student['minority_specify'] ?? '') ?></span>
  </div>

  <div class="pi-row">
    <span class="pi-num">9.</span> <span class="pi-label">Nationality</span>
    <span class="box-fill wide"><?= v($student['nationality'] ?? '') ?></span>
    <span class="pi-label">Domicile</span>
    <span class="inline-check">Sikkim <?= checkBox($student['domicile'] === 'Sikkim') ?></span>
    <span class="inline-check">Other <?= checkBox($student['domicile'] === 'Other') ?></span>
    <span class="pi-label">Specify</span>
    <span class="box-fill"><?= v($student['domicile_specify'] ?? '') ?></span>
  </div>

  <div class="pi-row">
    <span class="pi-num">10.</span> <span class="pi-label">Religion</span>
    <span class="box-fill wide"><?= v($student['religion'] ?? '') ?></span>
  </div>

  <div class="pi-row">
    <span class="pi-num">12.</span> <span class="pi-label">Whether differently abled</span>
    <span class="inline-check">Yes <?= checkBox(strtolower($student['differently_abled'] ?? '') === 'yes') ?></span>
    <span class="inline-check">No <?= checkBox(strtolower($student['differently_abled'] ?? '') === 'no') ?></span>
    <span class="pi-label">If yes, specify</span>
    <span class="box-fill"><?= v($student['differently_abled_specify'] ?? '') ?></span>
  </div>

</div>

<!-- ============================================================ -->
<!-- PAGE 2                                                        -->
<!-- ============================================================ -->
<div class="sheet bosse-sheet">

  <div class="section-heading page2-first">CONTACT DETAILS</div>

  <div class="contact-block">
    <div class="contact-banner">Permanent Address (Don't Repeat Name)</div>
    <div class="contact-lines">
      <div class="dotted-line"><?= v($student['address'] ?? '') ?></div>
      <div class="dotted-line">&nbsp;</div>
    </div>
    <div class="contact-row">
      <span class="inline-label">City</span><span class="dotted-fill"><?= v($student['city'] ?? '') ?></span>
      <span class="inline-label">State</span><span class="dotted-fill"><?= v($student['state'] ?? '') ?></span>
      <span class="inline-label">Pin Code</span><span class="dotted-fill"><?= v($student['pincode'] ?? '') ?></span>
    </div>
    <div class="contact-row full-row">
      <span class="inline-label">Permanent Mobile No. (On which all the important information to be delivered)</span>
      <span class="underline-fill"><?= v($student['mobile'] ?? '') ?></span>
    </div>
  </div>

  <div class="contact-block">
    <div class="contact-banner">Current Address for Communication, if Different</div>
    <div class="contact-lines">
      <div class="dotted-line"><?= v($student['current_address'] ?? '') ?></div>
      <div class="dotted-line">&nbsp;</div>
    </div>
    <div class="contact-row">
      <span class="inline-label">Parent/Guardian Name</span><span class="underline-fill"><?= v($student['guardian_name'] ?? '') ?></span>
      <span class="inline-label">Parent Occupation</span><span class="underline-fill"><?= v($student['guardian_occupation'] ?? '') ?></span>
    </div>
    <div class="contact-row">
      <span class="inline-label">Parent/Guardian Contact no.</span><span class="underline-fill"><?= v($student['guardian_mobile'] ?? '') ?></span>
      <span class="inline-label">Email Address</span><span class="underline-fill"><?= v($student['guardian_email'] ?? '') ?></span>
    </div>
  </div>

  <div class="section-heading">DETAILS OF LAST CLASS/EXAMINATION PASSED*</div>
  <div class="contact-row">
    <span class="inline-label">Class/Examination :</span><span class="underline-fill"><?= v($student['last_class'] ?? '') ?></span>
    <span class="inline-label">Marks :</span><span class="underline-fill short"><?= v($student['last_marks'] ?? '') ?></span>
    <span class="inline-label">Percentage (%) :</span><span class="underline-fill short"><?= v($student['last_percentage'] ?? '') ?></span>
  </div>
  <div class="contact-row full-row">
    <span class="inline-label">Name of School/Board :</span><span class="underline-fill"><?= v($student['last_school_board'] ?? '') ?></span>
  </div>
  <div class="contact-row">
    <span class="inline-label">Division :</span><span class="underline-fill"><?= v($student['division'] ?? '') ?></span>
    <span class="inline-label">Percentage (%) :</span><span class="underline-fill short"><?= v($student['last_percentage'] ?? '') ?></span>
  </div>

  <table class="form-table subjects-table">
    <tr>
      <th style="width:5%;">&nbsp;</th>
      <th style="width:70%;">Subjects Choosen</th>
      <th>Subject Code</th>
    </tr>
    <tr><td>1</td><td>Language Subject <?= v($subjects[0]['subject'] ?? '') ?></td><td><?= v($subjects[0]['code'] ?? '') ?></td></tr>
    <tr><td>2</td><td>Vocational Subject <?= v($subjects[1]['subject'] ?? '') ?></td><td><?= v($subjects[1]['code'] ?? '') ?></td></tr>
    <tr><td rowspan="3">3</td><td>Other Subjects:<br>(1)</td><td>&nbsp;</td></tr>
    <tr><td>(2)</td><td>&nbsp;</td></tr>
    <tr><td>(3)</td><td>&nbsp;</td></tr>
  </table>

  <div class="section-subnote">Additional Subjects (If any)</div>
  <table class="form-table subjects-table">
    <tr><td style="width:5%;">6</td><td style="width:70%;">&nbsp;</td><td>&nbsp;</td></tr>
    <tr><td>7</td><td>&nbsp;</td><td>&nbsp;</td></tr>
  </table>

  <div class="contact-row full-row">
    <span class="inline-label">Medium of Instruction opted for: English / Hindi / Nepali :</span>
    <span class="underline-fill"><?= v($student['medium_of_instruction'] ?? '') ?></span>
  </div>

  <div class="note-block">
    <strong>Note:</strong> &bull; 'Self Attested Copies of Certificates/Marks Sheets should be attached'<br>
    &bull; 'If you are an applicant for Secondary Course and do not have any school leaving certificate, please attach a self certificate of Literacy, saying that you know how to read and write. The format of the self certificate is attached.'
  </div>

  <table class="form-table payment-table">
    <tr>
      <th style="width:14%;">Mode of Payment</th>
      <th style="width:14%;">Date</th>
      <th style="width:16%;">Amount</th>
      <th style="width:26%;">Draft No./UTR NO.</th>
      <th>Name of the Bank/Branch</th>
    </tr>
    <tr>
      <td class="payment-mode-cell">
        <div><?= checkBox($student['payment_mode'] === 'DD') ?> DD</div>
        <div><?= checkBox($student['payment_mode'] === 'Online') ?> Online</div>
      </td>
      <td><?= v($student['payment_date'] ?? '') ?></td>
      <td><?= v($student['payment_amount'] ?? '') ?></td>
      <td><?= v($student['payment_reference'] ?? '') ?></td>
      <td><?= v($student['payment_bank'] ?? '') ?></td>
    </tr>
  </table>

  <div class="section-heading">DECLARATION BY CANDIDATE</div>
  <div class="declaration-text">I hereby declare that I have carefully read the instructions and all the informations furnished by me are correct.</div>

  <div class="sign-row">
    <span class="inline-label">Candidate's Signature</span><span class="underline-fill"></span>
    <span class="inline-label">Parent's /Guardian's Signature</span><span class="underline-fill"></span>
  </div>
  <div class="sign-row">
    <span class="inline-label">Date</span><span class="underline-fill"></span>
    <span class="inline-label">Place</span><span class="underline-fill"></span>
  </div>

</div>

<!-- ============================================================ -->
<!-- PAGE 3 — Subjects of TOC / Subjects of Part Admission        -->
<!-- (only relevant when Transfer of Credit / Part Admission was  -->
<!-- ticked on page 1; static structure, blank rows for the       -->
<!-- candidate/office to fill in by hand)                         -->
<!-- ============================================================ -->
<div class="sheet bosse-sheet">

  <div class="banner-title">Subjects of TOC (If applicable)</div>
  <table class="form-table toc-table">
    <tr>
      <th style="width:42%;">TOC Subjects</th>
      <th style="width:42%;">Subjects of BOSSE</th>
      <th>Code</th>
    </tr>
    <?php for ($i = 1; $i <= 4; $i++): ?>
      <tr class="tall-row"><td>(<?= $i ?>)</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <?php endfor; ?>
  </table>

  <div class="banner-title spaced">Subjects of Part Admission</div>
  <table class="form-table toc-table">
    <tr>
      <th style="width:84%;">Part Admission Subjects</th>
      <th>Code</th>
    </tr>
    <?php for ($i = 1; $i <= 4; $i++): ?>
      <tr class="tall-row"><td>(<?= $i ?>)</td><td>&nbsp;</td></tr>
    <?php endfor; ?>
  </table>

</div>

<!-- ============================================================ -->
<!-- PAGE 4 — Format for Self Certificate of Literacy              -->
<!-- (only applicable to Secondary Course applicants without a    -->
<!-- school leaving certificate — see note on page 2)              -->
<!-- ============================================================ -->
<div class="sheet bosse-sheet">

  <div class="banner-title">FORMAT FOR SELF CERTIFICATE OF LITERACY</div>
  <div class="literacy-subnote">(To be filled in only by Applicants of Secondary Course)</div>

  <div class="literacy-text">
    I, <span class="underline-fill grow"><?= v($candidateName) ?></span> (Name), Applicant for Secondary Course at the
    BOSSE (Board of Open Schooling and Skill Education), certify that I am literate, I can read and write
    <span class="underline-fill grow"><?= v($student['medium_of_instruction'] ?? '') ?></span> (Medium of Instruction). I understand that self learning is important in the open schooling
    system and I take the responsibility of my own studies. I am desirous of continuing my education. I am sending
    this application form to BOSSE for fulfilling this desire of mine. I am grateful to BOSSE for giving me this second
    chance, this opportunity to continue my education.
  </div>

  <div class="literacy-sign-grid">
    <div class="literacy-sign-row">
      <span class="inline-label">Date:</span><span class="underline-fill"></span>
      <span class="inline-label">Signature of Applicant:</span><span class="underline-fill"></span>
    </div>
    <div class="literacy-sign-row">
      <span class="inline-label">Place:</span><span class="underline-fill"></span>
      <span class="inline-label">Name of Applicant:</span><span class="underline-fill"><?= v($candidateName) ?></span>
    </div>
    <div class="literacy-sign-row">
      <span class="inline-label wide">Countersigned by Parent/Guardian's:</span><span class="underline-fill"></span>
    </div>
    <div class="literacy-sign-row">
      <span class="inline-label wide">Name of Parent/Guardian's:</span><span class="underline-fill"><?= v($student['guardian_name'] ?? '') ?></span>
    </div>
  </div>

</div>

</body>
</html>
