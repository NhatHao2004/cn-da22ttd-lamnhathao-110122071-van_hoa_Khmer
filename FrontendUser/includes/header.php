<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Khám phá và bảo tồn văn hóa Khmer Nam Bộ - Chùa, lễ hội, ngôn ngữ và truyền thống">
    <meta name="keywords" content="Khmer, văn hóa, chùa, lễ hội, tiếng Khmer, Nam Bộ">
    <meta name="author" content="Văn Hóa Khmer Nam Bộ">
    
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Văn Hóa Khmer Nam Bộ</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    
    <!-- Preconnect for Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        /* Premium Design System - Tourism & Culture & Education */
        :root {
            /* Luxury Gradient Colors */
            --gradient-royal: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e22ce 100%);
            --gradient-gold: linear-gradient(135deg, #d4af37 0%, #f4d03f 50%, #c5a028 100%);
            --gradient-emerald: linear-gradient(135deg, #0c4a6e 0%, #0891b2 50%, #06b6d4 100%);
            --gradient-sunset: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 50%, #c92a2a 100%);
            --gradient-glass: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            
            /* Primary Palette - Royal Blue & Gold */
            --primary: #2a5298;
            --primary-dark: #1e3c72;
            --primary-light: #4a7ac2;
            --secondary: #d4af37;
            --secondary-dark: #c5a028;
            --secondary-light: #f4d03f;
            --accent: #0891b2;
            --accent-dark: #0c4a6e;
            
            /* Semantic Colors */
            --success: #059669;
            --danger: #dc2626;
            --warning: #d97706;
            --info: #0891b2;
            
            /* Neutral Palette */
            --white: #ffffff;
            --black: #0a0a0a;
            --gray-50: #fafafa;
            --gray-100: #f5f5f5;
            --gray-200: #e5e5e5;
            --gray-300: #d4d4d4;
            --gray-400: #a3a3a3;
            --gray-500: #737373;
            --gray-600: #525252;
            --gray-700: #404040;
            --gray-800: #262626;
            --gray-900: #171717;
            
            /* Premium Shadows & Glows */
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.15);
            --shadow-xl: 0 16px 48px rgba(0, 0, 0, 0.2);
            --shadow-2xl: 0 24px 64px rgba(0, 0, 0, 0.25);
            --glow-primary: 0 0 30px rgba(42, 82, 152, 0.5);
            --glow-gold: 0 0 30px rgba(212, 175, 55, 0.5);
            --glow-white: 0 0 20px rgba(255, 255, 255, 0.6);
            
            /* Smooth Transitions */
            --transition-fast: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-base: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-smooth: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.7;
            color: var(--gray-800);
            background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
            overflow-x: hidden;
            font-size: 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            padding-top: 85px;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            line-height: 1.3;
            color: var(--gray-900);
            letter-spacing: -0.03em;
        }
        
        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition-base);
        }
        
        img {
            max-width: 100%;
            height: auto;
            display: block;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 32px;
        }
        
        /* Premium Glassmorphism Navbar - Tourism & Culture Design */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(30px) saturate(180%);
            -webkit-backdrop-filter: blur(30px) saturate(180%);
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            box-shadow: 0 8px 32px rgba(30, 60, 114, 0.12);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateY(0);
        }
        
        /* Golden Top Border Animation */
        .navbar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-gold);
            opacity: 0.8;
            box-shadow: var(--glow-gold);
        }
        
        /* Royal Bottom Glow Effect */
        .navbar::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            height: 20px;
            background: radial-gradient(ellipse at center, rgba(42, 82, 152, 0.15) 0%, transparent 70%);
            filter: blur(10px);
            opacity: 0;
            transition: var(--transition-base);
        }
        
        .navbar:hover::after {
            opacity: 1;
        }
        
        /* Container Layout: Logo Left - Menu Center - CTA Right */
        .navbar-container {
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
            padding: 0 2.5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 85px;
            position: relative;
        }
        
        /* Logo Section - Left Side */
        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: clamp(20px, 2vw, 28px);
            font-weight: 800;
            font-family: 'Poppins', sans-serif;
            background: var(--gradient-royal);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: var(--transition-base);
            white-space: nowrap;
            letter-spacing: -0.8px;
            flex-shrink: 0;
        }
        
        .navbar-logo:hover {
            transform: translateY(-2px);
            filter: drop-shadow(var(--glow-primary));
        }
        
        .navbar-logo i {
            font-size: clamp(32px, 3vw, 40px);
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: spin-elegant 20s linear infinite;
            filter: drop-shadow(var(--glow-gold));
        }
        
        @keyframes spin-elegant {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Menu Section - Center */
        .navbar-menu {
            display: flex;
            list-style: none;
            gap: 4px;
            justify-content: center;
            align-items: center;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            max-width: 50%;
            flex-wrap: nowrap;
        }
        
        .navbar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px clamp(12px, 1.5vw, 24px);
            border-radius: 16px;
            font-size: clamp(14px, 1vw, 15.5px);
            font-weight: 600;
            color: var(--gray-700);
            transition: var(--transition-base);
            position: relative;
            white-space: nowrap;
            overflow: hidden;
            letter-spacing: -0.2px;
        }
        
        /* Glassmorphism Hover Background */
        .navbar-menu a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            opacity: 0;
            transition: var(--transition-base);
            border-radius: 16px;
            box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.5);
        }
        
        .navbar-menu a:hover::before,
        .navbar-menu a.active::before {
            opacity: 1;
        }
        
        /* Golden Bottom Border with Glow */
        .navbar-menu a::after {
            content: '';
            position: absolute;
            bottom: 8px;
            left: 50%;
            transform: translateX(-50%) scaleX(0);
            width: 50%;
            height: 3px;
            background: var(--gradient-gold);
            border-radius: 3px;
            box-shadow: var(--glow-gold);
            transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .navbar-menu a:hover, 
        .navbar-menu a.active {
            color: var(--black);
            background: rgba(42, 82, 152, 0.1);
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(42, 82, 152, 0.2), var(--glow-primary);
        }
        
        .navbar-menu a:hover::after,
        .navbar-menu a.active::after {
            transform: translateX(-50%) scaleX(1);
        }
        
        .navbar-menu a i {
            font-size: clamp(16px, 1.2vw, 18px);
            transition: var(--transition-base);
            background: var(--gradient-royal);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .navbar-menu a:hover i,
        .navbar-menu a.active i {
            transform: scale(1.2) rotate(5deg);
            background: var(--gradient-gold);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* CTA Actions - Right Side */
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-shrink: 0;
        }
        
        /* Premium Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px clamp(16px, 2vw, 28px);
            border-radius: 16px;
            font-size: clamp(12px, 0.9vw, 13.5px);
            font-weight: 700;
            border: 2px solid transparent;
            cursor: pointer;
            transition: var(--transition-base);
            text-decoration: none;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }
        
        .btn:hover::before {
            width: 400px;
            height: 400px;
        }
        
        .btn:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-2xl);
        }
        
        .btn:active {
            transform: translateY(-2px);
        }
        
        /* Royal Blue Primary Button with Gold Glow */
        .btn-primary {
            background: var(--gradient-royal);
            color: white;
            box-shadow: 0 6px 24px rgba(42, 82, 152, 0.4);
            border: 1px solid rgba(212, 175, 55, 0.3);
        }
        
        .btn-primary:hover {
            box-shadow: 0 12px 40px rgba(42, 82, 152, 0.5), var(--glow-primary);
        }
        
        /* Golden Secondary Button with Black Text */
        .btn-secondary {
            background: var(--gradient-gold);
            color: var(--black);
            box-shadow: 0 6px 24px rgba(212, 175, 55, 0.5);
            font-weight: 800;
        }
        
        .btn-secondary:hover {
            box-shadow: 0 12px 40px rgba(212, 175, 55, 0.7), var(--glow-gold);
            color: var(--black);
        }
        
        /* Glass Outline Button */
        .btn-outline {
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
            color: var(--primary-dark);
            border: 2px solid var(--primary);
            box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.3);
        }
        
        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.9);
            color: var(--black);
            border-color: var(--secondary);
            box-shadow: var(--glow-gold);
        }
        
        /* Search Button with Premium Glass Effect */
        .btn-search {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(42, 82, 152, 0.2);
            border-radius: 16px;
            color: var(--primary);
            cursor: pointer;
            transition: var(--transition-base);
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.5);
        }
        
        .btn-search::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: var(--gradient-royal);
            transform: translate(-50%, -50%);
            transition: width 0.5s, height 0.5s;
            opacity: 0.8;
        }
        
        .btn-search:hover::before {
            width: 120px;
            height: 120px;
        }
        
        .btn-search:hover {
            background: rgba(255, 255, 255, 0.95);
            color: var(--black);
            border-color: var(--secondary);
            transform: translateY(-4px) rotate(90deg);
            box-shadow: 0 8px 28px rgba(212, 175, 55, 0.4), var(--glow-gold);
        }
        
        /* Mobile Menu Button with Glass Effect */
        .btn-mobile-menu {
            display: none;
            width: 52px;
            height: 52px;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(42, 82, 152, 0.2);
            border-radius: 16px;
            color: var(--primary);
            cursor: pointer;
            transition: var(--transition-base);
            box-shadow: inset 0 0 20px rgba(255, 255, 255, 0.5);
        }
        
        .btn-mobile-menu:hover {
            background: rgba(255, 255, 255, 0.95);
            color: var(--black);
            border-color: var(--secondary);
            transform: scale(1.08);
            box-shadow: var(--glow-gold);
        }
        
        /* User Dropdown with Premium Style */
        .user-dropdown a:hover {
            background: rgba(42, 82, 152, 0.1);
            transform: translateX(6px);
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .navbar-menu a {
                padding: 12px 16px;
                gap: 8px;
            }
        }
        
        @media (max-width: 1024px) {
            .navbar-menu {
                display: none;
            }
            
            .btn-mobile-menu {
                display: flex;
            }
            
            .navbar-container {
                height: 75px;
                padding: 0 3%;
            }
            
            .navbar-actions {
                gap: 10px;
            }
            
            body {
                padding-top: 75px;
            }
        }
        
        @media (max-width: 768px) {
            .navbar-container {
                padding: 0 4%;
                height: 70px;
            }
            
            .btn {
                padding: 12px 16px;
                gap: 6px;
            }
            
            .btn span {
                display: none;
            }
            
            .btn i {
                margin: 0;
            }
            
            body {
                padding-top: 70px;
            }
        }
    </style>
