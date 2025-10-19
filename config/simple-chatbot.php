<?php
/**
 * Linh2Store Chatbot
 * Linh2Store - Chatbot đơn giản và hiệu quả
 */

class Linh2StoreChatbot {
    
    /**
     * Xử lý tin nhắn đơn giản
     */
    public function processMessage($message) {
        $message = strtolower(trim($message));
        
        // FAQ cơ bản
        if (strpos($message, 'giá') !== false || strpos($message, 'price') !== false) {
            return "💰 <strong>Thông tin giá:</strong>\n\n• Son môi: 200k - 800k\n• Kem nền: 300k - 1.2tr\n• Phấn mắt: 150k - 600k\n• Mascara: 100k - 400k\n\n💡 <em>Giá có thể thay đổi theo khuyến mãi</em>";
        }
        
        if (strpos($message, 'ship') !== false || strpos($message, 'giao hàng') !== false) {
            return "🚚 <strong>Thông tin giao hàng:</strong>\n\n• Phí ship: 30k toàn quốc\n• Miễn phí ship đơn từ 500k\n• Thời gian: 1-3 ngày (TP.HCM), 3-5 ngày (tỉnh khác)\n• Hỗ trợ COD toàn quốc";
        }
        
        if (strpos($message, 'đổi trả') !== false || strpos($message, 'hoàn') !== false) {
            return "🔄 <strong>Chính sách đổi trả:</strong>\n\n• Đổi trả trong 7 ngày\n• Sản phẩm còn nguyên vẹn\n• Có hóa đơn mua hàng\n• Liên hệ: 0123456789";
        }
        
        if (strpos($message, 'khuyến mãi') !== false || strpos($message, 'sale') !== false) {
            return "🎉 <strong>Khuyến mãi hiện tại:</strong>\n\n• Giảm 20% đơn từ 500k\n• Tặng kèm sample cho đơn từ 300k\n• Miễn phí ship đơn từ 500k\n• Tích điểm thưởng cho thành viên";
        }
        
        if (strpos($message, 'son') !== false || strpos($message, 'môi') !== false) {
            return "💄 <strong>Son môi có sẵn:</strong>\n\n• MAC: Ruby Woo, Velvet Teddy, Chili\n• Dior: 999, 772, 772\n• Chanel: 99, 91, 92\n• YSL: 12, 13, 14\n\n🔍 <em>Xem chi tiết tại trang sản phẩm</em>";
        }
        
        if (strpos($message, 'kem nền') !== false || strpos($message, 'foundation') !== false) {
            return "🧴 <strong>Kem nền có sẵn:</strong>\n\n• MAC Studio Fix Fluid\n• Dior Forever Foundation\n• Chanel Vitalumière\n• YSL All Hours Foundation\n\n🔍 <em>Xem chi tiết tại trang sản phẩm</em>";
        }
        
        if (strpos($message, 'đặt hàng') !== false || strpos($message, 'mua') !== false) {
            return "🛒 <strong>Hướng dẫn đặt hàng:</strong>\n\n1. Chọn sản phẩm\n2. Thêm vào giỏ hàng\n3. Điền thông tin\n4. Chọn phương thức thanh toán\n5. Xác nhận đơn hàng\n\n💡 <em>Hỗ trợ: 0123456789</em>";
        }
        
        if (strpos($message, 'thanh toán') !== false || strpos($message, 'payment') !== false) {
            return "💳 <strong>Phương thức thanh toán:</strong>\n\n• COD (Thanh toán khi nhận hàng)\n• Chuyển khoản ngân hàng\n• VNPay\n• MoMo\n• Ví điện tử\n\n💡 <em>Hỗ trợ: 0123456789</em>";
        }
        
        if (strpos($message, 'liên hệ') !== false || strpos($message, 'hotline') !== false) {
            return "📞 <strong>Thông tin liên hệ:</strong>\n\n• Hotline: 0123456789\n• Email: support@linh2store.com\n• Facebook: Linh2Store\n• Instagram: @linh2store\n• Địa chỉ: 123 Nguyễn Văn A, Q1, TP.HCM";
        }
        
        if (strpos($message, 'giờ') !== false || strpos($message, 'thời gian') !== false) {
            return "⏰ <strong>Thời gian hoạt động:</strong>\n\n• Thứ 2 - Thứ 6: 8:00 - 22:00\n• Thứ 7 - Chủ nhật: 9:00 - 21:00\n• Hỗ trợ online 24/7\n• Hotline: 0123456789";
        }
        
        // Mặc định
        return "👋 <strong>Xin chào! Tôi có thể giúp bạn:</strong>\n\n• 💰 Thông tin giá sản phẩm\n• 🚚 Thông tin giao hàng\n• 🔄 Chính sách đổi trả\n• 🎉 Khuyến mãi hiện tại\n• 🛒 Hướng dẫn đặt hàng\n• 📞 Thông tin liên hệ\n\n💡 <em>Gõ từ khóa để được hỗ trợ nhanh!</em>";
    }
}
