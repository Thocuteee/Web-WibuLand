-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th12 03, 2025 lúc 07:18 AM
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
  `ID` int(11) NOT NULL,
  `NgayTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `cosplay`
--

INSERT INTO `cosplay` (`Name`, `Img1`, `Img2`, `Gia`, `SoLuongTonKho`, `SoLuongDaBan`, `Sale`, `TheLoai`, `ID`, `NgayTao`) VALUES
('Bộ đồ cosplay Shinobu', '_imgProduct/cosplay/1763303928_shinobu.webp', NULL, 150000, 10, 1, 15, 3, 15, '2025-12-03 11:38:13'),
('Bộ Trang Phục Cosplay Nhân Vật Anime Yuuki Asuna Trong Sword Art Online Dành Cho Nữ', '_imgProduct/cosplay/1763303986_asuna.jpg', NULL, 600000, 20, 10, 5, 3, 16, '2025-12-03 11:38:13'),
('Bộ đồ Cosplay Anya', '_imgProduct/cosplay/1763303698_anya.jpg', NULL, 400000, 10, 5, 10, 3, 17, '2025-12-03 11:38:13'),
('Đồ cosplay Shinobu ', '_imgProduct/cosplay/1763303777_shinobuu.jpg', NULL, 200000, 2, 1, 0, 3, 18, '2025-12-03 11:38:13'),
('Mua Maid Hầu Gái Ram/Rem, Cosplay Hầu Gái, Nhân Vật Hoạt Hình Giảm Giá - Yeep', '_imgProduct/cosplay/1763303866_rem.jpg', NULL, 600000, 40, 3, 25, 3, 19, '2025-12-03 11:38:13'),
('Mantian Pure White Symphony Riêng Yuki Nữ sinh Đồng phục học sinh Trang phục Cosplay', '_imgProduct/cosplay/1763304024_yukii.avif', NULL, 12000000, 3, 2, 5, 3, 20, '2025-12-03 11:38:13');

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

--
-- Đang đổ dữ liệu cho bảng `donhang`
--

INSERT INTO `donhang` (`IdDonHang`, `IdUser`, `MaDonHang`, `TenNguoiNhan`, `SDTNguoiNhan`, `DiaChiGiaoHang`, `TinhThanh`, `QuocGia`, `PhuongThucThanhToan`, `TongTien`, `PhiVanChuyen`, `GiamGia`, `TongCong`, `TrangThai`, `GhiChu`, `NgayTao`, `NgayCapNhat`) VALUES
(1, 1, 'WD202512010356121', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vietcombank', 9000000, 50000, 0, 9050000, 'Đã hủy', '', '2025-12-01 09:56:12', '2025-12-01 10:10:38'),
(2, 1, 'WD202512010421501', 'Chềnh Hưng Thọ', '0902846205', 'Tổ 1, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'cod', 1500000, 50000, 0, 1550000, 'Đã hủy', '', '2025-12-01 10:21:50', '2025-12-01 10:22:39'),
(3, 1, 'WD202512010423161', 'Chềnh Hưng Thọ', '0902846205', 'Tổ 1, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'cod', 85500, 50000, 0, 135500, 'Đã giao hàng', '', '2025-12-01 10:23:16', '2025-12-01 10:23:49'),
(4, 1, 'WD202512010805211', 'Chềnh Hưng Thọ', '0902846205', 'Tổ 1, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'cod', 25000000, 50000, 2500000, 22550000, 'Chờ xử lý', '', '2025-12-01 14:05:21', '2025-12-01 14:05:21'),
(5, 1, 'WD202512020819571', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 750, 50000, 0, 50750, 'Chờ xử lý', '', '2025-12-02 14:19:57', '2025-12-02 14:19:57'),
(6, 1, 'WD202512020837551', 'Chềnh Hưng Thọ', '0902846205', 'Tổ 1, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 750, 50000, 225, 50525, 'Chờ xử lý', '', '2025-12-02 14:37:55', '2025-12-02 14:37:55'),
(7, 1, 'WD202512020859211', 'Chềnh Hưng Thọ', '0902846205', 'Tổ 1, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 750, 50000, 675, 50075, 'Chờ xử lý', 'k j', '2025-12-02 14:59:21', '2025-12-02 14:59:21'),
(8, 1, 'WD202512020939351', 'Chềnh Hưng Thọ', '0902846205', 'Tổ 1, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 1500, 50000, 0, 51500, 'Chờ xử lý', '', '2025-12-02 15:39:35', '2025-12-02 15:39:35'),
(9, 1, 'WD202512021000171', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 750, 50000, 0, 50750, 'Chờ xử lý', '', '2025-12-02 16:00:17', '2025-12-02 16:00:17'),
(10, 1, 'WD202512021014511', 'Chềnh Hưng Thọ', '0902846205', 'Tổ 1, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 750, 50000, 0, 50750, 'Chờ xử lý', '', '2025-12-02 16:14:51', '2025-12-02 16:14:51'),
(11, 1, 'WD202512021016361', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 750, 50000, 0, 50750, 'Chờ xử lý', '', '2025-12-02 16:16:36', '2025-12-02 16:16:36'),
(12, 1, 'WD202512021020171', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 750, 50000, 0, 50750, 'Chờ xử lý', '', '2025-12-02 16:20:17', '2025-12-02 16:20:17'),
(13, 1, 'WD202512021022141', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 750, 50000, 0, 50750, 'Đã hủy', '', '2025-12-02 16:22:14', '2025-12-02 16:25:34'),
(14, 1, 'WD202512021027001', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 750, 50000, 0, 50750, 'Chờ xử lý', '', '2025-12-02 16:27:00', '2025-12-02 16:27:00'),
(15, 1, 'WD202512021033311', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 750, 50000, 0, 50750, 'Chờ xử lý', '', '2025-12-02 16:33:31', '2025-12-02 16:33:31'),
(16, 1, 'WD202512021046591', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 1500, 50000, 0, 51500, 'Chờ xử lý', '', '2025-12-02 16:46:59', '2025-12-02 16:46:59'),
(17, 1, 'WD202512021048501', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 2000, 50000, 0, 52000, 'Chờ xử lý', '', '2025-12-02 16:48:50', '2025-12-02 16:48:50'),
(18, 1, 'WD202512021049431', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 2000, 50000, 0, 52000, 'Đã hủy', '', '2025-12-02 16:49:43', '2025-12-02 16:50:42'),
(19, 1, 'WD202512021055151', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 2000, 50000, 0, 52000, 'Chờ xử lý', '', '2025-12-02 16:55:15', '2025-12-02 16:55:15'),
(20, 1, 'WD202512021100001', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 2000, 50000, 0, 52000, 'Chờ xử lý', '', '2025-12-02 17:00:00', '2025-12-02 17:00:00'),
(21, 1, 'WD202512021105391', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 2000, 50000, 0, 52000, 'Chờ xử lý', '', '2025-12-02 17:05:39', '2025-12-02 17:05:39'),
(22, 1, 'WD202512021108321', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'vnpay', 2000, 50000, 0, 52000, 'Chờ xử lý', '', '2025-12-02 17:08:32', '2025-12-02 17:08:32'),
(23, 1, 'WD202512030613591', 'Chềnh Hưng Thọ', '0902846205', 'hcm, Châu Đức, Láng Lớn, Bà Rịa - Vũng Tàu', 'Bà Rịa - Vũng Tàu', 'Việt Nam', 'cod', 26500000, 50000, 0, 26550000, 'Chờ xử lý', '', '2025-12-03 12:13:59', '2025-12-03 12:13:59');

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

--
-- Đang đổ dữ liệu cho bảng `donhang_chitiet`
--

INSERT INTO `donhang_chitiet` (`IdDonHangChiTiet`, `IdDonHang`, `LoaiSanPham`, `IdSanPham`, `TenSanPham`, `SoLuong`, `Gia`, `ThanhTien`) VALUES
(1, 1, 'mohinh', 9, 'Mô hình Arya', 1, 7500000, 7500000),
(2, 1, 'mohinh', 13, 'Mô hình Anime Figure Uchiha Madara Kimono Trắng', 1, 1500000, 1500000),
(3, 2, 'mohinh', 13, 'Mô hình Anime Figure Uchiha Madara Kimono Trắng', 1, 1500000, 1500000),
(4, 3, 'mohinh', 11, 'Set 6 Nhân Vật One Piece phong cách Chibi dễ thương No.1', 1, 85500, 85500),
(5, 4, 'mohinh', 14, 'Mô hình Changli - Wuthering Wave', 1, 25000000, 25000000),
(6, 5, 'mohinh', 9, 'Mô hình Arya', 1, 750, 750),
(7, 6, 'mohinh', 9, 'Mô hình Arya', 1, 750, 750),
(8, 7, 'mohinh', 9, 'Mô hình Arya', 1, 750, 750),
(9, 8, 'mohinh', 9, 'Mô hình Arya', 2, 750, 1500),
(10, 9, 'mohinh', 9, 'Mô hình Arya', 1, 750, 750),
(11, 10, 'mohinh', 9, 'Mô hình Arya', 1, 750, 750),
(12, 11, 'mohinh', 9, 'Mô hình Arya', 1, 750, 750),
(13, 12, 'mohinh', 9, 'Mô hình Arya', 1, 750, 750),
(14, 13, 'mohinh', 9, 'Mô hình Arya', 1, 750, 750),
(15, 14, 'mohinh', 9, 'Mô hình Arya', 1, 750, 750),
(16, 15, 'mohinh', 9, 'Mô hình Arya', 1, 750, 750),
(17, 16, 'mohinh', 9, 'Mô hình Arya', 1, 1500, 1500),
(18, 17, 'mohinh', 9, 'Mô hình Arya', 1, 2000, 2000),
(19, 18, 'mohinh', 9, 'Mô hình Arya', 1, 2000, 2000),
(20, 19, 'mohinh', 9, 'Mô hình Arya', 1, 2000, 2000),
(21, 20, 'mohinh', 9, 'Mô hình Arya', 1, 2000, 2000),
(22, 21, 'mohinh', 9, 'Mô hình Arya', 1, 2000, 2000),
(23, 22, 'mohinh', 9, 'Mô hình Arya', 1, 2000, 2000),
(24, 23, 'mohinh', 13, 'Mô hình Anime Figure Uchiha Madara Kimono Trắng', 1, 1500000, 1500000),
(25, 23, 'mohinh', 14, 'Mô hình Changli - Wuthering Wave', 1, 25000000, 25000000);

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
(14, 0, 100, 0),
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
(0, 25000000, 1, 1);

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
(28, 0, 'mohinh', 14, 1, 25000000, 1);

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
  `ID` int(11) NOT NULL,
  `NgayTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `magma`
