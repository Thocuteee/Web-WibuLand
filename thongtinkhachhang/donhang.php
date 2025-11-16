<?php
    session_start();
    include '../components/connect.php';
    include '../components/header.php';
    
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login&registration/login.php');
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $order_type = isset($_GET['type']) ? $_GET['type'] : 'normal'; // normal hoặc preorder
    
    // Lấy thông tin user
    $select_user = "SELECT NameUser, EmailUser FROM users WHERE IdUser = ?";
    $stmt_user = mysqli_prepare($conn, $select_user);
    mysqli_stmt_bind_param($stmt_user, "i", $user_id);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);
    $user_info = mysqli_fetch_assoc($result_user);
    mysqli_stmt_close($stmt_user);
    
    // Lấy tên viết tắt cho avatar
    $name_parts = explode(' ', $user_info['NameUser']);
    $initials = '';
    foreach ($name_parts as $part) {
        if (!empty($part)) {
            $initials .= mb_substr($part, 0, 1, 'UTF-8');
            if (mb_strlen($initials, 'UTF-8') >= 2) break;
        }
    }
    if (empty($initials)) {
        $initials = mb_substr($user_info['NameUser'], 0, 2, 'UTF-8');
    }
    $initials = mb_strtoupper($initials, 'UTF-8');
    
    // Lấy danh sách đơn hàng
    $orders = [];
    $select_orders = "SELECT * FROM donhang WHERE IdUser = ? ORDER BY NgayTao DESC";
    $stmt_orders = mysqli_prepare($conn, $select_orders);
    if ($stmt_orders) {
        mysqli_stmt_bind_param($stmt_orders, "i", $user_id);
        mysqli_stmt_execute($stmt_orders);
        $result_orders = mysqli_stmt_get_result($stmt_orders);
        
        while ($order = mysqli_fetch_assoc($result_orders)) {
            // Lấy chi tiết đơn hàng
            $select_details = "SELECT * FROM donhang_chitiet WHERE IdDonHang = ?";
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
        mysqli_stmt_close($stmt_orders);
    }
    
    // Hàm lấy tên sản phẩm từ category
    function get_product_name($conn, $category, $product_id) {
        $query = "SELECT Name FROM `$category` WHERE ID = ?";
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $product_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                mysqli_stmt_close($stmt);
                return $row['Name'];
            }
            mysqli_stmt_close($stmt);
        }
        return 'Sản phẩm không tồn tại';
    }
    
    // Hàm lấy ảnh sản phẩm
    function get_product_image($conn, $category, $product_id) {
        $query = "SELECT Img1 FROM `$category` WHERE ID = ?";
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $product_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                mysqli_stmt_close($stmt);
                return $row['Img1'];
            }
            mysqli_stmt_close($stmt);
        }
        return '';
    }
    
    // Hàm lấy màu trạng thái
    function get_status_color($status) {
        $colors = [
            'Chờ xử lý' => '#ff9800',
            'Đã xác nhận' => '#2196f3',
            'Đang giao hàng' => '#9c27b0',
            'Đã giao hàng' => '#4caf50',
            'Đã hủy' => '#f44336'
        ];
        return $colors[$status] ?? '#666';
    }
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../components/css/global.css">
    <link rel="stylesheet" href="../components/css/header_sidebar_footer.css">
    <link rel="stylesheet" href="css/thongtinkhachhang.css">
    <link rel="stylesheet" href="css/donhang.css">
    <title>Đơn hàng - Wibu Dreamland</title>
