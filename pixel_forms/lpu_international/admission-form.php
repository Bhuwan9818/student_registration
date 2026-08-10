<?php
/**
 * Lovely Professional University (LPU) — International Applicant
 * Information Form (For Admission 2026)
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
 */

require_once __DIR__ . '/../shared/helpers.php';

if (!isset($student)) {
    // Sample data so this file can be previewed standalone
    $student = [
        'application_no'     => '',
        'previous_programme' => '12th Standard',
        'result_status'      => 'Declared',       // Declared | Awaited
        'result_percentage'  => '82%',
        'course_name'        => 'B.Tech CSE',
        'first_name'         => 'RAHUL',
        'last_name'          => 'SHARMA',
        'father_name'        => 'SURESH SHARMA',
        'mother_name'        => 'GEETA SHARMA',
        'dob'                => '2007-08-15',
        'gender'             => 'Male',            // Male | Female | Transgender
        'address'            => '123 Milan Colony, Central Delhi',
        'city'               => 'Delhi',
        'state'              => 'Delhi',
        'country'            => 'India',
        'pincode'            => '110002',
        'nationality'        => 'Indian',
        'passport_no'        => 'A1234567',
        'phone_country_code' => '91',
        'telephone_no'       => '',
        'mobile'             => '9818404944',
        'guardian_mobile'    => '9811122233',
        'email'              => 'rahul@example.com',
        'guardian_email'     => 'suresh@example.com',
        'residential_required' => 'Yes',           // Yes | No
        'room_type'          => '2 seater',         // 1 seater|2 seater|3 seater|4 seater|Air Cooler|Air Conditioned
        'apartment_type'     => '',
        'standard_meal'      => 'Yes',
        'laundry'            => 'Yes',
        'photo_path'         => '',
    ];
}

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
<title>International Applicant Information Form - Lovely Professional University</title>
<link rel="stylesheet" href="../shared/common.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="no-print">
  <button onclick="window.print()">Print / Save as PDF</button>
</div>

