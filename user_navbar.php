<?php
// Pastikan session sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include koneksi database
include 'koneksi.php';

// Query data user jika sudah login
$user = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query_user = "SELECT * FROM datauser WHERE id = $user_id";
    $result_user = mysqli_query($connect, $query_user);
    if ($result_user && mysqli_num_rows($result_user) > 0) {
        $user = mysqli_fetch_array($result_user);
    }
}
?>

<style>
/* NAVBAR STYLES - KONSISTEN DI SEMUA HALAMAN */
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

.user-info {
    color: white;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.username {
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 768px) {
    .navbar {
        padding: 1rem;
        flex-direction: column;
        gap: 1rem;
    }
    
    .nav-links {
        flex-wrap: wrap;
        justify-content: center;
        gap: 1rem;
    }
}
</style>

<!-- Navbar HTML -->
<nav class="navbar">
    <div class="logo">
        <i class="fas fa-bus"></i> BISATA
    </div>
    <div class="nav-links">
        <a href="user_home.php">Daftar Bus</a>
        <a href="user_booking_history.php">Riwayat Pesanan</a>  
        <div class="user-info">
            <?php if (isset($_SESSION['user_id']) && $user): ?>
                <span class="username"><?php echo htmlspecialchars($user['nama_lengkap']); ?></span>
                <a href="index.html" class="logout-btn" onclick="return confirmLogout()">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="login.php" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
function confirmLogout() {
    return confirm('Apakah Anda yakin ingin logout?');
}
</script>