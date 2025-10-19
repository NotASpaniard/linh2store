-- AI Customer Behavior Analysis Schema
-- Linh2Store - AI Customer Behavior Analysis Database Schema

-- Customer segments table
CREATE TABLE IF NOT EXISTS ai_customer_segments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    segment_type ENUM('VIP', 'Loyal', 'Regular', 'New', 'At_Risk') NOT NULL,
    segment_score DECIMAL(3,2) NOT NULL,
    segment_factors TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_segment (user_id, segment_type)
);

-- Churn predictions table
CREATE TABLE IF NOT EXISTS ai_churn_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    churn_probability DECIMAL(3,2) NOT NULL,
    risk_factors TEXT,
    recommended_actions TEXT,
    prediction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_churn (user_id, churn_probability)
);

-- Lifetime value predictions table
CREATE TABLE IF NOT EXISTS ai_lifetime_value (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    predicted_clv DECIMAL(12,2) NOT NULL,
    confidence_score DECIMAL(3,2) NOT NULL,
    factors TEXT,
    prediction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_clv (user_id, predicted_clv)
);

-- Purchase patterns table
CREATE TABLE IF NOT EXISTS ai_purchase_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pattern_type ENUM('frequency', 'seasonal', 'price_sensitivity', 'brand_loyalty') NOT NULL,
    pattern_value DECIMAL(5,4) NOT NULL,
    pattern_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_pattern (user_id, pattern_type)
);

-- Personalization scores table
CREATE TABLE IF NOT EXISTS ai_personalization_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    personalization_score DECIMAL(3,2) NOT NULL,
    preferences TEXT,
    recommendations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_personalization (user_id, personalization_score)
);

-- Engagement metrics table
CREATE TABLE IF NOT EXISTS ai_engagement_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    engagement_score DECIMAL(3,2) NOT NULL,
    last_activity_date DATE,
    activity_frequency INT NOT NULL,
    preferred_channels TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_engagement (user_id, engagement_score)
);

-- Anomaly detection table
CREATE TABLE IF NOT EXISTS ai_anomaly_detection (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    anomaly_type ENUM('unusual_purchase', 'suspicious_activity', 'behavior_change') NOT NULL,
    anomaly_score DECIMAL(3,2) NOT NULL,
    description TEXT,
    is_resolved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_anomaly (user_id, anomaly_type, is_resolved)
);