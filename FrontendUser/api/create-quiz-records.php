<?php
/**
 * Tạo các record quiz trong bảng quiz để tránh lỗi foreign key
 */

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "Creating quiz records...\n\n";
    
    // Lấy tất cả các bài văn hóa
    try {
        $vanHoaStmt = $pdo->query("SELECT ma_van_hoa, tieu_de FROM van_hoa WHERE trang_thai = 'hien_thi'");
        $vanHoaList = $vanHoaStmt->fetchAll();
        
        $created = 0;
        foreach ($vanHoaList as $vh) {
            // Kiểm tra xem quiz đã tồn tại chưa
            $checkStmt = $pdo->prepare("SELECT ma_quiz FROM quiz WHERE ma_doi_tuong = ? AND loai_doi_tuong = 'van_hoa'");
            $checkStmt->execute([$vh['ma_van_hoa']]);
            
            if (!$checkStmt->fetch()) {
                // Tạo quiz mới
                $insertStmt = $pdo->prepare("
                    INSERT INTO quiz (ma_doi_tuong, loai_doi_tuong, tieu_de, mo_ta, trang_thai, ngay_tao) 
                    VALUES (?, 'van_hoa', ?, 'Quiz về văn hóa Khmer', 'hoat_dong', NOW())
                ");
                $insertStmt->execute([$vh['ma_van_hoa'], 'Quiz: ' . $vh['tieu_de']]);
                $created++;
                echo "Created quiz for van_hoa: {$vh['tieu_de']}\n";
            }
        }
        echo "Van hoa: $created records created\n\n";
    } catch (Exception $e) {
        echo "Van hoa error: " . $e->getMessage() . "\n\n";
    }
    
    // Kiểm tra và tạo cho chùa (nếu bảng tồn tại)
    try {
        $checkTable = $pdo->query("SHOW TABLES LIKE 'chua_khmer'");
        if ($checkTable->rowCount() > 0) {
            $chuaStmt = $pdo->query("SELECT ma_chua, ten_chua FROM chua_khmer WHERE trang_thai = 'hien_thi'");
            $chuaList = $chuaStmt->fetchAll();
            
            $created = 0;
            foreach ($chuaList as $chua) {
                $checkStmt = $pdo->prepare("SELECT ma_quiz FROM quiz WHERE ma_doi_tuong = ? AND loai_doi_tuong = 'chua'");
                $checkStmt->execute([$chua['ma_chua']]);
                
                if (!$checkStmt->fetch()) {
                    $insertStmt = $pdo->prepare("
                        INSERT INTO quiz (ma_doi_tuong, loai_doi_tuong, tieu_de, mo_ta, trang_thai, ngay_tao) 
                        VALUES (?, 'chua', ?, 'Quiz về chùa Khmer', 'hoat_dong', NOW())
                    ");
                    $insertStmt->execute([$chua['ma_chua'], 'Quiz: ' . $chua['ten_chua']]);
                    $created++;
                    echo "Created quiz for chua: {$chua['ten_chua']}\n";
                }
            }
            echo "Chua: $created records created\n\n";
        } else {
            echo "Chua: Table not found, skipped\n\n";
        }
    } catch (Exception $e) {
        echo "Chua error: " . $e->getMessage() . "\n\n";
    }
    
    // Lễ hội
    try {
        $checkTable = $pdo->query("SHOW TABLES LIKE 'le_hoi'");
        if ($checkTable->rowCount() > 0) {
            $leHoiStmt = $pdo->query("SELECT ma_le_hoi, ten_le_hoi FROM le_hoi WHERE trang_thai = 'hien_thi'");
            $leHoiList = $leHoiStmt->fetchAll();
            
            $created = 0;
            foreach ($leHoiList as $lh) {
                $checkStmt = $pdo->prepare("SELECT ma_quiz FROM quiz WHERE ma_doi_tuong = ? AND loai_doi_tuong = 'le_hoi'");
                $checkStmt->execute([$lh['ma_le_hoi']]);
                
                if (!$checkStmt->fetch()) {
                    $insertStmt = $pdo->prepare("
                        INSERT INTO quiz (ma_doi_tuong, loai_doi_tuong, tieu_de, mo_ta, trang_thai, ngay_tao) 
                        VALUES (?, 'le_hoi', ?, 'Quiz về lễ hội Khmer', 'hoat_dong', NOW())
                    ");
                    $insertStmt->execute([$lh['ma_le_hoi'], 'Quiz: ' . $lh['ten_le_hoi']]);
                    $created++;
                    echo "Created quiz for le_hoi: {$lh['ten_le_hoi']}\n";
                }
            }
            echo "Le hoi: $created records created\n\n";
        } else {
            echo "Le hoi: Table not found, skipped\n\n";
        }
    } catch (Exception $e) {
        echo "Le hoi error: " . $e->getMessage() . "\n\n";
    }
    
    // Truyện dân gian
    try {
        $checkTable = $pdo->query("SHOW TABLES LIKE 'truyen_dan_gian'");
        if ($checkTable->rowCount() > 0) {
            $truyenStmt = $pdo->query("SELECT ma_truyen, tieu_de FROM truyen_dan_gian WHERE trang_thai = 'hien_thi'");
            $truyenList = $truyenStmt->fetchAll();
            
            $created = 0;
            foreach ($truyenList as $truyen) {
                $checkStmt = $pdo->prepare("SELECT ma_quiz FROM quiz WHERE ma_doi_tuong = ? AND loai_doi_tuong = 'truyen'");
                $checkStmt->execute([$truyen['ma_truyen']]);
                
                if (!$checkStmt->fetch()) {
                    $insertStmt = $pdo->prepare("
                        INSERT INTO quiz (ma_doi_tuong, loai_doi_tuong, tieu_de, mo_ta, trang_thai, ngay_tao) 
                        VALUES (?, 'truyen', ?, 'Quiz về truyện dân gian', 'hoat_dong', NOW())
                    ");
                    $insertStmt->execute([$truyen['ma_truyen'], 'Quiz: ' . $truyen['tieu_de']]);
                    $created++;
                    echo "Created quiz for truyen: {$truyen['tieu_de']}\n";
                }
            }
            echo "Truyen: $created records created\n\n";
        } else {
            echo "Truyen: Table not found, skipped\n\n";
        }
    } catch (Exception $e) {
        echo "Truyen error: " . $e->getMessage() . "\n\n";
    }
    
    echo "\n=== Complete ===\n";
    
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
}

