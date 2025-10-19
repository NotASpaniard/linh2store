<?php
/**
 * AI Chatbot Advanced API
 * Linh2Store - API endpoint cho chatbot với tư duy nâng cao
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/ai-chatbot-advanced.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['message'])) {
        throw new Exception('Missing message parameter');
    }
    
    $message = trim($input['message']);
    $conversationId = $input['conversation_id'] ?? 'chat_advanced_' . time();
    $userId = $input['user_id'] ?? null;
    
    if (empty($message)) {
        throw new Exception('Message cannot be empty');
    }
    
    // Process message with advanced chatbot
    $chatbot = new AIChatbotAdvanced();
    $response = $chatbot->processMessage($message, $conversationId, $userId);
    
    echo json_encode([
        'success' => true,
        'response' => $response,
        'conversation_id' => $conversationId
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
