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
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow: hidden;
        }

        .container {
            display: flex;
            width: 100%;
            backdrop-filter: blur(10px);
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 250px;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Scrollbar Styling */
        .dashboard-content::-webkit-scrollbar {
            width: 6px;
        }

        .dashboard-content::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .dashboard-content::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            border-radius: 10px;
        }

        .dashboard-content::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #0984e3, #74b9ff);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
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
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <script>
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
                    // Add loading state
                    const originalText = this.textContent;
                    this.style.opacity = '0.7';
                    setTimeout(() => {
                        this.style.opacity = '1';
                    }, 300);
                }
            });
        });

        // Simulate real-time data updates
        function updateStats() {
            const numbers = document.querySelectorAll('.number');
            numbers.forEach(num => {
                const currentValue = parseInt(num.textContent);
                const change = Math.floor(Math.random() * 3) - 1; // -1, 0, or 1
                if (currentValue + change > 0) {
                    num.textContent = currentValue + change;
                    if (change > 0) {
                        num.style.color = '#00b894';
                        setTimeout(() => num.style.color = '#0984e3', 1000);
                    }
                }
            });
        }

        // Update stats every 30 seconds
        setInterval(updateStats, 30000);
    </script>
</body>
</html>