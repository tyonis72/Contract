<?php
include('../config/db.php');

// Hitung next value untuk subcompany_ref_company_id (MAX + 1)
$row = $conn->query("SELECT COALESCE(MAX(subcompany_ref_company_id),0) + 1 AS next_ref FROM T_Subcompany")->fetch_assoc();
$nextRef = $row['next_ref'] ?? 1;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Subcompany</title>
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
            <?php $page_title = 'Subcompany';
            $page_subtitle = 'Add';
            include('../partial/navbar.php'); ?>

            <div class="container-fluid py-4 px-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h3 class="mb-3">Add Subcompany</h3>

                        <form action="subcompany-insert.php" method="post">
                            <div class="mb-3">
                                <label class="form-label">Ref Company ID (auto)</label>
                                <input type="number" name="subcompany_ref_company_id" class="form-control" value="<?= htmlspecialchars($nextRef) ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subcompany Name</label>
                                <input
                                    type="text"
                                    name="subcompany_name"
                                    class="form-control"
                                    placeholder="Enter subcompany name"
                                    required>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="subcompany-list.php" class="btn btn-outline-secondary">
                                    <i class="fa fa-arrow-left me-1"></i> Back to List
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i> Save
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