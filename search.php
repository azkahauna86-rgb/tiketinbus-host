<?php
include 'includes/config.php';
include 'includes/header.php';


$from = isset($_GET['from']) ? sanitize($_GET['from']) : '';
$to = isset($_GET['to']) ? sanitize($_GET['to']) : '';
$date = isset($_GET['date']) ? sanitize($_GET['date']) : date('Y-m-d');
$passengers = isset($_GET['passengers']) ? intval($_GET['passengers']) : 1;



// Build search query
$where = "1=1";


if (!empty($from)) {
    $where .= " AND r.departure_city LIKE '%$from%'";
}
if (!empty($to)) {
    $where .= " AND r.arrival_city LIKE '%$to%'";
}


// Get available routes
$query = "SELECT r.*, b.bus_number, b.bus_type, b.total_seats, b.amenities,
                 op.name as operator_name, op.rating
          FROM routes r
          JOIN buses b ON r.bus_id = b.id
          JOIN bus_operators op ON b.operator_id = op.id
          WHERE $where AND r.available_seats >= $passengers
          ORDER BY r.departure_time, r.price";
$result = mysqli_query($conn, $query);
?>

<div class="container search-page">
    <h1>Cari & Pesan Tiket Bus</h1>
    
    <!-- Search Form -->
    <div class="search-form-container">
        <form action="search.php" method="GET" class="search-form compact">
                <div class="form-row">
                    <div class="form-group">
                        <label for="from"><i class="fas fa-map-marker-alt"></i> Dari</label>
                        <select id="from" name="from" class="form-control" required>
                            <option value="">Pilih Kota Asal</option>
                            <?php 
                            mysqli_data_seek($q_asal, 0); // Reset pointer
                            while ($row = mysqli_fetch_assoc($q_asal)) : ?>
                                <option value="<?php echo htmlspecialchars($row['departure_city']); ?>">
                                    <?php echo htmlspecialchars($row['departure_city']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group swap-container">
                        <button type="button" class="btn-swap" onclick="swapCities()" title="Tukar Kota">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                    </div>
                    
                    <div class="form-group">
                        <label for="to"><i class="fas fa-map-marker"></i> Ke</label>
                        <select id="to" name="to" class="form-control" required>
                            <option value="">Pilih Kota Tujuan</option>
                            <?php 
                            mysqli_data_seek($q_tujuan, 0); // Reset pointer
                            while ($row = mysqli_fetch_assoc($q_tujuan)) : ?>
                                <option value="<?php echo htmlspecialchars($row['arrival_city']); ?>">
                                    <?php echo htmlspecialchars($row['arrival_city']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                <div class="form-group">
                    <label for="date"><i class="fas fa-calendar-alt"></i> Tanggal</label>
                    <input type="date" id="date" name="date" value="<?php echo $date; ?>" required>
                </div>
                <div class="form-group">
                    <label for="passengers"><i class="fas fa-users"></i> Penumpang</label>
                    <select id="passengers" name="passengers">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $passengers == $i ? 'selected' : ''; ?>>
                                <?php echo $i; ?> Penumpang
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-search">
                <i class="fas fa-search"></i> CARI BUS
            </button>
        </form>
    </div>
    
    <!-- Search Results -->
    <div class="search-results">
        <div class="results-header">
            <h2>Hasil Pencarian</h2>
            <div class="search-summary">
                <p><strong>Rute:</strong> <?php echo $from; ?> → <?php echo $to; ?></p>
                <p><strong>Tanggal:</strong> <?php echo date('d F Y', strtotime($date)); ?></p>
                <p><strong>Penumpang:</strong> <?php echo $passengers; ?> orang</p>
            </div>
            
            <!-- Filters -->
            <div class="filters">
                <div class="filter-group">
                    <label>Urutkan:</label>
                    <select id="sortBy" onchange="sortResults()">
                        <option value="price_asc">Harga: Rendah ke Tinggi</option>
                        <option value="price_desc">Harga: Tinggi ke Rendah</option>
                        <option value="departure_asc">Keberangkatan: Pagi ke Malam</option>
                        <option value="departure_desc">Keberangkatan: Malam ke Pagi</option>
                        <option value="rating_desc">Rating: Tertinggi</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Tipe Bus:</label>
                    <div class="bus-type-filter">
                        <label><input type="checkbox" name="bus_type" value="AC" checked onchange="filterResults()"> AC</label>
                        <label><input type="checkbox" name="bus_type" value="Non-AC" checked onchange="filterResults()"> Non-AC</label>
                        <label><input type="checkbox" name="bus_type" value="Sleeper" checked onchange="filterResults()"> Sleeper</label>
                        <label><input type="checkbox" name="bus_type" value="Seater" checked onchange="filterResults()"> Seater</label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bus List -->
        <div class="bus-list">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($route = mysqli_fetch_assoc($result)) {
                    $departure_time = date('H:i', strtotime($route['departure_time']));
                    $arrival_time = date('H:i', strtotime($route['arrival_time']));
                    $departure_date = date('d M Y', strtotime($route['departure_time']));
                    $amenities = explode(',', $route['amenities']);
                    ?>
                    <div class="bus-card" data-price="<?php echo $route['price']; ?>" 
                         data-departure="<?php echo strtotime($route['departure_time']); ?>"
                         data-rating="<?php echo $route['rating']; ?>"
                         data-type="<?php echo $route['bus_type']; ?>">
                        <div class="bus-header">
                            <div class="bus-operator">
                                <h3><?php echo $route['operator_name']; ?></h3>
                                <div class="bus-info">
                                    <span class="bus-type"><?php echo $route['bus_type']; ?></span>
                                    <span class="bus-number"><?php echo $route['bus_number']; ?></span>
                                    <div class="rating">
                                        <i class="fas fa-star"></i> <?php echo $route['rating']; ?>/5
                                    </div>
                                </div>
                            </div>
                            <div class="price-section">
                                <div class="price">Rp <?php echo number_format($route['price'], 0, ',', '.'); ?></div>
                                <div class="per-person">per orang</div>
                                <div class="seats-available">
                                    <i class="fas fa-chair"></i> <?php echo $route['available_seats']; ?> kursi tersedia
                                </div>
                            </div>
                        </div>
                        
                        <div class="bus-schedule">
                            <div class="departure">
                                <div class="time"><?php echo $departure_time; ?></div>
                                <div class="city"><?php echo $route['departure_city']; ?></div>
                                <div class="date"><?php echo $departure_date; ?></div>
                            </div>
                            
                            <div class="journey">
                                <div class="duration"><?php echo $route['duration']; ?></div>
                                <div class="distance"><?php echo $route['distance']; ?> km</div>
                                <div class="line">
                                    <div class="dot start"></div>
                                    <div class="line-between"></div>
                                    <div class="dot end"></div>
                                </div>
                            </div>
                            
                            <div class="arrival">
                                <div class="time"><?php echo $arrival_time; ?></div>
                                <div class="city"><?php echo $route['arrival_city']; ?></div>
                                <div class="date"><?php echo date('d M Y', strtotime($route['arrival_time'])); ?></div>
                            </div>
                        </div>
                        
                        <div class="bus-amenities">
                            <h4><i class="fas fa-concierge-bell"></i> Fasilitas:</h4>
                            <div class="amenities-list">
                                <?php foreach ($amenities as $amenity): ?>
                                    <span><i class="fas fa-check"></i> <?php echo trim($amenity); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="bus-actions">
                            <div class="total-price">
                                <strong>Total untuk <?php echo $passengers; ?> penumpang:</strong>
                                <div class="amount">Rp <?php echo number_format($route['price'] * $passengers, 0, ',', '.'); ?></div>
                            </div>
                            <?php if (isLoggedIn()): ?>
                                <a href="order.php?route_id=<?= $route['id'] ?>&passengers=<?= $passengers ?>"
                                    class="btn-select">
                                    <i class="fas fa-ticket-alt"></i> Pilih & Pesan
                                </a>
                            <?php else: ?>
                                <a href="login.php?redirect=booking&route_id=<?php echo $route['id']; ?>&passengers=<?php echo $passengers; ?>" 
                                   class="btn-select">
                                    <i class="fas fa-ticket-alt"></i> Pilih & Login
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '
                <div class="no-results">
                    <i class="fas fa-bus-slash"></i>
                    <h3>Tidak ada bus tersedia</h3>
                    <p>Maaf, tidak ada bus yang tersedia untuk rute dan tanggal yang dipilih.</p>
                    <p>Coba cari dengan tanggal lain atau rute yang berbeda.</p>
                    <a href="index.php" class="btn-search">
                        <i class="fas fa-search"></i> Cari Lagi
                    </a>
                </div>';
            }
            ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.search-page {
    padding: 40px 0;
}

.search-form-container {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    margin-bottom: 40px;
    border: 1px solid #e0e0e0;
}

.search-form.compact .form-row {
    gap: 15px;
}

.search-results {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid #e0e0e0;
}

.results-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f8f9fa;
}

.search-summary {
    display: flex;
    gap: 30px;
    margin: 15px 0;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    flex-wrap: wrap;
}

.filters {
    display: flex;
    gap: 30px;
    margin-top: 20px;
    align-items: center;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.filter-group select {
    padding: 8px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    background: white;
}

.bus-type-filter {
    display: flex;
    gap: 15px;
}

.bus-type-filter label {
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}

.bus-list {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.bus-card {
    border: 2px solid #e0e0e0;
    border-radius: 15px;
    padding: 25px;
    transition: all 0.3s ease;
}

.bus-card:hover {
    border-color: #3498db;
    box-shadow: 0 10px 30px rgba(52, 152, 219, 0.15);
    transform: translateY(-3px);
}

.bus-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f8f9fa;
}

.bus-operator h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 22px;
}

.bus-info {
    display: flex;
    gap: 15px;
    align-items: center;
    color: #666;
}

.bus-type, .bus-number {
    background: #e9ecef;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.rating {
    color: #f39c12;
    font-weight: 600;
}

.price-section {
    text-align: right;
}

.price {
    font-size: 32px;
    font-weight: 700;
    color: #e74c3c;
    line-height: 1;
}

.per-person {
    color: #7f8c8d;
    font-size: 14px;
    margin: 5px 0;
}

.seats-available {
    color: #2ecc71;
    font-weight: 600;
}

.bus-schedule {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 30px;
    align-items: center;
    margin-bottom: 25px;
    padding: 25px;
    background: #f8f9fa;
    border-radius: 10px;
}

.departure, .arrival {
    text-align: center;
}

.departure .time, .arrival .time {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 5px;
}

.departure .city, .arrival .city {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
}

.departure .date, .arrival .date {
    color: #7f8c8d;
    font-size: 14px;
}

.journey {
    text-align: center;
    position: relative;
}

.duration {
    font-size: 16px;
    font-weight: 600;
    color: #3498db;
    margin-bottom: 5px;
}

.distance {
    color: #7f8c8d;
    font-size: 14px;
}

.line {
    position: relative;
    height: 3px;
    background: #3498db;
    margin: 20px 0;
}

.dot {
    position: absolute;
    width: 12px;
    height: 12px;
    background: #3498db;
    border-radius: 50%;
    top: -5px;
}

.dot.start {
    left: 0;
}

.dot.end {
    right: 0;
}

.bus-amenities {
    margin-bottom: 25px;
}

.bus-amenities h4 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 18px;
}

