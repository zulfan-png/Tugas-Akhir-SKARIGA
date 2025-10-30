<?php
// Start session
session_start();

// Include koneksi dengan path yang benar
include 'koneksi.php';

// Debug: Cek apakah koneksi berhasil
if (!isset($connect)) {
    die("Error: Variabel koneksi tidak terdefinisi. Periksa file koneksi.php");
}

// Cek koneksi database
if (!$connect) {
    die("Error: Koneksi database gagal. " . mysqli_connect_error());
}

// Cek apakah user sudah login dan memiliki akses admin/operator
if (!isset($_SESSION['user_id']) || ($_SESSION['level'] != 'admin' && $_SESSION['level'] != 'operator')) {
    header("Location: login.php");
    exit();
}

// Handle export to Excel
if (isset($_GET['export_excel'])) {
    exportLaporanToExcel($connect);
    exit();
}

// Fungsi untuk export laporan ke Excel
function exportLaporanToExcel($connect) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="laporan_pendapatan_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');
    
    // Cek apakah tabel perusahaan_bus ada
    $query_perusahaan_check = "SHOW TABLES LIKE 'perusahaan_bus'";
    $result_perusahaan_check = mysqli_query($connect, $query_perusahaan_check);
    $tabel_perusahaan_ada = mysqli_num_rows($result_perusahaan_check) > 0;
    
    // Query untuk statistik
    $query_stats = "
        SELECT 
            COUNT(*) as total_pemesanan,
            COALESCE(SUM(total_harga), 0) as total_pendapatan,
            COUNT(DISTINCT bus_id) as total_bus_terpakai
        FROM pemesanan 
        WHERE 
            status IN ('Dikonfirmasi', 'Selesai')
            AND MONTH(tanggal_berangkat) = MONTH(CURDATE())
            AND YEAR(tanggal_berangkat) = YEAR(CURDATE())
    ";
    $result_stats = mysqli_query($connect, $query_stats);
    $stats = mysqli_fetch_assoc($result_stats);
    
    // Query untuk data pemesanan detail
    if ($tabel_perusahaan_ada) {
        $query_pemesanan = "
            SELECT 
                p.*,
                u.nama_lengkap,
                u.nomor_hp,
                u.username,
                b.`tipe bus` as tipe_bus,
                b.jenis,
                b.kapasitas,
                per.nama_perusahaan,
                per.whatsapp_perusahaan
            FROM pemesanan p
            JOIN datauser u ON p.user_id = u.id
            JOIN bus b ON p.bus_id = b.id
            LEFT JOIN perusahaan_bus per ON b.perusahaan_id = per.id
            WHERE 
                p.status IN ('Dikonfirmasi', 'Selesai')
                AND MONTH(p.tanggal_berangkat) = MONTH(CURDATE())
                AND YEAR(p.tanggal_berangkat) = YEAR(CURDATE())
            ORDER BY p.tanggal_berangkat DESC
        ";
    } else {
        $query_pemesanan = "
            SELECT 
                p.*,
                u.nama_lengkap,
                u.nomor_hp,
                u.username,
                b.`tipe bus` as tipe_bus,
                b.jenis,
                b.kapasitas,
                'Perusahaan Tidak Tersedia' as nama_perusahaan,
                '-' as whatsapp_perusahaan
            FROM pemesanan p
            JOIN datauser u ON p.user_id = u.id
            JOIN bus b ON p.bus_id = b.id
            WHERE 
                p.status IN ('Dikonfirmasi', 'Selesai')
                AND MONTH(p.tanggal_berangkat) = MONTH(CURDATE())
                AND YEAR(p.tanggal_berangkat) = YEAR(CURDATE())
            ORDER BY p.tanggal_berangkat DESC
        ";
    }
    $result_pemesanan = mysqli_query($connect, $query_pemesanan);
    
    // Query untuk statistik per paket
    $query_paket = "
        SELECT 
            jenis_paket,
            COUNT(*) as jumlah_pemesanan,
            COALESCE(SUM(total_harga), 0) as total_pendapatan
        FROM pemesanan 
        WHERE 
            status IN ('Dikonfirmasi', 'Selesai')
            AND MONTH(tanggal_berangkat) = MONTH(CURDATE())
            AND YEAR(tanggal_berangkat) = YEAR(CURDATE())
        GROUP BY jenis_paket
        ORDER BY total_pendapatan DESC
    ";
    $result_paket = mysqli_query($connect, $query_paket);
    
    echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<!--[if gte mso 9]>';
    echo '<xml>';
    echo '<x:ExcelWorkbook>';
    echo '<x:ExcelWorksheets>';
    echo '<x:ExcelWorksheet>';
    echo '<x:Name>Ringkasan</x:Name>';
    echo '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
    echo '</x:ExcelWorksheet>';
    echo '<x:ExcelWorksheet>';
    echo '<x:Name>Detail Pemesanan</x:Name>';
    echo '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
    echo '</x:ExcelWorksheet>';
    echo '<x:ExcelWorksheet>';
    echo '<x:Name>Statistik Paket</x:Name>';
    echo '<x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
    echo '</x:ExcelWorksheet>';
    echo '</x:ExcelWorksheets>';
    echo '</x:ExcelWorkbook>';
    echo '</xml>';
    echo '<![endif]-->';
    echo '<style>';
    echo 'td { mso-number-format:\\@; }';
    echo '.header { background-color: #3c8dbc; color: white; font-weight: bold; text-align: center; }';
    echo '.subheader { background-color: #f4f4f4; font-weight: bold; }';
    echo '.total { background-color: #d4edda; font-weight: bold; }';
    echo '.currency { mso-number-format:"#,##0"; }';
    echo '</style>';
    echo '</head>';
    echo '<body>';
    
    // ==================== SHEET 1: RINGKASAN ====================
    echo '<table border="1" style="width:100%">';
    echo '<tr><td colspan="4" class="header" style="font-size:18px; padding:10px;">LAPORAN PENDAPATAN BULANAN</td></tr>';
    echo '<tr><td colspan="4" style="text-align:center; padding:5px;">Periode: ' . date('F Y') . ' | Dicetak: ' . date('d/m/Y H:i:s') . '</td></tr>';
    echo '<tr><td colspan="4"></td></tr>';
    
    // Statistik Ringkasan
    echo '<tr class="subheader">';
    echo '<td colspan="4" style="text-align:center; padding:8px;">RINGKASAN STATISTIK</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td style="padding:8px;"><strong>Total Pendapatan</strong></td>';
    echo '<td style="padding:8px;" class="currency">Rp ' . number_format($stats['total_pendapatan'], 0, ',', '.') . '</td>';
    echo '<td style="padding:8px;"><strong>Total Pemesanan</strong></td>';
    echo '<td style="padding:8px;">' . $stats['total_pemesanan'] . ' pesanan</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td style="padding:8px;"><strong>Bus Terpakai</strong></td>';
    echo '<td style="padding:8px;">' . $stats['total_bus_terpakai'] . ' bus</td>';
    echo '<td style="padding:8px;"><strong>Rata-rata per Pesanan</strong></td>';
    echo '<td style="padding:8px;" class="currency">Rp ' . number_format($stats['total_pemesanan'] > 0 ? $stats['total_pendapatan'] / $stats['total_pemesanan'] : 0, 0, ',', '.') . '</td>';
    echo '</tr>';
    
    echo '<tr><td colspan="4" style="padding:10px;"></td></tr>';
    
    // Statistik per Paket
    echo '<tr class="subheader">';
    echo '<td colspan="4" style="text-align:center; padding:8px;">STATISTIK PER JENIS PAKET</td>';
    echo '</tr>';
    
    echo '<tr class="subheader">';
    echo '<td style="padding:8px;">Jenis Paket</td>';
    echo '<td style="padding:8px;">Jumlah Pesanan</td>';
    echo '<td style="padding:8px;">Total Pendapatan</td>';
    echo '<td style="padding:8px;">Rata-rata</td>';
    echo '</tr>';
    
    $total_all_pendapatan = 0;
    $total_all_pesanan = 0;
    
    if (mysqli_num_rows($result_paket) > 0) {
        while($paket = mysqli_fetch_assoc($result_paket)) {
            echo '<tr>';
            echo '<td style="padding:6px;">' . ($paket['jenis_paket'] ?: 'Tidak ada data') . '</td>';
            echo '<td style="padding:6px; text-align:center;">' . $paket['jumlah_pemesanan'] . '</td>';
            echo '<td style="padding:6px;" class="currency">Rp ' . number_format($paket['total_pendapatan'], 0, ',', '.') . '</td>';
            echo '<td style="padding:6px;" class="currency">Rp ' . number_format($paket['jumlah_pemesanan'] > 0 ? $paket['total_pendapatan'] / $paket['jumlah_pemesanan'] : 0, 0, ',', '.') . '</td>';
            echo '</tr>';
            
            $total_all_pendapatan += $paket['total_pendapatan'];
            $total_all_pesanan += $paket['jumlah_pemesanan'];
        }
    } else {
        echo '<tr><td colspan="4" style="text-align:center; padding:10px;">Tidak ada data paket</td></tr>';
    }
    
    echo '<tr class="total">';
    echo '<td style="padding:8px;"><strong>TOTAL</strong></td>';
    echo '<td style="padding:8px; text-align:center;"><strong>' . $total_all_pesanan . '</strong></td>';
    echo '<td style="padding:8px;" class="currency"><strong>Rp ' . number_format($total_all_pendapatan, 0, ',', '.') . '</strong></td>';
    echo '<td style="padding:8px;" class="currency"><strong>Rp ' . number_format($total_all_pesanan > 0 ? $total_all_pendapatan / $total_all_pesanan : 0, 0, ',', '.') . '</strong></td>';
    echo '</tr>';
    
    echo '</table>';
    
    echo '<br><br>';
    
    // ==================== SHEET 2: DETAIL PEMESANAN ====================
    echo '<table border="1" style="width:100%">';
    echo '<tr><td colspan="12" class="header" style="font-size:18px; padding:10px;">DETAIL PEMESANAN BULAN ' . strtoupper(date('F Y')) . '</td></tr>';
    echo '<tr><td colspan="12" style="text-align:center; padding:5px;">Dicetak: ' . date('d/m/Y H:i:s') . '</td></tr>';
    echo '<tr><td colspan="12"></td></tr>';
    
    // Header Detail
    echo '<tr class="subheader">';
    echo '<th style="padding:8px;">No</th>';
    echo '<th style="padding:8px;">Nama Pemesan</th>';
    echo '<th style="padding:8px;">Kontak</th>';
    echo '<th style="padding:8px;">Perusahaan</th>';
    echo '<th style="padding:8px;">Tipe Bus</th>';
    echo '<th style="padding:8px;">Jenis</th>';
    echo '<th style="padding:8px;">Paket</th>';
    echo '<th style="padding:8px;">Tanggal Berangkat</th>';
    echo '<th style="padding:8px;">Tanggal Kembali</th>';
    echo '<th style="padding:8px;">Lokasi Jemput</th>';
    echo '<th style="padding:8px;">Tujuan</th>';
    echo '<th style="padding:8px;">Total Harga</th>';
    echo '</tr>';
    
    $no = 1;
    $grand_total = 0;
    
    if (mysqli_num_rows($result_pemesanan) > 0) {
        while($pemesanan = mysqli_fetch_assoc($result_pemesanan)) {
            echo '<tr>';
            echo '<td style="padding:6px; text-align:center;">' . $no++ . '</td>';
            echo '<td style="padding:6px;">' . htmlspecialchars($pemesanan['nama_lengkap']) . '</td>';
            echo '<td style="padding:6px;">' . ($pemesanan['nomor_hp'] ?: $pemesanan['username']) . '</td>';
            echo '<td style="padding:6px;">' . htmlspecialchars($pemesanan['nama_perusahaan'] ?: 'Tidak ada data') . '</td>';
            echo '<td style="padding:6px;">' . htmlspecialchars($pemesanan['tipe_bus']) . '</td>';
            echo '<td style="padding:6px;">' . htmlspecialchars($pemesanan['jenis']) . '</td>';
            echo '<td style="padding:6px;">' . htmlspecialchars($pemesanan['jenis_paket']) . '</td>';
            echo '<td style="padding:6px;">' . date('d/m/Y', strtotime($pemesanan['tanggal_berangkat'])) . '</td>';
            echo '<td style="padding:6px;">' . date('d/m/Y', strtotime($pemesanan['tanggal_kembali'])) . '</td>';
            echo '<td style="padding:6px;">' . htmlspecialchars($pemesanan['lokasi_penjemputan']) . '</td>';
            echo '<td style="padding:6px;">' . htmlspecialchars($pemesanan['tujuan']) . '</td>';
            echo '<td style="padding:6px;" class="currency">Rp ' . number_format($pemesanan['total_harga'], 0, ',', '.') . '</td>';
            echo '</tr>';
            
            $grand_total += $pemesanan['total_harga'];
        }
    } else {
        echo '<tr><td colspan="12" style="text-align:center; padding:20px;">Tidak ada data pemesanan untuk bulan ini</td></tr>';
    }
    
    if ($no > 1) {
        echo '<tr class="total">';
        echo '<td colspan="11" style="padding:8px; text-align:right;"><strong>GRAND TOTAL:</strong></td>';
        echo '<td style="padding:8px;" class="currency"><strong>Rp ' . number_format($grand_total, 0, ',', '.') . '</strong></td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    echo '<br><br>';
    
    // ==================== SHEET 3: STATISTIK PAKET ====================
    echo '<table border="1" style="width:100%">';
    echo '<tr><td colspan="6" class="header" style="font-size:18px; padding:10px;">STATISTIK DETAIL PER JENIS PAKET</td></tr>';
    echo '<tr><td colspan="6" style="text-align:center; padding:5px;">Periode: ' . date('F Y') . ' | Dicetak: ' . date('d/m/Y H:i:s') . '</td></tr>';
    echo '<tr><td colspan="6"></td></tr>';
    
    // Header Statistik Paket
    echo '<tr class="subheader">';
    echo '<th style="padding:8px;">Jenis Paket</th>';
    echo '<th style="padding:8px;">Jumlah Pesanan</th>';
    echo '<th style="padding:8px;">Persentase</th>';
    echo '<th style="padding:8px;">Total Pendapatan</th>';
    echo '<th style="padding:8px;">Persentase Pendapatan</th>';
    echo '<th style="padding:8px;">Rata-rata per Pesanan</th>';
    echo '</tr>';
    
    mysqli_data_seek($result_paket, 0); // Reset pointer
    $total_pendapatan_paket = 0;
    $total_pesanan_paket = 0;
    
    if (mysqli_num_rows($result_paket) > 0) {
        while($paket = mysqli_fetch_assoc($result_paket)) {
            $persentase_pesanan = $stats['total_pemesanan'] > 0 ? ($paket['jumlah_pemesanan'] / $stats['total_pemesanan']) * 100 : 0;
            $persentase_pendapatan = $stats['total_pendapatan'] > 0 ? ($paket['total_pendapatan'] / $stats['total_pendapatan']) * 100 : 0;
            $rata_rata = $paket['jumlah_pemesanan'] > 0 ? $paket['total_pendapatan'] / $paket['jumlah_pemesanan'] : 0;
            
            echo '<tr>';
            echo '<td style="padding:6px;">' . ($paket['jenis_paket'] ?: 'Tidak ada data') . '</td>';
            echo '<td style="padding:6px; text-align:center;">' . $paket['jumlah_pemesanan'] . '</td>';
            echo '<td style="padding:6px; text-align:center;">' . number_format($persentase_pesanan, 1) . '%</td>';
            echo '<td style="padding:6px;" class="currency">Rp ' . number_format($paket['total_pendapatan'], 0, ',', '.') . '</td>';
            echo '<td style="padding:6px; text-align:center;">' . number_format($persentase_pendapatan, 1) . '%</td>';
            echo '<td style="padding:6px;" class="currency">Rp ' . number_format($rata_rata, 0, ',', '.') . '</td>';
            echo '</tr>';
            
            $total_pendapatan_paket += $paket['total_pendapatan'];
            $total_pesanan_paket += $paket['jumlah_pemesanan'];
        }
    } else {
        echo '<tr><td colspan="6" style="text-align:center; padding:20px;">Tidak ada data paket untuk bulan ini</td></tr>';
    }
    
    if ($total_pesanan_paket > 0) {
        echo '<tr class="total">';
        echo '<td style="padding:8px;"><strong>TOTAL</strong></td>';
        echo '<td style="padding:8px; text-align:center;"><strong>' . $total_pesanan_paket . '</strong></td>';
        echo '<td style="padding:8px; text-align:center;"><strong>100%</strong></td>';
        echo '<td style="padding:8px;" class="currency"><strong>Rp ' . number_format($total_pendapatan_paket, 0, ',', '.') . '</strong></td>';
        echo '<td style="padding:8px; text-align:center;"><strong>100%</strong></td>';
        echo '<td style="padding:8px;" class="currency"><strong>Rp ' . number_format($total_pendapatan_paket / $total_pesanan_paket, 0, ',', '.') . '</strong></td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    echo '</body></html>';
    exit();
}

// Cek apakah tabel perusahaan_bus ada
$query_perusahaan_check = "SHOW TABLES LIKE 'perusahaan_bus'";
$result_perusahaan_check = mysqli_query($connect, $query_perusahaan_check);
$tabel_perusahaan_ada = mysqli_num_rows($result_perusahaan_check) > 0;

// Query untuk mendapatkan data pemesanan bulan ini dengan semua field yang diperlukan
$current_year = date('Y');
$current_month = date('m');

// Query untuk mendapatkan semua data pemesanan dengan status Dikonfirmasi/Selesai
if ($tabel_perusahaan_ada) {
    $query_pemesanan = "
        SELECT 
            p.*,
            u.nama_lengkap,
            u.nomor_hp,
            b.`tipe bus` as tipe_bus,
            b.jenis,
            per.nama_perusahaan
        FROM pemesanan p
        JOIN datauser u ON p.user_id = u.id
        JOIN bus b ON p.bus_id = b.id
        LEFT JOIN perusahaan_bus per ON b.perusahaan_id = per.id
        WHERE 
            p.status IN ('Dikonfirmasi', 'Selesai')
        ORDER BY p.tanggal_berangkat DESC, p.created_at DESC
    ";
} else {
    $query_pemesanan = "
        SELECT 
            p.*,
            u.nama_lengkap,
            u.nomor_hp,
            b.`tipe bus` as tipe_bus,
            b.jenis,
            'Perusahaan Tidak Tersedia' as nama_perusahaan
        FROM pemesanan p
        JOIN datauser u ON p.user_id = u.id
        JOIN bus b ON p.bus_id = b.id
        WHERE 
            p.status IN ('Dikonfirmasi', 'Selesai')
        ORDER BY p.tanggal_berangkat DESC, p.created_at DESC
    ";
}
$result_pemesanan = mysqli_query($connect, $query_pemesanan);

if ($result_pemesanan === false) {
    die("Error dalam query pemesanan: " . mysqli_error($connect));
}

// Simpan data pemesanan ke dalam array
$data_pemesanan = [];
$total_pendapatan = 0;
$total_pemesanan = 0;

if (mysqli_num_rows($result_pemesanan) > 0) {
    while($pemesanan = mysqli_fetch_assoc($result_pemesanan)) {
        $data_pemesanan[] = $pemesanan;
        $total_pendapatan += $pemesanan['total_harga'];
        $total_pemesanan++;
    }
}

// Query untuk statistik paket
$query_paket = "
    SELECT 
        jenis_paket,
        COUNT(*) as jumlah_pemesanan,
        COALESCE(SUM(total_harga), 0) as total_pendapatan
    FROM pemesanan 
    WHERE 
        status IN ('Dikonfirmasi', 'Selesai')
        AND MONTH(tanggal_berangkat) = MONTH(CURDATE())
        AND YEAR(tanggal_berangkat) = YEAR(CURDATE())
    GROUP BY jenis_paket
    ORDER BY total_pendapatan DESC
";

$result_paket = mysqli_query($connect, $query_paket);

if ($result_paket === false) {
    die("Error dalam query paket: " . mysqli_error($connect));
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
                        <h3><?= count($data_pemesanan) ?></h3>
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

        / ==================== SHEET 2: DETAIL PEMESANAN ====================
    echo '<table border="1" style="width:100%">';
    echo '<tr><td colspan="5" class="header" style="font-size:18px; padding:10px;">DETAIL PEMESANAN BUS</td></tr>';
    echo '<tr><td colspan="5" style="text-align:center; padding:5px;">Periode: ' . date('F Y') . ' | Dicetak: ' . date('d/m/Y H:i:s') . '</td></tr>';
    echo '<tr><td colspan="5"></td></tr>';
    
    // Header Detail - Sesuai dengan tampilan web
    echo '<tr class="subheader">';
    echo '<th style="padding:8px; text-align:center;">No</th>';
    echo '<th style="padding:8px;">Nama Pemesan</th>';
    echo '<th style="padding:8px;">Bus & Perusahaan</th>';
    echo '<th style="padding:8px;">Jenis Paket</th>';
    echo '<th style="padding:8px;">Total Harga</th>';
    echo '</tr>';

        <!-- Action buttons -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-solid bg-gray">
                    <div class="box-body text-center">
                        <div class="btn-group">
                            <a href="?export_excel=1" class="btn btn-success btn-lg" onclick="showLoading()">
                                <i class="fa fa-file-excel-o"></i> Export to Excel
                            </a>
                            <button class="btn btn-info btn-lg" onclick="window.print()">
                                <i class="fa fa-print"></i> Print Laporan
                            </button>
                            <button class="btn btn-primary btn-lg" onclick="location.reload()">
                                <i class="fa fa-refresh"></i> Refresh Data
                            </button>
                        </div>
                        <div style="margin-top: 10px;">
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> 
                                Data akan diexport dalam format Excel dengan 3 sheet: Ringkasan, Detail Pemesanan, dan Statistik Paket
                            </small>
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