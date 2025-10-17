<?php
/**
 * System Check - Kiểm tra toàn bộ hệ thống
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

echo "<h1>🔍 System Check - Linh2Store</h1>\n";
echo "<p>Kiểm tra toàn bộ hệ thống để tránh xung đột...</p>\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Kiểm tra database connection
echo "<h2>📊 Database Connection</h2>\n";
try {
    require_once 'config/database.php';
    $db = new Database();
    $conn = $db->getConnection();
    $success[] = "✅ Database connection successful";
} catch (Exception $e) {
    $errors[] = "❌ Database connection failed: " . $e->getMessage();
}

// 2. Kiểm tra authentication system
echo "<h2>🔐 Authentication System</h2>\n";
try {
    require_once 'config/auth-middleware.php';
    $success[] = "✅ AuthMiddleware loaded successfully";
} catch (Exception $e) {
    $errors[] = "❌ AuthMiddleware failed: " . $e->getMessage();
}

// 3. Kiểm tra JWT system
echo "<h2>🎫 JWT System</h2>\n";
try {
    require_once 'config/jwt.php';
    $success[] = "✅ JWT system loaded successfully";
} catch (Exception $e) {
    $errors[] = "❌ JWT system failed: " . $e->getMessage();
}

// 4. Kiểm tra OAuth system
echo "<h2>🔗 OAuth System</h2>\n";
try {
    require_once 'config/oauth.php';
    $success[] = "✅ OAuth system loaded successfully";
} catch (Exception $e) {
    $errors[] = "❌ OAuth system failed: " . $e->getMessage();
}

// 5. Kiểm tra AI systems
echo "<h2>🤖 AI Systems</h2>\n";
$aiSystems = [
    'AI Recommendations' => 'config/ai-recommendations.php',
    'AI Chatbot' => 'config/ai-chatbot.php',
    'AI Sentiment Analysis' => 'config/ai-sentiment-analysis.php',
    'AI Price Prediction' => 'config/ai-price-prediction.php'
];

foreach ($aiSystems as $name => $file) {
    try {
        if (file_exists($file)) {
            require_once $file;
            $success[] = "✅ $name loaded successfully";
        } else {
            $warnings[] = "⚠️ $name file not found: $file";
        }
    } catch (Exception $e) {
        $errors[] = "❌ $name failed: " . $e->getMessage();
    }
}

// 6. Kiểm tra API endpoints
echo "<h2>🌐 API Endpoints</h2>\n";
$apiEndpoints = [
    'Search API' => 'api/search.php',
    'Cart API' => 'api/cart.php',
    'Orders API' => 'api/orders.php',
    'AI Recommendations API' => 'api/ai-recommendations.php',
    'AI Chatbot API' => 'api/ai-chatbot.php',
    'AI Sentiment Analysis API' => 'api/ai-sentiment-analysis.php'
];

foreach ($apiEndpoints as $name => $file) {
    if (file_exists($file)) {
        $success[] = "✅ $name exists: $file";
    } else {
        $warnings[] = "⚠️ $name not found: $file";
    }
}

// 7. Kiểm tra demo pages
echo "<h2>🎨 Demo Pages</h2>\n";
$demoPages = [
    'AI Recommendations Demo' => 'ai-demo.php',
    'AI Chatbot Demo' => 'ai-chatbot-demo.php',
    'AI Sentiment Analysis Demo' => 'ai-sentiment-demo.php'
];

foreach ($demoPages as $name => $file) {
    if (file_exists($file)) {
        $success[] = "✅ $name exists: $file";
    } else {
        $warnings[] = "⚠️ $name not found: $file";
    }
}

// 8. Kiểm tra JavaScript conflicts
echo "<h2>⚡ JavaScript System</h2>\n";
if (file_exists('assets/js/main.js')) {
    $jsContent = file_get_contents('assets/js/main.js');
    
    // Kiểm tra search functions
    if (strpos($jsContent, 'search') !== false || strpos($jsContent, 'Search') !== false) {
        $warnings[] = "⚠️ JavaScript may contain search functions that could conflict";
    } else {
        $success[] = "✅ JavaScript clean - no search functions found";
    }
    
    // Kiểm tra cart functions
    if (strpos($jsContent, 'addToCart') !== false) {
        $success[] = "✅ Cart functions present in JavaScript";
    } else {
        $warnings[] = "⚠️ Cart functions not found in JavaScript";
    }
} else {
    $warnings[] = "⚠️ Main JavaScript file not found";
}

// 9. Kiểm tra search system
echo "<h2>🔍 Search System</h2>\n";
$searchFiles = [
    'Search API' => 'api/search.php',
    'Search Page' => 'san-pham/search.php',
    'Homepage Search' => 'index.php',
    'Product Page Search' => 'san-pham/index.php'
];

foreach ($searchFiles as $name => $file) {
    if (file_exists($file)) {
        $success[] = "✅ $name exists: $file";
    } else {
        $warnings[] = "⚠️ $name not found: $file";
    }
}

// 10. Kiểm tra database tables
echo "<h2>🗄️ Database Tables</h2>\n";
if (isset($conn)) {
    $tables = [
        'users', 'products', 'brands', 'categories', 'orders', 'order_items',
        'user_behavior', 'ai_recommendations', 'product_features', 'product_similarity',
        'chat_conversations', 'chat_messages', 'ai_knowledge_base',
        'sentiment_analysis_results', 'sentiment_keywords',
        'price_history', 'price_predictions', 'market_data'
    ];
    
    foreach ($tables as $table) {
        try {
            $stmt = $conn->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->fetch()) {
                $success[] = "✅ Table $table exists";
            } else {
                $warnings[] = "⚠️ Table $table not found";
            }
        } catch (Exception $e) {
            $warnings[] = "⚠️ Could not check table $table: " . $e->getMessage();
        }
    }
}

// 11. Kiểm tra file permissions
echo "<h2>📁 File Permissions</h2>\n";
$importantFiles = [
    'config/database.php',
    'config/auth-middleware.php',
    'config/jwt.php',
    'config/oauth.php',
    'assets/js/main.js',
    'api/search.php',
    'api/cart.php'
];

foreach ($importantFiles as $file) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            $success[] = "✅ $file is readable";
        } else {
            $errors[] = "❌ $file is not readable";
        }
    } else {
        $warnings[] = "⚠️ $file not found";
    }
}

// 12. Kiểm tra xung đột namespace
echo "<h2>🔧 Namespace Conflicts</h2>\n";
$conflictCheck = [
    'AuthMiddleware' => 'config/auth-middleware.php',
    'JWT' => 'config/jwt.php',
    'OAuthProvider' => 'config/oauth.php',
    'AIRecommendations' => 'config/ai-recommendations.php',
    'AIChatbot' => 'config/ai-chatbot.php',
    'AISentimentAnalysis' => 'config/ai-sentiment-analysis.php',
    'AIPricePrediction' => 'config/ai-price-prediction.php'
];

foreach ($conflictCheck as $class => $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, "class $class") !== false) {
            $success[] = "✅ Class $class defined in $file";
        } else {
            $warnings[] = "⚠️ Class $class not found in $file";
        }
    }
}

// Tổng kết
echo "<h2>📋 Summary</h2>\n";
echo "<div style='background: #d4edda; padding: 1rem; border-radius: 4px; margin: 1rem 0;'>\n";
echo "<h3>✅ Success (" . count($success) . ")</h3>\n";
foreach ($success as $item) {
    echo "<p>$item</p>\n";
}
echo "</div>\n";

if (!empty($warnings)) {
    echo "<div style='background: #fff3cd; padding: 1rem; border-radius: 4px; margin: 1rem 0;'>\n";
    echo "<h3>⚠️ Warnings (" . count($warnings) . ")</h3>\n";
    foreach ($warnings as $item) {
        echo "<p>$item</p>\n";
    }
    echo "</div>\n";
}

if (!empty($errors)) {
    echo "<div style='background: #f8d7da; padding: 1rem; border-radius: 4px; margin: 1rem 0;'>\n";
    echo "<h3>❌ Errors (" . count($errors) . ")</h3>\n";
    foreach ($errors as $item) {
        echo "<p>$item</p>\n";
    }
    echo "</div>\n";
}

// Kết luận
echo "<h2>🎯 Conclusion</h2>\n";
if (empty($errors)) {
    echo "<div style='background: #d1ecf1; padding: 1rem; border-radius: 4px; margin: 1rem 0;'>\n";
    echo "<h3>🎉 System Status: HEALTHY</h3>\n";
    echo "<p>Không có lỗi nghiêm trọng nào được phát hiện. Hệ thống hoạt động bình thường.</p>\n";
    echo "</div>\n";
} else {
    echo "<div style='background: #f8d7da; padding: 1rem; border-radius: 4px; margin: 1rem 0;'>\n";
    echo "<h3>⚠️ System Status: NEEDS ATTENTION</h3>\n";
    echo "<p>Có " . count($errors) . " lỗi cần được khắc phục trước khi hệ thống hoạt động ổn định.</p>\n";
    echo "</div>\n";
}

echo "<p><strong>Total Checks:</strong> " . (count($success) + count($warnings) + count($errors)) . "</p>\n";
echo "<p><strong>Success Rate:</strong> " . round((count($success) / (count($success) + count($warnings) + count($errors))) * 100, 1) . "%</p>\n";
?>
