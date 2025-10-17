<?php
/**
 * Wallet System
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/database.php';

class WalletSystem {
    
    /**
     * Tạo ví cho user mới
     */
    public static function createWallet($user_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Kiểm tra ví đã tồn tại chưa
            $stmt = $conn->prepare("SELECT id FROM user_wallets WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            if ($stmt->fetch()) {
                return true; // Ví đã tồn tại
            }
            
            // Tạo ví mới
            $stmt = $conn->prepare("
                INSERT INTO user_wallets (user_id, balance, frozen_balance, status) 
                VALUES (?, 0.00, 0.00, 'active')
            ");
            $stmt->execute([$user_id]);
            
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Lấy thông tin ví
     */
    public static function getWallet($user_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT w.*, u.full_name, u.email 
                FROM user_wallets w
                LEFT JOIN users u ON w.user_id = u.id
                WHERE w.user_id = ?
            ");
            $stmt->execute([$user_id]);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Nạp tiền vào ví
     */
    public static function deposit($user_id, $amount, $description = '', $reference_id = '') {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $conn->beginTransaction();
            
            // Lấy thông tin ví hiện tại
            $wallet = self::getWallet($user_id);
            if (!$wallet) {
                throw new Exception('Ví không tồn tại');
            }
            
            $balance_before = $wallet['balance'];
            $balance_after = $balance_before + $amount;
            
            // Cập nhật số dư ví
            $stmt = $conn->prepare("
                UPDATE user_wallets 
                SET balance = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE user_id = ?
            ");
            $stmt->execute([$balance_after, $user_id]);
            
            // Ghi lịch sử giao dịch
            $stmt = $conn->prepare("
                INSERT INTO wallet_transactions 
                (wallet_id, type, amount, balance_before, balance_after, description, reference_id, status) 
                VALUES (?, 'deposit', ?, ?, ?, ?, ?, 'completed')
            ");
            $stmt->execute([
                $wallet['id'], 
                $amount, 
                $balance_before, 
                $balance_after, 
                $description, 
                $reference_id
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
     * Rút tiền từ ví
     */
    public static function withdraw($user_id, $amount, $description = '', $reference_id = '') {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $conn->beginTransaction();
            
            // Lấy thông tin ví hiện tại
            $wallet = self::getWallet($user_id);
            if (!$wallet) {
                throw new Exception('Ví không tồn tại');
            }
            
            if ($wallet['balance'] < $amount) {
                throw new Exception('Số dư không đủ');
            }
            
            $balance_before = $wallet['balance'];
            $balance_after = $balance_before - $amount;
            
            // Cập nhật số dư ví
            $stmt = $conn->prepare("
                UPDATE user_wallets 
                SET balance = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE user_id = ?
            ");
            $stmt->execute([$balance_after, $user_id]);
            
            // Ghi lịch sử giao dịch
            $stmt = $conn->prepare("
                INSERT INTO wallet_transactions 
                (wallet_id, type, amount, balance_before, balance_after, description, reference_id, status) 
                VALUES (?, 'withdraw', ?, ?, ?, ?, ?, 'completed')
            ");
            $stmt->execute([
                $wallet['id'], 
                $amount, 
                $balance_before, 
                $balance_after, 
                $description, 
                $reference_id
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
     * Thanh toán bằng ví
     */
    public static function payWithWallet($user_id, $amount, $order_id, $description = '') {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $conn->beginTransaction();
            
            // Lấy thông tin ví hiện tại
            $wallet = self::getWallet($user_id);
            if (!$wallet) {
                throw new Exception('Ví không tồn tại');
            }
            
            if ($wallet['balance'] < $amount) {
                throw new Exception('Số dư ví không đủ');
            }
            
            $balance_before = $wallet['balance'];
            $balance_after = $balance_before - $amount;
            
            // Cập nhật số dư ví
            $stmt = $conn->prepare("
                UPDATE user_wallets 
                SET balance = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE user_id = ?
            ");
            $stmt->execute([$balance_after, $user_id]);
            
            // Ghi lịch sử giao dịch
            $stmt = $conn->prepare("
                INSERT INTO wallet_transactions 
                (wallet_id, type, amount, balance_before, balance_after, description, reference_id, status) 
                VALUES (?, 'payment', ?, ?, ?, ?, ?, 'completed')
            ");
            $stmt->execute([
                $wallet['id'], 
                $amount, 
                $balance_before, 
                $balance_after, 
                $description, 
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
     * Lấy lịch sử giao dịch ví
     */
    public static function getTransactionHistory($user_id, $limit = 20, $offset = 0) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("
                SELECT wt.*, w.user_id
                FROM wallet_transactions wt
                JOIN user_wallets w ON wt.wallet_id = w.id
                WHERE w.user_id = ?
                ORDER BY wt.created_at DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$user_id, $limit, $offset]);
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Kiểm tra số dư ví
     */
    public static function getBalance($user_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("SELECT balance FROM user_wallets WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch();
            
            return $result ? $result['balance'] : 0;
            
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>
