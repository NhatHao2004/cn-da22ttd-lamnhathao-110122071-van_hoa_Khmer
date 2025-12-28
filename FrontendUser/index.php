<?php
/**
 * Trang chủ - Văn hóa Khmer Nam Bộ
 * Redesigned with modern UI/UX
 */
$pageTitle = 'Trang chủ';
require_once __DIR__ . '/includes/header.php';

// Get statistics - removed
$pdo = getDBConnection();

// Get featured content
$featuredCulture = $pdo->query("SELECT * FROM van_hoa WHERE trang_thai = 'xuat_ban' ORDER BY luot_xem DESC LIMIT 3")->fetchAll();
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Vibrant Khmer Cultural Design ===== */
.home-unified-section {
    min-height: 100vh;
    background: linear-gradient(135deg, #FFF8E7 0%, #FFE8CC 50%, #FFF5E1 100%);
    padding: 140px 0 80px;
    position: relative;
}

.home-unified-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        radial-gradient(circle at 20% 30%, rgba(255, 193, 7, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 70%, rgba(220, 53, 69, 0.06) 0%, transparent 50%);
    pointer-events: none;
}

.home-unified-section .container {
    max-width: 1400px;
    position: relative;
    z-index: 1;
}

/* Hero Content */
.hero-content-unified {
    text-align: center;
    padding: 60px 0 40px;
}

.hero-temple-frame {
    max-width: 900px;
    margin: 0 auto 2rem;
    border-radius: 30px;
    overflow: hidden;
    border: 5px solid #D4AF37;
    box-shadow: 
        0 15px 50px rgba(212, 175, 55, 0.3),
        0 5px 15px rgba(0, 0, 0, 0.1),
        inset 0 0 0 2px rgba(255, 255, 255, 0.5);
    position: relative;
    aspect-ratio: 16 / 9;
    background: linear-gradient(135deg, #FFF8E7, #FFE8CC);
}

.hero-temple-frame::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, transparent 30%, transparent 70%, rgba(0,0,0,0.2) 100%);
    pointer-events: none;
    z-index: 1;
}

.hero-temple-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0;
    transition: opacity 1.2s ease-in-out;
}

.hero-temple-frame img.active {
    opacity: 1;
}

.hero-title-unified {
    font-size: clamp(2.5rem, 6vw, 4.8rem);
    font-weight: 800;
    color: #000000;
    margin-bottom: 1.5rem;
    line-height: 1.2;
    letter-spacing: -0.5px;
}

.hero-subtitle-unified {
    font-size: 1.35rem;
    color: #000000;
    font-weight: 600;
    max-width: 750px;
    margin: 0 auto 3rem;
    line-height: 1.7;
}

/* Features Content */
.features-content-unified {
    border: 4px solid #D4AF37;
    border-radius: 30px;
    padding: 80px 70px;
    background: linear-gradient(135deg, #FFFBF0 0%, #FFF8E7 100%);
    margin-bottom: 50px;
    box-shadow: 
        0 20px 60px rgba(212, 175, 55, 0.2),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
    position: relative;
}

.features-content-unified::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(135deg, #FFD700, #FFA500, #DC3545);
    border-radius: 32px;
    z-index: -1;
    opacity: 0.3;
}

.section-header-unified {
    text-align: center;
    margin-bottom: 3.5rem;
}

.section-title-unified {
    font-size: 2.8rem;
    font-weight: 900;
    color: #000000;
    margin-bottom: 1rem;
    letter-spacing: -0.5px;
}

.section-desc-unified {
    font-size: 1.2rem;
    color: #000000;
    font-weight: 600;
    max-width: 750px;
    margin: 0 auto;
    line-height: 1.7;
}

.features-grid-unified {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2.5rem;
}

.feature-card-unified {
    background: #FFFFFF;
    border-radius: 20px;
    overflow: hidden;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    height: 100%;
    box-shadow: 
        0 8px 25px rgba(0, 0, 0, 0.08),
        0 2px 8px rgba(0, 0, 0, 0.05);
    border: 3px solid transparent;
    position: relative;
}

.feature-card-unified .feature-img {
    height: 260px;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #FFF8E7, #FFE8CC);
}

.feature-card-unified .feature-img::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.05) 100%);
}

