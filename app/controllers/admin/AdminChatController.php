<?php

require_once __DIR__ . '/../../models/ChatModel.php';

require_once __DIR__ . '/../../../config/database.php';



class AdminChatController {

    private $chatModel;

    

    public function __construct() {
        // Ensure admin is authenticated
        if (!is_admin_authenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Admin not authenticated']);
            exit();
        }
        
        $this->chatModel = new ChatModel();
    }

    

    /**

     * Get chat dashboard data

     */

    public function getDashboard() {

        try {

            $stats = $this->chatModel->getConversationStats();

            $pendingConversations = $this->chatModel->getPendingConversations();

            $activeConversations = $this->chatModel->getActiveConversations();

            

            echo json_encode([

                'success' => true,

                'stats' => $stats,

                'pending' => $pendingConversations,

                'active' => $activeConversations

            ]);

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(['error' => $e->getMessage()]);

        }

    }

    

    /**

     * Get pending conversations

     */

    public function getPendingConversations() {

        try {

            $conversations = $this->chatModel->getPendingConversations();

            

            echo json_encode([

                'success' => true,

                'conversations' => $conversations

            ]);

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(['error' => $e->getMessage()]);

        }

    }

    

    /**

     * Get active conversations for current admin

     */

    public function getActiveConversations() {

        try {

            if (!isset($_SESSION['admin_id'])) {

                http_response_code(401);

                echo json_encode(['error' => 'Admin not authenticated']);

                return;

            }

            

            // Get all active conversations for admin dashboard

            $conversations = $this->chatModel->getActiveConversations();

            

            echo json_encode([

                'success' => true,

                'conversations' => $conversations

            ]);

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(['error' => $e->getMessage()]);

        }

    }

    

    /**

     * Get ended conversations

     */

    public function getEndedConversations() {

        try {

            $conversations = $this->chatModel->getEndedConversations();

            

            echo json_encode([

                'success' => true,

                'conversations' => $conversations

            ]);

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(['error' => $e->getMessage()]);

        }

    }

    

    /**

     * Assign admin to conversation

     */

    public function assignConversation() {

        try {

            if (!isset($_SESSION['admin_id'])) {

                http_response_code(401);

                echo json_encode(['error' => 'Admin not authenticated']);

                return;

            }

            

            $input = json_decode(file_get_contents('php://input'), true);

            $conversationId = $input['conversation_id'] ?? null;

            

            if (!$conversationId) {

                http_response_code(400);

                echo json_encode(['error' => 'Missing conversation ID']);

                return;

            }

            

            // Assign admin to conversation

            $success = $this->chatModel->assignAdminToConversation($conversationId, $_SESSION['admin_id']);

            

            if ($success) {

                // Add system message

                $adminName = $_SESSION['admin_name'];

                $systemMessage = "Customer service agent {$adminName} has joined the conversation and will assist you now.";

                $this->chatModel->addMessage($conversationId, 'system', null, $systemMessage);

                

                echo json_encode([

                    'success' => true,

                    'message' => 'Conversation assigned successfully'

                ]);

            } else {

                http_response_code(500);

                echo json_encode(['error' => 'Failed to assign conversation']);

            }

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(['error' => $e->getMessage()]);

        }

    }

    

    /**

     * Send admin message

     */

