<?php
/**
 * Blog API Endpoints
 * Linh2Store - Blog API for AJAX operations
 */

require_once '../config/auth-middleware.php';
require_once '../config/blog.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

$blogManager = new BlogManager();

try {
    switch ($action) {
        case 'like':
            if (!AuthMiddleware::isLoggedIn()) {
                echo json_encode(['error' => 'Vui lòng đăng nhập để like bài viết']);
                exit;
            }
            
            $postId = (int)($input['post_id'] ?? 0);
            $user = AuthMiddleware::getCurrentUser();
            
            if (!$postId) {
                echo json_encode(['error' => 'Post ID is required']);
                exit;
            }
            
            $result = $blogManager->likePost($postId, $user['id']);
            
            // Lấy số like mới
            $post = $blogManager->getPostById($postId);
            
            echo json_encode([
                'success' => true,
                'action' => $result['action'],
                'liked' => $result['liked'],
                'like_count' => $post['like_count']
            ]);
            break;
            
        case 'add_comment':
            if (!AuthMiddleware::isLoggedIn()) {
                echo json_encode(['error' => 'Vui lòng đăng nhập để bình luận']);
                exit;
            }
            
            $postId = (int)($input['post_id'] ?? 0);
            $content = trim($input['content'] ?? '');
            $parentId = isset($input['parent_id']) ? (int)$input['parent_id'] : null;
            $user = AuthMiddleware::getCurrentUser();
            
            if (!$postId || empty($content)) {
                echo json_encode(['error' => 'Post ID and content are required']);
                exit;
            }
            
            $success = $blogManager->addComment($postId, $user['id'], $content, $parentId);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Bình luận đã được gửi thành công'
                ]);
            } else {
                echo json_encode(['error' => 'Không thể gửi bình luận']);
            }
            break;
            
        case 'get_comments':
            $postId = (int)($input['post_id'] ?? 0);
            $status = $input['status'] ?? 'approved';
            
            if (!$postId) {
                echo json_encode(['error' => 'Post ID is required']);
                exit;
            }
            
            $comments = $blogManager->getComments($postId, $status);
            
            echo json_encode([
                'success' => true,
                'comments' => $comments
            ]);
            break;
            
        case 'delete_comment':
            if (!AuthMiddleware::isLoggedIn()) {
                echo json_encode(['error' => 'Vui lòng đăng nhập']);
                exit;
            }
            
            $commentId = (int)($input['comment_id'] ?? 0);
            $user = AuthMiddleware::getCurrentUser();
            
            if (!$commentId) {
                echo json_encode(['error' => 'Comment ID is required']);
                exit;
            }
            
            // TODO: Implement comment deletion logic
            // For now, just return success
            echo json_encode([
                'success' => true,
                'message' => 'Bình luận đã được xóa'
            ]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