</head>
<body>
    <?php include '../components/header.php'; ?>
    
    <main>
        <?php include '../components/sidebar.php'; ?>
        <div class="home-content">
            <div class="content" style="padding-top: 5rem; width: 95%; max-width: 140rem; margin: 0 auto;">
                
                <div class="container">
                    <!-- Sidebar -->
                    <div class="sidebar">
                        <div class="profile">
                            <div class="profile-pic"><?php echo $initials; ?></div>
                            <p><?php echo htmlspecialchars($user_info['NameUser']); ?></p>
                            <p style="font-size: 1.2rem; color: #666; margin-top: 0.5rem;"><?php echo htmlspecialchars($user_info['EmailUser']); ?></p>
                        </div>
                        <nav>
                            <div class="nav-item">
                                <img src="../Home/img/user.png" alt="User Icon" class="icon" />
                                <a href="thongtinkhachhang.php">Thông tin cá nhân</a>
                            </div>
                            <div class="nav-item active">
                                <img src="../Home/img/order.jpg" alt="Box Icon" class="icon" />
                                <a href="donhang.php?type=normal">Đơn hàng thông thường</a>
                            </div>
                            <div class="nav-item">
                                <img src="../Home/img/order.jpg" alt="Pre-order Icon" class="icon" />
                                <a href="donhang.php?type=preorder">Đơn hàng đặt trước/mua hộ</a>
                            </div>
                            <div class="nav-item">
                                <img src="../Home/img/home.png" alt="Home Icon" class="icon" />
                                <a href="voucher.php">Kho voucher</a>
                            </div>
                        </nav>
                    </div>

                    <!-- Main Content -->
                    <div class="main-content">
                        <h2><?php echo $order_type == 'preorder' ? 'Đơn hàng đặt trước/mua hộ' : 'Đơn hàng thông thường'; ?></h2>
                        
                        <?php if (empty($orders)): ?>
                            <div class="empty-state">
                                <div class="empty-icon">📦</div>
                                <h3>Chưa có đơn hàng nào</h3>
                                <p>Bạn chưa có đơn hàng <?php echo $order_type == 'preorder' ? 'đặt trước/mua hộ' : 'thông thường'; ?> nào.</p>
                                <a href="../Home/index.php" class="btn-shopping">Tiếp tục mua sắm</a>
                            </div>
                        <?php else: ?>
                            <div class="orders-list">
                                <?php foreach ($orders as $order): ?>
                                    <div class="order-card">
                                        <div class="order-header">
                                            <div class="order-info">
                                                <h3>Mã đơn hàng: <span class="order-code"><?php echo htmlspecialchars($order['MaDonHang']); ?></span></h3>
                                                <p class="order-date">Ngày đặt: <?php echo date('d/m/Y H:i', strtotime($order['NgayTao'])); ?></p>
                                            </div>
                                            <div class="order-status" style="background-color: <?php echo get_status_color($order['TrangThai']); ?>20; color: <?php echo get_status_color($order['TrangThai']); ?>; border: 2px solid <?php echo get_status_color($order['TrangThai']); ?>;">
                                                <?php echo htmlspecialchars($order['TrangThai']); ?>
                                            </div>
                                        </div>
                                        
                                        <div class="order-items">
                                            <?php foreach ($order['items'] as $item): 
                                                // Lấy ảnh sản phẩm từ database
                                                $product_image = get_product_image($conn, $item['LoaiSanPham'], $item['IdSanPham']);
                                            ?>
                                                <div class="order-item">
                                                    <img src="/admin/<?php echo htmlspecialchars($product_image ? $product_image : 'logo_1.png'); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['TenSanPham']); ?>"
                                                         onerror="this.src='../Home/img/logo_1.png'">
                                                    <div class="item-info">
                                                        <h4><?php echo htmlspecialchars($item['TenSanPham']); ?></h4>
                                                        <p>Số lượng: <?php echo $item['SoLuong']; ?> x <?php echo number_format($item['Gia']); ?>₫</p>
                                                    </div>
                                                    <div class="item-total">
                                                        <?php echo number_format($item['ThanhTien']); ?>₫
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <div class="order-summary">
                                            <div class="summary-row">
                                                <span>Tổng tiền sản phẩm:</span>
                                                <span><?php echo number_format($order['TongTien']); ?>₫</span>
                                            </div>
                                            <div class="summary-row">
                                                <span>Phí vận chuyển:</span>
                                                <span><?php echo number_format($order['PhiVanChuyen']); ?>₫</span>
                                            </div>
                                            <?php if ($order['GiamGia'] > 0): ?>
                                            <div class="summary-row discount">
                                                <span>Giảm giá:</span>
                                                <span>-<?php echo number_format($order['GiamGia']); ?>₫</span>
                                            </div>
                                            <?php endif; ?>
                                            <div class="summary-row total">
                                                <span>Tổng cộng:</span>
                                                <span><?php echo number_format($order['TongCong']); ?>₫</span>
                                            </div>
                                        </div>
                                        
                                        <div class="order-actions">
                                            <button class="btn-detail" onclick="toggleOrderDetail(<?php echo $order['IdDonHang']; ?>)">
                                                Xem chi tiết
                                            </button>
                                            <?php if ($order['TrangThai'] == 'Chờ xử lý'): ?>
                                            <button class="btn-cancel" onclick="cancelOrder(<?php echo $order['IdDonHang']; ?>)">
                                                Hủy đơn
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Chi tiết đơn hàng (ẩn mặc định) -->
                                        <div class="order-detail" id="detail-<?php echo $order['IdDonHang']; ?>" style="display: none;">
                                            <h4>Thông tin giao hàng</h4>
                                            <div class="detail-info">
                                                <p><strong>Người nhận:</strong> <?php echo htmlspecialchars($order['TenNguoiNhan']); ?></p>
                                                <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['SDTNguoiNhan']); ?></p>
                                                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['DiaChiGiaoHang']); ?></p>
                                                <p><strong>Tỉnh thành:</strong> <?php echo htmlspecialchars($order['TinhThanh'] ?? 'N/A'); ?></p>
                                                <p><strong>Phương thức thanh toán:</strong> <?php echo htmlspecialchars($order['PhuongThucThanhToan']); ?></p>
                                                <?php if (!empty($order['GhiChu'])): ?>
                                                <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['GhiChu']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include "../components/footer.php"; ?>
    
    <script src="../components/js/global.js" defer></script>
    <script>
        function toggleOrderDetail(orderId) {
            const detail = document.getElementById('detail-' + orderId);
            if (detail.style.display === 'none') {
                detail.style.display = 'block';
            } else {
                detail.style.display = 'none';
            }
        }
        
        function cancelOrder(orderId) {
            if (confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
                // TODO: Gửi request hủy đơn hàng
                alert('Tính năng hủy đơn hàng đang được phát triển');
            }
        }
    </script>
</body>
</html>

