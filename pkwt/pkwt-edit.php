<?php
include('../config/db.php');

$id = $_GET['id'] ?? '';
if ($id === '') { header("Location: pkwt-list.php"); exit; }

// ambil data lama
$stmt = $conn->prepare("SELECT * FROM T_Pkwt WHERE pkwt_id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$data) { header("Location: pkwt-list.php"); exit; }

// data dropdown
$regions = $conn->query("SELECT region_id, region_title FROM T_Region ORDER BY region_title ASC");
$cities  = $conn->query("SELECT city_id, city_title FROM T_City ORDER BY city_title ASC");
$companies = $conn->query("SELECT company_id, company_name FROM T_Company ORDER BY company_name ASC");
$subcompanies = $conn->query("SELECT subcompany_id, subcompany_name FROM T_Subcompany ORDER BY subcompany_name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $pkwt_no       = $_POST['pkwt_no'] ?? '';
  $pkwt_name     = $_POST['pkwt_name'] ?? '';
  $pkwt_position = $_POST['pkwt_position'] ?? '';
  $pkwt_ktp      = $_POST['pkwt_ktp'] ?? '';
  $pkwt_address  = $_POST['pkwt_address'] ?? '';
  $pkwt_gender   = $_POST['pkwt_gender'] ?? '';
  $pkwt_ref_region    = intval($_POST['pkwt_ref_region'] ?? 0);
  $pkwt_ref_city      = intval($_POST['pkwt_ref_city'] ?? 0);
  $pkwt_ref_company   = intval($_POST['pkwt_ref_company'] ?? 0);
  $pkwt_ref_subcompany= intval($_POST['pkwt_ref_subcompany'] ?? 0);
  $pkwt_start_date    = $_POST['pkwt_start_date'] ?? null;
  $pkwt_end_date      = $_POST['pkwt_end_date'] ?? null;
  $pkwt_salary        = $_POST['pkwt_salary'] ?? '';
  $pkwt_status        = $_POST['pkwt_status'] ?? 'active';
  $pkwt_current       = $_POST['pkwt_current'] ?? 'new';
  $pkwt_document      = $_POST['pkwt_document'] ?? 'no';

  $stmt = $conn->prepare("
    UPDATE T_Pkwt SET
      pkwt_no = ?,
      pkwt_name = ?,
      pkwt_position = ?,
      pkwt_ktp = ?,
      pkwt_address = ?,
      pkwt_gender = ?,
      pkwt_ref_region = ?,
      pkwt_ref_city = ?,
      pkwt_ref_company = ?,
      pkwt_ref_subcompany = ?,
      pkwt_start_date = ?,
      pkwt_end_date = ?,
      pkwt_salary = ?,
      pkwt_status = ?,
      pkwt_current = ?,
      pkwt_document = ?,
      pkwt_modify_date = NOW()
    WHERE pkwt_id = ?
  ");

  // 6 string, 4 int, 3 string (tgl, gaji), 3 string (status,current,doc), 1 int id
  // => "ssssssiiiisssssssi" = 17 tipe
  $stmt->bind_param(
    "ssssssiiiissssssi",
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
    $pkwt_document,
    $id
  );


  $stmt->execute();
  $stmt->close();

  header("Location: pkwt-list.php?msg=updated");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit PKWT</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="card shadow-sm border-0">
    <div class="card-body">
      <h5 class="fw-bold mb-3">Edit PKWT</h5>

      <form method="post">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">PKWT No</label>
            <input type="text" name="pkwt_no" class="form-control"
                   value="<?= htmlspecialchars($data['pkwt_no']) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Name</label>
            <input type="text" name="pkwt_name" class="form-control"
                   value="<?= htmlspecialchars($data['pkwt_name']) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Position</label>
            <input type="text" name="pkwt_position" class="form-control"
                   value="<?= htmlspecialchars($data['pkwt_position']) ?>">
          </div>

          <div class="col-md-4">
            <label class="form-label">KTP</label>
            <input type="text" name="pkwt_ktp" class="form-control"
                   value="<?= htmlspecialchars($data['pkwt_ktp']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Gender</label>
            <input type="text" name="pkwt_gender" class="form-control"
                   value="<?= htmlspecialchars($data['pkwt_gender']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Salary</label>
            <input type="text" name="pkwt_salary" class="form-control"
                   value="<?= htmlspecialchars($data['pkwt_salary']) ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label">Address</label>
            <textarea name="pkwt_address" class="form-control" rows="2"><?= htmlspecialchars($data['pkwt_address']) ?></textarea>
          </div>

          <div class="col-md-3">
            <label class="form-label">Region</label>
            <select name="pkwt_ref_region" class="form-select">
              <option value="">-- Select Region --</option>
              <?php if ($regions): while($r = $regions->fetch_assoc()): ?>
                <option value="<?= $r['region_id']; ?>"
                  <?= $r['region_id'] == $data['pkwt_ref_region'] ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($r['region_title']); ?>
                </option>
              <?php endwhile; endif; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">City</label>
            <select name="pkwt_ref_city" class="form-select">
              <option value="">-- Select City --</option>
              <?php if ($cities): while($c = $cities->fetch_assoc()): ?>
                <option value="<?= $c['city_id']; ?>"
                  <?= $c['city_id'] == $data['pkwt_ref_city'] ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($c['city_title']); ?>
                </option>
              <?php endwhile; endif; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Company</label>
            <select name="pkwt_ref_company" class="form-select">
              <option value="">-- Select Company --</option>
              <?php if ($companies): while($co = $companies->fetch_assoc()): ?>
                <option value="<?= $co['company_id']; ?>"
                  <?= $co['company_id'] == $data['pkwt_ref_company'] ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($co['company_name']); ?>
                </option>
              <?php endwhile; endif; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Subcompany</label>
            <select name="pkwt_ref_subcompany" class="form-select">
              <option value="">-- Select Subcompany --</option>
              <?php if ($subcompanies): while($s = $subcompanies->fetch_assoc()): ?>
                <option value="<?= $s['subcompany_id']; ?>"
                  <?= $s['subcompany_id'] == $data['pkwt_ref_subcompany'] ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($s['subcompany_name']); ?>
                </option>
              <?php endwhile; endif; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="pkwt_start_date" class="form-control"
                   value="<?= htmlspecialchars($data['pkwt_start_date']) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" name="pkwt_end_date" class="form-control"
                   value="<?= htmlspecialchars($data['pkwt_end_date']) ?>">
          </div>

          <div class="col-md-3">
            <label class="form-label">Status</label>
            <input type="text" name="pkwt_status" class="form-control"
                   value="<?= htmlspecialchars($data['pkwt_status']) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Current</label>
            <input type="text" name="pkwt_current" class="form-control"
                   value="<?= htmlspecialchars($data['pkwt_current']) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Document</label>
            <input type="text" name="pkwt_document" class="form-control"
                   value="<?= htmlspecialchars($data['pkwt_document']) ?>">
          </div>
        </div>

        <div class="mt-3">
          <a href="pkwt-list.php" class="btn btn-secondary btn-sm">Back</a>
          <button type="submit" class="btn btn-primary btn-sm">Update</button>
        </div>
      </form>

    </div>
  </div>
  <div class="mt-3">
    <a href="pkwt-list.php" class="btn btn-outline-secondary">
      <i class="fa fa-arrow-left me-1"></i> Kembali ke PKWT List
    </a>
  </div>

</div>
</body>
</html>
