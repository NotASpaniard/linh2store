<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/dang-nhap.php');
    exit;
}

$product_id = $_GET['id'] ?? '';
if (empty($product_id)) {
    header('Location: ../san-pham/');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Lấy thông tin sản phẩm
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: ../san-pham/');
    exit;
}

// Kiểm tra xem user đã mua và nhận hàng sản phẩm này chưa
$stmt = $conn->prepare("
    SELECT COUNT(*) as has_purchased 
    FROM order_items oi 
    JOIN orders o ON oi.order_id = o.id 
    WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'
");
$stmt->execute([$_SESSION['user_id'], $product_id]);
$has_purchased = $stmt->fetch()['has_purchased'] > 0;

// Kiểm tra xem user đã đánh giá chưa
$stmt = $conn->prepare("SELECT * FROM product_reviews WHERE user_id = ? AND product_id = ?");
$stmt->execute([$_SESSION['user_id'], $product_id]);
$existing_review = $stmt->fetch();

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'] ?? 0;
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    $errors = [];
    
    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Vui lòng chọn đánh giá từ 1-5 sao';
    }
    
    if (empty($title)) {
        $errors[] = 'Tiêu đề không được để trống';
    }
    
    if (empty($content)) {
        $errors[] = 'Nội dung đánh giá không được để trống';
    }
    
    if (empty($errors)) {
        try {
            if ($existing_review) {
                // Cập nhật review cũ
                $stmt = $conn->prepare("
                    UPDATE product_reviews 
                    SET rating = ?, title = ?, content = ?, is_approved = 0, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$rating, $title, $content, $existing_review['id']]);
                $success_message = 'Đã cập nhật đánh giá của bạn';
            } else {
                // Tạo review mới
                $stmt = $conn->prepare("
                    INSERT INTO product_reviews (product_id, user_id, rating, title, content, is_verified_purchase, is_approved)
                    VALUES (?, ?, ?, ?, ?, ?, 0)
                ");
                $stmt->execute([$product_id, $_SESSION['user_id'], $rating, $title, $content, $has_purchased]);
                $success_message = 'Đã gửi đánh giá của bạn. Đánh giá sẽ được kiểm duyệt trước khi hiển thị.';
            }
            
            // Cập nhật lại product_ratings
            $stmt = $conn->prepare("
                INSERT INTO product_ratings (product_id, average_rating, total_reviews, rating_1, rating_2, rating_3, rating_4, rating_5)
                SELECT 
                    product_id,
                    ROUND(AVG(rating), 2) as average_rating,
                    COUNT(*) as total_reviews,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5
                FROM product_reviews 
                WHERE product_id = ? AND is_approved = 1
                GROUP BY product_id
                ON DUPLICATE KEY UPDATE
                    average_rating = VALUES(average_rating),
                    total_reviews = VALUES(total_reviews),
                    rating_1 = VALUES(rating_1),
                    rating_2 = VALUES(rating_2),
                    rating_3 = VALUES(rating_3),
                    rating_4 = VALUES(rating_4),
                    rating_5 = VALUES(rating_5)
            ");
            $stmt->execute([$product_id]);
            
        } catch (Exception $e) {
            $error_message = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Lấy thông tin rating của sản phẩm
$stmt = $conn->prepare("SELECT * FROM product_ratings WHERE product_id = ?");
$stmt->execute([$product_id]);
$product_rating = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá sản phẩm - <?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .review-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .product-info {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .product-header {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .product-details h2 {
            margin: 0 0 10px 0;
            color: var(--primary-color);
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }
        
        .stars {
            color: #ffc107;
            font-size: 1.2em;
        }
        
        .rating-summary {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .rating-breakdown {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .rating-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 5px 0;
        }
        
        .rating-label {
            width: 20px;
            text-align: center;
        }
        
        .rating-progress {
            flex: 1;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .rating-fill {
            height: 100%;
            background: #ffc107;
            transition: width 0.3s ease;
        }
        
        .review-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .rating-input {
            display: flex;
            gap: 5px;
            margin: 15px 0;
        }
        
        .rating-input input[type="radio"] {
            display: none;
        }
        
        .rating-input label {
            font-size: 2em;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .rating-input label:hover,
        .rating-input input:checked ~ label,
        .rating-input label:hover ~ label {
            color: #ffc107;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .verified-badge {
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        
        .purchase-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="review-container">
        <h1>Đánh giá sản phẩm</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <!-- Thông tin sản phẩm -->
        <div class="product-info">
            <div class="product-header">
                <img src="../<?php echo getProductImage($product['id']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                <div class="product-details">
                    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                    <div class="product-rating">
                        <div class="stars">
                            <?php 
                            $avg_rating = $product_rating['average_rating'] ?? 0;
                            for ($i = 1; $i <= 5; $i++): 
                            ?>
                                <i class="fas fa-star<?php echo $i <= $avg_rating ? '' : '-o'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span><?php echo number_format($avg_rating, 1); ?>/5</span>
                        <span>(<?php echo $product_rating['total_reviews'] ?? 0; ?> đánh giá)</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tóm tắt đánh giá -->
        <?php if ($product_rating): ?>
        <div class="rating-summary">
            <h3>Tóm tắt đánh giá</h3>
            <div class="rating-breakdown">
                <div>
                    <div class="rating-bar">
                        <span class="rating-label">5★</span>
                        <div class="rating-progress">
                            <div class="rating-fill" style="width: <?php echo $product_rating['total_reviews'] > 0 ? ($product_rating['rating_5'] / $product_rating['total_reviews'] * 100) : 0; ?>%"></div>
                        </div>
                        <span><?php echo $product_rating['rating_5']; ?></span>
                    </div>
                    <div class="rating-bar">
                        <span class="rating-label">4★</span>
                        <div class="rating-progress">
                            <div class="rating-fill" style="width: <?php echo $product_rating['total_reviews'] > 0 ? ($product_rating['rating_4'] / $product_rating['total_reviews'] * 100) : 0; ?>%"></div>
                        </div>
                        <span><?php echo $product_rating['rating_4']; ?></span>
                    </div>
                    <div class="rating-bar">
                        <span class="rating-label">3★</span>
                        <div class="rating-progress">
                            <div class="rating-fill" style="width: <?php echo $product_rating['total_reviews'] > 0 ? ($product_rating['rating_3'] / $product_rating['total_reviews'] * 100) : 0; ?>%"></div>
                        </div>
                        <span><?php echo $product_rating['rating_3']; ?></span>
                    </div>
                    <div class="rating-bar">
                        <span class="rating-label">2★</span>
                        <div class="rating-progress">
                            <div class="rating-fill" style="width: <?php echo $product_rating['total_reviews'] > 0 ? ($product_rating['rating_2'] / $product_rating['total_reviews'] * 100) : 0; ?>%"></div>
                        </div>
                        <span><?php echo $product_rating['rating_2']; ?></span>
                    </div>
                    <div class="rating-bar">
                        <span class="rating-label">1★</span>
                        <div class="rating-progress">
                            <div class="rating-fill" style="width: <?php echo $product_rating['total_reviews'] > 0 ? ($product_rating['rating_1'] / $product_rating['total_reviews'] * 100) : 0; ?>%"></div>
                        </div>
                        <span><?php echo $product_rating['rating_1']; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Form đánh giá -->
        <div class="review-form">
            <h3><?php echo $existing_review ? 'Cập nhật đánh giá của bạn' : 'Viết đánh giá'; ?></h3>
            
            <?php if (!$has_purchased): ?>
                <div class="purchase-notice" style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Bạn chưa thể đánh giá sản phẩm này!</strong><br>
                    Chỉ những khách hàng đã mua và nhận hàng mới có thể viết đánh giá. 
                    <a href="../san-pham/" style="color: #856404; text-decoration: underline;">Mua sản phẩm ngay</a>
                </div>
            <?php endif; ?>
            
            <?php if ($has_purchased): ?>
            <form method="POST">
                <div class="form-group">
                    <label>Đánh giá của bạn *</label>
                    <div class="rating-input">
                        <input type="radio" id="star5" name="rating" value="5" <?php echo ($existing_review['rating'] ?? 0) == 5 ? 'checked' : ''; ?>>
                        <label for="star5">★</label>
                        <input type="radio" id="star4" name="rating" value="4" <?php echo ($existing_review['rating'] ?? 0) == 4 ? 'checked' : ''; ?>>
                        <label for="star4">★</label>
                        <input type="radio" id="star3" name="rating" value="3" <?php echo ($existing_review['rating'] ?? 0) == 3 ? 'checked' : ''; ?>>
                        <label for="star3">★</label>
                        <input type="radio" id="star2" name="rating" value="2" <?php echo ($existing_review['rating'] ?? 0) == 2 ? 'checked' : ''; ?>>
                        <label for="star2">★</label>
                        <input type="radio" id="star1" name="rating" value="1" <?php echo ($existing_review['rating'] ?? 0) == 1 ? 'checked' : ''; ?>>
                        <label for="star1">★</label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="title">Tiêu đề đánh giá *</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($existing_review['title'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="content">Nội dung đánh giá *</label>
                    <textarea id="content" name="content" placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm..." required><?php echo htmlspecialchars($existing_review['content'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-star"></i>
                        <?php echo $existing_review ? 'Cập nhật đánh giá' : 'Gửi đánh giá'; ?>
                    </button>
                    <a href="chi-tiet.php?id=<?php echo $product_id; ?>" class="btn btn-secondary">Quay lại</a>
                </div>
            </form>
            <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <i class="fas fa-lock" style="font-size: 3em; margin-bottom: 20px; color: #ccc;"></i>
                <h4>Bạn cần mua và nhận hàng trước khi đánh giá</h4>
                <p>Chỉ khách hàng đã mua và nhận hàng mới có thể viết đánh giá sản phẩm.</p>
                <a href="../san-pham/" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-shopping-cart"></i> Mua sản phẩm
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
