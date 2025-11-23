-- MBG System Database Backup
-- Backup Date: 2025-11-23 14:56:24
-- Database: db_mbg

SET FOREIGN_KEY_CHECKS=0;

-- Structure for tbl_absensi (BASE TABLE)
DROP TABLE IF EXISTS `tbl_absensi`;
CREATE TABLE `tbl_absensi` (
  `id_absensi` int(11) NOT NULL AUTO_INCREMENT,
  `id_karyawan` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `total_jam_kerja` decimal(5,2) DEFAULT NULL,
  `status_kehadiran` enum('hadir','izin','sakit','alpha') DEFAULT 'hadir',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_absensi`),
  KEY `id_karyawan` (`id_karyawan`),
  CONSTRAINT `tbl_absensi_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `tbl_karyawan` (`id_karyawan`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table tbl_absensi
INSERT INTO `tbl_absensi` VALUES('1','3','2025-11-23','00:08:21','00:08:32','0.00','hadir',NULL,'2025-11-23 06:08:21','2025-11-23 06:08:32');

-- Structure for tbl_admin_log (BASE TABLE)
DROP TABLE IF EXISTS `tbl_admin_log`;
CREATE TABLE `tbl_admin_log` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_super_admin` int(11) NOT NULL,
  `aktivitas` varchar(255) NOT NULL,
  `tabel_target` varchar(100) DEFAULT NULL,
  `id_target` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_log`),
  KEY `idx_admin` (`id_super_admin`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `tbl_admin_log_ibfk_1` FOREIGN KEY (`id_super_admin`) REFERENCES `tbl_super_admin` (`id_super_admin`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Structure for tbl_bahan_baku (BASE TABLE)
DROP TABLE IF EXISTS `tbl_bahan_baku`;
CREATE TABLE `tbl_bahan_baku` (
  `id_bahan` int(11) NOT NULL AUTO_INCREMENT,
  `nama_bahan` varchar(100) NOT NULL,
  `satuan` varchar(50) NOT NULL,
  `stok_saat_ini` decimal(10,2) DEFAULT 0.00,
  `harga_per_satuan` decimal(15,2) DEFAULT NULL,
  `stok_minimum` int(11) DEFAULT 10,
  `status` enum('tersedia','habis','discontinued') DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_bahan`),
  KEY `idx_nama` (`nama_bahan`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table tbl_bahan_baku
INSERT INTO `tbl_bahan_baku` VALUES('1','telor','kg','20.00',NULL,'10','tersedia','2025-11-23 02:06:33','2025-11-23 02:32:38');
INSERT INTO `tbl_bahan_baku` VALUES('2','beras','karung','100.00',NULL,'10','tersedia','2025-11-23 02:06:33','2025-11-23 02:32:38');

-- Structure for tbl_dapur (BASE TABLE)
DROP TABLE IF EXISTS `tbl_dapur`;
CREATE TABLE `tbl_dapur` (
  `id_dapur` int(11) NOT NULL AUTO_INCREMENT,
  `id_pengelola` int(11) NOT NULL,
  `nama_dapur` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `kapasitas_produksi` int(11) DEFAULT NULL,
  `jumlah_karyawan` int(11) DEFAULT 0,
  `status` enum('aktif','nonaktif','maintenance') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_dapur`),
  KEY `idx_pengelola` (`id_pengelola`),
  KEY `idx_status` (`status`),
  CONSTRAINT `tbl_dapur_ibfk_1` FOREIGN KEY (`id_pengelola`) REFERENCES `tbl_pengelola_dapur` (`id_pengelola`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table tbl_dapur
INSERT INTO `tbl_dapur` VALUES('1','1','dapur','kudus','3000','3','aktif','2025-11-21 14:06:46','2025-11-23 16:42:22');

-- Structure for tbl_detail_pembelanjaan (BASE TABLE)
DROP TABLE IF EXISTS `tbl_detail_pembelanjaan`;
CREATE TABLE `tbl_detail_pembelanjaan` (
  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_pembelanjaan` int(11) NOT NULL,
  `id_bahan` int(11) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `harga_satuan` decimal(15,2) DEFAULT NULL,
  `subtotal` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_detail`),
  KEY `idx_pembelanjaan` (`id_pembelanjaan`),
  KEY `idx_bahan` (`id_bahan`),
  CONSTRAINT `tbl_detail_pembelanjaan_ibfk_1` FOREIGN KEY (`id_pembelanjaan`) REFERENCES `tbl_pembelanjaan` (`id_pembelanjaan`) ON DELETE CASCADE,
  CONSTRAINT `tbl_detail_pembelanjaan_ibfk_2` FOREIGN KEY (`id_bahan`) REFERENCES `tbl_bahan_baku` (`id_bahan`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table tbl_detail_pembelanjaan
INSERT INTO `tbl_detail_pembelanjaan` VALUES('1','1','1','10.00','kg','30000.00','300000.00','2025-11-23 02:06:33');
INSERT INTO `tbl_detail_pembelanjaan` VALUES('2','1','2','50.00','karung','300000.00','15000000.00','2025-11-23 02:06:33');

-- Structure for tbl_dokumentasi_karyawan (BASE TABLE)
DROP TABLE IF EXISTS `tbl_dokumentasi_karyawan`;
CREATE TABLE `tbl_dokumentasi_karyawan` (
  `id_dokumentasi` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal_dokumentasi` date NOT NULL,
  `aktivitas` text NOT NULL,
  `foto_dokumentasi` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_dokumentasi`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table tbl_dokumentasi_karyawan
INSERT INTO `tbl_dokumentasi_karyawan` VALUES('2','2025-11-22','[ksdcm] sedang ','69221a9eb9d4a.png','2025-11-23 03:18:38');
INSERT INTO `tbl_dokumentasi_karyawan` VALUES('3','2025-11-23','[Tukang Masak] memasak','6922c0f3c5f4f.jpg','2025-11-23 15:08:19');

-- Structure for tbl_karyawan (BASE TABLE)
DROP TABLE IF EXISTS `tbl_karyawan`;
CREATE TABLE `tbl_karyawan` (
  `id_karyawan` int(11) NOT NULL AUTO_INCREMENT,
  `id_role` int(11) DEFAULT 3,
  `id_pengelola` int(11) NOT NULL,
  `id_dapur` int(11) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `bagian` varchar(100) DEFAULT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `tanggal_bergabung` date DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif','cuti') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `hari_libur` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_karyawan`),
  UNIQUE KEY `email` (`email`),
  KEY `id_role` (`id_role`),
  KEY `idx_pengelola` (`id_pengelola`),
  KEY `idx_dapur` (`id_dapur`),
  KEY `idx_status` (`status`),
  CONSTRAINT `tbl_karyawan_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `tbl_role` (`id_role`),
  CONSTRAINT `tbl_karyawan_ibfk_2` FOREIGN KEY (`id_pengelola`) REFERENCES `tbl_pengelola_dapur` (`id_pengelola`) ON DELETE CASCADE,
  CONSTRAINT `tbl_karyawan_ibfk_3` FOREIGN KEY (`id_dapur`) REFERENCES `tbl_dapur` (`id_dapur`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table tbl_karyawan
INSERT INTO `tbl_karyawan` VALUES('1','3','1','1','bbbb','chef',NULL,NULL,'Muhammadzikrialfadani01@gmail.com','123456','','kudus','2026-01-01',NULL,'aktif','2025-11-21 21:16:24','2025-11-21 21:16:49',NULL);
INSERT INTO `tbl_karyawan` VALUES('3','3','1','1','Zikri','tukang_masak','06:40:00','06:39:00','mrzkr1019@gmail.com','010101','234567','kudus',NULL,NULL,'aktif','2025-11-22 23:33:15','2025-11-23 14:37:45','Minggu');
INSERT INTO `tbl_karyawan` VALUES('4','3','1','1','aya','pengantar','11:00:00','13:00:00','m@exampel.com','101010','','kudus',NULL,NULL,'aktif','2025-11-23 16:42:22','2025-11-23 16:51:14','Sabtu,Minggu');

-- Structure for tbl_laporan (BASE TABLE)
DROP TABLE IF EXISTS `tbl_laporan`;
CREATE TABLE `tbl_laporan` (
  `id_laporan` int(11) NOT NULL AUTO_INCREMENT,
  `kode_laporan` varchar(50) NOT NULL,
  `tipe_laporan` enum('harian','mingguan','bulanan','custom') NOT NULL,
  `kategori_laporan` enum('produksi','keuangan','stok','karyawan','keseluruhan') NOT NULL,
  `id_pengelola` int(11) DEFAULT NULL,
  `id_dapur` int(11) DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `judul_laporan` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `data_laporan` longtext DEFAULT NULL COMMENT 'JSON format',
  `file_pdf` varchar(255) DEFAULT NULL,
  `status_laporan` enum('draft','final','approved') DEFAULT 'draft',
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_oleh_tipe` enum('super_admin','pengelola') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `konten_laporan` longtext DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table tbl_laporan
INSERT INTO `tbl_laporan` VALUES('1','LAP-KEU-20251123143725','bulanan','keuangan',NULL,NULL,'2025-11-01','2025-11-30','Laporan Keuangan Periode November 2025',NULL,NULL,NULL,'final','1','super_admin','2025-11-23 20:37:25','2025-11-23 20:37:25','{\"summary\":{\"total_pengeluaran\":15300000},\"details\":[{\"id_pembelanjaan\":\"1\",\"id_dapur\":\"1\",\"id_pengelola\":\"1\",\"kode_pembelanjaan\":\"PB202511231367\",\"no_nota_fisik\":\"\",\"tanggal_pembelian\":\"2025-11-22\",\"supplier\":\"pasar\",\"total_pembelian\":\"15300000.00\",\"metode_pembayaran\":\"tunai\",\"status_pembayaran\":\"lunas\",\"status\":\"selesai\",\"keterangan\":\"\",\"bukti_pembelian\":\"69220fd6e9c1a.jpeg\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\",\"nama_dapur\":\"dapur\"}]}');
INSERT INTO `tbl_laporan` VALUES('2','LAP-STO-20251123143747','bulanan','stok',NULL,NULL,'2025-11-01','2025-11-30','Laporan Stok Periode November 2025',NULL,NULL,NULL,'final','1','super_admin','2025-11-23 20:37:47','2025-11-23 20:37:47','{\"summary\":{\"total_item\":2},\"details\":[{\"id_bahan\":\"2\",\"nama_bahan\":\"beras\",\"satuan\":\"karung\",\"stok_saat_ini\":\"100.00\",\"harga_per_satuan\":null,\"stok_minimum\":\"10\",\"status\":\"tersedia\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\"},{\"id_bahan\":\"1\",\"nama_bahan\":\"telor\",\"satuan\":\"kg\",\"stok_saat_ini\":\"20.00\",\"harga_per_satuan\":null,\"stok_minimum\":\"10\",\"status\":\"tersedia\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\"}]}');
INSERT INTO `tbl_laporan` VALUES('3','LAP-KES-20251123143803','bulanan','keseluruhan',NULL,NULL,'2025-11-01','2025-11-30','Laporan Keseluruhan Periode November 2025',NULL,NULL,NULL,'final','1','super_admin','2025-11-23 20:38:03','2025-11-23 20:38:03','[]');
INSERT INTO `tbl_laporan` VALUES('4','LAP-PRO-20251123144022','bulanan','produksi',NULL,NULL,'2025-11-01','2025-11-30','Laporan Produksi Periode November 2025',NULL,NULL,NULL,'final','1','super_admin','2025-11-23 20:40:22','2025-11-23 20:40:22','{\"summary\":{\"total_menu\":1},\"details\":[{\"id_menu\":\"1\",\"id_pengelola\":\"1\",\"nama_menu\":\"Nasi kuning\",\"jumlah_porsi\":\"0\",\"deskripsi\":\"susu\\r\\nbuah\\r\\nbu\\r\\nbukj\\r\\nyhjn\\r\\n\",\"foto_menu\":\"6921fd37a9a9a.jpeg\",\"status\":\"aktif\",\"created_at\":\"2025-11-23 01:13:11\",\"updated_at\":\"2025-11-23 01:49:08\",\"tanggal_menu\":\"2025-11-22\",\"status_pengantaran\":\"belum_diantar\",\"nama_dapur\":\"dapur\"}]}');
INSERT INTO `tbl_laporan` VALUES('5','LAP-PRO-20251123144045','bulanan','produksi',NULL,NULL,'2025-11-01','2025-11-30','Laporan Produksi Periode November 2025',NULL,NULL,NULL,'final','1','super_admin','2025-11-23 20:40:45','2025-11-23 20:40:45','{\"summary\":{\"total_menu\":1},\"details\":[{\"id_menu\":\"1\",\"id_pengelola\":\"1\",\"nama_menu\":\"Nasi kuning\",\"jumlah_porsi\":\"0\",\"deskripsi\":\"susu\\r\\nbuah\\r\\nbu\\r\\nbukj\\r\\nyhjn\\r\\n\",\"foto_menu\":\"6921fd37a9a9a.jpeg\",\"status\":\"aktif\",\"created_at\":\"2025-11-23 01:13:11\",\"updated_at\":\"2025-11-23 01:49:08\",\"tanggal_menu\":\"2025-11-22\",\"status_pengantaran\":\"belum_diantar\",\"nama_dapur\":\"dapur\"}]}');
INSERT INTO `tbl_laporan` VALUES('6','LAP-KEU-20251123144101','bulanan','keuangan',NULL,NULL,'2025-11-01','2025-11-30','Laporan Keuangan Periode November 2025',NULL,NULL,NULL,'final','1','super_admin','2025-11-23 20:41:01','2025-11-23 20:41:01','{\"summary\":{\"total_pengeluaran\":15300000},\"details\":[{\"id_pembelanjaan\":\"1\",\"id_dapur\":\"1\",\"id_pengelola\":\"1\",\"kode_pembelanjaan\":\"PB202511231367\",\"no_nota_fisik\":\"\",\"tanggal_pembelian\":\"2025-11-22\",\"supplier\":\"pasar\",\"total_pembelian\":\"15300000.00\",\"metode_pembayaran\":\"tunai\",\"status_pembayaran\":\"lunas\",\"status\":\"selesai\",\"keterangan\":\"\",\"bukti_pembelian\":\"69220fd6e9c1a.jpeg\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\",\"nama_dapur\":\"dapur\"}]}');
INSERT INTO `tbl_laporan` VALUES('7','LAP-STO-20251123144108','bulanan','stok',NULL,NULL,'2025-11-01','2025-11-30','Laporan Stok Periode November 2025',NULL,NULL,NULL,'final','1','super_admin','2025-11-23 20:41:08','2025-11-23 20:41:08','{\"summary\":{\"total_item\":2},\"details\":[{\"id_bahan\":\"2\",\"nama_bahan\":\"beras\",\"satuan\":\"karung\",\"stok_saat_ini\":\"100.00\",\"harga_per_satuan\":null,\"stok_minimum\":\"10\",\"status\":\"tersedia\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\"},{\"id_bahan\":\"1\",\"nama_bahan\":\"telor\",\"satuan\":\"kg\",\"stok_saat_ini\":\"20.00\",\"harga_per_satuan\":null,\"stok_minimum\":\"10\",\"status\":\"tersedia\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\"}]}');
INSERT INTO `tbl_laporan` VALUES('8','LAP-KAR-20251123144116','bulanan','karyawan',NULL,NULL,'2025-11-01','2025-11-30','Laporan Karyawan Periode November 2025',NULL,NULL,NULL,'final','1','super_admin','2025-11-23 20:41:16','2025-11-23 20:41:16','{\"summary\":{\"total_karyawan\":3},\"details\":[{\"nama\":\"bbbb\",\"bagian\":\"chef\",\"hadir\":\"0\",\"izin\":\"0\",\"sakit\":\"0\",\"alpha\":\"0\"},{\"nama\":\"Zikri\",\"bagian\":\"tukang_masak\",\"hadir\":\"1\",\"izin\":\"0\",\"sakit\":\"0\",\"alpha\":\"0\"},{\"nama\":\"aya\",\"bagian\":\"pengantar\",\"hadir\":\"0\",\"izin\":\"0\",\"sakit\":\"0\",\"alpha\":\"0\"}]}');
INSERT INTO `tbl_laporan` VALUES('9','LAP-KES-20251123144122','bulanan','keseluruhan',NULL,NULL,'2025-11-01','2025-11-30','Laporan Keseluruhan Periode November 2025',NULL,NULL,NULL,'final','1','super_admin','2025-11-23 20:41:22','2025-11-23 20:41:22','[]');

-- Structure for tbl_log_aktivitas (BASE TABLE)
DROP TABLE IF EXISTS `tbl_log_aktivitas`;
CREATE TABLE `tbl_log_aktivitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(150) DEFAULT NULL,
  `user_type` varchar(50) NOT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `activity` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Structure for tbl_menu (BASE TABLE)
DROP TABLE IF EXISTS `tbl_menu`;
CREATE TABLE `tbl_menu` (
  `id_menu` int(11) NOT NULL AUTO_INCREMENT,
  `id_pengelola` int(11) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `jumlah_porsi` int(11) DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `foto_menu` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tanggal_menu` date DEFAULT curdate(),
  `status_pengantaran` enum('belum_diantar','proses','selesai') DEFAULT 'belum_diantar',
  PRIMARY KEY (`id_menu`),
  KEY `idx_pengelola` (`id_pengelola`),
  KEY `idx_status` (`status`),
  CONSTRAINT `tbl_menu_ibfk_1` FOREIGN KEY (`id_pengelola`) REFERENCES `tbl_pengelola_dapur` (`id_pengelola`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table tbl_menu
INSERT INTO `tbl_menu` VALUES('1','1','Nasi kuning','0','susu\r\nbuah\r\nbu\r\nbukj\r\nyhjn\r\n','6921fd37a9a9a.jpeg','aktif','2025-11-23 01:13:11','2025-11-23 01:49:08','2025-11-22','belum_diantar');

-- Structure for tbl_pembelanjaan (BASE TABLE)
DROP TABLE IF EXISTS `tbl_pembelanjaan`;
CREATE TABLE `tbl_pembelanjaan` (
  `id_pembelanjaan` int(11) NOT NULL AUTO_INCREMENT,
  `id_dapur` int(11) NOT NULL,
  `id_pengelola` int(11) NOT NULL,
  `kode_pembelanjaan` varchar(50) DEFAULT NULL,
  `no_nota_fisik` varchar(50) DEFAULT NULL,
  `tanggal_pembelian` date NOT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `total_pembelian` decimal(15,2) DEFAULT NULL,
  `metode_pembayaran` enum('tunai','transfer','kredit') DEFAULT 'tunai',
  `status_pembayaran` enum('lunas','belum_lunas','cicilan') DEFAULT 'lunas',
  `status` enum('rencana','selesai') DEFAULT 'rencana',
  `keterangan` text DEFAULT NULL,
  `bukti_pembelian` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_pembelanjaan`),
  UNIQUE KEY `kode_pembelanjaan` (`kode_pembelanjaan`),
  KEY `id_pengelola` (`id_pengelola`),
  KEY `idx_dapur` (`id_dapur`),
  KEY `idx_tanggal` (`tanggal_pembelian`),
  KEY `idx_kode` (`kode_pembelanjaan`),
  KEY `idx_status` (`status_pembayaran`),
  CONSTRAINT `tbl_pembelanjaan_ibfk_1` FOREIGN KEY (`id_dapur`) REFERENCES `tbl_dapur` (`id_dapur`) ON DELETE CASCADE,
  CONSTRAINT `tbl_pembelanjaan_ibfk_2` FOREIGN KEY (`id_pengelola`) REFERENCES `tbl_pengelola_dapur` (`id_pengelola`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table tbl_pembelanjaan
INSERT INTO `tbl_pembelanjaan` VALUES('1','1','1','PB202511231367','','2025-11-22','pasar','15300000.00','tunai','lunas','selesai','','69220fd6e9c1a.jpeg','2025-11-23 02:06:33','2025-11-23 02:32:38');

-- Structure for tbl_pengaduan (BASE TABLE)
DROP TABLE IF EXISTS `tbl_pengaduan`;
CREATE TABLE `tbl_pengaduan` (
  `id_pengaduan` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `tipe_user` enum('karyawan','pengelola') NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `status` enum('pending','proses','selesai') DEFAULT 'pending',
  `tanggapan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_pengaduan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Structure for tbl_pengelola_dapur (BASE TABLE)
DROP TABLE IF EXISTS `tbl_pengelola_dapur`;
CREATE TABLE `tbl_pengelola_dapur` (
  `id_pengelola` int(11) NOT NULL AUTO_INCREMENT,
  `id_role` int(11) DEFAULT 2,
  `nama` varchar(100) NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `verification_code` varchar(6) DEFAULT NULL,
  `verification_expires_at` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_pengelola`),
  UNIQUE KEY `email` (`email`),
  KEY `id_role` (`id_role`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  CONSTRAINT `tbl_pengelola_dapur_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `tbl_role` (`id_role`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table tbl_pengelola_dapur
INSERT INTO `tbl_pengelola_dapur` VALUES('1','2','abcd','089767890','m@xexample.com','Inc1019',NULL,'aktif',NULL,NULL,'1','2025-11-19 23:18:50','2025-11-23 06:12:39');
INSERT INTO `tbl_pengelola_dapur` VALUES('3','2','zikri',NULL,'muhammadzikrialfadani02@gmail.com','zikri123',NULL,'aktif',NULL,'2025-11-23 23:21:09','1','2025-11-23 05:21:09','2025-11-23 05:21:47');

-- Structure for tbl_produksi_harian (BASE TABLE)
DROP TABLE IF EXISTS `tbl_produksi_harian`;
CREATE TABLE `tbl_produksi_harian` (
  `id_produksi` int(11) NOT NULL AUTO_INCREMENT,
  `id_menu` int(11) NOT NULL,
  `id_karyawan` int(11) NOT NULL,
  `id_dapur` int(11) NOT NULL,
  `kode_produksi` varchar(50) DEFAULT NULL,
  `tanggal_produksi` date NOT NULL,
  `jumlah_porsi` int(11) NOT NULL,
  `status` enum('proses','selesai','gagal','pending') DEFAULT 'proses',
  `kualitas` enum('sangat_baik','baik','cukup','kurang') DEFAULT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_selesai` time DEFAULT NULL,
  `durasi_produksi` int(11) DEFAULT NULL COMMENT 'dalam menit',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_produksi`),
  UNIQUE KEY `idx_kode_produksi_unique` (`kode_produksi`),
  KEY `idx_menu` (`id_menu`),
  KEY `idx_karyawan` (`id_karyawan`),
  KEY `idx_dapur` (`id_dapur`),
  KEY `idx_tanggal` (`tanggal_produksi`),
  KEY `idx_kode` (`kode_produksi`),
  KEY `idx_status` (`status`),
  CONSTRAINT `tbl_produksi_harian_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `tbl_menu` (`id_menu`) ON DELETE CASCADE,
  CONSTRAINT `tbl_produksi_harian_ibfk_2` FOREIGN KEY (`id_karyawan`) REFERENCES `tbl_karyawan` (`id_karyawan`) ON DELETE CASCADE,
  CONSTRAINT `tbl_produksi_harian_ibfk_3` FOREIGN KEY (`id_dapur`) REFERENCES `tbl_dapur` (`id_dapur`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Structure for tbl_resep_menu (BASE TABLE)
DROP TABLE IF EXISTS `tbl_resep_menu`;
CREATE TABLE `tbl_resep_menu` (
  `id_resep` int(11) NOT NULL AUTO_INCREMENT,
  `id_menu` int(11) NOT NULL,
  `id_bahan` int(11) NOT NULL,
  `jumlah_bahan` decimal(10,2) NOT NULL,
  `satuan` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_resep`),
  KEY `idx_menu` (`id_menu`),
  KEY `idx_bahan` (`id_bahan`),
  CONSTRAINT `tbl_resep_menu_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `tbl_menu` (`id_menu`) ON DELETE CASCADE,
  CONSTRAINT `tbl_resep_menu_ibfk_2` FOREIGN KEY (`id_bahan`) REFERENCES `tbl_bahan_baku` (`id_bahan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Structure for tbl_riwayat_stok (BASE TABLE)
DROP TABLE IF EXISTS `tbl_riwayat_stok`;
CREATE TABLE `tbl_riwayat_stok` (
  `id_riwayat` int(11) NOT NULL AUTO_INCREMENT,
  `id_bahan` int(11) NOT NULL,
  `id_pembelanjaan` int(11) DEFAULT NULL,
  `tipe_transaksi` enum('masuk','keluar','adjustment') NOT NULL,
  `jumlah_stok` decimal(10,2) NOT NULL,
  `stok_sebelum` decimal(10,2) DEFAULT NULL,
  `stok_sesudah` decimal(10,2) DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `tanggal_transaksi` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_riwayat`),
  KEY `id_pembelanjaan` (`id_pembelanjaan`),
  KEY `idx_bahan` (`id_bahan`),
  KEY `idx_tanggal` (`tanggal_transaksi`),
  KEY `idx_tipe` (`tipe_transaksi`),
  CONSTRAINT `tbl_riwayat_stok_ibfk_1` FOREIGN KEY (`id_bahan`) REFERENCES `tbl_bahan_baku` (`id_bahan`) ON DELETE CASCADE,
  CONSTRAINT `tbl_riwayat_stok_ibfk_2` FOREIGN KEY (`id_pembelanjaan`) REFERENCES `tbl_pembelanjaan` (`id_pembelanjaan`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table tbl_riwayat_stok
INSERT INTO `tbl_riwayat_stok` VALUES('7','1','1','masuk','10.00','0.00','10.00','kg','2025-11-23','Pembelanjaan Selesai','2025-11-23 02:29:41');
INSERT INTO `tbl_riwayat_stok` VALUES('8','2','1','masuk','50.00','0.00','50.00','karung','2025-11-23','Pembelanjaan Selesai','2025-11-23 02:29:41');
INSERT INTO `tbl_riwayat_stok` VALUES('9','1','1','masuk','10.00','10.00','20.00','kg','2025-11-23','Pembelanjaan Selesai','2025-11-23 02:32:38');
INSERT INTO `tbl_riwayat_stok` VALUES('10','2','1','masuk','50.00','50.00','100.00','karung','2025-11-23','Pembelanjaan Selesai','2025-11-23 02:32:38');

-- Structure for tbl_role (BASE TABLE)
DROP TABLE IF EXISTS `tbl_role`;
CREATE TABLE `tbl_role` (
  `id_role` int(11) NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `nama_role` (`nama_role`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table tbl_role
INSERT INTO `tbl_role` VALUES('1','Super Admin','Administrator tertinggi dengan akses penuh ke seluruh sistem','2025-11-19 11:11:21','2025-11-19 11:11:21');
INSERT INTO `tbl_role` VALUES('2','Pengelola Dapur','Mengelola dapur, karyawan, menu, dan operasional','2025-11-19 11:11:21','2025-11-19 11:11:21');
INSERT INTO `tbl_role` VALUES('3','Karyawan','Staff yang bekerja di dapur dan melakukan produksi','2025-11-19 11:11:21','2025-11-19 11:11:21');

-- Structure for tbl_super_admin (BASE TABLE)
DROP TABLE IF EXISTS `tbl_super_admin`;
CREATE TABLE `tbl_super_admin` (
  `id_super_admin` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_super_admin`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table tbl_super_admin
INSERT INTO `tbl_super_admin` VALUES('1','admin','admin123','Super Administrator','admin@mbg.com','081234567890',NULL,'aktif','2025-11-23 20:11:05','2025-11-19 11:11:21','2025-11-23 20:11:05');

-- Structure for vw_dashboard_super_admin (VIEW)
DROP VIEW IF EXISTS `vw_dashboard_super_admin`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_dashboard_super_admin` AS select (select count(0) from `tbl_pengelola_dapur` where `tbl_pengelola_dapur`.`status` = 'aktif') AS `total_pengelola`,(select count(0) from `tbl_dapur` where `tbl_dapur`.`status` = 'aktif') AS `total_dapur`,(select count(0) from `tbl_karyawan` where `tbl_karyawan`.`status` = 'aktif') AS `total_karyawan`,(select count(0) from `tbl_menu` where `tbl_menu`.`status` = 'aktif') AS `total_menu`,(select count(0) from `tbl_produksi_harian` where `tbl_produksi_harian`.`tanggal_produksi` = curdate()) AS `produksi_hari_ini`,(select count(0) from `tbl_bahan_baku` where `tbl_bahan_baku`.`status` = 'tersedia') AS `total_bahan_aktif`,(select coalesce(sum(`tbl_pembelanjaan`.`total_pembelian`),0) from `tbl_pembelanjaan` where `tbl_pembelanjaan`.`tanggal_pembelian` = curdate()) AS `pembelanjaan_hari_ini`;

-- Data dump skipped for VIEW vw_dashboard_super_admin

-- Structure for vw_laporan_karyawan_bulanan (VIEW)
DROP VIEW IF EXISTS `vw_laporan_karyawan_bulanan`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_karyawan_bulanan` AS select year(`a`.`tanggal`) AS `tahun`,month(`a`.`tanggal`) AS `bulan`,date_format(`a`.`tanggal`,'%Y-%m') AS `periode`,`k`.`id_karyawan` AS `id_karyawan`,`k`.`nama` AS `nama`,`k`.`bagian` AS `bagian`,`d`.`nama_dapur` AS `nama_dapur`,count(`a`.`id_absensi`) AS `total_kehadiran`,sum(`a`.`total_jam_kerja`) AS `total_jam_kerja`,avg(`a`.`total_jam_kerja`) AS `rata_rata_jam_kerja`,sum(case when `a`.`status_kehadiran` = 'hadir' then 1 else 0 end) AS `hadir`,sum(case when `a`.`status_kehadiran` = 'izin' then 1 else 0 end) AS `izin`,sum(case when `a`.`status_kehadiran` = 'sakit' then 1 else 0 end) AS `sakit`,sum(case when `a`.`status_kehadiran` = 'alpha' then 1 else 0 end) AS `alpha`,(select count(0) from `tbl_produksi_harian` where `tbl_produksi_harian`.`id_karyawan` = `k`.`id_karyawan` and date_format(`tbl_produksi_harian`.`tanggal_produksi`,'%Y-%m') = date_format(`a`.`tanggal`,'%Y-%m')) AS `total_produksi` from ((`tbl_absensi` `a` join `tbl_karyawan` `k` on(`a`.`id_karyawan` = `k`.`id_karyawan`)) left join `tbl_dapur` `d` on(`k`.`id_dapur` = `d`.`id_dapur`)) group by year(`a`.`tanggal`),month(`a`.`tanggal`),`k`.`id_karyawan`;

-- Data dump skipped for VIEW vw_laporan_karyawan_bulanan

-- Structure for vw_laporan_karyawan_harian (VIEW)
DROP VIEW IF EXISTS `vw_laporan_karyawan_harian`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_karyawan_harian` AS select cast(`a`.`tanggal` as date) AS `tanggal`,`k`.`id_karyawan` AS `id_karyawan`,`k`.`nama` AS `nama`,`k`.`bagian` AS `bagian`,`d`.`nama_dapur` AS `nama_dapur`,count(`a`.`id_absensi`) AS `total_kehadiran`,sum(`a`.`total_jam_kerja`) AS `total_jam_kerja`,(select count(0) from `tbl_produksi_harian` where `tbl_produksi_harian`.`id_karyawan` = `k`.`id_karyawan` and cast(`tbl_produksi_harian`.`tanggal_produksi` as date) = cast(`a`.`tanggal` as date)) AS `total_produksi` from ((`tbl_absensi` `a` join `tbl_karyawan` `k` on(`a`.`id_karyawan` = `k`.`id_karyawan`)) left join `tbl_dapur` `d` on(`k`.`id_dapur` = `d`.`id_dapur`)) group by cast(`a`.`tanggal` as date),`k`.`id_karyawan`;

-- Data dump skipped for VIEW vw_laporan_karyawan_harian

-- Structure for vw_laporan_karyawan_mingguan (VIEW)
DROP VIEW IF EXISTS `vw_laporan_karyawan_mingguan`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_karyawan_mingguan` AS select yearweek(`a`.`tanggal`,1) AS `minggu_tahun`,cast(`a`.`tanggal` - interval weekday(`a`.`tanggal`) day as date) AS `tanggal_mulai_minggu`,`k`.`id_karyawan` AS `id_karyawan`,`k`.`nama` AS `nama`,`k`.`bagian` AS `bagian`,`d`.`nama_dapur` AS `nama_dapur`,count(`a`.`id_absensi`) AS `total_kehadiran`,sum(`a`.`total_jam_kerja`) AS `total_jam_kerja`,avg(`a`.`total_jam_kerja`) AS `rata_rata_jam_kerja`,(select count(0) from `tbl_produksi_harian` where `tbl_produksi_harian`.`id_karyawan` = `k`.`id_karyawan` and yearweek(`tbl_produksi_harian`.`tanggal_produksi`,1) = yearweek(`a`.`tanggal`,1)) AS `total_produksi` from ((`tbl_absensi` `a` join `tbl_karyawan` `k` on(`a`.`id_karyawan` = `k`.`id_karyawan`)) left join `tbl_dapur` `d` on(`k`.`id_dapur` = `d`.`id_dapur`)) group by yearweek(`a`.`tanggal`,1),`k`.`id_karyawan`;

-- Data dump skipped for VIEW vw_laporan_karyawan_mingguan

-- Structure for vw_laporan_keseluruhan_harian (VIEW)
DROP VIEW IF EXISTS `vw_laporan_keseluruhan_harian`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_keseluruhan_harian` AS select cast(curdate() as date) AS `tanggal`,(select count(0) from `tbl_produksi_harian` where cast(`tbl_produksi_harian`.`tanggal_produksi` as date) = curdate()) AS `total_produksi`,(select sum(`tbl_produksi_harian`.`jumlah_porsi`) from `tbl_produksi_harian` where cast(`tbl_produksi_harian`.`tanggal_produksi` as date) = curdate()) AS `total_porsi`,(select count(0) from `tbl_absensi` where `tbl_absensi`.`tanggal` = curdate()) AS `total_kehadiran`,(select coalesce(sum(`tbl_pembelanjaan`.`total_pembelian`),0) from `tbl_pembelanjaan` where cast(`tbl_pembelanjaan`.`tanggal_pembelian` as date) = curdate()) AS `total_pengeluaran`,(select count(0) from `tbl_riwayat_stok` where cast(`tbl_riwayat_stok`.`tanggal_transaksi` as date) = curdate() and `tbl_riwayat_stok`.`tipe_transaksi` = 'masuk') AS `transaksi_stok_masuk`,(select count(0) from `tbl_riwayat_stok` where cast(`tbl_riwayat_stok`.`tanggal_transaksi` as date) = curdate() and `tbl_riwayat_stok`.`tipe_transaksi` = 'keluar') AS `transaksi_stok_keluar`;

-- Data dump skipped for VIEW vw_laporan_keseluruhan_harian

-- Structure for vw_laporan_keuangan_bulanan (VIEW)
DROP VIEW IF EXISTS `vw_laporan_keuangan_bulanan`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_keuangan_bulanan` AS select year(`p`.`tanggal_pembelian`) AS `tahun`,month(`p`.`tanggal_pembelian`) AS `bulan`,date_format(`p`.`tanggal_pembelian`,'%Y-%m') AS `periode`,`d`.`nama_dapur` AS `nama_dapur`,`d`.`id_dapur` AS `id_dapur`,count(`p`.`id_pembelanjaan`) AS `total_transaksi`,sum(`p`.`total_pembelian`) AS `total_pengeluaran`,avg(`p`.`total_pembelian`) AS `rata_rata_transaksi`,sum(case when `p`.`status_pembayaran` = 'lunas' then `p`.`total_pembelian` else 0 end) AS `total_lunas`,sum(case when `p`.`status_pembayaran` = 'belum_lunas' then `p`.`total_pembelian` else 0 end) AS `total_belum_lunas` from (`tbl_pembelanjaan` `p` join `tbl_dapur` `d` on(`p`.`id_dapur` = `d`.`id_dapur`)) group by year(`p`.`tanggal_pembelian`),month(`p`.`tanggal_pembelian`),`d`.`id_dapur`;

-- Data dump skipped for VIEW vw_laporan_keuangan_bulanan

-- Structure for vw_laporan_keuangan_harian (VIEW)
DROP VIEW IF EXISTS `vw_laporan_keuangan_harian`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_keuangan_harian` AS select cast(`p`.`tanggal_pembelian` as date) AS `tanggal`,`d`.`nama_dapur` AS `nama_dapur`,`d`.`id_dapur` AS `id_dapur`,count(`p`.`id_pembelanjaan`) AS `total_transaksi`,sum(`p`.`total_pembelian`) AS `total_pengeluaran`,avg(`p`.`total_pembelian`) AS `rata_rata_transaksi`,sum(case when `p`.`metode_pembayaran` = 'tunai' then `p`.`total_pembelian` else 0 end) AS `pembayaran_tunai`,sum(case when `p`.`metode_pembayaran` = 'transfer' then `p`.`total_pembelian` else 0 end) AS `pembayaran_transfer`,sum(case when `p`.`metode_pembayaran` = 'kredit' then `p`.`total_pembelian` else 0 end) AS `pembayaran_kredit` from (`tbl_pembelanjaan` `p` join `tbl_dapur` `d` on(`p`.`id_dapur` = `d`.`id_dapur`)) group by cast(`p`.`tanggal_pembelian` as date),`d`.`id_dapur`;

-- Data dump skipped for VIEW vw_laporan_keuangan_harian

-- Structure for vw_laporan_keuangan_mingguan (VIEW)
DROP VIEW IF EXISTS `vw_laporan_keuangan_mingguan`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_keuangan_mingguan` AS select yearweek(`p`.`tanggal_pembelian`,1) AS `minggu_tahun`,cast(`p`.`tanggal_pembelian` - interval weekday(`p`.`tanggal_pembelian`) day as date) AS `tanggal_mulai_minggu`,cast(`p`.`tanggal_pembelian` - interval weekday(`p`.`tanggal_pembelian`) day + interval 6 day as date) AS `tanggal_akhir_minggu`,`d`.`nama_dapur` AS `nama_dapur`,`d`.`id_dapur` AS `id_dapur`,count(`p`.`id_pembelanjaan`) AS `total_transaksi`,sum(`p`.`total_pembelian`) AS `total_pengeluaran`,avg(`p`.`total_pembelian`) AS `rata_rata_transaksi` from (`tbl_pembelanjaan` `p` join `tbl_dapur` `d` on(`p`.`id_dapur` = `d`.`id_dapur`)) group by yearweek(`p`.`tanggal_pembelian`,1),`d`.`id_dapur`;

-- Data dump skipped for VIEW vw_laporan_keuangan_mingguan

-- Structure for vw_laporan_produksi_bulanan (VIEW)
DROP VIEW IF EXISTS `vw_laporan_produksi_bulanan`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_produksi_bulanan` AS select year(`ph`.`tanggal_produksi`) AS `tahun`,month(`ph`.`tanggal_produksi`) AS `bulan`,date_format(`ph`.`tanggal_produksi`,'%Y-%m') AS `periode`,`d`.`nama_dapur` AS `nama_dapur`,`d`.`id_dapur` AS `id_dapur`,count(`ph`.`id_produksi`) AS `total_produksi`,sum(`ph`.`jumlah_porsi`) AS `total_porsi`,sum(case when `ph`.`status` = 'selesai' then 1 else 0 end) AS `produksi_berhasil`,sum(case when `ph`.`status` = 'gagal' then 1 else 0 end) AS `produksi_gagal`,avg(`ph`.`durasi_produksi`) AS `rata_rata_durasi` from (`tbl_produksi_harian` `ph` join `tbl_dapur` `d` on(`ph`.`id_dapur` = `d`.`id_dapur`)) group by year(`ph`.`tanggal_produksi`),month(`ph`.`tanggal_produksi`),`d`.`id_dapur`;

-- Data dump skipped for VIEW vw_laporan_produksi_bulanan

-- Structure for vw_laporan_produksi_harian (VIEW)
DROP VIEW IF EXISTS `vw_laporan_produksi_harian`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_produksi_harian` AS select cast(`ph`.`tanggal_produksi` as date) AS `tanggal`,`d`.`nama_dapur` AS `nama_dapur`,`d`.`id_dapur` AS `id_dapur`,count(`ph`.`id_produksi`) AS `total_produksi`,sum(`ph`.`jumlah_porsi`) AS `total_porsi`,sum(case when `ph`.`status` = 'selesai' then 1 else 0 end) AS `produksi_berhasil`,sum(case when `ph`.`status` = 'gagal' then 1 else 0 end) AS `produksi_gagal`,avg(`ph`.`durasi_produksi`) AS `rata_rata_durasi`,group_concat(distinct `m`.`nama_menu` separator ', ') AS `menu_diproduksi` from ((`tbl_produksi_harian` `ph` join `tbl_dapur` `d` on(`ph`.`id_dapur` = `d`.`id_dapur`)) join `tbl_menu` `m` on(`ph`.`id_menu` = `m`.`id_menu`)) group by cast(`ph`.`tanggal_produksi` as date),`d`.`id_dapur`;

-- Data dump skipped for VIEW vw_laporan_produksi_harian

-- Structure for vw_laporan_produksi_mingguan (VIEW)
DROP VIEW IF EXISTS `vw_laporan_produksi_mingguan`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_produksi_mingguan` AS select yearweek(`ph`.`tanggal_produksi`,1) AS `minggu_tahun`,cast(`ph`.`tanggal_produksi` - interval weekday(`ph`.`tanggal_produksi`) day as date) AS `tanggal_mulai_minggu`,cast(`ph`.`tanggal_produksi` - interval weekday(`ph`.`tanggal_produksi`) day + interval 6 day as date) AS `tanggal_akhir_minggu`,`d`.`nama_dapur` AS `nama_dapur`,`d`.`id_dapur` AS `id_dapur`,count(`ph`.`id_produksi`) AS `total_produksi`,sum(`ph`.`jumlah_porsi`) AS `total_porsi`,sum(case when `ph`.`status` = 'selesai' then 1 else 0 end) AS `produksi_berhasil`,sum(case when `ph`.`status` = 'gagal' then 1 else 0 end) AS `produksi_gagal`,avg(`ph`.`durasi_produksi`) AS `rata_rata_durasi` from (`tbl_produksi_harian` `ph` join `tbl_dapur` `d` on(`ph`.`id_dapur` = `d`.`id_dapur`)) group by yearweek(`ph`.`tanggal_produksi`,1),`d`.`id_dapur`;

-- Data dump skipped for VIEW vw_laporan_produksi_mingguan

-- Structure for vw_laporan_stok_harian (VIEW)
DROP VIEW IF EXISTS `vw_laporan_stok_harian`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_stok_harian` AS select cast(`rs`.`tanggal_transaksi` as date) AS `tanggal`,`bb`.`id_bahan` AS `id_bahan`,`bb`.`nama_bahan` AS `nama_bahan`,`bb`.`kategori` AS `kategori`,`bb`.`satuan` AS `satuan`,sum(case when `rs`.`tipe_transaksi` = 'masuk' then `rs`.`jumlah_stok` else 0 end) AS `stok_masuk`,sum(case when `rs`.`tipe_transaksi` = 'keluar' then `rs`.`jumlah_stok` else 0 end) AS `stok_keluar`,(select `db_mbg`.`tbl_riwayat_stok`.`stok_sesudah` from `tbl_riwayat_stok` where `db_mbg`.`tbl_riwayat_stok`.`id_bahan` = `bb`.`id_bahan` order by `db_mbg`.`tbl_riwayat_stok`.`id_riwayat` desc limit 1) AS `stok_akhir` from (`tbl_riwayat_stok` `rs` join `tbl_bahan_baku` `bb` on(`rs`.`id_bahan` = `bb`.`id_bahan`)) group by cast(`rs`.`tanggal_transaksi` as date),`bb`.`id_bahan`;

-- Data dump skipped for VIEW vw_laporan_stok_harian

-- Structure for vw_laporan_stok_mingguan (VIEW)
DROP VIEW IF EXISTS `vw_laporan_stok_mingguan`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_stok_mingguan` AS select yearweek(`rs`.`tanggal_transaksi`,1) AS `minggu_tahun`,cast(`rs`.`tanggal_transaksi` - interval weekday(`rs`.`tanggal_transaksi`) day as date) AS `tanggal_mulai_minggu`,`bb`.`id_bahan` AS `id_bahan`,`bb`.`nama_bahan` AS `nama_bahan`,`bb`.`kategori` AS `kategori`,sum(case when `rs`.`tipe_transaksi` = 'masuk' then `rs`.`jumlah_stok` else 0 end) AS `total_stok_masuk`,sum(case when `rs`.`tipe_transaksi` = 'keluar' then `rs`.`jumlah_stok` else 0 end) AS `total_stok_keluar` from (`tbl_riwayat_stok` `rs` join `tbl_bahan_baku` `bb` on(`rs`.`id_bahan` = `bb`.`id_bahan`)) group by yearweek(`rs`.`tanggal_transaksi`,1),`bb`.`id_bahan`;

-- Data dump skipped for VIEW vw_laporan_stok_mingguan

SET FOREIGN_KEY_CHECKS=1;
