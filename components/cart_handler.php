<?php
    include 'connect.php'; 

    // Hàm trả về JSON và thoát script
    function send_json_response($status, $message = '') {
        header('Content-Type: application/json');
        echo json_encode(['status' => $status, 'message' => $message]);
        exit();
    }
    
    $action_processed = false;
    $is_ajax_request = isset($_GET['ajax']) && $_GET['ajax'] == 1;

    // Lấy URL trang gốc để chuyển hướng về sau khi xử lý xong
    $referring_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../Home/index.php';


    // ------------------------------------
    // 1. Xử lý Thêm sản phẩm vào Giỏ hàng (POST)
    // Giữ nguyên logic cũ và chuyển hướng ngay lập tức (không dùng AJAX cho POST form)
    // ------------------------------------
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        
        $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);
        $category = filter_var($_POST['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $quantity = filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT);
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        $item_key = $category . '_' . $product_id; 

        if (array_key_exists($item_key, $_SESSION['cart'])) {
            $_SESSION['cart'][$item_key]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$item_key] = array(
                'id' => $product_id,
                'category' => $category,
                'quantity' => $quantity
            );
        }
        
        header("Location: $referring_page");
        exit();
    } 
    
    // ------------------------------------
    // 2. Xử lý Thêm sản phẩm vào Yêu thích (GET)
    // ------------------------------------
    if (isset($_GET['action']) && $_GET['action'] == 'add_wishlist' && isset($_GET['product_id']) && isset($_GET['category'])) {
        $product_id = filter_var($_GET['product_id'], FILTER_SANITIZE_NUMBER_INT);
        $category = filter_var($_GET['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        $item_key = $category . '_' . $product_id; 

        if (!isset($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = array();
        }

        if (!array_key_exists($item_key, $_SESSION['wishlist'])) {
            $_SESSION['wishlist'][$item_key] = array(
                'id' => $product_id,
                'category' => $category
            );
            $action_processed = true;
        } else {
            // Nếu đã tồn tại, vẫn báo thành công để JS xử lý UI
            $action_processed = true;
        }

        if ($is_ajax_request) {
            send_json_response('success', 'Đã thêm vào yêu thích.');
        }
    }

    // ------------------------------------
    // 3. Xử lý Xóa sản phẩm khỏi Yêu thích (GET)
    // ------------------------------------
    if (isset($_GET['action']) && $_GET['action'] == 'remove_wishlist' && isset($_GET['key'])) {
        $item_key_to_remove = filter_var($_GET['key'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        if (isset($_SESSION['wishlist']) && array_key_exists($item_key_to_remove, $_SESSION['wishlist'])) {
            unset($_SESSION['wishlist'][$item_key_to_remove]);
            $action_processed = true;
        }

        if ($is_ajax_request) {
            send_json_response('success', 'Đã xóa khỏi yêu thích.');
        }
    }
    
    // ------------------------------------
    // 4. Xử lý Xóa TẤT CẢ sản phẩm khỏi Yêu thích (GET)
    // ------------------------------------
    if (isset($_GET['action']) && $_GET['action'] == 'remove_all_wishlist') {
        if (isset($_SESSION['wishlist'])) {
            unset($_SESSION['wishlist']); 
        }
        // Luôn chuyển hướng vì hành động này thường được thực hiện từ trang Yêu thích
        header("Location: $referring_page");
        exit();
    }

    // ------------------------------------
    // 5. Xử lý Xóa sản phẩm khỏi Giỏ hàng (GET)
    // ------------------------------------
    if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['key'])) {
        $item_key_to_remove = filter_var($_GET['key'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        if (isset($_SESSION['cart']) && array_key_exists($item_key_to_remove, $_SESSION['cart'])) {
            unset($_SESSION['cart'][$item_key_to_remove]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); 
        }
        // Luôn chuyển hướng vì hành động này thường được thực hiện từ popup Giỏ hàng
        header("Location: $referring_page");
        exit();
    }
    

    // ------------------------------------
    // REDIRECT CUỐI CÙNG (Chỉ chạy cho các request không phải AJAX đã được xử lý)
    // ------------------------------------
    if ($action_processed) {
        // Nếu không phải AJAX, chuyển hướng về trang trước (Đây là Fallback cho các trường hợp đặc biệt không dùng AJAX)
        header("Location: $referring_page");
        exit();
    } else {
        // Fallback: Chuyển hướng về trang chủ nếu không có hành động nào được nhận diện
        header("Location: ../Home/index.php");
        exit();
    }
?>