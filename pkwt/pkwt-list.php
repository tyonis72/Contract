<?php
include('../config/db.php');

/* ===== PAGINATION ===== */
$perPage = 10;
$page    = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset  = ($page - 1) * $perPage;

/* ===== SEARCH ===== */
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = " WHERE 1 ";
if ($search !== '') {
  $esc = $conn->real_escape_string($search);
  $where .= " AND (pkwt_no LIKE '%$esc%' OR pkwt_name LIKE '%$esc%')";
}

/* ===== COUNT ===== */
$countSql = $conn->query("SELECT COUNT(*) as total FROM T_Pkwt $where");
$totalRows = $countSql->fetch_assoc()['total'];
$totalPages = max(1, ceil($totalRows / $perPage));

/* ===== DATA PKWT ===== */
$sql = "
SELECT 
  p.*,
  r.region_title,
  c.city_title,
  co.company_name,
  s.subcompany_name
FROM T_Pkwt p
LEFT JOIN T_Region r ON r.region_id = p.pkwt_ref_region
LEFT JOIN T_City c ON c.city_id = p.pkwt_ref_city
LEFT JOIN T_Company co ON co.company_id = p.pkwt_ref_company
LEFT JOIN T_Subcompany s ON s.subcompany_id = p.pkwt_ref_subcompany
$where
ORDER BY p.pkwt_id DESC
LIMIT $perPage OFFSET $offset
";
$result = $conn->query($sql);

/* ===== AUTO CLOSE ===== */
$conn->query("
    UPDATE T_Pkwt SET pkwt_status = 'close'
    WHERE pkwt_status='active'
    AND pkwt_end_date < CURDATE()
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>PKWT List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../asset/css/style.css">
</head>

<body class="bg-light">

  <div class="d-flex" id="wrapper">
    <?php $base_url = '../';
    include('../partial/sidebar.php'); ?>

    <!-- CONTENT -->
    <div id="page-content" class="flex-grow-1">
      <?php $page_title = 'PKWT';
      $page_subtitle = 'List';
      include('../partial/navbar.php'); ?>

      <div class="container-fluid py-4 px-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">

            <!-- HEADER + SEARCH -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3 gap-2">
              <h5 class="mb-0 fw-bold">PKWT List</h5>

              <div class="d-flex gap-2">
                <form class="d-flex" method="get">
                  <input type="text" name="search" class="form-control form-control-sm me-2"
                    placeholder="Search PKWT no / name"
                    value="<?= htmlspecialchars($search) ?>">
                  <button class="btn btn-sm btn-primary" type="submit">
                    <i class="fa fa-search"></i>
                  </button>
                </form>

                <a href="pkwt-add.php" class="btn btn-sm btn-success">
                  <i class="fa fa-plus me-1"></i> Add PKWT
                </a>
              </div>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light text-center">
                  <tr>
                    <th>No</th>
                    <th>PKWT No</th>
                    <th>Name</th>
                    <th>Region</th>
                    <th>City</th>
                    <th>Company</th>
                    <th>Subcompany</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th width="120">Action</th>
                  </tr>
                </thead>
                <tbody>

                  <?php
                  $no = $offset + 1;
                  if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                      echo "
                  <tr>
                    <td class='text-center'>$no</td>
                    <td>{$row['pkwt_no']}</td>
                    <td>{$row['pkwt_name']}</td>
                    <td>{$row['region_title']}</td>
                    <td>{$row['city_title']}</td>
                    <td>{$row['company_name']}</td>
                    <td>{$row['subcompany_name']}</td>
                    <td>{$row['pkwt_start_date']}</td>
                    <td>{$row['pkwt_end_date']}</td>
                    <td class='text-center'>
                      " . ($row['pkwt_status'] == 'active' ?
                        "<span class='badge bg-success'>Active</span>" :
                        "<span class='badge bg-danger'>Close</span>") . "
                    </td>
                    <td class='text-center'>
                      <a href='pkwt-edit.php?id={$row['pkwt_id']}' class='btn btn-sm btn-warning me-1'>
                        <i class='fa fa-pen'></i>
                      </a>
                      <a href='pkwt-delete.php?id={$row['pkwt_id']}'
                         onclick=\"return confirm('Delete this PKWT?');\"
                         class='btn btn-sm btn-danger'>
                        <i class='fa fa-trash'></i>
                      </a>
                    </td>
                  </tr>";
                      $no++;
                    }
                  } else {
                    echo "<tr><td colspan='11' class='text-center text-muted py-4'>No data available</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="pkwt-list.php?page=1<?php if (!empty($search)) echo '&search=' . urlencode($search); ?>">First</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="pkwt-list.php?page=<?php echo $page - 1; ?><?php if (!empty($search)) echo '&search=' . urlencode($search); ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $page - 1);
                            $end = min($totalPages, $page + 2);
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="pkwt-list.php?page=<?php echo $i; ?><?php if (!empty($search)) echo '&search=' . urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="pkwt-list.php?page=<?php echo $page + 1; ?><?php if (!empty($search)) echo '&search=' . urlencode($search); ?>">Next</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="pkwt-list.php?page=<?php echo $totalPages; ?><?php if (!empty($search)) echo '&search=' . urlencode($search); ?>">Last</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="../asset/vendor/bootstrap/js/bootstrap.min.js"></script>
  <script src="../asset/js/script.js"></script>
</body>

</html>