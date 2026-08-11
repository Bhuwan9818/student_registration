<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$pageTitle = 'Centers';

// Create new Center account
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $fullName = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $aadhar   = trim($_POST['aadhar_no'] ?? '');
    $address  = trim($_POST['address'] ?? '');
    $city     = trim($_POST['city'] ?? '');
    $state    = trim($_POST['state'] ?? '');
    $pincode  = trim($_POST['pincode'] ?? '');

    $check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $check->execute([$username]);

    if ($check->fetch()) {
        flash('error', 'Username already exists. Choose a different one.');
    } elseif (strlen($password) < 6) {
        flash('error', 'Password must be at least 6 characters.');
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $ins = $pdo->prepare("INSERT INTO users (full_name, username, email, password, plain_password, role, parent_user_id, aadhar_no, address, city, state, pincode)
                               VALUES (?, ?, ?, ?, ?, 'staff', NULL, ?, ?, ?, ?, ?)");
        $ins->execute([$fullName, $username, $email, $hash, $password, $aadhar ?: null, $address ?: null, $city ?: null, $state ?: null, $pincode ?: null]);
        logActivity($pdo, $_SESSION['user_id'], 'staff_create', 'New center account created: ' . $fullName);
        flash('success', 'Center account created successfully.');
    }
    redirect('admin_centers.php');
}

// Toggle active/inactive
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $uid = (int)$_POST['user_id'];
    $upd = $pdo->prepare("UPDATE users SET status = IF(status='active','inactive','active') WHERE id = ? AND role = 'staff' AND parent_user_id IS NULL");
    $upd->execute([$uid]);
    flash('success', 'Center status updated.');
    redirect('admin_centers.php');
}

// Reset password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $uid = (int)$_POST['user_id'];
    $newPass = $_POST['new_password'];
    if (strlen($newPass) < 6) {
        flash('error', 'Password must be at least 6 characters.');
    } else {
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE users SET password = ?, plain_password = ? WHERE id = ? AND role = 'staff' AND parent_user_id IS NULL");
        $upd->execute([$hash, $newPass, $uid]);
        flash('success', 'Password reset successfully.');
    }
    redirect($_POST['redirect_to'] ?? 'admin_centers.php');
}

// Edit optional details (KYC / address) — does not touch login credentials
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_details'])) {
    $uid = (int)$_POST['user_id'];
    $upd = $pdo->prepare("UPDATE users SET email = ?, aadhar_no = ?, address = ?, city = ?, state = ?, pincode = ?
                           WHERE id = ? AND role = 'staff' AND parent_user_id IS NULL");
    $upd->execute([
        trim($_POST['email']) ?: null, trim($_POST['aadhar_no']) ?: null, trim($_POST['address']) ?: null,
        trim($_POST['city']) ?: null, trim($_POST['state']) ?: null, trim($_POST['pincode']) ?: null, $uid
    ]);
    flash('success', 'Center details updated.');
    redirect($_POST['redirect_to'] ?? 'admin_centers.php');
}

