<?php
    include ("../components/connect.php");
    function getMoreProduct($sanpham, $conn){
        $product = "SELECT * FROM `$sanpham`";
        $result = $conn->query($product);
        return $result;
    }


    if (!isset($_GET["category"])){
        $category = "mohinh";
    }
    else{ 
        $category = $_GET["category"];
    }

    //IN SẢN PHẨM
    $products= getMoreProduct($category,$conn);
    
  // THÊM SẢN PHẨM
if (isset($_POST["submitAdd"])){
    $productName = $_POST["productName"];
    $productPrice = $_POST["productPrice"];
    $productStock = $_POST["productStock"];
    $productSold = $_POST["productSold"];
    $productSale = $_POST["productSale"];
    
    // Xử lý category
    if ($_POST["productCategory"] == "mohinh"){
        $productCategory = 1;
        $category = "mohinh";
    }else if ($_POST["productCategory"] == "magma"){ 
        $productCategory = 2;
        $category = "magma";
    }else{
        $productCategory = 3;
        $category = "cosplay";
    }

    // Xử lý upload file
    if(isset($_FILES['productImage'])&& $_FILES['productImage']['size'] > 0) {
        $folder = "_imgProduct/$category/"; 

        $fileName = time() . '_' . $_FILES['productImage']['name'];
        $filePath = $folder . $fileName;

        // Upload file 
        if(move_uploaded_file($_FILES['productImage']['tmp_name'], $filePath)) {
            $productImage = $filePath;
            
            // Thêm vào database
            $sql = "INSERT INTO `$category` (Name,Img1,Gia,SoLuongTonKho,SoLuongDaBan,Sale,TheLoai) 
                   VALUES('$productName','$productImage','$productPrice','$productStock','$productSold','$productSale','$productCategory')";
            
            if(mysqli_query($conn, $sql)){
                $message = "Tải lên thành công";
            }
        } 
    }
    header("Location: admin.php?category=$category");
    exit();
}



//Mở form sửa chửa
if(isset($_GET["category"]) && isset ($_GET["id"])){
    $category = $_GET["category"];
    $id = $_GET["id"];
    $product = "SELECT * FROM `$category` WHERE ID = '$id' ";
    $result = $conn->query($product);
    if ($result->num_rows > 0) {
        $productToEdit = $result->fetch_assoc();
        echo "<script>
            window.onload = function() {
                document.getElementById('productEditModal').style.display = 'block';
            }
        </script>";
    }



        //CHỈNH SỬA THÔNG TIN
    if(isset($_POST["submitEdit"])){
        $arrayUpdate = array();

        //Kiểm tra Tên
        if($_POST["productName"] != $productToEdit['Name']){
            $productName = $_POST["productName"];
            $arrayUpdate[] = "Name='$productName'";
        }

        //Kiểm tra Gía
        if($_POST["productPrice"] != $productToEdit['Gia']){
            $productPrice = $_POST["productPrice"];
            $arrayUpdate[] = "Gia='$productPrice'";
        }

        //Kiểm tra số lượng tồn kho
        if($_POST["productStock"]!= $productToEdit['SoLuongTonKho']){
            $productStock = $_POST["productStock"];
            $arrayUpdate[] = "SoLuongTonKho='$productStock'";
        }

        //Kiểm tra số lượng đã bán
        if($_POST["productSold"] != $productToEdit['SoLuongDaBan']){
            $productSold = $_POST["productSold"];
            $arrayUpdate[] = "SoLuongDaBan='$productSold'";
        }

        //Kiểm tra giảm giá
        if($_POST["productSale"] != $productToEdit['Sale']){
            $productSale = $_POST["productSale"];
            $arrayUpdate[] = "Sale='$productSale'";
        }

        //Kiểm tra ảnh
        if(isset($_FILES['productImage'])&& $_FILES['productImage']['size'] > 0) {
            $folder = "_imgProduct/$category/"; 
    
            $fileName = time() . '_' . $_FILES['productImage']['name'];
            $filePath = $folder . $fileName;
    
            // Upload file 
            if(move_uploaded_file($_FILES['productImage']['tmp_name'], $filePath)) {
                $arrayUpdate[] = "Img1='$filePath'";
            }


        }
        if(!empty($arrayUpdate)) {
            $sql = "UPDATE `$category` SET " . implode(", ", $arrayUpdate) . " WHERE ID='$id'";
                
            if(mysqli_query($conn, $sql)){
                $message = "Cập nhật thành công";
                }
        }else{
                $message = "Không có gì thay đổi";    
            }
        header("Location: admin.php?category=$category");
        exit();
    }


    //XÓA SẢN PHẨM
    if (isset($_GET['action']) && $_GET['action'] == 'delete') {
        echo "<script>
            window.onload = function() {
                document.getElementById('productDeleteModal').style.display = 'block';
            }
         </script>";
        if (isset($_POST['submitDelete'])) {
            $id = $_GET['id'];
            $sql = "DELETE FROM `$category` WHERE ID = '$id'";
            if (mysqli_query($conn, $sql)) {
                $message = "Xóa thành công";
            }
            header("Location: admin.php?category=$category");
            exit();
        }
    }
}






