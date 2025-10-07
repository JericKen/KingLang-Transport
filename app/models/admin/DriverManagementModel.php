<?php

require_once __DIR__ . "/../../../config/database.php";



class DriverManagementModel {

    private $conn;



    public function __construct() {

        global $pdo;

        $this->conn = $pdo;

    }

    

    /**

     * Get all drivers

     */

    public function getAllDrivers() {

        $stmt = $this->conn->prepare("SELECT * FROM drivers WHERE deleted_at IS NULL ORDER BY full_name ASC");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    

    /**

     * Get driver by ID

     */

    public function getDriverById($driverId) {

        $stmt = $this->conn->prepare("SELECT * FROM drivers WHERE driver_id = :driver_id AND deleted_at IS NULL");

        $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    

    /**

     * Add a new driver

     */

    public function addDriver($data) {

        $stmt = $this->conn->prepare("

            INSERT INTO drivers (full_name, license_number, contact_number, address, 

                                status, availability, date_hired, license_expiry, notes)

            VALUES (:full_name, :license_number, :contact_number, :address, 

                    :status, :availability, :date_hired, :license_expiry, :notes)

        ");

        

        $stmt->bindParam(':full_name', $data['full_name'], PDO::PARAM_STR);

        $stmt->bindParam(':license_number', $data['license_number'], PDO::PARAM_STR);

        $stmt->bindParam(':contact_number', $data['contact_number'], PDO::PARAM_STR);

        $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);

        $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);

        $stmt->bindParam(':availability', $data['availability'], PDO::PARAM_STR);

        $stmt->bindParam(':date_hired', $data['date_hired'], PDO::PARAM_STR);

        $stmt->bindParam(':license_expiry', $data['license_expiry'], PDO::PARAM_STR);

        $stmt->bindParam(':notes', $data['notes'], PDO::PARAM_STR);

        

        $stmt->execute();

        return $this->conn->lastInsertId();

    }

    

    /**

     * Update an existing driver

     */

    public function updateDriver($driverId, $data) {

        $stmt = $this->conn->prepare("

            UPDATE drivers SET 

                full_name = :full_name,

                license_number = :license_number,

                contact_number = :contact_number,

                address = :address,

                status = :status,

                availability = :availability,

                date_hired = :date_hired,

                license_expiry = :license_expiry,

                notes = :notes

            WHERE driver_id = :driver_id

        ");

        

        $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);

        $stmt->bindParam(':full_name', $data['full_name'], PDO::PARAM_STR);

        $stmt->bindParam(':license_number', $data['license_number'], PDO::PARAM_STR);

        $stmt->bindParam(':contact_number', $data['contact_number'], PDO::PARAM_STR);

        $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);

        $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);

        $stmt->bindParam(':availability', $data['availability'], PDO::PARAM_STR);

        $stmt->bindParam(':date_hired', $data['date_hired'], PDO::PARAM_STR);

        $stmt->bindParam(':license_expiry', $data['license_expiry'], PDO::PARAM_STR);

        $stmt->bindParam(':notes', $data['notes'], PDO::PARAM_STR);

        

