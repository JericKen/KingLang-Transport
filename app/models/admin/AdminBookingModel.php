<?php
require_once __DIR__ . "/../../../config/database.php";

class AdminBookingModel {
    private $conn;

    public function __construct() {
        global $pdo;
        $this->conn = $pdo;
    }

    /**
     * Create a new booking in the database
     * 
     * @param array $bookingData Booking information
     * @param array|null $initialPayment Optional initial payment details
     * @return int|bool The booking ID if successful, false otherwise
     */
    public function createBooking($bookingData, $initialPayment = null) {
        try {
            $this->conn->beginTransaction();

            // Insert into bookings table
            $sql = "INSERT INTO bookings (
                client_name, contact_number, email, company_name, 
                pickup_point, destination, stops, 
                date_of_tour, pickup_time, number_of_days, number_of_buses, 
                total_cost, notes, status, payment_status, created_by, created_at
            ) VALUES (
                :client_name, :contact_number, :email, :company_name,
                :pickup_point, :destination, :stops,
                :date_of_tour, :pickup_time, :number_of_days, :number_of_buses,
                :total_cost, :notes, :status, :payment_status, :created_by, :created_at
            )";

            $stmt = $this->conn->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':client_name', $bookingData['client_name']);
            $stmt->bindParam(':contact_number', $bookingData['contact_number']);
            $stmt->bindParam(':email', $bookingData['email']);
            $stmt->bindParam(':company_name', $bookingData['company_name']);
            $stmt->bindParam(':pickup_point', $bookingData['pickup_point']);
            $stmt->bindParam(':destination', $bookingData['destination']);
            $stmt->bindParam(':stops', $bookingData['stops']);
            $stmt->bindParam(':date_of_tour', $bookingData['date_of_tour']);
            $stmt->bindParam(':pickup_time', $bookingData['pickup_time']);
            $stmt->bindParam(':number_of_days', $bookingData['number_of_days']);
            $stmt->bindParam(':number_of_buses', $bookingData['number_of_buses']);
            $stmt->bindParam(':total_cost', $bookingData['total_cost']);
            $stmt->bindParam(':notes', $bookingData['notes']);
            $stmt->bindParam(':status', $bookingData['status']);
            $stmt->bindParam(':payment_status', $bookingData['payment_status']);
            $stmt->bindParam(':created_by', $bookingData['created_by']);
            $stmt->bindParam(':created_at', $bookingData['created_at']);
            $stmt->execute();

            // Get the booking ID
            $bookingId = $this->conn->lastInsertId();

            // If initial payment is provided, add it to the payments table
            if ($initialPayment && $initialPayment['amount'] > 0) {
                $paymentSql = "INSERT INTO payments (
                    booking_id, amount, payment_method, reference_number, payment_date, created_at
                ) VALUES (
                    :booking_id, :amount, :payment_method, :reference_number, :payment_date, :created_at
                )";

