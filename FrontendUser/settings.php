<?php
/**
 * Cài đặt tài khoản - Modern Redesign
 */
require_once __DIR__ . '/includes/header.php';
$pageTitle = __('settings');

if (!isLoggedIn()) {
    redirect(BASE_URL . '/login.php');
}

$user = getCurrentUser();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = __('session_expired');
    } else {
        $action = $_POST['action'] ?? '';
        $pdo = getDBConnection();
        
        if ($action === 'update_profile') {
            // Handle avatar upload first
            $avatarUpdated = false;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $result = uploadImage($_FILES['avatar'], 'avatar');
                if ($result['success']) {
                    $stmt = $pdo->prepare("UPDATE nguoi_dung SET anh_dai_dien = ? WHERE ma_nguoi_dung = ?");
                    $stmt->execute([$result['filename'], $user['ma_nguoi_dung']]);
                    $avatarUpdated = true;
                } else {
                    $error = $result['message'];
                }
            }
            
            // Update profile info
            if (empty($error)) {
                $hoTen = sanitize($_POST['ho_ten'] ?? '');
                $soDienThoai = sanitize($_POST['so_dien_thoai'] ?? '');
                $ngaySinh = sanitize($_POST['ngay_sinh'] ?? '');
                $gioiTinh = sanitize($_POST['gioi_tinh'] ?? '');
                
                $stmt = $pdo->prepare("UPDATE nguoi_dung SET ho_ten = ?, so_dien_thoai = ?, ngay_sinh = ?, gioi_tinh = ? WHERE ma_nguoi_dung = ?");
                if ($stmt->execute([$hoTen, $soDienThoai, $ngaySinh ?: null, $gioiTinh ?: null, $user['ma_nguoi_dung']])) {
                    $_SESSION['settings_success'] = $avatarUpdated ? __('avatar_update_success') : __('update_success');
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                } else {
                    $error = __('error_occurred');
                }
            }
        }
        
        if ($action === 'change_password') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (!password_verify($currentPassword, $user['mat_khau'])) {
                $error = __('wrong_current_password');
            } elseif (strlen($newPassword) < 6) {
                $error = __('password_min');
            } elseif ($newPassword !== $confirmPassword) {
                $error = __('password_not_match');
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE nguoi_dung SET mat_khau = ? WHERE ma_nguoi_dung = ?");
                if ($stmt->execute([$hashedPassword, $user['ma_nguoi_dung']])) {
                    $_SESSION['settings_success'] = __('password_change_success');
                    header('Location: ' . $_SERVER['REQUEST_URI']);
                    exit;
                }
            }
        }
    }
}

// Get success message from session
if (isset($_SESSION['settings_success'])) {
    $success = $_SESSION['settings_success'];
    unset($_SESSION['settings_success']);
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<style>
/* ===== Settings Hero Section ===== */
.settings-hero {
    min-height: 30vh;
    background: #ffffff;
    position: relative;
    display: flex;
    align-items: center;
    padding-top: 140px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
}

.settings-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #000000;
    padding: 2rem 0;
}

.settings-hero-title {
    font-size: clamp(1.75rem, 4vw, 2.5rem);
    font-weight: 900;
    margin-bottom: 0.5rem;
    color: #000000 !important;
}

.settings-hero-subtitle {
    font-size: 1rem;
    color: #000000;
    font-weight: 600;
}

/* ===== Main Content ===== */
.settings-main {
    padding: 3rem 0;
    background: #ffffff;
    min-height: 60vh;
}

.settings-container {
    max-width: 800px;
    margin: 0 auto;
}

/* ===== Alert Messages ===== */
.alert-new {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 700;
    border: 2px solid #000000;
}

.alert-new.success {
    background: #ffffff;
    color: #059669;
}

.alert-new.success i {
    color: #059669;
}

.alert-new.error {
    background: #ffffff;
    color: #dc2626;
}

.alert-new.error i {
    color: #dc2626;
}

/* ===== Settings Card ===== */
.settings-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 2px solid #000000;
}

