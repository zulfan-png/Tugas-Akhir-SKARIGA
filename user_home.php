<?php
    include 'koneksi.php';
    session_start();

    // Handle logout
    if (isset($_GET['logout'])) {
        session_destroy();
        echo "<script>window.location.href = 'index.html'</script>";
        exit();
    }

    // Query data user jika sudah login - TAMBAHAN INI
    $user = null;
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $query_user = "SELECT * FROM datauser WHERE id = $user_id";
        $result_user = mysqli_query($connect, $query_user);
        if ($result_user && mysqli_num_rows($result_user) > 0) {
            $user = mysqli_fetch_array($result_user);
        }
    }

    // Handle search functionality
    $search_keyword = "";
    $where_condition = "WHERE b.status = 'Tersedia'";
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search_keyword = mysqli_real_escape_string($connect, $_GET['search']);
        $where_condition = "WHERE b.status = 'Tersedia' AND (
                            b.`tipe bus` LIKE '%$search_keyword%' OR 
                            b.jenis LIKE '%$search_keyword%' OR
                            pb.nama_perusahaan LIKE '%$search_keyword%'
                        )";
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
        
        /* Search Section */
        .search-section {
            max-width: 600px;
            margin: 0 auto 40px auto;
            padding: 0 20px;
        }
        
        .search-container {
            position: relative;
            width: 100%;
        }
        
        .search-input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            font-size: 16px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #1e40af;
            box-shadow: 0 4px 15px rgba(30, 64, 175, 0.2);
        }
        
        .search-button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .search-button:hover {
            background: #3730a3;
            transform: translateY(-50%) scale(1.05);
        }
        
        .search-examples {
            text-align: center;
            margin-top: 15px;
            color: #64748b;
            font-size: 14px;
        }
        
        .search-examples span {
            display: inline-block;
            margin: 0 8px;
            padding: 4px 12px;
            background: #f1f5f9;
            border-radius: 15px;
            color: #475569;
        }
        
        /* Search Results Info */
        .search-results-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 0 20px;
        }
        
        .search-results-info .results-count {
            color: #1e40af;
            font-weight: 600;
        }
        
        .search-results-info .search-keyword {
            color: #ef4444;
            font-weight: 600;
            background: #fef2f2;
            padding: 2px 8px;
            border-radius: 4px;
            margin: 0 5px;
        }
        
        .clear-search {
            color: #64748b;
            text-decoration: none;
            margin-left: 10px;
            padding: 5px 10px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .clear-search:hover {
            background: #f1f5f9;
            color: #374151;
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

        /* Counter bus tersedia */
        .bus-counter {
            text-align: center;
            margin-bottom: 20px;
            color: #64748b;
            font-size: 14px;
        }

        .counter-number {
            font-weight: 600;
            color: #1e40af;
        }

        /* Highlight search results */
        .highlight {
            background-color: #fef3c7;
            padding: 2px 4px;
            border-radius: 3px;
            color: #92400e;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
     <?php include 'user_navbar.php'; ?>

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

        <!-- Search Section -->
        <div class="search-section">
            <form method="GET" action="" class="search-container">
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       placeholder="Cari bus berdasarkan nama, jenis, atau perusahaan..." 
                       value="<?php echo htmlspecialchars($search_keyword); ?>">
                <button type="submit" class="search-button">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <?php
        // Query untuk mengambil data bus dengan kondisi pencarian
        $query = "SELECT 
                    b.id,
                    b.`tipe bus`,
                    b.jenis,
                    b.kapasitas,
                    b.fasilitas,
                    b.deskripsi,
                    b.status,
                    pb.nama_perusahaan,
                    (SELECT gambar_url FROM bus_gambar WHERE bus_id = b.id LIMIT 1) as gambar_utama,
                    (SELECT MIN(harga) FROM harga_bus WHERE bus_id = b.id AND jenis_harga = 'Paket 6 Jam') as harga_terendah
                 FROM bus b 
                 LEFT JOIN perusahaan_bus pb ON b.perusahaan_id = pb.id
                 $where_condition
                 ORDER BY b.created_at DESC";
        $result = mysqli_query($connect, $query);
        
        // Cek jika query gagal
        if (!$result) {
            die("Query error: " . mysqli_error($connect));
        }
        
        $jumlah_bus_tersedia = mysqli_num_rows($result);
        ?>

        <!-- Search Results Info -->
        <?php if (!empty($search_keyword)): ?>
        <div class="search-results-info">
            <p>
                Menampilkan <span class="results-count"><?php echo $jumlah_bus_tersedia; ?></span> hasil untuk 
                <span class="search-keyword">"<?php echo htmlspecialchars($search_keyword); ?>"</span>
                <a href="user_home.php" class="clear-search">
                    <i class="fas fa-times"></i> Hapus Pencarian
                </a>
            </p>
        </div>
        <?php else: ?>
        <!-- Counter bus tersedia -->
        <div class="bus-counter">
            Menampilkan <span class="counter-number"><?php echo $jumlah_bus_tersedia; ?></span> bus tersedia untuk Anda
        </div>
        <?php endif; ?>
        
        <div class="cards-container" id="bus-cards">
            <?php
            if ($jumlah_bus_tersedia > 0) {
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
                    
                    // Harga mulai dari (ambil harga paket 6 jam)
                    $harga_terendah = $row['harga_terendah'] ? $row['harga_terendah'] : 0;

                    // Highlight search results
                    $highlight_title = $row['nama_perusahaan'] . ' - ' . $row['tipe bus'];
                    $highlight_jenis = $row['jenis'];
            ?>
                <div class="card">
                    <a href="user_detail.php?id=<?php echo $row['id'] ?>" class="card-link">
                        <div class="card-header <?php echo $bgClass; ?>" style="<?php echo $backgroundImage; ?>">
                            <div class="card-header-content">
                                <div class="card-title"><?php echo $highlight_title; ?></div>
                                <div class="card-destination">
                                    <i class="fas fa-bus"></i>
                                    <?php echo $highlight_jenis; ?> • <?php echo $row['kapasitas'] ?> Kursi
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
                    <h3>
                        <?php if (!empty($search_keyword)): ?>
                            Tidak ditemukan bus dengan kata kunci "<?php echo htmlspecialchars($search_keyword); ?>"
                        <?php else: ?>
                            Maaf, tidak ada bus tersedia saat ini
                        <?php endif; ?>
                    </h3>
                    <p>
                        <?php if (!empty($search_keyword)): ?>
                            Coba gunakan kata kunci lain atau lihat semua bus yang tersedia.
                        <?php else: ?>
                            Semua bus sedang dipesan atau sedang dalam perawatan.
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($search_keyword)): ?>
                        <a href="user_home.php" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background: #1e40af; color: white; text-decoration: none; border-radius: 5px;">
                            <i class="fas fa-bus"></i> Lihat Semua Bus
                        </a>
                    <?php else: ?>
                        <p style="margin-top: 10px; font-size: 14px;">
                            Silakan coba lagi nanti atau hubungi customer service kami.
                        </p>
                    <?php endif; ?>
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
        function confirmLogout() {
            return confirm('Apakah Anda yakin ingin logout?');
        }

        // Auto focus on search input when page loads if there's a search keyword
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            const urlParams = new URLSearchParams(window.location.search);
            const searchParam = urlParams.get('search');
            
            if (searchParam && searchInput) {
                searchInput.focus();
                searchInput.setSelectionRange(searchParam.length, searchParam.length);
            }
        });

        // Clear search when clicking the clear button
        function clearSearch() {
            window.location.href = 'user_home.php';
        }
    </script>
</body>
</html>     