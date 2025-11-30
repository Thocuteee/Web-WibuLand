//- Mở và đóng dropdown
function dropdownOpenClose(){
    let activebtn = document.querySelector('.user');
    let dropdown = document.querySelector('.dropdown-menu')
    activebtn.onclick = function(){
        dropdown.classList.toggle('show');
    }
}

//-Mở và đóng sidebar ở phần main (Menu)
function siderOpenClose(){
    let btn_menu = document.querySelector('#btn-sidebar');
    let sidebar = document.querySelector(".main-nav");

    btn_menu.onclick = function(){ //Hàm hoạt động khi click chuột vào button
        sidebar.classList.toggle('active');//Thêm class .main-nav + active
    }
}

// Mở/Đóng popup giỏ hàng
function toggleCartPopup() {
    const cartPopup = document.getElementById('cart-popup');
    if (cartPopup) {
        const isOpen = cartPopup.style.display === 'block';
        cartPopup.style.display = isOpen ? 'none' : 'block';
        
        // Khi mở popup, scroll lên đầu danh sách để hiển thị tất cả items
        if (!isOpen) {
            const cartItems = cartPopup.querySelector('.cart-items');
            if (cartItems) {
                cartItems.scrollTop = 0;
            }
        }
    }
}

function quickAddToCart(event, product_id, category) {
    event.preventDefault();
    const quantity = 1; 

    fetch('../components/cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${product_id}&category=${category}&quantity=${quantity}&ajax=1`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Lỗi mạng: Không thể kết nối đến server.');
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            // Hiển thị thông báo và chuyển hướng nhẹ nhàng
            alert(`✅ Đã thêm 1 sản phẩm ${data.data.product_name || 'vào'} giỏ hàng!`);
            // Chuyển hướng với query param để header.php (có cart logic) load lại dữ liệu giỏ hàng chính xác
            window.location.href = window.location.href.split('?')[0] + '?cart_added=1';
            
        } else {
            alert(`Lỗi: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Lỗi AJAX:', error);
        alert(`Lỗi hệ thống: ${error.message}`);
    });
}


window.addEventListener('load', function() {
    siderOpenClose();
    dropdownOpenClose();
});