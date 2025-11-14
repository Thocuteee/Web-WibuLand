<?php
    include '../components/connect.php'; 
    // Include header để có các hàm cần thiết và logic tải giỏ hàng
    include '../components/header.php'; 
    // Các biến $items_to_display, $cart_total, $is_logged_in đã có từ header.php
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
    <?php // Header đã được include ?>
    
    <main>
        <?php include '../components/sidebar.php'; ?>
        <div class="home-content">
            <div class="content" style="padding-top: 5rem;">
                <h1>Giỏ hàng của tôi</h1>
                <div class="giohang-container">
                    <?php if (!empty($items_to_display)): ?>
                        <div class="giohang-list">
                            <?php foreach ($items_to_display as $item): 
                                $product_id = $item['IdSanPham'] ?? $item['id'];
                                $category = $item['LoaiSanPham'] ?? $item['category'];
                                $quantity = $item['SoLuong'] ?? $item['quantity'];
                                $item_key_or_id = $item['IdGioHangChiTiet'] ?? $category . '_' . $product_id; 
                                
                                $product_data = get_product_details_by_id_and_category($conn, $product_id, $category);
                                $item_price = $item['item_price'] ?? $product_data['final_price'];
                            ?>
                                <div class="giohang-item-full">
                                    <img src="/admin/<?php echo $product_data['Img1']; ?>" alt="<?php echo $product_data['Name']; ?>">
                                    <div class="item-details-full">
                                        <h2><?php echo $product_data['Name']; ?></h2>
                                        <p>Giá: <?php echo number_format($item_price); ?>₫</p>
                                        <div class="quantity-control-full">
                                            <span>Số lượng:</span>
                                            <input type="number" 
                                                   value="<?php echo $quantity; ?>" 
                                                   min="1" 
                                                   data-item-id="<?php echo $item_key_or_id; ?>" 
                                                   onchange="updateCartQuantity(this, <?php echo $is_logged_in ? 'true' : 'false'; ?>)"
                                                   class="item-quantity-input-full">
                                            <a href="../components/cart_handler.php?action=remove&key=<?php echo $item_key_or_id; ?>" class="remove-btn-full">Xóa</a>
                                        </div>
                                    </div>
                                    <span class="item-subtotal"><?php echo number_format($item_price * $quantity); ?>₫</span>
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
                        <button onclick="window.location.href='../Home/index.php'" style="padding: 1rem 2rem; background-color: var(--yellow-color); color: white; border-radius: 0.5rem; margin-top: 2rem;">Tiếp tục mua sắm</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include "../components/footer.php"?>
    <script src="../components/js/global.js" defer></script> 
    </body>
</html>