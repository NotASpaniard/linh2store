<?php
/**
 * AI Chatbot Buttons
 * Linh2Store - Chatbot với flow cứng và buttons
 */

require_once __DIR__ . '/database.php';

class AIChatbotButtons {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Xử lý tin nhắn
     */
    public function processMessage($userMessage, $conversationId) {
        try {
            // Lưu tin nhắn user
            $this->saveMessage($conversationId, $userMessage, 'user');
            
            // Lấy lịch sử hội thoại
            $conversationHistory = $this->getConversationHistory($conversationId);
            
            // Phân tích ngữ cảnh với flow cứng
            $context = $this->analyzeButtonContext($userMessage, $conversationHistory);
            
            // Tạo phản hồi với buttons
            $response = $this->generateButtonResponse($context, $userMessage, $conversationHistory);
            
            // Lưu phản hồi
            $this->saveMessage($conversationId, $response, 'assistant');
            
            return $response;
            
        } catch (Exception $e) {
            return "Xin lỗi, có lỗi xảy ra. Vui lòng thử lại.";
        }
    }
    
    /**
     * Phân tích ngữ cảnh với flow cứng
     */
    private function analyzeButtonContext($message, $conversationHistory) {
        $message = strtolower(trim($message));
        
        // Kiểm tra xem có đang trong quy trình tư vấn không
        $consultationStage = $this->getCurrentConsultationStage($conversationHistory);
        
        if ($consultationStage) {
            return [
                'type' => 'consultation_continue',
                'stage' => $consultationStage
            ];
        }
        
        // Kiểm tra cảm xúc
        if (strpos($message, 'mắc') !== false || strpos($message, 'đắt') !== false) {
            return [
                'type' => 'emotion_handling',
                'stage' => 'address_concern'
            ];
        }
        
        // Kiểm tra intent
        if (strpos($message, 'son') !== false || strpos($message, 'môi') !== false) {
            return [
                'type' => 'consultation_start',
                'stage' => 'consultation_start'
            ];
        }
        
        if (strpos($message, 'đơn hàng') !== false || strpos($message, 'tra cứu') !== false) {
            return [
                'type' => 'order_inquiry',
                'stage' => 'order_lookup'
            ];
        }
        
        // Kiểm tra xem có phải là cuộc trò chuyện mới không
        if (empty($conversationHistory)) {
            return [
                'type' => 'new_conversation',
                'stage' => 'greeting'
            ];
        }
        
        return [
            'type' => 'general_inquiry',
            'stage' => 'general_response'
        ];
    }
    
    /**
     * Tạo phản hồi với buttons
     */
    private function generateButtonResponse($context, $message, $conversationHistory) {
        switch ($context['type']) {
            case 'consultation_continue':
                return $this->handleConsultationContinue($context['stage'], $message);
                
            case 'emotion_handling':
                return "Mình hiểu cảm giác của bạn mà 😅\n\nSon của shop cam kết chính hãng, thành phần an toàn và lên màu cực chuẩn nên giá thành sẽ tương xứng với chất lượng ạ. Mình tin là khi dùng rồi, bạn sẽ thấy rất đáng tiền! 💖\n\nĐể mình tư vấn giúp bạn một vài màu son 'xứng đáng' với số tiền bỏ ra nhất nhé? Bạn thường thích dùng son cho dịp gì ạ?";
                
            case 'consultation_start':
                return "Tuyệt vời! Mình sẽ tư vấn son môi phù hợp cho bạn 💄\n\nBạn đang tìm kiếm một màu son cho dịp nào đặc biệt, hay chỉ để sử dụng hàng ngày thôi ạ?";
                
            case 'order_inquiry':
                return "Để tra cứu đơn hàng, bạn cần cung cấp:\n\n• Mã đơn hàng (nếu có)\n• Số điện thoại đặt hàng\n• Email đặt hàng\n\nBạn có thông tin nào trong số này không ạ? 📦";
                
            case 'new_conversation':
                return "Xin chào! Chào mừng bạn đến với Linh2Store 💖\n\nMình là Linh, trợ lý ảo của shop. Mình có thể giúp gì cho bạn hôm nay?\n\n• Tư vấn son môi phù hợp\n• Tra cứu đơn hàng\n• Hỗ trợ mua sắm";
                
            default:
                return "Mình có thể giúp bạn:\n\n• Tư vấn son môi phù hợp\n• Tra cứu đơn hàng\n• Hỗ trợ mua sắm\n• Giải đáp thắc mắc\n\nBạn cần hỗ trợ gì ạ? 😊";
        }
    }
    