.feature-card-unified .feature-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.feature-card-unified .feature-body {
    padding: 2rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #FFFFFF;
}

.feature-icon-unified {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
}

.feature-icon-unified.culture { 
    background: linear-gradient(135deg, #9333EA 0%, #C084FC 100%);
    color: #FFFFFF;
}
.feature-icon-unified.temple { 
    background: linear-gradient(135deg, #EC4899 0%, #F472B6 100%);
    color: #FFFFFF;
}
.feature-icon-unified.festival { 
    background: linear-gradient(135deg, #06B6D4 0%, #22D3EE 100%);
    color: #FFFFFF;
}
.feature-icon-unified.learn { 
    background: linear-gradient(135deg, #10B981 0%, #34D399 100%);
    color: #FFFFFF;
}
.feature-icon-unified.story { 
    background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%);
    color: #FFFFFF;
}
.feature-icon-unified.map { 
    background: linear-gradient(135deg, #3B82F6 0%, #60A5FA 100%);
    color: #FFFFFF;
}

.feature-title-unified {
    font-size: 1.5rem;
    font-weight: 800;
    color: #000000;
    margin-bottom: 1rem;
    line-height: 1.3;
}

.feature-desc-unified {
    font-size: 1.05rem;
    color: #000000;
    font-weight: 500;
    line-height: 1.7;
    margin-bottom: 1.5rem;
    flex: 1;
    text-align: justify;
}

.feature-link-unified {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #FFFFFF;
    font-weight: 700;
    font-size: 1rem;
    padding: 14px 28px;
    background: linear-gradient(135deg, #D4AF37 0%, #F4A460 100%);
    border-radius: 12px;
    width: fit-content;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
}

/* CTA Content */
.cta-content-unified {
    text-align: center;
    padding: 40px 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.6), rgba(255, 248, 231, 0.6));
    border-radius: 30px;
    border: 3px solid #D4AF37;
    box-shadow: 0 15px 40px rgba(212, 175, 55, 0.15);
}

.cta-icon-unified {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #DC3545 0%, #FF6B6B 100%);
    border: 4px solid #FFFFFF;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.8rem;
    color: #FFFFFF;
    margin: 0 auto 2rem;
    box-shadow: 
        0 10px 30px rgba(220, 53, 69, 0.3),
        0 0 0 8px rgba(220, 53, 69, 0.1);
    animation: pulse-glow 2s ease-in-out infinite;
}

@keyframes pulse-glow {
    0%, 100% { transform: scale(1); box-shadow: 0 10px 30px rgba(220, 53, 69, 0.3), 0 0 0 8px rgba(220, 53, 69, 0.1); }
    50% { transform: scale(1.05); box-shadow: 0 15px 40px rgba(220, 53, 69, 0.4), 0 0 0 12px rgba(220, 53, 69, 0.15); }
}

.cta-title-unified {
    font-size: 2.8rem;
    font-weight: 900;
    color: #000000;
    margin-bottom: 1rem;
    letter-spacing: -0.5px;
}

.cta-desc-unified {
    font-size: 1.2rem;
    color: #000000;
    font-weight: 600;
    max-width: 900px;
    margin: 0 auto 2.5rem;
    line-height: 1.7;
}

.btn-cta-unified {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 1.2rem 3rem;
    background: linear-gradient(135deg, #D4AF37 0%, #F4A460 100%);
    color: #FFFFFF;
    font-size: 1.1rem;
    font-weight: 800;
    border: none;
    border-radius: 15px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    text-transform: uppercase;
    letter-spacing: 1px;
    text-decoration: none;
    box-shadow: 
        0 8px 25px rgba(212, 175, 55, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-cta-unified::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s ease;
}

.btn-cta-unified:hover::before {
    left: 100%;
}

.btn-cta-unified:hover {
    background: linear-gradient(135deg, #DC3545 0%, #FF6B6B 100%);
    color: #FFFFFF;
    transform: translateY(-5px) scale(1.05);
    box-shadow: 
        0 15px 40px rgba(220, 53, 69, 0.5),
        inset 0 1px 0 rgba(255, 255, 255, 0.3);
}

/* ===== Responsive ===== */
@media (max-width: 1024px) {
    .features-grid-unified { grid-template-columns: repeat(2, 1fr); gap: 2rem; }
    .features-content-unified { padding: 60px 40px; }
}

@media (max-width: 768px) {
    .features-grid-unified { grid-template-columns: 1fr; gap: 2rem; }
    .feature-card-unified .feature-img { height: 220px; }
    .feature-card-unified .feature-body { padding: 1.5rem; }
    .feature-icon-unified { width: 60px; height: 60px; font-size: 1.75rem; }
    .feature-title-unified { font-size: 1.35rem; }
    .feature-desc-unified { font-size: 1rem; }
    .hero-title-unified { font-size: 2.2rem; }
    .section-title-unified { font-size: 2rem; }
    .cta-title-unified { font-size: 2rem; }
    .hero-temple-frame { border-radius: 20px; border-width: 4px; }
    .features-content-unified { padding: 50px 25px; border-radius: 20px; }
    .cta-content-unified { padding: 30px 20px; border-radius: 20px; }
}

@media (max-width: 480px) {
    .feature-card-unified .feature-img { height: 200px; }
    .hero-title-unified { font-size: 1.9rem; }
    .section-title-unified { font-size: 1.7rem; }
    .hero-temple-frame { border-radius: 16px; border-width: 3px; }
    .features-content-unified { padding: 35px 20px; border-radius: 16px; }
    .cta-content-unified { padding: 25px 15px; border-radius: 16px; }
    .btn-cta-unified { padding: 1rem 2rem; font-size: 1rem; }
}
</style>


<!-- Unified Home Section -->
<section class="home-unified-section">
    <div class="container">
        <!-- Hero Content -->
        <div class="hero-content-unified">
            <div class="hero-temple-frame">
                <img src="assets/images/Chua-Khmer-1.jpg" alt="Chùa Khmer Nam Bộ" class="active">
                <img src="assets/images/w-chua-ang-15-2-259.jpg" alt="Chùa Khmer Nam Bộ">
                <img src="assets/images/anh-1.jpg" alt="Chùa Khmer Nam Bộ">
                <img src="assets/images/trang_phuc_cua_nguoi_khmer.jpg" alt="Văn hóa Khmer Nam Bộ">
            </div>
            <h1 class="hero-title-unified">
                <?= __('hero_title') ?>
            </h1>
            <p class="hero-subtitle-unified"><?= __('hero_subtitle') ?></p>
        </div>

        <!-- Features Content -->
        <div class="features-content-unified">
            <div class="section-header-unified">
                <h2 class="section-title-unified"><?= __('feature_section_title') ?></h2>
                <p class="section-desc-unified"><?= __('feature_section_subtitle') ?></p>
            </div>
            
            <div class="features-grid-unified">
                <a href="<?= BASE_URL ?>/van-hoa.php" class="feature-card-unified">
                    <div class="feature-img">
                        <img src="assets/images/trang_phuc_cua_nguoi_khmer.jpg" alt="Văn hóa Khmer">
                    </div>
                    <div class="feature-body">
                        <div class="feature-icon-unified culture">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h3 class="feature-title-unified"><?= __('feature_culture_title') ?></h3>
                        <p class="feature-desc-unified">Văn hóa Khmer Nam Bộ giàu bản sắc, gắn với Phật giáo Theravada, lễ hội truyền thống, chùa chiền, nghệ thuật dân gian đặc trưng.</p>
                        <span class="feature-link-unified"><?= __('explore_now') ?> <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
                
                <a href="<?= BASE_URL ?>/chua-khmer.php" class="feature-card-unified">
                    <div class="feature-img">
                        <img src="assets/images/Chua-Khmer-1.jpg" alt="Chùa Khmer">
                    </div>
                    <div class="feature-body">
                        <div class="feature-icon-unified temple">
                            <i class="fas fa-gopuram"></i>
                        </div>
                        <h3 class="feature-title-unified"><?= __('feature_temple_title') ?></h3>
                        <p class="feature-desc-unified">Chùa Khmer là trung tâm văn hóa tín ngưỡng, mang kiến trúc đặc trưng, gắn với Phật giáo Theravada và sinh hoạt cộng đồng Khmer Nam Bộ.</p>
                        <span class="feature-link-unified"><?= __('explore_now') ?> <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
                
                <a href="<?= BASE_URL ?>/le-hoi.php" class="feature-card-unified">
                    <div class="feature-img">
                        <img src="assets/images/anh-1.jpg" alt="Lễ hội">
                    </div>
                    <div class="feature-body">
                        <div class="feature-icon-unified festival">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h3 class="feature-title-unified"><?= __('feature_festival_title') ?></h3>
                        <p class="feature-desc-unified">Lễ hội truyền thống Khmer phản ánh đời sống tinh thần phong phú, gắn với Phật giáo, nông nghiệp và các giá trị văn hóa cộng đồng lâu đời.</p>
                        <span class="feature-link-unified"><?= __('explore_now') ?> <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
                
                <a href="<?= BASE_URL ?>/hoc-tieng-khmer.php" class="feature-card-unified">
                    <div class="feature-img">
                        <img src="assets/images/DSCN5032.jpg" alt="Học tiếng Khmer">
                    </div>
                    <div class="feature-body">
                        <div class="feature-icon-unified learn">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h3 class="feature-title-unified"><?= __('feature_learn_title') ?></h3>
                        <p class="feature-desc-unified">Học tiếng Khmer giúp hiểu sâu văn hóa, giao tiếp hiệu quả và góp phần gìn giữ, phát huy bản sắc ngôn ngữ của cộng đồng Khmer.</p>
                        <span class="feature-link-unified"><?= __('explore_now') ?> <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
                
                <a href="<?= BASE_URL ?>/truyen-dan-gian.php" class="feature-card-unified">
                    <div class="feature-img">
                        <img src="assets/images/w-chua-ang-15-2-259.jpg" alt="Truyện dân gian">
                    </div>
                    <div class="feature-body">
                        <div class="feature-icon-unified story">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h3 class="feature-title-unified"><?= __('feature_story_title') ?></h3>
                        <p class="feature-desc-unified">Truyện dân gian Khmer chứa đựng triết lý sống sâu sắc, phản ánh tâm hồn, đạo lý và bản sắc văn hóa được lưu truyền qua nhiều thế hệ.</p>
                        <span class="feature-link-unified"><?= __('explore_now') ?> <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
                
                <a href="<?= BASE_URL ?>/ban-do.php" class="feature-card-unified">
                    <div class="feature-img">
                        <img src="assets/images/ban-do-1.jpg" alt="Bản đồ di sản">
                    </div>
                    <div class="feature-body">
                        <div class="feature-icon-unified map">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <h3 class="feature-title-unified"><?= __('feature_map_title') ?></h3>
                        <p class="feature-desc-unified">Bản đồ di sản giúp định vị, giới thiệu và kết nối các giá trị văn hóa, lịch sử tiêu biểu của cộng đồng Khmer một cách trực quan.</p>
                        <span class="feature-link-unified"><?= __('explore_now') ?> <i class="fas fa-arrow-right"></i></span>
                    </div>
                </a>
            </div>
        </div>

        <!-- CTA Content -->
        <div class="cta-content-unified">
            <div class="cta-icon-unified">
                <i class="fas fa-rocket"></i>
            </div>
            <h2 class="cta-title-unified"><?= __('cta_title') ?></h2>
            <p class="cta-desc-unified"><?= __('cta_desc') ?></p>
            <?php if (!isLoggedIn()): ?>
            <a href="<?= BASE_URL ?>/register.php" class="btn-cta-unified">
                <i class="fas fa-user-plus"></i> <?= __('register_now') ?> <i class="fas fa-arrow-right"></i>
            </a>
            <?php else: ?>
            <a href="<?= BASE_URL ?>/hoc-tieng-khmer.php" class="btn-cta-unified">
                <?= __('continue_learning') ?> <i class="fas fa-arrow-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Temple image slideshow
    const templeFrame = document.querySelector('.hero-temple-frame');
    if (templeFrame) {
        const images = templeFrame.querySelectorAll('img');
        let currentIndex = 0;
        
        function showNextImage() {
            // Remove active class from current image
            images[currentIndex].classList.remove('active');
            
            // Move to next image
            currentIndex = (currentIndex + 1) % images.length;
            
            // Add active class to next image
            images[currentIndex].classList.add('active');
        }
        
        // Change image every 4 seconds
        setInterval(showNextImage, 4000);
    }
    
    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
