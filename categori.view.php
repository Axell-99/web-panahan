<?php
include 'panggil.php';
include 'check_access.php';
requireAdmin();

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $min_age = (int)$_POST['min_age'];
                $max_age = (int)$_POST['max_age'];
                
                $stmt = $conn->prepare("INSERT INTO categories (name, min_age, max_age) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $name, $min_age, $max_age);
                $stmt->execute();
                $stmt->close();
                break;
                
            case 'update':
                $id = (int)$_POST['id'];
                $name = mysqli_real_escape_string($conn, $_POST['name']);
                $min_age = (int)$_POST['min_age'];
                $max_age = (int)$_POST['max_age'];
                
                $stmt = $conn->prepare("UPDATE categories SET name=?, min_age=?, max_age=? WHERE id=?");
                $stmt->bind_param("siii", $name, $min_age, $max_age, $id);
                $stmt->execute();
                $stmt->close();
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                break;
        }
        // Redirect to prevent form resubmission
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Search functionality
$search = '';
$sql = "SELECT * FROM categories";
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search = mysqli_real_escape_string($conn, $_GET['q']);
    $sql .= " WHERE name LIKE '%$search%' OR min_age LIKE '%$search%' OR max_age LIKE '%$search%'";
}
$sql .= " ORDER BY id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Data Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

    <h5>Data Categories</h5>

    <div class="d-flex justify-content-between mb-3">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
            Tambah Data
        </button>
        <form class="d-flex" method="get">
            <input class="form-control me-2" type="search" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari data...">
            <button class="btn btn-primary" type="submit">Cari</button>
        </form>
    </div>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th style="width: 50px;">No</th>
                <th>Nama</th>
                <th>Min Age</th>
                <th>Max Age</th>
                <th style="width: 150px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while ($row = $result->fetch_assoc()): 
            ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['name']); ?></td>
                <td><?= htmlspecialchars($row['min_age']); ?></td>
                <td><?= htmlspecialchars($row['max_age']); ?></td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="editData(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>', <?= $row['min_age'] ?>, <?= $row['max_age'] ?>)">
                        Edit
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteData(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>')">
                        Hapus
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <a href="dashboard.php" class="btn btn-secondary">Back</a>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">Nama Category</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Min Age</label>
                            <input type="number" class="form-control" name="min_age" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Age</label>
                            <input type="number" class="form-control" name="max_age" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Nama Category</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Min Age</label>
                            <input type="number" class="form-control" name="min_age" id="edit_min_age" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Max Age</label>
                            <input type="number" class="form-control" name="max_age" id="edit_max_age" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hapus Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus category <strong id="delete_name"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editData(id, name, minAge, maxAge) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_min_age').value = minAge;
            document.getElementById('edit_max_age').value = maxAge;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function deleteData(id, name) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_name').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>

</body>
</html>