    /**
     * Xử lý tiếp tục tư vấn với flow cứng
     */
    private function handleConsultationContinue($stage, $message) {
        switch ($stage) {
            case 'occasion_inquiry':
                return "Oki, cho những dịp đặc biệt thì nên chọn màu son nổi bật một chút! 🥰 Vậy bạn có thể cho mình biết tone da của bạn không ạ? (vd: trắng, trắng hồng, ngăm, ...)";
                
            case 'skin_tone_analysis':
                return "Tuyệt vời! Với tone da " . $this->extractSkinTone($message) . ", bạn thích màu son như thế nào ạ?\n\n• Đỏ quyến rũ\n• Hồng ngọt ngào\n• Cam tươi trẻ\n• Nâu đất cá tính";
                
            case 'color_preference':
                return "Màu " . $this->extractColorPreference($message) . " rất đẹp! Bạn thích chất son như thế nào ạ?\n\n• Son Lì (Matte) - bền màu, không bóng\n• Son có độ bóng (Glossy) - bóng đẹp, quyến rũ\n• Son dưỡng ẩm (Creamy) - mịn màng, dưỡng da";
                
            case 'texture_preference':
                return $this->generateProductRecommendation();
                
            default:
                return "Mình đang tư vấn son môi cho bạn. Bạn có thể cho mình biết thêm thông tin không ạ? 😊";
        }
    }
    
    /**
     * Lấy giai đoạn tư vấn hiện tại với flow cứng
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
            
            // Kiểm tra thông tin dịp sử dụng - MỞ RỘNG
            if (strpos($msg['message'], 'đặc biệt') !== false || 
                strpos($msg['message'], 'hàng ngày') !== false ||
                strpos($msg['message'], '20/10') !== false ||
                strpos($msg['message'], 'dịp') !== false ||
                strpos($msg['message'], 'lễ') !== false ||
                strpos($msg['message'], 'tiệc') !== false ||
                strpos($msg['message'], 'đi chơi') !== false ||
                strpos($msg['message'], 'đi làm') !== false) {
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
     * Trích xuất thông tin
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
    
    /**
     * Tạo gợi ý sản phẩm
     */
    private function generateProductRecommendation() {
        $recommendations = [];
        $recommendations[] = "Dựa trên thông tin của bạn, mình gợi ý:";
        $recommendations[] = "• MAC Ruby Woo - màu đỏ quyến rũ, chất son lì bền màu";
        $recommendations[] = "• MAC Velvet Teddy - màu nâu đất cá tính, chất son lì sang trọng";
        $recommendations[] = "• MAC Chili - màu cam đất tươi trẻ, chất son lì năng động";
        $recommendations[] = "\nBạn có muốn xem thêm sản phẩm khác không ạ? 💄";
        
        return implode("\n", $recommendations);
    }
    
    /**
     * Lấy lịch sử cuộc trò chuyện
     */
    private function getConversationHistory($conversationId) {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("
                SELECT message, role, created_at 
                FROM ai_chatbot_conversations 
                WHERE conversation_id = ? 
                ORDER BY created_at ASC
            ");
            $stmt->execute([$conversationId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Lưu tin nhắn
     */
    private function saveMessage($conversationId, $message, $role) {
        try {
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("
                INSERT INTO ai_chatbot_conversations (conversation_id, message, role, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$conversationId, $message, $role]);
        } catch (Exception $e) {
            // Ignore database errors
        }
    }
}
?>
