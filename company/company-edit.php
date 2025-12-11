<?php
include('../config/db.php');

// ---------- HANDLE UPDATE (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $company_id   = (int)($_POST['company_id'] ?? 0);
  $company_name = trim($_POST['company_name'] ?? '');

  if ($company_id <= 0) {
    echo "<script>
      alert('Invalid company ID.');
      window.location.href = 'company-list.php';
    </script>";
    exit;
  }

  if ($company_name === '') {
    echo "<script>
      alert('Company name is required.');
      window.history.back();
    </script>";
    exit;
  }

  // Cek duplikat nama (kecuali dirinya sendiri)
  $checkSql  = "SELECT COUNT(*) AS jml FROM T_Company WHERE company_name = ? AND company_id <> ?";
  $checkStmt = $conn->prepare($checkSql);
  $checkStmt->bind_param("si", $company_name, $company_id);
  $checkStmt->execute();
  $checkRow = $checkStmt->get_result()->fetch_assoc();

  if (($checkRow['jml'] ?? 0) > 0) {
    echo "<script>
      alert('Company name already exists, please use another name.');
      window.history.back();
    </script>";
    exit;
  }

  // Update data
  $updSql = "
    UPDATE T_Company
    SET company_name = ?
    WHERE company_id = ?
  ";
  $updStmt = $conn->prepare($updSql);
  $updStmt->bind_param("si", $company_name, $company_id);

  if ($updStmt->execute()) {
    echo "<script>
      alert('Company successfully updated.');
      window.location.href = 'company-list.php';
    </script>";
  } else {
    echo "<script>
      alert('Failed to update company: " . addslashes($updStmt->error) . "');
      window.history.back();
    </script>";
  }
  exit;
}

// ---------- TAMPILKAN FORM (GET) ----------
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  header("Location: company-list.php");
  exit;
}

$stmt = $conn->prepare("SELECT * FROM T_Company WHERE company_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$company = $stmt->get_result()->fetch_assoc();

if (!$company) {
  header("Location: company-list.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Edit Company</title>
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
      <?php $page_title = 'Company';
      $page_subtitle = 'Edit';
      include('../partial/navbar.php'); ?>

      <div class="container-fluid py-4 px-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h3 class="mb-3">Edit Company</h3>
            <form action="company-edit.php" method="post">
              <input type="hidden" name="company_id" value="<?= $company['company_id']; ?>">
              <div class="mb-3">
                <label class="form-label">Company Name</label>
                <input
                  type="text"
                  name="company_name"
                  class="form-control"
                  value="<?= htmlspecialchars($company['company_name']); ?>"
                  required>
              </div>
              <div class="d-flex justify-content-between">
                <a href="company-list.php" class="btn btn-outline-secondary">
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