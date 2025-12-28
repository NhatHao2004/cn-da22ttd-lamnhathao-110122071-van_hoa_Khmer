<?php
/**
 * Tạo chủ đề mới - Create New Thread
 */
require_once __DIR__ . '/includes/header.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php', 'Vui lòng đăng nhập để tạo chủ đề', 'warning');
}

$pageTitle = 'Tạo chủ đề mới';
$pdo = getDBConnection();

// Lấy danh mục
try {
    $categories = $pdo->query("SELECT * FROM danh_muc_dien_dan WHERE trang_thai = 'hien_thi' ORDER BY thu_tu")->fetchAll();
} catch (PDOException $e) {
    die("Lỗi database: Bảng 'danh_muc_dien_dan' không tồn tại. Vui lòng import file database/forum_tables.sql. Chi tiết: " . $e->getMessage());
}

// Xử lý form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        redirect(BASE_URL . '/tao-chu-de.php', 'Token không hợp lệ', 'error');
    }
    
    $ma_danh_muc = intval($_POST['ma_danh_muc'] ?? 0);
    $tieu_de = trim($_POST['tieu_de'] ?? '');
    $noi_dung = trim($_POST['noi_dung'] ?? '');
    
    $errors = [];
    if (!$ma_danh_muc) $errors[] = 'Vui lòng chọn danh mục';
    if (strlen($tieu_de) < 10) $errors[] = 'Tiêu đề phải có ít nhất 10 ký tự';
    if (strlen($noi_dung) < 20) $errors[] = 'Nội dung phải có ít nhất 20 ký tự';
    
    // Xử lý upload ảnh
    $uploadedImage = null;
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['hinh_anh'], 'forum');
        if ($uploadResult['success']) {
            $uploadedImage = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['message'];
        }
    }
    
    if (empty($errors)) {
        try {
            // Tạo slug
            $slug = createSlug($tieu_de) . '-' . time();
            
            $stmt = $pdo->prepare("INSERT INTO chu_de_thao_luan (ma_danh_muc, ma_nguoi_tao, tieu_de, slug, noi_dung, hinh_anh) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$ma_danh_muc, $_SESSION['user_id'], $tieu_de, $slug, $noi_dung, $uploadedImage])) {
                $threadId = $pdo->lastInsertId();
                addUserPoints($_SESSION['user_id'], 5, 'Tạo chủ đề mới');
                redirect(BASE_URL . "/chu-de.php?id=$threadId", 'Đã tạo chủ đề thành công!', 'success');
            } else {
                $errors[] = 'Không thể tạo chủ đề';
            }
        } catch (PDOException $e) {
            $errors[] = 'Lỗi database: ' . $e->getMessage();
            error_log("Forum create error: " . $e->getMessage());
        }
    }
}

function createSlug($str) {
    $str = mb_strtolower($str, 'UTF-8');
    $vietnamese = ['à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ','è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ','ì','í','ị','ỉ','ĩ','ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ','ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ','ỳ','ý','ỵ','ỷ','ỹ','đ'];
    $latin = ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','e','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y','d'];
    $str = str_replace($vietnamese, $latin, $str);
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/\s+/', '-', $str);
    return trim($str, '-');
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Create Thread Page - Unified Design ===== */
.create-thread-page {
    min-height: 100vh;
    background: #ffffff;
}

/* Hero Section */
.create-thread-hero {
    min-height: 35vh;
    background: #ffffff;
    position: relative;
    display: flex;
    align-items: center;
    padding-top: 140px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e2e8f0;
}

.create-thread-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #000000;
    padding: 1rem 0;
}

.create-thread-hero-title {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 900;
    margin-bottom: 0.5rem;
    color: #000000 !important;
}

.create-thread-hero-subtitle {
    font-size: 1.125rem;
    color: #000000;
    font-weight: 600;
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.6;
}

/* Main Content */
.create-thread-main {
    padding: 2.5rem 0;
    background: #ffffff;
}

.create-thread-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* Form Card */
.create-thread-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 2px solid #e2e8f0;
}

.create-thread-body {
    padding: 2rem;
}

/* Form Groups */
.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 700;
    color: #000000;
    margin-bottom: 0.5rem;
    font-size: 0.9375rem;
}

.form-group label span {
    color: #ef4444;
}

