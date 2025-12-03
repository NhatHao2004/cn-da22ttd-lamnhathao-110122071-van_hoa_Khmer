# 🚀 HƯỚNG DẪN NHANH - FRONTEND USER

## 📋 Yêu cầu

- PHP 8.0+
- MySQL 5.7+
- Apache/Nginx với mod_rewrite
- Database `van_hoa_khmer` đã được import

## ⚡ Cài đặt nhanh

### Bước 1: Kiểm tra database

Đảm bảo database đã được import từ Backend:

```bash
mysql -u root -p van_hoa_khmer < ../Backend/csdl/van_hoa_khmer.sql
```

### Bước 2: Cấu hình database

Mở file `config/database.php` và kiểm tra thông tin kết nối:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'van_hoa_khmer');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### Bước 3: Khởi động server

```bash
# Nếu dùng XAMPP
# Đảm bảo Apache và MySQL đang chạy
# Truy cập: http://localhost/DoAn_ChuyenNganh_Website/FrontendUser/

# Hoặc dùng PHP built-in server
cd FrontendUser
php -S localhost:8000
```

### Bước 4: Truy cập website

Mở trình duyệt và truy cập:
- XAMPP: `http://localhost/DoAn_ChuyenNganh_Website/FrontendUser/`
- PHP Server: `http://localhost:8000/`

## 🎨 Cấu trúc file

```
FrontendUser/
├── assets/
│   ├── css/
│   │   └── style.css          # CSS chính (đã hoàn thành)
│   ├── js/
│   │   └── main.js            # JavaScript (đã hoàn thành)
│   └── images/                # Thư mục hình ảnh
├── config/
│   └── database.php           # Kết nối database
├── includes/
│   ├── header.php             # Header (đã hoàn thành)
│   └── footer.php             # Footer (đã hoàn thành)
├── index.php                  # Trang chủ (đã hoàn thành)
├── search.php                 # API tìm kiếm (đã hoàn thành)
└── README.md
```

## 🎯 Tính năng đã có

### ✅ Hoàn thành
- [x] Trang chủ với UI hiện đại
- [x] Header với navigation menu
- [x] Footer với thông tin liên hệ
- [x] Hero section với thống kê
- [x] Features section (6 tính năng)
- [x] Content sections (Văn hóa, Chùa, Lễ hội, Bài học)
- [x] Search modal với AJAX
- [x] Mobile menu responsive
- [x] Back to top button
- [x] Scroll animations

### 🚧 Đang phát triển
- [ ] Trang đăng nhập/đăng ký
- [ ] Trang chi tiết văn hóa
- [ ] Trang chi tiết chùa
- [ ] Trang chi tiết lễ hội
- [ ] Trang chi tiết bài học
- [ ] Trang hồ sơ người dùng

## 🎨 Customize màu sắc

Mở file `assets/css/style.css` và chỉnh sửa CSS Variables:

```css
:root {
    --primary: #6366f1;        /* Màu chính */
    --secondary: #f59e0b;      /* Màu phụ */
    --accent: #ec4899;         /* Màu nhấn */
    /* ... */
}
```

## 📱 Responsive Breakpoints

- **Desktop:** > 768px
- **Mobile:** ≤ 768px

## 🔧 Troubleshooting

### Lỗi kết nối database
```
Lỗi: Lỗi kết nối database: SQLSTATE[HY000] [1045]
Giải pháp: Kiểm tra lại thông tin trong config/database.php
```

### Lỗi 404 Not Found
```
Lỗi: Trang không tìm thấy
Giải pháp: Kiểm tra file .htaccess và mod_rewrite đã bật chưa
```

### CSS/JS không load
```
Lỗi: Giao diện không hiển thị đúng
Giải pháp: Kiểm tra đường dẫn trong header.php
```

## 📞 Hỗ trợ

**Developer:** Lâm Nhật Hào  
**Email:** LamNhatHao@gmail.com  
**Phone:** 0337048780

## 🎉 Bắt đầu phát triển

Để thêm trang mới:

1. Tạo file PHP mới (ví dụ: `van-hoa.php`)
2. Include header: `include 'includes/header.php';`
3. Viết nội dung trang
4. Include footer: `include 'includes/footer.php';`
5. Thêm link vào menu trong `includes/header.php`

Ví dụ:

```php
<?php
$pageTitle = 'Văn hóa Khmer';
include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1>Văn hóa Khmer</h1>
        <!-- Nội dung của bạn -->
    </div>
</section>

<?php include 'includes/footer.php'; ?>
```

---

**Happy Coding! 🚀**
