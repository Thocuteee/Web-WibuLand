<?php
    include '../components/connect.php'; 
    // KHÔNG include header.php ở đây để tránh định nghĩa lại biến và hàm, 
    // nhưng cần include nó ở bên dưới để có HTML. 
    // Tuy nhiên, do các hàm get_product_details_by_id_and_category, get_or_create_cart_id cần được định nghĩa,
    // nên ta BẮT BUỘC phải include header.php ở đây.
    include '../components/header.php';
    
    // Khởi tạo các biến cần thiết (TRƯỚC KHI include header.php lần 2)
    // LƯU Ý: Header.php sẽ chạy lại logic load giỏ hàng, nên ta cần lưu biến để tránh bị ghi đè
    $cart_total_for_page = 0;
    $items_to_display_for_page = [];
    $is_logged_in_for_page = $user_id !== null;

    // --- LOGIC TẢI GIỎ HÀNG CHO TRANG ĐẦY ĐỦ ---
    if ($is_logged_in_for_page) {
        $cart_id = get_or_create_cart_id($conn, $user_id);
        // SỬA LỖI: Kiểm tra $cart_id khác FALSE để chấp nhận giá trị 0
        if ($cart_id !== false) { 
            // 1. Lấy danh sách sản phẩm trong giohang_chitiet
            $select_cart_query = "SELECT IdGioHangChiTiet, IdSanPham, LoaiSanPham, SoLuong, Gia as item_price FROM `giohang_chitiet` WHERE IdGioHang = ? ORDER BY IdGioHangChiTiet DESC";
            $stmt_cart = mysqli_prepare($conn, $select_cart_query);
            if($stmt_cart) {
                mysqli_stmt_bind_param($stmt_cart, "i", $cart_id);
                mysqli_stmt_execute($stmt_cart);
                $result_cart = mysqli_stmt_get_result($stmt_cart);
                
                // 2. Điền dữ liệu vào $items_to_display và tính tổng
                while ($row = mysqli_fetch_assoc($result_cart)) {
                    $items_to_display_for_page[] = $row;
                    $cart_total_for_page += $row['item_price'] * $row['SoLuong'];
                }
                mysqli_stmt_close($stmt_cart);
            }
        }
    } else if (isset($_SESSION['cart'])) {
        // Lấy từ Session (fallback nếu chưa đăng nhập)
        $items_to_display_for_page = $_SESSION['cart'];
        
        if (!empty($items_to_display_for_page)) {
            $cart_total_for_page = 0;
            // Phải tính toán lại giá và tổng cho session cart
            foreach ($items_to_display_for_page as $item_key => &$item) {
                $product_data = get_product_details_by_id_and_category($conn, $item['id'], $item['category']);
                if ($product_data) {
                    $item_price = $product_data['final_price'];
                    $cart_total_for_page += $item_price * $item['quantity'];
                    $item['IdGioHangChiTiet'] = $item_key; 
                    $item['item_price'] = $item_price;
                }
            }
            unset($item);
        }
    }
    
    // LƯU BIẾN ĐỂ TRÁNH BỊ HEADER.PHP GHI ĐÈ
    $_SESSION['_cart_for_page'] = $items_to_display_for_page;
    $_SESSION['_cart_total_for_page'] = $cart_total_for_page;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../components/css/global.css">
    <link rel="stylesheet" href="../components/css/header_sidebar_footer.css">
    <link rel="stylesheet" href="css/giohang.css"> 
    <title>Giỏ hàng</title>
