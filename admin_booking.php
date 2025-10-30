<?php
include 'koneksi.php';
session_start();

// Handle actions (konfirmasi/batal/edit/approve_edit/approve_cancel/selesai)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'confirm') {
        $query = "UPDATE pemesanan SET status = 'Dikonfirmasi' WHERE id = $booking_id";
        $message = "Pesanan berhasil dikonfirmasi";
        
    } elseif ($action == 'cancel') {
        $query = "UPDATE pemesanan SET status = 'Dibatalkan' WHERE id = $booking_id";
        $message = "Pesanan berhasil dibatalkan";
        
        // Kembalikan status bus menjadi Tersedia jika dibatalkan
        $bus_query = "SELECT bus_id FROM pemesanan WHERE id = $booking_id";
        $bus_result = mysqli_query($connect, $bus_query);
        $bus_data = mysqli_fetch_assoc($bus_result);
        
        if ($bus_data) {
            $update_bus = "UPDATE bus SET status = 'Tersedia' WHERE id = {$bus_data['bus_id']}";
            mysqli_query($connect, $update_bus);
        }
    } elseif ($action == 'selesai') {
        $query = "UPDATE pemesanan SET status = 'Selesai' WHERE id = $booking_id";
        $message = "Pesanan telah diselesaikan";
        
        // Kembalikan status bus menjadi Tersedia
        $bus_query = "SELECT bus_id FROM pemesanan WHERE id = $booking_id";
        $bus_result = mysqli_query($connect, $bus_query);
        $bus_data = mysqli_fetch_assoc($bus_result);
        
        if ($bus_data) {
            $update_bus = "UPDATE bus SET status = 'Tersedia' WHERE id = {$bus_data['bus_id']}";
            mysqli_query($connect, $update_bus);
        }
    } elseif ($action == 'approve_edit') {
        // Setujui edit - reset permintaan edit
        $query = "UPDATE pemesanan SET permintaan_edit = 'Tidak', data_edit_sebelumnya = NULL WHERE id = $booking_id";
        $message = "Permintaan edit telah disetujui";
    } elseif ($action == 'reject_edit') {
        // Tolak edit - kembalikan data sebelumnya
        $query_get_old = "SELECT data_edit_sebelumnya FROM pemesanan WHERE id = $booking_id";
        $result_old = mysqli_query($connect, $query_get_old);
        $old_data = mysqli_fetch_assoc($result_old);
        
        if ($old_data['data_edit_sebelumnya']) {
            $data_lama = json_decode($old_data['data_edit_sebelumnya'], true);
            $query = "UPDATE pemesanan SET 
                      jenis_paket = '".mysqli_real_escape_string($connect, $data_lama['jenis_paket'])."',
                      tanggal_berangkat = '".mysqli_real_escape_string($connect, $data_lama['tanggal_berangkat'])."',
                      jam_berangkat = '".mysqli_real_escape_string($connect, $data_lama['jam_berangkat'])."',
                      tanggal_kembali = '".mysqli_real_escape_string($connect, $data_lama['tanggal_kembali'])."',
                      lokasi_penjemputan = '".mysqli_real_escape_string($connect, $data_lama['lokasi_penjemputan'])."',
                      tujuan = '".mysqli_real_escape_string($connect, $data_lama['tujuan'])."',
                      jumlah_penumpang = ".intval($data_lama['jumlah_penumpang']).",
                      keterangan = '".mysqli_real_escape_string($connect, $data_lama['keterangan'])."',
                      permintaan_edit = 'Tidak',
                      data_edit_sebelumnya = NULL
                      WHERE id = $booking_id";
        } else {
            $query = "UPDATE pemesanan SET permintaan_edit = 'Tidak' WHERE id = $booking_id";
        }
        $message = "Permintaan edit telah ditolak";
    } elseif ($action == 'approve_cancel') {
        // Setujui pembatalan
        $query = "UPDATE pemesanan SET status = 'Dibatalkan', permintaan_batal = 'Tidak' WHERE id = $booking_id";
        $message = "Permintaan pembatalan telah disetujui";
        
        // Kembalikan status bus menjadi Tersedia
        $bus_query = "SELECT bus_id FROM pemesanan WHERE id = $booking_id";
        $bus_result = mysqli_query($connect, $bus_query);
        $bus_data = mysqli_fetch_assoc($bus_result);
        
        if ($bus_data) {
            $update_bus = "UPDATE bus SET status = 'Tersedia' WHERE id = {$bus_data['bus_id']}";
            mysqli_query($connect, $update_bus);
        }
    } elseif ($action == 'reject_cancel') {
        // Tolak pembatalan
        $query = "UPDATE pemesanan SET permintaan_batal = 'Tidak', alasan_batal = NULL WHERE id = $booking_id";
        $message = "Permintaan pembatalan telah ditolak";
    }
    
    if (isset($query) && mysqli_query($connect, $query)) {
        $success_message = $message;
    } else {
        $error_message = "Terjadi kesalahan: " . mysqli_error($connect);
    }
}

