<?php include('../config/db.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contract List</title>
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
      $page_subtitle = 'List';
      include('../partial/navbar.php'); ?>

      <div class="container-fluid py-4 px-4">

        <!-- Search Bar -->
        <form class="mb-3" method="GET">
          <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan Client Name..."
              value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i> Cari</button>
          </div>
        </form>

        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <table class="table table-bordered text-center align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>No</th>
                  <th>Contract ID</th>
                  <th>Contract No</th>
                  <th>Client Name</th>
                  <th>Region</th>
                  <th>Status</th>
                  <th>Start Date</th>
                  <th>Detail</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Pagination setup
                $limit = 10;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $limit;

                // Search setup
                $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                $where = "";
                if ($search !== '') {
                  $safe = $conn->real_escape_string($search);
                  $where = "WHERE C.company_name LIKE '%$safe%'";
                }

                // Hitung total data
                $countQuery = "
                SELECT COUNT(*) AS total
                FROM T_Wltk W
                LEFT JOIN T_Company C ON W.wltk_ref_company = C.company_id
                $where
              ";
                $countResult = $conn->query($countQuery);
                $totalRows = $countResult->fetch_assoc()['total'];
                $totalPages = ceil($totalRows / $limit);

                // Query data kontrak
                $sql = "
                SELECT W.wltk_id, W.wltk_no, C.company_name, R.region_title, 
                       W.wltk_status, W.wltk_start_date
                FROM T_Wltk W
                LEFT JOIN T_Company C ON W.wltk_ref_company = C.company_id
                LEFT JOIN T_Region R ON W.wltk_ref_region = R.region_id
                $where
                ORDER BY W.wltk_create_date DESC
                LIMIT $offset, $limit
              ";
                $result = $conn->query($sql);
                $no = $offset + 1;

                if ($result && $result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                          <td>{$no}</td>
                          <td>{$row['wltk_id']}</td>
                          <td>{$row['wltk_no']}</td>
                          <td>{$row['company_name']}</td>
                          <td>{$row['region_title']}</td>
                          <td>{$row['wltk_status']}</td>
                          <td>{$row['wltk_start_date']}</td>
                          <td>
                            <a href='contract-detail.php?id={$row['wltk_id']}' class='btn btn-sm btn-outline-primary'>
                              <i class='fa fa-eye'></i> Lihat
                            </a>
                          </td>
                        </tr>";
                    $no++;
                  }
                } else {
                  echo "<tr><td colspan='8' class='text-muted py-4'>Tidak ada data kontrak ditemukan</td></tr>";
                }
                ?>
              </tbody>
            </table>

            <!-- Pagination -->
            <nav class="mt-3">
              <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Sebelumnya</a>
                  </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                  </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                  <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Selanjutnya</a>
                  </li>
                <?php endif; ?>
              </ul>
            </nav>

            <div class="text-center mt-3">
              <a href="../index.php" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Dashboard
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../asset/js/script.js"></script>
</body>

</html>