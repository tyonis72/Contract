<?php
// ----------------------------------------
// START SESSION (AMAN)
// ----------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ----------------------------------------
// LOAD .env
// ----------------------------------------
$dot = parse_ini_file(__DIR__ . '/../.env');
if ($dot === false) {
    die('.env file not found or unreadable');
}

// ----------------------------------------
// AUTH CHECK
// ----------------------------------------
$allowed = ['login.php', 'login_process.php', 'logout.php'];
$currentScript = basename($_SERVER['PHP_SELF']);
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

if (!in_array($currentScript, $allowed)) {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . $basePath . '/auth/login.php');
        exit();
    }
}

// ----------------------------------------
// DATABASE CONNECTION
// ----------------------------------------
$conn = new mysqli(
    $dot['DB_HOST'],
    $dot['DB_USERNAME'],
    $dot['DB_PASSWORD'],
    $dot['DB_DATABASE'],
    (int)$dot['DB_PORT']
);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
