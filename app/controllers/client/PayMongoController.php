<?php
require_once __DIR__ . "/../../services/PayMongoService.php";
require_once __DIR__ . "/../../models/client/BookingModel.php";

class PayMongoController {
    private $payMongoService;
    private $bookingModel;
    
    public function __construct() {
        $this->payMongoService = new PayMongoService();
        $this->bookingModel = new Booking();
    }
    
    /**
     * Handle PayMongo webhook events
     */
    public function handleWebhook() {
        // Set response headers
        header('Content-Type: application/json');
        
        try {
            // Get raw POST data
            $payload = file_get_contents('php://input');
            $signature = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';
            
            if (empty($payload)) {
                http_response_code(400);
                echo json_encode(['error' => 'Empty payload']);
                return;
            }
            
            // Process webhook
            $result = $this->payMongoService->handleWebhook($payload, $signature);
            
            if ($result['success']) {
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => $result['message']]);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => $result['message']]);
            }
            
        } catch (Exception $e) {
            error_log("PayMongo webhook error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
        }
    }
    
    /**
     * Handle successful payment callback
     */
    public function handleSuccess() {
        session_start();
        
        try {
            $checkout_session_id = $_GET['session_id'] ?? null;
            $booking_id = $_GET['booking_id'] ?? null;
            
            if (!$checkout_session_id || !$booking_id) {
                $this->redirectWithError('Invalid payment parameters');
                return;
            }
            
            // Process successful payment
            $result = $this->payMongoService->handleSuccessfulPayment($checkout_session_id, $booking_id);
            
            if ($result['success']) {
                // Get booking details for display
                $booking = $this->bookingModel->getBooking($booking_id, $_SESSION['user_id'] ?? 0);
                
                // Set success message
                $_SESSION['payment_success'] = [
                    'message' => $result['message'],
                    'amount' => $result['amount'],
                    'payment_id' => $result['payment_id'],
                    'booking_id' => $booking_id,
                    'booking' => $booking
                ];
                
                // Redirect to success page
                header('Location: /paymongo/success-page');
                exit;
            } else {
                $this->redirectWithError($result['message']);
            }
            
        } catch (Exception $e) {
            error_log("PayMongo success handler error: " . $e->getMessage());
            $this->redirectWithError('Payment processing failed');
        }
    }
    
    /**
     * Handle cancelled payment callback
     */
    public function handleCancel() {
        session_start();
        
        try {
            $checkout_session_id = $_GET['session_id'] ?? null;
            $booking_id = $_GET['booking_id'] ?? null;
            
            if ($checkout_session_id && $booking_id) {
                // Process cancelled payment
                $result = $this->payMongoService->handleCancelledPayment($checkout_session_id, $booking_id);
                
                $_SESSION['payment_cancelled'] = [
                    'message' => 'Payment was cancelled',
                    'booking_id' => $booking_id
                ];
            }
            
            // Redirect to booking requests page
            header('Location: /home/booking-requests?payment=cancelled');
            exit;
            
        } catch (Exception $e) {
            error_log("PayMongo cancel handler error: " . $e->getMessage());
            header('Location: /home/booking-requests?payment=error');
            exit;
        }
    }
    
    /**
     * Display payment success page
     */
    public function showSuccessPage() {
        session_start();
        
        if (!isset($_SESSION['payment_success'])) {
            header('Location: /home/booking-requests');
            exit;
        }
        
        $paymentData = $_SESSION['payment_success'];
        unset($_SESSION['payment_success']); // Clear the session data
        
        require_once __DIR__ . "/../../views/client/paymongo_success.php";
    }
    
    /**
     * Get payment status for AJAX requests
     */
    public function getPaymentStatus() {
        header('Content-Type: application/json');
        
        try {
            $checkout_session_id = $_GET['session_id'] ?? null;
            
            if (!$checkout_session_id) {
                echo json_encode(['success' => false, 'message' => 'Session ID required']);
                return;
            }
            
            // Get payment details
            $payment = $this->payMongoService->getPaymentByCheckoutSession($checkout_session_id);
            
            if ($payment) {
                echo json_encode([
                    'success' => true,
                    'payment' => [
                        'id' => $payment['payment_id'],
                        'status' => $payment['status'],
                        'amount' => $payment['amount'],
                        'booking_id' => $payment['booking_id'],
                        'payment_method' => $payment['payment_method']
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Payment not found']);
            }
            
        } catch (Exception $e) {
            error_log("PayMongo status check error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error checking payment status']);
        }
    }
    
    /**
     * Redirect with error message
     */
    private function redirectWithError($message) {
        session_start();
        $_SESSION['payment_error'] = $message;
        header('Location: /home/booking-requests?payment=error');
        exit;
    }
}
?>
