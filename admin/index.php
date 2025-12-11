<?php
include('../config/db.php');

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $per_page;

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_query = '';
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $search_query = " WHERE admin_email LIKE '%$search%' OR admin_name LIKE '%$search%'";
}

// Get total records
$total_result = $conn->query("SELECT COUNT(*) AS total FROM T_Admin $search_query");
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $per_page);

// Fetch admin data
$query = "SELECT admin_id, admin_email, admin_name, admin_role, admin_active, admin_pic, admin_create_date, admin_modify_date 
          FROM T_Admin $search_query ORDER BY admin_create_date DESC LIMIT $offset, $per_page";
$result = $conn->query($query);
$admins = [];
while ($row = $result->fetch_assoc()) {
    $admins[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Management</title>
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
            <?php $page_title = 'Admin Management';
            include('../partial/navbar.php'); ?>

            <!-- MAIN CONTENT -->
            <div class="container-fluid p-4">
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h2>Daftar Admin</h2>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="admin-add.php" class="btn btn-primary"><i class="fa fa-plus me-2"></i>Tambah Admin</a>
                    </div>
                </div>

                <!-- Search Box -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" placeholder="Cari email atau nama..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-secondary">Cari</button>
                            <?php if (!empty($search)): ?>
                                <a href="index.php" class="btn btn-outline-secondary ms-2">Reset</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if (!empty($_GET['msg'])): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?php
                        $msg = htmlspecialchars($_GET['msg']);
                        if ($_GET['msg'] === 'added') echo 'Admin berhasil ditambahkan.';
                        elseif ($_GET['msg'] === 'updated') echo 'Admin berhasil diubah.';
                        elseif ($_GET['msg'] === 'deleted') echo 'Admin berhasil dihapus.';
                        else echo $msg;
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Email</th>
                                <th>Nama</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Profil Pic</th>
                                <th>Dibuat</th>
                                <th>Diubah</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($admins)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Tidak ada data admin.</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = $offset + 1; ?>
                                <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($admin['admin_email']); ?></td>
                                        <td><?php echo htmlspecialchars($admin['admin_name']); ?></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($admin['admin_role']); ?></span>
                                        </td>
                                        <td>
                                            <?php if (strtolower($admin['admin_active']) === 'active'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Tidak Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($admin['admin_pic'])): ?>
                                                <img src="../asset/admin_pic/<?php echo htmlspecialchars($admin['admin_pic']); ?>" alt="Pic" width="40" height="40" class="rounded-circle" style="object-fit: cover;">
                                            <?php else: ?>
                                                <i class="fas fa-user-circle fa-2x text-secondary"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d-m-Y H:i', strtotime($admin['admin_create_date'])); ?></td>
                                        <td><?php echo date('d-m-Y H:i', strtotime($admin['admin_modify_date'])); ?></td>
                                        <td class="text-center">
                                            <a href="admin-edit.php?id=<?php echo $admin['admin_id']; ?>" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>
                                            <a href="admin-delete.php?id=<?php echo $admin['admin_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?');"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=1<?php if (!empty($search)) echo '&search=' . urlencode($search); ?>">First</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=<?php echo $page - 1; ?><?php if (!empty($search)) echo '&search=' . urlencode($search); ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=<?php echo $i; ?><?php if (!empty($search)) echo '&search=' . urlencode($search); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=<?php echo $page + 1; ?><?php if (!empty($search)) echo '&search=' . urlencode($search); ?>">Next</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=<?php echo $total_pages; ?><?php if (!empty($search)) echo '&search=' . urlencode($search); ?>">Last</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

                <!-- Summary -->
                <div class="text-muted text-center mt-4">
                    <small>Total: <?php echo $total_records; ?> admin | Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></small>
                </div>
            </div>
        </div>
    </div>

    <script src="../asset/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../asset/js/script.js"></script>
</body>

</html>