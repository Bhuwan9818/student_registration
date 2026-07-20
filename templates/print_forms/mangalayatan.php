<?php
// Mangalayatan University — data overlaid on the actual scanned form image
?>
<style>
  .ov-wrap { position: relative; width: 100%; max-width: 800px; margin: 0 auto; background: #fff; }
  .ov-wrap img.bg { width: 100%; display: block; }
  .ov-wrap .ov { position: absolute; font-family: Arial, Helvetica, sans-serif; color: #111; font-size: 1.25vw; line-height: 1.1; white-space: nowrap; }
  .ov-photo { position: absolute; object-fit: cover; }
  .ov-note { position: absolute; background: #fff8e1; border: 1px solid #f0a500; border-radius: 4px; padding: 4px 10px; font-weight: 700; font-family: Arial, Helvetica, sans-serif; font-size: 1.3vw; }
  @media print {
    .ov-wrap .ov { font-size: 10px; }
    .ov-note { font-size: 11px; }
  }
</style>

<div class="ov-wrap">
  <img class="bg" src="<?= BASE_URL ?>/assets/print_forms/mangalayatan.jpg" alt="Mangalayatan University Form">

  <div class="ov" style="left:76%; top:1.9%;"><?= e($student['registration_no']) ?></div>

  <div class="ov-note" style="left:5%; top:18.6%; width:88%;">
    Applied Program: <?= e($student['course_name']) ?><?= $student['specialization'] ? ' — ' . e($student['specialization']) : '' ?>
    &nbsp;|&nbsp; Session: <?= e($student['year_label']) ?> &nbsp;|&nbsp; Semester: <?= e($student['semester_no']) ?>
  </div>

  <?php if ($student['photo_path']): ?>
    <img class="ov-photo" src="<?= e($student['photo_path']) ?>" style="left:83%; top:8.6%; width:13%; height:8%;" alt="Photo">
  <?php endif; ?>

  <div class="ov" style="left:5%; top:92.6%;"><?= date('d-m-Y') ?></div>
  <div class="ov" style="left:71%; top:90.6%;"><?= e($student['registration_no']) ?></div>
  <div class="ov" style="left:5%; top:94.3%;"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></div>
  <div class="ov" style="left:48%; top:94.3%;"><?= e($student['father_name']) ?></div>
  <div class="ov" style="left:17%; top:96%;"><?= e($student['course_name']) ?></div>
</div>
