<?php
/**
 * Product Relations System - Cross-selling & Upselling
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/database.php';

class ProductRelations {
    
    /**
     * Lấy sản phẩm liên quan
     */
    public static function getRelatedProducts($product_id, $relation_type = null, $limit = 10) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "
                SELECT pr.*, p.name, p.price, p.image, p.description,
                       b.name as brand_name, p.status
                FROM product_relations pr
                JOIN products p ON pr.related_product_id = p.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE pr.product_id = ? AND pr.status = 'active' AND p.status = 'active'
            ";
            
            $params = [$product_id];
            
            if ($relation_type) {
                $sql .= " AND pr.relation_type = ?";
                $params[] = $relation_type;
            }
            
            $sql .= " ORDER BY pr.priority DESC, p.name LIMIT ?";
            $params[] = $limit;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy sản phẩm cross-sell
     */
    public static function getCrossSellProducts($product_id, $limit = 5) {
        return self::getRelatedProducts($product_id, 'cross_sell', $limit);
    }
    
    /**
     * Lấy sản phẩm upsell
     */
    public static function getUpsellProducts($product_id, $limit = 3) {
        return self::getRelatedProducts($product_id, 'upsell', $limit);
    }
    
    /**
     * Lấy sản phẩm phụ kiện
     */
    public static function getAccessoryProducts($product_id, $limit = 5) {
        return self::getRelatedProducts($product_id, 'accessory', $limit);
    }
    
    /**
     * Lấy sản phẩm thay thế
     */
    public static function getAlternativeProducts($product_id, $limit = 5) {
        return self::getRelatedProducts($product_id, 'alternative', $limit);
    }
    
    /**
     * Lấy sản phẩm bổ sung
     */
    public static function getComplementProducts($product_id, $limit = 5) {
        return self::getRelatedProducts($product_id, 'complement', $limit);
    }
    
    /**
     * Thêm quan hệ sản phẩm
     */
    public static function addProductRelation($product_id, $related_product_id, $relation_type, $priority = 0) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                INSERT INTO product_relations (product_id, related_product_id, relation_type, priority) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE priority = ?, status = 'active'
            ");
            
            $stmt->execute([
                $product_id, $related_product_id, $relation_type, $priority,
                $priority
            ]);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Xóa quan hệ sản phẩm
     */
    public static function removeProductRelation($product_id, $related_product_id, $relation_type = null) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            if ($relation_type) {
                $stmt = $conn->prepare("
                    DELETE FROM product_relations 
                    WHERE product_id = ? AND related_product_id = ? AND relation_type = ?
                ");
                $stmt->execute([$product_id, $related_product_id, $relation_type]);
            } else {
                $stmt = $conn->prepare("
                    DELETE FROM product_relations 
                    WHERE product_id = ? AND related_product_id = ?
                ");
                $stmt->execute([$product_id, $related_product_id]);
            }
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Cập nhật quan hệ sản phẩm
     */
    public static function updateProductRelation($product_id, $related_product_id, $relation_type, $priority, $status = 'active') {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                UPDATE product_relations 
                SET priority = ?, status = ?
                WHERE product_id = ? AND related_product_id = ? AND relation_type = ?
            ");
            
            $stmt->execute([$priority, $status, $product_id, $related_product_id, $relation_type]);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy tất cả quan hệ của sản phẩm
     */
    public static function getAllProductRelations($product_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT pr.*, p.name, p.price, p.image, b.name as brand_name
                FROM product_relations pr
                JOIN products p ON pr.related_product_id = p.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE pr.product_id = ?
                ORDER BY pr.relation_type, pr.priority DESC
            ");
            $stmt->execute([$product_id]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy sản phẩm liên quan theo thương hiệu
     */
    public static function getRelatedProductsByBrand($product_id, $limit = 5) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT p.*, b.name as brand_name
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.brand_id = (
                    SELECT brand_id FROM products WHERE id = ?
                ) AND p.id != ? AND p.status = 'active'
                ORDER BY p.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$product_id, $product_id, $limit]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy sản phẩm liên quan theo danh mục
     */
    public static function getRelatedProductsByCategory($product_id, $limit = 5) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT p.*, c.name as category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.category_id = (
                    SELECT category_id FROM products WHERE id = ?
                ) AND p.id != ? AND p.status = 'active'
                ORDER BY p.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$product_id, $product_id, $limit]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy sản phẩm liên quan theo giá
     */
    public static function getRelatedProductsByPrice($product_id, $price_range = 0.2, $limit = 5) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Lấy giá sản phẩm hiện tại
            $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                return [];
            }
            
            $current_price = $product['price'];
            $min_price = $current_price * (1 - $price_range);
            $max_price = $current_price * (1 + $price_range);
            
            $stmt = $conn->prepare("
                SELECT p.*, b.name as brand_name
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.price BETWEEN ? AND ? 
                AND p.id != ? AND p.status = 'active'
                ORDER BY ABS(p.price - ?) ASC
                LIMIT ?
            ");
            $stmt->execute([$min_price, $max_price, $product_id, $current_price, $limit]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy sản phẩm liên quan thông minh
     */
    public static function getSmartRelatedProducts($product_id, $limit = 10) {
        try {
            $related_products = [];
            
            // Lấy sản phẩm cross-sell
            $cross_sell = self::getCrossSellProducts($product_id, 3);
            $related_products = array_merge($related_products, $cross_sell);
            
            // Lấy sản phẩm upsell
            $upsell = self::getUpsellProducts($product_id, 2);
            $related_products = array_merge($related_products, $upsell);
            
            // Lấy sản phẩm theo thương hiệu
            $by_brand = self::getRelatedProductsByBrand($product_id, 3);
            $related_products = array_merge($related_products, $by_brand);
            
            // Lấy sản phẩm theo danh mục
            $by_category = self::getRelatedProductsByCategory($product_id, 2);
            $related_products = array_merge($related_products, $by_category);
            
            // Loại bỏ trùng lặp
            $unique_products = [];
            $seen_ids = [];
            
            foreach ($related_products as $product) {
                if (!in_array($product['id'], $seen_ids)) {
                    $unique_products[] = $product;
                    $seen_ids[] = $product['id'];
                }
            }
            
            // Giới hạn số lượng
            return array_slice($unique_products, 0, $limit);
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy sản phẩm liên quan theo hành vi người dùng
     */
    public static function getRelatedProductsByBehavior($product_id, $user_id = null, $limit = 10) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $related_products = [];
            
            // Lấy sản phẩm từ lịch sử xem
            if ($user_id) {
                $stmt = $conn->prepare("
                    SELECT DISTINCT p.*, b.name as brand_name
                    FROM recently_viewed rv
                    JOIN products p ON rv.product_id = p.id
                    LEFT JOIN brands b ON p.brand_id = b.id
                    WHERE rv.user_id = ? AND p.id != ? AND p.status = 'active'
                    ORDER BY rv.viewed_at DESC
                    LIMIT ?
                ");
                $stmt->execute([$user_id, $product_id, $limit]);
                $related_products = array_merge($related_products, $stmt->fetchAll());
            }
            
            // Lấy sản phẩm từ giỏ hàng của người dùng khác
            $stmt = $conn->prepare("
                SELECT DISTINCT p.*, b.name as brand_name, COUNT(*) as frequency
                FROM cart c
                JOIN products p ON c.product_id = p.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE c.product_id IN (
                    SELECT DISTINCT product_id FROM cart 
                    WHERE user_id IN (
                        SELECT DISTINCT user_id FROM cart 
                        WHERE product_id = ?
                    )
                ) AND p.id != ? AND p.status = 'active'
                GROUP BY p.id
                ORDER BY frequency DESC
                LIMIT ?
            ");
            $stmt->execute([$product_id, $product_id, $limit]);
            $related_products = array_merge($related_products, $stmt->fetchAll());
            
            // Loại bỏ trùng lặp
            $unique_products = [];
            $seen_ids = [];
            
            foreach ($related_products as $product) {
                if (!in_array($product['id'], $seen_ids)) {
                    $unique_products[] = $product;
                    $seen_ids[] = $product['id'];
                }
            }
            
            return array_slice($unique_products, 0, $limit);
            
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
