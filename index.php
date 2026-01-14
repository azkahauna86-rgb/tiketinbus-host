<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'includes/config.php';
include 'includes/header.php';

/* QUERY KOTA ASAL */
$q_asal = mysqli_query($conn, "
    SELECT DISTINCT departure_city 
    FROM routes 
    ORDER BY departure_city ASC
");

/* QUERY KOTA TUJUAN */
$q_tujuan = mysqli_query($conn, "
    SELECT DISTINCT arrival_city 
    FROM routes 
    ORDER BY arrival_city ASC
");

if (!$q_asal || !$q_tujuan) {
    die("Error: " . mysqli_error($conn));
}
?>

<div class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1>Pesan Tiket Bus Online</h1>
            <p>Perjalanan nyaman ke berbagai kota di Indonesia dengan harga terjangkau</p>
            
            <form action="search.php" method="GET" class="search-form">
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
                        <input type="date" id="date" name="date" class="form-control" 
                               value="<?php echo date('Y-m-d'); ?>" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="passengers"><i class="fas fa-users"></i> Penumpang</label>
                        <select id="passengers" name="passengers" class="form-control">
                            <option value="1">1 Penumpang</option>
                            <option value="2" selected>2 Penumpang</option>
                            <option value="3">3 Penumpang</option>
                            <option value="4">4 Penumpang</option>
                            <option value="5">5 Penumpang</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i> CARI BUS
                </button>
            </form>
        </div>
    </div>
</div>

<div class="features-section">
    <div class="container">
        <h2>Mengapa Memilih Kami?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-bus"></i>
                </div>
                <h3>100+ Bus</h3>
                <p>Lebih dari 100 bus dari berbagai operator terpercaya</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Aman & Terpercaya</h3>
                <p>Transaksi aman dengan garansi uang kembali</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <h3>24/7 Support</h3>
                <p>Layanan pelanggan siap membantu kapan saja</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <h3>Harga Terbaik</h3>
                <p>Harga kompetitif dengan promo menarik</p>
            </div>
        </div>
    </div>
</div>

<div class="popular-routes">
    <div class="container">
        <h2>Rute Populer</h2>
        <div class="routes-grid">
            <?php
            $popular_routes = [
                ['Jakarta', 'Bandung', 'Rp 150.000', '4 jam'],
                ['Jakarta', 'Surabaya', 'Rp 350.000', '12 jam'],
                ['Surabaya', 'Malang', 'Rp 80.000', '3 jam'],
                ['Jakarta', 'Yogyakarta', 'Rp 250.000', '9 jam'],
                ['Jakarta', 'Semarang', 'Rp 200.000', '8 jam'],
                ['Bandung', 'Surabaya', 'Rp 300.000', '12 jam']
            ];
            
            foreach ($popular_routes as $route) {
                echo '
                <div class="route-card">
                    <div class="route-header">
                        <h3>' . htmlspecialchars($route[0]) . ' → ' . htmlspecialchars($route[1]) . '</h3>
                        <span class="duration">' . htmlspecialchars($route[3]) . '</span>
                    </div>
                    <div class="route-price">' . htmlspecialchars($route[2]) . '</div>
                    <a href="search.php?from=' . urlencode($route[0]) . '&to=' . urlencode($route[1]) . '" class="btn-book">
                        <i class="fas fa-ticket-alt"></i> Pesan Sekarang
                    </a>
                </div>';
            }
            ?>
        </div>
    </div>
</div>

<div class="how-it-works">
    <div class="container">
        <h2>Cara Memesan</h2>
        <div class="steps-grid">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Cari Bus</h3>
                <p>Tentukan rute dan tanggal perjalanan</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Pilih Kursi</h3>
                <p>Pilih bus dan tempat duduk favorit Anda</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Bayar</h3>
                <p>Lakukan pembayaran dengan metode pilihan</p>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <h3>Tiket Anda</h3>
                <p>Terima tiket elektronik via email</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Function to swap cities
function swapCities() {
    const fromSelect = document.getElementById('from');
    const toSelect = document.getElementById('to');
    const tempValue = fromSelect.value;
    const tempText = fromSelect.options[fromSelect.selectedIndex].text;
    
    // Swap values
    fromSelect.value = toSelect.value;
    toSelect.value = tempValue;
    
    // Swap displayed text
    for (let option of fromSelect.options) {
        if (option.value === fromSelect.value) {
            option.selected = true;
            break;
        }
    }
    
    for (let option of toSelect.options) {
        if (option.value === toSelect.value) {
            option.selected = true;
            break;
        }
    }
}

// Set minimum date for date input
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        
        // Ensure date is valid
        if (!dateInput.value || dateInput.value < today) {
            dateInput.value = today;
        }
    }
    
    // Mobile menu toggle (if needed)
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuBtn && navLinks) {
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('show');
        });
    }
    
    // Form validation
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const fromSelect = document.getElementById('from');
            const toSelect = document.getElementById('to');
            const dateInput = document.getElementById('date');
            
            // Check if cities are selected
            if (!fromSelect.value || !toSelect.value) {
                e.preventDefault();
                alert('Silakan pilih kota asal dan kota tujuan');
                return;
            }
            
            // Check if cities are the same
            if (fromSelect.value === toSelect.value) {
                e.preventDefault();
                alert('Kota asal dan kota tujuan tidak boleh sama');
                return;
            }
            
            // Check date
            if (!dateInput.value) {
                e.preventDefault();
                alert('Silakan pilih tanggal keberangkatan');
                return;
            }
        });
    }
});

// // Add loading state to search button
// document.addEventListener('DOMContentLoaded', function() {
//     const searchBtn = document.querySelector('.btn-search');
//     if (searchBtn) {
//         searchBtn.addEventListener('click', function() {
//             this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mencari...';
//             this.disabled = true;
            
//             // Re-enable after 3 seconds (in case of error)
//             setTimeout(() => {
//                 this.innerHTML = '<i class="fas fa-search"></i> CARI BUS';
//                 this.disabled = false;
//             }, 3000);
//         });
//     }
// });
// </script>

<style>
/* Additional styles for homepage */
.mobile-menu-btn {
    display: none;
    background: none;
    border: none;
    font-size: 24px;
    color: #333;
    cursor: pointer;
    padding: 10px;
}

@media (max-width: 768px) {
    .mobile-menu-btn {
        display: block;
    }
    
    .nav-links {
        display: none;
        flex-direction: column;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        padding: 20px;
        z-index: 1000;
    }
    
    .nav-links.show {
        display: flex;
    }
    
    .nav-links li {
        width: 100%;
        margin: 5px 0;
    }
    
    .nav-links a {
        width: 100%;
        justify-content: center;
    }
}

/* Select styling */
select.form-control {
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 15px;
    padding-right: 45px;
    cursor: pointer;
}

select.form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
}

/* Button swap animation */
.btn-swap {
    transition: all 0.3s ease;
}

.btn-swap:hover {
    animation: rotateSwap 0.6s ease;
}

@keyframes rotateSwap {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(180deg); }
}

/* Loading spinner */
.fa-spinner {
    animation: fa-spin 1s infinite linear;
}

@keyframes fa-spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Alert styling */
.alert {
    padding: 15px 20px;
    margin: 20px 0;
    border-radius: 8px;
    font-weight: 500;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}
</style>