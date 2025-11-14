<?php
    include '../components/connect.php'; // Kết nối DB và bắt đầu session

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
    
    // Khởi tạo các biến tổng
    $product_total = 0;
    $shipping_fee = 50000; // Phí vận chuyển cố định
    $discount_amount = 0; // Giảm giá mặc định

    // Tính toán tổng tiền
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item_key => $item) {
            $product_data = get_product_details_by_id_and_category($conn, $item['id'], $item['category']);
            if ($product_data) {
                $product_total += $product_data['final_price'] * $item['quantity'];
            }
        }
    }
    
    // Tính tổng cộng cuối cùng
    $grand_total = $product_total + $shipping_fee - $discount_amount;
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Css -->
    <link rel="stylesheet" href="../components/css/global.css">
    <link rel="stylesheet" href="../components/css/header_sidebar_footer.css">
    <link rel="stylesheet" href="css/thanhtoan.css">
        

        
    <title>Wibu Dreamland</title>
</head>
<body>
                <div id="pay-header">
        <!--Payments-Button-->
                        <div id="pay-header-1">
                            <div class="pay">
                                <div class="main-pay">
                                    <a href="../Home/index.html" class="left-link"><i>Trở về Home</i></a>
                                    <a href="#" class="right-link"><i>Trở về giỏ hàng</i></a>
                                </div>
                            </div>
                        </div>
                    
                        <!--Payments-Imformation-->
                        <div id="pay-header-2">
                            <div class="pay-imformation">
                                <div class="pay-imform">
                                    <h2 style="font-size: 1.4rem; margin-left: 30px; margin-top: -25px;">Thông tin giao hàng *</h2>
                                    <input type="text" id="name" placeholder="Họ và tên">
                                    <input type="text" id="phone" placeholder="Số điện thoại">
                                </div>
                    
                                <div class="pay-address">
                                    <label for="address" style="font-size: 20px; color: gray; border-bottom: 1px dashed gray;">Địa chỉ *</label>
                                    <div class="pay-country">
                                        <input type="text" id="city" placeholder="Tỉnh/Thành">
                                        <input type="text" id="district" placeholder="Quận/Huyện">
                                        <input type="text" id="ward" placeholder="Phường/Xã">
                                        <div class="address-order">
                                            <input type="text" id="orther" placeholder="Địa chỉ cụ thể">
                                        </div>
                                    </div>
                                </div>
                            </div>
                    
                            <!--Payments-Method-->
                            <div class="pay-method">
                                <h3 style="margin-left: 100px;margin-bottom: 40px; font-size: 1.3rem;">Phương thức thanh toán điện tử *</h3>
                                <div class="pay-option">
                                    <label for="momo">
                                        <input type="radio" name="payment" value="momo">
                                        <img src="/Home/img/MoMo_Logo.png" alt="MoMo">
                                    </label>
                    
                                    <label for="vietcombank"> 
                                        <input type="radio" name="payment" value="vietcombank">
                                        <img src="/Home/img/Vietcombank.jpg" alt="Vietcombank">
                                    </label>
                                </div>
                            </div>
                    
                            <!--Note Section-->
                            <div class="pay-note">
                                <div class="note-box">
                                    <h3>Lời nhắn:</h3>
                                    <textarea id="note" placeholder="Lưu ý cho Shop..."></textarea>
                                    <div class="character-count">0/200</div>
                                </div>
                            </div>
                        </div> 
                    </div> 
                    


                    <!--Product-->
                    <div class="product">
                        <div class="product-list">
                            <div class="product-items">
                                <img src="/Home/img/2_691919e9e99c438eb3eaf37501e9b3ac_large.webp" alt="Shiina Mahiru">
                                <div class="product-detail">
                                    <span class="product-title">Mahiru Shiina - The Angel Next Door Spoils Me Rotten</span> |
                                    <span class="product-subtitle">F:Nex 1/7 | FuRyu Figure</span> |
                                </div>
                                <div class="product-info">
                                    <span class="product-quantity">Số lượng: 1</span>
                                    <span class="product-price">1,000,000 đ</span>
                                </div>
                            </div>

                            <div class="product-items">
                                <img src="/Home/img/2_691919e9e99c438eb3eaf37501e9b3ac_large.webp" alt="Shiina Mahiru">
                                <div class="product-detail">
                                    <span class="product-title">Mahiru Shiina - The Angel Next Door Spoils Me Rotten</span> |
                                    <span class="product-subtitle">F:Nex 1/7 | FuRyu Figure</span> |

                                </div>
                                <div class="product-info">
                                    <span class="product-quantity">Số lượng: 1</span>
                                    <span class="product-price">1,000,000 đ</span>
                                </div>
                            </div>

                            <div class="product-items">
                                <img src="/Home/img/2_691919e9e99c438eb3eaf37501e9b3ac_large.webp" alt="Shiina Mahiru">
                                <div class="product-detail">
                                    <span class="product-title">Mahiru Shiina - The Angel Next Door Spoils Me Rotten</span> |
                                    <span class="product-subtitle">F:Nex 1/7 | FuRyu Figure</span> |
                                </div>
                                <div class="product-info">
                                    <span class="product-quantity">Số lượng: 1</span>
                                    <span class="product-price">1,000,000 đ</span>
                                </div>
                            </div>

                            <div class="product-items">
                                <img src="/Home/img/2_691919e9e99c438eb3eaf37501e9b3ac_large.webp" alt="Shiina Mahiru">
                                <div class="product-detail">
                                    <span class="product-title">Mahiru Shiina - The Angel Next Door Spoils Me Rotten</span> |
                                    <span class="product-subtitle">F:Nex 1/7 | FuRyu Figure</span> |
                                </div>
                                <div class="product-info">
                                    <span class="product-quantity">Số lượng: 1</span>
                                    <span class="product-price">1,000,000 đ</span>
                                </div>
                                
                            </div>
                            
                        </div>
                    </div>

                    <!-- Voucher Section -->
                    <div class="voucher">
                        <select>
                            <option class="voucher-select">Chọn Voucher</option>
                            <option value="10%">Giảm 10%</option>
                            <option value="20%">Giảm 20%</option>
                            <option value="90%">Giảm 90%</option>
                        </select>
                        <button class="apply-button">Áp dụng</button>
                    </div>

                    <!-- Summary Section -->
                    <div class="summary">
                        <p>Tổng phí sản phẩm: <span class="product-total">4,000,000 đ</span></p>
                        <p>Phí vận chuyển: <span class="shipping-fee">50,000 đ</span></p>
                        <p>Chi phí giảm: <span class="discount-amount">50,000 đ</span></p>
                        <h3>
                            <span>Tổng:</span> 
                            <span class="grand-total">4,000,000 đ</span>
                        </h3>
                    </div>
                    <!-- Payment Button -->
                    <button class="payments-button">Đặt hàng</button>



                </div>
            </div>
    </main>
                    
        
    <!-- Javascript -->
    <script src="../components/js/global.js" defer></script>
    <script src="js/home.js"defer></script>
</body>
</html> 
