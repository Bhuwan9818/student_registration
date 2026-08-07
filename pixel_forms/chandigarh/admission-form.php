<?php
/**
 * Chandigarh University — Admission Form (3 pages)
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
 *   $employment = ... (rows of {employer, designation, tenure} — optional)
 *   $pdcCheques = ... (rows of {cheque_no, date, bank, amount} — optional, up to 4)
 */

require_once __DIR__ . '/../shared/helpers.php';

if (!isset($student)) {
    // Sample data so this file can be previewed standalone
    $student = [
        'enrollment_no'   => '',
        'course_code'     => '',
        'course_name'     => 'MBA',
        'elective'        => 'Marketing',
        'fee_plan'        => 'Instalment',   // Instalment | Lumpsum | Annual
        'admission_year'  => '2026',
        'admission_cycle' => 'JULY',         // JAN | JULY
        'first_name'      => 'RAHUL',
        'last_name'       => 'SHARMA',
        'father_name'     => 'SURESH SHARMA',
        'mother_name'     => 'GEETA SHARMA',
        'gender'          => 'Male',
        'dob'             => '2000-08-15',
        'aadhar_no'       => '',
        'address'         => '123 Milan Colony',
        'city'            => 'Delhi',
        'state'           => 'Delhi',
        'pincode'         => '110002',
        'district'        => 'Central Delhi',
        'alt_mobile'      => '011-23456789',
        'mobile'          => '9818404944',
        'email'           => 'rahul@example.com',
        'alt_email'       => '',
        'guardian_mobile' => '',
        'nationality'     => 'Indian',
        'category'        => 'General',
        'employment_status' => 'Unemployed',
        'debarred'        => 'No',
        'debarred_details' => '',
        'photo_path'      => '',
        'signature_path'  => '',
        'application_no'  => '',

        'payment_mode'    => 'Online',       // Cash | Cheque | DD | Online
        'txn_id'          => '',
        'payment_date'    => '',
        'payment_bank'    => '',
        'payment_amount'  => '',

        'doc_degree_cert'      => false,
        'doc_diploma_cert'     => false,
        'doc_provisional_cert' => false,
        'doc_degree_marksheet' => false,
        'doc_marriage_cert'    => false,
        'doc_photos'           => true,
        'doc_service_cert'     => false,
        'doc_passport_copy'    => false,
        'doc_photo_id'         => true,
        'doc_10th_marksheet'   => true,
        'doc_12th_marksheet'   => true,

        'signature_place' => '',
        'signature_date'  => '',
    ];
    $academics = [
        ['level' => '10th', 'institution_board' => 'CBSE Board', 'year_of_passing' => '2016', 'percentage' => '88'],
        ['level' => '12th', 'institution_board' => 'CBSE Board', 'year_of_passing' => '2018', 'percentage' => '82'],
    ];
    $employment = [];
    $pdcCheques = [];
}
$academics = $academics ?? [];
$employment = $employment ?? [];
$pdcCheques = $pdcCheques ?? [];

$dobDay = $dobMonth = $dobYear = '';
if (!empty($student['dob'])) {
    [$dobYear, $dobMonth, $dobDay] = array_pad(explode('-', $student['dob']), 3, '');
}
$candidateName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admission Form - Chandigarh University</title>
<link rel="stylesheet" href="../shared/common.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="no-print">
  <button onclick="window.print()">Print / Save as PDF</button>
</div>

