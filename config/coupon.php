<?php
/**
 * Coupon System
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/database.php';

class CouponSystem {
    
    /**
     * Kiểm tra mã giảm giá có hợp lệ không
     */
    public static function validateCoupon($code, $user_id, $order_amount) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Lấy thông tin coupon
            $stmt = $conn->prepare("
                SELECT * FROM coupons 
                WHERE code = ? AND status = 'active' 
                AND start_date <= NOW() AND end_date >= NOW()
            ");
            $stmt->execute([$code]);
            $coupon = $stmt->fetch();
            
            if (!$coupon) {
                return ['valid' => false, 'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn'];
            }
            
            // Kiểm tra giới hạn sử dụng
            if ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) {
                return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng'];
            }
            
            // Kiểm tra số tiền tối thiểu
            if ($order_amount < $coupon['min_order_amount']) {
                return ['valid' => false, 'message' => 'Đơn hàng chưa đạt số tiền tối thiểu'];
            }
            
            // Kiểm tra giới hạn sử dụng per user
            $stmt = $conn->prepare("
                SELECT COUNT(*) as usage_count 
                FROM coupon_usage 
                WHERE coupon_id = ? AND user_id = ?
            ");
            $stmt->execute([$coupon['id'], $user_id]);
            $usage = $stmt->fetch();
            
            if ($usage['usage_count'] >= $coupon['user_limit']) {
                return ['valid' => false, 'message' => 'Bạn đã sử dụng hết lượt cho mã này'];
            }
            
            return [
                'valid' => true,
                'coupon' => $coupon,
                'message' => 'Mã giảm giá hợp lệ'
            ];
            
        } catch (Exception $e) {
            return ['valid' => false, 'message' => 'Lỗi hệ thống'];
        }
    }
    
    /**
     * Tính toán số tiền giảm giá
     */
    public static function calculateDiscount($coupon, $order_amount) {
        $discount = 0;
        
        switch ($coupon['type']) {
            case 'percentage':
                $discount = ($order_amount * $coupon['value']) / 100;
                if ($coupon['max_discount'] && $discount > $coupon['max_discount']) {
                    $discount = $coupon['max_discount'];
                }
                break;
                
            case 'fixed':
                $discount = $coupon['value'];
                break;
                
            case 'free_shipping':
                $discount = 0; // Sẽ xử lý riêng cho phí ship
                break;
        }
        
        return $discount;
    }
    
    /**
     * Áp dụng mã giảm giá
     */
    public static function applyCoupon($code, $user_id, $order_id, $order_amount) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Validate coupon
            $validation = self::validateCoupon($code, $user_id, $order_amount);
            if (!$validation['valid']) {
                return $validation;
            }
            
            $coupon = $validation['coupon'];
            $discount_amount = self::calculateDiscount($coupon, $order_amount);
            
            $conn->beginTransaction();
            
            // Ghi nhận sử dụng coupon
            $stmt = $conn->prepare("
                INSERT INTO coupon_usage (coupon_id, user_id, order_id, discount_amount) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$coupon['id'], $user_id, $order_id, $discount_amount]);
            
            // Cập nhật số lần sử dụng
            $stmt = $conn->prepare("
                UPDATE coupons 
                SET used_count = used_count + 1 
                WHERE id = ?
            ");
            $stmt->execute([$coupon['id']]);
            
            $conn->commit();
            
            return [
                'success' => true,
                'discount_amount' => $discount_amount,
                'coupon' => $coupon,
                'message' => 'Áp dụng mã giảm giá thành công'
            ];
            
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollback();
            }
            return ['success' => false, 'message' => 'Lỗi hệ thống'];
        }
    }
    
    /**
     * Lấy danh sách coupon có sẵn
     */
    public static function getAvailableCoupons($user_id = null) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $sql = "
                SELECT * FROM coupons 
                WHERE status = 'active' 
                AND start_date <= NOW() 
                AND end_date >= NOW()
                AND (usage_limit IS NULL OR used_count < usage_limit)
                ORDER BY created_at DESC
            ";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $coupons = $stmt->fetchAll();
            
            // Nếu có user_id, kiểm tra lượt sử dụng còn lại
            if ($user_id) {
                foreach ($coupons as &$coupon) {
                    $stmt = $conn->prepare("
                        SELECT COUNT(*) as usage_count 
                        FROM coupon_usage 
                        WHERE coupon_id = ? AND user_id = ?
                    ");
                    $stmt->execute([$coupon['id'], $user_id]);
                    $usage = $stmt->fetch();
                    
                    $coupon['remaining_uses'] = $coupon['user_limit'] - $usage['usage_count'];
                    $coupon['can_use'] = $coupon['remaining_uses'] > 0;
                }
            }
            
            return $coupons;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Tạo mã giảm giá mới
     */
    public static function createCoupon($data) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                INSERT INTO coupons 
                (code, name, description, type, value, min_order_amount, max_discount, 
                 usage_limit, user_limit, start_date, end_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['code'],
                $data['name'],
                $data['description'],
                $data['type'],
                $data['value'],
                $data['min_order_amount'],
                $data['max_discount'],
                $data['usage_limit'],
                $data['user_limit'],
                $data['start_date'],
                $data['end_date'],
                $data['status']
            ]);
            
            return $conn->lastInsertId();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy thống kê sử dụng coupon
     */
    public static function getCouponStats($coupon_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT 
                    c.*,
                    COUNT(cu.id) as total_usage,
                    SUM(cu.discount_amount) as total_discount,
                    AVG(cu.discount_amount) as avg_discount
                FROM coupons c
                LEFT JOIN coupon_usage cu ON c.id = cu.coupon_id
                WHERE c.id = ?
                GROUP BY c.id
            ");
            $stmt->execute([$coupon_id]);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy lịch sử sử dụng coupon của user
     */
    public static function getUserCouponHistory($user_id, $limit = 20, $offset = 0) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT cu.*, c.name as coupon_name, c.code, o.total_amount, o.status as order_status
                FROM coupon_usage cu
                JOIN coupons c ON cu.coupon_id = c.id
                JOIN orders o ON cu.order_id = o.id
                WHERE cu.user_id = ?
                ORDER BY cu.used_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$user_id, $limit, $offset]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Tạo mã giảm giá tự động
     */
    public static function generateCouponCode($length = 8) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0JQKA56789';
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $code;
    }
    
    /**
     * Kiểm tra mã coupon có tồn tại không
     */
    public static function isCouponCodeExists($code) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("SELECT id FROM coupons WHERE code = ?");
            $stmt->execute([$code]);
            
            return $stmt->fetch() ? true : false;
            
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
