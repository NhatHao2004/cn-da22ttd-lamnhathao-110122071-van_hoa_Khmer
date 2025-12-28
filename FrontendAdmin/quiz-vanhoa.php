<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

checkAdminAuth();
refreshAdminInfo();

$db = Database::getInstance();

// Disable cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Xử lý actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch($action) {
            case 'create_quiz':
                $maVanHoa = intval($_POST['ma_van_hoa']);
                $tieuDe = trim($_POST['tieu_de']);
                $moTa = trim($_POST['mo_ta'] ?? '');
                $thoiGian = intval($_POST['thoi_gian'] ?? 600);
                
                $sql = "INSERT INTO quiz (loai_quiz, ma_doi_tuong, tieu_de, mo_ta, thoi_gian) VALUES ('van_hoa', ?, ?, ?, ?)";
                $db->execute($sql, [$maVanHoa, $tieuDe, $moTa, $thoiGian]);
                
                $_SESSION['flash_message'] = 'Tạo quiz thành công!';
                $_SESSION['flash_type'] = 'success';
                break;
                
            case 'delete_quiz':
                $maQuiz = intval($_POST['ma_quiz']);
                $db->execute("DELETE FROM quiz WHERE ma_quiz = ? AND loai_quiz = 'van_hoa'", [$maQuiz]);
                
                $_SESSION['flash_message'] = 'Xóa quiz thành công!';
                $_SESSION['flash_type'] = 'success';
                break;
                
            case 'toggle_status':
                $maQuiz = intval($_POST['ma_quiz']);
                $newStatus = $_POST['new_status'] === 'hoat_dong' ? 'hoat_dong' : 'tam_dung';
                
                $sql = "UPDATE quiz SET trang_thai = ? WHERE ma_quiz = ? AND loai_quiz = 'van_hoa'";
                $db->execute($sql, [$newStatus, $maQuiz]);
                
                $statusText = $newStatus === 'hoat_dong' ? 'Hoạt động' : 'Tạm dừng';
                $_SESSION['flash_message'] = "Đã chuyển trạng thái thành: $statusText";
                $_SESSION['flash_type'] = 'success';
                break;
                
            case 'edit_quiz':
                $maQuiz = intval($_POST['ma_quiz']);
                $tieuDe = trim($_POST['tieu_de']);
                $moTa = trim($_POST['mo_ta'] ?? '');
                $thoiGian = intval($_POST['thoi_gian'] ?? 600);
                
                $sql = "UPDATE quiz SET tieu_de = ?, mo_ta = ?, thoi_gian = ? WHERE ma_quiz = ? AND loai_quiz = 'van_hoa'";
                $db->execute($sql, [$tieuDe, $moTa, $thoiGian, $maQuiz]);
                
                $_SESSION['flash_message'] = 'Cập nhật quiz thành công!';
                $_SESSION['flash_type'] = 'success';
                break;
                
            case 'add_questions':
                $maQuiz = intval($_POST['ma_quiz']);
                $soCauHoi = intval($_POST['so_cau_hoi'] ?? 10);
                $addedCount = 0;
                
                $result = $db->querySingle(
                    "SELECT COALESCE(MAX(thu_tu), 0) as max_order FROM cau_hoi_quiz WHERE ma_quiz = ?",
                    [$maQuiz]
                );
                $currentMaxOrder = $result ? intval($result['max_order']) : 0;
                $startOrder = $currentMaxOrder + 1;
                
                for ($i = 1; $i <= $soCauHoi; $i++) {
                    $noiDung = trim($_POST["cau_hoi_$i"] ?? '');
                    if (empty($noiDung)) continue;
                    
                    $thuTu = $startOrder + $addedCount;
                    $sql = "INSERT INTO cau_hoi_quiz (ma_quiz, noi_dung, thu_tu, diem, giai_thich) VALUES (?, ?, ?, 10, ?)";
                    $db->execute($sql, [$maQuiz, $noiDung, $thuTu, $_POST["giai_thich_$i"] ?? '']);
                    $cauHoiId = $db->lastInsertId();
                    
                    for ($j = 1; $j <= 4; $j++) {
                        $dapAn = trim($_POST["dap_an_{$i}_{$j}"] ?? '');
                        if (empty($dapAn)) continue;
                        
                        $laDung = isset($_POST["dap_an_dung_$i"]) && $_POST["dap_an_dung_$i"] == $j ? 1 : 0;
                        $sqlDapAn = "INSERT INTO dap_an_quiz (ma_cau_hoi, noi_dung, la_dap_an_dung, thu_tu) VALUES (?, ?, ?, ?)";
                        $db->execute($sqlDapAn, [$cauHoiId, $dapAn, $laDung, $j]);
                    }
                    
                    $addedCount++;
                }
                
                $_SESSION['flash_message'] = "Đã thêm $addedCount câu hỏi thành công!";
                $_SESSION['flash_type'] = 'success';
                break;
                
            case 'edit_question':
                $maCauHoi = intval($_POST['ma_cau_hoi']);
                $noiDung = trim($_POST['noi_dung']);
                $giaiThich = trim($_POST['giai_thich'] ?? '');
                
                $sql = "UPDATE cau_hoi_quiz SET noi_dung = ?, giai_thich = ? WHERE ma_cau_hoi = ?";
                $db->execute($sql, [$noiDung, $giaiThich, $maCauHoi]);
                
                for ($j = 1; $j <= 4; $j++) {
                    $maDapAn = intval($_POST["ma_dap_an_$j"]);
                    $dapAn = trim($_POST["dap_an_$j"]);
                    $laDung = isset($_POST["dap_an_dung"]) && $_POST["dap_an_dung"] == $j ? 1 : 0;
                    
                    $sqlDapAn = "UPDATE dap_an_quiz SET noi_dung = ?, la_dap_an_dung = ? WHERE ma_dap_an = ?";
                    $db->execute($sqlDapAn, [$dapAn, $laDung, $maDapAn]);
                }
                
                $_SESSION['flash_message'] = 'Cập nhật câu hỏi thành công!';
                $_SESSION['flash_type'] = 'success';
                break;
                
            case 'delete_question':
                $maCauHoi = intval($_POST['ma_cau_hoi']);
                $db->execute("DELETE FROM cau_hoi_quiz WHERE ma_cau_hoi = ?", [$maCauHoi]);
                
                $_SESSION['flash_message'] = 'Xóa câu hỏi thành công!';
                $_SESSION['flash_type'] = 'success';
                break;
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = 'Lỗi: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
    }
    
    header('Location: quiz-vanhoa.php');
    exit;
}

