<?php
    $host = "localhost";
    $dbname = "website_wibu";   
    $username = "root";
    $password = "";

    // Tắt hiển thị lỗi cho PHP Warning/Notice
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE);

    // Tạo kết nối
    $conn = @mysqli_connect("$host","$username","","$dbname") or die("Mất kết nối");
    session_start();