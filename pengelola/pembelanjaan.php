<?php
// pengelola/pembelanjaan.php
require_once '../koneksi.php';
checkRole(['pengelola']);

$id_pengelola = $_SESSION['user_id'];
$success = null;
$error = null;

// Handle Add Shopping Plan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $tanggal_pembelian = escape($_POST['tanggal_pembelian']);
    $supplier = escape($_POST['supplier']);
    
    // Get Dapur ID (Assuming single kitchen for now or from session/input)
    // For now, let's pick the first active kitchen of the manager
    $q_dapur = "SELECT id_dapur FROM tbl_dapur WHERE id_pengelola = '$id_pengelola' LIMIT 1";
    $r_dapur = mysqli_query($conn, $q_dapur);
    $dapur = mysqli_fetch_assoc($r_dapur);
    $id_dapur = $dapur['id_dapur'];

    if (!$id_dapur) {
        $error = "Anda belum memiliki dapur aktif!";
    } else {
        mysqli_begin_transaction($conn);
        try {
            // Insert Header
            $query = "INSERT INTO tbl_pembelanjaan (id_dapur, id_pengelola, tanggal_pembelian, supplier, status) 
                      VALUES ('$id_dapur', '$id_pengelola', '$tanggal_pembelian', '$supplier', 'rencana')";
            if (!mysqli_query($conn, $query)) throw new Exception("Gagal buat rencana: " . mysqli_error($conn));
            
            $id_pembelanjaan = mysqli_insert_id($conn);
            $total_belanja = 0;

            // Insert Details
            if (isset($_POST['nama_bahan'])) {
                $nama_bahans = $_POST['nama_bahan'];
                $jumlahs = $_POST['jumlah'];
                $satuans = $_POST['satuan'];
                $hargas = $_POST['harga'];
                
                for ($i = 0; $i < count($nama_bahans); $i++) {
                    if (!empty($nama_bahans[$i])) {
                        $nm_bahan = escape($nama_bahans[$i]);
                        $jml = escape($jumlahs[$i]);
                        $sat = escape($satuans[$i]);
                        $hrg = escape($hargas[$i]);
                        $subtotal = $jml * $hrg;
                        $total_belanja += $subtotal;
                        
                        // Check if bahan exists
                        $q_check = "SELECT id_bahan FROM tbl_bahan_baku WHERE nama_bahan = '$nm_bahan' LIMIT 1";
                        $r_check = mysqli_query($conn, $q_check);
                        
                        if (mysqli_num_rows($r_check) > 0) {
                            $b_id = mysqli_fetch_assoc($r_check)['id_bahan'];
                        } else {
                            // Create new bahan
                            $q_new = "INSERT INTO tbl_bahan_baku (nama_bahan, satuan, stok_saat_ini) VALUES ('$nm_bahan', '$sat', 0)";
                            if (!mysqli_query($conn, $q_new)) throw new Exception("Gagal buat bahan baru: " . mysqli_error($conn));
                            $b_id = mysqli_insert_id($conn);
                        }

                        $q_detail = "INSERT INTO tbl_detail_pembelanjaan (id_pembelanjaan, id_bahan, jumlah, satuan, harga_satuan, subtotal) 
                                     VALUES ('$id_pembelanjaan', '$b_id', '$jml', '$sat', '$hrg', '$subtotal')";
                        if (!mysqli_query($conn, $q_detail)) throw new Exception("Gagal simpan detail: " . mysqli_error($conn));
                    }
                }
            }
            
            // Update Total
            $q_update = "UPDATE tbl_pembelanjaan SET total_pembelian = '$total_belanja' WHERE id_pembelanjaan = '$id_pembelanjaan'";
            if (!mysqli_query($conn, $q_update)) throw new Exception("Gagal update total: " . mysqli_error($conn));

            mysqli_commit($conn);
            $success = "Rencana belanja berhasil dibuat!";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}

// Handle Finish Shopping (Upload Proof & Update Stock)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'finish') {
    $id_pembelanjaan = escape($_POST['id_pembelanjaan']);
    $no_nota_fisik = isset($_POST['no_nota_fisik']) ? escape($_POST['no_nota_fisik']) : '';
    
    $bukti_pembelian = null;
    if (isset($_FILES['bukti_pembelian']) && $_FILES['bukti_pembelian']['error'] == 0) {
        $upload = uploadFoto($_FILES['bukti_pembelian'], 'bukti'); 
        if ($upload['status']) {
            $bukti_pembelian = $upload['filename'];
        } else {
            $error = $upload['message'];
        }
    } else {
        $error = "Bukti pembayaran wajib diupload!";
    }

    if (!isset($error)) {
        mysqli_begin_transaction($conn);
        try {
            // Update Status & No Nota
            $query = "UPDATE tbl_pembelanjaan SET status = 'selesai', bukti_pembelian = '$bukti_pembelian', no_nota_fisik = '$no_nota_fisik' WHERE id_pembelanjaan = '$id_pembelanjaan'";
            if (!mysqli_query($conn, $query)) throw new Exception("Gagal update status: " . mysqli_error($conn));

            // Update Stock
            $q_detail = "SELECT * FROM tbl_detail_pembelanjaan WHERE id_pembelanjaan = '$id_pembelanjaan'";
            $r_detail = mysqli_query($conn, $q_detail);
            
            while ($row = mysqli_fetch_assoc($r_detail)) {
                $id_bahan = $row['id_bahan'];
                $jumlah = $row['jumlah'];
                $satuan = $row['satuan'];
                
                // Get Current Stock
                $q_stok = "SELECT stok_saat_ini FROM tbl_bahan_baku WHERE id_bahan = '$id_bahan'";
                $r_stok = mysqli_query($conn, $q_stok);
                $curr_stok = mysqli_fetch_assoc($r_stok)['stok_saat_ini'];
                
                $new_stok = $curr_stok + $jumlah;
                
                // Update Master Stock
                $q_upd_stok = "UPDATE tbl_bahan_baku SET stok_saat_ini = '$new_stok' WHERE id_bahan = '$id_bahan'";
                if (!mysqli_query($conn, $q_upd_stok)) throw new Exception("Gagal update stok master: " . mysqli_error($conn));
                
                // Insert History
                $q_hist = "INSERT INTO tbl_riwayat_stok (id_bahan, id_pembelanjaan, tipe_transaksi, jumlah_stok, stok_sebelum, stok_sesudah, satuan, tanggal_transaksi, keterangan) 
                           VALUES ('$id_bahan', '$id_pembelanjaan', 'masuk', '$jumlah', '$curr_stok', '$new_stok', '$satuan', CURDATE(), 'Pembelanjaan Selesai')";
                if (!mysqli_query($conn, $q_hist)) throw new Exception("Gagal catat riwayat: " . mysqli_error($conn));
            }

            mysqli_commit($conn);
            $success = "Pembelanjaan selesai! Stok telah diperbarui.";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = $e->getMessage();
        }
    }
}

