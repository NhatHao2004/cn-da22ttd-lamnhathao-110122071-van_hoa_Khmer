-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 29, 2025 lúc 06:05 PM
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

DELIMITER $$
--
-- Thủ tục
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cap_nhat_diem_nguoi_dung` (IN `p_ma_nguoi_dung` INT, IN `p_diem_thay_doi` INT, IN `p_loai_hoat_dong` VARCHAR(50), IN `p_mo_ta` VARCHAR(255))   BEGIN
    DECLARE v_tong_diem_moi INT;
    DECLARE v_cap_do_moi INT;
    
    -- Cập nhật điểm
    UPDATE `nguoi_dung` 
    SET `tong_diem` = `tong_diem` + p_diem_thay_doi
    WHERE `ma_nguoi_dung` = p_ma_nguoi_dung;
    
    -- Lấy tổng điểm mới
    SELECT `tong_diem` INTO v_tong_diem_moi
    FROM `nguoi_dung`
    WHERE `ma_nguoi_dung` = p_ma_nguoi_dung;
    
    -- Tính cấp độ mới (mỗi 100 điểm = 1 cấp)
    SET v_cap_do_moi = FLOOR(v_tong_diem_moi / 100) + 1;
    
    -- Cập nhật cấp độ
    UPDATE `nguoi_dung`
    SET `cap_do` = v_cap_do_moi
    WHERE `ma_nguoi_dung` = p_ma_nguoi_dung;
    
    -- Ghi lịch sử điểm
    INSERT INTO `lich_su_diem` (`ma_nguoi_dung`, `loai_hoat_dong`, `diem_thay_doi`, `mo_ta`)
    VALUES (p_ma_nguoi_dung, p_loai_hoat_dong, p_diem_thay_doi, p_mo_ta);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_tang_luot_xem` (IN `p_loai_doi_tuong` VARCHAR(50), IN `p_ma_doi_tuong` INT)   BEGIN
    CASE p_loai_doi_tuong
        WHEN 'van_hoa' THEN
            UPDATE `van_hoa` SET `luot_xem` = `luot_xem` + 1 WHERE `ma_van_hoa` = p_ma_doi_tuong;
        WHEN 'chua_khmer' THEN
            UPDATE `chua_khmer` SET `luot_xem` = `luot_xem` + 1 WHERE `ma_chua` = p_ma_doi_tuong;
        WHEN 'le_hoi' THEN
            UPDATE `le_hoi` SET `luot_xem` = `luot_xem` + 1 WHERE `ma_le_hoi` = p_ma_doi_tuong;
        WHEN 'truyen_dan_gian' THEN
            UPDATE `truyen_dan_gian` SET `luot_xem` = `luot_xem` + 1 WHERE `ma_truyen` = p_ma_doi_tuong;
    END CASE;
END$$

--
-- Các hàm
--
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_kiem_tra_hoan_thanh_bai_hoc` (`p_ma_nguoi_dung` INT, `p_ma_bai_hoc` INT) RETURNS TINYINT(1) DETERMINISTIC BEGIN
    DECLARE v_hoan_thanh BOOLEAN;
    
    SELECT EXISTS(
        SELECT 1 FROM `tien_trinh_hoc_tap`
        WHERE `ma_nguoi_dung` = p_ma_nguoi_dung
        AND `ma_bai_hoc` = p_ma_bai_hoc
        AND `trang_thai` = 'hoan_thanh'
    ) INTO v_hoan_thanh;
    
    RETURN v_hoan_thanh;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_tinh_cap_do` (`p_tong_diem` INT) RETURNS INT(11) DETERMINISTIC BEGIN
    RETURN FLOOR(p_tong_diem / 100) + 1;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `fn_tinh_tong_diem` (`p_ma_nguoi_dung` INT) RETURNS INT(11) DETERMINISTIC BEGIN
    DECLARE v_tong_diem INT;
    
    SELECT COALESCE(SUM(`diem_thay_doi`), 0) INTO v_tong_diem
    FROM `lich_su_diem`
    WHERE `ma_nguoi_dung` = p_ma_nguoi_dung;
    
    RETURN v_tong_diem;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bai_hoc`
--

CREATE TABLE `bai_hoc` (
  `ma_bai_hoc` int(10) UNSIGNED NOT NULL,
  `ma_danh_muc` int(10) UNSIGNED DEFAULT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `noi_dung` longtext DEFAULT NULL,
  `cap_do` enum('co_ban','trung_cap','nang_cao') DEFAULT 'co_ban',
  `thu_tu` int(11) DEFAULT 0,
  `thoi_luong` int(10) UNSIGNED DEFAULT 30 COMMENT 'Thời lượng học (phút)',
  `hinh_anh` varchar(255) DEFAULT NULL,
  `file_am_thanh` varchar(255) DEFAULT NULL,
  `trang_thai` enum('nhap','xuat_ban') DEFAULT 'nhap',
  `luot_hoc` int(10) UNSIGNED DEFAULT 0,
  `ma_nguoi_tao` int(10) UNSIGNED DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `binh_luan`
--

CREATE TABLE `binh_luan` (
  `ma_binh_luan` int(10) UNSIGNED NOT NULL,
  `ma_nguoi_dung` int(10) UNSIGNED NOT NULL,
  `loai_doi_tuong` varchar(50) NOT NULL COMMENT 'van_hoa, truyen, le_hoi',
  `ma_doi_tuong` int(10) UNSIGNED NOT NULL,
  `noi_dung` text NOT NULL,
  `binh_luan_cha` int(10) UNSIGNED DEFAULT NULL COMMENT 'ID bình luận cha (reply)',
  `trang_thai` enum('cho_duyet','da_duyet','tu_choi') DEFAULT 'cho_duyet',
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `binh_luan`
--

INSERT INTO `binh_luan` (`ma_binh_luan`, `ma_nguoi_dung`, `loai_doi_tuong`, `ma_doi_tuong`, `noi_dung`, `binh_luan_cha`, `trang_thai`, `ngay_tao`) VALUES
(1, 1, 'van_hoa', 1, 'Bài viết rất hay và bổ ích! Cảm ơn admin đã chia sẻ.', NULL, 'da_duyet', '2024-01-16 10:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cai_dat_he_thong`
--

