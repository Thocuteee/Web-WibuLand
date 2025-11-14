<?php
    include ("connect.php");
    
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
        $lv = 0;
        $user_name = 'Đăng kí/ Đăng nhập';
        $exp = 0;
        $max_exp = 0;
        
    }


?>

<?php
/**
 * Lấy chi tiết sản phẩm từ database dựa trên ID và Category.
 * (Được đặt trong header vì nó được include trên mọi trang)
 */
function get_product_details_by_id_and_category($conn, $product_id, $category) {
    if (empty($category) || $product_id <= 0) {
        return null;
    }
    
    // Sử dụng Prepared Statement để an toàn
    $select_query = "SELECT Name, Img1, Gia, Sale FROM `$category` WHERE ID = ?";
    $stmt = mysqli_prepare($conn, $select_query);
    
    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        // Tính giá cuối cùng (giá đã giảm)
        $price = $product['Gia'];
        if ($product['Sale'] > 0) {
            $final_price = $price * (1 - $product['Sale'] / 100);
        } else {
            $final_price = $price;
        }
        $product['final_price'] = $final_price;
        return $product;
    }
    
    if ($stmt) {
        mysqli_stmt_close($stmt);
    }
    return null;
}
?>
  
  
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
                            echo ' <a href="#">Đơn hàng</a>';
                            echo ' <a href="#">Kho vocher</a>';
                            echo '<a href="../login&registration/logout.php">Đăng xuất</a>';
                        }else{
                            echo '<a href="../login&registration/login.php">Đăng nhập</a>';
                            echo  ' <a href="../login&registration/registration.php">Đăng kí</a>';
                        }
                    ?>
                </div>

            </div>
            
            <a href="/YeuThich/yeuthich.php" class = "favorite-heart">
                <i class="fa-regular fa-heart"></i>
                <span>Sản phẩm yêu thích</span>
            </a>

            <div class = 'cart-shop' onclick="toggleCartPopup()">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Giỏ hàng</span>
            </div>

            <div id="cart-popup" class="cart-popup">
                    <div class="cart-popup-content">
                        <span class="close-btn" onclick="toggleCartPopup()">&times;</span>
                        <h1>Giỏ hàng của bạn</h1>
                        
                        <div class="cart-items">
                            <?php 
                                $cart_total = 0;
                                // Kiểm tra nếu giỏ hàng tồn tại và không rỗng
                                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])):
                                    foreach ($_SESSION['cart'] as $item_key => $item):
                                        // Lấy chi tiết sản phẩm từ DB
                                        $product_data = get_product_details_by_id_and_category($conn, $item['id'], $item['category']);
                                        
                                        if ($product_data):
                                            $item_subtotal = $product_data['final_price'] * $item['quantity'];
                                            $cart_total += $item_subtotal;
                            ?>
                                <div class="cart-item">
                                    <img src="/admin/<?php echo $product_data['Img1']; ?>" alt="<?php echo $product_data['Name']; ?>">
                                    <div class="item-details">
                                        <h2><?php echo $product_data['Name']; ?></h2>
                                        <p><?php echo number_format($product_data['final_price']); ?>₫ x <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <a href="../components/cart_handler.php?action=remove&key=<?php echo $item_key; ?>" class="remove-btn">Xóa</a>
                                </div>
                                <?php 
                                        endif;
                                    endforeach; 
                                else:
                            ?>
                                <p style="text-align: center; padding: 2rem; font-size: 1.5rem;">Giỏ hàng của bạn đang trống.</p>
                            <?php endif; ?>
                        </div>

                        <div class="cart-summary">
                            <h2>Tổng tiền: <span id="total-price"><?php echo number_format($cart_total); ?> VNĐ</span></h2>
                            <button class="checkout-btn" >
                                <a href="../thanhtoan/thanhtoan.php">Thanh toán</a>
                            </button>
                        </div>
                    </div>
                </div>
        </div>
    </header>