// Get Data
$query_belanja = "SELECT p.*, d.nama_dapur, 
                  (SELECT COUNT(*) FROM tbl_detail_pembelanjaan WHERE id_pembelanjaan = p.id_pembelanjaan) as jumlah_item
                  FROM tbl_pembelanjaan p 
                  JOIN tbl_dapur d ON p.id_dapur = d.id_dapur 
                  WHERE p.id_pengelola = '$id_pengelola' 
                  ORDER BY p.tanggal_pembelian DESC, p.created_at DESC";
$result_belanja = mysqli_query($conn, $query_belanja);

// Get Bahan Baku for Dropdown
$q_bahan = "SELECT * FROM tbl_bahan_baku ORDER BY nama_bahan ASC";
$r_bahan = mysqli_query($conn, $q_bahan);
$bahan_list = [];
while ($b = mysqli_fetch_assoc($r_bahan)) {
    $bahan_list[] = $b;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pembelanjaan - MBG System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
</head>
<body>
    <!-- Sidebar & Navbar (Copy from template) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="bi bi-list"></i></button>
    
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo"><img src="../assets/img/logo.png" alt="MBG Logo" class="logo-image"></div>
            <h4>MBG System</h4><small>Pengelola Panel</small>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a>
            <a href="dapur.php"><i class="bi bi-house"></i><span>Kelola Dapur</span></a>
            <a href="karyawan.php"><i class="bi bi-people"></i><span>Karyawan</span></a>
            <a href="absensi.php"><i class="bi bi-calendar-check"></i><span>Absensi Karyawan</span></a>
            <a href="menu.php"><i class="bi bi-card-list"></i><span>Menu</span></a>
            <a href="pembelanjaan.php" class="active"><i class="bi bi-cash-stack"></i><span>Pembelanjaan</span></a>
            <a href="stok.php"><i class="bi bi-box-seam"></i><span>Stok Bahan</span></a>
            <a href="dokumentasi.php"><i class="bi bi-journal-text"></i><span>Dokumentasi</span></a>
            <a href="laporan.php"><i class="bi bi-file-earmark-text"></i><span>Laporan</span></a>
            <a href="profil.php"><i class="bi bi-person-circle"></i><span>Profil</span></a>
            <a href="../logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="top-navbar">
            <div><h4 class="mb-0">Pembelanjaan</h4><small class="text-muted">Kelola rencana dan realisasi belanja</small></div>
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

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between mb-4">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle me-2"></i>Buat Rencana Belanja
            </button>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kode</th>
                            <th>Supplier</th>
                            <th>Detail Item</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($belanja = mysqli_fetch_assoc($result_belanja)): ?>
                        <?php
                        // Get item details
                        $q_items = "SELECT b.nama_bahan, d.jumlah, d.satuan, d.harga_satuan, d.subtotal 
                                    FROM tbl_detail_pembelanjaan d 
                                    JOIN tbl_bahan_baku b ON d.id_bahan = b.id_bahan 
                                    WHERE d.id_pembelanjaan = '{$belanja['id_pembelanjaan']}'";
                        $r_items = mysqli_query($conn, $q_items);
                        $items = [];
                        while($item = mysqli_fetch_assoc($r_items)) {
                            $items[] = $item;
                        }
                        $items_json = htmlspecialchars(json_encode($items), ENT_QUOTES, 'UTF-8');
                        ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($belanja['tanggal_pembelian'])) ?></td>
                            <td>
                                <span class="fw-bold"><?= $belanja['kode_pembelanjaan'] ?></span>
                                <?php if(isset($belanja['no_nota_fisik']) && $belanja['no_nota_fisik']): ?>
                                    <br><small class="text-primary"><i class="bi bi-receipt"></i> <?= $belanja['no_nota_fisik'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= $belanja['supplier'] ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick='showItemDetails(<?= $items_json ?>)'>
                                    <i class="bi bi-list-ul me-1"></i> Lihat Detail (<?= count($items) ?> item)
                                </button>
                            </td>
                            <td>Rp <?= number_format($belanja['total_pembelian'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge rounded-pill <?= $belanja['status'] == 'selesai' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                    <?= ucfirst($belanja['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($belanja['status'] == 'rencana'): ?>
                                    <button class="btn btn-success btn-sm" onclick="finishBelanja('<?= $belanja['id_pembelanjaan'] ?>')">
                                        <i class="bi bi-check-lg me-1"></i> Selesai
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary btn-sm" disabled>
                                        <i class="bi bi-check-all me-1"></i> Selesai
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title">Buat Rencana Belanja</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Rencana</label>
                                <input type="date" name="tanggal_pembelian" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Supplier / Toko</label>
                                <input type="text" name="supplier" class="form-control" placeholder="Nama Toko" required>
                            </div>
                        </div>
                        
                        <h6 class="border-bottom pb-2 mb-3">Daftar Barang</h6>
                        <div id="item-container">
                            <div class="row item-row mb-2">
                                <div class="col-md-4">
                                    <input type="text" name="nama_bahan[]" class="form-control" placeholder="Nama Bahan" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="jumlah[]" class="form-control" placeholder="Qty" step="0.01" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="satuan[]" class="form-control" placeholder="Satuan" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" name="harga[]" class="form-control" placeholder="Harga @" required>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm mt-1 remove-item" style="display:none;"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-item-row">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Barang
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Rencana</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Finish -->
    <div class="modal fade" id="modalFinish" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="finish">
                    <input type="hidden" name="id_pembelanjaan" id="finish_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Selesaikan Belanja</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Upload bukti pembayaran (nota/struk) untuk menyelesaikan belanja ini. Stok akan otomatis bertambah.</p>
                        <div class="mb-3">
                            <label class="form-label">Nomor Nota Toko (Opsional)</label>
                            <input type="text" name="no_nota_fisik" class="form-control" placeholder="Contoh: INV-001">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bukti Pembayaran <span class="text-danger">*</span></label>
                            <input type="file" name="bukti_pembelian" class="form-control" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Selesai & Update Stok</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Detail Item -->
    <div class="modal fade" id="modalDetailItem" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Item Belanja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Bahan</th>
                                    <th>Jumlah</th>
                                    <th>Satuan</th>
                                    <th>Harga Satuan</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="itemDetailBody">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">Total</th>
                                    <th id="itemDetailTotal">Rp 0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin-global.js"></script>
    <script>
        // Dynamic Item Rows
        document.getElementById('add-item-row').addEventListener('click', function() {
            const container = document.getElementById('item-container');
            const firstRow = container.querySelector('.item-row');
            const newRow = firstRow.cloneNode(true);
            
            newRow.querySelectorAll('input').forEach(input => input.value = '');
            newRow.querySelector('.remove-item').style.display = 'block';
            
            container.appendChild(newRow);
        });

        document.getElementById('item-container').addEventListener('click', function(e) {
            if (e.target.closest('.remove-item')) {
                const row = e.target.closest('.item-row');
                if (document.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                }
            }
        });

        function finishBelanja(id) {
            document.getElementById('finish_id').value = id;
            new bootstrap.Modal(document.getElementById('modalFinish')).show();
        }

        function showItemDetails(items) {
            const tbody = document.getElementById('itemDetailBody');
            tbody.innerHTML = '';
            let total = 0;
            
            items.forEach((item, index) => {
                const row = `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.nama_bahan}</td>
                        <td>${parseFloat(item.jumlah).toLocaleString('id-ID')}</td>
                        <td>${item.satuan}</td>
                        <td>Rp ${parseFloat(item.harga_satuan).toLocaleString('id-ID')}</td>
                        <td>Rp ${parseFloat(item.subtotal).toLocaleString('id-ID')}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
                total += parseFloat(item.subtotal);
            });
            
            document.getElementById('itemDetailTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
            new bootstrap.Modal(document.getElementById('modalDetailItem')).show();
        }
    </script>
</body>
</html>
