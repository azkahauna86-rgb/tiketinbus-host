<?php
session_start();
require_once '../includes/config.php';

$route_id = isset($_GET['route_id']) ? (int)$_GET['route_id'] : 0;
$passengers = isset($_GET['passengers']) ? (int)$_GET['passengers'] : 1;

$q = "SELECT r.*, b.total_seats, op.name AS operator_name 
      FROM routes r 
      JOIN buses b ON r.bus_id = b.id 
      JOIN bus_operators op ON b.operator_id = op.id 
      WHERE r.id = $route_id";
$res = mysqli_query($conn, $q);
$route = mysqli_fetch_assoc($res);

$booked_seats = [];
$qs = "SELECT p.seat_number FROM passengers p 
       JOIN bookings b ON p.booking_id = b.id 
       WHERE b.route_id = $route_id AND b.status != 'cancelled'";
$res_b = mysqli_query($conn, $qs);
while ($r = mysqli_fetch_assoc($res_b)) { $booked_seats[] = (int)$r['seat_number']; }

$harga_asli = isset($route['price']) ? (int)$route['price'] : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sistem Booking Bus</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; gap: 20px; padding: 20px; background: #f9f9f9; }
        .bus-layout { background: white; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
        .grid-kursi { display: grid; grid-template-columns: repeat(4, 50px); gap: 10px; margin-top: 15px; }
        .item-kursi { width: 50px; height: 50px; background: #28a745; color: white; display: flex; 
                      align-items: center; justify-content: center; cursor: pointer; border-radius: 5px; font-weight: bold; }
        .item-kursi.booked { background: #ccc; cursor: not-allowed; }
        .item-kursi.pilihan { background: #007bff; transform: scale(1.1); box-shadow: 0 0 10px rgba(0,0,0,0.2); }
        .sidebar { width: 300px; background: white; padding: 20px; border: 1px solid #ddd; border-radius: 10px; height: fit-content; }
        .total-harga { font-size: 24px; color: #e74c3c; font-weight: bold; margin: 10px 0; }
        .btn-kirim { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-kirim:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>

    <div class="bus-layout">
        <h3>Pilih <?php echo $passengers; ?> Kursi</h3>
        <div class="grid-kursi">
            <?php
            $jml_kursi = isset($route['total_seats']) ? (int)$route['total_seats'] : 20;
            for ($i = 1; $i <= $jml_kursi; $i++) {
                $st = in_array($i, $booked_seats) ? 'booked' : 'available';
                echo "<div class='item-kursi $st' onclick='aksiKlikKursi(this, $i)'>$i</div>";
            }
            ?>
        </div>
    </div>

    <div class="sidebar">
        <h3>Ringkasan</h3>
        <p>Kursi Terpilih: <strong id="txt-qty">0</strong></p>
        <hr>
        <p>Total Bayar:</p>
        <div class="total-harga" id="txt-total">Rp 0</div>

        <form id="formBookingFinal" action="order_submit.php" method="POST">
            <input type="hidden" name="route_id" value="<?php echo $route_id; ?>">
            <?php for($k = 1; $k <= $passengers; $k++): ?>
                <div style="margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                    <small>Penumpang <?php echo $k; ?>: <b id="label-k-<?php echo $k; ?>">-</b></small>
                    <input type="hidden" name="seat_<?php echo $k; ?>" id="input-k-<?php echo $k; ?>">
                    <input type="text" name="passenger_name_<?php echo $k; ?>" placeholder="Nama Lengkap" required style="width:100%; padding:5px; margin-top:3px;">
                </div>
            <?php endfor; ?>
            <button type="button" class="btn-kirim" id="btnProses" onclick="submitSekarang()" disabled>Lanjutkan</button>
        </form>
    </div>

<script>
    // NAMA VARIABEL SANGAT UNIK UNTUK MENGHINDARI "ALREADY DECLARED"
    let listKursiUser = [];
    const batasUser = <?php echo $passengers; ?>;
    const hargaUser = <?php echo $harga_asli; ?>;

    function aksiKlikKursi(el, no) {
        if (el.classList.contains('booked')) return;

        if (el.classList.contains('pilihan')) {
            el.classList.remove('pilihan');
            listKursiUser = listKursiUser.filter(x => x !== no);
        } else {
            if (listKursiUser.length >= batasUser) {
                alert("Maksimal pilih " + batasUser + " kursi");
                return;
            }
            el.classList.add('pilihan');
            listKursiUser.push(no);
        }
        updateTampilanBooking();
    }

    function updateTampilanBooking() {
        // Update form inputs
        for (let i = 1; i <= batasUser; i++) {
            let n = listKursiUser[i - 1] || "";
            document.getElementById('input-k-' + i).value = n;
            document.getElementById('label-k-' + i).innerText = n ? "Kursi " + n : "-";
        }

        // Update Ringkasan Sebelah Kanan
        let totalQty = listKursiUser.length;
        document.getElementById('txt-qty').innerText = totalQty + " Kursi";
        
        let totalBayar = totalQty * hargaUser;
        document.getElementById('txt-total').innerText = "Rp " + totalBayar.toLocaleString('id-ID');

        // Aktifkan Tombol
        document.getElementById('btnProses').disabled = (totalQty !== batasUser);
    }

    function submitSekarang() {
        document.getElementById('formBookingFinal').submit();
    }
</script>
</body>
</html>