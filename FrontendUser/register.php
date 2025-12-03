<?php
/**
 * Trang đăng ký - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();

// Nếu đã đăng nhập, chuyển về trang chủ
if(isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'config/database.php';

$pageTitle = 'Đăng ký';
$error = '';
$success = '';

// Xử lý đăng ký
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten = trim($_POST['ho_ten'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agree = isset($_POST['agree']);
    
    // Validation
    if(empty($ho_ten) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ';
    } elseif(strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự';
    } elseif($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } elseif(!$agree) {
        $error = 'Vui lòng đồng ý với điều khoản sử dụng';
    } else {
        $db = Database::getInstance();
        
        // Kiểm tra email đã tồn tại
        $existing = $db->querySingle(
            "SELECT id FROM nguoi_dung WHERE email = ?",
            [$email]
        );
        
        if($existing) {
            $error = 'Email đã được sử dụng';
        } else {
            // Tạo tài khoản mới
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $success = $db->execute(
                "INSERT INTO nguoi_dung (ho_ten, email, mat_khau, vai_tro, trang_thai, ngay_tao) 
                 VALUES (?, ?, ?, 'hoc_vien', 'hoat_dong', NOW())",
                [$ho_ten, $email, $hashed_password]
            );
            
            if($success) {
                $success = 'Đăng ký thành công! Vui lòng đăng nhập.';
                
                // Tự động đăng nhập
                $user = $db->querySingle(
                    "SELECT * FROM nguoi_dung WHERE email = ?",
                    [$email]
                );
                
                if($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['ho_ten'] = $user['ho_ten'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['avatar'] = $user['avatar'];
                    
                    header('Location: index.php');
                    exit;
                }
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại';
            }
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
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1>Đăng ký tài khoản</h1>
                <p>Tạo tài khoản để bắt đầu hành trình khám phá văn hóa Khmer</p>
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
            
            <form method="POST" class="auth-form" id="registerForm">
                <div class="form-group">
                    <label for="ho_ten">
                        <i class="fas fa-user"></i>
                        Họ và tên
                    </label>
                    <input 
                        type="text" 
                        id="ho_ten" 
                        name="ho_ten" 
                        placeholder="Nguyễn Văn A"
                        value="<?php echo htmlspecialchars($ho_ten ?? ''); ?>"
                        required
                    >
                </div>
                
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
                            placeholder="Ít nhất 6 ký tự"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength" id="passwordStrength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i>
                        Xác nhận mật khẩu
                    </label>
                    <div class="password-input">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Nhập lại mật khẩu"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <label class="checkbox-label" style="margin-bottom: 24px;">
                    <input type="checkbox" name="agree" required>
                    <span>Tôi đồng ý với <a href="terms.php" class="text-link">Điều khoản sử dụng</a> và <a href="privacy.php" class="text-link">Chính sách bảo mật</a></span>
                </label>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i>
                    Đăng ký
                </button>
            </form>
            
            <div class="auth-divider">
                <span>Hoặc đăng ký với</span>
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
                <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
            </div>
        </div>
        
        <!-- Auth Side Banner -->
        <div class="auth-banner">
            <div class="auth-banner-content">
                <h2>Tham gia cộng đồng</h2>
                <p>Hơn 10,000+ học viên đang khám phá và học tập văn hóa Khmer cùng chúng tôi</p>
                <div class="auth-banner-features">
                    <div class="feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Khóa học tiếng Khmer miễn phí</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Khám phá chùa & lễ hội</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Bản đồ di sản tương tác</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Truyện dân gian hấp dẫn</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Huy hiệu & xếp hạng</span>
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
    max-height: 90vh;
    overflow-y: auto;
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

.form-group input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--gray-200);
    border-radius: 8px;
    font-size: 15px;
    transition: var(--transition-base);
}

.form-group input:focus {
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

.password-strength {
    margin-top: 8px;
    height: 4px;
    background: var(--gray-200);
    border-radius: 2px;
    overflow: hidden;
}

.password-strength::after {
    content: '';
    display: block;
    height: 100%;
    width: 0;
    background: var(--danger);
    transition: var(--transition-base);
}

.password-strength.weak::after {
    width: 33%;
    background: var(--danger);
}

.password-strength.medium::after {
    width: 66%;
    background: var(--warning);
}

.password-strength.strong::after {
    width: 100%;
    background: var(--success);
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
}

.text-link {
    color: var(--primary);
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
    background: linear-gradient(135deg, #10b981, #059669);
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
    width: 24px;
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

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('passwordStrength');
    
    if(password.length === 0) {
        strengthDiv.className = 'password-strength';
        return;
    }
    
    let strength = 0;
    if(password.length >= 6) strength++;
    if(password.length >= 10) strength++;
    if(/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if(/\d/.test(password)) strength++;
    if(/[^a-zA-Z\d]/.test(password)) strength++;
    
    if(strength <= 2) {
        strengthDiv.className = 'password-strength weak';
    } else if(strength <= 4) {
        strengthDiv.className = 'password-strength medium';
    } else {
        strengthDiv.className = 'password-strength strong';
    }
});
</script>

<?php include 'includes/footer.php'; ?>
