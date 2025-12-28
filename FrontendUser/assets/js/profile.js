/**
 * Profile Page JavaScript
 */

let currentMessageId = null;

document.addEventListener('DOMContentLoaded', function() {
    updateSavedCount();
    loadSavedArticles();
    initializeModalEvents();
});

// ===== Saved Articles Functions =====
function updateSavedCount() {
    const savedArticles = JSON.parse(localStorage.getItem('savedArticles') || '[]');
    const countEl = document.getElementById('savedStatNumber');
    if (countEl) {
        countEl.textContent = savedArticles.length;
    }
}

async function loadSavedArticles() {
    const savedArticles = JSON.parse(localStorage.getItem('savedArticles') || '[]');
    const grid = document.getElementById('savedArticlesGrid');
    const countEl = document.getElementById('savedCount');
    
    if (!grid || !countEl) return;
    
    console.log('Saved articles from localStorage:', savedArticles);
    
    if (savedArticles.length === 0) {
        countEl.textContent = '0 bài viết';
        grid.innerHTML = '<p class="empty-text">Chưa có bài viết nào được lưu</p>';
        return;
    }
    
    countEl.textContent = savedArticles.length + ' bài viết';
    grid.innerHTML = '<p class="empty-text" style="color: #667eea;"><i class="fas fa-spinner fa-spin"></i> Đang tải...</p>';
    
    // Fetch article details from server
    try {
        const baseUrl = window.location.origin + '/DoAn_ChuyenNganh/FrontendUser';
        const response = await fetch(baseUrl + '/api/get-saved-articles.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: savedArticles })
        });
        
        console.log('API Response status:', response.status);
        const data = await response.json();
        console.log('API Response data:', data);
        
        if (data.success && data.articles && data.articles.length > 0) {
            grid.innerHTML = data.articles.map(article => `
                <a href="${baseUrl}/van-hoa-chi-tiet.php?id=${article.ma_van_hoa}" class="saved-item">
                    <div class="saved-item-image-wrapper">
                        ${article.image_url 
                            ? `<img src="${article.image_url}" alt="" class="saved-item-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"><div class="saved-item-placeholder" style="display:none;"><i class="fas fa-book-open"></i></div>`
                            : `<div class="saved-item-placeholder"><i class="fas fa-book-open"></i></div>`
                        }
                    </div>
                    <div class="saved-item-info">
                        <div class="saved-item-title">${escapeHtml(article.tieu_de)}</div>
                        <div class="saved-item-meta">
                            <span><i class="far fa-eye"></i> ${article.luot_xem || 0}</span>
                            <button class="remove-saved-btn" onclick="event.preventDefault(); event.stopPropagation(); removeSavedArticle(${article.ma_van_hoa})" title="Xóa khỏi danh sách lưu">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </a>
            `).join('');
        } else {
            grid.innerHTML = '<p class="empty-text">Không tìm thấy bài viết</p>';
            if (data.error) {
                console.error('API Error:', data.error);
            }
        }
    } catch (error) {
        console.error('Error loading saved articles:', error);
        grid.innerHTML = '<p class="empty-text" style="color: #ef4444;">Lỗi tải dữ liệu</p>';
    }
}

function removeSavedArticle(articleId) {
    if (!confirm('Bạn có chắc muốn xóa bài viết này khỏi danh sách lưu?')) return;
    
    let savedArticles = JSON.parse(localStorage.getItem('savedArticles') || '[]');
    savedArticles = savedArticles.filter(id => id !== articleId);
    localStorage.setItem('savedArticles', JSON.stringify(savedArticles));
    
    updateSavedCount();
    loadSavedArticles();
    showToast('Đã xóa khỏi danh sách lưu', 'success');
}

// ===== Message Functions =====
function openMessage(message) {
    currentMessageId = message.ma_tin_nhan;
    
    document.getElementById('modalTitle').textContent = message.tieu_de;
    document.getElementById('modalTime').textContent = formatDate(message.ngay_gui);
    document.getElementById('modalContent').textContent = message.noi_dung;
    
    // Show/hide mark as read button
    const markReadBtn = document.getElementById('markReadBtn');
    if (markReadBtn) {
        if (message.trang_thai === 'chua_doc') {
            markReadBtn.style.display = 'flex';
        } else {
            markReadBtn.style.display = 'none';
        }
    }
    
    document.getElementById('messageModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeMessageModal() {
    document.getElementById('messageModal').classList.remove('show');
    document.body.style.overflow = '';
    currentMessageId = null;
}

async function markAsRead() {
    if (!currentMessageId) return;
    
    const btn = document.getElementById('markReadBtn');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    
    try {
        const formData = new FormData();
        formData.append('message_id', currentMessageId);
        
        const baseUrl = window.location.origin + '/DoAn_ChuyenNganh/FrontendUser';
        const response = await fetch(baseUrl + '/api/mark-message-read.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Đã đánh dấu đã đọc', 'success');
            closeMessageModal();
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(result.message || 'Có lỗi xảy ra', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra', 'error');
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

async function markAllRead() {
    if (!confirm('Đánh dấu tất cả tin nhắn đã đọc?')) return;
    
    try {
        const baseUrl = window.location.origin + '/DoAn_ChuyenNganh/FrontendUser';
        const response = await fetch(baseUrl + '/api/mark-all-messages-read.php', {
            method: 'POST'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Đã đánh dấu tất cả đã đọc', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(result.message || 'Có lỗi xảy ra', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra', 'error');
    }
}

// ===== Modal Events =====
function initializeModalEvents() {
    // Close modal on outside click
    const modal = document.getElementById('messageModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeMessageModal();
            }
        });
    }
    
    // Close modal on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMessageModal();
        }
    });
}

// ===== Utility Functions =====
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (days > 7) {
        return date.toLocaleDateString('vi-VN', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } else if (days > 0) {
        return days + ' ngày trước';
    } else if (hours > 0) {
        return hours + ' giờ trước';
    } else if (minutes > 0) {
        return minutes + ' phút trước';
    } else {
        return 'Vừa xong';
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function showToast(message, type = 'info') {
    const existingToast = document.querySelector('.toast-message');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    toast.className = `toast-message ${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 
                 type === 'error' ? 'fa-exclamation-circle' : 
                 'fa-info-circle';
    
    toast.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${escapeHtml(message)}</span>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}
