<?php
$host = 'localhost';
$db   = 'cics_sc_simple';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $ex) {
    die('Cannot connect to the database. Start MySQL, import database.sql, and check config.php.');
}

session_start();
date_default_timezone_set('Asia/Manila');

$STATUSES   = ['Upcoming', 'Done', 'Cancelled'];
$CATEGORIES = ['Proposal', 'Minutes', 'Financial report', 'Form', 'Publication material', 'Other'];
$ALLOWED    = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'png', 'jpg', 'jpeg', 'zip', 'txt'];
$MAX_SIZE   = 10 * 1024 * 1024;

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function flash($message)
{
    $_SESSION['msg'] = $message;
}

function go($page)
{
    header('Location: ' . $page);
    exit;
}

function is_valid_date($value)
{
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d && $d->format('Y-m-d') === $value;
}

function file_size_text($bytes)
{
    return $bytes >= 1048576 ? number_format($bytes / 1048576, 1) . ' MB' : max(1, round($bytes / 1024)) . ' KB';
}
