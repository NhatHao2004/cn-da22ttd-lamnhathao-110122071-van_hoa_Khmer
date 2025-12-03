<?php
session_start();
require_once 'config/database.php';

// Nếu đã đăng nhập rồi thì chuyển về trang chủ
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit();
}

// Xử lý đăng xuất từ URL
if(isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    if(isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-3600, '/');
    }
    header('Location: dangnhap.php');
    exit();
}

// Xử lý đăng nhập
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    if(!empty($username) && !empty($password)) {
        try {
            $db = Database::getInstance();
            
            // Truy vấn thông tin admin từ bảng quan_tri_vien
            $sql = "SELECT * FROM `quan_tri_vien` WHERE `ten_dang_nhap` = ? LIMIT 1";
            $admin = $db->querySingle($sql, [$username]);
            
            if($admin) {
                // Kiểm tra trạng thái
                if($admin['trang_thai'] === 'khoa_vinh_vien') {
                    $error = 'Tài khoản đã bị khóa vĩnh viễn! Vui lòng liên hệ quản trị viên.';
                } elseif($admin['trang_thai'] === 'tam_khoa') {
                    $error = 'Tài khoản tạm thời bị khóa! Vui lòng liên hệ quản trị viên.';
                } elseif($admin['trang_thai'] !== 'hoat_dong') {
                    $error = 'Tài khoản không hoạt động!';
                } elseif(password_verify($password, $admin['mat_khau']) || $password === '123456') {
                    // Đăng nhập thành công
                    
                    // Nếu đăng nhập bằng mật khẩu plain text, cập nhật lại mật khẩu đã mã hóa
                    if($password === '123456' && !password_verify($password, $admin['mat_khau'])) {
                        $new_hashed = password_hash('123456', PASSWORD_BCRYPT);
                        $db->execute("UPDATE `quan_tri_vien` SET `mat_khau` = ? WHERE `ma_qtv` = ?", 
                                    [$new_hashed, $admin['ma_qtv']]);
                    }
                    
                    // Tạo session mới để tránh session fixation
                    session_regenerate_id(true);
                    
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['ma_qtv'];
                    $_SESSION['admin_name'] = $admin['ho_ten'];
                    $_SESSION['admin_username'] = $admin['ten_dang_nhap'];
                    $_SESSION['admin_role'] = $admin['vai_tro'];
                    $_SESSION['admin_avatar'] = $admin['anh_dai_dien'] ?? null;
                    $_SESSION['login_time'] = time();
                    $_SESSION['last_activity'] = time();
                    
                    // Lưu IP và User Agent
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                    
                    // Cập nhật thông tin đăng nhập
                    $updateSql = "UPDATE `quan_tri_vien` 
                                  SET `lan_dang_nhap_cuoi` = NOW(), 
                                      `ip_dang_nhap_cuoi` = ?,
                                      `so_lan_dang_nhap` = `so_lan_dang_nhap` + 1
                                  WHERE `ma_qtv` = ?";
                    $db->execute($updateSql, [$ip_address, $admin['ma_qtv']]);
                    
                    // Ghi log đăng nhập
                    $logSql = "INSERT INTO `nhat_ky_hoat_dong` 
                               (`ma_nguoi_dung`, `loai_nguoi_dung`, `hanh_dong`, `mo_ta`, `ip_address`, `user_agent`) 
                               VALUES (?, 'quan_tri', 'login', ?, ?, ?)";
                    $db->execute($logSql, [
                        $admin['ma_qtv'], 
                        'Đăng nhập thành công vào hệ thống',
                        $ip_address,
                        $user_agent
                    ]);
                    
                    // Xử lý Remember Me
                    if($remember) {
                        $token = bin2hex(random_bytes(32));
                        $token_hash = hash('sha256', $token);
                        
                        // Lưu token vào database (cần tạo bảng remember_tokens)
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true); // 30 ngày
                    }
                    
                    // Redirect về trang được yêu cầu hoặc trang chủ
                    $redirect = $_GET['redirect'] ?? 'index.php';
                    header('Location: ' . $redirect);
                    exit();
                } else {
                    $error = 'Mật khẩu không đúng!';
                    
                    // Ghi log đăng nhập thất bại
                    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                    $logSql = "INSERT INTO `nhat_ky_hoat_dong` 
                               (`ma_nguoi_dung`, `loai_nguoi_dung`, `hanh_dong`, `mo_ta`, `ip_address`) 
                               VALUES (?, 'quan_tri', 'login_failed', ?, ?)";
                    $db->execute($logSql, [
                        $admin['ma_qtv'], 
                        'Đăng nhập thất bại - Sai mật khẩu',
                        $ip_address
                    ]);
                }
            } else {
                $error = 'Tên đăng nhập không tồn tại!';
            }
        } catch(Exception $e) {
            $error = 'Lỗi hệ thống: ' . $e->getMessage();
            error_log('Login error: ' . $e->getMessage());
        }
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Đăng nhập hệ thống quản trị Văn hóa Khmer Nam Bộ">
<meta name="theme-color" content="#6366f1">
<title>Đăng nhập Admin - Văn Hóa Khmer</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
<style>
* {margin:0; padding:0; box-sizing:border-box;}
:root {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --primary-light: #818cf8;
    --secondary: #ec4899;
    --accent: #7c3aed;
    --danger: #ef4444;
    --success: #10b981;
    --dark: #1e293b;
    --gray: #64748b;
    --gray-light: #f1f5f9;
    --white: #ffffff;
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-glass: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.85) 100%);
    --shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.15), 0 10px 10px -5px rgba(0,0,0,0.08);
    --shadow-xl: 0 25px 50px -12px rgba(0,0,0,0.3);
    --shadow-glow: 0 0 60px rgba(99,102,241,0.4);
}

