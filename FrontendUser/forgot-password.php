<?php
/**
 * Quên mật khẩu
 */
$pageTitle = 'Quên mật khẩu';
require_once __DIR__ . '/includes/header.php';

if (isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Phiên làm việc hết hạn.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Vui lòng nhập email hợp lệ.';
        } else {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT id, ho_ten FROM nguoi_dung WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $stmt = $pdo->prepare("UPDATE nguoi_dung SET reset_token = ?, reset_expires = ? WHERE id = ?");
                $stmt->execute([$token, $expires, $user['id']]);
                
                // TODO: Send email with reset link
                $success = 'Nếu email tồn tại, chúng tôi đã gửi link đặt lại mật khẩu.';
            } else {
                // Don't reveal if email exists
                $success = 'Nếu email tồn tại, chúng tôi đã gửi link đặt lại mật khẩu.';
            }
        }
    }
}
?>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<main style="min-height: 100vh; display: flex; align-items: center; padding: 100px 0 50px;">
    <div class="container">
        <div style="max-width: 450px; margin: 0 auto;">
            <div class="card" style="padding: 2.5rem;">
                <div class="text-center mb-4">
                    <div style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                        <i class="fas fa-key" style="font-size: 2rem; color: white;"></i>
                    </div>
                    <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;"><?= __('forgot_password') ?></h1>
                    <p class="text-muted">Nhập email để nhận link đặt lại mật khẩu</p>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    
                    <div class="form-group">
                        <label class="form-label"><?= __('email') ?></label>
                        <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-paper-plane"></i> Gửi link đặt lại
                    </button>
                </form>
                
                <p class="text-center mt-4 text-muted">
                    <a href="<?= BASE_URL ?>/login.php" class="text-primary">
                        <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
                    </a>
                </p>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
