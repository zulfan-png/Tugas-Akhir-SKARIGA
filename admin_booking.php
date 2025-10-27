<?php
include 'koneksi.php';
session_start();

// Handle actions (konfirmasi/batal/edit)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'confirm') {
        $query = "UPDATE pemesanan SET status = 'Dikonfirmasi' WHERE id = $booking_id";
        $message = "Pesanan berhasil dikonfirmasi";
    } elseif ($action == 'cancel') {
        $query = "UPDATE pemesanan SET status = 'Dibatalkan' WHERE id = $booking_id";
        $message = "Pesanan berhasil dibatalkan";
    }
    
    if (mysqli_query($connect, $query)) {
        $success_message = $message;
    } else {
        $error_message = "Terjadi kesalahan: " . mysqli_error($connect);
    }
}

// Handle edit form submission - HANYA UNTUK STATUS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_booking'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    
    $query = "UPDATE pemesanan SET status = '$status' WHERE id = $booking_id";
    
    if (mysqli_query($connect, $query)) {
        $success_message = "Status pesanan berhasil diupdate";
    } else {
        $error_message = "Terjadi kesalahan: " . mysqli_error($connect);
    }
}

// Query semua pemesanan - TAMBAH jam_berangkat
$query = "SELECT p.*, u.nama_lengkap, u.nomor_hp, b.`tipe bus`, b.jenis, per.nama_perusahaan 
          FROM pemesanan p 
          JOIN datauser u ON p.user_id = u.id 
          JOIN bus b ON p.bus_id = b.id 
          JOIN perusahaan_bus per ON b.perusahaan_id = per.id 
          ORDER BY p.created_at DESC";
$result = mysqli_query($connect, $query);
?>

<?php include 'header.php'; ?>

