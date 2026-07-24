<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT s.*, u.full_name as staff_name, c.name as course_name,
                               un.name as university_name, un.form_template, sy.year_label
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

// Admin: delete registration
if (isAdmin() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_registration'])) {
    $name = $student['first_name'] . ' ' . $student['last_name'];
    $regNo = $student['registration_no'];
    deleteStudentRecord($pdo, $id);
    logActivity($pdo, $_SESSION['user_id'], 'delete', "Deleted registration $regNo for $name");
    flash('success', "Registration $regNo deleted.");
    redirect('admin_students.php');
}

$feeStmt = $pdo->prepare("SELECT f.*, u.full_name as submitted_by_name
                           FROM fees f LEFT JOIN users u ON u.id = f.submitted_by
                           WHERE f.student_id = ? ORDER BY f.id DESC");
$feeStmt->execute([$id]);
$fees = $feeStmt->fetchAll();

$academicLevels = academicLevelLabels();
$documentTypes  = documentTypeLabels();

$academicsStmt = $pdo->prepare("SELECT * FROM student_academics WHERE student_id = ?");
$academicsStmt->execute([$id]);
$academics = $academicsStmt->fetchAll();

$documentsStmt = $pdo->prepare("SELECT * FROM student_documents WHERE student_id = ?");
$documentsStmt->execute([$id]);
$documents = $documentsStmt->fetchAll();

// Re-registrations often don't re-collect academics/documents — fall back to the original record's
$sourceId = $id;
if (!$academics && !$documents && $student['parent_student_id']) {
    $sourceId = $student['parent_student_id'];
    $academicsStmt->execute([$sourceId]);
    $academics = $academicsStmt->fetchAll();
    $documentsStmt->execute([$sourceId]);
    $documents = $documentsStmt->fetchAll();
}
$academicsByLevel = [];
foreach ($academics as $a) { $academicsByLevel[$a['level']] = $a; }

$pageTitle = 'Registration - ' . $student['registration_no'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Registration</span>
    <h4><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h4>
  </div>
  <div class="d-flex gap-2">
    <!-- <a href="print_slip.php?id=<?= $student['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-print"></i> Print Slip</a> -->
    <?php if (in_array($student['form_template'] ?? '', ['sgvu', 'amity', 'mangalayatan', 'svsu'], true)): ?>
      <a href="print_pixel.php?id=<?= $student['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-file-lines"></i> Print </a>
    <?php endif; ?>
    <?php if (isAdmin()): ?>
      <a href="edit_student.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pen"></i> Edit</a>
      <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"><i class="fa-solid fa-trash"></i> Delete</button>
    <?php endif; ?>
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
          <span class="reg-no"><?= e($student['registration_no']) ?></span>
          <span class="badge bg-<?= $student['registration_type'] == 'fresh' ? 'primary' : 'info' ?> ms-1"><?= $student['registration_type'] == 'fresh' ? 'Fresh' : 'Re-Registration' ?></span>
          <?php if ($student['enrollment_no']): ?>
            <div class="small text-muted mt-1">Enrollment No: <span class="reg-no"><?= e($student['enrollment_no']) ?></span></div>
          <?php endif; ?>
        </div>
        <?= statusBadge($student['status']) ?>
      </div>
      <?php if ($student['registration_type'] === 're-registration' && $student['parent_student_id']): ?>
        <div class="small text-muted mb-3">Continuing from <a href="student_detail.php?id=<?= $student['parent_student_id'] ?>">previous registration</a>.</div>
      <?php endif; ?>

      <h6 class="text-primary mt-3">Personal Details</h6>
      <div class="row small mb-2">
        <div class="col-md-4"><strong>DOB:</strong> <?= e($student['dob']) ?></div>
        <div class="col-md-4"><strong>Gender:</strong> <?= e($student['gender']) ?></div>
        <div class="col-md-4"><strong>Category:</strong> <?= e($student['category']) ?></div>
        <div class="col-md-4"><strong>Aadhar:</strong> <?= e($student['aadhar_no']) ?></div>
        <div class="col-md-4"><strong>ABC ID:</strong> <?= e($student['abc_id'] ?: '-') ?></div>
        <div class="col-md-4"><strong>DEB ID:</strong> <?= e($student['deb_id'] ?: '-') ?></div>
        <div class="col-md-4"><strong>Employment Status:</strong> <?= e($student['employment_status'] ?: '-') ?></div>
        <div class="col-md-4"><strong>Marital Status:</strong> <?= e($student['marital_status'] ?: '-') ?></div>
        <div class="col-md-4"><strong>Religion:</strong> <?= e($student['religion'] ?: '-') ?></div>
        <div class="col-md-4"><strong>Nationality:</strong> <?= e($student['nationality'] ?: '-') ?></div>
      </div>

      <h6 class="text-primary mt-3">Contact & Address</h6>
      <div class="row small mb-2">
        <div class="col-md-4"><strong>Mobile:</strong> <?= e($student['mobile']) ?></div>
        <div class="col-md-4"><strong>Alt Mobile:</strong> <?= e($student['alt_mobile'] ?: '-') ?></div>
        <div class="col-md-4"><strong>Email:</strong> <?= e($student['email'] ?: '-') ?></div>
        <div class="col-md-4"><strong>Alt Email:</strong> <?= e($student['alt_email'] ?: '-') ?></div>
        <div class="col-md-8"><strong>Address:</strong> <?= e($student['address']) ?>, <?= e($student['city']) ?><?= $student['district'] ? ', ' . e($student['district']) : '' ?>, <?= e($student['state']) ?> - <?= e($student['pincode']) ?></div>
      </div>

      <h6 class="text-primary mt-3">Guardian Details</h6>
      <div class="row small mb-2">
        <div class="col-md-4"><strong>Father:</strong> <?= e($student['father_name']) ?></div>
        <div class="col-md-4"><strong>Mother:</strong> <?= e($student['mother_name']) ?></div>
        <div class="col-md-4"><strong>Guardian Mobile:</strong> <?= e($student['guardian_mobile'] ?: '-') ?></div>
      </div>

      <h6 class="text-primary mt-3">Academic Background</h6>
      <?php if ($academicsByLevel): ?>
        <div class="row small mb-2">
          <?php foreach ($academicLevels as $levelKey => $levelLabel): ?>
            <?php if (empty($academicsByLevel[$levelKey])) continue; ?>
            <?php $a = $academicsByLevel[$levelKey]; ?>
            <div class="col-md-6 mb-1">
              <strong><?= e($levelLabel) ?>:</strong> <?= e($a['institution_board']) ?>
              (<?= e($a['year_of_passing'] ?: '-') ?>, <?= e($a['percentage'] ?: '-') ?>%)
              <?php if ($a['marksheet_path']): ?> — <a href="<?= e($a['marksheet_path']) ?>" target="_blank">Marksheet</a><?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php elseif ($student['last_qualification']): ?>
        <div class="row small mb-2">
          <div class="col-md-4"><strong>Last Qualification:</strong> <?= e($student['last_qualification']) ?></div>
          <div class="col-md-4"><strong>Board/University:</strong> <?= e($student['board_university']) ?></div>
          <div class="col-md-4"><strong>Passing Year:</strong> <?= e($student['passing_year']) ?></div>
          <div class="col-md-4"><strong>Percentage:</strong> <?= e($student['percentage']) ?>%</div>
          <?php if ($student['marksheet_path']): ?>
          <div class="col-md-4"><a href="<?= e($student['marksheet_path']) ?>" target="_blank">View Marksheet</a></div>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <p class="text-muted small mb-2">No academic history on record.</p>
      <?php endif; ?>

      <h6 class="text-primary mt-3">Documents</h6>
      <?php if ($documents): ?>
        <div class="row small mb-2">
          <?php foreach ($documents as $d): ?>
            <div class="col-md-4 mb-1"><i class="fa-solid fa-file text-muted"></i> <a href="<?= e($d['file_path']) ?>" target="_blank"><?= e($documentTypes[$d['doc_type']] ?? $d['doc_type']) ?></a></div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-muted small mb-2">No documents uploaded.</p>
      <?php endif; ?>

      <h6 class="text-primary mt-3">Course Applied For</h6>
      <div class="row small mb-2">
        <div class="col-md-4"><strong>University:</strong> <?= e($student['university_name']) ?></div>
        <div class="col-md-4"><strong>Course:</strong> <?= e($student['course_name']) ?></div>
        <div class="col-md-4"><strong>Specialization:</strong> <?= e($student['specialization'] ?: '-') ?></div>
        <div class="col-md-4"><strong>Session:</strong> <?= e($student['year_label']) ?></div>
        <div class="col-md-4"><strong>Semester:</strong> <?= e($student['semester_no']) ?></div>
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

<?php if (isAdmin()): ?>
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h6 class="modal-title text-danger"><i class="fa-solid fa-triangle-exclamation"></i> Delete Registration</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="mb-1">Are you sure you want to permanently delete the registration for
            <strong><?= e($student['first_name'] . ' ' . $student['last_name']) ?></strong>
            (<span class="reg-no"><?= e($student['registration_no']) ?></span>)?</p>
          <p class="text-muted small mb-0">This also removes any fee records tied to this registration. This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="delete_registration" value="1" class="btn btn-sm btn-danger">Yes, Delete It</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
