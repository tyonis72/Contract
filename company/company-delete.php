<?php
include('../config/db.php');

$company_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($company_id <= 0) {
  header("Location: company-list.php");
  exit;
}

// Cek apakah company dipakai di T_Wltk (misal kontrak aktif)
$checkSql = "
  SELECT COUNT(*) AS total
  FROM T_Wltk
  WHERE wltk_ref_company = ?
    AND wltk_status = 'active'
";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $company_id);
$checkStmt->execute();
$checkRes = $checkStmt->get_result()->fetch_assoc();
$checkStmt->close();

if ($checkRes['total'] > 0) {
  echo "<script>
          alert('Company tidak dapat dihapus karena masih digunakan di contract aktif.');
          window.location.href = 'company-list.php';
        </script>";
  exit;
}

// Jika mau lebih ketat, bisa cek semua status (hapus hanya jika tidak dipakai sama sekali)
// $checkAll = $conn->prepare("SELECT COUNT(*) AS total FROM T_Wltk WHERE wltk_ref_company = ?");
// ...

$delSql = "DELETE FROM T_Company WHERE company_id = ?";
$delStmt = $conn->prepare($delSql);
$delStmt->bind_param("i", $company_id);
$ok = $delStmt->execute();
$delStmt->close();

if ($ok) {
  echo "<script>
          alert('Company berhasil dihapus.');
          window.location.href = 'company-list.php';
        </script>";
} else {
  echo "<script>
          alert('Gagal menghapus company.');
          window.location.href = 'company-list.php';
        </script>";
}
exit;
