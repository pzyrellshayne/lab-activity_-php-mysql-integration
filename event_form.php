<?php
require 'config.php';

$id    = (int)($_GET['id'] ?? 0);
$event = ['title' => '', 'event_date' => '', 'venue' => '', 'status' => 'Upcoming', 'description' => ''];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([$id]);
    $event = $stmt->fetch();
    if (!$event) {
        flash('That event no longer exists.');
        go('index.php');
    }
} elseif (is_valid_date($_GET['date'] ?? '')) {
    $event['event_date'] = $_GET['date'];
}

$fromCalendar = isset($_GET['date']) || ($_GET['from'] ?? '') === 'calendar';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event['title']       = trim($_POST['title'] ?? '');
    $event['event_date']  = trim($_POST['event_date'] ?? '');
    $event['venue']       = trim($_POST['venue'] ?? '');
    $event['status']      = $_POST['status'] ?? '';
    $event['description'] = trim($_POST['description'] ?? '');

    if ($event['title'] === '' || strlen($event['title']) > 100) {
        $errors[] = 'Enter an event title (up to 100 characters).';
    }
    if (!is_valid_date($event['event_date'])) {
        $errors[] = 'Enter a valid date.';
    }
    if (strlen($event['venue']) > 100) {
        $errors[] = 'Venue must be 100 characters or fewer.';
    }
    if (!in_array($event['status'], $STATUSES)) {
        $errors[] = 'Choose a valid status.';
    }

    if (!$errors) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE events SET title=?, event_date=?, venue=?, status=?, description=? WHERE id=?');
            $stmt->execute([$event['title'], $event['event_date'], $event['venue'], $event['status'], $event['description'], $id]);
            flash('Event updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO events (title, event_date, venue, status, description) VALUES (?,?,?,?,?)');
            $stmt->execute([$event['title'], $event['event_date'], $event['venue'], $event['status'], $event['description']]);
            flash('Event added.');
        }
        go($fromCalendar ? 'calendar.php?m=' . substr($event['event_date'], 0, 7) : 'index.php');
    }
}

$title = ($id ? 'Edit event' : 'Add event') . ' | CICS-SC';
$page  = 'schedule';
require 'header.php';
?>
<h1 class="mb-3"><?= $id ? 'Edit event' : 'Add event' ?></h1>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" class="panel p-4" style="max-width: 680px">
  <div class="mb-3">
    <label class="form-label" for="title">Event title</label>
    <input class="form-control" id="title" name="title" maxlength="100" required value="<?= e($event['title']) ?>">
  </div>
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <label class="form-label" for="event_date">Date</label>
      <input class="form-control" type="date" id="event_date" name="event_date" required value="<?= e($event['event_date']) ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label" for="status">Status</label>
      <select class="form-select" id="status" name="status">
        <?php foreach ($STATUSES as $s): ?><option <?= $s === $event['status'] ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="mb-3">
    <label class="form-label" for="venue">Venue</label>
    <input class="form-control" id="venue" name="venue" maxlength="100" value="<?= e($event['venue']) ?>">
  </div>
  <div class="mb-4">
    <label class="form-label" for="description">Notes</label>
    <textarea class="form-control" id="description" name="description" rows="3"><?= e($event['description']) ?></textarea>
  </div>
  <button class="btn btn-primary" type="submit"><?= $id ? 'Save changes' : 'Add event' ?></button>
  <a class="btn btn-outline-secondary" href="<?= $fromCalendar ? 'calendar.php' . (is_valid_date($event['event_date']) ? '?m=' . e(substr($event['event_date'], 0, 7)) : '') : 'index.php' ?>">Cancel</a>
</form>
<?php require 'footer.php'; ?>
