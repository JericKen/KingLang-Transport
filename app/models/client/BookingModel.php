<?php

require_once __DIR__ . "/../../../config/database.php";



class Booking {

    public $conn;

    

    public function __construct() {

        global $pdo;

        $this->conn = $pdo;

    }



    public function getDieselPrice() {

        try {

            // First check if the price is in settings

            require_once __DIR__ . "/../admin/Settings.php";

            $settings = new Settings();

            $diesel_price = $settings->getSetting('diesel_price');

            

            if ($diesel_price !== null) {

                return (float)$diesel_price;

            }

            

            // Fall back to the diesel_per_liter table if setting not found

            $stmt = $this->conn->prepare("SELECT price FROM diesel_per_liter ORDER BY date DESC LIMIT 1");

            $stmt->execute();

            $diesel_price = $stmt->fetchColumn() ?? 0;

            return $diesel_price;

        } catch (PDOException $e) {

            return "Database error: $e";

        }

    }



    public function requestBooking(

        $date_of_tour, $destination, $pickup_point, $number_of_days, $number_of_buses, $user_id, $stops, $total_cost, $balance, $trip_distances, $addresses, 

        $base_cost = null, $diesel_cost = null, $base_rate = null, $diesel_price = null, $total_distance = null, $pickup_time = null

    ) {

        // if ($is_rebooking) return;

        

        $days = $number_of_days - 1;

        $end_of_tour = date("Y-m-d", strtotime($date_of_tour . " + $days days"));



        try {

            $available_buses = $this->findAvailableBuses($date_of_tour, $end_of_tour, $number_of_buses);



            if (!$available_buses) {

                return ["success" => false, "message" => "Not enough buses available."];

            }

            

            // Check for driver availability - we need one driver per bus

            $available_drivers = $this->findAvailableDrivers($date_of_tour, $end_of_tour, $number_of_buses);

            

            if (!$available_drivers || count($available_drivers) < $number_of_buses) {

                return ["success" => false, "message" => "Not enough drivers available for the selected dates."];

            }



            // if ($is_rebooking && $this->bookingIsNotConfirmed($rebooking_id)) {

            //     $this->updateBooking($rebooking_id, $date_of_tour, $destination, $pickup_point, $number_of_days, $number_of_buses, $user_id, $stops, $total_cost, $balance, $trip_distances, $addresses, $base_cost, $diesel_cost, $base_rate, $diesel_price, $total_distance, $pickup_time);

            //     return ["success" => true, "message" => "Booking updated successfully!", "booking_id" => $rebooking_id];

            // }



            $stmt = $this->conn->prepare("INSERT INTO bookings (date_of_tour, end_of_tour, destination, pickup_point, pickup_time, number_of_days, number_of_buses, user_id, balance) VALUES (:date_of_tour, :end_of_tour, :destination, :pickup_point, :pickup_time, :number_of_days, :number_of_buses, :user_id, :balance)");

            $stmt->execute([

                ":date_of_tour" => $date_of_tour,

                ":end_of_tour" => $end_of_tour,

                ":destination" => $destination,

                ":pickup_point" => $pickup_point,

                ":pickup_time" => $pickup_time,

                ":number_of_days" => $number_of_days,       

                ":number_of_buses" => $number_of_buses,

                ":user_id" => $user_id,

                ":balance" => $balance

            ]);



            $booking_id = $this->conn->lastInsertID(); // get the added booking id to insert it in booking buses table



            $stmt = $this->conn->prepare("INSERT INTO booking_costs (booking_id, base_rate, base_cost, diesel_price, diesel_cost, total_cost, total_distance) VALUES (:booking_id, :base_rate, :base_cost, :diesel_price, :diesel_cost, :total_cost, :total_distance)");

            $stmt->execute([

                ":booking_id" => $booking_id,

                ":base_rate" => $base_rate,

                ":base_cost" => $base_cost,

                ":diesel_price" => $diesel_price,

                ":diesel_cost" => $diesel_cost,

                ":total_cost" => $total_cost,

                ":total_distance" => $total_distance,

            ]);

            

            foreach ($available_buses as $bus_id) {

                $stmt = $this->conn->prepare("INSERT INTO booking_buses (booking_id, bus_id) VALUES (:booking_id, :bus_id)");

                $stmt->execute([":booking_id" => $booking_id, ":bus_id" => $bus_id]);

            }

            

            // Assign drivers to the booking

            foreach ($available_drivers as $index => $driver_id) {

                if ($index >= $number_of_buses) break; // Only assign as many drivers as buses

                $stmt = $this->conn->prepare("INSERT INTO booking_driver (booking_id, driver_id) VALUES (:booking_id, :driver_id)");

                $stmt->execute([":booking_id" => $booking_id, ":driver_id" => $driver_id]);

            }



            // insert stops into booking_stops

            foreach ($stops as $index => $stop) {            

                $stmt = $this->conn->prepare("INSERT INTO booking_stops (booking_id, location, stop_order) VALUES (:booking_id, :location, :stop_order)");

                $stmt->execute([

                    ":booking_id" => $booking_id,

                    ":location" => $stop,

                    ":stop_order" => $index + 1

                ]);

            }



            foreach ($trip_distances["rows"] as $i => $row) {

                $distance_value = $row["elements"][$i]["distance"]["value"] ?? 0; // in km

                $origin = $addresses[$i];

                $destination = $addresses[$i + 1] ?? $addresses[0]; // round trip fallback



                $stmt = $this->conn->prepare("INSERT INTO trip_distances (origin, destination, distance, booking_id) VALUES (:origin, :destination, :distance, :booking_id)");

                $stmt->execute([

                    ":origin" => $origin, 

                    ":destination" => $destination, 

                    ":distance" => $distance_value,     

                    ":booking_id" => $booking_id

                ]);

            }



            return ["success" => true, "message" => "Booking request submitted successfully!", "booking_id" => $booking_id];

        } catch (PDOException $e) {

            return ["success" => false, "message" => "Database error: " . $e->getMessage()];

        }

    }



