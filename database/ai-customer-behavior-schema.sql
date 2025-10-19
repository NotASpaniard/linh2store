-- AI Customer Behavior Analysis Schema
-- Linh2Store - Advanced Customer Behavior AI

-- AI Customer Segments
CREATE TABLE IF NOT EXISTS ai_customer_segments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    segment_name VARCHAR(100) NOT NULL,
    segment_description TEXT,
    criteria JSON NOT NULL,
    customer_count INT DEFAULT 0,
    avg_order_value DECIMAL(10,2) DEFAULT 0.00,
    avg_purchase_frequency DECIMAL(5,2) DEFAULT 0.00,
    churn_probability DECIMAL(3,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_segment_name (segment_name)
);

-- AI Customer Lifetime Value
CREATE TABLE IF NOT EXISTS ai_customer_lifetime_value (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    predicted_clv DECIMAL(10,2) NOT NULL,
    confidence_score DECIMAL(3,2) DEFAULT 0.00,
    calculation_method VARCHAR(50) DEFAULT 'rfm_analysis',
    last_calculation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_clv (customer_id),
    INDEX idx_predicted_clv (predicted_clv)
);

-- AI Customer Churn Predictions
CREATE TABLE IF NOT EXISTS ai_customer_churn_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    churn_probability DECIMAL(3,2) NOT NULL,
    risk_level ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    risk_factors JSON,
    recommended_actions JSON,
    prediction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_churn (customer_id),
    INDEX idx_risk_level (risk_level)
);

-- AI Customer Purchase Patterns
CREATE TABLE IF NOT EXISTS ai_customer_purchase_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    pattern_type ENUM('frequency', 'seasonal', 'trending', 'declining') NOT NULL,
    pattern_data JSON,
    confidence_score DECIMAL(3,2) DEFAULT 0.00,
    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_pattern (customer_id, pattern_type)
);

-- AI Customer Recommendations
CREATE TABLE IF NOT EXISTS ai_customer_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    recommendation_type ENUM('product', 'category', 'brand', 'price') NOT NULL,
    recommended_items JSON,
    priority_score DECIMAL(3,2) DEFAULT 0.00,
    reasoning TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_rec (customer_id),
    INDEX idx_priority (priority_score)
);

-- AI Customer Engagement Score
CREATE TABLE IF NOT EXISTS ai_customer_engagement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    engagement_score DECIMAL(3,2) NOT NULL,
    engagement_factors JSON,
    last_activity TIMESTAMP,
    score_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_engagement (customer_id),
    INDEX idx_engagement_score (engagement_score)
);
