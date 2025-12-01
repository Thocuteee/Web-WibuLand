<?php
    include ("connect.php");
    
    // --- Lấy thông tin User ---
    if (isset($_SESSION['user_id'])){
        $user_id = $_SESSION['user_id'];

        $select_user = mysqli_query($conn,"SELECT * FROM users WHERE IdUser = '$user_id'");
        $user_info = mysqli_fetch_array($select_user);
        $user_name = $user_info['NameUser'];
        

        $select_lv = mysqli_query($conn,"SELECT * FROM expuser WHERE IdUser = '$user_id'");
        $user_lv = mysqli_fetch_array($select_lv);
        $lv = $user_lv['lv_user'];
        $exp = $user_lv['exp'];
        $max_exp = $user_lv['max_exp'];
    }else{
        $user_id = null; // Khởi tạo user_id là null nếu chưa đăng nhập
        $lv = 0;
        $user_name = 'Đăng kí/ Đăng nhập';
        $exp = 0;
        $max_exp = 0;
        
    }


?>

<?php
if (!function_exists('get_product_details_by_id_and_category')) {
/**
 * Lấy chi tiết sản phẩm từ database dựa trên ID và Category.
 */
function get_product_details_by_id_and_category($conn, $product_id, $category) {
    if (empty($category) || $product_id <= 0) {
        return null;
    }
    
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
        $product['TheLoaiId'] = $product['TheLoai'];
        return $product;
    }
    
    if ($stmt) mysqli_stmt_close($stmt);
    return null;
}
}


