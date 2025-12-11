<?php
include('config/db.php');

// ✅ Update otomatis status contract yang sudah melewati end_date
$conn->query("
  UPDATE T_Wltk 
  SET wltk_status = 'close' 
  WHERE wltk_end_date IS NOT NULL 
    AND wltk_end_date <= CURDATE() 
    AND wltk_status != 'close'
");

// Hitung jumlah project
$total = $conn->query("SELECT COUNT(*) AS total FROM T_Wltk")->fetch_assoc()['total'];
$open  = $conn->query("SELECT COUNT(*) AS open FROM T_Wltk WHERE wltk_status='active'")->fetch_assoc()['open'];
$close = $conn->query("SELECT COUNT(*) AS closep FROM T_Wltk WHERE wltk_status='close'")->fetch_assoc()['closep'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Dashboard | Total Contract</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="asset/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="asset/css/style.css">
</head>

<body>
  <div class="d-flex" id="wrapper">
    <!-- SUCCESS ALERT -->
    <?php if (!empty($_GET['success']) && $_GET['success'] === 'login'): ?>
      <div class="position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999;">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <strong>Selamat datang!</strong> Login berhasil. Anda login sebagai <strong><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; ?>

    <?php $base_url = ''; ?>
    <?php include('partial/sidebar.php'); ?>

    <!-- CONTENT -->
    <div id="page-content" class="bg-light w-100">
      <?php $page_title = 'Dashboard';
      $page_subtitle = 'Control Panel';
      include('partial/navbar.php'); ?>

      <div class="container-fluid py-4 px-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center bg-primary text-white p-3 rounded">
          <h4 class="mb-0 fw-bold">TOTAL CONTRACT</h4>
          <div class="fs-4 fw-bold bg-white text-primary px-3 py-1 rounded"><?= $total ?></div>
        </div>

        <div class="d-flex justify-content-end mt-3 mb-3">
          <a href="contract/add-contract.php" class="btn btn-primary">
            <i class="fa fa-plus me-1"></i> Add Contract
          </a>
        </div>


        <!-- Cards -->
        <div class="row g-3 mt-3 mb-4">
          <div class="col-md-6">
            <div class="card shadow-sm h-100 border-0">
              <div class="card-body text-center">
                <h5 class="fw-bold mb-1"><?= $open ?></h5>
                <p class="text-muted mb-3">ACTIVE</p>
                <i class="fa fa-hourglass-half fa-2x mb-3 text-secondary"></i>
                <a href="contract/contract-list.php?filter=active" class="btn btn-sm btn-primary">
                  More Info <i class="fa fa-arrow-right ms-1"></i>
                </a>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card shadow-sm h-100 border-0">
              <div class="card-body text-center">
                <h5 class="fw-bold mb-1"><?= $close ?></h5>
                <p class="text-muted mb-3">CLOSED</p>
                <i class="fa fa-check-circle fa-2x mb-3 text-secondary"></i>
                <a href="contract/contract-list.php?filter=close" class="btn btn-sm btn-primary">
                  More Info <i class="fa fa-arrow-right ms-1"></i>
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <table class="table table-bordered text-center align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>No</th>
                  <th>Job ID</th>
                  <th>PO No</th>
                  <th>Client Name</th>
                  <th>Region</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $sql = "
                SELECT W.wltk_id, W.wltk_no, C.company_name, R.region_title, 
                       W.wltk_status, W.wltk_start_date, W.wltk_end_date
                FROM T_Wltk W
                LEFT JOIN T_Company C ON W.wltk_ref_company = C.company_id
                LEFT JOIN T_Region R ON W.wltk_ref_region = R.region_id
                ORDER BY W.wltk_create_date DESC
                LIMIT 20
              ";
                $result = $conn->query($sql);
                $no = 1;
                if ($result && $result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                          <td>{$no}</td>
                          <td>{$row['wltk_id']}</td>
                          <td>{$row['wltk_no']}</td>
                          <td>{$row['company_name']}</td>
                          <td>{$row['region_title']}</td>
                          <td>{$row['wltk_start_date']}</td>
                          <td>{$row['wltk_end_date']}</td>
                          <td>{$row['wltk_status']}</td>
                        </tr>";
                    $no++;
                  }
                } else {
                  echo "<tr><td colspan='8' class='text-muted py-4'>No data available</td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="text-center mt-3">
          <a href="contract/contract-list.php" class="btn btn-outline-primary">
            Lihat Selengkapnya <i class="fa fa-arrow-right ms-1"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <script src="asset/vendor/bootstrap/js/bootstrap.min.js"></script>
  <script src="asset/js/script.js"></script>
</body>

</html>