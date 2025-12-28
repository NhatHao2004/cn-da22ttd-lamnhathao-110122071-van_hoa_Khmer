-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 27, 2025 lúc 11:10 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `van_hoa_khmer`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bai_hoc`
--

CREATE TABLE `bai_hoc` (
  `ma_bai_hoc` int(11) NOT NULL,
  `ma_danh_muc` int(11) DEFAULT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `tieu_de_khmer` varchar(255) DEFAULT NULL,
  `slug` varchar(300) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `noi_dung` longtext DEFAULT NULL,
  `cap_do` enum('co_ban','trung_cap','nang_cao') DEFAULT 'co_ban',
  `thu_tu` int(11) DEFAULT 0,
  `thoi_luong` int(11) DEFAULT 30 COMMENT 'Thời lượng học (phút)',
  `diem_thuong` int(11) DEFAULT 10,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `luot_xem` int(11) DEFAULT 0,
  `trang_thai` enum('hien_thi','an','xuat_ban') DEFAULT 'hien_thi',
  `ma_nguoi_tao` int(11) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng bài học tiếng Khmer';

--
-- Đang đổ dữ liệu cho bảng `bai_hoc`
--

INSERT INTO `bai_hoc` (`ma_bai_hoc`, `ma_danh_muc`, `tieu_de`, `tieu_de_khmer`, `slug`, `mo_ta`, `noi_dung`, `cap_do`, `thu_tu`, `thoi_luong`, `diem_thuong`, `hinh_anh`, `video_url`, `luot_xem`, `trang_thai`, `ma_nguoi_tao`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(46, 9, 'Truyền thống xuất gia niên thiếu trong Phật giáo Nam tông Khmer', NULL, 'truyen-thong-xuat-gia-nien-thieu-trong-phat-giao-nam-tong-khmer', 'Bài học giới thiệu truyền thống xuất gia niên thiếu của Phật giáo Nam tông Khmer, làm rõ ý nghĩa giáo dục đạo đức, tu báo hiếu và ảnh hưởng tích cực của truyền thống này đối với cộng đồng người Việt ở Nam Bộ.', '<h2>Giới thiệu</h2>\r\n<p>\r\nTrong quá trình đồng hành cùng dân tộc, Phật giáo Nam tông Khmer đã hình thành và gìn giữ nhiều truyền thống\r\nvăn hóa – giáo dục mang giá trị nhân văn sâu sắc. Một trong những truyền thống tiêu biểu là\r\n<strong>xuất gia niên thiếu</strong>, một hình thức tu học dành cho trẻ em và thanh thiếu niên tại các phum sóc Khmer.\r\n</p>\r\n\r\n<h2>Truyền thống xuất gia niên thiếu của người Khmer</h2>\r\n<p>\r\nTừ lâu, tại các cộng đồng Khmer Nam Bộ, việc đưa trẻ vào chùa tu học trong một khoảng thời gian nhất định\r\nđược xem là truyền thống thiêng liêng. Đây không chỉ là hành động mang ý nghĩa tôn giáo,\r\nmà còn là phương thức giáo dục đạo đức, nhân cách và lối sống cho thế hệ trẻ.\r\n</p>\r\n\r\n<h2>Sự lan tỏa sang cộng đồng người Việt</h2>\r\n<p>\r\nQua khảo sát thực tế, truyền thống xuất gia niên thiếu không chỉ tồn tại trong cộng đồng Khmer\r\nmà còn được người Việt ở Nam Bộ tiếp thu và thực hành như một <em>tập tục địa phương</em>.\r\nDần dần, tập tục này lan tỏa ra các vùng miền khác, góp phần ảnh hưởng đến đời sống Phật giáo người Việt,\r\ndù không phải là truyền thống nguyên gốc của Phật giáo Bắc tông.\r\n</p>\r\n\r\n<h2>Bốn ý nghĩa chính của xuất gia niên thiếu</h2>\r\n<ul>\r\n  <li><strong>Tu đền trả hiếu:</strong> Thể hiện lòng biết ơn và báo hiếu đối với cha mẹ, tổ tiên.</li>\r\n  <li><strong>Rèn luyện đạo đức:</strong> Giúp trẻ em học cách sống hướng thiện, biết kính trên nhường dưới.</li>\r\n  <li><strong>Học tập chữ nghĩa:</strong> Tiếp cận tri thức, chữ viết và giáo lý Phật giáo.</li>\r\n  <li><strong>Rèn luyện nếp sống tập thể:</strong> Hình thành tinh thần kỷ luật, đoàn kết và trách nhiệm.</li>\r\n</ul>\r\n\r\n<h2>Giá trị giáo dục và ý nghĩa xã hội</h2>\r\n<p>\r\nVới những ý nghĩa trên, truyền thống xuất gia niên thiếu của Phật giáo Nam tông Khmer\r\nđóng vai trò quan trọng trong việc hình thành nhân cách, đạo đức cho thanh thiếu niên.\r\nViệc học hỏi và kế thừa truyền thống này đã góp phần làm phong phú thêm đời sống văn hóa – tinh thần\r\ncủa cộng đồng người Việt, đặc biệt tại khu vực Nam Bộ.\r\n</p>\r\n\r\n<h2>Kết luận</h2>\r\n<p>\r\nXuất gia niên thiếu không chỉ là một truyền thống tôn giáo mà còn là một mô hình giáo dục nhân cách hiệu quả.\r\nĐây là minh chứng sinh động cho sự giao thoa văn hóa giữa các cộng đồng dân tộc,\r\ngóp phần xây dựng một xã hội nhân ái, hướng thiện và giàu bản sắc.\r\n</p>\r\n', 'co_ban', 0, 30, 20, 'lesson_1766827792_6658.jpg', 'https://www.youtube.com/watch?v=GHQxn6PuDHU', 0, 'hien_thi', NULL, '2025-12-27 09:29:52', '2025-12-27 09:30:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bai_viet_dien_dan`
--

