<?php

require_once __DIR__ . "/../../../config/database.php";
require_once __DIR__ . "/NotificationModel.php";
require_once __DIR__ . "/../client/NotificationModel.php";

class BookingManagementModel {
    public $conn;
    private $notificationModel;
    private $clientNotificationModel;

    public function __construct() {
        global $pdo;
        $this->conn = $pdo;
        $this->notificationModel = new NotificationModel();
        $this->clientNotificationModel = new ClientNotificationModel();
    }

    public function getAllBookings($status, $column, $order, $page = 1, $limit = 10) {
        $allowed_status = ["Pending", "Confirmed", "Canceled", "Rejected", "Completed", "Processing", "Upcoming", "Rebooking", "All"];
        $status = in_array($status, $allowed_status) ? $status : "";
        $status = ($status == "All") ? "" :
          (($status == "Confirmed") ? " AND b.status IN ('Confirmed', 'Processing')" :
          (($status == "Processing") ? " AND b.status = 'Processing'" :
          (($status == "Upcoming") ? " AND b.status = 'Confirmed' AND payment_status IN ('Paid', 'Partially Paid') AND date_of_tour > CURDATE()" :
          " AND b.status = '$status'")));

        $allowed_columns = ["booking_id", "client_name", "contact_number", "destination", "pickup_point", "date_of_tour", "end_of_tour", "number_of_days", "number_of_buses", "status", "payment_status", "total_cost"];
        $column = in_array($column, $allowed_columns) ? $column : "client_name";
        $order = $order === "asc" ? "ASC" : "DESC";

        // Calculate offset for pagination
        $offset = ($page - 1) * $limit;

        try {
            $stmt = $this->conn->prepare("
                SELECT b.booking_id, b.user_id, CONCAT(u.first_name, ' ', u.last_name) AS client_name, u.contact_number, b.destination, b.pickup_point, b.date_of_tour, b.end_of_tour, b.number_of_days, b.number_of_buses, b.status, b.payment_status, c.total_cost, b.balance
                FROM bookings b
                JOIN users u ON b.user_id = u.user_id
                JOIN booking_costs c ON b.booking_id = c.booking_id
                $status
                ORDER BY $column $order
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }  catch (PDOException $e) {
            return "Database error: $e";
        }
    }

    public function getPaymentHistory($booking_id) {
        try {
            $sql = "
                SELECT p.payment_id, p.booking_id, p.user_id, p.amount, p.payment_method,
                       p.proof_of_payment, p.status, p.payment_date, p.updated_at, p.is_canceled
                FROM payments p
                WHERE p.booking_id = :booking_id
                ORDER BY p.payment_date DESC
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":booking_id", $booking_id);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getTotalBookings($status) {
        $allowed_status = ["Pending", "Confirmed", "Canceled", "Rejected", "Completed", "Rebooking", "All"];
        $status = in_array($status, $allowed_status) ? $status : "";
        $status == "All" ? $status = "" : $status = "WHERE b.status = '$status'";

        try {
            $query = "
                SELECT COUNT(*) as total
                FROM bookings b
                $status
            ";

            // error_log("Query for counting: " . $query);

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            // error_log("Count result: " . print_r($result, true));
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Error in getTotalBookings: " . $e->getMessage());
            return 0;
        }
    }

    public function confirmBooking($booking_id, $discount = null, $discountType = null) {
        try {
            // Set the payment deadline to 2 days from now
            $payment_deadline = date('Y-m-d H:i:s', strtotime('+2 days'));

            $stmt = $this->conn->prepare("UPDATE bookings SET status = 'Confirmed', confirmed_at = NOW(), payment_deadline = :payment_deadline WHERE booking_id = :booking_id");
            $stmt->execute([
                ":booking_id" => $booking_id,
                ":payment_deadline" => $payment_deadline
            ]);

            // Get booking information
            $bookingInfo = $this->getBooking($booking_id);

            // Apply discount if provided
            if ($discount !== null && $discount > 0) {

                // Get current booking cost
                $stmt = $this->conn->prepare("SELECT total_cost FROM booking_costs WHERE booking_id = :booking_id");
                $stmt->execute([":booking_id" => $booking_id]);
                $originalCost = (float)$stmt->fetchColumn();

                // Calculate new discounted cost based on discount type
                $discountedCost = $originalCost;
                $discountValue = $discount;

                if ($discountType === 'percentage') {
                    // Percentage discount
                    $discountMultiplier = (100 - $discount) / 100;
                    $discountedCost = round($originalCost * $discountMultiplier, 2);
                } else if ($discountType === 'flat') {
                    // Flat amount discount
                    $discountedCost = max(0, round($originalCost - $discount, 2));
                    // Calculate equivalent percentage for storage
                    $discountValue = min(100, round(($discount / $originalCost) * 100, 2));
                }

                // Update the booking costs with discount
                $stmt = $this->conn->prepare("UPDATE booking_costs SET gross_price = :gross_price, total_cost = :total_cost, discount = :discount, discount_type = :discount_type, discount_amount = :discount_amount WHERE booking_id = :booking_id");
                $stmt->execute([
                    ":gross_price" => $originalCost,
                    ":total_cost" => $discountedCost,
                    ":discount" => $discountValue,
                    ":discount_type" => $discountType,
                    ":discount_amount" => ($discountType === 'flat') ? $discount : round(max(0, $originalCost - $discountedCost), 2),
                    ":booking_id" => $booking_id
                ]);

                // Also update the balance in the bookings table
                $stmt = $this->conn->prepare("SELECT SUM(amount) AS total_paid FROM payments WHERE booking_id = :booking_id AND status = 'Confirmed'");
                $stmt->execute([":booking_id" => $booking_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $totalPaid = isset($result["total_paid"]) ? $result["total_paid"] : 0;

                // Calculate balance with proper rounding
                $balance = round($discountedCost - $totalPaid, 2);

                // Handle tiny negative balances
                if ($balance > -0.1 && $balance < 0) {
                    $balance = 0;
                }

                // Update payment status
                $newStatus = "Unpaid";
                if ($totalPaid > 0 && $totalPaid < $discountedCost) {
                    $newStatus = "Partially Paid";
                } elseif ($totalPaid >= $discountedCost) {
                    $newStatus = "Paid";
                }

                $stmt = $this->conn->prepare("UPDATE bookings SET balance = :balance, payment_status = :payment_status WHERE booking_id = :booking_id");
                $stmt->execute([
                    ":balance" => $balance,
                    ":payment_status" => $newStatus,
                    ":booking_id" => $booking_id
                ]);
            }


            // Add admin notification
            // $message = "New booking confirmed for " . $bookingInfo['client_name'] . " to " . $bookingInfo['destination'];
            // $this->notificationModel->addNotification("booking_confirmed", $message, $booking_id);

            // Add client notification
            $clientMessage = "Your booking to " . $bookingInfo['destination'] . " has been confirmed.";
            $this->clientNotificationModel->addNotification($bookingInfo['user_id'], "booking_confirmed", $clientMessage, $booking_id);

            return "success";
        } catch (PDOException $e) {
            return "Database error: " . $e->getMessage();
        }
    }

    public function rejectBooking($reason, $booking_id, $user_id) {
        $type = "Booking";

        try {
            $stmt = $this->conn->prepare("UPDATE bookings SET status = 'Rejected' WHERE booking_id = :booking_id");
            $stmt->execute([":booking_id" => $booking_id]);

            $stmt = $this->conn->prepare("INSERT INTO rejected_trips (reason, type, booking_id, user_id) VALUES (:reason, :type, :booking_id, :user_id)");
            $stmt->execute([
                ":reason" => $reason,
                ":type" => $type,
                ":booking_id" => $booking_id,
                ":user_id" => $user_id
            ]);

            // Revert booking status if it was in 'Rebooking'
            $stmt = $this->conn->prepare("UPDATE bookings SET status = CASE WHEN status = 'Rebooking' THEN 'Confirmed' ELSE status END WHERE booking_id = :booking_id");
            $stmt->execute([":booking_id" => $booking_id]);

            // Get booking information
            $bookingInfo = $this->getBooking($booking_id);

            // // Add admin notification
            // $message = "Booking rejected for " . $bookingInfo['client_name'] . " to " . $bookingInfo['destination'];
            // $this->notificationModel->addNotification("booking_rejected", $message, $booking_id);

            // Add client notification
            $clientMessage = "Your booking to " . $bookingInfo['destination'] . " has been rejected. Reason: " . $reason;
            $this->clientNotificationModel->addNotification($bookingInfo['user_id'], "booking_rejected", $clientMessage, $booking_id);

            return ["success" => true, "message" => "Booking rejected successfully."];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function getRebookingRequests($status, $column, $order, $page = null, $limit = null) {
        $allowed_status = ["Pending", "Confirmed", "Rejected", "All"];
        $status = in_array($status, $allowed_status) ? $status : "";
        $status == "All" ? $status = "" : $status = " WHERE r.status = '$status'";

        // Align allowed sort columns with UI headers
        $allowed_columns = [
            "booking_id",
            "client_name",
            "contact_number",
            "email",
            "date_of_tour",
            "status"
        ];

        $column = in_array($column, $allowed_columns) ? $column : "booking_id";
        $order = $order === "asc" ? "ASC" : "DESC";

        try {
            $sql = "
                SELECT b.booking_id, r.request_id, r.status as rebooking_status, b.user_id, CONCAT(u.first_name, ' ', u.last_name) AS client_name, u.contact_number, u.email, b.destination, b.pickup_point, b.number_of_days, b.number_of_buses, r.status, b.payment_status, c.total_cost, b.balance, b.date_of_tour, b.end_of_tour
                FROM rebooking_request r
                JOIN users u ON r.user_id = u.user_id
                JOIN bookings b ON r.booking_id = b.booking_id
                JOIN booking_costs c ON r.booking_id = c.booking_id
                $status
                ORDER BY $column $order
            ";

            if (!is_null($page) && !is_null($limit) && (int)$page > 0 && (int)$limit > 0) {
                $offset = ((int)$page - 1) * (int)$limit;
                $sql .= " LIMIT :limit OFFSET :offset";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }  catch (PDOException $e) {
            return "Database error: $e";
        }
    }

    public function getBookingIdFromRebookingRequest($booking_id) {
        try {
            $stmt = $this->conn->prepare("SELECT booking_id FROM rebooking_request WHERE booking_id = :booking_id");
            $stmt->execute([ ":booking_id" => $booking_id ]);
            $result = $stmt->fetchColumn();

            if ($result === false) {
                return null; // No booking ID found
            }

            return $result;
        } catch (PDOException $e) {
            return "Database error: " . $e->getMessage();
        }
    }

    public function getAuditTrailByBookingId($bookingId) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM audit_trails WHERE entity_id = :entity_id AND entity_type = 'bookings' ORDER BY created_at DESC");
            $stmt->execute([':entity_id' => $bookingId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Audit trail error: " . $e->getMessage());
            return null;
        }
    }

    public function confirmRebookingRequest($booking_id, $discount = null, $discountType = null, $newBookingData = []) {
        try {
            // First, let's verify the rebooking request exists
            $stmt = $this->conn->prepare("SELECT * FROM rebooking_request WHERE booking_id = :booking_id");
            $stmt->execute([":booking_id" => $booking_id]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                return ["success" => false, "message" => "Rebooking request not found."];
            }

            // Get the original booking ID (we're getting it directly from the query result)
            $booking_id = $request['booking_id'];

            if (!$booking_id) {
                return ["success" => false, "message" => "Original booking ID not found."];
            }

            // Update rebooking request status
            $stmt = $this->conn->prepare("UPDATE rebooking_request SET status = 'Confirmed' WHERE booking_id = :booking_id");
            $stmt->execute([":booking_id" => $booking_id]);

            $result = $this->updateBooking(
                $booking_id,
                $newBookingData['date_of_tour'],
                $newBookingData['destination'],
                $newBookingData['pickup_point'],
                $newBookingData['number_of_days'],
                $newBookingData['number_of_buses'],
                $newBookingData['user_id'],
                $newBookingData['stops'],
                $newBookingData['booking_costs']['total_cost'],
                $newBookingData['balance'],
                $newBookingData['trip_distances'],
                $newBookingData['addresses'],
                $newBookingData['booking_costs']['base_cost'] ?? null,
                $newBookingData['booking_costs']['diesel_cost'] ?? null,
                $newBookingData['booking_costs']['base_rate'] ?? null,
                $newBookingData['booking_costs']['diesel_price'] ?? null,
                $newBookingData['booking_costs']['total_distance'] ?? null,
                $newBookingData['pickup_time'] ?? null
            );

            if (!$result["success"]) {
                return; // Return error if update failed
            }

            // Apply discount if provided
            if ($discount !== null && $discount > 0) {

                // Get current booking cost
                $stmt = $this->conn->prepare("SELECT total_cost FROM booking_costs WHERE booking_id = :booking_id");
                $stmt->execute([":booking_id" => $booking_id]);
                $originalCost = (float)$stmt->fetchColumn();

                // Calculate new discounted cost based on discount type
                $discountedCost = $originalCost;
                $discountValue = $discount;

                if ($discountType === 'percentage') {
                    // Percentage discount
                    $discountMultiplier = (100 - $discount) / 100;
                    $discountedCost = round($originalCost * $discountMultiplier, 2);
                } else if ($discountType === 'flat') {
                    // Flat amount discount
                    $discountedCost = max(0, round($originalCost - $discount, 2));
                    // Calculate equivalent percentage for storage
                    $discountValue = min(100, round(($discount / $originalCost) * 100, 2));
                }



            // Update the booking costs with discount
            $stmt = $this->conn->prepare("
                UPDATE booking_costs 
                SET gross_price = :gross_price, 
                    total_cost = :total_cost, 
                    discount = :discount, 
                    discount_type = :discount_type, 
                    discount_amount = :discount_amount 
                WHERE booking_id = :booking_id
            ");
            $stmt->execute([
                ":gross_price" => $originalCost,
                ":total_cost" => $discountedCost,
                ":discount" => $discountValue,
                ":discount_type" => $discountType,
                ":discount_amount" => ($discountType === 'flat') 
                    ? $discount 
                    : round(max(0, $originalCost - $discountedCost), 2),
                ":booking_id" => $booking_id
            ]);
                // Use discounted cost for further calculations
                $total_cost = $discountedCost;
            } else {
                // Get total cost for the new booking
                $stmt = $this->conn->prepare("
                    SELECT c.total_cost 
                    FROM booking_costs c 
                    WHERE c.booking_id = :booking_id
                ");
                $stmt->execute([":booking_id" => $booking_id]);
                $total_cost = (float) $stmt->fetchColumn();
            }

            // Get total paid amount from payments for the new booking
            $stmt = $this->conn->prepare("
                SELECT SUM(amount) AS total_paid 
                FROM payments 
                WHERE booking_id = :booking_id 
                AND status = 'Confirmed'
            ");
            $stmt->execute([":booking_id" => $booking_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_paid = isset($result["total_paid"]) ? $result["total_paid"] : 0;

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

            $stmt = $this->conn->prepare("
                UPDATE bookings 
                SET payment_status = :payment_status, 
                    status = 'Confirmed', 
                    balance = :balance, 
                    confirmed_at = NOW() 
                WHERE booking_id = :booking_id
            ");
            $stmt->execute([
                ":payment_status" => $new_status,
                ":booking_id" => $booking_id,
                ":balance" => $balance
            ]);

            // Get booking information
            $bookingInfo = $this->getBooking($booking_id);

            // Add admin notification
            $message = "Rebooking confirmed for " . $bookingInfo['client_name'] . " to " . $bookingInfo['destination'];
            $this->notificationModel->addNotification("rebooking_confirmed", $message, $booking_id);

            // Add client notification
            $clientMessage = "Your rebooking request for the trip to " . $bookingInfo['destination'] . " has been confirmed.";
            $this->clientNotificationModel->addNotification($bookingInfo['user_id'], "rebooking_confirmed", $clientMessage, $booking_id);

            return ["success" => true, "message" => "Rebooking request confirmed successfully."];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function findAvailableBuses($date_of_tour, $end_of_tour, $number_of_buses) {
        try {
            $stmt = $this->conn->prepare("
                SELECT bus_id
                FROM buses
                WHERE status = 'active'
                AND bus_id NOT IN (
                    SELECT bb.bus_id
                    FROM booking_buses bb
                    JOIN bookings bo ON bb.booking_id = bo.booking_id
                    WHERE
                        -- Only consider active bookings that need buses
                        (bo.status = 'Confirmed' OR bo.status = 'Processing')
                        -- Date range check
                        AND (
                            (bo.date_of_tour <= :date_of_tour AND bo.end_of_tour >= :date_of_tour)
                            OR (bo.date_of_tour <= :end_of_tour AND bo.end_of_tour >= :end_of_tour)
                            OR (bo.date_of_tour >= :date_of_tour AND bo.end_of_tour <= :end_of_tour)
                        )
                )
                LIMIT :number_of_buses
            ");

            $stmt->bindParam(":date_of_tour", $date_of_tour);
            $stmt->bindParam(":end_of_tour", $end_of_tour);
            $stmt->bindParam(":number_of_buses", $number_of_buses, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return "Database error: " . $e->getMessage();
        }
    }

    public function findAvailableDrivers($date_of_tour, $end_of_tour, $number_of_drivers) {
        try {
            $stmt = $this->conn->prepare("
                SELECT driver_id
                FROM drivers
                WHERE status = 'Active'
                AND availability = 'Available'
                AND driver_id NOT IN (
                    SELECT bd.driver_id
                    FROM booking_driver bd
                    JOIN bookings bo ON bd.booking_id = bo.booking_id
                    WHERE
                        -- Only consider active bookings that need drivers
                        (bo.status = 'Confirmed' OR bo.status = 'Processing')
                        -- Date range check
                        AND (
                            (bo.date_of_tour <= :date_of_tour AND bo.end_of_tour >= :date_of_tour)
                            OR (bo.date_of_tour <= :end_of_tour AND bo.end_of_tour >= :end_of_tour)
                            OR (bo.date_of_tour >= :date_of_tour AND bo.end_of_tour <= :end_of_tour)
                        )
                )
                LIMIT :number_of_drivers
            ");

            $stmt->bindParam(":date_of_tour", $date_of_tour);
            $stmt->bindParam(":end_of_tour", $end_of_tour);
            $stmt->bindParam(":number_of_drivers", $number_of_drivers, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return "Database error: " . $e->getMessage();
        }
    }

    public function updateBooking(
        $booking_id, $date_of_tour, $destination, $pickup_point, $number_of_days, 
        $number_of_buses, $user_id, $stops, $total_cost, $balance, $trip_distances, 
        $addresses, $base_cost = null, $diesel_cost = null, $base_rate = null, 
        $diesel_price = null, $total_distance = null, $pickup_time = null
    ) {
        $days = $number_of_days - 1;
        $end_of_tour = date("Y-m-d", strtotime($date_of_tour . " + $days days"));

        try {
            // Check bus availability
            $available_buses = $this->findAvailableBuses($date_of_tour, $end_of_tour, $number_of_buses);
            if (!$available_buses) {
                return "Not enough buses available.";
            }

            // Check driver availability
            $available_drivers = $this->findAvailableDrivers($date_of_tour, $end_of_tour, $number_of_buses);
            if (!$available_drivers || count($available_drivers) < $number_of_buses) {
                return "Not enough drivers available for the selected dates.";
            }

            // Update booking details
            $stmt = $this->conn->prepare("
                UPDATE bookings 
                SET date_of_tour = :date_of_tour, 
                    end_of_tour = :end_of_tour, 
                    destination = :destination, 
                    pickup_point = :pickup_point, 
                    pickup_time = :pickup_time, 
                    number_of_days = :number_of_days, 
                    number_of_buses = :number_of_buses, 
                    balance = :balance 
                WHERE booking_id = :booking_id AND user_id = :user_id
            ");
            $stmt->execute([
                ":date_of_tour" => $date_of_tour,
                ":end_of_tour" => $end_of_tour,
                ":destination" => $destination,
                ":pickup_point" => $pickup_point,
                ":pickup_time" => $pickup_time,
                ":number_of_days" => $number_of_days,
                ":number_of_buses" => $number_of_buses,
                ":balance" => $balance,
                ":booking_id" => $booking_id,
                ":user_id" => $user_id
            ]);

            // Update booking costs
            $stmt = $this->conn->prepare("
                UPDATE booking_costs 
                SET base_rate = :base_rate, 
                    base_cost = :base_cost, 
                    diesel_price = :diesel_price, 
                    diesel_cost = :diesel_cost, 
                    total_cost = :total_cost, 
                    total_distance = :total_distance 
                WHERE booking_id = :booking_id
            ");
            $stmt->execute([
                ":base_rate" => $base_rate,
                ":base_cost" => $base_cost,
                ":diesel_price" => $diesel_price,
                ":diesel_cost" => $diesel_cost,
                ":total_cost" => $total_cost,
                ":total_distance" => $total_distance,
                ":booking_id" => $booking_id
            ]);

            // Update stops
            $stmt = $this->conn->prepare("DELETE FROM booking_stops WHERE booking_id = :booking_id");
            $stmt->execute([":booking_id" => $booking_id]);

            $stops = is_array($stops) ? $stops : [];
            foreach ($stops as $index => $stop) {
                $stmt = $this->conn->prepare("
                    INSERT INTO booking_stops (booking_id, location, stop_order) 
                    VALUES (:booking_id, :location, :stop_order)
                ");
                $stmt->execute([
                    ":booking_id" => $booking_id,
                    ":location" => $stop["location"],
                    ":stop_order" => $index + 1
                ]);
            }

            // Update trip distances
            $stmt = $this->conn->prepare("DELETE FROM trip_distances WHERE booking_id = :booking_id");
            $stmt->execute([":booking_id" => $booking_id]);

            foreach ($trip_distances["rows"] as $i => $row) {
                $distance_value = $row["elements"][$i]["distance"]["value"] ?? 0;
                $origin = $addresses[$i];
                $destination = $addresses[$i + 1] ?? $addresses[0]; // Round-trip fallback

                $stmt = $this->conn->prepare("
                    INSERT INTO trip_distances (origin, destination, distance, booking_id) 
                    VALUES (:origin, :destination, :distance, :booking_id)
                ");
                $stmt->execute([
                    ":origin" => $origin,
                    ":destination" => $destination,
                    ":distance" => $distance_value,
                    ":booking_id" => $booking_id
                ]);
            }

            // Update buses
            $stmt = $this->conn->prepare("DELETE FROM booking_buses WHERE booking_id = :booking_id");
            $stmt->execute([":booking_id" => $booking_id]);

            foreach ($available_buses as $bus_id) {
                $stmt = $this->conn->prepare("
                    INSERT INTO booking_buses (booking_id, bus_id) 
                    VALUES (:booking_id, :bus_id)
                ");
                $stmt->execute([
                    ":booking_id" => $booking_id,
                    ":bus_id" => $bus_id
                ]);
            }

            // Update drivers
            $stmt = $this->conn->prepare("DELETE FROM booking_driver WHERE booking_id = :booking_id");
            $stmt->execute([":booking_id" => $booking_id]);

            foreach ($available_drivers as $index => $driver_id) {
                if ($index >= $number_of_buses) break; // Limit driver count to bus count
                $stmt = $this->conn->prepare("
                    INSERT INTO booking_driver (booking_id, driver_id) 
                    VALUES (:booking_id, :driver_id)
                ");
                $stmt->execute([
                    ":booking_id" => $booking_id,
                    ":driver_id" => $driver_id
                ]);
            }

            return ["success" => true, "message" => "Booking updated successfully."];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function rejectRebooking($reason, $booking_id, $user_id) {
        $type = "Rebooking";

        try {
            // Update rebooking request status
            $stmt = $this->conn->prepare("
                UPDATE rebooking_request 
                SET status = 'Rejected' 
                WHERE booking_id = :booking_id
            ");
            $stmt->execute([":booking_id" => $booking_id]);

            // Log rejection reason
            $stmt = $this->conn->prepare("
                INSERT INTO rejected_trips (reason, type, booking_id, user_id) 
                VALUES (:reason, :type, :booking_id, :user_id)
            ");
            $stmt->execute([
                ":reason" => $reason,
                ":type" => $type,
                ":booking_id" => $booking_id,
                ":user_id" => $user_id
            ]);

            // Revert booking status if it was in "Rebooking"
            $stmt = $this->conn->prepare("
                UPDATE bookings 
                SET status = CASE 
                    WHEN status = 'Rebooking' THEN 'Confirmed' 
                    ELSE status 
                END 
                WHERE booking_id = :booking_id
            ");
            $stmt->execute([":booking_id" => $booking_id]);

            // Get booking info
            $bookingInfo = $this->getBooking($booking_id);

            if (is_array($bookingInfo)) {
                // Notify admin
                $message = "Rebooking rejected for " 
                    . $bookingInfo['client_name'] 
                    . " to " . $bookingInfo['destination'];
                $this->notificationModel->addNotification("rebooking_rejected", $message, $booking_id);

                // Notify client
                $clientMessage = "Your rebooking request for the trip to " 
                    . $bookingInfo['destination'] 
                    . " has been rejected. Reason: " . $reason;
                $this->clientNotificationModel->addNotification(
                    $bookingInfo['user_id'],
                    "rebooking_rejected",
                    $clientMessage,
                    $booking_id
                );
            }

            return ["success" => true, "message" => "Rebooking request rejected successfully."];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    public function getBooking($booking_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    b.*, 
                    u.user_id, 
                    CONCAT(u.first_name, ' ', u.last_name) AS client_name, 
                    u.email, 
                    u.contact_number, 
                    c.*
                FROM bookings b
                JOIN users u ON b.user_id = u.user_id
                JOIN booking_costs c ON b.booking_id = c.booking_id
                WHERE b.booking_id = :booking_id
            ");
            $stmt->execute([":booking_id" => $booking_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return null; // No booking found
            }

            return $result;
        } catch (PDOException $e) {
            error_log("Error in getBooking: " . $e->getMessage());
            return ["error" => "Database error: " . $e->getMessage()];
        }
    }

    public function isClientPaid($booking_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT payment_status 
                FROM bookings 
                WHERE booking_id = :booking_id
            ");
            $stmt->execute([":booking_id" => $booking_id]);
            $payment_status = $stmt->fetchColumn();

            return $payment_status === "Partially Paid" || $payment_status === "Paid";
        } catch (PDOException $e) {
            return [
                "success" => false,
                "message" => "Database error: " . $e->getMessage()
            ];
        }
    }

    public function cancelPayment($booking_id, $user_id) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE payments 
                SET is_canceled = 1 
                WHERE booking_id = :booking_id 
                AND user_id = :user_id
            ");
            $stmt->execute([
                ":booking_id" => $booking_id,
                ":user_id" => $user_id
            ]);

            return ["success" => true];
        } catch (PDOException $e) {
            return [
                "success" => false,
                "message" => "Database error: " . $e->getMessage()
            ];
        }
    }

    public function getAmountPaid($booking_id, $user_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT SUM(amount) AS total_amount 
                FROM payments 
                WHERE status = 'Confirmed' 
                AND booking_id = :booking_id 
                AND user_id = :user_id
            ");
            $stmt->execute([
                ":booking_id" => $booking_id,
                ":user_id" => $user_id
            ]);

            return (float) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return [
                "success" => false,
                "message" => "Database error: " . $e->getMessage()
            ];
        }
    }


    public function cancelBooking($reason, $booking_id, $user_id, $amount_refunded, $reason_category = null) {
        try {
            // Update booking status
            $stmt = $this->conn->prepare("
                UPDATE bookings 
                SET status = 'Canceled' 
                WHERE booking_id = :booking_id
            ");
            $stmt->execute([":booking_id" => $booking_id]);

            // Insert cancellation record
            $stmt = $this->conn->prepare("
                INSERT INTO canceled_trips (
                    reason, booking_id, user_id, amount_refunded, 
                    canceled_by, cancellation_reason_category, custom_reason
                ) VALUES (
                    :reason, :booking_id, :user_id, :amount_refunded, 
                    :canceled_by, :cancellation_reason_category, :custom_reason
                )
            ");
            $stmt->execute([
                ":reason" => $reason,
                ":booking_id" => $booking_id,
                ":user_id" => $user_id,
                ":amount_refunded" => $amount_refunded,
                ":canceled_by" => $_SESSION["role"],
                ":cancellation_reason_category" => $reason_category,
                ":custom_reason" => ($reason_category === 'other') ? $reason : null
            ]);

            // Get booking information
            $bookingInfo = $this->getBooking($booking_id);

            // Add admin notification
            $message = "Booking canceled for " 
                . $bookingInfo['client_name'] 
                . " to " . $bookingInfo['destination'];
            $this->notificationModel->addNotification("booking_canceled", $message, $booking_id);

            // Add client notification
            $clientMessage = "Your booking to " . $bookingInfo['destination'] . " has been canceled.";
            if ($amount_refunded > 0) {
                $clientMessage .= " Refunded amount: " . $amount_refunded;
            }

            $this->clientNotificationModel->addNotification(
                $bookingInfo['user_id'],
                "booking_canceled",
                $clientMessage,
                $booking_id
            );

            return ["success" => true];
        } catch (PDOException $e) {
            return [
                "success" => false,
                "message" => "Database error: " . $e->getMessage()
            ];
        }
    }

    public function getBookingStops($booking_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT * 
                FROM booking_stops 
                WHERE booking_id = :booking_id 
                ORDER BY stop_order
            ");
            $stmt->execute([":booking_id" => $booking_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
        } catch (PDOException $e) {
            return "Database error.";
        }
    }

    public function getTripDistances($booking_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT * 
                FROM trip_distances 
                WHERE booking_id = :booking_id
            ");
            $stmt->execute([":booking_id" => $booking_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return "Database error.";
        }
    }

    public function getDieselPrice() {
        try {
            // First, check if the diesel price is defined in settings
            require_once __DIR__ . "/Settings.php";
            $settings = new Settings();
            $diesel_price = $settings->getSetting('diesel_price');

            if ($diesel_price !== null) {
                return (float) $diesel_price;
            }

            // Fallback to diesel_per_liter table
            $stmt = $this->conn->prepare("
                SELECT price 
                FROM diesel_per_liter 
                ORDER BY date DESC 
                LIMIT 1
            ");
            $stmt->execute();
            $diesel_price = $stmt->fetchColumn() ?? 0;

            return (float) $diesel_price;
        } catch (PDOException $e) {
            return "Database error: " . $e->getMessage();
        }
    }

    public function summaryMetrics($startDate = null, $endDate = null) {
        try {
            $dateFilter = "";
            $params = [];

            if ($startDate && $endDate) {
                $dateFilter = "AND date_of_tour BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }

            // 1️⃣ Total Bookings
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) AS total_bookings 
                FROM bookings 
                WHERE 1=1 $dateFilter
            ");
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $total_bookings = $stmt->fetchColumn();

            // 2️⃣ Total Revenue
            $revenueQuery = "
                SELECT SUM(p.amount) AS total_revenue
                FROM payments p
                JOIN bookings b ON p.booking_id = b.booking_id
                WHERE p.is_canceled = 0 
                AND p.status = 'Confirmed'
            ";
            if ($startDate && $endDate) {
                $revenueQuery .= " AND b.date_of_tour BETWEEN :start_date AND :end_date";
            }

            $stmt = $this->conn->prepare($revenueQuery);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $total_revenue = $stmt->fetchColumn() ?? 0;

            // 3️⃣ Upcoming Trips
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) AS upcoming_trips 
                FROM bookings 
                WHERE status = 'Confirmed' 
                AND date_of_tour > CURDATE()
                AND payment_status IN ('Paid', 'Partially Paid') 
                $dateFilter
            ");
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $upcoming_trips = $stmt->fetchColumn();

            // 4️⃣ Pending Bookings
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) AS pending_bookings 
                FROM bookings 
                WHERE status = 'Pending' 
                $dateFilter
            ");
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $pending_bookings = $stmt->fetchColumn();

            // 5️⃣ Processing Payments
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) AS processing_payments 
                FROM bookings 
                WHERE status = 'Processing' 
                AND payment_status IN ('Unpaid', 'Partially Paid') 
                $dateFilter
            ");
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $processing_payments = $stmt->fetchColumn();

            // 6️⃣ Flagged Bookings
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) AS flagged_bookings 
                FROM bookings 
                WHERE status = 'Confirmed'
                AND payment_status IN ('Unpaid', 'Partially Paid')
                AND date_of_tour < CURDATE()
                $dateFilter
            ");
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $flagged_bookings = $stmt->fetchColumn();

            // ✅ Return summary
            return [
                "total_bookings"       => (int)$total_bookings,
                "total_revenue"        => (float)$total_revenue,
                "upcoming_trips"       => (int)$upcoming_trips,
                "pending_bookings"     => (int)$pending_bookings,
                "processing_payments"  => (int)$processing_payments,
                "flagged_bookings"     => (int)$flagged_bookings
            ];

        } catch (PDOException $e) {
            return "Database error: " . $e->getMessage();
        }
    }

    public function getMonthlyBookingTrends($startDate = null, $endDate = null) {
        try {
            $year = date('Y');
            $dateFilter = "";
            $params = [':year' => $year];

            if ($startDate && $endDate) {
                $dateFilter = "AND date_of_tour BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
                unset($params[':year']); // remove year filter if specific range
            }

            // 1️⃣ Booking Count by Month
            $bookingQuery = "
                SELECT MONTH(date_of_tour) AS month, COUNT(booking_id) AS booking_count
                FROM bookings
                WHERE 1=1
            ";
            $bookingQuery .= $startDate && $endDate
                ? " $dateFilter"
                : " AND YEAR(date_of_tour) = :year";
            $bookingQuery .= " GROUP BY MONTH(date_of_tour) ORDER BY month";

            $stmt = $this->conn->prepare($bookingQuery);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $bookingResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 2️⃣ Revenue by Month
            $revenueQuery = "
                SELECT 
                    MONTH(b.date_of_tour) AS month,
                    SUM(CASE WHEN p.status = 'Confirmed' AND p.is_canceled = 0 THEN p.amount ELSE 0 END) AS total_revenue
                FROM bookings b
                LEFT JOIN payments p ON b.booking_id = p.booking_id
                WHERE 1=1
            ";
            $revenueQuery .= $startDate && $endDate
                ? " $dateFilter"
                : " AND YEAR(b.date_of_tour) = :year";
            $revenueQuery .= " GROUP BY MONTH(b.date_of_tour) ORDER BY month";

            $stmt = $this->conn->prepare($revenueQuery);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $revenueResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3️⃣ Initialize months
            $months = [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ];

            $bookingData = array_fill_keys(array_keys($months), 0);
            $revenueData = array_fill_keys(array_keys($months), 0);

            // Fill in results
            foreach ($bookingResults as $result) {
                $bookingData[(int)$result['month']] = (int)$result['booking_count'];
            }

            foreach ($revenueResults as $result) {
                $revenueData[(int)$result['month']] = (float)$result['total_revenue'];
            }

            // ✅ Return chart data
            return [
                'labels'   => array_values($months),
                'counts'   => array_values($bookingData),
                'revenues' => array_values($revenueData),
                'year'     => $year
            ];

        } catch (PDOException $e) {
            error_log("Error in getMonthlyBookingTrends: " . $e->getMessage());
            return "Database error: " . $e->getMessage();
        }
    }

    public function getTopDestinations($startDate = null, $endDate = null) {
        try {
            $dateFilter = "";
            $params = [];

            if ($startDate && $endDate) {
                $dateFilter = "AND date_of_tour BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }

            $query = "
                SELECT
                    b.destination as destination,
                    COUNT(b.booking_id) as booking_count,
                    SUM(CASE WHEN p.status = 'Confirmed' AND p.is_canceled = 0 THEN p.amount ELSE 0 END) as total_revenue
                FROM bookings b
                LEFT JOIN payments p ON b.booking_id = p.booking_id
                WHERE b.status IN ('Confirmed', 'Completed')
                AND b.payment_status IN ('Paid', 'Partially Paid')
                $dateFilter
                GROUP BY destination
                ORDER BY booking_count DESC
                LIMIT 10
            ";

            $stmt = $this->conn->prepare($query);
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
            }
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $destinations = [];
            $counts = [];
            $revenues = [];

            foreach ($results as $row) {
                $destinations[] = $row['destination'];
                $counts[] = (int)$row['booking_count'];
                $revenues[] = (float)$row['total_revenue'];
            }

            return [
                'labels' => $destinations,
                'counts' => $counts,
                'revenues' => $revenues
            ];
        } catch (PDOException $e) {
            error_log("Error in getTopDestinations: " . $e->getMessage());
            return "Database error: " . $e->getMessage();
        }

    }



    public function getBookingStatusDistribution($startDate = null, $endDate = null) {

        try {

            $dateFilter = "";

            $params = [];



            if ($startDate && $endDate) {

                $dateFilter = "AND date_of_tour BETWEEN :start_date AND :end_date";

                $params[':start_date'] = $startDate;

                $params[':end_date'] = $endDate;

            }



            $query = "

                SELECT

                    b.status as status,

                    COUNT(b.booking_id) as count,

                    SUM(CASE WHEN b.payment_status IN ('Paid', 'Partially Paid') AND b.status IN ('Confirmed', 'Completed') THEN c.total_cost ELSE 0 END) as total_revenue

                FROM bookings b

                JOIN booking_costs c ON b.booking_id = c.booking_id

                WHERE 1=1 $dateFilter

                GROUP BY status

            ";



            $stmt = $this->conn->prepare($query);

            if (!empty($params)) {

                foreach ($params as $key => $value) {

                    $stmt->bindValue($key, $value);

                }

            }

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



            // Define all possible statuses and their colors

            $statusColors = [

                'Pending' => '#ffc107',

                'Confirmed' => '#198754',

                'Canceled' => '#dc3545',

                'Rejected' => '#6c757d',

                'Completed' => '#0d6efd',

                'Processing' => '#fd7e14'

            ];



            // Initialize data structure

            $labels = [];

            $data = [];

            $values = [];



            // Fill in actual data

            foreach ($results as $row) {

                $labels[] = $row['status'];

                $data[] = (int)$row['count'];

                $values[] = (float)$row['total_revenue']; // Default color if status not found

            }



            return [

                'labels' => $labels,

                'counts' => $data,

                'values' => $values

            ];

        } catch (PDOException $e) {

            error_log("Error in getBookingStatusDistribution: " . $e->getMessage());

            return "Database error: " . $e->getMessage();

        }

    }



    public function paymentMethodChart($startDate = null, $endDate = null) {

        try {

            $dateFilter = "";

            $params = [];



            if ($startDate && $endDate) {

                $dateFilter = "AND b.date_of_tour BETWEEN :start_date AND :end_date";

                $params[':start_date'] = $startDate;

                $params[':end_date'] = $endDate;

            }



            $query = "

                SELECT

                    p.payment_method,

                    COUNT(*) as count,

                    SUM(p.amount) as amount

                FROM payments p

                JOIN bookings b ON p.booking_id = b.booking_id

                WHERE p.status = 'Confirmed' AND p.is_canceled = 0

                $dateFilter

                GROUP BY p.payment_method

            ";



            $stmt = $this->conn->prepare($query);

            if (!empty($params)) {

                foreach ($params as $key => $value) {

                    $stmt->bindValue($key, $value);

                }

            }

            $stmt->execute();



            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



            $labels = [];

            $data = [];

            $amounts = [];



            foreach ($results as $row) {

                $labels[] = $row['payment_method'];

                $data[] = (int)$row['count'];

                $amounts[] = (float)$row['amount'];

            }



            return [

                'labels' => $labels,

                'counts' => $data,

                'amounts' => $amounts

            ];

        } catch (PDOException $e) {

            error_log("Error in paymentMethodChart: " . $e->getMessage());

            return "Database error: " . $e->getMessage();

        }

    }



    public function getRevenueTrends($startDate = null, $endDate = null) {

        try {

            // Get current year

            $year = date('Y');

            $dateFilter = "";

            $params = [':year' => $year];



            if ($startDate && $endDate) {

                $dateFilter = "AND date_of_tour BETWEEN :start_date AND :end_date";

                $params[':start_date'] = $startDate;

                $params[':end_date'] = $endDate;

                // Remove year filter if we have specific date range

                unset($params[':year']);

            }



            // Get booking counts

            $bookingQuery = "

                SELECT

                    MONTH(date_of_tour) as month,

                    COUNT(booking_id) as booking_count

                FROM bookings

                WHERE 1=1

            ";



            if ($startDate && $endDate) {

                $bookingQuery .= " $dateFilter";

            } else {

                $bookingQuery .= " AND YEAR(date_of_tour) = :year";

            }



            $bookingQuery .= " GROUP BY MONTH(date_of_tour) ORDER BY month";



            $stmt = $this->conn->prepare($bookingQuery);

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            $stmt->execute();

            $bookingResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get revenue data

            $revenueQuery = "

                SELECT

                    MONTH(b.date_of_tour) as month,

                    SUM(CASE WHEN p.status = 'Confirmed' AND p.is_canceled = 0 THEN p.amount ELSE 0 END) as total_revenue

                FROM bookings b

                LEFT JOIN payments p ON b.booking_id = p.booking_id

            ";



            if ($startDate && $endDate) {

                $revenueQuery .= " $dateFilter";

            } else {

                $revenueQuery .= " AND YEAR(b.date_of_tour) = :year";

            }



            $revenueQuery .= " GROUP BY MONTH(b.date_of_tour) ORDER BY month";



            $stmt = $this->conn->prepare($revenueQuery);

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            $stmt->execute();

            $revenueResults = $stmt->fetchAll(PDO::FETCH_ASSOC);



            // Initialize data for all months

            $months = [

                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',

                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',

                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'

            ];



            $bookingData = [];

            $revenueData = [];



            foreach ($months as $monthNum => $monthName) {

                $bookingData[$monthNum] = 0;

                $revenueData[$monthNum] = 0;

            }



            // Fill in actual data

            foreach ($bookingResults as $result) {

                $bookingData[$result['month']] = (int)$result['booking_count'];

            }



            foreach ($revenueResults as $result) {

                $revenueData[$result['month']] = (float)$result['total_revenue'];

            }



            // Format for chart.js

            $labels = array_values($months);

            $bookingCounts = array_values($bookingData);

            $revenueCounts = array_values($revenueData);



            return [

                'labels' => $labels,

                'counts' => $bookingCounts,

                'revenues' => $revenueCounts,

                'year' => $year

            ];

        } catch (PDOException $e) {

            error_log("Error in getRevenueTrends: " . $e->getMessage());

            return "Database error: " . $e->getMessage();

        }

    }

    // New method for getting booking stats for the dashboard

    public function getBookingStats() {

        try {

            // Get total bookings

            $stmt = $this->conn->prepare("

                SELECT COUNT(*) AS total

                FROM bookings

            ");

            $stmt->execute();

            $total = $stmt->fetchColumn();



            // Get confirmed bookings

            $stmt = $this->conn->prepare("

                SELECT COUNT(*) AS confirmed

                FROM bookings

                WHERE status = 'Confirmed'

            ");

            $stmt->execute();

            $confirmed = $stmt->fetchColumn();



            // Get pending bookings

            $stmt = $this->conn->prepare("

                SELECT COUNT(*) AS pending

                FROM bookings

                WHERE status = 'Pending'

            ");

            $stmt->execute();

            $pending = $stmt->fetchColumn();



            // Get upcoming tours (future dates with confirmed status)

            $stmt = $this->conn->prepare("

                SELECT COUNT(*) AS upcoming

                FROM bookings

                WHERE status = 'Confirmed'

                AND date_of_tour >= CURDATE()

            ");

            $stmt->execute();

            $upcoming = $stmt->fetchColumn();



            return [

                'total' => $total,

                'confirmed' => $confirmed,

                'pending' => $pending,

                'upcoming' => $upcoming

            ];

        } catch (PDOException $e) {

            return "Database error: " . $e->getMessage();

        }

    }



    // New method for getting calendar bookings

    public function getCalendarBookings($start, $end) {

        try {

            $stmt = $this->conn->prepare("

                SELECT b.booking_id, b.user_id, CONCAT(u.first_name, ' ', u.last_name) AS client_name,

                u.contact_number, u.email, b.destination, b.pickup_point,

                b.date_of_tour, b.end_of_tour, b.number_of_days, b.number_of_buses,

                b.status, b.payment_status, c.total_cost, b.balance,

                b.created_at, b.updated_at

                FROM bookings b

                JOIN users u ON b.user_id = u.user_id

                JOIN booking_costs c ON b.booking_id = c.booking_id

                WHERE ((b.date_of_tour BETWEEN :start AND :end)

                    OR (b.end_of_tour BETWEEN :start AND :end)

                    OR (b.date_of_tour <= :start AND b.end_of_tour >= :end))

                ORDER BY b.date_of_tour ASC

            ");

            $stmt->bindParam(':start', $start);

            $stmt->bindParam(':end', $end);

            $stmt->execute();



            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            return "Database error: " . $e->getMessage();

        }

    }



    // New method for searching bookings

    public function searchBookings($searchTerm, $status, $page = 1, $limit = 10) {

        $allowed_status = ["Pending", "Confirmed", "Canceled", "Rejected", "Completed", "Rebooking", "All"];

        $status = in_array($status, $allowed_status) ? $status : "";

        $status_condition = ($status == "All") ? "" : " AND b.status = :status";



        // Calculate offset for pagination

        $offset = ($page - 1) * $limit;



        try {

            $stmt = $this->conn->prepare("

                SELECT b.booking_id, b.user_id, CONCAT(u.first_name, ' ', u.last_name) AS client_name,

                u.contact_number, b.destination, b.pickup_point, b.date_of_tour, b.end_of_tour,

                b.number_of_days, b.number_of_buses, b.status, b.payment_status, c.total_cost, b.balance

                FROM bookings b

                JOIN users u ON b.user_id = u.user_id

                JOIN booking_costs c ON b.booking_id = c.booking_id

                WHERE (

                    CONCAT(u.first_name, ' ', u.last_name) LIKE :search

                    OR u.contact_number LIKE :search

                    OR b.destination LIKE :search

                    OR b.pickup_point LIKE :search

                )

                $status_condition

                ORDER BY b.booking_id DESC

                LIMIT :limit OFFSET :offset

            ");



            $searchParam = "%" . $searchTerm . "%";

            $stmt->bindParam(':search', $searchParam);



            if ($status != "All") {

                $stmt->bindParam(':status', $status);

            }



            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();



            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            return "Database error: " . $e->getMessage();

        }

    }



    // New method for counting search results

    public function getTotalSearchResults($searchTerm, $status) {

        $allowed_status = ["Pending", "Confirmed", "Canceled", "Rejected", "Completed", "All"];

        $status = in_array($status, $allowed_status) ? $status : "";

        $status_condition = ($status == "All") ? "" : " AND b.status = :status";



        try {

            $stmt = $this->conn->prepare("

                SELECT COUNT(*) as total

                FROM bookings b

                JOIN users u ON b.user_id = u.user_id

                WHERE (

                    CONCAT(u.first_name, ' ', u.last_name) LIKE :search

                    OR u.contact_number LIKE :search

                    OR b.destination LIKE :search

                    OR b.pickup_point LIKE :search

                )

                $status_condition

            ");



            $searchParam = "%" . $searchTerm . "%";

            $stmt->bindParam(':search', $searchParam);



            if ($status != "All") {

                $stmt->bindParam(':status', $status);

            }



            $stmt->execute();



            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['total'];

        } catch (PDOException $e) {

            return 0;

        }

    }



    // New method for getting unpaid bookings

    public function getUnpaidBookings($page = 1, $limit = 10, $column = "booking_id", $order = "desc") {

        // Calculate offset for pagination

        $offset = ($page - 1) * $limit;



        // Validate the column to prevent SQL injection

        $allowed_columns = ["booking_id", "client_name", "contact_number", "destination", "pickup_point", "date_of_tour", "end_of_tour", "number_of_days", "number_of_buses", "status", "payment_status", "total_cost"];

        $column = in_array($column, $allowed_columns) ? $column : "booking_id";



        // Validate the order

        $order = strtolower($order) === "asc" ? "ASC" : "DESC";



        try {

            $stmt = $this->conn->prepare("

                SELECT b.booking_id, b.user_id, CONCAT(u.first_name, ' ', u.last_name) AS client_name,

                u.contact_number, b.destination, b.pickup_point, b.date_of_tour, b.end_of_tour,

                b.number_of_days, b.number_of_buses, b.status, b.payment_status, c.total_cost, b.balance

                FROM bookings b

                JOIN users u ON b.user_id = u.user_id

                JOIN booking_costs c ON b.booking_id = c.booking_id

                WHERE b.status = 'Confirmed'

                AND b.payment_status = 'Unpaid'

                ORDER BY $column $order

                LIMIT :limit OFFSET :offset

            ");



            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();



            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            return "Database error: " . $e->getMessage();

        }

    }



    // New method for counting total unpaid bookings

    public function getTotalUnpaidBookings() {

        try {

            $stmt = $this->conn->prepare("

                SELECT COUNT(*) as total

                FROM bookings b

                WHERE status = 'Confirmed'

                AND b.payment_status = 'Unpaid'

            ");



            $stmt->execute();



            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['total'];

        } catch (PDOException $e) {

            return 0;

        }

    }



    // New method for getting partially paid bookings

    public function getPartiallyPaidBookings($page = 1, $limit = 10, $column = "booking_id", $order = "desc") {

        // Calculate offset for pagination

        $offset = ($page - 1) * $limit;



        // Validate the column to prevent SQL injection

        $allowed_columns = ["booking_id", "client_name", "contact_number", "destination", "pickup_point", "date_of_tour", "end_of_tour", "number_of_days", "number_of_buses", "status", "payment_status", "total_cost"];

        $column = in_array($column, $allowed_columns) ? $column : "booking_id";



        // Validate the order

        $order = strtolower($order) === "asc" ? "ASC" : "DESC";



        try {

            $stmt = $this->conn->prepare("

                SELECT b.booking_id, b.user_id, CONCAT(u.first_name, ' ', u.last_name) AS client_name,

                u.contact_number, b.destination, b.pickup_point, b.date_of_tour, b.end_of_tour,

                b.number_of_days, b.number_of_buses, b.status, b.payment_status, c.total_cost, b.balance

                FROM bookings b

                JOIN users u ON b.user_id = u.user_id

                JOIN booking_costs c ON b.booking_id = c.booking_id

                WHERE (b.status = 'Confirmed' OR b.status = 'Completed')

                AND b.payment_status = 'Partially Paid'

                ORDER BY $column $order

                LIMIT :limit OFFSET :offset

            ");



            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();



            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            return "Database error: " . $e->getMessage();

        }

    }



    // New method for counting total partially paid bookings

    public function getTotalPartiallyPaidBookings() {

        try {

            $stmt = $this->conn->prepare("

                SELECT COUNT(*) as total

                FROM bookings b

                WHERE b.payment_status = 'Partially Paid'

            ");



            $stmt->execute();



            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['total'];

        } catch (PDOException $e) {

            return 0;

        }

    }



    // New method for getting all bookings for export

    public function getAllBookingsForExport($status) {

        $allowed_status = ["Pending", "Confirmed", "Canceled", "Rejected", "Completed", "All"];

        $status = in_array($status, $allowed_status) ? $status : "";

        $status_condition = ($status == "All") ? "" : " AND b.status = :status";



        try {

            $stmt = $this->conn->prepare("

                SELECT b.booking_id, b.user_id, CONCAT(u.first_name, ' ', u.last_name) AS client_name,

                u.contact_number, u.email, b.destination, b.pickup_point,

                b.date_of_tour, b.end_of_tour, b.number_of_days, b.number_of_buses,

                b.status, b.payment_status, c.total_cost, b.balance

                FROM bookings b

                JOIN users u ON b.user_id = u.user_id

                JOIN booking_costs c ON b.booking_id = c.booking_id

                WHERE $status_condition

                ORDER BY b.booking_id DESC

            ");



            if ($status != "All") {

                $stmt->bindParam(':status', $status);

            }



            $stmt->execute();



            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            return [];

        }

    }



    // New method for creating bookings by admin

    public function createBookingByAdmin($data) {

        try {

            $this->conn->beginTransaction();



            // Check if client already exists based on email

            $existingUserId = null;

            $stmt = $this->conn->prepare("SELECT user_id FROM users WHERE email = :email LIMIT 1");

            $stmt->execute([":email" => $data['email']]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);



            if ($result) {

                $existingUserId = $result['user_id'];

            }



            // Create or update user record

            if ($existingUserId) {

                // Update existing user

                $stmt = $this->conn->prepare("

                    UPDATE users SET

                    first_name = :first_name,

                    last_name = :last_name,

                    contact_number = :contact_number,

                    address = :address

                    WHERE user_id = :user_id

                ");



                // Split the client name into first and last name

                $nameParts = explode(" ", $data['client_name'], 2);

                $firstName = $nameParts[0];

                $lastName = isset($nameParts[1]) ? $nameParts[1] : "";



                $stmt->execute([

                    ":first_name" => $firstName,

                    ":last_name" => $lastName,

                    ":contact_number" => $data['contact_number'],

                    ":address" => $data['address'] ?? '',

                    ":user_id" => $existingUserId

                ]);



                $userId = $existingUserId;

            } else {

                // Create new user

                $stmt = $this->conn->prepare("

                    INSERT INTO users (

                        first_name, last_name, email, contact_number, address,

                        role, created_at, status, created_by

                    ) VALUES (

                        :first_name, :last_name, :email, :contact_number, :address,

                        'Client', NOW(), 'Active', 'Admin'

                    )

                ");



                // Split the client name into first and last name

                $nameParts = explode(" ", $data['client_name'], 2);

                $firstName = $nameParts[0];

                $lastName = isset($nameParts[1]) ? $nameParts[1] : "";



                $stmt->execute([

                    ":first_name" => $firstName,

                    ":last_name" => $lastName,

                    ":email" => $data['email'],

                    ":contact_number" => $data['contact_number'],

                    ":address" => $data['address'] ?? ''

                ]);



                $userId = $this->conn->lastInsertId();

            }



            // Create booking record

            $stmt = $this->conn->prepare("

                INSERT INTO bookings (

                    user_id, destination, pickup_point, date_of_tour, end_of_tour,

                    number_of_days, number_of_buses, status, payment_status, created_at, booked_at, balance,

                    estimated_pax, notes, created_by

                ) VALUES (

                    :user_id, :destination, :pickup_point, :date_of_tour, :end_of_tour,

                    :number_of_days, :number_of_buses, :status, :payment_status,

                    NOW(), NOW(), :balance, :estimated_pax, :notes, :created_by

                )

            ");



            // Set status based on data or default to confirmed for admin-created bookings

            $status = isset($data['status']) ? $data['status'] : 'Confirmed';

            $paymentStatus = isset($data['payment_status']) ? $data['payment_status'] : 'Unpaid';



            $stmt->execute([

                ":user_id" => $userId,

                ":destination" => $data['destination'],

                ":pickup_point" => $data['pickup_point'],

                ":date_of_tour" => $data['date_of_tour'],

                ":end_of_tour" => $data['end_of_tour'] ?? null,

                ":number_of_days" => $data['number_of_days'],

                ":number_of_buses" => $data['number_of_buses'],

                ":status" => $status,

                ":payment_status" => $paymentStatus,

                ":balance" => $data['total_cost'] ?? 0,

                ":estimated_pax" => $data['estimated_pax'] ?? 0,

                ":notes" => $data['notes'] ?? '',

                ":created_by" => $data['created_by'] ?? 'admin'

            ]);



            $bookingId = $this->conn->lastInsertId();



            // Create booking cost record

            $stmt = $this->conn->prepare("

                INSERT INTO booking_costs (

                    booking_id, gross_price, total_cost, discount, calculated_at

                ) VALUES (

                    :booking_id, :gross_price, :total_cost, :discount, NOW()

                )

            ");



            $totalCost = (float)$data['total_cost'];

            $discount = (float)($data['discount'] ?? 0);



            // Calculate gross price (total before discount)

            $grossPrice = $totalCost;

            if ($discount > 0) {

                $grossPrice = $totalCost / (1 - ($discount / 100));

            }



            $stmt->execute([

                ":booking_id" => $bookingId,

                ":gross_price" => $grossPrice,

                ":total_cost" => $totalCost,

                ":discount" => $discount

            ]);



            // If stops are provided, insert them

            if (isset($data['stops']) && !empty($data['stops'])) {

                $stmt = $this->conn->prepare("

                    INSERT INTO booking_stops (

                        booking_id, location, stop_order

                    ) VALUES (

                        :booking_id, :location, :stop_order

                    )

                ");



                foreach ($data['stops'] as $index => $stopLocation) {

                    $stmt->execute([

                        ":booking_id" => $bookingId,

                        ":location" => $stopLocation,

                        ":stop_order" => $index + 1

                    ]);

                }

            }



            // If initial payment is provided, record it

            if (isset($data['initial_payment']) && !empty($data['initial_payment'])) {

                $amountPaid = (float)$data['initial_payment']['amount_paid'];

                $paymentMethod = $data['initial_payment']['payment_method'];

                $paymentReference = $data['initial_payment']['payment_reference'] ?? 'Admin recorded';



                $stmt = $this->conn->prepare("

                    INSERT INTO payments (

                        booking_id, user_id, amount, payment_method,

                        reference_number, proof_of_payment, status, payment_date, created_at

                    ) VALUES (

                        :booking_id, :user_id, :amount, :payment_method,

                        :reference_number, :proof_of_payment, 'Confirmed', NOW(), NOW()

                    )

                ");



                $stmt->execute([

                    ":booking_id" => $bookingId,

                    ":user_id" => $userId,

                    ":amount" => $amountPaid,

                    ":payment_method" => $paymentMethod,

                    ":reference_number" => $paymentReference,

                    ":proof_of_payment" => 'Admin created'

                ]);



                // Update payment status and balance

                $balance = $totalCost - $amountPaid;

                $newPaymentStatus = 'Unpaid';



                if ($balance <= 0) {

                    $newPaymentStatus = 'Paid';

                    $balance = 0;

                } elseif ($amountPaid > 0) {

                    $newPaymentStatus = 'Partially Paid';

                }



                $stmt = $this->conn->prepare("

                    UPDATE bookings SET

                    payment_status = :payment_status,

                    balance = :balance,

                    amount_paid = :amount_paid

                    WHERE booking_id = :booking_id

                ");



                $stmt->execute([

                    ":payment_status" => $newPaymentStatus,

                    ":balance" => $balance,

                    ":amount_paid" => $amountPaid,

                    ":booking_id" => $bookingId

                ]);

            }



            // If booking is confirmed, set confirmed_at

            if ($status === 'Confirmed') {

                $stmt = $this->conn->prepare("

                    UPDATE bookings SET

                    confirmed_at = NOW()

                    WHERE booking_id = :booking_id

                ");



                $stmt->execute([":booking_id" => $bookingId]);



                // Add notification for client

                $clientMessage = "Your booking to " . $data['destination'] . " has been created and confirmed by admin.";

                $this->clientNotificationModel->addNotification($userId, "booking_created", $clientMessage, $bookingId);

            }



            $this->conn->commit();



            return [

                'success' => true,

                'booking_id' => $bookingId,

                'message' => 'Booking created successfully'

            ];



        } catch (PDOException $e) {

            $this->conn->rollBack();

            return [

                'success' => false,

                'message' => 'Database error: ' . $e->getMessage()

            ];

        } catch (Exception $e) {

            $this->conn->rollBack();

            return [

                'success' => false,

                'message' => 'Error: ' . $e->getMessage()

            ];

        }

    }

    /**

     * Get assigned drivers for a booking

     *

     * @param int $booking_id The booking ID

     * @return array List of drivers assigned to the booking

     */

    public function getAssignedDrivers($booking_id) {

        try {

            $stmt = $this->conn->prepare("

                SELECT d.*, bd.booking_id

                FROM drivers d

                JOIN booking_driver bd ON d.driver_id = bd.driver_id

                WHERE bd.booking_id = :booking_id

            ");

            $stmt->execute([":booking_id" => $booking_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error in getAssignedDrivers: " . $e->getMessage());

            return [];

        }

    }

    /**

     * Get assigned buses for a booking

     *

     * @param int $booking_id The booking ID

     * @return array List of buses assigned to the booking

     */

    public function getAssignedBuses($booking_id) {

        try {

            $stmt = $this->conn->prepare("

                SELECT b.*, bb.booking_id

                FROM buses b

                JOIN booking_buses bb ON b.bus_id = bb.bus_id

                WHERE bb.booking_id = :booking_id

            ");

            $stmt->execute([":booking_id" => $booking_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error in getAssignedBuses: " . $e->getMessage());

            return [];

        }

    }

	/**
	 * Get detailed assignment overview for a booking: buses and drivers per bus
	 */
	public function getBookingAssignments($booking_id) {
		try {
			// Fetch assigned buses
			$buses = $this->getAssignedBuses($booking_id);

			// Fetch mapping from booking_bus_driver if exists
			$busDriverMap = [];
			$stmt = $this->conn->prepare("SELECT bus_id, driver_id FROM booking_bus_driver WHERE booking_id = :booking_id");
			$stmt->execute([":booking_id" => $booking_id]);
			$rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
			foreach ($rows as $row) {
				$busId = (int)$row['bus_id'];
				$driverId = (int)$row['driver_id'];
				if (!isset($busDriverMap[$busId])) $busDriverMap[$busId] = [];
				$busDriverMap[$busId][] = $driverId;
			}

			// Fallback: if no mapping yet, return flat drivers list
			$drivers = $this->getAssignedDrivers($booking_id);

			return [
				'bus_list' => $buses,
				'driver_list' => $drivers,
				'bus_driver_map' => $busDriverMap,
			];
		} catch (PDOException $e) {
			return [
				'bus_list' => [],
				'driver_list' => [],
				'bus_driver_map' => [],
			];
		}
	}

	/**
	 * Update booking assignments: buses and multiple drivers per bus
	 * @param int $booking_id
	 * @param array $bus_ids
	 * @param array $bus_to_driver_ids associative [bus_id => [driver_id, ...]]
	 */
	public function updateBookingAssignments($booking_id, $bus_ids, $bus_to_driver_ids) {
		try {
			$this->conn->beginTransaction();

			// Clean current assignments
			$stmt = $this->conn->prepare("DELETE FROM booking_buses WHERE booking_id = :booking_id");
			$stmt->execute([":booking_id" => $booking_id]);
			$stmt = $this->conn->prepare("DELETE FROM booking_driver WHERE booking_id = :booking_id");
			$stmt->execute([":booking_id" => $booking_id]);
			$stmt = $this->conn->prepare("DELETE FROM booking_bus_driver WHERE booking_id = :booking_id");
			$stmt->execute([":booking_id" => $booking_id]);

			// Insert buses
			if (is_array($bus_ids)) {
				$stmtInsertBus = $this->conn->prepare("INSERT INTO booking_buses (booking_id, bus_id) VALUES (:booking_id, :bus_id)");
				foreach ($bus_ids as $bus_id) {
					$stmtInsertBus->execute([":booking_id" => $booking_id, ":bus_id" => $bus_id]);
				}
			}

			// Build unique driver set and insert to legacy booking_driver
			$uniqueDriverIds = [];
			foreach ($bus_to_driver_ids as $busId => $driverIds) {
				foreach ((array)$driverIds as $driverId) {
					$uniqueDriverIds[$driverId] = true;
				}
			}
			if (!empty($uniqueDriverIds)) {
				$stmtInsertDriver = $this->conn->prepare("INSERT INTO booking_driver (booking_id, driver_id) VALUES (:booking_id, :driver_id)");
				foreach (array_keys($uniqueDriverIds) as $driverId) {
					$stmtInsertDriver->execute([":booking_id" => $booking_id, ":driver_id" => $driverId]);
				}
			}

			// Insert mapping rows
			$stmtInsertMap = $this->conn->prepare("INSERT INTO booking_bus_driver (booking_id, bus_id, driver_id) VALUES (:booking_id, :bus_id, :driver_id)");
			foreach ($bus_to_driver_ids as $busId => $driverIds) {
				foreach ((array)$driverIds as $driverId) {
					$stmtInsertMap->execute([":booking_id" => $booking_id, ":bus_id" => $busId, ":driver_id" => $driverId]);
				}
			}

			$this->conn->commit();
			return ["success" => true];
		} catch (PDOException $e) {
			$this->conn->rollBack();
			return ["success" => false, "message" => "Database error: " . $e->getMessage()];
		}
	}

	public function getAvailableBusesForRange(string $startDate, string $endDate): array {
		try {
			$sql = "
				SELECT b.*
				FROM buses b
				WHERE (LOWER(b.status) = 'active')
				AND b.bus_id NOT IN (
					SELECT bb.bus_id
					FROM booking_buses bb
					JOIN bookings bo ON bb.booking_id = bo.booking_id
					WHERE (bo.status = 'Confirmed' OR bo.status = 'Processing')
					AND (
						(bo.date_of_tour <= :start_date AND bo.end_of_tour >= :start_date)
						OR
						(bo.date_of_tour <= :end_date AND bo.end_of_tour >= :end_date)
						OR
						(bo.date_of_tour >= :start_date AND bo.end_of_tour <= :end_date)
					)
				)
				ORDER BY b.name ASC
			";
			$stmt = $this->conn->prepare($sql);
			$stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
			return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
		} catch (PDOException $e) {
			return [];
		}
	}

	public function getAvailableDriversForRange(string $startDate, string $endDate): array {
		try {
			$sql = "
				SELECT d.*
				FROM drivers d
				WHERE d.status = 'Active' AND d.availability = 'Available'
				AND d.driver_id NOT IN (
					SELECT bd.driver_id
					FROM booking_driver bd
					JOIN bookings bo ON bd.booking_id = bo.booking_id
					WHERE (bo.status = 'Confirmed' OR bo.status = 'Processing')
					AND (
						(bo.date_of_tour <= :start_date AND bo.end_of_tour >= :start_date)
						OR
						(bo.date_of_tour <= :end_date AND bo.end_of_tour >= :end_date)
						OR
						(bo.date_of_tour >= :start_date AND bo.end_of_tour <= :end_date)
					)
				)
				ORDER BY d.full_name ASC
			";
			$stmt = $this->conn->prepare($sql);
			$stmt->execute([':start_date' => $startDate, ':end_date' => $endDate]);
			return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
		} catch (PDOException $e) {
			return [];
		}
	}

	public function getAvailableResourcesForBooking(int $booking_id): array {
		try {
			$booking = $this->getBooking($booking_id);
			if (!$booking) return ['buses' => [], 'drivers' => []];
			$start = $booking['date_of_tour'];
			$end = $booking['end_of_tour'] ?? $booking['date_of_tour'];
			$buses = $this->getAvailableBusesForRange($start, $end);
			$drivers = $this->getAvailableDriversForRange($start, $end);
			return ['buses' => $buses, 'drivers' => $drivers];
		} catch (PDOException $e) {
			return ['buses' => [], 'drivers' => []];
		}
	}

    public function getAuditTrailForRebookingRequest(int $bookingId, int $requestId) {
        try {
            // Get request created_at
            $stmt = $this->conn->prepare("SELECT created_at FROM rebooking_request WHERE request_id = :request_id AND booking_id = :booking_id");
            $stmt->execute([':request_id' => $requestId, ':booking_id' => $bookingId]);
            $requestCreatedAt = $stmt->fetchColumn();
            if (!$requestCreatedAt) {
                return null;
            }

            // First preference: audit at or immediately before the request time
            $stmt = $this->conn->prepare("SELECT * FROM audit_trails WHERE entity_type = 'bookings' AND entity_id = :booking_id AND created_at <= :ts ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([':booking_id' => $bookingId, ':ts' => $requestCreatedAt]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) return $row;

            // Fallback: the first audit after the request time
            $stmt = $this->conn->prepare("SELECT * FROM audit_trails WHERE entity_type = 'bookings' AND entity_id = :booking_id AND created_at >= :ts ORDER BY created_at ASC LIMIT 1");
            $stmt->execute([':booking_id' => $bookingId, ':ts' => $requestCreatedAt]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Error in getAuditTrailForRebookingRequest: ' . $e->getMessage());
            return null;
        }
    }

    // New: total count for rebooking requests (for pagination)
    public function getTotalRebookingRequests($status) {
        $allowed_status = ["Pending", "Confirmed", "Rejected", "All"];
        $status = in_array($status, $allowed_status) ? $status : "";
        $where = ($status == "All" || $status === "") ? "" : " WHERE r.status = :status";

        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM rebooking_request r $where");
            if ($where) {
                $stmt->bindValue(':status', $status);
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['total'] ?? 0);
        } catch (PDOException $e) {
            return 0;
        }
    }



    public function getUnpaidBookingsData($startDate = null, $endDate = null) {

        try {

            $dateFilter = "";

            $params = [];



            if ($startDate && $endDate) {

                $dateFilter = "AND b.date_of_tour BETWEEN :start_date AND :end_date";

                $params[':start_date'] = $startDate;

                $params[':end_date'] = $endDate;

            }



            $query = "

                SELECT

                    CASE

                        WHEN b.payment_status = 'Unpaid' THEN 'Unpaid'

                        WHEN b.payment_status = 'Partially Paid' THEN 'Partially Paid'

                        ELSE 'Paid'

                    END as payment_status,

                    COUNT(*) as count,

                    SUM(c.total_cost) as total_amount

                FROM bookings b

                JOIN booking_costs c ON b.booking_id = c.booking_id

                WHERE b.status IN ('Confirmed', 'Processing', 'Completed')

                $dateFilter

                GROUP BY

                    CASE

                        WHEN b.payment_status = 'Unpaid' THEN 'Unpaid'

                        WHEN b.payment_status = 'Partially Paid' THEN 'Partially Paid'

                        ELSE 'Paid'

                    END

                ORDER BY

                    CASE

                        WHEN b.payment_status = 'Unpaid' THEN 1

                        WHEN b.payment_status = 'Partially Paid' THEN 2

                        ELSE 3

                    END

            ";



            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



            $labels = [];

            $counts = [];

            $amounts = [];



            foreach ($results as $row) {

                $labels[] = $row['payment_status'];

                $counts[] = (int)$row['count'];

                $amounts[] = (float)$row['total_amount'];

            }



            return [

                'labels' => $labels,

                'counts' => $counts,

                'amounts' => $amounts

            ];



        } catch (PDOException $e) {

            error_log("Error in getUnpaidBookingsData: " . $e->getMessage());

            return "Database error: " . $e->getMessage();

        }

    }



    public function getPeakBookingPeriodsData($startDate = null, $endDate = null) {

        try {

            $dateFilter = "";

            $params = [];



            if ($startDate && $endDate) {

                $dateFilter = "AND date_of_tour BETWEEN :start_date AND :end_date";

                $params[':start_date'] = $startDate;

                $params[':end_date'] = $endDate;

            }



            // Get peak booking periods by day of week

            $query = "

                SELECT

                    CASE DAYOFWEEK(date_of_tour)

                        WHEN 1 THEN 'Sunday'

                        WHEN 2 THEN 'Monday'

                        WHEN 3 THEN 'Tuesday'

                        WHEN 4 THEN 'Wednesday'

                        WHEN 5 THEN 'Thursday'

                        WHEN 6 THEN 'Friday'

                        WHEN 7 THEN 'Saturday'

                    END as day_of_week,

                    COUNT(*) as booking_count

                FROM bookings

                WHERE status IN ('Confirmed', 'Processing', 'Completed')

                $dateFilter

                GROUP BY DAYOFWEEK(date_of_tour)

                ORDER BY booking_count DESC

                LIMIT 7

            ";



            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



            $labels = [];

            $counts = [];



            foreach ($results as $row) {

                $labels[] = $row['day_of_week'];

                $counts[] = (int)$row['booking_count'];

            }



            return [

                'labels' => $labels,

                'counts' => $counts

            ];



        } catch (PDOException $e) {

            error_log("Error in getPeakBookingPeriodsData: " . $e->getMessage());

            return "Database error: " . $e->getMessage();

        }

    }



    public function getTotalIncomeData($startDate = null, $endDate = null) {

        try {

            $dateFilter = "";

            $params = [];



            if ($startDate && $endDate) {

                $dateFilter = "AND p.payment_date BETWEEN :start_date AND :end_date";

                $params[':start_date'] = $startDate;

                $params[':end_date'] = $endDate;

            }



            // Get monthly income trends

            $query = "

                SELECT

                    DATE_FORMAT(p.payment_date, '%Y-%m') as month,

                    SUM(p.amount) as total_income

                FROM payments p

                WHERE p.status = 'Confirmed' AND p.is_canceled = 0

                $dateFilter

                GROUP BY DATE_FORMAT(p.payment_date, '%Y-%m')

                ORDER BY month

                LIMIT 12

            ";



            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



            $labels = [];

            $amounts = [];



            foreach ($results as $row) {

                $labels[] = date('M Y', strtotime($row['month'] . '-01'));

                $amounts[] = (float)$row['total_income'];

            }



            return [

                'labels' => $labels,

                'amounts' => $amounts

            ];



        } catch (PDOException $e) {

            error_log("Error in getTotalIncomeData: " . $e->getMessage());

            return "Database error: " . $e->getMessage();

        }

    }



    public function getOutstandingBalancesData($startDate = null, $endDate = null) {

        try {

            $dateFilter = "";

            $params = [];



            if ($startDate && $endDate) {

                $dateFilter = "AND b.date_of_tour BETWEEN :start_date AND :end_date";

                $params[':start_date'] = $startDate;

                $params[':end_date'] = $endDate;

            }



            // Get outstanding balances by month

            $query = "

                SELECT

                    DATE_FORMAT(b.date_of_tour, '%Y-%m') as month,

                    SUM(c.total_cost - COALESCE(paid_amounts.total_paid, 0)) as outstanding_amount

                FROM bookings b

                JOIN booking_costs c ON b.booking_id = c.booking_id

                LEFT JOIN (

                    SELECT

                        booking_id,

                        SUM(amount) as total_paid

                    FROM payments

                    WHERE status = 'Confirmed' AND is_canceled = 0

                    GROUP BY booking_id

                ) paid_amounts ON b.booking_id = paid_amounts.booking_id

                WHERE b.status IN ('Confirmed', 'Processing', 'Completed')

                AND (c.total_cost - COALESCE(paid_amounts.total_paid, 0)) > 0

                $dateFilter

                GROUP BY DATE_FORMAT(b.date_of_tour, '%Y-%m')

                ORDER BY month

                LIMIT 12

            ";



            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



            $labels = [];

            $amounts = [];



            foreach ($results as $row) {

                $labels[] = date('M Y', strtotime($row['month'] . '-01'));

                $amounts[] = (float)$row['outstanding_amount'];

            }



            return [

                'labels' => $labels,

                'amounts' => $amounts

            ];



        } catch (PDOException $e) {

            error_log("Error in getOutstandingBalancesData: " . $e->getMessage());

            return "Database error: " . $e->getMessage();

        }

    }



    public function getTopPayingClientsData($startDate = null, $endDate = null) {

        try {

            $dateFilter = "";

            $params = [];



            if ($startDate && $endDate) {

                $dateFilter = "AND p.payment_date BETWEEN :start_date AND :end_date";

                $params[':start_date'] = $startDate;

                $params[':end_date'] = $endDate;

            }



            // Get top paying clients

            $query = "

                SELECT

                    CONCAT(u.first_name, ' ', u.last_name) as client_name,

                    SUM(p.amount) as total_paid

                FROM payments p

                JOIN bookings b ON p.booking_id = b.booking_id

                JOIN users u ON b.user_id = u.user_id

                WHERE p.status = 'Confirmed' AND p.is_canceled = 0

                $dateFilter

                GROUP BY u.user_id, u.first_name, u.last_name

                ORDER BY total_paid DESC

                LIMIT 10

            ";



            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);



            $labels = [];

            $amounts = [];



            foreach ($results as $row) {

                $labels[] = $row['client_name'];

                $amounts[] = (float)$row['total_paid'];

            }



            return [

                'labels' => $labels,

                'amounts' => $amounts

            ];



        } catch (PDOException $e) {

            error_log("Error in getTopPayingClientsData: " . $e->getMessage());

            return "Database error: " . $e->getMessage();

        }

    }



    public function getDiscountsGivenData($startDate = null, $endDate = null) {

        try {

            $dateFilter = "";

            $params = [];



            if ($startDate && $endDate) {

                $dateFilter = "AND b.date_of_tour BETWEEN :start_date AND :end_date";

                $params[':start_date'] = $startDate;

                $params[':end_date'] = $endDate;

            }



            // Get total discounts given within the date range (sum peso value)
            $query = "

                SELECT

                    SUM(
                        COALESCE(
                            c.discount_amount,
                            CASE
                                WHEN c.discount_type = 'percentage' THEN ROUND((c.total_cost / NULLIF(100 - c.discount, 0)) * c.discount, 2) / 100
                                WHEN c.discount_type = 'flat' THEN c.discount
                                ELSE 0
                            END
                        )
                    ) as total_discount_amount

                FROM bookings b

                JOIN booking_costs c ON b.booking_id = c.booking_id

                WHERE b.status IN ('Confirmed', 'Processing', 'Completed')

                AND (c.discount_amount IS NOT NULL OR c.discount > 0)

                $dateFilter

            ";

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalDiscountAmount = (float)($result['total_discount_amount'] ?? 0);

            // Compute confirmed revenue within the same date range for percentage calculation
            $revenueQuery = "
                SELECT
                    SUM(p.amount) as total_revenue
                FROM payments p
                JOIN bookings b ON p.booking_id = b.booking_id
                WHERE p.is_canceled = 0
                  AND p.status = 'Confirmed'
                  " . ($startDate && $endDate ? " AND b.date_of_tour BETWEEN :start_date AND :end_date" : "") . "
            ";

            $revenueStmt = $this->conn->prepare($revenueQuery);
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $revenueStmt->bindValue($key, $value);
                }
            }
            $revenueStmt->execute();
            $totalRevenue = (float)($revenueStmt->fetchColumn() ?? 0);

            $discountsAsPercentOfRevenue = $totalRevenue > 0
                ? ($totalDiscountAmount / $totalRevenue) * 100
                : 0.0;

            return [
                'labels' => ['Total Revenue', 'Total Discount Amount'],
                'amounts' => [$totalRevenue, $totalDiscountAmount]
            ];

        } catch (PDOException $e) {

            error_log("Error in getDiscountsGivenData: " . $e->getMessage());

            return "Database error: " . $e->getMessage();

        }

    }

    public function getCancellationsByReason($startDate = null, $endDate = null) {
        try {
            $params = [];
            $dateFilter = "";
            if ($startDate && $endDate) {
                $dateFilter = "WHERE ct.canceled_at BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }

            // Prefer structured category when present; fall back to free-text reason
            $query = "
                SELECT 
                    TRIM(COALESCE(NULLIF(ct.cancellation_reason_category, ''), ct.reason)) AS reason_label,
                    COUNT(*) AS total
                FROM canceled_trips ct
                $dateFilter
                GROUP BY TRIM(COALESCE(NULLIF(ct.cancellation_reason_category, ''), ct.reason))
                ORDER BY total DESC
            ";

            $stmt = $this->conn->prepare($query);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$rows) {
                return [
                    'labels' => ['No Data'],
                    'counts' => [0]
                ];
            }

            $labels = [];
            $counts = [];
            foreach ($rows as $row) {
                $labels[] = $row['reason_label'] ?: 'Unspecified';
                $counts[] = (int)$row['total'];
            }

            return [
                'labels' => $labels,
                'counts' => $counts
            ];
        } catch (PDOException $e) {
            error_log("Error in getCancellationsByReason: " . $e->getMessage());
            return "Database error: " . $e->getMessage();
        }
    }

    public function getAverageRevenuePerTripData($startDate = null, $endDate = null) {
        try {
            $params = [];
            $dateFilter = "";
            if ($startDate && $endDate) {
                $dateFilter = "WHERE b.date_of_tour BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }

            // Compute average revenue per trip by month based on confirmed, non-canceled payments
            $query = "
                SELECT 
                    DATE_FORMAT(b.date_of_tour, '%Y-%m') AS period,
                    SUM(COALESCE(CASE WHEN p.is_canceled = 0 AND p.status = 'Confirmed' THEN p.amount ELSE 0 END, 0)) 
                        / NULLIF(COUNT(DISTINCT b.booking_id), 0) AS avg_revenue_per_trip
                FROM bookings b
                LEFT JOIN payments p ON p.booking_id = b.booking_id
                $dateFilter
                GROUP BY DATE_FORMAT(b.date_of_tour, '%Y-%m')
                ORDER BY period ASC
            ";

            $stmt = $this->conn->prepare($query);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $labels = [];
            $amounts = [];
            foreach ($rows as $row) {
                $labels[] = date('M Y', strtotime($row['period'] . '-01'));
                $amounts[] = (float)($row['avg_revenue_per_trip'] ?? 0);
            }

            return [
                'labels' => $labels,
                'amounts' => $amounts
            ];
        } catch (PDOException $e) {
            error_log("Error in getAverageRevenuePerTripData: " . $e->getMessage());
            return "Database error: " . $e->getMessage();
        }
    }

    public function getBusAvailabilityData($startDate = null, $endDate = null) {
        try {
            // Total active and maintenance buses (excluding soft-deleted)
            $totalActiveStmt = $this->conn->prepare("SELECT COUNT(*) FROM buses WHERE status = 'Active' AND (deleted_at IS NULL)");
            $totalActiveStmt->execute();
            $totalActive = (int)$totalActiveStmt->fetchColumn();

            $maintenanceStmt = $this->conn->prepare("SELECT COUNT(*) FROM buses WHERE status = 'Maintenance' AND (deleted_at IS NULL)");
            $maintenanceStmt->execute();
            $maintenanceCount = (int)$maintenanceStmt->fetchColumn();

            // Distinct booked buses within range
            $params = [];
            $dateFilter = "";
            if ($startDate && $endDate) {
                $dateFilter = "AND b.date_of_tour BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }
            $bookedQuery = "
                SELECT COUNT(DISTINCT bb.bus_id) AS booked
                FROM booking_buses bb
                JOIN bookings b ON b.booking_id = bb.booking_id
                WHERE b.status IN ('Confirmed','Processing')
                $dateFilter
            ";
            $bookedStmt = $this->conn->prepare($bookedQuery);
            foreach ($params as $k => $v) { $bookedStmt->bindValue($k, $v); }
            $bookedStmt->execute();
            $booked = (int)$bookedStmt->fetchColumn();

            $availableActive = max($totalActive - $booked, 0);

            return [
                'labels' => ['Active Available', 'Booked', 'Maintenance'],
                'counts' => [$availableActive, $booked, $maintenanceCount]
            ];
        } catch (PDOException $e) {
            error_log("Error in getBusAvailabilityData: " . $e->getMessage());
            return "Database error: " . $e->getMessage();
        }
    }

    public function getDriverAssignmentsPerDay($startDate = null, $endDate = null) {
        try {
            $params = [];
            $dateFilter = "";
            if ($startDate && $endDate) {
                $dateFilter = "AND b.date_of_tour BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }
            $query = "
                SELECT b.date_of_tour AS day, COUNT(DISTINCT bbd.driver_id) AS assignments
                FROM booking_bus_driver bbd
                JOIN bookings b ON b.booking_id = bbd.booking_id
                WHERE b.status IN ('Confirmed','Processing','Completed')
                $dateFilter
                GROUP BY b.date_of_tour
                ORDER BY b.date_of_tour ASC
            ";
            $stmt = $this->conn->prepare($query);
            foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $labels = [];
            $counts = [];
            foreach ($rows as $row) {
                $labels[] = $row['day'];
                $counts[] = (int)$row['assignments'];
            }
            return [ 'labels' => $labels, 'counts' => $counts ];
        } catch (PDOException $e) {
            error_log("Error in getDriverAssignmentsPerDay: " . $e->getMessage());
            return "Database error: " . $e->getMessage();
        }
    }

    public function getAverageTripDurationData($startDate = null, $endDate = null) {
        try {
            $params = [];
            $dateFilter = "";
            if ($startDate && $endDate) {
                $dateFilter = "WHERE b.date_of_tour BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }
            $query = "
                SELECT DATE_FORMAT(b.date_of_tour, '%Y-%m') AS period, AVG(b.number_of_days) AS avg_days
                FROM bookings b
                $dateFilter
                GROUP BY DATE_FORMAT(b.date_of_tour, '%Y-%m')
                ORDER BY period ASC
            ";
            $stmt = $this->conn->prepare($query);
            foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $labels = [];
            $amounts = [];
            foreach ($rows as $row) {
                $labels[] = date('M Y', strtotime($row['period'] . '-01'));
                $amounts[] = (float)$row['avg_days'];
            }
            return [ 'labels' => $labels, 'amounts' => $amounts ];
        } catch (PDOException $e) {
            error_log("Error in getAverageTripDurationData: " . $e->getMessage());
            return "Database error: " . $e->getMessage();
        }
    }

    public function getRepeatClientsData($startDate = null, $endDate = null) {
        try {
            $params = [];
            $dateFilter = "";
            if ($startDate && $endDate) {
                $dateFilter = "WHERE b.date_of_tour BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }
            $query = "
                SELECT SUM(CASE WHEN cnt > 1 THEN 1 ELSE 0 END) AS repeat_clients,
                       SUM(CASE WHEN cnt = 1 THEN 1 ELSE 0 END) AS single_clients
                FROM (
                    SELECT b.user_id, COUNT(*) AS cnt
                    FROM bookings b
                    $dateFilter
                    GROUP BY b.user_id
                ) t
            ";
            $stmt = $this->conn->prepare($query);
            foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $repeat = (int)($row['repeat_clients'] ?? 0);
            $single = (int)($row['single_clients'] ?? 0);
            return [ 'labels' => ['Repeat', 'Single'], 'counts' => [$repeat, $single] ];
        } catch (PDOException $e) {
            error_log("Error in getRepeatClientsData: " . $e->getMessage());
            return "Database error: " . $e->getMessage();
        }
    }

    public function getNewClientsData($startDate = null, $endDate = null) {
        try {
            $params = [];
            if (!($startDate && $endDate)) {
                return [ 'count' => 0 ];
            }
            // Users whose first booking falls within the range
            $query = "
                SELECT COUNT(*) AS new_clients
                FROM (
                    SELECT b.user_id, MIN(b.date_of_tour) AS first_booking
                    FROM bookings b
                    GROUP BY b.user_id
                ) t
                WHERE t.first_booking BETWEEN :start_date AND :end_date
            ";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':start_date', $startDate);
            $stmt->bindValue(':end_date', $endDate);
            $stmt->execute();
            $count = (int)$stmt->fetchColumn();
            return [ 'count' => $count ];
        } catch (PDOException $e) {
            error_log("Error in getNewClientsData: " . $e->getMessage());
            return "Database error: " . $e->getMessage();
        }
    }

    public function getClientSatisfactionSummary($startDate = null, $endDate = null) {
        try {
            $params = [];
            $dateFilter = "";
            if ($startDate && $endDate) {
                $dateFilter = "AND b.date_of_tour BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }
            $query = "
                SELECT t.rating, COUNT(*) AS cnt
                FROM testimonials t
                JOIN bookings b ON b.booking_id = t.booking_id
                WHERE t.is_approved = 1
                $dateFilter
                GROUP BY t.rating
                ORDER BY t.rating
            ";
            $stmt = $this->conn->prepare($query);
            foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $labels = ['1','2','3','4','5'];
            $countsMap = ['1'=>0,'2'=>0,'3'=>0,'4'=>0,'5'=>0];
            foreach ($rows as $row) { $countsMap[(string)$row['rating']] = (int)$row['cnt']; }
            $counts = array_values($countsMap);

            // Average rating
            $avgQuery = "
                SELECT AVG(t.rating) AS avg_rating
                FROM testimonials t
                JOIN bookings b ON b.booking_id = t.booking_id
                WHERE t.is_approved = 1
                $dateFilter
            ";
            $avgStmt = $this->conn->prepare($avgQuery);
            foreach ($params as $k => $v) { $avgStmt->bindValue($k, $v); }
            $avgStmt->execute();
            $avg = (float)($avgStmt->fetchColumn() ?? 0);

            return [ 'labels' => $labels, 'counts' => $counts, 'average' => round($avg, 2) ];
        } catch (PDOException $e) {
            error_log("Error in getClientSatisfactionSummary: " . $e->getMessage());
            return "Database error: " . $e->getMessage();
        }
    }

}

?>