<?php
include '../components/connect.php';


if (isset($_POST['submit'])) { // Kiểm tra xem form đăng nhập đã được gửi chưa

    $email = $_POST['email']; // Lấy dữ liệu email từ form
    $password =        $_POST['password']; // Lấy dữ liệu mật khẩu từ form 
    // Thực hiện câu truy vấn để kiểm tra người dùng với email và mật khẩu đã nhập
    $select_user = mysqli_query($conn, "SELECT * FROM `users` WHERE EmailUser = '$email' AND PasswordUser = '$password'");

    if (mysqli_num_rows($select_user) > 0) { // Kiểm tra nếu có bản ghi nào khớp với thông tin đăng nhập
        $row = mysqli_fetch_array($select_user); // Lấy dữ liệu của bản ghi tìm được

        $_SESSION["user_id"] = $row["IdUser"]; // Lưu ID người dùng vào session để sử dụng cho các thao tác sau

        if ($row["role"] == "0") {
            header ("Location: ../admin/admin.php");
        } else if ($row["role"] == "1") {
            header("Location: ../Home/index.php"); // Chuyển hướng đến trang chủ nếu đăng nhập thành công
        }
    } else {
        // Nếu thông tin đăng nhập không chính xác, thêm thông báo lỗi vào mảng `$message`
        $message[] = "Email đăng nhập hoặc mật khẩu không chính xác!";

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