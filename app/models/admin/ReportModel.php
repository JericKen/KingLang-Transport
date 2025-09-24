<?php

require_once __DIR__ . "/../../../config/database.php";



class ReportModel {

    private $conn;



    public function __construct() {

        global $pdo;

        $this->conn = $pdo;

    }



    /**

     * Get booking summary for a date range

     */

    public function getBookingSummary($startDate = null, $endDate = null) {

        try {

            $params = [];

            $whereClause = "WHERE 1=1";

            

            if ($startDate) {

                $whereClause .= " AND date_of_tour >= :start_date";

                $params[':start_date'] = $startDate;

            }   

            

            if ($endDate) {

                $whereClause .= " AND date_of_tour <= :end_date";

                $params[':end_date'] = $endDate;

            }



            // First Query: Revenue-related data

            $sql1 = "SELECT 

                SUM(CASE WHEN p.status = 'Confirmed' AND p.is_canceled = 0 THEN p.amount ELSE 0 END) AS total_revenue,

                SUM(CASE WHEN b.payment_status = 'Paid' THEN 

                        (CASE WHEN p.status = 'Confirmed' AND p.is_canceled = 0 THEN p.amount ELSE 0 END) 

                        ELSE 0 END) AS collected_revenue,

                AVG(CASE WHEN p.status = 'Confirmed' AND p.is_canceled = 0 THEN p.amount ELSE NULL END) AS average_booking_value

            FROM bookings b

            JOIN payments p ON b.booking_id = p.booking_id

            $whereClause";



            $stmt1 = $this->conn->prepare($sql1);

            foreach ($params as $key => $value) {

                $stmt1->bindValue($key, $value);

            }

            $stmt1->execute();

            $revenueData = $stmt1->fetch(PDO::FETCH_ASSOC);



            // Second Query: Booking-related data

            $sql2 = "SELECT 

                COUNT(*) AS total_bookings,

                SUM(CASE WHEN b.status = 'Confirmed' THEN 1 ELSE 0 END) AS confirmed_bookings,

                SUM(CASE WHEN b.status = 'Pending' THEN 1 ELSE 0 END) AS pending_bookings,

                SUM(CASE WHEN b.status = 'Canceled' THEN 1 ELSE 0 END) AS canceled_bookings,

                SUM(CASE WHEN b.status = 'Rejected' THEN 1 ELSE 0 END) AS rejected_bookings,

                SUM(CASE WHEN b.status = 'Completed' THEN 1 ELSE 0 END) AS completed_bookings,

                SUM(CASE WHEN b.payment_status = 'Partially Paid' THEN b.balance ELSE 0 END) AS outstanding_balance,

                SUM(b.number_of_buses) AS total_buses_booked,

                SUM(b.number_of_days) AS total_days_booked

            FROM bookings b

            $whereClause";



            $stmt2 = $this->conn->prepare($sql2);

            foreach ($params as $key => $value) {

                $stmt2->bindValue($key, $value);

            }

            $stmt2->execute();

            $bookingData = $stmt2->fetch(PDO::FETCH_ASSOC);



            // Merge both results

            return array_merge($revenueData, $bookingData);



        } catch (PDOException $e) {

            error_log("Error in getBookingSummary: " . $e->getMessage());

            throw new Exception("Failed to generate booking summary: " . $e->getMessage());

        }

    }



    

    /**

     * Get monthly booking trend

     */

