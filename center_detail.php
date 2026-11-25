<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'staff' AND parent_user_id IS NULL");
$stmt->execute([$id]);
$center = $stmt->fetch();

if (!$center) {
    flash('error', 'Center not found.');
    redirect('admin_centers.php');
}

// Get active university for this center (first student's university, or first active)
$activeUni = getActiveUniversity($pdo);

// ---- Handle sharing configuration save ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_sharing'])) {
    $sharingPct = (float)($_POST['sharing_percentage'] ?? 0);
    $courseId = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;

    // Validate
    if ($sharingPct < 0 || $sharingPct > 100) {
        flash('error', 'Sharing percentage must be between 0 and 100.');
    } else {
        saveCenterSharing($pdo, $id, $sharingPct, $courseId);
        flash('success', $courseId ? 'Course-specific sharing saved.' : 'Default sharing saved.');
    }
    redirect('center_detail.php?id=' . $id);
}

// ---- Handle sharing delete ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_sharing'])) {
    $sharingId = (int)$_POST['sharing_id'];
    deleteCenterSharing($pdo, $sharingId);
    flash('success', 'Sharing configuration deleted.');
    redirect('center_detail.php?id=' . $id);
}

$regStmt = $pdo->prepare("SELECT s.*, c.name as course_name, un.name as university_name, sy.year_label,
                           (SELECT status FROM fees f WHERE f.student_id = s.id ORDER BY f.id DESC LIMIT 1) as fee_status,
                           (SELECT amount FROM fees f WHERE f.student_id = s.id ORDER BY f.id DESC LIMIT 1) as fee_amount,
                           (SELECT mode FROM fees f WHERE f.student_id = s.id ORDER BY f.id DESC LIMIT 1) as fee_mode
                           FROM students s
                           LEFT JOIN courses c ON c.id = s.course_id
                           LEFT JOIN universities un ON un.id = s.university_id
                           LEFT JOIN sessions_years sy ON sy.id = s.session_id
                           WHERE s.created_by = ?
                           ORDER BY s.created_at DESC");
$regStmt->execute([$id]);
$registrations = $regStmt->fetchAll();

// Get fee totals with sharing calculations
$feeTotals = getFeeTotalsWithSharing($pdo, $id);

$subcenters = $pdo->query("SELECT id, full_name FROM users WHERE parent_user_id = $id ORDER BY full_name")->fetchAll();

// Get sharing configurations
$sharingConfigs = getAllCenterSharing($pdo, $id);

// Get courses for this university (for sharing dropdown)
$coursesForSharing = [];
if ($activeUni) {
    $coursesForSharing = getCoursesForSharing($pdo, $activeUni['id']);
}

// ---- CSV export ----
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="center_' . preg_replace('/[^a-z0-9]+/i', '_', $center['full_name']) . '_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Reg No', 'Type', 'Name', 'Mobile', 'Course', 'University', 'Session', 'Status', 'Fee Amount', 'Fee Mode', 'Fee Channel', 'Fee Status', 'Date']);
    foreach ($registrations as $r) {
        fputcsv($out, [
            $r['registration_no'], ucfirst($r['registration_type']), $r['first_name'] . ' ' . $r['last_name'], $r['mobile'],
            $r['course_name'], $r['university_name'], $r['year_label'], $r['status'],
            $r['fee_amount'] !== null ? $r['fee_amount'] : '', $r['fee_mode'] ?? '',
            $r['fee_mode'] ? (isOnlineMode($r['fee_mode']) ? 'Online' : 'Offline') : '',
            $r['fee_status'] ?? 'not paid', $r['created_at']
        ]);
    }
    fclose($out);
    exit;
}

