# 🎌 Wibu Dreamland - Nơi Giấc Mơ Wibu Thành Hiện Thực 🎌

Đây là dự án website Thương mại điện tử chuyên cung cấp các sản phẩm về Anime/Manga, mô hình (Figure) và trang phục Cosplay.

---

## 🚀 Tính năng chính

| Danh mục | Tính năng | Mô tả chi tiết |
| :--- | :--- | :--- |
| **Sản phẩm** | **Phân loại đa dạng** | Mô hình (Figures), Truyện tranh (Manga), Trang phục Cosplay. |
| **Người dùng** | **Đăng ký/Đăng nhập an toàn** | Sử dụng mã hóa mật khẩu bcrypt (`password_hash` và `password_verify`) và Prepared Statements (Ngăn chặn SQL Injection). |
| **Giỏ hàng** | **Quản lý linh hoạt** | Cho phép người dùng thêm/xóa sản phẩm và cập nhật số lượng động ngay trên popup giỏ hàng hoặc trang chi tiết. Hỗ trợ giỏ hàng **Session** (chưa đăng nhập) và đồng bộ hóa sang **Database** (sau khi đăng nhập). |
| **Yêu thích** | **Wishlist AJAX** | Thêm/xóa sản phẩm vào danh sách yêu thích một cách mượt mà bằng AJAX. Dữ liệu được lưu trữ trong bảng `yeuthich`. |
| **📦 Quản lý Tồn kho** | **Tự động hóa hoàn toàn** | Tự động kiểm tra và trừ tồn kho khi đặt hàng, hoàn lại tồn kho khi hủy đơn. Hiển thị trạng thái "Còn hàng/Hết hàng" và số lượng đã bán trên giao diện. |
| **Admin** | **CRUD Sản phẩm & Quản lý Tồn kho** | Trang quản trị cho phép Xem, Thêm, Sửa, Xóa (CRUD) sản phẩm và quản lý tồn kho theo từng danh mục (`mohinh`, `magma`, `cosplay`). |
| **Thanh toán** | **Tính toán tự động** | Tính tổng giá sản phẩm, phí vận chuyển (50.000₫), và áp dụng giảm giá (Voucher). |
| **Thông tin cá nhân**| **Quản lý Hồ sơ** | Cho phép người dùng cập nhật thông tin cá nhân (Họ tên, Địa chỉ, Ngày sinh, Giới tính, v.v.). |
| **Đơn hàng** | **Theo dõi đơn hàng** | Cho phép người dùng xem lịch sử và trạng thái các đơn hàng đã đặt. |

---

## 💻 Công nghệ sử dụng

* **Frontend:** HTML5, CSS3 (`listproducts.css`, `header_sidebar_footer.css`), JavaScript (`global.js`, `home.js`, `wishlist.js`).
* **Backend:** PHP (Native PHP & MySQLi Prepared Statements).
* **Database:** MySQL (`website_wibu`).
* **Mã hóa:** PHP `password_hash()` (Bcrypt).

---

## 🗄️ Cấu trúc Database (Tóm tắt từ `website_wibu.sql`)

Dự án sử dụng các bảng chính sau:

| Bảng | Mục đích | Các cột quan trọng |
| :--- | :--- | :--- |
| `users` | Lưu trữ thông tin người dùng. | `IdUser`, `EmailUser`, `PasswordUser` (đã băm), `SDT`, `DiaChi`. |
| `mohinh`, `magma`, `cosplay` | Lưu trữ dữ liệu sản phẩm theo danh mục. | `ID`, `Name`, `Gia`, `Sale`, `SoLuongTonKho`, `SoLuongDaBan`, `TheLoai`. |
| `giohang` | Lưu trữ thông tin tổng quát của giỏ hàng (cho user đã đăng nhập). | `IDGioHang`, `IdUser`, `TongGiaTien`, `TongSoLuong`. |
| `giohang_chitiet` | Chi tiết sản phẩm trong giỏ hàng. | `IdGioHangChiTiet`, `IdGioHang`, `IdSanPham`, `LoaiSanPham`, `SoLuong`, `Gia`. |
| `yeuthich` | Danh sách sản phẩm yêu thích (Wishlist). | `ID`, `IdUser`, `IDSanPham`, `LoaiSanPham`. |
| `voucher` | Thông tin các mã giảm giá có sẵn. | `IdVoucher`, `MaVoucher`, `PhanTramGiam`, `NgayKetThuc`. |
| `user_voucher` | Voucher mà người dùng đã nhận/sở hữu. | `Id`, `IdUser`, `IdVoucher`, `DaSuDung`. |
| `donhang` | Thông tin đơn hàng (đã được thêm vào database). | `IdDonHang`, `MaDonHang`, `TongCong`, `TrangThai`, `NgayTao`. |

