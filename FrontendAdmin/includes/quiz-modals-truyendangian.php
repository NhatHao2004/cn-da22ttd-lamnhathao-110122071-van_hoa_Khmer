<!-- Modal Tạo Quiz -->
<div class="modal fade" id="createQuizModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modern-modal">
            <form method="POST">
                <div class="modal-header-modern gradient-primary">
                    <div class="modal-icon-wrapper">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div>
                        <h5 class="modal-title-modern">Tạo Quiz Mới</h5>
                        <p class="modal-subtitle">Tạo bài kiểm tra kiến thức mới</p>
                    </div>
                    <button type="button" class="close-modern" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body-modern">
                    <input type="hidden" name="action" value="create_quiz">
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-book-reader"></i> Chọn Truyện <span class="text-danger">*</span>
                        </label>
                        <select name="ma_truyen" class="form-control-modern" required>
                            <option value="">-- Chọn truyện dân gian --</option>
                            <?php foreach ($truyenList as $truyen): ?>
                            <option value="<?= $truyen['ma_truyen'] ?>"><?= htmlspecialchars($truyen['tieu_de']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-heading"></i> Tiêu Đề Quiz <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="tieu_de" class="form-control-modern" placeholder="Nhập tiêu đề quiz..." required>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-align-left"></i> Mô Tả
                        </label>
                        <textarea name="mo_ta" class="form-control-modern" rows="3" placeholder="Nhập mô tả ngắn về quiz..."></textarea>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-clock"></i> Thời Gian (giây)
                        </label>
                        <input type="number" name="thoi_gian" class="form-control-modern" value="600" min="60" placeholder="600">
                    </div>
                </div>
                <div class="modal-footer-modern">
                    <button type="button" class="btn-modern btn-secondary-modern" data-dismiss="modal">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="fas fa-save"></i> Tạo Quiz
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa Quiz -->
<div class="modal fade" id="editQuizModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modern-modal">
            <form method="POST">
                <div class="modal-header-modern gradient-warning">
                    <div class="modal-icon-wrapper">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div>
                        <h5 class="modal-title-modern">Sửa Quiz</h5>
                        <p class="modal-subtitle">Chỉnh sửa thông tin quiz</p>
                    </div>
                    <button type="button" class="close-modern" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body-modern">
                    <input type="hidden" name="action" value="edit_quiz">
                    <input type="hidden" name="ma_quiz" id="edit_quiz_id">
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-heading"></i> Tiêu Đề <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="tieu_de" id="edit_quiz_tieu_de" class="form-control-modern" required>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-align-left"></i> Mô Tả
                        </label>
                        <textarea name="mo_ta" id="edit_quiz_mo_ta" class="form-control-modern" rows="3"></textarea>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-clock"></i> Thời Gian (giây)
                        </label>
                        <input type="number" name="thoi_gian" id="edit_quiz_thoi_gian" class="form-control-modern" min="60">
                    </div>
                </div>
                <div class="modal-footer-modern">
                    <button type="button" class="btn-modern btn-secondary-modern" data-dismiss="modal">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn-modern btn-warning-modern">
                        <i class="fas fa-save"></i> Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Xem Câu Hỏi -->
<div class="modal fade" id="viewQuestionsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content modern-modal">
            <div class="modal-header-modern gradient-info">
                <div class="modal-icon-wrapper">
                    <i class="fas fa-list"></i>
                </div>
                <div>
                    <h5 class="modal-title-modern">Xem & Sửa Câu Hỏi</h5>
                    <p class="modal-subtitle">Quản lý danh sách câu hỏi</p>
                </div>
                <button type="button" class="close-modern" data-dismiss="modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body-modern" style="max-height: 70vh; overflow-y: auto;">
                <div class="info-banner-modern">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>Quiz:</strong> <span id="view_quiz_title"></span>
                    </div>
                </div>
                <div id="questions_list_container"></div>
            </div>
            <div class="modal-footer-modern">
                <button type="button" class="btn-modern btn-secondary-modern" data-dismiss="modal">
                    <i class="fas fa-times"></i> Đóng
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Sửa Câu Hỏi -->
<div class="modal fade" id="editQuestionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modern-modal">
            <form method="POST">
                <div class="modal-header-modern gradient-warning">
                    <div class="modal-icon-wrapper">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h5 class="modal-title-modern">Sửa Câu Hỏi</h5>
                        <p class="modal-subtitle">Chỉnh sửa nội dung câu hỏi và đáp án</p>
                    </div>
                    <button type="button" class="close-modern" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body-modern">
                    <input type="hidden" name="action" value="edit_question">
                    <input type="hidden" name="ma_cau_hoi" id="edit_ma_cau_hoi">
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-question-circle"></i> Nội dung câu hỏi <span class="text-danger">*</span>
                        </label>
                        <textarea name="noi_dung" id="edit_noi_dung" class="form-control-modern" rows="3" placeholder="Nhập nội dung câu hỏi..." required></textarea>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-list-ul"></i> Đáp án (Chọn đáp án đúng)
                        </label>
                        <div class="answers-grid">
                            <?php for ($j = 1; $j <= 4; $j++): ?>
                            <div class="answer-item-modern">
                                <div class="answer-radio-wrapper">
                                    <input type="radio" name="dap_an_dung" value="<?= $j ?>" id="edit_dung_<?= $j ?>" class="radio-modern">
                                    <label for="edit_dung_<?= $j ?>" class="radio-label-modern"><?= chr(64 + $j) ?></label>
                                </div>
                                <input type="text" name="dap_an_<?= $j ?>" id="edit_dap_an_<?= $j ?>" class="form-control-modern" placeholder="Đáp án <?= chr(64 + $j) ?>" required>
                                <input type="hidden" name="ma_dap_an_<?= $j ?>" id="edit_ma_dap_an_<?= $j ?>">
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-lightbulb"></i> Giải thích
                        </label>
                        <textarea name="giai_thich" id="edit_giai_thich" class="form-control-modern" rows="2" placeholder="Giải thích đáp án đúng..."></textarea>
                    </div>
                </div>
                <div class="modal-footer-modern">
                    <button type="button" class="btn-modern btn-secondary-modern" data-dismiss="modal">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn-modern btn-warning-modern">
                        <i class="fas fa-save"></i> Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Thêm Câu Hỏi - Compact Version -->
<div class="modal fade" id="addQuestionsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content modern-modal">
            <form method="POST" id="questionsForm">
                <div class="modal-header-modern gradient-success">
                    <div class="modal-icon-wrapper">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div>
                        <h5 class="modal-title-modern">Thêm Câu Hỏi</h5>
                        <p class="modal-subtitle">Tạo câu hỏi mới cho quiz</p>
                    </div>
                    <button type="button" class="close-modern" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body-modern" style="max-height: 70vh; overflow-y: auto;">
                    <input type="hidden" name="action" value="add_questions">
                    <input type="hidden" name="ma_quiz" id="quiz_id_input">
                    <input type="hidden" name="so_cau_hoi" id="so_cau_hoi_input" value="10">
                    
                    <div class="info-banner-modern">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Quiz:</strong> <span id="quiz_title_display"></span>
                        </div>
                    </div>
                    
                    <div class="counter-card-modern">
                        <div class="counter-header">
                            <i class="fas fa-list-ol"></i>
                            <span>Số câu hỏi</span>
                        </div>
                        <div class="counter-controls">
                            <button type="button" class="counter-btn" onclick="changeSoCauHoi(-1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" id="so_cau_hoi_display" class="counter-input" 
                                   value="10" min="1" max="20" onchange="updateSoCauHoi(this.value)">
                            <button type="button" class="counter-btn" onclick="changeSoCauHoi(1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <small class="counter-hint">Từ 1 đến 20 câu hỏi</small>
                    </div>
                    
                    <div id="questions_container">
                    <?php for ($i = 1; $i <= 20; $i++): ?>
                    <div class="question-card-modern" data-question="<?= $i ?>" style="<?= $i > 10 ? 'display: none;' : '' ?>">
                        <div class="question-header-modern">
                            <div class="question-number-badge">
                                <i class="fas fa-question-circle"></i>
                                <span>Câu <?= $i ?></span>
                            </div>
                        </div>
                        <div class="question-body-modern">
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="fas fa-pen"></i> Nội dung câu hỏi
                                </label>
                                <textarea name="cau_hoi_<?= $i ?>" class="form-control-modern question-input" rows="2" placeholder="Nhập câu hỏi..."></textarea>
                            </div>
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="fas fa-list-ul"></i> Đáp án (Chọn đáp án đúng)
                                </label>
                                <div class="answers-grid">
                                    <?php for ($j = 1; $j <= 4; $j++): ?>
                                    <div class="answer-item-modern">
                                        <div class="answer-radio-wrapper">
                                            <input type="radio" name="dap_an_dung_<?= $i ?>" value="<?= $j ?>" id="dap_an_<?= $i ?>_<?= $j ?>" class="radio-modern" <?= $j === 1 ? 'checked' : '' ?>>
                                            <label for="dap_an_<?= $i ?>_<?= $j ?>" class="radio-label-modern"><?= chr(64 + $j) ?></label>
                                        </div>
                                        <input type="text" name="dap_an_<?= $i ?>_<?= $j ?>" class="form-control-modern answer-input" placeholder="Đáp án <?= chr(64 + $j) ?>">
                                    </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="form-group-modern">
                                <label class="form-label-modern">
                                    <i class="fas fa-lightbulb"></i> Giải thích
                                </label>
                                <textarea name="giai_thich_<?= $i ?>" class="form-control-modern" rows="2" placeholder="Giải thích đáp án đúng..."></textarea>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                    </div>
                </div>
                <div class="modal-footer-modern">
                    <button type="button" class="btn-modern btn-secondary-modern" data-dismiss="modal">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn-modern btn-success-modern">
                        <i class="fas fa-save"></i> Lưu <span id="submit_count">10</span> Câu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
