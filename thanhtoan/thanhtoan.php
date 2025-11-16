<?php
    include '../components/connect.php';
    
    // Lấy thông tin user từ session
    if (isset($_SESSION['user_id'])){
        $user_id = $_SESSION['user_id'];
    } else {
        $user_id = null;
    }
    
    // Định nghĩa các hàm cần thiết
    function get_product_details_by_id_and_category($conn, $product_id, $category) {
        if (empty($category) || $product_id <= 0) {
            return null;
        }
        
        $select_query = "SELECT Name, Img1, Gia, Sale FROM `$category` WHERE ID = ?";
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
            return $product;
        }
        
        if ($stmt) mysqli_stmt_close($stmt);
        return null;
    }
    
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
    
    // Khởi tạo các biến
    $items_to_display = [];
    $product_total = 0;
    $shipping_fee = 50000;
    $discount_amount = 0;
    $is_logged_in = $user_id !== null;

    // --- LOGIC TẢI GIỎ HÀNG ---
    if ($is_logged_in) {
        $cart_id = get_or_create_cart_id($conn, $user_id);
        if ($cart_id !== false) {
            $select_cart_query = "SELECT IdGioHangChiTiet, IdSanPham, LoaiSanPham, SoLuong, Gia as item_price FROM `giohang_chitiet` WHERE IdGioHang = ? ORDER BY IdGioHangChiTiet DESC";
            $stmt_cart = mysqli_prepare($conn, $select_cart_query);
            if($stmt_cart) {
                mysqli_stmt_bind_param($stmt_cart, "i", $cart_id);
                mysqli_stmt_execute($stmt_cart);
                $result_cart = mysqli_stmt_get_result($stmt_cart);
                
                while ($row = mysqli_fetch_assoc($result_cart)) {
                    $items_to_display[] = $row;
                    $product_total += $row['item_price'] * $row['SoLuong'];
                }
                mysqli_stmt_close($stmt_cart);
            }
        }
    } else if (isset($_SESSION['cart'])) {
        $items_to_display = $_SESSION['cart'];
        
        if (!empty($items_to_display)) {
            $product_total = 0;
            foreach ($items_to_display as $item_key => &$item) {
                $product_data = get_product_details_by_id_and_category($conn, $item['id'], $item['category']);
                if ($product_data) {
                    $item_price = $product_data['final_price'];
                    $product_total += $item_price * $item['quantity'];
                    $item['IdGioHangChiTiet'] = $item_key; 
                    $item['item_price'] = $item_price;
                }
            }
            unset($item);
        }
    }
    
    $grand_total = $product_total + $shipping_fee - $discount_amount;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../components/css/global.css">
    <link rel="stylesheet" href="../components/css/header_sidebar_footer.css">
    <link rel="stylesheet" href="css/thanhtoan.css">
    <title>Thanh toán - Wibu Dreamland</title>
