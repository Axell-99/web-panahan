<?php
include 'panggil.php';

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error_message = '';

if (isset($_POST['submit'])) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($name === '' || $password === '') {
        $error_message = 'Harap isi nama dan password';
    } else {
        // $conn = new mysqli('localhost', 'root', '', 'panahan_turnament_new');
        
        if ($conn->connect_error) {
            $error_message = 'Koneksi database gagal: ' . $conn->connect_error;
        } else {
            // Ganti 'username' dengan nama kolom yang sebenarnya ada di tabel
            // Kemungkinan: name, nama, full_name, user_name
            $sql = "SELECT * FROM users WHERE name = ?";  // UBAH 'name' sesuai kolom di database
            
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $name);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['login'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['name']; // Sesuaikan dengan kolom
                    $_SESSION['role'] = $user['role'] ?? 'user';
                    
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $error_message = 'Login gagal! Periksa nama dan password.';
                }
                
                $stmt->close();
            }
            $conn->close();
        }
    }
}
?>