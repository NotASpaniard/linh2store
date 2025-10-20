<?php
/**
 * Trang blog
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once '../config/auth-middleware.php';
require_once '../config/blog.php';
require_once '../config/image-helper.php';

$blogManager = new BlogManager();

// Lấy parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$tagId = isset($_GET['tag']) ? (int)$_GET['tag'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;

$limit = 12;
$offset = ($page - 1) * $limit;

try {
    // Lấy dữ liệu từ database
    $featured_posts = $blogManager->getFeaturedPosts(3);
    $posts = $blogManager->getAllPosts($limit, $offset, $categoryId, $tagId, $search);
    $categories = $blogManager->getAllCategories();
    $tags = $blogManager->getAllTags();
    $recent_posts = $blogManager->getRecentPosts(5);
    
    // Tính tổng số bài viết cho pagination
    $allPosts = $blogManager->getAllPosts(1000, 0, $categoryId, $tagId, $search);
    $totalPosts = count($allPosts);
    $totalPages = ceil($totalPosts / $limit);
    
} catch (Exception $e) {
    $posts = [];
    $featured_posts = [];
    $categories = [];
    $tags = [];
    $recent_posts = [];
    $totalPosts = 0;
    $totalPages = 0;
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
                        <p><i class="fas fa-phone"></i> Hotline: 1900 JQKA</p>
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
                        <form method="GET" action="index.php">
                            <input type="text" name="search" class="search-input" placeholder="Tìm kiếm bài viết..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    
                    <div class="user-actions">
                        <button id="theme-toggle" class="theme-toggle" title="Chuyển đổi giao diện">
                            <i class="fas fa-moon"></i>
                        </button>
                        
                        <?php if (AuthMiddleware::isLoggedIn()): ?>
                            <a href="../user/" class="user-icon" title="Tài khoản">
                                <i class="fas fa-user"></i>
                            </a>
                        <?php else: ?>
                            <a href="../auth/dang-nhap.php" class="user-icon" title="Đăng nhập">
                                <i class="fas fa-user"></i>
                            </a>
                        <?php endif; ?>
                        
                        <a href="../thanh-toan/" class="cart-icon" title="Thanh toán">
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
                            <img src="<?php echo $post['featured_image'] ?: '../images/product_1.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <div class="post-category"><?php echo htmlspecialchars($post['category_name']); ?></div>
                        </div>
                        <div class="post-content">
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                            <div class="post-meta">
                                <span class="post-author">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($post['author_name']); ?>
                                </span>
                                <span class="post-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                                </span>
                                <span class="post-views">
                                    <i class="fas fa-eye"></i>
                                    <?php echo $post['view_count']; ?>
                                </span>
                            </div>
                            <a href="chi-tiet.php?slug=<?php echo $post['slug']; ?>" class="btn btn-primary">
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
                                        <img src="<?php echo $post['featured_image'] ?: '../images/product_1.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($post['title']); ?>">
                                        <div class="post-category"><?php echo htmlspecialchars($post['category_name']); ?></div>
                                    </div>
                                    <div class="post-content">
                                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                        <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                                        <div class="post-meta">
                                            <span class="post-author">
                                                <i class="fas fa-user"></i>
                                                <?php echo htmlspecialchars($post['author_name']); ?>
                                            </span>
                                            <span class="post-date">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                                            </span>
                                            <span class="post-views">
                                                <i class="fas fa-eye"></i>
                                                <?php echo $post['view_count']; ?>
                                            </span>
                                        </div>
                                        <a href="chi-tiet.php?slug=<?php echo $post['slug']; ?>" class="btn btn-outline">
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
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $tagId ? '&tag=' . $tagId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i> Trước
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $tagId ? '&tag=' . $tagId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                   class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $tagId ? '&tag=' . $tagId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pagination-btn">
                                    Sau <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-4">
                    <!-- Sidebar -->
                    <div class="blog-sidebar">
                        <!-- Categories -->
                        <div class="sidebar-widget">
                            <h3>Danh mục</h3>
                            <ul class="category-list">
                                <li><a href="index.php">Tất cả (<?php echo $totalPosts; ?>)</a></li>
                                <?php foreach ($categories as $category): ?>
                                    <li>
                                        <a href="index.php?category=<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?> 
                                            (<?php echo $category['post_count']; ?>)
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <!-- Tags -->
                        <div class="sidebar-widget">
                            <h3>Tags phổ biến</h3>
                            <div class="tag-cloud">
                                <?php foreach (array_slice($tags, 0, 10) as $tag): ?>
                                    <a href="index.php?tag=<?php echo $tag['id']; ?>" class="tag-item">
                                        <?php echo htmlspecialchars($tag['name']); ?>
                                        <span class="tag-count">(<?php echo $tag['post_count']; ?>)</span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Recent Posts -->
                        <div class="sidebar-widget">
                            <h3>Bài viết gần đây</h3>
                            <div class="recent-posts">
                                <?php foreach ($recent_posts as $post): ?>
                                    <div class="recent-post">
                                        <img src="<?php echo $post['featured_image'] ?: '../images/product_1.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($post['title']); ?>">
                                        <div class="recent-post-info">
                                            <h4><a href="chi-tiet.php?slug=<?php echo $post['slug']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h4>
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
                        <li><i class="fas fa-map-marker-alt"></i> 8910 Đường JQK, Quận A, Sảnh Rồng</li>
                        <li><i class="fas fa-phone"></i> 1900 JQKA</li>
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
    <script src="../assets/js/blog.js"></script>
    <script>
        // Theme toggle functionality
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update toggle button icon
            const toggleBtn = document.getElementById('theme-toggle');
            if (toggleBtn) {
                const icon = toggleBtn.querySelector('i');
                if (icon) {
                    icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                }
            }
        }
        
        // Load theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            
            // Update toggle button icon
            const toggleBtn = document.getElementById('theme-toggle');
            if (toggleBtn) {
                const icon = toggleBtn.querySelector('i');
                if (icon) {
                    icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                }
            }
            
            // Add click event to toggle button
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleTheme();
                });
            }
        });
    </script>
    
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
            gap: 24px;
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
            gap: 24px;
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
            padding: 24px;
            margin-bottom: 24px;
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
        
        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 32px;
            padding: 20px 0;
        }
        
        .pagination-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: var(--white);
            color: var(--text-dark);
            text-decoration: none;
            border-radius: 8px;
            border: 1px solid var(--border-color, #e0e0e0);
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .pagination-btn:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
        }
        
        .pagination-btn.active {
            background: var(--cta-color);
            color: var(--white);
            border-color: var(--cta-color);
        }
        
        /* Tag Cloud */
        .tag-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .tag-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            background: var(--bg-light);
            color: var(--text-dark);
            text-decoration: none;
            border-radius: 20px;
            font-size: 14px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        
        .tag-item:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(33, 150, 243, 0.2);
        }
        
        .tag-count {
            font-size: 12px;
            opacity: 0.7;
        }
        
        /* Post Views */
        .post-views {
            display: flex;
            align-items: center;
            gap: 4px;
            color: var(--text-muted);
            font-size: 14px;
        }
        
        /* Recent Post Links */
        .recent-post-info h4 a {
            color: var(--text-dark);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .recent-post-info h4 a:hover {
            color: var(--cta-color);
        }
        
        /* Active Category/Tag */
        .category-list a[href*="category="],
        .tag-item[href*="tag="] {
            position: relative;
        }
        
        .category-list a:hover,
        .tag-item:hover {
            color: var(--cta-color);
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
