<?php
require_once __DIR__ . '/config/config.php';
requireLogin();

$pageTitle = 'Submit Fee';
$studentId = (int)($_GET['student_id'] ?? 0);

if (isAdmin()) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ? AND created_by = ?");
    $stmt->execute([$studentId, $_SESSION['user_id']]);
}
$student = $stmt->fetch();

if (!$student) {
    flash('error', 'Student not found or you do not have access to this record.');
    redirect(isAdmin() ? 'admin_students.php' : 'my_students.php');
}

// Calculate the center's payable fee using the sharing logic
$feeCalculation = calculateCenterFee($pdo, $studentId);

if (isset($feeCalculation['error'])) {
    flash('error', $feeCalculation['error']);
    redirect(isAdmin() ? 'admin_students.php' : 'my_students.php');
}

$centerPayableAmount = $feeCalculation['center_payable_amount'];
$courseFee = $feeCalculation['course_fee'];
$sharingPct = $feeCalculation['sharing_percentage'];
$discountAmount = $feeCalculation['discount_amount'];
$courseName = $feeCalculation['course_name'];
$periodNo = $feeCalculation['period_no'] ?? $feeCalculation['semester_no'] ?? 1;
$periodLabel = $feeCalculation['period_label'] ?? 'Semester';
$feeFrequency = $feeCalculation['fee_frequency'] ?? 'semester';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'];
    // Proof upload is now mandatory for all submissions, so entry_type is always 'upload'
    $entryType = 'upload';

    // Validate that submitted amount matches calculated amount
    $submittedAmount = (float)$_POST['amount'];
    if (abs($submittedAmount - $centerPayableAmount) > 0.01) {
        flash('error', 'Submitted amount does not match the calculated fee amount. Fee amount is fixed based on course fee and center sharing configuration.');
        redirect('submit_fee.php?student_id=' . $studentId);
    }

    $utrNo = trim($_POST['utr_no'] ?? '');
    $proofPath = null;
    $remarks = trim($_POST['remarks'] ?? '');

    // PROOF UPLOAD IS MANDATORY
    $proofPath = handleUpload('proof_file', 'fee_proofs', ['jpg','jpeg','png','pdf']);
    if (!$proofPath) {
        flash('error', 'Payment proof upload is mandatory. Please upload a screenshot or payment receipt.');
        redirect('submit_fee.php?student_id=' . $studentId);
    }

    // UTR number is required
    if (!$utrNo) {
        flash('error', 'Please provide a UTR / Transaction Reference Number.');
        redirect('submit_fee.php?student_id=' . $studentId);
    }

    $ins = $pdo->prepare("INSERT INTO fees (student_id, amount, mode, entry_type, utr_no, proof_path, remarks, submitted_by)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $ins->execute([$studentId, $centerPayableAmount, $mode, $entryType, $utrNo, $proofPath, $remarks, $_SESSION['user_id']]);

    logActivity($pdo, $_SESSION['user_id'], 'fee_submit',
        'Fee of ₹' . number_format($centerPayableAmount, 2) . ' submitted for ' . $student['first_name'] . ' ' . $student['last_name'], $studentId);

    flash('success', 'Fee submitted successfully. It will be verified by the admin shortly.');
    redirect('student_detail.php?id=' . $studentId);
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="table-card p-4" style="max-width:800px;">
  <h5 class="mb-1">Submit Fee</h5>
  <p class="text-muted small mb-3">
    For <strong><?= e($student['first_name'] . ' ' . $student['last_name']) ?></strong>
    (<?= e($student['registration_no']) ?>) — <?= e($periodLabel) ?> <?= $periodNo ?>
  </p>

  <!-- QR Code Payment Section -->
  <div class="alert alert-info border mb-4">
    <div class="row g-3 align-items-center">
      <div class="col-md-4 text-center">
        <div class="qr-code-container" style="padding:15px; background:#fff; border:1px solid #dee2e6; border-radius:8px; display:inline-block;">
          <img src="<?= BASE_URL ?>/assets/img/qr1.jpeg" alt="Payment QR Code" style="width:200px; height:200px;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
          
        </div>
      </div>
      <div class="col-md-8">
        <h6 class="mb-2">Payment Details</h6>
        <table class="table table-borderless table-sm">
          <tr><td style="width:40%;"><strong>Payable Amount:</strong></td><td><strong class="text-danger">₹<?= number_format($centerPayableAmount, 2) ?></strong></td></tr>
          <tr><td><strong>UPI ID:</strong></td><td><code class="text-primary">7703881681@ptaxis</code></td></tr>
          <tr><td><strong>Beneficiary:</strong></td><td>Ravi Kumar Jha</td></tr>
          <tr><td><strong>App:</strong></td><td>Paytm</td></tr>
          <tr><td><strong>Student:</strong></td><td><?= e($student['first_name'] . ' ' . $student['last_name']) ?></td></tr>
          <tr><td><strong>Reg No:</strong></td><td><?= e($student['registration_no']) ?></td></tr>
          <tr><td><strong>Course:</strong></td><td><?= e($courseName) ?></td></tr>
          <tr><td><strong>Period:</strong></td><td><?= e($periodLabel) ?> <?= $periodNo ?></td></tr>
          <tr><td><strong>Payment Ref:</strong></td><td><code>FEE-<?= e($student['registration_no']) ?>-<?= $studentId ?></code></td></tr>
        </table>
        <div class="form-text mt-2">
          <i class="fa-solid fa-info-circle"></i> Scan the Paytm UPI QR code above with any UPI app, or use the UPI ID <strong>7703881681@ptaxis</strong> to make the payment. After payment, upload the proof below.
        </div>
      </div>
    </div>
  </div>

  <!-- Fee Calculation Breakdown -->
  <div class="alert alert-light border mb-4">
    <h6 class="mb-3"><i class="fa-solid fa-calculator"></i> Fee Calculation Breakdown</h6>
    <div class="row g-2 mb-2">
      <div class="col-md-6"><strong>Course:</strong> <?= e($courseName) ?></div>
      <div class="col-md-6"><strong><?= e($periodLabel) ?>:</strong> <?= $periodNo ?> (<?= e($feeFrequency) ?>)</div>
    </div>
    <div class="row g-2 mb-2">
      <div class="col-md-6">
        <label class="form-label small mb-0">Course Fee (Admin Set)</label>
        <div class="mono fw-bold text-primary">₹<?= number_format($courseFee, 2) ?></div>
      </div>
      <div class="col-md-6">
        <label class="form-label small mb-0">Center Sharing (Discount)</label>
        <div class="mono fw-bold text-success"><span class="text-success">-<?= number_format($sharingPct, 2) ?>%</span></div>
      </div>
    </div>
    <hr class="my-2">
    <div class="row g-2">
      <div class="col-md-6">
        <label class="form-label small mb-0">Center Discount Amount</label>
        <div class="mono fw-bold text-success">₹<?= number_format($discountAmount, 2) ?></div>
      </div>
      <div class="col-md-6">
        <label class="form-label small mb-0"><strong>Amount Center Pays</strong></label>
        <div class="mono fw-bold text-danger fs-5">₹<?= number_format($centerPayableAmount, 2) ?></div>
      </div>
    </div>
    <div class="small text-muted mt-2">
      <i class="fa-solid fa-circle-info"></i> This amount is auto-calculated based on the course fee and your center's sharing configuration. You cannot modify this amount.
    </div>
  </div>

  <form method="POST" enctype="multipart/form-data">
    <!-- Hidden amount field - auto-calculated and validated server-side -->
    <input type="hidden" name="amount" value="<?= $centerPayableAmount ?>">

    <!-- Display-only amount field for UI -->
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label class="form-label">Fee Amount (₹) <span class="text-muted">(Auto-calculated, read-only)</span></label>
        <input type="text" class="form-control mono fw-bold text-danger" value="₹<?= number_format($centerPayableAmount, 2) ?>" readonly style="background-color: #f8f9fa;">
      </div>
      <div class="col-md-6">
        <label class="form-label">Payment Mode *</label>
        <select name="mode" class="form-select" required>
          <option value="UPI">UPI</option>
          <option value="Online">Online Transfer</option>
          <option value="Card">Card</option>
          <option value="Cash">Cash</option>
          <option value="Cheque">Cheque</option>
        </select>
      </div>
    </div>

    <label class="form-label d-block">Payment Proof <span class="text-danger">*</span></label>
    <p class="form-text small mb-3">Proof upload is <strong>mandatory</strong>. Please upload a screenshot of the payment, UPI transaction receipt, or bank receipt.</p>

    <div class="mb-3">
      <label class="form-label">UTR / Transaction Reference No.</label>
      <input type="text" name="utr_no" class="form-control" placeholder="Enter UTR / Transaction ID" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Payment Screenshot / Proof File *</label>
      <input type="file" name="proof_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
      <div class="form-text">Accepted formats: JPG, JPEG, PNG, PDF. Max size: 5MB.</div>
    </div>

    <div class="mb-3">
      <label class="form-label">Remarks (optional)</label>
      <input type="text" name="remarks" class="form-control" placeholder="e.g. Paid via UPI, Transaction successful">
    </div>

    <button type="submit" class="btn btn-success w-auto"><i class="fa-solid fa-check"></i> Submit Fee with Proof</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>