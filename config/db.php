<?php
// Mulai session dan cek autentikasi (kecuali halaman login/processing/logout)
session_start();

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

$host = "db"; // ini nama service di docker-compose.yml
$user = "root";
$pass = "password";
$db   = "ruser420_contract"; // ganti sesuai nama database kamu

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
