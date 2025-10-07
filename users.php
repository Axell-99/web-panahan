<?php
include 'panggil.php';
include 'check_access.php';
requireAdmin();

$result = $conn->query("SELECT * FROM users ORDER BY id ASC");

if($_SESSION['role']  != 'admin') {
    header('Location: kegiatan.view.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Users - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin: 2rem auto;
            padding: 2rem;
            max-width: 1200px;
        }
        
        .page-header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s infinite;
        }
        
        @keyframes shimmer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .page-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 2rem;
            position: relative;
            z-index: 1;
        }
        
        .action-bar {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 1px solid #e9ecef;
        }
        
        .btn-add {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
            color: white;
        }
        
        .search-form {
            background: white;
            border-radius: 25px;
            padding: 0.25rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
        }
        
        .search-form .form-control {
            border: none;
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            background: transparent;
        }
        
        .search-form .form-control:focus {
            box-shadow: none;
            border: none;
        }
        
        .btn-search {
            background: linear-gradient(45deg, #007bff, #0056b3);
            border: none;
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-search:hover {
            transform: scale(1.05);
            color: white;
        }
        
        .data-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
        }
        
        .data-table thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .data-table th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.875rem;
        }
        
        .data-table td {
            border: none;
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .data-table tbody tr {
            transition: all 0.3s ease;
        }
        
        .data-table tbody tr:hover {
            background: linear-gradient(90deg, #f8f9fa 0%, #e9ecef 100%);
            transform: scale(1.01);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .row-number {
            background: linear-gradient(45deg, #ff6b6b, #ee5a6f);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.875rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-edit {
            background: linear-gradient(45deg, #ffc107, #ff8f00);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-edit:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
        }
        
        .btn-delete {
            background: linear-gradient(45deg, #dc3545, #c82333);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-delete:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        }
        
        .back-button {
            background: linear-gradient(45deg, #6c757d, #5a6268);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .back-button:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="main-container">
        
        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fas fa-users me-2"></i>User Management System</h2>
            <p class="mb-0">Manage and organize user data efficiently</p>
        </div>

        <!-- Stats Card -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?= $result->num_rows; ?></div>
                    <div>Total Users</div>
                </div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <a href="tambah-user.php" class="btn-add">
                        <i class="fas fa-plus me-2"></i>Add New User
                    </a>
                </div>
                <div class="col-md-6">
                    <form class="search-form d-flex" method="get">
                        <input class="form-control me-2" type="search" name="q" placeholder="Search users...">
                        <button class="btn-search" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table data-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">
                            <i class="fas fa-hashtag me-1"></i>No
                        </th>
                        <th>
                            <i class="fas fa-user me-1"></i>Full Name
                        </th>
                        <th>
                            <i class="fas fa-envelope me-1"></i>Email Address
                        </th>
                        <th>
                            <i class="fas fa-envelope me-1"></i>Role
                        </th>
                        <th>
                            <i class="fas fa-envelope me-1"></i>Status
                        </th>
                        <th style="width: 200px;">
                            <i class="fas fa-cogs me-1"></i>Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($row = $result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td>
                            <div class="row-number"><?= $no++; ?></div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3" style="width: 40px; height: 40px; background: linear-gradient(45deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                                    <?= strtoupper(substr($row['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($row['name']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted"><?= htmlspecialchars($row['email']); ?></span>
                        </td>
                        <td>
                            <span class="text-muted"><?= htmlspecialchars($row['role']); ?></span>
                        </td>
                        <td>
                            <span class="text-muted"><?= htmlspecialchars($row['status']); ?></span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit-user.php?id=<?= $row['id']; ?>" class="btn-edit">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                <a href="hapus-user.php?id=<?= $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user?')">
                                    <i class="fas fa-trash me-1"></i>Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-users"></i>
            <h4>No Users Found</h4>
            <p>Start by adding your first user to the system.</p>
            <a href="tambah-user.php" class="btn-add">
                <i class="fas fa-plus me-2"></i>Add First User
            </a>
        </div>
        <?php endif; ?>

        <!-- Back Button -->
        <a href="dashboard.php" class="back-button">
            <i class="fas fa-arrow-left"></i>Back to Dashboard
        </a>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add smooth animations
    document.addEventListener('DOMContentLoaded', function() {
        // Animate table rows on load
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(20px)';
            setTimeout(() => {
                row.style.transition = 'all 0.5s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Enhanced delete confirmation
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const userName = this.closest('tr').querySelector('td:nth-child(2) .fw-bold').textContent;
                if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
                    window.location.href = this.href;
                }
            });
        });
    });
</script>
</body>
</html>