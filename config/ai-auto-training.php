<?php
/**
 * AI Auto Training Engine
 * Linh2Store - Automatic AI Training System
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/ai-training.php';

class AIAutoTraining {
    private $conn;
    private $training;
    
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->training = new AITraining();
    }
    
    /**
     * Auto train AI with comprehensive data
     */
    public function autoTrain() {
        $results = [
            'product_knowledge' => $this->trainProductKnowledge(),
            'brand_knowledge' => $this->trainBrandKnowledge(),
            'shipping_knowledge' => $this->trainShippingKnowledge(),
            'payment_knowledge' => $this->trainPaymentKnowledge(),
            'general_knowledge' => $this->trainGeneralKnowledge(),
            'conversation_patterns' => $this->trainConversationPatterns()
        ];
        
        return $results;
    }
    
    /**
     * Train product knowledge
     */
    private function trainProductKnowledge() {
        $productData = [
            // Son môi
            ['question' => 'Tìm son môi màu đỏ', 'answer' => 'Chúng tôi có nhiều son môi màu đỏ từ các thương hiệu cao cấp như MAC Ruby Woo, Dior 999, Chanel Rouge Allure, YSL Rouge Pur Couture. Màu đỏ có nhiều tông từ đỏ cổ điển đến đỏ cam, đỏ tím.'],
            ['question' => 'Son môi màu hồng', 'answer' => 'Son môi màu hồng rất đa dạng từ hồng nude, hồng pastel đến hồng đậm. Thương hiệu nổi bật: MAC Pink Plaid, Dior Addict, Chanel Rouge Coco, NARS Schiap.'],
            ['question' => 'Son môi màu nude', 'answer' => 'Son nude là xu hướng hot hiện tại. Chúng tôi có MAC Velvet Teddy, Dior Addict Beige, Chanel Rouge Coco Shine, YSL Rouge Volupté với nhiều tông nude khác nhau.'],
            ['question' => 'Son môi matte', 'answer' => 'Son matte cho độ bền cao và màu sắc rực rỡ. Nổi bật: MAC Retro Matte, Dior Rouge Dior, Chanel Rouge Allure Velvet, NARS Velvet Matte.'],
            ['question' => 'Son môi glossy', 'answer' => 'Son glossy cho độ bóng và quyến rũ. Thương hiệu: Dior Addict, Chanel Rouge Coco Shine, YSL Rouge Volupté, Tom Ford Lip Color.'],
            
            // Mỹ phẩm khác
            ['question' => 'Kem nền', 'answer' => 'Chúng tôi có kem nền từ các thương hiệu cao cấp: MAC Studio Fix, Dior Forever, Chanel Vitalumière, YSL All Hours, NARS Sheer Glow với nhiều tông màu.'],
            ['question' => 'Phấn mắt', 'answer' => 'Phấn mắt đa dạng từ Urban Decay Naked, MAC Eyeshadow, Dior 5 Couleurs, Chanel Les 4 Ombres, NARS Eyeshadow với nhiều màu sắc.'],
            ['question' => 'Mascara', 'answer' => 'Mascara cho lông mi dài và cong: Dior Diorshow, Chanel Le Volume, YSL Mascara Volume Effet Faux Cils, MAC False Lashes.'],
            ['question' => 'Kem che khuyết điểm', 'answer' => 'Kem che khuyết điểm chất lượng cao: NARS Radiant Creamy Concealer, MAC Studio Finish, Dior Forever, Chanel Le Correcteur.'],
            ['question' => 'Son dưỡng môi', 'answer' => 'Son dưỡng môi giữ ẩm: Dior Addict Lip Glow, Chanel Rouge Coco Shine, YSL Rouge Volupté Shine, MAC Lip Conditioner.']
        ];
        
        $trained = 0;
        foreach ($productData as $data) {
            $result = $this->training->trainWithConversation(
                $data['question'],
                $data['answer'],
                ['auto_training' => true, 'category' => 'product_search', 'source' => 'product_knowledge']
            );
            if ($result) $trained++;
        }
        
        return $trained;
    }
    
    /**
     * Train brand knowledge
     */
    private function trainBrandKnowledge() {
        $brandData = [
            ['question' => 'Thương hiệu MAC', 'answer' => 'MAC (Make-up Art Cosmetics) là thương hiệu mỹ phẩm chuyên nghiệp nổi tiếng với son môi matte, kem nền Studio Fix, phấn mắt đa dạng. Được yêu thích bởi makeup artist và beauty blogger.'],
            ['question' => 'Thương hiệu Dior', 'answer' => 'Dior là thương hiệu luxury cao cấp với son môi Rouge Dior, kem nền Forever, mascara Diorshow. Nổi tiếng với chất lượng cao và thiết kế sang trọng.'],
            ['question' => 'Thương hiệu Chanel', 'answer' => 'Chanel là thương hiệu luxury hàng đầu với son Rouge Allure, kem nền Vitalumière, phấn mắt Les 4 Ombres. Đại diện cho sự sang trọng và tinh tế.'],
            ['question' => 'Thương hiệu YSL', 'answer' => 'Yves Saint Laurent (YSL) nổi tiếng với son Rouge Pur Couture, kem nền All Hours, mascara Volume Effet. Thiết kế sang trọng và chất lượng cao cấp.'],
            ['question' => 'Thương hiệu NARS', 'answer' => 'NARS được yêu thích với son môi Audacious, kem nền Sheer Glow, phấn mắt đa dạng. Nổi tiếng với màu sắc độc đáo và chất lượng chuyên nghiệp.'],
            ['question' => 'Thương hiệu Tom Ford', 'answer' => 'Tom Ford là thương hiệu luxury cao cấp với son Lip Color, kem nền Traceless Foundation. Nổi tiếng với thiết kế sang trọng và chất lượng đỉnh cao.'],
            ['question' => 'Thương hiệu Urban Decay', 'answer' => 'Urban Decay nổi tiếng với bảng phấn mắt Naked, son môi Vice, kem nền All Nighter. Được yêu thích bởi màu sắc độc đáo và chất lượng chuyên nghiệp.'],
            ['question' => 'Thương hiệu nào tốt nhất', 'answer' => 'Mỗi thương hiệu có điểm mạnh riêng: MAC cho son matte, Dior cho luxury, Chanel cho sang trọng, YSL cho thiết kế, NARS cho màu sắc độc đáo. Tùy theo nhu cầu và sở thích cá nhân.']
        ];
        
        $trained = 0;
        foreach ($brandData as $data) {
            $result = $this->training->trainWithConversation(
                $data['question'],
                $data['answer'],
                ['auto_training' => true, 'category' => 'brand_info', 'source' => 'brand_knowledge']
            );
            if ($result) $trained++;
        }
        
        return $trained;
    }
    
    /**
     * Train shipping knowledge
     */
    private function trainShippingKnowledge() {
        $shippingData = [
            ['question' => 'Giao hàng như thế nào', 'answer' => 'Chúng tôi giao hàng toàn quốc với phí ship từ 30.000đ. Miễn phí ship cho đơn hàng từ 500.000đ. Thời gian giao hàng 1-3 ngày làm việc tùy theo khu vực.'],
            ['question' => 'Phí ship bao nhiêu', 'answer' => 'Phí ship từ 30.000đ cho đơn hàng dưới 500.000đ. Miễn phí ship cho đơn hàng từ 500.000đ trở lên. Phí ship có thể thay đổi tùy theo khu vực xa gần.'],
            ['question' => 'Thời gian giao hàng', 'answer' => 'Thời gian giao hàng 1-3 ngày làm việc cho khu vực nội thành, 3-5 ngày cho tỉnh thành khác. Đơn hàng sẽ được xử lý trong 24h và giao ngay khi có sẵn.'],
            ['question' => 'Giao hàng nhanh', 'answer' => 'Chúng tôi có dịch vụ giao hàng nhanh trong ngày cho khu vực nội thành với phí ship 50.000đ. Đơn hàng trước 12h sẽ được giao trong ngày.'],
            ['question' => 'Giao hàng quốc tế', 'answer' => 'Hiện tại chúng tôi chỉ giao hàng trong nước. Đang phát triển dịch vụ giao hàng quốc tế trong tương lai gần.'],
            ['question' => 'Theo dõi đơn hàng', 'answer' => 'Bạn có thể theo dõi đơn hàng qua mã vận đơn được gửi qua SMS/Email. Hoặc liên hệ hotline để được hỗ trợ theo dõi đơn hàng.'],
            ['question' => 'Đổi trả hàng', 'answer' => 'Chúng tôi hỗ trợ đổi trả hàng trong 7 ngày nếu sản phẩm còn nguyên vẹn, chưa sử dụng. Phí ship đổi trả do khách hàng chịu trừ trường hợp lỗi từ phía shop.']
        ];
        
        $trained = 0;
        foreach ($shippingData as $data) {
            $result = $this->training->trainWithConversation(
                $data['question'],
                $data['answer'],
                ['auto_training' => true, 'category' => 'shipping_info', 'source' => 'shipping_knowledge']
            );
            if ($result) $trained++;
        }
        
        return $trained;
    }
    
    /**
     * Train payment knowledge
     */
    private function trainPaymentKnowledge() {
        $paymentData = [
            ['question' => 'Thanh toán như thế nào', 'answer' => 'Chúng tôi hỗ trợ nhiều hình thức thanh toán: COD (trả tiền khi nhận hàng), chuyển khoản ngân hàng, ví điện tử MoMo, ZaloPay, VNPay, thẻ tín dụng.'],
            ['question' => 'COD là gì', 'answer' => 'COD (Cash on Delivery) là hình thức trả tiền khi nhận hàng. Bạn chỉ cần thanh toán khi shipper giao hàng đến, không cần chuyển khoản trước.'],
            ['question' => 'Chuyển khoản ngân hàng', 'answer' => 'Bạn có thể chuyển khoản qua các ngân hàng: Vietcombank, BIDV, Techcombank, Agribank. Thông tin chuyển khoản sẽ được gửi qua email sau khi đặt hàng.'],
            ['question' => 'Ví điện tử', 'answer' => 'Chúng tôi hỗ trợ thanh toán qua ví điện tử MoMo, ZaloPay, VNPay. Thanh toán nhanh chóng và an toàn, được bảo mật tuyệt đối.'],
            ['question' => 'Thẻ tín dụng', 'answer' => 'Chấp nhận thanh toán bằng thẻ Visa, Mastercard, JCB. Thanh toán an toàn với hệ thống bảo mật quốc tế, không lưu trữ thông tin thẻ.'],
            ['question' => 'Trả góp', 'answer' => 'Chúng tôi hỗ trợ trả góp qua thẻ tín dụng với lãi suất 0% trong 3-6 tháng. Điều kiện: thẻ tín dụng từ các ngân hàng liên kết.'],
            ['question' => 'Hoàn tiền', 'answer' => 'Trong trường hợp hủy đơn hàng, tiền sẽ được hoàn về tài khoản trong 3-5 ngày làm việc. Với COD, tiền sẽ được hoàn qua chuyển khoản.']
        ];
        
        $trained = 0;
        foreach ($paymentData as $data) {
            $result = $this->training->trainWithConversation(
                $data['question'],
                $data['answer'],
                ['auto_training' => true, 'category' => 'payment_info', 'source' => 'payment_knowledge']
            );
            if ($result) $trained++;
        }
        
        return $trained;
    }
    
    /**
     * Train general knowledge
     */
    private function trainGeneralKnowledge() {
        $generalData = [
            ['question' => 'Xin chào', 'answer' => 'Xin chào! Tôi là AI Chatbot của Linh2Store. Tôi có thể giúp bạn tìm sản phẩm mỹ phẩm, kiểm tra đơn hàng, tư vấn thương hiệu, hướng dẫn thanh toán và giao hàng. Bạn cần hỗ trợ gì?'],
            ['question' => 'Bạn có thể giúp gì', 'answer' => 'Tôi có thể giúp bạn: 1) Tìm kiếm sản phẩm mỹ phẩm theo tên, màu sắc, thương hiệu, 2) Kiểm tra thông tin đơn hàng, 3) Tư vấn về thương hiệu và sản phẩm, 4) Hướng dẫn thanh toán và giao hàng, 5) Trả lời các câu hỏi khác về mỹ phẩm.'],
            ['question' => 'Cảm ơn', 'answer' => 'Không có gì! Tôi rất vui được giúp đỡ bạn. Nếu có thêm câu hỏi gì về mỹ phẩm, đừng ngần ngại hỏi tôi nhé! 😊'],
            ['question' => 'Tạm biệt', 'answer' => 'Tạm biệt! Cảm ơn bạn đã sử dụng dịch vụ của Linh2Store. Chúc bạn có một ngày tốt lành và hẹn gặp lại! 👋'],
            ['question' => 'Giờ làm việc', 'answer' => 'Chúng tôi hoạt động 24/7 online. Thời gian giao hàng: 8h-18h các ngày trong tuần. Hotline hỗ trợ: 8h-22h hàng ngày.'],
            ['question' => 'Liên hệ', 'answer' => 'Bạn có thể liên hệ chúng tôi qua: Hotline: 1900-xxxx, Email: support@linh2store.com, Facebook: Linh2Store, Zalo: Linh2Store. Chúng tôi sẵn sàng hỗ trợ 24/7.'],
            ['question' => 'Địa chỉ', 'answer' => 'Showroom: 123 Đường ABC, Quận XYZ, TP.HCM. Giờ mở cửa: 9h-21h hàng ngày. Bạn có thể đến xem và thử sản phẩm trực tiếp.'],
            ['question' => 'Khuyến mãi', 'answer' => 'Chúng tôi thường có các chương trình khuyến mãi: Giảm giá 10-20% cho đơn hàng mới, Tặng quà kèm theo đơn hàng từ 500k, Miễn phí ship cho đơn hàng từ 500k. Theo dõi fanpage để cập nhật khuyến mãi mới nhất.']
        ];
        
        $trained = 0;
        foreach ($generalData as $data) {
            $result = $this->training->trainWithConversation(
                $data['question'],
                $data['answer'],
                ['auto_training' => true, 'category' => 'general', 'source' => 'general_knowledge']
            );
            if ($result) $trained++;
        }
        
        return $trained;
    }
    
    /**
     * Train conversation patterns
     */
    private function trainConversationPatterns() {
        $patternData = [
            // Greeting variations
            ['question' => 'Hello', 'answer' => 'Xin chào! Tôi là AI Chatbot của Linh2Store. Tôi có thể giúp bạn tìm sản phẩm mỹ phẩm, kiểm tra đơn hàng, hoặc trả lời câu hỏi. Bạn cần hỗ trợ gì?'],
            ['question' => 'Hi', 'answer' => 'Chào bạn! Tôi có thể giúp bạn tìm sản phẩm, tư vấn thương hiệu, hoặc hỗ trợ đơn hàng. Bạn muốn tìm gì?'],
            ['question' => 'Chào bạn', 'answer' => 'Chào bạn! Rất vui được gặp bạn. Tôi là trợ lý AI của Linh2Store, chuyên về mỹ phẩm cao cấp. Bạn cần tư vấn gì?'],
            
            // Product search variations
            ['question' => 'Có son gì không', 'answer' => 'Chúng tôi có rất nhiều loại son môi từ các thương hiệu cao cấp như MAC, Dior, Chanel, YSL, NARS. Bạn thích màu gì? Đỏ, hồng, nude, hay màu khác?'],
            ['question' => 'Son đỏ', 'answer' => 'Son môi màu đỏ rất đa dạng! Chúng tôi có MAC Ruby Woo (đỏ cổ điển), Dior 999 (đỏ tươi), Chanel Rouge Allure (đỏ sang trọng), YSL Rouge Pur Couture (đỏ quyến rũ). Bạn thích tông đỏ nào?'],
            ['question' => 'Kem nền', 'answer' => 'Kem nền chúng tôi có từ các thương hiệu cao cấp: MAC Studio Fix (che phủ tốt), Dior Forever (bền lâu), Chanel Vitalumière (tự nhiên), YSL All Hours (matte). Bạn cần tông màu gì?'],
            
            // Brand questions
            ['question' => 'MAC có gì', 'answer' => 'MAC nổi tiếng với son môi matte như Ruby Woo, Velvet Teddy, Retro Matte. Kem nền Studio Fix che phủ tốt. Phấn mắt đa dạng màu sắc. Được yêu thích bởi makeup artist chuyên nghiệp.'],
            ['question' => 'Dior có gì', 'answer' => 'Dior là thương hiệu luxury với son Rouge Dior (màu đỏ 999 nổi tiếng), kem nền Forever bền lâu, mascara Diorshow cho lông mi dài. Thiết kế sang trọng, chất lượng cao cấp.'],
            
            // Price questions
            ['question' => 'Giá bao nhiêu', 'answer' => 'Giá sản phẩm dao động từ 200k-2tr tùy thương hiệu và loại sản phẩm. Son môi: 200k-800k, kem nền: 400k-1.2tr, phấn mắt: 300k-1tr. Bạn quan tâm sản phẩm nào cụ thể?'],
            ['question' => 'Có rẻ không', 'answer' => 'Chúng tôi cam kết giá tốt nhất thị trường với sản phẩm chính hãng 100%. Thường xuyên có khuyến mãi giảm giá 10-20%. Miễn phí ship từ 500k. Bạn muốn xem sản phẩm nào?'],
            
            // Help questions
            ['question' => 'Giúp tôi', 'answer' => 'Tôi sẵn sàng giúp bạn! Bạn cần hỗ trợ gì? Tìm sản phẩm, kiểm tra đơn hàng, tư vấn thương hiệu, hay hướng dẫn thanh toán?'],
            ['question' => 'Không biết', 'answer' => 'Không sao! Tôi có thể giúp bạn tìm hiểu về mỹ phẩm. Bạn có thể bắt đầu bằng cách nói về sở thích màu sắc, thương hiệu yêu thích, hoặc mục đích sử dụng. Tôi sẽ tư vấn phù hợp!']
        ];
        
        $trained = 0;
        foreach ($patternData as $data) {
            $result = $this->training->trainWithConversation(
                $data['question'],
                $data['answer'],
                ['auto_training' => true, 'category' => 'conversation_pattern', 'source' => 'conversation_patterns']
            );
            if ($result) $trained++;
        }
        
        return $trained;
    }
    
    /**
     * Get training statistics
     */
    public function getTrainingStats() {
        return $this->training->getTrainingStats();
    }
}
?>
