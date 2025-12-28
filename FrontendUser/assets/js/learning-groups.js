/**
 * Learning Groups - JavaScript Functions
 * Quản lý các chức năng tương tác cho trang nhóm học tập
 */

// Hàm tham gia nhóm
function joinGroup(groupId) {
    if (!confirm('Bạn có muốn tham gia nhóm này?')) {
        return;
    }
    
    // Hiển thị loading
    const btn = event.target.closest('.btn-join-group');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    btn.disabled = true;
    btn.style.cursor = 'not-allowed';
    
    fetch('/DoAn_ChuyenNganh/FrontendUser/api/join_group.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ma_nhom: groupId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hiển thị thành công
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Tham gia thành công!';
            btn.style.background = 'linear-gradient(135deg, #48bb78 0%, #38a169 100%)';
            
            // Tạo hiệu ứng confetti nhỏ
            createSuccessEffect(btn);
            
            // Reload sau 1.5 giây
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            // Hiển thị lỗi
            showNotification(data.message || 'Không thể tham gia nhóm!', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
            btn.style.cursor = 'pointer';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Đã xảy ra lỗi khi tham gia nhóm!', 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
        btn.style.cursor = 'pointer';
    });
}

// Hàm tạo hiệu ứng thành công
function createSuccessEffect(element) {
    const rect = element.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;
    
    for (let i = 0; i < 10; i++) {
        const particle = document.createElement('div');
        particle.style.cssText = `
            position: fixed;
            width: 8px;
            height: 8px;
            background: #48bb78;
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            left: ${centerX}px;
            top: ${centerY}px;
        `;
        document.body.appendChild(particle);
        
        const angle = (Math.PI * 2 * i) / 10;
        const velocity = 50 + Math.random() * 50;
        const vx = Math.cos(angle) * velocity;
        const vy = Math.sin(angle) * velocity;
        
        let x = 0, y = 0, opacity = 1;
        const animate = () => {
            x += vx * 0.016;
            y += vy * 0.016 + 2;
            opacity -= 0.02;
            
            particle.style.transform = `translate(${x}px, ${y}px)`;
            particle.style.opacity = opacity;
            
            if (opacity > 0) {
                requestAnimationFrame(animate);
            } else {
                particle.remove();
            }
        };
        animate();
    }
}

// Hàm hiển thị thông báo
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-triangle'
    };
    
    const colors = {
        success: '#48bb78',
        error: '#f56565',
        info: '#667eea',
        warning: '#ed8936'
    };
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 10000;
        animation: slideInRight 0.3s ease;
        border-left: 4px solid ${colors[type]};
        max-width: 400px;
    `;
    
    notification.innerHTML = `
        <i class="fas ${icons[type]}" style="color: ${colors[type]}; font-size: 1.25rem;"></i>
        <span style="color: #2d3748; font-weight: 600;">${message}</span>
        <button onclick="this.parentElement.remove()" style="
            background: none;
            border: none;
            color: #a0aec0;
            cursor: pointer;
            font-size: 1.25rem;
            padding: 0;
            margin-left: auto;
        ">×</button>
    `;
    
    document.body.appendChild(notification);
    
    // Tự động xóa sau 5 giây
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Tìm kiếm nhóm với debounce
let searchTimeout;
function initializeSearch() {
    const searchInput = document.querySelector('.groups-search input');
    if (!searchInput) return;
    
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const searchTerm = e.target.value.toLowerCase().trim();
            const groupCards = document.querySelectorAll('.group-card');
            let visibleCount = 0;
            
            groupCards.forEach(card => {
                const groupName = card.querySelector('.group-card-name').textContent.toLowerCase();
                const groupDesc = card.querySelector('.group-card-desc').textContent.toLowerCase();
                
                if (searchTerm === '' || groupName.includes(searchTerm) || groupDesc.includes(searchTerm)) {
                    card.style.display = 'flex';
                    card.style.animation = 'fadeIn 0.3s ease';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Hiển thị/ẩn thông báo không tìm thấy
            updateNoResultsMessage(visibleCount, searchTerm);
        }, 300);
    });
    
    // Thêm hiệu ứng focus
    searchInput.addEventListener('focus', function() {
        this.parentElement.style.transform = 'scale(1.02)';
        this.parentElement.style.transition = 'transform 0.2s ease';
    });
    
    searchInput.addEventListener('blur', function() {
        this.parentElement.style.transform = 'scale(1)';
    });
}

// Cập nhật thông báo không tìm thấy kết quả
function updateNoResultsMessage(visibleCount, searchTerm) {
    const existingMsg = document.querySelector('.no-results-message');
    if (existingMsg) existingMsg.remove();
    
    if (visibleCount === 0 && searchTerm !== '') {
        const grid = document.querySelector('.groups-grid');
        const msg = document.createElement('div');
        msg.className = 'no-results-message';
        msg.style.cssText = `
            grid-column: 1/-1;
            text-align: center;
            padding: 3rem;
            color: #4a5568;
            animation: fadeIn 0.3s ease;
        `;
        msg.innerHTML = `
            <i class="fas fa-search" style="font-size: 3rem; color: #cbd5e0; margin-bottom: 1rem; display: block;"></i>
            <h3 style="color: #2d3748; margin-bottom: 0.5rem; font-size: 1.25rem;">Không tìm thấy nhóm nào</h3>
            <p style="color: #718096;">Thử tìm kiếm với từ khóa khác hoặc <a href="#" onclick="document.querySelector('.groups-search input').value=''; document.querySelector('.groups-search input').dispatchEvent(new Event('input')); return false;" style="color: #667eea; text-decoration: underline;">xóa bộ lọc</a></p>
        `;
        grid.appendChild(msg);
    }
}

// Thêm hiệu ứng lazy loading cho ảnh
function initializeLazyLoading() {
    const images = document.querySelectorAll('.group-card-banner-img');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.style.opacity = '0';
                    img.style.transition = 'opacity 0.3s ease';
                    
                    img.onload = () => {
                        img.style.opacity = '1';
                    };
                    
                    observer.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    }
}

// Thêm hiệu ứng scroll reveal cho cards
function initializeScrollReveal() {
    const cards = document.querySelectorAll('.group-card');
    
    if ('IntersectionObserver' in window) {
        const cardObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 50);
                }
            });
        }, {
            threshold: 0.1
        });
        
        cards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            cardObserver.observe(card);
        });
    }
}

// Thêm CSS animations
function addAnimationStyles() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
    `;
    document.head.appendChild(style);
}

// Khởi tạo khi trang load xong
document.addEventListener('DOMContentLoaded', function() {
    addAnimationStyles();
    initializeSearch();
    initializeLazyLoading();
    initializeScrollReveal();
    
    // Thêm smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Export functions để có thể sử dụng từ HTML
window.joinGroup = joinGroup;
window.showNotification = showNotification;
