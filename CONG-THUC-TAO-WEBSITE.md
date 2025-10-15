# 🚀 CÔNG THỨC TẠO WEBSITE HOÀN CHỈNH

## 📋 TỔNG QUAN
Công thức này giúp bạn tạo ra một website thương mại điện tử hoàn chỉnh với đầy đủ tính năng, có thể tái sử dụng cho bất kỳ dự án nào.

---

## 🏗️ KIẾN TRÚC TỔNG THỂ

### 1. CẤU TRÚC THƯ MỤC CHUẨN
```
project-name/
├── 📁 admin/                 # Trang quản trị
│   ├── index.php            # Dashboard chính
│   ├── products.php         # Quản lý sản phẩm
│   ├── orders.php           # Quản lý đơn hàng
│   ├── customers.php        # Quản lý khách hàng
│   ├── inventory.php        # Quản lý kho
│   ├── reports.php          # Báo cáo
│   └── api/                 # API cho admin
├── 📁 assets/               # Tài nguyên tĩnh
│   ├── css/
│   ├── js/
│   └── images/
├── 📁 auth/                 # Xác thực
│   ├── dang-nhap.php        # Đăng nhập
│   ├── dang-ky.php          # Đăng ký
│   └── dang-xuat.php        # Đăng xuất
├── 📁 config/               # Cấu hình
│   ├── database.php         # Kết nối DB
│   ├── session.php          # Quản lý session
│   └── image-helper.php     # Helper ảnh
├── 📁 database/             # Database
│   ├── schema.sql           # Cấu trúc DB
│   └── update-schema.sql    # Cập nhật DB
├── 📁 images/               # Ảnh sản phẩm
├── 📁 san-pham/             # Trang sản phẩm
├── 📁 thanh-toan/           # Thanh toán
├── 📁 user/                 # Trang người dùng
├── 📁 api/                  # API công khai
├── index.php                # Trang chủ
└── setup.php                # Cài đặt ban đầu
```

---

## 🗄️ DATABASE SCHEMA CHUẨN

### 1. BẢNG NGƯỜI DÙNG
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 2. BẢNG DANH MỤC
```sql
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    logo VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. BẢNG SẢN PHẨM
```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    brand_id INT,
    category_id INT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    sku VARCHAR(100) UNIQUE,
    stock_quantity INT DEFAULT 0,
    weight DECIMAL(8,2) DEFAULT 0,
    dimensions VARCHAR(50),
    ingredients TEXT,
    usage_instructions TEXT,
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    alt_text VARCHAR(200),
    sort_order INT DEFAULT 0,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

### 4. BẢNG ĐƠN HÀNG
```sql
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method ENUM('cod', 'bank_transfer', 'momo', 'vnpay') DEFAULT 'cod',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address TEXT NOT NULL,
    shipping_phone VARCHAR(20) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
```

### 5. BẢNG GIỎ HÀNG & YÊU THÍCH
```sql
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist_item (user_id, product_id)
);
```

---

## 🔧 CẤU HÌNH CORE

### 1. DATABASE CONNECTION (config/database.php)
```php
<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'your_database_name';
    private $username = 'your_username';
    private $password = 'your_password';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
```

### 2. SESSION MANAGEMENT (config/session.php)
```php
<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: auth/dang-nhap.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    $user = getCurrentUser();
    if (!$user || $user['role'] !== 'admin') {
        header('Location: index.php');
        exit();
    }
}
?>
```

### 3. IMAGE HELPER (config/image-helper.php)
```php
<?php
function getProductImage($product_id, $fallback_url = 'https://via.placeholder.com/300x300') {
    $images_dir = __DIR__ . '/../images/';
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    // Tìm ảnh theo pattern: product_{id}.{extension}
    foreach ($allowed_extensions as $ext) {
        $image_path = $images_dir . "product_{$product_id}.{$ext}";
        if (file_exists($image_path)) {
            return "images/product_{$product_id}.{$ext}";
        }
    }
    
    // Fallback: lấy ảnh từ thư mục
    $image_files = glob($images_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    if (!empty($image_files)) {
        $image_index = ($product_id - 1) % count($image_files);
        $selected_image = $image_files[$image_index];
        return 'images/' . basename($selected_image);
    }
    
    return $fallback_url;
}
?>
```

