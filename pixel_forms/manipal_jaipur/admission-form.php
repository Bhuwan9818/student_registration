<?php
/**
 * Manipal University Jaipur — Registration Form (UG/PG)
 * Academic Session 2025-26
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
 *   $documents = ... (rows of {name, submitted, pending, reason} — optional;
 *                      falls back to the standard 11-item checklist below)
 */

require_once __DIR__ . '/../shared/helpers.php';

if (!isset($student)) {
    // Sample data so this file can be previewed standalone
    $student = [
        'application_no'   => '',
        'course_name'      => 'B.Tech CSE',
        'first_name'       => 'RAHUL',
        'last_name'        => 'SHARMA',
        'father_name'      => 'SURESH SHARMA',
        'dob'              => '2007-08-15',
        'blood_group'      => 'B+',
        'gender'           => 'M',                 // M | F
        'category'         => 'GEN',                // GEN | ST | SC | OBC
        'address'          => '123 Milan Colony, Central Delhi',
        'city'             => 'Delhi',
        'pincode'          => '110002',
        'state'            => 'Delhi',
        'country'          => 'India',
        'nationality'      => 'Indian',
        'religion'         => 'Hindu',
        'caste'            => '',
        'specially_abled'  => 'No',
        'jk_resident'      => 'No',
        'aadhar_no'        => '',
        'mobile'           => '9818404944',
        'email'            => 'rahul@example.com',
        'guardian_mobile'  => '9811122233',
        'guardian_email'   => 'suresh@example.com',
    ];
}
$candidateName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));

$dobDay = $dobMonth = $dobYear = '';
if (!empty($student['dob'])) {
    [$dobYear, $dobMonth, $dobDay] = array_pad(explode('-', $student['dob']), 3, '');
}