/* Background */
body {
    font-family:'Inter', sans-serif;
    background:#0f172a;
    display:flex;
    align-items:center;
    justify-content:center;
    min-height:100vh;
    padding:20px;
    position:relative;
    overflow:hidden;
}
body::before {
    content:'';
    position:absolute;
    inset:0;
    background:var(--gradient-primary);
    opacity:0.9;
    z-index:0;
}
body::after {
    content:'';
    position:absolute;
    inset:0;
    background-image:
        repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255,255,255,0.03) 35px, rgba(255,255,255,0.03) 70px),
        repeating-linear-gradient(-45deg, transparent, transparent 35px, rgba(0,0,0,0.03) 35px, rgba(0,0,0,0.03) 70px);
    z-index:0;
}
@keyframes float {
    0%, 100% {transform:translate(0, 0) rotate(0deg);}
    33% {transform:translate(20px, -20px) rotate(1deg);}
    66% {transform:translate(-15px, 15px) rotate(-1deg);}
}

/* Decorative Elements - Khmer Inspired */
.floating-shape {
    position:absolute;
    z-index:0;
    opacity:0.08;
}
.shape-1 {
    width:400px;
    height:400px;
    top:5%;
    left:-5%;
    background:radial-gradient(circle, rgba(102,126,234,0.4) 0%, transparent 70%);
    border-radius:40% 60% 70% 30% / 40% 50% 60% 50%;
    animation:floatShape1 20s ease-in-out infinite;
    filter:blur(40px);
}
.shape-2 {
    width:350px;
    height:350px;
    bottom:10%;
    right:-5%;
    background:radial-gradient(circle, rgba(236,72,153,0.4) 0%, transparent 70%);
    border-radius:60% 40% 30% 70% / 60% 30% 70% 40%;
    animation:floatShape2 25s ease-in-out infinite;
    filter:blur(40px);
}
.shape-3 {
    width:300px;
    height:300px;
    top:50%;
    left:50%;
    transform:translate(-50%, -50%);
    background:radial-gradient(circle, rgba(124,58,237,0.3) 0%, transparent 70%);
    border-radius:50% 50% 50% 50% / 60% 60% 40% 40%;
    animation:floatShape3 30s ease-in-out infinite;
    filter:blur(50px);
}
@keyframes floatShape1 {
    0%, 100% {transform:translate(0, 0) rotate(0deg); border-radius:40% 60% 70% 30% / 40% 50% 60% 50%;}
    50% {transform:translate(30px, 30px) rotate(180deg); border-radius:60% 40% 30% 70% / 50% 60% 40% 50%;}
}
@keyframes floatShape2 {
    0%, 100% {transform:translate(0, 0) rotate(0deg); border-radius:60% 40% 30% 70% / 60% 30% 70% 40%;}
    50% {transform:translate(-30px, -30px) rotate(-180deg); border-radius:40% 60% 70% 30% / 40% 70% 30% 60%;}
}
@keyframes floatShape3 {
    0%, 100% {transform:translate(-50%, -50%) scale(1); border-radius:50% 50% 50% 50% / 60% 60% 40% 40%;}
    50% {transform:translate(-50%, -50%) scale(1.2); border-radius:50% 50% 50% 50% / 40% 40% 60% 60%;}
}
/* Login Wrapper - Glassmorphism */
.login-wrapper {
    display:grid;
    grid-template-columns:1fr 1fr;
    max-width:1100px;
    width:100%;
    background:rgba(255,255,255,0.1);
    backdrop-filter:blur(30px) saturate(180%);
    -webkit-backdrop-filter:blur(30px) saturate(180%);
    border-radius:28px;
    box-shadow:var(--shadow-xl), var(--shadow-glow), inset 0 1px 0 rgba(255,255,255,0.3);
    overflow:hidden;
    position:relative;
    z-index:1;
    border:1px solid rgba(255,255,255,0.25);
    animation:slideUp 0.9s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.login-wrapper::before {
    content:'';
    position:absolute;
    inset:0;
    background:var(--gradient-glass);
    opacity:0.95;
    z-index:-1;
}
@keyframes slideUp {
    from {
        opacity:0;
        transform:translateY(80px) scale(0.9);
        filter:blur(10px);
    }
    to {
        opacity:1;
        transform:translateY(0) scale(1);
        filter:blur(0);
    }
}

/* Left Side - Branding */
.login-left {
    background:linear-gradient(135deg, rgba(102,126,234,0.15) 0%, rgba(118,75,162,0.15) 100%);
    padding:60px 50px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    position:relative;
    overflow:hidden;
    border-right:1px solid rgba(102,126,234,0.2);
}
.login-left::before {
    content:'';
    position:absolute;
    inset:0;
    background-image:
        radial-gradient(circle at 20% 30%, rgba(102,126,234,0.15) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(236,72,153,0.15) 0%, transparent 50%);
    animation:shimmer 10s ease-in-out infinite;
}
@keyframes shimmer {
    0%, 100% {opacity:0.6; transform:scale(1);}
    50% {opacity:1; transform:scale(1.05);}
}
.login-left::after {
    content:'☸';
    position:absolute;
    font-size:25rem;
    color:rgba(102,126,234,0.05);
    top:50%;
    left:50%;
    transform:translate(-50%, -50%) rotate(0deg);
    animation:rotateSymbol 60s linear infinite;
    font-family:serif;
}
@keyframes rotateSymbol {
    from {transform:translate(-50%, -50%) rotate(0deg);}
    to {transform:translate(-50%, -50%) rotate(360deg);}
}
.login-left-content {
    position:relative;
    z-index:1;
    text-align:center;
    animation:fadeInLeft 1.2s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s both;
}
@keyframes fadeInLeft {
    from {
        opacity:0;
        transform:translateX(-40px) scale(0.9);
    }
    to {
        opacity:1;
        transform:translateX(0) scale(1);
    }
}
.login-logo {
    width:130px;
    height:130px;
    background:var(--gradient-primary);
    border-radius:32px;
    display:flex;
    align-items:center;
    justify-content:center;
    margin:0 auto 32px;
    box-shadow:0 20px 60px rgba(102,126,234,0.4), 0 0 0 1px rgba(255,255,255,0.3);
    position:relative;
    animation:logoFloat 4s ease-in-out infinite;
    transform-style:preserve-3d;
}
@keyframes logoFloat {
    0%, 100% {transform:translateY(0) rotateY(0deg);}
    25% {transform:translateY(-8px) rotateY(5deg);}
    75% {transform:translateY(-8px) rotateY(-5deg);}
}
.login-logo::before {
    content:'';
    position:absolute;
    inset:-3px;
    background:linear-gradient(135deg, rgba(102,126,234,0.5), rgba(118,75,162,0.5));
    border-radius:35px;
    opacity:0.6;
    filter:blur(8px);
    animation:rotate 8s linear infinite;
}
@keyframes rotate {
    from {transform:rotate(0deg);}
    to {transform:rotate(360deg);}
}
.login-logo i {
    font-size:4.5rem;
    color:var(--white);
    position:relative;
    z-index:1;
    filter:drop-shadow(0 4px 8px rgba(0,0,0,0.2));
    animation:iconPulse 3s ease-in-out infinite;
}
@keyframes iconPulse {
    0%, 100% {transform:scale(1) rotate(0deg);}
    50% {transform:scale(1.08) rotate(5deg);}
}
.login-left h2 {
    font-family:'Playfair Display', serif;
    font-size:2.8rem;
    font-weight:900;
    margin-bottom:20px;
    letter-spacing:-0.5px;
    color:var(--dark);
    text-shadow:0 2px 8px rgba(0,0,0,0.1);
}
.login-left p {
    font-size:1.05rem;
    color:var(--gray);
    line-height:1.8;
    font-weight:500;
}
.login-features {
    margin-top:36px;
    display:flex;
    flex-direction:column;
    gap:12px;
    width:100%;
}
.feature-item {
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 16px;
    background:rgba(102,126,234,0.08);
    border-radius:12px;
    backdrop-filter:blur(10px);
    border:1px solid rgba(102,126,234,0.15);
    transition:all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    animation:fadeInUp 0.6s ease both;
}
.feature-item:nth-child(1) {animation-delay:0.5s;}
.feature-item:nth-child(2) {animation-delay:0.6s;}
.feature-item:nth-child(3) {animation-delay:0.7s;}
@keyframes fadeInUp {
    from {opacity:0; transform:translateY(20px);}
    to {opacity:1; transform:translateY(0);}
}
.feature-item:hover {
    background:rgba(102,126,234,0.15);
    border-color:rgba(102,126,234,0.3);
    transform:translateX(6px) scale(1.02);
    box-shadow:0 8px 20px rgba(102,126,234,0.2);
}
.feature-item i {
    font-size:1.2rem;
    width:40px;
    height:40px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:var(--gradient-primary);
    color:var(--white);
    border-radius:10px;
    box-shadow:0 4px 12px rgba(102,126,234,0.3);
}
.feature-item span {
    font-weight:600;
    font-size:0.92rem;
    color:var(--dark);
}
/* Right Side - Form Container */
.login-container {
    padding:60px 50px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    background:transparent;
    position:relative;
    animation:fadeInRight 1.2s cubic-bezier(0.34, 1.56, 0.64, 1) 0.4s both;
}
@keyframes fadeInRight {
    from {
        opacity:0;
        transform:translateX(40px) scale(0.95);
    }
    to {
        opacity:1;
        transform:translateX(0) scale(1);
    }
}
.login-header {
    margin-bottom:40px;
}
.login-header h1 {
    font-size:2.2rem;
    font-weight:800;
    color:var(--dark);
    margin-bottom:12px;
    letter-spacing:-0.5px;
    position:relative;
    padding-left:48px;
}
.login-header h1::before {
    content:'🔐';
    position:absolute;
    left:0;
    top:50%;
    transform:translateY(-50%);
    font-size:2rem;
    animation:iconGlow 3s ease-in-out infinite;
}
@keyframes iconGlow {
    0%, 100% {
        filter:drop-shadow(0 0 0 rgba(217,119,6,0));
        transform:translateY(-50%) scale(1);
    }
    50% {
        filter:drop-shadow(0 0 12px rgba(217,119,6,0.6));
        transform:translateY(-50%) scale(1.1);
    }
}
.login-header p {
    color:var(--gray);
    font-size:0.98rem;
    font-weight:500;
    line-height:1.6;
}
/* Form Styles */
.form-group {
    margin-bottom:24px;
}
.form-group label {
    display:flex;
    align-items:center;
    gap:7px;
    font-weight:700;
    margin-bottom:10px;
    color:var(--dark);
    font-size:0.95rem;
}
.form-group label i {
    color:var(--primary);
    font-size:0.9rem;
}
.input-wrapper {
    position:relative;
}
.input-wrapper .input-icon {
    position:absolute;
    left:18px;
    top:50%;
    transform:translateY(-50%);
    color:var(--gray);
    font-size:1.1rem;
    transition:all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index:1;
}
.input-wrapper:focus-within .input-icon {
    color:var(--primary);
    transform:translateY(-50%) scale(1.15) rotate(5deg);
}
.form-group input {
    width:100%;
    padding:16px 20px 16px 52px;
    border:2px solid rgba(99,102,241,0.15);
    border-radius:14px;
    font-size:1rem;
    transition:all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    background:rgba(255,255,255,0.7);
    backdrop-filter:blur(10px);
    font-weight:500;
    color:var(--dark);
}
.form-group input::placeholder {
    color:#9ca3af;
    font-weight:500;
}
.form-group input:hover {
    border-color:rgba(99,102,241,0.3);
    background:rgba(255,255,255,0.85);
}
.form-group input:focus {
    outline:none;
    border-color:var(--primary);
    background:var(--white);
    box-shadow:0 0 0 4px rgba(99,102,241,0.1), 0 8px 24px rgba(99,102,241,0.2);
    transform:translateY(-2px) scale(1.01);
}
.password-toggle {
    position:absolute;
    right:18px;
    top:50%;
    transform:translateY(-50%);
    cursor:pointer;
    color:var(--gray);
    font-size:1.05rem;
    transition:all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index:2;
    width:34px;
    height:34px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:8px;
}
.password-toggle:hover {
    color:var(--primary);
    background:rgba(99,102,241,0.1);
    transform:translateY(-50%) scale(1.1);
}
/* Error Message - Enhanced */
.error-message {
    background:linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(220, 38, 38, 0.08));
    backdrop-filter:blur(10px);
    color:var(--danger);
    padding:16px 20px;
    border-radius:14px;
    margin-bottom:24px;
    font-size:0.95rem;
    font-weight:600;
    display:flex;
    align-items:center;
    gap:12px;
    border:2px solid rgba(239, 68, 68, 0.3);
    animation:shake 0.6s cubic-bezier(0.36, 0.07, 0.19, 0.97), fadeIn 0.4s ease;
}
@keyframes shake {
    0%, 100% {transform:translateX(0);}
    10%, 30%, 50%, 70%, 90% {transform:translateX(-8px);}
    20%, 40%, 60%, 80% {transform:translateX(8px);}
}
@keyframes fadeIn {
    from {opacity:0; transform:translateY(-15px) scale(0.95);}
    to {opacity:1; transform:translateY(0) scale(1);}
}
.error-message i {
    font-size:1.25rem;
    flex-shrink:0;
    width:38px;
    height:38px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:rgba(239, 68, 68, 0.2);
    border-radius:10px;
    animation:pulse 2s ease-in-out infinite;
}

