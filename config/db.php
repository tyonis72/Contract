<?php
// ----------------------------------------
// START SESSION
// ----------------------------------------
session_start();

// ----------------------------------------
// LOAD .env
// ----------------------------------------
$dot = parse_ini_file(__DIR__ . '/../.env');

// ----------------------------------------
// AUTH CHECK
// ----------------------------------------
$allowed = ['login.php', 'login_process.php', 'logout.php'];
$currentScript = basename($_SERVER['PHP_SELF']);

// Hitung base path secara dinamis untuk hosting
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

// Jika bukan halaman login/logout dan tidak ada session → redirect
if (!in_array($currentScript, $allowed)) {

    if (empty($_SESSION['admin_id'])) {

        // Redirect dinamis (AMAN untuk hosting)
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
    $dot['DB_PORT']
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
