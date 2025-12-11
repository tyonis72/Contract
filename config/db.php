<?php
// Mulai session dan cek autentikasi (kecuali halaman login/processing/logout)
session_start();

$dot = parse_ini_file(__DIR__.'/../.env');

$allowed = [
  // gunakan nama berkas saja karena kita membandingkan dengan basename($_SERVER['PHP_SELF'])
  'login.php',
  'login_process.php',
  'logout.php'
];
$currentScript = basename($_SERVER['PHP_SELF']);

if (!in_array($currentScript, $allowed)) {
  if (empty($_SESSION['admin_id'])) {
    // Arahkan ke halaman login — sesuaikan path jika aplikasi diletakkan di subfolder lain
    header('Location: /contract/auth/login.php');
    exit();
  }
}

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
