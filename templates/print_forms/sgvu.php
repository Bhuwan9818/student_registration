<?php
// Suresh Gyan Vihar University — Admission Form template
$check = function ($condition) { return $condition ? '&#9745;' : '&#9744;'; };
$nameParts = trim($student['first_name'] . ' ' . $student['last_name']);
?>
<style>
  .sgvu-form { max-width: 900px; margin: 0 auto; background: #fff; padding: 24px 30px; border: 1px solid #333; font-family: Arial, Helvetica, sans-serif; color: #111; font-size: .85rem; }
  .sgvu-form h1 { color: #c0272d; font-size: 1.7rem; font-weight: 700; margin: 0; }
  .sgvu-form .sub { font-size: .74rem; color: #333; margin-top: 2px; }
  .sgvu-title { text-align: center; font-weight: 700; font-size: 1.15rem; letter-spacing: .05em; margin: 14px 0 10px; border-top: 2px solid #111; border-bottom: 2px solid #111; padding: 6px 0; }
  .sgvu-field-row { display: flex; gap: 14px; margin-bottom: 8px; align-items: baseline; flex-wrap: wrap; }
  .sgvu-field-row .lbl { font-weight: 700; font-size: .78rem; white-space: nowrap; }
  .sgvu-field-row .val { border-bottom: 1px solid #111; flex: 1; min-height: 16px; padding: 0 4px; font-size: .82rem; }
  .sgvu-box { border: 1px solid #111; padding: 10px; margin-top: 8px; }
  .sgvu-photo-box { width: 110px; height: 130px; border: 1px solid #111; font-size: .68rem; text-align: center; padding: 4px; display: flex; align-items: center; justify-content: center; flex-direction: column; }
  .sgvu-photo-box img { max-width: 100%; max-height: 100%; }
  table.sgvu-table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: .8rem; }
  table.sgvu-table th, table.sgvu-table td { border: 1px solid #111; padding: 5px 6px; text-align: left; }
  table.sgvu-table th { background: #f0f0f0; }
  .sgvu-chk { margin-right: 14px; white-space: nowrap; font-size: .8rem; }
</style>

<div class="sgvu-form">
  <div class="d-flex justify-content-between align-items-start">
    <div>
      <h1>Suresh Gyan Vihar University</h1>
      <div class="sub">Established under UGC U/S 22 of UGC Act 1956 through its notification no. F.9-38/2008(CPP-I)<br>dated 1 April 2009.</div>
    </div>
    <?php if ($student['university_logo']): ?><img src="<?= e($student['university_logo']) ?>" style="height:50px;" alt=""><?php endif; ?>
  </div>

  <div class="sgvu-title">ADMISSION FORM</div>

  <div class="d-flex justify-content-between gap-3">
    <div class="flex-fill">
      <div class="sgvu-field-row"><span class="lbl">Enrolment No. (Leave Blank):</span><span class="val"><?= e($student['registration_no']) ?></span></div>
      <div class="sgvu-field-row"><span class="lbl">Course Code:</span><span class="val"></span><span class="lbl">Programme:</span><span class="val"><?= e($student['course_name']) ?></span></div>
      <div class="sgvu-field-row"><span class="lbl">Specialization:</span><span class="val"><?= e($student['specialization'] ?: '-') ?></span></div>
    </div>
    <div class="sgvu-photo-box">
      <?php if ($student['photo_path']): ?><img src="<?= e($student['photo_path']) ?>" alt="Photo"><?php else: ?>Paste box-sized photograph<?php endif; ?>
    </div>
  </div>

  <div class="sgvu-box">
    <div class="sgvu-field-row"><span class="lbl">Name of Candidate:</span><span class="val" style="text-transform:uppercase;"><?= e($nameParts) ?></span></div>
    <div class="sgvu-field-row"><span class="lbl">Father's Name:</span><span class="val" style="text-transform:uppercase;"><?= e($student['father_name']) ?></span></div>
    <div class="sgvu-field-row"><span class="lbl">Mother's Name:</span><span class="val" style="text-transform:uppercase;"><?= e($student['mother_name']) ?></span></div>
    <div class="sgvu-field-row">
      <span class="lbl">Gender:</span>
      <span class="sgvu-chk"><?= $check($student['gender'] === 'Male') ?> Male</span>
      <span class="sgvu-chk"><?= $check($student['gender'] === 'Female') ?> Female</span>
      <span class="lbl">Date of Birth:</span><span class="val"><?= e($student['dob']) ?></span>
    </div>
  </div>

  <div class="d-flex gap-3">
    <div class="sgvu-box flex-fill">
      <div style="font-weight:700; margin-bottom:4px;">PERMANENT ADDRESS:</div>
      <div><?= e($student['address']) ?></div>
      <div class="sgvu-field-row mt-2"><span class="lbl">Pin Code:</span><span class="val"><?= e($student['pincode']) ?></span></div>
      <div class="sgvu-field-row"><span class="lbl">City:</span><span class="val"><?= e($student['city']) ?></span><span class="lbl">State:</span><span class="val"><?= e($student['state']) ?></span></div>
      <div class="sgvu-field-row"><span class="lbl">Ph No.:</span><span class="val"><?= e($student['alt_mobile']) ?></span></div>
      <div class="sgvu-field-row"><span class="lbl">Mob No.:</span><span class="val"><?= e($student['mobile']) ?></span></div>
      <div class="sgvu-field-row"><span class="lbl">E-mail:</span><span class="val"><?= e($student['email']) ?></span></div>
    </div>
    <div class="sgvu-box flex-fill">
      <div style="font-weight:700; margin-bottom:4px;">MAILING ADDRESS:</div>
      <div><?= e($student['address']) ?></div>
      <div class="sgvu-field-row mt-2"><span class="lbl">Pin Code:</span><span class="val"><?= e($student['pincode']) ?></span></div>
      <div class="sgvu-field-row"><span class="lbl">City:</span><span class="val"><?= e($student['city']) ?></span><span class="lbl">State:</span><span class="val"><?= e($student['state']) ?></span></div>
      <div class="sgvu-field-row"><span class="lbl">Ph No.:</span><span class="val"><?= e($student['district']) ?></span></div>
      <div class="sgvu-field-row"><span class="lbl">Mob No.:</span><span class="val"><?= e($student['guardian_mobile']) ?></span></div>
      <div class="sgvu-field-row"><span class="lbl">E-mail:</span><span class="val"><?= e($student['alt_email']) ?></span></div>
    </div>
  </div>

  <div class="sgvu-box">
    <div class="sgvu-field-row">
      <span class="lbl">Nationality:</span>
      <span class="sgvu-chk"><?= $check(strtolower($student['nationality']) === 'indian') ?> Indian</span>
      <span class="sgvu-chk"><?= $check(strtolower($student['nationality']) !== 'indian' && $student['nationality']) ?> Others (<?= e($student['nationality']) ?>)</span>
    </div>
    <div class="sgvu-field-row" style="flex-wrap:wrap;">
      <span class="lbl">Category:</span>
      <?php foreach (['General','SC','ST','OBC','EWS','Other'] as $cat): ?>
        <span class="sgvu-chk"><?= $check($student['category'] === $cat) ?> <?= $cat ?></span>
      <?php endforeach; ?>
      <span class="sgvu-chk"><?= $check($student['employment_status'] === 'Employed') ?> Employed</span>
      <span class="sgvu-chk"><?= $check($student['employment_status'] === 'Unemployed') ?> Unemployed</span>
    </div>
    <div class="sgvu-field-row"><span class="lbl">Have you ever been debarred by any University/Board?</span>
      <span class="sgvu-chk"><?= $check(false) ?> No</span>
      <span class="sgvu-chk"><?= $check(false) ?> Yes</span>
    </div>
  </div>

  <div style="font-weight:700; margin-top:10px;">DETAILS OF PREVIOUS EXAMINATIONS PASSED FROM OTHER UNIVERSITY</div>
  <table class="sgvu-table">
    <thead><tr><th style="width:30px;">S. No</th><th>Name of Exam</th><th>Roll No.</th><th>Year of Passing</th><th>Percent / Grade</th><th>Name of University / Board</th></tr></thead>
    <tbody>
      <?php $sn = 1; foreach ($academicLevels as $levelKey => $levelLabel): ?>
        <?php $a = $academicsByLevel[$levelKey] ?? null; if (!$a || empty($a['institution_board'])) continue; ?>
        <tr>
          <td><?= $sn++ ?></td>
          <td><?= e($levelLabel) ?></td>
          <td>-</td>
          <td><?= e($a['year_of_passing']) ?></td>
          <td><?= e($a['percentage']) ?>%</td>
          <td><?= e($a['institution_board']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($sn === 1): ?><tr><td colspan="6" class="text-center text-muted">No academic history on record.</td></tr><?php endif; ?>
    </tbody>
  </table>

  <div class="d-flex justify-content-between mt-4" style="font-size:.78rem;">
    <div>Signature of candidate (in full)</div>
    <div>Registration No: <strong><?= e($student['registration_no']) ?></strong> &nbsp; | &nbsp; Issued: <?= date('d M Y') ?></div>
  </div>
</div>
