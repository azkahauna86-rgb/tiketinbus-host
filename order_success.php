<?php
// die('BOOKING PHP JALAN');

include 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['booking_id'])) {
    header('Location: search.php');
    exit();
}

$booking_id = $_SESSION['booking_id'];
unset($_SESSION['booking_id']);

// Get booking details
$query = "SELECT b.*, u.full_name, u.email, u.phone,
                 r.departure_city, r.arrival_city, r.departure_time, r.arrival_time, r.price,
                 bu.bus_number, op.name as operator_name
          FROM bookings b
          JOIN users u ON b.user_id = u.id
          JOIN routes r ON b.route_id = r.id
          JOIN buses bu ON r.bus_id = bu.id
          JOIN bus_operators op ON bu.operator_id = op.id
          WHERE b.id = '$booking_id' AND b.user_id = '{$_SESSION['user_id']}'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) != 1) {
    header('Location: user/dashboard.php');
    exit();
}

$booking = mysqli_fetch_assoc($result);

include 'includes/header.php';
?>

<div class="container success-page">
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>Pemesanan Berhasil!</h1>
        <p class="success-message">
            Tiket Anda telah berhasil dipesan. Detail pemesanan telah dikirim ke email Anda.
        </p>
        
        <div class="booking-summary">
            <div class="summary-header">
                <h2>Ringkasan Pemesanan</h2>
                <div class="booking-number">
                    No. Booking: <strong><?php echo $booking['booking_number']; ?></strong>
                </div>
            </div>
            
            <div class="summary-content">
                <div class="summary-item">
                    <div class="item-label">Nama Pemesan</div>
                    <div class="item-value"><?php echo $booking['full_name']; ?></div>
                </div>
                
                <div class="summary-item">
                    <div class="item-label">Email</div>
                    <div class="item-value"><?php echo $booking['email']; ?></div>
                </div>
                
                <div class="summary-item">
                    <div class="item-label">Rute</div>
                    <div class="item-value">
                        <?php echo $booking['departure_city']; ?> → <?php echo $booking['arrival_city']; ?>
                    </div>
                </div>
                
                <div class="summary-item">
                    <div class="item-label">Tanggal & Waktu</div>
                    <div class="item-value">
                        <?php echo date('d F Y', strtotime($booking['departure_time'])); ?> • 
                        <?php echo date('H:i', strtotime($booking['departure_time'])); ?>
                    </div>
                </div>
                
                <div class="summary-item">
                    <div class="item-label">Bus</div>
                    <div class="item-value">
                        <?php echo $booking['operator_name']; ?> - <?php echo $booking['bus_number']; ?>
                    </div>
                </div>
                
                <div class="summary-item">
                    <div class="item-label">Jumlah Penumpang</div>
                    <div class="item-value"><?php echo $booking['seats_booked']; ?> orang</div>
                </div>
                
                <div class="summary-item">
                    <div class="item-label">Total Pembayaran</div>
                    <div class="item-value total-price">
                        Rp <?php echo number_format($booking['total_amount'], 0, ',', '.'); ?>
                    </div>
                </div>
                
                <div class="summary-item">
                    <div class="item-label">Status</div>
                    <div class="item-value">
                        <span class="status confirmed"><?php echo ucfirst($booking['status']); ?></span>
                    </div>
                </div>
                
                <div class="summary-item">
                    <div class="item-label">Status Pembayaran</div>
                    <div class="item-value">
                        <span class="status <?php echo $booking['payment_status']; ?>">
                            <?php echo ucfirst($booking['payment_status']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="success-actions">
            <a href="user/generate_ticket.php?id=<?php echo $booking_id; ?>" class="btn-download" target="_blank">
                <i class="fas fa-download"></i> Download Tiket
            </a>
            
            <a href="user/my-tickets.php" class="btn-view">
                <i class="fas fa-ticket-alt"></i> Lihat Tiket Saya
            </a>
            
            <a href="search.php" class="btn-book">
                <i class="fas fa-bus"></i> Pesan Tiket Lagi
            </a>
            
            <a href="user/dashboard.php" class="btn-dashboard">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </div>
        
        <div class="success-instructions">
            <h3><i class="fas fa-info-circle"></i> Instruksi Selanjutnya:</h3>
            <ol>
                <li>Download tiket Anda dan simpan baik-baik</li>
                <li>Datang ke terminal minimal 1 jam sebelum keberangkatan</li>
                <li>Tunjukkan tiket elektronik atau print tiket saat check-in</li>
                <li>Siapkan identitas diri (KTP/SIM) yang masih berlaku</li>
                <li>Lakukan pembayaran sebelum batas waktu yang ditentukan</li>
            </ol>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.success-page {
    padding: 60px 0;
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.success-card {
    background: white;
    padding: 50px;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    max-width: 800px;
    width: 100%;
    text-align: center;
    border: 1px solid #e0e0e0;
}

.success-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
    color: white;
    font-size: 48px;
}

.success-card h1 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-size: 36px;
}

.success-message {
    color: #7f8c8d;
    font-size: 18px;
    margin-bottom: 40px;
    line-height: 1.6;
}

.booking-summary {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 40px;
    text-align: left;
}

.summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
}

.summary-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 24px;
}

.booking-number {
    font-size: 18px;
    color: #3498db;
}

.summary-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.summary-item:last-child {
    border-bottom: none;
}

.item-label {
    color: #7f8c8d;
    font-weight: 500;
}

.item-value {
    color: #2c3e50;
    font-weight: 600;
    text-align: right;
}

.item-value.total-price {
    font-size: 24px;
    color: #e74c3c;
    font-weight: 700;
}

.success-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.btn-download, .btn-view, .btn-book, .btn-dashboard {
    padding: 15px 25px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 10px;
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

.btn-view {
    background: #17a2b8;
    color: white;
}

.btn-view:hover {
    background: #138496;
    transform: translateY(-2px);
}

.btn-book {
    background: #3498db;
    color: white;
}

.btn-book:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

.btn-dashboard {
    background: #6c757d;
    color: white;
}

.btn-dashboard:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.success-instructions {
    background: #e8f4fc;
    border-radius: 15px;
    padding: 25px;
    text-align: left;
}

.success-instructions h3 {
    color: #2c3e50;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.success-instructions ol {
    margin: 0;
    padding-left: 20px;
    color: #2c3e50;
}

.success-instructions li {
    margin-bottom: 10px;
    line-height: 1.6;
}

.success-instructions li:last-child {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .success-card {
        padding: 30px 20px;
    }
    
    .summary-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .summary-content {
        grid-template-columns: 1fr;
    }
    
    .success-actions {
        flex-direction: column;
    }
    
    .btn-download, .btn-view, .btn-book, .btn-dashboard {
        justify-content: center;
    }
}
</style>