# 🎨 FRONTEND USER - VĂN HÓA KHMER NAM BỘ

## 📋 TỔNG QUAN

Frontend User là giao diện người dùng cho hệ thống Văn Hóa Khmer Nam Bộ, được xây dựng với PHP thuần và thiết kế UI/UX hiện đại.

**Phiên bản:** 1.0  
**Ngày tạo:** 30/11/2024  
**Trạng thái:** Đang phát triển 🚧

---

## 🏗️ CẤU TRÚC THƯ MỤC

```
FrontendUser/
├── assets/
│   ├── css/
│   │   └── style.css           # CSS chính
│   ├── js/
│   │   └── main.js             # JavaScript chính
│   └── images/                 # Hình ảnh
├── config/
│   └── database.php            # Kết nối database
├── includes/
│   ├── header.php              # Header chung
│   └── footer.php              # Footer chung
├── index.php                   # Trang chủ
└── README.md                   # File này
```

---

## 🎨 DESIGN SYSTEM

### Màu sắc
```css
--primary: #6366f1          /* Indigo - Màu chính */
--primary-dark: #4f46e5     /* Indigo đậm */
--primary-light: #818cf8    /* Indigo nhạt */
--secondary: #ec4899        /* Hồng - Màu phụ */
--success: #10b981          /* Xanh lá */
--warning: #f59e0b          /* Vàng */
--danger: #ef4444           /* Đỏ */
```

### Typography
- **Font:** Plus Jakarta Sans (Google Fonts)
- **Weights:** 300, 400, 500, 600, 700, 800
- **Base Size:** 16px
- **Line Height:** 1.6

### Components
- **Buttons:** Gradient, Outline, Icon
- **Cards:** Article, Temple, Festival, Lesson
- **Navigation:** Fixed navbar, Mobile menu
- **Modals:** Search modal, User dropdown
- **Sections:** Hero, Features, CTA

---

## 🚀 TÍNH NĂNG HIỆN TẠI

### ✅ Đã hoàn thành (Cập nhật: 30/11/2024)

#### 1. Trang chủ (index.php) - HOÀN THÀNH 100%
- **Hero Section:**
  - Tiêu đề gradient động với hiệu ứng text gradient
  - Call-to-action buttons với hover animation
  - Thống kê real-time (học viên, bài học, chùa, lễ hội) từ database
  
- **Features Section:**
  - 6 tính năng chính với icon
  - Hiệu ứng hover mượt mà
  - Link đến các trang chi tiết

- **Bài viết văn hóa nổi bật:**
  - Grid responsive
  - Badge "Nổi bật"
  - Hiển thị lượt xem, ngày đăng
  - Hover effect với scale image

- **Chùa Khmer nổi bật:**
  - Card với hình ảnh
  - Tên tiếng Việt & Khmer
  - Vị trí, loại chùa
  - Lượt xem

- **Lễ hội sắp diễn ra:**
  - Calendar badge với ngày tháng
  - Tên lễ hội Việt & Khmer
  - Địa điểm tổ chức

- **Bài học phổ biến:**
  - Badge cấp độ (Cơ bản, Trung cấp, Nâng cao)
  - Thời lượng học
  - Số học viên
  - CTA "Học ngay"

- **CTA Section:**
  - Gradient background
  - Buttons nổi bật
  - Kêu gọi hành động

#### 2. Navigation
- **Desktop Menu:**
  - Fixed navbar với backdrop blur
  - Active state cho trang hiện tại
  - Icon cho mỗi menu item
  - Search button
  - User menu dropdown (khi đăng nhập)
  - Login/Register buttons (khi chưa đăng nhập)

- **Mobile Menu:**
  - Hamburger toggle
  - Slide-in từ bên phải
  - Full-height overlay
  - Smooth transitions

- **Search Modal:**
  - Full-screen overlay
  - Focus vào input khi mở
  - AJAX search (sẵn sàng tích hợp)
  - Close on ESC key

#### 3. Footer
- **4 cột thông tin:**
  - Về chúng tôi + Social links
  - Liên kết nhanh
  - Tài nguyên
  - Thông tin liên hệ

- **Footer Bottom:**
  - Copyright
  - Credits

#### 4. JavaScript Features
- Mobile menu toggle
- Search modal
- User dropdown menu
- Back to top button
- Smooth scroll
- Scroll animations
- Navbar scroll effect
- Toast notifications
- Form validation helper
- AJAX helper
- Local storage helper

---

## 📊 DỮ LIỆU HIỂN THỊ

### Trang chủ lấy dữ liệu từ:
1. **Thống kê:**
   - `nguoi_dung` (người dùng hoạt động)
   - `chua_khmer` (chùa hoạt động)
   - `le_hoi` (lễ hội hiển thị)
   - `bai_hoc` (bài học xuất bản)
   - `van_hoa` (bài viết xuất bản)
   - `truyen_dan_gian` (truyện hiển thị)

2. **Bài viết nổi bật:**
   - Query: `van_hoa` WHERE `noi_bat = 1` AND `trang_thai = 'xuat_ban'`
   - Order: `luot_xem DESC`
   - Limit: 6

