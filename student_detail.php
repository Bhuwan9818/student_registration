<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT s.*, u.full_name as staff_name, c.name as course_name,
                               un.name as university_name, sy.year_label
                        FROM students s
                        LEFT JOIN users u ON u.id = s.created_by
                        LEFT JOIN courses c ON c.id = s.course_id
                        LEFT JOIN universities un ON un.id = s.university_id
                        LEFT JOIN sessions_years sy ON sy.id = s.session_id
                        WHERE s.id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    flash('error', 'Student record not found.');
    redirect(isAdmin() ? 'admin_students.php' : 'my_students.php');
}

// Staff can only view their own students
if (!isAdmin() && $student['created_by'] != $_SESSION['user_id']) {
    flash('error', 'You do not have permission to view this record.');
    redirect('my_students.php');
}

// Admin: approve / reject action
if (isAdmin() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_action'])) {
    $newStatus = $_POST['review_action'] === 'approve' ? 'approved' : 'rejected';
    $upd = $pdo->prepare("UPDATE students SET status = ? WHERE id = ?");
    $upd->execute([$newStatus, $id]);
    logActivity($pdo, $_SESSION['user_id'], $newStatus === 'approved' ? 'approve' : 'reject',
        ucfirst($newStatus) . ' registration for ' . $student['first_name'] . ' ' . $student['last_name'], $id);
    flash('success', "Registration marked as $newStatus.");
    redirect('student_detail.php?id=' . $id);
}

