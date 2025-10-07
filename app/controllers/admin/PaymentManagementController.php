<?php
require_once __DIR__ . "/../../models/admin/PaymentManagementModel.php";
require_once __DIR__ . "/../AuditTrailTrait.php";

class PaymentManagementController {
    use AuditTrailTrait;
    private $paymentModel;
    
    public function __construct() {
        $this->paymentModel = new PaymentManagementModel();
        
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
        // Load the payment management view
        require_once __DIR__ . "/../../views/admin/payment_management.php";
    }
    
    public function getPayments() {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        
        $status = $data['filter'] ?? 'all';
        $column = $data['sort'] ?? 'payment_id';
        $order = $data['order'] ?? 'DESC';
        $page = (int)($data['page'] ?? 1);
        $limit = (int)($data['limit'] ?? 10);
        $search = $data['search'] ?? '';

        try {

            $payments = $this->paymentModel->getPayments($status, $column, $order, $page, $limit, $search);
            $total = $this->paymentModel->getTotalPayments($status, $search);

            echo json_encode([
                'success' => true,
                'payments' => $payments,
                'total' => $total
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function getPaymentStats() {
        header('Content-Type: application/json');
        
        try {
            $stats = $this->paymentModel->getPaymentStats();
            
            echo json_encode([
                'success' => true,
                'total' => $stats['total'] ?? 0,
                'confirmed' => $stats['confirmed'] ?? 0,
                'pending' => $stats['pending'] ?? 0,
                'rejected' => $stats['rejected'] ?? 0
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function confirmPayment() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['payment_id'])) {
                throw new Exception('Payment ID is required');
            }

            $paymentId = (int)$data['payment_id'];
            
            // Get old payment data for audit trail
            $oldPaymentData = $this->getEntityBeforeUpdate('payments', 'payment_id', $paymentId);
            
            $this->paymentModel->confirmPayment($paymentId);
            
            // Log to audit trail
            $newPaymentData = array_merge($oldPaymentData ?: [], ['status' => 'Approved']);
            $this->logAudit('update', 'payment', $paymentId, $oldPaymentData, $newPaymentData, $_SESSION['admin_id']);

            echo json_encode([
                'success' => true,
                'message' => 'Payment confirmed successfully'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function rejectPayment() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['payment_id']) || !isset($data['reason'])) {
                throw new Exception('Payment ID and reason are required');
            }

            $paymentId = (int)$data['payment_id'];
            $reason = $data['reason'];
            
            // Get old payment data for audit trail
            $oldPaymentData = $this->getEntityBeforeUpdate('payments', 'payment_id', $paymentId);
            
            $this->paymentModel->rejectPayment($paymentId, $reason);
            
            // Log to audit trail
            $newPaymentData = array_merge($oldPaymentData ?: [], ['status' => 'Rejected', 'rejection_reason' => $reason]);
            $this->logAudit('update', 'payment', $paymentId, $oldPaymentData, $newPaymentData, $_SESSION['admin_id']);

            echo json_encode([
                'success' => true,
                'message' => 'Payment rejected successfully'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function recordManualPayment() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['booking_id']) || !isset($data['user_id']) || !isset($data['amount']) || !isset($data['payment_method'])) {
                throw new Exception('Missing required fields');
            }

            $bookingId = (int)$data['booking_id'];
            $userId = (int)$data['user_id'];
            $amount = (float)$data['amount'];
            $paymentMethod = $data['payment_method'];
            $notes = $data['notes'] ?? '';
            
            if ($amount <= 0) {
                throw new Exception('Amount must be greater than zero');
            }
            
            $result = $this->paymentModel->recordManualPayment($bookingId, $userId, $amount, $paymentMethod, $notes);

            echo json_encode([
                'success' => true,
                'message' => 'Payment recorded successfully',
                'payment_id' => $result['payment_id']
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function searchBookings() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $search = $data['search'] ?? '';
            
            $bookings = $this->paymentModel->searchBookings($search);

            echo json_encode([
                'success' => true,
                'bookings' => $bookings
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function searchClients() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $search = $data['search'] ?? '';
            
            $clients = $this->paymentModel->searchClients($search);

            echo json_encode([
                'success' => true,
                'clients' => $clients
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function getBookingDetails() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['booking_id'])) {
                throw new Exception('Booking ID is required');
            }
            
            $bookingId = (int)$data['booking_id'];
            $bookingDetails = $this->paymentModel->getBookingDetails($bookingId);
            
            if (!$bookingDetails) {
                throw new Exception('Booking not found');
            }

            echo json_encode([
                'success' => true,
                'booking' => $bookingDetails
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function viewProof() {
        try {
            $url = isset($_GET['url']) ? urldecode($_GET['url']) : null;
            
            if (!$url) {
                throw new Exception('Proof URL is required');
            }
            
            // Ensure the URL is for an image file
            $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
            
            if (!in_array($extension, $validExtensions)) {
                throw new Exception('Invalid file type');
            }
            
            // For security, ensure the file is in the uploads directory
            $uploadsDir = '/app/uploads/payments/';
            if (strpos($url, $uploadsDir) === false) {
                $url = $uploadsDir . basename($url);
            }
            
            // Display the file
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $url;
            
            if (!file_exists($filePath)) {
                throw new Exception('File not found');
            }
            
            if ($extension === 'pdf') {
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
            } else {
                header('Content-Type: image/' . ($extension === 'jpg' ? 'jpeg' : $extension));
            }
            
            readfile($filePath);
            exit;
        } catch (Exception $e) {
            header('Content-Type: text/html');
            echo '<div style="text-align: center; padding: 50px; font-family: Arial, sans-serif;">';
            echo '<h2 style="color: #d9534f;">Error</h2>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<a href="/admin/payments" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px;">Back to Payments</a>';
            echo '</div>';
        }
    }
} 