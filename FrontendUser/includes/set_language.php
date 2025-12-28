<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['language'])) {
    $language = $_POST['language'];
    
    // Validate language
    if (in_array($language, ['vi', 'km'])) {
        $_SESSION['language'] = $language;
        echo json_encode(['success' => true, 'language' => $language]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid language']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
