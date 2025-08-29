<?php
require_once __DIR__ . '/../../models/ChatModel.php';

class VisitorChatController {
    private $chatModel;
    
    public function __construct() {
        $this->chatModel = new ChatModel();
    }
    
    /**
     * Get or create conversation for visitor
     */
    public function getOrCreateVisitorConversation() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $sessionId = $input['session_id'] ?? null;
            
            if (!$sessionId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing session ID']);
                return;
            }
            
            // Store session ID in PHP session
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['visitor_session_id'] = $sessionId;
            
            $conversationId = $this->chatModel->getOrCreateVisitorConversation($sessionId);
            
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
     * Get messages for a visitor conversation
     */
    public function getVisitorMessages($conversationId) {
        try {
            $sessionId = $_SERVER['HTTP_X_SESSION_ID'] ?? null;
            
            if (!$sessionId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing session ID']);
                return;
            }
            
            // Verify session owns this conversation
            $conversation = $this->chatModel->getVisitorConversationStatus($conversationId, $sessionId);
            if (!$conversation) {
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
     * Send a visitor message
     */
    public function sendVisitorMessage() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $conversationId = $input['conversation_id'] ?? null;
            $sessionId = $input['session_id'] ?? null;
            $message = trim($input['message'] ?? '');
            
            if (!$conversationId || !$sessionId || empty($message)) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                return;
            }
            
            // Verify session owns this conversation
            $conversation = $this->chatModel->getVisitorConversationStatus($conversationId, $sessionId);
            if (!$conversation) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                return;
            }
            
            // If conversation was ended, reactivate it
            if ($conversation['status'] === 'ended') {
                $this->chatModel->reactivateConversation($conversationId);
            }
            
            // Add visitor message
            $messageId = $this->chatModel->addMessage($conversationId, 'client', null, $message);
            
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
            
            // Generate bot response if conversation is still in bot mode
            if ($conversation['status'] === 'bot') {
                // Use existing ChatModel method
                $botResponseText = $this->chatModel->processBotMessage($message, $conversationId);
                
                if ($botResponseText === null) {
                    // Friendly fallback and suggest human assistance
                    $botResponseText = "I'm not sure I have the exact answer to that. You can tap 'Get Help' to connect with a customer service agent, or try asking about pricing, booking, cancellation, contact, or fleet.";
                }
                
                $botMessageId = $this->chatModel->addMessage($conversationId, 'bot', null, $botResponseText);
                $response['bot_response'] = [
                    'id' => $botMessageId,
                    'conversation_id' => $conversationId,
                    'sender_type' => 'bot',
                    'message' => $botResponseText,
                    'sent_at' => date('Y-m-d H:i:s')
                ];
            }
            
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Request human assistance for visitor
     */
    public function requestVisitorHumanAssistance() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $conversationId = $input['conversation_id'] ?? null;
            $sessionId = $input['session_id'] ?? null;
            
            if (!$conversationId || !$sessionId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                return;
            }
            
            // Verify session owns this conversation
            $conversation = $this->chatModel->getVisitorConversationStatus($conversationId, $sessionId);
            if (!$conversation) {
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
     * End visitor conversation
     */
    public function endVisitorConversation() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $conversationId = $input['conversation_id'] ?? null;
            $sessionId = $input['session_id'] ?? null;
            
            if (!$conversationId || !$sessionId) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                return;
            }
            
            // Verify session owns this conversation
            $conversation = $this->chatModel->getVisitorConversationStatus($conversationId, $sessionId);
            if (!$conversation) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                return;
            }
            
            // End conversation
            $this->chatModel->endConversation($conversationId, 'client');
            
            // Add system message
            $endMessage = "This conversation has been ended. Thank you for using KingLang Bus Rental service! Feel free to start a new conversation if you need further assistance.";
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
}
?>
