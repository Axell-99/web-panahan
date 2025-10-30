<?php
// Aktifkan error reporting untuk debuggin
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'check_access.php';
requireLogin();

// Mulai session jika belum
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// HANDLER UNTUK BRACKET TOURNAMENT (ADUAN)
// ============================================
if (isset($_GET['aduan']) && $_GET['aduan'] == 'true') {
    try {
        include 'panggil.php';
    } catch (Exception $e) {
        die("Error koneksi database: " . $e->getMessage());
    }

    $kegiatan_id = isset($_GET['kegiatan_id']) ? intval($_GET['kegiatan_id']) : null;
    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
    $scoreboard_id = isset($_GET['scoreboard']) ? intval($_GET['scoreboard']) : null;

    if (!$kegiatan_id || !$category_id || !$scoreboard_id) {
        die("Parameter tidak lengkap.");
    }

    // Handler untuk menyimpan hasil match
    if (isset($_POST['save_match_result'])) {
        header('Content-Type: application/json');
        
        $match_id = $_POST['match_id'] ?? '';
        $winner_id = intval($_POST['winner_id'] ?? 0);
        $loser_id = intval($_POST['loser_id'] ?? 0);
        $bracket_size = intval($_POST['bracket_size'] ?? 0);
        
        try {
            // Check if match result already exists
            $checkQuery = "SELECT id FROM bracket_matches WHERE kegiatan_id = ? AND category_id = ? AND scoreboard_id = ? AND match_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("iiis", $kegiatan_id, $category_id, $scoreboard_id, $match_id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                // Update existing record
                $updateQuery = "UPDATE bracket_matches SET winner_id = ?, loser_id = ?, updated_at = NOW() WHERE kegiatan_id = ? AND category_id = ? AND scoreboard_id = ? AND match_id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("iiiiss", $winner_id, $loser_id, $kegiatan_id, $category_id, $scoreboard_id, $match_id);
                $updateStmt->execute();
                $updateStmt->close();
            } else {
                // Insert new record
                $insertQuery = "INSERT INTO bracket_matches (kegiatan_id, category_id, scoreboard_id, match_id, winner_id, loser_id, bracket_size, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->bind_param("iiisiii", $kegiatan_id, $category_id, $scoreboard_id, $match_id, $winner_id, $loser_id, $bracket_size);
                $insertStmt->execute();
                $insertStmt->close();
            }
            
            $checkStmt->close();
            
            echo json_encode(['status' => 'success', 'message' => 'Match result saved']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        
        $conn->close();
        exit;
    }

    // Handler untuk menyimpan champion
    if (isset($_POST['save_champion'])) {
        header('Content-Type: application/json');
        
        $champion_id = intval($_POST['champion_id'] ?? 0);
        $runner_up_id = intval($_POST['runner_up_id'] ?? 0);
        $third_place_id = !empty($_POST['third_place_id']) ? intval($_POST['third_place_id']) : null;
        $bracket_size = intval($_POST['bracket_size'] ?? 0);
        
        try {
            // Check if champion record already exists
            $checkQuery = "SELECT id FROM bracket_champions WHERE kegiatan_id = ? AND category_id = ? AND scoreboard_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("iii", $kegiatan_id, $category_id, $scoreboard_id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                // Update existing record
                if ($third_place_id !== null) {
                    $updateQuery = "UPDATE bracket_champions SET champion_id = ?, runner_up_id = ?, third_place_id = ?, bracket_size = ?, updated_at = NOW() WHERE kegiatan_id = ? AND category_id = ? AND scoreboard_id = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("iiiiii", $champion_id, $runner_up_id, $third_place_id, $bracket_size, $kegiatan_id, $category_id, $scoreboard_id);
                } else {
                    $updateQuery = "UPDATE bracket_champions SET champion_id = ?, runner_up_id = ?, bracket_size = ?, updated_at = NOW() WHERE kegiatan_id = ? AND category_id = ? AND scoreboard_id = ?";
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->bind_param("iiiiii", $champion_id, $runner_up_id, $bracket_size, $kegiatan_id, $category_id, $scoreboard_id);
                }
                $updateStmt->execute();
                $updateStmt->close();
            } else {
                // Insert new record
                if ($third_place_id !== null) {
                    $insertQuery = "INSERT INTO bracket_champions (kegiatan_id, category_id, scoreboard_id, champion_id, runner_up_id, third_place_id, bracket_size, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                    $insertStmt = $conn->prepare($insertQuery);
                    $insertStmt->bind_param("iiiiiii", $kegiatan_id, $category_id, $scoreboard_id, $champion_id, $runner_up_id, $third_place_id, $bracket_size);
                } else {
                    $insertQuery = "INSERT INTO bracket_champions (kegiatan_id, category_id, scoreboard_id, champion_id, runner_up_id, bracket_size, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                    $insertStmt = $conn->prepare($insertQuery);
                    $insertStmt->bind_param("iiiiii", $kegiatan_id, $category_id, $scoreboard_id, $champion_id, $runner_up_id, $bracket_size);
                }
                $insertStmt->execute();
                $insertStmt->close();
            }
            
            $checkStmt->close();
            
            echo json_encode(['status' => 'success', 'message' => 'Champion saved']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        
        $conn->close();
        exit;
    }

    // Ambil data kegiatan
    $kegiatanData = [];
    try {
        $queryKegiatan = "SELECT id, nama_kegiatan FROM kegiatan WHERE id = ?";
        $stmtKegiatan = $conn->prepare($queryKegiatan);
        $stmtKegiatan->bind_param("i", $kegiatan_id);
        $stmtKegiatan->execute();
        $resultKegiatan = $stmtKegiatan->get_result();
        
        if ($resultKegiatan->num_rows > 0) {
            $kegiatanData = $resultKegiatan->fetch_assoc();
        }
        $stmtKegiatan->close();
    } catch (Exception $e) {
        die("Error mengambil data kegiatan: " . $e->getMessage());
    }

    // Ambil data kategori
    $kategoriData = [];
    try {
        $queryKategori = "SELECT id, name FROM categories WHERE id = ?";
        $stmtKategori = $conn->prepare($queryKategori);
        $stmtKategori->bind_param("i", $category_id);
        $stmtKategori->execute();
        $resultKategori = $stmtKategori->get_result();
        
        if ($resultKategori->num_rows > 0) {
            $kategoriData = $resultKategori->fetch_assoc();
        }
        $stmtKategori->close();
    } catch (Exception $e) {
        die("Error mengambil data kategori: " . $e->getMessage());
    }

    // Ambil data peserta berdasarkan ranking
    $pesertaList = [];
    try {
        $queryPeserta = "
            SELECT 
                p.id,
                p.nama_peserta,
                p.jenis_kelamin
            FROM peserta p
            WHERE p.kegiatan_id = ? AND p.category_id = ?
            ORDER BY p.nama_peserta ASC
        ";
        $stmtPeserta = $conn->prepare($queryPeserta);
        $stmtPeserta->bind_param("ii", $kegiatan_id, $category_id);
        $stmtPeserta->execute();
        $resultPeserta = $stmtPeserta->get_result();
        
        while ($row = $resultPeserta->fetch_assoc()) {
            // Hitung total score untuk setiap peserta
            $queryScore = "SELECT score FROM score WHERE kegiatan_id = ? AND category_id = ? AND score_board_id = ? AND peserta_id = ?";
            $stmtScore = $conn->prepare($queryScore);
            $stmtScore->bind_param("iiii", $kegiatan_id, $category_id, $scoreboard_id, $row['id']);
            $stmtScore->execute();
            $resultScore = $stmtScore->get_result();
            
            $total_score = 0;
            $total_x = 0;
            while ($scoreRow = $resultScore->fetch_assoc()) {
                $scoreValue = strtolower($scoreRow['score']);
                if ($scoreValue == 'x') {
                    $total_score += 10;
                    $total_x++;
                } else if ($scoreValue != 'm') {
                    $total_score += intval($scoreValue);
                }
            }
            
            $row['total_score'] = $total_score;
            $row['total_x'] = $total_x;
            $pesertaList[] = $row;
            $stmtScore->close();
        }
        
        // Sort berdasarkan score tertinggi
        usort($pesertaList, function($a, $b) {
            if ($b['total_score'] != $a['total_score']) {
                return $b['total_score'] - $a['total_score'];
            }
            return $b['total_x'] - $a['total_x'];
        });
        
        $stmtPeserta->close();
    } catch (Exception $e) {
        die("Error mengambil data peserta: " . $e->getMessage());
    }

    $conn->close();
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tournament Eliminasi / Aduan <?= htmlspecialchars($kategoriData['name']) ?></title>
        <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #2d3436 0%, #000000 100%);
    min-height: 100vh;
    padding: 20px;
    color: white;
}

.container {
    max-width: 1600px;
    margin: 0 auto;
}

.header {
    text-align: center;
    margin-bottom: 30px;
    background: rgba(255, 255, 255, 0.05);
    padding: 20px;
    border-radius: 15px;
}

.back-btn {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    margin-bottom: 20px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.back-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.setup-container {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 15px;
    padding: 40px;
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.setup-container h2 {
    margin-bottom: 30px;
    font-size: 28px;
}

.bracket-size-options {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.size-option {
    background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
    border: none;
    color: white;
    padding: 20px 40px;
    border-radius: 12px;
    font-size: 24px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
}

.size-option:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(253, 203, 110, 0.4);
}

.size-option.active {
    background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);
}

.generate-btn {
    background: linear-gradient(135deg, #0984e3 0%, #6c5ce7 100%);
    border: none;
    color: white;
    padding: 15px 50px;
    border-radius: 12px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.generate-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(9, 132, 227, 0.4);
}

.generate-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.bracket-container {
    display: none;
    margin-top: 30px;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 20px;
    -webkit-overflow-scrolling: touch;
    position: relative;
}

.third-place-container {
    background: rgba(205, 127, 50, 0.15);
    border: 2px solid rgba(205, 127, 50, 0.5);
    border-radius: 15px;
    padding: 30px;
    margin-top: 40px;
    text-align: center;
    display: none;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.third-place-title {
    font-size: 24px;
    font-weight: 700;
    color: #cd7f32;
    margin-bottom: 25px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.third-place-bracket {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 12px;
    padding: 20px;
    display: inline-block;
    min-width: 400px;
}

.bracket {
    display: flex;
    justify-content: space-around;
    gap: 30px;
    min-width: fit-content;
    padding: 15px 0;
}

.round {
    display: flex;
    flex-direction: column;
    justify-content: space-around;
    min-height: 500px;
    flex: 1;
}

.round-title {
    text-align: center;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 15px;
    color: #fdcb6e;
}

.match {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin: 10px 0;
}

.player {
    background: linear-gradient(135deg, #ffa502 0%, #ff6348 100%);
    padding: 12px 16px;
    border-radius: 8px;
    min-width: 150px;
    max-width: 200px;
    font-weight: 600;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 3px solid transparent;
    font-size: 13px;
    text-align: center;
    word-break: break-word;
}

.player:hover:not(.empty) {
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(255, 165, 2, 0.4);
}

.player.winner {
    border-color: #00b894;
    box-shadow: 0 0 20px rgba(0, 184, 148, 0.5);
}

.player.empty {
    background: rgba(255, 255, 255, 0.1);
    color: #666;
    cursor: default;
}

.final-winner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.trophy {
    font-size: 80px;
    margin: 20px 0;
}

.winner-name {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    padding: 20px 40px;
    border-radius: 12px;
    font-size: 24px;
    font-weight: 700;
    color: #2d3436;
    box-shadow: 0 10px 30px rgba(255, 215, 0, 0.4);
}

.info-text {
    color: #ddd;
    margin-top: 20px;
    font-size: 14px;
}

/* ============================================
   RESPONSIVE BREAKPOINTS
   ============================================ */

/* Large Tablet - 1024px and below */
@media (max-width: 1024px) {
    body {
        padding: 15px;
    }

    .bracket {
        gap: 25px;
    }

    .round {
        min-height: 450px;
    }

    .player {
        min-width: 140px;
        max-width: 190px;
        padding: 11px 15px;
        font-size: 12px;
    }

    .round-title {
        font-size: 17px;
    }

    .trophy {
        font-size: 70px;
    }

    .winner-name {
        font-size: 22px;
        padding: 18px 35px;
    }
}

/* Tablet - 768px and below */
@media (max-width: 768px) {
    body {
        padding: 10px;
    }

    .header {
        padding: 15px;
        margin-bottom: 20px;
    }

    .setup-container {
        padding: 25px;
        max-width: 100%;
    }

    .setup-container h2 {
        font-size: 24px;
        margin-bottom: 20px;
    }

    .bracket-size-options {
        gap: 12px;
    }

    .size-option {
        padding: 15px 30px;
        font-size: 20px;
    }

    .generate-btn {
        padding: 12px 40px;
        font-size: 16px;
    }

    .bracket-container {
        padding: 15px;
        margin-top: 20px;
    }

    .bracket {
        gap: 25px;
    }

    .round {
        min-height: 550px;
    }

    .round-title {
        font-size: 18px;
        margin-bottom: 15px;
    }

    .player {
        min-width: 160px;
        max-width: 220px;
        padding: 12px 16px;
        font-size: 14px;
    }

    .trophy {
        font-size: 60px;
        margin: 15px 0;
    }

    .winner-name {
        font-size: 20px;
        padding: 15px 30px;
    }

    .third-place-container {
        padding: 20px;
        margin-top: 30px;
        max-width: 90%;
    }

    .third-place-title {
        font-size: 20px;
        margin-bottom: 20px;
    }

    .third-place-bracket {
        min-width: 100%;
        padding: 15px;
    }
}

/* Mobile Landscape - 640px and below */
@media (max-width: 640px) {
    body {
        padding: 8px;
    }

    .header {
        padding: 12px;
    }

    .back-btn {
        padding: 8px 16px;
        font-size: 14px;
    }

    .setup-container {
        padding: 20px;
    }

    .setup-container h2 {
        font-size: 20px;
    }

    .bracket-size-options {
        gap: 10px;
        flex-direction: column;
    }

    .size-option {
        padding: 12px 25px;
        font-size: 18px;
        width: 100%;
    }

    .generate-btn {
        padding: 10px 35px;
        font-size: 15px;
        width: 100%;
    }

    .bracket-container {
        padding: 10px;
    }

    .bracket {
        gap: 20px;
    }

    .round {
        min-height: 400px;
    }

    .round-title {
        font-size: 14px;
        margin-bottom: 12px;
    }

    .match {
        gap: 4px;
        margin: 8px 0;
    }

    .player {
        min-width: 120px;
        max-width: 180px;
        padding: 9px 12px;
        font-size: 11px;
        border-radius: 6px;
    }

    .trophy {
        font-size: 50px;
        margin: 12px 0;
    }

    .winner-name {
        font-size: 18px;
        padding: 12px 25px;
        border-radius: 10px;
    }

    .info-text {
        font-size: 12px;
        margin-top: 15px;
    }

    .third-place-container {
        padding: 15px;
        margin-top: 25px;
    }

    .third-place-title {
        font-size: 18px;
        margin-bottom: 15px;
        flex-direction: column;
        gap: 8px;
    }

    .third-place-bracket {
        padding: 12px;
    }
}

/* Mobile Portrait - 480px and below */
@media (max-width: 480px) {
    body {
        padding: 6px;
    }

    .header {
        padding: 10px;
        border-radius: 12px;
    }

    .back-btn {
        padding: 7px 14px;
        font-size: 13px;
        margin-bottom: 15px;
    }

    .setup-container {
        padding: 15px;
        border-radius: 12px;
    }

    .setup-container h2 {
        font-size: 18px;
        margin-bottom: 15px;
    }

    .bracket-size-options {
        gap: 8px;
    }

    .size-option {
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 10px;
    }

    .generate-btn {
        padding: 9px 30px;
        font-size: 14px;
        border-radius: 10px;
    }

    .bracket-container {
        padding: 8px;
        margin-top: 15px;
    }

    .bracket {
        gap: 15px;
    }

    .round {
        min-height: 350px;
    }

    .round-title {
        font-size: 13px;
        margin-bottom: 10px;
    }

    .match {
        gap: 3px;
        margin: 6px 0;
    }

    .player {
        min-width: 100px;
        max-width: 150px;
        padding: 8px 10px;
        font-size: 10px;
        border-radius: 6px;
        border: 2px solid transparent;
    }

    .trophy {
        font-size: 40px;
        margin: 10px 0;
    }

    .winner-name {
        font-size: 16px;
        padding: 10px 20px;
        border-radius: 8px;
    }

    .info-text {
        font-size: 11px;
        margin-top: 12px;
    }

    .third-place-container {
        padding: 12px;
        margin-top: 20px;
        border-radius: 12px;
    }

    .third-place-title {
        font-size: 16px;
        margin-bottom: 12px;
    }

    .third-place-bracket {
        padding: 10px;
        border-radius: 10px;
    }
}

/* Very Small Mobile - 360px and below */
@media (max-width: 360px) {
    body {
        padding: 5px;
    }

    .header {
        padding: 8px;
    }

    .back-btn {
        padding: 6px 12px;
        font-size: 12px;
    }

    .setup-container {
        padding: 12px;
    }

    .setup-container h2 {
        font-size: 16px;
    }

    .size-option {
        padding: 8px 16px;
        font-size: 14px;
    }

    .generate-btn {
        padding: 8px 25px;
        font-size: 13px;
    }

    .bracket {
        gap: 12px;
    }

    .round {
        min-height: 300px;
    }

    .round-title {
        font-size: 12px;
        margin-bottom: 8px;
    }

    .match {
        gap: 3px;
        margin: 5px 0;
    }

    .player {
        min-width: 85px;
        max-width: 130px;
        padding: 7px 8px;
        font-size: 9px;
        border-radius: 5px;
    }

    .trophy {
        font-size: 35px;
        margin: 8px 0;
    }

    .winner-name {
        font-size: 14px;
        padding: 8px 16px;
        border-radius: 7px;
    }

    .info-text {
        font-size: 10px;
        margin-top: 10px;
    }

    .third-place-container {
        padding: 10px;
        margin-top: 15px;
    }

    .third-place-title {
        font-size: 14px;
        margin-bottom: 10px;
    }

    .third-place-bracket {
        padding: 8px;
    }
}

/* Horizontal Scroll Indicator for Brackets */
.bracket-container::after {
    content: '← Geser untuk melihat →';
    display: block;
    text-align: center;
    color: rgba(255, 255, 255, 0.5);
    font-size: 12px;
    margin-top: 10px;
    font-style: italic;
}

@media (min-width: 1400px) {
    .bracket-container::after {
        display: none;
    }
}

/* Smooth scrolling for bracket container */
.bracket-container {
    scroll-behavior: smooth;
}

/* Custom scrollbar for bracket container */
.bracket-container::-webkit-scrollbar {
    height: 8px;
}

.bracket-container::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}

.bracket-container::-webkit-scrollbar-thumb {
    background: rgba(253, 203, 110, 0.5);
    border-radius: 10px;
}

.bracket-container::-webkit-scrollbar-thumb:hover {
    background: rgba(253, 203, 110, 0.7);
}

/* Touch-friendly hover states for mobile */
@media (hover: none) and (pointer: coarse) {
    .player:hover:not(.empty) {
        transform: none;
    }

    .player:active:not(.empty) {
        transform: scale(0.98);
        box-shadow: 0 3px 10px rgba(255, 165, 2, 0.4);
    }

    .size-option:hover {
        transform: none;
    }

    .size-option:active {
        transform: scale(0.95);
    }

    .generate-btn:hover {
        transform: none;
    }

    .generate-btn:active {
        transform: scale(0.98);
    }
}
</style>
    </head>
    <body>
        <div class="container">
            <a href="detail.php?action=scorecard&resource=index&kegiatan_id=<?= $kegiatan_id ?>&category_id=<?= $category_id ?>" class="back-btn">← Kembali</a>

            <div class="header">
                <h1>Tournament Eliminasi / Aduan</h1>
                <h3><?= htmlspecialchars($kategoriData['name']) ?></h3>
                <p><?= htmlspecialchars($kegiatanData['nama_kegiatan']) ?></p>
                <p style="margin-top: 10px; color: #74b9ff;">Total Peserta: <?= count($pesertaList) ?> orang</p>
            </div>

            <div class="setup-container" id="setupContainer">
                <h2>Pilih Jumlah Peserta Eliminasi / Aduan</h2>
                
                <div class="bracket-size-options">
                    <button class="size-option" onclick="selectBracketSize(16)" id="size16">16</button>
                    <button class="size-option" onclick="selectBracketSize(32)" id="size32">32</button>
                </div>

                <p class="info-text">
                    Pilih jumlah peserta untuk Eliminasi / Aduan
                </p>

                <button class="generate-btn" id="startBracketBtn" onclick="startBracket()" disabled style="margin-top: 30px;">
                    🏆 Masuk ke Eliminasi / Aduan
                </button>
            </div>

            <div class="bracket-container" id="bracketContainer">
                <div style="text-align: center; margin-bottom: 30px;">
                    <button class="generate-btn" id="generateBtn" onclick="generateBracket()">
                        🎲 Generate & Acak Eliminasi / Aduan
                    </button>
                    <button class="generate-btn" onclick="backToSetup()" style="background: linear-gradient(135deg, #636e72 0%, #2d3436 100%); margin-left: 10px;">
                        ← Kembali
                    </button>
                    <p class="info-text" style="margin-top: 15px;">
                        Klik tombol "Generate & Acak Eliminasi / Aduan" untuk mengacak posisi peserta secara random
                    </p>
                </div>
                
                <div id="bracketContent">
                    <!-- Bracket akan di-generate di sini -->
                </div>
                
                <div class="third-place-container" id="thirdPlaceSection">
                    <div class="third-place-title">
                        <span>🥉</span>
                        <span>PEREBUTAN JUARA 3</span>
                        <span>🥉</span>
                    </div>
                    <div class="third-place-bracket">
                        <div id="thirdPlaceMatch">
                            <div class="match">
                                <div class="player empty">Menunggu Semifinal</div>
                                <div class="player empty">Menunggu Semifinal</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            const pesertaData = <?= json_encode($pesertaList) ?>;
            let selectedSize = 0;
            let shuffledPeserta = [];
            let bracketData = {};
            let semifinalLosers = [];

            function selectBracketSize(size) {
                selectedSize = size;
                
                document.querySelectorAll('.size-option').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.getElementById('size' + size).classList.add('active');
                
                document.getElementById('startBracketBtn').disabled = false;
            }

            function startBracket() {
                if (selectedSize === 0) {
                    alert('Pilih jumlah peserta terlebih dahulu!');
                    return;
                }

                if (pesertaData.length < 2) {
                    alert('Minimal 2 peserta diperlukan untuk membuat bracket!');
                    return;
                }

                document.getElementById('setupContainer').style.display = 'none';
                document.getElementById('bracketContainer').style.display = 'block';

                showPlaceholderBracket();
            }

            function backToSetup() {
                if (confirm('Kembali ke setup akan mereset semua data bracket. Lanjutkan?')) {
                    document.getElementById('setupContainer').style.display = 'block';
                    document.getElementById('bracketContainer').style.display = 'none';
                    
                    document.getElementById('bracketContent').innerHTML = '';
                    document.getElementById('thirdPlaceMatch').innerHTML = '';
                    document.getElementById('thirdPlaceSection').style.display = 'none';
                    bracketData = {};
                    shuffledPeserta = [];
                    semifinalLosers = [];
                }
            }

            function showPlaceholderBracket() {
                if (selectedSize === 16) {
                    showPlaceholder16Bracket();
                } else {
                    showPlaceholder32Bracket();
                }
            }

            function showPlaceholder16Bracket() {
                const bracketHTML = `
                    <div class="bracket">
                        <div class="round">
                            <div class="round-title">Round of 16</div>
                            ${generatePlaceholderMatches(8)}
                        </div>
                        <div class="round">
                            <div class="round-title">Quarter Finals</div>
                            ${generatePlaceholderMatches(4)}
                        </div>
                        <div class="round">
                            <div class="round-title">Semi Finals</div>
                            ${generatePlaceholderMatches(2)}
                        </div>
                        <div class="round final-winner">
                            <div class="round-title">Finals</div>
                            <div class="trophy">🏆</div>
                            <div class="match">
                                <div class="player empty">Finalist 1</div>
                                <div class="player empty">Finalist 2</div>
                            </div>
                            <div class="winner-name" id="champion" style="margin-top: 30px; display: none;">Champion</div>
                        </div>
                    </div>
                `;
                document.getElementById('bracketContent').innerHTML = bracketHTML;
            }

            function showPlaceholder32Bracket() {
                const bracketHTML = `
                    <div class="bracket">
                        <div class="round">
                            <div class="round-title">Round of 32</div>
                            ${generatePlaceholderMatches(16)}
                        </div>
                        <div class="round">
                            <div class="round-title">Round of 16</div>
                            ${generatePlaceholderMatches(8)}
                        </div>
                        <div class="round">
                            <div class="round-title">Quarter Finals</div>
                            ${generatePlaceholderMatches(4)}
                        </div>
                        <div class="round">
                            <div class="round-title">Semi Finals</div>
                            ${generatePlaceholderMatches(2)}
                        </div>
                        <div class="round final-winner">
                            <div class="round-title">Finals</div>
                            <div class="trophy">🏆</div>
                            <div class="match">
                                <div class="player empty">Finalist 1</div>
                                <div class="player empty">Finalist 2</div>
                            </div>
                            <div class="winner-name" id="champion" style="margin-top: 30px; display: none;">Champion</div>
                        </div>
                    </div>
                `;
                document.getElementById('bracketContent').innerHTML = bracketHTML;
            }

            function generatePlaceholderMatches(numMatches) {
                let html = '';
                for (let i = 0; i < numMatches; i++) {
                    html += `
                        <div class="match">
                            <div class="player empty">TBD</div>
                            <div class="player empty">TBD</div>
                        </div>
                    `;
                }
                return html;
            }

            function shuffleArray(array) {
                const newArray = [...array];
                for (let i = newArray.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));
                    [newArray[i], newArray[j]] = [newArray[j], newArray[i]];
                }
                return newArray;
            }

            function generateBracket() {
                if (selectedSize === 0) {
                    alert('Pilih jumlah peserta terlebih dahulu!');
                    return;
                }

                if (pesertaData.length < 2) {
                    alert('Minimal 2 peserta diperlukan untuk membuat bracket!');
                    return;
                }

                shuffledPeserta = shuffleArray(pesertaData).slice(0, selectedSize);

                while (shuffledPeserta.length < selectedSize) {
                    shuffledPeserta.push({ id: null, nama_peserta: 'BYE', empty: true });
                }

                bracketData = {};
                semifinalLosers = [];
                shuffledPeserta.forEach((player, index) => {
                    bracketData[index] = {
                        player: player,
                        round: 1,
                        position: index
                    };
                });

                if (selectedSize === 16) {
                    generate16Bracket();
                } else {
                    generate32Bracket();
                }
                
                document.getElementById('thirdPlaceSection').style.display = 'block';
            }

            function generate16Bracket() {
                const bracketHTML = `
                    <div class="bracket">
                        <div class="round">
                            <div class="round-title">Round of 16</div>
                            ${generateMatches(0, 16, 1, 'r16')}
                        </div>
                        <div class="round">
                            <div class="round-title">Quarter Finals</div>
                            ${generateEmptyMatches(4, 2, 'qf')}
                        </div>
                        <div class="round">
                            <div class="round-title">Semi Finals</div>
                            ${generateEmptyMatches(2, 3, 'sf')}
                        </div>
                        <div class="round final-winner">
                            <div class="round-title">Finals</div>
                            <div class="trophy">🏆</div>
                            <div class="match" data-match="final">
                                <div class="player empty" data-slot="final-1">Finalist 1</div>
                                <div class="player empty" data-slot="final-2">Finalist 2</div>
                            </div>
                            <div class="winner-name" id="champion" style="margin-top: 30px; display: none;">Champion</div>
                        </div>
                    </div>
                `;
                document.getElementById('bracketContent').innerHTML = bracketHTML;
            }

            function generate32Bracket() {
                const bracketHTML = `
                    <div class="bracket">
                        <div class="round">
                            <div class="round-title">Round of 32</div>
                            ${generateMatches(0, 32, 1, 'r32')}
                        </div>
                        <div class="round">
                            <div class="round-title">Round of 16</div>
                            ${generateEmptyMatches(8, 2, 'r16')}
                        </div>
                        <div class="round">
                            <div class="round-title">Quarter Finals</div>
                            ${generateEmptyMatches(4, 3, 'qf')}
                        </div>
                        <div class="round">
                            <div class="round-title">Semi Finals</div>
                            ${generateEmptyMatches(2, 4, 'sf')}
                        </div>
                        <div class="round final-winner">
                            <div class="round-title">Finals</div>
                            <div class="trophy">🏆</div>
                            <div class="match" data-match="final">
                                <div class="player empty" data-slot="final-1">Finalist 1</div>
                                <div class="player empty" data-slot="final-2">Finalist 2</div>
                            </div>
                            <div class="winner-name" id="champion" style="margin-top: 30px; display: none;">Champion</div>
                        </div>
                    </div>
                `;
                document.getElementById('bracketContent').innerHTML = bracketHTML;
            }

            function generateMatches(start, end, round, prefix) {
                let html = '';
                let matchIndex = 0;
                
                for (let i = start; i < end; i += 2) {
                    const player1 = shuffledPeserta[i];
                    const player2 = shuffledPeserta[i + 1];
                    const matchId = `${prefix}-m${matchIndex}`;
                    
                    html += `
                        <div class="match" data-match="${matchId}">
                            <div class="player ${player1.empty ? 'empty' : ''}" 
                                 data-slot="${matchId}-1"
                                 data-player-index="${i}"
                                 data-player-id="${player1.id || ''}"
                                 onclick="${player1.empty ? '' : `selectWinner('${matchId}', 1, ${i})`}">
                                ${player1.nama_peserta}
                            </div>
                            <div class="player ${player2.empty ? 'empty' : ''}" 
                                 data-slot="${matchId}-2"
                                 data-player-index="${i + 1}"
                                 data-player-id="${player2.id || ''}"
                                 onclick="${player2.empty ? '' : `selectWinner('${matchId}', 2, ${i + 1})`}">
                                ${player2.nama_peserta}
                            </div>
                        </div>
                    `;
                    matchIndex++;
                }
                return html;
            }

            function generateEmptyMatches(count, round, prefix) {
                let html = '';
                for (let i = 0; i < count; i++) {
                    const matchId = `${prefix}-m${i}`;
                    html += `
                        <div class="match" data-match="${matchId}">
                            <div class="player empty" data-slot="${matchId}-1">TBD</div>
                            <div class="player empty" data-slot="${matchId}-2">TBD</div>
                        </div>
                    `;
                }
                return html;
            }

            function selectWinner(matchId, slot, playerIndex) {
                const player = shuffledPeserta[playerIndex];
                
                if (player.empty) return;

                const matchElement = document.querySelector(`[data-match="${matchId}"]`);
                const player1Element = matchElement.querySelector(`[data-slot="${matchId}-1"]`);
                const player2Element = matchElement.querySelector(`[data-slot="${matchId}-2"]`);

                player1Element.classList.remove('winner');
                player2Element.classList.remove('winner');

                const winnerElement = slot === 1 ? player1Element : player2Element;
                winnerElement.classList.add('winner');

                advanceWinner(matchId, player, playerIndex);
            }

            function selectWinnerNext(matchId, slot) {
                const matchElement = document.querySelector(`[data-match="${matchId}"]`);
                const slotElement = matchElement.querySelector(`[data-slot="${matchId}-${slot}"]`);
                
                if (slotElement.classList.contains('empty')) {
                    alert('Pemain belum ditentukan untuk slot ini!');
                    return;
                }

                const player1Element = matchElement.querySelector(`[data-slot="${matchId}-1"]`);
                const player2Element = matchElement.querySelector(`[data-slot="${matchId}-2"]`);
                player1Element.classList.remove('winner');
                player2Element.classList.remove('winner');

                slotElement.classList.add('winner');

                const playerName = slotElement.textContent.trim();
                const playerIndex = slotElement.getAttribute('data-player-index');
                const playerId = slotElement.getAttribute('data-player-id');
                
                if (playerIndex) {
                    const player = shuffledPeserta[parseInt(playerIndex)];
                    advanceWinner(matchId, player, parseInt(playerIndex));
                } else {
                    const player = { 
                        id: playerId, 
                        nama_peserta: playerName 
                    };
                    advanceWinner(matchId, player, null);
                }
            }

            function advanceWinner(matchId, player, playerIndex) {
                let nextMatchId, nextSlot;

                if (matchId.startsWith('r16-m')) {
                    const matchNum = parseInt(matchId.split('m')[1]);
                    nextMatchId = `qf-m${Math.floor(matchNum / 2)}`;
                    nextSlot = (matchNum % 2) + 1;
                } else if (matchId.startsWith('qf-m')) {
                    const matchNum = parseInt(matchId.split('m')[1]);
                    nextMatchId = `sf-m${Math.floor(matchNum / 2)}`;
                    nextSlot = (matchNum % 2) + 1;
                } else if (matchId.startsWith('sf-m')) {
                    const matchNum = parseInt(matchId.split('m')[1]);
                    
                    const matchElement = document.querySelector(`[data-match="${matchId}"]`);
                    const player1Element = matchElement.querySelector(`[data-slot="${matchId}-1"]`);
                    const player2Element = matchElement.querySelector(`[data-slot="${matchId}-2"]`);
                    
                    const loserElement = player1Element.classList.contains('winner') ? player2Element : player1Element;
                    const loserName = loserElement.textContent.trim();
                    const loserId = loserElement.getAttribute('data-player-id');
                    
                    if (loserName !== 'TBD' && !semifinalLosers.some(l => l.id === loserId)) {
                        semifinalLosers.push({
                            id: loserId,
                            nama_peserta: loserName,
                            index: loserElement.getAttribute('data-player-index')
                        });
                        
                        updateThirdPlaceMatch();
                    }
                    
                    nextMatchId = 'final';
                    nextSlot = matchNum + 1;
                } else if (matchId.startsWith('r32-m')) {
                    const matchNum = parseInt(matchId.split('m')[1]);
                    nextMatchId = `r16-m${Math.floor(matchNum / 2)}`;
                    nextSlot = (matchNum % 2) + 1;
                }

                if (nextMatchId) {
                    const nextSlotElement = document.querySelector(`[data-slot="${nextMatchId}-${nextSlot}"]`);
                    if (nextSlotElement) {
                        nextSlotElement.textContent = player.nama_peserta;
                        nextSlotElement.classList.remove('empty');
                        nextSlotElement.setAttribute('data-player-index', playerIndex !== null ? playerIndex : '');
                        nextSlotElement.setAttribute('data-player-id', player.id || '');
                        
                        nextSlotElement.onclick = function() { 
                            selectWinnerNext(nextMatchId, nextSlot); 
                        };
                        
                        if (nextMatchId === 'final') {
                            const finalMatch = document.querySelector(`[data-match="final"]`);
                            const finalist1 = finalMatch.querySelector(`[data-slot="final-1"]`);
                            const finalist2 = finalMatch.querySelector(`[data-slot="final-2"]`);
                            
                            if (!finalist1.classList.contains('empty') && !finalist2.classList.contains('empty')) {
                                finalist1.onclick = function() { 
                                    selectFinalWinner(1);
                                };
                                finalist2.onclick = function() { 
                                    selectFinalWinner(2);
                                };
                            }
                        }
                    }
                }
            }

            function selectFinalWinner(slot) {
                const finalMatch = document.querySelector(`[data-match="final"]`);
                const finalist1 = finalMatch.querySelector(`[data-slot="final-1"]`);
                const finalist2 = finalMatch.querySelector(`[data-slot="final-2"]`);
                
                if (finalist1.classList.contains('empty') || finalist2.classList.contains('empty')) {
                    alert('Kedua finalist harus sudah ditentukan!');
                    return;
                }
                
                finalist1.classList.remove('winner');
                finalist2.classList.remove('winner');
                
                const winnerElement = slot === 1 ? finalist1 : finalist2;
                winnerElement.classList.add('winner');
                
                const championName = winnerElement.textContent.trim();
                declareChampion(championName);
            }

            function updateThirdPlaceMatch() {
                if (semifinalLosers.length === 2) {
                    const thirdPlaceMatch = document.getElementById('thirdPlaceMatch');
                    thirdPlaceMatch.innerHTML = `
                        <div class="match" data-match="third-place" style="margin: 0 auto;">
                            <div class="player" 
                                 data-slot="third-1"
                                 data-player-id="${semifinalLosers[0].id}"
                                 data-player-index="${semifinalLosers[0].index}"
                                 onclick="selectThirdPlace(0)"
                                 style="background: linear-gradient(135deg, #cd7f32 0%, #b87333 100%); color: white;">
                                ${semifinalLosers[0].nama_peserta}
                            </div>
                            <div class="player" 
                                 data-slot="third-2"
                                 data-player-id="${semifinalLosers[1].id}"
                                 data-player-index="${semifinalLosers[1].index}"
                                 onclick="selectThirdPlace(1)"
                                 style="background: linear-gradient(135deg, #cd7f32 0%, #b87333 100%); color: white;">
                                ${semifinalLosers[1].nama_peserta}
                            </div>
                        </div>
                    `;
                    
                    console.log('Third place match updated:', semifinalLosers);
                }
            }

            function selectThirdPlace(index) {
                const matchElement = document.querySelector(`[data-match="third-place"]`);
                if (!matchElement) {
                    alert('Match element tidak ditemukan!');
                    return;
                }
                
                const player1Element = matchElement.querySelector(`[data-slot="third-1"]`);
                const player2Element = matchElement.querySelector(`[data-slot="third-2"]`);
                
                if (!player1Element || !player2Element) {
                    alert('Player elements tidak ditemukan!');
                    return;
                }
                
                player1Element.classList.remove('winner');
                player2Element.classList.remove('winner');
                
                const winnerElement = index === 0 ? player1Element : player2Element;
                const loserElement = index === 0 ? player2Element : player1Element;
                winnerElement.classList.add('winner');
                
                const thirdPlaceWinner = semifinalLosers[index];
                const thirdPlaceLoser = semifinalLosers[index === 0 ? 1 : 0];
                
                // Save to database
                saveMatchResult('third-place', thirdPlaceWinner.id, thirdPlaceLoser.id);
                
                setTimeout(() => {
                    alert('🥉 Juara 3: ' + thirdPlaceWinner.nama_peserta + '\n\nSelamat atas pencapaian luar biasa!');
                }, 300);
            }

            function saveMatchResult(matchId, winnerId, loserId) {
                if (!winnerId || !loserId) {
                    console.log('Skipping save - missing IDs:', {matchId, winnerId, loserId});
                    return;
                }
                
                const formData = new FormData();
                formData.append('save_match_result', '1');
                formData.append('match_id', matchId);
                formData.append('winner_id', winnerId);
                formData.append('loser_id', loserId);
                formData.append('kegiatan_id', <?= $kegiatan_id ?>);
                formData.append('category_id', <?= $category_id ?>);
                formData.append('scoreboard_id', <?= $scoreboard_id ?>);
                formData.append('bracket_size', selectedSize);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Match result saved:', data);
                })
                .catch(error => {
                    console.error('Error saving match result:', error);
                });
            }

            function saveChampion(championId, runnerUpId, thirdPlaceId) {
                const formData = new FormData();
                formData.append('save_champion', '1');
                formData.append('champion_id', championId);
                formData.append('runner_up_id', runnerUpId);
                if (thirdPlaceId) {
                    formData.append('third_place_id', thirdPlaceId);
                }
                formData.append('kegiatan_id', <?= $kegiatan_id ?>);
                formData.append('category_id', <?= $category_id ?>);
                formData.append('scoreboard_id', <?= $scoreboard_id ?>);
                formData.append('bracket_size', selectedSize);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Champion saved:', data);
                })
                .catch(error => {
                    console.error('Error saving champion:', error);
                });
            }

            function declareChampion(championName) {
                const championElement = document.getElementById('champion');
                championElement.textContent = '🏆 ' + championName + ' 🏆';
                championElement.style.display = 'block';
                
                // Get champion and runner-up IDs
                const finalMatch = document.querySelector(`[data-match="final"]`);
                const finalist1 = finalMatch.querySelector(`[data-slot="final-1"]`);
                const finalist2 = finalMatch.querySelector(`[data-slot="final-2"]`);
                
                const championId = finalist1.classList.contains('winner') ? 
                                  finalist1.getAttribute('data-player-id') : 
                                  finalist2.getAttribute('data-player-id');
                                  
                const runnerUpId = finalist1.classList.contains('winner') ? 
                                  finalist2.getAttribute('data-player-id') : 
                                  finalist1.getAttribute('data-player-id');
                
                // Get third place if exists
                let thirdPlaceId = null;
                const thirdPlaceMatch = document.querySelector(`[data-match="third-place"]`);
                if (thirdPlaceMatch) {
                    const thirdWinner = thirdPlaceMatch.querySelector('.player.winner');
                    if (thirdWinner) {
                        thirdPlaceId = thirdWinner.getAttribute('data-player-id');
                    }
                }
                
                // Save to database
                saveMatchResult('final', championId, runnerUpId);
                saveChampion(championId, runnerUpId, thirdPlaceId);
                
                setTimeout(() => {
                    let message = '🎉 Selamat kepada juara: ' + championName + '! 🎉';
                    if (thirdPlaceId) {
                        const thirdPlaceName = semifinalLosers.find(p => p.id == thirdPlaceId)?.nama_peserta;
                        if (thirdPlaceName) {
                            const runnerUpName = finalist1.classList.contains('winner') ? 
                                                finalist2.textContent.trim() : 
                                                finalist1.textContent.trim();
                            message += '\n\n🥈 Juara 2: ' + runnerUpName;
                            message += '\n🥉 Juara 3: ' + thirdPlaceName;
                        }
                    }
                    alert(message);
                }, 500);
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}


