<?php
ob_start(); // FIX utama
session_start();
require_once '../config/db.php';

$debug = true;
function debugAlert($msg)
{
    echo "<script>console.log('DEBUG: " . addslashes($msg) . "');</script>";
}

// Cek POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($debug) debugAlert("Method bukan POST");
    header("Location: login.php?error=method");
    exit();
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($email === '' || $password === '') {
    if ($debug) debugAlert("Input kosong");
    header("Location: login.php?error=empty");
    exit();
}

if ($debug) debugAlert("Email: $email");

// Query DB
$stmt = $conn->prepare('SELECT * FROM T_Admin WHERE admin_email = ? LIMIT 1');
if (!$stmt) {
    if ($debug) debugAlert("Prepare gagal");
    header('Location: login.php?error=db');
    exit();
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($debug) debugAlert("Query executed");

// Cek user
if ($row = $result->fetch_assoc()) {

    if ($debug) debugAlert("Email ditemukan");

    $hash = $row['admin_password'];
    $isValid = false;

    if (!empty($hash)) {
        if (password_verify($password, $hash)) $isValid = true;
        elseif ($password === $hash) $isValid = true;
    }

    if ($debug) debugAlert($isValid ? "Password benar" : "Password salah");

    if ($isValid) {
        $_SESSION['admin_id'] = $row['admin_id'];
        $_SESSION['admin_email'] = $row['admin_email'];
        $_SESSION['admin_name'] = $row['admin_name'];
        $_SESSION['admin_role'] = $row['admin_role'];
        $_SESSION['admin_pic'] = isset($row['admin_pic']) ? $row['admin_pic'] : '';

        if ($debug) debugAlert("Login OK");

        header('Location: ../index.php?success=login');
        exit();
    } else {
        header('Location: login.php?error=invalid_password');
        exit();
    }
} else {
    if ($debug) debugAlert("Email tidak ditemukan");
    header('Location: login.php?error=not_found');
    exit();
}

ob_end_flush(); // keluarkan output debug