<!-- ============================================================ -->
<!-- PAGE 1 — Admission Form                                       -->
<!-- ============================================================ -->
<div class="sheet">

  <table class="header-table">
    <tr>
      <td class="header-left">
        <h1 class="uni-name">CHANDIGARH UNIVERSITY</h1>
        <div class="uni-sub">Chandigarh University NH-95 Chandigarh-Ludhiana Highway, Mohali, Punjab (INDIA)</div>
      </td>
      <td class="header-right">
        <img src="assets/chandigarh-logo.jpeg" alt="Chandigarh University Logo" class="uni-logo">
      </td>
    </tr>
  </table>

  <div class="form-title">ADMISSION FORM</div>

  <div class="instructions">
    All entries must be filled by the candidate himself/ herself in capital letters. Put (tick) for Yes, &times; for NO and "NA"
    where not applicable in the box. The application form consists of two pages
  </div>

  <table class="top-grid">
    <tr>
      <td class="top-grid-fields">

        <div class="field-row">
          <div class="field-label two-line">ENROLMENT N0.<br>(LEAVE BLANK)</div>
          <?= charBoxes($student['enrollment_no'] ?? '', 20) ?>
        </div>

        <div class="field-row">
          <div class="field-label">COURSE CODE</div>
          <?= charBoxes($student['course_code'] ?? '', 10) ?>
          <div class="field-label inline-label">PROGRAMME</div>
          <div class="dotted-fill"><?= v($student['course_name']) ?></div>
        </div>

        <div class="field-row">
          <div class="field-label">ELECTIVE</div>
          <div class="dotted-fill wide"><?= v($student['elective']) ?></div>
        </div>

        <div class="field-row fee-plan-row">
          <div class="field-label">FEE PLAN</div>
          <span class="inline-check">Instalment <?= checkBox(strtolower($student['fee_plan'] ?? '') === 'instalment') ?></span>
          <span class="inline-check">Lumpsum <?= checkBox(strtolower($student['fee_plan'] ?? '') === 'lumpsum') ?></span>
          <span class="inline-check">Annual <?= checkBox(strtolower($student['fee_plan'] ?? '') === 'annual') ?></span>
          <div class="field-label inline-label">Year</div>
          <div class="dotted-fill year-fill"><?= v($student['admission_year']) ?></div>
          <span class="inline-check">JAN <?= checkBox(strtoupper($student['admission_cycle'] ?? '') === 'JAN') ?></span>
          <span class="inline-check">JULY <?= checkBox(strtoupper($student['admission_cycle'] ?? '') === 'JULY') ?></span>
        </div>

      </td>
      <td class="photo-cell">
        <div class="photo-box" style="width:110px;height:130px;">
          <?php if (!empty($student['photo_path'])): ?>
            <img src="<?= v($student['photo_path']) ?>" alt="Photo">
          <?php else: ?>
            Paste box- sized photograph of candidate, duly attached by head of the institution. Do not use pin or stapler.
          <?php endif; ?>
        </div>
        <div class="photo-note">Please enclose four identical photographs along with the application form</div>
        <div class="signature-box" style="width:110px;height:38px;">
          <?php if (!empty($student['signature_path'])): ?>
            <img src="<?= v($student['signature_path']) ?>" alt="Signature">
          <?php endif; ?>
        </div>
        <div class="photo-note">Signature of candidate (in full)</div>
      </td>
    </tr>
  </table>

  <div class="secondary-note bordered">(As entered in Secondary/ Senior Secondary Certificate)</div>

  <div class="field-row">
    <div class="field-label wide-label">NAME OF<br>CANDIDATE:</div>
    <?= charBoxes($candidateName, 32) ?>
  </div>
  <div class="field-row">
    <div class="field-label wide-label">FATHER'S NAME:</div>
    <?= charBoxes($student['father_name'] ?? '', 32) ?>
  </div>
  <div class="field-row">
    <div class="field-label wide-label">MOTHER'S NAME:</div>
    <?= charBoxes($student['mother_name'] ?? '', 32) ?>
  </div>

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

  <div class="field-row">
    <div class="field-label">AADHAR NUMBER</div>
    <div class="dotted-fill wide"><?= v($student['aadhar_no'] ?? '') ?></div>
  </div>

  <table class="address-grid">
    <tr>
      <td class="address-col">
        <div class="field-label">PERMANENT<br>ADDRESS:</div>
        <?= charBoxes($student['address'] ?? '', 30) ?>
        <?= charBoxes('', 30) ?>
        <?= charBoxes('', 30) ?>
        <div class="field-row pincode-row">
          <span class="inline-label">PIN CODE</span>
          <?= charBoxes($student['pincode'] ?? '', 8) ?>
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
        <?= charBoxes('', 30) ?>
        <div class="field-row pincode-row">
          <span class="inline-label">PIN CODE</span>
          <?= charBoxes($student['pincode'] ?? '', 8) ?>
        </div>
        <div class="underline-row">CITY <span class="dotted-fill"><?= v($student['city']) ?></span> STATE<span class="dotted-fill"><?= v($student['state']) ?></span></div>
        <div class="underline-row">STD CODE <span class="dotted-fill"><?= v($student['district']) ?></span></div>
        <div class="underline-row">PH. No. <span class="dotted-fill"><?= v('') ?></span> MOB. No.<span class="dotted-fill"><?= v($student['guardian_mobile']) ?></span></div>
        <div class="underline-row">E-MAIL: <span class="dotted-fill"><?= v($student['alt_email']) ?></span></div>
      </td>
    </tr>
  </table>
  <div class="secondary-note center">(Any changes in address should be immediately communicated to the University)</div>

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
    <span class="field-label debarred-label">HAVE YOU EVER BEEN DEBARRED BY ANY UNIVERSITY/BOARD?</span>
    <span class="inline-check">NO <?= checkBox(strtolower($student['debarred'] ?? 'no') === 'no') ?></span>
    <span class="inline-check">YES <?= checkBox(strtolower($student['debarred'] ?? '') === 'yes') ?></span>
    <span class="small-note">If yes, give details</span>
    <span class="dotted-fill"><?= v($student['debarred_details'] ?? '') ?></span>
  </div>

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
    <?php for ($i = count($rows); $i < 5; $i++): ?>
      <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <?php endfor; ?>
  </table>