// ============================================
// HANDLER UNTUK SCORECARD SETUP
// ============================================
if (isset($_GET['action']) && $_GET['action'] == 'scorecard') {
    try {
        include 'panggil.php';
    } catch (Exception $e) {
        die("Error koneksi database: " . $e->getMessage());
    }

    $kegiatan_id = isset($_GET['kegiatan_id']) ? intval($_GET['kegiatan_id']) : null;
    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

    if (!$kegiatan_id || !$category_id) {
        die("Parameter kegiatan_id dan category_id harus diisi.");
    }

    $mysql_table_score_board = mysqli_query($conn, "SELECT * FROM score_boards WHERE kegiatan_id=".$kegiatan_id." AND category_id=".$category_id." ORDER BY created DESC");
    if(isset($_GET['scoreboard'])) {
        $mysql_data_score = mysqli_query($conn, "SELECT * FROM score WHERE kegiatan_id=".$kegiatan_id." AND category_id=".$category_id." AND score_board_id=".$_GET['scoreboard']." ");
    }
    
    // Ambil data kegiatan
    $kegiatanData = [];
    try {
        $queryKegiatan = "SELECT id, nama_kegiatan FROM kegiatan WHERE id = ?";
        $stmtKegiatan = $conn->prepare($queryKegiatan);
        $stmtKegiatan->bind_param("i", $kegiatan_id);
        $stmtKegiatan->execute();
        $resultKegiatan = $stmtKegiatan->get_result();
        
        if ($resultKegiatan->num_rows > 0) {
            $kegiatanData = $resultKegiatan->fetch_assoc();
        } else {
            die("Kegiatan tidak ditemukan.");
        }
        $stmtKegiatan->close();
    } catch (Exception $e) {
        die("Error mengambil data kegiatan: " . $e->getMessage());
    }

    // Ambil data kategori
    $kategoriData = [];
    try {
        $queryKategori = "SELECT id, name FROM categories WHERE id = ?";
        $stmtKategori = $conn->prepare($queryKategori);
        $stmtKategori->bind_param("i", $category_id);
        $stmtKategori->execute();
        $resultKategori = $stmtKategori->get_result();
        
        if ($resultKategori->num_rows > 0) {
            $kategoriData = $resultKategori->fetch_assoc();
        } else {
            die("Kategori tidak ditemukan.");
        }
        $stmtKategori->close();
    } catch (Exception $e) {
        die("Error mengambil data kategori: " . $e->getMessage());
    }

    // Ambil data peserta berdasarkan kegiatan dan kategori
    $pesertaList = [];
    $peserta_score = [];
    try {
        $queryPeserta = "
            SELECT 
                p.id,
                p.nama_peserta,
                p.jenis_kelamin,
                c.name as category_name
            FROM peserta p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.kegiatan_id = ? AND p.category_id = ?
            ORDER BY p.nama_peserta ASC
        ";
        $stmtPeserta = $conn->prepare($queryPeserta);
        $stmtPeserta->bind_param("ii", $kegiatan_id, $category_id);
        $stmtPeserta->execute();
        $resultPeserta = $stmtPeserta->get_result();
        
        while ($row = $resultPeserta->fetch_assoc()) {
            $pesertaList[] = $row;
        }

        if(isset($_GET['scoreboard'])) {
            foreach($pesertaList as $a) {
                $mysql_score_total = mysqli_query($conn, "SELECT * FROM score WHERE kegiatan_id=".$kegiatan_id." AND category_id=".$category_id." AND score_board_id =".$_GET['scoreboard']." AND peserta_id=".$a['id']);
                $score = 0;
                $x_score = 0;
                while($b = mysqli_fetch_array($mysql_score_total)) {
                    if($b['score'] == 'm') {
                        $score = $score + 0;
                    } else if($b['score'] == 'x') {
                        $score = $score + 10;
                        $x_score = $x_score + 1;
                    } else {
                        $score = $score + (int)$b['score'];
                    }
                }
                $peserta_score[] = ['id' => $a['id'], 'total_score' => $score, 'total_x' => $x_score];
            }
        }

        $stmtPeserta->close();
    } catch (Exception $e) {
        die("Error mengambil data peserta: " . $e->getMessage());
    }

    if(isset($_POST['create'])) {
        $create_score_board = mysqli_query($conn,"INSERT INTO `score_boards` 
                                                    (`kegiatan_id`, `category_id`, `jumlah_sesi`, `jumlah_anak_panah`, `created`) 
                                                    VALUES 
                                                    ('".$kegiatan_id."', '".$category_id."', '".$_POST['jumlahSesi']."', '".$_POST['jumlahPanah']."', '".$_POST['local_time']."');");
        header("Location: detail.php?action=scorecard&resource=index&kegiatan_id=".$kegiatan_id."&category_id=".$category_id);
    }

    if(isset($_POST['save_score'])) {
        header("Content-Type: application/json; charset=UTF-8");

        $nama = !empty($_POST['nama']) ? $_POST['nama'] : "Anonim";
        $checkScore = mysqli_query($conn, "SELECT * FROM score WHERE kegiatan_id='".$kegiatan_id."' AND category_id='".$category_id."' AND score_board_id='".$_GET['scoreboard']."' AND peserta_id='".$_POST['peserta_id']."' AND arrow='".$_POST['arrow']."' AND session='".$_POST['session']."'");
        if (!$checkScore) {
            echo json_encode([
                "status" => "error",
                "message" => "Query Error: " . mysqli_error($conn)
            ]);
            exit;
        }
        $fetch_checkScore = mysqli_fetch_assoc($checkScore);

        if($fetch_checkScore) {
            $message = "Score updated";
            if(empty($_POST['score'])) {
                $score = mysqli_query($conn,"DELETE FROM score WHERE id='".$fetch_checkScore['id']."'");
            } else {
                $score = mysqli_query($conn,"UPDATE score SET score='".$_POST['score']."' WHERE id='".$fetch_checkScore['id']."'");
            }
        } else {
            if(!empty($_POST['score'])) {
                $score = mysqli_query($conn,"INSERT INTO `score` 
                                                    (`kegiatan_id`, `category_id`, `score_board_id`, `peserta_id`, `arrow`, `session`, `score`) 
                                                    VALUES 
                                                    ('".$kegiatan_id."', '".$category_id."', '".$_GET['scoreboard']."', '".$_POST['peserta_id']."', '".$_POST['arrow']."','".$_POST['session']."','".$_POST['score']."');");
                $message = "Score added";
            } else {
                $message = "Empty score - no action";
            }
        }

        echo json_encode([
            "status" => "success",
            "message" => $message
        ]);
        exit;
    }

    if(isset($_GET['delete_score_board'])) {
        $delete_score_board = mysqli_query($conn,'DELETE FROM `score_boards` WHERE `score_boards`.`id` ='.$_GET['delete_score_board']);
        header("Location: detail.php?action=scorecard&resource=index&kegiatan_id=".$kegiatan_id."&category_id=".$category_id);
    }

    if(isset($_GET['scoreboard'])) { 
        $sql_show_score_board = mysqli_query($conn,'SELECT * FROM `score_boards` WHERE `score_boards`.`id` ='.$_GET['scoreboard']);
        $show_score_board = mysqli_fetch_assoc($sql_show_score_board);
    }

    $conn->close();
    
    // BAGIAN SCORECARD SETUP
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Setup Scorecard Panahan - <?= htmlspecialchars($kategoriData['name']) ?></title>
        <!-- Tambahan CDN untuk Export Excel -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #2D3436 0%, #636e72 100%);
    min-height: 100vh;
    padding: 20px;
    color: white;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
}

.back-btn {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    margin-bottom: 20px;
    transition: background 0.3s ease;
    text-decoration: none;
}

.back-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.setup-form {
    background: rgba(45, 52, 54, 0.95);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    margin-bottom: 20px;
}

.header {
    text-align: center;
    margin-bottom: 30px;
}

.logo {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
    border-radius: 15px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
}

.title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 10px;
}

.subtitle {
    font-size: 14px;
    color: #ddd;
}

.category-info {
    background: rgba(116, 185, 255, 0.1);
    border: 1px solid rgba(116, 185, 255, 0.3);
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 25px;
    text-align: center;
}

.category-name {
    font-size: 16px;
    font-weight: 600;
    color: #74b9ff;
    margin-bottom: 5px;
}

.event-name {
    font-size: 14px;
    color: #ddd;
}

.peserta-count {
    font-size: 18px;
    font-weight: 700;
    color: #fdcb6e;
    margin-top: 10px;
}

.form-group {
    margin-bottom: 25px;
}

.form-label {
    display: block;
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 10px;
    color: #74b9ff;
}

.form-input {
    width: 100%;
    background: rgba(116, 185, 255, 0.1);
    border: 1px solid rgba(116, 185, 255, 0.3);
    border-radius: 12px;
    padding: 15px;
    color: white;
    font-size: 18px;
    text-align: center;
    transition: all 0.3s ease;
}

.form-input:focus {
    outline: none;
    border-color: #74b9ff;
    background: rgba(116, 185, 255, 0.15);
    box-shadow: 0 0 0 3px rgba(116, 185, 255, 0.1);
}

.create-btn {
    width: 100%;
    background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
    border: none;
    border-radius: 15px;
    padding: 16px;
    font-size: 16px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.create-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(253, 203, 110, 0.3);
}

.create-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-warning {
    background: rgba(255, 193, 7, 0.1);
    border: 1px solid rgba(255, 193, 7, 0.3);
    color: #ffc107;
}

.scorecard-container {
    background: rgba(45, 52, 54, 0.95);
    border-radius: 20px;
    padding: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    display: none;
    max-width: none;
    width: 100%;
}

.scorecard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    background: rgba(116, 185, 255, 0.1);
    padding: 15px;
    border-radius: 12px;
    flex-wrap: wrap;
    gap: 10px;
}

