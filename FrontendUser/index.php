<?php
/**
 * Trang chủ - Frontend User
 * Văn Hóa Khmer Nam Bộ
 */

session_start();
require_once 'config/database.php';

$pageTitle = 'Trang chủ';
$db = Database::getInstance();

// Lấy thống kê
$stats = [
    'hoc_vien' => $db->count('nguoi_dung', "vai_tro = 'hoc_vien' AND trang_thai = 'hoat_dong'"),
    'chua' => $db->count('chua_khmer', "trang_thai = 'hoat_dong'"),
    'le_hoi' => $db->count('le_hoi', "trang_thai = 'hien_thi'"),
    'bai_hoc' => $db->count('bai_hoc', "trang_thai = 'xuat_ban'")
];

// Lấy bài viết văn hóa nổi bật
$vanHoaNoiBat = $db->query(
    "SELECT * FROM van_hoa 
     WHERE noi_bat = 1 AND trang_thai = 'xuat_ban' 
     ORDER BY luot_xem DESC 
     LIMIT 6"
);

// Lấy chùa nổi bật
$chuaNoiBat = $db->query(
    "SELECT * FROM chua_khmer 
     WHERE trang_thai = 'hoat_dong' 
     ORDER BY luot_xem DESC 
     LIMIT 4"
);

// Lấy lễ hội sắp diễn ra
$leHoiSapDienRa = $db->query(
    "SELECT * FROM le_hoi 
     WHERE ngay_bat_dau >= CURDATE() AND trang_thai = 'hien_thi'
     ORDER BY ngay_bat_dau ASC 
     LIMIT 3"
);

