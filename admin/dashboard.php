<?php
require_once '../includes/config.php';
requireAdmin();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - BusTicket</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2><i class="fas fa-bus"></i> <span>Admin Panel</span></h2>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active"><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                    <li><a href="buses.php"><i class="fas fa-bus"></i> <span>Bus Management</span></a></li>
                    <li><a href="routes.php"><i class="fas fa-route"></i> <span>Rute & Jadwal</span></a></li>
                    <li><a href="bookings.php"><i class="fas fa-ticket-alt"></i> <span>Pemesanan</span></a></li>
                    <li><a href="users.php"><i class="fas fa-users"></i> <span>Pengguna</span></a></li>
                    <li><a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="admin-header">
                <div class="header-left">
                    <h1>Dashboard Admin</h1>
                    <p>Selamat datang, <?php echo $_SESSION['full_name']; ?>!</p>
                </div>
                <div class="header-right">
                    <div class="admin-profile">
                        <span><?php echo $_SESSION['username']; ?></span>
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </header>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Pemesanan</h3>
                        <?php
                        $query = "SELECT COUNT(*) as total FROM bookings";
                        $result = mysqli_query($conn, $query);
                        $row = mysqli_fetch_assoc($result);
                        ?>
                        <p class="stat-number"><?php echo $row['total']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                        <i class="fas fa-bus"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Bus</h3>
                        <?php
                        $query = "SELECT COUNT(*) as total FROM buses";
                        $result = mysqli_query($conn, $query);
                        $row = mysqli_fetch_assoc($result);
                        ?>
                        <p class="stat-number"><?php echo $row['total']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Pengguna</h3>
                        <?php
                        $query = "SELECT COUNT(*) as total FROM users";
                        $result = mysqli_query($conn, $query);
                        $row = mysqli_fetch_assoc($result);
                        ?>
                        <p class="stat-number"><?php echo $row['total']; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Pendapatan</h3>
                        <?php
                        $query = "SELECT SUM(total_amount) as revenue FROM bookings WHERE payment_status = 'paid'";
                        $result = mysqli_query($conn, $query);
                        $row = mysqli_fetch_assoc($result);
                        $revenue = $row['revenue'] ? $row['revenue'] : 0;
                        ?>
                        <p class="stat-number">Rp <?php echo number_format($revenue, 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="content-section">
                <div class="section-header">
                    <h2>Pemesanan Terbaru</h2>
                    <a href="bookings.php" class="btn-view-all">Lihat Semua</a>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Pemesanan</th>
                            <th>Nama</th>
                            <th>Rute</th>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT b.*, u.full_name, r.departure_city, r.arrival_city 
                                 FROM bookings b 
                                 JOIN users u ON b.user_id = u.id 
                                 JOIN routes r ON b.route_id = r.id 
                                 ORDER BY b.booking_date DESC LIMIT 10";
                        $result = mysqli_query($conn, $query);
                        
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $status_class = strtolower($row['status']);
                                $payment_class = strtolower($row['payment_status']);
                                ?>
                                <tr>
                                    <td><?php echo $row['booking_number']; ?></td>
                                    <td><?php echo $row['full_name']; ?></td>
                                    <td><?php echo $row['departure_city'] . ' → ' . $row['arrival_city']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['booking_date'])); ?></td>
                                    <td>Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></td>
                                    <td><span class="status <?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                    <td><span class="status <?php echo $payment_class; ?>"><?php echo ucfirst($row['payment_status']); ?></span></td>
                                    <td>
                                        <button class="btn-action view" onclick="viewBooking(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action edit" onclick="editBooking(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="8" style="text-align: center;">Tidak ada data pemesanan</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Users -->
            <div class="content-section">
                <div class="section-header">
                    <h2>Pengguna Terbaru</h2>
                    <a href="users.php" class="btn-view-all">Lihat Semua</a>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Tipe</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
                        $result = mysqli_query($conn, $query);
                        
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $user_type = ($row['user_type'] == 'admin') ? '<span class="status confirmed">Admin</span>' : '<span class="status pending">User</span>';
                                ?>
                                <tr>
                                    <td><?php echo $row['username']; ?></td>
                                    <td><?php echo $row['full_name']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $user_type; ?></td>
                                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <button class="btn-action view" onclick="viewUser(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action edit" onclick="editUser(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo '<tr><td colspan="6" style="text-align: center;">Tidak ada data pengguna</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function viewBooking(id) {
        window.location.href = 'bookings.php?action=view&id=' + id;
    }
    
    function editBooking(id) {
        window.location.href = 'bookings.php?action=edit&id=' + id;
    }
    
    function viewUser(id) {
        window.location.href = 'users.php?action=view&id=' + id;
    }
    
    function editUser(id) {
        window.location.href = 'users.php?action=edit&id=' + id;
    }
    </script>
</body>
</html>