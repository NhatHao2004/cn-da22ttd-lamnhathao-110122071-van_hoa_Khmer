<!-- Create Group Modal - Modern Design -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/create-group-modal.css">
<div id="createGroupModal" class="create-modal" style="display: none;">
    <div class="create-modal-overlay" onclick="hideCreateGroupModal()"></div>
    <div class="create-modal-container">
        <!-- Left Side - Preview -->
        <div class="create-modal-preview">
            <div class="preview-header">
                <div class="preview-icon" id="previewIcon">
                    <i class="fas fa-book"></i>
                </div>
                <h3 class="preview-title" id="previewTitle">Tên nhóm của bạn</h3>
            </div>
            <div class="preview-banner" id="previewBannerArea">
                <i class="fas fa-image"></i>
                <span>Ảnh banner</span>
            </div>
            <div class="preview-stats">
                <div class="preview-stat">
                    <i class="fas fa-users"></i>
                    <span>0 thành viên</span>
                </div>
                <div class="preview-stat">
                    <i class="fas fa-file-alt"></i>
                    <span>0 bài viết</span>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Form -->
        <div class="create-modal-form">
            <div class="create-modal-header">
                <div class="header-title">
                    <div class="header-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div>
                        <h3>Tạo nhóm mới</h3>
                        <p>Điền thông tin để tạo nhóm học tập</p>
                    </div>
                </div>
                <button class="create-modal-close" onclick="hideCreateGroupModal()" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="createGroupForm" onsubmit="handleCreateGroup(event)">
                <div class="create-modal-body">
                    <div class="form-field">
                        <label for="ten_nhom">
                            <i class="fas fa-heading"></i>
                            Tên nhóm <span class="required">*</span>
                        </label>
                        <input type="text" id="ten_nhom" name="ten_nhom" required 
                               placeholder="Ví dụ: Học tiếng Khmer cơ bản"
                               oninput="updatePreview()">
                    </div>
                    
                    <div class="form-field">
                        <label for="ten_nhom_km">
                            <i class="fas fa-language"></i>
                            Tên nhóm (Khmer)
                        </label>
                        <input type="text" id="ten_nhom_km" name="ten_nhom_km" 
                               placeholder="ឧទាហរណ៍: រៀនភាសាខ្មែរ">
                    </div>
                    
                    <div class="form-field">
                        <label for="mo_ta">
                            <i class="fas fa-align-left"></i>
                            Mô tả nhóm
                        </label>
                        <textarea id="mo_ta" name="mo_ta" rows="3" 
                                  placeholder="Mô tả ngắn gọn về nhóm học tập này..."></textarea>
                    </div>
                    
                    <div class="form-field">
                        <label for="mo_ta_km">
                            <i class="fas fa-align-left"></i>
                            Mô tả nhóm (Khmer)
                        </label>
                        <textarea id="mo_ta_km" name="mo_ta_km" rows="3" 
                                  placeholder="ការពិពណ៌នាខ្លីៗអំពីក្រុមសិក្សានេះ..."></textarea>
                    </div>
                    
                    <div class="form-field">
                        <label>
                            <i class="fas fa-icons"></i>
                            Chọn biểu tượng <span class="required">*</span>
                        </label>
                        <div class="icon-grid">
                            <label class="icon-option selected">
                                <input type="radio" name="icon" value="fas fa-book" checked onchange="updateIconPreview(this)">
                                <i class="fas fa-book"></i>
                            </label>
                            <label class="icon-option">
                                <input type="radio" name="icon" value="fas fa-users" onchange="updateIconPreview(this)">
                                <i class="fas fa-users"></i>
                            </label>
                            <label class="icon-option">
                                <input type="radio" name="icon" value="fas fa-graduation-cap" onchange="updateIconPreview(this)">
                                <i class="fas fa-graduation-cap"></i>
                            </label>
                            <label class="icon-option">
                                <input type="radio" name="icon" value="fas fa-language" onchange="updateIconPreview(this)">
                                <i class="fas fa-language"></i>
                            </label>
                            <label class="icon-option">
                                <input type="radio" name="icon" value="fas fa-palette" onchange="updateIconPreview(this)">
                                <i class="fas fa-palette"></i>
                            </label>
                            <label class="icon-option">
                                <input type="radio" name="icon" value="fas fa-music" onchange="updateIconPreview(this)">
                                <i class="fas fa-music"></i>
                            </label>
                            <label class="icon-option">
                                <input type="radio" name="icon" value="fas fa-utensils" onchange="updateIconPreview(this)">
                                <i class="fas fa-utensils"></i>
                            </label>
                            <label class="icon-option">
                                <input type="radio" name="icon" value="fas fa-landmark" onchange="updateIconPreview(this)">
                                <i class="fas fa-landmark"></i>
                            </label>
                            <label class="icon-option">
                                <input type="radio" name="icon" value="fas fa-pen-fancy" onchange="updateIconPreview(this)">
                                <i class="fas fa-pen-fancy"></i>
                            </label>
                            <label class="icon-option">
                                <input type="radio" name="icon" value="fas fa-calendar-alt" onchange="updateIconPreview(this)">
                                <i class="fas fa-calendar-alt"></i>
                            </label>
                            <label class="icon-option">
                                <input type="radio" name="icon" value="fas fa-book-open" onchange="updateIconPreview(this)">
                                <i class="fas fa-book-open"></i>
                            </label>
                            <label class="icon-option">
                                <input type="radio" name="icon" value="fas fa-theater-masks" onchange="updateIconPreview(this)">
                                <i class="fas fa-theater-masks"></i>
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-field">
                        <label for="hinh_anh">
                            <i class="fas fa-image"></i>
                            Ảnh banner nhóm
                        </label>
                        <div class="file-upload-area" id="fileUploadArea" onclick="document.getElementById('hinh_anh').click()">
                            <input type="file" id="hinh_anh" name="hinh_anh" accept="image/*" onchange="previewBanner(this)" style="display: none;">
                            <div class="file-upload-content" id="fileUploadContent">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Kéo thả hoặc click để chọn ảnh</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="create-modal-footer">
                    <button type="button" class="btn-cancel" onclick="hideCreateGroupModal()">
                        <i class="fas fa-times"></i>
                        Hủy
                    </button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-check"></i>
                        Tạo nhóm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Update preview in real-time
