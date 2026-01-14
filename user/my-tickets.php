<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Filter
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$where = "b.user_id = '$user_id'";
if ($status_filter) {
    $where .= " AND b.status = '$status_filter'";
}

// Get user bookings with filter
$query = "SELECT b.*, r.departure_city, r.arrival_city, r.departure_time, r.arrival_time, r.price,
                 bu.bus_number, bu.bus_type, op.name as operator_name, op.rating
          FROM bookings b
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
    <title>Tiket Saya - BusTicket</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #e0e0e0;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        .filter-select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            background: white;
        }
        
        .filter-btn {
            padding: 12px 25px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-btn:hover {
            background: #2980b9;
        }
        
        .ticket-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
            display: none;
        }
        
        .ticket-details.show {
            display: block;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .detail-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }
        
        .detail-item h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-item p {
            margin: 0;
            color: #333;
            font-size: 16px;
        }
        
        .passengers-list {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .passenger-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .passenger-item:last-child {
            border-bottom: none;
        }
        
        .no-tickets {
            text-align: center;
            padding: 60px 20px;
        }
        
        .no-tickets i {
            font-size: 80px;
            color: #bdc3c7;
            margin-bottom: 20px;
        }
        
        .no-tickets h3 {
            font-size: 24px;
            color: #7f8c8d;
            margin-bottom: 15px;
        }
        
        .no-tickets p {
            color: #95a5a6;
            margin-bottom: 30px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .ticket-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn-download, .btn-cancel, .btn-details {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-download {
            background: #28a745;
            color: white;
        }
        
        .btn-download:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            background: #dc3545;
            color: white;
        }
        
        .btn-cancel:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-details {
            background: #17a2b8;
            color: white;
        }
        
        .btn-details:hover {
            background: #138496;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="user-container">
        <?php include 'sidebar.php'; ?>

        <div class="user-main">
            <h1>Tiket Saya</h1>
            <p class="welcome-message">
                <i class="fas fa-ticket-alt"></i> Lihat dan kelola semua tiket Anda
            </p>
            
            <!-- Filters -->
            <div class="filters">
                <form method="GET" action="" class="filter-form">
                    <div class="filter-group">
                        <label for="status"><i class="fas fa-filter"></i> Filter Status</label>
                        <select name="status" id="status" class="filter-select">
                            <option value="">Semua Status</option>
                            <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="filter-btn">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    
                    <a href="my-tickets.php" class="btn-details">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </form>
            </div>
            
            <!-- Tickets List -->
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="ticket-list">
                    <?php while ($booking = mysqli_fetch_assoc($result)): 
                        $status_class = strtolower($booking['status']);
                        $payment_class = strtolower($booking['payment_status']);
                        $departure_date = date('d M Y', strtotime($booking['departure_time']));
                        $departure_time = date('H:i', strtotime($booking['departure_time']));
                        $arrival_time = date('H:i', strtotime($booking['arrival_time']));
                    ?>
                        <div class="ticket-card">
                            <div class="ticket-header">
                                <div>
                                    <span class="ticket-number">
                                        <i class="fas fa-ticket-alt"></i> <?php echo $booking['booking_number']; ?>
                                    </span>
                                    <span style="margin-left: 15px; color: #7f8c8d;">
                                        <i class="fas fa-calendar"></i> <?php echo $departure_date; ?>
                                    </span>
                                </div>
                                <div>
                                    <span class="ticket-status <?php echo $status_class; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                    <span class="ticket-status <?php echo $payment_class; ?>" style="margin-left: 10px;">
                                        <?php echo ucfirst($booking['payment_status']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="ticket-body">
                                <div class="route-info">
                                    <h3><?php echo $booking['departure_city']; ?> → <?php echo $booking['arrival_city']; ?></h3>
                                    <p>
                                        <i class="fas fa-clock"></i> <?php echo $departure_time; ?> - <?php echo $arrival_time; ?>
                                        &nbsp;&nbsp;|&nbsp;&nbsp;
                                        <i class="fas fa-bus"></i> <?php echo $booking['operator_name']; ?> 
                                        (<?php echo $booking['bus_number']; ?> - <?php echo $booking['bus_type']; ?>)
                                        &nbsp;&nbsp;|&nbsp;&nbsp;
                                        <i class="fas fa-star"></i> <?php echo $booking['rating']; ?>/5
                                    </p>
                                </div>
                                
                                <div class="ticket-price">
                                    <p><?php echo $booking['seats_booked']; ?> kursi × Rp <?php echo number_format($booking['price'], 0, ',', '.'); ?></p>
                                    <p class="total-price">Total: Rp <?php echo number_format($booking['total_amount'], 0, ',', '.'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Ticket Actions -->
                            <div class="ticket-actions">
                                <?php if ($booking['status'] == 'confirmed'): ?>
                                    <a href="generate_ticket.php?id=<?php echo $booking['id']; ?>" class="btn-download" target="_blank">
                                        <i class="fas fa-download"></i> Download Tiket
                                    </a>
                                    <a href="print_ticket.php?id=<?php echo $booking['id']; ?>" class="btn-details" target="_blank">
                                        <i class="fas fa-print"></i> Cetak Tiket
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($booking['status'] != 'cancelled'): ?>
                                    <a href="cancel.php?id=<?php echo $booking['id']; ?>" class="btn-cancel" 
                                       onclick="return confirm('Batalkan tiket ini?')">
                                        <i class="fas fa-times"></i> Batalkan
                                    </a>
                                <?php endif; ?>
                                
                                <button class="btn-details" onclick="toggleDetails(<?php echo $booking['id']; ?>)">
                                    <i class="fas fa-info-circle"></i> Detail
                                </button>
                            </div>
                            
                            <!-- Ticket Details (Hidden by default) -->
                            <div id="details-<?php echo $booking['id']; ?>" class="ticket-details">
                                <div class="details-grid">
                                    <div class="detail-item">
                                        <h4>No. Booking</h4>
                                        <p><?php echo $booking['booking_number']; ?></p>
                                    </div>
                                    <div class="detail-item">
                                        <h4>Tanggal Pemesanan</h4>
                                        <p><?php echo date('d M Y H:i', strtotime($booking['booking_date'])); ?></p>
                                    </div>
                                    <div class="detail-item">
                                        <h4>Status</h4>
                                        <p><span class="status <?php echo $status_class; ?>"><?php echo ucfirst($booking['status']); ?></span></p>
                                    </div>
                                    <div class="detail-item">
                                        <h4>Pembayaran</h4>
                                        <p><span class="status <?php echo $payment_class; ?>"><?php echo ucfirst($booking['payment_status']); ?></span></p>
                                    </div>
                                    <div class="detail-item">
                                        <h4>Bus</h4>
                                        <p><?php echo $booking['bus_number']; ?> (<?php echo $booking['bus_type']; ?>)</p>
                                    </div>
                                    <div class="detail-item">
                                        <h4>Operator</h4>
                                        <p><?php echo $booking['operator_name']; ?> ⭐ <?php echo $booking['rating']; ?></p>
                                    </div>
                                    <div class="detail-item">
                                        <h4>Jadwal</h4>
                                        <p><?php echo $departure_date; ?> <?php echo $departure_time; ?> - <?php echo $arrival_time; ?></p>
                                    </div>
                                    <div class="detail-item">
                                        <h4>Jumlah Penumpang</h4>
                                        <p><?php echo $booking['seats_booked']; ?> orang</p>
                                    </div>
                                </div>
                                
                                <!-- Get passengers for this booking -->
                                <?php
                                $passengers_query = "SELECT * FROM passengers WHERE booking_id = '{$booking['id']}'";
                                $passengers_result = mysqli_query($conn, $passengers_query);
                                
                                if (mysqli_num_rows($passengers_result) > 0): ?>
                                    <div class="passengers-list">
                                        <h4 style="margin-bottom: 15px;"><i class="fas fa-users"></i> Daftar Penumpang</h4>
                                        <?php while ($passenger = mysqli_fetch_assoc($passengers_result)): ?>
                                            <div class="passenger-item">
                                                <div>
                                                    <strong><?php echo $passenger['name']; ?></strong>
                                                    <div style="color: #666; font-size: 14px;">
                                                        <?php echo $passenger['age']; ?> tahun | 
                                                        <?php echo $passenger['gender']; ?> | 
                                                        Kursi: <?php echo $passenger['seat_number']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-tickets">
                    <i class="fas fa-ticket-alt"></i>
                    <h3>Tidak ada tiket</h3>
                    <p><?php echo $status_filter ? "Tidak ada tiket dengan status '{$status_filter}'" : "Anda belum memiliki tiket."; ?></p>
                    <a href="../search.php" class="btn-book">
                        <i class="fas fa-bus"></i> Pesan Tiket Sekarang
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    
    <script>
    function toggleDetails(bookingId) {
        const detailsDiv = document.getElementById('details-' + bookingId);
        detailsDiv.classList.toggle('show');
    }
    
    function printTicket(bookingId) {
        window.open('print_ticket.php?id=' + bookingId, '_blank');
    }
    </script>
</body>
</html>