.category-header-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.category-icon {
    width: 30px;
    height: 30px;
    background: #fdcb6e;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.scorecard-title {
    text-align: center;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 15px;
    background: rgba(0, 0, 0, 0.3);
    padding: 10px;
    border-radius: 8px;
}

.player-section {
    margin-bottom: 40px;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.player-header {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 20px;
    color: white;
    text-align: center;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
}

.score-table-container {
    overflow-x: auto;
    margin: 20px 0;
    border-radius: 12px;
    background: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.score-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 14px;
    background: white;
    min-width: 600px;
}

.score-table th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 8px;
    text-align: center;
    font-weight: 600;
    border: 1px solid rgba(255, 255, 255, 0.3);
    position: sticky;
    top: 0;
    z-index: 5;
}

.score-table td {
    padding: 8px;
    border: 1px solid #e1e8ed;
    text-align: center;
    vertical-align: middle;
}

.session-row:nth-child(even) {
    background: rgba(79, 172, 254, 0.05);
}

.session-row:hover {
    background: rgba(79, 172, 254, 0.1);
}

.session-label {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    font-size: 14px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    min-width: 60px;
}

.arrow-input {
    width: 50px;
    height: 40px;
    background: transparent;
    border: 2px solid transparent;
    border-radius: 6px;
    padding: 8px 4px;
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.arrow-input:hover {
    background: rgba(79, 172, 254, 0.1);
    border-color: #4facfe;
}

.arrow-input:focus {
    outline: none;
    background: white;
    border-color: #4facfe;
    box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.2);
}

