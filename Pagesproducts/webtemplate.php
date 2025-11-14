<?php
// BƯỚC 1: SỬ DỤNG require_once VỚI ĐƯỜNG DẪN TƯƠNG ĐỐI CHÍNH XÁC (2 CẤP)
// Từ /Pagesproducts/Mohinh/mohinh.php -> /components/connect.php
require_once '../../components/connect.php'; 

/**
 * Hàm lấy danh sách sản phẩm theo Category
 */
function getMoreProduct($sanpham) {
    global $conn; // Đọc biến $conn từ phạm vi toàn cầu
    
    // Kiểm tra $conn để tránh lỗi query() on null
    if (!$conn) {
        return (object)['num_rows' => 0, 'fetch_assoc' => function(){ return null; }];
    }

    $product = "SELECT * FROM `$sanpham` ORDER BY ID DESC LIMIT 20";
    $result = $conn->query($product);
    return $result;
}

/**
 * Hàm tạo cấu trúc HTML cho các trang danh sách sản phẩm
 */
function generateHTMLPagesProducts($sanpham, $name_category) {

    // HTML code
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Paytone+One&display=swap" rel="stylesheet">
        <script src="https://kit.fontawesome.com/eff669a9ab.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="/components/css/global.css">
        <link rel="stylesheet" href="/components/css/header_sidebar_footer.css">
        <link rel="stylesheet" href="/Home/css/home.css">
        <link rel="stylesheet" href="/Pagesproducts/css/listproducts.css">
        <title><?php echo $name_category; ?></title>
        <title><?php echo $name_category; ?></title>
    </head>
    <body>
        <?php 
            // Đường dẫn này cũng phải là 2 cấp để Mohinh/mohinh.php có thể include
            include '../../components/header.php'; 
        ?>
        <main>
            <?php include '../../components/sidebar.php'; ?>
            <div class="home-content">
                <div class="content">
                    <div class="container">
                        <?php
                            $result = getMoreProduct($sanpham); 
                            if ($result->num_rows > 0) {
                                echo '<div class="section">
                                    <h2>
                                        ' .$name_category. '
                                    </h2>
                                    <div class="product-list">';
                    
                                while ($row = $result->fetch_assoc()) {
                                    // Liên kết trang chi tiết (chỉ cần 1 cấp ..)
                                    $detail_url = "../product_detail.php?id=" . $row['ID'] . "&category=" . $sanpham;
                                    
                                    // === LOGIC KIỂM TRA YÊU THÍCH VÀ TẠO LINK ===
                                    $wishlist_key = $sanpham . '_' . $row['ID'];
                                    $is_in_wishlist = isset($_SESSION['wishlist']) && array_key_exists($wishlist_key, $_SESSION['wishlist']);

                                    if ($is_in_wishlist) {
                                        $heart_class = 'fa-solid fa-heart'; 
                                        // Đường dẫn đến cart_handler.php (2 cấp ..)
                                        $wishlist_action_url = '../../components/cart_handler.php?action=remove_wishlist&key=' . $wishlist_key;
                                    } else {
                                        $heart_class = 'fa-regular fa-heart'; 
                                        // Đường dẫn đến cart_handler.php (2 cấp ..)
                                        $wishlist_action_url = '../../components/cart_handler.php?action=add_wishlist&product_id=' . $row['ID'] . '&category=' . $sanpham;
                                    }
                                    // ===========================================
                                    
                                    echo '<div class="product">';

                                    echo '<a href="' . $detail_url . '">';
                                    echo '<img src="/admin/' . $row['Img1'] . '">';
                                    echo '</a>';
                                    
                                    echo '<a href="' . $detail_url . '">';
                                    echo '<div class="name">' . $row['Name'] . '</div>';
                                    echo '</a>';

                                    if ($row['SoLuongDaBan'] == $row['SoLuongTonKho']) {
                                        echo '<div class="sold-out" style ="background: orange">Hết hàng</div>';
                                        
                                    } else {
                                        if ($row['Sale'] > 0) {
                                            echo '<div class="discount">-' . $row['Sale'] . '%</div>';
                                            
                                            //Tính giá giảm không sợ lỗ
                                            $Giacu = number_format(($row['Gia'] / (1-$row['Sale'] / 100)));
                                        }
                                    
                                        echo '<div class="price">' . number_format($row['Gia']) . '₫</div>';
                                        if (isset($Giacu)) {    
                                            echo '<div class="old-price">' . $Giacu . '₫</div>';
                                        }
                                        
                                        // Nút Yêu thích động
                                        echo '<div class="heart-icon">';
                                        // Link trỏ đến #, gọi hàm JS
                                        echo '<a href="#" onclick="toggleWishlist(event, ' . $row['ID'] . ', \'' . $sanpham . '\', ' . ($is_in_wishlist ? 'true' : 'false') . ')">';
                                        // Đặt ID cho icon để JS có thể tìm và thay đổi trạng thái
                                        echo '<i id="wishlist_' . $wishlist_key . '" class="' . $heart_class . '" style="color: #f70202;"></i>'; 
                                        echo '</a>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                }
                                
                                echo '</div> 
                                <div class = "btn-see-more"><button>Xem thêm >></button></div>
                                </div>';
                            }
                        ?>
                    </div>
                </div>
            </div>
        </main>
        <?php include "../../components/footer.php"?>

        <script src="/components/js/global.js" defer></script>
        <script src="/components/js/wishlist.js" defer></script>                   
    
    </body>
    </html>
    <?php
}
?>