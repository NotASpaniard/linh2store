<?php
/**
 * Loyalty Points System
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/database.php';

class LoyaltySystem {
    
    // Cấu hình điểm thưởng
    const POINTS_PER_VND = 1; // 1 điểm = 1,000 VND
    const POINTS_REDEMPTION_RATE = 1000; // 1 điểm = 1,000 VND
    const POINTS_EXPIRY_DAYS = 365; // Điểm hết hạn sau 1 năm
    
    /**
     * Tạo hệ thống điểm cho user mới
     */
    public static function createLoyaltyAccount($user_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Kiểm tra đã có điểm chưa
            $stmt = $conn->prepare("SELECT id FROM loyalty_points WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            if ($stmt->fetch()) {
                return true; // Đã có điểm
            }
            
            // Tạo tài khoản điểm mới
            $stmt = $conn->prepare("
                INSERT INTO loyalty_points (user_id, points, total_earned, total_redeemed) 
                VALUES (?, 0, 0, 0)
            ");
            $stmt->execute([$user_id]);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Tính điểm thưởng từ đơn hàng
     */
    public static function calculatePoints($order_amount) {
        return floor($order_amount / self::POINTS_PER_VND);
    }
    
    /**
     * Tính giá trị tiền từ điểm
     */
    public static function calculateValue($points) {
        return $points * self::POINTS_REDEMPTION_RATE;
    }
    
    /**
     * Tích điểm từ đơn hàng
     */
    public static function earnPoints($user_id, $order_id, $order_amount, $description = '') {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $conn->beginTransaction();
            
            // Tính điểm thưởng
            $points = self::calculatePoints($order_amount);
            
            if ($points <= 0) {
                $conn->rollback();
                return true; // Không có điểm để tích
            }
            
            // Lấy thông tin điểm hiện tại
            $stmt = $conn->prepare("SELECT * FROM loyalty_points WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $loyalty = $stmt->fetch();
            
            if (!$loyalty) {
                // Tạo tài khoản điểm nếu chưa có
                self::createLoyaltyAccount($user_id);
                $stmt = $conn->prepare("SELECT * FROM loyalty_points WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $loyalty = $stmt->fetch();
            }
            
            $balance_before = $loyalty['points'];
            $balance_after = $balance_before + $points;
            $total_earned = $loyalty['total_earned'] + $points;
            
            // Cập nhật điểm
            $stmt = $conn->prepare("
                UPDATE loyalty_points 
                SET points = ?, total_earned = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE user_id = ?
            ");
            $stmt->execute([$balance_after, $total_earned, $user_id]);
            
            // Ghi lịch sử tích điểm
            $stmt = $conn->prepare("
                INSERT INTO loyalty_transactions 
                (user_id, type, points, balance_before, balance_after, description, reference_id, expires_at) 
                VALUES (?, 'earn', ?, ?, ?, ?, ?, ?)
            ");
            $expires_at = date('Y-m-d', strtotime('+' . self::POINTS_EXPIRY_DAYS . ' days'));
            $stmt->execute([
                $user_id, 
                $points, 
                $balance_before, 
                $balance_after, 
                $description ?: "Tích điểm từ đơn hàng #$order_id", 
                $order_id,
                $expires_at
            ]);
            
            $conn->commit();
            return $points;
            
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollback();
            }
            return false;
        }
    }
    
    /**
     * Sử dụng điểm để thanh toán
     */
    public static function redeemPoints($user_id, $points, $order_id, $description = '') {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $conn->beginTransaction();
            
            // Lấy thông tin điểm hiện tại
            $stmt = $conn->prepare("SELECT * FROM loyalty_points WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $loyalty = $stmt->fetch();
            
            if (!$loyalty || $loyalty['points'] < $points) {
                throw new Exception('Điểm không đủ để sử dụng');
            }
            
            $balance_before = $loyalty['points'];
            $balance_after = $balance_before - $points;
            $total_redeemed = $loyalty['total_redeemed'] + $points;
            
            // Cập nhật điểm
            $stmt = $conn->prepare("
                UPDATE loyalty_points 
                SET points = ?, total_redeemed = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE user_id = ?
            ");
            $stmt->execute([$balance_after, $total_redeemed, $user_id]);
            
            // Ghi lịch sử sử dụng điểm
            $stmt = $conn->prepare("
                INSERT INTO loyalty_transactions 
                (user_id, type, points, balance_before, balance_after, description, reference_id) 
                VALUES (?, 'redeem', ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $user_id, 
                $points, 
                $balance_before, 
                $balance_after, 
                $description ?: "Sử dụng điểm cho đơn hàng #$order_id", 
                $order_id
            ]);
            
            $conn->commit();
            return true;
            
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollback();
            }
            return false;
        }
    }
    
    /**
     * Lấy thông tin điểm của user
     */
    public static function getLoyaltyInfo($user_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT lp.*, u.full_name, u.email
                FROM loyalty_points lp
                LEFT JOIN users u ON lp.user_id = u.id
                WHERE lp.user_id = ?
            ");
            $stmt->execute([$user_id]);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy lịch sử giao dịch điểm
     */
    public static function getTransactionHistory($user_id, $limit = 20, $offset = 0) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT * FROM loyalty_transactions 
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$user_id, $limit, $offset]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Kiểm tra điểm hết hạn
     */
    public static function checkExpiredPoints() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Lấy các giao dịch điểm sắp hết hạn
            $stmt = $conn->prepare("
                SELECT user_id, SUM(points) as expired_points
                FROM loyalty_transactions 
                WHERE type = 'earn' 
                AND expires_at <= CURDATE() 
                AND expires_at > DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY user_id
            ");
            $stmt->execute();
            $expired_transactions = $stmt->fetchAll();
            
            foreach ($expired_transactions as $transaction) {
                // Trừ điểm hết hạn
                $stmt = $conn->prepare("
                    UPDATE loyalty_points 
                    SET points = GREATEST(0, points - ?), updated_at = CURRENT_TIMESTAMP 
                    WHERE user_id = ?
                ");
                $stmt->execute([$transaction['expired_points'], $transaction['user_id']]);
                
                // Ghi lịch sử điểm hết hạn
                $stmt = $conn->prepare("
                    INSERT INTO loyalty_transactions 
                    (user_id, type, points, balance_before, balance_after, description) 
                    VALUES (?, 'expire', ?, 0, 0, 'Điểm hết hạn')
                ");
                $stmt->execute([$transaction['user_id'], $transaction['expired_points']]);
            }
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy cấp độ thành viên
     */
    public static function getMemberLevel($user_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Tính tổng chi tiêu
            $stmt = $conn->prepare("
                SELECT COALESCE(SUM(total_amount), 0) as total_spent
                FROM orders 
                WHERE user_id = ? AND status = 'completed'
            ");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch();
            $total_spent = $result['total_spent'];
            
            // Xác định cấp độ
            if ($total_spent >= 10000000) {
                return 'diamond'; // Kim cương - 10M+
            } elseif ($total_spent >= 5000000) {
                return 'platinum'; // Bạch kim - 5M+
            } elseif ($total_spent >= 2000000) {
                return 'gold'; // Vàng - 2M+
            } elseif ($total_spent >= 500000) {
                return 'silver'; // Bạc - 500K+
            } else {
                return 'bronze'; // Đồng - <500K
            }
            
        } catch (Exception $e) {
            return 'bronze';
        }
    }
    
    /**
     * Lấy ưu đãi theo cấp độ
     */
    public static function getMemberBenefits($level) {
        $benefits = [
            'bronze' => [
                'name' => 'Thành viên Đồng',
                'discount' => 0,
                'points_bonus' => 1.0,
                'free_shipping' => false,
                'priority_support' => false
            ],
            'silver' => [
                'name' => 'Thành viên Bạc',
                'discount' => 5,
                'points_bonus' => 1.2,
                'free_shipping' => false,
                'priority_support' => false
            ],
            'gold' => [
                'name' => 'Thành viên Vàng',
                'discount' => 10,
                'points_bonus' => 1.5,
                'free_shipping' => true,
                'priority_support' => true
            ],
            'platinum' => [
                'name' => 'Thành viên Bạch kim',
                'discount' => 15,
                'points_bonus' => 2.0,
                'free_shipping' => true,
                'priority_support' => true
            ],
            'diamond' => [
                'name' => 'Thành viên Kim cương',
                'discount' => 20,
                'points_bonus' => 2.5,
                'free_shipping' => true,
                'priority_support' => true
            ]
        ];
        
        return $benefits[$level] ?? $benefits['bronze'];
    }
}
?>
