<?php
/**
 * AI Beauty Advisor
 * Linh2Store - Professional Beauty Consultation AI
 */

class AIBeautyAdvisor {
    
    /**
     * Analyze customer consultation needs
     */
    public function analyzeConsultation($message, $conversationHistory = []) {
        $message = strtolower(trim($message));
        
        // Consultation stages
        $stages = [
            'greeting' => $this->isGreeting($message),
            'needs_assessment' => $this->needsAssessment($message),
            'skin_tone_analysis' => $this->skinToneAnalysis($message),
            'color_preference' => $this->colorPreference($message),
            'texture_preference' => $this->texturePreference($message),
            'budget_inquiry' => $this->budgetInquiry($message),
            'product_recommendation' => $this->productRecommendation($message),
            'objection_handling' => $this->objectHandling($message),
            'closing' => $this->closing($message)
        ];
        
        // Determine current stage
        $currentStage = $this->determineCurrentStage($stages, $conversationHistory);
        
        return [
            'stage' => $currentStage,
            'stages' => $stages,
            'needs_follow_up' => $this->needsFollowUp($currentStage, $conversationHistory)
        ];
    }
    
    /**
     * Generate professional consultation response
     */
    public function generateConsultationResponse($stage, $message, $conversationHistory = []) {
        switch ($stage) {
            case 'greeting':
                return $this->generateGreetingResponse();
                
            case 'needs_assessment':
                return $this->generateNeedsAssessmentResponse($message);
                
            case 'skin_tone_analysis':
                return $this->generateSkinToneResponse($message);
                
            case 'color_preference':
                return $this->generateColorPreferenceResponse($message);
                
            case 'texture_preference':
                return $this->generateTexturePreferenceResponse($message);
                
            case 'budget_inquiry':
                return $this->generateBudgetResponse($message);
                
            case 'product_recommendation':
                return $this->generateProductRecommendation($message, $conversationHistory);
                
            case 'objection_handling':
                return $this->generateObjectionHandlingResponse($message);
                
            case 'closing':
                return $this->generateClosingResponse($message);
                
            default:
                return $this->generateDefaultResponse($message);
        }
    }
    
