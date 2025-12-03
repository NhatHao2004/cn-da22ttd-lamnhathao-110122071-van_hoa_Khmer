<?php
session_start();
require_once 'config/database.php';
require_once 'includes/auth.php';

// Kiểm tra đăng nhập
checkAdminAuth();

header('Content-Type: application/json; charset=utf-8');

$db = Database::getInstance();
$query = $_GET['q'] ?? '';
$results = [];

if(strlen($query) >= 2) {
    $searchTerm = '%' . $query . '%';
    
    // Tìm kiếm bài viết
    $articles = $db->query(
        "SELECT ma_bai_viet as id, tieu_de as title, 'bai_viet' as type, 'Bài viết' as type_label 
        FROM bai_viet 
        WHERE tieu_de LIKE ? OR noi_dung LIKE ? 
        LIMIT 3",
        [$searchTerm, $searchTerm]
    );
    
    if($articles) {
        foreach($articles as $article) {
            $article['url'] = 'vanhoa.php?id=' . $article['id'];
            $results[] = $article;
        }
    }
    
    // Tìm kiếm chùa
    $temples = $db->query(
        "SELECT ma_chua as id, ten_chua as title, 'chua' as type, 'Chùa Khmer' as type_label 
        FROM chua_khmer 
        WHERE ten_chua LIKE ? OR mo_ta LIKE ? 
        LIMIT 3",
        [$searchTerm, $searchTerm]
    );
    
    if($temples) {
        foreach($temples as $temple) {
            $temple['url'] = 'chua.php?id=' . $temple['id'];
            $results[] = $temple;
        }
    }
    
    // Tìm kiếm lễ hội
    $festivals = $db->query(
        "SELECT ma_le_hoi as id, ten_le_hoi as title, 'le_hoi' as type, 'Lễ hội' as type_label 
        FROM le_hoi 
        WHERE ten_le_hoi LIKE ? OR mo_ta LIKE ? 
        LIMIT 3",
        [$searchTerm, $searchTerm]
    );
    
    if($festivals) {
        foreach($festivals as $festival) {
            $festival['url'] = 'lehoi.php?id=' . $festival['id'];
            $results[] = $festival;
        }
    }
    
    // Tìm kiếm truyện
    $stories = $db->query(
        "SELECT ma_truyen as id, tieu_de as title, 'truyen' as type, 'Truyện dân gian' as type_label 
        FROM truyen_dan_gian 
        WHERE tieu_de LIKE ? OR noi_dung LIKE ? 
        LIMIT 2",
        [$searchTerm, $searchTerm]
    );
    
    if($stories) {
        foreach($stories as $story) {
            $story['url'] = 'truyen.php?id=' . $story['id'];
            $results[] = $story;
        }
    }
    
    // Tìm kiếm bài học
    $lessons = $db->query(
        "SELECT ma_bai_hoc as id, tieu_de as title, 'bai_hoc' as type, 'Bài học' as type_label 
        FROM bai_hoc 
        WHERE tieu_de LIKE ? OR noi_dung LIKE ? 
        LIMIT 2",
        [$searchTerm, $searchTerm]
    );
    
    if($lessons) {
        foreach($lessons as $lesson) {
            $lesson['url'] = 'hoctiengkhmer.php?id=' . $lesson['id'];
            $results[] = $lesson;
        }
    }
}

echo json_encode($results, JSON_UNESCAPED_UNICODE);
