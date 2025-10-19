<?php
/**
 * Chi tiết bài viết blog
 * Linh2Store - Blog Detail Page
 */

require_once '../config/auth-middleware.php';
require_once '../config/blog.php';
require_once '../config/image-helper.php';

$blogManager = new BlogManager();
$post = null;
$relatedPosts = [];
$comments = [];
$isLiked = false;

// Lấy slug từ URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

try {
    // Lấy bài viết theo slug
    $post = $blogManager->getPostBySlug($slug);
    
    if (!$post) {
        header('Location: index.php');
        exit;
    }
    
    // Tăng lượt xem
    $blogManager->incrementViewCount($post['id']);
    
    // Lấy bài viết liên quan
    $relatedPosts = $blogManager->getRelatedPosts($post['id'], 4);
    
    // Lấy comments
    $comments = $blogManager->getComments($post['id']);
    
    // Kiểm tra user đã like chưa
    if (AuthMiddleware::isLoggedIn()) {
        $user = AuthMiddleware::getCurrentUser();
        $isLiked = $blogManager->isPostLiked($post['id'], $user['id']);
    }
    
} catch (Exception $e) {
    header('Location: index.php');
    exit;
}

// SEO Meta
$metaTitle = $post['meta_title'] ?: $post['title'];
$metaDescription = $post['meta_description'] ?: $post['excerpt'];
$metaKeywords = $post['meta_keywords'] ?: '';
$ogImage = $post['og_image'] ?: $post['featured_image'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($metaTitle); ?> - Linh2Store</title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($metaKeywords); ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($metaTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($ogImage); ?>">
    <meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:type" content="article">
    
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
                        <a href="index.php" class="nav-link">Blog</a>
                        <a href="../lien-he/" class="nav-link">Liên hệ</a>
                    </nav>
                    
                    <div class="user-actions">
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
            <a href="index.php">Blog</a>
            <span>/</span>
            <span><?php echo htmlspecialchars($post['title']); ?></span>
        </div>
    </div>

    <!-- Article Content -->
    <section class="article-content">
        <div class="container">
            <div class="row">
                <div class="col-8">
                    <article class="blog-article">
                        <!-- Article Header -->
                        <header class="article-header">
                            <div class="article-meta">
                                <span class="article-category"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                <span class="article-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($post['created_at'])); ?>
                                </span>
                                <span class="article-author">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($post['author_name']); ?>
                                </span>
                                <span class="article-views">
                                    <i class="fas fa-eye"></i>
                                    <?php echo $post['view_count']; ?> lượt xem
                                </span>
                            </div>
                            
                            <h1 class="article-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                            
                            <div class="article-excerpt">
                                <?php echo htmlspecialchars($post['excerpt']); ?>
                            </div>
                        </header>
                        
                        <!-- Featured Image -->
                        <?php if ($post['featured_image']): ?>
                            <div class="article-featured-image">
                                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($post['title']); ?>">
                            </div>
                        <?php endif; ?>
                        
                        <!-- Article Content -->
                        <div class="article-body">
                            <?php echo $post['content']; ?>
                        </div>
                        
                        <!-- Article Actions -->
                        <div class="article-actions">
                            <div class="action-buttons">
                                <button class="action-btn like-btn <?php echo $isLiked ? 'liked' : ''; ?>" 
                                        data-post-id="<?php echo $post['id']; ?>">
                                    <i class="fas fa-heart"></i>
                                    <span class="like-count"><?php echo $post['like_count']; ?></span>
                                </button>
                                
                                <button class="action-btn share-btn" onclick="sharePost()">
                                    <i class="fas fa-share"></i>
                                    Chia sẻ
                                </button>
                                
                                <button class="action-btn copy-btn" onclick="copyLink()">
                                    <i class="fas fa-link"></i>
                                    Copy link
                                </button>
                            </div>
                            
                            <div class="share-buttons">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" class="share-link facebook">
                                    <i class="fab fa-facebook"></i>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['title']); ?>" 
                                   target="_blank" class="share-link twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" 
                                   target="_blank" class="share-link linkedin">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Comments Section -->
                        <div class="comments-section">
                            <h3>Bình luận (<?php echo count($comments); ?>)</h3>
                            
                            <?php if (AuthMiddleware::isLoggedIn()): ?>
                                <form class="comment-form" id="comment-form">
                                    <div class="comment-input">
                                        <textarea name="content" placeholder="Viết bình luận của bạn..." required></textarea>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i>
                                            Gửi bình luận
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="login-prompt">
                                    <p>Vui lòng <a href="../auth/dang-nhap.php">đăng nhập</a> để bình luận</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="comments-list" id="comments-list">
                                <?php foreach ($comments as $comment): ?>
                                    <div class="comment-item">
                                        <div class="comment-avatar">
                                            <img src="<?php echo $comment['avatar'] ?: '../images/product_1.jpg'; ?>" 
                                                 alt="<?php echo htmlspecialchars($comment['username']); ?>">
                                        </div>
                                        <div class="comment-content">
                                            <div class="comment-header">
                                                <span class="comment-author"><?php echo htmlspecialchars($comment['username']); ?></span>
                                                <span class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></span>
                                            </div>
                                            <div class="comment-text">
                                                <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Replies -->
                                    <?php if (!empty($comment['replies'])): ?>
                                        <div class="comment-replies">
                                            <?php foreach ($comment['replies'] as $reply): ?>
                                                <div class="comment-item reply">
                                                    <div class="comment-avatar">
                                                        <img src="<?php echo $reply['avatar'] ?: '../images/product_1.jpg'; ?>" 
                                                             alt="<?php echo htmlspecialchars($reply['username']); ?>">
                                                    </div>
                                                    <div class="comment-content">
                                                        <div class="comment-header">
                                                            <span class="comment-author"><?php echo htmlspecialchars($reply['username']); ?></span>
                                                            <span class="comment-date"><?php echo date('d/m/Y H:i', strtotime($reply['created_at'])); ?></span>
                                                        </div>
                                                        <div class="comment-text">
                                                            <?php echo nl2br(htmlspecialchars($reply['content'])); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </article>
                </div>
                
                <div class="col-4">
                    <!-- Sidebar -->
                    <div class="blog-sidebar">
                        <!-- Related Posts -->
                        <?php if (!empty($relatedPosts)): ?>
                            <div class="sidebar-widget">
                                <h3>Bài viết liên quan</h3>
                                <div class="related-posts">
                                    <?php foreach ($relatedPosts as $relatedPost): ?>
                                        <div class="related-post">
                                            <img src="<?php echo $relatedPost['featured_image'] ?: '../images/product_1.jpg'; ?>" 
                                                 alt="<?php echo htmlspecialchars($relatedPost['title']); ?>">
                                            <div class="related-post-info">
                                                <h4><a href="chi-tiet.php?slug=<?php echo $relatedPost['slug']; ?>">
                                                    <?php echo htmlspecialchars($relatedPost['title']); ?>
                                                </a></h4>
                                                <span class="related-post-date">
                                                    <?php echo date('d/m/Y', strtotime($relatedPost['created_at'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
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
                <p>&copy; 2025 Linh2Store. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/blog.js"></script>
    
    <style>
        .article-content {
            padding: 40px 0;
            background: var(--bg-light);
        }
        
        .blog-article {
            background: var(--white);
            border-radius: 12px;
            padding: 40px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 32px;
        }
        
        .article-header {
            margin-bottom: 32px;
        }
        
        .article-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 16px;
            font-size: 14px;
            color: var(--text-muted);
        }
        
        .article-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .article-category {
            background: var(--cta-color);
            color: var(--white);
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .article-title {
            font-size: 32px;
            color: var(--text-dark);
            margin-bottom: 16px;
            line-height: 1.3;
        }
        
        .article-excerpt {
            font-size: 18px;
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 24px;
        }
        
        .article-featured-image {
            margin: 32px 0;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .article-featured-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        
        .article-body {
            font-size: 16px;
            line-height: 1.8;
            color: var(--text-dark);
            margin-bottom: 40px;
        }
        
        .article-body h2, .article-body h3, .article-body h4 {
            color: var(--text-dark);
            margin: 24px 0 16px 0;
        }
        
        .article-body p {
            margin-bottom: 16px;
        }
        
        .article-body ul, .article-body ol {
            margin: 16px 0;
            padding-left: 24px;
        }
        
        .article-body li {
            margin-bottom: 8px;
        }
        
        .article-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 0;
            border-top: 1px solid var(--border-color, #e0e0e0);
            border-bottom: 1px solid var(--border-color, #e0e0e0);
            margin-bottom: 40px;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--bg-light);
            color: var(--text-dark);
            border: 1px solid var(--border-color, #e0e0e0);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .action-btn:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }
        
        .action-btn.liked {
            background: var(--cta-color);
            color: var(--white);
            border-color: var(--cta-color);
        }
        
        .share-buttons {
            display: flex;
            gap: 8px;
        }
        
        .share-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: var(--white);
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        
        .share-link:hover {
            transform: translateY(-2px);
        }
        
        .share-link.facebook {
            background: #3b5998;
        }
        
        .share-link.twitter {
            background: #1da1f2;
        }
        
        .share-link.linkedin {
            background: #0077b5;
        }
        
        .comments-section {
            margin-top: 40px;
        }
        
        .comments-section h3 {
            color: var(--text-dark);
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--primary-color);
        }
        
        .comment-form {
            margin-bottom: 32px;
        }
        
        .comment-input {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .comment-input textarea {
            width: 100%;
            min-height: 100px;
            padding: 12px;
            border: 1px solid var(--border-color, #e0e0e0);
            border-radius: 8px;
            resize: vertical;
            font-family: inherit;
        }
        
        .login-prompt {
            text-align: center;
            padding: 20px;
            background: var(--bg-light);
            border-radius: 8px;
            margin-bottom: 32px;
        }
        
        .comments-list {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        
        .comment-item {
            display: flex;
            gap: 16px;
            padding: 16px;
            background: var(--bg-light);
            border-radius: 8px;
        }
        
        .comment-item.reply {
            margin-left: 40px;
            background: var(--white);
            border: 1px solid var(--border-color, #e0e0e0);
        }
        
        .comment-avatar img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .comment-content {
            flex: 1;
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .comment-author {
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .comment-date {
            font-size: 12px;
            color: var(--text-muted);
        }
        
        .comment-text {
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        .comment-replies {
            margin-top: 16px;
        }
        
        .related-posts {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .related-post {
            display: flex;
            gap: 12px;
            padding: 12px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }
        
        .related-post:hover {
            background: var(--bg-light);
        }
        
        .related-post img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }
        
        .related-post-info h4 {
            margin: 0 0 4px 0;
            font-size: 14px;
        }
        
        .related-post-info h4 a {
            color: var(--text-dark);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .related-post-info h4 a:hover {
            color: var(--cta-color);
        }
        
        .related-post-date {
            font-size: 12px;
            color: var(--text-muted);
        }
        
        @media (max-width: 768px) {
            .col-8, .col-4 {
                flex: 0 0 100%;
            }
            
            .blog-article {
                padding: 24px;
            }
            
            .article-title {
                font-size: 24px;
            }
            
            .article-actions {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
            
            .action-buttons {
                flex-wrap: wrap;
            }
            
            .comment-item.reply {
                margin-left: 20px;
            }
        }
    </style>
</body>
</html>