$feeStmt = $pdo->prepare("SELECT f.*, u.full_name as submitted_by_name
                           FROM fees f LEFT JOIN users u ON u.id = f.submitted_by
                           WHERE f.student_id = ? ORDER BY f.id DESC");
$feeStmt->execute([$id]);
$fees = $feeStmt->fetchAll();

$pageTitle = 'Registration - ' . $student['registration_no'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Registration</span>
    <h4><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h4>
  </div>
  <div class="d-flex gap-2">
    <a href="print_slip.php?id=<?= $student['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-print"></i> Print Slip</a>
    <a href="<?= isAdmin() ? 'admin_students.php' : 'my_students.php' ?>" class="btn btn-sm btn-outline-secondary">
      <i class="fa-solid fa-arrow-left"></i> Back
    </a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="table-card p-4 mb-3">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h5 class="mb-0"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h5>
          <span class="text-muted small"><?= e($student['registration_no']) ?></span>
        </div>
        <?= statusBadge($student['status']) ?>
      </div>

      <h6 class="text-primary mt-3">Personal Details</h6>
      <div class="row small mb-2">
        <div class="col-md-4"><strong>DOB:</strong> <?= e($student['dob']) ?></div>
        <div class="col-md-4"><strong>Gender:</strong> <?= e($student['gender']) ?></div>
        <div class="col-md-4"><strong>Category:</strong> <?= e($student['category']) ?></div>
        <div class="col-md-4"><strong>Aadhar:</strong> <?= e($student['aadhar_no']) ?></div>
      </div>

      <h6 class="text-primary mt-3">Contact & Address</h6>
      <div class="row small mb-2">
        <div class="col-md-4"><strong>Mobile:</strong> <?= e($student['mobile']) ?></div>
        <div class="col-md-4"><strong>Alt Mobile:</strong> <?= e($student['alt_mobile']) ?></div>
        <div class="col-md-4"><strong>Email:</strong> <?= e($student['email']) ?></div>
        <div class="col-md-12"><strong>Address:</strong> <?= e($student['address']) ?>, <?= e($student['city']) ?>, <?= e($student['state']) ?> - <?= e($student['pincode']) ?></div>
      </div>

      <h6 class="text-primary mt-3">Guardian Details</h6>
      <div class="row small mb-2">
        <div class="col-md-4"><strong>Father:</strong> <?= e($student['father_name']) ?></div>
        <div class="col-md-4"><strong>Mother:</strong> <?= e($student['mother_name']) ?></div>
        <div class="col-md-4"><strong>Guardian Mobile:</strong> <?= e($student['guardian_mobile']) ?></div>
      </div>

      <h6 class="text-primary mt-3">Academic Background</h6>
      <div class="row small mb-2">
        <div class="col-md-4"><strong>Last Qualification:</strong> <?= e($student['last_qualification']) ?></div>
        <div class="col-md-4"><strong>Board/University:</strong> <?= e($student['board_university']) ?></div>
        <div class="col-md-4"><strong>Passing Year:</strong> <?= e($student['passing_year']) ?></div>
        <div class="col-md-4"><strong>Percentage:</strong> <?= e($student['percentage']) ?>%</div>
        <?php if ($student['marksheet_path']): ?>
        <div class="col-md-4"><a href="<?= e($student['marksheet_path']) ?>" target="_blank">View Marksheet</a></div>
        <?php endif; ?>
      </div>

      <h6 class="text-primary mt-3">Course Applied For</h6>
      <div class="row small mb-2">
        <div class="col-md-4"><strong>University:</strong> <?= e($student['university_name']) ?></div>
        <div class="col-md-4"><strong>Course:</strong> <?= e($student['course_name']) ?></div>
        <div class="col-md-4"><strong>Session:</strong> <?= e($student['year_label']) ?></div>
      </div>

      <div class="text-muted small mt-3">Submitted by <?= e($student['staff_name']) ?> on <?= date('d M Y, h:i A', strtotime($student['created_at'])) ?></div>
    </div>

    <?php if (isAdmin() && $student['status'] === 'submitted'): ?>
    <div class="table-card p-3">
      <h6>Review this registration</h6>
      <form method="POST" class="d-flex gap-2">
        <button type="submit" name="review_action" value="approve" class="btn btn-success btn-sm"><i class="fa-solid fa-check"></i> Approve</button>
        <button type="submit" name="review_action" value="reject" class="btn btn-danger btn-sm"><i class="fa-solid fa-xmark"></i> Reject</button>
      </form>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-4">
    <div class="table-card p-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">Fee Status</h6>
        <?php if (!isAdmin()): ?>
          <a href="submit_fee.php?student_id=<?= $student['id'] ?>" class="btn btn-sm btn-primary">Submit Fee</a>
        <?php endif; ?>
      </div>

      <?php if (!$fees): ?>
        <p class="text-muted small mb-0">No fee submitted yet.</p>
      <?php endif; ?>

      <?php foreach ($fees as $f): ?>
        <div class="border rounded p-2 mb-2 small">
          <div class="d-flex justify-content-between">
            <strong>₹<?= number_format($f['amount'], 2) ?></strong>
            <?= statusBadge($f['status']) ?>
          </div>
          <div>Mode: <?= e($f['mode']) ?> (<?= e($f['entry_type']) ?>)</div>
          <?php if ($f['utr_no']): ?><div>UTR: <?= e($f['utr_no']) ?></div><?php endif; ?>
          <?php if ($f['proof_path']): ?><div><a href="<?= e($f['proof_path']) ?>" target="_blank">View Proof</a></div><?php endif; ?>
          <div class="text-muted">By <?= e($f['submitted_by_name']) ?> on <?= date('d M Y', strtotime($f['submitted_at'])) ?></div>

          <?php if (isAdmin() && $f['status'] === 'pending'): ?>
          <form method="POST" action="admin_fees.php" class="d-flex gap-1 mt-2">
            <input type="hidden" name="fee_id" value="<?= $f['id'] ?>">
            <input type="hidden" name="redirect_to" value="student_detail.php?id=<?= $student['id'] ?>">
            <button type="submit" name="fee_action" value="verify" class="btn btn-success btn-sm flex-fill">Verify</button>
            <button type="submit" name="fee_action" value="reject" class="btn btn-danger btn-sm flex-fill">Reject</button>
          </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
