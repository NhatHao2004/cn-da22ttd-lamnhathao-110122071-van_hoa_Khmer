<?php
/**
 * Trang đăng nhập - Unified Design
 */
require_once __DIR__ . '/includes/header.php';
$pageTitle = __('login');

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = __('session_expired');
    } else {
        $tenDangNhap = sanitize($_POST['ten_dang_nhap'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($tenDangNhap) || empty($password)) {
            $error = __('fill_all_info');
        } else {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT * FROM nguoi_dung WHERE ten_dang_nhap = ? AND trang_thai = 'hoat_dong'");
            $stmt->execute([$tenDangNhap]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['mat_khau'])) {
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                
                // Lưu session theo cả 2 cách để đảm bảo tương thích
                $_SESSION['user_id'] = $user['ma_nguoi_dung'];
                $_SESSION['user_name'] = $user['ho_ten'];
                $_SESSION['logged_in'] = true;
                
                // Lưu thông tin user đầy đủ (cho các API cần)
                $_SESSION['user'] = [
                    'ma_nguoi_dung' => $user['ma_nguoi_dung'],
                    'ten_dang_nhap' => $user['ten_dang_nhap'],
                    'ho_ten' => $user['ho_ten'],
                    'email' => $user['email'],
                    'anh_dai_dien' => $user['anh_dai_dien'],
                    'tong_diem' => $user['tong_diem'],
                    'cap_do' => $user['cap_do']
                ];
                
                $stmt = $pdo->prepare("UPDATE nguoi_dung SET lan_dang_nhap_cuoi = NOW() WHERE ma_nguoi_dung = ?");
                $stmt->execute([$user['ma_nguoi_dung']]);
                
                // Log activity (optional - comment out if causing issues)
                try {
                    logActivity($user['ma_nguoi_dung'], 'login', 'Đăng nhập thành công');
                } catch (Exception $e) {
                    // Ignore logging errors
                }
                
                // Set success message and redirect
                $_SESSION['flash_message'] = __('login_success');
                $_SESSION['flash_type'] = 'success';
                
                header('Location: ' . BASE_URL . '/index.php');
                exit;
            } else {
                $error = __('wrong_credentials');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= getCurrentLang() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('login') ?> - <?= __('site_name') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            width: 100%;
            height: 100%;
            background: #ffffff;
        }
        
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #ffffff;
        }
        
        .auth-wrapper {
            width: 100%;
            max-width: 480px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-logo {
            font-size: 3.5rem;
            margin-bottom: 1rem;
        }
        
        .auth-title {
            font-size: 2rem;
            font-weight: 900;
            color: #000000;
            margin-bottom: 0.5rem;
        }
        
        .auth-subtitle {
            font-size: 1rem;
            color: #64748b;
            font-weight: 600;
        }
        
        .auth-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 2px solid #000000;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9375rem;
            font-weight: 600;
        }
        
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 2px solid #dc2626;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            font-size: 0.9375rem;
            font-weight: 700;
            color: #000000;
            margin-bottom: 0.5rem;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #000000;
            font-weight: 600;
        }
        
        .form-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            border: 2px solid #000000;
            border-radius: 12px;
            font-size: 0.9375rem;
            font-weight: 600;
            transition: all 0.3s ease;
            background: #ffffff;
            color: #000000;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #000000;
            background: white;
            box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.1);
        }
        
        .form-input::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .checkbox-wrapper input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #000000;
            cursor: pointer;
        }
        
        .checkbox-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #000000;
            cursor: pointer;
        }
        
        .forgot-link {
            font-size: 0.875rem;
            color: #000000;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }
        
        .btn-submit {
            width: 100%;
            padding: 0.875rem 1.5rem;
            background: #ffffff;
            color: #000000;
            border: 2px solid #000000;
            border-radius: 12px;
            font-size: 0.9375rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background: #000000;
            color: #ffffff;
            transform: translateY(-2px);
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 2px;
            background: #e2e8f0;
        }
        
        .divider span {
            padding: 0 1rem;
        }
        
        .social-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .btn-social {
            flex: 1;
            padding: 0.75rem;
            border: 2px solid #000000;
            border-radius: 12px;
            background: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            color: #000000;
        }
        
        .btn-social:hover {
            background: #000000;
            color: #ffffff;
        }
        
        .btn-social.google i { color: #ea4335; }
        .btn-social.facebook i { color: #1877f2; }
        
        .btn-social:hover i {
            color: #ffffff;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: #64748b;
            font-size: 0.9375rem;
            font-weight: 600;
        }
        
        .auth-footer a {
            color: #000000;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .back-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
            padding: 0.75rem 1.25rem;
            background: #ffffff;
            color: #000000;
            border: 2px solid #000000;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }
        
        .back-home:hover {
            background: #000000;
            color: #ffffff;
        }
        
        @media (max-width: 640px) {
            .auth-container {
                padding: 1.5rem;
            }
            
            .auth-card {
                padding: 2rem 1.5rem;
            }
            
            .auth-title {
                font-size: 1.75rem;
            }
            
            .social-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-wrapper">
            <a href="<?= BASE_URL ?>/index.php" class="back-home">
                <i class="fas fa-arrow-left"></i> <?= __('home') ?? 'Trang chủ' ?>
            </a>
            
            <div class="auth-header">
                <div class="auth-logo">🏛️</div>
                <h1 class="auth-title"><?= __('login') ?? 'Đăng nhập' ?></h1>
                <p class="auth-subtitle"><?= __('welcome_back') ?? 'Chào mừng bạn trở lại' ?></p>
            </div>
            
            <div class="auth-card">
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="form-group">
                        <label class="form-label"><?= __('username') ?? 'Tên đăng nhập' ?></label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" name="ten_dang_nhap" class="form-input" 
                                   placeholder="<?= __('username') ?? 'Tên đăng nhập' ?>" required
                                   value="<?= sanitize($_POST['ten_dang_nhap'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?= __('password') ?? 'Mật khẩu' ?></label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" class="form-input" 
                                   placeholder="••••••••" required>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" name="remember">
                            <span class="checkbox-label"><?= __('remember_me') ?? 'Ghi nhớ' ?></span>
                        </label>
                        <a href="<?= BASE_URL ?>/forgot-password.php" class="forgot-link">
                            <?= __('forgot_password') ?? 'Quên mật khẩu?' ?>
                        </a>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-sign-in-alt"></i>
                        <?= __('login') ?? 'Đăng nhập' ?>
                    </button>
                </form>
                
                <div class="divider"><span><?= __('or') ?? 'Hoặc' ?></span></div>
                
                <div class="social-buttons">
                    <button class="btn-social google">
                        <i class="fab fa-google"></i> Google
                    </button>
                    <button class="btn-social facebook">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </button>
                </div>
            </div>
            
            <div class="auth-footer">
                <?= __('no_account') ?? 'Chưa có tài khoản?' ?> 
                <a href="<?= BASE_URL ?>/register.php"><?= __('register') ?? 'Đăng ký ngay' ?></a>
            </div>
        </div>
    </div>
</body>
</html>