---

## 🎨 FRONTEND TEMPLATE

### 1. CSS FRAMEWORK (assets/css/main.css)
```css
/* CSS Variables */
:root {
    --primary-color: #EC407A;
    --secondary-color: #E3F2FD;
    --cta-color: #FF4081;
    --text-dark: #333;
    --text-light: #666;
    --white: #fff;
    --bg-light: #f8f9fa;
    --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
    --shadow-lg: 0 4px 8px rgba(0,0,0,0.15);
    --radius-sm: 4px;
    --radius-lg: 8px;
    --spacing-xs: 4px;
    --spacing-sm: 8px;
    --spacing-md: 16px;
    --spacing-lg: 24px;
    --spacing-xl: 32px;
    --transition-fast: 0.2s ease;
}

/* Reset & Base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    line-height: 1.6;
    color: var(--text-dark);
    background: var(--bg-light);
}

/* Grid System */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--spacing-lg);
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -var(--spacing-sm);
}

.col-6 { width: 50%; padding: 0 var(--spacing-sm); }
.col-4 { width: 33.333%; padding: 0 var(--spacing-sm); }
.col-3 { width: 25%; padding: 0 var(--spacing-sm); }

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-xs);
    padding: var(--spacing-sm) var(--spacing-md);
    border: none;
    border-radius: var(--radius-sm);
    text-decoration: none;
    cursor: pointer;
    transition: all var(--transition-fast);
    font-size: 14px;
}

.btn-primary {
    background: var(--cta-color);
    color: var(--white);
}

.btn-secondary {
    background: var(--text-light);
    color: var(--white);
}

.btn:hover {
    opacity: 0.8;
    transform: translateY(-1px);
}

/* Cards */
.product-card {
    background: var(--white);
    border-radius: var(--radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: all var(--transition-fast);
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

/* Forms */
.form-group {
    margin-bottom: var(--spacing-md);
}

.form-control {
    width: 100%;
    padding: var(--spacing-sm) var(--spacing-md);
    border: 1px solid #ddd;
    border-radius: var(--radius-sm);
    font-size: 14px;
}

/* Responsive */
@media (max-width: 768px) {
    .col-6, .col-4, .col-3 {
        width: 100%;
    }
}
```

### 2. JAVASCRIPT FRAMEWORK (assets/js/main.js)
```javascript
// Utility Functions
const $ = (selector) => document.querySelector(selector);
const $$ = (selector) => document.querySelectorAll(selector);

// AJAX Helper
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        return { success: false, message: 'Có lỗi xảy ra' };
    }
}

// Cart Management
class CartManager {
    static async addToCart(productId, quantity = 1) {
        const result = await fetchData('api/cart.php', {
            method: 'POST',
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                quantity: quantity
            })
        });
        
        if (result.success) {
            this.showNotification('Đã thêm vào giỏ hàng!', 'success');
            this.updateCartCount();
        } else {
            this.showNotification(result.message, 'error');
        }
    }
    
    static showNotification(message, type = 'info') {
        // Tạo notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // Style notification
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '12px 20px',
            borderRadius: '4px',
            color: 'white',
            zIndex: '9999',
            backgroundColor: type === 'success' ? '#28a745' : '#dc3545'
        });
        
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

// Initialize when DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart
    CartManager.updateCartCount();
    
    // Initialize other features
    initializeProductCards();
    initializeSearch();
});

function initializeProductCards() {
    // Add to cart buttons
    $$('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            CartManager.addToCart(productId);
        });
    });
}
```

---

## 📱 RESPONSIVE DESIGN

### 1. MOBILE-FIRST APPROACH
```css
/* Mobile (default) */
.container { padding: 0 var(--spacing-sm); }
.hero-content h1 { font-size: 24px; }
.products-grid { grid-template-columns: 1fr; }

/* Tablet */
@media (min-width: 768px) {
    .products-grid { grid-template-columns: repeat(2, 1fr); }
    .hero-content h1 { font-size: 32px; }
}

/* Desktop */
@media (min-width: 1024px) {
    .products-grid { grid-template-columns: repeat(3, 1fr); }
    .hero-content h1 { font-size: 48px; }
}
```

