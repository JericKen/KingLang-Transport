<?php

require_once __DIR__ . "/../../../config/database.php";



class UserManagementModel {

    private $conn;



    public function __construct() {

        global $pdo;

        $this->conn = $pdo;

    }



    public function getAllUsers($offset = 0, $limit = 10, $searchTerm = '', $sortColumn = 'created_at', $sortDirection = 'DESC', $roleFilter = '') {

        try {

            // Validate sort column to prevent SQL injection

            $allowedColumns = ['user_id', 'first_name', 'last_name', 'email', 'contact_number', 'role', 'created_at'];

            if (!in_array($sortColumn, $allowedColumns)) {

                $sortColumn = 'created_at'; // Default sort

            }

            

            // Validate sort direction

            $sortDirection = strtoupper($sortDirection);

            if ($sortDirection !== 'ASC' && $sortDirection !== 'DESC') {

                $sortDirection = 'DESC'; // Default direction

            }

            

            $query = "SELECT user_id, first_name, last_name, email, contact_number, role, created_at, company_name 

                      FROM users WHERE deleted_at IS NULL";

            

            if (!empty($searchTerm)) {

                $searchTerm = "%$searchTerm%";

                $query .= " AND (first_name LIKE :searchTerm OR last_name LIKE :searchTerm OR 

                           email LIKE :searchTerm OR contact_number LIKE :searchTerm)";

            }

            

            // Add role filter if provided

            if (!empty($roleFilter) && $roleFilter !== 'All') {

                $query .= " AND role = :roleFilter";

            }

            

            $query .= " ORDER BY $sortColumn $sortDirection LIMIT :offset, :limit";

            

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

            

            if (!empty($searchTerm)) {

                $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);

            }

            

            // Bind role filter if provided

