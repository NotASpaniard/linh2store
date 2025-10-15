<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../auth/dang-nhap.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Xử lý actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $review_id = $_POST['review_id'] ?? '';
    
    try {
        switch ($action) {
            case 'approve':
                $stmt = $conn->prepare("UPDATE product_reviews SET is_approved = 1 WHERE id = ?");
                $stmt->execute([$review_id]);
                $success_message = 'Đã duyệt đánh giá';
                break;
                
            case 'reject':
                $stmt = $conn->prepare("UPDATE product_reviews SET is_approved = 0 WHERE id = ?");
                $stmt->execute([$review_id]);
                $success_message = 'Đã từ chối đánh giá';
                break;
                
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM product_reviews WHERE id = ?");
                $stmt->execute([$review_id]);
                $success_message = 'Đã xóa đánh giá';
                break;
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
            WHERE is_approved = 1
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
        $stmt->execute();
        
    } catch (Exception $e) {
        $error_message = 'Có lỗi xảy ra: ' . $e->getMessage();
    }
}

// Lấy danh sách reviews
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

$where_conditions = [];
$params = [];

if ($filter === 'pending') {
    $where_conditions[] = "pr.is_approved = 0";
} elseif ($filter === 'approved') {
    $where_conditions[] = "pr.is_approved = 1";
}

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR pr.title LIKE ? OR pr.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$sql = "
    SELECT 
        pr.*,
        p.name as product_name,
        u.username,
        u.full_name,
        u.email
    FROM product_reviews pr
    JOIN products p ON pr.product_id = p.id
    JOIN users u ON pr.user_id = u.id
    $where_clause
    ORDER BY pr.created_at DESC
    LIMIT 50
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$reviews = $stmt->fetchAll();

// Thống kê
$stats_sql = "
    SELECT 
        COUNT(*) as total_reviews,
        SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved_reviews,
        SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending_reviews,
        ROUND(AVG(rating), 2) as average_rating
    FROM product_reviews
";
$stmt = $conn->prepare($stats_sql);
$stmt->execute();
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đánh giá - Admin</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .review-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .review-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .review-info {
            flex: 1;
        }
        
        .review-actions {
            display: flex;
            gap: 10px;
        }
        
        .rating-stars {
            color: #ffc107;
            font-size: 1.2em;
        }
        
        .review-content {
            margin: 15px 0;
        }
        
        .review-meta {
            color: #666;
            font-size: 0.9em;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .btn-small {
            padding: 5px 10px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Quản lý đánh giá sản phẩm</h1>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <!-- Thống kê -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_reviews']; ?></div>
                <div>Tổng đánh giá</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['approved_reviews']; ?></div>
                <div>Đã duyệt</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['pending_reviews']; ?></div>
                <div>Chờ duyệt</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['average_rating']; ?></div>
                <div>Đánh giá TB</div>
            </div>
        </div>
        
        <!-- Bộ lọc -->
        <div class="filters">
            <form method="GET" class="filter-group">
                <select name="filter">
                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                    <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Chờ duyệt</option>
                    <option value="approved" <?php echo $filter === 'approved' ? 'selected' : ''; ?>>Đã duyệt</option>
                </select>
                
                <input type="text" name="search" placeholder="Tìm kiếm..." value="<?php echo htmlspecialchars($search); ?>">
                
                <button type="submit" class="btn btn-primary">Lọc</button>
                <a href="reviews.php" class="btn btn-secondary">Xóa bộ lọc</a>
            </form>
        </div>
        
        <!-- Danh sách reviews -->
        <div class="reviews-list">
            <?php if (empty($reviews)): ?>
                <div class="empty-state">
                    <p>Không có đánh giá nào</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div class="review-info">
                                <h3><?php echo htmlspecialchars($review['title']); ?></h3>
                                <div class="rating-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                    <?php endfor; ?>
                                    <span>(<?php echo $review['rating']; ?>/5)</span>
                                </div>
                                <div class="review-meta">
                                    <strong><?php echo htmlspecialchars($review['product_name']); ?></strong> - 
                                    Bởi <?php echo htmlspecialchars($review['full_name'] ?: $review['username']); ?> - 
                                    <?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?>
                                    <?php if ($review['is_verified_purchase']): ?>
                                        <span class="verified-badge">✓ Đã mua</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="review-actions">
                                <span class="status-badge <?php echo $review['is_approved'] ? 'status-approved' : 'status-pending'; ?>">
                                    <?php echo $review['is_approved'] ? 'Đã duyệt' : 'Chờ duyệt'; ?>
                                </span>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                    
                                    <?php if (!$review['is_approved']): ?>
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-small">
                                            <i class="fas fa-check"></i> Duyệt
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="reject" class="btn btn-warning btn-small">
                                            <i class="fas fa-times"></i> Hủy duyệt
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button type="submit" name="action" value="delete" class="btn btn-danger btn-small" 
                                            onclick="return confirm('Bạn có chắc muốn xóa đánh giá này?')">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="review-content">
                            <p><?php echo nl2br(htmlspecialchars($review['content'])); ?></p>
                        </div>
                        
                        <?php if ($review['helpful_count'] > 0): ?>
                            <div class="review-meta">
                                <i class="fas fa-thumbs-up"></i> <?php echo $review['helpful_count']; ?> người thấy hữu ích
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
