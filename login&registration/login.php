<?php
include '../components/connect.php';


if (isset($_POST['submit'])) { // Kiểm tra xem form đăng nhập đã được gửi chưa

    // Lấy dữ liệu và loại bỏ các ký tự không cần thiết (trim, strip_tags)
    // Mặc dù Prepared Statements giúp ngăn SQL Injection, việc làm sạch dữ liệu vẫn là một thói quen tốt.
    $email = trim(strip_tags($_POST['email']));
    $password = $_POST['password']; 
    
    // BƯỚC 1: SỬ DỤNG PREPARED STATEMENT ĐỂ TRUY VẤN NGƯỜI DÙNG CHỈ BẰNG EMAIL (CHỐNG SQL INJECTION)
    $select_query = "SELECT * FROM `users` WHERE EmailUser = ?";
    
    // Chuẩn bị truy vấn
    $stmt = mysqli_prepare($conn, $select_query);
    
    // Gắn tham số email vào truy vấn
    // "s" là kiểu string (chuỗi)
    mysqli_stmt_bind_param($stmt, "s", $email);
    
    // Thực thi truy vấn
    mysqli_stmt_execute($stmt);
    
    // Lấy kết quả
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) { // Kiểm tra nếu tìm thấy email người dùng
        $row = mysqli_fetch_array($result); // Lấy dữ liệu của bản ghi tìm được
        $hashed_password = $row["PasswordUser"]; // Lấy mật khẩu đã băm từ DB

        // BƯỚC 2: DÙNG PHP ĐỂ KIỂM TRA MẬT KHẨU
        if (password_verify($password, $hashed_password)) {
            // Mật khẩu khớp -> Đăng nhập thành công
            $_SESSION["user_id"] = $row["IdUser"]; // Lưu ID người dùng vào session

            // Kiểm tra redirect sau khi đăng nhập
            $redirect_url = '../Home/index.php'; // Mặc định về trang chủ
            if (isset($_SESSION['redirect_after_login']) && !empty($_SESSION['redirect_after_login'])) {
                $redirect_url = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
            } elseif (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
                $redirect_url = urldecode($_GET['redirect']);
            }

            if ($row["role"] == "0") {
                header("Location: ../admin/admin.php");
            } else if ($row["role"] == "1") {
                header("Location: " . $redirect_url); // Chuyển hướng đến trang được yêu cầu hoặc trang chủ
            }
            // Giải phóng statement
            mysqli_stmt_close($stmt); 
            exit(); // Quan trọng: Kết thúc script sau khi chuyển hướng
        } else {
            // Mật khẩu KHÔNG khớp
            $message[] = "Email đăng nhập hoặc mật khẩu không chính xác!";
        }
    } else {
        // Không tìm thấy email
        $message[] = "Email đăng nhập hoặc mật khẩu không chính xác!";
    }
    
    // Đóng statement nếu nó vẫn đang mở
    if (isset($stmt) && $stmt) {
        mysqli_stmt_close($stmt);
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- GOOGLE FONT -->
    <!-- Kiểu chữ Noto San, Paytone -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Paytone+One&display=swap"
        rel="stylesheet">
    <!-- FONT AWESOME -->
    <script src="https://kit.fontawesome.com/eff669a9ab.js" crossorigin="anonymous"></script>
    <!-- Css -->
    <link rel="stylesheet" href="css/login&registration.css">
    <title>Đăng kí</title>
    <script src="/login&registration/js/Password.js" defer></script>
</head>

<body>
<?php
    if (isset($message)) {
      echo '<div class="alert">' . $message[0] . '</div>';
    }
  ?>
    <div class="main-login">
        <div class="wrapper">
            <div class="login-box">
                <form action="" method="POST">
                    <h2>Đăng nhập</h2>
                    <div class="input-box">
                        <i class="fa-solid fa-envelope"></i>
                        <div class="input">
                            <input style="font-size:1.5rem" type="email" name="email" placeholder="Email" required>
                        </div> <!-- required có mục đích để kiểm tra người dùng có nhập thông tin đúng kiểu input không -->
                    </div>

                    <div class="input-box">
                        <i class="fa-solid fa-lock"></i>
                        <input style="margin-left: 3.5rem; font-size:1.5rem" type="password" id="password" name="password" placeholder="Mật khẩu" required>
                        <span class="checkbox-container">
                            <input type="checkbox" id="showPassword" onclick="togglePassword()" />
                            <label for="showPassword" class="checkbox-label"></label>
                        </span>
                    </div>

                    <div class="forgot-pass">
                        <a href="#">Bạn quên mật khẩu?</a>
                    </div>

                    <div class="button">
                        <button type="submit" name="submit" >Đăng nhập</button>
                    </div>
                    <div class="sign-link">
                        <span>Chưa có tài khoản?</span>
                        <a href="registration.php">Đăng kí</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>