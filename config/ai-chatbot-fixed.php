<?php
/**
 * AI Chatbot Fixed - Tư duy đúng
 * Linh2Store - Chatbot với tư duy mạch lạc
 */

class AIChatbotFixed {
    private $db;
    private $conversationState = [];
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Xử lý tin nhắn với tư duy đúng
     */
    public function processMessage($message, $conversationId = null, $userId = null) {
        // Lấy lịch sử cuộc trò chuyện
        $conversationHistory = $this->getConversationHistory($conversationId);
        
        // Phân tích ngữ cảnh
        $context = $this->analyzeContext($message, $conversationHistory);
        
        // Tạo phản hồi dựa trên ngữ cảnh
        $response = $this->generateContextualResponse($context, $message, $conversationHistory);
        
        // Lưu cuộc trò chuyện
        $this->saveConversation($conversationId, $userId, $message, $response);
        
        return $response;
    }
    
    /**
     * Phân tích ngữ cảnh cuộc trò chuyện
     */
    private function analyzeContext($message, $conversationHistory) {
        $message = strtolower(trim($message));
        
        // Nếu là cuộc trò chuyện mới (chỉ có lời chào)
        if (empty($conversationHistory)) {
            if ($this->isGreeting($message)) {
                return [
                    'type' => 'new_conversation',
                    'stage' => 'greeting',
                    'needs_response' => true
                ];
            }
        }
        
        // Nếu đang trong quy trình tư vấn
        if ($this->isInConsultation($conversationHistory)) {
            return $this->analyzeConsultationContext($message, $conversationHistory);
        }
        
        // Nếu hỏi về đơn hàng
        if ($this->isOrderInquiry($message)) {
            return [
                'type' => 'order_inquiry',
                'stage' => 'order_lookup',
                'needs_response' => true
            ];
        }
        
        // Nếu hỏi về sản phẩm
        if ($this->isProductInquiry($message)) {
            return [
                'type' => 'product_inquiry',
                'stage' => 'consultation_start',
                'needs_response' => true
            ];
        }
        
        // Câu hỏi chung
        return [
            'type' => 'general_inquiry',
            'stage' => 'general_response',
            'needs_response' => true
        ];
    }
    
