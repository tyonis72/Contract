<?php
include('../config/db.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: subcompany-list.php');
    exit;
}

$sql = "SELECT * FROM T_Subcompany WHERE subcompany_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
if (!$data) {
    header('Location: subcompany-list.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Subcompany</title>
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
            $page_subtitle = 'Edit';
            include('../partial/navbar.php'); ?>

            <div class="container-fluid py-4 px-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h3 class="mb-3">Edit Subcompany</h3>

                        <form action="subcompany-update.php" method="post">
                            <input type="hidden" name="subcompany_id" value="<?= $data['subcompany_id'] ?>">

                            <div class="mb-3">
                                <label class="form-label">Ref Company ID</label>
                                <input type="number" name="subcompany_ref_company_id" class="form-control" value="<?= htmlspecialchars($data['subcompany_ref_company_id']) ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Subcompany Name</label>
                                <input
                                    type="text"
                                    name="subcompany_name"
                                    class="form-control"
                                    value="<?= htmlspecialchars($data['subcompany_name']) ?>"
                                    required>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="subcompany-list.php" class="btn btn-outline-secondary">
                                    <i class="fa fa-arrow-left me-1"></i> Back to List
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i> Save Changes
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