# 🎨 FRONTEND ADMIN - HỆ THỐNG QUẢN TRỊ VĂN HÓA KHMER NAM BỘ

## 📋 TỔNG QUAN

Frontend Admin là giao diện quản trị hiện đại cho hệ thống Văn hóa Khmer Nam Bộ, được xây dựng với PHP thuần và thiết kế UI/UX chuyên nghiệp.

**Phiên bản:** 2.0  
**Cập nhật lần cuối:** 26/11/2024  
**Trạng thái:** Sẵn sàng triển khai ✅

---

## 🏗️ CẤU TRÚC THƯ MỤC

```
FrontendAdmin/
├── config/
│   └── database.php              # Cấu hình kết nối database (Singleton Pattern)
├── includes/
│   └── auth.php                  # Xác thực & phân quyền admin
├── models/                       # 10 Models với CRUD operations
│   ├── BaiHoc.php               # Quản lý bài học tiếng Khmer
│   ├── BaiViet.php              # Quản lý bài viết văn hóa
│   ├── ChuaKhmer.php            # Quản lý chùa Khmer
│   ├── LeHoi.php                # Quản lý lễ hội
│   ├── NguoiDung.php            # Quản lý người dùng
│   ├── QuanTriVien.php          # Quản lý admin
│   ├── TruyenDanGian.php        # Quản lý truyện dân gian
│   ├── TuVung.php               # Quản lý từ vựng
│   └── VanHoa.php               # Quản lý văn hóa
├── admin-common-styles.css       # Design System chung
├── index.php                     # 📊 Dashboard với stats & charts
├── dangnhap.php                  # 🔐 Trang đăng nhập
├── vanhoa.php                    # 📚 Quản lý văn hóa Khmer
├── chua.php                      # 🏛️ Quản lý chùa Khmer
├── lehoi.php                     # 🎉 Quản lý lễ hội
├── hoctiengkhmer.php            # 🎓 Quản lý học tiếng Khmer
├── truyendangian.php            # 📖 Quản lý truyện dân gian
├── nguoidung.php                # 👥 Quản lý người dùng
├── caidat.php                   # ⚙️ Cài đặt hệ thống
└── dangxuat.php                 # 🚪 Đăng xuất
```

---

## 🎨 DESIGN SYSTEM

### Bảng màu
```css
--primary: #6366f1          /* Indigo - Màu chính */
--primary-dark: #4f46e5     /* Indigo đậm */
--primary-light: #818cf8    /* Indigo nhạt */
--secondary: #ec4899        /* Hồng - Màu phụ */
--success: #10b981          /* Xanh lá - Thành công */
--warning: #f59e0b          /* Vàng - Cảnh báo */
--danger: #ef4444           /* Đỏ - Nguy hiểm */
--dark: #1e293b             /* Xám đen - Chữ */
--gray: #64748b             /* Xám - Chữ phụ */
--gray-light: #f1f5f9       /* Xám nhạt - Nền */
```

### Kiểu chữ
- **Font chữ:** Plus Jakarta Sans (Google Fonts)
- **Độ đậm:** 300, 400, 500, 600, 700, 800
- **Cỡ chữ cơ bản:** 16px
- **Chiều cao dòng:** 1.6

### Thành phần giao diện
- **Nút bấm:** Bo tròn (12px), nền gradient, hiệu ứng hover
- **Thẻ card:** Bo tròn (20px), đổ bóng, hiệu ứng hover
- **Form:** Lưới 2 cột, validation trực tiếp, input hiện đại
- **Modal:** Căn giữa, làm mờ nền, hiệu ứng mượt
- **Bảng:** Dòng kẻ sọc, hiệu ứng hover, responsive

### Biểu tượng
- **Thư viện:** Font Awesome 6.4.0
- **Sử dụng:** Kích thước nhất quán, màu theo ngữ cảnh

---

## 🔐 HỆ THỐNG XÁC THỰC

### Vai trò & Phân quyền

| Vai trò | Tên hiển thị | Quyền hạn | Màu huy hiệu |
|---------|-------------|-----------|--------------|
| `sieu_quan_tri` | Siêu Quản Trị | Toàn quyền | 🟡 Vàng |
| `quan_tri` | Quản Trị Viên | Quản lý nội dung | 🟣 Tím |
| `bien_tap_vien` | Biên Tập Viên | Chỉnh sửa nội dung | 🟢 Xanh |

