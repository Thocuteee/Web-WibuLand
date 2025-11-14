// components/js/wishlist.js

// Hàm xử lý việc thêm/xóa Yêu thích qua AJAX
function toggleWishlist(event, product_id, category, is_in_wishlist) {
    // Ngăn chặn trình duyệt chuyển hướng (ngăn chặn lỗi giật giật)
    event.preventDefault();

    const item_key = `${category}_${product_id}`;
    const action = is_in_wishlist ? 'remove_wishlist' : 'add_wishlist';
    const iconElement = document.getElementById(`wishlist_${item_key}`);

    // Đã sửa: Gửi product_id và category rõ ràng.
    const url = `/components/cart_handler.php?action=${action}&product_id=${product_id}&category=${category}&ajax=1`;
    // Lưu ý: Cần thêm key=${item_key} cho phiên bản cũ nếu bạn muốn giữ lại, 
    // nhưng trong logic PHP mới, chúng ta chỉ cần product_id và category.

    fetch(url)
        .then(response => {
            // Kiểm tra HTTP status code
            if (!response.ok) {
                throw new Error(`Lỗi HTTP: ${response.status} - Không thể kết nối đến server.`);
            }
            return response.json(); // Phân tích phản hồi JSON
        })
        .then(data => {
            if (data.status === 'success') {
                // Thay đổi trạng thái icon ngay lập tức trên giao diện
                if (action === 'add_wishlist') {
                    iconElement.classList.remove('fa-regular');
                    iconElement.classList.add('fa-solid');
                    // Cập nhật hàm onclick để hành động tiếp theo là XÓA
                    iconElement.parentNode.onclick = (e) => toggleWishlist(e, product_id, category, true);

                } else if (action === 'remove_wishlist') {
                    iconElement.classList.remove('fa-solid');
                    iconElement.classList.add('fa-regular');
                    // Cập nhật hàm onclick để hành động tiếp theo là THÊM
                    iconElement.parentNode.onclick = (e) => toggleWishlist(e, product_id, category, false);
                }
            } else {
                console.error('Lỗi từ server:', data.message);
                // Hiển thị thông báo lỗi (ví dụ: "Cần đăng nhập...")
                alert(`Lỗi xử lý yêu thích: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Lỗi AJAX:', error);
            // Hiển thị thông báo lỗi chi tiết cho người dùng
            alert(`Lỗi kết nối server: ${error.message}`);
        });
}