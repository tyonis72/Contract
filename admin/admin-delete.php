<?php
include('../config/db.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if admin exists
$result = $conn->query("SELECT admin_id FROM T_Admin WHERE admin_id = $id");
if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

// Prevent deleting the current logged-in admin
if ($id === $_SESSION['admin_id']) {
    $_SESSION['error'] = 'Anda tidak dapat menghapus akun sendiri.';
    header('Location: index.php');
    exit();
}

// Delete admin
if ($conn->query("DELETE FROM T_Admin WHERE admin_id = $id")) {
    header('Location: index.php?msg=deleted');
    exit();
} else {
    $_SESSION['error'] = 'Gagal menghapus admin.';
    header('Location: index.php');
    exit();
}
