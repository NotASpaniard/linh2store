<?php
/**
 * Trang liên hệ
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/session.php';

$success_message = '';
$error_message = '';

// Xử lý form liên hệ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Email không hợp lệ.';
    } else {
        // Ở đây có thể gửi email hoặc lưu vào database
        $success_message = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - Linh2Store</title>
    <meta name="description" content="Liên hệ với Linh2Store - Cửa hàng son môi và mỹ phẩm cao cấp. Hotline: 1900 1234, Email: info@linh2store.com">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="row justify-between align-center">
                    <div class="col">
                        <p><i class="fas fa-phone"></i> Hotline: 1900 1234</p>
                    </div>
                    <div class="col">
                        <p><i class="fas fa-truck"></i> Miễn phí ship đơn từ 500k</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="header-main">
            <div class="container">
                <div class="header-content">
                    <a href="../" class="logo">Linh2Store</a>
                    
                    <nav class="nav">
                        <a href="../" class="nav-link">Trang chủ</a>
                        <a href="../san-pham/" class="nav-link">Sản phẩm</a>
                        <a href="../thuong-hieu/" class="nav-link">Thương hiệu</a>
                        <a href="../blog/" class="nav-link">Blog</a>
                        <a href="index.php" class="nav-link active">Liên hệ</a>
                    </nav>
                    
                    <div class="search-bar">
                        <input type="text" class="search-input" placeholder="Tìm kiếm sản phẩm...">
                        <button class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    
                    <div class="user-actions">
                        <?php if (isLoggedIn()): ?>
                            <a href="../user/" class="user-icon" title="Tài khoản">
                                <i class="fas fa-user"></i>
                            </a>
                        <?php else: ?>
                            <a href="../auth/dang-nhap.php" class="user-icon" title="Đăng nhập">
                                <i class="fas fa-user"></i>
                            </a>
                        <?php endif; ?>
                        
                        <a href="../gio-hang/" class="cart-icon" title="Giỏ hàng">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">0</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="../">Trang chủ</a>
            <span>/</span>
            <span>Liên hệ</span>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Liên hệ với chúng tôi</h1>
                <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
            </div>
        </div>
    </section>

    <!-- Contact Info & Form -->
    <section class="contact-section">
        <div class="container">
            <div class="row">
                <div class="col-6">
                    <!-- Contact Form -->
                    <div class="contact-form-container">
                        <h2>Gửi tin nhắn cho chúng tôi</h2>
                        <p>Điền thông tin bên dưới và chúng tôi sẽ liên hệ lại với bạn</p>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" class="contact-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name" class="form-label">Họ và tên *</label>
                                    <input type="text" id="name" name="name" class="form-input" 
                                           value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" id="email" name="email" class="form-input" 
                                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="tel" id="phone" name="phone" class="form-input" 
                                           value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="subject" class="form-label">Chủ đề *</label>
                                    <select id="subject" name="subject" class="form-input" required>
                                        <option value="">Chọn chủ đề</option>
                                        <option value="Hỗ trợ sản phẩm" <?php echo ($subject ?? '') === 'Hỗ trợ sản phẩm' ? 'selected' : ''; ?>>Hỗ trợ sản phẩm</option>
                                        <option value="Đơn hàng" <?php echo ($subject ?? '') === 'Đơn hàng' ? 'selected' : ''; ?>>Đơn hàng</option>
                                        <option value="Đổi trả" <?php echo ($subject ?? '') === 'Đổi trả' ? 'selected' : ''; ?>>Đổi trả</option>
                                        <option value="Góp ý" <?php echo ($subject ?? '') === 'Góp ý' ? 'selected' : ''; ?>>Góp ý</option>
                                        <option value="Khác" <?php echo ($subject ?? '') === 'Khác' ? 'selected' : ''; ?>>Khác</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="message" class="form-label">Nội dung *</label>
                                <textarea id="message" name="message" class="form-input form-textarea" 
                                          rows="5" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i>
                                Gửi tin nhắn
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="col-6">
                    <!-- Contact Info -->
                    <div class="contact-info">
                        <h2>Thông tin liên hệ</h2>
                        <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn mọi lúc</p>
                        
                        <div class="contact-items">
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-details">
                                    <h3>Địa chỉ</h3>
                                    <p>123 Đường ABC, Quận 1<br>TP. Hồ Chí Minh, Việt Nam</p>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="contact-details">
                                    <h3>Điện thoại</h3>
                                    <p>Hotline: 1900 1234<br>Mobile: 0909 123 456</p>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-details">
                                    <h3>Email</h3>
                                    <p>info@linh2store.com<br>support@linh2store.com</p>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="contact-details">
                                    <h3>Giờ làm việc</h3>
                                    <p>Thứ 2 - Thứ 6: 8:00 - 18:00<br>Thứ 7 - Chủ nhật: 9:00 - 17:00</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Social Media -->
                        <div class="social-section">
                            <h3>Theo dõi chúng tôi</h3>
                            <div class="social-links">
                                <a href="#" class="social-link facebook">
                                    <i class="fab fa-facebook-f"></i>
                                    <span>Facebook</span>
                                </a>
                                <a href="#" class="social-link instagram">
                                    <i class="fab fa-instagram"></i>
                                    <span>Instagram</span>
                                </a>
                                <a href="#" class="social-link youtube">
                                    <i class="fab fa-youtube"></i>
                                    <span>YouTube</span>
                                </a>
                                <a href="#" class="social-link tiktok">
                                    <i class="fab fa-tiktok"></i>
                                    <span>TikTok</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <h2>Vị trí cửa hàng</h2>
            <div class="map-container">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.3253119128!2d106.700423315334!3d10.776611692315!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f4b90b63bd9%3A0x308d2b4b3a3b3b3b!2sQu%E1%BA%ADn%201%2C%20H%E1%BB%93%20Ch%C3%AD%20Minh!5e0!3m2!1svi!2s!4v1640000000000!5m2!1svi!2s" 
                    width="100%" 
                    height="400" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div class="section-header">
                <h2>Câu hỏi thường gặp</h2>
                <p>Những câu hỏi được khách hàng quan tâm nhất</p>
            </div>
            
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Làm thế nào để đặt hàng?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Bạn có thể đặt hàng trực tiếp trên website hoặc gọi hotline 1900 1234. Chúng tôi sẽ hỗ trợ bạn trong suốt quá trình đặt hàng.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Thời gian giao hàng là bao lâu?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Đối với khu vực TP.HCM: 1-2 ngày làm việc. Các tỉnh thành khác: 3-5 ngày làm việc. Miễn phí ship cho đơn hàng từ 500.000đ.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Chính sách đổi trả như thế nào?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Chúng tôi hỗ trợ đổi trả trong vòng 7 ngày kể từ ngày nhận hàng. Sản phẩm phải còn nguyên vẹn, chưa sử dụng.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Sản phẩm có chính hãng không?</h3>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>100% sản phẩm tại Linh2Store đều chính hãng, có đầy đủ giấy tờ chứng minh nguồn gốc xuất xứ.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Linh2Store</h3>
                    <p>Website bán son môi & mỹ phẩm cao cấp dành cho phụ nữ hiện đại.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Danh mục</h3>
                    <ul>
                        <li><a href="../san-pham/">Tất cả sản phẩm</a></li>
                        <li><a href="../san-pham/son-moi/">Son môi</a></li>
                        <li><a href="../san-pham/son-kem/">Son kem</a></li>
                        <li><a href="../san-pham/son-thoi/">Son thỏi</a></li>
                        <li><a href="../san-pham/son-nuoc/">Son nước</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Hỗ trợ</h3>
                    <ul>
                        <li><a href="index.php">Liên hệ</a></li>
                        <li><a href="../huong-dan/">Hướng dẫn mua hàng</a></li>
                        <li><a href="../doi-tra/">Chính sách đổi trả</a></li>
                        <li><a href="../bao-mat/">Bảo mật thông tin</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Liên hệ</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> 8910 Đường JQK, Quận A, Sảnh Rồng</li>
                        <li><i class="fas fa-phone"></i> 1900 1234</li>
                        <li><i class="fas fa-envelope"></i> info@linh2store.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Linh2Store. ...</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    
    <script>
        // FAQ Toggle
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', function() {
                const faqItem = this.parentElement;
                const answer = faqItem.querySelector('.faq-answer');
                const icon = this.querySelector('i');
                
                faqItem.classList.toggle('active');
                
                if (faqItem.classList.contains('active')) {
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    answer.style.maxHeight = '0';
                    icon.style.transform = 'rotate(0deg)';
                }
            });
        });
    </script>
    
    <style>
        .contact-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            padding: var(--spacing-3xl) 0;
            text-align: center;
        }
        
        .hero-content h1 {
            font-size: var(--font-size-3xl);
            color: var(--text-dark);
            margin-bottom: var(--spacing-lg);
        }
        
        .hero-content p {
            font-size: var(--font-size-lg);
            color: var(--text-light);
        }
        
        .contact-section {
            padding: var(--spacing-3xl) 0;
            background: var(--white);
        }
        
        .contact-form-container {
            background: var(--bg-light);
            border-radius: var(--radius-lg);
            padding: var(--spacing-3xl);
            box-shadow: var(--shadow-sm);
        }
        
        .contact-form-container h2 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-sm);
        }
        
        .contact-form-container p {
            color: var(--text-light);
            margin-bottom: var(--spacing-xl);
        }
        
        .form-row {
            display: flex;
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .contact-info {
            padding: var(--spacing-xl);
        }
        
        .contact-info h2 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-sm);
        }
        
        .contact-info > p {
            color: var(--text-light);
            margin-bottom: var(--spacing-xl);
        }
        
        .contact-items {
            margin-bottom: var(--spacing-3xl);
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-xl);
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--cta-color);
            font-size: var(--font-size-lg);
            flex-shrink: 0;
        }
        
        .contact-details h3 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-sm);
        }
        
        .contact-details p {
            color: var(--text-light);
            margin: 0;
        }
        
        .social-section {
            border-top: 1px solid var(--primary-color);
            padding-top: var(--spacing-xl);
        }
        
        .social-section h3 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-lg);
        }
        
        .social-links {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        .social-link {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            text-decoration: none;
            color: var(--text-dark);
            transition: all var(--transition-fast);
        }
        
        .social-link:hover {
            transform: translateX(4px);
        }
        
        .social-link.facebook:hover {
            background: #1877F2;
            color: var(--white);
        }
        
        .social-link.instagram:hover {
            background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);
            color: var(--white);
        }
        
        .social-link.youtube:hover {
            background: #FF0000;
            color: var(--white);
        }
        
        .social-link.tiktok:hover {
            background: #000000;
            color: var(--white);
        }
        
        .map-section {
            padding: var(--spacing-3xl) 0;
            background: var(--bg-light);
        }
        
        .map-section h2 {
            text-align: center;
            color: var(--text-dark);
            margin-bottom: var(--spacing-xl);
        }
        
        .map-container {
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        
        .faq-section {
            padding: var(--spacing-3xl) 0;
            background: var(--white);
        }
        
        .section-header {
            text-align: center;
            margin-bottom: var(--spacing-3xl);
        }
        
        .section-header h2 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-sm);
        }
        
        .section-header p {
            color: var(--text-light);
            font-size: var(--font-size-lg);
        }
        
        .faq-list {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .faq-item {
            background: var(--bg-light);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-lg);
            overflow: hidden;
        }
        
        .faq-question {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-xl);
            cursor: pointer;
            transition: background var(--transition-fast);
        }
        
        .faq-question:hover {
            background: var(--primary-color);
        }
        
        .faq-question h3 {
            color: var(--text-dark);
            margin: 0;
        }
        
        .faq-question i {
            color: var(--cta-color);
            transition: transform var(--transition-fast);
        }
        
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height var(--transition-normal);
        }
        
        .faq-answer p {
            padding: 0 var(--spacing-xl) var(--spacing-xl);
            color: var(--text-light);
            margin: 0;
        }
        
        .faq-item.active .faq-question {
            background: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .col-6 {
                flex: 0 0 100%;
            }
            
            .form-row {
                flex-direction: column;
                gap: var(--spacing-md);
            }
            
            .contact-item {
                flex-direction: column;
                text-align: center;
            }
            
            .social-links {
                flex-direction: row;
                flex-wrap: wrap;
            }
        }
    </style>
</body>
</html>
