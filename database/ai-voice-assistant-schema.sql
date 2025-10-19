-- AI Voice Assistant Schema
-- Linh2Store - Advanced AI Voice Interface

-- AI Voice Commands
CREATE TABLE IF NOT EXISTS ai_voice_commands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    command_text TEXT NOT NULL,
    command_intent VARCHAR(100) NOT NULL,
    command_entities JSON,
    response_template TEXT,
    success_rate DECIMAL(3,2) DEFAULT 0.00,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_command_intent (command_intent),
    INDEX idx_success_rate (success_rate)
);

-- AI Voice Interactions
CREATE TABLE IF NOT EXISTS ai_voice_interactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NULL,
    session_id VARCHAR(100) NOT NULL,
    voice_input TEXT,
    voice_output TEXT,
    intent_recognized VARCHAR(100),
    entities_extracted JSON,
    response_time_ms INT,
    satisfaction_score DECIMAL(3,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer_voice (customer_id),
    INDEX idx_session (session_id)
);

-- AI Voice Analytics
CREATE TABLE IF NOT EXISTS ai_voice_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    analysis_date DATE NOT NULL,
    total_interactions INT DEFAULT 0,
    successful_interactions INT DEFAULT 0,
    avg_response_time DECIMAL(8,2) DEFAULT 0.00,
    top_intents JSON,
    top_entities JSON,
    satisfaction_avg DECIMAL(3,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_analysis_date (analysis_date)
);
