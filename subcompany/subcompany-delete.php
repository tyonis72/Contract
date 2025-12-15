<?php
include('../config/db.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: subcompany-list.php');
    exit;
}

$sql = "DELETE FROM T_Subcompany WHERE subcompany_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    echo "<script>alert('Subcompany deleted'); window.location.href = 'subcompany-list.php';</script>";
} else {
    echo "<script>alert('Failed to delete subcompany: " . addslashes($stmt->error) . "'); window.location.href = 'subcompany-list.php';</script>";
}

exit;
