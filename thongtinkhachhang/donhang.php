<?php
    // Bắt đầu output buffering để chặn output không mong muốn
    ob_start();
    
    session_start();
    include '../components/connect.php';
    
    // API endpoint để kiểm tra thanh toán (cho AJAX) - PHẢI XỬ LÝ TRƯỚC KHI INCLUDE HEADER
    if (isset($_GET['check_payment']) && !empty($_GET['check_payment'])) {
        // Xóa tất cả output buffer
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'error',
                'paid' => false,
                'message' => 'Bạn cần đăng nhập'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        $check_order_id = (int)$_GET['check_payment'];
        $user_id = $_SESSION['user_id'];
        
        $check_query = "SELECT TrangThai, TongCong, PhuongThucThanhToan, MaDonHang FROM donhang WHERE IdDonHang = ? AND IdUser = ?";
        $stmt_check = mysqli_prepare($conn, $check_query);
        
        if (!$stmt_check) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'error',
                'paid' => false,
                'message' => 'Lỗi kết nối database'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        mysqli_stmt_bind_param($stmt_check, "ii", $check_order_id, $user_id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        $order_check = mysqli_fetch_assoc($result_check);
        mysqli_stmt_close($stmt_check);
        
        // Kiểm tra thanh toán thành công: Trạng thái "Đã xác nhận" VÀ TongCong = 0
        // (TongCong = 0 nghĩa là đã thanh toán xong, không còn nợ)
        $is_paid = false;
        if ($order_check) {
            $is_paid = ($order_check['TrangThai'] == 'Đã xác nhận') && ($order_check['TongCong'] == 0);
        }
        
        header('Content-Type: application/json; charset=utf-8');
        if ($is_paid) {
            echo json_encode([
                'status' => 'success', 
                'paid' => true,
                'message' => 'Thanh toán thành công! Đơn hàng ' . ($order_check['MaDonHang'] ?? '') . ' đã được xác nhận và số tiền đã được cập nhật.',
                'order_code' => $order_check['MaDonHang'] ?? ''
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'status' => 'pending', 
                'paid' => false,
                'order_status' => $order_check['TrangThai'] ?? 'Chưa xác định',
                'remaining_amount' => (int)($order_check['TongCong'] ?? 0),
                'message' => 'Đang chờ thanh toán. Trạng thái: ' . ($order_check['TrangThai'] ?? 'Chưa xác định') . ', Số tiền còn lại: ' . number_format($order_check['TongCong'] ?? 0) . '₫'
            ], JSON_UNESCAPED_UNICODE);
        }
        exit();
    }
    
    // Nếu không phải API call, tiếp tục xử lý bình thường
    include '../components/header.php';
    
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login&registration/login.php');
        exit();
    }
    
    // Kiểm tra thanh toán thành công từ VNPay (nếu có)
    if (isset($_SESSION['vnpay_payment_success']) && $_SESSION['vnpay_payment_success']) {
        $success_order_id = $_SESSION['vnpay_order_id'] ?? null;
        unset($_SESSION['vnpay_payment_success']);
        unset($_SESSION['vnpay_order_id']);
        
        // Có thể hiển thị thông báo thành công ở đây nếu cần
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
                                            <button class="btn-cancel" onclick="cancelOrder(<?php echo $order['IdDonHang']; ?>, this)">
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
        
        function cancelOrder(orderId, buttonElement) {
            if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này?\n\n⚠️ Lưu ý:\n- Đơn hàng sau khi hủy không thể khôi phục\n- Kho sản phẩm sẽ được hoàn trả tự động')) {
                return;
            }
            
            // Hiển thị loading
            const cancelButton = buttonElement || event.target;
            const originalText = cancelButton.innerHTML;
            const orderCard = cancelButton.closest('.order-card');
            
            cancelButton.disabled = true;
            cancelButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xử lý...';
            
            // Gửi AJAX request
            fetch('../components/order_cancel_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'order_id=' + encodeURIComponent(orderId)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Lỗi phản hồi HTTP: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    // Hiển thị thông báo thành công
                    alert('✅ ' + (data.message || 'Đơn hàng đã được hủy thành công!'));
                    // Reload trang để cập nhật trạng thái
                    window.location.reload();
                } else {
                    // Hiển thị thông báo lỗi
                    alert('❌ ' + (data.message || 'Không thể hủy đơn hàng. Vui lòng thử lại.'));
                    cancelButton.disabled = false;
                    cancelButton.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Lỗi khi hủy đơn hàng:', error);
                alert('❌ Đã xảy ra lỗi khi hủy đơn hàng. Vui lòng thử lại hoặc làm mới trang.\n\nNếu vấn đề vẫn tiếp tục, vui lòng liên hệ hỗ trợ.');
                cancelButton.disabled = false;
                cancelButton.innerHTML = originalText;
            });
        }
    </script>
</body>
</html>

