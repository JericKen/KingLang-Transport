<?php

require_once __DIR__ . "/../../../config/database.php";

require_once __DIR__ . "/../client/NotificationModel.php";



class PaymentManagementModel {

    private $conn;

    private $clientNotificationModel;



    public function __construct() {

        global $pdo;

        $this->conn = $pdo;

        $this->clientNotificationModel = new ClientNotificationModel();

    }



    public function getPayments($status = null, $column = 'payment_id', $order = 'DESC', $page = 1, $limit = 10, $search = '') {

        try {

            $offset = ($page - 1) * $limit;

            $params = [];

            $whereClause = "WHERE is_canceled = 0";



            if ($status && $status !== 'all') {

                $whereClause .= " AND p.status = :status";

                $params[':status'] = $status;

            }



            if ($search) {

                $whereClause .= " AND (b.booking_id LIKE :search OR CONCAT(u.first_name, ' ', u.last_name) LIKE :search)";

                $params[':search'] = "%$search%";

            }



            $stmt = $this->conn->prepare("

                SELECT 

                    p.payment_id,

                    p.amount,

                    p.payment_method,

                    p.proof_of_payment,

                    p.status,

                    p.payment_date,

                    p.is_canceled,

                    b.booking_id,

                    b.destination,

                    b.pickup_point,

                    b.date_of_tour,

                    c.total_cost,

                    CONCAT(u.first_name, ' ', u.last_name) AS client_name,

                    u.contact_number

                FROM payments p 

                JOIN bookings b ON p.booking_id = b.booking_id 

                JOIN booking_costs c ON b.booking_id = c.booking_id

                JOIN users u ON p.user_id = u.user_id 

                $whereClause 

                ORDER BY $column $order

                LIMIT :limit OFFSET :offset

            ");

            

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();



            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error in getPayments: " . $e->getMessage());

            throw new Exception("Failed to retrieve payments: " . $e->getMessage());

        }

    }



    public function getTotalPayments($status = null, $search = '') {

        try {

            $params = [];

            $whereClause = "WHERE 1=1";



            if ($status && $status !== 'all') {

                $whereClause .= " AND p.status = :status";

                $params[':status'] = $status;

            }



            if ($search) {

                $whereClause .= " AND (b.booking_id LIKE :search OR CONCAT(u.first_name, ' ', u.last_name) LIKE :search)";

                $params[':search'] = "%$search%";

            }



            $sql = "SELECT COUNT(*) as total 

                    FROM payments p 

                    JOIN bookings b ON p.booking_id = b.booking_id 

                    JOIN users u ON p.user_id = u.user_id 

                    $whereClause";



            $stmt = $this->conn->prepare($sql);

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            $stmt->execute();



            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        } catch (PDOException $e) {

            error_log("Error in getTotalPayments: " . $e->getMessage());

            throw new Exception("Failed to get total payments: " . $e->getMessage());

        }

    }



    public function confirmPayment($paymentId) {

        try {

            $this->conn->beginTransaction();



            // Get booking ID from payment

            $sql = "SELECT p.booking_id, p.user_id, p.amount, b.destination 

                    FROM payments p

                    JOIN bookings b ON p.booking_id = b.booking_id 

                    WHERE p.payment_id = :id";

            $stmt = $this->conn->prepare($sql);

            $stmt->execute([':id' => $paymentId]);

            $paymentData = $stmt->fetch(PDO::FETCH_ASSOC);

            

            if (!$paymentData) {

                throw new Exception("Payment not found");

            }

            

            $bookingId = $paymentData['booking_id'];

            $userId = $paymentData['user_id'];

            $amount = $paymentData['amount'];

            $destination = $paymentData['destination'];



            // Update payment status

            $sql = "UPDATE payments SET status = 'Confirmed', updated_at = NOW() WHERE payment_id = :id";

            $stmt = $this->conn->prepare($sql);

            $stmt->execute([':id' => $paymentId]);



            // Update payment status and balance

            $stmt = $this->conn->prepare("SELECT SUM(amount) AS total_paid FROM payments WHERE booking_id = :booking_id AND status = 'Confirmed'");

            $stmt->execute([":booking_id" => $bookingId]);

            $total_paid = $stmt->fetch(PDO::FETCH_ASSOC)["total_paid"] ?? 0;



            $stmt = $this->conn->prepare("SELECT c.total_cost, b.balance FROM bookings b JOIN booking_costs c ON b.booking_id = c.booking_id WHERE b.booking_id = :booking_id");

            $stmt->execute([":booking_id" => $bookingId]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $total_cost = $result["total_cost"] ?? 0;



            // Calculate balance with proper rounding

            $balance = round($total_cost - $total_paid, 2);

            

            // Handle tiny negative balances

            if ($balance > -0.1 && $balance < 0) {

                $balance = 0;

            }



            $new_status = "Unpaid";

            if ($total_paid > 0 && $total_paid < $total_cost) {

                $new_status = "Partially Paid";

            } elseif ($total_paid >= $total_cost) {

                $new_status = "Paid";

            }



            $stmt = $this->conn->prepare("UPDATE bookings SET payment_status = :payment_status, status = 'Confirmed', balance = :balance WHERE booking_id = :booking_id");

            $stmt->execute([

                ":payment_status" => $new_status,

                ":booking_id" => $bookingId,

                ":balance" => $balance

            ]);



            // Add client notification

            $clientMessage = "Your payment of " . number_format($amount, 2) . " for booking to " . $destination . " has been confirmed.";

            $this->clientNotificationModel->addNotification($userId, "payment_confirmed", $clientMessage, $bookingId);



            $this->conn->commit();

            return true;

        } catch (PDOException $e) {

            $this->conn->rollBack();

            error_log("Error in confirmPayment: " . $e->getMessage());

            throw new Exception("Failed to confirm payment: " . $e->getMessage());

        }

    }



    public function updatePaymentStatus($booking_id) {

        try {

            // This method should be removed since its logic is now directly in confirmPayment

            // to avoid transaction management issues

            throw new Exception("This method should not be called directly - use confirmPayment instead");



        } catch (PDOException $e) {

            return "Database error";

        }

    }



    public function rejectPayment($paymentId, $reason) {

        try {

            $this->conn->beginTransaction();



            // Get booking ID from payment

            $sql = "SELECT p.booking_id, p.user_id, p.amount, b.destination 

                    FROM payments p

                    JOIN bookings b ON p.booking_id = b.booking_id 

                    WHERE p.payment_id = :id";

            $stmt = $this->conn->prepare($sql);

            $stmt->execute([':id' => $paymentId]);

            $paymentData = $stmt->fetch(PDO::FETCH_ASSOC);

            

            if (!$paymentData) {

                throw new Exception("Payment not found");

            }

            

            $bookingId = $paymentData['booking_id'];

            $userId = $paymentData['user_id'];

            $amount = $paymentData['amount'];

            $destination = $paymentData['destination'];



            // Update payment status

            $sql = "UPDATE payments SET status = 'Rejected', updated_at = NOW() WHERE payment_id = :id";

            $stmt = $this->conn->prepare($sql);

            $stmt->execute([':id' => $paymentId]);



            // Check if there are any other pending payments for this booking

            $sql = "SELECT COUNT(*) FROM payments 

                    WHERE booking_id = :booking_id 

                    AND payment_id != :payment_id 

                    AND status = 'PENDING'";

            $stmt = $this->conn->prepare($sql);

            $stmt->execute([':booking_id' => $bookingId, ':payment_id' => $paymentId]);

            $otherPendingPayments = $stmt->fetchColumn();



            // If this was the only pending payment, revert booking to its previous status

            if ($otherPendingPayments == 0) {

                $sql = "UPDATE bookings SET status = 'Confirmed'

                        WHERE booking_id = :booking_id AND status = 'Processing'";

                $stmt = $this->conn->prepare($sql);

                $stmt->execute([':booking_id' => $bookingId]);

            }



            // Record rejection reason

            $sql = "INSERT INTO rejected_trips (reason, type, booking_id, user_id) 

                    VALUES (:reason, 'Payment', :booking_id, :user_id)";

            $stmt = $this->conn->prepare($sql);

            $stmt->execute([

                ':reason' => $reason, 

                ':booking_id' => $bookingId, 

                ':user_id' => $userId

            ]);



            // Add client notification

            $clientMessage = "Your payment of " . number_format($amount, 2) . " for booking to " . $destination . " has been rejected. Reason: " . $reason;

            $this->clientNotificationModel->addNotification($userId, "payment_rejected", $clientMessage, $bookingId);



            $this->conn->commit();

            return true;

        } catch (PDOException $e) {

            $this->conn->rollBack();

            error_log("Error in rejectPayment: " . $e->getMessage());

            throw new Exception("Failed to reject payment: " . $e->getMessage());

        }

    }



    public function recordManualPayment($bookingId, $userId, $amount, $paymentMethod, $notes = '') {

        try {

            $this->conn->beginTransaction();

            

            // Insert the payment record

            $sql = "INSERT INTO payments (booking_id, user_id, amount, payment_method, status, notes) 

                    VALUES (:booking_id, :user_id, :amount, :payment_method, 'Confirmed', :notes)";

            $stmt = $this->conn->prepare($sql);

            $stmt->execute([

                ':booking_id' => $bookingId,

                ':user_id' => $userId,

                ':amount' => $amount,

                ':payment_method' => $paymentMethod,

                ':notes' => $notes

            ]);

            

            $paymentId = $this->conn->lastInsertId();

            

            // Get booking info for notification

            $sql = "SELECT b.destination, CONCAT(u.first_name, ' ', u.last_name) AS client_name 

                    FROM bookings b

                    JOIN users u ON b.user_id = u.user_id

                    WHERE b.booking_id = :booking_id";

            $stmt = $this->conn->prepare($sql);

            $stmt->execute([':booking_id' => $bookingId]);

            $bookingInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            

            // Update payment status and balance in bookings table

            $stmt = $this->conn->prepare("SELECT SUM(amount) AS total_paid FROM payments WHERE booking_id = :booking_id AND status = 'Confirmed'");

            $stmt->execute([":booking_id" => $bookingId]);

            $total_paid = $stmt->fetch(PDO::FETCH_ASSOC)["total_paid"] ?? 0;



            $stmt = $this->conn->prepare("SELECT c.total_cost FROM bookings b JOIN booking_costs c ON b.booking_id = c.booking_id WHERE b.booking_id = :booking_id");

            $stmt->execute([":booking_id" => $bookingId]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $total_cost = $result["total_cost"] ?? 0;



            // Calculate balance

            $balance = round($total_cost - $total_paid, 2);

            

            // Handle tiny negative balances

            if ($balance > -0.1 && $balance < 0) {

                $balance = 0;

            }



            $new_status = "Unpaid";

            if ($total_paid > 0 && $total_paid < $total_cost) {

                $new_status = "Partially Paid";

            } elseif ($total_paid >= $total_cost) {

                $new_status = "Paid";

            }



            $stmt = $this->conn->prepare("UPDATE bookings SET payment_status = :payment_status, status = 'Confirmed', balance = :balance WHERE booking_id = :booking_id");

            $stmt->execute([

                ":payment_status" => $new_status,

                ":booking_id" => $bookingId,

                ":balance" => $balance

            ]);

            

            // Add client notification

            if (isset($bookingInfo['client_name']) && isset($bookingInfo['destination'])) {

                $message = "Your payment of PHP " . number_format($amount, 2) . " for booking to " . $bookingInfo['destination'] . " has been recorded.";

                $this->clientNotificationModel->addNotification($userId, "payment_recorded", $message, $bookingId);

            }

            

            $this->conn->commit();

            return ["success" => true, "payment_id" => $paymentId];

        } catch (PDOException $e) {

            $this->conn->rollBack();

            error_log("Error in recordManualPayment: " . $e->getMessage());

            throw new Exception("Failed to record payment: " . $e->getMessage());

        }

    }



    public function searchBookings($search) {

        try {

            $search = "%$search%";

            $sql = "

                SELECT b.booking_id, b.destination, b.date_of_tour, b.status, b.payment_status, 

                       b.user_id, CONCAT(u.first_name, ' ', u.last_name) AS client_name,

                       c.total_cost, b.balance

                FROM bookings b

                JOIN users u ON b.user_id = u.user_id

                JOIN booking_costs c ON b.booking_id = c.booking_id

                WHERE (b.booking_id LIKE :search 

                   OR b.destination LIKE :search

                   OR CONCAT(u.first_name, ' ', u.last_name) LIKE :search)

                ORDER BY b.booking_id DESC

                LIMIT 10

            ";

            

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':search', $search);

            $stmt->execute();

            

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error in searchBookings: " . $e->getMessage());

            throw new Exception("Failed to search bookings: " . $e->getMessage());

        }

    }

    

    public function searchClients($search) {

        try {

            $search = "%$search%";

            $sql = "

                SELECT user_id, CONCAT(first_name, ' ', last_name) AS client_name, email, contact_number

                FROM users 

                WHERE role = 'Client'

                AND (user_id LIKE :search 

                  OR first_name LIKE :search 

                  OR last_name LIKE :search

                  OR CONCAT(first_name, ' ', last_name) LIKE :search

                  OR email LIKE :search)

                ORDER BY last_name, first_name

                LIMIT 10

            ";

            

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':search', $search);

            $stmt->execute();

            

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error in searchClients: " . $e->getMessage());

            throw new Exception("Failed to search clients: " . $e->getMessage());

        }

    }

    

    public function getBookingDetails($bookingId) {

        try {

            $sql = "SELECT 

                        b.booking_id,

                        b.user_id,

                        b.destination,

                        b.payment_status,

                        b.balance,

                        c.total_cost,

                        CONCAT(u.first_name, ' ', u.last_name) AS client_name

                    FROM bookings b

                    JOIN booking_costs c ON b.booking_id = c.booking_id

                    JOIN users u ON b.user_id = u.user_id

                    WHERE b.booking_id = :booking_id";

            

            $stmt = $this->conn->prepare($sql);

            $stmt->execute([':booking_id' => $bookingId]);

            

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error in getBookingDetails: " . $e->getMessage());

            throw new Exception("Failed to get booking details: " . $e->getMessage());

        }

    }

    

    public function getPaymentStats() {

        try {

            // Get total count

            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM payments WHERE is_canceled = 0");

            $stmt->execute();

            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

            

            // Get confirmed count

            $stmt = $this->conn->prepare("SELECT COUNT(*) as confirmed FROM payments WHERE status = 'CONFIRMED' AND is_canceled = 0");

            $stmt->execute();

            $confirmed = $stmt->fetch(PDO::FETCH_ASSOC)['confirmed'] ?? 0;

            

            // Get pending count

            $stmt = $this->conn->prepare("SELECT COUNT(*) as pending FROM payments WHERE status = 'PENDING' AND is_canceled = 0");

            $stmt->execute();

            $pending = $stmt->fetch(PDO::FETCH_ASSOC)['pending'] ?? 0;

            

            // Get rejected count

            $stmt = $this->conn->prepare("SELECT COUNT(*) as rejected FROM payments WHERE status = 'REJECTED' AND is_canceled = 0");

            $stmt->execute();

            $rejected = $stmt->fetch(PDO::FETCH_ASSOC)['rejected'] ?? 0;

            

            return [

                'total' => $total,

                'confirmed' => $confirmed,

                'pending' => $pending,

                'rejected' => $rejected

            ];

        } catch (PDOException $e) {

            error_log("Error in getPaymentStats: " . $e->getMessage());

            throw new Exception("Failed to get payment statistics: " . $e->getMessage());

        }

    }

} 