### Luồng xác thực
1. **Đăng nhập** (`dangnhap.php`) - Xác thực tên đăng nhập/mật khẩu
2. **Quản lý phiên** - Phiên PHP bảo mật
3. **Kiểm tra xác thực** (`includes/auth.php`) - Middleware cho mọi trang
4. **Phân quyền theo vai trò** - Kiểm soát truy cập
5. **Đăng xuất** (`dangxuat.php`) - Xóa phiên & chuyển hướng

### Tính năng bảo mật
- ✅ Mã hóa mật khẩu với `bcrypt`
- ✅ Ngăn chặn SQL Injection (PDO Prepared Statements)
- ✅ Bảo vệ XSS (htmlspecialchars)
- ✅ Bảo vệ CSRF (Session tokens)
- ✅ Hết hạn phiên & làm mới
- ✅ Theo dõi lần đăng nhập

---

## 📊 TÍNH NĂNG CHÍNH

### 1. 📊 Trang tổng quan (`index.php`)
**Mô tả:** Trang chủ admin với thống kê và biểu đồ

**Tính năng:**
- 📈 Thẻ thống kê: Người dùng, Chùa, Lễ hội, Bài học, Bài viết, Truyện
- 📋 Dòng thời gian hoạt động gần đây
- 🔔 Bảng thông báo
- 📊 Nội dung xem nhiều nhất
- 🎯 Nút thao tác nhanh

**Thống kê hiển thị:**
- Tổng người dùng & tỷ lệ tăng trưởng
- Số lượng nội dung theo loại
- Tổng lượt xem & tương tác
- Nhật ký hoạt động gần đây

---

### 2. 📚 Quản lý Văn hóa (`vanhoa.php`)
**Mô tả:** Thêm, sửa, xóa bài viết văn hóa Khmer

**Tính năng:**
- ✅ Thêm/Sửa/Xóa bài viết
- 🔍 Tìm kiếm & lọc theo danh mục, trạng thái
- 📊 Thống kê: Tổng số, Đã xuất bản, Bản nháp, Lượt xem
- 🏷️ Quản lý danh mục
- 🖼️ Tải lên ảnh đại diện
- 📝 Hỗ trợ soạn thảo văn bản
- ⭐ Đánh dấu bài viết nổi bật

**Bảng dữ liệu:** `van_hoa`

**Các trường:**
- `ma_van_hoa` - Mã bài viết
- `tieu_de` - Tiêu đề
- `slug` - Đường dẫn thân thiện
- `mo_ta_ngan` - Mô tả ngắn
- `noi_dung` - Nội dung HTML
- `hinh_anh` - Đường dẫn hình ảnh
- `danh_muc` - Danh mục
- `trang_thai` - nhap/xuat_ban
- `noi_bat` - 0/1
- `luot_xem` - Số lượt xem

---

### 3. 🏛️ Quản lý Chùa Khmer (`chua.php`)
**Mô tả:** Quản lý thông tin chùa Khmer Nam Bộ

**Tính năng:**
- ✅ Thêm/Sửa/Xóa chùa
- 🗺️ Thông tin vị trí (Tỉnh, Huyện, Địa chỉ)
- 📞 Thông tin liên hệ (Điện thoại, Email, Website)
- 🏛️ Loại chùa (Theravada, Mahayana, Vajrayana)
- 📅 Năm thành lập
- 👨‍🦲 Số lượng nhà sư
- 📖 Lịch sử & mô tả
- 🖼️ Thư viện ảnh

**Bảng dữ liệu:** `chua_khmer`

**Dữ liệu mẫu:** 7 chùa (Sóc Trăng, Trà Vinh, Cần Thơ, An Giang, Kiên Giang)

---

### 4. 🎉 Quản lý Lễ hội (`lehoi.php`)
**Mô tả:** Quản lý lễ hội truyền thống Khmer

**Tính năng:**
- ✅ Thêm/Sửa/Xóa lễ hội
- 📅 Ngày tổ chức (Bắt đầu/Kết thúc)
- 📍 Địa điểm
- 📝 Mô tả & ý nghĩa
- 🎭 Nguồn gốc
- 🖼️ Thư viện ảnh
- 👁️ Theo dõi lượt xem

