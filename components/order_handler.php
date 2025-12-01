// File: components/order_handler.php (Tạo mới)
<?php
    // Bắt đầu output buffering để chặn output từ header.php
    ob_start();
    
    include 'connect.php';
    
    // Chỉ include các hàm cần thiết từ header.php mà không output HTML
    // Tách phần functions từ header.php
    if (!function_exists('get_product_details_by_id_and_category')) {
        function get_product_details_by_id_and_category($conn, $product_id, $category) {
            if (empty($category) || $product_id <= 0) {
                return null;
            }
            
            $select_query = "SELECT Name, Img1, Gia, Sale, TheLoai FROM `$category` WHERE ID = ?";
            $stmt = mysqli_prepare($conn, $select_query);
            
            if (!$stmt) return null;

            mysqli_stmt_bind_param($stmt, "i", $product_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $product = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                
                $price = $product['Gia'];
                if ($product['Sale'] > 0) {
                    $final_price = $price * (1 - $product['Sale'] / 100);
                } else {
                    $final_price = $price;
                }
                $product['final_price'] = $final_price;
                $product['TheLoaiId'] = $product['TheLoai'];
                return $product;
            }
            
            if ($stmt) mysqli_stmt_close($stmt);
            return null;
        }
    }

    if (!function_exists('get_or_create_cart_id')) {
        function get_or_create_cart_id($conn, $user_id) {
            $query = "SELECT IDGioHang FROM `giohang` WHERE IdUser = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                return $row['IDGioHang'];
            } else {
                return false;
            }
        }
    }
    
    // Xóa output buffer để tránh output HTML
    ob_end_clean();
    
    // Hàm chuyển hướng với thông báo
    function redirect_with_message($location, $type, $message) {
        $_SESSION['order_message'] = ['type' => $type, 'text' => $message];
        header("Location: $location");
        exit();
    }
    
    // Yêu cầu POST và đăng nhập
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
        redirect_with_message('../Home/index.php', 'error', 'Truy cập không hợp lệ hoặc chưa đăng nhập.');
    }
    
    $user_id = $_SESSION['user_id'];
    
    // 1. Lấy và làm sạch dữ liệu từ POST
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $city = mysqli_real_escape_string($conn, trim($_POST['city']));
    $district = mysqli_real_escape_string($conn, trim($_POST['district']));
    $ward = mysqli_real_escape_string($conn, trim($_POST['ward']));
    $address_detail = mysqli_real_escape_string($conn, trim($_POST['address_detail']));
    $payment_method = mysqli_real_escape_string($conn, trim($_POST['payment']));
    $note = mysqli_real_escape_string($conn, trim($_POST['note'] ?? ''));
    $voucher_code = mysqli_real_escape_string($conn, trim($_POST['voucher_code'] ?? ''));
    $discount_amount = (int)($_POST['discount_amount'] ?? 0); // Lấy giá trị giảm giá đã tính ở Frontend
    $total_price_final = (int)($_POST['total_price_final'] ?? 0); // Tổng cộng cuối cùng
    $product_total = (int)($_POST['product_total'] ?? 0); // Tổng tiền sản phẩm
    
    $full_address = "$address_detail, $ward, $district, $city";
    $shipping_fee = 50000;
    
    // 2. Xác minh Giỏ hàng và Tổng tiền
    $cart_id = get_or_create_cart_id($conn, $user_id);
    
    // Lấy chi tiết giỏ hàng
    $items_to_order = [];
    if ($cart_id !== false) {
        $select_items_query = "SELECT IdGioHangChiTiet, IdSanPham, LoaiSanPham, SoLuong, Gia as item_price FROM `giohang_chitiet` WHERE IdGioHang = ?";
        $stmt_items = mysqli_prepare($conn, $select_items_query);
        mysqli_stmt_bind_param($stmt_items, "i", $cart_id);
        mysqli_stmt_execute($stmt_items);
        $result_items = mysqli_stmt_get_result($stmt_items);
        while ($row = mysqli_fetch_assoc($result_items)) {
            // Cần lấy Tên sản phẩm từ DB (sử dụng hàm từ header.php)
            $product_data = get_product_details_by_id_and_category($conn, $row['IdSanPham'], $row['LoaiSanPham']);
            if ($product_data) {
                 $row['TenSanPham'] = $product_data['Name'];
                 $items_to_order[] = $row;
            }
        }
        mysqli_stmt_close($stmt_items);
    }
    
    if (empty($items_to_order)) {
        redirect_with_message('../GioHang/giohang.php', 'error', 'Giỏ hàng trống, không thể đặt hàng.');
    }
    
    // 3. Khởi tạo Transaction (Đảm bảo tất cả cùng thành công hoặc thất bại)
    mysqli_autocommit($conn, FALSE);
    $success = true;
    
    // 4. INSERT vào bảng donhang
    $insert_order_query = "INSERT INTO `donhang` 
        (IdUser, MaDonHang, TenNguoiNhan, SDTNguoiNhan, DiaChiGiaoHang, TinhThanh, PhuongThucThanhToan, TongTien, PhiVanChuyen, GiamGia, TongCong, GhiChu)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_order = mysqli_prepare($conn, $insert_order_query);
    
    // Tạo Mã đơn hàng duy nhất (ví dụ: WD2025MMDDHHMMSS)
    $ma_don_hang = 'WD' . date('YmdHis') . $user_id;
    
    // Tổng tiền sản phẩm, Phí vận chuyển, Giảm giá, Tổng cộng
    $TongCongFinal = $product_total + $shipping_fee - $discount_amount;
    
    // Dòng này cần được kiểm tra kỹ: tổng tiền sản phẩm ($product_total)
    mysqli_stmt_bind_param($stmt_order, "issssssiiis", 
        $user_id, $ma_don_hang, $name, $phone, $full_address, $city, $payment_method, 
        $product_total, $shipping_fee, $discount_amount, $TongCongFinal, $note);
        
    if (!mysqli_stmt_execute($stmt_order)) {
        $success = false;
        $db_error = mysqli_error($conn);
    }
    $order_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt_order);

    // 5. INSERT vào bảng donhang_chitiet
    if ($success) {
        $insert_detail_query = "INSERT INTO `donhang_chitiet` 
            (IdDonHang, LoaiSanPham, IdSanPham, TenSanPham, SoLuong, Gia, ThanhTien)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_detail = mysqli_prepare($conn, $insert_detail_query);
        
        foreach ($items_to_order as $item) {
            $item_subtotal = $item['item_price'] * $item['SoLuong'];
            
            mysqli_stmt_bind_param($stmt_detail, "isisiis", 
                $order_id, $item['LoaiSanPham'], $item['IdSanPham'], $item['TenSanPham'], 
                $item['SoLuong'], $item['item_price'], $item_subtotal);

            if (!mysqli_stmt_execute($stmt_detail)) {
                $success = false;
                $db_error = mysqli_error($conn);
                break;
            }
        }
        mysqli_stmt_close($stmt_detail);
    }

    // 6. Xóa giỏ hàng (giohang_chitiet) và commit/rollback
    if ($success) {
        $delete_cart_query = "DELETE FROM `giohang_chitiet` WHERE IdGioHang = ?";
        $stmt_delete = mysqli_prepare($conn, $delete_cart_query);
        mysqli_stmt_bind_param($stmt_delete, "i", $cart_id);
        if (!mysqli_stmt_execute($stmt_delete)) {
            $success = false;
            $db_error = mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_delete);
    }

    if ($success) {
        mysqli_commit($conn);
        redirect_with_message("../thongtinkhachhang/donhang.php", "success", "Đặt hàng thành công! Mã đơn hàng của bạn là $ma_don_hang.");
    } else {
        mysqli_rollback($conn);
        // Trong môi trường production, chỉ hiển thị thông báo lỗi chung
        redirect_with_message("../thanhtoan/thanhtoan.php", "error", "Lỗi: Không thể hoàn tất đơn hàng. Vui lòng thử lại. (DB Error: $db_error)");
    }
?>