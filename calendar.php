<?php
require 'config.php';

$month = $_GET['m'] ?? date('Y-m');
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
    $month = date('Y-m');
}

$first        = new DateTimeImmutable($month . '-01');
$daysInMonth  = (int)$first->format('t');
$startWeekday = (int)$first->format('w');
$prev         = $first->modify('-1 month')->format('Y-m');
$next         = $first->modify('+1 month')->format('Y-m');
$rangeEnd     = $first->modify('first day of next month')->format('Y-m-d');

$stmt = $pdo->prepare('SELECT * FROM events WHERE event_date >= ? AND event_date < ? ORDER BY event_date, id');
$stmt->execute([$first->format('Y-m-d'), $rangeEnd]);
$byDay = [];
foreach ($stmt->fetchAll() as $ev) {
    $byDay[(int)date('j', strtotime($ev['event_date']))][] = $ev;
}

$today = date('Y-m-d');
$cells = (int)(ceil(($startWeekday + $daysInMonth) / 7) * 7);

$title = 'Calendar | CICS-SC';
$page  = 'calendar';
require 'header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <h1 class="m-0">Calendar</h1>
  <a class="btn btn-primary" href="event_form.php?from=calendar">+ Add event</a>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div class="d-flex align-items-center gap-2">
    <a class="btn btn-outline-primary btn-sm" href="?m=<?= e($prev) ?>" aria-label="Previous month">&laquo;</a>
    <strong class="fs-5 px-2"><?= e($first->format('F Y')) ?></strong>
    <a class="btn btn-outline-primary btn-sm" href="?m=<?= e($next) ?>" aria-label="Next month">&raquo;</a>
    <a class="btn btn-link btn-sm" href="calendar.php">Today</a>
  </div>
  <form method="get" class="d-flex gap-2">
    <input class="form-control form-control-sm" type="month" name="m" value="<?= e($month) ?>" aria-label="Jump to month">
    <button class="btn btn-outline-primary btn-sm" type="submit">Go</button>
  </form>
</div>

<div class="panel cal-wrap">
  <table class="cal">
    <thead>
      <tr><?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $d): ?><th scope="col"><?= $d ?></th><?php endforeach; ?></tr>
    </thead>
    <tbody>
    <?php for ($i = 0; $i < $cells; $i++): ?>
      <?php if ($i % 7 === 0): ?><tr><?php endif; ?>
      <?php
      $day = $i - $startWeekday + 1;
      if ($day < 1 || $day > $daysInMonth):
      ?>
        <td class="off"></td>
      <?php else:
        $date = $first->format('Y-m-') . str_pad((string)$day, 2, '0', STR_PAD_LEFT);
      ?>
        <td class="<?= $date === $today ? 'today' : '' ?>">
          <div class="top">
            <span class="day"><?= $day ?></span>
            <a class="add" href="event_form.php?date=<?= e($date) ?>" aria-label="Add an event on <?= e(date('F j', strtotime($date))) ?>">+</a>
          </div>
          <?php foreach ($byDay[$day] ?? [] as $ev): ?>
            <a class="ev badge-<?= e(strtolower($ev['status'])) ?>" href="event_form.php?id=<?= (int)$ev['id'] ?>&from=calendar" title="<?= e($ev['title']) ?>"><?= e($ev['title']) ?></a>
          <?php endforeach; ?>
        </td>
      <?php endif; ?>
      <?php if ($i % 7 === 6): ?></tr><?php endif; ?>
    <?php endfor; ?>
    </tbody>
  </table>
</div>

<div class="legend d-flex flex-wrap gap-2 mt-3">
  <?php foreach ($STATUSES as $s): ?><span class="badge badge-<?= e(strtolower($s)) ?>"><?= e($s) ?></span><?php endforeach; ?>
</div>
<?php require 'footer.php'; ?>
