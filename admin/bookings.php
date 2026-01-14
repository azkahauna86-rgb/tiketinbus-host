<?php
require_once '../includes/config.php';
requireAdmin();

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = sanitize($_GET['action']);
    $id = sanitize($_GET['id']);
    
    if ($action == 'confirm') {
        $query = "UPDATE bookings SET status = 'confirmed' WHERE id = '$id'";
        mysqli_query($conn, $query);
        $_SESSION['message'] = 'Pemesanan dikonfirmasi';
        $_SESSION['message_type'] = 'success';
    } elseif ($action == 'cancel') {
        $query = "UPDATE bookings SET status = 'cancelled' WHERE id = '$id'";
        mysqli_query($conn, $query);
        $_SESSION['message'] = 'Pemesanan dibatalkan';
        $_SESSION['message_type'] = 'success';
    } elseif ($action == 'paid') {
        $query = "UPDATE bookings SET payment_status = 'paid' WHERE id = '$id'";
        mysqli_query($conn, $query);
        $_SESSION['message'] = 'Pembayaran dikonfirmasi';
        $_SESSION['message_type'] = 'success';
    } elseif ($action == 'delete') {
        $query = "DELETE FROM bookings WHERE id = '$id'";
        mysqli_query($conn, $query);
        $_SESSION['message'] = 'Pemesanan dihapus';
        $_SESSION['message_type'] = 'success';
    }
    
    header('Location: bookings.php');
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$where = "1=1";
if ($search) {
    $where .= " AND (b.booking_number LIKE '%$search%' OR u.full_name LIKE '%$search%' OR u.email LIKE '%$search%')";
}
if ($status) {
    $where .= " AND b.status = '$status'";
}

// Get bookings
$query = "SELECT b.*, u.full_name, u.email, u.phone, 
                 r.departure_city, r.arrival_city, r.departure_time,
                 bu.bus_number, op.name as operator_name
          FROM bookings b
          JOIN users u ON b.user_id = u.id
          JOIN routes r ON b.route_id = r.id
          JOIN buses bu ON r.bus_id = bu.id
          JOIN bus_operators op ON bu.operator_id = op.id
          WHERE $where
          ORDER BY b.booking_date DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Booking Management</h1>
                    <p>Kelola semua pemesanan tiket</p>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <span><?php echo $_SESSION['username']; ?></span>
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </header>

            <div class="content-section">
                <div class="section-header">
                    <h2>Daftar Pemesanan</h2>
                    
                    <!-- Search Form -->
                    <form method="GET" action="" class="search-form" style="display: flex; gap: 10px;">
                        <input type="text" name="search" placeholder="Cari booking..." 
                               value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="width: 200px;">
                        <select name="status" class="form-control" style="width: 150px;">
                            <option value="">Semua Status</option>
                            <option value="confirmed" <?php echo $status == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-search"></i> Cari
                        </button>
                        <a href="bookings.php" class="btn-action">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </form>
                </div>

                <!-- Bookings List -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Booking</th>
                            <th>Pelanggan</th>
                            <th>Rute</th>
                            <th>Bus</th>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td><?php echo $row['booking_number']; ?></td>
                                    <td>
                                        <strong><?php echo $row['full_name']; ?></strong><br>
                                        <small><?php echo $row['email']; ?></small><br>
                                        <small><?php echo $row['phone']; ?></small>
                                    </td>
                                    <td>
                                        <?php echo $row['departure_city']; ?> → <?php echo $row['arrival_city']; ?><br>
                                        <small><?php echo date('d M Y H:i', strtotime($row['departure_time'])); ?></small>
                                    </td>
                                    <td>
                                        <?php echo $row['bus_number']; ?><br>
                                        <small><?php echo $row['operator_name']; ?></small>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($row['booking_date'])); ?></td>
                                    <td>
                                        <?php echo $row['seats_booked']; ?> kursi<br>
                                        <strong>Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></strong>
                                    </td>
                                    <td>
                                        <span class="status <?php echo $row['status']; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status <?php echo $row['payment_status']; ?>">
                                            <?php echo ucfirst($row['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 5px;">
                                            <?php if ($row['status'] != 'confirmed'): ?>
                                                <a href="bookings.php?action=confirm&id=<?php echo $row['id']; ?>" 
                                                   class="btn-action edit" style="padding: 5px 10px; font-size: 12px;">
                                                    <i class="fas fa-check"></i> Confirm
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($row['status'] != 'cancelled'): ?>
                                                <a href="bookings.php?action=cancel&id=<?php echo $row['id']; ?>" 
                                                   class="btn-action delete" style="padding: 5px 10px; font-size: 12px;">
                                                    <i class="fas fa-times"></i> Cancel
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if ($row['payment_status'] != 'paid'): ?>
                                                <a href="bookings.php?action=paid&id=<?php echo $row['id']; ?>" 
                                                   class="btn-action" style="background: #28a745; color: white; padding: 5px 10px; font-size: 12px;">
                                                    <i class="fas fa-money-bill"></i> Paid
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="bookings.php?action=delete&id=<?php echo $row['id']; ?>" 
                                               class="btn-action delete" style="padding: 5px 10px; font-size: 12px;"
                                               onclick="return confirm('Hapus pemesanan ini?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="9" style="text-align: center;">Tidak ada data pemesanan</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Booking Statistics -->
            <div class="content-section">
                <div class="section-header">
                    <h2>Statistik Pemesanan</h2>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <?php
                    // Total bookings
                    $query = "SELECT COUNT(*) as total FROM bookings";
                    $result = mysqli_query($conn, $query);
                    $total = mysqli_fetch_assoc($result)['total'];
                    
                    // Total revenue
                    $query = "SELECT SUM(total_amount) as revenue FROM bookings WHERE payment_status = 'paid'";
                    $result = mysqli_query($conn, $query);
                    $revenue = mysqli_fetch_assoc($result)['revenue'] ?: 0;
                    
                    // Today's bookings
                    $today = date('Y-m-d');
                    $query = "SELECT COUNT(*) as today FROM bookings WHERE DATE(booking_date) = '$today'";
                    $result = mysqli_query($conn, $query);
                    $today_count = mysqli_fetch_assoc($result)['today'];
                    
                    // Pending payments
                    $query = "SELECT COUNT(*) as pending FROM bookings WHERE payment_status = 'pending'";
                    $result = mysqli_query($conn, $query);
                    $pending = mysqli_fetch_assoc($result)['pending'];
                    ?>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Pemesanan</h3>
                            <p class="stat-number"><?php echo $total; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Total Pendapatan</h3>
                            <p class="stat-number">Rp <?php echo number_format($revenue, 0, ',', '.'); ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Pemesanan Hari Ini</h3>
                            <p class="stat-number"><?php echo $today_count; ?></p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-info">
                            <h3>Pembayaran Tertunda</h3>
                            <p class="stat-number"><?php echo $pending; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>