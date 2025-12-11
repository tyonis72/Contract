<?php
include('../config/db.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch admin data
$result = $conn->query("SELECT * FROM T_Admin WHERE admin_id = $id");
if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$admin = $result->fetch_assoc();

// Fetch roles from T_Role table
$roles_result = $conn->query("SELECT role_id, role_title FROM T_Role ORDER BY role_title");
$roles = [];
while ($role = $roles_result->fetch_assoc()) {
    $roles[] = $role;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['admin_email'] ?? '');
    $password = $_POST['admin_password'] ?? '';
    $name = trim($_POST['admin_name'] ?? '');
    $role = trim($_POST['admin_role'] ?? '');
    $active = $_POST['admin_active'] ?? 'active';

    // Validation
    if (empty($email) || empty($name)) {
        $error = 'Email dan nama tidak boleh kosong.';
    } else {
        // Check if email already exists (exclude current admin)
        $check = $conn->query("SELECT admin_id FROM T_Admin WHERE admin_email = '{$conn->real_escape_string($email)}' AND admin_id != $id");
        if ($check->num_rows > 0) {
            $error = 'Email sudah digunakan oleh admin lain.';
        } else {
            // Build update query
            if (!empty($password)) {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE T_Admin SET admin_email = ?, admin_password = ?, admin_name = ?, admin_role = ?, admin_active = ?, admin_modify_date = NOW() 
                               WHERE admin_id = ?");
                if ($stmt) {
                    $stmt->bind_param('sssssi', $email, $hashed_password, $name, $role, $active, $id);
                }
            } else {
                // Don't update password
                $stmt = $conn->prepare("UPDATE T_Admin SET admin_email = ?, admin_name = ?, admin_role = ?, admin_active = ?, admin_modify_date = NOW() 
                               WHERE admin_id = ?");
                if ($stmt) {
                    $stmt->bind_param('ssssi', $email, $name, $role, $active, $id);
                }
            }

            if ($stmt && $stmt->execute()) {
                header('Location: index.php?msg=updated');
                exit();
            } else {
                $error = 'Gagal mengubah admin.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Admin</title>
    <link href="../asset/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/style.css">
</head>

<body>
    <div class="d-flex" id="wrapper">
        <?php $base_url = '../';
        include('../partial/sidebar.php'); ?>

        <!-- CONTENT -->
        <div class="flex-grow-1">
            <?php $page_title = 'Edit Admin';
            include('../partial/navbar.php'); ?>

            <!-- MAIN CONTENT -->
            <div class="container-fluid p-4">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Form Edit Admin</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <?php echo $error; ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Admin ID</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin['admin_id']); ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="admin_email" class="form-control" value="<?php echo htmlspecialchars($admin['admin_email']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="admin_password" class="form-control">
                                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                                        <input type="text" name="admin_name" class="form-control" value="<?php echo htmlspecialchars($admin['admin_name']); ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select name="admin_role" class="form-control">
                                            <option value="">-- Pilih Role --</option>
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?php echo htmlspecialchars($role['role_id']); ?>"
                                                    <?php echo $admin['admin_role'] === $role['role_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($role['role_title']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Status Aktif</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="admin_active" id="active_yes" value="active"
                                                <?php echo strtolower($admin['admin_active']) === 'active' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="active_yes">Aktif</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="admin_active" id="active_no" value="inactive"
                                                <?php echo strtolower($admin['admin_active']) !== 'active' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="active_no">Tidak Aktif</label>
                                        </div>
                                    </div>

                                    <div class="mb-3 p-3 bg-light rounded">
                                        <small class="text-muted">
                                            <strong>Dibuat:</strong> <?php echo date('d-m-Y H:i', strtotime($admin['admin_create_date'])); ?><br>
                                            <strong>Diubah:</strong> <?php echo date('d-m-Y H:i', strtotime($admin['admin_modify_date'])); ?>
                                        </small>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-save me-2"></i>Simpan</button>
                                        <a href="index.php" class="btn btn-secondary"><i class="fa fa-arrow-left me-2"></i>Kembali</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../asset/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../asset/js/script.js"></script>
</body>

</html>