<?php
include 'panggil.php';
include 'check_access.php';
requireAdmin();

// Fungsi untuk menentukan kategori berdasarkan ranking
function getKategoriFromRanking($ranking) {
    if ($ranking >= 1 && $ranking <= 3) {
        return ['kategori' => 'A', 'label' => 'Sangat Baik', 'color' => 'success', 'icon' => '🏆'];
    } elseif ($ranking >= 4 && $ranking <= 10) {
        return ['kategori' => 'B', 'label' => 'Baik', 'color' => 'primary', 'icon' => '🥈'];
    } elseif ($ranking >= 11 && $ranking <= 20) {
        return ['kategori' => 'C', 'label' => 'Cukup', 'color' => 'info', 'icon' => '🥉'];
    } elseif ($ranking >= 21 && $ranking <= 32) {
        return ['kategori' => 'D', 'label' => 'Perlu Latihan', 'color' => 'warning', 'icon' => '📊'];
    } else {
        return ['kategori' => 'E', 'label' => 'Pemula', 'color' => 'secondary', 'icon' => '📈'];
    }
}

// Fungsi untuk menghitung kategori dominan
function getKategoriDominan($rankings) {
    if (empty($rankings)) {
        return ['kategori' => 'E', 'label' => 'Pemula (Belum Pernah Bertanding)', 'color' => 'secondary', 'icon' => '📈'];
    }
    
    $kategoriCount = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];
    
    foreach ($rankings as $rank) {
        $kat = getKategoriFromRanking($rank['ranking']);
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

// Fungsi untuk mendapatkan statistik bracket/aduan peserta
function getBracketStatistics($conn, $peserta_nama) {
    $stats = [
        'total_bracket' => 0,
        'bracket_champion' => 0,
        'bracket_runner_up' => 0,
        'bracket_third_place' => 0,
        'bracket_matches_won' => 0,
        'bracket_matches_lost' => 0,
        'bracket_history' => []
    ];
    
    // Hitung jumlah sebagai champion - cari berdasarkan nama peserta
    $queryChampion = "SELECT COUNT(*) as total FROM bracket_champions bc
                      INNER JOIN peserta p ON bc.champion_id = p.id
                      WHERE p.nama_peserta = ?";
    $stmtChampion = $conn->prepare($queryChampion);
    $stmtChampion->bind_param("s", $peserta_nama);
    $stmtChampion->execute();
    $resultChampion = $stmtChampion->get_result();
    if ($row = $resultChampion->fetch_assoc()) {
        $stats['bracket_champion'] = $row['total'];
    }
    $stmtChampion->close();
    
    // Hitung jumlah sebagai runner up
    $queryRunnerUp = "SELECT COUNT(*) as total FROM bracket_champions bc
                      INNER JOIN peserta p ON bc.runner_up_id = p.id
                      WHERE p.nama_peserta = ?";
    $stmtRunnerUp = $conn->prepare($queryRunnerUp);
    $stmtRunnerUp->bind_param("s", $peserta_nama);
    $stmtRunnerUp->execute();
    $resultRunnerUp = $stmtRunnerUp->get_result();
    if ($row = $resultRunnerUp->fetch_assoc()) {
        $stats['bracket_runner_up'] = $row['total'];
    }
    $stmtRunnerUp->close();
    
    // Hitung jumlah sebagai third place
    $queryThird = "SELECT COUNT(*) as total FROM bracket_champions bc
                   INNER JOIN peserta p ON bc.third_place_id = p.id
                   WHERE p.nama_peserta = ?";
    $stmtThird = $conn->prepare($queryThird);
    $stmtThird->bind_param("s", $peserta_nama);
    $stmtThird->execute();
    $resultThird = $stmtThird->get_result();
    if ($row = $resultThird->fetch_assoc()) {
        $stats['bracket_third_place'] = $row['total'];
    }
    $stmtThird->close();
    
    // Hitung matches won
    $queryWon = "SELECT COUNT(*) as total FROM bracket_matches bm
                 INNER JOIN peserta p ON bm.winner_id = p.id
                 WHERE p.nama_peserta = ?";
    $stmtWon = $conn->prepare($queryWon);
    $stmtWon->bind_param("s", $peserta_nama);
    $stmtWon->execute();
    $resultWon = $stmtWon->get_result();
    if ($row = $resultWon->fetch_assoc()) {
        $stats['bracket_matches_won'] = $row['total'];
    }
    $stmtWon->close();
    
    // Hitung matches lost
    $queryLost = "SELECT COUNT(*) as total FROM bracket_matches bm
                  INNER JOIN peserta p ON bm.loser_id = p.id
                  WHERE p.nama_peserta = ?";
    $stmtLost = $conn->prepare($queryLost);
    $stmtLost->bind_param("s", $peserta_nama);
    $stmtLost->execute();
    $resultLost = $stmtLost->get_result();
    if ($row = $resultLost->fetch_assoc()) {
        $stats['bracket_matches_lost'] = $row['total'];
    }
    $stmtLost->close();
    
    // Ambil riwayat bracket
    $queryHistory = "
        SELECT DISTINCT
            bc.kegiatan_id,
            bc.category_id,
            bc.scoreboard_id,
            k.nama_kegiatan,
            c.name as category_name,
            bc.champion_id,
            bc.runner_up_id,
            bc.third_place_id,
            bc.bracket_size,
            bc.created_at,
            CASE 
                WHEN p1.nama_peserta = ? THEN 'champion'
                WHEN p2.nama_peserta = ? THEN 'runner_up'
                WHEN p3.nama_peserta = ? THEN 'third_place'
                ELSE 'participant'
            END as position
        FROM bracket_champions bc
        INNER JOIN kegiatan k ON bc.kegiatan_id = k.id
        INNER JOIN categories c ON bc.category_id = c.id
        LEFT JOIN peserta p1 ON bc.champion_id = p1.id
        LEFT JOIN peserta p2 ON bc.runner_up_id = p2.id
        LEFT JOIN peserta p3 ON bc.third_place_id = p3.id
        WHERE p1.nama_peserta = ? OR p2.nama_peserta = ? OR p3.nama_peserta = ?
        ORDER BY bc.created_at DESC
    ";
    
    $stmtHistory = $conn->prepare($queryHistory);
    $stmtHistory->bind_param("ssssss", $peserta_nama, $peserta_nama, $peserta_nama, $peserta_nama, $peserta_nama, $peserta_nama);
    $stmtHistory->execute();
    $resultHistory = $stmtHistory->get_result();
    
    while ($row = $resultHistory->fetch_assoc()) {
        $stats['bracket_history'][] = $row;
    }
    $stmtHistory->close();
    
    $stats['total_bracket'] = count($stats['bracket_history']);
    
    return $stats;
}

// Handle export to Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=statistik_peserta_" . date('Y-m-d') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    $query = "SELECT 
                MIN(p.id) as id,
                p.nama_peserta,
                p.jenis_kelamin,
                p.asal_kota,
                p.nama_club,
                p.sekolah
              FROM peserta p
              GROUP BY p.nama_peserta, p.jenis_kelamin, p.asal_kota, p.nama_club, p.sekolah
              ORDER BY p.nama_peserta ASC";
    
    $result = $conn->query($query);
    
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>No</th>";
    echo "<th>Nama Peserta</th>";
    echo "<th>Gender</th>";
    echo "<th>Asal Kota</th>";
    echo "<th>Club</th>";
    echo "<th>Sekolah</th>";
    echo "<th>Total Turnamen</th>";
    echo "<th>Kategori Dominan</th>";
    echo "<th>Rata-rata Ranking</th>";
    echo "<th>Juara 1</th>";
    echo "<th>Juara 2</th>";
    echo "<th>Juara 3</th>";
    echo "<th>Top 10</th>";
    echo "<th>Bracket Champion</th>";
    echo "<th>Bracket Runner Up</th>";
    echo "<th>Bracket 3rd Place</th>";
    echo "<th>Bracket Win Rate</th>";
    echo "</tr>";
    
    $no = 1;
    while ($peserta = $result->fetch_assoc()) {
        // Ambil semua ranking peserta ini
        $queryRanking = "
            SELECT 
                sb.kegiatan_id,
                sb.category_id,
                k.nama_kegiatan,
                c.name as category_name,
                sb.id as scoreboard_id
            FROM score_boards sb
            INNER JOIN kegiatan k ON sb.kegiatan_id = k.id
            INNER JOIN categories c ON sb.category_id = c.id
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
        $top10 = 0;
        
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
            
            $ranking = 0;
            foreach ($pesertaScores as $index => $ps) {
                // Cek berdasarkan nama peserta, bukan ID
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
                $rankings[] = ['ranking' => $ranking, 'turnamen' => $turnamen['nama_kegiatan']];
                
                if ($ranking == 1) $juara1++;
                if ($ranking == 2) $juara2++;
                if ($ranking == 3) $juara3++;
                if ($ranking <= 10) $top10++;
            }
            
            $stmtPT->close();
        }
        
        $stmtRank->close();
        
        // Ambil statistik bracket
        $bracketStats = getBracketStatistics($conn, $peserta['nama_peserta']);
        $bracketWinRate = ($bracketStats['bracket_matches_won'] + $bracketStats['bracket_matches_lost']) > 0 
            ? round(($bracketStats['bracket_matches_won'] / ($bracketStats['bracket_matches_won'] + $bracketStats['bracket_matches_lost'])) * 100, 1) . '%'
            : '0%';
        
        $totalTurnamen = count($rankings);
        $kategoriDominan = getKategoriDominan($rankings);
        $avgRanking = $totalTurnamen > 0 ? round(array_sum(array_column($rankings, 'ranking')) / $totalTurnamen, 2) : '-';
        
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . htmlspecialchars($peserta['nama_peserta']) . "</td>";
        echo "<td>" . htmlspecialchars($peserta['jenis_kelamin']) . "</td>";
        echo "<td>" . htmlspecialchars($peserta['asal_kota'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($peserta['nama_club'] ?? '-') . "</td>";
        echo "<td>" . htmlspecialchars($peserta['sekolah'] ?? '-') . "</td>";
        echo "<td>" . $totalTurnamen . "</td>";
        echo "<td>" . $kategoriDominan['kategori'] . " - " . $kategoriDominan['label'] . "</td>";
        echo "<td>" . $avgRanking . "</td>";
        echo "<td>" . $juara1 . "</td>";
        echo "<td>" . $juara2 . "</td>";
        echo "<td>" . $juara3 . "</td>";
        echo "<td>" . $top10 . "</td>";
        echo "<td>" . $bracketStats['bracket_champion'] . "</td>";
        echo "<td>" . $bracketStats['bracket_runner_up'] . "</td>";
        echo "<td>" . $bracketStats['bracket_third_place'] . "</td>";
        echo "<td>" . $bracketWinRate . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    exit();
}

// Ambil filter
$gender = $_GET['gender'] ?? '';
$nama = $_GET['nama'] ?? '';
$club = $_GET['club'] ?? '';
$kategori_filter = $_GET['kategori'] ?? '';

// Query untuk mengambil semua peserta unik - gunakan GROUP BY untuk memastikan tidak ada duplikat
// Prioritaskan ID terkecil jika ada nama yang sama
$query = "SELECT 
            MIN(p.id) as id,
            p.nama_peserta,
            p.jenis_kelamin,
            p.asal_kota,
            p.nama_club,
            p.sekolah,
            MAX(p.tanggal_lahir) as tanggal_lahir
          FROM peserta p
          WHERE 1=1";

$params = [];
$types = '';

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

// GROUP BY nama_peserta untuk menggabungkan peserta dengan nama sama
$query .= " GROUP BY p.nama_peserta, p.jenis_kelamin, p.asal_kota, p.nama_club, p.sekolah";
$query .= " ORDER BY p.nama_peserta ASC";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

// Proses data peserta dengan ranking
$pesertaData = [];
$totalKategoriA = 0;
$totalKategoriB = 0;
$totalKategoriC = 0;
$totalKategoriD = 0;
$totalKategoriE = 0;

// Array untuk tracking peserta yang sudah diproses (hindari duplikat berdasarkan ID)
$processedPeserta = [];

while ($peserta = $result->fetch_assoc()) {
    // Skip jika peserta dengan ID ini sudah diproses
    if (isset($processedPeserta[$peserta['id']])) {
        continue;
    }
    
    // Tandai peserta ini sudah diproses
    $processedPeserta[$peserta['id']] = true;
    // Ambil semua ranking peserta ini dari berbagai turnamen
    // Cari berdasarkan nama_peserta untuk menggabungkan semua ID dengan nama sama
    $queryRanking = "
        SELECT 
            sb.kegiatan_id,
            sb.category_id,
            k.nama_kegiatan,
            c.name as category_name,
            sb.id as scoreboard_id,
            sb.created
        FROM score_boards sb
        INNER JOIN kegiatan k ON sb.kegiatan_id = k.id
        INNER JOIN categories c ON sb.category_id = c.id
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
    $top10 = 0;
    
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
        
        $ranking = 0;
        foreach ($pesertaScores as $index => $ps) {
            // Cek berdasarkan nama peserta, bukan ID
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
            $katInfo = getKategoriFromRanking($ranking);
            $rankings[] = [
                'ranking' => $ranking,
                'turnamen' => $turnamen['nama_kegiatan'],
                'kategori' => $turnamen['category_name'],
                'tanggal' => $turnamen['created'],
                'kategori_ranking' => $katInfo
            ];
            
            if ($ranking == 1) $juara1++;
            if ($ranking == 2) $juara2++;
            if ($ranking == 3) $juara3++;
            if ($ranking <= 10) $top10++;
        }
        
        $stmtPT->close();
    }
    
    $stmtRank->close();
    
    // Ambil statistik bracket
    $bracketStats = getBracketStatistics($conn, $peserta['nama_peserta']);
    
    $kategoriDominan = getKategoriDominan($rankings);
    
    if (!empty($kategori_filter) && $kategoriDominan['kategori'] != $kategori_filter) {
        continue;
    }
    
    $totalTurnamen = count($rankings);
    $avgRanking = $totalTurnamen > 0 ? round(array_sum(array_column($rankings, 'ranking')) / $totalTurnamen, 2) : 0;
    
    $umur = 0;
    if (!empty($peserta['tanggal_lahir'])) {
        $dob = new DateTime($peserta['tanggal_lahir']);
        $today = new DateTime();
        $umur = $today->diff($dob)->y;
    }
    
    $pesertaData[] = [
        'id' => $peserta['id'],
        'nama' => $peserta['nama_peserta'],
        'gender' => $peserta['jenis_kelamin'],
        'umur' => $umur,
        'kota' => $peserta['asal_kota'],
        'club' => $peserta['nama_club'],
        'sekolah' => $peserta['sekolah'],
        'total_turnamen' => $totalTurnamen,
        'kategori_dominan' => $kategoriDominan,
        'avg_ranking' => $avgRanking,
        'juara1' => $juara1,
        'juara2' => $juara2,
        'juara3' => $juara3,
        'top10' => $top10,
        'rankings' => $rankings,
        'bracket_stats' => $bracketStats
    ];
    
    switch ($kategoriDominan['kategori']) {
        case 'A': $totalKategoriA++; break;
        case 'B': $totalKategoriB++; break;
        case 'C': $totalKategoriC++; break;
        case 'D': $totalKategoriD++; break;
        case 'E': $totalKategoriE++; break;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistik & Penilaian Peserta - Turnamen Panahan</title>
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
        }

        body { 
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .header-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.3);
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

        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-left: 4px solid;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .stats-card.kategori-a { border-color: var(--success-color); }
        .stats-card.kategori-b { border-color: var(--primary-color); }
        .stats-card.kategori-c { border-color: var(--accent-color); }
        .stats-card.kategori-d { border-color: var(--warning-color); }
        .stats-card.kategori-e { border-color: #6b7280; }

        .stats-card h4 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }

        .filter-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .data-table {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table th {
            background: linear-gradient(135deg, var(--dark-color) 0%, #374151 100%);
            color: white;
            font-weight: 600;
            padding: 1rem 0.75rem;
            text-transform: uppercase;
            font-size: 0.875rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table td {
            padding: 1rem 0.75rem;
            vertical-align: middle;
            font-size: 0.9rem;
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .badge-kategori {
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 600;
        }

        .badge-ranking {
            font-size: 0.75rem;
            padding: 0.35rem 0.6rem;
            border-radius: 20px;
        }

        .btn {
            border-radius: 12px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-radius: 20px 20px 0 0;
        }

        .ranking-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-size: 0.75rem;
            margin: 0.1rem;
        }

        .trophy-icon {
            font-size: 1.2rem;
            margin-right: 0.3rem;
        }

        .table-responsive {
            max-height: 70vh;
        }

        .kategori-legend {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .legend-item {
            display: inline-block;
            margin: 0.5rem 1rem 0.5rem 0;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .bracket-stats-badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
            border-radius: 15px;
            margin: 0.1rem;
            display: inline-block;
        }

        .bracket-card {
            background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%);
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .bracket-card h6 {
            color: #2d3436;
            font-weight: 700;
            margin-bottom: 0.8rem;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="header-card text-center">
        <h1><i class="fas fa-chart-line me-3"></i>Statistik & Penilaian Anak Didik</h1>
        <p class="mb-0">Sistem Kategorisasi Kemampuan Berdasarkan Performa Turnamen</p>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="dashboard.php" class="btn btn-info">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
        </a>
        <a href="?export=excel<?= !empty($gender) ? '&gender='.$gender : '' ?><?= !empty($nama) ? '&nama='.$nama : '' ?><?= !empty($club) ? '&club='.$club : '' ?><?= !empty($kategori_filter) ? '&kategori='.$kategori_filter : '' ?>" class="btn btn-success">
            <i class="fas fa-file-excel me-2"></i>Export Excel
        </a>
    </div>

    <!-- Kategori Legend -->
    <div class="kategori-legend">
        <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Sistem Kategorisasi</h5>
        <div>
            <span class="legend-item bg-success text-white">🏆 Kategori A: Peringkat 1-3 (Sangat Baik)</span>
            <span class="legend-item bg-primary text-white">🥈 Kategori B: Peringkat 4-10 (Baik)</span>
            <span class="legend-item bg-info text-white">🥉 Kategori C: Peringkat 11-20 (Cukup)</span>
            <span class="legend-item bg-warning text-dark">📊 Kategori D: Peringkat 21-32 (Perlu Latihan)</span>
            <span class="legend-item bg-secondary text-white">📈 Kategori E: Peringkat 33+ / Belum Bertanding (Pemula)</span>
        </div>
        <p class="mt-3 mb-0 text-muted small">
            <i class="fas fa-lightbulb me-1"></i>
            <strong>Catatan:</strong> Kategori dominan ditentukan berdasarkan kategori yang paling sering muncul dari seluruh turnamen yang diikuti peserta.
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stats-card kategori-a text-center">
                <h4 class="text-success"><?= $totalKategoriA ?></h4>
                <small class="text-muted">Kategori A</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card kategori-b text-center">
                <h4 class="text-primary"><?= $totalKategoriB ?></h4>
                <small class="text-muted">Kategori B</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card kategori-c text-center">
                <h4 class="text-info"><?= $totalKategoriC ?></h4>
                <small class="text-muted">Kategori C</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card kategori-d text-center">
                <h4 class="text-warning"><?= $totalKategoriD ?></h4>
                <small class="text-muted">Kategori D</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card kategori-e text-center">
                <h4 class="text-secondary"><?= $totalKategoriE ?></h4>
                <small class="text-muted">Kategori E</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card text-center" style="border-color: var(--primary-color);">
                <h4 class="text-primary"><?= count($pesertaData) ?></h4>
                <small class="text-muted">Total Peserta</small>
            </div>
        </div>
    </div>

    <!-- Form Filter -->
    <div class="filter-card">
        <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Pencarian</h5>
        <form method="get">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Nama Peserta</label>
                    <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($nama) ?>" placeholder="Cari nama...">
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
                    <label class="form-label">Club</label>
                    <input type="text" class="form-control" name="club" value="<?= htmlspecialchars($club) ?>" placeholder="Nama club...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Kategori</label>
                    <select class="form-select" name="kategori">
                        <option value="">Semua Kategori</option>
                        <option value="A" <?= $kategori_filter=="A"?'selected':'' ?>>Kategori A</option>
                        <option value="B" <?= $kategori_filter=="B"?'selected':'' ?>>Kategori B</option>
                        <option value="C" <?= $kategori_filter=="C"?'selected':'' ?>>Kategori C</option>
                        <option value="D" <?= $kategori_filter=="D"?'selected':'' ?>>Kategori D</option>
                        <option value="E" <?= $kategori_filter=="E"?'selected':'' ?>>Kategori E</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                    <a href="?" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabel Statistik -->
    <div class="data-table">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Peserta</th>
                        <th>Gender</th>
                        <th>Umur</th>
                        <th>Club</th>
                        <th>Kategori</th>
                        <th>Total Turnamen</th>
                        <th>Avg Ranking</th>
                        <th>Juara 1</th>
                        <th>Juara 2</th>
                        <th>Juara 3</th>
                        <th>Top 10</th>
                        <th>Bracket Stats</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($pesertaData)): ?>
                    <tr>
                        <td colspan="14" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3"></i><br>
                            <h5>Tidak ada data peserta yang ditemukan</h5>
                            <p>Silakan ubah filter atau pastikan peserta telah mengikuti turnamen.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = 1; 
                    foreach ($pesertaData as $p): 
                    ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                                <strong><?= htmlspecialchars($p['nama']) ?></strong><br>
                                <small class="text-muted"><?= htmlspecialchars($p['sekolah'] ?? '-') ?></small>
                            </td>
                            <td>
                                <span class="badge <?= $p['gender'] == 'Laki-laki' ? 'bg-primary' : 'bg-danger' ?>">
                                    <i class="fas <?= $p['gender'] == 'Laki-laki' ? 'fa-mars' : 'fa-venus' ?> me-1"></i>
                                    <?= htmlspecialchars($p['gender'] ?? '') ?>
                                </span>
                            </td>
                            <td><?= $p['umur'] > 0 ? $p['umur'] . ' tahun' : '-' ?></td>
                            <td class="small"><?= htmlspecialchars($p['club'] ?? '-') ?></td>
                            <td>
                                <span class="badge badge-kategori bg-<?= $p['kategori_dominan']['color'] ?> text-white">
                                    <?= $p['kategori_dominan']['icon'] ?> 
                                    Kategori <?= $p['kategori_dominan']['kategori'] ?>
                                </span>
                                <br>
                                <small class="text-muted"><?= $p['kategori_dominan']['label'] ?></small>
                            </td>
                            <td class="text-center">
                                <strong class="text-primary"><?= $p['total_turnamen'] ?></strong>
                            </td>
                            <td class="text-center">
                                <?php if ($p['avg_ranking'] > 0): ?>
                                    <span class="badge bg-secondary">#<?= $p['avg_ranking'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($p['juara1'] > 0): ?>
                                    <span class="badge bg-warning text-dark">
                                        <span class="trophy-icon">🥇</span><?= $p['juara1'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($p['juara2'] > 0): ?>
                                    <span class="badge bg-secondary text-white">
                                        <span class="trophy-icon">🥈</span><?= $p['juara2'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($p['juara3'] > 0): ?>
                                    <span class="badge bg-info text-white">
                                        <span class="trophy-icon">🥉</span><?= $p['juara3'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($p['top10'] > 0): ?>
                                    <span class="badge bg-primary"><?= $p['top10'] ?>x</span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($p['bracket_stats']['total_bracket'] > 0): ?>
                                    <span class="bracket-stats-badge bg-warning text-dark" title="Champion">
                                        🏆 <?= $p['bracket_stats']['bracket_champion'] ?>
                                    </span><br>
                                    <span class="bracket-stats-badge bg-secondary text-white" title="Runner Up">
                                        🥈 <?= $p['bracket_stats']['bracket_runner_up'] ?>
                                    </span><br>
                                    <span class="bracket-stats-badge bg-info text-white" title="3rd Place">
                                        🥉 <?= $p['bracket_stats']['bracket_third_place'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" onclick="showDetail(<?= htmlspecialchars(json_encode($p)) ?>)">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if (!empty($pesertaData)): ?>
        <div class="mt-3 text-end">
            <small class="text-muted">Menampilkan <?= count($pesertaData) ?> peserta</small>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">
                    <i class="fas fa-chart-bar me-2"></i>Detail Performa Peserta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Informasi Peserta -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title mb-3" id="modalNama"></h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Gender:</strong> <span id="modalGender"></span></p>
                                <p class="mb-2"><strong>Umur:</strong> <span id="modalUmur"></span></p>
                                <p class="mb-2"><strong>Kota:</strong> <span id="modalKota"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Club:</strong> <span id="modalClub"></span></p>
                                <p class="mb-2"><strong>Sekolah:</strong> <span id="modalSekolah"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistik Keseluruhan -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <strong><i class="fas fa-trophy me-2"></i>Statistik Keseluruhan</strong>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h4 class="text-primary" id="modalTotalTurnamen">0</h4>
                                <small class="text-muted">Total Turnamen</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-warning" id="modalJuara1">0</h4>
                                <small class="text-muted">🥇 Juara 1</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-secondary" id="modalJuara2">0</h4>
                                <small class="text-muted">🥈 Juara 2</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-info" id="modalJuara3">0</h4>
                                <small class="text-muted">🥉 Juara 3</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-md-6">
                                <h4 id="modalAvgRanking">0</h4>
                                <small class="text-muted">Rata-rata Ranking</small>
                            </div>
                            <div class="col-md-6">
                                <h4 class="text-primary" id="modalTop10">0</h4>
                                <small class="text-muted">Masuk Top 10</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kategori Dominan -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <strong><i class="fas fa-award me-2"></i>Kategori Kemampuan</strong>
                    </div>
                    <div class="card-body text-center">
                        <h2 id="modalKategori"></h2>
                        <p class="mb-0" id="modalKategoriLabel"></p>
                    </div>
                </div>

                <!-- Statistik Bracket/Aduan -->
                <div class="card mb-3" id="bracketStatsCard" style="display: none;">
                    <div class="card-header bg-warning text-dark">
                        <strong><i class="fas fa-crosshairs me-2"></i>Statistik Bracket / Aduan</strong>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h4 class="text-warning" id="modalBracketChampion">0</h4>
                                <small class="text-muted">🏆 Juara 1</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-secondary" id="modalBracketRunnerUp">0</h4>
                                <small class="text-muted">🥈 Juara 2</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-info" id="modalBracketThirdPlace">0</h4>
                                <small class="text-muted">🥉 Juara 3</small>
                            </div>
                            <div class="col-md-3">
                                <h4 class="text-primary" id="modalBracketTotal">0</h4>
                                <small class="text-muted">Total Bracket</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row text-center">
                            <div class="col-md-4">
                                <h4 class="text-success" id="modalBracketWon">0</h4>
                                <small class="text-muted">Matches Won</small>
                            </div>
                            <div class="col-md-4">
                                <h4 class="text-danger" id="modalBracketLost">0</h4>
                                <small class="text-muted">Matches Lost</small>
                            </div>
                            <div class="col-md-4">
                                <h4 id="modalBracketWinRate">0%</h4>
                                <small class="text-muted">Win Rate</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Bracket -->
                <div class="card mb-3" id="bracketHistoryCard" style="display: none;">
                    <div class="card-header bg-dark text-white">
                        <strong><i class="fas fa-history me-2"></i>Riwayat Bracket / Aduan</strong>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Turnamen</th>
                                        <th>Kategori</th>
                                        <th>Tanggal</th>
                                        <th>Posisi</th>
                                        <th>Bracket Size</th>
                                    </tr>
                                </thead>
                                <tbody id="modalBracketHistory">
                                    <!-- Akan diisi oleh JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Turnamen -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <strong><i class="fas fa-history me-2"></i>Riwayat Kualifikasi / Turnament</strong>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Turnamen</th>
                                        <th>Kategori</th>
                                        <th>Tanggal</th>
                                        <th>Ranking</th>
                                        <th>Kategori</th>
                                    </tr>
                                </thead>
                                <tbody id="modalRiwayat">
                                    <!-- Akan diisi oleh JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
function showDetail(data) {
    // Set informasi peserta
    document.getElementById('modalNama').textContent = data.nama;
    document.getElementById('modalGender').innerHTML = `<span class="badge bg-${data.gender === 'Laki-laki' ? 'primary' : 'danger'}">${data.gender}</span>`;
    document.getElementById('modalUmur').textContent = data.umur > 0 ? data.umur + ' tahun' : '-';
    document.getElementById('modalKota').textContent = data.kota || '-';
    document.getElementById('modalClub').textContent = data.club || '-';
    document.getElementById('modalSekolah').textContent = data.sekolah || '-';
    
    // Set statistik
    document.getElementById('modalTotalTurnamen').textContent = data.total_turnamen;
    document.getElementById('modalJuara1').textContent = data.juara1;
    document.getElementById('modalJuara2').textContent = data.juara2;
    document.getElementById('modalJuara3').textContent = data.juara3;
    document.getElementById('modalAvgRanking').textContent = data.avg_ranking > 0 ? '#' + data.avg_ranking : '-';
    document.getElementById('modalTop10').textContent = data.top10;
    
    // Set kategori
    const katInfo = data.kategori_dominan;
    document.getElementById('modalKategori').innerHTML = `
        <span class="badge badge-kategori bg-${katInfo.color} text-white" style="font-size: 1.5rem; padding: 1rem 2rem;">
            ${katInfo.icon} Kategori ${katInfo.kategori}
        </span>
    `;
    document.getElementById('modalKategoriLabel').innerHTML = `<strong>${katInfo.label}</strong>`;
    
    // Set statistik bracket
    const bracketStats = data.bracket_stats;
    if (bracketStats.total_bracket > 0) {
        document.getElementById('bracketStatsCard').style.display = 'block';
        document.getElementById('modalBracketChampion').textContent = bracketStats.bracket_champion;
        document.getElementById('modalBracketRunnerUp').textContent = bracketStats.bracket_runner_up;
        document.getElementById('modalBracketThirdPlace').textContent = bracketStats.bracket_third_place;
        document.getElementById('modalBracketTotal').textContent = bracketStats.total_bracket;
        document.getElementById('modalBracketWon').textContent = bracketStats.bracket_matches_won;
        document.getElementById('modalBracketLost').textContent = bracketStats.bracket_matches_lost;
        
        const totalMatches = bracketStats.bracket_matches_won + bracketStats.bracket_matches_lost;
        const winRate = totalMatches > 0 ? ((bracketStats.bracket_matches_won / totalMatches) * 100).toFixed(1) : 0;
        document.getElementById('modalBracketWinRate').textContent = winRate + '%';
        
        // Set riwayat bracket
        const bracketHistoryBody = document.getElementById('modalBracketHistory');
        bracketHistoryBody.innerHTML = '';
        
        if (bracketStats.bracket_history.length > 0) {
            document.getElementById('bracketHistoryCard').style.display = 'block';
            
            bracketStats.bracket_history.forEach((h, index) => {
                let positionBadge = '';
                if (h.position === 'champion') {
                    positionBadge = '<span class="badge bg-warning text-dark">🏆 Juara 1</span>';
                } else if (h.position === 'runner_up') {
                    positionBadge = '<span class="badge bg-secondary text-white">🥈 Juara 2</span>';
                } else if (h.position === 'third_place') {
                    positionBadge = '<span class="badge bg-info text-white">🥉 Juara 3</span>';
                } else {
                    positionBadge = '<span class="badge bg-light text-dark">Participant</span>';
                }
                
                const row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${h.nama_kegiatan}</td>
                        <td><small>${h.category_name}</small></td>
                        <td><small>${new Date(h.created_at).toLocaleDateString('id-ID')}</small></td>
                        <td>${positionBadge}</td>
                        <td class="text-center"><span class="badge bg-dark">${h.bracket_size}</span></td>
                    </tr>
                `;
                bracketHistoryBody.innerHTML += row;
            });
        } else {
            document.getElementById('bracketHistoryCard').style.display = 'none';
        }
    } else {
        document.getElementById('bracketStatsCard').style.display = 'none';
        document.getElementById('bracketHistoryCard').style.display = 'none';
    }
    
    // Set riwayat turnamen
    const riwayatBody = document.getElementById('modalRiwayat');
    riwayatBody.innerHTML = '';
    
    if (data.rankings.length === 0) {
        riwayatBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Belum ada riwayat turnamen</td></tr>';
    } else {
        data.rankings.forEach((r, index) => {
            const katRank = r.kategori_ranking;
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${r.turnamen}</td>
                    <td><small>${r.kategori}</small></td>
                    <td><small>${new Date(r.tanggal).toLocaleDateString('id-ID')}</small></td>
                    <td class="text-center">
                        <span class="badge bg-secondary">#${r.ranking}</span>
                    </td>
                    <td>
                        <span class="badge bg-${katRank.color} text-white">
                            ${katRank.icon} ${katRank.kategori}
                        </span>
                    </td>
                </tr>
            `;
            riwayatBody.innerHTML += row;
        });
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('detailModal'));
    modal.show();
}

// Auto-submit form on select change
document.querySelectorAll('select[name="gender"], select[name="kategori"]').forEach(function(select) {
    select.addEventListener('change', function() {
        this.form.submit();
    });
});

// Konfirmasi export Excel
document.querySelector('a[href*="export=excel"]')?.addEventListener('click', function(e) {
    if (!confirm('Export data statistik ke Excel?\n\nProses ini mungkin membutuhkan waktu beberapa saat.')) {
        e.preventDefault();
    }
});

// Tooltip
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
</body>
</html>