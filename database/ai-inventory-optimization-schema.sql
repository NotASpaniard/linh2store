-- AI Inventory Optimization System Schema
-- Linh2Store - AI Inventory Optimization Database Schema

-- Demand forecasts table
CREATE TABLE IF NOT EXISTS ai_demand_forecasts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    forecast_date DATE NOT NULL,
    predicted_demand INT NOT NULL,
    confidence_score DECIMAL(3,2) NOT NULL,
    model_used VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_forecast (product_id, forecast_date)
);

-- Stock alerts table
CREATE TABLE IF NOT EXISTS ai_stock_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    alert_type ENUM('low_stock', 'overstock', 'reorder_point', 'expiry_warning') NOT NULL,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    message TEXT NOT NULL,
    recommended_action TEXT,
    is_resolved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    INDEX idx_product_alert (product_id, alert_type, is_resolved)
);

-- Supplier recommendations table
CREATE TABLE IF NOT EXISTS ai_supplier_recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id INT NOT NULL,
    recommendation_type ENUM('bulk_purchase', 'negotiate_price', 'alternative_supplier', 'payment_terms') NOT NULL,
    reasoning TEXT NOT NULL,
    priority_score DECIMAL(3,2) NOT NULL,
    estimated_savings DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_supplier_priority (supplier_id, priority_score)
);

-- Warehouse efficiency table
CREATE TABLE IF NOT EXISTS ai_warehouse_efficiency (
    id INT AUTO_INCREMENT PRIMARY KEY,
    warehouse_zone VARCHAR(50) NOT NULL,
    efficiency_score DECIMAL(3,2) NOT NULL,
    throughput_rate DECIMAL(8,2) NOT NULL,
    space_utilization DECIMAL(3,2) NOT NULL,
    date_recorded DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_zone_date (warehouse_zone, date_recorded)
);

-- Inventory patterns table
CREATE TABLE IF NOT EXISTS ai_inventory_patterns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    pattern_type ENUM('seasonal', 'trend', 'cyclical', 'irregular') NOT NULL,
    pattern_strength DECIMAL(3,2) NOT NULL,
    pattern_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_product_pattern (product_id, pattern_type)
);

-- Predictive maintenance table
CREATE TABLE IF NOT EXISTS ai_predictive_maintenance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id VARCHAR(50) NOT NULL,
    maintenance_type ENUM('preventive', 'corrective', 'predictive') NOT NULL,
    predicted_failure_date DATE,
    confidence_score DECIMAL(3,2) NOT NULL,
    recommended_actions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_equipment_date (equipment_id, predicted_failure_date)
);