<?php
include 'koneksi.php';
session_start();

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle edit pesanan
    if (isset($_POST['edit_pesanan'])) {
        $booking_id = $_POST['booking_id'];
        $bus_id = mysqli_real_escape_string($connect, $_POST['bus_id']);
        $jenis_paket = mysqli_real_escape_string($connect, $_POST['jenis_paket']);
        $status = mysqli_real_escape_string($connect, $_POST['status']);
        $tanggal_berangkat = mysqli_real_escape_string($connect, $_POST['tanggal_berangkat']);
        $jam_berangkat = mysqli_real_escape_string($connect, $_POST['jam_berangkat']);
        $tanggal_kembali = mysqli_real_escape_string($connect, $_POST['tanggal_kembali']);
        $lokasi_penjemputan = mysqli_real_escape_string($connect, $_POST['lokasi_penjemputan']);
        $tujuan = mysqli_real_escape_string($connect, $_POST['tujuan']);
        $jumlah_penumpang = intval($_POST['jumlah_penumpang']);
        $keterangan = mysqli_real_escape_string($connect, $_POST['keterangan']);
        $total_harga = floatval($_POST['total_harga']);
        
        // Ambil data lama untuk logging dan update status bus
        $query_old = "SELECT * FROM pemesanan WHERE id = $booking_id";
        $result_old = mysqli_query($connect, $query_old);
        $old_data = mysqli_fetch_assoc($result_old);
        
        // Update data pesanan
        $query = "UPDATE pemesanan SET 
                  bus_id = '$bus_id',
                  jenis_paket = '$jenis_paket',
                  status = '$status',
                  tanggal_berangkat = '$tanggal_berangkat',
                  jam_berangkat = '$jam_berangkat',
                  tanggal_kembali = '$tanggal_kembali',
                  lokasi_penjemputan = '$lokasi_penjemputan',
                  tujuan = '$tujuan',
                  jumlah_penumpang = $jumlah_penumpang,
                  keterangan = '$keterangan',
                  total_harga = $total_harga,
                  updated_at = NOW()
                  WHERE id = $booking_id";
        
        if (mysqli_query($connect, $query)) {
            // Handle perubahan status bus berdasarkan perubahan status booking
            // Reset status bus lama jika bus berubah
            if ($old_data['bus_id'] != $bus_id) {
                $reset_bus_lama = "UPDATE bus SET status = 'Tersedia' WHERE id = {$old_data['bus_id']}";
                mysqli_query($connect, $reset_bus_lama);
            }
            
            // Update status bus baru
            $bus_status = 'Tersedia'; // default
            
            if ($status == 'Dikonfirmasi') {
                $bus_status = 'Disewa';
            } elseif ($status == 'Selesai' || $status == 'Dibatalkan') {
                $bus_status = 'Tersedia';
            } elseif ($status == 'Menunggu Konfirmasi' && $old_data['status'] == 'Dikonfirmasi') {
                $bus_status = 'Tersedia';
            }
            
            $update_bus = "UPDATE bus SET status = '$bus_status' WHERE id = $bus_id";
            mysqli_query($connect, $update_bus);
            
            $success_message = "Data pesanan berhasil diupdate!";
        } else {
            $error_message = "Terjadi kesalahan: " . mysqli_error($connect);
        }
    }
    
    // Handle hapus pesanan
    if (isset($_POST['hapus_pesanan'])) {
        $booking_id = $_POST['booking_id'];
        
        // Ambil data pesanan untuk update status bus
        $query_old = "SELECT * FROM pemesanan WHERE id = $booking_id";
        $result_old = mysqli_query($connect, $query_old);
        $old_data = mysqli_fetch_assoc($result_old);
        
        // Update status bus menjadi tersedia
        if ($old_data['bus_id']) {
            $reset_bus = "UPDATE bus SET status = 'Tersedia' WHERE id = {$old_data['bus_id']}";
            mysqli_query($connect, $reset_bus);
        }
        
        // Hapus pesanan
        $query = "DELETE FROM pemesanan WHERE id = $booking_id";
        
        if (mysqli_query($connect, $query)) {
            $_SESSION['success_message'] = "Pesanan berhasil dihapus!";
            header("Location: admin_booking.php");
            exit();
        } else {
            $error_message = "Terjadi kesalahan saat menghapus: " . mysqli_error($connect);
        }
    }
}

