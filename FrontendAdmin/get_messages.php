<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

checkAdminAuth();

header('Content-Type: application/json; charset=utf-8');

$db = Database::getInstance();
$admin_id = $_SESSION['admin_id'];

// Lấy tin nhắn
$messages_query = "
    SELECT 
        tn.ma_tin_nhan,
        tn.ma_nguoi_gui,
        tn.noi_dung,
        tn.trang_thai,
        tn.lien_ket,
        COALESCE(qtv.ten_dang_nhap, nd.ho_ten, 'Người dùng') as ten_nguoi_gui,
        SUBSTRING(tn.noi_dung, 1, 50) as noi_dung_preview,
        CASE 
            WHEN TIMESTAMPDIFF(SECOND, tn.ngay_tao, NOW()) < 60 
                THEN 'Vừa xong'
            WHEN TIMESTAMPDIFF(MINUTE, tn.ngay_tao, NOW()) < 60 
                THEN CONCAT(TIMESTAMPDIFF(MINUTE, tn.ngay_tao, NOW()), ' phút trước')
            WHEN TIMESTAMPDIFF(HOUR, tn.ngay_tao, NOW()) < 24 
                THEN CONCAT(TIMESTAMPDIFF(HOUR, tn.ngay_tao, NOW()), ' giờ trước')
            WHEN TIMESTAMPDIFF(DAY, tn.ngay_tao, NOW()) < 7 
                THEN CONCAT(TIMESTAMPDIFF(DAY, tn.ngay_tao, NOW()), ' ngày trước')
            ELSE DATE_FORMAT(tn.ngay_tao, '%d/%m/%Y')
        END as thoi_gian_hien_thi
    FROM tin_nhan tn
    LEFT JOIN quan_tri_vien qtv ON tn.ma_nguoi_gui = qtv.ma_qtv AND tn.loai_nguoi_gui = 'admin'
    LEFT JOIN nguoi_dung nd ON tn.ma_nguoi_gui = nd.ma_nguoi_dung AND tn.loai_nguoi_gui = 'user'
    WHERE tn.ma_nguoi_nhan = ?
    ORDER BY 
        CASE WHEN tn.trang_thai = 'chua_doc' THEN 0 ELSE 1 END,
        tn.ngay_tao DESC 
    LIMIT 10
";

$messages = $db->query($messages_query, [$admin_id]) ?: [];

// Thêm dấu ... nếu nội dung dài hơn
foreach($messages as &$msg) {
    if(mb_strlen($msg['noi_dung']) > 50) {
        $msg['noi_dung_preview'] .= '...';
    }
}

// Đếm số tin nhắn chưa đọc
$unread_count = $db->querySingle(
    "SELECT COUNT(*) as count FROM tin_nhan WHERE ma_nguoi_nhan = ? AND trang_thai = 'chua_doc'",
    [$admin_id]
)['count'] ?? 0;

echo json_encode([
    'success' => true,
    'messages' => $messages,
    'unread_count' => (int)$unread_count
], JSON_UNESCAPED_UNICODE);
