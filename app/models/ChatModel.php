<?php

require_once __DIR__ . '/../../config/database.php';



class ChatModel {

    private $pdo;

    

    public function __construct() {

        global $pdo;

        $this->pdo = $pdo;

    }

    

    /**

     * Create a new conversation for a client

     */

    public function createConversation($clientId) {

        try {

            $stmt = $this->pdo->prepare("

                INSERT INTO conversations (client_id, status) 

                VALUES (?, 'bot')

            ");

            $stmt->execute([$clientId]);

            

            $conversationId = $this->pdo->lastInsertId();

            

            // Add welcome message

            $this->addMessage($conversationId, 'bot', null, 

                "Welcome to KingLang Bus Rental! 🚌 How can I assist you today? You can ask about our services, pricing, booking process, or any other questions you might have."

            );

            

            return $conversationId;

        } catch (PDOException $e) {

            error_log("Error creating conversation: " . $e->getMessage());

            throw new Exception("Failed to create conversation");

        }

    }

    

    /**

     * Add a message to a conversation

     */

    public function addMessage($conversationId, $senderType, $senderId, $message) {

        try {

            $stmt = $this->pdo->prepare("

                INSERT INTO messages (conversation_id, sender_type, sender_id, message) 

                VALUES (?, ?, ?, ?)

            ");

            $stmt->execute([$conversationId, $senderType, $senderId, $message]);

            

            // Update conversation timestamp

            $this->updateConversationTimestamp($conversationId);

            

            return $this->pdo->lastInsertId();

        } catch (PDOException $e) {

            error_log("Error adding message: " . $e->getMessage());

            throw new Exception("Failed to add message");

        }

    }

    

    /**

     * Get all messages for a conversation

     */

    public function getConversationMessages($conversationId) {

        try {

            $stmt = $this->pdo->prepare("

                SELECT m.*, 

                       CASE 

                           WHEN m.sender_type = 'client' THEN CONCAT(u.first_name, ' ', u.last_name)

                           WHEN m.sender_type = 'admin' THEN CONCAT(u.first_name, ' ', u.last_name, ' (Admin)')

                           WHEN m.sender_type = 'bot' THEN 'KingLang Assistant'

                           WHEN m.sender_type = 'system' THEN 'System'

                           ELSE 'Unknown'

                       END as sender_name

                FROM messages m 

                LEFT JOIN conversations c ON m.conversation_id = c.id 

                LEFT JOIN users u ON (

                    (m.sender_type = 'admin' AND c.admin_id = u.user_id) OR 

                    (m.sender_type = 'client' AND c.client_id = u.user_id)

                )

                WHERE m.conversation_id = ? 

                ORDER BY m.sent_at ASC

            ");

            $stmt->execute([$conversationId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error getting conversation messages: " . $e->getMessage());

            return [];

        }

    }

    

    /**

     * Get conversation status and details

     */

    public function getConversationStatus($conversationId) {

        try {

            $stmt = $this->pdo->prepare("

                SELECT c.*, 

                       CONCAT(client.first_name, ' ', client.last_name) as client_name,

                       CONCAT(admin.first_name, ' ', admin.last_name) as admin_name

                FROM conversations c 

                LEFT JOIN users client ON c.client_id = client.user_id

                LEFT JOIN users admin ON c.admin_id = admin.user_id

                WHERE c.id = ?

            ");

            $stmt->execute([$conversationId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error getting conversation status: " . $e->getMessage());

            return null;

        }

    }

    

    /**

     * Get or create conversation for a client

     */

    public function getOrCreateConversation($clientId) {

        try {

            // First, verify the client exists

            $stmt = $this->pdo->prepare("SELECT user_id FROM users WHERE user_id = ?");

            $stmt->execute([$clientId]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            

            if (!$user) {

                throw new Exception("User not found");

            }

            

            // First, try to get an existing active conversation

            $stmt = $this->pdo->prepare("

                SELECT id FROM conversations 

                WHERE client_id = ? AND status != 'ended' 

                ORDER BY updated_at DESC 

                LIMIT 1

            ");

            $stmt->execute([$clientId]);

            $conversation = $stmt->fetch(PDO::FETCH_ASSOC);

            

            if ($conversation) {

                return $conversation['id'];

            }

            

            // If no active conversation, create a new one

            return $this->createConversation($clientId);

        } catch (PDOException $e) {

            error_log("Error getting or creating conversation: " . $e->getMessage());

            throw new Exception("Failed to get or create conversation: " . $e->getMessage());

        }

    }

    

    /**

     * Process bot response to a message

     */

    public function processBotMessage($message, $conversationId) {

        try {

            $lowerMessage = strtolower($message);

            

            // Check for bot responses in database

            $stmt = $this->pdo->prepare("

                SELECT response FROM bot_responses 

                WHERE is_active = 1 AND LOWER(?) LIKE CONCAT('%', LOWER(keyword), '%')

                ORDER BY LENGTH(keyword) DESC 

                LIMIT 1

            ");

            $stmt->execute([$message]);

            $botResponse = $stmt->fetch(PDO::FETCH_ASSOC);

            

            if ($botResponse) {

                return $botResponse['response'];

            }

            

            // If no specific response found, return null to indicate human assistance needed

            return null;

        } catch (PDOException $e) {

            error_log("Error processing bot message: " . $e->getMessage());

            return null;

        }

    }

    

    /**

     * Request human assistance for a conversation

     */

    public function requestHumanAssistance($conversationId) {

        try {

            $stmt = $this->pdo->prepare("

                UPDATE conversations 

                SET status = 'human_requested' 

                WHERE id = ?

            ");

            return $stmt->execute([$conversationId]);

        } catch (PDOException $e) {

            error_log("Error requesting human assistance: " . $e->getMessage());

            return false;

        }

    }

    

    /**

     * Assign admin to a conversation

     */

    public function assignAdminToConversation($conversationId, $adminId) {

        try {

            $stmt = $this->pdo->prepare("

                UPDATE conversations 

                SET admin_id = ?, status = 'human_assigned' 

                WHERE id = ?

            ");

            return $stmt->execute([$adminId, $conversationId]);

        } catch (PDOException $e) {

            error_log("Error assigning admin to conversation: " . $e->getMessage());

            return false;

        }

    }

    

    /**

     * End a conversation

     */

    public function endConversation($conversationId, $endedBy) {

        try {

            $stmt = $this->pdo->prepare("

                UPDATE conversations 

                SET status = 'ended', ended_by = ?, ended_at = NOW() 

                WHERE id = ?

            ");

            return $stmt->execute([$endedBy, $conversationId]);

        } catch (PDOException $e) {

            error_log("Error ending conversation: " . $e->getMessage());

            return false;

        }

    }

    

    /**

     * Reactivate an ended conversation

     */

    public function reactivateConversation($conversationId) {

        try {

            $stmt = $this->pdo->prepare("

                UPDATE conversations 

                SET status = 'bot', admin_id = NULL, ended_by = NULL, ended_at = NULL 

                WHERE id = ?

            ");

            return $stmt->execute([$conversationId]);

        } catch (PDOException $e) {

            error_log("Error reactivating conversation: " . $e->getMessage());

            return false;

        }

    }

    

    /**

     * Get pending conversations (waiting for admin)

     */

    public function getPendingConversations() {

        try {

            $stmt = $this->pdo->prepare("

                SELECT c.id, c.client_id, c.session_id, c.started_at, c.updated_at,

                       COALESCE(CONCAT(u.first_name, ' ', u.last_name), 'Visitor') as client_name,

                       (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY sent_at DESC LIMIT 1) as last_message,

                       (SELECT sent_at FROM messages WHERE conversation_id = c.id ORDER BY sent_at DESC LIMIT 1) as last_message_time

                FROM conversations c

                LEFT JOIN users u ON c.client_id = u.user_id

                WHERE c.status = 'human_requested'

                ORDER BY c.updated_at DESC

            ");

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error getting pending conversations: " . $e->getMessage());

            return [];

        }

    }

    

    /**

     * Get active conversations for an admin

     */

    public function getActiveConversations($adminId = null) {

        try {

            if ($adminId) {

                // Get conversations assigned to specific admin

                $stmt = $this->pdo->prepare("

                    SELECT c.id, c.client_id, c.session_id, c.started_at, c.updated_at,

                           COALESCE(CONCAT(u.first_name, ' ', u.last_name), 'Visitor') as client_name,

                           (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY sent_at DESC LIMIT 1) as last_message,

                           (SELECT sent_at FROM messages WHERE conversation_id = c.id ORDER BY sent_at DESC LIMIT 1) as last_message_time

                    FROM conversations c

                    LEFT JOIN users u ON c.client_id = u.user_id

                    WHERE c.admin_id = ? AND c.status = 'human_assigned'

                    ORDER BY c.updated_at DESC

                ");

                $stmt->execute([$adminId]);

            } else {

                // Get all active conversations (for admin dashboard)

                $stmt = $this->pdo->prepare("

                    SELECT c.id, c.client_id, c.session_id, c.started_at, c.updated_at,

                           COALESCE(CONCAT(u.first_name, ' ', u.last_name), 'Visitor') as client_name,

                           CONCAT(admin.first_name, ' ', admin.last_name) as admin_name,

                           (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY sent_at DESC LIMIT 1) as last_message,

                           (SELECT sent_at FROM messages WHERE conversation_id = c.id ORDER BY sent_at DESC LIMIT 1) as last_message_time

                    FROM conversations c

                    LEFT JOIN users u ON c.client_id = u.user_id

                    LEFT JOIN users admin ON c.admin_id = admin.user_id

                    WHERE c.status = 'human_assigned'

                    ORDER BY c.updated_at DESC

                ");

                $stmt->execute();

            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error getting active conversations: " . $e->getMessage());

            return [];

        }

    }

    

    /**

     * Get ended conversations

     */

    public function getEndedConversations($limit = 50) {

        try {

            $stmt = $this->pdo->prepare("

                SELECT c.id, c.client_id, c.session_id, c.started_at, c.updated_at, c.ended_at, c.ended_by,

                       COALESCE(CONCAT(u.first_name, ' ', u.last_name), 'Visitor') as client_name,

                       CONCAT(admin.first_name, ' ', admin.last_name) as admin_name,

                       (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY sent_at DESC LIMIT 1) as last_message

                FROM conversations c

                LEFT JOIN users u ON c.client_id = u.user_id

                LEFT JOIN users admin ON c.admin_id = admin.user_id

                WHERE c.status = 'ended'

                ORDER BY c.ended_at DESC

                LIMIT " . (int)$limit

            );

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error getting ended conversations: " . $e->getMessage());

            return [];

        }

    }

    

    /**

     * Update conversation timestamp

     */

    private function updateConversationTimestamp($conversationId) {

        try {

            $stmt = $this->pdo->prepare("

                UPDATE conversations 

                SET updated_at = NOW() 

                WHERE id = ?

            ");

            $stmt->execute([$conversationId]);

        } catch (PDOException $e) {

            error_log("Error updating conversation timestamp: " . $e->getMessage());

        }

    }

    

    /**

     * Get bot responses for admin management

     */

    public function getBotResponses() {

        try {

            $stmt = $this->pdo->prepare("

                SELECT * FROM bot_responses 

                ORDER BY category, keyword

            ");

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error getting bot responses: " . $e->getMessage());

            return [];

        }

    }

    

    /**

     * Add or update bot response

     */

    public function saveBotResponse($id, $keyword, $response, $category, $isActive = 1) {

        try {

            if ($id) {

                // Update existing response

                $stmt = $this->pdo->prepare("

                    UPDATE bot_responses 

                    SET keyword = ?, response = ?, category = ?, is_active = ? 

                    WHERE id = ?

                ");

                return $stmt->execute([$keyword, $response, $category, $isActive, $id]);

            } else {

                // Create new response

                $stmt = $this->pdo->prepare("

                    INSERT INTO bot_responses (keyword, response, category, is_active) 

                    VALUES (?, ?, ?, ?)

                ");

                return $stmt->execute([$keyword, $response, $category, $isActive]);

            }

        } catch (PDOException $e) {

            error_log("Error saving bot response: " . $e->getMessage());

            return false;

        }

    }

    

    /**

     * Delete bot response

     */

    public function deleteBotResponse($id) {

        try {

            $stmt = $this->pdo->prepare("DELETE FROM bot_responses WHERE id = ?");

            return $stmt->execute([$id]);

        } catch (PDOException $e) {

            error_log("Error deleting bot response: " . $e->getMessage());

            return false;

        }

    }

    

    /**

     * Get conversation statistics

     */

    public function getConversationStats() {

        try {

            $stats = [];

            

            // Total conversations today (or recent if none today)

            $stmt = $this->pdo->prepare("

                SELECT COUNT(*) as count FROM conversations 

                WHERE DATE(started_at) = CURDATE()

            ");

            $stmt->execute();

            $todayCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            

            if ($todayCount == 0) {

                // If no conversations today, show recent conversations (last 7 days)

                $stmt = $this->pdo->prepare("

                    SELECT COUNT(*) as count FROM conversations 

                    WHERE started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)

                ");

                $stmt->execute();

                $stats['conversations_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            } else {

                $stats['conversations_today'] = $todayCount;

            }

            

            // Active conversations

            $stmt = $this->pdo->prepare("

                SELECT COUNT(*) as count FROM conversations 

                WHERE status = 'human_assigned'

            ");

            $stmt->execute();

            $stats['active_conversations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            

            // Pending conversations

            $stmt = $this->pdo->prepare("

                SELECT COUNT(*) as count FROM conversations 

                WHERE status = 'human_requested'

            ");

            $stmt->execute();

            $stats['pending_conversations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            

            // Ended conversations

            $stmt = $this->pdo->prepare("

                SELECT COUNT(*) as count FROM conversations 

                WHERE status = 'ended'

            ");

            $stmt->execute();

            $stats['ended_conversations'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            

            // Total messages today (or recent if none today)

            $stmt = $this->pdo->prepare("

                SELECT COUNT(*) as count FROM messages 

                WHERE DATE(sent_at) = CURDATE()

            ");

            $stmt->execute();

            $todayMessages = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            

            if ($todayMessages == 0) {

                // If no messages today, show recent messages (last 7 days)

                $stmt = $this->pdo->prepare("

                    SELECT COUNT(*) as count FROM messages 

                    WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)

                ");

                $stmt->execute();

                $stats['messages_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            } else {

                $stats['messages_today'] = $todayMessages;

            }

            

            return $stats;

        } catch (PDOException $e) {

            error_log("Error getting conversation stats: " . $e->getMessage());

            return [];

        }

    }

    /**
     * Create a new conversation for a visitor (non-authenticated)
     */
    public function createVisitorConversation($sessionId) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO conversations (client_id, status, session_id) 
                VALUES (NULL, 'bot', ?)
            ");
            $stmt->execute([$sessionId]);
            
            $conversationId = $this->pdo->lastInsertId();
            
            // Add welcome message
            $this->addMessage($conversationId, 'bot', null, 
                "Welcome to KingLang Bus Rental! 🚌 I'm here to help you with any questions about our services. You can ask about pricing, booking process, available buses, or anything else you'd like to know!"
            );
            
            return $conversationId;
        } catch (PDOException $e) {
            error_log("Error creating visitor conversation: " . $e->getMessage());
            throw new Exception("Failed to create visitor conversation");
        }
    }

    /**
     * Get or create conversation for a visitor
     */
    public function getOrCreateVisitorConversation($sessionId) {
        try {
            // First, try to get an existing active conversation for this session
            $stmt = $this->pdo->prepare("
                SELECT id FROM conversations 
                WHERE session_id = ? AND status != 'ended' 
                ORDER BY updated_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$sessionId]);
            $conversation = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($conversation) {
                return $conversation['id'];
            }
            
            // If no active conversation, create a new one
            return $this->createVisitorConversation($sessionId);
        } catch (PDOException $e) {
            error_log("Error getting or creating visitor conversation: " . $e->getMessage());
            throw new Exception("Failed to get or create visitor conversation: " . $e->getMessage());
        }
    }

    /**
     * Get visitor conversation status and details
     */
    public function getVisitorConversationStatus($conversationId, $sessionId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT c.*, 
                       CONCAT(admin.first_name, ' ', admin.last_name) as admin_name
                FROM conversations c 
                LEFT JOIN users admin ON c.admin_id = admin.user_id
                WHERE c.id = ? AND c.session_id = ?
            ");
            $stmt->execute([$conversationId, $sessionId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting visitor conversation status: " . $e->getMessage());
            return null;
        }
    }

}

?>

