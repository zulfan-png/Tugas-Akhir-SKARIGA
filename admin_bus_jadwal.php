<?php
// admin_bus_jadwal.php
include 'koneksi.php';
session_start();

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query untuk mengambil data bus dengan atau tanpa pencarian
$query = "SELECT b.*, p.nama_perusahaan, p.whatsapp_perusahaan as whatsapp_perusahaan_bus,
          (SELECT COUNT(*) FROM pemesanan p2 
           WHERE p2.bus_id = b.id 
           AND p2.tanggal_berangkat = CURDATE() 
           AND p2.status = 'Dikonfirmasi') as pesanan_hari_ini,
          (SELECT COUNT(*) FROM pemesanan p3 
           WHERE p3.bus_id = b.id 
           AND p3.tanggal_berangkat >= CURDATE() 
           AND p3.status = 'Dikonfirmasi') as total_pesanan_aktif
          FROM bus b 
          LEFT JOIN perusahaan_bus p ON b.perusahaan_id = p.id 
          WHERE 1=1";

// Tambahkan kondisi pencarian jika ada
if (!empty($search)) {
    $search = mysqli_real_escape_string($connect, $search);
    $query .= " AND (b.`tipe bus` LIKE '%$search%' 
                    OR b.jenis LIKE '%$search%'
                    OR p.nama_perusahaan LIKE '%$search%'
                    OR b.id IN (
                        SELECT DISTINCT p4.bus_id 
                        FROM pemesanan p4 
                        JOIN datauser u ON p4.user_id = u.id 
                        WHERE u.nama_lengkap LIKE '%$search%' 
                        OR u.username LIKE '%$search%'
                    ))";
}

$query .= " ORDER BY b.status, p.nama_perusahaan, b.`tipe bus`";

$result = mysqli_query($connect, $query);

// Ambil data pemesanan untuk jadwal
$jadwal_query = "SELECT p.*, b.`tipe bus`, per.nama_perusahaan, u.nama_lengkap 
                FROM pemesanan p 
                JOIN bus b ON p.bus_id = b.id 
                JOIN perusahaan_bus per ON b.perusahaan_id = per.id 
                JOIN datauser u ON p.user_id = u.id 
                WHERE p.status = 'Dikonfirmasi' 
                AND p.tanggal_berangkat >= CURDATE() 
                ORDER BY p.tanggal_berangkat, p.jam_berangkat";
$jadwal_result = mysqli_query($connect, $jadwal_query);

// Ambil SEMUA data bus untuk kalender
$calendar_bus_query = "SELECT b.id, b.`tipe bus`, p.nama_perusahaan, b.status 
                     FROM bus b 
                     JOIN perusahaan_bus p ON b.perusahaan_id = p.id 
                     ORDER BY p.nama_perusahaan, b.`tipe bus`";
$calendar_bus_result = mysqli_query($connect, $calendar_bus_query);
?>

<?php include 'header.php'; ?>

