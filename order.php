<?php
include 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php?redirect=order');
    exit();
}

$route_id = isset($_GET['route_id']) ? intval($_GET['route_id']) : 0;
$passengers = isset($_GET['passengers']) ? intval($_GET['passengers']) : 1;

if ($route_id == 0) {
    header('Location: search.php');
    exit();
}

// Get route and bus details
$query = "SELECT r.*, b.bus_number, b.bus_type, b.total_seats, b.amenities,
                 op.name as operator_name, op.rating
          FROM routes r
          JOIN buses b ON r.bus_id = b.id
          JOIN bus_operators op ON b.operator_id = op.id
          WHERE r.id = '$route_id'";
$result = mysqli_query($conn, $query);
$route = mysqli_fetch_assoc($result);

// Get already booked seats for this route
$booked_seats_query = "SELECT p.seat_number 
                      FROM passengers p
                      JOIN bookings b ON p.booking_id = b.id
                      WHERE b.route_id = '$route_id' 
                      AND b.status != 'cancelled'";
$booked_seats_result = mysqli_query($conn, $booked_seats_query);
$booked_seats = [];
while ($row = mysqli_fetch_assoc($booked_seats_result)) {
    $booked_seats[] = $row['seat_number'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
$selected_seats = [];

for ($i = 1; $i <= $passengers; $i++) {
    if (!empty($_POST['seat_'.$i])) {
        $selected_seats[] = $_POST['seat_'.$i];
    }
}

if (count($selected_seats) == $passengers) {

    $_SESSION['order_data'] = [
        'route_id' => $route_id,
        'passengers' => $passengers,
        'selected_seats' => $selected_seats,
        'passenger_data' => []
    ];

    for ($i = 1; $i <= $passengers; $i++) {
        $_SESSION['order_data']['passenger_data'][] = [
            'name' => $_POST['passenger_name_'.$i],
            'age' => $_POST['passenger_age_'.$i],
            'gender' => $_POST['passenger_gender_'.$i],
            'seat_number' => $_POST['seat_'.$i]
        ];
    }

    header('Location: order_submit.php');
    exit();
} else {
    $error = "Harap pilih semua $passengers kursi";
}
}

include 'includes/header.php';
?>

<div class="container order-page">
    <div class="order-steps">
        <div class="step active">
            <div class="step-number">1</div>
            <div class="step-title">Pilih Kursi</div>
        </div>
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-title">Data Penumpang</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-title">Konfirmasi</div>
        </div>
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-title">Selesai</div>
        </div>
    </div>

    <div class="order-container">
        <!-- Left: Route Info & Seat Selection -->
        <div class="order-left">
            <div class="route-info-card">
                <h2>Detail Perjalanan</h2>
                <div class="route-details">
                    <div class="cities">
                        <h3><?php echo $route['departure_city']; ?> → <?php echo $route['arrival_city']; ?></h3>
                        <div class="route-meta">
                            <span><i class="fas fa-calendar"></i> <?php echo date('d F Y', strtotime($route['departure_time'])); ?></span>
                            <span><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($route['departure_time'])); ?> - <?php echo date('H:i', strtotime($route['arrival_time'])); ?></span>
                            <span><i class="fas fa-bus"></i> <?php echo $route['operator_name']; ?></span>
                        </div>
                    </div>
                    <div class="price-info">
                        <div class="price">Rp <?php echo number_format($route['price'], 0, ',', '.'); ?></div>
                        <div class="per-person">per orang</div>
                    </div>
                </div>
            </div>

            <div class="seat-selection-card">
                <h2><i class="fas fa-chair"></i> Pilih Tempat Duduk</h2>
                <p class="subtitle">Pilih <?php echo $passengers; ?> kursi (<?php echo $route['available_seats']; ?> kursi tersedia)</p>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="bus-layout">
                    <!-- Driver Area -->
                    <div class="driver-area">
                        <div class="driver-seat">
                            <i class="fas fa-user-tie"></i>
                            <span>Supir</span>
                        </div>
                        <div class="steering">
                            <i class="fas fa-car"></i>
                        </div>
                    </div>
                    
                    <!-- Seats Grid -->
                    <div class="seats-container">
                        <div class="seats-grid" id="seatsGrid">
                            <?php
                            $total_seats = $route['total_seats'];
                            $seats_per_row = 4;
                            $rows = ceil($total_seats / $seats_per_row);
                            
                            for ($row = 1; $row <= $rows; $row++) {
                                echo '<div class="seat-row">';
                                echo '<div class="row-number">' . $row . '</div>';
                                
                                for ($col = 1; $col <= $seats_per_row; $col++) {
                                    $seat_number = ($row - 1) * $seats_per_row + $col;
                                    
                                    if ($seat_number <= $total_seats) {
                                        $seat_label = str_pad($seat_number, 2, '0', STR_PAD_LEFT);
                                        $is_booked = in_array($seat_number, $booked_seats);
                                        $seat_class = $is_booked ? 'booked' : 'available';
                                        
                                        echo '<div class="seat ' . $seat_class . '" 
                                               data-seat="' . $seat_number . '">';
                                        
                                        if (!$is_booked) {
                                            echo '<input type="checkbox" 
                                                   name="selected_seats[]" 
                                                   value="' . $seat_number . '" 
                                                   id="seat_' . $seat_number . '" 
                                                   class="seat-checkbox">';
                                        }
                                        
                                        echo '<span class="seat-number">' . $seat_label . '</span>';
                                        echo '</div>';
                                        
                                        if ($col == 2) {
                                            echo '<div class="aisle-gap"></div>';
                                        }
                                    }
                                }
                                echo '</div>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Seat Legend -->
                    <div class="seat-legend">
                        <div class="legend-item">
                            <div class="seat-icon available"></div>
                            <span>Tersedia</span>
                        </div>
                        <div class="legend-item">
                            <div class="seat-icon selected"></div>
                            <span>Dipilih</span>
                        </div>
                        <div class="legend-item">
                            <div class="seat-icon booked"></div>
                            <span>Tidak Tersedia</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Passenger Form & Summary -->
        <div class="order-right">
            <form method="POST" action="order_success.php" id="orderForm">
                <div class="passenger-form-card">
                    <h2><i class="fas fa-users"></i> Data Penumpang</h2>
                    
                    <?php for ($i = 1; $i <= $passengers; $i++): ?>
                    <div class="passenger-form-section">
                        <h3>Penumpang <?php echo $i; ?> <span class="seat-assignment" id="seatAssignment<?php echo $i; ?>">(Kursi: <span class="seat-value">Belum dipilih</span>)</span></h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="passenger_name_<?php echo $i; ?>">Nama Lengkap *</label>
                                <input type="text" id="passenger_name_<?php echo $i; ?>" 
                                       name="passenger_name_<?php echo $i; ?>" required
                                       placeholder="Nama sesuai KTP">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="passenger_age_<?php echo $i; ?>">Usia *</label>
                                <input type="number" id="passenger_age_<?php echo $i; ?>" 
                                       name="passenger_age_<?php echo $i; ?>" min="1" max="120" required
                                       placeholder="Usia penumpang">
                            </div>
                            
                            <div class="form-group">
                                <label for="passenger_gender_<?php echo $i; ?>">Jenis Kelamin *</label>
                                <select id="passenger_gender_<?php echo $i; ?>" 
                                        name="passenger_gender_<?php echo $i; ?>" required>
                                    <option value="">Pilih</option>
                                    <option value="Male">Laki-laki</option>
                                    <option value="Female">Perempuan</option>
                                </select>
                            </div>
                            
                            <input type="hidden" name="seat_<?php echo $i; ?>" id="seatInput<?php echo $i; ?>">
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
                
                <div class="summary-card">
                    <h2>Ringkasan Pemesanan</h2>
                    
                    <div class="summary-details">
                        <div class="summary-item">
                            <span>Harga per orang:</span>
                            <span>Rp <?php echo number_format($route['price'], 0, ',', '.'); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Jumlah penumpang:</span>
                            <span><?php echo $passengers; ?> orang</span>
                        </div>
                        <div class="summary-item">
                            <span>Kursi terpilih:</span>
                            <span id="selectedSeatsCount">0 kursi</span>
                        </div>
                        <div class="summary-item total">
                            <strong>Total Pembayaran:</strong>
                            <strong id="totalPrice">Rp 0</strong>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="search.php" class="btn-back">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn-submit" id="submitBtn" disabled>
                            <i class="fas fa-check-circle"></i> Lanjutkan ke Pembayaran
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.order-page {
    padding: 30px 0;
}

.order-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 40px;
    max-width: 800px;
    margin-left: auto;
    margin-right: auto;
}