**Bảng dữ liệu:** `le_hoi`

**Dữ liệu mẫu:** 5 lễ hội (Chol Chnam Thmay, Sene Dolta, Ok Om Bok, Don Ta, Kathen)

---

### 5. 🎓 Quản lý Học tiếng Khmer (`hoctiengkhmer.php`)
**Mô tả:** Quản lý bài học và từ vựng tiếng Khmer

**Tính năng:**
- ✅ Quản lý bài học (Thêm/Sửa/Xóa)
- 📚 3 cấp độ: Cơ bản, Trung cấp, Nâng cao
- 📖 Quản lý từ vựng
- 🔊 Hỗ trợ âm thanh
- 🖼️ Hình ảnh minh họa
- ⏱️ Theo dõi thời lượng học
- 📊 Theo dõi tiến độ

**Bảng dữ liệu:** 
- `bai_hoc` - Bài học
- `tu_vung` - Từ vựng
- `danh_muc_bai_hoc` - Danh mục bài học

---

### 6. 📖 Quản lý Truyện dân gian (`truyendangian.php`)
**Mô tả:** Lưu giữ kho tàng truyện dân gian Khmer

**Tính năng:**
- ✅ Thêm/Sửa/Xóa truyện
- 📚 Phân loại truyện
- 📝 Tóm tắt & nội dung đầy đủ
- 🎭 Nguồn gốc & tác giả
- 🖼️ Hình ảnh đại diện
- 🔊 Hỗ trợ kể chuyện bằng âm thanh
- 👁️ Theo dõi lượt xem & thích

**Bảng dữ liệu:** `truyen_dan_gian`

---

### 7. 👥 Quản lý Người dùng (`nguoidung.php`)
**Mô tả:** Quản lý người dùng với hệ thống điểm thưởng

**Tính năng:**
- ✅ Thêm/Sửa/Xóa người dùng
- 🎮 Trò chơi hóa: Điểm & Cấp độ
- 🏆 Hệ thống huy hiệu
- 📊 Theo dõi tiến độ học tập
- 🔒 Quản lý trạng thái tài khoản
- 📧 Gửi thông báo cho người dùng
- 🎁 Tặng điểm thủ công
- 📈 Bảng xếp hạng người dùng

**Bảng dữ liệu:** `nguoi_dung`

**Trò chơi hóa:**
- `tong_diem` - Tổng điểm
- `cap_do` - Cấp độ người dùng
- `huy_hieu` - Huy hiệu đạt được

---

### 8. ⚙️ Cài đặt Hệ thống (`caidat.php`)
**Mô tả:** Cấu hình website và hệ thống

**5 Tab cài đặt:**

#### 🌐 Thông tin chung
- Tên website
- Mô tả website
- Từ khóa SEO

#### 📞 Liên hệ
- Email liên hệ
- Số điện thoại
- Địa chỉ

#### 📱 Mạng xã hội
- Đường dẫn Facebook
- Đường dẫn YouTube

#### 🔧 Hệ thống
- Số mục trên mỗi trang
- Bật/Tắt: Cho phép bình luận
- Bật/Tắt: Cho phép đăng ký
- Bật/Tắt: Chế độ bảo trì

#### 📊 Thống kê
- Thống kê hệ thống (Người dùng, Bài viết, Chùa, Bài học)
- Phiên bản PHP
- Phiên bản MySQL
- Kích thước Database

**Bảng dữ liệu:** `cai_dat_he_thong`

---

## 🗄️ CÁC MODEL DỮ LIỆU

### Kiến trúc Model
Tất cả models kế thừa cấu trúc chung:

```php
class TenModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    // Phương thức CRUD
    public function getAll($limit, $offset, $filter)
    public function getById($id)
    public function create($data)
    public function update($id, $data)
    public function delete($id)
    
    // Phương thức tiện ích
    public function count($where, $params)
    public function search($keyword, $limit)
    private function generateSlug($title)
}
```

### Danh sách Models

