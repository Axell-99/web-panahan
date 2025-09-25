-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 25, 2025 at 03:55 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `panahan_turnament_new`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `min_age` int NOT NULL,
  `max_age` int NOT NULL,
  `gender` enum('Laki-laki','Perempuan','Campuran') COLLATE utf8mb4_general_ci DEFAULT 'Campuran',
  `max_participants` int DEFAULT '32',
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `min_age`, `max_age`, `gender`, `max_participants`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Official', 0, 1000, 'Campuran', 16, 'active', '2025-08-16 04:37:59', '2025-09-08 06:06:21'),
(2, 'Shortbow NON Pelajar Putra jarak 20m', 0, 1000, 'Laki-laki', 16, 'active', '2025-08-16 04:37:59', '2025-08-27 05:28:05'),
(3, 'Shortbow NON Pelajar Putri jarak 20m', 0, 1000, 'Perempuan', 16, 'active', '2025-08-16 04:37:59', '2025-08-27 05:29:50'),
(4, 'Shortbow Pelajar Putra SD 1-3 jarak 5m', 6, 9, 'Laki-laki', 16, 'active', '2025-08-16 04:37:59', '2025-08-27 05:30:02'),
(5, 'Shortbow Pelajar Putra SD 4-6 jarak 7m', 10, 12, 'Laki-laki', 32, 'active', '2025-08-16 04:37:59', '2025-08-18 06:03:09'),
(6, 'Shortbow Pelajar Putra SMA jarak 15m', 15, 18, 'Laki-laki', 32, 'active', '2025-08-16 04:37:59', '2025-08-27 05:30:12'),
(7, 'Shortbow Pelajar Putri SMA jarak 15m', 15, 18, 'Perempuan', 32, 'active', '2025-08-16 04:37:59', '2025-08-27 05:30:21'),
(8, 'Shortbow Pelajar Putra SMP jarak 10m', 12, 15, 'Laki-laki', 32, 'active', '2025-08-16 04:37:59', '2025-08-27 05:30:33'),
(10, 'Shortbow Pelajar Putri SD 1-3 jarak 5m', 6, 9, 'Perempuan', 16, 'active', '2025-08-16 04:48:56', '2025-08-27 05:30:52'),
(11, 'Shortbow Pelajar Putri SD 4-6 jarak 7m', 10, 12, 'Perempuan', 16, 'active', '2025-08-16 04:48:56', '2025-08-18 06:07:43'),
(12, 'Shortbow Pelajar Putri SMA jarak 10m', 15, 18, 'Perempuan', 16, 'active', '2025-08-16 04:48:56', '2025-08-27 05:31:04'),
(13, 'Shortbow Pemula Putra jarak 3m', 4, 6, 'Laki-laki', 16, 'active', '2025-08-16 04:48:56', '2025-08-27 05:31:27'),
(14, 'Shortbow Pemula Putri jarak 3m', 4, 6, 'Perempuan', 32, 'active', '2025-08-16 04:48:56', '2025-08-27 05:31:37'),
(35, 'testt', 1, 11, 'Laki-laki', 32, 'active', '2025-08-26 23:00:03', '2025-08-26 23:00:03'),
(36, 'ppp', 1, 11, 'Campuran', 32, 'active', '2025-08-26 23:05:20', '2025-08-26 23:05:20'),
(37, 'r', 1, 11, 'Campuran', 32, 'active', '2025-08-27 05:29:16', '2025-08-27 05:29:16'),
(38, 'Shortbow Pelajar Putri SMP jarak 10m', 12, 15, 'Campuran', 32, 'active', '2025-08-28 08:31:05', '2025-08-28 08:31:05');

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` int NOT NULL,
  `nama_kegiatan` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kegiatan`
--

INSERT INTO `kegiatan` (`id`, `nama_kegiatan`) VALUES
(1, 'apasajala'),
(6, 'testing'),
(7, 'pp'),
(9, 'testi'),
(11, 'Panahan 2025'),
(12, 'Latihan Bersama Internal ');

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan_kategori`
--

CREATE TABLE `kegiatan_kategori` (
  `id` int NOT NULL,
  `kegiatan_id` int NOT NULL,
  `category_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kegiatan_kategori`
--

INSERT INTO `kegiatan_kategori` (`id`, `kegiatan_id`, `category_id`) VALUES
(21, 6, 2),
(22, 6, 3),
(31, 7, 1),
(32, 7, 2),
(33, 7, 3),
(40, 1, 1),
(41, 1, 2),
(42, 1, 3),
(43, 1, 35),
(44, 1, 36),
(63, 9, 1),
(64, 9, 2),
(65, 9, 3),
(66, 9, 4),
(67, 9, 10),
(68, 9, 8),
(69, 9, 38),
(91, 11, 1),
(92, 11, 2),
(93, 11, 3),
(94, 11, 13),
(95, 11, 14),
(96, 11, 4),
(97, 11, 10),
(98, 11, 5),
(99, 11, 11),
(100, 11, 8),
(101, 11, 38),
(102, 11, 6),
(103, 11, 7),
(104, 11, 12),
(107, 12, 2),
(108, 12, 3);

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int NOT NULL,
  `tournament_id` int NOT NULL,
  `category_id` int NOT NULL,
  `round_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `match_number` int NOT NULL,
  `player1_id` int DEFAULT NULL,
  `player2_id` int DEFAULT NULL,
  `winner_id` int DEFAULT NULL,
  `scheduled_time` datetime DEFAULT NULL,
  `actual_start_time` datetime DEFAULT NULL,
  `actual_end_time` datetime DEFAULT NULL,
  `court_venue` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled','bye') COLLATE utf8mb4_general_ci DEFAULT 'scheduled',
  `notes` text COLLATE utf8mb4_general_ci,
  `referee_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `tournament_id`, `category_id`, `round_name`, `match_number`, `player1_id`, `player2_id`, `winner_id`, `scheduled_time`, `actual_start_time`, `actual_end_time`, `court_venue`, `status`, `notes`, `referee_name`, `created_at`, `updated_at`) VALUES
(1, 1, 10, 'Round 1', 1, 2, NULL, NULL, '2025-09-01 09:30:00', NULL, NULL, 'Court 2', 'scheduled', NULL, 'Wasit B', '2025-08-16 04:53:16', '2025-08-16 04:53:16'),
(2, 1, 11, 'Round 1', 1, 3, NULL, NULL, '2025-09-01 10:00:00', NULL, NULL, 'Court 1', 'scheduled', NULL, 'Wasit A', '2025-08-16 04:53:16', '2025-08-16 04:53:16'),
(3, 1, 12, 'Round 1', 1, 4, NULL, NULL, '2025-09-01 10:30:00', NULL, NULL, 'Court 2', 'scheduled', NULL, 'Wasit B', '2025-08-16 04:53:16', '2025-08-16 04:53:16'),
(4, 1, 13, 'Round 1', 1, 5, NULL, NULL, '2025-09-01 11:00:00', NULL, NULL, 'Court 1', 'scheduled', NULL, 'Wasit C', '2025-08-16 04:53:16', '2025-08-16 04:53:16'),
(5, 1, 14, 'Round 1', 1, NULL, NULL, NULL, '2025-09-01 11:30:00', NULL, NULL, 'Court 2', 'scheduled', NULL, 'Wasit C', '2025-08-16 04:53:16', '2025-08-16 04:53:16');

-- --------------------------------------------------------

--
-- Table structure for table `match_results`
--

CREATE TABLE `match_results` (
  `id` int NOT NULL,
  `match_id` int NOT NULL,
  `set_number` int NOT NULL,
  `player1_score` int DEFAULT '0',
  `player2_score` int DEFAULT '0',
  `duration_minutes` int DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `birthdate` date NOT NULL,
  `gender` enum('Laki-laki','Perempuan') COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `emergency_contact` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergency_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `photo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('active','inactive','banned') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `participants`
--

