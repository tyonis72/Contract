<?php
include('../config/db.php');

// Ambil ID kontrak dari parameter URL
$id = isset($_GET['id']) ? trim($_GET['id']) : '';

if ($id === '') {
  die("<div style='padding:40px; text-align:center; font-family:sans-serif;'>
        <h3>Invalid Contract ID</h3>
        <a href='contract.php'>Kembali ke daftar kontrak</a>
      </div>");
}

$id_safe = $conn->real_escape_string($id);


// Query detail kontrak
$sql = "
  SELECT 
    W.wltk_id,
    W.wltk_no,
    W.wltk_status,
    W.wltk_start_date,
    W.wltk_end_date,
    W.wltk_address,
    W.wltk_document,
    W.wltk_create_date,
    W.wltk_modify_date,
    R.region_title,
    T.city_title
  FROM T_Wltk W
  LEFT JOIN T_Company C ON W.wltk_ref_company = C.company_id
  LEFT JOIN T_Region R ON W.wltk_ref_region = R.region_id
  LEFT JOIN T_City T ON W.wltk_ref_city = T.city_id
  WHERE W.wltk_id = '$id_safe'
";


$result = $conn->query($sql);
$data = $result && $result->num_rows > 0 ? $result->fetch_assoc() : null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contract Detail</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../asset/css/style.css">
</head>

<body>

  <div class="d-flex" id="wrapper">
    <?php $base_url = '../';
    include('../partial/sidebar.php'); ?>

    <!-- Main Content -->
    <div id="page-content" class="flex-grow-1 bg-light">
      <?php $page_title = 'Contract';
      $page_subtitle = 'Detail';
      include('../partial/navbar.php'); ?>

      <div class="container-fluid py-4 px-4">

        <?php if ($data): ?>
          <!-- Contract Information -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-bold">
              <i class="fa fa-file-contract me-2"></i> Contract Information
            </div>
            <div class="card-body">
              <table class="table table-borderless">
                <tr>
                  <th width="200">Contract ID</th>
                  <td><?= $data['wltk_id'] ?></td>
                </tr>
                <tr>
                  <th>Contract No</th>
                  <td><?= htmlspecialchars($data['wltk_no']) ?></td>
                </tr>
                <tr>
                  <th>Status</th>
                  <td><?= ucfirst($data['wltk_status']) ?></td>
                </tr>
                <tr>
                  <th>Start Date</th>
                  <td><?= $data['wltk_start_date'] ?></td>
                </tr>
                <tr>
                  <th>End Date</th>
                  <td><?= $data['wltk_end_date'] ?></td>
                </tr>
                <tr>
                  <th>Document</th>
                  <td><?= $data['wltk_document'] ?: '-' ?></td>
                </tr>
                <tr>
                  <th>Address</th>
                  <td><?= htmlspecialchars($data['wltk_address']) ?></td>
                </tr>
                <tr>
                  <th>City</th>
                  <td><?= $data['city_title'] ?: '-' ?></td>
                </tr>
                <tr>
                  <th>Region</th>
                  <td><?= $data['region_title'] ?: '-' ?></td>
                </tr>
                <tr>
                  <th>Created At</th>
                  <td><?= $data['wltk_create_date'] ?></td>
                </tr>
                <tr>
                  <th>Update At</th>
                  <td><?= $data['wltk_modify_date'] ?></td>
                </tr>
              </table>
            </div>
          </div>

          <!-- Client Information -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-secondary text-white fw-bold">
              <i class="fa fa-building me-2"></i> Client Information
            </div>
            <div class="card-body">
              <table class="table table-borderless">
                <tr>
                  <th>Address</th>
                  <td><?= htmlspecialchars($data['wltk_address']) ?: '-' ?></td>
                </tr>
              </table>
            </div>
          </div>

          <!-- Region Information -->
          <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-info text-white fw-bold">
              <i class="fa fa-map-marker-alt me-2"></i> Region Information
            </div>
            <div class="card-body">
              <table class="table table-borderless">
                <tr>
                  <th width="200">Region</th>
                  <td><?= $data['region_title'] ?: '-' ?></td>
                </tr>
                <tr>
                  <th>City</th>
                  <td><?= $data['city_title'] ?: '-' ?></td>
                </tr>
              </table>
            </div>
          </div>

          <div class="text-center">
            <a href="contract-list.php" class="btn btn-outline-secondary">
              <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar Kontrak
            </a>
          </div>

        <?php else: ?>
          <div class="alert alert-danger text-center">
            Data kontrak tidak ditemukan.
          </div>
          <div class="text-center">
            <a href="contract-list.php" class="btn btn-outline-secondary">
              <i class="fa fa-arrow-left me-1"></i> Kembali ke Daftar Kontrak
            </a>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../asset/js/script.js"></script>
</body>

</html>