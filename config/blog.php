<?php
/**
 * Blog Configuration
 * Linh2Store - Blog Management System
 */

require_once __DIR__ . '/database.php';

class BlogManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Lấy tất cả bài viết với pagination và filter
     */
    public function getAllPosts($limit = 12, $offset = 0, $categoryId = null, $tagId = null, $search = null) {
        $conn = $this->db->getConnection();
        
        $where = ["bp.status = 'published'"];
        $params = [];
        
        if ($categoryId) {
            $where[] = "bp.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($tagId) {
            $where[] = "bpt.tag_id = ?";
            $params[] = $tagId;
        }
        
        if ($search) {
            $where[] = "(bp.title LIKE ? OR bp.excerpt LIKE ? OR bp.content LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "
            SELECT DISTINCT bp.*, bc.name as category_name, bc.slug as category_slug,
                   u.username as author_name, u.avatar as author_avatar
            FROM blog_posts bp
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id
            LEFT JOIN users u ON bp.author_id = u.id
            " . ($tagId ? "LEFT JOIN blog_post_tags bpt ON bp.id = bpt.post_id" : "") . "
            WHERE $whereClause
            ORDER BY bp.featured DESC, bp.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy bài viết theo ID
     */
    public function getPostById($id) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT bp.*, bc.name as category_name, bc.slug as category_slug,
                   u.username as author_name, u.avatar as author_avatar,
                   bs.meta_title, bs.meta_description, bs.meta_keywords, bs.og_image
            FROM blog_posts bp
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id
            LEFT JOIN users u ON bp.author_id = u.id
            LEFT JOIN blog_seo bs ON bp.id = bs.post_id
            WHERE bp.id = ? AND bp.status = 'published'
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Lấy bài viết theo slug
     */
    public function getPostBySlug($slug) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT bp.*, bc.name as category_name, bc.slug as category_slug,
                   u.username as author_name, u.avatar as author_avatar,
                   bs.meta_title, bs.meta_description, bs.meta_keywords, bs.og_image
            FROM blog_posts bp
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id
            LEFT JOIN users u ON bp.author_id = u.id
            LEFT JOIN blog_seo bs ON bp.id = bs.post_id
            WHERE bp.slug = ? AND bp.status = 'published'
        ");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    
    /**
     * Lấy bài viết liên quan
     */
    public function getRelatedPosts($postId, $limit = 4) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT DISTINCT bp.*, bc.name as category_name
            FROM blog_posts bp
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id
            LEFT JOIN blog_post_tags bpt ON bp.id = bpt.post_id
            WHERE bp.id != ? 
            AND bp.status = 'published'
            AND (
                bp.category_id = (SELECT category_id FROM blog_posts WHERE id = ?)
                OR bpt.tag_id IN (SELECT tag_id FROM blog_post_tags WHERE post_id = ?)
            )
            ORDER BY bp.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$postId, $postId, $postId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Tăng lượt xem
     */
    public function incrementViewCount($postId) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("UPDATE blog_posts SET view_count = view_count + 1 WHERE id = ?");
        return $stmt->execute([$postId]);
    }
    
    /**
     * Like bài viết
     */
    public function likePost($postId, $userId) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->beginTransaction();
            
            // Kiểm tra đã like chưa
            $stmt = $conn->prepare("SELECT id FROM blog_likes WHERE post_id = ? AND user_id = ?");
            $stmt->execute([$postId, $userId]);
            
            if ($stmt->fetch()) {
                // Đã like rồi, unlike
                $stmt = $conn->prepare("DELETE FROM blog_likes WHERE post_id = ? AND user_id = ?");
                $stmt->execute([$postId, $userId]);
                
                $stmt = $conn->prepare("UPDATE blog_posts SET like_count = like_count - 1 WHERE id = ?");
                $stmt->execute([$postId]);
                
                $conn->commit();
                return ['action' => 'unliked', 'liked' => false];
            } else {
                // Chưa like, like
                $stmt = $conn->prepare("INSERT INTO blog_likes (post_id, user_id) VALUES (?, ?)");
                $stmt->execute([$postId, $userId]);
                
                $stmt = $conn->prepare("UPDATE blog_posts SET like_count = like_count + 1 WHERE id = ?");
                $stmt->execute([$postId]);
                
                $conn->commit();
                return ['action' => 'liked', 'liked' => true];
            }
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }
    
    /**
     * Kiểm tra user đã like bài viết chưa
     */
    public function isPostLiked($postId, $userId) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("SELECT id FROM blog_likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);
        return $stmt->fetch() ? true : false;
    }
    
    /**
     * Thêm comment
     */
    public function addComment($postId, $userId, $content, $parentId = null) {
        $conn = $this->db->getConnection();
        
        try {
            $conn->beginTransaction();
            
            $stmt = $conn->prepare("
                INSERT INTO blog_comments (post_id, user_id, parent_comment_id, content) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$postId, $userId, $parentId, $content]);
            
            // Tăng comment count
            $stmt = $conn->prepare("UPDATE blog_posts SET comment_count = comment_count + 1 WHERE id = ?");
            $stmt->execute([$postId]);
            
            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }
    
    /**
     * Lấy comments của bài viết
     */
    public function getComments($postId, $status = 'approved') {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT bc.*, u.username, u.avatar
            FROM blog_comments bc
            LEFT JOIN users u ON bc.user_id = u.id
            WHERE bc.post_id = ? AND bc.status = ? AND bc.parent_comment_id IS NULL
            ORDER BY bc.created_at ASC
        ");
        $stmt->execute([$postId, $status]);
        $comments = $stmt->fetchAll();
        
        // Lấy replies cho mỗi comment
        foreach ($comments as &$comment) {
            $stmt = $conn->prepare("
                SELECT bc.*, u.username, u.avatar
                FROM blog_comments bc
                LEFT JOIN users u ON bc.user_id = u.id
                WHERE bc.parent_comment_id = ? AND bc.status = ?
                ORDER BY bc.created_at ASC
            ");
            $stmt->execute([$comment['id'], $status]);
            $comment['replies'] = $stmt->fetchAll();
        }
        
        return $comments;
    }
    
    /**
     * Lấy tất cả categories
     */
    public function getAllCategories() {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT bc.*, COUNT(bp.id) as post_count
            FROM blog_categories bc
            LEFT JOIN blog_posts bp ON bc.id = bp.category_id AND bp.status = 'published'
            WHERE bc.status = 'active'
            GROUP BY bc.id
            ORDER BY bc.name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy tất cả tags
     */
    public function getAllTags() {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT bt.*, COUNT(bpt.post_id) as post_count
            FROM blog_tags bt
            LEFT JOIN blog_post_tags bpt ON bt.id = bpt.tag_id
            LEFT JOIN blog_posts bp ON bpt.post_id = bp.id AND bp.status = 'published'
            GROUP BY bt.id
            ORDER BY bt.name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Tìm kiếm bài viết
     */
    public function searchPosts($keyword, $limit = 12, $offset = 0) {
        return $this->getAllPosts($limit, $offset, null, null, $keyword);
    }
    
    /**
     * Lấy bài viết nổi bật
     */
    public function getFeaturedPosts($limit = 3) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT bp.*, bc.name as category_name, bc.slug as category_slug,
                   u.username as author_name, u.avatar as author_avatar
            FROM blog_posts bp
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id
            LEFT JOIN users u ON bp.author_id = u.id
            WHERE bp.status = 'published' AND bp.featured = 1
            ORDER BY bp.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Lấy bài viết gần đây
     */
    public function getRecentPosts($limit = 5) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT bp.*, bc.name as category_name
            FROM blog_posts bp
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id
            WHERE bp.status = 'published'
            ORDER BY bp.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Tạo slug từ title
     */
    public function createSlug($title) {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
    
    /**
     * Kiểm tra slug đã tồn tại chưa
     */
    public function isSlugExists($slug, $excludeId = null) {
        $conn = $this->db->getConnection();
        
        $sql = "SELECT id FROM blog_posts WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ? true : false;
    }
    
    /**
     * Tạo slug unique
     */
    public function generateUniqueSlug($title, $excludeId = null) {
        $baseSlug = $this->createSlug($title);
        $slug = $baseSlug;
        $counter = 1;
        
        while ($this->isSlugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}
?>
