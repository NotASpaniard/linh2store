<?php
/**
 * Product Tracking System - Recently Viewed & Comparison
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/database.php';

class ProductTracking {
    
    /**
     * Thêm sản phẩm vào danh sách đã xem
     */
    public static function addToRecentlyViewed($user_id, $product_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Kiểm tra xem đã tồn tại chưa
            $stmt = $conn->prepare("
                SELECT id FROM recently_viewed 
                WHERE user_id = ? AND product_id = ?
            ");
            $stmt->execute([$user_id, $product_id]);
            
            if ($stmt->fetch()) {
                // Cập nhật thời gian xem
                $stmt = $conn->prepare("
                    UPDATE recently_viewed 
                    SET viewed_at = CURRENT_TIMESTAMP 
                    WHERE user_id = ? AND product_id = ?
                ");
                $stmt->execute([$user_id, $product_id]);
            } else {
                // Thêm mới
                $stmt = $conn->prepare("
                    INSERT INTO recently_viewed (user_id, product_id) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$user_id, $product_id]);
            }
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy sản phẩm đã xem gần đây
     */
    public static function getRecentlyViewed($user_id, $limit = 10) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT rv.*, p.name, p.price, p.image, p.description,
                       b.name as brand_name, c.name as category_name
                FROM recently_viewed rv
                JOIN products p ON rv.product_id = p.id
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE rv.user_id = ? AND p.status = 'active'
                ORDER BY rv.viewed_at DESC
                LIMIT ?
            ");
            $stmt->execute([$user_id, $limit]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Xóa sản phẩm khỏi danh sách đã xem
     */
    public static function removeFromRecentlyViewed($user_id, $product_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                DELETE FROM recently_viewed 
                WHERE user_id = ? AND product_id = ?
            ");
            $stmt->execute([$user_id, $product_id]);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Xóa tất cả sản phẩm đã xem
     */
    public static function clearRecentlyViewed($user_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                DELETE FROM recently_viewed 
                WHERE user_id = ?
            ");
            $stmt->execute([$user_id]);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Thêm sản phẩm vào danh sách so sánh
     */
    public static function addToComparison($user_id, $product_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Kiểm tra số lượng sản phẩm trong danh sách so sánh
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count FROM product_comparisons 
                WHERE user_id = ?
            ");
            $stmt->execute([$user_id]);
            $count = $stmt->fetch()['count'];
            
            if ($count >= 4) {
                return false; // Tối đa 4 sản phẩm
            }
            
            // Kiểm tra xem đã tồn tại chưa
            $stmt = $conn->prepare("
                SELECT id FROM product_comparisons 
                WHERE user_id = ? AND product_id = ?
            ");
            $stmt->execute([$user_id, $product_id]);
            
            if ($stmt->fetch()) {
                return false; // Đã tồn tại
            }
            
            // Thêm mới
            $stmt = $conn->prepare("
                INSERT INTO product_comparisons (user_id, product_id) 
                VALUES (?, ?)
            ");
            $stmt->execute([$user_id, $product_id]);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy danh sách sản phẩm so sánh
     */
    public static function getComparisonList($user_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT pc.*, p.name, p.price, p.image, p.description,
                       b.name as brand_name, c.name as category_name,
                       p.stock_quantity, p.status
                FROM product_comparisons pc
                JOIN products p ON pc.product_id = p.id
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE pc.user_id = ?
                ORDER BY pc.added_at ASC
            ");
            $stmt->execute([$user_id]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Xóa sản phẩm khỏi danh sách so sánh
     */
    public static function removeFromComparison($user_id, $product_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                DELETE FROM product_comparisons 
                WHERE user_id = ? AND product_id = ?
            ");
            $stmt->execute([$user_id, $product_id]);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Xóa tất cả sản phẩm so sánh
     */
    public static function clearComparison($user_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                DELETE FROM product_comparisons 
                WHERE user_id = ?
            ");
            $stmt->execute([$user_id]);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy thông tin so sánh chi tiết
     */
    public static function getComparisonDetails($user_id) {
        try {
            $products = self::getComparisonList($user_id);
            
            if (empty($products)) {
                return [];
            }
            
            $comparison_data = [
                'products' => $products,
                'features' => [],
                'specifications' => []
            ];
            
            // Lấy thông số kỹ thuật
            $db = new Database();
            $conn = $db->getConnection();
            
            foreach ($products as $product) {
                // Lấy thông số kỹ thuật (nếu có bảng specifications)
                $stmt = $conn->prepare("
                    SELECT * FROM product_specifications 
                    WHERE product_id = ?
                ");
                $stmt->execute([$product['id']]);
                $specs = $stmt->fetchAll();
                
                if ($specs) {
                    $comparison_data['specifications'][$product['id']] = $specs;
                }
            }
            
            return $comparison_data;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy sản phẩm phổ biến
     */
    public static function getPopularProducts($limit = 10) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT p.*, b.name as brand_name, c.name as category_name,
                       COUNT(rv.id) as view_count
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN recently_viewed rv ON p.id = rv.product_id
                WHERE p.status = 'active'
                GROUP BY p.id
                ORDER BY view_count DESC, p.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy sản phẩm được xem nhiều nhất
     */
    public static function getMostViewedProducts($limit = 10) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT p.*, b.name as brand_name, c.name as category_name,
                       COUNT(rv.id) as view_count
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN recently_viewed rv ON p.id = rv.product_id
                WHERE p.status = 'active'
                GROUP BY p.id
                HAVING view_count > 0
                ORDER BY view_count DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy sản phẩm được so sánh nhiều nhất
     */
    public static function getMostComparedProducts($limit = 10) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT p.*, b.name as brand_name, c.name as category_name,
                       COUNT(pc.id) as comparison_count
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN product_comparisons pc ON p.id = pc.product_id
                WHERE p.status = 'active'
                GROUP BY p.id
                HAVING comparison_count > 0
                ORDER BY comparison_count DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy thống kê sản phẩm
     */
    public static function getProductStats($product_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Số lần xem
            $stmt = $conn->prepare("
                SELECT COUNT(*) as view_count FROM recently_viewed 
                WHERE product_id = ?
            ");
            $stmt->execute([$product_id]);
            $view_count = $stmt->fetch()['view_count'];
            
            // Số lần so sánh
            $stmt = $conn->prepare("
                SELECT COUNT(*) as comparison_count FROM product_comparisons 
                WHERE product_id = ?
            ");
            $stmt->execute([$product_id]);
            $comparison_count = $stmt->fetch()['comparison_count'];
            
            // Số lần thêm vào giỏ hàng
            $stmt = $conn->prepare("
                SELECT COUNT(*) as cart_count FROM cart 
                WHERE product_id = ?
            ");
            $stmt->execute([$product_id]);
            $cart_count = $stmt->fetch()['cart_count'];
            
            return [
                'view_count' => $view_count,
                'comparison_count' => $comparison_count,
                'cart_count' => $cart_count
            ];
            
        } catch (Exception $e) {
            return [
                'view_count' => 0,
                'comparison_count' => 0,
                'cart_count' => 0
            ];
        }
    }
}
?>
