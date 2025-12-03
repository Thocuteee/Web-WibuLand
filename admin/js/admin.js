function showAddModal() {
    document.getElementById('productAddModal').style.display = 'block';
}

function closeAddModal() {
    document.getElementById('productAddModal').style.display = 'none';
}

// Hàm tìm kiếm sản phẩm
function searchProducts() {
    const searchInput = document.getElementById('searchInput');
    const searchValue = searchInput.value.toLowerCase().trim();
    const table = document.getElementById('productTableBody');
    const rows = table.getElementsByTagName('tr');
    
    let foundCount = 0;
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const productName = row.cells[2]?.textContent.toLowerCase() || '';
        const productId = row.cells[0]?.textContent.toLowerCase() || '';
        
        if (searchValue === '' || productName.includes(searchValue) || productId.includes(searchValue)) {
            row.style.display = '';
            foundCount++;
        } else {
            row.style.display = 'none';
        }
    }
    
    // Hiển thị thông báo nếu không tìm thấy
    if (foundCount === 0 && searchValue !== '') {
        showNotification('Không tìm thấy sản phẩm nào!', 'error');
    } else if (searchValue !== '') {
        showNotification(`Tìm thấy ${foundCount} sản phẩm`, 'success');
    }
}

// Tìm kiếm khi nhấn Enter
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchProducts();
            }
        });
        
        // Tìm kiếm real-time khi gõ
        searchInput.addEventListener('input', function() {
            if (this.value.length >= 2 || this.value.length === 0) {
                searchProducts();
            }
        });
    }
});

// Hàm hiển thị thông báo
function showNotification(message, type) {
    // Xóa thông báo cũ nếu có
    const oldNotif = document.querySelector('.search-notification');
    if (oldNotif) {
        oldNotif.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = 'search-notification';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        padding: 15px 25px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: bold;
        z-index: 10000;
        animation: slideIn 0.3s ease-out;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        ${type === 'success' ? 'background: #28a745; color: white;' : 'background: #dc3545; color: white;'}
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 2000);
}

// CSS Animation cho notification
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