    public function getBooking($booking_id, $user_id) {

        try {

            $stmt = $this->conn->prepare("

                SELECT b.*, CONCAT(u.first_name, ' ', u.last_name) AS client_name, u.contact_number, u.email, u.company_name, c.*

                FROM bookings b

                JOIN users u ON b.user_id = u.user_id

                JOIN booking_costs c ON b.booking_id = c.booking_id

                WHERE b.booking_id = :booking_id AND b.user_id = :user_id

            ");



            $stmt->execute([

                ":booking_id" => $booking_id,

                ":user_id" => $user_id

            ]);

            $stops = $this->getBookingStops($booking_id);

            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            $booking["stops"] = $stops;

            return $booking;

        } catch (PDOException $e) {

            return "Database error";

        }

    }



    public function getBookingStops($booking_id) {

        try {

            $stmt = $this->conn->prepare("SELECT * FROM booking_stops WHERE booking_id = :booking_id ORDER BY stop_order");

            $stmt->execute([":booking_id" => $booking_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];

        } catch (PDOException $e) {

            return "Database error.";

        }

    }



    public function getTripDistances($booking_id) {

        try {

            $stmt = $this->conn->prepare("SELECT * FROM trip_distances WHERE booking_id = :booking_id");

            $stmt->execute([":booking_id" => $booking_id]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            return "Database error";

        }

    }



    public function requestRebooking($booking_id, $user_id) {

        try {

            // Enforce max rebookings per client setting

            require_once __DIR__ . "/../admin/Settings.php";

            $settings = new Settings();

            $maxRebooks = (int)($settings->getSetting('max_rebookings_per_client') ?? 0);

            if ($maxRebooks > 0) {

                $stmt = $this->conn->prepare("SELECT COUNT(*) FROM rebooking_request WHERE user_id = :user_id");

                $stmt->execute([':user_id' => $user_id]);

                $count = (int)$stmt->fetchColumn();

                if ($count >= $maxRebooks) {

                    return ["success" => false, "message" => "You have reached the maximum number of rebooking requests allowed."];

                }

            }

            

            $stmt = $this->conn->prepare("UPDATE bookings SET status = 'Rebooking' WHERE booking_id = :booking_id");

            $stmt->execute([":booking_id" => $booking_id]);

            

            $stmt = $this->conn->prepare("INSERT INTO rebooking_request (booking_id, user_id) VALUES (:booking_id, :user_id)");

            $stmt->execute([":booking_id" => $booking_id, ":user_id" => $user_id]);

            return ["success" => true, "message" => "Rebooking request submitted successfully."];

        } catch (PDOException $e) {

            return "Databse error";

        }   

    }



    // public function bookingExistsInReschedRequests($booking_id) {

    //     try {

    //         $stmt = $this->conn->prepare("SELECT * FROM reschedule_requests WHERE booking_id = :booking_id AND status != 'Confirmed' ORDER BY booking_id DESC");

    //         $stmt->execute([":booking_id" => $booking_id]);

    //         $resched_request = $stmt->fetch(PDO::FETCH_ASSOC);

    //         if ($resched_request) {

    //             return true;

    //         } else {

    //             return false;

    //         }

    //     } catch (PDOException $e) {

    //         return "Database error: $e";

    //     }

    // }



    public function bookingIsNotConfirmed($booking_id) { 

        try {

            $stmt = $this->conn->prepare("SELECT * FROM bookings WHERE booking_id = :booking_id");

            $stmt->execute([":booking_id" => $booking_id]);

            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($booking["status"] === "Confirmed") {

                return false;

            } else {

                return true;

            }

        } catch (PDOException $e) {

            return "Database error: $e";

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

                            OR

                            (bo.date_of_tour <= :end_of_tour AND bo.end_of_tour >= :end_of_tour)

                            OR

                            (bo.date_of_tour >= :date_of_tour AND bo.end_of_tour <= :end_of_tour)

                        )

                )

                LIMIT :number_of_buses;

            ");

            $stmt->bindParam(":date_of_tour", $date_of_tour);

            $stmt->bindParam(":end_of_tour", $end_of_tour);

            $stmt->bindParam(":number_of_buses", $number_of_buses, PDO::PARAM_INT);

            $stmt->execute();



            return $stmt->fetchAll(PDO::FETCH_COLUMN);       

        } catch (PDOException $e) {

            return "Database error: $e";

        }

    }



    /**

     * Find available drivers for a given date range

     * 

     * @param string $date_of_tour Start date of the tour

     * @param string $end_of_tour End date of the tour

     * @param int $number_of_drivers Number of drivers needed (typically same as number of buses)

     * @return array|string Array of driver IDs or error message

     */

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

                            OR

                            (bo.date_of_tour <= :end_of_tour AND bo.end_of_tour >= :end_of_tour)

                            OR

                            (bo.date_of_tour >= :date_of_tour AND bo.end_of_tour <= :end_of_tour)

                        )

                )

                LIMIT :number_of_drivers;

            ");

            $stmt->bindParam(":date_of_tour", $date_of_tour);

            $stmt->bindParam(":end_of_tour", $end_of_tour);

            $stmt->bindParam(":number_of_drivers", $number_of_drivers, PDO::PARAM_INT);

            $stmt->execute();



            return $stmt->fetchAll(PDO::FETCH_COLUMN);       

        } catch (PDOException $e) {

            return "Database error: $e";

        }

    }



    public function getAllBookings($user_id, $status, $column, $order, $page = 1, $limit = 10, $search = "", $date_filter = null, $balance_filter = null) {

        $allowed_status = ["pending", "confirmed", "canceled", "rejected", "completed", "processing", "rebooking", "all"];

        $status = in_array($status, $allowed_status) ? $status : "all";

        

        // Build the status condition based on filter combinations

        $status_condition = "";

        

        // Special handling for balance_filter=unpaid (only confirmed or processing)

        if ($balance_filter === "unpaid") {

            if ($status === "all") {

                // If status is "all" and we're filtering for unpaid, only include confirmed or processing

                $status_condition = " AND (b.status = 'Confirmed' OR b.status = 'Processing')";

            } else {

                // If a specific status is selected with unpaid filter, use that status

                $status_condition = " AND b.status = '".ucfirst($status)."'";

            }

        } 

        // Special handling for date_filter=past (only confirmed or completed)

        else if ($date_filter === "past") {

            if ($status === "all") {

                // For past bookings, include completed, confirmed, and processing instead of only completed bookings

                $status_condition = " AND (b.status = 'Completed' OR b.status = 'Confirmed' OR b.status = 'Processing')";

            } else {

                // If a specific status is selected with past filter, use that status

                $status_condition = " AND b.status = '".ucfirst($status)."'";

            }

        }

        // Special handling for date_filter=upcoming (only confirmed)

        else if ($date_filter === "upcoming") {

            // For upcoming, we always want confirmed bookings regardless of status filter

            $status_condition = " AND b.status = 'Confirmed'";

        }

        // Special handling for canceled filter

        else if ($status === "canceled") {

            // Always show canceled bookings when canceled filter is selected

            $status_condition = " AND b.status = 'Canceled'";

        }

        // Special handling for confirmed filter

        else if ($status === "confirmed") {

            $status_condition = " AND b.status IN ('Confirmed', 'Processing')";

        }

        // Default status handling

        else if ($status !== "all") {

            // Simple status filter if no special cases apply

            $status_condition = " AND b.status = '".ucfirst($status)."'";

        }



        $allowed_columns = ["destination", "date_of_tour", "end_of_tour", "number_of_days", "number_of_buses", "total_cost", "balance", "status", "payment_status", "booking_id"];

        $column = in_array($column, $allowed_columns) ? $column : "date_of_tour";

        $order = $order === "asc" ? "ASC" : "DESC";

        

        // Add search condition if search term provided

        $search_condition = "";

        if (!empty($search)) {

            $search = '%' . $search . '%';

            $search_condition = " AND (b.destination LIKE :search OR b.pickup_point LIKE :search)";

        }

        

        // Add date filter condition if provided

        $date_condition = "";

        if ($date_filter === "upcoming") {

            $date_condition = " AND b.date_of_tour >= CURDATE()";

        } else if ($date_filter === "past") {

            // A booking is considered past if:

            // 1. It has already ended (end_of_tour < today), OR

            // 2. It started in the past and should already be over based on number_of_days

            $date_condition = " AND ((b.end_of_tour < CURDATE()) OR (b.date_of_tour < CURDATE() AND DATEDIFF(CURDATE(), b.date_of_tour) >= b.number_of_days))";

        }

        

        // Add balance filter condition if provided

        $balance_condition = "";

        if ($balance_filter === "unpaid") {

            $balance_condition = " AND b.balance > 0";

        }

        

        // Calculate offset for pagination

        $offset = ($page - 1) * $limit;

        

        try {

            // Get total count for pagination

            $countSql = "

                SELECT COUNT(*) FROM bookings b

                WHERE b.user_id = :user_id

                $status_condition $search_condition $date_condition $balance_condition

            ";

            

            $countStmt = $this->conn->prepare($countSql);

            $countStmt->bindParam(":user_id", $user_id);

            

            if (!empty($search)) {

                $countStmt->bindParam(":search", $search);

            }

            

            $countStmt->execute();

            $total_records = $countStmt->fetchColumn();

            

            // Get paginated results

            $sql = "

                SELECT b.booking_id, b.date_of_tour, b.end_of_tour, b.destination, b.pickup_point, 

                       b.number_of_days, b.number_of_buses, b.user_id, b.balance, b.status, 

                       b.payment_status, 

                       c.base_cost, c.diesel_cost, c.total_cost, c.base_rate, c.diesel_price, c.total_distance, 

                       u.first_name, u.last_name, u.contact_number

                FROM bookings b

                LEFT JOIN booking_costs c ON b.booking_id = c.booking_id

                LEFT JOIN users u ON b.user_id = u.user_id

                WHERE b.user_id = :user_id

                $status_condition $search_condition $date_condition $balance_condition

                ORDER BY $column $order

                LIMIT :limit OFFSET :offset

            ";

            

            // Debug log - write query to error log for troubleshooting

            

            if ($date_filter === "past") {

                error_log("PAST FILTER QUERY: " . $sql);

                error_log("Status condition: " . $status_condition);

                error_log("Date condition: " . $date_condition);

                error_log("Filter parameters: status=" . $status . ", date_filter=" . $date_filter . ", balance_filter=" . $balance_filter . ", search=" . $search);

            }



            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(":user_id", $user_id);

            

            if (!empty($search)) {

                $stmt->bindParam(":search", $search);

            }

            

            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);

            $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);

            $stmt->execute();



            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            

            // Debug log - log the results for past filter

            if ($date_filter === "past") {

                error_log("PAST FILTER RESULTS COUNT: " . count($bookings));

                foreach ($bookings as $index => $booking) {

                    error_log("Booking #$index - ID: {$booking['booking_id']}, Status: {$booking['status']}, Date: {$booking['date_of_tour']}, End: {$booking['end_of_tour']}");

                }

            }



            // Calculate total pages

            $total_pages = ceil($total_records / $limit);

            

            return [

                "bookings" => $bookings ?: [],

                "total_records" => $total_records,

                "total_pages" => $total_pages,

                "current_page" => $page

            ];

        } catch (PDOException $e) {

            return "Database error: " . $e->getMessage();

        }

    }

    

    public function getBookingsCount($user_id, $status = "all") {

        $allowed_status = ["pending", "confirmed", "canceled", "rejected", "completed", "processing", "rebooking", "all"];

        $status = in_array($status, $allowed_status) ? $status : "all";

        $status_condition = $status === "all" ? "" : " AND status = '".ucfirst($status)."'";

        

        try {

            $sql = "

                SELECT COUNT(*) 

                FROM bookings 

                WHERE user_id = :user_id

                $status_condition

            ";

            

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(":user_id", $user_id);

            $stmt->execute();

            

            return (int) $stmt->fetchColumn();

        } catch (PDOException $e) {

            return 0;

        }

    }

    

    public function getUpcomingToursCount($user_id) {

        try {

            $sql = "

                SELECT COUNT(*) 

                FROM bookings 

                WHERE user_id = :user_id 

                  AND date_of_tour >= CURDATE()

                  AND status = 'Confirmed'

            ";

            

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(":user_id", $user_id);

            $stmt->execute();

            

            return (int) $stmt->fetchColumn();

        } catch (PDOException $e) {

            return 0;

        }

    }

    

    public function getBookingsForCalendar($user_id, $start, $end) {

        try {

            $sql = "

                SELECT b.booking_id, b.date_of_tour, b.end_of_tour, b.destination, 

                       b.status, c.total_cost, b.balance

                FROM bookings b

                LEFT JOIN booking_costs c ON b.booking_id = c.booking_id

                WHERE b.user_id = :user_id 

                  AND ((b.date_of_tour BETWEEN :start_date AND :end_date) 

                       OR (b.end_of_tour BETWEEN :start_date AND :end_date)

                       OR (b.date_of_tour <= :start_date AND b.end_of_tour >= :end_date))

            ";

            

            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(":user_id", $user_id);

            $stmt->bindParam(":start_date", $start);

            $stmt->bindParam(":end_date", $end);

            $stmt->execute();

            

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            return [];

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



    public function isClientPaid($booking_id) {

        try {

            $stmt = $this->conn->prepare("SELECT payment_status FROM bookings WHERE booking_id = :booking_id");

            $stmt->execute([":booking_id" => $booking_id]);

            $payment_status = $stmt->fetchColumn();

            return $payment_status === "Partially Paid" || $payment_status === "Paid";

        } catch (PDOException $e) {

            return ["success" => false, "message" => "Database error: " . $e->getMessage()];

        }

    }



    public function cancelPayment($booking_id, $user_id) {

        try {

            $stmt = $this->conn->prepare("UPDATE payments SET is_canceled = 1 WHERE booking_id = :booking_id AND user_id = :user_id");

            $stmt->execute([":booking_id" => $booking_id, ":user_id" => $user_id]);

            return ["success" => true];

        } catch (PDOException $e) {

            return ["success" => false, "message" => "Database error: " . $e->getMessage()];

        }

    }



    public function getAmountPaid($booking_id, $user_id) {

        try {

            $stmt = $this->conn->prepare("SELECT SUM(amount) AS total_amount FROM payments WHERE status = 'Confirmed' AND booking_id = :booking_id AND user_id = :user_id");

            $stmt->execute([":booking_id" => $booking_id, ":user_id" => $user_id]);

            return (float) $stmt->fetchColumn();

        } catch (PDOException $e) {

            return ["success" => false, "message" => "Database error: " . $e->getMessage()];

        }

    }



    public function cancelBooking($reason, $booking_id, $user_id, $amount_refunded, $reason_category = null) {

        try {

            $stmt = $this->conn->prepare("UPDATE bookings SET status = 'Canceled' WHERE booking_id = :booking_id");

            $stmt->execute([":booking_id" => $booking_id]);



            $stmt = $this->conn->prepare("INSERT INTO canceled_trips (reason, booking_id, user_id, amount_refunded, canceled_by, cancellation_reason_category, custom_reason) VALUES (:reason, :booking_id, :user_id, :amount_refunded, :canceled_by, :cancellation_reason_category, :custom_reason)");

            $stmt->execute([
                ":reason" => $reason, 
                ":booking_id" => $booking_id, 
                ":user_id" => $user_id, 
                ":amount_refunded" => $amount_refunded, 
                ":canceled_by" => "Client",
                ":cancellation_reason_category" => $reason_category,
                ":custom_reason" => ($reason_category === 'other') ? $reason : null
            ]);



            return ["success" => true];

        } catch (PDOException $e) {

            return ["success" => false, "message" => "Database error: " . $e->getMessage()];

        }

    }



    public function updatePastBookings() {

        try {

            $stmt = $this->conn->prepare("UPDATE bookings SET status = 'Completed' WHERE end_of_tour < CURDATE() AND status != 'completed' AND balance = 0");

            $stmt->execute();

            return "Updated successfully!";

        } catch (PDOException $e) {

            return "Database error: $e";

        }

    }



    // payment

    public function isPaymentRequested($booking_id) {

        try {

            $stmt = $this->conn->prepare("SELECT * FROM payments WHERE booking_id = :booking_id AND status = 'Pending'");

            $stmt->execute([":booking_id" => $booking_id]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;

        } catch (PDOException $e) {

            return "Database error: $e";

        }

    }



    public function addPayment($booking_id, $user_id, $amount, $payment_method, $proof_of_payment = null) {

        try {

            if ($this->isPaymentRequested($booking_id)) {

                return ["success" => false, "message" => "Payment already requested for this booking."];

            }

            

            $stmt = $this->conn->prepare("INSERT INTO payments (booking_id, user_id, amount, payment_method, proof_of_payment) VALUES (:booking_id, :user_id, :amount, :payment_method, :proof_of_payment)");

            $stmt->execute([

                ":booking_id" => $booking_id,

                ":user_id" => $user_id,

                ":amount" => $amount,

                ":payment_method" => $payment_method,

                ":proof_of_payment" => $proof_of_payment

            ]);

            

            // Update booking status to Processing without changing payment status or balance

            $stmt = $this->conn->prepare("UPDATE bookings SET status = 'Processing' WHERE booking_id = :booking_id");

            $stmt->execute([":booking_id" => $booking_id]);

            

            return ["success" => true, "message" => "Payment request submitted successfully!"];

        } catch (PDOException $e) {

            return ["success" => false, "message" => "Database error: " . $e->getMessage()];

        }

    }



    public function updateBooking(

        $rebooking_id, $date_of_tour, $destination, $pickup_point, $number_of_days, $number_of_buses, $user_id, $stops, $total_cost, $balance, $trip_distances, $addresses, 

        $base_cost = null, $diesel_cost = null, $base_rate = null, $diesel_price = null, $total_distance = null, $pickup_time = null

    ) {

        $days = $number_of_days - 1;

        $end_of_tour = date("Y-m-d", strtotime($date_of_tour . " + $days days"));



        try {

            $available_buses = $this->findAvailableBuses($date_of_tour, $end_of_tour, $number_of_buses);



            if (!$available_buses) {

                return "Not enough buses available.";

            }

            

            // Check for driver availability

            $available_drivers = $this->findAvailableDrivers($date_of_tour, $end_of_tour, $number_of_buses);

            

            if (!$available_drivers || count($available_drivers) < $number_of_buses) {

                return "Not enough drivers available for the selected dates.";

            }



            // Update the booking

            $stmt = $this->conn->prepare("UPDATE bookings SET date_of_tour = :date_of_tour, end_of_tour = :end_of_tour, destination = :destination, pickup_point = :pickup_point, pickup_time = :pickup_time, number_of_days = :number_of_days, number_of_buses = :number_of_buses, balance = :balance WHERE booking_id = :booking_id AND user_id = :user_id");

            $stmt->execute([

                ":date_of_tour" => $date_of_tour,

                ":end_of_tour" => $end_of_tour,

                ":destination" => $destination,

                ":pickup_point" => $pickup_point,

                ":pickup_time" => $pickup_time,

                ":number_of_days" => $number_of_days,       

                ":number_of_buses" => $number_of_buses,

                ":balance" => $balance,

                ":booking_id" => $rebooking_id,

                ":user_id" => $user_id

            ]);



            // Update the booking costs

            $stmt = $this->conn->prepare("UPDATE booking_costs SET base_rate = :base_rate, base_cost = :base_cost, diesel_price = :diesel_price, diesel_cost = :diesel_cost, total_cost = :total_cost, total_distance = :total_distance WHERE booking_id = :booking_id");

            $stmt->execute([

                ":base_rate" => $base_rate,

                ":base_cost" => $base_cost,

                ":diesel_price" => $diesel_price,

                ":diesel_cost" => $diesel_cost,

                ":total_cost" => $total_cost,

                ":total_distance" => $total_distance,

                ":booking_id" => $rebooking_id

            ]);



            // Delete existing stops

            $stmt = $this->conn->prepare("DELETE FROM booking_stops WHERE booking_id = :booking_id");

            $stmt->execute([":booking_id" => $rebooking_id]);



            // Insert new stops

            $stops = is_array($stops) ? $stops : [];

            foreach ($stops as $index => $stop) {            

                $stmt = $this->conn->prepare("INSERT INTO booking_stops (booking_id, location, stop_order) VALUES (:booking_id, :location, :stop_order)");

                $stmt->execute([

                    ":booking_id" => $rebooking_id,

                    ":location" => $stop["location"],

                    ":stop_order" => $index + 1

                ]);

            }



            // Delete existing trip distances

            $stmt = $this->conn->prepare("DELETE FROM trip_distances WHERE booking_id = :booking_id");

            $stmt->execute([":booking_id" => $rebooking_id]);



            // Insert new trip distances

            foreach ($trip_distances["rows"] as $i => $row) {

                $distance_value = $row["elements"][$i]["distance"]["value"] ?? 0; // in km

                $origin = $addresses[$i];

                $destination = $addresses[$i + 1] ?? $addresses[0]; // round trip fallback



                $stmt = $this->conn->prepare("INSERT INTO trip_distances (origin, destination, distance, booking_id) VALUES (:origin, :destination, :distance, :booking_id)");

                $stmt->execute([

                    ":origin" => $origin, 

                    ":destination" => $destination, 

                    ":distance" => $distance_value,     

                    ":booking_id" => $rebooking_id

                ]);

            }



            // Delete existing booking buses

            $stmt = $this->conn->prepare("DELETE FROM booking_buses WHERE booking_id = :booking_id");

            $stmt->execute([":booking_id" => $rebooking_id]);



            // Insert new booking buses

            foreach ($available_buses as $bus_id) {

                $stmt = $this->conn->prepare("INSERT INTO booking_buses (booking_id, bus_id) VALUES (:booking_id, :bus_id)");

                $stmt->execute([":booking_id" => $rebooking_id, ":bus_id" => $bus_id]);

            }

            

            // Delete existing driver assignments

            $stmt = $this->conn->prepare("DELETE FROM booking_driver WHERE booking_id = :booking_id");

            $stmt->execute([":booking_id" => $rebooking_id]);

            

            // Assign new drivers

            foreach ($available_drivers as $index => $driver_id) {

                if ($index >= $number_of_buses) break; // Only assign as many drivers as buses

                $stmt = $this->conn->prepare("INSERT INTO booking_driver (booking_id, driver_id) VALUES (:booking_id, :driver_id)");

                $stmt->execute([":booking_id" => $rebooking_id, ":driver_id" => $driver_id]);

            }



            return "success";

        } catch (PDOException $e) {

            return "Database error: $e";

        }

    }



    // Add a new method to save terms agreement information

    public function saveTermsAgreement($booking_id, $user_id, $agreed_terms, $user_ip) {

        try {

            $stmt = $this->conn->prepare("INSERT INTO terms_agreements (booking_id, user_id, agreed_terms, user_ip) VALUES (:booking_id, :user_id, :agreed_terms, :user_ip)");

            $stmt->execute([

                ":booking_id" => $booking_id,

                ":user_id" => $user_id,

                ":agreed_terms" => $agreed_terms ? 1 : 0,

                ":user_ip" => $user_ip

            ]);

            return true;

        } catch (PDOException $e) {

            error_log("Error saving terms agreement: " . $e->getMessage());

            return false;

        }

    }



    // Add a method to get terms agreement information for a booking

    public function getTermsAgreement($booking_id) {

        try {

            $stmt = $this->conn->prepare("SELECT * FROM terms_agreements WHERE booking_id = :booking_id");

            $stmt->execute([":booking_id" => $booking_id]);

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error getting terms agreement: " . $e->getMessage());

            return false;

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

}





?>