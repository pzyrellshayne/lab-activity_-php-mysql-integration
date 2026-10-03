<?php $page = $page ?? ''; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'CICS-SC Management System') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand app-nav">
  <div class="container">
    <a class="navbar-brand" href="index.php">CICS-SC</a>
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link <?= $page === 'schedule' ? 'active' : '' ?>" href="index.php">Schedule</a></li>
      <li class="nav-item"><a class="nav-link <?= $page === 'calendar' ? 'active' : '' ?>" href="calendar.php">Calendar</a></li>
      <li class="nav-item"><a class="nav-link <?= $page === 'files' ? 'active' : '' ?>" href="files.php">Files</a></li>
    </ul>
  </div>
</nav>
<main class="container py-4">
<?php
if (!empty($_SESSION['msg'])) {
    echo '<div class="alert alert-success alert-dismissible fade show">' . e($_SESSION['msg'])
       . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['msg']);
}
