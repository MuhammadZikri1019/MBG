<?php
require_once 'koneksi.php';

// Check if column exists first
$check = mysqli_query($conn, "SHOW COLUMNS FROM tbl_laporan LIKE 'konten_laporan'");
if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE tbl_laporan ADD COLUMN konten_laporan LONGTEXT DEFAULT NULL";
    if (mysqli_query($conn, $sql)) {
        echo "<h1>Berhasil!</h1>";
        echo "<p>Kolom <code>konten_laporan</code> berhasil ditambahkan.</p>";
    } else {
        echo "<h1>Gagal</h1>";
        echo "<p>Error: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<h1>Sudah Ada</h1>";
    echo "<p>Kolom <code>konten_laporan</code> sudah ada.</p>";
}
echo "<p><a href='admin/laporan-sistem.php'>Kembali ke Laporan Sistem</a></p>";
?>
