<?php
/**
 * Trang blog
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/session.php';
require_once '../config/database.php';

$posts = [];
$featured_posts = [];
$categories = [];

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Lấy bài viết nổi bật
    $stmt = $conn->prepare("
        SELECT * FROM blog_posts 
        WHERE status = 'published' AND featured = 1 
        ORDER BY created_at DESC 
        LIMIT 3
    ");
    $stmt->execute();
    $featured_posts = $stmt->fetchAll();
    
    // Lấy tất cả bài viết
    $stmt = $conn->prepare("
        SELECT * FROM blog_posts 
        WHERE status = 'published' 
        ORDER BY created_at DESC 
        LIMIT 12
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll();
    
    // Lấy danh mục blog
    $stmt = $conn->prepare("
        SELECT * FROM blog_categories 
        WHERE status = 'active' 
        ORDER BY name
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
} catch (Exception $e) {
    $posts = [];
    $featured_posts = [];
    $categories = [];
}

// Nếu không có dữ liệu, tạo dữ liệu mẫu
if (empty($posts)) {
    $posts = [
        [
            'id' => 1,
            'title' => 'Xu hướng son môi 2025: Những màu sắc hot nhất',
            'excerpt' => 'Khám phá những xu hướng son môi mới nhất năm 2025, từ màu nude ấm áp đến những tông đỏ rực rỡ.',
            'content' => 'Năm 2025 mang đến những xu hướng son môi vô cùng thú vị...',
            'featured_image' => 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?w=600',
            'created_at' => '2025-01-15 10:00:00',
            'author' => 'Linh2Store Team',
            'category' => 'Xu hướng làm đẹp'
        ],
        [
            'id' => 2,
            'title' => 'Cách chọn son môi phù hợp với tông da',
            'excerpt' => 'Hướng dẫn chi tiết cách chọn màu son môi phù hợp với từng tông da để tôn lên vẻ đẹp tự nhiên.',
            'content' => 'Việc chọn đúng màu son môi có thể thay đổi hoàn toàn gương mặt của bạn...',
            'featured_image' => 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c0?w=600',
            'created_at' => '2025-01-14 14:30:00',
            'author' => 'Chuyên gia làm đẹp',
            'category' => 'Tips làm đẹp'
        ],
        [
            'id' => 3,
            'title' => 'Review top 5 son môi MAC được yêu thích nhất',
            'excerpt' => 'Đánh giá chi tiết 5 màu son môi MAC được các beauty blogger yêu thích nhất.',
            'content' => 'MAC là một trong những thương hiệu son môi được yêu thích nhất...',
            'featured_image' => 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c1?w=600',
            'created_at' => '2025-01-13 09:15:00',
            'author' => 'Beauty Reviewer',
            'category' => 'Review sản phẩm'
        ],
        [
            'id' => 4,
            'title' => 'Cách bảo quản son môi để giữ được lâu',
            'excerpt' => 'Những mẹo hay để bảo quản son môi đúng cách, giúp sản phẩm giữ được chất lượng tốt nhất.',
            'content' => 'Son môi là một sản phẩm mỹ phẩm cần được bảo quản cẩn thận...',
            'featured_image' => 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c2?w=600',
            'created_at' => '2025-01-12 16:45:00',
            'author' => 'Linh2Store Team',
            'category' => 'Chăm sóc da'
        ],
        [
            'id' => 5,
            'title' => 'Son môi cho từng dịp: Công sở, hẹn hò, tiệc tùng',
            'excerpt' => 'Gợi ý màu son môi phù hợp cho từng hoàn cảnh và dịp đặc biệt.',
            'content' => 'Mỗi dịp khác nhau đòi hỏi một phong cách son môi khác nhau...',
            'featured_image' => 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c3?w=600',
            'created_at' => '2025-01-11 11:20:00',
            'author' => 'Stylist',
            'category' => 'Phong cách'
        ],
        [
            'id' => 6,
            'title' => 'So sánh son môi lì vs son môi bóng: Nên chọn loại nào?',
            'excerpt' => 'Phân tích ưu nhược điểm của son môi lì và son môi bóng để bạn có lựa chọn phù hợp.',
            'content' => 'Son môi lì và son môi bóng đều có những ưu điểm riêng...',
            'featured_image' => 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c4?w=600',
            'created_at' => '2025-01-10 13:10:00',
            'author' => 'Makeup Artist',
            'category' => 'Kiến thức'
        ]
    ];
    
    $featured_posts = array_slice($posts, 0, 3);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Linh2Store</title>
    <meta name="description" content="Blog làm đẹp Linh2Store - Chia sẻ những tips, xu hướng và kiến thức về son môi, mỹ phẩm cao cấp.">
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
                        <a href="index.php" class="nav-link active">Blog</a>
                        <a href="../lien-he/" class="nav-link">Liên hệ</a>
                    </nav>
                    
                    <div class="search-bar">
                        <input type="text" class="search-input" placeholder="Tìm kiếm bài viết...">
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
            <span>Blog</span>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="blog-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Blog Làm Đẹp</h1>
                <p>Khám phá những bí quyết làm đẹp, xu hướng mới nhất và tips chăm sóc da</p>
            </div>
        </div>
    </section>

    <!-- Featured Posts -->
    <?php if (!empty($featured_posts)): ?>
    <section class="featured-posts">
        <div class="container">
            <div class="section-header">
                <h2>Bài viết nổi bật</h2>
                <p>Những bài viết được yêu thích nhất</p>
            </div>
            
            <div class="featured-grid">
                <?php foreach ($featured_posts as $index => $post): ?>
                    <div class="featured-post <?php echo $index === 0 ? 'main-post' : ''; ?>">
                        <div class="post-image">
                            <img src="<?php echo $post['featured_image']; ?>" 
                                 alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <div class="post-category"><?php echo htmlspecialchars($post['category']); ?></div>
                        </div>
                        <div class="post-content">
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                            <div class="post-meta">
                                <span class="post-author">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($post['author']); ?>
                                </span>
                                <span class="post-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                                </span>
                            </div>
                            <a href="chi-tiet.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">
                                Đọc thêm
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- All Posts -->
    <section class="all-posts">
        <div class="container">
            <div class="row">
                <div class="col-8">
                    <div class="section-header">
                        <h2>Tất cả bài viết</h2>
                    </div>
                    
                    <div class="posts-grid">
                        <?php if (!empty($posts)): ?>
                            <?php foreach ($posts as $post): ?>
                                <article class="post-card">
                                    <div class="post-image">
                                        <img src="<?php echo $post['featured_image']; ?>" 
                                             alt="<?php echo htmlspecialchars($post['title']); ?>">
                                        <div class="post-category"><?php echo htmlspecialchars($post['category']); ?></div>
                                    </div>
                                    <div class="post-content">
                                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                        <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                                        <div class="post-meta">
                                            <span class="post-author">
                                                <i class="fas fa-user"></i>
                                                <?php echo htmlspecialchars($post['author']); ?>
                                            </span>
                                            <span class="post-date">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                                            </span>
                                        </div>
                                        <a href="chi-tiet.php?id=<?php echo $post['id']; ?>" class="btn btn-outline">
                                            Đọc thêm
                                        </a>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-posts">
                                <p>Chưa có bài viết nào</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-4">
                    <!-- Sidebar -->
                    <div class="blog-sidebar">
                        <!-- Categories -->
                        <div class="sidebar-widget">
                            <h3>Danh mục</h3>
                            <ul class="category-list">
                                <li><a href="#">Xu hướng làm đẹp</a></li>
                                <li><a href="#">Tips làm đẹp</a></li>
                                <li><a href="#">Review sản phẩm</a></li>
                                <li><a href="#">Chăm sóc da</a></li>
                                <li><a href="#">Phong cách</a></li>
                                <li><a href="#">Kiến thức</a></li>
                            </ul>
                        </div>
                        
                        <!-- Recent Posts -->
                        <div class="sidebar-widget">
                            <h3>Bài viết gần đây</h3>
                            <div class="recent-posts">
                                <?php foreach (array_slice($posts, 0, 5) as $post): ?>
                                    <div class="recent-post">
                                        <img src="<?php echo $post['featured_image']; ?>" 
                                             alt="<?php echo htmlspecialchars($post['title']); ?>">
                                        <div class="recent-post-info">
                                            <h4><?php echo htmlspecialchars($post['title']); ?></h4>
                                            <span class="post-date"><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Newsletter -->
                        <div class="sidebar-widget newsletter-widget">
                            <h3>Đăng ký nhận tin</h3>
                            <p>Nhận những bài viết mới nhất về làm đẹp</p>
                            <form class="newsletter-form">
                                <input type="email" placeholder="Nhập email của bạn">
                                <button type="submit" class="btn btn-primary">Đăng ký</button>
                            </form>
                        </div>
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
                        <li><a href="../lien-he/">Liên hệ</a></li>
                        <li><a href="../huong-dan/">Hướng dẫn mua hàng</a></li>
                        <li><a href="../doi-tra/">Chính sách đổi trả</a></li>
                        <li><a href="../bao-mat/">Bảo mật thông tin</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Liên hệ</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Đường ABC, Quận 1, TP.HCM</li>
                        <li><i class="fas fa-phone"></i> 1900 1234</li>
                        <li><i class="fas fa-envelope"></i> info@linh2store.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Linh2Store. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    
    <style>
        .blog-hero {
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
        
        .featured-posts {
            padding: var(--spacing-3xl) 0;
            background: var(--white);
        }
        
        .all-posts {
            padding: var(--spacing-3xl) 0;
            background: var(--bg-light);
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
        
        .featured-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: var(--spacing-xl);
            height: 500px;
        }
        
        .featured-post {
            position: relative;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: transform var(--transition-normal);
        }
        
        .featured-post:hover {
            transform: translateY(-4px);
        }
        
        .featured-post.main-post {
            grid-row: span 2;
        }
        
        .post-image {
            position: relative;
            height: 100%;
        }
        
        .post-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .post-category {
            position: absolute;
            top: var(--spacing-md);
            left: var(--spacing-md);
            background: var(--cta-color);
            color: var(--white);
            padding: var(--spacing-xs) var(--spacing-sm);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-sm);
            font-weight: 500;
        }
        
        .post-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: var(--white);
            padding: var(--spacing-xl);
        }
        
        .post-content h3 {
            color: var(--white);
            margin-bottom: var(--spacing-sm);
            font-size: var(--font-size-lg);
        }
        
        .post-content p {
            color: rgba(255,255,255,0.9);
            margin-bottom: var(--spacing-md);
            font-size: var(--font-size-sm);
        }
        
        .post-meta {
            display: flex;
            gap: var(--spacing-md);
            margin-bottom: var(--spacing-md);
            font-size: var(--font-size-sm);
        }
        
        .post-meta span {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            color: rgba(255,255,255,0.8);
        }
        
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-xl);
        }
        
        .post-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all var(--transition-normal);
        }
        
        .post-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        
        .post-card .post-image {
            height: 200px;
        }
        
        .post-card .post-content {
            position: static;
            background: var(--white);
            color: var(--text-dark);
            padding: var(--spacing-lg);
        }
        
        .post-card .post-content h3 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-sm);
        }
        
        .post-card .post-content p {
            color: var(--text-light);
            margin-bottom: var(--spacing-md);
        }
        
        .post-card .post-meta {
            margin-bottom: var(--spacing-md);
        }
        
        .post-card .post-meta span {
            color: var(--text-muted);
        }
        
        .blog-sidebar {
            position: sticky;
            top: var(--spacing-xl);
        }
        
        .sidebar-widget {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--spacing-xl);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-sm);
        }
        
        .sidebar-widget h3 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .category-list {
            list-style: none;
            padding: 0;
        }
        
        .category-list li {
            margin-bottom: var(--spacing-sm);
        }
        
        .category-list a {
            color: var(--text-light);
            text-decoration: none;
            transition: color var(--transition-fast);
        }
        
        .category-list a:hover {
            color: var(--cta-color);
        }
        
        .recent-posts {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        .recent-post {
            display: flex;
            gap: var(--spacing-md);
            padding: var(--spacing-sm);
            border-radius: var(--radius-md);
            transition: background var(--transition-fast);
        }
        
        .recent-post:hover {
            background: var(--primary-color);
        }
        
        .recent-post img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--radius-sm);
        }
        
        .recent-post-info h4 {
            font-size: var(--font-size-sm);
            color: var(--text-dark);
            margin: 0 0 var(--spacing-xs) 0;
        }
        
        .recent-post-info .post-date {
            font-size: var(--font-size-xs);
            color: var(--text-muted);
        }
        
        .newsletter-widget {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: var(--text-dark);
        }
        
        .newsletter-form {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }
        
        .newsletter-form input {
            padding: var(--spacing-sm) var(--spacing-md);
            border: 1px solid var(--white);
            border-radius: var(--radius-sm);
            background: var(--white);
        }
        
        .no-posts {
            text-align: center;
            padding: var(--spacing-3xl);
            color: var(--text-light);
        }
        
        @media (max-width: 768px) {
            .col-8, .col-4 {
                flex: 0 0 100%;
            }
            
            .featured-grid {
                grid-template-columns: 1fr;
                height: auto;
            }
            
            .featured-post.main-post {
                grid-row: span 1;
            }
            
            .posts-grid {
                grid-template-columns: 1fr;
            }
            
            .blog-sidebar {
                position: static;
                margin-top: var(--spacing-xl);
            }
        }
    </style>
</body>
</html>
