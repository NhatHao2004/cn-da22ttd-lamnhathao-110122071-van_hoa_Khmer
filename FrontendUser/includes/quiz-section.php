<?php
/**
 * Quiz Section Component - Modern Design
 * Hiển thị quiz trong các trang chi tiết
 */

if (!isset($quiz_type) || !isset($content_id)) {
    return;
}

$pdo = getDBConnection();
$quiz = null;
$questions = [];

// Lấy quiz theo loại - Sử dụng bảng quiz chung
try {
    // Xác định loại quiz và mã đối tượng
    $loaiQuiz = '';
    switch ($quiz_type) {
        case 'van_hoa':
            $loaiQuiz = 'van_hoa';
            break;
        case 'chua':
            $loaiQuiz = 'chua';
            break;
        case 'le_hoi':
            $loaiQuiz = 'le_hoi';
            break;
        case 'truyen':
            $loaiQuiz = 'truyen_dan_gian';
            break;
    }
    
    // Lấy quiz từ bảng quiz chung
    $stmt = $pdo->prepare("SELECT * FROM quiz WHERE loai_quiz = ? AND ma_doi_tuong = ? AND trang_thai = 'hoat_dong' LIMIT 1");
    $stmt->execute([$loaiQuiz, $content_id]);
    $quiz = $stmt->fetch();
    
    if ($quiz) {
        // Lấy câu hỏi từ bảng cau_hoi_quiz chung
        $qStmt = $pdo->prepare("SELECT * FROM cau_hoi_quiz WHERE ma_quiz = ? ORDER BY thu_tu");
        $qStmt->execute([$quiz['ma_quiz']]);
        $questions = $qStmt->fetchAll();
        
        // Lấy đáp án cho mỗi câu hỏi
        foreach ($questions as &$q) {
            $aStmt = $pdo->prepare("SELECT * FROM dap_an_quiz WHERE ma_cau_hoi = ? ORDER BY thu_tu");
            $aStmt->execute([$q['ma_cau_hoi']]);
            $q['dap_an'] = $aStmt->fetchAll();
        }
    }
} catch (PDOException $e) {
    error_log("Quiz section error: " . $e->getMessage());
    $quiz = null;
}

if (!$quiz || empty($questions)) {
    return;
}

$totalQuestions = count($questions);
?>

