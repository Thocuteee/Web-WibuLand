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

    <!-- Christmas Theme Styles -->
    <style>
        /* Christmas Background */
        body {
            background: linear-gradient(135deg, #5a5a5a 0%, #7a7a7a 50%, #5a5a5a 100%) !important;
            position: relative;
            overflow-x: hidden;
        }

        /* Snowflakes Animation */
        .snowflake {
            position: fixed;
            top: -10px;
            z-index: 9999;
            color: white;
            font-size: 1.8em;
            animation: fall linear infinite;
            opacity: 0.9;
            user-select: none;
            pointer-events: none;
            text-shadow: 0 0 5px rgba(255,255,255,0.8);
        }

        @keyframes fall {
            to {
                transform: translateY(100vh) rotate(360deg);
            }
        }

        /* Christmas Banner */
        .christmas-banner {
            background: linear-gradient(135deg, #d42426 0%, #b71c1c 100%);
            color: white;
            padding: 1.5rem 2rem;
            text-align: center;
            margin: 1rem auto;
            border-radius: 1.5rem;
            box-shadow: 0 0.5rem 2rem rgba(212,36,38,0.4);
            position: relative;
            overflow: hidden;
            max-width: 95%;
        }

        .christmas-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 10px;
            background: repeating-linear-gradient(
                90deg,
                #ffd700 0px,
                #ffd700 15px,
                #165b33 15px,
                #165b33 30px,
                #d42426 30px,
                #d42426 45px
            );
            animation: lightMove 2s linear infinite;
        }

        @keyframes lightMove {
            from { transform: translateX(0); }
            to { transform: translateX(45px); }
        }

        .christmas-banner h2 {
            font-size: 2.5rem;
            margin: 1rem 0 0.5rem;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
            animation: glow 2s ease-in-out infinite;
        }

        @keyframes glow {
            0%, 100% { 
                text-shadow: 0 0 10px #ffd700, 0 0 20px #ffd700, 3px 3px 6px rgba(0,0,0,0.3); 
            }
            50% { 
                text-shadow: 0 0 20px #ffd700, 0 0 40px #ffd700, 3px 3px 6px rgba(0,0,0,0.3); 
            }
        }

        .christmas-banner p {
            font-size: 1.6rem;
            margin-bottom: 0.5rem;
        }

        /* Christmas Decorations on Products */
        .product::before {
            content: '❄️';
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 2rem;
            opacity: 0.7;
            animation: sparkle 3s ease-in-out infinite;
        }

        @keyframes sparkle {
            0%, 100% { opacity: 0.3; transform: rotate(0deg); }
            50% { opacity: 1; transform: rotate(180deg); }
        }

        /* Christmas Toggle Button */
        .christmas-toggle {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #d42426 0%, #b71c1c 100%);
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            cursor: pointer;
            box-shadow: 0 5px 20px rgba(212,36,38,0.5);
            z-index: 1000;
            animation: float 3s ease-in-out infinite;
            border: 3px solid #ffd700;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .christmas-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 30px rgba(212,36,38,0.7);
        }

        /* Make sections stand out on gray background */
        .section {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.5rem 2rem rgba(0,0,0,0.15);
        }

        /* Flash Sale Christmas Style */
        .flash-sale {
            background: linear-gradient(135deg, #165b33 0%, #0d3d1f 100%) !important;
            border: 3px solid #ffd700;
        }

        .flash-sale h2 {
            color: white !important;
        }
    </style>
        
    <title>🎄 Wibu Dreamland - Giáng Sinh Vui Vẻ! 🎅</title>
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
                        // Chỉ hiển thị nếu đã đăng nhập
                        if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
                        echo '<div class="add-to-cart-quick" style="position: absolute; bottom: 3rem; right: 0.5rem; z-index: 10;">';
                        // Gọi hàm JS quickAddToCart với ID và Category
                        echo '<a href="#" onclick="quickAddToCart(event, ' . $row['ID'] . ', \'' . $sanpham . '\')" title="Thêm vào Giỏ hàng">';
                        echo '<i class="fa-solid fa-cart-plus" style="font-size: 2.3rem; color: black; background-color: var(--yellow-color); padding: 0.5rem; border-radius: 50%;"></i>'; 
                        echo '</a>';
                        echo '</div>';
                        } else {
                            // Nếu chưa đăng nhập, hiển thị nút yêu cầu đăng nhập
                            echo '<div class="add-to-cart-quick" style="position: absolute; bottom: 3rem; right: 0.5rem; z-index: 10;">';
                            echo '<a href="../login&registration/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']) . '" title="Đăng nhập để thêm vào giỏ hàng">';
                            echo '<i class="fa-solid fa-lock" style="font-size: 2.3rem; color: white; background-color: #666; padding: 0.5rem; border-radius: 50%;"></i>'; 
                            echo '</a>';
                            echo '</div>';
                        }
                        
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
            <!-- Christmas Banner -->
            <div class="christmas-banner">
                <h2>🎄 GIÁNG SINH VUI VẺ - ƯU ĐÃI ĐẶC BIỆT! 🎅</h2>
                <p>✨ Giảm giá lên đến 50% cho tất cả sản phẩm Anime/Manga/Cosplay ✨</p>
                <p style="font-size: 1.4rem;">🎁 Miễn phí vận chuyển cho đơn hàng từ 300.000₫ 🎁</p>
            </div>

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
                                    </div>
                                    <div class="add-to-cart-quick">
                                        <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                                        <a href="#" onclick="quickAddToCart(event, <?php echo $fs_id; ?>, '<?php echo $fs_category; ?>')" title="Thêm nhanh vào giỏ hàng">
                                        <?php else: ?>
                                        <a href="../login&registration/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" title="Đăng nhập để thêm vào giỏ hàng">
                                        <?php endif; ?>
                                            <i class="fa-solid fa-cart-plus"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="product-sold">Đã bán: <?php echo $fs_sold; ?> sản phẩm (Còn lại: <?php echo $fs_remaining; ?>)</div>
                                <div class="progress-bar">
                                    <div class="percent" style="width: <?php echo min(100, $fs_percent_sold); ?>%;"></div>
                                </div>
                            </div>
                            
                            <?php
                            // Lấy 3 sản phẩm sale khác từ database
                            $flash_sale_products = [];
                            $tables = ['mohinh', 'magma', 'cosplay'];
                            
                            foreach ($tables as $table) {
                                $query = "SELECT *, '$table' AS category FROM `$table` 
                                         WHERE Sale > 0 AND SoLuongTonKho > 0";
                                
                                // Loại trừ sản phẩm đã hiển thị ở card đầu tiên
                                if ($best_sale_product) {
                                    $best_id = $best_sale_product['ID'];
                                    $best_cat = $best_sale_product['category'];
                                    if ($table == $best_cat) {
                                        $query .= " AND ID != $best_id";
                                    }
                                }
                                
                                $query .= " ORDER BY Sale DESC, SoLuongDaBan DESC LIMIT 3";
                                $result = mysqli_query($conn, $query);
                                
                                if ($result) {
                                    while ($product = mysqli_fetch_assoc($result)) {
                                        $flash_sale_products[] = $product;
                                        if (count($flash_sale_products) >= 3) break;
                                    }
                                }
                                if (count($flash_sale_products) >= 3) break;
                            }
                            
                            // Hiển thị 3 sản phẩm
                            foreach (array_slice($flash_sale_products, 0, 3) as $product):
                                $name = htmlspecialchars($product['Name']);
                                $img = "/admin/" . $product['Img1'];
                                $price = $product['Gia'];
                                $sale = $product['Sale'];
                                $old_price = $price / (1 - $sale / 100);
                                $stock = $product['SoLuongTonKho'];
                                $sold = $product['SoLuongDaBan'];
                                $remaining = $stock - $sold;
                                $percent_sold = ($sold / $stock) * 100;
                                $id = $product['ID'];
                                $category = $product['category'];
                                $detail_url = "/Pagesproducts/product_detail.php?id=" . $id . "&category=" . $category;
                            ?>
                                <div class="product-card">
                                    <div class="product-img">
                                        <a href="<?php echo $detail_url; ?>">
                                            <img src="<?php echo $img; ?>" alt="<?php echo $name; ?>">
                                        </a>
                                    </div>
                                    <div class="product-content">
                                        <div class="product-title">
                                            <h3>
                                                <a href="<?php echo $detail_url; ?>"><?php echo $name; ?></a>
                                            </h3>
                                        </div>
                                        <div class="price">
                                            <span class="price-sale"><?php echo number_format($price); ?>₫</span>
                                            <span class="price-not-sale"><?php echo number_format($old_price); ?>₫</span>
                                        </div>
                                        <div class="add-to-cart-quick">
                                            <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                                            <a href="#" onclick="quickAddToCart(event, <?php echo $id; ?>, '<?php echo $category; ?>')" title="Thêm nhanh vào giỏ hàng">
                                            <?php else: ?>
                                            <a href="../login&registration/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" title="Đăng nhập để thêm vào giỏ hàng">
                                            <?php endif; ?>
                                                <i class="fa-solid fa-cart-plus"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="product-sold">Đã bán: <?php echo $sold; ?> sản phẩm<?php if ($remaining > 0): ?> (Còn lại: <?php echo $remaining; ?>)<?php endif; ?></div>
                                    <div class="progress-bar">
                                        <div class="percent" style="width: <?php echo min(100, $percent_sold); ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php else: ?>
                                <p style="text-align: center; width: 100%; font-size: 1.6rem;">Không có sản phẩm Flash Sale nào đang hoạt động.</p>
                            <?php endif; ?>

                        </div>
                        <div class="foot-flash-sale">
                            <a href="/Pagesproducts/sale_products.php" class="btn-view-all">Xem tất cả >> </a>
                        </div>

                    </div>
                </div>
                


                <?php
                // Lấy Top 10 sản phẩm bán chạy
                $best_sellers = [];
                $categories_for_top = ['mohinh' => 'Mô hình', 'magma' => 'Manga', 'cosplay' => 'Cosplay'];
                
                foreach ($categories_for_top as $table => $cat_name) {
                    $best_query = "SELECT *, '$table' as category, '$cat_name' as category_name 
                                   FROM `$table` 
                                   WHERE SoLuongDaBan > 0
                                   ORDER BY SoLuongDaBan DESC 
                                   LIMIT 5";
                    
                    $best_result = mysqli_query($conn, $best_query);
                    if ($best_result) {
                        while ($product = mysqli_fetch_assoc($best_result)) {
                            $product['revenue'] = $product['SoLuongDaBan'] * $product['Gia'];
                            $best_sellers[] = $product;
                        }
                    }
                }
                
                usort($best_sellers, function($a, $b) {
                    return $b['SoLuongDaBan'] - $a['SoLuongDaBan'];
                });
                $best_sellers = array_slice($best_sellers, 0, 10);
                ?>

                <!-- Top 10 Bán Chạy -->
                <?php if (count($best_sellers) > 0): ?>
                <div class="section" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); padding: 3rem; border-radius: 1.5rem; margin-bottom: 3rem; box-shadow: 0 0.5rem 2rem rgba(0,0,0,0.15);">
                    <h2 style="text-align: center; color: #d42426; font-size: 2.5rem; margin-bottom: 2rem; border-bottom: 3px solid #ffd700; padding-bottom: 1.5rem;">
                        <i class="fa-solid fa-trophy"></i> Top 10 Sản Phẩm Bán Chạy Nhất
                    </h2>
                    
                    <div class="product-list">
                        <?php foreach (array_slice($best_sellers, 0, 10) as $index => $row): 
                            $rank = $index + 1;
                            $detail_url = "/Pagesproducts/product_detail.php?id=" . $row['ID'] . "&category=" . $row['category'];
                            
                            $wishlist_key = $row['category'] . '_' . $row['ID'];
                            $is_in_wishlist = isset($user_wishlist[$wishlist_key]);
                            $heart_class = $is_in_wishlist ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
                            
                            $available_stock = (int)$row['SoLuongTonKho'];
                        ?>
                        
                        <div class="product" style="position: relative;">
                            <!-- Rank Badge -->
                            <div style="position: absolute; top: 10px; left: 10px; background: linear-gradient(135deg, #ffd700 0%, #ffb300 100%); color: #333; font-weight: bold; font-size: 1.6rem; padding: 0.5rem 1rem; border-radius: 1rem; z-index: 5; box-shadow: 0 3px 10px rgba(255,215,0,0.4);">
                                <?php if ($rank == 1): ?>
                                    🥇 #<?php echo $rank; ?>
                                <?php elseif ($rank == 2): ?>
                                    🥈 #<?php echo $rank; ?>
                                <?php elseif ($rank == 3): ?>
                                    🥉 #<?php echo $rank; ?>
                                <?php else: ?>
                                    #<?php echo $rank; ?>
                                <?php endif; ?>
                            </div>

                            <a href="<?php echo $detail_url; ?>">
                                <img src="/admin/<?php echo $row['Img1']; ?>" alt="<?php echo htmlspecialchars($row['Name']); ?>">
                            </a>
                            
                            <a href="<?php echo $detail_url; ?>">
                                <div class="name"><?php echo htmlspecialchars($row['Name']); ?></div>
                            </a>
                            
                            <?php if ($available_stock > 0): ?>
                                <?php if ($row['Sale'] > 0): ?>
                                    <div class="discount">-<?php echo $row['Sale']; ?>%</div>
                                <?php endif; ?>
                                
                                <div class="category-badge" style="position: absolute; top: 50px; left: 10px; background: #667eea; color: white; padding: 5px 10px; border-radius: 5px; font-size: 1.1rem;">
                                    <?php echo $row['category_name']; ?>
                                </div>
                                
                                <div class="price"><?php echo number_format($row['Gia']); ?>₫</div>
                                
                                <div class="sold-info" style="font-size: 1.3rem; color: #d42426; font-weight: bold; margin-top: 5px;">
                                    🔥 Đã bán: <?php echo number_format($row['SoLuongDaBan']); ?>
                                </div>
                                
                                <div class="heart-icon">
                                    <a href="#" onclick="toggleWishlist(event, <?php echo $row['ID']; ?>, '<?php echo $row['category']; ?>', <?php echo $is_in_wishlist ? 'true' : 'false'; ?>)">
                                        <i id="wishlist_<?php echo $wishlist_key; ?>" class="<?php echo $heart_class; ?>" style="color: #f70202;"></i>
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="sold-out" style="background: orange">Hết hàng</div>
                            <?php endif; ?>
                        </div>
                        
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

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
    
    <!-- Spacer để đẩy footer xuống -->
    <div style="height: 20rem;"></div>

    <?php include "../components/footer.php"?>
    
    <!-- Christmas Toggle Button -->
    <div class="christmas-toggle" onclick="alert('🎄 Theme Giáng Sinh đang bật! Chúc bạn mua sắm vui vẻ! 🎅')" title="Theme Giáng Sinh">
        🎄
    </div>

    <!-- Snowflakes Container -->
    <div id="snowflakes-container"></div>
    
    <script src="../components/js/global.js" defer></script>
    <script src="js/home.js"defer></script>
    <script src="../components/js/wishlist.js" defer></script>

    <!-- Christmas Snowfall Script -->
    <script>
        // Tạo hiệu ứng tuyết rơi
        function createSnowflakes() {
            const container = document.getElementById('snowflakes-container');
            if (!container) return;
            
            const snowflakeSymbols = ['❄', '❅', '❆', '✻', '✼'];
            const numberOfSnowflakes = window.innerWidth > 768 ? 50 : 30; // Ít hơn trên mobile
            
            for (let i = 0; i < numberOfSnowflakes; i++) {
                const snowflake = document.createElement('div');
                snowflake.className = 'snowflake';
                snowflake.textContent = snowflakeSymbols[Math.floor(Math.random() * snowflakeSymbols.length)];
                
                // Random position
                snowflake.style.left = Math.random() * 100 + '%';
                
                // Random animation duration (5-12s)
                snowflake.style.animationDuration = (Math.random() * 7 + 5) + 's';
                
                // Random animation delay (0-8s)
                snowflake.style.animationDelay = Math.random() * 8 + 's';
                
                // Random font size
                snowflake.style.fontSize = (Math.random() * 1.5 + 1) + 'em';
                
                // Random opacity
                snowflake.style.opacity = Math.random() * 0.5 + 0.5;
                
                container.appendChild(snowflake);
            }
        }

        // Chạy khi trang load xong
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', createSnowflakes);
        } else {
            createSnowflakes();
        }

        // Log Christmas message
        console.log('%c🎄 Merry Christmas from Wibu Dreamland! 🎅', 'color: #d42426; font-size: 20px; font-weight: bold;');
        console.log('%c✨ Giảm giá đặc biệt mùa Giáng sinh! ✨', 'color: #165b33; font-size: 16px;');
    </script> 
</body>
</html>