// Lấy bài học phổ biến
$baiHocPhoBien = $db->query(
    "SELECT * FROM bai_hoc 
     WHERE trang_thai = 'xuat_ban' 
     ORDER BY luot_hoc DESC 
     LIMIT 4"
);

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); color: white; padding: 100px 0 80px; position: relative; overflow: hidden;">
    <!-- Animated Background -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.15;">
        <div style="position: absolute; top: 15%; left: 8%; width: 350px; height: 350px; background: radial-gradient(circle, rgba(255,255,255,0.8), transparent); border-radius: 50%; filter: blur(90px); animation: float 20s infinite;"></div>
        <div style="position: absolute; bottom: 15%; right: 8%; width: 450px; height: 450px; background: radial-gradient(circle, rgba(255,255,255,0.6), transparent); border-radius: 50%; filter: blur(110px); animation: float 25s infinite reverse;"></div>
        <div style="position: absolute; top: 50%; left: 50%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,255,255,0.4), transparent); border-radius: 50%; filter: blur(100px); animation: float 30s infinite; transform: translate(-50%, -50%);"></div>
    </div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <div class="hero-content" style="text-align: center; max-width: 900px; margin: 0 auto;">
            <div style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 28px; background: rgba(255,255,255,0.2); backdrop-filter: blur(12px); border-radius: 50px; margin-bottom: 28px; font-size: 16px; font-weight: 700; white-space: nowrap; border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 4px 16px rgba(0,0,0,0.15);">
                <i class="fas fa-sparkles" style="color: #fbbf24; animation: pulse 2s infinite;"></i>
                <span style="white-space: nowrap; color: #ffffff;">Nền tảng văn hóa Khmer hàng đầu Việt Nam</span>
            </div>
            
            <h1 class="hero-title" style="font-size: 80px; font-weight: 900; line-height: 1.15; margin-bottom: 36px; text-shadow: 0 6px 24px rgba(0,0,0,0.4); letter-spacing: -0.015em; white-space: nowrap; color: #ffffff; text-align: center;">
                Khám Phá Văn Hóa Khmer Nam Bộ
            </h1>
            
            <p class="hero-subtitle" style="font-size: 26px; opacity: 0.98; margin-bottom: 50px; line-height: 1.6; font-weight: 600; max-width: 1100px; margin-left: auto; margin-right: auto; color: #ffffff; text-shadow: 0 2px 12px rgba(0,0,0,0.3); text-align: center; white-space: nowrap;">
                Bảo tồn và phát triển di sản văn hóa Khmer - Kết nối truyền thống với hiện đại
            </p>
            <div class="hero-buttons" style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin-bottom: 60px;">
                <a href="hoc-tieng-khmer.php" class="btn btn-primary" style="background: white; color: var(--primary); font-size: 17px; padding: 16px 32px; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
                    <i class="fas fa-graduation-cap"></i>
                    <span style="white-space: nowrap;"></span>Bắt đầu học ngay</span>
                </a>
                <a href="chua-khmer.php" class="btn btn-outline" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.5); color: white; font-size: 17px; padding: 16px 32px;">
                    <i class="fas fa-place-of-worship"></i>
                    <span style="white-space: nowrap;">Khám phá chùa Khmer</span>
                </a>
            </div>
            
            <!-- Stats -->
            <div class="hero-stats" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 28px; max-width: 900px; margin: 0 auto;">
                <div class="stat-card" style="padding: 28px 20px; background: rgba(255,255,255,0.2); backdrop-filter: blur(12px); border-radius: 20px; border: 1px solid rgba(255,255,255,0.3); transition: var(--transition-base); box-shadow: 0 4px 16px rgba(0,0,0,0.15);">
                    <div class="stat-number" style="font-size: 42px; font-weight: 900; margin-bottom: 6px; color: #ffffff; text-shadow: 0 2px 8px rgba(0,0,0,0.2);"><?php echo number_format($stats['hoc_vien']); ?>+</div>
                    <div class="stat-label" style="font-size: 15px; opacity: 0.95; font-weight: 700; color: #ffffff; white-space: nowrap;">Học viên</div>
                </div>
                <div class="stat-card" style="padding: 28px 20px; background: rgba(255,255,255,0.2); backdrop-filter: blur(12px); border-radius: 20px; border: 1px solid rgba(255,255,255,0.3); transition: var(--transition-base); box-shadow: 0 4px 16px rgba(0,0,0,0.15);">
                    <div class="stat-number" style="font-size: 42px; font-weight: 900; margin-bottom: 6px; color: #ffffff; text-shadow: 0 2px 8px rgba(0,0,0,0.2);"><?php echo number_format($stats['bai_hoc']); ?>+</div>
                    <div class="stat-label" style="font-size: 15px; opacity: 0.95; font-weight: 700; color: #ffffff; white-space: nowrap;">Bài học</div>
                </div>
                <div class="stat-card" style="padding: 28px 20px; background: rgba(255,255,255,0.2); backdrop-filter: blur(12px); border-radius: 20px; border: 1px solid rgba(255,255,255,0.3); transition: var(--transition-base); box-shadow: 0 4px 16px rgba(0,0,0,0.15);">
                    <div class="stat-number" style="font-size: 42px; font-weight: 900; margin-bottom: 6px; color: #ffffff; text-shadow: 0 2px 8px rgba(0,0,0,0.2);"><?php echo number_format($stats['chua']); ?>+</div>
                    <div class="stat-label" style="font-size: 15px; opacity: 0.95; font-weight: 700; color: #ffffff; white-space: nowrap;">Chùa Khmer</div>
                </div>
                <div class="stat-card" style="padding: 28px 20px; background: rgba(255,255,255,0.2); backdrop-filter: blur(12px); border-radius: 20px; border: 1px solid rgba(255,255,255,0.3); transition: var(--transition-base); box-shadow: 0 4px 16px rgba(0,0,0,0.15);">
                    <div class="stat-number" style="font-size: 42px; font-weight: 900; margin-bottom: 6px; color: #ffffff; text-shadow: 0 2px 8px rgba(0,0,0,0.2);"><?php echo number_format($stats['le_hoi']); ?>+</div>
                    <div class="stat-label" style="font-size: 15px; opacity: 0.95; font-weight: 700; color: #ffffff; white-space: nowrap;">Lễ hội</div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    @keyframes float {
        0%, 100% { transform: translateY(0) translateX(0); }
        50% { transform: translateY(-30px) translateX(30px); }
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.1); }
    }
    
    .stat-card:hover {
        transform: translateY(-10px) scale(1.08);
        background: rgba(255,255,255,0.3) !important;
        box-shadow: 0 8px 32px rgba(0,0,0,0.25) !important;
    }
    
    @media (max-width: 768px) {
        .hero {
            padding: 60px 0 40px !important;
        }
        
        .hero-title {
            font-size: 42px !important;
            white-space: normal !important;
        }
        
        .hero-subtitle {
            font-size: 18px !important;
            white-space: normal !important;
        }
        
        .hero-stats {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 16px !important;
        }
    }
