<?php
require 'config.php';

$stmt = $pdo->prepare('SELECT original_name, stored_name FROM files WHERE id = ?');
$stmt->execute([(int)($_GET['id'] ?? 0)]);
$f = $stmt->fetch();

$path = $f ? __DIR__ . '/uploads/' . basename($f['stored_name']) : '';
if (!$f || !is_file($path)) {
    http_response_code(404);
    exit('File not found.');
}

header('Content-Type: application/octet-stream');
header('Content-Length: ' . filesize($path));
header('Content-Disposition: attachment; filename="' . str_replace('"', '', $f['original_name']) . '"');
header('X-Content-Type-Options: nosniff');
readfile($path);
