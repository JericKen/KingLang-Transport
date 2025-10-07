<?php

require_once __DIR__ . "/../../models/admin/BookingManagementModel.php";

require_once __DIR__ . "/../AuditTrailTrait.php";



class BookingManagementController {

    use AuditTrailTrait;

    

    private $bookingModel;



    public function __construct() {

        $this->bookingModel = new BookingManagementModel();

        

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



    // New method for booking stats dashboard

    public function getBookingStats() {

        header("Content-Type: application/json");

        

        $stats = $this->bookingModel->getBookingStats();

        

        if (is_array($stats)) {

            echo json_encode(["success" => true, "stats" => $stats]);

        } else {

            echo json_encode(["success" => false, "message" => $stats]);

        }

    }

    

    // New method for calendar bookings

    public function getCalendarBookings() {

        $data = json_decode(file_get_contents("php://input"), true);

        $start = isset($data["start"]) ? $data["start"] : date('Y-m-01'); // First day of current month

        $end = isset($data["end"]) ? $data["end"] : date('Y-m-t'); // Last day of current month

        

        $bookings = $this->bookingModel->getCalendarBookings($start, $end);

        

        header("Content-Type: application/json");

        

        if (is_array($bookings)) {

            echo json_encode(["success" => true, "bookings" => $bookings]);

        } else {

            echo json_encode(["success" => false, "message" => $bookings]);

        }

    }

    

    // New method for search bookings

    public function searchBookings() {

        $data = json_decode(file_get_contents("php://input"), true);

        $searchTerm = $data["searchTerm"];

        $status = $data["status"];

        $page = isset($data["page"]) ? (int)$data["page"] : 1;

        $limit = isset($data["limit"]) ? (int)$data["limit"] : 10;

        

        $bookings = $this->bookingModel->searchBookings($searchTerm, $status, $page, $limit);

        $total = $this->bookingModel->getTotalSearchResults($searchTerm, $status);

        

        // Fix for pagination bug when total == limit

        // We need to make sure totalPages is at least 1, and correctly calculated

        $totalPages = max(1, ceil((int)$total / (int)$limit));

        

        $response = [

            "success" => true, 

            "bookings" => $bookings,

            "pagination" => [

                "total" => $total,

                "totalPages" => $totalPages,

                "currentPage" => $page,

                "limit" => $limit

            ]

        ];

        

        header("Content-Type: application/json");

        

        if (is_array($bookings)) {

            echo json_encode($response);

        } else {

            echo json_encode(["success" => false, "message" => $bookings]);

        }

    }

    

    // New method for unpaid bookings filter

    public function getUnpaidBookings() {

        $data = json_decode(file_get_contents("php://input"), true);

        $page = isset($data["page"]) ? (int)$data["page"] : 1;

        $limit = isset($data["limit"]) ? (int)$data["limit"] : 10;

        $column = isset($data["column"]) ? $data["column"] : "booking_id";

        $order = isset($data["order"]) ? $data["order"] : "desc";

        

        $bookings = $this->bookingModel->getUnpaidBookings($page, $limit, $column, $order);

        $total = $this->bookingModel->getTotalUnpaidBookings();

        

        // Fix for pagination bug when total == limit

        // We need to make sure totalPages is at least 1, and correctly calculated

        $totalPages = max(1, ceil((int)$total / (int)$limit));

        

        $response = [

            "success" => true, 

            "bookings" => $bookings,

            "pagination" => [

                "total" => $total,

                "totalPages" => $totalPages,

                "currentPage" => $page,

                "limit" => $limit

            ]

        ];

        

        header("Content-Type: application/json");

        

        if (is_array($bookings)) {

            echo json_encode($response);

        } else {

            echo json_encode(["success" => false, "message" => $bookings]);

        }

    }

    

    // New method for partially paid bookings filter

    public function getPartiallyPaidBookings() {

        $data = json_decode(file_get_contents("php://input"), true);

        $page = isset($data["page"]) ? (int)$data["page"] : 1;

        $limit = isset($data["limit"]) ? (int)$data["limit"] : 10;

        $column = isset($data["column"]) ? $data["column"] : "booking_id";

        $order = isset($data["order"]) ? $data["order"] : "desc";

        

        $bookings = $this->bookingModel->getPartiallyPaidBookings($page, $limit, $column, $order);

        $total = $this->bookingModel->getTotalPartiallyPaidBookings();

        

        // Fix for pagination bug when total == limit

        // We need to make sure totalPages is at least 1, and correctly calculated

        $totalPages = max(1, ceil((int)$total / (int)$limit));

        

        $response = [

            "success" => true, 

            "bookings" => $bookings,

            "pagination" => [

                "total" => $total,

                "totalPages" => $totalPages,

                "currentPage" => $page,

                "limit" => $limit

            ]

        ];

        

        header("Content-Type: application/json");

        

        if (is_array($bookings)) {

            echo json_encode($response);

        } else {

            echo json_encode(["success" => false, "message" => $bookings]);

        }

    }

    

    // New method for export bookings

    public function exportBookings() {

        $format = $_GET["format"] ?? "csv";

        $status = $_GET["status"] ?? "All";

        

        $bookings = $this->bookingModel->getAllBookingsForExport($status);

        

        header("Content-Type: application/octet-stream");

        header("Content-Disposition: attachment; filename=bookings_" . strtolower($status) . "_" . date("Y-m-d") . "." . $format);

        header("Pragma: no-cache");

        header("Expires: 0");

        

        if ($format === "csv") {

            $this->exportToCSV($bookings);

        } else {

            // For simplicity, we'll just export CSV for now

            // PDF export would require additional libraries

            $this->exportToCSV($bookings);

        }

        

        exit;

    }

    

    // Helper method for CSV export

    private function exportToCSV($bookings) {

        $output = fopen("php://output", "w");

        

        // Write headers

        fputcsv($output, [

            "ID", "Client Name", "Contact Number", "Email", "Destination", 

            "Date of Tour", "Number of Days", "Number of Buses", "Total Cost",

            "Payment Status", "Status"

        ]);

        

        // Write data

        foreach ($bookings as $booking) {

            fputcsv($output, [

                $booking["booking_id"],

                $booking["client_name"],

                $booking["contact_number"],

                $booking["email"] ?? "N/A",

                $booking["destination"],

                $booking["date_of_tour"],

                $booking["number_of_days"],

                $booking["number_of_buses"],

                $booking["total_cost"],

                $booking["payment_status"],

                $booking["status"]

            ]);

        }

        

        fclose($output);

    }



    public function showBookingDetail() {

        include_once __DIR__ . "/../../views/admin/booking_request.php";

    }



    public function getAllBookings() {

        $data = json_decode(file_get_contents("php://input"), true);

        $status = $data["status"];

        $column = $data["column"];

        $order = $data["order"];

        $page = isset($data["page"]) ? (int)$data["page"] : 1;

        $limit = isset($data["limit"]) ? (int)$data["limit"] : 10;



        $bookings = $this->bookingModel->getAllBookings($status, $column, $order, $page, $limit);

        $total = $this->bookingModel->getTotalBookings($status);

        

        // Fix for pagination bug when total == limit

        // We need to make sure totalPages is at least 1, and correctly calculated

        $totalPages = max(1, ceil((int)$total / (int)$limit));

        

        // Debug info

        // error_log("Debug - Total records: $total, Limit: $limit, Total Pages Calculated: $totalPages");

        

        $response = [

            "success" => true, 

            "bookings" => $bookings,

            "pagination" => [

                "total" => $total,

                "totalPages" => $totalPages,

                "currentPage" => $page,

                "limit" => $limit

            ]

        ];

        

        // error_log("Response: " . json_encode($response));



        header("Content-Type: application/json");



        if (is_array($bookings)) {

            echo json_encode($response);

        } else {

            echo json_encode(["success" => false, "message" => $bookings]);

        }

    }



    public function showBookingTable() {

        require_once __DIR__ . "/../../views/admin/booking_management.php";

    }



    public function confirmBooking() {

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $data = json_decode(file_get_contents("php://input"), true);

            $booking_id = $data["bookingId"];

            $discount = isset($data["discount"]) ? (float)$data["discount"] : null;

            $discountType = isset($data["discountType"]) ? $data["discountType"] : null;

            

            $oldBookingData = $this->getEntityBeforeUpdate('bookings', 'booking_id', $booking_id);

            $oldBookingCostData = $this->getEntityBeforeUpdate('booking_costs', 'booking_id', $booking_id);



            foreach ($oldBookingCostData as $key => $value) {

                if ($key === 'discount' || $key === 'discount_type') $oldBookingData[$key] = $value;

                continue;

            }



            $result = $this->bookingModel->confirmBooking($booking_id, $discount, $discountType);



            $newBookingData = array_merge($oldBookingData ?: [], ['status' => 'Confirmed', 'discount' => $discount, 'discount_type' => $discountType]);

            // Log the confirmation in the audit trail

            $this->logAudit('update', 'bookings', $booking_id, $oldBookingData, $newBookingData, $_SESSION['admin_id']);



            header("Content-Type: application/json");



            echo json_encode([

                "success" => $result === "success",

                "message" => $result === "success" 

                    ? "Booking confirmed successfully." 

                    : $result

            ]);

        }   

    }



