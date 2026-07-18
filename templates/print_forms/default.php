<style>
  .slip {
    max-width: 760px;
    margin: 0 auto;
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
  }
  .slip-header {
    background: var(--ink);
    color: #fff;
    padding: 24px 32px;
    display: flex;
    align-items: center;
    gap: 16px;
  }
  .slip-body { padding: 32px; }
  .slip-photo {
    width: 110px; height: 130px; object-fit: cover;
    border: 1px solid var(--border); border-radius: 6px; background: #f5f5f5;
  }
  .slip-row { display: flex; padding: 8px 0; border-bottom: 1px dashed var(--border); font-size: .92rem; }
  .slip-row .label { width: 190px; color: var(--muted); font-weight: 500; }
  .slip-footer { padding: 20px 32px; text-align: center; color: var(--muted); font-size: .78rem; border-top: 1px solid var(--border); }
</style>

<div class="slip">
  <div class="slip-header">
    <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="VS Academy" style="width:52px; height:52px; object-fit:contain; flex-shrink:0; background:#fff; border-radius:10px; padding:5px;">
    <div>
      <div style="font-family:var(--font-display); font-size:1.2rem; font-weight:600;">VS Academy</div>
      <div style="font-size:.78rem; color:var(--gold-light); text-transform:uppercase; letter-spacing:.08em;">Provisional Admission Slip</div>
    </div>
    <div class="ms-auto text-end">
      <div class="mono" style="font-size:1rem;"><?= e($student['registration_no']) ?></div>
      <div style="font-size:.72rem; opacity:.7;">Issued <?= date('d M Y') ?></div>
    </div>
  </div>

  <div class="slip-body">
    <div class="d-flex gap-4 mb-3">
      <?php if ($student['photo_path']): ?>
        <img src="<?= e($student['photo_path']) ?>" class="slip-photo" alt="Photo">
      <?php else: ?>
        <div class="slip-photo d-flex align-items-center justify-content-center text-muted small">No Photo</div>
      <?php endif; ?>
      <div class="flex-fill">
        <h4 class="mb-1"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h4>
        <div class="mb-2"><?= statusBadge($student['status']) ?></div>
        <div class="slip-row"><span class="label">Course Applied</span><span><?= e($student['course_name'] ?? '-') ?><?= $student['specialization'] ? ' (' . e($student['specialization']) . ')' : '' ?> — Semester <?= e($student['semester_no']) ?></span></div>
        <div class="slip-row"><span class="label">University</span><span><?= e($student['university_name'] ?? '-') ?></span></div>
        <div class="slip-row"><span class="label">Session</span><span><?= e($student['year_label'] ?? '-') ?></span></div>
      </div>
    </div>

    <div class="slip-row"><span class="label">Date of Birth</span><span><?= e($student['dob']) ?></span></div>
    <div class="slip-row"><span class="label">Gender / Category</span><span><?= e($student['gender']) ?> / <?= e($student['category']) ?></span></div>
    <div class="slip-row"><span class="label">Mobile</span><span><?= e($student['mobile']) ?></span></div>
    <div class="slip-row"><span class="label">Email</span><span><?= e($student['email']) ?: '-' ?></span></div>
    <div class="slip-row"><span class="label">Address</span><span><?= e($student['address']) ?>, <?= e($student['city']) ?>, <?= e($student['state']) ?> - <?= e($student['pincode']) ?></span></div>
    <div class="slip-row"><span class="label">Father's Name</span><span><?= e($student['father_name']) ?></span></div>
    <?php
      $topLevel = $academicsByLevel['UG'] ?? $academicsByLevel['12th'] ?? $academicsByLevel['10th'] ?? null;
    ?>
    <?php if ($topLevel): ?>
      <div class="slip-row"><span class="label">Last Qualification</span><span><?= e($topLevel['institution_board']) ?> (<?= e($topLevel['percentage']) ?>%)</span></div>
    <?php elseif ($student['last_qualification']): ?>
      <div class="slip-row"><span class="label">Last Qualification</span><span><?= e($student['last_qualification']) ?> (<?= e($student['percentage']) ?>%)</span></div>
    <?php endif; ?>
    <div class="slip-row">
      <span class="label">Fee Status</span>
      <span>
        <?php if ($fee && $fee['status'] === 'verified'): ?>
          Paid ₹<?= number_format($fee['amount'], 2) ?> — <?= statusBadge('verified') ?>
        <?php else: ?>
          <span class="badge bg-light text-dark border">Not Yet Verified</span>
        <?php endif; ?>
      </span>
    </div>
  </div>

  <div class="slip-footer">
    This is a system-generated provisional admission slip and does not require a signature.
  </div>
</div>
