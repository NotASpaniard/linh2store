<?php
/**
 * AI Chatbot Fallback
 * Linh2Store - Chatbot với logic thông minh không cần API
 */

class AIChatbotFallback {
    private $db;
    private $conversationState = [];
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Xử lý tin nhắn với logic thông minh
     */
    public function processMessage($message, $conversationId = null, $userId = null) {
        // Lấy lịch sử cuộc trò chuyện
        $conversationHistory = $this->getConversationHistory($conversationId);
        
        // Phân tích ngữ cảnh và cảm xúc
        $context = $this->analyzeContext($message, $conversationHistory);
        
        // Tạo phản hồi thông minh
        $response = $this->generateIntelligentResponse($context, $message, $conversationHistory);
        
        // Lưu cuộc trò chuyện
        $this->saveConversation($conversationId, $userId, $message, $response);
        
        return $response;
    }
    
    /**
     * Phân tích ngữ cảnh thông minh
     */
    private function analyzeContext($message, $conversationHistory) {
        $message = strtolower(trim($message));
        
        // Kiểm tra cảm xúc
        $emotion = $this->detectEmotion($message);
        $intent = $this->detectIntent($message);
        
        // Nếu là cuộc trò chuyện mới
        if (empty($conversationHistory)) {
            if ($this->isGreeting($message)) {
                return [
                    'type' => 'new_conversation',
                    'stage' => 'greeting',
                    'emotion' => $emotion,
                    'intent' => $intent
                ];
            }
        }
        
        // Kiểm tra xem có đang trong quy trình tư vấn không
        $consultationStage = $this->getCurrentConsultationStage($conversationHistory);
        
        if ($consultationStage) {
            return [
                'type' => 'consultation_continue',
                'stage' => $consultationStage,
                'emotion' => $emotion,
                'intent' => $intent
            ];
        }
        
        // Xử lý các tình huống khác
        if ($emotion === 'complaint' || $emotion === 'concern') {
            return [
                'type' => 'emotion_handling',
                'stage' => 'address_concern',
                'emotion' => $emotion,
                'intent' => $intent
            ];
        }
        
        if ($intent === 'product_inquiry') {
            return [
                'type' => 'consultation_start',
                'stage' => 'consultation_start',
                'emotion' => $emotion,
                'intent' => $intent
            ];
        }
        
        if ($intent === 'order_inquiry') {
            return [
                'type' => 'order_inquiry',
                'stage' => 'order_lookup',
                'emotion' => $emotion,
                'intent' => $intent
            ];
        }
        
        return [
            'type' => 'general_inquiry',
            'stage' => 'general_response',
            'emotion' => $emotion,
            'intent' => $intent
        ];
    }
    
    /**
     * Nhận diện cảm xúc thông minh
     */
    private function detectEmotion($message) {
        // Phàn nàn về giá
        if (strpos($message, 'mắc') !== false || 
            strpos($message, 'đắt') !== false || 
            strpos($message, 'thế') !== false) {
            return 'complaint';
        }
        
        // Thắc mắc
        if (strpos($message, 'tại sao') !== false || 
            strpos($message, 'sao') !== false || 
            strpos($message, 'như thế nào') !== false) {
            return 'curiosity';
        }
        
        // Hào hứng
        if (strpos($message, 'tuyệt') !== false || 
            strpos($message, 'đẹp') !== false || 
            strpos($message, 'thích') !== false) {
            return 'excitement';
        }
        
        return 'neutral';
    }
    
    /**
     * Nhận diện ý định
     */
    private function detectIntent($message) {
        if (strpos($message, 'son') !== false || 
            strpos($message, 'môi') !== false || 
            strpos($message, 'lipstick') !== false) {
            return 'product_inquiry';
        }
        
        if (strpos($message, 'đơn hàng') !== false || 
            strpos($message, 'tra cứu') !== false || 
            strpos($message, 'kiểm tra') !== false) {
            return 'order_inquiry';
        }
        
        if (strpos($message, 'giá') !== false || 
            strpos($message, 'ship') !== false || 
            strpos($message, 'khuyến mãi') !== false) {
            return 'general_inquiry';
        }
        
        return 'general_inquiry';
    }
    