function updatePreview() {
    const title = document.getElementById('ten_nhom').value || 'Tên nhóm của bạn';
    document.getElementById('previewTitle').textContent = title;
}

// Update icon preview
function updateIconPreview(input) {
    const iconClass = input.value;
    document.getElementById('previewIcon').innerHTML = '<i class="' + iconClass + '"></i>';
    
    // Update selected state
    document.querySelectorAll('.icon-option').forEach(opt => opt.classList.remove('selected'));
    input.closest('.icon-option').classList.add('selected');
}

// Preview banner image
function previewBanner(input) {
    const uploadArea = document.getElementById('fileUploadArea');
    const previewBannerArea = document.getElementById('previewBannerArea');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Kiểm tra kích thước file (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Kích thước file quá lớn! Vui lòng chọn file nhỏ hơn 5MB.');
            input.value = '';
            return;
        }
        
        // Kiểm tra định dạng
        if (!file.type.match('image.*')) {
            alert('Vui lòng chọn file ảnh!');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            // Update upload area
            uploadArea.classList.add('has-file');
            uploadArea.innerHTML = '<img src="' + e.target.result + '" alt="Preview">' +
                '<input type="file" id="hinh_anh" name="hinh_anh" accept="image/*" onchange="previewBanner(this)" style="display: none;">';
            
            // Update preview banner
            previewBannerArea.innerHTML = '<img src="' + e.target.result + '" alt="Banner Preview">';
        };
        reader.readAsDataURL(file);
    }
}

function showCreateGroupModal() {
    const modal = document.getElementById('createGroupModal');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function hideCreateGroupModal() {
    const modal = document.getElementById('createGroupModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
    
    // Reset form
    document.getElementById('createGroupForm').reset();
    
    // Reset preview
    document.getElementById('previewTitle').textContent = 'Tên nhóm của bạn';
    document.getElementById('previewIcon').innerHTML = '<i class="fas fa-book"></i>';
    document.getElementById('previewBannerArea').innerHTML = '<i class="fas fa-image"></i><span>Ảnh banner</span>';
    
    // Reset file upload
    const uploadArea = document.getElementById('fileUploadArea');
    uploadArea.classList.remove('has-file');
    uploadArea.innerHTML = '<input type="file" id="hinh_anh" name="hinh_anh" accept="image/*" onchange="previewBanner(this)" style="display: none;">' +
        '<div class="file-upload-content" id="fileUploadContent">' +
        '<i class="fas fa-cloud-upload-alt"></i>' +
        '<span>Kéo thả hoặc click để chọn ảnh</span>' +
        '<small>PNG, JPG tối đa 5MB • 1200x400px</small>' +
        '</div>';
    
    // Reset icon selection
    document.querySelectorAll('.icon-option').forEach(opt => opt.classList.remove('selected'));
    document.querySelector('.icon-option:first-child').classList.add('selected');
}

function handleCreateGroup(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    // Hiển thị loading
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
    submitBtn.disabled = true;
    
    fetch('<?= BASE_URL ?>/api/create_group.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server trả về dữ liệu không hợp lệ.');
            });
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            hideCreateGroupModal();
            showNotification('Tạo nhóm thành công!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(result.message || 'Không thể tạo nhóm!', 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Đã xảy ra lỗi: ' + error.message, 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}
</script>
