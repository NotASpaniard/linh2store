-- AI Sentiment Analysis System Schema
-- Linh2Store - Website bán son môi & mỹ phẩm cao cấp

-- Bảng lưu trữ kết quả phân tích cảm xúc
CREATE TABLE IF NOT EXISTS sentiment_analysis_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT,
    user_id INT,
    text_content TEXT NOT NULL,
    sentiment_score DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
    sentiment_label ENUM('positive', 'negative', 'neutral', 'mixed') NOT NULL,
    confidence_score DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
    emotion_scores JSON,
    keywords JSON,
    processing_time_ms INT,
    model_version VARCHAR(50) DEFAULT 'v1.0',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_review_id (review_id),
    INDEX idx_user_id (user_id),
    INDEX idx_sentiment_label (sentiment_label),
    INDEX idx_sentiment_score (sentiment_score),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng lưu trữ từ khóa cảm xúc
CREATE TABLE IF NOT EXISTS sentiment_keywords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(100) NOT NULL,
    sentiment_type ENUM('positive', 'negative', 'neutral') NOT NULL,
    weight DECIMAL(3,2) NOT NULL DEFAULT 1.00,
    category VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_keyword (keyword),
    INDEX idx_sentiment_type (sentiment_type),
    INDEX idx_category (category),
    INDEX idx_is_active (is_active),
    UNIQUE KEY unique_keyword (keyword, sentiment_type)
);

-- Bảng lưu trữ cấu hình AI Sentiment Analysis
CREATE TABLE IF NOT EXISTS ai_sentiment_config (
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

-- Bảng lưu trữ lịch sử training AI Sentiment Analysis
CREATE TABLE IF NOT EXISTS ai_sentiment_training_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    training_type ENUM('sentiment_classification', 'emotion_detection', 'keyword_extraction') NOT NULL,
    dataset_size INT NOT NULL,
    accuracy_score DECIMAL(5,4),
    precision_score DECIMAL(5,4),
    recall_score DECIMAL(5,4),
    f1_score DECIMAL(5,4),
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

-- Insert default AI Sentiment Analysis configuration
INSERT INTO ai_sentiment_config (config_key, config_value, config_type, description) VALUES
('sentiment_threshold_positive', '0.6', 'float', 'Ngưỡng phân loại cảm xúc tích cực'),
('sentiment_threshold_negative', '-0.6', 'float', 'Ngưỡng phân loại cảm xúc tiêu cực'),
('confidence_threshold', '0.7', 'float', 'Ngưỡng tin cậy tối thiểu cho phân tích'),
('enable_emotion_detection', 'true', 'boolean', 'Cho phép nhận dạng cảm xúc'),
('enable_keyword_extraction', 'true', 'boolean', 'Cho phép trích xuất từ khóa'),
('enable_real_time_analysis', 'true', 'boolean', 'Cho phép phân tích thời gian thực'),
('max_text_length', '1000', 'integer', 'Độ dài tối đa của văn bản (ký tự)'),
('emotion_categories', '["joy", "sadness", "anger", "fear", "surprise", "disgust"]', 'json', 'Các loại cảm xúc được nhận dạng'),
('positive_keywords_weight', '1.0', 'float', 'Trọng số từ khóa tích cực'),
('negative_keywords_weight', '1.0', 'float', 'Trọng số từ khóa tiêu cực'),
('neutral_keywords_weight', '0.5', 'float', 'Trọng số từ khóa trung tính'),
('enable_sentiment_tracking', 'true', 'boolean', 'Cho phép theo dõi xu hướng cảm xúc'),
('sentiment_aggregation_window', '7', 'integer', 'Cửa sổ thời gian tổng hợp cảm xúc (ngày)'),
('enable_sentiment_alerts', 'true', 'boolean', 'Cho phép cảnh báo cảm xúc'),
('negative_sentiment_threshold', '0.3', 'float', 'Ngưỡng cảnh báo cảm xúc tiêu cực'),
('model_retrain_frequency', '168', 'integer', 'Tần suất retrain model (giờ)')
ON DUPLICATE KEY UPDATE 
config_value = VALUES(config_value),
updated_at = CURRENT_TIMESTAMP;

-- Insert sample sentiment keywords
INSERT INTO sentiment_keywords (keyword, sentiment_type, weight, category) VALUES
-- Positive keywords
('tuyệt vời', 'positive', 1.0, 'quality'),
('xuất sắc', 'positive', 1.0, 'quality'),
('hoàn hảo', 'positive', 1.0, 'quality'),
('tốt', 'positive', 0.8, 'quality'),
('hài lòng', 'positive', 0.9, 'satisfaction'),
('thích', 'positive', 0.7, 'preference'),
('yêu', 'positive', 1.0, 'preference'),
('đẹp', 'positive', 0.8, 'appearance'),
('chất lượng', 'positive', 0.9, 'quality'),
('giá trị', 'positive', 0.8, 'value'),
('khuyến nghị', 'positive', 0.9, 'recommendation'),
('mua lại', 'positive', 0.8, 'loyalty'),
('nhanh', 'positive', 0.7, 'service'),
('giao hàng', 'positive', 0.6, 'service'),
('đóng gói', 'positive', 0.6, 'service'),
('nhân viên', 'positive', 0.7, 'service'),
('hỗ trợ', 'positive', 0.8, 'service'),
('tư vấn', 'positive', 0.8, 'service'),
('chuyên nghiệp', 'positive', 0.9, 'service'),
('nhiệt tình', 'positive', 0.8, 'service'),

-- Negative keywords
('tệ', 'negative', 1.0, 'quality'),
('kém', 'negative', 0.9, 'quality'),
('không tốt', 'negative', 0.8, 'quality'),
('thất vọng', 'negative', 0.9, 'satisfaction'),
('không hài lòng', 'negative', 0.9, 'satisfaction'),
('ghét', 'negative', 1.0, 'preference'),
('xấu', 'negative', 0.8, 'appearance'),
('kém chất lượng', 'negative', 0.9, 'quality'),
('đắt', 'negative', 0.7, 'price'),
('không đáng', 'negative', 0.8, 'value'),
('không khuyến nghị', 'negative', 0.9, 'recommendation'),
('chậm', 'negative', 0.7, 'service'),
('giao hàng chậm', 'negative', 0.8, 'service'),
('đóng gói kém', 'negative', 0.7, 'service'),
('nhân viên kém', 'negative', 0.8, 'service'),
('không hỗ trợ', 'negative', 0.8, 'service'),
('tư vấn kém', 'negative', 0.8, 'service'),
('không chuyên nghiệp', 'negative', 0.9, 'service'),
('lạnh nhạt', 'negative', 0.7, 'service'),
('lừa đảo', 'negative', 1.0, 'fraud'),

-- Neutral keywords
('bình thường', 'neutral', 0.5, 'quality'),
('ổn', 'neutral', 0.5, 'quality'),
('chấp nhận được', 'neutral', 0.5, 'quality'),
('không có gì đặc biệt', 'neutral', 0.5, 'quality'),
('trung bình', 'neutral', 0.5, 'quality'),
('có thể', 'neutral', 0.5, 'possibility'),
('có lẽ', 'neutral', 0.5, 'possibility'),
('có thể là', 'neutral', 0.5, 'possibility'),
('không chắc', 'neutral', 0.5, 'uncertainty'),
('không biết', 'neutral', 0.5, 'uncertainty')
ON DUPLICATE KEY UPDATE 
weight = VALUES(weight),
updated_at = CURRENT_TIMESTAMP;
