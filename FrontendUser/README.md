# 🏛️ Frontend User - Văn hóa Khmer Nam Bộ

Giao diện người dùng cho nền tảng số hóa và bảo tồn văn hóa Khmer Nam Bộ.

## 📁 Cấu trúc thư mục

```
FrontendUser/
├── index.php                    # Trang chủ
├── login.php                    # Đăng nhập
├── register.php                 # Đăng ký
├── logout.php                   # Đăng xuất
├── forgot-password.php          # Quên mật khẩu
├── profile.php                  # Trang cá nhân
├── settings.php                 # Cài đặt
├── van-hoa.php                  # Danh sách văn hóa
├── van-hoa-chi-tiet.php         # Chi tiết văn hóa
├── chua-khmer.php               # Danh sách chùa
├── chua-khmer-chi-tiet.php      # Chi tiết chùa
├── le-hoi.php                   # Danh sách lễ hội
├── le-hoi-chi-tiet.php          # Chi tiết lễ hội
├── hoc-tieng-khmer.php          # Học tiếng Khmer
├── bai-hoc-chi-tiet.php         # Chi tiết bài học
├── truyen-dan-gian.php          # Truyện dân gian
├── truyen-chi-tiet.php          # Đọc truyện
├── ban-do.php                   # Bản đồ di sản
├── search.php                   # Tìm kiếm
├── leaderboard.php              # Bảng xếp hạng
├── api/                         # API endpoints
├── assets/                      # CSS, JS, Images
├── config/                      # Cấu hình
├── includes/                    # Header, Footer, Functions
└── models/                      # Models
```

## 🚀 Tính năng

### Phase 1: Tính năng cốt lõi ✅
- [x] Trang chủ với hero section, thống kê, features
- [x] Hệ thống Auth (đăng nhập, đăng ký, quên mật khẩu)
- [x] Profile & Dashboard người dùng
- [x] Trang Văn hóa Khmer (danh sách + chi tiết)
- [x] Trang Chùa Khmer (danh sách + chi tiết + bản đồ)
- [x] Trang Lễ hội (timeline + calendar view)
- [x] Học tiếng Khmer (bài học + từ vựng)
- [x] Truyện dân gian
- [x] Gamification (điểm, huy hiệu, bảng xếp hạng)
- [x] Tìm kiếm đa nội dung

### Phase 2: Tính năng nâng cao ✅
- [x] Bản đồ di sản (Leaflet.js + OpenStreetMap)
- [x] Đa ngôn ngữ (Việt ↔ Khmer)
- [x] Hệ thống bình luận
- [x] Responsive design

### Phase 3: Tính năng mở rộng ✅
- [x] AI Chatbot (placeholder - cần tích hợp API)

## 🛠️ Cài đặt

1. Copy thư mục `FrontendUser` vào web server (XAMPP, WAMP, etc.)
2. Cấu hình database trong `config/database.php`
3. Import database schema
4. Truy cập `http://localhost/FrontendUser`

## 🎨 Design System

### Colors
- Primary: `#667eea`
- Secondary: `#764ba2`
- Success: `#10b981`
- Warning: `#f59e0b`
- Danger: `#ef4444`

### Typography
- Font chính: Inter, Plus Jakarta Sans
- Font Khmer: Battambang, Kantumruy Pro

## 📱 Responsive

- Mobile: < 640px
- Tablet: 640px - 1024px
- Desktop: > 1024px

## 🔐 Bảo mật

- Password hashing (bcrypt)
- CSRF protection
- XSS prevention
- SQL injection prevention (PDO)

## 📄 License

© 2024 Văn hóa Khmer Nam Bộ