// Lấy danh sách quiz
$quizzes = $db->query("
    SELECT q.*, v.tieu_de as ten_van_hoa,
           (SELECT COUNT(*) FROM cau_hoi_quiz WHERE ma_quiz = q.ma_quiz) as so_cau_hoi
    FROM quiz q
    LEFT JOIN van_hoa v ON q.ma_doi_tuong = v.ma_van_hoa
    WHERE q.loai_quiz = 'van_hoa'
    ORDER BY q.ngay_tao DESC
");

// Lấy danh sách văn hóa
$vanHoaList = $db->query("SELECT ma_van_hoa, tieu_de FROM van_hoa ORDER BY tieu_de");

// Kiểm tra nếu query trả về false
if ($quizzes === false) {
    $quizzes = [];
}
if ($vanHoaList === false) {
    $vanHoaList = [];
}

// Thống kê
$totalQuizzes = count($quizzes);
$activeQuizzes = count(array_filter($quizzes, fn($q) => $q['trang_thai'] === 'hoat_dong'));
$totalQuestions = array_sum(array_column($quizzes, 'so_cau_hoi'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Quiz Văn Hóa Khmer</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/quiz-admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Inline critical CSS */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: #f1f5f9; }
        .container-fluid { max-width: 1400px; margin: 0 auto; padding: 2rem; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <div class="page-title-wrapper">
                    <div class="page-icon-wrapper" style="background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);">
                        <i class="fas fa-book" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h1>Quản Lý Quiz Văn Hóa</h1>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; flex-wrap: nowrap;">
                    <a href="vanhoa.php" class="btn-add-new btn-back">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                    <button class="btn-add-new" onclick="$('#createQuizModal').modal('show')">
                        <i class="fas fa-plus-circle"></i> Tạo Quiz Mới
                    </button>
                </div>
            </div>
        </div>

        <!-- Flash Message -->
        <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert-modern alert-<?= $_SESSION['flash_type'] ?>">
            <i class="fas fa-<?= $_SESSION['flash_type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= $_SESSION['flash_message'] ?>
        </div>
        <?php 
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
        endif; 
        ?>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card" style="border-top: 4px solid #667eea;">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Tổng Quiz</div>
                        <div class="stat-number" style="color: #667eea;"><?= $totalQuizzes ?></div>
                    </div>
                    <div class="stat-icon-modern" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card" style="border-top: 4px solid #10b981;">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Đang Hoạt Động</div>
                        <div class="stat-number" style="color: #10b981;"><?= $activeQuizzes ?></div>
                    </div>
                    <div class="stat-icon-modern" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card" style="border-top: 4px solid #f59e0b;">
                <div class="stat-header">
                    <div>
                        <div class="stat-label">Tổng Câu Hỏi</div>
                        <div class="stat-number" style="color: #f59e0b;"><?= $totalQuestions ?></div>
                    </div>
                    <div class="stat-icon-modern" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-question"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-card-header">
                <h3 class="table-card-title">
                    <i class="fas fa-list"></i> Danh Sách Quiz
                </h3>
            </div>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th width="5%">STT</th>
                            <th width="30%">Tiêu Đề</th>
                            <th width="20%">Bài Viết</th>
                            <th width="10%">Câu Hỏi</th>
                            <th width="10%">Thời Gian</th>
                            <th width="10%">Trạng Thái</th>
                            <th width="15%">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($quizzes)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 3rem; color: #94a3b8;">
                                <i class="fas fa-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.5;"></i>
                                Chưa có quiz nào. Nhấn "Tạo Quiz Mới" để bắt đầu!
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($quizzes as $index => $quiz): ?>
                        <tr>
                            <td><strong><?= $index + 1 ?></strong></td>
                            <td>
                                <div style="font-weight: 600; color: #1e293b;">
                                    <?= htmlspecialchars($quiz['tieu_de']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($quiz['ten_van_hoa']) ?></td>
                            <td>
                                <span class="badge-modern badge-info">
                                    <i class="fas fa-list-ol"></i> <?= $quiz['so_cau_hoi'] ?> câu
                                </span>
                            </td>
                            <td><?= $quiz['thoi_gian'] ?>s</td>
                            <td>
                                <button class="badge-modern badge-<?= $quiz['trang_thai'] === 'hoat_dong' ? 'success' : 'secondary' ?>" 
                                        onclick="toggleStatus(<?= $quiz['ma_quiz'] ?>, '<?= $quiz['trang_thai'] ?>')"
                                        title="Click để thay đổi"
                                        style="white-space: nowrap;">
                                    <span><?= $quiz['trang_thai'] === 'hoat_dong' ? 'Hoạt động' : 'Tạm dừng' ?></span>
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </td>
                            <td>
                                <div class="action-btn-group">
                                    <button class="action-btn warning" onclick="editQuiz(<?= htmlspecialchars(json_encode($quiz), ENT_QUOTES) ?>)" title="Sửa Quiz">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button class="action-btn success" onclick="openAddQuestionsModal(<?= $quiz['ma_quiz'] ?>, '<?= htmlspecialchars($quiz['tieu_de'], ENT_QUOTES) ?>')" title="Thêm câu hỏi">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button class="action-btn info" onclick="viewQuestions(<?= $quiz['ma_quiz'] ?>, '<?= htmlspecialchars($quiz['tieu_de'], ENT_QUOTES) ?>')" title="Xem & Sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn danger" onclick="deleteQuiz(<?= $quiz['ma_quiz'] ?>)" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <?php include 'includes/quiz-modals.php'; ?>
    
    <!-- Scripts -->
    <script src="assets/js/quiz-admin.js"></script>
</body>
</html>
