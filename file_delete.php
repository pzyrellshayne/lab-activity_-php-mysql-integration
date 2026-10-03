<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT stored_name FROM files WHERE id = ?');
    $stmt->execute([$id]);
    $f = $stmt->fetch();
    if ($f) {
        $pdo->prepare('DELETE FROM files WHERE id = ?')->execute([$id]);
        $path = __DIR__ . '/uploads/' . basename($f['stored_name']);
        if (is_file($path)) {
            unlink($path);
        }
        flash('File deleted.');
    }
}
go('files.php');
