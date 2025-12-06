<?php
// PASTIKAN SESSION CHECK ADA DI PALING ATAS
include 'panggil.php';
include 'check_access.php';
requireAdmin();

// Redirect ke login jika belum login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: index.php');
    exit;
}

// Ambil data user dari session
$username = $_SESSION['username'] ?? 'User';
$name = $_SESSION['name'] ?? $username;
$role = $_SESSION['role'] ?? 'user';

// FUNGSI UNTUK KATEGORI
function getKategoriFromRanking($ranking, $totalPeserta) {
    if ($totalPeserta <= 1) {
        return ['kategori' => 'A', 'label' => 'Sangat Baik', 'color' => 'success', 'icon' => '🏆'];
    }
    
    $persentase = ($ranking / $totalPeserta) * 100;
    
    if ($ranking <= 3 && $persentase <= 30) {
        return ['kategori' => 'A', 'label' => 'Sangat Baik', 'color' => 'success', 'icon' => '🏆'];
    }
    elseif ($ranking <= 10 && $persentase <= 40) {
        return ['kategori' => 'B', 'label' => 'Baik', 'color' => 'primary', 'icon' => '🥈'];
    }
    elseif ($persentase <= 60) {
        return ['kategori' => 'C', 'label' => 'Cukup', 'color' => 'info', 'icon' => '🥉'];
    }
    elseif ($persentase <= 80) {
        return ['kategori' => 'D', 'label' => 'Perlu Latihan', 'color' => 'warning', 'icon' => '📊'];
    }
    else {
        return ['kategori' => 'E', 'label' => 'Pemula', 'color' => 'secondary', 'icon' => '📈'];
    }
}

function getKategoriDominan($rankings) {
    if (empty($rankings)) {
        return ['kategori' => 'E', 'label' => 'Pemula (Belum Pernah Bertanding)', 'color' => 'secondary', 'icon' => '📈'];
    }
    
    $kategoriCount = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];
    
    foreach ($rankings as $rank) {
        $kat = getKategoriFromRanking($rank['ranking'], $rank['total_peserta']);
        $kategoriCount[$kat['kategori']]++;
    }
    
    arsort($kategoriCount);
    $dominan = key($kategoriCount);
    
    if ($dominan == 'A') {
        return ['kategori' => 'A', 'label' => 'Sangat Baik', 'color' => 'success', 'icon' => '🏆'];
    } elseif ($dominan == 'B') {
        return ['kategori' => 'B', 'label' => 'Baik', 'color' => 'primary', 'icon' => '🥈'];
    } elseif ($dominan == 'C') {
        return ['kategori' => 'C', 'label' => 'Cukup', 'color' => 'info', 'icon' => '🥉'];
    } elseif ($dominan == 'D') {
        return ['kategori' => 'D', 'label' => 'Perlu Latihan', 'color' => 'warning', 'icon' => '📊'];
    } else {
        return ['kategori' => 'E', 'label' => 'Pemula', 'color' => 'secondary', 'icon' => '📈'];
    }
}

// 1. HITUNG JUMLAH ATLET DAN AMBIL DAFTAR NAMA
$queryTotalAtlet = "SELECT DISTINCT nama_peserta FROM peserta ORDER BY nama_peserta ASC";
$resultTotalAtlet = $conn->query($queryTotalAtlet);
$daftarAtlet = [];
while ($row = $resultTotalAtlet->fetch_assoc()) {
    $daftarAtlet[] = $row['nama_peserta'];
}
$totalAtlet = count($daftarAtlet);

// 2. HITUNG JUMLAH CLUB DAN AMBIL DAFTAR NAMA CLUB
$queryTotalClub = "SELECT DISTINCT nama_club FROM peserta WHERE nama_club IS NOT NULL AND nama_club != '' ORDER BY nama_club ASC";
$resultTotalClub = $conn->query($queryTotalClub);
$daftarClub = [];
while ($row = $resultTotalClub->fetch_assoc()) {
    $daftarClub[] = $row['nama_club'];
}
$totalClub = count($daftarClub);