<!-- Modern Quiz Section -->
<div class="quiz-wrapper" id="quizSection">
    <!-- Quiz Start Card -->
    <div class="quiz-start-card" id="quizStartScreen">
        <div class="quiz-start-header">
            <div class="quiz-icon-wrapper">
                <i class="fas fa-brain"></i>
            </div>
            <h2 class="quiz-title"><?= htmlspecialchars($quiz['tieu_de']) ?></h2>
        </div>
        
        <div class="quiz-stats-row">
            <div class="quiz-stat-item">
                <div class="stat-icon"><i class="fas fa-question-circle"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $totalQuestions ?></span>
                    <span class="stat-label">Câu hỏi</span>
                </div>
            </div>
            <div class="quiz-stat-item">
                <div class="stat-icon time"><i class="fas fa-stopwatch"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= floor($quiz['thoi_gian'] / 60) ?></span>
                    <span class="stat-label">Phút</span>
                </div>
            </div>
            <div class="quiz-stat-item">
                <div class="stat-icon points"><i class="fas fa-trophy"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $quiz['diem_toi_da'] ?? 100 ?></span>
                    <span class="stat-label">Điểm</span>
                </div>
            </div>
        </div>
        
        <?php if (isLoggedIn()): ?>
        <button class="quiz-start-btn" onclick="startQuiz()">
            <span class="btn-text">Bắt đầu Quiz</span>
            <span class="btn-icon"><i class="fas fa-arrow-right"></i></span>
        </button>
        <?php else: ?>
        <div class="quiz-login-box">
            <i class="fas fa-lock"></i>
            <p>Vui lòng <a href="<?= BASE_URL ?>/login.php">đăng nhập</a> để làm quiz</p>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Quiz Questions Container -->
    <div class="quiz-play-container" id="quizQuestionsContainer" style="display: none;">
        <!-- Top Bar -->
        <div class="quiz-topbar">
            <div class="quiz-progress-info">
                <span class="progress-text">Câu <span id="currentQuestion">1</span> / <?= $totalQuestions ?></span>
                <div class="progress-bar-mini">
                    <div class="progress-fill-mini" id="quizProgressFill"></div>
                </div>
            </div>
            <div class="quiz-timer-box" id="quizTimer">
                <i class="fas fa-clock"></i>
                <span id="timerDisplay"><?= floor($quiz['thoi_gian'] / 60) ?>:00</span>
            </div>
        </div>
        
        <!-- Questions -->
        <?php foreach ($questions as $index => $question): ?>
        <div class="quiz-question-card" data-question="<?= $index + 1 ?>" style="<?= $index > 0 ? 'display: none;' : '' ?>">
            <div class="question-header">
                <span class="question-badge">Câu <?= $index + 1 ?></span>
            </div>
            <h3 class="question-content"><?= htmlspecialchars($question['noi_dung']) ?></h3>
            
            <div class="answers-grid">
                <?php 
                $answers = $question['dap_an'] ?? [];
                $colors = ['blue', 'green', 'orange', 'purple'];
                foreach ($answers as $aIndex => $answer): 
                ?>
                <div class="answer-card <?= $colors[$aIndex % 4] ?>" data-answer="<?= $answer['ma_dap_an'] ?>" data-correct="<?= $answer['la_dap_an_dung'] ?>">
                    <input type="radio" name="question_<?= $index + 1 ?>" value="<?= $answer['ma_dap_an'] ?>" style="display:none;">
                    <span class="answer-prefix"><?= chr(65 + $aIndex) ?></span>
                    <span class="answer-content"><?= htmlspecialchars($answer['noi_dung']) ?></span>
                    <span class="answer-check"><i class="fas fa-check"></i></span>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (!empty($question['giai_thich'])): ?>
            <div class="explanation-box" style="display: none;">
                <i class="fas fa-lightbulb"></i>
                <p><?= htmlspecialchars($question['giai_thich']) ?></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <!-- Navigation -->
        <div class="quiz-nav-bar">
            <button class="nav-btn prev" id="prevBtn" onclick="prevQuestion()" disabled>
                <i class="fas fa-chevron-left"></i>
                <span>Trước</span>
            </button>
            <div class="question-dots" id="questionDots">
                <?php for ($i = 1; $i <= $totalQuestions; $i++): ?>
                <span class="dot <?= $i === 1 ? 'active' : '' ?>" onclick="goToQuestion(<?= $i - 1 ?>)"><?= $i ?></span>
                <?php endfor; ?>
            </div>
            <button class="nav-btn next" id="nextBtn" onclick="nextQuestion()">
                <span>Tiếp</span>
                <i class="fas fa-chevron-right"></i>
            </button>
            <button class="nav-btn submit" id="submitBtn" onclick="submitQuiz()" style="display: none;">
                <span>Nộp bài</span>
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
    
    <!-- Quiz Result -->
    <div class="quiz-result-card" id="quizResult" style="display: none;">
        <div class="result-animation" id="resultAnimation">
            <div class="confetti"></div>
        </div>
        <div class="result-content">
            <div class="result-emoji" id="resultIcon">🎉</div>
            <h2 class="result-title" id="resultTitle">Xuất sắc!</h2>
            
            <div class="score-circle">
                <svg viewBox="0 0 100 100">
                    <circle class="score-bg" cx="50" cy="50" r="45"/>
                    <circle class="score-progress" id="scoreCircle" cx="50" cy="50" r="45"/>
                </svg>
                <div class="score-text">
                    <span class="score-num" id="scoreNumber">0</span>
                    <span class="score-total">/ <?= $totalQuestions ?></span>
                </div>
            </div>
            
            <div class="points-earned" id="resultPoints">
                <i class="fas fa-star"></i>
                <span>+<span id="pointsEarned">0</span> điểm</span>
            </div>
            
            <p class="result-message" id="resultMessage">Bạn đã hoàn thành bài quiz!</p>
            
            <div class="result-buttons">
                <button class="result-btn secondary" onclick="reviewQuiz()">
                    <i class="fas fa-eye"></i> Xem đáp án
                </button>
                <button class="result-btn primary" onclick="retryQuiz()">
                    <i class="fas fa-redo"></i> Làm lại
                </button>
                <a href="<?= BASE_URL ?>/profile.php" class="result-btn profile-btn">
                    <i class="fas fa-user"></i> Xem hồ sơ
                </a>
            </div>
        </div>
    </div>
