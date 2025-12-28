<?php
/**
 * Download file với encoding đúng
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$file = $_GET['file'] ?? '';
$type = $_GET['type'] ?? 'document'; // document hoặc image

if (!$file) {
    die('File không tồn tại');
}

// Xác định đường dẫn
if ($type === 'image') {
    $filepath = __DIR__ . '/uploads/posts/' . basename($file);
} else {
    $filepath = __DIR__ . '/uploads/documents/' . basename($file);
}

// Kiểm tra file tồn tại
if (!file_exists($filepath)) {
    die('File không tồn tại');
}

// Lấy thông tin file
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $filepath);
finfo_close($finfo);

// Set headers
header('Content-Type: ' . $mime_type . '; charset=UTF-8');
header('Content-Disposition: inline; filename="' . basename($file) . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: public, max-age=3600');

// Output file
readfile($filepath);
exit;
?>
