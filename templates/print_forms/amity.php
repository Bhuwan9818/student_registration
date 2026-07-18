<?php
// Amity University — Registration cum Enrolment Form template
$check = function ($condition) { return $condition ? '&#9745;' : '&#9744;'; };
$nameSplit = preg_split('/\s+/', trim($student['first_name'] . ' ' . $student['last_name']), 3);
$firstN = $nameSplit[0] ?? ''; $middleN = count($nameSplit) > 2 ? $nameSplit[1] : ''; $lastN = end($nameSplit);
?>
<style>
  .amity-form { max-width: 900px; margin: 0 auto; background: #fff; padding: 24px 30px; border: 1px solid #333; font-family: Arial, Helvetica, sans-serif; color: #111; font-size: .85rem; }
  .amity-header-bar { height: 5px; background: linear-gradient(90deg, #002d62, #f0a500); margin: 10px 0 16px; }
  .amity-form .title-block { display: flex; align-items: center; gap: 14px; }
  .amity-form .uni-name { color: #002d62; font-weight: 800; font-size: 1.3rem; line-height: 1.1; }
  .amity-form .dept-name { font-weight: 700; font-size: 1rem; color: #111; }
  .amity-title { text-align: center; font-weight: 700; font-size: 1.05rem; text-decoration: underline; margin: 10px 0 4px; }
  .amity-photo-box { width: 100px; height: 120px; border: 1px solid #111; font-size: .65rem; text-align: center; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 4px; }
  .amity-photo-box img { max-width: 100%; max-height: 100%; }
  .amity-field-row { display: flex; gap: 14px; margin-bottom: 10px; align-items: baseline; flex-wrap: wrap; }
  .amity-field-row .lbl { font-weight: 700; font-size: .8rem; white-space: nowrap; }
  .amity-field-row .val { border-bottom: 1px dotted #111; flex: 1; min-height: 16px; padding: 0 4px; }
  .amity-chk { margin-right: 16px; white-space: nowrap; }
  table.amity-table { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: .8rem; }
  table.amity-table th, table.amity-table td { border: 1px solid #111; padding: 6px 8px; text-align: left; }
  table.amity-table th { background: #f0f0f0; }
</style>

<div class="amity-form">
  <div class="d-flex justify-content-between align-items-start">
    <div class="title-block">
      <?php if ($student['university_logo']): ?><img src="<?= e($student['university_logo']) ?>" style="height:54px;" alt=""><?php endif; ?>
      <div>
        <div class="uni-name"><?= e($student['university_name']) ?></div>
        <div class="dept-name">Directorate of Distance &amp; Online Education</div>
      </div>
    </div>
    <div class="amity-photo-box">
      <?php if ($student['photo_path']): ?><img src="<?= e($student['photo_path']) ?>" alt="Photo"><?php else: ?>Space for passport size photograph<?php endif; ?>
    </div>
  </div>
  <div class="amity-header-bar"></div>

  <div class="amity-title">Registration cum Enrolment Form</div>
  <div class="text-center mb-3">Academic session: <strong><?= e($student['year_label']) ?></strong></div>

  <div class="amity-field-row">
    <span class="lbl">Program Name:</span><span class="val"><?= e($student['course_name']) ?><?= $student['specialization'] ? ' - ' . e($student['specialization']) : '' ?></span>
    <span class="lbl">Program Code:</span><span class="val"></span>
  </div>

  <div class="amity-field-row">
    <span class="lbl">Full Name of Student:</span>
    <span class="amity-chk"><?= $check($student['gender'] === 'Male') ?> Mr.</span>
    <span class="amity-chk"><?= $check($student['gender'] === 'Female') ?> Mrs.</span>
    <span class="val">Last: <?= e($lastN) ?> &nbsp; Middle: <?= e($middleN) ?> &nbsp; First: <?= e($firstN) ?></span>
  </div>

  <div class="amity-field-row">
    <span class="lbl">Father's Name:</span><span class="val"><?= e($student['father_name']) ?></span>
    <span class="lbl">Mother's Name:</span><span class="val"><?= e($student['mother_name']) ?></span>
  </div>

  <div class="amity-field-row">
    <span class="lbl">Nationality:</span><span class="val"><?= e($student['nationality']) ?></span>
    <span class="lbl">State of Domicile:</span><span class="val"><?= e($student['state']) ?></span>
  </div>

  <div class="amity-field-row">
    <span class="lbl">Date of Birth:</span><span class="val"><?= e($student['dob']) ?></span>
    <span class="lbl">Sex:</span>
    <span class="amity-chk"><?= $check($student['gender'] === 'Male') ?> Male</span>
    <span class="amity-chk"><?= $check($student['gender'] === 'Female') ?> Female</span>
  </div>

  <div class="amity-field-row">
    <span class="lbl">E-mail Address:</span><span class="val"><?= e($student['email']) ?></span>
    <span class="lbl">Contact No.:</span><span class="val"><?= e($student['mobile']) ?></span>
  </div>

  <div class="amity-field-row">
    <span class="lbl">Correspondence Address:</span>
    <span class="val"><?= e($student['address']) ?>, <?= e($student['city']) ?><?= $student['district'] ? ', ' . e($student['district']) : '' ?>, <?= e($student['state']) ?> - <?= e($student['pincode']) ?></span>
  </div>

  <div style="font-weight:700; margin-top:12px;">Educational Qualifications: <span style="font-weight:400; font-size:.75rem;">(including 12 yrs of formal schooling)</span></div>
  <table class="amity-table">
    <thead><tr><th>Name of School/University</th><th>Year of Passing</th><th>Board/College/University</th><th>Main Subject</th><th>Aggregate of Marks</th></tr></thead>
    <tbody>
      <?php $any = false; foreach ($academicLevels as $levelKey => $levelLabel): ?>
        <?php $a = $academicsByLevel[$levelKey] ?? null; if (!$a || empty($a['institution_board'])) continue; $any = true; ?>
        <tr>
          <td><?= e($levelLabel) ?></td>
          <td><?= e($a['year_of_passing']) ?></td>
          <td><?= e($a['institution_board']) ?></td>
          <td><?= e($student['specialization'] ?: '-') ?></td>
          <td><?= e($a['percentage']) ?>%</td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$any): ?><tr><td colspan="5" class="text-center text-muted">No academic history on record.</td></tr><?php endif; ?>
    </tbody>
  </table>

  <div class="text-center mt-4" style="font-size:.75rem;">
    <?= e($student['university_name']) ?> Directorate of Distance &amp; Online Education<br>
    Registration No: <strong><?= e($student['registration_no']) ?></strong> &nbsp; | &nbsp; Issued: <?= date('d M Y') ?>
  </div>
</div>
