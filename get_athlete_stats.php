<?php
header('Content-Type: application/json');
include 'panggil.php';

if (!isset($_GET['name']) || empty($_GET['name'])) {
    echo json_encode(['success' => false, 'message' => 'Nama atlet tidak ditemukan']);
    exit;
}

$athleteName = $_GET['name'];

// Query untuk mendapatkan data turnamen atlet
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
$stmtRank->bind_param("s", $athleteName);
$stmtRank->execute();
$resultRank = $stmtRank->get_result();

$tournaments = [];
$juara1 = 0;
$juara2 = 0;
$juara3 = 0;

while ($turnamen = $resultRank->fetch_assoc()) {
    // Ambil semua peserta dalam turnamen ini
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
    
    // Sort peserta berdasarkan score
    usort($pesertaScores, function($a, $b) {
        if ($b['total_score'] != $a['total_score']) {
            return $b['total_score'] - $a['total_score'];
        }
        return $b['total_x'] - $a['total_x'];
    });
    
    $totalPesertaTurnamen = count($pesertaScores);
    $ranking = 0;
    
    // Cari ranking atlet
    foreach ($pesertaScores as $index => $ps) {
        $queryCheckName = "SELECT nama_peserta FROM peserta WHERE id = ?";
        $stmtCheckName = $conn->prepare($queryCheckName);
        $stmtCheckName->bind_param("i", $ps['peserta_id']);
        $stmtCheckName->execute();
        $resultCheckName = $stmtCheckName->get_result();
        $checkName = $resultCheckName->fetch_assoc();
        $stmtCheckName->close();
        
        if ($checkName && $checkName['nama_peserta'] == $athleteName) {
            $ranking = $index + 1;
            break;
        }
    }
    
    if ($ranking > 0) {
        $tournaments[] = [
            'nama_kegiatan' => $turnamen['nama_kegiatan'],
            'category' => $turnamen['category_name'],
            'tanggal' => date('d M Y', strtotime($turnamen['created'])),
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

$response = [
    'success' => true,
    'data' => [
        'total_turnamen' => count($tournaments),
        'juara1' => $juara1,
        'juara2' => $juara2,
        'juara3' => $juara3,
        'tournaments' => $tournaments
    ]
];

echo json_encode($response);
?>