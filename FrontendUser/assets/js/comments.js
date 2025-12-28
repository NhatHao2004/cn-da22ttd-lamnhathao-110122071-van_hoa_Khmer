/**
 * Comment System JavaScript
 */

class CommentSystem {
    constructor(options) {
        this.container = document.querySelector(options.container);
        this.loai = options.loai;
        this.ma_noi_dung = options.ma_noi_dung;
        this.apiUrl = options.apiUrl || '/DoAn_ChuyenNganh/FrontendUser/api/comments.php';
        this.csrfToken = options.csrfToken || '';
        this.isLoggedIn = options.isLoggedIn || false;
        this.currentUser = options.currentUser || null;
        
        this.init();
    }
    
    init() {
        this.loadComments();
        this.bindEvents();
    }
    
    async loadComments() {
        const listContainer = this.container.querySelector('.comments-list');
        const countEl = this.container.querySelector('.comments-count');
        
        listContainer.innerHTML = '<div class="comments-loading"><i class="fas fa-spinner fa-spin"></i></div>';
        
        try {
            const response = await fetch(`${this.apiUrl}?action=get&loai=${this.loai}&ma_noi_dung=${this.ma_noi_dung}`);
            const data = await response.json();
            
            if (data.success) {
                countEl.textContent = data.total;
                this.renderComments(data.comments);
            }
        } catch (error) {
            listContainer.innerHTML = '<div class="comments-empty"><i class="fas fa-exclamation-circle"></i><p>Không thể tải bình luận</p></div>';
        }
    }
    
    renderComments(comments) {
        const listContainer = this.container.querySelector('.comments-list');
        
        if (comments.length === 0) {
            listContainer.innerHTML = `
                <div class="comments-empty">
                    <i class="fas fa-comments"></i>
                    <p>Chưa có bình luận nào. Hãy là người đầu tiên!</p>
                </div>
            `;
            return;
        }
        
        listContainer.innerHTML = comments.map(comment => this.renderComment(comment)).join('');
    }
    
    renderComment(comment, isReply = false) {
        const avatarContent = comment.anh_dai_dien 
            ? `<img src="/DoAn_ChuyenNganh/uploads/avatar/${comment.anh_dai_dien}" alt="">`
            : this.getInitials(comment.ho_ten);
        
        const likedClass = comment.user_liked ? 'liked' : '';
        const likeIcon = comment.user_liked ? 'fas fa-heart' : 'far fa-heart';
        
        const repliesHtml = !isReply && comment.replies && comment.replies.length > 0
            ? `<div class="comment-replies">${comment.replies.map(r => this.renderComment(r, true)).join('')}</div>`
            : '';
        
        const replyBtn = !isReply && this.isLoggedIn
            ? `<button class="comment-action-btn btn-reply" data-id="${comment.ma_binh_luan}"><i class="far fa-comment"></i> Trả lời</button>`
            : '';
        
        const deleteBtn = this.currentUser && comment.ma_nguoi_dung == this.currentUser.ma_nguoi_dung
            ? `<button class="comment-action-btn btn-delete" data-id="${comment.ma_binh_luan}"><i class="far fa-trash-alt"></i></button>`
            : '';
        
        const avatarClass = isReply ? 'reply-avatar' : 'comment-avatar';
        const itemClass = isReply ? 'reply-item' : 'comment-item';
        
        return `
            <div class="${itemClass}" data-comment-id="${comment.ma_binh_luan}">
                <div class="${avatarClass}">${avatarContent}</div>
                <div class="comment-content">
                    <div class="comment-header">
                        <span class="comment-author">${this.escapeHtml(comment.ho_ten)}</span>
                        ${comment.tong_diem >= 100 ? '<span class="comment-badge">Top User</span>' : ''}
                        <span class="comment-time">${comment.time_ago}</span>
                    </div>
                    <div class="comment-text">${this.escapeHtml(comment.noi_dung)}</div>
                    <div class="comment-actions">
                        <button class="comment-action-btn btn-like ${likedClass}" data-id="${comment.ma_binh_luan}">
                            <i class="${likeIcon}"></i> <span>${comment.so_like}</span>
                        </button>
                        ${replyBtn}
                        <button class="comment-action-btn btn-report" data-id="${comment.ma_binh_luan}">
                            <i class="far fa-flag"></i>
                        </button>
                        ${deleteBtn}
                    </div>
                    ${repliesHtml}
                </div>
            </div>
        `;
    }
    
