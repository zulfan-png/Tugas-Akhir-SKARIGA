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

// Handle cancel booking - TAMBAHAN: Fitur Batal untuk User
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    // Update status menjadi Dibatalkan
    $query_cancel = "UPDATE pemesanan SET status = 'Dibatalkan' WHERE id = $booking_id AND user_id = $user_id";
    
    if (mysqli_query($connect, $query_cancel)) {
        $success_message = "Pesanan berhasil dibatalkan";
    } else {
        $error_message = "Terjadi kesalahan: " . mysqli_error($connect);
    }
}

// Ambil ID bus dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Bus tidak ditemukan");
}
$bus_id = $_GET['id'];

// Query data bus
$query_bus = "SELECT b.*, p.nama_perusahaan 
              FROM bus b 
              LEFT JOIN perusahaan_bus p ON b.perusahaan_id = p.id 
              WHERE b.id = $bus_id";
$result_bus = mysqli_query($connect, $query_bus);
$bus = mysqli_fetch_array($result_bus);

if (!$bus) {
    die("Bus tidak ditemukan");
}

// Query harga bus
$query_harga = "SELECT * FROM harga_bus WHERE bus_id = $bus_id ORDER BY 
                CASE jenis_harga 
                    WHEN 'Paket 6 Jam' THEN 1
                    WHEN 'Paket 12 Jam' THEN 2  
                    WHEN 'Paket 24 Jam' THEN 3
                    WHEN 'Paket 6 Jam (Weekend)' THEN 4
                    WHEN 'Paket 12 Jam (Weekend)' THEN 5
                    WHEN 'Paket 24 Jam (Weekend)' THEN 6
                    WHEN 'Luar Kota' THEN 7
                    ELSE 8
                END";
$result_harga = mysqli_query($connect, $query_harga);