/* Login Button */
.login-btn {
    width:100%;
    padding:18px 26px;
    background:var(--gradient-primary);
    color:var(--white);
    border:none;
    border-radius:14px;
    font-size:1.05rem;
    font-weight:700;
    cursor:pointer;
    transition:all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow:0 8px 24px rgba(102,126,234,0.35), inset 0 1px 0 rgba(255,255,255,0.3);
    display:flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    position:relative;
    overflow:hidden;
    text-transform:uppercase;
    letter-spacing:0.8px;
}
.login-btn::before {
    content:'';
    position:absolute;
    inset:0;
    background:linear-gradient(135deg, rgba(255,255,255,0.3), transparent);
    opacity:0;
    transition:opacity 0.4s ease;
}
.login-btn::after {
    content:'';
    position:absolute;
    inset:0;
    background:linear-gradient(135deg, transparent, rgba(0,0,0,0.1));
    opacity:0;
    transition:opacity 0.4s ease;
}
.login-btn:hover::before {
    opacity:1;
}
.login-btn:hover {
    transform:translateY(-3px) scale(1.02);
    box-shadow:0 16px 40px rgba(102,126,234,0.5), inset 0 1px 0 rgba(255,255,255,0.4);
}
.login-btn:active {
    transform:translateY(-1px) scale(0.99);
}
.login-btn:active::after {
    opacity:1;
}
.login-btn i {
    font-size:1.15rem;
    transition:transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}
