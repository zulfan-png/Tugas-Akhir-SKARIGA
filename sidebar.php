<?php
// sidebar.php
// Mendapatkan nama file saat ini untuk active menu
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="main-sidebar">
    <section class="sidebar">
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">MAIN NAVIGATION</li>
            <li class="<?php echo $current_page == 'admin_bus_jadwal.php' ? 'active' : ''; ?>">
                <a href="admin_bus_jadwal.php">
                    <i class="fa fa-calendar"></i> <span>Jadwal Bus</span>
                </a>
            </li>
            <li class="<?php echo $current_page == 'admin_booking.php' ? 'active' : ''; ?>">
                <a href="admin_booking.php">
                    <i class="fa fa-calendar-check-o"></i> <span>Management Pesanan</span>
                </a>
            </li>
            <li class="<?php echo $current_page == 'admin_pemesanan_tambah.php' ? 'active' : ''; ?>">
                <a href="admin_pemesanan_tambah.php">
                    <i class="fa fa-plus-circle"></i> <span>Tambah Pemesanan</span>
                </a>
            </li>
            <li class="<?php echo $current_page == 'admin_bus.php' ? 'active' : ''; ?>">
                <a href="admin_bus.php">
                    <i class="fa fa-bus"></i> <span>Data Bus</span>
                </a>
            </li>
            <li class="<?php echo $current_page == 'admin_user.php' ? 'active' : ''; ?>">
                <a href="admin_user.php">
                    <i class="fa fa-users"></i> <span>Data User</span>
                </a>
            </li>
            <li>
                <a href="?logout=true">
                    <i class="fa fa-sign-out"></i> <span>Logout</span>
                </a>
            </li>
        </ul>
    </section>
</aside>