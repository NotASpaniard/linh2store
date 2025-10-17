<?php
/**
 * AI Chatbot API
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

require_once __DIR__ . '/../config/ai-chatbot.php';
require_once __DIR__ . '/../config/auth-middleware.php';

try {
    $chatbot = new AIChatbot();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'start_conversation':
            handleStartConversation($chatbot);
            break;
            
        case 'send_message':
            handleSendMessage($chatbot);
            break;
            
        case 'get_history':
            handleGetHistory($chatbot);
            break;
            
        case 'get_active_conversations':
            handleGetActiveConversations($chatbot);
            break;
            
        case 'close_conversation':
            handleCloseConversation($chatbot);
            break;
            
        case 'add_feedback':
            handleAddFeedback($chatbot);
            break;
            
        case 'get_stats':
            handleGetStats($chatbot);
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
 * Start a new conversation
 */
function handleStartConversation($chatbot) {
    $userId = AuthMiddleware::getCurrentUser()['id'] ?? null;
    $sessionId = $_POST['session_id'] ?? null;
    
    $result = $chatbot->startConversation($userId, $sessionId);
    
    echo json_encode([
        'success' => true,
        'conversation' => $result
    ]);
}

/**
 * Send a message
 */
function handleSendMessage($chatbot) {
    $input = json_decode(file_get_contents('php://input'), true);
    $conversationId = $input['conversation_id'] ?? null;
    $message = $input['message'] ?? null;
    
    if (!$conversationId || !$message) {
        throw new Exception('Missing required parameters');
    }
    
    $userId = AuthMiddleware::getCurrentUser()['id'] ?? null;
    $response = $chatbot->processMessage($conversationId, $message, $userId);
    
    echo json_encode([
        'success' => true,
        'response' => $response
    ]);
}

/**
 * Get conversation history
 */
function handleGetHistory($chatbot) {
    $conversationId = intval($_GET['conversation_id'] ?? 0);
    $limit = intval($_GET['limit'] ?? 20);
    
    if (!$conversationId) {
        throw new Exception('Conversation ID is required');
    }
    
    $history = $chatbot->getConversationHistory($conversationId, $limit);
    
    echo json_encode([
        'success' => true,
        'history' => $history
    ]);
}

/**
 * Get active conversations
 */
function handleGetActiveConversations($chatbot) {
    $userId = AuthMiddleware::getCurrentUser()['id'] ?? null;
    $conversations = $chatbot->getActiveConversations($userId);
    
    echo json_encode([
        'success' => true,
        'conversations' => $conversations
    ]);
}

/**
 * Close conversation
 */
function handleCloseConversation($chatbot) {
    $input = json_decode(file_get_contents('php://input'), true);
    $conversationId = $input['conversation_id'] ?? null;
    
    if (!$conversationId) {
        throw new Exception('Conversation ID is required');
    }
    
    $success = $chatbot->closeConversation($conversationId);
    
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Conversation closed' : 'Failed to close conversation'
    ]);
}

/**
 * Add feedback
 */
function handleAddFeedback($chatbot) {
    $input = json_decode(file_get_contents('php://input'), true);
    $conversationId = $input['conversation_id'] ?? null;
    $messageId = $input['message_id'] ?? null;
    $feedbackType = $input['feedback_type'] ?? null;
    $feedbackText = $input['feedback_text'] ?? null;
    
    if (!$conversationId || !$messageId || !$feedbackType) {
        throw new Exception('Missing required parameters');
    }
    
    $userId = AuthMiddleware::getCurrentUser()['id'] ?? null;
    $success = $chatbot->addFeedback($conversationId, $messageId, $userId, $feedbackType, $feedbackText);
    
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Feedback added' : 'Failed to add feedback'
    ]);
}

/**
 * Get chatbot statistics
 */
function handleGetStats($chatbot) {
    $stats = $chatbot->getChatbotStats();
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}
?>
