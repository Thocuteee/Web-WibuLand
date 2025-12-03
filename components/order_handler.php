// File: components/order_handler.php (Tạo mới)
<?php
    // Bắt đầu output buffering để chặn output từ header.php
    ob_start();
    
    // Đảm bảo session đã được start
    if (!session_id()) {
        session_start();
    }
    
    include 'connect.php';
    include 'shipping_calculator.php';
    
    // Kiểm tra kết nối database
    if (!$conn) {
        ob_end_clean();
        $_SESSION['order_message'] = ['type' => 'error', 'text' => 'Lỗi kết nối database. Vui lòng thử lại sau.'];
        header("Location: ../thanhtoan/thanhtoan.php");
        exit();
    }
    
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
        // Đảm bảo session đã start
        if (!session_id()) {
            session_start();
        }
        $_SESSION['order_message'] = ['type' => $type, 'text' => $message];
        
        // Đảm bảo không có output trước khi redirect
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header("Location: $location");
        exit();
    }
    
    // Yêu cầu POST và đăng nhập
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect_with_message('../Home/index.php', 'error', 'Yêu cầu không hợp lệ. Vui lòng sử dụng form để đặt hàng.');
    }
    
    if (!isset($_SESSION['user_id'])) {
        redirect_with_message('../login&registration/login.php', 'error', 'Bạn cần đăng nhập để đặt hàng.');
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
    
    // Lấy phí vận chuyển từ form (nếu có) hoặc tính lại
    if (isset($_POST['shipping_fee']) && $_POST['shipping_fee'] > 0) {
        $shipping_fee = (int)$_POST['shipping_fee'];
    }
    
    $full_address = "$address_detail, $ward, $district, $city";
    
    // Lấy phí vận chuyển từ form (ưu tiên giá trị từ form để hỗ trợ voucher free ship)
    $shipping_fee = 0;
    if (isset($_POST['shipping_fee']) && $_POST['shipping_fee'] !== '' && $_POST['shipping_fee'] !== null) {
        $shipping_fee = (int)$_POST['shipping_fee'];
        // Nếu shipping_fee = 0 từ form (có thể do voucher free ship), giữ nguyên 0
        // Không tính lại để tránh ghi đè voucher
    } else {
        // Chỉ tính lại nếu không có giá trị từ form
        $shipping_fee = calculate_shipping_fee($city, $district, $ward);
    }
    
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
    
    if (!$stmt_order) {
        $success = false;
        $db_error = mysqli_error($conn);
    } else {
        // Tạo Mã đơn hàng duy nhất (ví dụ: WD2025MMDDHHMMSS)
        $ma_don_hang = 'WD' . date('YmdHis') . $user_id;
        
        // Tính lại tổng tiền sản phẩm từ giỏ hàng để đảm bảo chính xác
        $calculated_product_total = 0;
        foreach ($items_to_order as $item) {
            $calculated_product_total += $item['item_price'] * $item['SoLuong'];
        }
        
        // Sử dụng giá trị từ form nếu có, nếu không thì tính từ giỏ hàng
        if ($product_total <= 0) {
            $product_total = $calculated_product_total;
        }
        
        // Tính lại phí vận chuyển nếu chưa có hoặc cần tính lại
        if (!isset($shipping_fee) || $shipping_fee <= 0) {
            $shipping_fee = calculate_shipping_fee($city, $district, $ward);
        }
        
        // Tổng tiền sản phẩm, Phí vận chuyển, Giảm giá, Tổng cộng
        // Đảm bảo discount_amount không vượt quá product_total
        if ($discount_amount > $product_total) {
            $discount_amount = $product_total;
        }
        
        $TongCongFinal = $product_total + $shipping_fee - $discount_amount;
        
        // Đảm bảo TongCongFinal không âm
        if ($TongCongFinal < 0) {
            $TongCongFinal = 0;
        }
        
        // Dòng này cần được kiểm tra kỹ: tổng tiền sản phẩm ($product_total)
        mysqli_stmt_bind_param($stmt_order, "issssssiiiis", 
            $user_id, $ma_don_hang, $name, $phone, $full_address, $city, $payment_method, 
            $product_total, $shipping_fee, $discount_amount, $TongCongFinal, $note);
            
        if (!mysqli_stmt_execute($stmt_order)) {
            $success = false;
            $db_error = mysqli_error($conn);
            error_log("Order INSERT Error: " . $db_error);
        } else {
            $order_id = mysqli_insert_id($conn);
            if ($order_id <= 0) {
                $success = false;
                $db_error = "Không thể lấy ID đơn hàng sau khi insert.";
                error_log("Order ID Error: Cannot get order ID after insert");
            }
        }
        mysqli_stmt_close($stmt_order);
    }

    // 5. Kiểm tra tồn kho trước khi tạo đơn hàng
    if ($success) {
        foreach ($items_to_order as $item) {
            $check_stock_query = "SELECT SoLuongTonKho FROM `{$item['LoaiSanPham']}` WHERE ID = ?";
            $stmt_stock = mysqli_prepare($conn, $check_stock_query);
            
            if (!$stmt_stock) {
                $success = false;
                $db_error = "Lỗi kiểm tra tồn kho: " . mysqli_error($conn);
                error_log("Stock check prepare error: " . mysqli_error($conn));
                break;
            }
            
            mysqli_stmt_bind_param($stmt_stock, "i", $item['IdSanPham']);
            mysqli_stmt_execute($stmt_stock);
            $result_stock = mysqli_stmt_get_result($stmt_stock);
            
            if ($row_stock = mysqli_fetch_assoc($result_stock)) {
                $available_stock = (int)$row_stock['SoLuongTonKho'];
                
                if ($available_stock < $item['SoLuong']) {
                    $success = false;
                    $db_error = "Sản phẩm '{$item['TenSanPham']}' không đủ hàng. Còn lại: {$available_stock}, yêu cầu: {$item['SoLuong']}";
                    error_log("Insufficient stock for product: {$item['TenSanPham']}");
                    mysqli_stmt_close($stmt_stock);
                    break;
                }
            } else {
                $success = false;
                $db_error = "Không tìm thấy sản phẩm trong kho: {$item['TenSanPham']}";
                error_log("Product not found: {$item['TenSanPham']}");
                mysqli_stmt_close($stmt_stock);
                break;
            }
            
            mysqli_stmt_close($stmt_stock);
        }
    }

    // 6. INSERT vào bảng donhang_chitiet
    if ($success) {
        $insert_detail_query = "INSERT INTO `donhang_chitiet` 
            (IdDonHang, LoaiSanPham, IdSanPham, TenSanPham, SoLuong, Gia, ThanhTien)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_detail = mysqli_prepare($conn, $insert_detail_query);
        
        if (!$stmt_detail) {
            $success = false;
            $db_error = mysqli_error($conn);
        } else {
            foreach ($items_to_order as $item) {
                $item_subtotal = $item['item_price'] * $item['SoLuong'];
                
                mysqli_stmt_bind_param($stmt_detail, "isisiis", 
                    $order_id, 
                    $item['LoaiSanPham'], 
                    $item['IdSanPham'], 
                    $item['TenSanPham'], 
                    $item['SoLuong'], 
                    $item['item_price'], 
                    $item_subtotal
            );
                
                if (!mysqli_stmt_execute($stmt_detail)) {
                    $success = false;
                    $db_error = mysqli_error($conn);
                    break;
                }
            }
            mysqli_stmt_close($stmt_detail);
        }
    }
    
    // 7. Trừ tồn kho và cộng số lượng đã bán
    if ($success) {
        foreach ($items_to_order as $item) {
            // Trừ tồn kho và cộng số lượng đã bán
            $update_stock_query = "UPDATE `{$item['LoaiSanPham']}` 
                                  SET SoLuongTonKho = SoLuongTonKho - ?, 
                                      SoLuongDaBan = SoLuongDaBan + ? 
                                  WHERE ID = ?";
            
            $stmt_update_stock = mysqli_prepare($conn, $update_stock_query);
            
            if (!$stmt_update_stock) {
                $success = false;
                $db_error = "Lỗi cập nhật tồn kho: " . mysqli_error($conn);
                error_log("Stock update prepare error: " . mysqli_error($conn));
                break;
            }
            
            mysqli_stmt_bind_param($stmt_update_stock, "iii", 
                $item['SoLuong'], 
                $item['SoLuong'], 
                $item['IdSanPham']
            );
            
            if (!mysqli_stmt_execute($stmt_update_stock)) {
                $success = false;
                $db_error = "Lỗi cập nhật tồn kho cho sản phẩm: {$item['TenSanPham']}";
                error_log("Stock update execution error: " . mysqli_error($conn));
                mysqli_stmt_close($stmt_update_stock);
                break;
            }
            
            mysqli_stmt_close($stmt_update_stock);
        }
    }

    // 8. Xóa giỏ hàng (giohang_chitiet) và commit/rollback
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

    // Xử lý transaction
    if ($success) {
        mysqli_commit($conn);
        mysqli_autocommit($conn, TRUE); // Khôi phục autocommit
        
        // Nếu thanh toán bằng VNPay, trả về JSON để hiển thị popup QR
        if ($payment_method == 'vnpay') {
            // Lưu order_id vào session để dùng sau
            $_SESSION['pending_vnpay_order_id'] = $order_id;
            $_SESSION['pending_vnpay_order_code'] = $ma_don_hang;
            
            // Đảm bảo không có output trước JSON
            while (ob_get_level()) {
                ob_end_clean();
            }
            
            // Trả về JSON để JavaScript xử lý hiển thị popup
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'success',
                'payment_method' => 'vnpay',
                'order_id' => $order_id,
                'order_code' => $ma_don_hang,
                'amount' => $TongCongFinal,
                'message' => 'Đơn hàng đã được tạo. Vui lòng thanh toán để hoàn tất.'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        // COD hoặc các phương thức khác
        redirect_with_message("../thongtinkhachhang/donhang.php", "success", "Đặt hàng thành công! Mã đơn hàng của bạn là $ma_don_hang.");
    } else {
        mysqli_rollback($conn);
        mysqli_autocommit($conn, TRUE); // Khôi phục autocommit
        // Log lỗi để debug
        $error_message = $db_error ?? 'Unknown error';
        error_log("Order Handler Error: " . $error_message);
        
        // Nếu là VNPay và có lỗi, trả về JSON error
        if ($payment_method == 'vnpay') {
            while (ob_get_level()) {
                ob_end_clean();
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'error',
                'message' => 'Lỗi: Không thể hoàn tất đơn hàng. Vui lòng thử lại hoặc liên hệ hỗ trợ.',
                'error_detail' => $error_message
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        // Fallback: Nếu redirect không hoạt động, hiển thị lỗi
        if (headers_sent()) {
            die("Lỗi: Không thể hoàn tất đơn hàng. Chi tiết lỗi: " . htmlspecialchars($error_message) . "<br><a href='../thanhtoan/thanhtoan.php'>Quay lại trang thanh toán</a>");
        }
        redirect_with_message("../thanhtoan/thanhtoan.php", "error", "Lỗi: Không thể hoàn tất đơn hàng. Vui lòng thử lại hoặc liên hệ hỗ trợ.");
    }
    
    // Không đóng kết nối vì có thể các file khác cần dùng
?>