-- AI Fraud Detection Schema
-- Linh2Store - Advanced AI Fraud Prevention

-- AI Fraud Alerts
CREATE TABLE IF NOT EXISTS ai_fraud_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type ENUM('payment', 'account', 'transaction', 'behavioral') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    customer_id INT NULL,
    order_id INT NULL,
    fraud_score DECIMAL(3,2) NOT NULL,
    risk_factors JSON,
    recommended_action ENUM('monitor', 'block', 'verify', 'approve') NOT NULL,
    is_resolved BOOLEAN DEFAULT FALSE,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_alert_type (alert_type),
    INDEX idx_severity (severity),
    INDEX idx_fraud_score (fraud_score)
);

-- AI Payment Fraud Detection
CREATE TABLE IF NOT EXISTS ai_payment_fraud (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(100) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    fraud_probability DECIMAL(3,2) NOT NULL,
    risk_indicators JSON,
    device_fingerprint VARCHAR(255),
    ip_address VARCHAR(45),
    location_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transaction (transaction_id),
    INDEX idx_fraud_probability (fraud_probability)
);

-- AI Account Security
CREATE TABLE IF NOT EXISTS ai_account_security (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    security_score DECIMAL(3,2) NOT NULL,
    login_anomalies JSON,
    device_trust_score DECIMAL(3,2) DEFAULT 0.00,
    location_anomalies JSON,
    behavior_patterns JSON,
    last_analysis TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_security (customer_id),
    INDEX idx_security_score (security_score)
);

-- AI Transaction Monitoring
CREATE TABLE IF NOT EXISTS ai_transaction_monitoring (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    monitoring_score DECIMAL(3,2) NOT NULL,
    velocity_checks JSON,
    pattern_analysis JSON,
    geo_verification JSON,
    device_analysis JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_monitoring (order_id),
    INDEX idx_monitoring_score (monitoring_score)
);