// Query data user
$user_id = $_SESSION['user_id'];
$query_user = "SELECT * FROM datauser WHERE id = $user_id";
$result_user = mysqli_query($connect, $query_user);
$user = mysqli_fetch_array($result_user);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis_paket = $_POST['jenis_paket'];
    $tanggal_berangkat = $_POST['tanggal_berangkat'];
    $jam_berangkat = $_POST['jam_berangkat']; // TAMBAHAN: jam berangkat
    
    // Untuk paket 24 jam dan luar kota, gunakan tanggal kembali
    // Untuk paket lainnya, tanggal kembali sama dengan tanggal berangkat
    if (in_array($jenis_paket, ['Paket 24 Jam', 'Paket 24 Jam (Weekend)', 'Luar Kota'])) {
        $tanggal_kembali = $_POST['tanggal_kembali'];
    } else {
        $tanggal_kembali = $tanggal_berangkat;
    }
    
    $lokasi_penjemputan = $_POST['lokasi_penjemputan'];
    $tujuan = $_POST['tujuan'];
    $jumlah_penumpang = $_POST['jumlah_penumpang'];
    $keterangan = $_POST['keterangan'];
    
    // Hitung total harga
    $query_harga_paket = "SELECT harga FROM harga_bus WHERE bus_id = $bus_id AND jenis_harga = '$jenis_paket'";
    $result_harga_paket = mysqli_query($connect, $query_harga_paket);
    $harga_data = mysqli_fetch_array($result_harga_paket);
    $harga_paket = $harga_data['harga'];
    
    // Hitung selisih hari untuk paket 24 Jam dan Luar Kota
    $total_harga = $harga_paket;
    if (in_array($jenis_paket, ['Paket 24 Jam', 'Paket 24 Jam (Weekend)', 'Luar Kota'])) {
        $tanggal1 = new DateTime($tanggal_berangkat);
        $tanggal2 = new DateTime($tanggal_kembali);
        $selisih_hari = $tanggal2->diff($tanggal1)->days;
        
        // Jika selisih hari lebih dari 0, tambahkan biaya untuk hari tambahan
        if ($selisih_hari > 0) {
            // Untuk paket 24 Jam, tambahkan biaya per hari tambahan
            if (in_array($jenis_paket, ['Paket 24 Jam', 'Paket 24 Jam (Weekend)'])) {
                $harga_per_hari_tambahan = $harga_paket; // Atau bisa disesuaikan dengan harga khusus
                $total_harga = $harga_paket + ($harga_per_hari_tambahan * $selisih_hari);
            }
            // Untuk Luar Kota, tambahkan biaya per hari tambahan (bisa berbeda rate)
            elseif ($jenis_paket == 'Luar Kota') {
                $harga_per_hari_tambahan = $harga_paket * 0.8; // 80% dari harga paket per hari tambahan
                $total_harga = $harga_paket + ($harga_per_hari_tambahan * $selisih_hari);
            }
        }
    }
    
    // Insert pemesanan - TAMBAH jam_berangkat
    $query_insert = "INSERT INTO pemesanan (
        user_id, bus_id, jenis_paket, tanggal_berangkat, jam_berangkat, tanggal_kembali, 
        lokasi_penjemputan, tujuan, jumlah_penumpang, keterangan, total_harga
    ) VALUES (
        $user_id, $bus_id, '$jenis_paket', '$tanggal_berangkat', '$jam_berangkat', '$tanggal_kembali',
        '$lokasi_penjemputan', '$tujuan', $jumlah_penumpang, '$keterangan', $total_harga
    )";
    
    if (mysqli_query($connect, $query_insert)) {
        // Redirect ke riwayat pesanan
        header("Location: user_booking_history.php");
        exit();
    } else {
        $pesan_error = "Terjadi kesalahan: " . mysqli_error($connect);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pemesanan | BISATA</title>
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

        /* Container */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Breadcrumb */
        .breadcrumb {
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: #64748b;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: #1e40af;
        }

        /* Layout */
        .booking-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        /* Form Styling */
        .form-section {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            background: #f9fafb;
        }

        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        /* Summary Card */
        .summary-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .bus-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .bus-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
        }

        .bus-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-label {
            color: #64748b;
            font-weight: 500;
        }

        .info-value {
            color: #1e293b;
            font-weight: 600;
        }

        .price-display {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-top: 20px;
        }

        .price-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .price-amount {
            font-size: 28px;
            font-weight: 700;
        }

        .price-details {
            margin-top: 10px;
            font-size: 12px;
            opacity: 0.9;
        }

        /* Button */
        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            margin-top: 20px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }

        .submit-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }

        /* Alert Messages */
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Hidden element */
        .hidden {
            display: none !important;
        }

        /* Action Buttons - TAMBAHAN: Style untuk tombol batal */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }

        .btn-cancel {
            background: #ef4444;
            color: white;
        }

        .btn-cancel:hover {
            background: #dc2626;
            transform: translateY(-1px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .booking-layout {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .form-section, .summary-card {
                padding: 25px;
            }
            
            .summary-card {
                position: static;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include 'user_navbar.php'; ?>

    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="user_detail.php?id=<?php echo $bus_id; ?>"><i class="fas fa-arrow-left"></i> Kembali ke Detail Bus</a>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($pesan_error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $pesan_error; ?>
            </div>
        <?php endif; ?>

        <div class="booking-layout">
            <!-- Form Pemesanan -->
            <div class="form-section">
                <h2 class="section-title">Form Pemesanan Bus</h2>
                
                <form method="POST" id="bookingForm">
                    <div class="form-group">
                        <label class="form-label">Jenis Paket</label>
                        <select name="jenis_paket" class="form-control form-select" required onchange="updateHarga(); toggleTanggalKembali()">
                            <option value="">Pilih Paket</option>
                            <?php 
                            mysqli_data_seek($result_harga, 0); // Reset pointer
                            while($harga = mysqli_fetch_array($result_harga)): ?>
                                <option value="<?php echo $harga['jenis_harga']; ?>" data-harga="<?php echo $harga['harga']; ?>">
                                    <?php echo $harga['jenis_harga']; ?> - Rp <?php echo number_format($harga['harga'], 0, ',', '.'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tanggal Berangkat</label>
                        <input type="date" name="tanggal_berangkat" class="form-control" min="<?php echo date('Y-m-d'); ?>" required onchange="calculateTotalHarga()">
                    </div>

                    <!-- TAMBAHAN: Input Jam Berangkat -->
                    <div class="form-group">
                        <label class="form-label">Jam Berangkat</label>
                        <input type="time" name="jam_berangkat" class="form-control" value="08:00" required>
                        <small style="color: #64748b; font-size: 12px;">Pilih jam penjemputan</small>
                    </div>

                    <div class="form-group" id="tanggalKembaliGroup">
                        <label class="form-label">Tanggal Kembali</label>
                        <input type="date" name="tanggal_kembali" class="form-control" min="<?php echo date('Y-m-d'); ?>" onchange="calculateTotalHarga()">
                        <small style="color: #64748b; font-size: 12px;">Hanya untuk paket 24 Jam dan Luar Kota</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lokasi Penjemputan</label>
                        <input type="text" name="lokasi_penjemputan" class="form-control" placeholder="Alamat lengkap penjemputan" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tujuan</label>
                        <input type="text" name="tujuan" class="form-control" placeholder="Tujuan perjalanan" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jumlah Penumpang</label>
                        <input type="number" name="jumlah_penumpang" class="form-control" min="1" max="<?php echo $bus['kapasitas']; ?>" value="1" required>
                        <small style="color: #64748b; font-size: 12px;">Maksimal: <?php echo $bus['kapasitas']; ?> penumpang</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Keterangan Tambahan (Opsional)</label>
                        <textarea name="keterangan" class="form-control" placeholder="Catatan khusus atau permintaan tambahan"></textarea>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        Kirim Pemesanan
                    </button>
                </form>

                <!-- TAMBAHAN: Tombol Batal untuk pesanan yang sedang menunggu konfirmasi -->
                <?php
                // Query untuk mengecek apakah user memiliki pesanan yang sedang menunggu konfirmasi untuk bus ini
                $query_pending_booking = "SELECT * FROM pemesanan 
                                         WHERE user_id = $user_id 
                                         AND bus_id = $bus_id 
                                         AND status = 'Menunggu Konfirmasi' 
                                         ORDER BY created_at DESC 
                                         LIMIT 1";
                $result_pending = mysqli_query($connect, $query_pending_booking);
                $pending_booking = mysqli_fetch_array($result_pending);
                
                if ($pending_booking): ?>
                    <div class="action-buttons">
                        <a href="user_booking.php?action=cancel&id=<?php echo $pending_booking['id'] ?>" 
                           class="btn btn-cancel" 
                           onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')">
                            <i class="fas fa-times"></i> Batalkan Pesanan Sebelumnya
                        </a>
                    </div>
                    <div style="margin-top: 15px; padding: 12px; background: #fef3c7; border-radius: 8px; border-left: 4px solid #f59e0b;">
                        <p style="color: #92400e; font-size: 14px; margin: 0;">
                            <i class="fas fa-info-circle"></i> 
                            Anda memiliki pesanan yang sedang menunggu konfirmasi untuk bus ini.
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Summary -->
            <div class="summary-card">
                <h3 class="bus-title"><?php echo $bus['nama_perusahaan'] ?> - <?php echo $bus['tipe bus'] ?></h3>
                <div class="bus-info">
                    <div class="info-item">
                        <span class="info-label">Tipe Bus:</span>
                        <span class="info-value"><?php echo $bus['tipe bus'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Jenis:</span>
                        <span class="info-value"><?php echo $bus['jenis'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Kapasitas:</span>
                        <span class="info-value"><?php echo $bus['kapasitas'] ?> Kursi</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value" style="color: <?php echo $bus['status'] == 'Tersedia' ? '#10b981' : '#ef4444'; ?>">
                            <?php echo $bus['status'] ?>
                        </span>
                    </div>
                </div>

                <div class="price-display">
                    <div class="price-label">Total Harga</div>
                    <div class="price-amount" id="displayHarga">Rp 0</div>
                    <div class="price-details" id="priceDetails"></div>
                    <small id="priceNote">Pilih paket untuk melihat harga</small>
                </div>

                <div style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 8px; border-left: 4px solid #f59e0b;">
                    <h4 style="color: #92400e; margin-bottom: 8px; font-size: 14px;">
                        <i class="fas fa-info-circle"></i> Informasi Penting
                    </h4>
                    <ul style="color: #92400e; font-size: 12px; padding-left: 20px;">
                        <li>Harga sudah termasuk sopir, BBM, dan tol</li>
                        <li>Tidak termasuk biaya parkir dan akomodasi sopir</li>
                        <li>Pemesanan harus dikonfirmasi oleh admin terlebih dahulu</li>
                        <li>Pembayaran dilakukan setelah pemesanan dikonfirmasi</li>
                        <li>Paket 6 Jam: hanya diperbolehkan 1 tujuan wisata</li>
                        <li>Paket 12 Jam: hanya diperbolehkan 3 tujuan wisata</li>
                        <li>Paket 24 Jam & Luar Kota: Biaya tambahan untuk hari ekstra</li>
                        <li>Pesanan dapat dibatalkan selama status "Menunggu Konfirmasi"</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentHargaPaket = 0;
        let currentJenisPaket = '';

        function updateHarga() {
            const select = document.querySelector('select[name="jenis_paket"]');
            const selectedOption = select.options[select.selectedIndex];
            const harga = selectedOption.getAttribute('data-harga');
            const displayHarga = document.getElementById('displayHarga');
            const priceNote = document.getElementById('priceNote');
            const priceDetails = document.getElementById('priceDetails');
            
            if (harga) {
                currentHargaPaket = parseInt(harga);
                currentJenisPaket = selectedOption.value;
                calculateTotalHarga();
                priceNote.textContent = selectedOption.textContent.split(' - ')[0];
            } else {
                currentHargaPaket = 0;
                currentJenisPaket = '';
                displayHarga.textContent = 'Rp 0';
                priceDetails.innerHTML = '';
                priceNote.textContent = 'Pilih paket untuk melihat harga';
            }
        }

        function calculateTotalHarga() {
            if (!currentHargaPaket) return;

            const tanggalBerangkat = document.querySelector('input[name="tanggal_berangkat"]').value;
            const tanggalKembali = document.querySelector('input[name="tanggal_kembali"]').value;
            const displayHarga = document.getElementById('displayHarga');
            const priceDetails = document.getElementById('priceDetails');

            let totalHarga = currentHargaPaket;
            let detailsHTML = '';

            // Paket yang memerlukan perhitungan hari tambahan
            const paketDenganHariTambahan = [
                'Paket 24 Jam',
                'Paket 24 Jam (Weekend)', 
                'Luar Kota'
            ];

            if (paketDenganHariTambahan.includes(currentJenisPaket) && tanggalBerangkat && tanggalKembali) {
                const tgl1 = new Date(tanggalBerangkat);
                const tgl2 = new Date(tanggalKembali);
                const selisihHari = Math.ceil((tgl2 - tgl1) / (1000 * 60 * 60 * 24));
                
                if (selisihHari > 0) {
                    let hargaPerHariTambahan = currentHargaPaket;
                    
                    // Sesuaikan rate untuk Luar Kota
                    if (currentJenisPaket === 'Luar Kota') {
                        hargaPerHariTambahan = currentHargaPaket * 0.8; // 80% dari harga paket
                    }
                    
                    const biayaTambahan = hargaPerHariTambahan * selisihHari;
                    totalHarga += biayaTambahan;

                    detailsHTML = `
                        <div>Harga Paket: Rp ${formatRupiah(currentHargaPaket)}</div>
                        <div>${selisihHari} hari tambahan: Rp ${formatRupiah(biayaTambahan)}</div>
                        <div style="margin-top: 5px; border-top: 1px solid rgba(255,255,255,0.3); padding-top: 5px;">
                            <strong>Total: Rp ${formatRupiah(totalHarga)}</strong>
                        </div>
                    `;
                } else {
                    detailsHTML = `<div>Harga paket 1 hari: Rp ${formatRupiah(totalHarga)}</div>`;
                }
            } else {
                detailsHTML = `<div>Harga paket: Rp ${formatRupiah(totalHarga)}</div>`;
            }

            displayHarga.textContent = 'Rp ' + formatRupiah(totalHarga);
            priceDetails.innerHTML = detailsHTML;
        }

        function toggleTanggalKembali() {
            const select = document.querySelector('select[name="jenis_paket"]');
            const selectedOption = select.options[select.selectedIndex];
            const tanggalKembaliGroup = document.getElementById('tanggalKembaliGroup');
            const tanggalKembaliInput = document.querySelector('input[name="tanggal_kembali"]');
            
            // Paket yang memerlukan tanggal kembali
            const paketDenganTanggalKembali = [
                'Paket 24 Jam',
                'Paket 24 Jam (Weekend)', 
                'Luar Kota'
            ];
            
            const paketTerpilih = selectedOption.value;
            
            if (paketDenganTanggalKembali.includes(paketTerpilih)) {
                // Tampilkan tanggal kembali dan set required
                tanggalKembaliGroup.classList.remove('hidden');
                tanggalKembaliInput.required = true;
                
                // Set tanggal kembali default (1 hari setelah tanggal berangkat)
                const tanggalBerangkat = document.querySelector('input[name="tanggal_berangkat"]').value;
                if (tanggalBerangkat) {
                    const nextDay = new Date(tanggalBerangkat);
                    nextDay.setDate(nextDay.getDate() + 1);
                    tanggalKembaliInput.value = nextDay.toISOString().split('T')[0];
                }
            } else {
                // Sembunyikan tanggal kembali dan hapus required
                tanggalKembaliGroup.classList.add('hidden');
                tanggalKembaliInput.required = false;
                tanggalKembaliInput.value = ''; // Kosongkan nilai
            }
            
            calculateTotalHarga();
        }

        function formatRupiah(angka) {
            return parseInt(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function confirmLogout() {
            return confirm('Apakah Anda yakin ingin logout?');
        }

        // Form validation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const tanggalBerangkat = document.querySelector('input[name="tanggal_berangkat"]').value;
            const tanggalKembali = document.querySelector('input[name="tanggal_kembali"]').value;
            const jenisPaket = document.querySelector('select[name="jenis_paket"]').value;
            
            const paketDenganTanggalKembali = [
                'Paket 24 Jam',
                'Paket 24 Jam (Weekend)', 
                'Luar Kota'
            ];
            
            if (!jenisPaket) {
                e.preventDefault();
                alert('Silakan pilih jenis paket!');
                return false;
            }
            
            // Validasi khusus untuk paket yang memerlukan tanggal kembali
            if (paketDenganTanggalKembali.includes(jenisPaket)) {
                if (!tanggalKembali) {
                    e.preventDefault();
                    alert('Tanggal kembali harus diisi untuk paket ini!');
                    return false;
                }
                
                if (tanggalKembali < tanggalBerangkat) {
                    e.preventDefault();
                    alert('Tanggal kembali tidak boleh sebelum tanggal berangkat!');
                    return false;
                }
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleTanggalKembali(); // Set initial state
        });
    </script>
</body>
</html>