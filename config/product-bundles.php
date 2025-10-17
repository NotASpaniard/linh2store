<?php
/**
 * Product Bundles System
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/database.php';

class ProductBundles {
    
    /**
     * Lấy tất cả combo sản phẩm
     */
    public static function getAllBundles($status = 'active') {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "SELECT * FROM product_bundles WHERE 1=1";
            $params = [];
            
            if ($status) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy thông tin combo theo ID
     */
    public static function getBundleById($bundle_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT * FROM product_bundles 
                WHERE id = ? AND status = 'active'
            ");
            $stmt->execute([$bundle_id]);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy sản phẩm trong combo
     */
    public static function getBundleItems($bundle_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT bi.*, p.name, p.price, p.image, p.description,
                       b.name as brand_name
                FROM bundle_items bi
                JOIN products p ON bi.product_id = p.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE bi.bundle_id = ?
                ORDER BY bi.id
            ");
            $stmt->execute([$bundle_id]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy combo với sản phẩm
     */
    public static function getBundleWithItems($bundle_id) {
        try {
            $bundle = self::getBundleById($bundle_id);
            if (!$bundle) {
                return false;
            }
            
            $bundle['items'] = self::getBundleItems($bundle_id);
            return $bundle;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Tính giá combo
     */
    public static function calculateBundlePrice($bundle_id) {
        try {
            $bundle = self::getBundleById($bundle_id);
            if (!$bundle) {
                return 0;
            }
            
            $items = self::getBundleItems($bundle_id);
            $original_price = 0;
            
            foreach ($items as $item) {
                $original_price += $item['price'] * $item['quantity'];
            }
            
            return [
                'original_price' => $original_price,
                'bundle_price' => $bundle['bundle_price'],
                'savings' => $original_price - $bundle['bundle_price'],
                'discount_percentage' => $bundle['discount_percentage']
            ];
            
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Kiểm tra combo có sẵn hàng không
     */
    public static function checkBundleAvailability($bundle_id) {
        try {
            $items = self::getBundleItems($bundle_id);
            
            foreach ($items as $item) {
                if ($item['stock_quantity'] < $item['quantity']) {
                    return false;
                }
            }
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy combo theo loại
     */
    public static function getBundlesByType($bundle_type) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT * FROM product_bundles 
                WHERE bundle_type = ? AND status = 'active'
                ORDER BY created_at DESC
            ");
            $stmt->execute([$bundle_type]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lấy combo theo ngày
     */
    public static function getBundlesByDate($date = null) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            if (!$date) {
                $date = date('Y-m-d H:i:s');
            }
            
            $stmt = $conn->prepare("
                SELECT * FROM product_bundles 
                WHERE status = 'active' 
                AND (start_date IS NULL OR start_date <= ?)
                AND (end_date IS NULL OR end_date >= ?)
                ORDER BY created_at DESC
            ");
            $stmt->execute([$date, $date]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Tạo combo mới
     */
    public static function createBundle($data) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $conn->beginTransaction();
            
            // Tạo combo
            $stmt = $conn->prepare("
                INSERT INTO product_bundles 
                (name, description, bundle_type, original_price, bundle_price, 
                 discount_percentage, image_url, status, start_date, end_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['name'],
                $data['description'],
                $data['bundle_type'],
                $data['original_price'],
                $data['bundle_price'],
                $data['discount_percentage'],
                $data['image_url'],
                $data['status'],
                $data['start_date'],
                $data['end_date']
            ]);
            
            $bundle_id = $conn->lastInsertId();
            
            // Thêm sản phẩm vào combo
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $stmt = $conn->prepare("
                        INSERT INTO bundle_items (bundle_id, product_id, quantity, is_required) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $bundle_id,
                        $item['product_id'],
                        $item['quantity'],
                        $item['is_required']
                    ]);
                }
            }
            
            $conn->commit();
            return $bundle_id;
            
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }
    
    /**
     * Cập nhật combo
     */
    public static function updateBundle($bundle_id, $data) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $conn->beginTransaction();
            
            // Cập nhật thông tin combo
            $stmt = $conn->prepare("
                UPDATE product_bundles 
                SET name = ?, description = ?, bundle_type = ?, original_price = ?, 
                    bundle_price = ?, discount_percentage = ?, image_url = ?, 
                    status = ?, start_date = ?, end_date = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['name'],
                $data['description'],
                $data['bundle_type'],
                $data['original_price'],
                $data['bundle_price'],
                $data['discount_percentage'],
                $data['image_url'],
                $data['status'],
                $data['start_date'],
                $data['end_date'],
                $bundle_id
            ]);
            
            // Xóa sản phẩm cũ
            $stmt = $conn->prepare("DELETE FROM bundle_items WHERE bundle_id = ?");
            $stmt->execute([$bundle_id]);
            
            // Thêm sản phẩm mới
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $stmt = $conn->prepare("
                        INSERT INTO bundle_items (bundle_id, product_id, quantity, is_required) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $bundle_id,
                        $item['product_id'],
                        $item['quantity'],
                        $item['is_required']
                    ]);
                }
            }
            
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }
    
    /**
     * Xóa combo
     */
    public static function deleteBundle($bundle_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $conn->beginTransaction();
            
            // Xóa sản phẩm trong combo
            $stmt = $conn->prepare("DELETE FROM bundle_items WHERE bundle_id = ?");
            $stmt->execute([$bundle_id]);
            
            // Xóa combo
            $stmt = $conn->prepare("DELETE FROM product_bundles WHERE id = ?");
            $stmt->execute([$bundle_id]);
            
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }
    
    /**
     * Lấy combo phù hợp với sản phẩm
     */
    public static function getBundlesForProduct($product_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT DISTINCT pb.*
                FROM product_bundles pb
                JOIN bundle_items bi ON pb.id = bi.bundle_id
                WHERE bi.product_id = ? AND pb.status = 'active'
                ORDER BY pb.discount_percentage DESC
            ");
            $stmt->execute([$product_id]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Tìm kiếm combo
     */
    public static function searchBundles($search_term) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT * FROM product_bundles 
                WHERE (name LIKE ? OR description LIKE ?) 
                AND status = 'active'
                ORDER BY created_at DESC
            ");
            $stmt->execute(["%$search_term%", "%$search_term%"]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