?>


<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wibu Dreamland/Admin</title>
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>
    <div class="container">
        <?php
            if (isset($message)) {
            echo '<div class="alert">' . $message . '</div>';
            }
        ?>

        <h1>Quản lý sản phẩm</h1>
        <div class="actions">
            <a href="../Home/index.php" class="btn-func" style="background-color: #4caf50;">
                <i class="fa-solid fa-home"></i> Về Trang chủ
            </a>
            
            <a href="orders.php" class="btn-func" style="background-color: #f9a01b;">
                <i class="fa-solid fa-box"></i> Quản lý Đơn hàng
            </a>
            
            <a href="inventory.php" class="btn-func" style="background-color: #667eea;">
                <i class="fa-solid fa-boxes-stacked"></i> Quản lý Tồn kho
            </a>
            
            <form method="GET" action="">
                <select id="categorySelect" onchange="this.form.submit()" name="category">
                    <option value="mohinh" <?php if ($category == "mohinh") echo "selected" ?>>Mô hình</option>
                    <option value="magma" <?php if ($category == "magma") echo "selected" ?>>Manga</option>
                    <option value="cosplay" <?php if ($category == "cosplay") echo "selected" ?>>Cosplay</option>
                </select>
            </form>
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Tìm kiếm sản phẩm...">
                <button onclick="searchProducts()">Tìm kiếm</button>
            </div>
            <button onclick="showAddModal()">Thêm sản phẩm</button>
        </div>
        <div id="productTable">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng tồn kho</th>
                        <th>Số lượng đã bán</th>
                        <th>Sale (%)</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    <?php   
                    if ($products->num_rows > 0) {
                        while($row = $products->fetch_assoc()) {
    
                            echo "<tr>";
                            echo "<td>".$row['ID']."</td>";
                            echo "<td><img src='".$row['Img1']."' alt='' width='100px'></td>";
                            echo "<td>".$row['Name']."</td>";
                            echo "<td>".$row['Gia']."</td>";
                            echo "<td>".$row['SoLuongTonKho']."</td>";
                            echo "<td>".$row['SoLuongDaBan']."</td>";
                            echo "<td>".$row['Sale']."</td>";
                            echo "<td>
                                    <a href='admin.php?category=$category&id=".$row['ID']."' class='btn-func'>Chỉnh sửa</a>
                                    <a href='admin.php?category=$category&action=delete&id=".$row['ID']."' class='btn-func'>Xóa</a>
                                </td>";
                            echo "</tr>";
                        }
                    }
                    ?>


                </tbody>
            </table>
        </div>
    </div>

    <!-- Form Add Product -->
    <div id="productAddModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddModal()">&times;</span>
            <h2 id="modalTitle">Thêm sản phẩm</h2>
            <form id="productForm" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Tên sản phẩm:</label>
                    <input name="productName" type="text" id="productName" value="" <?php echo "" ?> required>
                </div>
                <div class="form-group">
                    <label>Hình ảnh:</label>
                    <input name="productImage" type="file" id="productImage" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Giá:</label>
                    <input name="productPrice" type="number" id="productPrice" required>
                </div>
                <div class="form-group">
                    <label>Số lượng tồn kho:</label>
                    <input name="productStock" type="number" id="productStock" required>
                </div>
                <div class="form-group">
                    <label>Số lượng đã bán:</label>
                    <input name="productSold" type="number" id="productSold" required>
                </div>
                <div class="form-group">
                    <label>Sale (%):</label>
                    <input name="productSale" type="number" id="productSale" min="0" max="100">
                </div>
                <div class="form-group">
                    <label>Thể loại:</label>
                    <select name="productCategory" id="productCategory" required>
                        <option value="mohinh" <?php if ($category == "mohinh") echo "selected" ?>>Mô hình</option>
                        <option value="magma" <?php if ($category == "magma") echo "selected" ?>>Manga</option>
                        <option value="cosplay" <?php if ($category == "cosplay") echo "selected" ?>>Cosplay</option>
                    </select>
                </div>
                <button type="submit" name="submitAdd">Lưu</button>
            </form>
        </div>
    </div>



    <!-- Form Edit Product -->
    <div id="productEditModal" class="modal">
        <div class="modal-content">
            <span class="close"
                onclick="window.location.href='admin.php?category=<?php echo $category; ?>'">&times;</span>
            <h2 id="modalTitle">Chỉnh sửa sản phẩm</h2>
            <form id="productForm" method="POST"
                action="admin.php?category=<?php echo $category; ?>&id=<?php echo $id; ?>"
                enctype="multipart/form-data">
                <div class="form-group">
                    <label>Tên sản phẩm:</label>
                    <input value="<?php echo $productToEdit['Name']; ?>" name="productName" type="text" id="productName"
                        required>
                </div>
                <div class="form-group">
                    <label>Hình ảnh:</label>
                    <img src="<?php echo $productToEdit['Img1']; ?>" style="width: 100px"><br><br>
                    <input name="productImage" type="file" id="productImage" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Giá:</label>
                    <input value="<?php echo $productToEdit['Gia']; ?>" name="productPrice" type="number"
                        id="productPrice" required>
                </div>
                <div class="form-group">
                    <label>Số lượng tồn kho:</label>
                    <input value="<?php echo $productToEdit['SoLuongTonKho']; ?>" name="productStock" type="number"
                        id="productStock" required>
                </div>
                <div class="form-group">
                    <label>Số lượng đã bán:</label>
                    <input value="<?php echo $productToEdit['SoLuongDaBan']; ?>" name="productSold" type="number"
                        id="productSold" required>
                </div>
                <div class="form-group">
                    <label>Sale (%):</label>
                    <input value="<?php echo $productToEdit['Sale']; ?>" name="productSale" type="number"
                        id="productSale" min="0" max="100">
                </div>
                <button type="submit" name="submitEdit">Lưu</button>
            </form>
        </div>
    </div>

    <!-- FORM XÁC ĐỊNH MUỐN XÓA -->
    <div id="productDeleteModal" class="modal">
        <div class="modal-content">
             <form id="productForm" method="POST">
                <span class="close"
                    onclick="window.location.href='admin.php?category=<?php echo $category; ?>'">&times;</span>
                <h2>Xác nhận xóa</h2>
                <p>Bạn có chắc chắn muốn xóa sản phẩm này không?</p>
            
                <div class="button-group">
                    <button class="delete-btn" type="submit" name="submitDelete" >Xóa</button>
                </div>
            </form>       
        </div>
    </div>
            



</body>

<script src="js/admin.js"></script>

</html>