3. **Chùa nổi bật:**
   - Query: `chua_khmer` WHERE `trang_thai = 'hoat_dong'`
   - Order: `luot_xem DESC`
   - Limit: 4

4. **Lễ hội sắp diễn ra:**
   - Query: `le_hoi` WHERE `ngay_bat_dau >= CURDATE()`
   - Order: `ngay_bat_dau ASC`
   - Limit: 3

5. **Bài học phổ biến:**
   - Query: `bai_hoc` WHERE `trang_thai = 'xuat_ban'`
   - Order: `luot_hoc DESC`
   - Limit: 4

---

## 🎯 RESPONSIVE DESIGN

### Breakpoints
- **Desktop:** > 768px
- **Mobile:** ≤ 768px

### Mobile Optimizations
- Hamburger menu
- Stacked hero stats (2 columns)
- Single column grids
- Stacked CTA buttons
- Optimized font sizes
- Touch-friendly buttons

---

## 🔧 CÀI ĐẶT & SỬ DỤNG

### Yêu cầu
- PHP 8.0+
- MySQL 5.7+
- Apache/Nginx
- Database `van_hoa_khmer` đã được import

### Cài đặt

1. **Đảm bảo database đã sẵn sàng:**
```bash
# Import database từ Backend
mysql -u root -p van_hoa_khmer < ../Backend/csdl/van_hoa_khmer.sql
```

2. **Cấu hình database:**
Chỉnh sửa `config/database.php` nếu cần:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'van_hoa_khmer');
define('DB_USER', 'root');
define('DB_PASS', '');
```

3. **Truy cập trang web:**
```
http://localhost/WEB_VHKhmerNamBo/FrontendUser/
```

---

## 📝 CHUẨN CODE

### PHP
- PSR-12 coding standard
- Camel case cho biến
- Snake case cho database fields
- Comments rõ ràng
- Error handling với try-catch

### HTML
- Semantic HTML5
- ARIA labels khi cần
- Alt text cho images
- Proper heading hierarchy

### CSS
- BEM naming convention
- CSS Variables
- Mobile-first approach
- Flexbox & Grid
- Smooth transitions

### JavaScript
- ES6+ syntax
- Vanilla JS (no jQuery)
- Event delegation
- Async/await
- Error handling

---

## 🚧 ĐANG PHÁT TRIỂN

### Phase 1 (Đang làm)
- [ ] Trang đăng nhập/đăng ký
- [ ] Trang hồ sơ người dùng
- [ ] Trang danh sách văn hóa
- [ ] Trang chi tiết văn hóa
- [ ] Trang danh sách chùa
- [ ] Trang chi tiết chùa

### Phase 2 (Kế hoạch)
- [ ] Trang danh sách lễ hội
- [ ] Trang chi tiết lễ hội
- [ ] Trang học tiếng Khmer
- [ ] Trang chi tiết bài học
- [ ] Trang truyện dân gian
- [ ] Trang chi tiết truyện

### Phase 3 (Tương lai)
- [ ] Bản đồ di sản Khmer (Google Maps)
- [ ] Gamification UI (Leaderboard, Badges)
- [ ] Hệ thống bình luận
- [ ] Giao diện song ngữ (Việt-Khmer)
- [ ] PWA support
- [ ] AI Chatbot

---

## ✨ MỚI CẬP NHẬT

### Version 1.1 - 30/11/2024

**Đã hoàn thành:**
- ✅ Thiết kế lại toàn bộ UI với design system hiện đại
- ✅ Responsive hoàn chỉnh cho mobile và desktop
- ✅ Header với navigation menu đầy đủ
- ✅ Footer với 4 cột thông tin
- ✅ Hero section với gradient background và stats
- ✅ Features section với 6 tính năng chính
- ✅ Content sections: Văn hóa, Chùa, Lễ hội, Bài học
- ✅ Search modal với AJAX search
- ✅ Mobile menu với overlay
- ✅ Back to top button
- ✅ Scroll animations
- ✅ CSS Variables cho dễ customize
- ✅ Poppins & Inter fonts

**Cải tiến:**
- Modern UI với màu sắc theo 60-30-10 rule
- Card design với hover effects mượt mà
- Khoảng trắng hợp lý (24-40px)
- Shadow và border radius nhất quán
- Micro interactions trên buttons và cards

## 🐛 KNOWN ISSUES

1. **Image placeholders:** Cần thêm ảnh thực tế cho các content
2. **User authentication:** Chưa implement đăng nhập/đăng ký
3. **Loading states:** Cần thêm skeleton screens khi load data
4. **Error handling:** Cần thêm error pages (404, 403, 500)

---

## 📞 LIÊN HỆ

**Developer:** Lâm Nhật Hào  
**Email:** LamNhatHao@gmail.com  
**Phone:** 0337048780

---

## 📄 LICENSE

Copyright © 2024 Văn Hóa Khmer Nam Bộ  
All rights reserved.

---

**Built with ❤️ for Khmer Culture Preservation**
