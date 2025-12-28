<?php
/**
 * Trang đăng ký - Unified Design
 */
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/header.php';
$pageTitle = __('register');

if (isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = __('session_expired');
    } else {
        $hoTen = sanitize($_POST['ho_ten'] ?? '');
        $tenDangNhap = sanitize($_POST['ten_dang_nhap'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $terms = isset($_POST['terms']);
        
        if (empty($hoTen) || empty($tenDangNhap) || empty($email) || empty($password)) {
            $error = __('fill_all_info');
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $tenDangNhap)) {
            $error = __('username_invalid') ?? 'Tên đăng nhập chỉ chứa chữ cái, số và dấu gạch dưới (3-30 ký tự).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = __('invalid_email');
        } elseif (strlen($password) < 6) {
            $error = __('password_min');
        } elseif ($password !== $confirmPassword) {
            $error = __('password_not_match');
        } elseif (!$terms) {
            $error = __('agree_terms');
        } else {
            $pdo = getDBConnection();
            
            $stmt = $pdo->prepare("SELECT ma_nguoi_dung FROM nguoi_dung WHERE email = ? OR ten_dang_nhap = ?");
            $stmt->execute([$email, $tenDangNhap]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                $stmt2 = $pdo->prepare("SELECT ma_nguoi_dung FROM nguoi_dung WHERE ten_dang_nhap = ?");
                $stmt2->execute([$tenDangNhap]);
                if ($stmt2->fetch()) {
                    $error = __('username_exists') ?? 'Tên đăng nhập này đã được sử dụng.';
                } else {
                    $error = __('email_exists');
                }
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO nguoi_dung (ten_dang_nhap, email, mat_khau, ho_ten, trang_thai, ngay_tao) VALUES (?, ?, ?, ?, 'hoat_dong', NOW())");
                
                if ($stmt->execute([$tenDangNhap, $email, $hashedPassword, $hoTen])) {
                    $userId = $pdo->lastInsertId();
                    // Log activity if function exists and table exists
                    try {
                        if (function_exists('logActivity')) {
                            logActivity($userId, 'register', 'Đăng ký tài khoản mới');
                        }
                    } catch (Exception $e) {
                        // Ignore logging errors - table might not exist
                        error_log("Log activity error: " . $e->getMessage());
                    }
                    redirect(BASE_URL . '/login.php', __('register_success'), 'success');
                } else {
                    $error = __('error_occurred');
                }
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
    <title><?= __('register') ?> - <?= __('site_name') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            width: 100%;
            min-height: 100%;
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
            max-width: 520px;
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
        
        .form-hint {
            font-size: 0.8125rem;
            color: #64748b;
            margin-top: 0.375rem;
            font-weight: 600;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            cursor: pointer;
            margin-bottom: 1.5rem;
        }
        
        .checkbox-wrapper input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #000000;
            cursor: pointer;
            margin-top: 2px;
            flex-shrink: 0;
        }
        
        .checkbox-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #000000;
            cursor: pointer;
            line-height: 1.5;
        }
        
        .checkbox-label a {
            color: #000000;
            text-decoration: underline;
            font-weight: 700;
        }
        
        .checkbox-label a:hover {
            text-decoration: none;
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
            
            .form-row {
                grid-template-columns: 1fr;
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
                <h1 class="auth-title"><?= __('register') ?? 'Đăng ký' ?></h1>
                <p class="auth-subtitle"><?= __('create_account') ?? 'Tạo tài khoản mới' ?></p>
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
                        <label class="form-label"><?= __('full_name') ?? 'Họ và tên' ?></label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" name="ho_ten" class="form-input" 
                                   placeholder="Nguyễn Văn A" required
                                   value="<?= sanitize($_POST['ho_ten'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?= __('username') ?? 'Tên đăng nhập' ?></label>
                        <div class="input-wrapper">
                            <i class="fas fa-at"></i>
                            <input type="text" name="ten_dang_nhap" class="form-input" 
                                   placeholder="nguyenvana" required
                                   pattern="[a-zA-Z0-9_]{3,30}"
                                   value="<?= sanitize($_POST['ten_dang_nhap'] ?? '') ?>">
                        </div>
                        <div class="form-hint"><?= __('username_hint') ?? 'Chỉ chữ cái, số và dấu gạch dưới (3-30 ký tự)' ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?= __('email') ?? 'Email' ?></label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-input" 
                                   placeholder="your@email.com" required
                                   value="<?= sanitize($_POST['email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label"><?= __('password') ?? 'Mật khẩu' ?></label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" class="form-input" 
                                       placeholder="<?= __('min_6_chars') ?? 'Tối thiểu 6 ký tự' ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label"><?= __('confirm_password') ?? 'Xác nhận mật khẩu' ?></label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="confirm_password" class="form-input" 
                                       placeholder="<?= __('retype_password') ?? 'Nhập lại mật khẩu' ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="terms" required>
                        <span class="checkbox-label">
                            <?= __('terms_agree') ?? 'Tôi đồng ý với' ?> 
                            <a href="#"><?= __('terms_of_use') ?? 'Điều khoản sử dụng' ?></a> 
                            <?= __('and') ?? 'và' ?> 
                            <a href="#"><?= __('footer_privacy') ?? 'Chính sách bảo mật' ?></a>
                        </span>
                    </label>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-user-plus"></i>
                        <?= __('register') ?? 'Đăng ký' ?>
                    </button>
                </form>
            </div>
            
            <div class="auth-footer">
                <?= __('have_account') ?? 'Đã có tài khoản?' ?> 
                <a href="<?= BASE_URL ?>/login.php"><?= __('login') ?? 'Đăng nhập ngay' ?></a>
            </div>
        </div>
    </div>
</body>
</html>