</div>


<style>
/* ===== Modern Quiz Styles - Clean White Design ===== */
.quiz-wrapper {
    margin: 2rem 0;
}

/* Start Card */
.quiz-start-card {
    background: #ffffff;
    border-radius: 24px;
    padding: 3rem 2rem;
    text-align: center;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(59, 130, 246, 0.15);
    border: 3px solid #e2e8f0;
}

.quiz-start-header {
    position: relative;
    z-index: 1;
}

.quiz-icon-wrapper {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2.5rem;
    color: white;
    box-shadow: 0 15px 40px rgba(59, 130, 246, 0.4);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.quiz-title {
    font-size: 2rem;
    font-weight: 900;
    color: #000000;
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

/* Stats Row - Single Line */
.quiz-stats-row {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 2rem;
    margin: 2.5rem 0;
    position: relative;
    z-index: 1;
}

.quiz-stat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: #f8fafc;
    padding: 1rem 1.5rem;
    border-radius: 16px;
    border: 3px solid #e2e8f0;
    transition: all 0.3s;
}

.quiz-stat-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.15);
    border-color: #3b82f6;
}

.stat-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.125rem;
    flex-shrink: 0;
}

.stat-info {
    display: flex;
    align-items: baseline;
    gap: 0.25rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 900;
    color: #000000;
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: #64748b;
    font-weight: 600;
}

/* Start Button */
.quiz-start-btn {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    padding: 1.125rem 3rem;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: none;
    border-radius: 50px;
    font-size: 1.25rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 15px 40px rgba(59, 130, 246, 0.5);
}

.quiz-start-btn:hover {
    transform: translateY(-4px) scale(1.03);
    box-shadow: 0 20px 50px rgba(59, 130, 246, 0.6);
}

.quiz-start-btn .btn-icon {
    width: 36px;
    height: 36px;
    background: rgba(255,255,255,0.25);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    transition: transform 0.3s;
}

.quiz-start-btn:hover .btn-icon {
    transform: translateX(5px);
}

.quiz-login-box {
    position: relative;
    z-index: 1;
    background: #fef3c7;
    padding: 1.5rem 2rem;
    border-radius: 16px;
    color: #92400e;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    border: 2px solid #fde68a;
    font-weight: 600;
}

.quiz-login-box a {
    color: #3b82f6;
    font-weight: 700;
    text-decoration: underline;
}

/* Play Container */
.quiz-play-container {
    background: white;
    border-radius: 24px;
    padding: 2rem;
    box-shadow: 0 10px 40px rgba(59, 130, 246, 0.15);
    border: 3px solid #e2e8f0;
}

/* Top Bar */
.quiz-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 3px solid #f1f5f9;
}

.quiz-progress-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.progress-text {
    font-weight: 800;
    color: #000000;
    font-size: 1.05rem;
}

