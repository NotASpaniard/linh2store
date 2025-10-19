-- AI Inventory Optimization Schema
-- Linh2Store - Advanced AI Inventory Management

-- AI Inventory Predictions
CREATE TABLE IF NOT EXISTS ai_inventory_predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    prediction_date DATE NOT NULL,
    predicted_demand INT NOT NULL,
    confidence_score DECIMAL(3,2) DEFAULT 0.00,
    algorithm_used VARCHAR(50) DEFAULT 'ensemble',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_product_date (product_id, prediction_date),
    INDEX idx_confidence (confidence_score)
);

-- AI Stock Alerts
CREATE TABLE IF NOT EXISTS ai_stock_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    alert_type ENUM('low_stock', 'overstock', 'expiring', 'trending') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    message TEXT NOT NULL,
    recommended_action TEXT,
    is_resolved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    INDEX idx_product_alert (product_id, alert_type),
    INDEX idx_severity (severity),
    INDEX idx_resolved (is_resolved)
);

-- AI Demand Patterns
CREATE TABLE IF NOT EXISTS ai_demand_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    pattern_type ENUM('seasonal', 'trending', 'declining', 'stable') NOT NULL,
    pattern_data JSON,
    confidence_score DECIMAL(3,2) DEFAULT 0.00,
    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_pattern (product_id, pattern_type),
    INDEX idx_confidence (confidence_score)
);

-- AI Supplier Recommendations
CREATE TABLE IF NOT EXISTS ai_supplier_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    supplier_id INT,
    recommendation_type ENUM('restock', 'switch_supplier', 'negotiate_price', 'bulk_order') NOT NULL,
    priority_score DECIMAL(3,2) DEFAULT 0.00,
    reasoning TEXT,
    estimated_savings DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_supplier (product_id, supplier_id),
    INDEX idx_priority (priority_score)
);

-- AI Inventory Analytics
CREATE TABLE IF NOT EXISTS ai_inventory_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    analysis_date DATE NOT NULL,
    total_products INT NOT NULL,
    low_stock_count INT DEFAULT 0,
    overstock_count INT DEFAULT 0,
    turnover_rate DECIMAL(5,2) DEFAULT 0.00,
    carrying_cost DECIMAL(10,2) DEFAULT 0.00,
    optimization_score DECIMAL(3,2) DEFAULT 0.00,
    recommendations_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_analysis_date (analysis_date)
);
