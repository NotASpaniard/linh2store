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
                <h1>Đăng ký tài khoản</h1>
                <p>Tham gia cộng đồng Linh2Store</p>
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
                    <label for="full_name">Họ và tên *</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Tên đăng nhập *</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu *</label>
                    <input type="password" id="password" name="password" required>
                    <div id="password-strength"></div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Đăng ký</button>
            </form>
            
            <div class="auth-footer">
                <p>Đã có tài khoản? <a href="dang-nhap.php">Đăng nhập ngay</a></p>
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
                        if (data.strength === 'weak') {
                            this.classList.add('password-weak');
                        } else if (data.strength === 'medium') {
                            this.classList.add('password-medium');
                        } else if (data.strength === 'strong') {
                            this.classList.add('password-strong');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error checking password strength:', error);
                });
            } else {
                strengthContainer.innerHTML = '';
                this.classList.remove('password-weak', 'password-medium', 'password-strong');
            }
        });

        // Password confirmation checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password');
            
            if (confirmPassword.value) {
                checkPasswordMatch();
            }
        });

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
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: var(--spacing-lg);
        }
        
        .auth-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: var(--spacing-xl);
            width: 100%;
            max-width: 400px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .auth-header h1 {
            color: var(--text-dark);
            font-size: var(--font-size-xl);
            margin-bottom: var(--spacing-sm);
        }
        
        .auth-header p {
            color: var(--text-light);
            font-size: var(--font-size-sm);
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
        
        .form-group input {
            width: 100%;
            padding: var(--spacing-md);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: var(--font-size-base);
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .btn-full {
            width: 100%;
            padding: var(--spacing-md);
            font-size: var(--font-size-base);
            font-weight: 600;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: var(--spacing-lg);
        }
        
        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: var(--spacing-md);
            border-radius: 8px;
            margin-bottom: var(--spacing-lg);
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        
        .alert-success {
            background: #e8f5e8;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        
        .password-weak {
            border-color: #f44336;
        }
        
        .password-medium {
            border-color: #ff9800;
        }
        
        .password-strong {
            border-color: #4caf50;
        }
        
        .form-input.password-mismatch {
            border-color: #f44336;
        }
    </style>
</body>
</html>
