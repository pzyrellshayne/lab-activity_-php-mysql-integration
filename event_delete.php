<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('DELETE FROM events WHERE id = ?');
    $stmt->execute([(int)($_POST['id'] ?? 0)]);
    flash('Event deleted.');
}
go('index.php');