$pageTitle = 'Center Report - ' . $center['full_name'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Centers</span>
    <h4><?= e($center['full_name']) ?></h4>
  </div>
  <div class="d-flex gap-2">
    <a href="?id=<?= $id ?>&export=csv" class="btn btn-gold btn-sm"><i class="fa-solid fa-file-arrow-down"></i> Download Report (CSV)</a>
    <a href="admin_centers.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Centers</a>
  </div>
</div>

<!-- Fee Summary Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <i class="fa-solid fa-users stat-icon"></i>
      <div class="stat-label">Total Registrations</div>
      <div class="stat-value"><?= count($registrations) ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <i class="fa-solid fa-wifi stat-icon"></i>
      <div class="stat-label">Online Fees (Net)</div>
      <div class="stat-value" style="font-size:1.5rem;">₹<?= number_format($feeTotals['online_total'], 2) ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <i class="fa-solid fa-money-bill-wave stat-icon"></i>
      <div class="stat-label">Offline Fees (Net)</div>
      <div class="stat-value" style="font-size:1.5rem;">₹<?= number_format($feeTotals['offline_total'], 2) ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <i class="fa-solid fa-sitemap stat-icon"></i>
      <div class="stat-label">Sub-Centers</div>
      <div class="stat-value"><?= count($subcenters) ?></div>
    </div>
  </div>
</div>

<!-- Sharing Summary Cards -->
<?php if ($feeTotals['discount_total'] > 0): ?>
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card" style="border-left-color: var(--success);">
      <i class="fa-solid fa-tags stat-icon" style="color: var(--success);"></i>
      <div class="stat-label">Center Discount (Sharing)</div>
      <div class="stat-value" style="font-size:1.5rem; color: var(--success);">₹<?= number_format($feeTotals['discount_total'], 2) ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card" style="border-left-color: var(--navy);">
      <i class="fa-solid fa-sack-dollar stat-icon" style="color: var(--navy);"></i>
      <div class="stat-label">Net Collected from Center</div>
      <div class="stat-value" style="font-size:1.5rem; color: var(--navy);">₹<?= number_format($feeTotals['net_total'], 2) ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <i class="fa-solid fa-money-bill-trend-up stat-icon"></i>
      <div class="stat-label">Gross Course Fees</div>
      <div class="stat-value" style="font-size:1.5rem;">₹<?= number_format($feeTotals['gross_total'], 2) ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card" style="border-left-color: var(--gold);">
      <i class="fa-solid fa-hand-holding-dollar stat-icon" style="color: var(--gold);"></i>
      <div class="stat-label">Center Saves</div>
      <div class="stat-value" style="font-size:1.5rem; color: var(--gold);">₹<?= number_format($feeTotals['gross_total'] - $feeTotals['net_total'], 2) ?></div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($subcenters): ?>
<div class="table-card p-3 mb-3">
  <div class="section-title mb-2">Sub-Centers under this Center</div>
  <?php foreach ($subcenters as $sc): ?>
    <a href="subcenter_detail.php?id=<?= $sc['id'] ?>" class="badge bg-light text-dark border me-2 mb-2 d-inline-block p-2"><?= e($sc['full_name']) ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Fee Sharing Configuration Section -->
<div class="table-card p-3 mb-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="section-title mb-0">Fee Sharing Configuration</div>
    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#sharingModal">
      <i class="fa-solid fa-plus"></i> Add / Update Sharing
    </button>
  </div>

  <?php if ($sharingConfigs): ?>
  <div class="table-responsive mb-3">
    <table class="table table-sm table-ledger align-middle">
      <thead>
        <tr>
          <th>Type</th>
          <th>Course</th>
          <th>Sharing % (Center Discount)</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($sharingConfigs as $sc): ?>
        <tr>
          <td>
            <span class="badge bg-<?= $sc['course_id'] ? 'info' : 'primary' ?>">
              <?= $sc['course_id'] ? 'Course-Specific' : 'Default (All Courses)' ?>
            </span>
          </td>
          <td><?= $sc['course_name'] ? e($sc['course_name']) : '<span class="text-muted">—</span>' ?></td>
          <td><strong><?= number_format($sc['sharing_percentage'], 2) ?>%</strong>
            <div class="small text-muted">
              Center pays <?= number_format(100 - $sc['sharing_percentage'], 2) ?>% of course fee
            </div>
          </td>
          <td><span class="badge bg-<?= $sc['is_active'] ? 'success' : 'secondary' ?>"><?= $sc['is_active'] ? 'Active' : 'Inactive' ?></span></td>
          <td>
            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this sharing configuration?');">
              <input type="hidden" name="sharing_id" value="<?= $sc['id'] ?>">
              <button type="submit" name="delete_sharing" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="alert alert-light border small mb-0">
    <i class="fa-solid fa-circle-info"></i> No sharing configuration set. Click <strong>Add / Update Sharing</strong> to configure default sharing percentage for this center.
  </div>
  <?php endif; ?>
</div>

<!-- Sharing Configuration Modal -->
<div class="modal fade" id="sharingModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h6 class="modal-title">Configure Fee Sharing</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Scope</label>
            <select name="course_id" class="form-select" id="sharingCourseSelect">
              <option value="">Default (All Courses)</option>
              <?php foreach ($coursesForSharing as $c): ?>
                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Select a course for course-specific sharing, or leave as Default for all courses.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Sharing Percentage <small class="text-muted">(%)</small></label>
            <input type="number" step="0.01" min="0" max="100" name="sharing_percentage" class="form-control" value="0" required>
            <div class="form-text">
              <strong>This is the discount the center gets on the course fee.</strong><br>
              Example: Course fee ₹10,000, Sharing 60% → Center pays ₹4,000 (40%) and saves ₹6,000.
            </div>
          </div>

          <div class="alert alert-info small mt-3">
            <i class="fa-solid fa-circle-info"></i>
            <strong>How it works:</strong>
            Course Fee × (100 - Sharing %) / 100 = Amount Center Pays<br>
            <strong>Examples:</strong>
            • Course ₹10,000, Sharing 60% → Center pays ₹4,000
            • Course ₹25,000, Sharing 50% → Center pays ₹12,500
            • Course ₹50,000, Sharing 30% → Center pays ₹35,000
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="save_sharing" class="btn btn-sm btn-primary">Save Configuration</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="table-card p-3 mb-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="section-title mb-0">Account Details</div>
    <div class="d-flex gap-1">
      <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetModal">Reset Password</button>
      <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal">Edit Details</button>
    </div>
  </div>
  <div class="row small g-2">
    <div class="col-md-3"><strong>Username:</strong> <?= e($center['username']) ?></div>
    <div class="col-md-3">
      <strong>Password:</strong>
      <?php if ($center['plain_password']): ?>
        <span id="pwdMask" class="mono">••••••••</span>
        <span id="pwdReal" class="mono d-none"><?= e($center['plain_password']) ?></span>
        <button type="button" class="btn btn-sm btn-link p-0 ms-1" onclick="togglePwd()"><i class="fa-solid fa-eye" id="pwdIcon"></i></button>
      <?php else: ?>
        <span class="text-muted">not available — reset to view</span>
      <?php endif; ?>
    </div>
    <div class="col-md-3"><strong>Email:</strong> <?= e($center['email'] ?: '-') ?></div>
    <div class="col-md-3"><strong>Aadhar No.:</strong> <?= e($center['aadhar_no'] ?: '-') ?></div>
    <div class="col-md-6"><strong>Address:</strong> <?= e($center['address'] ?: '-') ?></div>
    <div class="col-md-2"><strong>City:</strong> <?= e($center['city'] ?: '-') ?></div>
    <div class="col-md-2"><strong>State:</strong> <?= e($center['state'] ?: '-') ?></div>
    <div class="col-md-2"><strong>Pincode:</strong> <?= e($center['pincode'] ?: '-') ?></div>
  </div>
</div>

<!-- Reset password modal -->
<div class="modal fade" id="resetModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="admin_centers.php">
        <div class="modal-header">
          <h6 class="modal-title">Reset Password - <?= e($center['full_name']) ?></h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="user_id" value="<?= $center['id'] ?>">
          <input type="hidden" name="redirect_to" value="center_detail.php?id=<?= $center['id'] ?>">
          <label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control" minlength="6" required>
        </div>
        <div class="modal-footer">
          <button type="submit" name="reset_password" value="1" class="btn btn-primary btn-sm">Update Password</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit details modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="admin_centers.php">
        <div class="modal-header">
          <h6 class="modal-title">Edit Details - <?= e($center['full_name']) ?></h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="user_id" value="<?= $center['id'] ?>">
          <input type="hidden" name="redirect_to" value="center_detail.php?id=<?= $center['id'] ?>">
          <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= e($center['email']) ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Aadhar Card No. <small class="text-muted">(optional)</small></label>
            <input type="text" name="aadhar_no" class="form-control" value="<?= e($center['aadhar_no']) ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">Address <small class="text-muted">(optional)</small></label>
            <input type="text" name="address" class="form-control" value="<?= e($center['address']) ?>">
          </div>
          <div class="row g-2">
            <div class="col-md-4 mb-2">
              <label class="form-label">City</label>
              <input type="text" name="city" class="form-control" value="<?= e($center['city']) ?>">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">State</label>
              <input type="text" name="state" class="form-control" value="<?= e($center['state']) ?>">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Pincode</label>
              <input type="text" name="pincode" class="form-control" value="<?= e($center['pincode']) ?>">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="edit_details" value="1" class="btn btn-primary btn-sm">Save Details</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function togglePwd() {
  const mask = document.getElementById('pwdMask');
  const real = document.getElementById('pwdReal');
  const icon = document.getElementById('pwdIcon');
  const showing = !real.classList.contains('d-none');
  mask.classList.toggle('d-none', !showing);
  real.classList.toggle('d-none', showing);
  icon.classList.toggle('fa-eye', showing);
  icon.classList.toggle('fa-eye-slash', !showing);
}
</script>

<div class="table-card p-3">
  <div class="section-title mb-3">All Registrations by <?= e($center['full_name']) ?></div>
  <div class="table-responsive">
    <table class="table table-sm table-ledger align-middle">
      <thead>
        <tr><th>Reg No</th><th>Type</th><th>Name</th><th>Course</th><th>Status</th><th>Fee</th><th>Channel</th><th>Date</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($registrations as $r): ?>
        <tr>
          <td class="reg-no"><?= e($r['registration_no']) ?></td>
          <td><span class="badge bg-<?= $r['registration_type'] == 'fresh' ? 'primary' : 'info' ?>"><?= $r['registration_type'] == 'fresh' ? 'Fresh' : 'Re-Reg' ?></span></td>
          <td><?= e($r['first_name'] . ' ' . $r['last_name']) ?></td>
          <td><?= e($r['course_name'] ?? '-') ?></td>
          <td><?= statusBadge($r['status']) ?></td>
          <td><?= $r['fee_status'] ? statusBadge($r['fee_status']) : '<span class="badge bg-light text-dark border">Not Paid</span>' ?></td>
          <td><?= $r['fee_mode'] ? (isOnlineMode($r['fee_mode']) ? 'Online' : 'Offline') : '-' ?></td>
          <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
          <td><a href="student_detail.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$registrations): ?>
          <tr><td colspan="9" class="text-center text-muted py-4">No registrations yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>