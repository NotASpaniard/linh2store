-- AI Image Recognition System Schema
-- Linh2Store - Website bán son môi & mỹ phẩm cao cấp

-- Bảng lưu trữ kết quả nhận dạng hình ảnh
CREATE TABLE IF NOT EXISTS image_recognition_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    image_path VARCHAR(500) NOT NULL,
    image_hash VARCHAR(64) NOT NULL,
    recognition_type ENUM('product', 'brand', 'color', 'text', 'object') NOT NULL,
    confidence_score DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
    recognized_data JSON,
    processing_time_ms INT,
    model_version VARCHAR(50) DEFAULT 'v1.0',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_image_hash (image_hash),
    INDEX idx_recognition_type (recognition_type),
    INDEX idx_confidence_score (confidence_score),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Bảng lưu trữ dữ liệu training cho AI
CREATE TABLE IF NOT EXISTS ai_training_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    image_path VARCHAR(500) NOT NULL,
    image_hash VARCHAR(64) NOT NULL,
    image_type ENUM('product', 'brand', 'color', 'text', 'object') NOT NULL,
    labels JSON,
    features JSON,
    is_verified BOOLEAN DEFAULT FALSE,
    verified_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product_id (product_id),
    INDEX idx_image_hash (image_hash),
    INDEX idx_image_type (image_type),
    INDEX idx_is_verified (is_verified),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Bảng lưu trữ kết quả so sánh hình ảnh
CREATE TABLE IF NOT EXISTS image_similarity_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_image_id INT NOT NULL,
    target_image_id INT NOT NULL,
    similarity_score DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
    similarity_type ENUM('visual', 'color', 'texture', 'shape', 'hybrid') NOT NULL,
    algorithm_version VARCHAR(50) DEFAULT 'v1.0',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_source_image (source_image_id),
    INDEX idx_target_image (target_image_id),
    INDEX idx_similarity_score (similarity_score),
    INDEX idx_similarity_type (similarity_type),
    FOREIGN KEY (source_image_id) REFERENCES ai_training_images(id) ON DELETE CASCADE,
    FOREIGN KEY (target_image_id) REFERENCES ai_training_images(id) ON DELETE CASCADE
);

-- Bảng lưu trữ cấu hình AI Image Recognition
CREATE TABLE IF NOT EXISTS ai_image_config (
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

-- Bảng lưu trữ lịch sử training AI Image Recognition
CREATE TABLE IF NOT EXISTS ai_image_training_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    training_type ENUM('product_recognition', 'brand_recognition', 'color_recognition', 'text_recognition') NOT NULL,
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

-- Insert default AI Image Recognition configuration
INSERT INTO ai_image_config (config_key, config_value, config_type, description) VALUES
('recognition_confidence_threshold', '0.7', 'float', 'Ngưỡng tin cậy tối thiểu cho nhận dạng'),
('max_image_size_mb', '10', 'integer', 'Kích thước tối đa của hình ảnh (MB)'),
('supported_formats', '["jpg", "jpeg", "png", "gif", "webp"]', 'json', 'Các định dạng hình ảnh được hỗ trợ'),
('enable_product_recognition', 'true', 'boolean', 'Cho phép nhận dạng sản phẩm'),
('enable_brand_recognition', 'true', 'boolean', 'Cho phép nhận dạng thương hiệu'),
('enable_color_recognition', 'true', 'boolean', 'Cho phép nhận dạng màu sắc'),
('enable_text_recognition', 'true', 'boolean', 'Cho phép nhận dạng văn bản'),
('enable_object_recognition', 'true', 'boolean', 'Cho phép nhận dạng đối tượng'),
('similarity_threshold', '0.8', 'float', 'Ngưỡng tương tự cho tìm kiếm hình ảnh'),
('max_similarity_results', '10', 'integer', 'Số kết quả tương tự tối đa'),
('enable_auto_tagging', 'true', 'boolean', 'Cho phép gắn thẻ tự động'),
('enable_face_detection', 'false', 'boolean', 'Cho phép nhận dạng khuôn mặt'),
('model_retrain_frequency', '168', 'integer', 'Tần suất retrain model (giờ)'),
('enable_real_time_processing', 'true', 'boolean', 'Cho phép xử lý thời gian thực'),
('image_compression_quality', '85', 'integer', 'Chất lượng nén hình ảnh (1-100)')
ON DUPLICATE KEY UPDATE 
config_value = VALUES(config_value),
updated_at = CURRENT_TIMESTAMP;
