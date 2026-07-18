<?php
// Swami Vivekanand Subharti University — Application Form for Admission template
$check = function ($condition) { return $condition ? '&#9745;' : '&#9744;'; };
?>
<style>
  .svsu-form { max-width: 900px; margin: 0 auto; background: #fff; padding: 24px 30px; border: 1px solid #333; font-family: Arial, Helvetica, sans-serif; color: #111; font-size: .85rem; }
  .svsu-form h1 { font-size: 1.05rem; font-weight: 800; text-align: center; margin: 4px 0 0; }
  .svsu-form h2 { font-size: .95rem; font-weight: 700; text-align: center; margin: 0; }
  .svsu-title { text-align: center; font-weight: 700; font-size: 1rem; text-decoration: underline; margin: 8px 0 12px; }
  .svsu-photo-box { width: 100px; height: 120px; border: 1px solid #111; font-size: .65rem; text-align: center; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 4px; }
  .svsu-photo-box img { max-width: 100%; max-height: 100%; }
  .svsu-field-row { display: flex; gap: 14px; margin-bottom: 9px; align-items: baseline; flex-wrap: wrap; }
  .svsu-field-row .lbl { font-weight: 700; font-size: .8rem; white-space: nowrap; }
  .svsu-field-row .val { border-bottom: 1px solid #111; flex: 1; min-height: 16px; padding: 0 4px; }
  .svsu-chk { margin-right: 16px; white-space: nowrap; }
  table.svsu-table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: .8rem; }
  table.svsu-table th, table.svsu-table td { border: 1px solid #111; padding: 5px 6px; text-align: left; }
  table.svsu-table th { background: #f0f0f0; }
  .svsu-declare { font-size: .78rem; margin-top: 14px; border-top: 1px solid #111; padding-top: 10px; }
</style>

<div class="svsu-form">
  <div class="d-flex justify-content-between align-items-start mb-2" style="font-size:.78rem;">
    <div><strong>Application No.:</strong> DDE-<?= e($student['registration_no']) ?></div>
    <div><strong>Session:</strong> <?= e($student['year_label']) ?></div>
  </div>

  <div class="d-flex justify-content-between align-items-center">
    <?php if ($student['university_logo']): ?><img src="<?= e($student['university_logo']) ?>" style="height:60px;" alt=""><?php endif; ?>
    <div class="flex-fill text-center">
      <h2>DIRECTORATE OF DISTANCE EDUCATION</h2>
      <h1><?= e($student['university_name']) ?></h1>
    </div>
    <div class="svsu-photo-box">
      <?php if ($student['photo_path']): ?><img src="<?= e($student['photo_path']) ?>" alt="Photo"><?php else: ?>Affix recent Passport Size Photograph<?php endif; ?>
    </div>
  </div>
  <div class="svsu-title">APPLICATION FORM FOR ADMISSION</div>

  <div class="svsu-field-row"><span class="lbl">Enrolment Number:</span><span class="val"></span></div>
  <div class="svsu-field-row"><span class="lbl">Programme Applied For:</span><span class="val"><?= e($student['course_name']) ?><?= $student['specialization'] ? ' — ' . e($student['specialization']) : '' ?></span></div>

  <div class="svsu-field-row"><span class="lbl">1. Applicant's Name:</span><span class="val" style="text-transform:uppercase;"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></span></div>
  <div class="svsu-field-row"><span class="lbl">2. Father's Name:</span><span class="val" style="text-transform:uppercase;"><?= e($student['father_name']) ?></span></div>
  <div class="svsu-field-row"><span class="lbl">3. Mother's Name:</span><span class="val" style="text-transform:uppercase;"><?= e($student['mother_name']) ?></span></div>

  <div class="svsu-field-row">
    <span class="lbl">4. Sex:</span>
    <span class="svsu-chk"><?= $check($student['gender'] === 'Male') ?> Male</span>
    <span class="svsu-chk"><?= $check($student['gender'] === 'Female') ?> Female</span>
    <span class="lbl">5. Date of Birth:</span><span class="val"><?= e($student['dob']) ?></span>
  </div>

  <div class="svsu-field-row"><span class="lbl">6. Address for Correspondence:</span>
    <span class="val"><?= e($student['address']) ?>, <?= e($student['city']) ?>, <?= e($student['state']) ?> - <?= e($student['pincode']) ?></span>
  </div>
  <div class="svsu-field-row">
    <span class="lbl">Phone No.:</span><span class="val"><?= e($student['alt_mobile'] ?: '-') ?></span>
    <span class="lbl">Mobile No.:</span><span class="val"><?= e($student['mobile']) ?></span>
    <span class="lbl">E-mail:</span><span class="val"><?= e($student['email']) ?></span>
  </div>

  <div style="font-size:.78rem; margin: 8px 0;">Please ensure that you have enclosed the DD for the prescribed fees in full and other certificates as indicated in prospectus.</div>

  <div style="font-weight:700; font-size:.82rem;">7. Details of Fee Payment:</div>
  <div class="svsu-field-row">
    <span class="lbl">Cash/DD No./RTGS:</span><span class="val"><?= $fee ? e($fee['utr_no'] ?: $fee['mode']) : '-' ?></span>
    <span class="lbl">Date:</span><span class="val"><?= $fee ? date('d-m-Y', strtotime($fee['submitted_at'])) : '-' ?></span>
  </div>
  <div class="svsu-field-row">
    <span class="lbl">Bank:</span><span class="val"></span>
    <span class="lbl">Amount:</span><span class="val"><?= $fee ? '₹' . number_format($fee['amount'], 2) : '-' ?></span>
  </div>

  <div class="svsu-field-row">
    <span class="lbl">8. Nationality:</span><span class="val"><?= e($student['nationality']) ?></span>
  </div>
  <div class="svsu-field-row" style="flex-wrap:wrap;">
    <span class="lbl">9. Category:</span>
    <?php foreach (['General' => 'Gen.', 'OBC' => 'OBC', 'SC' => 'SC', 'ST' => 'ST', 'Other' => 'Others'] as $catKey => $catLabel): ?>
      <span class="svsu-chk"><?= $check($student['category'] === $catKey) ?> <?= $catLabel ?></span>
    <?php endforeach; ?>
  </div>
  <div class="svsu-field-row"><span class="lbl">10. Employment Status:</span><span class="val"><?= e($student['employment_status']) ?></span></div>

  <div style="font-weight:700; font-size:.82rem; margin-top:10px;">11. Details of Educational Qualifications (From Matriculation onwards):</div>
  <table class="svsu-table">
    <thead><tr><th>Name of Examination</th><th>Subject</th><th>Year of Passing</th><th>Name of University/Board</th><th>Division/Grade</th></tr></thead>
    <tbody>
      <?php $any = false; foreach ($academicLevels as $levelKey => $levelLabel): ?>
        <?php $a = $academicsByLevel[$levelKey] ?? null; if (!$a || empty($a['institution_board'])) continue; $any = true; ?>
        <tr>
          <td><?= e($levelLabel) ?></td>
          <td><?= e($student['specialization'] ?: '-') ?></td>
          <td><?= e($a['year_of_passing']) ?></td>
          <td><?= e($a['institution_board']) ?></td>
          <td><?= e($a['percentage']) ?>%</td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$any): ?><tr><td colspan="5" class="text-center text-muted">No academic history on record.</td></tr><?php endif; ?>
    </tbody>
  </table>

  <div class="svsu-declare">
    I hereby declare that the information furnished herein above is true and correct to the best of my knowledge and belief.
    I further declare that the attested photocopies of the certificates submitted by me at the time of admission are the true
    copies of the originals. I have read the prospectus and the rules and regulations of the University.
  </div>

  <div class="d-flex justify-content-between mt-4" style="font-size:.78rem;">
    <div>Place &amp; Date: __________________</div>
    <div>Signature of the Applicant</div>
  </div>
</div>