.arrow-input:disabled {
    background: #f8f9fa;
    color: #666;
    cursor: not-allowed;
}

.total-cell {
    background: rgba(253, 203, 110, 0.1);
    font-weight: 700;
    color: #e17055;
}

.end-cell {
    background: rgba(0, 184, 148, 0.1);
    color: #00b894;
    font-weight: 700;
}

.arrow-input[value="x"],
.arrow-input[value="X"] {
    background: rgba(40, 167, 69, 0.1);
    border-color: #28a745;
    color: #28a745;
    font-weight: 700;
}

.arrow-input[value="m"],
.arrow-input[value="M"] {
    background: rgba(220, 53, 69, 0.1);
    border-color: #dc3545;
    color: #dc3545;
    font-weight: 700;
}

.arrow-input[value="10"] {
    background: rgba(40, 167, 69, 0.1);
    border-color: #28a745;
    color: #28a745;
    font-weight: 700;
}

.arrow-input[value="9"],
.arrow-input[value="8"] {
    background: rgba(255, 193, 7, 0.1);
    border-color: #ffc107;
    color: #856404;
    font-weight: 600;
}

.total-summary {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    margin-top: 20px;
    color: white;
    box-shadow: 0 4px 15px rgba(67, 233, 123, 0.3);
}

.grand-total {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 5px;
}

.x-count {
    font-size: 16px;
    font-weight: 600;
    opacity: 0.9;
}

.edit-btn {
    background: rgba(116, 185, 255, 0.2);
    border: 1px solid rgba(116, 185, 255, 0.5);
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    margin-top: 20px;
    width: 100%;
}

.edit-btn:hover {
    background: rgba(116, 185, 255, 0.3);
}

.table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    margin: 1rem 0;
    border-radius: 12px;
    background: white;
    box-shadow: 0 8px 24px rgba(22, 28, 37, 0.08);
    padding: 0;
}

.styled-table {
    width: 100%;
    border-collapse: collapse;
    font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    font-size: 14px;
    color: #1f2937;
    min-width: 640px;
    background: white;
}

.styled-table thead th {
    text-align: left;
    padding: 16px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 2;
    border: none;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
}

.styled-table thead th:first-child {
    border-radius: 12px 0 0 0;
}

.styled-table thead th:last-child {
    border-radius: 0 12px 0 0;
}

.styled-table tbody td {
    padding: 14px 20px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(15, 23, 42, 0.06);
}

.styled-table tbody tr {
    transition: all 0.2s ease;
}

.styled-table tbody tr:nth-child(even) {
    background: rgba(102, 126, 234, 0.02);
}

