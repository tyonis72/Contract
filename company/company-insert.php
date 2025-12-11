<?php
include('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $company_name = trim($_POST['company_name'] ?? '');

  // Validasi wajib isi
  if ($company_name === '') {
    echo "<script>
      alert('Company name is required');
      window.history.back();
    </script>";
    exit;
  }

  // Cek duplikat nama company
  $checkSql  = "SELECT COUNT(*) AS jml FROM T_Company WHERE company_name = ?";
  $checkStmt = $conn->prepare($checkSql);
  $checkStmt->bind_param("s", $company_name);
  $checkStmt->execute();
  $checkRow = $checkStmt->get_result()->fetch_assoc();

  if (($checkRow['jml'] ?? 0) > 0) {
    echo "<script>
      alert('Company name already exists, please use another name.');
      window.history.back();
    </script>";
    exit;
  }

  // Asumsi struktur:
  //  - company_id (AI, PK)
  //  - company_name (NOT NULL)
  //  - company_create_date (NOT NULL, diisi NOW())
  $sql = "
    INSERT INTO T_Company (
      company_name,
      company_create_date
    ) VALUES (
      ?, NOW()
    )
  ";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $company_name);

  if ($stmt->execute()) {
    echo "<script>
      alert('Company successfully added');
      window.location.href = 'company-list.php';
    </script>";
  } else {
    echo "<script>
      alert('Failed to add company: " . addslashes($stmt->error) . "');
      window.history.back();
    </script>";
  }

  exit;
}

// Jika bukan method POST, redirect ke list
header("Location: company-list.php");
exit;
