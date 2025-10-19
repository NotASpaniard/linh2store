<?php
/**
 * AI Training System
 * Linh2Store - Advanced AI Chatbot Training
 */

require_once __DIR__ . '/database.php';

class AITraining {
    private $conn;
    
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }
    
    /**
     * Train AI with new conversation data
     */
    public function trainWithConversation($userMessage, $botResponse, $context = []) {
        try {
            // Extract keywords from user message
            $keywords = $this->extractKeywords($userMessage);
            
            // Determine category
            $category = $this->categorizeMessage($userMessage);
            
            // Calculate confidence score
            $confidence = $this->calculateConfidence($userMessage, $botResponse);
            
            // Store training data
            $sql = "INSERT INTO ai_training_data (user_message, bot_response, keywords, category, confidence_score, context, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                $userMessage,
                $botResponse,
                implode(',', $keywords),
                $category,
                $confidence,
                json_encode($context)
            ]);
            
            if ($result) {
                // Update knowledge base with new patterns
                $this->updateKnowledgeBase($userMessage, $botResponse, $category, $keywords);
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("AI Training Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Extract keywords from message
     */
    private function extractKeywords($message) {
        $keywords = [];
        
        // Common Vietnamese keywords
        $keywordPatterns = [
            'sản phẩm' => 'product',
            'son môi' => 'lipstick',
            'mỹ phẩm' => 'cosmetics',
            'thương hiệu' => 'brand',
            'giá' => 'price',
            'đơn hàng' => 'order',
            'giao hàng' => 'shipping',
            'thanh toán' => 'payment',
            'liên hệ' => 'contact',
            'hỗ trợ' => 'support',
            'tìm kiếm' => 'search',
            'màu' => 'color',
            'kích thước' => 'size',
            'chất lượng' => 'quality'
        ];
        
        $message = strtolower($message);
        foreach ($keywordPatterns as $vietnamese => $english) {
            if (strpos($message, $vietnamese) !== false) {
                $keywords[] = $vietnamese;
            }
        }
        
        return $keywords;
    }
    
    /**
     * Categorize message
     */
    private function categorizeMessage($message) {
        $message = strtolower($message);
        
        if (strpos($message, 'tìm') !== false || strpos($message, 'sản phẩm') !== false) {
            return 'product_search';
        } elseif (strpos($message, 'đơn hàng') !== false || strpos($message, 'kiểm tra') !== false) {
            return 'order_tracking';
        } elseif (strpos($message, 'thương hiệu') !== false || strpos($message, 'brand') !== false) {
            return 'brand_info';
        } elseif (strpos($message, 'giao hàng') !== false || strpos($message, 'ship') !== false) {
            return 'shipping_info';
        } elseif (strpos($message, 'thanh toán') !== false || strpos($message, 'payment') !== false) {
            return 'payment_info';
        } elseif (strpos($message, 'liên hệ') !== false || strpos($message, 'contact') !== false) {
            return 'contact_info';
        } else {
            return 'general';
        }
    }
    
    /**
     * Calculate confidence score
     */
    private function calculateConfidence($userMessage, $botResponse) {
        // Linh2Store confidence calculation based on response length and keywords
        $confidence = 0.5; // Base confidence
        
        // Increase confidence for longer, more detailed responses
        if (strlen($botResponse) > 100) {
            $confidence += 0.2;
        }
        
        // Increase confidence if response contains helpful information
        if (strpos($botResponse, 'có thể') !== false || strpos($botResponse, 'giúp') !== false) {
            $confidence += 0.1;
        }
        
        // Increase confidence for specific product/service mentions
        if (strpos($botResponse, 'sản phẩm') !== false || strpos($botResponse, 'thương hiệu') !== false) {
            $confidence += 0.1;
        }
        
        return min(1.0, $confidence);
    }
    
    /**
     * Update knowledge base with new patterns
     */
    private function updateKnowledgeBase($userMessage, $botResponse, $category, $keywords) {
        try {
            // Check if similar pattern already exists
            $sql = "SELECT id FROM ai_knowledge_base 
                    WHERE question LIKE ? OR answer LIKE ? 
                    LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(["%$userMessage%", "%$botResponse%"]);
            
            if ($stmt->rowCount() == 0) {
                // Add new knowledge entry
                $sql = "INSERT INTO ai_knowledge_base (category, keywords, question, answer, priority, is_active, created_at) 
                        VALUES (?, ?, ?, ?, ?, 1, NOW())";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    $category,
                    implode(',', $keywords),
                    $userMessage,
                    $botResponse,
                    5 // Medium priority
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Knowledge Base Update Error: " . $e->getMessage());
        }
    }
    
    /**
     * Get training statistics
     */
    public function getTrainingStats() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_conversations,
                        AVG(confidence_score) as avg_confidence,
                        COUNT(DISTINCT category) as categories_covered
                    FROM ai_training_data 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetch();
            
        } catch (Exception $e) {
            return ['total_conversations' => 0, 'avg_confidence' => 0, 'categories_covered' => 0];
        }
    }
    
    /**
     * Create training data table
     */
    public function createTrainingTable() {
        $sql = "CREATE TABLE IF NOT EXISTS ai_training_data (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_message TEXT NOT NULL,
            bot_response TEXT NOT NULL,
            keywords TEXT,
            category VARCHAR(50),
            confidence_score DECIMAL(3,2) DEFAULT 0.5,
            context JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_category (category),
            INDEX idx_confidence (confidence_score),
            INDEX idx_created_at (created_at)
        )";
        
        $this->conn->exec($sql);
        return true;
    }
}
?>
