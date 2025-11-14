<?php
    // BẮT ĐẦU: Chỉ include connect.php để khởi động session và kết nối DB.
    // Hàm get_product_details_by_id_and_category được định nghĩa trong header.php.
    include '../components/connect.php'; 
    include '../components/header.php'; // Cần include header để có hàm get_product_details_by_id_and_category
    
    // === BỔ SUNG: LOGIC LẤY DANH SÁCH YÊU THÍCH TỪ DB ===
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $db_wishlist_items = [];

    if ($user_id) {
        // Lấy tất cả các sản phẩm yêu thích của người dùng từ DB
        $select_wishlist_query = "SELECT IdSanPham, LoaiSanPham FROM `yeuthich` WHERE IdUser = ?";
        $stmt_wishlist = mysqli_prepare($conn, $select_wishlist_query);
        if ($stmt_wishlist) {
            mysqli_stmt_bind_param($stmt_wishlist, "i", $user_id);
            mysqli_stmt_execute($stmt_wishlist);
            $result_wishlist = mysqli_stmt_get_result($stmt_wishlist);
            
            while ($row = mysqli_fetch_assoc($result_wishlist)) {
                $db_wishlist_items[] = [
                    'id' => $row['IdSanPham'],
                    'category' => $row['LoaiSanPham']
                ];
            }
            mysqli_stmt_close($stmt_wishlist);
        }
    }
    // ========================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../components/css/global.css">
    <link rel="stylesheet" href="../components/css/header_sidebar_footer.css">
    <link rel="stylesheet" href="../YeuThich/css/YeuThich.css">
    <script src="https://kit.fontawesome.com/eff669a9ab.js" crossorigin="anonymous"></script>    
    <title>Wibu Dreamland - Yêu Thích</title>
</head>
<body>
    <?php // include '../components/header.php'; // Đã include ở trên ?>
    
    <main>
        <?php include '../components/sidebar.php'; ?>
          
            <div class="home-content">
                <div class="content" style = "height:100vh">
                <h1>Danh sách sản phẩm yêu thích</h1>

                <div class="bg">
                <?php if ($user_id): // Chỉ hiển thị nút xóa tất cả nếu user đã đăng nhập ?>
                    <a href="../components/cart_handler.php?action=remove_all_wishlist" class="remove-all" onclick="return confirm('Bạn có chắc chắn muốn xóa tất cả sản phẩm yêu thích?');">Xóa tất cả</a>
                <?php endif; ?>
                <br>
                
                <div class="product-grid">
                <?php 
                    // Lặp qua danh sách yêu thích lấy từ DB
                    if (!empty($db_wishlist_items)):
                        foreach ($db_wishlist_items as $item):
                            $product_id = $item['id'];
                            $category = $item['category'];
                            $item_key = $category . '_' . $product_id;
                            
                            $product_data = get_product_details_by_id_and_category($conn, $product_id, $category);
                            
                            if ($product_data):
                ?>
                                <div class="product-card">
                                    <a href="../Pagesproducts/product_detail.php?id=<?php echo $product_id; ?>&category=<?php echo $category; ?>">
                                        <img class="pic" src="/admin/<?php echo $product_data['Img1']; ?>" alt="<?php echo $product_data['Name']; ?>">
                                    </a>
                                    
                                    <h3><?php echo $product_data['Name']; ?></h3>
                                    
                                    <p><?php echo number_format($product_data['final_price']); ?>₫</p>
                                    
                                    <form action="../components/cart_handler.php" method="POST" style="margin-top: 1rem;">
                                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                        <input type="hidden" name="category" value="<?php echo $category; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="action" value="add">
                                        <button type="submit" style="background-color: #f9a01b; color: white; border: none; padding: 0.8rem 1rem; border-radius: 0.5rem; cursor: pointer; font-size: 1.4rem;">
                                            <i class="fa-solid fa-cart-shopping"></i> Thêm vào Giỏ hàng
                                        </button>
                                    </form>

                                    <a href="../components/cart_handler.php?action=remove_wishlist&product_id=<?php echo $product_id; ?>&category=<?php echo $category; ?>">
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
        <script src="../components/js/wishlist.js" defer></script> 
        <script src="js/home.js"defer></script>
    
    
        
    </body>
    </html>