.styled-table tbody tr:hover {
    background: rgba(102, 126, 234, 0.08);
    transform: scale(1.01);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.styled-table tbody tr:last-child td {
    border-bottom: none;
}

.styled-table tbody td:first-child {
    width: 64px;
    text-align: center;
    font-weight: 600;
    color: #667eea;
}

.styled-table tbody td:nth-child(2) {
    color: #6b7280;
    font-weight: 500;
}

.styled-table tbody td:nth-child(3),
.styled-table tbody td:nth-child(4) {
    font-weight: 600;
    color: #374151;
}

.styled-table tbody td:last-child {
    white-space: nowrap;
}

.btn {
    display: inline-block;
    padding: 8px 14px;
    font-size: 12px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    margin-right: 6px;
    margin-bottom: 4px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    text-align: center;
}

.btn:last-child {
    margin-right: 0;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn:active {
    transform: translateY(0);
}

.styled-table .btn:nth-child(1) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.styled-table .btn:nth-child(1):hover {
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.styled-table .btn:nth-child(2) {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.styled-table .btn:nth-child(2):hover {
    box-shadow: 0 4px 12px rgba(79, 172, 254, 0.4);
}

.styled-table .btn:nth-child(3) {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
}

.styled-table .btn:nth-child(3):hover {
    box-shadow: 0 4px 12px rgba(67, 233, 123, 0.4);
}

.styled-table .btn:nth-child(4) {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.styled-table .btn:nth-child(4):hover {
    box-shadow: 0 4px 12px rgba(250, 112, 154, 0.4);
}

.header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
}

.add-link {
    text-decoration: none;
    background: #2563eb;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 14px;
}

.header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

h3 {
    margin: 0;
    color: white;
}

.table-container {
    position: relative;
    width: 100%;
}

.table-loading {
    opacity: 0.5;
    pointer-events: none;
}

.table-empty {
    text-align: center;
    padding: 40px 20px;
    color: #6b7280;
    font-style: italic;
}

.table-empty::before {
    content: "📋";
    display: block;
    font-size: 48px;
    margin-bottom: 16px;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.styled-table tbody tr {
    animation: fadeIn 0.3s ease-in-out;
}

.styled-table tbody tr:nth-child(1) { animation-delay: 0.05s; }
.styled-table tbody tr:nth-child(2) { animation-delay: 0.1s; }
.styled-table tbody tr:nth-child(3) { animation-delay: 0.15s; }
.styled-table tbody tr:nth-child(4) { animation-delay: 0.2s; }
.styled-table tbody tr:nth-child(5) { animation-delay: 0.25s; }

/* TAMBAHAN CSS UNTUK TOMBOL EXPORT */
.export-btn {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.export-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.header-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

/* ============================================
   RESPONSIVE BREAKPOINTS
   ============================================ */

/* Tablet - 1024px and below */
@media (max-width: 1024px) {
    .container {
        max-width: 100%;
        padding: 0 15px;
    }

    .setup-form,
    .scorecard-container {
        padding: 20px;
    }

    .player-section {
        padding: 20px;
    }
}

/* Tablet - 768px and below */
@media (max-width: 768px) {
    body {
        padding: 10px;
    }

    .container {
        padding: 0 5px;
    }

    .setup-form,
    .scorecard-container {
        padding: 12px;
        border-radius: 12px;
    }

    .logo {
        width: 45px;
        height: 45px;
        font-size: 22px;
    }

    .title {
        font-size: 17px;
    }

    .subtitle {
        font-size: 12px;
    }

    .scorecard-header {
        flex-direction: column;
        text-align: center;
        padding: 10px;
        gap: 8px;
    }

    .category-header-info {
        justify-content: center;
        flex-wrap: wrap;
    }

    .player-section {
        padding: 12px;
        margin-bottom: 25px;
    }

    .player-header {
        font-size: 15px;
        padding: 10px;
    }

    .score-table {
        font-size: 11px;
        min-width: 420px;
    }
    
    .score-table th,
    .score-table td {
        padding: 5px 3px;
    }
    
    .arrow-input {
        width: 38px;
        height: 32px;
        padding: 5px 2px;
        font-size: 11px;
    }
    
    .grand-total {
        font-size: 19px;
    }

    .x-count {
        font-size: 13px;
    }

    .total-summary {
        padding: 12px;
    }

    .styled-table {
        font-size: 11px;
        min-width: 100%;
    }
    
    .styled-table thead th,
    .styled-table tbody td {
        padding: 8px 6px;
    }

    .styled-table thead th {
        font-size: 10px;
    }

    .styled-table tbody td:first-child {
        width: 40px;
    }
    
    .btn {
        padding: 5px 8px;
        font-size: 9px;
        margin-right: 2px;
        margin-bottom: 3px;
        display: inline-block;
    }

    .header-flex {
        flex-direction: column;
        gap: 8px;
        align-items: stretch;
    }

    .add-link {
        width: 100%;
        text-align: center;
        display: block;
        padding: 10px 12px;
        font-size: 13px;
    }

    .form-input {
        padding: 12px;
        font-size: 16px;
    }

    .create-btn {
        padding: 13px;
        font-size: 15px;
    }

    .table-wrapper {
        margin: 0.5rem 0;
        border-radius: 8px;
    }

    .header-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .header-actions {
        width: 100%;
    }

    .export-btn, .add-link {
        flex: 1;
        text-align: center;
        justify-content: center;
    }
}

/* Mobile - 640px and below */
@media (max-width: 640px) {
    body {
        padding: 8px;
    }

    .setup-form,
    .scorecard-container {
        padding: 10px;
        border-radius: 10px;
    }

    .logo {
        width: 40px;
        height: 40px;
        font-size: 18px;
        margin-bottom: 12px;
    }

    .title {
        font-size: 15px;
    }

    .subtitle {
        font-size: 11px;
    }

    .form-input {
        padding: 10px;
        font-size: 15px;
    }

    .create-btn {
        padding: 11px;
        font-size: 14px;
    }

    .category-info {
        padding: 10px;
    }

    .category-name {
        font-size: 13px;
    }

    .event-name {
        font-size: 12px;
    }

    .peserta-count {
        font-size: 15px;
    }

    .player-section {
        padding: 10px;
        margin-bottom: 20px;
    }

    .score-table {
        min-width: 380px;
        font-size: 10px;
    }

    .score-table th {
        padding: 6px 2px;
        font-size: 10px;
    }

    .score-table td {
        padding: 5px 2px;
    }
    
    .arrow-input {
        width: 34px;
        height: 28px;
        font-size: 10px;
        padding: 4px 1px;
    }

    .session-label {
        font-size: 10px;
        min-width: 45px;
    }

    .styled-table {
        font-size: 10px;
        min-width: 100%;
    }
    
    .styled-table thead th,
    .styled-table tbody td {
        padding: 7px 4px;
    }

    .styled-table thead th {
        font-size: 9px;
    }

    .styled-table tbody td:first-child {
        width: 35px;
    }

    .styled-table tbody td:nth-child(2) {
        font-size: 9px;
    }
    
    .btn {
        padding: 4px 6px;
        font-size: 8px;
        margin-right: 2px;
        margin-bottom: 2px;
        border-radius: 6px;
    }

    .player-header {
        font-size: 14px;
        padding: 9px;
    }

    .total-summary {
        padding: 10px;
    }

    .grand-total {
        font-size: 17px;
    }

    .x-count {
        font-size: 12px;
    }

    h3 {
        font-size: 16px;
    }

    .add-link {
        padding: 9px 12px;
        font-size: 12px;
    }

    .export-btn {
        padding: 9px 12px;
        font-size: 12px;
    }
}

/* Mobile - 480px and below */
@media (max-width: 480px) {
    body {
        padding: 6px;
    }

    .back-btn {
        width: 34px;
        height: 34px;
        margin-bottom: 12px;
    }

    .setup-form,
    .scorecard-container {
        padding: 8px;
        border-radius: 8px;
    }

    .header {
        margin-bottom: 15px;
    }

    .logo {
        width: 36px;
        height: 36px;
        font-size: 16px;
        margin-bottom: 10px;
    }

    .title {
        font-size: 14px;
    }

    .subtitle {
        font-size: 10px;
    }

    .form-label {
        font-size: 13px;
        margin-bottom: 8px;
    }

    .form-input {
        padding: 9px;
        font-size: 14px;
    }

    .create-btn {
        padding: 10px;
        font-size: 13px;
    }

    .category-info {
        padding: 8px;
    }

    .scorecard-header {
        padding: 8px;
    }

    .category-icon {
        width: 24px;
        height: 24px;
        font-size: 13px;
    }

    .scorecard-title {
        font-size: 13px;
        padding: 7px;
    }

    .player-section {
        padding: 8px;
        margin-bottom: 18px;
    }

    .player-header {
        font-size: 13px;
        padding: 8px;
    }

    .score-table {
        min-width: 340px;
        font-size: 9px;
    }

    .score-table th {
        padding: 5px 2px;
        font-size: 9px;
    }

    .score-table td {
        padding: 4px 1px;
    }
    
    .arrow-input {
        width: 30px;
        height: 26px;
        font-size: 9px;
        padding: 3px 1px;
        border-radius: 4px;
    }

    .session-label {
        font-size: 9px;
        min-width: 40px;
    }

    .total-summary {
        padding: 10px;
    }

    .grand-total {
        font-size: 16px;
    }

    .x-count {
        font-size: 11px;
    }

    .edit-btn {
        padding: 9px 14px;
        font-size: 12px;
    }

    .styled-table {
        font-size: 9px;
        min-width: 100%;
    }
    
    .styled-table thead th,
    .styled-table tbody td {
        padding: 6px 3px;
    }

    .styled-table thead th {
        font-size: 8px;
        padding: 8px 3px;
    }

    .styled-table thead th:first-child {
        border-radius: 8px 0 0 0;
    }

    .styled-table thead th:last-child {
        border-radius: 0 8px 0 0;
    }

    .styled-table tbody td:first-child {
        width: 30px;
        font-size: 8px;
    }

    .styled-table tbody td:nth-child(2) {
        font-size: 8px;
    }

    .styled-table tbody td:nth-child(3),
    .styled-table tbody td:nth-child(4) {
        font-size: 8px;
    }
    
    .btn {
        padding: 3px 5px;
        font-size: 7px;
        margin-right: 1px;
        margin-bottom: 2px;
        white-space: nowrap;
        border-radius: 4px;
    }

    .add-link {
        padding: 8px 10px;
        font-size: 11px;
    }

    .export-btn {
        padding: 8px 10px;
        font-size: 11px;
    }

    h3 {
        font-size: 14px;
    }

    .table-empty {
        padding: 25px 12px;
        font-size: 11px;
    }

    .table-empty::before {
        font-size: 32px;
        margin-bottom: 10px;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .alert {
        padding: 10px;
        font-size: 11px;
    }

    .table-wrapper {
        border-radius: 8px;
        padding: 0;
    }
}

/* Very small mobile - 360px and below */
@media (max-width: 360px) {
    body {
        padding: 5px;
    }

    .setup-form,
    .scorecard-container {
        padding: 6px;
    }

    .logo {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }

    .title {
        font-size: 13px;
    }

    .subtitle {
        font-size: 9px;
    }

    .form-input {
        padding: 8px;
        font-size: 13px;
    }

    .create-btn {
        padding: 9px;
        font-size: 12px;
    }

    .score-table {
        min-width: 300px;
        font-size: 8px;
    }

    .score-table th {
        padding: 4px 1px;
        font-size: 8px;
    }

    .score-table td {
        padding: 3px 1px;
    }

    .arrow-input {
        width: 28px;
        height: 24px;
        font-size: 8px;
        padding: 2px 1px;
    }

    .session-label {
        font-size: 8px;
        min-width: 35px;
    }

    .styled-table {
        min-width: 100%;
        font-size: 8px;
    }

    .styled-table thead th,
    .styled-table tbody td {
        padding: 5px 2px;
    }

    .styled-table thead th {
        font-size: 7px;
        letter-spacing: 0.3px;
    }

    .styled-table tbody td:first-child {
        width: 25px;
        font-size: 7px;
    }

    .styled-table tbody td:nth-child(2) {
        font-size: 7px;
    }

    .styled-table tbody td:nth-child(3),
    .styled-table tbody td:nth-child(4) {
        font-size: 7px;
    }

    .btn {
        padding: 2px 4px;
        font-size: 6px;
        border-radius: 3px;
        margin-right: 1px;
        margin-bottom: 1px;
    }

    .player-header {
        font-size: 12px;
        padding: 7px;
    }

    .grand-total {
        font-size: 15px;
    }

    .x-count {
        font-size: 10px;
    }

    .category-name {
        font-size: 12px;
    }

    .event-name {
        font-size: 11px;
    }

    .peserta-count {
        font-size: 14px;
    }

    h3 {
        font-size: 13px;
    }

    .add-link {
        padding: 7px 8px;
        font-size: 10px;
    }

    .export-btn {
        padding: 7px 8px;
        font-size: 10px;
    }
}

/* Print styles */
@media print {
    body {
        background: white;
        padding: 0;
    }

    .back-btn,
    .edit-btn,
    .add-link,
    .export-btn {
        display: none;
    }

    .setup-form,
    .scorecard-container {
        box-shadow: none;
        background: white;
        color: black;
    }

    .table-wrapper {
        box-shadow: none;
        border: 1px solid #ddd;
    }
    
    .styled-table .btn {
        display: none;
    }
    
    .styled-table tbody tr:hover {
        background: transparent;
        transform: none;
        box-shadow: none;
    }

    .score-table-container {
        box-shadow: none;
    }
}
</style>
        
    </head>
    <body>
         <div class="container">
            <?php if(isset($_GET['resource'])) { ?>
                <?php if($_GET['resource'] == 'form') { ?>
                    <a  class="back-btn" href="detail.php?action=scorecard&resource=index&kegiatan_id=<?= $kegiatan_id ?>&category_id=<?= $category_id ?>">←</a>
                    <form action="" method="post">
                        <div class="setup-form" id="setupForm">
                            <input type="hidden" id="local_time" name="local_time">
                            <div class="header">
                                <div class="logo">🏹</div>
                                <div class="title">Setup Scorecard</div>
                                <div class="subtitle">Atur jumlah sesi dan anak panah</div>
                            </div>

                            <div class="category-info">
                                <div class="category-name"><?= htmlspecialchars($kategoriData['name']) ?></div>
                                <div class="event-name"><?= htmlspecialchars($kegiatanData['nama_kegiatan']) ?></div>
                                <div class="peserta-count"><?= count($pesertaList) ?> Peserta Terdaftar</div>
                            </div>

                            <?php if (count($pesertaList) == 0): ?>
                                <div class="alert alert-warning">
                                    <strong>Peringatan:</strong> Tidak ada peserta yang terdaftar dalam kategori ini.
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label class="form-label">Jumlah Sesi</label>
                                <input type="number" class="form-input" name="jumlahSesi" id="jumlahSesi" min="1" value="9" placeholder="9">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Jumlah Anak Panah per Sesi</label>
                                <input type="number" class="form-input" name="jumlahPanah" id="jumlahPanah" min="1"  value="3" placeholder="3">
                            </div>

                            <button type="submit" name="create" class="create-btn" <?= count($pesertaList) == 0 ? 'disabled' : '' ?>>
                                Buat Scorecard
                            </button>
                        </div>
                    </form>
                <?php } ?>
                <?php if($_GET['resource'] == 'index') { ?>
                    <div class="setup-form" id="setupForm">
                        <div class="header-bar">
                            <button class="back-btn" onclick="goBack()">←</button>
                            <div class="header-actions">
                                <a href="detail.php?action=scorecard&resource=form&kegiatan_id=<?= $kegiatan_id ?>&category_id=<?= $category_id ?>" class="add-link">Tambah data +</a>
                                <button onclick="exportTableToExcel()" class="export-btn">📊 Export Excel</button>
                            </div>
                        </div>
                        <div class="table-wrapper">
                            <table class="styled-table" id="scorecardTable">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Jumlah Sesi</th>
                                        <th>Jumlah Anak Panah</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        $loopNumber = 1;
                                        while($a = mysqli_fetch_array($mysql_table_score_board)) { ?>
                                            <tr>
                                                <td><?= $loopNumber++ ?></td>
                                                <td><?= $a['created'] ?></td>
                                                <td><?= $a['jumlah_sesi'] ?></td>
                                                <td><?= $a['jumlah_anak_panah'] ?></td>
                                                <td>
                                                    <a href="detail.php?action=scorecard&resource=index&kegiatan_id=<?= $kegiatan_id ?>&category_id=<?= $category_id ?>&scoreboard=<?= $a['id'] ?>&rangking=true" class="btn">Ranking</a>
                                                    <a href="detail.php?action=scorecard&resource=index&kegiatan_id=<?= $kegiatan_id ?>&category_id=<?= $category_id ?>&scoreboard=<?= $a['id'] ?>" class="btn">Detail</a>
                                                    <a href="detail.php?aduan=true&kegiatan_id=<?= $kegiatan_id ?>&category_id=<?= $category_id ?>&scoreboard=<?= $a['id'] ?>" class="btn">Aduan</a>
                                                    <button onclick="delete_score_board('<?= $kegiatan_id ?>', '<?= $category_id ?>', '<?= $a['id'] ?>')" class="btn">Hapus</button>
                                                </td>
                                            </tr>
                                        <?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php } ?>
            <?php }?>

            <div class="scorecard-container" id="scorecardContainer">
                <div class="header-flex">
                    <a  class="back-btn" href="detail.php?action=scorecard&resource=index&kegiatan_id=<?= $kegiatan_id ?>&category_id=<?= $category_id ?>">←</a>
                    <h3>Score Board <?= (isset($_GET['rangking'])) ? '(Ranking)' : '' ?></h3>
                    <button onclick="exportScorecardToExcel()" class="export-btn">📊 Export</button>
                </div>
                <div class="scorecard-header">
                    <div class="category-header-info">
                        <div class="category-icon">🎯</div>
                        <div>
                            <div class="category-name" style="font-size: 14px; margin: 0;"><?= htmlspecialchars($kategoriData['name']) ?></div>
                            <div style="font-size: 12px; color: #ddd;"><?= htmlspecialchars($kegiatanData['nama_kegiatan']) ?></div>
                        </div>
                    </div>
                    <div class="category-header-info">
                        <div class="category-icon">👥</div>
                        <div>
                            <div class="category-name" style="font-size: 14px; margin: 0;" id="pesertaCount"><?= count($pesertaList) ?></div>
                            <div style="font-size: 12px; color: #ddd;">Peserta</div>
                        </div>
                    </div>
                    <div class="category-header-info">
                        <div class="category-icon">🏹</div>
                        <div>
                            <div class="category-name" style="font-size: 14px; margin: 0;" id="panahCount">-</div>
                            <div style="font-size: 12px; color: #ddd;">Anak Panah</div>
                        </div>
                    </div>
                </div>

                <div class="scorecard-title">Informasi Skor</div>

                <div id="playersContainer"></div>

                <button class="edit-btn" onclick="editScorecard()">
                    Edit Setup
                </button>
            </div>
        </div>

        <script>
    <?php if($_GET['resource'] == 'form') { ?>
        let now = new Date();
        let formatted = now.getFullYear() + "-" 
            + String(now.getMonth()+1).padStart(2, '0') + "-"
            + String(now.getDate()).padStart(2, '0') + " "
            + String(now.getHours()).padStart(2, '0') + ":"
            + String(now.getMinutes()).padStart(2, '0') + ":"
            + String(now.getSeconds()).padStart(2, '0');

        document.getElementById("local_time").value = formatted;
    <?php } ?>
    
    const pesertaData = <?= json_encode($pesertaList) ?>;
    <?php if(isset($_GET['rangking'])) { ?>
        const peserta_score = <?= json_encode($peserta_score) ?>;
        function tambahAtributById(id, key, value) {
            const peserta = pesertaData.find(p => p.id === id);
            if (peserta) {
                peserta[key] = value;
            }
        }

        for(let i = 0; i < peserta_score.length; i++) {
            tambahAtributById(peserta_score[i]['id'], "total_score", peserta_score[i]['total_score']);
            tambahAtributById(peserta_score[i]['id'], "x_score", peserta_score[i]['total_x']);
        }

        pesertaData.sort((a, b) => {
            if (b.total_score !== a.total_score) {
                return b.total_score - a.total_score;
            }
            return b.x_score - a.x_score;
        });
    <?php } ?>
    
    <?php if(isset($_GET['scoreboard'])) { ?>
        openScoreBoard("<?= $show_score_board['jumlah_sesi'] ?>", "<?= $show_score_board['jumlah_anak_panah'] ?>");
    <?php } ?> 
    
    function delete_score_board(kegiatan_id, category_id, id) {
        if(confirm("Apakah anda yakin akan menghapus data ini?")) {
            window.location.href = `detail.php?action=scorecard&resource=index&kegiatan_id=${kegiatan_id}&category_id=${category_id}&delete_score_board=${id}`;
        }
    }

    <?php 
        if(isset($mysql_data_score)) {
            while($jatuh = mysqli_fetch_array($mysql_data_score)) { ?> 
                document.getElementById("peserta_<?= $jatuh['peserta_id'] ?>_a<?= $jatuh['arrow'] ?>_s<?= $jatuh['session'] ?>").value = "<?= $jatuh['score'] ?>";
                hitungPerArrow('peserta_<?= $jatuh['peserta_id'] ?>', '<?= $jatuh['arrow'] ?>', '<?= $jatuh['session'] ?>','<?= $show_score_board['jumlah_anak_panah'] ?>')
            <?php } ?>
        <?php }
     ?> 
    
    function goBack() {
        window.history.back();
    }

    function openScoreBoard(jumlahSesi_data, jumlahPanah_data) {
        const jumlahSesi = parseInt(jumlahSesi_data);
        const jumlahPanah = parseInt(jumlahPanah_data);
        document.getElementById('panahCount').textContent = jumlahSesi * jumlahPanah;
        generatePlayerSections(jumlahSesi, jumlahPanah);
        document.getElementById('setupForm').style.display = 'none';
        document.getElementById('scorecardContainer').style.display = 'block';
        document.querySelector('.container').style.maxWidth = '1200px';
    }

    function generatePlayerSections(jumlahSesi, jumlahPanah) {
        const playersContainer = document.getElementById('playersContainer');
        playersContainer.innerHTML = '';

        pesertaData.forEach((peserta, index) => {
            const playerId = `peserta_${peserta.id}`;
            const playerName = peserta.nama_peserta;
            
            const playerSection = document.createElement('div');
            playerSection.className = 'player-section';
            playerSection.innerHTML = `
                <div class="player-header">
                    ${playerName} (${peserta.jenis_kelamin}) ${typeof peserta.total_score !== 'undefined' ? ` - Juara ${index + 1}` : ''}
                </div>
                <div class="score-table-container">
                    <table class="score-table">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 60px;">Sesi</th>
                                <th colspan="${jumlahPanah}">Anak Panah</th>
                                <th rowspan="2" style="width: 60px;">Total</th>
                                <th rowspan="2" style="width: 60px;">End</th>
                            </tr>
                            <tr>
                                ${Array.from({length: jumlahPanah}, (_, i) => `<th style="width: 50px;">${i + 1}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            ${generateTableRows(playerId, jumlahSesi, jumlahPanah)}
                        </tbody>
                    </table>
                </div>
                <div class="total-summary" id="${playerId}_summary">
                    <div style="font-size: 14px; margin-bottom: 8px;">Total Keseluruhan</div>
                    <div class="grand-total" id="${playerId}_grand_total">0 poin</div>
                    ${typeof peserta.x_score !== 'undefined' ? `<div class="x-count">X Score: ${peserta.x_score}</div>` : ''}
                </div>
            `;
            
            playersContainer.appendChild(playerSection);
        });
    }

    function generateTableRows(playerId, jumlahSesi, jumlahPanah) {
        let rowsHtml = '';
        
        for (let session = 1; session <= jumlahSesi; session++) {
            const arrowInputs = Array.from({length: jumlahPanah}, (_, arrow) => `
                <td>
                    <input type="text" 
                           class="arrow-input" 
                           <?= (isset($_GET['rangking'])) ? 'disabled' : '' ?>
                           id="${playerId}_a${arrow + 1}_s${session}"
                           placeholder=""
                           oninput="validateArrowInput(this);hitungPerArrow('${playerId}', '${arrow + 1}', '${session}','${jumlahPanah}', this)">
                </td>
            `).join('');
            
            rowsHtml += `
                <tr class="session-row">
                    <td class="session-label">S${session}</td>
                    ${arrowInputs}
                    <td class="total-cell">
                        <input type="text" 
                               class="arrow-input" 
                               id="${playerId}_total_a${session}"
                               readonly
                               style="background: rgba(253, 203, 110, 0.1); border-color: #e17055;">
                    </td>
                    <td class="end-cell">
                        <input type="text" 
                               class="arrow-input" 
                               id="${playerId}_end_a${session}"
                               readonly
                               style="background: rgba(0, 184, 148, 0.1); border-color: #00b894;">
                    </td>
                </tr>
            `;
        }
        
        return rowsHtml;
    }

    function hitungPerArrow(playerId, arrow, session, totalArrow, el) {
        let sessionTotal = 0;
        
        for(let a = 1; a <= totalArrow; a++) {
            const input = document.getElementById(`${playerId}_a${a}_s${session}`);
            if(input && input.value) {
                let val = input.value.trim().toLowerCase();
                let score = 0;
                if (val === "x") {
                    score = 10;
                } else if (val === "m") {
                    score = 0;
                } else if (!isNaN(val) && val !== "") {
                    score = parseInt(val);
                }
                sessionTotal += score;
            }
        }
        
        const totalInput = document.getElementById(`${playerId}_total_a${session}`);
        if(totalInput) {
            totalInput.value = sessionTotal;
        }
        
        let maxSession = 20;
        let runningTotal = 0;
        
        for(let s = 1; s <= maxSession; s++) {
            const sessionTotalInput = document.getElementById(`${playerId}_total_a${s}`);
            const sessionEndInput = document.getElementById(`${playerId}_end_a${s}`);
            
            if(sessionTotalInput && sessionEndInput) {
                if(sessionTotalInput.value && sessionTotalInput.value !== '') {
                    runningTotal += parseInt(sessionTotalInput.value) || 0;
                }
                sessionEndInput.value = runningTotal;
            } else {
                break;
            }
        }
        
        const grandTotalElement = document.getElementById(`${playerId}_grand_total`);
        if(grandTotalElement) {
            grandTotalElement.innerText = runningTotal + " poin";
        }
        
        if(el != null) {
            let arr_playerID = playerId.split("_");
            let nama = "Marsha and The Bear";
            
            fetch("", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "save_score=1" +
                    "&nama=" + encodeURIComponent(nama) +
                    "&peserta_id=" + encodeURIComponent(arr_playerID[1]) +
                    "&arrow=" + encodeURIComponent(arrow) +
                    "&session=" + encodeURIComponent(session) + 
                    "&score=" + encodeURIComponent(document.getElementById(el.id).value)
            })
            .then(response => response.json())
            .then(data => {
                console.log("Score saved: " + data.message);
            })
            .catch(err => console.error(err));
        }
        return 0;
    }

    function validateArrowInput(el) {
        let val = el.value.trim().toLowerCase();

        if (!/^(10|[0-9]|x|m)?$/i.test(val)) {
            el.value = "";
            return;
        }
        
        if (val === 'x' || val === 'X') {
            el.style.background = 'rgba(40, 167, 69, 0.1)';
            el.style.borderColor = '#28a745';
            el.style.color = '#28a745';
            el.style.fontWeight = '700';
        } else if (val === 'm' || val === 'M') {
            el.style.background = 'rgba(220, 53, 69, 0.1)';
            el.style.borderColor = '#dc3545';
            el.style.color = '#dc3545';
            el.style.fontWeight = '700';
        } else if (val === '10') {
            el.style.background = 'rgba(40, 167, 69, 0.1)';
            el.style.borderColor = '#28a745';
            el.style.color = '#28a745';
            el.style.fontWeight = '700';
        } else if (val === '9' || val === '8') {
            el.style.background = 'rgba(255, 193, 7, 0.1)';
            el.style.borderColor = '#ffc107';
            el.style.color = '#856404';
            el.style.fontWeight = '600';
        } else {
            el.style.background = 'transparent';
            el.style.borderColor = 'transparent';
            el.style.color = '#333';
            el.style.fontWeight = '600';
        }
    }

    function editScorecard() {
        document.getElementById('setupForm').style.display = 'block';
        document.getElementById('scorecardContainer').style.display = 'none';
        document.querySelector('.container').style.maxWidth = '500px'; 
    }

    // FUNGSI EXPORT EXCEL UNTUK SCORECARD DETAIL
    function exportScorecardToExcel() {
        const wb = XLSX.utils.book_new();
        
        // Dapatkan info kategori dan kegiatan
        const categoryName = "<?= htmlspecialchars($kategoriData['name']) ?>";
        const eventName = "<?= htmlspecialchars($kegiatanData['nama_kegiatan']) ?>";
        
        // Dapatkan jumlah sesi dan jumlah panah dari peserta pertama
        const firstPlayerSection = document.querySelector('.player-section');
        const firstTable = firstPlayerSection ? firstPlayerSection.querySelector('.score-table') : null;
        let jumlahSesi = 0;
        let jumlahPanah = 0;
        
        if (firstTable) {
            // Hitung jumlah sesi dari baris tbody
            const sessionRows = firstTable.querySelectorAll('tbody tr');
            jumlahSesi = sessionRows.length;
            
            // Hitung jumlah panah dari header baris kedua (th tanpa rowspan/colspan)
            const secondHeaderRow = firstTable.querySelectorAll('thead tr:nth-child(2) th');
            jumlahPanah = secondHeaderRow.length;
            
            console.log('Jumlah Sesi:', jumlahSesi);
            console.log('Jumlah Panah:', jumlahPanah);
        }
        
        // Jika masih 0, coba deteksi dari input yang ada
        if (jumlahPanah === 0 && pesertaData.length > 0) {
            const firstPlayerId = `peserta_${pesertaData[0].id}`;
            let arrowCount = 1;
            while (document.getElementById(`${firstPlayerId}_a${arrowCount}_s1`)) {
                arrowCount++;
            }
            jumlahPanah = arrowCount - 1;
            console.log('Jumlah Panah (dari input):', jumlahPanah);
        }
        
        // ============ SHEET 1: REKAP TOTAL ============
        const rekapData = [];
        
        // Header untuk sheet rekap
        rekapData.push([categoryName]);
        rekapData.push([eventName]);
        rekapData.push([]);
        
        const rekapHeaders = ['No', 'Nama'];
        for (let i = 1; i <= jumlahSesi; i++) {
            rekapHeaders.push(`Rambahan ${i}`);
        }
        rekapHeaders.push('Total');
        rekapData.push(rekapHeaders);
        
        // Isi data rekap
        pesertaData.forEach((peserta, index) => {
            const playerId = `peserta_${peserta.id}`;
            const row = [
                index + 1,
                peserta.nama_peserta
            ];
            
            for (let s = 1; s <= jumlahSesi; s++) {
                const totalInput = document.getElementById(`${playerId}_total_a${s}`);
                const value = totalInput ? (totalInput.value || '0') : '0';
                row.push(value);
            }
            
            const grandTotalEl = document.getElementById(`${playerId}_grand_total`);
            const grandTotal = grandTotalEl ? grandTotalEl.textContent.replace(' poin', '') : '0';
            row.push(grandTotal);
            
            rekapData.push(row);
        });
        
        const wsRekap = XLSX.utils.aoa_to_sheet(rekapData);
        
        // Set lebar kolom untuk sheet rekap
        const rekapColWidths = [
            { wch: 5 },
            { wch: 20 }
        ];
        for (let i = 0; i < jumlahSesi; i++) {
            rekapColWidths.push({ wch: 12 });
        }
        rekapColWidths.push({ wch: 12 });
        wsRekap['!cols'] = rekapColWidths;
        
        // ============ SHEET 2: TRAINING (HORIZONTAL LAYOUT) ============
        const trainingData = [];
        
        // Header utama
        trainingData.push([categoryName]);
        trainingData.push([eventName]);
        trainingData.push([]);
        
        pesertaData.forEach((peserta, pesertaIndex) => {
            const playerId = `peserta_${peserta.id}`;
            
            // Spacing antar peserta
            if (pesertaIndex > 0) {
                trainingData.push([]);
            }
            
            // Header peserta
            trainingData.push([`Rank#${pesertaIndex + 1} ${peserta.nama_peserta}`]);
            
            // Header kolom: Rambahan | Shot 1 | Shot 2 | ... | Total | End
            const headerRow = ['Rambahan'];
            for (let a = 1; a <= jumlahPanah; a++) {
                headerRow.push(`Shot ${a}`);
            }
            headerRow.push('Total');
            headerRow.push('End');
            trainingData.push(headerRow);
            
            // Data setiap rambahan (per baris)
            for (let s = 1; s <= jumlahSesi; s++) {
                const row = [s]; // Nomor rambahan
                
                // Isi nilai setiap shot
                for (let a = 1; a <= jumlahPanah; a++) {
                    const input = document.getElementById(`${playerId}_a${a}_s${s}`);
                    const value = input ? (input.value || '') : '';
                    row.push(value);
                }
                
                // Total rambahan ini
                const totalInput = document.getElementById(`${playerId}_total_a${s}`);
                const totalValue = totalInput ? (totalInput.value || '0') : '0';
                row.push(totalValue);
                
                // End (akumulasi sampai rambahan ini)
                const endInput = document.getElementById(`${playerId}_end_a${s}`);
                const endValue = endInput ? (endInput.value || '0') : '0';
                row.push(endValue);
                
                trainingData.push(row);
            }
        });
        
        const wsTraining = XLSX.utils.aoa_to_sheet(trainingData);
        
        // Set lebar kolom untuk sheet training
        const trainingColWidths = [
            { wch: 12 } // Kolom Rambahan
        ];
        for (let i = 0; i < jumlahPanah; i++) {
            trainingColWidths.push({ wch: 8 }); // Kolom Shot
        }
        trainingColWidths.push({ wch: 10 }); // Total
        trainingColWidths.push({ wch: 10 }); // End
        wsTraining['!cols'] = trainingColWidths;
        
        // Tambahkan sheet dengan urutan yang benar
        XLSX.utils.book_append_sheet(wb, wsRekap, "Rekap Total");
        XLSX.utils.book_append_sheet(wb, wsTraining, "Training");
        
        const fileName = `Scorecard_${categoryName}_${new Date().toISOString().split('T')[0]}.xlsx`;
        XLSX.writeFile(wb, fileName);
    }
</script>
    </body>
    </html>
    <?php
    exit;
}

// ============================================
// CEK EXPORT EXCEL
// ============================================
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    try {
        include 'panggil.php';
    } catch (Exception $e) {
        die("Error koneksi database: " . $e->getMessage());
    }

    $kegiatan_id = isset($_GET['kegiatan_id']) ? intval($_GET['kegiatan_id']) : null;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filter_kategori = isset($_GET['filter_kategori']) ? intval($_GET['filter_kategori']) : 0;
    $filter_gender = isset($_GET['filter_gender']) ? $_GET['filter_gender'] : '';

    if (!$kegiatan_id) {
        die("ID Kegiatan tidak valid.");
    }

    $kegiatanData = [];
    try {
        $queryKegiatan = "SELECT id, nama_kegiatan FROM kegiatan WHERE id = ?";
        $stmtKegiatan = $conn->prepare($queryKegiatan);
        $stmtKegiatan->bind_param("i", $kegiatan_id);
        $stmtKegiatan->execute();
        $resultKegiatan = $stmtKegiatan->get_result();
        
        if ($resultKegiatan->num_rows > 0) {
            $kegiatanData = $resultKegiatan->fetch_assoc();
        } else {
            die("Kegiatan tidak ditemukan.");
        }
        $stmtKegiatan->close();
    } catch (Exception $e) {
        die("Error mengambil data kegiatan: " . $e->getMessage());
    }

    $whereConditions = ["p.kegiatan_id = ?"];
    $params = [$kegiatan_id];
    $types = "i";

    if (!empty($search)) {
        $whereConditions[] = "(p.nama_peserta LIKE ? OR p.asal_kota LIKE ? OR p.nama_club LIKE ? OR p.sekolah LIKE ?)";
        $searchParam = "%$search%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        $types .= "ssss";
    }

    if ($filter_kategori > 0) {
        $whereConditions[] = "p.category_id = ?";
        $params[] = $filter_kategori;
        $types .= "i";
    }

    if (!empty($filter_gender)) {
        $whereConditions[] = "p.jenis_kelamin = ?";
        $params[] = $filter_gender;
        $types .= "s";
    }

    $whereClause = implode(" AND ", $whereConditions);

    $queryPeserta = "
        SELECT 
            p.id,
            p.nama_peserta,
            p.tanggal_lahir,
            p.jenis_kelamin,
            p.asal_kota,
            p.nama_club,
            p.sekolah,
            p.kelas,
            p.nomor_hp,
            p.bukti_pembayaran,
            c.name as category_name,
            c.min_age,
            c.max_age,
            c.gender as category_gender,
            TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) as umur
        FROM peserta p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $whereClause
        ORDER BY p.nama_peserta ASC
    ";

    $pesertaList = [];
    try {
        $stmtPeserta = $conn->prepare($queryPeserta);
        if (!empty($params)) {
            $stmtPeserta->bind_param($types, ...$params);
        }
        $stmtPeserta->execute();
        $resultPeserta = $stmtPeserta->get_result();
        
        while ($row = $resultPeserta->fetch_assoc()) {
            $pesertaList[] = $row;
        }
        $stmtPeserta->close();
    } catch (Exception $e) {
        die("Error mengambil data peserta: " . $e->getMessage());
    }

    $filename = "Daftar_Peserta_" . preg_replace('/[^A-Za-z0-9_\-]/', '_', $kegiatanData['nama_kegiatan']) . "_" . date('Y-m-d_H-i-s') . ".xls";

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    echo '<!DOCTYPE html>';
    echo '<html>';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<style>';
    echo 'table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }';
    echo 'th, td { border: 1px solid #000; padding: 8px; text-align: left; vertical-align: top; }';
    echo 'th { background-color: #4472C4; color: white; font-weight: bold; text-align: center; }';
    echo '.center { text-align: center; }';
    echo '.badge { background-color: #E7E6E6; padding: 2px 6px; border-radius: 3px; font-size: 11px; }';
    echo '.badge-male { background-color: #D4E6F1; color: #1B4F72; }';
    echo '.badge-female { background-color: #FADBD8; color: #922B21; }';
    echo '.badge-paid { background-color: #D5F4E6; color: #0E6655; }';
    echo '.badge-unpaid { background-color: #FADBD8; color: #922B21; }';
    echo '.header-info { margin-bottom: 20px; }';
    echo '.header-info h2 { color: #2E86C1; margin: 5px 0; }';
    echo '.header-info p { margin: 3px 0; color: #566573; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';

    echo '<div class="header-info">';
    echo '<h2>' . htmlspecialchars($kegiatanData['nama_kegiatan']) . '</h2>';
    echo '<p><strong>Total Peserta:</strong> ' . count($pesertaList) . ' orang</p>';
    echo '<p><strong>Tanggal Export:</strong> ' . date('d F Y, H:i:s') . '</p>';

    if (!empty($search) || $filter_kategori > 0 || !empty($filter_gender)) {
        echo '<p><strong>Filter yang diterapkan:</strong>';
        $filters = [];
        if (!empty($search)) $filters[] = "Pencarian: \"$search\"";
        if ($filter_kategori > 0) {
            $queryKat = "SELECT name FROM categories WHERE id = ?";
            $stmtKat = $conn->prepare($queryKat);
            $stmtKat->bind_param("i", $filter_kategori);
            $stmtKat->execute();
            $resultKat = $stmtKat->get_result();
            if ($resultKat->num_rows > 0) {
                $kategori = $resultKat->fetch_assoc();
                $filters[] = "Kategori: " . $kategori['name'];
            }
            $stmtKat->close();
        }
        if (!empty($filter_gender)) $filters[] = "Gender: $filter_gender";
        echo ' ' . implode(', ', $filters);
        echo '</p>';
    }
    echo '</div>';

    echo '<table>';
    echo '<thead>';
    echo '<tr>';
    echo '<th style="width: 40px;">No</th>';
    echo '<th style="width: 200px;">Nama Peserta</th>';
    echo '<th style="width: 100px;">Tanggal Lahir</th>';
    echo '<th style="width: 60px;">Umur</th>';
    echo '<th style="width: 100px;">Jenis Kelamin</th>';
    echo '<th style="width: 150px;">Kategori</th>';
    echo '<th style="width: 120px;">Asal Kota</th>';
    echo '<th style="width: 150px;">Nama Club</th>';
    echo '<th style="width: 150px;">Sekolah</th>';
    echo '<th style="width: 80px;">Kelas</th>';
    echo '<th style="width: 130px;">Nomor HP</th>';
    echo '<th style="width: 120px;">Status Pembayaran</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    if (count($pesertaList) > 0) {
        foreach ($pesertaList as $index => $peserta) {
            echo '<tr>';
            echo '<td class="center">' . ($index + 1) . '</td>';
            echo '<td><strong>' . htmlspecialchars($peserta['nama_peserta']) . '</strong></td>';
            echo '<td class="center">' . date('d/m/Y', strtotime($peserta['tanggal_lahir'])) . '</td>';
            echo '<td class="center">' . $peserta['umur'] . ' tahun</td>';
            
            $genderClass = $peserta['jenis_kelamin'] == 'Laki-laki' ? 'badge-male' : 'badge-female';
            echo '<td class="center"><span class="badge ' . $genderClass . '">' . htmlspecialchars($peserta['jenis_kelamin']) . '</span></td>';
            
            echo '<td>';
            echo '<span class="badge">' . htmlspecialchars($peserta['category_name']) . '</span><br>';
            echo '<small>(' . $peserta['min_age'] . '-' . $peserta['max_age'] . ' thn, ';
            echo ($peserta['category_gender'] == 'Campuran' ? 'Putra/Putri' : $peserta['category_gender']) . ')</small>';
            echo '</td>';
            
            echo '<td>' . htmlspecialchars($peserta['asal_kota'] ?: '-') . '</td>';
            echo '<td>' . htmlspecialchars($peserta['nama_club'] ?: '-') . '</td>';
            echo '<td>' . htmlspecialchars($peserta['sekolah'] ?: '-') . '</td>';
            echo '<td class="center">' . htmlspecialchars($peserta['kelas'] ?: '-') . '</td>';
            echo '<td>' . htmlspecialchars($peserta['nomor_hp']) . '</td>';
            
            if (!empty($peserta['bukti_pembayaran'])) {
                echo '<td class="center"><span class="badge badge-paid">SUDAH BAYAR</span><br><small>File: ' . htmlspecialchars($peserta['bukti_pembayaran']) . '</small></td>';
            } else {
                echo '<td class="center"><span class="badge badge-unpaid">BELUM BAYAR</span></td>';
            }
            
            echo '</tr>';
        }
    } else {
        echo '<tr>';
        echo '<td colspan="12" class="center" style="padding: 30px; font-style: italic; color: #666;">Tidak ada data peserta yang ditemukan</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    echo '<br><br>';
    echo '<div class="header-info">';
    echo '<h3>Ringkasan Statistik</h3>';

    $statistik = [
        'total' => count($pesertaList),
        'laki_laki' => 0,
        'perempuan' => 0,
        'sudah_bayar' => 0,
        'belum_bayar' => 0,
        'kategori' => []
    ];

    foreach ($pesertaList as $peserta) {
        if ($peserta['jenis_kelamin'] == 'Laki-laki') {
            $statistik['laki_laki']++;
        } else {
            $statistik['perempuan']++;
        }
        
        if (!empty($peserta['bukti_pembayaran'])) {
            $statistik['sudah_bayar']++;
        } else {
            $statistik['belum_bayar']++;
        }
        
        $kategori = $peserta['category_name'];
        if (!isset($statistik['kategori'][$kategori])) {
            $statistik['kategori'][$kategori] = 0;
        }
        $statistik['kategori'][$kategori]++;
    }

    echo '<table style="width: 50%; margin-top: 10px;">';
    echo '<tr><th>Keterangan</th><th>Jumlah</th></tr>';
    echo '<tr><td>Total Peserta</td><td class="center"><strong>' . $statistik['total'] . '</strong></td></tr>';
    echo '<tr><td>Laki-laki</td><td class="center">' . $statistik['laki_laki'] . '</td></tr>';
    echo '<tr><td>Perempuan</td><td class="center">' . $statistik['perempuan'] . '</td></tr>';
    echo '<tr><td>Sudah Bayar</td><td class="center">' . $statistik['sudah_bayar'] . '</td></tr>';
    echo '<tr><td>Belum Bayar</td><td class="center">' . $statistik['belum_bayar'] . '</td></tr>';
    echo '</table>';

    if (!empty($statistik['kategori'])) {
        echo '<br>';
        echo '<h4>Distribusi per Kategori:</h4>';
        echo '<table style="width: 50%;">';
        echo '<tr><th>Kategori</th><th>Jumlah Peserta</th></tr>';
        foreach ($statistik['kategori'] as $kategori => $jumlah) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($kategori) . '</td>';
            echo '<td class="center"><strong>' . $jumlah . '</strong></td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    echo '</div>';
    echo '</body>';
    echo '</html>';

    $conn->close();
    exit;
}

// ============================================
// BAGIAN TAMPILAN NORMAL (DAFTAR PESERTA)
// ============================================

try {
    include 'panggil.php';
} catch (Exception $e) {
    die("Error koneksi database: " . $e->getMessage());
}

$kegiatan_id = isset($_GET['kegiatan_id']) ? intval($_GET['kegiatan_id']) : null;

if (!$kegiatan_id) {
    try {
        $queryFirstKegiatan = "SELECT id FROM kegiatan WHERE id = " . (isset($_GET['POST']) ? intval($_GET['POST']) : $_GET['id']);
        $resultFirstKegiatan = $conn->query($queryFirstKegiatan);
        if ($resultFirstKegiatan && $resultFirstKegiatan->num_rows > 0) {
            $firstKegiatan = $resultFirstKegiatan->fetch_assoc();
            $kegiatan_id = $firstKegiatan['id'];
        }
    } catch (Exception $e) {
        die("Error mengambil kegiatan: " . $e->getMessage());
    }
}

if (!$kegiatan_id) {
    die("Tidak ada kegiatan yang tersedia.");
}

$kegiatanData = [];
try {
    $queryKegiatan = "SELECT id, nama_kegiatan FROM kegiatan WHERE id = ?";
    $stmtKegiatan = $conn->prepare($queryKegiatan);
    $stmtKegiatan->bind_param("i", $kegiatan_id);
    $stmtKegiatan->execute();
    $resultKegiatan = $stmtKegiatan->get_result();
    
    if ($resultKegiatan->num_rows > 0) {
        $kegiatanData = $resultKegiatan->fetch_assoc();
    } else {
        die("Kegiatan tidak ditemukan.");
    }
    $stmtKegiatan->close();
} catch (Exception $e) {
    die("Error mengambil data kegiatan: " . $e->getMessage());
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_kategori = isset($_GET['filter_kategori']) ? intval($_GET['filter_kategori']) : 0;
$filter_gender = isset($_GET['filter_gender']) ? $_GET['filter_gender'] : '';

$whereConditions = ["p.kegiatan_id = ?"];
$params = [$kegiatan_id];
$types = "i";

if (!empty($search)) {
    $whereConditions[] = "(p.nama_peserta LIKE ? OR p.asal_kota LIKE ? OR p.nama_club LIKE ? OR p.sekolah LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
    $types .= "ssss";
}

if ($filter_kategori > 0) {
    $whereConditions[] = "p.category_id = ?";
    $params[] = $filter_kategori;
    $types .= "i";
}

if (!empty($filter_gender)) {
    $whereConditions[] = "p.jenis_kelamin = ?";
    $params[] = $filter_gender;
    $types .= "s";
}

$whereClause = implode(" AND ", $whereConditions);

$queryPeserta = "
    SELECT 
        p.id,
        p.nama_peserta,
        p.tanggal_lahir,
        p.jenis_kelamin,
        p.asal_kota,
        p.nama_club,
        p.sekolah,
        p.kelas,
        p.nomor_hp,
        p.bukti_pembayaran,
        c.name as category_name,
        c.min_age,
        c.max_age,
        c.gender as category_gender,
        TIMESTAMPDIFF(YEAR, p.tanggal_lahir, CURDATE()) as umur
    FROM peserta p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE $whereClause
    ORDER BY p.nama_peserta ASC
";

$pesertaList = [];
$totalPeserta = 0;

try {
    $stmtPeserta = $conn->prepare($queryPeserta);
    if (!empty($params)) {
        $stmtPeserta->bind_param($types, ...$params);
    }
    $stmtPeserta->execute();
    $resultPeserta = $stmtPeserta->get_result();
    
    while ($row = $resultPeserta->fetch_assoc()) {
        $pesertaList[] = $row;
    }
    $totalPeserta = count($pesertaList);
    $stmtPeserta->close();
} catch (Exception $e) {
    die("Error mengambil data peserta: " . $e->getMessage());
}

$kategoriesList = [];
try {
    $queryKategori = "
        SELECT DISTINCT c.id, c.name 
        FROM categories c 
        INNER JOIN kegiatan_kategori kk ON c.id = kk.category_id 
        WHERE kk.kegiatan_id = ? AND c.status = 'active'
        ORDER BY c.name ASC
    ";
    $stmtKategori = $conn->prepare($queryKategori);
    $stmtKategori->bind_param("i", $kegiatan_id);
    $stmtKategori->execute();
    $resultKategori = $stmtKategori->get_result();
    
    while ($row = $resultKategori->fetch_assoc()) {
        $kategoriesList[] = $row;
    }
    $stmtKategori->close();
} catch (Exception $e) {
    // Biarkan kosong jika error
}

$statistik = [
    'total' => $totalPeserta,
    'laki_laki' => 0,
    'perempuan' => 0,
    'kategori' => [],
    'sudah_bayar' => 0,
    'belum_bayar' => 0
];

foreach ($pesertaList as $peserta) {
    if ($peserta['jenis_kelamin'] == 'Laki-laki') {
        $statistik['laki_laki']++;
    } else {
        $statistik['perempuan']++;
    }
    
    if (!empty($peserta['bukti_pembayaran'])) {
        $statistik['sudah_bayar']++;
    } else {
        $statistik['belum_bayar']++;
    }
    
    $kategori = $peserta['category_name'];
    if (!isset($statistik['kategori'][$kategori])) {
        $statistik['kategori'][$kategori] = 0;
    }
    $statistik['kategori'][$kategori]++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peserta - <?= htmlspecialchars($kegiatanData['nama_kegiatan']) ?></title>
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
            max-width: 1400px;
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

        .content {
            padding: 30px;
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

        .statistics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-card.primary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-card.success {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .stat-card.warning {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .stat-card.info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card.danger {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 13px;
            opacity: 0.9;
        }

        .filters {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .form-control {
            padding: 10px 12px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #4facfe;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .filter-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .btn-input {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: none;
            text-align: center;
        }

        .btn-input:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .btn-input.show {
            display: inline-block;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 10px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table td {
            padding: 12px 10px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
        }

        .table tbody tr:hover {
            background: #f8f9fa;
        }

        .table tbody tr:nth-child(even) {
            background: rgba(79, 172, 254, 0.02);
        }

        .table tbody tr:nth-child(even):hover {
            background: #f0f8ff;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
        }

        .badge-male {
            background: #e3f2fd;
            color: #1976d2;
        }

        .badge-female {
            background: #fce4ec;
            color: #c2185b;
        }

        .badge-category {
            background: #f3e5f5;
            color: #7b1fa2;
            margin-bottom: 3px;
        }

        .age-info {
            font-size: 12px;
            color: #666;
            font-style: italic;
        }

        .payment-status {
            text-align: center;
            padding: 8px;
        }

        .payment-icon {
            font-size: 24px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .payment-icon:hover {
            transform: scale(1.2);
        }

        .payment-success {
            color: #28a745;
            filter: drop-shadow(0 2px 4px rgba(40, 167, 69, 0.3));
        }

        .payment-pending {
            color: #dc3545;
            filter: drop-shadow(0 2px 4px rgba(220, 53, 69, 0.3));
        }

        .payment-tooltip {
            position: relative;
            display: inline-block;
        }

        .payment-tooltip .tooltip-text {
            visibility: hidden;
            width: 140px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 8px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -70px;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
        }

        .payment-tooltip:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            position: relative;
            margin: 5% auto;
            width: 90%;
            max-width: 700px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .modal-body {
            padding: 20px;
            text-align: center;
        }

        .modal-body img {
            max-width: 100%;
            max-height: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            z-index: 1001;
        }

        .close:hover {
            opacity: 0.7;
        }

        .no-data {
            text-align: center;
            padding: 50px 20px;
            color: #666;
            font-style: italic;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: space-between;
            align-items: center;
        }

        .export-btn {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(67, 233, 123, 0.3);
        }

        .category-distribution {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .category-distribution h4 {
            margin-bottom: 15px;
            color: #333;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }

        .category-item {
            background: white;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Mobile Card View */
        .mobile-card-view {
            display: none;
        }

        .participant-card {
            background: white;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #4facfe;
        }

        .participant-card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .participant-number {
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .participant-name {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            flex: 1;
            margin: 0 12px;
            word-break: break-word;
        }

        .participant-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-item.full-width {
            grid-column: 1 / -1;
        }

        .detail-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .detail-value {
            font-size: 14px;
            color: #333;
            font-weight: 600;
        }

        .participant-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 10px;
            border-top: 1px solid #e9ecef;
            flex-wrap: wrap;
            gap: 8px;
        }

        .contact-link {
            color: #4facfe;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        /* Tablet Responsive */
        @media (max-width: 1024px) {
            .filters-grid {
                grid-template-columns: 1fr 1fr;
                gap: 15px;
            }
            
            .filter-group:first-child {
                grid-column: 1 / -1;
            }
            
            .filter-buttons {
                flex-direction: row;
                gap: 10px;
                grid-column: 1 / -1;
            }
            
            .statistics {
                grid-template-columns: repeat(3, 1fr);
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .table {
                min-width: 1000px;
            }

            .category-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .container {
                border-radius: 10px;
            }

            .content {
                padding: 15px;
            }
            
            .header {
                padding: 20px 15px;
            }

            .header h1 {
                font-size: 22px;
            }

            .header p {
                font-size: 14px;
            }

            .kegiatan-info h3 {
                font-size: 16px;
            }

            .kegiatan-info p {
                font-size: 14px;
            }
            
            .statistics {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .stat-card {
                padding: 15px 10px;
            }

            .stat-number {
                font-size: 24px;
            }

            .stat-label {
                font-size: 11px;
            }

            .filters {
                padding: 15px;
            }

            .filters-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .filter-group:first-child {
                grid-column: 1;
            }

            .filter-buttons {
                flex-direction: column;
                gap: 8px;
                grid-column: 1;
            }

            .btn, .btn-input {
                width: 100%;
                padding: 12px;
            }
            
            .actions {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }

            .actions > div {
                width: 100%;
            }

            .export-btn {
                display: block;
                text-align: center;
                width: 100%;
            }

            .actions > div:last-child {
                text-align: center;
            }

            /* Hide table, show cards on mobile */
            .table-container {
                display: none;
            }

            .mobile-card-view {
                display: block;
            }

            .category-distribution {
                padding: 15px;
            }

            .category-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .category-item {
                padding: 10px;
            }

            .modal-content {
                margin: 10% auto;
                width: 95%;
            }

            .modal-header {
                padding: 15px;
            }

            .modal-header h3 {
                font-size: 16px;
            }

            .modal-body {
                padding: 15px;
            }

            .modal-body img {
                max-height: 400px;
            }

            .close {
                right: 15px;
                top: 12px;
                font-size: 24px;
            }

            .no-data h3 {
                font-size: 18px;
            }

            .no-data p {
                font-size: 14px;
            }
        }

        /* Extra Small Mobile */
        @media (max-width: 480px) {
            .header h1 {
                font-size: 20px;
            }

            .statistics {
                grid-template-columns: 1fr;
            }

            .participant-details {
                grid-template-columns: 1fr;
            }

            .stat-number {
                font-size: 22px;
            }
        }

        /* Landscape Mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            .statistics {
                grid-template-columns: repeat(3, 1fr);
            }

            .participant-details {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Daftar Peserta Terdaftar</h1>
            <p>Kelola dan pantau peserta yang telah mendaftar</p>
            
            <div class="kegiatan-info">
                <h3><?= htmlspecialchars($kegiatanData['nama_kegiatan']) ?></h3>
                <p>Total Peserta Terdaftar: <?= $totalPeserta ?> orang</p>
            </div>
        </div>

        <div class="content">
            <a href="kegiatan.view.php" class="back-link">← Kembali Ke Kegiatan</a>

            <div class="statistics">
                <div class="stat-card primary">
                    <div class="stat-number"><?= $statistik['total'] ?></div>
                    <div class="stat-label">Total Peserta</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-number"><?= $statistik['laki_laki'] ?></div>
                    <div class="stat-label">Laki-laki</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-number"><?= $statistik['perempuan'] ?></div>
                    <div class="stat-label">Perempuan</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-number"><?= $statistik['sudah_bayar'] ?></div>
                    <div class="stat-label">Sudah Bayar</div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-number"><?= $statistik['belum_bayar'] ?></div>
                    <div class="stat-label">Belum Bayar</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= count($statistik['kategori']) ?></div>
                    <div class="stat-label">Kategori</div>
                </div>
            </div>

            <div class="filters">
                <form method="GET" action="">
                    <input type="hidden" name="kegiatan_id" value="<?= $kegiatan_id ?>">
                    
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="search">Cari Peserta</label>
                            <input type="text" 
                                   id="search" 
                                   name="search" 
                                   class="form-control" 
                                   placeholder="Nama, kota, club, atau sekolah..."
                                   value="<?= htmlspecialchars($search) ?>">
                        </div>
                        
                        <div class="filter-group">
                            <label for="filter_kategori">Kategori</label>
                            <select id="filter_kategori" name="filter_kategori" class="form-control">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($kategoriesList as $kategori): ?>
                                    <option value="<?= $kategori['id'] ?>" 
                                            <?= $filter_kategori == $kategori['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($kategori['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="filter_gender">Jenis Kelamin</label>
                            <select id="filter_gender" name="filter_gender" class="form-control">
                                <option value="">Semua</option>
                                <option value="Laki-laki" <?= $filter_gender == 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="Perempuan" <?= $filter_gender == 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                        
                        <div class="filter-group filter-buttons">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="#" 
                               id="inputBtn"
                               class="btn-input <?= $filter_kategori > 0 ? 'show' : '' ?>"
                               onclick="goToInput(event)">
                                📝 Input Score
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="actions">
                <div>
                    <?php if ($totalPeserta > 0): ?>
                        <a href="?export=excel&kegiatan_id=<?= $kegiatan_id ?>&search=<?= urlencode($search) ?>&filter_kategori=<?= $filter_kategori ?>&filter_gender=<?= urlencode($filter_gender) ?>" 
                           class="export-btn" target="_blank">
                            📊 Export ke Excel
                        </a>
                    <?php endif; ?>
                </div>
                <div>
                    <?php if ($totalPeserta > 0): ?>
                        <span style="color: #666; font-size: 14px;">
                            Menampilkan <?= $totalPeserta ?> peserta
                            <?php if (!empty($search) || $filter_kategori > 0 || !empty($filter_gender)): ?>
                                dengan filter
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Desktop Table View -->
            <div class="table-container">
                <?php if ($totalPeserta > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 30px;">No</th>
                                <th style="width: 180px;">Nama Peserta</th>
                                <th style="width: 80px;">Umur</th>
                                <th style="width: 90px;">Gender</th>
                                <th style="width: 140px;">Kategori</th>
                                <th style="width: 100px;">Asal Kota</th>
                                <th style="width: 130px;">Club</th>
                                <th style="width: 130px;">Sekolah</th>
                                <th style="width: 70px;">Kelas</th>
                                <th style="width: 110px;">No. HP</th>
                                <th style="width: 80px;">Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pesertaList as $index => $peserta): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($peserta['nama_peserta']) ?></strong>
                                        <div class="age-info">
                                            Lahir: <?= date('d/m/Y', strtotime($peserta['tanggal_lahir'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= $peserta['umur'] ?> tahun</strong>
                                    </td>
                                    <td>
                                        <span class="badge <?= $peserta['jenis_kelamin'] == 'Laki-laki' ? 'badge-male' : 'badge-female' ?>">
                                            <?= $peserta['jenis_kelamin'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="badge badge-category">
                                            <?= htmlspecialchars($peserta['category_name']) ?>
                                        </div>
                                        <div class="age-info">
                                            <?= $peserta['min_age'] ?>-<?= $peserta['max_age'] ?> thn 
                                            (<?= $peserta['category_gender'] == 'Campuran' ? 'Putra/Putri' : $peserta['category_gender'] ?>)
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($peserta['asal_kota'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($peserta['nama_club'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($peserta['sekolah'] ?: '-') ?></td>
                                    <td><?= htmlspecialchars($peserta['kelas'] ?: '-') ?></td>
                                    <td>
                                        <a href="tel:<?= htmlspecialchars($peserta['nomor_hp']) ?>" 
                                           style="color: #4facfe; text-decoration: none;">
                                            <?= htmlspecialchars($peserta['nomor_hp']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="payment-status">
                                            <?php if (!empty($peserta['bukti_pembayaran'])): ?>
                                                <div class="payment-tooltip">
                                                    <span class="payment-icon payment-success" 
                                                          onclick="showPaymentModal('<?= htmlspecialchars($peserta['nama_peserta']) ?>', '<?= $peserta['bukti_pembayaran'] ?>')">
                                                        📄✅
                                                    </span>
                                                    <span class="tooltip-text">Klik untuk lihat bukti pembayaran</span>
                                                </div>
                                            <?php else: ?>
                                                <div class="payment-tooltip">
                                                    <span class="payment-icon payment-pending">❌</span>
                                                    <span class="tooltip-text">Belum upload bukti pembayaran</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-data">
                        <h3>Belum Ada Peserta Terdaftar</h3>
                        <p>
                            <?php if (!empty($search) || $filter_kategori > 0 || !empty($filter_gender)): ?>
                                Tidak ada peserta yang sesuai dengan filter yang dipilih.
                                <br><br>
                                <a href="?kegiatan_id=<?= $kegiatan_id ?>" class="btn btn-secondary">Reset Filter</a>
                            <?php else: ?>
                                Belum ada peserta yang mendaftar untuk kegiatan ini.
                                <br><br>
                                <a href="form_pendaftaran.php?kegiatan_id=<?= $kegiatan_id ?>" class="btn btn-success">Daftarkan Peserta Pertama</a>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mobile Card View -->
            <div class="mobile-card-view">
                <?php if ($totalPeserta > 0): ?>
                    <?php foreach ($pesertaList as $index => $peserta): ?>
                        <div class="participant-card">
                            <div class="participant-card-header">
                                <div class="participant-number"><?= $index + 1 ?></div>
                                <div class="participant-name"><?= htmlspecialchars($peserta['nama_peserta']) ?></div>
                                <div class="payment-status">
                                    <?php if (!empty($peserta['bukti_pembayaran'])): ?>
                                        <span class="payment-icon payment-success" 
                                              onclick="showPaymentModal('<?= htmlspecialchars($peserta['nama_peserta']) ?>', '<?= $peserta['bukti_pembayaran'] ?>')">📄✅</span>
                                    <?php else: ?>
                                        <span class="payment-icon payment-pending">❌</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="participant-details">
                                <div class="detail-item">
                                    <span class="detail-label">Gender</span>
                                    <span class="detail-value">
                                        <span class="badge <?= $peserta['jenis_kelamin'] == 'Laki-laki' ? 'badge-male' : 'badge-female' ?>">
                                            <?= $peserta['jenis_kelamin'] ?>
                                        </span>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Umur</span>
                                    <span class="detail-value"><?= $peserta['umur'] ?> tahun</span>
                                </div>
                                <div class="detail-item full-width">
                                    <span class="detail-label">Kategori</span>
                                    <span class="detail-value">
                                        <span class="badge badge-category"><?= htmlspecialchars($peserta['category_name']) ?></span>
                                        <span class="age-info" style="display: block; margin-top: 4px;">
                                            <?= $peserta['min_age'] ?>-<?= $peserta['max_age'] ?> thn 
                                            (<?= $peserta['category_gender'] == 'Campuran' ? 'Putra/Putri' : $peserta['category_gender'] ?>)
                                        </span>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Asal Kota</span>
                                    <span class="detail-value"><?= htmlspecialchars($peserta['asal_kota'] ?: '-') ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Kelas</span>
                                    <span class="detail-value"><?= htmlspecialchars($peserta['kelas'] ?: '-') ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Club</span>
                                    <span class="detail-value"><?= htmlspecialchars($peserta['nama_club'] ?: '-') ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Sekolah</span>
                                    <span class="detail-value"><?= htmlspecialchars($peserta['sekolah'] ?: '-') ?></span>
                                </div>
                            </div>
                            <div class="participant-footer">
                                <a href="tel:<?= htmlspecialchars($peserta['nomor_hp']) ?>" class="contact-link">📞 <?= htmlspecialchars($peserta['nomor_hp']) ?></a>
                                <span class="age-info">Lahir: <?= date('d/m/Y', strtotime($peserta['tanggal_lahir'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-data">
                        <h3>Belum Ada Peserta Terdaftar</h3>
                        <p>
                            <?php if (!empty($search) || $filter_kategori > 0 || !empty($filter_gender)): ?>
                                Tidak ada peserta yang sesuai dengan filter yang dipilih.
                                <br><br>
                                <a href="?kegiatan_id=<?= $kegiatan_id ?>" class="btn btn-secondary">Reset Filter</a>
                            <?php else: ?>
                                Belum ada peserta yang mendaftar untuk kegiatan ini.
                                <br><br>
                                <a href="form_pendaftaran.php?kegiatan_id=<?= $kegiatan_id ?>" class="btn btn-success">Daftarkan Peserta Pertama</a>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($statistik['kategori'])): ?>
                <div class="category-distribution">
                    <h4>Distribusi per Kategori:</h4>
                    <div class="category-grid">
                        <?php foreach ($statistik['kategori'] as $kategori => $jumlah): ?>
                            <div class="category-item">
                                <strong><?= htmlspecialchars($kategori) ?></strong><br>
                                <span style="color: #4facfe; font-size: 18px; font-weight: 600;"><?= $jumlah ?> orang</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close" onclick="closePaymentModal()">&times;</span>
                <h3 id="modal-title">Bukti Pembayaran</h3>
            </div>
            <div class="modal-body">
                <div id="modal-image-container"></div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('filter_kategori').addEventListener('change', function() {
            updateInputButton();
        });

        document.getElementById('filter_gender').addEventListener('change', function() {
            updateInputButton();
        });

        document.getElementById('search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });

        document.getElementById('search').addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                this.form.submit();
            }
        });

        function updateInputButton() {
            const kategoriSelect = document.getElementById('filter_kategori');
            const inputBtn = document.getElementById('inputBtn');
            
            if (kategoriSelect.value && kategoriSelect.value !== '') {
                inputBtn.classList.add('show');
                inputBtn.href = 'detail.php?action=scorecard&resource=index&kegiatan_id=<?= $kegiatan_id ?>&category_id=' + kategoriSelect.value;
            } else {
                inputBtn.classList.remove('show');
            }
        }

        function goToInput(e) {
            const kategoriSelect = document.getElementById('filter_kategori');
            
            if (!kategoriSelect.value || kategoriSelect.value === '') {
                e.preventDefault();
                alert('Silakan pilih kategori terlebih dahulu!');
                return false;
            }
            
            window.location.href = 'detail.php?action=scorecard&resource=index&kegiatan_id=<?= $kegiatan_id ?>&category_id=' + kategoriSelect.value;
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateInputButton();
        });

        function showPaymentModal(namaPeserta, fileName) {
            const modal = document.getElementById('paymentModal');
            const modalTitle = document.getElementById('modal-title');
            const imageContainer = document.getElementById('modal-image-container');
            
            modalTitle.textContent = 'Bukti Pembayaran - ' + namaPeserta;
            
            const fileExtension = fileName.toLowerCase().split('.').pop();
            const imagePath = 'uploads/pembayaran/' + fileName;
            
            if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
                imageContainer.innerHTML = `
                    <img src="${imagePath}" alt="Bukti Pembayaran" style="max-width: 100%; max-height: 500px; border-radius: 8px;">
                    <div style="margin-top: 15px; padding: 10px; background: #f8f9fa; border-radius: 6px; font-size: 14px; color: #666;">
                        <strong>File:</strong> ${fileName}<br>
                        <strong>Peserta:</strong> ${namaPeserta}
                    </div>
                `;
            } else if (fileExtension === 'pdf') {
                imageContainer.innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <div style="font-size: 48px; color: #dc3545; margin-bottom: 20px;">📄</div>
                        <h4>File PDF</h4>
                        <p style="margin: 15px 0; color: #666;">File bukti pembayaran dalam format PDF</p>
                        <a href="${imagePath}" target="_blank" class="btn btn-primary" style="margin: 10px;">Buka PDF</a>
                        <a href="${imagePath}" download="${fileName}" class="btn btn-success" style="margin: 10px;">Download</a>
                        <div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 6px; font-size: 14px; color: #666;">
                            <strong>File:</strong> ${fileName}<br>
                            <strong>Peserta:</strong> ${namaPeserta}
                        </div>
                    </div>
                `;
            } else {
                imageContainer.innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <div style="font-size: 48px; color: #ffc107; margin-bottom: 20px;">⚠️</div>
                        <h4>File tidak dapat ditampilkan</h4>
                        <p style="margin: 15px 0; color: #666;">Format file tidak didukung untuk preview</p>
                        <a href="${imagePath}" target="_blank" class="btn btn-primary" style="margin: 10px;">Buka File</a>
                        <a href="${imagePath}" download="${fileName}" class="btn btn-success" style="margin: 10px;">Download</a>
                        <div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-radius: 6px; font-size: 14px; color: #666;">
                            <strong>File:</strong> ${fileName}<br>
                            <strong>Peserta:</strong> ${namaPeserta}
                        </div>
                    </div>
                `;
            }
            
            modal.style.display = 'block';
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('paymentModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closePaymentModal();
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>