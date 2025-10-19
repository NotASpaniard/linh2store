<?php
/**
 * AI Chatbot ChatGPT API
 * Linh2Store - API endpoint cho chatbot tích hợp ChatGPT
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/ai-chatbot-chatgpt.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['message'])) {
        throw new Exception('Missing message parameter');
    }
    
    $message = trim($input['message']);
    $conversationId = $input['conversation_id'] ?? 'chat_chatgpt_' . time();
    $userId = $input['user_id'] ?? null;
    
    if (empty($message)) {
        throw new Exception('Message cannot be empty');
    }
    
    // Process message with ChatGPT
    $chatbot = new AIChatbotChatGPT();
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