.step {
    text-align: center;
    flex: 1;
    position: relative;
}

.step-number {
    width: 40px;
    height: 40px;
    background: #e0e0e0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    margin: 0 auto 10px;
}

.step.active .step-number {
    background: #3498db;
}

.step-title {
    font-size: 14px;
    color: #7f8c8d;
}

.order-container {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 30px;
}

@media (max-width: 1200px) {
    .order-container {
        grid-template-columns: 1fr;
    }
}

.order-left, .order-right {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.route-info-card, .seat-selection-card, .passenger-form-card, .summary-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid #e0e0e0;
}

.route-info-card h2, .seat-selection-card h2, .passenger-form-card h2, .summary-card h2 {
    margin-top: 0;
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
}

.route-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.cities h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 22px;
}

.route-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.route-meta span {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #7f8c8d;
    font-size: 14px;
}

.price-info {
    text-align: right;
}

.price {
    font-size: 28px;
    font-weight: bold;
    color: #e74c3c;
}

.per-person {
    color: #7f8c8d;
    font-size: 14px;
}

.subtitle {
    color: #7f8c8d;
    margin-bottom: 20px;
}

/* Seat Selection */
.bus-layout {
    margin-top: 20px;
}

.driver-area {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #2c3e50;
    border-radius: 10px;
    color: white;
    margin-bottom: 20px;
}

