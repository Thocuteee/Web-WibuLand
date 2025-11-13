<?php
    include ("../components/connect.php");
    function getMoreProduct($sanpham, $conn){
        $product = "SELECT * FROM `$sanpham`";
        $result = $conn->query($product);
        return $result;
    }


?>