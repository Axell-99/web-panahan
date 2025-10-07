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
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 30px;
            background-color: #f5f5f5;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
            font-size: 32px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            font-weight: bold;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-info {
            background-color: #17a2b8;
            color: white;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .search-box {
            display: flex;
            gap: 10px;
        }

        .search-box input {
            padding: 12px 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 300px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-top: 10px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 20px;
            text-align: left;
            font-size: 16px;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 18px;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .actions {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border: none;
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            max-height: 85vh;
            overflow-y: auto;
        }

        .close {
            color: #999;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
            margin-top: -5px;
        }

        .close:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        .form-group input[type="text"]:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }

        .checkbox-group {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            max-height: 250px;
            overflow-y: auto;
            background-color: #f9f9f9;
        }

        .checkbox-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
            padding: 8px;
            background-color: white;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }

        .checkbox-item:last-child {
            margin-bottom: 0;
        }

        .checkbox-item input[type="checkbox"] {
            margin-right: 10px;
            margin-top: 2px;
            transform: scale(1.1);
        }

        .checkbox-label {
            flex: 1;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
        }

        .checkbox-label strong {
            display: block;
            margin-bottom: 2px;
            color: #007bff;
        }

        .checkbox-label .age-info {
            font-size: 12px;
            color: #666;
            font-style: italic;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .required {
            color: #dc3545;
        }

        .category-badge {
            display: inline-block;
            background-color: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin: 2px;
            white-space: nowrap;
        }

        .text-muted {
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Data Kegiatan</h1>
        <a href="dashboard.php" class="back-link">← Kembali ke Dashboard</a>
    
        <div class="header">
            <button class="btn btn-success" onclick="openModal()">Tambah Data</button>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Cari data..." onkeyup="searchData()">
                <button class="btn btn-primary" onclick="searchData()">Cari</button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kegiatan</th>
                    <th>Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php if (empty($kegiatanData)): ?>
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada data kegiatan</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($kegiatanData as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($item['nama']) ?></td>
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
                                    <a href="pendaftaran.php?id=<?php echo $item ['id']?>" class="btn btn-info">Link Pendaftaran</a>
                                    <a href="detail.php?id=<?php echo $item ['id']?>" class="btn btn-info">Detail</a>
                                    <button class="btn btn-warning" onclick="editData(<?= $item['id'] ?>)">Edit</button>
                                    <button class="btn btn-danger" onclick="deleteData(<?= $item['id'] ?>)">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Modal -->
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2 id="modalTitle">Tambah Data Kegiatan</h2>
                <form id="kegiatanForm" method="POST">
                    <input type="hidden" id="editId" name="editId" value="">
                    
                    <div class="form-group">
                        <label for="namaKegiatan">Nama Kegiatan <span class="required">*</span></label>
                        <input type="text" id="namaKegiatan" name="namaKegiatan" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Pilih Kategori <span class="required">*</span></label>
                        <div class="checkbox-group">
                            <?php foreach ($kategoriList as $kategori): ?>
                                <div class="checkbox-item">
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
const allData = [...kegiatanData]; // Backup untuk pencarian

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
        // Tampilkan semua data
        displayData(allData);
    } else {
        // Filter data
        const filtered = allData.filter(item => 
            item.nama.toLowerCase().includes(searchTerm)
        );
        displayData(filtered);
    }
}

function displayData(data) {
    const tableBody = document.getElementById('tableBody');
    
    if (data.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="4" class="text-center">Tidak ada data yang ditemukan</td></tr>';
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
                <td>${item.nama}</td>
                <td>${categoryBadges}</td>
                <td>
                    <div class="actions">
                        <a href="pendaftaran.php" class="btn btn-info">Link Pendaftaran</a>
                        <a href="#" class="btn btn-info">Detail</a>
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
</script>
</body>
</html>