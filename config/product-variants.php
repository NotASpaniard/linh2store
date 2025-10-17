<?php
/**
 * Product Variants System
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/database.php';

class ProductVariants {
    
    /**
     * Lấy tất cả biến thể của sản phẩm
     */
    public static function getProductVariants($product_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT * FROM product_variants 
                WHERE product_id = ? AND status = 'active'
                ORDER BY variant_type, variant_value
            ");
            $stmt->execute([$product_id]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy biến thể theo loại
     */
    public static function getVariantsByType($product_id, $variant_type) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT * FROM product_variants 
                WHERE product_id = ? AND variant_type = ? AND status = 'active'
                ORDER BY variant_value
            ");
            $stmt->execute([$product_id, $variant_type]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy thông tin biến thể theo ID
     */
    public static function getVariantById($variant_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT pv.*, p.name as product_name, p.price as base_price
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                WHERE pv.id = ?
            ");
            $stmt->execute([$variant_id]);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Tính giá sản phẩm với biến thể
     */
    public static function calculateVariantPrice($product_id, $variant_id = null) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Lấy giá gốc sản phẩm
            $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                return 0;
            }
            
            $base_price = $product['price'];
            
            // Nếu có biến thể, tính giá điều chỉnh
            if ($variant_id) {
                $stmt = $conn->prepare("
                    SELECT price_adjustment FROM product_variants 
                    WHERE id = ? AND product_id = ? AND status = 'active'
                ");
                $stmt->execute([$variant_id, $product_id]);
                $variant = $stmt->fetch();
                
                if ($variant) {
                    $base_price += $variant['price_adjustment'];
                }
            }
            
            return $base_price;
            
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Kiểm tra tồn kho biến thể
     */
    public static function checkVariantStock($variant_id, $quantity = 1) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT stock_quantity FROM product_variants 
                WHERE id = ? AND status = 'active'
            ");
            $stmt->execute([$variant_id]);
            $variant = $stmt->fetch();
            
            if (!$variant) {
                return false;
            }
            
            return $variant['stock_quantity'] >= $quantity;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Cập nhật tồn kho biến thể
     */
    public static function updateVariantStock($variant_id, $quantity_change) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                UPDATE product_variants 
                SET stock_quantity = stock_quantity + ?,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$quantity_change, $variant_id]);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Tạo biến thể mới
     */
    public static function createVariant($data) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                INSERT INTO product_variants 
                (product_id, variant_name, variant_type, variant_value, variant_code, 
                 price_adjustment, stock_quantity, sku, image_url, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['product_id'],
                $data['variant_name'],
                $data['variant_type'],
                $data['variant_value'],
                $data['variant_code'],
                $data['price_adjustment'],
                $data['stock_quantity'],
                $data['sku'],
                $data['image_url'],
                $data['status']
            ]);
            
            return $conn->lastInsertId();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy tất cả loại biến thể của sản phẩm
     */
    public static function getVariantTypes($product_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT DISTINCT variant_type, COUNT(*) as variant_count
                FROM product_variants 
                WHERE product_id = ? AND status = 'active'
                GROUP BY variant_type
                ORDER BY variant_type
            ");
            $stmt->execute([$product_id]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy biến thể theo SKU
     */
    public static function getVariantBySku($sku) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT pv.*, p.name as product_name, p.price as base_price
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                WHERE pv.sku = ? AND pv.status = 'active'
            ");
            $stmt->execute([$sku]);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy biến thể theo mã biến thể
     */
    public static function getVariantByCode($product_id, $variant_code) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT * FROM product_variants 
                WHERE product_id = ? AND variant_code = ? AND status = 'active'
            ");
            $stmt->execute([$product_id, $variant_code]);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy tất cả biến thể với thông tin sản phẩm
     */
    public static function getVariantsWithProduct($product_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT pv.*, p.name as product_name, p.price as base_price,
                       (p.price + pv.price_adjustment) as final_price
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                WHERE pv.product_id = ? AND pv.status = 'active'
                ORDER BY pv.variant_type, pv.variant_value
            ");
            $stmt->execute([$product_id]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Tìm kiếm biến thể
     */
    public static function searchVariants($search_term, $variant_type = null) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "
                SELECT pv.*, p.name as product_name, p.price as base_price
                FROM product_variants pv
                JOIN products p ON pv.product_id = p.id
                WHERE (pv.variant_name LIKE ? OR pv.variant_value LIKE ? OR pv.sku LIKE ?)
                AND pv.status = 'active'
            ";
            
            $params = ["%$search_term%", "%$search_term%", "%$search_term%"];
            
            if ($variant_type) {
                $sql .= " AND pv.variant_type = ?";
                $params[] = $variant_type;
            }
            
            $sql .= " ORDER BY pv.variant_type, pv.variant_value";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
