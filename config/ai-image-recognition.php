<?php
/**
 * AI Image Recognition Engine
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once __DIR__ . '/database.php';

class AIImageRecognition {
    private $db;
    private $conn;
    private $config;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->loadConfig();
    }
    
    /**
     * Load AI Image Recognition configuration
     */
    private function loadConfig() {
        $sql = "SELECT config_key, config_value, config_type FROM ai_image_config WHERE is_active = 1";
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
     * Process uploaded image
     */
    public function processImage($imagePath, $userId = null, $recognitionType = 'product') {
        // Validate image
        if (!$this->validateImage($imagePath)) {
            throw new Exception('Invalid image format or size');
        }
        
        // Generate image hash
        $imageHash = $this->generateImageHash($imagePath);
        
        // Check if already processed
        $existingResult = $this->getExistingResult($imageHash);
        if ($existingResult) {
            return $existingResult;
        }
        
        // Process image based on type
        $startTime = microtime(true);
        $result = $this->performRecognition($imagePath, $recognitionType);
        $processingTime = (microtime(true) - $startTime) * 1000;
        
        // Store result
        $this->storeRecognitionResult($userId, $imagePath, $imageHash, $recognitionType, $result, $processingTime);
        
        return $result;
    }
    
    /**
     * Validate image
     */
    private function validateImage($imagePath) {
        if (!file_exists($imagePath)) {
            return false;
        }
        
        // Check file size
        $fileSize = filesize($imagePath);
        $maxSize = $this->getConfig('max_image_size_mb', 10) * 1024 * 1024;
        if ($fileSize > $maxSize) {
            return false;
        }
        
        // Check file format
        $supportedFormats = $this->getConfig('supported_formats', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        if (!in_array($extension, $supportedFormats)) {
            return false;
        }
        
        // Check if it's a valid image
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Generate image hash
     */
    private function generateImageHash($imagePath) {
        return hash('sha256', file_get_contents($imagePath));
    }
    
    /**
     * Get existing recognition result
     */
    private function getExistingResult($imageHash) {
        $sql = "SELECT * FROM image_recognition_results WHERE image_hash = ? ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$imageHash]);
        return $stmt->fetch();
    }
    
    /**
     * Perform image recognition
     */
    private function performRecognition($imagePath, $recognitionType) {
        $result = [
            'confidence_score' => 0,
            'recognized_data' => [],
            'similar_products' => [],
            'tags' => []
        ];
        
        // Product recognition
        if ($this->getConfig('enable_product_recognition', true) && $recognitionType === 'product') {
            $productResult = $this->recognizeProduct($imagePath);
            $result = array_merge($result, $productResult);
        }
        
        // Brand recognition
        if ($this->getConfig('enable_brand_recognition', true)) {
            $brandResult = $this->recognizeBrand($imagePath);
            $result['recognized_data']['brand'] = $brandResult;
        }
        
        // Color recognition
        if ($this->getConfig('enable_color_recognition', true)) {
            $colorResult = $this->recognizeColors($imagePath);
            $result['recognized_data']['colors'] = $colorResult;
        }
        
        // Text recognition
        if ($this->getConfig('enable_text_recognition', true)) {
            $textResult = $this->recognizeText($imagePath);
            $result['recognized_data']['text'] = $textResult;
        }
        
        // Object recognition
        if ($this->getConfig('enable_object_recognition', true)) {
            $objectResult = $this->recognizeObjects($imagePath);
            $result['recognized_data']['objects'] = $objectResult;
        }
        
        // Auto tagging
        if ($this->getConfig('enable_auto_tagging', true)) {
            $result['tags'] = $this->generateAutoTags($result['recognized_data']);
        }
        
        return $result;
    }
    
    /**
     * Recognize product in image
     */
    private function recognizeProduct($imagePath) {
        // This is a simplified implementation
        // In a real system, you would use machine learning models
        
        $result = [
            'confidence_score' => 0.5,
            'recognized_data' => [],
            'similar_products' => []
        ];
        
        // Extract image features
        $features = $this->extractImageFeatures($imagePath);
        
        // Find similar products in database
        $similarProducts = $this->findSimilarProducts($features);
        $result['similar_products'] = $similarProducts;
        
        // Calculate confidence based on similarity
        if (!empty($similarProducts)) {
            $result['confidence_score'] = $similarProducts[0]['similarity_score'] ?? 0.5;
        }
        
        return $result;
    }
    
    /**
     * Recognize brand in image
     */
    private function recognizeBrand($imagePath) {
        // Simplified brand recognition
        $brands = ['MAC', 'Chanel', 'Dior', 'YSL', 'Lancôme', 'Estée Lauder'];
        $result = [
            'detected_brands' => [],
            'confidence_scores' => []
        ];
        
        // In a real system, you would use OCR or logo detection
        // For now, we'll simulate with random results
        $randomBrand = $brands[array_rand($brands)];
        $result['detected_brands'][] = $randomBrand;
        $result['confidence_scores'][] = rand(70, 95) / 100;
        
        return $result;
    }
    
    /**
     * Recognize colors in image
     */
    private function recognizeColors($imagePath) {
        $result = [
            'dominant_colors' => [],
            'color_palette' => [],
            'color_percentages' => []
        ];
        
        // Get dominant colors
        $colors = $this->getDominantColors($imagePath);
        $result['dominant_colors'] = $colors;
        
        // Generate color palette
        $palette = $this->generateColorPalette($colors);
        $result['color_palette'] = $palette;
        
        return $result;
    }
    
    /**
     * Recognize text in image
     */
    private function recognizeText($imagePath) {
        $result = [
            'detected_text' => [],
            'confidence_scores' => []
        ];
        
        // In a real system, you would use OCR
        // For now, we'll simulate with random text
        $sampleTexts = [
            'Linh2Store',
            'Son môi cao cấp',
            'Mỹ phẩm chính hãng',
            'Giá tốt nhất'
        ];
        
        $randomText = $sampleTexts[array_rand($sampleTexts)];
        $result['detected_text'][] = $randomText;
        $result['confidence_scores'][] = rand(80, 95) / 100;
        
        return $result;
    }
    
    /**
     * Recognize objects in image
     */
    private function recognizeObjects($imagePath) {
        $result = [
            'detected_objects' => [],
            'confidence_scores' => []
        ];
        
        // In a real system, you would use object detection models
        $objects = ['son môi', 'mỹ phẩm', 'chai lọ', 'hộp đựng', 'nhãn hiệu'];
        $randomObject = $objects[array_rand($objects)];
        $result['detected_objects'][] = $randomObject;
        $result['confidence_scores'][] = rand(75, 90) / 100;
        
        return $result;
    }
    
    /**
     * Extract image features
     */
    private function extractImageFeatures($imagePath) {
        // Simplified feature extraction
        $imageInfo = getimagesize($imagePath);
        $features = [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'aspect_ratio' => $imageInfo[0] / $imageInfo[1],
            'file_size' => filesize($imagePath),
            'format' => $imageInfo['mime']
        ];
        
        return $features;
    }
    
    /**
     * Find similar products
     */
    private function findSimilarProducts($features) {
        $sql = "SELECT p.*, b.name as brand_name,
                       ABS(p.width - ?) as width_diff,
                       ABS(p.height - ?) as height_diff
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                WHERE p.status = 'active'
                ORDER BY (width_diff + height_diff) ASC
                LIMIT ?";
        
        $limit = $this->getConfig('max_similarity_results', 10);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$features['width'], $features['height'], $limit]);
        
        $products = $stmt->fetchAll();
        
        // Calculate similarity scores
        foreach ($products as &$product) {
            $product['similarity_score'] = $this->calculateSimilarityScore($features, $product);
        }
        
        // Sort by similarity score
        usort($products, function($a, $b) {
            return $b['similarity_score'] <=> $a['similarity_score'];
        });
        
        return $products;
    }
    
    /**
     * Calculate similarity score
     */
    private function calculateSimilarityScore($features, $product) {
        $score = 0;
        
        // Width similarity
        $widthDiff = abs($features['width'] - $product['width_diff']);
        $widthScore = max(0, 1 - $widthDiff / 1000);
        $score += $widthScore * 0.3;
        
        // Height similarity
        $heightDiff = abs($features['height'] - $product['height_diff']);
        $heightScore = max(0, 1 - $heightDiff / 1000);
        $score += $heightScore * 0.3;
        
        // Aspect ratio similarity
        $aspectRatio = $features['width'] / $features['height'];
        $productAspectRatio = $product['width'] / $product['height'];
        $aspectScore = max(0, 1 - abs($aspectRatio - $productAspectRatio));
        $score += $aspectScore * 0.4;
        
        return min(1, $score);
    }
    
    /**
     * Get dominant colors
     */
    private function getDominantColors($imagePath) {
        $colors = [];
        
        // Simplified color extraction
        $image = imagecreatefromstring(file_get_contents($imagePath));
        if (!$image) {
            return $colors;
        }
        
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Sample colors from image
        $sampleSize = min(100, $width * $height);
        $step = max(1, ($width * $height) / $sampleSize);
        
        for ($i = 0; $i < $sampleSize; $i++) {
            $x = ($i * $step) % $width;
            $y = intval(($i * $step) / $width);
            
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            
            $colors[] = [
                'r' => $r,
                'g' => $g,
                'b' => $b,
                'hex' => sprintf('#%02x%02x%02x', $r, $g, $b)
            ];
        }
        
        imagedestroy($image);
        
        // Group similar colors
        $groupedColors = $this->groupSimilarColors($colors);
        
        return $groupedColors;
    }
    
    /**
     * Group similar colors
     */
    private function groupSimilarColors($colors) {
        $groups = [];
        $threshold = 30; // Color difference threshold
        
        foreach ($colors as $color) {
            $added = false;
            foreach ($groups as &$group) {
                $avgColor = $group['average'];
                $diff = abs($color['r'] - $avgColor['r']) + 
                        abs($color['g'] - $avgColor['g']) + 
                        abs($color['b'] - $avgColor['b']);
                
                if ($diff < $threshold) {
                    $group['colors'][] = $color;
                    $group['count']++;
                    $group['average'] = $this->calculateAverageColor($group['colors']);
                    $added = true;
                    break;
                }
            }
            
            if (!$added) {
                $groups[] = [
                    'colors' => [$color],
                    'count' => 1,
                    'average' => $color
                ];
            }
        }
        
        // Sort by count
        usort($groups, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        
        return array_slice($groups, 0, 5); // Return top 5 colors
    }
    
    /**
     * Calculate average color
     */
    private function calculateAverageColor($colors) {
        $r = $g = $b = 0;
        $count = count($colors);
        
        foreach ($colors as $color) {
            $r += $color['r'];
            $g += $color['g'];
            $b += $color['b'];
        }
        
        return [
            'r' => intval($r / $count),
            'g' => intval($g / $count),
            'b' => intval($b / $count),
            'hex' => sprintf('#%02x%02x%02x', intval($r / $count), intval($g / $count), intval($b / $count))
        ];
    }
    
    /**
     * Generate color palette
     */
    private function generateColorPalette($colors) {
        $palette = [];
        
        foreach ($colors as $colorGroup) {
            $palette[] = [
                'hex' => $colorGroup['average']['hex'],
                'rgb' => $colorGroup['average'],
                'percentage' => ($colorGroup['count'] / array_sum(array_column($colors, 'count'))) * 100
            ];
        }
        
        return $palette;
    }
    
    /**
     * Generate auto tags
     */
    private function generateAutoTags($recognizedData) {
        $tags = [];
        
        // Add brand tags
        if (isset($recognizedData['brand']['detected_brands'])) {
            foreach ($recognizedData['brand']['detected_brands'] as $brand) {
                $tags[] = $brand;
            }
        }
        
        // Add color tags
        if (isset($recognizedData['colors']['dominant_colors'])) {
            foreach ($recognizedData['colors']['dominant_colors'] as $color) {
                $tags[] = $color['average']['hex'];
            }
        }
        
        // Add object tags
        if (isset($recognizedData['objects']['detected_objects'])) {
            foreach ($recognizedData['objects']['detected_objects'] as $object) {
                $tags[] = $object;
            }
        }
        
        return array_unique($tags);
    }
    
    /**
     * Store recognition result
     */
    private function storeRecognitionResult($userId, $imagePath, $imageHash, $recognitionType, $result, $processingTime) {
        $sql = "INSERT INTO image_recognition_results 
                (user_id, image_path, image_hash, recognition_type, confidence_score, recognized_data, processing_time_ms) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $userId,
            $imagePath,
            $imageHash,
            $recognitionType,
            $result['confidence_score'],
            json_encode($result),
            $processingTime
        ]);
        
        return $this->conn->lastInsertId();
    }
    
    /**
     * Get recognition statistics
     */
    public function getRecognitionStats() {
        $sql = "SELECT 
                    COUNT(*) as total_recognitions,
                    AVG(confidence_score) as avg_confidence,
                    AVG(processing_time_ms) as avg_processing_time,
                    recognition_type,
                    COUNT(*) as count
                FROM image_recognition_results 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY recognition_type";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>
