<?php
require_once '../includes/config.php';
requireAdmin();

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_bus'])) {
        $bus_number = sanitize($_POST['bus_number']);
        $operator_id = sanitize($_POST['operator_id']);
        $bus_type = sanitize($_POST['bus_type']);
        $total_seats = sanitize($_POST['total_seats']);
        $amenities = sanitize($_POST['amenities']);
        
        $query = "INSERT INTO buses (bus_number, operator_id, bus_type, total_seats, amenities) 
                 VALUES ('$bus_number', '$operator_id', '$bus_type', '$total_seats', '$amenities')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = 'Bus berhasil ditambahkan';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Error: ' . mysqli_error($conn);
            $_SESSION['message_type'] = 'error';
        }
    } elseif (isset($_POST['update_bus'])) {
        $id = sanitize($_POST['id']);
        $bus_number = sanitize($_POST['bus_number']);
        $operator_id = sanitize($_POST['operator_id']);
        $bus_type = sanitize($_POST['bus_type']);
        $total_seats = sanitize($_POST['total_seats']);
        $amenities = sanitize($_POST['amenities']);
        
        $query = "UPDATE buses SET 
                 bus_number = '$bus_number',
                 operator_id = '$operator_id',
                 bus_type = '$bus_type',
                 total_seats = '$total_seats',
                 amenities = '$amenities'
                 WHERE id = '$id'";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = 'Bus berhasil diperbarui';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Error: ' . mysqli_error($conn);
            $_SESSION['message_type'] = 'error';
        }
    } elseif (isset($_POST['delete_bus'])) {
        $id = sanitize($_POST['id']);
        
        $query = "DELETE FROM buses WHERE id = '$id'";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = 'Bus berhasil dihapus';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Error: ' . mysqli_error($conn);
            $_SESSION['message_type'] = 'error';
        }
    }
    
    header('Location: buses.php');
    exit();
}

// Get bus operators for dropdown
$operators_query = "SELECT * FROM bus_operators ORDER BY name";
$operators_result = mysqli_query($conn, $operators_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Management - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <header class="admin-header">
                <div class="header-left">
                    <h1>Bus Management</h1>
                    <p>Kelola data bus dan operator</p>
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
                    <h2>Daftar Bus</h2>
                    <button class="btn-view-all" onclick="showAddForm()">
                        <i class="fas fa-plus"></i> Tambah Bus
                    </button>
                </div>

                <!-- Add/Edit Form -->
                <div id="busForm" class="card" style="display: none;">
                    <div class="card-header">
                        <h3 id="formTitle">Tambah Bus Baru</h3>
                        <button class="btn-action delete" onclick="hideForm()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form method="POST" action="">
                        <input type="hidden" id="busId" name="id">
                        
                        <div class="form-group">
                            <label for="bus_number">Nomor Bus</label>
                            <input type="text" id="bus_number" name="bus_number" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="operator_id">Operator</label>
                            <select id="operator_id" name="operator_id" class="form-control" required>
                                <option value="">Pilih Operator</option>
                                <?php while ($operator = mysqli_fetch_assoc($operators_result)): ?>
                                    <option value="<?php echo $operator['id']; ?>">
                                        <?php echo $operator['name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label for="bus_type">Tipe Bus</label>
                                <select id="bus_type" name="bus_type" class="form-control" required>
                                    <option value="AC">AC</option>
                                    <option value="Non-AC">Non-AC</option>
                                    <option value="Sleeper">Sleeper</option>
                                    <option value="Seater">Seater</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="total_seats">Jumlah Kursi</label>
                                <input type="number" id="total_seats" name="total_seats" class="form-control" min="1" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="amenities">Fasilitas</label>
                            <textarea id="amenities" name="amenities" class="form-control" rows="3" 
                                      placeholder="AC, Toilet, WiFi, Snack, dll."></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" id="submitBtn" name="add_bus" class="btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <button type="button" class="btn-action delete" onclick="hideForm()">
                                <i class="fas fa-times"></i> Batal
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Bus List -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Bus</th>
                            <th>Operator</th>
                            <th>Tipe</th>
                            <th>Kursi</th>
                            <th>Fasilitas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT b.*, o.name as operator_name 
                                 FROM buses b 
                                 JOIN bus_operators o ON b.operator_id = o.id 
                                 ORDER BY b.bus_number";
                        $result = mysqli_query($conn, $query);
                        
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                ?>
                                <tr>
                                    <td><?php echo $row['bus_number']; ?></td>
                                    <td><?php echo $row['operator_name']; ?></td>
                                    <td><?php echo $row['bus_type']; ?></td>
                                    <td><?php echo $row['total_seats']; ?></td>
                                    <td><?php echo $row['amenities']; ?></td>
                                    <td>
                                        <button class="btn-action edit" onclick="editBus(
                                            '<?php echo $row['id']; ?>',
                                            '<?php echo $row['bus_number']; ?>',
                                            '<?php echo $row['operator_id']; ?>',
                                            '<?php echo $row['bus_type']; ?>',
                                            '<?php echo $row['total_seats']; ?>',
                                            `<?php echo addslashes($row['amenities']); ?>`
                                        )">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn-action delete" onclick="deleteBus(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="6" style="text-align: center;">Tidak ada data bus</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Konfirmasi Hapus</h3>
            <p>Apakah Anda yakin ingin menghapus bus ini?</p>
            <form method="POST" action="" id="deleteForm">
                <input type="hidden" name="id" id="deleteId">
                <input type="hidden" name="delete_bus" value="1">
                <div class="modal-actions">
                    <button type="submit" class="btn-action delete">Ya, Hapus</button>
                    <button type="button" class="btn-action" onclick="closeModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <style>
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    
    .modal-content {
        background: white;
        padding: 30px;
        border-radius: 15px;
        max-width: 400px;
        width: 90%;
    }
    
    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
    }
    
    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }
    </style>

    <script>
    function showAddForm() {
        document.getElementById('busForm').style.display = 'block';
        document.getElementById('formTitle').textContent = 'Tambah Bus Baru';
        document.getElementById('submitBtn').name = 'add_bus';
        resetForm();
    }
    
    function hideForm() {
        document.getElementById('busForm').style.display = 'none';
    }
    
    function resetForm() {
        document.getElementById('busId').value = '';
        document.getElementById('bus_number').value = '';
        document.getElementById('operator_id').value = '';
        document.getElementById('bus_type').value = 'AC';
        document.getElementById('total_seats').value = '40';
        document.getElementById('amenities').value = '';
    }
    
    function editBus(id, busNumber, operatorId, busType, totalSeats, amenities) {
        document.getElementById('busForm').style.display = 'block';
        document.getElementById('formTitle').textContent = 'Edit Bus';
        document.getElementById('submitBtn').name = 'update_bus';
        
        document.getElementById('busId').value = id;
        document.getElementById('bus_number').value = busNumber;
        document.getElementById('operator_id').value = operatorId;
        document.getElementById('bus_type').value = busType;
        document.getElementById('total_seats').value = totalSeats;
        document.getElementById('amenities').value = amenities;
        
        // Scroll to form
        document.getElementById('busForm').scrollIntoView({ behavior: 'smooth' });
    }
    
    function deleteBus(id) {
        document.getElementById('deleteModal').style.display = 'flex';
        document.getElementById('deleteId').value = id;
    }
    
    function closeModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target == modal) {
            closeModal();
        }
    }
    </script>
</body>
</html>