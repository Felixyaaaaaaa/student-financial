-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for keuangan_db
CREATE DATABASE IF NOT EXISTS `keuangan_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `keuangan_db`;

-- Dumping structure for table keuangan_db.anggaran
CREATE TABLE IF NOT EXISTS `anggaran` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_mahasiswa` int NOT NULL,
  `tipe` enum('Harian','Mingguan','Bulanan','Tahunan') COLLATE utf8mb4_general_ci NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_buat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_mahasiswa` (`id_mahasiswa`),
  CONSTRAINT `anggaran_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table keuangan_db.anggaran: ~7 rows (approximately)
INSERT INTO `anggaran` (`id`, `id_mahasiswa`, `tipe`, `jumlah`, `keterangan`, `tanggal_buat`) VALUES
	(21, 4, 'Harian', 100000.00, 'makan', '2025-08-29 01:37:26'),
	(22, 5, 'Harian', 20000.00, '', '2025-10-01 03:24:55'),
	(23, 5, 'Mingguan', 200000.00, '', '2025-10-03 15:45:09'),
	(24, 5, 'Bulanan', 1000000.00, '', '2025-10-03 15:45:44'),
	(25, 5, 'Tahunan', 12000000.00, '', '2025-10-03 15:45:54'),
	(28, 10, 'Mingguan', 500000.00, 'satu', '2025-10-10 02:01:47'),
	(29, 9, 'Mingguan', 100000.00, '', '2025-10-10 02:03:17'),
	(31, 11, 'Harian', 20000.00, '', '2025-10-10 02:25:59');