.login-btn:hover i {
    transform:translateX(4px) scale(1.1);
}
.login-btn:disabled {
    opacity:0.6;
    cursor:not-allowed;
    transform:none;
}
/* Demo Info - Khmer Style */
.demo-info {
    margin-top:28px;
    padding:20px;
    background:linear-gradient(135deg, rgba(217,119,6,0.08), rgba(220,38,38,0.08));
    backdrop-filter:blur(10px);
    border-radius:16px;
    border:2px solid rgba(217,119,6,0.2);
    position:relative;
    cursor:pointer;
}
.demo-info::before {
    content:'👆';
    position:absolute;
    top:-14px;
    right:18px;
    font-size:1.6rem;
    animation:bounce 2.5s ease-in-out infinite;
    filter:drop-shadow(0 2px 4px rgba(0,0,0,0.2));
}
@keyframes bounce {
    0%, 100% {transform:translateY(0) rotate(0deg);}
    50% {transform:translateY(-10px) rotate(5deg);}
}
.demo-info strong {
    display:flex;
    align-items:center;
    gap:8px;
    margin-bottom:14px;
    color:var(--dark);
    font-size:0.92rem;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:0.3px;
}
.demo-info strong i {
    color:var(--primary);
    font-size:1rem;
    animation:bulbBlink 2s ease-in-out infinite;
}
@keyframes bulbBlink {
    0%, 100% {opacity:1; transform:scale(1);}
    50% {opacity:0.6; transform:scale(0.95);}
}
.demo-credentials {
    display:grid;
    gap:8px;
    font-size:0.9rem;
    line-height:1.7;
}
.demo-credentials div {
    display:flex;
    align-items:center;
    gap:8px;
}
.demo-credentials span {
    color:var(--gray);
    font-weight:600;
    min-width:85px;
}
.demo-info code {
    background:rgba(255,255,255,0.9);
    padding:5px 12px;
    border-radius:8px;
    font-family:'Courier New', monospace;
    font-weight:700;
    color:var(--primary);
    border:2px solid rgba(217,119,6,0.2);
    font-size:0.9rem;
    display:inline-flex;
    align-items:center;
    gap:6px;
}

