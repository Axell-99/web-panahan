<?php
// debug_session.php
// File untuk cek isi session dan troubleshooting

session_start();

echo "<h2>Debug Session Information</h2>";
echo "<hr>";

// Cek apakah ada session
if (empty($_SESSION)) {
    echo "<p style='color: red;'><strong>SESSION KOSONG!</strong></p>";
    echo "<p>Silakan login terlebih dahulu di <a href='index.php'>index.php</a></p>";
} else {
    echo "<p style='color: green;'><strong>SESSION AKTIF</strong></p>";
    echo "<h3>Isi Session:</h3>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
}

echo "<hr>";

// Cek detail session
echo "<h3>Detail Session:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Key</th><th>Value</th></tr>";

$keys = ['login', 'user_id', 'username', 'role'];
foreach ($keys as $key) {
    $value = isset($_SESSION[$key]) ? $_SESSION[$key] : '<span style="color:red;">TIDAK ADA</span>';
    echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
}
echo "</table>";

echo "<hr>";

// Cek fungsi
echo "<h3>Test Fungsi:</h3>";

function isLoggedIn() {
    return isset($_SESSION['login']) && $_SESSION['login'] === true;
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

echo "<p>isLoggedIn(): " . (isLoggedIn() ? '<span style="color:green;">TRUE ✓</span>' : '<span style="color:red;">FALSE ✗</span>') . "</p>";
echo "<p>isAdmin(): " . (isAdmin() ? '<span style="color:green;">TRUE ✓</span>' : '<span style="color:red;">FALSE ✗</span>') . "</p>";

echo "<hr>";

// Cek tipe data
echo "<h3>Cek Tipe Data:</h3>";
if (isset($_SESSION['login'])) {
    echo "<p>Tipe data \$_SESSION['login']: " . gettype($_SESSION['login']) . "</p>";
    echo "<p>Nilai \$_SESSION['login']: ";
    var_dump($_SESSION['login']);
    echo "</p>";
}

if (isset($_SESSION['role'])) {
    echo "<p>Tipe data \$_SESSION['role']: " . gettype($_SESSION['role']) . "</p>";
    echo "<p>Nilai \$_SESSION['role']: ";
    var_dump($_SESSION['role']);
    echo "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Ke Login</a> | <a href='dashboard.php'>Ke Dashboard</a> | <a href='kegiatan.view.php'>Ke Kegiatan</a></p>";
?>