.driver-seat, .steering {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
}

.seats-container {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    overflow-x: auto;
}

.seats-grid {
    display: flex;
    flex-direction: column;
    gap: 15px;
    min-width: 500px;
}

.seat-row {
    display: flex;
    align-items: center;
    gap: 15px;
}

.row-number {
    width: 30px;
    height: 30px;
    background: #3498db;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.seat {
    width: 45px;
    height: 45px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.seat.available {
    background: #28a745;
    border: 2px solid #218838;
}

.seat.available:hover {
    background: #218838;
    transform: scale(1.05);
}

.seat.selected {
    background: #3498db;
    border-color: #2980b9;
    transform: scale(1.05);
}

.seat.booked {
    background: #dc3545;
    border-color: #c82333;
    cursor: not-allowed;
    opacity: 0.7;
}

.seat-number {
    color: white;
    font-weight: bold;
    font-size: 14px;
}

.seat-checkbox {
    display: none;
}

.aisle-gap {
    width: 40px;
    flex-shrink: 0;
}

.seat-legend {
    display: flex;
    justify-content: center;
    gap: 30px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.seat-icon {
    width: 20px;
    height: 20px;
    border-radius: 4px;
}

.seat-icon.available {
    background: #28a745;
    border: 2px solid #218838;
}

.seat-icon.selected {
    background: #3498db;
    border: 2px solid #2980b9;
}

.seat-icon.booked {
    background: #dc3545;
    border: 2px solid #c82333;
}

/* Passenger Form */
.passenger-form-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

.passenger-form-section h3 {
    margin-top: 0;
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 18px;
}

.seat-assignment {
    font-size: 14px;
    color: #7f8c8d;
    font-weight: normal;
}

.seat-assignment .seat-value {
    color: #3498db;
    font-weight: bold;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 8px;
    font-weight: 500;
    color: #2c3e50;
    font-size: 14px;
}

.form-group input,
.form-group select {
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
}

/* Summary */
.summary-details {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 25px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.summary-item.total {
    border-bottom: none;
    border-top: 2px solid #f0f0f0;
    padding-top: 15px;
    margin-top: 5px;
}

#totalPrice {
    font-size: 24px;
    color: #e74c3c;
}

.form-actions {
    display: flex;
    gap: 15px;
}

.btn-back {
    flex: 1;
    padding: 15px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 10px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.btn-submit {
    flex: 2;
    padding: 15px;
    background: #2ecc71;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-submit:hover:not(:disabled) {
    background: #27ae60;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
}

.btn-submit:disabled {
    background: #95a5a6;
    cursor: not-allowed;
    transform: none;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #f5c6cb;
}
</style>

<script>
selectedSeats = [];
const maxSeats = <?php echo $passengers; ?>;
const seatPrice = <?php echo $route['price']; ?>;

document.addEventListener('DOMContentLoaded', function() {
    initializeSeatSelection();
    updateSummary();
});

function initializeSeatSelection() {
    // Add click event to available seats
    document.querySelectorAll('.seat.available').forEach(seat => {
        seat.addEventListener('click', function() {
            const seatNumber = parseInt(this.dataset.seat);
            toggleSeatSelection(seatNumber, this);
        });
    });
}

function toggleSeatSelection(seatNumber, seatElement) {
    const seatIndex = selectedSeats.indexOf(seatNumber);
    const seatCheckbox = seatElement.querySelector('.seat-checkbox');
    
    if (seatIndex === -1) {
        // Select seat
        if (selectedSeats.length < maxSeats) {
            selectedSeats.push(seatNumber);
            seatElement.classList.add('selected');
            if (seatCheckbox) seatCheckbox.checked = true;
            
            // Update passenger seat assignment
            updatePassengerSeatAssignment();
            updateSummary();
        } else {
            alert(`Maksimal ${maxSeats} kursi yang dapat dipilih`);
        }
    } else {
        // Deselect seat
        selectedSeats.splice(seatIndex, 1);
        seatElement.classList.remove('selected');
        if (seatCheckbox) seatCheckbox.checked = false;
        
        updatePassengerSeatAssignment();
        updateSummary();
    }
}

function updatePassengerSeatAssignment() {
    for (let i = 1; i <= maxSeats; i++) {
        const seatAssignment = document.getElementById(`seatAssignment${i}`);
        const seatInput = document.getElementById(`seatInput${i}`);
        
        if (seatAssignment && i <= selectedSeats.length) {
            const seatNumber = selectedSeats[i-1];
            seatAssignment.querySelector('.seat-value').textContent = 'Kursi ' + String(seatNumber).padStart(2, '0');
            
            if (seatInput) {
                seatInput.value = seatNumber;
            }
        } else if (seatAssignment) {
            seatAssignment.querySelector('.seat-value').textContent = 'Belum dipilih';
            
            if (seatInput) {
                seatInput.value = '';
            }
        }
    }
}

function updateSummary() {
    // Update selected seats count
    document.getElementById('selectedSeatsCount').textContent = 
        selectedSeats.length + ' kursi';
    
    // Update total price
    const total = seatPrice * maxSeats;
    document.getElementById('totalPrice').textContent = 
        'Rp ' + total.toLocaleString('id-ID');
    
    // Enable/disable submit button
    const submitBtn = document.getElementById('submitBtn');
    if (selectedSeats.length === maxSeats) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Lanjutkan ke Pembayaran';
    } else {
        submitBtn.disabled = true;
        const remaining = maxSeats - selectedSeats.length;
        submitBtn.innerHTML = `<i class="fas fa-exclamation-circle"></i> Pilih ${remaining} kursi lagi`;
    }
}

// Form validation
document.getElementById('orderForm').addEventListener('submit', function(e) {
    // Check all seats are selected
    if (selectedSeats.length !== maxSeats) {
        e.preventDefault();
        alert(`Harap pilih semua ${maxSeats} kursi terlebih dahulu`);
        return false;
    }
    
    // Check all passenger fields are filled
    let allFilled = true;
    for (let i = 1; i <= maxSeats; i++) {
        const nameField = document.getElementById(`passenger_name_${i}`);
        const ageField = document.getElementById(`passenger_age_${i}`);
        const genderField = document.getElementById(`passenger_gender_${i}`);
        
        if (!nameField.value || !ageField.value || !genderField.value) {
            allFilled = false;
            break;
        }
    }
    
    if (!allFilled) {
        e.preventDefault();
        alert('Harap isi semua data penumpang dengan lengkap');
        return false;
    }
    
    return true;
});
</script>