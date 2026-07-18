<?php
// Mangalayatan University — Application Form template
?>
<style>
  .mu-form { max-width: 900px; margin: 0 auto; background: #fff; padding: 0; border: 1px solid #333; font-family: Arial, Helvetica, sans-serif; color: #111; font-size: .85rem; }
  .mu-topbar { background: #16305c; color: #fff; padding: 8px 20px; display: flex; justify-content: space-between; font-size: .78rem; }
  .mu-header { padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #16305c; }
  .mu-header h1 { color: #f0a500; font-size: 1.6rem; font-weight: 800; margin: 0; text-align: center; }
  .mu-uni-name { color: #16305c; font-weight: 800; font-size: 1.15rem; }
  .mu-body { padding: 20px 24px; }
  .mu-section-title { background: #16305c; color: #fff; font-weight: 700; padding: 6px 12px; margin: 14px 0 8px; font-size: .85rem; }
  .mu-photo-box { width: 100px; height: 120px; border: 1px solid #111; font-size: .65rem; text-align: center; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 4px; }
  .mu-photo-box img { max-width: 100%; max-height: 100%; }
  .mu-field-row { display: flex; gap: 14px; margin-bottom: 8px; align-items: baseline; flex-wrap: wrap; }
  .mu-field-row .lbl { font-weight: 700; font-size: .8rem; white-space: nowrap; }
  .mu-field-row .val { border-bottom: 1px dotted #111; flex: 1; min-height: 16px; padding: 0 4px; }
  table.mu-table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: .8rem; }
  table.mu-table th, table.mu-table td { border: 1px solid #111; padding: 6px 8px; text-align: left; }
  table.mu-table th { background: #f0f0f0; }
  .mu-receipt { border-top: 2px dashed #111; margin-top: 20px; padding-top: 14px; display: flex; justify-content: space-between; align-items: center; font-size: .8rem; }
</style>

<div class="mu-form">
  <div class="mu-topbar">
    <span><?= e($student['university_name']) ?></span>
    <span>Application No: <?= e($student['registration_no']) ?></span>
  </div>
  <div class="mu-header">
    <div class="d-flex align-items-center gap-3">
      <?php if ($student['university_logo']): ?><img src="<?= e($student['university_logo']) ?>" style="height:48px;" alt=""><?php endif; ?>
      <div class="mu-uni-name"><?= e($student['university_name']) ?></div>
    </div>
    <h1>APPLICATION FORM</h1>
    <div class="mu-photo-box">
      <?php if ($student['photo_path']): ?><img src="<?= e($student['photo_path']) ?>" alt="Photo"><?php else: ?>Affix latest Passport size Color Photograph<?php endif; ?>
    </div>
  </div>

  <div class="mu-body">
    <div class="mu-section-title">PROGRAM APPLYING FOR</div>
    <div class="mu-field-row">
      <span class="lbl">Program:</span>
      <span class="val"><strong><?= e($student['course_name']) ?></strong><?= $student['specialization'] ? ' (' . e($student['specialization']) . ')' : '' ?></span>
      <span class="lbl">Session:</span><span class="val"><?= e($student['year_label']) ?></span>
      <span class="lbl">Semester:</span><span class="val"><?= e($student['semester_no']) ?></span>
    </div>

    <div class="mu-section-title">APPLICANT DETAILS</div>
    <div class="mu-field-row">
      <span class="lbl">Name:</span><span class="val"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></span>
      <span class="lbl">Father's Name:</span><span class="val"><?= e($student['father_name']) ?></span>
    </div>
    <div class="mu-field-row">
      <span class="lbl">Mother's Name:</span><span class="val"><?= e($student['mother_name']) ?></span>
      <span class="lbl">Date of Birth:</span><span class="val"><?= e($student['dob']) ?></span>
      <span class="lbl">Gender:</span><span class="val"><?= e($student['gender']) ?></span>
    </div>
    <div class="mu-field-row">
      <span class="lbl">Category:</span><span class="val"><?= e($student['category']) ?></span>
      <span class="lbl">Nationality:</span><span class="val"><?= e($student['nationality']) ?></span>
    </div>
    <div class="mu-field-row">
      <span class="lbl">Mobile:</span><span class="val"><?= e($student['mobile']) ?></span>
      <span class="lbl">Email:</span><span class="val"><?= e($student['email']) ?></span>
    </div>
    <div class="mu-field-row">
      <span class="lbl">Address:</span>
      <span class="val"><?= e($student['address']) ?>, <?= e($student['city']) ?>, <?= e($student['state']) ?> - <?= e($student['pincode']) ?></span>
    </div>

    <div class="mu-section-title">EDUCATIONAL QUALIFICATIONS</div>
    <table class="mu-table">
      <thead><tr><th>Level</th><th>Board/University</th><th>Year of Passing</th><th>Percentage / CGPA</th></tr></thead>
      <tbody>
        <?php $any = false; foreach ($academicLevels as $levelKey => $levelLabel): ?>
          <?php $a = $academicsByLevel[$levelKey] ?? null; if (!$a || empty($a['institution_board'])) continue; $any = true; ?>
          <tr>
            <td><?= e($levelLabel) ?></td>
            <td><?= e($a['institution_board']) ?></td>
            <td><?= e($a['year_of_passing']) ?></td>
            <td><?= e($a['percentage']) ?>%</td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$any): ?><tr><td colspan="4" class="text-center text-muted">No academic history on record.</td></tr><?php endif; ?>
      </tbody>
    </table>

    <div class="mu-receipt">
      <div>
        <div><strong>Form Receipt</strong></div>
        <div>Name: <?= e($student['first_name'] . ' ' . $student['last_name']) ?> &nbsp; | &nbsp; Father's Name: <?= e($student['father_name']) ?></div>
        <div>Program Applied: <?= e($student['course_name']) ?></div>
      </div>
      <div class="text-end">
        <div>Sign of Student</div>
        <div class="mt-3">Sign of Authorized Signatory</div>
      </div>
    </div>
  </div>
</div>
