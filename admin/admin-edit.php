<?php
include('../config/db.php');

/* ==============================
   VALIDASI ID
   ============================== */
if (!isset($_GET['id']) || $_GET['id'] === '') {
    header('Location: index.php');
    exit();
}

$id = $_GET['id']; // STRING

/* ==============================
   AMBIL DATA ADMIN
   ============================== */
$stmt = $conn->prepare("SELECT * FROM T_Admin WHERE admin_id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$admin = $result->fetch_assoc();

/* ==============================
   FETCH ROLES
   ============================== */
$roles_result = $conn->query("SELECT role_id, role_title FROM T_Role ORDER BY role_title");
$roles = [];
while ($row = $roles_result->fetch_assoc()) {
    $roles[] = $row;
}

/* ==============================
   SUBMIT FORM
   ============================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email  = trim($_POST['admin_email'] ?? '');
    $name   = trim($_POST['admin_name'] ?? '');
    $role   = trim($_POST['admin_role'] ?? '');
    $active = $_POST['admin_active'] ?? 'active';
    $password = $_POST['admin_password'] ?? '';

    if ($email === '' || $name === '') {
        $error = 'Email dan nama tidak boleh kosong.';
    } else {

        /* CEK EMAIL DUPLIKAT */
        $check = $conn->prepare("
            SELECT admin_id 
            FROM T_Admin 
            WHERE admin_email = ? AND admin_id != ?
        ");
        $check->bind_param("ss", $email, $id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'Email sudah digunakan oleh admin lain.';
        } else {

            /* NEW: HANDLE UPLOAD FOTO PROFIL (support base64 from cropper) */
            $new_pic = '';
            $old_pic = $admin['admin_pic'];

            if (!empty($_POST['admin_pic_data'])) {
                $data = $_POST['admin_pic_data'];
                if (preg_match('/^data:(image\/(png|jpeg));base64,/', $data, $m)) {
                    $mime = $m[1];
                    $ext = $m[2] === 'png' ? '.png' : '.jpg';
                    $base64 = substr($data, strpos($data, ',') + 1);
                    $decoded = base64_decode($base64);
                    if ($decoded === false) {
                        $error = 'Gagal mendecode gambar.';
                    } elseif (strlen($decoded) > 2 * 1024 * 1024) {
                        $error = 'Ukuran file maksimal 2MB.';
                    } else {
                        $targetDir = __DIR__ . '/../asset/admin_pic/';
                        if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                        $filename = $id . '_' . time() . $ext;

                        /* Ensure filename fits DB column */
                        $maxLen = null;
                        $colRes = $conn->query("SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'T_Admin' AND COLUMN_NAME = 'admin_pic'");
                        if ($colRes) {
                            $colRow = $colRes->fetch_assoc();
                            $maxLen = isset($colRow['CHARACTER_MAXIMUM_LENGTH']) ? (int)$colRow['CHARACTER_MAXIMUM_LENGTH'] : null;
                        }
                        if (is_int($maxLen) && $maxLen > 0 && strlen($filename) > $maxLen) {
                            $allowedBaseLen = $maxLen - (strlen(ltrim($ext, '.')) + 1);
                            if ($allowedBaseLen > 0) {
                                $baseName = substr($id . '_' . time(), 0, $allowedBaseLen);
                                $filename = $baseName . '.' . ltrim($ext, '.');
                            } else {
                                $hash = substr(sha1($filename), 0, max(1, $maxLen - 1));
                                $filename = $hash;
                            }
                        }

                        if (file_put_contents($targetDir . $filename, $decoded) !== false) {
                            $new_pic = $filename;
                        } else {
                            $error = 'Gagal menyimpan gambar.';
                        }
                    }
                } else {
                    $error = 'Format file harus JPG atau PNG.';
                }
            } elseif (isset($_FILES['admin_pic']) && (($_FILES['admin_pic']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE)) {
                $file = $_FILES['admin_pic'];
                if ($file['error'] === UPLOAD_ERR_OK) {
                    if ($file['size'] <= 2 * 1024 * 1024) {
                        $checkImg = getimagesize($file['tmp_name']);
                        if ($checkImg !== false && in_array($checkImg['mime'], ['image/jpeg', 'image/png'])) {
                            $ext = $checkImg['mime'] === 'image/png' ? '.png' : '.jpg';
                            $targetDir = __DIR__ . '/../asset/admin_pic/';
                            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
                            $filename = $id . '_' . time() . $ext;
                            if (move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
                                $new_pic = $filename;
                            } else {
                                $error = 'Gagal mengunggah foto.';
                            }
                        } else {
                            $error = 'Format file harus JPG atau PNG.';
                        }
                    } else {
                        $error = 'Ukuran file maksimal 2MB.';
                    }
                } else {
                    $error = 'Terjadi kesalahan saat mengunggah file.';
                }
            }

            if (empty($error)) {
                $setParts = [];
                $types = '';
                $params = [];

                $setParts[] = 'admin_email = ?';
                $types .= 's';
                $params[] = &$email;

                if ($password !== '') {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $setParts[] = 'admin_password = ?';
                    $types .= 's';
                    $params[] = &$hashed;
                }

                $setParts[] = 'admin_name = ?';
                $types .= 's';
                $params[] = &$name;

                $setParts[] = 'admin_role = ?';
                $types .= 's';
                $params[] = &$role;

                $setParts[] = 'admin_active = ?';
                $types .= 's';
                $params[] = &$active;

                if ($new_pic !== '') {
                    $setParts[] = 'admin_pic = ?';
                    $types .= 's';
                    $params[] = &$new_pic;
                }

                $setParts[] = 'admin_modify_date = NOW()';

                $sql = "UPDATE T_Admin SET " . implode(', ', $setParts) . " WHERE admin_id = ?";
                $types .= 's';
                $params[] = &$id;

                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $bindArr = [];
                    $bindArr[] = $types;
                    foreach ($params as $k => $v) $bindArr[] = &$params[$k];
                    call_user_func_array([$stmt, 'bind_param'], $bindArr);

                    if ($stmt->execute()) {
                        if ($new_pic !== '' && !empty($old_pic) && $old_pic !== $new_pic) {
                            $oldPath = __DIR__ . '/../asset/admin_pic/' . $old_pic;
                            if (file_exists($oldPath)) @unlink($oldPath);
                        }

                        if (isset($_SESSION['admin_id']) && $_SESSION['admin_id'] === $id && $new_pic !== '') {
                            $_SESSION['admin_pic'] = $new_pic;
                        }

                        header('Location: index.php?msg=updated');
                        exit();
                    } else {
                        $error = 'Gagal mengubah admin.';
                    }
                } else {
                    $error = 'Prepare statement gagal: ' . $conn->error;
                }
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
    <link href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet">
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

                                <form method="POST" enctype="multipart/form-data">
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

                                    <div class="mb-3">
                                        <label class="form-label">Foto Profil</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <div>
                                                <?php if (!empty($admin['admin_pic']) && file_exists(__DIR__ . '/../asset/admin_pic/' . $admin['admin_pic'])): ?>
                                                    <img id="preview-thumb" src="<?php echo $base_url . 'asset/admin_pic/' . htmlspecialchars($admin['admin_pic']); ?>" alt="Preview" width="60" height="60" class="rounded-circle" style="object-fit: cover;">
                                                <?php else: ?>
                                                    <img id="preview-thumb" src="" alt="Preview" width="60" height="60" class="rounded-circle" style="object-fit: cover; display:none;">
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <input type="file" id="admin_pic_input" name="admin_pic" accept=".jpg,.jpeg,.png" class="form-control">
                                                <input type="hidden" name="admin_pic_data" id="admin_pic_data">
                                                <small class="text-muted">Format: JPG/PNG. Maks 2MB. Setelah memilih file Anda bisa crop sebelum menyimpan.</small>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-outline-secondary" id="openCropperBtn" style="display:none;">Crop</button>
                                            </div>
                                        </div>

                                        <!-- Crop modal -->
                                        <div class="modal fade" id="cropperModal" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Crop Foto Profil</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <div style="max-height:60vh;">
                                                            <img id="cropperImage" src="" style="max-width:100%;">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="button" class="btn btn-primary" id="cropAndUse">Gunakan Foto</button>
                                                    </div>
                                                </div>
                                            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
    <script>
        (function() {
            let cropper, modalEl, bsModal;
            document.addEventListener('DOMContentLoaded', function() {
                const input = document.getElementById('admin_pic_input');
                const preview = document.getElementById('preview-thumb');
                const openBtn = document.getElementById('openCropperBtn');
                const img = document.getElementById('cropperImage');
                const hidden = document.getElementById('admin_pic_data');
                modalEl = document.getElementById('cropperModal');
                bsModal = new bootstrap.Modal(modalEl, {});

                input.addEventListener('change', function(e) {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;
                    if (!/image\/(jpeg|png)/.test(file.type)) {
                        alert('Format file harus JPG/PNG.');
                        input.value = '';
                        return;
                    }
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Ukuran maksimal 2MB.');
                        input.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        img.src = ev.target.result;
                        preview.src = ev.target.result;
                        preview.style.display = 'inline-block';
                        openBtn.style.display = 'inline-block';
                    };
                    reader.readAsDataURL(file);
                });

                openBtn.addEventListener('click', function() {
                    bsModal.show();
                    modalEl.addEventListener('shown.bs.modal', function handler() {
                        cropper = new Cropper(img, {
                            aspectRatio: 1,
                            viewMode: 1,
                            background: false,
                            movable: false,
                            zoomable: true
                        });
                        modalEl.removeEventListener('shown.bs.modal', handler);
                    });
                    modalEl.addEventListener('hidden.bs.modal', function() {
                        if (cropper) {
                            cropper.destroy();
                            cropper = null;
                        }
                    });
                });

                document.getElementById('cropAndUse').addEventListener('click', function() {
                    if (!cropper) return;
                    cropper.getCroppedCanvas({
                        width: 400,
                        height: 400,
                        imageSmoothingQuality: 'high'
                    }).toBlob(function(blob) {
                        const reader = new FileReader();
                        reader.onloadend = function() {
                            hidden.value = reader.result; // data URL
                            preview.src = reader.result;
                            bsModal.hide();
                        };
                        reader.readAsDataURL(blob);
                    }, 'image/jpeg', 0.9);
                });

            });
        })();
    </script>
</body>

</html>