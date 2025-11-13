// JavaScript

let slideIndex = 0; // sửa ở đây: quản lý chỉ số slider
const sliders = document.querySelectorAll(".slider-bg"); // sửa ở đây: lấy tất cả các slider

function showSlides() {
  // Ẩn tất cả các slider trước
  sliders.forEach((slider) => (slider.style.display = "none")); // sửa ở đây: ẩn toàn bộ các slider

  // Hiển thị slider hiện tại
  sliders[slideIndex].style.display = "block"; // sửa ở đây: hiển thị slider hiện tại

  // Tăng chỉ số slider, nếu đến cuối thì quay lại slider đầu tiên
  slideIndex = (slideIndex + 1) % sliders.length; // sửa ở đây: tính toán chỉ số slider tiếp theo

  // Lặp lại quá trình này mỗi 1 giây (1000ms)
  setTimeout(showSlides, 5000); // sửa ở đây: thiết lập thời gian cập nhật  300000
}

// Gọi hàm để bắt đầu slider tự động
showSlides();