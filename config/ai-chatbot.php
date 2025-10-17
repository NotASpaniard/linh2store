<?php
/**
 * AI Chatbot Engine
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/database.php';

class AIChatbot {
    private $db;
    private $conn;
    private $config;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->loadConfig();
    }
    
    /**
     * Load chatbot configuration
     */
    private function loadConfig() {
        $sql = "SELECT config_key, config_value, config_type FROM chatbot_config WHERE is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $configs = $stmt->fetchAll();
        
        $this->config = [];
        foreach ($configs as $config) {
            $value = $config['config_value'];
            
            switch ($config['config_type']) {
                case 'integer':
                    $value = (int) $value;
                    break;
                case 'float':
                    $value = (float) $value;
                    break;
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'json':
                    $value = json_decode($value, true);
                    break;
            }
            
            $this->config[$config['config_key']] = $value;
        }
    }
    
    /**
     * Get configuration value
     */
    public function getConfig($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Start a new conversation
     */
    public function startConversation($userId = null, $sessionId = null) {
        if (!$sessionId) {
            $sessionId = $this->generateSessionId();
        }
        
        $sql = "INSERT INTO chat_conversations (user_id, session_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId, $sessionId]);
        
        $conversationId = $this->conn->lastInsertId();
        
        // Send welcome message
        $this->sendMessage($conversationId, 'bot', $this->getConfig('welcome_message', 'Xin chào! Tôi có thể giúp gì cho bạn?'));
        
        return [
            'conversation_id' => $conversationId,
            'session_id' => $sessionId
        ];
    }
    
    /**
     * Send a message
     */
    public function sendMessage($conversationId, $senderType, $messageText, $messageType = 'text', $metadata = null) {
        $sql = "INSERT INTO chat_messages (conversation_id, sender_type, message_text, message_type, metadata) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$conversationId, $senderType, $messageText, $messageType, $metadata ? json_encode($metadata) : null]);
        
        // Update conversation last message time
        $this->updateConversationLastMessage($conversationId);
        
        return $this->conn->lastInsertId();
    }
    
    /**
     * Process user message and generate response
     */
    public function processMessage($conversationId, $userMessage, $userId = null) {
        // Save user message
        $this->sendMessage($conversationId, 'user', $userMessage);
        
        // Check for escalation keywords
        if ($this->shouldEscalate($userMessage)) {
            return $this->handleEscalation($conversationId);
        }
        
        // Generate AI response
        $response = $this->generateResponse($userMessage, $conversationId, $userId);
        
        // Save bot response
        $this->sendMessage($conversationId, 'bot', $response['text'], $response['type'], $response['metadata']);
        
        return $response;
    }
    
    /**
     * Generate AI response
     */
    private function generateResponse($userMessage, $conversationId, $userId) {
        // 1. Check knowledge base
        $knowledgeResponse = $this->searchKnowledgeBase($userMessage);
        if ($knowledgeResponse) {
            return [
                'text' => $knowledgeResponse['answer'],
                'type' => 'text',
                'metadata' => ['source' => 'knowledge_base', 'category' => $knowledgeResponse['category']]
            ];
        }
        
        // 2. Check for product search
        $productResponse = $this->handleProductSearch($userMessage);
        if ($productResponse) {
            return $productResponse;
        }
        
        // 3. Check for order tracking
        $orderResponse = $this->handleOrderTracking($userMessage, $userId);
        if ($orderResponse) {
            return $orderResponse;
        }
        
        // 4. Default response
        return $this->getDefaultResponse($userMessage);
    }
    
    /**
     * Search knowledge base
     */
    private function searchKnowledgeBase($query) {
        $sql = "SELECT * FROM ai_knowledge_base 
                WHERE is_active = 1 
                AND (MATCH(question, answer, keywords) AGAINST(? IN NATURAL LANGUAGE MODE)
                     OR question LIKE ? OR answer LIKE ?)
                ORDER BY priority DESC, 
                         MATCH(question, answer, keywords) AGAINST(? IN NATURAL LANGUAGE MODE) DESC
                LIMIT 1";
        
        $searchTerm = "%$query%";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$query, $searchTerm, $searchTerm, $query]);
        
        return $stmt->fetch();
    }
    
    /**
     * Handle product search
     */
    private function handleProductSearch($message) {
        if (!$this->getConfig('enable_product_search', true)) {
            return null;
        }
        
        // Extract product keywords
        $keywords = $this->extractProductKeywords($message);
        if (empty($keywords)) {
            return null;
        }
        
        // Search products
        $products = $this->searchProducts($keywords);
        if (empty($products)) {
            return [
                'text' => 'Xin lỗi, tôi không tìm thấy sản phẩm nào phù hợp với yêu cầu của bạn. Bạn có thể thử tìm kiếm với từ khóa khác.',
                'type' => 'text',
                'metadata' => ['source' => 'product_search', 'keywords' => $keywords]
            ];
        }
        
        $productList = $this->formatProductList($products);
        return [
            'text' => "Tôi tìm thấy một số sản phẩm phù hợp:\n\n$productList\n\nBạn có thể xem chi tiết sản phẩm tại trang sản phẩm.",
            'type' => 'product',
            'metadata' => ['source' => 'product_search', 'products' => $products, 'keywords' => $keywords]
        ];
    }
    
    /**
     * Handle order tracking
     */
    private function handleOrderTracking($message, $userId) {
        if (!$this->getConfig('enable_order_tracking', true) || !$userId) {
            return null;
        }
        
        // Extract order ID
        $orderId = $this->extractOrderId($message);
        if (!$orderId) {
            return null;
        }
        
        // Get order details
        $order = $this->getOrderDetails($orderId, $userId);
        if (!$order) {
            return [
                'text' => 'Xin lỗi, tôi không tìm thấy đơn hàng với mã này. Vui lòng kiểm tra lại mã đơn hàng.',
                'type' => 'text',
                'metadata' => ['source' => 'order_tracking', 'order_id' => $orderId]
            ];
        }
        
        $orderInfo = $this->formatOrderInfo($order);
        return [
            'text' => "Thông tin đơn hàng của bạn:\n\n$orderInfo",
            'type' => 'order',
            'metadata' => ['source' => 'order_tracking', 'order' => $order]
        ];
    }
    
    /**
     * Get default response
     */
    private function getDefaultResponse($message) {
        $defaultResponses = [
            'Xin lỗi, tôi chưa hiểu rõ câu hỏi của bạn. Bạn có thể hỏi về sản phẩm, đơn hàng, hoặc chính sách của chúng tôi.',
            'Tôi có thể giúp bạn tìm sản phẩm, kiểm tra đơn hàng, hoặc trả lời câu hỏi. Bạn cần hỗ trợ gì?',
            'Bạn có thể hỏi tôi về sản phẩm, thương hiệu, đơn hàng, hoặc bất kỳ thông tin nào khác.',
            'Tôi là trợ lý ảo của Linh2Store. Hãy cho tôi biết bạn cần hỗ trợ gì nhé!'
        ];
        
        $response = $defaultResponses[array_rand($defaultResponses)];
        return [
            'text' => $response,
            'type' => 'text',
            'metadata' => ['source' => 'default']
        ];
    }
    
    /**
     * Check if should escalate to human
     */
    private function shouldEscalate($message) {
        if (!$this->getConfig('enable_escalation', true)) {
            return false;
        }
        
        $escalationKeywords = $this->getConfig('escalation_keywords', []);
        $message = strtolower($message);
        
        foreach ($escalationKeywords as $keyword) {
            if (strpos($message, strtolower($keyword)) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Handle escalation
     */
    private function handleEscalation($conversationId) {
        $response = "Tôi hiểu bạn cần hỗ trợ từ nhân viên. Tôi sẽ chuyển tiếp cuộc trò chuyện này cho đội ngũ hỗ trợ khách hàng. Họ sẽ liên hệ với bạn sớm nhất có thể.";
        
        $this->sendMessage($conversationId, 'bot', $response);
        
        // Mark conversation for escalation
        $this->markForEscalation($conversationId);
        
        return [
            'text' => $response,
            'type' => 'text',
            'metadata' => ['source' => 'escalation']
        ];
    }
    
    /**
     * Extract product keywords
     */
    private function extractProductKeywords($message) {
        $keywords = [];
        $message = strtolower($message);
        
        // Common product keywords
        $productTerms = ['son', 'môi', 'mỹ phẩm', 'kem', 'phấn', 'mascara', 'eyeliner', 'foundation', 'concealer'];
        
        foreach ($productTerms as $term) {
            if (strpos($message, $term) !== false) {
                $keywords[] = $term;
            }
        }
        
        return $keywords;
    }
    
    /**
     * Search products
     */
    private function searchProducts($keywords) {
        $keywordStr = implode(' ', $keywords);
        $sql = "SELECT p.*, b.name as brand_name 
                FROM products p 
                LEFT JOIN brands b ON p.brand_id = b.id 
                WHERE p.status = 'active' 
                AND (p.name LIKE ? OR p.description LIKE ? OR b.name LIKE ?)
                ORDER BY p.name LIKE ? DESC, p.name LIKE ? DESC
                LIMIT 5";
        
        $searchTerm = "%$keywordStr%";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Format product list
     */
    private function formatProductList($products) {
        $list = '';
        foreach ($products as $product) {
            $list .= "• {$product['name']} - " . number_format($product['price']) . "đ\n";
        }
        return $list;
    }
    
    /**
     * Extract order ID from message
     */
    private function extractOrderId($message) {
        // Look for order ID pattern (e.g., "DH123456", "ORDER-123", "123456")
        if (preg_match('/(?:DH|ORDER-|#)?(\d{6,})/i', $message, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * Get order details
     */
    private function getOrderDetails($orderId, $userId) {
        $sql = "SELECT o.*, os.name as status_name 
                FROM orders o 
                LEFT JOIN order_statuses os ON o.status = os.id 
                WHERE o.id = ? AND o.user_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId, $userId]);
        return $stmt->fetch();
    }
    
    /**
     * Format order information
     */
    private function formatOrderInfo($order) {
        return "Mã đơn hàng: {$order['id']}\n" .
               "Trạng thái: {$order['status_name']}\n" .
               "Ngày đặt: " . date('d/m/Y H:i', strtotime($order['created_at'])) . "\n" .
               "Tổng tiền: " . number_format($order['total_amount']) . "đ";
    }
    
    /**
     * Get conversation history
     */
    public function getConversationHistory($conversationId, $limit = 20) {
        $sql = "SELECT * FROM chat_messages 
                WHERE conversation_id = ? 
                ORDER BY created_at ASC 
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$conversationId, $limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get active conversations
     */
    public function getActiveConversations($userId = null) {
        $sql = "SELECT c.*, COUNT(m.id) as message_count 
                FROM chat_conversations c 
                LEFT JOIN chat_messages m ON c.id = m.conversation_id 
                WHERE c.status = 'active'";
        
        $params = [];
        if ($userId) {
            $sql .= " AND c.user_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " GROUP BY c.id ORDER BY c.last_message_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Close conversation
     */
    public function closeConversation($conversationId) {
        $sql = "UPDATE chat_conversations 
                SET status = 'closed', closed_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$conversationId]);
    }
    
    /**
     * Add feedback
     */
    public function addFeedback($conversationId, $messageId, $userId, $feedbackType, $feedbackText = null) {
        $sql = "INSERT INTO chat_feedback (conversation_id, message_id, user_id, feedback_type, feedback_text) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$conversationId, $messageId, $userId, $feedbackType, $feedbackText]);
    }
    
    /**
     * Update conversation last message time
     */
    private function updateConversationLastMessage($conversationId) {
        $sql = "UPDATE chat_conversations SET last_message_at = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$conversationId]);
    }
    
    /**
     * Mark conversation for escalation
     */
    private function markForEscalation($conversationId) {
        // This could be implemented with a separate table or field
        // For now, we'll just log it
        error_log("Conversation $conversationId marked for escalation");
    }
    
    /**
     * Generate session ID
     */
    private function generateSessionId() {
        return 'chat_' . uniqid() . '_' . time();
    }
    
    /**
     * Get chatbot statistics
     */
    public function getChatbotStats() {
        $sql = "SELECT 
                    COUNT(DISTINCT c.id) as total_conversations,
                    COUNT(DISTINCT c.user_id) as unique_users,
                    COUNT(m.id) as total_messages,
                    AVG(CASE WHEN m.sender_type = 'bot' THEN 1 ELSE 0 END) as bot_response_rate
                FROM chat_conversations c 
                LEFT JOIN chat_messages m ON c.id = m.conversation_id 
                WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
}
?>
