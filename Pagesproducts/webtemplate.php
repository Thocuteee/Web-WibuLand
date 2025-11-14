<?php
    function getMoreProduct($sanpham, $conn){
        $product = "SELECT * FROM `$sanpham` ORDER BY ID DESC LIMIT 20";
        $result = $conn->query($product);
        return $result;
    }

?>



<?php
function generateHTMLPagesProducts($sanpham, $name_category) {

    // HTML code
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="/components/css/global.css">
        <link rel="stylesheet" href="/components/css/header_sidebar_footer.css">
        <link rel="stylesheet" href="/Home/css/home.css">
        <link rel="stylesheet" href="/Pagesproducts/css/listproducts.css">
        <title><?php echo $name_category; ?></title>
    </head>
    <body>
        <?php include '../../components/header.php'; ?>
        <main>
            <?php include '../../components/sidebar.php'; ?>
            <div class="home-content">
                <div class="content">
                    <div class="container">
                        <?php
                            $result = getMoreProduct($sanpham, $conn);
                            if ($result->num_rows > 0) {
                                echo '<div class="section">
                                    <h2>
                                        ' .$name_category. '
                                    </h2>
                                    <div class="product-list">';
                    
                                while ($row = $result->fetch_assoc()) {
                                    $detail_url = "product_detail.php?id=" . $row['ID'] . "&category=" . $sanpham;
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
                                        echo '<div class="heart-icon"><i class="fa-regular fa-heart" style="color: #f70202;"></i></div>'; 
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

    
    </body>
    </html>
    <?php
}
