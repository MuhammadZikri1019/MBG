-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 24 Nov 2025 pada 04.22
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_mbg`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_generate_laporan_keseluruhan` (IN `p_tipe_laporan` ENUM('harian','mingguan','bulanan'), IN `p_tanggal_mulai` DATE, IN `p_tanggal_akhir` DATE, IN `p_id_dapur` INT, IN `p_dibuat_oleh` INT, IN `p_dibuat_oleh_tipe` ENUM('super_admin','pengelola'))   BEGIN
    DECLARE v_kode_laporan VARCHAR(50);
    DECLARE v_judul VARCHAR(255);
    DECLARE v_data_json LONGTEXT;
    
    SET v_kode_laporan = CONCAT('LAP-ALL-', UPPER(p_tipe_laporan), '-', DATE_FORMAT(NOW(), '%Y%m%d%H%i%s'));
    SET v_judul = CONCAT('Laporan Keseluruhan ', UPPER(LEFT(p_tipe_laporan, 1)), LOWER(SUBSTRING(p_tipe_laporan, 2)), 
                         ' (', DATE_FORMAT(p_tanggal_mulai, '%d/%m/%Y'), ' - ', DATE_FORMAT(p_tanggal_akhir, '%d/%m/%Y'), ')');
    
    SELECT JSON_OBJECT(
        'produksi', JSON_OBJECT(
            'total_produksi', (SELECT COUNT(*) FROM tbl_produksi_harian WHERE tanggal_produksi BETWEEN p_tanggal_mulai AND p_tanggal_akhir AND (p_id_dapur IS NULL OR id_dapur = p_id_dapur)),
            'total_porsi', (SELECT SUM(jumlah_porsi) FROM tbl_produksi_harian WHERE tanggal_produksi BETWEEN p_tanggal_mulai AND p_tanggal_akhir AND (p_id_dapur IS NULL OR id_dapur = p_id_dapur)),
            'produksi_berhasil', (SELECT COUNT(*) FROM tbl_produksi_harian WHERE tanggal_produksi BETWEEN p_tanggal_mulai AND p_tanggal_akhir AND status = 'selesai' AND (p_id_dapur IS NULL OR id_dapur = p_id_dapur))
        ),
        'keuangan', JSON_OBJECT(
            'total_transaksi', (SELECT COUNT(*) FROM tbl_pembelanjaan WHERE tanggal_pembelian BETWEEN p_tanggal_mulai AND p_tanggal_akhir AND (p_id_dapur IS NULL OR id_dapur = p_id_dapur)),
            'total_pengeluaran', (SELECT COALESCE(SUM(total_pembelian), 0) FROM tbl_pembelanjaan WHERE tanggal_pembelian BETWEEN p_tanggal_mulai AND p_tanggal_akhir AND (p_id_dapur IS NULL OR id_dapur = p_id_dapur))
        ),
        'karyawan', JSON_OBJECT(
            'total_kehadiran', (SELECT COUNT(*) FROM tbl_absensi WHERE tanggal BETWEEN p_tanggal_mulai AND p_tanggal_akhir),
            'rata_rata_jam_kerja', (SELECT AVG(total_jam_kerja) FROM tbl_absensi WHERE tanggal BETWEEN p_tanggal_mulai AND p_tanggal_akhir)
        ),
        'stok', JSON_OBJECT(
            'transaksi_masuk', (SELECT COUNT(*) FROM tbl_riwayat_stok WHERE tanggal_transaksi BETWEEN p_tanggal_mulai AND p_tanggal_akhir AND tipe_transaksi = 'masuk'),
            'transaksi_keluar', (SELECT COUNT(*) FROM tbl_riwayat_stok WHERE tanggal_transaksi BETWEEN p_tanggal_mulai AND p_tanggal_akhir AND tipe_transaksi = 'keluar')
        )
    ) INTO v_data_json;
    
    INSERT INTO tbl_laporan (
        kode_laporan, tipe_laporan, kategori_laporan, id_dapur,
        tanggal_mulai, tanggal_akhir, judul_laporan, data_laporan,
        dibuat_oleh, dibuat_oleh_tipe, status_laporan
    ) VALUES (
        v_kode_laporan, p_tipe_laporan, 'keseluruhan', p_id_dapur,
        p_tanggal_mulai, p_tanggal_akhir, v_judul, v_data_json,
        p_dibuat_oleh, p_dibuat_oleh_tipe, 'final'
    );
    
    SELECT v_kode_laporan AS kode_laporan, 'Laporan berhasil dibuat' AS message;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_generate_laporan_produksi` (IN `p_tipe_laporan` ENUM('harian','mingguan','bulanan'), IN `p_tanggal_mulai` DATE, IN `p_tanggal_akhir` DATE, IN `p_id_dapur` INT, IN `p_dibuat_oleh` INT, IN `p_dibuat_oleh_tipe` ENUM('super_admin','pengelola'))   BEGIN
    DECLARE v_kode_laporan VARCHAR(50);
    DECLARE v_judul VARCHAR(255);
    DECLARE v_data_json LONGTEXT;
    
    SET v_kode_laporan = CONCAT('LAP-PROD-', UPPER(p_tipe_laporan), '-', DATE_FORMAT(NOW(), '%Y%m%d%H%i%s'));
    SET v_judul = CONCAT('Laporan Produksi ', UPPER(LEFT(p_tipe_laporan, 1)), LOWER(SUBSTRING(p_tipe_laporan, 2)), 
                         ' (', DATE_FORMAT(p_tanggal_mulai, '%d/%m/%Y'), ' - ', DATE_FORMAT(p_tanggal_akhir, '%d/%m/%Y'), ')');
    
    SELECT JSON_OBJECT(
        'summary', JSON_OBJECT(
            'total_produksi', COUNT(*),
            'total_porsi', SUM(jumlah_porsi),
            'produksi_berhasil', SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END),
            'produksi_gagal', SUM(CASE WHEN status = 'gagal' THEN 1 ELSE 0 END),
            'rata_rata_durasi', AVG(durasi_produksi)
        ),
        'detail', JSON_ARRAYAGG(
            JSON_OBJECT(
                'tanggal', tanggal_produksi,
                'menu', (SELECT nama_menu FROM tbl_menu WHERE id_menu = tbl_produksi_harian.id_menu),
                'jumlah_porsi', jumlah_porsi,
                'status', status,
                'karyawan', (SELECT nama FROM tbl_karyawan WHERE id_karyawan = tbl_produksi_harian.id_karyawan)
            )
        )
    ) INTO v_data_json
    FROM tbl_produksi_harian
    WHERE tanggal_produksi BETWEEN p_tanggal_mulai AND p_tanggal_akhir
    AND (p_id_dapur IS NULL OR id_dapur = p_id_dapur);
    
    INSERT INTO tbl_laporan (
        kode_laporan, tipe_laporan, kategori_laporan, id_dapur,
        tanggal_mulai, tanggal_akhir, judul_laporan, data_laporan,
        dibuat_oleh, dibuat_oleh_tipe, status_laporan
    ) VALUES (
        v_kode_laporan, p_tipe_laporan, 'produksi', p_id_dapur,
        p_tanggal_mulai, p_tanggal_akhir, v_judul, v_data_json,
        p_dibuat_oleh, p_dibuat_oleh_tipe, 'final'
    );
    
    SELECT v_kode_laporan AS kode_laporan, 'Laporan berhasil dibuat' AS message;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_kurangi_stok_produksi` (IN `p_id_produksi` INT)   BEGIN
    -- ══════════════════════════════════════
    -- BAGIAN 1: DECLARE VARIABEL (SEMUA!)
    -- ══════════════════════════════════════
    DECLARE v_id_menu INT;
    DECLARE v_jumlah_porsi INT;
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id_bahan INT;
    DECLARE v_jumlah_bahan DECIMAL(10,2);
    DECLARE v_satuan VARCHAR(50);
    DECLARE v_stok_sebelum DECIMAL(10,2);
    DECLARE v_total_keluar DECIMAL(10,2);
    
    -- ══════════════════════════════════════
    -- BAGIAN 2: DECLARE CURSOR
    -- ══════════════════════════════════════
    DECLARE cur CURSOR FOR 
        SELECT id_bahan, jumlah_bahan, satuan 
        FROM tbl_resep_menu 
        WHERE id_menu = v_id_menu;
    
    -- ══════════════════════════════════════
    -- BAGIAN 3: DECLARE HANDLER
    -- ══════════════════════════════════════
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- ══════════════════════════════════════
    -- BAGIAN 4: KODE EXECUTABLE
    -- ══════════════════════════════════════
    
    -- Sekarang baru boleh SELECT INTO
    SELECT id_menu, jumlah_porsi 
    INTO v_id_menu, v_jumlah_porsi
    FROM tbl_produksi_harian
    WHERE id_produksi = p_id_produksi;
    
    -- Buka cursor
    OPEN cur;
    
    -- Loop untuk proses setiap bahan
    read_loop: LOOP
        FETCH cur INTO v_id_bahan, v_jumlah_bahan, v_satuan;
        
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Hitung total bahan keluar
        SET v_total_keluar = v_jumlah_bahan * v_jumlah_porsi;
        
        -- Ambil stok terakhir
        SELECT COALESCE(stok_sesudah, 0) 
        INTO v_stok_sebelum 
        FROM tbl_riwayat_stok 
        WHERE id_bahan = v_id_bahan 
        ORDER BY id_riwayat DESC 
        LIMIT 1;
        
        -- Insert riwayat stok keluar
        INSERT INTO tbl_riwayat_stok (
            id_bahan, 
            tipe_transaksi, 
            jumlah_stok, 
            stok_sebelum, 
            stok_sesudah, 
            satuan, 
            tanggal_transaksi,
            keterangan
        ) VALUES (
            v_id_bahan,
            'keluar',
            v_total_keluar,
            v_stok_sebelum,
            v_stok_sebelum - v_total_keluar,
            v_satuan,
            CURDATE(),
            CONCAT('Produksi: ', p_id_produksi)
        );
    END LOOP;
    
    -- Tutup cursor
    CLOSE cur;
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_tambah_stok_pembelanjaan` (IN `p_id_pembelanjaan` INT)   BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id_bahan INT;
    DECLARE v_jumlah DECIMAL(10,2);
    DECLARE v_satuan VARCHAR(50);
    DECLARE v_stok_sebelum DECIMAL(10,2);
    
    DECLARE cur CURSOR FOR 
        SELECT id_bahan, jumlah, satuan 
        FROM tbl_detail_pembelanjaan 
        WHERE id_pembelanjaan = p_id_pembelanjaan;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO v_id_bahan, v_jumlah, v_satuan;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        SELECT COALESCE(stok_sesudah, 0) INTO v_stok_sebelum 
        FROM tbl_riwayat_stok 
        WHERE id_bahan = v_id_bahan 
        ORDER BY id_riwayat DESC 
        LIMIT 1;
        
        INSERT INTO tbl_riwayat_stok (
            id_bahan, 
            id_pembelanjaan, 
            tipe_transaksi, 
            jumlah_stok, 
            stok_sebelum, 
            stok_sesudah, 
            satuan, 
            tanggal_transaksi
        ) VALUES (
            v_id_bahan,
            p_id_pembelanjaan,
            'masuk',
            v_jumlah,
            v_stok_sebelum,
            v_stok_sebelum + v_jumlah,
            v_satuan,
            CURDATE()
        );
    END LOOP;
    
    CLOSE cur;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_absensi`
