<?php
    // File: components/order_cancel_handler.php
    // Xử lý hủy đơn hàng
    
    // Bắt đầu output buffering
    ob_start();
    
    // Đảm bảo session đã được start
    if (!session_id()) {
        session_start();
    }
    
    include 'connect.php';
    
    // Kiểm tra kết nối database
    if (!$conn) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối database.']);
        exit();
    }
    
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập để thực hiện thao tác này.']);
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Kiểm tra AJAX request
    $is_ajax_request = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                       strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
                       (isset($_POST['ajax']) && $_POST['ajax'] == '1') ||
                       (isset($_GET['ajax']) && $_GET['ajax'] == '1');
    
    // Hàm trả về JSON response
    function send_json_response($status, $message, $data = []) {
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        
        $response = json_encode([
            'status' => $status, 
            'message' => $message, 
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
        
        if ($response === false) {
            $response = json_encode([
                'status' => 'error', 
                'message' => 'Lỗi xử lý dữ liệu', 
                'data' => []
            ], JSON_UNESCAPED_UNICODE);
        }
        
        echo $response;
        exit();
    }
    
    // Hàm chuyển hướng với thông báo
    function redirect_with_message($location, $type, $message) {
        if (!session_id()) {
            session_start();
        }
        $_SESSION['order_message'] = ['type' => $type, 'text' => $message];
        
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        header("Location: $location");
        exit();
    }
    
    // Chỉ xử lý POST và GET requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
        if ($is_ajax_request) {
            send_json_response('error', 'Yêu cầu không hợp lệ.');
        } else {
            redirect_with_message('../thongtinkhachhang/donhang.php', 'error', 'Yêu cầu không hợp lệ.');
        }
    }
    
    // Kiểm tra có order_id không
    if (!isset($_REQUEST['order_id'])) {
        if ($is_ajax_request) {
            send_json_response('error', 'Thiếu thông tin đơn hàng.');
        } else {
            redirect_with_message('../thongtinkhachhang/donhang.php', 'error', 'Thiếu thông tin đơn hàng.');
        }
    }
    
    // Lấy và làm sạch ID đơn hàng
    $order_id = filter_var($_REQUEST['order_id'], FILTER_SANITIZE_NUMBER_INT);
    
    if ($order_id <= 0) {
        if ($is_ajax_request) {
            send_json_response('error', 'ID đơn hàng không hợp lệ.');
        } else {
            redirect_with_message('../thongtinkhachhang/donhang.php', 'error', 'ID đơn hàng không hợp lệ.');
        }
    }
    
    // Kiểm tra đơn hàng thuộc về user VÀ đang ở trạng thái 'Chờ xử lý'
    $check_query = "SELECT IdDonHang, TrangThai FROM `donhang` WHERE IdDonHang = ? AND IdUser = ?";
    $stmt_check = mysqli_prepare($conn, $check_query);
    
    if (!$stmt_check) {
        if ($is_ajax_request) {
            send_json_response('error', 'Lỗi truy vấn hệ thống khi kiểm tra đơn hàng.');
        } else {
            redirect_with_message('../thongtinkhachhang/donhang.php', 'error', 'Lỗi hệ thống khi kiểm tra đơn hàng.');
        }
    }
    
    mysqli_stmt_bind_param($stmt_check, "ii", $order_id, $user_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) == 0) {
        mysqli_stmt_close($stmt_check);
        if ($is_ajax_request) {
            send_json_response('error', 'Đơn hàng không tồn tại hoặc không thuộc về bạn.');
        } else {
            redirect_with_message('../thongtinkhachhang/donhang.php', 'error', 'Đơn hàng không tồn tại hoặc không thuộc về bạn.');
        }
    }
    
    $order_data = mysqli_fetch_assoc($result_check);
    mysqli_stmt_close($stmt_check);
    
    // Kiểm tra trạng thái đơn hàng
    $current_status = $order_data['TrangThai'];
    if ($current_status !== 'Chờ xử lý') {
        $status_messages = [
            'Đã xác nhận' => 'Đơn hàng đã được xác nhận, không thể hủy. Vui lòng liên hệ hỗ trợ nếu cần hủy đơn.',
            'Đang giao hàng' => 'Đơn hàng đang được giao, không thể hủy. Vui lòng liên hệ hỗ trợ.',
            'Đã giao hàng' => 'Đơn hàng đã được giao, không thể hủy.',
            'Đã hủy' => 'Đơn hàng này đã được hủy trước đó.'
        ];
        
        $message = $status_messages[$current_status] ?? "Chỉ có thể hủy đơn hàng đang ở trạng thái 'Chờ xử lý'. Trạng thái hiện tại: $current_status";
        
        if ($is_ajax_request) {
            send_json_response('error', $message);
        } else {
            redirect_with_message('../thongtinkhachhang/donhang.php', 'error', $message);
        }
    }
    
    // Bắt đầu transaction để đảm bảo tính nhất quán
    mysqli_autocommit($conn, FALSE);
    $success = true;
    $db_error = '';
    
    // 1. Cập nhật trạng thái đơn hàng thành 'Đã hủy'
    $update_query = "UPDATE `donhang` SET TrangThai = 'Đã hủy', NgayCapNhat = NOW() WHERE IdDonHang = ? AND IdUser = ?";
    $stmt_update = mysqli_prepare($conn, $update_query);
    
    if (!$stmt_update) {
        $success = false;
        $db_error = mysqli_error($conn);
    } else {
        mysqli_stmt_bind_param($stmt_update, "ii", $order_id, $user_id);
        
        if (!mysqli_stmt_execute($stmt_update)) {
            $success = false;
            $db_error = mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_update);
    }
    
    // 2. Hoàn kho sản phẩm (nếu cần)
    if ($success) {
        // Lấy chi tiết đơn hàng để hoàn kho
        $select_details_query = "SELECT LoaiSanPham, IdSanPham, SoLuong FROM `donhang_chitiet` WHERE IdDonHang = ?";
        $stmt_details = mysqli_prepare($conn, $select_details_query);
        
        if (!$stmt_details) {
            $success = false;
            $db_error = "Lỗi khi lấy chi tiết đơn hàng: " . mysqli_error($conn);
            error_log("Failed to prepare statement for order details: " . mysqli_error($conn));
        } else {
            mysqli_stmt_bind_param($stmt_details, "i", $order_id);
            mysqli_stmt_execute($stmt_details);
            $result_details = mysqli_stmt_get_result($stmt_details);
            
            $restore_errors = [];
            
            while ($item = mysqli_fetch_assoc($result_details)) {
                $category = $item['LoaiSanPham'];
                $product_id = $item['IdSanPham'];
                $quantity = $item['SoLuong'];
                
                // Kiểm tra category hợp lệ
                $valid_categories = ['mohinh', 'magma', 'cosplay'];
                if (!in_array($category, $valid_categories)) {
                    error_log("Invalid category for stock restoration: $category");
                    $restore_errors[] = "Danh mục không hợp lệ: $category";
                    continue;
                }
                
                // Kiểm tra sản phẩm có tồn tại không
                $check_product_query = "SELECT ID, SoLuongTonKho, SoLuongDaBan FROM `$category` WHERE ID = ?";
                $stmt_check = mysqli_prepare($conn, $check_product_query);
                
                if (!$stmt_check) {
                    $restore_errors[] = "Lỗi kiểm tra sản phẩm ID $product_id: " . mysqli_error($conn);
                    error_log("Failed to prepare check statement for product ID $product_id in category $category: " . mysqli_error($conn));
                    continue;
                }
                
                mysqli_stmt_bind_param($stmt_check, "i", $product_id);
                mysqli_stmt_execute($stmt_check);
                $product_result = mysqli_stmt_get_result($stmt_check);
                
                if (mysqli_num_rows($product_result) > 0) {
                    // Cập nhật số lượng tồn kho (tăng lên) và giảm số lượng đã bán
                    $update_stock_query = "UPDATE `$category` SET SoLuongTonKho = SoLuongTonKho + ?, SoLuongDaBan = GREATEST(0, SoLuongDaBan - ?) WHERE ID = ?";
                    $stmt_stock = mysqli_prepare($conn, $update_stock_query);
                    
                    if (!$stmt_stock) {
                        $restore_errors[] = "Lỗi chuẩn bị câu lệnh hoàn kho cho sản phẩm ID $product_id: " . mysqli_error($conn);
                        error_log("Failed to prepare stock update statement for product ID $product_id in category $category: " . mysqli_error($conn));
                        mysqli_stmt_close($stmt_check);
                        continue;
                    }
                    
                    mysqli_stmt_bind_param($stmt_stock, "iii", $quantity, $quantity, $product_id);
                    if (!mysqli_stmt_execute($stmt_stock)) {
                        $restore_errors[] = "Lỗi hoàn kho sản phẩm ID $product_id trong danh mục $category: " . mysqli_error($conn);
                        error_log("Failed to restore stock for product ID $product_id in category $category: " . mysqli_error($conn));
                        $success = false; // Đánh dấu lỗi để rollback
                    }
                    mysqli_stmt_close($stmt_stock);
                } else {
                    $restore_errors[] = "Không tìm thấy sản phẩm ID $product_id trong danh mục $category";
                    error_log("Product ID $product_id not found in category $category");
                    // Không đánh dấu lỗi vì sản phẩm có thể đã bị xóa
                }
                mysqli_stmt_close($stmt_check);
            }
            mysqli_stmt_close($stmt_details);
            
            // Nếu có lỗi nghiêm trọng khi hoàn kho, đánh dấu để rollback
            if (!empty($restore_errors) && !$success) {
                $db_error = "Lỗi khi hoàn kho: " . implode("; ", $restore_errors);
                error_log("Stock restoration errors for order ID $order_id: " . $db_error);
            } elseif (!empty($restore_errors)) {
                // Có lỗi nhưng không nghiêm trọng (ví dụ: sản phẩm không tồn tại)
                error_log("Stock restoration warnings for order ID $order_id: " . implode("; ", $restore_errors));
            }
        }
    }
    
    // Xử lý transaction
    if ($success) {
        mysqli_commit($conn);
        mysqli_autocommit($conn, TRUE);
        
        if ($is_ajax_request) {
            send_json_response('success', 'Đơn hàng đã được hủy thành công! Kho sản phẩm đã được hoàn trả.', [
                'order_id' => $order_id, 
                'new_status' => 'Đã hủy'
            ]);
        } else {
            redirect_with_message('../thongtinkhachhang/donhang.php', 'success', "Đơn hàng #$order_id đã được hủy thành công. Kho sản phẩm đã được hoàn trả.");
        }
    } else {
        mysqli_rollback($conn);
        mysqli_autocommit($conn, TRUE);
        
        $error_message = $db_error ?? 'Lỗi không xác định khi hủy đơn hàng';
        error_log("Order Cancel Error for order ID $order_id: " . $error_message);
        
        if ($is_ajax_request) {
            send_json_response('error', 'Lỗi khi hủy đơn hàng. Vui lòng thử lại hoặc liên hệ hỗ trợ nếu vấn đề vẫn tiếp tục.');
        } else {
            redirect_with_message('../thongtinkhachhang/donhang.php', 'error', 'Lỗi khi hủy đơn hàng. Vui lòng thử lại hoặc liên hệ hỗ trợ.');
        }
    }
?>

