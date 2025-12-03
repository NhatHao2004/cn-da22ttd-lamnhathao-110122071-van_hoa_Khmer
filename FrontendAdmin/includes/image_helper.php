<?php
/**
 * Image Helper - Hỗ trợ xử lý đường dẫn ảnh
 * 
 * @author Lâm Nhật Hào
 * @version 1.0
 */

/**
 * Lấy đường dẫn đầy đủ của ảnh
 * 
 * @param string $imagePath Đường dẫn tương đối của ảnh (vd: uploads/vanhoa/image.jpg)
 * @param bool $absolute Trả về đường dẫn tuyệt đối hay tương đối
 * @return string Đường dẫn ảnh hoặc ảnh placeholder
 */
function getImageUrl($imagePath, $absolute = false) {
    // Nếu không có ảnh, trả về placeholder
    if (empty($imagePath)) {
        return $absolute 
            ? $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/FrontendAdmin/assets/images/no-image.png'
            : 'assets/images/no-image.png';
    }
    
    // Nếu đã là URL đầy đủ (http/https), trả về luôn
    if (preg_match('/^https?:\/\//', $imagePath)) {
        return $imagePath;
    }
    
    // Xử lý đường dẫn tương đối
    // Loại bỏ dấu / ở đầu nếu có
    $imagePath = ltrim($imagePath, '/');
    
    // Kiểm tra file có tồn tại không
    $fullPath = __DIR__ . '/../' . $imagePath;
    if (!file_exists($fullPath)) {
        return $absolute 
            ? $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/FrontendAdmin/assets/images/no-image.png'
            : 'assets/images/no-image.png';
    }
    
    // Trả về đường dẫn
    if ($absolute) {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/FrontendAdmin/' . $imagePath;
    }
    
    return $imagePath;
}

/**
 * Hiển thị thẻ img với xử lý lỗi
 * 
 * @param string $imagePath Đường dẫn ảnh
 * @param string $alt Text thay thế
 * @param string $class CSS class
 * @param array $attributes Các thuộc tính khác
 * @return string HTML img tag
 */
function renderImage($imagePath, $alt = '', $class = '', $attributes = []) {
    $url = getImageUrl($imagePath);
    $placeholder = 'assets/images/no-image.png';
    
    $attrs = [];
    $attrs[] = 'src="' . htmlspecialchars($url) . '"';
    $attrs[] = 'alt="' . htmlspecialchars($alt) . '"';
    
    if ($class) {
        $attrs[] = 'class="' . htmlspecialchars($class) . '"';
    }
    
    // Thêm onerror để hiển thị placeholder nếu ảnh lỗi
    $attrs[] = 'onerror="this.src=\'' . $placeholder . '\'; this.onerror=null;"';
    
    // Thêm loading lazy
    $attrs[] = 'loading="lazy"';
    
    // Thêm các thuộc tính khác
    foreach ($attributes as $key => $value) {
        $attrs[] = htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
    }
    
    return '<img ' . implode(' ', $attrs) . '>';
}

/**
 * Lấy thumbnail của ảnh (nếu có)
 * 
 * @param string $imagePath Đường dẫn ảnh gốc
 * @return string Đường dẫn thumbnail
 */
function getThumbnailUrl($imagePath) {
    if (empty($imagePath)) {
        return 'assets/images/no-image.png';
    }
    
    // Tạo đường dẫn thumbnail
    $pathInfo = pathinfo($imagePath);
    $thumbnailPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
    
    // Kiểm tra thumbnail có tồn tại không
    $fullPath = __DIR__ . '/../' . $thumbnailPath;
    if (file_exists($fullPath)) {
        return $thumbnailPath;
    }
    
    // Nếu không có thumbnail, trả về ảnh gốc
    return getImageUrl($imagePath);
}

/**
 * Kiểm tra ảnh có tồn tại không
 * 
 * @param string $imagePath Đường dẫn ảnh
 * @return bool
 */
function imageExists($imagePath) {
    if (empty($imagePath)) {
        return false;
    }
    
    $fullPath = __DIR__ . '/../' . ltrim($imagePath, '/');
    return file_exists($fullPath) && is_file($fullPath);
}

/**
 * Lấy kích thước ảnh
 * 
 * @param string $imagePath Đường dẫn ảnh
 * @return array|false [width, height, type] hoặc false nếu lỗi
 */
function getImageDimensions($imagePath) {
    if (!imageExists($imagePath)) {
        return false;
    }
    
    $fullPath = __DIR__ . '/../' . ltrim($imagePath, '/');
    $size = @getimagesize($fullPath);
    
    if ($size === false) {
        return false;
    }
    
    return [
        'width' => $size[0],
        'height' => $size[1],
        'type' => $size[2],
        'mime' => $size['mime']
    ];
}

/**
 * Format kích thước file
 * 
 * @param string $imagePath Đường dẫn ảnh
 * @return string Kích thước file đã format (vd: 1.5 MB)
 */
function getImageFileSize($imagePath) {
    if (!imageExists($imagePath)) {
        return 'N/A';
    }
    
    $fullPath = __DIR__ . '/../' . ltrim($imagePath, '/');
    $bytes = filesize($fullPath);
    
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    
    return round($bytes, 2) . ' ' . $units[$i];
}
