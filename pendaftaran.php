<?php
// Aktifkan error reporting untuk debugging
include 'check_access.php';
requireLogin();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Mulai session jika belum
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include file koneksi database
try {
    include 'panggil.php';
} catch (Exception $e) {
    die("Error koneksi database: " . $e->getMessage());
}

// Ambil ID kegiatan dari URL atau ambil kegiatan pertama yang tersedia
$kegiatan_id = isset($_GET['kegiatan_id']) ? intval($_GET['kegiatan_id']) : null;

// Jika tidak ada kegiatan_id, ambil kegiatan pertama yang tersedia
if (!$kegiatan_id) {
    try {
        $queryFirstKegiatan = "SELECT id FROM kegiatan WHERE id =".$_GET['id'];
        $resultFirstKegiatan = $conn->query($queryFirstKegiatan);
        if ($resultFirstKegiatan && $resultFirstKegiatan->num_rows > 0) {
            $firstKegiatan = $resultFirstKegiatan->fetch_assoc();
            $kegiatan_id = $firstKegiatan['id'];
        }
    } catch (Exception $e) {
        die("Error mengambil kegiatan: " . $e->getMessage());
    }
}

// Jika masih tidak ada kegiatan
if (!$kegiatan_id) {
    die("Tidak ada kegiatan yang tersedia. Silakan buat kegiatan terlebih dahulu.");
}

// Ambil data kegiatan dan kategorinya
$kegiatanData = [];
try {
    $query = "
        SELECT 
            k.id as kegiatan_id,
            k.nama_kegiatan,
            c.id as category_id,
            c.name as category_name,
            c.min_age,
            c.max_age,
            c.gender
        FROM kegiatan k
        LEFT JOIN kegiatan_kategori kk ON k.id = kk.kegiatan_id
        LEFT JOIN categories c ON kk.category_id = c.id
        WHERE k.id = ? AND c.status = 'active'
        ORDER BY c.min_age ASC, c.name ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $kegiatan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (empty($kegiatanData)) {
            $kegiatanData['kegiatan_id'] = $row['kegiatan_id'];
            $kegiatanData['nama_kegiatan'] = $row['nama_kegiatan'];
            $kegiatanData['kategori'] = [];
        }
        if ($row['category_id']) {
            $kegiatanData['kategori'][] = [
                'id' => $row['category_id'],
                'name' => $row['category_name'],
                'min_age' => $row['min_age'],
                'max_age' => $row['max_age'],
                'gender' => $row['gender']
            ];
        }
    }
    $stmt->close();
    
    // Jika kegiatan tidak ditemukan
    if (empty($kegiatanData)) {
        die("Kegiatan tidak ditemukan.");
    }
    
    // Jika kegiatan tidak memiliki kategori
    if (empty($kegiatanData['kategori'])) {
        die("Kegiatan '{$kegiatanData['nama_kegiatan']}' belum memiliki kategori. Silakan tambahkan kategori terlebih dahulu.");
    }
    
} catch (Exception $e) {
    die("Error mengambil data kegiatan: " . $e->getMessage());
}

