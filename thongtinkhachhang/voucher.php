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
    
    // Lấy voucher của user
    $user_vouchers = [];
    $select_vouchers = "SELECT uv.*, v.TenVoucher, v.MoTa, v.PhanTramGiam, v.GiaTriGiamToiDa, 
                               v.GiaTriDonHangToiThieu, v.NgayBatDau, v.NgayKetThuc, v.TrangThai as VoucherStatus
                        FROM user_voucher uv
                        INNER JOIN voucher v ON uv.IdVoucher = v.IdVoucher
                        WHERE uv.IdUser = ? AND uv.DaSuDung = 0
                        ORDER BY uv.NgayNhan DESC";
    $stmt_vouchers = mysqli_prepare($conn, $select_vouchers);
    if ($stmt_vouchers) {
        mysqli_stmt_bind_param($stmt_vouchers, "i", $user_id);
        mysqli_stmt_execute($stmt_vouchers);
        $result_vouchers = mysqli_stmt_get_result($stmt_vouchers);
        
        while ($voucher = mysqli_fetch_assoc($result_vouchers)) {
            // Kiểm tra voucher còn hiệu lực không
            $today = date('Y-m-d');
            $voucher['ConHieuLuc'] = ($voucher['NgayKetThuc'] >= $today && $voucher['VoucherStatus'] == 1);
            $user_vouchers[] = $voucher;
        }
        mysqli_stmt_close($stmt_vouchers);
    }
    
    // Lấy voucher đã sử dụng
    $used_vouchers = [];
    $select_used = "SELECT uv.*, v.TenVoucher, v.MoTa, v.PhanTramGiam, uv.NgaySuDung
                    FROM user_voucher uv
                    INNER JOIN voucher v ON uv.IdVoucher = v.IdVoucher
                    WHERE uv.IdUser = ? AND uv.DaSuDung = 1
                    ORDER BY uv.NgaySuDung DESC";
    $stmt_used = mysqli_prepare($conn, $select_used);
    if ($stmt_used) {
        mysqli_stmt_bind_param($stmt_used, "i", $user_id);
        mysqli_stmt_execute($stmt_used);
        $result_used = mysqli_stmt_get_result($stmt_used);
        
        while ($voucher = mysqli_fetch_assoc($result_used)) {
            $used_vouchers[] = $voucher;
        }
        mysqli_stmt_close($stmt_used);
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
    <link rel="stylesheet" href="css/voucher.css">
    <title>Kho voucher - Wibu Dreamland</title>
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
                            <div class="nav-item">
                                <img src="../Home/img/order.jpg" alt="Box Icon" class="icon" />
                                <a href="donhang.php?type=normal">Đơn hàng thông thường</a>
                            </div>
                            <div class="nav-item">
                                <img src="../Home/img/order.jpg" alt="Pre-order Icon" class="icon" />
                                <a href="donhang.php?type=preorder">Đơn hàng đặt trước/mua hộ</a>
                            </div>
                            <div class="nav-item active">
                                <img src="../Home/img/home.png" alt="Home Icon" class="icon" />
                                <a href="voucher.php">Kho voucher</a>
                            </div>
                        </nav>
                    </div>

                    <!-- Main Content -->
                    <div class="main-content">
                        <h2>Kho voucher của tôi</h2>
                        
                        <!-- Tab navigation -->
                        <div class="voucher-tabs">
                            <button class="tab-btn active" onclick="showTab('available')">Voucher có sẵn (<?php echo count($user_vouchers); ?>)</button>
                            <button class="tab-btn" onclick="showTab('used')">Đã sử dụng (<?php echo count($used_vouchers); ?>)</button>
                        </div>
                        
                        <!-- Voucher có sẵn -->
                        <div id="tab-available" class="tab-content active">
                            <?php if (empty($user_vouchers)): ?>
                                <div class="empty-state">
                                    <div class="empty-icon">🎫</div>
                                    <h3>Chưa có voucher nào</h3>
                                    <p>Bạn chưa có voucher nào trong kho.</p>
                                </div>
                            <?php else: ?>
                                <div class="vouchers-grid">
                                    <?php foreach ($user_vouchers as $voucher): ?>
                                        <div class="voucher-card <?php echo $voucher['ConHieuLuc'] ? 'active' : 'expired'; ?>">
                                            <div class="voucher-header">
                                                <div class="voucher-discount">
                                                    <span class="discount-percent"><?php echo $voucher['PhanTramGiam']; ?>%</span>
                                                    <span class="discount-label">GIẢM</span>
                                                </div>
                                                <?php if (!$voucher['ConHieuLuc']): ?>
                                                <div class="expired-badge">Hết hạn</div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="voucher-body">
                                                <h3><?php echo htmlspecialchars($voucher['TenVoucher']); ?></h3>
                                                <p class="voucher-description"><?php echo htmlspecialchars($voucher['MoTa']); ?></p>
                                                <div class="voucher-details">
                                                    <?php if ($voucher['GiaTriGiamToiDa']): ?>
                                                    <p><i class="fa-solid fa-tag"></i> Giảm tối đa: <?php echo number_format($voucher['GiaTriGiamToiDa']); ?>₫</p>
                                                    <?php endif; ?>
                                                    <p><i class="fa-solid fa-cart-shopping"></i> Đơn tối thiểu: <?php echo number_format($voucher['GiaTriDonHangToiThieu']); ?>₫</p>
                                                    <p><i class="fa-solid fa-calendar"></i> HSD: <?php echo date('d/m/Y', strtotime($voucher['NgayKetThuc'])); ?></p>
                                                </div>
                                                <div class="voucher-code">
                                                    <span>Mã: <strong><?php echo htmlspecialchars($voucher['MaVoucher']); ?></strong></span>
                                                    <button class="btn-copy" onclick="copyVoucherCode('<?php echo htmlspecialchars($voucher['MaVoucher']); ?>')">
                                                        <i class="fa-solid fa-copy"></i> Copy
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Voucher đã sử dụng -->
                        <div id="tab-used" class="tab-content">
                            <?php if (empty($used_vouchers)): ?>
                                <div class="empty-state">
                                    <div class="empty-icon">✅</div>
                                    <h3>Chưa có voucher đã sử dụng</h3>
                                    <p>Bạn chưa sử dụng voucher nào.</p>
                                </div>
                            <?php else: ?>
                                <div class="vouchers-grid">
                                    <?php foreach ($used_vouchers as $voucher): ?>
                                        <div class="voucher-card used">
                                            <div class="voucher-header">
                                                <div class="voucher-discount">
                                                    <span class="discount-percent"><?php echo $voucher['PhanTramGiam']; ?>%</span>
                                                    <span class="discount-label">GIẢM</span>
                                                </div>
                                                <div class="used-badge">Đã sử dụng</div>
                                            </div>
                                            <div class="voucher-body">
                                                <h3><?php echo htmlspecialchars($voucher['TenVoucher']); ?></h3>
                                                <p class="voucher-description"><?php echo htmlspecialchars($voucher['MoTa']); ?></p>
                                                <div class="voucher-details">
                                                    <p><i class="fa-solid fa-calendar-check"></i> Đã dùng: <?php echo date('d/m/Y H:i', strtotime($voucher['NgaySuDung'])); ?></p>
                                                </div>
                                                <div class="voucher-code">
                                                    <span>Mã: <strong><?php echo htmlspecialchars($voucher['MaVoucher']); ?></strong></span>
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
        </div>
    </main>
    
    <?php include "../components/footer.php"; ?>
    
    <script src="../components/js/global.js" defer></script>
    <script src="https://kit.fontawesome.com/eff669a9ab.js" crossorigin="anonymous"></script>
    <script>
        function showTab(tabName) {
            // Ẩn tất cả tab content
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Ẩn tất cả tab buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Hiển thị tab được chọn
            document.getElementById('tab-' + tabName).classList.add('active');
            event.target.classList.add('active');
        }
        
        function copyVoucherCode(code) {
            navigator.clipboard.writeText(code).then(function() {
                alert('Đã copy mã voucher: ' + code);
            }, function() {
                // Fallback cho trình duyệt cũ
                const textarea = document.createElement('textarea');
                textarea.value = code;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Đã copy mã voucher: ' + code);
            });
        }
    </script>
</body>
</html>