<?php include 'sidebar.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <h1>
            Jadwal Bus
            <small>Management Ketersediaan dan Jadwal Bus</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Jadwal Bus</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">

        <!-- Statistik -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-bus"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Bus Tersedia</span>
                        <span class="info-box-number">
                            <?php
                            $tersedia_query = "SELECT COUNT(*) as total FROM bus WHERE status = 'Tersedia'";
                            $tersedia_result = mysqli_query($connect, $tersedia_query);
                            $tersedia = mysqli_fetch_array($tersedia_result);
                            echo $tersedia['total'];
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-wrench"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Dalam Perawatan</span>
                        <span class="info-box-number">
                            <?php
                            $perawatan_query = "SELECT COUNT(*) as total FROM bus WHERE status = 'Dalam Perawatan'";
                            $perawatan_result = mysqli_query($connect, $perawatan_query);
                            $perawatan = mysqli_fetch_array($perawatan_result);
                            echo $perawatan['total'];
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-times"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Tidak Tersedia</span>
                        <span class="info-box-number">
                            <?php
                            $tidak_tersedia_query = "SELECT COUNT(*) as total FROM bus WHERE status = 'Tidak Tersedia'";
                            $tidak_tersedia_result = mysqli_query($connect, $tidak_tersedia_query);
                            $tidak_tersedia = mysqli_fetch_array($tidak_tersedia_result);
                            echo $tidak_tersedia['total'];
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-blue"><i class="fa fa-calendar-check-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pesanan Aktif</span>
                        <span class="info-box-number">
                            <?php
                            $pesanan_query = "SELECT COUNT(*) as total FROM pemesanan WHERE status = 'Dikonfirmasi' AND tanggal_berangkat >= CURDATE()";
                            $pesanan_result = mysqli_query($connect, $pesanan_query);
                            $pesanan = mysqli_fetch_array($pesanan_result);
                            echo $pesanan['total'];
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar View -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-calendar"></i> Kalender Ketersediaan Bus (7 Hari ke Depan)
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body" style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table table-bordered" style="margin-bottom: 0;">
                                <thead>
                                    <tr class="bg-gray-light">
                                        <th width="18%" class="text-center" style="border-right: 2px solid #ddd; background-color: #f8f9fa !important;">
                                            <div style="font-weight: bold; color: #333; font-size: 14px;">BUS</div>
                                        </th>
                                        <?php
                                        // Generate 7 hari ke depan
                                        for ($i = 0; $i < 7; $i++):
                                            $date = date('Y-m-d', strtotime("+$i days"));
                                            $day_name_id = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                            $day_name = $day_name_id[date('w', strtotime($date))];
                                            $day_number = date('d', strtotime($date));
                                            $month_id = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                            $month = $month_id[date('n', strtotime($date)) - 1];
                                            $is_today = ($i == 0);
                                        ?>
                                            <th class="text-center" width="11.7%" style="border-right: 1px solid #ddd; <?php echo $is_today ? 'background-color: #e3f2fd !important;' : 'background-color: #f8f9fa !important;'; ?>">
                                                <div style="font-size: 12px; color: #666; font-weight: normal;"><?php echo $day_name ?></div>
                                                <div style="font-size: 16px; color: #333; font-weight: bold;"><?php echo $day_number ?></div>
                                                <div style="font-size: 12px; color: #666; font-weight: normal;"><?php echo $month ?></div>
                                                <?php if ($is_today): ?>
                                                    <div style="font-size: 10px; color: #2196F3; font-weight: bold;">HARI INI</div>
                                                <?php endif; ?>
                                            </th>
                                        <?php endfor; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Tampilkan SEMUA bus di kalender
                                    if ($calendar_bus_result && mysqli_num_rows($calendar_bus_result) > 0) {
                                        mysqli_data_seek($calendar_bus_result, 0);
                                        $row_count = 0;
                                        while($bus = mysqli_fetch_array($calendar_bus_result)):
                                            $row_count++;
                                            $row_class = ($row_count % 2 == 0) ? 'bg-white' : 'bg-gray-light';
                                    ?>
                                        <tr class="<?php echo $row_class; ?>">
                                            <td style="border-right: 2px solid #ddd; vertical-align: middle;">
                                                <div style="padding: 8px;">
                                                    <div style="font-weight: bold; color: #333; font-size: 13px;">
                                                        <?php echo $bus['nama_perusahaan'] ?>
                                                    </div>
                                                    <div style="color: #666; font-size: 12px; margin-top: 2px;">
                                                        <?php echo $bus['tipe bus'] ?>
                                                    </div>
                                                    <div style="margin-top: 4px;">
                                                        <span class="label label-<?php 
                                                            echo $bus['status'] == 'Tersedia' ? 'success' : 
                                                                 ($bus['status'] == 'Dalam Perawatan' ? 'warning' : 
                                                                 ($bus['status'] == 'Disewa' ? 'info' : 'danger'));
                                                        ?>" style="font-size: 10px; padding: 3px 6px;">
                                                            <?php echo $bus['status'] ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <?php
                                            for ($i = 0; $i < 7; $i++):
                                            $current_date = date('Y-m-d', strtotime("+$i days"));
                                            $is_today = ($i == 0);
                                                
                                                // Query untuk mengecek pemesanan aktif pada tanggal spesifik
    $pesanan_query = "SELECT p.tanggal_berangkat, p.tanggal_kembali, 
                    b.`tipe bus`, per.nama_perusahaan, u.nama_lengkap
                    FROM pemesanan p
                    JOIN bus b ON p.bus_id = b.id
                    JOIN perusahaan_bus per ON b.perusahaan_id = per.id  
                    JOIN datauser u ON p.user_id = u.id
                    WHERE p.bus_id = " . $bus['id'] . " 
                    AND p.status = 'Dikonfirmasi'
                    AND '$current_date' BETWEEN p.tanggal_berangkat AND p.tanggal_kembali
                    LIMIT 1";
    
                    $pesanan_result = mysqli_query($connect, $pesanan_query);
                    
                    // Cek status bus dari tabel bus
                    $status_bus = $bus['status'];
                    
                    if ($pesanan_result && mysqli_num_rows($pesanan_result) > 0) {
                        // Jika ada pemesanan yang dikonfirmasi pada tanggal ini, status = Disewa
                        $pesanan = mysqli_fetch_array($pesanan_result);
                        $status_kalender = 'disewa';
                        $badge_class = 'info';
                        $badge_icon = 'fa-road';
                        $badge_text = 'Disewa';
                        $title = "Disewa dari " . date('d M Y', strtotime($pesanan['tanggal_berangkat'])) . 
                                " hingga " . date('d M Y', strtotime($pesanan['tanggal_kembali']));
                        $bg_color = 'background-color: #e3f2fd !important;';
                    } else {
                        // Jika tidak ada pemesanan, gunakan status dari tabel bus
                        if ($status_bus == 'Tersedia') {
                            $status_kalender = 'tersedia';
                            $badge_class = 'success';
                            $badge_icon = 'fa-check-circle';
                            $badge_text = 'Tersedia';
                            $title = "Tersedia pada " . date('d M Y', strtotime($current_date));
                            $bg_color = 'background-color: #e8f5e8 !important;';
                        } else if ($status_bus == 'Dalam Perawatan') {
                            $status_kalender = 'perawatan';
                            $badge_class = 'warning';
                            $badge_icon = 'fa-wrench';
                            $badge_text = 'Perawatan';
                            $title = "Bus dalam perawatan";
                            $bg_color = 'background-color: #fff3e0 !important;';
                        } else if ($status_bus == 'Disewa') {
                            // Status Disewa di tabel bus tapi tidak ada pemesanan aktif
                            $status_kalender = 'disewa_tabel';
                            $badge_class = 'info';
                            $badge_icon = 'fa-road';
                            $badge_text = 'Disewa*';
                            $title = "Status bus: Disewa (di tabel bus)";
                            $bg_color = 'background-color: #e3f2fd !important;';
                        } else {
                            $status_kalender = 'tidak_tersedia';
                            $badge_class = 'default';
                            $badge_icon = 'fa-ban';
                            $badge_text = 'Tidak Tersedia';
                            $title = "Bus tidak tersedia";
                            $bg_color = 'background-color: #f5f5f5 !important;';
                        }
                    }
                                            ?>
                                                <td class="text-center" style="vertical-align: middle; border-right: 1px solid #ddd; <?php echo $bg_color; ?><?php echo $is_today ? ' border-left: 3px solid #2196F3 !important;' : ''; ?>"
                                                    data-toggle="tooltip" 
                                                    title="<?php echo $title; ?>">
                                                    <div style="padding: 15px 5px;">
                                                        <i class="fa <?php echo $badge_icon; ?> text-<?php echo $badge_class; ?>" 
                                                           style="font-size: 18px; margin-bottom: 5px; display: block;"></i>
                                                        <span class="label label-<?php echo $badge_class; ?>" 
                                                              style="font-size: 10px; padding: 3px 6px; display: block;">
                                                            <?php echo $badge_text; ?>
                                                        </span>
                                                        <?php if ($status_kalender == 'disewa' && isset($pesanan)): ?>
                                                            <small style="display: block; margin-top: 3px; font-size: 9px; color: #666;">
                                                                <?php echo date('d M', strtotime($pesanan['tanggal_berangkat'])) . 
                                                                      ' - ' . date('d M', strtotime($pesanan['tanggal_kembali'])); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            <?php endfor; ?>
                                        </tr>
                                    <?php 
                                        endwhile;
                                    } else {
                                        echo '<tr><td colspan="8" class="text-center text-muted" style="padding: 20px; background-color: #f9f9f9;">Tidak ada bus untuk ditampilkan</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="box-footer" style="background-color: #f8f9fa; border-top: 1px solid #ddd;">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <div style="display: inline-block; margin: 0 10px;">
                                    <span class="label label-success" style="font-size: 11px;">
                                        <i class="fa fa-check-circle"></i> Tersedia
                                    </span>
                                </div>
                                <div style="display: inline-block; margin: 0 10px;">
                                    <span class="label label-info" style="font-size: 11px;">
                                        <i class="fa fa-road"></i> Disewa
                                    </span>
                                </div>
                                <div style="display: inline-block; margin: 0 10px;">
                                    <span class="label label-warning" style="font-size: 11px;">
                                        <i class="fa fa-wrench"></i> Perawatan
                                    </span>
                                </div>
                                <div style="display: inline-block; margin: 0 10px;">
                                    <span class="label label-default" style="font-size: 11px;">
                                        <i class="fa fa-ban"></i> Tidak Tersedia
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Bus - FULL WIDTH -->
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Daftar Bus</h3>
                <div class="box-tools pull-right">
                    <span class="label label-primary">
                        Total: <?php echo mysqli_num_rows($result); ?> Bus
                    </span>
                </div>
            </div>
            <div class="box-body">
                <!-- Form Pencarian -->
                <div class="row">
                    <div class="col-md-6 col-md-offset-6">
                        <form method="GET" action="">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan nama bus, jenis, perusahaan, atau username pemesan..." value="<?php echo htmlspecialchars($search); ?>">
                                <span class="input-group-btn">
                                    <?php if (!empty($search)): ?>
                                        <a href="admin_bus_jadwal.php" class="btn btn-default">
                                            <i class="fa fa-times"></i> Reset
                                        </a>
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-search"></i> Cari
                                    </button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if (!empty($search)): ?>
                    <div class="alert alert-info" style="margin-top: 15px;">
                        <i class="fa fa-info-circle"></i> 
                        Menampilkan hasil pencarian untuk: <strong>"<?php echo htmlspecialchars($search); ?>"</strong>
                        <span class="pull-right">
                            Ditemukan <?php echo mysqli_num_rows($result); ?> bus
                        </span>
                    </div>
                <?php endif; ?>

                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive" style="margin-top: 15px;">
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr class="bg-primary">
                                    <th width="20%">Perusahaan & Kontak</th>
                                    <th width="15%">Tipe & Jenis Bus</th>
                                    <th width="8%">Kapasitas</th>
                                    <th width="12%">Status</th>
                                    <th width="15%">Fasilitas</th>
                                    <th width="15%">Pesanan Aktif</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Reset pointer result set
                                mysqli_data_seek($result, 0);
                                while($bus = mysqli_fetch_array($result)): 
                                    
                                    // Query untuk mendapatkan pesanan aktif bus ini
                                    $pesanan_aktif_query = "SELECT id FROM pemesanan 
                                                          WHERE bus_id = {$bus['id']} 
                                                          AND status = 'Dikonfirmasi' 
                                                          AND tanggal_berangkat >= CURDATE() 
                                                          LIMIT 1";
                                    $pesanan_aktif_result = mysqli_query($connect, $pesanan_aktif_query);
                                    $pesanan_aktif = mysqli_fetch_array($pesanan_aktif_result);
                                    
                                ?>
                                    <tr>
                                        <td>
                                            <div class="bus-company-info">
                                                <strong class="text-primary"><?php echo $bus['nama_perusahaan'] ?></strong>
                                                <br>
                                                <?php if (!empty($bus['whatsapp_perusahaan_bus'])): ?>
                                                    <small class="text-success">
                                                        <i class="fa fa-whatsapp"></i> 
                                                        <?php echo $bus['whatsapp_perusahaan_bus'] ?>
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-muted">
                                                        <i class="fa fa-phone"></i> 
                                                        No WhatsApp
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="bus-type-info">
                                                <strong><?php echo $bus['tipe bus'] ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fa fa-tag"></i> 
                                                    <?php echo $bus['jenis'] ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <span class="badge bg-blue" style="font-size: 14px;">
                                                    <?php echo $bus['kapasitas'] ?> orang
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="bus-status-info">
                                                <span class="label label-<?php 
                                                    echo $bus['status'] == 'Tersedia' ? 'success' : 
                                                         ($bus['status'] == 'Dalam Perawatan' ? 'warning' : 
                                                         ($bus['status'] == 'Disewa' ? 'info' : 'danger'));
                                                ?>" style="font-size: 12px;">
                                                    <?php echo $bus['status'] ?>
                                                </span>
                                                <?php if($bus['pesanan_hari_ini'] > 0): ?>
                                                    <br>
                                                    <small class="text-success">
                                                        <i class="fa fa-calendar-check-o"></i> 
                                                        <?php echo $bus['pesanan_hari_ini'] ?> pesanan hari ini
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($bus['fasilitas'])): ?>
                                                <small class="text-muted">
                                                    <?php 
                                                    $fasilitas = substr($bus['fasilitas'], 0, 50);
                                                    echo $fasilitas;
                                                    if (strlen($bus['fasilitas']) > 50) echo '...';
                                                    ?>
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">Tidak ada fasilitas</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="booking-info">
                                                <?php if($bus['total_pesanan_aktif'] > 0): ?>
                                                    <span class="badge bg-orange" style="font-size: 12px;">
                                                        <i class="fa fa-list"></i> 
                                                        <?php echo $bus['total_pesanan_aktif'] ?> pesanan aktif
                                                    </span>
                                                    <br>
                                                    <?php if ($pesanan_aktif): ?>
                                                        <small class="text-info">
                                                            <i class="fa fa-external-link"></i> 
                                                            <a href="admin_pesanan_edit.php?id=<?php echo $pesanan_aktif['id']; ?>" 
                                                               style="color: #2196F3;">
                                                                Edit pesanan terbaru
                                                            </a>
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">
                                                            <i class="fa fa-info-circle"></i> 
                                                            Lihat detail di menu pesanan
                                                        </small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">
                                                        <i class="fa fa-check-circle"></i> 
                                                        Tidak ada pesanan
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group-vertical">
                                                <a href="admin_booking.php?bus_id=<?php echo $bus['id'] ?>" 
                                                   class="btn btn-xs btn-info btn-block" 
                                                   title="Lihat Pesanan Bus Ini">
                                                    <i class="fa fa-list"></i> Lihat Pesanan
                                                </a>
                                                <?php if ($pesanan_aktif): ?>
                                                    <a href="admin_pesanan_edit.php?id=<?php echo $pesanan_aktif['id']; ?>" 
                                                       class="btn btn-xs btn-warning btn-block" 
                                                       title="Edit Pesanan Aktif">
                                                        <i class="fa fa-edit"></i> Edit Pesanan
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-xs btn-default btn-block" disabled 
                                                            title="Tidak ada pesanan aktif">
                                                        <i class="fa fa-edit"></i> Edit Pesanan
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center" style="padding: 40px;">
                        <i class="fa fa-bus" style="font-size: 64px; color: #d2d6de; margin-bottom: 20px;"></i>
                        <h3 style="color: #999;">
                            <?php if (!empty($search)): ?>
                                Tidak ditemukan bus dengan kata kunci "<?php echo htmlspecialchars($search); ?>"
                            <?php else: ?>
                                Tidak ada data bus
                            <?php endif; ?>
                        </h3>
                        <p style="color: #999;">
                            <?php if (!empty($search)): ?>
                                Coba dengan kata kunci lain atau <a href="admin_bus_jadwal.php">tampilkan semua bus</a>
                            <?php else: ?>
                                Tidak ditemukan bus dalam sistem
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>  
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<style>
.bus-company-info strong {
    font-size: 14px;
}
.bus-type-info strong {
    font-size: 14px;
}
.bus-status-info .label {
    font-size: 12px;
    padding: 4px 8px;
}
.booking-info .badge {
    font-size: 12px;
    padding: 4px 8px;
}
.table-responsive {
    border: 1px solid #f4f4f4;
}
.table > thead > tr > th {
    border-bottom: 2px solid #3c8dbc;
    vertical-align: middle;
}
.table > tbody > tr > td {
    vertical-align: middle;
}
.btn-group-vertical .btn {
    margin-bottom: 2px;
    text-align: left;
    padding: 5px 8px;
}
/* Style untuk kalender */
.bg-gray-light {
    background-color: #f8f9fa !important;
}
</style>

<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

<?php include 'footer.php'; ?>