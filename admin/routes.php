<?php
require_once '../includes/config.php';
requireAdmin();

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_route'])) {
        $bus_id = sanitize($_POST['bus_id']);
        $departure_city = sanitize($_POST['departure_city']);
        $arrival_city = sanitize($_POST['arrival_city']);
        $departure_time = sanitize($_POST['departure_time']);
        $arrival_time = sanitize($_POST['arrival_time']);
        $duration = sanitize($_POST['duration']);
        $distance = sanitize($_POST['distance']);
        $price = sanitize($_POST['price']);
        
        // Get available seats from bus
        $bus_query = "SELECT total_seats FROM buses WHERE id = '$bus_id'";
        $bus_result = mysqli_query($conn, $bus_query);
        $bus = mysqli_fetch_assoc($bus_result);
        $available_seats = $bus['total_seats'];
        
        $query = "INSERT INTO routes (bus_id, departure_city, arrival_city, departure_time, 
                  arrival_time, duration, distance, price, available_seats) 
                 VALUES ('$bus_id', '$departure_city', '$arrival_city', '$departure_time',
                         '$arrival_time', '$duration', '$distance', '$price', '$available_seats')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = 'Rute berhasil ditambahkan';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Error: ' . mysqli_error($conn);
            $_SESSION['message_type'] = 'error';
        }
    } elseif (isset($_POST['update_route'])) {
        $id = sanitize($_POST['id']);
        $bus_id = sanitize($_POST['bus_id']);
        $departure_city = sanitize($_POST['departure_city']);
        $arrival_city = sanitize($_POST['arrival_city']);
        $departure_time = sanitize($_POST['departure_time']);
        $arrival_time = sanitize($_POST['arrival_time']);
        $duration = sanitize($_POST['duration']);
        $distance = sanitize($_POST['distance']);
        $price = sanitize($_POST['price']);
        
        $query = "UPDATE routes SET 
                 bus_id = '$bus_id',
                 departure_city = '$departure_city',
                 arrival_city = '$arrival_city',
                 departure_time = '$departure_time',
                 arrival_time = '$arrival_time',
                 duration = '$duration',
                 distance = '$distance',
                 price = '$price'
                 WHERE id = '$id'";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = 'Rute berhasil diperbarui';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Error: ' . mysqli_error($conn);
            $_SESSION['message_type'] = 'error';
        }
    } elseif (isset($_POST['delete_route'])) {
        $id = sanitize($_POST['id']);
        
        $query = "DELETE FROM routes WHERE id = '$id'";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = 'Rute berhasil dihapus';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Error: ' . mysqli_error($conn);
            $_SESSION['message_type'] = 'error';
        }
    }
    
    header('Location: routes.php');
    exit();
}

// Get buses for dropdown
$buses_query = "SELECT b.*, o.name as operator_name 
               FROM buses b 
               JOIN bus_operators o ON b.operator_id = o.id 
               ORDER BY b.bus_number";
