<?php
    include '../components/connect.php'; 
    // Include header để có các hàm cần thiết (get_product_details_by_id_and_category, get_or_create_cart_id)
    include '../components/header.php'; 
    
    // Các biến $items_to_display, $cart_total, $is_logged_in đã có từ header.php
    $cart_total = 0;
    $items_to_display = [];
    $is_logged_in = $user_id !== null;

    // Logic tải Giỏ hàng (tương tự như trong header.php để đảm bảo đồng bộ)
    if ($is_logged_in) {
        $cart_id = get_or_create_cart_id($conn, $user_id);
        if ($cart_id) {
            $select_cart_query = "SELECT IdGioHangChiTiet, IdSanPham, LoaiSanPham, SoLuong, Gia as item_price FROM `giohang_chitiet` WHERE IdGioHang = ?";
            $stmt_cart = mysqli_prepare($conn, $select_cart_query);
            if($stmt_cart) {
                mysqli_stmt_bind_param($stmt_cart, "i", $cart_id);
                mysqli_stmt_execute($stmt_cart);
                $result_cart = mysqli_stmt_get_result($stmt_cart);
                
                while ($row = mysqli_fetch_assoc($result_cart)) {
                    $items_to_display[] = $row;
                    $cart_total += $row['item_price'] * $row['SoLuong'];
                }
                mysqli_stmt_close($stmt_cart);
            }
        }
    } else if (isset($_SESSION['cart'])) {
        // Lấy từ Session (fallback nếu chưa đăng nhập)
        $items_to_display = $_SESSION['cart'];
        
        if (!empty($items_to_display)) {
            $cart_total = 0;
            foreach ($items_to_display as $item_key => &$item) {
                $product_data = get_product_details_by_id_and_category($conn, $item['id'], $item['category']);
                if ($product_data) {
                    $item_price = $product_data['final_price'];
                    $cart_total += $item_price * $item['quantity'];
                    $item['IdGioHangChiTiet'] = $item_key; 
                    $item['item_price'] = $item_price;
                }
            }
            unset($item);
        }
    }
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
                    <?php if (!empty($items_to_display)): ?>
                        <div class="giohang-list">
                            <div class="giohang-header">
                                <span class="col-product">Sản phẩm</span>
                                <span class="col-price">Giá</span>
                                <span class="col-quantity">Số lượng</span>
                                <span class="col-total">Tổng tiền</span>
                                <span class="col-action">Xóa</span>
                            </div>

                            <?php foreach ($items_to_display as $item): 
                                $product_id = $item['IdSanPham'] ?? $item['id'];
                                $category = $item['LoaiSanPham'] ?? $item['category'];
                                $quantity = $item['SoLuong'] ?? $item['quantity'];
                                $item_key_or_id = $item['IdGioHangChiTiet'] ?? $category . '_' . $product_id; 
                                
                                $product_data = get_product_details_by_id_and_category($conn, $product_id, $category);
                                $item_price = $item['item_price'] ?? $product_data['final_price'];
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
                            <?php endforeach; ?>
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