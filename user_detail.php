<?php
include 'koneksi.php';
session_start();

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    echo "<script>window.location.href = 'index.html'</script>";
    exit();
}

// Ambil ID bus dari URL
$id = $_GET['id'];

// Query data bus
$query_bus = "SELECT * FROM bus WHERE id = $id";
$result_bus = mysqli_query($connect, $query_bus);
$bus = mysqli_fetch_array($result_bus);

// Query gambar bus
$query_gambar = "SELECT * FROM bus_gambar WHERE bus_id = $id";
$result_gambar = mysqli_query($connect, $query_gambar);

// Format fasilitas
$fasilitas_array = [];
if (!empty($bus['fasilitas'])) {
    $fasilitas_array = explode(", ", $bus['fasilitas']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $bus['perusahaan'] ?> - <?php echo $bus['tipe bus'] ?> | BISATA</title>
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

        /* Container utama */
        .container {
            max-width: 1200px;
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

        /* Layout utama */
        .detail-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        /* Image Slider 16:9 */
        .image-slider {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }

        .slider-container {
            position: relative;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            background: #e2e8f0;
        }

        .slider-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .slider-image.active {
            display: block;
        }

        .slider-nav {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: background 0.3s;
        }

        .slider-dot.active {
            background: white;
        }

        .slider-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.8);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #1e40af;
            transition: all 0.3s;
            z-index: 10;
        }

        .slider-arrow:hover {
            background: white;
        }

        .slider-arrow.prev {
            left: 20px;
        }

        .slider-arrow.next {
            right: 20px;
        }

        /* Info Bus - Layout Baru yang Lebih Rapi */
        .bus-info {
            padding: 20px 0;
        }

        .bus-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 20px;
            line-height: 1.3;
        }

        /* Spesifikasi dalam grid yang rapi */
        .bus-specs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f1f5f9;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #1e40af;
        }

        .spec-item i {
            color: #1e40af;
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .spec-label {
            font-weight: 600;
            color: #475569;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .spec-value {
            color: #1e293b;
            font-weight: 500;
            font-size: 15px;
        }

        /* Fasilitas - Pindah ke bawah spesifikasi dan di atas deskripsi */
        .facilities-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
            grid-column: 1 / -1;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
        }

        .facilities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .facility-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-size: 14px;
        }

        .facility-item i {
            color: #10b981;
            font-size: 14px;
        }

        /* Deskripsi - Sekarang di bawah fasilitas */
        .description-section {
            padding: 30px;  
            border-radius: 12px;
            grid-column: 1 / -1;
        }

        .description-text {
            color: #475569;
            line-height: 1.8;
            text-align: justify;
        }

        /* Harga */
        .pricing-section {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
            margin-top: 20px;
            grid-column: 1 / -1;
        }

        .pricing-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .pricing-title {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .price-note {
            color: #64748b;
            font-size: 16px;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.5;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .price-category {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 30px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
        }

        .category-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .category-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .weekday-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            color: white;
        }

        .weekend-icon {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .category-title {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .category-subtitle {
            color: #64748b;
            font-size: 14px;
            margin-top: 5px;
        }

        .price-items {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 20px;
            background: white;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .duration {
            color: #475569;
            font-weight: 600;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .duration i {
            color: #1e40af;
            font-size: 14px;
        }

        .price {
            color: #1e40af;
            font-weight: 700;
            font-size: 20px;
            text-align: right;
        }

        .price-period {
            display: block;
            font-size: 12px;
            color: #64748b;
            font-weight: 500;
            margin-top: 2px;
        }

        /* CTA Button */
        .cta-section {
            text-align: center;
            padding-top: 30px;
            border-top: 2px solid #e2e8f0;
        }

        .book-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 18px 60px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
        }

        /* Empty state untuk gambar */
        .no-images {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #64748b;
            text-align: center;
        }

        .no-images i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #cbd5e1;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .detail-layout {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .bus-specs {
                grid-template-columns: 1fr;
            }
            
            .facilities-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            
            .pricing-grid {
                grid-template-columns: 1fr;
            }
            
            .price-items {
                grid-template-columns: 1fr;
            }
            
            .pricing-header {
                text-align: center;
            }
            
            .category-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .book-btn {
                padding: 15px 40px;
                font-size: 16px;
            }
            
            .description-section {
                margin-top: 0;
                padding: 20px;
            }
            
            .facilities-section {
                padding: 20px;
                margin-bottom: 20px;
            }
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
            <a href="user_dashboard.php">Daftar Bus</a>
            <a href="#about">Tentang Kami</a>
            <a href="#contact">Kontak</a>
            <div class="user-info">
                <a href="?logout=true" class="logout-btn" onclick="return confirmLogout()">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="user_home.php"><i class="fas fa-arrow-left"></i> Kembali ke Daftar Bus</a>
        </div>

        <div class="detail-layout">
            <!-- Image Slider 16:9 -->
            <div class="image-slider">
                <div class="slider-container" id="sliderContainer">
                    <?php if (mysqli_num_rows($result_gambar) > 0): ?>
                        <?php 
                        $image_index = 0;
                        while($gambar = mysqli_fetch_array($result_gambar)): 
                        ?>
                            <img src="uploads/<?php echo $gambar['gambar_url'] ?>" 
                                 alt="Gambar Bus" 
                                 class="slider-image <?php echo $image_index === 0 ? 'active' : ''; ?>"
                                 data-index="<?php echo $image_index; ?>">
                            <?php $image_index++; ?>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-images">
                            <i class="fas fa-image"></i>
                            <p>Tidak ada gambar tersedia</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (mysqli_num_rows($result_gambar) > 1): ?>
                    <button class="slider-arrow prev" onclick="changeSlide(-1)">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="slider-arrow next" onclick="changeSlide(1)">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    
                    <div class="slider-nav" id="sliderNav">
                        <?php for($i = 0; $i < mysqli_num_rows($result_gambar); $i++): ?>
                            <div class="slider-dot <?php echo $i === 0 ? 'active' : ''; ?>" 
                                 data-index="<?php echo $i; ?>" 
                                 onclick="goToSlide(<?php echo $i; ?>)"></div>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Bus Info - Layout Baru yang Lebih Rapi -->
            <div class="bus-info">
                <h1 class="bus-title"><?php echo $bus['perusahaan'] ?> - <?php echo $bus['tipe bus'] ?></h1>
                
                <!-- Spesifikasi dalam grid yang rapi -->
                <div class="bus-specs">
                    <div class="spec-item">
                        <i class="fas fa-building"></i>
                        <div>
                            <div class="spec-label">Perusahaan</div>
                            <div class="spec-value"><?php echo $bus['perusahaan'] ?></div>
                        </div>
                    </div>
                    <div class="spec-item">
                        <i class="fas fa-tag"></i>
                        <div>
                            <div class="spec-label">Tipe Bus</div>
                            <div class="spec-value"><?php echo $bus['tipe bus'] ?></div>
                        </div>
                    </div>
                    <div class="spec-item">
                        <i class="fas fa-bus"></i>
                        <div>
                            <div class="spec-label">Jenis</div>
                            <div class="spec-value"><?php echo $bus['jenis'] ?></div>
                        </div>
                    </div>
                    <div class="spec-item">
                        <i class="fas fa-users"></i>
                        <div>
                            <div class="spec-label">Kapasitas</div>
                            <div class="spec-value"><?php echo $bus['kapasitas'] ?> Kursi</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fasilitas - Sekarang di bawah spesifikasi dan di atas deskripsi -->
            <div class="facilities-section">
                <h3 class="section-title">Fasilitas</h3>
                <div class="facilities-grid">
                    <?php foreach($fasilitas_array as $fasilitas): ?>
                        <div class="facility-item">
                            <i class="fas fa-check-circle"></i>
                            <span><?php echo $fasilitas ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Deskripsi - Sekarang di bawah fasilitas -->
            <div class="description-section">
                <h3 class="section-title">Deskripsi</h3>
                <p class="description-text"><?php echo $bus['deskripsi'] ?></p>
            </div>
        </div>

        <!-- Harga -->
        <div class="pricing-section">
            <div class="pricing-header">
                <h2 class="pricing-title">Daftar Harga Sewa</h2>
                <p class="price-note">*Harga sudah termasuk sopir, BBM, dan tol. Tidak termasuk biaya parkir dan akomodasi sopir</p>
            </div>
            
            <div class="pricing-grid">
                <div class="price-category">
                    <div class="category-header">
                        <div class="category-icon weekday-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div>
                            <h4 class="category-title">Weekday</h4>
                            <p class="category-subtitle">Senin - Jumat</p>
                        </div>
                    </div>
                    <div class="price-items">
                        <div class="price-item">
                            <span class="duration">
                                <i class="fas fa-clock"></i>
                                6 Jam
                            </span>
                            <span class="price">
                                Rp <?php echo number_format($bus['harga1'], 0, ',', '.') ?>
                                <span class="price-period">per paket</span>
                            </span>
                        </div>
                        <div class="price-item">
                            <span class="duration">
                                <i class="fas fa-clock"></i>
                                12 Jam
                            </span>
                            <span class="price">
                                Rp <?php echo number_format($bus['harga2'], 0, ',', '.') ?>
                                <span class="price-period">per paket</span>
                            </span>
                        </div>
                        <div class="price-item">
                            <span class="duration">
                                <i class="fas fa-clock"></i>
                                24 Jam / Per-Hari
                            </span>
                            <span class="price">
                                Rp <?php echo number_format($bus['harga3'], 0, ',', '.') ?>
                                <span class="price-period">per paket</span>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="price-category">
                    <div class="category-header">
                        <div class="category-icon weekend-icon">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div>
                            <h4 class="category-title">Weekend</h4>
                            <p class="category-subtitle">Sabtu - Minggu / Hari Libur</p>
                        </div>
                    </div>
                    <div class="price-items">
                        <div class="price-item">
                            <span class="duration">
                                <i class="fas fa-clock"></i>
                                6 Jam
                            </span>
                            <span class="price">
                                Rp <?php echo number_format($bus['harga4'], 0, ',', '.') ?>
                                <span class="price-period">per paket</span>
                            </span>
                        </div>
                        <div class="price-item">
                            <span class="duration">
                                <i class="fas fa-clock"></i>
                                12 Jam
                            </span>
                            <span class="price">
                                Rp <?php echo number_format($bus['harga5'], 0, ',', '.') ?>
                                <span class="price-period">per paket</span>
                            </span>
                        </div>
                        <div class="price-item">
                            <span class="duration">
                                <i class="fas fa-clock"></i>
                                24 Jam / Per-Hari
                            </span>
                            <span class="price">
                                Rp <?php echo number_format($bus['harga6'], 0, ',', '.') ?>
                                <span class="price-period">per paket</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cta-section">
                <button class="book-btn" onclick="bookBus()">
                    <i class="fas fa-calendar-check"></i>
                    Pesan Sekarang
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slider-image');
        const dots = document.querySelectorAll('.slider-dot');
        const totalSlides = slides.length;

        function showSlide(index) {
            // Hide all slides
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            // Show current slide
            slides[index].classList.add('active');
            dots[index].classList.add('active');
            currentSlide = index;
        }

        function changeSlide(direction) {
            let newIndex = currentSlide + direction;
            
            if (newIndex < 0) {
                newIndex = totalSlides - 1;
            } else if (newIndex >= totalSlides) {
                newIndex = 0;
            }
            
            showSlide(newIndex);
        }

        function goToSlide(index) {
            showSlide(index);
        }

        // Auto slide (optional)
        setInterval(() => {
            if (totalSlides > 1) {
                changeSlide(1);
            }
        }, 5000);

        function bookBus() {
            alert('Fitur pemesanan akan segera tersedia!');
            // window.location.href = 'booking.php?id=<?php echo $bus['id']; ?>';
        }

        function confirmLogout() {
            return confirm('Apakah Anda yakin ingin logout?');
        }
    </script>
</body>
</html>