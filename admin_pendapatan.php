<?php
include 'koneksi.php';
session_start();

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['level'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Jika parameter export_excel ada, generate file Excel
if (isset($_GET['export_excel'])) {
    // Query untuk mengambil data pesanan dengan informasi lengkap
    $query = "SELECT p.*, u.nama_lengkap, u.nomor_hp, b.`tipe bus`, b.jenis, 
              per.nama_perusahaan,
              DATEDIFF(p.tanggal_kembali, p.tanggal_berangkat) as lama_pemesanan_hari
              FROM pemesanan p 
              JOIN datauser u ON p.user_id = u.id 
              JOIN bus b ON p.bus_id = b.id 
              JOIN perusahaan_bus per ON b.perusahaan_id = per.id 
              ORDER BY p.created_at DESC";
    $result = mysqli_query($connect, $query);

    // Hitung total harga semua pesanan
    $total_query = "SELECT SUM(total_harga) as total_semua FROM pemesanan WHERE status != 'Dibatalkan'";
    $total_result = mysqli_query($connect, $total_query);
    $total_data = mysqli_fetch_assoc($total_result);
    $total_semua = $total_data['total_semua'] ?? 0;

    // Header untuk file Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"laporan_pesanan_bus_" . date('Y-m-d') . ".xls\"");
    header("Pragma: no-cache");
    header("Expires: 0");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pesanan Bus</title>
    <style>
        .table {
            border-collapse: collapse;
            width: 100%;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .total-row {
            background-color: #e8f4ff;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">LAPORAN PESANAN BUS</h2>
    <p style="text-align: center;">Tanggal Cetak: <?php echo date('d F Y H:i:s'); ?></p>
    
    <table class="table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Nama Pemesan</th>
                <th width="12%">Nomor HP</th>
                <th width="15%">Nama Perusahaan Bus</th>
                <th width="12%">Tipe Bus</th>
                <th width="10%">Jenis Bus</th>
                <th width="15%">Paket Pemesanan</th>
                <th width="8%">Lama (Hari)</th>
                <th width="8%">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $total_penumpang = 0;
            while($pesanan = mysqli_fetch_array($result)): 
                $total_penumpang += $pesanan['jumlah_penumpang'];
                // Hitung lama pemesanan dalam hari
                $lama_hari = $pesanan['lama_pemesanan_hari'];
                if ($lama_hari == 0) {
                    $lama_hari = 1; // Minimal 1 hari untuk pemesanan hari yang sama
                }
            ?>
                <tr>
                    <td class="text-center"><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($pesanan['nama_lengkap']); ?></td>
                    <td><?php echo htmlspecialchars($pesanan['nomor_hp']); ?></td>
                    <td><?php echo htmlspecialchars($pesanan['nama_perusahaan']); ?></td>
                    <td><?php echo htmlspecialchars($pesanan['tipe bus']); ?></td>
                    <td><?php echo htmlspecialchars($pesanan['jenis']); ?></td>
                    <td><?php echo htmlspecialchars($pesanan['jenis_paket']); ?></td>
                    <td class="text-center"><?php echo $lama_hari; ?> hari</td>
                    <td class="text-right">Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></td>
                </tr>
            <?php endwhile; ?>
            
            <!-- Baris Total -->
            <tr class="total-row">
                <td colspan="7" class="text-right"><strong>TOTAL KESELURUHAN:</strong></td>
                <td class="text-center"><strong><?php echo ($no-1); ?> pesanan</strong></td>
                <td class="text-right"><strong>Rp <?php echo number_format($total_semua, 0, ',', '.'); ?></strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
<?php
    // Tutup koneksi database dan exit
    mysqli_close($connect);
    exit();
}

// Jika bukan export, tampilkan halaman normal
// Query untuk statistik
$query_pendapatan = "SELECT SUM(total_harga) as total_pendapatan FROM pemesanan WHERE status != 'Dibatalkan' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$result_pendapatan = mysqli_query($connect, $query_pendapatan);
$data_pendapatan = mysqli_fetch_assoc($result_pendapatan);
$total_pendapatan = $data_pendapatan['total_pendapatan'] ?? 0;

$query_pemesanan = "SELECT COUNT(*) as total_pemesanan FROM pemesanan WHERE status = 'Dikonfirmasi' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$result_pemesanan = mysqli_query($connect, $query_pemesanan);
$data_pemesanan = mysqli_fetch_assoc($result_pemesanan);
$total_pemesanan = $data_pemesanan['total_pemesanan'] ?? 0;

$query_bus = "SELECT COUNT(DISTINCT bus_id) as total_bus FROM pemesanan WHERE status != 'Dibatalkan' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$result_bus = mysqli_query($connect, $query_bus);
$data_bus = mysqli_fetch_assoc($result_bus);
$total_bus = $data_bus['total_bus'] ?? 0;

$query_paket = "SELECT DISTINCT jenis_paket FROM pemesanan WHERE status != 'Dibatalkan'";
$result_paket = mysqli_query($connect, $query_paket);

// Query untuk data pemesanan
$query_data = "SELECT p.*, u.nama_lengkap, b.`tipe bus`, b.jenis, per.nama_perusahaan 
               FROM pemesanan p 
               JOIN datauser u ON p.user_id = u.id 
               JOIN bus b ON p.bus_id = b.id 
               JOIN perusahaan_bus per ON b.perusahaan_id = per.id 
               ORDER BY p.created_at DESC 
               LIMIT 10";
$result_data = mysqli_query($connect, $query_data);
$data_pemesanan = [];
while($row = mysqli_fetch_assoc($result_data)) {
    $data_pemesanan[] = $row;
}
?>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- CSS untuk memperbaiki posisi icon -->
<style>
.small-box {
    position: relative;
    display: block;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
}
.small-box > .inner {
    padding: 10px;
    position: relative;
    z-index: 10;
}
.small-box h3 {
    font-size: 38px;
    font-weight: bold;
    margin: 0 0 10px 0;
    white-space: nowrap;
    padding: 0;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
}
.small-box p {
    font-size: 15px;
    font-weight: bold;
    text-shadow: 1px 1px 1px rgba(0,0,0,0.2);
}
.small-box .icon {
    position: absolute;
    bottom: 10px;
    right: 10px;
    z-index: 0;
    font-size: 70px;
    color: rgba(255,255,255,0.3);
    transition: all .3s linear;
}
.small-box:hover .icon {
    font-size: 75px;
    color: rgba(255,255,255,0.4);
}
.small-box > .small-box-footer {
    position: relative;
    text-align: center;
    padding: 3px 0;
    color: #fff;
    color: rgba(255,255,255,0.8);
    display: block;
    z-index: 10;
    background: rgba(0,0,0,0.1);
    text-decoration: none;
}
.small-box > .small-box-footer:hover {
    color: #fff;
    background: rgba(0,0,0,0.15);
}
/* Warna khusus untuk setiap box */
.bg-aqua .icon { color: rgba(255,255,255,0.3); }
.bg-green .icon { color: rgba(255,255,255,0.3); }
.bg-yellow .icon { color: rgba(255,255,255,0.3); }
.bg-red .icon { color: rgba(255,255,255,0.3); }

/* Style untuk loading */
.loading {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
}
.loading-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 30px;
    border-radius: 5px;
    text-align: center;
}
</style>

<!-- Loading overlay -->
<div class="loading" id="loading">
    <div class="loading-content">
        <i class="fa fa-spinner fa-spin fa-3x fa-fw text-primary"></i>
        <h3>Membuat Laporan Excel...</h3>
        <p>Harap tunggu, file sedang dipersiapkan</p>
    </div>
</div>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="row">
            <div class="col-md-8">
                <h1>
                    <i class="fa fa-line-chart"></i> Laporan Pendapatan
                    <small>Statistik pendapatan bulanan</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Laporan Pendapatan</li>
                </ol>
            </div>
            <div class="col-md-4 text-right">
                <a href="?export_excel=1" class="btn btn-success" style="margin-top: 15px;" onclick="showLoading()">
                    <i class="fa fa-file-excel-o"></i> Export to Excel
                </a>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <!-- Small boxes (Stat box) -->
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h3>
                        <p>Total Pendapatan</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-money"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Bulan: <?= date('F Y') ?> <i class="fa fa-calendar"></i>
                    </a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?= $total_pemesanan ?></h3>
                        <p>Total Pemesanan</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-calendar-check-o"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Pemesanan Dikonfirmasi <i class="fa fa-check"></i>
                    </a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?= $total_bus ?></h3>
                        <p>Bus Terpakai</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bus"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Bus yang dipesan <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3><?= mysqli_num_rows($result_paket) ?></h3>
                        <p>Jenis Paket</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-cubes"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        Variasi paket <i class="fa fa-pie-chart"></i>
                    </a>
                </div>
            </div>
            <!-- ./col -->
        </div>
        <!-- /.row -->

        <!-- Action buttons -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-solid bg-gray">
                    <div class="box-body text-center">
                        <div class="btn-group">
                            <a href="?export_excel=1" class="btn btn-success btn-lg" onclick="showLoading()">
                                <i class="fa fa-file-excel-o"></i> Export to Excel
                            </a>
                            <button class="btn btn-primary btn-lg" onclick="location.reload()">
                                <i class="fa fa-refresh"></i> Refresh Data
                            </button>
                        </div>
                        <div style="margin-top: 10px;">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> 
                                Data akan diexport dalam format Excel dengan kolom: No, Nama Pemesan, Nomor HP, Perusahaan Bus, Tipe Bus, Jenis Bus, Paket Pemesanan, Lama (Hari), dan Total Harga
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Data Pemesanan Terbaru -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">10 Pemesanan Terbaru</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Pemesan</th>
                                        <th>Bus & Perusahaan</th>
                                        <th>Paket</th>
                                        <th>Lama (Hari)</th>
                                        <th>Total Harga</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($data_pemesanan as $index => $pesanan): 
                                        $lama_hari = (strtotime($pesanan['tanggal_kembali']) - strtotime($pesanan['tanggal_berangkat'])) / (60 * 60 * 24);
                                        if ($lama_hari == 0) $lama_hari = 1;
                                    ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($pesanan['nama_lengkap']) ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($pesanan['tipe bus']) ?></strong> - 
                                                <?= htmlspecialchars($pesanan['jenis']) ?><br>
                                                <small><?= htmlspecialchars($pesanan['nama_perusahaan']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($pesanan['jenis_paket']) ?></td>
                                            <td class="text-center"><?= $lama_hari ?> hari</td>
                                            <td class="text-right">Rp <?= number_format($pesanan['total_harga'], 0, ',', '.') ?></td>
                                            <td>
                                                <span class="label label-<?= 
                                                    $pesanan['status'] == 'Dikonfirmasi' ? 'success' : 
                                                    ($pesanan['status'] == 'Menunggu Konfirmasi' ? 'warning' : 
                                                    ($pesanan['status'] == 'Selesai' ? 'info' : 'danger')) 
                                                ?>">
                                                    <?= $pesanan['status'] ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include 'footer.php'; ?>

<script>
function showLoading() {
    document.getElementById('loading').style.display = 'block';
    // Otomatis sembunyikan loading setelah 5 detik (fallback)
    setTimeout(function() {
        document.getElementById('loading').style.display = 'none';
    }, 5000);
}

// Sembunyikan loading ketika halaman selesai dimuat
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('loading').style.display = 'none';
    
    // Tambahkan event listener untuk semua link export
    const exportLinks = document.querySelectorAll('a[href*="export_excel"]');
    exportLinks.forEach(link => {
        link.addEventListener('click', showLoading);
    });
});

// Sembunyikan loading ketika pengguna kembali ke halaman (misal setelah download)
window.addEventListener('pageshow', function() {
    document.getElementById('loading').style.display = 'none';
});
</script>

<?php
// Tutup koneksi
mysqli_close($connect);
?>