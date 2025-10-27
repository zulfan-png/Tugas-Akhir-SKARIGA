<?php
include 'koneksi.php';
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    echo "<script>window.location.href = 'index.html'</script>";
    exit();
}

// Query data user
$user_id = $_SESSION['user_id'];
$query_user = "SELECT * FROM datauser WHERE id = $user_id";
$result_user = mysqli_query($connect, $query_user);
$user = mysqli_fetch_array($result_user);

// Query riwayat pemesanan user - TAMBAH jam_berangkat
$query_booking = "SELECT p.*, b.`tipe bus`, b.jenis, per.nama_perusahaan 
                  FROM pemesanan p 
                  JOIN bus b ON p.bus_id = b.id 
                  JOIN perusahaan_bus per ON b.perusahaan_id = per.id 
                  WHERE p.user_id = $user_id 
                  ORDER BY p.created_at DESC";
$result_booking = mysqli_query($connect, $query_booking);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan | BISATA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .booking-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .bus-info {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-menunggu {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-dikonfirmasi {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-dibatalkan {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
        }
        
        .detail-value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 500;
        }
        
        .price {
            font-size: 18px;
            font-weight: 700;
            color: #1e40af;
        }
        
        /* WhatsApp Button di Riwayat */
        .btn-whatsapp {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #25D366;
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-whatsapp:hover {
            background: #128C7E;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #cbd5e1;
        }

        .booking-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .booking-date {
            font-size: 14px;
            color: #64748b;
        }

        @media (max-width: 768px) {
            .booking-footer {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .btn-whatsapp {
                align-self: flex-end;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'user_navbar.php'; ?>

    <div class="container">
        <h1 class="page-title">Riwayat Pesanan Saya</h1>
        
        <?php if (mysqli_num_rows($result_booking) > 0): ?>
            <?php while($booking = mysqli_fetch_array($result_booking)): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div>
                            <div class="bus-info">
                                <?php echo $booking['nama_perusahaan'] ?> - <?php echo $booking['tipe bus'] ?>
                            </div>
                        </div>
                        <div class="status status-<?php echo strtolower(str_replace(' ', '-', $booking['status'])) ?>">
                            <?php echo $booking['status'] ?>
                        </div>
                    </div>
                    
                    <div class="booking-details">
                        <div class="detail-item">
                            <span class="detail-label">Jenis Paket</span>
                            <span class="detail-value"><?php echo $booking['jenis_paket'] ?></span>
                        </div>
                            <div class="detail-item">
                                <span class="detail-label">Tanggal & Jam Berangkat</span>
                                <span class="detail-value">
                                    <?php echo date('d M Y', strtotime($booking['tanggal_berangkat'])) ?> 
                                    pukul <?php echo date('H:i', strtotime($booking['jam_berangkat'])) ?>
                                </span>
                            </div>
                        <?php if ($booking['tanggal_kembali'] != $booking['tanggal_berangkat']): ?>
                        <div class="detail-item">
                            <span class="detail-label">Tanggal Kembali</span>
                            <span class="detail-value"><?php echo date('d M Y', strtotime($booking['tanggal_kembali'])) ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-item">
                            <span class="detail-label">Jumlah Penumpang</span>
                            <span class="detail-value"><?php echo $booking['jumlah_penumpang'] ?> orang</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Lokasi Penjemputan</span>
                            <span class="detail-value"><?php echo $booking['lokasi_penjemputan'] ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Tujuan</span>
                            <span class="detail-value"><?php echo $booking['tujuan'] ?></span>
                        </div>
                    </div>
                    
                    <?php if (!empty($booking['keterangan'])): ?>
                        <div class="detail-item" style="margin-bottom: 15px;">
                            <span class="detail-label">Keterangan</span>
                            <span class="detail-value"><?php echo $booking['keterangan'] ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="booking-footer">
                        <div>
                            <div class="booking-date">
                                Pesan pada: <?php echo date('d M Y H:i', strtotime($booking['created_at'])) ?>
                            </div>
                            <div class="price">
                                Rp <?php echo number_format($booking['total_harga'], 0, ',', '.') ?>
                            </div>
                        </div>
                        
                        <!-- TOMBOL WHATSAPP -->
                        <?php 
                        // Query untuk mendapatkan WhatsApp dari bus yang dipesan
                        $whatsapp_query = "SELECT whatsapp_perusahaan FROM bus WHERE id = " . $booking['bus_id'];
                        $whatsapp_result = mysqli_query($connect, $whatsapp_query);
                        $whatsapp_data = mysqli_fetch_array($whatsapp_result);
                        ?>
                        
                        <?php if (!empty($whatsapp_data['whatsapp_perusahaan'])): ?>
                            <a href="https://wa.me/<?php echo $whatsapp_data['whatsapp_perusahaan'] ?>?text=Halo,%20saya%20ingin%20konfirmasi%20pesanan%20bus%20dengan%20detail:%0A%0A*Pesanan:* <?php echo urlencode($booking['nama_perusahaan'] . ' - ' . $booking['tipe bus']) ?>%0A*Tanggal:* <?php echo urlencode(date('d M Y', strtotime($booking['tanggal_berangkat']))) ?>%0A*Paket:* <?php echo urlencode($booking['jenis_paket']) ?>%0A*Status:* <?php echo urlencode($booking['status']) ?>%0A%0ASaya%20ingin%20konfirmasi%20lebih%20lanjut." 
                               target="_blank" 
                               class="btn-whatsapp">
                                <i class="fab fa-whatsapp"></i>
                                WhatsApp
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list" style="font-size: 64px; margin-bottom: 20px; color: #cbd5e1;"></i>
                <h3>Belum ada riwayat pesanan</h3>
                <p>Silakan lakukan pemesanan bus terlebih dahulu</p>
                <a href="user_home.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #1e40af; color: white; text-decoration: none; border-radius: 5px;">
                    <i class="fas fa-bus"></i> Pesan Bus Sekarang
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function confirmLogout() {
            return confirm('Apakah Anda yakin ingin logout?');
        }
    </script>
</body>
</html>