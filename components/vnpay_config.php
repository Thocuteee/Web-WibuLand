<?php
/**
 * File: components/vnpay_config.php
 * Cấu hình VNPay
 */

// Cấu hình VNPay
define('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'); // URL thanh toán VNPay (sandbox)
define('VNPAY_TMN_CODE', 'YOUR_TMN_CODE'); // Mã TMN từ VNPay (thay bằng mã thực tế)
define('VNPAY_HASH_SECRET', 'YOUR_HASH_SECRET'); // Secret key từ VNPay (thay bằng key thực tế)
define('VNPAY_RETURN_URL', 'http://localhost/components/vnpay_return.php'); // URL callback (thay bằng domain thực tế)

// Hàm tạo URL thanh toán VNPay
function create_vnpay_url($order_id, $amount, $order_desc, $order_type = 'other') {
    $vnp_TmnCode = VNPAY_TMN_CODE;
    $vnp_HashSecret = VNPAY_HASH_SECRET;
    $vnp_Url = VNPAY_URL;
    $vnp_Returnurl = VNPAY_RETURN_URL;
    
    $vnp_TxnRef = $order_id; // Mã đơn hàng
    $vnp_OrderInfo = $order_desc; // Mô tả đơn hàng
    $vnp_OrderType = $order_type; // Loại đơn hàng
    $vnp_Amount = $amount * 100; // Số tiền (VNPay yêu cầu nhân 100)
    $vnp_Locale = 'vn'; // Ngôn ngữ
    $vnp_IpAddr = $_SERVER['REMOTE_ADDR']; // IP khách hàng
    $vnp_CreateDate = date('YmdHis'); // Ngày tạo
    
    $inputData = array(
        "vnp_Version" => "2.1.0",
        "vnp_TmnCode" => $vnp_TmnCode,
        "vnp_Amount" => $vnp_Amount,
        "vnp_Command" => "pay",
        "vnp_CreateDate" => $vnp_CreateDate,
        "vnp_CurrCode" => "VND",
        "vnp_IpAddr" => $vnp_IpAddr,
        "vnp_Locale" => $vnp_Locale,
        "vnp_OrderInfo" => $vnp_OrderInfo,
        "vnp_OrderType" => $vnp_OrderType,
        "vnp_ReturnUrl" => $vnp_Returnurl,
        "vnp_TxnRef" => $vnp_TxnRef,
    );
    
    // Sắp xếp mảng theo key
    ksort($inputData);
    
    // Tạo query string
    $query = "";
    $i = 0;
    $hashdata = "";
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
        $query .= urlencode($key) . "=" . urlencode($value) . '&';
    }
    
    // Tạo chữ ký
    $vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $vnp_Url .= '?' . $query . 'vnp_SecureHash=' . $vnp_SecureHash;
    
    return $vnp_Url;
}

// Hàm xác thực chữ ký từ VNPay
function verify_vnpay_signature($inputData, $vnp_SecureHash) {
    $vnp_HashSecret = VNPAY_HASH_SECRET;
    
    // Loại bỏ vnp_SecureHash
    unset($inputData['vnp_SecureHash']);
    
    // Sắp xếp mảng
    ksort($inputData);
    
    // Tạo hashdata
    $hashdata = "";
    $i = 0;
    foreach ($inputData as $key => $value) {
        if ($i == 1) {
            $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
        } else {
            $hashdata .= urlencode($key) . "=" . urlencode($value);
            $i = 1;
        }
    }
    
    // Tạo chữ ký
    $secureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    
    return $secureHash === $vnp_SecureHash;
}
?>

