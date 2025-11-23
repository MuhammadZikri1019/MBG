<?php
// admin/generate-laporan.php
require_once '../koneksi.php';
checkRole(['super_admin']);

$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$allowed_jenis = ['produksi', 'keuangan', 'stok', 'karyawan', 'keseluruhan'];

if (!in_array($jenis, $allowed_jenis)) {
    echo "<script>alert('Jenis laporan tidak valid!'); window.location.href='laporan-sistem.php';</script>";
    exit;
}

// Set Period (Default: Current Month)
$tanggal_mulai = date('Y-m-01');
$tanggal_akhir = date('Y-m-t');

// Prepare Data Snapshot
$snapshot_data = [];

switch ($jenis) {
    case 'keuangan':
        // Get Total Pembelanjaan (Selesai)
        $q = "SELECT p.*, d.nama_dapur 
              FROM tbl_pembelanjaan p 
              JOIN tbl_dapur d ON p.id_dapur = d.id_dapur
              WHERE p.status = 'selesai' 
              AND p.tanggal_pembelian BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'";
        $res = mysqli_query($conn, $q);
        $total_pengeluaran = 0;
        $items = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $total_pengeluaran += $row['total_pembelian'];
            $items[] = $row;
        }
        $snapshot_data = [
            'summary' => ['total_pengeluaran' => $total_pengeluaran],
            'details' => $items
        ];
        break;

    case 'stok':
        // Get Current Stock
        $q = "SELECT * FROM tbl_bahan_baku ORDER BY nama_bahan ASC";
        $res = mysqli_query($conn, $q);
        $items = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $items[] = $row;
        }
        $snapshot_data = [
            'summary' => ['total_item' => count($items)],
            'details' => $items
        ];
        break;

    case 'karyawan':
        // Get Attendance Summary
        $q = "SELECT k.nama, k.bagian, 
              COUNT(CASE WHEN a.status_kehadiran = 'hadir' THEN 1 END) as hadir,
              COUNT(CASE WHEN a.status_kehadiran = 'izin' THEN 1 END) as izin,
              COUNT(CASE WHEN a.status_kehadiran = 'sakit' THEN 1 END) as sakit,
              COUNT(CASE WHEN a.status_kehadiran = 'alpha' THEN 1 END) as alpha
              FROM tbl_karyawan k
              LEFT JOIN tbl_absensi a ON k.id_karyawan = a.id_karyawan 
              AND a.tanggal BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'
              WHERE k.status = 'aktif'
              GROUP BY k.id_karyawan";
        $res = mysqli_query($conn, $q);
        $items = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $items[] = $row;
        }
        $snapshot_data = [
            'summary' => ['total_karyawan' => count($items)],
            'details' => $items
        ];
        break;

    case 'produksi':
        // Get Menu List
        $q = "SELECT m.*, d.nama_dapur 
              FROM tbl_menu m
              JOIN tbl_pengelola_dapur pd ON m.id_pengelola = pd.id_pengelola
              JOIN tbl_dapur d ON pd.id_pengelola = d.id_pengelola
              ORDER BY m.tanggal_menu DESC";
        $res = mysqli_query($conn, $q);
        $items = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $items[] = $row;
        }
        $snapshot_data = [
            'summary' => ['total_menu' => count($items)],
            'details' => $items
        ];
        break;
}

// Encode Snapshot
$konten_laporan = mysqli_real_escape_string($conn, json_encode($snapshot_data));
$kode_laporan = "LAP-" . strtoupper(substr($jenis, 0, 3)) . "-" . date('YmdHis');
$judul_laporan = "Laporan " . ucfirst($jenis) . " Periode " . date('F Y');
$dibuat_oleh = $_SESSION['user_id'];
$dibuat_oleh_tipe = 'super_admin';

// Insert into tbl_laporan
$query = "INSERT INTO tbl_laporan (kode_laporan, judul_laporan, tipe_laporan, kategori_laporan, konten_laporan, tanggal_mulai, tanggal_akhir, status_laporan, dibuat_oleh, dibuat_oleh_tipe) 
          VALUES ('$kode_laporan', '$judul_laporan', 'bulanan', '$jenis', '$konten_laporan', '$tanggal_mulai', '$tanggal_akhir', 'final', '$dibuat_oleh', '$dibuat_oleh_tipe')";

if (mysqli_query($conn, $query)) {
    $id_laporan = mysqli_insert_id($conn);
    header("Location: detail-laporan.php?id=$id_laporan");
} else {
    echo "Gagal generate laporan: " . mysqli_error($conn);
}
?>