    public function rejectBooking() {

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data['bookingId']) || !isset($data['reason'])) {

            echo json_encode(['message' => 'Invalid request data.']);

            return;

        }



        $bookingId = $data['bookingId'];

        $reason = $data['reason'];

        

        // Get booking data before update for audit trail

        $oldBookingData = $this->getEntityBeforeUpdate('bookings', 'booking_id', $bookingId);

        $oldBookingData['rejection_reason'] = "";

        

        try {

            global $pdo;

            $pdo->beginTransaction();

            

            // Update booking status to rejected

            $stmt = $pdo->prepare("

                UPDATE bookings 

                SET status = 'Rejected' 

                WHERE booking_id = ?

            ");

            $stmt->execute([$bookingId]);

            

            // Get user ID for notification

            $stmt = $pdo->prepare("SELECT user_id FROM bookings WHERE booking_id = ?");

            $stmt->execute([$bookingId]);

            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            

            if (!$booking) {

                throw new PDOException("Booking not found.");

            }

            

            // Record rejection reason

            $stmt = $pdo->prepare("

                INSERT INTO rejected_trips (reason, booking_id, user_id)

                VALUES (?, ?, ?)

            ");

            $stmt->execute([$reason, $bookingId, $booking['user_id']]);

            

            // Get the updated booking data

            $newBookingData = array_merge($oldBookingData ?: [], ['status' => 'Rejected', 'rejection_reason' => $reason]);

            

            // Create notification for admin

            $stmt = $pdo->prepare("

                INSERT INTO admin_notifications (type, message, reference_id)

                VALUES ('booking_rejected', 'Booking #" . $bookingId . " has been rejected.', ?)

            ");

            $stmt->execute([$bookingId]);

            

            // Create notification for client

            $stmt = $pdo->prepare("

                INSERT INTO client_notifications (user_id, type, message, reference_id)

                VALUES (?, 'booking_rejected', 'Your booking #" . $bookingId . " has been rejected. Reason: " . $reason . "', ?)

            ");

            $stmt->execute([$booking['user_id'], $bookingId]);

            

            $pdo->commit();

            

            // Log to audit trail

            $this->logAudit(

                'update', 

                'bookings', 

                $bookingId, 

                $oldBookingData, 

                $newBookingData, 

                $_SESSION['admin_id']

            );

            

            echo json_encode(['success' => true, 'message' => 'Booking rejected successfully.']);

            

        } catch (PDOException $e) {

            $pdo->rollBack();

            error_log("Error rejecting booking: " . $e->getMessage());

            echo json_encode(['message' => 'Failed to reject booking. Please try again.']);

        }

    }



    public function showReschedRequestTable() {

        require_once __DIR__ . "/../../views/admin/rebooking_requests.php";

    }



    public function getRebookingRequests() {

        $data = json_decode(file_get_contents("php://input"), true);

        $status = $data["status"];

        $order = $data["order"];

        $column = $data["column"];



        $page = isset($data["page"]) ? (int)$data["page"] : null;

        $limit = isset($data["limit"]) ? (int)$data["limit"] : null;



        // If pagination parameters are provided, use them

        if (!is_null($page) && !is_null($limit) && $page > 0 && $limit > 0) {

            $reschedRequests = $this->bookingModel->getRebookingRequests($status, $column, $order, $page, $limit);

            $total = $this->bookingModel->getTotalRebookingRequests($status);

        } else {

            $reschedRequests = $this->bookingModel->getRebookingRequests($status, $column, $order);

            $total = is_array($reschedRequests) ? count($reschedRequests) : 0;

        }



        header("Content-Type: application/json");



        if (is_array($reschedRequests)) {

            $response = [

                "success" => true,

                "requests" => $reschedRequests

            ];

            if (!is_null($page) && !is_null($limit) && $page > 0 && $limit > 0) {

                $response["pagination"] = [

                    "total" => (int)$total,

                    "totalPages" => max(1, (int)ceil($total / $limit)),

                    "currentPage" => $page,

                    "limit" => $limit

                ];

            }

            echo json_encode($response);

        } else {

            echo json_encode(["success" => false, "message" => $reschedRequests]);

        }

    }



     public function getBookingAuditDetails() {

        $data = json_decode(file_get_contents("php://input"), true);

        $bookingId = isset($data["bookingId"]) ? $data["bookingId"] : null;

        $requestId = isset($data["requestId"]) ? (int)$data["requestId"] : null;

        

        if ($bookingId <= 0) {

            header('Content-Type: application/json');

            echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);

            return;

        }

        

        // Default: latest audit

        $auditDetails = null;

        // If requestId provided, try to get the audit snapshot closest to request creation

        if ($requestId) {

            $auditDetails = $this->bookingModel->getAuditTrailForRebookingRequest($bookingId, $requestId);

        }

        // Fallback to latest if we didn't find a time-sliced audit

        if (!$auditDetails) {

            $auditDetails = $this->bookingModel->getAuditTrailByBookingId($bookingId);

        }

        if ($auditDetails) {

            if (!empty($auditDetails['old_values'])) {

                $auditDetails['old_values'] = json_decode($auditDetails['old_values'], true);

            }

            if (!empty($auditDetails['new_values'])) {

                $auditDetails['new_values'] = json_decode($auditDetails['new_values'], true);

            }

            $auditDetails['created_at_formatted'] = convertToManilaTime($auditDetails['created_at'], 'Y-m-d H:i:s');

        }

        header('Content-Type: application/json');

        echo json_encode($auditDetails ? ['success' => true, 'auditDetails' => $auditDetails] : ['success' => false, 'message' => 'Audit trail not found']);

    }



    public function confirmRebookingRequest() {

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $data = json_decode(file_get_contents("php://input"), true);

            $rebooking_id = $data["bookingId"];

            $discount = isset($data["discount"]) ? (float)$data["discount"] : null;

            $discountType = isset($data["discountType"]) ? $data["discountType"] : null;



            $newBookingData = $this->bookingModel->getAuditTrailByBookingId($rebooking_id);

            $result = $this->bookingModel->confirmRebookingRequest($rebooking_id, $discount, $discountType, json_decode($newBookingData['new_values'], true));

            header("Content-Type: application/json");

            

            echo json_encode([

                "success" => $result["success"],

                "message" => $result["success"] 

                    ? "Booking confirmed successfully." 

                    : $result["message"]

            ]);

        }   

    }



    public function rejectRebooking() {

        header("Content-Type: application/json");



        $data = json_decode(file_get_contents("php://input"), true);

        $reason = $data["reason"];

        $booking_id = (int) $data["bookingId"];

        $user_id = (int) $data["userId"];



        $result = $this->bookingModel->rejectRebooking($reason, $booking_id, $user_id);



        echo json_encode([

            "success" => $result["success"],

            "message" => $result["success"] 

                ? "Booking rejected successfully." 

                : $result["message"]

        ]);

    }



    public function getBooking() {

        header("Content-Type: application/json");



        $data = json_decode(file_get_contents("php://input"), true);



        $booking_id = $data["bookingId"];



        $booking = $this->bookingModel->getBooking($booking_id);

        $stops = $this->bookingModel->getBookingStops($booking_id);

        $distances = $this->bookingModel->getTripDistances($booking_id);

        $diesel = $this->bookingModel->getDieselPrice();



        if ($booking) {

            echo json_encode(["success" => true, "booking" => $booking, "stops" => $stops, "distances" =>  $distances, "diesel" => $diesel]);

        } else {

            echo json_encode(["success" => false, "message" => $booking]);

        }

    }



    // This is the endpoint for the admin booking details modal

    public function getBookingDetails() {

        header("Content-Type: application/json");

        

        $data = json_decode(file_get_contents("php://input"), true);

        $booking_id = isset($data["bookingId"]) ? $data["bookingId"] : null;

        

        if (!$booking_id) {

            echo json_encode([

                "success" => false,

                "message" => "Booking ID is required"

            ]);

            return;

        }

        

        // Get booking details

        $booking = $this->bookingModel->getBooking($booking_id);

        

        // Get stops

        $stops = $this->bookingModel->getBookingStops($booking_id);



        $payments = $this->bookingModel->getPaymentHistory($booking_id);

        

        if ($booking) {

            // Add stops to booking object

            $booking['stops'] = $stops;

            $booking['payments'] = $payments;

            // Include assignment overview

            $booking['assignments'] = $this->bookingModel->getBookingAssignments($booking_id);

            echo json_encode([

                "success" => true,

                "booking" => $booking

            ]);

        } else {

            echo json_encode([

                "success" => false,

                "message" => "Booking not found"

            ]);

        }

    }

    // New endpoint: get buses/drivers and current mapping for a booking
    public function getBookingAssignments() {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);
        $booking_id = isset($data["bookingId"]) ? (int)$data["bookingId"] : 0;
        if ($booking_id <= 0) {
            echo json_encode(["success" => false, "message" => "Invalid booking id"]);
            return;
        }
        $assignments = $this->bookingModel->getBookingAssignments($booking_id);
        echo json_encode(["success" => true, "assignments" => $assignments]);
    }

    // New endpoint: update buses and multi-driver assignments for a booking
    public function updateBookingAssignments() {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"), true);
        $booking_id = isset($data["bookingId"]) ? (int)$data["bookingId"] : 0;

        // Accept both legacy and new payload keys
        $bus_ids = [];
        if (isset($data["busIds"]) && is_array($data["busIds"])) {
            $bus_ids = $data["busIds"]; // legacy key
        } elseif (isset($data["buses"]) && is_array($data["buses"])) {
            $bus_ids = $data["buses"]; // new key
        }

        $bus_to_driver_ids = [];
        if (isset($data["busToDriverIds"]) && is_array($data["busToDriverIds"])) {
            $bus_to_driver_ids = $data["busToDriverIds"]; // legacy key
        } elseif (isset($data["assignments"]) && is_array($data["assignments"])) {
            $bus_to_driver_ids = $data["assignments"]; // new key
        }

        if ($booking_id <= 0) {
            echo json_encode(["success" => false, "message" => "Invalid booking id"]);
            return;
        }

        // Normalize types to integers
        $bus_ids = array_values(array_unique(array_map('intval', $bus_ids)));
        $normalized_map = [];
        foreach ($bus_to_driver_ids as $busId => $driverIds) {
            $busIdInt = (int)$busId;
            $normalized_map[$busIdInt] = array_values(array_unique(array_map('intval', (array)$driverIds)));
        }

        $result = $this->bookingModel->updateBookingAssignments($booking_id, $bus_ids, $normalized_map);
        echo json_encode(["success" => $result["success"], "message" => $result["success"] ? "Assignments updated" : ($result["message"] ?? "Failed to update")]);
    }



    public function printInvoice($booking_id = null) {

        if (!$booking_id) {

            // Redirect to bookings page if no ID provided

            header("Location: /admin/booking-requests");

            exit();

        }

        

        // Get booking details

        $booking = $this->bookingModel->getBooking($booking_id);

        

        if (!$booking) {

            // Booking not found

            header("Location: /admin/booking-requests");

            exit();

        }

        

        // Get booking stops

        $stops = $this->bookingModel->getBookingStops($booking_id);

        

        // Get payment history

        $payments = $this->bookingModel->getPaymentHistory($booking_id);

        

        // Load the invoice template view

        require_once __DIR__ . "/../../views/admin/invoice.php";

    }



    public function printContract($booking_id = null) {

        if (!$booking_id) {

            // Redirect to bookings page if no ID provided

            header("Location: /admin/booking-requests");

            exit();

        }

        

        // Get booking details

        $booking = $this->bookingModel->getBooking($booking_id);

        

        if (!$booking) {

            // Booking not found

            header("Location: /admin/booking-requests");

            exit();

        }

        

        // Get booking stops

        $stops = $this->bookingModel->getBookingStops($booking_id);

        

        // Get assigned drivers and buses

        $drivers = $this->bookingModel->getAssignedDrivers($booking_id);

        $buses = $this->bookingModel->getAssignedBuses($booking_id);

        

        // Load the contract template view

        require_once __DIR__ . "/../../views/admin/contract.php";

    }



    public function cancelBooking() {

        header("Content-Type: application/json");



        $data = json_decode(file_get_contents("php://input"), true);



        $booking_id = $data["bookingId"];

        $user_id = $data["userId"];

        $reason = $data["reason"];

        $amount_paid = 0;



        if ($this->bookingModel->isClientPaid($booking_id)) {

            $amount_paid = $this->bookingModel->getAmountPaid($booking_id, $user_id);

            $this->bookingModel->cancelPayment($booking_id, $user_id);

        }



        $amount_refunded = $amount_paid * 0.80;



        $result = $this->bookingModel->cancelBooking($reason, $booking_id, $user_id, $amount_refunded);



        echo json_encode([

            "success" => $result["success"], 

            "message" => $result["success"] 

                ? "Booking Canceled Successfully." 

                : $result["message"]

        ]);



    }



    public function summaryMetrics() {

        header("Content-Type: application/json");



        // Get data from POST request

        $jsonData = file_get_contents('php://input');

        $data = json_decode($jsonData, true);

        

        $startDate = isset($data['start_date']) ? $data['start_date'] : null;

        $endDate = isset($data['end_date']) ? $data['end_date'] : null;



        $summary_metrics = $this->bookingModel->summaryMetrics($startDate, $endDate);



        echo json_encode($summary_metrics);

    }



    public function paymentMethodChart() {

        header("Content-Type: application/json");



        try {

            // Get data from POST request

            $jsonData = file_get_contents('php://input');

            $data = json_decode($jsonData, true);

            

            $startDate = isset($data['start_date']) ? $data['start_date'] : null;

            $endDate = isset($data['end_date']) ? $data['end_date'] : null;

            

            $payment_methods = $this->bookingModel->paymentMethodChart($startDate, $endDate);

            

            // Check if we received an error message instead of the expected data

            if (is_string($payment_methods) && strpos($payment_methods, "Database error") !== false) {

                echo json_encode(["error" => $payment_methods]);

                return;

            }

            

            echo json_encode($payment_methods);

        } catch (Exception $e) {

            echo json_encode(["error" => "Failed to retrieve payment method data: " . $e->getMessage()]);

        }

    }



    public function monthlyBookingTrends() {

        header("Content-Type: application/json");

        

        try {

            // Get data from POST request

            $jsonData = file_get_contents('php://input');

            $data = json_decode($jsonData, true);

            

            $startDate = isset($data['start_date']) ? $data['start_date'] : null;

            $endDate = isset($data['end_date']) ? $data['end_date'] : null;

            

            $trends = $this->bookingModel->getMonthlyBookingTrends($startDate, $endDate);

        echo json_encode($trends);

        } catch (Exception $e) {

            echo json_encode(["error" => "Failed to retrieve monthly booking trends: " . $e->getMessage()]);

        }

    }

    

    public function topDestinations() {

        header("Content-Type: application/json");

        

        try {

            // Get data from POST request

            $jsonData = file_get_contents('php://input');

            $data = json_decode($jsonData, true);

            

            $startDate = isset($data['start_date']) ? $data['start_date'] : null;

            $endDate = isset($data['end_date']) ? $data['end_date'] : null;

            

            $destinations = $this->bookingModel->getTopDestinations($startDate, $endDate);

        echo json_encode($destinations);

        } catch (Exception $e) {

            echo json_encode(["error" => "Failed to retrieve top destinations: " . $e->getMessage()]);

        }

    }

    

    public function bookingStatusDistribution() {

        header("Content-Type: application/json");

        

        try {

            // Get data from POST request

            $jsonData = file_get_contents('php://input');

            $data = json_decode($jsonData, true);

            

            $startDate = isset($data['start_date']) ? $data['start_date'] : null;

            $endDate = isset($data['end_date']) ? $data['end_date'] : null;

            

            $statusDistribution = $this->bookingModel->getBookingStatusDistribution($startDate, $endDate);

            echo json_encode($statusDistribution);

        } catch (Exception $e) {

            echo json_encode(["error" => "Failed to retrieve booking status distribution: " . $e->getMessage()]);

        }

    }

    

    public function revenueTrends() {

        header("Content-Type: application/json");

        

        try {

            // Get data from POST request

            $jsonData = file_get_contents('php://input');

            $data = json_decode($jsonData, true);

            

            $startDate = isset($data['start_date']) ? $data['start_date'] : null;

            $endDate = isset($data['end_date']) ? $data['end_date'] : null;

            

            $trends = $this->bookingModel->getRevenueTrends($startDate, $endDate);

            echo json_encode($trends);

        } catch (Exception $e) {

            echo json_encode(["error" => "Failed to retrieve revenue trends: " . $e->getMessage()]);

        }

    }

    

    // New method for showing the create booking form

    public function showCreateBookingForm() {

        include_once __DIR__ . "/../../views/admin/create_booking.php";

    }

    

    // New method for processing admin-created bookings

    public function createBooking() {

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $data = json_decode(file_get_contents("php://input"), true);

            

            // Validate required fields

            $requiredFields = [

                'clientName', 'contactNumber', 'email', 

                'destination', 'pickupPoint', 'dateOfTour', 

                'numberOfDays', 'numberOfBuses', 'totalCost'

            ];

            

            foreach ($requiredFields as $field) {

                if (!isset($data[$field]) || $data[$field] === '') {

                    header("Content-Type: application/json");

                    echo json_encode([

                        "success" => false, 

                        "message" => "Please fill in all required fields.",

                        "field" => $field

                    ]);

                    return;

                }

            }

            

            // Prepare booking data for the model

            $bookingData = [

                'client_name' => $data['clientName'],

                'contact_number' => $data['contactNumber'],

                'email' => $data['email'],

                'address' => $data['address'],

                'destination' => $data['destination'],

                'pickup_point' => $data['pickupPoint'],

                'date_of_tour' => $data['dateOfTour'],

                'number_of_days' => (int)$data['numberOfDays'],

                'number_of_buses' => (int)$data['numberOfBuses'],

                'estimated_pax' => isset($data['estimatedPax']) ? (int)$data['estimatedPax'] : 0,

                'total_cost' => (float)$data['totalCost'],

                'discount' => isset($data['discount']) ? (float)$data['discount'] : 0,

                'notes' => isset($data['notes']) ? $data['notes'] : '',

                'status' => 'Confirmed', // Admin-created bookings are auto-confirmed

                'created_by' => 'admin', // Mark as created by admin

                'stops' => isset($data['stops']) ? $data['stops'] : [],

            ];

            

            // Calculate the end date based on start date and number of days

            $startDate = new DateTime($data['dateOfTour']);

            $endDate = clone $startDate;

            $endDate->modify('+' . ((int)$data['numberOfDays'] - 1) . ' days');

            $bookingData['end_of_tour'] = $endDate->format('Y-m-d');

            

            // Add initial payment if provided

            if (isset($data['initialPayment']) && $data['initialPayment']) {

                $bookingData['initial_payment'] = [

                    'amount_paid' => (float)$data['initialPayment']['amountPaid'],

                    'payment_method' => $data['initialPayment']['paymentMethod'],

                    'payment_reference' => $data['initialPayment']['paymentReference'] ?? null

                ];

            }

            

            // Process the booking creation

            $result = $this->bookingModel->createBookingByAdmin($bookingData);

            

            header("Content-Type: application/json");

            if ($result['success']) {

                echo json_encode([

                    "success" => true, 

                    "message" => "Booking created successfully!",

                    "booking_id" => $result['booking_id']

                ]);

            } else {

                echo json_encode([

                    "success" => false, 

                    "message" => $result['message']

                ]);

            }

        }

    }



    // Return only available buses and drivers for this booking's date range

    public function getAvailableResourcesForBooking() {

        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"), true);

        $booking_id = isset($data["bookingId"]) ? (int)$data["bookingId"] : 0;

        if ($booking_id <= 0) {

            echo json_encode(["success" => false, "message" => "Invalid booking id"]);

            return;

        }

        $resources = $this->bookingModel->getAvailableResourcesForBooking($booking_id);

        echo json_encode(["success" => true, "buses" => $resources['buses'], "drivers" => $resources['drivers']]);

    }

}

?>