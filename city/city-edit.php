<?php
include('../config/db.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  header("Location: city-list.php");
  exit;
}

// Ambil data city
$cityStmt = $conn->prepare("SELECT * FROM T_City WHERE city_id = ?");
$cityStmt->bind_param("i", $id);
$cityStmt->execute();
$city = $cityStmt->get_result()->fetch_assoc();
if (!$city) {
  header("Location: city-list.php");
  exit;
}

// Ambil region untuk dropdown
$regionRes = $conn->query("SELECT region_id, region_title FROM T_Region ORDER BY region_title ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit City</title>
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
      $page_subtitle = 'Edit';
      include('../partial/navbar.php'); ?>

      <div class="container-fluid py-4 px-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h3 class="mb-3">Edit City</h3>
            <form action="city-update.php" method="post">
              <input type="hidden" name="city_id" value="<?= $city['city_id']; ?>">
              <div class="mb-3">
                <label class="form-label">City Name</label>
                <input
                  type="text"
                  name="city_title"
                  class="form-control"
                  value="<?= htmlspecialchars($city['city_title']); ?>"
                  required>
              </div>
              <div class="mb-3">
                <label class="form-label">Region</label>
                <select name="city_ref_region_id" class="form-select" required>
                  <option value="">-- Select Region --</option>
                  <?php while ($r = $regionRes->fetch_assoc()): ?>
                    <option
                      value="<?= $r['region_id']; ?>"
                      <?= $r['region_id'] == $city['city_ref_region_id'] ? 'selected' : ''; ?>>
                      <?= htmlspecialchars($r['region_title']); ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>
              <div class="d-flex justify-content-between">
                <a href="city-list.php" class="btn btn-outline-secondary">
                  <i class="fa fa-arrow-left me-1"></i> Back to List
                </a>
                <button type="submit" class="btn btn-primary">
                  <i class="fa fa-save me-1"></i> Update
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../asset/js/script.js"></script>
</body>

</html>