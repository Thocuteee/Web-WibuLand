// File: thanhtoan/thanhtoan.php (Thay thế toàn bộ nội dung file này)

<?php
    include '../components/connect.php';
    include '../components/header.php'; // Cần include header để lấy các hàm và biến user
    
    // Lấy thông tin user từ session
    $user_id = $_SESSION['user_id'] ?? null;
    $user_info = null;

    if ($user_id) {
        $select_user = "SELECT * FROM users WHERE IdUser = ?";
        $stmt_user = mysqli_prepare($conn, $select_user);
        mysqli_stmt_bind_param($stmt_user, "i", $user_id);
        mysqli_stmt_execute($stmt_user);
        $result_user = mysqli_stmt_get_result($stmt_user);
        $user_info = mysqli_fetch_assoc($result_user);
        mysqli_stmt_close($stmt_user);
    }
    
    // Include hàm tính phí vận chuyển
    include '../components/shipping_calculator.php';
    
    // --- KHỞI TẠO LOGIC TẢI GIỎ HÀNG (Giống GioHang/giohang.php) ---
    $items_to_display = [];
    $product_total = 0;
    
    // Tính phí vận chuyển dựa trên địa chỉ (nếu có)
    $shipping_fee = 50000; // Mặc định
    if (isset($user_info['TinhThanh']) && !empty($user_info['TinhThanh'])) {
        $city = $user_info['TinhThanh'];
        $district = $user_info['QuanHuyen'] ?? '';
        $shipping_fee = calculate_shipping_fee($city, $district);
    }
    
    $discount_amount = 0;
    $is_logged_in = $user_id !== null;
    
    // Lưu biến tạm để tránh bị header.php ghi đè khi include lại
    if ($is_logged_in && $conn) {
        $cart_id = get_or_create_cart_id($conn, $user_id);
        if ($cart_id !== false) {
            $select_cart_query = "SELECT IdSanPham, LoaiSanPham, SoLuong, Gia as item_price FROM `giohang_chitiet` WHERE IdGioHang = ? ORDER BY IdGioHangChiTiet DESC";
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
    } else if (isset($_SESSION['cart']) && $conn) {
        $items_to_display = $_SESSION['cart'];
        
        if (!empty($items_to_display)) {
            $product_total = 0;
            foreach ($items_to_display as $item_key => &$item) {
                $product_data = get_product_details_by_id_and_category($conn, $item['id'], $item['category']);
                if ($product_data) {
                    $item_price = $product_data['final_price'];
                    $product_total += $item_price * $item['quantity'];
                    $item['item_price'] = $item_price;
                    // Chuẩn hóa tên cột để đồng nhất với DB
                    $item['IdSanPham'] = $item['id'];
                    $item['LoaiSanPham'] = $item['category'];
                    $item['SoLuong'] = $item['quantity'];
                }
            }
            unset($item);
        }
    }
    
    // Lưu vào session để tránh bị header.php ghi đè
    $_SESSION['_checkout_items'] = $items_to_display;
    $_SESSION['_checkout_total'] = $product_total;
    
    // Tính lại tổng tiền dựa trên items_to_display thực tế
    $product_total = 0;
    if (!empty($items_to_display) && is_array($items_to_display)) {
        foreach ($items_to_display as $item) {
            $quantity = $item['SoLuong'] ?? $item['quantity'] ?? 1;
            $item_price = $item['item_price'] ?? 0;
            
            // Nếu không có giá từ DB, lấy từ product_data
            if ($item_price == 0) {
                $product_id = $item['IdSanPham'] ?? $item['id'] ?? 0;
                $category = $item['LoaiSanPham'] ?? $item['category'] ?? '';
                if ($product_id > 0 && !empty($category) && $conn) {
                    $product_data = get_product_details_by_id_and_category($conn, $product_id, $category);
                    if ($product_data) {
                        $item_price = $product_data['final_price'] ?? 0;
                        $item['item_price'] = $item_price; // Lưu lại để dùng sau
                    }
                }
            }
            
            $product_total += $item_price * $quantity;
        }
    }
    
    // Đảm bảo không bị lỗi chia 0 nếu giỏ hàng rỗng
    $product_total = max(0, $product_total);
    
    // Cập nhật lại vào session
    $_SESSION['_checkout_total'] = $product_total;
    
    // Tổng tiền cuối cùng
    $grand_total = $product_total + $shipping_fee - $discount_amount;
    
    // Lấy thông báo lỗi/thành công từ Session (nếu có)
    $message = $_SESSION['order_message'] ?? null;
    if (isset($_SESSION['order_message'])) {
        unset($_SESSION['order_message']);
    }
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
                
                <div style="text-align: center; margin-bottom: 3rem;">
                    <h1 style="font-size: 3rem; margin-bottom: 0.5rem; color: var(--yellow-color); font-weight: 700; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">Thanh toán</h1>
                    <p style="font-size: 1.6rem; color: #666;">Vui lòng điền đầy đủ thông tin để hoàn tất đơn hàng</p>
                </div>
                
                <?php if ($message): ?>
                    <div class="message <?php echo $message['type']; ?>" style="max-width: 60rem; margin: 0 auto 2rem; padding: 1.5rem; border-radius: 1rem; font-size: 1.5rem; text-align: center; background-color: <?php echo ($message['type'] == 'success' ? '#d4edda' : '#f8d7da'); ?>; color: <?php echo ($message['type'] == 'success' ? '#155724' : '#721c24'); ?>;">
                        <?php echo htmlspecialchars($message['text']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="../components/order_handler.php" id="checkout-form">
                    <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
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
                                        <h2>Thông tin giao hàng *</h2>
                                        <input type="text" id="name" name="name" placeholder="Họ và tên" value="<?php echo htmlspecialchars($user_info['NameUser'] ?? ''); ?>" required>
                                        <input type="text" id="phone" name="phone" placeholder="Số điện thoại" value="<?php echo htmlspecialchars($user_info['SDT'] ?? ''); ?>" required>
                                    </div>
                    
                                    <div class="pay-address">
                                        <label for="address">Địa chỉ *</label>
                                        <div class="pay-country">
                                            <input type="text" id="city" name="city" placeholder="Tỉnh/Thành" value="<?php echo htmlspecialchars($user_info['TinhThanh'] ?? ''); ?>" required>
                                            <input type="text" id="district" name="district" placeholder="Quận/Huyện" required>
                                            <input type="text" id="ward" name="ward" placeholder="Phường/Xã" required>
                                            <div class="address-order">
                                                <input type="text" id="orther" name="address_detail" placeholder="Địa chỉ cụ thể (Số nhà, tên đường...)" value="<?php echo htmlspecialchars($user_info['DiaChi'] ?? ''); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                    
                                <div class="pay-method">
                                    <h3>Phương thức thanh toán *</h3>
                                    <div class="pay-option">
                                        <label for="momo">
                                            <input type="radio" name="payment" value="momo" required>
                                            <img src="/Home/img/MoMo_Logo.png" alt="MoMo">
                                            <span>MoMo</span>
                                        </label>
                    
                                        <label for="vietcombank"> 
                                            <input type="radio" name="payment" value="vietcombank" required>
                                            <img src="/Home/img/Vietcombank.jpg" alt="Vietcombank">
                                            <span>Vietcombank</span>
                                        </label>
                                        
                                        <label for="cod"> 
                                            <input type="radio" name="payment" value="cod" required>
                                            <img src="/Home/img/cash-on-delivery.png" alt="COD" style="filter: invert(1);">
                                            <span>COD</span>
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
                    
                        <div class="product" style="flex: 1; min-width: 40rem;">
                            <h2>Sản phẩm đã chọn</h2>
                            <div class="product-list">
                                <?php 
                                // Lấy lại dữ liệu từ session nếu bị mất sau khi include header.php
                                if (isset($_SESSION['_checkout_items'])) {
                                    $items_to_display = $_SESSION['_checkout_items'];
                                    $product_total = $_SESSION['_checkout_total'] ?? 0;
                                    unset($_SESSION['_checkout_items']);
                                    unset($_SESSION['_checkout_total']);
                                }
                                
                                // Đảm bảo $items_to_display là mảng
                                if (!is_array($items_to_display)) {
                                    $items_to_display = [];
                                }
                                
                                // Lọc bỏ các item không hợp lệ
                                $items_to_display = array_filter($items_to_display, function($item) {
                                    $product_id = $item['IdSanPham'] ?? $item['id'] ?? 0;
                                    $category = $item['LoaiSanPham'] ?? $item['category'] ?? '';
                                    return $product_id > 0 && !empty($category);
                                });
                                
                                if (!empty($items_to_display)): ?>
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
                                    <div class="product-items" style="text-align: center; padding: 3rem; border: 2px dashed #ddd; border-radius: 1rem;">
                                        <p style="font-size: 1.8rem; color: #999; margin-bottom: 1rem; font-weight: 600;">Giỏ hàng của bạn đang trống</p>
                                        <p style="font-size: 1.4rem; color: #666; margin-bottom: 2rem;">Vui lòng thêm sản phẩm vào giỏ hàng trước khi thanh toán</p>
                                        <a href="../GioHang/giohang.php" style="display: inline-block; margin-right: 1rem; padding: 1rem 2rem; background-color: var(--yellow-color); color: black; border-radius: 0.5rem; text-decoration: none; font-weight: 600;">
                                            Xem giỏ hàng
                                        </a>
                                        <a href="../Home/index.php" style="display: inline-block; padding: 1rem 2rem; background-color: #333; color: white; border-radius: 0.5rem; text-decoration: none; font-weight: 600;">
                                            Quay lại mua sắm
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 2rem; margin-top: 2rem; max-width: 60rem; margin-left: auto; margin-right: auto;">
                        <div class="voucher">
                            <select id="voucher-select" name="voucher_code">
                                <option class="voucher-select" value="">Chọn Voucher</option>
                                <option value="10">Giảm 10% (Placeholder)</option>
                                <option value="20">Giảm 20% (Placeholder)</option>
                                <option value="30">Giảm 30% (Placeholder)</option>
                            </select>
                            <button class="apply-button" type="button" onclick="applyVoucher()">Áp dụng</button>
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
                        
                        <input type="hidden" name="product_total" id="input_product_total" value="<?php echo $product_total; ?>">
                        <input type="hidden" name="discount_amount" id="input_discount_amount" value="<?php echo $discount_amount; ?>">
                        <input type="hidden" name="shipping_fee" id="input_shipping_fee" value="<?php echo $shipping_fee; ?>">
                        <input type="hidden" name="total_price_final" id="input_total_price_final" value="<?php echo $grand_total; ?>">
                    
                        <button class="payments-button" type="submit" name="submit_order" <?php echo empty($items_to_display) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>>ĐẶT HÀNG</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <?php include "../components/footer.php"; ?>
    
    <script src="../components/js/global.js" defer></script>
    <script>
        const productTotal = <?php echo $product_total; ?>;
        let shippingFee = <?php echo $shipping_fee; ?>;
        
        // Hàm tính phí vận chuyển dựa trên địa chỉ (JavaScript)
        function calculateShippingFee(city, district) {
            city = city.toLowerCase().trim();
            district = district.toLowerCase().trim();
            
            const hcmVariants = ['hồ chí minh', 'ho chi minh', 'hcm', 'tp.hcm', 'tp hcm', 'thành phố hồ chí minh'];
            const isHCM = hcmVariants.some(v => city.includes(v)) || city.includes('hồ chí minh') || city.includes('ho chi minh');
            const binhThanhVariants = ['bình thạnh', 'binh thanh'];
            const isBinhThanh = binhThanhVariants.some(v => district.includes(v)) || district.includes('bình thạnh') || district.includes('binh thanh');
            
            // Cùng quận Bình Thạnh: 20,000₫
            if (isHCM && isBinhThanh) {
                return 20000;
            }
            
            // Khác quận nhưng cùng HCM: 30,000₫ - 40,000₫
            if (isHCM && !isBinhThanh) {
                const innerDistricts = ['quận 1', 'quận 2', 'quận 3', 'quận 4', 'quận 5', 'quận 6', 'quận 7', 'quận 8', 
                    'quận 9', 'quận 10', 'quận 11', 'quận 12', 'tân bình', 'tân phú', 'phú nhuận',
                    'gò vấp', 'bình tân', 'thủ đức'];
                const isInner = innerDistricts.some(d => district.includes(d));
                return isInner ? 30000 : 40000;
            }
            
            // Miền Nam: 50,000₫ - 70,000₫
            const southProvinces = ['bình dương', 'đồng nai', 'bà rịa', 'vũng tàu', 'tây ninh', 
                'bình phước', 'long an', 'tiền giang', 'bến tre', 'vĩnh long', 'đồng tháp',
                'an giang', 'kiên giang', 'cà mau', 'bạc liêu', 'sóc trăng', 'trà vinh',
                'hậu giang', 'cần thơ'];
            const isSouth = southProvinces.some(p => city.includes(p));
            if (isSouth) {
                const nearHCM = ['bình dương', 'đồng nai', 'bà rịa', 'vũng tàu', 'tây ninh', 'bình phước', 'long an'];
                const isNear = nearHCM.some(p => city.includes(p));
                return isNear ? 50000 : 70000;
            }
            
            // Miền Trung: 80,000₫ - 100,000₫
            const centralProvinces = ['đà nẵng', 'quảng nam', 'quảng ngãi', 'bình định', 'phú yên', 'khánh hòa',
                'ninh thuận', 'bình thuận', 'quảng bình', 'quảng trị', 'thừa thiên huế',
                'kon tum', 'gia lai', 'đắk lắk', 'đắk nông', 'lâm đồng'];
            const isCentral = centralProvinces.some(p => city.includes(p));
            if (isCentral) {
                const nearCentral = ['đà nẵng', 'quảng nam', 'khánh hòa', 'bình thuận'];
                const isNear = nearCentral.some(p => city.includes(p));
                return isNear ? 80000 : 100000;
            }
            
            // Miền Bắc: 100,000₫ - 150,000₫
            const northProvinces = ['hà nội', 'hải phòng', 'hải dương', 'hưng yên', 'thái bình', 'nam định',
                'ninh bình', 'hà nam', 'bắc ninh', 'bắc giang', 'quảng ninh', 'lạng sơn',
                'cao bằng', 'bắc kạn', 'thái nguyên', 'tuyên quang', 'hà giang', 'yên bái',
                'lào cai', 'điện biên', 'sơn la', 'hoà bình', 'phú thọ', 'vĩnh phúc'];
            const isNorth = northProvinces.some(p => city.includes(p));
            if (isNorth) {
                const nearNorth = ['hà nội', 'hải phòng', 'hải dương', 'bắc ninh', 'hưng yên', 'vĩnh phúc'];
                const isNear = nearNorth.some(p => city.includes(p));
                return isNear ? 100000 : 150000;
            }
            
            // Mặc định: 80,000₫
            return 80000;
        }
        
        // Cập nhật phí vận chuyển khi người dùng nhập địa chỉ
        function updateShippingFee() {
            const city = document.getElementById('city').value.trim();
            const district = document.getElementById('district').value.trim();
            
            if (city && district) {
                shippingFee = calculateShippingFee(city, district);
                updateTotal();
            }
        }
        
        // Cập nhật tổng tiền
        function updateTotal() {
            const discountAmount = parseInt(document.getElementById('input_discount_amount').value) || 0;
            const grandTotal = productTotal + shippingFee - discountAmount;
            
            document.querySelector('.shipping-fee').textContent = shippingFee.toLocaleString('en-US') + '₫';
            document.querySelector('.grand-total').textContent = grandTotal.toLocaleString('en-US') + '₫';
            document.getElementById('input_shipping_fee').value = shippingFee;
            document.getElementById('input_total_price_final').value = grandTotal;
        }
        
        // Lắng nghe sự kiện thay đổi địa chỉ
        document.getElementById('city').addEventListener('blur', updateShippingFee);
        document.getElementById('district').addEventListener('blur', updateShippingFee);

        // Thêm class selected cho label khi radio được chọn (JS)
        document.querySelectorAll('input[name="payment"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.pay-option label').forEach(label => {
                    label.classList.remove('selected');
                });
                if (this.checked) {
                    this.closest('label').classList.add('selected');
                }
            });
            if (radio.checked) {
                radio.closest('label').classList.add('selected');
            }
        });

        // Cập nhật bộ đếm ký tự
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
            let discountAmount = 0;
            let currentProductTotal = productTotal; // Bắt đầu từ tổng tiền gốc

            if (discountPercent > 0) {
                discountAmount = Math.round(currentProductTotal * discountPercent / 100);
            }
            
            // Cập nhật lại shipping fee nếu địa chỉ đã được nhập
            const city = document.getElementById('city').value.trim();
            const district = document.getElementById('district').value.trim();
            if (city && district) {
                shippingFee = calculateShippingFee(city, district);
            }
            
            const grandTotal = currentProductTotal + shippingFee - discountAmount;
            
            document.querySelector('.discount-amount').textContent = discountAmount.toLocaleString('en-US') + '₫';
            document.querySelector('.grand-total').textContent = grandTotal.toLocaleString('en-US') + '₫';
            document.querySelector('.shipping-fee').textContent = shippingFee.toLocaleString('en-US') + '₫';

            // Cập nhật giá trị ẩn để gửi đi
            document.getElementById('input_discount_amount').value = discountAmount;
            document.getElementById('input_shipping_fee').value = shippingFee;
            document.getElementById('input_total_price_final').value = grandTotal;

            alert(`Giảm giá ${discountPercent}% đã được áp dụng!`);
        }
        
        // Không cần hàm submitOrder() nữa, thay bằng submit form HTML
        // Đảm bảo Form được validate đúng trước khi submit
        document.getElementById('checkout-form').addEventListener('submit', function(event) {
            // Kiểm tra các trường bắt buộc mà HTML không bắt được (nếu cần)
            const itemsCount = <?php echo (isset($items_to_display) && is_array($items_to_display)) ? count($items_to_display) : 0; ?>;
            if (itemsCount === 0) {
                event.preventDefault();
                alert('Giỏ hàng của bạn đang trống! Vui lòng thêm sản phẩm vào giỏ hàng trước khi thanh toán.');
                window.location.href = '../GioHang/giohang.php';
                return false;
            }
            
            // Kiểm tra các trường bắt buộc
            const name = document.getElementById('name').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const city = document.getElementById('city').value.trim();
            const district = document.getElementById('district').value.trim();
            const ward = document.getElementById('ward').value.trim();
            const addressDetail = document.getElementById('orther').value.trim();
            const paymentMethod = document.querySelector('input[name="payment"]:checked');
            
            if (!name || !phone || !city || !district || !ward || !addressDetail || !paymentMethod) {
                event.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin giao hàng và chọn phương thức thanh toán!');
                return false;
            }
            
            // Nếu không có lỗi, form sẽ được submit tới components/order_handler.php
            // Không cần alert này vì có thể làm phiền người dùng
            // alert('Đang xử lý đơn hàng, vui lòng chờ...');
        });
    </script>
</body>
</html>