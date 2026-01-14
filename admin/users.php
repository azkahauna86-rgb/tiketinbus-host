<?php
require_once '../includes/config.php';
requireAdmin();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_user'])) {
        $id = sanitize($_POST['id']);
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        $user_type = sanitize($_POST['user_type']);
        
        $query = "UPDATE users SET 
                 full_name = '$full_name',
                 email = '$email',
                 phone = '$phone',
                 address = '$address',
                 user_type = '$user_type'
                 WHERE id = '$id'";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = 'User berhasil diperbarui';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Error: ' . mysqli_error($conn);
            $_SESSION['message_type'] = 'error';
        }
    } elseif (isset($_POST['delete_user'])) {
        $id = sanitize($_POST['id']);
        
        // Don't delete admin users
        $check_query = "SELECT user_type FROM users WHERE id = '$id'";
        $check_result = mysqli_query($conn, $check_query);
        $user = mysqli_fetch_assoc($check_result);
        
        if ($user['user_type'] != 'admin') {
            $query = "DELETE FROM users WHERE id = '$id'";
            if (mysqli_query($conn, $query)) {
                $_SESSION['message'] = 'User berhasil dihapus';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Error: ' . mysqli_error($conn);
                $_SESSION['message_type'] = 'error';
            }
        } else {
            $_SESSION['message'] = 'Tidak dapat menghapus user admin';
            $_SESSION['message_type'] = 'error';
        }
    }
    
    header('Location: users.php');
    exit();
}

// Get users
$query = "SELECT * FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <header class="admin-header">
                <div class="header-left">
                    <h1>User Management</h1>
                    <p>Kelola data pengguna</p>
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
                    <h2>Daftar Pengguna</h2>
                </div>

                <!-- Users List -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Tipe</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $user_type = $row['user_type'] == 'admin' ? 
                                    '<span class="status confirmed">Admin</span>' : 
                                    '<span class="status pending">User</span>';
                                ?>
                                <tr>
                                    <td><?php echo $row['username']; ?></td>
                                    <td><?php echo $row['full_name']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['phone'] ?: '-'; ?></td>
                                    <td><?php echo $user_type; ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <button class="btn-action edit" onclick="editUser(
                                            '<?php echo $row['id']; ?>',
                                            '<?php echo $row['full_name']; ?>',
                                            '<?php echo $row['email']; ?>',
                                            '<?php echo $row['phone']; ?>',
                                            `<?php echo addslashes($row['address']); ?>`,
                                            '<?php echo $row['user_type']; ?>'
                                        )">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <?php if ($row['user_type'] != 'admin'): ?>
                                            <button class="btn-action delete" onclick="deleteUser(<?php echo $row['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="7" style="text-align: center;">Tidak ada data pengguna</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Edit User</h3>
            <form method="POST" action="" id="editForm">
                <input type="hidden" name="id" id="editId">
                <input type="hidden" name="update_user" value="1">
                
                <div class="form-group">
                    <label for="edit_full_name">Nama Lengkap</label>
                    <input type="text" id="edit_full_name" name="full_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_phone">Telepon</label>
                    <input type="text" id="edit_phone" name="phone" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="edit_address">Alamat</label>
                    <textarea id="edit_address" name="address" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="edit_user_type">Tipe User</label>
                    <select id="edit_user_type" name="user_type" class="form-control" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="btn-primary">Simpan</button>
                    <button type="button" class="btn-action delete" onclick="closeModal()">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function editUser(id, fullName, email, phone, address, userType) {
        document.getElementById('editModal').style.display = 'flex';
        
        document.getElementById('editId').value = id;
        document.getElementById('edit_full_name').value = fullName;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_phone').value = phone || '';
        document.getElementById('edit_address').value = address || '';
        document.getElementById('edit_user_type').value = userType;
    }
    
    function deleteUser(id) {
        if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = id;
            
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_user';
            deleteInput.value = '1';
            
            form.appendChild(idInput);
            form.appendChild(deleteInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('editModal');
        if (event.target == modal) {
            closeModal();
        }
    }
    </script>
</body>
</html>