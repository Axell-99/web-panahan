<?php
include 'panggil.php';
include 'check_access.php';
requireAdmin();

// Handle update request
if (isset($_POST['update_id'])) {
    $update_id = intval($_POST['update_id']);
    $nama_peserta = $_POST['nama_peserta'];
    $category_id = intval($_POST['category_id']);
    $kegiatan_id = intval($_POST['kegiatan_id']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $asal_kota = $_POST['asal_kota'];
    $nama_club = $_POST['nama_club'];
    $sekolah = $_POST['sekolah'];
    $kelas = $_POST['kelas'];
    $nomor_hp = $_POST['nomor_hp'];
    
    $stmt = $conn->prepare("UPDATE peserta SET nama_peserta=?, category_id=?, kegiatan_id=?, tanggal_lahir=?, jenis_kelamin=?, asal_kota=?, nama_club=?, sekolah=?, kelas=?, nomor_hp=? WHERE id=?");
    $stmt->bind_param("siisssssssi", $nama_peserta, $category_id, $kegiatan_id, $tanggal_lahir, $jenis_kelamin, $asal_kota, $nama_club, $sekolah, $kelas, $nomor_hp, $update_id);
    
    if ($stmt->execute()) {
        $success_message = "Data peserta berhasil diperbarui!";
    } else {
        $error_message = "Gagal memperbarui data peserta!";
    }
}

// Handle delete request
if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    
    // Get bukti pembayaran file first to delete it
    $stmt = $conn->prepare("SELECT bukti_pembayaran FROM peserta WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $peserta_data = $result->fetch_assoc();
    
    if ($peserta_data) {
        // Delete file if exists
        if (!empty($peserta_data['bukti_pembayaran'])) {
            $file_path = 'uploads/' . $peserta_data['bukti_pembayaran'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM peserta WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        
        if ($stmt->execute()) {
            $success_message = "Data peserta berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus data peserta!";
        }
    } else {
        $error_message = "Data peserta tidak ditemukan!";
    }
}

// Handle export to Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    // Set headers untuk download Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=data_peserta_" . date('Y-m-d') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    // Ambil data dengan filter yang sama
    $category_id = $_GET['category_id'] ?? '';
    $kegiatan_id = $_GET['kegiatan_id'] ?? '';
    $gender = $_GET['gender'] ?? '';
    $nama = $_GET['nama'] ?? '';
    $club = $_GET['club'] ?? '';

    $query = "SELECT p.*, c.name AS category_name, k.nama_kegiatan
              FROM peserta p 
              LEFT JOIN categories c ON p.category_id = c.id
              LEFT JOIN kegiatan k ON p.kegiatan_id = k.id
              WHERE 1=1";

    $params = [];
    $types = '';

    if (!empty($category_id)) {
        $query .= " AND p.category_id = ?";
        $params[] = $category_id;
        $types .= "i";
    }

    if (!empty($kegiatan_id)) {
        $query .= " AND p.kegiatan_id = ?";
        $params[] = $kegiatan_id;
        $types .= "i";
    }

    if (!empty($gender)) {
        $query .= " AND p.jenis_kelamin = ?";
        $params[] = $gender;
        $types .= "s";
    }

    if (!empty($nama)) {
        $query .= " AND p.nama_peserta LIKE ?";
        $params[] = "%$nama%";
        $types .= "s";
    }

    if (!empty($club)) {
        $query .= " AND p.nama_club LIKE ?";
        $params[] = "%$club%";
        $types .= "s";
    }

    $query .= " ORDER BY p.nama_peserta ASC";

    if (!empty($params)) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($query);
    }

    // Output Excel content
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>No</th>";
    echo "<th>ID</th>";
    echo "<th>Nama Peserta</th>";
    echo "<th>Kategori</th>";
    echo "<th>Kegiatan</th>";
    echo "<th>Tanggal Lahir</th>";
    echo "<th>Umur</th>";
    echo "<th>Jenis Kelamin</th>";
    echo "<th>Asal Kota</th>";
    echo "<th>Nama Club</th>";
    echo "<th>Sekolah</th>";
    echo "<th>Kelas</th>";
    echo "<th>Nomor HP</th>";
    echo "<th>Status Pembayaran</th>";
    echo "<th>Bukti Pembayaran</th>";
    echo "<th>Tanggal Daftar</th>";
    echo "</tr>";
    
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        // Hitung umur
        $umur = "-";
        if (!empty($row['tanggal_lahir'])) {
            $dob = new DateTime($row['tanggal_lahir']);
            $today = new DateTime();
            $umur = $today->diff($dob)->y . " tahun";
        }
        
        $statusBayar = !empty($row['bukti_pembayaran']) ? 'Sudah Bayar' : 'Belum Bayar';
        
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['nama_peserta']) . "</td>";
        echo "<td>" . htmlspecialchars($row['category_name'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['nama_kegiatan'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['tanggal_lahir'] ?? '-') . "</td>";
        echo "<td>" . $umur . "</td>";
        echo "<td>" . htmlspecialchars($row['jenis_kelamin']) . "</td>";
        echo "<td>" . htmlspecialchars($row['asal_kota'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['nama_club'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['sekolah'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['kelas'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['nomor_hp'] ?? '-') . "</td>";
        echo "<td>" . $statusBayar . "</td>";
        echo "<td>" . htmlspecialchars($row['bukti_pembayaran'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at'] ?? '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    exit();
}

// --- Ambil kategori untuk dropdown ---
$kategoriResult = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
$kategoriList = [];
while ($row = $kategoriResult->fetch_assoc()) {
    $kategoriList[] = $row;
}

// --- Ambil kegiatan untuk dropdown ---
$kegiatanResult = $conn->query("SELECT id, nama_kegiatan FROM kegiatan ORDER BY nama_kegiatan ASC");
$kegiatanList = [];
while ($row = $kegiatanResult->fetch_assoc()) {
    $kegiatanList[] = $row;
}

// --- Ambil filter dari GET ---
$category_id = $_GET['category_id'] ?? '';
$kegiatan_id = $_GET['kegiatan_id'] ?? '';
$gender = $_GET['gender'] ?? '';
$nama = $_GET['nama'] ?? '';
$club = $_GET['club'] ?? '';

// --- Query peserta ---
$query = "SELECT p.*, c.name AS category_name, k.nama_kegiatan
          FROM peserta p 
          LEFT JOIN categories c ON p.category_id = c.id
          LEFT JOIN kegiatan k ON p.kegiatan_id = k.id
          WHERE 1=1";

$params = [];
$types = '';

if (!empty($category_id)) {
    $query .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

if (!empty($kegiatan_id)) {
    $query .= " AND p.kegiatan_id = ?";
    $params[] = $kegiatan_id;
    $types .= "i";
}

if (!empty($gender)) {
    $query .= " AND p.jenis_kelamin = ?";
    $params[] = $gender;
    $types .= "s";
}

if (!empty($nama)) {
    $query .= " AND p.nama_peserta LIKE ?";
    $params[] = "%$nama%";
    $types .= "s";
}

if (!empty($club)) {
    $query .= " AND p.nama_club LIKE ?";
    $params[] = "%$club%";
    $types .= "s";
}

$query .= " ORDER BY p.nama_peserta ASC";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

$peserta = [];
while ($row = $result->fetch_assoc()) {
    $peserta[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Peserta - Turnamen Panahan</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #8b5cf6;
            --accent-color: #06b6d4;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-color: #1f2937;
            --light-gray: #f8fafc;
            --medium-gray: #e2e8f0;
            --text-dark: #374151;
            --text-light: #6b7280;
        }

        body { 
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            min-height: 100vh;
        }

        .header-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.3);
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .header-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(180deg); }
        }

        .filter-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .data-table {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background: linear-gradient(135deg, var(--dark-color) 0%, #374151 100%);
            color: white;
            border: none;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 1rem 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table th:first-child {
            border-top-left-radius: 20px;
        }

        .table th:last-child {
            border-top-right-radius: 20px;
        }

        .table td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            font-size: 0.9rem;
            border-bottom: 1px solid var(--medium-gray);
            transition: all 0.3s ease;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn {
            border-radius: 12px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
        }

        .btn-filter {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        .btn-filter:hover {
            background: linear-gradient(135deg, #5855eb 0%, #7c3aed 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #dc2626 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, #ea580c 100%);
            border: none;
            color: white;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
            color: white;
        }

        .btn-outline-secondary {
            border: 2px solid var(--medium-gray);
            color: var(--text-light);
        }

        .btn-outline-secondary:hover {
            background: var(--medium-gray);
            border-color: var(--medium-gray);
            color: var(--text-dark);
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #059669 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }

        .btn-info {
            background: linear-gradient(135deg, var(--accent-color) 0%, #0891b2 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 182, 212, 0.4);
        }

        .stats-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 252, 0.9) 100%);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color), var(--success-color), var(--warning-color));
        }

        .stats-card h4 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stats-card small {
            color: var(--text-light);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-control, .form-select {
            border: 2px solid var(--medium-gray);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
            background: white;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .table-responsive {
            max-height: 70vh;
            border-radius: 20px;
        }

        .small-text {
            font-size: 0.85rem;
        }

        .text-truncate-custom {
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table-responsive::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 10px;
        }

        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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

        .data-table {
            animation: fadeInUp 0.6s ease;
        }

        .action-buttons {
            margin-bottom: 2rem;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
            border-bottom: none;
            padding: 1.5rem 2rem;
        }

        .modal-body {
            padding: 2rem;
        }

        .modal-footer {
            border-top: none;
            padding: 1.5rem 2rem;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="header-card text-center">
        <h1><i class="fas fa-bow-arrow me-3"></i>Data Peserta Turnamen Panahan</h1>
        <p class="mb-0">Sistem Manajemen Peserta Turnamen</p>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="action-buttons d-flex justify-content-between align-items-center">
        <div>
            <a href="dashboard.php" class="btn btn-info">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
            </a>
        </div>
        <div>
            <?php
            // Build export URL with current filters
            $exportParams = [];
            if (!empty($category_id)) $exportParams['category_id'] = $category_id;
            if (!empty($kegiatan_id)) $exportParams['kegiatan_id'] = $kegiatan_id;
            if (!empty($gender)) $exportParams['gender'] = $gender;
            if (!empty($nama)) $exportParams['nama'] = $nama;
            if (!empty($club)) $exportParams['club'] = $club;
            $exportParams['export'] = 'excel';
            $exportUrl = '?' . http_build_query($exportParams);
            ?>
            <a href="<?= $exportUrl ?>" class="btn btn-success">
                <i class="fas fa-file-excel me-2"></i>Export Excel
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card text-center text-white">
                <h4><?= count($peserta) ?></h4>
                <small>Total Peserta</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card text-center text-white">
                <h4><?= count(array_filter($peserta, fn($p) => $p['jenis_kelamin'] == 'Laki-laki')) ?></h4>
                <small>Laki-laki</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card text-center text-white">
                <h4><?= count(array_filter($peserta, fn($p) => $p['jenis_kelamin'] == 'Perempuan')) ?></h4>
                <small>Perempuan</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card text-center text-white">
                <h4><?= count(array_filter($peserta, fn($p) => !empty($p['bukti_pembayaran']))) ?></h4>
                <small>Sudah Bayar</small>
            </div>
        </div>
    </div>

    <!-- Form Filter -->
    <div class="filter-card">
        <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Pencarian</h5>
        <form method="get">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Kategori</label>
                    <select class="form-select" name="category_id">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($kategoriList as $kat): ?>
                            <option value="<?= $kat['id'] ?>" <?= $category_id==$kat['id']?'selected':'' ?>>
                                <?= htmlspecialchars($kat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kegiatan</label>
                    <select class="form-select" name="kegiatan_id">
                        <option value="">Semua Kegiatan</option>
                        <?php foreach ($kegiatanList as $keg): ?>
                            <option value="<?= $keg['id'] ?>" <?= $kegiatan_id==$keg['id']?'selected':'' ?>>
                                <?= htmlspecialchars($keg['nama_kegiatan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Gender</label>
                    <select class="form-select" name="gender">
                        <option value="">Semua</option>
                        <option value="Laki-laki" <?= $gender=="Laki-laki"?'selected':'' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= $gender=="Perempuan"?'selected':'' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Nama Peserta</label>
                    <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($nama) ?>" placeholder="Cari nama...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Club</label>
                    <input type="text" class="form-control" name="club" value="<?= htmlspecialchars($club) ?>" placeholder="Nama club...">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-12 text-end">
                    <button type="submit" class="btn btn-filter btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                    <a href="?" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabel Peserta -->
    <div class="data-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Peserta</th>
                        <th>Kategori</th>
                        <th>Kegiatan</th>
                        <th>Umur</th>
                        <th>Gender</th>
                        <th>Asal Kota</th>
                        <th>Club</th>
                        <th>Sekolah</th>
                        <th>Kelas</th>
                        <th>No. HP</th>
                        <th>Status</th>
                        <th style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($peserta)): ?>
                    <tr>
                        <td colspan="13" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i><br>
                            Tidak ada data peserta yang ditemukan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = 1; 
                    foreach ($peserta as $p): 
                        // Hitung umur dari tanggal_lahir
                        $umur = "-";
                        if (!empty($p['tanggal_lahir'])) {
                            $dob = new DateTime($p['tanggal_lahir']);
                            $today = new DateTime();
                            $umur = $today->diff($dob)->y . " tahun";
                        }
                        
                        // Status pembayaran
                        $statusBayar = !empty($p['bukti_pembayaran']) ? 'Sudah Bayar' : 'Belum Bayar';
                        $badgeClass = !empty($p['bukti_pembayaran']) ? 'bg-success' : 'bg-warning text-dark';
                    ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                                <strong><?= htmlspecialchars($p['nama_peserta']) ?></strong><br>
                                <small class="text-muted">ID: <?= $p['id'] ?></small>
                            </td>
                            <td>
                                <?php if (!empty($p['category_name'])): ?>
                                    <span class="badge bg-info text-dark"><?= htmlspecialchars($p['category_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">Belum ditentukan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($p['nama_kegiatan'])): ?>
                                    <span class="badge bg-primary"><?= htmlspecialchars($p['nama_kegiatan']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted small">Belum ditentukan</span>
                                <?php endif; ?>
                            </td>
                            <td class="small-text"><?= $umur ?></td>
                            <td>
                                <span class="badge badge-gender <?= $p['jenis_kelamin'] == 'Laki-laki' ? 'bg-primary' : 'bg-danger' ?>">
                                    <i class="fas <?= $p['jenis_kelamin'] == 'Laki-laki' ? 'fa-mars' : 'fa-venus' ?> me-1"></i>
                                    <?= htmlspecialchars($p['jenis_kelamin']) ?>
                                </span>
                            </td>
                            <td class="small-text"><?= htmlspecialchars($p['asal_kota'] ?? '-') ?></td>
                            <td class="small-text text-truncate-custom" title="<?= htmlspecialchars($p['nama_club'] ?? '') ?>">
                                <?= htmlspecialchars($p['nama_club'] ?? '-') ?>
                            </td>
                            <td class="small-text text-truncate-custom" title="<?= htmlspecialchars($p['sekolah'] ?? '') ?>">
                                <?= htmlspecialchars($p['sekolah'] ?? '-') ?>
                            </td>
                            <td class="small-text"><?= htmlspecialchars($p['kelas'] ?? '-') ?></td>
                            <td class="small-text">
                                <?php if (!empty($p['nomor_hp'])): ?>
                                    <a href="tel:<?= $p['nomor_hp'] ?>" class="text-decoration-none">
                                        <i class="fas fa-phone-alt me-1"></i><?= htmlspecialchars($p['nomor_hp']) ?>
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-status <?= $badgeClass ?>">
                                    <i class="fas <?= !empty($p['bukti_pembayaran']) ? 'fa-check-circle' : 'fa-clock' ?> me-1"></i>
                                    <?= $statusBayar ?>
                                </span>
                                <?php if (!empty($p['bukti_pembayaran'])): ?>
                                    <br><small class="text-muted">
                                        <a href="#" class="text-info" onclick="showImage('<?= htmlspecialchars($p['bukti_pembayaran']) ?>', '<?= htmlspecialchars($p['nama_peserta']) ?>')">
                                            <i class="fas fa-file-image me-1"></i>Lihat Bukti
                                        </a>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm me-1 mb-1" onclick="editPeserta(<?= htmlspecialchars(json_encode($p)) ?>)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm mb-1" onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama_peserta'], ENT_QUOTES) ?>')" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if (!empty($peserta)): ?>
        <div class="mt-3 text-end">
            <small class="text-muted">Menampilkan <?= count($peserta) ?> peserta</small>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Edit Peserta -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="editModalLabel">
                    <i class="fas fa-edit me-2"></i>Edit Data Peserta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="editForm">
                <div class="modal-body">
                    <input type="hidden" name="update_id" id="edit_id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Peserta <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_peserta" id="edit_nama_peserta" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" id="edit_category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($kategoriList as $kat): ?>
                                    <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Kegiatan <span class="text-danger">*</span></label>
                            <select class="form-select" name="kegiatan_id" id="edit_kegiatan_id" required>
                                <option value="">Pilih Kegiatan</option>
                                <?php foreach ($kegiatanList as $keg): ?>
                                    <option value="<?= $keg['id'] ?>"><?= htmlspecialchars($keg['nama_kegiatan']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_lahir" id="edit_tanggal_lahir" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select class="form-select" name="jenis_kelamin" id="edit_jenis_kelamin" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Asal Kota</label>
                            <input type="text" class="form-control" name="asal_kota" id="edit_asal_kota">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Nama Club</label>
                            <input type="text" class="form-control" name="nama_club" id="edit_nama_club">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Sekolah</label>
                            <input type="text" class="form-control" name="sekolah" id="edit_sekolah">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Kelas</label>
                            <input type="text" class="form-control" name="kelas" id="edit_kelas">
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Nomor HP</label>
                            <input type="text" class="form-control" name="nomor_hp" id="edit_nomor_hp" placeholder="Contoh: 08123456789">
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <small><strong>Catatan:</strong> Field yang bertanda <span class="text-danger">*</span> wajib diisi.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal untuk menampilkan gambar -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Bukti Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <!-- Content akan dimuat dinamis oleh JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Tutup
                </button>
                <a id="downloadImage" href="" download class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-user-times fa-4x text-danger mb-3"></i>
                    <h5>Apakah Anda yakin ingin menghapus peserta ini?</h5>
                    <p class="text-muted mb-0">Nama Peserta:</p>
                    <p class="fw-bold" id="deletePesertaName"></p>
                </div>
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Perhatian:</strong> Data yang dihapus tidak dapat dikembalikan!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Batal
                </button>
                <form method="POST" id="deleteForm" style="display: inline;">
                    <input type="hidden" name="delete_id" id="deleteIdInput">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-2"></i>Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
// Auto-submit form on select change untuk better UX
document.querySelectorAll('select[name="category_id"], select[name="kegiatan_id"], select[name="gender"]').forEach(function(select) {
    select.addEventListener('change', function() {
        this.form.submit();
    });
});

// Tooltip untuk teks yang terpotong
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
});

// Function untuk edit peserta
function editPeserta(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_nama_peserta').value = data.nama_peserta || '';
    document.getElementById('edit_category_id').value = data.category_id || '';
    document.getElementById('edit_kegiatan_id').value = data.kegiatan_id || '';
    document.getElementById('edit_tanggal_lahir').value = data.tanggal_lahir || '';
    document.getElementById('edit_jenis_kelamin').value = data.jenis_kelamin || '';
    document.getElementById('edit_asal_kota').value = data.asal_kota || '';
    document.getElementById('edit_nama_club').value = data.nama_club || '';
    document.getElementById('edit_sekolah').value = data.sekolah || '';
    document.getElementById('edit_kelas').value = data.kelas || '';
    document.getElementById('edit_nomor_hp').value = data.nomor_hp || '';
    
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();
}

// Function untuk konfirmasi hapus
function confirmDelete(id, nama) {
    document.getElementById('deleteIdInput').value = id;
    document.getElementById('deletePesertaName').textContent = nama;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

// Function untuk menampilkan gambar dalam modal
function showImage(filename, pesertaName) {
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    const modalTitle = document.getElementById('imageModalLabel');
    const modalBody = document.querySelector('#imageModal .modal-body');
    const downloadLink = document.getElementById('downloadImage');
    
    // Set title
    modalTitle.textContent = 'Bukti Pembayaran - ' + pesertaName;
    
    // Cek ekstensi file untuk menentukan cara menampilkan
    const fileExtension = filename.toLowerCase().split('.').pop();
    const imagePath = 'uploads/' + filename;
    
    // Reset modal body
    modalBody.innerHTML = '';
    
    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].includes(fileExtension)) {
        // Tampilkan sebagai gambar
        const img = document.createElement('img');
        img.id = 'modalImage';
        img.className = 'img-fluid';
        img.style.maxWidth = '100%';
        img.style.maxHeight = '500px';
        img.style.borderRadius = '8px';
        img.alt = 'Bukti Pembayaran';
        
        // Error div
        const errorDiv = document.createElement('div');
        errorDiv.id = 'imageError';
        errorDiv.className = 'alert alert-warning mt-3';
        errorDiv.style.display = 'none';
        
        // Info div
        const infoDiv = document.createElement('div');
        infoDiv.className = 'mt-3 p-3 bg-light rounded';
        infoDiv.style.fontSize = '14px';
        infoDiv.innerHTML = `
            <strong>File:</strong> ${filename}<br>
            <strong>Peserta:</strong> ${pesertaName}<br>
            <strong>Path:</strong> ${imagePath}
        `;
        
        // Test loading gambar dengan berbagai kemungkinan path
        const possiblePaths = [
            'uploads/' + filename,
            'uploads/bukti/' + filename,
            'uploads/pembayaran/' + filename,
            filename
        ];
        
        let pathIndex = 0;
        
        function tryNextPath() {
            if (pathIndex >= possiblePaths.length) {
                // Semua path gagal, tampilkan error
                errorDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Gambar tidak dapat dimuat dari semua path yang dicoba.
                    <br><strong>File:</strong> ${filename}
                    <br><strong>Path yang dicoba:</strong>
                    <ul style="text-align: left; margin: 10px 0;">
                        ${possiblePaths.map(path => `<li>${path}</li>`).join('')}
                    </ul>
                    <div class="mt-3">
                        ${possiblePaths.map(path => 
                            `<a href="${path}" target="_blank" class="btn btn-sm btn-outline-primary me-2 mb-2">
                                <i class="fas fa-external-link-alt me-1"></i>Coba: ${path}
                            </a>`
                        ).join('')}
                    </div>
                `;
                errorDiv.style.display = 'block';
                modalBody.appendChild(errorDiv);
                modalBody.appendChild(infoDiv);
                return;
            }
            
            const testImage = new Image();
            const currentPath = possiblePaths[pathIndex];
            
            testImage.onload = function() {
                // Gambar berhasil dimuat
                img.src = currentPath;
                modalBody.appendChild(img);
                modalBody.appendChild(infoDiv);
                
                // Update info dengan path yang berhasil
                infoDiv.innerHTML = `
                    <strong>File:</strong> ${filename}<br>
                    <strong>Peserta:</strong> ${pesertaName}<br>
                    <strong>Path:</strong> ${currentPath} ✅
                `;
                
                // Set download link
                downloadLink.href = currentPath;
                downloadLink.download = 'bukti_pembayaran_' + pesertaName.replace(/[^a-zA-Z0-9]/g, '_') + '.' + fileExtension;
            };
            
            testImage.onerror = function() {
                // Coba path berikutnya
                pathIndex++;
                tryNextPath();
            };
            
            // Mulai test loading
            testImage.src = currentPath;
        }
        
        // Mulai dari path pertama
        tryNextPath();
        
    } else if (fileExtension === 'pdf') {
        // Tampilkan interface untuk PDF
        modalBody.innerHTML = `
            <div class="text-center p-4">
                <div style="font-size: 48px; color: #dc3545; margin-bottom: 20px;">📄</div>
                <h4>File PDF</h4>
                <p class="mb-3 text-muted">File bukti pembayaran dalam format PDF</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="${imagePath}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i>Buka PDF
                    </a>
                    <a href="${imagePath}" download="${filename}" class="btn btn-success">
                        <i class="fas fa-download me-1"></i>Download
                    </a>
                </div>
                <div class="mt-3 p-3 bg-light rounded text-start" style="font-size: 14px;">
                    <strong>File:</strong> ${filename}<br>
                    <strong>Peserta:</strong> ${pesertaName}
                </div>
            </div>
        `;
        
        // Set download link
        downloadLink.href = imagePath;
        downloadLink.download = filename;
        
    } else {
        // File format tidak didukung
        modalBody.innerHTML = `
            <div class="text-center p-4">
                <div style="font-size: 48px; color: #ffc107; margin-bottom: 20px;">⚠️</div>
                <h4>File tidak dapat ditampilkan</h4>
                <p class="mb-3 text-muted">Format file tidak didukung untuk preview</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="${imagePath}" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i>Buka File
                    </a>
                    <a href="${imagePath}" download="${filename}" class="btn btn-success">
                        <i class="fas fa-download me-1"></i>Download
                    </a>
                </div>
                <div class="mt-3 p-3 bg-light rounded text-start" style="font-size: 14px;">
                    <strong>File:</strong> ${filename}<br>
                    <strong>Peserta:</strong> ${pesertaName}<br>
                    <strong>Format:</strong> ${fileExtension.toUpperCase()}
                </div>
            </div>
        `;
        
        // Set download link
        downloadLink.href = imagePath;
        downloadLink.download = filename;
    }
    
    // Show modal
    modal.show();
}

// Konfirmasi sebelum export Excel
document.querySelector('a[href*="export=excel"]').addEventListener('click', function(e) {
    if (!confirm('Apakah Anda yakin ingin mengexport data ke Excel? Proses ini mungkin membutuhkan waktu beberapa detik.')) {
        e.preventDefault();
    }
});

// Auto dismiss alerts after 5 seconds
setTimeout(function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        var bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>
</body>
</html>