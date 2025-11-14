<?php
    // Bao gồm file kết nối để bắt đầu session
    include 'connect.php'; 

    // Lấy dữ liệu từ form Thêm vào Giỏ hàng
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        
        $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);
        $category = filter_var($_POST['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $quantity = filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT);
        
        // --- Bắt đầu Logic lưu vào Session Giỏ hàng ---

        // Kiểm tra xem giỏ hàng đã tồn tại trong session chưa
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        // Tạo khóa định danh duy nhất cho sản phẩm
        // Điều này cần thiết vì ID sản phẩm có thể trùng giữa các bảng (ví dụ: mô hình ID 1 và manga ID 1)
        $item_key = $category . '_' . $product_id; 

        if (array_key_exists($item_key, $_SESSION['cart'])) {
            // Nếu sản phẩm đã có trong giỏ, tăng số lượng
            $_SESSION['cart'][$item_key]['quantity'] += $quantity;
        } else {
            // Nếu sản phẩm chưa có, thêm mới vào giỏ
            $_SESSION['cart'][$item_key] = array(
                'id' => $product_id,
                'category' => $category,
                'quantity' => $quantity
            );
        }

        // Chuyển hướng người dùng trở lại trang chi tiết sản phẩm 
        // và thêm thông báo (nếu cần, bạn có thể thêm logic thông báo ở đây)
        header("Location: ../Pagesproducts/product_detail.php?id=$product_id&category=$category&message=added");
        exit();
    } 
    
    // Xử lý các hành động khác (Xóa sản phẩm,...)
    // Sẽ được thêm vào sau nếu cần.

    // Nếu không có hành động nào được xác định, chuyển hướng về trang chủ
    header("Location: ../Home/index.php");
    exit();


    // ------------------------------------
    // Xử lý Xóa sản phẩm khỏi Giỏ hàng
    // ------------------------------------
    if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['key'])) {
        $item_key_to_remove = filter_var($_GET['key'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        if (isset($_SESSION['cart']) && array_key_exists($item_key_to_remove, $_SESSION['cart'])) {
            // Xóa sản phẩm khỏi giỏ hàng
            unset($_SESSION['cart'][$item_key_to_remove]);
            
            // Sắp xếp lại mảng sau khi xóa để tránh lỗi
            $_SESSION['cart'] = array_values($_SESSION['cart']); 
        }

        // Chuyển hướng người dùng về trang trước (hoặc trang chủ nếu không biết)
        $referring_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../Home/index.php';
        header("Location: $referring_page");
        exit();
    }
    
    // Nếu không có hành động nào được xác định, chuyển hướng về trang chủ
    header("Location: ../Home/index.php");
    exit();
?>