<?php
/**
 * Upload Handler - Xử lý upload file ảnh
 * Hỗ trợ: JPG, JPEG, PNG, GIF, WEBP
 * 
 * @author Lâm Nhật Hào
 * @version 2.0
 */

class ImageUploader {
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize = 5242880; // 5MB
    private $errors = [];
    
    public function __construct($subDir = 'general') {
        // Tạo đường dẫn upload - lưu vào thư mục root/uploads
        $baseDir = __DIR__ . '/../../uploads/';
        $this->uploadDir = $baseDir . $subDir . '/';
        
        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        
        // Tạo file .htaccess để bảo vệ
        $htaccessFile = $baseDir . '.htaccess';
        if (!file_exists($htaccessFile)) {
            $htaccessContent = "# Chỉ cho phép truy cập file ảnh\n";
            $htaccessContent .= "<FilesMatch \"\\.(jpg|jpeg|png|gif|webp)$\">\n";
            $htaccessContent .= "    Order Allow,Deny\n";
            $htaccessContent .= "    Allow from all\n";
            $htaccessContent .= "</FilesMatch>\n";
            $htaccessContent .= "# Chặn truy cập file PHP\n";
            $htaccessContent .= "<FilesMatch \"\\.php$\">\n";
            $htaccessContent .= "    Order Deny,Allow\n";
            $htaccessContent .= "    Deny from all\n";
            $htaccessContent .= "</FilesMatch>\n";
            file_put_contents($htaccessFile, $htaccessContent);
        }
    }
    
    /**
     * Upload một file ảnh
     */
    public function upload($fileInput, $customName = null) {
        $this->errors = [];
        
        // Kiểm tra file có được upload không
        if (!isset($fileInput) || $fileInput['error'] === UPLOAD_ERR_NO_FILE) {
            $this->errors[] = 'Không có file nào được chọn';
            return false;
        }
        
        // Kiểm tra lỗi upload
        if ($fileInput['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($fileInput['error']);
            return false;
        }
        
        // Kiểm tra kích thước file
        if ($fileInput['size'] > $this->maxFileSize) {
            $this->errors[] = 'File quá lớn. Kích thước tối đa: ' . ($this->maxFileSize / 1024 / 1024) . 'MB';
            return false;
        }
        
        // Kiểm tra loại file
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileInput['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            $this->errors[] = 'Loại file không được phép. Chỉ chấp nhận: JPG, PNG, GIF, WEBP';
            return false;
        }
        
        // Kiểm tra extension
        $originalName = $fileInput['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $this->allowedExtensions)) {
            $this->errors[] = 'Phần mở rộng file không hợp lệ';
            return false;
        }
        
        // Tạo tên file mới
        if ($customName) {
            $fileName = $this->sanitizeFileName($customName) . '.' . $extension;
        } else {
            $fileName = $this->generateFileName($extension);
        }
        
        // Đường dẫn đầy đủ
        $filePath = $this->uploadDir . $fileName;
        
        // Nếu file đã tồn tại, thêm số vào tên
        $counter = 1;
        $baseFileName = pathinfo($fileName, PATHINFO_FILENAME);
        while (file_exists($filePath)) {
            $fileName = $baseFileName . '_' . $counter . '.' . $extension;
            $filePath = $this->uploadDir . $fileName;
            $counter++;
        }
        
        // Di chuyển file
        if (move_uploaded_file($fileInput['tmp_name'], $filePath)) {
            // Tối ưu hóa ảnh
            $this->optimizeImage($filePath, $mimeType);
            
            // Trả về đường dẫn tương đối từ root project (chuẩn hóa dấu / cho cross-platform)
            $basePath = str_replace('\\', '/', realpath(__DIR__ . '/../..'));
            $fullPath = str_replace('\\', '/', realpath($filePath));
            $relativePath = str_replace($basePath . '/', '', $fullPath);
            
            return $relativePath;
        } else {
            $this->errors[] = 'Không thể lưu file. Vui lòng kiểm tra quyền thư mục';
            return false;
        }
    }
    
    /**
     * Upload nhiều file
     */
    public function uploadMultiple($filesInput) {
        $uploadedFiles = [];
        $this->errors = [];
        
        if (!isset($filesInput['name']) || !is_array($filesInput['name'])) {
            $this->errors[] = 'Dữ liệu file không hợp lệ';
            return false;
        }
        
        $fileCount = count($filesInput['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            // Tạo mảng file đơn
            $file = [
                'name' => $filesInput['name'][$i],
                'type' => $filesInput['type'][$i],
                'tmp_name' => $filesInput['tmp_name'][$i],
                'error' => $filesInput['error'][$i],
                'size' => $filesInput['size'][$i]
            ];
            
            // Upload từng file
            $result = $this->upload($file);
            if ($result) {
                $uploadedFiles[] = $result;
            }
        }
        
        return $uploadedFiles;
    }
    
    /**
     * Xóa file ảnh
     */
    public function delete($filePath) {
        if (empty($filePath)) {
            return false;
        }
        
        // Chuyển đường dẫn tương đối thành tuyệt đối
        $fullPath = __DIR__ . '/../' . $filePath;
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
    
    /**
     * Tối ưu hóa ảnh
     */
    private function optimizeImage($filePath, $mimeType) {
        // Kiểm tra GD extension có được cài đặt không
        if (!extension_loaded('gd')) {
            // Nếu không có GD, bỏ qua việc tối ưu hóa
            return true;
        }
        
        // Giới hạn kích thước ảnh
        $maxWidth = 1920;
        $maxHeight = 1080;
        $quality = 85;
        
        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            return true; // Không thể đọc ảnh, bỏ qua
        }
        
        list($width, $height) = $imageInfo;
        
        // Nếu ảnh nhỏ hơn giới hạn, không cần resize
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return true;
        }
        
        // Tính tỷ lệ resize
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        // Tạo ảnh từ file gốc
        $source = null;
        try {
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    if (function_exists('imagecreatefromjpeg')) {
                        $source = @imagecreatefromjpeg($filePath);
                    }
                    break;
                case 'image/png':
                    if (function_exists('imagecreatefrompng')) {
                        $source = @imagecreatefrompng($filePath);
                    }
                    break;
                case 'image/gif':
                    if (function_exists('imagecreatefromgif')) {
                        $source = @imagecreatefromgif($filePath);
                    }
                    break;
                case 'image/webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $source = @imagecreatefromwebp($filePath);
                    }
                    break;
                default:
                    return true;
            }
        } catch (Exception $e) {
            return true; // Lỗi khi tạo ảnh, bỏ qua
        }
        
