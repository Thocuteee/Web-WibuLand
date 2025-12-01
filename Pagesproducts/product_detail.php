<?php
include '../components/connect.php'; // Kết nối DB

// 1. Lấy ID và Category từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$product = null;
$message = '';

// Kiểm tra tính hợp lệ của input
if ($product_id > 0 && !empty($category)) {
    // 2. CHỐNG SQL INJECTION BẰNG PREPARED STATEMENTS
    $select_query = "SELECT * FROM `$category` WHERE ID = ?";
    
    // Chuẩn bị truy vấn
    $stmt = mysqli_prepare($conn, $select_query);
    
    // Gắn tham số (Kiểu "i" cho integer)
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    
    // Thực thi truy vấn
    mysqli_stmt_execute($stmt);
    
    // Lấy kết quả
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
    } else {
        $message = "Sản phẩm không tồn tại.";
    }
    mysqli_stmt_close($stmt);
} else {
    $message = "Đường dẫn sản phẩm không hợp lệ.";
}

// Nếu không tìm thấy sản phẩm, dừng lại và thông báo
if (!$product) {
    echo "<h1>Lỗi: $message</h1>";
    exit();
}

// Tính giá sau khi giảm giá (nếu có)
$price = $product['Gia'];
if ($product['Sale'] > 0) {
    $price_sale = $price * (1 - $product['Sale'] / 100);
    $price_display = number_format($price_sale);
    $old_price_display = number_format($price);
} else {
    $price_display = number_format($price);
    $old_price_display = null;
}

$related_products = [];
if ($product && isset($product['TheLoai'])) {
    $category_id = $product['TheLoai'];
    $current_id = $product['ID'];
    
    // Lấy 4 sản phẩm cùng loại, khác ID hiện tại
    $select_related = "SELECT * FROM `$category` WHERE TheLoai = ? AND ID != ? LIMIT 4";
    $stmt_related = mysqli_prepare($conn, $select_related);
    
    if ($stmt_related) {
        mysqli_stmt_bind_param($stmt_related, "ii", $category_id, $current_id);
        mysqli_stmt_execute($stmt_related);
        $result_related = mysqli_stmt_get_result($stmt_related);
        
        while ($row = mysqli_fetch_assoc($result_related)) {
            // Tính giá cuối cùng cho sản phẩm liên quan
            $price_related = $row['Gia'];
            $sale_related = $row['Sale'];
            if ($sale_related > 0) {
                $final_price_related = $price_related * (1 - $sale_related / 100);
            } else {
                $final_price_related = $price_related;
            }
            $row['final_price'] = $final_price_related;
            $related_products[] = $row;
        }
        mysqli_stmt_close($stmt_related);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/components/css/global.css">
    <link rel="stylesheet" href="/components/css/header_sidebar_footer.css">
    <link rel="stylesheet" href="css/product_detail.css"> 
    <title><?php echo $product['Name']; ?></title>
</head>
<body>
    <?php include '../components/header.php'; ?>
    
    <main>
        <?php include '../components/sidebar.php'; ?>
        
        <div class="home-content">
            <div class="content">
                
                

                <div class="product-container">
                    
                    <div class="product-images">
                        <img src="../admin/<?php echo $product['Img1']; ?>" alt="<?php echo $product['Name']; ?>" class="main-image" id="main-product-image">
                        
                        <div class="thumbnail-gallery">
                            <img src="../admin/<?php echo $product['Img1']; ?>" alt="Thumbnail 1" class="thumbnail-item active" onclick="changeMainImage(this)">
                            
                            <?php if (!empty($product['Img2'])): ?>
                            <img src="../admin/<?php echo $product['Img2']; ?>" alt="Thumbnail 2" class="thumbnail-item" onclick="changeMainImage(this)">
                            <?php endif; ?>
                            
                            </div>
                    </div>

                    <div class="product-info-area">
                        <h1><?php echo $product['Name']; ?></h1>
                        
                        <div class="price-section">
                            <?php if ($old_price_display): ?>
                                <span class="old-price"><?php echo $old_price_display; ?>₫</span>
                            <?php endif; ?>
                            <span class="current-price"><?php echo $price_display; ?>₫</span>
                            <?php if ($product['Sale'] > 0): ?>
                                <span class="discount-badge">-<?php echo $product['Sale']; ?>%</span>
                            <?php endif; ?>
                        </div>

                        <div class="stock-info">
                            <p>Tình trạng: 
                                <?php if ($product['SoLuongTonKho'] > $product['SoLuongDaBan']): ?>
                                    <span class="in-stock">Còn hàng (<?php echo $product['SoLuongTonKho'] - $product['SoLuongDaBan']; ?> sản phẩm)</span>
                                <?php else: ?>
                                    <span class="out-of-stock">Hết hàng</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <?php if ($product['SoLuongTonKho'] > $product['SoLuongDaBan']): ?>
                            <?php 
                            // Kiểm tra đăng nhập
                            $is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
                            ?>
                            <?php if (!$is_logged_in): ?>
                                <div class="login-required-message" style="padding: 2rem; background-color: #fff3cd; border: 2px solid #ffc107; border-radius: 0.5rem; margin-top: 2rem;">
                                    <p style="font-size: 1.6rem; color: #856404; margin-bottom: 1.5rem; text-align: center;">
                                        <i class="fa-solid fa-lock" style="margin-right: 0.5rem;"></i>
                                        Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng
                                    </p>
                                    <div style="text-align: center;">
                                        <a href="../login&registration/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                                           class="btn-login-required" 
                                           style="display: inline-block; padding: 1rem 2rem; background-color: var(--yellow-color); color: black; text-decoration: none; border-radius: 0.5rem; font-weight: 600; margin-right: 1rem;">
                                            <i class="fa-solid fa-sign-in-alt"></i> Đăng nhập
                                        </a>
                                        <a href="../login&registration/registration.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                                           class="btn-register-required" 
                                           style="display: inline-block; padding: 1rem 2rem; background-color: #333; color: white; text-decoration: none; border-radius: 0.5rem; font-weight: 600;">
                                            <i class="fa-solid fa-user-plus"></i> Đăng ký
                                        </a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <form action="../components/cart_handler.php" method="POST" class="add-to-cart-form">
                                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                    <input type="hidden" name="category" value="<?php echo $category; ?>">
                                    <input type="hidden" name="action" value="add">
                                    
                                    <div class="quantity-control">
                                        <label for="quantity">Số lượng:</label>
                                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['SoLuongTonKho'] - $product['SoLuongDaBan']; ?>">
                                    </div>
                                    
                                    <button type="submit" class="btn-add-to-cart">
                                        <i class="fa-solid fa-cart-shopping"></i> Thêm vào Giỏ hàng
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        </div>
                </div>
            </div>
        </div>
    </main>

    <?php include "../components/footer.php"?>
    <script src="/components/js/global.js" defer></script>
</body>
</html>