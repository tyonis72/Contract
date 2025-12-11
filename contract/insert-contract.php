<?php
include('../config/db.php');

// Cek jika request bukan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../contract-list.php");
    exit;
}

// Ambil data dari form
$wltk_id            = $_POST['wltk_id'] ?? '';
$wltk_no            = $_POST['wltk_no'] ?? '';
$wltk_ref_company   = $_POST['wltk_ref_company'] ?? '';
$wltk_ref_subcompany= $_POST['wltk_ref_subcompany'] ?? NULL;
$wltk_ref_region    = $_POST['wltk_ref_region'] ?? '';
$wltk_ref_city      = $_POST['wltk_ref_city'] ?? '';
$wltk_start_date    = $_POST['wltk_start_date'] ?? '';
$wltk_end_date      = $_POST['wltk_end_date'] ?? '';
$wltk_status        = $_POST['wltk_status'] ?? 'active';
$wltk_document      = $_POST['wltk_document'] ?? 'no';
$wltk_address       = $_POST['wltk_address'] ?? NULL;
$wltk_notes         = $_POST['wltk_notes'] ?? NULL;

// Validasi minimal
if (empty($wltk_id) || empty($wltk_no)) {
    die("❌ Error: WLTK ID atau WLTK No tidak boleh kosong.");
}

// Query Insert
$sql = "
INSERT INTO T_Wltk (
    wltk_id, wltk_no, wltk_ref_company, wltk_ref_subcompany,
    wltk_ref_region, wltk_ref_city, wltk_start_date, wltk_end_date,
    wltk_status, wltk_document, wltk_address, wltk_notes,
    wltk_create_date
) VALUES (
    '$wltk_id', '$wltk_no', '$wltk_ref_company', 
    " . ($wltk_ref_subcompany !== NULL ? "'$wltk_ref_subcompany'" : "NULL") . ",
    '$wltk_ref_region', '$wltk_ref_city', 
    '$wltk_start_date', '$wltk_end_date',
    '$wltk_status', '$wltk_document',
    " . ($wltk_address !== NULL ? "'$wltk_address'" : "NULL") . ",
    " . ($wltk_notes !== NULL ? "'$wltk_notes'" : "NULL") . ",
    NOW()
)
";

// Eksekusi query
if ($conn->query($sql)) {
    header("Location: contract-list.php?success=1");
    exit;
} else {
    echo "❌ Gagal menyimpan data: " . $conn->error;
}
?>
