<?php
include 'includes/config.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = '$username' OR email = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['created_at'] = $user['created_at'];
            
            // Redirect to original page or dashboard
            if (isset($_GET['redirect']) && $_GET['redirect'] == 'booking' && isset($_GET['route_id'])) {
                header('Location: booking.php?route_id=' . $_GET['route_id'] . '&passengers=' . ($_GET['passengers'] ?? 1));
            } elseif ($user['user_type'] == 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: user/dashboard.php');
            }
            exit();
        } else {
            $error = 'Username atau password salah';
        }
    } else {
        $error = 'Username atau password salah';
    }
}

include 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-left">
            <div class="auth-content">
                <h1>Selamat Datang Kembali</h1>
                <p>Login untuk melanjutkan pemesanan tiket bus Anda</p>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" class="auth-form">
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Username atau Email</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Masukkan username atau email">
                    </div>
                    
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" required 
                                   placeholder="Masukkan password">
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox">
                            <input type="checkbox" name="remember">
                            <span>Ingat saya</span>
                        </label>
                        <a href="forgot-password.php" class="forgot-link">Lupa password?</a>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                    
                    <div class="auth-divider">
                        <span>atau</span>
                    </div>
                    
                    <div class="social-login">
                        <button type="button" class="btn-social google">
                            <i class="fab fa-google"></i> Login dengan Google
                        </button>
                        <button type="button" class="btn-social facebook">
                            <i class="fab fa-facebook"></i> Login dengan Facebook
                        </button>
                    </div>
                </form>
                
                <div class="auth-footer">
                    <p>Belum punya akun? <a href="register.php">Daftar disini</a></p>
                </div>
            </div>
        </div>
        
        <div class="auth-right">
            <div class="auth-hero">
                <div class="hero-content">
                    <h2>Pesan Tiket Bus Online</h2>
                    <p>Lebih mudah, lebih cepat, dan lebih aman dengan BusTicket</p>
                    
                    <div class="features">
                        <div class="feature">
                            <i class="fas fa-bus"></i>
                            <div>
                                <h4>100+ Bus</h4>
                                <p>Pilihan bus terbaik</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <h4>100% Aman</h4>
                                <p>Transaksi terjamin</p>
                            </div>
                        </div>
                        <div class="feature">
                            <i class="fas fa-headset"></i>
                            <div>
                                <h4>24/7 Support</h4>
                                <p>Bantuan siap membantu</p>
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
.auth-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.auth-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    max-width: 1200px;
    width: 100%;
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    min-height: 700px;
}

@media (max-width: 992px) {
    .auth-container {
        grid-template-columns: 1fr;
        max-width: 500px;
    }
    
    .auth-right {
        display: none;
    }
}

.auth-left {
    padding: 60px 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.auth-content {
    width: 100%;
    max-width: 400px;
}

.auth-content h1 {
    font-size: 32px;
    color: #2c3e50;
    margin-bottom: 10px;
    font-weight: 700;
}

.auth-content > p {
    color: #7f8c8d;
    margin-bottom: 40px;
    font-size: 16px;
}

.auth-form .form-group {
    margin-bottom: 25px;
}

.auth-form label {
    display: block;
    margin-bottom: 8px;
    color: #2c3e50;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}

.password-input {
    position: relative;
}

.password-input input {
    width: 100%;
    padding: 15px 50px 15px 20px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.password-input input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
}

.toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #7f8c8d;
    cursor: pointer;
    font-size: 18px;
}

.toggle-password:hover {
    color: #3498db;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: #2c3e50;
}

.checkbox input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.forgot-link {
    color: #3498db;
    text-decoration: none;
    font-weight: 500;
}

.forgot-link:hover {
    text-decoration: underline;
}

.btn-login {
    width: 100%;
    padding: 18px;
    background: #3498db;
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

.btn-login:hover {
    background: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(52, 152, 219, 0.3);
}

.auth-divider {
    text-align: center;
    margin: 30px 0;
    position: relative;
}

.auth-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e0e0e0;
}

.auth-divider span {
    background: white;
    padding: 0 20px;
    color: #7f8c8d;
    position: relative;
}

.social-login {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.btn-social {
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    background: white;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-social.google:hover {
    background: #f8f9fa;
    border-color: #db4437;
    color: #db4437;
}

.btn-social.facebook:hover {
    background: #f8f9fa;
    border-color: #4267B2;
    color: #4267B2;
}

.auth-footer {
    text-align: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
    color: #7f8c8d;
}

.auth-footer a {
    color: #3498db;
    text-decoration: none;
    font-weight: 600;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.auth-right {
    background: linear-gradient(135deg, #3498db 0%, #2ecc71 100%);
    color: white;
    padding: 60px 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.auth-hero {
    max-width: 500px;
}

.hero-content h2 {
    font-size: 36px;
    margin-bottom: 20px;
    font-weight: 700;
}

.hero-content > p {
    font-size: 18px;
    margin-bottom: 40px;
    opacity: 0.9;
}

.features {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-top: 50px;
}

.feature {
    display: flex;
    align-items: center;
    gap: 20px;
}

.feature i {
    font-size: 32px;
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.feature h4 {
    margin: 0 0 5px 0;
    font-size: 20px;
}

.feature p {
    margin: 0;
    opacity: 0.8;
}
</style>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleButton = document.querySelector('.toggle-password i');
    
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
</script>