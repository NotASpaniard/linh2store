<?php
/**
 * AI Natural Language Processing
 * Linh2Store - Advanced AI NLP Engine
 */

class AINLP {
    
    /**
     * Analyze user intent from natural language
     */
    public function analyzeIntent($message) {
        $message = strtolower(trim($message));
        
        // Intent patterns with synonyms and variations
        $intents = [
            'product_search' => [
                'keywords' => ['tìm', 'có', 'bán', 'mua', 'son', 'kem', 'phấn', 'mascara', 'màu', 'đỏ', 'hồng', 'nude'],
                'patterns' => ['tìm.*son', 'có.*son', 'bán.*son', 'mua.*son', 'son.*màu', 'kem.*nền', 'phấn.*mắt'],
                'synonyms' => [
                    'son' => ['son môi', 'lipstick', 'son'],
                    'kem nền' => ['foundation', 'kem nền', 'base'],
                    'phấn mắt' => ['eyeshadow', 'phấn mắt', 'shadow'],
                    'mascara' => ['mascara', 'chuốt mi'],
                    'đỏ' => ['đỏ', 'red', 'màu đỏ'],
                    'hồng' => ['hồng', 'pink', 'màu hồng'],
                    'nude' => ['nude', 'màu nude', 'màu da']
                ]
            ],
            'brand_inquiry' => [
                'keywords' => ['thương hiệu', 'brand', 'mac', 'dior', 'chanel', 'ysl', 'nars', 'tom ford'],
                'patterns' => ['thương hiệu.*nào', 'brand.*nào', 'mac.*có', 'dior.*có', 'chanel.*có'],
                'synonyms' => [
                    'thương hiệu' => ['brand', 'thương hiệu', 'hãng'],
                    'mac' => ['mac', 'makeup art cosmetics'],
                    'dior' => ['dior', 'christian dior'],
                    'chanel' => ['chanel', 'coco chanel']
                ]
            ],
            'shipping_info' => [
                'keywords' => ['giao hàng', 'ship', 'phí ship', 'thời gian', 'cod', 'nhận hàng'],
                'patterns' => ['giao hàng.*như thế', 'ship.*bao nhiêu', 'thời gian.*giao', 'cod.*là gì'],
                'synonyms' => [
                    'giao hàng' => ['shipping', 'giao hàng', 'delivery'],
                    'phí ship' => ['shipping fee', 'phí ship', 'phí giao hàng'],
                    'cod' => ['cod', 'cash on delivery', 'trả tiền khi nhận']
                ]
            ],
            'payment_info' => [
                'keywords' => ['thanh toán', 'payment', 'chuyển khoản', 'momo', 'zalopay', 'vnpay', 'thẻ'],
                'patterns' => ['thanh toán.*như thế', 'payment.*method', 'chuyển khoản', 'momo.*thanh toán'],
                'synonyms' => [
                    'thanh toán' => ['payment', 'thanh toán', 'pay'],
                    'chuyển khoản' => ['bank transfer', 'chuyển khoản', 'transfer'],
                    'momo' => ['momo', 'ví momo'],
                    'zalopay' => ['zalopay', 'zalo pay']
                ]
            ],
            'price_inquiry' => [
                'keywords' => ['giá', 'price', 'bao nhiêu', 'rẻ', 'đắt', 'khuyến mãi', 'sale'],
                'patterns' => ['giá.*bao nhiêu', 'price.*how much', 'rẻ.*không', 'đắt.*không', 'sale.*không'],
                'synonyms' => [
                    'giá' => ['price', 'giá', 'cost'],
                    'rẻ' => ['cheap', 'rẻ', 'affordable'],
                    'đắt' => ['expensive', 'đắt', 'high price'],
                    'khuyến mãi' => ['sale', 'discount', 'khuyến mãi', 'giảm giá']
                ]
            ],
            'greeting' => [
                'keywords' => ['xin chào', 'hello', 'hi', 'chào', 'chào bạn'],
                'patterns' => ['xin chào', 'hello', 'hi', 'chào.*bạn'],
                'synonyms' => [
                    'xin chào' => ['hello', 'hi', 'xin chào', 'chào'],
                    'chào bạn' => ['hello', 'hi there', 'chào bạn']
                ]
            ],
            'help_request' => [
                'keywords' => ['giúp', 'help', 'hỗ trợ', 'support', 'có thể giúp', 'làm gì'],
                'patterns' => ['giúp.*tôi', 'help.*me', 'hỗ trợ.*gì', 'có thể.*giúp'],
                'synonyms' => [
                    'giúp' => ['help', 'giúp', 'assist'],
                    'hỗ trợ' => ['support', 'hỗ trợ', 'assistance']
                ]
            ]
        ];
        
        $scores = [];
        
        foreach ($intents as $intent => $config) {
            $score = 0;
            
            // Check keywords
            foreach ($config['keywords'] as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $score += 2;
                }
            }
            
            // Check patterns
            foreach ($config['patterns'] as $pattern) {
                if (preg_match('/' . $pattern . '/i', $message)) {
                    $score += 3;
                }
            }
            
            // Check synonyms
            foreach ($config['synonyms'] as $word => $synonyms) {
                foreach ($synonyms as $synonym) {
                    if (strpos($message, $synonym) !== false) {
                        $score += 1;
                    }
                }
            }
            
            $scores[$intent] = $score;
        }
        