</div>

<!-- ============================================================ -->
<!-- PAGE 2 — Payment of Fee / Documents / Employment / Undertaking -->
<!-- ============================================================ -->
<div class="sheet">

  <div class="page2-banner">CHANDIGARH UNIVERSITY</div>
  <div class="page2-address">Chandigarh University NH-95 Chandigarh-Ludhiana Highway, Mohali, Punjab (INDIA)</div>
  <div class="page2-title">PAYMENT OF FEE</div>

  <div class="numbered-row"><span class="field-label bold">Mode of Payment</span>
    <span class="inline-check">Cash <?= checkBox($student['payment_mode'] === 'Cash') ?></span>
    <span class="inline-check">Cheque <?= checkBox($student['payment_mode'] === 'Cheque') ?></span>
    <span class="inline-check">DD <?= checkBox($student['payment_mode'] === 'DD') ?></span>
    <span class="inline-check">Online <?= checkBox($student['payment_mode'] === 'Online') ?></span>
  </div>
  <div class="numbered-row">
    <span class="field-label">DD/Cheque No./ Online Transaction ID:</span>
    <span class="underline-fill"><?= v($student['txn_id'] ?? '') ?></span>
  </div>
  <div class="numbered-row">
    <span class="field-label">Date:</span><span class="underline-fill short"><?= v($student['payment_date'] ?? '') ?></span>
    <span class="field-label">Bank Name:</span><span class="underline-fill"><?= v($student['payment_bank'] ?? '') ?></span>
    <span class="field-label">Amount:</span><span class="underline-fill short"><?= v($student['payment_amount'] ?? '') ?></span>
  </div>

  <div class="section-subheading">Incase of installment Post Dated Cheques (PDC) details</div>
  <?php for ($i = 0; $i < 4; $i++): $pdc = $pdcCheques[$i] ?? []; ?>
  <div class="numbered-row">
    <span class="field-label">Cheque No:</span><span class="underline-fill short"><?= v($pdc['cheque_no'] ?? '') ?></span>
    <span class="field-label">Date:</span><span class="underline-fill short"><?= v($pdc['date'] ?? '') ?></span>
    <span class="field-label">Bank Name:</span><span class="underline-fill"><?= v($pdc['bank'] ?? '') ?></span>
    <span class="field-label">Amount:</span><span class="underline-fill short"><?= v($pdc['amount'] ?? '') ?></span>
  </div>
  <?php endfor; ?>

  <div class="section-subheading">Colour Scan Copy of following documents attached herewith (Please Tick):</div>
  <div class="doc-checklist">
    <span class="doc-item"><?= checkBox(!empty($student['doc_degree_cert'])) ?> Degree Certificate</span>
    <span class="doc-item"><?= checkBox(!empty($student['doc_diploma_cert'])) ?> Diploma Certificate</span>
    <span class="doc-item"><?= checkBox(!empty($student['doc_provisional_cert'])) ?> Provisional Certificate</span>
    <span class="doc-item"><?= checkBox(!empty($student['doc_degree_marksheet'])) ?> Degree all year Marksheet</span>
    <span class="doc-item"><?= checkBox(!empty($student['doc_marriage_cert'])) ?> Marriage Certificate</span>
    <span class="doc-item"><?= checkBox(!empty($student['doc_photos'])) ?> Photos 3 nos</span>
    <span class="doc-item"><?= checkBox(!empty($student['doc_service_cert'])) ?> Service Certificate</span>
    <span class="doc-item"><?= checkBox(!empty($student['doc_passport_copy'])) ?> Copy of Passports</span>
    <span class="doc-item"><?= checkBox(!empty($student['doc_photo_id'])) ?> Photo Identity</span>
    <span class="doc-item"><?= checkBox(!empty($student['doc_10th_marksheet'])) ?> 10th Marksheet</span>
    <span class="doc-item"><?= checkBox(!empty($student['doc_12th_marksheet'])) ?> 12th Marksheet</span>
  </div>

  <div class="page2-title">EMPLOYMENT DETAILS</div>
  <table class="form-table employment-table">
    <tr>
      <th style="width:10%;">S. No.</th>
      <th style="width:38%;">EMPLOYER NAME</th>
      <th style="width:30%;">DESIGNATION</th>
      <th>TENURE</th>
    </tr>
    <?php
      $eRows = array_filter($employment, fn($e) => !empty($e['employer']));
      $sn = 1;
      foreach ($eRows as $e):
    ?>
      <tr>
        <td><?= $sn++ ?></td>
        <td><?= v($e['employer'] ?? '') ?></td>
        <td><?= v($e['designation'] ?? '') ?></td>
        <td><?= v($e['tenure'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
    <?php for ($i = count($eRows); $i < 3; $i++): ?>
      <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    <?php endfor; ?>
  </table>

  <div class="office-use-box">
    <div class="office-use-title">FOR OFFICE USE ONLY</div>
    <div class="office-use-grid">
      <div>Application No.<span class="underline-fill"><?= v($student['application_no'] ?? '') ?></span></div>
      <div>Challan No.<span class="underline-fill"></span></div>
      <div>Reg No.<span class="underline-fill"></span></div>
    </div>
    <div class="office-use-row"><?= checkBox() ?> Approved &nbsp; <?= checkBox() ?> Confirmed &nbsp; <?= checkBox() ?> Provisional</div>
    <div class="office-use-row">Admission Status: <span class="underline-fill"></span></div>
    <div class="office-use-row">Signature: <span class="underline-fill"></span> Date: <span class="underline-fill short"></span></div>
  </div>

  <div class="page2-title">CERTIFICATE OF UNDERTAKING:</div>
  <ol class="undertaking-list">
    <li>I have understood the university guidelines, fees payment terms, all the other terms and conditions of the university. I agree to abide by the university policy and guidelines issued from time to time.</li>
    <li>I am eligible for the program as I have completed my education/qualifying degree from government a recognized university.</li>
    <li>All educational documents submitted are true copies, if found illegitimate, my admission can be forfeited without any refund. I take complete responsibility and the University would not be liable for any related consequences.</li>
    <li>I understand that in case I withdraw from the program I will not be entitled to claim any refund of fees amount paid by me.</li>
    <li>I agree that I will pay the fees to CHANDIGARH UNIVERSITY whether or not I continue in the program, I understand the Jurisdiction for all disputes (if any) relating to the Institute is only/exclusively Mohali, Punjab, India.</li>
    <li>I hereby declare that the information provided by me in the application is true and correct to the best of my knowledge.</li>
    <li>My signature below certifies that I have read, understood and I agree to the rules and regulations, including "Legal Aspects" and my financial responsibilities towards the said program.</li>
    <li>Submission of Fees and Admission form does not mean that admission is confirmed. The admission will be treated as enrolled only after Registration Number has been generated by University.</li>
    <li>I am aware that I have applied for the Online Programs offered by the university and my course delivery would happen through the learning management system.</li>
  </ol>

  <div class="sign-row">
    <span class="field-label">Place:</span><span class="underline-fill"><?= v($student['signature_place'] ?? '') ?></span>
    <span class="field-label">Date:</span><span class="underline-fill"><?= v($student['signature_date'] ?? '') ?></span>
    <span class="field-label">(Signature of Applicant)</span><span class="underline-fill"></span>
  </div>

</div>

<!-- ============================================================ -->
<!-- PAGE 3 — Terms & Conditions                                   -->
<!-- ============================================================ -->
<div class="sheet">

  <div class="page2-title">Terms &amp; Condition:</div>

  <div class="legal-text">
    <p>CHANDIGARH UNIVERSITY, reserves the right to change the body of knowledge, prescribed books, the curriculum, examination pattern, evaluation system, rules and regulations. The students are governed by the latest regulations applicable to them during the relevant academic year. This document is designed to provide the prospective students with information only. CHANDIGARH UNIVERSITY, Mohali, Punjab has no liability of any kind to any person for providing this information, whether or not such persons rely on it and even if they inform CHANDIGARH UNIVERSITY of their reliance on it.</p>

    <p>This document may contain forward-looking statements like, but not limited to, general market, macro-economic, governmental and regulatory trends, technological developments, legislative developments, court decisions, scope for further studies, career opportunities for graduates from CHANDIGARH UNIVERSITY. Such forward-looking statements contained herein are subject to certain risks and uncertainties that could cause actual results to differ materially from those reflected in the forward-looking statements. CHANDIGARH UNIVERSITY undertakes no duty to update any forward-looking statements, to reflect future events or circumstances.</p>

    <p><strong>Enrollment Agreement:</strong> The "Application Form for Enrollment" is the Enrollment Agreement (hereinafter referred to as the Agreement) between the applicants who wish to enroll for CHANDIGARH UNIVERSITY Programs.</p>

    <p><strong>Entire Agreement:</strong> This Agreement constitutes and expresses the entire agreement and understanding between CHANDIGARH UNIVERSITY and the students of CHANDIGARH UNIVERSITY in reference to all matters herein referred to, all previous discussions, promises, representations and understandings relative thereto, if any, had between the parties hereto, being herein merged.</p>

    <p><strong>Conclusion of the Agreement:</strong> The Agreement is irrevocably concluded after the applicant signs the application form and submits it along with the required amount, physically, electronically or otherwise.</p>

    <p><strong>No Third Party Beneficiaries:</strong> Enrollment of any student into the Program, shall not entitle any person (including, without limitation, members) to any rights as third party beneficiary.</p>

    <p><strong>Balance of Dues:</strong> The liability of the student to pay the balance of dues continues until the last installment is cleared even if the student, for any reason, withdraws from/discontinues the pursuit of the program. Wherever students have arrears of payment, they will not be permitted to register for the examinations or their examination result will not be released and their mark-sheets, pass certificates will not be issued. Further, such students will be considered as inactive on the rolls and their names are liable to be removed from the records.</p>

    <p><strong>No Obligation to Services:</strong> CHANDIGARH UNIVERSITY has no obligation to render any services to the student members beyond the period of validity of enrollment. To clarify further, no obligation of CHANDIGARH UNIVERSITY shall survive beyond the period of validity of enrollment.</p>

    <p><strong>Limitation of Liability:</strong> The liability of CHANDIGARH UNIVERSITY towards the students is limited only to the extent of the fee paid by them. To clarify further, CHANDIGARH UNIVERSITY shall not be liable to the students for punitive, exemplary, special, indirect, or consequential damages, including without limitation, lost profits.</p>

    <p><strong>Force Majeure:</strong> CHANDIGARH UNIVERSITY shall not be liable for delay or failure in performance of any of its obligations under the Agreement when such delay or failure arises from events or circumstances beyond the reasonable control of CHANDIGARH UNIVERSITY (including without limitation, acts of God, fire, flood, war, explosion, sabotage, terrorism, embargo, civil commotion, acts or omissions of any government entity, supplier delays, decisions of the University, decisions of the courts and governments, communications or power failure, equipment or software malfunction, or labor disputes).</p>

    <p><strong>Indemnity:</strong> A student agrees to indemnify, defend and hold CHANDIGARH UNIVERSITY harmless from and against any and all loss, damage, liability and expense (including reasonable attorney's fees and costs) arising out of any third party claim, action or proceeding based directly or indirectly on the acts of omission or commission by the member or his/her agents, the breach or alleged breach or failure to comply with any applicable laws or regulations, concerning the practice of profession of management.</p>

    <p><strong>Arbitration:</strong> All disputes relating to or arising out of this Agreement shall be settled by reference to arbitration only and not by recourse to the courts of law including consumer courts/for a, as per the applicable Indian Law including the Arbitration and Conciliation Act of 1996. Arbitration shall be conducted by an arbitration tribunal consisting of a single member only. CHANDIGARH UNIVERSITY's nominee shall be the 'persona designata' as an arbitrator. The venue of arbitration shall be Mohali, Punjab, India. The students should first exhaust the remedy from the Institute Arbitration Tribunal before approaching any court of law and/or seeking redressal under the provision of Consumer Protection Act 1986. The arbitration clause shall however not apply if CHANDIGARH UNIVERSITY and/or the authorized agent decide to prosecute any student for any criminal offences, including but not limited to dishonor of postdated cheques.</p>

    <p><strong>Applicable Law:</strong> The Agreement shall be deemed to have been made in Mohali in the State of Punjab, India and shall be construed and enforced in accordance with and the validity and performance hereof shall be governed by the laws of the State of Mohali, Punjab, India, without reference to principles of conflict of laws thereof. Judicial proceedings regarding any matter arising under the terms of the Agreement shall be brought in the relevant courts of Mohali, Punjab, India.</p>

    <p><strong>Jurisdiction</strong> for all disputes (if any) relating to CHANDIGARH UNIVERSITY is only/exclusively in Mohali, Punjab, India.</p>
  </div>

</div>

</body>
</html>
