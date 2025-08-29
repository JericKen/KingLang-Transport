<?php
require_once __DIR__ . '/../../models/ChatModel.php';

class ChatController {
    private $chatModel;
    
    public function __construct() {
        $this->chatModel = new ChatModel();
    }
    
    /**
     * Get or create conversation for current user
     */
    public function getOrCreateConversation() {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'User not authenticated']);
                return;
            }
            
            $conversationId = $this->chatModel->getOrCreateConversation($_SESSION['user_id']);
            
            echo json_encode([
                'success' => true,
                'conversation_id' => $conversationId
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get messages for a conversation
     */
    public function getMessages($conversationId) {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'User not authenticated']);
                return;
            }
            
            // Verify user owns this conversation
            $conversation = $this->chatModel->getConversationStatus($conversationId);
            if (!$conversation || $conversation['client_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                return;
            }
            
            $messages = $this->chatModel->getConversationMessages($conversationId);
            
            echo json_encode([
                'success' => true,
                'messages' => $messages
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Send a message
     */
    public function sendMessage() {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'User not authenticated']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $conversationId = $input['conversation_id'] ?? null;
            $message = trim($input['message'] ?? '');
            
            if (!$conversationId || empty($message)) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                return;
            }
            
            // Verify user owns this conversation
            $conversation = $this->chatModel->getConversationStatus($conversationId);
            if (!$conversation || $conversation['client_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                return;
            }
            
            // If conversation was ended, reactivate it
            if ($conversation['status'] === 'ended') {
                $this->chatModel->reactivateConversation($conversationId);
            }
            
            // Add client message
            $messageId = $this->chatModel->addMessage($conversationId, 'client', $_SESSION['user_id'], $message);
            
            $response = [
                'success' => true,
                'message_id' => $messageId,
                'message' => [
                    'id' => $messageId,
                    'conversation_id' => $conversationId,
                    'sender_type' => 'client',
                    'message' => $message,
                    'sent_at' => date('Y-m-d H:i:s')
                ]
            ];
            
            // Get updated conversation status
            $updatedConversation = $this->chatModel->getConversationStatus($conversationId);
            
            // Only process bot response if not talking to human
            if ($updatedConversation['status'] !== 'human_assigned' && !$updatedConversation['admin_id']) {
                $botResponse = $this->chatModel->processBotMessage($message, $conversationId);
                
                if ($botResponse) {
                    // Bot can handle the question
                    $botMessageId = $this->chatModel->addMessage($conversationId, 'bot', null, $botResponse);
                    $response['bot_response'] = [
                        'id' => $botMessageId,
                        'conversation_id' => $conversationId,
                        'sender_type' => 'bot',
                        'message' => $botResponse,
                        'sent_at' => date('Y-m-d H:i:s')
                    ];
                } else {
                    // Bot cannot handle the question, offer human assistance
                    $complexMessage = "I'm sorry, but I don't have enough information to answer your question properly. Would you like to talk to a customer service representative who can help you better?";
                    $buttonsHtml = '<div class="mt-2 button-container"><button onclick="requestHumanAssistance()" class="btn btn-sm btn-primary">Yes, connect me with an agent</button> <button onclick="resetChat()" class="btn btn-sm btn-outline-secondary">No, I\'ll ask something else</button></div>';
                    
                    $fullMessage = $complexMessage . ' ' . $buttonsHtml;
                    $botMessageId = $this->chatModel->addMessage($conversationId, 'bot', null, $fullMessage);
                    $response['bot_response'] = [
                        'id' => $botMessageId,
                        'conversation_id' => $conversationId,
                        'sender_type' => 'bot',
                        'message' => $fullMessage,
                        'sent_at' => date('Y-m-d H:i:s')
                    ];
                }
            }
            
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Request human assistance
     */
    public function requestHumanAssistance() {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'User not authenticated']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $conversationId = $input['conversation_id'] ?? null;
            
            if (!$conversationId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing conversation ID']);
                return;
            }
            
            // Verify user owns this conversation
            $conversation = $this->chatModel->getConversationStatus($conversationId);
            if (!$conversation || $conversation['client_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                return;
            }
            
            // Request human assistance
            $this->chatModel->requestHumanAssistance($conversationId);
            
            // Add system message
            $systemMessage = "Thank you for your patience. I'm connecting you with one of our customer service representatives who will be able to help you better. Please wait a moment while I transfer your conversation to an available agent.";
            $this->chatModel->addMessage($conversationId, 'bot', null, $systemMessage);
            
            echo json_encode([
                'success' => true,
                'message' => 'Human assistance requested successfully'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * End conversation
     */
    public function endConversation() {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'User not authenticated']);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $conversationId = $input['conversation_id'] ?? null;
            
            if (!$conversationId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing conversation ID']);
                return;
            }
            
            // Verify user owns this conversation
            $conversation = $this->chatModel->getConversationStatus($conversationId);
            if (!$conversation || $conversation['client_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                return;
            }
            
            // End conversation
            $this->chatModel->endConversation($conversationId, 'client');
            
            // Add system message
            $endMessage = "This conversation has been ended by you. Thank you for using KingLang Bus Rental service! Feel free to start a new conversation if you need further assistance.";
            $this->chatModel->addMessage($conversationId, 'system', null, $endMessage);
            
            echo json_encode([
                'success' => true,
                'message' => 'Conversation ended successfully'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Get conversation status
     */
    public function getConversationStatus($conversationId) {
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'User not authenticated']);
                return;
            }
            
            // Verify user owns this conversation
            $conversation = $this->chatModel->getConversationStatus($conversationId);
            if (!$conversation || $conversation['client_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                return;
            }
            
            echo json_encode([
                'success' => true,
                'conversation' => $conversation
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>