        // Return intent with highest score
        $bestIntent = array_keys($scores, max($scores))[0];
        $confidence = max($scores) / 10; // Normalize to 0-1
        
        return [
            'intent' => $bestIntent,
            'confidence' => min(1.0, $confidence),
            'scores' => $scores
        ];
    }
    
    /**
     * Extract entities from message
     */
    public function extractEntities($message) {
        $entities = [];
        $message = strtolower(trim($message));
        
        // Color entities
        $colors = [
            'đỏ' => ['đỏ', 'red', 'màu đỏ'],
            'hồng' => ['hồng', 'pink', 'màu hồng'],
            'nude' => ['nude', 'màu nude', 'màu da'],
            'cam' => ['cam', 'orange', 'màu cam'],
            'tím' => ['tím', 'purple', 'màu tím'],
            'xanh' => ['xanh', 'blue', 'màu xanh']
        ];
        
        foreach ($colors as $color => $variations) {
            foreach ($variations as $variation) {
                if (strpos($message, $variation) !== false) {
                    $entities['color'] = $color;
                    break 2;
                }
            }
        }
        
        // Brand entities
        $brands = [
            'mac' => ['mac', 'makeup art cosmetics'],
            'dior' => ['dior', 'christian dior'],
            'chanel' => ['chanel', 'coco chanel'],
            'ysl' => ['ysl', 'yves saint laurent', 'saint laurent'],
            'nars' => ['nars'],
            'tom ford' => ['tom ford'],
            'urban decay' => ['urban decay'],
            'lancome' => ['lancome'],
            'estee lauder' => ['estee lauder']
        ];
        
        foreach ($brands as $brand => $variations) {
            foreach ($variations as $variation) {
                if (strpos($message, $variation) !== false) {
                    $entities['brand'] = $brand;
                    break 2;
                }
            }
        }
        
        // Product entities
        $products = [
            'son môi' => ['son môi', 'lipstick', 'son', 'son lipstick'],
            'kem nền' => ['kem nền', 'foundation', 'base'],
            'phấn mắt' => ['phấn mắt', 'eyeshadow', 'shadow'],
            'mascara' => ['mascara', 'chuốt mi'],
            'kem che khuyết điểm' => ['kem che khuyết điểm', 'concealer'],
            'son dưỡng môi' => ['son dưỡng môi', 'lip balm', 'lip care']
        ];
        
        foreach ($products as $product => $variations) {
            foreach ($variations as $variation) {
                if (strpos($message, $variation) !== false) {
                    $entities['product'] = $product;
                    break 2;
                }
            }
        }
        
        return $entities;
    }
    
    /**
     * Generate contextual response
     */
    public function generateContextualResponse($intent, $entities, $originalMessage) {
        $responses = [
            'product_search' => [
                'default' => 'Tôi có thể giúp bạn tìm sản phẩm mỹ phẩm. Bạn đang tìm gì cụ thể?',
                'with_color' => 'Tôi có thể giúp bạn tìm {product} màu {color}. Chúng tôi có nhiều lựa chọn từ các thương hiệu cao cấp.',
                'with_brand' => 'Thương hiệu {brand} có nhiều sản phẩm chất lượng cao. Bạn quan tâm sản phẩm nào?',
                'with_product' => 'Chúng tôi có nhiều {product} từ các thương hiệu cao cấp. Bạn thích màu gì?'
            ],
            'brand_inquiry' => [
                'default' => 'Chúng tôi có nhiều thương hiệu mỹ phẩm cao cấp như MAC, Dior, Chanel, YSL, NARS, Tom Ford, Urban Decay.',
                'specific_brand' => 'Thương hiệu {brand} nổi tiếng với {brand_info}. Bạn quan tâm sản phẩm nào của {brand}?'
            ],
            'shipping_info' => [
                'default' => 'Chúng tôi giao hàng toàn quốc với phí ship từ 30.000đ. Miễn phí ship cho đơn hàng từ 500.000đ. Thời gian giao hàng 1-3 ngày làm việc.',
                'cod' => 'COD (trả tiền khi nhận hàng) là hình thức thanh toán tiện lợi. Bạn chỉ cần thanh toán khi shipper giao hàng đến.',
                'time' => 'Thời gian giao hàng 1-3 ngày làm việc cho khu vực nội thành, 3-5 ngày cho tỉnh thành khác.'
            ],
            'payment_info' => [
                'default' => 'Chúng tôi hỗ trợ nhiều hình thức thanh toán: COD, chuyển khoản ngân hàng, ví điện tử MoMo, ZaloPay, VNPay, thẻ tín dụng.',
                'bank' => 'Bạn có thể chuyển khoản qua các ngân hàng: Vietcombank, BIDV, Techcombank, Agribank.',
                'ewallet' => 'Thanh toán qua ví điện tử MoMo, ZaloPay, VNPay rất nhanh chóng và an toàn.'
            ],
            'price_inquiry' => [
                'default' => 'Giá sản phẩm dao động từ 200k-2tr tùy thương hiệu và loại sản phẩm. Bạn quan tâm sản phẩm nào cụ thể?',
                'cheap' => 'Chúng tôi cam kết giá tốt nhất thị trường với sản phẩm chính hãng 100%. Thường xuyên có khuyến mãi giảm giá 10-20%.',
                'sale' => 'Hiện tại chúng tôi có nhiều chương trình khuyến mãi: Giảm giá 10-20% cho đơn hàng mới, Tặng quà kèm theo đơn hàng từ 500k.'
            ],
            'greeting' => [
                'default' => 'Xin chào! Tôi là AI Chatbot của Linh2Store. Tôi có thể giúp bạn tìm sản phẩm mỹ phẩm, kiểm tra đơn hàng, hoặc trả lời câu hỏi. Bạn cần hỗ trợ gì?'
            ],
            'help_request' => [
                'default' => 'Tôi có thể giúp bạn: 1) Tìm kiếm sản phẩm mỹ phẩm, 2) Kiểm tra thông tin đơn hàng, 3) Tư vấn về thương hiệu, 4) Hướng dẫn thanh toán và giao hàng, 5) Trả lời các câu hỏi khác.'
            ]
        ];
        
        $intentResponses = $responses[$intent] ?? $responses['help_request'];
        
        // Choose most appropriate response based on entities
        if (isset($entities['color']) && isset($entities['product'])) {
            $response = $intentResponses['with_color'] ?? $intentResponses['default'];
            $response = str_replace('{product}', $entities['product'], $response);
            $response = str_replace('{color}', $entities['color'], $response);
        } elseif (isset($entities['brand'])) {
            $response = $intentResponses['specific_brand'] ?? $intentResponses['default'];
            $response = str_replace('{brand}', $entities['brand'], $response);
            $response = str_replace('{brand_info}', $this->getBrandInfo($entities['brand']), $response);
        } elseif (isset($entities['product'])) {
            $response = $intentResponses['with_product'] ?? $intentResponses['default'];
            $response = str_replace('{product}', $entities['product'], $response);
        } else {
            $response = $intentResponses['default'];
        }
        
        return $response;
    }
    
    /**
     * Get brand information
     */
    private function getBrandInfo($brand) {
        $brandInfo = [
            'mac' => 'son môi matte và kem nền chuyên nghiệp',
            'dior' => 'son Rouge Dior và kem nền Forever cao cấp',
            'chanel' => 'son Rouge Allure và kem nền Vitalumière sang trọng',
            'ysl' => 'son Rouge Pur Couture và kem nền All Hours quyến rũ',
            'nars' => 'son Audacious và kem nền Sheer Glow độc đáo',
            'tom ford' => 'son Lip Color và kem nền Traceless Foundation đỉnh cao',
            'urban decay' => 'bảng phấn mắt Naked và son Vice màu sắc độc đáo'
        ];
        
        return $brandInfo[$brand] ?? 'sản phẩm chất lượng cao';
    }
    
    /**
     * Check if message is unclear or ambiguous
     */
    public function isUnclear($message, $intent, $confidence) {
        // If confidence is too low, message might be unclear
        if ($confidence < 0.3) {
            return true;
        }
        
        // Check for unclear patterns
        $unclearPatterns = [
            'không biết', 'không hiểu', 'gì đó', 'cái gì', 'như thế nào'
        ];
        
        foreach ($unclearPatterns as $pattern) {
            if (strpos(strtolower($message), $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate clarification questions
     */
    public function generateClarificationQuestions($intent, $entities) {
        $questions = [
            'product_search' => [
                'Bạn đang tìm sản phẩm gì? Son môi, kem nền, phấn mắt?',
                'Bạn thích màu gì? Đỏ, hồng, nude, hay màu khác?',
                'Bạn có thương hiệu yêu thích nào không?'
            ],
            'brand_inquiry' => [
                'Bạn quan tâm thương hiệu nào? MAC, Dior, Chanel, YSL?',
                'Bạn muốn tìm hiểu về sản phẩm gì của thương hiệu đó?'
            ],
            'shipping_info' => [
                'Bạn muốn biết về phí ship, thời gian giao hàng, hay COD?',
                'Bạn ở khu vực nào? Nội thành hay tỉnh thành khác?'
            ],
            'payment_info' => [
                'Bạn muốn thanh toán bằng cách nào? COD, chuyển khoản, hay ví điện tử?',
                'Bạn có thẻ tín dụng không? Chúng tôi hỗ trợ trả góp 0% lãi suất.'
            ]
        ];
        
        return $questions[$intent] ?? ['Bạn có thể nói rõ hơn về điều bạn cần hỗ trợ không?'];
    }
}
?>
