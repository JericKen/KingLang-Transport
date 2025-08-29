<?php
require_once __DIR__ . "/../../models/client/NotificationModel.php";

class NotificationsController {
    private $notificationModel;
    
    public function __construct() {
        $this->notificationModel = new ClientNotificationModel();
        
        // Check if the user is logged in as a client
        $requestUri = $_SERVER['REQUEST_URI'];
        if (strpos($requestUri, '/home') === 0 && 
            strpos($requestUri, '/home/login') === false && 
            strpos($requestUri, '/home/signup') === false) {
            
            if (!isset($_SESSION["user_id"])) {
                header("Location: /home/login");
                exit();
            }
        }
    }
    
    // Add the index method to render the notifications view
    public function index() {
        if (!isset($_SESSION["user_id"])) {
            header("Location: /home/login");
            exit();
        }
        
        require_once __DIR__ . "/../../views/client/notifications.php";
    }
    
    public function getNotifications() {
        if (!isset($_SESSION["user_id"])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        $user_id = $_SESSION["user_id"];
        $page = 1;
        $limit = 20;
        
        // Check request method
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check content type for JSON
            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
            
            if (strpos($contentType, 'application/json') !== false) {
                // Handle JSON request
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
                
                if (isset($data['page'])) {
                    $page = (int)$data['page'];
                }
                
                if (isset($data['limit'])) {
                    $limit = (int)$data['limit'];
                }
            }
        }
        
        // Validate page and limit
        if ($page < 1) $page = 1;
        if ($limit < 1 || $limit > 100) $limit = 20;
        
        // Calculate offset
        $offset = ($page - 1) * $limit;
        
        // Get notifications for this page
        $notifications = $this->notificationModel->getAllNotificationsWithPagination($user_id, $limit, $offset);
        $unreadCount = $this->notificationModel->getNotificationCount($user_id);
        $total = $this->notificationModel->getTotalNotificationCount($user_id);
        $total_pages = ceil($total / $limit);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'pagination' => [
                'total_records' => $total,
                'total_pages' => $total_pages,
                'current_page' => $page,
                'limit' => $limit
            ]
        ]);
    }
    
    public function getAllNotificationsWithPagination() {
        if (!isset($_SESSION["user_id"])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        $user_id = $_SESSION["user_id"];
        $page = 1;
        $limit = 20;
        
        // Check request method
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check content type for JSON
            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
            
            if (strpos($contentType, 'application/json') !== false) {
                // Handle JSON request
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
                
                if (isset($data['page'])) {
                    $page = (int)$data['page'];
                }
                
                if (isset($data['limit'])) {
                    $limit = (int)$data['limit'];
                }
            }
        } else {
            // Handle GET request
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        }
        
        // Validate page and limit
        if ($page < 1) $page = 1;
        if ($limit < 1 || $limit > 100) $limit = 20;
        
        // Calculate offset
        $offset = ($page - 1) * $limit;
        
        // Get total count for pagination
        $total = $this->notificationModel->getTotalNotificationCount($user_id);
        $total_pages = ceil($total / $limit);
        
        // Get notifications for this page
        $notifications = $this->notificationModel->getAllNotificationsWithPagination($user_id, $limit, $offset);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'notifications' => $notifications,
            'pagination' => [
                'total_records' => $total,
                'total_pages' => $total_pages,
                'current_page' => $page,
                'limit' => $limit
            ]
        ]);
    }
    
    public function markAsRead() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check content type for JSON
            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
            
            if (strpos($contentType, 'application/json') !== false) {
                // Handle JSON request
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
                
                if (isset($data['notification_id'])) {
                    $notification_id = $data['notification_id'];
                    $success = $this->notificationModel->markAsRead($notification_id);
                    
                    header('Content-Type: application/json');
                    echo json_encode(['success' => $success]);
                    exit;
                }
            } else if (isset($_POST['notification_id'])) {
                // Handle form data request for backward compatibility
                $notification_id = $_POST['notification_id'];
                $success = $this->notificationModel->markAsRead($notification_id);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => $success]);
                exit;
            }
        }
        
        // Invalid request
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    public function markAllAsRead() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION["user_id"])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Not authenticated']);
                exit;
            }
            
            $user_id = $_SESSION["user_id"];
            $success = $this->notificationModel->markAllAsRead($user_id);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit;
        }
        
        // Invalid request
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    // Method to add a test notification for the current user
    public function addTestNotification() {
        if (!isset($_SESSION["user_id"])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        $user_id = $_SESSION["user_id"];
        $result = $this->notificationModel->addNotification(
            $user_id,
            'test_notification',
            'This is a test notification to verify the notification system is working properly.',
            null
        );
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => (bool)$result,
            'message' => (bool)$result ? 'Test notification added successfully' : 'Failed to add test notification'
        ]);
    }
} 