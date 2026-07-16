<?php
require_once __DIR__ . '/config/config.php';
requireAdmin();

$courseId = (int)($_GET['course_id'] ?? 0);
$stmt = $pdo->prepare("SELECT c.*, u.name as university_name FROM courses c JOIN universities u ON u.id = c.university_id WHERE c.id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    flash('error', 'Course not found.');
    redirect('admin_master.php');
}

// Add sub-course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $ins = $pdo->prepare("INSERT INTO sub_courses (course_id, name) VALUES (?, ?)");
        $ins->execute([$courseId, $name]);
        flash('success', 'Sub-course added.');
    }
    redirect('sub_courses.php?course_id=' . $courseId);
}

// Edit sub-course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_item'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $upd = $pdo->prepare("UPDATE sub_courses SET name = ? WHERE id = ? AND course_id = ?");
    $upd->execute([$name, $id, $courseId]);
    flash('success', 'Sub-course updated.');
    redirect('sub_courses.php?course_id=' . $courseId);
}

// Toggle active/inactive
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_item'])) {
    $id = (int)$_POST['id'];
    $pdo->prepare("UPDATE sub_courses SET status = IF(status='active','inactive','active') WHERE id = ? AND course_id = ?")->execute([$id, $courseId]);
    flash('success', 'Status updated.');
    redirect('sub_courses.php?course_id=' . $courseId);
}

// Delete sub-course (blocked if students already picked it)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    $id = (int)$_POST['id'];
    $subStmt = $pdo->prepare("SELECT name FROM sub_courses WHERE id = ? AND course_id = ?");
    $subStmt->execute([$id, $courseId]);
    $subName = $subStmt->fetchColumn();

    $inUse = $pdo->prepare("SELECT COUNT(*) FROM students WHERE specialization = ? AND course_id = ?");
    $inUse->execute([$subName, $courseId]);
    if ($subName && $inUse->fetchColumn() > 0) {
        flash('error', 'Cannot delete this sub-course — students are already registered under it. Disable it instead.');
    } else {
        $pdo->prepare("DELETE FROM sub_courses WHERE id = ? AND course_id = ?")->execute([$id, $courseId]);
        flash('success', 'Sub-course deleted.');
    }
    redirect('sub_courses.php?course_id=' . $courseId);
}

$subCourses = $pdo->prepare("SELECT * FROM sub_courses WHERE course_id = ? ORDER BY name");
$subCourses->execute([$courseId]);
$subCourses = $subCourses->fetchAll();

$pageTitle = 'Sub-Courses - ' . $course['name'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <span class="eyebrow"><?= e($course['university_name']) ?> — <?= e($course['name']) ?></span>
    <h4>Sub-Courses</h4>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSubCourseModal"><i class="fa-solid fa-plus"></i> Add Sub-Course</button>
    <a href="admin_master.php" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Master Data</a>
  </div>
</div>

<p class="text-muted small mb-3">These appear as the "Specialization / Sub Course" options when staff register a student under <strong><?= e($course['name']) ?></strong>.</p>

<div class="table-card p-3" style="max-width:600px;">
  <ul class="list-group list-group-flush">
    <?php foreach ($subCourses as $sc): ?>
    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
      <span><?= e($sc['name']) ?></span>
      <span class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-link p-0 text-muted" data-bs-toggle="modal" data-bs-target="#editSubCourseModal<?= $sc['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></button>
        <form method="POST" class="d-inline">
          <input type="hidden" name="id" value="<?= $sc['id'] ?>">
          <button type="submit" name="toggle_item" value="1" class="badge border-0 bg-<?= $sc['status']=='active'?'success':'secondary' ?>"><?= ucfirst($sc['status']) ?></button>
        </form>
        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this sub-course?');">
          <input type="hidden" name="id" value="<?= $sc['id'] ?>">
          <button type="submit" name="delete_item" value="1" class="btn btn-sm btn-link p-0 text-danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
        </form>
      </span>
    </li>

    <!-- Edit Sub-Course Modal -->
    <div class="modal fade" id="editSubCourseModal<?= $sc['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
      <form method="POST">
        <div class="modal-header"><h6 class="modal-title">Edit Sub-Course</h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <input type="hidden" name="id" value="<?= $sc['id'] ?>">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" value="<?= e($sc['name']) ?>" required>
        </div>
        <div class="modal-footer"><button type="submit" name="edit_item" value="1" class="btn btn-primary btn-sm">Save Changes</button></div>
      </form>
    </div></div></div>
    <?php endforeach; ?>
    <?php if (!$subCourses): ?>
      <li class="list-group-item px-0 text-muted small">No sub-courses yet for this course.</li>
    <?php endif; ?>
  </ul>
</div>

<!-- Add Sub-Course Modal -->
<div class="modal fade" id="addSubCourseModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <form method="POST">
    <div class="modal-header"><h6 class="modal-title">Add Sub-Course to <?= e($course['name']) ?></h6><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <label class="form-label">Name</label>
      <input type="text" name="name" class="form-control" placeholder="e.g. English, History, Computer Science" required>
    </div>
    <div class="modal-footer"><button type="submit" name="add_item" value="1" class="btn btn-primary btn-sm">Add</button></div>
  </form>
</div></div></div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
