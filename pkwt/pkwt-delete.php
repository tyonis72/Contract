<?php
include('../config/db.php');

$id = $_GET['id'] ?? '';
if ($id !== '') {
  $stmt = $conn->prepare("DELETE FROM T_Pkwt WHERE pkwt_id = ?");
  $stmt->bind_param("s", $id);
  $stmt->execute();
}

header("Location: pkwt-list.php?msg=deleted");
exit;
