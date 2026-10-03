<?php
require 'config.php';

$q        = trim($_GET['q'] ?? '');
$category = in_array($_GET['category'] ?? '', $CATEGORIES) ? $_GET['category'] : '';
$eventId  = (int)($_GET['event_id'] ?? 0);

$sql    = 'SELECT f.*, e.title AS event_title FROM files f LEFT JOIN events e ON e.id = f.event_id WHERE (f.title LIKE ? OR f.original_name LIKE ?)';
$params = ['%' . $q . '%', '%' . $q . '%'];
if ($category !== '') {
    $sql .= ' AND f.category = ?';
    $params[] = $category;
}
if ($eventId) {
    $sql .= ' AND f.event_id = ?';
    $params[] = $eventId;
}
$sql .= ' ORDER BY f.uploaded_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$files = $stmt->fetchAll();

$title = 'Files | CICS-SC';
$page  = 'files';
require 'header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="m-0">Files</h1>
  <a class="btn btn-primary" href="file_form.php<?= $eventId ? '?event_id=' . $eventId : '' ?>">+ Upload file</a>
</div>

<form method="get" class="row g-2 mb-3">
  <input type="hidden" name="event_id" value="<?= $eventId ?: '' ?>">
  <div class="col-md-6"><input class="form-control" type="search" name="q" placeholder="Search by title or file name" aria-label="Search files" value="<?= e($q) ?>"></div>
  <div class="col-md-4">
    <select class="form-select" name="category" aria-label="Filter by category" onchange="this.form.submit()">
      <option value="">All categories</option>
      <?php foreach ($CATEGORIES as $c): ?><option <?= $c === $category ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2 d-grid"><button class="btn btn-outline-primary" type="submit">Search</button></div>
</form>
<?php if ($eventId): ?><p class="text-muted">Showing files for one event. <a href="files.php">Show all files</a></p><?php endif; ?>

<div class="panel">
<?php if (!$files): ?>
  <p class="text-center text-muted p-4 m-0">No files found. <a href="file_form.php">Upload one.</a></p>
<?php else: ?>
  <div class="table-responsive">
    <table class="table align-middle m-0">
      <thead><tr><th>Title</th><th>Category</th><th>Event</th><th>Size</th><th>Uploaded</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($files as $f): ?>
        <tr>
          <td><strong><?= e($f['title']) ?></strong><div class="text-muted small"><?= e($f['original_name']) ?></div></td>
          <td><?= e($f['category']) ?></td>
          <td><?= $f['event_title'] ? e($f['event_title']) : '<span class="text-muted">None</span>' ?></td>
          <td class="text-nowrap"><?= e(file_size_text($f['size'])) ?></td>
          <td class="text-nowrap"><?= e(date('M j, Y', strtotime($f['uploaded_at']))) ?></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-primary" href="file_download.php?id=<?= (int)$f['id'] ?>">Download</a>
            <a class="btn btn-sm btn-outline-secondary" href="file_form.php?id=<?= (int)$f['id'] ?>">Edit</a>
            <form method="post" action="file_delete.php" class="d-inline" onsubmit="return confirm('Delete this file permanently?');">
              <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
</div>
<?php require 'footer.php'; ?>