// Handle edit form submission - UNTUK STATUS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_booking'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    $old_status = $_POST['old_status'];
    
    $query = "UPDATE pemesanan SET status = '$status' WHERE id = $booking_id";
    
    if (mysqli_query($connect, $query)) {
        // Handle perubahan status bus berdasarkan perubahan status booking
        $bus_query = "SELECT bus_id FROM pemesanan WHERE id = $booking_id";
        $bus_result = mysqli_query($connect, $bus_query);
        $bus_data = mysqli_fetch_assoc($bus_result);
        
        if ($bus_data) {
            $bus_status = 'Tersedia'; // default
            
            if ($status == 'Dikonfirmasi') {
                $bus_status = 'Disewa';
            } elseif ($status == 'Selesai' || $status == 'Dibatalkan') {
                $bus_status = 'Tersedia';
            } elseif ($status == 'Menunggu Konfirmasi' && $old_status == 'Dikonfirmasi') {
                $bus_status = 'Tersedia';
            }
            
            $update_bus = "UPDATE bus SET status = '$bus_status' WHERE id = {$bus_data['bus_id']}";
            mysqli_query($connect, $update_bus);
        }
        
        $success_message = "Status pesanan berhasil diupdate";
    } else {
        $error_message = "Terjadi kesalahan: " . mysqli_error($connect);
    }
}

// GANTI query utama di admin_booking.php - bagian SELECT
$query = "SELECT p.*, u.nama_lengkap, u.nomor_hp, b.`tipe bus`, b.jenis, 
          b.status as status_bus_tabel,  -- status dari tabel bus
          per.nama_perusahaan, per.whatsapp_perusahaan,
          
          -- Hitung status availability berdasarkan pemesanan aktif
          (SELECT COUNT(*) FROM pemesanan p2 
           WHERE p2.bus_id = b.id 
           AND p2.status = 'Dikonfirmasi'
           AND p2.tanggal_berangkat <= p.tanggal_kembali 
           AND p2.tanggal_kembali >= p.tanggal_berangkat
           AND p2.id != p.id) as ada_pemesanan_aktif,
           
          (SELECT GROUP_CONCAT(CONCAT('Pesanan #', p3.id, ' (', p3.tanggal_berangkat, ' s/d ', p3.tanggal_kembali, ')') SEPARATOR '; ')
           FROM pemesanan p3 
           WHERE p3.bus_id = b.id 
           AND p3.status = 'Dikonfirmasi'
           AND p3.tanggal_berangkat <= p.tanggal_kembali 
           AND p3.tanggal_kembali >= p.tanggal_berangkat
           AND p3.id != p.id) as konflik_detail
           
          FROM pemesanan p 
          JOIN datauser u ON p.user_id = u.id 
          JOIN bus b ON p.bus_id = b.id 
          JOIN perusahaan_bus per ON b.perusahaan_id = per.id 
          ORDER BY p.created_at DESC";