$centers = $pdo->query("SELECT u.*,
                        (SELECT COUNT(*) FROM students s WHERE s.created_by = u.id) as total_forms,
                        (SELECT COUNT(*) FROM users sc WHERE sc.parent_user_id = u.id) as subcenter_count
                        FROM users u WHERE role = 'staff' AND parent_user_id IS NULL ORDER BY created_at DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow">Administration</span>
    <h4>Centers</h4>
  </div>
  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
    <i class="fa-solid fa-plus"></i> Add Center
  </button>
</div>

<p class="text-muted small mb-3">Centers are the primary registration accounts you create — each can also have its own Sub-Centers (see the Sub-Centers page) working under it.</p>

<div class="table-card p-3">
  <div class="table-responsive">
    <table class="table table-sm table-ledger align-middle">
      <thead class="table-light">
        <tr><th>Name</th><th>Username</th><th>Password</th><th>Email</th><th>Registrations</th><th>Online Fees</th><th>Offline Fees</th><th>Sub-Centers</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($centers as $s): ?>
        <?php $feeTotals = getFeeTotals($pdo, $s['id']); ?>
        <tr>
          <td><a href="center_detail.php?id=<?= $s['id'] ?>"><?= e($s['full_name']) ?></a></td>
          <td><?= e($s['username']) ?></td>
          <td>
            <?php if ($s['plain_password']): ?>
              <span id="pwdMask<?= $s['id'] ?>" class="mono">••••••••</span>
              <span id="pwdReal<?= $s['id'] ?>" class="mono d-none"><?= e($s['plain_password']) ?></span>
              <button type="button" class="btn btn-sm btn-link p-0 ms-1" onclick="togglePwd(<?= $s['id'] ?>)"><i class="fa-solid fa-eye" id="pwdIcon<?= $s['id'] ?>"></i></button>
            <?php else: ?>
              <span class="text-muted small">reset to view</span>
            <?php endif; ?>
          </td>
          <td><?= e($s['email']) ?></td>
          <td><?= $s['total_forms'] ?></td>
          <td>₹<?= number_format($feeTotals['online_total'], 2) ?></td>
          <td>₹<?= number_format($feeTotals['offline_total'], 2) ?></td>
          <td>
            <?php if ($s['subcenter_count'] > 0): ?>
              <a href="admin_subcenters.php?center_id=<?= $s['id'] ?>"><?= $s['subcenter_count'] ?></a>
            <?php else: ?>
              <span class="text-muted">0</span>
            <?php endif; ?>
          </td>
          <td><span class="badge bg-<?= $s['status'] == 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($s['status']) ?></span></td>
          <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
          <td class="d-flex gap-1">
            <a href="center_detail.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">Report</a>
            <form method="POST">
              <input type="hidden" name="user_id" value="<?= $s['id'] ?>">
              <button type="submit" name="toggle_status" value="1" class="btn btn-sm btn-outline-<?= $s['status'] == 'active' ? 'secondary' : 'success' ?>">
                <?= $s['status'] == 'active' ? 'Disable' : 'Enable' ?>
              </button>
            </form>
            <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetModal<?= $s['id'] ?>">Reset Pwd</button>
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal<?= $s['id'] ?>">Edit Details</button>
          </td>
        </tr>

        <!-- Reset password modal -->
        <div class="modal fade" id="resetModal<?= $s['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST">
                <div class="modal-header">
                  <h6 class="modal-title">Reset Password - <?= e($s['full_name']) ?></h6>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="user_id" value="<?= $s['id'] ?>">
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

        <!-- Edit details modal (optional KYC / address fields) -->
        <div class="modal fade" id="editModal<?= $s['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST">
                <div class="modal-header">
                  <h6 class="modal-title">Edit Details - <?= e($s['full_name']) ?></h6>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="user_id" value="<?= $s['id'] ?>">
                  <div class="mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($s['email']) ?>">
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Aadhar Card No. <small class="text-muted">(optional)</small></label>
                    <input type="text" name="aadhar_no" class="form-control" value="<?= e($s['aadhar_no']) ?>">
                  </div>
                  <div class="mb-2">
                    <label class="form-label">Address <small class="text-muted">(optional)</small></label>
                    <input type="text" name="address" class="form-control" value="<?= e($s['address']) ?>">
                  </div>
                  <div class="row g-2">
                    <div class="col-md-4 mb-2">
                      <label class="form-label">City</label>
                      <input type="text" name="city" class="form-control" value="<?= e($s['city']) ?>">
                    </div>
                    <div class="col-md-4 mb-2">
                      <label class="form-label">State</label>
                      <input type="text" name="state" class="form-control" value="<?= e($s['state']) ?>">
                    </div>
                    <div class="col-md-4 mb-2">
                      <label class="form-label">Pincode</label>
                      <input type="text" name="pincode" class="form-control" value="<?= e($s['pincode']) ?>">
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
        <?php endforeach; ?>
        <?php if (!$centers): ?>
          <tr><td colspan="11" class="text-center text-muted py-4">No center accounts yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Create Center modal -->
<div class="modal fade" id="createUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h6 class="modal-title">Add New Center</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-2">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control">
          </div>
          <div class="mb-2">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" minlength="6" required>
          </div>
          <hr>
          <p class="text-muted small mb-2">The fields below are optional and can be filled in later from Edit Details.</p>
          <div class="mb-2">
            <label class="form-label">Aadhar Card No. <small class="text-muted">(optional)</small></label>
            <input type="text" name="aadhar_no" class="form-control">
          </div>
          <div class="mb-2">
            <label class="form-label">Address <small class="text-muted">(optional)</small></label>
            <input type="text" name="address" class="form-control">
          </div>
          <div class="row g-2">
            <div class="col-md-4 mb-2">
              <label class="form-label">City</label>
              <input type="text" name="city" class="form-control">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">State</label>
              <input type="text" name="state" class="form-control">
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label">Pincode</label>
              <input type="text" name="pincode" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="create_user" value="1" class="btn btn-primary btn-sm">Create Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function togglePwd(id) {
  const mask = document.getElementById('pwdMask' + id);
  const real = document.getElementById('pwdReal' + id);
  const icon = document.getElementById('pwdIcon' + id);
  const showing = !real.classList.contains('d-none');
  mask.classList.toggle('d-none', !showing);
  real.classList.toggle('d-none', showing);
  icon.classList.toggle('fa-eye', showing);
  icon.classList.toggle('fa-eye-slash', !showing);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