    /**
     * Check if greeting
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
     * Check if needs assessment
     */
    private function needsAssessment($message) {
        $needsPatterns = [
            'tìm son', 'mua son', 'cần son', 'son môi', 'lipstick', 'màu son', 'son đẹp'
        ];
        
        foreach ($needsPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if skin tone analysis
     */
    private function skinToneAnalysis($message) {
        $skinTonePatterns = [
            'tone da', 'da sáng', 'da tối', 'da trung bình', 'da ấm', 'da lạnh', 'màu da'
        ];
        
        foreach ($skinTonePatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if color preference
     */
    private function colorPreference($message) {
        $colorPatterns = [
            'màu đỏ', 'màu hồng', 'màu nude', 'màu cam', 'màu tím', 'màu nâu', 'tông màu'
        ];
        
        foreach ($colorPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if texture preference
     */
    private function texturePreference($message) {
        $texturePatterns = [
            'son lì', 'son bóng', 'son dưỡng', 'matte', 'glossy', 'cream', 'chất son'
        ];
        
        foreach ($texturePatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if budget inquiry
     */
    private function budgetInquiry($message) {
        $budgetPatterns = [
            'giá', 'giá bao nhiêu', 'rẻ', 'đắt', 'ngân sách', 'budget', 'tiền'
        ];
        
        foreach ($budgetPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if product recommendation
     */
    private function productRecommendation($message) {
        $productPatterns = [
            'gợi ý', 'đề xuất', 'khuyên', 'nên mua', 'phù hợp', 'tốt nhất'
        ];
        
        foreach ($productPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if objection handling
     */
    private function objectHandling($message) {
        $objectionPatterns = [
            'đắt quá', 'không phù hợp', 'không thích', 'không chắc', 'suy nghĩ', 'do dự'
        ];
        
        foreach ($objectionPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if closing
     */
    private function closing($message) {
        $closingPatterns = [
            'cảm ơn', 'tạm biệt', 'bye', 'hẹn gặp lại', 'kết thúc'
        ];
        
        foreach ($closingPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Determine current consultation stage
     */
    private function determineCurrentStage($stages, $conversationHistory) {
        // If no conversation history, start with greeting
        if (empty($conversationHistory)) {
            return 'greeting';
        }
        
        // Extract customer information from conversation history
        $customerInfo = $this->extractCustomerInfo($conversationHistory);
        
        // Check if we have enough information for recommendations
        $hasBasicInfo = !empty($customerInfo['skin_tone']) && 
                       !empty($customerInfo['color_preference']) && 
                       !empty($customerInfo['texture_preference']);
        
        if ($hasBasicInfo) {
            // We have enough info, provide recommendations
            return 'product_recommendation';
        }
        
        // Determine next stage based on what information is missing
        if (empty($customerInfo['skin_tone'])) {
            return 'skin_tone_analysis';
        } elseif (empty($customerInfo['color_preference'])) {
            return 'color_preference';
        } elseif (empty($customerInfo['texture_preference'])) {
            return 'texture_preference';
        } elseif (empty($customerInfo['budget'])) {
            return 'budget_inquiry';
        } elseif (empty($customerInfo['occasion'])) {
            return 'needs_assessment';
        } else {
            // All information collected, provide recommendations
            return 'product_recommendation';
        }
    }
    
    /**
     * Extract customer information from conversation history
     */
    private function extractCustomerInfo($conversationHistory) {
        $info = [
            'skin_tone' => '',
            'color_preference' => '',
            'texture_preference' => '',
            'budget' => '',
            'occasion' => ''
        ];
        
        foreach ($conversationHistory as $msg) {
            if ($msg['sender'] === 'user') {
                $text = strtolower($msg['text']);
                
                // Extract skin tone
                if (strpos($text, 'sáng') !== false) $info['skin_tone'] = 'sáng';
                elseif (strpos($text, 'tối') !== false) $info['skin_tone'] = 'tối';
                elseif (strpos($text, 'trung bình') !== false) $info['skin_tone'] = 'trung bình';
                
                // Extract color preference
                if (strpos($text, 'đỏ') !== false) $info['color_preference'] = 'đỏ';
                elseif (strpos($text, 'hồng') !== false) $info['color_preference'] = 'hồng';
                elseif (strpos($text, 'nude') !== false) $info['color_preference'] = 'nude';
                elseif (strpos($text, 'cam') !== false) $info['color_preference'] = 'cam';
                elseif (strpos($text, 'tím') !== false) $info['color_preference'] = 'tím';
                
                // Extract texture preference
                if (strpos($text, 'lì') !== false || strpos($text, 'matte') !== false) $info['texture_preference'] = 'lì';
                elseif (strpos($text, 'bóng') !== false || strpos($text, 'glossy') !== false) $info['texture_preference'] = 'bóng';
                elseif (strpos($text, 'dưỡng') !== false || strpos($text, 'cream') !== false) $info['texture_preference'] = 'dưỡng ẩm';
                
                // Extract budget
                if (preg_match('/(\d+)\s*k/', $text, $matches)) {
                    $info['budget'] = $matches[1] . 'k';
                } elseif (preg_match('/(\d+)\s*tr/', $text, $matches)) {
                    $info['budget'] = $matches[1] . 'tr';
                }
                
                // Extract occasion
                if (strpos($text, 'quan hệ') !== false || strpos($text, 'date') !== false) $info['occasion'] = 'quan hệ';
                elseif (strpos($text, 'đi làm') !== false || strpos($text, 'công việc') !== false) $info['occasion'] = 'đi làm';
                elseif (strpos($text, 'đi chơi') !== false || strpos($text, 'party') !== false) $info['occasion'] = 'đi chơi';
                elseif (strpos($text, 'sự kiện') !== false || strpos($text, 'đặc biệt') !== false) $info['occasion'] = 'sự kiện đặc biệt';
            }
        }
        
        return $info;
    }
    
    /**
     * Check if needs follow-up questions
     */
    private function needsFollowUp($currentStage, $conversationHistory) {
        $followUpStages = ['greeting', 'needs_assessment', 'skin_tone_analysis', 'color_preference', 'texture_preference'];
        return in_array($currentStage, $followUpStages);
    }
    
    /**
     * Generate greeting response
     */
    private function generateGreetingResponse() {
        $greetings = [
            "Chào bạn! 💄 Mình là chuyên gia tư vấn làm đẹp của Linh2Store. Mình rất vui được giúp bạn tìm ra thỏi son hoàn hảo nhất! Bạn đang tìm kiếm một màu son cho dịp đặc biệt hay để sử dụng hàng ngày?",
            "Xin chào! 🌸 Mình là cố vấn làm đẹp chuyên nghiệp của Linh2Store. Mình sẽ giúp bạn tìm được thỏi son phù hợp nhất với tone da và phong cách của bạn. Bạn có dịp gì đặc biệt cần son môi không?",
            "Chào mừng bạn đến với Linh2Store! 💋 Mình là chuyên gia tư vấn son môi, sẵn sàng giúp bạn tìm ra màu son lý tưởng. Bạn thường thích những tông màu nào? Đỏ quyến rũ, hồng ngọt ngào, hay nude tự nhiên?"
        ];
        
        return $greetings[array_rand($greetings)];
    }
    
    /**
     * Generate needs assessment response
     */
    private function generateNeedsAssessmentResponse($message) {
        $responses = [
            "Tuyệt vời! Mình hiểu bạn đang tìm son môi. Để mình tư vấn chính xác nhất, bạn có thể cho mình biết:\n\n1️⃣ Bạn thường sử dụng son cho dịp gì? (Đi làm, đi chơi, sự kiện đặc biệt)\n2️⃣ Bạn có tone da sáng, trung bình hay tối?\n3️⃣ Bạn thích chất son lì, bóng hay dưỡng ẩm?",
            "Rất tốt! Mình sẽ giúp bạn tìm son phù hợp. Trước tiên, mình cần hiểu thêm về bạn:\n\n💡 Bạn có thể miêu tả tone da của mình không? (Sáng, trung bình, tối)\n💡 Bạn thích những tông màu nào? (Đỏ, hồng, nude, cam...)\n💡 Bạn muốn chất son như thế nào? (Matte, glossy, cream)",
            "Tuyệt! Mình rất vui được tư vấn cho bạn. Để đưa ra gợi ý chính xác nhất, bạn có thể chia sẻ:\n\n🎨 Tone da của bạn là gì? (Sáng/trung bình/tối, ấm/lạnh)\n🎨 Sở thích màu sắc của bạn?\n🎨 Chất son bạn mong muốn? (Lì, bóng, dưỡng ẩm)"
        ];
        
        return $responses[array_rand($responses)];
    }
    
    /**
     * Generate skin tone response
     */
    private function generateSkinToneResponse($message) {
        $responses = [
            "Cảm ơn bạn đã chia sẻ! 💕 Với tone da này, mình có một số gợi ý màu son rất phù hợp. Bạn thích những tông màu nào? Ví dụ: đỏ quyến rũ, hồng ngọt ngào, cam tươi trẻ, hay nâu đất cá tính?",
            "Tuyệt vời! Mình đã hiểu tone da của bạn. Bây giờ bạn có thể cho mình biết sở thích màu sắc không? Mình có thể gợi ý những màu son sẽ làm nổi bật vẻ đẹp tự nhiên của bạn!",
            "Perfect! 🎯 Với tone da này, mình sẽ đưa ra những gợi ý màu son hoàn hảo. Bạn thích chất son như thế nào? Lì (matte), có độ bóng (glossy), hay dưỡng ẩm (cream)?"
        ];
        
        return $responses[array_rand($responses)];
    }
    
    /**
     * Generate color preference response
     */
    private function generateColorPreferenceResponse($message) {
        $responses = [
            "Tuyệt vời! Mình hiểu sở thích màu sắc của bạn rồi. Bạn muốn chất son như thế nào? Lì (matte) cho độ bền cao, bóng (glossy) cho vẻ quyến rũ, hay dưỡng ẩm (cream) cho sự thoải mái?",
            "Rất tốt! Với sở thích màu sắc này, mình sẽ tìm những thỏi son phù hợp nhất. Bạn có ngân sách cụ thể nào không? Mình sẽ gợi ý trong tầm giá phù hợp.",
            "Perfect! 🎨 Mình đã hiểu rõ sở thích của bạn. Bây giờ bạn có thể cho mình biết ngân sách dự kiến không? Mình sẽ đưa ra những lựa chọn tốt nhất trong tầm giá của bạn."
        ];
        
        return $responses[array_rand($responses)];
    }
    
    /**
     * Generate texture preference response
     */
    private function generateTexturePreferenceResponse($message) {
        $responses = [
            "Tuyệt vời! Mình đã hiểu rõ nhu cầu của bạn. Bạn có ngân sách cụ thể nào không? Mình sẽ gợi ý những thỏi son chất lượng tốt nhất trong tầm giá phù hợp.",
            "Perfect! 💄 Với những tiêu chí này, mình sẽ đưa ra những gợi ý hoàn hảo. Bạn có ngân sách dự kiến bao nhiêu? Mình sẽ tìm những sản phẩm tốt nhất trong tầm giá của bạn.",
            "Rất tốt! Mình đã nắm được sở thích của bạn. Bạn có thể cho mình biết ngân sách không? Mình sẽ gợi ý những thỏi son đáng giá nhất trong tầm giá của bạn."
        ];
        
        return $responses[array_rand($responses)];
    }
    
    /**
     * Generate budget response
     */
    private function generateBudgetResponse($message) {
        $responses = [
            "Cảm ơn bạn! Với ngân sách này, mình sẽ gợi ý những thỏi son chất lượng tốt nhất. Dựa trên tone da và sở thích của bạn, mình có một số lựa chọn hoàn hảo:",
            "Tuyệt vời! Mình hiểu ngân sách của bạn rồi. Với những tiêu chí bạn đã chia sẻ, mình sẽ đưa ra những gợi ý tốt nhất trong tầm giá này:",
            "Perfect! 💰 Với ngân sách này, mình sẽ tìm những thỏi son đáng giá nhất. Dựa trên thông tin bạn cung cấp, mình có những gợi ý hoàn hảo:"
        ];
        
        return $responses[array_rand($responses)];
    }
    
    /**
     * Generate product recommendation
     */
    private function generateProductRecommendation($message, $conversationHistory) {
        // Extract customer preferences from conversation
        $customerInfo = $this->extractCustomerInfo($conversationHistory);
        
        $recommendations = $this->getProductRecommendations($customerInfo);
        
        $response = "Perfect! 💄 Dựa trên thông tin bạn đã chia sẻ:\n";
        $response .= "• Tone da: {$customerInfo['skin_tone']}\n";
        $response .= "• Màu sắc: {$customerInfo['color_preference']}\n";
        $response .= "• Chất son: {$customerInfo['texture_preference']}\n";
        $response .= "• Dịp sử dụng: {$customerInfo['occasion']}\n\n";
        
        $response .= "Mình gợi ý những thỏi son hoàn hảo cho bạn:\n\n";
        
        foreach ($recommendations as $index => $product) {
            $response .= ($index + 1) . ". **{$product['name']}** - {$product['color']}\n";
            $response .= "   💄 {$product['description']}\n";
            $response .= "   💰 Giá: {$product['price']}\n";
            $response .= "   🔗 [Xem chi tiết]({$product['link']})\n\n";
        }
        
        $response .= "Những thỏi son này sẽ làm nổi bật vẻ đẹp tự nhiên của bạn! Bạn có muốn thêm sản phẩm nào vào giỏ hàng không? 🛒";
        
        return $response;
    }
    
    /**
     * Generate objection handling response
     */
    private function generateObjectionHandlingResponse($message) {
        $responses = [
            "Mình hiểu sự do dự của bạn! 💕 Hiện tại chúng mình đang có chương trình giảm giá 10% cho đơn hàng đầu tiên. Ngoài ra, chúng mình có chính sách đổi trả trong 7 ngày nếu không hài lòng. Bạn có muốn thử một thỏi son nhỏ trước không?",
            "Mình hoàn toàn hiểu! 😊 Để bạn yên tâm hơn, chúng mình có chính sách đổi trả miễn phí trong 7 ngày. Ngoài ra, hiện đang có ưu đãi giảm giá 15% cho khách hàng mới. Bạn có muốn mình tư vấn thêm về thành phần sản phẩm không?",
            "Mình hiểu sự quan tâm của bạn! 🌟 Chúng mình cam kết sản phẩm chính hãng 100% và có chính sách bảo hành. Hiện đang có khuyến mãi đặc biệt: Mua 2 tặng 1 cho một số sản phẩm. Bạn có muốn mình giải thích thêm về lợi ích của từng thỏi son không?"
        ];
        
        return $responses[array_rand($responses)];
    }
    
    /**
     * Generate closing response
     */
    private function generateClosingResponse($message) {
        $responses = [
            "Cảm ơn bạn đã tin tưởng mình! 💋 Mình rất vui được tư vấn cho bạn. Nếu có thêm câu hỏi gì về son môi hay mỹ phẩm, đừng ngần ngại hỏi mình nhé! Chúc bạn luôn xinh đẹp! 🌸",
            "Rất vui được phục vụ bạn! 💄 Mình hy vọng bạn sẽ tìm được thỏi son hoàn hảo. Nếu cần tư vấn thêm về mỹ phẩm khác, mình luôn sẵn sàng hỗ trợ! Chúc bạn một ngày tuyệt vời! ✨",
            "Cảm ơn bạn! 🌺 Mình rất hạnh phúc được giúp bạn tìm son phù hợp. Hãy quay lại nếu cần tư vấn thêm về làm đẹp nhé! Chúc bạn luôn tự tin và xinh đẹp! 💕"
        ];
        
        return $responses[array_rand($responses)];
    }
    
    /**
     * Generate default response
     */
    private function generateDefaultResponse($message) {
        $responses = [
            "Mình hiểu bạn đang cần hỗ trợ! 💄 Bạn có thể cho mình biết cụ thể hơn về nhu cầu son môi của bạn không? Mình sẽ tư vấn chính xác nhất.",
            "Mình sẵn sàng giúp bạn! 🌸 Bạn đang tìm kiếm loại son môi nào? Màu sắc, chất son, hay thương hiệu cụ thể?",
            "Mình rất vui được hỗ trợ bạn! 💋 Bạn có thể chia sẻ thêm về sở thích son môi của mình không? Mình sẽ đưa ra gợi ý phù hợp nhất."
        ];
        
        return $responses[array_rand($responses)];
    }
    
    /**
     * Extract customer preferences from conversation
     */
    private function extractCustomerPreferences($conversationHistory) {
        $preferences = [
            'skin_tone' => '',
            'color_preference' => '',
            'texture_preference' => '',
            'budget' => '',
            'occasion' => ''
        ];
        
        foreach ($conversationHistory as $msg) {
            if ($msg['sender'] === 'user') {
                $text = strtolower($msg['text']);
                
                // Extract skin tone
                if (strpos($text, 'sáng') !== false) $preferences['skin_tone'] = 'sáng';
                elseif (strpos($text, 'tối') !== false) $preferences['skin_tone'] = 'tối';
                elseif (strpos($text, 'trung bình') !== false) $preferences['skin_tone'] = 'trung bình';
                
                // Extract color preference
                if (strpos($text, 'đỏ') !== false) $preferences['color_preference'] = 'đỏ';
                elseif (strpos($text, 'hồng') !== false) $preferences['color_preference'] = 'hồng';
                elseif (strpos($text, 'nude') !== false) $preferences['color_preference'] = 'nude';
                elseif (strpos($text, 'cam') !== false) $preferences['color_preference'] = 'cam';
                
                // Extract texture preference
                if (strpos($text, 'lì') !== false || strpos($text, 'matte') !== false) $preferences['texture_preference'] = 'lì';
                elseif (strpos($text, 'bóng') !== false || strpos($text, 'glossy') !== false) $preferences['texture_preference'] = 'bóng';
                elseif (strpos($text, 'dưỡng') !== false || strpos($text, 'cream') !== false) $preferences['texture_preference'] = 'dưỡng ẩm';
            }
        }
        
        return $preferences;
    }
    
    /**
     * Get product recommendations based on customer info
     */
    private function getProductRecommendations($customerInfo) {
        // Filter recommendations based on customer preferences
        $allRecommendations = [
            // Red lipsticks for bright skin tone
            [
                'name' => 'MAC Ruby Woo',
                'color' => 'Đỏ cổ điển',
                'description' => 'Son matte đỏ cổ điển, bền màu cả ngày, hoàn hảo cho tone da sáng',
                'price' => '650.000đ',
                'link' => '/san-pham/mac-ruby-woo',
                'skin_tone' => 'sáng',
                'color_preference' => 'đỏ',
                'texture_preference' => 'lì',
                'occasion' => 'quan hệ'
            ],
            [
                'name' => 'Dior 999',
                'color' => 'Đỏ tươi',
                'description' => 'Son đỏ tươi sang trọng, phù hợp tone da sáng, lý tưởng cho buổi hẹn hò',
                'price' => '850.000đ',
                'link' => '/san-pham/dior-999',
                'skin_tone' => 'sáng',
                'color_preference' => 'đỏ',
                'texture_preference' => 'lì',
                'occasion' => 'quan hệ'
            ],
            [
                'name' => 'Chanel Rouge Allure',
                'color' => 'Đỏ quyến rũ',
                'description' => 'Son đỏ quyến rũ, thiết kế sang trọng, hoàn hảo cho tone da sáng',
                'price' => '1.200.000đ',
                'link' => '/san-pham/chanel-rouge-allure',
                'skin_tone' => 'sáng',
                'color_preference' => 'đỏ',
                'texture_preference' => 'lì',
                'occasion' => 'quan hệ'
            ],
            [
                'name' => 'YSL Rouge Pur Couture',
                'color' => 'Đỏ đậm',
                'description' => 'Son đỏ đậm quyến rũ, chất lì bền màu, phù hợp tone da sáng',
                'price' => '750.000đ',
                'link' => '/san-pham/ysl-rouge-pur-couture',
                'skin_tone' => 'sáng',
                'color_preference' => 'đỏ',
                'texture_preference' => 'lì',
                'occasion' => 'quan hệ'
            ]
        ];
        
        // Filter recommendations based on customer preferences
        $filteredRecommendations = [];
        foreach ($allRecommendations as $product) {
            $match = true;
            
            // Check skin tone match
            if (!empty($customerInfo['skin_tone']) && $product['skin_tone'] !== $customerInfo['skin_tone']) {
                $match = false;
            }
            
            // Check color preference match
            if (!empty($customerInfo['color_preference']) && $product['color_preference'] !== $customerInfo['color_preference']) {
                $match = false;
            }
            
            // Check texture preference match
            if (!empty($customerInfo['texture_preference']) && $product['texture_preference'] !== $customerInfo['texture_preference']) {
                $match = false;
            }
            
            // Check occasion match
            if (!empty($customerInfo['occasion']) && $product['occasion'] !== $customerInfo['occasion']) {
                $match = false;
            }
            
            if ($match) {
                $filteredRecommendations[] = $product;
            }
        }
        
        // If no specific matches, return general recommendations
        if (empty($filteredRecommendations)) {
            $filteredRecommendations = array_slice($allRecommendations, 0, 3);
        }
        
        return array_slice($filteredRecommendations, 0, 3);
    }
}
?>
