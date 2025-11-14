<?php
    include 'connect.php'; 

    // Hàm trả về JSON và thoát script
    function send_json_response($status, $message = '') {
        ini_set('display_errors', 0);
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'message' => $message]);
        exit();
    }

    // Hàm lấy ID Giỏ hàng (Tạo mới nếu chưa có)
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
            // Tạo giỏ hàng mới
            $insert_query = "INSERT INTO `giohang` (IdUser, TongGiaTien, TongSoLuong) VALUES (?, 0, 0)";
            $stmt_insert = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt_insert, "i", $user_id);
            if (mysqli_stmt_execute($stmt_insert)) {
                $new_id = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt_insert);
                return $new_id;
            }
            mysqli_stmt_close($stmt_insert);
            return false;
        }
    }

    // Logic tính toán lại tổng tiền/tổng số lượng (Cần được gọi sau mỗi thao tác thêm/xóa/cập nhật)
    function calculate_cart_totals($conn, $cart_id) {
        // Lấy chi tiết sản phẩm và tổng hợp
        $query = "SELECT g.SoLuong, t.Gia, t.Sale, t.TheLoai 
                  FROM `giohang_chitiet` g 
                  JOIN (
                      SELECT ID, Gia, Sale, TheLoai, 'mohinh' as CategoryName FROM `mohinh` 
                      UNION ALL
                      SELECT ID, Gia, Sale, TheLoai, 'magma' as CategoryName FROM `magma`
                      UNION ALL
                      SELECT ID, Gia, Sale, TheLoai, 'cosplay' as CategoryName FROM `cosplay`
                  ) t ON g.IdSanPham = t.ID AND g.LoaiSanPham = t.CategoryName
                  WHERE g.IdGioHang = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) return false;
        mysqli_stmt_bind_param($stmt, "i", $cart_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $total_price = 0;
        $total_quantity = 0;
        
        while ($row = mysqli_fetch_assoc($result)) {
            $price = $row['Gia'];
            if ($row['Sale'] > 0) {
                // Giả định Gia đã là giá cuối cùng sau sale khi được lưu trong bảng sản phẩm
                // Nhưng ở đây, ta sử dụng Gia và Sale để tính lại giá cuối cùng
                $final_price = $price * (1 - $row['Sale'] / 100);
            } else {
                $final_price = $price;
            }
            
            $total_price += $final_price * $row['SoLuong'];
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
    
    // --- KHAI BÁO CÁC BIẾN CHÍNH ---
    $is_ajax_request = isset($_GET['ajax']) && $_GET['ajax'] == 1;
    $referring_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../Home/index.php';

    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $cart_id = $user_id ? get_or_create_cart_id($conn, $user_id) : null;
    
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    
    // ------------------------------------
    // 0. Xử lý Đồng bộ Giỏ hàng Session -> DB (Chỉ chạy khi có user và giỏ hàng session chưa được đồng bộ)
    // ------------------------------------
    if ($user_id && isset($_SESSION['cart']) && !isset($_SESSION['cart_synced'])) {
        $current_session_cart = $_SESSION['cart'];
        
        // Vòng lặp qua giỏ hàng Session để thêm vào DB
        foreach ($current_session_cart as $item_key => $item) {
            $product_id = $item['id'];
            $category = $item['category'];
            $quantity = $item['quantity'];
            
            // Lấy giá sản phẩm (giá hiển thị sau sale) và TheLoai từ bảng sản phẩm
            $product_data = get_product_details_by_id_and_category($conn, $product_id, $category);
            
            if ($product_data) {
                // Kiểm tra xem sản phẩm đã có trong chi tiết giỏ hàng DB chưa
                $check_item_query = "SELECT IdGioHangChiTiet, SoLuong FROM `giohang_chitiet` WHERE IdGioHang = ? AND IdSanPham = ? AND LoaiSanPham = ?";
                $stmt_check = mysqli_prepare($conn, $check_item_query);
                mysqli_stmt_bind_param($stmt_check, "iis", $cart_id, $product_id, $category);
                mysqli_stmt_execute($stmt_check);
                $result_check = mysqli_stmt_get_result($stmt_check);

                if (mysqli_num_rows($result_check) > 0) {
                    // Nếu có, cập nhật số lượng
                    $row_check = mysqli_fetch_assoc($result_check);
                    $new_quantity = $row_check['SoLuong'] + $quantity;
                    $update_query = "UPDATE `giohang_chitiet` SET SoLuong = ? WHERE IdGioHangChiTiet = ?";
                    $stmt_update = mysqli_prepare($conn, $update_query);
                    mysqli_stmt_bind_param($stmt_update, "ii", $new_quantity, $row_check['IdGioHangChiTiet']);
                    mysqli_stmt_execute($stmt_update);
                    mysqli_stmt_close($stmt_update);
                } else {
                    // Nếu chưa có, thêm mới
                    $insert_query = "INSERT INTO `giohang_chitiet` (IdGioHang, LoaiSanPham, IdSanPham, SoLuong, Gia, IdTheLoai) 
                                     VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt_insert = mysqli_prepare($conn, $insert_query);
                    
                    // Lấy ID Thể loại từ DB (Giả định giá trị TheLoai trong bảng sản phẩm là IdTheLoai)
                    $the_loai_id = $product_data['TheLoai']; 
                    $final_price = $product_data['final_price']; 
                    
                    mysqli_stmt_bind_param($stmt_insert, "isiiii", $cart_id, $category, $product_id, $quantity, $final_price, $the_loai_id);
                    mysqli_stmt_execute($stmt_insert);
                    mysqli_stmt_close($stmt_insert);
                }
                mysqli_stmt_close($stmt_check);
            }
        }
        
        // Xóa giỏ hàng Session và đánh dấu đã đồng bộ
        unset($_SESSION['cart']);
        $_SESSION['cart_synced'] = true;
        
        // Tính toán lại tổng tiền sau khi đồng bộ
        calculate_cart_totals($conn, $cart_id);
    }
    
    // ------------------------------------
    // 1. Xử lý Thêm sản phẩm vào Giỏ hàng (POST/GET) - DÙNG DB
    // ------------------------------------
    if ($action == 'add' && isset($_REQUEST['product_id']) && isset($_REQUEST['category']) && isset($_REQUEST['quantity'])) {
        $product_id = filter_var($_REQUEST['product_id'], FILTER_SANITIZE_NUMBER_INT);
        $category = filter_var($_REQUEST['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $quantity = filter_var($_REQUEST['quantity'], FILTER_SANITIZE_NUMBER_INT);

        if (!$user_id) {
            // Nếu chưa đăng nhập, dùng lại logic SESSION cũ
            // ... (Logic SESSION cũ: Giống phần Session trong phiên bản trước) ...
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = array();
            }
            $item_key = $category . '_' . $product_id; 
            // Cần lấy final_price và TheLoai để lưu vào Session nếu bạn muốn giữ cấu trúc cũ
            $product_data = get_product_details_by_id_and_category($conn, $product_id, $category);
            
            if (array_key_exists($item_key, $_SESSION['cart'])) {
                $_SESSION['cart'][$item_key]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$item_key] = array(
                    'id' => $product_id,
                    'category' => $category,
                    'quantity' => $quantity,
                    // Giả định Session không cần lưu Gia và IdTheLoai
                );
            }
            // Chuyển hướng ngay lập tức sau khi thêm vào Session
            header("Location: $referring_page");
            exit();
        } 
        
        // --- LOGIC THÊM VÀO DB (KHI ĐÃ ĐĂNG NHẬP) ---
        $product_data = get_product_details_by_id_and_category($conn, $product_id, $category);
        
        if (!$product_data) {
             header("Location: $referring_page");
             exit();
        }
        
        $final_price = $product_data['final_price']; 
        $the_loai_id = $product_data['TheLoai']; 

        // 1. Kiểm tra nếu sản phẩm đã tồn tại trong DB
        $check_item_query = "SELECT IdGioHangChiTiet, SoLuong FROM `giohang_chitiet` WHERE IdGioHang = ? AND IdSanPham = ? AND LoaiSanPham = ?";
        $stmt_check = mysqli_prepare($conn, $check_item_query);
        mysqli_stmt_bind_param($stmt_check, "iis", $cart_id, $product_id, $category);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($result_check) > 0) {
            // 2. Nếu có, cập nhật số lượng
            $row_check = mysqli_fetch_assoc($result_check);
            $new_quantity = $row_check['SoLuong'] + $quantity;
            $update_query = "UPDATE `giohang_chitiet` SET SoLuong = ? WHERE IdGioHangChiTiet = ?";
            $stmt_update = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt_update, "ii", $new_quantity, $row_check['IdGioHangChiTiet']);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
        } else {
            // 3. Nếu chưa có, thêm mới
            $insert_query = "INSERT INTO `giohang_chitiet` (IdGioHang, LoaiSanPham, IdSanPham, SoLuong, Gia, IdTheLoai) 
                             VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_insert = mysqli_prepare($conn, $insert_query);
            // Giá trị Gia ở đây nên là giá cuối cùng đã tính (final_price)
            mysqli_stmt_bind_param($stmt_insert, "isiiii", $cart_id, $category, $product_id, $quantity, $final_price, $the_loai_id);
            mysqli_stmt_execute($stmt_insert);
            mysqli_stmt_close($stmt_insert);
        }
        mysqli_stmt_close($stmt_check);
        
        // 4. Tính toán lại tổng tiền sau khi thêm/cập nhật
        calculate_cart_totals($conn, $cart_id);
        
        header("Location: $referring_page");
        exit();
    }
    
    // ------------------------------------
    // 2. Xử lý Xóa sản phẩm khỏi Giỏ hàng (GET) - DÙNG DB
    // ------------------------------------
    if ($action == 'remove' && isset($_GET['key'])) {
        $item_key_to_remove = filter_var($_GET['key'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        // Định dạng key: IdGioHangChiTiet (sẽ lưu key là IDChiTietGioHang)

        if (!$user_id) {
            // Logic xóa khỏi Session (Giữ nguyên logic cũ)
            if (isset($_SESSION['cart']) && array_key_exists($item_key_to_remove, $_SESSION['cart'])) {
                unset($_SESSION['cart'][$item_key_to_remove]);
                // Re-index array (Giữ lại nếu bạn muốn dùng array_values)
                // $_SESSION['cart'] = array_values($_SESSION['cart']); 
            }
            header("Location: $referring_page");
            exit();
        }
        
        // --- LOGIC XÓA KHỎI DB (KHI ĐÃ ĐĂNG NHẬP) ---
        $delete_query = "DELETE FROM `giohang_chitiet` WHERE IdGioHangChiTiet = ? AND IdGioHang = ?";
        $stmt_delete = mysqli_prepare($conn, $delete_query);
        
        // key ở đây phải là IdGioHangChiTiet nếu bạn muốn dùng key như cũ
        // Giả sử key là IdGioHangChiTiet
        $item_detail_id = (int)$item_key_to_remove; 
        
        mysqli_stmt_bind_param($stmt_delete, "ii", $item_detail_id, $cart_id);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
        
        // Tính toán lại tổng tiền sau khi xóa
        calculate_cart_totals($conn, $cart_id);

        header("Location: $referring_page");
        exit();
    }
    
    // ------------------------------------
    // 3. Xử lý Cập nhật số lượng (AJAX - Dùng cho popup Giỏ hàng) - DÙNG DB
    // ------------------------------------
    if ($action == 'update_quantity' && $is_ajax_request && isset($_POST['item_detail_id']) && isset($_POST['quantity'])) {
        $item_detail_id = filter_var($_POST['item_detail_id'], FILTER_SANITIZE_NUMBER_INT);
        $new_quantity = filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT);
        $status = 'success';
        $message = 'Cập nhật thành công';
        
        if (!$user_id) {
             send_json_response('error', 'Bạn cần đăng nhập để cập nhật giỏ hàng.');
        }

        if ($new_quantity < 1) {
            // Nếu số lượng là 0, chuyển sang xóa sản phẩm
            $delete_query = "DELETE FROM `giohang_chitiet` WHERE IdGioHangChiTiet = ? AND IdGioHang = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, "ii", $item_detail_id, $cart_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            // Cập nhật số lượng
            $update_query = "UPDATE `giohang_chitiet` SET SoLuong = ? WHERE IdGioHangChiTiet = ? AND IdGioHang = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "iii", $new_quantity, $item_detail_id, $cart_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                $status = 'error';
                $message = 'Lỗi DB: Không thể cập nhật số lượng.';
            }
            mysqli_stmt_close($stmt);
        }
        
        // Tính toán lại tổng tiền
        calculate_cart_totals($conn, $cart_id);

        // Lấy lại tổng tiền sau khi cập nhật để trả về JS
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
    // Các logic Wishlist (đã sửa ở lần trước)
    // ------------------------------------
    if (in_array($action, ['add_wishlist', 'remove_wishlist', 'remove_all_wishlist'])) {
        // ... (Logic đã cung cấp ở lần trước) ...
    }
    
    // --- FALLBACK (Chỉ Wishlist cần, phần còn lại đã được xử lý) ---
    // (Giữ nguyên logic Wishlist từ lần trước)
    if ($action == 'add_wishlist' && isset($_GET['product_id']) && isset($_GET['category'])) {
        $product_id = filter_var($_GET['product_id'], FILTER_SANITIZE_NUMBER_INT);
        $category = filter_var($_GET['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $status = 'success';
        $message = 'Đã thêm vào yêu thích.';
        
        // Kiểm tra/Thêm vào DB
        $insert_query = "INSERT INTO `yeuthich` (IdUser, IdSanPham, LoaiSanPham) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        
        if (!$stmt) {
            send_json_response('error', 'Lỗi truy vấn (Prepare): ' . mysqli_error($conn));
        }
            
        mysqli_stmt_bind_param($stmt, "iis", $user_id, $product_id, $category);
        
        if (!mysqli_stmt_execute($stmt)) {
            if (mysqli_errno($conn) == 1062) {
                $message = 'Sản phẩm đã có trong danh sách yêu thích.';
            } else {
                $message = 'Lỗi DB: Không thể thêm sản phẩm. Mã lỗi: ' . mysqli_errno($conn);
                $status = 'error';
            }
        }
        mysqli_stmt_close($stmt);

        if ($is_ajax_request) {
            send_json_response($status, $message);
        }
        
        header("Location: $referring_page");
        exit();
    }

    if ($action == 'remove_wishlist' && isset($_GET['product_id']) && isset($_GET['category'])) {
        $product_id = filter_var($_GET['product_id'], FILTER_SANITIZE_NUMBER_INT);
        $category = filter_var($_GET['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $status = 'success';
        $message = 'Đã xóa khỏi yêu thích.';
        
        $delete_query = "DELETE FROM `yeuthich` WHERE IdUser = ? AND IdSanPham = ? AND LoaiSanPham = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        
        if (!$stmt) {
            send_json_response('error', 'Lỗi truy vấn (Prepare): ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "iis", $user_id, $product_id, $category);
        
        if (!mysqli_stmt_execute($stmt)) {
            $message = 'Lỗi DB: Không thể xóa sản phẩm.';
            $status = 'error';
        }
        mysqli_stmt_close($stmt);

        if ($is_ajax_request) {
            send_json_response($status, $message);
        }
        
        header("Location: $referring_page");
        exit();
    }
    
    if (!$action) {
        header("Location: ../Home/index.php");
        exit();
    }
?>