<div class="sheet lpu-sheet">

  <!-- ================= HEADER ================= -->
  <table class="header-table">
    <tr>
      <td class="header-logo">
        <img src="assets/logo-lpu.png" alt="LPU Logo" class="uni-logo">
        <!-- <div class="uni-tagline">Transforming Education Transforming India</div> -->
      </td>
      <td class="header-title-col">
        <div class="app-no">Application No.<span class="dotted-fill short"><?= v($student['application_no'] ?? '') ?></span></div>
        <div class="form-title">INTERNATIONAL APPLICANT<br>INFORMATION FORM</div>
        <div class="form-subtitle">For Admission 2026</div>
      </td>
    </tr>
  </table>

  <!-- ================= ADMISSION PARTICULARS ================= -->
  <div class="section-banner">ADMISSION PARTICULARS</div>
  <table class="top-grid">
    <tr>
      <td class="top-grid-fields">
        <div class="numbered-row">
          <span class="num">1.</span> <span class="field-label">Previous Programme/ Level of Study</span>
          <span class="dotted-fill"><?= v($student['previous_programme'] ?? '') ?></span>
        </div>
        <div class="numbered-row">
          <span class="num">2.</span> <span class="field-label">Results status:- Declared/ Awaited</span>
          <span class="inline-label">If declared, Percentage/ CGPA/ GPA</span>
          <span class="dotted-fill"><?= v($student['result_percentage'] ?? '') ?></span>
        </div>
        <div class="numbered-row">
          <span class="num">3.</span> <span class="field-label">Programme Applied for</span>
          <span class="dotted-fill"><?= v($student['course_name'] ?? '') ?></span>
        </div>
      </td>
      <td class="photo-cell">
        <div class="photo-box" style="width:110px;height:105px;">
          <?php if (!empty($student['photo_path'])): ?>
            <img src="<?= v($student['photo_path']) ?>" alt="Photo">
          <?php else: ?>
            Paste Recent Passport size coloured Photograph
          <?php endif; ?>
        </div>
        <div class="photo-note">(upload for online submission)</div>
      </td>
    </tr>
  </table>

  <!-- ================= PERSONAL INFORMATION ================= -->
  <div class="section-banner">PERSONAL INFORMATION</div>
  <div class="applicant-detail-note">Applicant's Detail <span class="normal-weight">[exactly as it appears in Passport/ National ID</span></div>

  <div class="underline-field">
    <span class="field-label">Name</span>
    <span class="underline-fill"><?= v($candidateName) ?></span>
  </div>
  <div class="underline-field">
    <span class="field-label">Father's Name</span>
    <span class="underline-fill"><?= v($student['father_name'] ?? '') ?></span>
  </div>
  <div class="underline-field">
    <span class="field-label">Mother's Name</span>
    <span class="underline-fill"><?= v($student['mother_name'] ?? '') ?></span>
  </div>

  <div class="dob-gender-row">
    <div class="dob-block">
      <div class="field-label">Date of Birth</div>
      <div class="dob-boxes">
        <div class="dob-group">
          <div class="dob-caption">DD</div>
          <?= charBoxes($dobDay, 2) ?>
        </div>
        <div class="dob-group">
          <div class="dob-caption">MM</div>
          <?= charBoxes($dobMonth, 2) ?>
        </div>
        <div class="dob-group">
          <div class="dob-caption">YYYY</div>
          <?= charBoxes($dobYear, 4) ?>
        </div>
      </div>
    </div>
    <div class="gender-block">
      <span class="field-label">Gender</span>
      <span class="inline-check">Male <?= checkBox($student['gender'] === 'Male') ?></span>
      <span class="inline-check">Female <?= checkBox($student['gender'] === 'Female') ?></span>
      <span class="inline-check">Transgender <?= checkBox($student['gender'] === 'Transgender') ?></span>
    </div>
  </div>

  <!-- ================= CONTACT DETAILS ================= -->
  <div class="section-banner">CONTACT DETAILS</div>
  <div class="contact-box">

    <div class="box-heading">RESIDENTIAL ADDRESS</div>
    <div class="underline-fill full-line"><?= v($student['address'] ?? '') ?></div>
    <div class="underline-field small-field">
      <span class="inline-label">City/Town</span>
      <span class="underline-fill"><?= v($student['city'] ?? '') ?></span>
    </div>
    <div class="field-row">
      <span class="inline-label">State/Province</span>
      <span class="underline-fill"><?= v($student['state'] ?? '') ?></span>
      <span class="inline-label">Country</span>
      <span class="underline-fill"><?= v($student['country'] ?? '') ?></span>
      <span class="inline-label">Zip/ Postal Code</span>
      <?= charBoxes($student['pincode'] ?? '', 6) ?>
    </div>
    <div class="field-row">
      <span class="inline-label">Nationality</span>
      <?= charBoxes($student['nationality'] ?? '', 10) ?>
      <span class="inline-label">Passport/National ID No.</span>
      <span class="underline-fill"><?= v($student['passport_no'] ?? '') ?></span>
    </div>

    <div class="box-heading">PHONE NUMBER DETAILS</div>
    <div class="field-row">
      <div class="phone-group">
        <div class="dob-caption">Country Code</div>
        <?= charBoxes($student['phone_country_code'] ?? '', 3) ?>
      </div>
      <div class="phone-group grow">
        <div class="dob-caption">Telephone No.</div>
        <?= charBoxes($student['telephone_no'] ?? '', 10) ?>
      </div>
    </div>
    <div class="field-row">
      <div class="phone-group grow">
        <div class="dob-caption">Mobile No. (Applicant)<sup>#</sup></div>
        <?= charBoxes($student['mobile'] ?? '', 10) ?>
      </div>
      <div class="phone-group grow">
        <div class="dob-caption">Mobile No. (Parent/Guardian)<sup>#</sup></div>
        <?= charBoxes($student['guardian_mobile'] ?? '', 10) ?>
      </div>
    </div>

    <div class="box-heading">E-MAIL ADDRESS</div>
    <div class="field-row">
      <span class="inline-label narrow-label">Applicant</span>
      <?= charBoxes($student['email'] ?? '', 22) ?>
    </div>
    <div class="field-row">
      <span class="inline-label narrow-label">Parent/Guardian</span>
      <?= charBoxes($student['guardian_email'] ?? '', 22) ?>
    </div>
  </div>

  <!-- ================= RESIDENTIAL / LAUNDRY / MEAL PLAN ================= -->
  <div class="section-banner">RESIDENTIAL/ LAUNDRY/ MEAL PLAN</div>
  <div class="numbered-row">
    <span class="num">1.</span> <span class="field-label underline-label">RESIDENTIAL FACILITY REQUIRED</span>
    <span class="inline-check">Yes <?= checkBox(strtolower($student['residential_required'] ?? '') === 'yes') ?></span>
    <span class="inline-check">No <?= checkBox(strtolower($student['residential_required'] ?? '') === 'no') ?></span>
  </div>

  <table class="residential-table">
    <tr>
      <td class="res-col">
        <div class="res-title">1(a) Standard Hostel Rooms:</div>
        <div class="res-row"><span class="inline-check">1 seater <?= checkBox($student['room_type'] === '1 seater') ?></span><span class="inline-check">3 seater <?= checkBox($student['room_type'] === '3 seater') ?></span><span class="inline-check">Air Cooler <?= checkBox($student['room_type'] === 'Air Cooler') ?></span></div>
        <div class="res-row"><span class="inline-check">2 seater <?= checkBox($student['room_type'] === '2 seater') ?></span><span class="inline-check">4 seater <?= checkBox($student['room_type'] === '4 seater') ?></span><span class="inline-check">Air Conditioned <?= checkBox($student['room_type'] === 'Air Conditioned') ?></span></div>
      </td>
      <td class="res-col">
        <div class="res-title">1(b) Apartments (with AC):</div>
        <div class="res-row"><span class="inline-check">1 seater <?= checkBox($student['apartment_type'] === '1 seater') ?></span><span class="inline-check">3 seater <?= checkBox($student['apartment_type'] === '3 seater') ?></span></div>
        <div class="res-row"><span class="inline-check">2 seater <?= checkBox($student['apartment_type'] === '2 seater') ?></span><span class="inline-check">4 seater <?= checkBox($student['apartment_type'] === '4 seater') ?></span></div>
      </td>
      <td class="res-col meal-col">
        <div class="res-title">2. Standard Meal</div>
        <div class="res-row"><span class="inline-check">Yes <?= checkBox(strtolower($student['standard_meal'] ?? '') === 'yes') ?></span><span class="inline-check">No. <?= checkBox(strtolower($student['standard_meal'] ?? '') === 'no') ?></span></div>
        <div class="res-title">3. Laundry</div>
        <div class="res-row"><span class="inline-check">Yes <?= checkBox(strtolower($student['laundry'] ?? '') === 'yes') ?></span><span class="inline-check">No. <?= checkBox(strtolower($student['laundry'] ?? '') === 'no') ?></span></div>
      </td>
    </tr>
  </table>

  <!-- ================= SIGNATURES ================= -->
  <div class="signature-row">
    <div class="signature-col">
      <div class="signature-line">&#10003;</div>
      <div class="signature-caption">Signature of the Parent/Legal Guardian</div>
    </div>
    <div class="signature-col">
      <div class="signature-line">&#10003;</div>
      <div class="signature-caption">Signature of Applicant</div>
    </div>
  </div>

</div>
</body>
</html>
