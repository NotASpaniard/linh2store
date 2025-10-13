<?php
/**
 * Script setup database
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Đọc file schema
    $schema = file_get_contents('database/schema.sql');
    
    // Tách các câu lệnh SQL
    $statements = explode(';', $schema);
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $conn->exec($statement);
            $success_count++;
        } catch (PDOException $e) {
            $error_count++;
            echo "Lỗi: " . $e->getMessage() . "\n";
            echo "SQL: " . substr($statement, 0, 100) . "...\n\n";
        }
    }
    
    echo "✅ Setup database hoàn thành!\n";
    echo "✅ Thành công: $success_count câu lệnh\n";
    echo "❌ Lỗi: $error_count câu lệnh\n\n";
    
    echo "🔑 Thông tin đăng nhập:\n";
    echo "👤 Admin: username=admin, password=password\n";
    echo "👤 User: username=testuser, password=password\n\n";
    
    echo "🌐 Đường dẫn:\n";
    echo "🏠 Trang chủ: http://localhost/linh2store/\n";
    echo "🔐 Đăng nhập: http://localhost/linh2store/auth/dang-nhap.php\n";
    echo "📝 Đăng ký: http://localhost/linh2store/auth/dang-ky.php\n";
    echo "⚙️ Admin: http://localhost/linh2store/admin/\n";
    
} catch (Exception $e) {
    echo "❌ Lỗi kết nối database: " . $e->getMessage() . "\n";
    echo "Vui lòng kiểm tra:\n";
    echo "1. XAMPP đang chạy\n";
    echo "2. MySQL service đang chạy\n";
    echo "3. Database 'linh2store' đã được tạo\n";
    echo "4. Cấu hình database trong config/database.php\n";
}
?>
