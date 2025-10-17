<?php
/**
 * Installment Payment System
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/database.php';

class InstallmentSystem {
    
    /**
     * Lấy danh sách gói trả góp
     */
    public static function getInstallmentPlans() {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT * FROM installment_plans 
                WHERE status = 'active' 
                ORDER BY months ASC, interest_rate ASC
            ");
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Tính toán trả góp
     */
    public static function calculateInstallment($amount, $plan_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("SELECT * FROM installment_plans WHERE id = ?");
            $stmt->execute([$plan_id]);
            $plan = $stmt->fetch();
            
            if (!$plan) {
                return false;
            }
            
            // Kiểm tra số tiền có phù hợp không
            if ($amount < $plan['min_amount'] || $amount > $plan['max_amount']) {
                return false;
            }
            
            $months = $plan['months'];
            $interest_rate = $plan['interest_rate'];
            
            // Tính lãi suất
            $interest_amount = ($amount * $interest_rate * $months) / 100;
            $total_amount = $amount + $interest_amount;
            $monthly_payment = $total_amount / $months;
            
            return [
                'plan' => $plan,
                'original_amount' => $amount,
                'interest_amount' => $interest_amount,
                'total_amount' => $total_amount,
                'monthly_payment' => $monthly_payment,
                'months' => $months
            ];
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Tạo đơn hàng trả góp
     */
    public static function createInstallmentOrder($order_id, $plan_id, $amount) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $calculation = self::calculateInstallment($amount, $plan_id);
            if (!$calculation) {
                return false;
            }
            
            $conn->beginTransaction();
            
            // Tạo đơn hàng trả góp
            $stmt = $conn->prepare("
                INSERT INTO installment_orders 
                (order_id, installment_plan_id, total_amount, monthly_payment, 
                 interest_amount, remaining_amount, status, start_date, end_date) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending', CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? MONTH))
            ");
            $stmt->execute([
                $order_id,
                $plan_id,
                $calculation['total_amount'],
                $calculation['monthly_payment'],
                $calculation['interest_amount'],
                $calculation['total_amount'],
                $calculation['months']
            ]);
            
            $installment_order_id = $conn->lastInsertId();
            
            // Tạo lịch thanh toán
            for ($i = 1; $i <= $calculation['months']; $i++) {
                $due_date = date('Y-m-d', strtotime("+$i months"));
                
                $stmt = $conn->prepare("
                    INSERT INTO installment_payments 
                    (installment_order_id, payment_number, amount, due_date, status) 
                    VALUES (?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([
                    $installment_order_id,
                    $i,
                    $calculation['monthly_payment'],
                    $due_date
                ]);
            }
            
            $conn->commit();
            return $installment_order_id;
            
        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollback();
            }
            return false;
        }
    }
    
    /**
     * Lấy thông tin đơn hàng trả góp
     */
    public static function getInstallmentOrder($order_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT io.*, ip.name as plan_name, ip.months, ip.interest_rate
                FROM installment_orders io
                JOIN installment_plans ip ON io.installment_plan_id = ip.id
                WHERE io.order_id = ?
            ");
            $stmt->execute([$order_id]);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy lịch thanh toán
     */
    public static function getPaymentSchedule($installment_order_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT * FROM installment_payments 
                WHERE installment_order_id = ? 
                ORDER BY payment_number ASC
            ");
            $stmt->execute([$installment_order_id]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Thanh toán kỳ trả góp
     */
    public static function makePayment($installment_order_id, $payment_number, $amount, $payment_method, $transaction_id = '') {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $conn->beginTransaction();
            
            // Lấy thông tin kỳ thanh toán
            $stmt = $conn->prepare("
                SELECT * FROM installment_payments 
                WHERE installment_order_id = ? AND payment_number = ?
            ");
            $stmt->execute([$installment_order_id, $payment_number]);
            $payment = $stmt->fetch();
            
            if (!$payment || $payment['status'] === 'paid') {
                throw new Exception('Kỳ thanh toán không hợp lệ');
            }
            
            // Cập nhật trạng thái thanh toán
            $stmt = $conn->prepare("
                UPDATE installment_payments 
                SET status = 'paid', paid_date = CURDATE(), 
                    payment_method = ?, transaction_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$payment_method, $transaction_id, $payment['id']]);
            
            // Cập nhật số tiền còn lại
            $stmt = $conn->prepare("
                UPDATE installment_orders 
                SET remaining_amount = remaining_amount - ?,
                    status = CASE 
                        WHEN remaining_amount - ? <= 0 THEN 'completed'
                        ELSE 'active'
                    END
                WHERE id = ?
            ");
            $stmt->execute([$amount, $amount, $installment_order_id]);
            
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
     * Lấy thống kê trả góp
     */
    public static function getInstallmentStats($user_id = null) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $where_clause = "1=1";
            $params = [];
            
            if ($user_id) {
                $where_clause = "o.user_id = ?";
                $params[] = $user_id;
            }
            
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_amount,
                    SUM(interest_amount) as total_interest,
                    AVG(monthly_payment) as avg_monthly_payment,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_orders,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
                    COUNT(CASE WHEN status = 'defaulted' THEN 1 END) as defaulted_orders
                FROM installment_orders io
                JOIN orders o ON io.order_id = o.id
                WHERE $where_clause
            ");
            $stmt->execute($params);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Kiểm tra đủ điều kiện trả góp
     */
    public static function checkEligibility($user_id, $amount) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Kiểm tra lịch sử thanh toán
            $stmt = $conn->prepare("
                SELECT COUNT(*) as defaulted_count
                FROM installment_orders io
                JOIN orders o ON io.order_id = o.id
                WHERE o.user_id = ? AND io.status = 'defaulted'
            ");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch();
            
            // Nếu có lịch sử trả góp không đúng hạn, từ chối
            if ($result['defaulted_count'] > 0) {
                return false;
            }
            
            // Kiểm tra số tiền tối thiểu
            $stmt = $conn->prepare("
                SELECT MIN(min_amount) as min_amount
                FROM installment_plans 
                WHERE status = 'active'
            ");
            $stmt->execute();
            $result = $stmt->fetch();
            
            return $amount >= $result['min_amount'];
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy gói trả góp phù hợp
     */
    public static function getSuitablePlans($amount) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT * FROM installment_plans 
                WHERE status = 'active' 
                AND min_amount <= ? AND max_amount >= ?
                ORDER BY months ASC, interest_rate ASC
            ");
            $stmt->execute([$amount, $amount]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
