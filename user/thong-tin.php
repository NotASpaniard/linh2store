<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/dang-nhap.php');
    exit();
}

$user = $_SESSION['user'];
$success_message = '';
$error_message = '';

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $district = trim($_POST['district']);
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];
    
    // Validation
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = 'Họ tên không được để trống';
    }
    
    if (empty($email)) {
        $errors[] = 'Email không được để trống';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ';
    }
    
    if (empty($phone)) {
        $errors[] = 'Số điện thoại không được để trống';
    } elseif (!preg_match('/^[0-9]{10,11}$/', $phone)) {
        $errors[] = 'Số điện thoại không hợp lệ';
    }
    
    if (empty($address)) {
        $errors[] = 'Địa chỉ không được để trống';
    }
    
    if (empty($city)) {
        $errors[] = 'Thành phố không được để trống';
    }
    
    if (empty($district)) {
        $errors[] = 'Quận/Huyện không được để trống';
    }
    
    if (empty($errors)) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                UPDATE users 
                SET full_name = ?, email = ?, phone = ?, address = ?, city = ?, district = ?, birthday = ?, gender = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $full_name, $email, $phone, $address, $city, $district, 
                $birthday ?: null, $gender ?: null, $user['id']
            ]);
            
            // Cập nhật session
            $_SESSION['user']['full_name'] = $full_name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['phone'] = $phone;
            $_SESSION['user']['address'] = $address;
            $_SESSION['user']['city'] = $city;
            $_SESSION['user']['district'] = $district;
            $_SESSION['user']['birthday'] = $birthday;
            $_SESSION['user']['gender'] = $gender;
            
            $success_message = 'Cập nhật thông tin thành công!';
            $user = $_SESSION['user']; // Refresh user data
            
        } catch (Exception $e) {
            $error_message = 'Có lỗi xảy ra khi cập nhật thông tin: ' . $e->getMessage();
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Lấy thông tin user từ database
try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $user_data = $stmt->fetch();
    
    if ($user_data) {
        $user = array_merge($user, $user_data);
    }
} catch (Exception $e) {
    $error_message = 'Không thể tải thông tin người dùng';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            padding: var(--spacing-xl);
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .profile-header h1 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-md);
        }
        
        .profile-header p {
            color: var(--text-light);
            font-size: var(--font-size-lg);
        }
        
        .profile-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        
        .profile-avatar {
            text-align: center;
            padding: var(--spacing-xl);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--white);
        }
        
        .avatar {
            width: 120px;
            height: 120px;
            border-radius: var(--radius-full);
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--spacing-md);
            font-size: var(--font-size-4xl);
            border: 4px solid rgba(255, 255, 255, 0.3);
        }
        
        .avatar-name {
            font-size: var(--font-size-xl);
            font-weight: 600;
            margin-bottom: var(--spacing-xs);
        }
        
        .avatar-email {
            font-size: var(--font-size-base);
            opacity: 0.9;
        }
        
        .profile-form {
            padding: var(--spacing-xl);
        }
        
        .form-section {
            margin-bottom: var(--spacing-xl);
        }
        
        .form-section h3 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-lg);
        }
        
        .form-group {
            margin-bottom: var(--spacing-lg);
        }
        
        .form-group label {
            display: block;
            margin-bottom: var(--spacing-xs);
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: var(--spacing-md);
            border: 2px solid #e9ecef;
            border-radius: var(--radius-sm);
            font-size: var(--font-size-base);
            transition: border-color var(--transition-fast);
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-actions {
            display: flex;
            gap: var(--spacing-md);
            justify-content: center;
            margin-top: var(--spacing-xl);
            padding-top: var(--spacing-xl);
            border-top: 1px solid #e9ecef;
        }
        
        .btn {
            padding: var(--spacing-md) var(--spacing-xl);
            border: none;
            border-radius: var(--radius-sm);
            text-decoration: none;
            font-size: var(--font-size-base);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: var(--accent-color);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }
        
        .btn-outline:hover {
            background: var(--primary-color);
            color: var(--white);
        }
        
        .alert {
            padding: var(--spacing-md);
            border-radius: var(--radius-sm);
            margin-bottom: var(--spacing-lg);
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-lg);
            margin-top: var(--spacing-xl);
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: var(--spacing-lg);
            border-radius: var(--radius-md);
            text-align: center;
        }
        
        .stat-card h4 {
            margin: 0 0 var(--spacing-xs) 0;
            color: var(--accent-color);
            font-size: var(--font-size-2xl);
        }
        
        .stat-card p {
            margin: 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .profile-stats {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../">
                        <h1>Linh2Store</h1>
                    </a>
                </div>
                
                <nav class="main-nav">
                    <a href="../">Trang chủ</a>
                    <a href="../san-pham/">Sản phẩm</a>
                    <a href="../thuong-hieu/">Thương hiệu</a>
                    <a href="../blog/">Blog</a>
                    <a href="../lien-he/">Liên hệ</a>
                </nav>
                
                <div class="header-actions">
                    <a href="../thanh-toan/" class="cart-icon" title="Thanh toán">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                    <a href="index.php" class="user-icon" title="Tài khoản">
                        <i class="fas fa-user"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="../">Trang chủ</a>
            <span>/</span>
            <a href="index.php">Tài khoản</a>
            <span>/</span>
            <span>Thông tin cá nhân</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="profile-container">
        <div class="profile-header">
            <h1>Thông tin cá nhân</h1>
            <p>Cập nhật thông tin tài khoản của bạn</p>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-card">
            <div class="profile-avatar">
                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="avatar-name"><?php echo htmlspecialchars($user['full_name'] ?? $user['name']); ?></div>
                <div class="avatar-email"><?php echo htmlspecialchars($user['email']); ?></div>
            </div>
            
            <form method="POST" class="profile-form">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-section">
                    <h3>Thông tin cơ bản</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="full_name">Họ và tên *</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Số điện thoại *</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="birthday">Ngày sinh</label>
                            <input type="date" id="birthday" name="birthday" value="<?php echo $user['birthday'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="gender">Giới tính</label>
                            <select id="gender" name="gender">
                                <option value="">Chọn giới tính</option>
                                <option value="male" <?php echo ($user['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Nam</option>
                                <option value="female" <?php echo ($user['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Nữ</option>
                                <option value="other" <?php echo ($user['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Khác</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Địa chỉ</h3>
                    <div class="form-group">
                        <label for="address">Địa chỉ chi tiết *</label>
                        <textarea id="address" name="address" placeholder="Số nhà, tên đường, phường/xã..." required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="city">Thành phố/Tỉnh *</label>
                            <select id="city" name="city" required>
                                <option value="">Chọn thành phố/tỉnh</option>
                                <option value="hanoi" <?php echo ($user['city'] ?? '') === 'hanoi' ? 'selected' : ''; ?>>Hà Nội</option>
                                <option value="hcm" <?php echo ($user['city'] ?? '') === 'hcm' ? 'selected' : ''; ?>>TP. Hồ Chí Minh</option>
                                <option value="danang" <?php echo ($user['city'] ?? '') === 'danang' ? 'selected' : ''; ?>>Đà Nẵng</option>
                                <option value="cantho" <?php echo ($user['city'] ?? '') === 'cantho' ? 'selected' : ''; ?>>Cần Thơ</option>
                                <option value="haiphong" <?php echo ($user['city'] ?? '') === 'haiphong' ? 'selected' : ''; ?>>Hải Phòng</option>
                                <option value="other" <?php echo ($user['city'] ?? '') === 'other' ? 'selected' : ''; ?>>Khác</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="district">Quận/Huyện *</label>
                            <input type="text" id="district" name="district" value="<?php echo htmlspecialchars($user['district'] ?? ''); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Cập nhật thông tin
                    </button>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại
                    </a>
                </div>
            </form>
        </div>
        
        <div class="profile-stats">
            <div class="stat-card">
                <h4>3</h4>
                <p>Đơn hàng</p>
            </div>
            <div class="stat-card">
                <h4>4</h4>
                <p>Sản phẩm yêu thích</p>
            </div>
            <div class="stat-card">
                <h4>2</h4>
                <p>Đánh giá</p>
            </div>
            <div class="stat-card">
                <h4>1</h4>
                <p>Tháng thành viên</p>
            </div>
        </div>
    </div>

    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const fullName = document.getElementById('full_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const address = document.getElementById('address').value.trim();
            const city = document.getElementById('city').value;
            const district = document.getElementById('district').value.trim();
            
            if (!fullName || !email || !phone || !address || !city || !district) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin bắt buộc');
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('Email không hợp lệ');
                return;
            }
            
            // Phone validation
            const phoneRegex = /^[0-9]{10,11}$/;
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                alert('Số điện thoại không hợp lệ');
                return;
            }
        });
        
        // Auto-hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>
