<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Css -->
    <link rel="stylesheet" href="../components/css/global.css">
    <link rel="stylesheet" href="../components/css/header_sidebar_footer.css">
    <link rel="stylesheet" href="../YeuThich/css/YeuThich.css">
          

        
    <title>Wibu Dreamland</title>
</head>
<body>
    <!-- Include Header -->
    <?php include '../components/header.php'; ?>
    
    <!-- MAIN_WEBSITE -->
    <main>
        <!-- Include Sidebar -->
        <?php include '../components/sidebar.php'; ?>
          
            <!-- HOME-CONTENT -->
            <div class="home-content">
                <div class="content" style = "height:100vh">
                <h1>Danh sách sản phẩm yêu thích</h1>

                <div class="bg">
                <button class="remove-all">Xóa tất cả</button>
                <br>
                
                <div class="product-grid">
                <?php 
                    for ($i = 0; $i < 10; $i++) {
                        echo '
                        <div class="product-card">
                        <img class="pic" src="../Home/img/2_691919e9e99c438eb3eaf37501e9b3ac_large.webp" 
                        alt="Dress-up-Darling">
                        <h3>Dress-up-Darling</h3>
                        <p>560,000đ</p>
                        <i class="fa-solid fa-circle-xmark   remove-btn" style="color: #ff0000;"></i>
                    </div>';
                    }
                    ?>
                    </div>
                
            </div>
    </main>
        <?php include "../components/footer.php"?>
        
        <!-- Javascript -->
        <script src="../components/js/global.js" defer></script>
        <script src="js/home.js"defer></script>
    
    
        
    </body>
    </html> 