$buses_result = mysqli_query($conn, $buses_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Management - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Route Management</h1>
                    <p>Kelola rute dan jadwal bus</p>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <span><?php echo $_SESSION['username']; ?></span>
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </header>

            <div class="content-section">
                <div class="section-header">
                    <h2>Daftar Rute</h2>
                    <button class="btn-view-all" onclick="showAddForm()">
                        <i class="fas fa-plus"></i> Tambah Rute
                    </button>
                </div>

                <!-- Add/Edit Form -->
                <div id="routeForm" class="card" style="display: none;">
                    <div class="card-header">
                        <h3 id="formTitle">Tambah Rute Baru</h3>
                        <button class="btn-action delete" onclick="hideForm()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form method="POST" action="">
                        <input type="hidden" id="routeId" name="id">
                        
                        <div class="form-group">
                            <label for="bus_id">Bus</label>
                            <select id="bus_id" name="bus_id" class="form-control" required>
                                <option value="">Pilih Bus</option>
                                <?php while ($bus = mysqli_fetch_assoc($buses_result)): ?>
                                    <option value="<?php echo $bus['id']; ?>">
                                        <?php echo $bus['bus_number'] . ' - ' . $bus['operator_name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label for="departure_city">Kota Keberangkatan</label>
                                <input type="text" id="departure_city" name="departure_city" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="arrival_city">Kota Tujuan</label>
                                <input type="text" id="arrival_city" name="arrival_city" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label for="departure_time">Waktu Keberangkatan</label>
                                <input type="datetime-local" id="departure_time" name="departure_time" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="arrival_time">Waktu Kedatangan</label>
                                <input type="datetime-local" id="arrival_time" name="arrival_time" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label for="duration">Durasi</label>
                                <input type="text" id="duration" name="duration" class="form-control" placeholder="4 jam" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="distance">Jarak (km)</label>
                                <input type="number" id="distance" name="distance" class="form-control" step="0.01" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="price">Harga</label>
                                <input type="number" id="price" name="price" class="form-control" min="0" required>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" id="submitBtn" name="add_route" class="btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <button type="button" class="btn-action delete" onclick="hideForm()">
                                <i class="fas fa-times"></i> Batal
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Routes List -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Rute</th>
                            <th>Bus</th>
                            <th>Waktu</th>
                            <th>Durasi</th>
                            <th>Jarak</th>
                            <th>Harga</th>
                            <th>Kursi Tersedia</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        mysqli_data_seek($buses_result, 0); // Reset pointer
                        $buses = array();
                        while ($bus = mysqli_fetch_assoc($buses_result)) {
                            $buses[$bus['id']] = $bus;
                        }
                        
                        $query = "SELECT * FROM routes ORDER BY departure_time";
                        $result = mysqli_query($conn, $query);
                        
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $bus = $buses[$row['bus_id']] ?? null;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $row['departure_city']; ?> → <?php echo $row['arrival_city']; ?></strong><br>
                                        <small><?php echo date('d M Y', strtotime($row['departure_time'])); ?></small>
                                    </td>
                                    <td>
                                        <?php if ($bus): ?>
                                            <?php echo $bus['bus_number']; ?><br>
                                            <small><?php echo $bus['operator_name']; ?></small>
                                        <?php else: ?>
                                            Bus tidak ditemukan
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo date('H:i', strtotime($row['departure_time'])); ?> - 
                                        <?php echo date('H:i', strtotime($row['arrival_time'])); ?>
                                    </td>
                                    <td><?php echo $row['duration']; ?></td>
                                    <td><?php echo $row['distance']; ?> km</td>
                                    <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                                    <td><?php echo $row['available_seats']; ?></td>
                                    <td>
                                        <button class="btn-action edit" onclick="editRoute(
                                            '<?php echo $row['id']; ?>',
                                            '<?php echo $row['bus_id']; ?>',
                                            '<?php echo $row['departure_city']; ?>',
                                            '<?php echo $row['arrival_city']; ?>',
                                            '<?php echo date('Y-m-d\TH:i', strtotime($row['departure_time'])); ?>',
                                            '<?php echo date('Y-m-d\TH:i', strtotime($row['arrival_time'])); ?>',
                                            '<?php echo $row['duration']; ?>',
                                            '<?php echo $row['distance']; ?>',
                                            '<?php echo $row['price']; ?>'
                                        )">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action delete" onclick="deleteRoute(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="8" style="text-align: center;">Tidak ada data rute</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function showAddForm() {
        document.getElementById('routeForm').style.display = 'block';
        document.getElementById('formTitle').textContent = 'Tambah Rute Baru';
        document.getElementById('submitBtn').name = 'add_route';
        resetForm();
    }
    
    function hideForm() {
        document.getElementById('routeForm').style.display = 'none';
    }
    
    function resetForm() {
        document.getElementById('routeId').value = '';
        document.getElementById('bus_id').value = '';
        document.getElementById('departure_city').value = '';
        document.getElementById('arrival_city').value = '';
        document.getElementById('departure_time').value = '';
        document.getElementById('arrival_time').value = '';
        document.getElementById('duration').value = '';
        document.getElementById('distance').value = '';
        document.getElementById('price').value = '';
    }
    
    function editRoute(id, busId, departureCity, arrivalCity, departureTime, arrivalTime, duration, distance, price) {
        document.getElementById('routeForm').style.display = 'block';
        document.getElementById('formTitle').textContent = 'Edit Rute';
        document.getElementById('submitBtn').name = 'update_route';
        
        document.getElementById('routeId').value = id;
        document.getElementById('bus_id').value = busId;
        document.getElementById('departure_city').value = departureCity;
        document.getElementById('arrival_city').value = arrivalCity;
        document.getElementById('departure_time').value = departureTime;
        document.getElementById('arrival_time').value = arrivalTime;
        document.getElementById('duration').value = duration;
        document.getElementById('distance').value = distance;
        document.getElementById('price').value = price;
        
        document.getElementById('routeForm').scrollIntoView({ behavior: 'smooth' });
    }
    
    function deleteRoute(id) {
        if (confirm('Apakah Anda yakin ingin menghapus rute ini?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_route';
            deleteInput.value = '1';
            
            form.appendChild(idInput);
            form.appendChild(deleteInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>