if (!function_exists('get_or_create_cart_id')) {
/**
 * Hàm lấy ID Giỏ hàng (Tạo mới nếu chưa có)
 */
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
        return (int)$row['IDGioHang'];
    } else {
        // Tạo giỏ hàng mới
        mysqli_stmt_close($stmt);
        $insert_query = "INSERT INTO `giohang` (IdUser, TongGiaTien, TongSoLuong) VALUES (?, 0, 0)";
        $stmt_insert = mysqli_prepare($conn, $insert_query);
        
        if (!$stmt_insert) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt_insert, "i", $user_id);
        $execute_result = mysqli_stmt_execute($stmt_insert);
        
        if ($execute_result) {
            $new_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt_insert);
            if ($new_id > 0) {
                return (int)$new_id;
            }
        } else {
            mysqli_stmt_close($stmt_insert);
            return false;
        }
        mysqli_stmt_close($stmt_insert);
        return false;
    }
}
}
?>
  
  <script src="https://kit.fontawesome.com/eff669a9ab.js" crossorigin="anonymous"></script>

  <header>
        <div class='logo'>
            <img src="/Home/img/logo_1.png" alt=""  onclick = 'window.location.href="/Home/index.php"'>
        </div>
        <form action="" class="search-bar">
            <input type="text" placeholder="Bạn đang tìm gì...?">
            <i class="fa-solid fa-magnifying-glass"></i>
        </form>
        <div class="exp-bar">
            <span class ="exp">  
                EXP:
                <span id="exp-value"><?php echo $exp?></span>
                <span id="exp-value-max"><?php echo "/".$max_exp?></span>
            </span>
            <div class="progress">
                <div id="exp-progress"></div>
            </div>
        </div>
        <div class = 'user-bar'>
            <div class="user">
                <div class = 'img-user'>
                    <i class="fa-solid fa-circle-user fa-flip-horizontal"></i>
                </div>
    
                <div class ="user-info">
                    <span id = "user-name"><?php echo $user_name?></span>
                    <span id = "lv">Lv: <?php echo $lv ?></span>
                </div>

                <div class="dropdown-menu">
                    <?php
                        if (isset($_SESSION['user_id'])){
                            echo ' <a href="/thongtinkhachhang/thongtinkhachhang.php">Thông tin tài khoản</a>';
                            echo '<a href="../login&registration/logout.php">Đăng xuất</a>';
                        }else{
                            echo '<a href="../login&registration/login.php">Đăng nhập</a>';
                            echo  ' <a href="../login&registration/registration.php">Đăng kí</a>';
                        }
                    ?>
                </div>

            </div>
            
            <a href="/YeuThich/yeuthich.php" class="favorite-heart">
                <i class="fa-regular fa-heart"></i>
                <span>Sản phẩm yêu thích</span>
            </a>

            <div class="cart-shop" onclick="toggleCartPopup();" style="cursor: pointer;"> 
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Giỏ hàng</span>
            </div>

            <div id="cart-popup" class="cart-popup">
                    <div class="cart-popup-content">
                        <span class="close-btn" onclick="toggleCartPopup()">&times;</span>
                        <h1>Giỏ hàng của bạn 
                            <?php if (!empty($items_to_display)): ?>
                                <span style="font-size: 1.4rem; color: #666;">(<?php echo count($items_to_display); ?> sản phẩm)</span>
                            <?php endif; ?>
                        </h1>
                        
                        <div class="cart-items">
                            <?php 
                                $cart_total = 0;
                                $items_to_display = [];
                                $is_logged_in = $user_id !== null;

                                // Lấy dữ liệu Giỏ hàng từ DB (nếu đã đăng nhập)
                                if ($is_logged_in) {
                                    $cart_id = get_or_create_cart_id($conn, $user_id);
                                    // SỬA LỖI: Kiểm tra $cart_id khác FALSE để chấp nhận giá trị 0
                                    if ($cart_id !== false) { 
                                        
                                        // Lấy danh sách sản phẩm chi tiết
                                        $select_cart_query = "SELECT IdGioHangChiTiet, IdSanPham, LoaiSanPham as category, SoLuong as quantity, Gia as item_price FROM `giohang_chitiet` WHERE IdGioHang = ?";
                                        $stmt_cart = mysqli_prepare($conn, $select_cart_query);
                                        if($stmt_cart) {
                                            mysqli_stmt_bind_param($stmt_cart, "i", $cart_id);
                                            mysqli_stmt_execute($stmt_cart);
                                            $result_cart = mysqli_stmt_get_result($stmt_cart);
                                            
                                            while ($row = mysqli_fetch_assoc($result_cart)) {
                                                $items_to_display[] = $row;
                                            }
                                            mysqli_stmt_close($stmt_cart);
                                        }
                                        
                                        // Tính toán tổng tiền từ TẤT CẢ các items (đảm bảo chính xác)
                                        $cart_total = 0;
                                        foreach ($items_to_display as &$item) {
                                            // Sửa: DB dùng SoLuong, nhưng query alias là quantity, nên cần check cả 2
                                            $quantity = $item['quantity'] ?? $item['SoLuong'] ?? 0;
                                            $item_price = $item['item_price'] ?? 0;
                                            $item_subtotal = $item_price * $quantity;
                                            $cart_total += $item_subtotal;
                                        }
                                        unset($item);
                                    }
                                } else if (isset($_SESSION['cart'])) {
                                    // Lấy từ Session (fallback nếu chưa đăng nhập)
                                    $items_to_display = $_SESSION['cart'];
                                }
                                // Tính toán tổng tiền cho Session Cart (nếu có)
                                if (!$is_logged_in && !empty($items_to_display)) {
                                    $cart_total = 0;
                                    foreach ($items_to_display as $item_key => &$item) {
                                        $product_data = get_product_details_by_id_and_category($conn, $item['id'], $item['category']);
                                        if ($product_data) {
                                            $item_price = $product_data['final_price'];
                                            $item_subtotal = $item_price * $item['quantity'];
                                            $cart_total += $item_subtotal;
                                            // Gán ID/Key cho mục đích xóa/cập nhật
                                            $item['IdGioHangChiTiet'] = $item_key; 
                                            $item['item_price'] = $item_price;
                                        }
                                    }
                                    unset($item);
                                }


                                if (!empty($items_to_display)):
                                    // Đếm số lượng items để debug
                                    $total_items = count($items_to_display);
                                    foreach ($items_to_display as $item):
                                        $product_id = $item['IdSanPham'] ?? $item['id'];
                                        $category = $item['LoaiSanPham'] ?? $item['category'];
                                        $quantity = $item['SoLuong'] ?? $item['quantity'];
                                        // ID Chi Tiết (DB) hoặc key (Session)
                                        $item_key_or_id = $item['IdGioHangChiTiet'] ?? $category . '_' . $product_id; 
                                        
                                        // Lấy chi tiết sản phẩm để hiển thị tên và ảnh
                                        $product_data = get_product_details_by_id_and_category($conn, $product_id, $category);
                                        $item_price = $item['item_price'] ?? ($product_data['final_price'] ?? 0);
                            
                            // ĐÃ THÊM KIỂM TRA $product_data - Hiển thị tất cả items, kể cả khi không tìm thấy product_data
                            if ($product_data):
                            ?>
                                <div class="cart-item">
                                    <img src="/admin/<?php echo $product_data['Img1']; ?>" alt="<?php echo htmlspecialchars($product_data['Name']); ?>">
                                    <div class="item-details">
                                        <h2><?php echo htmlspecialchars($product_data['Name']); ?></h2>
                                        <p>
                                            <?php echo number_format($item_price); ?>₫ x 
                                            <input type="number" 
                                                value="<?php echo $quantity; ?>" 
                                                min="1" 
                                                data-item-id="<?php echo htmlspecialchars($item_key_or_id); ?>" 
                                                onchange="updateCartQuantity(this, <?php echo $is_logged_in ? 'true' : 'false'; ?>)"
                                                class="item-quantity-input">
                                        </p>
                                    </div>
                                    <a href="../components/cart_handler.php?action=remove&key=<?php echo urlencode($item_key_or_id); ?>" class="remove-btn">Xóa</a>
                                </div>
                                <?php 
                            else:
                                // Hiển thị item ngay cả khi không tìm thấy product_data (sản phẩm đã bị xóa)
                            ?>
                                <div class="cart-item" style="opacity: 0.6;">
                                    <div style="width: 60px; height: 60px; background-color: #ddd; border-radius: 5px; margin-right: 15px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-image" style="color: #999;"></i>
                                    </div>
                                    <div class="item-details">
                                        <h2 style="color: #999;">Sản phẩm không còn tồn tại</h2>
                                        <p style="color: #999;">
                                            ID: <?php echo $product_id; ?> - Category: <?php echo htmlspecialchars($category); ?>
                                            <input type="number" 
                                                value="<?php echo $quantity; ?>" 
                                                min="1" 
                                                data-item-id="<?php echo htmlspecialchars($item_key_or_id); ?>" 
                                                onchange="updateCartQuantity(this, <?php echo $is_logged_in ? 'true' : 'false'; ?>)"
                                                class="item-quantity-input" 
                                                disabled>
                                        </p>
                                    </div>
                                    <a href="../components/cart_handler.php?action=remove&key=<?php echo urlencode($item_key_or_id); ?>" class="remove-btn">Xóa</a>
                                </div>
                                <?php 
                            endif; // Kết thúc kiểm tra $product_data
                                    endforeach; 
                                else:
                            ?>
                                <p style="text-align: center; padding: 2rem; font-size: 1.5rem;">Giỏ hàng của bạn đang trống.</p>
                            <?php endif; ?>
                        </div>

                        <div class="cart-summary">
                            <h2>Tổng tiền: <span id="total-price"><?php echo number_format($cart_total); ?> VNĐ</span></h2>
                            <a href="../GioHang/giohang.php" style="display: block; text-align: center; padding: 0.8rem; background-color: #f0f0f0; color: black; border-radius: 0.5rem; margin-bottom: 1rem; text-decoration: none; font-size: 1.4rem; font-weight: 600;">
                                <i class="fa-solid fa-eye"></i> Xem chi tiết giỏ hàng
                            </a>
                            <button class="checkout-btn" onclick="window.location.href='../thanhtoan/thanhtoan.php'">
                                Thanh toán
                            </button>
                        </div>
                    </div>
                </div>
        </div>
    </header>

    <script>
    // Hàm JavaScript để gửi yêu cầu AJAX cập nhật số lượng
    function updateCartQuantity(inputElement, is_logged_in) {
        const newQuantity = parseInt(inputElement.value);
        const itemDetailId = inputElement.getAttribute('data-item-id');
        const cartItem = inputElement.closest('.cart-item');
        
        if (newQuantity < 1) {
            if (!confirm("Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?")) {
                inputElement.value = 1; 
                return;
            }
            // Nếu đồng ý xóa, chuyển hướng sang link xóa
            const removeLink = cartItem.querySelector('.remove-btn');
            if (removeLink) {
                 window.location.href = removeLink.href;
                 return;
            }
        }
        
        if (is_logged_in) {
            // Xử lý AJAX cho người dùng đã đăng nhập (sử dụng DB)
            fetch('../components/cart_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_quantity&item_detail_id=${itemDetailId}&quantity=${newQuantity}&ajax=1`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Lỗi phản hồi HTTP: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('total-price').textContent = data.data.total_price + ' VNĐ';
                    
                    if (newQuantity < 1) {
                        cartItem.remove();
                    }
                } else {
                    alert('Lỗi cập nhật giỏ hàng: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Lỗi AJAX:', error);
                alert('Lỗi kết nối server khi cập nhật số lượng: ' + error.message);
            });
        } else {
            // Xử lý giỏ hàng Session (chuyển hướng tải lại trang để PHP xử lý session)
            window.location.href = `../components/cart_handler.php?action=update_session_quantity&key=${itemDetailId}&quantity=${newQuantity}`;
        }
    }

    // Hàm reload popup giỏ hàng (tải lại dữ liệu từ server)
    function reloadCartPopup() {
        const cartPopup = document.getElementById('cart-popup');
        if (cartPopup) {
            // Reload trang để lấy dữ liệu mới từ PHP
            // Hoặc có thể dùng AJAX để reload chỉ phần cart-popup
            // Ở đây ta reload trang để đảm bảo dữ liệu luôn mới nhất
            window.location.reload();
        }
    }

    // Tự động mở popup giỏ hàng sau khi thêm sản phẩm
    window.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('cart_added') === '1') {
            // Mở popup giỏ hàng
            const cartPopup = document.getElementById('cart-popup');
            const cartItems = cartPopup ? cartPopup.querySelector('.cart-items') : null;
            
            if (cartPopup) {
                cartPopup.style.display = 'block';
                // Scroll lên đầu danh sách items để hiển thị items mới thêm
                if (cartItems) {
                    cartItems.scrollTop = 0;
                }
            }
            
            // Xóa parameter khỏi URL để tránh mở lại khi refresh
            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.search.replace(/[?&]cart_added=1/, '').replace(/^&/, '?');
            window.history.replaceState({}, document.title, newUrl);
        }
    });
    </script>