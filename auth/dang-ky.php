<?php
/**
 * Trang đăng ký
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/database.php';
require_once '../config/session.php';

// Nếu đã đăng nhập, chuyển về trang chủ
if (isLoggedIn()) {
    header('Location: ../');
    exit();
}

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
    if (!verifyCSRFToken($csrf_token)) {
        $error = 'Token không hợp lệ. Vui lòng thử lại.';
    } elseif (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp.';
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
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['user'] = [
                            'id' => $user_id,
                            'username' => $username,
                            'email' => $email,
                            'full_name' => $full_name,
                            'role' => 'user'
                        ];
                        
                        // Chuyển về trang chủ sau 2 giây
                        header('refresh:2;url=/linh2store/');
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
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
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
                        placeholder="Nhập mật khẩu (ít nhất 6 ký tự)"
                        required
                        minlength="6"
                    >
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
        // Kiểm tra mật khẩu xác nhận
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Mật khẩu xác nhận không khớp');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Kiểm tra khi thay đổi mật khẩu chính
        document.getElementById('password').addEventListener('input', function() {
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword.value) {
                confirmPassword.dispatchEvent(new Event('input'));
            }
        });
    </script>
</body>
</html>
