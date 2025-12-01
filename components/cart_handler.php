<?php
    // Bắt đầu output buffering NGAY từ đầu để chặn mọi output không mong muốn
    // Phải gọi ob_start() trước mọi thứ, kể cả include
    ob_start();
    
    // Tắt hiển thị lỗi để tránh output không mong muốn
    ini_set('display_errors', 0);
    error_reporting(0);
    
    // Include connect.php - đảm bảo không có output
    include 'connect.php';
    
    // Kiểm tra xem có output nào không mong muốn không
    $buffer_content = ob_get_contents();
    if (!empty($buffer_content) && !preg_match('/^\s*$/', $buffer_content)) {
        // Nếu có output không mong muốn, xóa nó
        ob_clean();
    } 

    // Hàm trả về JSON và thoát script
    function send_json_response($status, $message = '', $data = []) {
        // Xóa mọi output buffer và đóng nó
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Đảm bảo không có output nào
        ini_set('display_errors', 0);
        error_reporting(0);
        
        // Set header JSON - phải gọi trước khi có bất kỳ output nào
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
        }
        
        // Gửi JSON
        $response = json_encode(['status' => $status, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE);
        
        if ($response === false) {
            // Nếu json_encode thất bại, gửi lỗi
            $response = json_encode(['status' => 'error', 'message' => 'Lỗi xử lý dữ liệu', 'data' => []], JSON_UNESCAPED_UNICODE);
        }
        
        echo $response;
        exit();
    }
    
    // ====================================================================
    // HÀM HỖ TRỢ CHÍNH: LẤY THÔNG TIN SẢN PHẨM TỪ DB
    // ====================================================================
    function get_product_details_by_id_and_category($conn, $product_id, $category) {
        if (empty($category) || $product_id <= 0) {
            return null;
        }
        
        // Thêm TheLoai
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
            // Lưu ID TheLoai dưới dạng ID số (1, 2, 3) để khớp với cột IdTheLoai
            $product['TheLoaiId'] = $product['TheLoai']; 
            return $product;
        }
        
        if ($stmt) mysqli_stmt_close($stmt);
        return null;
    }

    // Hàm lấy ID Giỏ hàng (Tạo mới nếu chưa có)
    function get_or_create_cart_id($conn, $user_id) {
        if (!$conn || !$user_id) {
            return false;
        }
        
        // Kiểm tra user có tồn tại không
        $check_user = "SELECT IdUser FROM `users` WHERE IdUser = ?";
        $stmt_check = mysqli_prepare($conn, $check_user);
        if ($stmt_check) {
            mysqli_stmt_bind_param($stmt_check, "i", $user_id);
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);
            if (mysqli_num_rows($result_check) == 0) {
                mysqli_stmt_close($stmt_check);
                return false; // User không tồn tại
            }
            mysqli_stmt_close($stmt_check);
        }
        
        $query = "SELECT IDGioHang FROM `giohang` WHERE IdUser = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }
        
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            $cart_id_value = (int)$row['IDGioHang'];
            return $cart_id_value;
        } else {
            // Tạo giỏ hàng mới
            mysqli_stmt_close($stmt);
            
            // Lấy IDGioHang lớn nhất để tạo ID mới (vì không có AUTO_INCREMENT)
            $max_id_query = "SELECT MAX(IDGioHang) as max_id FROM `giohang`";
            $max_result = mysqli_query($conn, $max_id_query);
            $new_id = 1;
            if ($max_result && $row_max = mysqli_fetch_assoc($max_result)) {
                $max_id = (int)$row_max['max_id'];
                // Nếu max_id >= 0, tăng lên 1. Nếu max_id < 0 (không có), dùng 1
                $new_id = ($max_id >= 0) ? ($max_id + 1) : 1;
            }
            
            // Đảm bảo new_id > 0 (tránh xung đột với IDGioHang = 0 nếu có)
            if ($new_id <= 0) {
                $new_id = 1;
            }
            
            $insert_query = "INSERT INTO `giohang` (IDGioHang, IdUser, TongGiaTien, TongSoLuong) VALUES (?, ?, 0, 0)";
            $stmt_insert = mysqli_prepare($conn, $insert_query);
            
            if (!$stmt_insert) {
                $prepare_error = mysqli_error($conn);
                $prepare_errno = mysqli_errno($conn);
                error_log("Cart prepare failed for user $user_id: [$prepare_errno] $prepare_error");
                return false;
            }
            
            mysqli_stmt_bind_param($stmt_insert, "ii", $new_id, $user_id);
            $execute_result = mysqli_stmt_execute($stmt_insert);
            
            if ($execute_result) {
                mysqli_stmt_close($stmt_insert);
                return (int)$new_id;
            } else {
                // Lưu lỗi để debug
                $error = mysqli_error($conn);
                $errno = mysqli_errno($conn);
                mysqli_stmt_close($stmt_insert);
                // Log lỗi vào error_log để debug
                error_log("Cart creation failed for user $user_id with ID $new_id: [$errno] $error");
                
                // Nếu lỗi do duplicate key (ID đã tồn tại), thử lại với ID khác
                if ($errno == 1062) { // Duplicate entry
                    $new_id++;
                    $stmt_insert2 = mysqli_prepare($conn, $insert_query);
                    if ($stmt_insert2) {
                        mysqli_stmt_bind_param($stmt_insert2, "ii", $new_id, $user_id);
                        if (mysqli_stmt_execute($stmt_insert2)) {
                            mysqli_stmt_close($stmt_insert2);
                            return (int)$new_id;
                        }
                        mysqli_stmt_close($stmt_insert2);
                    }
                }
                return false;
            }
        }
    }

    // Logic tính toán lại tổng tiền/tổng số lượng
    function calculate_cart_totals($conn, $cart_id) {
        $query = "SELECT g.SoLuong, g.Gia FROM `giohang_chitiet` g WHERE g.IdGioHang = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, "i", $cart_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $total_price = 0;
        $total_quantity = 0;
        
        while ($row = mysqli_fetch_assoc($result)) {
            $total_price += $row['Gia'] * $row['SoLuong'];
            $total_quantity += $row['SoLuong'];
        }
        mysqli_stmt_close($stmt);

        // Cập nhật lại tổng tiền và tổng số lượng vào bảng giohang
        $update_query = "UPDATE `giohang` SET TongGiaTien = ?, TongSoLuong = ? WHERE IDGioHang = ?";
        $stmt_update = mysqli_prepare($conn, $update_query);
        if (!$stmt_update) return false;
        mysqli_stmt_bind_param($stmt_update, "iii", $total_price, $total_quantity, $cart_id);
        $success = mysqli_stmt_execute($stmt_update);
        mysqli_stmt_close($stmt_update);
        
        return $success;
    }
    // ====================================================================

    
    // Kiểm tra AJAX request từ cả GET và POST
    $is_ajax_request = (isset($_GET['ajax']) && $_GET['ajax'] == 1) || 
                       (isset($_POST['ajax']) && $_POST['ajax'] == 1) ||
                       (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1) ||
                       (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    $referring_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../Home/index.php';

    // Đảm bảo session đã được khởi tạo
    if (!session_id()) {
        session_start();
    }
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // Lấy hoặc tạo cart_id nếu user đã đăng nhập
    $cart_id = null;
    if ($user_id && $conn) {
        try {
            $cart_id = get_or_create_cart_id($conn, $user_id);
            // Lưu ý: cart_id có thể là 0 (nếu IDGioHang = 0 trong DB), đây là hợp lệ
            // Chỉ false mới là lỗi
            if ($cart_id === false && $conn) {
                // Thử tạo lại giỏ hàng một lần nữa
                $cart_id = get_or_create_cart_id($conn, $user_id);
            }
            // Nếu vẫn false, log lỗi chi tiết
            if ($cart_id === false) {
                $db_error = mysqli_error($conn);
                $db_errno = mysqli_errno($conn);
                error_log("Failed to get or create cart for user_id: $user_id. DB error [$db_errno]: $db_error");
            }
        } catch (Exception $e) {
            // Nếu có lỗi, log và set cart_id = false
            error_log("Exception in get_or_create_cart_id: " . $e->getMessage());
            $cart_id = false;
        } catch (Error $e) {
            // Bắt cả PHP 7+ Error
            error_log("Error in get_or_create_cart_id: " . $e->getMessage());
            $cart_id = false;
        }
    }
    
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    
    // ------------------------------------
    // 0. Xử lý Đồng bộ Giỏ hàng Session -> DB (Quan trọng)
    // ------------------------------------
    // Lưu ý: cart_id có thể là 0 (hợp lệ), chỉ false mới là lỗi
    if ($user_id && $cart_id !== false && $cart_id !== null && isset($_SESSION['cart']) && !isset($_SESSION['cart_synced'])) {
        $current_session_cart = $_SESSION['cart'];
        
        foreach ($current_session_cart as $item_key => $item) {
            $product_id = $item['id'];
            $category = $item['category'];
            $quantity = $item['quantity'];
            
            $product_data = get_product_details_by_id_and_category($conn, $product_id, $category);
            
            if ($product_data) {
                $final_price = (int)$product_data['final_price'];
                $the_loai_id = (int)$product_data['TheLoaiId']; 

                $check_item_query = "SELECT IdGioHangChiTiet, SoLuong FROM `giohang_chitiet` WHERE IdGioHang = ? AND IdSanPham = ? AND LoaiSanPham = ?";
                $stmt_check = mysqli_prepare($conn, $check_item_query);
                // SỬA LỖI: LoaiSanPham là VARCHAR (string), nên dùng "s"
                mysqli_stmt_bind_param($stmt_check, "iis", $cart_id, $product_id, $category);
                mysqli_stmt_execute($stmt_check);
                $result_check = mysqli_stmt_get_result($stmt_check);
                
                if (mysqli_num_rows($result_check) > 0) {
                    $row_check = mysqli_fetch_assoc($result_check);
                    $new_quantity = $row_check['SoLuong'] + $quantity;
                    $update_query = "UPDATE `giohang_chitiet` SET SoLuong = ? WHERE IdGioHangChiTiet = ?";
                    $stmt_update = mysqli_prepare($conn, $update_query);
                    mysqli_stmt_bind_param($stmt_update, "ii", $new_quantity, $row_check['IdGioHangChiTiet']);
                    mysqli_stmt_execute($stmt_update);
                    mysqli_stmt_close($stmt_update);
                } else {
                    $insert_query = "INSERT INTO `giohang_chitiet` (IdGioHang, LoaiSanPham, IdSanPham, SoLuong, Gia, IdTheLoai) 
                                     VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt_insert = mysqli_prepare($conn, $insert_query);
                    // SỬA LỖI: LoaiSanPham là VARCHAR (string), nên dùng "s"
                    // Định dạng: i (IdGioHang), s (LoaiSanPham), i (IdSanPham), i (SoLuong), i (Gia), i (IdTheLoai)
                    mysqli_stmt_bind_param($stmt_insert, "isiiii", $cart_id, $category, $product_id, $quantity, $final_price, $the_loai_id);
                    mysqli_stmt_execute($stmt_insert);
                    mysqli_stmt_close($stmt_insert);
                }
                mysqli_stmt_close($stmt_check);
            }
        }
        
        unset($_SESSION['cart']);
        $_SESSION['cart_synced'] = true;
        calculate_cart_totals($conn, $cart_id);
    }
    
    // ------------------------------------
    // 1. Xử lý Thêm sản phẩm vào Giỏ hàng (POST/GET)
    // ------------------------------------
    if ($action == 'add' && isset($_REQUEST['product_id']) && isset($_REQUEST['category']) && isset($_REQUEST['quantity'])) {
        try {
        // Kiểm tra kết nối database
        if (!$conn) {
            if ($is_ajax_request) { 
                send_json_response('error', 'Lỗi kết nối database. Vui lòng thử lại sau.'); 
            }
            while (ob_get_level()) {
                ob_end_clean();
            }
            header("Location: $referring_page");
            exit();
        }
        
        $product_id = filter_var($_REQUEST['product_id'], FILTER_SANITIZE_NUMBER_INT);
        $category = filter_var($_REQUEST['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $quantity = filter_var($_REQUEST['quantity'], FILTER_SANITIZE_NUMBER_INT);
        
        // Validate input
        if ($product_id <= 0 || empty($category) || $quantity <= 0) {
            if ($is_ajax_request) { 
                send_json_response('error', 'Thông tin sản phẩm không hợp lệ.'); 
            }
            header("Location: $referring_page");
            exit();
        }
        
        $product_data = get_product_details_by_id_and_category($conn, $product_id, $category);
        if (!$product_data) { 
            if ($is_ajax_request) { 
                send_json_response('error', 'Sản phẩm không tồn tại.'); 
            }
            // Xóa output buffer trước khi redirect
            while (ob_get_level()) {
                ob_end_clean();
            }
            header("Location: $referring_page");
            exit();
        }
        
        if (!$user_id) {
            // Logic SESSION
            if (!isset($_SESSION['cart'])) { 
                $_SESSION['cart'] = array(); 
            }
            $item_key = $category . '_' . $product_id; 
            
            if (array_key_exists($item_key, $_SESSION['cart'])) {
                $_SESSION['cart'][$item_key]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$item_key] = array('id' => $product_id, 'category' => $category, 'quantity' => $quantity);
            }
            
            if ($is_ajax_request) { 
                send_json_response('success', 'Đã thêm vào giỏ hàng (Session).', ['product_name' => $product_data['Name']]); 
            }

            // Thêm parameter để tự động mở popup giỏ hàng
            while (ob_get_level()) {
                ob_end_clean();
            }
            $separator = strpos($referring_page, '?') !== false ? '&' : '?';
            header("Location: $referring_page{$separator}cart_added=1");
            exit();
        } 
        
        // Kiểm tra cart_id - nếu không có, thử tạo lại
        // Lưu ý: cart_id có thể là 0 (hợp lệ), chỉ false mới là lỗi
        if ($cart_id === false && $user_id && $conn) {
            // Thử tạo lại giỏ hàng với error handling tốt hơn
            try {
                $cart_id = get_or_create_cart_id($conn, $user_id);
            } catch (Exception $e) {
                $cart_id = false;
            } catch (Error $e) {
                $cart_id = false;
            }
        }
        
        if ($cart_id === false) {
            if ($is_ajax_request) { 
                // Kiểm tra xem có phải lỗi kết nối không
                if (!$conn) {
                    send_json_response('error', 'Lỗi kết nối database. Vui lòng thử lại sau.'); 
                } elseif (!$user_id) {
                    send_json_response('error', 'Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng.'); 
                } else {
                    // Kiểm tra xem có phải lỗi database không
                    if ($conn) {
                        $db_error = mysqli_error($conn);
                        $db_errno = mysqli_errno($conn);
                        if ($db_error) {
                            // Hiển thị lỗi cụ thể để debug
                            $error_msg = 'Lỗi database (Code: ' . $db_errno . '): ' . htmlspecialchars($db_error);
                            send_json_response('error', $error_msg); 
                        } else {
                            // Kiểm tra lại lỗi database cụ thể
                            $last_error = mysqli_error($conn);
                            $last_errno = mysqli_errno($conn);
                            if ($last_error) {
                                send_json_response('error', 'Lỗi tạo giỏ hàng: ' . htmlspecialchars($last_error) . ' (Code: ' . $last_errno . ')'); 
                            } else {
                                send_json_response('error', 'Không thể tạo giỏ hàng. User ID: ' . $user_id . '. Vui lòng thử lại hoặc đăng nhập lại.'); 
                            }
                        }
                    } else {
                        send_json_response('error', 'Không thể tạo giỏ hàng. Vui lòng thử lại hoặc đăng nhập lại.'); 
                    }
                }
            }
            while (ob_get_level()) {
                ob_end_clean();
            }
            header("Location: $referring_page");
            exit();
        }
        
        // LOGIC DB
        $final_price = (int)$product_data['final_price']; 
        $the_loai_id = (int)$product_data['TheLoaiId']; 

        $check_item_query = "SELECT IdGioHangChiTiet, SoLuong FROM `giohang_chitiet` WHERE IdGioHang = ? AND IdSanPham = ? AND LoaiSanPham = ?";
        $stmt_check = mysqli_prepare($conn, $check_item_query);
        
        if (!$stmt_check) {
            if ($is_ajax_request) { 
                send_json_response('error', 'Lỗi truy vấn database. Vui lòng thử lại.'); 
            }
            while (ob_get_level()) {
                ob_end_clean();
            }
            header("Location: $referring_page");
            exit();
        }
        
        mysqli_stmt_bind_param($stmt_check, "iis", $cart_id, $product_id, $category);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) > 0) {
            $row_check = mysqli_fetch_assoc($result_check);
            $new_quantity = $row_check['SoLuong'] + $quantity;
            $update_query = "UPDATE `giohang_chitiet` SET SoLuong = ? WHERE IdGioHangChiTiet = ?";
            $stmt_update = mysqli_prepare($conn, $update_query);
            if ($stmt_update) {
                mysqli_stmt_bind_param($stmt_update, "ii", $new_quantity, $row_check['IdGioHangChiTiet']);
                mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);
            }
        } else {
            $insert_query = "INSERT INTO `giohang_chitiet` (IdGioHang, LoaiSanPham, IdSanPham, SoLuong, Gia, IdTheLoai) 
                            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $insert_query);
            if ($stmt_insert) {
                mysqli_stmt_bind_param($stmt_insert, "isiiii", $cart_id, $category, $product_id, $quantity, $final_price, $the_loai_id);
                mysqli_stmt_execute($stmt_insert);
                mysqli_stmt_close($stmt_insert);
            }
        }
        mysqli_stmt_close($stmt_check);
        calculate_cart_totals($conn, $cart_id);
        
        if ($is_ajax_request) { 
            send_json_response('success', 'Đã thêm vào giỏ hàng.', ['product_name' => $product_data['Name']]); 
        }
        
        // Thêm parameter để tự động mở popup giỏ hàng
        while (ob_get_level()) {
            ob_end_clean();
        }
        $separator = strpos($referring_page, '?') !== false ? '&' : '?';
        header("Location: $referring_page{$separator}cart_added=1");
        exit();
        } catch (Exception $e) {
            // Bắt mọi lỗi và trả về JSON
            if ($is_ajax_request) {
                send_json_response('error', 'Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()));
            } else {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                header("Location: $referring_page");
                exit();
            }
        } catch (Error $e) {
            // Bắt cả PHP 7+ Error
            if ($is_ajax_request) {
                send_json_response('error', 'Lỗi hệ thống: ' . htmlspecialchars($e->getMessage()));
            } else {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                header("Location: $referring_page");
                exit();
            }
        }
    }
    
    // ------------------------------------
    // 2. Xử lý Xóa sản phẩm khỏi Giỏ hàng (GET)
    // ------------------------------------
    if ($action == 'remove' && isset($_GET['key'])) {
        $item_key_to_remove = filter_var($_GET['key'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$user_id) {
            // Logic xóa khỏi Session
            if (isset($_SESSION['cart']) && array_key_exists($item_key_to_remove, $_SESSION['cart'])) {
                unset($_SESSION['cart'][$item_key_to_remove]);
            }
            header("Location: $referring_page");
            exit();
        }
        
        // LOGIC DB: key là IdGioHangChiTiet
        $item_detail_id = (int)$item_key_to_remove; 
        
        $delete_query = "DELETE FROM `giohang_chitiet` WHERE IdGioHangChiTiet = ? AND IdGioHang = ?";
        $stmt_delete = mysqli_prepare($conn, $delete_query);
        
        mysqli_stmt_bind_param($stmt_delete, "ii", $item_detail_id, $cart_id);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
        
        calculate_cart_totals($conn, $cart_id);

        header("Location: $referring_page");
        exit();
    }
    
    // ------------------------------------
    // 3. Xử lý Cập nhật Số lượng Session (Non-AJAX Redirect)
    // ------------------------------------
    if ($action == 'update_session_quantity' && isset($_GET['key']) && isset($_GET['quantity'])) {
        $item_key = filter_var($_GET['key'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $new_quantity = filter_var($_GET['quantity'], FILTER_SANITIZE_NUMBER_INT);
        
        if (isset($_SESSION['cart']) && array_key_exists($item_key, $_SESSION['cart'])) {
            if ($new_quantity < 1) {
                unset($_SESSION['cart'][$item_key]);
            } else {
                $_SESSION['cart'][$item_key]['quantity'] = $new_quantity;
            }
        }
        header("Location: $referring_page");
        exit();
    }
    
    // ------------------------------------
    // 4. Xử lý Cập nhật số lượng (AJAX - DÙNG DB)
    // ------------------------------------
    if ($action == 'update_quantity' && $is_ajax_request && isset($_POST['item_detail_id']) && isset($_POST['quantity'])) {
        $item_detail_id = filter_var($_POST['item_detail_id'], FILTER_SANITIZE_NUMBER_INT);
        $new_quantity = filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT);
        $status = 'success'; $message = 'Cập nhật thành công';
        
        if (!$user_id) { send_json_response('error', 'Bạn cần đăng nhập để cập nhật giỏ hàng.'); }

        if ($new_quantity < 1) {
            $delete_query = "DELETE FROM `giohang_chitiet` WHERE IdGioHangChiTiet = ? AND IdGioHang = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, "ii", $item_detail_id, $cart_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            $update_query = "UPDATE `giohang_chitiet` SET SoLuong = ? WHERE IdGioHangChiTiet = ? AND IdGioHang = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "iii", $new_quantity, $item_detail_id, $cart_id);
            if (!mysqli_stmt_execute($stmt)) { $status = 'error'; $message = 'Lỗi DB: Không thể cập nhật số lượng.'; }
            mysqli_stmt_close($stmt);
        }
        
        calculate_cart_totals($conn, $cart_id);

        $get_total_query = "SELECT TongGiaTien FROM `giohang` WHERE IDGioHang = ?";
        $stmt_total = mysqli_prepare($conn, $get_total_query);
        mysqli_stmt_bind_param($stmt_total, "i", $cart_id);
        mysqli_stmt_execute($stmt_total);
        $result_total = mysqli_stmt_get_result($stmt_total);
        $row_total = mysqli_fetch_assoc($result_total);
        $total_price_vnd = number_format($row_total['TongGiaTien']);
        mysqli_stmt_close($stmt_total);
        
        send_json_response($status, $message, ['total_price' => $total_price_vnd]);
    }
    
    // ------------------------------------
    // Logic Wishlist (Giữ nguyên)
    // ------------------------------------
    $wishlist_actions = ['add_wishlist', 'remove_wishlist', 'remove_all_wishlist'];
    if (in_array($action, $wishlist_actions)) {
        if (!isset($_SESSION['user_id'])) {
            if ($is_ajax_request) { send_json_response('error', 'Bạn cần đăng nhập để sử dụng tính năng này.'); } 
            else { header("Location: ../login&registration/login.php"); exit(); }
        }
        $user_id = $_SESSION['user_id'];
        
        if ($action == 'add_wishlist' && isset($_GET['product_id']) && isset($_GET['category'])) {
            $product_id = filter_var($_GET['product_id'], FILTER_SANITIZE_NUMBER_INT);
            $category = filter_var($_GET['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $status = 'success'; $message = 'Đã thêm vào yêu thích.';
            $insert_query = "INSERT INTO `yeuthich` (IdUser, IdSanPham, LoaiSanPham) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            if (!$stmt) { 
                $error_msg = $conn ? mysqli_error($conn) : 'Lỗi kết nối database';
                send_json_response('error', 'Lỗi truy vấn (Prepare): ' . $error_msg); 
            }
            mysqli_stmt_bind_param($stmt, "iis", $user_id, $product_id, $category);
            if (!mysqli_stmt_execute($stmt)) {
                if (mysqli_errno($conn) == 1062) { $message = 'Sản phẩm đã có trong danh sách yêu thích.'; } 
                else { $message = 'Lỗi DB: Không thể thêm sản phẩm. Mã lỗi: ' . mysqli_errno($conn); $status = 'error'; }
            }
            mysqli_stmt_close($stmt);
            if ($is_ajax_request) { send_json_response($status, $message); }
            header("Location: $referring_page"); exit();
        }

        if ($action == 'remove_wishlist' && isset($_GET['product_id']) && isset($_GET['category'])) {
            $product_id = filter_var($_GET['product_id'], FILTER_SANITIZE_NUMBER_INT);
            $category = filter_var($_GET['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $status = 'success'; $message = 'Đã xóa khỏi yêu thích.';
            $delete_query = "DELETE FROM `yeuthich` WHERE IdUser = ? AND IdSanPham = ? AND LoaiSanPham = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            if (!$stmt) { 
                $error_msg = $conn ? mysqli_error($conn) : 'Lỗi kết nối database';
                send_json_response('error', 'Lỗi truy vấn (Prepare): ' . $error_msg); 
            }
            mysqli_stmt_bind_param($stmt, "iis", $user_id, $product_id, $category);
            if (!mysqli_stmt_execute($stmt)) { $message = 'Lỗi DB: Không thể xóa sản phẩm.'; $status = 'error'; }
            mysqli_stmt_close($stmt);
            if ($is_ajax_request) { send_json_response($status, $message); }
            header("Location: $referring_page"); exit();
        }
        
        if ($action == 'remove_all_wishlist') {
            $delete_all_query = "DELETE FROM `yeuthich` WHERE IdUser = ?";
            $stmt = mysqli_prepare($conn, $delete_all_query);
            if ($stmt) { mysqli_stmt_bind_param($stmt, "i", $user_id); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt); }
            header("Location: $referring_page"); exit();
        }
    }
    
    if (!$action) {
        header("Location: ../Home/index.php");
        exit();
    }
?>