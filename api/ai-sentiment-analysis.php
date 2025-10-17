<?php
/**
 * AI Sentiment Analysis API
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

require_once __DIR__ . '/../config/ai-sentiment-analysis.php';
require_once __DIR__ . '/../config/auth-middleware.php';

try {
    $sentiment = new AISentimentAnalysis();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'analyze':
            handleAnalyze($sentiment);
            break;
            
        case 'analyze_bulk':
            handleAnalyzeBulk($sentiment);
            break;
            
        case 'get_stats':
            handleGetStats($sentiment);
            break;
            
        case 'get_trends':
            handleGetTrends($sentiment);
            break;
            
        case 'get_alerts':
            handleGetAlerts($sentiment);
            break;
            
        case 'get_summary':
            handleGetSummary($sentiment);
            break;
            
        case 'get_keyword_stats':
            handleGetKeywordStats($sentiment);
            break;
            
        case 'update_keywords':
            handleUpdateKeywords($sentiment);
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
 * Analyze sentiment of text
 */
function handleAnalyze($sentiment) {
    $input = json_decode(file_get_contents('php://input'), true);
    $text = $input['text'] ?? null;
    $reviewId = $input['review_id'] ?? null;
    
    if (!$text) {
        throw new Exception('Text is required');
    }
    
    $userId = AuthMiddleware::getCurrentUser()['id'] ?? null;
    $result = $sentiment->analyzeSentiment($text, $reviewId, $userId);
    
    echo json_encode([
        'success' => true,
        'result' => $result
    ]);
}

/**
 * Analyze bulk sentiment
 */
function handleAnalyzeBulk($sentiment) {
    $input = json_decode(file_get_contents('php://input'), true);
    $texts = $input['texts'] ?? null;
    
    if (!$texts || !is_array($texts)) {
        throw new Exception('Texts array is required');
    }
    
    $results = $sentiment->analyzeBulkSentiment($texts);
    
    echo json_encode([
        'success' => true,
        'results' => $results
    ]);
}

/**
 * Get sentiment statistics
 */
function handleGetStats($sentiment) {
    $timeframe = intval($_GET['timeframe'] ?? 30);
    $stats = $sentiment->getSentimentStats($timeframe);
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'timeframe' => $timeframe
    ]);
}

/**
 * Get sentiment trends
 */
function handleGetTrends($sentiment) {
    $timeframe = intval($_GET['timeframe'] ?? 7);
    $trends = $sentiment->getSentimentTrends($timeframe);
    
    echo json_encode([
        'success' => true,
        'trends' => $trends,
        'timeframe' => $timeframe
    ]);
}

/**
 * Get sentiment alerts
 */
function handleGetAlerts($sentiment) {
    $alerts = $sentiment->getSentimentAlerts();
    
    echo json_encode([
        'success' => true,
        'alerts' => $alerts
    ]);
}

/**
 * Get sentiment summary
 */
function handleGetSummary($sentiment) {
    $timeframe = intval($_GET['timeframe'] ?? 30);
    $summary = $sentiment->getSentimentSummary($timeframe);
    
    echo json_encode([
        'success' => true,
        'summary' => $summary
    ]);
}

/**
 * Get keyword statistics
 */
function handleGetKeywordStats($sentiment) {
    $stats = $sentiment->getKeywordStats();
    
    echo json_encode([
        'success' => true,
        'keyword_stats' => $stats
    ]);
}

/**
 * Update sentiment keywords
 */
function handleUpdateKeywords($sentiment) {
    $input = json_decode(file_get_contents('php://input'), true);
    $keyword = $input['keyword'] ?? null;
    $sentimentType = $input['sentiment_type'] ?? null;
    $weight = $input['weight'] ?? null;
    $category = $input['category'] ?? null;
    
    if (!$keyword || !$sentimentType || !$weight) {
        throw new Exception('Missing required parameters');
    }
    
    $success = $sentiment->updateSentimentKeywords($keyword, $sentimentType, $weight, $category);
    
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Keywords updated successfully' : 'Failed to update keywords'
    ]);
}
?>