---

## 🔐 AUTHENTICATION SYSTEM

### 1. LOGIN PAGE (auth/dang-nhap.php)
```php
<?php
require_once '../config/session.php';
require_once '../config/database.php';

$error = '';

if ($_POST) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            header('Location: ../index.php');
            exit();
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng';
        }
    } catch (Exception $e) {
        $error = 'Có lỗi xảy ra, vui lòng thử lại';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-form">
            <h1>Đăng nhập</h1>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Tên đăng nhập:</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Mật khẩu:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Đăng nhập</button>
            </form>
            
            <p>Chưa có tài khoản? <a href="dang-ky.php">Đăng ký ngay</a></p>
        </div>
    </div>
</body>
</html>
```

---

## 🛒 E-COMMERCE FEATURES

### 1. PRODUCT LISTING (san-pham/index.php)
```php
<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../config/image-helper.php';

// Lấy tham số
$page = max(1, intval($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$category = intval($_GET['category'] ?? 0);
$brand = intval($_GET['brand'] ?? 0);
$sort = $_GET['sort'] ?? 'newest';
$limit = 12;
$offset = ($page - 1) * $limit;

$products = [];
$total_products = 0;
$categories = [];
$brands = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Xây dựng query
    $where_conditions = ["p.status = 'active'"];
    $params = [];
    
    if ($search) {
        $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($category) {
        $where_conditions[] = "p.category_id = ?";
        $params[] = $category;
    }
    
    if ($brand) {
        $where_conditions[] = "p.brand_id = ?";
        $params[] = $brand;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Sắp xếp
    $order_by = match($sort) {
        'price_low' => 'p.price ASC',
        'price_high' => 'p.price DESC',
        'name' => 'p.name ASC',
        default => 'p.created_at DESC'
    };
    
    // Đếm tổng số sản phẩm
    $count_sql = "SELECT COUNT(*) as total FROM products p WHERE $where_clause";
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total_products = $stmt->fetch()['total'];
    
    // Lấy sản phẩm
    $sql = "
        SELECT p.*, b.name as brand_name, c.name as category_name
        FROM products p 
        LEFT JOIN brands b ON p.brand_id = b.id 
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE $where_clause 
        ORDER BY $order_by
        LIMIT $limit OFFSET $offset
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Lấy danh mục và thương hiệu
    $stmt = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $categories = $stmt->fetchAll();
    
    $stmt = $conn->query("SELECT * FROM brands WHERE status = 'active' ORDER BY name");
    $brands = $stmt->fetchAll();
    
} catch (Exception $e) {
    $products = [];
    $total_products = 0;
}

$total_pages = ceil($total_products / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <!-- Header content -->
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <!-- Filters -->
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <label>Tìm kiếm:</label>
                        <input type="text" name="search" placeholder="Tên sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label>Danh mục:</label>
                        <select name="category">
                            <option value="">Tất cả</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Sắp xếp:</label>
                        <select name="sort">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Giá cao đến thấp</option>
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Tên A-Z</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Lọc</button>
                </form>
            </div>

            <!-- Products Grid -->
            <div class="products-section">
                <div class="products-header">
                    <h2>Sản phẩm (<?php echo $total_products; ?> sản phẩm)</h2>
                </div>
                
                <div class="products-grid">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <div class="product-image">
                                    <img src="../<?php echo getProductImage($product['id']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         loading="lazy">
                                    <div class="product-actions">
                                        <button class="action-btn wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                        <button class="action-btn quick-view-btn" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title">
                                        <a href="chi-tiet.php?id=<?php echo $product['id']; ?>">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </a>
                                    </h3>
                                    <p class="product-brand"><?php echo htmlspecialchars($product['brand_name']); ?></p>
                                    <div class="product-price">
                                        <span class="price-current"><?php echo number_format($product['price']); ?>đ</span>
                                        <?php if ($product['sale_price']): ?>
                                            <span class="price-sale"><?php echo number_format($product['sale_price']); ?>đ</span>
                                        <?php endif; ?>
                                    </div>
                                    <button class="btn btn-primary add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-products">
                            <h3>Không có sản phẩm nào</h3>
                            <p>Không tìm thấy sản phẩm phù hợp với bộ lọc</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                           class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
```

