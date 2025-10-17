<?php
/**
 * Setup AI Chatbot System
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h2>💬 Setting up AI Chatbot System...</h2>\n";
    
    // Read and execute SQL schema
    $sql = file_get_contents('database/ai-chatbot-schema.sql');
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
        echo "\n🎉 AI Chatbot System setup completed successfully!\n";
        
        // Test the system
        echo "\n<h3>🧪 Testing AI Chatbot System...</h3>\n";
        
        // Test configuration
        $configSql = "SELECT COUNT(*) as count FROM chatbot_config";
        $stmt = $conn->prepare($configSql);
        $stmt->execute();
        $configCount = $stmt->fetch()['count'];
        echo "✅ Chatbot Configuration entries: $configCount\n";
        
        // Test tables
        $tables = ['chat_conversations', 'chat_messages', 'ai_knowledge_base', 'chat_feedback', 'chatbot_training_log'];
        foreach ($tables as $table) {
            $tableSql = "SELECT COUNT(*) as count FROM $table";
            $stmt = $conn->prepare($tableSql);
            $stmt->execute();
            $count = $stmt->fetch()['count'];
            echo "✅ Table $table: $count records\n";
        }
        
        // Test knowledge base
        $kbSql = "SELECT COUNT(*) as count FROM ai_knowledge_base";
        $stmt = $conn->prepare($kbSql);
        $stmt->execute();
        $kbCount = $stmt->fetch()['count'];
        echo "✅ Knowledge Base entries: $kbCount\n";
        
        echo "\n🚀 AI Chatbot System is ready to use!\n";
        echo "\nNext steps:\n";
        echo "1. Test the chatbot at ai-chatbot-demo.php\n";
        echo "2. Add more knowledge base entries\n";
        echo "3. Customize chatbot responses\n";
        
    } else {
        echo "\n⚠️ Setup completed with errors. Please check the error messages above.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
}
?>