    public function sendMessage() {

        try {

            if (!isset($_SESSION['admin_id'])) {

                http_response_code(401);

                echo json_encode(['error' => 'Admin not authenticated']);

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

            

            // Verify admin is assigned to this conversation

            $conversation = $this->chatModel->getConversationStatus($conversationId);

            if (!$conversation || $conversation['admin_id'] != $_SESSION['admin_id']) {

                http_response_code(403);

                echo json_encode(['error' => 'Access denied']);

                return;

            }

            

            // Add admin message

            $messageId = $this->chatModel->addMessage($conversationId, 'admin', $_SESSION['admin_id'], $message);

            

            echo json_encode([

                'success' => true,

                'message_id' => $messageId,

                'message' => [

                    'id' => $messageId,

                    'conversation_id' => $conversationId,

                    'sender_type' => 'admin',

                    'message' => $message,

                    'sent_at' => date('Y-m-d H:i:s')

                ]

            ]);

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(['error' => $e->getMessage()]);

        }

    }

    

    /**

     * Get conversation messages

     */

    public function getConversationMessages($conversationId) {

        try {

            if (!isset($_SESSION['admin_id'])) {

                http_response_code(401);

                echo json_encode(['error' => 'Admin not authenticated']);

                return;

            }

            

            $messages = $this->chatModel->getConversationMessages($conversationId);

            $conversation = $this->chatModel->getConversationStatus($conversationId);

            

            echo json_encode([

                'success' => true,

                'messages' => $messages,

                'conversation' => $conversation

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

            if (!isset($_SESSION['admin_id'])) {

                http_response_code(401);

                echo json_encode(['error' => 'Admin not authenticated']);

                return;

            }

            

            $input = json_decode(file_get_contents('php://input'), true);

            $conversationId = $input['conversation_id'] ?? null;

            $reason = $input['reason'] ?? 'Admin ended the conversation';

            

            if (!$conversationId) {

                http_response_code(400);

                echo json_encode(['error' => 'Missing conversation ID']);

                return;

            }

            

            // Verify admin is assigned to this conversation

            $conversation = $this->chatModel->getConversationStatus($conversationId);

            if (!$conversation || $conversation['admin_id'] != $_SESSION['admin_id']) {

                http_response_code(403);

                echo json_encode(['error' => 'Access denied']);

                return;

            }

            

            // End conversation

            $this->chatModel->endConversation($conversationId, 'admin');

            

            // Add system message

            $endMessage = "This conversation has been ended by the support agent. {$reason}. Thank you for using KingLang Bus Rental service!";

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

     * Get bot responses for management

     */

    public function getBotResponses() {

        try {

            $responses = $this->chatModel->getBotResponses();

            

            echo json_encode([

                'success' => true,

                'responses' => $responses

            ]);

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(['error' => $e->getMessage()]);

        }

    }

    

    /**

     * Save bot response

     */

    public function saveBotResponse() {

        try {

            $input = json_decode(file_get_contents('php://input'), true);

            $id = $input['id'] ?? null;

            $keyword = trim($input['keyword'] ?? '');

            $response = trim($input['response'] ?? '');

            $category = trim($input['category'] ?? '');

            $isActive = $input['is_active'] ?? 1;

            

            if (empty($keyword) || empty($response)) {

                http_response_code(400);

                echo json_encode(['error' => 'Keyword and response are required']);

                return;

            }

            

            $success = $this->chatModel->saveBotResponse($id, $keyword, $response, $category, $isActive);

            

            if ($success) {

                echo json_encode([

                    'success' => true,

                    'message' => $id ? 'Bot response updated successfully' : 'Bot response created successfully'

                ]);

            } else {

                http_response_code(500);

                echo json_encode(['error' => 'Failed to save bot response']);

            }

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(['error' => $e->getMessage()]);

        }

    }

    

    /**

     * Delete bot response

     */

    public function deleteBotResponse($id) {

        try {

            $success = $this->chatModel->deleteBotResponse($id);

            

            if ($success) {

                echo json_encode([

                    'success' => true,

                    'message' => 'Bot response deleted successfully'

                ]);

            } else {

                http_response_code(500);

                echo json_encode(['error' => 'Failed to delete bot response']);

            }

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(['error' => $e->getMessage()]);

        }

    }

    

    /**

     * Get conversation statistics

     */

    public function getStats() {

        try {

            $stats = $this->chatModel->getConversationStats();

            

            echo json_encode([

                'success' => true,

                'stats' => $stats

            ]);

        } catch (Exception $e) {

            http_response_code(500);

            echo json_encode(['error' => $e->getMessage()]);

        }

    }


    public function viewConversation($conversationId) {
        try {
            if (!isset($_SESSION['admin_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Admin not authenticated']);
                return;
            }

            // Optional: track that an admin viewed the conversation (audit/logging)
            echo json_encode([
                'success' => true,
                'message' => 'Conversation marked as viewed'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

}

?>

