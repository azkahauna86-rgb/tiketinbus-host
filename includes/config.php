
<?php
// includes/config.php - FIXED VERSION

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tiketbus');

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// ========== FUNGSI UTAMA ==========

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: ../login.php');
        exit();
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['message'] = 'Akses ditolak! Hanya admin yang dapat mengakses halaman ini.';
        $_SESSION['message_type'] = 'error';
        header('Location: ../index.php');
        exit();
    }
}

// Format currency
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Generate booking number
function generateBookingNumber() {
    return 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

// **FIXED SANITIZE FUNCTION - PHP 7.4+ Compatible**
function sanitize($data) {
    // If data is array, sanitize each element
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    
    // Remove whitespace
    $data = trim($data);
    
    // Remove slashes if they exist (backward compatibility)
    if (!empty($data) && (strpos($data, '\\') !== false || (strpos($data, '"') !== false && strpos($data, '\"') !== false))) {
        $data = stripslashes($data);
    }
    
    // Convert special characters to HTML entities
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    // Escape for MySQL (only if connection exists)
    global $conn;
    if (isset($conn) && is_object($conn)) {
        $data = mysqli_real_escape_string($conn, $data);
    }
    
    return $data;
}

// Simple sanitize without database dependency (for GET/POST)
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Set message for user
function setMessage($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

// Show message if exists
function showMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        
        $alert_class = 'alert-success';
        if ($type == 'error') $alert_class = 'alert-error';
        if ($type == 'warning') $alert_class = 'alert-warning';
        if ($type == 'info') $alert_class = 'alert-info';
        
        return "<div class='alert $alert_class'>$message</div>";
    }
    return '';
}

// Get current date time
function getCurrentDateTime() {
    return date('Y-m-d H:i:s');
}

// Get current date
function getCurrentDate() {
    return date('Y-m-d');
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone number (Indonesia)
function isValidPhone($phone) {
    return preg_match('/^(\+62|62|0)8[1-9][0-9]{6,9}$/', $phone);
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Redirect with message
function redirect($url, $message = '', $type = 'success') {
    if (!empty($message)) {
        setMessage($message, $type);
    }
    header("Location: $url");
    exit();
}

// Get user data
function getUserData($user_id = null) {
    global $conn;
    
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    if (!$user_id) return null;
    
    $user_id = sanitize($user_id);
    $query = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Get user bookings count
function getUserBookingsCount($user_id = null) {
    global $conn;
    
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    if (!$user_id) return 0;
    
    $user_id = sanitize($user_id);
    $query = "SELECT COUNT(*) as count FROM bookings WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['count'];
    }
    
    return 0;
}

// Check if route exists
function routeExists($route_id) {
    global $conn;
    $route_id = sanitize($route_id);
    $query = "SELECT id FROM routes WHERE id = '$route_id' LIMIT 1";
    $result = mysqli_query($conn, $query);
    return ($result && mysqli_num_rows($result) > 0);
}

// Get route details
function getRouteDetails($route_id) {
    global $conn;
    $route_id = sanitize($route_id);
    $query = "SELECT r.*, b.bus_number, b.bus_type, op.name as operator_name 
              FROM routes r 
              JOIN buses b ON r.bus_id = b.id 
              JOIN bus_operators op ON b.operator_id = op.id 
              WHERE r.id = '$route_id' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Get available seats for route
function getAvailableSeats($route_id) {
    global $conn;
    $route_id = sanitize($route_id);
    $query = "SELECT available_seats FROM routes WHERE id = '$route_id' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['available_seats'];
    }
    
    return 0;
}

// Update available seats
function updateAvailableSeats($route_id, $seats_booked) {
    global $conn;
    $route_id = sanitize($route_id);
    $seats_booked = intval($seats_booked);
    
    $current_seats = getAvailableSeats($route_id);
    $new_seats = $current_seats - $seats_booked;
    
    if ($new_seats < 0) {
        return false; // Not enough seats
    }
    
    $query = "UPDATE routes SET available_seats = '$new_seats' WHERE id = '$route_id'";
    return mysqli_query($conn, $query);
}

// Get booking details
function getBookingDetails($booking_id) {
    global $conn;
    $booking_id = sanitize($booking_id);
    $query = "SELECT b.*, u.full_name, u.email, u.phone,
                     r.departure_city, r.arrival_city, r.departure_time, r.arrival_time, r.price,
                     bu.bus_number, op.name as operator_name
              FROM bookings b
              JOIN users u ON b.user_id = u.id
              JOIN routes r ON b.route_id = r.id
              JOIN buses bu ON r.bus_id = bu.id
              JOIN bus_operators op ON bu.operator_id = op.id
              WHERE b.id = '$booking_id' LIMIT 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
    }
    
    return null;
}

// Get passengers for booking
function getPassengers($booking_id) {
    global $conn;
    $booking_id = sanitize($booking_id);
    $query = "SELECT * FROM passengers WHERE booking_id = '$booking_id' ORDER BY id";
    $result = mysqli_query($conn, $query);
    
    $passengers = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $passengers[] = $row;
        }
    }
    
    return $passengers;
}

