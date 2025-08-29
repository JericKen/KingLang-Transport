<?php
require_once __DIR__ . '/../../models/admin/BookingManagementModel.php';
require_once __DIR__ . '/../../models/admin/NotificationModel.php';
require_once __DIR__ . '/../../models/client/NotificationModel.php';

class BookingDeadlineController {
    private $bookingModel;
    private $adminNotificationModel;
    private $clientNotificationModel;
    
    public function __construct() {
        $this->bookingModel = new BookingManagementModel();
        $this->adminNotificationModel = new NotificationModel();
        $this->clientNotificationModel = new ClientNotificationModel();
    }
    
    /**
     * Check for bookings past their payment deadline and cancel them
     * This should be run by a cron job
     */
    public function processPastDueBookings() {
        try {
            // Get all confirmed bookings that are past their payment deadline and not paid
            $stmt = $this->bookingModel->conn->prepare("
                SELECT b.booking_id, b.user_id, b.destination, b.date_of_tour, 
                       CONCAT(u.first_name, ' ', u.last_name) AS client_name,
                       u.email
                FROM bookings b
                JOIN users u ON b.user_id = u.user_id
                WHERE b.status = 'Confirmed' 
                  AND b.is_rebooked = 0
                  AND b.is_rebooking = 0
                  AND b.payment_status IN ('Unpaid')
                  AND b.payment_deadline < CURDATE()
                  AND NOT EXISTS (
                      SELECT 1 FROM canceled_trips ct 
                      WHERE ct.booking_id = b.booking_id
                  ) 
            ");
            $stmt->execute();
            $pastDueBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $cancelCount = 0;
            foreach ($pastDueBookings as $booking) {
                // Cancel the booking
                $this->cancelPastDueBooking($booking);
                $cancelCount++;
            }
            
            return [
                'success' => true,
                'message' => "Processed $cancelCount bookings with expired payment deadlines."
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => "Error processing past due bookings: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Cancel a specific booking that is past its payment deadline
     */
    private function cancelPastDueBooking($booking) {
        try {
            // Update booking status to canceled
            $stmt = $this->bookingModel->conn->prepare("
                UPDATE bookings 
                SET status = 'Canceled'
                WHERE booking_id = :booking_id
            ");
            $stmt->execute([':booking_id' => $booking['booking_id']]);
            
            // Add entry to canceled_trips table
            $reason = 'Automatic cancellation due to payment deadline expiration';
            $canceledBy = 'System';
            
            $stmt = $this->bookingModel->conn->prepare("
                INSERT INTO canceled_trips (reason, booking_id, user_id, amount_refunded, canceled_by) 
                VALUES (:reason, :booking_id, :user_id, 0, :canceled_by)
            ");
            $stmt->execute([
                ':reason' => $reason,
                ':booking_id' => $booking['booking_id'],
                ':user_id' => $booking['user_id'],
                ':canceled_by' => $canceledBy
            ]);
            
            // Send notification to client
            $clientMessage = "Your booking for trip to {$booking['destination']} on {$booking['date_of_tour']} has been canceled due to non-payment. Please contact us if you need further assistance.";
            $this->clientNotificationModel->addNotification(
                $booking['user_id'],
                'booking_canceled',
                $clientMessage,
                $booking['booking_id']
            );
            
            // Send notification to admin
            $adminMessage = "Booking ID {$booking['booking_id']} for {$booking['client_name']} has been automatically canceled due to the client not making payment within 2 days. Please review.";
            $this->adminNotificationModel->addNotification(
                'booking_canceled',
                $adminMessage,
                $booking['booking_id']
            );
            
            return true;
        } catch (PDOException $e) {
            error_log("Error canceling past due booking: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * API endpoint to manually check and cancel overdue bookings
     * This is useful for testing or manual triggering
     */
    public function checkDeadlines() {
        header("Content-Type: application/json");
        $result = $this->processPastDueBookings();
        echo json_encode($result);
    }
} 