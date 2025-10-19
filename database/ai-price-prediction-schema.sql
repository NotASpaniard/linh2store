-- AI Price Prediction System Schema
-- Linh2Store - AI Price Prediction Database Schema

-- Price configuration table
CREATE TABLE IF NOT EXISTS ai_price_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Price history table
CREATE TABLE IF NOT EXISTS ai_price_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    date_recorded DATE NOT NULL,
    source VARCHAR(50) DEFAULT 'manual',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_date (product_id, date_recorded),
    INDEX idx_date (date_recorded)
);

-- Market trends table
CREATE TABLE IF NOT EXISTS ai_market_trends (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trend_type VARCHAR(100) NOT NULL,
    trend_value DECIMAL(5,4) NOT NULL,
    date_recorded DATE NOT NULL,
    confidence DECIMAL(3,2) DEFAULT 0.5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_trend_date (trend_type, date_recorded)
);

-- Price predictions table
CREATE TABLE IF NOT EXISTS ai_price_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    predicted_date DATE NOT NULL,
    predicted_price DECIMAL(10,2) NOT NULL,
    confidence_score DECIMAL(3,2) NOT NULL,
    model_used VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_prediction (product_id, predicted_date)
);

-- Competitor prices table
CREATE TABLE IF NOT EXISTS ai_competitor_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    competitor_name VARCHAR(100) NOT NULL,
    competitor_price DECIMAL(10,2) NOT NULL,
    date_recorded DATE NOT NULL,
    source_url TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_competitor (product_id, competitor_name)
);

-- Seasonal patterns table
CREATE TABLE IF NOT EXISTS ai_seasonal_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    season VARCHAR(20) NOT NULL,
    price_multiplier DECIMAL(3,2) NOT NULL,
    confidence DECIMAL(3,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_season (product_id, season)
);

-- Price alerts table
CREATE TABLE IF NOT EXISTS ai_price_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    alert_type ENUM('price_drop', 'price_increase', 'target_reached') NOT NULL,
    threshold_price DECIMAL(10,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_active (product_id, is_active)
);