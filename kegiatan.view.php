<?php
include 'panggil.php';
include 'check_access.php';
requireLogin();

// Ambil semua kategori
$kategoriResult = $conn->query("SELECT id, name, min_age, max_age FROM categories ORDER BY min_age ASC");
$kategoriList = [];
while ($row = $kategoriResult->fetch_assoc()) {
    $kategoriList[] = $row;
}

// Proses hapus data
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $kegiatanId = intval($_GET['id']);
    
    // Hapus dari kegiatan_kategori dulu (foreign key)
    $conn->query("DELETE FROM kegiatan_kategori WHERE kegiatan_id = $kegiatanId");
    
    // Kemudian hapus dari kegiatan
    $conn->query("DELETE FROM kegiatan WHERE id = $kegiatanId");
    
    header("Location: kegiatan.view.php");
    exit;
}

// Proses tambah/edit data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['namaKegiatan'];
    $kategoriDipilih = isset($_POST['kategori']) ? $_POST['kategori'] : [];
    $editId = isset($_POST['editId']) ? intval($_POST['editId']) : null;

    if ($editId) {
        // Update data existing
        $stmt = $conn->prepare("UPDATE kegiatan SET nama_kegiatan = ? WHERE id = ?");
        $stmt->bind_param("si", $nama, $editId);
        $stmt->execute();
        $stmt->close();

        // Hapus kategori lama
        $conn->query("DELETE FROM kegiatan_kategori WHERE kegiatan_id = $editId");
        
        $kegiatanId = $editId;
    } else {
        // Insert data baru
        $stmt = $conn->prepare("INSERT INTO kegiatan (nama_kegiatan) VALUES (?)");
        $stmt->bind_param("s", $nama);
        $stmt->execute();
        $kegiatanId = $stmt->insert_id;
        $stmt->close();
    }

    // Simpan kategori terpilih
    if (!empty($kategoriDipilih)) {
        foreach ($kategoriDipilih as $kategoriId) {
            $stmt = $conn->prepare("INSERT INTO kegiatan_kategori (kegiatan_id, category_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $kegiatanId, $kategoriId);
            $stmt->execute();
            $stmt->close();
        }
    }

    // redirect setelah simpan
    header("Location: kegiatan.view.php");
    exit;
}

// Ambil data kegiatan dengan kategorinya
$query = "
    SELECT 
        k.id, 
        k.nama_kegiatan AS kegiatan_nama,
        GROUP_CONCAT(kk.category_id) AS category_ids,
        GROUP_CONCAT(c.name SEPARATOR '|') AS category_names
    FROM kegiatan k
    LEFT JOIN kegiatan_kategori kk ON k.id = kk.kegiatan_id
    LEFT JOIN categories c ON kk.category_id = c.id
    GROUP BY k.id, k.nama_kegiatan
    ORDER BY k.id DESC
";

