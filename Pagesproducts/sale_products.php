<?php
require_once '../components/connect.php';

// Lấy danh sách yêu thích của user
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$user_wishlist = [];

if ($user_id) {
    $select_wishlist_query = "SELECT IdSanPham, LoaiSanPham FROM `yeuthich` WHERE IdUser = ?";
    $stmt_wishlist = mysqli_prepare($conn, $select_wishlist_query);
    if ($stmt_wishlist) {
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

// Lấy tất cả sản phẩm đang sale
$sale_products = [];
$categories = ['mohinh' => 'Mô hình', 'magma' => 'Manga', 'cosplay' => 'Cosplay'];

foreach ($categories as $table => $cat_name) {
    $query = "SELECT *, '$table' AS category, '$cat_name' AS category_name 
              FROM `$table` 
              WHERE Sale > 0 AND SoLuongTonKho > 0
              ORDER BY Sale DESC, SoLuongDaBan DESC";
    
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($product = mysqli_fetch_assoc($result)) {
            $sale_products[] = $product;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../components/css/global.css">
    <link rel="stylesheet" href="../components/css/header_sidebar_footer.css">
    <link rel="stylesheet" href="css/listproducts.css">
    <link rel="stylesheet" href="../Home/css/home.css">
    <style>
        /* Christmas Theme for Sale Page */
        body {
            background: linear-gradient(135deg, #5a5a5a 0%, #7a7a7a 50%, #5a5a5a 100%);
        }

        .sale-banner {
            background: linear-gradient(135deg, #d42426 0%, #b71c1c 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            margin: 2rem auto;
            border-radius: 1.5rem;
            box-shadow: 0 0.5rem 2rem rgba(212,36,38,0.4);
            max-width: 95%;
        }

        .sale-banner h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
            animation: glow 2s ease-in-out infinite;
        }

        @keyframes glow {
            0%, 100% { text-shadow: 0 0 20px #ffd700, 0 0 30px #ffd700; }
            50% { text-shadow: 0 0 30px #ffd700, 0 0 50px #ffd700; }
        }

        .sale-banner p {
            font-size: 1.6rem;
            margin-top: 1rem;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 1.5rem;
            margin: 2rem auto;
            max-width: 95%;
            box-shadow: 0 0.5rem 2rem rgba(0,0,0,0.15);
        }

        .section h2 {
            color: #d42426;
            border-bottom: 3px solid #ffd700;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }

        .product {
            position: relative;
        }

        .product .discount {
            background: linear-gradient(135deg, #d42426 0%, #b71c1c 100%);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .no-products {
            text-align: center;
            padding: 5rem 2rem;
            font-size: 1.8rem;
            color: #666;
        }

        .filter-section {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 1rem 2rem;
            border: 2px solid #d42426;
            background: white;
            color: #d42426;
            border-radius: 2rem;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: #d42426;
            color: white;
        }
    </style>
    <title>🎁 Flash Sale - Tất Cả Sản Phẩm Giảm Giá | Wibu Dreamland</title>
</head>
<body>
    <?php include '../components/header.php'; ?>
    
    <main>
        <?php include '../components/sidebar.php'; ?>
        
        <div class="home-content">
            <!-- Sale Banner -->
            <div class="sale-banner">
                <h1>🔥 FLASH SALE - GIẢM GIÁ ĐẶC BIỆT 🔥</h1>
                <p>✨ Tất cả sản phẩm đang được giảm giá lên đến 50% ✨</p>
                <p style="font-size: 1.4rem; margin-top: 0.5rem;">
                    🎁 Tìm ngay sản phẩm yêu thích với giá tốt nhất!
                </p>
            </div>

            <div class="content">
                <div class="container">
                    <?php if (count($sale_products) > 0): ?>
                        
                        <div class="section">
                            <h2>
                                <i class="fa-solid fa-fire"></i> 
                                Tất cả sản phẩm đang giảm giá (<?php echo count($sale_products); ?> sản phẩm)
                            </h2>
                            
                            <div class="product-list">
                                <?php foreach ($sale_products as $row): 
                                    $detail_url = "/Pagesproducts/product_detail.php?id=" . $row['ID'] . "&category=" . $row['category'];
                                    
                                    $wishlist_key = $row['category'] . '_' . $row['ID'];
                                    $is_in_wishlist = isset($user_wishlist[$wishlist_key]);
                                    $heart_class = $is_in_wishlist ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
                                    
                                    $Giacu = null;
                                    $final_price = $row['Gia'];
                                    if ($row['Sale'] > 0) {
                                        $Giacu = number_format($row['Gia'] / (1 - $row['Sale'] / 100));
                                    }
                                    
                                    $available_stock = (int)$row['SoLuongTonKho'];
                                ?>
                                
                                <div class="product">
                                    <a href="<?php echo $detail_url; ?>">
                                        <img src="/admin/<?php echo $row['Img1']; ?>" alt="<?php echo htmlspecialchars($row['Name']); ?>">
                                    </a>
                                    
                                    <a href="<?php echo $detail_url; ?>">
                                        <div class="name"><?php echo htmlspecialchars($row['Name']); ?></div>
                                    </a>
                                    
                                    <?php if ($available_stock > 0): ?>
                                        <div class="discount">-<?php echo $row['Sale']; ?>%</div>
                                        <div class="category-badge" style="position: absolute; top: 50px; left: 10px; background: #165b33; color: white; padding: 5px 10px; border-radius: 5px; font-size: 1.1rem;">
                                            <?php echo $row['category_name']; ?>
                                        </div>
                                        
                                        <div class="price"><?php echo number_format($final_price); ?>₫</div>
                                        <?php if (isset($Giacu)): ?>
                                            <div class="old-price"><?php echo $Giacu; ?>₫</div>
                                        <?php endif; ?>
                                        
                                        <div class="sold-info" style="font-size: 1.2rem; color: #666; margin-top: 5px;">
                                            Đã bán: <?php echo $row['SoLuongDaBan']; ?>
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
                        
                    <?php else: ?>
                        <div class="no-products">
                            <i class="fa-solid fa-box-open" style="font-size: 5rem; color: #ddd; margin-bottom: 2rem;"></i>
                            <p>Hiện tại không có sản phẩm nào đang giảm giá.</p>
                            <p style="font-size: 1.4rem; margin-top: 1rem;">
                                <a href="/Home/index.php" style="color: #d42426;">← Quay về trang chủ</a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>
    
    <script src="../components/js/global.js"></script>
    <script src="../components/js/wishlist.js"></script>
</body>
</html>

