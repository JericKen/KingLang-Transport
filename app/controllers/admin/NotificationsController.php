<?php
require_once __DIR__ . "/../../../app/models/admin/NotificationModel.php";

class NotificationsController {
    private $notificationModel;
    
    public function __construct() {
        $this->notificationModel = new NotificationModel();
        
        // Check if the user is logged in and is an admin
        $requestUri = $_SERVER['REQUEST_URI'];
        if (strpos($requestUri, '/admin') === 0 && 
            strpos($requestUri, '/admin/login') === false && 
            strpos($requestUri, '/admin/submit-login') === false) {
            
            if (!isset($_SESSION["role"]) || ($_SESSION["role"] !== "Super Admin" && $_SESSION["role"] !== "Admin")) {
                header("Location: /admin/login");
                exit();
            }
        }
    }
    
    public function index() {
        // Get all notifications for the notifications page
        $notifications = $this->notificationModel->getAllNotifications(50); // Show last 50 notifications
        
        require_once __DIR__ . "/../../views/admin/notifications/index.php";
    }
    
    public function getNotifications() {
        if (!isset($_SESSION["role"]) || ($_SESSION["role"] !== "Super Admin" && $_SESSION["role"] !== "Admin")) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
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
        $notifications = $this->notificationModel->getAllNotificationsWithPagination($limit, $offset);
        $unreadCount = $this->notificationModel->getNotificationCount();
        $total = $this->notificationModel->getTotalNotificationCount();
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
        if (!isset($_SESSION["role"]) || ($_SESSION["role"] !== "Super Admin" && $_SESSION["role"] !== "Admin")) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
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
        $total = $this->notificationModel->getTotalNotificationCount();
        $total_pages = ceil($total / $limit);
        
        // Get notifications for this page
        $notifications = $this->notificationModel->getAllNotificationsWithPagination($limit, $offset);
        
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
            // Check content type for JSON
            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
            
            // Both JSON and form data requests are handled the same way for this endpoint
            $success = $this->notificationModel->markAllAsRead();
            
            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit;
        }
        
        // Invalid request
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
        exit;
    }
    
    // Method to add a test notification
    public function addTestNotification() {
        if (!isset($_SESSION["role"]) || ($_SESSION["role"] !== "Super Admin" && $_SESSION["role"] !== "Admin")) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        $result = $this->notificationModel->addNotification(
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