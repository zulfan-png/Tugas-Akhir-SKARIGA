<?php
include 'koneksi.php';
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Cek session timeout (opsional - 1 jam)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 3600)) {
    session_destroy();
    header("Location: index.html?timeout=1");
    exit();
}

// Perbarui waktu session
$_SESSION['login_time'] = time();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.html");
    exit();
}

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

// Handle pembatalan pesanan
if (isset($_POST['batal_pesanan'])) {
    $booking_id = $_POST['booking_id'];
    $alasan_batal = mysqli_real_escape_string($connect, $_POST['alasan_batal']);
    
    // Update status menjadi permintaan batal
    $query = "UPDATE pemesanan SET permintaan_batal = 'Ya', alasan_batal = '$alasan_batal' WHERE id = $booking_id AND user_id = {$_SESSION['user_id']}";
    
    if (mysqli_query($connect, $query)) {
        $success_message = "Permintaan pembatalan telah dikirim. Menunggu persetujuan admin.";
    } else {
        $error_message = "Terjadi kesalahan: " . mysqli_error($connect);
    }
}

// Handle permintaan edit
if (isset($_POST['edit_pesanan'])) {
    $booking_id = $_POST['booking_id'];
    $jenis_paket = mysqli_real_escape_string($connect, $_POST['jenis_paket']);
    $tanggal_berangkat = mysqli_real_escape_string($connect, $_POST['tanggal_berangkat']);
    $jam_berangkat = mysqli_real_escape_string($connect, $_POST['jam_berangkat']);
    $tanggal_kembali = mysqli_real_escape_string($connect, $_POST['tanggal_kembali']);
    $lokasi_penjemputan = mysqli_real_escape_string($connect, $_POST['lokasi_penjemputan']);
    $tujuan = mysqli_real_escape_string($connect, $_POST['tujuan']);
    $jumlah_penumpang = mysqli_real_escape_string($connect, $_POST['jumlah_penumpang']);
    $keterangan = mysqli_real_escape_string($connect, $_POST['keterangan']);
    
    // Simpan data lama sebelum edit
    $query_old_data = "SELECT * FROM pemesanan WHERE id = $booking_id AND user_id = {$_SESSION['user_id']}";
    $result_old = mysqli_query($connect, $query_old_data);
    
    if (mysqli_num_rows($result_old) > 0) {
        $old_data = mysqli_fetch_assoc($result_old);
        
        $data_sebelumnya = json_encode([
            'jenis_paket' => $old_data['jenis_paket'],
            'tanggal_berangkat' => $old_data['tanggal_berangkat'],
            'jam_berangkat' => $old_data['jam_berangkat'],
            'tanggal_kembali' => $old_data['tanggal_kembali'],
            'lokasi_penjemputan' => $old_data['lokasi_penjemputan'],
            'tujuan' => $old_data['tujuan'],
            'jumlah_penumpang' => $old_data['jumlah_penumpang'],
            'keterangan' => $old_data['keterangan'],
            'total_harga' => $old_data['total_harga']
        ]);
        
        // Update dengan data baru dan set permintaan edit
        $query = "UPDATE pemesanan SET 
                  jenis_paket = '$jenis_paket',
                  tanggal_berangkat = '$tanggal_berangkat',
                  jam_berangkat = '$jam_berangkat',
                  tanggal_kembali = '$tanggal_kembali',
                  lokasi_penjemputan = '$lokasi_penjemputan',
                  tujuan = '$tujuan',
                  jumlah_penumpang = $jumlah_penumpang,
                  keterangan = '$keterangan',
                  permintaan_edit = 'Ya',
                  data_edit_sebelumnya = '$data_sebelumnya'
                  WHERE id = $booking_id AND user_id = {$_SESSION['user_id']}";
        
        if (mysqli_query($connect, $query)) {
            $success_message = "Permintaan edit telah dikirim. Menunggu persetujuan admin.";
        } else {
            $error_message = "Terjadi kesalahan: " . mysqli_error($connect);
        }
    } else {
        $error_message = "Pesanan tidak ditemukan.";
    }
}

// Query data user
$user_id = $_SESSION['user_id'];
$query_user = "SELECT * FROM datauser WHERE id = $user_id";
$result_user = mysqli_query($connect, $query_user);
$user = mysqli_fetch_array($result_user);

