<?php
/**
 * Chatbot API với xác thực và lưu lịch sử
 * Kết nối giữa PHP frontend và Node.js Cerebras backend
 * Có fallback response khi server không khả dụng
 */

// Khởi tạo session trước khi làm bất cứ điều gì
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Kiểm tra đăng nhập - hỗ trợ cả 2 cách lưu session
 */
function isUserLoggedIn()
{
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return true;
    }
    if (isset($_SESSION['user']['ma_nguoi_dung']) && !empty($_SESSION['user']['ma_nguoi_dung'])) {
        $_SESSION['user_id'] = $_SESSION['user']['ma_nguoi_dung'];
        return true;
    }
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        return true;
    }
    return false;
}

/**
 * Lấy user ID từ session
 */
function getUserId()
{
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        return intval($_SESSION['user_id']);
    }
    if (isset($_SESSION['user']['ma_nguoi_dung']) && !empty($_SESSION['user']['ma_nguoi_dung'])) {
        return intval($_SESSION['user']['ma_nguoi_dung']);
    }
    return null;
}

/**
 * Fallback response khi không kết nối được Node.js server
 */
function getFallbackResponse($message)
{
    $message = mb_strtolower($message, 'UTF-8');
    
    // Các câu trả lời mẫu về văn hóa Khmer
    $responses = [
        'chào' => 'Xin chào! 👋 Tôi là trợ lý AI về văn hóa Khmer Nam Bộ. Tôi có thể giúp bạn tìm hiểu về văn hóa, lễ hội, chùa chiền và ngôn ngữ Khmer. Bạn muốn hỏi gì?',
        'hello' => 'Xin chào! 👋 Tôi là trợ lý AI về văn hóa Khmer Nam Bộ. Bạn cần tôi giúp gì?',
        'văn hóa' => 'Văn hóa Khmer Nam Bộ là một nền văn hóa phong phú với nhiều đặc trưng độc đáo:

🏛️ **Kiến trúc**: Các ngôi chùa Khmer với mái cong nhiều tầng, trang trí tinh xảo
🎭 **Nghệ thuật**: Múa Robam, nhạc cụ Skor, điêu khắc Phật giáo
👗 **Trang phục**: Sampot - trang phục truyền thống đặc trưng
🍜 **Ẩm thực**: Bún nước lèo, bánh tét, các món ăn đặc sản

Bạn muốn tìm hiểu thêm về khía cạnh nào?',
        'lễ hội' => 'Người Khmer Nam Bộ có nhiều lễ hội truyền thống quan trọng:

🎉 **Chol Chnam Thmay** (Tết Khmer): Lễ hội lớn nhất, diễn ra vào tháng 4
🌙 **Ok Om Bok** (Lễ Cúng Trăng): Rằm tháng 10, có đua ghe ngo
🙏 **Pchum Ben** (Lễ Cúng Ông Bà): Kéo dài 15 ngày, tưởng nhớ tổ tiên
⭐ **Dolta**: Lễ cúng sao giải hạn

Bạn muốn biết chi tiết về lễ hội nào?',
        'chùa' => 'Chùa Khmer là trung tâm văn hóa và tâm linh của cộng đồng:

🏛️ **Đặc điểm kiến trúc**:
- Mái cong nhiều tầng, trang trí hoa văn tinh xảo
- Tượng Phật và các vị thần Hindu
- Sân chùa rộng cho các hoạt động cộng đồng

📍 **Một số chùa nổi tiếng**:
- Chùa Âng (Trà Vinh)
- Chùa Dơi (Sóc Trăng)
- Chùa Xiêm Cán (Bạc Liêu)

Bạn muốn tìm hiểu về chùa nào cụ thể?',
        'tiếng khmer' => 'Tiếng Khmer là ngôn ngữ của người Khmer với hệ chữ viết riêng:

📚 **Đặc điểm**:
- Có nguồn gốc từ chữ Phạn (Sanskrit)
- Viết từ trái sang phải
- Có 33 phụ âm và nhiều nguyên âm

🗣️ **Một số từ cơ bản**:
- Xin chào: ជំរាបសួរ (Chom reap suor)
- Cảm ơn: អរគុណ (Orkun)
- Tạm biệt: លាហើយ (Lea haoey)

Bạn muốn học thêm từ vựng nào?',
        'truyện' => 'Truyện dân gian Khmer rất phong phú và mang nhiều bài học:

📖 **Các thể loại**:
- Truyện cổ tích: Thạch Sanh, Tấm Cám phiên bản Khmer
- Truyền thuyết: Về nguồn gốc các địa danh, lễ hội
- Truyện ngụ ngôn: Bài học đạo đức qua các con vật

🌟 **Đặc điểm**:
- Thường có yếu tố Phật giáo
- Đề cao lòng hiếu thảo, nhân nghĩa
- Kết thúc có hậu

Bạn muốn nghe truyện nào?',
        'cảm ơn' => 'Không có gì! 😊 Rất vui được giúp bạn tìm hiểu về văn hóa Khmer Nam Bộ. Nếu có câu hỏi gì khác, đừng ngại hỏi nhé!',
        'tạm biệt' => 'Tạm biệt! 👋 Hẹn gặp lại bạn. Chúc bạn có những trải nghiệm thú vị khi khám phá văn hóa Khmer Nam Bộ!'
    ];
    
    // Tìm câu trả lời phù hợp
    foreach ($responses as $keyword => $response) {
        if (strpos($message, $keyword) !== false) {
            return $response;
        }
    }
    
    // Câu trả lời mặc định
    return 'Cảm ơn bạn đã hỏi! 🙏

Tôi có thể giúp bạn tìm hiểu về:
• **Văn hóa Khmer**: Phong tục, nghệ thuật, ẩm thực
• **Lễ hội**: Chol Chnam Thmay, Ok Om Bok, Pchum Ben
• **Chùa Khmer**: Kiến trúc, lịch sử các ngôi chùa
• **Tiếng Khmer**: Từ vựng, cách phát âm cơ bản
• **Truyện dân gian**: Các câu chuyện truyền thống

Hãy hỏi cụ thể hơn để tôi có thể giúp bạn tốt hơn nhé!';
}

