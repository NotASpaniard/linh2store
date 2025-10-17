<?php
/**
 * Create OAuth Tables
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/database.php';

echo "<h1>🔧 Tạo OAuth Tables</h1>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    
    // SQL để tạo bảng oauth_accounts
    $sql = "
    CREATE TABLE IF NOT EXISTS oauth_accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        provider VARCHAR(50) NOT NULL,
        provider_id VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_provider (provider, provider_id),
        INDEX idx_user_id (user_id),
        INDEX idx_provider (provider)
    )";
    
    $conn->exec($sql);
    echo "<p>✅ <strong>Bảng oauth_accounts:</strong> Đã tạo thành công</p>";
    
    // Kiểm tra xem cột avatar đã tồn tại chưa
    $stmt = $conn->query("SHOW COLUMNS FROM users LIKE 'avatar'");
    if ($stmt->rowCount() == 0) {
        $sql = "ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER phone";
        $conn->exec($sql);
        echo "<p>✅ <strong>Cột avatar:</strong> Đã thêm vào bảng users</p>";
    } else {
        echo "<p>✅ <strong>Cột avatar:</strong> Đã tồn tại trong bảng users</p>";
    }
    
    echo "</div>";
    
    echo "<h2>🧪 Test Database:</h2>";
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px;'>";
    
    // Test tạo OAuth account
    $stmt = $conn->prepare("
        INSERT INTO oauth_accounts (user_id, provider, provider_id, created_at) 
        VALUES (1, 'google', 'test_123', CURRENT_TIMESTAMP)
        ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP
    ");
    
    try {
        $stmt->execute();
        echo "<p>✅ <strong>Test insert OAuth:</strong> Thành công</p>";
        
        // Xóa test record
        $stmt = $conn->prepare("DELETE FROM oauth_accounts WHERE provider_id = 'test_123'");
        $stmt->execute();
        echo "<p>✅ <strong>Test delete OAuth:</strong> Thành công</p>";
        
    } catch (Exception $e) {
        echo "<p>⚠️ <strong>Test insert OAuth:</strong> " . $e->getMessage() . "</p>";
    }
    
    echo "</div>";
    
    echo "<h2>✅ Hoàn thành!</h2>";
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 8px;'>";
    echo "<p>OAuth tables đã được tạo thành công. Bây giờ bạn có thể:</p>";
    echo "<ul>";
    echo "<li>✅ Test Google OAuth login</li>";
    echo "<li>✅ Test Facebook OAuth login</li>";
    echo "<li>✅ Lưu thông tin OAuth users</li>";
    echo "</ul>";
    echo "<p><a href='test-google-oauth.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test OAuth ngay</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px;'>";
    echo "<h2>❌ Lỗi:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p><strong>Stack trace:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>