CREATE TABLE `bai_viet_dien_dan` (
  `ma_bai_viet` int(11) NOT NULL,
  `ma_chu_de` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ma_bai_viet_cha` int(11) DEFAULT NULL,
  `noi_dung` longtext NOT NULL,
  `so_like` int(11) DEFAULT 0,
  `trang_thai` enum('hien_thi','cho_duyet','an') DEFAULT 'hien_thi',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bai_viet_nhom`
--

CREATE TABLE `bai_viet_nhom` (
  `ma_bai_viet` int(11) NOT NULL,
  `ma_nhom` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL COMMENT 'Người đăng',
  `tieu_de` varchar(500) DEFAULT NULL COMMENT 'Tiêu đề bài viết',
  `noi_dung` text NOT NULL COMMENT 'Nội dung bài viết',
  `hinh_anh` text DEFAULT NULL COMMENT 'Danh sách ảnh (JSON)',
  `tep_dinh_kem` text DEFAULT NULL COMMENT 'Danh sách file đính kèm (JSON)',
  `loai_bai_viet` enum('van_ban','hinh_anh','video','tai_lieu','thong_bao') DEFAULT 'van_ban',
  `ngay_dang` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `trang_thai` enum('hien_thi','an','cho_duyet','da_xoa') DEFAULT 'hien_thi',
  `so_luot_thich` int(11) DEFAULT 0,
  `so_binh_luan` int(11) DEFAULT 0,
  `so_chia_se` int(11) DEFAULT 0,
  `ghim_bai` tinyint(1) DEFAULT 0 COMMENT 'Ghim bài viết lên đầu',
  `anh_dinh_kem` text DEFAULT NULL COMMENT 'JSON array chứa tên file ảnh',
  `tai_lieu_dinh_kem` text DEFAULT NULL COMMENT 'JSON array chứa thông tin tài liệu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng bài viết trong nhóm';

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `binh_luan`
--

CREATE TABLE `binh_luan` (
  `ma_binh_luan` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `loai_noi_dung` enum('van_hoa','chua','le_hoi','bai_hoc','truyen','forum') NOT NULL,
  `ma_noi_dung` int(11) NOT NULL,
  `ma_binh_luan_cha` int(11) DEFAULT NULL,
  `noi_dung` text NOT NULL,
  `so_like` int(11) DEFAULT 0,
  `so_bao_cao` int(11) DEFAULT 0,
  `trang_thai` enum('hien_thi','cho_duyet','an','spam') DEFAULT 'hien_thi',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `binh_luan_bai_viet_nhom`
--

CREATE TABLE `binh_luan_bai_viet_nhom` (
  `ma_binh_luan` int(11) NOT NULL,
  `ma_bai_viet` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ma_binh_luan_cha` int(11) DEFAULT NULL COMMENT 'ID bình luận cha (cho reply)',
  `noi_dung` text NOT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `ngay_binh_luan` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `trang_thai` enum('hien_thi','an','da_xoa') DEFAULT 'hien_thi',
  `so_luot_thich` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng bình luận bài viết nhóm';

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `binh_luan_dien_dan`
--

CREATE TABLE `binh_luan_dien_dan` (
  `ma_binh_luan` int(11) NOT NULL,
  `ma_chu_de` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `ma_binh_luan_cha` int(11) DEFAULT NULL,
  `trang_thai` enum('hien_thi','an','spam') DEFAULT 'hien_thi',
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `binh_luan_dien_dan`
--

INSERT INTO `binh_luan_dien_dan` (`ma_binh_luan`, `ma_chu_de`, `ma_nguoi_dung`, `noi_dung`, `hinh_anh`, `ma_binh_luan_cha`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(12, 33, 34, 'Về chủ đề này tôi thấy nó rất bổ ít', NULL, NULL, 'hien_thi', '2025-12-27 17:08:02', '2025-12-27 17:08:02'),
(13, 33, 35, 'Tôi cũng thấy bổ ít giống như bạn', NULL, 12, 'hien_thi', '2025-12-27 17:09:28', '2025-12-27 17:09:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cau_hoi_quiz`
--

CREATE TABLE `cau_hoi_quiz` (
  `ma_cau_hoi` int(11) NOT NULL,
  `ma_quiz` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `noi_dung_khmer` text DEFAULT NULL,
  `thu_tu` int(11) DEFAULT 1,
  `diem` int(11) DEFAULT 10,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `giai_thich` text DEFAULT NULL COMMENT 'Giải thích đáp án',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng câu hỏi thống nhất - gộp từ tất cả bảng cau_hoi_quiz_*';

--
-- Đang đổ dữ liệu cho bảng `cau_hoi_quiz`
--

INSERT INTO `cau_hoi_quiz` (`ma_cau_hoi`, `ma_quiz`, `noi_dung`, `noi_dung_khmer`, `thu_tu`, `diem`, `hinh_anh`, `giai_thich`, `ngay_tao`) VALUES
(24, 17, 'Trang phục truyền thống của người Khmer Nam Bộ phản ánh rõ nhất yếu tố nào sau đây?', NULL, 1, 10, NULL, 'Trang phục Khmer Nam Bộ không chỉ phục vụ sinh hoạt mà còn thể hiện tín ngưỡng, quan niệm thẩm mỹ và lối sống giản dị, hài hòa với thiên nhiên của cộng đồng người Khmer.', '2025-12-26 15:35:29'),
(25, 17, 'Trang phục phổ biến của nam giới Khmer Nam Bộ trong sinh hoạt hằng ngày là:', NULL, 2, 10, NULL, 'Nam giới Khmer thường mặc áo bà ba hoặc áo ngắn cùng với xà-rông, vừa tiện lợi cho lao động, vừa phù hợp với điều kiện khí hậu nóng ẩm của Nam Bộ.', '2025-12-26 15:35:29'),
(26, 17, 'Hoa văn và màu sắc trên trang phục Khmer Nam Bộ thường mang ý nghĩa gì?', NULL, 3, 10, NULL, 'Hoa văn trên trang phục Khmer thường lấy cảm hứng từ thiên nhiên và tín ngưỡng, còn màu sắc như vàng, đỏ, xanh, tím tượng trưng cho sự may mắn, bình an và cuộc sống ấm no.', '2025-12-26 15:35:29'),
(27, 17, 'Trang phục truyền thống của người Khmer Nam Bộ thường được sử dụng nhiều nhất trong những dịp nào?', NULL, 4, 10, NULL, 'Trang phục truyền thống của người Khmer Nam Bộ vừa được sử dụng trong đời sống hằng ngày, vừa giữ vai trò quan trọng trong các lễ hội như Chôl Chnăm Thmây, Sen Dolta, Ok Om Bok, góp phần gìn giữ và thể hiện bản sắc văn hóa dân tộc.', '2025-12-26 15:35:29'),
(28, 17, 'Việc bảo tồn và phát huy trang phục truyền thống của người Khmer Nam Bộ hiện nay có ý nghĩa chủ yếu nào sau đây?', NULL, 5, 10, NULL, 'Bảo tồn trang phục truyền thống giúp gìn giữ bản sắc văn hóa Khmer Nam Bộ, đồng thời tăng cường sự gắn kết cộng đồng và làm phong phú thêm nền văn hóa đa dạng của Việt Nam, không chỉ giới hạn trong du lịch hay một nhóm tuổi nhất định.', '2025-12-26 15:36:35'),
(29, 18, 'Chùa Âng (Trà Vinh) còn có tên tiếng Khmer là gì?', NULL, 1, 10, NULL, 'Tên tiếng Khmer của Chùa Âng là Wat Angkorajaborey (វត្តអង្គររាជបុរី), phản ánh nguồn gốc và truyền thống lâu đời của chùa trong cộng đồng người Khmer.', '2025-12-26 15:48:03'),
(30, 18, 'Chùa Âng thuộc hệ phái Phật giáo nào?', NULL, 2, 10, NULL, 'Chùa Âng là ngôi chùa Khmer tiêu biểu, thuộc Phật giáo Nam tông (Theravada) – hệ phái phổ biến trong cộng đồng người Khmer Nam Bộ.', '2025-12-26 15:48:03'),
(31, 18, 'Theo lịch sử, Chùa Âng được xây dựng vào khoảng thời gian nào?', NULL, 3, 10, NULL, 'Chùa Âng được xây dựng vào khoảng năm 990, là một trong những ngôi chùa Khmer cổ nhất tại tỉnh Trà Vinh.', '2025-12-26 15:48:03'),
(32, 18, 'Vai trò nổi bật của Chùa Âng đối với cộng đồng người Khmer Nam Bộ là gì?', NULL, 4, 10, NULL, 'Chùa Âng không chỉ là nơi sinh hoạt tôn giáo mà còn là trung tâm văn hóa – giáo dục, nơi truyền dạy đạo đức, chữ Khmer và tổ chức các lễ hội truyền thống.', '2025-12-26 15:48:03'),
(33, 18, 'Lễ hội nào sau đây thường được tổ chức tại Chùa Âng?', NULL, 5, 10, NULL, 'Chôl Chnăm Thmây là Tết cổ truyền của người Khmer và là một trong những lễ hội lớn được tổ chức tại Chùa Âng cùng với Sen Dolta và Ok Om Bok.', '2025-12-26 15:48:03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chatbot_history`
--

CREATE TABLE `chatbot_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `sender` enum('user','bot') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chua_khmer`
--

CREATE TABLE `chua_khmer` (
  `ma_chua` int(11) NOT NULL,
  `ten_chua` varchar(200) NOT NULL,
  `ten_tieng_khmer` varchar(200) DEFAULT NULL,
  `slug` varchar(250) NOT NULL,
  `dia_chi` varchar(255) DEFAULT NULL,
  `tinh_thanh` varchar(100) DEFAULT NULL,
  `quan_huyen` varchar(100) DEFAULT NULL,
  `kinh_do` decimal(10,6) DEFAULT NULL,
  `vi_do` decimal(10,6) DEFAULT NULL,
  `loai_chua` enum('Theravada','Mahayana','Vajrayana') DEFAULT 'Theravada',
  `so_dien_thoai` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `mo_ta_ngan` text DEFAULT NULL,
  `lich_su` longtext DEFAULT NULL,
  `hinh_anh_chinh` varchar(255) DEFAULT NULL,
  `thu_vien_anh` text DEFAULT NULL,
  `nam_thanh_lap` int(11) DEFAULT NULL,
  `so_nha_su` int(11) DEFAULT 0,
  `luot_xem` int(11) DEFAULT 0,
  `trang_thai` enum('hoat_dong','ngung_hoat_dong') DEFAULT 'hoat_dong',
  `ma_nguoi_tao` int(11) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chua_khmer`
--

INSERT INTO `chua_khmer` (`ma_chua`, `ten_chua`, `ten_tieng_khmer`, `slug`, `dia_chi`, `tinh_thanh`, `quan_huyen`, `kinh_do`, `vi_do`, `loai_chua`, `so_dien_thoai`, `email`, `website`, `mo_ta_ngan`, `lich_su`, `hinh_anh_chinh`, `thu_vien_anh`, `nam_thanh_lap`, `so_nha_su`, `luot_xem`, `trang_thai`, `ma_nguoi_tao`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(10, 'Chùa Âng', 'វត្តអង្គររាជបុរី', 'chua-ang-1766765697', 'Khóm 4, phường 8, thành phố Trà Vinh, tỉnh Trà Vinh', 'Trà Vinh', 'Thành phố Trà Vinh', 106.303550, 9.915831, 'Theravada', '0337048780', 'ChuaAnh@gmail.com', 'https://vi.wikipedia.org/wiki/Chùa_Âng', '<h2>Giới thiệu chung</h2>\r\n<p>\r\n<strong>Chùa Âng</strong> là một trong những ngôi chùa Khmer cổ kính và tiêu biểu nhất \r\ncủa tỉnh Trà Vinh. Chùa không chỉ là nơi sinh hoạt tôn giáo của cộng đồng người Khmer, \r\nmà còn là trung tâm văn hóa, giáo dục và gìn giữ bản sắc dân tộc Khmer Nam Bộ.\r\n</p>\r\n<p>\r\nVới kiến trúc đặc trưng Phật giáo Nam tông Khmer, không gian thanh tịnh và cảnh quan xanh mát, \r\nchùa Âng là điểm đến tâm linh và du lịch văn hóa nổi bật của Trà Vinh.\r\n</p>\r\n', '<h2>Lịch sử hình thành và phát triển</h2>\r\n<p>\r\nChùa Âng, tên tiếng Khmer là <strong>Wat Angkorajaborey</strong>, được xây dựng vào khoảng \r\nnăm <strong>990</strong>, gắn liền với lịch sử hình thành và phát triển lâu đời \r\ncủa cộng đồng người Khmer tại Trà Vinh.\r\n</p>\r\n\r\n<p>\r\nTrải qua nhiều thế kỷ, chùa Âng đã được trùng tu và tôn tạo nhiều lần nhưng vẫn giữ được \r\nnhững nét kiến trúc truyền thống đặc trưng của Phật giáo Nam tông Khmer. \r\nChùa không chỉ là nơi tu hành của các nhà sư mà còn là trung tâm sinh hoạt văn hóa, \r\nnơi tổ chức các lễ hội lớn như <em>Chôl Chnăm Thmây</em>, <em>Sen Dolta</em> và <em>Ok Om Bok</em>.\r\n</p>\r\n\r\n<p>\r\nTrong lịch sử, chùa Âng còn đóng vai trò quan trọng trong việc \r\n<strong>giáo dục đạo đức, truyền dạy chữ Khmer</strong> và bảo tồn các giá trị văn hóa truyền thống. \r\nNgày nay, chùa là biểu tượng tiêu biểu cho đời sống tâm linh của người Khmer Nam Bộ \r\nvà là điểm nhấn văn hóa – du lịch của tỉnh Trà Vinh.\r\n</p>\r\n', 'uploads/chua/20251226_171457_694eb481be53b.jpg', NULL, 1000, 0, 8, 'hoat_dong', 2, '2025-12-26 09:42:27', '2025-12-26 16:15:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chu_de_thao_luan`
--

CREATE TABLE `chu_de_thao_luan` (
  `ma_chu_de` int(11) NOT NULL,
  `ma_danh_muc` int(11) NOT NULL,
  `ma_nguoi_tao` int(11) NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `noi_dung` longtext NOT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `luot_xem` int(11) DEFAULT 0,
  `so_tra_loi` int(11) DEFAULT 0,
  `ghim` tinyint(1) DEFAULT 0,
  `khoa` tinyint(1) DEFAULT 0,
  `trang_thai` enum('mo','khoa','an','hien_thi') DEFAULT 'mo',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `hoat_dong_cuoi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chu_de_thao_luan`
--

INSERT INTO `chu_de_thao_luan` (`ma_chu_de`, `ma_danh_muc`, `ma_nguoi_tao`, `tieu_de`, `slug`, `noi_dung`, `hinh_anh`, `luot_xem`, `so_tra_loi`, `ghim`, `khoa`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`, `hoat_dong_cuoi`) VALUES
(33, 1, 34, 'Chùa Khmer trong đời sống văn hóa cộng đồng Nam Bộ', 'vai-tro-cua-chua-khmer-trong-doi-song-van-hoa-cong-dong-nam-bo-1766828179', '<p><strong>\r\nChùa Khmer không chỉ là nơi sinh hoạt tôn giáo mà còn giữ vai trò quan trọng như một trung tâm văn hóa,\r\ngiáo dục và gắn kết cộng đồng của người Khmer Nam Bộ qua nhiều thế hệ.\r\n</strong></p>\r\n\r\n<p><strong>\r\nBên cạnh các hoạt động tín ngưỡng, chùa Khmer còn là nơi truyền dạy đạo đức, chữ viết,\r\nngôn ngữ và các giá trị truyền thống cho thanh thiếu niên, góp phần bảo tồn bản sắc dân tộc.\r\n</strong></p>\r\n\r\n<p><strong>\r\nMình mong muốn cùng mọi người thảo luận về vai trò của chùa Khmer trong xã hội hiện đại,\r\nnhững thay đổi hiện nay và cách gìn giữ, phát huy các giá trị văn hóa truyền thống trong tương lai.\r\n</strong></p>', '694fa8932817c_1766828179.jpeg', 16, 0, 0, 0, 'mo', '2025-12-27 09:36:19', '2025-12-27 10:09:45', '2025-12-27 09:36:19');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_muc`
--

CREATE TABLE `danh_muc` (
  `ma_danh_muc` int(11) NOT NULL,
  `ten_danh_muc` varchar(100) NOT NULL,
  `ten_danh_muc_khmer` varchar(100) DEFAULT NULL,
  `slug` varchar(150) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `loai` enum('bai_hoc','van_hoa','le_hoi','truyen') DEFAULT 'van_hoa',
  `thu_tu` int(11) DEFAULT 0,
  `trang_thai` enum('hien_thi','an') DEFAULT 'hien_thi',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc`
--

INSERT INTO `danh_muc` (`ma_danh_muc`, `ten_danh_muc`, `ten_danh_muc_khmer`, `slug`, `mo_ta`, `loai`, `thu_tu`, `trang_thai`, `ngay_tao`) VALUES
(1, 'Phong tục tập quán', 'ទំនៀមទម្លាប់', 'phong-tuc-tap-quan', NULL, 'van_hoa', 1, 'hien_thi', '2025-12-20 08:20:46'),
(2, 'Ẩm thực', 'ម្ហូបអាហារ', 'am-thuc', NULL, 'van_hoa', 2, 'hien_thi', '2025-12-20 08:20:46'),
(3, 'Nghệ thuật', 'សិល្បៈ', 'nghe-thuat', NULL, 'van_hoa', 3, 'hien_thi', '2025-12-20 08:20:46'),
(4, 'Trang phục', 'សម្លៀកបំពាក់', 'trang-phuc', NULL, 'van_hoa', 4, 'hien_thi', '2025-12-20 08:20:46'),
(5, 'Truyện cổ tích', 'រឿងព្រេងនិទាន', 'truyen-co-tich', NULL, 'truyen', 1, 'hien_thi', '2025-12-20 08:20:46'),
(6, 'Truyền thuyết', 'រឿងព្រេង', 'truyen-thuyet', NULL, 'truyen', 2, 'hien_thi', '2025-12-20 08:20:46'),
(7, 'Bảng chữ cái', 'អក្សរក្រម', 'bang-chu-cai', NULL, 'bai_hoc', 1, 'hien_thi', '2025-12-20 08:38:02'),
(8, 'Từ vựng cơ bản', 'វាក្យសព្ទមូលដ្ឋាន', 'tu-vung-co-ban', NULL, 'bai_hoc', 2, 'hien_thi', '2025-12-20 08:38:02'),
(9, 'Ngữ pháp', 'វេយ្យាករណ៍', 'ngu-phap', NULL, 'bai_hoc', 3, 'hien_thi', '2025-12-20 08:38:02'),
(10, 'Hội thoại', 'សន្ទនា', 'hoi-thoai', NULL, 'bai_hoc', 4, 'hien_thi', '2025-12-20 08:38:02'),
(11, 'Truyện dân gian', 'រឿងប្រជាប្រិយ', 'truyen-dan-gian', 'Truyện dân gian được truyền miệng qua các thế hệ', 'truyen', 3, 'hien_thi', '2025-12-20 08:46:39'),
(12, 'Thần thoại', 'រឿងទេវកថា', 'than-thoai', 'Những câu chuyện thần thoại về các vị thần và anh hùng', 'truyen', 4, 'hien_thi', '2025-12-20 08:46:39');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_muc_bai_hoc`
--

CREATE TABLE `danh_muc_bai_hoc` (
  `ma_danh_muc` int(11) NOT NULL,
  `ten_danh_muc` varchar(255) NOT NULL,
  `ten_danh_muc_khmer` varchar(255) DEFAULT NULL,
  `slug` varchar(300) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `mau_sac` varchar(50) DEFAULT '#667eea',
  `thu_tu` int(11) DEFAULT 0,
  `trang_thai` enum('hien_thi','an') DEFAULT 'hien_thi',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng danh mục bài học';

--
-- Đang đổ dữ liệu cho bảng `danh_muc_bai_hoc`
--

INSERT INTO `danh_muc_bai_hoc` (`ma_danh_muc`, `ten_danh_muc`, `ten_danh_muc_khmer`, `slug`, `mo_ta`, `hinh_anh`, `icon`, `mau_sac`, `thu_tu`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 'Bảng chữ cái', 'អក្សរក្រម', 'bang-chu-cai', 'Học bảng chữ cái tiếng Khmer', NULL, 'fa-font', '#667eea', 1, 'hien_thi', '2025-12-26 05:24:47', NULL),
(2, 'Số đếm', 'លេខ', 'so-dem', 'Học đếm số bằng tiếng Khmer', NULL, 'fa-calculator', '#10b981', 2, 'hien_thi', '2025-12-26 05:24:47', NULL),
(3, 'Giao tiếp cơ bản', 'ការទំនាក់ទំនងមូលដ្ឋាន', 'giao-tiep-co-ban', 'Các câu giao tiếp hàng ngày', NULL, 'fa-comments', '#f59e0b', 3, 'hien_thi', '2025-12-26 05:24:47', NULL),
(4, 'Gia đình', 'គ្រួសារ', 'gia-dinh', 'Từ vựng về gia đình', NULL, 'fa-users', '#ec4899', 4, 'hien_thi', '2025-12-26 05:24:47', NULL),
(5, 'Màu sắc', 'ពណ៌', 'mau-sac', 'Học tên các màu sắc', NULL, 'fa-palette', '#8b5cf6', 5, 'hien_thi', '2025-12-26 05:24:47', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_muc_dien_dan`
--

CREATE TABLE `danh_muc_dien_dan` (
  `ma_danh_muc` int(11) NOT NULL,
  `ten_danh_muc` varchar(100) NOT NULL,
  `ten_danh_muc_km` varchar(100) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `mo_ta_km` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fas fa-comments',
  `mau_sac` varchar(20) DEFAULT '#667eea',
  `thu_tu` int(11) DEFAULT 0,
  `trang_thai` enum('hien_thi','an') DEFAULT 'hien_thi',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc_dien_dan`
--

INSERT INTO `danh_muc_dien_dan` (`ma_danh_muc`, `ten_danh_muc`, `ten_danh_muc_km`, `mo_ta`, `mo_ta_km`, `icon`, `mau_sac`, `thu_tu`, `trang_thai`, `ngay_tao`) VALUES
(1, 'Văn hóa Khmer', 'វប្បធម៌ខ្មែរ', 'Thảo luận về phong tục, nghệ thuật', 'ពិភាក្សាអំពីទំនៀមទម្លាប់ សិល្បៈវប្បធម៌ខ្មែរ', 'fas fa-landmark', '#667eea', 1, 'hien_thi', '2025-12-20 08:20:46'),
(2, 'Học tiếng Khmer', 'រៀនភាសាខ្មែរ', 'Hỏi đáp, chia sẻ kinh nghiệm học tập', 'សំណួរ និងចែករំលែកបទពិសោធន៍រៀនភាសាខ្មែរ', 'fas fa-graduation-cap', '#10b981', 2, 'hien_thi', '2025-12-20 08:20:46'),
(3, 'Chùa và Lễ hội', 'វត្ត និងពិធីបុណ្យ', 'Thông tin về chùa Khmer và các lễ hội', 'ព័ត៌មានអំពីវត្តខ្មែរ និងពិធីបុណ្យប្រពៃណី', 'fas fa-place-of-worship', '#f59e0b', 3, 'hien_thi', '2025-12-20 08:20:46'),
(4, 'Chia sẻ tài liệu', 'ចែករំលែកឯកសារ', 'Chia sẻ sách, tài liệu, video học tập', 'ចែករំលែកសៀវភៅ ឯកសារ វីដេអូសិក្សា', 'fas fa-book', '#ec4899', 4, 'hien_thi', '2025-12-20 08:20:46'),
(5, 'Góc hỏi đáp', 'កន្លែងសំណួរ', 'Đặt câu hỏi và nhận giải đáp', 'សួរសំណួរ និងទទួលចម្លើយពីសហគមន៍', 'fas fa-question-circle', '#8b5cf6', 5, 'hien_thi', '2025-12-20 08:20:46');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dap_an_quiz`
--

CREATE TABLE `dap_an_quiz` (
  `ma_dap_an` int(11) NOT NULL,
  `ma_cau_hoi` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `la_dap_an_dung` tinyint(1) DEFAULT 0,
  `thu_tu` int(11) DEFAULT 1,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng đáp án thống nhất - gộp từ tất cả bảng dap_an_quiz_*';

--
-- Đang đổ dữ liệu cho bảng `dap_an_quiz`
--

INSERT INTO `dap_an_quiz` (`ma_dap_an`, `ma_cau_hoi`, `noi_dung`, `la_dap_an_dung`, `thu_tu`, `ngay_tao`) VALUES
(93, 24, 'Sự giao thoa với văn hóa phương Tây', 0, 1, '2025-12-26 15:35:29'),
(94, 24, 'Điều kiện khí hậu lạnh quanh năm', 0, 2, '2025-12-26 15:35:29'),
(95, 24, 'Tín ngưỡng, thẩm mỹ và lối sống gắn bó với thiên nhiên', 1, 3, '2025-12-26 15:35:29'),
(96, 24, 'Xu hướng thời trang hiện đại', 0, 4, '2025-12-26 15:35:29'),
(97, 25, 'Áo dài và quần tây', 0, 1, '2025-12-26 15:35:29'),
(98, 25, 'Áo bà ba kết hợp với xà-rông', 1, 2, '2025-12-26 15:35:29'),
(99, 25, 'Áo Vest truyền thống', 0, 3, '2025-12-26 15:35:29'),
(100, 25, 'Áo chẽn và quần bó', 0, 4, '2025-12-26 15:35:29'),
(101, 26, 'Chỉ dùng để trang trí cho đẹp', 0, 1, '2025-12-26 15:35:29'),
(102, 26, 'Thể hiện địa vị xã hội của người mặc', 0, 2, '2025-12-26 15:35:29'),
(103, 26, 'Phục vụ cho mục đích thương mại', 0, 3, '2025-12-26 15:35:29'),
(104, 26, 'Mang ý nghĩa tâm linh, biểu trưng cho may mắn và hạnh phúc', 1, 4, '2025-12-26 15:35:29'),
(105, 27, 'Sinh hoạt thường ngày và các lễ hội truyền thống', 1, 1, '2025-12-26 15:35:29'),
(106, 27, 'Các buổi họp hành hành chính', 0, 2, '2025-12-26 15:35:29'),
(107, 27, 'Hoạt động thương mại và buôn bán', 0, 3, '2025-12-26 15:35:29'),
(108, 27, 'Các sự kiện thể thao hiện đại', 0, 4, '2025-12-26 15:35:29'),
(109, 28, 'Chỉ nhằm phục vụ hoạt động du lịch', 0, 1, '2025-12-26 15:36:35'),
(110, 28, 'Góp phần giữ gìn bản sắc văn hóa và tăng cường đoàn kết cộng đồng', 1, 2, '2025-12-26 15:36:35'),
(111, 28, 'Thay thế hoàn toàn trang phục hiện đại', 0, 3, '2025-12-26 15:36:35'),
(112, 28, 'Chỉ dành cho người cao tuổi trong cộng đồng', 0, 4, '2025-12-26 15:36:35'),
(113, 29, 'Wat Mahatup', 0, 1, '2025-12-26 15:48:03'),
(114, 29, 'Wat Angkorajaborey', 1, 2, '2025-12-26 15:48:03'),
(115, 29, 'Wat Kompong', 0, 3, '2025-12-26 15:48:03'),
(116, 29, 'Wat Serey', 0, 4, '2025-12-26 15:48:03'),
(117, 30, 'Phật giáo Bắc tông', 0, 1, '2025-12-26 15:48:03'),
(118, 30, 'Phật giáo Hòa Hảo', 0, 2, '2025-12-26 15:48:03'),
(119, 30, 'Phật giáo Nam tông (Theravada)', 1, 3, '2025-12-26 15:48:03'),
(120, 30, 'Phật giáo Đại thừa Tây Tạng', 0, 4, '2025-12-26 15:48:03'),
(121, 31, 'Thế kỷ XIX', 0, 1, '2025-12-26 15:48:03'),
(122, 31, 'Năm 1850', 0, 2, '2025-12-26 15:48:03'),
(123, 31, 'Đầu thế kỷ XXI', 0, 3, '2025-12-26 15:48:03'),
(124, 31, 'Khoảng năm 990', 1, 4, '2025-12-26 15:48:03'),
(125, 32, 'Chỉ là điểm tham quan du lịch', 0, 1, '2025-12-26 15:48:03'),
(126, 32, 'Trung tâm mua bán, trao đổi hàng hóa', 0, 2, '2025-12-26 15:48:03'),
(127, 32, 'Trung tâm tôn giáo, văn hóa và giáo dục truyền thống', 1, 3, '2025-12-26 15:48:03'),
(128, 32, 'Nơi tổ chức các hoạt động thể thao', 0, 4, '2025-12-26 15:48:03'),
(129, 33, 'Tết Trung thu', 1, 1, '2025-12-26 15:48:03'),
(130, 33, 'Chôl Chnăm Thmây', 0, 2, '2025-12-26 15:48:03'),
(131, 33, 'Lễ Giáng sinh', 0, 3, '2025-12-26 15:48:03'),
(132, 33, 'Tết Nguyên đán', 0, 4, '2025-12-26 15:48:03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `huy_hieu`
--

CREATE TABLE `huy_hieu` (
  `ma_huy_hieu` int(11) NOT NULL,
  `ten_huy_hieu` varchar(100) NOT NULL,
  `ten_huy_hieu_khmer` varchar(100) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `dieu_kien` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `mau_sac` varchar(50) DEFAULT NULL,
  `diem_thuong` int(11) DEFAULT 0,
  `thu_tu` int(11) DEFAULT 0,
  `trang_thai` enum('hoat_dong','khong_hoat_dong') DEFAULT 'hoat_dong',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `huy_hieu`
--

INSERT INTO `huy_hieu` (`ma_huy_hieu`, `ten_huy_hieu`, `ten_huy_hieu_khmer`, `mo_ta`, `dieu_kien`, `icon`, `mau_sac`, `diem_thuong`, `thu_tu`, `trang_thai`, `ngay_tao`) VALUES
(1, 'Người mới bắt đầu', 'អ្នកចាប់ផ្តើម', 'Hoàn thành bài học đầu tiên', 'Hoàn thành 1 bài học', 'fa-star', '#fbbf24', 10, 1, 'hoat_dong', '2025-12-20 08:20:46'),
(2, 'Siêng năng', 'ឧស្សាហ៍', 'Học 7 ngày liên tiếp', 'Học liên tục 7 ngày', 'fa-fire', '#ef4444', 50, 2, 'hoat_dong', '2025-12-20 08:20:46'),
(3, 'Học giỏi', 'សិក្សាល្អ', 'Hoàn thành 10 bài học', 'Hoàn thành 10 bài học', 'fa-graduation-cap', '#3b82f6', 100, 3, 'hoat_dong', '2025-12-20 08:20:46'),
(4, 'Quiz Master', 'ម្ចាស់ Quiz', 'Đạt điểm tuyệt đối trong một bài quiz', 'Đạt 100 điểm', 'fa-trophy', '#fbbf24', 50, 4, 'hoat_dong', '2025-12-20 08:20:46'),
(5, 'Người yêu Quiz', 'អ្នកស្រលាញ់ Quiz', 'Hoàn thành 5 bài quiz', 'Hoàn thành 5 quiz', 'fa-clipboard-check', '#10b981', 30, 5, 'hoat_dong', '2025-12-20 08:20:46'),
(9, 'Bậc Thầy Quiz', NULL, 'Đạt điểm tuyệt đối (100%) trong một bài quiz', NULL, 'fa-star', '#fbbf24', 50, 0, 'hoat_dong', '2025-12-26 02:10:03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `huy_hieu_nguoi_dung`
--

CREATE TABLE `huy_hieu_nguoi_dung` (
  `ma_hh_nguoi_dung` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ma_huy_hieu` int(11) NOT NULL,
  `ngay_dat_duoc` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ket_qua_quiz`
--

CREATE TABLE `ket_qua_quiz` (
  `ma_ket_qua` int(11) NOT NULL,
  `ma_quiz` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `diem` decimal(5,2) DEFAULT 0.00,
  `so_cau_dung` int(11) DEFAULT 0,
  `so_cau_sai` int(11) DEFAULT 0,
  `tong_so_cau` int(11) DEFAULT 0,
  `thoi_gian_lam_bai` int(11) DEFAULT 0 COMMENT 'Thời gian làm bài (giây)',
  `trang_thai` enum('dang_lam','hoan_thanh','het_gio') DEFAULT 'hoan_thanh',
  `chi_tiet_tra_loi` longtext DEFAULT NULL COMMENT 'Chi tiết câu trả lời (JSON)',
  `ngay_lam_bai` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng kết quả thống nhất - gộp từ tất cả bảng ket_qua_quiz_* và ket_qua_kiem_tra';

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ket_qua_quiz_chua`
--

CREATE TABLE `ket_qua_quiz_chua` (
  `ma_ket_qua` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ma_chua` int(11) NOT NULL,
  `tong_so_cau` int(11) NOT NULL DEFAULT 10,
  `so_cau_dung` int(11) NOT NULL DEFAULT 0,
  `diem` int(11) NOT NULL DEFAULT 0,
  `thoi_gian_lam_bai` int(11) DEFAULT NULL COMMENT 'Thời gian làm bài (giây)',
  `chi_tiet_cau_tra_loi` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`chi_tiet_cau_tra_loi`)),
  `ngay_lam_bai` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng kết quả quiz về chùa Khmer';

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `le_hoi`
--

CREATE TABLE `le_hoi` (
  `ma_le_hoi` int(11) NOT NULL,
  `ten_le_hoi` varchar(200) NOT NULL,
  `ten_le_hoi_khmer` varchar(200) DEFAULT NULL,
  `slug` varchar(250) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `noi_dung` longtext DEFAULT NULL,
  `ngay_bat_dau` date DEFAULT NULL,
  `ngay_ket_thuc` date DEFAULT NULL,
  `ngay_dien_ra` varchar(100) DEFAULT NULL,
  `dia_diem` varchar(255) DEFAULT NULL,
  `tinh_thanh` varchar(100) DEFAULT NULL,
  `anh_dai_dien` varchar(255) DEFAULT NULL,
  `thu_vien_anh` text DEFAULT NULL,
  `y_nghia` text DEFAULT NULL,
  `nguon_goc` text DEFAULT NULL,
  `loai_le_hoi` enum('ton_giao','van_hoa','the_thao','khac') DEFAULT 'ton_giao',
  `luot_xem` int(11) DEFAULT 0,
  `trang_thai` enum('hien_thi','an') DEFAULT 'hien_thi',
  `ma_nguoi_tao` int(11) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `le_hoi`
--

INSERT INTO `le_hoi` (`ma_le_hoi`, `ten_le_hoi`, `ten_le_hoi_khmer`, `slug`, `mo_ta`, `noi_dung`, `ngay_bat_dau`, `ngay_ket_thuc`, `ngay_dien_ra`, `dia_diem`, `tinh_thanh`, `anh_dai_dien`, `thu_vien_anh`, `y_nghia`, `nguon_goc`, `loai_le_hoi`, `luot_xem`, `trang_thai`, `ma_nguoi_tao`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(8, 'Lễ hội Ok Om Bok (Lễ cúng Trăng)', 'ពិធីបុណ្យអុំទូក និងសំពះព្រះខែ', 'le-hoi-ok-om-bok-le-cung-trang-1766765714', '<h2>Giới thiệu lễ hội</h2>\r\n<p>\r\n<strong>Lễ hội Ok Om Bok</strong>, còn gọi là <em>Lễ cúng Trăng</em>, \r\nlà một trong những lễ hội truyền thống quan trọng nhất của đồng bào Khmer Nam Bộ. \r\nLễ hội thể hiện lòng biết ơn đối với Mặt Trăng – vị thần được tin là đã ban cho \r\nmùa màng bội thu, cuộc sống ấm no và bình an.\r\n</p>\r\n\r\n<h2>Ý nghĩa văn hóa – tín ngưỡng</h2>\r\n<p>\r\nTheo quan niệm của người Khmer, Mặt Trăng có vai trò quan trọng trong đời sống nông nghiệp. \r\nLễ Ok Om Bok được tổ chức sau mùa thu hoạch nhằm tạ ơn thần linh, \r\nđồng thời cầu mong mưa thuận gió hòa, mùa màng tốt tươi cho năm sau.\r\n</p>\r\n\r\n<h2>Các nghi lễ chính</h2>\r\n<p>\r\nNghi lễ cúng Trăng thường diễn ra vào buổi tối với các lễ vật truyền thống \r\nnhư <strong>cốm dẹp, chuối, khoai, dừa, mía</strong>. \r\nNgười lớn tuổi sẽ đọc lời khấn, sau đó cho trẻ em ăn cốm dẹp với niềm tin \r\nmang lại sức khỏe và may mắn.\r\n</p>\r\n\r\n<h2>Hoạt động lễ hội đặc sắc</h2>\r\n<ul>\r\n  <li><strong>Đua ghe Ngo</strong> – hoạt động sôi nổi, thu hút đông đảo người dân và du khách</li>\r\n  <li><strong>Thả đèn nước, đèn gió</strong> – cầu mong bình an và hạnh phúc</li>\r\n  <li>Các hoạt động văn nghệ, múa hát truyền thống Khmer</li>\r\n</ul>\r\n\r\n<h2>Giá trị bảo tồn và phát huy</h2>\r\n<p>\r\nNgày nay, lễ hội Ok Om Bok không chỉ là sinh hoạt tín ngưỡng \r\nmà còn là <strong>sự kiện văn hóa – du lịch tiêu biểu</strong> của vùng Nam Bộ. \r\nViệc bảo tồn và phát huy lễ hội góp phần giữ gìn bản sắc văn hóa Khmer \r\nvà tăng cường sự giao lưu, đoàn kết giữa các dân tộc.\r\n</p>', NULL, '2026-10-15', '2026-10-18', NULL, 'Các chùa Khmer tại tỉnh Trà Vinh', NULL, 'uploads/lehoi/20251226_171514_694eb4926e383.jpg', NULL, '', '', 'ton_giao', 2, 'hien_thi', 2, '2025-12-26 09:51:48', '2025-12-26 16:16:14');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_su_hoat_dong`
--

CREATE TABLE `lich_su_hoat_dong` (
  `ma_hoat_dong` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `loai_hoat_dong` enum('hoc_bai','lam_quiz','doc_truyen','xem_le_hoi','xem_van_hoa','binh_luan','dat_huy_hieu') NOT NULL,
  `ma_doi_tuong` int(11) DEFAULT NULL COMMENT 'ID của bài học/quiz/truyện',
  `mo_ta` varchar(500) DEFAULT NULL,
  `diem_thuong` int(11) DEFAULT 0,
  `ngay_thuc_hien` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `luot_thich_bai_viet_nhom`
--

CREATE TABLE `luot_thich_bai_viet_nhom` (
  `ma_luot_thich` int(11) NOT NULL,
  `ma_bai_viet` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `loai_cam_xuc` enum('thich','yeu_thich','ha_ha','wow','buon','phan_no') DEFAULT 'thich',
  `ngay_thich` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lượt thích bài viết';

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `luot_thich_binh_luan`
--

CREATE TABLE `luot_thich_binh_luan` (
  `ma_luot_thich` int(11) NOT NULL,
  `ma_binh_luan` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `luot_thich_binh_luan_nhom`
--

CREATE TABLE `luot_thich_binh_luan_nhom` (
  `ma_luot_thich` int(11) NOT NULL,
  `ma_binh_luan` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ngay_thich` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lượt thích bình luận';

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `luot_thich_chu_de`
--

CREATE TABLE `luot_thich_chu_de` (
  `ma_luot_thich` int(11) NOT NULL,
  `ma_chu_de` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `ma_nguoi_dung` int(11) NOT NULL,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `gioi_tinh` enum('nam','nu','khac') DEFAULT NULL,
  `so_dien_thoai` varchar(15) DEFAULT NULL,
  `anh_dai_dien` varchar(255) DEFAULT NULL,
  `tong_diem` int(11) DEFAULT 0,
  `cap_do` int(11) DEFAULT 1,
  `ngon_ngu` enum('vi','km') DEFAULT 'vi',
  `trang_thai` enum('hoat_dong','khoa','cho_xac_thuc') DEFAULT 'hoat_dong',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `lan_dang_nhap_cuoi` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`ma_nguoi_dung`, `ten_dang_nhap`, `email`, `mat_khau`, `ho_ten`, `ngay_sinh`, `gioi_tinh`, `so_dien_thoai`, `anh_dai_dien`, `tong_diem`, `cap_do`, `ngon_ngu`, `trang_thai`, `ngay_tao`, `lan_dang_nhap_cuoi`) VALUES
(34, 'LamNhatHao', 'LamNhatHao@gmail.com', '$2y$10$GUEAHZBJlr.qfCGC4uiZb.JKT6qPeSzNtp.REI.lejjOrWPLsZDaC', 'Lâm Nhật Hào', '2004-11-21', 'nam', '0337048780', '694eb4406be13_1766765632.jpg', 27, 1, 'vi', 'hoat_dong', '2025-12-26 15:19:26', '2025-12-27 10:09:42'),
(35, 'LamHieu', 'LamHieu@gmail.com', '$2y$10$lanI7OvrSJK5sRNiQpxnCehN1zGTp1iHVKUbVSyses2jbj4r.X8zi', 'Lâm Hiếu', NULL, NULL, '', '694fb03e85851_1766830142.jpg', 2, 1, 'vi', 'hoat_dong', '2025-12-27 10:08:44', '2025-12-27 10:08:51');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhat_ky_hoat_dong`
--

CREATE TABLE `nhat_ky_hoat_dong` (
  `ma_hoat_dong` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) DEFAULT NULL,
  `loai_nguoi_dung` enum('nguoi_dung','quan_tri') DEFAULT 'nguoi_dung',
  `hanh_dong` varchar(100) NOT NULL COMMENT 'login, logout, quiz_complete, lesson_complete, badge_earned, etc.',
  `loai_doi_tuong` varchar(50) DEFAULT NULL COMMENT 'van_hoa, chua, le_hoi, bai_hoc, truyen, quiz, etc.',
  `ma_doi_tuong` int(11) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `diem_thuong` int(11) DEFAULT 0,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng nhật ký hoạt động của người dùng';

--
-- Đang đổ dữ liệu cho bảng `nhat_ky_hoat_dong`
--

INSERT INTO `nhat_ky_hoat_dong` (`ma_hoat_dong`, `ma_nguoi_dung`, `loai_nguoi_dung`, `hanh_dong`, `loai_doi_tuong`, `ma_doi_tuong`, `mo_ta`, `diem_thuong`, `ip_address`, `user_agent`, `ngay_tao`) VALUES
(31, 34, 'nguoi_dung', 'register', NULL, NULL, 'Đăng ký tài khoản mới', 0, '::1', NULL, '2025-12-26 15:19:26'),
(32, 34, 'nguoi_dung', 'login', NULL, NULL, 'Đăng nhập thành công', 0, '::1', NULL, '2025-12-26 15:19:42'),
(38, 34, 'nguoi_dung', 'earn_points', NULL, NULL, 'Nhận 5 điểm: Đọc truyện: Sự tích cây Thốt Nốt', 0, '::1', NULL, '2025-12-26 15:57:51'),
(39, 34, 'nguoi_dung', 'earn_points', NULL, NULL, 'Nhận 5 điểm: Tạo chủ đề mới', 0, '::1', NULL, '2025-12-26 16:02:37'),
(40, 34, 'nguoi_dung', 'earn_points', NULL, NULL, 'Nhận 5 điểm: Tạo chủ đề mới', 0, '::1', NULL, '2025-12-26 16:03:57'),
(41, 34, 'nguoi_dung', 'earn_points', NULL, NULL, 'Nhận 5 điểm: Đọc truyện: Sự tích cây Thốt Nốt', 0, '::1', NULL, '2025-12-26 16:06:26'),
(43, 34, 'nguoi_dung', 'login', NULL, NULL, 'Đăng nhập thành công', 0, '::1', NULL, '2025-12-26 16:13:36'),
(49, 34, 'nguoi_dung', 'login', NULL, NULL, 'Đăng nhập thành công', 0, '::1', NULL, '2025-12-27 08:23:46'),
(53, 34, 'nguoi_dung', 'login', NULL, NULL, 'Đăng nhập thành công', 0, '::1', NULL, '2025-12-27 09:30:08'),
(54, 34, 'nguoi_dung', 'earn_points', NULL, NULL, 'Nhận 5 điểm: Tạo chủ đề mới', 0, '::1', NULL, '2025-12-27 09:36:19'),
(55, 34, 'nguoi_dung', 'earn_points', NULL, NULL, 'Nhận 2 điểm: Trả lời chủ đề', 0, '::1', NULL, '2025-12-27 10:08:02'),
(58, 35, 'nguoi_dung', 'register', NULL, NULL, 'Đăng ký tài khoản mới', 0, '::1', NULL, '2025-12-27 10:08:44'),
(59, 35, 'nguoi_dung', 'login', NULL, NULL, 'Đăng nhập thành công', 0, '::1', NULL, '2025-12-27 10:08:51'),
(60, 35, 'nguoi_dung', 'earn_points', NULL, NULL, 'Nhận 2 điểm: Trả lời chủ đề', 0, '::1', NULL, '2025-12-27 10:09:28'),
(61, 34, 'nguoi_dung', 'login', NULL, NULL, 'Đăng nhập thành công', 0, '::1', NULL, '2025-12-27 10:09:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhom_hoc_tap`
--

CREATE TABLE `nhom_hoc_tap` (
  `ma_nhom` int(11) NOT NULL,
  `ten_nhom` varchar(255) NOT NULL COMMENT 'Tên nhóm tiếng Việt',
  `ten_nhom_km` varchar(255) DEFAULT NULL COMMENT 'Tên nhóm tiếng Khmer',
  `mo_ta` text DEFAULT NULL COMMENT 'Mô tả nhóm',
  `mo_ta_km` text DEFAULT NULL COMMENT 'Mô tả tiếng Khmer',
  `hinh_anh` varchar(255) DEFAULT NULL COMMENT 'Ảnh banner nhóm',
  `icon` varchar(100) DEFAULT 'fas fa-users' COMMENT 'Icon FontAwesome',
  `anh_banner` varchar(500) DEFAULT NULL COMMENT 'Đường dẫn ảnh banner của nhóm',
  `mau_sac` varchar(20) DEFAULT '#8b7355' COMMENT 'Màu chủ đạo',
  `loai_nhom` enum('cong_khai','rieng_tu','bi_mat') DEFAULT 'cong_khai' COMMENT 'Loại nhóm',
  `ma_nguoi_tao` int(11) NOT NULL COMMENT 'ID người tạo nhóm',
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `trang_thai` enum('hoat_dong','tam_khoa','da_xoa') DEFAULT 'hoat_dong',
  `so_thanh_vien` int(11) DEFAULT 0 COMMENT 'Số lượng thành viên',
  `so_bai_viet` int(11) DEFAULT 0 COMMENT 'Số lượng bài viết',
  `thu_tu` int(11) DEFAULT 0 COMMENT 'Thứ tự hiển thị'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng nhóm học tập cộng đồng';

--
-- Đang đổ dữ liệu cho bảng `nhom_hoc_tap`
--

INSERT INTO `nhom_hoc_tap` (`ma_nhom`, `ten_nhom`, `ten_nhom_km`, `mo_ta`, `mo_ta_km`, `hinh_anh`, `icon`, `anh_banner`, `mau_sac`, `loai_nhom`, `ma_nguoi_tao`, `ngay_tao`, `ngay_cap_nhat`, `trang_thai`, `so_thanh_vien`, `so_bai_viet`, `thu_tu`) VALUES
(42, 'Học tiếng Khmer cơ bản', 'រៀនភាសាខ្មែរ​មូលដ្ឋាន', 'Học tiếng Khmer cơ bản', 'រៀនភាសាខ្មែរ​មូលដ្ឋាន', NULL, 'fas fa-graduation-cap', 'uploads/group_banners/03-sh-605.jpg', '#000000', 'cong_khai', 34, '2025-12-27 17:00:16', '2025-12-27 17:04:11', 'hoat_dong', 1, 0, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `noi_quy_nhom`
--

CREATE TABLE `noi_quy_nhom` (
  `ma_noi_quy` int(11) NOT NULL,
  `ma_nhom` int(11) NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `tieu_de_km` varchar(255) DEFAULT NULL,
  `noi_dung` text NOT NULL,
  `noi_dung_km` text DEFAULT NULL,
  `thu_tu` int(11) DEFAULT 0,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng nội quy nhóm';

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quan_tri_vien`
--

CREATE TABLE `quan_tri_vien` (
  `ma_qtv` int(11) NOT NULL,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `so_dien_thoai` varchar(15) DEFAULT NULL,
  `anh_dai_dien` varchar(255) DEFAULT NULL,
  `vai_tro` enum('sieu_quan_tri','quan_tri','bien_tap_vien') DEFAULT 'bien_tap_vien',
  `trang_thai` enum('hoat_dong','khoa') DEFAULT 'hoat_dong',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `lan_dang_nhap_cuoi` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `quan_tri_vien`
--

INSERT INTO `quan_tri_vien` (`ma_qtv`, `ten_dang_nhap`, `mat_khau`, `ho_ten`, `email`, `so_dien_thoai`, `anh_dai_dien`, `vai_tro`, `trang_thai`, `ngay_tao`, `lan_dang_nhap_cuoi`) VALUES
(2, 'LamNhatHao', '$2y$10$/U3xnEzIas2CZr8AHxKRa.PuLwXoPJUGdq9rXObGkCD/Q1.Zt9t1e', 'Lâm Nhật Hào', 'lamnhathao@khmerculture.vn', NULL, NULL, 'sieu_quan_tri', 'hoat_dong', '2025-12-20 08:22:29', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quiz`
--

CREATE TABLE `quiz` (
  `ma_quiz` int(11) NOT NULL,
  `loai_quiz` enum('van_hoa','chua','le_hoi','truyen_dan_gian','tong_hop') NOT NULL,
  `ma_doi_tuong` int(11) DEFAULT NULL COMMENT 'ID của văn hóa/chùa/lễ hội/truyện',
  `tieu_de` varchar(255) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `thoi_gian` int(11) DEFAULT 600 COMMENT 'Thời gian làm bài (giây)',
  `muc_do` enum('de','trung_binh','kho') DEFAULT 'trung_binh',
  `diem_toi_da` int(11) DEFAULT 100,
  `trang_thai` enum('hoat_dong','tam_dung') DEFAULT 'hoat_dong',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng quiz thống nhất - gộp từ quiz_van_hoa, quiz_chua, quiz_le_hoi, quiz_truyen_dan_gian, quiz_tong_hop, bai_kiem_tra';

--
-- Đang đổ dữ liệu cho bảng `quiz`
--

INSERT INTO `quiz` (`ma_quiz`, `loai_quiz`, `ma_doi_tuong`, `tieu_de`, `mo_ta`, `thoi_gian`, `muc_do`, `diem_toi_da`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(16, 'chua', 8, 'Ý nghĩa văn hóa – tín ngưỡng của Lễ hội Chol Chnam Thmay của người Khmer Nam Bộ', '<div class=\"modal fade\" id=\"viewQuestionsModal\" tabindex=\"-1\" aria-labelledby=\"viewQuestionsModalLabel\" style=\"display: none;\" aria-hidden=\"true\">\r\n    <div class=\"modal-dialog modal-xl\" role=\"document\">\r\n        <div class=\"modal-content modern-modal\">\r\n            <div class=\"modal-header-modern gradient-info\">\r\n                <div class=\"modal-icon-wrapper\">\r\n                    <i class=\"fas fa-list\"></i>\r\n                </div>\r\n                <div>\r\n                    <h5 class=\"modal-title-modern\">Xem &amp; Sửa Câu Hỏi</h5>\r\n                    <p class=\"modal-subtitle\">Quản lý danh sách câu hỏi</p>\r\n                </div>\r\n                <button type=\"button\" class=\"close-modern\" data-dismiss=\"modal\">\r\n                    <i class=\"fas fa-times\"></i>\r\n                </button>\r\n            </div>\r\n            <div class=\"modal-body-modern\" style=\"max-height: 70vh; overflow-y: auto;\">\r\n                <div class=\"info-banner-modern\">\r\n                    <i class=\"fas fa-info-circle\"></i>\r\n                    <div>\r\n                        <strong>Quiz:</strong> <span id=\"view_quiz_title\">Ý nghĩa văn hóa – tín ngưỡng của Lễ hội Chol Chnam Thmay của người Khmer Nam Bộ</span>\r\n                    </div>\r\n                </div>\r\n                <div id=\"questions_list_container\"><div class=\"info-banner-modern\"><i class=\"fas fa-info-circle\"></i><div>Chưa có câu hỏi nào</div></div></div>\r\n            </div>\r\n            <div class=\"modal-footer-modern\">\r\n                <button type=\"button\" class=\"btn-modern btn-secondary-modern\" data-dismiss=\"modal\">\r\n                    <i class=\"fas fa-times\"></i> Đóng\r\n                </button>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</div>', 600, 'trung_binh', 100, 'hoat_dong', '2025-12-26 05:14:23', '2025-12-26 05:14:23'),
(17, 'van_hoa', 14, 'Nét Đẹp Độc Đáo Trang Phục Của Người Khmer Nam Bộ', 'Trang phục truyền thống của người Khmer Nam Bộ mang vẻ đẹp mộc mạc, tinh tế, thể hiện bản sắc văn hóa và đời sống tinh thần của cộng đồng qua nhiều thế hệ.', 600, 'trung_binh', 100, 'hoat_dong', '2025-12-26 15:31:52', '2025-12-26 15:31:52'),
(18, 'chua', 10, 'Chùa Âng', 'Chùa Âng là một trong những ngôi chùa Khmer cổ kính và tiêu biểu nhất \r\ncủa tỉnh Trà Vinh. Chùa không chỉ là nơi sinh hoạt tôn giáo của cộng đồng người Khmer, \r\nmà còn là trung tâm văn hóa, giáo dục và gìn giữ bản sắc dân tộc Khmer Nam Bộ.', 600, 'trung_binh', 100, 'hoat_dong', '2025-12-26 15:44:45', '2025-12-26 15:44:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thanh_vien_nhom`
--

CREATE TABLE `thanh_vien_nhom` (
  `ma_thanh_vien_nhom` int(11) NOT NULL,
  `ma_nhom` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `vai_tro` enum('quan_tri_vien','thanh_vien','cho_duyet') DEFAULT 'thanh_vien' COMMENT 'Vai trò trong nhóm',
  `ngay_tham_gia` datetime DEFAULT current_timestamp(),
  `trang_thai` enum('hoat_dong','da_roi','bi_chan') DEFAULT 'hoat_dong'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng thành viên trong nhóm';

--
-- Đang đổ dữ liệu cho bảng `thanh_vien_nhom`
--

INSERT INTO `thanh_vien_nhom` (`ma_thanh_vien_nhom`, `ma_nhom`, `ma_nguoi_dung`, `vai_tro`, `ngay_tham_gia`, `trang_thai`) VALUES
(23, 42, 34, '', '2025-12-27 17:00:16', 'hoat_dong');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_ke_nguoi_dung`
--

CREATE TABLE `thong_ke_nguoi_dung` (
  `ma_thong_ke` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `tong_bai_hoc_hoan_thanh` int(11) DEFAULT 0,
  `tong_quiz_hoan_thanh` int(11) DEFAULT 0,
  `tong_diem` int(11) DEFAULT 0,
  `cap_do_hien_tai` int(11) DEFAULT 1,
  `kinh_nghiem_hien_tai` int(11) DEFAULT 0,
  `kinh_nghiem_can_thiet` int(11) DEFAULT 100,
  `so_ngay_hoc_lien_tiep` int(11) DEFAULT 0,
  `ngay_hoc_gan_nhat` date DEFAULT NULL,
  `tong_thoi_gian_hoc` int(11) DEFAULT 0 COMMENT 'Tổng thời gian học (phút)',
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tien_trinh_hoc_tap`
--

CREATE TABLE `tien_trinh_hoc_tap` (
  `ma_tien_trinh` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ma_bai_hoc` int(11) NOT NULL,
  `trang_thai` enum('chua_hoc','dang_hoc','hoan_thanh') DEFAULT 'chua_hoc',
  `diem` int(11) DEFAULT 0,
  `tien_do` int(11) DEFAULT 0 COMMENT 'Phần trăm hoàn thành',
  `diem_dat_duoc` int(11) DEFAULT 0,
  `so_lan_hoc` int(11) DEFAULT 0,
  `thoi_gian_hoc` int(11) DEFAULT 0 COMMENT 'Tổng thời gian học (phút)',
  `lan_hoc_cuoi` timestamp NULL DEFAULT NULL,
  `ngay_bat_dau` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_hoan_thanh` timestamp NULL DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `truyen_dan_gian`
--

CREATE TABLE `truyen_dan_gian` (
  `ma_truyen` int(11) NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `tieu_de_khmer` varchar(255) DEFAULT NULL,
  `slug` varchar(300) NOT NULL,
  `tom_tat` text DEFAULT NULL,
  `noi_dung` longtext NOT NULL,
  `anh_dai_dien` varchar(255) DEFAULT NULL,
  `ma_danh_muc` int(11) DEFAULT NULL,
  `tac_gia` varchar(100) DEFAULT NULL,
  `nguon` varchar(255) DEFAULT NULL,
  `luot_xem` int(11) DEFAULT 0,
  `luot_thich` int(11) DEFAULT 0,
  `trang_thai` enum('hien_thi','an') DEFAULT 'hien_thi',
  `ma_nguoi_tao` int(11) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `truyen_dan_gian`
--

INSERT INTO `truyen_dan_gian` (`ma_truyen`, `tieu_de`, `tieu_de_khmer`, `slug`, `tom_tat`, `noi_dung`, `anh_dai_dien`, `ma_danh_muc`, `tac_gia`, `nguon`, `luot_xem`, `luot_thich`, `trang_thai`, `ma_nguoi_tao`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(7, 'Sự tích cây Thốt Nốt', 'រឿងដើមត្នោត (Reung Daeum Thnaot)', 'su-tich-cay-thot-not-1766765734', 'Truyện kể về nguồn gốc cây thốt nốt – loài cây gắn bó mật thiết với đời sống của người Khmer. Qua câu chuyện, nhân dân gửi gắm bài học về lòng hiếu thảo, sự cần cù lao động và tinh thần sẻ chia trong cộng đồng.', '<p>\r\nNgày xửa ngày xưa, tại một phum sóc của người Khmer Nam Bộ, \r\ncó hai cha con sống nương tựa vào nhau bằng nghề làm ruộng. \r\nNgười cha tuổi đã cao, sức yếu dần, mọi công việc trong nhà \r\nđều trông cậy vào người con trai hiếu thảo.\r\n</p>\r\n\r\n<p>\r\nMột năm nọ, hạn hán kéo dài, ruộng đồng khô cạn, \r\nlúa không trổ bông, cuộc sống của hai cha con trở nên vô cùng khó khăn. \r\nNgười con trai ngày đêm trăn trở, mong tìm được cách giúp cha vượt qua cơn đói khát.\r\n</p>\r\n\r\n<p>\r\nMột buổi tối, trong giấc mơ, chàng trai thấy một vị thần hiện ra \r\nvà dặn rằng: “Nếu con thật lòng hiếu thảo, hãy gieo hạt giống này \r\ntrước nhà, chăm sóc nó bằng tất cả tấm lòng. Khi cây lớn lên, \r\nnó sẽ nuôi sống con và cả cộng đồng”.\r\n</p>\r\n\r\n<p>\r\nTỉnh dậy, chàng trai thấy trong tay mình có một hạt giống lạ. \r\nChàng liền làm theo lời thần dạy, gieo hạt trước nhà và ngày ngày \r\nchăm sóc, tưới nước, bảo vệ cây non khỏi nắng gió.\r\n</p>\r\n\r\n<p>\r\nThời gian trôi qua, cây lớn rất nhanh, thân cao vút, lá xòe rộng. \r\nCây cho nước ngọt, trái thơm và lá dùng để lợp nhà, đan lát. \r\nNhờ cây ấy, hai cha con vượt qua được nạn đói, \r\ncuộc sống dần trở nên ấm no hơn.\r\n</p>\r\n\r\n<p>\r\nKhông giữ riêng cho mình, chàng trai chia sẻ nước và trái cây \r\ncho bà con trong phum sóc. Từ đó, cây được nhân rộng khắp nơi, \r\ntrở thành loài cây gắn bó với đời sống của người Khmer.\r\n</p>\r\n\r\n<p>\r\nNgười dân gọi đó là <strong>cây thốt nốt</strong> \r\nvà xem nó như biểu tượng của sự cần cù, hiếu thảo và tinh thần cộng đồng. \r\nCâu chuyện về cây thốt nốt được truyền từ đời này sang đời khác \r\nnhư một bài học quý giá cho con cháu.\r\n</p>', 'uploads/truyendangian/20251226_171534_694eb4a624fe9.jpg', 5, 'Dân gian Khmer', 'Truyền miệng trong cộng đồng người Khmer Nam Bộ', 2, 0, 'hien_thi', 2, '2025-12-26 09:57:45', '2025-12-26 10:15:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tu_vung`
--

CREATE TABLE `tu_vung` (
  `ma_tu_vung` int(11) NOT NULL,
  `ma_bai_hoc` int(11) DEFAULT NULL,
  `tu_tieng_viet` varchar(255) NOT NULL,
  `tu_tieng_khmer` varchar(255) NOT NULL,
  `phien_am` varchar(255) DEFAULT NULL,
  `nghia` text DEFAULT NULL,
  `vi_du` text DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `am_thanh` varchar(255) DEFAULT NULL,
  `loai_tu` enum('danh_tu','dong_tu','tinh_tu','trang_tu','khac') DEFAULT 'danh_tu',
  `muc_do` enum('co_ban','trung_cap','nang_cao') DEFAULT 'co_ban',
  `thu_tu` int(11) DEFAULT 0,
  `trang_thai` enum('hien_thi','an') DEFAULT 'hien_thi',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng từ vựng tiếng Khmer';

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `van_hoa`
--

CREATE TABLE `van_hoa` (
  `ma_van_hoa` int(11) NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `tieu_de_khmer` varchar(255) DEFAULT NULL,
  `slug` varchar(300) NOT NULL,
  `tom_tat` text DEFAULT NULL,
  `noi_dung` longtext NOT NULL,
  `hinh_anh_chinh` varchar(255) DEFAULT NULL,
  `thu_vien_anh` text DEFAULT NULL,
  `ma_danh_muc` int(11) DEFAULT NULL,
  `luot_xem` int(11) DEFAULT 0,
  `trang_thai` enum('xuat_ban','nhap','an') DEFAULT 'nhap',
  `ma_nguoi_tao` int(11) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `noi_bat` tinyint(1) DEFAULT 0 COMMENT 'Đánh dấu bài viết nổi bật (0=không, 1=có)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `van_hoa`
--

INSERT INTO `van_hoa` (`ma_van_hoa`, `tieu_de`, `tieu_de_khmer`, `slug`, `tom_tat`, `noi_dung`, `hinh_anh_chinh`, `thu_vien_anh`, `ma_danh_muc`, `luot_xem`, `trang_thai`, `ma_nguoi_tao`, `ngay_tao`, `ngay_cap_nhat`, `noi_bat`) VALUES
(14, 'Nét Đẹp Độc Đáo Trang Phục Của Người Khmer Nam Bộ', '', 'net-dep-doc-dao-trang-phuc-cua-nguoi-khmer-nam-bo', 'Trang phục truyền thống của người Khmer Nam Bộ mang vẻ đẹp mộc mạc, tinh tế, thể hiện bản sắc văn hóa và đời sống tinh thần của cộng đồng qua nhiều thế hệ.', '<section>\r\n  <h2>🌸 Giới thiệu</h2>\r\n  <p>\r\n    Trang phục truyền thống của người <strong>Khmer Nam Bộ</strong> không chỉ là phương tiện \r\n    che chở cơ thể trong sinh hoạt hằng ngày mà còn là biểu hiện sinh động của \r\n    <strong>bản sắc văn hóa dân tộc</strong>. Qua từng kiểu dáng, màu sắc và hoa văn, \r\n    trang phục phản ánh rõ nét đời sống tinh thần, tín ngưỡng cũng như quan niệm thẩm mỹ \r\n    của cộng đồng người Khmer trong quá trình hình thành và phát triển lâu dài tại vùng \r\n    Đồng bằng sông Cửu Long.\r\n  </p>\r\n  <p>\r\n    Trải qua nhiều thế hệ, dù chịu ảnh hưởng của đời sống hiện đại, \r\n    trang phục truyền thống của người Khmer vẫn giữ được những giá trị cốt lõi, \r\n    trở thành <em>niềm tự hào văn hóa</em> và là sợi dây gắn kết cộng đồng.\r\n  </p>\r\n</section>\r\n\r\n<section>\r\n  <h2>👔 Trang phục truyền thống của nam giới</h2>\r\n  <p>\r\n    Trang phục của nam giới Khmer Nam Bộ mang tính <strong>đơn giản, tiện lợi</strong>, \r\n    phù hợp với điều kiện lao động nông nghiệp và sinh hoạt thường ngày. \r\n    Phổ biến nhất là <strong>áo bà ba</strong> hoặc áo ngắn tay, kết hợp với \r\n    <strong>xà-rông</strong> quấn quanh người.\r\n  </p>\r\n  <p>\r\n    Màu sắc trang phục thường là những gam màu trầm, nhã nhặn, \r\n    thể hiện sự chững chạc và điềm đạm. Trong đời sống thường nhật, \r\n    trang phục giúp người mặc dễ dàng di chuyển, lao động và thích nghi \r\n    với khí hậu nóng ẩm của vùng Nam Bộ.\r\n  </p>\r\n  <p>\r\n    Vào những dịp lễ hội, cưới hỏi hay các nghi lễ tôn giáo, \r\n    nam giới Khmer thường lựa chọn xà-rông có <em>hoa văn tinh xảo</em>, \r\n    áo được may chỉnh tề hơn, thể hiện sự tôn kính đối với cộng đồng \r\n    và các giá trị truyền thống.\r\n  </p>\r\n</section>\r\n\r\n<section>\r\n  <h2>👗 Trang phục truyền thống của nữ giới</h2>\r\n  <p>\r\n    Trang phục truyền thống của phụ nữ Khmer Nam Bộ nổi bật với vẻ đẹp \r\n    <strong>dịu dàng, kín đáo nhưng không kém phần tinh tế</strong>. \r\n    Phụ nữ thường mặc áo ngắn hoặc áo dài truyền thống kết hợp với \r\n    <strong>váy xà-rông</strong> có hoa văn được dệt công phu.\r\n  </p>\r\n  <p>\r\n    Váy xà-rông của phụ nữ Khmer không chỉ có giá trị thẩm mỹ mà còn thể hiện \r\n    sự khéo léo, cần cù của người phụ nữ trong lao động thủ công truyền thống. \r\n    Mỗi họa tiết trên váy đều mang những ý nghĩa riêng, gắn với thiên nhiên, \r\n    cuộc sống và niềm tin tâm linh.\r\n  </p>\r\n  <p>\r\n    Bên cạnh đó, <em>khăn choàng</em> và các loại <strong>trang sức bạc, vàng</strong> \r\n    như vòng tay, dây chuyền, bông tai… góp phần tôn lên vẻ đẹp mềm mại, \r\n    duyên dáng và sự trang nhã của người phụ nữ Khmer Nam Bộ.\r\n  </p>\r\n</section>\r\n\r\n<section>\r\n  <h2>🎨 Hoa văn và màu sắc trong trang phục Khmer</h2>\r\n  <p>\r\n    Hoa văn là yếu tố làm nên nét đặc trưng nổi bật của trang phục Khmer Nam Bộ. \r\n    Các họa tiết thường được lấy cảm hứng từ <strong>thiên nhiên</strong> như \r\n    hoa lá, chim muông, sóng nước hoặc hình tượng thần linh trong tín ngưỡng dân gian.\r\n  </p>\r\n  <p>\r\n    Mỗi hoa văn đều mang ý nghĩa biểu trưng cho sự <em>bình an, may mắn và thịnh vượng</em>. \r\n    Sự sắp xếp hài hòa giữa các họa tiết thể hiện tư duy thẩm mỹ tinh tế \r\n    và quan niệm sống hòa hợp với tự nhiên của người Khmer.\r\n  </p>\r\n  <p>\r\n    Màu sắc sử dụng trong trang phục thường là <strong>vàng, đỏ, xanh, tím</strong>. \r\n    Trong đó, màu vàng tượng trưng cho Phật giáo và sự cao quý, \r\n    màu đỏ thể hiện sức sống và niềm tin, \r\n    màu xanh gắn với thiên nhiên và sự yên bình.\r\n  </p>\r\n</section>\r\n\r\n<section>\r\n  <h2>🎉 Trang phục trong lễ hội và sinh hoạt văn hóa</h2>\r\n  <p>\r\n    Trang phục truyền thống giữ vai trò quan trọng trong các lễ hội lớn của người Khmer \r\n    như <strong>Chôl Chnăm Thmây</strong>, <strong>Sen Dolta</strong> \r\n    và <strong>Ok Om Bok</strong>. Vào những dịp này, \r\n    người dân thường mặc trang phục truyền thống đẹp nhất để tham gia nghi lễ, \r\n    sinh hoạt cộng đồng và các hoạt động văn hóa – nghệ thuật.\r\n  </p>\r\n  <p>\r\n    Hình ảnh những bộ trang phục rực rỡ sắc màu góp phần tạo nên \r\n    <em>không gian lễ hội trang nghiêm, đậm đà bản sắc dân tộc</em>, \r\n    đồng thời thể hiện lòng tôn kính đối với tổ tiên và các giá trị tinh thần truyền thống.\r\n  </p>\r\n</section>\r\n\r\n<section>\r\n  <h2>🌿 Giá trị bảo tồn và phát huy trang phục truyền thống</h2>\r\n  <p>\r\n    Trong bối cảnh hội nhập và phát triển, \r\n    trang phục truyền thống của người Khmer Nam Bộ đang đứng trước \r\n    nhiều thách thức từ xu hướng hiện đại hóa. Tuy nhiên, \r\n    cộng đồng người Khmer vẫn luôn ý thức được tầm quan trọng \r\n    của việc <strong>bảo tồn và phát huy</strong> các giá trị văn hóa truyền thống.\r\n  </p>\r\n  <p>\r\n    Hiện nay, trang phục Khmer không chỉ xuất hiện trong lễ hội \r\n    mà còn được giới thiệu rộng rãi trong các hoạt động \r\n    <em>du lịch văn hóa, giáo dục truyền thống</em> \r\n    và các sự kiện giao lưu văn hóa dân tộc.\r\n  </p>\r\n  <p>\r\n    Việc gìn giữ trang phục truyền thống không chỉ góp phần \r\n    bảo tồn bản sắc văn hóa Khmer Nam Bộ, \r\n    mà còn làm phong phú thêm nền văn hóa đa dạng của Việt Nam.\r\n  </p>\r\n</section>\r\n', 'uploads/vanhoa/20251226_171427_694eb4631fcfd.jpg', NULL, 4, 10, 'xuat_ban', 2, '2025-12-26 09:27:33', '2025-12-26 16:15:47', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `yeu_thich`
--

CREATE TABLE `yeu_thich` (
  `ma_yeu_thich` int(11) NOT NULL,
  `ma_nguoi_dung` int(11) NOT NULL,
  `ma_doi_tuong` int(11) NOT NULL COMMENT 'ID của bài viết/chùa/truyện...',
  `loai_doi_tuong` varchar(50) NOT NULL COMMENT 'van_hoa, chua_khmer, truyen_dan_gian, le_hoi',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `bai_hoc`
--
ALTER TABLE `bai_hoc`
  ADD PRIMARY KEY (`ma_bai_hoc`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `ma_danh_muc` (`ma_danh_muc`),
  ADD KEY `cap_do` (`cap_do`);

--
-- Chỉ mục cho bảng `bai_viet_dien_dan`
--
ALTER TABLE `bai_viet_dien_dan`
  ADD PRIMARY KEY (`ma_bai_viet`),
  ADD KEY `ma_chu_de` (`ma_chu_de`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`);

--
-- Chỉ mục cho bảng `bai_viet_nhom`
--
ALTER TABLE `bai_viet_nhom`
  ADD PRIMARY KEY (`ma_bai_viet`),
  ADD KEY `idx_nhom` (`ma_nhom`),
  ADD KEY `idx_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_ngay_dang` (`ngay_dang`);

--
-- Chỉ mục cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD PRIMARY KEY (`ma_binh_luan`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `idx_loai_noidung` (`loai_noi_dung`,`ma_noi_dung`),
  ADD KEY `idx_trang_thai` (`trang_thai`);

--
-- Chỉ mục cho bảng `binh_luan_bai_viet_nhom`
--
ALTER TABLE `binh_luan_bai_viet_nhom`
  ADD PRIMARY KEY (`ma_binh_luan`),
  ADD KEY `idx_bai_viet` (`ma_bai_viet`),
  ADD KEY `idx_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `idx_binh_luan_cha` (`ma_binh_luan_cha`);

--
-- Chỉ mục cho bảng `binh_luan_dien_dan`
--
ALTER TABLE `binh_luan_dien_dan`
  ADD PRIMARY KEY (`ma_binh_luan`),
  ADD KEY `idx_chu_de` (`ma_chu_de`),
  ADD KEY `idx_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `idx_binh_luan_cha` (`ma_binh_luan_cha`);

--
-- Chỉ mục cho bảng `cau_hoi_quiz`
--
ALTER TABLE `cau_hoi_quiz`
  ADD PRIMARY KEY (`ma_cau_hoi`),
  ADD KEY `ma_quiz` (`ma_quiz`);

--
-- Chỉ mục cho bảng `chatbot_history`
--
ALTER TABLE `chatbot_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Chỉ mục cho bảng `chua_khmer`
--
ALTER TABLE `chua_khmer`
  ADD PRIMARY KEY (`ma_chua`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `chu_de_thao_luan`
--
ALTER TABLE `chu_de_thao_luan`
  ADD PRIMARY KEY (`ma_chu_de`),
  ADD KEY `ma_danh_muc` (`ma_danh_muc`),
  ADD KEY `ma_nguoi_tao` (`ma_nguoi_tao`);

--
-- Chỉ mục cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  ADD PRIMARY KEY (`ma_danh_muc`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `danh_muc_bai_hoc`
--
ALTER TABLE `danh_muc_bai_hoc`
  ADD PRIMARY KEY (`ma_danh_muc`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_thu_tu` (`thu_tu`);

--
-- Chỉ mục cho bảng `danh_muc_dien_dan`
--
ALTER TABLE `danh_muc_dien_dan`
  ADD PRIMARY KEY (`ma_danh_muc`);

--
-- Chỉ mục cho bảng `dap_an_quiz`
--
ALTER TABLE `dap_an_quiz`
  ADD PRIMARY KEY (`ma_dap_an`),
  ADD KEY `ma_cau_hoi` (`ma_cau_hoi`);

--
-- Chỉ mục cho bảng `huy_hieu`
--
ALTER TABLE `huy_hieu`
  ADD PRIMARY KEY (`ma_huy_hieu`);

--
-- Chỉ mục cho bảng `huy_hieu_nguoi_dung`
--
ALTER TABLE `huy_hieu_nguoi_dung`
  ADD PRIMARY KEY (`ma_hh_nguoi_dung`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `ma_huy_hieu` (`ma_huy_hieu`);

--
-- Chỉ mục cho bảng `ket_qua_quiz`
--
ALTER TABLE `ket_qua_quiz`
  ADD PRIMARY KEY (`ma_ket_qua`),
  ADD KEY `ma_quiz` (`ma_quiz`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`);

--
-- Chỉ mục cho bảng `ket_qua_quiz_chua`
--
ALTER TABLE `ket_qua_quiz_chua`
  ADD PRIMARY KEY (`ma_ket_qua`),
  ADD KEY `idx_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `idx_chua` (`ma_chua`),
  ADD KEY `idx_ngay_lam_bai` (`ngay_lam_bai`),
  ADD KEY `idx_diem` (`diem`);

--
-- Chỉ mục cho bảng `le_hoi`
--
ALTER TABLE `le_hoi`
  ADD PRIMARY KEY (`ma_le_hoi`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `lich_su_hoat_dong`
--
ALTER TABLE `lich_su_hoat_dong`
  ADD PRIMARY KEY (`ma_hoat_dong`),
  ADD KEY `idx_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `idx_loai_hoat_dong` (`loai_hoat_dong`);

--
-- Chỉ mục cho bảng `luot_thich_bai_viet_nhom`
--
ALTER TABLE `luot_thich_bai_viet_nhom`
  ADD PRIMARY KEY (`ma_luot_thich`),
  ADD UNIQUE KEY `unique_like` (`ma_bai_viet`,`ma_nguoi_dung`),
  ADD KEY `idx_bai_viet` (`ma_bai_viet`),
  ADD KEY `idx_nguoi_dung` (`ma_nguoi_dung`);

--
-- Chỉ mục cho bảng `luot_thich_binh_luan`
--
ALTER TABLE `luot_thich_binh_luan`
  ADD PRIMARY KEY (`ma_luot_thich`),
  ADD UNIQUE KEY `unique_like` (`ma_binh_luan`,`ma_nguoi_dung`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`);

--
-- Chỉ mục cho bảng `luot_thich_binh_luan_nhom`
--
ALTER TABLE `luot_thich_binh_luan_nhom`
  ADD PRIMARY KEY (`ma_luot_thich`),
  ADD UNIQUE KEY `unique_like_comment` (`ma_binh_luan`,`ma_nguoi_dung`),
  ADD KEY `idx_binh_luan` (`ma_binh_luan`),
  ADD KEY `idx_nguoi_dung` (`ma_nguoi_dung`);

--
-- Chỉ mục cho bảng `luot_thich_chu_de`
--
ALTER TABLE `luot_thich_chu_de`
  ADD PRIMARY KEY (`ma_luot_thich`),
  ADD UNIQUE KEY `unique_like` (`ma_chu_de`,`ma_nguoi_dung`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`);

--
-- Chỉ mục cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`ma_nguoi_dung`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`);

--
-- Chỉ mục cho bảng `nhat_ky_hoat_dong`
--
ALTER TABLE `nhat_ky_hoat_dong`
  ADD PRIMARY KEY (`ma_hoat_dong`),
  ADD KEY `idx_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `idx_hanh_dong` (`hanh_dong`),
  ADD KEY `idx_loai_doi_tuong` (`loai_doi_tuong`),
  ADD KEY `idx_ngay_tao` (`ngay_tao`),
  ADD KEY `idx_loai_nguoi_dung` (`loai_nguoi_dung`),
  ADD KEY `idx_ma_doi_tuong` (`ma_doi_tuong`);

--
-- Chỉ mục cho bảng `nhom_hoc_tap`
--
ALTER TABLE `nhom_hoc_tap`
  ADD PRIMARY KEY (`ma_nhom`),
  ADD KEY `idx_nguoi_tao` (`ma_nguoi_tao`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_loai_nhom` (`loai_nhom`);

--
-- Chỉ mục cho bảng `noi_quy_nhom`
--
ALTER TABLE `noi_quy_nhom`
  ADD PRIMARY KEY (`ma_noi_quy`),
  ADD KEY `idx_nhom` (`ma_nhom`);

--
-- Chỉ mục cho bảng `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  ADD PRIMARY KEY (`ma_qtv`),
  ADD UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`);

--
-- Chỉ mục cho bảng `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`ma_quiz`),
  ADD KEY `idx_loai_quiz` (`loai_quiz`),
  ADD KEY `idx_ma_doi_tuong` (`ma_doi_tuong`);

--
-- Chỉ mục cho bảng `thanh_vien_nhom`
--
ALTER TABLE `thanh_vien_nhom`
  ADD PRIMARY KEY (`ma_thanh_vien_nhom`),
  ADD UNIQUE KEY `unique_member` (`ma_nhom`,`ma_nguoi_dung`),
  ADD KEY `idx_nhom` (`ma_nhom`),
  ADD KEY `idx_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `idx_vai_tro` (`vai_tro`);

--
-- Chỉ mục cho bảng `thong_ke_nguoi_dung`
--
ALTER TABLE `thong_ke_nguoi_dung`
  ADD PRIMARY KEY (`ma_thong_ke`),
  ADD UNIQUE KEY `unique_user` (`ma_nguoi_dung`);

--
-- Chỉ mục cho bảng `tien_trinh_hoc_tap`
--
ALTER TABLE `tien_trinh_hoc_tap`
  ADD PRIMARY KEY (`ma_tien_trinh`),
  ADD UNIQUE KEY `unique_user_lesson` (`ma_nguoi_dung`,`ma_bai_hoc`);

--
-- Chỉ mục cho bảng `truyen_dan_gian`
--
ALTER TABLE `truyen_dan_gian`
  ADD PRIMARY KEY (`ma_truyen`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Chỉ mục cho bảng `tu_vung`
--
ALTER TABLE `tu_vung`
  ADD PRIMARY KEY (`ma_tu_vung`),
  ADD KEY `idx_bai_hoc` (`ma_bai_hoc`),
  ADD KEY `idx_loai_tu` (`loai_tu`),
  ADD KEY `idx_muc_do` (`muc_do`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_thu_tu` (`thu_tu`);

--
-- Chỉ mục cho bảng `van_hoa`
--
ALTER TABLE `van_hoa`
  ADD PRIMARY KEY (`ma_van_hoa`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `ma_danh_muc` (`ma_danh_muc`);

--
-- Chỉ mục cho bảng `yeu_thich`
--
ALTER TABLE `yeu_thich`
  ADD PRIMARY KEY (`ma_yeu_thich`),
  ADD UNIQUE KEY `unique_favorite` (`ma_nguoi_dung`,`ma_doi_tuong`,`loai_doi_tuong`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `loai_doi_tuong` (`loai_doi_tuong`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bai_hoc`
--
ALTER TABLE `bai_hoc`
  MODIFY `ma_bai_hoc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT cho bảng `bai_viet_dien_dan`
--
ALTER TABLE `bai_viet_dien_dan`
  MODIFY `ma_bai_viet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `bai_viet_nhom`
--
ALTER TABLE `bai_viet_nhom`
  MODIFY `ma_bai_viet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  MODIFY `ma_binh_luan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `binh_luan_bai_viet_nhom`
--
ALTER TABLE `binh_luan_bai_viet_nhom`
  MODIFY `ma_binh_luan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `binh_luan_dien_dan`
--
ALTER TABLE `binh_luan_dien_dan`
  MODIFY `ma_binh_luan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `cau_hoi_quiz`
--
ALTER TABLE `cau_hoi_quiz`
  MODIFY `ma_cau_hoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT cho bảng `chatbot_history`
--
ALTER TABLE `chatbot_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `chua_khmer`
--
ALTER TABLE `chua_khmer`
  MODIFY `ma_chua` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `chu_de_thao_luan`
--
ALTER TABLE `chu_de_thao_luan`
  MODIFY `ma_chu_de` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  MODIFY `ma_danh_muc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `danh_muc_bai_hoc`
--
ALTER TABLE `danh_muc_bai_hoc`
  MODIFY `ma_danh_muc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `danh_muc_dien_dan`
--
ALTER TABLE `danh_muc_dien_dan`
  MODIFY `ma_danh_muc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `dap_an_quiz`
--
ALTER TABLE `dap_an_quiz`
  MODIFY `ma_dap_an` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT cho bảng `huy_hieu`
--
ALTER TABLE `huy_hieu`
  MODIFY `ma_huy_hieu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `huy_hieu_nguoi_dung`
--
ALTER TABLE `huy_hieu_nguoi_dung`
  MODIFY `ma_hh_nguoi_dung` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT cho bảng `ket_qua_quiz`
--
ALTER TABLE `ket_qua_quiz`
  MODIFY `ma_ket_qua` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT cho bảng `ket_qua_quiz_chua`
--
ALTER TABLE `ket_qua_quiz_chua`
  MODIFY `ma_ket_qua` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `le_hoi`
--
ALTER TABLE `le_hoi`
  MODIFY `ma_le_hoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `lich_su_hoat_dong`
--
ALTER TABLE `lich_su_hoat_dong`
  MODIFY `ma_hoat_dong` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `luot_thich_bai_viet_nhom`
--
ALTER TABLE `luot_thich_bai_viet_nhom`
  MODIFY `ma_luot_thich` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `luot_thich_binh_luan`
--
ALTER TABLE `luot_thich_binh_luan`
  MODIFY `ma_luot_thich` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `luot_thich_binh_luan_nhom`
--
ALTER TABLE `luot_thich_binh_luan_nhom`
  MODIFY `ma_luot_thich` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `luot_thich_chu_de`
--
ALTER TABLE `luot_thich_chu_de`
  MODIFY `ma_luot_thich` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `ma_nguoi_dung` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT cho bảng `nhat_ky_hoat_dong`
--
ALTER TABLE `nhat_ky_hoat_dong`
  MODIFY `ma_hoat_dong` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT cho bảng `nhom_hoc_tap`
--
ALTER TABLE `nhom_hoc_tap`
  MODIFY `ma_nhom` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT cho bảng `noi_quy_nhom`
--
ALTER TABLE `noi_quy_nhom`
  MODIFY `ma_noi_quy` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  MODIFY `ma_qtv` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `quiz`
--
ALTER TABLE `quiz`
  MODIFY `ma_quiz` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `thanh_vien_nhom`
--
ALTER TABLE `thanh_vien_nhom`
  MODIFY `ma_thanh_vien_nhom` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `thong_ke_nguoi_dung`
--
ALTER TABLE `thong_ke_nguoi_dung`
  MODIFY `ma_thong_ke` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `tien_trinh_hoc_tap`
--
ALTER TABLE `tien_trinh_hoc_tap`
  MODIFY `ma_tien_trinh` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `truyen_dan_gian`
--
ALTER TABLE `truyen_dan_gian`
  MODIFY `ma_truyen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `tu_vung`
--
ALTER TABLE `tu_vung`
  MODIFY `ma_tu_vung` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `van_hoa`
--
ALTER TABLE `van_hoa`
  MODIFY `ma_van_hoa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `yeu_thich`
--
ALTER TABLE `yeu_thich`
  MODIFY `ma_yeu_thich` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bai_hoc`
--
ALTER TABLE `bai_hoc`
  ADD CONSTRAINT `fk_baihoc_danhmuc` FOREIGN KEY (`ma_danh_muc`) REFERENCES `danh_muc` (`ma_danh_muc`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `bai_viet_dien_dan`
--
ALTER TABLE `bai_viet_dien_dan`
  ADD CONSTRAINT `fk_baiviet_chude` FOREIGN KEY (`ma_chu_de`) REFERENCES `chu_de_thao_luan` (`ma_chu_de`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_baiviet_nguoidung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `bai_viet_nhom`
--
ALTER TABLE `bai_viet_nhom`
  ADD CONSTRAINT `fk_bai_viet_nguoi_dung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bai_viet_nhom` FOREIGN KEY (`ma_nhom`) REFERENCES `nhom_hoc_tap` (`ma_nhom`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bai_viet_nhom_nguoi_dung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bai_viet_nhom_nhom` FOREIGN KEY (`ma_nhom`) REFERENCES `nhom_hoc_tap` (`ma_nhom`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD CONSTRAINT `fk_binhluan_nguoidung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `binh_luan_bai_viet_nhom`
--
ALTER TABLE `binh_luan_bai_viet_nhom`
  ADD CONSTRAINT `fk_binh_luan_bai_viet` FOREIGN KEY (`ma_bai_viet`) REFERENCES `bai_viet_nhom` (`ma_bai_viet`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_binh_luan_cha` FOREIGN KEY (`ma_binh_luan_cha`) REFERENCES `binh_luan_bai_viet_nhom` (`ma_binh_luan`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_binh_luan_nguoi_dung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `binh_luan_dien_dan`
--
ALTER TABLE `binh_luan_dien_dan`
  ADD CONSTRAINT `binh_luan_dien_dan_ibfk_1` FOREIGN KEY (`ma_chu_de`) REFERENCES `chu_de_thao_luan` (`ma_chu_de`) ON DELETE CASCADE,
  ADD CONSTRAINT `binh_luan_dien_dan_ibfk_2` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE,
  ADD CONSTRAINT `binh_luan_dien_dan_ibfk_3` FOREIGN KEY (`ma_binh_luan_cha`) REFERENCES `binh_luan_dien_dan` (`ma_binh_luan`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `cau_hoi_quiz`
--
ALTER TABLE `cau_hoi_quiz`
  ADD CONSTRAINT `fk_cauhoi_quiz` FOREIGN KEY (`ma_quiz`) REFERENCES `quiz` (`ma_quiz`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chatbot_history`
--
ALTER TABLE `chatbot_history`
  ADD CONSTRAINT `fk_chatbot_user` FOREIGN KEY (`user_id`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chu_de_thao_luan`
--
ALTER TABLE `chu_de_thao_luan`
  ADD CONSTRAINT `fk_chude_danhmuc` FOREIGN KEY (`ma_danh_muc`) REFERENCES `danh_muc_dien_dan` (`ma_danh_muc`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chude_nguoidung` FOREIGN KEY (`ma_nguoi_tao`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `dap_an_quiz`
--
ALTER TABLE `dap_an_quiz`
  ADD CONSTRAINT `fk_dapan_cauhoi` FOREIGN KEY (`ma_cau_hoi`) REFERENCES `cau_hoi_quiz` (`ma_cau_hoi`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `huy_hieu_nguoi_dung`
--
ALTER TABLE `huy_hieu_nguoi_dung`
  ADD CONSTRAINT `fk_hh_huyhieu` FOREIGN KEY (`ma_huy_hieu`) REFERENCES `huy_hieu` (`ma_huy_hieu`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_hh_nguoidung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `ket_qua_quiz`
--
ALTER TABLE `ket_qua_quiz`
  ADD CONSTRAINT `fk_ketqua_nguoidung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ketqua_quiz` FOREIGN KEY (`ma_quiz`) REFERENCES `quiz` (`ma_quiz`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `ket_qua_quiz_chua`
--
ALTER TABLE `ket_qua_quiz_chua`
  ADD CONSTRAINT `fk_ket_qua_quiz_chua_chua` FOREIGN KEY (`ma_chua`) REFERENCES `chua_khmer` (`ma_chua`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ket_qua_quiz_chua_nguoi_dung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `luot_thich_bai_viet_nhom`
--
ALTER TABLE `luot_thich_bai_viet_nhom`
  ADD CONSTRAINT `fk_like_bai_viet` FOREIGN KEY (`ma_bai_viet`) REFERENCES `bai_viet_nhom` (`ma_bai_viet`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_like_nguoi_dung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `luot_thich_binh_luan`
--
ALTER TABLE `luot_thich_binh_luan`
  ADD CONSTRAINT `luot_thich_binh_luan_ibfk_1` FOREIGN KEY (`ma_binh_luan`) REFERENCES `binh_luan_dien_dan` (`ma_binh_luan`) ON DELETE CASCADE,
  ADD CONSTRAINT `luot_thich_binh_luan_ibfk_2` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `luot_thich_binh_luan_nhom`
--
ALTER TABLE `luot_thich_binh_luan_nhom`
  ADD CONSTRAINT `fk_like_binh_luan` FOREIGN KEY (`ma_binh_luan`) REFERENCES `binh_luan_bai_viet_nhom` (`ma_binh_luan`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_like_comment_nguoi_dung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `luot_thich_chu_de`
--
ALTER TABLE `luot_thich_chu_de`
  ADD CONSTRAINT `luot_thich_chu_de_ibfk_1` FOREIGN KEY (`ma_chu_de`) REFERENCES `chu_de_thao_luan` (`ma_chu_de`) ON DELETE CASCADE,
  ADD CONSTRAINT `luot_thich_chu_de_ibfk_2` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `nhat_ky_hoat_dong`
--
ALTER TABLE `nhat_ky_hoat_dong`
  ADD CONSTRAINT `fk_nhat_ky_nguoi_dung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `nhom_hoc_tap`
--
ALTER TABLE `nhom_hoc_tap`
  ADD CONSTRAINT `fk_nhom_nguoi_tao` FOREIGN KEY (`ma_nguoi_tao`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `noi_quy_nhom`
--
ALTER TABLE `noi_quy_nhom`
  ADD CONSTRAINT `fk_noi_quy_nhom` FOREIGN KEY (`ma_nhom`) REFERENCES `nhom_hoc_tap` (`ma_nhom`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thanh_vien_nhom`
--
ALTER TABLE `thanh_vien_nhom`
  ADD CONSTRAINT `fk_thanh_vien_nguoi_dung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_thanh_vien_nhom` FOREIGN KEY (`ma_nhom`) REFERENCES `nhom_hoc_tap` (`ma_nhom`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_thanh_vien_nhom_nguoi_dung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_thanh_vien_nhom_nhom` FOREIGN KEY (`ma_nhom`) REFERENCES `nhom_hoc_tap` (`ma_nhom`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tu_vung`
--
ALTER TABLE `tu_vung`
  ADD CONSTRAINT `fk_tu_vung_bai_hoc` FOREIGN KEY (`ma_bai_hoc`) REFERENCES `bai_hoc` (`ma_bai_hoc`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `yeu_thich`
--
ALTER TABLE `yeu_thich`
  ADD CONSTRAINT `fk_yeuthich_nguoidung` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
