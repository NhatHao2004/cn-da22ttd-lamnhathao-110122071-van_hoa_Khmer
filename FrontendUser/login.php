<?php
/**
 * Trang đăng nhập - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();

// Nếu đã đăng nhập, chuyển về trang chủ
if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/database.php';

$pageTitle = 'Đăng nhập';
$error = '';
$success = '';

// Xử lý đăng nhập
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validation
    if(empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        $db = Database::getInstance();
        
        // Tìm user theo email
        $user = $db->querySingle(
            "SELECT * FROM nguoi_dung WHERE email = ? AND vai_tro = 'hoc_vien'",
            [$email]
        );
        
        if($user && password_verify($password, $user['mat_khau'])) {
            // Kiểm tra trạng thái
            if($user['trang_thai'] !== 'hoat_dong') {
                $error = 'Tài khoản của bạn đã bị khóa';
            } else {
                // Đăng nhập thành công
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['ho_ten'] = $user['ho_ten'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['avatar'] = $user['avatar'];
                
                // Cập nhật lần đăng nhập cuối
                $db->execute(
                    "UPDATE nguoi_dung SET lan_dang_nhap_cuoi = NOW() WHERE id = ?",
                    [$user['id']]
                );
                
                // Remember me
                if($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (86400 * 30), '/');
                    
                    $db->execute(
                        "UPDATE nguoi_dung SET remember_token = ? WHERE id = ?",
                        [$token, $user['id']]
                    );
                }
                
                // Redirect
                $redirect = $_GET['redirect'] ?? 'index.php';
                header('Location: ' . $redirect);
                exit;
            }
        } else {
            $error = 'Email hoặc mật khẩu không chính xác';
        }
    }
}

include 'includes/header.php';
?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fas fa-dharmachakra"></i>
                </div>
                <h1>Đăng nhập</h1>
                <p>Chào mừng trở lại! Đăng nhập để tiếp tục học tập</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form" id="loginForm">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="example@email.com"
                        value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Mật khẩu
                    </label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="••••••••"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <span>Ghi nhớ đăng nhập</span>
                    </label>
                    <a href="forgot-password.php" class="text-link">Quên mật khẩu?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i>
                    Đăng nhập
                </button>
            </form>
            
            <div class="auth-divider">
                <span>Hoặc đăng nhập với</span>
            </div>
            
            <div class="social-login">
                <button class="btn-social btn-google" onclick="alert('Tính năng đang phát triển')">
                    <i class="fab fa-google"></i>
                    Google
                </button>
                <button class="btn-social btn-facebook" onclick="alert('Tính năng đang phát triển')">
                    <i class="fab fa-facebook-f"></i>
                    Facebook
                </button>
            </div>
            
            <div class="auth-footer">
                <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
            </div>
        </div>
        
        <!-- Auth Side Banner -->
        <div class="auth-banner">
            <div class="auth-banner-content">
                <h2>Khám phá Văn Hóa Khmer</h2>
                <p>Tham gia cộng đồng để học tiếng Khmer, khám phá chùa chiền, lễ hội và bảo tồn văn hóa truyền thống</p>
                <div class="auth-banner-features">
                    <div class="feature">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Học tiếng Khmer miễn phí</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-trophy"></i>
                        <span>Nhận huy hiệu & điểm thưởng</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-users"></i>
                        <span>Kết nối cộng đồng</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.auth-section {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 120px 24px 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
}

.auth-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
}

.auth-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    max-width: 1000px;
    width: 100%;
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    position: relative;
    z-index: 1;
}

.auth-card {
    padding: 48px;
}

.auth-header {
    text-align: center;
    margin-bottom: 32px;
}

.auth-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border-radius: 20px;
    font-size: 36px;
    color: white;
}

.auth-header h1 {
    font-size: 28px;
    margin-bottom: 8px;
}

.auth-header p {
    color: var(--gray-600);
    font-size: 14px;
}

.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
}

.alert-error {
    background: #fee;
    color: var(--danger);
    border: 1px solid #fcc;
}

.alert-success {
    background: #efe;
    color: var(--success);
    border: 1px solid #cfc;
}

.auth-form {
    margin-bottom: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    margin-bottom: 8px;
    color: var(--gray-700);
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--gray-200);
    border-radius: 8px;
    font-size: 15px;
    transition: var(--transition-base);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.password-input {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: var(--gray-500);
    cursor: pointer;
    padding: 4px;
}

.form-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
}

.text-link {
    color: var(--primary);
    font-size: 14px;
    font-weight: 500;
}

.text-link:hover {
    text-decoration: underline;
}

.btn-block {
    width: 100%;
}

.auth-divider {
    text-align: center;
    margin: 24px 0;
    position: relative;
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 40%;
    height: 1px;
    background: var(--gray-200);
}

.auth-divider::before { left: 0; }
.auth-divider::after { right: 0; }

.auth-divider span {
    background: white;
    padding: 0 16px;
    color: var(--gray-500);
    font-size: 14px;
}

.social-login {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 24px;
}

.btn-social {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    border: 2px solid var(--gray-200);
    border-radius: 8px;
    background: white;
    font-weight: 500;
    transition: var(--transition-base);
}

.btn-social:hover {
    border-color: var(--gray-300);
    background: var(--gray-50);
}

.btn-google { color: #EA4335; }
.btn-facebook { color: #1877F2; }

.auth-footer {
    text-align: center;
    padding-top: 24px;
    border-top: 1px solid var(--gray-200);
}

.auth-footer a {
    color: var(--primary);
    font-weight: 600;
}

.auth-banner {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    padding: 48px;
    display: flex;
    align-items: center;
    color: white;
    position: relative;
    overflow: hidden;
}

.auth-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 30px 30px;
}

.auth-banner-content {
    position: relative;
    z-index: 1;
}

.auth-banner h2 {
    font-size: 32px;
    color: white;
    margin-bottom: 16px;
}

.auth-banner p {
    font-size: 16px;
    line-height: 1.6;
    opacity: 0.95;
    margin-bottom: 32px;
}

.auth-banner-features {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.auth-banner-features .feature {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 15px;
}

.auth-banner-features .feature i {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    font-size: 18px;
}

@media (max-width: 768px) {
    .auth-container {
        grid-template-columns: 1fr;
    }
    
    .auth-card {
        padding: 32px 24px;
    }
    
    .auth-banner {
        display: none;
    }
    
    .social-login {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector('.toggle-password i');
    
    if(input.type === 'password') {
        input.type = 'text';
        button.classList.remove('fa-eye');
        button.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        button.classList.remove('fa-eye-slash');
        button.classList.add('fa-eye');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