// Get popular routes for homepage
function getPopularRoutes($limit = 6) {
    global $conn;
    $limit = intval($limit);
    $query = "SELECT r.*, b.bus_type, op.name as operator_name, 
                     COUNT(bk.id) as booking_count
              FROM routes r
              JOIN buses b ON r.bus_id = b.id
              JOIN bus_operators op ON b.operator_id = op.id
              LEFT JOIN bookings bk ON r.id = bk.route_id
              WHERE r.departure_time >= CURDATE()
              GROUP BY r.id
              ORDER BY booking_count DESC, r.departure_time ASC
              LIMIT $limit";
    $result = mysqli_query($conn, $query);
    
    $routes = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $routes[] = $row;
        }
    }
    
    return $routes;
}

// Get all cities from database
function getAllCities() {
    global $conn;
    $query = "SELECT DISTINCT departure_city as city FROM routes 
              UNION 
              SELECT DISTINCT arrival_city as city FROM routes 
              ORDER BY city ASC";
    $result = mysqli_query($conn, $query);
    
    $cities = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $cities[] = $row['city'];
        }
    }
    
    return $cities;
}

// Get user statistics
function getUserStatistics($user_id = null) {
    global $conn;
    
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    if (!$user_id) return null;
    
    $user_id = sanitize($user_id);
    $stats = [
        'total_bookings' => 0,
        'confirmed_bookings' => 0,
        'pending_bookings' => 0,
        'cancelled_bookings' => 0,
        'total_spent' => 0
    ];
    
    // Total bookings
    $query = "SELECT COUNT(*) as count FROM bookings WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $stats['total_bookings'] = $row['count'];
    }
    
    // Confirmed bookings
    $query = "SELECT COUNT(*) as count FROM bookings WHERE user_id = '$user_id' AND status = 'confirmed'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $stats['confirmed_bookings'] = $row['count'];
    }
    
    // Pending bookings
    $query = "SELECT COUNT(*) as count FROM bookings WHERE user_id = '$user_id' AND status = 'pending'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $stats['pending_bookings'] = $row['count'];
    }
    
    // Cancelled bookings
    $query = "SELECT COUNT(*) as count FROM bookings WHERE user_id = '$user_id' AND status = 'cancelled'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $stats['cancelled_bookings'] = $row['count'];
    }
    
    // Total spent
    $query = "SELECT SUM(total_amount) as total FROM bookings WHERE user_id = '$user_id' AND payment_status = 'paid'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $stats['total_spent'] = $row['total'] ?: 0;
    }
    
    return $stats;
}

// Log user activity
function logActivity($user_id, $action, $details = '') {
    global $conn;
    
    $user_id = sanitize($user_id);
    $action = sanitize($action);
    $details = sanitize($details);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $query = "INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) 
              VALUES ('$user_id', '$action', '$details', '$ip_address', '$user_agent')";
    
    return mysqli_query($conn, $query);
}

// Check if email exists
function emailExists($email) {
    global $conn;
    $email = sanitize($email);
    $query = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);
    return ($result && mysqli_num_rows($result) > 0);
}

// Check if username exists
function usernameExists($username) {
    global $conn;
    $username = sanitize($username);
    $query = "SELECT id FROM users WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($conn, $query);
    return ($result && mysqli_num_rows($result) > 0);
}

// Get bus operators for dropdown
function getBusOperators() {
    global $conn;
    $query = "SELECT * FROM bus_operators ORDER BY name ASC";
    $result = mysqli_query($conn, $query);
    
    $operators = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $operators[] = $row;
        }
    }
    
    return $operators;
}

// Get buses for dropdown
function getBuses() {
    global $conn;
    $query = "SELECT b.*, op.name as operator_name 
              FROM buses b
              JOIN bus_operators op ON b.operator_id = op.id
              ORDER BY b.bus_number ASC";
    $result = mysqli_query($conn, $query);
    
    $buses = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $buses[] = $row;
        }
    }
    
    return $buses;
}

// Error handler
function handleError($error) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['error'] = $error;
    }
    error_log("Bus Ticket System Error: " . $error);
}

// Success handler
function handleSuccess($message) {
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['success'] = $message;
    }
}

// Debug function (for development only)
function debug($data, $die = false) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    if ($die) die();
}

// Auto-clean old sessions
function cleanOldSessions() {
    global $conn;
    $old_time = date('Y-m-d H:i:s', strtotime('-1 day'));
    $query = "DELETE FROM sessions WHERE last_activity < '$old_time'";
    mysqli_query($conn, $query);
}

// Initialize system
function initSystem() {
    // Clean old sessions periodically
    if (rand(1, 100) === 1) { // 1% chance on each request
        cleanOldSessions();
    }
}

// Call initialization
initSystem();
?>