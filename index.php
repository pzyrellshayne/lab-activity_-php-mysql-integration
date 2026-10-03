<?php
require 'config.php';

$q      = trim($_GET['q'] ?? '');
$status = in_array($_GET['status'] ?? '', $STATUSES) ? $_GET['status'] : '';

$sql    = 'SELECT e.*, (SELECT COUNT(*) FROM files f WHERE f.event_id = e.id) AS file_count FROM events e WHERE e.title LIKE ?';
$params = ['%' . $q . '%'];
if ($status !== '') {
    $sql .= ' AND e.status = ?';
    $params[] = $status;
}
$sql .= ' ORDER BY e.event_date';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();

$title = 'Schedule | CICS-SC';
$page  = 'schedule';
require 'header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="m-0">Schedule</h1>
  <a class="btn btn-primary" href="event_form.php">+ Add event</a>
</div>

<form method="get" class="row g-2 mb-3">
  <div class="col-md-6"><input class="form-control" type="search" name="q" placeholder="Search events" aria-label="Search events" value="<?= e($q) ?>"></div>
  <div class="col-md-4">
    <select class="form-select" name="status" aria-label="Filter by status" onchange="this.form.submit()">
      <option value="">All statuses</option>
      <?php foreach ($STATUSES as $s): ?><option <?= $s === $status ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2 d-grid"><button class="btn btn-outline-primary" type="submit">Search</button></div>
</form>

<div class="panel">
<?php if (!$events): ?>
  <p class="text-center text-muted p-4 m-0">No events found. <a href="event_form.php">Add one.</a></p>
<?php else: ?>
  <div class="table-responsive">
    <table class="table align-middle m-0">
      <thead><tr><th>Date</th><th>Event</th><th>Venue</th><th>Status</th><th>Files</th><th class="text-end">Actions</th></tr></thead>
      <tbody>
      <?php foreach ($events as $ev): ?>
        <tr>
          <td class="text-nowrap"><?= e(date('M j, Y', strtotime($ev['event_date']))) ?></td>
          <td><strong><?= e($ev['title']) ?></strong><?php if ($ev['description']): ?><div class="text-muted small"><?= e($ev['description']) ?></div><?php endif; ?></td>
          <td><?= e($ev['venue'] ?: 'To be announced') ?></td>
          <td><span class="badge badge-<?= e(strtolower($ev['status'])) ?>"><?= e($ev['status']) ?></span></td>
          <td><a href="files.php?event_id=<?= (int)$ev['id'] ?>"><?= (int)$ev['file_count'] ?></a></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-primary" href="event_form.php?id=<?= (int)$ev['id'] ?>">Edit</a>
            <form method="post" action="event_delete.php" class="d-inline" onsubmit="return confirm('Delete this event?');">
              <input type="hidden" name="id" value="<?= (int)$ev['id'] ?>">
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