    bindEvents() {
        // Submit comment form
        const form = this.container.querySelector('.comment-form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
        
        // Delegate events for dynamic content
        this.container.addEventListener('click', (e) => {
            const target = e.target.closest('button');
            if (!target) return;
            
            if (target.classList.contains('btn-like')) {
                this.handleLike(target);
            } else if (target.classList.contains('btn-reply')) {
                this.showReplyForm(target);
            } else if (target.classList.contains('btn-report')) {
                this.showReportModal(target);
            } else if (target.classList.contains('btn-delete')) {
                this.handleDelete(target);
            } else if (target.classList.contains('btn-cancel-reply')) {
                this.hideReplyForm(target);
            } else if (target.classList.contains('btn-submit-reply')) {
                this.handleReplySubmit(target);
            }
        });
        
        // Report modal events
        const reportModal = document.getElementById('reportModal');
        if (reportModal) {
            reportModal.querySelector('.report-modal-close').addEventListener('click', () => {
                reportModal.classList.remove('active');
            });
            reportModal.querySelector('.report-form').addEventListener('submit', (e) => this.handleReport(e));
        }
    }
    
    async handleSubmit(e) {
        e.preventDefault();
        
        if (!this.isLoggedIn) {
            alert('Vui lòng đăng nhập để bình luận');
            return;
        }
        
        const textarea = e.target.querySelector('textarea');
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const content = textarea.value.trim();
        
        if (!content) return;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
        
        try {
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('csrf_token', this.csrfToken);
            formData.append('loai', this.loai);
            formData.append('ma_noi_dung', this.ma_noi_dung);
            formData.append('noi_dung', content);
            
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                textarea.value = '';
                this.loadComments();
                this.showToast('Đã đăng bình luận!', 'success');
            } else {
                this.showToast(data.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            this.showToast('Không thể gửi bình luận', 'error');
        }
        
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi';
    }
    
    async handleLike(btn) {
        if (!this.isLoggedIn) {
            alert('Vui lòng đăng nhập để thích bình luận');
            return;
        }
        
        const commentId = btn.dataset.id;
        
        try {
            const formData = new FormData();
            formData.append('action', 'like');
            formData.append('ma_binh_luan', commentId);
            
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                const icon = btn.querySelector('i');
                const count = btn.querySelector('span');
                
                if (data.action === 'liked') {
                    btn.classList.add('liked');
                    icon.className = 'fas fa-heart';
                } else {
                    btn.classList.remove('liked');
                    icon.className = 'far fa-heart';
                }
                count.textContent = data.likes;
            }
        } catch (error) {
            console.error('Like error:', error);
        }
    }
    
    showReplyForm(btn) {
        const commentId = btn.dataset.id;
        const commentItem = btn.closest('.comment-item');
        
        // Remove existing reply forms
        document.querySelectorAll('.reply-form').forEach(f => f.remove());
        
        const replyForm = document.createElement('div');
        replyForm.className = 'reply-form';
        replyForm.innerHTML = `
            <textarea placeholder="Viết trả lời..." rows="2"></textarea>
            <div class="reply-form-actions">
                <button type="button" class="btn-comment btn-comment-secondary btn-cancel-reply">Hủy</button>
                <button type="button" class="btn-comment btn-comment-primary btn-submit-reply" data-parent="${commentId}">Gửi</button>
            </div>
        `;
        
        commentItem.querySelector('.comment-content').appendChild(replyForm);
        replyForm.querySelector('textarea').focus();
    }
    
    hideReplyForm(btn) {
        btn.closest('.reply-form').remove();
    }
    
    async handleReplySubmit(btn) {
        const parentId = btn.dataset.parent;
        const form = btn.closest('.reply-form');
        const textarea = form.querySelector('textarea');
        const content = textarea.value.trim();
        
        if (!content) return;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        try {
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('csrf_token', this.csrfToken);
            formData.append('loai', this.loai);
            formData.append('ma_noi_dung', this.ma_noi_dung);
            formData.append('ma_cha', parentId);
            formData.append('noi_dung', content);
            
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.loadComments();
                this.showToast('Đã gửi trả lời!', 'success');
            } else {
                this.showToast(data.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            this.showToast('Không thể gửi trả lời', 'error');
        }
    }
    
    showReportModal(btn) {
        if (!this.isLoggedIn) {
            alert('Vui lòng đăng nhập để báo cáo');
            return;
        }
        
        const commentId = btn.dataset.id;
        const modal = document.getElementById('reportModal');
        modal.querySelector('input[name="ma_binh_luan"]').value = commentId;
        modal.classList.add('active');
    }
    
    async handleReport(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        formData.append('action', 'report');
        formData.append('csrf_token', this.csrfToken);
        
        try {
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('reportModal').classList.remove('active');
                form.reset();
                this.showToast('Đã gửi báo cáo!', 'success');
            } else {
                this.showToast(data.message || 'Có lỗi xảy ra', 'error');
            }
        } catch (error) {
            this.showToast('Không thể gửi báo cáo', 'error');
        }
    }
    
    async handleDelete(btn) {
        if (!confirm('Bạn có chắc muốn xóa bình luận này?')) return;
        
        const commentId = btn.dataset.id;
        
        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('ma_binh_luan', commentId);
            
            const response = await fetch(this.apiUrl, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.loadComments();
                this.showToast('Đã xóa bình luận', 'success');
            }
        } catch (error) {
            this.showToast('Không thể xóa bình luận', 'error');
        }
    }
    
    getInitials(name) {
        return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
}

// Toast notification styles
const toastStyles = document.createElement('style');
toastStyles.textContent = `
    .toast-notification {
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transform: translateY(100px);
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 10000;
    }
    .toast-notification.show {
        transform: translateY(0);
        opacity: 1;
    }
    .toast-success { border-left: 4px solid #10b981; }
    .toast-success i { color: #10b981; }
    .toast-error { border-left: 4px solid #ef4444; }
    .toast-error i { color: #ef4444; }
`;
document.head.appendChild(toastStyles);
