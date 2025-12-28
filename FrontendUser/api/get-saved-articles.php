<?php
/**
 * API lấy thông tin bài viết đã lưu
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Chỉ include config, không include header đầy đủ để tránh output HTML
require_once __DIR__ . '/../config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $ids = $input['ids'] ?? [];
    
    if (empty($ids)) {
        echo json_encode(['success' => true, 'articles' => []]);
        exit;
    }
    
    // Sanitize IDs - chỉ giữ số nguyên dương
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, function($id) { return $id > 0; });
    
    if (empty($ids)) {
        echo json_encode(['success' => true, 'articles' => []]);
        exit;
    }
    
    $pdo = getDBConnection();
    
    // Build query với placeholders
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT ma_van_hoa, tieu_de, tieu_de_khmer, hinh_anh_chinh, luot_xem, ngay_tao 
            FROM van_hoa 
            WHERE ma_van_hoa IN ($placeholders) AND trang_thai = 'xuat_ban'
            ORDER BY ngay_tao DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Xử lý đường dẫn ảnh
    foreach ($articles as &$article) {
        $imgPath = $article['hinh_anh_chinh'] ?? '';
        if (empty($imgPath)) {
            $article['image_url'] = '';
        } elseif (strpos($imgPath, 'http') === 0) {
            $article['image_url'] = $imgPath;
        } elseif (strpos($imgPath, 'uploads/') === 0) {
            $article['image_url'] = '/DoAn_ChuyenNganh/' . $imgPath;
        } else {
            $article['image_url'] = '/DoAn_ChuyenNganh/uploads/vanhoa/' . $imgPath;
        }
    }
    
    echo json_encode(['success' => true, 'articles' => $articles]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