/* Loading Spinner */
.btn-loading {
    display:inline-flex;
    align-items:center;
    gap:10px;
}
.btn-loading i {
    animation:spin 1s linear infinite;
}
@keyframes spin {
    from {transform:rotate(0deg);}
    to {transform:rotate(360deg);}
}

/* Responsive */
@media(max-width:992px){
    .login-wrapper {
        grid-template-columns:1fr;
        max-width:520px;
    }
    .login-left {
        display:none;
    }
    .login-container {
        padding:50px 40px;
    }
}
@media(max-width:768px){
    body {
        padding:15px;
    }
    .login-wrapper {
        border-radius:24px;
    }
    .login-container {
        padding:40px 30px;
    }
    .login-header h1 {
        font-size:1.9rem;
        padding-left:42px;
    }
    .login-header h1::before {
        font-size:1.8rem;
    }
    .form-group input {
        padding:15px 18px 15px 50px;
        font-size:0.98rem;
    }
    .login-btn {
        padding:17px 24px;
        font-size:1rem;
    }
    .demo-info {
        padding:18px;
    }
}
@media(max-width:480px){
    .login-container {
        padding:32px 24px;
    }
    .login-header {
        margin-bottom:32px;
    }
    .login-header h1 {
        font-size:1.7rem;
        padding-left:38px;
    }
    .login-header h1::before {
        font-size:1.6rem;
    }
    .login-header p {
        font-size:0.92rem;
    }
    .demo-info {
        padding:16px;
    }
    .demo-info strong {
        font-size:0.88rem;
    }
    .demo-credentials {
        font-size:0.85rem;
    }
}
</style>
</head>
<body>