// Query riwayat pemesanan user
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
        
        .status-menunggu-konfirmasi {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-dikonfirmasi {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-selesai {
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }

        .close {
            font-size: 24px;
            cursor: pointer;
            color: #64748b;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #374151;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #1e40af;
            color: white;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .status-permintaan {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 12px;
            background: #fbbf24;
            color: #92400e;
            margin-left: 10px;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
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
            
            .action-buttons {
                flex-direction: column;
                width: 100%;
            }
            
            .action-buttons .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'user_navbar.php'; ?>

    <div class="container">
        <h1 class="page-title">Riwayat Pesanan Saya</h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (mysqli_num_rows($result_booking) > 0): ?>
            <?php while($booking = mysqli_fetch_array($result_booking)): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <div>
                            <div class="bus-info">
                                <?php echo $booking['nama_perusahaan'] ?> - <?php echo $booking['tipe bus'] ?>
                                <?php if ($booking['permintaan_edit'] == 'Ya'): ?>
                                    <span class="status-permintaan">Menunggu Persetujuan Edit</span>
                                <?php endif; ?>
                                <?php if ($booking['permintaan_batal'] == 'Ya'): ?>
                                    <span class="status-permintaan">Menunggu Persetujuan Batal</span>
                                <?php endif; ?>
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
                    
                    <?php if (!empty($booking['alasan_batal'])): ?>
                        <div class="detail-item" style="margin-bottom: 15px;">
                            <span class="detail-label">Alasan Pembatalan</span>
                            <span class="detail-value"><?php echo $booking['alasan_batal'] ?></span>
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
                        
                        <div class="action-buttons">
                            <!-- Tombol Edit -->
                            <?php if ($booking['status'] == 'Menunggu Konfirmasi' && $booking['permintaan_edit'] == 'Tidak' && $booking['permintaan_batal'] == 'Tidak'): ?>
                                <button class="btn btn-primary" onclick="openEditModal(<?php echo $booking['id'] ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            <?php endif; ?>
                            
                            <!-- Tombol Batal -->
                            <?php if ($booking['status'] == 'Menunggu Konfirmasi' && $booking['permintaan_batal'] == 'Tidak' && $booking['permintaan_edit'] == 'Tidak'): ?>
                                <button class="btn btn-danger" onclick="openCancelModal(<?php echo $booking['id'] ?>)">
                                    <i class="fas fa-times"></i> Batal
                                </button>
                            <?php endif; ?>
                            
                            <!-- WhatsApp Button -->
                            <?php 
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

    <!-- Modal Edit -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Pesanan</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST" id="editForm">
                <input type="hidden" name="booking_id" id="edit_booking_id">
                
                <div class="form-group">
                    <label>Jenis Paket</label>
                    <select name="jenis_paket" class="form-control" required id="edit_jenis_paket">
                        <option value="Paket 6 Jam">Paket 6 Jam</option>
                        <option value="Paket 12 Jam">Paket 12 Jam</option>
                        <option value="Paket 24 Jam">Paket 24 Jam</option>
                        <option value="Luar Kota">Luar Kota</option>
                        <option value="Paket 6 Jam (Weekend)">Paket 6 Jam (Weekend)</option>
                        <option value="Paket 12 Jam (Weekend)">Paket 12 Jam (Weekend)</option>
                        <option value="Paket 24 Jam (Weekend)">Paket 24 Jam (Weekend)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Tanggal Berangkat</label>
                    <input type="date" name="tanggal_berangkat" class="form-control" required id="edit_tanggal_berangkat">
                </div>
                
                <div class="form-group">
                    <label>Jam Berangkat</label>
                    <input type="time" name="jam_berangkat" class="form-control" required id="edit_jam_berangkat">
                </div>
                
                <div class="form-group">
                    <label>Tanggal Kembali</label>
                    <input type="date" name="tanggal_kembali" class="form-control" required id="edit_tanggal_kembali">
                </div>
                
                <div class="form-group">
                    <label>Lokasi Penjemputan</label>
                    <textarea name="lokasi_penjemputan" class="form-control" rows="2" required id="edit_lokasi_penjemputan"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Tujuan</label>
                    <textarea name="tujuan" class="form-control" rows="2" required id="edit_tujuan"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Jumlah Penumpang</label>
                    <input type="number" name="jumlah_penumpang" class="form-control" min="1" required id="edit_jumlah_penumpang">
                </div>
                
                <div class="form-group">
                    <label>Keterangan (Opsional)</label>
                    <textarea name="keterangan" class="form-control" rows="3" id="edit_keterangan"></textarea>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Permintaan edit akan dikirim ke admin untuk persetujuan. 
                    Perubahan akan berlaku setelah disetujui oleh admin.
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                    <button type="submit" name="edit_pesanan" class="btn btn-primary">Kirim Permintaan Edit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Batal -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Batalkan Pesanan</h3>
                <span class="close" onclick="closeCancelModal()">&times;</span>
            </div>
            <form method="POST" id="cancelForm">
                <input type="hidden" name="booking_id" id="cancel_booking_id">
                
                <div class="form-group">
                    <label>Alasan Pembatalan</label>
                    <textarea name="alasan_batal" class="form-control" rows="4" placeholder="Mohon jelaskan alasan pembatalan pesanan..." required></textarea>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Permintaan pembatalan akan dikirim ke admin untuk persetujuan. 
                    Pesanan akan dibatalkan setelah disetujui oleh admin.
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">Batal</button>
                    <button type="submit" name="batal_pesanan" class="btn btn-danger">Kirim Permintaan Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(bookingId) {
            // Ambil data pesanan via AJAX
            fetch('get_booking_data.php?id=' + bookingId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit_booking_id').value = bookingId;
                        document.getElementById('edit_jenis_paket').value = data.booking.jenis_paket;
                        document.getElementById('edit_tanggal_berangkat').value = data.booking.tanggal_berangkat;
                        document.getElementById('edit_jam_berangkat').value = data.booking.jam_berangkat;
                        document.getElementById('edit_tanggal_kembali').value = data.booking.tanggal_kembali;
                        document.getElementById('edit_lokasi_penjemputan').value = data.booking.lokasi_penjemputan;
                        document.getElementById('edit_tujuan').value = data.booking.tujuan;
                        document.getElementById('edit_jumlah_penumpang').value = data.booking.jumlah_penumpang;
                        document.getElementById('edit_keterangan').value = data.booking.keterangan || '';
                        
                        document.getElementById('editModal').style.display = 'block';
                    } else {
                        alert('Gagal memuat data pesanan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memuat data');
                });
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function openCancelModal(bookingId) {
            document.getElementById('cancel_booking_id').value = bookingId;
            document.getElementById('cancelModal').style.display = 'block';
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }

        // Close modal ketika klik di luar
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        function confirmLogout() {
            return confirm('Apakah Anda yakin ingin logout?');
        }

        // Validasi form edit
        document.getElementById('editForm').addEventListener('submit', function(e) {
            const tanggalBerangkat = new Date(document.getElementById('edit_tanggal_berangkat').value);
            const tanggalKembali = new Date(document.getElementById('edit_tanggal_kembali').value);
            const today = new Date();
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
        });
    </script>
</body>
</html>