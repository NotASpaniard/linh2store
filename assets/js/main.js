/**
 * Linh2Store - JavaScript chính
 * Website bán son môi & mỹ phẩm cao cấp
 */

// Khởi tạo khi DOM đã load
document.addEventListener('DOMContentLoaded', function() {
    initApp();
});

/**
 * Khởi tạo ứng dụng
 */
function initApp() {
    initSearch();
    initCart();
    initProductImages();
    initColorSwatches();
    initMobileMenu();
    initSmoothScroll();
    initLazyLoading();
}

/**
 * Khởi tạo tìm kiếm
 */
function initSearch() {
    const searchInput = document.querySelector('.search-input');
    const searchBtn = document.querySelector('.search-btn');

    if (searchInput) {
        // Tìm kiếm real-time
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length >= 2) {
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            } else {
                hideSearchResults();
            }
        });

        // Tìm kiếm khi nhấn Enter
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performSearch(this.value.trim());
            }
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            const query = searchInput.value.trim();
            if (query) {
                performSearch(query);
            }
        });
    }
}

/**
 * Thực hiện tìm kiếm
 */
function performSearch(query) {
    if (!query) return;

    // Hiển thị loading
    showLoading();

    // Gửi request tìm kiếm
    fetch(`/linh2store/api/search.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            displaySearchResults(data);
        })
        .catch(error => {
            hideLoading();
            console.error('Lỗi tìm kiếm:', error);
            showAlert('Có lỗi xảy ra khi tìm kiếm', 'error');
        });
}

/**
 * Hiển thị kết quả tìm kiếm
 */
function displaySearchResults(results) {
    const searchContainer = document.querySelector('.search-results');
    if (!searchContainer) {
        createSearchResultsContainer();
    }

    const container = document.querySelector('.search-results');
    if (!results || results.length === 0) {
        container.innerHTML = '<p class="no-results">Không tìm thấy sản phẩm nào</p>';
        return;
    }

    let html = '<div class="search-results-list">';
    results.forEach(product => {
        html += `
            <div class="search-result-item" onclick="goToProduct(${product.id})">
                <img src="${product.image}" alt="${product.name}" class="result-image">
                <div class="result-info">
                    <h4>${product.name}</h4>
                    <p class="result-price">${formatPrice(product.price)}</p>
                </div>
            </div>
        `;
    });
    html += '</div>';

    container.innerHTML = html;
    container.style.display = 'block';
}

/**
 * Tạo container kết quả tìm kiếm
 */
function createSearchResultsContainer() {
    const searchBar = document.querySelector('.search-bar');
    if (searchBar) {
        const container = document.createElement('div');
        container.className = 'search-results';
        container.style.cssText = `
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #E3F2FD;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.12);
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
            display: none;
        `;
        searchBar.appendChild(container);
    }
}

/**
 * Ẩn kết quả tìm kiếm
 */
function hideSearchResults() {
    const container = document.querySelector('.search-results');
    if (container) {
        container.style.display = 'none';
    }
}

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
    formData.append('product_id', productId);
    if (colorId) formData.append('color_id', colorId);
    formData.append('quantity', quantity);

    fetch('/linh2store/api/cart.php', {
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

    fetch(`/linh2store/api/cart.php?id=${cartItemId}`, {
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
    fetch('/linh2store/api/cart.php')
        .then(response => response.json())
        .then(data => {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = data.count || 0;
                cartCount.style.display = data.count > 0 ? 'block' : 'none';
            }
        })
        .catch(error => {
            console.error('Lỗi cập nhật giỏ hàng:', error);
        });
}

/**
 * Khởi tạo hình ảnh sản phẩm
 */
function initProductImages() {
    const productImages = document.querySelectorAll('.product-image img');

    productImages.forEach(img => {
        // Zoom effect khi hover
        img.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
        });

        img.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });

        // Lazy loading
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            });

            observer.observe(img);
        }
    });
}

/**
 * Khởi tạo color swatches
 */
function initColorSwatches() {
    const colorSwatches = document.querySelectorAll('.color-swatch');

    colorSwatches.forEach(swatch => {
        swatch.addEventListener('click', function() {
            // Xóa active class từ tất cả swatches
            colorSwatches.forEach(s => s.classList.remove('active'));

            // Thêm active class cho swatch được chọn
            this.classList.add('active');

            // Cập nhật hình ảnh sản phẩm nếu có
            const productImage = document.querySelector('.product-main-image');
            if (productImage && this.dataset.image) {
                productImage.src = this.dataset.image;
            }
        });
    });
}

/**
 * Khởi tạo mobile menu
 */
function initMobileMenu() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            this.classList.toggle('active');
        });

        // Đóng menu khi click outside
        document.addEventListener('click', function(e) {
            if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                mobileMenu.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
            }
        });
    }
}

/**
 * Khởi tạo smooth scroll
 */
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Khởi tạo lazy loading
 */
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const lazyImages = document.querySelectorAll('img[data-src]');

        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        lazyImages.forEach(img => imageObserver.observe(img));
    }
}

/**
 * Hiển thị loading
 */
function showLoading() {
    const existingLoader = document.querySelector('.loading-overlay');
    if (existingLoader) return;

    const loader = document.createElement('div');
    loader.className = 'loading-overlay';
    loader.innerHTML = '<div class="loading"></div>';
    loader.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    `;

    document.body.appendChild(loader);
}

/**
 * Ẩn loading
 */
function hideLoading() {
    const loader = document.querySelector('.loading-overlay');
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
 * Chuyển đến trang sản phẩm
 */
function goToProduct(productId) {
    window.location.href = `/linh2store/san-pham/chi-tiet.php?id=${productId}`;
}

/**
 * Thêm CSS animation cho alert
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