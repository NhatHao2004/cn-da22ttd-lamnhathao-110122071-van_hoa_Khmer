<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

checkAdminAuth();
refreshAdminInfo();

$db = Database::getInstance();

// Disable cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// Lấy filter
$loaiFilter = $_GET['loai'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Lấy dữ liệu từ các bảng quiz hiện có
$quizzes = [];

try {
    // 1. Quiz Văn hóa
    $vanHoaQuizzes = $db->query("
        SELECT 
            CONCAT('vh_', q.ma_quiz) as id_unique,
            q.ma_quiz,
            q.tieu_de,
            q.mo_ta,
            'van_hoa' as loai_bai_kiem_tra,
            q.ma_van_hoa as ma_doi_tuong,
            q.thoi_gian as thoi_gian_lam_bai,
            'trung_binh' as muc_do,
            q.trang_thai,
            q.ngay_tao,
            v.tieu_de as ten_doi_tuong,
            (SELECT COUNT(*) FROM cau_hoi_quiz WHERE ma_quiz = q.ma_quiz) as so_cau_hoi
        FROM quiz_van_hoa q
        LEFT JOIN van_hoa v ON q.ma_van_hoa = v.ma_van_hoa
    ");
    if ($vanHoaQuizzes && is_array($vanHoaQuizzes)) {
        $quizzes = array_merge($quizzes, $vanHoaQuizzes);
    }
    
    // 2. Quiz Chùa
    $chuaQuizzes = $db->query("
        SELECT 
            CONCAT('ch_', q.ma_quiz) as id_unique,
            q.ma_quiz,
            q.tieu_de,
            q.mo_ta,
            'chua' as loai_bai_kiem_tra,
            q.ma_chua as ma_doi_tuong,
            q.thoi_gian as thoi_gian_lam_bai,
            'trung_binh' as muc_do,
            q.trang_thai,
            q.ngay_tao,
            c.ten_chua as ten_doi_tuong,
            (SELECT COUNT(*) FROM cau_hoi_quiz_chua WHERE ma_quiz = q.ma_quiz) as so_cau_hoi
        FROM quiz_chua q
        LEFT JOIN chua c ON q.ma_chua = c.ma_chua
    ");
    if ($chuaQuizzes && is_array($chuaQuizzes)) {
        $quizzes = array_merge($quizzes, $chuaQuizzes);
    }
    
    // 3. Quiz Lễ hội
    $leHoiQuizzes = $db->query("
        SELECT 
            CONCAT('lh_', q.ma_quiz) as id_unique,
            q.ma_quiz,
            q.tieu_de,
            q.mo_ta,
            'le_hoi' as loai_bai_kiem_tra,
            q.ma_le_hoi as ma_doi_tuong,
            q.thoi_gian as thoi_gian_lam_bai,
            'trung_binh' as muc_do,
            q.trang_thai,
            q.ngay_tao,
            l.ten_le_hoi as ten_doi_tuong,
            (SELECT COUNT(*) FROM cau_hoi_quiz_le_hoi WHERE ma_quiz = q.ma_quiz) as so_cau_hoi
        FROM quiz_le_hoi q
        LEFT JOIN le_hoi l ON q.ma_le_hoi = l.ma_le_hoi
    ");
    if ($leHoiQuizzes && is_array($leHoiQuizzes)) {
        $quizzes = array_merge($quizzes, $leHoiQuizzes);
    }
    
    // 4. Quiz Truyện dân gian
    $truyenQuizzes = $db->query("
        SELECT 
            CONCAT('tr_', q.ma_quiz) as id_unique,
            q.ma_quiz,
            q.tieu_de,
            q.mo_ta,
            'truyen_dan_gian' as loai_bai_kiem_tra,
            q.ma_truyen as ma_doi_tuong,
            q.thoi_gian as thoi_gian_lam_bai,
            'trung_binh' as muc_do,
            q.trang_thai,
            q.ngay_tao,
            t.tieu_de as ten_doi_tuong,
            (SELECT COUNT(*) FROM cau_hoi_quiz_truyen WHERE ma_quiz = q.ma_quiz) as so_cau_hoi
        FROM quiz_truyen_dan_gian q
        LEFT JOIN truyen_dan_gian t ON q.ma_truyen = t.ma_truyen
    ");
    if ($truyenQuizzes && is_array($truyenQuizzes)) {
        $quizzes = array_merge($quizzes, $truyenQuizzes);
    }
    
} catch (Exception $e) {
    // Nếu có lỗi, quizzes vẫn là mảng rỗng
}

// Áp dụng filter
if ($loaiFilter) {
    $quizzes = array_filter($quizzes, fn($q) => $q['loai_bai_kiem_tra'] === $loaiFilter);
}

if ($searchQuery) {
    $quizzes = array_filter($quizzes, function($q) use ($searchQuery) {
        return stripos($q['tieu_de'], $searchQuery) !== false || 
               stripos($q['mo_ta'] ?? '', $searchQuery) !== false;
    });
}

// Sắp xếp theo ngày tạo mới nhất
usort($quizzes, function($a, $b) {
    return strtotime($b['ngay_tao']) - strtotime($a['ngay_tao']);
});

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
    <title>Tổng Quan Quiz - Quản Lý</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
        
        .main-container { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 24px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .page-header-content { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem; }
        .page-title-wrapper { display: flex; align-items: center; gap: 1.5rem; }
        
        .page-icon-wrapper {
            width: 80px; height: 80px;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
        }
        
        .page-header h1 { font-size: 2.5rem; font-weight: 800; margin: 0; }
        .page-header p { opacity: 0.9; margin: 0.5rem 0 0; font-size: 1.125rem; }
        
        .btn-add-new {
            background: white;
            color: #667eea;
            border: none;
            padding: 1rem 2rem;
            border-radius: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            text-decoration: none;
        }
        
        .btn-add-new:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(0,0,0,0.2);
            color: #667eea;
        }
        
        .btn-back {
            background: rgba(255,255,255,0.2);
            color: white;
            backdrop-filter: blur(10px);
        }
        
        .btn-back:hover { color: white; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 50px rgba(0,0,0,0.12); }
        
        .stat-header { display: flex; justify-content: space-between; align-items: center; }
        
        .stat-label { font-size: 0.875rem; color: #64748b; font-weight: 600; margin-bottom: 0.5rem; }
        .stat-number { font-size: 2.5rem; font-weight: 800; }
        
        .stat-icon-modern {
            width: 60px; height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
        }
        
        .filter-section {
            background: white;
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        
        .filter-form { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
        
        .filter-form select,
        .filter-form input {
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.9375rem;
            transition: all 0.3s;
        }
        
        .filter-form select:focus,
        .filter-form input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .btn-filter {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-reset {
            padding: 0.75rem 1.5rem;
            background: #f1f5f9;
            color: #64748b;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-reset:hover {
            background: #e2e8f0;
            color: #475569;
        }
        
        .table-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .table-card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #f1f5f9;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        }
        
        .table-card-title {
            font-size: 1.375rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .modern-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .modern-table thead th {
            background: #f8fafc;
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 700;
            color: #475569;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .modern-table tbody td {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        
        .modern-table tbody tr:hover {
            background: #f8fafc;
        }
        
        .badge-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.8125rem;
            font-weight: 600;
        }
        
        .badge-success { background: rgba(16, 185, 129, 0.15); color: #10b981; }
        .badge-secondary { background: rgba(148, 163, 184, 0.15); color: #64748b; }
        .badge-info { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
        .badge-warning { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .badge-danger { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
        .badge-primary { background: rgba(102, 126, 234, 0.15); color: #667eea; }
        
        .action-btn {
            width: 36px; height: 36px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            color: white;
            text-decoration: none;
        }
        
        .action-btn.info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        
        .action-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #94a3b8;
        }
        
        .empty-state i {
            font-size: 4rem;
            display: block;
            margin-bottom: 1.5rem;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .page-header-content { flex-direction: column; align-items: flex-start; }
            .stats-grid { grid-template-columns: 1fr; }
            .filter-form { flex-direction: column; }
            .filter-form select, .filter-form input { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header-content">
                <div class="page-title-wrapper">
                    <div class="page-icon-wrapper">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <h1>Tổng Quan Quiz</h1>
                        <p>Xem tổng hợp tất cả quiz từ các module khác nhau</p>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; flex-wrap: nowrap;">
                    <a href="index.php" class="btn-add-new btn-back">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                    <a href="fix-quiz-foreign-keys.php" class="btn-add-new" style="background: rgba(255,255,255,0.2); color: white;">
                        <i class="fas fa-wrench"></i> Sửa Foreign Keys
                    </a>
                </div>
            </div>
        </div>

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

        <!-- Filter Section -->
        <div class="filter-section">
            <form class="filter-form" method="GET">
                <div style="flex: 1; min-width: 250px;">
                    <input type="text" name="search" placeholder="Tìm kiếm quiz..." 
                           value="<?= htmlspecialchars($searchQuery) ?>" class="form-control">
                </div>
                <div style="min-width: 200px;">
                    <select name="loai" class="form-control">
                        <option value="">Tất cả loại</option>
                        <option value="van_hoa" <?= $loaiFilter === 'van_hoa' ? 'selected' : '' ?>>Văn hóa</option>
                        <option value="chua" <?= $loaiFilter === 'chua' ? 'selected' : '' ?>>Chùa</option>
                        <option value="le_hoi" <?= $loaiFilter === 'le_hoi' ? 'selected' : '' ?>>Lễ hội</option>
                        <option value="truyen_dan_gian" <?= $loaiFilter === 'truyen_dan_gian' ? 'selected' : '' ?>>Truyện dân gian</option>
                    </select>
                </div>
                <button type="submit" class="btn-filter">
                    <i class="fas fa-search"></i> Lọc
                </button>
                <?php if ($loaiFilter || $searchQuery): ?>
                <a href="quiz-manager.php" class="btn-reset">
                    <i class="fas fa-times"></i> Xóa lọc
                </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-card-header">
                <h3 class="table-card-title">
                    <i class="fas fa-list"></i> Danh Sách Quiz (<?= $totalQuizzes ?>)
                </h3>
            </div>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th width="5%">ID</th>
                            <th width="30%">Tiêu Đề</th>
                            <th width="12%">Loại</th>
                            <th width="20%">Liên Kết</th>
                            <th width="8%">Câu Hỏi</th>
                            <th width="10%">Thời Gian</th>
                            <th width="10%">Trạng Thái</th>
                            <th width="5%">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($quizzes)): ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <div style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem;">Chưa có quiz nào</div>
                                <div style="font-size: 0.9375rem;">Hãy tạo quiz mới từ các trang quản lý riêng</div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($quizzes as $quiz): 
                            $loaiLabels = [
                                'van_hoa' => ['text' => 'Văn hóa', 'color' => 'primary', 'page' => 'quiz-vanhoa.php'],
                                'chua' => ['text' => 'Chùa', 'color' => 'info', 'page' => 'quiz-chua.php'],
                                'le_hoi' => ['text' => 'Lễ hội', 'color' => 'warning', 'page' => 'quiz-lehoi.php'],
                                'truyen_dan_gian' => ['text' => 'Truyện', 'color' => 'success', 'page' => 'quiz-truyendangian.php']
                            ];
                            $loaiInfo = $loaiLabels[$quiz['loai_bai_kiem_tra']] ?? ['text' => 'Khác', 'color' => 'secondary', 'page' => '#'];
                        ?>
                        <tr>
                            <td><strong>#<?= $quiz['ma_quiz'] ?></strong></td>
                            <td>
                                <div style="font-weight: 600; color: #1e293b; margin-bottom: 0.25rem;">
                                    <?= htmlspecialchars($quiz['tieu_de']) ?>
                                </div>
                                <?php if (!empty($quiz['mo_ta'])): ?>
                                <div style="font-size: 0.8125rem; color: #64748b;">
                                    <?= htmlspecialchars(mb_substr($quiz['mo_ta'], 0, 60)) ?><?= mb_strlen($quiz['mo_ta']) > 60 ? '...' : '' ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge-modern badge-<?= $loaiInfo['color'] ?>">
                                    <?= $loaiInfo['text'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($quiz['ten_doi_tuong']): ?>
                                <div style="font-size: 0.875rem; color: #64748b;">
                                    <i class="fas fa-link"></i> <?= htmlspecialchars($quiz['ten_doi_tuong']) ?>
                                </div>
                                <?php else: ?>
                                <div style="font-size: 0.875rem; color: #94a3b8; font-style: italic;">
                                    <i class="fas fa-unlink"></i> Không liên kết
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge-modern badge-info">
                                    <i class="fas fa-list-ol"></i> <?= $quiz['so_cau_hoi'] ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 0.875rem; color: #64748b;">
                                    <i class="far fa-clock"></i> <?= $quiz['thoi_gian_lam_bai'] ?>s
                                </div>
                            </td>
                            <td>
                                <span class="badge-modern badge-<?= $quiz['trang_thai'] === 'hoat_dong' ? 'success' : 'secondary' ?>">
                                    <?= $quiz['trang_thai'] === 'hoat_dong' ? 'Hoạt động' : 'Tạm dừng' ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= $loaiInfo['page'] ?>" class="action-btn info" title="Quản lý chi tiết">
                                    <i class="fas fa-cog"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Info Box -->
        <div style="background: white; border-radius: 20px; padding: 2rem; margin-top: 2rem; box-shadow: 0 10px 40px rgba(0,0,0,0.08);">
            <h3 style="color: #1e293b; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.75rem;">
                <i class="fas fa-info-circle" style="color: #667eea;"></i>
                Hướng Dẫn Sử Dụng
            </h3>
            <div style="color: #64748b; line-height: 1.8;">
                <p style="margin-bottom: 1rem;">
                    <strong>Trang này chỉ hiển thị tổng quan</strong> - Để quản lý chi tiết quiz (thêm/sửa/xóa), vui lòng:
                </p>
                <ul style="list-style: none; padding: 0; display: grid; gap: 0.75rem;">
                    <li style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="badge-modern badge-primary">Văn hóa</span>
                        <span>→ Click icon <i class="fas fa-cog"></i> hoặc vào <a href="quiz-vanhoa.php" style="color: #667eea; font-weight: 600;">quiz-vanhoa.php</a></span>
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="badge-modern badge-info">Chùa</span>
                        <span>→ Click icon <i class="fas fa-cog"></i> hoặc vào <a href="quiz-chua.php" style="color: #667eea; font-weight: 600;">quiz-chua.php</a></span>
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="badge-modern badge-warning">Lễ hội</span>
                        <span>→ Click icon <i class="fas fa-cog"></i> hoặc vào <a href="quiz-lehoi.php" style="color: #667eea; font-weight: 600;">quiz-lehoi.php</a></span>
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="badge-modern badge-success">Truyện</span>
                        <span>→ Click icon <i class="fas fa-cog"></i> hoặc vào <a href="quiz-truyendangian.php" style="color: #667eea; font-weight: 600;">quiz-truyendangian.php</a></span>
                    </li>
                </ul>
                <p style="margin-top: 1.5rem; padding: 1rem; background: rgba(102, 126, 234, 0.05); border-radius: 12px; border-left: 4px solid #667eea;">
                    <i class="fas fa-lightbulb" style="color: #f59e0b;"></i>
                    <strong>Mẹo:</strong> Nếu quiz không có liên kết (hiển thị "Không liên kết"), bạn có thể vào trang quản lý riêng để liên kết lại với bài viết mới.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
