// Main JavaScript file

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            navLinks.classList.toggle('show');
        });
    }
    
    // Date input - set min date to today
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    
    dateInputs.forEach(input => {
        input.min = today;
        if (!input.value) {
            input.value = today;
        }
    });
    
    // Auto-swap cities in search form
    const swapBtn = document.querySelector('.swap-cities');
    if (swapBtn) {
        swapBtn.addEventListener('click', function() {
            const fromInput = document.getElementById('from');
            const toInput = document.getElementById('to');
            const temp = fromInput.value;
            fromInput.value = toInput.value;
            toInput.value = temp;
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('form[needs-validation]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Load cities for autocomplete
    loadCities();
    
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
});

// Load cities for autocomplete
function loadCities() {
    const cityInputs = document.querySelectorAll('.city-autocomplete');
    
    const cities = [
        'Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Semarang', 
        'Yogyakarta', 'Malang', 'Denpasar', 'Makassar', 'Palembang',
        'Bogor', 'Tangerang', 'Bekasi', 'Depok', 'Cirebon',
        'Solo', 'Madiun', 'Kediri', 'Jember', 'Banyuwangi',
        'Lampung', 'Padang', 'Pekanbaru', 'Bengkulu', 'Jambi'
    ];
    
    cityInputs.forEach(input => {
        input.addEventListener('input', function() {
            const value = this.value.toLowerCase();
            const datalist = this.nextElementSibling;
            
            if (datalist && datalist.tagName === 'DATALIST') {
                datalist.innerHTML = '';
                
                cities.filter(city => 
                    city.toLowerCase().includes(value)
                ).forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    datalist.appendChild(option);
                });
            }
        });
    });
}

// Toast notifications
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Format currency
function formatCurrency(amount) {
    return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Calculate duration between two times
function calculateDuration(start, end) {
    const startTime = new Date(start);
    const endTime = new Date(end);
    const diff = endTime - startTime;
    
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    
    if (hours > 0 && minutes > 0) {
        return `${hours} jam ${minutes} menit`;
    } else if (hours > 0) {
        return `${hours} jam`;
    } else {
        return `${minutes} menit`;
    }
}

// Seat selection functions
let selectedSeats = [];

function selectSeat(seatElement, seatNumber) {
    const maxSeats = parseInt(document.querySelector('[data-max-seats]')?.dataset.maxSeats || 1);
    
    if (seatElement.classList.contains('booked')) {
        return;
    }
    
    if (seatElement.classList.contains('selected')) {
        // Deselect seat
        seatElement.classList.remove('selected');
        selectedSeats = selectedSeats.filter(s => s !== seatNumber);
    } else {
        // Select seat
        if (selectedSeats.length < maxSeats) {
            seatElement.classList.add('selected');
            selectedSeats.push(seatNumber);
        } else {
            showToast(`Maksimal ${maxSeats} kursi yang dapat dipilih`, 'warning');
            return;
        }
    }
    
    updateSelectedSeatsDisplay();
    updatePassengerSeatFields();
}

function updateSelectedSeatsDisplay() {
    const displayElement = document.getElementById('selectedSeatsDisplay');
    if (displayElement) {
        if (selectedSeats.length > 0) {
            displayElement.textContent = selectedSeats.sort((a, b) => a - b).join(', ');
        } else {
            displayElement.textContent = 'Belum ada kursi yang dipilih';
        }
    }
}

function updatePassengerSeatFields() {
    const passengerCount = parseInt(document.querySelector('[data-passengers]')?.dataset.passengers || 1);
    
    for (let i = 1; i <= passengerCount; i++) {
        const seatField = document.getElementById(`seat_${i}`);
        if (seatField && selectedSeats[i - 1]) {
            seatField.value = selectedSeats[i - 1];
        }
    }
}

function setupCityAutocomplete(inputId, listId) {
    const input = document.getElementById(inputId);
    const list = document.getElementById(listId);

    input.addEventListener("keyup", function () {
        const keyword = this.value.trim();
        if (keyword.length < 2) {
            list.style.display = "none";
            return;
        }

        fetch(`search_city.php?q=${keyword}`)
            .then(res => res.json())
            .then(data => {
                list.innerHTML = "";
                if (data.length === 0) {
                    list.style.display = "none";
                    return;
                }

                data.forEach(city => {
                    const div = document.createElement("div");
                    div.className = "autocomplete-item";
                    div.innerHTML = `
                        <strong>${city.name}</strong><br>
                        <small>${city.province}</small>
                    `;
                    div.onclick = () => {
                        input.value = city.name;
                        list.style.display = "none";
                    };
                    list.appendChild(div);
                });

                list.style.display = "block";
            });
    });

    document.addEventListener("click", () => {
        list.style.display = "none";
    });
}

// setupCityAutocomplete("from", "from-list");
// setupCityAutocomplete("to", "to-list");


// AJAX functions
function loadData(url, callback) {
    fetch(url)
        .then(response => response.json())
        .then(data => callback(data))
        .catch(error => console.error('Error:', error));
}

function postData(url, data, callback) {
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => callback(data))
    .catch(error => console.error('Error:', error));
}