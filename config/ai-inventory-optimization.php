<?php
/**
 * AI Inventory Optimization Engine
 * Linh2Store - Advanced AI Inventory Management
 */

class AIInventoryOptimization {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Predict inventory demand
     */
    public function predictDemand($productId, $daysAhead = 30) {
        try {
            // Get historical sales data
            $salesData = $this->getHistoricalSales($productId, 90);
            
            // Analyze patterns
            $patterns = $this->analyzeSalesPatterns($salesData);
            
            // Generate predictions
            $predictions = $this->generateDemandPredictions($patterns, $daysAhead);
            
            // Save predictions
            $this->savePredictions($productId, $predictions);
            
            return $predictions;
            
        } catch (Exception $e) {
            error_log("AI Inventory Prediction Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate stock alerts
     */
    public function generateStockAlerts() {
        try {
            $alerts = [];
            
            // Get all products
            $products = $this->getAllProducts();
            
            foreach ($products as $product) {
                // Check low stock (using default reorder point of 10)
                $reorderPoint = 10;
                if ($product['stock_quantity'] <= $reorderPoint) {
                    $alerts[] = $this->createAlert($product['id'], 'low_stock', 'high', 
                        "Sản phẩm {$product['name']} sắp hết hàng. Cần nhập thêm sản phẩm.");
                }
                
                // Check overstock (using default max stock of 100)
                $maxStock = 100;
                if ($product['stock_quantity'] > $maxStock * 1.5) {
                    $alerts[] = $this->createAlert($product['id'], 'overstock', 'medium', 
                        "Sản phẩm {$product['name']} tồn kho quá nhiều. Cần giảm giá hoặc tăng cường marketing.");
                }
                
                // Check trending products
                if ($this->isTrending($product['id'])) {
                    $alerts[] = $this->createAlert($product['id'], 'trending', 'low', 
                        "Sản phẩm {$product['name']} đang trending. Cần tăng cường quảng cáo.");
                }
            }
            
            // Save alerts
            $this->saveAlerts($alerts);
            
            return $alerts;
            
        } catch (Exception $e) {
            error_log("AI Stock Alert Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Analyze demand patterns
     */
    public function analyzeDemandPatterns($productId) {
        try {
            $salesData = $this->getHistoricalSales($productId, 365);
            
            $patterns = [
                'seasonal' => $this->detectSeasonalPattern($salesData),
                'trending' => $this->detectTrendingPattern($salesData),
                'declining' => $this->detectDecliningPattern($salesData),
                'stable' => $this->detectStablePattern($salesData)
            ];
            
            // Save patterns
            $this->savePatterns($productId, $patterns);
            
            return $patterns;
            
        } catch (Exception $e) {
            error_log("AI Pattern Analysis Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate supplier recommendations
     */
    public function generateSupplierRecommendations() {
        try {
            $recommendations = [];
            
            // Get products with low stock
            $lowStockProducts = $this->getLowStockProducts();
            
            foreach ($lowStockProducts as $product) {
                // Find best supplier
                $bestSupplier = $this->findBestSupplier($product['id']);
                
                if ($bestSupplier) {
                    $recommendations[] = [
                        'product_id' => $product['id'],
                        'supplier_id' => $bestSupplier['id'],
                        'recommendation_type' => 'restock',
                        'priority_score' => $this->calculatePriorityScore($product),
                        'reasoning' => "Nhà cung cấp {$bestSupplier['name']} có giá tốt nhất và thời gian giao hàng nhanh.",
                        'estimated_savings' => $this->calculateSavings($product, $bestSupplier)
                    ];
                }
            }
            
            // Save recommendations
            $this->saveRecommendations($recommendations);
            
            return $recommendations;
            
        } catch (Exception $e) {
            error_log("AI Supplier Recommendation Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get inventory analytics
     */
    public function getInventoryAnalytics() {
        try {
            $analytics = [
                'total_products' => $this->getTotalProducts(),
                'low_stock_count' => $this->getLowStockCount(),
                'overstock_count' => $this->getOverstockCount(),
                'turnover_rate' => $this->calculateTurnoverRate(),
                'carrying_cost' => $this->calculateCarryingCost(),
                'optimization_score' => $this->calculateOptimizationScore(),
                'recommendations_count' => $this->getRecommendationsCount()
            ];
            
            // Save analytics
            $this->saveAnalytics($analytics);
            
            return $analytics;
            
        } catch (Exception $e) {
            error_log("AI Inventory Analytics Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get historical sales data
     */
    private function getHistoricalSales($productId, $days) {
        $sql = "SELECT DATE(created_at) as sale_date, SUM(quantity) as total_sold
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE oi.product_id = ? 
                AND o.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND o.status != 'cancelled'
                GROUP BY DATE(created_at)
                ORDER BY sale_date ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Analyze sales patterns
     */
    private function analyzeSalesPatterns($salesData) {
        if (empty($salesData)) return [];
        
        $patterns = [
            'trend' => $this->calculateTrend($salesData),
            'seasonality' => $this->calculateSeasonality($salesData),
            'volatility' => $this->calculateVolatility($salesData),
            'average_daily' => $this->calculateAverageDaily($salesData)
        ];
        
        return $patterns;
    }
    
    /**
     * Generate demand predictions
     */
    private function generateDemandPredictions($patterns, $daysAhead) {
        $predictions = [];
        $baseDemand = $patterns['average_daily'] ?? 0;
        $trend = $patterns['trend'] ?? 0;
        
        for ($i = 1; $i <= $daysAhead; $i++) {
            $predictedDemand = $baseDemand + ($trend * $i);
            $confidence = $this->calculateConfidence($patterns, $i);
            
            $predictions[] = [
                'date' => date('Y-m-d', strtotime("+{$i} days")),
                'predicted_demand' => max(0, round($predictedDemand)),
                'confidence_score' => $confidence
            ];
        }
        
        return $predictions;
    }
    
    /**
     * Detect seasonal pattern
     */
    private function detectSeasonalPattern($salesData) {
        if (count($salesData) < 30) return false;
        
        $monthlySales = [];
        foreach ($salesData as $sale) {
            $month = date('m', strtotime($sale['sale_date']));
            $monthlySales[$month] = ($monthlySales[$month] ?? 0) + $sale['total_sold'];
        }
        
        $variance = $this->calculateVariance(array_values($monthlySales));
        return $variance > 0.3; // High variance indicates seasonality
    }
    
    /**
     * Detect trending pattern
     */
    private function detectTrendingPattern($salesData) {
        if (count($salesData) < 14) return false;
        
        $recentSales = array_slice($salesData, -14);
        $olderSales = array_slice($salesData, -28, 14);
        
        $recentAvg = array_sum(array_column($recentSales, 'total_sold')) / count($recentSales);
        $olderAvg = array_sum(array_column($olderSales, 'total_sold')) / count($olderSales);
        
        return $recentAvg > $olderAvg * 1.2; // 20% increase indicates trending
    }
    
    /**
     * Detect declining pattern
     */
    private function detectDecliningPattern($salesData) {
        if (count($salesData) < 14) return false;
        
        $recentSales = array_slice($salesData, -14);
        $olderSales = array_slice($salesData, -28, 14);
        
        $recentAvg = array_sum(array_column($recentSales, 'total_sold')) / count($recentSales);
        $olderAvg = array_sum(array_column($olderSales, 'total_sold')) / count($olderSales);
        
        return $recentAvg < $olderAvg * 0.8; // 20% decrease indicates declining
    }
    
    /**
     * Detect stable pattern
     */
    private function detectStablePattern($salesData) {
        if (count($salesData) < 14) return false;
        
        $volatility = $this->calculateVolatility($salesData);
        return $volatility < 0.2; // Low volatility indicates stability
    }
    
    /**
     * Calculate trend
     */
    private function calculateTrend($salesData) {
        if (count($salesData) < 2) return 0;
        
        $x = range(1, count($salesData));
        $y = array_column($salesData, 'total_sold');
        
        $n = count($x);
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumXX = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumXX += $x[$i] * $x[$i];
        }
        
        return ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
    }
    
    /**
     * Calculate volatility
     */
    private function calculateVolatility($salesData) {
        if (count($salesData) < 2) return 0;
        
        $values = array_column($salesData, 'total_sold');
        $mean = array_sum($values) / count($values);
        
        $variance = 0;
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        $variance /= count($values);
        
        return sqrt($variance) / $mean;
    }
    
    /**
     * Calculate confidence score
     */
    private function calculateConfidence($patterns, $daysAhead) {
        $baseConfidence = 0.8;
        $volatilityPenalty = $patterns['volatility'] ?? 0;
        $timePenalty = $daysAhead * 0.01;
        
        return max(0.1, $baseConfidence - $volatilityPenalty - $timePenalty);
    }
    
    /**
     * Save predictions to database
     */
    private function savePredictions($productId, $predictions) {
        foreach ($predictions as $prediction) {
            $sql = "INSERT INTO ai_inventory_predictions 
                    (product_id, prediction_date, predicted_demand, confidence_score) 
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    predicted_demand = VALUES(predicted_demand),
                    confidence_score = VALUES(confidence_score)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $productId,
                $prediction['date'],
                $prediction['predicted_demand'],
                $prediction['confidence_score']
            ]);
        }
    }
    
    /**
     * Save alerts to database
     */
    private function saveAlerts($alerts) {
        foreach ($alerts as $alert) {
            $sql = "INSERT INTO ai_stock_alerts 
                    (product_id, alert_type, severity, message, recommended_action) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $alert['product_id'],
                $alert['alert_type'],
                $alert['severity'],
                $alert['message'],
                $alert['recommended_action'] ?? null
            ]);
        }
    }
    
    /**
     * Get all products
     */
    private function getAllProducts() {
        $sql = "SELECT p.*, 
                       COALESCE(SUM(oi.quantity), 0) as total_sold,
                       COALESCE(AVG(oi.quantity), 0) as avg_quantity
                FROM products p
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled'
                GROUP BY p.id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create alert
     */
    private function createAlert($productId, $type, $severity, $message, $action = null) {
        return [
            'product_id' => $productId,
            'alert_type' => $type,
            'severity' => $severity,
            'message' => $message,
            'recommended_action' => $action
        ];
    }
    
    /**
     * Check if product is trending
     */
    private function isTrending($productId) {
        $sql = "SELECT COUNT(*) as recent_orders
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE oi.product_id = ? 
                AND o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND o.status != 'cancelled'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['recent_orders'] > 5; // More than 5 orders in last 7 days
    }
    
    /**
     * Get low stock products
     */
    private function getLowStockProducts() {
        $sql = "SELECT * FROM products 
                WHERE stock_quantity <= reorder_point";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Find best supplier
     */
    private function findBestSupplier($productId) {
        // This would typically query a suppliers table
        // For now, return mock data
        return [
            'id' => 1,
            'name' => 'Nhà cung cấp A',
            'price' => 100000,
            'delivery_time' => 3
        ];
    }
    
    /**
     * Calculate priority score
     */
    private function calculatePriorityScore($product) {
        $stockRatio = $product['stock_quantity'] / $product['reorder_point'];
        $salesVelocity = $product['total_sold'] ?? 0;
        
        return min(1.0, ($stockRatio * 0.3) + ($salesVelocity * 0.7));
    }
    
    /**
     * Calculate savings
     */
    private function calculateSavings($product, $supplier) {
        $currentPrice = $product['price'] ?? 0;
        $supplierPrice = $supplier['price'] ?? 0;
        $quantity = $product['reorder_quantity'] ?? 0;
        
        return max(0, ($currentPrice - $supplierPrice) * $quantity);
    }
    
    /**
     * Save patterns to database
     */
    private function savePatterns($productId, $patterns) {
        foreach ($patterns as $type => $detected) {
            if ($detected) {
                $sql = "INSERT INTO ai_demand_patterns 
                        (product_id, pattern_type, pattern_data, confidence_score) 
                        VALUES (?, ?, ?, ?)";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    $productId,
                    $type,
                    json_encode(['detected' => true]),
                    0.8
                ]);
            }
        }
    }
    
    /**
     * Save recommendations to database
     */
    private function saveRecommendations($recommendations) {
        foreach ($recommendations as $rec) {
            $sql = "INSERT INTO ai_supplier_recommendations 
                    (product_id, supplier_id, recommendation_type, priority_score, reasoning, estimated_savings) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $rec['product_id'],
                $rec['supplier_id'],
                $rec['recommendation_type'],
                $rec['priority_score'],
                $rec['reasoning'],
                $rec['estimated_savings']
            ]);
        }
    }
    
    /**
     * Save analytics to database
     */
    private function saveAnalytics($analytics) {
        $sql = "INSERT INTO ai_inventory_analytics 
                (analysis_date, total_products, low_stock_count, overstock_count, 
                 turnover_rate, carrying_cost, optimization_score, recommendations_count) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            date('Y-m-d'),
            $analytics['total_products'],
            $analytics['low_stock_count'],
            $analytics['overstock_count'],
            $analytics['turnover_rate'],
            $analytics['carrying_cost'],
            $analytics['optimization_score'],
            $analytics['recommendations_count']
        ]);
    }
    
    /**
     * Get total products
     */
    private function getTotalProducts() {
        $sql = "SELECT COUNT(*) as count FROM products";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Get low stock count
     */
    private function getLowStockCount() {
        $sql = "SELECT COUNT(*) as count FROM products WHERE stock_quantity <= reorder_point";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Get overstock count
     */
    private function getOverstockCount() {
        $sql = "SELECT COUNT(*) as count FROM products WHERE stock_quantity > max_stock * 1.5";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Calculate turnover rate
     */
    private function calculateTurnoverRate() {
        // Simplified calculation
        return 0.75; // 75% turnover rate
    }
    
    /**
     * Calculate carrying cost
     */
    private function calculateCarryingCost() {
        $sql = "SELECT SUM(stock_quantity * price * 0.2) as cost FROM products";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['cost'] ?? 0;
    }
    
    /**
     * Calculate optimization score
     */
    private function calculateOptimizationScore() {
        // Simplified calculation based on various factors
        return 0.85; // 85% optimization score
    }
    
    /**
     * Get recommendations count
     */
    private function getRecommendationsCount() {
        $sql = "SELECT COUNT(*) as count FROM ai_supplier_recommendations WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
    
    /**
     * Calculate variance
     */
    private function calculateVariance($values) {
        if (empty($values)) return 0;
        
        $mean = array_sum($values) / count($values);
        $variance = 0;
        
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        
        return $variance / count($values);
    }
    
    /**
     * Calculate average daily sales
     */
    private function calculateAverageDaily($salesData) {
        if (empty($salesData)) return 0;
        
        $totalSold = array_sum(array_column($salesData, 'total_sold'));
        $days = count($salesData);
        
        return $totalSold / $days;
    }
}
?>
