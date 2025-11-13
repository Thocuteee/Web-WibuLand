document.addEventListener("DOMContentLoaded", function () {
    const UserField = document.getElementById("User");
    const emailField = document.getElementById("email");
    const passwordField = document.getElementById("pass");
    const passwordConfirmField = document.getElementById("pass_");
    const phoneField = document.getElementById("num");
    const form = document.getElementById("myForm");
    // Kiểm tra người dùng
    UserField.addEventListener("input", function () {
      const UserError = document.querySelector("#NoticeTextUser");
      const userValue = UserField.value; // Lấy giá trị từ trường người dùng
  
      if (userValue.length >= 10 && userValue.length <= 20) {
        UserError.textContent = "";
      } else {
        UserError.textContent = "*Độ dài từ 10 đến 20 kí tự.";
      }
    });
  
    // Hàm kiểm tra email khi người dùng nhập liệu vào trường email
    emailField.addEventListener("input", function () {
      const emailError = document.querySelector("#NoticeTextEmail");
      const email = emailField.value;
  
      // Kiểm tra email có chứa ký tự '@' hợp lệ hay không
      if (
        !email.includes("@") ||
        email.indexOf("@") === 0 ||
        email.indexOf("@") === email.length - 1
      ) {
        emailError.textContent =
          "*Email phải chứa ký tự '@' và ký tự trước và sau '@'.";
      } else {
        emailError.textContent = ""; // Xóa thông báo nếu đúng
      }
    });
  
    // Hàm kiểm tra mật khẩu khi người dùng nhập liệu vào trường mật khẩu
    passwordField.addEventListener("input", function () {
      const passwordError = document.querySelector("#NoticeTextPass");
      const password = passwordField.value;
  
      // Kiểm tra mật khẩu có ít nhất 6 ký tự hay không
      if (password.length < 8) {
        passwordError.textContent = "* Mật khẩu phải có ít nhất 8 ký tự.";
      } else {
        passwordError.textContent = ""; // Xóa thông báo nếu đúng
      }
    });
  
    // Kiểm tra mật khẩu nhập lại
    passwordConfirmField.addEventListener("input", function () {
      const passwordConfirmError = document.querySelector("#NoticeTextPass_");
      const passwordConfirm = passwordConfirmField.value;
      const password = passwordField.value;
  
      // Kiểm tra mật khẩu nhập lại có trùng khớp với mật khẩu không
      if (password !== passwordConfirm) {
        passwordConfirmError.textContent = "* Mật khẩu không khớp.";
      } else {
        passwordConfirmError.textContent = ""; // Xóa thông báo nếu đúng
      }
    });
  
    //Kiểm tra số điện thoại
  
    phoneField.addEventListener("input", function () {
      const phoneError = document.querySelector("#NoticeTextNum");
      const phone = phoneField.value;
  
      // Kiểm tra số điện thoại có 10 ký tự và bắt đầu bằng số 0
      const phoneRegex = /^0\d{9}$/; // Biểu thức chính quy: bắt đầu bằng 0 và có 9 chữ số tiếp theo
  
      if (!phoneRegex.test(phone)) {
        phoneError.textContent = "* Số điện thoại không hợp lệ.";
      } else {
        phoneError.textContent = ""; // Xóa thông báo nếu đúng
      }
    });
  
    // Kiểm tra khi người dùng nhấn nút submit
    form.addEventListener("submit", function (event) {
      let valid = true;
  
      //Kiem tra User
      const UserError = document.querySelector("#NoticeTextUser");
      const userValue = UserField.value; // Lấy giá trị từ trường người dùng
      if (userValue.length <= 10 && userValue.length >= 20) {
        UserError.textContent = "*Độ dài từ 10 đến 20 kí tự.";
        valid = false;
      }
  
      // Kiểm tra email
      const emailError = document.querySelector("#NoticeTextEmail");
      const email = emailField.value;
      if (
        !email.includes("@") ||
        email.indexOf("@") === 0 ||
        email.indexOf("@") === email.length - 1
      ) {
        emailError.textContent =
          "*Email phải chứa ký tự '@' và ký tự trước và sau '@'.";
        valid = false; // Nếu có lỗi, ngừng submit
      }
  
      // Kiểm tra mật khẩu
      const passwordError = document.querySelector("#NoticeTextPass");
      const password = passwordField.value;
      if (password.length < 8) {
        passwordError.textContent = "*Mật khẩu phải có ít nhất 8 ký tự.";
        valid = false; // Nếu có lỗi, ngừng submit
      }
  
      // Kiểm tra mật khẩu nhập lại
      const passwordConfirmError = document.querySelector("#NoticeTextPass_");
      const passwordConfirm = passwordConfirmField.value;
      if (password !== passwordConfirm) {
        passwordConfirmError.textContent = "*Mật khẩu không khớp.";
        valid = false; // Nếu có lỗi, ngừng submit
      }
  
      //kiểm tra lại sdt
      const phoneError = document.querySelector("#NoticeTextNum");
      const phone = phoneField.value;
      const phoneRegex = /^0\d{9}$/;
      if (!phoneRegex.test(phone)) {
        phoneError.textContent = "* Số điện thoại không hợp lệ.";
        valid = false;
      }
  
      // Nếu có lỗi, ngừng submit form
      if (!valid) {
        event.preventDefault();
      }
      else{
        form.method = "POST";
        form.submit();
      }
    });
  });
  