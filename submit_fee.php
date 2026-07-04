<?php
require_once __DIR__ . '/config/config.php';
requireStaff();

$pageTitle = 'Submit Fee';
$studentId = (int)($_GET['student_id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ? AND created_by = ?");
$stmt->execute([$studentId, $_SESSION['user_id']]);
$student = $stmt->fetch();

if (!$student) {
    flash('error', 'Student not found or you do not have access to this record.');
    redirect('my_students.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount    = (float)$_POST['amount'];
    $mode      = $_POST['mode'];
    $entryType = $_POST['entry_type']; // manual | upload

    if ($amount <= 0) {
        flash('error', 'Please enter a valid fee amount.');
        redirect('submit_fee.php?student_id=' . $studentId);
    }

    $utrNo = null;
    $proofPath = null;
    $remarks = trim($_POST['remarks'] ?? '');

    if ($entryType === 'upload') {
        $utrNo = trim($_POST['utr_no'] ?? '');
        $proofPath = handleUpload('proof_file', 'fee_proofs', ['jpg','jpeg','png','pdf']);
        if (!$proofPath && !$utrNo) {
            flash('error', 'Please provide a UTR number or upload a payment proof.');
            redirect('submit_fee.php?student_id=' . $studentId);
        }
    }

    $ins = $pdo->prepare("INSERT INTO fees (student_id, amount, mode, entry_type, utr_no, proof_path, remarks, submitted_by)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $ins->execute([$studentId, $amount, $mode, $entryType, $utrNo, $proofPath, $remarks, $_SESSION['user_id']]);

    flash('success', 'Fee submitted successfully. It will be verified by the admin shortly.');
    redirect('student_detail.php?id=' . $studentId);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="table-card bg-white p-4" style="max-width:650px;">
  <h5 class="mb-1">Submit Fee</h5>
  <p class="text-muted small mb-4">
    For <strong><?= e($student['first_name'] . ' ' . $student['last_name']) ?></strong>
    (<?= e($student['registration_no']) ?>)
  </p>

  <form method="POST" enctype="multipart/form-data">
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label">Fee Amount (₹) *</label>
        <input type="number" step="0.01" name="amount" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Payment Mode *</label>
        <select name="mode" class="form-select" required>
          <option value="Cash">Cash</option>
          <option value="Cheque">Cheque</option>
          <option value="Online">Online Transfer</option>
          <option value="UPI">UPI</option>
          <option value="Card">Card</option>
        </select>
      </div>
    </div>

    <label class="form-label d-block">How would you like to record this payment? *</label>
    <div class="d-flex gap-4 mb-3">
      <div class="form-check">
        <input class="form-check-input" type="radio" name="entry_type" value="manual" id="rManual" checked>
        <label class="form-check-label" for="rManual">Manual Entry</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="entry_type" value="upload" id="rUpload">
        <label class="form-check-label" for="rUpload">Upload Proof (UTR / Screenshot)</label>
      </div>
    </div>

    <div id="manualBlock">
      <div class="mb-3">
        <label class="form-label">Remarks (optional)</label>
        <input type="text" name="remarks" class="form-control" placeholder="e.g. Collected at counter by staff">
      </div>
    </div>

    <div id="uploadBlock" class="d-none">
      <div class="mb-3">
        <label class="form-label">UTR / Transaction Reference No.</label>
        <input type="text" name="utr_no" class="form-control">
      </div>
      <div class="mb-3">
        <label class="form-label">Payment Screenshot / Proof</label>
        <input type="file" name="proof_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
      </div>
    </div>

    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Submit Fee</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