-- Dumping structure for table keuangan_db.mahasiswa
CREATE TABLE IF NOT EXISTS `mahasiswa` (
  `id_mahasiswa` int NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `kata_sandi` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mahasiswa`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table keuangan_db.mahasiswa: ~8 rows (approximately)
INSERT INTO `mahasiswa` (`id_mahasiswa`, `nama`, `email`, `kata_sandi`, `created_at`, `updated_at`) VALUES
	(4, 'lingga', 'lingga@gmail.com', '$2y$10$6cVR3eUsa8AO4z2DtUQEH.4.B6BV1DFWlg3vWiU5Gu8VsoO1yU3CW', '2025-08-29 08:15:53', '2025-08-29 08:45:33'),
	(5, 'Joko', 'joko@gmail.com', '$2y$10$8RytHN9dNsHTLTXerxGDEucFqoQirodvQhjY9W8GuFJ/p3uanS2uu', '2025-09-03 22:47:34', '2025-10-08 11:23:28'),
	(7, 'felix', 'felixyulianasterino@gmail.com', '$2y$10$hLPISOT.vPnBF3G.UYcgYOCoJ.YATiqIYOHVv7uXFfGGls9wGpIhm', '2025-10-08 16:59:15', '2025-10-08 16:59:15'),
	(8, 'putri', 'putri@gmail.com', '$2y$10$QhJftSwbIh98TuE79FrIveFx94I7OME9r3V.r67Hbr3SKDgpOfc4O', '2025-10-08 18:36:07', '2025-10-08 18:36:07'),
	(9, 'rico', 'rico@gmail.com', '$2y$10$XjDrZe1NiIRI.n6pt1n5yO.UZylFcicTLmAOTXQHpLu/n67wkZHgK', '2025-10-10 09:00:45', '2025-10-10 09:00:45'),
	(10, 'omega', 'dosen@gmail.com', '$2y$10$XJ0r9U.LLKgxQ8MolF8D4OJko5UxknbHX/l/0zioR/5cZQ2BgsARi', '2025-10-10 09:00:52', '2025-10-10 09:00:52'),
	(11, 'levi', 'levi@gmail.com', '$2y$10$.QY4xV2oPgW5O0UXF785Me8bsMQTSydJcD2DYNj5D9ACCKVZabIRq', '2025-10-10 09:01:46', '2025-10-10 09:01:46'),
	(12, 'amba', 'ambalingga@gmail.com', '$2y$10$0cbpF.1bz/CXZ6rGcsQtCusWfwNj09wd6sEKgHQHSgDRt.CwSqfnC', '2025-10-10 09:09:18', '2025-10-10 09:09:18');

-- Dumping structure for table keuangan_db.tabungan
CREATE TABLE IF NOT EXISTS `tabungan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_mahasiswa` int NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `saldo` decimal(15,2) NOT NULL DEFAULT '0.00',
  `target_amount` decimal(15,2) DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_mahasiswa` (`id_mahasiswa`),
  CONSTRAINT `tabungan_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table keuangan_db.tabungan: ~3 rows (approximately)
INSERT INTO `tabungan` (`id`, `id_mahasiswa`, `nama`, `saldo`, `target_amount`, `target_date`, `created_at`, `updated_at`) VALUES
	(9, 4, 'BANK MANDIRI', 10000.00, 1000000.00, NULL, '2025-08-29 01:31:15', '2025-08-29 01:33:43'),
	(13, 5, 'BANK MANDIRI - SIMPANAN', 58000.00, 2000000.00, NULL, '2025-09-03 16:10:25', '2025-10-08 04:25:58'),
	(14, 11, 'BANK MANDIRI', 60000.00, NULL, NULL, '2025-10-10 02:07:27', '2025-10-10 02:07:27');

-- Dumping structure for table keuangan_db.tabungan_transaksi
CREATE TABLE IF NOT EXISTS `tabungan_transaksi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tabungan_id` int NOT NULL,
  `id_mahasiswa` int NOT NULL,
  `tipe` enum('deposit','withdraw') COLLATE utf8mb4_general_ci NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tabungan_id` (`tabungan_id`),
  KEY `id_mahasiswa` (`id_mahasiswa`),
  CONSTRAINT `tabungan_transaksi_ibfk_1` FOREIGN KEY (`tabungan_id`) REFERENCES `tabungan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tabungan_transaksi_ibfk_2` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table keuangan_db.tabungan_transaksi: ~4 rows (approximately)
INSERT INTO `tabungan_transaksi` (`id`, `tabungan_id`, `id_mahasiswa`, `tipe`, `jumlah`, `keterangan`, `tanggal`) VALUES
	(15, 9, 4, 'deposit', 10000.00, '', '2025-08-29 01:33:43'),
	(16, 13, 5, 'deposit', 20000.00, 'day one nabung', '2025-10-03 15:28:29'),
	(17, 13, 5, 'withdraw', 12000.00, '', '2025-10-03 15:40:24'),
	(18, 13, 5, 'deposit', 20000.00, '', '2025-10-03 15:44:14'),
	(19, 13, 5, 'deposit', 30000.00, '', '2025-10-08 04:25:58'),
	(20, 14, 11, 'deposit', 60000.00, 'Initial deposit', '2025-10-10 02:07:27');

-- Dumping structure for table keuangan_db.transaksi
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_mahasiswa` int NOT NULL,
  `jenis` enum('Pemasukan','Pengeluaran') COLLATE utf8mb4_general_ci NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_mahasiswa` (`id_mahasiswa`),
  CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table keuangan_db.transaksi: ~8 rows (approximately)
INSERT INTO `transaksi` (`id`, `id_mahasiswa`, `jenis`, `jumlah`, `keterangan`, `tanggal`) VALUES
	(41, 4, 'Pemasukan', 1000000.00, 'saku', '2025-08-29 01:36:40'),
	(43, 4, 'Pengeluaran', 900000.00, 'slot', '2025-08-29 01:39:18'),
	(45, 5, 'Pemasukan', 1000000.00, 'saku bulanan\r\n\r\n', '2025-09-03 16:11:10'),
	(46, 5, 'Pengeluaran', 20000.00, 'Transfer ke tabungan: BANK MANDIRI - SIMPANAN - day one nabung', '2025-10-03 15:28:29'),
	(47, 5, 'Pemasukan', 12000.00, 'Tarik dari tabungan: BANK MANDIRI - SIMPANAN', '2025-10-03 15:40:24'),
	(49, 5, 'Pengeluaran', 30000.00, 'Transfer ke tabungan: BANK MANDIRI - SIMPANAN', '2025-10-08 04:25:58'),
	(50, 11, 'Pemasukan', 100000.00, '', '2025-10-10 02:05:40'),
	(51, 11, 'Pengeluaran', 10000.00, '', '2025-10-10 02:06:00'),
	(52, 11, 'Pengeluaran', 60000.00, 'Transfer ke tabungan: BANK MANDIRI', '2025-10-10 02:07:27'),
	(53, 5, 'Pengeluaran', 15000.00, 'geprek\r\n', '2025-11-14 07:04:20');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
