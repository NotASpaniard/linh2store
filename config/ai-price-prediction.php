<?php
/**
 * AI Price Prediction Engine
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/database.php';

class AIPricePrediction {
    private $db;
    private $conn;
    private $config;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->loadConfig();
    }
    
    /**
     * Load AI Price Prediction configuration
     */
    private function loadConfig() {
        $sql = "SELECT config_key, config_value, config_type FROM ai_price_config WHERE is_active = 1";
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
     * Get configuration value
     */
    public function getConfig($key, $default = null) {
        return $this->config[$key] ?? $default;
    }
    
    /**
     * Predict price for a product
     */
    public function predictPrice($productId, $predictionType = 'short_term') {
        // Get product data
        $product = $this->getProductData($productId);
        if (!$product) {
            throw new Exception('Product not found');
        }
        
        // Get historical price data
        $priceHistory = $this->getPriceHistory($productId);
        if (empty($priceHistory)) {
            throw new Exception('Insufficient price history for prediction');
        }
        
        // Get market data
        $marketData = $this->getMarketData();
        
        // Perform prediction
        $prediction = $this->performPrediction($product, $priceHistory, $marketData, $predictionType);
        
        // Store prediction
        $this->storePrediction($productId, $prediction, $predictionType);
        
        return $prediction;
    }
    
    /**
     * Get product data
     */
    private function getProductData($productId) {
        $sql = "SELECT p.*, b.name as brand_name, c.name as category_name 
                FROM products p 
                LEFT JOIN brands b ON p.brand_id = b.id 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetch();
    }
    
    /**
     * Get price history
     */
    private function getPriceHistory($productId, $days = 365) {
        $sql = "SELECT * FROM price_history 
                WHERE product_id = ? 
                AND start_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                ORDER BY start_date ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId, $days]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get market data
     */
    private function getMarketData($days = 30) {
        $sql = "SELECT * FROM market_data 
                WHERE date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                ORDER BY date DESC 
                LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$days]);
        return $stmt->fetch();
    }
    
    /**
     * Perform price prediction
     */
    private function performPrediction($product, $priceHistory, $marketData, $predictionType) {
        $prediction = [
            'predicted_price' => 0,
            'confidence_score' => 0,
            'features_used' => [],
            'analysis' => []
        ];
        
        // 1. Historical trend analysis
        $trendAnalysis = $this->analyzeTrend($priceHistory);
        $prediction['analysis']['trend'] = $trendAnalysis;
        $prediction['features_used'][] = 'historical_trend';
        
        // 2. Seasonal analysis
        if ($this->getConfig('enable_seasonal_analysis', true)) {
            $seasonalAnalysis = $this->analyzeSeasonal($priceHistory);
            $prediction['analysis']['seasonal'] = $seasonalAnalysis;
            $prediction['features_used'][] = 'seasonal_pattern';
        }
        
        // 3. Market analysis
        if ($this->getConfig('enable_market_analysis', true) && $marketData) {
            $marketAnalysis = $this->analyzeMarket($marketData);
            $prediction['analysis']['market'] = $marketAnalysis;
            $prediction['features_used'][] = 'market_indicators';
        }
        
        // 4. Competitor analysis
        if ($this->getConfig('enable_competitor_analysis', true)) {
            $competitorAnalysis = $this->analyzeCompetitors($product);
            $prediction['analysis']['competitors'] = $competitorAnalysis;
            $prediction['features_used'][] = 'competitor_prices';
        }
        
        // 5. Calculate predicted price
        $prediction['predicted_price'] = $this->calculatePredictedPrice($product, $prediction['analysis'], $predictionType);
        
        // 6. Calculate confidence score
        $prediction['confidence_score'] = $this->calculateConfidenceScore($prediction['analysis']);
        
        return $prediction;
    }
    
    /**
     * Analyze price trend
     */
    private function analyzeTrend($priceHistory) {
        if (count($priceHistory) < 2) {
            return ['trend' => 'stable', 'strength' => 0, 'slope' => 0];
        }
        
        $prices = array_column($priceHistory, 'price');
        $dates = array_column($priceHistory, 'start_date');
        
        // Calculate linear regression
        $regression = $this->linearRegression($dates, $prices);
        
        // Determine trend
        $trend = 'stable';
        if ($regression['slope'] > 0.01) {
            $trend = 'increasing';
        } elseif ($regression['slope'] < -0.01) {
            $trend = 'decreasing';
        }
        
        // Calculate trend strength
        $strength = abs($regression['slope']) * 100;
        
        return [
            'trend' => $trend,
            'strength' => $strength,
            'slope' => $regression['slope'],
            'r_squared' => $regression['r_squared']
        ];
    }
    
    /**
     * Analyze seasonal patterns
     */
    private function analyzeSeasonal($priceHistory) {
        $seasonalData = [];
        
        foreach ($priceHistory as $record) {
            $month = date('n', strtotime($record['start_date']));
            if (!isset($seasonalData[$month])) {
                $seasonalData[$month] = [];
            }
            $seasonalData[$month][] = $record['price'];
        }
        
        $seasonalPattern = [];
        foreach ($seasonalData as $month => $prices) {
            $seasonalPattern[$month] = [
                'avg_price' => array_sum($prices) / count($prices),
                'count' => count($prices)
            ];
        }
        
        return [
            'pattern' => $seasonalPattern,
            'current_month' => (int) date('n'),
            'seasonal_factor' => $this->calculateSeasonalFactor($seasonalPattern)
        ];
    }
    
    /**
     * Analyze market data
     */
    private function analyzeMarket($marketData) {
        $analysis = [
            'inflation_impact' => 0,
            'exchange_rate_impact' => 0,
            'market_index_impact' => 0
        ];
        
        if ($marketData['inflation_rate']) {
            $analysis['inflation_impact'] = $marketData['inflation_rate'] * 0.1; // 10% impact
        }
        
        if ($marketData['exchange_rate']) {
            $analysis['exchange_rate_impact'] = ($marketData['exchange_rate'] - 1) * 0.05; // 5% impact
        }
        
        if ($marketData['market_index']) {
            $analysis['market_index_impact'] = ($marketData['market_index'] - 100) * 0.02; // 2% impact
        }
        
        return $analysis;
    }
    
    /**
     * Analyze competitors
     */
    private function analyzeCompetitors($product) {
        $sql = "SELECT p.*, b.name as brand_name 
                FROM products p 
                LEFT JOIN brands b ON p.brand_id = b.id 
                WHERE p.category_id = ? 
                AND p.id != ? 
                AND p.status = 'active'
                ORDER BY p.price ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$product['category_id'], $product['id']]);
        $competitors = $stmt->fetchAll();
        
        if (empty($competitors)) {
            return ['avg_price' => 0, 'price_range' => [0, 0], 'competitor_count' => 0];
        }
        
        $prices = array_column($competitors, 'price');
        $avgPrice = array_sum($prices) / count($prices);
        $minPrice = min($prices);
        $maxPrice = max($prices);
        
        return [
            'avg_price' => $avgPrice,
            'price_range' => [$minPrice, $maxPrice],
            'competitor_count' => count($competitors),
            'price_percentile' => $this->calculatePricePercentile($product['price'], $prices)
        ];
    }
    
    /**
     * Calculate predicted price
     */
    private function calculatePredictedPrice($product, $analysis, $predictionType) {
        $basePrice = $product['price'];
        $adjustment = 0;
        
        // Historical trend adjustment
        if (isset($analysis['trend'])) {
            $trendWeight = $this->getConfig('historical_weight', 0.5);
            $trendImpact = $analysis['trend']['slope'] * $trendWeight;
            $adjustment += $trendImpact;
        }
        
        // Seasonal adjustment
        if (isset($analysis['seasonal'])) {
            $seasonalFactor = $analysis['seasonal']['seasonal_factor'];
            $adjustment += $seasonalFactor * 0.1; // 10% seasonal impact
        }
        
        // Market adjustment
        if (isset($analysis['market'])) {
            $marketWeight = $this->getConfig('market_weight', 0.2);
            $marketImpact = ($analysis['market']['inflation_impact'] + 
                           $analysis['market']['exchange_rate_impact'] + 
                           $analysis['market']['market_index_impact']) * $marketWeight;
            $adjustment += $marketImpact;
        }
        
        // Competitor adjustment
        if (isset($analysis['competitors'])) {
            $competitorWeight = $this->getConfig('competitor_weight', 0.3);
            $competitorPrice = $analysis['competitors']['avg_price'];
            if ($competitorPrice > 0) {
                $competitorImpact = (($competitorPrice - $basePrice) / $basePrice) * $competitorWeight;
                $adjustment += $competitorImpact;
            }
        }
        
        // Apply prediction horizon multiplier
        $horizonMultiplier = $this->getHorizonMultiplier($predictionType);
        $adjustment *= $horizonMultiplier;
        
        $predictedPrice = $basePrice * (1 + $adjustment);
        
        // Ensure reasonable bounds
        $minPrice = $basePrice * 0.5; // 50% of current price
        $maxPrice = $basePrice * 2.0;  // 200% of current price
        
        return max($minPrice, min($maxPrice, $predictedPrice));
    }
    
    /**
     * Calculate confidence score
     */
    private function calculateConfidenceScore($analysis) {
        $confidence = 0;
        
        // Base confidence
        $confidence += 0.3;
        
        // Trend confidence
        if (isset($analysis['trend']) && $analysis['trend']['r_squared'] > 0.5) {
            $confidence += 0.2;
        }
        
        // Seasonal confidence
        if (isset($analysis['seasonal']) && $analysis['seasonal']['seasonal_factor'] > 0.1) {
            $confidence += 0.2;
        }
        
        // Market confidence
        if (isset($analysis['market'])) {
            $confidence += 0.1;
        }
        
        // Competitor confidence
        if (isset($analysis['competitors']) && $analysis['competitors']['competitor_count'] > 0) {
            $confidence += 0.2;
        }
        
        return min(1, $confidence);
    }
    
    /**
     * Get horizon multiplier
     */
    private function getHorizonMultiplier($predictionType) {
        switch ($predictionType) {
            case 'short_term':
                return 1.0;
            case 'medium_term':
                return 1.5;
            case 'long_term':
                return 2.0;
            default:
                return 1.0;
        }
    }
    
    /**
     * Linear regression
     */
    private function linearRegression($x, $y) {
        $n = count($x);
        if ($n < 2) {
            return ['slope' => 0, 'intercept' => 0, 'r_squared' => 0];
        }
        
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumXX = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumXX += $x[$i] * $x[$i];
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        // Calculate R-squared
        $yMean = $sumY / $n;
        $ssTotal = 0;
        $ssResidual = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $yPred = $slope * $x[$i] + $intercept;
            $ssTotal += pow($y[$i] - $yMean, 2);
            $ssResidual += pow($y[$i] - $yPred, 2);
        }
        
        $rSquared = 1 - ($ssResidual / $ssTotal);
        
        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'r_squared' => $rSquared
        ];
    }
    
    /**
     * Calculate seasonal factor
     */
    private function calculateSeasonalFactor($seasonalPattern) {
        $currentMonth = (int) date('n');
        $yearlyAvg = array_sum(array_column($seasonalPattern, 'avg_price')) / count($seasonalPattern);
        
        if (isset($seasonalPattern[$currentMonth])) {
            $currentMonthAvg = $seasonalPattern[$currentMonth]['avg_price'];
            return ($currentMonthAvg - $yearlyAvg) / $yearlyAvg;
        }
        
        return 0;
    }
    
    /**
     * Calculate price percentile
     */
    private function calculatePricePercentile($price, $competitorPrices) {
        $count = 0;
        foreach ($competitorPrices as $competitorPrice) {
            if ($price <= $competitorPrice) {
                $count++;
            }
        }
        
        return $count / count($competitorPrices);
    }
    
    /**
     * Store prediction
     */
    private function storePrediction($productId, $prediction, $predictionType) {
        $horizonDays = $this->getConfig("prediction_horizon_{$predictionType}", 7);
        $expiryDate = date('Y-m-d H:i:s', strtotime("+{$horizonDays} days"));
        
        $sql = "INSERT INTO price_predictions 
                (product_id, predicted_price, confidence_score, prediction_type, prediction_horizon_days, features_used, expires_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $productId,
            $prediction['predicted_price'],
            $prediction['confidence_score'],
            $predictionType,
            $horizonDays,
            json_encode($prediction['features_used']),
            $expiryDate
        ]);
        
        return $this->conn->lastInsertId();
    }
    
    /**
     * Get price prediction statistics
     */
    public function getPredictionStats() {
        $sql = "SELECT 
                    prediction_type,
                    COUNT(*) as count,
                    AVG(confidence_score) as avg_confidence,
                    AVG(predicted_price) as avg_predicted_price
                FROM price_predictions 
                WHERE expires_at > NOW()
                GROUP BY prediction_type";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Get price alerts
     */
    public function getPriceAlerts() {
        if (!$this->getConfig('enable_price_alerts', true)) {
            return [];
        }
        
        $threshold = $this->getConfig('price_change_threshold', 0.1);
        
        $sql = "SELECT p.*, pp.predicted_price, pp.confidence_score, pp.prediction_type
                FROM products p
                JOIN price_predictions pp ON p.id = pp.product_id
                WHERE pp.expires_at > NOW()
                AND ABS(pp.predicted_price - p.price) / p.price > ?
                ORDER BY ABS(pp.predicted_price - p.price) / p.price DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }
}
?>
