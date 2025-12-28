<style>
/* ===== New Header Design ===== */
.header-new {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    background: #ffffff;
    border-bottom: 2px solid #e2e8f0;
    padding: 1.25rem 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.header-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Logo */
.header-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    font-weight: 900;
    font-size: 1.5rem;
    transition: all 0.3s ease;
    color: #000000;
}

.header-logo:hover {
    transform: scale(1.02);
}

.header-logo-icon {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.25rem;
    color: #f59e0b;
}

.header-logo span {
    color: #000000;
}
</style>

<style>
/* Right Actions */
.header-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

/* Icon Buttons */
.header-icon-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 2px solid #000000;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #000000;
    font-size: 1.25rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    position: relative;
}

.header-icon-btn:hover {
    background: #000000;
    color: #ffffff;
    transform: translateY(-2px);
}

/* User Profile Button */
.header-user {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 16px 8px 8px;
    background: #ffffff;
    border: 2px solid #000000;
    border-radius: 50px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.header-user:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
}

.header-user-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #000000;
}

.header-user-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.header-user-lang {
    font-size: 0.75rem;
    font-weight: 700;
    color: #000000;
    letter-spacing: 0.5px;
    background: #f59e0b;
    color: #ffffff;
    padding: 2px 8px;
    border-radius: 20px;
}

.header-user-name {
    font-size: 0.9375rem;
    color: #000000;
    font-weight: 700;
    max-width: 120px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.header-user-arrow {
    color: #000000;
    font-size: 0.75rem;
    transition: all 0.3s ease;
    margin-left: 4px;
}

.menu-dropdown:hover .header-user-arrow {
    transform: rotate(180deg);
}

/* Menu Dropdown */
.menu-dropdown {
    position: relative;
}

.menu-dropdown-content {
    position: absolute;
    top: calc(100% + 12px);
    right: 0;
    background: white;
    border-radius: 20px;
    border: 2px solid #000000;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 1rem;
    min-width: 240px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px) scale(0.95);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1000;
}

