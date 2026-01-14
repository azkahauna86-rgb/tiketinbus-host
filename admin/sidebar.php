<?php
// admin/sidebar.php
?>
<div class="sidebar">
    <div class="logo">
        <h2><i class="fas fa-bus"></i> <span>Admin Panel</span></h2>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'buses.php' ? 'active' : ''; ?>">
                <a href="buses.php"><i class="fas fa-bus"></i> <span>Bus Management</span></a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'routes.php' ? 'active' : ''; ?>">
                <a href="routes.php"><i class="fas fa-route"></i> <span>Rute & Jadwal</span></a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>">
                <a href="bookings.php"><i class="fas fa-ticket-alt"></i> <span>Pemesanan</span></a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <a href="users.php"><i class="fas fa-users"></i> <span>Pengguna</span></a>
            </li>
            <li>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
            </li>
        </ul>
    </nav>
</div>