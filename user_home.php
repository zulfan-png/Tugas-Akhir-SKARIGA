<?php
    include 'koneksi.php';
    session_start();

    // Handle logout
    if (isset($_GET['logout'])) {
        session_destroy();
        echo "<script>window.location.href = 'index.html'</script>";
        exit();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bisata - Daftar Bus</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reset dan gaya dasar */
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
        
        /* Navbar */
        .navbar {
            background: #1e40af;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .nav-links a:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.3s;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background: #dc2626;
        }

        .login-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.3s;
            font-size: 14px;
        }
        
        .login-btn:hover {
            background: #0da271;
        }
        
        /* Container utama */
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Hero Section dengan Gambar */
        .hero {
            background: linear-gradient(rgba(30, 64, 175, 0.8), rgba(55, 48, 163, 0.8)), 
                        url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 120px 20px;
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
        }
        
        .hero p {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.95;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.3);
        }
        
        .hero-cta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background-color: white;
            color: #1e40af;
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        
        .hero-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.4);
            background-color: #f1f5f9;
        }
        
        /* Header */
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
        }
        
        .header h1 {
            color: #1e40af;
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #64748b;
            font-size: 16px;
        }
        
        /* Grid card */
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
            padding: 0 20px;
        }
        
        /* Card individual */
        .card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Card Header dengan Gambar */
        .card-header {
            position: relative;
            height: 160px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            flex-shrink: 0;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.7) 100%);
            z-index: 1;
        }
        
        .card-header-content {
            position: relative;
            z-index: 2;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.8);
        }
        
        .card-destination {
            font-size: 14px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 8px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        }
        
        .card-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .card-price {
            font-size: 18px;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 10px;
        }
        
        .price-note {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 15px;
        }
        
        .card-description {
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }
        
        .card-features {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .feature-tag {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .card-footer {
            padding: 15px 20px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            margin-top: auto;
            flex-shrink: 0;
        }
        
        .detail-btn {
            background: #1e40af;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background 0.3s;
            font-size: 14px;
            width: 100%;
            justify-content: center;
        }
        
        .detail-btn:hover {
            background: #3730a3;
        }
        
        /* Status badge */
        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 2;
        }
        
        .status-tersedia {
            background-color: #10b981;  
            color: white;
        }
        
        /* Default background jika tidak ada gambar */
        .default-bg {
            background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
        }
        
        /* State kosong */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
            grid-column: 1 / -1;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #cbd5e1;
        }
        
        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #475569;
        }

        /* User info */
        .user-info {
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .username {
            font-weight: 500;
        }

        .card-link {
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-bus"></i> BISATA
        </div>
        <div class="nav-links">
            <a href="#bus-list">Daftar Bus</a>
            <a href="#about">Tentang Kami</a>
            <a href="#contact">Kontak</a>
                <div class="user-info">
                    <a href="?logout=true" class="logout-btn" onclick="return confirmLogout()">Logout
                    </a>
                </div>
        </div>
    </nav>

    <!-- Hero Section dengan Gambar -->
    <section class="hero">
        <div class="hero-content">
            <h1>BISATA?</h1>
            <p>JELAS BISA! PILIH BUS MU SEKARANG!</p>
            <a href="#bus-list" class="hero-cta">
                <i class="fas fa-bus"></i>
                Lihat Daftar Bus
            </a>
        </div>
    </section>

    <div class="container">
        <div class="header" id="bus-list">
            <h1>Pilihan Bus Terbaik</h1>
            <p>Temukan bus perfect untuk perjalanan Anda</p>
        </div>
        
        <div class="cards-container">
            <?php
            // Query untuk mengambil data dari tabel bus
            $query = "SELECT b.*, 
                     (SELECT gambar_url FROM bus_gambar WHERE bus_id = b.id LIMIT 1) as gambar_utama 
                     FROM bus b";
            $result = mysqli_query($connect, $query);

            if (mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_array($result)){
                    // Cek apakah ada gambar di database
                    $backgroundImage = '';
                    $bgClass = 'default-bg';
                    
                    if (!empty($row['gambar_utama'])) {
                        $backgroundImage = "background-image: url('uploads/" . $row['gambar_utama'] . "');";
                        $bgClass = '';
                    }
                    
                    // Format fasilitas
                    $fasilitas_array = [];
                    if (!empty($row['fasilitas'])) {
                        $fasilitas_array = explode(", ", $row['fasilitas']);
                        // Ambil maksimal 3 fasilitas untuk ditampilkan di card
                        $fasilitas_array = array_slice($fasilitas_array, 0, 3);
                    }
                    
                    // Harga mulai dari (ambil harga terkecil)
                    $harga_array = [$row['harga1'], $row['harga2'], $row['harga3'], $row['harga4'], $row['harga5'], $row['harga6']];
                    $harga_terendah = min($harga_array);
            ?>
                <div class="card">
                    <a href="user_detail.php?id=<?php echo $row['id'] ?>" class="card-link">
                        <div class="card-header <?php echo $bgClass; ?>" style="<?php echo $backgroundImage; ?>">
                            <div class="card-header-content">
                                <div class="card-title"><?php echo $row['perusahaan'] ?> - <?php echo $row['tipe bus'] ?></div>
                                <div class="card-destination">
                                    <i class="fas fa-bus"></i>
                                    <?php echo $row['jenis'] ?> • <?php echo $row['kapasitas'] ?> Kursi
                                </div>
                            </div>
                            <div class="status-badge status-tersedia">
                                Tersedia
                            </div>
                        </div>
                        <div class="card-body"> 
                            <div class="card-price">Mulai dari Rp <?php echo number_format($harga_terendah, 0, ',', '.') ?> / 6 jam</div>
                            <div class="price-note">*Harga bervariasi tergantung durasi</div>
                            <div class="card-description">
                                <?php echo substr($row['deskripsi'], 0, 100) . '...' ?>
                            </div>
                            <?php if (!empty($fasilitas_array)): ?>
                            <div class="card-features">
                                <?php foreach($fasilitas_array as $fasilitas): ?>
                                    <span class="feature-tag"><?php echo $fasilitas ?></span>
                                <?php endforeach; ?>
                                <?php if (count($fasilitas_array) >= 3): ?>
                                    <span class="feature-tag">+Lainnya</span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <span class="detail-btn">
                                <i class="fas fa-info-circle"></i>
                                Lihat Detail & Harga
                            </span>
                        </div>
                    </a>
                </div>
            <?php }
            } else { ?>
                <div class="empty-state">
                    <i class="fas fa-bus-slash"></i>
                    <h3>Maaf, tidak ada bus tersedia saat ini</h3>
                    <p>Semua bus sedang dipesan atau sedang dalam perawatan.</p>
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
        function confirmLogout() {
            return confirm('Apakah Anda yakin ingin logout?');
        }
    </script>
</body>
</html>