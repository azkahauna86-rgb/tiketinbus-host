<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user bookings
$query = "SELECT b.*, r.departure_city, r.arrival_city, r.departure_time, r.price,
                 bu.bus_number, op.name as operator_name
          FROM bookings b
          JOIN routes r ON b.route_id = r.id
          JOIN buses bu ON r.bus_id = bu.id
          JOIN bus_operators op ON bu.operator_id = op.id
          WHERE b.user_id = '$user_id'
          ORDER BY b.booking_date DESC
          LIMIT 5";
$bookings_result = mysqli_query($conn, $query);

// Get booking stats
$total_bookings = mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE user_id = '$user_id'");
$total_bookings = mysqli_fetch_assoc($total_bookings)['total'];

$active_bookings = mysqli_query($conn, "SELECT COUNT(*) as total FROM bookings WHERE user_id = '$user_id' AND status = 'confirmed'");
$active_bookings = mysqli_fetch_assoc($active_bookings)['total'];

$total_spent = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM bookings WHERE user_id = '$user_id' AND payment_status = 'paid'");
$total_spent = mysqli_fetch_assoc($total_spent)['total'] ?: 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - BusTicket</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="user-container">
        <!-- Sidebar -->
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
                    <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="my-tickets.php"><i class="fas fa-ticket-alt"></i> Tiket Saya</a></li>
                    <li><a href="../search.php"><i class="fas fa-bus"></i> Pesan Tiket</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profil Saya</a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="user-main">
            <h1>Dashboard</h1>
            <p class="welcome-message">
                <i class="fas fa-hand-wave"></i> Selamat datang, <?php echo $_SESSION['full_name']; ?>!
            </p>
            
            <!-- Quick Stats -->
            <div class="user-stats">
                <div class="user-stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Pemesanan</h3>
                        <p class="stat-number"><?php echo $total_bookings; ?></p>
                    </div>
                </div>
                
                <div class="user-stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Tiket Aktif</h3>
                        <p class="stat-number"><?php echo $active_bookings; ?></p>
                    </div>
                </div>
                
                <div class="user-stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Pengeluaran</h3>
                        <p class="stat-number">Rp <?php echo number_format($total_spent, 0, ',', '.'); ?></p>
                    </div>
                </div>
                
                <div class="user-stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Poin Reward</h3>
                        <p class="stat-number"><?php echo $total_bookings * 10; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Tickets -->
            <div class="recent-tickets">
                <h2><i class="fas fa-history"></i> Tiket Terbaru</h2>
                
                <?php if (mysqli_num_rows($bookings_result) > 0): ?>
                    <div class="ticket-list">
                        <?php while ($booking = mysqli_fetch_assoc($bookings_result)): 
                            $status_class = strtolower($booking['status']);
                            $payment_class = strtolower($booking['payment_status']);
                        ?>
                            <div class="ticket-card">
                                <div class="ticket-header">
                                    <span class="ticket-number">
                                        <i class="fas fa-ticket-alt"></i> <?php echo $booking['booking_number']; ?>
                                    </span>
                                    <span class="ticket-status <?php echo $status_class; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                                <div class="ticket-body">
                                    <div class="route-info">
                                        <h3><?php echo $booking['departure_city']; ?> → <?php echo $booking['arrival_city']; ?></h3>
                                        <p>
                                            <i class="fas fa-calendar"></i> 
                                            <?php echo date('d M Y', strtotime($booking['departure_time'])); ?>
                                            &nbsp;&nbsp;
                                            <i class="fas fa-clock"></i> 
                                            <?php echo date('H:i', strtotime($booking['departure_time'])); ?>
                                        </p>
                                    </div>
                                    <div class="bus-info">
                                        <p><i class="fas fa-bus"></i> <?php echo $booking['operator_name']; ?> - <?php echo $booking['bus_number']; ?></p>
                                    </div>
                                    <div class="ticket-price">
                                        <p><?php echo $booking['seats_booked']; ?> kursi × Rp <?php echo number_format($booking['price'], 0, ',', '.'); ?></p>
                                        <p class="total-price">Total: Rp <?php echo number_format($booking['total_amount'], 0, ',', '.'); ?></p>
                                    </div>
                                    <div class="payment-info">
                                        <span class="status <?php echo $payment_class; ?>">
                                            <?php echo ucfirst($booking['payment_status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="ticket-actions">
                                    <?php if ($booking['status'] == 'confirmed'): ?>
                                        <a href="ticket.php?id=<?php echo $booking['id']; ?>" class="btn-download" target="_blank">
                                            <i class="fas fa-download"></i> Download Tiket
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($booking['status'] != 'cancelled'): ?>
                                        <a href="cancel.php?id=<?php echo $booking['id']; ?>" class="btn-cancel" 
                                           onclick="return confirm('Batalkan tiket ini?')">
                                            <i class="fas fa-times"></i> Batalkan
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="booking-details.php?id=<?php echo $booking['id']; ?>" class="btn-view">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="my-tickets.php" class="btn-book" style="display: inline-block;">
                            <i class="fas fa-list"></i> Lihat Semua Tiket
                        </a>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-ticket-alt"></i>
                        <h3>Belum ada tiket</h3>
                        <p>Anda belum memiliki tiket. Pesan tiket pertama Anda sekarang!</p>
                        <a href="../search.php" class="btn-book">
                            <i class="fas fa-bus"></i> Pesan Tiket
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Quick Actions -->
            <div class="recent-tickets">
                <h2><i class="fas fa-bolt"></i> Aksi Cepat</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                    <a href="../search.php" class="user-stat-card" style="text-decoration: none; text-align: center;">
                        <div class="stat-icon" style="margin: 0 auto 15px;">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Cari Bus</h3>
                        <p>Temukan bus untuk perjalanan Anda</p>
                    </a>
                    
                    <a href="my-tickets.php" class="user-stat-card" style="text-decoration: none; text-align: center;">
                        <div class="stat-icon" style="margin: 0 auto 15px;">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <h3>Tiket Saya</h3>
                        <p>Lihat semua tiket Anda</p>
                    </a>
                    
                    <a href="profile.php" class="user-stat-card" style="text-decoration: none; text-align: center;">
                        <div class="stat-icon" style="margin: 0 auto 15px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3>Profil</h3>
                        <p>Kelola akun Anda</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <style>
    .total-price {
        font-size: 20px;
        font-weight: bold;
        color: #e74c3c;
        margin-top: 5px;
    }
    
    .payment-info {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    </style>
</body>
</html>