        if (!$source) {
            return true; // Không thể tạo ảnh, bỏ qua
        }
        
        // Tạo ảnh mới với kích thước đã resize
        $destination = @imagecreatetruecolor($newWidth, $newHeight);
        if (!$destination) {
            @imagedestroy($source);
            return true;
        }
        
        // Giữ trong suốt cho PNG và GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            @imagealphablending($destination, false);
            @imagesavealpha($destination, true);
            $transparent = @imagecolorallocatealpha($destination, 255, 255, 255, 127);
            @imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize ảnh
        @imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Lưu ảnh đã resize
        try {
            switch ($mimeType) {
                case 'image/jpeg':
                case 'image/jpg':
                    @imagejpeg($destination, $filePath, $quality);
                    break;
                case 'image/png':
                    @imagepng($destination, $filePath, 9);
                    break;
                case 'image/gif':
                    @imagegif($destination, $filePath);
                    break;
                case 'image/webp':
                    @imagewebp($destination, $filePath, $quality);
                    break;
            }
        } catch (Exception $e) {
            // Lỗi khi lưu, không sao
        }
        
        // Giải phóng bộ nhớ
        @imagedestroy($source);
        @imagedestroy($destination);
        
        return true;
    }
    
    /**
     * Tạo tên file ngẫu nhiên
     */
    private function generateFileName($extension) {
        return date('Ymd_His') . '_' . uniqid() . '.' . $extension;
    }
    
    /**
     * Làm sạch tên file
     */
    private function sanitizeFileName($fileName) {
        // Chuyển tiếng Việt sang không dấu
        $fileName = $this->removeVietnameseTones($fileName);
        
        // Chỉ giữ chữ cái, số, gạch ngang và gạch dưới
        $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fileName);
        
        // Loại bỏ nhiều gạch dưới liên tiếp
        $fileName = preg_replace('/_+/', '_', $fileName);
        
        // Loại bỏ gạch dưới ở đầu và cuối
        $fileName = trim($fileName, '_');
        
        return strtolower($fileName);
    }
    
    /**
     * Chuyển tiếng Việt sang không dấu
     */
    private function removeVietnameseTones($str) {
        $vietnamese = [
            'à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ',
            'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
            'ì', 'í', 'ị', 'ỉ', 'ĩ',
            'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
            'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
            'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ', 'đ',
            'À', 'Á', 'Ạ', 'Ả', 'Ã', 'Â', 'Ầ', 'Ấ', 'Ậ', 'Ẩ', 'Ẫ', 'Ă', 'Ằ', 'Ắ', 'Ặ', 'Ẳ', 'Ẵ',
            'È', 'É', 'Ẹ', 'Ẻ', 'Ẽ', 'Ê', 'Ề', 'Ế', 'Ệ', 'Ể', 'Ễ',
            'Ì', 'Í', 'Ị', 'Ỉ', 'Ĩ',
            'Ò', 'Ó', 'Ọ', 'Ỏ', 'Õ', 'Ô', 'Ồ', 'Ố', 'Ộ', 'Ổ', 'Ỗ', 'Ơ', 'Ờ', 'Ớ', 'Ợ', 'Ở', 'Ỡ',
            'Ù', 'Ú', 'Ụ', 'Ủ', 'Ũ', 'Ư', 'Ừ', 'Ứ', 'Ự', 'Ử', 'Ữ',
            'Ỳ', 'Ý', 'Ỵ', 'Ỷ', 'Ỹ', 'Đ'
        ];
        
        $latin = [
            'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
            'i', 'i', 'i', 'i', 'i',
            'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'y', 'y', 'y', 'y', 'y', 'd',
            'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A',
            'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E',
            'I', 'I', 'I', 'I', 'I',
            'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O',
            'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U',
            'Y', 'Y', 'Y', 'Y', 'Y', 'D'
        ];
        
        return str_replace($vietnamese, $latin, $str);
    }
    
    /**
     * Lấy thông báo lỗi upload
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'File quá lớn';
            case UPLOAD_ERR_PARTIAL:
                return 'File chỉ được upload một phần';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Thiếu thư mục tạm';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Không thể ghi file vào đĩa';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload bị chặn bởi extension';
            default:
                return 'Lỗi không xác định';
        }
    }
    
    /**
     * Lấy danh sách lỗi
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Lấy thông báo lỗi dạng chuỗi
     */
    public function getErrorString() {
        return implode(', ', $this->errors);
    }
}
