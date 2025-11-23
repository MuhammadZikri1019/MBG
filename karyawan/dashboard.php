<?php
require_once '../koneksi.php';
checkRole(['karyawan']);

$id_karyawan = $_SESSION['user_id'];
$today = date('Y-m-d');

// 1. Ambil Data Karyawan & Dapur
$query_user = "SELECT k.*, d.nama_dapur, d.alamat as alamat_dapur 
               FROM tbl_karyawan k
               LEFT JOIN tbl_dapur d ON k.id_dapur = d.id_dapur
               WHERE k.id_karyawan = '$id_karyawan'";
$result_user = mysqli_query($conn, $query_user);
$user = mysqli_fetch_assoc($result_user);
// 1.5 Cek Hari Libur Rutin (Mingguan)
$is_libur = false;
$libur_data = [];

$days_id = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];
$today_name = $days_id[date('l')];

if (!empty($user['hari_libur'])) {
    $user_libur_days = explode(',', $user['hari_libur']);
    if (in_array($today_name, $user_libur_days)) {
        $is_libur = true;
        $libur_data = ['keterangan' => 'Hari Libur Rutin (' . $today_name . ')'];
    }
}
// 2. Handle Absensi Action
$message = '';
$message_type = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'absen_masuk') {
        $jam_masuk = date('H:i:s');
        $query_absen = "INSERT INTO tbl_absensi (id_karyawan, tanggal, jam_masuk, status_kehadiran) 
                        VALUES ('$id_karyawan', '$today', '$jam_masuk', 'hadir')";
        if (mysqli_query($conn, $query_absen)) {
            $message = 'Berhasil absen masuk! Selamat bekerja.';
            $message_type = 'success';
        } else {
            $message = 'Gagal absen masuk: ' . mysqli_error($conn);
            $message_type = 'danger';
        }
    } elseif ($_POST['action'] == 'absen_keluar') {
        $jam_keluar = date('H:i:s');
        
        // Hitung durasi kerja
        $query_cek = "SELECT jam_masuk FROM tbl_absensi WHERE id_karyawan = '$id_karyawan' AND tanggal = '$today'";
        $res_cek = mysqli_query($conn, $query_cek);
        $row_cek = mysqli_fetch_assoc($res_cek);
        
        $masuk = strtotime($row_cek['jam_masuk']);
        $keluar = strtotime($jam_keluar);
        $durasi = round(abs($keluar - $masuk) / 3600, 2);
        
        $query_update = "UPDATE tbl_absensi 
                         SET jam_keluar = '$jam_keluar', total_jam_kerja = '$durasi' 
                         WHERE id_karyawan = '$id_karyawan' AND tanggal = '$today'";
        if (mysqli_query($conn, $query_update)) {
            $message = 'Berhasil absen keluar! Terima kasih atas kerja kerasnya.';
            $message_type = 'success';
        } else {
            $message = 'Gagal absen keluar: ' . mysqli_error($conn);
            $message_type = 'danger';
        }
    } elseif ($_POST['action'] == 'selesai_antar') {
        $id_menu = escape($_POST['id_menu']);
        $query = "UPDATE tbl_menu SET status_pengantaran = 'selesai' WHERE id_menu = '$id_menu'";
        if (mysqli_query($conn, $query)) {
            $message = 'Pengantaran berhasil diselesaikan!';
            $message_type = 'success';
        }
    }
}

// 3. Ambil Data Absensi Hari Ini
$query_today = "SELECT * FROM tbl_absensi WHERE id_karyawan = '$id_karyawan' AND tanggal = '$today'";
$result_today = mysqli_query($conn, $query_today);
$absen_today = mysqli_fetch_assoc($result_today);

// 4. Ambil Statistik Absensi Bulan Ini
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');
$query_stats = "SELECT COUNT(*) as total_hadir, SUM(total_jam_kerja) as total_jam 
                FROM tbl_absensi 
                WHERE id_karyawan = '$id_karyawan' 
                AND tanggal BETWEEN '$month_start' AND '$month_end'";
