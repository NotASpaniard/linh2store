-- AI Chatbot System Schema
-- Linh2Store - Website bán son môi & mỹ phẩm cao cấp

-- Bảng lưu trữ cuộc trò chuyện
CREATE TABLE IF NOT EXISTS chat_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    session_id VARCHAR(255) NOT NULL,
    status ENUM('active', 'closed', 'archived') DEFAULT 'active',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),
    INDEX idx_status (status),
    INDEX idx_started_at (started_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng lưu trữ tin nhắn
CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_type ENUM('user', 'bot', 'admin') NOT NULL,
    message_text TEXT NOT NULL,
    message_type ENUM('text', 'image', 'file', 'product', 'order', 'system') DEFAULT 'text',
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_sender_type (sender_type),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (conversation_id) REFERENCES chat_conversations(id) ON DELETE CASCADE
);

-- Bảng lưu trữ kiến thức AI
CREATE TABLE IF NOT EXISTS ai_knowledge_base (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(100) NOT NULL,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    keywords TEXT,
    priority INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_priority (priority),
    INDEX idx_is_active (is_active),
    FULLTEXT KEY ft_question (question),
    FULLTEXT KEY ft_answer (answer),
    FULLTEXT KEY ft_keywords (keywords)
);

-- Bảng lưu trữ phản hồi người dùng
CREATE TABLE IF NOT EXISTS chat_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    message_id INT NOT NULL,
    user_id INT,
    feedback_type ENUM('helpful', 'not_helpful', 'inappropriate') NOT NULL,
    feedback_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_message_id (message_id),
    INDEX idx_user_id (user_id),
    INDEX idx_feedback_type (feedback_type),
    FOREIGN KEY (conversation_id) REFERENCES chat_conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (message_id) REFERENCES chat_messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng lưu trữ cấu hình chatbot
CREATE TABLE IF NOT EXISTS chatbot_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT,
    config_type ENUM('string', 'integer', 'float', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_config_key (config_key),
    INDEX idx_is_active (is_active)
);

-- Bảng lưu trữ lịch sử training chatbot
CREATE TABLE IF NOT EXISTS chatbot_training_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    training_type ENUM('knowledge_base', 'conversation', 'feedback') NOT NULL,
    data_size INT NOT NULL,
    accuracy_score DECIMAL(5,4),
    training_duration_seconds INT,
    model_version VARCHAR(50),
    status ENUM('running', 'completed', 'failed') DEFAULT 'running',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    INDEX idx_training_type (training_type),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Insert default chatbot configuration
INSERT INTO chatbot_config (config_key, config_value, config_type, description) VALUES
('bot_name', 'Linh2Store Assistant', 'string', 'Tên của chatbot'),
('welcome_message', 'Xin chào! Tôi là trợ lý ảo của Linh2Store. Tôi có thể giúp bạn tìm sản phẩm, kiểm tra đơn hàng, hoặc trả lời câu hỏi. Bạn cần hỗ trợ gì?', 'string', 'Tin nhắn chào mừng'),
('max_conversation_length', '50', 'integer', 'Số tin nhắn tối đa trong một cuộc trò chuyện'),
('response_delay_ms', '1000', 'integer', 'Độ trễ phản hồi (milliseconds)'),
('enable_product_search', 'true', 'boolean', 'Cho phép tìm kiếm sản phẩm'),
('enable_order_tracking', 'true', 'boolean', 'Cho phép theo dõi đơn hàng'),
('enable_feedback', 'true', 'boolean', 'Cho phép phản hồi'),
('auto_close_inactive_minutes', '30', 'integer', 'Tự động đóng cuộc trò chuyện không hoạt động (phút)'),
('enable_escalation', 'true', 'boolean', 'Cho phép chuyển tiếp cho nhân viên'),
('escalation_keywords', '["nhân viên", "quản lý", "giám đốc", "khiếu nại"]', 'json', 'Từ khóa chuyển tiếp'),
('knowledge_base_version', '1.0', 'string', 'Phiên bản cơ sở kiến thức'),
('ai_model_version', '1.0', 'string', 'Phiên bản mô hình AI')
ON DUPLICATE KEY UPDATE 
config_value = VALUES(config_value),
updated_at = CURRENT_TIMESTAMP;

