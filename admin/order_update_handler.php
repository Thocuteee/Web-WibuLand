<?php
    // File: admin/order_update_handler.php

    session_start();
    include '../components/connect.php'; 

    // CHỈ CHO PHÉP ADMIN (role = 0) truy cập
    if (!isset($_SESSION['user_id']) || ($_SESSION['user_id'] != 1)) { // Giả định admin có IdUser = 1 hoặc kiểm tra role khác
        header('Location: ../login&registration/login.php');
        exit();
    }
    
    // Hàm chuyển hướng với thông báo
    function admin_redirect_with_message($location, $message, $type = 'success') {
        $_SESSION['admin_message'] = ['type' => $type, 'text' => $message];
        header("Location: $location");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || !isset($_POST['order_id'])) {
        admin_redirect_with_message('orders.php', 'Yêu cầu không hợp lệ.', 'error');
    }

    $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);
    $action = $_POST['action'];
    $new_status = '';
    $db_error = '';

    // Xác định trạng thái mới
    switch ($action) {
        case 'approve':
            $new_status = 'Đã xác nhận';
            break;
        case 'reject':
            $new_status = 'Đã hủy';
            break;
        case 'ship':
            $new_status = 'Đang giao hàng';
            break;
        case 'complete':
            $new_status = 'Đã giao hàng';
            break;
        default:
            admin_redirect_with_message('orders.php', 'Hành động không hợp lệ.', 'error');
    }

    // 1. Kiểm tra đơn hàng có tồn tại
    $check_query = "SELECT TrangThai FROM `donhang` WHERE IdDonHang = ?";
    $stmt_check = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt_check, "i", $order_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) == 0) {
        admin_redirect_with_message('orders.php', "Đơn hàng #$order_id không tồn tại.", 'error');
    }

    $order_data = mysqli_fetch_assoc($result_check);
    $current_status = $order_data['TrangThai'];
    mysqli_stmt_close($stmt_check);
    
    // Chỉ cho phép chuyển trạng thái nếu không phải là Đã hủy hoặc Đã giao hàng
    if ($current_status == 'Đã hủy' || $current_status == 'Đã giao hàng') {
        admin_redirect_with_message('orders.php', "Không thể thay đổi trạng thái của đơn hàng đã $current_status.", 'error');
    }

    // Bắt đầu Transaction
    mysqli_autocommit($conn, FALSE);
    $success = true;

    // 2. Lấy thông tin đơn hàng để kiểm tra phương thức thanh toán
    $select_order_info = "SELECT PhuongThucThanhToan, TongCong FROM `donhang` WHERE IdDonHang = ?";
    $stmt_info = mysqli_prepare($conn, $select_order_info);
    mysqli_stmt_bind_param($stmt_info, "i", $order_id);
    mysqli_stmt_execute($stmt_info);
    $result_info = mysqli_stmt_get_result($stmt_info);
    $order_info = mysqli_fetch_assoc($result_info);
    mysqli_stmt_close($stmt_info);
    
    $payment_method = $order_info['PhuongThucThanhToan'] ?? '';
    $current_total = $order_info['TongCong'] ?? 0;
    
    // 3. Cập nhật trạng thái và TongCong (nếu là thanh toán trước và đang xác nhận)
    // Nếu đơn hàng đã thanh toán trước (vnpay) và đang được xác nhận, set TongCong = 0
    if ($new_status == 'Đã xác nhận' && $payment_method == 'vnpay' && $current_total > 0) {
        $update_query = "UPDATE `donhang` SET TrangThai = ?, TongCong = 0, NgayCapNhat = NOW() WHERE IdDonHang = ?";
    } else {
        $update_query = "UPDATE `donhang` SET TrangThai = ?, NgayCapNhat = NOW() WHERE IdDonHang = ?";
    }
    
    $stmt_update = mysqli_prepare($conn, $update_query);
    
    if (!$stmt_update) {
        $success = false;
        $db_error = mysqli_error($conn);
    } else {
        // Cả hai trường hợp đều bind cùng kiểu: string (TrangThai) và int (IdDonHang)
        mysqli_stmt_bind_param($stmt_update, "si", $new_status, $order_id);
        
        if (!mysqli_stmt_execute($stmt_update)) {
            $success = false;
            $db_error = mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_update);
    }

    // 4. Hoàn kho/Trừ kho (Chỉ thực hiện Trừ kho khi chuyển từ 'Chờ xử lý' -> 'Đã xác nhận' và Hoàn kho khi chuyển sang 'Đã hủy')
    if ($success) {
        // Tùy chỉnh logic trừ/hoàn kho theo yêu cầu của bạn. 
        // Ví dụ: Giả sử chỉ trừ kho khi đơn hàng được đặt (đã xảy ra ở cart_handler), 
        // và chỉ hoàn kho khi bị hủy.
        
        // Để đơn giản và chính xác với logic hủy đơn (đã có trong file order_cancel_handler.php),
        // tôi sẽ sử dụng logic đó để hoàn kho.
        
        // Vì đây là trang Admin, chúng ta sẽ **hoàn kho** khi trạng thái chuyển sang 'Đã hủy'
        if ($new_status == 'Đã hủy' && $current_status !== 'Đã hủy') {
            // Lấy chi tiết đơn hàng
            $select_details_query = "SELECT LoaiSanPham, IdSanPham, SoLuong FROM `donhang_chitiet` WHERE IdDonHang = ?";
            $stmt_details = mysqli_prepare($conn, $select_details_query);
            mysqli_stmt_bind_param($stmt_details, "i", $order_id);
            mysqli_stmt_execute($stmt_details);
            $result_details = mysqli_stmt_get_result($stmt_details);
            
            while ($item = mysqli_fetch_assoc($result_details)) {
                $category = $item['LoaiSanPham'];
                $product_id = $item['IdSanPham'];
                $quantity = $item['SoLuong'];
                
                // Hoàn lại tồn kho
                $update_stock_query = "UPDATE `$category` SET SoLuongTonKho = SoLuongTonKho + ?, SoLuongDaBan = GREATEST(0, SoLuongDaBan - ?) WHERE ID = ?";
                $stmt_stock = mysqli_prepare($conn, $update_stock_query);
                
                if ($stmt_stock) {
                    mysqli_stmt_bind_param($stmt_stock, "iii", $quantity, $quantity, $product_id);
                    if (!mysqli_stmt_execute($stmt_stock)) {
                        $success = false;
                        $db_error = mysqli_error($conn);
                        break;
                    }
                    mysqli_stmt_close($stmt_stock);
                } else {
                    $success = false;
                    $db_error = "Lỗi prepare hoàn kho: " . mysqli_error($conn);
                    break;
                }
            }
            mysqli_stmt_close($stmt_details);
        }
    }


    // 5. Kết thúc Transaction
    if ($success) {
        mysqli_commit($conn);
        mysqli_autocommit($conn, TRUE);
        admin_redirect_with_message('orders.php', "Cập nhật trạng thái đơn hàng #$order_id thành công! Trạng thái mới: $new_status.");
    } else {
        mysqli_rollback($conn);
        mysqli_autocommit($conn, TRUE);
        $error_message = $db_error ?? 'Lỗi không xác định khi cập nhật đơn hàng.';
        admin_redirect_with_message('orders.php', "Lỗi: Không thể cập nhật đơn hàng #$order_id. Chi tiết: $error_message", 'error');
    }

?>