$result = $conn->query($query);
$kegiatanData = [];
while ($row = $result->fetch_assoc()) {
    $categoryIds = $row['category_ids'] ? explode(',', $row['category_ids']) : [];
    $categoryNames = $row['category_names'] ? explode('|', $row['category_names']) : [];
    
    $kegiatanData[] = [
        'id' => $row['id'],
        'nama' => $row['kegiatan_nama'],
        'category_ids' => array_map('intval', $categoryIds),
        'category_names' => $categoryNames
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kegiatan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 20px;
            color: #2d3748;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            background: white;
            padding: 25px 35px;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            margin-bottom: 20px;
        }

        h1 {
            color: #1a202c;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .back-link:hover {
            gap: 10px;
            color: #5568d3;
        }

        .controls-section {
            background: white;
            padding: 20px 30px;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-info {
            background: #3b82f6;
            color: white;
        }

        .btn-info:hover {
            background: #2563eb;
        }
        .btn-iya {
            background: #5c20acff;
            color: white;
        }

        .btn-iya:hover {
            background: #5c20acff;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn-warning:hover {
            background: #d97706;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .search-box {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            flex: 1;
            padding: 12px 18px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .table-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 16px 20px;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #f9fafb;
            color: #374151;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }

        tbody tr {
            border-bottom: 1px solid #f3f4f6;
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            background: #f9fafb;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .actions .btn {
            padding: 8px 16px;
            font-size: 13px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 35px;
            width: 90%;
            max-width: 600px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            max-height: 85vh;
            overflow-y: auto;
        }

        .modal-content::-webkit-scrollbar {
            width: 8px;
        }

        .modal-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .modal-content::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }

        .close {
            color: #9ca3af;
            float: right;
            font-size: 28px;
            font-weight: 600;
            cursor: pointer;
            line-height: 1;
            transition: all 0.2s ease;
        }

        .close:hover {
            color: #ef4444;
        }

        .modal-content h2 {
            color: #1a202c;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 25px;
            clear: both;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
            font-size: 14px;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
        }

        .form-group input[type="text"]:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .checkbox-group {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            max-height: 280px;
            overflow-y: auto;
            background: #fafafa;
        }

        .checkbox-group::-webkit-scrollbar {
            width: 8px;
        }

        .checkbox-group::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .checkbox-group::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }

        .checkbox-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
            padding: 12px;
            background: white;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .checkbox-item:hover {
            border-color: #667eea;
            background: #f9fafb;
        }

        .checkbox-item:last-child {
            margin-bottom: 0;
        }

        .checkbox-item input[type="checkbox"] {
            margin-right: 12px;
            margin-top: 2px;
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .checkbox-label {
            flex: 1;
            font-size: 14px;
            line-height: 1.5;
            color: #374151;
            cursor: pointer;
        }

        .checkbox-label strong {
            display: block;
            margin-bottom: 4px;
            color: #1a202c;
            font-weight: 600;
        }

        .checkbox-label .age-info {
            font-size: 12px;
            color: #6b7280;
        }

        .form-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #f3f4f6;
        }

        .required {
            color: #ef4444;
        }

        .category-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            margin: 2px;
        }

        .text-muted {
            color: #9ca3af;
            font-style: italic;
            font-size: 14px;
        }

        .no-data {
            padding: 40px 20px;
            text-align: center;
            color: #9ca3af;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .page-header, .controls-section, .table-container {
                padding: 20px;
            }

            h1 {
                font-size: 24px;
            }

            .controls-section {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                max-width: 100%;
            }

            table {
                font-size: 13px;
            }

            th, td {
                padding: 12px 14px;
            }

            .actions {
                flex-direction: column;
            }

            .actions .btn {
                width: 100%;
                justify-content: center;
            }

            .modal-content {
                padding: 25px;
                margin: 20px auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
            <h1>Data Kegiatan</h1>
        </div>
    
        <div class="controls-section">
            <button class="btn btn-success" onclick="openModal()">
                + Tambah Data
            </button>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Cari kegiatan..." onkeyup="searchData()">
                <button class="btn btn-primary" onclick="searchData()">Cari</button>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Nama Kegiatan</th>
                        <th>Kategori</th>
                        <th style="width: 400px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($kegiatanData)): ?>
                        <tr>
                            <td colspan="4" class="no-data">Tidak ada data kegiatan</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($kegiatanData as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><strong><?= htmlspecialchars($item['nama']) ?></strong></td>
                                <td>
                                    <?php if (empty($item['category_names'])): ?>
                                        <span class="text-muted">Belum ada kategori</span>
                                    <?php else: ?>
                                        <?php foreach ($item['category_names'] as $categoryName): ?>
                                            <span class="category-badge"><?= htmlspecialchars($categoryName) ?></span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="pendaftaran.php?id=<?php echo $item['id']?>" class="btn btn-iya">Pendaftaran</a>
                                        <a href="detail.php?id=<?php echo $item['id']?>" class="btn btn-info">Detail</a>
                                        <button class="btn btn-warning" onclick="editData(<?= $item['id'] ?>)">Edit</button>
                                        <button class="btn btn-danger" onclick="deleteData(<?= $item['id'] ?>)">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">×</span>
                <h2 id="modalTitle">Tambah Data Kegiatan</h2>
                <form id="kegiatanForm" method="POST">
                    <input type="hidden" id="editId" name="editId" value="">
                    
                    <div class="form-group">
                        <label for="namaKegiatan">Nama Kegiatan <span class="required">*</span></label>
                        <input type="text" id="namaKegiatan" name="namaKegiatan" required placeholder="Masukkan nama kegiatan">
                    </div>
                    
                    <div class="form-group">
                        <label>Pilih Kategori <span class="required">*</span></label>
                        <div class="checkbox-group">
                            <?php foreach ($kategoriList as $kategori): ?>
                                <div class="checkbox-item" onclick="toggleCheckbox('kategori_<?= $kategori['id']; ?>')">
                                    <input type="checkbox" name="kategori[]" value="<?= $kategori['id']; ?>" id="kategori_<?= $kategori['id']; ?>">
                                    <label for="kategori_<?= $kategori['id']; ?>" class="checkbox-label">
                                        <strong><?= htmlspecialchars($kategori['name']); ?></strong>
                                        <div class="age-info">Lahir Tahun <?= date("Y") - $kategori['max_age']; ?> – <?= date("Y") - $kategori['min_age']; ?></div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="button" class="btn btn-danger" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
// Data kegiatan dari PHP
const kegiatanData = <?= json_encode($kegiatanData) ?>;
const allData = [...kegiatanData];

function toggleCheckbox(id) {
    const checkbox = document.getElementById(id);
    checkbox.checked = !checkbox.checked;
}

function openModal() {
    document.getElementById('myModal').style.display = 'block';
    document.getElementById('modalTitle').textContent = 'Tambah Data Kegiatan';
    document.getElementById('namaKegiatan').value = '';
    document.getElementById('editId').value = '';
    
    const checkboxes = document.querySelectorAll('input[name="kategori[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
}

function closeModal() {
    document.getElementById('myModal').style.display = 'none';
}

function editData(id) {
    const item = allData.find(data => data.id == id);
    if (item) {
        document.getElementById('myModal').style.display = 'block';
        document.getElementById('modalTitle').textContent = 'Edit Data Kegiatan';
        document.getElementById('namaKegiatan').value = item.nama;
        document.getElementById('editId').value = item.id;
        
        const checkboxes = document.querySelectorAll('input[name="kategori[]"]');
        checkboxes.forEach(checkbox => checkbox.checked = false);
        
        if (item.category_ids && item.category_ids.length > 0) {
            item.category_ids.forEach(categoryId => {
                const checkbox = document.querySelector(`input[name="kategori[]"][value="${categoryId}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        }
    }
}

function deleteData(id) {
    if (confirm('Yakin ingin menghapus data ini?')) {
        window.location.href = `?action=delete&id=${id}`;
    }
}

function searchData() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const tableBody = document.getElementById('tableBody');
    
    if (searchTerm === '') {
        displayData(allData);
    } else {
        const filtered = allData.filter(item => 
            item.nama.toLowerCase().includes(searchTerm)
        );
        displayData(filtered);
    }
}

function displayData(data) {
    const tableBody = document.getElementById('tableBody');
    
    if (data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="4" class="no-data">Tidak ada data yang ditemukan</td></tr>';
        return;
    }
    
    tableBody.innerHTML = data.map((item, index) => {
        let categoryBadges = '';
        if (item.category_names && item.category_names.length > 0) {
            categoryBadges = item.category_names.map(name => 
                `<span class="category-badge">${name}</span>`
            ).join(' ');
        } else {
            categoryBadges = '<span class="text-muted">Belum ada kategori</span>';
        }
        
        return `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${item.nama}</strong></td>
                <td>${categoryBadges}</td>
                <td>
                    <div class="actions">
                        <a href="pendaftaran.php?id=${item.id}" class="btn btn-info">Pendaftaran</a>
                        <a href="detail.php?id=${item.id}" class="btn btn-info">Detail</a>
                        <button class="btn btn-warning" onclick="editData(${item.id})">Edit</button>
                        <button class="btn btn-danger" onclick="deleteData(${item.id})">Hapus</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Validasi form sebelum submit
document.getElementById('kegiatanForm').addEventListener('submit', function(e) {
    const selectedCategories = document.querySelectorAll('input[name="kategori[]"]:checked');
    if (selectedCategories.length === 0) {
        e.preventDefault();
        alert('Pilih minimal satu kategori!');
        return false;
    }
});

// Tutup modal jika klik di luar
window.onclick = function(event) {
    const modal = document.getElementById('myModal');
    if (event.target === modal) {
        closeModal();
    }
}

// Prevent checkbox click from bubbling to parent div
document.querySelectorAll('.checkbox-item input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});
</script>
</body>
</html>