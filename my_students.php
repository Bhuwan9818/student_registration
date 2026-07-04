<?php
require_once __DIR__ . '/config/config.php';
requireStaff();

$pageTitle = 'My Submissions';
$uid = $_SESSION['user_id'];

$where = ['s.created_by = ?'];
$params = [$uid];

if (!empty($_GET['status'])) {
    $where[] = 's.status = ?';
    $params[] = $_GET['status'];
}
if (!empty($_GET['q'])) {
    $where[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.registration_no LIKE ?)";
    $like = '%' . $_GET['q'] . '%';
    array_push($params, $like, $like, $like);
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

$stmt = $pdo->prepare("SELECT s.*, c.name as course_name,
                        (SELECT status FROM fees f WHERE f.student_id = s.id ORDER BY f.id DESC LIMIT 1) as fee_status
                        FROM students s
                        LEFT JOIN courses c ON c.id = s.course_id
                        $whereSql
                        ORDER BY s.created_at DESC");
$stmt->execute($params);
$students = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<h4 class="mb-3">My Submissions</h4>

<form method="GET" class="table-card bg-white p-3 mb-3">
  <div class="row g-2">
    <div class="col-md-5">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name / reg no" value="<?= e($_GET['q'] ?? '') ?>">
    </div>
    <div class="col-md-4">
      <select name="status" class="form-select form-select-sm">
        <option value="">All Status</option>
        <option value="submitted" <?= (($_GET['status'] ?? '') == 'submitted') ? 'selected' : '' ?>>Submitted</option>
        <option value="approved" <?= (($_GET['status'] ?? '') == 'approved') ? 'selected' : '' ?>>Approved</option>
        <option value="rejected" <?= (($_GET['status'] ?? '') == 'rejected') ? 'selected' : '' ?>>Rejected</option>
      </select>
    </div>
    <div class="col-md-3 d-flex gap-2">
      <button class="btn btn-sm btn-primary flex-fill">Filter</button>
      <a href="my_students.php" class="btn btn-sm btn-outline-secondary flex-fill">Reset</a>
    </div>
  </div>
</form>

<div class="table-card bg-white p-3">
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead class="table-light">
        <tr><th>Reg No</th><th>Name</th><th>Course</th><th>Status</th><th>Fee</th><th>Date</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($students as $s): ?>
        <tr>
          <td><?= e($s['registration_no']) ?></td>
          <td><?= e($s['first_name'] . ' ' . $s['last_name']) ?></td>
          <td><?= e($s['course_name'] ?? '-') ?></td>
          <td><?= statusBadge($s['status']) ?></td>
          <td><?= $s['fee_status'] ? statusBadge($s['fee_status']) : '<span class="badge bg-light text-dark border">Not Paid</span>' ?></td>
          <td><?= date('d M Y', strtotime($s['created_at'])) ?></td>
          <td>
            <a href="student_detail.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
            <?php if (!$s['fee_status']): ?>
              <a href="submit_fee.php?student_id=<?= $s['id'] ?>" class="btn btn-sm btn-outline-success">Pay Fee</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$students): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No submissions found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
