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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --dark-color: #1f2937;
            --light-bg: #f8fafc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 10px;
        }

        .main-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 15px;
            max-width: 1400px;
            margin: 0 auto;
            animation: fadeInUp 0.6s ease-out;
            width: 100%;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .page-header h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            word-break: break-word;
        }

        .page-header h5 i {
            font-size: 20px;
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #059669);
            padding: 10px 16px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            width: 100%;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            background: linear-gradient(135deg, #059669, var(--success-color));
        }

        .top-controls {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }

        .search-container {
            display: flex;
            gap: 8px;
            width: 100%;
        }

        .search-container input {
            flex: 1;
            min-width: 0;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            padding: 10px 12px;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 10px 16px;
            white-space: nowrap;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table {
            margin: 0;
            min-width: 600px;
            width: 100%;
        }

        .table thead {
            background: linear-gradient(135deg, #f8fafc, #e5e7eb);
        }

        .table thead th {
            border: none;
            padding: 12px 10px;
            font-weight: 700;
            color: var(--dark-color);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f1f5f9;
        }

        .table tbody tr:hover {
            background: linear-gradient(to right, #faf5ff, #f5f3ff);
        }

        .table tbody td {
            padding: 12px 10px;
            vertical-align: middle;
            color: #374151;
            font-size: 13px;
        }

        .btn-sm {
            padding: 6px 10px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.3s ease;
            border: none;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-sm.btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }

        .btn-sm.btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .btn-sm.btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .btn-sm.btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            display: inline-flex;
            width: 100%;
            text-align: center;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(107, 114, 128, 0.4);
            color: white;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px 20px;
            border: none;
        }

        .modal-header.bg-danger {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
        }

        .modal-title {
            font-weight: 700;
            font-size: 16px;
            word-break: break-word;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .modal-footer button,
        .modal-footer .btn {
            flex: 1;
            min-width: 100px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .badge-no {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 5px 10px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
        }

        .age-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .age-badge.min {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
        }

        .age-badge.max {
            background: linear-gradient(135deg, #fce7f3, #fbcfe8);
            color: #9f1239;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .empty-state h5 {
            font-size: 16px;
            margin-bottom: 8px;
        }

        .empty-state p {
            font-size: 13px;
            margin: 0;
        }

        /* Mobile Optimizations */
        @media (max-width: 575px) {
            body {
                padding: 5px;
            }

            .main-container {
                padding: 10px;
                border-radius: 15px;
            }

            .page-header {
                padding: 12px;
                margin-bottom: 15px;
            }

            .page-header h5 {
                font-size: 15px;
            }

            .page-header h5 i {
                font-size: 16px;
            }

            .top-controls {
                gap: 10px;
                margin-bottom: 15px;
            }

            .btn-success {
                padding: 10px 14px;
                font-size: 13px;
            }

            .search-container {
                gap: 6px;
            }

            .form-control {
                padding: 8px 10px;
                font-size: 13px;
            }

            .btn-primary {
                padding: 8px 12px;
                font-size: 13px;
            }

            .table {
                font-size: 12px;
                min-width: 500px;
            }

            .table thead th {
                padding: 10px 8px;
                font-size: 10px;
            }

            .table tbody td {
                padding: 10px 8px;
                font-size: 12px;
            }

            .badge-no {
                padding: 4px 8px;
                font-size: 11px;
            }

            .age-badge {
                padding: 3px 8px;
                font-size: 10px;
            }

            .btn-sm {
                padding: 5px 8px;
                font-size: 11px;
                gap: 3px;
            }

            .btn-sm i {
                font-size: 10px;
            }

            .action-buttons {
                gap: 4px;
            }

            .btn-secondary {
                padding: 10px 16px;
                font-size: 13px;
            }

            .modal-dialog {
                margin: 10px;
            }

            .modal-title {
                font-size: 14px;
            }

            .modal-body {
                padding: 15px;
            }

            .form-label {
                font-size: 12px;
            }

            .modal-footer {
                padding: 12px 15px;
                gap: 8px;
            }

            .empty-state {
                padding: 30px 15px;
            }

            .empty-state i {
                font-size: 36px;
            }

            .empty-state h5 {
                font-size: 14px;
            }

            .empty-state p {
                font-size: 12px;
            }
        }

        /* Extra Small Mobile */
        @media (max-width: 400px) {
            .page-header h5 {
                font-size: 14px;
                gap: 6px;
            }

            .btn-success,
            .btn-primary,
            .btn-secondary {
                font-size: 12px;
            }

            .table {
                min-width: 450px;
                font-size: 11px;
            }

            .table thead th {
                padding: 8px 6px;
                font-size: 9px;
            }

            .table tbody td {
                padding: 8px 6px;
                font-size: 11px;
            }

            .btn-sm {
                padding: 4px 6px;
                font-size: 10px;
            }

            /* Hide icons on very small screens */
            .btn-success i:not(.modal-title i),
            .btn-primary i:not(.modal-title i),
            .btn-secondary i:not(.modal-title i) {
                display: none;
            }
        }

        /* Tablet and Desktop */
        @media (min-width: 576px) {
            .page-header h5 {
                font-size: 20px;
            }

            .btn-success,
            .btn-secondary {
                width: auto;
                min-width: 150px;
            }

            .top-controls {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }

            .search-container {
                width: auto;
                max-width: 400px;
            }
        }

        @media (min-width: 768px) {
            body {
                padding: 20px;
            }

            .main-container {
                padding: 25px;
            }

            .page-header {
                padding: 20px;
            }

            .page-header h5 {
                font-size: 24px;
            }

            .table thead th {
                padding: 15px 12px;
                font-size: 12px;
            }

            .table tbody td {
                padding: 14px 12px;
                font-size: 14px;
            }

            .table tbody tr:hover {
                transform: scale(1.005);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            }

            .btn-sm {
                padding: 6px 12px;
                font-size: 13px;
            }

            .empty-state {
                padding: 50px 20px;
            }

            .empty-state i {
                font-size: 56px;
            }

            .empty-state h5 {
                font-size: 18px;
            }

            .empty-state p {
                font-size: 14px;
            }

            .modal-title {
                font-size: 18px;
            }

            .modal-body {
                padding: 25px;
            }

            .modal-footer {
                padding: 18px 25px;
            }
        }

        @media (min-width: 992px) {
            .main-container {
                padding: 30px;
            }

            .page-header h5 {
                font-size: 28px;
            }

            .table thead th {
                padding: 18px 15px;
            }

            .table tbody td {
                padding: 16px 15px;
            }
        }

        /* Landscape mode adjustments */
        @media (max-width: 767px) and (orientation: landscape) {
            .page-header h5 {
                font-size: 16px;
            }

            .empty-state {
                padding: 25px 15px;
            }

            .empty-state i {
                font-size: 40px;
            }
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .btn-success,
            .btn-secondary,
            .search-container,
            .action-buttons {
                display: none !important;
            }

            .main-container {
                box-shadow: none;
            }
        }

        /* Custom scrollbar for table */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }
    </style>
</head>
<body>

    <div class="main-container">
        <div class="page-header">
            <h5>
                <i class="fas fa-layer-group"></i>
                <span>Data Categories Management</span>
            </h5>
        </div>

        <div class="top-controls">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="fas fa-plus-circle"></i>
                <span>Tambah Data</span>
            </button>
            <form class="search-container" method="get">
                <input class="form-control" type="search" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="🔍 Cari category, umur...">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i>
                    <span>Cari</span>
                </button>
            </form>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 70px;">No</th>
                            <th>Nama Category</th>
                            <th style="width: 110px;">Min Age</th>
                            <th style="width: 110px;">Max Age</th>
                            <th style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($result->num_rows > 0):
                            $no = 1;
                            while ($row = $result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><span class="badge-no"><?= $no++; ?></span></td>
                            <td><strong><?= htmlspecialchars($row['name']); ?></strong></td>
                            <td><span class="age-badge min"><?= htmlspecialchars($row['min_age']); ?> th</span></td>
                            <td><span class="age-badge max"><?= htmlspecialchars($row['max_age']); ?> th</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-primary" onclick="editData(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>', <?= $row['min_age'] ?>, <?= $row['max_age'] ?>)">
                                        <i class="fas fa-edit"></i>
                                        <span>Edit</span>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteData(<?= $row['id'] ?>, '<?= addslashes($row['name']) ?>')">
                                        <i class="fas fa-trash"></i>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h5>Tidak ada data</h5>
                                    <p>Silakan tambahkan category baru</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle"></i>
                        <span>Tambah Category</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-tag"></i>
                                <span>Nama Category</span>
                            </label>
                            <input type="text" class="form-control" name="name" placeholder="Masukkan nama category" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-hourglass-start"></i>
                                <span>Min Age</span>
                            </label>
                            <input type="number" class="form-control" name="min_age" placeholder="Umur minimum" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-hourglass-end"></i>
                                <span>Max Age</span>
                            </label>
                            <input type="number" class="form-control" name="max_age" placeholder="Umur maximum" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i>
                        <span>Edit Category</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-tag"></i>
                                <span>Nama Category</span>
                            </label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-hourglass-start"></i>
                                <span>Min Age</span>
                            </label>
                            <input type="number" class="form-control" name="min_age" id="edit_min_age" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-hourglass-end"></i>
                                <span>Max Age</span>
                            </label>
                            <input type="number" class="form-control" name="max_age" id="edit_max_age" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync-alt"></i>
                            <span>Update</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Hapus Category</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus category <strong id="delete_name"></strong>?</p>
                    <p class="text-muted mb-0">
                        <small>
                            <i class="fas fa-info-circle"></i>
                            Tindakan ini tidak dapat dibatalkan!
                        </small>
                    </p>
                </div>
                <div class="modal-footer">
                    <form method="POST" style="display: contents;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i>
                            <span>Batal</span>
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i>
                            <span>Hapus</span>
                        </button>
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

        // Smooth scroll to top after form submission
        if (window.location.search === '') {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Add touch feedback for mobile
        if ('ontouchstart' in window) {
            document.querySelectorAll('.btn, .table tbody tr').forEach(elem => {
                elem.addEventListener('touchstart', function() {
                    this.style.opacity = '0.8';
                });
                elem.addEventListener('touchend', function() {
                    this.style.opacity = '1';
                });
            });
        }

        // Prevent double submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                }
            });
        });

        // Auto-hide modals on mobile after successful action
        window.addEventListener('pageshow', function() {
            document.querySelectorAll('.modal').forEach(modal => {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            });
        });
    </script>

</body>
</html>