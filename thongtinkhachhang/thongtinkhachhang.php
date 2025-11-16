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
    $message = '';
    $message_type = '';
    
    // Xử lý cập nhật thông tin
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
        $name = mysqli_real_escape_string($conn, trim($_POST['name']));
        $gender = mysqli_real_escape_string($conn, trim($_POST['gender'] ?? ''));
        $address = mysqli_real_escape_string($conn, trim($_POST['address'] ?? ''));
        $country = mysqli_real_escape_string($conn, trim($_POST['country'] ?? 'Việt Nam'));
        $province = mysqli_real_escape_string($conn, trim($_POST['province'] ?? ''));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
        $birthday = mysqli_real_escape_string($conn, trim($_POST['birthday'] ?? ''));
        
        // Validation
        if (empty($name) || empty($email) || empty($phone)) {
            $message = 'Vui lòng điền đầy đủ các trường bắt buộc!';
            $message_type = 'error';
        } else {
            // Kiểm tra email đã tồn tại chưa (trừ user hiện tại)
            $check_email = "SELECT IdUser FROM users WHERE EmailUser = ? AND IdUser != ?";
            $stmt_check = mysqli_prepare($conn, $check_email);
            mysqli_stmt_bind_param($stmt_check, "si", $email, $user_id);
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);
            
            if (mysqli_num_rows($result_check) > 0) {
                $message = 'Email này đã được sử dụng bởi tài khoản khác!';
                $message_type = 'error';
            } else {
                // Cập nhật thông tin
                $update_query = "UPDATE users SET 
                    NameUser = ?, 
                    EmailUser = ?, 
                    SDT = ?, 
                    GioiTinh = ?, 
                    DiaChi = ?, 
                    QuocGia = ?, 
                    TinhThanh = ?, 
                    NgaySinh = ? 
                    WHERE IdUser = ?";
                
                $stmt = mysqli_prepare($conn, $update_query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, "ssssssssi", $name, $email, $phone, $gender, $address, $country, $province, $birthday, $user_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $message = 'Cập nhật thông tin thành công!';
                        $message_type = 'success';
                    } else {
                        $message = 'Lỗi khi cập nhật thông tin: ' . mysqli_error($conn);
                        $message_type = 'error';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $message = 'Lỗi khi chuẩn bị câu lệnh SQL!';
                    $message_type = 'error';
                }
            }
            mysqli_stmt_close($stmt_check);
        }
    }
    
    // Lấy thông tin user hiện tại
    $select_user = "SELECT * FROM users WHERE IdUser = ?";
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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../components/css/global.css">
    <link rel="stylesheet" href="../components/css/header_sidebar_footer.css">
    <link rel="stylesheet" href="css/thongtinkhachhang.css">
    <title>Thông tin tài khoản - Wibu Dreamland</title>
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
                            <div class="nav-item active">
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
                            <div class="nav-item">
                                <img src="../Home/img/home.png" alt="Home Icon" class="icon" />
                                <a href="voucher.php">Kho voucher</a>
                            </div>
                        </nav>
                    </div>

                    <!-- Main Content -->
                    <div class="main-content">
                        <h2>Thông tin tài khoản</h2>
                        
                        <?php if ($message): ?>
                            <div class="message <?php echo $message_type; ?>">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <table>
                                <tr>
                                    <td><label>Họ và tên *</label></td>
                                    <td>
                                        <input type="text" name="name" placeholder="Nhập họ và tên" 
                                               value="<?php echo htmlspecialchars($user_info['NameUser'] ?? ''); ?>" required />
                                    </td>
                                </tr>

                                <tr>
                                    <td><label>Giới tính</label></td>
                                    <td>
                                        <div class="gender">
                                            <label>
                                                <input type="radio" name="gender" value="Nam" 
                                                       <?php echo (isset($user_info['GioiTinh']) && $user_info['GioiTinh'] == 'Nam') ? 'checked' : ''; ?> />
                                                Nam
                                            </label>
                                            <label>
                                                <input type="radio" name="gender" value="Nữ" 
                                                       <?php echo (isset($user_info['GioiTinh']) && $user_info['GioiTinh'] == 'Nữ') ? 'checked' : ''; ?> />
                                                Nữ
                                            </label>
                                            <label>
                                                <input type="radio" name="gender" value="Khác" 
                                                       <?php echo (!isset($user_info['GioiTinh']) || ($user_info['GioiTinh'] != 'Nam' && $user_info['GioiTinh'] != 'Nữ')) ? 'checked' : ''; ?> />
                                                Khác
                                            </label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td><label>Địa chỉ</label></td>
                                    <td>
                                        <input type="text" name="address" placeholder="Nhập địa chỉ" 
                                               value="<?php echo htmlspecialchars($user_info['DiaChi'] ?? ''); ?>" />
                                    </td>
                                </tr>

                                <tr>
                                    <td><label>Quốc gia</label></td>
                                    <td>
                                        <select name="country">
                                            <option value="Việt Nam" <?php echo (!isset($user_info['QuocGia']) || $user_info['QuocGia'] == 'Việt Nam') ? 'selected' : ''; ?>>Việt Nam</option>
                                            <option value="Khác" <?php echo (isset($user_info['QuocGia']) && $user_info['QuocGia'] != 'Việt Nam') ? 'selected' : ''; ?>>Khác</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td><label>Tỉnh thành</label></td>
                                    <td>
                                        <select name="province">
                                            <option value="">Chọn tỉnh thành</option>
                                            <option value="TP Hồ Chí Minh" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'TP Hồ Chí Minh') ? 'selected' : ''; ?>>TP Hồ Chí Minh</option>
                                            <option value="Hà Nội" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Hà Nội') ? 'selected' : ''; ?>>Hà Nội</option>
                                            <option value="Đà Nẵng" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Đà Nẵng') ? 'selected' : ''; ?>>Đà Nẵng</option>
                                            <option value="Cần Thơ" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Cần Thơ') ? 'selected' : ''; ?>>Cần Thơ</option>
                                            <option value="An Giang" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'An Giang') ? 'selected' : ''; ?>>An Giang</option>
                                            <option value="Bà Rịa - Vũng Tàu" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Bà Rịa - Vũng Tàu') ? 'selected' : ''; ?>>Bà Rịa - Vũng Tàu</option>
                                            <option value="Bắc Giang" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Bắc Giang') ? 'selected' : ''; ?>>Bắc Giang</option>
                                            <option value="Bắc Kạn" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Bắc Kạn') ? 'selected' : ''; ?>>Bắc Kạn</option>
                                            <option value="Bạc Liêu" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Bạc Liêu') ? 'selected' : ''; ?>>Bạc Liêu</option>
                                            <option value="Bắc Ninh" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Bắc Ninh') ? 'selected' : ''; ?>>Bắc Ninh</option>
                                            <option value="Bến Tre" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Bến Tre') ? 'selected' : ''; ?>>Bến Tre</option>
                                            <option value="Bình Định" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Bình Định') ? 'selected' : ''; ?>>Bình Định</option>
                                            <option value="Bình Dương" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Bình Dương') ? 'selected' : ''; ?>>Bình Dương</option>
                                            <option value="Bình Phước" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Bình Phước') ? 'selected' : ''; ?>>Bình Phước</option>
                                            <option value="Bình Thuận" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Bình Thuận') ? 'selected' : ''; ?>>Bình Thuận</option>
                                            <option value="Cà Mau" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Cà Mau') ? 'selected' : ''; ?>>Cà Mau</option>
                                            <option value="Cao Bằng" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Cao Bằng') ? 'selected' : ''; ?>>Cao Bằng</option>
                                            <option value="Đắk Lắk" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Đắk Lắk') ? 'selected' : ''; ?>>Đắk Lắk</option>
                                            <option value="Đắk Nông" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Đắk Nông') ? 'selected' : ''; ?>>Đắk Nông</option>
                                            <option value="Điện Biên" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Điện Biên') ? 'selected' : ''; ?>>Điện Biên</option>
                                            <option value="Đồng Nai" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Đồng Nai') ? 'selected' : ''; ?>>Đồng Nai</option>
                                            <option value="Đồng Tháp" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Đồng Tháp') ? 'selected' : ''; ?>>Đồng Tháp</option>
                                            <option value="Gia Lai" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Gia Lai') ? 'selected' : ''; ?>>Gia Lai</option>
                                            <option value="Hà Giang" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Hà Giang') ? 'selected' : ''; ?>>Hà Giang</option>
                                            <option value="Hà Nam" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Hà Nam') ? 'selected' : ''; ?>>Hà Nam</option>
                                            <option value="Hà Tĩnh" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Hà Tĩnh') ? 'selected' : ''; ?>>Hà Tĩnh</option>
                                            <option value="Hải Dương" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Hải Dương') ? 'selected' : ''; ?>>Hải Dương</option>
                                            <option value="Hải Phòng" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Hải Phòng') ? 'selected' : ''; ?>>Hải Phòng</option>
                                            <option value="Hậu Giang" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Hậu Giang') ? 'selected' : ''; ?>>Hậu Giang</option>
                                            <option value="Hòa Bình" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Hòa Bình') ? 'selected' : ''; ?>>Hòa Bình</option>
                                            <option value="Hưng Yên" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Hưng Yên') ? 'selected' : ''; ?>>Hưng Yên</option>
                                            <option value="Khánh Hòa" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Khánh Hòa') ? 'selected' : ''; ?>>Khánh Hòa</option>
                                            <option value="Kiên Giang" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Kiên Giang') ? 'selected' : ''; ?>>Kiên Giang</option>
                                            <option value="Kon Tum" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Kon Tum') ? 'selected' : ''; ?>>Kon Tum</option>
                                            <option value="Lai Châu" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Lai Châu') ? 'selected' : ''; ?>>Lai Châu</option>
                                            <option value="Lâm Đồng" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Lâm Đồng') ? 'selected' : ''; ?>>Lâm Đồng</option>
                                            <option value="Lạng Sơn" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Lạng Sơn') ? 'selected' : ''; ?>>Lạng Sơn</option>
                                            <option value="Lào Cai" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Lào Cai') ? 'selected' : ''; ?>>Lào Cai</option>
                                            <option value="Long An" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Long An') ? 'selected' : ''; ?>>Long An</option>
                                            <option value="Nam Định" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Nam Định') ? 'selected' : ''; ?>>Nam Định</option>
                                            <option value="Nghệ An" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Nghệ An') ? 'selected' : ''; ?>>Nghệ An</option>
                                            <option value="Ninh Bình" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Ninh Bình') ? 'selected' : ''; ?>>Ninh Bình</option>
                                            <option value="Ninh Thuận" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Ninh Thuận') ? 'selected' : ''; ?>>Ninh Thuận</option>
                                            <option value="Phú Thọ" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Phú Thọ') ? 'selected' : ''; ?>>Phú Thọ</option>
                                            <option value="Phú Yên" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Phú Yên') ? 'selected' : ''; ?>>Phú Yên</option>
                                            <option value="Quảng Bình" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Quảng Bình') ? 'selected' : ''; ?>>Quảng Bình</option>
                                            <option value="Quảng Nam" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Quảng Nam') ? 'selected' : ''; ?>>Quảng Nam</option>
                                            <option value="Quảng Ngãi" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Quảng Ngãi') ? 'selected' : ''; ?>>Quảng Ngãi</option>
                                            <option value="Quảng Ninh" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Quảng Ninh') ? 'selected' : ''; ?>>Quảng Ninh</option>
                                            <option value="Quảng Trị" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Quảng Trị') ? 'selected' : ''; ?>>Quảng Trị</option>
                                            <option value="Sóc Trăng" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Sóc Trăng') ? 'selected' : ''; ?>>Sóc Trăng</option>
                                            <option value="Sơn La" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Sơn La') ? 'selected' : ''; ?>>Sơn La</option>
                                            <option value="Tây Ninh" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Tây Ninh') ? 'selected' : ''; ?>>Tây Ninh</option>
                                            <option value="Thái Bình" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Thái Bình') ? 'selected' : ''; ?>>Thái Bình</option>
                                            <option value="Thái Nguyên" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Thái Nguyên') ? 'selected' : ''; ?>>Thái Nguyên</option>
                                            <option value="Thanh Hóa" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Thanh Hóa') ? 'selected' : ''; ?>>Thanh Hóa</option>
                                            <option value="Thừa Thiên Huế" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Thừa Thiên Huế') ? 'selected' : ''; ?>>Thừa Thiên Huế</option>
                                            <option value="Tiền Giang" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Tiền Giang') ? 'selected' : ''; ?>>Tiền Giang</option>
                                            <option value="Trà Vinh" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Trà Vinh') ? 'selected' : ''; ?>>Trà Vinh</option>
                                            <option value="Tuyên Quang" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Tuyên Quang') ? 'selected' : ''; ?>>Tuyên Quang</option>
                                            <option value="Vĩnh Long" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Vĩnh Long') ? 'selected' : ''; ?>>Vĩnh Long</option>
                                            <option value="Vĩnh Phúc" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Vĩnh Phúc') ? 'selected' : ''; ?>>Vĩnh Phúc</option>
                                            <option value="Yên Bái" <?php echo (isset($user_info['TinhThanh']) && $user_info['TinhThanh'] == 'Yên Bái') ? 'selected' : ''; ?>>Yên Bái</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td><label>Email *</label></td>
                                    <td>
                                        <input type="email" name="email" placeholder="Nhập email" 
                                               value="<?php echo htmlspecialchars($user_info['EmailUser'] ?? ''); ?>" required />
                                    </td>
                                </tr>

                                <tr>
                                    <td><label>Số điện thoại *</label></td>
                                    <td>
                                        <input type="tel" name="phone" placeholder="Nhập số điện thoại" 
                                               value="<?php echo htmlspecialchars($user_info['SDT'] ?? ''); ?>" required />
                                    </td>
                                </tr>

                                <tr>
                                    <td><label>Ngày sinh</label></td>
                                    <td>
                                        <input type="date" name="birthday" 
                                               value="<?php echo isset($user_info['NgaySinh']) && $user_info['NgaySinh'] != '0000-00-00' ? $user_info['NgaySinh'] : ''; ?>" />
                                    </td>
                                </tr>
                            </table>

                            <button type="submit" name="update_profile">CẬP NHẬT</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include "../components/footer.php"; ?>
    
    <script src="../components/js/global.js" defer></script>
    <script>
        // Thêm class selected cho label khi radio được chọn (fallback cho trình duyệt cũ)
        document.querySelectorAll('.gender input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.gender label').forEach(label => {
                    label.classList.remove('selected');
                });
                if (this.checked) {
                    this.closest('label').classList.add('selected');
                }
            });
            
            if (radio.checked) {
                radio.closest('label').classList.add('selected');
            }
        });
        
        // Style cho label.selected
        const style = document.createElement('style');
        style.textContent = '.gender label.selected { border-color: var(--yellow-color) !important; background-color: #fff9e6 !important; color: var(--yellow-color) !important; font-weight: 600 !important; }';
        document.head.appendChild(style);
    </script>
</body>
</html>
