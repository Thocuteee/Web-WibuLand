<?php
    // BẮT ĐẦU: Chỉ include connect.php để khởi động session và kết nối DB.
    // Hàm get_product_details_by_id_and_category sẽ được load khi include header.php.
    include '../components/connect.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../components/css/global.css">
    <link rel="stylesheet" href="../components/css/header_sidebar_footer.css">
    <link rel="stylesheet" href="../YeuThich/css/YeuThich.css">
          

        
    <title>Wibu Dreamland</title>
</head>
<body>
    <?php include '../components/header.php'; ?>
    
    <main>
        <?php include '../components/sidebar.php'; ?>
          
            <div class="home-content">
                <div class="content" style = "height:100vh">
                <h1>Danh sách sản phẩm yêu thích</h1>

                <div class="bg">
                <a href="../components/cart_handler.php?action=remove_all_wishlist" class="remove-all" onclick="return confirm('Bạn có chắc chắn muốn xóa tất cả sản phẩm yêu thích?');">Xóa tất cả</a>
                <br>
                
                <div class="product-grid">
                <?php 
                    // Kiểm tra và lặp qua danh sách yêu thích trong Session
                    if (isset($_SESSION['wishlist']) && !empty($_SESSION['wishlist'])):
                        foreach ($_SESSION['wishlist'] as $item_key => $item):
                            // Gọi hàm để lấy chi tiết sản phẩm (Được định nghĩa trong header.php)
                            $product_data = get_product_details_by_id_and_category($conn, $item['id'], $item['category']);
                            if ($product_data):
                ?>
                                <div class="product-card">
                                    <a href="../Pagesproducts/product_detail.php?id=<?php echo $item['id']; ?>&category=<?php echo $item['category']; ?>">
                                        <img class="pic" src="/admin/<?php echo $product_data['Img1']; ?>" alt="<?php echo $product_data['Name']; ?>">
                                    </a>
                                    
                                    <h3><?php echo $product_data['Name']; ?></h3>
                                    
                                    <p><?php echo number_format($product_data['final_price']); ?>₫</p>
                                    
                                    <form action="../components/cart_handler.php" method="POST" style="margin-top: 1rem;">
                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="category" value="<?php echo $item['category']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="action" value="add">
                                        <button type="submit" style="background-color: #f9a01b; color: white; border: none; padding: 0.8rem 1rem; border-radius: 0.5rem; cursor: pointer; font-size: 1.4rem;">
                                            <i class="fa-solid fa-cart-shopping"></i> Thêm vào Giỏ hàng
                                        </button>
                                    </form>

                                    <a href="../components/cart_handler.php?action=remove_wishlist&key=<?php echo $item_key; ?>">
                                        <i class="fa-solid fa-circle-xmark remove-btn" style="color: #ff0000; position: absolute; top: 1rem; right: 1rem;"></i>
                                    </a>
                                </div>
                <?php
                            endif;
                        endforeach;
                    else:
                ?>
                        <p style="text-align: center; font-size: 1.6rem; width: 100%;">Danh sách yêu thích của bạn đang trống.</p>
                <?php endif; ?>
                    </div>
                
            </div>
    </main>
        <?php include "../components/footer.php"?>
        
        <script src="../components/js/global.js" defer></script>
        <script src="js/home.js"defer></script>
    
    
        
    </body>
    </html>