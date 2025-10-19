-- AI Marketing Automation Schema
-- Linh2Store - Advanced AI Marketing System

-- AI Marketing Campaigns
CREATE TABLE IF NOT EXISTS ai_marketing_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_name VARCHAR(200) NOT NULL,
    campaign_type ENUM('email', 'sms', 'push', 'social', 'retargeting') NOT NULL,
    target_segment VARCHAR(100),
    ai_generated BOOLEAN DEFAULT TRUE,
    content_data JSON,
    performance_metrics JSON,
    status ENUM('draft', 'active', 'paused', 'completed') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_campaign_type (campaign_type),
    INDEX idx_status (status)
);

-- AI Email Personalization
CREATE TABLE IF NOT EXISTS ai_email_personalization (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    email_template VARCHAR(100) NOT NULL,
    personalized_content JSON,
    personalization_score DECIMAL(3,2) DEFAULT 0.00,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    opened_at TIMESTAMP NULL,
    clicked_at TIMESTAMP NULL,
    INDEX idx_customer_email (customer_id),
    INDEX idx_template (email_template)
);

-- AI Social Media Insights
CREATE TABLE IF NOT EXISTS ai_social_insights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform ENUM('facebook', 'instagram', 'tiktok', 'youtube', 'twitter') NOT NULL,
    content_type ENUM('post', 'story', 'video', 'ad') NOT NULL,
    engagement_metrics JSON,
    sentiment_analysis JSON,
    trending_topics JSON,
    insight_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_platform (platform),
    INDEX idx_content_type (content_type)
);

-- AI A/B Testing
CREATE TABLE IF NOT EXISTS ai_ab_testing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_name VARCHAR(200) NOT NULL,
    test_type ENUM('email', 'landing_page', 'ad_creative', 'product_page') NOT NULL,
    variant_a JSON,
    variant_b JSON,
    traffic_split DECIMAL(3,2) DEFAULT 0.50,
    winner_variant ENUM('A', 'B', 'inconclusive') NULL,
    confidence_level DECIMAL(3,2) DEFAULT 0.00,
    test_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    test_end TIMESTAMP NULL,
    INDEX idx_test_type (test_type),
    INDEX idx_winner (winner_variant)
);

-- AI Content Generation
CREATE TABLE IF NOT EXISTS ai_content_generation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_type ENUM('product_description', 'blog_post', 'social_post', 'email_subject') NOT NULL,
    generated_content TEXT NOT NULL,
    content_metadata JSON,
    quality_score DECIMAL(3,2) DEFAULT 0.00,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_content_type (content_type),
    INDEX idx_quality_score (quality_score)
);
