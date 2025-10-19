<?php
/**
 * Linh2Store Chatbot API
 * Linh2Store - API endpoint cho chatbot đơn giản
 */

require_once '../config/Linh2Store-chatbot.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $message = $input['message'] ?? '';
    
    if (empty($message)) {
        echo json_encode(['error' => 'Message is required']);
        exit;
    }
    
    $chatbot = new Linh2StoreChatbot();
    $response = $chatbot->processMessage($message);
    
    echo json_encode([
        'success' => true,
        'response' => $response,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
