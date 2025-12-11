<?php
include('../config/db.php');

$city_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($city_id <= 0) {
  header("Location: city-list.php");
  exit;
}

/*
  CONTOH CEK RELASI:
  Misal di tabel T_Pkwt ada kolom:
  - pkwt_ref_city_id (FK ke T_City.city_id)
  - pkwt_status      (active / close)
  Sesuaikan query ini dengan strukturmu.
*/

// CEK PKWT YANG MASIH AKTIF
$checkSql = "
  SELECT COUNT(*) AS total
  FROM T_Wltk
  WHERE wltk_ref_city = ?
    AND wltk_status = 'active'
";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $city_id);
$checkStmt->execute();
$checkRes = $checkStmt->get_result();
$used = $checkRes->fetch_assoc()['total'] ?? 0;
$checkStmt->close();

if ($used > 0) {
  echo "<script>
          alert('Data kota tidak dapat dihapus karena masih digunakan pada data WLTK yang aktif.');
          window.location.href = 'city-list.php';
        </script>";
  exit;
}

// JIKA TIDAK DIPAKAI, HAPUS DATA CITY
$delSql = "DELETE FROM T_City WHERE city_id = ?";
$delStmt = $conn->prepare($delSql);
$delStmt->bind_param("i", $city_id);

if ($delStmt->execute()) {
  echo "<script>
          alert('Data kota berhasil dihapus.');
          window.location.href = 'city-list.php';
        </script>";
} else {
  echo "<script>
          alert('Gagal menghapus data kota.');
          window.location.href = 'city-list.php';
        </script>";
}

$delStmt->close();
$conn->close();
