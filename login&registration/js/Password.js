// Chờ cho đến khi nội dung trang đã tải xong
document.addEventListener("DOMContentLoaded", function () {
    // Bắt đối tượng checkbox và ô nhập mật khẩu
    const passwordField = document.getElementById("password");
    const showPasswordCheckbox = document.getElementById("showPassword");
  
    // Gán sự kiện 'change' cho checkbox để ẩn/hiện mật khẩu
    showPasswordCheckbox.addEventListener("change", function () {
      // Nếu checkbox được chọn, đổi ô nhập mật khẩu thành dạng 'text' để hiện mật khẩu
      if (showPasswordCheckbox.checked) {
        passwordField.type = "text";
      } else {
        // Nếu không, đổi lại thành dạng 'password' để ẩn mật khẩu
        passwordField.type = "password";
      }
    });
  });
  