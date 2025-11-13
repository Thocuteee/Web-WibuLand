<?php
    $host = "localhost";
    $dbname = "website_wibu";   
    $username = "root";
    $password = "";

    // Tạo kết nối
    $conn = @mysqli_connect("$host","$username","","$dbname") or die("Mất kết nối");
    session_start();
?>

<!-- GOOGLE FONT -->
        <!-- Kiểu chữ Noto San, Paytone -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Paytone+One&display=swap" rel="stylesheet">
    <!-- FONT AWESOME -->
    <script src="https://kit.fontawesome.com/eff669a9ab.js" crossorigin="anonymous"></script>