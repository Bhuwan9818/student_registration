<?php
// Swami Vivekanand Subharti University — data overlaid on the actual scanned form (2 pages)
$check = function ($condition) { return $condition ? '&#10003;' : ''; };
?>
<style>
  .ov-wrap { position: relative; width: 100%; max-width: 800px; margin: 0 auto 24px; background: #fff; }
  .ov-wrap img.bg { width: 100%; display: block; }
  .ov-wrap .ov { position: absolute; font-family: Arial, Helvetica, sans-serif; color: #111; font-size: 1.25vw; line-height: 1.1; white-space: nowrap; }
  .ov-photo { position: absolute; object-fit: cover; }
  @media print {
    .ov-wrap .ov { font-size: 10px; }
    .ov-wrap { page-break-after: always; }
  }
</style>

<!-- Page 1 -->
<div class="ov-wrap">
  <img class="bg" src="<?= BASE_URL ?>/assets/print_forms/svsu_p1.jpg" alt="SVSU Form Page 1">

  <div class="ov" style="left:22%; top:10%;"><?= e($student['registration_no']) ?></div>
  <div class="ov" style="left:37%; top:30.5%;"><?= e($student['course_name']) ?><?= $student['specialization'] ? ' — ' . e($student['specialization']) : '' ?></div>

  <div class="ov" style="left:8%; top:35.8%; text-transform:uppercase;"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></div>
  <div class="ov" style="left:8%; top:42.6%; text-transform:uppercase;"><?= e($student['father_name']) ?></div>
  <div class="ov" style="left:8%; top:49%; text-transform:uppercase;"><?= e($student['mother_name']) ?></div>

  <div class="ov" style="left:16%; top:54.5%;"><?= $check($student['gender'] === 'Male') ?></div>
  <div class="ov" style="left:22.5%; top:54.5%;"><?= $check($student['gender'] === 'Female') ?></div>
  <?php
    $dobParts = $student['dob'] ? explode('-', $student['dob']) : ['', '', ''];
    // dob stored as YYYY-MM-DD
    $dobDay = $dobParts[2] ?? ''; $dobMonth = $dobParts[1] ?? ''; $dobYear = $dobParts[0] ?? '';
  ?>
  <div class="ov" style="left:58%; top:56%;"><?= e($dobDay) ?></div>
  <div class="ov" style="left:66%; top:56%;"><?= e($dobMonth) ?></div>
  <div class="ov" style="left:76%; top:56%;"><?= e($dobYear) ?></div>

  <div class="ov" style="left:8%; top:61.3%; white-space:normal; width:60%;"><?= e($student['address']) ?>, <?= e($student['city']) ?>, <?= e($student['state']) ?></div>
  <div class="ov" style="left:80%; top:64.5%;"><?= e($student['pincode']) ?></div>

  <div class="ov" style="left:8%; top:72%;"><?= e($student['alt_mobile']) ?></div>
  <div class="ov" style="left:33%; top:72%;"><?= e($student['mobile']) ?></div>
  <div class="ov" style="left:61%; top:72%;"><?= e($student['email']) ?></div>

  <div class="ov" style="left:8%; top:86%;"><?= $fee ? e($fee['utr_no'] ?: $fee['mode']) : '' ?></div>
  <div class="ov" style="left:53%; top:86%;"><?= $fee ? date('d-m-Y', strtotime($fee['submitted_at'])) : '' ?></div>
  <div class="ov" style="left:8%; top:89%;"></div>
  <div class="ov" style="left:53%; top:89%;"><?= $fee ? '₹' . number_format($fee['amount'], 2) : '' ?></div>

  <?php if ($student['photo_path']): ?>
    <img class="ov-photo" src="<?= e($student['photo_path']) ?>" style="left:83%; top:8.5%; width:14%; height:8%;" alt="Photo">
  <?php endif; ?>
</div>

<!-- Page 2 -->
<div class="ov-wrap">
  <img class="bg" src="<?= BASE_URL ?>/assets/print_forms/svsu_p2.jpg" alt="SVSU Form Page 2">

  <div class="ov" style="left:8%; top:8.3%;"><?= e($student['nationality']) ?></div>

  <?php
    $catX = ['General' => 57.5, 'OBC' => 64.5, 'SC' => 71.5, 'ST' => 78, 'Other' => 84];
    foreach ($catX as $catKey => $x):
  ?>
    <div class="ov" style="left:<?= $x ?>%; top:15%;"><?= $check($student['category'] === $catKey) ?></div>
  <?php endforeach; ?>

  <div class="ov" style="left:8%; top:13.6%;"><?= e($student['employment_status']) ?></div>

  <?php
    $rowTop = 26;
    foreach ($academicLevels as $levelKey => $levelLabel):
      $a = $academicsByLevel[$levelKey] ?? null;
      if (!$a || empty($a['institution_board'])) continue;
  ?>
    <div class="ov" style="left:8%; top:<?= $rowTop ?>%;"><?= e($levelLabel) ?></div>
    <div class="ov" style="left:30%; top:<?= $rowTop ?>%;"><?= e($student['specialization'] ?: '-') ?></div>
    <div class="ov" style="left:57%; top:<?= $rowTop ?>%;"><?= e($a['year_of_passing']) ?></div>
    <div class="ov" style="left:65%; top:<?= $rowTop ?>%;"><?= e($a['institution_board']) ?></div>
    <div class="ov" style="left:89%; top:<?= $rowTop ?>%;"><?= e($a['percentage']) ?>%</div>
    <?php $rowTop += 5.6; endforeach; ?>
</div>
