<?php
/**
 * Sửa chủ đề thảo luận
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/header.php';

if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php', 'Vui lòng đăng nhập', 'warning');
}

$id = intval($_GET['id'] ?? 0);
if (!$id) redirect(BASE_URL . '/dien-dan.php');

$pdo = getDBConnection();

// Lấy thông tin chủ đề
$stmt = $pdo->prepare("SELECT * FROM chu_de_thao_luan WHERE ma_chu_de = ?");
$stmt->execute([$id]);
$thread = $stmt->fetch();

if (!$thread) {
    redirect(BASE_URL . '/dien-dan.php', 'Chủ đề không tồn tại', 'error');
}

// Kiểm tra quyền sở hữu
if ($thread['ma_nguoi_tao'] != $_SESSION['user_id']) {
    redirect(BASE_URL . '/dien-dan.php', 'Bạn không có quyền sửa chủ đề này', 'error');
}

$pageTitle = 'Sửa chủ đề';

// Lấy danh mục
$categories = $pdo->query("SELECT * FROM danh_muc_dien_dan WHERE trang_thai = 'hien_thi' ORDER BY thu_tu")->fetchAll();

// Xử lý form
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        redirect(BASE_URL . '/sua-chu-de.php?id=' . $id, 'Token không hợp lệ', 'error');
    }
    
    $ma_danh_muc = intval($_POST['ma_danh_muc'] ?? 0);
    $tieu_de = trim($_POST['tieu_de'] ?? '');
    $noi_dung = trim($_POST['noi_dung'] ?? '');
    
    if (!$ma_danh_muc) $errors[] = 'Vui lòng chọn danh mục';
    if (strlen($tieu_de) < 10) $errors[] = 'Tiêu đề phải có ít nhất 10 ký tự';
    if (strlen($noi_dung) < 20) $errors[] = 'Nội dung phải có ít nhất 20 ký tự';
    
    // Xử lý upload ảnh mới (nếu có)
    $uploadedImage = $thread['hinh_anh'];
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadImage($_FILES['hinh_anh'], 'forum');
        if ($uploadResult['success']) {
            $uploadedImage = $uploadResult['filename'];
            // Xóa ảnh cũ nếu có
            $oldImagePath = __DIR__ . '/uploads/forum/' . $thread['hinh_anh'];
            if ($thread['hinh_anh'] && file_exists($oldImagePath)) {
                @unlink($oldImagePath);
            }
        } else {
            $errors[] = $uploadResult['message'];
        }
    }
    
    if (empty($errors)) {
        try {
            $updateStmt = $pdo->prepare("UPDATE chu_de_thao_luan SET ma_danh_muc = ?, tieu_de = ?, noi_dung = ?, hinh_anh = ? WHERE ma_chu_de = ?");
            if ($updateStmt->execute([$ma_danh_muc, $tieu_de, $noi_dung, $uploadedImage, $id])) {
                redirect(BASE_URL . "/chu-de.php?id=$id", 'Đã cập nhật chủ đề thành công!', 'success');
            } else {
                $errors[] = 'Không thể cập nhật chủ đề';
            }
        } catch (PDOException $e) {
            $errors[] = 'Lỗi database: ' . $e->getMessage();
            error_log("Forum update error: " . $e->getMessage());
        }
    }
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* Sử dụng lại style từ tao-chu-de.php */
.edit-thread-page {
    min-height: 100vh;
    background: #ffffff;
}

.edit-thread-hero {
    min-height: 35vh;
    background: #ffffff;
    position: relative;
    display: flex;
    align-items: center;
    padding-top: 140px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e2e8f0;
}

.edit-thread-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #000000;
    padding: 1rem 0;
}

.edit-thread-hero-title {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 900;
    margin-bottom: 0.5rem;
    color: #000000 !important;
}

.edit-thread-hero-subtitle {
    font-size: 1.125rem;
    color: #000000;
    font-weight: 600;
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.6;
}

.edit-thread-main {
    padding: 2.5rem 0;
    background: #ffffff;
}

.edit-thread-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

.edit-thread-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 2px solid #e2e8f0;
}

.edit-thread-body {
    padding: 2rem;
}

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

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1.5rem;
    border-top: 2px solid #e2e8f0;
}

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

@media (max-width: 768px) {
    .edit-thread-hero {
        min-height: 30vh;
        padding-top: 100px;
        padding-bottom: 15px;
    }
    
    .edit-thread-hero-title {
        font-size: 1.75rem;
    }
    
    .edit-thread-hero-subtitle {
        font-size: 1rem;
    }
    
    .edit-thread-main {
        padding: 1.5rem 0;
    }
    
    .edit-thread-body {
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

<main class="edit-thread-page">
    <!-- Hero Section -->
    <section class="edit-thread-hero">
        <div class="container">
            <div class="edit-thread-hero-content">
                <h1 class="edit-thread-hero-title">✏️ Sửa chủ đề</h1>
                <p class="edit-thread-hero-subtitle">Cập nhật thông tin chủ đề thảo luận của bạn</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="edit-thread-main">
        <div class="edit-thread-container">
            <div class="edit-thread-card">
                <div class="edit-thread-body">
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
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="form-group">
                        <label>Danh mục <span>*</span></label>
                        <select name="ma_danh_muc" class="form-control" required>
                            <option value="">-- Chọn danh mục thảo luận --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['ma_danh_muc'] ?>" <?= $thread['ma_danh_muc'] == $cat['ma_danh_muc'] ? 'selected' : '' ?>>
                                <?= sanitize($cat['ten_danh_muc']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-help">Chọn danh mục phù hợp nhất với chủ đề của bạn</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Tiêu đề chủ đề <span>*</span></label>
                        <input type="text" name="tieu_de" class="form-control" value="<?= sanitize($thread['tieu_de']) ?>" required minlength="10" maxlength="200">
                        <div class="form-help">Tiêu đề ngắn gọn, rõ ràng (10-200 ký tự)</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Nội dung chi tiết <span>*</span></label>
                        <textarea name="noi_dung" class="form-control" required minlength="20"><?= sanitize($thread['noi_dung']) ?></textarea>
                        <div class="form-help">Mô tả chi tiết, rõ ràng (tối thiểu 20 ký tự)</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Hình ảnh minh họa (không bắt buộc)</label>
                        <?php if ($thread['hinh_anh']): ?>
                        <div style="margin-bottom: 1rem;">
                            <img src="/DoAn_ChuyenNganh/uploads/forum/<?= $thread['hinh_anh'] ?>" alt="Current image" style="max-width: 200px; border-radius: 8px;" onerror="this.style.display='none'; this.nextElementSibling.innerHTML='<span style=color:red>Ảnh không tồn tại</span>'">
                            <p style="font-size: 0.875rem; color: #64748b; margin-top: 0.5rem;">Ảnh hiện tại (tải ảnh mới để thay thế)</p>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="hinh_anh" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                        <div class="form-help">JPG, PNG, GIF, WEBP - Tối đa 5MB</div>
                    </div>
                    
                    <div class="form-actions">
                        <a href="<?= BASE_URL ?>/chu-de.php?id=<?= $id ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy bỏ
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
