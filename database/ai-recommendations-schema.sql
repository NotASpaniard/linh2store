-- AI Recommendations System Schema
-- Linh2Store - Website bán son môi & mỹ phẩm cao cấp

-- Bảng lưu trữ dữ liệu hành vi người dùng
CREATE TABLE IF NOT EXISTS user_behavior (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    action_type ENUM('view', 'add_to_cart', 'purchase', 'like', 'dislike') NOT NULL,
    session_id VARCHAR(255),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_product_id (product_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng lưu trữ kết quả AI recommendations
CREATE TABLE IF NOT EXISTS ai_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    recommendation_type ENUM('collaborative', 'content_based', 'hybrid', 'trending', 'similar') NOT NULL,
    score DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
    algorithm_version VARCHAR(50) DEFAULT 'v1.0',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_product_id (product_id),
    INDEX idx_recommendation_type (recommendation_type),
    INDEX idx_score (score),
    INDEX idx_created_at (created_at),
    UNIQUE KEY unique_user_product (user_id, product_id, recommendation_type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng lưu trữ dữ liệu sản phẩm cho AI
CREATE TABLE IF NOT EXISTS product_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    feature_name VARCHAR(100) NOT NULL,
    feature_value TEXT,
    feature_type ENUM('text', 'numeric', 'categorical', 'boolean') NOT NULL,
    importance_score DECIMAL(3,2) DEFAULT 1.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product_id (product_id),
    INDEX idx_feature_name (feature_name),
    INDEX idx_feature_type (feature_type),
    UNIQUE KEY unique_product_feature (product_id, feature_name),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng lưu trữ ma trận tương tự sản phẩm
CREATE TABLE IF NOT EXISTS product_similarity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_a_id INT NOT NULL,
    product_b_id INT NOT NULL,
    similarity_score DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
    similarity_type ENUM('content', 'collaborative', 'hybrid') NOT NULL,
    algorithm_version VARCHAR(50) DEFAULT 'v1.0',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product_a (product_a_id),
    INDEX idx_product_b (product_b_id),
    INDEX idx_similarity_score (similarity_score),
    INDEX idx_similarity_type (similarity_type),
    UNIQUE KEY unique_product_similarity (product_a_id, product_b_id, similarity_type),
    FOREIGN KEY (product_a_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (product_b_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng lưu trữ lịch sử training AI
CREATE TABLE IF NOT EXISTS ai_training_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    algorithm_name VARCHAR(100) NOT NULL,
    training_data_size INT NOT NULL,
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
    INDEX idx_algorithm_name (algorithm_name),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- Bảng lưu trữ cấu hình AI
CREATE TABLE IF NOT EXISTS ai_config (
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

-- Insert default AI configuration
INSERT INTO ai_config (config_key, config_value, config_type, description) VALUES
('recommendation_algorithm', 'hybrid', 'string', 'Algorithm used for recommendations (collaborative, content_based, hybrid)'),
('min_similarity_threshold', '0.3', 'float', 'Minimum similarity score for product recommendations'),
('max_recommendations_per_user', '10', 'integer', 'Maximum number of recommendations per user'),
('recommendation_expiry_days', '7', 'integer', 'Number of days before recommendations expire'),
('enable_content_based', 'true', 'boolean', 'Enable content-based filtering'),
('enable_collaborative', 'true', 'boolean', 'Enable collaborative filtering'),
('enable_trending', 'true', 'boolean', 'Enable trending products'),
('trending_window_days', '30', 'integer', 'Window for calculating trending products'),
('feature_weights', '{"brand": 0.3, "category": 0.4, "price": 0.2, "rating": 0.1}', 'json', 'Weights for different product features'),
('model_retrain_frequency', '24', 'integer', 'Hours between model retraining'),
('enable_real_time_updates', 'true', 'boolean', 'Enable real-time recommendation updates')
ON DUPLICATE KEY UPDATE 
config_value = VALUES(config_value),
updated_at = CURRENT_TIMESTAMP;
