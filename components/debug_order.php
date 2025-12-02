<?php
// File tạm thời để debug order_handler
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'connect.php';

echo "<h1>Debug Order Handler</h1>";

echo "<h2>1. Kiểm tra kết nối database:</h2>";
if ($conn) {
    echo "✓ Kết nối database thành công<br>";
} else {
    echo "✗ Lỗi kết nối database: " . mysqli_connect_error() . "<br>";
    exit();
}

echo "<h2>2. Kiểm tra bảng donhang:</h2>";
$check_table = "SHOW TABLES LIKE 'donhang'";
$result = mysqli_query($conn, $check_table);
if (mysqli_num_rows($result) > 0) {
    echo "✓ Bảng donhang tồn tại<br>";
    
    // Kiểm tra cấu trúc bảng
    $desc_query = "DESCRIBE donhang";
    $desc_result = mysqli_query($conn, $desc_query);
    echo "<h3>Cấu trúc bảng donhang:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($desc_result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . ($row['Extra'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Kiểm tra AUTO_INCREMENT
    $ai_query = "SHOW TABLE STATUS LIKE 'donhang'";
    $ai_result = mysqli_query($conn, $ai_query);
    if ($ai_row = mysqli_fetch_assoc($ai_result)) {
        echo "<h3>Thông tin AUTO_INCREMENT:</h3>";
        echo "Auto_increment: " . ($ai_row['Auto_increment'] ?? 'NULL') . "<br>";
    }
} else {
    echo "✗ Bảng donhang KHÔNG tồn tại!<br>";
}

echo "<h2>3. Kiểm tra bảng donhang_chitiet:</h2>";
$check_table2 = "SHOW TABLES LIKE 'donhang_chitiet'";
$result2 = mysqli_query($conn, $check_table2);
if (mysqli_num_rows($result2) > 0) {
    echo "✓ Bảng donhang_chitiet tồn tại<br>";
} else {
    echo "✗ Bảng donhang_chitiet KHÔNG tồn tại!<br>";
}

echo "<h2>4. Kiểm tra session:</h2>";
if (isset($_SESSION['user_id'])) {
    echo "✓ User ID: " . $_SESSION['user_id'] . "<br>";
} else {
    echo "✗ Chưa đăng nhập<br>";
}

echo "<h2>5. Kiểm tra POST data:</h2>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "✓ Có POST data<br>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
} else {
    echo "✗ Không có POST data (phải dùng GET để xem trang này)<br>";
}

echo "<h2>6. Test INSERT vào donhang:</h2>";
$test_query = "INSERT INTO `donhang` 
    (IdUser, MaDonHang, TenNguoiNhan, SDTNguoiNhan, DiaChiGiaoHang, TinhThanh, PhuongThucThanhToan, TongTien, PhiVanChuyen, GiamGia, TongCong, GhiChu)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $test_query);
if ($stmt) {
    echo "✓ Prepare statement thành công<br>";
    mysqli_stmt_close($stmt);
} else {
    echo "✗ Prepare statement thất bại: " . mysqli_error($conn) . "<br>";
}

?>


