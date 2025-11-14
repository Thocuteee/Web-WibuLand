// components/js/wishlist.js

// Hàm xử lý việc thêm/xóa Yêu thích qua AJAX
function toggleWishlist(event, product_id, category, is_in_wishlist) {
    // Ngăn chặn trình duyệt chuyển hướng (ngăn chặn lỗi giật giật)
    event.preventDefault();

    const item_key = `${category}_${product_id}`;
    const action = is_in_wishlist ? 'remove_wishlist' : 'add_wishlist';
    const iconElement = document.getElementById(`wishlist_${item_key}`);

    // Đã sửa: Chuyển sang đường dẫn tuyệt đối để tránh lỗi đường dẫn tương đối
    const url = `/components/cart_handler.php?action=${action}&product_id=${product_id}&category=${category}&key=${item_key}&ajax=1`;

    fetch(url)
        .then(response => {
            // Kiểm tra HTTP status code
            if (!response.ok) {
                // Nếu lỗi 404, 500, etc.
                throw new Error(`Lỗi HTTP: ${response.status} - Không thể kết nối đến server.`);
            }
            return response.json();
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
                alert(`Lỗi xử lý yêu thích: ${data.message}`);
            }
        })
        .catch(error => {
            console.error('Lỗi AJAX:', error);
            // Hiển thị thông báo lỗi chi tiết cho người dùng
            alert(`Lỗi kết nối server: ${error.message}`);
        });
}