// 3. AMBIL DATA ATLET BERPRESTASI (Kategori A & B)
$queryAtlet = "SELECT 
                MIN(p.id) as id,
                p.nama_peserta,
                p.jenis_kelamin,
                p.nama_club
              FROM peserta p
              GROUP BY p.nama_peserta, p.jenis_kelamin, p.nama_club
              ORDER BY p.nama_peserta ASC";

$resultAtlet = $conn->query($queryAtlet);
$atletBerprestasi = [];
$atletKurangPrestasi = [];

while ($peserta = $resultAtlet->fetch_assoc()) {
    $queryRanking = "
        SELECT 
            sb.kegiatan_id,
            sb.category_id,
            sb.id as scoreboard_id
        FROM score_boards sb
        WHERE EXISTS (
            SELECT 1 FROM score s 
            INNER JOIN peserta p2 ON s.peserta_id = p2.id
            WHERE s.score_board_id = sb.id 
            AND p2.nama_peserta = ?
        )
        ORDER BY sb.created DESC
    ";
    
    $stmtRank = $conn->prepare($queryRanking);
    $stmtRank->bind_param("s", $peserta['nama_peserta']);
    $stmtRank->execute();
    $resultRank = $stmtRank->get_result();
    
    $rankings = [];
    $juara1 = 0;
    $juara2 = 0;
    $juara3 = 0;
    
    while ($turnamen = $resultRank->fetch_assoc()) {
        $queryPesertaTurnamen = "
            SELECT DISTINCT s.peserta_id
            FROM score s
            WHERE s.kegiatan_id = ? 
            AND s.category_id = ? 
            AND s.score_board_id = ?
        ";
        
        $stmtPT = $conn->prepare($queryPesertaTurnamen);
        $stmtPT->bind_param("iii", $turnamen['kegiatan_id'], $turnamen['category_id'], $turnamen['scoreboard_id']);
        $stmtPT->execute();
        $resultPT = $stmtPT->get_result();
        
        $pesertaScores = [];
        while ($pt = $resultPT->fetch_assoc()) {
            $queryScore = "
                SELECT score 
                FROM score 
                WHERE kegiatan_id = ? 
                AND category_id = ? 
                AND score_board_id = ? 
                AND peserta_id = ?
            ";
            
            $stmtScore = $conn->prepare($queryScore);
            $stmtScore->bind_param("iiii", $turnamen['kegiatan_id'], $turnamen['category_id'], $turnamen['scoreboard_id'], $pt['peserta_id']);
            $stmtScore->execute();
            $resultScore = $stmtScore->get_result();
            
            $totalScore = 0;
            $totalX = 0;
            
            while ($scoreRow = $resultScore->fetch_assoc()) {
                $scoreValue = strtolower($scoreRow['score']);
                if ($scoreValue == 'x') {
                    $totalScore += 10;
                    $totalX++;
                } else if ($scoreValue != 'm') {
                    $totalScore += intval($scoreValue);
                }
            }
            
            $pesertaScores[] = [
                'peserta_id' => $pt['peserta_id'],
                'total_score' => $totalScore,
                'total_x' => $totalX
            ];
            
            $stmtScore->close();
        }
        
        usort($pesertaScores, function($a, $b) {
            if ($b['total_score'] != $a['total_score']) {
                return $b['total_score'] - $a['total_score'];
            }
            return $b['total_x'] - $a['total_x'];
        });
        
        $totalPesertaTurnamen = count($pesertaScores);
        $ranking = 0;
        
        foreach ($pesertaScores as $index => $ps) {
            $queryCheckName = "SELECT nama_peserta FROM peserta WHERE id = ?";
            $stmtCheckName = $conn->prepare($queryCheckName);
            $stmtCheckName->bind_param("i", $ps['peserta_id']);
            $stmtCheckName->execute();
            $resultCheckName = $stmtCheckName->get_result();
            $checkName = $resultCheckName->fetch_assoc();
            $stmtCheckName->close();
            
            if ($checkName && $checkName['nama_peserta'] == $peserta['nama_peserta']) {
                $ranking = $index + 1;
                break;
            }
        }
        
        if ($ranking > 0) {
            $rankings[] = [
                'ranking' => $ranking,
                'total_peserta' => $totalPesertaTurnamen
            ];
            
            if ($ranking == 1) $juara1++;
            if ($ranking == 2) $juara2++;
            if ($ranking == 3) $juara3++;
        }
        
        $stmtPT->close();
    }
    
    $stmtRank->close();
    
    $kategoriDominan = getKategoriDominan($rankings);
    $totalTurnamen = count($rankings);
    $avgRanking = $totalTurnamen > 0 ? round(array_sum(array_column($rankings, 'ranking')) / $totalTurnamen, 2) : 0;
    
    $atletData = [
        'nama' => $peserta['nama_peserta'],
        'gender' => $peserta['jenis_kelamin'],
        'club' => $peserta['nama_club'],
        'kategori' => $kategoriDominan['kategori'],
        'kategori_label' => $kategoriDominan['label'],
        'kategori_icon' => $kategoriDominan['icon'],
        'kategori_color' => $kategoriDominan['color'],
        'total_turnamen' => $totalTurnamen,
        'avg_ranking' => $avgRanking,
        'juara1' => $juara1,
        'juara2' => $juara2,
        'juara3' => $juara3
    ];
    
    // Pisahkan berdasarkan kategori
    if (in_array($kategoriDominan['kategori'], ['A', 'B'])) {
        $atletBerprestasi[] = $atletData;
    } else if (in_array($kategoriDominan['kategori'], ['D', 'E'])) {
        $atletKurangPrestasi[] = $atletData;
    }
}

