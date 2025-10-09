<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Detail Debug Test</h3>";

// Test 1: Cek file dependencies
echo "1. Cek file check_access.php: ";
if (file_exists('check_access.php')) {
    echo "✅ ADA<br>";
    echo "   Isi file: <pre>";
    echo htmlspecialchars(file_get_contents('check_access.php'));
    echo "</pre><br>";
} else {
    echo "❌ TIDAK ADA<br>";
}

// Test 2: Cek function requireLogin
echo "2. Test requireLogin(): ";
if (file_exists('check_access.php')) {
    include 'check_access.php';
    if (function_exists('requireLogin')) {
        echo "✅ Function EXISTS<br>";
        // Commented out to prevent redirect
        // requireLogin();
    } else {
        echo "❌ Function NOT FOUND<br>";
    }
}

// Test 3: Session
echo "3. Session status: ";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "✅ Started<br>";
} else {
    echo "✅ Already active<br>";
}

// Test 4: Database
echo "4. Database connection: ";
try {
    include 'panggil.php';
    if (isset($conn) && $conn) {
        echo "✅ Connected<br>";
    } else {
        echo "❌ Failed<br>";
        die("Stop - no database connection");
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    die();
}

// Test 5: Query kegiatan
echo "5. Test query kegiatan:<br>";
$query = "SELECT * FROM kegiatan LIMIT 1";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        echo "   ✅ ID: " . $row['id'] . "<br>";
        echo "   ✅ Nama: " . $row['nama_kegiatan'] . "<br>";
        $test_id = $row['id'];
    } else {
        echo "   ❌ No data<br>";
        $test_id = 1;
    }
} else {
    echo "   ❌ Query error: " . mysqli_error($conn) . "<br>";
    $test_id = 1;
}

// Test 6: Akses detail.php dengan parameter
echo "<br>6. Test akses detail.php:<br>";
echo "<a href='detail.php?kegiatan_id=$test_id' target='_blank' style='display:inline-block; padding:10px 20px; background:#4CAF50; color:white; text-decoration:none; border-radius:5px; margin:10px 0;'>
    🔗 Buka detail.php?kegiatan_id=$test_id
</a><br><br>";

// Test 7: Cek tabel categories
echo "7. Test tabel categories:<br>";
$query_cat = "SELECT * FROM categories LIMIT 3";
$result_cat = mysqli_query($conn, $query_cat);
if ($result_cat) {
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Status</th></tr>";
    while ($cat = mysqli_fetch_assoc($result_cat)) {
        echo "<tr>";
        echo "<td>" . $cat['id'] . "</td>";
        echo "<td>" . htmlspecialchars($cat['name']) . "</td>";
        echo "<td>" . (isset($cat['status']) ? $cat['status'] : 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
} else {
    echo "   ❌ Error: " . mysqli_error($conn) . "<br>";
}

// Test 8: Cek tabel peserta
echo "8. Test tabel peserta:<br>";
$query_peserta = "SELECT COUNT(*) as total FROM peserta";
$result_peserta = mysqli_query($conn, $query_peserta);
if ($result_peserta) {
    $row_peserta = mysqli_fetch_assoc($result_peserta);
    echo "   ✅ Total peserta: " . $row_peserta['total'] . "<br>";
} else {
    echo "   ❌ Error: " . mysqli_error($conn) . "<br>";
}

// Test 9: Simulasi akses detail.php
echo "<br>9. Simulasi load detail.php:<br>";
$_GET['kegiatan_id'] = $test_id;
echo "   Set kegiatan_id = $test_id<br>";

// Test 10: Cek struktur tabel
echo "<br>10. Struktur tabel peserta:<br>";
$query_structure = "DESCRIBE peserta";
$result_structure = mysqli_query($conn, $query_structure);
if ($result_structure) {
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($field = mysqli_fetch_assoc($result_structure)) {
        echo "<tr>";
        echo "<td>" . $field['Field'] . "</td>";
        echo "<td>" . $field['Type'] . "</td>";
        echo "<td>" . $field['Null'] . "</td>";
        echo "<td>" . ($field['Key'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "   ❌ Error: " . mysqli_error($conn) . "<br>";
}

echo "<br><br><h4>Jika semua test di atas OK, tapi detail.php masih tidak bisa dibuka, coba:</h4>";
echo "<ol>";
echo "<li>Klik tombol hijau di atas</li>";
echo "<li>Screenshot error yang muncul</li>";
echo "<li>Cek console browser (F12) untuk JavaScript errors</li>";
echo "<li>Cek error log PHP di server</li>";
echo "</ol>";
?>