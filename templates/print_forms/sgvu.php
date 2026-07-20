<?php
// Suresh Gyan Vihar University — data overlaid directly on the actual scanned form image
$check = function ($condition) { return $condition ? '&#10003;' : ''; };
?>
<style>
  .ov-wrap { position: relative; width: 100%; max-width: 800px; margin: 0 auto; background: #fff; }
  .ov-wrap img.bg { width: 100%; display: block; }
  .ov-wrap .ov { position: absolute; font-family: Arial, Helvetica, sans-serif; color: #111; font-size: 1.3vw; line-height: 1.1; white-space: nowrap; }
  .ov-photo { position: absolute; object-fit: cover; }
  @media print {
    .ov-wrap .ov { font-size: 10.5px; }
  }
</style>

<div class="ov-wrap">
  <img class="bg" src="<?= BASE_URL ?>/assets/print_forms/sgvu.jpg" alt="Suresh Gyan Vihar University Form">

  <div class="ov" style="left:33%; top:13.9%; letter-spacing:1px;"><?= e($student['registration_no']) ?></div>
  <div class="ov" style="left:46%; top:18.3%;"><?= e($student['course_name']) ?></div>
  <div class="ov" style="left:23%; top:19.9%;"><?= e($student['specialization'] ?: '-') ?></div>

  <div class="ov" style="left:19%; top:34%; text-transform:uppercase;"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></div>
  <div class="ov" style="left:19%; top:38%; text-transform:uppercase;"><?= e($student['father_name']) ?></div>
  <div class="ov" style="left:19%; top:40.7%; text-transform:uppercase;"><?= e($student['mother_name']) ?></div>

  <div class="ov" style="left:19.5%; top:42.3%;"><?= $check($student['gender'] === 'Male') ?></div>
  <div class="ov" style="left:31%; top:42.3%;"><?= $check($student['gender'] === 'Female') ?></div>
  <div class="ov" style="left:58%; top:41.4%;"><?= e($student['dob']) ?></div>

  <div class="ov" style="left:5%; top:48.5%; white-space:normal; width:42%;"><?= e($student['address']) ?></div>
  <div class="ov" style="left:33%; top:53.5%;"><?= e($student['pincode']) ?></div>
  <div class="ov" style="left:13%; top:55.3%;"><?= e($student['city']) ?></div>
  <div class="ov" style="left:45%; top:55.3%;"><?= e($student['state']) ?></div>
  <div class="ov" style="left:17%; top:58.9%;"><?= e($student['alt_mobile']) ?></div>
  <div class="ov" style="left:17%; top:58.9%;"><?= e($student['mobile']) ?></div>
  <div class="ov" style="left:13%; top:60.5%;"><?= e($student['email']) ?></div>

  <div class="ov" style="left:54%; top:48.5%; white-space:normal; width:42%;"><?= e($student['address']) ?></div>
  <div class="ov" style="left:81%; top:53.5%;"><?= e($student['pincode']) ?></div>
  <div class="ov" style="left:63%; top:55.3%;"><?= e($student['city']) ?></div>
  <div class="ov" style="left:88%; top:55.3%;"><?= e($student['state']) ?></div>
  <div class="ov" style="left:65%; top:58.9%;"><?= e($student['district']) ?></div>
  <div class="ov" style="left:66%; top:58.9%;"><?= e($student['guardian_mobile']) ?></div>
  <div class="ov" style="left:59%; top:60.5%;"><?= e($student['alt_email']) ?></div>

  <div class="ov" style="left:16%; top:66.2%;"><?= $check(strtolower($student['nationality']) === 'indian') ?></div>
  <div class="ov" style="left:27.5%; top:66.2%;"><?= $check(strtolower($student['nationality']) !== 'indian' && $student['nationality']) ?></div>

  <?php
    $catX = ['General' => 19, 'SC' => 29, 'ST' => 35, 'OBC' => 41.5, 'Other' => 90];
    foreach ($catX as $catKey => $x):
  ?>
    <div class="ov" style="left:<?= $x ?>%; top:68.1%;"><?= $check($student['category'] === $catKey) ?></div>
  <?php endforeach; ?>
  <div class="ov" style="left:67%; top:68.1%;"><?= $check($student['employment_status'] === 'Employed') ?></div>
  <div class="ov" style="left:78%; top:68.1%;"><?= $check($student['employment_status'] === 'Unemployed') ?></div>

  <?php
    $sn = 1;
    $rowTop = 82;
    foreach ($academicLevels as $levelKey => $levelLabel):
      $a = $academicsByLevel[$levelKey] ?? null;
      if (!$a || empty($a['institution_board'])) continue;
  ?>
    <div class="ov" style="left:6%; top:<?= $rowTop ?>%;"><?= $sn++ ?></div>
    <div class="ov" style="left:12%; top:<?= $rowTop ?>%;"><?= e($levelLabel) ?></div>
    <div class="ov" style="left:49%; top:<?= $rowTop ?>%;"><?= e($a['year_of_passing']) ?></div>
    <div class="ov" style="left:60%; top:<?= $rowTop ?>%;"><?= e($a['percentage']) ?>%</div>
    <div class="ov" style="left:73%; top:<?= $rowTop ?>%;"><?= e($a['institution_board']) ?></div>
    <?php $rowTop += 2.15; endforeach; ?>

  <?php if ($student['photo_path']): ?>
    <img class="ov-photo" src="<?= e($student['photo_path']) ?>" style="left:83%; top:12.5%; width:14%; height:9%;" alt="Photo">
  <?php endif; ?>
</div>