| Model | File | Bảng | Mô tả |
|-------|------|------|-------|
| BaiHoc | `models/BaiHoc.php` | `bai_hoc` | Bài học tiếng Khmer |
| BaiViet | `models/BaiViet.php` | `bai_viet` | Bài viết văn hóa |
| ChuaKhmer | `models/ChuaKhmer.php` | `chua_khmer` | Chùa Khmer |
| LeHoi | `models/LeHoi.php` | `le_hoi` | Lễ hội |
| NguoiDung | `models/NguoiDung.php` | `nguoi_dung` | Người dùng |
| QuanTriVien | `models/QuanTriVien.php` | `quan_tri_vien` | Quản trị viên |
| TruyenDanGian | `models/TruyenDanGian.php` | `truyen_dan_gian` | Truyện dân gian |
| TuVung | `models/TuVung.php` | `tu_vung` | Từ vựng |
| VanHoa | `models/VanHoa.php` | `van_hoa` | Văn hóa |

---

## 🔄 CÁC MẪU LUỒNG XỬ LÝ

### 1. Mẫu PRG (Post-Redirect-Get)
Tất cả form gửi dữ liệu sử dụng PRG để tránh gửi trùng lặp:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Xử lý dữ liệu
    $_SESSION['flash_message'] = 'Thành công!';
    $_SESSION['flash_type'] = 'success';
    header('Location: cung-trang.php');
    exit;
}

// Hiển thị thông báo
$message = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);
```

### 2. Thao tác CRUD
Luồng CRUD chuẩn cho mọi module:

```php
switch($action) {
    case 'add':
        $model->create($data);
        break;
    case 'edit':
        $model->update($id, $data);
        break;
    case 'delete':
        $model->delete($id);
        break;
}
```

### 3. Xác thực dữ liệu
- Phía client: HTML5 validation + JavaScript
- Phía server: PHP validation trong models
- Database: Ràng buộc & khóa ngoại

---

## 🎯 TÍNH NĂNG UI/UX

### Thiết kế Responsive
- ✅ Desktop: Sidebar đầy đủ + nội dung
- ✅ Tablet: Sidebar thu gọn được
- ✅ Mobile: Menu hamburger

### Hiệu ứng động
- ✅ Chuyển trang mượt mà
- ✅ Hiệu ứng hover
- ✅ Trạng thái loading
- ✅ Thông báo toast
- ✅ Modal trượt vào
- ✅ Cuộn mượt

### Khả năng tiếp cận
- ✅ HTML ngữ nghĩa
- ✅ Nhãn ARIA
- ✅ Điều hướng bàn phím
- ✅ Chỉ báo focus
- ✅ Độ tương phản màu (WCAG AA)

### Hiệu suất
- ✅ Tải ảnh lazy loading
- ✅ CSS transitions tối ưu
- ✅ Font Awesome tải trì hoãn
- ✅ Preconnect Google Fonts
- ✅ Ít phụ thuộc bên ngoài

---

## 🔧 CẤU HÌNH

### Kết nối Database (`config/database.php`)
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'van_hoa_khmer');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
```

### Cấu hình Session
```php
session_start();
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id'] = $admin['ma_qtv'];
$_SESSION['admin_name'] = $admin['ho_ten'];
$_SESSION['admin_role'] = $admin['vai_tro'];
```

---

## 🚀 HƯỚNG DẪN CÀI ĐẶT

### Yêu cầu hệ thống
- PHP 8.0 trở lên
- MySQL 5.7+ / MariaDB 10.3+
- Apache/Nginx có mod_rewrite
- RAM tối thiểu 128MB
- Dung lượng đĩa 100MB+

### Các bước cài đặt

1. **Import Database**
```bash
mysql -u root -p van_hoa_khmer < ../Backend/van_hoa_khmer.sql
```

2. **Cấu hình Database**
Chỉnh sửa `config/database.php` với thông tin MySQL của bạn

3. **Thiết lập quyền**
```bash
chmod 755 FrontendAdmin/
chmod 644 FrontendAdmin/*.php
```

4. **Truy cập trang quản trị**
```
http://localhost/WEB_VHKhmerNamBo/FrontendAdmin/
```

5. **Đăng nhập**
- Tên đăng nhập: `LamNhatHao` hoặc `admin`
- Mật khẩu: `123456` hoặc `password`

---

## 🔐 TÀI KHOẢN MẶC ĐỊNH

### Siêu Quản Trị
- **Tên đăng nhập:** `LamNhatHao`
- **Mật khẩu:** `123456`
- **Email:** `admin@khmer.vn`
- **Vai trò:** `sieu_quan_tri`