    /**
     * Lấy giai đoạn tư vấn hiện tại
     */
    private function getCurrentConsultationStage($conversationHistory) {
        if (empty($conversationHistory)) return null;
        
        $hasConsultationStart = false;
        $hasOccasionInfo = false;
        $hasSkinToneInfo = false;
        $hasColorPreference = false;
        $hasTexturePreference = false;
        
        foreach ($conversationHistory as $msg) {
            if (strpos($msg['message'], 'tư vấn') !== false || 
                strpos($msg['message'], 'dịp') !== false) {
                $hasConsultationStart = true;
            }
            
            if (strpos($msg['message'], 'đặc biệt') !== false || 
                strpos($msg['message'], 'hàng ngày') !== false) {
                $hasOccasionInfo = true;
            }
            
            if (strpos($msg['message'], 'tone da') !== false || 
                strpos($msg['message'], 'trắng') !== false || 
                strpos($msg['message'], 'ngăm') !== false) {
                $hasSkinToneInfo = true;
            }
            
            if (strpos($msg['message'], 'màu') !== false || 
                strpos($msg['message'], 'đỏ') !== false || 
                strpos($msg['message'], 'hồng') !== false) {
                $hasColorPreference = true;
            }
            
            if (strpos($msg['message'], 'chất') !== false || 
                strpos($msg['message'], 'lì') !== false || 
                strpos($msg['message'], 'bóng') !== false) {
                $hasTexturePreference = true;
            }
        }
        
        if ($hasConsultationStart && !$hasOccasionInfo) {
            return 'occasion_inquiry';
        } elseif ($hasOccasionInfo && !$hasSkinToneInfo) {
            return 'skin_tone_analysis';
        } elseif ($hasSkinToneInfo && !$hasColorPreference) {
            return 'color_preference';
        } elseif ($hasColorPreference && !$hasTexturePreference) {
            return 'texture_preference';
        } elseif ($hasTexturePreference) {
            return 'product_recommendation';
        }
        
        return null;
    }
    
    /**
     * Tạo phản hồi thông minh
     */
    private function generateIntelligentResponse($context, $message, $conversationHistory) {
        switch ($context['type']) {
            case 'new_conversation':
                return $this->handleNewConversation($message, $context['emotion']);
                
            case 'consultation_continue':
                return $this->handleConsultationContinue($context['stage'], $message, $context['emotion']);
                
            case 'emotion_handling':
                return $this->handleEmotion($message, $context['emotion']);
                
            case 'consultation_start':
                return $this->handleConsultationStart($message, $context['emotion']);
                
            case 'order_inquiry':
                return $this->handleOrderInquiry($message, $context['emotion']);
                
            case 'general_inquiry':
                return $this->handleGeneralInquiry($message, $context['emotion']);
                
            default:
                return "Xin lỗi, mình chưa hiểu rõ. Bạn có thể nói rõ hơn được không ạ? 😊";
        }
    }
    
    /**
     * Xử lý cuộc trò chuyện mới
     */
    private function handleNewConversation($message, $emotion) {
        if ($this->isGreeting($message)) {
            return "Xin chào! Chào mừng bạn đến với Linh2Store 💖\n\nMình là Linh, trợ lý ảo của shop. Mình có thể giúp gì cho bạn hôm nay?\n\n• Tư vấn son môi phù hợp\n• Tra cứu đơn hàng\n• Hỗ trợ mua sắm";
        }
        return "Xin chào! Mình là Linh, trợ lý ảo của Linh2Store. Mình có thể giúp gì cho bạn hôm nay? 😊";
    }
    
    /**
     * Xử lý tiếp tục tư vấn
     */
    private function handleConsultationContinue($stage, $message, $emotion) {
        switch ($stage) {
            case 'occasion_inquiry':
                return "Oki, cho những dịp đặc biệt thì nên chọn màu son nổi bật một chút! 🥰 Vậy bạn có thể cho mình biết tone da của bạn không ạ? (vd: trắng, trắng hồng, ngăm, ...)";
                
            case 'skin_tone_analysis':
                return "Tuyệt vời! Với tone da " . $this->extractSkinTone($message) . ", bạn thích màu son như thế nào ạ?\n\n• Đỏ quyến rũ\n• Hồng ngọt ngào\n• Cam tươi trẻ\n• Nâu đất cá tính";
                
            case 'color_preference':
                return "Màu " . $this->extractColorPreference($message) . " rất đẹp! Bạn thích chất son như thế nào ạ?\n\n• Son Lì (Matte) - bền màu, không bóng\n• Son có độ bóng (Glossy) - bóng đẹp, quyến rũ\n• Son dưỡng ẩm (Creamy) - mịn màng, dưỡng da";
                
            case 'texture_preference':
                return $this->generateProductRecommendation($conversationHistory);
                
            case 'product_recommendation':
                return "Bạn có muốn xem thêm sản phẩm khác không ạ? Hoặc mình có thể tư vấn thêm về cách sử dụng son môi! 💄";
                
            default:
                return "Mình đang tư vấn son môi cho bạn. Bạn có thể cho mình biết thêm thông tin không ạ? 😊";
        }
    }
    
