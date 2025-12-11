<?php
include('../config/db.php');

/* ===== PAGINATION SETTING ===== */
$perPage = 100;
$page    = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset  = ($page - 1) * $perPage;

/* ===== SEARCH ===== */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = " WHERE 1=1 ";
if ($search !== '') {
  $esc = $conn->real_escape_string($search);
  $where .= " AND company_name LIKE '%$esc%'";
}

/* ===== HITUNG TOTAL DATA ===== */
$countSql = "SELECT COUNT(*) AS total FROM T_Company $where";
$totalRows = $conn->query($countSql)->fetch_assoc()['total'];
$totalPages = max(1, ceil($totalRows / $perPage));

/* ===== AMBIL DATA COMPANY ===== */
$sql = "
  SELECT *
  FROM T_Company
  $where
  ORDER BY company_create_date DESC, company_id DESC
  LIMIT $perPage OFFSET $offset
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Company List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../asset/css/style.css">
</head>

<body class="bg-light">
  <div class="d-flex" id="wrapper">
    <?php $base_url = '../';
    include('../partial/sidebar.php'); ?>

    <!-- Page Content -->
    <div id="page-content" class="flex-grow-1">
      <?php $page_title = 'Company';
      $page_subtitle = 'Master Data';
      include('../partial/navbar.php'); ?>

      <div class="container-fluid py-4 px-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
              <h5 class="mb-0 fw-bold">Company List</h5>
              <div class="d-flex gap-2">
                <form class="d-flex" method="get">
                  <input type="text" name="search" class="form-control form-control-sm me-2"
                    placeholder="Search company name" value="<?= htmlspecialchars($search) ?>">
                  <button class="btn btn-sm btn-primary" type="submit"><i class="fa fa-search"></i></button>
                </form>
                <a href="company-add.php" class="btn btn-sm btn-success">
                  <i class="fa fa-plus me-1"></i> Add Company
                </a>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light text-center">
                  <tr>
                    <th>No</th>
                    <th>Company ID</th>
                    <th>Company Name</th>
                    <th>Create Date</th>
                    <th>Modify Date</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if ($result && $result->num_rows > 0) {
                    $no = $offset + 1;
                    while ($row = $result->fetch_assoc()) {
                      echo "<tr>
                          <td class='text-center'>{$no}</td>
                          <td class='text-center'>{$row['company_id']}</td>
                          <td>{$row['company_name']}</td>
                          <td class='text-center'>{$row['company_create_date']}</td>
                          <td class='text-center'>{$row['company_modify_date']}</td>
                          <td class='text-center'>
                          <a href='company-edit.php?id={$row['company_id']}' class='btn btn-sm btn-warning me-1'>
                          <i class='fa fa-pen'></i>
                          </a>
                          <a href='company-delete.php?id={$row['company_id']}'
                            class='btn btn-sm btn-danger'
                            onclick=\"return confirm('Delete this company?');\">
                            <i class='fa fa-trash'></i>
                          </a>
                        </td>
                        </tr>";
                      $no++;
                    }
                  } else {
                    echo "<tr><td colspan='6' class='text-center text-muted py-4'>No data available</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <nav class="mt-3">
              <ul class="pagination pagination-sm justify-content-end mb-0">
                <?php
                $qsBase = $_GET;
                for ($p = 1; $p <= $totalPages; $p++) {
                  $qsBase['page'] = $p;
                  $link = '?' . http_build_query($qsBase);
                  $active = ($p == $page) ? 'active' : '';
                  echo "<li class='page-item $active'>
                        <a class='page-link' href='$link'>$p</a>
                      </li>";
                }
                ?>
              </ul>
            </nav>

          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../asset/js/script.js"></script>
</body>

</html>