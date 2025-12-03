<?php
/**
 * Đăng xuất admin
 * File này xử lý việc đăng xuất an toàn
 */

// Bắt đầu session
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/auth.php';

// Kiểm tra có đang đăng nhập không
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: dangnhap.php');
    exit;
}

// Xác nhận đăng xuất (tránh CSRF)
if(isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    logoutAdmin();
} else {
    // Hiển thị trang xác nhận đăng xuất
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Xác nhận đăng xuất - Admin</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
            
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                padding: 20px;
                position: relative;
                overflow: hidden;
            }
            
            /* Animated background circles */
            body::before {
                content: '';
                position: absolute;
                width: 500px;
                height: 500px;
                background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
                border-radius: 50%;
                top: -200px;
                right: -200px;
                animation: float 6s ease-in-out infinite;
            }
            
            body::after {
                content: '';
                position: absolute;
                width: 400px;
                height: 400px;
                background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
                border-radius: 50%;
                bottom: -150px;
                left: -150px;
                animation: float 8s ease-in-out infinite reverse;
            }
            
            @keyframes float {
                0%, 100% { transform: translateY(0) rotate(0deg); }
                50% { transform: translateY(-20px) rotate(5deg); }
            }
            
            .logout-container {
                position: relative;
                z-index: 1;
                animation: slideUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(50px) scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }
            
            .logout-box {
                background: white;
                padding: 50px 45px;
                border-radius: 28px;
                box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
                text-align: center;
                max-width: 480px;
                width: 100%;
                position: relative;
            }
            
            .logout-icon-wrapper {
                width: 120px;
                height: 120px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 28px;
                box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
                animation: pulse 2s ease-in-out infinite;
                position: relative;
            }
            
            .logout-icon-wrapper::before {
                content: '';
                position: absolute;
                width: 100%;
                height: 100%;
                border-radius: 50%;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                opacity: 0.3;
                animation: ripple 2s ease-out infinite;
            }
            
            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }
            
            @keyframes ripple {
                0% {
                    transform: scale(1);
                    opacity: 0.3;
                }
                100% {
                    transform: scale(1.4);
                    opacity: 0;
                }
            }
            
            .logout-icon {
                font-size: 3rem;
                color: white;
                position: relative;
                z-index: 1;
            }
            
            h2 {
                font-size: 2rem;
                font-weight: 900;
                margin-bottom: 16px;
                color: #1e293b;
                letter-spacing: -0.5px;
            }
            
            .logout-description {
                color: #94a3b8;
                font-size: 1.05rem;
                margin-bottom: 50px;
                line-height: 1.7;
                font-weight: 500;
            }
            
            .button-group {
                display: flex;
                gap: 14px;
                margin-top: 32px;
            }
            
            .btn {
                flex: 1;
                padding: 18px 32px;
                border: none;
                border-radius: 16px;
                font-weight: 700;
                font-size: 1.05rem;
                cursor: pointer;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                position: relative;
                overflow: hidden;
            }
            
            .btn::before {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: translate(-50%, -50%);
                transition: width 0.6s, height 0.6s;
            }
            
            .btn:hover::before {
                width: 300px;
                height: 300px;
            }
            
            .btn i {
                font-size: 1.2rem;
                position: relative;
                z-index: 1;
            }
            
            .btn span {
                position: relative;
                z-index: 1;
                white-space: nowrap;
            }
            
            .btn-cancel {
                background: #f1f5f9;
                color: #475569;
                border: 2px solid #e2e8f0;
            }
            
            .btn-cancel:hover {
                background: #e2e8f0;
                transform: translateY(-3px);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            }
            
            .btn-cancel:active {
                transform: translateY(-1px);
            }
            
            .btn-logout {
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                color: white;
                box-shadow: 0 8px 20px rgba(239, 68, 68, 0.35);
            }
            
            .btn-logout:hover {
                transform: translateY(-3px);
                box-shadow: 0 12px 30px rgba(239, 68, 68, 0.5);
            }
            
            .btn-logout:active {
                transform: translateY(-1px);
            }
            
            /* User info display */
            .user-info {
                background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
                padding: 20px;
                border-radius: 16px;
                margin-bottom: 32px;
                display: flex;
                align-items: center;
                gap: 16px;
                border: 2px solid rgba(102, 126, 234, 0.15);
            }
            
            .user-avatar {
                width: 56px;
                height: 56px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.5rem;
                font-weight: 800;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            }
            
            .user-details {
                text-align: left;
                flex: 1;
            }
            
            .user-name {
                font-size: 1.1rem;
                font-weight: 700;
                color: #1e293b;
                margin-bottom: 4px;
            }
            
            .user-role {
                font-size: 0.85rem;
                color: #64748b;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 6px;
            }
            
            /* Responsive */
            @media (max-width: 480px) {
                .logout-box {
                    padding: 40px 30px;
                }
                
                h2 {
                    font-size: 1.6rem;
                }
                
                .logout-icon-wrapper {
                    width: 100px;
                    height: 100px;
                }
                
                .logout-icon {
                    font-size: 2.5rem;
                }
                
                .button-group {
                    flex-direction: column;
                }
                
                .btn {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class="logout-container">
            <div class="logout-box">
                <div class="logout-icon-wrapper">
                    <i class="fas fa-sign-out-alt logout-icon"></i>
                </div>
                
                <h2>Xác nhận đăng xuất</h2>
                <p class="logout-description">Bạn có chắc chắn muốn đăng xuất?</p>
                
                <div class="button-group">
                    <a href="index.php" class="btn btn-cancel">
                        <i class="fas fa-times"></i>
                        <span>Hủy</span>
                    </a>
                    <a href="dangxuat.php?confirm=yes" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Đăng xuất</span>
                    </a>
                </div>
            </div>
        </div>
        
        <script>
            // Add keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // ESC to cancel
                if(e.key === 'Escape') {
                    window.location.href = 'index.php';
                }
                // Enter to confirm logout
                if(e.key === 'Enter') {
                    window.location.href = 'dangxuat.php?confirm=yes';
                }
            });
            
            // Add ripple effect on button click
            document.querySelectorAll('.btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.5);
                        left: ${x}px;
                        top: ${y}px;
                        pointer-events: none;
                        animation: ripple-effect 0.6s ease-out;
                    `;
                    
                    this.appendChild(ripple);
                    setTimeout(() => ripple.remove(), 600);
                });
            });
            
            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple-effect {
                    from {
                        transform: scale(0);
                        opacity: 1;
                    }
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        </script>
    </body>
    </html>
    <?php
    exit;
}
