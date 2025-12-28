<?php
/**
 * API lấy danh sách câu hỏi của quiz
 */
header('Content-Type: application/json');
require_once 'includes/auth.php';
require_once 'config/database.php';

// Kiểm tra session
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$quizId = intval($_GET['quiz_id'] ?? 0);

if (!$quizId) {
    echo json_encode(['success' => false, 'message' => 'Quiz ID không hợp lệ']);
    exit;
}

try {
    $db = Database::getInstance();
    
    // Lấy danh sách câu hỏi
    $questions = $db->query("
        SELECT * FROM cau_hoi_quiz 
        WHERE ma_quiz = ? 
        ORDER BY thu_tu
    ", [$quizId]);
    
    // Lấy đáp án cho từng câu hỏi
    $answers = [];
    foreach ($questions as $q) {
        $result = $db->query("
            SELECT * FROM dap_an_quiz 
            WHERE ma_cau_hoi = ? 
            ORDER BY thu_tu
        ", [$q['ma_cau_hoi']]);
        
        // Đảm bảo luôn trả về mảng, không phải false
        $answers[$q['ma_cau_hoi']] = is_array($result) ? $result : [];
    }
    
    echo json_encode([
        'success' => true,
        'questions' => $questions,
        'answers' => $answers
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
