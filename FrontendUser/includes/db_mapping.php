<?php
/**
 * Database Column Mapping
 * Mapping giữa tên cột trong code và database thực tế
 * 
 * Database: van_hoa_khmer
 * 
 * BẢNG NGUOI_DUNG:
 * - ma_nguoi_dung (PK) - thay vì id
 * - ten_dang_nhap
 * - email
 * - mat_khau
 * - ho_ten
 * - ngay_sinh
 * - gioi_tinh
 * - so_dien_thoai
 * - anh_dai_dien - thay vì avatar
 * - tong_diem - thay vì diem
 * - cap_do
 * - ngon_ngu
 * - trang_thai: 'hoat_dong', 'khoa', 'cho_xac_thuc'
 * - ngay_tao
 * - lan_dang_nhap_cuoi
 * 
 * BẢNG VAN_HOA:
 * - ma_van_hoa (PK)
 * - tieu_de
 * - tieu_de_khmer
 * - slug
 * - tom_tat
 * - noi_dung
 * - hinh_anh_chinh - thay vì hinh_anh
 * - thu_vien_anh
 * - danh_muc
 * - tac_gia
 * - nguon_tham_khao
 * - luot_xem
 * - noi_bat
 * - trang_thai: 'nhap', 'xuat_ban', 'luu_tru'
 * - ma_nguoi_tao
 * - ngay_xuat_ban
 * - ngay_tao
 * - ngay_cap_nhat
 * 
 * BẢNG CHUA_KHMER:
 * - ma_chua (PK)
 * - ten_chua
 * - ten_tieng_khmer
 * - slug
 * - dia_chi
 * - tinh_thanh
 * - quan_huyen
 * - kinh_do
 * - vi_do
 * - loai_chua
 * - so_dien_thoai
 * - email
 * - website
 * - mo_ta_ngan
 * - lich_su
 * - hinh_anh_chinh
 * - thu_vien_anh
 * - nam_thanh_lap
 * - so_nha_su
 * - luot_xem
 * - trang_thai: 'hoat_dong', 'ngung_hoat_dong'
 * - ma_nguoi_tao
 * - ngay_tao
 * - ngay_cap_nhat
 * 
 * BẢNG LE_HOI:
 * - ma_le_hoi (PK)
 * - ten_le_hoi
 * - ten_le_hoi_khmer
 * - slug
 * - mo_ta
 * - noi_dung
 * - ngay_bat_dau
 * - ngay_ket_thuc
 * - ngay_dien_ra
 * - dia_diem
 * - tinh_thanh
 * - anh_dai_dien - thay vì hinh_anh
 * - thu_vien_anh
 * - y_nghia
 * - nguon_goc
 * - loai_le_hoi
 * - luot_xem
 * - trang_thai: 'hien_thi', 'an'
 * - ma_nguoi_tao
 * - ngay_tao
 * - ngay_cap_nhat
 * 
 * BẢNG BAI_HOC:
 * - ma_bai_hoc (PK)
 * - ma_danh_muc
 * - tieu_de
 * - slug
 * - mo_ta
 * - noi_dung
 * - cap_do: 'co_ban', 'trung_cap', 'nang_cao'
 * - thu_tu
 * - diem_thuong - thay vì diem
 * - thoi_luong
 * - hinh_anh
 * - video_url
 * - file_am_thanh
 * - luot_hoc
 * - trang_thai: 'xuat_ban', 'nhap', 'an'
 * - ma_nguoi_tao
 * - ngay_tao
 * - ngay_cap_nhat
 * 
 * BẢNG TU_VUNG:
 * - ma_tu_vung (PK)
 * - ma_bai_hoc
 * - tu_khmer
 * - phien_am
 * - nghia_tieng_viet - thay vì tu_viet
 * - vi_du
 * - file_am_thanh - thay vì audio
 * - anh_minh_hoa
 * - loai_tu
 * - ghi_chu
 * - thu_tu
 * - ngay_tao
 * 
 * BẢNG TRUYEN_DAN_GIAN:
 * - ma_truyen (PK)
 * - tieu_de
 * - tieu_de_khmer
 * - slug
 * - tom_tat
 * - noi_dung
 * - anh_dai_dien - thay vì hinh_anh
 * - file_audio - thay vì audio
 * - the_loai
 * - do_tuoi
 * - nguon_goc
 * - tac_gia
 * - thoi_luong_doc - thay vì thoi_gian_doc
 * - luot_xem
 * - luot_thich
 * - trang_thai: 'hien_thi', 'an'
 * - ma_nguoi_tao
 * - ngay_tao
 * - ngay_cap_nhat
 * 
 * BẢNG BINH_LUAN:
 * - ma_binh_luan (PK)
 * - ma_nguoi_dung
 * - loai_doi_tuong - thay vì loai
 * - ma_doi_tuong - thay vì noi_dung_id
 * - noi_dung
 * - ma_binh_luan_cha
 * - luot_thich
 * - trang_thai: 'cho_duyet', 'da_duyet', 'tu_choi'
 * - ngay_tao
 * 
 * BẢNG YEU_THICH:
 * - ma_yeu_thich (PK)
 * - ma_nguoi_dung
 * - loai_doi_tuong
 * - ma_doi_tuong
 * - ngay_tao
 * 
 * BẢNG TIEN_TRINH_HOC_TAP:
 * - ma_tien_trinh (PK)
 * - ma_nguoi_dung
 * - ma_bai_hoc
 * - trang_thai: 'chua_hoc', 'dang_hoc', 'hoan_thanh'
 * - diem_so
 * - thoi_gian_hoc
 * - so_lan_hoc
 * - ngay_bat_dau
 * - ngay_hoan_thanh
 * - ngay_cap_nhat
 * 
 * BẢNG HUY_HIEU:
 * - ma_huy_hieu (PK)
 * - ten_huy_hieu
 * - ten_huy_hieu_khmer
 * - mo_ta
 * - dieu_kien
 * - icon
 * - mau_sac
 * - diem_thuong
 * - thu_tu
 * - trang_thai
 * - ngay_tao
 * 
 * BẢNG HUY_HIEU_NGUOI_DUNG:
 * - ma_hh_nguoi_dung (PK)
 * - ma_nguoi_dung
 * - ma_huy_hieu
 * - ngay_dat_duoc
 * 
 * BẢNG NHAT_KY_HOAT_DONG:
 * - ma_hoat_dong (PK)
 * - ma_nguoi_dung
 * - loai_nguoi_dung
 * - hanh_dong
 * - loai_doi_tuong
 * - ma_doi_tuong
 * - mo_ta
 * - ip_address
 * - user_agent
 * - ngay_tao
 */
?>