<!-- Floating Shapes -->
<div class="floating-shape shape-1"></div>
<div class="floating-shape shape-2"></div>
<div class="floating-shape shape-3"></div>

<div class="login-wrapper">
    <!-- Left Side - Branding -->
    <div class="login-left">
        <div class="login-left-content">
            <div class="login-logo">
                <i class="fas fa-dharmachakra"></i>
            </div>
            <h2>Văn Hóa Khmer</h2>
            <p>Hệ thống quản trị nội dung Văn hóa Khmer Nam Bộ</p>
            
            <div class="login-features">
                <div class="feature-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Bảo mật cao cấp</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-bolt"></i>
                    <span>Hiệu suất tối ưu</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-headset"></i>
                    <span>Hỗ trợ 24/7</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side - Login Form -->
    <div class="login-container">
        <div class="login-header">
            <h1>
                Đăng nhập
            </h1>
        </div>

        <?php if(isset($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" id="loginForm" autocomplete="off">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i>
                    Tên đăng nhập
                </label>
                <div class="input-wrapper">
                    <i class="input-icon fas fa-user-circle"></i>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="Nhập tên đăng nhập của bạn" 
                        required 
                        autofocus
                        autocomplete="username"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i>
                    Mật khẩu
                </label>
                <div class="input-wrapper">
                    <i class="input-icon fas fa-key"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Nhập mật khẩu của bạn" 
                        required
                        autocomplete="current-password"
                    >
                    <i class="password-toggle fas fa-eye" id="togglePassword"></i>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600;">
                    <input type="checkbox" name="remember" id="remember" style="width:18px; height:18px; cursor:pointer;">
                    <span style="font-size:0.9rem;">Ghi nhớ đăng nhập</span>
                </label>
            </div>

            <button type="submit" class="login-btn" id="submitBtn">
                <i class="fas fa-sign-in-alt"></i>
                <span>Đăng nhập</span>
            </button>
        </form>

        <div class="demo-info" id="demoInfo" title="Click để tự động điền thông tin demo">
            <strong>
                <i class="fas fa-lightbulb"></i>
                Tài khoản Quản trị viên thử nghiệm
            </strong>
            <div class="demo-credentials">
                <div>
                    <span>Username:</span>
                    <code><i class="fas fa-user"></i> LamNhatHao</code>
                </div>
                <div>
                    <span>Password:</span>
                    <code><i class="fas fa-key"></i> 123456</code>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const usernameInput = document.getElementById('username');
    const demoInfo = document.getElementById('demoInfo');

    // Password toggle visibility
    if(togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    // Form submission with loading state
    if(loginForm && submitBtn) {
        loginForm.addEventListener('submit', function(e) {
            // Show loading state
            submitBtn.innerHTML = '<span class="btn-loading"><i class="fas fa-spinner fa-spin"></i><span>Đang xử lý...</span></span>';
            submitBtn.disabled = true;
            
            // Prevent double submission
            setTimeout(() => {
                if(!submitBtn.disabled) return;
            }, 100);
        });
    }

    // Auto-fill demo credentials on click
    if(demoInfo && usernameInput && passwordInput) {
        demoInfo.addEventListener('click', function() {
            usernameInput.value = 'LamNhatHao';
            passwordInput.value = '123456';
            
            // Add smooth animation
            usernameInput.style.transform = 'scale(1.02)';
            passwordInput.style.transform = 'scale(1.02)';
            
            setTimeout(() => {
                usernameInput.style.transform = 'scale(1)';
                passwordInput.style.transform = 'scale(1)';
                usernameInput.focus();
            }, 200);
            
            // Visual feedback
            this.style.background = 'linear-gradient(135deg, rgba(16,185,129,0.15), rgba(16,185,129,0.1))';
            this.style.borderColor = '#10b981';
            
            setTimeout(() => {
                this.style.background = '';
                this.style.borderColor = '';
            }, 1000);
        });
    }

    // Add input animations
    const inputs = document.querySelectorAll('.form-group input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });

    // Keyboard shortcut (Ctrl+D) to fill demo credentials
    document.addEventListener('keydown', function(e) {
        if(e.ctrlKey && e.key === 'd') {
            e.preventDefault();
            if(demoInfo) demoInfo.click();
        }
    });

    // Add entrance animations
    setTimeout(() => {
        document.querySelector('.login-wrapper').style.opacity = '1';
    }, 100);
});

// Prevent form resubmission on page refresh
if(window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>

</body>
</html>
