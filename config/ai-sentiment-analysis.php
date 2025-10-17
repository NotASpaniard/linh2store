<?php
/**
 * AI Sentiment Analysis Engine
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/database.php';

class AISentimentAnalysis {
    private $db;
    private $conn;
    private $config;
    private $sentimentKeywords;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->loadConfig();
        $this->loadSentimentKeywords();
    }
    
    /**
     * Load AI Sentiment Analysis configuration
     */
    private function loadConfig() {
        $sql = "SELECT config_key, config_value, config_type FROM ai_sentiment_config WHERE is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $configs = $stmt->fetchAll();
        
        $this->config = [];
        foreach ($configs as $config) {
            $value = $config['config_value'];
            
            switch ($config['config_type']) {
                case 'integer':
                    $value = (int) $value;
                    break;
                case 'float':
                    $value = (float) $value;
                    break;
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                    break;
                case 'json':
                    $value = json_decode($value, true);
                    break;
            }
            
            $this->config[$config['config_key']] = $value;
        }
    }
    
    /**
     * Load sentiment keywords
     */
    private function loadSentimentKeywords() {
        $sql = "SELECT keyword, sentiment_type, weight, category FROM sentiment_keywords WHERE is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $keywords = $stmt->fetchAll();
        
        $this->sentimentKeywords = [];
        foreach ($keywords as $keyword) {
            $this->sentimentKeywords[$keyword['keyword']] = [
                'type' => $keyword['sentiment_type'],
                'weight' => $keyword['weight'],
                'category' => $keyword['category']
            ];
        }
    }
    
    /**
     * Get configuration value
     */
    public function getConfig($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Analyze sentiment of text
     */
    public function analyzeSentiment($text, $reviewId = null, $userId = null) {
        $startTime = microtime(true);
        
        // Validate text
        if (!$this->validateText($text)) {
            throw new Exception('Invalid text for sentiment analysis');
        }
        
        // Check if already analyzed
        if ($reviewId) {
            $existingResult = $this->getExistingResult($reviewId);
            if ($existingResult) {
                return $existingResult;
            }
        }
        
        // Perform sentiment analysis
        $result = $this->performSentimentAnalysis($text);
        
        // Calculate processing time
        $processingTime = (microtime(true) - $startTime) * 1000;
        
        // Store result
        $this->storeSentimentResult($reviewId, $userId, $text, $result, $processingTime);
        
        return $result;
    }
    
    /**
     * Validate text
     */
    private function validateText($text) {
        if (empty($text) || strlen($text) < 3) {
            return false;
        }
        
        $maxLength = $this->getConfig('max_text_length', 1000);
        if (strlen($text) > $maxLength) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get existing analysis result
     */
    private function getExistingResult($reviewId) {
        $sql = "SELECT * FROM sentiment_analysis_results WHERE review_id = ? ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$reviewId]);
        return $stmt->fetch();
    }
    
    /**
     * Perform sentiment analysis
     */
    private function performSentimentAnalysis($text) {
        $result = [
            'sentiment_score' => 0,
            'sentiment_label' => 'neutral',
            'confidence_score' => 0,
            'emotion_scores' => [],
            'keywords' => []
        ];
        
        // 1. Keyword-based analysis
        $keywordResult = $this->analyzeByKeywords($text);
        $result['sentiment_score'] = $keywordResult['score'];
        $result['keywords'] = $keywordResult['keywords'];
        
        // 2. Emotion detection
        if ($this->getConfig('enable_emotion_detection', true)) {
            $emotionResult = $this->detectEmotions($text);
            $result['emotion_scores'] = $emotionResult;
        }
        
        // 3. Determine sentiment label
        $result['sentiment_label'] = $this->determineSentimentLabel($result['sentiment_score']);
        
        // 4. Calculate confidence score
        $result['confidence_score'] = $this->calculateConfidenceScore($result);
        
        return $result;
    }
    
    /**
     * Analyze sentiment by keywords
     */
    private function analyzeByKeywords($text) {
        $text = strtolower($text);
        $score = 0;
        $keywords = [];
        
        foreach ($this->sentimentKeywords as $keyword => $data) {
            if (strpos($text, $keyword) !== false) {
                $weight = $data['weight'];
                $type = $data['type'];
                
                // Apply weight based on sentiment type
                if ($type === 'positive') {
                    $score += $weight * $this->getConfig('positive_keywords_weight', 1.0);
                } elseif ($type === 'negative') {
                    $score -= $weight * $this->getConfig('negative_keywords_weight', 1.0);
                } else {
                    $score += $weight * $this->getConfig('neutral_keywords_weight', 0.5) * 0.1;
                }
                
                $keywords[] = [
                    'keyword' => $keyword,
                    'type' => $type,
                    'weight' => $weight,
                    'category' => $data['category']
                ];
            }
        }
        
        // Normalize score
        $score = max(-1, min(1, $score));
        
        return [
            'score' => $score,
            'keywords' => $keywords
        ];
    }
    
    /**
     * Detect emotions in text
     */
    private function detectEmotions($text) {
        $emotions = $this->getConfig('emotion_categories', ['joy', 'sadness', 'anger', 'fear', 'surprise', 'disgust']);
        $emotionScores = [];
        
        // Emotion keywords mapping
        $emotionKeywords = [
            'joy' => ['vui', 'hạnh phúc', 'thích', 'yêu', 'tuyệt vời', 'xuất sắc', 'hoàn hảo'],
            'sadness' => ['buồn', 'thất vọng', 'không hài lòng', 'tệ', 'kém'],
            'anger' => ['tức giận', 'bực bội', 'khó chịu', 'ghét', 'lừa đảo'],
            'fear' => ['lo lắng', 'sợ', 'không chắc', 'không biết'],
            'surprise' => ['bất ngờ', 'ngạc nhiên', 'không ngờ'],
            'disgust' => ['ghê tởm', 'kinh tởm', 'khó chịu']
        ];
        
        $text = strtolower($text);
        
        foreach ($emotions as $emotion) {
            $score = 0;
            if (isset($emotionKeywords[$emotion])) {
                foreach ($emotionKeywords[$emotion] as $keyword) {
                    if (strpos($text, $keyword) !== false) {
                        $score += 1;
                    }
                }
            }
            $emotionScores[$emotion] = $score;
        }
        
        // Normalize scores
        $totalScore = array_sum($emotionScores);
        if ($totalScore > 0) {
            foreach ($emotionScores as $emotion => $score) {
                $emotionScores[$emotion] = $score / $totalScore;
            }
        }
        
        return $emotionScores;
    }
    
    /**
     * Determine sentiment label
     */
    private function determineSentimentLabel($score) {
        $positiveThreshold = $this->getConfig('sentiment_threshold_positive', 0.6);
        $negativeThreshold = $this->getConfig('sentiment_threshold_negative', -0.6);
        
        if ($score >= $positiveThreshold) {
            return 'positive';
        } elseif ($score <= $negativeThreshold) {
            return 'negative';
        } else {
            return 'neutral';
        }
    }
    
    /**
     * Calculate confidence score
     */
    private function calculateConfidenceScore($result) {
        $confidence = 0;
        
        // Base confidence on sentiment score magnitude
        $magnitude = abs($result['sentiment_score']);
        $confidence += $magnitude * 0.5;
        
        // Boost confidence if keywords found
        if (!empty($result['keywords'])) {
            $confidence += 0.3;
        }
        
        // Boost confidence if emotions detected
        if (!empty($result['emotion_scores'])) {
            $maxEmotion = max($result['emotion_scores']);
            $confidence += $maxEmotion * 0.2;
        }
        
        return min(1, $confidence);
    }
    
    /**
     * Store sentiment analysis result
     */
    private function storeSentimentResult($reviewId, $userId, $text, $result, $processingTime) {
        $sql = "INSERT INTO sentiment_analysis_results 
                (review_id, user_id, text_content, sentiment_score, sentiment_label, confidence_score, emotion_scores, keywords, processing_time_ms) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $reviewId,
            $userId,
            $text,
            $result['sentiment_score'],
            $result['sentiment_label'],
            $result['confidence_score'],
            json_encode($result['emotion_scores']),
            json_encode($result['keywords']),
            $processingTime
        ]);
        
        return $this->conn->lastInsertId();
    }
    
    /**
     * Get sentiment statistics
     */
    public function getSentimentStats($timeframe = 30) {
        $sql = "SELECT 
                    sentiment_label,
                    COUNT(*) as count,
                    AVG(sentiment_score) as avg_score,
                    AVG(confidence_score) as avg_confidence
                FROM sentiment_analysis_results 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY sentiment_label";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$timeframe]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get sentiment trends
     */
    public function getSentimentTrends($timeframe = 7) {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    sentiment_label,
                    COUNT(*) as count,
                    AVG(sentiment_score) as avg_score
                FROM sentiment_analysis_results 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(created_at), sentiment_label
                ORDER BY date DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$timeframe]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get sentiment alerts
     */
    public function getSentimentAlerts() {
        if (!$this->getConfig('enable_sentiment_alerts', true)) {
            return [];
        }
        
        $negativeThreshold = $this->getConfig('negative_sentiment_threshold', 0.3);
        $window = $this->getConfig('sentiment_aggregation_window', 7);
        
        $sql = "SELECT 
                    COUNT(*) as negative_count,
                    AVG(sentiment_score) as avg_score
                FROM sentiment_analysis_results 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND sentiment_label = 'negative'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$window]);
        $result = $stmt->fetch();
        
        $alerts = [];
        if ($result['negative_count'] > 0 && $result['avg_score'] < $negativeThreshold) {
            $alerts[] = [
                'type' => 'negative_sentiment_spike',
                'message' => 'Có sự gia tăng đáng kể trong cảm xúc tiêu cực',
                'count' => $result['negative_count'],
                'avg_score' => $result['avg_score']
            ];
        }
        
        return $alerts;
    }
    
    /**
     * Analyze bulk sentiment
     */
    public function analyzeBulkSentiment($texts) {
        $results = [];
        
        foreach ($texts as $index => $text) {
            try {
                $result = $this->analyzeSentiment($text);
                $results[$index] = $result;
            } catch (Exception $e) {
                $results[$index] = [
                    'error' => $e->getMessage(),
                    'sentiment_score' => 0,
                    'sentiment_label' => 'neutral',
                    'confidence_score' => 0
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Get sentiment keywords statistics
     */
    public function getKeywordStats() {
        $sql = "SELECT 
                    sk.keyword,
                    sk.sentiment_type,
                    sk.category,
                    COUNT(sar.id) as usage_count,
                    AVG(sar.sentiment_score) as avg_impact
                FROM sentiment_keywords sk
                LEFT JOIN sentiment_analysis_results sar ON JSON_CONTAINS(sar.keywords, JSON_OBJECT('keyword', sk.keyword))
                WHERE sk.is_active = 1
                GROUP BY sk.id
                ORDER BY usage_count DESC, avg_impact DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Update sentiment keywords
     */
    public function updateSentimentKeywords($keyword, $sentimentType, $weight, $category = null) {
        $sql = "INSERT INTO sentiment_keywords (keyword, sentiment_type, weight, category) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                weight = VALUES(weight),
                category = VALUES(category),
                updated_at = CURRENT_TIMESTAMP";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$keyword, $sentimentType, $weight, $category]);
    }
    
    /**
     * Get sentiment analysis summary
     */
    public function getSentimentSummary($timeframe = 30) {
        $stats = $this->getSentimentStats($timeframe);
        $trends = $this->getSentimentTrends($timeframe);
        $alerts = $this->getSentimentAlerts();
        
        return [
            'stats' => $stats,
            'trends' => $trends,
            'alerts' => $alerts,
            'timeframe' => $timeframe
        ];
    }
}
?>
