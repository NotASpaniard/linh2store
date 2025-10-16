<?php
/**
 * Trang đăng ký
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/database.php';
require_once '../config/auth-middleware.php';
require_once '../config/password-validator.php';

// Nếu đã đăng nhập, chuyển về trang chủ
AuthMiddleware::requireGuest();

$error = '';
$success = '';

// Xử lý đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Kiểm tra CSRF token
    if (!AuthMiddleware::verifyCSRFToken($csrf_token)) {
        $error = 'Token không hợp lệ. Vui lòng thử lại.';
    } elseif (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } else {
        // Kiểm tra độ mạnh mật khẩu
        $password_validation = PasswordValidator::validatePassword($password, $confirm_password);
        if (!$password_validation['is_valid']) {
            $error = implode('. ', $password_validation['errors']);
        } else {
            try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Kiểm tra username đã tồn tại
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Tên đăng nhập đã được sử dụng.';
            } else {
                // Kiểm tra email đã tồn tại
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email đã được sử dụng.';
                } else {
                    // Tạo tài khoản mới
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("
                        INSERT INTO users (username, email, password, full_name, phone, role, status) 
                        VALUES (?, ?, ?, ?, ?, 'user', 'active')
                    ");
                    
                    if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone])) {
                        $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.';
                        
                        // Tự động đăng nhập
                        $user_id = $conn->lastInsertId();
                        $user = [
                            'id' => $user_id,
                            'username' => $username,
                            'email' => $email,
                            'full_name' => $full_name,
                            'role' => 'user'
                        ];
                        
                        // Sử dụng JWT để đăng nhập
                        AuthMiddleware::loginUser($user);
                    } else {
                        $error = 'Có lỗi xảy ra khi tạo tài khoản. Vui lòng thử lại.';
                    }
                }
            }
        } catch (Exception $e) {
            $error = 'Có lỗi xảy ra. Vui lòng thử lại sau.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><a href="../" class="logo">Linh2Store</a></h1>
                <h2>Đăng ký tài khoản</h2>
                <p>Tham gia cộng đồng làm đẹp cùng chúng tôi!</p>
            </div>
            
            <!-- OAuth Login Options -->
            <div class="oauth-section">
                <div class="oauth-divider">
                    <span>Hoặc đăng ký bằng</span>
                </div>
                <div class="oauth-buttons">
                    <a href="<?php echo OAuthProvider::getGoogleAuthUrl(); ?>" class="oauth-btn google-btn">
                        <i class="fab fa-google"></i>
                        <span>Google</span>
                    </a>
                    <a href="<?php echo OAuthProvider::getFacebookAuthUrl(); ?>" class="oauth-btn facebook-btn">
                        <i class="fab fa-facebook-f"></i>
                        <span>Facebook</span>
                    </a>
                </div>
                <div class="oauth-divider">
                    <span>Hoặc tạo tài khoản mới</span>
                </div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo AuthMiddleware::generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="full_name" class="form-label">
                        <i class="fas fa-user"></i> Họ và tên *
                    </label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        class="form-input" 
                        placeholder="Nhập họ và tên đầy đủ"
                        value="<?php echo htmlspecialchars($full_name ?? ''); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="username" class="form-label">
                        <i class="fas fa-at"></i> Tên đăng nhập *
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        placeholder="Nhập tên đăng nhập"
                        value="<?php echo htmlspecialchars($username ?? ''); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Email *
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="Nhập địa chỉ email"
                        value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone"></i> Số điện thoại
                    </label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        class="form-input" 
                        placeholder="Nhập số điện thoại (tùy chọn)"
                        value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Mật khẩu *
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Nhập mật khẩu (tối thiểu 3/5 độ mạnh)"
                        required
                        minlength="9"
                    >
                    <div id="password-strength" class="password-strength-container"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password" class="form-label">
                        <i class="fas fa-lock"></i> Xác nhận mật khẩu *
                    </label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        class="form-input" 
                        placeholder="Nhập lại mật khẩu"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="agree_terms" value="1" required>
                        <span class="checkmark"></span>
                        Tôi đồng ý với <a href="#" target="_blank">Điều khoản sử dụng</a> và <a href="#" target="_blank">Chính sách bảo mật</a> *
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-user-plus"></i>
                    Đăng ký tài khoản
                </button>
            </form>
            
            <div class="auth-links">
                <a href="dang-nhap.php" class="auth-link">
                    <i class="fas fa-sign-in-alt"></i>
                    Đã có tài khoản? Đăng nhập ngay
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthContainer = document.getElementById('password-strength');
            
            if (password.length > 0) {
                // Gọi API để kiểm tra độ mạnh mật khẩu
                fetch('../api/check-password-strength.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ password: password })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        strengthContainer.innerHTML = data.html;
                        
                        // Cập nhật class cho input
                        this.classList.remove('password-weak', 'password-fair', 'password-good', 'password-strong');
                        this.classList.add('password-' + data.strength.level);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            } else {
                strengthContainer.innerHTML = '';
                this.classList.remove('password-weak', 'password-fair', 'password-good', 'password-strong');
            }
            
            // Kiểm tra mật khẩu xác nhận
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword.value) {
                checkPasswordMatch();
            }
        });
        
        // Kiểm tra mật khẩu xác nhận
        document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);
        
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password');
            
            if (confirmPassword.value && password !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Mật khẩu xác nhận không khớp');
                confirmPassword.classList.add('password-mismatch');
            } else {
                confirmPassword.setCustomValidity('');
                confirmPassword.classList.remove('password-mismatch');
            }
        }
    </script>
    
    <style>
        /* OAuth Styles */
        .oauth-section {
            margin-bottom: var(--spacing-xl);
        }
        
        .oauth-divider {
            text-align: center;
            position: relative;
            margin: var(--spacing-lg) 0;
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .oauth-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--primary-color);
            z-index: 1;
        }
        
        .oauth-divider span {
            background: var(--white);
            padding: 0 var(--spacing-md);
            position: relative;
            z-index: 2;
        }
        
        .oauth-buttons {
            display: flex;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
        }
        
        .oauth-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-md);
            border: 2px solid var(--primary-color);
            border-radius: var(--radius-md);
            text-decoration: none;
            font-weight: 500;
            transition: all var(--transition-fast);
        }
        
        .oauth-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .google-btn {
            color: #db4437;
            border-color: #db4437;
        }
        
        .google-btn:hover {
            background: #db4437;
            color: var(--white);
        }
        
        .facebook-btn {
            color: #4267B2;
            border-color: #4267B2;
        }
        
        .facebook-btn:hover {
            background: #4267B2;
            color: var(--white);
        }
        
        /* Password Strength Styles */
        .password-strength-container {
            margin-top: var(--spacing-sm);
        }
        
        .password-strength-indicator {
            font-size: var(--font-size-sm);
        }
        
        .strength-bar {
            width: 100%;
            height: 6px;
            background: #e0e0e0;
            border-radius: var(--radius-sm);
            overflow: hidden;
            margin-bottom: var(--spacing-xs);
        }
        
        .strength-fill {
            height: 100%;
            transition: all var(--transition-fast);
            border-radius: var(--radius-sm);
        }
        
        .strength-label {
            font-weight: 500;
            margin-bottom: var(--spacing-xs);
        }
        
        .strength-feedback {
            font-size: var(--font-size-xs);
            color: var(--text-light);
        }
        
        .feedback-item {
            margin-bottom: 2px;
        }
        
        /* Password input states */
        .form-input.password-weak {
            border-color: #f44336;
        }
        
        .form-input.password-fair {
            border-color: #ff9800;
        }
        
        .form-input.password-good {
            border-color: #4caf50;
        }
        
        .form-input.password-strong {
            border-color: #2196f3;
        }
        
        .form-input.password-mismatch {
            border-color: #f44336;
        }
        
        @media (max-width: 480px) {
            .oauth-buttons {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
