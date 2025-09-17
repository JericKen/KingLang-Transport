<?php

require_once __DIR__ . '/../../models/admin/BookingManagementModel.php';

require_once __DIR__ . '/../../models/admin/NotificationModel.php';

require_once __DIR__ . '/../../models/client/NotificationModel.php';



class BookingReviewReminderController {

    private $bookingModel;

    private $adminNotificationModel;

    private $clientNotificationModel;

    

    public function __construct() {

        $this->bookingModel = new BookingManagementModel();

        $this->adminNotificationModel = new NotificationModel();

        $this->clientNotificationModel = new ClientNotificationModel();

    }

    

    /**

     * Check for bookings that need review reminders (3 days before tour date)

     * This should be run by a cron job

     */

    public function processReviewReminders() {

        try {

            // Get pending bookings that are 3 days before their tour date

            $stmt = $this->bookingModel->conn->prepare("

                SELECT b.booking_id, b.user_id, b.destination, b.date_of_tour, 

                       CONCAT(u.first_name, ' ', u.last_name) AS client_name,

                       u.email

                FROM bookings b

                JOIN users u ON b.user_id = u.user_id

                WHERE b.status = 'Pending' 

                  AND b.date_of_tour = DATE_ADD(CURDATE(), INTERVAL 3 DAY)

            ");

            $stmt->execute();

            $pendingBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            

            $reminderCount = 0;

            foreach ($pendingBookings as $booking) {

                // Send reminder notification to admin

                $this->sendReviewReminder($booking);

                $reminderCount++;

            }

            

            return [

                'success' => true,

                'message' => "Sent $reminderCount review reminders for bookings 3 days before tour date."

            ];

        } catch (PDOException $e) {

            return [

                'success' => false,

                'message' => "Error processing review reminders: " . $e->getMessage()

            ];

        }

    }

    

    /**

     * Send a reminder notification to admin for a specific booking

     */

    private function sendReviewReminder($booking) {

        try {

            // Format the date for display

            $formattedDate = date('F j, Y', strtotime($booking['date_of_tour']));

            

            // Add admin notification

            $adminMessage = "URGENT: Booking ID {$booking['booking_id']} for {$booking['client_name']} to {$booking['destination']} on {$formattedDate} needs review. This booking will be automatically cancelled if not reviewed by the tour date.";

            $this->adminNotificationModel->addNotification("booking_review_reminder", $adminMessage, $booking['booking_id']);

            

            return true;

        } catch (PDOException $e) {

            error_log("Error sending review reminder: " . $e->getMessage());

            return false;

        }

    }

        

    /**

     * Check for bookings that need to be auto-cancelled (on tour date if still pending)

     * This should be run by a cron job

     */

    public function processAutoCancellations() {

        try {

            // Get pending bookings that are on their tour date

            $stmt = $this->bookingModel->conn->prepare("

                SELECT b.booking_id, b.user_id, b.destination, b.date_of_tour, 

                       CONCAT(u.first_name, ' ', u.last_name) AS client_name,

                       u.email

                FROM bookings b

                JOIN users u ON b.user_id = u.user_id

                WHERE b.status = 'Pending' 

                  AND b.date_of_tour <= CURDATE()

                  AND NOT EXISTS (

                      SELECT 1 FROM canceled_trips ct 

                      WHERE ct.booking_id = b.booking_id

                  )

            ");

            $stmt->execute();

            $expiredBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            

            $cancelCount = 0;

            foreach ($expiredBookings as $booking) {

                // Auto-cancel the booking

                $this->autoCancelBooking($booking);

                $cancelCount++;

            }

            

            return [

                'success' => true,

                'message' => "Auto-cancelled $cancelCount bookings that were not reviewed by their tour date."

            ];

        } catch (PDOException $e) {

            return [

                'success' => false,

                'message' => "Error processing auto-cancellations: " . $e->getMessage()

            ];

        }

    }

    

    /**

     * Auto-cancel a specific booking that has reached its tour date without review

     */

    private function autoCancelBooking($booking) {

        try {

            // Update booking status to canceled

            $stmt = $this->bookingModel->conn->prepare("

                UPDATE bookings 

                SET status = 'Canceled'

                WHERE booking_id = :booking_id

            ");

            $stmt->execute([':booking_id' => $booking['booking_id']]);

            

            // Add entry to canceled_trips table

            $reason = 'Automatic cancellation due to lack of review by tour date';

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

            $formattedDate = date('F j, Y', strtotime($booking['date_of_tour']));

            $clientMessage = "Your booking request for trip to {$booking['destination']} on {$formattedDate} has been automatically cancelled due to lack of confirmation. Please contact us if you wish to rebook.";

            $this->clientNotificationModel->addNotification(

                $booking['user_id'],

                'booking_canceled',

                $clientMessage,

                $booking['booking_id']

            );

            

            // Send notification to admin

            $adminMessage = "Booking ID {$booking['booking_id']} for {$booking['client_name']} has been automatically cancelled due to lack of review by the tour date.";

            $this->adminNotificationModel->addNotification(

                "booking_auto_canceled", 

                $adminMessage, 

                $booking['booking_id']

            );

            

            return true;

        } catch (PDOException $e) {

            error_log("Error auto-cancelling booking: " . $e->getMessage());

            return false;

        }

    }



    /**

     * Get bookings that need review (3 days before tour date or less)

     * This is used for the admin dashboard

     */

    public function getUrgentReviewBookings() {

        try {

            // Get pending bookings that are 3 days or less before their tour date

            $stmt = $this->bookingModel->conn->prepare("

                SELECT b.booking_id, b.user_id, b.destination, b.date_of_tour, 

                       CONCAT(u.first_name, ' ', u.last_name) AS client_name,

                       u.email, b.pickup_point, b.number_of_buses,

                       DATEDIFF(b.date_of_tour, CURDATE()) AS days_remaining

                FROM bookings b

                JOIN users u ON b.user_id = u.user_id

                WHERE b.status = 'Pending' 

                  AND b.date_of_tour <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)

                  AND b.is_rebooking = 0

                  AND b.is_rebooked = 0

                ORDER BY b.date_of_tour ASC

            ");

            $stmt->execute();

            $urgentBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            

            // Format dates for display

            foreach ($urgentBookings as &$booking) {

                $booking['formatted_date'] = date('F j, Y', strtotime($booking['date_of_tour']));

            }

            

            return [

                'success' => true,

                'bookings' => $urgentBookings,

                'count' => count($urgentBookings)

            ];



            echo json_encode($urgentBookings);

        } catch (PDOException $e) {

            return [

                'success' => false,

                'message' => "Error getting urgent review bookings: " . $e->getMessage()

            ];

        }

    }

    

    /**

     * API endpoint for getting urgent review bookings

     * Returns JSON response

     */

    public function showUrgentReviewBookings() {

        $result = $this->getUrgentReviewBookings();

        

        // Set JSON content type

        header('Content-Type: application/json');

        

        // Return the result as JSON

        echo json_encode($result);

        exit;

    }

    

    /**

     * API endpoint to manually trigger auto-cancellation for overdue bookings

     * This can be called from the admin dashboard

     * Returns JSON response

     */

    public function manualAutoCancellation() {

        try {

            // Get pending bookings that are on or past their tour date

            $stmt = $this->bookingModel->conn->prepare("

                SELECT b.booking_id, b.user_id, b.destination, b.date_of_tour, 

                       CONCAT(u.first_name, ' ', u.last_name) AS client_name,

                       u.email

                FROM bookings b

                JOIN users u ON b.user_id = u.user_id

                WHERE b.status = 'Pending' 

                  AND b.date_of_tour <= CURDATE()

                  AND NOT EXISTS (

                      SELECT 1 FROM canceled_trips ct 

                      WHERE ct.booking_id = b.booking_id

                  )

            ");

            $stmt->execute();

            $expiredBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            

            $cancelledBookings = [];

            foreach ($expiredBookings as $booking) {

                // Auto-cancel the booking

                if ($this->autoCancelBooking($booking)) {

                    $cancelledBookings[] = [

                        'booking_id' => $booking['booking_id'],

                        'client_name' => $booking['client_name'],

                        'destination' => $booking['destination'],

                        'date_of_tour' => $booking['date_of_tour']

                    ];

                }

            }

            

            // Set JSON content type

            header('Content-Type: application/json');

            

            // Return the result as JSON

            echo json_encode([

                'success' => true,

                'message' => count($cancelledBookings) . " bookings have been automatically cancelled.",

                'cancelled_bookings' => $cancelledBookings

            ]);

            exit;

            

        } catch (PDOException $e) {

            // Set JSON content type

            header('Content-Type: application/json');

            

            // Return error as JSON

            echo json_encode([

                'success' => false,

                'message' => "Error processing auto-cancellations: " . $e->getMessage()

            ]);

            exit;

        }

    }

} 