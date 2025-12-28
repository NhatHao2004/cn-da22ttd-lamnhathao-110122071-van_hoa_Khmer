<?php
/**
 * Comment System Component
 * Hệ thống bình luận có thể tái sử dụng cho nhiều loại nội dung
 */

/**
 * Kiểm tra bảng binh_luan có tồn tại không
 */
function commentTableExists() {
    static $exists = null;
    if ($exists === null) {
        try {
            $pdo = getDBConnection();
            $result = $pdo->query("SHOW TABLES LIKE 'binh_luan'");
            $exists = $result->rowCount() > 0;
        } catch (Exception $e) {
            $exists = false;
        }
    }
    return $exists;
}

/**
 * Lấy danh sách bình luận theo nội dung
 */
function getComments($loai_noi_dung, $ma_noi_dung, $limit = 20) {
    if (!commentTableExists()) return [];
    
    try {
        $pdo = getDBConnection();
        $sql = "SELECT bl.*, nd.ho_ten, nd.anh_dai_dien, nd.tong_diem,
                (SELECT COUNT(*) FROM binh_luan WHERE ma_binh_luan_cha = bl.ma_binh_luan AND trang_thai = 'hien_thi') as so_tra_loi
                FROM binh_luan bl
                JOIN nguoi_dung nd ON bl.ma_nguoi_dung = nd.ma_nguoi_dung
                WHERE bl.loai_noi_dung = ? AND bl.ma_noi_dung = ? 
                AND bl.ma_binh_luan_cha IS NULL AND bl.trang_thai = 'hien_thi'
                ORDER BY bl.ngay_tao DESC LIMIT ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$loai_noi_dung, $ma_noi_dung, $limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Lấy replies của một bình luận
 */
function getReplies($ma_binh_luan_cha) {
    if (!commentTableExists()) return [];
    
    try {
        $pdo = getDBConnection();
        $sql = "SELECT bl.*, nd.ho_ten, nd.anh_dai_dien, nd.tong_diem
                FROM binh_luan bl
                JOIN nguoi_dung nd ON bl.ma_nguoi_dung = nd.ma_nguoi_dung
                WHERE bl.ma_binh_luan_cha = ? AND bl.trang_thai = 'hien_thi'
                ORDER BY bl.ngay_tao ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ma_binh_luan_cha]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Đếm tổng số bình luận
 */
function countComments($loai_noi_dung, $ma_noi_dung) {
    if (!commentTableExists()) return 0;
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM binh_luan WHERE loai_noi_dung = ? AND ma_noi_dung = ? AND trang_thai = 'hien_thi'");
        $stmt->execute([$loai_noi_dung, $ma_noi_dung]);
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * Kiểm tra user đã like bình luận chưa
 */
function hasLikedComment($ma_binh_luan, $ma_nguoi_dung) {
    if (!commentTableExists()) return false;
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT 1 FROM like_binh_luan WHERE ma_binh_luan = ? AND ma_nguoi_dung = ?");
        $stmt->execute([$ma_binh_luan, $ma_nguoi_dung]);
        return $stmt->fetch() ? true : false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Thêm bình luận mới
 */
function addComment($data) {
    if (!commentTableExists()) return false;
    
    try {
        $pdo = getDBConnection();
        $sql = "INSERT INTO binh_luan (ma_nguoi_dung, loai_noi_dung, ma_noi_dung, ma_binh_luan_cha, noi_dung) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $data['ma_nguoi_dung'],
            $data['loai_noi_dung'],
            $data['ma_noi_dung'],
            $data['ma_binh_luan_cha'] ?? null,
            $data['noi_dung']
        ]);
        
        if ($result) {
            $commentId = $pdo->lastInsertId();
            // Thêm điểm cho người dùng
            if (function_exists('addUserPoints')) {
                addUserPoints($data['ma_nguoi_dung'], 2, 'Đăng bình luận');
            }
            
            // Gửi thông báo nếu là reply
            if (!empty($data['ma_binh_luan_cha'])) {
                sendCommentNotification($commentId, 'tra_loi_binh_luan');
            }
            
            return $commentId;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Like/Unlike bình luận
 */
function toggleLikeComment($ma_binh_luan, $ma_nguoi_dung) {
    $pdo = getDBConnection();
    
    if (hasLikedComment($ma_binh_luan, $ma_nguoi_dung)) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM like_binh_luan WHERE ma_binh_luan = ? AND ma_nguoi_dung = ?");
        $stmt->execute([$ma_binh_luan, $ma_nguoi_dung]);
        $pdo->prepare("UPDATE binh_luan SET so_like = so_like - 1 WHERE ma_binh_luan = ?")->execute([$ma_binh_luan]);
        return ['action' => 'unliked', 'likes' => getLikeCount($ma_binh_luan)];
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO like_binh_luan (ma_binh_luan, ma_nguoi_dung) VALUES (?, ?)");
        $stmt->execute([$ma_binh_luan, $ma_nguoi_dung]);
        $pdo->prepare("UPDATE binh_luan SET so_like = so_like + 1 WHERE ma_binh_luan = ?")->execute([$ma_binh_luan]);
        
        // Gửi thông báo
        sendCommentNotification($ma_binh_luan, 'like_binh_luan', $ma_nguoi_dung);
        
        return ['action' => 'liked', 'likes' => getLikeCount($ma_binh_luan)];
    }
}

function getLikeCount($ma_binh_luan) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT so_like FROM binh_luan WHERE ma_binh_luan = ?");
    $stmt->execute([$ma_binh_luan]);
    return $stmt->fetchColumn();
}

/**
 * Báo cáo vi phạm
 */
function reportComment($ma_binh_luan, $ma_nguoi_bao_cao, $ly_do, $mo_ta = '') {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO bao_cao_vi_pham (ma_nguoi_bao_cao, loai_doi_tuong, ma_doi_tuong, ly_do, mo_ta) VALUES (?, 'binh_luan', ?, ?, ?)");
    $result = $stmt->execute([$ma_nguoi_bao_cao, $ma_binh_luan, $ly_do, $mo_ta]);
    
    if ($result) {
        // Tăng số báo cáo
        $pdo->prepare("UPDATE binh_luan SET so_bao_cao = so_bao_cao + 1 WHERE ma_binh_luan = ?")->execute([$ma_binh_luan]);
        
        // Tự động ẩn nếu quá 3 báo cáo
        $pdo->prepare("UPDATE binh_luan SET trang_thai = 'cho_duyet' WHERE ma_binh_luan = ? AND so_bao_cao >= 3")->execute([$ma_binh_luan]);
    }
    return $result;
}

/**
 * Gửi thông báo
 */
function sendCommentNotification($ma_binh_luan, $loai, $ma_nguoi_gui = null) {
    $pdo = getDBConnection();
    
    // Lấy thông tin bình luận
    $stmt = $pdo->prepare("SELECT bl.*, nd.ho_ten FROM binh_luan bl JOIN nguoi_dung nd ON bl.ma_nguoi_dung = nd.ma_nguoi_dung WHERE bl.ma_binh_luan = ?");
    $stmt->execute([$ma_binh_luan]);
    $comment = $stmt->fetch();
    if (!$comment) return;
    
    $ma_nguoi_nhan = null;
    $tieu_de = '';
    $noi_dung = '';
    
    if ($loai === 'tra_loi_binh_luan' && $comment['ma_binh_luan_cha']) {
        // Lấy người viết bình luận gốc
        $stmt = $pdo->prepare("SELECT ma_nguoi_dung FROM binh_luan WHERE ma_binh_luan = ?");
        $stmt->execute([$comment['ma_binh_luan_cha']]);
        $parent = $stmt->fetch();
        if ($parent && $parent['ma_nguoi_dung'] != $comment['ma_nguoi_dung']) {
            $ma_nguoi_nhan = $parent['ma_nguoi_dung'];
            $tieu_de = $comment['ho_ten'] . ' đã trả lời bình luận của bạn';
            $noi_dung = mb_substr($comment['noi_dung'], 0, 100) . '...';
        }
    } elseif ($loai === 'like_binh_luan' && $ma_nguoi_gui) {
        if ($comment['ma_nguoi_dung'] != $ma_nguoi_gui) {
            $ma_nguoi_nhan = $comment['ma_nguoi_dung'];
            $stmt = $pdo->prepare("SELECT ho_ten FROM nguoi_dung WHERE ma_nguoi_dung = ?");
            $stmt->execute([$ma_nguoi_gui]);
            $sender = $stmt->fetch();
            $tieu_de = ($sender['ho_ten'] ?? 'Ai đó') . ' đã thích bình luận của bạn';
            $noi_dung = mb_substr($comment['noi_dung'], 0, 100) . '...';
        }
    }
    
    if ($ma_nguoi_nhan) {
        $stmt = $pdo->prepare("INSERT INTO thong_bao_nguoi_dung (ma_nguoi_nhan, ma_nguoi_gui, loai, tieu_de, noi_dung) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$ma_nguoi_nhan, $ma_nguoi_gui ?? $comment['ma_nguoi_dung'], $loai, $tieu_de, $noi_dung]);
    }
}
?>