.settings-card-title {
    font-size: 1.25rem;
    font-weight: 900;
    color: #000000;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.settings-card-title i {
    color: #f59e0b;
}

/* ===== Avatar Section ===== */
.avatar-section {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid #000000;
}

.avatar-preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #000000;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.avatar-upload-btn {
    padding: 0.75rem 1.5rem;
    background: #ffffff;
    color: #000000;
    border: 2px solid #000000;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.avatar-upload-btn:hover {
    background: #000000;
    color: #ffffff;
}

.avatar-hint {
    font-size: 0.8125rem;
    color: #64748b;
    font-weight: 600;
    margin-top: 0.5rem;
}
</style>


<style>
/* ===== Form Styles ===== */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.form-group-new {
    margin-bottom: 1.25rem;
}

.form-label-new {
    display: block;
    font-size: 0.875rem;
    font-weight: 700;
    color: #000000;
    margin-bottom: 0.5rem;
}

.form-input-new {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #000000;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 600;
    transition: all 0.3s ease;
    background: #ffffff;
    color: #000000;
}

.form-input-new:focus {
    outline: none;
    border-color: #000000;
    background: white;
    box-shadow: 0 0 0 4px rgba(0, 0, 0, 0.1);
}

.form-input-new:disabled {
    background: #f1f5f9;
    color: #94a3b8;
    cursor: not-allowed;
}

/* ===== Buttons ===== */
.btn-settings {
    padding: 0.875rem 2rem;
    border-radius: 12px;
    font-size: 0.9375rem;
    font-weight: 700;
    border: 2px solid #000000;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-settings.primary {
    background: #ffffff;
    color: #000000;
}

.btn-settings.primary:hover {
    background: #000000;
    color: #ffffff;
    transform: translateY(-2px);
}

.btn-settings.danger {
    background: #ffffff;
    color: #dc2626;
    border-color: #dc2626;
}

.btn-settings.danger:hover {
    background: #dc2626;
    color: #ffffff;
    transform: translateY(-2px);
}

/* ===== Language Buttons ===== */
.language-options {
    display: flex;
    gap: 1rem;
}

.language-btn {
    flex: 1;
    padding: 1rem 1.5rem;
    border: 2px solid #000000;
    border-radius: 12px;
    background: #ffffff;
    color: #000000;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    transition: all 0.3s ease;
    text-decoration: none;
}

.language-btn:hover {
    background: #f8fafc;
}

.language-btn.active {
    background: #000000;
    color: #ffffff;
}

.language-btn span {
    font-size: 1.5rem;
}

/* ===== Danger Zone ===== */
.settings-card.danger {
    border: 2px solid #dc2626;
}

.settings-card.danger .settings-card-title {
    color: #dc2626;
}

.settings-card.danger .settings-card-title i {
    color: #dc2626;
}

.danger-desc {
    font-size: 0.9375rem;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    .avatar-section {
        flex-direction: column;
        text-align: center;
    }
    .language-options {
        flex-direction: column;
    }
}
</style>