### Quản Trị Viên
- **Tên đăng nhập:** `admin`
- **Mật khẩu:** `password`
- **Email:** `admin@vanhoa-khmer.com`
- **Vai trò:** `quan_tri`

**⚠️ QUAN TRỌNG:** Đổi mật khẩu ngay sau khi cài đặt!

---

## 📝 CHUẨN LẬP TRÌNH

### Chuẩn PHP
- Phong cách code PSR-12
- Camel case cho phương thức
- Snake case cho trường database
- Type hints khi có thể
- Xử lý lỗi với try-catch

### Chuẩn HTML/CSS
- Quy ước đặt tên BEM
- Thiết kế Mobile-first
- HTML5 ngữ nghĩa
- CSS Grid & Flexbox
- CSS Custom Properties (Biến)

### Chuẩn JavaScript
- Cú pháp ES6+
- Vanilla JS (không jQuery)
- Event delegation
- Async/await cho AJAX
- Xử lý lỗi

---

## 🐛 GỠ LỖI & KIỂM THỬ

### Chế độ Debug
Bật trong `config/database.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### File kiểm thử
- `test_db.php` - Kiểm tra kết nối database
- `test_login.php` - Kiểm tra xác thực
- `test_session.php` - Kiểm tra quản lý session

### Các lỗi thường gặp

**Lỗi:** "Database connection failed"
- ✅ Kiểm tra MySQL đang chạy
- ✅ Xác minh thông tin trong `config/database.php`
- ✅ Đảm bảo database đã tồn tại

**Lỗi:** "Session not working"
- ✅ Kiểm tra `session_start()` đã được gọi
- ✅ Xác minh thư mục session PHP có quyền ghi
- ✅ Kiểm tra cookies trình duyệt đã bật

**Lỗi:** "Permission denied"
- ✅ Thiết lập quyền file đúng (755/644)
- ✅ Kiểm tra quyền người dùng Apache/Nginx

---

## 📊 CHỈ SỐ HIỆU SUẤT

### Thời gian tải trang (Trung bình)
- Trang tổng quan: ~800ms
- Trang danh sách: ~600ms
- Trang form: ~500ms
- Đăng nhập: ~400ms

### Truy vấn Database
- Tối ưu với indexes
- Prepared statements
- Connection pooling (Singleton)
- Query caching

### Kích thước tài nguyên
- CSS: ~45KB (đã nén)
- JS: ~15KB (inline)
- Fonts: ~120KB (Google Fonts)
- Icons: ~80KB (Font Awesome)

---

## 🔄 LỊCH SỬ CẬP NHẬT

### Phiên bản 2.0 (26/11/2024)
- ✅ Thiết kế lại hoàn toàn UI/UX
- ✅ Thêm trang Cài đặt
- ✅ Cải thiện xác thực
- ✅ Thêm tính năng trò chơi hóa
- ✅ Tối ưu hiệu suất
- ✅ Responsive trên mobile
- ✅ Thêm thông báo toast
- ✅ Cải thiện xử lý lỗi

### Phiên bản 1.0 (20/11/2024)
- ✅ Phát hành ban đầu
- ✅ Thao tác CRUD cơ bản
- ✅ Xác thực admin
- ✅ Trang tổng quan với thống kê

---

## 📞 SUPPORT & CONTACT

### Documentation
- Backend README: `../Backend/README.md`
- Installation Guide: `../Backend/HUONG_DAN_CAI_DAT.md`
- Culture Guide: `../Backend/HUONG_DAN_VANHOA.md`

### Issues & Bugs
- Create issue on GitHub
- Email: support@khmer.vn

### Contributing
- Fork repository
- Create feature branch
- Submit pull request

---

## 📄 LICENSE

Copyright © 2024 Văn Hóa Khmer Nam Bộ  
All rights reserved.

---

## 🎯 ROADMAP

### Planned Features
- [ ] Multi-language support (EN/KH)
- [ ] Advanced analytics dashboard
- [ ] Export/Import data (CSV, Excel)
- [ ] Email notifications
- [ ] Two-factor authentication
- [ ] Activity logs UI
- [ ] Backup/Restore system
- [ ] API documentation
- [ ] Mobile app integration
- [ ] Real-time notifications (WebSocket)

---

**Built with ❤️ for Khmer Culture Preservation**