</style>

<!-- Features Section -->
<section class="section" style="padding: 100px 0; background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);">
    <div class="container">
        <div class="section-header" style="text-align: center; max-width: 750px; margin: 0 auto 70px;">
            <h2 class="section-title" style="font-size: 56px; margin-bottom: 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; font-weight: 900; letter-spacing: -0.025em; line-height: 1.1; text-align: center; white-space: nowrap;">
                Tính năng nổi bật
            </h2>
            <p class="section-subtitle" style="font-size: 20px; color: var(--gray-600); line-height: 1.7; font-weight: 600;">
                Khám phá những điều tuyệt vời về văn hóa Khmer qua các tính năng đa dạng
            </p>
        </div>
        
        <div class="features-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 36px;">
            <a href="van-hoa.php" class="feature-card" style="padding: 40px 36px; background: white; border-radius: 24px; border: 2px solid var(--gray-200); transition: var(--transition-base); cursor: pointer; text-decoration: none; display: block; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
                <div class="feature-icon" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 20px; color: white; font-size: 36px; margin-bottom: 28px; box-shadow: 0 12px 28px rgba(102, 126, 234, 0.4);">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3 class="feature-title" style="font-size: 26px; margin-bottom: 16px; color: var(--gray-900); font-weight: 800; letter-spacing: -0.015em; line-height: 1.3; white-space: nowrap;">Văn hóa Khmer</h3>
                <p class="feature-description" style="color: var(--gray-600); line-height: 1.8; margin-bottom: 20px; font-size: 17px;">
                    Tìm hiểu về lịch sử, phong tục tập quán và truyền thống văn hóa Khmer Nam Bộ
                </p>
                <span style="color: #667eea; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; font-size: 16px; white-space: nowrap;">
                    Khám phá ngay <i class="fas fa-arrow-right"></i>
                </span>
            </a>
            
            <a href="chua-khmer.php" class="feature-card" style="padding: 40px 36px; background: white; border-radius: 24px; border: 2px solid var(--gray-200); transition: var(--transition-base); cursor: pointer; text-decoration: none; display: block; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
                <div class="feature-icon" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 20px; color: white; font-size: 36px; margin-bottom: 28px; box-shadow: 0 12px 28px rgba(245, 158, 11, 0.4);">
                    <i class="fas fa-place-of-worship"></i>
                </div>
                <h3 class="feature-title" style="font-size: 26px; margin-bottom: 16px; color: var(--gray-900); font-weight: 800; letter-spacing: -0.015em; line-height: 1.3; white-space: nowrap;">Chùa Khmer</h3>
                <p class="feature-description" style="color: var(--gray-600); line-height: 1.8; margin-bottom: 20px; font-size: 17px;">
                    Khám phá kiến trúc độc đáo và ý nghĩa tâm linh của các ngôi chùa Khmer
                </p>
                <span style="color: var(--secondary); font-weight: 700; display: inline-flex; align-items: center; gap: 8px; font-size: 16px; white-space: nowrap;">
                    Khám phá ngay <i class="fas fa-arrow-right"></i>
                </span>
            </a>
            
            <a href="le-hoi.php" class="feature-card" style="padding: 40px 36px; background: white; border-radius: 24px; border: 2px solid var(--gray-200); transition: var(--transition-base); cursor: pointer; text-decoration: none; display: block; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
                <div class="feature-icon" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #ec4899, #be185d); border-radius: 20px; color: white; font-size: 36px; margin-bottom: 28px; box-shadow: 0 12px 28px rgba(236, 72, 153, 0.4);">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <h3 class="feature-title" style="font-size: 26px; margin-bottom: 16px; color: var(--gray-900); font-weight: 800; letter-spacing: -0.015em; line-height: 1.3; white-space: nowrap;">Lễ hội truyền thống</h3>
                <p class="feature-description" style="color: var(--gray-600); line-height: 1.8; margin-bottom: 20px; font-size: 17px;">
                    Tham gia và trải nghiệm các lễ hội văn hóa đặc sắc của người Khmer
                </p>
                <span style="color: #ec4899; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; font-size: 16px; white-space: nowrap;">
                    Khám phá ngay <i class="fas fa-arrow-right"></i>
                </span>
            </a>
            
            <a href="hoc-tieng-khmer.php" class="feature-card" style="padding: 40px 36px; background: white; border-radius: 24px; border: 2px solid var(--gray-200); transition: var(--transition-base); cursor: pointer; text-decoration: none; display: block; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
                <div class="feature-icon" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #10b981, #059669); border-radius: 20px; color: white; font-size: 36px; margin-bottom: 28px; box-shadow: 0 12px 28px rgba(16, 185, 129, 0.4);">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="feature-title" style="font-size: 26px; margin-bottom: 16px; color: var(--gray-900); font-weight: 800; letter-spacing: -0.015em; line-height: 1.3; white-space: nowrap;">Học tiếng Khmer</h3>
                <p class="feature-description" style="color: var(--gray-600); line-height: 1.8; margin-bottom: 20px; font-size: 17px;">
                    Khóa học tiếng Khmer từ cơ bản đến nâng cao với phương pháp hiện đại
                </p>
                <span style="color: var(--success); font-weight: 700; display: inline-flex; align-items: center; gap: 8px; font-size: 16px; white-space: nowrap;">
                    Bắt đầu học <i class="fas fa-arrow-right"></i>
                </span>
            </a>
            
            <a href="truyen-dan-gian.php" class="feature-card" style="padding: 40px 36px; background: white; border-radius: 24px; border: 2px solid var(--gray-200); transition: var(--transition-base); cursor: pointer; text-decoration: none; display: block; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
                <div class="feature-icon" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #8b5cf6, #6d28d9); border-radius: 20px; color: white; font-size: 36px; margin-bottom: 28px; box-shadow: 0 12px 28px rgba(139, 92, 246, 0.4);">
                    <i class="fas fa-book"></i>
                </div>
                <h3 class="feature-title" style="font-size: 26px; margin-bottom: 16px; color: var(--gray-900); font-weight: 800; letter-spacing: -0.015em; line-height: 1.3; white-space: nowrap;">Truyện dân gian</h3>
                <p class="feature-description" style="color: var(--gray-600); line-height: 1.8; margin-bottom: 20px; font-size: 17px;">
                    Đọc và lắng nghe những câu chuyện dân gian truyền thống đầy ý nghĩa
                </p>
                <span style="color: #8b5cf6; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; font-size: 16px; white-space: nowrap;">
                    Đọc truyện <i class="fas fa-arrow-right"></i>
                </span>
            </a>
            
            <a href="#" class="feature-card" style="padding: 40px 36px; background: white; border-radius: 24px; border: 2px solid var(--gray-200); transition: var(--transition-base); cursor: pointer; text-decoration: none; display: block; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
                <div class="feature-icon" style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 20px; color: white; font-size: 36px; margin-bottom: 28px; box-shadow: 0 12px 28px rgba(59, 130, 246, 0.4);">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h3 class="feature-title" style="font-size: 26px; margin-bottom: 16px; color: var(--gray-900); font-weight: 800; letter-spacing: -0.015em; line-height: 1.3; white-space: nowrap;">Bản đồ di sản</h3>
                <p class="feature-description" style="color: var(--gray-600); line-height: 1.8; margin-bottom: 20px; font-size: 17px;">
                    Khám phá bản đồ tương tác các di sản văn hóa Khmer trên toàn Nam Bộ
                </p>
                <span style="color: var(--info); font-weight: 700; display: inline-flex; align-items: center; gap: 8px; font-size: 16px; white-space: nowrap;">
                    Sắp ra mắt <i class="fas fa-arrow-right"></i>
                </span>
            </a>
        </div>
    </div>
