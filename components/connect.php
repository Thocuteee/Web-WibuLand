<?php
    // Đảm bảo không có output trước khi kết nối
    if (!isset($conn)) {
        $host = "localhost";
        $dbname = "website_wibu";   
        $username = "root";
        $password = "";

        // Tắt hiển thị lỗi cho PHP Warning/Notice
        ini_set('display_errors', 0);
        error_reporting(E_ALL & ~E_NOTICE);

        // Tạo kết nối - không dùng die() để tránh output
        $conn = @mysqli_connect($host, $username, $password, $dbname);
        if (!$conn) {
            // Nếu là AJAX request, trả về JSON lỗi
            if (isset($_GET['ajax']) || isset($_POST['ajax']) || 
                (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối database']);
                exit();
            }
            // Nếu không phải AJAX, chỉ set biến null
            $conn = null;
        }
        
        if (!session_id()) {
            session_start();
        }
    }