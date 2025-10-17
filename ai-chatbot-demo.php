<?php
/**
 * AI Chatbot Demo Page
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/auth-middleware.php';
require_once 'config/ai-chatbot.php';

// Check if user is logged in
if (!AuthMiddleware::isLoggedIn()) {
    header('Location: auth/dang-nhap.php');
    exit;
}

$user = AuthMiddleware::getCurrentUser();
$chatbot = new AIChatbot();

// Get active conversations
$conversations = $chatbot->getActiveConversations($user['id']);
$stats = $chatbot->getChatbotStats();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chatbot Demo - Linh2Store</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <img src="assets/images/logo.png" alt="Linh2Store">
                    </a>
                </div>
                
                <nav class="nav">
                    <a href="index.php" class="nav-link">Trang chủ</a>
                    <a href="san-pham/" class="nav-link">Sản phẩm</a>
                    <a href="thuong-hieu/" class="nav-link">Thương hiệu</a>
                    <a href="blog/" class="nav-link">Blog</a>
                    <a href="lien-he/" class="nav-link">Liên hệ</a>
                </nav>
                
                <div class="user-actions">
                    <a href="user/" class="user-icon" title="Tài khoản">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="gio-hang.php" class="cart-icon" title="Giỏ hàng">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <div class="chatbot-demo">
                <h1>💬 AI Chatbot Demo</h1>
                <p>Xin chào <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>! Trò chuyện với trợ lý ảo của chúng tôi:</p>
                
                <!-- Chatbot Stats -->
                <div class="chatbot-stats">
                    <h3>📊 Chatbot Statistics</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h4>Total Conversations</h4>
                            <p><?php echo $stats['total_conversations'] ?? 0; ?></p>
                        </div>
                        <div class="stat-card">
                            <h4>Unique Users</h4>
                            <p><?php echo $stats['unique_users'] ?? 0; ?></p>
                        </div>
                        <div class="stat-card">
                            <h4>Total Messages</h4>
                            <p><?php echo $stats['total_messages'] ?? 0; ?></p>
                        </div>
                        <div class="stat-card">
                            <h4>Response Rate</h4>
                            <p><?php echo number_format(($stats['bot_response_rate'] ?? 0) * 100, 1); ?>%</p>
                        </div>
                    </div>
                </div>
                
                <!-- Active Conversations -->
                <div class="conversations-section">
                    <h3>💬 Active Conversations</h3>
                    <?php if (!empty($conversations)): ?>
                        <div class="conversations-list">
                            <?php foreach ($conversations as $conversation): ?>
                                <div class="conversation-card" data-conversation-id="<?php echo $conversation['id']; ?>">
                                    <div class="conversation-header">
                                        <h4>Conversation #<?php echo $conversation['id']; ?></h4>
                                        <span class="conversation-status"><?php echo ucfirst($conversation['status']); ?></span>
                                    </div>
                                    <div class="conversation-info">
                                        <p>Messages: <?php echo $conversation['message_count']; ?></p>
                                        <p>Last activity: <?php echo date('d/m/Y H:i', strtotime($conversation['last_message_at'])); ?></p>
                                    </div>
                                    <div class="conversation-actions">
                                        <button class="btn btn-primary" onclick="openConversation(<?php echo $conversation['id']; ?>)">
                                            <i class="fas fa-comments"></i> Continue Chat
                                        </button>
                                        <button class="btn btn-outline" onclick="closeConversation(<?php echo $conversation['id']; ?>)">
                                            <i class="fas fa-times"></i> Close
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-conversations">
                            <i class="fas fa-comments"></i>
                            <h3>Chưa có cuộc trò chuyện nào</h3>
                            <p>Hãy bắt đầu trò chuyện với trợ lý ảo!</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Chat Interface -->
                <div class="chat-interface">
                    <h3>🤖 Chat with AI Assistant</h3>
                    <div class="chat-container">
                        <div class="chat-messages" id="chatMessages">
                            <!-- Messages will be loaded here -->
                        </div>
                        <div class="chat-input">
                            <input type="text" id="messageInput" placeholder="Nhập tin nhắn của bạn..." maxlength="500">
                            <button id="sendButton" onclick="sendMessage()">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Chat Controls -->
                <div class="chat-controls">
                    <h3>🎛️ Chat Controls</h3>
                    <div class="controls-grid">
                        <button class="btn btn-primary" onclick="startNewConversation()">
                            <i class="fas fa-plus"></i> New Conversation
                        </button>
                        <button class="btn btn-outline" onclick="loadConversationHistory()">
                            <i class="fas fa-history"></i> Load History
                        </button>
                        <button class="btn btn-outline" onclick="clearChat()">
                            <i class="fas fa-trash"></i> Clear Chat
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Linh2Store. Tất cả quyền được bảo lưu.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        let currentConversationId = null;
        let currentSessionId = null;
        
        // Initialize chat
        document.addEventListener('DOMContentLoaded', function() {
            startNewConversation();
            
            // Handle Enter key in message input
            document.getElementById('messageInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        });
        
        // Start new conversation
        function startNewConversation() {
            showLoading();
            fetch('api/ai-chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'start_conversation',
                    session_id: currentSessionId
                })
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    currentConversationId = data.conversation.conversation_id;
                    currentSessionId = data.conversation.session_id;
                    clearChat();
                    addMessage('bot', 'Xin chào! Tôi là trợ lý ảo của Linh2Store. Tôi có thể giúp bạn tìm sản phẩm, kiểm tra đơn hàng, hoặc trả lời câu hỏi. Bạn cần hỗ trợ gì?');
                } else {
                    showAlert('Lỗi khởi tạo cuộc trò chuyện: ' + data.message, 'error');
                }
            })
            .catch(error => {
                hideLoading();
                showAlert('Lỗi kết nối: ' + error.message, 'error');
            });
        }
        
        // Send message
        function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (!message) return;
            
            if (!currentConversationId) {
                showAlert('Vui lòng khởi tạo cuộc trò chuyện trước', 'error');
                return;
            }
            
            // Add user message to chat
            addMessage('user', message);
            messageInput.value = '';
            
            // Show typing indicator
            showTypingIndicator();
            
            // Send message to API
            fetch('api/ai-chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'send_message',
                    conversation_id: currentConversationId,
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                hideTypingIndicator();
                if (data.success) {
                    addMessage('bot', data.response.text, data.response.type, data.response.metadata);
                } else {
                    addMessage('bot', 'Xin lỗi, có lỗi xảy ra. Vui lòng thử lại.');
                }
            })
            .catch(error => {
                hideTypingIndicator();
                addMessage('bot', 'Xin lỗi, có lỗi kết nối. Vui lòng thử lại.');
            });
        }
        
        // Add message to chat
        function addMessage(sender, text, type = 'text', metadata = null) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            let messageContent = text;
            if (type === 'product' && metadata && metadata.products) {
                messageContent += '\n\nSản phẩm gợi ý:';
                metadata.products.forEach(product => {
                    messageContent += `\n• ${product.name} - ${product.price}đ`;
                });
            }
            
            messageDiv.innerHTML = `
                <div class="message-content">
                    <div class="message-text">${messageContent.replace(/\n/g, '<br>')}</div>
                    <div class="message-time">${new Date().toLocaleTimeString()}</div>
                </div>
            `;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Show typing indicator
        function showTypingIndicator() {
            const chatMessages = document.getElementById('chatMessages');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot typing';
            typingDiv.id = 'typingIndicator';
            typingDiv.innerHTML = `
                <div class="message-content">
                    <div class="message-text">
                        <i class="fas fa-circle"></i>
                        <i class="fas fa-circle"></i>
                        <i class="fas fa-circle"></i>
                        AI đang trả lời...
                    </div>
                </div>
            `;
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Hide typing indicator
        function hideTypingIndicator() {
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }
        
        // Clear chat
        function clearChat() {
            document.getElementById('chatMessages').innerHTML = '';
        }
        
        // Load conversation history
        function loadConversationHistory() {
            if (!currentConversationId) {
                showAlert('Vui lòng khởi tạo cuộc trò chuyện trước', 'error');
                return;
            }
            
            fetch(`api/ai-chatbot.php?action=get_history&conversation_id=${currentConversationId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        clearChat();
                        data.history.forEach(message => {
                            addMessage(message.sender_type, message.message_text, message.message_type, message.metadata ? JSON.parse(message.metadata) : null);
                        });
                    } else {
                        showAlert('Lỗi tải lịch sử: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    showAlert('Lỗi kết nối: ' + error.message, 'error');
                });
        }
        
        // Open conversation
        function openConversation(conversationId) {
            currentConversationId = conversationId;
            loadConversationHistory();
        }
        
        // Close conversation
        function closeConversation(conversationId) {
            fetch('api/ai-chatbot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'close_conversation',
                    conversation_id: conversationId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Cuộc trò chuyện đã được đóng', 'success');
                    location.reload();
                } else {
                    showAlert('Lỗi đóng cuộc trò chuyện: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('Lỗi kết nối: ' + error.message, 'error');
            });
        }
    </script>
    
    <style>
        .chatbot-demo {
            padding: 2rem 0;
        }
        
        .chatbot-demo h1 {
            color: #EC407A;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .chatbot-stats {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }
        
        .stat-card p {
            margin: 0;
            font-size: 1.5rem;
            font-weight: bold;
            color: #EC407A;
        }
        
        .conversations-section {
            margin: 2rem 0;
        }
        
        .conversations-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .conversation-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 1rem;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .conversation-header h4 {
            margin: 0;
            color: #333;
        }
        
        .conversation-status {
            background: #EC407A;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .conversation-info {
            margin: 0.5rem 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .conversation-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .conversation-actions .btn {
            flex: 1;
            padding: 0.5rem;
            font-size: 0.9rem;
        }
        
        .no-conversations {
            text-align: center;
            padding: 3rem 0;
        }
        
        .no-conversations i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }
        
        .chat-interface {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
        }
        
        .chat-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 1rem;
            background: #f8f9fa;
        }
        
        .message {
            margin-bottom: 1rem;
            display: flex;
        }
        
        .message.user {
            justify-content: flex-end;
        }
        
        .message.bot {
            justify-content: flex-start;
        }
        
        .message-content {
            max-width: 70%;
            padding: 0.75rem 1rem;
            border-radius: 18px;
            position: relative;
        }
        
        .message.user .message-content {
            background: #EC407A;
            color: white;
        }
        
        .message.bot .message-content {
            background: white;
            color: #333;
            border: 1px solid #eee;
        }
        
        .message.typing .message-content {
            background: #e9ecef;
            color: #666;
        }
        
        .message-text {
            margin-bottom: 0.25rem;
        }
        
        .message-time {
            font-size: 0.8rem;
            opacity: 0.7;
        }
        
        .chat-input {
            display: flex;
            padding: 1rem;
            background: white;
            border-top: 1px solid #eee;
        }
        
        .chat-input input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
        }
        
        .chat-input button {
            margin-left: 0.5rem;
            padding: 0.75rem 1rem;
            background: #EC407A;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
        }
        
        .chat-input button:hover {
            background: #d81b60;
        }
        
        .chat-controls {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
        }
        
        .controls-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
    </style>
</body>
</html>
