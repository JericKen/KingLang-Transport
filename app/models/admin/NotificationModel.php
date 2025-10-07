<?php
require_once __DIR__ . "/../../../config/database.php";

class NotificationModel {
    private $conn;
    
    public function __construct() {
        global $pdo;
        $this->conn = $pdo;
        $this->ensureNotificationsTableExists();
    }
    
    private function ensureNotificationsTableExists() {
        try {
            $query = "CREATE TABLE IF NOT EXISTS admin_notifications (
                notification_id INT AUTO_INCREMENT PRIMARY KEY,
                type VARCHAR(50) NOT NULL,
                message TEXT NOT NULL,
                reference_id INT,
                is_read BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $this->conn->exec($query);
            return true;
        } catch (PDOException $e) {
            error_log("Error creating notifications table: " . $e->getMessage());
            return false;
        }
    }
    
    public function addNotification($type, $message, $reference_id = null) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO admin_notifications (type, message, reference_id) VALUES (:type, :message, :reference_id)");
            $stmt->execute([
                ":type" => $type,
                ":message" => $message,
                ":reference_id" => $reference_id
            ]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error adding notification: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUnreadNotifications() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM admin_notifications WHERE is_read = 0 ORDER BY created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting unread notifications: " . $e->getMessage());
            return [];
        }
    }
    
    public function getNotificationCount() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting notification count: " . $e->getMessage());
            return 0;
        }
    }
    
    public function markAsRead($notification_id) {
        try {
            $stmt = $this->conn->prepare("UPDATE admin_notifications SET is_read = 1 WHERE notification_id = :notification_id");
            $stmt->execute([":notification_id" => $notification_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Error marking notification as read: " . $e->getMessage());
            return false;
        }
    }
    
    public function markAllAsRead() {
        try {
            $stmt = $this->conn->prepare("UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error marking all notifications as read: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllNotifications($limit = 20) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT :limit");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all notifications: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTotalNotificationCount() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM admin_notifications");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting total notification count: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getAllNotificationsWithPagination($limit = 20, $offset = 0) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM admin_notifications 
                                          ORDER BY created_at DESC 
                                          LIMIT :limit OFFSET :offset");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting paginated notifications: " . $e->getMessage());
            return [];
        }
    }
} 