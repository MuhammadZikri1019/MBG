<?php
// pengelola/absensi.php
require_once '../koneksi.php';
checkRole(['pengelola']);

$id_pengelola = $_SESSION['user_id'];

// Proses Update Absensi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id_absensi = escape($_POST['id_absensi']);
    $tanggal = escape($_POST['tanggal']);
    $jam_masuk = escape($_POST['jam_masuk']);
    $jam_keluar = !empty($_POST['jam_keluar']) ? escape($_POST['jam_keluar']) : NULL;
    $status_kehadiran = escape($_POST['status_kehadiran']);
    $keterangan = !empty($_POST['keterangan']) ? escape($_POST['keterangan']) : NULL;
    
    // Validasi: Pastikan absensi milik karyawan pengelola ini
    $check_access = "SELECT a.id_absensi 
                     FROM tbl_absensi a
                     INNER JOIN tbl_karyawan k ON a.id_karyawan = k.id_karyawan
                     WHERE a.id_absensi = '$id_absensi' AND k.id_pengelola = '$id_pengelola'";
    
    if (mysqli_num_rows(mysqli_query($conn, $check_access)) == 0) {
        $error = "Anda tidak memiliki akses untuk mengedit absensi ini.";
    } else {
        // Calculate total hours
        $total_jam_kerja = NULL;
        if ($jam_masuk && $jam_keluar) {
            $masuk = new DateTime($jam_masuk);
            $keluar = new DateTime($jam_keluar);
            $diff = $keluar->diff($masuk);
            $total_jam_kerja = $diff->h + ($diff->i / 60);
        }
        
        $query = "UPDATE tbl_absensi SET 
                  tanggal = '$tanggal',
                  jam_masuk = " . ($jam_masuk ? "'$jam_masuk'" : "NULL") . ",
                  jam_keluar = " . ($jam_keluar ? "'$jam_keluar'" : "NULL") . ",
                  total_jam_kerja = " . ($total_jam_kerja ? "'$total_jam_kerja'" : "NULL") . ",
                  status_kehadiran = '$status_kehadiran',
                  keterangan = " . ($keterangan ? "'$keterangan'" : "NULL") . "
                  WHERE id_absensi = '$id_absensi'";
        
        if (mysqli_query($conn, $query)) {
            $success = "Absensi berhasil diupdate!";
        } else {
            $error = "Gagal mengupdate absensi: " . mysqli_error($conn);
        }
    }
}

// Proses Hapus Absensi
if (isset($_GET['delete'])) {
    $id_absensi = escape($_GET['delete']);
    
    $check_access = "SELECT a.id_absensi 
                     FROM tbl_absensi a
                     INNER JOIN tbl_karyawan k ON a.id_karyawan = k.id_karyawan
                     WHERE a.id_absensi = '$id_absensi' AND k.id_pengelola = '$id_pengelola'";
    
    if (mysqli_num_rows(mysqli_query($conn, $check_access)) == 0) {
        $error = "Anda tidak memiliki akses untuk menghapus absensi ini.";
    } else {
        $query = "DELETE FROM tbl_absensi WHERE id_absensi = '$id_absensi'";
        if (mysqli_query($conn, $query)) {
            $success = "Absensi berhasil dihapus!";
        } else {
            $error = "Gagal menghapus absensi: " . mysqli_error($conn);
        }
    }
}

// Get filter parameters
$filter_karyawan = isset($_GET['karyawan']) ? escape($_GET['karyawan']) : '';
$filter_status = isset($_GET['status']) ? escape($_GET['status']) : '';
$tanggal_mulai = isset($_GET['tanggal_mulai']) ? escape($_GET['tanggal_mulai']) : date('Y-m-01');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? escape($_GET['tanggal_akhir']) : date('Y-m-d');

// Fetch absensi data
$query_absensi = "SELECT a.*, k.nama, k.bagian, k.hari_libur, d.nama_dapur
                  FROM tbl_absensi a
                  INNER JOIN tbl_karyawan k ON a.id_karyawan = k.id_karyawan
                  LEFT JOIN tbl_dapur d ON k.id_dapur = d.id_dapur
                  WHERE k.id_pengelola = '$id_pengelola'
                  AND a.tanggal BETWEEN '$tanggal_mulai' AND '$tanggal_akhir'";

if ($filter_karyawan) {
    $query_absensi .= " AND a.id_karyawan = '$filter_karyawan'";
}
if ($filter_status) {
    $query_absensi .= " AND a.status_kehadiran = '$filter_status'";
}

$query_absensi .= " ORDER BY a.tanggal DESC, k.nama ASC";
$result_absensi = mysqli_query($conn, $query_absensi);