</head>
<body>
    <?php include '../components/header.php'; ?>
    
    <main>
        <?php include '../components/sidebar.php'; ?>
        <div class="home-content">
            <div class="content" style="padding-top: 5rem; width: 80%;">
                
                <a href="../Home/index.php" class="btn-back-home" style="font-size: 1.6rem; color: var(--yellow-color); margin-bottom: 2rem; display: inline-block;">
                    <i class="fa-solid fa-chevron-left"></i> Tiếp tục mua sắm
                </a>
                
                <h1>Giỏ hàng của tôi</h1>
                <div class="giohang-container">
                    <?php 
                    // Dùng lại biến đã lưu để tránh bị header.php ghi đè
                    // Sau khi include header.php lần 2, biến có thể bị reset
                    if (isset($_SESSION['_cart_for_page'])) {
                        $items_to_display = $_SESSION['_cart_for_page'];
                        $cart_total = $_SESSION['_cart_total_for_page'];
                        $is_logged_in = $is_logged_in_for_page;
                        // Xóa session tạm
                        unset($_SESSION['_cart_for_page']);
                        unset($_SESSION['_cart_total_for_page']);
                    } else {
                        // Fallback: dùng biến hiện tại
                        $items_to_display = $items_to_display_for_page ?? [];
                        $cart_total = $cart_total_for_page ?? 0;
                        $is_logged_in = $is_logged_in_for_page ?? false;
                    }
                    
                    if (!empty($items_to_display)): ?>
                        <div class="giohang-list">
                            <div class="giohang-header">
                                <span class="col-product">Sản phẩm</span>
                                <span class="col-price">Giá</span>
                                <span class="col-quantity">Số lượng</span>
                                <span class="col-total">Tổng tiền</span>
                                <span class="col-action">Xóa</span>
                            </div>

                            <?php foreach ($items_to_display as $item): 
                                // Đảm bảo lấy đúng product_id và category từ item
                                $product_id = $item['IdSanPham'] ?? $item['id'] ?? 0;
                                $category = $item['LoaiSanPham'] ?? $item['category'] ?? '';
                                $quantity = $item['SoLuong'] ?? $item['quantity'] ?? 1;
                                $item_key_or_id = $item['IdGioHangChiTiet'] ?? $category . '_' . $product_id; 
                                
                                // Lấy chi tiết sản phẩm để hiển thị đúng tên và ảnh
                                $product_data = get_product_details_by_id_and_category($conn, $product_id, $category);
                                
                                // Sử dụng giá từ DB (đã được lưu khi thêm vào giỏ hàng), không tính lại
                                // Giá trong DB là giá đã tính sale rồi
                                $item_price = $item['item_price'] ?? 0;
                                
                                // Nếu không có giá từ DB, lấy từ product_data (fallback)
                                if ($item_price == 0 && $product_data) {
                                    $item_price = $product_data['final_price'] ?? 0;
                                }
                            
                            // ĐÃ THÊM KIỂM TRA $product_data - Hiển thị ngay cả khi không tìm thấy product_data
                            if ($product_data):
                            ?>
                                <div class="giohang-item-full" id="cart-item-<?php echo $item_key_or_id; ?>">
                                    <div class="col-product">
                                        <img src="/admin/<?php echo $product_data['Img1']; ?>" alt="<?php echo $product_data['Name']; ?>">
                                        <a href="../Pagesproducts/product_detail.php?id=<?php echo $product_id; ?>&category=<?php echo $category; ?>">
                                            <?php echo $product_data['Name']; ?>
                                        </a>
                                    </div>
                                    <span class="col-price"><?php echo number_format($item_price); ?>₫</span>
                                    <div class="col-quantity">
                                        <input type="number" 
                                               value="<?php echo $quantity; ?>" 
                                               min="1" 
                                               data-item-id="<?php echo $item_key_or_id; ?>" 
                                               onchange="updateCartQuantity(this, <?php echo $is_logged_in ? 'true' : 'false'; ?>)"
                                               class="item-quantity-input-full">
                                    </div>
                                    <span class="col-total subtotal-item"><?php echo number_format($item_price * $quantity); ?>₫</span>
                                    <div class="col-action">
                                        <a href="../components/cart_handler.php?action=remove&key=<?php echo $item_key_or_id; ?>" class="remove-btn-full">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php 
                            endif; // Kết thúc kiểm tra $product_data
                            endforeach; ?>
                        </div>
                        <div class="giohang-summary-full">
                            <h2>Tóm tắt đơn hàng</h2>
                            <p>Tổng tiền sản phẩm: <span id="cart-subtotal"><?php echo number_format($cart_total); ?>₫</span></p>
                            <p>Phí vận chuyển: <span>50,000₫</span></p>
                            <h3>Tổng cộng: <span id="cart-grand-total"><?php echo number_format($cart_total + 50000); ?>₫</span></h3>
                            <button onclick="window.location.href='../thanhtoan/thanhtoan.php'">Tiến hành Thanh toán</button>
                        </div>
                    <?php else: ?>
                        <p style="text-align: center; font-size: 1.8rem; margin-top: 5rem; width: 100%;">Giỏ hàng của bạn đang trống.</p>
                        <a href="../Home/index.php" style="display: block; text-align: center; padding: 1rem 2rem; background-color: var(--yellow-color); color: black; border-radius: 0.5rem; margin-top: 2rem;">
                            <i class="fa-solid fa-shopping-bag"></i> Quay lại mua sắm
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include "../components/footer.php"?>
    
    <script src="../components/js/global.js" defer></script> 
    <script src="../components/js/wishlist.js" defer></script> 
    <script>
        function updateCartQuantity(inputElement, is_logged_in) {
            const newQuantity = parseInt(inputElement.value);
            const itemDetailId = inputElement.getAttribute('data-item-id');
            const cartItem = inputElement.closest('.giohang-item-full');
            
            if (newQuantity < 1) {
                if (!confirm("Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?")) {
                    inputElement.value = 1; 
                    return;
                }
                const removeLink = cartItem.querySelector('.remove-btn-full');
                if (removeLink) {
                    window.location.href = removeLink.href;
                    return;
                }
            }
            
            if (is_logged_in) {
                fetch('../components/cart_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', },
                    body: `action=update_quantity&item_detail_id=${itemDetailId}&quantity=${newQuantity}&ajax=1`
                })
                .then(response => {
                    if (!response.ok) { throw new Error('Lỗi phản hồi HTTP: ' + response.status); }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        document.getElementById('cart-subtotal').textContent = data.data.total_price + '₫';
                        document.getElementById('cart-grand-total').textContent = (parseInt(data.data.total_price.replace(/,/g, '')) + 50000).toLocaleString('en-US') + '₫';
                        window.location.reload();
                    } else {
                        alert('Lỗi cập nhật giỏ hàng: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Lỗi AJAX:', error);
                    alert('Lỗi kết nối server khi cập nhật số lượng: ' + error.message);
                });
            } else {
                window.location.href = `../components/cart_handler.php?action=update_session_quantity&key=${itemDetailId}&quantity=${newQuantity}`;
            }
        }
    </script>
</body>
</html>