</section>

<style>
    .feature-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 24px 48px rgba(0,0,0,0.12);
        border-color: #667eea;
    }
    
    .feature-card:hover .feature-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    .feature-icon {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    @media (max-width: 1024px) {
        .features-grid {
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 28px !important;
        }
    }
    
    @media (max-width: 640px) {
        .features-grid {
            grid-template-columns: 1fr !important;
            gap: 24px !important;
        }
    }
</style>

<!-- Văn hóa nổi bật -->
<?php if($vanHoaNoiBat && count($vanHoaNoiBat) > 0): ?>
<section class="section" style="padding: 100px 0; background: linear-gradient(180deg, #f9fafb 0%, #ffffff 100%);">
    <div class="container">
        <div class="section-header" style="text-align: center; max-width: 750px; margin: 0 auto 70px;">
            <h2 class="section-title" style="font-size: 56px; margin-bottom: 24px; color: var(--gray-900); font-weight: 900; letter-spacing: -0.025em; line-height: 1.1; text-align: center; white-space: nowrap;">Văn hóa nổi bật</h2>
            <p class="section-subtitle" style="font-size: 20px; color: var(--gray-600); line-height: 1.7; font-weight: 600;">Khám phá những bài viết văn hóa được quan tâm nhất</p>
        </div>
        
        <div class="content-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 32px;">
            <?php foreach($vanHoaNoiBat as $item): ?>
            <a href="van-hoa-chi-tiet.php?id=<?php echo $item['id']; ?>" class="content-card" style="background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.06); transition: var(--transition-base); text-decoration: none; display: block; border: 2px solid transparent;">
                <div style="position: relative; overflow: hidden; height: 240px;">
                    <img src="<?php echo $item['hinh_anh'] ?: 'assets/images/placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($item['tieu_de']); ?>" 
                         class="content-image" 
                         style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.4));"></div>
                    <div style="position: absolute; top: 16px; right: 16px;">
                        <span class="content-badge" style="display: inline-block; padding: 8px 18px; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 30px; font-size: 13px; font-weight: 800; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); white-space: nowrap;">Nổi bật</span>
                    </div>
                </div>
                <div class="content-body" style="padding: 28px;">
                    <h3 class="content-title" style="font-size: 20px; font-weight: 800; margin-bottom: 14px; color: var(--gray-900); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; letter-spacing: -0.01em;"><?php echo htmlspecialchars($item['tieu_de']); ?></h3>
                    <div class="content-meta" style="display: flex; gap: 18px; font-size: 15px; color: var(--gray-500); font-weight: 600;">
                        <span style="display: flex; align-items: center; gap: 6px;"><i class="fas fa-eye" style="color: #667eea;"></i> <?php echo number_format($item['luot_xem']); ?></span>
                        <span style="display: flex; align-items: center; gap: 6px;"><i class="fas fa-calendar" style="color: #667eea;"></i> <?php echo date('d/m/Y', strtotime($item['ngay_tao'])); ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 56px;">
            <a href="van-hoa.php" class="btn btn-primary" style="font-size: 17px; padding: 18px 40px; background: linear-gradient(135deg, #667eea, #764ba2); border: none; border-radius: 50px; color: white; font-weight: 700; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4); transition: all 0.3s ease;">
                <i class="fas fa-arrow-right" style="margin-right: 8px;"></i>
                <span style="white-space: nowrap;">Xem tất cả văn hóa</span>
            </a>
        </div>
    </div>
