-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 16, 2025 lúc 04:31 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `website_wibu`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `anime`
--

CREATE TABLE `anime` (
  `NameAnime` varchar(255) NOT NULL,
  `Img` varchar(255) NOT NULL,
  `MoTa` varchar(255) NOT NULL,
  `DuongDanAnime` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cosplay`
--

CREATE TABLE `cosplay` (
  `Name` varchar(255) NOT NULL,
  `Img1` varchar(255) NOT NULL,
  `Img2` varchar(255) DEFAULT NULL,
  `Gia` float NOT NULL,
  `SoLuongTonKho` int(11) NOT NULL,
  `SoLuongDaBan` int(11) NOT NULL DEFAULT 0,
  `Sale` int(11) NOT NULL,
  `TheLoai` int(11) NOT NULL,
  `ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cosplay`
--

INSERT INTO `cosplay` (`Name`, `Img1`, `Img2`, `Gia`, `SoLuongTonKho`, `SoLuongDaBan`, `Sale`, `TheLoai`, `ID`) VALUES
('Bộ đồ cosplay Shinobu', '_imgProduct/cosplay/1763303928_shinobu.webp', NULL, 150000, 10, 1, 15, 3, 15),
('Bộ Trang Phục Cosplay Nhân Vật Anime Yuuki Asuna Trong Sword Art Online Dành Cho Nữ', '_imgProduct/cosplay/1763303986_asuna.jpg', NULL, 600000, 20, 10, 5, 3, 16),
('Bộ đồ Cosplay Anya', '_imgProduct/cosplay/1763303698_anya.jpg', NULL, 400000, 10, 5, 10, 3, 17),
('Đồ cosplay Shinobu ', '_imgProduct/cosplay/1763303777_shinobuu.jpg', NULL, 200000, 2, 1, 0, 3, 18),
('Mua Maid Hầu Gái Ram/Rem, Cosplay Hầu Gái, Nhân Vật Hoạt Hình Giảm Giá - Yeep', '_imgProduct/cosplay/1763303866_rem.jpg', NULL, 600000, 40, 3, 25, 3, 19),
('Mantian Pure White Symphony Riêng Yuki Nữ sinh Đồng phục học sinh Trang phục Cosplay', '_imgProduct/cosplay/1763304024_yukii.avif', NULL, 12000000, 3, 2, 5, 3, 20);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donhang`
--

CREATE TABLE `donhang` (
  `IdDonHang` int(11) NOT NULL,
  `IdUser` int(11) NOT NULL,
  `MaDonHang` varchar(50) NOT NULL,
  `TenNguoiNhan` varchar(100) NOT NULL,
  `SDTNguoiNhan` varchar(11) NOT NULL,
  `DiaChiGiaoHang` text NOT NULL,
  `TinhThanh` varchar(100) DEFAULT NULL,
  `QuocGia` varchar(50) DEFAULT 'Việt Nam',
  `PhuongThucThanhToan` varchar(50) NOT NULL,
  `TongTien` int(11) NOT NULL,
  `PhiVanChuyen` int(11) DEFAULT 50000,
  `GiamGia` int(11) DEFAULT 0,
  `TongCong` int(11) NOT NULL,
  `TrangThai` varchar(50) DEFAULT 'Chờ xử lý',
  `GhiChu` text DEFAULT NULL,
  `NgayTao` datetime DEFAULT current_timestamp(),
  `NgayCapNhat` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donhang_chitiet`
--

CREATE TABLE `donhang_chitiet` (
  `IdDonHangChiTiet` int(11) NOT NULL,
  `IdDonHang` int(11) NOT NULL,
  `LoaiSanPham` varchar(50) NOT NULL,
  `IdSanPham` int(11) NOT NULL,
  `TenSanPham` varchar(255) NOT NULL,
  `SoLuong` int(11) NOT NULL,
  `Gia` int(11) NOT NULL,
  `ThanhTien` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `expuser`
--

CREATE TABLE `expuser` (
  `IdUser` int(11) NOT NULL,
  `exp` int(11) NOT NULL DEFAULT 0,
  `max_exp` int(11) NOT NULL DEFAULT 100,
  `lv_user` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `expuser`
--

INSERT INTO `expuser` (`IdUser`, `exp`, `max_exp`, `lv_user`) VALUES
(12, 0, 100, 0),
(1, 0, 100, 0),
(14, 0, 100, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `giohang`
--

CREATE TABLE `giohang` (
  `IDGioHang` int(11) NOT NULL,
  `TongGiaTien` int(11) NOT NULL,
  `TongSoLuong` int(11) NOT NULL,
  `IdUser` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `giohang`
--

INSERT INTO `giohang` (`IDGioHang`, `TongGiaTien`, `TongSoLuong`, `IdUser`) VALUES
(0, 25081000, 2, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `giohang_chitiet`
--

CREATE TABLE `giohang_chitiet` (
  `IdGioHangChiTiet` int(11) NOT NULL,
  `IdGioHang` int(11) NOT NULL,
  `LoaiSanPham` varchar(50) NOT NULL,
  `IdSanPham` int(11) NOT NULL,
  `SoLuong` int(11) NOT NULL,
  `Gia` int(11) NOT NULL,
  `IdTheLoai` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `giohang_chitiet`
--

INSERT INTO `giohang_chitiet` (`IdGioHangChiTiet`, `IdGioHang`, `LoaiSanPham`, `IdSanPham`, `SoLuong`, `Gia`, `IdTheLoai`) VALUES
(23, 0, 'mohinh', 14, 1, 25000000, 1),
(24, 0, 'mohinh', 12, 1, 81000, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `magma`
--

CREATE TABLE `magma` (
  `Name` text NOT NULL,
  `Img1` text NOT NULL,
  `Img2` text NOT NULL,
  `Gia` float NOT NULL,
  `SoLuongTonKho` int(11) NOT NULL,
  `SoLuongDaBan` int(11) NOT NULL,
  `Sale` int(11) NOT NULL,
  `TheLoai` int(11) NOT NULL,
  `ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `magma`
--

INSERT INTO `magma` (`Name`, `Img1`, `Img2`, `Gia`, `SoLuongTonKho`, `SoLuongDaBan`, `Sale`, `TheLoai`, `ID`) VALUES
('Dược Sư tự sự', '_imgProduct/magma/1763141113_maomao.jpg', '', 20000, 233, 20, 10, 2, 8),
('Naruto', '_imgProduct/magma/1763141151_âruto.jpg', '', 34000, 200, 20, 10, 2, 9),
('Arya Vol 4', '_imgProduct/magma/1763194988_arya 4.jpg', '', 135000, 120, 56, 0, 2, 10),
('Học viện Anh hùng - Tập 1', '_imgProduct/magma/1763303514_deku.jpg', '', 40000, 1200, 350, 0, 2, 11),
('Thanh gươm diệt quỷ Tập 15', '_imgProduct/magma/1763304108_demon.jpg', '', 36000, 2100, 660, 0, 2, 12),
('Chú thuật hồi chiến - Chapter 19', '_imgProduct/magma/1763303641_jjk.jpg', '', 40000, 300, 910, 0, 2, 13);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `mohinh`
--

CREATE TABLE `mohinh` (
  `Name` varchar(255) NOT NULL,
  `Img1` varchar(255) NOT NULL,
  `Img2` varchar(255) NOT NULL,
  `Gia` float NOT NULL,
  `SoLuongTonKho` int(11) NOT NULL,
  `SoLuongDaBan` int(11) NOT NULL DEFAULT 0,
  `Sale` int(11) NOT NULL,
  `TheLoai` int(11) NOT NULL,
  `ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `mohinh`
--

INSERT INTO `mohinh` (`Name`, `Img1`, `Img2`, `Gia`, `SoLuongTonKho`, `SoLuongDaBan`, `Sale`, `TheLoai`, `ID`) VALUES
('Mô Hình Roronoa Zoro - Asura Cửu Kiếm (Có LED)', '_imgProduct/mohinh/Screenshot 2024-11-17 215458.png', '', 1800000, 50, 15, 15, 1, 7),
('Luffy ', '_imgProduct/mohinh/1763141312_luffy_figure.jpg', '', 550000, 5, 3, 20, 1, 8),
('Mô hình Arya', '_imgProduct/mohinh/1763194923_arya firuge.jpg', '', 15000000, 10, 0, 50, 1, 9),
('Mô hình One Piece Nhân vật Monkey D Luffy Gear 5 Trạng Thái Thức Tỉnh Nika thần Mặt Trời', '_imgProduct/mohinh/1763303193_lufffy.jpg', '', 500000, 10, 3, 0, 1, 10),
('Set 6 Nhân Vật One Piece phong cách Chibi dễ thương No.1', '_imgProduct/mohinh/1763303232_chibi.jpg', '', 90000, 20, 10, 5, 1, 11),
('Mô hình Anime Kimetsu No Yaiba Trọn bộ 6 Nhân Vật Chibi', '_imgProduct/mohinh/1763303282_kamado.jpg', '', 90000, 505, 20, 10, 1, 12),
('Mô hình Anime Figure Uchiha Madara Kimono Trắng', '_imgProduct/mohinh/1763303340_madara.jpg', '', 1500000, 5, 2, 0, 1, 13),
('Mô hình Changli - Wuthering Wave', '_imgProduct/mohinh/1763303450_changli.jpg', '', 25000000, 4, 0, 0, 1, 14);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `theloai_sanpham`
--

CREATE TABLE `theloai_sanpham` (
  `IdTheLoai` int(11) NOT NULL,
  `TheLoai` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `theloai_sanpham`
--

INSERT INTO `theloai_sanpham` (`IdTheLoai`, `TheLoai`) VALUES
(1, '[Mô hình]'),
(2, '[Truyện tranh]'),
(3, '[Trang phục cosplay]');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `IdUser` int(11) NOT NULL,
  `SDT` varchar(11) NOT NULL,
  `NameUser` varchar(50) NOT NULL,
  `EmailUser` varchar(50) NOT NULL,
  `GioiTinh` varchar(10) DEFAULT NULL,
  `DiaChi` varchar(255) DEFAULT NULL,
  `QuocGia` varchar(50) DEFAULT 'Việt Nam',
  `TinhThanh` varchar(100) DEFAULT NULL,
  `NgaySinh` date DEFAULT NULL,
  `PasswordUser` varchar(255) NOT NULL,
  `role` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`IdUser`, `SDT`, `NameUser`, `EmailUser`, `GioiTinh`, `DiaChi`, `QuocGia`, `TinhThanh`, `NgaySinh`, `PasswordUser`, `role`) VALUES
(1, '0902846205', 'Chềnh Hưng Thọ', 'chenhhungtho01@gmail.com', 'Nam', 'hcm', 'Việt Nam', 'Bà Rịa - Vũng Tàu', '2005-01-01', '$2y$10$YSlwi/3IOZOh8TVOYLzdbuMXEo2IgEe33OF/s8SewA7ydFX7BiWBG', 0),
(12, '0123456789', 'Đặng Anh Duy', 'chungtabanguoi@gmail.com', NULL, NULL, 'Việt Nam', NULL, NULL, '$2y$10$nBp2ddB6DKMaLH9t3nCTIOzDLFk9kjw/.H0SYSdOmI6CQ6e1c1ZWW', 1),
(14, '0234567899', 'Nguyễn Thành Đạt', 'vfqqbt8182@chedoden.com', NULL, NULL, 'Việt Nam', NULL, NULL, '$2y$10$vLC/4xeXTGRnSL23raO37.xplCML.ImVSvSou5eeUcgNQnbPvCnmy', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_voucher`
--

CREATE TABLE `user_voucher` (
  `Id` int(11) NOT NULL,
  `IdUser` int(11) NOT NULL,
  `IdVoucher` int(11) NOT NULL,
  `MaVoucher` varchar(50) NOT NULL,
  `DaSuDung` tinyint(1) DEFAULT 0,
  `NgayNhan` datetime DEFAULT current_timestamp(),
  `NgaySuDung` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `voucher`
--

CREATE TABLE `voucher` (
  `IdVoucher` int(11) NOT NULL,
  `MaVoucher` varchar(50) NOT NULL,
  `TenVoucher` varchar(255) NOT NULL,
  `MoTa` text DEFAULT NULL,
  `PhanTramGiam` int(11) NOT NULL,
  `GiaTriGiamToiDa` int(11) DEFAULT NULL,
  `GiaTriDonHangToiThieu` int(11) DEFAULT 0,
  `SoLuong` int(11) NOT NULL DEFAULT 0,
  `NgayBatDau` date NOT NULL,
  `NgayKetThuc` date NOT NULL,
  `TrangThai` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `voucher`
--

INSERT INTO `voucher` (`IdVoucher`, `MaVoucher`, `TenVoucher`, `MoTa`, `PhanTramGiam`, `GiaTriGiamToiDa`, `GiaTriDonHangToiThieu`, `SoLuong`, `NgayBatDau`, `NgayKetThuc`, `TrangThai`) VALUES
(1, 'WELCOME10', 'Chào mừng thành viên mới', 'Giảm 10% cho đơn hàng đầu tiên', 10, 50000, 100000, 100, '2025-11-16', '2026-11-16', 1),
(2, 'SALE20', 'Giảm giá 20%', 'Giảm 20% cho đơn hàng từ 500.000đ', 20, 200000, 500000, 50, '2025-11-16', '2026-05-16', 1),
(3, 'VIP30', 'Voucher VIP', 'Giảm 30% cho khách hàng VIP', 30, 300000, 1000000, 20, '2025-11-16', '2026-11-16', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `yeuthich`
--

CREATE TABLE `yeuthich` (
  `ID` int(11) NOT NULL,
  `IdUser` int(11) NOT NULL,
  `IDSanPham` int(11) NOT NULL,
  `LoaiSanPham` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `yeuthich`
--

INSERT INTO `yeuthich` (`ID`, `IdUser`, `IDSanPham`, `LoaiSanPham`) VALUES
(13, 1, 8, 'mohinh'),
(12, 1, 9, 'mohinh'),
(16, 1, 11, 'mohinh'),
(17, 1, 12, 'mohinh'),
(14, 1, 13, 'mohinh'),
(15, 1, 14, 'mohinh');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `cosplay`
--
ALTER TABLE `cosplay`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `TheLoai` (`TheLoai`);

--
-- Chỉ mục cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`IdDonHang`),
  ADD UNIQUE KEY `MaDonHang` (`MaDonHang`),
  ADD KEY `IdUser` (`IdUser`);

--
-- Chỉ mục cho bảng `donhang_chitiet`
--
ALTER TABLE `donhang_chitiet`
  ADD PRIMARY KEY (`IdDonHangChiTiet`),
  ADD KEY `IdDonHang` (`IdDonHang`);

--
-- Chỉ mục cho bảng `expuser`
--
ALTER TABLE `expuser`
  ADD KEY `IdUser` (`IdUser`);

--
-- Chỉ mục cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD PRIMARY KEY (`IDGioHang`),
  ADD KEY `IdUser` (`IdUser`);

--
-- Chỉ mục cho bảng `giohang_chitiet`
--
ALTER TABLE `giohang_chitiet`
  ADD PRIMARY KEY (`IdGioHangChiTiet`),
  ADD KEY `IdTheLoai` (`IdTheLoai`),
  ADD KEY `IdGioHang` (`IdGioHang`);

--
-- Chỉ mục cho bảng `magma`
--
ALTER TABLE `magma`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `TheLoai` (`TheLoai`);

--
-- Chỉ mục cho bảng `mohinh`
--
ALTER TABLE `mohinh`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `TheLoai` (`TheLoai`);

--
-- Chỉ mục cho bảng `theloai_sanpham`
--
ALTER TABLE `theloai_sanpham`
  ADD PRIMARY KEY (`IdTheLoai`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`IdUser`);

--
-- Chỉ mục cho bảng `user_voucher`
--
ALTER TABLE `user_voucher`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `IdUser` (`IdUser`),
  ADD KEY `IdVoucher` (`IdVoucher`);

--
-- Chỉ mục cho bảng `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`IdVoucher`),
  ADD UNIQUE KEY `MaVoucher` (`MaVoucher`);

--
-- Chỉ mục cho bảng `yeuthich`
--
ALTER TABLE `yeuthich`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `user_product_unique` (`IdUser`,`IDSanPham`,`LoaiSanPham`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `cosplay`
--
ALTER TABLE `cosplay`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `donhang`
--
ALTER TABLE `donhang`
  MODIFY `IdDonHang` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `donhang_chitiet`
--
ALTER TABLE `donhang_chitiet`
  MODIFY `IdDonHangChiTiet` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `giohang_chitiet`
--
ALTER TABLE `giohang_chitiet`
  MODIFY `IdGioHangChiTiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `magma`
--
ALTER TABLE `magma`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `mohinh`
--
ALTER TABLE `mohinh`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `theloai_sanpham`
--
ALTER TABLE `theloai_sanpham`
  MODIFY `IdTheLoai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `IdUser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `user_voucher`
--
ALTER TABLE `user_voucher`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `voucher`
--
ALTER TABLE `voucher`
  MODIFY `IdVoucher` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `yeuthich`
--
ALTER TABLE `yeuthich`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `cosplay`
--
ALTER TABLE `cosplay`
  ADD CONSTRAINT `cosplay_ibfk_1` FOREIGN KEY (`TheLoai`) REFERENCES `theloai_sanpham` (`IdTheLoai`);

--
-- Các ràng buộc cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD CONSTRAINT `donhang_ibfk_1` FOREIGN KEY (`IdUser`) REFERENCES `users` (`IdUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `donhang_chitiet`
--
ALTER TABLE `donhang_chitiet`
  ADD CONSTRAINT `donhang_chitiet_ibfk_1` FOREIGN KEY (`IdDonHang`) REFERENCES `donhang` (`IdDonHang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `expuser`
--
ALTER TABLE `expuser`
  ADD CONSTRAINT `expuser_ibfk_1` FOREIGN KEY (`IdUser`) REFERENCES `users` (`IdUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD CONSTRAINT `giohang_ibfk_1` FOREIGN KEY (`IdUser`) REFERENCES `users` (`IdUser`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `giohang_chitiet`
--
ALTER TABLE `giohang_chitiet`
  ADD CONSTRAINT `giohang_chitiet_ibfk_1` FOREIGN KEY (`IdTheLoai`) REFERENCES `theloai_sanpham` (`IdTheLoai`),
  ADD CONSTRAINT `giohang_chitiet_ibfk_2` FOREIGN KEY (`IdGioHang`) REFERENCES `giohang` (`IDGioHang`);

--
-- Các ràng buộc cho bảng `magma`
--
ALTER TABLE `magma`
  ADD CONSTRAINT `magma_ibfk_1` FOREIGN KEY (`TheLoai`) REFERENCES `theloai_sanpham` (`IdTheLoai`);

--
-- Các ràng buộc cho bảng `mohinh`
--
ALTER TABLE `mohinh`
  ADD CONSTRAINT `mohinh_ibfk_1` FOREIGN KEY (`TheLoai`) REFERENCES `theloai_sanpham` (`IdTheLoai`);

--
-- Các ràng buộc cho bảng `user_voucher`
--
ALTER TABLE `user_voucher`
  ADD CONSTRAINT `user_voucher_ibfk_1` FOREIGN KEY (`IdUser`) REFERENCES `users` (`IdUser`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_voucher_ibfk_2` FOREIGN KEY (`IdVoucher`) REFERENCES `voucher` (`IdVoucher`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `yeuthich`
--
ALTER TABLE `yeuthich`
  ADD CONSTRAINT `fk_yeuthich_user` FOREIGN KEY (`IdUser`) REFERENCES `users` (`IdUser`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
