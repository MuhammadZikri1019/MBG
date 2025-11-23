<?php
// pengelola/karyawan.php
require_once '../koneksi.php';
checkRole(['pengelola']);

$id_pengelola = $_SESSION['user_id'];

// Proses Tambah Karyawan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $id_dapur = escape($_POST['id_dapur']);
    $nama = escape($_POST['nama']);
    $email = escape($_POST['email']);
    $password = escape($_POST['password']);
    $no_telepon = escape($_POST['no_telepon']);
    $alamat = escape($_POST['alamat']);
    $bagian = escape($_POST['bagian']);
    $status = escape($_POST['status']);
    $jam_masuk = escape($_POST['jam_masuk']);
    $jam_keluar = escape($_POST['jam_keluar']);
    $hari_libur = isset($_POST['hari_libur']) ? implode(',', $_POST['hari_libur']) : '';
    
    // Validasi: Pastikan dapur milik pengelola ini
    $check_dapur = "SELECT id_dapur FROM tbl_dapur WHERE id_dapur = '$id_dapur' AND id_pengelola = '$id_pengelola'";
    if (mysqli_num_rows(mysqli_query($conn, $check_dapur)) == 0) {
        $error = "Dapur tidak valid atau bukan milik Anda!";
    } else {
        // Check email duplicate
        $check_email = "SELECT * FROM tbl_karyawan WHERE email = '$email'";
        if (mysqli_num_rows(mysqli_query($conn, $check_email)) > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            $query = "INSERT INTO tbl_karyawan (id_dapur, id_pengelola, nama, email, password, no_telepon, alamat, bagian, status, jam_masuk, jam_keluar, hari_libur) 
                      VALUES ('$id_dapur', '$id_pengelola', '$nama', '$email', '$password', '$no_telepon', '$alamat', '$bagian', '$status', '$jam_masuk', '$jam_keluar', '$hari_libur')";
            
            if (mysqli_query($conn, $query)) {
                $success = "Karyawan berhasil ditambahkan!";
                
                // Kirim Email Notifikasi
                $to = $email;
                $subject = "Akun Karyawan Baru - MBG System";
                $message = "
                Halo $nama,
                
                Akun Anda telah dibuat di MBG System. Berikut adalah detail login Anda:
                
                Email: $email
                Password: $password
                
                Silakan login dan segera ganti password Anda.
                
                Salam,
                Tim MBG System
                ";
                $headers = "From: no-reply@mbgsystem.com";
                
                // Coba kirim email (gunakan @ untuk menyembunyikan warning jika SMTP belum disetting)
                if (@mail($to, $subject, $message, $headers)) {
                    $success .= " Email notifikasi berhasil dikirim.";
                } else {
                    $success .= " (Catatan: Email notifikasi gagal dikirim karena server lokal belum dikonfigurasi SMTP, tapi data karyawan sudah tersimpan).";
                }
                
            } else {
                $error = "Gagal menambahkan karyawan: " . mysqli_error($conn);
            }
        }
    }
}

