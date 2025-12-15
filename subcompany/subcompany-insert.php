<?php
include('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subcompany_ref_company_id = intval($_POST['subcompany_ref_company_id'] ?? 0);
    $subcompany_name = trim($_POST['subcompany_name'] ?? '');

    if ($subcompany_name === '') {
        echo "<script>alert('Subcompany name is required'); window.history.back();</script>";
        exit;
    }

    // Cek duplikat nama
    $checkSql  = "SELECT COUNT(*) AS jml FROM T_Subcompany WHERE subcompany_name = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $subcompany_name);
    $checkStmt->execute();
    $checkRow = $checkStmt->get_result()->fetch_assoc();
    if (($checkRow['jml'] ?? 0) > 0) {
        echo "<script>alert('Subcompany name already exists'); window.history.back();</script>";
        exit;
    }

    $sql = "INSERT INTO T_Subcompany (
    subcompany_ref_company_id,
    subcompany_name,
    subcompany_create_date
  ) VALUES (?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $subcompany_ref_company_id, $subcompany_name);

    if ($stmt->execute()) {
        echo "<script>alert('Subcompany successfully added'); window.location.href = 'subcompany-list.php';</script>";
    } else {
        echo "<script>alert('Failed to add subcompany: " . addslashes($stmt->error) . "'); window.history.back();</script>";
    }
    exit;
}

header("Location: subcompany-list.php");
exit;
