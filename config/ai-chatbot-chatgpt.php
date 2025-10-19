<?php
/**
 * AI Chatbot with ChatGPT Integration
 * Linh2Store - Chatbot tích hợp ChatGPT/DeepSeek
 */

class AIChatbotChatGPT {
    private $db;
    private $apiKey;
    private $apiUrl;
    
    public function __construct() {
        $this->db = new Database();
        require_once __DIR__ . '/chatgpt-config.php';
        $this->apiKey = CHATGPT_API_KEY;
        $this->apiUrl = CHATGPT_API_URL;
    }
    
    /**
     * Xử lý tin nhắn với ChatGPT
     */
    public function processMessage($message, $conversationId = null, $userId = null) {
        // Lấy lịch sử cuộc trò chuyện
        $conversationHistory = $this->getConversationHistory($conversationId);
        
        // Tạo prompt hoàn chỉnh
        $systemPrompt = $this->createSystemPrompt();
        $conversationContext = $this->createConversationContext($conversationHistory);
        
        // Gọi ChatGPT API
        $response = $this->callChatGPTAPI($systemPrompt, $conversationContext, $message);
        
        // Lưu cuộc trò chuyện
        $this->saveConversation($conversationId, $userId, $message, $response);
        
        return $response;
    }
    
    /**
     * Tạo system prompt hoàn chỉnh
     */
    private function createSystemPrompt() {
        return SYSTEM_PROMPT;
    }
    
    /**
     * Tạo ngữ cảnh cuộc trò chuyện
     */
    private function createConversationContext($conversationHistory) {
        $context = [];
        
        foreach ($conversationHistory as $msg) {
            $context[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'assistant',
                'content' => $msg['message']
            ];
        }
        
        return $context;
    }
    
    /**
     * Gọi ChatGPT API
     */
    private function callChatGPTAPI($systemPrompt, $conversationContext, $userMessage) {
        $messages = [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ]
        ];
        
        // Thêm lịch sử cuộc trò chuyện
        foreach ($conversationContext as $msg) {
            $messages[] = $msg;
        }
        
        // Thêm tin nhắn hiện tại
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];
        
        $data = [
            'model' => CHATGPT_MODEL,
            'messages' => $messages,
            'max_tokens' => MAX_TOKENS,
            'temperature' => TEMPERATURE
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('ChatGPT API error: ' . $httpCode);
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            throw new Exception('ChatGPT API error: ' . $result['error']['message']);
        }
        
        return $result['choices'][0]['message']['content'];
    }
    
    /**
     * Lấy lịch sử cuộc trò chuyện
     */
    private function getConversationHistory($conversationId) {
        if (!$conversationId) return [];
        
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("
                SELECT message, role 
                FROM ai_chatbot_conversations 
                WHERE conversation_id = ? 
                ORDER BY created_at ASC
            ");
            $stmt->execute([$conversationId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lưu cuộc trò chuyện
     */
    private function saveConversation($conversationId, $userId, $userMessage, $botResponse) {
        try {
            $conn = $this->db->getConnection();
            
            // Lưu tin nhắn người dùng
            $stmt = $conn->prepare("
                INSERT INTO ai_chatbot_conversations (conversation_id, user_id, message, role, created_at) 
                VALUES (?, ?, ?, 'user', NOW())
            ");
            $stmt->execute([$conversationId, $userId, $userMessage]);
            
            // Lưu phản hồi bot
            $stmt = $conn->prepare("
                INSERT INTO ai_chatbot_conversations (conversation_id, user_id, message, role, created_at) 
                VALUES (?, ?, ?, 'assistant', NOW())
            ");
            $stmt->execute([$conversationId, $userId, $botResponse]);
            
        } catch (Exception $e) {
            // Log error but don't break the flow
        }
    }
}
?>
