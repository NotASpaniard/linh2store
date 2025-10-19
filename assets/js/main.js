/**
 * Linh2Store - JavaScript tối ưu (chỉ cart functions)
 * Website bán son môi & mỹ phẩm cao cấp
 */

// Khởi tạo khi DOM đã load
document.addEventListener('DOMContentLoaded', function() {
    initCart();
    initProductImages();
    initColorSwatches();
    initMobileMenu();
    initSmoothScroll();
    initLazyLoading();
});

/**
 * Khởi tạo giỏ hàng
 */
function initCart() {
    updateCartCount();

    // Lắng nghe sự kiện thêm vào giỏ hàng
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-cart')) {
            e.preventDefault();
            const productId = e.target.dataset.productId;
            const colorId = e.target.dataset.colorId;
            const quantity = e.target.dataset.quantity || 1;

            addToCart(productId, colorId, quantity);
        }
    });

    // Lắng nghe sự kiện xóa khỏi giỏ hàng
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-from-cart')) {
            e.preventDefault();
            const cartItemId = e.target.dataset.cartItemId;
            removeFromCart(cartItemId);
        }
    });
}

/**
 * Thêm vào giỏ hàng
 */
function addToCart(productId, colorId = null, quantity = 1) {
    showLoading();

    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    if (colorId) formData.append('color_id', colorId);
    formData.append('quantity', quantity);

    fetch('../api/cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                updateCartCount();
                showAlert('Đã thêm vào giỏ hàng!', 'success');
            } else {
                showAlert(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Lỗi thêm vào giỏ hàng:', error);
            showAlert('Có lỗi xảy ra khi thêm vào giỏ hàng', 'error');
        });
}

/**
 * Xóa khỏi giỏ hàng
 */
function removeFromCart(cartItemId) {
    showLoading();

    fetch(`../api/cart.php?id=${cartItemId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            if (data.success) {
                updateCartCount();
                // Reload trang giỏ hàng nếu đang ở trang đó
                if (window.location.pathname.includes('thanh-toan')) {
                    location.reload();
                }
            } else {
                showAlert(data.message || 'Có lỗi xảy ra', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Lỗi xóa khỏi giỏ hàng:', error);
            showAlert('Có lỗi xảy ra khi xóa khỏi giỏ hàng', 'error');
        });
}

/**
 * Cập nhật số lượng giỏ hàng
 */
function updateCartCount() {
    fetch('../api/cart.php')
        .then(response => response.json())
        .then(data => {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount && data.items) {
                const count = data.items.length;
                cartCount.textContent = count;
                cartCount.style.display = count > 0 ? 'block' : 'none';
            }
        })
        .catch(error => {
            console.error('Lỗi cập nhật số lượng giỏ hàng:', error);
        });
}

/**
 * Khởi tạo hình ảnh sản phẩm
 */
function initProductImages() {
    const productImages = document.querySelectorAll('.product-image');
    productImages.forEach(img => {
        img.addEventListener('click', function() {
            const modal = document.createElement('div');
            modal.className = 'image-modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <img src="${this.src}" alt="${this.alt}">
                </div>
            `;
            document.body.appendChild(modal);

            modal.querySelector('.close').addEventListener('click', () => {
                modal.remove();
            });
        });
    });
}

/**
 * Khởi tạo color swatches
 */
function initColorSwatches() {
    const swatches = document.querySelectorAll('.color-swatch');
    swatches.forEach(swatch => {
        swatch.addEventListener('click', function() {
            swatches.forEach(s => s.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

/**
 * Khởi tạo mobile menu
 */
function initMobileMenu() {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const mobileMenu = document.querySelector('.mobile-menu');

    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
        });
    }
}

/**
 * Khởi tạo smooth scroll
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Khởi tạo lazy loading
 */
function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
}

/**
 * Hiển thị loading
 */
function showLoading() {
    const loader = document.createElement('div');
    loader.className = 'loader';
    loader.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(loader);
}

/**
 * Ẩn loading
 */
function hideLoading() {
    const loader = document.querySelector('.loader');
    if (loader) {
        loader.remove();
    }
}

/**
 * Hiển thị thông báo
 */
function showAlert(message, type = 'info') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    alert.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        max-width: 300px;
        animation: slideIn 0.3s ease;
    `;

    document.body.appendChild(alert);

    // Tự động ẩn sau 3 giây
    setTimeout(() => {
        alert.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }, 3000);
}

/**
 * Format giá tiền
 */
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
}

/**
 * Thêm CSS animation
 */
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    .color-swatch.active {
        transform: scale(1.2);
        border-color: #EC407A;
    }
    
    .mobile-menu {
        display: none;
    }
    
    .mobile-menu.active {
        display: block;
    }
    
    .lazy {
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .lazy.loaded {
        opacity: 1;
    }
`;
document.head.appendChild(style);