---

## 📋 Cài đặt và Khởi chạy

### Yêu cầu

* Web Server (Apache, Nginx).
* PHP (Phiên bản >= 7.4).
* MySQL/MariaDB.
* **XAMPP** hoặc **Laragon** được khuyến nghị.

### Các bước

1.  **Clone hoặc Tải về dự án.**
2.  **Cài đặt Database:**
    * Tạo một cơ sở dữ liệu mới trong phpMyAdmin với tên: `website_wibu`.
    * Nhập (Import) file **`sql/website_wibu.sql`** vào cơ sở dữ liệu vừa tạo.
    * **(Tùy chọn)** Nếu database của bạn chưa có cột tồn kho, chạy file **`sql/update_inventory_system.sql`** để cập nhật.
3.  **Cấu hình kết nối:**
    * Kiểm tra file **`components/connect.php`** và đảm bảo các thông số `host`, `dbname`, `username`, `password` khớp với cấu hình MySQL của bạn.
4.  **Khởi chạy Server:**
    * Đặt thư mục dự án vào thư mục gốc của server web (ví dụ: `htdocs` của XAMPP).
    * Tru cập: `http://localhost/tên_thư_mục_dự_án/Home/index.php`.

### 🔑 Tài khoản Admin mặc định

Bạn có thể đăng nhập bằng tài khoản Admin đã được lưu trong DB:
* **Email:** `chenhhungtho01@gmail.com`
* **Mật khẩu (Password):** (Mật khẩu gốc đã được băm bằng bcrypt, bạn có thể tự đăng ký tài khoản và dùng lại cấu trúc email/mật khẩu tương tự để kiểm thử).

---

## 📦 Hướng dẫn Quản lý Tồn kho

Hệ thống quản lý tồn kho tự động đã được tích hợp! Xem hướng dẫn chi tiết tại: **[INVENTORY_MANAGEMENT_GUIDE.md](INVENTORY_MANAGEMENT_GUIDE.md)**

### Các tính năng chính:
✅ Tự động trừ tồn kho khi khách hàng đặt hàng  
✅ Tự động hoàn lại tồn kho khi đơn hàng bị hủy  
✅ Kiểm tra tồn kho trước khi cho phép thêm vào giỏ hàng  
✅ Hiển thị trạng thái "Còn hàng / Hết hàng" trên giao diện  
✅ Hiển thị số lượng đã bán cho mỗi sản phẩm  

### File liên quan:
- `components/order_handler.php` - Xử lý trừ tồn kho khi đặt hàng
- `components/cart_handler.php` - Kiểm tra tồn kho khi thêm giỏ hàng
- `components/order_cancel_handler.php` - Hoàn lại tồn kho khi hủy đơn
- `admin/order_update_handler.php` - Quản lý tồn kho trong admin
- `sql/update_inventory_system.sql` - File SQL migration

---

## 🗑️ Ghi chú Bảo trì

* **Lỗi Tái Định nghĩa Hàm:** Lỗi "Cannot redeclare function" đã được khắc phục bằng cách thêm `if (!function_exists())` vào các hàm dùng chung trong `components/header.php` để đảm bảo chúng chỉ được định nghĩa một lần.
* **Đường dẫn ảnh:** Tất cả đường dẫn ảnh trong PHP đều bắt đầu bằng `/admin/` (ví dụ: `/admin/_imgProduct/mohinh/...`).
* **Quản lý Tồn kho:** Tính năng tự động quản lý tồn kho đã được tích hợp. Xem chi tiết tại `INVENTORY_MANAGEMENT_GUIDE.md`.

---

**© Wibu Dreamland**
