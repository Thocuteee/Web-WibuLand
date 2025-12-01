<?php
/**
 * File: components/vnpay_qr.php
 * Tạo mã QR thanh toán VNPay
 */

session_start();
include 'connect.php';
include 'vnpay_config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập']);
    exit();
}

// Kiểm tra có order_id không
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin đơn hàng']);
    exit();
}

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Lấy thông tin đơn hàng
$select_order = "SELECT * FROM donhang WHERE IdDonHang = ? AND IdUser = ?";
$stmt = mysqli_prepare($conn, $select_order);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Đơn hàng không tồn tại']);
    exit();
}

// Tạo URL thanh toán VNPay
$amount = $order['TongCong'];
$order_desc = "Thanh toan don hang " . $order['MaDonHang'];
$payment_url = create_vnpay_url($order['MaDonHang'], $amount, $order_desc);

// Trả về URL thanh toán
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'payment_url' => $payment_url,
    'order_code' => $order['MaDonHang'],
    'amount' => number_format($amount) . '₫'
]);
?>