-- Insert sample knowledge base
INSERT INTO ai_knowledge_base (category, question, answer, keywords, priority) VALUES
('sản phẩm', 'Bạn có những loại son môi nào?', 'Chúng tôi có đầy đủ các loại son môi: son lì, son bóng, son matte, son satin, son dưỡng môi. Tất cả đều là hàng chính hãng từ các thương hiệu nổi tiếng.', 'son môi, loại son, chủng loại', 5),
('sản phẩm', 'Son môi có bao nhiêu màu?', 'Chúng tôi có hơn 50 màu son môi khác nhau, từ màu nude nhẹ nhàng đến màu đỏ đậm quyến rũ. Bạn có thể xem tất cả màu sắc tại trang sản phẩm.', 'màu son, màu sắc, tông màu', 5),
('đơn hàng', 'Làm sao để theo dõi đơn hàng?', 'Bạn có thể theo dõi đơn hàng bằng cách đăng nhập vào tài khoản và vào mục "Đơn hàng của tôi". Hoặc bạn có thể cung cấp mã đơn hàng để tôi kiểm tra giúp.', 'theo dõi đơn hàng, kiểm tra đơn hàng, mã đơn hàng', 5),
('đơn hàng', 'Thời gian giao hàng bao lâu?', 'Thời gian giao hàng từ 1-3 ngày làm việc trong nội thành, 3-7 ngày làm việc cho các tỉnh thành khác. Chúng tôi sẽ gửi thông báo khi đơn hàng được giao.', 'thời gian giao hàng, bao lâu, ship', 5),
('thanh toán', 'Có những phương thức thanh toán nào?', 'Chúng tôi hỗ trợ thanh toán bằng thẻ tín dụng, chuyển khoản ngân hàng, ví điện tử (MoMo, ZaloPay), và thanh toán khi nhận hàng (COD).', 'thanh toán, phương thức thanh toán, COD', 5),
('chính sách', 'Có được đổi trả không?', 'Có, chúng tôi hỗ trợ đổi trả trong vòng 7 ngày kể từ khi nhận hàng với điều kiện sản phẩm còn nguyên vẹn, chưa sử dụng.', 'đổi trả, hoàn tiền, chính sách', 5),
('chính sách', 'Phí ship bao nhiêu?', 'Miễn phí ship cho đơn hàng từ 500.000đ trở lên. Đơn hàng dưới 500.000đ sẽ tính phí ship 30.000đ.', 'phí ship, miễn phí ship, vận chuyển', 5),
('hỗ trợ', 'Làm sao liên hệ với bạn?', 'Bạn có thể liên hệ với chúng tôi qua hotline 1900-xxxx, email support@linh2store.com, hoặc chat trực tiếp với tôi ngay bây giờ!', 'liên hệ, hotline, email, hỗ trợ', 5),
('thương hiệu', 'Có những thương hiệu nào?', 'Chúng tôi cung cấp các thương hiệu mỹ phẩm nổi tiếng như MAC, Chanel, Dior, YSL, Lancôme, Estée Lauder và nhiều thương hiệu khác.', 'thương hiệu, brand, nhãn hiệu', 5),
('khuyến mãi', 'Có chương trình khuyến mãi không?', 'Có, chúng tôi thường xuyên có các chương trình khuyến mãi như giảm giá, tặng quà, tích điểm đổi thưởng. Bạn có thể xem chi tiết tại trang chủ.', 'khuyến mãi, giảm giá, tặng quà', 5)
ON DUPLICATE KEY UPDATE 
answer = VALUES(answer),
updated_at = CURRENT_TIMESTAMP;
