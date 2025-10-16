<?php
/**
 * Trang đăng nhập
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/database.php';
require_once '../config/auth-middleware.php';
require_once '../config/oauth.php';

// Nếu đã đăng nhập, chuyển về trang chủ
AuthMiddleware::requireGuest();

$error = '';
$success = '';

// Xử lý lỗi từ OAuth callback
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Kiểm tra CSRF token
    if (!AuthMiddleware::verifyCSRFToken($csrf_token)) {
        $error = 'Token không hợp lệ. Vui lòng thử lại.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin.';
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Tìm user theo username hoặc email
            $stmt = $conn->prepare("
                SELECT id, username, email, password, full_name, role, status 
                FROM users 
                WHERE (username = ? OR email = ?) AND status = 'active'
            ");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Đăng nhập thành công - sử dụng JWT
                AuthMiddleware::loginUser($user);
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
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
    <title>Đăng nhập - Linh2Store</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1><a href="../" class="logo">Linh2Store</a></h1>
                <h2>Đăng nhập tài khoản</h2>
                <p>Chào mừng bạn quay trở lại!</p>
            </div>
            
            <!-- OAuth Login Options -->
            <div class="oauth-section">
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
                    <span>Hoặc đăng nhập bằng tài khoản</span>
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
                    <label for="username" class="form-label">
                        <i class="fas fa-user"></i> Tên đăng nhập hoặc Email
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        placeholder="Nhập tên đăng nhập hoặc email"
                        value="<?php echo htmlspecialchars($username ?? ''); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Mật khẩu
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Nhập mật khẩu"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1">
                        <span class="checkmark"></span>
                        Ghi nhớ đăng nhập
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-sign-in-alt"></i>
                    Đăng nhập
                </button>
            </form>
            
            <div class="auth-links">
                <a href="quen-mat-khau.php" class="auth-link">
                    <i class="fas fa-key"></i>
                    Quên mật khẩu?
                </a>
                <a href="dang-ky.php" class="auth-link">
                    <i class="fas fa-user-plus"></i>
                    Chưa có tài khoản? Đăng ký ngay
                </a>
            </div>
        </div>
    </div>
    
    <style>
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-md);
        }
        
        .auth-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            padding: var(--spacing-3xl);
            width: 100%;
            max-width: 400px;
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }
        
        .auth-header .logo {
            font-size: var(--font-size-2xl);
            color: var(--cta-color);
            text-decoration: none;
            font-weight: 700;
        }
        
        .auth-header h2 {
            color: var(--text-dark);
            margin: var(--spacing-md) 0 var(--spacing-sm);
        }
        
        .auth-header p {
            color: var(--text-light);
            margin: 0;
        }
        
        .auth-form {
            margin-bottom: var(--spacing-xl);
        }
        
        .form-group {
            margin-bottom: var(--spacing-lg);
        }
        
        .form-label {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-sm);
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .form-input {
            width: 100%;
            padding: var(--spacing-md);
            border: 2px solid var(--primary-color);
            border-radius: var(--radius-md);
            font-size: var(--font-size-base);
            transition: border-color var(--transition-fast);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--cta-color);
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            cursor: pointer;
            font-size: var(--font-size-sm);
            color: var(--text-light);
        }
        
        .checkbox-label input[type="checkbox"] {
            display: none;
        }
        
        .checkmark {
            width: 20px;
            height: 20px;
            border: 2px solid var(--primary-color);
            border-radius: var(--radius-sm);
            position: relative;
            transition: all var(--transition-fast);
        }
        
        .checkbox-label input[type="checkbox"]:checked + .checkmark {
            background: var(--cta-color);
            border-color: var(--cta-color);
        }
        
        .checkbox-label input[type="checkbox"]:checked + .checkmark::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: var(--white);
            font-size: 12px;
            font-weight: bold;
        }
        
        .btn-lg {
            padding: var(--spacing-md) var(--spacing-xl);
            font-size: var(--font-size-lg);
            font-weight: 600;
        }
        
        .w-100 {
            width: 100%;
        }
        
        .auth-links {
            text-align: center;
            border-top: 1px solid var(--primary-color);
            padding-top: var(--spacing-lg);
        }
        
        .auth-link {
            display: block;
            color: var(--text-light);
            text-decoration: none;
            padding: var(--spacing-sm) 0;
            transition: color var(--transition-fast);
        }
        
        .auth-link:hover {
            color: var(--cta-color);
        }
        
        .auth-link i {
            margin-right: var(--spacing-sm);
        }
        
        @media (max-width: 480px) {
            .auth-card {
                padding: var(--spacing-xl);
                margin: var(--spacing-md);
            }
        }
        
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
        
        @media (max-width: 480px) {
            .oauth-buttons {
                flex-direction: column;
            }
        }
        </style>
</body>
</html>
