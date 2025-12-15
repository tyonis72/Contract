<?php
include('../config/db.php');

///////////////////////
// SEARCH + PAGINATION
///////////////////////
$limit  = 100;
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// keyword search
$keyword   = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchSql = '';
if ($keyword !== '') {
  $safe = $conn->real_escape_string($keyword);
  $searchSql = "WHERE 
      C.city_title   LIKE '%{$safe}%' 
      OR R.region_title LIKE '%{$safe}%'";
}

// Hitung total data
$countRes = $conn->query("
  SELECT COUNT(*) AS total
  FROM T_City C
  LEFT JOIN T_Region R ON C.city_ref_region_id = R.region_id
  $searchSql
");
$total_rows  = $countRes->fetch_assoc()['total'] ?? 0;
$total_pages = $total_rows > 0 ? ceil($total_rows / $limit) : 1;

// Ambil data City + Region
$sql = "
  SELECT 
    C.city_id,
    C.city_title,
    R.region_title
  FROM T_City C
  LEFT JOIN T_Region R ON C.city_ref_region_id = R.region_id
  $searchSql
  ORDER BY C.city_title ASC
  LIMIT $limit OFFSET $offset
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>City List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../asset/css/style.css">
</head>

<body>

  <div class="d-flex" id="wrapper">
    <?php $base_url = '../';
    include('../partial/sidebar.php'); ?>

    <!-- MAIN CONTENT -->
    <div id="page-content" class="flex-grow-1 bg-light">
      <?php $page_title = 'City';
      $page_subtitle = 'Management';
      include('../partial/navbar.php'); ?>

      <!-- CONTENT -->
      <div class="container-fluid py-4 px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
          <h3 class="mb-0">City List</h3>
          <form class="d-flex" method="get" action="">
            <input
              type="text"
              name="search"
              class="form-control form-control-sm me-2"
              placeholder="Search city / region..."
              value="<?= htmlspecialchars($keyword); ?>">
            <button class="btn btn-sm btn-outline-secondary me-2" type="submit">
              <i class="fa fa-search"></i>
            </button>
            <?php if ($keyword !== ''): ?>
              <a href="city-list.php" class="btn btn-sm btn-outline-danger">
                <i class="fa fa-times"></i>
              </a>
            <?php endif; ?>
          </form>
          <a href="city-add.php" class="btn btn-primary">
            <i class="fa fa-plus me-1"></i> Add City
          </a>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light text-center">
                  <tr>
                    <th style="width: 60px;">No</th>
                    <th>City</th>
                    <th>Region</th>
                    <th style="width: 160px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  if ($result && $result->num_rows > 0) {
                    $no = $offset + 1;
                    while ($row = $result->fetch_assoc()) {
                  ?>
                      <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['city_title'] ?? ''); ?></td>
                        <td><?= htmlspecialchars($row['region_title'] ?? ''); ?></td>
                        <td class="text-center">
                          <a href="city-edit.php?id=<?= $row['city_id']; ?>" class="btn btn-sm btn-warning">
                            <i class="fa fa-pen"></i>
                          </a>
                          <a href="city-delete.php?id=<?= $row['city_id']; ?>"
                            class="btn btn-sm btn-danger"
                            onclick="return confirm('Yakin ingin menghapus data ini?');">
                            <i class="fa fa-trash"></i>
                          </a>
                        </td>
                      </tr>
                  <?php
                    }
                  } else {
                    echo "<tr><td colspan='4' class='text-center text-muted py-4'>No data available</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>

            <!-- PAGINATION RINGKAS -->
            <?php if ($total_pages > 1): ?>
              <nav aria-label="Page navigation" class="mt-3">
                <ul class="pagination justify-content-center mb-0">
                  <?php
                  // helper untuk bawa query search
                  $q = $keyword !== '' ? '&search=' . urlencode($keyword) : '';
                  if ($page > 1): ?>
                    <li class="page-item">
                      <a class="page-link" href="?page=1<?= $q; ?>">First</a>
                    </li>
                    <li class="page-item">
                      <a class="page-link" href="?page=<?= $page - 1; ?><?= $q; ?>">Prev</a>
                    </li>
                  <?php endif; ?>

                  <?php
                  $start = max(1, $page - 2);
                  $end   = min($total_pages, $page + 2);
                  for ($i = $start; $i <= $end; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : ''; ?>">
                      <a class="page-link" href="?page=<?= $i; ?><?= $q; ?>"><?= $i; ?></a>
                    </li>
                  <?php endfor; ?>

                  <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                      <a class="page-link" href="?page=<?= $page + 1; ?><?= $q; ?>">Next</a>
                    </li>
                    <li class="page-item">
                      <a class="page-link" href="?page=<?= $total_pages; ?><?= $q; ?>">Last</a>
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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../asset/js/script.js"></script>
</body>

</html>