-- AI Marketing Automation Schema
-- Linh2Store - AI Marketing Automation Database Schema

-- Email personalization table
CREATE TABLE IF NOT EXISTS ai_email_personalization (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject_line VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    open_rate DECIMAL(3,2) DEFAULT 0,
    click_rate DECIMAL(3,2) DEFAULT 0,
    conversion_rate DECIMAL(3,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_email (user_id, created_at)
);

-- Social media insights table
CREATE TABLE IF NOT EXISTS ai_social_insights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform ENUM('Facebook', 'Instagram', 'TikTok', 'YouTube', 'Twitter') NOT NULL,
    post_type ENUM('Product Showcase', 'Beauty Tutorial', 'Customer Review', 'Behind the Scenes') NOT NULL,
    engagement_rate DECIMAL(3,2) NOT NULL,
    reach INT NOT NULL,
    conversion_rate DECIMAL(3,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_platform_type (platform, post_type)
);

-- A/B testing table
CREATE TABLE IF NOT EXISTS ai_ab_testing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_name VARCHAR(255) NOT NULL,
    test_type ENUM('email', 'website', 'advertisement', 'product_page') NOT NULL,
    variant_a TEXT NOT NULL,
    variant_b TEXT NOT NULL,
    winner_variant ENUM('A', 'B', 'Inconclusive') NULL,
    confidence_level DECIMAL(3,2) NULL,
    test_start TIMESTAMP NOT NULL,
    test_end TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_test_type (test_type, test_start)
);

-- Content generation table
CREATE TABLE IF NOT EXISTS ai_content_generation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_type ENUM('product_description', 'email_template', 'social_post', 'blog_article', 'ad_copy') NOT NULL,
    content_text TEXT NOT NULL,
    quality_score DECIMAL(3,2) NOT NULL,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_content_type (content_type, quality_score)
);

-- Audience targeting table
CREATE TABLE IF NOT EXISTS ai_audience_targeting (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    target_segment VARCHAR(100) NOT NULL,
    target_criteria TEXT NOT NULL,
    estimated_reach INT NOT NULL,
    conversion_rate DECIMAL(3,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_campaign_segment (campaign_id, target_segment)
);

-- Performance analytics table
CREATE TABLE IF NOT EXISTS ai_performance_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value DECIMAL(10,2) NOT NULL,
    date_recorded DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_campaign_metric (campaign_id, metric_name, date_recorded)
);

-- Lead scoring table
CREATE TABLE IF NOT EXISTS ai_lead_scoring (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lead_score DECIMAL(3,2) NOT NULL,
    scoring_factors TEXT,
    recommended_actions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_score (user_id, lead_score)
);