        return $stmt->execute();

    }

    

    /**

     * Delete a driver

     */

    public function deleteDriver($driverId) {

        // Prevent deletion if driver is associated with any bookings
        $check = $this->conn->prepare("SELECT COUNT(*) FROM booking_driver WHERE driver_id = :driver_id");
        $check->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
        $check->execute();
        if ((int)$check->fetchColumn() > 0) {
            return "Cannot delete driver associated with one or more bookings.";
        }

        $stmt = $this->conn->prepare("UPDATE drivers SET deleted_at = NOW() WHERE driver_id = :driver_id AND deleted_at IS NULL");
        $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
        if ($stmt->execute() && $stmt->rowCount() > 0) {
            return true;
        }
        return "Failed to delete driver.";

    }

    public function restoreDriver($driverId) {
        $stmt = $this->conn->prepare("UPDATE drivers SET deleted_at = NULL WHERE driver_id = :driver_id AND deleted_at IS NOT NULL");
        $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function getDeletedDrivers() {
        $stmt = $this->conn->prepare("SELECT * FROM drivers WHERE deleted_at IS NOT NULL ORDER BY full_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    

    /**

     * Update driver profile photo

     */

    public function updateDriverPhoto($driverId, $photoPath) {

        $stmt = $this->conn->prepare("UPDATE drivers SET profile_photo = :profile_photo WHERE driver_id = :driver_id");

        $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);

        $stmt->bindParam(':profile_photo', $photoPath, PDO::PARAM_STR);

        return $stmt->execute();

    }

    

    /**

     * Get driver statistics

     */

    public function getDriverStatistics() {

        $stats = [];

        

        // Total drivers

        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM drivers WHERE deleted_at IS NULL");

        $stmt->execute();

        $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        

        // Active drivers

        $stmt = $this->conn->prepare("SELECT COUNT(*) as active FROM drivers WHERE status = 'Active' AND deleted_at IS NULL");

        $stmt->execute();

        $stats['active'] = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

        

        // Inactive drivers

        $stmt = $this->conn->prepare("SELECT COUNT(*) as inactive FROM drivers WHERE status = 'Inactive' AND deleted_at IS NULL");

        $stmt->execute();

        $stats['inactive'] = $stmt->fetch(PDO::FETCH_ASSOC)['inactive'];

        

        // On leave drivers

        $stmt = $this->conn->prepare("SELECT COUNT(*) as on_leave FROM drivers WHERE status = 'On Leave' AND deleted_at IS NULL");

        $stmt->execute();

        $stats['on_leave'] = $stmt->fetch(PDO::FETCH_ASSOC)['on_leave'];

        

        // Available drivers

        $stmt = $this->conn->prepare("SELECT COUNT(*) as available FROM drivers WHERE availability = 'Available' AND deleted_at IS NULL");

        $stmt->execute();

        $stats['available'] = $stmt->fetch(PDO::FETCH_ASSOC)['available'];

        

        // Assigned drivers

        $stmt = $this->conn->prepare("SELECT COUNT(*) as assigned FROM drivers WHERE availability = 'Assigned' AND deleted_at IS NULL");

        $stmt->execute();

        $stats['assigned'] = $stmt->fetch(PDO::FETCH_ASSOC)['assigned'];

        

        return $stats;

    }

    

    /**

     * Get most active drivers

     */

    public function getMostActiveDrivers($limit = 5) {

        $stmt = $this->conn->prepare("

            SELECT d.driver_id, d.full_name, COUNT(bd.booking_id) as trip_count 

            FROM drivers d

            JOIN booking_driver bd ON d.driver_id = bd.driver_id

            JOIN bookings b ON bd.booking_id = b.booking_id

            WHERE b.status = 'Completed' AND d.deleted_at IS NULL

            GROUP BY d.driver_id

            ORDER BY trip_count DESC

            LIMIT :limit

        ");

        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    

    /**

     * Get drivers with expiring licenses

     */

    public function getDriversWithExpiringLicenses($daysThreshold = 30) {

        $stmt = $this->conn->prepare("

            SELECT * FROM drivers 

            WHERE license_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)

            ORDER BY license_expiry ASC

        ");

        $stmt->bindParam(':days', $daysThreshold, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    

    /**

     * Get driver schedule

     */

    public function getDriverSchedule($driverId, $startDate = null, $endDate = null) {

        if (!$startDate) {

            $startDate = date('Y-m-d');

        }

        

        if (!$endDate) {

            $endDate = date('Y-m-d', strtotime('+30 days'));

        }

        

        $query = "

            SELECT 

                b.booking_id,

                b.destination,

                b.pickup_point,

                b.date_of_tour,

                b.end_of_tour,

                b.pickup_time,

                b.number_of_days,

                b.status,

                d.driver_id,

                d.full_name AS driver_name,

                u.first_name,

                u.last_name,

                u.company_name

            FROM 

                bookings b

            JOIN 

                booking_driver bd ON b.booking_id = bd.booking_id

            JOIN 

                drivers d ON bd.driver_id = d.driver_id

            JOIN 

                users u ON b.user_id = u.user_id

            WHERE 

                d.driver_id = :driver_id

                AND (

                    (b.date_of_tour BETWEEN :start_date AND :end_date)

                    OR (b.end_of_tour BETWEEN :start_date AND :end_date)

                    OR (b.date_of_tour <= :start_date AND b.end_of_tour >= :end_date)

                )

                AND b.status IN ('Confirmed', 'Processing')

            ORDER BY 

                b.date_of_tour ASC

        ";

        

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':driver_id', $driverId, PDO::PARAM_INT);

        $stmt->bindParam(':start_date', $startDate, PDO::PARAM_STR);

        $stmt->bindParam(':end_date', $endDate, PDO::PARAM_STR);

        $stmt->execute();

        

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

}

?> 