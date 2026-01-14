<?php
// user/sidebar.php
?>
<div class="user-sidebar">
    <div class="user-profile">
        <div class="profile-img">
            <i class="fas fa-user-circle"></i>
            <div class="profile-status"></div>
        </div>
        <h3><?php echo $_SESSION['full_name']; ?></h3>
        <p><?php echo $_SESSION['email']; ?></p>
        <p>Member sejak: <?php echo date('M Y', strtotime($_SESSION['created_at'] ?? 'now')); ?></p>
    </div>
    
    <nav class="user-menu">
        <ul>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'my-tickets.php' ? 'active' : ''; ?>">
                <a href="my-tickets.php"><i class="fas fa-ticket-alt"></i> Tiket Saya</a>
            </li>
            <li>
                <a href="../search.php"><i class="fas fa-bus"></i> Pesan Tiket</a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <a href="profile.php"><i class="fas fa-user"></i> Profil Saya</a>
            </li>
            <li>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>
</div>