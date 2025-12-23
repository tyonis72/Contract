<?php
include('../config/db.php');

/* ==============================
   GENERATE ADMIN ID (STRING)
   ============================== */
function generateAdminId()
{
    return 'ADM' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
}

/* ==============================
   FETCH ROLES
   ============================== */
$roles_result = $conn->query("SELECT role_id, role_title FROM T_Role ORDER BY role_title");
$roles = [];
while ($row = $roles_result->fetch_assoc()) {
    $roles[] = $row;
}

/* ==============================
   FORM SUBMIT
   ============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['admin_email'] ?? '');
    $password = $_POST['admin_password'] ?? '';
    $name     = trim($_POST['admin_name'] ?? '');
    $role     = trim($_POST['admin_role'] ?? '');
    $active   = $_POST['admin_active'] ?? 'active';

    if ($email === '' || $password === '' || $name === '') {
        $error = 'Email, password, dan nama tidak boleh kosong.';
    } else {

        /* CEK EMAIL */
        $check = $conn->prepare("SELECT admin_id FROM T_Admin WHERE admin_email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'Email sudah terdaftar.';
        } else {

            /* HASH PASSWORD */
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            /* GENERATE ADMIN ID UNIK */
            do {
                $admin_id = generateAdminId();
                $cek_id = $conn->prepare("SELECT admin_id FROM T_Admin WHERE admin_id = ?");
                $cek_id->bind_param("s", $admin_id);
                $cek_id->execute();
                $cek_id->store_result();
            } while ($cek_id->num_rows > 0);

            /* INSERT DATA */
            $stmt = $conn->prepare("
                INSERT INTO T_Admin
                (admin_id, admin_email, admin_password, admin_name, admin_role, admin_active, admin_create_date)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            if ($stmt) {
                $stmt->bind_param(
                    "ssssss",
                    $admin_id,
                    $email,
                    $hashed_password,
                    $name,
                    $role,
                    $active
                );

                if ($stmt->execute()) {
                    header('Location: index.php?msg=added');
                    exit();
                } else {
                    $error = 'Gagal menambah admin: ' . $stmt->error;
                }
            } else {
                $error = 'Prepare statement gagal: ' . $conn->error;
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
    <title>Tambah Admin</title>
    <link href="../asset/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/style.css">
</head>
<body>
<div class="d-flex" id="wrapper">

<?php $base_url = '../'; include('../partial/sidebar.php'); ?>

<div class="flex-grow-1">
<?php $page_title = 'Tambah Admin'; include('../partial/navbar.php'); ?>

<div class="container-fluid p-4">
<div class="row">
<div class="col-md-8 offset-md-2">
<div class="card">
<div class="card-header">
<h5 class="mb-0">Form Tambah Admin</h5>
</div>

<div class="card-body">

<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show">
<?= htmlspecialchars($error) ?>
<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="POST">

<div class="mb-3">
<label class="form-label">Email *</label>
<input type="email" name="admin_email" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Password *</label>
<input type="password" name="admin_password" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Nama *</label>
<input type="text" name="admin_name" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Role</label>
<select name="admin_role" class="form-control">
<option value="">-- Pilih Role --</option>
<?php foreach ($roles as $r): ?>
<option value="<?= htmlspecialchars($r['role_id']) ?>">
<?= htmlspecialchars($r['role_title']) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="mb-3">
<label class="form-label">Status</label>
<div class="form-check">
<input class="form-check-input" type="radio" name="admin_active" value="active" checked>
<label class="form-check-label">Aktif</label>
</div>
<div class="form-check">
<input class="form-check-input" type="radio" name="admin_active" value="inactive">
<label class="form-check-label">Tidak Aktif</label>
</div>
</div>

<button type="submit" class="btn btn-primary">
<i class="fa fa-save me-2"></i>Simpan
</button>
<a href="index.php" class="btn btn-secondary">
<i class="fa fa-arrow-left me-2"></i>Kembali
</a>

</form>
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