--

INSERT INTO `magma` (`Name`, `Img1`, `Img2`, `Gia`, `SoLuongTonKho`, `SoLuongDaBan`, `Sale`, `TheLoai`, `ID`, `NgayTao`) VALUES
('Dược Sư tự sự', '_imgProduct/magma/1763141113_maomao.jpg', '', 20000, 233, 20, 10, 2, 8, '2025-12-03 11:38:13'),
('Naruto', '_imgProduct/magma/1763141151_âruto.jpg', '', 34000, 200, 20, 10, 2, 9, '2025-12-03 11:38:13'),
('Arya Vol 4', '_imgProduct/magma/1763194988_arya 4.jpg', '', 135000, 120, 56, 0, 2, 10, '2025-12-03 11:38:13'),
('Học viện Anh hùng - Tập 1', '_imgProduct/magma/1763303514_deku.jpg', '', 40000, 1200, 350, 0, 2, 11, '2025-12-03 11:38:13'),
('Thanh gươm diệt quỷ Tập 15', '_imgProduct/magma/1763304108_demon.jpg', '', 36000, 2100, 660, 0, 2, 12, '2025-12-03 11:38:13'),
('Chú thuật hồi chiến - Chapter 19', '_imgProduct/magma/1763303641_jjk.jpg', '', 40000, 300, 910, 0, 2, 13, '2025-12-03 11:38:13');

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
  `ID` int(11) NOT NULL,
  `NgayTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `mohinh`
--

INSERT INTO `mohinh` (`Name`, `Img1`, `Img2`, `Gia`, `SoLuongTonKho`, `SoLuongDaBan`, `Sale`, `TheLoai`, `ID`, `NgayTao`) VALUES
('Mô Hình Roronoa Zoro - Asura Cửu Kiếm (Có LED)', '_imgProduct/mohinh/Screenshot 2024-11-17 215458.png', '', 1900000, 52, 15, 15, 1, 7, '2025-12-03 11:38:13'),
('Luffy ', '_imgProduct/mohinh/1763141312_luffy_figure.jpg', '', 550000, 5, 3, 20, 1, 8, '2025-12-03 11:38:13'),
('Mô hình Arya', '_imgProduct/mohinh/1763194923_arya firuge.jpg', '', 2000, 13, 0, 0, 1, 9, '2025-12-03 11:38:13'),
('Mô hình One Piece Nhân vật Monkey D Luffy Gear 5 Trạng Thái Thức Tỉnh Nika thần Mặt Trời', '_imgProduct/mohinh/1763303193_lufffy.jpg', '', 500000, 10, 3, 0, 1, 10, '2025-12-03 11:38:13'),
('Set 6 Nhân Vật One Piece phong cách Chibi dễ thương No.1', '_imgProduct/mohinh/1763303232_chibi.jpg', '', 90000, 20, 10, 5, 1, 11, '2025-12-03 11:38:13'),
('Mô hình Anime Kimetsu No Yaiba Trọn bộ 6 Nhân Vật Chibi', '_imgProduct/mohinh/1763303282_kamado.jpg', '', 90000, 505, 20, 10, 1, 12, '2025-12-03 11:38:13'),
('Mô hình Anime Figure Uchiha Madara Kimono Trắng', '_imgProduct/mohinh/1763303340_madara.jpg', '', 1500000, 6, 1, 0, 1, 13, '2025-12-03 11:38:13'),
('Mô hình Changli - Wuthering Wave', '_imgProduct/mohinh/1763303450_changli.jpg', '', 25000000, 3, 1, 0, 1, 14, '2025-12-03 11:38:13');

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
  MODIFY `IdDonHang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `donhang_chitiet`
--
ALTER TABLE `donhang_chitiet`
  MODIFY `IdDonHangChiTiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `giohang_chitiet`
--
ALTER TABLE `giohang_chitiet`
  MODIFY `IdGioHangChiTiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

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
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

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
