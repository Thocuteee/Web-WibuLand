// File: Home/index.php (Thay thế toàn bộ nội dung trong file này)

<?php
    // components/connect.php sẽ khởi tạo $conn và session
    include '../components/connect.php'; 
    
    // --- LOGIC TÌM SẢN PHẨM CÓ % SALE CAO NHẤT (FLASH SALE) ---
    global $conn;
    $best_sale_product = null;
    $max_sale = 0;
    
    // Đảm bảo $conn đã được thiết lập (từ connect.php)
    if (isset($conn)) {
        $tables = ['mohinh', 'magma', 'cosplay'];
        
        foreach ($tables as $table) {
            // Tìm sản phẩm có Sale > 0 và còn hàng (SoLuongTonKho - SoLuongDaBan) > 0
            $query = "SELECT *, '$table' AS category FROM `$table` WHERE Sale > 0 AND (SoLuongTonKho - SoLuongDaBan) > 0 ORDER BY Sale DESC, Gia ASC LIMIT 1";
            $result = mysqli_query($conn, $query);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $product = mysqli_fetch_assoc($result);
                
                if ($product['Sale'] > $max_sale) {
                    $max_sale = $product['Sale'];
                    $best_sale_product = $product;
                }
            }
        }
    }
    // ----------------------------------------------------
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

        if ($user_id && isset($conn)) {
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
        
        // === ĐỊNH NGHĨA HÀM HIỂN THỊ SẢN PHẨM (ĐÃ THÊM NÚT ADD TO CART) ===
        function getProducts($name_category, $conn, $sanpham,$link){
            // Khai báo biến global để truy cập mảng wishlist
            global $user_wishlist; 
            
            $product = "SELECT * FROM `$sanpham` ORDER BY ID DESC LIMIT 5";
            $result = $conn->query($product);
            
            if ($result && $result->num_rows > 0) {
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
                    
                    // Tính toán giá
                    $Giacu = null;
                    $final_price = $row['Gia'];
                    if ($row['Sale'] > 0) {
                        $final_price = $row['Gia'] * (1 - $row['Sale'] / 100); 
                        $Giacu = number_format($row['Gia']);
                    }

                    echo '<div class="product">';
                    
                    echo '<a href="' . $detail_url . '">';
                    echo '<img src="../admin/' . $row['Img1'] . '">';
                    echo '</a>';

                    echo '<a href="' . $detail_url . '">';
                    echo '<div class="name">' . $row['Name'] . '</div>';
                    echo '</a>';
                    
                    if (($row['SoLuongTonKho'] - $row['SoLuongDaBan']) <= 0) {
                        echo '<div class="sold-out" style ="background: orange">Hết hàng</div>';
                        
                    } else {
                        if ($row['Sale'] > 0) {
                            echo '<div class="discount">-' . $row['Sale'] . '%</div>';
                        }
                    
                        echo '<div class="price">' . number_format($final_price) . '₫</div>';
                        if (isset($Giacu)) {
                            echo '<div class="old-price">' . $Giacu . '₫</div>';
                        }
                        
                        // Nút Thêm vào giỏ hàng nhanh (Add to Cart Button)
                        echo '<div class="add-to-cart-quick" style="position: absolute; bottom: 3rem; right: 0.5rem; z-index: 10;">';
                        // Gọi hàm JS quickAddToCart với ID và Category
                        echo '<a href="#" onclick="quickAddToCart(event, ' . $row['ID'] . ', \'' . $sanpham . '\')" title="Thêm vào Giỏ hàng">';
                        echo '<i class="fa-solid fa-cart-plus" style="font-size: 2.3rem; color: black; background-color: var(--yellow-color); padding: 0.5rem; border-radius: 50%;"></i>'; 
                        echo '</a>';
                        echo '</div>';
                        
                        // Nút Yêu thích (có thể click)
                        echo '<div class="heart-icon">';
                        echo '<a href="#" onclick="toggleWishlist(event, ' . $row['ID'] . ', \'' . $sanpham . '\', ' . ($is_in_wishlist ? 'true' : 'false') . ')">';
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
                            <i class="fa-solid fa-chevron-left" id="btn-slide-left"></i>
                            <i class="fa-solid fa-chevron-right" id="btn-slide-right"></i>
                        </div>
                        <div class="indicator-dots">
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
                            <?php if ($best_sale_product): 
                                $fs_name = htmlspecialchars($best_sale_product['Name']);
                                $fs_img = "/admin/" . $best_sale_product['Img1'];
                                $fs_price_sale = $best_sale_product['Gia'];
                                $fs_sale_percent = $best_sale_product['Sale'];
                                $fs_old_price_calc = $fs_price_sale / (1 - $fs_sale_percent / 100);
                                $fs_old_price = number_format($fs_old_price_calc);
                                $fs_final_price = number_format($fs_price_sale);
                                $fs_stock = $best_sale_product['SoLuongTonKho'];
                                $fs_sold = $best_sale_product['SoLuongDaBan'];
                                $fs_remaining = $fs_stock - $fs_sold;
                                $fs_percent_sold = ($fs_sold / $fs_stock) * 100;
                                
                                $fs_id = $best_sale_product['ID'];
                                $fs_category = $best_sale_product['category'];
                                $fs_detail_url = "/Pagesproducts/product_detail.php?id=" . $fs_id . "&category=" . $fs_category;
                            ?>
                            <div class="product-card"> 
                                <div class="product-img">
                                    <a href="<?php echo $fs_detail_url; ?>">
                                        <img src="<?php echo $fs_img; ?>" alt="<?php echo $fs_name; ?>">
                                    </a>
                                </div>
                                <div class="product-content">
                                    <div class="product-title">
                                        <h3>
                                            <a href="<?php echo $fs_detail_url; ?>"><?php echo $fs_name; ?></a>
                                        </h3>
                                    </div>
                                    <div class="price">
                                        <span class="price-sale"><?php echo $fs_final_price; ?>₫</span>
                                        <span class="price-not-sale"><?php echo $fs_old_price; ?>₫</span>
                                        
                                        <a href="#" onclick="quickAddToCart(event, <?php echo $fs_id; ?>, '<?php echo $fs_category; ?>')" title="Thêm nhanh vào giỏ hàng">
                                            <i class="fa-solid fa-cart-plus" style="color: black; font-size: 1.8rem;"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="product-sold">Đã bán: <?php echo $fs_sold; ?> sản phẩm (Còn lại: <?php echo $fs_remaining; ?>)</div>
                                <div class="progress-bar">
                                    <div class="percent" style="width: <?php echo min(100, $fs_percent_sold); ?>%;"></div>
                                </div>
                            </div>
                            
                            <?php for ($i = 0; $i < 3; $i++): ?>
                                <div class="product-card" onclick='window.location.href="/Pagesproducts/Mohinh/mohinh.php"'>
                                    <div class="product-img">
                                        <img src="/admin/_imgProduct/mohinh/Screenshot 2024-11-17 210810.png" alt="Mô hình nổi bật">
                                    </div>
                                    <div class="product-content">
                                        <div class="product-title">
                                            <h3><a href="#">Mô hình Giảm giá (Placeholder)</a></h3>
                                        </div>
                                        <div class="price">
                                            <span class="price-sale">1,000,000₫</span>
                                            <span class="price-not-sale">1,200,000₫</span>
                                            <span><i class="fa-solid fa-cart-plus" style="color: gray; font-size: 1.8rem;"></i></span>
                                        </div>
                                    </div>
                                    <div class="product-sold">Đã bán: 45 sản phẩm</div>
                                    <div class="progress-bar"><div class="percent" style="width: 45%;"></div></div>
                                </div>
                            <?php endfor; ?>

                            <?php else: ?>
                                <p style="text-align: center; width: 100%; font-size: 1.6rem;">Không có sản phẩm Flash Sale nào đang hoạt động.</p>
                            <?php endif; ?>

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