$result_stats = mysqli_query($conn, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

// 5. Ambil Tugas (Khusus Pengantar)
$is_pengantar = (strtolower($user['bagian']) == 'pengantar');
$delivery_tasks = [];
if ($is_pengantar && $user['id_dapur']) {
    $q_dapur = "SELECT id_pengelola FROM tbl_dapur WHERE id_dapur = '{$user['id_dapur']}'";
    $r_dapur = mysqli_query($conn, $q_dapur);
    $d_dapur = mysqli_fetch_assoc($r_dapur);
    
    if ($d_dapur) {
        $id_pengelola = $d_dapur['id_pengelola'];
        $query_menu = "SELECT * FROM tbl_menu 
                       WHERE id_pengelola = '$id_pengelola' 
                       AND tanggal_menu = '$today'
                       ORDER BY status_pengantaran ASC";
        $result_menu = mysqli_query($conn, $query_menu);
        while ($row = mysqli_fetch_assoc($result_menu)) {
            $delivery_tasks[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Karyawan - MBG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="../assets/img/logo.png" alt="MBG Logo" class="logo-image">
            </div>
            <h4>MBG System</h4>
            <small>Karyawan Panel</small>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php" class="active">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="dokumentasi.php">
                <i class="bi bi-journal-text"></i>
                <span>Dokumentasi</span>
            </a>
            <a href="profil.php">
                <i class="bi bi-person-circle"></i>
                <span>Profil</span>
            </a>
            <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div>
                <h4>Dashboard Karyawan</h4>
                <small><i class="bi bi-calendar3 me-2"></i><?= date('l, d F Y') ?></small>
            </div>
            <div class="user-profile">
                <a href="profil.php" class="text-decoration-none text-dark d-flex align-items-center">
                    <div class="text-end me-3">
                        <div class="fw-bold"><?= $_SESSION['user_name'] ?></div>
                        <small class="text-muted"><?= ucfirst($_SESSION['bagian']) ?></small>
                    </div>
                    <div class="user-avatar">
                        <?php if (isset($_SESSION['foto_profil']) && $_SESSION['foto_profil']): ?>
                            <img src="../assets/img/profil/<?= $_SESSION['foto_profil'] ?>" alt="Profil" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show animate-alert" role="alert">
                <i class="bi bi-<?= $message_type == 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2"></i>
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card primary">
                    <div class="icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h3><?= $stats['total_hadir'] ?? 0 ?></h3>
                    <p>Hadir</p>
                </div>
            </div>
            
            <div class="col-6 col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card success">
                    <div class="icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h3><?= number_format($stats['total_jam'] ?? 0, 1) ?></h3>
                    <p>Jam Kerja</p>
                </div>
            </div>
            
            <div class="col-6 col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card info">
                    <div class="icon">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </div>
                    <h3><?= isset($absen_today['jam_masuk']) ? date('H:i', strtotime($absen_today['jam_masuk'])) : '--:--' ?></h3>
                    <p>Masuk</p>
                </div>
            </div>
            
            <div class="col-6 col-xl-3 col-lg-6 col-md-6">
                <div class="stat-card warning">
                    <div class="icon">
                        <i class="bi bi-box-arrow-left"></i>
                    </div>
                    <h3><?= isset($absen_today['jam_keluar']) ? date('H:i', strtotime($absen_today['jam_keluar'])) : '--:--' ?></h3>
                    <p>Keluar</p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Absensi Card -->
            <div class="col-lg-4">
                <div class="chart-card">
                    <h5><i class="bi bi-fingerprint me-2"></i>Absensi Hari Ini</h5>
                    <div class="mt-4">
                        <?php if ($is_libur): ?>
                            <div class="alert alert-info mb-0 text-center py-4">
                                <i class="bi bi-calendar-event display-4 d-block mb-3 text-info"></i>
                                <h5 class="alert-heading">Hari Libur</h5>
                                <p class="mb-0"><?= $libur_data['keterangan'] ?></p>
                            </div>
                        <?php elseif (!$absen_today): ?>
                            <div class="alert alert-warning mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Anda belum absen masuk hari ini
                            </div>
                            <form method="POST">
                                <input type="hidden" name="action" value="absen_masuk">
                                <button type="submit" class="btn btn-primary w-100 py-3">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Absen Masuk Sekarang
                                </button>
                            </form>
                        <?php elseif ($absen_today['jam_masuk'] && !$absen_today['jam_keluar']): ?>
                            <div class="alert alert-success mb-3">
                                <i class="bi bi-check-circle me-2"></i>
                                Anda sudah absen masuk pada <?= date('H:i', strtotime($absen_today['jam_masuk'])) ?>
                            </div>
                            <form method="POST">
                                <input type="hidden" name="action" value="absen_keluar">
                                <button type="submit" class="btn btn-danger w-100 py-3">
                                    <i class="bi bi-box-arrow-left me-2"></i>Absen Keluar Sekarang
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="bi bi-check-all me-2"></i>
                                Absensi hari ini telah selesai<br>
                                <small class="text-muted">
                                    Masuk: <?= date('H:i', strtotime($absen_today['jam_masuk'])) ?> | 
                                    Keluar: <?= date('H:i', strtotime($absen_today['jam_keluar'])) ?> 
                                    (<?= $absen_today['total_jam_kerja'] ?? 0 ?> jam)
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tasks / Activity -->
            <div class="col-lg-8">
                <?php if ($is_pengantar): ?>
                    <div class="table-card">
                        <h5><i class="bi bi-truck me-2"></i>Daftar Pengantaran Hari Ini</h5>
                        <?php if (count($delivery_tasks) > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Menu</th>
                                            <th>Deskripsi</th>
                                            <th>Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($delivery_tasks as $task): ?>
                                            <tr>
                                                <td><strong><?= $task['nama_menu'] ?></strong></td>
                                                <td><small><?= substr($task['deskripsi'], 0, 50) ?>...</small></td>
                                                <td>
                                                    <?php if ($task['status_pengantaran'] == 'belum_diantar'): ?>
                                                        <span class="badge bg-secondary">Belum Diantar</span>
                                                    <?php elseif ($task['status_pengantaran'] == 'proses'): ?>
                                                        <span class="badge bg-warning text-dark">Proses</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Selesai</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($task['status_pengantaran'] != 'selesai'): ?>
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('Selesaikan pengantaran?')">
                                                            <input type="hidden" name="action" value="selesai_antar">
                                                            <input type="hidden" name="id_menu" value="<?= $task['id_menu'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                <i class="bi bi-check-lg"></i> Selesai
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted"><i class="bi bi-check-all"></i> Terkirim</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h5>Tidak Ada Tugas Pengantaran</h5>
                                <p>Belum ada jadwal pengantaran untuk hari ini</p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="chart-card text-center py-5">
                        <i class="bi bi-emoji-smile display-1 text-primary opacity-50 mb-4"></i>
                        <h4>Selamat Bekerja, <?= explode(' ', $user['nama'])[0] ?>!</h4>
                        <p class="text-muted mb-0">
                            Tetap semangat dan jaga kualitas kerja.<br>
                            Jangan lupa untuk melakukan absensi tepat waktu.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
    <script>
        console.log('👤 Karyawan Dashboard - Loaded Successfully!');
    </script>
</body>
</html>