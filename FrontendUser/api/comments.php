<?php
/**
 * API xử lý bình luận
 */

// Khởi tạo session trước
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/comments.php';

$response = ['success' => false, 'message' => ''];

// Hàm lấy user ID từ session - hỗ trợ cả 2 cách lưu
function getSessionUserId() {
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return intval($_SESSION['user_id']);
    }
    if (isset($_SESSION['user']['ma_nguoi_dung']) && !empty($_SESSION['user']['ma_nguoi_dung'])) {
        return intval($_SESSION['user']['ma_nguoi_dung']);
    }
    return null;
}

// Kiểm tra đăng nhập cho các action cần thiết
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$requireAuth = ['add', 'like', 'report', 'delete'];

$sessionUserId = getSessionUserId();

if (in_array($action, $requireAuth) && !$sessionUserId) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
    exit;
}

// Đồng bộ session nếu cần
if ($sessionUserId && !isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = $sessionUserId;
}

switch ($action) {
    case 'get':
        // Lấy danh sách bình luận
        $loai = $_GET['loai'] ?? '';
        $ma_noi_dung = intval($_GET['ma_noi_dung'] ?? 0);
        $limit = intval($_GET['limit'] ?? 20);
        
        if (!$loai || !$ma_noi_dung) {
            $response['message'] = 'Thiếu thông tin';
            break;
        }
        
        $comments = getComments($loai, $ma_noi_dung, $limit);
        $total = countComments($loai, $ma_noi_dung);
        
        // Thêm thông tin like cho user hiện tại
        foreach ($comments as &$comment) {
            $comment['user_liked'] = isLoggedIn() ? hasLikedComment($comment['ma_binh_luan'], $_SESSION['user_id']) : false;
            $comment['replies'] = getReplies($comment['ma_binh_luan']);
            foreach ($comment['replies'] as &$reply) {
                $reply['user_liked'] = isLoggedIn() ? hasLikedComment($reply['ma_binh_luan'], $_SESSION['user_id']) : false;
            }
            $comment['time_ago'] = timeAgo($comment['ngay_tao']);
        }
        
        $response = [
            'success' => true,
            'comments' => $comments,
            'total' => $total
        ];
        break;
        
    case 'add':
        // Thêm bình luận mới
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $response['message'] = 'Token không hợp lệ';
            break;
        }
        
        $noi_dung = trim($_POST['noi_dung'] ?? '');
        $loai = $_POST['loai'] ?? '';
        $ma_noi_dung = intval($_POST['ma_noi_dung'] ?? 0);
        $ma_cha = !empty($_POST['ma_cha']) ? intval($_POST['ma_cha']) : null;
        
        if (empty($noi_dung) || strlen($noi_dung) < 2) {
            $response['message'] = 'Nội dung quá ngắn';
            break;
        }
        
        if (strlen($noi_dung) > 2000) {
            $response['message'] = 'Nội dung quá dài (tối đa 2000 ký tự)';
            break;
        }
        
        $commentId = addComment([
            'ma_nguoi_dung' => $sessionUserId,
            'loai_noi_dung' => $loai,
            'ma_noi_dung' => $ma_noi_dung,
            'ma_binh_luan_cha' => $ma_cha,
            'noi_dung' => $noi_dung
        ]);
        
        if ($commentId) {
            // Lấy thông tin bình luận vừa tạo
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT bl.*, nd.ho_ten, nd.anh_dai_dien FROM binh_luan bl JOIN nguoi_dung nd ON bl.ma_nguoi_dung = nd.ma_nguoi_dung WHERE bl.ma_binh_luan = ?");
            $stmt->execute([$commentId]);
            $newComment = $stmt->fetch();
            $newComment['time_ago'] = 'Vừa xong';
            $newComment['user_liked'] = false;
            $newComment['replies'] = [];
            
            $response = [
                'success' => true,
                'message' => 'Đã đăng bình luận',
                'comment' => $newComment
            ];
        } else {
            $response['message'] = 'Không thể đăng bình luận';
        }
        break;
        
    case 'like':
        // Like/Unlike bình luận
        $ma_binh_luan = intval($_POST['ma_binh_luan'] ?? 0);
        
        if (!$ma_binh_luan) {
            $response['message'] = 'Thiếu thông tin';
            break;
        }
        
        $result = toggleLikeComment($ma_binh_luan, $sessionUserId);
        $response = [
            'success' => true,
            'action' => $result['action'],
            'likes' => $result['likes']
        ];
        break;
        
    case 'report':
        // Báo cáo vi phạm
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $response['message'] = 'Token không hợp lệ';
            break;
        }
        
        $ma_binh_luan = intval($_POST['ma_binh_luan'] ?? 0);
        $ly_do = $_POST['ly_do'] ?? '';
        $mo_ta = trim($_POST['mo_ta'] ?? '');
        
        if (!$ma_binh_luan || !in_array($ly_do, ['spam', 'xuc_pham', 'sai_su_that', 'khac'])) {
            $response['message'] = 'Thông tin không hợp lệ';
            break;
        }
        
        if (reportComment($ma_binh_luan, $sessionUserId, $ly_do, $mo_ta)) {
            $response = ['success' => true, 'message' => 'Đã gửi báo cáo'];
        } else {
            $response['message'] = 'Không thể gửi báo cáo';
        }
        break;
        
    case 'delete':
        // Xóa bình luận (chỉ chủ sở hữu)
        $ma_binh_luan = intval($_POST['ma_binh_luan'] ?? 0);
        
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT ma_nguoi_dung FROM binh_luan WHERE ma_binh_luan = ?");
        $stmt->execute([$ma_binh_luan]);
        $comment = $stmt->fetch();
        
        if (!$comment || $comment['ma_nguoi_dung'] != $sessionUserId) {
            $response['message'] = 'Không có quyền xóa';
            break;
        }
        
        $stmt = $pdo->prepare("UPDATE binh_luan SET trang_thai = 'an' WHERE ma_binh_luan = ?");
        if ($stmt->execute([$ma_binh_luan])) {
            $response = ['success' => true, 'message' => 'Đã xóa bình luận'];
        }
        break;
        
    default:
        $response['message'] = 'Action không hợp lệ';
}

echo json_encode($response);
