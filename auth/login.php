<?php
// Halaman login di folder auth
require_once __DIR__ . '/../config/db.php';
// Jika sudah login, arahkan ke dashboard
if (!empty($_SESSION['admin_id'])) {
    header('Location: /contract/index.php');
    exit();
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="../asset/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../asset/css/style.css">
</head>

<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Login</h5>
                        <?php if (!empty($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <?php
                                $errorCode = $_GET['error'];
                                switch ($errorCode) {
                                    case 'empty':
                                        echo 'Email dan password tidak boleh kosong.';
                                        break;
                                    case 'not_found':
                                        echo 'Email tidak terdaftar dalam sistem.';
                                        break;
                                    case 'invalid_password':
                                        echo 'Password yang Anda masukkan salah.';
                                        break;
                                    case 'inactive':
                                        echo 'Akun Anda tidak aktif. Hubungi administrator.';
                                        break;
                                    case 'db_error':
                                        echo 'Terjadi kesalahan sistem. Silakan coba lagi.';
                                        break;
                                    default:
                                        echo 'Login gagal. Periksa email / password.';
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                        <form action="login_process.php" method="post">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-primary" type="submit">Masuk</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../asset/vendor/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>