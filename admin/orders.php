<?php
    // File: admin/orders.php

    session_start();
    include '../components/connect.php'; 

    // CHỈ CHO PHÉP ADMIN (role = 0) truy cập
    if (!isset($_SESSION['user_id']) || ($_SESSION['user_id'] != 1)) { // Giả định admin có IdUser = 1
        header('Location: ../login&registration/login.php');
        exit();
    }
    
    // Lấy thông báo (nếu có)
    $message = $_SESSION['admin_message'] ?? null;
    if (isset($_SESSION['admin_message'])) {
        unset($_SESSION['admin_message']);
    }

    // Lấy danh sách đơn hàng và tên người dùng
    $orders = [];
    $select_orders = "SELECT d.*, u.NameUser 
                      FROM donhang d
                      JOIN users u ON d.IdUser = u.IdUser
                      ORDER BY d.NgayTao DESC";
    $result_orders = mysqli_query($conn, $select_orders);

    if ($result_orders) {
        while ($order = mysqli_fetch_assoc($result_orders)) {
             // Lấy chi tiết đơn hàng
            $select_details = "SELECT TenSanPham, SoLuong, Gia FROM donhang_chitiet WHERE IdDonHang = ?";
            $stmt_details = mysqli_prepare($conn, $select_details);
            mysqli_stmt_bind_param($stmt_details, "i", $order['IdDonHang']);
            mysqli_stmt_execute($stmt_details);
            $result_details = mysqli_stmt_get_result($stmt_details);
            
            $order['items'] = [];
            while ($detail = mysqli_fetch_assoc($result_details)) {
                $order['items'][] = $detail;
            }
            mysqli_stmt_close($stmt_details);
            $orders[] = $order;
        }
    }

    // Hàm lấy màu trạng thái
    function get_status_color($status) {
        $colors = [
            'Chờ xử lý' => '#ff9800', // Cam
            'Đã xác nhận' => '#2196f3', // Xanh dương
            'Đang giao hàng' => '#9c27b0', // Tím
            'Đã giao hàng' => '#4caf50', // Xanh lá
            'Đã hủy' => '#f44336' // Đỏ
        ];
        return $colors[$status] ?? '#666';
    }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Quản lý Đơn hàng</title>
    <link rel="stylesheet" href="css/admin.css">
    <script src="https://kit.fontawesome.com/eff669a9ab.js" crossorigin="anonymous"></script>
    <style>
        .order-card { 
            border: 1px solid #ccc; border-radius: 8px; margin-bottom: 2rem; padding: 1.5rem; 
            background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); 
        }
        .order-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 1rem; margin-bottom: 1rem; }
        .order-id { font-size: 1.8rem; font-weight: bold; color: #333; }
        .order-status { padding: 0.5rem 1rem; border-radius: 5px; font-weight: 600; font-size: 1.4rem; }
        .order-info { font-size: 1.4rem; }
        .order-actions { margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .order-actions button { padding: 0.8rem 1.5rem; font-size: 1.3rem; cursor: pointer; border: none; border-radius: 5px; color: white; transition: background-color 0.3s; }
        .btn-approve { background-color: #4CAF50; }
        .btn-reject { background-color: #f44336; }
        .btn-ship { background-color: #2196f3; }
        .btn-complete { background-color: #ff9800; }
        .btn-approve:hover { background-color: #45a049; }
        .btn-reject:hover { background-color: #d32f2f; }
        .btn-ship:hover { background-color: #1976d2; }
        .btn-complete:hover { background-color: #e68a00; }
        .item-details { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out, opacity 0.3s ease-out; opacity: 0; padding-top: 0; }
        .item-details.show { max-height: 500px; opacity: 1; padding-top: 1rem; }
        .detail-item { font-size: 1.3rem; border-bottom: 1px dashed #eee; padding: 0.5rem 0; display: flex; justify-content: space-between; }
        .order-summary-admin { font-size: 1.4rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee; }
        .order-summary-admin strong { font-weight: 700; color: #333; }
    </style>
</head>

<body>
    <div class="container">
        <h1>Quản lý Đơn hàng</h1>
        <div class="actions">
            <a href="admin.php" class="btn-func" style="background-color: #6c757d;">
                <i class="fa-solid fa-arrow-left"></i> Quay lại QL Sản phẩm
            </a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert" style="background-color: <?php echo $message['type'] == 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message['type'] == 'success' ? '#155724' : '#721c24'; ?>;">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <p style="font-size: 1.6rem; text-align: center; margin-top: 5rem;">Không có đơn hàng nào.</p>
        <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <span class="order-id">Mã ĐH: <?php echo htmlspecialchars($order['MaDonHang']); ?></span>
                                <p class="order-date">Khách: **<?php echo htmlspecialchars($order['NameUser']); ?>** (ID: <?php echo $order['IdUser']; ?>)</p>
                                <p class="order-date">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['NgayTao'])); ?></p>
                            </div>
                            <div class="order-status" style="background-color: <?php echo get_status_color($order['TrangThai']); ?>20; color: <?php echo get_status_color($order['TrangThai']); ?>; border: 2px solid <?php echo get_status_color($order['TrangThai']); ?>;">
                                <?php echo htmlspecialchars($order['TrangThai']); ?>
                            </div>
                        </div>

                        <div class="order-info">
                            <p>Địa chỉ: <?php echo htmlspecialchars($order['DiaChiGiaoHang']); ?></p>
                            <p>SĐT: <?php echo htmlspecialchars($order['SDTNguoiNhan']); ?> | TT: <?php echo htmlspecialchars($order['PhuongThucThanhToan']); ?></p>
                            <p>Tổng tiền: <strong style="color: <?php echo get_status_color('Đã giao hàng'); ?>;"><?php echo number_format($order['TongCong']); ?>₫</strong></p>
                        </div>
                        
                        <div id="details-<?php echo $order['IdDonHang']; ?>" class="item-details">
                            <p style="font-weight: bold; margin-top: 1rem;">Chi tiết:</p>
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="detail-item">
                                    <span><?php echo htmlspecialchars($item['TenSanPham']); ?></span>
                                    <span><?php echo $item['SoLuong']; ?> x <?php echo number_format($item['Gia']); ?>₫ = **<?php echo number_format($item['ThanhTien']); ?>₫**</span>
                                </div>
                            <?php endforeach; ?>
                            <div class="order-summary-admin">
                                <p>Ghi chú: <?php echo htmlspecialchars($order['GhiChu'] ?: 'Không có ghi chú'); ?></p>
                            </div>
                        </div>

                        <div class="order-actions">
                            <button onclick="toggleOrderDetails(<?php echo $order['IdDonHang']; ?>)" class="btn-detail" style="background-color: #6c757d;">
                                Xem/Ẩn chi tiết
                            </button>
                            
                            <?php if ($order['TrangThai'] == 'Chờ xử lý'): ?>
                                <form method="POST" action="order_update_handler.php" onsubmit="return confirm('Xác nhận duyệt đơn hàng <?php echo $order['MaDonHang']; ?>?');">
                                    <input type="hidden" name="order_id" value="<?php echo $order['IdDonHang']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn-approve">Duyệt (Xác nhận)</button>
                                </form>
                                <form method="POST" action="order_update_handler.php" onsubmit="return confirm('Xác nhận từ chối và HOÀN KHO đơn hàng <?php echo $order['MaDonHang']; ?>?');">
                                    <input type="hidden" name="order_id" value="<?php echo $order['IdDonHang']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn-reject">Từ chối (Hủy)</button>
                                </form>
                            <?php elseif ($order['TrangThai'] == 'Đã xác nhận'): ?>
                                <form method="POST" action="order_update_handler.php" onsubmit="return confirm('Xác nhận chuyển đơn hàng <?php echo $order['MaDonHang']; ?> sang Đang giao hàng?');">
                                    <input type="hidden" name="order_id" value="<?php echo $order['IdDonHang']; ?>">
                                    <input type="hidden" name="action" value="ship">
                                    <button type="submit" class="btn-ship">Chuyển sang Đang giao hàng</button>
                                </form>
                                 <form method="POST" action="order_update_handler.php" onsubmit="return confirm('Xác nhận HOÀN KHO và HỦY đơn hàng <?php echo $order['MaDonHang']; ?>?');">
                                    <input type="hidden" name="order_id" value="<?php echo $order['IdDonHang']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn-reject">Hủy (Hoàn kho)</button>
                                </form>
                            <?php elseif ($order['TrangThai'] == 'Đang giao hàng'): ?>
                                <form method="POST" action="order_update_handler.php" onsubmit="return confirm('Xác nhận đơn hàng <?php echo $order['MaDonHang']; ?> đã hoàn thành (Đã giao hàng)?');">
                                    <input type="hidden" name="order_id" value="<?php echo $order['IdDonHang']; ?>">
                                    <input type="hidden" name="action" value="complete">
                                    <button type="submit" class="btn-complete">Hoàn thành</button>
                                </form>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleOrderDetails(orderId) {
            const detailsDiv = document.getElementById('details-' + orderId);
            detailsDiv.classList.toggle('show');
        }

        // Ẩn alert sau 4 giây (Giống logic trong admin.css)
        window.onload = function() {
            const alert = document.querySelector('.alert');
            if (alert) {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 500); // Đợi hiệu ứng fade out
                }, 4000);
            }
        };
    </script>
</body>
</html>