</section>

<style>
    .content-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 24px 48px rgba(0,0,0,0.12);
        border-color: #667eea;
    }
    
    .content-card:hover .content-image {
        transform: scale(1.15);
    }
    
    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
    
    @media (max-width: 640px) {
        .content-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>
<?php endif; ?>

<!-- Chùa Khmer nổi bật -->
<?php if($chuaNoiBat && count($chuaNoiBat) > 0): ?>
<section class="section" style="padding: 100px 0; background: white;">
    <div class="container">
        <div class="section-header" style="text-align: center; max-width: 750px; margin: 0 auto 70px;">
            <h2 class="section-title" style="font-size: 56px; margin-bottom: 24px; color: var(--gray-900); font-weight: 900; letter-spacing: -0.025em; line-height: 1.1; text-align: center; white-space: nowrap;">Chùa Khmer nổi bật</h2>
            <p class="section-subtitle" style="font-size: 20px; color: var(--gray-600); line-height: 1.7; font-weight: 600; text-align: center;">Những ngôi chùa tiêu biểu của người Khmer Nam Bộ</p>
        </div>
        
        <div class="content-grid" style="grid-template-columns: repeat(4, 1fr);">
            <?php foreach($chuaNoiBat as $chua): ?>
            <a href="chua-khmer-chi-tiet.php?id=<?php echo $chua['id']; ?>" class="content-card">
                <img src="<?php echo $chua['hinh_anh'] ?: 'assets/images/placeholder-temple.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($chua['ten_chua']); ?>" 
                     class="content-image">
                <div class="content-body">
                    <h3 class="content-title"><?php echo htmlspecialchars($chua['ten_chua']); ?></h3>
                    <p style="font-size: 14px; color: var(--gray-600); margin-bottom: 8px;">
                        <?php echo htmlspecialchars($chua['ten_tieng_khmer']); ?>
                    </p>
                    <div class="content-meta">
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($chua['tinh_thanh']); ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 32px;">
            <a href="chua-khmer.php" class="btn btn-secondary">
                <i class="fas fa-place-of-worship"></i>
                Khám phá thêm chùa
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Lễ hội sắp diễn ra -->
<?php if($leHoiSapDienRa && count($leHoiSapDienRa) > 0): ?>
<section class="section" style="padding: 100px 0; background: linear-gradient(180deg, #ffffff 0%, #f9fafb 100%);">
    <div class="container">
        <div class="section-header" style="text-align: center; max-width: 750px; margin: 0 auto 70px;">
            <h2 class="section-title" style="font-size: 56px; margin-bottom: 24px; color: var(--gray-900); font-weight: 900; letter-spacing: -0.025em; line-height: 1.1; text-align: center; white-space: nowrap;">Lễ hội sắp diễn ra</h2>
            <p class="section-subtitle" style="font-size: 20px; color: var(--gray-600); line-height: 1.7; font-weight: 600; text-align: center;">Đừng bỏ lỡ những lễ hội văn hóa sắp tới</p>
        </div>
        
        <div class="content-grid">
            <?php foreach($leHoiSapDienRa as $leHoi): ?>
            <a href="le-hoi-chi-tiet.php?id=<?php echo $leHoi['id']; ?>" class="content-card">
                <img src="<?php echo $leHoi['hinh_anh'] ?: 'assets/images/placeholder-festival.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($leHoi['ten_le_hoi']); ?>" 
                     class="content-image">
                <div class="content-body">
                    <span class="content-badge" style="background: var(--danger);">
                        <?php echo date('d/m/Y', strtotime($leHoi['ngay_bat_dau'])); ?>
                    </span>
                    <h3 class="content-title"><?php echo htmlspecialchars($leHoi['ten_le_hoi']); ?></h3>
                    <p style="font-size: 14px; color: var(--gray-600); margin-bottom: 8px;">
                        <?php echo htmlspecialchars($leHoi['ten_tieng_khmer']); ?>
                    </p>
                    <div class="content-meta">
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($leHoi['dia_diem']); ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 32px;">
            <a href="le-hoi.php" class="btn btn-primary">
                <i class="fas fa-calendar-alt"></i>
                Xem lịch lễ hội
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Bài học phổ biến -->
<?php if($baiHocPhoBien && count($baiHocPhoBien) > 0): ?>
<section class="section" style="padding: 100px 0; background: linear-gradient(180deg, #f9fafb 0%, #ffffff 100%);">
    <div class="container">
        <div class="section-header" style="text-align: center; max-width: 750px; margin: 0 auto 70px;">
            <h2 class="section-title" style="font-size: 56px; margin-bottom: 24px; color: var(--gray-900); font-weight: 900; letter-spacing: -0.025em; line-height: 1.1; text-align: center; white-space: nowrap;">Bài học phổ biến</h2>
            <p class="section-subtitle" style="font-size: 20px; color: var(--gray-600); line-height: 1.7; font-weight: 600; text-align: center;">Các khóa học tiếng Khmer được yêu thích nhất</p>
        </div>
        
        <div class="content-grid" style="grid-template-columns: repeat(4, 1fr);">
            <?php foreach($baiHocPhoBien as $baiHoc): ?>
            <a href="bai-hoc-chi-tiet.php?id=<?php echo $baiHoc['id']; ?>" class="content-card">
                <img src="<?php echo $baiHoc['hinh_anh'] ?: 'assets/images/placeholder-lesson.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($baiHoc['tieu_de']); ?>" 
                     class="content-image">
                <div class="content-body">
                    <?php
                    $capDoColors = [
                        'co_ban' => 'var(--success)',
                        'trung_cap' => 'var(--warning)',
                        'nang_cao' => 'var(--danger)'
                    ];
                    $capDoLabels = [
                        'co_ban' => 'Cơ bản',
                        'trung_cap' => 'Trung cấp',
                        'nang_cao' => 'Nâng cao'
                    ];
                    ?>
                    <span class="content-badge" style="background: <?php echo $capDoColors[$baiHoc['cap_do']]; ?>;">
                        <?php echo $capDoLabels[$baiHoc['cap_do']]; ?>
                    </span>
                    <h3 class="content-title"><?php echo htmlspecialchars($baiHoc['tieu_de']); ?></h3>
                    <div class="content-meta">
                        <span><i class="fas fa-clock"></i> <?php echo $baiHoc['thoi_luong']; ?> phút</span>
                        <span><i class="fas fa-users"></i> <?php echo number_format($baiHoc['luot_hoc']); ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 32px;">
            <a href="hoc-tieng-khmer.php" class="btn btn-primary">
                <i class="fas fa-graduation-cap"></i>
                Bắt đầu học ngay
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="cta-section" style="padding: 120px 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); color: white; position: relative; overflow: hidden;">
    <!-- Animated Background -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.15;">
        <div style="position: absolute; top: 30%; left: 20%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,255,255,0.8), transparent); border-radius: 50%; filter: blur(80px); animation: float 18s infinite;"></div>
        <div style="position: absolute; bottom: 30%; right: 20%; width: 350px; height: 350px; background: radial-gradient(circle, rgba(255,255,255,0.6), transparent); border-radius: 50%; filter: blur(100px); animation: float 22s infinite reverse;"></div>
    </div>
    
    <div class="container" style="position: relative; z-index: 1; text-align: center; max-width: 850px; margin: 0 auto;">
        <div style="display: inline-flex; align-items: center; gap: 12px; padding: 12px 28px; background: rgba(255,255,255,0.2); backdrop-filter: blur(12px); border-radius: 50px; margin-bottom: 32px; font-size: 16px; font-weight: 700; border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 4px 16px rgba(0,0,0,0.15);">
            <i class="fas fa-fire" style="color: #fbbf24; animation: pulse 2s infinite;"></i>
            <span style="white-space: nowrap;">Cùng nhau bảo tồn văn hóa Khmer</span>
        </div>
        
        <h2 class="cta-title" style="font-size: 60px; font-weight: 900; margin-bottom: 28px; text-shadow: 0 6px 24px rgba(0,0,0,0.3); line-height: 1.2; letter-spacing: -0.02em; white-space: nowrap; text-align: center;">
            Sẵn sàng khám phá <span style="color: #fbbf24; text-shadow: 0 4px 16px rgba(251, 191, 36, 0.5);">Văn hóa Khmer?</span>
        </h2>
        <p class="cta-description" style="font-size: 22px; opacity: 0.98; margin-bottom: 48px; line-height: 1.7; font-weight: 600; text-shadow: 0 2px 8px rgba(0,0,0,0.2); text-align: center; max-width: 1000px; margin-left: auto; margin-right: auto; white-space: nowrap;">
            Tham gia cộng đồng của chúng tôi để bảo tồn và phát triển văn hóa Khmer Nam Bộ
        </p>
        <div class="hero-buttons" style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            <a href="register.php" class="btn btn-primary" style="background: white; color: #667eea; font-size: 18px; padding: 18px 42px; box-shadow: 0 8px 28px rgba(0,0,0,0.2); border-radius: 50px; font-weight: 700; transition: all 0.3s ease;">
                <i class="fas fa-user-plus" style="margin-right: 8px;"></i>
                <span style="white-space: nowrap;">Đăng ký ngay</span>
            </a>
            <a href="van-hoa.php" class="btn btn-outline" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(12px); border: 2px solid rgba(255,255,255,0.5); color: white; font-size: 18px; padding: 18px 42px; border-radius: 50px; font-weight: 700; transition: all 0.3s ease;">
                <i class="fas fa-compass" style="margin-right: 8px;"></i>
                <span style="white-space: nowrap;">Khám phá ngay</span>
            </a>
        </div>
        
        <!-- Stats Mini -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; max-width: 700px; margin: 70px auto 0; padding-top: 50px; border-top: 2px solid rgba(255,255,255,0.25);">
            <div>
                <div style="font-size: 42px; font-weight: 900; margin-bottom: 8px; text-shadow: 0 2px 12px rgba(0,0,0,0.2);"><?php echo number_format($stats['hoc_vien']); ?>+</div>
                <div style="font-size: 16px; opacity: 0.95; font-weight: 600; white-space: nowrap;">Học viên đã tham gia</div>
            </div>
            <div>
                <div style="font-size: 42px; font-weight: 900; margin-bottom: 8px; text-shadow: 0 2px 12px rgba(0,0,0,0.2);"><?php echo number_format($stats['bai_hoc']); ?>+</div>
                <div style="font-size: 16px; opacity: 0.95; font-weight: 600; white-space: nowrap;">Bài học chất lượng</div>
            </div>
            <div>
                <div style="font-size: 42px; font-weight: 900; margin-bottom: 8px; text-shadow: 0 2px 12px rgba(0,0,0,0.2);"><?php echo number_format($stats['chua']); ?>+</div>
                <div style="font-size: 16px; opacity: 0.95; font-weight: 600; white-space: nowrap;">Chùa Khmer ghi nhận</div>
            </div>
        </div>
    </div>
</section>

<style>
    .btn-primary:hover {
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 12px 36px rgba(0,0,0,0.3);
        background: #f9fafb !important;
    }
    
    .btn-outline:hover {
        background: rgba(255,255,255,0.3) !important;
        transform: translateY(-4px) scale(1.05);
    }
    
    @media (max-width: 768px) {
        .cta-section {
            padding: 70px 20px !important;
        }
        
        .cta-title {
            font-size: 38px !important;
        }
        
        .cta-description {
            font-size: 18px !important;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>