--

CREATE TABLE `tbl_absensi` (
  `id_absensi` int(11) NOT NULL,
  `id_karyawan` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `jam_keluar` time DEFAULT NULL,
  `total_jam_kerja` decimal(5,2) DEFAULT NULL,
  `status_kehadiran` enum('hadir','izin','sakit','alpha') DEFAULT 'hadir',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_absensi`
--

INSERT INTO `tbl_absensi` (`id_absensi`, `id_karyawan`, `tanggal`, `jam_masuk`, `jam_keluar`, `total_jam_kerja`, `status_kehadiran`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 3, '2025-11-23', '00:08:21', '00:08:32', 0.00, 'hadir', NULL, '2025-11-22 23:08:21', '2025-11-22 23:08:32');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_admin_log`
--

CREATE TABLE `tbl_admin_log` (
  `id_log` int(11) NOT NULL,
  `id_super_admin` int(11) NOT NULL,
  `aktivitas` varchar(255) NOT NULL,
  `tabel_target` varchar(100) DEFAULT NULL,
  `id_target` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_bahan_baku`
--

CREATE TABLE `tbl_bahan_baku` (
  `id_bahan` int(11) NOT NULL,
  `nama_bahan` varchar(100) NOT NULL,
  `satuan` varchar(50) NOT NULL,
  `stok_saat_ini` decimal(10,2) DEFAULT 0.00,
  `harga_per_satuan` decimal(15,2) DEFAULT NULL,
  `stok_minimum` int(11) DEFAULT 10,
  `status` enum('tersedia','habis','discontinued') DEFAULT 'tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_bahan_baku`
--

INSERT INTO `tbl_bahan_baku` (`id_bahan`, `nama_bahan`, `satuan`, `stok_saat_ini`, `harga_per_satuan`, `stok_minimum`, `status`, `created_at`, `updated_at`) VALUES
(1, 'telor', 'kg', 20.00, NULL, 10, 'tersedia', '2025-11-22 19:06:33', '2025-11-22 19:32:38'),
(2, 'beras', 'karung', 100.00, NULL, 10, 'tersedia', '2025-11-22 19:06:33', '2025-11-22 19:32:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_dapur`
--

CREATE TABLE `tbl_dapur` (
  `id_dapur` int(11) NOT NULL,
  `id_pengelola` int(11) NOT NULL,
  `nama_dapur` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `kapasitas_produksi` int(11) DEFAULT NULL,
  `jumlah_karyawan` int(11) DEFAULT 0,
  `status` enum('aktif','nonaktif','maintenance') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_dapur`
--

INSERT INTO `tbl_dapur` (`id_dapur`, `id_pengelola`, `nama_dapur`, `alamat`, `kapasitas_produksi`, `jumlah_karyawan`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'dapur', 'kudus', 3000, 3, 'aktif', '2025-11-21 07:06:46', '2025-11-23 09:42:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_detail_pembelanjaan`
--

CREATE TABLE `tbl_detail_pembelanjaan` (
  `id_detail` int(11) NOT NULL,
  `id_pembelanjaan` int(11) NOT NULL,
  `id_bahan` int(11) NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `harga_satuan` decimal(15,2) DEFAULT NULL,
  `subtotal` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_detail_pembelanjaan`
--

INSERT INTO `tbl_detail_pembelanjaan` (`id_detail`, `id_pembelanjaan`, `id_bahan`, `jumlah`, `satuan`, `harga_satuan`, `subtotal`, `created_at`) VALUES
(1, 1, 1, 10.00, 'kg', 30000.00, 300000.00, '2025-11-22 19:06:33'),
(2, 1, 2, 50.00, 'karung', 300000.00, 15000000.00, '2025-11-22 19:06:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_dokumentasi_karyawan`
--

CREATE TABLE `tbl_dokumentasi_karyawan` (
  `id_dokumentasi` int(11) NOT NULL,
  `tanggal_dokumentasi` date NOT NULL,
  `aktivitas` text NOT NULL,
  `foto_dokumentasi` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_dokumentasi_karyawan`
--

INSERT INTO `tbl_dokumentasi_karyawan` (`id_dokumentasi`, `tanggal_dokumentasi`, `aktivitas`, `foto_dokumentasi`, `created_at`) VALUES
(2, '2025-11-22', '[ksdcm] sedang ', '69221a9eb9d4a.png', '2025-11-22 20:18:38'),
(3, '2025-11-23', '[Tukang Masak] memasak', '6922c0f3c5f4f.jpg', '2025-11-23 08:08:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_karyawan`
--

CREATE TABLE `tbl_karyawan` (
  `id_karyawan` int(11) NOT NULL,
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
  `hari_libur` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_karyawan`
--

INSERT INTO `tbl_karyawan` (`id_karyawan`, `id_role`, `id_pengelola`, `id_dapur`, `nama`, `bagian`, `jam_masuk`, `jam_keluar`, `email`, `password`, `no_telepon`, `alamat`, `tanggal_bergabung`, `foto_profil`, `status`, `created_at`, `updated_at`, `hari_libur`) VALUES
(1, 3, 1, 1, 'bbbb', 'chef', NULL, NULL, 'Muhammadzikrialfadani01@gmail.com', '123456', '', 'kudus', '2026-01-01', NULL, 'aktif', '2025-11-21 14:16:24', '2025-11-21 14:16:49', NULL),
(3, 3, 1, 1, 'Zikri', 'tukang_masak', '06:40:00', '06:39:00', 'mrzkr1019@gmail.com', '010101', '234567', 'kudus', NULL, NULL, 'aktif', '2025-11-22 16:33:15', '2025-11-23 07:37:45', 'Minggu'),
(4, 3, 1, 1, 'aya', 'pengantar', '11:00:00', '13:00:00', 'm@exampel.com', '101010', '', 'kudus', NULL, NULL, 'aktif', '2025-11-23 09:42:22', '2025-11-23 09:51:14', 'Sabtu,Minggu');

--
-- Trigger `tbl_karyawan`
--
DELIMITER $$
CREATE TRIGGER `trg_update_jumlah_karyawan_delete` AFTER DELETE ON `tbl_karyawan` FOR EACH ROW BEGIN
    IF OLD.id_dapur IS NOT NULL THEN
        UPDATE tbl_dapur 
        SET jumlah_karyawan = (
            SELECT COUNT(*) 
            FROM tbl_karyawan 
            WHERE id_dapur = OLD.id_dapur AND status = 'aktif'
        )
        WHERE id_dapur = OLD.id_dapur;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_update_jumlah_karyawan_insert` AFTER INSERT ON `tbl_karyawan` FOR EACH ROW BEGIN
    IF NEW.id_dapur IS NOT NULL THEN
        UPDATE tbl_dapur 
        SET jumlah_karyawan = (
            SELECT COUNT(*) 
            FROM tbl_karyawan 
            WHERE id_dapur = NEW.id_dapur AND status = 'aktif'
        )
        WHERE id_dapur = NEW.id_dapur;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_update_jumlah_karyawan_update` AFTER UPDATE ON `tbl_karyawan` FOR EACH ROW BEGIN
    IF OLD.id_dapur IS NOT NULL THEN
        UPDATE tbl_dapur 
        SET jumlah_karyawan = (
            SELECT COUNT(*) 
            FROM tbl_karyawan 
            WHERE id_dapur = OLD.id_dapur AND status = 'aktif'
        )
        WHERE id_dapur = OLD.id_dapur;
    END IF;
    
    IF NEW.id_dapur IS NOT NULL THEN
        UPDATE tbl_dapur 
        SET jumlah_karyawan = (
            SELECT COUNT(*) 
            FROM tbl_karyawan 
            WHERE id_dapur = NEW.id_dapur AND status = 'aktif'
        )
        WHERE id_dapur = NEW.id_dapur;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_laporan`
--

CREATE TABLE `tbl_laporan` (
  `id_laporan` int(11) NOT NULL,
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
  `konten_laporan` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_laporan`
--

INSERT INTO `tbl_laporan` (`id_laporan`, `kode_laporan`, `tipe_laporan`, `kategori_laporan`, `id_pengelola`, `id_dapur`, `tanggal_mulai`, `tanggal_akhir`, `judul_laporan`, `deskripsi`, `data_laporan`, `file_pdf`, `status_laporan`, `dibuat_oleh`, `dibuat_oleh_tipe`, `created_at`, `updated_at`, `konten_laporan`) VALUES
(1, 'LAP-KEU-20251123143725', 'bulanan', 'keuangan', NULL, NULL, '2025-11-01', '2025-11-30', 'Laporan Keuangan Periode November 2025', NULL, NULL, NULL, 'final', 1, 'super_admin', '2025-11-23 13:37:25', '2025-11-23 13:37:25', '{\"summary\":{\"total_pengeluaran\":15300000},\"details\":[{\"id_pembelanjaan\":\"1\",\"id_dapur\":\"1\",\"id_pengelola\":\"1\",\"kode_pembelanjaan\":\"PB202511231367\",\"no_nota_fisik\":\"\",\"tanggal_pembelian\":\"2025-11-22\",\"supplier\":\"pasar\",\"total_pembelian\":\"15300000.00\",\"metode_pembayaran\":\"tunai\",\"status_pembayaran\":\"lunas\",\"status\":\"selesai\",\"keterangan\":\"\",\"bukti_pembelian\":\"69220fd6e9c1a.jpeg\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\",\"nama_dapur\":\"dapur\"}]}'),
(2, 'LAP-STO-20251123143747', 'bulanan', 'stok', NULL, NULL, '2025-11-01', '2025-11-30', 'Laporan Stok Periode November 2025', NULL, NULL, NULL, 'final', 1, 'super_admin', '2025-11-23 13:37:47', '2025-11-23 13:37:47', '{\"summary\":{\"total_item\":2},\"details\":[{\"id_bahan\":\"2\",\"nama_bahan\":\"beras\",\"satuan\":\"karung\",\"stok_saat_ini\":\"100.00\",\"harga_per_satuan\":null,\"stok_minimum\":\"10\",\"status\":\"tersedia\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\"},{\"id_bahan\":\"1\",\"nama_bahan\":\"telor\",\"satuan\":\"kg\",\"stok_saat_ini\":\"20.00\",\"harga_per_satuan\":null,\"stok_minimum\":\"10\",\"status\":\"tersedia\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\"}]}'),
(3, 'LAP-KES-20251123143803', 'bulanan', 'keseluruhan', NULL, NULL, '2025-11-01', '2025-11-30', 'Laporan Keseluruhan Periode November 2025', NULL, NULL, NULL, 'final', 1, 'super_admin', '2025-11-23 13:38:03', '2025-11-23 13:38:03', '[]'),
(4, 'LAP-PRO-20251123144022', 'bulanan', 'produksi', NULL, NULL, '2025-11-01', '2025-11-30', 'Laporan Produksi Periode November 2025', NULL, NULL, NULL, 'final', 1, 'super_admin', '2025-11-23 13:40:22', '2025-11-23 13:40:22', '{\"summary\":{\"total_menu\":1},\"details\":[{\"id_menu\":\"1\",\"id_pengelola\":\"1\",\"nama_menu\":\"Nasi kuning\",\"jumlah_porsi\":\"0\",\"deskripsi\":\"susu\\r\\nbuah\\r\\nbu\\r\\nbukj\\r\\nyhjn\\r\\n\",\"foto_menu\":\"6921fd37a9a9a.jpeg\",\"status\":\"aktif\",\"created_at\":\"2025-11-23 01:13:11\",\"updated_at\":\"2025-11-23 01:49:08\",\"tanggal_menu\":\"2025-11-22\",\"status_pengantaran\":\"belum_diantar\",\"nama_dapur\":\"dapur\"}]}'),
(5, 'LAP-PRO-20251123144045', 'bulanan', 'produksi', NULL, NULL, '2025-11-01', '2025-11-30', 'Laporan Produksi Periode November 2025', NULL, NULL, NULL, 'final', 1, 'super_admin', '2025-11-23 13:40:45', '2025-11-23 13:40:45', '{\"summary\":{\"total_menu\":1},\"details\":[{\"id_menu\":\"1\",\"id_pengelola\":\"1\",\"nama_menu\":\"Nasi kuning\",\"jumlah_porsi\":\"0\",\"deskripsi\":\"susu\\r\\nbuah\\r\\nbu\\r\\nbukj\\r\\nyhjn\\r\\n\",\"foto_menu\":\"6921fd37a9a9a.jpeg\",\"status\":\"aktif\",\"created_at\":\"2025-11-23 01:13:11\",\"updated_at\":\"2025-11-23 01:49:08\",\"tanggal_menu\":\"2025-11-22\",\"status_pengantaran\":\"belum_diantar\",\"nama_dapur\":\"dapur\"}]}'),
(6, 'LAP-KEU-20251123144101', 'bulanan', 'keuangan', NULL, NULL, '2025-11-01', '2025-11-30', 'Laporan Keuangan Periode November 2025', NULL, NULL, NULL, 'final', 1, 'super_admin', '2025-11-23 13:41:01', '2025-11-23 13:41:01', '{\"summary\":{\"total_pengeluaran\":15300000},\"details\":[{\"id_pembelanjaan\":\"1\",\"id_dapur\":\"1\",\"id_pengelola\":\"1\",\"kode_pembelanjaan\":\"PB202511231367\",\"no_nota_fisik\":\"\",\"tanggal_pembelian\":\"2025-11-22\",\"supplier\":\"pasar\",\"total_pembelian\":\"15300000.00\",\"metode_pembayaran\":\"tunai\",\"status_pembayaran\":\"lunas\",\"status\":\"selesai\",\"keterangan\":\"\",\"bukti_pembelian\":\"69220fd6e9c1a.jpeg\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\",\"nama_dapur\":\"dapur\"}]}'),
(7, 'LAP-STO-20251123144108', 'bulanan', 'stok', NULL, NULL, '2025-11-01', '2025-11-30', 'Laporan Stok Periode November 2025', NULL, NULL, NULL, 'final', 1, 'super_admin', '2025-11-23 13:41:08', '2025-11-23 13:41:08', '{\"summary\":{\"total_item\":2},\"details\":[{\"id_bahan\":\"2\",\"nama_bahan\":\"beras\",\"satuan\":\"karung\",\"stok_saat_ini\":\"100.00\",\"harga_per_satuan\":null,\"stok_minimum\":\"10\",\"status\":\"tersedia\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\"},{\"id_bahan\":\"1\",\"nama_bahan\":\"telor\",\"satuan\":\"kg\",\"stok_saat_ini\":\"20.00\",\"harga_per_satuan\":null,\"stok_minimum\":\"10\",\"status\":\"tersedia\",\"created_at\":\"2025-11-23 02:06:33\",\"updated_at\":\"2025-11-23 02:32:38\"}]}'),
(8, 'LAP-KAR-20251123144116', 'bulanan', 'karyawan', NULL, NULL, '2025-11-01', '2025-11-30', 'Laporan Karyawan Periode November 2025', NULL, NULL, NULL, 'final', 1, 'super_admin', '2025-11-23 13:41:16', '2025-11-23 13:41:16', '{\"summary\":{\"total_karyawan\":3},\"details\":[{\"nama\":\"bbbb\",\"bagian\":\"chef\",\"hadir\":\"0\",\"izin\":\"0\",\"sakit\":\"0\",\"alpha\":\"0\"},{\"nama\":\"Zikri\",\"bagian\":\"tukang_masak\",\"hadir\":\"1\",\"izin\":\"0\",\"sakit\":\"0\",\"alpha\":\"0\"},{\"nama\":\"aya\",\"bagian\":\"pengantar\",\"hadir\":\"0\",\"izin\":\"0\",\"sakit\":\"0\",\"alpha\":\"0\"}]}'),
(9, 'LAP-KES-20251123144122', 'bulanan', 'keseluruhan', NULL, NULL, '2025-11-01', '2025-11-30', 'Laporan Keseluruhan Periode November 2025', NULL, NULL, NULL, 'final', 1, 'super_admin', '2025-11-23 13:41:22', '2025-11-23 13:41:22', '[]');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_log_aktivitas`
--

CREATE TABLE `tbl_log_aktivitas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(150) DEFAULT NULL,
  `user_type` varchar(50) NOT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `activity` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_menu`
--

CREATE TABLE `tbl_menu` (
  `id_menu` int(11) NOT NULL,
  `id_pengelola` int(11) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `jumlah_porsi` int(11) DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `foto_menu` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tanggal_menu` date DEFAULT curdate(),
  `status_pengantaran` enum('belum_diantar','proses','selesai') DEFAULT 'belum_diantar'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_menu`
--

INSERT INTO `tbl_menu` (`id_menu`, `id_pengelola`, `nama_menu`, `jumlah_porsi`, `deskripsi`, `foto_menu`, `status`, `created_at`, `updated_at`, `tanggal_menu`, `status_pengantaran`) VALUES
(1, 1, 'Nasi kuning', 0, 'susu\r\nbuah\r\nbu\r\nbukj\r\nyhjn\r\n', '6921fd37a9a9a.jpeg', 'aktif', '2025-11-22 18:13:11', '2025-11-22 18:49:08', '2025-11-22', 'belum_diantar');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_pembelanjaan`
--

CREATE TABLE `tbl_pembelanjaan` (
  `id_pembelanjaan` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_pembelanjaan`
--

INSERT INTO `tbl_pembelanjaan` (`id_pembelanjaan`, `id_dapur`, `id_pengelola`, `kode_pembelanjaan`, `no_nota_fisik`, `tanggal_pembelian`, `supplier`, `total_pembelian`, `metode_pembayaran`, `status_pembayaran`, `status`, `keterangan`, `bukti_pembelian`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'PB202511231367', '', '2025-11-22', 'pasar', 15300000.00, 'tunai', 'lunas', 'selesai', '', '69220fd6e9c1a.jpeg', '2025-11-22 19:06:33', '2025-11-22 19:32:38');

--
-- Trigger `tbl_pembelanjaan`
--
DELIMITER $$
CREATE TRIGGER `trg_generate_kode_pembelanjaan` BEFORE INSERT ON `tbl_pembelanjaan` FOR EACH ROW BEGIN
    DECLARE v_kode VARCHAR(50);
    DECLARE v_exists INT;
    DECLARE v_attempt INT DEFAULT 0;
    
    retry_loop: LOOP
        SET v_kode = CONCAT(
            'PB',
            DATE_FORMAT(NOW(), '%Y%m%d'),
            LPAD(FLOOR(RAND() * 10000), 4, '0')
        );
        
        SELECT COUNT(*) INTO v_exists 
        FROM tbl_pembelanjaan 
        WHERE kode_pembelanjaan = v_kode;
        
        IF v_exists = 0 THEN
            LEAVE retry_loop;
        END IF;
        
        SET v_attempt = v_attempt + 1;
        
        IF v_attempt >= 10 THEN
            SET v_kode = CONCAT(
                'PB',
                DATE_FORMAT(NOW(6), '%Y%m%d%H%i%s%f')
            );
            LEAVE retry_loop;
        END IF;
    END LOOP;
    
    SET NEW.kode_pembelanjaan = v_kode;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_pengaduan`
--

CREATE TABLE `tbl_pengaduan` (
  `id_pengaduan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `tipe_user` enum('karyawan','pengelola') NOT NULL,
  `judul` varchar(255) NOT NULL,
  `isi` text NOT NULL,
  `status` enum('pending','proses','selesai') DEFAULT 'pending',
  `tanggapan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_pengelola_dapur`
--

CREATE TABLE `tbl_pengelola_dapur` (
  `id_pengelola` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_pengelola_dapur`
--

INSERT INTO `tbl_pengelola_dapur` (`id_pengelola`, `id_role`, `nama`, `no_telepon`, `email`, `password`, `foto_profil`, `status`, `verification_code`, `verification_expires_at`, `is_verified`, `created_at`, `updated_at`) VALUES
(1, 2, 'abcd', '089767890', 'm@xexample.com', 'Inc1019', NULL, 'aktif', NULL, NULL, 1, '2025-11-19 16:18:50', '2025-11-22 23:12:39'),
(3, 2, 'zikri', NULL, 'muhammadzikrialfadani02@gmail.com', 'zikri123', NULL, 'aktif', NULL, '2025-11-23 23:21:09', 1, '2025-11-22 22:21:09', '2025-11-22 22:21:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_produksi_harian`
--

CREATE TABLE `tbl_produksi_harian` (
  `id_produksi` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Trigger `tbl_produksi_harian`
--
DELIMITER $$
CREATE TRIGGER `trg_generate_kode_produksi` BEFORE INSERT ON `tbl_produksi_harian` FOR EACH ROW BEGIN
    DECLARE v_counter INT;
    DECLARE v_kode VARCHAR(50);
    DECLARE v_tanggal VARCHAR(8);
    
    -- Hanya generate jika kode_produksi NULL
    IF NEW.kode_produksi IS NULL OR NEW.kode_produksi = '' THEN
        -- Format tanggal: YYYYMMDD
        SET v_tanggal = DATE_FORMAT(NOW(), '%Y%m%d');
        
        -- Hitung berapa produksi hari ini (by tanggal di kode, bukan created_at)
        SELECT COUNT(*) + 1 INTO v_counter 
        FROM tbl_produksi_harian 
        WHERE kode_produksi LIKE CONCAT('PRD', v_tanggal, '%');
        
        -- Generate kode: PRD + YYYYMMDD + counter 4 digit
        SET v_kode = CONCAT('PRD', v_tanggal, LPAD(v_counter, 4, '0'));
        
        -- Set kode ke record baru
        SET NEW.kode_produksi = v_kode;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_resep_menu`
--

CREATE TABLE `tbl_resep_menu` (
  `id_resep` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `id_bahan` int(11) NOT NULL,
  `jumlah_bahan` decimal(10,2) NOT NULL,
  `satuan` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_riwayat_stok`
--

CREATE TABLE `tbl_riwayat_stok` (
  `id_riwayat` int(11) NOT NULL,
  `id_bahan` int(11) NOT NULL,
  `id_pembelanjaan` int(11) DEFAULT NULL,
  `tipe_transaksi` enum('masuk','keluar','adjustment') NOT NULL,
  `jumlah_stok` decimal(10,2) NOT NULL,
  `stok_sebelum` decimal(10,2) DEFAULT NULL,
  `stok_sesudah` decimal(10,2) DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `tanggal_transaksi` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_riwayat_stok`
--

INSERT INTO `tbl_riwayat_stok` (`id_riwayat`, `id_bahan`, `id_pembelanjaan`, `tipe_transaksi`, `jumlah_stok`, `stok_sebelum`, `stok_sesudah`, `satuan`, `tanggal_transaksi`, `keterangan`, `created_at`) VALUES
(7, 1, 1, 'masuk', 10.00, 0.00, 10.00, 'kg', '2025-11-23', 'Pembelanjaan Selesai', '2025-11-22 19:29:41'),
(8, 2, 1, 'masuk', 50.00, 0.00, 50.00, 'karung', '2025-11-23', 'Pembelanjaan Selesai', '2025-11-22 19:29:41'),
(9, 1, 1, 'masuk', 10.00, 10.00, 20.00, 'kg', '2025-11-23', 'Pembelanjaan Selesai', '2025-11-22 19:32:38'),
(10, 2, 1, 'masuk', 50.00, 50.00, 100.00, 'karung', '2025-11-23', 'Pembelanjaan Selesai', '2025-11-22 19:32:38');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_role`
--

CREATE TABLE `tbl_role` (
  `id_role` int(11) NOT NULL,
  `nama_role` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_role`
--

INSERT INTO `tbl_role` (`id_role`, `nama_role`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'Administrator tertinggi dengan akses penuh ke seluruh sistem', '2025-11-19 04:11:21', '2025-11-19 04:11:21'),
(2, 'Pengelola Dapur', 'Mengelola dapur, karyawan, menu, dan operasional', '2025-11-19 04:11:21', '2025-11-19 04:11:21'),
(3, 'Karyawan', 'Staff yang bekerja di dapur dan melakukan produksi', '2025-11-19 04:11:21', '2025-11-19 04:11:21');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tbl_super_admin`
--

CREATE TABLE `tbl_super_admin` (
  `id_super_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tbl_super_admin`
--

INSERT INTO `tbl_super_admin` (`id_super_admin`, `username`, `password`, `nama_lengkap`, `email`, `no_telepon`, `foto_profil`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin123', 'Super Administrator', 'admin@mbg.com', '081234567890', NULL, 'aktif', '2025-11-23 13:11:05', '2025-11-19 04:11:21', '2025-11-23 13:11:05');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `vw_dashboard_super_admin`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `vw_dashboard_super_admin` (
`total_pengelola` bigint(21)
,`total_dapur` bigint(21)
,`total_karyawan` bigint(21)
,`total_menu` bigint(21)
,`produksi_hari_ini` bigint(21)
,`total_bahan_aktif` bigint(21)
,`pembelanjaan_hari_ini` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `vw_laporan_karyawan_bulanan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `vw_laporan_karyawan_bulanan` (
`tahun` int(4)
,`bulan` int(2)
,`periode` varchar(7)
,`id_karyawan` int(11)
,`nama` varchar(100)
,`bagian` varchar(100)
,`nama_dapur` varchar(100)
,`total_kehadiran` bigint(21)
,`total_jam_kerja` decimal(27,2)
,`rata_rata_jam_kerja` decimal(9,6)
,`hadir` decimal(22,0)
,`izin` decimal(22,0)
,`sakit` decimal(22,0)
,`alpha` decimal(22,0)
,`total_produksi` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `vw_laporan_karyawan_harian`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `vw_laporan_karyawan_harian` (
`tanggal` date
,`id_karyawan` int(11)
,`nama` varchar(100)
,`bagian` varchar(100)
,`nama_dapur` varchar(100)
,`total_kehadiran` bigint(21)
,`total_jam_kerja` decimal(27,2)
,`total_produksi` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `vw_laporan_karyawan_mingguan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `vw_laporan_karyawan_mingguan` (
`minggu_tahun` int(6)
,`tanggal_mulai_minggu` date
,`id_karyawan` int(11)
,`nama` varchar(100)
,`bagian` varchar(100)
,`nama_dapur` varchar(100)
,`total_kehadiran` bigint(21)
,`total_jam_kerja` decimal(27,2)
,`rata_rata_jam_kerja` decimal(9,6)
,`total_produksi` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `vw_laporan_keseluruhan_harian`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `vw_laporan_keseluruhan_harian` (
`tanggal` date
,`total_produksi` bigint(21)
,`total_porsi` decimal(32,0)
,`total_kehadiran` bigint(21)
,`total_pengeluaran` decimal(37,2)
,`transaksi_stok_masuk` bigint(21)
,`transaksi_stok_keluar` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `vw_laporan_keuangan_bulanan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `vw_laporan_keuangan_bulanan` (
`tahun` int(4)
,`bulan` int(2)
,`periode` varchar(7)
,`nama_dapur` varchar(100)
,`id_dapur` int(11)
,`total_transaksi` bigint(21)
,`total_pengeluaran` decimal(37,2)
,`rata_rata_transaksi` decimal(19,6)
,`total_lunas` decimal(37,2)
,`total_belum_lunas` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `vw_laporan_keuangan_harian`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `vw_laporan_keuangan_harian` (
`tanggal` date
,`nama_dapur` varchar(100)
,`id_dapur` int(11)
,`total_transaksi` bigint(21)
,`total_pengeluaran` decimal(37,2)
,`rata_rata_transaksi` decimal(19,6)
,`pembayaran_tunai` decimal(37,2)
,`pembayaran_transfer` decimal(37,2)
,`pembayaran_kredit` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `vw_laporan_keuangan_mingguan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `vw_laporan_keuangan_mingguan` (
`minggu_tahun` int(6)
,`tanggal_mulai_minggu` date
,`tanggal_akhir_minggu` date
,`nama_dapur` varchar(100)
,`id_dapur` int(11)
,`total_transaksi` bigint(21)
,`total_pengeluaran` decimal(37,2)
,`rata_rata_transaksi` decimal(19,6)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `vw_laporan_produksi_bulanan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `vw_laporan_produksi_bulanan` (
`tahun` int(4)
,`bulan` int(2)
,`periode` varchar(7)
,`nama_dapur` varchar(100)
,`id_dapur` int(11)
,`total_produksi` bigint(21)
,`total_porsi` decimal(32,0)
,`produksi_berhasil` decimal(22,0)
,`produksi_gagal` decimal(22,0)
,`rata_rata_durasi` decimal(14,4)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `vw_laporan_produksi_harian`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `vw_laporan_produksi_harian` (
`tanggal` date
,`nama_dapur` varchar(100)
,`id_dapur` int(11)
,`total_produksi` bigint(21)
,`total_porsi` decimal(32,0)
,`produksi_berhasil` decimal(22,0)
,`produksi_gagal` decimal(22,0)
,`rata_rata_durasi` decimal(14,4)
,`menu_diproduksi` mediumtext
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `vw_laporan_produksi_mingguan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `vw_laporan_produksi_mingguan` (
`minggu_tahun` int(6)
,`tanggal_mulai_minggu` date
,`tanggal_akhir_minggu` date
,`nama_dapur` varchar(100)
,`id_dapur` int(11)
,`total_produksi` bigint(21)
,`total_porsi` decimal(32,0)
,`produksi_berhasil` decimal(22,0)
,`produksi_gagal` decimal(22,0)
,`rata_rata_durasi` decimal(14,4)
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `vw_laporan_stok_harian`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `vw_laporan_stok_harian` (
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `vw_laporan_stok_mingguan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `vw_laporan_stok_mingguan` (
);

-- --------------------------------------------------------

--
-- Struktur untuk view `vw_dashboard_super_admin`
--
DROP TABLE IF EXISTS `vw_dashboard_super_admin`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_dashboard_super_admin`  AS SELECT (select count(0) from `tbl_pengelola_dapur` where `tbl_pengelola_dapur`.`status` = 'aktif') AS `total_pengelola`, (select count(0) from `tbl_dapur` where `tbl_dapur`.`status` = 'aktif') AS `total_dapur`, (select count(0) from `tbl_karyawan` where `tbl_karyawan`.`status` = 'aktif') AS `total_karyawan`, (select count(0) from `tbl_menu` where `tbl_menu`.`status` = 'aktif') AS `total_menu`, (select count(0) from `tbl_produksi_harian` where `tbl_produksi_harian`.`tanggal_produksi` = curdate()) AS `produksi_hari_ini`, (select count(0) from `tbl_bahan_baku` where `tbl_bahan_baku`.`status` = 'tersedia') AS `total_bahan_aktif`, (select coalesce(sum(`tbl_pembelanjaan`.`total_pembelian`),0) from `tbl_pembelanjaan` where `tbl_pembelanjaan`.`tanggal_pembelian` = curdate()) AS `pembelanjaan_hari_ini` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `vw_laporan_karyawan_bulanan`
--
DROP TABLE IF EXISTS `vw_laporan_karyawan_bulanan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_karyawan_bulanan`  AS SELECT year(`a`.`tanggal`) AS `tahun`, month(`a`.`tanggal`) AS `bulan`, date_format(`a`.`tanggal`,'%Y-%m') AS `periode`, `k`.`id_karyawan` AS `id_karyawan`, `k`.`nama` AS `nama`, `k`.`bagian` AS `bagian`, `d`.`nama_dapur` AS `nama_dapur`, count(`a`.`id_absensi`) AS `total_kehadiran`, sum(`a`.`total_jam_kerja`) AS `total_jam_kerja`, avg(`a`.`total_jam_kerja`) AS `rata_rata_jam_kerja`, sum(case when `a`.`status_kehadiran` = 'hadir' then 1 else 0 end) AS `hadir`, sum(case when `a`.`status_kehadiran` = 'izin' then 1 else 0 end) AS `izin`, sum(case when `a`.`status_kehadiran` = 'sakit' then 1 else 0 end) AS `sakit`, sum(case when `a`.`status_kehadiran` = 'alpha' then 1 else 0 end) AS `alpha`, (select count(0) from `tbl_produksi_harian` where `tbl_produksi_harian`.`id_karyawan` = `k`.`id_karyawan` and date_format(`tbl_produksi_harian`.`tanggal_produksi`,'%Y-%m') = date_format(`a`.`tanggal`,'%Y-%m')) AS `total_produksi` FROM ((`tbl_absensi` `a` join `tbl_karyawan` `k` on(`a`.`id_karyawan` = `k`.`id_karyawan`)) left join `tbl_dapur` `d` on(`k`.`id_dapur` = `d`.`id_dapur`)) GROUP BY year(`a`.`tanggal`), month(`a`.`tanggal`), `k`.`id_karyawan` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `vw_laporan_karyawan_harian`
--
DROP TABLE IF EXISTS `vw_laporan_karyawan_harian`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_karyawan_harian`  AS SELECT cast(`a`.`tanggal` as date) AS `tanggal`, `k`.`id_karyawan` AS `id_karyawan`, `k`.`nama` AS `nama`, `k`.`bagian` AS `bagian`, `d`.`nama_dapur` AS `nama_dapur`, count(`a`.`id_absensi`) AS `total_kehadiran`, sum(`a`.`total_jam_kerja`) AS `total_jam_kerja`, (select count(0) from `tbl_produksi_harian` where `tbl_produksi_harian`.`id_karyawan` = `k`.`id_karyawan` and cast(`tbl_produksi_harian`.`tanggal_produksi` as date) = cast(`a`.`tanggal` as date)) AS `total_produksi` FROM ((`tbl_absensi` `a` join `tbl_karyawan` `k` on(`a`.`id_karyawan` = `k`.`id_karyawan`)) left join `tbl_dapur` `d` on(`k`.`id_dapur` = `d`.`id_dapur`)) GROUP BY cast(`a`.`tanggal` as date), `k`.`id_karyawan` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `vw_laporan_karyawan_mingguan`
--
DROP TABLE IF EXISTS `vw_laporan_karyawan_mingguan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_karyawan_mingguan`  AS SELECT yearweek(`a`.`tanggal`,1) AS `minggu_tahun`, cast(`a`.`tanggal` - interval weekday(`a`.`tanggal`) day as date) AS `tanggal_mulai_minggu`, `k`.`id_karyawan` AS `id_karyawan`, `k`.`nama` AS `nama`, `k`.`bagian` AS `bagian`, `d`.`nama_dapur` AS `nama_dapur`, count(`a`.`id_absensi`) AS `total_kehadiran`, sum(`a`.`total_jam_kerja`) AS `total_jam_kerja`, avg(`a`.`total_jam_kerja`) AS `rata_rata_jam_kerja`, (select count(0) from `tbl_produksi_harian` where `tbl_produksi_harian`.`id_karyawan` = `k`.`id_karyawan` and yearweek(`tbl_produksi_harian`.`tanggal_produksi`,1) = yearweek(`a`.`tanggal`,1)) AS `total_produksi` FROM ((`tbl_absensi` `a` join `tbl_karyawan` `k` on(`a`.`id_karyawan` = `k`.`id_karyawan`)) left join `tbl_dapur` `d` on(`k`.`id_dapur` = `d`.`id_dapur`)) GROUP BY yearweek(`a`.`tanggal`,1), `k`.`id_karyawan` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `vw_laporan_keseluruhan_harian`
--
DROP TABLE IF EXISTS `vw_laporan_keseluruhan_harian`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_keseluruhan_harian`  AS SELECT cast(curdate() as date) AS `tanggal`, (select count(0) from `tbl_produksi_harian` where cast(`tbl_produksi_harian`.`tanggal_produksi` as date) = curdate()) AS `total_produksi`, (select sum(`tbl_produksi_harian`.`jumlah_porsi`) from `tbl_produksi_harian` where cast(`tbl_produksi_harian`.`tanggal_produksi` as date) = curdate()) AS `total_porsi`, (select count(0) from `tbl_absensi` where `tbl_absensi`.`tanggal` = curdate()) AS `total_kehadiran`, (select coalesce(sum(`tbl_pembelanjaan`.`total_pembelian`),0) from `tbl_pembelanjaan` where cast(`tbl_pembelanjaan`.`tanggal_pembelian` as date) = curdate()) AS `total_pengeluaran`, (select count(0) from `tbl_riwayat_stok` where cast(`tbl_riwayat_stok`.`tanggal_transaksi` as date) = curdate() and `tbl_riwayat_stok`.`tipe_transaksi` = 'masuk') AS `transaksi_stok_masuk`, (select count(0) from `tbl_riwayat_stok` where cast(`tbl_riwayat_stok`.`tanggal_transaksi` as date) = curdate() and `tbl_riwayat_stok`.`tipe_transaksi` = 'keluar') AS `transaksi_stok_keluar` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `vw_laporan_keuangan_bulanan`
--
DROP TABLE IF EXISTS `vw_laporan_keuangan_bulanan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_keuangan_bulanan`  AS SELECT year(`p`.`tanggal_pembelian`) AS `tahun`, month(`p`.`tanggal_pembelian`) AS `bulan`, date_format(`p`.`tanggal_pembelian`,'%Y-%m') AS `periode`, `d`.`nama_dapur` AS `nama_dapur`, `d`.`id_dapur` AS `id_dapur`, count(`p`.`id_pembelanjaan`) AS `total_transaksi`, sum(`p`.`total_pembelian`) AS `total_pengeluaran`, avg(`p`.`total_pembelian`) AS `rata_rata_transaksi`, sum(case when `p`.`status_pembayaran` = 'lunas' then `p`.`total_pembelian` else 0 end) AS `total_lunas`, sum(case when `p`.`status_pembayaran` = 'belum_lunas' then `p`.`total_pembelian` else 0 end) AS `total_belum_lunas` FROM (`tbl_pembelanjaan` `p` join `tbl_dapur` `d` on(`p`.`id_dapur` = `d`.`id_dapur`)) GROUP BY year(`p`.`tanggal_pembelian`), month(`p`.`tanggal_pembelian`), `d`.`id_dapur` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `vw_laporan_keuangan_harian`
--
DROP TABLE IF EXISTS `vw_laporan_keuangan_harian`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_keuangan_harian`  AS SELECT cast(`p`.`tanggal_pembelian` as date) AS `tanggal`, `d`.`nama_dapur` AS `nama_dapur`, `d`.`id_dapur` AS `id_dapur`, count(`p`.`id_pembelanjaan`) AS `total_transaksi`, sum(`p`.`total_pembelian`) AS `total_pengeluaran`, avg(`p`.`total_pembelian`) AS `rata_rata_transaksi`, sum(case when `p`.`metode_pembayaran` = 'tunai' then `p`.`total_pembelian` else 0 end) AS `pembayaran_tunai`, sum(case when `p`.`metode_pembayaran` = 'transfer' then `p`.`total_pembelian` else 0 end) AS `pembayaran_transfer`, sum(case when `p`.`metode_pembayaran` = 'kredit' then `p`.`total_pembelian` else 0 end) AS `pembayaran_kredit` FROM (`tbl_pembelanjaan` `p` join `tbl_dapur` `d` on(`p`.`id_dapur` = `d`.`id_dapur`)) GROUP BY cast(`p`.`tanggal_pembelian` as date), `d`.`id_dapur` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `vw_laporan_keuangan_mingguan`
--
DROP TABLE IF EXISTS `vw_laporan_keuangan_mingguan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_keuangan_mingguan`  AS SELECT yearweek(`p`.`tanggal_pembelian`,1) AS `minggu_tahun`, cast(`p`.`tanggal_pembelian` - interval weekday(`p`.`tanggal_pembelian`) day as date) AS `tanggal_mulai_minggu`, cast(`p`.`tanggal_pembelian` - interval weekday(`p`.`tanggal_pembelian`) day + interval 6 day as date) AS `tanggal_akhir_minggu`, `d`.`nama_dapur` AS `nama_dapur`, `d`.`id_dapur` AS `id_dapur`, count(`p`.`id_pembelanjaan`) AS `total_transaksi`, sum(`p`.`total_pembelian`) AS `total_pengeluaran`, avg(`p`.`total_pembelian`) AS `rata_rata_transaksi` FROM (`tbl_pembelanjaan` `p` join `tbl_dapur` `d` on(`p`.`id_dapur` = `d`.`id_dapur`)) GROUP BY yearweek(`p`.`tanggal_pembelian`,1), `d`.`id_dapur` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `vw_laporan_produksi_bulanan`
--
DROP TABLE IF EXISTS `vw_laporan_produksi_bulanan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_produksi_bulanan`  AS SELECT year(`ph`.`tanggal_produksi`) AS `tahun`, month(`ph`.`tanggal_produksi`) AS `bulan`, date_format(`ph`.`tanggal_produksi`,'%Y-%m') AS `periode`, `d`.`nama_dapur` AS `nama_dapur`, `d`.`id_dapur` AS `id_dapur`, count(`ph`.`id_produksi`) AS `total_produksi`, sum(`ph`.`jumlah_porsi`) AS `total_porsi`, sum(case when `ph`.`status` = 'selesai' then 1 else 0 end) AS `produksi_berhasil`, sum(case when `ph`.`status` = 'gagal' then 1 else 0 end) AS `produksi_gagal`, avg(`ph`.`durasi_produksi`) AS `rata_rata_durasi` FROM (`tbl_produksi_harian` `ph` join `tbl_dapur` `d` on(`ph`.`id_dapur` = `d`.`id_dapur`)) GROUP BY year(`ph`.`tanggal_produksi`), month(`ph`.`tanggal_produksi`), `d`.`id_dapur` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `vw_laporan_produksi_harian`
--
DROP TABLE IF EXISTS `vw_laporan_produksi_harian`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_produksi_harian`  AS SELECT cast(`ph`.`tanggal_produksi` as date) AS `tanggal`, `d`.`nama_dapur` AS `nama_dapur`, `d`.`id_dapur` AS `id_dapur`, count(`ph`.`id_produksi`) AS `total_produksi`, sum(`ph`.`jumlah_porsi`) AS `total_porsi`, sum(case when `ph`.`status` = 'selesai' then 1 else 0 end) AS `produksi_berhasil`, sum(case when `ph`.`status` = 'gagal' then 1 else 0 end) AS `produksi_gagal`, avg(`ph`.`durasi_produksi`) AS `rata_rata_durasi`, group_concat(distinct `m`.`nama_menu` separator ', ') AS `menu_diproduksi` FROM ((`tbl_produksi_harian` `ph` join `tbl_dapur` `d` on(`ph`.`id_dapur` = `d`.`id_dapur`)) join `tbl_menu` `m` on(`ph`.`id_menu` = `m`.`id_menu`)) GROUP BY cast(`ph`.`tanggal_produksi` as date), `d`.`id_dapur` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `vw_laporan_produksi_mingguan`
--
DROP TABLE IF EXISTS `vw_laporan_produksi_mingguan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_produksi_mingguan`  AS SELECT yearweek(`ph`.`tanggal_produksi`,1) AS `minggu_tahun`, cast(`ph`.`tanggal_produksi` - interval weekday(`ph`.`tanggal_produksi`) day as date) AS `tanggal_mulai_minggu`, cast(`ph`.`tanggal_produksi` - interval weekday(`ph`.`tanggal_produksi`) day + interval 6 day as date) AS `tanggal_akhir_minggu`, `d`.`nama_dapur` AS `nama_dapur`, `d`.`id_dapur` AS `id_dapur`, count(`ph`.`id_produksi`) AS `total_produksi`, sum(`ph`.`jumlah_porsi`) AS `total_porsi`, sum(case when `ph`.`status` = 'selesai' then 1 else 0 end) AS `produksi_berhasil`, sum(case when `ph`.`status` = 'gagal' then 1 else 0 end) AS `produksi_gagal`, avg(`ph`.`durasi_produksi`) AS `rata_rata_durasi` FROM (`tbl_produksi_harian` `ph` join `tbl_dapur` `d` on(`ph`.`id_dapur` = `d`.`id_dapur`)) GROUP BY yearweek(`ph`.`tanggal_produksi`,1), `d`.`id_dapur` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `vw_laporan_stok_harian`
--
DROP TABLE IF EXISTS `vw_laporan_stok_harian`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_stok_harian`  AS SELECT cast(`rs`.`tanggal_transaksi` as date) AS `tanggal`, `bb`.`id_bahan` AS `id_bahan`, `bb`.`nama_bahan` AS `nama_bahan`, `bb`.`kategori` AS `kategori`, `bb`.`satuan` AS `satuan`, sum(case when `rs`.`tipe_transaksi` = 'masuk' then `rs`.`jumlah_stok` else 0 end) AS `stok_masuk`, sum(case when `rs`.`tipe_transaksi` = 'keluar' then `rs`.`jumlah_stok` else 0 end) AS `stok_keluar`, (select `tbl_riwayat_stok`.`stok_sesudah` from `tbl_riwayat_stok` where `tbl_riwayat_stok`.`id_bahan` = `bb`.`id_bahan` order by `tbl_riwayat_stok`.`id_riwayat` desc limit 1) AS `stok_akhir` FROM (`tbl_riwayat_stok` `rs` join `tbl_bahan_baku` `bb` on(`rs`.`id_bahan` = `bb`.`id_bahan`)) GROUP BY cast(`rs`.`tanggal_transaksi` as date), `bb`.`id_bahan` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `vw_laporan_stok_mingguan`
--
DROP TABLE IF EXISTS `vw_laporan_stok_mingguan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_laporan_stok_mingguan`  AS SELECT yearweek(`rs`.`tanggal_transaksi`,1) AS `minggu_tahun`, cast(`rs`.`tanggal_transaksi` - interval weekday(`rs`.`tanggal_transaksi`) day as date) AS `tanggal_mulai_minggu`, `bb`.`id_bahan` AS `id_bahan`, `bb`.`nama_bahan` AS `nama_bahan`, `bb`.`kategori` AS `kategori`, sum(case when `rs`.`tipe_transaksi` = 'masuk' then `rs`.`jumlah_stok` else 0 end) AS `total_stok_masuk`, sum(case when `rs`.`tipe_transaksi` = 'keluar' then `rs`.`jumlah_stok` else 0 end) AS `total_stok_keluar` FROM (`tbl_riwayat_stok` `rs` join `tbl_bahan_baku` `bb` on(`rs`.`id_bahan` = `bb`.`id_bahan`)) GROUP BY yearweek(`rs`.`tanggal_transaksi`,1), `bb`.`id_bahan` ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  ADD PRIMARY KEY (`id_absensi`),
  ADD KEY `id_karyawan` (`id_karyawan`);

--
-- Indeks untuk tabel `tbl_admin_log`
--
ALTER TABLE `tbl_admin_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_admin` (`id_super_admin`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indeks untuk tabel `tbl_bahan_baku`
--
ALTER TABLE `tbl_bahan_baku`
  ADD PRIMARY KEY (`id_bahan`),
  ADD KEY `idx_nama` (`nama_bahan`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `tbl_dapur`
--
ALTER TABLE `tbl_dapur`
  ADD PRIMARY KEY (`id_dapur`),
  ADD KEY `idx_pengelola` (`id_pengelola`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `tbl_detail_pembelanjaan`
--
ALTER TABLE `tbl_detail_pembelanjaan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `idx_pembelanjaan` (`id_pembelanjaan`),
  ADD KEY `idx_bahan` (`id_bahan`);

--
-- Indeks untuk tabel `tbl_dokumentasi_karyawan`
--
ALTER TABLE `tbl_dokumentasi_karyawan`
  ADD PRIMARY KEY (`id_dokumentasi`);

--
-- Indeks untuk tabel `tbl_karyawan`
--
ALTER TABLE `tbl_karyawan`
  ADD PRIMARY KEY (`id_karyawan`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_role` (`id_role`),
  ADD KEY `idx_pengelola` (`id_pengelola`),
  ADD KEY `idx_dapur` (`id_dapur`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `tbl_laporan`
--
ALTER TABLE `tbl_laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD UNIQUE KEY `kode_laporan` (`kode_laporan`),
  ADD KEY `id_pengelola` (`id_pengelola`),
  ADD KEY `id_dapur` (`id_dapur`),
  ADD KEY `idx_kode` (`kode_laporan`),
  ADD KEY `idx_tipe` (`tipe_laporan`),
  ADD KEY `idx_kategori` (`kategori_laporan`),
  ADD KEY `idx_tanggal` (`tanggal_mulai`,`tanggal_akhir`),
  ADD KEY `idx_status` (`status_laporan`),
  ADD KEY `idx_laporan_tipe` (`tipe_laporan`,`kategori_laporan`);

--
-- Indeks untuk tabel `tbl_log_aktivitas`
--
ALTER TABLE `tbl_log_aktivitas`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `tbl_menu`
--
ALTER TABLE `tbl_menu`
  ADD PRIMARY KEY (`id_menu`),
  ADD KEY `idx_pengelola` (`id_pengelola`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `tbl_pembelanjaan`
--
ALTER TABLE `tbl_pembelanjaan`
  ADD PRIMARY KEY (`id_pembelanjaan`),
  ADD UNIQUE KEY `kode_pembelanjaan` (`kode_pembelanjaan`),
  ADD KEY `id_pengelola` (`id_pengelola`),
  ADD KEY `idx_dapur` (`id_dapur`),
  ADD KEY `idx_tanggal` (`tanggal_pembelian`),
  ADD KEY `idx_kode` (`kode_pembelanjaan`),
  ADD KEY `idx_status` (`status_pembayaran`);

--
-- Indeks untuk tabel `tbl_pengaduan`
--
ALTER TABLE `tbl_pengaduan`
  ADD PRIMARY KEY (`id_pengaduan`);

--
-- Indeks untuk tabel `tbl_pengelola_dapur`
--
ALTER TABLE `tbl_pengelola_dapur`
  ADD PRIMARY KEY (`id_pengelola`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_role` (`id_role`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `tbl_produksi_harian`
--
ALTER TABLE `tbl_produksi_harian`
  ADD PRIMARY KEY (`id_produksi`),
  ADD UNIQUE KEY `idx_kode_produksi_unique` (`kode_produksi`),
  ADD KEY `idx_menu` (`id_menu`),
  ADD KEY `idx_karyawan` (`id_karyawan`),
  ADD KEY `idx_dapur` (`id_dapur`),
  ADD KEY `idx_tanggal` (`tanggal_produksi`),
  ADD KEY `idx_kode` (`kode_produksi`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `tbl_resep_menu`
--
ALTER TABLE `tbl_resep_menu`
  ADD PRIMARY KEY (`id_resep`),
  ADD KEY `idx_menu` (`id_menu`),
  ADD KEY `idx_bahan` (`id_bahan`);

--
-- Indeks untuk tabel `tbl_riwayat_stok`
--
ALTER TABLE `tbl_riwayat_stok`
  ADD PRIMARY KEY (`id_riwayat`),
  ADD KEY `id_pembelanjaan` (`id_pembelanjaan`),
  ADD KEY `idx_bahan` (`id_bahan`),
  ADD KEY `idx_tanggal` (`tanggal_transaksi`),
  ADD KEY `idx_tipe` (`tipe_transaksi`);

--
-- Indeks untuk tabel `tbl_role`
--
ALTER TABLE `tbl_role`
  ADD PRIMARY KEY (`id_role`),
  ADD UNIQUE KEY `nama_role` (`nama_role`);

--
-- Indeks untuk tabel `tbl_super_admin`
--
ALTER TABLE `tbl_super_admin`
  ADD PRIMARY KEY (`id_super_admin`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  MODIFY `id_absensi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tbl_admin_log`
--
ALTER TABLE `tbl_admin_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_bahan_baku`
--
ALTER TABLE `tbl_bahan_baku`
  MODIFY `id_bahan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `tbl_dapur`
--
ALTER TABLE `tbl_dapur`
  MODIFY `id_dapur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tbl_detail_pembelanjaan`
--
ALTER TABLE `tbl_detail_pembelanjaan`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `tbl_dokumentasi_karyawan`
--
ALTER TABLE `tbl_dokumentasi_karyawan`
  MODIFY `id_dokumentasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `tbl_karyawan`
--
ALTER TABLE `tbl_karyawan`
  MODIFY `id_karyawan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `tbl_laporan`
--
ALTER TABLE `tbl_laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `tbl_log_aktivitas`
--
ALTER TABLE `tbl_log_aktivitas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_menu`
--
ALTER TABLE `tbl_menu`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tbl_pembelanjaan`
--
ALTER TABLE `tbl_pembelanjaan`
  MODIFY `id_pembelanjaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tbl_pengaduan`
--
ALTER TABLE `tbl_pengaduan`
  MODIFY `id_pengaduan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_pengelola_dapur`
--
ALTER TABLE `tbl_pengelola_dapur`
  MODIFY `id_pengelola` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `tbl_produksi_harian`
--
ALTER TABLE `tbl_produksi_harian`
  MODIFY `id_produksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `tbl_resep_menu`
--
ALTER TABLE `tbl_resep_menu`
  MODIFY `id_resep` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tbl_riwayat_stok`
--
ALTER TABLE `tbl_riwayat_stok`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `tbl_role`
--
ALTER TABLE `tbl_role`
  MODIFY `id_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `tbl_super_admin`
--
ALTER TABLE `tbl_super_admin`
  MODIFY `id_super_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tbl_absensi`
--
ALTER TABLE `tbl_absensi`
  ADD CONSTRAINT `tbl_absensi_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `tbl_karyawan` (`id_karyawan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_admin_log`
--
ALTER TABLE `tbl_admin_log`
  ADD CONSTRAINT `tbl_admin_log_ibfk_1` FOREIGN KEY (`id_super_admin`) REFERENCES `tbl_super_admin` (`id_super_admin`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_dapur`
--
ALTER TABLE `tbl_dapur`
  ADD CONSTRAINT `tbl_dapur_ibfk_1` FOREIGN KEY (`id_pengelola`) REFERENCES `tbl_pengelola_dapur` (`id_pengelola`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_detail_pembelanjaan`
--
ALTER TABLE `tbl_detail_pembelanjaan`
  ADD CONSTRAINT `tbl_detail_pembelanjaan_ibfk_1` FOREIGN KEY (`id_pembelanjaan`) REFERENCES `tbl_pembelanjaan` (`id_pembelanjaan`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_detail_pembelanjaan_ibfk_2` FOREIGN KEY (`id_bahan`) REFERENCES `tbl_bahan_baku` (`id_bahan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_karyawan`
--
ALTER TABLE `tbl_karyawan`
  ADD CONSTRAINT `tbl_karyawan_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `tbl_role` (`id_role`),
  ADD CONSTRAINT `tbl_karyawan_ibfk_2` FOREIGN KEY (`id_pengelola`) REFERENCES `tbl_pengelola_dapur` (`id_pengelola`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_karyawan_ibfk_3` FOREIGN KEY (`id_dapur`) REFERENCES `tbl_dapur` (`id_dapur`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `tbl_laporan`
--
ALTER TABLE `tbl_laporan`
  ADD CONSTRAINT `tbl_laporan_ibfk_1` FOREIGN KEY (`id_pengelola`) REFERENCES `tbl_pengelola_dapur` (`id_pengelola`) ON DELETE SET NULL,
  ADD CONSTRAINT `tbl_laporan_ibfk_2` FOREIGN KEY (`id_dapur`) REFERENCES `tbl_dapur` (`id_dapur`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `tbl_menu`
--
ALTER TABLE `tbl_menu`
  ADD CONSTRAINT `tbl_menu_ibfk_1` FOREIGN KEY (`id_pengelola`) REFERENCES `tbl_pengelola_dapur` (`id_pengelola`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_pembelanjaan`
--
ALTER TABLE `tbl_pembelanjaan`
  ADD CONSTRAINT `tbl_pembelanjaan_ibfk_1` FOREIGN KEY (`id_dapur`) REFERENCES `tbl_dapur` (`id_dapur`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_pembelanjaan_ibfk_2` FOREIGN KEY (`id_pengelola`) REFERENCES `tbl_pengelola_dapur` (`id_pengelola`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_pengelola_dapur`
--
ALTER TABLE `tbl_pengelola_dapur`
  ADD CONSTRAINT `tbl_pengelola_dapur_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `tbl_role` (`id_role`);

--
-- Ketidakleluasaan untuk tabel `tbl_produksi_harian`
--
ALTER TABLE `tbl_produksi_harian`
  ADD CONSTRAINT `tbl_produksi_harian_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `tbl_menu` (`id_menu`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_produksi_harian_ibfk_2` FOREIGN KEY (`id_karyawan`) REFERENCES `tbl_karyawan` (`id_karyawan`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_produksi_harian_ibfk_3` FOREIGN KEY (`id_dapur`) REFERENCES `tbl_dapur` (`id_dapur`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_resep_menu`
--
ALTER TABLE `tbl_resep_menu`
  ADD CONSTRAINT `tbl_resep_menu_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `tbl_menu` (`id_menu`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_resep_menu_ibfk_2` FOREIGN KEY (`id_bahan`) REFERENCES `tbl_bahan_baku` (`id_bahan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tbl_riwayat_stok`
--
ALTER TABLE `tbl_riwayat_stok`
  ADD CONSTRAINT `tbl_riwayat_stok_ibfk_1` FOREIGN KEY (`id_bahan`) REFERENCES `tbl_bahan_baku` (`id_bahan`) ON DELETE CASCADE,
  ADD CONSTRAINT `tbl_riwayat_stok_ibfk_2` FOREIGN KEY (`id_pembelanjaan`) REFERENCES `tbl_pembelanjaan` (`id_pembelanjaan`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
