-- Adminer 4.8.4 MySQL 8.0.21 dump - FIXED VERSION

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

-- ============================================
-- TABLES (Urutan berdasarkan dependencies)
-- ============================================

-- 1. Master Tables (No dependencies)

DROP TABLE IF EXISTS `tbl_role`;
CREATE TABLE `tbl_role` (
  `id_role` int NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `nama_role` (`nama_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_role` (`id_role`, `nama_role`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1,	'Super Admin',	'Administrator tertinggi dengan akses penuh ke seluruh sistem',	'2025-11-19 04:11:21',	'2025-11-19 04:11:21'),
(2,	'Pengelola Dapur',	'Mengelola dapur, karyawan, menu, dan operasional',	'2025-11-19 04:11:21',	'2025-11-19 04:11:21'),
(3,	'Karyawan',	'Staff yang bekerja di dapur dan melakukan produksi',	'2025-11-19 04:11:21',	'2025-11-19 04:11:21');

DROP TABLE IF EXISTS `tbl_super_admin`;
CREATE TABLE `tbl_super_admin` (
  `id_super_admin` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `no_telepon` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_profil` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_general_ci DEFAULT 'aktif',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_super_admin`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_super_admin` (`id_super_admin`, `username`, `password`, `nama_lengkap`, `email`, `no_telepon`, `foto_profil`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1,	'admin',	'admin123',	'Super Administrator',	'admin@mbg.com',	'081234567890',	NULL,	'aktif',	'2025-11-24 02:46:38',	'2025-11-19 04:11:21',	'2025-11-24 02:46:38');

DROP TABLE IF EXISTS `tbl_pengelola_dapur`;
CREATE TABLE `tbl_pengelola_dapur` (
  `id_pengelola` int NOT NULL AUTO_INCREMENT,
  `id_role` int DEFAULT '2',
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `no_telepon` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `foto_profil` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_general_ci DEFAULT 'aktif',
  `verification_code` varchar(6) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `verification_expires_at` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pengelola`),
  UNIQUE KEY `email` (`email`),
  KEY `id_role` (`id_role`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  CONSTRAINT `tbl_pengelola_dapur_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `tbl_role` (`id_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_pengelola_dapur` (`id_pengelola`, `id_role`, `nama`, `no_telepon`, `email`, `password`, `foto_profil`, `status`, `verification_code`, `verification_expires_at`, `is_verified`, `created_at`, `updated_at`) VALUES
(1,	2,	'abcd',	'089767890',	'm@xexample.com',	'Inc1019',	NULL,	'aktif',	NULL,	NULL,	1,	'2025-11-19 16:18:50',	'2025-11-22 23:12:39'),
(3,	2,	'zikri',	NULL,	'muhammadzikrialfadani02@gmail.com',	'zikri123',	NULL,	'aktif',	NULL,	'2025-11-23 23:21:09',	1,	'2025-11-22 22:21:09',	'2025-11-22 22:21:47');

-- 2. Dependent Tables

DROP TABLE IF EXISTS `tbl_dapur`;
CREATE TABLE `tbl_dapur` (
  `id_dapur` int NOT NULL AUTO_INCREMENT,
  `id_pengelola` int NOT NULL,
  `nama_dapur` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_general_ci NOT NULL,
  `kapasitas_produksi` int DEFAULT NULL,
  `jumlah_karyawan` int DEFAULT '0',
  `status` enum('aktif','nonaktif','maintenance') COLLATE utf8mb4_general_ci DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_dapur`),
  KEY `idx_pengelola` (`id_pengelola`),
  KEY `idx_status` (`status`),
  CONSTRAINT `tbl_dapur_ibfk_1` FOREIGN KEY (`id_pengelola`) REFERENCES `tbl_pengelola_dapur` (`id_pengelola`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_dapur` (`id_dapur`, `id_pengelola`, `nama_dapur`, `alamat`, `kapasitas_produksi`, `jumlah_karyawan`, `status`, `created_at`, `updated_at`) VALUES
(1,	1,	'dapur',	'kudus',	3000,	3,	'aktif',	'2025-11-21 07:06:46',	'2025-11-23 09:42:22');

DROP TABLE IF EXISTS `tbl_bahan_baku`;
CREATE TABLE `tbl_bahan_baku` (
  `id_bahan` int NOT NULL AUTO_INCREMENT,
  `nama_bahan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `satuan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `stok_saat_ini` decimal(10,2) DEFAULT '0.00',
  `harga_per_satuan` decimal(15,2) DEFAULT NULL,
  `stok_minimum` int DEFAULT '10',
  `status` enum('tersedia','habis','discontinued') COLLATE utf8mb4_general_ci DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_bahan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- FIXED: Removed duplicate entries
INSERT INTO `tbl_bahan_baku` (`id_bahan`, `nama_bahan`, `satuan`, `stok_saat_ini`, `harga_per_satuan`, `stok_minimum`, `status`, `created_at`, `updated_at`) VALUES
(1,	'telor',	'kg',	20.00,	NULL,	10,	'tersedia',	'2025-11-22 19:06:33',	'2025-11-22 19:32:38'),
(2,	'beras',	'karung',	100.00,	NULL,	10,	'tersedia',	'2025-11-22 19:06:33',	'2025-11-22 19:32:38');

DROP TABLE IF EXISTS `tbl_karyawan`;
CREATE TABLE `tbl_karyawan` (
  `id_karyawan` int NOT NULL AUTO_INCREMENT,
  `id_role` int DEFAULT '3',
  `id_pengelola` int NOT NULL,
  `id_dapur` int DEFAULT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `bagian` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `no_telepon` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  `tanggal_bergabung` date DEFAULT NULL,
  `foto_profil` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('aktif','nonaktif','cuti') COLLATE utf8mb4_general_ci DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hari_libur` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_karyawan`),
  UNIQUE KEY `email` (`email`),
  KEY `id_role` (`id_role`),
  KEY `idx_pengelola` (`id_pengelola`),
  KEY `idx_dapur` (`id_dapur`),
  KEY `idx_status` (`status`),
  CONSTRAINT `tbl_karyawan_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `tbl_role` (`id_role`),
  CONSTRAINT `tbl_karyawan_ibfk_2` FOREIGN KEY (`id_pengelola`) REFERENCES `tbl_pengelola_dapur` (`id_pengelola`) ON DELETE CASCADE,
  CONSTRAINT `tbl_karyawan_ibfk_3` FOREIGN KEY (`id_dapur`) REFERENCES `tbl_dapur` (`id_dapur`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_karyawan` (`id_karyawan`, `id_role`, `id_pengelola`, `id_dapur`, `nama`, `bagian`, `jam_masuk`, `jam_keluar`, `email`, `password`, `no_telepon`, `alamat`, `tanggal_bergabung`, `foto_profil`, `status`, `created_at`, `updated_at`, `hari_libur`) VALUES
(1,	3,	1,	1,	'bbbb',	'chef',	NULL,	NULL,	'Muhammadzikrialfadani01@gmail.com',	'123456',	'',	'kudus',	'2026-01-01',	NULL,	'aktif',	'2025-11-21 14:16:24',	'2025-11-21 14:16:49',	NULL),
(3,	3,	1,	1,	'Zikri',	'tukang_masak',	'06:40:00',	'06:39:00',	'mrzkr1019@gmail.com',	'010101',	'234567',	'kudus',	NULL,	NULL,	'aktif',	'2025-11-22 16:33:15',	'2025-11-23 07:37:45',	'Minggu'),
(4,	3,	1,	1,	'aya',	'pengantar',	'11:00:00',	'13:00:00',	'm@exampel.com',	'101010',	'',	'kudus',	NULL,	NULL,	'aktif',	'2025-11-23 09:42:22',	'2025-11-23 09:51:14',	'Sabtu,Minggu');

DROP TABLE IF EXISTS `tbl_absensi`;
CREATE TABLE `tbl_absensi` (
  `id_absensi` int NOT NULL AUTO_INCREMENT,
  `id_karyawan` int NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `total_jam_kerja` decimal(5,2) DEFAULT NULL,
  `status_kehadiran` enum('hadir','izin','sakit','alpha') COLLATE utf8mb4_general_ci DEFAULT 'hadir',
  `keterangan` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_absensi`),
  KEY `id_karyawan` (`id_karyawan`),
  CONSTRAINT `tbl_absensi_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `tbl_karyawan` (`id_karyawan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_absensi` (`id_absensi`, `id_karyawan`, `tanggal`, `jam_masuk`, `jam_keluar`, `total_jam_kerja`, `status_kehadiran`, `keterangan`, `created_at`, `updated_at`) VALUES
(1,	3,	'2025-11-23',	'00:08:21',	'00:08:32',	0.00,	'hadir',	NULL,	'2025-11-22 23:08:21',	'2025-11-22 23:08:32'),
(2,	3,	'2025-11-24',	'03:48:13',	'09:54:08',	6.10,	'hadir',	NULL,	'2025-11-24 02:48:13',	'2025-11-24 02:54:08');

DROP TABLE IF EXISTS `tbl_pembelanjaan`;
CREATE TABLE `tbl_pembelanjaan` (
  `id_pembelanjaan` int NOT NULL AUTO_INCREMENT,
  `id_dapur` int NOT NULL,
  `id_pengelola` int NOT NULL,
  `kode_pembelanjaan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `no_nota_fisik` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_pembelian` date NOT NULL,
  `supplier` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_pembelian` decimal(15,2) DEFAULT NULL,
  `metode_pembayaran` enum('tunai','transfer','kredit') COLLATE utf8mb4_general_ci DEFAULT 'tunai',
  `status_pembayaran` enum('lunas','belum_lunas','cicilan') COLLATE utf8mb4_general_ci DEFAULT 'lunas',
  `status` enum('rencana','selesai') COLLATE utf8mb4_general_ci DEFAULT 'rencana',
  `keterangan` text COLLATE utf8mb4_general_ci,
  `bukti_pembelian` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pembelanjaan`),
  UNIQUE KEY `kode_pembelanjaan` (`kode_pembelanjaan`),
  KEY `id_pengelola` (`id_pengelola`),
  KEY `idx_dapur` (`id_dapur`),
  KEY `idx_tanggal` (`tanggal_pembelian`),
  KEY `idx_kode` (`kode_pembelanjaan`),
  KEY `idx_status` (`status_pembayaran`),
  CONSTRAINT `tbl_pembelanjaan_ibfk_1` FOREIGN KEY (`id_dapur`) REFERENCES `tbl_dapur` (`id_dapur`) ON DELETE CASCADE,
  CONSTRAINT `tbl_pembelanjaan_ibfk_2` FOREIGN KEY (`id_pengelola`) REFERENCES `tbl_pengelola_dapur` (`id_pengelola`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_pembelanjaan` (`id_pembelanjaan`, `id_dapur`, `id_pengelola`, `kode_pembelanjaan`, `no_nota_fisik`, `tanggal_pembelian`, `supplier`, `total_pembelian`, `metode_pembayaran`, `status_pembayaran`, `status`, `keterangan`, `bukti_pembelian`, `created_at`, `updated_at`) VALUES
(1,	1,	1,	'PB202511231367',	'',	'2025-11-22',	'pasar',	15300000.00,	'tunai',	'lunas',	'selesai',	'',	'69220fd6e9c1a.jpeg',	'2025-11-22 19:06:33',	'2025-11-22 19:32:38');

DROP TABLE IF EXISTS `tbl_detail_pembelanjaan`;
CREATE TABLE `tbl_detail_pembelanjaan` (
  `id_detail` int NOT NULL AUTO_INCREMENT,
  `id_pembelanjaan` int NOT NULL,
  `id_bahan` int NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `satuan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `harga_satuan` decimal(15,2) DEFAULT NULL,
  `subtotal` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_detail`),
  KEY `idx_pembelanjaan` (`id_pembelanjaan`),
  KEY `idx_bahan` (`id_bahan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_detail_pembelanjaan` (`id_detail`, `id_pembelanjaan`, `id_bahan`, `jumlah`, `satuan`, `harga_satuan`, `subtotal`, `created_at`) VALUES
(1,	1,	1,	10.00,	'kg',	30000.00,	300000.00,	'2025-11-22 19:06:33'),
(2,	1,	2,	50.00,	'karung',	300000.00,	15000000.00,	'2025-11-22 19:06:33');

DROP TABLE IF EXISTS `tbl_riwayat_stok`;
CREATE TABLE `tbl_riwayat_stok` (
  `id_riwayat` int NOT NULL AUTO_INCREMENT,
  `id_bahan` int NOT NULL,
  `id_pembelanjaan` int DEFAULT NULL,
  `tipe_transaksi` enum('masuk','keluar','adjustment') COLLATE utf8mb4_general_ci NOT NULL,
  `jumlah_stok` decimal(10,2) NOT NULL,
  `stok_sebelum` decimal(10,2) DEFAULT NULL,
  `stok_sesudah` decimal(10,2) DEFAULT NULL,
  `satuan` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_transaksi` date NOT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_riwayat`),
  KEY `id_pembelanjaan` (`id_pembelanjaan`),
  KEY `idx_bahan` (`id_bahan`),
  KEY `idx_tanggal` (`tanggal_transaksi`),
  KEY `idx_tipe` (`tipe_transaksi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_riwayat_stok` (`id_riwayat`, `id_bahan`, `id_pembelanjaan`, `tipe_transaksi`, `jumlah_stok`, `stok_sebelum`, `stok_sesudah`, `satuan`, `tanggal_transaksi`, `keterangan`, `created_at`) VALUES
(7,	1,	1,	'masuk',	10.00,	0.00,	10.00,	'kg',	'2025-11-23',	'Pembelanjaan Selesai',	'2025-11-22 19:29:41'),
(8,	2,	1,	'masuk',	50.00,	0.00,	50.00,	'karung',	'2025-11-23',	'Pembelanjaan Selesai',	'2025-11-22 19:29:41'),
(9,	1,	1,	'masuk',	10.00,	10.00,	20.00,	'kg',	'2025-11-23',	'Pembelanjaan Selesai',	'2025-11-22 19:32:38'),
(10,	2,	1,	'masuk',	50.00,	50.00,	100.00,	'karung',	'2025-11-23',	'Pembelanjaan Selesai',	'2025-11-22 19:32:38');

DROP TABLE IF EXISTS `tbl_dokumentasi_karyawan`;
CREATE TABLE `tbl_dokumentasi_karyawan` (
  `id_dokumentasi` int NOT NULL AUTO_INCREMENT,
  `tanggal_dokumentasi` date NOT NULL,
  `aktivitas` text COLLATE utf8mb4_general_ci NOT NULL,
  `foto_dokumentasi` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_dokumentasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_dokumentasi_karyawan` (`id_dokumentasi`, `tanggal_dokumentasi`, `aktivitas`, `foto_dokumentasi`, `created_at`) VALUES
(2,	'2025-11-22',	'[ksdcm] sedang ',	'69221a9eb9d4a.png',	'2025-11-22 20:18:38'),
(3,	'2025-11-23',	'[Tukang Masak] memasak',	'6922c0f3c5f4f.jpg',	'2025-11-23 08:08:19');

DROP TABLE IF EXISTS `tbl_menu`;
CREATE TABLE `tbl_menu` (
  `id_menu` int NOT NULL AUTO_INCREMENT,
  `id_pengelola` int NOT NULL,
  `nama_menu` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `jumlah_porsi` int DEFAULT '0',
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `foto_menu` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_general_ci DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tanggal_menu` date DEFAULT NULL,
  `status_pengantaran` enum('belum_diantar','proses','selesai') COLLATE utf8mb4_general_ci DEFAULT 'belum_diantar',
  PRIMARY KEY (`id_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `tbl_resep_menu`;
CREATE TABLE `tbl_resep_menu` (
  `id_resep` int NOT NULL AUTO_INCREMENT,
  `id_menu` int NOT NULL,
  `id_bahan` int NOT NULL,
  `jumlah_bahan` decimal(10,2) NOT NULL,
  `satuan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_resep`),
  KEY `idx_menu` (`id_menu`),
  KEY `idx_bahan` (`id_bahan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `tbl_produksi_harian`;
CREATE TABLE `tbl_produksi_harian` (
  `id_produksi` int NOT NULL AUTO_INCREMENT,
  `id_menu` int NOT NULL,
  `id_karyawan` int NOT NULL,
  `id_dapur` int NOT NULL,
  `kode_produksi` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_produksi` date NOT NULL,
  `jumlah_porsi` int NOT NULL,
  `status` enum('proses','selesai','gagal','pending') COLLATE utf8mb4_general_ci DEFAULT 'proses',
  `kualitas` enum('sangat_baik','baik','cukup','kurang') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_selesai` time DEFAULT NULL,
  `durasi_produksi` int DEFAULT NULL COMMENT 'dalam menit',
  `keterangan` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_produksi`),
  UNIQUE KEY `idx_kode_produksi_unique` (`kode_produksi`),
  KEY `idx_menu` (`id_menu`),
  KEY `idx_karyawan` (`id_karyawan`),
  KEY `idx_dapur` (`id_dapur`),
  KEY `idx_tanggal` (`tanggal_produksi`),
  KEY `idx_kode` (`kode_produksi`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `tbl_laporan`;
CREATE TABLE `tbl_laporan` (
  `id_laporan` int NOT NULL AUTO_INCREMENT,
  `kode_laporan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tipe_laporan` enum('harian','mingguan','bulanan','custom') COLLATE utf8mb4_general_ci NOT NULL,
  `kategori_laporan` enum('produksi','keuangan','stok','karyawan','keseluruhan') COLLATE utf8mb4_general_ci NOT NULL,
  `id_pengelola` int DEFAULT NULL,
  `id_dapur` int DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `judul_laporan` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `data_laporan` longtext COLLATE utf8mb4_general_ci COMMENT 'JSON format',
  `file_pdf` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_laporan` enum('draft','final','approved') COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `dibuat_oleh` int DEFAULT NULL,
  `dibuat_oleh_tipe` enum('super_admin','pengelola') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `konten_laporan` longtext COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id_laporan`),
  UNIQUE KEY `kode_laporan` (`kode_laporan`),
  KEY `id_pengelola` (`id_pengelola`),
  KEY `id_dapur` (`id_dapur`),
  KEY `idx_kode` (`kode_laporan`),
  KEY `idx_tipe` (`tipe_laporan`),
  KEY `idx_kategori` (`kategori_laporan`),
  KEY `idx_tanggal` (`tanggal_mulai`,`tanggal_akhir`),
  KEY `idx_status` (`status_laporan`),
  KEY `idx_laporan_tipe` (`tipe_laporan`,`kategori_laporan`),
  CONSTRAINT `tbl_laporan_ibfk_1` FOREIGN KEY (`id_pengelola`) REFERENCES `tbl_pengelola_dapur` (`id_pengelola`) ON DELETE SET NULL,
  CONSTRAINT `tbl_laporan_ibfk_2` FOREIGN KEY (`id_dapur`) REFERENCES `tbl_dapur` (`id_dapur`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_laporan` (`id_laporan`, `kode_laporan`, `tipe_laporan`, `kategori_laporan`, `id_pengelola`, `id_dapur`, `tanggal_mulai`, `tanggal_akhir`, `judul_laporan`, `deskripsi`, `data_laporan`, `file_pdf`, `status_laporan`, `dibuat_oleh`, `dibuat_oleh_tipe`, `created_at`, `updated_at`, `konten_laporan`) VALUES
(1,	'LAP-KEU-20251123143725',	'bulanan',	'keuangan',	NULL,	NULL,	'2025-11-01',	'2025-11-30',	'Laporan Keuangan Periode November 2025',	NULL,	NULL,	NULL,	'final',	1,	'super_admin',	'2025-11-23 13:37:25',	'2025-11-23 13:37:25',	'{\"summary\":{\"total_pengeluaran\":15300000},\"details\":[{\"id_pembelanjaan\":\"1\",\"id_dapur\":\"1\",\"id_pengelola\":\"1\",\"kode_pembelanjaan\":\"PB202511231367\",\"no_nota_fisik\":\"\",\"tanggal_pembelian\":\"2025-11-22\",\"supplier\":\"pasar\",\"total_pembelian\":\"15300000.00\",\"metode_pembayaran\":\"tunai\",\"status_pembayaran\":\"lunas\",\"status\":\"selesai\",\"keterangan\":\"\",\"bukti_pembelian\":\"69220fd6e9c1a.jpeg\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\",\"nama_dapur\":\"dapur\"}]}'),
(2,	'LAP-STO-20251123143747',	'bulanan',	'stok',	NULL,	NULL,	'2025-11-01',	'2025-11-30',	'Laporan Stok Periode November 2025',	NULL,	NULL,	NULL,	'final',	1,	'super_admin',	'2025-11-23 13:37:47',	'2025-11-23 13:37:47',	'{\"summary\":{\"total_item\":2},\"details\":[{\"id_bahan\":\"2\",\"nama_bahan\":\"beras\",\"satuan\":\"karung\",\"stok_saat_ini\":\"100.00\",\"harga_per_satuan\":null,\"stok_minimum\":\"10\",\"status\":\"tersedia\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\"},{\"id_bahan\":\"1\",\"nama_bahan\":\"telor\",\"satuan\":\"kg\",\"stok_saat_ini\":\"20.00\",\"harga_per_satuan\":null,\"stok_minimum\":\"10\",\"status\":\"tersedia\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\"}]}'),
(3,	'LAP-KES-20251123143803',	'bulanan',	'keseluruhan',	NULL,	NULL,	'2025-11-01',	'2025-11-30',	'Laporan Keseluruhan Periode November 2025',	NULL,	NULL,	NULL,	'final',	1,	'super_admin',	'2025-11-23 13:38:03',	'2025-11-23 13:38:03',	'[]'),
(4,	'LAP-PRO-20251123144022',	'bulanan',	'produksi',	NULL,	NULL,	'2025-11-01',	'2025-11-30',	'Laporan Produksi Periode November 2025',	NULL,	NULL,	NULL,	'final',	1,	'super_admin',	'2025-11-23 13:40:22',	'2025-11-23 13:40:22',	'{\"summary\":{\"total_menu\":1},\"details\":[{\"id_menu\":\"1\",\"id_pengelola\":\"1\",\"nama_menu\":\"Nasi kuning\",\"jumlah_porsi\":\"0\",\"deskripsi\":\"susu\\r\\nbuah\\r\\nbu\\r\\nbukj\\r\\nyhjn\\r\\n\",\"foto_menu\":\"6921fd37a9a9a.jpeg\",\"status\":\"aktif\",\"created_at\":\"2025-11-23 01:13:11\",\"updated_at\":\"2025-11-23 01:49:08\",\"tanggal_menu\":\"2025-11-22\",\"status_pengantaran\":\"belum_diantar\",\"nama_dapur\":\"dapur\"}]}'),
(5,	'LAP-PRO-20251123144045',	'bulanan',	'produksi',	NULL,	NULL,	'2025-11-01',	'2025-11-30',	'Laporan Produksi Periode November 2025',	NULL,	NULL,	NULL,	'final',	1,	'super_admin',	'2025-11-23 13:40:45',	'2025-11-23 13:40:45',	'{\"summary\":{\"total_menu\":1},\"details\":[{\"id_menu\":\"1\",\"id_pengelola\":\"1\",\"nama_menu\":\"Nasi kuning\",\"jumlah_porsi\":\"0\",\"deskripsi\":\"susu\\r\\nbuah\\r\\nbu\\r\\nbukj\\r\\nyhjn\\r\\n\",\"foto_menu\":\"6921fd37a9a9a.jpeg\",\"status\":\"aktif\",\"created_at\":\"2025-11-23 01:13:11\",\"updated_at\":\"2025-11-23 01:49:08\",\"tanggal_menu\":\"2025-11-22\",\"status_pengantaran\":\"belum_diantar\",\"nama_dapur\":\"dapur\"}]}'),
(6,	'LAP-KEU-20251123144101',	'bulanan',	'keuangan',	NULL,	NULL,	'2025-11-01',	'2025-11-30',	'Laporan Keuangan Periode November 2025',	NULL,	NULL,	NULL,	'final',	1,	'super_admin',	'2025-11-23 13:41:01',	'2025-11-23 13:41:01',	'{\"summary\":{\"total_pengeluaran\":15300000},\"details\":[{\"id_pembelanjaan\":\"1\",\"id_dapur\":\"1\",\"id_pengelola\":\"1\",\"kode_pembelanjaan\":\"PB202511231367\",\"no_nota_fisik\":\"\",\"tanggal_pembelian\":\"2025-11-22\",\"supplier\":\"pasar\",\"total_pembelian\":\"15300000.00\",\"metode_pembayaran\":\"tunai\",\"status_pembayaran\":\"lunas\",\"status\":\"selesai\",\"keterangan\":\"\",\"bukti_pembelian\":\"69220fd6e9c1a.jpeg\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\",\"nama_dapur\":\"dapur\"}]}'),
(7,	'LAP-STO-20251123144108',	'bulanan',	'stok',	NULL,	NULL,	'2025-11-01',	'2025-11-30',	'Laporan Stok Periode November 2025',	NULL,	NULL,	NULL,	'final',	1,	'super_admin',	'2025-11-23 13:41:08',	'2025-11-23 13:41:08',	'{\"summary\":{\"total_item\":2},\"details\":[{\"id_bahan\":\"2\",\"nama_bahan\":\"beras\",\"satuan\":\"karung\",\"stok_saat_ini\":\"100.00\",\"harga_per_satuan\":null,\"stok_minimum\":\"10\",\"status\":\"tersedia\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\"},{\"id_bahan\":\"1\",\"nama_bahan\":\"telor\",\"satuan\":\"kg\",\"stok_saat_ini\":\"20.00\",\"harga_per_satuan\":null,\"stok_minimum\":\"10\",\"status\":\"tersedia\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\"}]}'),
(8,	'LAP-KAR-20251123144116',	'bulanan',	'karyawan',	NULL,	NULL,	'2025-11-01',	'2025-11-30',	'Laporan Karyawan Periode November 2025',	NULL,	NULL,	NULL,	'final',	1,	'super_admin',	'2025-11-23 13:41:16',	'2025-11-23 13:41:16',	'{\"summary\":{\"total_karyawan\":3},\"details\":[{\"nama\":\"bbbb\",\"bagian\":\"chef\",\"hadir\":\"0\",\"izin\":\"0\",\"sakit\":\"0\",\"alpha\":\"0\"},{\"nama\":\"Zikri\",\"bagian\":\"tukang_masak\",\"hadir\":\"1\",\"izin\":\"0\",\"sakit\":\"0\",\"alpha\":\"0\"},{\"nama\":\"aya\",\"bagian\":\"pengantar\",\"hadir\":\"0\",\"izin\":\"0\",\"sakit\":\"0\",\"alpha\":\"0\"}]}'),
(9,	'LAP-KES-20251123144122',	'bulanan',	'keseluruhan',	NULL,	NULL,	'2025-11-01',	'2025-11-30',	'Laporan Keseluruhan Periode November 2025',	NULL,	NULL,	NULL,	'final',	1,	'super_admin',	'2025-11-23 13:41:22',	'2025-11-23 13:41:22',	'[]');

DROP TABLE IF EXISTS `tbl_admin_log`;
CREATE TABLE `tbl_admin_log` (
  `id_log` int NOT NULL AUTO_INCREMENT,
  `id_super_admin` int NOT NULL,
  `aktivitas` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `tabel_target` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_target` int DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `ip_address` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `idx_admin` (`id_super_admin`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `tbl_admin_log_ibfk_1` FOREIGN KEY (`id_super_admin`) REFERENCES `tbl_super_admin` (`id_super_admin`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `tbl_log_aktivitas`;
CREATE TABLE `tbl_log_aktivitas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `user_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `user_email` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `activity` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `tbl_pengaduan`;
CREATE TABLE `tbl_pengaduan` (
  `id_pengaduan` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `tipe_user` enum('karyawan','pengelola') COLLATE utf8mb4_general_ci NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `isi` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('pending','proses','selesai') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `tanggapan` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pengaduan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- NOTE: Stored Procedures and Views are SKIPPED
-- Wasmer may not support stored procedures or they require special privileges
-- If you need stored procedures, you'll need to create them manually in Wasmer's Adminer interface
-- or contact Wasmer support for assistance

-- 2025-11-24 Fixed by AI Assistant
