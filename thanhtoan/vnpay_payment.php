<?php
/**
 * File: thanhtoan/vnpay_payment.php
 * Trang hiển thị QR code và thông tin thanh toán VNPay
 */

session_start();
include '../components/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login&registration/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    header('Location: ../thongtinkhachhang/donhang.php');
    exit();
}

// Lấy thông tin đơn hàng
$select_order = "SELECT * FROM donhang WHERE IdDonHang = ? AND IdUser = ?";
$stmt = mysqli_prepare($conn, $select_order);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$order) {
    header('Location: ../thongtinkhachhang/donhang.php');
    exit();
}

// Lấy URL thanh toán VNPay
include '../components/vnpay_config.php';
$amount = $order['TongCong'];
$order_desc = "Thanh toan don hang " . $order['MaDonHang'];
$payment_url = create_vnpay_url($order['MaDonHang'], $amount, $order_desc);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán VNPay - Wibu Dreamland</title>
    <link rel="stylesheet" href="../components/css/global.css">
    <link rel="stylesheet" href="../components/css/header_sidebar_footer.css">
    <style>
        .vnpay-container {
            max-width: 600px;
            margin: 5rem auto;
            padding: 3rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,.1);
            text-align: center;
        }
        .vnpay-container h2 {
            color: var(--yellow-color);
            margin-bottom: 2rem;
        }
        .order-info {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        .order-info p {
            margin: 1rem 0;
            font-size: 1.6rem;
        }
        .order-info strong {
            color: #333;
        }
        .qr-section {
            margin: 2rem 0;
        }
        .btn-payment {
            display: inline-block;
            padding: 1.5rem 3rem;
            background-color: #1f4788;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-size: 1.6rem;
            font-weight: 600;
            margin-top: 2rem;
            transition: background-color 0.3s;
        }
        .btn-payment:hover {
            background-color: #16335c;
        }
        .btn-cancel {
            display: inline-block;
            padding: 1rem 2rem;
            background-color: #ccc;
            color: #333;
            text-decoration: none;
            border-radius: 0.5rem;
            font-size: 1.4rem;
            margin-top: 1rem;
        }
        .instructions {
            background: #fff3cd;
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin-top: 2rem;
            text-align: left;
        }
        .instructions h4 {
            color: #856404;
            margin-bottom: 1rem;
        }
        .instructions ol {
            margin-left: 2rem;
            color: #856404;
        }
        .instructions li {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <?php include '../components/header.php'; ?>
    
    <main>
        <?php include '../components/sidebar.php'; ?>
        <div class="home-content">
            <div class="content" style="padding-top: 5rem;">
                <div class="vnpay-container">
                    <h2><i class="fa-solid fa-qrcode"></i> Thanh toán qua VNPay</h2>
                    
                    <div class="order-info">
                        <p><strong>Mã đơn hàng:</strong> <?php echo htmlspecialchars($order['MaDonHang']); ?></p>
                        <p><strong>Số tiền:</strong> <span style="color: var(--yellow-color); font-size: 2rem; font-weight: bold;"><?php echo number_format($amount); ?>₫</span></p>
                        <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['TenNguoiNhan']); ?></p>
                        <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['DiaChiGiaoHang']); ?></p>
                    </div>
                    
                    <div class="qr-section">
                        <p style="font-size: 1.6rem; margin-bottom: 2rem;">Vui lòng click vào nút bên dưới để thanh toán qua VNPay (QR BIDV)</p>
                        <a href="<?php echo htmlspecialchars($payment_url); ?>" class="btn-payment" target="_blank">
                            <i class="fa-solid fa-credit-card"></i> Thanh toán VNPay
                        </a>
                    </div>
                    
                    <div class="instructions">
                        <h4><i class="fa-solid fa-info-circle"></i> Hướng dẫn thanh toán:</h4>
                        <ol>
                            <li>Click vào nút "Thanh toán VNPay" ở trên</li>
                            <li>Quét mã QR bằng ứng dụng ngân hàng (BIDV, Vietcombank, v.v.)</li>
                            <li>Xác nhận thanh toán trên ứng dụng ngân hàng</li>
                            <li>Hệ thống sẽ tự động cập nhật trạng thái đơn hàng sau khi thanh toán thành công</li>
                        </ol>
                    </div>
                    
                    <a href="../thongtinkhachhang/donhang.php" class="btn-cancel">
                        <i class="fa-solid fa-arrow-left"></i> Quay lại đơn hàng
                    </a>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../components/footer.php'; ?>
    
    <script>
        // Kiểm tra thanh toán định kỳ (polling) - tùy chọn
        // Có thể thêm logic để kiểm tra trạng thái đơn hàng sau khi thanh toán
    </script>
</body>
</html>