</head>
<body>
    <!-- Premium Glassmorphism Navbar - Tourism & Culture -->
    <nav class="navbar">
        <div class="navbar-container">
            <!-- Logo - Left Side with Gold Icon -->
            <a href="index.php" class="navbar-logo">
                <i class="fas fa-dharmachakra"></i>
                <span>Văn Hóa Khmer</span>
            </a>
            
            <!-- Navigation Menu - Center -->
            <ul class="navbar-menu">
                <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Trang chủ</span>
                </a></li>
                <li><a href="van-hoa.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'van-hoa.php' || basename($_SERVER['PHP_SELF']) == 'van-hoa-chi-tiet.php' ? 'active' : ''; ?>">
                    <i class="fas fa-book-open"></i>
                    <span>Văn hóa</span>
                </a></li>
                <li><a href="chua-khmer.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'chua-khmer.php' || basename($_SERVER['PHP_SELF']) == 'chua-khmer-chi-tiet.php' ? 'active' : ''; ?>">
                    <i class="fas fa-place-of-worship"></i>
                    <span>Chùa Khmer</span>
                </a></li>
                <li><a href="le-hoi.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'le-hoi.php' || basename($_SERVER['PHP_SELF']) == 'le-hoi-chi-tiet.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-days"></i>
                    <span>Lễ hội</span>
                </a></li>
                <li><a href="hoc-tieng-khmer.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'hoc-tieng-khmer.php' || basename($_SERVER['PHP_SELF']) == 'bai-hoc-chi-tiet.php' ? 'active' : ''; ?>">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Học tiếng</span>
                </a></li>
                <li><a href="truyen-dan-gian.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'truyen-dan-gian.php' || basename($_SERVER['PHP_SELF']) == 'truyen-chi-tiet.php' ? 'active' : ''; ?>">
                    <i class="fas fa-book-bookmark"></i>
                    <span>Truyện</span>
                </a></li>
            </ul>
            
            <!-- CTA Buttons - Right Side -->
            <div class="navbar-actions">
                <button class="btn-search" id="searchBtn" aria-label="Tìm kiếm" title="Tìm kiếm (Ctrl+K)">
                    <i class="fas fa-magnifying-glass"></i>
                </button>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- User Menu with Premium Design -->
                    <div class="user-menu" style="position: relative;">
                        <button class="btn btn-primary" id="userMenuBtn" style="position: relative; z-index: 10;">
                            <img src="<?php echo isset($_SESSION['avatar']) && $_SESSION['avatar'] ? $_SESSION['avatar'] : 'assets/images/default-avatar.png'; ?>" 
                                 alt="Avatar"
                                 style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(212, 175, 55, 0.6); box-shadow: 0 2px 12px rgba(0,0,0,0.15);">
                            <span><?php echo htmlspecialchars($_SESSION['ho_ten']); ?></span>
                            <i class="fas fa-chevron-down" style="font-size: 12px; transition: var(--transition-base);"></i>
                        </button>
                        <div class="user-dropdown" id="userDropdown" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 14px; min-width: 260px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border-radius: 18px; box-shadow: var(--shadow-2xl); padding: 12px; z-index: 1001; border: 2px solid rgba(212, 175, 55, 0.2);">
                            <a href="profile.php" style="display: flex; align-items: center; gap: 16px; padding: 16px 20px; border-radius: 14px; color: var(--gray-700); transition: var(--transition-base); font-weight: 600;">
                                <div style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: var(--gradient-royal); border-radius: 12px; color: white;">
                                    <i class="fas fa-user-circle" style="font-size: 20px;"></i>
                                </div>
                                <span>Trang cá nhân</span>
                                <i class="fas fa-arrow-right" style="margin-left: auto; font-size: 12px; color: var(--gray-400);"></i>
                            </a>
                            <a href="settings.php" style="display: flex; align-items: center; gap: 16px; padding: 16px 20px; border-radius: 14px; color: var(--gray-700); transition: var(--transition-base); font-weight: 600;">
                                <div style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: var(--gradient-emerald); border-radius: 12px; color: white;">
                                    <i class="fas fa-cog" style="font-size: 20px;"></i>
                                </div>
                                <span>Cài đặt</span>
                                <i class="fas fa-arrow-right" style="margin-left: auto; font-size: 12px; color: var(--gray-400);"></i>
                            </a>
                            <div style="height: 2px; background: var(--gradient-gold); margin: 12px 0; border-radius: 2px; opacity: 0.3;"></div>
                            <a href="logout.php" style="display: flex; align-items: center; gap: 16px; padding: 16px 20px; border-radius: 14px; color: white; background: var(--gradient-sunset); transition: var(--transition-base); font-weight: 700; box-shadow: 0 4px 16px rgba(238, 90, 111, 0.3);">
                                <i class="fas fa-sign-out-alt" style="font-size: 20px;"></i> 
                                <span>Đăng xuất</span>
                                <i class="fas fa-arrow-right" style="margin-left: auto; font-size: 12px;"></i>
                            </a>
                        </div>
                    </div>
                    <style>
                        .user-dropdown a:hover {
                            background: rgba(102, 126, 234, 0.08);
                            transform: translateX(4px);
                        }
                        #userMenuBtn:hover .fa-chevron-down {
                            transform: rotate(180deg);
                        }
                    </style>
                <?php else: ?>
                    <!-- Guest CTA Buttons -->
                    <a href="login.php" class="btn btn-outline">
                        <i class="fas fa-arrow-right-to-bracket"></i>
                        <span>Đăng nhập</span>
                    </a>
                    <a href="register.php" class="btn btn-secondary">
                        <i class="fas fa-sparkles"></i>
                        <span>Đăng ký ngay</span>
                    </a>
                <?php endif; ?>
                
                <!-- Mobile Menu Toggle -->
                <button class="btn-mobile-menu" id="mobileMenuToggle" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>
    
    <!-- Modern Mobile Menu with Gradient -->
    <div class="mobile-overlay" id="mobileOverlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 998; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); animation: fadeIn 0.3s ease;"></div>
    <div class="mobile-menu" id="mobileMenu" style="position: fixed; top: 0; right: -100%; bottom: 0; width: 340px; max-width: 90%; background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%); z-index: 999; transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1); overflow-y: auto; box-shadow: var(--shadow-2xl); border-left: 2px solid rgba(102, 126, 234, 0.1);">
        <div class="mobile-menu-header" style="display: flex; justify-content: space-between; align-items: center; padding: 24px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.05) 100%); border-bottom: 2px solid rgba(102, 126, 234, 0.1);">
            <div class="navbar-logo" style="font-size: 22px; filter: none;">
                <i class="fas fa-dharmachakra"></i>
                <span>Khmer Nam Bộ</span>
            </div>
            <button class="mobile-menu-close" id="closeMobileMenu" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; background: rgba(102, 126, 234, 0.15); border: none; border-radius: 12px; cursor: pointer; color: var(--primary); transition: var(--transition-base); font-size: 18px;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <nav class="mobile-menu-nav" style="padding: 20px;">
            <a href="index.php" style="display: flex; align-items: center; gap: 16px; padding: 16px 18px; border-radius: 14px; color: var(--gray-700); font-weight: 600; transition: var(--transition-base); margin-bottom: 6px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <div style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: var(--gradient-primary); border-radius: 10px; color: white; font-size: 18px;">
                    <i class="fas fa-home"></i>
                </div>
                <span style="flex: 1;">Trang chủ</span>
                <i class="fas fa-chevron-right" style="font-size: 14px; color: var(--gray-400);"></i>
            </a>
            <a href="van-hoa.php" style="display: flex; align-items: center; gap: 16px; padding: 16px 18px; border-radius: 14px; color: var(--gray-700); font-weight: 600; transition: var(--transition-base); margin-bottom: 6px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <div style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: var(--gradient-primary); border-radius: 10px; color: white; font-size: 18px;">
                    <i class="fas fa-book-open"></i>
                </div>
                <span style="flex: 1;">Văn hóa</span>
                <i class="fas fa-chevron-right" style="font-size: 14px; color: var(--gray-400);"></i>
            </a>
            <a href="chua-khmer.php" style="display: flex; align-items: center; gap: 16px; padding: 16px 18px; border-radius: 14px; color: var(--gray-700); font-weight: 600; transition: var(--transition-base); margin-bottom: 6px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <div style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: var(--gradient-gold); border-radius: 10px; color: white; font-size: 18px;">
                    <i class="fas fa-place-of-worship"></i>
                </div>
                <span style="flex: 1;">Chùa Khmer</span>
                <i class="fas fa-chevron-right" style="font-size: 14px; color: var(--gray-400);"></i>
            </a>
            <a href="le-hoi.php" style="display: flex; align-items: center; gap: 16px; padding: 16px 18px; border-radius: 14px; color: var(--gray-700); font-weight: 600; transition: var(--transition-base); margin-bottom: 6px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <div style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: var(--gradient-secondary); border-radius: 10px; color: white; font-size: 18px;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <span style="flex: 1;">Lễ hội</span>
                <i class="fas fa-chevron-right" style="font-size: 14px; color: var(--gray-400);"></i>
            </a>
            <a href="hoc-tieng-khmer.php" style="display: flex; align-items: center; gap: 16px; padding: 16px 18px; border-radius: 14px; color: var(--gray-700); font-weight: 600; transition: var(--transition-base); margin-bottom: 6px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <div style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: var(--gradient-success); border-radius: 10px; color: white; font-size: 18px;">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <span style="flex: 1;">Học tiếng Khmer</span>
                <i class="fas fa-chevron-right" style="font-size: 14px; color: var(--gray-400);"></i>
            </a>
            <a href="truyen-dan-gian.php" style="display: flex; align-items: center; gap: 16px; padding: 16px 18px; border-radius: 14px; color: var(--gray-700); font-weight: 600; transition: var(--transition-base); margin-bottom: 6px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <div style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: var(--gradient-primary); border-radius: 10px; color: white; font-size: 18px;">
                    <i class="fas fa-book"></i>
                </div>
                <span style="flex: 1;">Truyện dân gian</span>
                <i class="fas fa-chevron-right" style="font-size: 14px; color: var(--gray-400);"></i>
            </a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <div style="height: 2px; background: var(--gradient-primary); margin: 20px 0; border-radius: 2px; opacity: 0.2;"></div>
                <a href="profile.php" style="display: flex; align-items: center; gap: 16px; padding: 16px 18px; border-radius: 14px; color: var(--gray-700); font-weight: 600; transition: var(--transition-base); margin-bottom: 6px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <div style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: var(--gradient-primary); border-radius: 10px; color: white; font-size: 18px;">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span style="flex: 1;">Trang cá nhân</span>
                    <i class="fas fa-chevron-right" style="font-size: 14px; color: var(--gray-400);"></i>
                </a>
                <a href="settings.php" style="display: flex; align-items: center; gap: 16px; padding: 16px 18px; border-radius: 14px; color: var(--gray-700); font-weight: 600; transition: var(--transition-base); margin-bottom: 6px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <div style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #a8a8a8 0%, #6b6b6b 100%); border-radius: 10px; color: white; font-size: 18px;">
                        <i class="fas fa-cog"></i>
                    </div>
                    <span style="flex: 1;">Cài đặt</span>
                    <i class="fas fa-chevron-right" style="font-size: 14px; color: var(--gray-400);"></i>
                </a>
                <a href="logout.php" style="display: flex; align-items: center; gap: 16px; padding: 16px 18px; border-radius: 14px; color: white; font-weight: 600; transition: var(--transition-base); background: var(--gradient-secondary); box-shadow: 0 4px 12px rgba(245, 87, 108, 0.3);">
                    <div style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.25); border-radius: 10px; color: white; font-size: 18px;">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span style="flex: 1;">Đăng xuất</span>
                    <i class="fas fa-arrow-right" style="font-size: 14px;"></i>
                </a>
            <?php else: ?>
                <div style="height: 2px; background: var(--gradient-primary); margin: 20px 0; border-radius: 2px; opacity: 0.2;"></div>
                <a href="login.php" style="display: flex; align-items: center; gap: 16px; padding: 16px 18px; border-radius: 14px; color: var(--gray-700); font-weight: 600; transition: var(--transition-base); margin-bottom: 12px; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.04); border: 2px solid var(--primary);">
                    <div style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: rgba(102, 126, 234, 0.15); border-radius: 10px; color: var(--primary); font-size: 18px;">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <span style="flex: 1; color: var(--primary);">Đăng nhập</span>
                    <i class="fas fa-arrow-right" style="font-size: 14px; color: var(--primary);"></i>
                </a>
                <a href="register.php" style="display: flex; align-items: center; justify-content: center; gap: 12px; padding: 18px; background: var(--gradient-primary); color: white; font-weight: 700; border-radius: 14px; text-align: center; box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4); font-size: 16px;">
                    <i class="fas fa-user-plus" style="font-size: 18px;"></i>
                    <span>Đăng ký ngay</span>
                    <i class="fas fa-sparkles" style="font-size: 16px;"></i>
                </a>
            <?php endif; ?>
        </nav>
    </div>
    
    <style>
        .mobile-menu-nav a:hover {
            transform: translateX(6px) scale(1.02);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12) !important;
        }
        .mobile-menu-close:hover {
            background: var(--primary);
            color: white;
            transform: rotate(90deg);
        }
    </style>
    
    <!-- Modern Search Modal with Gradient -->
    <div class="search-modal" id="searchModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(18, 24, 43, 0.85); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); z-index: 2000; animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
        <div class="search-modal-content" style="max-width: 860px; margin: 100px auto; padding: 0 28px; position: relative;">
            <button class="search-close" id="closeSearch" style="position: absolute; top: -16px; right: 40px; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(245, 87, 108, 0.9) 0%, rgba(240, 93, 168, 0.9) 100%); backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.3); border-radius: 50%; color: white; cursor: pointer; transition: var(--transition-base); font-size: 22px; box-shadow: 0 8px 24px rgba(245, 87, 108, 0.4);">
                <i class="fas fa-times"></i>
            </button>
            
            <!-- Search Box with Gradient Border -->
            <div class="search-box-wrapper" style="padding: 3px; background: var(--gradient-primary); border-radius: 24px; box-shadow: var(--shadow-2xl), 0 0 40px rgba(102, 126, 234, 0.3); margin-bottom: 28px;">
                <div class="search-box" style="background: white; padding: 28px 32px; border-radius: 21px; display: flex; align-items: center; gap: 20px;">
                    <div style="width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; background: var(--gradient-primary); border-radius: 14px; flex-shrink: 0;">
                        <i class="fas fa-search" style="font-size: 22px; color: white;"></i>
                    </div>
                    <input type="text" id="searchInput" placeholder="Tìm kiếm văn hóa, chùa, lễ hội, bài học, truyện..." autocomplete="off" style="flex: 1; font-size: 19px; border: none; outline: none; font-family: 'Outfit', sans-serif; color: var(--gray-800); font-weight: 500;">
                    <div style="display: flex; gap: 8px; flex-shrink: 0;">
                        <kbd style="padding: 6px 12px; background: var(--gray-100); border-radius: 8px; font-size: 13px; font-weight: 600; color: var(--gray-600); border: 1px solid var(--gray-300);">Ctrl</kbd>
                        <kbd style="padding: 6px 12px; background: var(--gray-100); border-radius: 8px; font-size: 13px; font-weight: 600; color: var(--gray-600); border: 1px solid var(--gray-300);">K</kbd>
                    </div>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 24px; padding: 0 4px;">
                <span style="color: rgba(255,255,255,0.7); font-size: 14px; font-weight: 600;">Tìm kiếm nhanh:</span>
                <button onclick="document.getElementById('searchInput').value='văn hóa'" style="padding: 8px 16px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; color: white; font-size: 13px; font-weight: 600; cursor: pointer; transition: var(--transition-base);">
                    <i class="fas fa-book-open" style="margin-right: 6px;"></i>Văn hóa
                </button>
                <button onclick="document.getElementById('searchInput').value='chùa'" style="padding: 8px 16px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; color: white; font-size: 13px; font-weight: 600; cursor: pointer; transition: var(--transition-base);">
                    <i class="fas fa-place-of-worship" style="margin-right: 6px;"></i>Chùa
                </button>
                <button onclick="document.getElementById('searchInput').value='lễ hội'" style="padding: 8px 16px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; color: white; font-size: 13px; font-weight: 600; cursor: pointer; transition: var(--transition-base);">
                    <i class="fas fa-calendar-alt" style="margin-right: 6px;"></i>Lễ hội
                </button>
                <button onclick="document.getElementById('searchInput').value='học tiếng khmer'" style="padding: 8px 16px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; color: white; font-size: 13px; font-weight: 600; cursor: pointer; transition: var(--transition-base);">
                    <i class="fas fa-graduation-cap" style="margin-right: 6px;"></i>Học tiếng
                </button>
            </div>
            
            <!-- Search Results -->
            <div class="search-results" id="searchResults" style="background: white; border-radius: 20px; box-shadow: var(--shadow-2xl); max-height: 520px; overflow-y: auto; display: none; border: 2px solid rgba(102, 126, 234, 0.1);"></div>
        </div>
    </div>
    
    <style>
        @keyframes fadeIn {
            from { 
                opacity: 0;
                transform: scale(0.95);
            }
            to { 
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .search-close:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
            color: var(--danger);
            transform: rotate(90deg) scale(1.1);
            box-shadow: 0 12px 32px rgba(245, 87, 108, 0.5);
        }
        
        .search-box-wrapper:hover {
            box-shadow: var(--shadow-2xl), 0 0 60px rgba(102, 126, 234, 0.5);
        }
        
        .search-box input::placeholder {
            color: var(--gray-400);
        }
        
        .search-box input:focus {
            color: var(--gray-900);
        }
        
        [style*="padding: 8px 16px"]:hover {
            background: rgba(255,255,255,0.25) !important;
            border-color: rgba(255,255,255,0.4) !important;
            transform: translateY(-2px);
        }
        
        /* Custom Scrollbar for Search Results */
        .search-results::-webkit-scrollbar {
            width: 8px;
        }
        
        .search-results::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: 10px;
        }
        
        .search-results::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: 10px;
        }
        
        .search-results::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
