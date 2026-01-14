<?php
session_start();
require_once 'includes/config.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Akses tidak valid";
    exit;
}

$route_id = $_POST['route_id'] ?? null;

$passengers = 0;
$selected_seats = [];
$passenger_data = [];

foreach ($_POST as $key => $value) {
    if (strpos($key, 'seat_') === 0 && $value !== '') {
        $selected_seats[] = $value;
    }
}

foreach ($_POST as $key => $value) {
    if (strpos($key, 'passenger_name_') === 0) {
        $i = str_replace('passenger_name_', '', $key);
        $passenger_data[] = [
            'name' => $_POST['passenger_name_'.$i],
            'age' => $_POST['passenger_age_'.$i],
            'gender' => $_POST['passenger_gender_'.$i],
            'seat_number' => $_POST['seat_'.$i]
        ];
    }
}

$passengers = count($passenger_data);

if ($passengers === 0 || count($selected_seats) !== $passengers) {
    echo "Data kursi atau penumpang tidak lengkap";
    echo "<pre>";
    print_r($_POST);
    exit;
}

/* SIMPAN KE SESSION */
$_SESSION['order_data'] = [
    'route_id' => $_GET['route_id'] ?? null,
    'passengers' => $passengers,
    'selected_seats' => $selected_seats,
    'passenger_data' => $passenger_data
];
?>

<!DOCTYPE html>
<html>
<head>
<title>Order Submit</title>
</head>
<body>

<h2>Order Submit Berhasil</h2>

<pre>
<?php print_r($_SESSION['order_data']); ?>
</pre>

<a href="payment.php">Lanjut ke Pembayaran</a>

</body>
</html>
