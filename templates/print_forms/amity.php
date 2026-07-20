<?php
// Amity University — data overlaid directly on the actual scanned form image
$check = function ($condition) { return $condition ? '&#10003;' : ''; };
$fullName = trim($student['first_name'] . ' ' . $student['last_name']);
?>
<style>
  .ov-wrap { position: relative; width: 100%; max-width: 800px; margin: 0 auto; background: #fff; }
  .ov-wrap img.bg { width: 100%; display: block; }
  .ov-wrap .ov { position: absolute; font-family: Arial, Helvetica, sans-serif; color: #111; font-size: 1.35vw; line-height: 1.1; white-space: nowrap; }
  .ov-photo { position: absolute; object-fit: cover; }
  @media print {
    .ov-wrap .ov { font-size: 11px; }
  }
</style>

<div class="ov-wrap">
  <img class="bg" src="<?= BASE_URL ?>/assets/print_forms/amity.jpg" alt="Amity University Form">

  <div class="ov" style="left:20%; top:24.7%; width:25%; white-space:normal; font-size:1.05vw;"><?= e($student['course_name']) ?><?= $student['specialization'] ? ' - ' . e($student['specialization']) : '' ?></div>

  <div class="ov" style="left:34.2%; top:31.2%;"><?= $check($student['gender'] === 'Male') ?></div>
  <div class="ov" style="left:34.2%; top:33.1%;"><?= $check($student['gender'] === 'Female') ?></div>
  <div class="ov" style="left:39%; top:30.6%;"><?= e($student['last_name'] ?: '-') ?></div>
  <div class="ov" style="left:75%; top:30.6%;"><?= e($student['first_name']) ?></div>

  <div class="ov" style="left:15%; top:36.3%;"><?= e($student['father_name']) ?></div>
  <div class="ov" style="left:58%; top:36.3%;"><?= e($student['mother_name']) ?></div>

  <div class="ov" style="left:17%; top:41.6%;"><?= e($student['nationality']) ?></div>
  <div class="ov" style="left:62%; top:41.6%;"><?= e($student['state']) ?></div>

  <div class="ov" style="left:15%; top:45.2%;"><?= e($student['dob']) ?></div>
  <div class="ov" style="left:63.5%; top:46.2%;"><?= $check($student['gender'] === 'Male') ?></div>
  <div class="ov" style="left:73.5%; top:46.2%;"><?= $check($student['gender'] === 'Female') ?></div>

  <div class="ov" style="left:19%; top:50.6%;"><?= e($student['email']) ?></div>
  <div class="ov" style="left:67%; top:50.6%;"><?= e($student['mobile']) ?></div>

  <div class="ov" style="left:30%; top:54.2%; white-space:normal; width:68%;"><?= e($student['address']) ?>, <?= e($student['city']) ?><?= $student['district'] ? ', ' . e($student['district']) : '' ?> - <?= e($student['pincode']) ?></div>

  <?php
    // Educational Qualifications table — up to 3 rows starting around y=83%
    $rows = [];
    foreach ($academicLevels as $levelKey => $levelLabel) {
        $a = $academicsByLevel[$levelKey] ?? null;
        if ($a && !empty($a['institution_board'])) { $rows[] = ['label' => $levelLabel, 'a' => $a]; }
    }
    $rowTop = 83.3;
    foreach (array_slice($rows, 0, 3) as $r):
      $a = $r['a'];
  ?>
    <div class="ov" style="left:30%; top:<?= $rowTop ?>%;"><?= e($r['label']) ?></div>
    <div class="ov" style="left:47%; top:<?= $rowTop ?>%;"><?= e($a['year_of_passing']) ?></div>
    <div class="ov" style="left:55%; top:<?= $rowTop ?>%; white-space:normal; width:14%;"><?= e($a['institution_board']) ?></div>
    <div class="ov" style="left:70%; top:<?= $rowTop ?>%;"><?= e($student['specialization'] ?: '-') ?></div>
    <div class="ov" style="left:85%; top:<?= $rowTop ?>%;"><?= e($a['percentage']) ?>%</div>
    <?php $rowTop += 3.3; endforeach; ?>

  <?php if ($student['photo_path']): ?>
    <img class="ov-photo" src="<?= e($student['photo_path']) ?>" style="left:80.5%; top:14.7%; width:14%; height:11%;" alt="Photo">
  <?php endif; ?>
</div>
