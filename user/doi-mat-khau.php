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

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    $errors = [];
    
    if (empty($current_password)) {
        $errors[] = 'Mật khẩu hiện tại không được để trống';
    }
    
    if (empty($new_password)) {
        $errors[] = 'Mật khẩu mới không được để trống';
    } elseif (strlen($new_password) < 6) {
        $errors[] = 'Mật khẩu mới phải có ít nhất 6 ký tự';
    }
    
    if (empty($confirm_password)) {
        $errors[] = 'Xác nhận mật khẩu không được để trống';
    } elseif ($new_password !== $confirm_password) {
        $errors[] = 'Mật khẩu xác nhận không khớp';
    }
    
    if (empty($errors)) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Kiểm tra mật khẩu hiện tại
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $user_data = $stmt->fetch();
            
            if (!$user_data || !password_verify($current_password, $user_data['password'])) {
                $error_message = 'Mật khẩu hiện tại không đúng';
            } else {
                // Cập nhật mật khẩu mới
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$hashed_password, $user['id']]);
                
                $success_message = 'Đổi mật khẩu thành công!';
            }
            
        } catch (Exception $e) {
            $error_message = 'Có lỗi xảy ra khi đổi mật khẩu: ' . $e->getMessage();
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .password-container {
            max-width: 600px;
            margin: 0 auto;
            padding: var(--spacing-xl);
        }
        
        .password-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .password-header h1 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-md);
        }
        
        .password-header p {
            color: var(--text-light);
            font-size: var(--font-size-lg);
        }
        
        .password-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            padding: var(--spacing-xl);
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
            border: 2px solid #e9ecef;
            border-radius: var(--radius-sm);
            font-size: var(--font-size-base);
            transition: border-color var(--transition-fast);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .password-strength {
            margin-top: var(--spacing-sm);
        }
        
        .strength-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: var(--radius-sm);
            overflow: hidden;
            margin-bottom: var(--spacing-xs);
        }
        
        .strength-fill {
            height: 100%;
            transition: all var(--transition-fast);
            border-radius: var(--radius-sm);
        }
        
        .strength-weak { background: #dc3545; width: 25%; }
        .strength-fair { background: #ffc107; width: 50%; }
        .strength-good { background: #17a2b8; width: 75%; }
        .strength-strong { background: #28a745; width: 100%; }
        
        .strength-text {
            font-size: var(--font-size-sm);
            color: var(--text-light);
        }
        
        .password-requirements {
            background: #f8f9fa;
            padding: var(--spacing-md);
            border-radius: var(--radius-sm);
            margin-top: var(--spacing-sm);
        }
        
        .password-requirements h4 {
            margin: 0 0 var(--spacing-sm) 0;
            color: var(--text-dark);
            font-size: var(--font-size-sm);
        }
        
        .password-requirements ul {
            margin: 0;
            padding-left: var(--spacing-lg);
            color: var(--text-light);
            font-size: var(--font-size-sm);
        }
        
        .password-requirements li {
            margin-bottom: var(--spacing-xs);
        }
        
        .password-requirements li.valid {
            color: #28a745;
        }
        
        .password-requirements li.invalid {
            color: #dc3545;
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
        
        .btn-primary:disabled {
            background: var(--text-light);
            cursor: not-allowed;
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
        
        .security-tips {
            background: #e3f2fd;
            padding: var(--spacing-lg);
            border-radius: var(--radius-md);
            margin-top: var(--spacing-xl);
        }
        
        .security-tips h3 {
            margin: 0 0 var(--spacing-md) 0;
            color: var(--text-dark);
            font-size: var(--font-size-lg);
        }
        
        .security-tips ul {
            margin: 0;
            padding-left: var(--spacing-lg);
            color: var(--text-light);
        }
        
        .security-tips li {
            margin-bottom: var(--spacing-xs);
        }
        
        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
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
                        <?php if (!empty($user['avatar']) && file_exists("../images/avatars/" . $user['avatar'])): ?>
                            <img src="../images/avatars/<?php echo $user['avatar']; ?>" alt="Avatar" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
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
            <span>Đổi mật khẩu</span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="password-container">
        <div class="password-header">
            <h1>Đổi mật khẩu</h1>
            <p>Bảo mật tài khoản của bạn</p>
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
        
        <div class="password-card">
            <form method="POST" id="passwordForm">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label for="current_password">Mật khẩu hiện tại *</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Mật khẩu mới *</label>
                    <input type="password" id="new_password" name="new_password" required>
                    
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-text" id="strengthText">Nhập mật khẩu để kiểm tra độ mạnh</div>
                    </div>
                    
                    <div class="password-requirements">
                        <h4>Yêu cầu mật khẩu:</h4>
                        <ul>
                            <li id="req-length" class="invalid">Ít nhất 6 ký tự</li>
                            <li id="req-uppercase" class="invalid">Có chữ hoa</li>
                            <li id="req-lowercase" class="invalid">Có chữ thường</li>
                            <li id="req-number" class="invalid">Có số</li>
                            <li id="req-special" class="invalid">Có ký tự đặc biệt</li>
                        </ul>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu mới *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="fas fa-key"></i>
                        Đổi mật khẩu
                    </button>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại
                    </a>
                </div>
            </form>
        </div>
        
        <div class="security-tips">
            <h3><i class="fas fa-shield-alt"></i> Mẹo bảo mật</h3>
            <ul>
                <li>Sử dụng mật khẩu mạnh với ít nhất 8 ký tự</li>
                <li>Kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt</li>
                <li>Không sử dụng thông tin cá nhân trong mật khẩu</li>
                <li>Đổi mật khẩu định kỳ để bảo mật tài khoản</li>
                <li>Không chia sẻ mật khẩu với người khác</li>
            </ul>
        </div>
    </div>

    <script>
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        const submitBtn = document.getElementById('submitBtn');
        
        // Password strength checker
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            
            // Update strength bar
            strengthFill.className = 'strength-fill strength-' + strength.level;
            strengthText.textContent = strength.text;
            
            // Update requirements
            updateRequirements(password);
            
            // Check if passwords match
            checkPasswordMatch();
        });
        
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        
        function checkPasswordStrength(password) {
            let score = 0;
            let requirements = {
                length: password.length >= 6,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };
            
            // Calculate score
            Object.values(requirements).forEach(req => {
                if (req) score++;
            });
            
            if (score < 2) return { level: 'weak', text: 'Mật khẩu yếu' };
            if (score < 4) return { level: 'fair', text: 'Mật khẩu trung bình' };
            if (score < 5) return { level: 'good', text: 'Mật khẩu tốt' };
            return { level: 'strong', text: 'Mật khẩu mạnh' };
        }
        
        function updateRequirements(password) {
            const requirements = {
                length: password.length >= 6,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };
            
            Object.keys(requirements).forEach(req => {
                const element = document.getElementById('req-' + req);
                if (requirements[req]) {
                    element.className = 'valid';
                } else {
                    element.className = 'invalid';
                }
            });
        }
        
        function checkPasswordMatch() {
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword && newPassword !== confirmPassword) {
                confirmPasswordInput.style.borderColor = '#dc3545';
                submitBtn.disabled = true;
            } else {
                confirmPasswordInput.style.borderColor = '#e9ecef';
                updateSubmitButton();
            }
        }
        
        function updateSubmitButton() {
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const currentPassword = document.getElementById('current_password').value;
            
            const isValid = newPassword.length >= 6 && 
                           newPassword === confirmPassword && 
                           currentPassword.length > 0;
            
            submitBtn.disabled = !isValid;
        }
        
        // Form validation
        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (!currentPassword || !newPassword || !confirmPassword) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp');
                return;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Mật khẩu mới phải có ít nhất 6 ký tự');
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