    /**
     * Xử lý cảm xúc
     */
    private function handleEmotion($message, $emotion) {
        if ($emotion === 'complaint') {
            return "Mình hiểu cảm giác của bạn mà 😅\n\nSon của shop cam kết chính hãng, thành phần an toàn và lên màu cực chuẩn nên giá thành sẽ tương xứng với chất lượng ạ. Mình tin là khi dùng rồi, bạn sẽ thấy rất đáng tiền! 💖\n\nĐể mình tư vấn giúp bạn một vài màu son 'xứng đáng' với số tiền bỏ ra nhất nhé? Bạn thường thích dùng son cho dịp gì ạ?";
        }
        
        if ($emotion === 'curiosity') {
            return "Mình rất vui khi bạn quan tâm! 😊 Bạn muốn biết thêm về điều gì ạ? Mình có thể tư vấn son môi phù hợp cho bạn!";
        }
        
        if ($emotion === 'excitement') {
            return "Mình cũng rất hào hứng khi tư vấn cho bạn! 🥰 Bạn có muốn mình gợi ý thêm sản phẩm khác không ạ?";
        }
        
        return "Mình hiểu bạn đang quan tâm. Mình có thể giúp gì cho bạn ạ? 😊";
    }
    
    /**
     * Xử lý bắt đầu tư vấn
     */
    private function handleConsultationStart($message, $emotion) {
        return "Tuyệt vời! Mình sẽ tư vấn son môi phù hợp cho bạn 💄\n\nBạn đang tìm kiếm một màu son cho dịp nào đặc biệt, hay chỉ để sử dụng hàng ngày thôi ạ?";
    }
    
    /**
     * Xử lý hỏi về đơn hàng
     */
    private function handleOrderInquiry($message, $emotion) {
        return "Để tra cứu đơn hàng, bạn cần cung cấp:\n\n• Mã đơn hàng (nếu có)\n• Số điện thoại đặt hàng\n• Email đặt hàng\n\nBạn có thông tin nào trong số này không ạ? 📦";
    }
    
    /**
     * Xử lý câu hỏi chung
     */
    private function handleGeneralInquiry($message, $emotion) {
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
        $skinTone = $this->extractSkinToneFromHistory($conversationHistory);
        $colorPreference = $this->extractColorPreferenceFromHistory($conversationHistory);
        $texturePreference = $this->extractTexturePreferenceFromHistory($conversationHistory);
        
        $recommendations = [];
        $recommendations[] = "Dựa trên thông tin của bạn, mình gợi ý:";
        $recommendations[] = "• MAC Ruby Woo - màu đỏ quyến rũ, chất son lì bền màu";
        $recommendations[] = "• MAC Velvet Teddy - màu nâu đất cá tính, chất son lì sang trọng";
        $recommendations[] = "• MAC Chili - màu cam đất tươi trẻ, chất son lì năng động";
        $recommendations[] = "\nBạn có muốn xem thêm sản phẩm khác không ạ? 💄";
        
        return implode("\n", $recommendations);
    }
    
    /**
     * Trích xuất thông tin từ cuộc trò chuyện
     */
    private function extractSkinTone($message) {
        if (strpos($message, 'trắng') !== false) return 'trắng';
        if (strpos($message, 'trung tính') !== false) return 'trung tính';
        if (strpos($message, 'ngăm') !== false) return 'ngăm';
        return 'trắng';
    }
    
    private function extractColorPreference($message) {
        if (strpos($message, 'đỏ') !== false) return 'đỏ';
        if (strpos($message, 'hồng') !== false) return 'hồng';
        if (strpos($message, 'cam') !== false) return 'cam';
        if (strpos($message, 'nâu') !== false) return 'nâu';
        return 'đỏ';
    }
    
    private function extractSkinToneFromHistory($conversationHistory) {
        foreach ($conversationHistory as $msg) {
            if (strpos($msg['message'], 'trắng') !== false) return 'trắng';
            if (strpos($msg['message'], 'trung tính') !== false) return 'trung tính';
            if (strpos($msg['message'], 'ngăm') !== false) return 'ngăm';
        }
        return 'trắng';
    }
    
    private function extractColorPreferenceFromHistory($conversationHistory) {
        foreach ($conversationHistory as $msg) {
            if (strpos($msg['message'], 'đỏ') !== false) return 'đỏ';
            if (strpos($msg['message'], 'hồng') !== false) return 'hồng';
            if (strpos($msg['message'], 'cam') !== false) return 'cam';
            if (strpos($msg['message'], 'nâu') !== false) return 'nâu';
        }
        return 'đỏ';
    }
    
    private function extractTexturePreferenceFromHistory($conversationHistory) {
        foreach ($conversationHistory as $msg) {
            if (strpos($msg['message'], 'lì') !== false) return 'lì';
            if (strpos($msg['message'], 'bóng') !== false) return 'bóng';
            if (strpos($msg['message'], 'dưỡng') !== false) return 'dưỡng';
        }
        return 'lì';
    }
    
    /**
     * Kiểm tra lời chào
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