    public function getMonthlyBookingTrend($startDate = null, $endDate = null) {

        try {

            $params = [];

            $whereClause = "WHERE 1=1";

            

            if ($startDate) {

                $whereClause .= " AND date_of_tour >= :start_date";

                $params[':start_date'] = $startDate;

            }

            

            if ($endDate) {

                $whereClause .= " AND date_of_tour <= :end_date";

                $params[':end_date'] = $endDate;

            }



            // Query 1: total bookings per month

            $sqlBookings = "SELECT 

                MONTH(b.date_of_tour) AS month,

                COUNT(b.booking_id) AS total_bookings

            FROM bookings b

            $whereClause

            GROUP BY MONTH(b.date_of_tour)

            ORDER BY month";



            $stmtBookings = $this->conn->prepare($sqlBookings);

            foreach ($params as $key => $value) {

                $stmtBookings->bindValue($key, $value);

            }

            $stmtBookings->execute();

            $bookingsResult = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);



            // Query 2: total revenue per month

            $sqlRevenue = "SELECT 

                MONTH(b.date_of_tour) AS month,

                SUM(CASE WHEN p.status = 'Confirmed' AND p.is_canceled = 0 THEN p.amount ELSE 0 END) as total_revenue

            FROM bookings b

            LEFT JOIN payments p ON b.booking_id = p.booking_id

            $whereClause

            GROUP BY MONTH(b.date_of_tour)

            ORDER BY month";



            $stmtRevenue = $this->conn->prepare($sqlRevenue);

            foreach ($params as $key => $value) {

                $stmtRevenue->bindValue($key, $value);

            }

            $stmtRevenue->execute();

            $revenueResult = $stmtRevenue->fetchAll(PDO::FETCH_ASSOC);



            // Fill in missing months with zero values

            $monthlyData = [];

            for ($i = 1; $i <= 12; $i++) {

                $monthlyData[$i] = [

                    'month' => $i,

                    'total_bookings' => 0,

                    'total_revenue' => 0

                ];

            }



            foreach ($bookingsResult as $row) {

                $monthlyData[$row['month']]['total_bookings'] = (int)$row['total_bookings'];

            }

            foreach ($revenueResult as $row) {

                $monthlyData[$row['month']]['total_revenue'] = (float)$row['total_revenue'];

            }



            return array_values($monthlyData);

        } catch (PDOException $e) {

            error_log("Error in getMonthlyBookingTrend: " . $e->getMessage());

            throw new Exception("Failed to generate monthly booking trend: " . $e->getMessage());

        }

    }

    

    /**

     * Get top destinations by booking count

     */

    public function getTopDestinations($limit = 10, $startDate = null, $endDate = null) {

        try {

            $params = [];

            $whereClause = "WHERE status IN ('Confirmed', 'Completed')";

            

            if ($startDate) {

                $whereClause .= " AND date_of_tour >= :start_date";

                $params[':start_date'] = $startDate;

            }

            

            if ($endDate) {

                $whereClause .= " AND date_of_tour <= :end_date";

                $params[':end_date'] = $endDate;;

            }

            

            $sql = "SELECT 

                b.destination,

                COUNT(b.booking_id) as booking_count,

                SUM(c.total_cost) as total_revenue

            FROM bookings b

            JOIN booking_costs c ON b.booking_id = c.booking_id

            $whereClause

            GROUP BY b.destination

            ORDER BY booking_count DESC

            LIMIT :limit";

            

            $stmt = $this->conn->prepare($sql);

            

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

            $stmt->execute();

            

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error in getTopDestinations: " . $e->getMessage());

            throw new Exception("Failed to generate top destinations report: " . $e->getMessage());

        }

    }

    

    /**

     * Get payment method distribution

     */

    public function getPaymentMethodDistribution($startDate = null, $endDate = null) {

        try {

            $params = [];

            $whereClause = "WHERE status = 'Confirmed' AND is_canceled = 0";

            

            if ($startDate) {

                $whereClause .= " AND payment_date >= :start_date";

                $params[':start_date'] = $startDate;

            }

            

            if ($endDate) {

                $whereClause .= " AND payment_date <= :end_date";

                $params[':end_date'] = $endDate;

            }

            

            $sql = "SELECT 

                payment_method,

                COUNT(*) as payment_count,

                SUM(amount) as total_amount

            FROM payments 

            $whereClause

            GROUP BY payment_method

            ORDER BY payment_count DESC";

            

            $stmt = $this->conn->prepare($sql);

            

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            

            $stmt->execute();

            

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error in getPaymentMethodDistribution: " . $e->getMessage());

            throw new Exception("Failed to generate payment method distribution: " . $e->getMessage());

        }

    }

    

    /**

     * Get cancellation reasons report

     */

    public function getCancellationReport($startDate = null, $endDate = null) {

        try {

            $params = [];

            $whereClause = "WHERE 1=1";

            

            if ($startDate) {

                $whereClause .= " AND c.canceled_at >= :start_date";

                $params[':start_date'] = $startDate;

            }

            

            if ($endDate) {

                $whereClause .= " AND c.canceled_at <= :end_date";

                $params[':end_date'] = $endDate;

            }

            

            $sql = "SELECT 

                c.reason,

                c.canceled_by,

                COUNT(*) as cancellation_count,

                SUM(bc.total_cost) as total_value,

                SUM(c.amount_refunded) as total_refunded

            FROM canceled_trips c

            JOIN bookings b ON c.booking_id = b.booking_id

            JOIN booking_costs bc ON c.booking_id = bc.booking_id

            $whereClause

            GROUP BY c.reason, c.canceled_by

            ORDER BY cancellation_count DESC";

            

            $stmt = $this->conn->prepare($sql);

            

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            

            $stmt->execute();

            

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error in getCancellationReport: " . $e->getMessage());

            throw new Exception("Failed to generate cancellation report: " . $e->getMessage());

        }

    }

    

    /**

     * Get client booking history

     */

    public function getClientBookingHistory($userId, $startDate = null, $endDate = null) {

        try {

            $params = [':user_id' => $userId];

            $whereClause = "WHERE b.user_id = :user_id";



            if ($startDate) {

                $whereClause .= " AND b.date_of_tour >= :start_date";

                $params[':start_date'] = $startDate;

            }



            if ($endDate) {

                $whereClause .= " AND b.date_of_tour <= :end_date";

                $params[':end_date'] = $endDate;

            }



            $sql = "SELECT 

                b.booking_id,

                b.destination,

                b.pickup_point,

                b.date_of_tour,

                b.end_of_tour,

                b.number_of_buses,

                b.number_of_days,

                c.total_cost,

                b.status,

                b.payment_status,

                b.balance

            FROM bookings b

            JOIN booking_costs c ON b.booking_id = c.booking_id

            $whereClause

            ORDER BY b.date_of_tour DESC";

            

            $stmt = $this->conn->prepare($sql);

            

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value); 

            }



            $stmt->execute();

            

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error in getClientBookingHistory: " . $e->getMessage());

            throw new Exception("Failed to generate client booking history: " . $e->getMessage());

        }

    }

    

    /**

     * Get detailed booking list with search/filter

     */

    public function getDetailedBookingList($filters = [], $page = 1, $limit = 20) {

        try {

            $whereClause = "WHERE 1=1";

            $params = [];

            

            // Apply filters

            if (!empty($filters['start_date'])) {

                $whereClause .= " AND b.is_rebooked = :is_rebooked";

                $params[':is_rebooked'] = 0;

                

                $whereClause .= " AND b.date_of_tour >= :start_date";

                $params[':start_date'] = $filters['start_date'];

            }

            

            if (!empty($filters['end_date'])) {

                $whereClause .= " AND b.date_of_tour <= :end_date";

                $params[':end_date'] = $filters['end_date'];

            }

            

            if (!empty($filters['status']) && $filters['status'] !== 'All') {

                $whereClause .= " AND b.status = :status";

                $params[':status'] = $filters['status'];

            }

            

            if (!empty($filters['payment_status']) && $filters['payment_status'] !== 'All') {

                $whereClause .= " AND b.payment_status = :payment_status";

                $params[':payment_status'] = $filters['payment_status'];

            }

            

            if (!empty($filters['search'])) {

                $whereClause .= " AND (b.destination LIKE :search OR CONCAT(u.first_name, ' ', u.last_name) LIKE :search)";

                $params[':search'] = "%{$filters['search']}%";

            }

            

            // Calculate offset for pagination

            $offset = ($page - 1) * $limit;

            

            // Get bookings

            $sql = "SELECT 

                b.booking_id, 

                b.user_id, 

                CONCAT(u.first_name, ' ', u.last_name) AS client_name, 

                u.contact_number, 

                b.destination, 

                b.pickup_point, 

                b.date_of_tour, 

                b.end_of_tour, 

                b.number_of_days, 

                b.number_of_buses, 

                b.status, 

                c.total_cost, 

                b.payment_status,

                b.balance

            FROM bookings b

            JOIN users u ON b.user_id = u.user_id

            JOIN booking_costs c ON b.booking_id = c.booking_id

            $whereClause

            ORDER BY b.date_of_tour DESC

            LIMIT :limit OFFSET :offset";

            

            $stmt = $this->conn->prepare($sql);

            

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();

            

            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            

            // Get total count for pagination

            $countSql = "SELECT COUNT(*) as total FROM bookings b JOIN users u ON b.user_id = u.user_id $whereClause";

            $countStmt = $this->conn->prepare($countSql);

            

            foreach ($params as $key => $value) {

                $countStmt->bindValue($key, $value);

            }

            

            $countStmt->execute();

            $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            

            return [

                'bookings' => $bookings,

                'total' => $totalCount,

                'page' => $page,

                'limit' => $limit,

                'total_pages' => ceil($totalCount / $limit)

            ];

        } catch (PDOException $e) {

            error_log("Error in getDetailedBookingList: " . $e->getMessage());

            throw new Exception("Failed to generate detailed booking list: " . $e->getMessage());

        }

    }

    

    /**

     * Get financial summary report

     */

    public function getFinancialSummary($startDate = null, $endDate = null) {

        try {

            $whereClause = "WHERE 1=1";

            $params = [];

            

            if ($startDate) {

                $whereClause .= " AND b.date_of_tour >= :start_date";

                $params[':start_date'] = $startDate;

            }



            if ($endDate) {

                $whereClause .= " AND b.date_of_tour <= :end_date";

                $params[':end_date'] = $endDate;

            }

            

            $sql = "SELECT 

                SUM(c.total_cost) AS total_revenue,

                SUM(CASE WHEN b.payment_status = 'Paid' THEN c.total_cost ELSE 0 END) AS collected_revenue,

                SUM(CASE WHEN b.payment_status IN ('Partially Paid', 'Unpaid') THEN b.balance ELSE 0 END) AS outstanding_balance,

                COUNT(DISTINCT user_id) AS unique_clients,

                AVG(c.total_cost) AS average_booking_value

            FROM bookings b

            JOIN booking_costs c ON b.booking_id = c.booking_id

            $whereClause";

            

            $stmt = $this->conn->prepare($sql);

            

            foreach ($params as $key => $value) {

                $stmt->bindValue($key, $value);

            }

            

            $stmt->execute();



            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Error in getFinancialSummary: " . $e->getMessage());

            throw new Exception("Failed to generate financial summary: " . $e->getMessage());

        }

    }

} 