/**
 * Quiz Admin JavaScript
 */

let currentSoCauHoi = 10;

// Toggle trạng thái
function toggleStatus(quizId, currentStatus) {
    const newStatus = currentStatus === 'hoat_dong' ? 'tam_dung' : 'hoat_dong';
    const statusText = newStatus === 'hoat_dong' ? 'Hoạt động' : 'Tạm dừng';
    
    if (confirm(`Chuyển trạng thái thành: ${statusText}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="toggle_status">
            <input type="hidden" name="ma_quiz" value="${quizId}">
            <input type="hidden" name="new_status" value="${newStatus}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Sửa quiz
function editQuiz(quiz) {
    document.getElementById('edit_quiz_id').value = quiz.ma_quiz;
    document.getElementById('edit_quiz_tieu_de').value = quiz.tieu_de;
    document.getElementById('edit_quiz_mo_ta').value = quiz.mo_ta || '';
    document.getElementById('edit_quiz_thoi_gian').value = quiz.thoi_gian;
    $('#editQuizModal').modal('show');
}

// Xóa quiz
function deleteQuiz(id) {
    if (confirm('Bạn có chắc muốn xóa quiz này? Tất cả câu hỏi sẽ bị xóa!')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_quiz">
            <input type="hidden" name="ma_quiz" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Mở modal thêm câu hỏi
function openAddQuestionsModal(quizId, quizTitle) {
    document.getElementById('quiz_id_input').value = quizId;
    document.getElementById('quiz_title_display').textContent = quizTitle;
    updateSoCauHoi(10);
    document.querySelectorAll('.question-input, .answer-input').forEach(input => input.value = '');
    $('#addQuestionsModal').modal('show');
}

// Thay đổi số câu hỏi
function changeSoCauHoi(delta) {
    const input = document.getElementById('so_cau_hoi_display');
    let newValue = parseInt(input.value) + delta;
    if (newValue < 1) newValue = 1;
    if (newValue > 20) newValue = 20;
    input.value = newValue;
    updateSoCauHoi(newValue);
}

function updateSoCauHoi(value) {
    value = parseInt(value);
    if (value < 1) value = 1;
    if (value > 20) value = 20;
    
    currentSoCauHoi = value;
    document.getElementById('so_cau_hoi_display').value = value;
    document.getElementById('so_cau_hoi_input').value = value;
    document.getElementById('submit_count').textContent = value;
    
    document.querySelectorAll('.question-card, .question-card-modern').forEach((card, index) => {
        const questionNum = index + 1;
        if (questionNum <= value) {
            card.style.display = 'block';
            card.querySelectorAll('.question-input, .answer-input').forEach(input => input.required = true);
        } else {
            card.style.display = 'none';
            card.querySelectorAll('.question-input, .answer-input').forEach(input => input.required = false);
        }
    });
}

// Xem câu hỏi
async function viewQuestions(quizId, quizTitle) {
    document.getElementById('view_quiz_title').textContent = quizTitle;
    const container = document.getElementById('questions_list_container');
    container.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2">Đang tải...</p></div>';
    
    $('#viewQuestionsModal').modal('show');
    
    try {
        const response = await fetch(`quiz-questions-api.php?quiz_id=${quizId}`);
        const data = await response.json();
        
        if (data.success && data.questions.length > 0) {
            let html = '';
            data.questions.forEach((q) => {
                const answers = data.answers[q.ma_cau_hoi] || [];
                const questionId = `q_${q.ma_cau_hoi}`;
                
                // Lưu dữ liệu vào window object
                window[`question_${q.ma_cau_hoi}`] = q;
                window[`answers_${q.ma_cau_hoi}`] = answers;
                
                html += `
                    <div class="question-card-modern" style="margin-bottom: 1.5rem;">
                        <div class="question-header-modern">
                            <div class="question-number-badge">
                                <i class="fas fa-question-circle"></i>
                                <span>Câu ${q.thu_tu}</span>
                            </div>
                            <div style="margin-left: auto; display: flex; gap: 0.5rem;">
                                <button class="btn btn-sm btn-warning" style="border-radius: 8px;" onclick="editQuestion(window.question_${q.ma_cau_hoi}, window.answers_${q.ma_cau_hoi})">
                                    <i class="fas fa-edit"></i> Sửa
                                </button>
                                <button class="btn btn-sm btn-danger" style="border-radius: 8px;" onclick="deleteQuestion(${q.ma_cau_hoi})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="question-body-modern">
                            <p style="font-weight: 600; font-size: 1.05rem; margin-bottom: 1rem; color: #1e293b;">${q.noi_dung}</p>
                            <div class="answers-grid">
                                ${answers.map((a, idx) => `
                                    <div class="answer-item-modern ${a.la_dap_an_dung ? 'border-success' : ''}" style="${a.la_dap_an_dung ? 'background: #d1fae5; border-color: #10b981;' : ''}">
                                        <div class="answer-radio-wrapper">
                                            <span class="radio-label-modern" style="${a.la_dap_an_dung ? 'background: #10b981;' : 'background: #94a3b8;'}">${String.fromCharCode(65 + idx)}</span>
                                        </div>
                                        <span style="flex: 1; font-weight: ${a.la_dap_an_dung ? '600' : '400'};">${a.noi_dung}</span>
                                        ${a.la_dap_an_dung ? '<i class="fas fa-check-circle text-success"></i>' : ''}
                                    </div>
                                `).join('')}
                            </div>
                            ${q.giai_thich ? `
                                <div style="margin-top: 1rem; padding: 1.25rem; background: #f8fafc; border: 2px solid #e2e8f0; border-left: 4px solid #6366f1; border-radius: 12px;">
                                    <div style="display: flex; align-items: start; gap: 0.75rem;">
                                        <i class="fas fa-lightbulb" style="color: #6366f1; font-size: 1.25rem; margin-top: 0.125rem; flex-shrink: 0;"></i>
                                        <div style="flex: 1;">
                                            <div style="color: #64748b; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Giải thích</div>
                                            <div style="color: #1e293b; font-size: 1rem; line-height: 1.6;">${q.giai_thich}</div>
                                        </div>
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="info-banner-modern"><i class="fas fa-info-circle"></i><div>Chưa có câu hỏi nào</div></div>';
        }
    } catch (error) {
        container.innerHTML = '<div class="alert-modern alert-error"><i class="fas fa-exclamation-circle"></i> Lỗi tải dữ liệu</div>';
    }
}

// Sửa câu hỏi
function editQuestion(question, answers) {
    console.log('=== EDIT QUESTION DEBUG ===');
    console.log('Question:', question);
    console.log('Answers:', answers);
    console.log('Answers length:', answers ? answers.length : 'undefined');
    
    if (!question) {
        alert('Lỗi: Không có dữ liệu câu hỏi!');
        return;
    }
    
    if (!answers || answers.length === 0) {
        alert('Lỗi: Không có dữ liệu đáp án!');
        return;
    }
    
    // Reset form
    document.getElementById('edit_ma_cau_hoi').value = question.ma_cau_hoi;
    document.getElementById('edit_noi_dung').value = question.noi_dung;
    document.getElementById('edit_giai_thich').value = question.giai_thich || '';
    
    console.log('Set question content:', question.noi_dung);
    
    // Reset tất cả radio buttons trước
    for (let i = 1; i <= 4; i++) {
        const radio = document.getElementById(`edit_dung_${i}`);
        if (radio) {
            radio.checked = false;
        } else {
            console.error(`Radio button edit_dung_${i} not found!`);
        }
    }
    
    // Load đáp án
    answers.forEach((answer, index) => {
        const num = index + 1;
        const maDapAnInput = document.getElementById(`edit_ma_dap_an_${num}`);
        const dapAnInput = document.getElementById(`edit_dap_an_${num}`);
        const radioInput = document.getElementById(`edit_dung_${num}`);
        
        console.log(`Answer ${num}:`, answer);
        console.log(`  - ma_dap_an input exists:`, !!maDapAnInput);
        console.log(`  - dap_an input exists:`, !!dapAnInput);
        console.log(`  - radio input exists:`, !!radioInput);
        
        if (maDapAnInput) {
            maDapAnInput.value = answer.ma_dap_an;
            console.log(`  - Set ma_dap_an: ${answer.ma_dap_an}`);
        }
        
        if (dapAnInput) {
            dapAnInput.value = answer.noi_dung;
            console.log(`  - Set noi_dung: ${answer.noi_dung}`);
        }
        
        // Đánh dấu đáp án đúng
        if (radioInput && (answer.la_dap_an_dung == 1 || answer.la_dap_an_dung === true || answer.la_dap_an_dung === '1')) {
            radioInput.checked = true;
            console.log(`  - Marked as correct answer`);
        }
    });
    
    console.log('=== END DEBUG ===');
    
    $('#viewQuestionsModal').modal('hide');
    setTimeout(() => {
        $('#editQuestionModal').modal('show');
    }, 300);
}

// Xóa câu hỏi
function deleteQuestion(id) {
    if (confirm('Bạn có chắc muốn xóa câu hỏi này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete_question">
            <input type="hidden" name="ma_cau_hoi" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

// Validate form
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('questionsForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const soCauHoi = parseInt(document.getElementById('so_cau_hoi_input').value);
            let hasContent = false;
            let errorMessages = [];
            
            for (let i = 1; i <= soCauHoi; i++) {
                const questionInput = document.querySelector(`[name="cau_hoi_${i}"]`);
                const question = questionInput ? questionInput.value.trim() : '';
                
                if (question) {
                    hasContent = true;
                    for (let j = 1; j <= 4; j++) {
                        const answerInput = document.querySelector(`[name="dap_an_${i}_${j}"]`);
                        const answer = answerInput ? answerInput.value.trim() : '';
                        if (!answer) {
                            errorMessages.push(`Câu ${i}: Thiếu đáp án ${String.fromCharCode(64 + j)}`);
                        }
                    }
                }
            }
            
            if (!hasContent) {
                alert('Vui lòng nhập ít nhất 1 câu hỏi!');
                e.preventDefault();
                return false;
            }
            
            if (errorMessages.length > 0) {
                alert('Vui lòng kiểm tra:\n\n' + errorMessages.join('\n'));
                e.preventDefault();
                return false;
            }
            
            return confirm(`Bạn có chắc muốn thêm ${soCauHoi} câu hỏi này?`);
        });
    }
    
    updateSoCauHoi(10);
});
