<?php
/**
 * AI Chatbot Buttons API
 * Linh2Store - API endpoint cho chatbot với flow cứng và buttons
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

try {
    require_once '../config/ai-chatbot-buttons.php';
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['message']) || empty($input['message'])) {
        echo json_encode(['success' => false, 'error' => 'Message is required']);
        exit();
    }
    
    $message = trim($input['message']);
    $conversationId = $input['conversation_id'] ?? 'default_' . time();
    
    $chatbot = new AIChatbotButtons();
    $response = $chatbot->processMessage($message, $conversationId);
    
    echo json_encode([
        'success' => true,
        'response' => $response,
        'conversation_id' => $conversationId
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error: ' . $e->getMessage()
    ]);
}
?>
