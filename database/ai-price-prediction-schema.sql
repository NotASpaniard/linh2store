-- AI Price Prediction System Schema
-- Linh2Store - Website bán son môi & mỹ phẩm cao cấp

-- Bảng lưu trữ dữ liệu giá lịch sử
CREATE TABLE IF NOT EXISTS price_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    price_type ENUM('original', 'sale', 'discount', 'promotion') DEFAULT 'original',
    start_date DATE NOT NULL,
    end_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_id (product_id),
    INDEX idx_start_date (start_date),
    INDEX idx_end_date (end_date),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng lưu trữ kết quả dự đoán giá
CREATE TABLE IF NOT EXISTS price_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    predicted_price DECIMAL(10,2) NOT NULL,
    confidence_score DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
    prediction_type ENUM('short_term', 'medium_term', 'long_term') NOT NULL,
    prediction_horizon_days INT NOT NULL,
    algorithm_version VARCHAR(50) DEFAULT 'v1.0',
    features_used JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    INDEX idx_product_id (product_id),
    INDEX idx_predicted_price (predicted_price),
    INDEX idx_confidence_score (confidence_score),
    INDEX idx_prediction_type (prediction_type),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng lưu trữ dữ liệu thị trường
CREATE TABLE IF NOT EXISTS market_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    market_index DECIMAL(10,4),
    inflation_rate DECIMAL(5,4),
    exchange_rate DECIMAL(10,4),
    commodity_prices JSON,
    economic_indicators JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (date),
    INDEX idx_created_at (created_at)
);

-- Bảng lưu trữ cấu hình AI Price Prediction
CREATE TABLE IF NOT EXISTS ai_price_config (
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

-- Bảng lưu trữ lịch sử training AI Price Prediction
CREATE TABLE IF NOT EXISTS ai_price_training_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    training_type ENUM('price_prediction', 'market_analysis', 'trend_analysis') NOT NULL,
    dataset_size INT NOT NULL,
    accuracy_score DECIMAL(5,4),
    mae_score DECIMAL(10,4),
    rmse_score DECIMAL(10,4),
    mape_score DECIMAL(5,4),
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

-- Insert default AI Price Prediction configuration
INSERT INTO ai_price_config (config_key, config_value, config_type, description) VALUES
('prediction_horizon_short', '7', 'integer', 'Tầm nhìn dự đoán ngắn hạn (ngày)'),
('prediction_horizon_medium', '30', 'integer', 'Tầm nhìn dự đoán trung hạn (ngày)'),
('prediction_horizon_long', '90', 'integer', 'Tầm nhìn dự đoán dài hạn (ngày)'),
('confidence_threshold', '0.7', 'float', 'Ngưỡng tin cậy tối thiểu cho dự đoán'),
('enable_market_analysis', 'true', 'boolean', 'Cho phép phân tích thị trường'),
('enable_trend_analysis', 'true', 'boolean', 'Cho phép phân tích xu hướng'),
('enable_seasonal_analysis', 'true', 'boolean', 'Cho phép phân tích theo mùa'),
('enable_competitor_analysis', 'true', 'boolean', 'Cho phép phân tích đối thủ'),
('price_volatility_threshold', '0.15', 'float', 'Ngưỡng biến động giá'),
('trend_strength_threshold', '0.6', 'float', 'Ngưỡng sức mạnh xu hướng'),
('seasonal_pattern_threshold', '0.5', 'float', 'Ngưỡng mẫu theo mùa'),
('competitor_weight', '0.3', 'float', 'Trọng số phân tích đối thủ'),
('market_weight', '0.2', 'float', 'Trọng số phân tích thị trường'),
('historical_weight', '0.5', 'float', 'Trọng số dữ liệu lịch sử'),
('enable_real_time_prediction', 'true', 'boolean', 'Cho phép dự đoán thời gian thực'),
('prediction_update_frequency', '24', 'integer', 'Tần suất cập nhật dự đoán (giờ)'),
('enable_price_alerts', 'true', 'boolean', 'Cho phép cảnh báo giá'),
('price_change_threshold', '0.1', 'float', 'Ngưỡng thay đổi giá để cảnh báo'),
('model_retrain_frequency', '168', 'integer', 'Tần suất retrain model (giờ)'),
('enable_ensemble_prediction', 'true', 'boolean', 'Cho phép dự đoán ensemble')
ON DUPLICATE KEY UPDATE 
config_value = VALUES(config_value),
updated_at = CURRENT_TIMESTAMP;
