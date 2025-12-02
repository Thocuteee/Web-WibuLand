<?php
/**
 * File: components/vnpay_qr.php
 * Tạo mã QR thanh toán VNPay
 */

// Bắt đầu output buffering để tránh output không mong muốn
ob_start();

session_start();
include 'connect.php';
include 'vnpay_config.php';

// Xóa output buffer trước khi trả JSON
while (ob_get_level()) {
    ob_end_clean();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập'], JSON_UNESCAPED_UNICODE);
    exit();
}

// Kiểm tra có order_id không
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin đơn hàng'], JSON_UNESCAPED_UNICODE);
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
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Đơn hàng không tồn tại'], JSON_UNESCAPED_UNICODE);
    exit();
}

// Kiểm tra xem hàm create_vnpay_url có tồn tại không
if (!function_exists('create_vnpay_url')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Lỗi cấu hình VNPay. Vui lòng liên hệ quản trị viên.'], JSON_UNESCAPED_UNICODE);
    exit();
}

// Nếu chưa cấu hình TMN CODE hoặc HASH SECRET -> trả về URL test (chỉ để hiển thị QR, không thanh toán thật)
$amount = (int)$order['TongCong'];
$order_desc = "Thanh toan don hang " . $order['MaDonHang'];

if (VNPAY_TMN_CODE === 'YOUR_TMN_CODE' || VNPAY_HASH_SECRET === 'YOUR_HASH_SECRET') {
    // Tạo URL test (QR vẫn quét được nhưng không thực hiện giao dịch thật)
    $test_url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?vnp_Amount=" . ($amount * 100) . 
                "&vnp_Command=pay&vnp_CreateDate=" . date('YmdHis') . 
                "&vnp_CurrCode=VND&vnp_IpAddr=" . ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . 
                "&vnp_Locale=vn&vnp_OrderInfo=" . urlencode($order_desc) . 
                "&vnp_OrderType=other&vnp_ReturnUrl=" . urlencode(VNPAY_RETURN_URL) . 
                "&vnp_TmnCode=TEST&vnp_TxnRef=" . urlencode($order['MaDonHang']) . 
                "&vnp_Version=2.1.0&vnp_SecureHash=TEST";
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'warning',
        'message' => 'VNPay chưa được cấu hình. Đây là QR test, không thanh toán thật.',
        'payment_url' => $test_url,
        'order_code' => $order['MaDonHang'],
        'amount' => number_format($amount) . '₫',
        'amount_number' => $amount,
        'config_required' => true
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Tạo URL thanh toán VNPay thực tế
$payment_url = create_vnpay_url($order['MaDonHang'], $amount, $order_desc);

// Trả về URL thanh toán
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'success',
    'payment_url' => $payment_url,
    'order_code' => $order['MaDonHang'],
    'amount' => number_format($amount) . '₫',
    'amount_number' => $amount
], JSON_UNESCAPED_UNICODE);
exit();
?>