// Standard document checklist (matches the printed form); pass $documents to override.
$defaultDocuments = [
    'X Marks Sheet & Passing Certificate (Self Attested Photocopy)',
    'XII Marks Sheet (Self-Attested Photocopy)',
    'XII Passing Certificate (Self-Attested Photocopy)',
    'UG Passing Certificate (Self Attested Photocopy) only for Post Graduate Programs',
    'Transfer Certificate (Original)',
    'Character Certificate (Original)',
    'Migration Certificate (Original/Digilocker Downloaded)',
    'MET rank card/ JEE-Mains (if applicable)',
    'Anti-Ragging Affidavits (Original, Downloaded from Anti-Ragging Portal)',
    'Anti-Drug Affidavits (Original both candidate and guardian on Minimum Rs/-50 Stamp Paper)',
    'Medical Fitness Certificate (Original should be signed by registered medical practitioners)',
];
$documents = $documents ?? array_map(fn($name) => ['name' => $name, 'submitted' => '', 'pending' => '', 'reason' => ''], $defaultDocuments);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registration Form (UG/PG) - Manipal University Jaipur</title>
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
      <td class="header-logo"><img src="assets/manipal-logo.png" alt="Manipal University Jaipur Logo" class="uni-logo"></td>
      <!-- <td class="header-name">
        <div class="uni-name">MANIPAL UNIVERSITY</div>
        <div class="uni-name-sub">JAIPUR</div>
        <div class="uni-establish">(University under Section 2(f) of the UGC Act)</div>
      </td> -->
    </tr>
  </table>

  <div class="form-title">REGISTRATION FORM (UG/PG)</div>
  <div class="form-session">(Academic Session 2025-26)</div>
  <div class="app-no-row">Application No:<span class="dotted-fill short"><?= v($student['application_no'] ?? '') ?></span></div>

  <div class="block-letters-note">(PLEASE FILL FORM IN BLOCK LETTERS)</div>

  <!-- ================= NUMBERED FIELDS ================= -->
  <div class="numbered-row">
    <span class="num">1.</span> <span class="field-label">Program applied for</span>
    <span class="underline-fill"><?= v($student['course_name'] ?? '') ?></span>
  </div>
  <div class="numbered-row">
    <span class="num">2.</span> <span class="field-label">Name of the Applicant (As per 10th marks sheet)</span>
    <span class="underline-fill"><?= v($candidateName) ?></span>
  </div>
  <div class="numbered-row">
    <span class="num">3.</span> <span class="field-label">Father's Name</span>
    <span class="underline-fill"><?= v($student['father_name'] ?? '') ?></span>
  </div>
  <div class="numbered-row">
    <span class="num">4.</span> <span class="field-label">Date of Birth</span>
    <span class="dob-fill"><?= v($dobDay) ?></span>/<span class="dob-fill"><?= v($dobMonth) ?></span>/<span class="dob-fill"><?= v($dobYear) ?></span>
    <span class="num">5.</span> <span class="field-label">Blood Group</span>
    <span class="underline-fill short"><?= v($student['blood_group'] ?? '') ?></span>
    <span class="num">6.</span> <span class="field-label">Gender (M/F)</span>
    <span class="underline-fill short"><?= v($student['gender'] ?? '') ?></span>
  </div>
  <div class="numbered-row">
    <span class="num">7.</span> <span class="field-label">Category (GEN/ST/SC/OBC)</span>
    <span class="underline-fill short"><?= v($student['category'] ?? '') ?></span>
    <span class="num">8.</span> <span class="field-label">Address for Correspondence</span>
    <span class="underline-fill"><?= v($student['address'] ?? '') ?></span>
  </div>
  <div class="numbered-row indent-row">
    <span class="inline-label">City</span>
    <span class="underline-fill"><?= v($student['city'] ?? '') ?></span>
    <span class="inline-label">PIN</span>
    <span class="underline-fill"><?= v($student['pincode'] ?? '') ?></span>
    <span class="inline-label">State</span>
    <span class="underline-fill"><?= v($student['state'] ?? '') ?></span>
  </div>
  <div class="numbered-row indent-row">
    <span class="inline-label">Country</span>
    <span class="underline-fill"><?= v($student['country'] ?? '') ?></span>
  </div>
  <div class="numbered-row">
    <span class="num">9.</span> <span class="field-label">Nationality</span>
    <span class="underline-fill"><?= v($student['nationality'] ?? '') ?></span>
    <span class="num">10.</span> <span class="field-label">Religion</span>
    <span class="underline-fill"><?= v($student['religion'] ?? '') ?></span>
    <span class="num">11.</span> <span class="field-label">Caste</span>
    <span class="underline-fill"><?= v($student['caste'] ?? '') ?></span>
  </div>
  <div class="numbered-row">
    <span class="num">12.</span> <span class="field-label">Specially Abled (Yes/ No)</span>
    <span class="underline-fill short"><?= v($student['specially_abled'] ?? '') ?></span>
    <span class="num">13.</span> <span class="field-label">J &amp; K Resident (Yes/ No)</span>
    <span class="underline-fill short"><?= v($student['jk_resident'] ?? '') ?></span>
  </div>
  <div class="numbered-row">
    <span class="num">14.</span> <span class="field-label">Aadhaar No.</span>
    <span class="underline-fill"><?= v($student['aadhar_no'] ?? '') ?></span>
  </div>
  <div class="numbered-row">
    <span class="num">15.</span> <span class="field-label">Student Mobile No</span>
    <span class="underline-fill"><?= v($student['mobile'] ?? '') ?></span>
    <span class="inline-label">Email ID</span>
    <span class="underline-fill"><?= v($student['email'] ?? '') ?></span>
  </div>
  <div class="numbered-row">
    <span class="num">16.</span> <span class="field-label">Parents Mobile No</span>
    <span class="underline-fill"><?= v($student['guardian_mobile'] ?? '') ?></span>
    <span class="inline-label">Email ID</span>
    <span class="underline-fill"><?= v($student['guardian_email'] ?? '') ?></span>
  </div>

  <!-- ================= DOCUMENTS TABLE ================= -->
  <div class="numbered-row"><span class="num">17.</span> <span class="field-label bold">Certificate/Documents to be submitted</span></div>
  <table class="form-table doc-table">
    <tr>
      <th style="width:5%;">S.No.</th>
      <th style="width:45%;">Documents /Certificate</th>
      <th style="width:14%;">Submitted</th>
      <th style="width:12%;">Pending</th>
      <th>Reason for Pendency</th>
    </tr>
    <?php foreach ($documents as $i => $doc): ?>
      <tr>
        <td class="center"><?= $i + 1 ?></td>
        <td class="doc-name"><?= v($doc['name'] ?? '') ?></td>
        <td><?= v($doc['submitted'] ?? '') ?></td>
        <td><?= v($doc['pending'] ?? '') ?></td>
        <td><?= v($doc['reason'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <div class="disclaimer">
    <strong>DISCLAIMER:</strong>
    If there are not sufficient numbers of applications in any program the institute reserves the right not to offer same program. In such cases the candidates may be offered alternative programs based on their willingness and eligibility.
  </div>

  <div class="declaration">
    <strong>DECLARATION BY THE CANDIDATE:</strong>
    <p>&bull; I hereby declare that all the information given by me in this application is true and correct to the best of my knowledge and belief. I also note that if any of the above statements are found to be incorrect or false or any information or particulars have been suppressed or omitted there from, I am liable to be disqualified and my admission may be cancelled. I have read and understood the contents of the Admission Announcement for the various Programs. I hereby permit the University to use, display or transfer any of the details furnished by me in this form for complying with the admission formalities/Regulatory Authorities.</p>
    <p>&bull; I agree to confirm to the rules and regulations at present in force or that may hereafter be made for the administration of the University and its hostels. I am aware that if I found responsible in any disciplinary issue then University has full authority to take any action including expulsion.</p>
  </div>

  <!-- ================= SIGNATURES ================= -->
  <div class="signature-row">
    <div class="signature-box">
      <div>Signature of the</div>
      <div>Applicant<span class="underline-fill"></span></div>
    </div>
    <div class="signature-box">
      <div>Signature of the</div>
      <div>Parent/Guardian<span class="underline-fill"></span></div>
    </div>
  </div>

  <!-- ================= FOR OFFICE USE ONLY ================= -->
  <div class="office-use">
    <div class="office-use-title">For Office Use Only</div>
    <div class="office-use-row">Documents Received and Checked by: Name<span class="underline-fill"></span> Faculty<span class="underline-fill"></span> Signature<span class="underline-fill"></span></div>
    <div class="office-use-row">Verified by HoD: Name<span class="underline-fill"></span> Signature<span class="underline-fill"></span></div>
    <div class="office-use-row">Verified by Admission Dept.: Name<span class="underline-fill"></span> Signature<span class="underline-fill"></span></div>
  </div>

</div>
</body>
</html>
