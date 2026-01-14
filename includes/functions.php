<?php
// Function to generate booking number
function generateBookingNumber() {
    return 'BK' . strtoupper(uniqid());
}

// Function to check login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

// Function to format currency
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Function to calculate duration
function calculateDuration($start, $end) {
    $start_time = new DateTime($start);
    $end_time = new DateTime($end);
    $interval = $start_time->diff($end_time);
    
    $hours = $interval->h;
    $minutes = $interval->i;
    
    if ($hours > 0 && $minutes > 0) {
        return $hours . ' jam ' . $minutes . ' menit';
    } elseif ($hours > 0) {
        return $hours . ' jam';
    } else {
        return $minutes . ' menit';
    }
}

// Function to sanitize input
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Function to redirect with message
function redirectWithMessage($url, $type, $message) {
    $_SESSION['message_type'] = $type;
    $_SESSION['message'] = $message;
    header("Location: $url");
    exit();
}
?> -->
<?php
// This file is now merged with config.php
?>