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
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow-x: hidden;
        }

        .container {
            display: flex;
            width: 100%;
            backdrop-filter: blur(10px);
            position: relative;
        }

        /* Hamburger Menu */
        .hamburger {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 12px;
            padding: 12px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .hamburger:hover {
            transform: scale(1.05);
        }

        .hamburger span {
            display: block;
            width: 25px;
            height: 3px;
            background: #2d3436;
            margin: 5px 0;
            transition: all 0.3s ease;
            border-radius: 3px;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }

        /* Overlay untuk mobile */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .overlay.active {
            display: block;
            opacity: 1;
        }

        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            display: flex;
            flex-direction: column;
            padding: 0;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            z-index: 1000;
        }

        .logo {
            padding: 30px 20px;
            text-align: center;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            font-size: 24px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            overflow: hidden;
        }

        .logo::before {
            content: '🏹';
            position: absolute;
            top: -20px;
            right: -20px;
            font-size: 60px;
            opacity: 0.3;
            transform: rotate(15deg);
        }

        .menu-section {
            padding: 20px;
            flex: 1;
            overflow-y: auto;
        }

        .menu-btn, .dropdown-btn {
            width: 100%;
            margin: 8px 0;
            padding: 15px 20px;
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            text-align: left;
            font-size: 15px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .menu-btn::before, .dropdown-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .menu-btn:hover::before, .dropdown-btn:hover::before {
            left: 100%;
        }

        .menu-btn:hover, .dropdown-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(116, 185, 255, 0.4);
        }

        .menu-btn a {
            color: white;
            text-decoration: none;
            display: block;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .menu-btn {
            padding: 0;
        }

        .menu-btn a {
            padding: 15px 20px;
        }

        .dropdown {
            width: 100%;
        }

        .dropdown-content {
            display: none;
            flex-direction: column;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-top: 8px;
            overflow: hidden;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-content a {
            padding: 12px 20px;
            text-decoration: none;
            color: #2d3436;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .dropdown-content a:hover {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white;
            border-left: 3px solid #ff6b6b;
            transform: translateX(5px);
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            min-width: 0;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .header-left h1 {
            color: #2d3436;
            font-size: 28px;
            font-weight: 700;
        }

        .header-left p {
            color: #636e72;
            margin-top: 5px;
        }

        .username-container {
            display: flex;
            align-items: center;
            gap: 15px;
            background: linear-gradient(135deg, #348dd6ff, #348dd6ff);
            padding: 12px 20px;
            border-radius: 50px;
            color: white;
            box-shadow: 0 4px 15px rgba(253, 121, 168, 0.3);
        }

        .username {
            font-size: 16px;
            font-weight: 600;
        }

        .profile-logo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .dashboard-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(116, 185, 255, 0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .welcome-card h2 {
            color: #2d3436;
            font-size: 32px;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .welcome-card p {
            color: #636e72;
            font-size: 18px;
            position: relative;
            z-index: 1;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b6b, #ee5a24);
        }

        .stat-card h3 {
            color: #2d3436;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
            color: #0984e3;
            margin-bottom: 5px;
        }

        .logout-section {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .logout-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ff7675, #d63031);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 118, 117, 0.4);
        }

        /* Scrollbar Styling */
        .dashboard-content::-webkit-scrollbar,
        .menu-section::-webkit-scrollbar {
            width: 6px;
        }

        .dashboard-content::-webkit-scrollbar-track,
        .menu-section::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .dashboard-content::-webkit-scrollbar-thumb,
        .menu-section::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            border-radius: 10px;
        }

        .dashboard-content::-webkit-scrollbar-thumb:hover,
        .menu-section::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #0984e3, #74b9ff);
        }

        /* Responsive Design untuk Tablet */
        @media (max-width: 1024px) {
            .sidebar {
                width: 250px;
            }

            .header-left h1 {
                font-size: 24px;
            }

            .welcome-card h2 {
                font-size: 28px;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        /* Responsive Design untuk Mobile */
        @media (max-width: 768px) {
            .hamburger {
                display: block;
            }

            .sidebar {
                position: fixed;
                left: -280px;
                top: 0;
                bottom: 0;
                width: 280px;
                z-index: 1000;
                transition: left 0.3s ease;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                width: 100%;
                margin-left: 0;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                padding: 80px 20px 20px;
                text-align: center;
            }

            .header-left h1 {
                font-size: 22px;
            }

            .header-left p {
                font-size: 14px;
            }

            .username-container {
                padding: 10px 15px;
                gap: 10px;
            }

            .username {
                font-size: 14px;
            }

            .profile-logo {
                width: 35px;
                height: 35px;
            }

            .dashboard-content {
                padding: 20px 15px;
            }

            .welcome-card {
                padding: 25px 20px;
                border-radius: 15px;
            }

            .welcome-card h2 {
                font-size: 24px;
            }

            .welcome-card p {
                font-size: 16px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-card h3 {
                font-size: 20px;
            }

            .stat-card .number {
                font-size: 32px;
            }

            .logo {
                padding: 25px 15px;
                font-size: 20px;
                letter-spacing: 1px;
            }

            .menu-section {
                padding: 15px;
            }

            .menu-btn, .dropdown-btn {
                padding: 12px 15px;
                font-size: 14px;
            }

            .dropdown-content a {
                padding: 10px 15px;
                font-size: 14px;
            }

            .logout-section {
                padding: 15px;
            }

            .logout-btn {
                padding: 12px;
                font-size: 14px;
            }
        }

        /* Extra Small Mobile */
        @media (max-width: 480px) {
            .hamburger {
                top: 15px;
                left: 15px;
                padding: 10px;
            }

            .hamburger span {
                width: 22px;
                height: 2.5px;
            }

            .header {
                padding: 70px 15px 15px;
            }

            .header-left h1 {
                font-size: 20px;
            }

            .header-left p {
                font-size: 13px;
            }

            .welcome-card {
                padding: 20px 15px;
            }

            .welcome-card h2 {
                font-size: 20px;
            }

            .welcome-card p {
                font-size: 14px;
            }

            .stat-card h3 {
                font-size: 18px;
            }

            .stat-card .number {
                font-size: 28px;
            }

            .dashboard-content {
                padding: 15px 10px;
            }

            .logo {
                font-size: 18px;
                padding: 20px 10px;
            }

            .logo::before {
                font-size: 50px;
            }
        }

        /* Landscape Mode untuk Mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            .header {
                flex-direction: row;
                padding: 15px 20px 15px 70px;
            }

            .welcome-card {
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Hamburger Menu -->
    <button class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <div class="container">
        <div class="sidebar" id="sidebar">
            <div class="logo">
                Turnamen Panahan
            </div>
            
            <div class="menu-section">
                <button class="menu-btn"><a href="dashboard.php">🏠 Dashboard</a></button>

                <!-- Dropdown Master Data -->
                <div class="dropdown">
                    <button class="dropdown-btn">📊 Master Data ▾</button>
                    <div class="dropdown-content">
                        <a href="users.php">👥 Users</a>
                        <a href="categori.view.php">📋 Kategori</a>
                        <a href="pertandingan.view.php">🏆 Pertandingan</a>
                    </div>
                </div>

                <button class="menu-btn"><a href="kegiatan.view.php">📅 Kegiatan</a></button>
                <button class="menu-btn"><a href="peserta.view.php">👨‍👩‍👧‍👦 Peserta</a></button>
                <button class="menu-btn"><a href="statistik.php">📝 Statistik</a></button>
            </div>

            <div class="logout-section">
                <a href="logout.php" class="logout-btn" onclick="return confirm('Yakin ingin logout?')">
                    🚪 Logout
                </a>
            </div>
        </div>

        <div class="main-content">
            <div class="header">
                <div class="header-left">
                    <h1>Dashboard <?php echo ucfirst($role); ?></h1>
                    <p>Sistem Pendaftaran Turnamen Panahan</p>
                </div>
                <div class="username-container">
                    <span class="username"><?php echo htmlspecialchars($name); ?></span>
                    <img src="angzay.png" alt="Profile" class="profile-logo" onerror="this.style.display='none';">
                </div>
            </div>
            
            <div class="dashboard-content">
                <div class="welcome-card">
                    <h2>🎯 Selamat Datang, <?php echo htmlspecialchars($name); ?>!</h2>
                    <p>Anda Sekarang Berada di Dashboard Turnamen Panahan</p>
                </div>

                <!-- Uncomment jika ingin menampilkan statistik -->
                <!-- <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Peserta</h3>
                        <div class="number">127</div>
                        <p>Peserta terdaftar</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Pertandingan Aktif</h3>
                        <div class="number">8</div>
                        <p>Sedang berlangsung</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Kategori</h3>
                        <div class="number">12</div>
                        <p>Kategori tersedia</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Kegiatan Bulan Ini</h3>
                        <div class="number">5</div>
                        <p>Event terjadwal</p>
                    </div>
                </div> -->
            </div>
        </div>
    </div>

    <script>
        // Hamburger Menu Toggle
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        function toggleSidebar() {
            hamburger.classList.toggle('active');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        hamburger.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);

        // Close sidebar when clicking menu item on mobile
        if (window.innerWidth <= 768) {
            document.querySelectorAll('.menu-btn a, .dropdown-content a').forEach(link => {
                link.addEventListener('click', () => {
                    toggleSidebar();
                });
            });
        }

        // Enhanced dropdown functionality
        document.querySelectorAll('.dropdown-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const content = btn.nextElementSibling;
                const isOpen = content.style.display === 'flex';
                
                // Close all other dropdowns
                document.querySelectorAll('.dropdown-content').forEach(dc => {
                    dc.style.display = 'none';
                });
                document.querySelectorAll('.dropdown-btn').forEach(db => {
                    db.innerHTML = db.innerHTML.replace('▴', '▾');
                });
                
                // Toggle current dropdown
                if (!isOpen) {
                    content.style.display = 'flex';
                    btn.innerHTML = btn.innerHTML.replace('▾', '▴');
                }
            });
        });

        // Add smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Add loading animation for menu items
        document.querySelectorAll('.menu-btn, .dropdown-content a').forEach(item => {
            item.addEventListener('click', function(e) {
                if (this.tagName === 'A' || this.querySelector('a')) {
                    const originalText = this.textContent;
                    this.style.opacity = '0.7';
                    setTimeout(() => {
                        this.style.opacity = '1';
                    }, 300);
                }
            });
        });

        // Simulate real-time data updates (jika statistik aktif)
        function updateStats() {
            const numbers = document.querySelectorAll('.number');
            numbers.forEach(num => {
                const currentValue = parseInt(num.textContent);
                const change = Math.floor(Math.random() * 3) - 1;
                if (currentValue + change > 0) {
                    num.textContent = currentValue + change;
                    if (change > 0) {
                        num.style.color = '#00b894';
                        setTimeout(() => num.style.color = '#0984e3', 1000);
                    }
                }
            });
        }

        // Update stats every 30 seconds (jika statistik aktif)
        // setInterval(updateStats, 30000);

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                hamburger.classList.remove('active');
                overlay.classList.remove('active');
            }
        });

        // Prevent body scroll when sidebar is open on mobile
        const body = document.body;
        const observer = new MutationObserver(() => {
            if (sidebar.classList.contains('active') && window.innerWidth <= 768) {
                body.style.overflow = 'hidden';
            } else {
                body.style.overflow = '';
            }
        });

        observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
    </script>
</body>
</html>