---

## 🛡️ SECURITY BEST PRACTICES

### 1. INPUT VALIDATION
```php
function validateInput($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? '';
        
        if (isset($rule['required']) && $rule['required'] && empty($value)) {
            $errors[$field] = "Trường {$field} là bắt buộc";
            continue;
        }
        
        if (isset($rule['email']) && $rule['email'] && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$field] = "Email không hợp lệ";
        }
        
        if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
            $errors[$field] = "Tối thiểu {$rule['min_length']} ký tự";
        }
        
        if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
            $errors[$field] = "Tối đa {$rule['max_length']} ký tự";
        }
    }
    
    return $errors;
}
```

### 2. SQL INJECTION PREVENTION
```php
// Luôn sử dụng prepared statements
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
$stmt->execute([$username]);
$user = $stmt->fetch();

// Không bao giờ làm thế này:
// $sql = "SELECT * FROM users WHERE username = '$username'";
```

### 3. XSS PREVENTION
```php
// Luôn escape output
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');

// Hoặc sử dụng filter
echo filter_var($user_input, FILTER_SANITIZE_STRING);
```

---

## 🚀 DEPLOYMENT CHECKLIST

### 1. PRE-DEPLOYMENT
- [ ] Kiểm tra tất cả tính năng hoạt động
- [ ] Test responsive trên mobile/tablet
- [ ] Kiểm tra performance
- [ ] Backup database
- [ ] Cập nhật thông tin cấu hình

### 2. PRODUCTION SETUP
```php
// config/database.php - Production
private $host = 'your-production-host';
private $db_name = 'your_production_db';
private $username = 'your_production_user';
private $password = 'your_secure_password';
```

### 3. SECURITY HARDENING
- [ ] Đổi mật khẩu database mặc định
- [ ] Cập nhật PHP version
- [ ] Cấu hình HTTPS
- [ ] Thiết lập firewall
- [ ] Backup tự động

---

## 📊 MONITORING & ANALYTICS

### 1. ERROR LOGGING
```php
// config/error-handler.php
function logError($message, $file = '', $line = 0) {
    $log = date('Y-m-d H:i:s') . " - $message";
    if ($file) $log .= " in $file";
    if ($line) $log .= " on line $line";
    $log .= PHP_EOL;
    
    file_put_contents('logs/error.log', $log, FILE_APPEND);
}

set_error_handler(function($severity, $message, $file, $line) {
    logError($message, $file, $line);
});
```

### 2. PERFORMANCE MONITORING
```php
// config/performance.php
function startTimer() {
    return microtime(true);
}

function endTimer($start) {
    return microtime(true) - $start;
}

// Sử dụng
$start = startTimer();
// ... code ...
$execution_time = endTimer($start);
if ($execution_time > 1) {
    logError("Slow query: {$execution_time}s");
}
```

---

## 🎯 KẾT LUẬN

Công thức này cung cấp:

✅ **Kiến trúc hoàn chỉnh** - Từ database đến frontend  
✅ **Bảo mật cao** - XSS, SQL injection prevention  
✅ **Responsive design** - Mobile-first approach  
✅ **Tính năng đầy đủ** - E-commerce, admin, user management  
✅ **Dễ tái sử dụng** - Template cho mọi dự án  
✅ **Performance tốt** - Optimized queries, caching  
✅ **Maintainable** - Clean code, documentation  

**Cách sử dụng:**
1. Copy toàn bộ cấu trúc thư mục
2. Cập nhật database credentials
3. Chạy setup.php để tạo database
4. Upload ảnh vào thư mục images/
5. Customize theo nhu cầu dự án

**Kết quả:** Một website hoàn chỉnh, sẵn sàng production! 🚀