<?php include 'sidebar.php'; ?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Management Pesanan Bus
            <small>Kelola semua pemesanan bus</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Management Pesanan</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-check"></i> Sukses!</h4>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-ban"></i> Error!</h4>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Daftar Semua Pesanan</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($booking = mysqli_fetch_array($result)): ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h4 class="panel-title">
                                                    <i class="fa fa-bus"></i>
                                                    <?php echo $booking['nama_perusahaan'] ?> - <?php echo $booking['tipe bus'] ?>
                                                </h4>
                                                <small>
                                                    <strong><?php echo $booking['nama_lengkap'] ?></strong> 
                                                    (<?php echo $booking['nomor_hp'] ?>)
                                                </small>
                                            </div>
                                            <div class="col-md-4 text-right">
                                                <span class="label 
                                                    <?php 
                                                    if ($booking['status'] == 'Menunggu Konfirmasi') echo 'label-warning';
                                                    elseif ($booking['status'] == 'Dikonfirmasi') echo 'label-success';
                                                    else echo 'label-danger';
                                                    ?>">
                                                    <?php echo $booking['status'] ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <strong>Jenis Paket:</strong><br>
                                                        <?php echo $booking['jenis_paket'] ?>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <strong>Tanggal Berangkat:</strong><br>
                                                        <?php echo date('d M Y', strtotime($booking['tanggal_berangkat'])) ?> 
                                                        pukul <?php echo date('H:i', strtotime($booking['jam_berangkat'])) ?>
                                                    </div>
                                                </div>
                                                <?php if ($booking['tanggal_kembali'] != $booking['tanggal_berangkat']): ?>
                                                <div class="row" style="margin-top: 10px;">
                                                    <div class="col-sm-6">
                                                        <strong>Tanggal Kembali:</strong><br>
                                                        <?php echo date('d M Y', strtotime($booking['tanggal_kembali'])) ?>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <strong>Jumlah Penumpang:</strong><br>
                                                        <?php echo $booking['jumlah_penumpang'] ?> orang
                                                    </div>
                                                </div>
                                                <?php else: ?>
                                                <div class="row" style="margin-top: 10px;">
                                                    <div class="col-sm-6">
                                                        <strong>Jumlah Penumpang:</strong><br>
                                                        <?php echo $booking['jumlah_penumpang'] ?> orang
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                <div class="row" style="margin-top: 10px;">
                                                    <div class="col-sm-12">
                                                        <strong>Lokasi Penjemputan:</strong><br>
                                                        <?php echo $booking['lokasi_penjemputan'] ?>
                                                    </div>
                                                </div>
                                                <div class="row" style="margin-top: 10px;">
                                                    <div class="col-sm-12">
                                                        <strong>Tujuan:</strong><br>
                                                        <?php echo $booking['tujuan'] ?>
                                                    </div>
                                                </div>
                                                <?php if (!empty($booking['keterangan'])): ?>
                                                <div class="row" style="margin-top: 10px;">
                                                    <div class="col-sm-12">
                                                        <strong>Keterangan:</strong><br>
                                                        <?php echo $booking['keterangan'] ?>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <strong>Tanggal Pesan:</strong><br>
                                                        <?php echo date('d M Y H:i', strtotime($booking['created_at'])) ?>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <strong>Total Harga:</strong><br>
                                                        <span class="text-primary" style="font-size: 18px; font-weight: bold;">
                                                            Rp <?php echo number_format($booking['total_harga'], 0, ',', '.') ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="row" style="margin-top: 20px;">
                                                    <div class="col-sm-12">
                                                        <div class="btn-group">
                                                            <?php if ($booking['status'] == 'Menunggu Konfirmasi'): ?>
                                                                <a href="admin_booking.php?action=confirm&id=<?php echo $booking['id'] ?>" 
                                                                   class="btn btn-success btn-sm">
                                                                    <i class="fa fa-check"></i> Setujui
                                                                </a>
                                                                <a href="admin_booking.php?action=cancel&id=<?php echo $booking['id'] ?>" 
                                                                   class="btn btn-danger btn-sm">
                                                                    <i class="fa fa-times"></i> Tolak
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="btn btn-success btn-sm" disabled>
                                                                    <i class="fa fa-check"></i> Setujui
                                                                </button>
                                                                <button class="btn btn-danger btn-sm" disabled>
                                                                    <i class="fa fa-times"></i> Tolak
                                                                </button>
                                                            <?php endif; ?>
                                                            <button class="btn btn-primary btn-sm" onclick="openEditModal(<?php echo $booking['id'] ?>, '<?php echo $booking['status'] ?>')">
                                                                <i class="fa fa-edit"></i> Edit Status
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center" style="padding: 40px;">
                                <i class="fa fa-clipboard-list" style="font-size: 64px; color: #d2d6de; margin-bottom: 20px;"></i>
                                <h3 style="color: #999;">Belum ada pesanan</h3>
                                <p style="color: #999;">Menunggu customer melakukan pemesanan</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- /.box-body -->
                </div>
                <!-- /.box -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- Modal Edit Status -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="editModalLabel">Edit Status Pesanan</h4>
            </div>
            <form method="POST" id="editForm">
                <div class="modal-body">
                    <input type="hidden" name="booking_id" id="edit_booking_id">
                    <input type="hidden" name="edit_booking" value="1">
                    
                    <div class="form-group">
                        <label>Status Pesanan</label>
                        <select name="status" id="edit_status" class="form-control" required>
                            <option value="Menunggu Konfirmasi">Menunggu Konfirmasi</option>
                            <option value="Dikonfirmasi">Dikonfirmasi</option>
                            <option value="Dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        <strong>Informasi:</strong> Tombol edit hanya untuk mengubah status pesanan. 
                        Data lainnya tidak dapat diubah untuk menjaga integritas data.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Simpan Perubahan Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Footer -->
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
    function openEditModal(bookingId, currentStatus) {
        // Isi form dengan data yang ada
        document.getElementById('edit_booking_id').value = bookingId;
        document.getElementById('edit_status').value = currentStatus;
        
        // Tampilkan modal
        $('#editModal').modal('show');
    }
</script>

</body>
</html>