.progress-bar-mini {
    width: 220px;
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill-mini {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #2563eb);
    border-radius: 4px;
    transition: width 0.3s;
    width: 0%;
}

.quiz-timer-box {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1.5rem;
    background: #fef2f2;
    color: #dc2626;
    border-radius: 14px;
    font-weight: 800;
    font-size: 1.25rem;
    border: 2px solid #fecaca;
}

/* Question Card */
.quiz-question-card {
    animation: slideIn 0.4s ease;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

.question-header {
    margin-bottom: 1.25rem;
}

.question-badge {
    display: inline-block;
    padding: 0.625rem 1.25rem;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    border-radius: 50px;
    font-size: 0.9375rem;
    font-weight: 700;
}

.question-content {
    font-size: 1.375rem;
    font-weight: 800;
    color: #000000;
    line-height: 1.5;
    margin-bottom: 1.75rem;
}

/* Answers Grid */
.answers-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}

.answer-card {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.5rem;
    background: #ffffff;
    border: 3px solid #e2e8f0;
    border-radius: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.answer-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.2);
}

.answer-card.blue:hover { border-color: #3b82f6; background: #eff6ff; }
.answer-card.green:hover { border-color: #10b981; background: #ecfdf5; }
.answer-card.orange:hover { border-color: #f59e0b; background: #fffbeb; }
.answer-card.purple:hover { border-color: #8b5cf6; background: #f5f3ff; }

.answer-prefix {
    width: 46px;
    height: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-weight: 800;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.answer-card.blue .answer-prefix { background: #dbeafe; color: #2563eb; }
.answer-card.green .answer-prefix { background: #d1fae5; color: #059669; }
.answer-card.orange .answer-prefix { background: #fef3c7; color: #d97706; }
.answer-card.purple .answer-prefix { background: #ede9fe; color: #7c3aed; }

.answer-content {
    flex: 1;
    font-weight: 600;
    color: #1f2937;
    font-size: 1.05rem;
}

.answer-check {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
}

/* Selected State */
.answer-card.selected {
    border-width: 4px;
}

.answer-card.selected.blue { border-color: #3b82f6; background: #eff6ff; }
.answer-card.selected.green { border-color: #10b981; background: #ecfdf5; }
.answer-card.selected.orange { border-color: #f59e0b; background: #fffbeb; }
.answer-card.selected.purple { border-color: #8b5cf6; background: #f5f3ff; }

.answer-card.selected .answer-check {
    display: flex;
    background: #3b82f6;
}

/* Correct/Incorrect States */
.answer-card.correct {
    border-color: #10b981 !important;
    background: #d1fae5 !important;
    border-width: 4px !important;
}

.answer-card.correct .answer-prefix {
    background: #10b981;
    color: white;
}

.answer-card.correct .answer-check {
    display: flex;
    background: #10b981;
}

.answer-card.incorrect {
    border-color: #ef4444 !important;
    background: #fee2e2 !important;
    border-width: 4px !important;
}

.answer-card.incorrect .answer-prefix {
    background: #ef4444;
    color: white;
}

.answer-card.incorrect .answer-check {
    display: flex;
    background: #ef4444;
}

.answer-card.incorrect .answer-check i::before {
    content: "\f00d";
}

/* Explanation */
.explanation-box {
    margin-top: 1.75rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-radius: 18px;
    display: flex;
    align-items: flex-start;
    gap: 1.25rem;
    border: 2px solid #fde68a;
}

.explanation-box i {
    color: #f59e0b;
    font-size: 1.5rem;
    margin-top: 2px;
}

.explanation-box p {
    margin: 0;
    color: #92400e;
    line-height: 1.6;
    font-weight: 600;
}

/* Navigation Bar */
.quiz-nav-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 3px solid #f1f5f9;
}

.nav-btn {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 1rem 1.75rem;
    border-radius: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    font-size: 1rem;
}

.nav-btn.prev {
    background: #f1f5f9;
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.nav-btn.prev:hover:not(:disabled) {
    background: #e2e8f0;
    border-color: #cbd5e1;
}

.nav-btn.prev:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.nav-btn.next, .nav-btn.submit {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
}

.nav-btn.next:hover, .nav-btn.submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.5);
}

/* Question Dots */
.question-dots {
    display: flex;
    gap: 0.625rem;
}

.question-dots .dot {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
    border: 2px solid #e2e8f0;
}

.question-dots .dot:hover {
    background: #e2e8f0;
    border-color: #cbd5e1;
}

.question-dots .dot.active {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    border-color: #3b82f6;
}

.question-dots .dot.answered {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

/* Back to Result Button */
.back-to-result-wrapper {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 3px solid #f1f5f9;
}

.back-to-result-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.75rem;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    border: none;
    border-radius: 14px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
}

.back-to-result-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.5);
}

.back-to-result-btn i {
    font-size: 1.125rem;
}
</style>


<style>
/* Result Card */
.quiz-result-card {
    background: white;
    border-radius: 24px;
    padding: 3rem 2rem;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.result-content {
    position: relative;
    z-index: 1;
}

.result-emoji {
    font-size: 5rem;
    margin-bottom: 1rem;
    animation: bounce 0.6s ease;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

.result-title {
    font-size: 2rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 2rem;
}

/* Score Circle */
.score-circle {
    width: 180px;
    height: 180px;
    margin: 0 auto 1.5rem;
    position: relative;
}

.score-circle svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.score-bg {
    fill: none;
    stroke: #e2e8f0;
    stroke-width: 8;
}

.score-progress {
    fill: none;
    stroke: url(#scoreGradient);
    stroke-width: 8;
    stroke-linecap: round;
    stroke-dasharray: 283;
    stroke-dashoffset: 283;
    transition: stroke-dashoffset 1s ease;
}

.score-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.score-text {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 0.25rem;
}

.score-num {
    font-size: 2.5rem;
    font-weight: 800;
    color: #667eea;
    line-height: 1;
}

.score-total {
    font-size: 1.5rem;
    font-weight: 700;
    color: #94a3b8;
}

/* Points Earned */
.points-earned {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.75rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 50px;
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: 1rem;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.points-earned i {
    color: #fbbf24;
}

.result-message {
    color: #64748b;
    font-size: 1rem;
    margin-bottom: 2rem;
}

/* Result Buttons */
.result-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.result-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.75rem;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
}

.result-btn.secondary {
    background: #f1f5f9;
    color: #475569;
}

.result-btn.secondary:hover {
    background: #e2e8f0;
}

.result-btn.primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.result-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.result-btn.profile-btn {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    text-decoration: none;
}

.result-btn.profile-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
    .quiz-stats-row {
        flex-direction: column;
        gap: 1rem;
    }
    
    .quiz-stat-item {
        justify-content: center;
    }
    
    .answers-grid {
        grid-template-columns: 1fr;
    }
    
    .quiz-nav-bar {
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .question-dots {
        order: 3;
        width: 100%;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .progress-bar-mini {
        width: 150px;
    }
}

/* ===== Badge Notification Styles ===== */
.badge-notification-container {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.badge-notification-container.show {
    opacity: 1;
}

.badge-notification-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
}

.badge-notification-modal {
    position: relative;
    z-index: 1;
    background: white;
    border-radius: 24px;
    padding: 2.5rem;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: badgeSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes badgeSlideIn {
    from {
        transform: translateY(-50px) scale(0.9);
        opacity: 0;
    }
    to {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

.badge-notification-header {
    text-align: center;
    margin-bottom: 2rem;
}

.badge-notification-header i {
    font-size: 3rem;
    color: #f59e0b;
    margin-bottom: 1rem;
    animation: badgeBounce 0.6s ease infinite;
}

@keyframes badgeBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.badge-notification-header h3 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #000000;
    margin: 0;
}

.badge-notification-body {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.badge-notification-item {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8fafc, #ffffff);
    border-radius: 16px;
    border: 2px solid #e2e8f0;
    transition: all 0.3s ease;
}

.badge-notification-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border-color: #cbd5e1;
}

.badge-notification-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.badge-notification-info {
    flex: 1;
}

.badge-notification-info h4 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #000000;
    margin: 0 0 0.5rem 0;
}

.badge-notification-info p {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0 0 0.75rem 0;
    line-height: 1.5;
}

.badge-bonus {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.875rem;
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    color: white;
    border-radius: 50px;
    font-size: 0.8125rem;
    font-weight: 700;
}

.badge-notification-close {
    width: 100%;
    margin-top: 1.5rem;
    padding: 1rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.badge-notification-close:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
}

@media (max-width: 640px) {
    .badge-notification-modal {
        padding: 2rem 1.5rem;
    }
    
    .badge-notification-item {
        flex-direction: column;
        text-align: center;
    }
    
    .badge-notification-icon {
        width: 60px;
        height: 60px;
        font-size: 1.75rem;
    }
}
</style>

<!-- SVG Gradient Definition -->
<svg width="0" height="0">
    <defs>
        <linearGradient id="scoreGradient" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" style="stop-color:#667eea"/>
            <stop offset="100%" style="stop-color:#764ba2"/>
        </linearGradient>
    </defs>
</svg>

<script>
// Quiz Variables
let currentQuestionIndex = 0;
let totalQuestions = <?= $totalQuestions ?>;
let timeLimit = <?= $quiz['thoi_gian'] ?>;
let timerInterval = null;
let userAnswers = {};
let quizStarted = false;

function startQuiz() {
    document.getElementById('quizStartScreen').style.display = 'none';
    document.getElementById('quizQuestionsContainer').style.display = 'block';
    quizStarted = true;
    startTimer();
    updateProgress();
}

function startTimer() {
    let timeLeft = timeLimit;
    updateTimerDisplay(timeLeft);
    
    timerInterval = setInterval(() => {
        timeLeft--;
        updateTimerDisplay(timeLeft);
        
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            submitQuiz();
        }
    }, 1000);
}

function updateTimerDisplay(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    document.getElementById('timerDisplay').textContent = 
        `${mins}:${secs.toString().padStart(2, '0')}`;
    
    const timerBox = document.getElementById('quizTimer');
    if (seconds <= 60) {
        timerBox.style.background = '#fef2f2';
        timerBox.style.color = '#dc2626';
        timerBox.style.animation = 'pulse 0.5s infinite';
    }
}

function updateProgress() {
    const progress = ((currentQuestionIndex + 1) / totalQuestions) * 100;
    document.getElementById('quizProgressFill').style.width = progress + '%';
    document.getElementById('currentQuestion').textContent = currentQuestionIndex + 1;
    
    // Update dots
    document.querySelectorAll('.question-dots .dot').forEach((dot, i) => {
        dot.classList.remove('active');
        if (i === currentQuestionIndex) dot.classList.add('active');
    });
    
    // Update navigation buttons
    document.getElementById('prevBtn').disabled = currentQuestionIndex === 0;
    
    if (currentQuestionIndex === totalQuestions - 1) {
        document.getElementById('nextBtn').style.display = 'none';
        document.getElementById('submitBtn').style.display = 'flex';
    } else {
        document.getElementById('nextBtn').style.display = 'flex';
        document.getElementById('submitBtn').style.display = 'none';
    }
}

function showQuestion(index) {
    document.querySelectorAll('.quiz-question-card').forEach((q, i) => {
        q.style.display = i === index ? 'block' : 'none';
    });
    currentQuestionIndex = index;
    updateProgress();
}

function goToQuestion(index) {
    showQuestion(index);
}

function nextQuestion() {
    if (currentQuestionIndex < totalQuestions - 1) {
        showQuestion(currentQuestionIndex + 1);
    }
}

function prevQuestion() {
    if (currentQuestionIndex > 0) {
        showQuestion(currentQuestionIndex - 1);
    }
}

// Handle answer selection
document.querySelectorAll('.answer-card').forEach(card => {
    card.addEventListener('click', function() {
        const questionCard = this.closest('.quiz-question-card');
        const questionNum = questionCard.dataset.question;
        
        // Remove selected from siblings
        questionCard.querySelectorAll('.answer-card').forEach(c => c.classList.remove('selected'));
        
        // Add selected to this
        this.classList.add('selected');
        this.querySelector('input').checked = true;
        
        // Store answer
        userAnswers[questionNum] = {
            selected: this.dataset.answer,
            correct: this.dataset.correct === '1'
        };
        
        // Mark dot as answered
        document.querySelectorAll('.question-dots .dot')[questionNum - 1].classList.add('answered');
    });
});

async function submitQuiz() {
    clearInterval(timerInterval);
    
    // Calculate score
    let correctCount = 0;
    Object.values(userAnswers).forEach(answer => {
        if (answer.correct) correctCount++;
    });
    
    const timeSpent = timeLimit - (timerInterval ? 0 : timeLimit);
    
    // Send to server
    try {
        const response = await fetch('<?= BASE_URL ?>/api/quiz-submit.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                quiz_type: '<?= $quiz_type ?>',
                quiz_id: <?= $quiz['ma_quiz'] ?>,
                correct_count: correctCount,
                total_questions: totalQuestions,
                time_spent: timeSpent
            })
        });
        const data = await response.json();
        if (data.success) {
            document.getElementById('pointsEarned').textContent = data.points_earned;
            
            // Show notification about points update
            if (data.points_earned > 0) {
                // Update message to inform user
                const resultMsg = document.getElementById('resultMessage');
                resultMsg.innerHTML = `Bạn đã hoàn thành bài quiz!<br><small style="color: #10b981; font-weight: 600;">✓ Điểm đã được cập nhật vào hồ sơ của bạn</small>`;
            }
            
            // Show new badges if any
            if (data.new_badges && data.new_badges.length > 0) {
                showNewBadges(data.new_badges);
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
    
    // Show results
    document.getElementById('quizQuestionsContainer').style.display = 'none';
    document.getElementById('quizResult').style.display = 'block';
    
    document.getElementById('scoreNumber').textContent = correctCount;
    
    // Animate score circle
    const percentage = (correctCount / totalQuestions) * 100;
    const circumference = 283;
    const offset = circumference - (percentage / 100) * circumference;
    document.getElementById('scoreCircle').style.strokeDashoffset = offset;
    
    // Set result message
    let icon, title, message;
    const pointsEarned = correctCount * 10;
    
    if (percentage >= 80) {
        icon = '🎉'; title = 'Xuất sắc!'; message = `Bạn đã nắm vững kiến thức!`;
    } else if (percentage >= 60) {
        icon = '👍'; title = 'Tốt lắm!'; message = `Bạn đã hiểu khá tốt!`;
    } else if (percentage >= 40) {
        icon = '📚'; title = 'Cần cố gắng!'; message = `Hãy đọc lại bài viết nhé!`;
    } else {
        icon = '💪'; title = 'Đừng nản!'; message = `Hãy tìm hiểu thêm và thử lại!`;
    }
    
    document.getElementById('resultIcon').textContent = icon;
    document.getElementById('resultTitle').textContent = title;
    document.getElementById('resultMessage').textContent = message;
    if (!document.getElementById('pointsEarned').textContent || document.getElementById('pointsEarned').textContent === '0') {
        document.getElementById('pointsEarned').textContent = pointsEarned;
    }
}

function reviewQuiz() {
    // Reset to first question
    currentQuestionIndex = 0;
    
    // Hide result, show questions
    document.getElementById('quizResult').style.display = 'none';
    document.getElementById('quizQuestionsContainer').style.display = 'block';
    
    // Show correct/incorrect states for all questions
    document.querySelectorAll('.quiz-question-card').forEach((questionCard, qIndex) => {
        const questionNum = qIndex + 1;
        
        questionCard.querySelectorAll('.answer-card').forEach(card => {
            const isCorrect = card.dataset.correct === '1';
            const wasSelected = userAnswers[questionNum] && userAnswers[questionNum].selected === card.dataset.answer;
            
            if (isCorrect) {
                card.classList.add('correct');
            } else if (wasSelected) {
                card.classList.add('incorrect');
            }
            
            // Disable clicking
            card.style.pointerEvents = 'none';
        });
        
        // Show explanation if exists
        const explanation = questionCard.querySelector('.explanation-box');
        if (explanation) {
            explanation.style.display = 'flex';
        }
    });
    
    // Show only first question
    showQuestion(0);
    
    // Update navigation for review mode
    document.getElementById('submitBtn').style.display = 'none';
    document.getElementById('nextBtn').style.display = 'flex';
    document.getElementById('nextBtn').textContent = 'Tiếp';
    
    // Hide timer
    document.getElementById('quizTimer').style.display = 'none';
    
    // Update progress text
    document.querySelector('.progress-text').innerHTML = 'Xem lại - Câu <span id="currentQuestion">1</span> / ' + totalQuestions;
}

function retryQuiz() {
    // Reset all states
    currentQuestionIndex = 0;
    userAnswers = {};
    
    // Remove all answer states
    document.querySelectorAll('.answer-card').forEach(card => {
        card.classList.remove('selected', 'correct', 'incorrect');
        card.style.pointerEvents = 'auto';
        card.querySelector('input').checked = false;
    });
    
    // Hide all explanations
    document.querySelectorAll('.explanation-box').forEach(exp => {
        exp.style.display = 'none';
    });
    
    // Reset dots
    document.querySelectorAll('.question-dots .dot').forEach(dot => {
        dot.classList.remove('answered', 'active');
    });
    document.querySelectorAll('.question-dots .dot')[0].classList.add('active');
    
    // Hide result, show start screen
    document.getElementById('quizResult').style.display = 'none';
    document.getElementById('quizStartScreen').style.display = 'block';
    
    // Reset timer display
    document.getElementById('quizTimer').style.display = 'flex';
    document.querySelector('.progress-text').innerHTML = 'Câu <span id="currentQuestion">1</span> / ' + totalQuestions;
}

// Show new badges earned
function showNewBadges(badges) {
    if (!badges || badges.length === 0) return;
    
    // Create badge notification container
    const badgeContainer = document.createElement('div');
    badgeContainer.className = 'badge-notification-container';
    badgeContainer.innerHTML = `
        <div class="badge-notification-overlay"></div>
        <div class="badge-notification-modal">
            <div class="badge-notification-header">
                <i class="fas fa-trophy"></i>
                <h3>Chúc mừng! Bạn đạt huy hiệu mới!</h3>
            </div>
            <div class="badge-notification-body">
                ${badges.map(badge => `
                    <div class="badge-notification-item">
                        <div class="badge-notification-icon" style="background: ${badge.color};">
                            <i class="${badge.icon}"></i>
                        </div>
                        <div class="badge-notification-info">
                            <h4>${badge.name}</h4>
                            <p>${badge.description}</p>
                            <span class="badge-bonus">+${badge.bonus_points} điểm thưởng</span>
                        </div>
                    </div>
                `).join('')}
            </div>
            <button class="badge-notification-close" onclick="closeBadgeNotification()">
                <i class="fas fa-times"></i> Đóng
            </button>
        </div>
    `;
    
    document.body.appendChild(badgeContainer);
    
    // Animate in
    setTimeout(() => {
        badgeContainer.classList.add('show');
    }, 100);
}

function closeBadgeNotification() {
    const container = document.querySelector('.badge-notification-container');
    if (container) {
        container.classList.remove('show');
        setTimeout(() => {
            container.remove();
        }, 300);
    }
}
</script>