// Proses insert data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_peserta = trim($_POST['nama_peserta']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $asal_kota = trim($_POST['asal_kota']);
    $nama_club = trim($_POST['nama_club']);
    $sekolah = trim($_POST['sekolah']);
    $kelas = trim($_POST['kelas']);
    $nomor_hp = trim($_POST['nomor_hp']);
    $category_ids = isset($_POST['category_ids']) ? $_POST['category_ids'] : [];

    // Validasi
    $errors = [];
    
    if (empty($nama_peserta)) {
        $errors[] = "Nama peserta wajib diisi";
    }
    
    if (empty($tanggal_lahir)) {
        $errors[] = "Tanggal lahir wajib diisi";
    }
    
    if (empty($jenis_kelamin)) {
        $errors[] = "Jenis kelamin wajib dipilih";
    }
    
    if (empty($nomor_hp)) {
        $errors[] = "Nomor HP wajib diisi";
    }
    
    if (empty($category_ids)) {
        $errors[] = "Minimal pilih satu kategori";
    }
    
    // Validasi upload file
    $bukti_pembayaran = '';
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES['bukti_pembayaran']['type'];
        $file_size = $_FILES['bukti_pembayaran']['size'];
        $file_tmp = $_FILES['bukti_pembayaran']['tmp_name'];
        $file_name = $_FILES['bukti_pembayaran']['name'];
        
        // Validasi tipe file
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Tipe file tidak diizinkan. Hanya JPG, PNG, GIF, dan PDF yang diperbolehkan";
        }
        
        // Validasi ukuran file
        if ($file_size > $max_size) {
            $errors[] = "Ukuran file terlalu besar. Maksimal 5MB";
        }
        
        if (empty($errors)) {
            // Create uploads directory if not exists
            $upload_dir = 'uploads/pembayaran/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $unique_name = date('YmdHis') . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $unique_name;
            
            // Move uploaded file
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $bukti_pembayaran = $unique_name;
            } else {
                $errors[] = "Gagal mengupload file bukti pembayaran";
            }
        }
    }
    
    // Validasi umur dan gender sesuai kategori yang dipilih
    if (!empty($tanggal_lahir) && !empty($category_ids) && !empty($jenis_kelamin)) {
        $birth_date = new DateTime($tanggal_lahir);
        $current_date = new DateTime();
        $age = $current_date->diff($birth_date)->y;
        
        $invalidCategories = [];
        foreach ($category_ids as $category_id) {
            // Cari kategori yang dipilih
            foreach ($kegiatanData['kategori'] as $kategori) {
                if ($kategori['id'] == $category_id) {
                    // Validasi umur
                    if ($age < $kategori['min_age'] || $age > $kategori['max_age']) {
                        $invalidCategories[] = $kategori['name'] . " (umur {$kategori['min_age']}-{$kategori['max_age']} tahun)";
                    }
                    
                    // Validasi gender
                    if ($kategori['gender'] !== 'Campuran' && $kategori['gender'] !== $jenis_kelamin) {
                        $invalidCategories[] = $kategori['name'] . " (khusus {$kategori['gender']})";
                    }
                    
                    break;
                }
            }
        }
        
        if (!empty($invalidCategories)) {
            $errors[] = "Kategori tidak sesuai: " . implode(', ', $invalidCategories);
        }
    }

    if (empty($errors)) {
        try {
            // Loop untuk setiap kategori yang dipilih
            $successCount = 0;
            $selectedCategoryNames = [];
            
            foreach ($category_ids as $category_id) {
                // Ambil nama kategori untuk pesan sukses
                foreach ($kegiatanData['kategori'] as $kategori) {
                    if ($kategori['id'] == $category_id) {
                        $selectedCategoryNames[] = $kategori['name'];
                        break;
                    }
                }
                
                // Insert dengan kolom bukti_pembayaran
                $stmt = $conn->prepare("INSERT INTO peserta (nama_peserta, tanggal_lahir, jenis_kelamin, asal_kota, nama_club, sekolah, kelas, nomor_hp, bukti_pembayaran, category_id, kegiatan_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param("sssssssssii", 
                    $nama_peserta, 
                    $tanggal_lahir, 
                    $jenis_kelamin, 
                    $asal_kota, 
                    $nama_club, 
                    $sekolah, 
                    $kelas, 
                    $nomor_hp,
                    $bukti_pembayaran,
                    $category_id,
                    $kegiatan_id
                );
                
                if ($stmt->execute()) {
                    $successCount++;
                }
                
                $stmt->close();
            }
            
            if ($successCount > 0) {
                $categoryList = implode(', ', $selectedCategoryNames);
                $_SESSION['success'] = "Data peserta berhasil didaftarkan untuk {$successCount} kategori ({$categoryList}) pada kegiatan " . $kegiatanData['nama_kegiatan'] . "!";
                header("Location: " . $_SERVER['PHP_SELF'] . "?kegiatan_id=" . $kegiatan_id);
                exit;
            } else {
                $errors[] = "Gagal menyimpan data";
            }
            
        } catch (Exception $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
    
    $_SESSION['errors'] = $errors;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pendaftaran Peserta - <?= htmlspecialchars($kegiatanData['nama_kegiatan']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .kegiatan-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .kegiatan-info h3 {
            font-size: 20px;
            margin-bottom: 8px;
        }

        .form-container {
            padding: 40px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 8px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .required {
            color: #e74c3c;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #4facfe;
            background-color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 172, 254, 0.2);
        }

        .form-control:hover {
            border-color: #b8c6db;
        }

        .file-input-container {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px dashed #e1e8ed;
            border-radius: 8px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            cursor: pointer;
            text-align: center;
            font-size: 14px;
            color: #666;
        }

        .file-input:hover {
            border-color: #4facfe;
            background-color: rgba(79, 172, 254, 0.1);
        }

        .file-input input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-info {
            margin-top: 8px;
            font-size: 12px;
            color: #666;
            text-align: center;
        }

        .file-preview {
            margin-top: 10px;
            padding: 10px;
            background: #e8f4f8;
            border-radius: 6px;
            font-size: 14px;
            color: #333;
            display: none;
        }

        .checkbox-group {
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            padding: 15px;
            background-color: #f8f9fa;
            max-height: 300px;
            overflow-y: auto;
        }

        .checkbox-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            padding: 12px;
            background-color: white;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .checkbox-item:hover {
            border-color: #4facfe;
            box-shadow: 0 2px 8px rgba(79, 172, 254, 0.1);
        }

        .checkbox-item:last-child {
            margin-bottom: 0;
        }

        .checkbox-item input[type="checkbox"] {
            margin-right: 12px;
            margin-top: 3px;
            transform: scale(1.2);
            accent-color: #4facfe;
        }

        .checkbox-label {
            flex: 1;
            cursor: pointer;
        }

        .checkbox-label .category-name {
            font-weight: 600;
            color: #333;
            font-size: 15px;
            display: block;
            margin-bottom: 4px;
        }

        .checkbox-label .age-info {
            font-size: 12px;
            color: #666;
            font-style: italic;
            margin-bottom: 2px;
        }

        .checkbox-label .gender-info {
            font-size: 12px;
            color: #007bff;
            font-weight: 500;
        }

        .checkbox-item.disabled {
            opacity: 0.5;
            background-color: #f5f5f5;
        }

        .checkbox-item.disabled input[type="checkbox"] {
            cursor: not-allowed;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 8px;
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .radio-item input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: #4facfe;
        }

        .radio-item label {
            margin-bottom: 0;
            font-weight: normal;
            cursor: pointer;
        }

        .btn-container {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #f1f3f4;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 172, 254, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #4facfe;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .category-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 14px;
            color: #1976d2;
        }

        .age-warning {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .form-container {
                padding: 25px;
            }
            
            .header {
                padding: 20px;
            }
            
            .radio-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .checkbox-group {
                max-height: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Form Pendaftaran Peserta</h1>
            <p>Silakan lengkapi data diri Anda dengan benar</p>
            
            <div class="kegiatan-info">
                <h3><?= htmlspecialchars($kegiatanData['nama_kegiatan']) ?></h3>
                <p>Kategori yang tersedia: <?= count($kegiatanData['kategori']) ?> kategori</p>
            </div>
        </div>

        <div class="form-container">
            <a href="kegiatan.view.php" class="back-link">← Kembali Ke Kegiatan</a>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['success']; ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <li><?= $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php unset($_SESSION['errors']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="kegiatan_id" value="<?= $kegiatan_id ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nama_peserta">Nama Peserta <span class="required">*</span></label>
                        <input type="text" 
                               id="nama_peserta" 
                               name="nama_peserta" 
                               class="form-control" 
                               value="<?= isset($_POST['nama_peserta']) ? htmlspecialchars($_POST['nama_peserta']) : ''; ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_lahir">Tanggal Lahir <span class="required">*</span></label>
                        <input type="date" 
                               id="tanggal_lahir" 
                               name="tanggal_lahir" 
                               class="form-control"
                               value="<?= isset($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : ''; ?>"
                               onchange="updateKategoriOptions()"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Jenis Kelamin <span class="required">*</span></label>
                        <div class="radio-group">
                            <div class="radio-item">
                                <input type="radio" 
                                       id="laki_laki" 
                                       name="jenis_kelamin" 
                                       value="Laki-laki"
                                       onchange="updateKategoriOptions()"
                                       <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'Laki-laki') ? 'checked' : ''; ?>
                                       required>
                                <label for="laki_laki">Laki-laki</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" 
                                       id="perempuan" 
                                       name="jenis_kelamin" 
                                       value="Perempuan"
                                       onchange="updateKategoriOptions()"
                                       <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'Perempuan') ? 'checked' : ''; ?>
                                       required>
                                <label for="perempuan">Perempuan</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nomor_hp">Nomor HP <span class="required">*</span></label>
                        <input type="tel" 
                               id="nomor_hp" 
                               name="nomor_hp" 
                               class="form-control" 
                               placeholder="08xxxxxxxxxx"
                               value="<?= isset($_POST['nomor_hp']) ? htmlspecialchars($_POST['nomor_hp']) : ''; ?>"
                               required>
                    </div>

                    <div class="form-group full-width">
                        <label for="bukti_pembayaran">Bukti Pembayaran</label>
                        <div class="file-input-container">
                            <div class="file-input" onclick="document.getElementById('bukti_pembayaran').click()">
                                <input type="file" 
                                       id="bukti_pembayaran" 
                                       name="bukti_pembayaran" 
                                       accept=".jpg,.jpeg,.png,.gif,.pdf"
                                       onchange="previewFile()">
                                <span id="file-text">📁 Klik untuk memilih file bukti pembayaran</span>
                            </div>
                            <div class="file-info">
                                Format: JPG, PNG, GIF, PDF | Maksimal: 5MB
                            </div>
                            <div id="file-preview" class="file-preview"></div>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Kategori yang Diikuti <span class="required">*</span></label>
                        <div class="checkbox-group" id="kategori-group">
                            <?php 
                            $selectedCategories = isset($_POST['category_ids']) ? $_POST['category_ids'] : [];
                            ?>
                            <?php foreach ($kegiatanData['kategori'] as $kategori): ?>
                                <div class="checkbox-item" 
                                     data-min-age="<?= $kategori['min_age'] ?>"
                                     data-max-age="<?= $kategori['max_age'] ?>"
                                     data-gender="<?= $kategori['gender'] ?>">
                                    <input type="checkbox" 
                                           name="category_ids[]" 
                                           value="<?= $kategori['id'] ?>" 
                                           id="category_<?= $kategori['id'] ?>"
                                           <?= in_array($kategori['id'], $selectedCategories) ? 'checked' : ''; ?>>
                                    <label for="category_<?= $kategori['id'] ?>" class="checkbox-label">
                                        <span class="category-name"><?= htmlspecialchars($kategori['name']) ?></span>
                                        <div class="age-info">Umur: <?= $kategori['min_age'] ?>-<?= $kategori['max_age'] ?> tahun (Lahir <?= date("Y") - $kategori['max_age'] ?> – <?= date("Y") - $kategori['min_age'] ?>)</div>
                                        <div class="gender-info"><?= $kategori['gender'] == 'Campuran' ? 'Putra & Putri' : 'Khusus ' . $kategori['gender'] ?></div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="category-info" id="category-info">
                            <strong>Info:</strong> Anda dapat memilih beberapa kategori sekaligus. Pilih tanggal lahir dan jenis kelamin terlebih dahulu untuk melihat kategori yang sesuai.
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="asal_kota">Asal Kota</label>
                        <input type="text" 
                               id="asal_kota" 
                               name="asal_kota" 
                               class="form-control"
                               value="<?= isset($_POST['asal_kota']) ? htmlspecialchars($_POST['asal_kota']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="nama_club">Nama Club</label>
                        <input type="text" 
                               id="nama_club" 
                               name="nama_club" 
                               class="form-control"
                               value="<?= isset($_POST['nama_club']) ? htmlspecialchars($_POST['nama_club']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="sekolah">Sekolah</label>
                        <input type="text" 
                               id="sekolah" 
                               name="sekolah" 
                               class="form-control"
                               value="<?= isset($_POST['sekolah']) ? htmlspecialchars($_POST['sekolah']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="kelas">Kelas</label>
                        <input type="text" 
                               id="kelas" 
                               name="kelas" 
                               class="form-control" 
                               placeholder="Contoh: XII IPA 1"
                               value="<?= isset($_POST['kelas']) ? htmlspecialchars($_POST['kelas']) : ''; ?>">
                    </div>
                </div>

                <div class="btn-container">
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
                    <button type="submit" class="btn btn-primary">Daftar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function resetForm() {
            if (confirm('Yakin ingin mengosongkan semua field?')) {
                document.querySelector('form').reset();
                document.getElementById('file-text').textContent = '📁 Klik untuk memilih file bukti pembayaran';
                document.getElementById('file-preview').style.display = 'none';
                updateKategoriOptions();
            }
        }

        // Preview file yang dipilih
        function previewFile() {
            const fileInput = document.getElementById('bukti_pembayaran');
            const fileText = document.getElementById('file-text');
            const filePreview = document.getElementById('file-preview');
            
            if (fileInput.files && fileInput.files[0]) {
                const file = fileInput.files[0];
                const fileName = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB
                
                fileText.innerHTML = `✅ ${fileName}`;
                filePreview.innerHTML = `
                    <strong>File dipilih:</strong><br>
                    📄 Nama: ${fileName}<br>
                    📊 Ukuran: ${fileSize} MB<br>
                    📅 Tipe: ${file.type}
                `;
                filePreview.style.display = 'block';
            } else {
                fileText.textContent = '📁 Klik untuk memilih file bukti pembayaran';
                filePreview.style.display = 'none';
            }
        }

        // Auto format nomor HP
        document.getElementById('nomor_hp').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('0')) {
                e.target.value = value;
            } else if (value.startsWith('62')) {
                e.target.value = '0' + value.substring(2);
            }
        });

        // Update kategori options berdasarkan umur dan gender
        function updateKategoriOptions() {
            const tanggalLahir = document.getElementById('tanggal_lahir').value;
            const jenisKelamin = document.querySelector('input[name="jenis_kelamin"]:checked');
            const checkboxItems = document.querySelectorAll('.checkbox-item');
            const categoryInfo = document.getElementById('category-info');
            
            if (!tanggalLahir || !jenisKelamin) {
                // Reset semua opsi
                checkboxItems.forEach(item => {
                    item.classList.remove('disabled');
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    checkbox.disabled = false;
                });
                categoryInfo.innerHTML = '<strong>Info:</strong> Anda dapat memilih beberapa kategori sekaligus. Pilih tanggal lahir dan jenis kelamin terlebih dahulu untuk melihat kategori yang sesuai.';
                return;
            }

            // Hitung umur
            const birthDate = new Date(tanggalLahir);
            const currentDate = new Date();
            const age = Math.floor((currentDate - birthDate) / (365.25 * 24 * 60 * 60 * 1000));

            const selectedGender = jenisKelamin.value;
            let availableCategories = 0;
            let availableCategoryNames = [];

            // Filter checkbox berdasarkan umur dan gender
            checkboxItems.forEach(item => {
                const minAge = parseInt(item.getAttribute('data-min-age'));
                const maxAge = parseInt(item.getAttribute('data-max-age'));
                const categoryGender = item.getAttribute('data-gender');
                const checkbox = item.querySelector('input[type="checkbox"]');
                const categoryName = item.querySelector('.category-name').textContent;

                // Check age and gender compatibility
                const ageMatch = age >= minAge && age <= maxAge;
                const genderMatch = categoryGender === 'Campuran' || categoryGender === selectedGender;

                if (ageMatch && genderMatch) {
                    item.classList.remove('disabled');
                    checkbox.disabled = false;
                    availableCategories++;
                    availableCategoryNames.push(categoryName);
                } else {
                    item.classList.add('disabled');
                    checkbox.disabled = true;
                    checkbox.checked = false; // Uncheck jika tidak sesuai
                }
            });

            // Update info kategori
            let infoHtml = `<strong>Umur Anda: ${age} tahun | Jenis Kelamin: ${selectedGender}</strong><br>`;
            if (availableCategories > 0) {
                infoHtml += `Kategori yang sesuai: ${availableCategories} kategori`;
                if (availableCategories <= 3) {
                    infoHtml += ` (${availableCategoryNames.join(', ')})`;
                }
                if (availableCategories > 1) {
                    infoHtml += '<br><em>Anda dapat memilih beberapa kategori sekaligus.</em>';
                }
            } else {
                infoHtml += '<span style="color: #dc3545;">Tidak ada kategori yang sesuai dengan kriteria Anda</span>';
            }

            categoryInfo.innerHTML = infoHtml;
        }

        // Validasi form sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const checkedCategories = document.querySelectorAll('input[name="category_ids[]"]:checked');
            if (checkedCategories.length === 0) {
                e.preventDefault();
                alert('Pilih minimal satu kategori!');
                return false;
            }
        });

        // Panggil fungsi saat halaman dimuat jika ada data
        document.addEventListener('DOMContentLoaded', function() {
            const tanggalLahir = document.getElementById('tanggal_lahir').value;
            const jenisKelamin = document.querySelector('input[name="jenis_kelamin"]:checked');
            if (tanggalLahir && jenisKelamin) {
                updateKategoriOptions();
            }
        });
    </script>
</body>
</html>