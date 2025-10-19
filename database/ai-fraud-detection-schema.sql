-- AI Fraud Detection Schema
-- Linh2Store - AI Fraud Detection Database Schema

-- Fraud alerts table
CREATE TABLE IF NOT EXISTS ai_fraud_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alert_type ENUM('suspicious_payment', 'multiple_failed_attempts', 'unusual_location', 'rapid_successive_orders', 'account_takeover') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    description TEXT NOT NULL,
    fraud_score DECIMAL(3,2) NOT NULL,
    user_id INT NULL,
    transaction_id INT NULL,
    is_resolved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    INDEX idx_alert_severity (alert_type, severity, is_resolved)
);

-- Payment fraud analysis table
CREATE TABLE IF NOT EXISTS ai_payment_fraud (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    payment_method ENUM('credit_card', 'debit_card', 'bank_transfer', 'e_wallet', 'cryptocurrency') NOT NULL,
    fraud_probability DECIMAL(3,2) NOT NULL,
    risk_factors TEXT,
    is_blocked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transaction_fraud (transaction_id, fraud_probability)
);

-- Account security table
CREATE TABLE IF NOT EXISTS ai_account_security (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    security_score DECIMAL(3,2) NOT NULL,
    device_trust_score DECIMAL(3,2) NOT NULL,
    login_anomalies INT DEFAULT 0,
    last_analysis TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_security (user_id, security_score)
);

-- Transaction monitoring table
CREATE TABLE IF NOT EXISTS ai_transaction_monitoring (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    monitoring_score DECIMAL(3,2) NOT NULL,
    anomaly_detected BOOLEAN DEFAULT FALSE,
    risk_level ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transaction_monitoring (transaction_id, risk_level)
);

-- Geographic analysis table
CREATE TABLE IF NOT EXISTS ai_geographic_analysis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    country VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    is_suspicious BOOLEAN DEFAULT FALSE,
    risk_score DECIMAL(3,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_geo (user_id, is_suspicious)
);

-- Behavioral analysis table
CREATE TABLE IF NOT EXISTS ai_behavioral_analysis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    behavior_score DECIMAL(3,2) NOT NULL,
    anomaly_detected BOOLEAN DEFAULT FALSE,
    risk_level ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    behavior_patterns TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_behavior (user_id, risk_level)
);

-- Risk scoring table
CREATE TABLE IF NOT EXISTS ai_risk_scoring (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    overall_risk_score DECIMAL(3,2) NOT NULL,
    payment_risk DECIMAL(3,2) NOT NULL,
    account_risk DECIMAL(3,2) NOT NULL,
    behavioral_risk DECIMAL(3,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_risk (user_id, overall_risk_score)
);