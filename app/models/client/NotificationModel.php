<?php
require_once __DIR__ . "/../../../config/database.php";

class ClientNotificationModel {
    private $conn;
    
    public function __construct() {
        global $pdo;
        $this->conn = $pdo;
        $this->ensureNotificationsTableExists();
    }
    
    private function ensureNotificationsTableExists() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS client_notifications (
                notification_id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                type VARCHAR(50) NOT NULL,
                message TEXT NOT NULL,
                reference_id INT,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $this->conn->exec($query);
            return true;
        } catch (PDOException $e) {
            error_log("Error creating client notifications table: " . $e->getMessage());
            return false;
        }
    }
    
    public function addNotification($user_id, $type, $message, $reference_id = null) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO client_notifications (user_id, type, message, reference_id) 
                                        VALUES (:user_id, :type, :message, :reference_id)");
            $stmt->execute([
                ":user_id" => $user_id,
                ":type" => $type,
                ":message" => $message,
                ":reference_id" => $reference_id
            ]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error adding client notification: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUnreadNotifications($user_id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM client_notifications 
                                        WHERE user_id = :user_id AND is_read = 0 
                                        ORDER BY created_at DESC");
            $stmt->execute([":user_id" => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting unread client notifications: " . $e->getMessage());
            return [];
        }
    }
    
    public function getNotificationCount($user_id) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM client_notifications 
                                          WHERE user_id = :user_id AND is_read = 0");
            $stmt->execute([":user_id" => $user_id]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting client notification count: " . $e->getMessage());
            return 0;
        }
    }
    
    public function markAsRead($notification_id) {
        try {
            $stmt = $this->conn->prepare("UPDATE client_notifications SET is_read = 1 
                                          WHERE notification_id = :notification_id");
            $stmt->execute([":notification_id" => $notification_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Error marking client notification as read: " . $e->getMessage());
            return false;
        }
    }
    
    public function markAllAsRead($user_id) {
        try {
            $stmt = $this->conn->prepare("UPDATE client_notifications SET is_read = 1 
                                          WHERE user_id = :user_id AND is_read = 0");
            $stmt->execute([":user_id" => $user_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Error marking all client notifications as read: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllNotifications($user_id, $limit = 20) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM client_notifications 
                                          WHERE user_id = :user_id 
                                          ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all client notifications: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTotalNotificationCount($user_id) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM client_notifications 
                                          WHERE user_id = :user_id");
            $stmt->execute([":user_id" => $user_id]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting total client notification count: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getAllNotificationsWithPagination($user_id, $limit = 20, $offset = 0) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM client_notifications 
                                          WHERE user_id = :user_id 
                                          ORDER BY created_at DESC 
                                          LIMIT :limit OFFSET :offset");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting paginated client notifications: " . $e->getMessage());
            return [];
        }
    }
} 