$result = mysqli_query($connect, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin | Management Pesanan</title>
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
                        <div class="box-tools pull-right">
                            <a href="admin_pemesanan_tambah.php" class="btn btn-sm btn-success">
                                <i class="fa fa-plus"></i> Tambah Pemesanan Manual
                            </a>
                        </div>
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
                                                    <?php if ($booking['permintaan_edit'] == 'Ya'): ?>
                                                        <span class="label label-warning" style="margin-left: 10px;">
                                                            <i class="fa fa-edit"></i> Permintaan Edit
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($booking['permintaan_batal'] == 'Ya'): ?>
                                                        <span class="label label-danger" style="margin-left: 10px;">
                                                            <i class="fa fa-times"></i> Permintaan Batal
                                                        </span>
                                                    <?php endif; ?>
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
                                                    elseif ($booking['status'] == 'Dikonfirmasi') echo 'label-primary';
                                                    elseif ($booking['status'] == 'Selesai') echo 'label-success';
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
                                                <?php if (!empty($booking['alasan_batal'])): ?>
                                                <div class="row" style="margin-top: 10px;">
                                                    <div class="col-sm-12">
                                                        <strong>Alasan Pembatalan:</strong><br>
                                                        <span style="color: #dc3545;"><?php echo $booking['alasan_batal'] ?></span>
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
                                                
                                                <!-- Tampilkan informasi permintaan edit/batal -->
                                                <?php if ($booking['permintaan_edit'] == 'Ya'): ?>
                                                    <div class="alert alert-warning" style="margin-top: 10px; padding: 10px;">
                                                        <strong><i class="fa fa-edit"></i> Permintaan Edit</strong>
                                                        <br>User mengajukan perubahan data pesanan.
                                                        <div class="btn-group" style="margin-top: 5px;">
                                                            <a href="admin_booking.php?action=approve_edit&id=<?php echo $booking['id'] ?>" 
                                                               class="btn btn-success btn-xs">
                                                                <i class="fa fa-check"></i> Setujui Edit
                                                            </a>
                                                            <a href="admin_booking.php?action=reject_edit&id=<?php echo $booking['id'] ?>" 
                                                               class="btn btn-danger btn-xs">
                                                                <i class="fa fa-times"></i> Tolak Edit
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($booking['permintaan_batal'] == 'Ya'): ?>
                                                    <div class="alert alert-danger" style="margin-top: 10px; padding: 10px;">
                                                        <strong><i class="fa fa-times"></i> Permintaan Pembatalan</strong>
                                                        <br>User mengajukan pembatalan pesanan.
                                                        <div class="btn-group" style="margin-top: 5px;">
                                                            <a href="admin_booking.php?action=approve_cancel&id=<?php echo $booking['id'] ?>" 
                                                               class="btn btn-success btn-xs">
                                                                <i class="fa fa-check"></i> Setujui Batal
                                                            </a>
                                                            <a href="admin_booking.php?action=reject_cancel&id=<?php echo $booking['id'] ?>" 
                                                               class="btn btn-danger btn-xs">
                                                                <i class="fa fa-times"></i> Tolak Batal
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="row" style="margin-top: 20px;">
                                                    <div class="col-sm-12">
                                                        <div class="btn-group">
                                                            <?php if ($booking['status'] == 'Menunggu Konfirmasi' && $booking['permintaan_batal'] == 'Tidak'): ?>
                                                                <a href="admin_booking.php?action=confirm&id=<?php echo $booking['id'] ?>" 
                                                                   class="btn btn-success btn-sm">
                                                                    <i class="fa fa-check"></i> Setujui
                                                                </a>
                                                                <a href="admin_booking.php?action=cancel&id=<?php echo $booking['id'] ?>" 
                                                                   class="btn btn-danger btn-sm">
                                                                    <i class="fa fa-times"></i> Tolak
                                                                </a>
                                                            <?php elseif ($booking['status'] == 'Dikonfirmasi'): ?>
                                                                <a href="admin_booking.php?action=selesai&id=<?php echo $booking['id'] ?>" 
                                                                   class="btn btn-success btn-sm">
                                                                    <i class="fa fa-flag-checkered"></i> Selesai
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="btn btn-success btn-sm" disabled>
                                                                    <i class="fa fa-check"></i> Setujui
                                                                </button>
                                                                <button class="btn btn-danger btn-sm" disabled>
                                                                    <i class="fa fa-times"></i> Tolak
                                                                </button>
                                                            <?php endif; ?>
                                                            <a href="admin_pesanan_edit.php?id=<?php echo $booking['id'] ?>" class="btn btn-primary btn-sm">
                                                                <i class="fa fa-edit"></i> Edit Pesanan
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Tampilkan status bus dengan informasi "Tidak Tersedia" -->
<div style="margin-top: 10px;">
    <small class="text-muted">
        <i class="fa fa-bus"></i> Status Ketersediaan: 
        <span class="label 
            <?php 
            // Status berdasarkan pemesanan aktif, BUKAN dari tabel bus
            if ($booking['ada_pemesanan_aktif'] > 0) {
                echo 'label-danger';
                $status_text = 'Tidak Tersedia (Ada Pemesanan Aktif)';
            } else {
                // Jika tidak ada pemesanan aktif, gunakan status dari tabel bus
                if ($booking['status_bus_tabel'] == 'Tersedia') {
                    echo 'label-success';
                    $status_text = 'Tersedia';
                } elseif ($booking['status_bus_tabel'] == 'Dalam Perawatan') {
                    echo 'label-warning';
                    $status_text = 'Dalam Perawatan';
                } elseif ($booking['status_bus_tabel'] == 'Tidak Tersedia') {
                    echo 'label-danger';
                    $status_text = 'Tidak Tersedia';
                } else {
                    echo 'label-default';
                    $status_text = $booking['status_bus_tabel'];
                }
            }
            ?>">
            <?php echo $status_text; ?>
        </span>
    </small>
    
    <!-- PERINGATAN JIKA ADA KONFLIK JADWAL (bus sudah dipesan pesanan lain yang dikonfirmasi) -->
    <?php if ($booking['ada_pemesanan_aktif'] > 0 && $booking['status'] != 'Dikonfirmasi'): ?>
        <div class="alert alert-danger" style="margin-top: 8px; padding: 5px 10px; margin-bottom: 0;">
            <small>
                <i class="fa fa-exclamation-circle"></i> 
                <strong>KONFLIK JADWAL:</strong> Bus ini sudah dipesan pada tanggal: 
                <?php 
                $konflik_list = explode('; ', $booking['konflik_detail']);
                foreach ($konflik_list as $index => $konflik) {
                    if ($index > 0) echo ', ';
                    echo $konflik;
                }
                ?>
            </small>
        </div>
    <?php endif; ?>
</div>  

                                                <!-- Tombol WhatsApp -->
                                                <div style="margin-top: 10px;">
                                                    <?php if (!empty($booking['whatsapp_perusahaan'])): ?>
                                                        <a href="https://wa.me/<?php echo $booking['whatsapp_perusahaan']; ?>?text=Halo,%20saya%20admin%20BISATA.%20Konfirmasi%20pesanan%20bus:%0A%0A*Pemesan:* <?php echo urlencode($booking['nama_lengkap']); ?>%0A*Bus:* <?php echo urlencode($booking['nama_perusahaan'] . ' - ' . $booking['tipe bus']); ?>%0A*Tanggal:* <?php echo urlencode(date('d M Y', strtotime($booking['tanggal_berangkat']))); ?>%0A*Status:* <?php echo urlencode($booking['status']); ?>%0A%0ATerima%20kasih." 
                                                        target="_blank" 
                                                        class="btn btn-success btn-xs">
                                                            <i class="fa fa-whatsapp"></i> WhatsApp Perusahaan
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($booking['nomor_hp'])): ?>
                                                        <a href="https://wa.me/62<?php echo substr($booking['nomor_hp'], 1); ?>?text=Halo%20<?php echo urlencode($booking['nama_lengkap']); ?>,%20saya%20admin%20BISATA.%20Mengenai%20pesanan%20bus%20Anda:%0A%0A*Bus:* <?php echo urlencode($booking['nama_perusahaan'] . ' - ' . $booking['tipe bus']); ?>%0A*Tanggal:* <?php echo urlencode(date('d M Y', strtotime($booking['tanggal_berangkat']))); ?>%0A*Status:* <?php echo urlencode($booking['status']); ?>%0A%0AApakah%20ada%20yang%20bisa%20kami%20bantu?" 
                                                        target="_blank" 
                                                        class="btn btn-info btn-xs">
                                                            <i class="fa fa-whatsapp"></i> WhatsApp Pemesan
                                                        </a>
                                                    <?php endif; ?>
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
                    <input type="hidden" name="old_status" id="edit_old_status">
                    <input type="hidden" name="edit_booking" value="1">
                    
                    <div class="form-group">
                        <label>Status Pesanan</label>
                        <select name="status" id="edit_status" class="form-control" required>
                            <option value="Menunggu Konfirmasi">Menunggu Konfirmasi</option>
                            <option value="Dikonfirmasi">Dikonfirmasi</option>
                            <option value="Selesai">Selesai</option>
                            <option value="Dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        <strong>Informasi:</strong> 
                        <ul>
                            <li>Status "Dikonfirmasi" akan mengubah status bus menjadi "Disewa"</li>
                            <li>Status "Selesai" atau "Dibatalkan" akan mengembalikan status bus menjadi "Tersedia"</li>
                            <li>Tombol edit hanya untuk mengubah status pesanan</li>
                        </ul>
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
        document.getElementById('edit_old_status').value = currentStatus;
        
        // Tampilkan modal
        $('#editModal').modal('show');
    }
</script>

</body>
</html>