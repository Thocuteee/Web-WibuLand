<?php
/**
 * File: components/vnpay_return.php
 * Xử lý callback từ VNPay sau khi thanh toán
 */

session_start();
include 'connect.php';
include 'vnpay_config.php';

// Lấy dữ liệu từ VNPay
$vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
$inputData = [];

foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

// Xác thực chữ ký
$is_valid = verify_vnpay_signature($inputData, $vnp_SecureHash);

if (!$is_valid) {
    $_SESSION['order_message'] = ['type' => 'error', 'text' => 'Chữ ký không hợp lệ'];
    header("Location: ../thongtinkhachhang/donhang.php");
    exit();
}

// Lấy thông tin từ VNPay
$vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
$vnp_TxnRef = $inputData['vnp_TxnRef'] ?? ''; // Mã đơn hàng
$vnp_Amount = $inputData['vnp_Amount'] ?? 0;
$vnp_TransactionStatus = $inputData['vnp_TransactionStatus'] ?? '';
$vnp_TransactionNo = $inputData['vnp_TransactionNo'] ?? '';

// Kiểm tra kết quả thanh toán
// ResponseCode = '00' và TransactionStatus = '00' là thành công
if ($vnp_ResponseCode == '00' && $vnp_TransactionStatus == '00') {
    // Tìm đơn hàng theo mã đơn hàng
    $select_order = "SELECT * FROM donhang WHERE MaDonHang = ?";
    $stmt = mysqli_prepare($conn, $select_order);
    mysqli_stmt_bind_param($stmt, "s", $vnp_TxnRef);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($order) {
        // Kiểm tra số tiền
        $order_amount = $order['TongCong'] * 100; // VNPay trả về số tiền đã nhân 100
        
        if ($vnp_Amount == $order_amount) {
            // Cập nhật trạng thái đơn hàng thành "Đã xác nhận" (đã thanh toán thành công)
            $update_order = "UPDATE donhang SET TrangThai = 'Đã xác nhận', NgayCapNhat = NOW() WHERE IdDonHang = ?";
            $stmt_update = mysqli_prepare($conn, $update_order);
            mysqli_stmt_bind_param($stmt_update, "i", $order['IdDonHang']);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
            
            // Đóng popup nếu đang mở (thông qua session flag)
            $_SESSION['vnpay_payment_success'] = true;
            $_SESSION['vnpay_order_id'] = $order['IdDonHang'];
            
            $_SESSION['order_message'] = [
                'type' => 'success', 
                'text' => '✅ Thanh toán thành công! Đơn hàng ' . $order['MaDonHang'] . ' đã được xác nhận và sẽ được xử lý sớm nhất.'
            ];
        } else {
            $_SESSION['order_message'] = [
                'type' => 'error', 
                'text' => 'Số tiền thanh toán không khớp với đơn hàng.'
            ];
        }
    } else {
        $_SESSION['order_message'] = [
            'type' => 'error', 
            'text' => 'Không tìm thấy đơn hàng.'
        ];
    }
} else {
    // Thanh toán thất bại
    $error_messages = [
        '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
        '09' => 'Thẻ/Tài khoản chưa đăng ký dịch vụ InternetBanking',
        '10' => 'Xác thực thông tin thẻ/tài khoản không đúng. Quá 3 lần',
        '11' => 'Đã hết hạn chờ thanh toán. Xin vui lòng thực hiện lại giao dịch.',
        '12' => 'Thẻ/Tài khoản bị khóa.',
        '13' => 'Nhập sai mật khẩu xác thực giao dịch (OTP). Quá 5 lần',
        '51' => 'Tài khoản không đủ số dư để thực hiện giao dịch.',
        '65' => 'Tài khoản đã vượt quá hạn mức giao dịch trong ngày.',
        '75' => 'Ngân hàng thanh toán đang bảo trì.',
        '79' => 'Nhập sai mật khẩu thanh toán quá số lần quy định.',
        '99' => 'Lỗi không xác định'
    ];
    
    $error_msg = $error_messages[$vnp_ResponseCode] ?? 'Thanh toán thất bại. Mã lỗi: ' . $vnp_ResponseCode;
    $_SESSION['order_message'] = ['type' => 'error', 'text' => $error_msg];
}

// Redirect về trang đơn hàng
header("Location: ../thongtinkhachhang/donhang.php");
exit();
?>