.form-help {
    font-size: 0.8125rem;
    color: #64748b;
    margin-top: 0.375rem;
    font-weight: 600;
}

/* Form Controls */
.form-control {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-weight: 600;
    background: #ffffff;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    background: white;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

textarea.form-control {
    min-height: 250px;
    resize: vertical;
    font-family: inherit;
    line-height: 1.6;
}

select.form-control {
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23000000' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    appearance: none;
}

/* Image Upload */
.image-upload-wrapper {
    position: relative;
}

.image-upload-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    padding: 2rem;
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.3s ease;
}

.image-upload-label:hover {
    border-color: #667eea;
    background: rgba(102, 126, 234, 0.05);
}

.image-upload-label i {
    font-size: 2rem;
    color: #667eea;
}

.image-upload-text {
    text-align: center;
}

.image-upload-text strong {
    display: block;
    color: #000000;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.image-upload-text small {
    color: #64748b;
    font-size: 0.8125rem;
}

.image-upload-input {
    display: none;
}

.image-preview {
    margin-top: 1rem;
    position: relative;
    display: none;
}

.image-preview.active {
    display: block;
}

.image-preview img {
    max-width: 100%;
    max-height: 300px;
    border-radius: 12px;
    border: 2px solid #e2e8f0;
}

.image-preview-remove {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1.25rem;
    transition: all 0.3s ease;
}

.image-preview-remove:hover {
    background: #dc2626;
    transform: scale(1.1);
}

/* Guidelines Box */
.guidelines-box {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
    border: 2px solid rgba(102, 126, 234, 0.2);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}

.guidelines-box h4 {
    font-size: 0.9375rem;
    font-weight: 900;
    color: #000000;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.guidelines-box h4 i {
    color: #667eea;
}

.guidelines-box ul {
    margin: 0;
    padding-left: 1.25rem;
}

.guidelines-box li {
    font-size: 0.875rem;
    color: #64748b;
    margin-bottom: 0.5rem;
    font-weight: 600;
    line-height: 1.5;
}

.guidelines-box li:last-child {
    margin-bottom: 0;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1.5rem;
    border-top: 2px solid #e2e8f0;
}

/* Buttons */
.btn {
    padding: 0.875rem 2rem;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #ffffff;
    border: none;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5568d3, #6a3f8f);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    color: #ffffff;
}

.btn-secondary {
    background: #f8fafc;
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.btn-secondary:hover {
    background: #e2e8f0;
    border-color: #cbd5e1;
    color: #475569;
}

/* Alert */
.alert {
    padding: 1rem 1.25rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    border: 2px solid;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
    color: #dc2626;
}

.alert-error i {
    color: #ef4444;
    font-size: 1.25rem;
    flex-shrink: 0;
    margin-top: 0.125rem;
}

.alert-content {
    flex: 1;
}

.alert strong {
    font-weight: 900;
    display: block;
    margin-bottom: 0.5rem;
}

.alert ul {
    margin: 0.5rem 0 0 1.25rem;
    padding: 0;
}

.alert li {
    margin-bottom: 0.25rem;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .create-thread-hero {
        min-height: 30vh;
        padding-top: 100px;
        padding-bottom: 15px;
    }
    
    .create-thread-hero-title {
        font-size: 1.75rem;
    }
    
    .create-thread-hero-subtitle {
        font-size: 1rem;
    }
    
    .create-thread-main {
        padding: 1.5rem 0;
    }
    
    .create-thread-body {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Image preview functionality
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('hinh_anh');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File quá lớn! Vui lòng chọn ảnh dưới 5MB.');
                    imageInput.value = '';
                    return;
                }
                
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Định dạng file không hợp lệ! Chỉ chấp nhận JPG, PNG, GIF, WEBP.');
                    imageInput.value = '';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.classList.add('active');
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

function removeImage() {
    const imageInput = document.getElementById('hinh_anh');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    imageInput.value = '';
    previewImg.src = '';
    imagePreview.classList.remove('active');
}
</script>

<!-- Quill Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Viết nội dung chi tiết về chủ đề bạn muốn thảo luận...\n\nBạn có thể:\n- Định dạng text (in đậm, nghiêng, gạch chân)\n- Tạo danh sách\n- Thêm link\n- Và nhiều hơn nữa!',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link'],
                ['clean']
            ]
        }
    });
    
    // Load existing content if any
    var existingContent = document.getElementById('noi_dung_hidden').value;
    if (existingContent) {
        quill.root.innerHTML = existingContent;
    }
    
    // Sync content realtime whenever user types
    quill.on('text-change', function() {
        var html = quill.root.innerHTML;
        document.getElementById('noi_dung_hidden').value = html;
    });
    
    // Validate before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        var text = quill.getText().trim();
        
        if (text.length < 20) {
            e.preventDefault();
            alert('Nội dung phải có ít nhất 20 ký tự!');
            return false;
        }
        
        // Final sync to be safe
        var html = quill.root.innerHTML;
        document.getElementById('noi_dung_hidden').value = html;
    });
});
</script>

