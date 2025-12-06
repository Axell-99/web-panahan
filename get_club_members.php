<?php
header('Content-Type: application/json');
include 'panggil.php';

if (!isset($_GET['club']) || empty($_GET['club'])) {
    echo json_encode(['success' => false, 'message' => 'Nama club tidak ditemukan']);
    exit;
}

$clubName = $_GET['club'];

// Query untuk mendapatkan anggota club
$query = "SELECT DISTINCT nama_peserta, jenis_kelamin 
          FROM peserta 
          WHERE nama_club = ? 
          ORDER BY nama_peserta ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $clubName);
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = [
        'nama_peserta' => $row['nama_peserta'],
        'jenis_kelamin' => $row['jenis_kelamin']
    ];
}

$stmt->close();

$response = [
    'success' => true,
    'members' => $members
];

echo json_encode($response);
?>