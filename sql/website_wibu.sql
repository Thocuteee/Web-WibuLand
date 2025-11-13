-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 16, 2024 lúc 03:29 AM
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
(3, 0, 100, 0),
(1, 20, 100, 20);

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

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `giohang_chitiet`
--

CREATE TABLE `giohang_chitiet` (
  `IdGioHangChiTiet` int(11) NOT NULL,
  `IdGioHang` int(11) NOT NULL,
  `LoaiSanPham` int(11) NOT NULL,
  `IdSanPham` int(11) NOT NULL,
  `SoLuong` int(11) NOT NULL,
  `Gia` int(11) NOT NULL,
  `IdTheLoai` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
('dgdg', '_imgProduct/mohinh/1731663910_Screenshot 2024-10-27 100422.png', '', 6000, 20, 3, 60, 1, 5),
('hfh', '_imgProduct/mohinh/1731663931_bocchi-walking-in-the-rain-thumb.jpg', '', 2000, 3000, 2, 10, 1, 6);

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
  `PasswordUser` varchar(50) NOT NULL,
  `role` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`IdUser`, `SDT`, `NameUser`, `EmailUser`, `PasswordUser`, `role`) VALUES
(1, '0917610804', 'Dương Thuận Thông', 'thuanthong675@gmail.com', '123456789', 0),
(2, '123456789', 'Dương Thuận Buồm', 'traolongngonqu@gmail.com', '123456789', 1),
(3, '987654321', 'Dương Văn', 'duongvan@gmail.com', '123456789', 1);

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
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `cosplay`
--
ALTER TABLE `cosplay`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `giohang_chitiet`
--
ALTER TABLE `giohang_chitiet`
  MODIFY `IdGioHangChiTiet` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `magma`
--
ALTER TABLE `magma`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `mohinh`
--
ALTER TABLE `mohinh`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `theloai_sanpham`
--
ALTER TABLE `theloai_sanpham`
  MODIFY `IdTheLoai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `IdUser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