// Fetch karyawan for dropdown
$query_karyawan = "SELECT k.* FROM tbl_karyawan k WHERE k.id_pengelola = '$id_pengelola' AND k.status = 'aktif' ORDER BY k.nama ASC";
$result_karyawan = mysqli_query($conn, $query_karyawan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Karyawan - MBG System</title>
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
            <a href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
            <a href="dapur.php"><i class="bi bi-house"></i><span>Kelola Dapur</span></a>
            <a href="karyawan.php"><i class="bi bi-people"></i><span>Karyawan</span></a>
            <a href="absensi.php" class="active">
                <i class="bi bi-calendar-check"></i>
                <span>Absensi Karyawan</span>
            </a>

            <a href="menu.php"><i class="bi bi-card-list"></i><span>Menu</span></a>
            <a href="pembelanjaan.php"><i class="bi bi-cash-stack"></i><span>Pembelanjaan</span></a>
            <a href="stok.php"><i class="bi bi-box-seam"></i><span>Stok Bahan</span></a>
            <a href="dokumentasi.php"><i class="bi bi-journal-text"></i><span>Dokumentasi</span></a>
            <a href="laporan.php"><i class="bi bi-file-earmark-text"></i><span>Laporan</span></a>
            <a href="profil.php">
                <i class="bi bi-person-circle"></i>
                <span>Profil</span>
            </a>
            <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="top-navbar">
            <div><h4 class="mb-0">Absensi Karyawan</h4><small class="text-muted">Kelola Absensi Harian</small></div>
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

        <div class="content-wrapper">
            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i><?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <h5><i class="bi bi-calendar-check"></i> Daftar Absensi Karyawan</h5>
                <p class="text-muted mb-0">Lihat dan kelola absensi karyawan. Karyawan mengisi absensi dari akun mereka sendiri.</p>
            </div>

            <!-- Table with Filter -->
            <div class="table-card">
                <div class="mb-4 pb-3 border-bottom">
                    <form method="GET" action="absensi.php">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Karyawan</label>
                                <select name="karyawan" class="form-select">
                                    <option value="">Semua Karyawan</option>
                                    <?php 
                                    mysqli_data_seek($result_karyawan, 0);
                                    while($k = mysqli_fetch_assoc($result_karyawan)): 
                                    ?>
                                        <option value="<?= $k['id_karyawan'] ?>" <?= $filter_karyawan == $k['id_karyawan'] ? 'selected' : '' ?>>
                                            <?= $k['nama'] ?> - <?= $k['bagian'] ?? 'Umum' ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">Semua Status</option>
                                    <option value="hadir" <?= $filter_status == 'hadir' ? 'selected' : '' ?>>Hadir</option>
                                    <option value="izin" <?= $filter_status == 'izin' ? 'selected' : '' ?>>Izin</option>
                                    <option value="sakit" <?= $filter_status == 'sakit' ? 'selected' : '' ?>>Sakit</option>
                                    <option value="alpha" <?= $filter_status == 'alpha' ? 'selected' : '' ?>>Alpha</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Dari Tanggal</label>
                                <input type="date" name="tanggal_mulai" class="form-control" value="<?= $tanggal_mulai ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sampai Tanggal</label>
                                <input type="date" name="tanggal_akhir" class="form-control" value="<?= $tanggal_akhir ?>">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label d-none d-md-block">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-filter me-1"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
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
                                <th style="width: 5%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result_absensi) > 0): 
                                $no = 1;
                                while($a = mysqli_fetch_assoc($result_absensi)):
                                    $status_class = $a['status_kehadiran'] == 'hadir' ? 'bg-success' : 
                                                   ($a['status_kehadiran'] == 'izin' ? 'bg-warning' : 
                                                   ($a['status_kehadiran'] == 'sakit' ? 'bg-info' : 'bg-danger'));
                                    
                                    // Check for weekly holiday
                                    $is_weekly_libur = false;
                                    if (!empty($a['hari_libur'])) {
                                        $days_id = [
                                            'Sunday' => 'Minggu',
                                            'Monday' => 'Senin',
                                            'Tuesday' => 'Selasa',
                                            'Wednesday' => 'Rabu',
                                            'Thursday' => 'Kamis',
                                            'Friday' => 'Jumat',
                                            'Saturday' => 'Sabtu'
                                        ];
                                        $day_name = $days_id[date('l', strtotime($a['tanggal']))];
                                        $libur_days = explode(',', $a['hari_libur']);
                                        if (in_array($day_name, $libur_days)) {
                                            $is_weekly_libur = true;
                                        }
                                    }
                            ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($a['tanggal'])) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= $a['nama'] ?></div>
                                </td>
                                <td>
                                    <span class="badge-bagian">
                                        <?= ucwords(str_replace('_', ' ', $a['bagian'] ?? 'umum')) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($is_weekly_libur): ?>
                                        <span class="badge bg-info text-dark border">Libur</span>
                                    <?php elseif ($a['jam_masuk']): ?>
                                        <span class="badge bg-light text-success border">
                                            <?= date('H:i', strtotime($a['jam_masuk'])) ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($is_weekly_libur): ?>
                                        <span class="badge bg-info text-dark border">Libur</span>
                                    <?php elseif ($a['jam_keluar']): ?>
                                        <span class="badge bg-light text-danger border">
                                            <?= date('H:i', strtotime($a['jam_keluar'])) ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($is_weekly_libur): ?>
                                        <span class="text-muted">-</span>
                                    <?php elseif ($a['total_jam_kerja']): ?>
                                        <strong><?= number_format($a['total_jam_kerja'], 2) ?></strong> jam
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $status_class ?> rounded-pill">
                                        <?= ucfirst($a['status_kehadiran']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button class="btn btn-sm btn-light text-warning border" onclick='editAbsensi(<?= htmlspecialchars(json_encode($a), ENT_QUOTES, 'UTF-8') ?>)' title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light text-danger border" onclick="deleteAbsensi(<?= $a['id_absensi'] ?>, '<?= htmlspecialchars($a['nama'], ENT_QUOTES, 'UTF-8') ?>', '<?= date('d/m/Y', strtotime($a['tanggal'])) ?>')" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="bi bi-calendar-x" style="font-size: 48px;"></i>
                                    <p class="mt-2">Tidak ada data absensi ditemukan</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="absensi.php">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_absensi" id="edit_id_absensi">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Absensi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Karyawan</label>
                            <input type="text" id="edit_nama_karyawan" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" id="edit_tanggal" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam Masuk</label>
                                <input type="time" name="jam_masuk" id="edit_jam_masuk" class="form-control" onchange="calculateHours('edit')">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam Keluar</label>
                                <input type="time" name="jam_keluar" id="edit_jam_keluar" class="form-control" onchange="calculateHours('edit')">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Total Jam Kerja</label>
                            <input type="text" id="edit_total_jam" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status Kehadiran <span class="text-danger">*</span></label>
                            <select name="status_kehadiran" id="edit_status_kehadiran" class="form-select" required>
                                <option value="hadir">Hadir</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alpha">Alpha</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="edit_keterangan" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
    <script>
        // Calculate hours
        function calculateHours(prefix) {
            const jamMasuk = document.getElementById(prefix + '_jam_masuk').value;
            const jamKeluar = document.getElementById(prefix + '_jam_keluar').value;
            
            if (jamMasuk && jamKeluar) {
                const masuk = new Date('1970-01-01 ' + jamMasuk);
                const keluar = new Date('1970-01-01 ' + jamKeluar);
                const diff = (keluar - masuk) / 1000 / 60 / 60; // hours
                
                if (diff > 0) {
                    document.getElementById(prefix + '_total_jam').value = diff.toFixed(2) + ' jam';
                } else {
                    document.getElementById(prefix + '_total_jam').value = 'Jam keluar harus lebih besar';
                }
            }
        }

        // Edit function
        function editAbsensi(data) {
            document.getElementById('edit_id_absensi').value = data.id_absensi;
            document.getElementById('edit_nama_karyawan').value = data.nama + ' - ' + (data.bagian || 'Umum');
            document.getElementById('edit_tanggal').value = data.tanggal;
            document.getElementById('edit_jam_masuk').value = data.jam_masuk ? data.jam_masuk.substring(0, 5) : '';
            document.getElementById('edit_jam_keluar').value = data.jam_keluar ? data.jam_keluar.substring(0, 5) : '';
            document.getElementById('edit_status_kehadiran').value = data.status_kehadiran;
            document.getElementById('edit_keterangan').value = data.keterangan || '';
            
            if (data.total_jam_kerja) {
                document.getElementById('edit_total_jam').value = parseFloat(data.total_jam_kerja).toFixed(2) + ' jam';
            } else {
                document.getElementById('edit_total_jam').value = '';
            }
            
            const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
            modal.show();
        }

        // Delete function
        function deleteAbsensi(id, nama, tanggal) {
            if (confirm(`Hapus absensi ${nama} pada tanggal ${tanggal}?`)) {
                window.location.href = `absensi.php?delete=${id}`;
            }
        }
    </script>
</body>
</html>
