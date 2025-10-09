<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. Testing PHP...<br>";

// Test koneksi database
try {
    include 'panggil.php';
    echo "2. Database connected: " . ($conn ? "YES" : "NO") . "<br>";
} catch (Exception $e) {
    echo "2. Database Error: " . $e->getMessage() . "<br>";
    die();
}

// Test parameter
echo "3. kegiatan_id: " . (isset($_GET['kegiatan_id']) ? $_GET['kegiatan_id'] : "TIDAK ADA") . "<br>";
echo "4. category_id: " . (isset($_GET['category_id']) ? $_GET['category_id'] : "TIDAK ADA") . "<br>";

// Test query
if ($conn) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM kegiatan");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "5. Total kegiatan di database: " . $row['total'] . "<br>";
    } else {
        echo "5. Query Error: " . mysqli_error($conn) . "<br>";
    }
}

echo "<br>Jika semua OK, coba akses: <a href='detail.php?kegiatan_id=1'>detail.php?kegiatan_id=1</a>";
?>