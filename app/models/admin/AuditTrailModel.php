<?php

class AuditTrailModel {

    private $pdo;



    public function __construct() {

        global $pdo;

        $this->pdo = $pdo;

    }



    private function getUsernameById($userId) {

        try {

            $statement = $this->pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) as username FROM users WHERE user_id = :user_id");

            $statement->execute([':user_id' => $userId]);

            return $statement->fetchColumn();

        } catch (PDOException $e) {

            error_log("Error fetching username: " . $e->getMessage());

            return null;

        }

    }



    private function getUserRoleById($userId) {

        try {

            $statement = $this->pdo->prepare("SELECT role FROM users WHERE user_id = :user_id");

            $statement->execute([':user_id' => $userId]);

            return $statement->fetchColumn();

        } catch (PDOException $e) {

            error_log("Error fetching user role: " . $e->getMessage());

            return null;

        }

    }



    /**

     * Log an action to the audit trail

     *

     * @param string $action The action performed (e.g., "create", "update", "delete")

     * @param string $entityType The type of entity affected (e.g., "booking", "user", "payment")

     * @param int $entityId The ID of the entity affected

     * @param array|null $oldValues The old values before the change (can be null for create actions)

     * @param array|null $newValues The new values after the change (can be null for delete actions)

     * @return bool Success or failure

     */

    public function logAction($action, $entityType, $entityId, $oldValues = null, $newValues = null, $userId = null) {

        try {

            // Get current user information

            // $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

            $username = $this->getUsernameById($userId);

            $userRole = $this->getUserRoleById($userId);



            error_log("user ID: $userId");

            

            // Get client information

            $ipAddress = $this->getClientIP();

            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;

            

            // Convert arrays to JSON for storage

            $oldValuesJson = $oldValues ? json_encode($oldValues) : null;

            $newValuesJson = $newValues ? json_encode($newValues) : null;

            

            $statement = $this->pdo->prepare("

                INSERT INTO audit_trails (

                    user_id, username, user_role, action, entity_type, entity_id, 

                    old_values, new_values, ip_address, user_agent

                ) VALUES (

                    :user_id, :username, :user_role, :action, :entity_type, :entity_id, 

                    :old_values, :new_values, :ip_address, :user_agent

                )

            ");

            

            $statement->execute([

                ':user_id' => $userId,

                ':username' => $username,

                ':user_role' => $userRole,

                ':action' => $action,

                ':entity_type' => $entityType,

                ':entity_id' => $entityId,

                ':old_values' => $oldValuesJson,

                ':new_values' => $newValuesJson,

                ':ip_address' => $ipAddress,

                ':user_agent' => $userAgent

            ]);

            

            return true;

        } catch (PDOException $e) {

            error_log("Audit trail error: " . $e->getMessage());

            return false;

        }

    }

    

    /**

     * Get audit trail entries with filtering and pagination

     *

     * @param array $filters Associative array of filters

     * @param int $page Page number

     * @param int $perPage Records per page

     * @return array Audit trail entries

     */

    public function getAuditTrails($filters = [], $page = 1, $perPage = 20) {

        try {

            $conditions = [];

            $params = [];

            $limitOffset = ($page - 1) * $perPage;

            

            // Build the WHERE clause based on filters

            if (!empty($filters['user_id'])) {

                $conditions[] = "user_id = :user_id";

                $params[':user_id'] = $filters['user_id'];

            }

            

            if (!empty($filters['action'])) {

                $conditions[] = "action = :action";

                $params[':action'] = $filters['action'];

            }

            

            if (!empty($filters['entity_type'])) {
                // Normalize and compare case-insensitively
                $normalizedEntityType = strtolower($filters['entity_type']);
                if ($normalizedEntityType === 'booking') {
                    $normalizedEntityType = 'bookings';
                }
                $conditions[] = "LOWER(entity_type) = :entity_type";
                $params[':entity_type'] = $normalizedEntityType;
            }

            

            if (!empty($filters['entity_id'])) {

                $conditions[] = "entity_id = :entity_id";

                $params[':entity_id'] = $filters['entity_id'];

            }

            

            if (!empty($filters['date_from'])) {

                $conditions[] = "created_at >= :date_from";

                $params[':date_from'] = $filters['date_from'] . ' 00:00:00';

            }

            

            if (!empty($filters['date_to'])) {

                $conditions[] = "created_at <= :date_to";

                $params[':date_to'] = $filters['date_to'] . ' 23:59:59';

            }

            

            // Text search filter across common fields
            if (!empty($filters['search'])) {
                $conditions[] = "(username LIKE :search OR action LIKE :search OR entity_type LIKE :search OR ip_address LIKE :search OR CAST(audit_id AS CHAR) LIKE :search OR CAST(entity_id AS CHAR) LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            // Construct the SQL query

            $whereClause = empty($conditions) ? "" : "WHERE " . implode(" AND ", $conditions);

            

            $sql = "

                SELECT * FROM audit_trails

                $whereClause

                ORDER BY created_at DESC

                LIMIT :limit OFFSET :offset

            ";

            

            $countSql = "

                SELECT COUNT(*) FROM audit_trails

                $whereClause

            ";

            

            // Get total count for pagination

            $countStatement = $this->pdo->prepare($countSql);

            foreach ($params as $key => $value) {

                $countStatement->bindValue($key, $value);

            }

            $countStatement->execute();

            $totalCount = $countStatement->fetchColumn();

            

            // Get paginated results

            $statement = $this->pdo->prepare($sql);

            foreach ($params as $key => $value) {

                $statement->bindValue($key, $value);

            }

            $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);

            $statement->bindValue(':offset', $limitOffset, PDO::PARAM_INT);

            $statement->execute();

            

            $results = $statement->fetchAll(PDO::FETCH_ASSOC);

            

            return [

                'total' => $totalCount,

                'page' => $page,

                'per_page' => $perPage,

                'records' => $results

            ];

        } catch (PDOException $e) {

            error_log("Audit trail error: " . $e->getMessage());

            return [

                'total' => 0,

                'page' => $page,

                'per_page' => $perPage,

                'records' => []

            ];

        }

    }

    

    /**

     * Get audit trail details for a specific entry

     *

     * @param int $auditId The ID of the audit trail entry

     * @return array|null The audit trail entry or null if not found

     */

    public function getAuditTrailById($auditId) {

        try {

            $statement = $this->pdo->prepare("SELECT * FROM audit_trails WHERE audit_id = :audit_id");

            $statement->execute([':audit_id' => $auditId]);

            return $statement->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Audit trail error: " . $e->getMessage());

            return null;

        }

    }

    

    /**

     * Get audit history for a specific entity

     *

     * @param string $entityType The type of entity

     * @param int $entityId The ID of the entity

     * @return array The audit history for the entity

     */

    public function getEntityHistory($entityType, $entityId) {

        try {

            $statement = $this->pdo->prepare("

                SELECT * FROM audit_trails 

                WHERE entity_type = :entity_type AND entity_id = :entity_id

                ORDER BY created_at DESC

            ");

            $statement->execute([

                ':entity_type' => $entityType, 

                ':entity_id' => $entityId

            ]);

            return $statement->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            error_log("Audit trail error: " . $e->getMessage());

            return [];

        }

    }

    

    /**

     * Get the client's IP address

     *

     * @return string The client's IP address

     */

    private function getClientIP() {

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {

            return $_SERVER['HTTP_CLIENT_IP'];

        }



        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

            // May contain multiple IPs, take the first one

            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            return trim($ipList[0]);

        }



        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    }



} 