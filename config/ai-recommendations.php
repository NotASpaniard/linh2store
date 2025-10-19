<?php
/**
 * AI Recommendations Engine
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/database.php';

class AIRecommendations {
    private $db;
    private $conn;
    private $config;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->loadConfig();
    }
    
    /**
     * Load AI configuration from database
     */
    private function loadConfig() {
        $sql = "SELECT config_key, config_value, config_type FROM ai_config WHERE is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $configs = $stmt->fetchAll();
        
        $this->config = [];
        foreach ($configs as $config) {
            $value = $config['config_value'];
            
            // Convert value based on type
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
     * Get configuration value
     */
    public function getConfig($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Track user behavior
     */
    public function trackBehavior($userId, $productId, $actionType, $sessionId = null, $ipAddress = null, $userAgent = null) {
        $sql = "INSERT INTO user_behavior (user_id, product_id, action_type, session_id, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$userId, $productId, $actionType, $sessionId, $ipAddress, $userAgent]);
    }
    
    /**
     * Get recommendations for a user
     */
    public function getRecommendations($userId, $limit = null) {
        $limit = $limit ?? (int)$this->getConfig('max_recommendations_per_user', 10);
        
        // Clear expired recommendations
        $this->clearExpiredRecommendations();
        
        // Check if user has existing recommendations
        $existingRecs = $this->getExistingRecommendations($userId);
        if (count($existingRecs) >= $limit) {
            return $existingRecs;
        }
        
        // Generate new recommendations
        $newRecs = $this->generateRecommendations($userId, $limit - count($existingRecs));
        
        // Store new recommendations
        $this->storeRecommendations($userId, $newRecs);
        
        // Return combined recommendations
        return array_merge($existingRecs, $newRecs);
    }
    
    /**
     * Get existing recommendations
     */
    private function getExistingRecommendations($userId) {
        $limit = (int)$this->getConfig('max_recommendations_per_user', 10);
        $sql = "SELECT r.*, p.name, p.price, b.name as brand_name
                FROM ai_recommendations r
                JOIN products p ON r.product_id = p.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE r.user_id = ? AND (r.expires_at IS NULL OR r.expires_at > NOW())
                ORDER BY r.score DESC
                LIMIT " . $limit;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Generate new recommendations
     */
    private function generateRecommendations($userId, $limit) {
        $recommendations = [];
        
        // 1. Collaborative Filtering
        if ($this->getConfig('enable_collaborative', true)) {
            $collabRecs = $this->getCollaborativeRecommendations($userId, $limit);
            $recommendations = array_merge($recommendations, $collabRecs);
        }
        
        // 2. Content-Based Filtering
        if ($this->getConfig('enable_content_based', true)) {
            $contentRecs = $this->getContentBasedRecommendations($userId, $limit);
            $recommendations = array_merge($recommendations, $contentRecs);
        }
        
        // 3. Trending Products
        if ($this->getConfig('enable_trending', true)) {
            $trendingRecs = $this->getTrendingRecommendations($userId, $limit);
            $recommendations = array_merge($recommendations, $trendingRecs);
        }
        
        // Remove duplicates and sort by score
        $recommendations = $this->deduplicateAndSort($recommendations);
        
        return array_slice($recommendations, 0, $limit);
    }
    
    /**
     * Collaborative Filtering - Find users with similar behavior
     */
    private function getCollaborativeRecommendations($userId, $limit) {
        $sql = "SELECT p.id, p.name, p.price, b.name as brand_name,
                       COUNT(*) as interaction_count,
                       AVG(CASE WHEN ub.action_type = 'purchase' THEN 1 ELSE 0.5 END) as score
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                JOIN user_behavior ub ON p.id = ub.product_id
                WHERE p.status = 'active'
                AND ub.user_id IN (
                    SELECT DISTINCT ub2.user_id
                    FROM user_behavior ub1
                    JOIN user_behavior ub2 ON ub1.product_id = ub2.product_id
                    WHERE ub1.user_id = ? AND ub2.user_id != ?
                    GROUP BY ub2.user_id
                    HAVING COUNT(DISTINCT ub1.product_id) >= 2
                )
                AND p.id NOT IN (
                    SELECT DISTINCT product_id 
                    FROM user_behavior 
                    WHERE user_id = ?
                )
                GROUP BY p.id
                HAVING score >= ?
                ORDER BY score DESC, interaction_count DESC
                LIMIT " . $limit;
        
        $minScore = (float)$this->getConfig('min_similarity_threshold', 0.3);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId, $userId, $userId, $minScore]);
        
        $results = $stmt->fetchAll();
        return array_map(function($item) {
            $item['recommendation_type'] = 'collaborative';
            return $item;
        }, $results);
    }
    
    /**
     * Content-Based Filtering - Find similar products
     */
    private function getContentBasedRecommendations($userId, $limit) {
        // Get user's preferred features
        $userFeatures = $this->getUserPreferredFeatures($userId);
        if (empty($userFeatures)) {
            return [];
        }
        
        $sql = "SELECT p.id, p.name, p.price, b.name as brand_name,
                       SUM(pf.importance_score * ?) as score
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN product_features pf ON p.id = pf.product_id
                WHERE p.status = 'active'
                AND p.id NOT IN (
                    SELECT DISTINCT product_id 
                    FROM user_behavior 
                    WHERE user_id = ?
                )
                AND pf.feature_name IN (" . str_repeat('?,', count($userFeatures) - 1) . "?)
                GROUP BY p.id
                HAVING score >= ?
                ORDER BY score DESC
                LIMIT " . $limit;
        
        $params = array_merge([$userFeatures], [$userId], array_keys($userFeatures), [(float)$this->getConfig('min_similarity_threshold', 0.3)]);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        
        $results = $stmt->fetchAll();
        return array_map(function($item) {
            $item['recommendation_type'] = 'content_based';
            return $item;
        }, $results);
    }
    
    /**
     * Get trending products
     */
    private function getTrendingRecommendations($userId, $limit) {
        $trendingWindow = (int)$this->getConfig('trending_window_days', 30);
        
        $sql = "SELECT p.id, p.name, p.price, b.name as brand_name,
                       COUNT(*) as interaction_count,
                       COUNT(*) / DATEDIFF(NOW(), MIN(ub.created_at)) as trend_score
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                JOIN user_behavior ub ON p.id = ub.product_id
                WHERE p.status = 'active'
                AND ub.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND p.id NOT IN (
                    SELECT DISTINCT product_id 
                    FROM user_behavior 
                    WHERE user_id = ?
                )
                GROUP BY p.id
                HAVING trend_score >= ?
                ORDER BY trend_score DESC, interaction_count DESC
                LIMIT " . $limit;
        
        $minTrendScore = (float)$this->getConfig('min_similarity_threshold', 0.3);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$trendingWindow, $userId, $minTrendScore]);
        
        $results = $stmt->fetchAll();
        return array_map(function($item) {
            $item['recommendation_type'] = 'trending';
            return $item;
        }, $results);
    }
    
    /**
     * Get user's preferred features
     */
    private function getUserPreferredFeatures($userId) {
        $sql = "SELECT pf.feature_name, AVG(pf.importance_score) as avg_importance
                FROM product_features pf
                JOIN user_behavior ub ON pf.product_id = ub.product_id
                WHERE ub.user_id = ? AND ub.action_type IN ('purchase', 'like')
                GROUP BY pf.feature_name
                HAVING avg_importance > 0.5
                ORDER BY avg_importance DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        $results = $stmt->fetchAll();
        
        $features = [];
        foreach ($results as $result) {
            $features[$result['feature_name']] = $result['avg_importance'];
        }
        
        return $features;
    }
    
    /**
     * Store recommendations in database
     */
    private function storeRecommendations($userId, $recommendations) {
        $expiryDays = (int)$this->getConfig('recommendation_expiry_days', 7);
        $expiryDate = date('Y-m-d H:i:s', strtotime("+{$expiryDays} days"));
        
        $sql = "INSERT INTO ai_recommendations (user_id, product_id, recommendation_type, score, expires_at) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        foreach ($recommendations as $rec) {
            $stmt->execute([
                $userId,
                $rec['id'],
                $rec['recommendation_type'],
                $rec['score'],
                $expiryDate
            ]);
        }
    }
    
    /**
     * Clear expired recommendations
     */
    private function clearExpiredRecommendations() {
        // Check if expires_at column exists first
        $checkColumn = $this->conn->query("SHOW COLUMNS FROM ai_recommendations LIKE 'expires_at'");
        if ($checkColumn->rowCount() > 0) {
            $sql = "DELETE FROM ai_recommendations WHERE expires_at IS NOT NULL AND expires_at < NOW()";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }
    }
    
    /**
     * Remove duplicates and sort by score
     */
    private function deduplicateAndSort($recommendations) {
        $seen = [];
        $unique = [];
        
        foreach ($recommendations as $rec) {
            $key = $rec['id'];
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $rec;
            }
        }
        
        // Sort by score descending
        usort($unique, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return $unique;
    }
    
    /**
     * Get similar products for a given product
     */
    public function getSimilarProducts($productId, $limit = 5) {
        $sql = "SELECT p.id, p.name, p.price, b.name as brand_name,
                       ps.similarity_score as score
                FROM product_similarity ps
                JOIN products p ON ps.product_b_id = p.id
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE ps.product_a_id = ? AND p.status = 'active'
                ORDER BY ps.similarity_score DESC
                LIMIT " . $limit;
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Calculate product similarity
     */
    public function calculateProductSimilarity($productId) {
        // Get product features
        $sql = "SELECT feature_name, feature_value, importance_score 
                FROM product_features 
                WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);
        $features = $stmt->fetchAll();
        
        if (empty($features)) {
            return false;
        }
        
        // Find similar products
        $sql = "SELECT p.id, p.name, p.price, p.image, p.slug, b.name as brand_name
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.status = 'active' AND p.id != ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);
        $products = $stmt->fetchAll();
        
        $similarities = [];
        foreach ($products as $product) {
            $similarity = $this->calculateSimilarityScore($productId, $product['id'], $features);
            if ($similarity > 0.3) {
                $similarities[] = [
                    'product_a_id' => $productId,
                    'product_b_id' => $product['id'],
                    'similarity_score' => $similarity,
                    'similarity_type' => 'content'
                ];
            }
        }
        
        // Store similarities
        if (!empty($similarities)) {
            $this->storeSimilarities($similarities);
        }
        
        return $similarities;
    }
    
    /**
     * Calculate similarity score between two products
     */
    private function calculateSimilarityScore($productA, $productB, $featuresA) {
        $sql = "SELECT feature_name, feature_value, importance_score 
                FROM product_features 
                WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productB]);
        $featuresB = $stmt->fetchAll();
        
        if (empty($featuresB)) {
            return 0;
        }
        
        $featuresBMap = [];
        foreach ($featuresB as $feature) {
            $featuresBMap[$feature['feature_name']] = $feature;
        }
        
        $totalScore = 0;
        $totalWeight = 0;
        
        foreach ($featuresA as $featureA) {
            $featureName = $featureA['feature_name'];
            if (isset($featuresBMap[$featureName])) {
                $featureB = $featuresBMap[$featureName];
                $similarity = $this->calculateFeatureSimilarity($featureA, $featureB);
                $weight = $featureA['importance_score'];
                $totalScore += $similarity * $weight;
                $totalWeight += $weight;
            }
        }
        
        return $totalWeight > 0 ? $totalScore / $totalWeight : 0;
    }
    
    /**
     * Calculate similarity between two features
     */
    private function calculateFeatureSimilarity($featureA, $featureB) {
        if ($featureA['feature_type'] === 'numeric') {
            $valueA = (float) $featureA['feature_value'];
            $valueB = (float) $featureB['feature_value'];
            $maxValue = max($valueA, $valueB);
            return $maxValue > 0 ? 1 - abs($valueA - $valueB) / $maxValue : 0;
        } elseif ($featureA['feature_type'] === 'categorical') {
            return $featureA['feature_value'] === $featureB['feature_value'] ? 1 : 0;
        } elseif ($featureA['feature_type'] === 'boolean') {
            return $featureA['feature_value'] === $featureB['feature_value'] ? 1 : 0;
        } else {
            // Text similarity using Jaccard similarity
            $wordsA = array_unique(explode(' ', strtolower($featureA['feature_value'])));
            $wordsB = array_unique(explode(' ', strtolower($featureB['feature_value'])));
            $intersection = array_intersect($wordsA, $wordsB);
            $union = array_unique(array_merge($wordsA, $wordsB));
            return count($union) > 0 ? count($intersection) / count($union) : 0;
        }
    }
    
    /**
     * Store product similarities
     */
    private function storeSimilarities($similarities) {
        $sql = "INSERT INTO product_similarity (product_a_id, product_b_id, similarity_score, similarity_type) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                similarity_score = VALUES(similarity_score),
                updated_at = CURRENT_TIMESTAMP";
        $stmt = $this->conn->prepare($sql);
        
        foreach ($similarities as $similarity) {
            $stmt->execute([
                $similarity['product_a_id'],
                $similarity['product_b_id'],
                $similarity['similarity_score'],
                $similarity['similarity_type']
            ]);
        }
    }
    
    /**
     * Get recommendation statistics
     */
    public function getRecommendationStats() {
        $sql = "SELECT 
                    COUNT(*) as total_recommendations,
                    COUNT(DISTINCT user_id) as users_with_recommendations,
                    AVG(score) as avg_score,
                    recommendation_type,
                    COUNT(*) as count
                FROM ai_recommendations 
                WHERE expires_at IS NULL OR expires_at > NOW()
                GROUP BY recommendation_type";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