// Kiểm tra đăng nhập
if (!isUserLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'Vui lòng đăng nhập để sử dụng chatbot',
        'requireLogin' => true
    ]);
    exit();
}

$userId = getUserId();
$pdo = getDBConnection();

// Kiểm tra và tạo bảng chatbot_history nếu chưa có
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `chatbot_history` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `message` text NOT NULL,
            `sender` enum('user','bot') NOT NULL DEFAULT 'user',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    // Bảng có thể đã tồn tại, bỏ qua lỗi
}

// GET: Lấy lịch sử chat
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT message, sender, created_at 
            FROM chatbot_history 
            WHERE user_id = ? 
            ORDER BY created_at ASC
            LIMIT 100
        ");
        $stmt->execute([$userId]);
        $history = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'history' => $history
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể tải lịch sử chat'
        ]);
    }
    exit();
}

// DELETE: Xóa lịch sử chat
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        $stmt = $pdo->prepare("DELETE FROM chatbot_history WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Đã xóa lịch sử chat'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Không thể xóa lịch sử chat'
        ]);
    }
    exit();
}

// POST: Gửi tin nhắn
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['message']) || empty(trim($input['message']))) {
        http_response_code(400);
        echo json_encode(['error' => 'Tin nhắn không được để trống']);
        exit();
    }

    $message = trim($input['message']);
    
    // Lưu tin nhắn người dùng vào database
    try {
        $stmt = $pdo->prepare("
            INSERT INTO chatbot_history (user_id, message, sender) 
            VALUES (?, ?, 'user')
        ");
        $stmt->execute([$userId, $message]);
    } catch (PDOException $e) {
        error_log("Lỗi lưu tin nhắn: " . $e->getMessage());
    }

    // Lấy lịch sử hội thoại từ database
    $conversationHistory = [];
    try {
        $stmt = $pdo->prepare("
            SELECT message, sender 
            FROM chatbot_history 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([$userId]);
        $dbHistory = $stmt->fetchAll();
        
        foreach (array_reverse($dbHistory) as $item) {
            $conversationHistory[] = [
                'role' => $item['sender'] === 'user' ? 'user' : 'assistant',
                'content' => $item['message']
            ];
        }
    } catch (PDOException $e) {
        $conversationHistory = [];
    }

    // Thử kết nối Node.js chatbot server
    $chatbotUrl = 'http://localhost:3000/api/chat';
    $postData = json_encode([
        'message' => $message,
        'conversationHistory' => $conversationHistory
    ]);

    $reply = null;
    $useNodeJs = false;

    // Thử gọi Node.js server
    $ch = curl_init($chatbotUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response !== false && $httpCode === 200) {
        $responseData = json_decode($response, true);
        if (isset($responseData['success']) && $responseData['success'] && isset($responseData['reply'])) {
            $reply = $responseData['reply'];
            $useNodeJs = true;
        }
    }

    // Nếu Node.js không khả dụng, sử dụng fallback
    if (!$reply) {
        $reply = getFallbackResponse($message);
    }

    // Lưu phản hồi của bot vào database
    try {
        $stmt = $pdo->prepare("
            INSERT INTO chatbot_history (user_id, message, sender) 
            VALUES (?, ?, 'bot')
        ");
        $stmt->execute([$userId, $reply]);
    } catch (PDOException $e) {
        error_log("Lỗi lưu phản hồi bot: " . $e->getMessage());
    }

    echo json_encode([
        'success' => true,
        'reply' => $reply,
        'source' => $useNodeJs ? 'ai' : 'fallback',
        'timestamp' => date('c')
    ]);
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
