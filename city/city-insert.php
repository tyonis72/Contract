<?php
include('../config/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $city_title      = trim($_POST['city_title'] ?? '');
  $city_ref_region = (int)($_POST['city_ref_region'] ?? 0);

  if ($city_title !== '' && $city_ref_region > 0) {

    // Cek duplikat nama kota (case-insensitive) di region yang sama
    $checkSql = "
      SELECT COUNT(*) AS total
      FROM T_City
      WHERE LOWER(city_title) = LOWER(?)
        AND city_ref_region_id = ?
    ";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("si", $city_title, $city_ref_region);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $exists = $checkResult->fetch_assoc()['total'] ?? 0;
    $checkStmt->close();

    if ($exists > 0) {
      // Jika sudah ada, tampilkan alert lalu kembali ke form
      echo "<script>
              alert('Nama kota tersebut sudah digunakan di region ini. Silakan gunakan nama lain.');
              window.history.back();
            </script>";
      exit;
    }

    // Insert jika tidak duplikat
    $sql = "
      INSERT INTO T_City (
        city_title,
        city_ref_region_id,
        city_create_date
      ) VALUES (
        ?, ?, NOW()
      )
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $city_title, $city_ref_region);
    $stmt->execute();
    $stmt->close();
  }
}

header("Location: city-list.php?status=success");
exit;
