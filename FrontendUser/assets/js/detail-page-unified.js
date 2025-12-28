/**
 * Unified Detail Page JavaScript
 * Các chức năng chung cho tất cả trang chi tiết
 */

// ===== Comment Functions =====
document.addEventListener('DOMContentLoaded', function() {
    // Submit comment form
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('.comment-submit-btn');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
            
            try {
                const response = await fetch('/DoAn_ChuyenNganh/FrontendUser/api/comment.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Bình luận của bạn đã được gửi!', 'success');
                    document.getElementById('commentContent').value = '';
                    
                    // Reload comments after 1 second
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showToast(result.message || 'Có lỗi xảy ra. Vui lòng thử lại!', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
});

// Like comment
window.likeComment = async function(commentId) {
    if (!commentId) return;
    
    try {
        const response = await fetch('/DoAn_ChuyenNganh/FrontendUser/api/like-comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ comment_id: commentId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const commentItem = document.querySelector(`[data-id="${commentId}"]`);
            if (commentItem) {
                const likeBtn = commentItem.querySelector('.like-btn');
                const likeIcon = likeBtn.querySelector('i');
                const likeCount = likeBtn.querySelector('span');
                
                if (result.liked) {
                    likeBtn.classList.add('liked');
                    likeIcon.className = 'fas fa-heart';
                } else {
                    likeBtn.classList.remove('liked');
                    likeIcon.className = 'far fa-heart';
                }
                
                if (likeCount) {
                    likeCount.textContent = result.likes;
                }
            }
        } else {
            showToast(result.message || 'Có lỗi xảy ra!', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
    }
}

// Edit comment
window.editComment = function(commentId) {
    const textDiv = document.getElementById(`comment-text-${commentId}`);
    const editForm = document.getElementById(`edit-form-${commentId}`);
    
    if (textDiv && editForm) {
        textDiv.style.display = 'none';
        editForm.style.display = 'block';
        editForm.querySelector('textarea').focus();
    }
}

// Cancel edit
window.cancelEdit = function(commentId) {
    const textDiv = document.getElementById(`comment-text-${commentId}`);
    const editForm = document.getElementById(`edit-form-${commentId}`);
    
    if (textDiv && editForm) {
        textDiv.style.display = 'block';
        editForm.style.display = 'none';
    }
}

// Save edit
window.saveEdit = async function(commentId) {
    const editForm = document.getElementById(`edit-form-${commentId}`);
    const textarea = editForm.querySelector('textarea');
    const noiDung = textarea.value.trim();
    
    if (noiDung.length < 5) {
        showToast('Nội dung phải có ít nhất 5 ký tự', 'error');
        return;
    }
    
    try {
        const response = await fetch('/DoAn_ChuyenNganh/FrontendUser/api/edit-comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                comment_id: commentId,
                noi_dung: noiDung
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const textDiv = document.getElementById(`comment-text-${commentId}`);
            if (textDiv) {
                textDiv.innerHTML = result.noi_dung;
                textDiv.style.display = 'block';
            }
            editForm.style.display = 'none';
            showToast('Đã cập nhật bình luận!', 'success');
        } else {
            showToast(result.message || 'Có lỗi xảy ra!', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
    }
}

// Delete comment
window.deleteComment = async function(commentId) {
    if (!confirm('Bạn có chắc chắn muốn xóa bình luận này?')) {
        return;
    }
    
    try {
        const response = await fetch('/DoAn_ChuyenNganh/FrontendUser/api/delete-comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ comment_id: commentId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const commentItem = document.querySelector(`[data-id="${commentId}"]`);
            if (commentItem) {
                commentItem.style.opacity = '0';
                commentItem.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    commentItem.remove();
                    // Update comment count
                    const countElement = document.querySelector('#comments-section h3');
                    if (countElement) {
                        const currentCount = parseInt(countElement.textContent.match(/\d+/)[0]);
                        countElement.textContent = `Bình luận (${currentCount - 1})`;
                    }
                }, 300);
            }
            showToast('Đã xóa bình luận!', 'success');
        } else {
            showToast(result.message || 'Có lỗi xảy ra!', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
    }
}

// Show reply form
window.showReplyForm = function(commentId) {
    const replyForm = document.getElementById(`replyForm-${commentId}`);
    if (replyForm) {
        replyForm.style.display = 'block';
        replyForm.querySelector('textarea').focus();
    }
}

// Hide reply form
window.hideReplyForm = function(commentId) {
    const replyForm = document.getElementById(`replyForm-${commentId}`);
    if (replyForm) {
        replyForm.style.display = 'none';
        replyForm.querySelector('textarea').value = '';
    }
}

// Submit reply
window.submitReply = async function(event, parentId) {
    event.preventDefault();
    
    const form = event.target;
    const textarea = form.querySelector('textarea');
    const content = textarea.value.trim();
    
    if (!content) {
        showToast('Vui lòng nhập nội dung trả lời!', 'error');
        return;
    }
    
    const commentForm = document.getElementById('commentForm');
    if (!commentForm) return;
    
    const loaiNoiDung = commentForm.querySelector('[name="loai_noi_dung"]').value;
    const maNoiDung = commentForm.querySelector('[name="ma_noi_dung"]').value;
    
    const formData = new FormData();
    formData.append('loai_noi_dung', loaiNoiDung);
    formData.append('ma_noi_dung', maNoiDung);
    formData.append('noi_dung', content);
    formData.append('ma_binh_luan_cha', parentId);
    
    try {
        const response = await fetch('/DoAn_ChuyenNganh/FrontendUser/api/comment.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Trả lời của bạn đã được gửi!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast(result.message || 'Có lỗi xảy ra!', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
    }
}

// ===== Save/Bookmark Functions =====
window.saveArticle = async function(articleId, contentType = 'van_hoa') {
    const saveBtn = document.getElementById('saveBtn');
    const saveIcon = document.getElementById('saveIcon');
    const saveText = document.getElementById('saveText');
    
    if (!saveBtn) return;
    
    // Xác định loại nội dung từ URL nếu không được truyền vào
    if (contentType === 'van_hoa') {
        const currentPath = window.location.pathname;
        if (currentPath.includes('chua-khmer')) {
            contentType = 'chua';
        } else if (currentPath.includes('le-hoi')) {
            contentType = 'le_hoi';
        } else if (currentPath.includes('truyen')) {
            contentType = 'truyen';
        }
    }
    
    const originalText = saveText.textContent;
    const isSaved = saveBtn.classList.contains('saved');
    saveBtn.disabled = true;
    saveText.textContent = 'Đang xử lý...';
    
    try {
        const response = await fetch('/DoAn_ChuyenNganh/FrontendUser/actions/bookmark.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: isSaved ? 'remove' : 'add',
                loai_doi_tuong: contentType,
                ma_doi_tuong: articleId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (!isSaved) {
                saveIcon.className = 'fas fa-bookmark';
                saveIcon.style.opacity = '1';
                saveText.textContent = 'Đã lưu';
                saveBtn.classList.add('saved');
                showToast('Đã lưu bài viết!', 'success');
            } else {
                saveIcon.className = 'fas fa-bookmark';
                saveIcon.style.opacity = '0.4';
                saveText.textContent = 'Lưu bài viết';
                saveBtn.classList.remove('saved');
                showToast('Đã bỏ lưu bài viết!', 'info');
            }
        } else {
            showToast(result.message || 'Có lỗi xảy ra!', 'error');
            saveText.textContent = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
        saveText.textContent = originalText;
    } finally {
        saveBtn.disabled = false;
    }
}

// ===== Share Functions =====
window.shareArticle = function() {
    const url = window.location.href;
    const title = document.querySelector('.detail-title')?.textContent || document.title;
    
    if (navigator.share) {
        navigator.share({
            title: title,
            url: url
        }).then(() => {
            showToast('Đã chia sẻ thành công!', 'success');
        }).catch((error) => {
            console.log('Error sharing:', error);
            copyToClipboard(url);
        });
    } else {
        copyToClipboard(url);
    }
}

window.shareStory = function() {
    shareArticle();
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Đã sao chép link!', 'success');
        }).catch(() => {
            fallbackCopyToClipboard(text);
        });
    } else {
        fallbackCopyToClipboard(text);
    }
}

function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showToast('Đã sao chép link!', 'success');
    } catch (err) {
        showToast('Không thể sao chép link!', 'error');
    }
    
    document.body.removeChild(textArea);
}

// ===== Toast Notification =====
function showToast(message, type = 'info') {
    // Remove existing toast
    const existingToast = document.querySelector('.toast-message');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create new toast
    const toast = document.createElement('div');
    toast.className = `toast-message ${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 
                 type === 'error' ? 'fa-exclamation-circle' : 
                 'fa-info-circle';
    
    toast.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    // Hide and remove toast
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// ===== Font Size Control (for stories) =====
let currentFontSize = 1.125; // Default 1.125rem

window.changeFontSize = function(delta) {
    const content = document.getElementById('storyContent');
    if (!content) return;
    
    currentFontSize += delta * 0.125;
    
    // Limit font size
    if (currentFontSize < 0.875) currentFontSize = 0.875;
    if (currentFontSize > 1.75) currentFontSize = 1.75;
    
    content.style.fontSize = currentFontSize + 'rem';
    
    // Save to localStorage
    localStorage.setItem('storyFontSize', currentFontSize);
    
    showToast(`Cỡ chữ: ${currentFontSize.toFixed(2)}rem`, 'info');
}

// Load saved font size
document.addEventListener('DOMContentLoaded', function() {
    const savedFontSize = localStorage.getItem('storyFontSize');
    if (savedFontSize) {
        currentFontSize = parseFloat(savedFontSize);
        const content = document.getElementById('storyContent');
        if (content) {
            content.style.fontSize = currentFontSize + 'rem';
        }
    }
});

// ===== Bookmark Toggle =====
window.toggleBookmark = async function(itemId) {
    const btn = event.target.closest('.action-btn');
    if (!btn) return;
    
    const icon = btn.querySelector('i');
    const originalClass = icon.className;
    
    btn.disabled = true;
    icon.className = 'fas fa-spinner fa-spin';
    
    try {
        const response = await fetch('/DoAn_ChuyenNganh/FrontendUser/api/favorites.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                type: 'truyen',
                id: itemId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (result.action === 'added') {
                icon.className = 'fas fa-bookmark';
                showToast('Đã lưu!', 'success');
            } else {
                icon.className = 'far fa-bookmark';
                showToast('Đã bỏ lưu!', 'info');
            }
        } else {
            icon.className = originalClass;
            showToast(result.message || 'Có lỗi xảy ra!', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        icon.className = originalClass;
        showToast('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
    } finally {
        btn.disabled = false;
    }
}

// ===== Smooth Scroll to Comments =====
window.scrollToComments = function() {
    const commentsSection = document.getElementById('comments-section');
    if (commentsSection) {
        commentsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// ===== Initialize Map (if Leaflet is available) =====
document.addEventListener('DOMContentLoaded', function() {
    const mapElement = document.getElementById('detail-map');
    if (mapElement && typeof L !== 'undefined') {
        const lat = parseFloat(mapElement.dataset.lat);
        const lng = parseFloat(mapElement.dataset.lng);
        const name = mapElement.dataset.name || 'Vị trí';
        
        if (lat && lng) {
            const map = L.map('detail-map').setView([lat, lng], 15);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            L.marker([lat, lng]).addTo(map)
                .bindPopup(`<b>${name}</b>`)
                .openPopup();
        }
    }
});

// ===== Image Lazy Loading =====
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
});

// ===== Auto-adjust Hero Background Size =====
document.addEventListener('DOMContentLoaded', function() {
    autoAdjustHeroBackground();
    
    // Re-adjust on window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(autoAdjustHeroBackground, 250);
    });
});

function autoAdjustHeroBackground() {
    const heroBg = document.querySelector('.detail-hero-bg');
    
    if (!heroBg) return;
    
    // Add parallax and animate classes
    heroBg.classList.add('parallax', 'animate');
    
    const bgImage = window.getComputedStyle(heroBg).backgroundImage;
    
    if (bgImage && bgImage !== 'none') {
        // Extract URL from background-image
        const imageUrl = bgImage.replace(/url\(['"]?(.*?)['"]?\)/i, '$1');
        
        // Create temporary image to get dimensions
        const img = new Image();
        
        img.onload = function() {
            const imageWidth = this.width;
            const imageHeight = this.height;
            const imageRatio = imageWidth / imageHeight;
            
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const viewportRatio = viewportWidth / viewportHeight;
            
            // Remove existing size classes
            heroBg.classList.remove('portrait', 'landscape', 'contain', 'fill', 'top', 'bottom', 'error');
            
            // Keep parallax and animate
            heroBg.classList.add('parallax', 'animate');
            
            // Skip auto-adjustment on mobile (use cover)
            if (viewportWidth <= 768) {
                // Remove parallax on mobile for better performance
                heroBg.classList.remove('parallax');
                return;
            }
            
            // Determine best fit based on aspect ratios
            if (imageRatio < 0.7) {
                // Very tall portrait image
                heroBg.classList.add('portrait');
                
                // If image is taller than viewport, position at top
                if (imageHeight > viewportHeight * 1.5) {
                    heroBg.classList.add('top');
                }
            } else if (imageRatio > 2.5) {
                // Very wide panoramic image
                heroBg.classList.add('landscape');
                
                // If image is much wider than viewport, use contain
                if (imageWidth > viewportWidth * 2) {
                    heroBg.classList.remove('landscape');
                    heroBg.classList.add('contain');
                }
            } else if (Math.abs(imageRatio - viewportRatio) > 0.8) {
                // Aspect ratio very different from viewport
                heroBg.classList.add('contain');
            } else if (imageRatio < 1.2 && imageRatio > 0.8) {
                // Nearly square image
                if (imageWidth < viewportWidth * 0.8 || imageHeight < viewportHeight * 0.8) {
                    // Small square image
                    heroBg.classList.add('contain');
                }
            }
            // else: use default 'cover' for most cases
            
            console.log('Hero background adjusted:', {
                imageRatio: imageRatio.toFixed(2),
                viewportRatio: viewportRatio.toFixed(2),
                classes: heroBg.className
            });
        };
        
        img.onerror = function() {
            // Image failed to load, show gradient fallback
            heroBg.classList.add('error');
            console.error('Failed to load hero background image');
        };
        
        img.src = imageUrl;
    }
}

// ===== Print Page =====
window.printPage = function() {
    window.print();
}

// ===== Back to Top =====
window.backToTop = function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Show/hide back to top button
window.addEventListener('scroll', function() {
    const backToTopBtn = document.getElementById('backToTopBtn');
    if (backToTopBtn) {
        if (window.pageYOffset > 300) {
            backToTopBtn.style.display = 'flex';
        } else {
            backToTopBtn.style.display = 'none';
        }
    }
});