</head>
<body>
    <?php include '../components/header.php'; ?>
    
    <main>
        <?php include '../components/sidebar.php'; ?>
        <div class="home-content">
            <div class="content" style="padding-top: 5rem; width: 95%; max-width: 140rem; margin: 0 auto;">
                
                <h1 style="text-align: center; font-size: 2.5rem; margin-bottom: 2rem; color: var(--yellow-color);">Thanh toán</h1>
                
                <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                    <!-- Form thông tin giao hàng -->
                    <div id="pay-header" style="flex: 1; min-width: 50rem;">
                        <div id="pay-header-1">
                            <div class="pay">
                                <div class="main-pay">
                                    <a href="../Home/index.php" class="left-link"><i>Trở về Home</i></a>
                                    <a href="../GioHang/giohang.php" class="right-link"><i>Trở về giỏ hàng</i></a>
                                </div>
                            </div>
                        </div>
                
                        <div id="pay-header-2">
                            <div class="pay-imformation">
                                <div class="pay-imform">
                                    <h2 style="font-size: 1.4rem; margin-left: 30px; margin-top: -25px;">Thông tin giao hàng *</h2>
                                    <input type="text" id="name" name="name" placeholder="Họ và tên" required>
                                    <input type="text" id="phone" name="phone" placeholder="Số điện thoại" required>
                                </div>
                
                                <div class="pay-address">
                                    <label for="address" style="font-size: 20px; color: gray; border-bottom: 1px dashed gray;">Địa chỉ *</label>
                                    <div class="pay-country">
                                        <input type="text" id="city" name="city" placeholder="Tỉnh/Thành" required>
                                        <input type="text" id="district" name="district" placeholder="Quận/Huyện" required>
                                        <input type="text" id="ward" name="ward" placeholder="Phường/Xã" required>
                                        <div class="address-order">
                                            <input type="text" id="orther" name="address_detail" placeholder="Địa chỉ cụ thể" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                
                            <div class="pay-method">
                                <h3 style="margin-left: 100px;margin-bottom: 40px; font-size: 1.3rem;">Phương thức thanh toán điện tử *</h3>
                                <div class="pay-option">
                                    <label for="momo">
                                        <input type="radio" name="payment" value="momo" required>
                                        <img src="/Home/img/MoMo_Logo.png" alt="MoMo">
                                    </label>
                
                                    <label for="vietcombank"> 
                                        <input type="radio" name="payment" value="vietcombank" required>
                                        <img src="/Home/img/Vietcombank.jpg" alt="Vietcombank">
                                    </label>
                                </div>
                            </div>
                
                            <div class="pay-note">
                                <div class="note-box">
                                    <h3>Lời nhắn:</h3>
                                    <textarea id="note" name="note" placeholder="Lưu ý cho Shop..." maxlength="200"></textarea>
                                    <div class="character-count">0/200</div>
                                </div>
                            </div>
                        </div> 
                    </div> 
                
                    <!-- Danh sách sản phẩm -->
                    <div class="product" style="flex: 1; min-width: 40rem;">
                        <h2 style="text-align: center; font-size: 1.8rem; margin-bottom: 1rem; color: var(--yellow-color);">Sản phẩm đã chọn</h2>
                        <div class="product-list">
                            <?php if (!empty($items_to_display)): ?>
                                <?php foreach ($items_to_display as $item): 
                                    $product_id = $item['IdSanPham'] ?? $item['id'] ?? 0;
                                    $category = $item['LoaiSanPham'] ?? $item['category'] ?? '';
                                    $quantity = $item['SoLuong'] ?? $item['quantity'] ?? 1;
                                    
                                    $product_data = get_product_details_by_id_and_category($conn, $product_id, $category);
                                    $item_price = $item['item_price'] ?? 0;
                                    
                                    if ($item_price == 0 && $product_data) {
                                        $item_price = $product_data['final_price'] ?? 0;
                                    }
                                
                                    if ($product_data):
                                ?>
                                    <div class="product-items">
                                        <img src="/admin/<?php echo $product_data['Img1']; ?>" alt="<?php echo htmlspecialchars($product_data['Name']); ?>">
                                        <div class="product-detail">
                                            <span class="product-title"><?php echo htmlspecialchars($product_data['Name']); ?></span>
                                        </div>
                                        <div class="product-info">
                                            <span class="product-quantity">Số lượng: <?php echo $quantity; ?></span>
                                            <span class="product-price"><?php echo number_format($item_price * $quantity); ?>₫</span>
                                        </div>
                                    </div>
                                <?php 
                                    endif;
                                endforeach; 
                            else: ?>
                                <div class="product-items" style="text-align: center; padding: 2rem;">
                                    <p style="font-size: 1.6rem; color: #666;">Giỏ hàng của bạn đang trống.</p>
                                    <a href="../Home/index.php" style="display: inline-block; margin-top: 1rem; padding: 1rem 2rem; background-color: var(--yellow-color); color: black; border-radius: 0.5rem; text-decoration: none;">
                                        Quay lại mua sắm
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Voucher và Summary -->
                <div style="display: flex; flex-direction: column; gap: 2rem; margin-top: 2rem; max-width: 60rem; margin-left: auto; margin-right: auto;">
                    <div class="voucher">
                        <select id="voucher-select">
                            <option class="voucher-select" value="">Chọn Voucher</option>
                            <option value="10">Giảm 10%</option>
                            <option value="20">Giảm 20%</option>
                            <option value="30">Giảm 30%</option>
                        </select>
                        <button class="apply-button" onclick="applyVoucher()">Áp dụng</button>
                    </div>

                    <div class="summary">
                        <p>Tổng phí sản phẩm: <span class="product-total"><?php echo number_format($product_total); ?>₫</span></p>
                        <p>Phí vận chuyển: <span class="shipping-fee"><?php echo number_format($shipping_fee); ?>₫</span></p>
                        <p>Chi phí giảm: <span class="discount-amount"><?php echo number_format($discount_amount); ?>₫</span></p>
                        <h3>
                            <span>Tổng:</span> 
                            <span class="grand-total"><?php echo number_format($grand_total); ?>₫</span>
                        </h3>
                    </div>
                
                    <button class="payments-button" onclick="submitOrder()">Đặt hàng</button>
                </div>
            </div>
        </div>
    </main>
    
    <?php include "../components/footer.php"; ?>
    
    <script src="../components/js/global.js" defer></script>
    <script>
        const noteTextarea = document.getElementById('note');
        const characterCount = document.querySelector('.character-count');
        
        if (noteTextarea && characterCount) {
            noteTextarea.addEventListener('input', function() {
                const count = this.value.length;
                characterCount.textContent = count + '/200';
            });
        }
        
        function applyVoucher() {
            const voucherSelect = document.getElementById('voucher-select');
            const discountPercent = parseInt(voucherSelect.value);
            const productTotal = <?php echo $product_total; ?>;
            const shippingFee = <?php echo $shipping_fee; ?>;
            
            if (discountPercent > 0) {
                const discountAmount = Math.round(productTotal * discountPercent / 100);
                const grandTotal = productTotal + shippingFee - discountAmount;
                
                document.querySelector('.discount-amount').textContent = discountAmount.toLocaleString('en-US') + '₫';
                document.querySelector('.grand-total').textContent = grandTotal.toLocaleString('en-US') + '₫';
            } else {
                document.querySelector('.discount-amount').textContent = '0₫';
                document.querySelector('.grand-total').textContent = (productTotal + shippingFee).toLocaleString('en-US') + '₫';
            }
        }
        
        function submitOrder() {
            const name = document.getElementById('name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const city = document.getElementById('city').value.trim();
            const district = document.getElementById('district').value.trim();
            const ward = document.getElementById('ward').value.trim();
            const addressDetail = document.getElementById('orther').value.trim();
            const paymentMethod = document.querySelector('input[name="payment"]:checked');
            
            if (!name || !phone || !city || !district || !ward || !addressDetail) {
                alert('Vui lòng điền đầy đủ thông tin giao hàng!');
                return;
            }
            
            if (!paymentMethod) {
                alert('Vui lòng chọn phương thức thanh toán!');
                return;
            }
            
            const itemsCount = <?php echo count($items_to_display); ?>;
            if (itemsCount === 0) {
                alert('Giỏ hàng của bạn đang trống!');
                window.location.href = '../GioHang/giohang.php';
                return;
            }
            
            alert('Đơn hàng của bạn đã được gửi thành công! (Tính năng đang được phát triển)');
        }
    </script>
</body>
</html>
