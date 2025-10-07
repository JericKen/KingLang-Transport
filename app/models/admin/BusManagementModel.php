<?php

require_once __DIR__ . "/../../../config/database.php";



class BusManagementModel {

    private $conn;



    public function __construct() {

        global $pdo;

        $this->conn = $pdo;

    }

    

    /**

     * Get all buses

     * @return array Array of buses

     */

    public function getAllBuses() {

        $stmt = $this->conn->prepare("SELECT * FROM buses WHERE deleted_at IS NULL ORDER BY name ASC");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    public function getDeletedBuses() {
        $stmt = $this->conn->prepare("SELECT * FROM buses WHERE deleted_at IS NOT NULL ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    

    /**

     * Get a single bus by ID

     * @param int $busId The bus ID

     * @return array|false Bus data or false if not found

     */

    public function getBusById($busId) {

        $stmt = $this->conn->prepare("SELECT * FROM buses WHERE bus_id = :bus_id AND deleted_at IS NULL");

        $stmt->bindParam(":bus_id", $busId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    

    /**

     * Add a new bus

     * @param string $name Bus name

     * @param string $capacity Bus capacity

     * @param string $status Bus status

     * @param string $licensePlate Bus license plate

     * @param string $model Bus model

     * @param int $year Bus year

     * @param string $lastMaintenance Last maintenance date (YYYY-MM-DD)

     * @return bool|string True on success or error message

     */

    public function addBus($name, $capacity, $status, $licensePlate = null, $model = null, $year = null, $lastMaintenance = null) {

        try {

            // Check if bus with same name already exists

            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM buses WHERE name = :name");

            $stmt->bindParam(":name", $name);

            $stmt->execute();

            

            if ($stmt->fetchColumn() > 0) {

                return "A bus with this name already exists.";

            }

            

            $stmt = $this->conn->prepare("INSERT INTO buses (name, capacity, status, license_plate, model, year, last_maintenance) 

                                         VALUES (:name, :capacity, :status, :license_plate, :model, :year, :last_maintenance)");

            $stmt->bindParam(":name", $name);

            $stmt->bindParam(":capacity", $capacity);

            $stmt->bindParam(":status", $status);

            $stmt->bindParam(":license_plate", $licensePlate);

            $stmt->bindParam(":model", $model);

            $stmt->bindParam(":year", $year, PDO::PARAM_INT);

            $stmt->bindParam(":last_maintenance", $lastMaintenance);

            $result = $stmt->execute();

            

            if ($result) {

                return true;

            } else {

                return "Failed to add bus.";

            }

        } catch (PDOException $e) {

            return "Database error: " . $e->getMessage();

        }

    }

    

    /**

     * Update an existing bus

     * @param int $busId Bus ID

     * @param string $name Bus name

     * @param string $capacity Bus capacity

     * @param string $status Bus status

     * @param string $licensePlate Bus license plate

     * @param string $model Bus model

     * @param int $year Bus year

     * @param string $lastMaintenance Last maintenance date (YYYY-MM-DD)

     * @return bool|string True on success or error message

     */

    public function updateBus($busId, $name, $capacity, $status, $licensePlate = null, $model = null, $year = null, $lastMaintenance = null) {

        try {

            // Check if another bus with the same name exists (excluding this one)

            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM buses WHERE name = :name AND bus_id != :bus_id");

            $stmt->bindParam(":name", $name);

            $stmt->bindParam(":bus_id", $busId, PDO::PARAM_INT);

            $stmt->execute();

            

            if ($stmt->fetchColumn() > 0) {

                return "Another bus with this name already exists.";

            }

            

            $stmt = $this->conn->prepare("UPDATE buses SET 

                                         name = :name, 

                                         capacity = :capacity, 

                                         status = :status,

                                         license_plate = :license_plate,

                                         model = :model,

                                         year = :year,

                                         last_maintenance = :last_maintenance

                                         WHERE bus_id = :bus_id");

            $stmt->bindParam(":bus_id", $busId, PDO::PARAM_INT);

            $stmt->bindParam(":name", $name);

            $stmt->bindParam(":capacity", $capacity);

            $stmt->bindParam(":status", $status);

            $stmt->bindParam(":license_plate", $licensePlate);

            $stmt->bindParam(":model", $model);

            $stmt->bindParam(":year", $year, PDO::PARAM_INT);

            $stmt->bindParam(":last_maintenance", $lastMaintenance);

            $result = $stmt->execute();

            

            if ($result) {

                return true;

            } else {

                return "Failed to update bus.";

            }

        } catch (PDOException $e) {

            return "Database error: " . $e->getMessage();

        }

    }

    

    /**

     * Delete a bus

     * @param int $busId Bus ID

     * @return bool|string True on success or error message

     */

    public function deleteBus($busId) {

        try {

            // Check if bus is being used in any bookings

            $stmt = $this->conn->prepare("SELECT COUNT(*) FROM booking_buses WHERE bus_id = :bus_id");

            $stmt->bindParam(":bus_id", $busId, PDO::PARAM_INT);

            $stmt->execute();

            

            if ($stmt->fetchColumn() > 0) {

                return "Cannot delete this bus as it is associated with one or more bookings.";

            }

            

            // Soft delete
            $stmt = $this->conn->prepare("UPDATE buses SET deleted_at = NOW() WHERE bus_id = :bus_id AND deleted_at IS NULL");

            $stmt->bindParam(":bus_id", $busId, PDO::PARAM_INT);

            $result = $stmt->execute();

            

            if ($result && $stmt->rowCount() > 0) {

                return true;

            } else {

                return "Failed to delete bus.";

            }

        } catch (PDOException $e) {

            return "Database error: " . $e->getMessage();

        }

    }

    public function restoreBus($busId) {
        try {
            $stmt = $this->conn->prepare("UPDATE buses SET deleted_at = NULL WHERE bus_id = :bus_id AND deleted_at IS NOT NULL");
            $stmt->bindParam(":bus_id", $busId, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return true;
            }
            return "Bus not found or not deleted.";
        } catch (PDOException $e) {
            return "Database error: " . $e->getMessage();
        }
    }

    

    /**

     * Get bus usage statistics

     * @return array Bus usage statistics

     */

    public function getBusUsageStats() {

        $stats = [];

        

        // Get total buses

        $stmt = $this->conn->prepare("SELECT COUNT(*) as total, 

                                      SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active,

                                      SUM(CASE WHEN status = 'Maintenance' THEN 1 ELSE 0 END) as maintenance

                                      FROM buses WHERE deleted_at IS NULL");

        $stmt->execute();

        $stats['counts'] = $stmt->fetch(PDO::FETCH_ASSOC);

        

        // Get buses with most bookings (top 5)

        $stmt = $this->conn->prepare("

            SELECT b.bus_id, b.name, COUNT(bb.booking_buses_id) as booking_count

            FROM buses b

            JOIN booking_buses bb ON b.bus_id = bb.bus_id

            JOIN bookings bk ON bb.booking_id = bk.booking_id

            WHERE b.deleted_at IS NULL

              AND bk.status NOT IN ('Canceled', 'Rejected')

            GROUP BY b.bus_id

            ORDER BY booking_count DESC

            LIMIT 5

        ");

        $stmt->execute();

        $stats['most_used'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        

        // Get current month usage

        $currentMonth = date('Y-m-01');

        $nextMonth = date('Y-m-01', strtotime('+1 month'));

        

        $stmt = $this->conn->prepare("

            SELECT b.bus_id, b.name, COUNT(DISTINCT bk.booking_id) as booking_count

            FROM buses b

            LEFT JOIN booking_buses bb ON b.bus_id = bb.bus_id

            LEFT JOIN bookings bk ON bb.booking_id = bk.booking_id

            WHERE b.deleted_at IS NULL AND bk.date_of_tour >= :current_month AND bk.date_of_tour < :next_month

              AND bk.status NOT IN ('Canceled', 'Rejected')

            GROUP BY b.bus_id

            ORDER BY booking_count DESC

        ");

        $stmt->bindParam(':current_month', $currentMonth);

        $stmt->bindParam(':next_month', $nextMonth);

        $stmt->execute();

        $stats['current_month'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        

        return $stats;

    }

    

    /**

     * Get bus availability for a date range

     * @param string $startDate Start date (YYYY-MM-DD)

     * @param string $endDate End date (YYYY-MM-DD)

     * @return array Bus availability for each date

     */

    public function getBusAvailability($startDate, $endDate) {

        // Get total number of active buses

        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM buses WHERE status = 'Active' AND deleted_at IS NULL");

        $stmt->execute();

        $totalBuses = (int) $stmt->fetchColumn();

        

        // Create an array for each date in the range

        $start = new DateTime($startDate);

        $end = new DateTime($endDate);

        $interval = DateInterval::createFromDateString('1 day');

        $period = new DatePeriod($start, $interval, $end->modify('+1 day'));

        

        $availability = [];

        

        foreach ($period as $dt) {

            $currentDate = $dt->format("Y-m-d");

            

            // For each date, find how many buses are already booked

            $stmt = $this->conn->prepare("

                SELECT COUNT(DISTINCT bb.bus_id) 

                FROM booking_buses bb

                JOIN bookings bo ON bb.booking_id = bo.booking_id

                WHERE 

                    -- Only consider bookings with active statuses

                    (bo.status = 'Confirmed' OR bo.status = 'Processing')

                    AND (bo.is_rebooked = 0)

                    -- Date range check

                    AND (bo.date_of_tour <= :current_date AND bo.end_of_tour >= :current_date)

            ");

            $stmt->bindParam(":current_date", $currentDate);

            $stmt->execute();

            $bookedBuses = (int) $stmt->fetchColumn();

            

            // Calculate available buses

            $availableBuses = $totalBuses - $bookedBuses;

            if ($availableBuses < 0) $availableBuses = 0;

            

            $availability[] = [

                "date" => $currentDate,

                "available" => $availableBuses,

                "total" => $totalBuses,

                "booked" => $bookedBuses

            ];

        }

        

        return $availability;

    }

    

    /**

     * Get bus schedule for a specific bus

     * @param int $busId Bus ID

     * @param string $startDate Start date (YYYY-MM-DD)

     * @param string $endDate End date (YYYY-MM-DD)

     * @return array Bus schedule

     */

    public function getBusSchedule($busId, $startDate, $endDate) {

        $stmt = $this->conn->prepare("

            SELECT b.booking_id, b.destination, b.pickup_point, 

                   b.date_of_tour, b.end_of_tour, b.status,

                   u.first_name, u.last_name

            FROM bookings b

            JOIN booking_buses bb ON b.booking_id = bb.booking_id

            JOIN users u ON b.user_id = u.user_id

            WHERE bb.bus_id = :bus_id

            AND ((b.date_of_tour BETWEEN :start_date AND :end_date) 

                OR (b.end_of_tour BETWEEN :start_date AND :end_date)

                OR (b.date_of_tour <= :start_date AND b.end_of_tour >= :end_date))

            AND (b.status = 'Confirmed' OR b.status = 'Processing')

            ORDER BY b.date_of_tour ASC

        ");

        $stmt->bindParam(":bus_id", $busId, PDO::PARAM_INT);

        $stmt->bindParam(":start_date", $startDate);

        $stmt->bindParam(":end_date", $endDate);

        $stmt->execute();

        

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    

    /**

     * Log an action to the audit trail

     * @param int $userId User ID

     * @param string $userRole User role

     * @param string $actionType Type of action (add, update, delete)

     * @param string $details Details of the action

     * @return bool True on success, false on failure

     */

    public function logAuditTrail($userId, $userRole, $actionType, $details) {

        try {

            $stmt = $this->conn->prepare("

                INSERT INTO audit_trail (user_id, user_role, action_type, entity_type, details)

                VALUES (:user_id, :user_role, :action_type, 'bus', :details)

            ");

            

            return $stmt->execute([

                ':user_id' => $userId,

                ':user_role' => $userRole,

                ':action_type' => $actionType,

                ':details' => $details

            ]);

        } catch (Exception $e) {

            // Silently fail - audit trail should not interrupt normal operation

            return false;

        }

    }

}

?> 