// Ambil data pesanan berdasarkan ID
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id == 0) {
    die("ID pesanan tidak valid");
}

$query = "SELECT p.*, u.nama_lengkap, u.nomor_hp, b.`tipe bus`, b.jenis, b.kapasitas, per.nama_perusahaan 
          FROM pemesanan p 
          JOIN datauser u ON p.user_id = u.id 
          JOIN bus b ON p.bus_id = b.id 
          JOIN perusahaan_bus per ON b.perusahaan_id = per.id 
          WHERE p.id = $booking_id";
$result = mysqli_query($connect, $query);

if (mysqli_num_rows($result) == 0) {
    die("Pesanan tidak ditemukan");
}

$booking = mysqli_fetch_array($result);

// Fungsi untuk mendapatkan paket berdasarkan bus
function getPaketByBus($bus_id, $connect) {
    $query = "SELECT jenis_harga FROM harga_bus WHERE bus_id = $bus_id GROUP BY jenis_harga";
    $result = mysqli_query($connect, $query);
    
    $paket = [];
    while($row = mysqli_fetch_array($result)) {
        $paket[] = $row['jenis_harga'];
    }
    
    return $paket;
}

// Fungsi untuk mendapatkan harga berdasarkan bus dan jenis paket
function getHargaByPaket($bus_id, $jenis_paket, $connect) {
    $query = "SELECT harga FROM harga_bus WHERE bus_id = $bus_id AND jenis_harga = '$jenis_paket' LIMIT 1";
    $result = mysqli_query($connect, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        return $row['harga'];
    }
    
    return 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin | Edit Pesanan</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/AdminLTE.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Edit Pesanan
            <small>Ubah data pesanan bus</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="admin_booking.php">Management Pesanan</a></li>
            <li class="active">Edit Pesanan</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Edit Data Pesanan</h3>
                        <div class="box-tools pull-right">
                            <span class="label label-info">
                                ID: <?php echo $booking['id']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible" style="margin: 15px;">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-check"></i> Sukses!</h4>
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible" style="margin: 15px;">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h4><i class="icon fa fa-ban"></i> Error!</h4>
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Informasi Pesanan -->
                    <div class="box-body">
                        <div class="alert alert-info">
                            <h4><i class="icon fa fa-info"></i> Informasi Pesanan</h4>
                            <strong>Pemesan:</strong> <?php echo $booking['nama_lengkap'] ?> (<?php echo $booking['nomor_hp'] ?>)<br>
                            <strong>Dibuat:</strong> <?php echo date('d M Y H:i', strtotime($booking['created_at'])) ?>
                        </div>
                    </div>
                    
                    <form method="POST" action="" id="editPesananForm">
                        <div class="box-body">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Pilih Bus *</label>
                                        <select name="bus_id" class="form-control" required id="bus_select">
                                            <option value="">-- Pilih Bus --</option>
                                            <?php 
                                            $bus_query = "SELECT b.*, p.nama_perusahaan FROM bus b 
                                                         JOIN perusahaan_bus p ON b.perusahaan_id = p.id 
                                                         WHERE b.status = 'Tersedia' OR b.id = {$booking['bus_id']}
                                                         ORDER BY p.nama_perusahaan, b.`tipe bus`";
                                            $bus_result = mysqli_query($connect, $bus_query);
                                            while($bus = mysqli_fetch_array($bus_result)): 
                                            ?>
                                                <option value="<?php echo $bus['id'] ?>" 
                                                    data-kapasitas="<?php echo $bus['kapasitas'] ?>"
                                                    <?php echo ($booking['bus_id'] == $bus['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $bus['nama_perusahaan'] ?> - <?php echo $bus['tipe bus'] ?> (Kapasitas: <?php echo $bus['kapasitas'] ?>)
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <small class="text-muted" id="kapasitas_info">
                                            Kapasitas bus: <?php echo $booking['kapasitas']; ?> penumpang
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Jenis Paket *</label>
                                        <select name="jenis_paket" class="form-control" required id="jenis_paket_select">
                                            <?php
                                            $paket_list = getPaketByBus($booking['bus_id'], $connect);
                                            foreach($paket_list as $paket):
                                            ?>
                                                <option value="<?php echo $paket; ?>" 
                                                    <?php echo ($booking['jenis_paket'] == $paket) ? 'selected' : ''; ?>>
                                                    <?php echo $paket; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted" id="harga_info">
                                            Harga: Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Status Pesanan *</label>
                                        <select name="status" class="form-control" required>
                                            <option value="Menunggu Konfirmasi" <?php echo $booking['status'] == 'Menunggu Konfirmasi' ? 'selected' : ''; ?>>Menunggu Konfirmasi</option>
                                            <option value="Dikonfirmasi" <?php echo $booking['status'] == 'Dikonfirmasi' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                                            <option value="Selesai" <?php echo $booking['status'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                                            <option value="Dibatalkan" <?php echo $booking['status'] == 'Dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Jumlah Penumpang *</label>
                                        <input type="number" name="jumlah_penumpang" class="form-control" 
                                               value="<?php echo $booking['jumlah_penumpang']; ?>" min="1" required id="jumlah_penumpang">
                                        <small class="text-muted" id="kapasitas_warning" style="color: red; display: none;">
                                            Jumlah penumpang melebihi kapasitas bus!
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Berangkat *</label>
                                        <input type="date" name="tanggal_berangkat" class="form-control" 
                                               value="<?php echo $booking['tanggal_berangkat']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Jam Berangkat *</label>
                                        <input type="time" name="jam_berangkat" class="form-control" 
                                               value="<?php echo $booking['jam_berangkat']; ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal Kembali *</label>
                                        <input type="date" name="tanggal_kembali" class="form-control" 
                                               value="<?php echo $booking['tanggal_kembali']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Total Harga (Rp) *</label>
                                        <input type="number" name="total_harga" class="form-control" 
                                               value="<?php echo $booking['total_harga']; ?>" min="0" step="1000" required id="total_harga">
                                        <small class="text-muted">Harga akan terisi otomatis berdasarkan paket</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Lokasi Penjemputan *</label>
                                <textarea name="lokasi_penjemputan" class="form-control" rows="2" required><?php echo $booking['lokasi_penjemputan']; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Tujuan *</label>
                                <textarea name="tujuan" class="form-control" rows="2" required><?php echo $booking['tujuan']; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Keterangan Tambahan</label>
                                <textarea name="keterangan" class="form-control" rows="3"><?php echo $booking['keterangan']; ?></textarea>
                            </div>
                            
                            <?php if (!empty($booking['alasan_batal'])): ?>
                            <div class="form-group">
                                <label>Alasan Pembatalan</label>
                                <textarea class="form-control" rows="2" readonly style="background-color: #f8f8f8;"><?php echo $booking['alasan_batal']; ?></textarea>
                                <small class="text-muted">Alasan pembatalan dari user (readonly)</small>
                            </div>
                            <?php endif; ?>
                        </div>
                        <!-- /.box-body -->
                        
                        <div class="box-footer">
                            <button type="submit" name="edit_pesanan" class="btn btn-primary">
                                <i class="fa fa-save"></i> Simpan Perubahan
                            </button>
                            <a href="admin_booking.php" class="btn btn-default">
                                <i class="fa fa-arrow-left"></i> Kembali ke Management Pesanan
                            </a>
                            
                            <button type="button" class="btn btn-danger pull-right" data-toggle="modal" data-target="#modalHapus">
                                <i class="fa fa-trash"></i> Hapus Pesanan
                            </button>
                            
                            <?php if (!empty($booking['data_edit_sebelumnya'])): ?>
                            <button type="button" class="btn btn-info" onclick="lihatDataSebelumnya()">
                                <i class="fa fa-history"></i> Lihat Data Sebelumnya
                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <!-- /.box -->
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- Modal Hapus Pesanan -->
<div class="modal fade" id="modalHapus" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Konfirmasi Hapus Pesanan</h4>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pesanan ini?</p>
                <div class="alert alert-warning">
                    <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan! Data pesanan akan dihapus permanen.
                </div>
                <div class="well">
                    <strong>Detail Pesanan:</strong><br>
                    ID: <?php echo $booking['id']; ?><br>
                    Pemesan: <?php echo $booking['nama_lengkap']; ?><br>
                    Bus: <?php echo $booking['nama_perusahaan'] . ' - ' . $booking['tipe bus']; ?><br>
                    Tanggal Berangkat: <?php echo date('d M Y', strtotime($booking['tanggal_berangkat'])); ?>
                </div>
            </div>
            <div class="modal-footer">
                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    <button type="submit" name="hapus_pesanan" class="btn btn-danger">
                        <i class="fa fa-trash"></i> Ya, Hapus Pesanan
                    </button>
                </form>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <i class="fa fa-times"></i> Batal
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Data Sebelumnya -->
<?php if (!empty($booking['data_edit_sebelumnya'])): ?>
<div class="modal fade" id="modalDataSebelumnya" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">Data Sebelum Edit</h4>
            </div>
            <div class="modal-body">
                <?php
                $data_lama = json_decode($booking['data_edit_sebelumnya'], true);
                if ($data_lama):
                ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Field</th>
                            <th width="35%">Data Lama</th>
                            <th width="35%">Data Saat Ini</th>
                        </tr>
                        <tr>
                            <td><strong>Jenis Paket</strong></td>
                            <td><?php echo $data_lama['jenis_paket']; ?></td>
                            <td><?php echo $booking['jenis_paket']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Berangkat</strong></td>
                            <td><?php echo date('d M Y', strtotime($data_lama['tanggal_berangkat'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($booking['tanggal_berangkat'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Jam Berangkat</strong></td>
                            <td><?php echo date('H:i', strtotime($data_lama['jam_berangkat'])); ?></td>
                            <td><?php echo date('H:i', strtotime($booking['jam_berangkat'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Kembali</strong></td>
                            <td><?php echo date('d M Y', strtotime($data_lama['tanggal_kembali'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($booking['tanggal_kembali'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Lokasi Penjemputan</strong></td>
                            <td><?php echo $data_lama['lokasi_penjemputan']; ?></td>
                            <td><?php echo $booking['lokasi_penjemputan']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tujuan</strong></td>
                            <td><?php echo $data_lama['tujuan']; ?></td>
                            <td><?php echo $booking['tujuan']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Jumlah Penumpang</strong></td>
                            <td><?php echo $data_lama['jumlah_penumpang']; ?> orang</td>
                            <td><?php echo $booking['jumlah_penumpang']; ?> orang</td>
                        </tr>
                        <tr>
                            <td><strong>Total Harga</strong></td>
                            <td>Rp <?php echo number_format($data_lama['total_harga'], 0, ',', '.'); ?></td>
                            <td>Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?></td>
                        </tr>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">Data sebelumnya tidak dapat dibaca.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<footer class="main-footer">
    <div class="pull-right hidden-xs">
        <b>Version</b> 1.0.0
    </div>
    <strong>Copyright &copy; 2024 <a href="#">BISATA</a>.</strong> All rights reserved.
</footer>

</div>
<!-- ./wrapper -->

<!-- jQuery 2.2.3 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/js/app.min.js"></script>

<script>
// Fungsi untuk memuat paket berdasarkan bus yang dipilih
function loadPaketByBus(busId) {
    if (busId) {
        // AJAX request untuk mendapatkan paket berdasarkan bus
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_paket_by_bus.php?bus_id=' + busId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var paketSelect = document.getElementById('jenis_paket_select');
                paketSelect.innerHTML = xhr.responseText;
                
                // Reset harga
                document.getElementById('total_harga').value = '';
                document.getElementById('harga_info').textContent = '';
                
                // Trigger perubahan paket untuk load harga
                if (paketSelect.options.length > 0) {
                    paketSelect.dispatchEvent(new Event('change'));
                }
            }
        };
        xhr.send();
    } else {
        var paketSelect = document.getElementById('jenis_paket_select');
        paketSelect.innerHTML = '<option value="">-- Pilih Bus Terlebih Dahulu --</option>';
        document.getElementById('total_harga').value = '';
        document.getElementById('harga_info').textContent = '';
    }
}

// Fungsi untuk memuat harga berdasarkan paket yang dipilih
function loadHargaByPaket(busId, jenisPaket) {
    if (busId && jenisPaket) {
        // AJAX request untuk mendapatkan harga berdasarkan paket
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_harga_by_paket.php?bus_id=' + busId + '&jenis_paket=' + encodeURIComponent(jenisPaket), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    document.getElementById('total_harga').value = response.harga;
                    document.getElementById('harga_info').textContent = 'Harga: Rp ' + formatRupiah(response.harga);
                    document.getElementById('harga_info').style.color = 'green';
                } else {
                    document.getElementById('total_harga').value = '';
                    document.getElementById('harga_info').textContent = response.message;
                    document.getElementById('harga_info').style.color = 'red';
                }
            }
        };
        xhr.send();
    }
}

// Format angka ke format Rupiah
function formatRupiah(angka) {
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Event listener untuk perubahan bus
document.getElementById('bus_select').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    var kapasitas = selectedOption.getAttribute('data-kapasitas');
    var busId = this.value;
    
    // Tampilkan informasi kapasitas
    if (kapasitas) {
        document.getElementById('kapasitas_info').textContent = 'Kapasitas bus: ' + kapasitas + ' penumpang';
        document.getElementById('kapasitas_info').style.color = 'blue';
    } else {
        document.getElementById('kapasitas_info').textContent = '';
    }
    
    // Muat paket berdasarkan bus
    loadPaketByBus(busId);
    
    checkCapacity();
});

// Event listener untuk perubahan paket
document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'jenis_paket_select') {
        var busId = document.getElementById('bus_select').value;
        var jenisPaket = e.target.value;
        loadHargaByPaket(busId, jenisPaket);
    }
});

// Validasi jumlah penumpang tidak melebihi kapasitas
document.getElementById('jumlah_penumpang').addEventListener('input', checkCapacity);

function checkCapacity() {
    var busSelect = document.getElementById('bus_select');
    var jumlahPenumpang = document.getElementById('jumlah_penumpang');
    var warning = document.getElementById('kapasitas_warning');
    
    if (busSelect.value && jumlahPenumpang.value) {
        var selectedOption = busSelect.options[busSelect.selectedIndex];
        var kapasitas = parseInt(selectedOption.getAttribute('data-kapasitas'));
        var penumpang = parseInt(jumlahPenumpang.value);
        
        if (penumpang > kapasitas) {
            warning.style.display = 'block';
        } else {
            warning.style.display = 'none';
        }
    } else {
        warning.style.display = 'none';
    }
}

function lihatDataSebelumnya() {
    $('#modalDataSebelumnya').modal('show');
}

// Validasi form
document.getElementById('editPesananForm').addEventListener('submit', function(e) {
    var tanggalBerangkat = new Date(document.querySelector('input[name="tanggal_berangkat"]').value);
    var tanggalKembali = new Date(document.querySelector('input[name="tanggal_kembali"]').value);
    var today = new Date();
    today.setHours(0, 0, 0, 0);

    if (tanggalBerangkat < today) {
        e.preventDefault();
        alert('Tanggal berangkat tidak boleh kurang dari hari ini');
        return false;
    }

    if (tanggalKembali < tanggalBerangkat) {
        e.preventDefault();
        alert('Tanggal kembali tidak boleh kurang dari tanggal berangkat');
        return false;
    }
    
    // Validasi kapasitas
    var busSelect = document.getElementById('bus_select');
    var jumlahPenumpang = document.getElementById('jumlah_penumpang');
    
    if (busSelect.value && jumlahPenumpang.value) {
        var selectedOption = busSelect.options[busSelect.selectedIndex];
        var kapasitas = parseInt(selectedOption.getAttribute('data-kapasitas'));
        var penumpang = parseInt(jumlahPenumpang.value);
        
        if (penumpang > kapasitas) {
            e.preventDefault();
            alert('Jumlah penumpang melebihi kapasitas bus!');
            return false;
        }
    }
});

// Trigger pada saat halaman dimuat
window.onload = function() {
    var busSelect = document.getElementById('bus_select');
    if (busSelect.value) {
        // Set info kapasitas awal
        var selectedOption = busSelect.options[busSelect.selectedIndex];
        var kapasitas = selectedOption.getAttribute('data-kapasitas');
        document.getElementById('kapasitas_info').textContent = 'Kapasitas bus: ' + kapasitas + ' penumpang';
        document.getElementById('kapasitas_info').style.color = 'blue';
        
        // Set info harga awal
        document.getElementById('harga_info').textContent = 'Harga: Rp ' + formatRupiah(<?php echo $booking['total_harga']; ?>);
        document.getElementById('harga_info').style.color = 'green';
        
        checkCapacity();
    }
};
</script>

</body>
</html>