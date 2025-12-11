<?php
include('../config/db.php');

// ====== FUNCTION ALERT + REDIRECT ======
function jsAlertBack($msg, $target = 'region-list.php')
{
  echo "<script>alert('" . addslashes($msg) . "');window.location.href='{$target}';</script>";
  exit;
}

// ============= INSERT (ADD) =============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
  $title = trim($_POST['region_title'] ?? '');

  if ($title === '') {
    jsAlertBack('Region title tidak boleh kosong.');
  }

  // Cek duplikat nama
  global $conn;
  $check = $conn->prepare("SELECT COUNT(*) AS jml FROM T_Region WHERE region_title = ?");
  $check->bind_param("s", $title);
  $check->execute();
  $res = $check->get_result()->fetch_assoc();
  $check->close();

  if (($res['jml'] ?? 0) > 0) {
    jsAlertBack('Nama region sudah digunakan, silakan gunakan nama lain.');
  }

  // Insert
  $stmt = $conn->prepare("
    INSERT INTO T_Region (region_title, region_create_date) 
    VALUES (?, NOW())
  ");
  $stmt->bind_param("s", $title);
  $stmt->execute();
  $stmt->close();

  header("Location: region-list.php");
  exit;
}

// ============= UPDATE (EDIT) =============
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
  $id    = (int)($_POST['region_id'] ?? 0);
  $title = trim($_POST['region_title'] ?? '');

  if ($id <= 0) {
    jsAlertBack('ID region tidak valid.');
  }
  if ($title === '') {
    jsAlertBack('Region title tidak boleh kosong.');
  }

  // Cek duplikat nama (exclude diri sendiri)
  global $conn;
  $check = $conn->prepare("
    SELECT COUNT(*) AS jml 
    FROM T_Region 
    WHERE region_title = ? AND region_id <> ?
  ");
  $check->bind_param("si", $title, $id);
  $check->execute();
  $res = $check->get_result()->fetch_assoc();
  $check->close();

  if (($res['jml'] ?? 0) > 0) {
    jsAlertBack('Nama region sudah digunakan oleh data lain.');
  }

  // Update
  $stmt = $conn->prepare("
    UPDATE T_Region 
    SET region_title = ?, region_modify_date = NOW()
    WHERE region_id = ?
  ");
  $stmt->bind_param("si", $title, $id);
  $stmt->execute();
  $stmt->close();

  header("Location: region-list.php");
  exit;
}

// ============= DELETE =============
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  if ($id > 0) {

    // Cek apakah region dipakai di T_Wltk dengan status masih aktif
    // Sesuaikan kondisi status jika di sistemmu pakai value lain
    $check = $conn->prepare("
      SELECT COUNT(*) AS jml 
      FROM T_Wltk 
      WHERE wltk_ref_region = ? 
        AND (wltk_status IS NULL OR wltk_status <> 'close')
    ");
    $check->bind_param("i", $id);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();
    $check->close();

    if (($res['jml'] ?? 0) > 0) {
      jsAlertBack('Region tidak bisa dihapus karena masih digunakan di contract yang aktif.');
    }

    // Jika aman, hapus
    $stmt = $conn->prepare("DELETE FROM T_Region WHERE region_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
  }
  header("Location: region-list.php");
  exit;
}

// ============= AMBIL DATA LIST =============
$result = $conn->query("SELECT * FROM T_Region ORDER BY region_id ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Region | Contract Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../asset/css/style.css">
</head>

<body>
  <div class="d-flex" id="wrapper">
    <?php $base_url = '../';
    include('../partial/sidebar.php'); ?>

    <!-- CONTENT -->
    <div id="page-content" class="bg-light w-100">
      <?php $page_title = 'Region';
      $page_subtitle = 'List';
      include('../partial/navbar.php'); ?>

      <div class="container-fluid py-4 px-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="mb-0 fw-bold">Region List</h4>
          <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa fa-plus me-1"></i>Add Region
          </button>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
              <thead class="table-light text-center">
                <tr>
                  <th style="width:60px;">No</th>
                  <th>ID</th>
                  <th>Region Title</th>
                  <th>Create Date</th>
                  <th>Modify Date</th>
                  <th style="width:130px;">Action</th>
                </tr>
              </thead>
              <tbody class="text-center">
                <?php
                $no = 1;
                if ($result && $result->num_rows > 0):
                  while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= $no++; ?></td>
                      <td><?= $row['region_id']; ?></td>
                      <td><?= htmlspecialchars($row['region_title']); ?></td>
                      <td><?= $row['region_create_date']; ?></td>
                      <td><?= $row['region_modify_date']; ?></td>
                      <td>
                        <button
                          class="btn btn-sm btn-warning me-1 btn-edit"
                          data-id="<?= $row['region_id']; ?>"
                          data-title="<?= htmlspecialchars($row['region_title']); ?>"
                          data-bs-toggle="modal"
                          data-bs-target="#editModal">
                          <i class="fa fa-pen"></i>
                        </button>
                        <a href="region-list.php?delete=<?= $row['region_id']; ?>"
                          class="btn btn-sm btn-danger"
                          onclick="return confirm('Delete this region?');">
                          <i class="fa fa-trash"></i>
                        </a>
                      </td>
                    </tr>
                  <?php endwhile;
                else: ?>
                  <tr>
                    <td colspan="6" class="py-4 text-muted">No data</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ADD MODAL -->
  <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="post" class="modal-content">
        <input type="hidden" name="action" value="add">
        <div class="modal-header">
          <h5 class="modal-title">Add Region</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Region Title</label>
            <input type="text" name="region_title" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>

  <!-- EDIT MODAL -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <form method="post" class="modal-content">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="region_id" id="edit_region_id">
        <div class="modal-header">
          <h5 class="modal-title">Edit Region</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Region Title</label>
            <input type="text" name="region_title" id="edit_region_title" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>

  <script src="../asset/vendor/bootstrap/js/bootstrap.min.js"></script>
  <script src="../asset/js/script.js"></script>
  <script>
    // isi form edit
    document.querySelectorAll('.btn-edit').forEach(btn => {
      btn.addEventListener('click', () => {
        document.getElementById('edit_region_id').value = btn.dataset.id;
        document.getElementById('edit_region_title').value = btn.dataset.title;
      });
    });
  </script>
</body>

</html>