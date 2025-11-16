<?php
    // components/connect.php sẽ khởi tạo $conn và session
    include '../components/connect.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../components/css/global.css">
    <link rel="stylesheet" href="../components/css/header_sidebar_footer.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="../Pagesproducts/css/listproducts.css">

        
    <title>Wibu Dreamland</title>
</head>
<body>
    <?php include '../components/header.php'; ?>
    
    <?php
        // === KHỞI TẠO BIẾN VÀ LẤY DANH SÁCH YÊU THÍCH TỪ DB ===
        global $conn; // Đảm bảo $conn có sẵn
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $user_wishlist = [];

        if ($user_id) {
            $select_wishlist_query = "SELECT IdSanPham, LoaiSanPham FROM `yeuthich` WHERE IdUser = ?";
            if ($stmt_wishlist = mysqli_prepare($conn, $select_wishlist_query)) {
                mysqli_stmt_bind_param($stmt_wishlist, "i", $user_id);
                mysqli_stmt_execute($stmt_wishlist);
                $result_wishlist = mysqli_stmt_get_result($stmt_wishlist);
                
                while ($row = mysqli_fetch_assoc($result_wishlist)) {
                    $key = $row['LoaiSanPham'] . '_' . $row['IdSanPham'];
                    $user_wishlist[$key] = true;
                }
                mysqli_stmt_close($stmt_wishlist);
            }
        }
        
        // === ĐỊNH NGHĨA HÀM HIỂN THỊ SẢN PHẨM ===
        function getProducts($name_category, $conn, $sanpham,$link){
            // Khai báo biến global để truy cập mảng wishlist
            global $user_wishlist; 
            
            $product = "SELECT * FROM `$sanpham` ORDER BY ID DESC LIMIT 5";
            $result = $conn->query($product);
            
            if ($result->num_rows > 0) {
                echo '<div class="section">
                    <h2>
                        ' .$name_category. '
                        <a href="'.$link.'">Xem tất cả »</a>
                    </h2>
                    <div class="product-list">';
    
                while ($row = $result->fetch_assoc()) {
                    
                    // === LOGIC KIỂM TRA VÀ TẠO ICON YÊU THÍCH ĐỘNG ===
                    $wishlist_key = $sanpham . '_' . $row['ID'];
                    $is_in_wishlist = isset($user_wishlist[$wishlist_key]); 

                    $heart_class = $is_in_wishlist ? 'fa-solid fa-heart' : 'fa-regular fa-heart'; 
                    // ===========================================
                    
                    $detail_url = "/Pagesproducts/product_detail.php?id=" . $row['ID'] . "&category=" . $sanpham;
                    
                    echo '<div class="product">';
                    
                    echo '<a href="' . $detail_url . '">';
                    echo '<img src="../admin/' . $row['Img1'] . '">';
                    echo '</a>';

                    echo '<a href="' . $detail_url . '">';
                    echo '<div class="name">' . $row['Name'] . '</div>';
                    echo '</a>';
                    
                    if ($row['SoLuongTonKho'] <= $row['SoLuongDaBan']) {
                        echo '<div class="sold-out" style ="background: orange">Hết hàng</div>';
                        
                    } else {
                        $Giacu = null;
                        if ($row['Sale'] > 0) {
                            echo '<div class="discount">-' . $row['Sale'] . '%</div>';
                            
                            //Tính giá gốc
                            $Giacu = number_format(($row['Gia'] / (1 - $row['Sale'] / 100)));
                        }
                    
                        $final_price = ($row['Sale'] > 0) ? $row['Gia'] : $row['Gia']; 

                        echo '<div class="price">' . number_format($final_price) . '₫</div>';
                        if (isset($Giacu)) {
                            echo '<div class="old-price">' . $Giacu . '₫</div>';
                        }
                        
                        // Nút Yêu thích (có thể click)
                        echo '<div class="heart-icon">';
                        // Gán sự kiện onclick để gọi hàm JS
                        echo '<a href="#" onclick="toggleWishlist(event, ' . $row['ID'] . ', \'' . $sanpham . '\', ' . ($is_in_wishlist ? 'true' : 'false') . ')">';
                        // Đặt ID để JS có thể tìm và thay đổi trạng thái icon
                        echo '<i id="wishlist_' . $wishlist_key . '" class="' . $heart_class . '" style="color: #f70202;"></i>'; 
                        echo '</a>';
                        echo '</div>'; 
                    }
                    echo '</div>';
                }
                
                echo '</div> </div><br><br><br>';
            }
        }
    ?>

    <main>
        <?php include '../components/sidebar.php'; ?>
        
        <div class="home-content">
            <div class="content">

                <div class="slide-show">
                    <div class="list-pic">
                        <div class="images-wrapper">
                            <img src="img/2__1___1___1__93e4a132748647e6a3ff58b3fa64038a.webp">
                            <img src="img/order.jpg">
                        </div>
                        <div class="btnLR">
                            <i class="fa-solid fa-chevron-left"></i>
                            <i class="fa-solid fa-chevron-right"></i>
                        </div>
                    </div>
                </div>
                
                
                <div class="flash-sale-table-bg">
                    <div class="flash-sale-table">
                        <div class="head-flash-sale">
                            <div class="text">
                                <span>Flash Sale</span>
                                <img src="img/logo_sale.png" alt="">

                            </div>
                            
                            <div class="name-website-flash-sale">
                                <H2>Wibu Dreamland</H2>
                            </div>

                            <div class="count-time">
                                <div class="time-box">
                                    <strong class="time">00</strong>
                                    <small>Ngày</small>
                                </div>
                                <div class="time-box">
                                    <strong class="time">00</strong>
                                    <small>Giờ</small>
                                </div>
                                <div class="time-box">
                                    <strong class="time">00</strong>
                                    <small>Phút</small>
                                </div>
                                <div class="time-box">
                                    <strong class="time">00</strong>
                                    <small>Giây</small>
                                </div>
            
                            </div>
                        </div>
                        
                        <div class="body-flash-sale">
                                <div class="product-card" onclick='window.location.href="/products/products.html"'>

                                <div class="product-img">
                                    <img src="/Home/img/2_691919e9e99c438eb3eaf37501e9b3ac_large.webp" alt="">
                                </div>
                                <div class="product-content">
                                    <div class="product-title">
                                        <h3>
                                            <a href="/products/products.html">Nendoroid 2625 Kirigiri Kyouko - Danganronpa 1 / 2 Reload | Good Smile Company Figure </a>
                                        </h3>
                                    </div>
                                    <div class="price">
                                        <span class="price-sale">1,550,000 VND</span>
                                        <span class="price-not-sale">1,000,000 VND</span>
                                        <span><i class="fa-regular fa-heart" style="color: #f70202;"></i></span>
                                    </div>
                                </div>
                                <div class = "product-sold">Đã bán: 59 sản phẩm</div>
                                <div class="progress-bar">
                                    <div class="percent" style="width: 60%;"></div>
                                </div>
                            </div>
                                <div class="product-card">

                                <div class="product-img">
                                    <img src="/Home/img/2_691919e9e99c438eb3eaf37501e9b3ac_large.webp" alt="">
                                </div>
                                <div class="product-content">
                                    <div class="product-title">
                                        <h3>
                                            <a href="#">Nendoroid 2625 Kirigiri Kyouko - Danganronpa 1 / 2 Reload | Good Smile Company Figure </a>
                                        </h3>
                                    </div>
                                    <div class="price">
                                        <span class="price-sale">1,550,000 VND</span>
                                        <span class="price-not-sale">1,000,000 VND</span>
                                        <span><i class="fa-regular fa-heart" style="color: #f70202;"></i></span>
                                    </div>
                                </div>
                                <div class = "product-sold">Đã bán: 59 sản phẩm</div>
                                <div class="progress-bar">
                                    <div class="percent" style="width: 60%;"></div>
                                </div>
                            </div>
                                    <div class="product-card">

                                <div class="product-img">
                                    <img src="/Home/img/2_691919e9e99c438eb3eaf37501e9b3ac_large.webp" alt="">
                                </div>
                                <div class="product-content">
                                    <div class="product-title">
                                        <h3>
                                            <a href="#">Nendoroid 2625 Kirigiri Kyouko - Danganronpa 1 / 2 Reload | Good Smile Company Figure </a>
                                        </h3>
                                    </div>
                                    <div class="price">
                                        <span class="price-sale">1,550,000 VND</span>
                                        <span class="price-not-sale">1,000,000 VND</span>
                                        <span><i class="fa-regular fa-heart" style="color: #f70202;"></i></span>
                                    </div>
                                </div>
                                <div class = "product-sold">Đã bán: 59 sản phẩm</div>
                                <div class="progress-bar">
                                    <div class="percent" style="width: 60%;"></div>
                                </div>
                            </div>
                                    <div class="product-card">

                                <div class="product-img">
                                    <img src="/Home/img/2_691919e9e99c438eb3eaf37501e9b3ac_large.webp" alt="">
                                </div>
                                <div class="product-content">
                                    <div class="product-title">
                                        <h3>
                                            <a href="#">Nendoroid 2625 Kirigiri Kyouko - Danganronpa 1 / 2 Reload | Good Smile Company Figure </a>
                                        </h3>
                                    </div>
                                    <div class="price">
                                        <span class="price-sale">1,550,000 VND</span>
                                        <span class="price-not-sale">1,000,000 VND</span>
                                        <span><i class="fa-regular fa-heart" style="color: #f70202;"></i></span>
                                    </div>
                                </div>
                                <div class = "product-sold">Đã bán: 59 sản phẩm</div>
                                <div class="progress-bar">
                                    <div class="percent" style="width: 60%;"></div>
                                </div>
                            </div>
                            

                        </div>
                        <div class="foot-flash-sale">
                            <a href="#" class ="btn-view-all">Xem tất cả >> </a>
                        </div>

                    </div>
                </div>
                


                <div class="container">
                    <?php
                        // === GỌI HÀM HIỂN THỊ SẢN PHẨM ===
                        getProducts ('Mô hình', $conn, 'mohinh','/Pagesproducts/Mohinh/mohinh.php');
                        getProducts ('Truyện tranh',$conn,'magma','/Pagesproducts/Manga/manga.php');
                        getProducts ('Cosplay',$conn, 'cosplay','/Pagesproducts/Cosplay/cosplay.php');
                    ?> 
                </div> 




            </div>
        </div>
    </main>
                            

    <?php include "../components/footer.php"?>
    
    <script src="../components/js/global.js" defer></script>
    <script src="js/home.js"defer></script>
    <script src="../components/js/wishlist.js" defer></script> 
</body>
</html>