    /**
     * Kiểm tra có phải lời chào không
     */
    private function isGreeting($message) {
        $greetingPatterns = [
            'xin chào', 'chào', 'hello', 'hi', 'chào bạn', 'chào cô', 'chào anh', 'chào chị'
        ];
        
        foreach ($greetingPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Kiểm tra có đang trong quy trình tư vấn không
     */
    private function isInConsultation($conversationHistory) {
        if (empty($conversationHistory)) return false;
        
        foreach ($conversationHistory as $msg) {
            if (strpos($msg['message'], 'tư vấn') !== false || 
                strpos($msg['message'], 'tone da') !== false ||
                strpos($msg['message'], 'màu son') !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Kiểm tra có phải hỏi về đơn hàng không
     */
    private function isOrderInquiry($message) {
        $orderPatterns = [
            'đơn hàng', 'mã đơn', 'tra cứu', 'kiểm tra đơn', 'tình trạng đơn'
        ];
        
        foreach ($orderPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Kiểm tra có phải hỏi về sản phẩm không
     */
    private function isProductInquiry($message) {
        $productPatterns = [
            'son môi', 'son', 'lipstick', 'màu son', 'tìm son', 'mua son'
        ];
        
        foreach ($productPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Phân tích ngữ cảnh tư vấn
     */
    private function analyzeConsultationContext($message, $conversationHistory) {
        // Kiểm tra thông tin đã có
        $hasSkinTone = $this->hasSkinToneInfo($conversationHistory);
        $hasColorPreference = $this->hasColorPreference($conversationHistory);
        $hasTexturePreference = $this->hasTexturePreference($conversationHistory);
        
        if (!$hasSkinTone) {
            return [
                'type' => 'consultation',
                'stage' => 'skin_tone_analysis',
                'needs_response' => true
            ];
        } elseif (!$hasColorPreference) {
            return [
                'type' => 'consultation',
                'stage' => 'color_preference',
                'needs_response' => true
            ];
        } elseif (!$hasTexturePreference) {
            return [
                'type' => 'consultation',
                'stage' => 'texture_preference',
                'needs_response' => true
            ];
        } else {
            return [
                'type' => 'consultation',
                'stage' => 'product_recommendation',
                'needs_response' => true
            ];
        }
    }
    
    /**
     * Tạo phản hồi dựa trên ngữ cảnh
     */
    private function generateContextualResponse($context, $message, $conversationHistory) {
        switch ($context['type']) {
            case 'new_conversation':
                return $this->handleNewConversation($message);
                
            case 'consultation':
                return $this->handleConsultation($context['stage'], $message, $conversationHistory);
                
            case 'order_inquiry':
                return $this->handleOrderInquiry($message);
                
            case 'product_inquiry':
                return $this->handleProductInquiry($message);
                
            case 'general_inquiry':
                return $this->handleGeneralInquiry($message);
                
            default:
                return "Xin lỗi, mình chưa hiểu rõ. Bạn có thể nói rõ hơn được không ạ? 😊";
        }
    }
    
    /**
     * Xử lý cuộc trò chuyện mới
     */
    private function handleNewConversation($message) {
        if ($this->isGreeting($message)) {
            return "Xin chào! Chào mừng bạn đến với Linh2Store 💖\n\nMình là Linh, trợ lý ảo của shop. Mình có thể giúp gì cho bạn hôm nay?\n\n• Tư vấn son môi phù hợp\n• Tra cứu đơn hàng\n• Hỗ trợ mua sắm";
        }
        return "Xin chào! Mình là Linh, trợ lý ảo của Linh2Store. Mình có thể giúp gì cho bạn hôm nay? 😊";
    }
    
    /**
     * Xử lý tư vấn sản phẩm
     */
    private function handleConsultation($stage, $message, $conversationHistory) {
        switch ($stage) {
            case 'skin_tone_analysis':
                return "Để mình tư vấn màu son phù hợp nhất, bạn có thể cho mình biết tone da của bạn là gì không ạ?\n\n• Trắng/Trắng hồng\n• Trung tính\n• Ngăm/Ngăm đen\n\nHoặc bạn có thể miêu tả đơn giản thôi ạ! 😊";
                
            case 'color_preference':
                return "Bạn thích màu son như thế nào ạ?\n\n• Đỏ quyến rũ\n• Hồng ngọt ngào\n• Cam tươi trẻ\n• Nâu đất cá tính\n• Màu khác (bạn có thể miêu tả)";
                
            case 'texture_preference':
                return "Bạn thích chất son như thế nào ạ?\n\n• Son Lì (Matte) - bền màu, không bóng\n• Son có độ bóng (Glossy) - bóng đẹp, quyến rũ\n• Son dưỡng ẩm (Creamy) - mịn màng, dưỡng da";
                
            case 'product_recommendation':
                return $this->generateProductRecommendation($conversationHistory);
                
            default:
                return "Mình đang tư vấn son môi cho bạn. Bạn có thể cho mình biết thêm thông tin không ạ? 😊";
        }
    }
    
    /**
     * Xử lý hỏi về đơn hàng
     */
    private function handleOrderInquiry($message) {
        return "Để tra cứu đơn hàng, bạn cần cung cấp:\n\n• Mã đơn hàng (nếu có)\n• Số điện thoại đặt hàng\n• Email đặt hàng\n\nBạn có thông tin nào trong số này không ạ? 📦";
    }
    
    /**
     * Xử lý hỏi về sản phẩm
     */
    private function handleProductInquiry($message) {
        return "Tuyệt vời! Mình sẽ tư vấn son môi phù hợp cho bạn 💄\n\nBạn đang tìm kiếm một màu son cho dịp nào đặc biệt, hay chỉ để sử dụng hàng ngày thôi ạ?";
    }
    
    /**
     * Xử lý câu hỏi chung
     */
    private function handleGeneralInquiry($message) {
        $message = strtolower($message);
        
        if (strpos($message, 'giá') !== false || strpos($message, 'price') !== false) {
            return "Giá sản phẩm của chúng mình rất cạnh tranh! Bạn có thể xem giá chi tiết tại trang sản phẩm. Có sản phẩm nào bạn quan tâm không ạ? 💰";
        }
        
        if (strpos($message, 'ship') !== false || strpos($message, 'giao hàng') !== false) {
            return "Chúng mình giao hàng toàn quốc! Phí ship từ 30k-50k tùy khu vực. Miễn phí ship cho đơn từ 500k. Bạn ở khu vực nào ạ? 🚚";
        }
        
        if (strpos($message, 'khuyến mãi') !== false || strpos($message, 'sale') !== false) {
            return "Hiện tại chúng mình có nhiều chương trình khuyến mãi hấp dẫn! Bạn có thể xem tại trang chủ hoặc mình có thể tư vấn sản phẩm phù hợp với ngân sách của bạn ạ! 🎉";
        }
        
        return "Mình có thể giúp bạn:\n\n• Tư vấn son môi phù hợp\n• Tra cứu đơn hàng\n• Hỗ trợ mua sắm\n• Giải đáp thắc mắc\n\nBạn cần hỗ trợ gì ạ? 😊";
    }
    
    /**
     * Tạo gợi ý sản phẩm
     */
    private function generateProductRecommendation($conversationHistory) {
        // Lấy thông tin từ cuộc trò chuyện
        $skinTone = $this->extractSkinTone($conversationHistory);
        $colorPreference = $this->extractColorPreference($conversationHistory);
        $texturePreference = $this->extractTexturePreference($conversationHistory);
        
        $recommendations = [];
        
        // Logic gợi ý dựa trên thông tin
        if ($skinTone && $colorPreference && $texturePreference) {
            $recommendations[] = "Dựa trên thông tin của bạn, mình gợi ý:";
            $recommendations[] = "• MAC Ruby Woo - màu đỏ quyến rũ, chất son lì bền màu";
            $recommendations[] = "• MAC Velvet Teddy - màu nâu đất cá tính, chất son lì sang trọng";
            $recommendations[] = "• MAC Chili - màu cam đất tươi trẻ, chất son lì năng động";
            $recommendations[] = "\nBạn có muốn xem thêm sản phẩm khác không ạ? 💄";
        }
        
        return implode("\n", $recommendations);
    }
    
    /**
     * Kiểm tra thông tin đã có
     */
    private function hasSkinToneInfo($conversationHistory) {
        foreach ($conversationHistory as $msg) {
            if (strpos($msg['message'], 'trắng') !== false || 
                strpos($msg['message'], 'trung tính') !== false || 
                strpos($msg['message'], 'ngăm') !== false) {
                return true;
            }
        }
        return false;
    }
    
    private function hasColorPreference($conversationHistory) {
        foreach ($conversationHistory as $msg) {
            if (strpos($msg['message'], 'đỏ') !== false || 
                strpos($msg['message'], 'hồng') !== false || 
                strpos($msg['message'], 'cam') !== false || 
                strpos($msg['message'], 'nâu') !== false) {
                return true;
            }
        }
        return false;
    }
    
    private function hasTexturePreference($conversationHistory) {
        foreach ($conversationHistory as $msg) {
            if (strpos($msg['message'], 'lì') !== false || 
                strpos($msg['message'], 'bóng') !== false || 
                strpos($msg['message'], 'dưỡng') !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Trích xuất thông tin từ cuộc trò chuyện
     */
    private function extractSkinTone($conversationHistory) {
        foreach ($conversationHistory as $msg) {
            if (strpos($msg['message'], 'trắng') !== false) return 'trắng';
            if (strpos($msg['message'], 'trung tính') !== false) return 'trung tính';
            if (strpos($msg['message'], 'ngăm') !== false) return 'ngăm';
        }
        return null;
    }
    
    private function extractColorPreference($conversationHistory) {
        foreach ($conversationHistory as $msg) {
            if (strpos($msg['message'], 'đỏ') !== false) return 'đỏ';
            if (strpos($msg['message'], 'hồng') !== false) return 'hồng';
            if (strpos($msg['message'], 'cam') !== false) return 'cam';
            if (strpos($msg['message'], 'nâu') !== false) return 'nâu';
        }
        return null;
    }
    
    private function extractTexturePreference($conversationHistory) {
        foreach ($conversationHistory as $msg) {
            if (strpos($msg['message'], 'lì') !== false) return 'lì';
            if (strpos($msg['message'], 'bóng') !== false) return 'bóng';
            if (strpos($msg['message'], 'dưỡng') !== false) return 'dưỡng';
        }
        return null;
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
