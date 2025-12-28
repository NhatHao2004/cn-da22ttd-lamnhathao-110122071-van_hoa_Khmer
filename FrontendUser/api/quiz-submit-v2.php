<?php
/**
 * API lưu kết quả quiz và cộng điểm cho người dùng
 * Mỗi câu đúng = 10 điểm
 * VERSION 2 - Simplified error handling
 */

// Prevent any output before JSON
ob_start();

// Function to send JSON response and exit
function sendJSON($data) {
    // Clear any previous output
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

// Set error handler
set_exception_handler(function($e) {
    sendJSON([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
});

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load dependencies
try {
    require_once __DIR__ . '/../config/database.php';
    
    // Badge system is optional
    if (file_exists(__DIR__ . '/../includes/badge-system.php')) {
        require_once __DIR__ . '/../includes/badge-system.php';
    }
} catch (Exception $e) {
    sendJSON([
        'success' => false,
        'message' => 'Failed to load dependencies: ' . $e->getMessage()
    ]);
}

// Check login
$userId = null;
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $userId = intval($_SESSION['user_id']);
} elseif (isset($_SESSION['user']['ma_nguoi_dung']) && !empty($_SESSION['user']['ma_nguoi_dung'])) {
    $userId = intval($_SESSION['user']['ma_nguoi_dung']);
}

if (!$userId) {
    sendJSON(['success' => false, 'message' => 'Vui lòng đăng nhập']);
}

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSON(['success' => false, 'message' => 'Method not allowed']);
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    sendJSON(['success' => false, 'message' => 'Invalid JSON input']);
}

$quizType = $input['quiz_type'] ?? '';
$quizId = intval($input['quiz_id'] ?? 0);
$correctCount = intval($input['correct_count'] ?? 0);
$totalQuestions = intval($input['total_questions'] ?? 0);
$timeSpent = intval($input['time_spent'] ?? 0);

if (!$quizType || !$quizId || $totalQuestions <= 0) {
    sendJSON(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
}

// Calculate score
// Chỉ được điểm nếu làm đúng 100% câu hỏi
if ($correctCount === $totalQuestions) {
    $currentScore = 50; // Cố định 50 điểm cho quiz hoàn hảo
} else {
    $currentScore = 0; // Không được điểm nếu sai bất kỳ câu nào
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();

    // Determine result table
    $resultTable = '';
    $quizObjectType = '';
    switch ($quizType) {
        case 'van_hoa':
            $resultTable = 'ket_qua_quiz';
            $quizObjectType = 'van_hoa';
            break;
        case 'chua':
            $resultTable = 'ket_qua_quiz_chua';
            $quizObjectType = 'chua';
            break;
        case 'le_hoi':
            $resultTable = 'ket_qua_quiz_le_hoi';
            $quizObjectType = 'le_hoi';
            break;
        case 'truyen':
            $resultTable = 'ket_qua_quiz_truyen';
            $quizObjectType = 'truyen';
            break;
        default:
            throw new Exception('Loại quiz không hợp lệ');
    }
    
    // Get previous max score
    $maxPreviousScore = 0;
    $tableCheck = $pdo->query("SHOW TABLES LIKE '$resultTable'");
    if ($tableCheck->rowCount() > 0) {
        $maxScoreStmt = $pdo->prepare("
            SELECT MAX(diem) as max_diem FROM $resultTable 
            WHERE ma_quiz = ? AND ma_nguoi_dung = ?
        ");
        $maxScoreStmt->execute([$quizId, $userId]);
        $maxResult = $maxScoreStmt->fetch(PDO::FETCH_ASSOC);
        $maxPreviousScore = intval($maxResult['max_diem'] ?? 0);
    }
    
    // Calculate points to award
    $pointsEarned = 0;
    $maxQuizScore = 50; // Điểm tối đa cho mỗi quiz là 50
    
    if ($maxPreviousScore >= $maxQuizScore) {
        // Đã đạt điểm tối đa rồi
        $pointsEarned = 0;
    } else if ($currentScore > $maxPreviousScore) {
        // Chỉ cộng điểm nếu đạt điểm cao hơn lần trước
        $pointsEarned = $currentScore - $maxPreviousScore;
    }
    
    // Save quiz result
    if ($tableCheck->rowCount() > 0) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO $resultTable (ma_quiz, ma_nguoi_dung, diem, so_cau_dung, tong_so_cau, thoi_gian_lam_bai) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$quizId, $userId, $currentScore, $correctCount, $totalQuestions, $timeSpent]);
        } catch (PDOException $e) {
            error_log("Error saving quiz result: " . $e->getMessage());
            // Continue anyway
        }
    }
    
    // Update user points
    if ($pointsEarned > 0) {
        $updateStmt = $pdo->prepare("UPDATE nguoi_dung SET tong_diem = tong_diem + ? WHERE ma_nguoi_dung = ?");
        $updateStmt->execute([$pointsEarned, $userId]);
    }
    
    // Get new total points
    $pointsStmt = $pdo->prepare("SELECT tong_diem, cap_do FROM nguoi_dung WHERE ma_nguoi_dung = ?");
    $pointsStmt->execute([$userId]);
    $userData = $pointsStmt->fetch(PDO::FETCH_ASSOC);
    $newTotalPoints = $userData['tong_diem'];
    
    // Update session
    if (isset($_SESSION['user'])) {
        $_SESSION['user']['tong_diem'] = $newTotalPoints;
        $_SESSION['user']['cap_do'] = $userData['cap_do'];
    }
    
    // Check badges
    $newBadges = [];
    if (function_exists('checkBadgesAfterQuiz')) {
        try {
            $newBadges = checkBadgesAfterQuiz($pdo, $userId);
            
            // Refresh points if badges were awarded
            if (!empty($newBadges)) {
                $pointsStmt->execute([$userId]);
                $userData = $pointsStmt->fetch(PDO::FETCH_ASSOC);
                $newTotalPoints = $userData['tong_diem'];
                
                if (isset($_SESSION['user'])) {
                    $_SESSION['user']['tong_diem'] = $newTotalPoints;
                }
            }
        } catch (Exception $e) {
            error_log("Badge check error: " . $e->getMessage());
        }
    }
    
    $pdo->commit();
    
    // Create response message
    $responseMessage = 'Đã lưu kết quả quiz';
    if ($pointsEarned > 0) {
        $responseMessage = "Chúc mừng! Bạn được cộng $pointsEarned điểm";
    } else if ($maxPreviousScore >= $maxQuizScore) {
        $responseMessage = 'Bạn đã đạt điểm tối đa cho quiz này trước đó.';
    } else {
        $responseMessage = 'Kết quả được lưu. Để được cộng điểm, bạn cần đạt điểm cao hơn lần trước.';
    }
    
    sendJSON([
        'success' => true,
        'message' => $responseMessage,
        'points_earned' => $pointsEarned,
        'current_score' => $currentScore,
        'max_previous_score' => $maxPreviousScore,
        'new_total_points' => $newTotalPoints,
        'correct_count' => $correctCount,
        'total_questions' => $totalQuestions,
        'is_max_reached' => ($maxPreviousScore >= $maxQuizScore || $currentScore >= $maxQuizScore),
        'new_badges' => array_map(function($badge) {
            return [
                'name' => $badge['name_vi'] ?? $badge['name'] ?? 'Badge',
                'description' => $badge['description'] ?? '',
                'icon' => $badge['icon'] ?? 'fa-award',
                'color' => $badge['color'] ?? '#3b82f6',
                'bonus_points' => $badge['bonus_points'] ?? 0
            ];
        }, $newBadges ?? [])
    ]);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Quiz submit error: " . $e->getMessage());
    sendJSON([
        'success' => false,
        'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