            if (!empty($roleFilter) && $roleFilter !== 'All') {

                $stmt->bindParam(':roleFilter', $roleFilter, PDO::PARAM_STR);

            }

            

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            return ["error" => "Database error: " . $e->getMessage()];

        }

    }



    public function getTotalUsersCount($searchTerm = '', $roleFilter = '') {

        try {

            $query = "SELECT COUNT(*) as total FROM users WHERE deleted_at IS NULL";

            

            if (!empty($searchTerm)) {

                $searchTerm = "%$searchTerm%";

                $query .= " AND (first_name LIKE :searchTerm OR last_name LIKE :searchTerm OR 

                           email LIKE :searchTerm OR contact_number LIKE :searchTerm)";

            }

            

            // Add role filter if provided

            if (!empty($roleFilter) && $roleFilter !== 'All') {

                $query .= " AND role = :roleFilter";

            }

            

            $stmt = $this->conn->prepare($query);

            

            if (!empty($searchTerm)) {

                $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);

            }

            

            // Bind role filter if provided

            if (!empty($roleFilter) && $roleFilter !== 'All') {

                $stmt->bindParam(':roleFilter', $roleFilter, PDO::PARAM_STR);

            }

            

            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['total'];

        } catch (PDOException $e) {

            return 0;

        }

    }



    public function getDeletedUsers($offset = 0, $limit = 10, $searchTerm = '', $sortColumn = 'created_at', $sortDirection = 'DESC', $roleFilter = '') {

        try {

            $allowedColumns = ['user_id', 'first_name', 'last_name', 'email', 'contact_number', 'role', 'created_at'];
            if (!in_array($sortColumn, $allowedColumns)) {
                $sortColumn = 'created_at';
            }
            $sortDirection = strtoupper($sortDirection);
            if ($sortDirection !== 'ASC' && $sortDirection !== 'DESC') {
                $sortDirection = 'DESC';
            }

            $query = "SELECT user_id, first_name, last_name, email, contact_number, role, created_at, company_name 

                      FROM users WHERE deleted_at IS NOT NULL";

            if (!empty($searchTerm)) {
                $searchTermLike = "%$searchTerm%";
                $query .= " AND (first_name LIKE :searchTerm OR last_name LIKE :searchTerm OR 
                           email LIKE :searchTerm OR contact_number LIKE :searchTerm)";
            }

            if (!empty($roleFilter) && $roleFilter !== 'All') {
                $query .= " AND role = :roleFilter";
            }

            $query .= " ORDER BY $sortColumn $sortDirection LIMIT :offset, :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            if (!empty($searchTerm)) {
                $stmt->bindParam(':searchTerm', $searchTermLike, PDO::PARAM_STR);
            }
            if (!empty($roleFilter) && $roleFilter !== 'All') {
                $stmt->bindParam(':roleFilter', $roleFilter, PDO::PARAM_STR);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["error" => "Database error: " . $e->getMessage()];
        }

    }



    public function getTotalDeletedUsersCount($searchTerm = '', $roleFilter = '') {

        try {
            $query = "SELECT COUNT(*) as total FROM users WHERE deleted_at IS NOT NULL";
            if (!empty($searchTerm)) {
                $searchTermLike = "%$searchTerm%";
                $query .= " AND (first_name LIKE :searchTerm OR last_name LIKE :searchTerm OR 
                           email LIKE :searchTerm OR contact_number LIKE :searchTerm)";
            }
            if (!empty($roleFilter) && $roleFilter !== 'All') {
                $query .= " AND role = :roleFilter";
            }
            $stmt = $this->conn->prepare($query);
            if (!empty($searchTerm)) {
                $stmt->bindParam(':searchTerm', $searchTermLike, PDO::PARAM_STR);
            }
            if (!empty($roleFilter) && $roleFilter !== 'All') {
                $stmt->bindParam(':roleFilter', $roleFilter, PDO::PARAM_STR);
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            return 0;
        }

    }



    public function getUserById($userId) {

        try {

            $stmt = $this->conn->prepare("SELECT user_id, first_name, last_name, email, contact_number, role, created_at, company_name

                                         FROM users WHERE user_id = :userId AND deleted_at IS NULL");

            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            return ["error" => "Database error: " . $e->getMessage()];

        }

    }



    public function createUser($firstName, $lastName, $email, $contactNumber, $password, $role) {

        try {

            // Check if email already exists

            $checkStmt = $this->conn->prepare("SELECT user_id FROM users WHERE email = :email AND deleted_at IS NULL");

            $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);

            $checkStmt->execute();

            

            if ($checkStmt->fetch()) {

                return ["error" => "Email already exists"];

            }

            

            // Check if contact number already exists

            if (!empty($contactNumber)) {

                $checkStmt = $this->conn->prepare("SELECT user_id FROM users WHERE contact_number = :contact_number AND deleted_at IS NULL");

                $checkStmt->bindParam(':contact_number', $contactNumber, PDO::PARAM_STR);

                $checkStmt->execute();

                

                if ($checkStmt->fetch()) {

                    return ["error" => "Contact number already exists"];

                }

            }

            

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            

            $stmt = $this->conn->prepare("INSERT INTO users (first_name, last_name, email, contact_number, password, role) 

                                        VALUES (:firstName, :lastName, :email, :contactNumber, :password, :role)");

            

            $stmt->bindParam(':firstName', $firstName, PDO::PARAM_STR);

            $stmt->bindParam(':lastName', $lastName, PDO::PARAM_STR);

            $stmt->bindParam(':email', $email, PDO::PARAM_STR);

            $stmt->bindParam(':contactNumber', $contactNumber, PDO::PARAM_STR);

            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

            $stmt->bindParam(':role', $role, PDO::PARAM_STR);

            

            $stmt->execute();

            return ["success" => "User created successfully", "user_id" => $this->conn->lastInsertId()];

        } catch (PDOException $e) {

            return ["error" => "Database error: " . $e->getMessage()];

        }

    }



    public function updateUser($userId, $firstName, $lastName, $email, $contactNumber, $role, $companyName, $password = null) {

        try {

            // Check if email already exists for another user

            $checkStmt = $this->conn->prepare("SELECT user_id FROM users WHERE email = :email AND user_id != :userId AND deleted_at IS NULL");

            $checkStmt->bindParam(':email', $email, PDO::PARAM_STR);

            $checkStmt->bindParam(':userId', $userId, PDO::PARAM_INT);

            $checkStmt->execute();

            

            if ($checkStmt->fetch()) {

                return ["error" => "Email already exists"];

            }

            

            // Check if contact number already exists for another user

            if (!empty($contactNumber)) {

                $checkStmt = $this->conn->prepare("SELECT user_id FROM users WHERE contact_number = :contact_number AND user_id != :userId AND deleted_at IS NULL");

                $checkStmt->bindParam(':contact_number', $contactNumber, PDO::PARAM_STR);

                $checkStmt->bindParam(':userId', $userId, PDO::PARAM_INT);

                $checkStmt->execute();

                

                if ($checkStmt->fetch()) {

                    return ["error" => "Contact number already exists"];

                }

            }

            

            if ($password) {

                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $this->conn->prepare("UPDATE users SET first_name = :firstName, last_name = :lastName, 

                                            email = :email, contact_number = :contactNumber, company_name = :company_name

                                            password = :password, role = :role 

                                            WHERE user_id = :userId");

                $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

            } else {

                $stmt = $this->conn->prepare("UPDATE users SET first_name = :firstName, last_name = :lastName, 

                                            email = :email, contact_number = :contactNumber, role = :role, company_name = :company_name

                                            WHERE user_id = :userId");

            }

            

            $stmt->bindParam(':firstName', $firstName, PDO::PARAM_STR);

            $stmt->bindParam(':lastName', $lastName, PDO::PARAM_STR);

            $stmt->bindParam(':email', $email, PDO::PARAM_STR);

            $stmt->bindParam(':contactNumber', $contactNumber, PDO::PARAM_STR);

            $stmt->bindParam(':role', $role, PDO::PARAM_STR);

            $stmt->bindParam(':company_name', $companyName, PDO::PARAM_STR);

            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

            

            $stmt->execute();

            return ["success" => "User updated successfully"];

        } catch (PDOException $e) {

            return ["error" => "Database error: " . $e->getMessage()];

        }

    }



    public function deleteUser($userId) {

        try {

            // Check if user has related bookings

            $checkStmt = $this->conn->prepare("SELECT COUNT(*) as bookingCount FROM bookings WHERE user_id = :userId");

            $checkStmt->bindParam(':userId', $userId, PDO::PARAM_INT);

            $checkStmt->execute();

            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

            

            if ($result['bookingCount'] > 0) {

                return ["error" => "Cannot delete user with existing bookings"];

            }

            

            // Soft delete by setting deleted_at
            $stmt = $this->conn->prepare("UPDATE users SET deleted_at = NOW() WHERE user_id = :userId AND deleted_at IS NULL");

            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

            $stmt->execute();

            

            if ($stmt->rowCount() > 0) {

                return ["success" => "User deleted successfully"];

            } else {

                return ["error" => "User not found"];

            }

        } catch (PDOException $e) {

            return ["error" => "Database error: " . $e->getMessage()];

        }

    }



    public function restoreUser($userId) {

        try {

            // Only restore if currently soft-deleted
            $stmt = $this->conn->prepare("UPDATE users SET deleted_at = NULL WHERE user_id = :userId AND deleted_at IS NOT NULL");

            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);

            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                return ["success" => "User restored successfully"];

            } else {

                return ["error" => "User not found or not deleted"];

            }

        } catch (PDOException $e) {

            return ["error" => "Database error: " . $e->getMessage()];

        }

    }



    public function getUserStats() {

        try {

            // Get total users count

            $totalQuery = "SELECT COUNT(*) as total FROM users WHERE deleted_at IS NULL";

            $totalStmt = $this->conn->prepare($totalQuery);

            $totalStmt->execute();

            $totalResult = $totalStmt->fetch(PDO::FETCH_ASSOC);

            

            // Get count by role

            $roleQuery = "SELECT role, COUNT(*) as count FROM users WHERE deleted_at IS NULL GROUP BY role";

            $roleStmt = $this->conn->prepare($roleQuery);

            $roleStmt->execute();

            $roleResults = $roleStmt->fetchAll(PDO::FETCH_ASSOC);

            

            // Format the stats

            $stats = [

                'total' => $totalResult['total'] ?? 0,

                'client' => 0,

                'admin' => 0,

                'superAdmin' => 0

            ];

            

            foreach ($roleResults as $role) {

                if ($role['role'] == 'Client') {

                    $stats['client'] = $role['count'];

                } else if ($role['role'] == 'Admin') {

                    $stats['admin'] = $role['count'];

                } else if ($role['role'] == 'Super Admin') {

                    $stats['superAdmin'] = $role['count'];

                }

            }

            

            return $stats;

        } catch (PDOException $e) {

            return ["error" => "Database error: " . $e->getMessage()];

        }

    }



    public function getUserStatistics() {

        try {

            $stats = [];

            

            // Get total number of users

            $totalUsersQuery = "SELECT COUNT(*) as total FROM users WHERE deleted_at IS NULL";

            $totalUsersResult = $this->conn->prepare($totalUsersQuery);

            $totalUsersResult->execute();

            $stats['totalUsers'] = $totalUsersResult->fetch(PDO::FETCH_ASSOC)['total'];

            

            // Get users registered in the last 30 days

            $recentUsersQuery = "SELECT COUNT(*) as recent FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND deleted_at IS NULL";

            $recentUsersResult = $this->conn->prepare($recentUsersQuery);

            $recentUsersResult->execute();

            $stats['recentUsers'] = $recentUsersResult->fetch(PDO::FETCH_ASSOC)['recent'];

            

            // Get active vs inactive users - use role as a proxy

            $adminUsersQuery = "SELECT COUNT(*) as admin FROM users WHERE role IN ('Admin', 'Super Admin') AND deleted_at IS NULL";

            $adminUsersResult = $this->conn->prepare($adminUsersQuery);

            $adminUsersResult->execute();

            $stats['activeUsers'] = $adminUsersResult->fetch(PDO::FETCH_ASSOC)['admin'];

            

            $clientUsersQuery = "SELECT COUNT(*) as client FROM users WHERE role = 'Client' AND deleted_at IS NULL";

            $clientUsersResult = $this->conn->prepare($clientUsersQuery);

            $clientUsersResult->execute();

            $stats['inactiveUsers'] = $clientUsersResult->fetch(PDO::FETCH_ASSOC)['client'];

            

            return $stats;

        } catch (PDOException $e) {

            throw new PDOException("Error fetching user statistics: " . $e->getMessage());

        }

    }

}

?> 