.amenities-list {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.amenities-list span {
    background: white;
    padding: 8px 15px;
    border-radius: 20px;
    border: 2px solid #e0e0e0;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.amenities-list span i {
    color: #2ecc71;
}

.bus-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 2px solid #f8f9fa;
}

.total-price {
    text-align: right;
}

.total-price strong {
    display: block;
    color: #7f8c8d;
    font-size: 14px;
}

.total-price .amount {
    font-size: 28px;
    font-weight: 700;
    color: #e74c3c;
}

.btn-select {
    background: #2ecc71;
    color: white;
    padding: 15px 30px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
}

.btn-select:hover {
    background: #27ae60;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
    color: white;
}

.no-results {
    text-align: center;
    padding: 60px 20px;
}

.no-results i {
    font-size: 80px;
    color: #bdc3c7;
    margin-bottom: 20px;
}

.no-results h3 {
    font-size: 28px;
    color: #2c3e50;
    margin-bottom: 15px;
}

.no-results p {
    color: #7f8c8d;
    margin-bottom: 10px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}
</style>

<script>
function sortResults() {
    const sortBy = document.getElementById('sortBy').value;
    const busCards = document.querySelectorAll('.bus-card');
    const cardsArray = Array.from(busCards);
    
    cardsArray.sort((a, b) => {
        if (sortBy === 'price_asc') {
            return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
        } else if (sortBy === 'price_desc') {
            return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
        } else if (sortBy === 'departure_asc') {
            return parseInt(a.dataset.departure) - parseInt(b.dataset.departure);
        } else if (sortBy === 'departure_desc') {
            return parseInt(b.dataset.departure) - parseInt(a.dataset.departure);
        } else if (sortBy === 'rating_desc') {
            return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
        }
        return 0;
    });
    
    const busList = document.querySelector('.bus-list');
    busList.innerHTML = '';
    cardsArray.forEach(card => busList.appendChild(card));
}

function filterResults() {
    const checkedTypes = Array.from(document.querySelectorAll('input[name="bus_type"]:checked'))
        .map(cb => cb.value);
    
    const busCards = document.querySelectorAll('.bus-card');
    
    busCards.forEach(card => {
        const busType = card.dataset.type;
        if (checkedTypes.includes(busType)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>