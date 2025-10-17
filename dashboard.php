<?php
// PASTIKAN SESSION CHECK ADA DI PALING ATAS
include 'panggil.php';
include 'check_access.php';
requireAdmin();

// Redirect ke login jika belum login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header('Location: index.php');
    exit;
}

// Ambil data user dari session
$username = $_SESSION['username'] ?? 'User';
$name = $_SESSION['name'] ?? $username;
$role = $_SESSION['role'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Turnamen Panahan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* SIDEBAR */
        .sidebar {
            width: 280px;
            background: white;
            display: flex;
            flex-direction: column;
        }

        .logo {
            padding: 30px 20px;
            background: #ff6b6b;
            color: white;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }

        .menu-section {
            padding: 20px;
            flex: 1;
        }

        /* MENU ITEMS - SUPER SIMPLE */
        .menu-item {
            margin-bottom: 10px;
        }

        .menu-item a {
            display: block;
            padding: 15px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .menu-item a:hover {
            background: #2980b9;
        }

        /* DROPDOWN - SUPER SIMPLE */
        .dropdown-btn {
            width: 100%;
            padding: 15px 20px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            text-align: left;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
        }

        .dropdown-btn:hover {
            background: #2980b9;
        }

        .dropdown-content {
            display: none;
            background: #ecf0f1;
            border-radius: 8px;
            margin-top: 5px;
            padding: 5px;
        }

        .dropdown-content.active {
            display: block;
        }

        .dropdown-content a {
            display: block;
            padding: 10px 20px;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 5px;
            margin: 2px 0;
        }

        .dropdown-content a:hover {
            background: #3498db;
            color: white;
        }

        .logout-section {
            padding: 20px;
            border-top: 1px solid #ddd;
        }

        .logout-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: #e74c3c;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 24px;
            color: #2c3e50;
        }

        .username-container {
            background: #3498db;
            padding: 10px 20px;
            border-radius: 25px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-logo {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: 2px solid white;
        }

        .dashboard-content {
            flex: 1;
            padding: 30px;
        }

        .welcome-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .welcome-card h2 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .welcome-card p {
            color: #7f8c8d;
        }

        /* MOBILE */
        .hamburger {
            display: none;
        }

        @media (max-width: 768px) {
            .hamburger {
                display: block;
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
                background: white;
                border: none;
                padding: 10px;
                border-radius: 5px;
                cursor: pointer;
            }

            .hamburger span {
                display: block;
                width: 25px;
                height: 3px;
                background: #333;
                margin: 5px 0;
            }

            .sidebar {
                position: fixed;
                left: -280px;
                top: 0;
                bottom: 0;
                z-index: 1000;
                transition: left 0.3s;
            }

            .sidebar.active {
                left: 0;
            }

            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 999;
            }

            .overlay.active {
                display: block;
            }

            .header {
                padding-top: 70px;
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Hamburger -->
    <button class="hamburger" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Overlay -->
    <div class="overlay" onclick="toggleMenu()"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            🏹 TURNAMEN PANAHAN
        </div>
        
        <div class="menu-section">
            <!-- Dashboard -->
            <div class="menu-item">
                <a href="dashboard.php">🏠 Dashboard</a>
            </div>

            <!-- Master Data Dropdown -->
            <div class="menu-item">
                <button class="dropdown-btn" onclick="toggleDropdown(this)">
                    📊 Master Data ▾
                </button>
                <div class="dropdown-content">
                    <a href="users.php">👥 Users</a>
                    <a href="categori.view.php">📋 Kategori</a>
                    <a href="pertandingan.view.php">🏆 Pertandingan</a>
                </div>
            </div>

            <!-- Kegiatan -->
            <div class="menu-item">
                <a href="kegiatan.view.php">📅 Kegiatan</a>
            </div>

            <!-- Peserta -->
            <div class="menu-item">
                <a href="peserta.view.php">👨‍👩‍👧‍👦 Peserta</a>
            </div>

            <!-- Statistik -->
            <div class="menu-item">
                <a href="statistik.php">📝 Statistik</a>
            </div>
        </div>

        <div class="logout-section">
            <a href="logout.php" class="logout-btn" onclick="return confirm('Yakin ingin logout?')">
                🚪 Logout
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h1>Dashboard <?php echo ucfirst($role); ?></h1>
                <p>Sistem Pendaftaran Turnamen Panahan</p>
            </div>
            <div class="username-container">
                <span><?php echo htmlspecialchars($name); ?></span>
                <img src="angzay.png" alt="Profile" class="profile-logo" onerror="this.style.display='none';">
            </div>
        </div>
        
        <div class="dashboard-content">
            <div class="welcome-card">
                <h2>🎯 Selamat Datang, <?php echo htmlspecialchars($name); ?>!</h2>
                <p>Anda Sekarang Berada di Dashboard Turnamen Panahan</p>
            </div>
        </div>
    </div>

    <script>
        // Toggle Dropdown
        function toggleDropdown(button) {
            const content = button.nextElementSibling;
            const allDropdowns = document.querySelectorAll('.dropdown-content');
            
            // Close all dropdowns
            allDropdowns.forEach(d => {
                if (d !== content) {
                    d.classList.remove('active');
                }
            });
            
            // Toggle current
            content.classList.toggle('active');
            
            // Change arrow
            if (content.classList.contains('active')) {
                button.textContent = button.textContent.replace('▾', '▴');
            } else {
                button.textContent = button.textContent.replace('▴', '▾');
            }
        }

        // Toggle Mobile Menu
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.overlay');
            
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        // Close menu when clicking menu item on mobile
        if (window.innerWidth <= 768) {
            document.querySelectorAll('.menu-item a, .dropdown-content a').forEach(link => {
                link.addEventListener('click', toggleMenu);
            });
        }
    </script>
</body>
</html>