.menu-dropdown.active .menu-dropdown-content {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

/* Main Menu - Mega Menu Style */
.main-menu-content {
    min-width: 480px;
    padding: 0;
    overflow: hidden;
    border: 2px solid #000000;
}

.main-menu-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.main-menu-title {
    font-size: 1rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}

.main-menu-title i {
    font-size: 1.25rem;
}

.main-menu-close {
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    border-radius: 8px;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.main-menu-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.main-menu-body {
    padding: 1rem;
}

.main-menu-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.main-menu-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 1rem 0.75rem;
    background: white;
    border-radius: 16px;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid #f1f5f9;
    position: relative;
    overflow: hidden;
}

.main-menu-item::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.main-menu-item:hover {
    border-color: rgba(102, 126, 234, 0.3);
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(102, 126, 234, 0.15);
}

.main-menu-item:hover::before {
    opacity: 1;
}

.main-menu-icon {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    color: white;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 1;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.main-menu-item:hover .main-menu-icon {
    transform: scale(1.1) rotate(-8deg);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.main-menu-icon.culture { background: linear-gradient(135deg, #667eea, #764ba2); }
.main-menu-icon.temple { background: linear-gradient(135deg, #f093fb, #f5576c); }
.main-menu-icon.festival { background: linear-gradient(135deg, #4facfe, #00f2fe); }
.main-menu-icon.learn { background: linear-gradient(135deg, #43e97b, #38f9d7); }
.main-menu-icon.story { background: linear-gradient(135deg, #fa709a, #fee140); }
.main-menu-icon.map { background: linear-gradient(135deg, #6366f1, #8b5cf6); }

.main-menu-label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #334155;
    text-align: center;
    transition: color 0.3s ease;
    position: relative;
    z-index: 1;
    white-space: nowrap;
}

.main-menu-item:hover .main-menu-label {
    color: #667eea;
}

.main-menu-footer {
    padding: 1rem 1.5rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.main-menu-footer-link {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #667eea;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.main-menu-footer-link:hover {
    gap: 12px;
}

.main-menu-footer-link i {
    transition: transform 0.3s ease;
}

.main-menu-footer-link:hover i {
    transform: translateX(4px);
}

@media (max-width: 480px) {
    .main-menu-content { min-width: 300px; }
    .main-menu-grid { grid-template-columns: repeat(2, 1fr); }
}

/* User Menu Style */
.menu-dropdown-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 0.875rem 1.25rem;
    color: #475569;
    text-decoration: none;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 500;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.menu-dropdown-item::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    transform: scaleY(0);
    transition: transform 0.2s ease;
    border-radius: 0 3px 3px 0;
}

.menu-dropdown-item:hover {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.08), rgba(118, 75, 162, 0.08));
    color: #667eea;
    padding-left: 1.5rem;
}

.menu-dropdown-item:hover::before {
    transform: scaleY(1);
}

.menu-dropdown-item i {
    width: 22px;
    text-align: center;
    font-size: 1.1rem;
    transition: all 0.2s ease;
}

.menu-dropdown-item:hover i {
    transform: scale(1.1);
    color: #764ba2;
}

.menu-divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
    margin: 0.5rem 1rem;
}

/* Toggle Button Active State */
.header-icon-btn.active {
    background: linear-gradient(135deg, #667eea, #764ba2) !important;
    color: white !important;
    border-color: transparent !important;
}
</style>

<style>
/* User Profile Button - Modern Design */
.header-user {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 16px 8px 8px;
    background: white;
    border-radius: 60px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    box-shadow: 0 2px 12px rgba(102, 126, 234, 0.1);
    border: 1px solid rgba(102, 126, 234, 0.1);
}

.header-user:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(102, 126, 234, 0.2);
    border-color: rgba(102, 126, 234, 0.2);
}

.header-user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
}

.header-user:hover .header-user-avatar {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.header-user-info {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
}

.header-user-lang {
    font-size: 0.6875rem;
    color: #667eea;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.5px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    padding: 2px 8px;
    border-radius: 20px;
}

.header-user-name {
    font-size: 0.9375rem;
    color: #1e293b;
    font-weight: 600;
    max-width: 120px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.header-user-arrow {
    color: #94a3b8;
    font-size: 0.75rem;
    transition: all 0.3s ease;
    margin-left: 4px;
}

.menu-dropdown:hover .header-user-arrow {
    transform: rotate(180deg);
    color: #667eea;
}

/* Language Items */
.menu-dropdown-item.lang-item {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
}

.menu-dropdown-item.lang-item.active {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.15), rgba(118, 75, 162, 0.15));
    color: #667eea;
}

.menu-dropdown-item.lang-item.active::before {
    transform: scaleY(1);
}

/* Logout Item */
.menu-dropdown-item.logout-item {
    color: #ef4444;
}

.menu-dropdown-item.logout-item:hover {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

.menu-dropdown-item.logout-item:hover i {
    color: #dc2626;
}

/* Auth Buttons - Glassmorphism Style */
.header-auth {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 6px;
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: 50px;
    border: 1px solid rgba(255, 255, 255, 0.4);
    box-shadow: 0 4px 24px rgba(102, 126, 234, 0.1), inset 0 1px 1px rgba(255, 255, 255, 0.6);
}

.btn-header-login {
    padding: 10px 22px;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #5a67d8;
    border: 1px solid rgba(102, 126, 234, 0.2);
    border-radius: 40px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.btn-header-login::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 40px;
}

.btn-header-login:hover {
    background: rgba(255, 255, 255, 0.9);
    color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.2);
    border-color: rgba(102, 126, 234, 0.4);
}

.btn-header-login:hover::before {
    opacity: 1;
}

.btn-header-register {
    padding: 10px 22px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9));
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 40px;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3), inset 0 1px 1px rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
}

.btn-header-register::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s ease;
}

.btn-header-register:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.3);
    color: white;
    background: linear-gradient(135deg, rgba(102, 126, 234, 1), rgba(118, 75, 162, 1));
}

.btn-header-register:hover::before {
    left: 100%;
}

/* Mobile */
@media (max-width: 768px) {
    .header-user-info { display: none; }
    .header-user { padding: 6px; }
    .header-auth { gap: 0.35rem; padding: 4px; }
    .btn-header-login, .btn-header-register { padding: 8px 14px; font-size: 0.8125rem; }
}
</style>


<?php
// Giữ lại các query parameters hiện tại khi chuyển ngôn ngữ
$currentParams = $_GET;
$currentParams['lang'] = 'vi';
$viUrl = '?' . http_build_query($currentParams);
$currentParams['lang'] = 'km';
$kmUrl = '?' . http_build_query($currentParams);
$currentLang = getCurrentLang();
?>

<header class="header-new">
    <div class="header-container">
        <!-- Logo -->
        <a href="<?= BASE_URL ?>/index.php" class="header-logo">
            <div class="header-logo-icon">
                <i class="fas fa-dharmachakra"></i>
            </div>
            <span><?= __('site_name') ?></span>
        </a>
        
        <!-- Right Actions -->
        <div class="header-actions">
            <!-- Menu Button -->
            <div class="menu-dropdown" id="mainMenuDropdown">
                <button class="header-icon-btn" id="mainMenuBtn" title="Menu">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="menu-dropdown-content main-menu-content">
                    <div class="main-menu-header">
                        <span class="main-menu-title">
                            <i class="fas fa-compass"></i>
                            <?= __('footer_explore') ?>
                        </span>
                        <button class="main-menu-close" id="mainMenuClose">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="main-menu-body">
                        <div class="main-menu-grid">
                            <a href="<?= BASE_URL ?>/van-hoa.php" class="main-menu-item">
                                <div class="main-menu-icon culture"><i class="fas fa-book-open"></i></div>
                                <span class="main-menu-label"><?= __('nav_culture') ?></span>
                            </a>
                            <a href="<?= BASE_URL ?>/chua-khmer.php" class="main-menu-item">
                                <div class="main-menu-icon temple"><i class="fas fa-place-of-worship"></i></div>
                                <span class="main-menu-label"><?= __('nav_temples') ?></span>
                            </a>
                            <a href="<?= BASE_URL ?>/le-hoi.php" class="main-menu-item">
                                <div class="main-menu-icon festival"><i class="fas fa-calendar-alt"></i></div>
                                <span class="main-menu-label"><?= __('nav_festivals') ?></span>
                            </a>
                            <a href="<?= BASE_URL ?>/hoc-tieng-khmer.php" class="main-menu-item">
                                <div class="main-menu-icon learn"><i class="fas fa-graduation-cap"></i></div>
                                <span class="main-menu-label"><?= __('nav_learn') ?></span>
                            </a>
                            <a href="<?= BASE_URL ?>/truyen-dan-gian.php" class="main-menu-item">
                                <div class="main-menu-icon story"><i class="fas fa-book"></i></div>
                                <span class="main-menu-label"><?= __('nav_stories') ?></span>
                            </a>
                            <a href="<?= BASE_URL ?>/learning_groups.php" class="main-menu-item">
                                <div class="main-menu-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);"><i class="fas fa-users"></i></div>
                                <span class="main-menu-label">Nhóm học tập</span>
                            </a>
                            <a href="<?= BASE_URL ?>/ban-do.php" class="main-menu-item">
                                <div class="main-menu-icon map"><i class="fas fa-map-marked-alt"></i></div>
                                <span class="main-menu-label"><?= __('nav_map') ?></span>
                            </a>
                            <a href="<?= BASE_URL ?>/dien-dan.php" class="main-menu-item">
                                <div class="main-menu-icon" style="background: linear-gradient(135deg, #8b5cf6, #6366f1);"><i class="fas fa-comments"></i></div>
                                <span class="main-menu-label"><?= __('nav_forum') ?></span>
                            </a>
                        </div>
                    </div>
                    <div class="main-menu-footer">
                        <a href="<?= BASE_URL ?>/search.php" class="main-menu-footer-link">
                            <i class="fas fa-search"></i>
                            <?= __('search') ?>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Search Button -->
            <button class="header-icon-btn" id="searchToggleBtn" title="<?= __('search') ?>">
                <i class="fas fa-search"></i>
            </button>
            
            <?php if (isLoggedIn()): ?>
            <!-- User Menu -->
            <div class="menu-dropdown">
                <div class="header-user">
                    <img src="<?= !empty($currentUser['anh_dai_dien']) ? UPLOAD_PATH . 'avatar/' . $currentUser['anh_dai_dien'] : BASE_URL . '/assets/images/default-avatar.svg' ?>" 
                         alt="Avatar" class="header-user-avatar">
                    <div class="header-user-info">
                        <span class="header-user-lang"><?= $currentLang === 'vi' ? 'VI' : 'KM' ?></span>
                        <span class="header-user-name"><?= sanitize($currentUser['ho_ten'] ?? 'User') ?></span>
                    </div>
                    <i class="fas fa-chevron-down header-user-arrow"></i>
                </div>
                <div class="menu-dropdown-content">
                    <a href="<?= BASE_URL ?>/profile.php" class="menu-dropdown-item">
                        <i class="fas fa-user-circle"></i>
                        <?= __('profile') ?>
                    </a>
                    <a href="<?= BASE_URL ?>/settings.php" class="menu-dropdown-item">
                        <i class="fas fa-cog"></i>
                        <?= __('settings') ?>
                    </a>
                    <div class="menu-divider"></div>
                    <a href="<?= $viUrl ?>" class="menu-dropdown-item lang-item <?= $currentLang === 'vi' ? 'active' : '' ?>">
                        <span>🇻🇳</span>
                        Tiếng Việt
                    </a>
                    <a href="<?= $kmUrl ?>" class="menu-dropdown-item lang-item <?= $currentLang === 'km' ? 'active' : '' ?>">
                        <span>🇰🇭</span>
                        ភាសាខ្មែរ
                    </a>
                    <div class="menu-divider"></div>
                    <a href="<?= BASE_URL ?>/logout.php" class="menu-dropdown-item logout-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <?= __('logout') ?>
                    </a>
                </div>
            </div>
            <?php else: ?>
            <!-- Auth Buttons -->
            <div class="header-auth">
                <a href="<?= BASE_URL ?>/login.php" class="btn-header-login"><?= __('login') ?></a>
                <a href="<?= BASE_URL ?>/register.php" class="btn-header-register"><?= __('register') ?></a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Search Popup -->
<div class="search-popup" id="searchPopup">
    <div class="search-popup-overlay"></div>
    <div class="search-popup-content">
        <form action="<?= BASE_URL ?>/search.php" method="GET" class="search-popup-form">
            <i class="fas fa-search search-popup-icon"></i>
            <input type="text" name="q" placeholder="<?= __('search_placeholder') ?>" class="search-popup-input" autofocus>
            <button type="button" class="search-popup-close" id="searchCloseBtn">
                <i class="fas fa-times"></i>
            </button>
        </form>
        <div class="search-popup-hint">
            <span>Nhấn <kbd>Enter</kbd> để tìm kiếm hoặc <kbd>Esc</kbd> để đóng</span>
        </div>
    </div>
</div>

<style>
/* Search Popup */
.search-popup {
    position: fixed;
    inset: 0;
    z-index: 2000;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 15vh;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.search-popup.active {
    opacity: 1;
    visibility: visible;
}

.search-popup-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(8px);
}

.search-popup-content {
    position: relative;
    width: 100%;
    max-width: 600px;
    padding: 0 1.5rem;
    transform: translateY(-20px) scale(0.95);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.search-popup.active .search-popup-content {
    transform: translateY(0) scale(1);
}

.search-popup-form {
    display: flex;
    align-items: center;
    background: white;
    border-radius: 20px;
    padding: 0.5rem;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
}

.search-popup-icon {
    padding: 1rem 1.25rem;
    color: #667eea;
    font-size: 1.25rem;
}

.search-popup-input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 1.125rem;
    padding: 1rem 0;
    background: transparent;
}

.search-popup-close {
    width: 48px;
    height: 48px;
    border: none;
    background: #f1f5f9;
    border-radius: 14px;
    color: #64748b;
    font-size: 1.125rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-popup-close:hover {
    background: #fee2e2;
    color: #ef4444;
}

.search-popup-hint {
    text-align: center;
    margin-top: 1rem;
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.875rem;
}

.search-popup-hint kbd {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-family: inherit;
    margin: 0 0.25rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Main Menu Toggle
    const mainMenuBtn = document.getElementById('mainMenuBtn');
    const mainMenuDropdown = document.getElementById('mainMenuDropdown');
    const mainMenuClose = document.getElementById('mainMenuClose');
    
    function closeMainMenu() {
        mainMenuDropdown?.classList.remove('active');
        mainMenuBtn?.classList.remove('active');
    }
    
    if (mainMenuBtn && mainMenuDropdown) {
        mainMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            mainMenuDropdown.classList.toggle('active');
            mainMenuBtn.classList.toggle('active');
            
            // Close other dropdowns
            document.querySelectorAll('.menu-dropdown').forEach(dropdown => {
                if (dropdown !== mainMenuDropdown) {
                    dropdown.classList.remove('active');
                    dropdown.querySelector('.header-icon-btn, .header-user')?.classList.remove('active');
                }
            });
        });
        
        // Close button
        mainMenuClose?.addEventListener('click', function(e) {
            e.stopPropagation();
            closeMainMenu();
        });
    }
    
    // User Menu Toggle
    const userMenus = document.querySelectorAll('.menu-dropdown:not(#mainMenuDropdown)');
    userMenus.forEach(menu => {
        const trigger = menu.querySelector('.header-user');
        if (trigger) {
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                menu.classList.toggle('active');
                
                // Close main menu
                if (mainMenuDropdown) {
                    mainMenuDropdown.classList.remove('active');
                    mainMenuBtn?.classList.remove('active');
                }
            });
        }
    });
    
    // Search Toggle
    const searchToggleBtn = document.getElementById('searchToggleBtn');
    const searchPopup = document.getElementById('searchPopup');
    const searchCloseBtn = document.getElementById('searchCloseBtn');
    const searchInput = searchPopup?.querySelector('.search-popup-input');
    
    if (searchToggleBtn && searchPopup) {
        searchToggleBtn.addEventListener('click', function() {
            searchPopup.classList.toggle('active');
            searchToggleBtn.classList.toggle('active');
            if (searchPopup.classList.contains('active')) {
                setTimeout(() => searchInput?.focus(), 100);
            }
        });
        
        searchCloseBtn?.addEventListener('click', function() {
            searchPopup.classList.remove('active');
            searchToggleBtn.classList.remove('active');
        });
        
        searchPopup.querySelector('.search-popup-overlay')?.addEventListener('click', function() {
            searchPopup.classList.remove('active');
            searchToggleBtn.classList.remove('active');
        });
    }
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.menu-dropdown')) {
            document.querySelectorAll('.menu-dropdown').forEach(dropdown => {
                dropdown.classList.remove('active');
                dropdown.querySelector('.header-icon-btn, .header-user')?.classList.remove('active');
            });
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Escape to close
        if (e.key === 'Escape') {
            document.querySelectorAll('.menu-dropdown').forEach(dropdown => {
                dropdown.classList.remove('active');
                dropdown.querySelector('.header-icon-btn, .header-user')?.classList.remove('active');
            });
            searchPopup?.classList.remove('active');
            searchToggleBtn?.classList.remove('active');
        }
        
        // Ctrl+K to open search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            searchPopup?.classList.add('active');
            searchToggleBtn?.classList.add('active');
            setTimeout(() => searchInput?.focus(), 100);
        }
    });
});
</script>