<main class="create-thread-page">
    <!-- Hero Section -->
    <section class="create-thread-hero">
        <div class="container">
            <div class="create-thread-hero-content">
                <h1 class="create-thread-hero-title">✍️ Tạo chủ đề mới</h1>
                <p class="create-thread-hero-subtitle">Chia sẻ ý kiến, câu hỏi hoặc thảo luận với cộng đồng</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="create-thread-main">
        <div class="create-thread-container">
            <div class="create-thread-card">
                <div class="create-thread-body">
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div class="alert-content">
                            <strong>Có lỗi xảy ra:</strong>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                
                <!-- Guidelines -->
                <div class="guidelines-box">
                    <h4><i class="fas fa-lightbulb"></i> Hướng dẫn tạo chủ đề</h4>
                    <ul>
                        <li>✅ Chọn danh mục phù hợp với nội dung thảo luận của bạn</li>
                        <li>✅ Tiêu đề nên rõ ràng, súc tích và mô tả đúng nội dung (tối thiểu 10 ký tự)</li>
                        <li>✅ Nội dung cần chi tiết, dễ hiểu và có giá trị với cộng đồng (tối thiểu 20 ký tự)</li>
                        <li>✅ Tôn trọng các thành viên khác và tuân thủ quy định của diễn đàn</li>
                    </ul>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="form-group">
                        <label>Danh mục <span>*</span></label>
                        <select name="ma_danh_muc" class="form-control" required>
                            <option value="">-- Chọn danh mục thảo luận --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['ma_danh_muc'] ?>" <?= ($ma_danh_muc ?? '') == $cat['ma_danh_muc'] ? 'selected' : '' ?>>
                                <?= sanitize($cat['ten_danh_muc']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-help">Chọn danh mục phù hợp nhất với chủ đề của bạn</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Tiêu đề chủ đề <span>*</span></label>
                        <input type="text" name="tieu_de" class="form-control" value="<?= sanitize($tieu_de ?? '') ?>" placeholder="Ví dụ: Hỏi về lễ hội truyền thống Chol Chnam Thmay..." required minlength="10" maxlength="200">
                        <div class="form-help">Tiêu đề ngắn gọn, rõ ràng (10-200 ký tự)</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Nội dung chi tiết <span>*</span></label>
                        <div id="editor" style="min-height: 300px; border: 2px solid #e2e8f0; border-radius: 12px; background: white;"></div>
                        <textarea name="noi_dung" id="noi_dung_hidden" style="display: none;"><?= htmlspecialchars($noi_dung ?? '') ?></textarea>
                        <div class="form-help">Sử dụng editor để định dạng nội dung (in đậm, nghiêng, danh sách, v.v.)</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Hình ảnh minh họa (không bắt buộc)</label>
                        <div class="image-upload-wrapper">
                            <label for="hinh_anh" class="image-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <div class="image-upload-text">
                                    <strong>Nhấn để chọn ảnh</strong>
                                    <small>JPG, PNG, GIF, WEBP - Tối đa 5MB</small>
                                </div>
                            </label>
                            <input type="file" id="hinh_anh" name="hinh_anh" class="image-upload-input" accept="image/jpeg,image/png,image/gif,image/webp">
                            <div class="image-preview" id="imagePreview">
                                <img src="" alt="Preview" id="previewImg">
                                <button type="button" class="image-preview-remove" onclick="removeImage()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="form-help">Thêm hình ảnh để chủ đề của bạn sinh động và thu hút hơn</div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="<?= BASE_URL ?>/dien-dan.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Hủy bỏ
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Đăng chủ đề
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