<!-- Hero Section -->
<section class="settings-hero">
    <div class="container">
        <div class="settings-hero-content">
            <h1 class="settings-hero-title"><?= __('settings') ?></h1>
            <p class="settings-hero-subtitle"><?= __('manage_account') ?? 'Quản lý thông tin tài khoản của bạn' ?></p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="settings-main">
    <div class="container settings-container">
        <?php if ($error): ?>
        <div class="alert-new error">
            <i class="fas fa-exclamation-circle"></i>
            <?= $error ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert-new success">
            <i class="fas fa-check-circle"></i>
            <?= $success ?>
        </div>
        <?php endif; ?>
        
        <!-- Profile Settings -->
        <div class="settings-card">
            <h3 class="settings-card-title">
                <i class="fas fa-user-circle"></i>
                <?= __('personal_info') ?>
            </h3>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="action" value="update_profile">
                
                <!-- Avatar -->
                <div class="avatar-section">
                    <img src="<?= !empty($user['anh_dai_dien']) ? UPLOAD_PATH . 'avatar/' . $user['anh_dai_dien'] : BASE_URL . '/assets/images/default-avatar.svg' ?>" 
                         alt="Avatar" id="avatarPreview" class="avatar-preview">
                    <div>
                        <label class="avatar-upload-btn">
                            <i class="fas fa-camera"></i> <?= __('change_avatar') ?>
                            <input type="file" name="avatar" accept="image/*" style="display: none;" onchange="previewAvatar(this)">
                        </label>
                        <p class="avatar-hint"><?= __('avatar_hint') ?></p>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group-new">
                        <label class="form-label-new"><?= __('full_name') ?></label>
                        <input type="text" name="ho_ten" class="form-input-new" value="<?= sanitize($user['ho_ten']) ?>" required>
                    </div>
                    <div class="form-group-new">
                        <label class="form-label-new"><?= __('email') ?></label>
                        <input type="email" class="form-input-new" value="<?= sanitize($user['email']) ?>" disabled>
                    </div>
                    <div class="form-group-new">
                        <label class="form-label-new"><?= __('phone') ?></label>
                        <input type="tel" name="so_dien_thoai" class="form-input-new" value="<?= sanitize($user['so_dien_thoai'] ?? '') ?>" placeholder="<?= __('enter_phone') ?? 'Nhập số điện thoại' ?>">
                    </div>
                    <div class="form-group-new">
                        <label class="form-label-new"><?= __('birthday') ?? 'Ngày sinh' ?></label>
                        <input type="date" name="ngay_sinh" class="form-input-new" value="<?= sanitize($user['ngay_sinh'] ?? '') ?>">
                    </div>
                    <div class="form-group-new">
                        <label class="form-label-new"><?= __('gender') ?? 'Giới tính' ?></label>
                        <select name="gioi_tinh" class="form-input-new">
                            <option value=""><?= __('select_gender') ?? 'Chọn giới tính' ?></option>
                            <option value="nam" <?= ($user['gioi_tinh'] ?? '') === 'nam' ? 'selected' : '' ?>><?= __('male') ?? 'Nam' ?></option>
                            <option value="nu" <?= ($user['gioi_tinh'] ?? '') === 'nu' ? 'selected' : '' ?>><?= __('female') ?? 'Nữ' ?></option>
                            <option value="khac" <?= ($user['gioi_tinh'] ?? '') === 'khac' ? 'selected' : '' ?>><?= __('other') ?? 'Khác' ?></option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn-settings primary">
                    <i class="fas fa-save"></i> <?= __('save') ?>
                </button>
            </form>
        </div>
        
        <!-- Change Password -->
        <div class="settings-card">
            <h3 class="settings-card-title">
                <i class="fas fa-lock"></i>
                <?= __('change_password') ?>
            </h3>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group-new">
                    <label class="form-label-new"><?= __('current_password') ?></label>
                    <input type="password" name="current_password" class="form-input-new" required>
                </div>
                
                <div class="form-grid">
                    <div class="form-group-new">
                        <label class="form-label-new"><?= __('new_password') ?></label>
                        <input type="password" name="new_password" class="form-input-new" required>
                    </div>
                    <div class="form-group-new">
                        <label class="form-label-new"><?= __('confirm_new_password') ?></label>
                        <input type="password" name="confirm_password" class="form-input-new" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-settings primary">
                    <i class="fas fa-key"></i> <?= __('change_password') ?>
                </button>
            </form>
        </div>
        
        <!-- Language Settings -->
        <div class="settings-card">
            <h3 class="settings-card-title">
                <i class="fas fa-globe"></i>
                <?= __('language') ?>
            </h3>
            
            <div class="language-options">
                <a href="?lang=vi" class="language-btn <?= getCurrentLang() === 'vi' ? 'active' : '' ?>">
                    <span>🇻🇳</span> Tiếng Việt
                </a>
                <a href="?lang=km" class="language-btn <?= getCurrentLang() === 'km' ? 'active' : '' ?>">
                    <span>🇰🇭</span> ភាសាខ្មែរ
                </a>
            </div>
        </div>
        

    </div>
</section>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
        
        // Auto submit form when file selected
        input.closest('form').submit();
    }
}


</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
