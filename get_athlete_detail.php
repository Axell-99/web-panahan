<?php
include 'panggil.php';
include 'check_access.php';
requireAdmin();

header('Content-Type: application/json');

if (!isset($_GET['nama'])) {
    echo json_encode(['success' => false, 'message' => 'Nama tidak ditemukan']);
    exit;
}

$nama = $_GET['nama'];

// Fungsi kategori
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

// Ambil data peserta
$queryPeserta = "SELECT 
                    MIN(p.id) as id,
                    p.nama_peserta,
                    p.jenis_kelamin,
                    p.nama_club,
                    p.sekolah
                 FROM peserta p
                 WHERE p.nama_peserta = ?
                 GROUP BY p.nama_peserta, p.jenis_kelamin, p.nama_club, p.sekolah";

$stmtPeserta = $conn->prepare($queryPeserta);
$stmtPeserta->bind_param("s", $nama);
$stmtPeserta->execute();
$resultPeserta = $stmtPeserta->get_result();

if ($resultPeserta->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Peserta tidak ditemukan']);
    exit;
}

$peserta = $resultPeserta->fetch_assoc();

// Ambil semua ranking
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
$stmtRank->bind_param("s", $nama);
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
        
        if ($checkName && $checkName['nama_peserta'] == $nama) {
            $ranking = $index + 1;
            break;
        }
    }
    
    if ($ranking > 0) {
        $katInfo = getKategoriFromRanking($ranking, $totalPesertaTurnamen);
        $rankings[] = [
            'ranking' => $ranking,
            'turnamen' => $turnamen['nama_kegiatan'],
            'kategori' => $turnamen['category_name'],
            'tanggal' => $turnamen['created'],
            'kategori_ranking' => $katInfo,
            'total_peserta' => $totalPesertaTurnamen
        ];
        
        if ($ranking == 1) $juara1++;
        if ($ranking == 2) $juara2++;
        if ($ranking == 3) $juara3++;
        if ($ranking <= 10) $top10++;
    }
    
    $stmtPT->close();
}

$stmtRank->close();

$kategoriDominan = getKategoriDominan($rankings);
$totalTurnamen = count($rankings);
$avgRanking = $totalTurnamen > 0 ? round(array_sum(array_column($rankings, 'ranking')) / $totalTurnamen, 2) : 0;

$response = [
    'success' => true,
    'athlete' => [
        'nama' => $peserta['nama_peserta'],
        'gender' => $peserta['jenis_kelamin'],
        'club' => $peserta['nama_club'],
        'sekolah' => $peserta['sekolah'],
        'kategori_dominan' => $kategoriDominan,
        'total_turnamen' => $totalTurnamen,
        'avg_ranking' => $avgRanking,
        'juara1' => $juara1,
        'juara2' => $juara2,
        'juara3' => $juara3,
        'top10' => $top10,
        'rankings' => $rankings
    ]
];

echo json_encode($response);
?>