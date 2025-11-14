<?php
    $host = "localhost";
    $dbname = "website_wibu";   
    $username = "root";
    $password = "";

    // Tạo kết nối
    $conn = @mysqli_connect("$host","$username","","$dbname") or die("Mất kết nối");
    session_start();
?>