// Proses Update Karyawan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id_karyawan = escape($_POST['id_karyawan']);
    $id_dapur = escape($_POST['id_dapur']);
    $nama = escape($_POST['nama']);
    $email = escape($_POST['email']);
    $password = escape($_POST['password']);
    $no_telepon = escape($_POST['no_telepon']);
    $alamat = escape($_POST['alamat']);
    $bagian = escape($_POST['bagian']);
    $status = escape($_POST['status']);
    $jam_masuk = escape($_POST['jam_masuk']);
    $jam_keluar = escape($_POST['jam_keluar']);
    $hari_libur = isset($_POST['hari_libur']) ? implode(',', $_POST['hari_libur']) : '';
    
    // Validasi: Pastikan karyawan milik salah satu dapur pengelola ini
    $check_access = "SELECT k.id_karyawan 
                     FROM tbl_karyawan k
                     JOIN tbl_dapur d ON k.id_dapur = d.id_dapur
                     WHERE k.id_karyawan = '$id_karyawan' AND d.id_pengelola = '$id_pengelola'";
    
    if (mysqli_num_rows(mysqli_query($conn, $check_access)) == 0) {
        $error = "Anda tidak memiliki akses untuk mengedit karyawan ini.";
    } else {
         // Validasi: Pastikan dapur tujuan juga milik pengelola ini
        $check_dapur = "SELECT id_dapur FROM tbl_dapur WHERE id_dapur = '$id_dapur' AND id_pengelola = '$id_pengelola'";
        if (mysqli_num_rows(mysqli_query($conn, $check_dapur)) == 0) {
            $error = "Dapur tujuan tidak valid atau bukan milik Anda!";
        } else {
            // Check email duplicate (exclude current user)
            $check_email = "SELECT * FROM tbl_karyawan WHERE email = '$email' AND id_karyawan != '$id_karyawan'";
            if (mysqli_num_rows(mysqli_query($conn, $check_email)) > 0) {
                $error = "Email sudah digunakan karyawan lain!";
            } else {
                // Update with or without password change
                if (!empty($password)) {
                    $query = "UPDATE tbl_karyawan SET 
                              id_dapur = '$id_dapur',
                              nama = '$nama',
                              email = '$email',
                              password = '$password',
                              no_telepon = '$no_telepon',
                              alamat = '$alamat',
                              bagian = '$bagian',
                              status = '$status',
                              jam_masuk = '$jam_masuk',
                              jam_keluar = '$jam_keluar',
                              hari_libur = '$hari_libur'
                              WHERE id_karyawan = '$id_karyawan'";
                } else {
                    $query = "UPDATE tbl_karyawan SET 
                              id_dapur = '$id_dapur',
                              nama = '$nama',
                              email = '$email',
                              no_telepon = '$no_telepon',
                              alamat = '$alamat',
                              bagian = '$bagian',
                              status = '$status',
                              jam_masuk = '$jam_masuk',
                              jam_keluar = '$jam_keluar',
                              hari_libur = '$hari_libur'
                              WHERE id_karyawan = '$id_karyawan'";
                }
                
                if (mysqli_query($conn, $query)) {
                    $success = "Data karyawan berhasil diupdate!";
                } else {
                    $error = "Gagal mengupdate karyawan: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Proses Hapus Karyawan
if (isset($_GET['delete'])) {
    $id_karyawan = escape($_GET['delete']);
    
    // Validasi: Pastikan karyawan milik salah satu dapur pengelola ini
    $check_access = "SELECT k.id_karyawan 
                     FROM tbl_karyawan k
                     JOIN tbl_dapur d ON k.id_dapur = d.id_dapur
                     WHERE k.id_karyawan = '$id_karyawan' AND d.id_pengelola = '$id_pengelola'";
                     
    if (mysqli_num_rows(mysqli_query($conn, $check_access)) > 0) {
        $query = "DELETE FROM tbl_karyawan WHERE id_karyawan = '$id_karyawan'";
        
        if (mysqli_query($conn, $query)) {
            $success = "Karyawan berhasil dihapus!";
        } else {
            $error = "Gagal menghapus karyawan: " . mysqli_error($conn);
        }
    } else {
        $error = "Anda tidak memiliki akses untuk menghapus karyawan ini.";
    }
}

// Ambil data karyawan milik pengelola (melalui relasi dapur)
$query_karyawan = "SELECT k.*, d.nama_dapur, d.alamat as alamat_dapur
                   FROM tbl_karyawan k
                   JOIN tbl_dapur d ON k.id_dapur = d.id_dapur
                   WHERE d.id_pengelola = '$id_pengelola'
                   ORDER BY k.created_at DESC";
$result_karyawan = mysqli_query($conn, $query_karyawan);

// Ambil data dapur milik pengelola untuk dropdown
$query_dapur = "SELECT * FROM tbl_dapur WHERE id_pengelola = '$id_pengelola' AND status = 'aktif' ORDER BY nama_dapur ASC";
$result_dapur = mysqli_query($conn, $query_dapur);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Karyawan - MBG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <style>
        .badge-bagian {
            background: #e3f5fc;
            color: #0c5460;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 11px;
            white-space: nowrap;
            display: inline-block;
            font-weight: 600;
        }
        .table-dapur th {
            font-weight: 600;
            color: var(--text-dark);
            background-color: #f8f9fa;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
            justify-content: center;
        }
        .btn-action {
            border: none;
            background: none;
            padding: 5px 10px;
            border-radius: 5px;
            transition: all 0.2s;
        }
        .btn-edit { color: var(--warning); }
        .btn-edit:hover { background-color: #fff3cd; }
        .btn-delete { color: var(--danger); }
        .btn-delete:hover { background-color: #f8d7da; }
    </style>
</head>
<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
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
            <small>Pengelola Panel</small>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="dapur.php">
                <i class="bi bi-house"></i>
                <span>Kelola Dapur</span>
            </a>
            <a href="karyawan.php" class="active">
                <i class="bi bi-people"></i>
                <span>Karyawan</span>
            </a>
            <a href="absensi.php"><i class="bi bi-calendar-check"></i><span>Absensi Karyawan</span></a>

            <a href="menu.php">
                <i class="bi bi-card-list"></i>
                <span>Menu</span>
            </a>

            <a href="pembelanjaan.php">
                <i class="bi bi-cash-stack"></i>
                <span>Pembelanjaan</span>
            </a>
            <a href="stok.php">
                <i class="bi bi-box-seam"></i>
                <span>Stok Bahan</span>
            </a>
            <a href="dokumentasi.php">
                <i class="bi bi-journal-text"></i>
                <span>Dokumentasi</span>
            </a>
            <a href="laporan.php">
                <i class="bi bi-file-earmark-text"></i>
                <span>Laporan</span>
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
                <h4 class="mb-0">Kelola Karyawan</h4>
                <small class="text-muted">Manajemen data karyawan dapur Anda</small>
            </div>
            <div class="user-profile">
                <a href="profil.php" class="text-decoration-none text-dark d-flex align-items-center">
                    <div class="text-end me-3">
                        <div class="fw-bold"><?= $_SESSION['user_name'] ?></div>
                        <small class="text-muted">Pengelola Dapur</small>
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
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show animate-alert" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show animate-alert" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mb-4">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle me-2"></i>Tambah Karyawan
            </button>
            <div class="input-group" style="width: 300px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0 ps-0" placeholder="Cari karyawan...">
            </div>
        </div>

        <!-- Karyawan Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle table-dapur" id="karyawanTable">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 15%;">Nama Lengkap</th>
                            <th style="width: 15%;">Email</th>
                            <th style="width: 10%;">No Telepon</th>
                            <th style="width: 15%;">Alamat</th>
                            <th style="width: 15%;">Dapur</th>
                            <th style="width: 10%;">Bagian</th>
                            <th style="width: 15%;">Jam Kerja</th>
                            <th style="width: 5%;" class="text-center">Status</th>
                            <th style="width: 10%;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="karyawanTableBody">
                        <?php 
                        $no = 1;
                        if (mysqli_num_rows($result_karyawan) > 0):
                            while ($karyawan = mysqli_fetch_assoc($result_karyawan)): 
                        ?>
                        <tr class="dapur-row" data-name="<?= strtolower($karyawan['nama']) ?>">
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                                <div class="fw-bold"><?= $karyawan['nama'] ?></div>
                            </td>
                            <td>
                                <small><?= $karyawan['email'] ?></small>
                            </td>
                            <td>
                                <small><?= $karyawan['no_telepon'] ?? '-' ?></small>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 150px;" title="<?= $karyawan['alamat'] ?>">
                                    <?= $karyawan['alamat'] ?>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= $karyawan['nama_dapur'] ?? '-' ?></div>
                            </td>
                            <td>
                                <span class="badge-bagian">
                                    <?= ucwords(str_replace('_', ' ', $karyawan['bagian'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column align-items-start gap-1">
                                    <?php if ($karyawan['jam_masuk'] && $karyawan['jam_keluar']): ?>
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-box-arrow-in-right text-success me-1"></i>
                                            <?= date('H:i', strtotime($karyawan['jam_masuk'])) ?>
                                        </span>
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-box-arrow-right text-danger me-1"></i>
                                            <?= date('H:i', strtotime($karyawan['jam_keluar'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Belum diset</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge-status <?= $karyawan['status'] == 'aktif' ? 'bg-success' : ($karyawan['status'] == 'cuti' ? 'bg-warning' : 'bg-danger') ?>">
                                    <?= ucfirst($karyawan['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-edit" onclick='editKaryawan(<?= htmlspecialchars(json_encode($karyawan), ENT_QUOTES, 'UTF-8') ?>)' title="Edit Karyawan">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn-action btn-delete" onclick="deleteKaryawan(<?= $karyawan['id_karyawan'] ?>, '<?= htmlspecialchars($karyawan['nama'], ENT_QUOTES, 'UTF-8') ?>')" title="Hapus Karyawan">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-person-x" style="font-size: 48px;"></i>
                                    <p class="mt-2">Belum ada data karyawan.</p>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Work Hours / Jam Kerja Table -->
        <div class="table-card mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5><i class="bi bi-clock-history"></i> Riwayat Jam Kerja Karyawan</h5>
                <div class="d-flex gap-2">
                    <select id="filterKaryawanJamKerja" class="form-select form-select-sm" style="width: 250px;">
                        <option value="">Semua Karyawan</option>
                        <?php 
                        mysqli_data_seek($result_karyawan, 0);
                        while($k = mysqli_fetch_assoc($result_karyawan)): 
                        ?>
                            <option value="<?= $k['id_karyawan'] ?>"><?= $k['nama'] ?> - <?= $k['bagian'] ?? 'Umum' ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select id="filterPeriodeJamKerja" class="form-select form-select-sm" style="width: 150px;">
                        <option value="7">7 Hari Terakhir</option>
                        <option value="30" selected>30 Hari Terakhir</option>
                        <option value="90">90 Hari Terakhir</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle table-dapur">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 15%;">Tanggal</th>
                            <th style="width: 20%;">Nama Karyawan</th>
                            <th style="width: 15%;">Bagian</th>
                            <th style="width: 10%;" class="text-center">Masuk</th>
                            <th style="width: 10%;" class="text-center">Keluar</th>
                            <th style="width: 10%;" class="text-center">Total</th>
                            <th style="width: 10%;" class="text-center">Status</th>
                            <th style="width: 5%;">Ket</th>
                        </tr>
                    </thead>
                    <tbody id="jamKerjaTableBody">
                        <?php
                        // Query jam kerja karyawan (30 hari terakhir)
                        $query_jamkerja = "SELECT a.*, k.nama, k.bagian, k.hari_libur, d.nama_dapur
                                           FROM tbl_absensi a
                                           INNER JOIN tbl_karyawan k ON a.id_karyawan = k.id_karyawan
                                           LEFT JOIN tbl_dapur d ON k.id_dapur = d.id_dapur
                                           WHERE k.id_pengelola = '$id_pengelola'
                                           AND a.tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                                           ORDER BY a.tanggal DESC, k.nama ASC";
                        $result_jamkerja = mysqli_query($conn, $query_jamkerja);
                        
                        if (mysqli_num_rows($result_jamkerja) > 0):
                            $no = 1;
                            $total_jam = 0;
                            while($jk = mysqli_fetch_assoc($result_jamkerja)):
                                $status_class = $jk['status_kehadiran'] == 'hadir' ? 'bg-success' : 
                                               ($jk['status_kehadiran'] == 'izin' ? 'bg-warning' : 
                                               ($jk['status_kehadiran'] == 'sakit' ? 'bg-info' : 'bg-danger'));
                                $total_jam += ($jk['total_jam_kerja'] ?? 0);
                                
                                // Check for weekly holiday
                                $is_weekly_libur = false;
                                if (!empty($jk['hari_libur'])) {
                                    $days_id = [
                                        'Sunday' => 'Minggu',
                                        'Monday' => 'Senin',
                                        'Tuesday' => 'Selasa',
                                        'Wednesday' => 'Rabu',
                                        'Thursday' => 'Kamis',
                                        'Friday' => 'Jumat',
                                        'Saturday' => 'Sabtu'
                                    ];
                                    $day_name = $days_id[date('l', strtotime($jk['tanggal']))];
                                    $libur_days = explode(',', $jk['hari_libur']);
                                    if (in_array($day_name, $libur_days)) {
                                        $is_weekly_libur = true;
                                    }
                                }
                        ?>
                        <tr data-karyawan-id="<?= $jk['id_karyawan'] ?>" data-tanggal="<?= $jk['tanggal'] ?>">
                            <td class="text-center"><?= $no++ ?></td>
                            <td>
                                <div class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($jk['tanggal'])) ?></div>
                            </td>
                            <td>
                                <div class="fw-bold"><?= $jk['nama'] ?></div>
                            </td>
                            <td>
                                <span class="badge-bagian">
                                    <?= ucwords(str_replace('_', ' ', $jk['bagian'] ?? 'umum')) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($is_weekly_libur): ?>
                                    <span class="badge bg-info text-dark border">Libur</span>
                                <?php elseif ($jk['jam_masuk']): ?>
                                    <span class="badge bg-light text-success border">
                                        <?= date('H:i', strtotime($jk['jam_masuk'])) ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($is_weekly_libur): ?>
                                    <span class="badge bg-info text-dark border">Libur</span>
                                <?php elseif ($jk['jam_keluar']): ?>
                                    <span class="badge bg-light text-danger border">
                                        <?= date('H:i', strtotime($jk['jam_keluar'])) ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($is_weekly_libur): ?>
                                    <span class="text-muted">-</span>
                                <?php elseif ($jk['total_jam_kerja']): ?>
                                    <strong><?= number_format($jk['total_jam_kerja'], 2) ?></strong> jam
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $status_class ?> rounded-pill">
                                    <?= ucfirst($jk['status_kehadiran']) ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= $jk['keterangan'] ? nl2br(htmlspecialchars($jk['keterangan'])) : '-' ?>
                                </small>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        ?>
                        <tr class="table-secondary fw-bold">
                            <td colspan="6" class="text-end">Total Jam Kerja (30 Hari Terakhir):</td>
                            <td class="text-center"><?= number_format($total_jam, 2) ?> jam</td>
                            <td colspan="2"></td>
                        </tr>
                        <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">Belum ada data jam kerja</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty State for Search -->
        <div class="text-center py-5" id="emptyState" style="display: none;">
            <i class="bi bi-search text-muted" style="font-size: 48px;"></i>
            <h5 class="mt-3 text-muted">Tidak ada data ditemukan</h5>
            <p class="text-muted">Coba gunakan kata kunci yang berbeda</p>
        </div>
    </div>

    <!-- Modal Tambah Karyawan -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Karyawan Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control" required 
                                       placeholder="Masukkan nama lengkap">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required 
                                       placeholder="contoh@email.com">
                                <small class="text-muted">Username dan password akan dikirim ke email ini</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required 
                                       placeholder="Minimal 6 karakter" minlength="6">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No Telepon</label>
                                <input type="text" name="no_telepon" class="form-control" 
                                       placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat" class="form-control" rows="2" required 
                                      placeholder="Masukkan alamat lengkap"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Dapur <span class="text-danger">*</span></label>
                                <select name="id_dapur" class="form-select" required>
                                    <option value="">Pilih Dapur</option>
                                    <?php 
                                    mysqli_data_seek($result_dapur, 0);
                                    while ($dapur = mysqli_fetch_assoc($result_dapur)): 
                                    ?>
                                        <option value="<?= $dapur['id_dapur'] ?>">
                                            <?= $dapur['nama_dapur'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Bagian <span class="text-danger">*</span></label>
                                <select name="bagian" class="form-select" required>
                                    <option value="">Pilih Bagian</option>
                                    <option value="tukang_masak">Tukang Masak</option>
                                    <option value="cuci_piring">Tukang Cuci Piring</option>
                                    <option value="pengantar">Pengantar Makanan</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Non Aktif</option>
                                    <option value="cuti">Cuti</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam Masuk</label>
                                <input type="time" name="jam_masuk" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam Keluar</label>
                                <input type="time" name="jam_keluar" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hari Libur Rutin</label>
                            <div class="d-flex flex-wrap gap-3">
                                <?php
                                $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                foreach ($days as $day):
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="hari_libur[]" value="<?= $day ?>" id="add_libur_<?= $day ?>">
                                    <label class="form-check-label" for="add_libur_<?= $day ?>">
                                        <?= $day ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Karyawan -->
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit Data Karyawan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_karyawan" id="edit_id_karyawan">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="nama" id="edit_nama" class="form-control" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="edit_email" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <small class="text-muted">(Kosongkan jika tidak diubah)</small></label>
                                <input type="password" name="password" id="edit_password" class="form-control" 
                                       placeholder="Minimal 6 karakter" minlength="6">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No Telepon</label>
                                <input type="text" name="no_telepon" id="edit_no_telepon" class="form-control">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat" id="edit_alamat" class="form-control" rows="2" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Dapur <span class="text-danger">*</span></label>
                                <select name="id_dapur" id="edit_id_dapur" class="form-select" required>
                                    <option value="">Pilih Dapur</option>
                                    <?php 
                                    mysqli_data_seek($result_dapur, 0);
                                    while ($dapur = mysqli_fetch_assoc($result_dapur)): 
                                    ?>
                                        <option value="<?= $dapur['id_dapur'] ?>">
                                            <?= $dapur['nama_dapur'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Bagian <span class="text-danger">*</span></label>
                                <select name="bagian" id="edit_bagian" class="form-select" required>
                                    <option value="">Pilih Bagian</option>
                                    <option value="tukang_masak">Tukang Masak</option>
                                    <option value="cuci_piring">Tukang Cuci Piring</option>
                                    <option value="pengantar">Pengantar Makanan</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="edit_status" class="form-select" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Non Aktif</option>
                                    <option value="cuti">Cuti</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam Masuk</label>
                                <input type="time" name="jam_masuk" id="edit_jam_masuk" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam Keluar</label>
                                <input type="time" name="jam_keluar" id="edit_jam_keluar" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hari Libur Rutin</label>
                            <div class="d-flex flex-wrap gap-3">
                                <?php
                                $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                foreach ($days as $day):
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="hari_libur[]" value="<?= $day ?>" id="edit_libur_<?= $day ?>">
                                    <label class="form-check-label" for="edit_libur_<?= $day ?>">
                                        <?= $day ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
    <script src="../assets/js/auth.js"></script>
    <script>
        // Search Functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.dapur-row');
            const emptyState = document.getElementById('emptyState');
            const tableCard = document.querySelector('.table-card');
            let visibleCount = 0;
            
            tableRows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                
                if (rowText.includes(searchValue)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            if (visibleCount === 0) {
                tableCard.style.display = 'none';
                emptyState.style.display = 'block';
            } else {
                tableCard.style.display = 'block';
                emptyState.style.display = 'none';
            }
        });

        // Edit Karyawan Function
        function editKaryawan(karyawan) {
            document.getElementById('edit_id_karyawan').value = karyawan.id_karyawan;
            document.getElementById('edit_nama').value = karyawan.nama;
            document.getElementById('edit_email').value = karyawan.email;
            document.getElementById('edit_no_telepon').value = karyawan.no_telepon || '';
            document.getElementById('edit_alamat').value = karyawan.alamat;
            document.getElementById('edit_id_dapur').value = karyawan.id_dapur || '';
            document.getElementById('edit_bagian').value = karyawan.bagian;
            document.getElementById('edit_status').value = karyawan.status;
            document.getElementById('edit_jam_masuk').value = karyawan.jam_masuk || '';
            document.getElementById('edit_jam_keluar').value = karyawan.jam_keluar || '';
            document.getElementById('edit_password').value = '';
            
            // Reset checkboxes
            document.querySelectorAll('input[name="hari_libur[]"]').forEach(cb => cb.checked = false);
            
            // Populate checkboxes
            if (karyawan.hari_libur) {
                const liburDays = karyawan.hari_libur.split(',');
                liburDays.forEach(day => {
                    const checkbox = document.getElementById('edit_libur_' + day);
                    if (checkbox) checkbox.checked = true;
                });
            }
            
            const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
            modal.show();
        }

        // Delete Karyawan Function
        function deleteKaryawan(id, nama) {
            if (confirm(`Apakah Anda yakin ingin menghapus karyawan "${nama}"?\n\nData yang dihapus tidak dapat dikembalikan!`)) {
                window.location.href = `karyawan.php?delete=${id}`;
            }
        }

        // Filter Jam Kerja by Karyawan
        document.getElementById('filterKaryawanJamKerja').addEventListener('change', function() {
            filterJamKerja();
        });

        // Filter Jam Kerja by Periode
        document.getElementById('filterPeriodeJamKerja').addEventListener('change', function() {
            filterJamKerja();
        });

        function filterJamKerja() {
            const filterKaryawan = document.getElementById('filterKaryawanJamKerja').value;
            const filterPeriode = parseInt(document.getElementById('filterPeriodeJamKerja').value);
            const rows = document.querySelectorAll('#jamKerjaTableBody tr[data-karyawan-id]');
            
            let visibleCount = 0;
            let totalJam = 0;
            const today = new Date();
            
            rows.forEach(row => {
                const karyawanId = row.getAttribute('data-karyawan-id');
                const tanggal = new Date(row.getAttribute('data-tanggal'));
                const daysDiff = Math.floor((today - tanggal) / (1000 * 60 * 60 * 24));
                
                let showKaryawan = !filterKaryawan || karyawanId === filterKaryawan;
                let showPeriode = daysDiff <= filterPeriode;
                
                if (showKaryawan && showPeriode) {
                    row.style.display = '';
                    visibleCount++;
                    
                    // Calculate total hours for visible rows
                    const jamCell = row.children[6].textContent;
                    const jamMatch = jamCell.match(/[\d.]+/);
                    if (jamMatch) {
                        totalJam += parseFloat(jamMatch[0]);
                    }
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update total row
            const totalRow = document.querySelector('#jamKerjaTableBody tr.table-secondary');
            if (totalRow) {
                totalRow.children[1].textContent = `Total Jam Kerja (${filterPeriode} Hari Terakhir):`;
                totalRow.children[2].textContent = totalJam.toFixed(2) + ' jam';
                totalRow.style.display = visibleCount > 0 ? '' : 'none';
            }
        }
    </script>
</body>
</html>
