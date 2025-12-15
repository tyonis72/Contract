<?php
include('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['subcompany_id'] ?? 0);
    $subcompany_name = trim($_POST['subcompany_name'] ?? '');

    if ($id <= 0 || $subcompany_name === '') {
        echo "<script>alert('Invalid input'); window.history.back();</script>";
        exit;
    }

    // Cek duplikat nama (kecuali sendiri)
    $checkSql  = "SELECT COUNT(*) AS jml FROM T_Subcompany WHERE subcompany_name = ? AND subcompany_id <> ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $subcompany_name, $id);
    $checkStmt->execute();
    $checkRow = $checkStmt->get_result()->fetch_assoc();
    if (($checkRow['jml'] ?? 0) > 0) {
        echo "<script>alert('Subcompany name already exists'); window.history.back();</script>";
        exit;
    }

    $sql = "UPDATE T_Subcompany SET subcompany_name = ?, subcompany_modify_date = NOW() WHERE subcompany_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $subcompany_name, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Subcompany successfully updated'); window.location.href = 'subcompany-list.php';</script>";
    } else {
        echo "<script>alert('Failed to update subcompany: " . addslashes($stmt->error) . "'); window.history.back();</script>";
    }
    exit;
}

header("Location: subcompany-list.php");
exit;
