<?php
include('../config/db.php');

// Ambil data untuk dropdown
$companies = $conn->query("SELECT company_id, company_name FROM T_Company ORDER BY company_name ASC");
$regions   = $conn->query("SELECT region_id, region_title FROM T_Region ORDER BY region_title ASC");
$cities    = $conn->query("SELECT city_id, city_title FROM T_City ORDER BY city_title ASC");

// Generate AUTO JOB ID (WLTKxxxxx)
$getLast = $conn->query("SELECT wltk_id FROM T_Wltk ORDER BY wltk_create_date DESC LIMIT 1");

if ($getLast->num_rows > 0) {
  $lastId = $getLast->fetch_assoc()['wltk_id'];
  $number = intval(substr($lastId, 4)) + 1;
} else {
  $number = 1;
}

$jobID = "WLTK" . str_pad($number, 5, "0", STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Contract</title>

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
      <?php $page_title = 'Contract';
      $page_subtitle = 'Add';
      include('../partial/navbar.php'); ?>

      <div class="container py-4">

        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h4 class="fw-bold mb-3">Create New Contract</h4>

            <form action="insert-contract.php" method="POST">

              <div class="row g-3">

                <div class="col-md-4">
                  <label class="form-label fw-semibold">Job ID</label>
                  <input type="text" name="wltk_id" class="form-control" value="<?= $jobID ?>" readonly>
                </div>

                <div class="col-md-4">
                  <label class="form-label fw-semibold">PO Number</label>
                  <input type="text" name="wltk_no" class="form-control" required>
                </div>

                <div class="col-md-4">
                  <label class="form-label fw-semibold">Client (Company)</label>
                  <select name="wltk_ref_company" class="form-select" required>
                    <option value="">-- Select Company --</option>
                    <?php while ($c = $companies->fetch_assoc()): ?>
                      <option value="<?= $c['company_id'] ?>"><?= $c['company_name'] ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label fw-semibold">Region</label>
                  <select name="wltk_ref_region" class="form-select" required>
                    <option value="">-- Select Region --</option>
                    <?php while ($r = $regions->fetch_assoc()): ?>
                      <option value="<?= $r['region_id'] ?>"><?= $r['region_title'] ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label fw-semibold">City</label>
                  <select name="wltk_ref_city" class="form-select" required>
                    <option value="">-- Select City --</option>
                    <?php while ($ct = $cities->fetch_assoc()): ?>
                      <option value="<?= $ct['city_id'] ?>"><?= $ct['city_title'] ?></option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label fw-semibold">Status</label>
                  <select name="wltk_status" class="form-select" required>
                    <option value="active">Active</option>
                    <option value="close">Close</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-semibold">Start Date</label>
                  <input type="date" name="wltk_start_date" class="form-control" required>
                </div>

                <div class="col-md-6">
                  <label class="form-label fw-semibold">End Date</label>
                  <input type="date" name="wltk_end_date" class="form-control" required>
                </div>

                <div class="col-12">
                  <label class="form-label fw-semibold">Address</label>
                  <textarea name="wltk_address" class="form-control" rows="2"></textarea>
                </div>

                <div class="col-12">
                  <label class="form-label fw-semibold">Notes</label>
                  <textarea name="wltk_notes" class="form-control" rows="2"></textarea>
                </div>

              </div>

              <div class="text-end mt-3">
                <a href="contract-list.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Contract</button>
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