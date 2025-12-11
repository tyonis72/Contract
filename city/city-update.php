<?php
include('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: city-list.php");
  exit;
}

$city_id          = (int)($_POST['city_id'] ?? 0);
$city_title       = trim($_POST['city_title'] ?? '');
$city_ref_region  = (int)($_POST['city_ref_region_id'] ?? 0);

if ($city_id <= 0 || $city_title === '' || $city_ref_region <= 0) {
  echo "<script>alert('Data tidak lengkap.'); window.history.back();</script>";
  exit;
}

// CEK DUPLIKAT NAMA (case-insensitive) DI REGION YANG SAMA, KECUALI DIRI SENDIRI
$checkSql = "
  SELECT COUNT(*) AS total
  FROM T_City
  WHERE LOWER(city_title) = LOWER(?)
    AND city_ref_region_id = ?
    AND city_id <> ?
";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("sii", $city_title, $city_ref_region, $city_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$exists = $checkResult->fetch_assoc()['total'] ?? 0;
$checkStmt->close();

if ($exists > 0) {
  echo "<script>
          alert('Nama kota tersebut sudah digunakan di region ini. Silakan gunakan nama lain.');
          window.history.back();
        </script>";
  exit;
}

// UPDATE DATA
$sql = "
  UPDATE T_City
  SET city_title = ?, city_ref_region_id = ?
  WHERE city_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $city_title, $city_ref_region, $city_id);

if ($stmt->execute()) {
  echo "<script>
          alert('Data kota berhasil diperbarui.');
          window.location.href = 'city-list.php';
        </script>";
} else {
  echo "<script>
          alert('Gagal memperbarui data kota.');
          window.history.back();
        </script>";
}

$stmt->close();
$conn->close();