                $paymentStmt = $this->conn->prepare($paymentSql);
                $paymentStmt->bindParam(':booking_id', $bookingId);
                $paymentStmt->bindParam(':amount', $initialPayment['amount']);
                $paymentStmt->bindParam(':payment_method', $initialPayment['payment_method']);
                $paymentStmt->bindParam(':reference_number', $initialPayment['reference_number']);
                $paymentStmt->bindParam(':payment_date', $initialPayment['payment_date']);
                $paymentStmt->bindParam(':created_at', $bookingData['created_at']);
                $paymentStmt->execute();
            }

            // Add a booking history record
            $historySql = "INSERT INTO booking_history (
                booking_id, status, notes, changed_by, created_at
            ) VALUES (
                :booking_id, :status, :notes, :changed_by, :created_at
            )";

            $historyStmt = $this->conn->prepare($historySql);
            $historyStmt->bindParam(':booking_id', $bookingId);
            $historyStmt->bindParam(':status', $bookingData['status']);
            $notes = "Booking created by admin";
            $historyStmt->bindParam(':notes', $notes);
            $historyStmt->bindParam(':changed_by', $bookingData['created_by']);
            $historyStmt->bindParam(':created_at', $bookingData['created_at']);
            $historyStmt->execute();

            // Commit the transaction
            $this->conn->commit();
            return $bookingId;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating booking: " . $e->getMessage());
            return false;
        }
    }
    

    /**

     * Get booking by ID

     * 

     * @param int $bookingId

     * @return array|bool The booking data or false if not found

     */

    public function getBookingById($bookingId) {

        try {

            $sql = "SELECT * FROM bookings WHERE booking_id = :booking_id";

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':booking_id', $bookingId);

            $stmt->execute();

            

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error getting booking: " . $e->getMessage());

            return false;

        }

    }

    

    /**

     * Get all bookings with pagination and filtering

     * 

     * @param string $status Filter by status

     * @param string $column Sort column

     * @param string $order Sort order

     * @param int $page Page number

     * @param int $limit Items per page

     * @return array|bool List of bookings or false on error

     */

    public function getAllBookings($status = 'all', $column = 'created_at', $order = 'DESC', $page = 1, $limit = 10) {

        try {

            // Calculate offset for pagination

            $offset = ($page - 1) * $limit;

            

            // Build the query based on status filter

            $sql = "SELECT * FROM bookings";

            $params = [];

            

            if ($status !== 'all' && $status !== '') {

                $sql .= " WHERE status = :status";

                $params[':status'] = $status;

            }

            

            // Add sorting

            $sql .= " ORDER BY {$column} {$order}";

            

            // Add pagination

            $sql .= " LIMIT :limit OFFSET :offset";

            

            $stmt = $this->conn->prepare($sql);

            

            // Bind parameters

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            

            $stmt->execute();

            

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error getting bookings: " . $e->getMessage());

            return false;

        }

    }

    

    /**

     * Get the total number of bookings

     * 

     * @param string $status Filter by status

     * @return int|bool Total count or false on error

     */

    public function getTotalBookings($status = 'all') {

        try {

            // Build the query based on status filter

            $sql = "SELECT COUNT(*) as total FROM bookings";

            $params = [];

            

            if ($status !== 'all' && $status !== '') {

                $sql .= " WHERE status = :status";

                $params[':status'] = $status;

            }

            

            $stmt = $this->conn->prepare($sql);

            

            // Bind parameters

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            

            return $result['total'];

        } catch (PDOException $e) {

            error_log("Error counting bookings: " . $e->getMessage());

            return false;

        }

    }

    

    /**

     * Update booking status

     * 

     * @param int $bookingId Booking ID

     * @param string $status New status

     * @param string $notes Optional notes

     * @param string $changedBy User who made the change

     * @return bool Success status

     */

    public function updateBookingStatus($bookingId, $status, $notes = '', $changedBy = 'admin') {

        try {

            $this->conn->beginTransaction();

            

            // Update status in bookings table

            $sql = "UPDATE bookings SET status = :status WHERE booking_id = :booking_id";

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':status', $status);

            $stmt->bindParam(':booking_id', $bookingId);

            $stmt->execute();

            

            // Add entry to booking history

            $historySql = "INSERT INTO booking_history (

                booking_id, status, notes, changed_by, created_at

            ) VALUES (

                :booking_id, :status, :notes, :changed_by, :created_at

            )";

            

            $historyStmt = $this->conn->prepare($historySql);

            

            // Bind history parameters

            $historyStmt->bindParam(':booking_id', $bookingId);

            $historyStmt->bindParam(':status', $status);

            $historyStmt->bindParam(':notes', $notes);

            $historyStmt->bindParam(':changed_by', $changedBy);

            $createdAt = date('Y-m-d H:i:s');

            $historyStmt->bindParam(':created_at', $createdAt);

            

            $historyStmt->execute();

            

            // Commit the transaction

            $this->conn->commit();

            

            return true;

        } catch (PDOException $e) {

            // Roll back the transaction on error

            $this->conn->rollBack();

            error_log("Error updating booking status: " . $e->getMessage());

            return false;

        }

    }

    

    /**

     * Add payment to a booking

     * 

     * @param array $paymentData Payment details

     * @return int|bool Payment ID if successful, false otherwise

     */

    public function addPayment($paymentData) {

        try {

            $this->conn->beginTransaction();

            

            // Insert into payments table

            $sql = "INSERT INTO payments (

                booking_id, amount, payment_method, reference_number, payment_date, created_at

            ) VALUES (

                :booking_id, :amount, :payment_method, :reference_number, :payment_date, :created_at

            )";

            

            $stmt = $this->conn->prepare($sql);

            

            // Bind parameters

            $stmt->bindParam(':booking_id', $paymentData['booking_id']);

            $stmt->bindParam(':amount', $paymentData['amount']);

            $stmt->bindParam(':payment_method', $paymentData['payment_method']);

            $stmt->bindParam(':reference_number', $paymentData['reference_number']);

            $stmt->bindParam(':payment_date', $paymentData['payment_date']);

            $stmt->bindParam(':created_at', $paymentData['created_at']);

            

            $stmt->execute();

            $paymentId = $this->conn->lastInsertId();

            

            // Update payment status in booking table

            // First get total cost and current payments

            $bookingSql = "SELECT total_cost FROM bookings WHERE booking_id = :booking_id";

            $bookingStmt = $this->conn->prepare($bookingSql);

            $bookingStmt->bindParam(':booking_id', $paymentData['booking_id']);

            $bookingStmt->execute();

            $booking = $bookingStmt->fetch(PDO::FETCH_ASSOC);

            

            $paymentsSql = "SELECT SUM(amount) as total_paid FROM payments WHERE booking_id = :booking_id";

            $paymentsStmt = $this->conn->prepare($paymentsSql);

            $paymentsStmt->bindParam(':booking_id', $paymentData['booking_id']);

            $paymentsStmt->execute();

            $payments = $paymentsStmt->fetch(PDO::FETCH_ASSOC);

            

            $totalCost = $booking['total_cost'];

            $totalPaid = $payments['total_paid'];

            

            // Determine payment status

            $paymentStatus = 'Unpaid';

            if ($totalPaid >= $totalCost) {

                $paymentStatus = 'Paid';

            } else if ($totalPaid > 0) {

                $paymentStatus = 'Partially Paid';

            }

            

            // Update booking

            $updateSql = "UPDATE bookings SET payment_status = :payment_status WHERE booking_id = :booking_id";

            $updateStmt = $this->conn->prepare($updateSql);

            $updateStmt->bindParam(':payment_status', $paymentStatus);

            $updateStmt->bindParam(':booking_id', $paymentData['booking_id']);

            $updateStmt->execute();

            

            // Commit transaction

            $this->conn->commit();

            

            return $paymentId;

        } catch (PDOException $e) {

            $this->conn->rollBack();

            error_log("Error adding payment: " . $e->getMessage());

            return false;

        }

    }

    

    /**

     * Get payments for a booking

     * 

     * @param int $bookingId Booking ID

     * @return array|bool Array of payments or false on error

     */

    public function getPaymentsByBookingId($bookingId) {

        try {

            $sql = "SELECT * FROM payments WHERE booking_id = :booking_id ORDER BY payment_date DESC";

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':booking_id', $bookingId);

            $stmt->execute();

            

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error getting payments: " . $e->getMessage());

            return false;

        }

    }

    

    /**

     * Get booking history

     * 

     * @param int $bookingId Booking ID

     * @return array|bool Array of history entries or false on error

     */

    public function getBookingHistory($bookingId) {

        try {

            $sql = "SELECT * FROM booking_history WHERE booking_id = :booking_id ORDER BY created_at DESC";

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':booking_id', $bookingId);

            $stmt->execute();

            

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error getting booking history: " . $e->getMessage());

            return false;

        }

    }

    

    /**

     * Get terms agreement information for a booking

     * 

     * @param int $bookingId

     * @return array|bool Terms agreement data or false if not found

     */

    public function getTermsAgreement($bookingId) {

        try {

            $sql = "SELECT * FROM terms_agreements WHERE booking_id = :booking_id";

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':booking_id', $bookingId);

            $stmt->execute();

            

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error getting terms agreement: " . $e->getMessage());

            return false;

        }

    }

} 