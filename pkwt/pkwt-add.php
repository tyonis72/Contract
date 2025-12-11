<?php
include('../config/db.php');

/* ============================
   LOAD OPTION DROPDOWN
===============================*/
$region  = $conn->query("SELECT * FROM T_Region ORDER BY region_title");
$city    = $conn->query("SELECT * FROM T_City ORDER BY city_title");
$company = $conn->query("SELECT * FROM T_Company ORDER BY company_name");
$subcomp = $conn->query("SELECT * FROM T_Subcompany ORDER BY subcompany_name");

/* ============================
   SAVE DATA
===============================*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $pkwt_no        = trim($_POST['pkwt_no']);
  $pkwt_name      = trim($_POST['pkwt_name']);
  $pkwt_position  = trim($_POST['pkwt_position']);
  $pkwt_ktp       = trim($_POST['pkwt_ktp']);
  $pkwt_address   = trim($_POST['pkwt_address']);
  $pkwt_gender    = trim($_POST['pkwt_gender']);

  $pkwt_ref_region     = intval($_POST['pkwt_ref_region']);
  $pkwt_ref_city       = intval($_POST['pkwt_ref_city']);
  $pkwt_ref_company    = intval($_POST['pkwt_ref_company']);
  $pkwt_ref_subcompany = intval($_POST['pkwt_ref_subcompany']);

  $pkwt_start_date = $_POST['pkwt_start_date'];
  $pkwt_end_date   = $_POST['pkwt_end_date'];
  $pkwt_salary     = $_POST['pkwt_salary'];

  $pkwt_status   = "active";
  $pkwt_current  = "new";
  $pkwt_document = "no";

  // CEK DUPLIKAT NOMOR
  $check = $conn->prepare("SELECT COUNT(*) FROM T_Pkwt WHERE pkwt_no = ?");
  $check->bind_param("s", $pkwt_no);
  $check->execute();
  $check->bind_result($count);
  $check->fetch();
  $check->close();

  if ($count > 0) {
    echo "<script>alert('PKWT Number already used!'); window.history.back();</script>";
    exit;
  }

  /* ============================================================
   1.2 CEK DUPLIKAT KTP
============================================================ */
$checkKtp = $conn->prepare("SELECT COUNT(*) FROM T_Pkwt WHERE pkwt_ktp = ?");
$checkKtp->bind_param("s", $pkwt_ktp);
$checkKtp->execute();
$checkKtp->bind_result($countKtp);
$checkKtp->fetch();
$checkKtp->close();

if ($countKtp > 0) {
  echo "<script>alert('KTP already used!'); window.history.back();</script>";
  exit;
}


  // INSERT NEW
  $stmt = $conn->prepare("
    INSERT INTO T_Pkwt (
      pkwt_no, pkwt_name, pkwt_position, pkwt_ktp, pkwt_address, pkwt_gender,
      pkwt_ref_region, pkwt_ref_city, pkwt_ref_company, pkwt_ref_subcompany,
      pkwt_start_date, pkwt_end_date, pkwt_salary,
      pkwt_status, pkwt_current, pkwt_document, pkwt_create_date
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
  ");

  $stmt->bind_param(
    "ssssssiiiissssss",
    $pkwt_no,
    $pkwt_name,
    $pkwt_position,
    $pkwt_ktp,
    $pkwt_address,
    $pkwt_gender,
    $pkwt_ref_region,
    $pkwt_ref_city,
    $pkwt_ref_company,
    $pkwt_ref_subcompany,
    $pkwt_start_date,
    $pkwt_end_date,
    $pkwt_salary,
    $pkwt_status,
    $pkwt_current,
    $pkwt_document
  );

  if ($stmt->execute()) {
    header("Location: pkwt-list.php?msg=added");
    exit;
  } else {
    echo "<script>alert('Failed to save data!'); window.history.back();</script>";
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add PKWT</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold mb-0">Add PKWT</h3>
    <a href="pkwt-add.php" class="btn btn-success btn-sm">+ Add PKWT</a>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-body">

      <form method="post">
        <div class="row g-3">

          <div class="col-md-4">
            <label class="form-label">PKWT No</label>
            <input type="text" name="pkwt_no" class="form-control" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Name</label>
            <input type="text" name="pkwt_name" class="form-control" required>
          </div>

          <div class="col-md-4">
            <label class="form-label">Position</label>
            <input type="text" name="pkwt_position" class="form-control">
          </div>

          <div class="col-md-4">
            <label class="form-label">KTP</label>
            <input type="text" name="pkwt_ktp" class="form-control">
          </div>

          <div class="col-md-4">
            <label class="form-label">Gender</label>
            <input type="text" name="pkwt_gender" class="form-control" placeholder="L / P">
          </div>

          <div class="col-md-4">
            <label class="form-label">Salary</label>
            <input type="number" name="pkwt_salary" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Address</label>
            <textarea name="pkwt_address" class="form-control" rows="2"></textarea>
          </div>

          <div class="col-md-3">
            <label class="form-label">Region</label>
            <select name="pkwt_ref_region" class="form-select" required>
              <option value="">-- Pilih --</option>
              <?php while ($r = $region->fetch_assoc()): ?>
              <option value="<?= $r['region_id'] ?>"><?= $r['region_title'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">City</label>
            <select name="pkwt_ref_city" class="form-select" required>
              <option value="">-- Pilih --</option>
              <?php while ($c = $city->fetch_assoc()): ?>
              <option value="<?= $c['city_id'] ?>"><?= $c['city_title'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Company</label>
            <select name="pkwt_ref_company" class="form-select" required>
              <option value="">-- Pilih --</option>
              <?php while ($co = $company->fetch_assoc()): ?>
              <option value="<?= $co['company_id'] ?>"><?= $co['company_name'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Subcompany</label>
            <select name="pkwt_ref_subcompany" class="form-select" required>
              <option value="">-- Pilih --</option>
              <?php while ($sc = $subcomp->fetch_assoc()): ?>
              <option value="<?= $sc['subcompany_id'] ?>"><?= $sc['subcompany_name'] ?></option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Start</label>
            <input type="date" name="pkwt_start_date" class="form-control">
          </div>

          <div class="col-md-3">
            <label class="form-label">End</label>
            <input type="date" name="pkwt_end_date" class="form-control">
          </div>

        </div>

        <div class="mt-3">
          <a href="pkwt-list.php" class="btn btn-secondary btn-sm">Back</a>
          <button type="submit" class="btn btn-primary btn-sm">Save</button>
        </div>

      </form>

    </div>
  </div>

</div>
</body>
</html>
