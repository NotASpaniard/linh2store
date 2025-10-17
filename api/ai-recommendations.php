<?php
/**
 * AI Recommendations API
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../config/ai-recommendations.php';
require_once __DIR__ . '/../config/auth-middleware.php';

try {
    $ai = new AIRecommendations();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_recommendations':
            handleGetRecommendations($ai);
            break;
            
        case 'track_behavior':
            handleTrackBehavior($ai);
            break;
            
        case 'get_similar':
            handleGetSimilar($ai);
            break;
            
        case 'get_stats':
            handleGetStats($ai);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get recommendations for user
 */
function handleGetRecommendations($ai) {
    $userId = AuthMiddleware::getCurrentUser()['id'] ?? null;
    if (!$userId) {
        throw new Exception('User not authenticated');
    }
    
    $limit = intval($_GET['limit'] ?? 10);
    $recommendations = $ai->getRecommendations($userId, $limit);
    
    echo json_encode([
        'success' => true,
        'recommendations' => $recommendations,
        'count' => count($recommendations)
    ]);
}

/**
 * Track user behavior
 */
function handleTrackBehavior($ai) {
    $userId = AuthMiddleware::getCurrentUser()['id'] ?? null;
    if (!$userId) {
        throw new Exception('User not authenticated');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = $input['product_id'] ?? null;
    $actionType = $input['action_type'] ?? null;
    
    if (!$productId || !$actionType) {
        throw new Exception('Missing required parameters');
    }
    
    $sessionId = session_id();
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $success = $ai->trackBehavior($userId, $productId, $actionType, $sessionId, $ipAddress, $userAgent);
    
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Behavior tracked successfully' : 'Failed to track behavior'
    ]);
}

/**
 * Get similar products
 */
function handleGetSimilar($ai) {
    $productId = intval($_GET['product_id'] ?? 0);
    $limit = intval($_GET['limit'] ?? 5);
    
    if (!$productId) {
        throw new Exception('Product ID is required');
    }
    
    $similarProducts = $ai->getSimilarProducts($productId, $limit);
    
    echo json_encode([
        'success' => true,
        'similar_products' => $similarProducts,
        'count' => count($similarProducts)
    ]);
}

/**
 * Get recommendation statistics
 */
function handleGetStats($ai) {
    $stats = $ai->getRecommendationStats();
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}
?>