CREATE TABLE `cai_dat_he_thong` (
  `ma_cai_dat` int(10) UNSIGNED NOT NULL,
  `khoa` varchar(100) NOT NULL,
  `gia_tri` text DEFAULT NULL,
  `mo_ta` varchar(255) DEFAULT NULL,
  `nhom` varchar(50) DEFAULT 'chung' COMMENT 'chung, lien_he, mang_xa_hoi, he_thong',
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `cai_dat_he_thong`
--

INSERT INTO `cai_dat_he_thong` (`ma_cai_dat`, `khoa`, `gia_tri`, `mo_ta`, `nhom`, `ngay_cap_nhat`) VALUES
(63, 'site_name', 'Văn Hóa Khmer Nam Bộ', NULL, 'chung', '2025-11-29 22:28:36'),
(64, 'site_description', 'Hệ thống quản lý và học tập văn hóa Khmer Nam Bộ', NULL, 'chung', '2025-11-29 22:28:36'),
(65, 'site_keywords', 'Văn hóa khmer, khmer nam bộ, học tiếng khmer', NULL, 'chung', '2025-11-29 22:29:06'),
(66, 'admin_email', 'LamNhatHao@gmail.com', NULL, 'chung', '2025-11-29 22:30:11'),
(67, 'contact_phone', '0337048780', NULL, 'chung', '2025-11-29 22:30:11'),
(68, 'contact_address', 'Xã Phong Phú, Tĩnh Vĩnh Long', NULL, 'chung', '2025-11-29 22:30:11'),
(69, 'facebook_url', '', NULL, 'chung', '2025-11-29 22:28:36'),
(70, 'youtube_url', '', NULL, 'chung', '2025-11-29 22:28:36'),
(71, 'items_per_page', '10', NULL, 'chung', '2025-11-29 22:28:36'),
(72, 'enable_comments', '1', NULL, 'chung', '2025-11-29 22:28:36'),
(73, 'enable_registration', '1', NULL, 'chung', '2025-11-29 22:28:36'),
(74, 'maintenance_mode', '0', NULL, 'chung', '2025-11-29 22:28:36'),
(119, 'twitter_url', '', NULL, 'chung', '2025-11-29 22:51:10'),
(120, 'instagram_url', '', NULL, 'chung', '2025-11-29 22:51:10');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chua_khmer`
--

CREATE TABLE `chua_khmer` (
  `ma_chua` int(10) UNSIGNED NOT NULL,
  `ten_chua` varchar(200) NOT NULL,
  `ten_tieng_khmer` varchar(200) DEFAULT NULL,
  `slug` varchar(250) NOT NULL,
  `dia_chi` varchar(255) DEFAULT NULL,
  `tinh_thanh` varchar(100) DEFAULT NULL,
  `quan_huyen` varchar(100) DEFAULT NULL,
  `loai_chua` enum('Theravada','Mahayana','Vajrayana') DEFAULT 'Theravada',
  `so_dien_thoai` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `mo_ta_ngan` text DEFAULT NULL,
  `lich_su` longtext DEFAULT NULL,
  `hinh_anh_chinh` varchar(255) DEFAULT NULL,
  `thu_vien_anh` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`thu_vien_anh`)),
  `nam_thanh_lap` year(4) DEFAULT NULL,
  `so_nha_su` int(10) UNSIGNED DEFAULT 0,
  `trang_thai` enum('hoat_dong','ngung_hoat_dong','dang_xay_dung') DEFAULT 'hoat_dong',
  `luot_xem` int(10) UNSIGNED DEFAULT 0,
  `ma_nguoi_tao` int(10) UNSIGNED DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_muc`
--

CREATE TABLE `danh_muc` (
  `ma_danh_muc` int(10) UNSIGNED NOT NULL,
  `ten_danh_muc` varchar(100) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `loai` enum('van_hoa','bai_hoc','truyen','khac') DEFAULT 'khac',
  `danh_muc_cha` int(10) UNSIGNED DEFAULT NULL,
  `thu_tu` int(11) DEFAULT 0,
  `trang_thai` enum('hien_thi','an') DEFAULT 'hien_thi',
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc`
--

INSERT INTO `danh_muc` (`ma_danh_muc`, `ten_danh_muc`, `slug`, `mo_ta`, `loai`, `danh_muc_cha`, `thu_tu`, `trang_thai`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 'Văn hóa truyền thống', 'van-hoa-truyen-thong', 'Các bài viết về văn hóa truyền thống Khmer Nam Bộ', 'van_hoa', NULL, 1, 'hien_thi', '2025-11-29 09:27:45', '2025-11-29 09:27:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `huy_hieu`
--

CREATE TABLE `huy_hieu` (
  `ma_huy_hieu` int(10) UNSIGNED NOT NULL,
  `ten_huy_hieu` varchar(100) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `dieu_kien` text DEFAULT NULL COMMENT 'Điều kiện đạt huy hiệu',
  `diem_thuong` int(10) UNSIGNED DEFAULT 0,
  `thu_tu` int(11) DEFAULT 0,
  `trang_thai` enum('hoat_dong','khong_hoat_dong') DEFAULT 'hoat_dong',
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `huy_hieu`
--

INSERT INTO `huy_hieu` (`ma_huy_hieu`, `ten_huy_hieu`, `mo_ta`, `icon`, `dieu_kien`, `diem_thuong`, `thu_tu`, `trang_thai`, `ngay_tao`) VALUES
(1, 'Người mới', 'Chào mừng bạn đến với hệ thống', 'fa-user-plus', 'Hoàn thành đăng ký tài khoản', 10, 1, 'hoat_dong', '2025-11-29 09:27:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `le_hoi`
--

CREATE TABLE `le_hoi` (
  `ma_le_hoi` int(10) UNSIGNED NOT NULL,
  `ten_le_hoi` varchar(200) NOT NULL,
  `ten_le_hoi_khmer` varchar(200) DEFAULT NULL,
  `slug` varchar(250) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `ngay_bat_dau` date DEFAULT NULL,
  `ngay_ket_thuc` date DEFAULT NULL,
  `dia_diem` varchar(255) DEFAULT NULL,
  `anh_dai_dien` varchar(255) DEFAULT NULL,
  `thu_vien_anh` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`thu_vien_anh`)),
  `y_nghia` longtext DEFAULT NULL,
  `nguon_goc` longtext DEFAULT NULL,
  `trang_thai` enum('hien_thi','an') DEFAULT 'hien_thi',
  `luot_xem` int(10) UNSIGNED DEFAULT 0,
  `ma_nguoi_tao` int(10) UNSIGNED DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `lich_su_diem`
--

CREATE TABLE `lich_su_diem` (
  `ma_lich_su` int(10) UNSIGNED NOT NULL,
  `ma_nguoi_dung` int(10) UNSIGNED NOT NULL,
  `loai_hoat_dong` varchar(50) NOT NULL COMMENT 'hoan_thanh_bai_hoc, binh_luan, dang_nhap',
  `diem_thay_doi` int(11) NOT NULL COMMENT 'Số điểm thay đổi (+ hoặc -)',
  `mo_ta` varchar(255) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `lich_su_diem`
--

INSERT INTO `lich_su_diem` (`ma_lich_su`, `ma_nguoi_dung`, `loai_hoat_dong`, `diem_thay_doi`, `mo_ta`, `ngay_tao`) VALUES
(1, 1, 'dang_ky', 10, 'Điểm thưởng đăng ký tài khoản', '2024-01-01 10:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `ma_nguoi_dung` int(10) UNSIGNED NOT NULL,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `ho_ten` varchar(100) DEFAULT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `gioi_tinh` enum('nam','nu','khac') DEFAULT NULL,
  `so_dien_thoai` varchar(15) DEFAULT NULL,
  `anh_dai_dien` varchar(255) DEFAULT NULL,
  `tong_diem` int(10) UNSIGNED DEFAULT 0,
  `cap_do` int(10) UNSIGNED DEFAULT 1,
  `huy_hieu` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Danh sách huy hiệu đạt được' CHECK (json_valid(`huy_hieu`)),
  `trang_thai` enum('hoat_dong','bi_khoa') DEFAULT 'hoat_dong',
  `so_lan_dang_nhap` int(10) UNSIGNED DEFAULT 0,
  `lan_dang_nhap_cuoi` datetime DEFAULT NULL,
  `ip_dang_nhap_cuoi` varchar(45) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`ma_nguoi_dung`, `ten_dang_nhap`, `email`, `mat_khau`, `ho_ten`, `ngay_sinh`, `gioi_tinh`, `so_dien_thoai`, `anh_dai_dien`, `tong_diem`, `cap_do`, `huy_hieu`, `trang_thai`, `so_lan_dang_nhap`, `lan_dang_nhap_cuoi`, `ip_dang_nhap_cuoi`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 'LamNhatHao', 'LamNhatHao@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lâm Nhật Hào', '2004-11-21', 'nam', '0337048780', 'uploads/avatar/20251129_150955_692afeb3c3b92.jpg', 1000, 1, NULL, 'hoat_dong', 1, NULL, NULL, '2025-11-29 09:27:45', '2025-11-29 23:04:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_dung_huy_hieu`
--

CREATE TABLE `nguoi_dung_huy_hieu` (
  `ma_nguoi_dung` int(10) UNSIGNED NOT NULL,
  `ma_huy_hieu` int(10) UNSIGNED NOT NULL,
  `ngay_dat_duoc` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung_huy_hieu`
--

INSERT INTO `nguoi_dung_huy_hieu` (`ma_nguoi_dung`, `ma_huy_hieu`, `ngay_dat_duoc`) VALUES
(1, 1, '2024-01-01 10:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhat_ky_hoat_dong`
--

CREATE TABLE `nhat_ky_hoat_dong` (
  `ma_nhat_ky` int(10) UNSIGNED NOT NULL,
  `ma_nguoi_dung` int(10) UNSIGNED NOT NULL,
  `loai_nguoi_dung` enum('quan_tri','nguoi_dung') NOT NULL,
  `hanh_dong` varchar(50) NOT NULL COMMENT 'create, update, delete, login, logout',
  `loai_doi_tuong` varchar(50) DEFAULT NULL COMMENT 'van_hoa, chua, le_hoi, bai_hoc, truyen',
  `ma_doi_tuong` int(10) UNSIGNED DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `nhat_ky_hoat_dong`
--

INSERT INTO `nhat_ky_hoat_dong` (`ma_nhat_ky`, `ma_nguoi_dung`, `loai_nguoi_dung`, `hanh_dong`, `loai_doi_tuong`, `ma_doi_tuong`, `mo_ta`, `ip_address`, `user_agent`, `ngay_tao`) VALUES
(83, 1, 'quan_tri', 'logout', NULL, NULL, 'Đăng xuất khỏi hệ thống', '::1', NULL, '2025-11-29 23:09:45'),
(84, 1, 'quan_tri', 'login', NULL, NULL, 'Đăng nhập thành công vào hệ thống', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-29 23:09:47'),
(85, 1, 'quan_tri', 'logout', NULL, NULL, 'Đăng xuất khỏi hệ thống', '::1', NULL, '2025-11-29 23:59:55'),
(86, 1, 'quan_tri', 'login', NULL, NULL, 'Đăng nhập thành công vào hệ thống', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-11-29 23:59:57');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quan_tri_vien`
--

CREATE TABLE `quan_tri_vien` (
  `ma_qtv` int(10) UNSIGNED NOT NULL,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `vai_tro` enum('sieu_quan_tri','quan_tri','bien_tap_vien') DEFAULT 'bien_tap_vien',
  `trang_thai` enum('hoat_dong','tam_khoa','khoa_vinh_vien') DEFAULT 'hoat_dong',
  `anh_dai_dien` varchar(255) DEFAULT NULL,
  `so_dien_thoai` varchar(15) DEFAULT NULL,
  `dia_chi` text DEFAULT NULL,
  `so_lan_dang_nhap` int(10) UNSIGNED DEFAULT 0,
  `lan_dang_nhap_cuoi` datetime DEFAULT NULL,
  `ip_dang_nhap_cuoi` varchar(45) DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `quan_tri_vien`
--

INSERT INTO `quan_tri_vien` (`ma_qtv`, `ten_dang_nhap`, `email`, `mat_khau`, `ho_ten`, `vai_tro`, `trang_thai`, `anh_dai_dien`, `so_dien_thoai`, `dia_chi`, `so_lan_dang_nhap`, `lan_dang_nhap_cuoi`, `ip_dang_nhap_cuoi`, `ngay_tao`, `ngay_cap_nhat`) VALUES
(1, 'LamNhatHao', 'lamnhathao@khmer.vn', '$2y$10$KVU.7pwTCYaeI/eITYNLdON9rkW3UjTQOxcuDY0PSemJa/SAf4TBC', 'Lâm Nhật Hào', 'sieu_quan_tri', 'hoat_dong', NULL, '0901234567', NULL, 162, '2025-11-29 23:59:57', '::1', '2025-11-29 09:27:45', '2025-11-29 23:59:57');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tags`
--

CREATE TABLE `tags` (
  `ma_tag` int(10) UNSIGNED NOT NULL,
  `ten_tag` varchar(50) NOT NULL,
  `slug` varchar(70) NOT NULL,
  `so_lan_su_dung` int(10) UNSIGNED DEFAULT 0,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thong_bao`
--

CREATE TABLE `thong_bao` (
  `ma_thong_bao` int(10) UNSIGNED NOT NULL,
  `ma_qtv` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL = gửi cho tất cả admin',
  `tieu_de` varchar(255) NOT NULL,
  `noi_dung` text NOT NULL,
  `loai` enum('thong_tin','canh_bao','thanh_cong','loi') DEFAULT 'thong_tin',
  `lien_ket` varchar(255) DEFAULT NULL,
  `trang_thai` enum('chua_doc','da_doc') DEFAULT 'chua_doc',
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tien_trinh_hoc_tap`
--

CREATE TABLE `tien_trinh_hoc_tap` (
  `ma_tien_trinh` int(10) UNSIGNED NOT NULL,
  `ma_nguoi_dung` int(10) UNSIGNED NOT NULL,
  `ma_bai_hoc` int(10) UNSIGNED NOT NULL,
  `tien_do` tinyint(3) UNSIGNED DEFAULT 0 COMMENT 'Tiến độ 0-100%',
  `trang_thai` enum('chua_hoc','dang_hoc','hoan_thanh') DEFAULT 'chua_hoc',
  `diem_dat_duoc` int(10) UNSIGNED DEFAULT 0,
  `thoi_gian_hoc` int(10) UNSIGNED DEFAULT 0 COMMENT 'Thời gian học (phút)',
  `lan_hoc_cuoi` datetime DEFAULT NULL,
  `ngay_bat_dau` datetime DEFAULT current_timestamp(),
  `ngay_hoan_thanh` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Bẫy `tien_trinh_hoc_tap`
--
DELIMITER $$
CREATE TRIGGER `trg_tang_luot_hoc` AFTER INSERT ON `tien_trinh_hoc_tap` FOR EACH ROW BEGIN
    UPDATE `bai_hoc` SET `luot_hoc` = `luot_hoc` + 1
    WHERE `ma_bai_hoc` = NEW.`ma_bai_hoc`;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tin_nhan`
--

CREATE TABLE `tin_nhan` (
  `ma_tin_nhan` int(10) UNSIGNED NOT NULL,
  `ma_nguoi_gui` int(10) UNSIGNED NOT NULL,
  `loai_nguoi_gui` enum('admin','user') NOT NULL,
  `ma_nguoi_nhan` int(10) UNSIGNED NOT NULL,
  `loai_nguoi_nhan` enum('admin','user') NOT NULL,
  `noi_dung` text NOT NULL,
  `lien_ket` varchar(255) DEFAULT NULL,
  `trang_thai` enum('chua_doc','da_doc') DEFAULT 'chua_doc',
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tin_nhan`
--

INSERT INTO `tin_nhan` (`ma_tin_nhan`, `ma_nguoi_gui`, `loai_nguoi_gui`, `ma_nguoi_nhan`, `loai_nguoi_nhan`, `noi_dung`, `lien_ket`, `trang_thai`, `ngay_tao`) VALUES
(30, 1, 'admin', 1, 'user', 'abc', NULL, 'da_doc', '2025-11-30 00:04:42');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `truyen_dan_gian`
--

CREATE TABLE `truyen_dan_gian` (
  `ma_truyen` int(10) UNSIGNED NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `tieu_de_khmer` varchar(255) DEFAULT NULL,
  `slug` varchar(300) NOT NULL,
  `tom_tat` text DEFAULT NULL,
  `noi_dung` longtext NOT NULL,
  `anh_dai_dien` varchar(255) DEFAULT NULL,
  `the_loai` enum('truyen_co_tich','truyen_than_thoai','truyen_truyen_thuyet','truyen_ngụ_ngon','khac') DEFAULT 'truyen_co_tich',
  `nguon_goc` text DEFAULT NULL,
  `tac_gia` varchar(100) DEFAULT NULL,
  `file_am_thanh` varchar(255) DEFAULT NULL COMMENT 'File kể chuyện',
  `trang_thai` enum('hien_thi','an') DEFAULT 'hien_thi',
  `luot_xem` int(10) UNSIGNED DEFAULT 0,
  `luot_thich` int(10) UNSIGNED DEFAULT 0,
  `ma_nguoi_tao` int(10) UNSIGNED DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tu_vung`
--

CREATE TABLE `tu_vung` (
  `ma_tu_vung` int(10) UNSIGNED NOT NULL,
  `ma_bai_hoc` int(10) UNSIGNED NOT NULL,
  `tu_tieng_viet` varchar(100) NOT NULL,
  `tu_tieng_khmer` varchar(100) NOT NULL,
  `phien_am` varchar(150) DEFAULT NULL,
  `nghia` text DEFAULT NULL,
  `vi_du` text DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `file_am_thanh` varchar(255) DEFAULT NULL,
  `thu_tu` int(11) DEFAULT 0,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `van_hoa`
--

CREATE TABLE `van_hoa` (
  `ma_van_hoa` int(10) UNSIGNED NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `mo_ta_ngan` text DEFAULT NULL,
  `noi_dung` longtext NOT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `ma_danh_muc` int(10) UNSIGNED DEFAULT NULL,
  `tac_gia` varchar(100) DEFAULT NULL,
  `trang_thai` enum('nhap','xuat_ban','luu_tru') DEFAULT 'nhap',
  `noi_bat` tinyint(1) DEFAULT 0,
  `luot_xem` int(10) UNSIGNED DEFAULT 0,
  `ma_nguoi_tao` int(10) UNSIGNED DEFAULT NULL,
  `ma_nguoi_cap_nhat` int(10) UNSIGNED DEFAULT NULL,
  `ngay_xuat_ban` datetime DEFAULT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp(),
  `ngay_cap_nhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `van_hoa_tags`
--

CREATE TABLE `van_hoa_tags` (
  `ma_van_hoa` int(10) UNSIGNED NOT NULL,
  `ma_tag` int(10) UNSIGNED NOT NULL,
  `ngay_tao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Bẫy `van_hoa_tags`
--
DELIMITER $$
CREATE TRIGGER `trg_giam_su_dung_tag` AFTER DELETE ON `van_hoa_tags` FOR EACH ROW BEGIN
    UPDATE `tags` SET `so_lan_su_dung` = `so_lan_su_dung` - 1
    WHERE `ma_tag` = OLD.`ma_tag` AND `so_lan_su_dung` > 0;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_tang_su_dung_tag` AFTER INSERT ON `van_hoa_tags` FOR EACH ROW BEGIN
    UPDATE `tags` SET `so_lan_su_dung` = `so_lan_su_dung` + 1
    WHERE `ma_tag` = NEW.`ma_tag`;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `v_bai_viet_pho_bien`
-- (See below for the actual view)
--
CREATE TABLE `v_bai_viet_pho_bien` (
`ma_van_hoa` int(10) unsigned
,`tieu_de` varchar(255)
,`slug` varchar(300)
,`mo_ta_ngan` text
,`hinh_anh` varchar(255)
,`luot_xem` int(10) unsigned
,`ngay_xuat_ban` datetime
,`ten_danh_muc` varchar(100)
,`nguoi_tao` varchar(100)
);

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `v_bang_xep_hang_nguoi_dung`
-- (See below for the actual view)
--
CREATE TABLE `v_bang_xep_hang_nguoi_dung` (
`ma_nguoi_dung` int(10) unsigned
,`ten_dang_nhap` varchar(50)
,`ho_ten` varchar(100)
,`anh_dai_dien` varchar(255)
,`tong_diem` int(10) unsigned
,`cap_do` int(10) unsigned
,`xep_hang` bigint(21)
);

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `v_hoat_dong_gan_day`
-- (See below for the actual view)
--
CREATE TABLE `v_hoat_dong_gan_day` (
`ma_nhat_ky` int(10) unsigned
,`hanh_dong` varchar(50)
,`loai_doi_tuong` varchar(50)
,`mo_ta` text
,`ngay_tao` datetime
,`nguoi_thuc_hien` varchar(100)
,`loai_nguoi_dung` enum('quan_tri','nguoi_dung')
);

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `v_thong_ke_tong_quan`
-- (See below for the actual view)
--
CREATE TABLE `v_thong_ke_tong_quan` (
`tong_nguoi_dung` bigint(21)
,`nguoi_dung_hoat_dong` bigint(21)
,`tong_bai_viet` bigint(21)
,`bai_viet_xuat_ban` bigint(21)
,`tong_chua` bigint(21)
,`tong_le_hoi` bigint(21)
,`tong_bai_hoc` bigint(21)
,`tong_truyen` bigint(21)
,`tong_luot_xem_van_hoa` decimal(32,0)
,`tong_luot_xem_chua` decimal(32,0)
,`tong_luot_xem_truyen` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Cấu trúc cho view `v_bai_viet_pho_bien`
--
DROP TABLE IF EXISTS `v_bai_viet_pho_bien`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_bai_viet_pho_bien`  AS SELECT `v`.`ma_van_hoa` AS `ma_van_hoa`, `v`.`tieu_de` AS `tieu_de`, `v`.`slug` AS `slug`, `v`.`mo_ta_ngan` AS `mo_ta_ngan`, `v`.`hinh_anh` AS `hinh_anh`, `v`.`luot_xem` AS `luot_xem`, `v`.`ngay_xuat_ban` AS `ngay_xuat_ban`, `d`.`ten_danh_muc` AS `ten_danh_muc`, `q`.`ho_ten` AS `nguoi_tao` FROM ((`van_hoa` `v` left join `danh_muc` `d` on(`v`.`ma_danh_muc` = `d`.`ma_danh_muc`)) left join `quan_tri_vien` `q` on(`v`.`ma_nguoi_tao` = `q`.`ma_qtv`)) WHERE `v`.`trang_thai` = 'xuat_ban' ORDER BY `v`.`luot_xem` DESC LIMIT 0, 50 ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `v_bang_xep_hang_nguoi_dung`
--
DROP TABLE IF EXISTS `v_bang_xep_hang_nguoi_dung`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_bang_xep_hang_nguoi_dung`  AS SELECT `nguoi_dung`.`ma_nguoi_dung` AS `ma_nguoi_dung`, `nguoi_dung`.`ten_dang_nhap` AS `ten_dang_nhap`, `nguoi_dung`.`ho_ten` AS `ho_ten`, `nguoi_dung`.`anh_dai_dien` AS `anh_dai_dien`, `nguoi_dung`.`tong_diem` AS `tong_diem`, `nguoi_dung`.`cap_do` AS `cap_do`, rank() over ( order by `nguoi_dung`.`tong_diem` desc) AS `xep_hang` FROM `nguoi_dung` WHERE `nguoi_dung`.`trang_thai` = 'hoat_dong' ORDER BY `nguoi_dung`.`tong_diem` DESC LIMIT 0, 100 ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `v_hoat_dong_gan_day`
--
DROP TABLE IF EXISTS `v_hoat_dong_gan_day`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_hoat_dong_gan_day`  AS SELECT `nk`.`ma_nhat_ky` AS `ma_nhat_ky`, `nk`.`hanh_dong` AS `hanh_dong`, `nk`.`loai_doi_tuong` AS `loai_doi_tuong`, `nk`.`mo_ta` AS `mo_ta`, `nk`.`ngay_tao` AS `ngay_tao`, CASE WHEN `nk`.`loai_nguoi_dung` = 'quan_tri' THEN `q`.`ho_ten` WHEN `nk`.`loai_nguoi_dung` = 'nguoi_dung' THEN `nd`.`ho_ten` END AS `nguoi_thuc_hien`, `nk`.`loai_nguoi_dung` AS `loai_nguoi_dung` FROM ((`nhat_ky_hoat_dong` `nk` left join `quan_tri_vien` `q` on(`nk`.`ma_nguoi_dung` = `q`.`ma_qtv` and `nk`.`loai_nguoi_dung` = 'quan_tri')) left join `nguoi_dung` `nd` on(`nk`.`ma_nguoi_dung` = `nd`.`ma_nguoi_dung` and `nk`.`loai_nguoi_dung` = 'nguoi_dung')) ORDER BY `nk`.`ngay_tao` DESC LIMIT 0, 100 ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `v_thong_ke_tong_quan`
--
DROP TABLE IF EXISTS `v_thong_ke_tong_quan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_thong_ke_tong_quan`  AS SELECT (select count(0) from `nguoi_dung`) AS `tong_nguoi_dung`, (select count(0) from `nguoi_dung` where `nguoi_dung`.`trang_thai` = 'hoat_dong') AS `nguoi_dung_hoat_dong`, (select count(0) from `van_hoa`) AS `tong_bai_viet`, (select count(0) from `van_hoa` where `van_hoa`.`trang_thai` = 'xuat_ban') AS `bai_viet_xuat_ban`, (select count(0) from `chua_khmer`) AS `tong_chua`, (select count(0) from `le_hoi`) AS `tong_le_hoi`, (select count(0) from `bai_hoc`) AS `tong_bai_hoc`, (select count(0) from `truyen_dan_gian`) AS `tong_truyen`, (select sum(`van_hoa`.`luot_xem`) from `van_hoa`) AS `tong_luot_xem_van_hoa`, (select sum(`chua_khmer`.`luot_xem`) from `chua_khmer`) AS `tong_luot_xem_chua`, (select sum(`truyen_dan_gian`.`luot_xem`) from `truyen_dan_gian`) AS `tong_luot_xem_truyen` ;

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
  ADD KEY `ma_nguoi_tao` (`ma_nguoi_tao`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_cap_do` (`cap_do`),
  ADD KEY `idx_thu_tu` (`thu_tu`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_bai_hoc_cap_do_trang_thai` (`cap_do`,`trang_thai`);
ALTER TABLE `bai_hoc` ADD FULLTEXT KEY `idx_fulltext_search` (`tieu_de`,`mo_ta`);

--
-- Chỉ mục cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD PRIMARY KEY (`ma_binh_luan`),
  ADD KEY `ma_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `binh_luan_cha` (`binh_luan_cha`),
  ADD KEY `idx_doi_tuong` (`loai_doi_tuong`,`ma_doi_tuong`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_ngay_tao` (`ngay_tao`);

--
-- Chỉ mục cho bảng `cai_dat_he_thong`
--
ALTER TABLE `cai_dat_he_thong`
  ADD PRIMARY KEY (`ma_cai_dat`),
  ADD UNIQUE KEY `khoa` (`khoa`),
  ADD KEY `idx_khoa` (`khoa`),
  ADD KEY `idx_nhom` (`nhom`);

--
-- Chỉ mục cho bảng `chua_khmer`
--
ALTER TABLE `chua_khmer`
  ADD PRIMARY KEY (`ma_chua`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `ma_nguoi_tao` (`ma_nguoi_tao`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_tinh_thanh` (`tinh_thanh`),
  ADD KEY `idx_loai_chua` (`loai_chua`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_luot_xem` (`luot_xem`);
ALTER TABLE `chua_khmer` ADD FULLTEXT KEY `idx_fulltext_search` (`ten_chua`,`ten_tieng_khmer`,`mo_ta_ngan`);

--
-- Chỉ mục cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  ADD PRIMARY KEY (`ma_danh_muc`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `danh_muc_cha` (`danh_muc_cha`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_loai` (`loai`),
  ADD KEY `idx_trang_thai` (`trang_thai`);

--
-- Chỉ mục cho bảng `huy_hieu`
--
ALTER TABLE `huy_hieu`
  ADD PRIMARY KEY (`ma_huy_hieu`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_thu_tu` (`thu_tu`);

--
-- Chỉ mục cho bảng `le_hoi`
--
ALTER TABLE `le_hoi`
  ADD PRIMARY KEY (`ma_le_hoi`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `ma_nguoi_tao` (`ma_nguoi_tao`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_ngay_bat_dau` (`ngay_bat_dau`),
  ADD KEY `idx_luot_xem` (`luot_xem`);
ALTER TABLE `le_hoi` ADD FULLTEXT KEY `idx_fulltext_search` (`ten_le_hoi`,`ten_le_hoi_khmer`,`mo_ta`);

--
-- Chỉ mục cho bảng `lich_su_diem`
--
ALTER TABLE `lich_su_diem`
  ADD PRIMARY KEY (`ma_lich_su`),
  ADD KEY `idx_ma_nguoi_dung` (`ma_nguoi_dung`),
  ADD KEY `idx_loai_hoat_dong` (`loai_hoat_dong`),
  ADD KEY `idx_ngay_tao` (`ngay_tao`);

--
-- Chỉ mục cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`ma_nguoi_dung`),
  ADD UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_ten_dang_nhap` (`ten_dang_nhap`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_tong_diem` (`tong_diem`),
  ADD KEY `idx_cap_do` (`cap_do`),
  ADD KEY `idx_nguoi_dung_diem_cap_do` (`tong_diem`,`cap_do`);

--
-- Chỉ mục cho bảng `nguoi_dung_huy_hieu`
--
ALTER TABLE `nguoi_dung_huy_hieu`
  ADD PRIMARY KEY (`ma_nguoi_dung`,`ma_huy_hieu`),
  ADD KEY `ma_huy_hieu` (`ma_huy_hieu`),
  ADD KEY `idx_ngay_dat_duoc` (`ngay_dat_duoc`);

--
-- Chỉ mục cho bảng `nhat_ky_hoat_dong`
--
ALTER TABLE `nhat_ky_hoat_dong`
  ADD PRIMARY KEY (`ma_nhat_ky`),
  ADD KEY `idx_nguoi_dung` (`ma_nguoi_dung`,`loai_nguoi_dung`),
  ADD KEY `idx_hanh_dong` (`hanh_dong`),
  ADD KEY `idx_loai_doi_tuong` (`loai_doi_tuong`),
  ADD KEY `idx_ngay_tao` (`ngay_tao`);

--
-- Chỉ mục cho bảng `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  ADD PRIMARY KEY (`ma_qtv`),
  ADD UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_ten_dang_nhap` (`ten_dang_nhap`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_vai_tro` (`vai_tro`),
  ADD KEY `idx_trang_thai` (`trang_thai`);

--
-- Chỉ mục cho bảng `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`ma_tag`),
  ADD UNIQUE KEY `ten_tag` (`ten_tag`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_so_lan_su_dung` (`so_lan_su_dung`);

--
-- Chỉ mục cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD PRIMARY KEY (`ma_thong_bao`),
  ADD KEY `idx_ma_qtv` (`ma_qtv`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_ngay_tao` (`ngay_tao`);

--
-- Chỉ mục cho bảng `tien_trinh_hoc_tap`
--
ALTER TABLE `tien_trinh_hoc_tap`
  ADD PRIMARY KEY (`ma_tien_trinh`),
  ADD UNIQUE KEY `unique_user_lesson` (`ma_nguoi_dung`,`ma_bai_hoc`),
  ADD KEY `ma_bai_hoc` (`ma_bai_hoc`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_tien_do` (`tien_do`),
  ADD KEY `idx_tien_trinh_nguoi_dung_trang_thai` (`ma_nguoi_dung`,`trang_thai`);

--
-- Chỉ mục cho bảng `tin_nhan`
--
ALTER TABLE `tin_nhan`
  ADD PRIMARY KEY (`ma_tin_nhan`),
  ADD KEY `idx_nguoi_gui` (`ma_nguoi_gui`,`loai_nguoi_gui`),
  ADD KEY `idx_nguoi_nhan` (`ma_nguoi_nhan`,`loai_nguoi_nhan`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_ngay_tao` (`ngay_tao`);

--
-- Chỉ mục cho bảng `truyen_dan_gian`
--
ALTER TABLE `truyen_dan_gian`
  ADD PRIMARY KEY (`ma_truyen`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `ma_nguoi_tao` (`ma_nguoi_tao`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_the_loai` (`the_loai`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_luot_xem` (`luot_xem`),
  ADD KEY `idx_luot_thich` (`luot_thich`);
ALTER TABLE `truyen_dan_gian` ADD FULLTEXT KEY `idx_fulltext_search` (`tieu_de`,`tieu_de_khmer`,`tom_tat`,`noi_dung`);

--
-- Chỉ mục cho bảng `tu_vung`
--
ALTER TABLE `tu_vung`
  ADD PRIMARY KEY (`ma_tu_vung`),
  ADD KEY `idx_ma_bai_hoc` (`ma_bai_hoc`),
  ADD KEY `idx_thu_tu` (`thu_tu`);

--
-- Chỉ mục cho bảng `van_hoa`
--
ALTER TABLE `van_hoa`
  ADD PRIMARY KEY (`ma_van_hoa`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `ma_nguoi_tao` (`ma_nguoi_tao`),
  ADD KEY `ma_nguoi_cap_nhat` (`ma_nguoi_cap_nhat`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_trang_thai` (`trang_thai`),
  ADD KEY `idx_noi_bat` (`noi_bat`),
  ADD KEY `idx_luot_xem` (`luot_xem`),
  ADD KEY `idx_ngay_xuat_ban` (`ngay_xuat_ban`),
  ADD KEY `idx_van_hoa_trang_thai_ngay` (`trang_thai`,`ngay_xuat_ban`),
  ADD KEY `idx_van_hoa_danh_muc_trang_thai` (`ma_danh_muc`,`trang_thai`);
ALTER TABLE `van_hoa` ADD FULLTEXT KEY `idx_fulltext_search` (`tieu_de`,`mo_ta_ngan`,`noi_dung`);

--
-- Chỉ mục cho bảng `van_hoa_tags`
--
ALTER TABLE `van_hoa_tags`
  ADD PRIMARY KEY (`ma_van_hoa`,`ma_tag`),
  ADD KEY `idx_ma_tag` (`ma_tag`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `bai_hoc`
--
ALTER TABLE `bai_hoc`
  MODIFY `ma_bai_hoc` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  MODIFY `ma_binh_luan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `cai_dat_he_thong`
--
ALTER TABLE `cai_dat_he_thong`
  MODIFY `ma_cai_dat` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT cho bảng `chua_khmer`
--
ALTER TABLE `chua_khmer`
  MODIFY `ma_chua` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  MODIFY `ma_danh_muc` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `huy_hieu`
--
ALTER TABLE `huy_hieu`
  MODIFY `ma_huy_hieu` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `le_hoi`
--
ALTER TABLE `le_hoi`
  MODIFY `ma_le_hoi` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `lich_su_diem`
--
ALTER TABLE `lich_su_diem`
  MODIFY `ma_lich_su` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `ma_nguoi_dung` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `nhat_ky_hoat_dong`
--
ALTER TABLE `nhat_ky_hoat_dong`
  MODIFY `ma_nhat_ky` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT cho bảng `quan_tri_vien`
--
ALTER TABLE `quan_tri_vien`
  MODIFY `ma_qtv` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `tags`
--
ALTER TABLE `tags`
  MODIFY `ma_tag` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  MODIFY `ma_thong_bao` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `tien_trinh_hoc_tap`
--
ALTER TABLE `tien_trinh_hoc_tap`
  MODIFY `ma_tien_trinh` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `tin_nhan`
--
ALTER TABLE `tin_nhan`
  MODIFY `ma_tin_nhan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT cho bảng `truyen_dan_gian`
--
ALTER TABLE `truyen_dan_gian`
  MODIFY `ma_truyen` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `tu_vung`
--
ALTER TABLE `tu_vung`
  MODIFY `ma_tu_vung` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `van_hoa`
--
ALTER TABLE `van_hoa`
  MODIFY `ma_van_hoa` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bai_hoc`
--
ALTER TABLE `bai_hoc`
  ADD CONSTRAINT `bai_hoc_ibfk_1` FOREIGN KEY (`ma_danh_muc`) REFERENCES `danh_muc` (`ma_danh_muc`) ON DELETE SET NULL,
  ADD CONSTRAINT `bai_hoc_ibfk_2` FOREIGN KEY (`ma_nguoi_tao`) REFERENCES `quan_tri_vien` (`ma_qtv`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `binh_luan`
--
ALTER TABLE `binh_luan`
  ADD CONSTRAINT `binh_luan_ibfk_1` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE,
  ADD CONSTRAINT `binh_luan_ibfk_2` FOREIGN KEY (`binh_luan_cha`) REFERENCES `binh_luan` (`ma_binh_luan`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `chua_khmer`
--
ALTER TABLE `chua_khmer`
  ADD CONSTRAINT `chua_khmer_ibfk_1` FOREIGN KEY (`ma_nguoi_tao`) REFERENCES `quan_tri_vien` (`ma_qtv`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  ADD CONSTRAINT `danh_muc_ibfk_1` FOREIGN KEY (`danh_muc_cha`) REFERENCES `danh_muc` (`ma_danh_muc`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `le_hoi`
--
ALTER TABLE `le_hoi`
  ADD CONSTRAINT `le_hoi_ibfk_1` FOREIGN KEY (`ma_nguoi_tao`) REFERENCES `quan_tri_vien` (`ma_qtv`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `lich_su_diem`
--
ALTER TABLE `lich_su_diem`
  ADD CONSTRAINT `lich_su_diem_ibfk_1` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `nguoi_dung_huy_hieu`
--
ALTER TABLE `nguoi_dung_huy_hieu`
  ADD CONSTRAINT `nguoi_dung_huy_hieu_ibfk_1` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE,
  ADD CONSTRAINT `nguoi_dung_huy_hieu_ibfk_2` FOREIGN KEY (`ma_huy_hieu`) REFERENCES `huy_hieu` (`ma_huy_hieu`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thong_bao`
--
ALTER TABLE `thong_bao`
  ADD CONSTRAINT `thong_bao_ibfk_1` FOREIGN KEY (`ma_qtv`) REFERENCES `quan_tri_vien` (`ma_qtv`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tien_trinh_hoc_tap`
--
ALTER TABLE `tien_trinh_hoc_tap`
  ADD CONSTRAINT `tien_trinh_hoc_tap_ibfk_1` FOREIGN KEY (`ma_nguoi_dung`) REFERENCES `nguoi_dung` (`ma_nguoi_dung`) ON DELETE CASCADE,
  ADD CONSTRAINT `tien_trinh_hoc_tap_ibfk_2` FOREIGN KEY (`ma_bai_hoc`) REFERENCES `bai_hoc` (`ma_bai_hoc`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `truyen_dan_gian`
--
ALTER TABLE `truyen_dan_gian`
  ADD CONSTRAINT `truyen_dan_gian_ibfk_1` FOREIGN KEY (`ma_nguoi_tao`) REFERENCES `quan_tri_vien` (`ma_qtv`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `tu_vung`
--
ALTER TABLE `tu_vung`
  ADD CONSTRAINT `tu_vung_ibfk_1` FOREIGN KEY (`ma_bai_hoc`) REFERENCES `bai_hoc` (`ma_bai_hoc`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `van_hoa`
--
ALTER TABLE `van_hoa`
  ADD CONSTRAINT `van_hoa_ibfk_1` FOREIGN KEY (`ma_danh_muc`) REFERENCES `danh_muc` (`ma_danh_muc`) ON DELETE SET NULL,
  ADD CONSTRAINT `van_hoa_ibfk_2` FOREIGN KEY (`ma_nguoi_tao`) REFERENCES `quan_tri_vien` (`ma_qtv`) ON DELETE SET NULL,
  ADD CONSTRAINT `van_hoa_ibfk_3` FOREIGN KEY (`ma_nguoi_cap_nhat`) REFERENCES `quan_tri_vien` (`ma_qtv`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `van_hoa_tags`
--
ALTER TABLE `van_hoa_tags`
  ADD CONSTRAINT `van_hoa_tags_ibfk_1` FOREIGN KEY (`ma_van_hoa`) REFERENCES `van_hoa` (`ma_van_hoa`) ON DELETE CASCADE,
  ADD CONSTRAINT `van_hoa_tags_ibfk_2` FOREIGN KEY (`ma_tag`) REFERENCES `tags` (`ma_tag`) ON DELETE CASCADE;

DELIMITER $$
--
-- Sự kiện
--
CREATE DEFINER=`root`@`localhost` EVENT `evt_xoa_thong_bao_cu` ON SCHEDULE EVERY 1 DAY STARTS '2025-11-29 09:15:16' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM `thong_bao`
    WHERE `trang_thai` = 'da_doc'
    AND `ngay_tao` < DATE_SUB(NOW(), INTERVAL 90 DAY)$$

CREATE DEFINER=`root`@`localhost` EVENT `evt_xoa_nhat_ky_cu` ON SCHEDULE EVERY 1 WEEK STARTS '2025-11-29 09:15:16' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM `nhat_ky_hoat_dong`
    WHERE `ngay_tao` < DATE_SUB(NOW(), INTERVAL 180 DAY)$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
