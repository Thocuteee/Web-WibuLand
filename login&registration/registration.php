<!-- <?php
      include '../components/connect.php';


      if (isset($_POST['submit'])) {
        $username = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $phone = $_POST['phone'];

        $select_user = mysqli_query($conn, "SELECT * FROM `users` WHERE SDT = '$phone' OR EmailUser = '$email'");

        if (mysqli_num_rows($select_user) > 0) {
          $message[] = "Tài khoản đã tồn tại! ";
          
        } else {
          $add_user = mysqli_query($conn, "INSERT INTO `users` (SDT,NameUser,EmailUser,PasswordUser) VALUES('$phone','$username','$email', '$password')");

          $id_user = mysqli_insert_id($conn);

          $add_user_exp = mysqli_query($conn, "INSERT INTO `expuser` (IdUser) VALUES('$id_user')");


          $_SESSION["user_id"] = $id_user;
          header("Location: ../Home/index.php");
          exit();
        }
      }
      ?> -->
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- GOOGLE FONT -->
  <!-- Kiểu chữ Noto San, Paytone -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Paytone+One&display=swap"
    rel="stylesheet" />
  <!-- FONT AWESOME -->
  <script
    src="https://kit.fontawesome.com/eff669a9ab.js"
    crossorigin="anonymous"></script>
  <!-- Css -->
  <link rel="stylesheet" href="css/login&registration.css" />
  <script src="/login&registration/js/registration.js"></script>
  <title>Đăng kí</title>
</head>

<body>
  <?php
    if (isset($message)) {
      echo '<div class="alert">' . $message[0] . '</div>';
    }
  ?>
  <form id="myForm">
    <div class="main-login">
      <div class="wrapper">
        <div class="login-box">
          <form action="" method="POST">
            <h2>Đăng kí</h2>

            <!-- Người dùng -->
            <div class="input-box">
              <i class="fa-solid fa-user"></i>
              <input
                style="margin-left: 3.5rem; font-size: 1.5rem"
                type="text"
                name="name"
                placeholder="Tên người dùng"
                id="User" />
              <div class="NoticeText" id="NoticeTextUser"></div>
            </div>

            <!-- Email -->
            <div class="input-box">
              <i class="fa-solid fa-envelope"></i>
              <input
                style="margin-left: 3.5rem; font-size: 1.5rem"
                type="email"
                name="email"
                placeholder="Email"
                id="email" />
              <div class="NoticeText" id="NoticeTextEmail"></div>
            </div>

            <!-- Mật khẩu -->
            <div class="input-box">
              <i class="fa-solid fa-lock"></i>
              <input
                style="margin-left: 3.5rem; font-size: 1.5rem"
                type="password"
                name="password"
                placeholder="Mật khẩu"
                id="pass" />
              <div class="NoticeText" id="NoticeTextPass"></div>
            </div>

            <!-- Nhập lại mật khẩu -->
            <div class="input-box">
              <i class="fa-solid fa-lock"></i>
              <input
                style="margin-left: 3.5rem; font-size: 1.5rem"
                type="password"
                name="password"
                placeholder="Nhập lại mật khẩu"
                id="pass_" />
              <div class="NoticeText" id="NoticeTextPass_"></div>
            </div>

            <!-- Số điện thoại -->
            <div class="input-box">
              <i class="fa-solid fa-phone"></i>
              <input
                style="margin-left: 3.5rem; font-size: 1.5rem"
                type="text"
                name="phone"
                placeholder="Số điện thoại"
                id="num" />
              <div class="NoticeText" id="NoticeTextNum"></div>
            </div>

            <div class="button">
              <button type="submit" name="submit">Đăng kí</button>
            </div>

            <div class="sign-link">
              <span>Đã có tài khoản?</span>
              <a href="login.php">Đăng nhập</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </form>
</body>

</html>