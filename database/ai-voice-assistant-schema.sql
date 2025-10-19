-- AI Voice Assistant Schema
-- Linh2Store - AI Voice Assistant Database Schema

-- Voice interactions table
CREATE TABLE IF NOT EXISTS ai_voice_interactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    voice_input TEXT NOT NULL,
    voice_output TEXT NOT NULL,
    intent_recognized VARCHAR(100) NOT NULL,
    response_time_ms INT NOT NULL,
    satisfaction_score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_voice (user_id, created_at)
);

-- Voice commands table
CREATE TABLE IF NOT EXISTS ai_voice_commands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    command_text TEXT NOT NULL,
    intent VARCHAR(100) NOT NULL,
    response_template TEXT NOT NULL,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_intent_usage (intent, usage_count)
);

-- Speech recognition table
CREATE TABLE IF NOT EXISTS ai_speech_recognition (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    audio_file_path VARCHAR(500) NOT NULL,
    transcribed_text TEXT NOT NULL,
    confidence_score DECIMAL(3,2) NOT NULL,
    language VARCHAR(10) DEFAULT 'vi',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_speech (user_id, confidence_score)
);

-- Text to speech table
CREATE TABLE IF NOT EXISTS ai_text_to_speech (
    id INT AUTO_INCREMENT PRIMARY KEY,
    text_content TEXT NOT NULL,
    audio_file_path VARCHAR(500) NOT NULL,
    voice_type ENUM('male', 'female', 'child') DEFAULT 'female',
    speed DECIMAL(3,2) DEFAULT 1.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_voice_speed (voice_type, speed)
);

-- Natural language understanding table
CREATE TABLE IF NOT EXISTS ai_natural_language_understanding (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    input_text TEXT NOT NULL,
    intent VARCHAR(100) NOT NULL,
    entities TEXT,
    confidence_score DECIMAL(3,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_intent (user_id, intent)
);

-- Dialogue management table
CREATE TABLE IF NOT EXISTS ai_dialogue_management (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    conversation_id VARCHAR(100) NOT NULL,
    dialogue_state VARCHAR(100) NOT NULL,
    context_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_conversation (user_id, conversation_id)
);

-- Voice analytics table
CREATE TABLE IF NOT EXISTS ai_voice_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    interaction_count INT DEFAULT 0,
    average_satisfaction DECIMAL(3,2) DEFAULT 0,
    most_common_intent VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_analytics (user_id, average_satisfaction)
);