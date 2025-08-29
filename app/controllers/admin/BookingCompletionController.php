<?php
require_once __DIR__ . '/../../models/admin/BookingManagementModel.php';
require_once __DIR__ . '/../../models/admin/NotificationModel.php';

class BookingCompletionController {
    private $bookingModel;
    private $notificationModel;
    
    public function __construct() {
        $this->bookingModel = new BookingManagementModel();
        $this->notificationModel = new NotificationModel();
    }
    
    /**
     * Check for bookings that have ended and mark them as completed
     * Also notify admins about partially paid completed bookings
     * This should be run by a cron job
     */
    public function processCompletedBookings() {
        try {
            // Get confirmed bookings that have ended but are not marked as completed
            $stmt = $this->bookingModel->conn->prepare("
                SELECT b.booking_id, b.user_id, b.destination, b.end_of_tour, b.payment_status,
                       CONCAT(u.first_name, ' ', u.last_name) AS client_name
                FROM bookings b
                JOIN users u ON b.user_id = u.user_id
                WHERE b.status = 'Confirmed' 
                  AND b.payment_status IN ('Partially Paid', 'Fully Paid')
                  AND b.end_of_tour < CURDATE()
                  AND b.is_rebooking = 0
                  AND b.is_rebooked = 0
            ");
            $stmt->execute();
            $completedBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $completedCount = 0;
            $partialPaymentCount = 0;
            
            foreach ($completedBookings as $booking) {
                // Update booking status to completed
                $success = $this->markBookingAsCompleted($booking);
                
                if ($success) {
                    $completedCount++;
                    
                    // If partially paid, notify admin
                    if ($booking['payment_status'] === 'Partially Paid') {
                        $this->notifyPartialPayment($booking);
                        $partialPaymentCount++;
                    }
                } else {
                    error_log("Failed to mark booking ID {$booking['booking_id']} as completed");
                }
            }
            
            // Log the results for debugging
            error_log("Completed bookings process: Found " . count($completedBookings) . " bookings, successfully updated $completedCount");
            
            return [
                'success' => true,
                'message' => "Processed $completedCount completed bookings, found $partialPaymentCount with partial payments."
            ];
        } catch (PDOException $e) {
            error_log("Exception in processCompletedBookings: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Error processing completed bookings: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Mark a booking as completed
     */
    private function markBookingAsCompleted($booking) {
        try {
            $stmt = $this->bookingModel->conn->prepare("
                UPDATE bookings 
                SET status = 'Completed',
                    completed_at = NOW()
                WHERE booking_id = :booking_id
            ");
            $result = $stmt->execute([':booking_id' => $booking['booking_id']]);
            
            if (!$result) {
                error_log("Database update failed for booking ID {$booking['booking_id']}");
                return false;
            }
            
            if ($stmt->rowCount() === 0) {
                // error_log("No rows affected when updating booking ID {$booking['booking_id']} to Completed");
                return false;
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error marking booking as completed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Notify admin about a completed booking with partial payment
     */
    private function notifyPartialPayment($booking) {
        try {
            // Create notification for admin
            $message = "Booking ID {$booking['booking_id']} for {$booking['client_name']} is completed but marked as partially paid. Please confirm if full payment was collected in cash on the trip day.";
            
            $this->notificationModel->addNotification(
                'booking_payment',
                $message,
                $booking['booking_id']
            );
            
            return true;
        } catch (PDOException $e) {
            error_log("Error notifying about partial payment: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * API endpoint to manually check for completed bookings
     */
    public function checkCompletions() {
        header("Content-Type: application/json");
        $result = $this->processCompletedBookings();
        echo json_encode($result);
    }
} 