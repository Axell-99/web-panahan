<?php
session_start(); 
include 'panggil.php';
// Cek jika sudah login
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: dashboard.php');
    } else {
        header('Location: kegiatan.view.php');
    }
    exit;
}

$error_message = '';

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $password = trim($_POST['password']);

    if (empty($name) || empty($password)) {
        $error_message = 'Harap isi nama dan password';
    } else {
        $sql = "SELECT * FROM users WHERE name = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session dengan benar
                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['name'];
                $_SESSION['role'] = $user['role'] ?? 'user';
                
                // PENTING: Regenerate session ID untuk keamanan
                session_regenerate_id(true);
                
                // Redirect berdasarkan role
                if ($_SESSION['role'] === 'admin') {
                    header('Location: dashboard.php');
                } else {
                    header('Location: kegiatan.view.php');
                }
                exit;
            } else {
                $error_message = 'Login gagal! Username atau password salah.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Turnamen Panahan</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }

        .container {
            position: relative;
            z-index: 1;
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 400px;
            min-height: 500px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .header {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            text-align: center;
            padding: 40px 20px 30px;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '🏹';
            position: absolute;
            top: -20px;
            right: -20px;
            font-size: 80px;
            opacity: 0.3;
            transform: rotate(15deg);
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            z-index: 1;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .login-box {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2d3436;
            font-size: 14px;
        }

        .input-field {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid rgba(116, 185, 255, 0.2);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .input-field:focus {
            outline: none;
            border-color: #74b9ff;
            box-shadow: 0 0 0 3px rgba(116, 185, 255, 0.1);
            transform: translateY(-2px);
        }

        .input-field::placeholder {
            color: #636e72;
            opacity: 0.7;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 10px;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(116, 185, 255, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .forgot-password {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(116, 185, 255, 0.2);
        }

        .forgot-password a {
            color: #74b9ff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .forgot-password a:hover {
            color: #0984e3;
            text-decoration: underline;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-error {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            color: #d63031;
        }

        .alert-success {
            background: rgba(0, 184, 148, 0.1);
            border: 1px solid rgba(0, 184, 148, 0.3);
            color: #00b894;
        }

        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 38px;
            color: #636e72;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .form-group:focus-within .input-icon {
            color: #74b9ff;
            opacity: 1;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 38px;
            background: none;
            border: none;
            color: #636e72;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: #74b9ff;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .container {
                width: 90%;
                min-width: 300px;
                margin: 20px;
            }

            .login-box {
                padding: 30px 20px;
            }

            .header {
                padding: 30px 20px 25px;
            }

            .header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Turnamen Panahan</h1>
            <p>Sistem Pendaftaran</p>
        </div>
        
        <div class="login-box">
            <form method="POST" id="loginForm">
                <?php if (isset($_GET['message']) && $_GET['message'] == 'logout_success'): ?>
                    <div class="alert alert-success">
                        Logout berhasil! Silakan login kembali.
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Nama</label>
                    <input 
                        type="text" 
                        id="name"
                        name="name" 
                        class="input-field"
                        placeholder="Masukkan nama Anda"
                        value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                        required
                    >
                    <div class="input-icon">👤</div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        class="input-field"
                        placeholder="Masukkan password Anda"
                        required
                    >
                    <button type="button" class="password-toggle" onclick="togglePassword()">👁️</button>
                </div>

                <button type="submit" name="submit" class="login-btn" id="loginBtn">
                    Login
                </button>
            </form>

            <div class="forgot-password">
                <a href="debug_session.php">Cek Session (Debug)</a>
            </div>
        </div>
    </div>

    <script>
        // Password toggle functionality
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleBtn.textContent = '🙈';
            } else {
                passwordField.type = 'password';
                toggleBtn.textContent = '👁️';
            }
        }

        // Auto focus pada input pertama
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('name').focus();
        });

        // Enter key navigation
        document.getElementById('name').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('password').focus();
            }
        });

        document.getElementById('password').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('loginForm').submit();
            }
        });
    </script>
</body>
</html>