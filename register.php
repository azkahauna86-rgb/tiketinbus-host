<?php
include 'includes/config.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    
    // Validation
    $errors = [];
    
    // Check if username exists
    $check_username = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check_username) > 0) {
        $errors[] = 'Username sudah digunakan';
    }
    
    // Check if email exists
    $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $errors[] = 'Email sudah terdaftar';
    }
    
    // Check password match
    if ($password !== $confirm_password) {
        $errors[] = 'Password tidak cocok';
    }
    
    // Check password strength
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter';
    }
    
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $query = "INSERT INTO users (username, email, password, full_name, phone, user_type) 
                 VALUES ('$username', '$email', '$hashed_password', '$full_name', '$phone', 'user')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = 'Pendaftaran berhasil! Silakan login.';
            header('Location: login.php');
            exit();
        } else {
            $errors[] = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-left">
            <div class="auth-content">
                <h1>Buat Akun Baru</h1>
                <p>Bergabung dengan BusTicket untuk mulai memesan tiket</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul style="margin: 0; padding-left: 20px;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="auth-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name"><i class="fas fa-user"></i> Nama Lengkap *</label>
                            <input type="text" id="full_name" name="full_name" required 
                                   placeholder="Masukkan nama lengkap" value="<?php echo $_POST['full_name'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username"><i class="fas fa-user-circle"></i> Username *</label>
                            <input type="text" id="username" name="username" required 
                                   placeholder="Masukkan username" value="<?php echo $_POST['username'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email *</label>
                            <input type="email" id="email" name="email" required 
                                   placeholder="contoh@email.com" value="<?php echo $_POST['email'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone"></i> Nomor Telepon *</label>
                            <input type="tel" id="phone" name="phone" required 
                                   placeholder="0812-3456-7890" value="<?php echo $_POST['phone'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="password"><i class="fas fa-lock"></i> Password *</label>
                            <div class="password-input">
                                <input type="password" id="password" name="password" required 
                                       placeholder="Minimal 6 karakter">
                                <button type="button" class="toggle-password" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="password-hint">Minimal 6 karakter</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password"><i class="fas fa-lock"></i> Konfirmasi Password *</label>
                            <div class="password-input">
                                <input type="password" id="confirm_password" name="confirm_password" required 
                                       placeholder="Ulangi password">
                                <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="terms">
                        <label class="checkbox">
                            <input type="checkbox" id="agree_terms" required>
                            <span>Saya setuju dengan 
                                <a href="terms.php" target="_blank">Syarat & Ketentuan</a> dan 
                                <a href="privacy.php" target="_blank">Kebijakan Privasi</a>
                            </span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-register">
                        <i class="fas fa-user-plus"></i> Daftar Sekarang
                    </button>
                    
                    <div class="auth-divider">
                        <span>atau</span>
                    </div>
                    
                    <div class="social-login">
                        <button type="button" class="btn-social google">
                            <i class="fab fa-google"></i> Daftar dengan Google
                        </button>
                        <button type="button" class="btn-social facebook">
                            <i class="fab fa-facebook"></i> Daftar dengan Facebook
                        </button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p>Sudah punya akun? <a href="login.php">Login disini</a></p>
                </div>
            </div>
        </div>
        
        <div class="auth-right">
            <div class="auth-hero">
                <div class="hero-content">
                    <h2>Manfaat Bergabung</h2>
                    <p>Dapatkan pengalaman terbaik dalam pemesanan tiket bus</p>
                    
                    <div class="benefits">
                        <div class="benefit">
                            <i class="fas fa-bolt"></i>
                            <div>
                                <h4>Pemesanan Cepat</h4>
                                <p>Proses pemesanan hanya dalam 3 menit</p>
                            </div>
                        </div>
                        <div class="benefit">
                            <i class="fas fa-tags"></i>
                            <div>
                                <h4>Promo Eksklusif</h4>
                                <p>Diskon dan penawaran spesial untuk member</p>
                            </div>
                        </div>
                        <div class="benefit">
                            <i class="fas fa-history"></i>
                            <div>
                                <h4>Riwayat Tiket</h4>
                                <p>Akses mudah ke semua tiket Anda</p>
                            </div>
                        </div>
                        <div class="benefit">
                            <i class="fas fa-star"></i>
                            <div>
                                <h4>Poin Reward</h4>
                                <p>Kumpulkan poin dan tukarkan dengan hadiah</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.benefits {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-top: 50px;
}

.benefit {
    display: flex;
    align-items: center;
    gap: 20px;
}

.benefit i {
    font-size: 28px;
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.benefit h4 {
    margin: 0 0 5px 0;
    font-size: 18px;
}

.benefit p {
    margin: 0;
    opacity: 0.8;
    font-size: 14px;
}

.password-hint {
    color: #7f8c8d;
    font-size: 12px;
    margin-top: 5px;
    display: block;
}

.btn-register {
    width: 100%;
    padding: 18px;
    background: #2ecc71;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-register:hover {
    background: #27ae60;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(46, 204, 113, 0.3);
}
</style>

<script>
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const toggleButton = passwordInput.nextElementSibling.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleButton.classList.remove('fa-eye');
        toggleButton.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleButton.classList.remove('fa-eye-slash');
        toggleButton.classList.add('fa-eye');
    }
}

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const hint = document.querySelector('.password-hint');
    
    if (password.length < 6) {
        hint.innerHTML = '❌ Password terlalu pendek (minimal 6 karakter)';
        hint.style.color = '#e74c3c';
    } else {
        hint.innerHTML = '✅ Password cukup kuat';
        hint.style.color = '#2ecc71';
    }
});

// Confirm password match
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (confirmPassword && password !== confirmPassword) {
        this.style.borderColor = '#e74c3c';
        this.style.boxShadow = '0 0 0 3px rgba(231, 76, 60, 0.2)';
    } else {
        this.style.borderColor = '#e0e0e0';
        this.style.boxShadow = 'none';
    }
});
</script>