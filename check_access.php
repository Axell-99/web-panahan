<?php
// check_access.php
// File ini digunakan untuk mengecek akses user berdasarkan role

// Tidak perlu session_start() karena sudah ada di panggil.php

// Fungsi untuk cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['login']) && $_SESSION['login'] === true;
}

// Fungsi untuk cek apakah user adalah admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Fungsi untuk redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

// Fungsi untuk redirect jika bukan admin
function requireAdmin() {
    requireLogin(); // Pastikan sudah login dulu
    
    if (!isAdmin()) {
        // Redirect ke halaman yang diizinkan untuk non-admin
        header('Location: kegiatan.view.php');
        exit;
    }
}

// Fungsi untuk cek akses halaman
function checkPageAccess($currentPage) {
    requireLogin(); // Pastikan user sudah login
    
    // Daftar halaman yang bisa diakses semua user
    $allowedForAll = [
        'kegiatan.view.php',
        'logout.php',
        'profile.php' // jika ada halaman profile
    ];
    
    // Jika admin, bisa akses semua halaman
    if (isAdmin()) {
        return true;
    }
    
    // Jika bukan admin, cek apakah halaman diizinkan
    if (!in_array($currentPage, $allowedForAll)) {
        header('Location: kegiatan.view.php');
        exit;
    }
    
    return true;
}
?>