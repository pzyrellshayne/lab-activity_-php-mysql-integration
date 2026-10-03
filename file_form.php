<?php
require 'config.php';

$id   = (int)($_GET['id'] ?? 0);
$file = ['title' => '', 'category' => 'Proposal', 'event_id' => (int)($_GET['event_id'] ?? 0)];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM files WHERE id = ?');
    $stmt->execute([$id]);
    $file = $stmt->fetch();
    if (!$file) {
        flash('That file no longer exists.');
        go('files.php');
    }
}
$events = $pdo->query('SELECT id, title, event_date FROM events ORDER BY event_date DESC')->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file['title']    = trim($_POST['title'] ?? '');
    $file['category'] = $_POST['category'] ?? '';
    $file['event_id'] = (int)($_POST['event_id'] ?? 0);

    if ($file['title'] === '' || strlen($file['title']) > 100) {
        $errors[] = 'Enter a file title (up to 100 characters).';
    }
    if (!in_array($file['category'], $CATEGORIES)) {
        $errors[] = 'Choose a valid category.';
    }
    $eventId = null;
    if ($file['event_id'] > 0) {
        $check = $pdo->prepare('SELECT COUNT(*) FROM events WHERE id = ?');
        $check->execute([$file['event_id']]);
        if ($check->fetchColumn()) {
            $eventId = $file['event_id'];
        } else {
            $errors[] = 'The selected event does not exist.';
        }
    }

    if (!$id) {
        $up = $_FILES['document'] ?? null;
        if (!$up || $up['error'] === UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Choose a file to upload.';
        } elseif ($up['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'The upload failed. The file may be larger than the server limit.';
        } else {
            $ext = strtolower(pathinfo($up['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $ALLOWED)) {
                $errors[] = 'That file type is not allowed. Use: ' . implode(', ', $ALLOWED) . '.';
            } elseif ($up['size'] > $MAX_SIZE) {
                $errors[] = 'The file is larger than 10 MB.';
            }
        }
    }

    if (!$errors) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE files SET title=?, category=?, event_id=? WHERE id=?');
            $stmt->execute([$file['title'], $file['category'], $eventId, $id]);
            flash('File details updated.');
        } else {
            $stored = bin2hex(random_bytes(16)) . '.' . $ext;
            $name   = preg_replace('/[^A-Za-z0-9._ -]/', '_', basename($up['name']));
            if (move_uploaded_file($up['tmp_name'], __DIR__ . '/uploads/' . $stored)) {
                $stmt = $pdo->prepare('INSERT INTO files (title, category, original_name, stored_name, size, event_id) VALUES (?,?,?,?,?,?)');
                $stmt->execute([$file['title'], $file['category'], $name, $stored, $up['size'], $eventId]);
                flash('File uploaded.');
            } else {
                $errors[] = 'The server could not save the file. Check that the uploads folder is writable.';
            }
        }
        if (!$errors) {
            go('files.php');
        }
    }
}

$title = ($id ? 'Edit file' : 'Upload file') . ' | CICS-SC';
$page  = 'files';
require 'header.php';
?>
<h1 class="mb-3"><?= $id ? 'Edit file details' : 'Upload file' ?></h1>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="panel p-4" style="max-width: 680px">
  <?php if ($id): ?>
    <p class="text-muted">Current file: <?= e($file['original_name']) ?>. To replace it, delete this entry and upload a new one.</p>
  <?php else: ?>
    <div class="mb-3">
      <label class="form-label" for="document">Document</label>
      <input class="form-control" type="file" id="document" name="document" required>
      <div class="form-text">Up to 10 MB. Allowed: <?= e(implode(', ', $ALLOWED)) ?>.</div>
    </div>
  <?php endif; ?>
  <div class="mb-3">
    <label class="form-label" for="title">Title</label>
    <input class="form-control" id="title" name="title" maxlength="100" required value="<?= e($file['title']) ?>">
  </div>
  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <label class="form-label" for="category">Category</label>
      <select class="form-select" id="category" name="category">
        <?php foreach ($CATEGORIES as $c): ?><option <?= $c === $file['category'] ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label" for="event_id">Linked event</label>
      <select class="form-select" id="event_id" name="event_id">
        <option value="0">None</option>
        <?php foreach ($events as $ev): ?>
          <option value="<?= (int)$ev['id'] ?>" <?= (int)$ev['id'] === (int)$file['event_id'] ? 'selected' : '' ?>><?= e($ev['title']) ?> (<?= e(date('M j, Y', strtotime($ev['event_date']))) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <button class="btn btn-primary" type="submit"><?= $id ? 'Save changes' : 'Upload file' ?></button>
  <a class="btn btn-outline-secondary" href="files.php">Cancel</a>
</form>
<?php require 'footer.php'; ?>
