<style>
/* ===== Footer Redesign ===== */
.footer-new {
    background: #ffffff;
    color: #1e293b;
    position: relative;
    overflow: hidden;
    border-top: 2px solid #e2e8f0;
}

.footer-main {
    padding: 60px 0 40px;
    position: relative;
    z-index: 1;
}

.footer-grid-new {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 3rem;
}

.footer-brand-new {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.5rem;
    font-weight: 900;
    color: #000000;
    margin-bottom: 1.25rem;
}

.footer-brand-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}
</style>
<style>
.footer-desc-new {
    color: #1e293b;
    line-height: 1.7;
    margin-bottom: 1.5rem;
    font-size: 0.9375rem;
    font-weight: 600;
}

.footer-social-new {
    display: flex;
    gap: 12px;
}

.footer-social-new a {
    width: 42px;
    height: 42px;
    background: #f1f5f9;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 1.125rem;
    transition: all 0.3s ease;
    border: 2px solid #e2e8f0;
}

.footer-social-new a:hover {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    transform: translateY(-3px);
    border-color: transparent;
}

.footer-title-new {
    color: #000000;
    font-size: 1.125rem;
    font-weight: 900;
    margin-bottom: 1.5rem;
    position: relative;
    padding-bottom: 12px;
}

.footer-title-new::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 40px;
    height: 3px;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 2px;
}

.footer-links-new {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.footer-links-new a {
    color: #1e293b;
    text-decoration: none;
    font-size: 0.9375rem;
    font-weight: 700;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.footer-links-new a::before {
    content: '';
    width: 6px;
    height: 6px;
    background: #cbd5e1;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.footer-links-new a:hover {
    color: #667eea;
    padding-left: 8px;
}

.footer-links-new a:hover::before {
    background: #667eea;
}
</style>
<style>
.footer-bottom-new {
    border-top: 1px solid #e2e8f0;
    padding: 24px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.footer-copyright {
    color: #1e293b;
    font-size: 0.875rem;
    font-weight: 700;
}

.footer-copyright span {
    color: #667eea;
    font-weight: 700;
}

.footer-author {
    color: #1e293b;
    font-size: 0.875rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}

.footer-author i {
    color: #f43f5e;
}

.footer-author span {
    color: #000000;
    font-weight: 900;
}

/* Responsive */
@media (max-width: 1024px) {
    .footer-grid-new {
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }
}

@media (max-width: 640px) {
    .footer-grid-new {
        grid-template-columns: 1fr;
        gap: 2rem;
        text-align: center;
    }
    
    .footer-brand-new {
        justify-content: center;
    }
    
    .footer-social-new {
        justify-content: center;
    }
    
    .footer-title-new::after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .footer-links-new a {
        justify-content: center;
    }
    
    .footer-bottom-new {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<footer class="footer-new">
    <div class="container">
        <div class="footer-main">
            <div class="footer-grid-new">
                <!-- Brand -->
                <div>
                    <div class="footer-brand-new">
                        <div class="footer-brand-icon">
                            <i class="fas fa-dharmachakra"></i>
                        </div>
                        <?= __('site_name') ?>
                    </div>
                    <p class="footer-desc-new">
                        <?= __('footer_desc') ?>
                    </p>
                    <div class="footer-social-new">
                        <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" title="TikTok"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="footer-title-new"><?= __('footer_explore') ?></h4>
                    <div class="footer-links-new">
                        <a href="<?= BASE_URL ?>/van-hoa.php"><?= __('nav_culture') ?></a>
                        <a href="<?= BASE_URL ?>/chua-khmer.php"><?= __('nav_temples') ?></a>
                        <a href="<?= BASE_URL ?>/le-hoi.php"><?= __('nav_festivals') ?></a>
                        <a href="<?= BASE_URL ?>/truyen-dan-gian.php"><?= __('nav_stories') ?></a>
                    </div>
                </div>
                
                <!-- Learning -->
                <div>
                    <h4 class="footer-title-new"><?= __('footer_learning') ?></h4>
                    <div class="footer-links-new">
                        <a href="<?= BASE_URL ?>/hoc-tieng-khmer.php"><?= __('nav_learn') ?></a>
                        <a href="<?= BASE_URL ?>/leaderboard.php"><?= __('footer_leaderboard') ?></a>
                        <a href="<?= BASE_URL ?>/ban-do.php"><?= __('nav_map') ?></a>
                    </div>
                </div>
                
                <!-- Support -->
                <div>
                    <h4 class="footer-title-new"><?= __('footer_support') ?></h4>
                    <div class="footer-links-new">
                        <a href="#"><?= __('footer_about') ?></a>
                        <a href="#"><?= __('footer_contact') ?></a>
                        <a href="#"><?= __('footer_terms') ?></a>
                        <a href="#"><?= __('footer_privacy') ?></a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom-new">
            <p class="footer-copyright">
                <?= date('Y') ?> <span><?= __('site_name') ?></span>.
            </p>
            <p class="footer-author">
                <i class="fas fa-heart"></i><span>Lâm Nhật Hào</span>
            </p>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