// Sort atlet berprestasi berdasarkan juara 1 terbanyak
usort($atletBerprestasi, function($a, $b) {
    if ($b['juara1'] != $a['juara1']) {
        return $b['juara1'] - $a['juara1'];
    }
    if ($b['juara2'] != $a['juara2']) {
        return $b['juara2'] - $a['juara2'];
    }
    return $b['juara3'] - $a['juara3'];
});

// Sort atlet kurang prestasi berdasarkan avg ranking terburuk
usort($atletKurangPrestasi, function($a, $b) {
    if ($a['kategori'] != $b['kategori']) {
        $kategoriOrder = ['E' => 1, 'D' => 2];
        return $kategoriOrder[$a['kategori']] - $kategoriOrder[$b['kategori']];
    }
    return $b['avg_ranking'] - $a['avg_ranking'];
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Turnamen Panahan</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .sidebar {
            width: 280px;
            background: white;
            display: flex;
            flex-direction: column;
        }

        .logo {
            padding: 30px 20px;
            background: #ff6b6b;
            color: white;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }

        .menu-section {
            padding: 20px;
            flex: 1;
        }

        .menu-item {
            margin-bottom: 10px;
        }

        .menu-item a {
            display: block;
            padding: 15px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .menu-item a:hover {
            background: #2980b9;
            transform: translateX(5px);
        }

        .dropdown-btn {
            width: 100%;
            padding: 15px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            text-align: left;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .dropdown-btn:hover {
            background: #2980b9;
        }

        .dropdown-content {
            display: none;
            background: #ecf0f1;
            border-radius: 8px;
            margin-top: 5px;
            padding: 5px;
        }

        .dropdown-content.active {
            display: block;
        }

        .dropdown-content a {
            display: block;
            padding: 10px 20px;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 5px;
            margin: 2px 0;
        }

        .dropdown-content a:hover {
            background: #3498db;
            color: white;
        }

        .logout-section {
            padding: 20px;
            border-top: 1px solid #ddd;
        }

        .logout-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: #e74c3c;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 24px;
            color: #2c3e50;
        }

        .header p {
            color: #7f8c8d;
            font-size: 14px;
        }

        .username-container {
            background: #3498db;
            padding: 10px 20px;
            border-radius: 25px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-logo {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: 2px solid white;
        }

        .dashboard-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .welcome-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .welcome-card h2 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .welcome-card p {
            color: #7f8c8d;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border-left: 5px solid;
            display: flex;
            flex-direction: column;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .stat-card.blue { border-color: #3498db; }
        .stat-card.green { border-color: #2ecc71; }

        .stat-card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-icon.blue { background: #e3f2fd; color: #3498db; }
        .stat-icon.green { background: #e8f5e9; color: #2ecc71; }

        .stat-info h3 {
            color: #7f8c8d;
            font-size: 14px;
            font-weight: normal;
            margin-bottom: 5px;
        }

        .stat-info .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
        }

        .stat-list {
            flex: 1;
            overflow-y: auto;
            max-height: 300px;
        }

        .stat-list-item {
            padding: 10px 15px;
            margin-bottom: 5px;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .stat-list-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .stat-list-item .number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #3498db;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            flex-shrink: 0;
        }

        .stat-list-item .name {
            flex: 1;
            color: #2c3e50;
            font-size: 14px;
        }

        .stat-list::-webkit-scrollbar {
            width: 6px;
        }

        .stat-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .stat-list::-webkit-scrollbar-thumb {
            background: #bdc3c7;
            border-radius: 10px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            animation: fadeIn 0.3s;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 900px;
            max-height: 90vh;
            width: 90%;
            overflow-y: auto;
            animation: slideUp 0.3s;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .modal-header {
            padding: 25px 30px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border-radius: 20px 20px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 24px;
            margin: 0;
        }

        .modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
        }

        .chart-container {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .summary-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .summary-card h4 {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .summary-card .value {
            font-size: 32px;
            font-weight: bold;
        }

        .member-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }

        .member-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #3498db;
            transition: all 0.3s;
            cursor: pointer;
        }

        .member-card:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .member-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .member-gender {
            color: #7f8c8d;
            font-size: 13px;
        }

        .tournament-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid #3498db;
        }

        .tournament-name {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .tournament-details {
            font-size: 13px;
            color: #7f8c8d;
        }

        .rank-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 5px;
        }

        .rank-gold { background: linear-gradient(135deg, #f1c40f, #f39c12); color: white; }
        .rank-silver { background: linear-gradient(135deg, #bdc3c7, #95a5a6); color: white; }
        .rank-bronze { background: linear-gradient(135deg, #e67e22, #d35400); color: white; }
        .rank-normal { background: #3498db; color: white; }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-content::-webkit-scrollbar {
            width: 8px;
        }

        .modal-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .modal-content::-webkit-scrollbar-thumb {
            background: #bdc3c7;
            border-radius: 10px;
        }

        .hamburger {
            display: none;
        }

        @media (max-width: 1400px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .hamburger {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
                background: white;
                border: none;
                padding: 10px;
                border-radius: 5px;
                cursor: pointer;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            }

            .hamburger span {
                display: block;
                width: 25px;
                height: 3px;
                background: #333;
                margin: 5px 0;
            }

            .sidebar {
                position: fixed;
                left: -280px;
                top: 0;
                bottom: 0;
                z-index: 1000;
                transition: left 0.3s;
            }

            .sidebar.active {
                left: 0;
            }

            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }

            .overlay.active {
                display: block;
            }

            .header {
                padding-top: 70px;
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <button class="hamburger" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="overlay" onclick="toggleMenu()"></div>

    <div class="sidebar" id="sidebar">
        <div class="logo">
            🏹 TURNAMEN PANAHAN
        </div>
        
        <div class="menu-section">
            <div class="menu-item">
                <a href="dashboard.php">🏠 Dashboard</a>
            </div>

            <div class="menu-item">
                <button class="dropdown-btn" onclick="toggleDropdown(this)">
                    📊 Master Data ▾
                </button>
                <div class="dropdown-content">
                    <a href="users.php">👥 Users</a>
                    <a href="categori.view.php">📋 Kategori</a>
                </div>
            </div>

            <div class="menu-item">
                <a href="kegiatan.view.php">📅 Kegiatan</a>
            </div>

            <div class="menu-item">
                <a href="peserta.view.php">👨‍👩‍👧‍👦 Peserta</a>
            </div>

            <div class="menu-item">
                <a href="statistik.php">📊 Statistik</a>
            </div>
        </div>

        <div class="logout-section">
            <a href="logout.php" class="logout-btn" onclick="return confirm('Yakin ingin logout?')">
                🚪 Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h1>Dashboard <?php echo ucfirst($role); ?></h1>
                <p>Sistem Pendaftaran Turnamen Panahan</p>
            </div>
            <div class="username-container">
                <span><?php echo htmlspecialchars($name); ?></span>
                <img src="angzay.png" alt="Profile" class="profile-logo" onerror="this.style.display='none';">
            </div>
        </div>
        
        <div class="dashboard-content">
            <div class="welcome-card">
                <h2>🎯 Selamat Datang, <?php echo htmlspecialchars($name); ?>!</h2>
                <p>Anda Sekarang Berada di Dashboard Turnamen Panahan</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-card-header">
                        <div class="stat-icon blue">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Atlet Terdaftar</h3>
                            <div class="stat-number"><?php echo $totalAtlet; ?></div>
                        </div>
                    </div>
                    <div class="stat-list">
                        <?php if (empty($daftarAtlet)): ?>
                            <div style="text-align: center; padding: 20px; color: #7f8c8d;">
                                <i class="fas fa-user-slash" style="font-size: 32px; opacity: 0.3; margin-bottom: 10px;"></i>
                                <p style="font-size: 13px;">Belum ada atlet terdaftar</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($daftarAtlet as $index => $namaAtlet): ?>
                                <div class="stat-list-item" onclick="showAthleteStats('<?php echo htmlspecialchars($namaAtlet, ENT_QUOTES); ?>')">
                                    <div class="number"><?php echo $index + 1; ?></div>
                                    <div class="name">
                                        <?php echo htmlspecialchars($namaAtlet); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-card-header">
                        <div class="stat-icon green">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Club Terdaftar</h3>
                            <div class="stat-number"><?php echo $totalClub; ?></div>
                        </div>
                    </div>
                    <div class="stat-list">
                        <?php if (empty($daftarClub)): ?>
                            <div style="text-align: center; padding: 20px; color: #7f8c8d;">
                                <i class="fas fa-building" style="font-size: 32px; opacity: 0.3; margin-bottom: 10px;"></i>
                                <p style="font-size: 13px;">Belum ada club terdaftar</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($daftarClub as $index => $namaClub): ?>
                                <div class="stat-list-item" onclick="showClubMembers('<?php echo htmlspecialchars($namaClub, ENT_QUOTES); ?>')">
                                    <div class="number" style="background: #2ecc71;"><?php echo $index + 1; ?></div>
                                    <div class="name">
                                        <?php echo htmlspecialchars($namaClub); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stat-card blue">
                    <div class="stat-card-header">
                        <div class="stat-icon blue">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Atlet Berprestasi</h3>
                            <div class="stat-number"><?php echo count($atletBerprestasi); ?></div>
                        </div>
                    </div>
                    <div class="stat-list">
                        <?php if (empty($atletBerprestasi)): ?>
                            <div style="text-align: center; padding: 20px; color: #7f8c8d;">
                                <i class="fas fa-medal" style="font-size: 32px; opacity: 0.3; margin-bottom: 10px;"></i>
                                <p style="font-size: 13px;">Belum ada data</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($atletBerprestasi as $index => $atlet): ?>
                                <div class="stat-list-item" onclick="showAthleteStats('<?php echo htmlspecialchars($atlet['nama'], ENT_QUOTES); ?>')">
                                    <div class="number"><?php echo $index + 1; ?></div>
                                    <div class="name">
                                        <?php echo htmlspecialchars($atlet['nama']); ?> 
                                        <small style="color: #27ae60;">🏆 <?php echo $atlet['juara1']; ?>x</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-card-header">
                        <div class="stat-icon green">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Perlu Peningkatan</h3>
                            <div class="stat-number"><?php echo count($atletKurangPrestasi); ?></div>
                        </div>
                    </div>
                    <div class="stat-list">
                        <?php if (empty($atletKurangPrestasi)): ?>
                            <div style="text-align: center; padding: 20px; color: #7f8c8d;">
                                <i class="fas fa-chart-bar" style="font-size: 32px; opacity: 0.3; margin-bottom: 10px;"></i>
                                <p style="font-size: 13px;">Semua atlet berprestasi!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($atletKurangPrestasi as $index => $atlet): ?>
                                <div class="stat-list-item" onclick="showAthleteStats('<?php echo htmlspecialchars($atlet['nama'], ENT_QUOTES); ?>')">
                                    <div class="number" style="background: #e67e22;"><?php echo $index + 1; ?></div>
                                    <div class="name">
                                        <?php echo htmlspecialchars($atlet['nama']); ?>
                                        <small style="color: #e67e22;">📊 Kat. <?php echo $atlet['kategori']; ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="athleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="athleteModalTitle">Statistik Atlet</h2>
                <button class="modal-close" onclick="closeAthleteModal()">&times;</button>
            </div>
            <div class="modal-body" id="athleteModalBody">
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #3498db;"></i>
                    <p style="margin-top: 15px; color: #7f8c8d;">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>

    <div id="clubModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="clubModalTitle">Anggota Club</h2>
                <button class="modal-close" onclick="closeClubModal()">&times;</button>
            </div>
            <div class="modal-body" id="clubModalBody">
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #2ecc71;"></i>
                    <p style="margin-top: 15px; color: #7f8c8d;">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleDropdown(button) {
            const content = button.nextElementSibling;
            const allDropdowns = document.querySelectorAll('.dropdown-content');
            
            allDropdowns.forEach(d => {
                if (d !== content) {
                    d.classList.remove('active');
                }
            });
            
            content.classList.toggle('active');
            
            if (content.classList.contains('active')) {
                button.textContent = button.textContent.replace('▾', '▴');
            } else {
                button.textContent = button.textContent.replace('▴', '▾');
            }
        }

        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.overlay');
            
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        if (window.innerWidth <= 768) {
            document.querySelectorAll('.menu-item a, .dropdown-content a').forEach(link => {
                link.addEventListener('click', toggleMenu);
            });
        }

        function showAthleteStats(athleteName) {
            const modal = document.getElementById('athleteModal');
            const modalTitle = document.getElementById('athleteModalTitle');
            const modalBody = document.getElementById('athleteModalBody');
            
            modalTitle.textContent = 'Statistik: ' + athleteName;
            modal.classList.add('active');
            
            fetch('get_athlete_stats.php?name=' + encodeURIComponent(athleteName))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayAthleteStats(data.data, athleteName);
                    } else {
                        modalBody.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-exclamation-circle" style="font-size: 48px; color: #e74c3c;"></i><p style="margin-top: 15px;">' + data.message + '</p></div>';
                    }
                })
                .catch(error => {
                    modalBody.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #f39c12;"></i><p style="margin-top: 15px;">Terjadi kesalahan saat memuat data</p></div>';
                });
        }

        function displayAthleteStats(data, athleteName) {
            const modalBody = document.getElementById('athleteModalBody');
            
            let html = '<div class="stats-summary">';
            html += '<div class="summary-card"><h4>Total Turnamen</h4><div class="value">' + data.total_turnamen + '</div></div>';
            html += '<div class="summary-card"><h4>Juara 1</h4><div class="value">🥇 ' + data.juara1 + '</div></div>';
            html += '<div class="summary-card"><h4>Juara 2</h4><div class="value">🥈 ' + data.juara2 + '</div></div>';
            html += '<div class="summary-card"><h4>Juara 3</h4><div class="value">🥉 ' + data.juara3 + '</div></div>';
            html += '</div>';
            
            if (data.tournaments && data.tournaments.length > 0) {
                // html += '<div class="chart-container">';
                // html += '<h3 style="margin-bottom: 15px; color: #2c3e50;">📊 Grafik Performa</h3>';
                // html += '<canvas id="athleteChart"></canvas>';
                // html += '</div>';
                
                html += '<div style="margin-top: 25px;">';
                html += '<h3 style="margin-bottom: 15px; color: #2c3e50;">🏆 Riwayat Turnamen</h3>';
                html += '<div style="max-height: 300px; overflow-y: auto;">';
                
                data.tournaments.forEach((tournament, index) => {
                    let badgeClass = 'rank-normal';
                    if (tournament.ranking == 1) badgeClass = 'rank-gold';
                    else if (tournament.ranking == 2) badgeClass = 'rank-silver';
                    else if (tournament.ranking == 3) badgeClass = 'rank-bronze';
                    
                    html += '<div class="tournament-item">';
                    html += '<div class="tournament-name">';
                    html += '<span class="rank-badge ' + badgeClass + '">#' + tournament.ranking + '</span>';
                    html += tournament.nama_kegiatan;
                    html += '</div>';
                    html += '<div class="tournament-details">';
                    html += '<span>📅 ' + tournament.tanggal + '</span> • ';
                    html += '<span>📋 ' + tournament.category + '</span> • ';
                    html += '<span>👥 ' + tournament.total_peserta + ' peserta</span>';
                    html += '</div>';
                    html += '</div>';
                });
                
                html += '</div></div>';
            } else {
                html += '<div style="text-align: center; padding: 40px;"><i class="fas fa-trophy" style="font-size: 48px; opacity: 0.3;"></i><p style="margin-top: 15px; color: #7f8c8d;">Belum pernah mengikuti turnamen</p></div>';
            }
            
            modalBody.innerHTML = html;
            
            if (data.tournaments && data.tournaments.length > 0) {
                createAthleteChart(data.tournaments);
            }
        }

        function createAthleteChart(tournaments) {
            const ctx = document.getElementById('athleteChart');
            if (!ctx) return;
            
            const labels = tournaments.map(t => t.nama_kegiatan.substring(0, 20) + '...');
            const rankings = tournaments.map(t => t.ranking);
            const colors = rankings.map(r => {
                if (r == 1) return '#f1c40f';
                if (r == 2) return '#95a5a6';
                if (r == 3) return '#e67e22';
                return '#3498db';
            });
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Ranking',
                        data: rankings,
                        backgroundColor: colors,
                        borderColor: colors.map(c => c),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            reverse: true,
                            title: {
                                display: true,
                                text: 'Ranking (1 = Terbaik)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Ranking: #' + context.parsed.y;
                                }
                            }
                        }
                    }
                }
            });
        }

        function showClubMembers(clubName) {
            if (!clubName || clubName === 'No Club') {
                return;
            }
            
            const modal = document.getElementById('clubModal');
            const modalTitle = document.getElementById('clubModalTitle');
            const modalBody = document.getElementById('clubModalBody');
            
            modalTitle.textContent = 'Anggota Club: ' + clubName;
            modal.classList.add('active');
            
            fetch('get_club_members.php?club=' + encodeURIComponent(clubName))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayClubMembers(data.members);
                    } else {
                        modalBody.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-exclamation-circle" style="font-size: 48px; color: #e74c3c;"></i><p style="margin-top: 15px;">' + data.message + '</p></div>';
                    }
                })
                .catch(error => {
                    modalBody.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #f39c12;"></i><p style="margin-top: 15px;">Terjadi kesalahan saat memuat data</p></div>';
                });
        }

        function displayClubMembers(members) {
            const modalBody = document.getElementById('clubModalBody');
            
            if (members.length === 0) {
                modalBody.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-users" style="font-size: 48px; opacity: 0.3;"></i><p style="margin-top: 15px; color: #7f8c8d;">Tidak ada anggota dalam club ini</p></div>';
                return;
            }
            
            let html = '<div style="margin-bottom: 20px;">';
            html += '<div class="summary-card" style="display: inline-block; margin-right: 15px;">';
            html += '<h4>Total Anggota</h4><div class="value">' + members.length + '</div>';
            html += '</div>';
            html += '</div>';
            
            html += '<div class="member-list">';
            members.forEach((member, index) => {
                html += '<div class="member-card" onclick="showAthleteStats(\'' + member.nama_peserta.replace(/'/g, "\\'") + '\')">';
                html += '<div class="member-name">' + (index + 1) + '. ' + member.nama_peserta + '</div>';
                html += '<div class="member-gender">⚤ ' + member.jenis_kelamin + '</div>';
                html += '</div>';
            });
            html += '</div>';
            
            modalBody.innerHTML = html;
        }

        function closeAthleteModal() {
            document.getElementById('athleteModal').classList.remove('active');
        }

        function closeClubModal() {
            document.getElementById('clubModal').classList.remove('active');
        }

        window.onclick = function(event) {
            const athleteModal = document.getElementById('athleteModal');
            const clubModal = document.getElementById('clubModal');
            
            if (event.target === athleteModal) {
                closeAthleteModal();
            }
            if (event.target === clubModal) {
                closeClubModal();
            }
        }
    </script>
</body>
</html>