INSERT INTO `participants` (`id`, `name`, `birthdate`, `gender`, `email`, `phone`, `address`, `emergency_contact`, `emergency_phone`, `photo`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Ahmad Fauzi', '2010-05-15', 'Laki-laki', 'ahmad.fauzi@email.com', '08123456789', NULL, NULL, NULL, NULL, 'active', '2025-08-16 04:53:15', '2025-08-16 04:53:15'),
(2, 'Siti Nurhaliza', '2011-08-20', 'Perempuan', 'siti.nurhaliza@email.com', '08234567890', NULL, NULL, NULL, NULL, 'active', '2025-08-16 04:53:15', '2025-08-16 04:53:15'),
(3, 'Budi Santoso', '2008-03-10', 'Laki-laki', 'budi.santoso@email.com', '08345678901', NULL, NULL, NULL, NULL, 'active', '2025-08-16 04:53:15', '2025-08-16 04:53:15'),
(4, 'Dewi Sartika', '2009-12-05', 'Perempuan', 'dewi.sartika@email.com', '08456789012', NULL, NULL, NULL, NULL, 'active', '2025-08-16 04:53:15', '2025-08-16 04:53:15'),
(5, 'Rafi Ahmad', '2006-07-18', 'Laki-laki', 'rafi.ahmad@email.com', '08567890123', NULL, NULL, NULL, NULL, 'active', '2025-08-16 04:53:15', '2025-08-16 04:53:15'),
(6, 'Maya Putri', '2007-11-25', 'Perempuan', 'maya.putri@email.com', '08678901234', NULL, NULL, NULL, NULL, 'active', '2025-08-16 04:53:15', '2025-08-16 04:53:15'),
(7, 'Doni Pratama', '1995-04-12', 'Laki-laki', 'doni.pratama@email.com', '08789012345', NULL, NULL, NULL, NULL, 'active', '2025-08-16 04:53:15', '2025-08-16 04:53:15'),
(9, 'test', '2025-08-08', 'Laki-laki', 'test@test.com', '0812345', '-', '-', '-', NULL, 'active', '2025-08-18 05:38:10', '2025-08-18 05:38:10');

-- --------------------------------------------------------

--
-- Table structure for table `peserta`
--

CREATE TABLE `peserta` (
  `id` int NOT NULL,
  `nama_peserta` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') COLLATE utf8mb4_general_ci NOT NULL,
  `asal_kota` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_club` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sekolah` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kelas` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nomor_hp` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bukti_pembayaran` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `kegiatan_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peserta`
--

INSERT INTO `peserta` (`id`, `nama_peserta`, `tanggal_lahir`, `jenis_kelamin`, `asal_kota`, `nama_club`, `sekolah`, `kelas`, `nomor_hp`, `bukti_pembayaran`, `category_id`, `kegiatan_id`) VALUES
(1, 'test', '2025-08-01', 'Laki-laki', 'Balikpapan', 'Pova Archery', 'STM', 'XII Alat Berat', '0821128311', NULL, NULL, NULL),
(2, 'test', '2025-08-01', 'Laki-laki', 'Balikpapan', 'Pova Archery', 'STM', 'XII Alat Berat', '0821128311', NULL, 1, NULL),
(3, 'testt', '2025-07-31', 'Perempuan', 'Balikpapan', 'Pova Archery', 'STM', 'XII Alat Berat', '0821128311', NULL, 2, 1),
(4, 'ziya', '2016-01-28', 'Laki-laki', 'Samarinda', 'Airlangga Club', 'Airlangga', '3 sd', '0812345161', NULL, 1, 9),
(5, 'yaya', '2017-01-01', 'Laki-laki', 'Samarinda', 'Airlangga Club', 'Airlangga', '3 sd', '08123456789', NULL, 2, 9),
(6, 'qiyam', '2017-06-06', 'Laki-laki', 'Samarinda', 'Airlangga Club', 'Airlangga', '3 sd', '0812334411', NULL, 4, 9),
(7, 'lia', '2000-01-05', 'Perempuan', 'Samarinda', 'Airlangga Club', 'Airlangga', '12', '08771219211', NULL, 3, 9),
(8, 'wijaya', '2011-01-04', 'Laki-laki', 'Samarinda', 'Airlangga Club', 'Airlangga', '13', '08991273161', NULL, 8, 9),
(9, 'jihan', '2013-02-05', 'Perempuan', 'Samarinda', 'Airlangga Club', 'Airlangga', '9', '0874445778686', NULL, 38, 9),
(10, 'syla', '2008-06-10', 'Perempuan', 'Surabaya', 'Airlangga Club', 'Airlangga', '10', '08197621831', NULL, 3, 1),
(11, 'oke 123', '2022-02-01', 'Laki-laki', 'Surabaya', 'Pova Archery', 'Airlangga', 'TK', '0888881921', '20250901064008_68b53fc8e1497.jpeg', 1, 9),
(12, 'ian', '2025-09-01', 'Laki-laki', 'Jakarta', 'Jakarta Archery', 'SMKN 1 Jakarta', '12', '0817213616', '20250902025239_68b65bf773996.png', 1, 9),
(13, 'Untung', '2008-09-15', 'Laki-laki', 'Jepang', 'Untung Jago', 'SMK 7', 'XII', '0812578391', '20250902055113_68b685d102647.jpg', 2, 9),
(14, 'Elsa', '2008-01-02', 'Perempuan', 'Purwakarta', 'Panther Archery', 'SMK 11', '12', '0816213719', '20250902071355_68b69933a6e26.png', 3, 11),
(15, 'abu zikri', '2005-06-07', 'Laki-laki', 'Kukar', 'Arrimayah Nurul Islam Horseback Archery', '-', '-', '08152812168', '20250903083406_68b7fd7e4f22f.jpeg', 2, 11),
(16, 'Priyo', '2003-01-03', 'Laki-laki', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '08618613113', '20250903083731_68b7fe4b440b9.png', 2, 11),
(17, 'siko', '2005-06-12', 'Laki-laki', 'Samarinda', 'independent', '-', '-', '0865464563635', '20250903083937_68b7fec9efa12.png', 2, 11),
(18, 'Achmed FS', '2003-03-20', 'Laki-laki', 'Samarinda', 'Dhiya Rabbany Archery Mu\'Minin Kalimantan', '-', '-', '08231331344', '20250903084107_68b7ff23c1bc0.png', 2, 11),
(19, 'Muhammad Husin', '2004-04-14', 'Laki-laki', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '08366252637', '20250903084247_68b7ff875bae0.png', 2, 11),
(20, 'Andhi Trisnaputra', '2004-12-27', 'Laki-laki', 'Samarinda', 'NFH ARCHERY', '-', '-', '08168137181', '20250903084421_68b7ffe52a1e2.png', 2, 11),
(21, 'Pandhu Dhitya AA', '2003-12-28', 'Laki-laki', 'Samarinda', 'Fakarchery', '-', '-', '0816813618', '20250903084712_68b800908a6c1.png', 2, 11),
(22, 'Didin Wahyudin', '2006-01-30', 'Laki-laki', 'Samarinda', 'Didin Wahyudin', '-', '-', '08121318113', '20250903084819_68b800d3c6ffa.png', 2, 11),
(23, 'Selviansyah', '2003-12-28', 'Laki-laki', 'Samarinda', 'Perdana kaltim', '-', '-', '08816311391', '20250903085020_68b8014c33491.png', 2, 11),
(24, 'Muhammad Arkhan Khalfani', '2020-01-04', 'Laki-laki', 'Samarinda', 'AL AZHAR ARCHERY', '-', '-', '08121836113', '20250904010824_68b8e6882dc66.png', 13, 11),
(25, 'Ukkasyah', '2020-01-04', 'Laki-laki', 'Samarinda', 'FAKARCHERY', '-', '-', '081213781813', '20250904010929_68b8e6c9079c0.png', 13, 11),
(26, 'Muhammad Taqiyuddin Abdillah El-Haq', '2020-01-28', 'Laki-laki', 'Bontang', 'Al Izzah Archery', '-', '-', '08162183611', '20250904011042_68b8e71289bc5.png', 13, 11),
(27, 'Rifqi Muhammad Hamasi', '2020-03-05', 'Laki-laki', 'Samarinda', 'FAKARCHERY', '-', '-', '08713161134', '20250904011147_68b8e753124a6.png', 13, 11),
(28, 'Habibi Muhammad Al Fatih', '2020-03-04', 'Laki-laki', 'Samarinda', 'Cakrawala Kaki Langit Archery', '-', '-', '081868813741', '20250904011403_68b8e7db458ab.png', 13, 11),
(29, 'Muhammad Abdullah', '2020-07-08', 'Laki-laki', 'Kutai Kartanegara', 'Ibadurrahaman Archery Club', '-', '-', '0818319713', '20250904011453_68b8e80de6c45.png', 13, 11),
(30, 'Zahid Hamizan Rabbani', '2020-01-29', 'Laki-laki', 'Samarinda', 'Cakrawala Kaki Langit Archery', '-', '-', '081368163817', '20250904011536_68b8e838e770a.png', 13, 11),
(31, 'Arshaka Ayman Zaid Saswanto', '2020-03-05', 'Laki-laki', 'Samarinda', 'Rumah Belajar Archery Club', '-', '-', '081868171391', '20250904011614_68b8e85e822f1.png', 13, 11),
(32, 'Muammar Bilal Ibrahim', '2020-02-03', 'Laki-laki', 'Samarinda', 'FAKARCHERY', '-', '-', '08813816381', '20250904011827_68b8e8e343202.png', 13, 11),
(33, 'Muhammad fahri', '2020-02-04', 'Laki-laki', 'Samarinda', 'Tunas harapan archery club', '-', '-', '0871638171', '20250904011913_68b8e911d6c4e.png', 13, 11),
(34, 'Uwais Hanif Ibrahim', '2020-07-08', 'Laki-laki', 'Samarinda', 'FAKARCHERY', '-', '-', '08613617131', '20250904012000_68b8e94006cbb.png', 13, 11),
(35, 'Muhammad Sayyid Musyaffa', '2020-07-08', 'Laki-laki', 'Samarinda', 'Tunas harapan archery club', '-', '-', '0981831871', '20250904012246_68b8e9e69b0b5.png', 13, 11),
(36, 'Khalif Abdurrahman', '2020-01-29', 'Laki-laki', 'Samarinda', 'FAKARCHERY', '-', '-', '08813618731', '20250904012426_68b8ea4ab7a72.png', 13, 11),
(37, 'Muhammad Rayandra Putra Pratama', '2019-02-12', 'Laki-laki', 'Samarinda', 'Tunas harapan archery club', '-', '-', '0817318631', '20250904012639_68b8eacf6011d.png', 13, 11),
(38, 'MUHAMMAD AIDIL SAPUTRARANI', '2020-07-08', 'Laki-laki', 'Samarinda', 'NFH ARCHERY', '-', '-', '0817368813', '20250904012724_68b8eafcb380d.png', 13, 11),
(39, 'Annisa Shafa Hendriawanti', '2020-02-04', 'Perempuan', 'Balikpapan', 'Akademi Horsebow Sejati', '-', '-', '08137197391', '20250904012943_68b8eb872bff1.png', 14, 11),
(40, 'MAUDY AULIA AZZAHRA', '2020-01-28', 'Perempuan', 'Samarinda', 'FAKARCHERY', '-', '-', '08183183191', '20250904013033_68b8ebb946547.png', 14, 11),
(41, 'Dzakiyyah shofiy', '2020-10-13', 'Perempuan', 'Balikpapan', 'Akademi Horsebow Sejati', '-', '-', '081381711314', '20250904013129_68b8ebf1db128.png', 14, 11),
(42, 'Farzana Qiana', '2019-02-05', 'Perempuan', 'Samarinda', 'FAKARCHERY', '-', '-', '08183619713', '20250904013335_68b8ec6fbf904.png', 14, 11),
(43, 'Halimatussa\'diah', '2020-06-09', 'Perempuan', 'Samarinda', 'AL AZHAR ARCHERY', '-', '-', '08183619731', '20250904013436_68b8ecac4228f.png', 14, 11),
(44, 'Bilqis Azizah Azzahra', '2019-01-29', 'Perempuan', 'Samarinda', 'FAKARCHERY', '-', '-', '08131971937', '20250904014646_68b8ef86db990.jpeg', 14, 11),
(45, 'Hafizah Adiibah', '2018-01-02', 'Perempuan', 'Samarinda', 'Al Azhar Archery', '-', '-', '089970876537', '20250904015203_68b8f0c39c12e.jpeg', 14, 11),
(46, 'YUINA SHARAWY SAPUTRARANI', '2019-06-29', 'Perempuan', 'Samarinda', 'NFH ARCHERY', '-', '-', '083877895232', '20250904015528_68b8f190cbe06.jpeg', 14, 11),
(47, 'Rayta Havva Huri Hayyuna', '2020-06-24', 'Perempuan', 'Samarinda', 'Cakrawala Kaki Langit Archery', '-', '-', '083877563241', '20250904015822_68b8f23ea34a0.jpeg', 14, 11),
(48, 'Delisha Almahyra', '2019-08-14', 'Perempuan', 'Penajam', 'Prabu Archery', '-', '-', '083812512188', '20250904020038_68b8f2c69a370.jpeg', 14, 11),
(49, 'Maryam Farzana Arrahman', '2020-10-13', 'Perempuan', 'Samarinda', 'Cakrawala Kaki Langit Archery', '-', '-', '0813719713', '20250904022357_68b8f83d8f9ee.png', 14, 11),
(50, 'Daisha Arsyila', '2019-03-06', 'Perempuan', 'Penajam', 'Prabu Archery', '-', '-', '08135173141', '20250904025007_68b8fe5fbac91.png', 14, 11),
(51, 'Alifa Putri Zhafirah Dirgahayu', '2019-11-20', 'Perempuan', 'Samarinda', 'Cakrawala Kaki Langit Archery', '-', '-', '081638168179', '20250904025058_68b8fe9244644.png', 14, 11),
(52, 'Khairunnisa nur Aprilia', '2020-02-04', 'Perempuan', 'Samarinda', 'Rantau Archery', '-', '-', '08193719317', '20250904025213_68b8fedde0167.png', 14, 11),
(53, 'Raline Mecca El Jasmine', '2020-01-28', 'Perempuan', 'Samarinda', 'AL AZHAR ARCHERY', '-', '-', '08761639179', '20250904025316_68b8ff1c6d978.png', 14, 11),
(54, 'Muhammad Al Abbasy Langit Firdaus', '2016-03-17', 'Laki-laki', 'Balikpapan', 'Akademi Horsebow Sejati', '-', '-', '0884616139', '20250904025910_68b9007eb6225.png', 4, 11),
(55, 'Musa', '2016-06-07', 'Laki-laki', 'Muara Barak', 'Al-Muhajirin Archery Club', '-', '-', '0871063193', '20250904030032_68b900d07e678.png', 4, 11),
(56, 'Faidhan Audisepta djatmiko', '2016-03-09', 'Laki-laki', 'Samarinda', 'Khidir Archery Club', '-', '-', '08916819194', '20250904030119_68b900ffe5b4d.png', 4, 11),
(57, 'Muhammad Daffa Ibnu Hafidz', '2016-06-08', 'Laki-laki', 'Balikpapan', 'Akademi Horsebow Sejati', '-', '-', '08715236186', '20250904030234_68b9014a721b7.png', 4, 11),
(58, 'Muhammad Atid Abidzar', '2016-03-09', 'Laki-laki', 'Muara Barak', 'Al-Muhajirin Archery Club', '-', '-', '0864183618', '20250904030743_68b9027f0eb98.png', 4, 11),
(59, 'Masdavi Fairel Aldebaran', '2016-02-09', 'Laki-laki', 'Samarinda', 'MIN 2 Samarinda', '-', '-', '081636139', '20250904031033_68b903292345e.png', 4, 11),
(60, 'ADLAN BILAL HABIBIE', '2016-02-09', 'Laki-laki', 'Samarinda', 'Al - Azhar Archery', '-', '-', '0816248168', '20250904031135_68b90367531b7.png', 4, 11),
(61, 'ADLAN BILAL HABIBIE', '2018-02-20', 'Laki-laki', 'Samarinda', 'Al - Azhar Archery', '-', '-', '089912817111', '20250904032504_68b90690849ee.png', 4, 11),
(62, 'Muhammad Izat Nabhan', '2016-03-09', 'Laki-laki', 'Muara Badak', 'Al-Muhajirin Archery Club', '-', '-', '0877121113111', '20250904032623_68b906df9ed06.png', 4, 11),
(63, 'Mohammad Hasbillah', '2016-10-27', 'Laki-laki', 'Balikpapan', 'Minu Archery', '-', '-', '083613671361', '20250904032717_68b907155cb4b.png', 4, 11),
(64, 'Arsakha Aurellio Shakeel', '2016-12-15', 'Laki-laki', 'Samarinda', 'AL AZHAR ARCHERY', '-', '-', '083165361521', '20250904032929_68b907996f07c.png', 4, 11),
(65, 'Atharizz Calief Abdillah', '2016-04-07', 'Laki-laki', 'Samarinda', 'Ar Rajwa Archery Club', '-', '-', '08413131313', '20250904033026_68b907d2f0f24.jpeg', 4, 11),
(66, 'Sayyid Yusuf Halim', '2016-12-08', 'Laki-laki', 'Balikpapan', 'Minu Archery', '-', '-', '0864743554678', '20250904033137_68b90819a62d1.jpeg', 4, 11),
(67, 'Nuzul Repandi', '2016-07-28', 'Laki-laki', 'Samarinda', 'AL AZHAR ARCHERY', '-', '-', '08267251762', '20250904033255_68b90867a5da4.jpeg', 4, 11),
(68, 'Fayyadh Hanifur Rahman', '2016-02-11', 'Laki-laki', 'Samarinda', 'Ar Rajwa Archery Club', '-', '-', '0867138171', '20250904064330_68b93512b0cfd.png', 4, 11),
(69, 'Edwin', '2008-06-08', 'Laki-laki', 'Samarinda', 'Airlangga Club', '-', '-', '08168163198', '20250908035928_68be54a08ed02.png', 2, 11),
(70, 'jijah', '2008-11-03', 'Perempuan', 'Samarinda', 'Pova Archery', 'SMK 11 Samarinda', 'XII TKJ', '08178614141', '20250908060803_68be72c3e960f.png', 1, 11),
(71, 'Pur', '2000-06-12', 'Laki-laki', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '0899193193', '20250925075446_68d4f546277c3.jpeg', 2, 12),
(72, 'Gusti', '2006-01-31', 'Laki-laki', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '0889623729', '20250925075523_68d4f56be8dc0.jpeg', 2, 12),
(73, 'Prio', '2023-02-07', 'Laki-laki', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '08123456789', '20250925075552_68d4f5888fcc7.jpeg', 2, 12),
(74, 'Syam', '2025-09-02', 'Laki-laki', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '0812334411', '20250925075620_68d4f5a4a0dee.jpeg', 2, 12),
(75, 'Mas Sakur', '2025-09-17', 'Laki-laki', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '0812334411', '20250925075657_68d4f5c953d38.jpeg', 2, 12),
(76, 'Aldi', '2025-09-10', 'Laki-laki', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '0812334411', '20250925075742_68d4f5f68e4ce.jpeg', 2, 12),
(77, 'Burhan', '2024-02-28', 'Laki-laki', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '0821128311', '20250925075817_68d4f61922eb2.jpeg', 2, 12),
(78, 'Rosyid', '2025-09-03', 'Laki-laki', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '0816391731', '20250925075857_68d4f64192a10.jpeg', 2, 12),
(79, 'Pak Tontro', '2025-02-05', 'Laki-laki', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '0812345161', '20250925075932_68d4f6641b6e3.jpeg', 2, 12),
(80, 'Pak Udin', '2025-01-07', 'Laki-laki', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '08771219211', '20250925080003_68d4f683a9c83.jpeg', 2, 12),
(81, 'Husin', '2023-01-31', 'Laki-laki', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '0812334411', '20250925080033_68d4f6a15675d.jpeg', 2, 12),
(82, 'Ngaisah', '2025-03-06', 'Perempuan', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '0821128311', '20250925080125_68d4f6d53f218.jpeg', 3, 12),
(83, 'Rina', '2024-06-04', 'Perempuan', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '0821128311', '20250925080202_68d4f6faa876c.jpeg', 3, 12),
(84, 'Rina', '2025-09-11', 'Perempuan', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '0899193193', '20250925080225_68d4f71127a11.jpeg', 3, 12),
(85, 'Fitri', '2025-02-04', 'Perempuan', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '0812334411', '20250925080307_68d4f73bddd55.jpeg', 3, 12),
(86, 'Mila', '2022-03-09', 'Perempuan', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '08771219211', '20250925080347_68d4f76324674.jpeg', 3, 12),
(87, 'Widya', '2025-09-03', 'Perempuan', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '0812345161', '20250925080419_68d4f783a6f27.jpeg', 3, 12),
(88, 'Wida', '2025-09-04', 'Perempuan', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '08123456789', '20250925080447_68d4f79f41067.jpeg', 3, 12),
(89, 'Zulfa', '2025-09-18', 'Perempuan', 'Samarinda', 'Gold Archery Samarinda', '-', '-', '08123456789', '20250925080518_68d4f7bee21d2.jpeg', 3, 12);

-- --------------------------------------------------------

--
-- Table structure for table `score`
--

CREATE TABLE `score` (
  `id` bigint NOT NULL,
  `category_id` int NOT NULL,
  `kegiatan_id` int NOT NULL,
  `peserta_id` int NOT NULL,
  `score` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `session` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `arrow` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `score_board_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `score`
--

INSERT INTO `score` (`id`, `category_id`, `kegiatan_id`, `peserta_id`, `score`, `session`, `arrow`, `score_board_id`) VALUES
(43, 2, 11, 15, '1', '1', '1', 8),
(44, 2, 11, 15, '2', '1', '2', 8),
(45, 2, 11, 15, '3', '1', '3', 8),
(46, 2, 11, 15, '10', '2', '1', 8),
(47, 2, 11, 15, 'x', '2', '2', 8),
(48, 2, 11, 15, '3', '2', '3', 8),
(49, 2, 11, 18, '10', '1', '1', 8),
(50, 2, 11, 18, '5', '1', '2', 8),
(51, 2, 11, 18, '10', '1', '3', 8),
(52, 2, 11, 18, 'x', '2', '1', 8),
(53, 2, 11, 18, 'x', '2', '2', 8),
(54, 2, 11, 18, 'x', '2', '3', 8),
(55, 2, 11, 20, 'x', '1', '1', 8),
(56, 2, 11, 20, 'x', '1', '2', 8),
(57, 2, 11, 20, 'x', '1', '3', 8),
(58, 2, 11, 20, 'x', '2', '1', 8),
(59, 2, 11, 20, 'x', '2', '2', 8),
(60, 2, 11, 20, '5', '2', '3', 8),
(61, 2, 11, 15, '', '3', '1', 8),
(62, 2, 12, 76, '1', '1', '1', 12),
(63, 2, 12, 76, '2', '1', '2', 12),
(64, 2, 12, 76, 'm', '1', '3', 12),
(65, 2, 12, 76, '6', '1', '4', 12),
(66, 2, 12, 76, '1', '1', '5', 12),
(67, 2, 12, 76, '3', '1', '6', 12),
(68, 2, 12, 76, '3', '2', '1', 12),
(69, 2, 12, 76, '3', '2', '2', 12),
(70, 2, 12, 76, '4', '2', '3', 12),
(71, 2, 12, 76, '3', '2', '4', 12),
(72, 2, 12, 76, '1', '2', '5', 12),
(73, 2, 12, 76, '1', '2', '6', 12),
(74, 2, 12, 76, '3', '3', '1', 12),
(75, 2, 12, 76, '3', '3', '2', 12),
(76, 2, 12, 76, '4', '3', '3', 12),
(77, 2, 12, 76, '5', '3', '4', 12),
(78, 2, 12, 76, '6', '3', '5', 12),
(79, 2, 12, 76, '5', '3', '6', 12),
(80, 2, 12, 76, '1', '4', '1', 12),
(81, 2, 12, 76, '1', '4', '2', 12),
(82, 2, 12, 76, '1', '4', '3', 12),
(83, 2, 12, 76, '2', '4', '4', 12),
(84, 2, 12, 76, '3', '4', '5', 12),
(85, 2, 12, 76, '4', '4', '6', 12),
(86, 2, 12, 76, '6', '5', '1', 12),
(87, 2, 12, 76, '6', '5', '2', 12),
(88, 2, 12, 76, '6', '5', '3', 12),
(89, 2, 12, 76, '6', '5', '4', 12),
(90, 2, 12, 76, '2', '5', '5', 12),
(91, 2, 12, 76, '1', '5', '6', 12),
(92, 2, 12, 76, '4', '6', '1', 12),
(93, 2, 12, 76, '5', '6', '2', 12),
(94, 2, 12, 76, '3', '6', '3', 12),
(95, 2, 12, 76, '2', '6', '4', 12),
(96, 2, 12, 76, '1', '6', '5', 12),
(97, 2, 12, 76, '1', '6', '6', 12),
(98, 2, 12, 76, '1', '7', '1', 12),
(99, 2, 12, 76, '5', '7', '2', 12),
(100, 2, 12, 76, '6', '7', '3', 12),
(101, 2, 12, 76, '1', '7', '4', 12),
(102, 2, 12, 76, '5', '7', '5', 12),
(103, 2, 12, 76, '6', '7', '6', 12),
(104, 2, 12, 77, '4', '1', '1', 12),
(105, 2, 12, 77, '2', '1', '2', 12),
(106, 2, 12, 77, '4', '1', '3', 12),
(107, 2, 12, 77, '3', '1', '4', 12),
(108, 2, 12, 77, '4', '1', '5', 12),
(109, 2, 12, 77, '4', '1', '6', 12),
(110, 2, 12, 77, '1', '2', '1', 12),
(111, 2, 12, 77, '4', '2', '2', 12),
(112, 2, 12, 77, '5', '2', '3', 12),
(113, 2, 12, 77, '4', '2', '4', 12),
(114, 2, 12, 77, '3', '2', '5', 12),
(115, 2, 12, 77, '4', '2', '6', 12),
(116, 2, 12, 77, '6', '3', '2', 12),
(117, 2, 12, 77, '5', '3', '1', 12),
(118, 2, 12, 77, '6', '3', '3', 12),
(119, 2, 12, 77, '5', '3', '4', 12),
(120, 2, 12, 77, '4', '3', '5', 12),
(121, 2, 12, 77, '3', '3', '6', 12),
(122, 2, 12, 77, '2', '4', '1', 12),
(123, 2, 12, 77, '3', '4', '2', 12),
(124, 2, 12, 77, '4', '4', '3', 12),
(125, 2, 12, 77, '4', '4', '4', 12),
(126, 2, 12, 77, '3', '4', '5', 12),
(127, 2, 12, 77, '2', '4', '6', 12),
(128, 2, 12, 77, '4', '5', '1', 12),
(129, 2, 12, 77, '3', '5', '2', 12),
(130, 2, 12, 77, '5', '5', '4', 12),
(131, 2, 12, 77, '4', '5', '3', 12),
(132, 2, 12, 77, '4', '5', '5', 12),
(133, 2, 12, 77, '3', '5', '6', 12),
(134, 2, 12, 77, '2', '6', '1', 12),
(135, 2, 12, 77, '4', '6', '2', 12),
(136, 2, 12, 77, '5', '6', '3', 12),
(137, 2, 12, 77, '6', '6', '4', 12),
(138, 2, 12, 77, '5', '6', '5', 12),
(139, 2, 12, 77, '4', '6', '6', 12),
(140, 2, 12, 77, '3', '7', '1', 12),
(141, 2, 12, 77, '4', '7', '2', 12),
(142, 2, 12, 77, '3', '7', '3', 12),
(143, 2, 12, 77, '4', '7', '4', 12),
(144, 2, 12, 77, '4', '7', '5', 12),
(145, 2, 12, 77, '3', '7', '6', 12),
(146, 2, 12, 72, '4', '1', '1', 12),
(147, 2, 12, 72, '4', '1', '2', 12),
(148, 2, 12, 72, '4', '1', '3', 12),
(149, 2, 12, 72, '4', '1', '4', 12),
(150, 2, 12, 72, '4', '1', '5', 12),
(151, 2, 12, 72, '4', '1', '6', 12),
(152, 2, 12, 72, '4', '2', '1', 12),
(153, 2, 12, 72, '4', '2', '2', 12),
(154, 2, 12, 72, '4', '2', '3', 12),
(155, 2, 12, 72, '4', '2', '4', 12),
(156, 2, 12, 72, '4', '2', '5', 12),
(157, 2, 12, 72, '4', '2', '6', 12),
(158, 2, 12, 72, '4', '3', '1', 12),
(159, 2, 12, 72, '4', '3', '2', 12),
(160, 2, 12, 72, '4', '3', '3', 12),
(161, 2, 12, 72, '4', '3', '4', 12),
(162, 2, 12, 72, '4', '3', '5', 12),
(163, 2, 12, 72, '4', '3', '6', 12),
(164, 2, 12, 72, '4', '4', '1', 12),
(165, 2, 12, 72, '4', '4', '2', 12),
(166, 2, 12, 72, '4', '4', '3', 12),
(167, 2, 12, 72, '3', '4', '4', 12),
(168, 2, 12, 72, '3', '4', '5', 12),
(169, 2, 12, 72, '3', '4', '6', 12),
(170, 2, 12, 72, '3', '5', '1', 12),
(171, 2, 12, 72, '3', '5', '2', 12),
(172, 2, 12, 72, '3', '5', '3', 12),
(173, 2, 12, 72, '3', '5', '4', 12),
(174, 2, 12, 72, '3', '5', '5', 12),
(175, 2, 12, 72, '3', '5', '6', 12),
(176, 2, 12, 72, '2', '6', '1', 12),
(177, 2, 12, 72, '2', '6', '2', 12),
(178, 2, 12, 72, '2', '6', '3', 12),
(179, 2, 12, 72, '2', '6', '4', 12),
(180, 2, 12, 72, '2', '6', '5', 12),
(181, 2, 12, 72, '2', '6', '6', 12),
(182, 2, 12, 72, '2', '7', '1', 12),
(183, 2, 12, 72, '1', '7', '2', 12),
(184, 2, 12, 72, '1', '7', '3', 12),
(185, 2, 12, 72, '1', '7', '4', 12),
(186, 2, 12, 72, '1', '7', '5', 12),
(187, 2, 12, 72, '1', '7', '6', 12),
(188, 2, 12, 81, '3', '1', '1', 12),
(189, 2, 12, 81, '3', '1', '2', 12),
(190, 2, 12, 81, '3', '1', '3', 12),
(191, 2, 12, 81, '2', '1', '4', 12),
(192, 2, 12, 81, '5', '1', '5', 12),
(193, 2, 12, 81, '4', '1', '6', 12),
(194, 2, 12, 81, '3', '2', '1', 12),
(195, 2, 12, 81, '2', '2', '2', 12),
(196, 2, 12, 81, '4', '2', '4', 12),
(197, 2, 12, 81, '5', '2', '3', 12),
(198, 2, 12, 81, '3', '2', '6', 12),
(199, 2, 12, 81, '3', '2', '5', 12),
(200, 2, 12, 81, '3', '3', '1', 12),
(201, 2, 12, 81, '3', '3', '2', 12),
(202, 2, 12, 81, '3', '3', '3', 12),
(203, 2, 12, 81, '3', '3', '4', 12),
(204, 2, 12, 81, '3', '3', '5', 12),
(205, 2, 12, 81, '3', '3', '6', 12),
(206, 2, 12, 81, '3', '4', '1', 12),
(207, 2, 12, 81, '3', '4', '2', 12),
(208, 2, 12, 81, '3', '4', '3', 12),
(209, 2, 12, 81, '3', '4', '4', 12),
(210, 2, 12, 81, '3', '4', '5', 12),
(211, 2, 12, 81, '3', '4', '6', 12),
(212, 2, 12, 81, '2', '5', '1', 12),
(213, 2, 12, 81, '2', '5', '2', 12),
(214, 2, 12, 81, '2', '5', '3', 12),
(215, 2, 12, 81, '2', '5', '4', 12),
(216, 2, 12, 81, '2', '5', '5', 12),
(217, 2, 12, 81, '2', '5', '6', 12),
(218, 2, 12, 81, '2', '6', '1', 12),
(219, 2, 12, 81, '2', '6', '2', 12),
(220, 2, 12, 81, '2', '6', '3', 12),
(221, 2, 12, 81, '2', '6', '4', 12),
(222, 2, 12, 81, '2', '6', '5', 12),
(223, 2, 12, 81, '2', '6', '6', 12),
(224, 2, 12, 81, '2', '7', '1', 12),
(225, 2, 12, 81, '2', '7', '2', 12),
(226, 2, 12, 81, '2', '7', '3', 12),
(227, 2, 12, 81, '2', '7', '4', 12),
(228, 2, 12, 81, '2', '7', '5', 12),
(229, 2, 12, 81, '2', '7', '6', 12),
(230, 2, 12, 75, '6', '1', '1', 12),
(231, 2, 12, 75, '6', '1', '2', 12),
(232, 2, 12, 75, '6', '1', '3', 12),
(233, 2, 12, 75, '6', '1', '4', 12),
(234, 2, 12, 75, '6', '1', '5', 12),
(235, 2, 12, 75, '6', '1', '6', 12),
(236, 2, 12, 75, '6', '2', '1', 12),
(237, 2, 12, 75, '6', '2', '2', 12),
(238, 2, 12, 75, '6', '2', '3', 12),
(239, 2, 12, 75, '6', '2', '4', 12),
(240, 2, 12, 75, '6', '2', '5', 12),
(241, 2, 12, 75, '6', '2', '6', 12),
(242, 2, 12, 75, '6', '3', '1', 12),
(243, 2, 12, 75, '6', '3', '2', 12),
(244, 2, 12, 75, '6', '3', '3', 12),
(245, 2, 12, 75, '6', '3', '4', 12),
(246, 2, 12, 75, '6', '3', '5', 12),
(247, 2, 12, 75, '6', '3', '6', 12),
(248, 2, 12, 75, '6', '4', '1', 12),
(249, 2, 12, 75, '6', '4', '2', 12),
(250, 2, 12, 75, '6', '4', '3', 12),
(251, 2, 12, 75, '6', '4', '4', 12),
(252, 2, 12, 75, '6', '4', '5', 12),
(253, 2, 12, 75, '6', '4', '6', 12),
(254, 2, 12, 75, '6', '5', '1', 12),
(255, 2, 12, 75, '6', '5', '2', 12),
(256, 2, 12, 75, '6', '5', '3', 12),
(257, 2, 12, 75, '6', '5', '4', 12),
(258, 2, 12, 75, '6', '5', '5', 12),
(259, 2, 12, 75, '6', '5', '6', 12),
(260, 2, 12, 75, '6', '6', '1', 12),
(261, 2, 12, 75, '6', '6', '2', 12),
(262, 2, 12, 75, '6', '6', '3', 12),
(263, 2, 12, 75, '6', '6', '4', 12),
(264, 2, 12, 75, '6', '6', '5', 12),
(265, 2, 12, 75, '6', '6', '6', 12),
(266, 2, 12, 75, '6', '7', '1', 12),
(267, 2, 12, 75, '6', '7', '2', 12),
(268, 2, 12, 75, '6', '7', '3', 12),
(269, 2, 12, 75, '6', '7', '4', 12),
(270, 2, 12, 75, '6', '7', '5', 12),
(271, 2, 12, 75, '6', '7', '6', 12),
(272, 2, 12, 79, '5', '1', '1', 12),
(273, 2, 12, 79, '5', '1', '2', 12),
(274, 2, 12, 79, '5', '1', '3', 12),
(275, 2, 12, 79, '5', '1', '4', 12),
(276, 2, 12, 79, '5', '1', '5', 12),
(277, 2, 12, 79, '5', '1', '6', 12),
(278, 2, 12, 79, '5', '2', '1', 12),
(279, 2, 12, 79, '5', '2', '2', 12),
(280, 2, 12, 79, '5', '2', '3', 12),
(281, 2, 12, 79, '5', '2', '4', 12),
(282, 2, 12, 79, '5', '2', '5', 12),
(283, 2, 12, 79, '5', '2', '6', 12),
(284, 2, 12, 79, '5', '3', '1', 12),
(285, 2, 12, 79, '5', '3', '2', 12),
(286, 2, 12, 79, '5', '3', '3', 12),
(287, 2, 12, 79, '5', '3', '4', 12),
(288, 2, 12, 79, '5', '3', '5', 12),
(289, 2, 12, 79, '5', '3', '6', 12),
(290, 2, 12, 79, '5', '4', '1', 12),
(291, 2, 12, 79, '5', '4', '2', 12),
(292, 2, 12, 79, '5', '4', '3', 12),
(293, 2, 12, 79, '5', '4', '4', 12),
(294, 2, 12, 79, '5', '4', '5', 12),
(295, 2, 12, 79, '5', '4', '6', 12),
(296, 2, 12, 79, '5', '5', '1', 12),
(297, 2, 12, 79, '5', '5', '2', 12),
(298, 2, 12, 79, '5', '5', '3', 12),
(299, 2, 12, 79, '5', '5', '4', 12),
(300, 2, 12, 79, '5', '5', '5', 12),
(301, 2, 12, 79, '5', '5', '6', 12),
(302, 2, 12, 79, '5', '6', '1', 12),
(303, 2, 12, 79, '5', '6', '2', 12),
(304, 2, 12, 79, '5', '6', '3', 12),
(305, 2, 12, 79, '5', '6', '4', 12),
(306, 2, 12, 79, '5', '6', '5', 12),
(307, 2, 12, 79, '5', '6', '6', 12),
(308, 2, 12, 79, '5', '7', '1', 12),
(309, 2, 12, 79, '5', '7', '2', 12),
(310, 2, 12, 79, '5', '7', '3', 12),
(311, 2, 12, 79, '5', '7', '4', 12),
(312, 2, 12, 79, '5', '7', '5', 12),
(313, 2, 12, 79, '5', '7', '6', 12),
(314, 2, 12, 80, '4', '1', '1', 12),
(315, 2, 12, 80, '4', '1', '2', 12),
(316, 2, 12, 80, '4', '1', '3', 12),
(317, 2, 12, 80, '4', '1', '4', 12),
(318, 2, 12, 80, '5', '1', '5', 12),
(319, 2, 12, 80, '5', '1', '6', 12),
(320, 2, 12, 80, '5', '2', '1', 12),
(321, 2, 12, 80, '5', '2', '2', 12),
(322, 2, 12, 80, '4', '2', '3', 12),
(323, 2, 12, 80, '4', '2', '4', 12),
(324, 2, 12, 80, '4', '2', '5', 12),
(325, 2, 12, 80, '3', '2', '6', 12),
(326, 2, 12, 80, '3', '3', '1', 12),
(327, 2, 12, 80, '3', '3', '2', 12),
(328, 2, 12, 80, '3', '3', '3', 12),
(329, 2, 12, 80, '3', '3', '4', 12),
(330, 2, 12, 80, '5', '3', '5', 12),
(331, 2, 12, 80, '5', '3', '6', 12),
(332, 2, 12, 80, '5', '4', '1', 12),
(333, 2, 12, 80, '5', '4', '2', 12),
(334, 2, 12, 80, '5', '4', '3', 12),
(335, 2, 12, 80, '5', '4', '4', 12),
(336, 2, 12, 80, '4', '4', '5', 12),
(337, 2, 12, 80, '4', '4', '6', 12),
(338, 2, 12, 80, '4', '5', '1', 12),
(339, 2, 12, 80, '4', '5', '2', 12),
(340, 2, 12, 80, '6', '5', '3', 12),
(341, 2, 12, 80, '6', '5', '4', 12),
(342, 2, 12, 80, '6', '5', '5', 12),
(343, 2, 12, 80, '6', '5', '6', 12),
(344, 2, 12, 80, '6', '6', '1', 12),
(345, 2, 12, 80, '6', '6', '2', 12),
(346, 2, 12, 80, '6', '6', '3', 12),
(347, 2, 12, 80, '6', '6', '4', 12),
(348, 2, 12, 80, '6', '6', '5', 12),
(349, 2, 12, 80, '6', '6', '6', 12),
(350, 2, 12, 80, '6', '7', '1', 12),
(351, 2, 12, 80, '6', '7', '2', 12),
(352, 2, 12, 80, '6', '7', '3', 12),
(353, 2, 12, 80, '6', '7', '4', 12),
(354, 2, 12, 80, '6', '7', '5', 12),
(355, 2, 12, 80, '6', '7', '6', 12),
(356, 2, 12, 73, '6', '1', '1', 12),
(357, 2, 12, 73, '6', '1', '2', 12),
(358, 2, 12, 73, '6', '1', '3', 12),
(359, 2, 12, 73, '6', '1', '4', 12),
(360, 2, 12, 73, '6', '1', '5', 12),
(361, 2, 12, 73, '6', '1', '6', 12),
(362, 2, 12, 73, '6', '2', '1', 12),
(363, 2, 12, 73, '6', '2', '2', 12),
(364, 2, 12, 73, '6', '2', '3', 12),
(365, 2, 12, 73, '6', '2', '4', 12),
(366, 2, 12, 73, '6', '2', '5', 12),
(367, 2, 12, 73, '6', '2', '6', 12),
(368, 2, 12, 73, '6', '3', '1', 12),
(369, 2, 12, 73, '6', '3', '2', 12),
(370, 2, 12, 73, '6', '3', '3', 12),
(371, 2, 12, 73, '6', '3', '4', 12),
(372, 2, 12, 73, '6', '3', '5', 12),
(373, 2, 12, 73, '6', '3', '6', 12),
(374, 2, 12, 73, '6', '4', '1', 12),
(375, 2, 12, 73, '6', '4', '2', 12),
(376, 2, 12, 73, '6', '4', '3', 12),
(377, 2, 12, 73, '6', '4', '4', 12),
(378, 2, 12, 73, '6', '4', '5', 12),
(379, 2, 12, 73, '6', '4', '6', 12),
(380, 2, 12, 73, '5', '5', '1', 12),
(381, 2, 12, 73, '5', '5', '2', 12),
(382, 2, 12, 73, '5', '5', '3', 12),
(383, 2, 12, 73, '5', '5', '4', 12),
(384, 2, 12, 73, '5', '5', '5', 12),
(385, 2, 12, 73, '5', '5', '6', 12),
(386, 2, 12, 73, '5', '6', '1', 12),
(387, 2, 12, 73, '5', '6', '2', 12),
(388, 2, 12, 73, '5', '6', '3', 12),
(389, 2, 12, 73, '5', '6', '4', 12),
(390, 2, 12, 73, '5', '6', '5', 12),
(391, 2, 12, 73, '5', '6', '6', 12),
(392, 2, 12, 73, '0', '7', '1', 12),
(393, 2, 12, 73, '0', '7', '2', 12),
(394, 2, 12, 73, '0', '7', '3', 12),
(395, 2, 12, 73, '0', '7', '4', 12),
(396, 2, 12, 73, '4', '7', '5', 12),
(397, 2, 12, 73, '4', '7', '6', 12),
(398, 2, 12, 71, '5', '1', '1', 12),
(399, 2, 12, 71, '5', '1', '2', 12),
(400, 2, 12, 71, '5', '1', '3', 12),
(401, 2, 12, 71, '5', '1', '4', 12),
(402, 2, 12, 71, '5', '1', '5', 12),
(403, 2, 12, 71, '5', '1', '6', 12),
(404, 2, 12, 71, '5', '2', '1', 12),
(405, 2, 12, 71, '5', '2', '2', 12),
(406, 2, 12, 71, '5', '2', '3', 12),
(407, 2, 12, 71, '5', '2', '4', 12),
(408, 2, 12, 71, '5', '2', '5', 12),
(409, 2, 12, 71, '5', '2', '6', 12),
(410, 2, 12, 71, '5', '3', '1', 12),
(411, 2, 12, 71, '5', '3', '2', 12),
(412, 2, 12, 71, '5', '3', '3', 12),
(413, 2, 12, 71, '4', '3', '4', 12),
(414, 2, 12, 71, '4', '3', '5', 12),
(415, 2, 12, 71, '4', '3', '6', 12),
(416, 2, 12, 71, '4', '4', '1', 12),
(417, 2, 12, 71, '4', '4', '2', 12),
(418, 2, 12, 71, '4', '4', '3', 12),
(419, 2, 12, 71, '3', '4', '4', 12),
(420, 2, 12, 71, '3', '4', '5', 12),
(421, 2, 12, 71, '3', '4', '6', 12),
(422, 2, 12, 71, '3', '5', '1', 12),
(423, 2, 12, 71, '3', '5', '2', 12),
(424, 2, 12, 71, '3', '5', '3', 12),
(425, 2, 12, 71, '3', '5', '4', 12),
(426, 2, 12, 71, '2', '5', '5', 12),
(427, 2, 12, 71, '2', '5', '6', 12),
(428, 2, 12, 71, '2', '6', '1', 12),
(429, 2, 12, 71, '2', '6', '2', 12),
(430, 2, 12, 71, '2', '6', '3', 12),
(431, 2, 12, 71, '2', '6', '4', 12),
(432, 2, 12, 71, '6', '6', '5', 12),
(433, 2, 12, 71, '6', '6', '6', 12),
(434, 2, 12, 71, '6', '7', '1', 12),
(435, 2, 12, 71, '6', '7', '2', 12),
(436, 2, 12, 71, '6', '7', '3', 12),
(437, 2, 12, 71, '6', '7', '4', 12),
(438, 2, 12, 71, '6', '7', '5', 12),
(439, 2, 12, 71, '6', '7', '6', 12),
(440, 2, 12, 78, '5', '1', '1', 12),
(441, 2, 12, 78, '5', '1', '2', 12),
(442, 2, 12, 78, '5', '1', '3', 12),
(443, 2, 12, 78, '5', '1', '4', 12),
(444, 2, 12, 78, '5', '1', '5', 12),
(445, 2, 12, 78, '4', '1', '6', 12),
(446, 2, 12, 78, '4', '2', '1', 12),
(447, 2, 12, 78, '4', '2', '2', 12),
(448, 2, 12, 78, '4', '2', '3', 12),
(449, 2, 12, 78, '5', '2', '4', 12),
(450, 2, 12, 78, '3', '2', '5', 12),
(451, 2, 12, 78, '3', '2', '6', 12),
(452, 2, 12, 78, '3', '3', '1', 12),
(453, 2, 12, 78, '3', '3', '2', 12),
(454, 2, 12, 78, '3', '3', '3', 12),
(455, 2, 12, 78, '3', '3', '4', 12),
(456, 2, 12, 78, '3', '3', '6', 12),
(457, 2, 12, 78, '4', '3', '5', 12),
(458, 2, 12, 78, '5', '4', '1', 12),
(459, 2, 12, 78, '5', '4', '2', 12),
(460, 2, 12, 78, '5', '4', '3', 12),
(461, 2, 12, 78, '5', '4', '4', 12),
(462, 2, 12, 78, '4', '4', '5', 12),
(463, 2, 12, 78, '4', '4', '6', 12),
(464, 2, 12, 78, '4', '5', '1', 12),
(465, 2, 12, 78, '4', '5', '2', 12),
(466, 2, 12, 78, '4', '5', '3', 12),
(467, 2, 12, 78, '4', '5', '4', 12),
(468, 2, 12, 78, '3', '5', '5', 12),
(469, 2, 12, 78, '3', '5', '6', 12),
(470, 2, 12, 78, '3', '6', '1', 12),
(471, 2, 12, 78, '3', '6', '2', 12),
(472, 2, 12, 78, '3', '6', '3', 12),
(473, 2, 12, 78, '3', '6', '4', 12),
(474, 2, 12, 78, '2', '6', '5', 12),
(475, 2, 12, 78, '3', '6', '6', 12),
(476, 2, 12, 78, '2', '7', '1', 12),
(477, 2, 12, 78, '3', '7', '2', 12),
(478, 2, 12, 78, '2', '7', '3', 12),
(479, 2, 12, 78, '3', '7', '4', 12),
(480, 2, 12, 78, '2', '7', '5', 12),
(481, 2, 12, 78, '3', '7', '6', 12),
(482, 2, 12, 74, '4', '1', '1', 12),
(483, 2, 12, 74, '4', '1', '2', 12),
(484, 2, 12, 74, '4', '1', '3', 12),
(485, 2, 12, 74, '4', '1', '4', 12),
(486, 2, 12, 74, '4', '1', '5', 12),
(487, 2, 12, 74, '4', '1', '6', 12),
(488, 2, 12, 74, '4', '2', '1', 12),
(489, 2, 12, 74, '4', '2', '2', 12),
(490, 2, 12, 74, '4', '2', '3', 12),
(491, 2, 12, 74, '4', '2', '4', 12),
(492, 2, 12, 74, '4', '2', '5', 12),
(493, 2, 12, 74, '4', '2', '6', 12),
(494, 2, 12, 74, '4', '3', '1', 12),
(495, 2, 12, 74, '4', '3', '2', 12),
(496, 2, 12, 74, '4', '3', '3', 12),
(497, 2, 12, 74, '4', '3', '4', 12),
(498, 2, 12, 74, '4', '3', '5', 12),
(499, 2, 12, 74, '4', '3', '6', 12),
(500, 2, 12, 74, '4', '4', '1', 12),
(501, 2, 12, 74, '4', '4', '2', 12),
(502, 2, 12, 74, '4', '4', '3', 12),
(503, 2, 12, 74, '4', '4', '4', 12),
(504, 2, 12, 74, '4', '4', '5', 12),
(505, 2, 12, 74, '4', '4', '6', 12),
(506, 2, 12, 74, '4', '5', '1', 12),
(507, 2, 12, 74, '4', '5', '2', 12),
(508, 2, 12, 74, '4', '5', '3', 12),
(509, 2, 12, 74, '4', '5', '4', 12),
(510, 2, 12, 74, '4', '5', '5', 12),
(511, 2, 12, 74, '4', '5', '6', 12),
(512, 2, 12, 74, '4', '6', '1', 12),
(513, 2, 12, 74, '4', '6', '2', 12),
(514, 2, 12, 74, '4', '6', '3', 12),
(515, 2, 12, 74, '4', '6', '4', 12),
(516, 2, 12, 74, '4', '6', '5', 12),
(517, 2, 12, 74, '4', '6', '6', 12),
(518, 2, 12, 74, '4', '7', '1', 12),
(519, 2, 12, 74, '4', '7', '2', 12),
(520, 2, 12, 74, '4', '7', '3', 12),
(521, 2, 12, 74, '4', '7', '4', 12),
(522, 2, 12, 74, '4', '7', '5', 12),
(523, 2, 12, 74, '4', '7', '6', 12),
(524, 2, 11, 22, '', '1', '1', 13),
(526, 2, 12, 76, '2', '1', '2', 16),
(527, 2, 12, 76, '5', '1', '3', 16),
(528, 2, 12, 76, 'x', '2', '1', 16),
(529, 2, 12, 76, 'm', '2', '2', 16),
(530, 2, 12, 76, 'x', '2', '3', 16);

-- --------------------------------------------------------

--
-- Table structure for table `score_boards`
--

CREATE TABLE `score_boards` (
  `id` int NOT NULL,
  `kegiatan_id` int NOT NULL,
  `category_id` int NOT NULL,
  `jumlah_sesi` int NOT NULL,
  `jumlah_anak_panah` int NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `score_boards`
--

INSERT INTO `score_boards` (`id`, `kegiatan_id`, `category_id`, `jumlah_sesi`, `jumlah_anak_panah`, `created`) VALUES
(2, 1, 1, 1, 1, '2025-09-23 11:56:06'),
(8, 11, 2, 9, 3, '2025-09-23 21:11:38'),
(9, 11, 2, 5, 3, '2025-09-23 21:12:57'),
(12, 12, 2, 7, 6, '2025-09-25 16:17:04'),
(13, 11, 2, 9, 3, '2025-09-25 21:09:43'),
(14, 11, 2, 9, 3, '2025-09-25 21:32:24'),
(15, 11, 2, 9, 3, '2025-09-25 23:20:43'),
(16, 12, 2, 9, 3, '2025-09-25 23:43:17');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `key_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `value` text COLLATE utf8mb4_general_ci,
  `description` text COLLATE utf8mb4_general_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key_name`, `value`, `description`, `updated_at`) VALUES
(1, 'site_name', 'Tournament Management System', 'Nama aplikasi', '2025-08-16 04:53:15'),
(2, 'site_logo', '/assets/images/logo.png', 'Path logo aplikasi', '2025-08-16 04:53:15'),
(3, 'default_match_duration', '60', 'Durasi default pertandingan dalam menit', '2025-08-16 04:53:15'),
(4, 'timezone', 'Asia/Jakarta', 'Timezone aplikasi', '2025-08-16 04:53:15'),
(5, 'currency', 'IDR', 'Mata uang yang digunakan', '2025-08-16 04:53:15'),
(6, 'max_upload_size', '5MB', 'Ukuran maksimal upload file', '2025-08-16 04:53:15'),
(7, 'contact_email', 'info@tournament.com', 'Email kontak', '2025-08-16 04:53:15'),
(8, 'contact_phone', '+62-21-1234567', 'Nomor telepon kontak', '2025-08-16 04:53:15');

-- --------------------------------------------------------

--
-- Table structure for table `tournaments`
--

CREATE TABLE `tournaments` (
  `id` int NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `registration_start` datetime NOT NULL,
  `registration_end` datetime NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `entry_fee` decimal(10,2) DEFAULT '0.00',
  `prize_pool` decimal(10,2) DEFAULT '0.00',
  `max_participants` int DEFAULT '32',
  `tournament_type` enum('single_elimination','double_elimination','round_robin','swiss') COLLATE utf8mb4_general_ci DEFAULT 'single_elimination',
  `status` enum('draft','registration','ongoing','completed','cancelled') COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `rules` text COLLATE utf8mb4_general_ci,
  `contact_person` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `poster` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournaments`
--

INSERT INTO `tournaments` (`id`, `name`, `description`, `start_date`, `end_date`, `registration_start`, `registration_end`, `location`, `entry_fee`, `prize_pool`, `max_participants`, `tournament_type`, `status`, `rules`, `contact_person`, `contact_phone`, `poster`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Turnamen Panahan 2025', '-', '2025-09-01 08:00:00', '2025-09-03 18:00:00', '2025-08-01 00:00:00', '2025-08-25 23:59:59', '-', '150000.00', '50000000.00', 128, 'single_elimination', 'registration', NULL, NULL, NULL, NULL, NULL, '2025-08-16 04:37:59', '2025-08-18 05:14:23'),
(2, 'Turnamen Badminton Nusantara 2025', 'Turnamen badminton tingkat nasional dengan berbagai kategori usia', '2025-09-01 08:00:00', '2025-09-03 18:00:00', '2025-08-01 00:00:00', '2025-08-25 23:59:59', 'GOR Senayan, Jakarta', '150000.00', '50000000.00', 128, 'single_elimination', 'registration', NULL, NULL, NULL, NULL, NULL, '2025-08-16 04:48:56', '2025-08-16 04:48:56'),
(4, 'Testing', '-', '2025-08-01 11:44:38', '2025-08-02 11:44:38', '2025-08-01 11:44:38', '2025-08-05 11:44:38', '-', '1.10', '1.00', 32, 'single_elimination', 'registration', '-', '-', '-', '-', NULL, '2025-08-01 03:44:38', '2025-08-01 03:44:38');

-- --------------------------------------------------------

--
-- Table structure for table `tournament_categories`
--

CREATE TABLE `tournament_categories` (
  `id` int NOT NULL,
  `tournament_id` int NOT NULL,
  `category_id` int NOT NULL,
  `max_participants` int DEFAULT '32',
  `entry_fee` decimal(10,2) DEFAULT '0.00',
  `prize_1st` decimal(10,2) DEFAULT '0.00',
  `prize_2nd` decimal(10,2) DEFAULT '0.00',
  `prize_3rd` decimal(10,2) DEFAULT '0.00',
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournament_categories`
--

INSERT INTO `tournament_categories` (`id`, `tournament_id`, `category_id`, `max_participants`, `entry_fee`, `prize_1st`, `prize_2nd`, `prize_3rd`, `status`) VALUES
(1, 1, 1, 32, '0.00', '0.00', '0.00', '0.00', 'active'),
(2, 1, 2, 32, '0.00', '0.00', '0.00', '0.00', 'active'),
(3, 1, 3, 32, '0.00', '0.00', '0.00', '0.00', 'active'),
(4, 1, 4, 32, '0.00', '0.00', '0.00', '0.00', 'active'),
(5, 1, 5, 32, '0.00', '0.00', '0.00', '0.00', 'active'),
(6, 1, 6, 32, '0.00', '0.00', '0.00', '0.00', 'active'),
(7, 1, 7, 32, '0.00', '0.00', '0.00', '0.00', 'active'),
(8, 1, 8, 32, '0.00', '0.00', '0.00', '0.00', 'active'),
(16, 1, 10, 16, '100000.00', '2000000.00', '1000000.00', '500000.00', 'active'),
(17, 1, 11, 16, '150000.00', '3000000.00', '1500000.00', '750000.00', 'active'),
(18, 1, 12, 16, '150000.00', '3000000.00', '1500000.00', '750000.00', 'active'),
(19, 1, 13, 32, '200000.00', '5000000.00', '2500000.00', '1250000.00', 'active'),
(20, 1, 14, 32, '200000.00', '5000000.00', '2500000.00', '1250000.00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `tournament_participants`
--

CREATE TABLE `tournament_participants` (
  `id` int NOT NULL,
  `tournament_id` int NOT NULL,
  `participant_id` int NOT NULL,
  `category_id` int NOT NULL,
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_status` enum('pending','paid','refunded') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT NULL,
  `seed_number` int DEFAULT NULL,
  `status` enum('registered','confirmed','withdrew','disqualified') COLLATE utf8mb4_general_ci DEFAULT 'registered',
  `notes` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tournament_participants`
--

INSERT INTO `tournament_participants` (`id`, `tournament_id`, `participant_id`, `category_id`, `registration_date`, `payment_status`, `payment_date`, `seed_number`, `status`, `notes`) VALUES
(1, 1, 2, 10, '2025-08-16 04:53:16', 'pending', NULL, NULL, 'confirmed', NULL),
(2, 1, 3, 11, '2025-08-16 04:53:16', 'pending', NULL, NULL, 'confirmed', NULL),
(3, 1, 4, 12, '2025-08-16 04:53:16', 'pending', NULL, NULL, 'confirmed', NULL),
(4, 1, 5, 13, '2025-08-16 04:53:16', 'pending', NULL, NULL, 'confirmed', NULL),
(5, 1, 6, 14, '2025-08-16 04:53:16', 'pending', NULL, NULL, 'confirmed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','operator','viewer') COLLATE utf8mb4_general_ci DEFAULT 'operator',
  `status` enum('active','inactive') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(11, 'yoga', 'yoga@gmail.com', '$2y$10$EwaVuikDfnmrlAwEZOjjy.NxISUnIuyTLbcG3e5tCHvgopM/CXjha', 'admin', 'active', '2025-09-10 06:05:31', '2025-09-10 06:05:31'),
(13, 'untung', 'untung@gmail.com', '$2y$10$YvbeZ2P2YX/kwah4mh1fpO6OTtHwowAS5NbzuHHgwRXEwRYcKL4QC', 'admin', 'active', '2025-09-10 07:25:50', '2025-09-10 07:25:50'),
(17, 'Admin', 'admin1@gmail.com', '$2y$10$bOQLGK.KPt8OKhSIZuvNJemueyldT.XwoidyDFhT9ANb5ivla6qXC', 'admin', 'active', '2025-09-10 07:31:42', '2025-09-10 07:31:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kegiatan_kategori`
--
ALTER TABLE `kegiatan_kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `match_results`
--
ALTER TABLE `match_results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `peserta`
--
ALTER TABLE `peserta`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `score`
--
ALTER TABLE `score`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `score_boards`
--
ALTER TABLE `score_boards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tournament_participants`
--
ALTER TABLE `tournament_participants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `kegiatan_kategori`
--
ALTER TABLE `kegiatan_kategori`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `match_results`
--
ALTER TABLE `match_results`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `peserta`
--
ALTER TABLE `peserta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `score`
--
ALTER TABLE `score`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=531;

--
-- AUTO_INCREMENT for table `score_boards`
--
ALTER TABLE `score_boards`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tournament_participants`
--
ALTER TABLE `tournament_participants`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
