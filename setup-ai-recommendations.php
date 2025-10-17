<?php
/**
 * Setup AI Recommendations System
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h2>🤖 Setting up AI Recommendations System...</h2>\n";
    
    // Read and execute SQL schema
    $sql = file_get_contents('database/ai-recommendations-schema.sql');
    $statements = explode(';', $sql);
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;
        
        try {
            $conn->exec($statement);
            $successCount++;
            echo "✅ Executed: " . substr($statement, 0, 50) . "...\n";
        } catch (PDOException $e) {
            $errorCount++;
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n<h3>📊 Setup Results:</h3>\n";
    echo "✅ Successful statements: $successCount\n";
    echo "❌ Failed statements: $errorCount\n";
    
    if ($errorCount === 0) {
        echo "\n🎉 AI Recommendations System setup completed successfully!\n";
        
        // Test the system
        echo "\n<h3>🧪 Testing AI System...</h3>\n";
        
        // Test configuration
        $configSql = "SELECT COUNT(*) as count FROM ai_config";
        $stmt = $conn->prepare($configSql);
        $stmt->execute();
        $configCount = $stmt->fetch()['count'];
        echo "✅ AI Configuration entries: $configCount\n";
        
        // Test tables
        $tables = ['user_behavior', 'ai_recommendations', 'product_features', 'product_similarity', 'ai_training_log'];
        foreach ($tables as $table) {
            $tableSql = "SELECT COUNT(*) as count FROM $table";
            $stmt = $conn->prepare($tableSql);
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "✅ Table $table: $count records\n";
        }
        
        echo "\n🚀 AI Recommendations System is ready to use!\n";
        echo "\nNext steps:\n";
        echo "1. Add product features to product_features table\n";
        echo "2. Start tracking user behavior\n";
        echo "3. Generate recommendations using AIRecommendations class\n";
        
    } else {
        echo "\n⚠️ Setup completed with errors. Please check the error messages above.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
}
?>
