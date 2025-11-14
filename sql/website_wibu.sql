-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 14, 2025 lúc 08:59 PM
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
('Bộ đồ cosplay Shinobu', '_imgProduct/cosplay/1763141238_shinobu.webp', NULL, 150000, 10, 1, 15, 3, 15),
('Đồ cosplay yuki', '_imgProduct/cosplay/1763142034_yuki.jpeg', NULL, 600000, 20, 10, 5, 3, 16);

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
(1, 0, 100, 0);

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
(0, 440000, 1, 1);

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
(4, 0, 'mohinh', 8, 1, 440000, 1);

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
('Naruto', '_imgProduct/magma/1763141151_âruto.jpg', '', 34000, 200, 20, 10, 2, 9);

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
('Luffy ', '_imgProduct/mohinh/1763141312_luffy_figure.jpg', '', 550000, 5, 3, 20, 1, 8);

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
  `PasswordUser` varchar(255) NOT NULL,
  `role` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`IdUser`, `SDT`, `NameUser`, `EmailUser`, `PasswordUser`, `role`) VALUES
(1, '0902846205', 'Chềnh Hưng Thọ', 'chenhhungtho01@gmail.com', '$2y$10$YSlwi/3IOZOh8TVOYLzdbuMXEo2IgEe33OF/s8SewA7ydFX7BiWBG', 0),
(12, '0123456789', 'Đặng Anh Duy', 'chungtabanguoi@gmail.com', '$2y$10$nBp2ddB6DKMaLH9t3nCTIOzDLFk9kjw/.H0SYSdOmI6CQ6e1c1ZWW', 1);

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
(8, 1, 7, 'mohinh'),
(7, 1, 8, 'mohinh');

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
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `giohang_chitiet`
--
ALTER TABLE `giohang_chitiet`
  MODIFY `IdGioHangChiTiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `magma`
--
ALTER TABLE `magma`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `mohinh`
--
ALTER TABLE `mohinh`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `theloai_sanpham`
--
ALTER TABLE `theloai_sanpham`
  MODIFY `IdTheLoai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `IdUser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `yeuthich`
--
ALTER TABLE `yeuthich`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `cosplay`
--
ALTER TABLE `cosplay`
  ADD CONSTRAINT `cosplay_ibfk_1` FOREIGN KEY (`TheLoai`) REFERENCES `theloai_sanpham` (`IdTheLoai`);

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
-- Các ràng buộc cho bảng `yeuthich`
--
ALTER TABLE `yeuthich`
  ADD CONSTRAINT `fk_yeuthich_user` FOREIGN KEY (`IdUser`) REFERENCES `users` (`IdUser`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
