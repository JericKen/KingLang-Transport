<?php
trait AuditTrailTrait {
    /**
     * Log an audit action
     * 
     * @param string $action The action performed (e.g., "create", "update", "delete")
     * @param string $entityType The type of entity affected (e.g., "booking", "user", "payment")
     * @param int $entityId The ID of the entity affected
     * @param array|null $oldValues The old values before the change (can be null for create actions)
     * @param array|null $newValues The new values after the change (can be null for delete actions)
     * @return bool Success or failure
     */
    protected function logAudit($action, $entityType, $entityId, $oldValues = null, $newValues = null, $userId = null) {
        require_once __DIR__ . '/../models/admin/AuditTrailModel.php';
        $auditTrailModel = new AuditTrailModel();
        return $auditTrailModel->logAction($action, $entityType, $entityId, $oldValues, $newValues, $userId);
    }
    
    /**
     * Get entity data before update for auditing purposes
     * 
     * @param string $table The table name
     * @param string $idColumn The ID column name
     * @param int $id The ID value
     * @return array|null The entity data or null if not found
     */
    protected function getEntityBeforeUpdate($table, $idColumn, $id) {
        global $pdo;
        try {
            $statement = $pdo->prepare("SELECT * FROM {$table} WHERE {$idColumn} = :id");
            $statement->execute([':id' => $id]);
            return ($table == 'trip_distances' | $table == 'booking_stops') 
                ? $statement->fetchAll(PDO::FETCH_ASSOC) 
                : $statement->fetch(PDO::FETCH_ASSOC) ?? [];
        } catch (PDOException $e) {
            error_log("Error getting entity data: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Compare old and new values to determine what changed
     * 
     * @param array $oldValues The old values
     * @param array $newValues The new values
     * @return array The changes (keys that are different)
     */
    protected function getChanges($oldValues, $newValues) {
        $changes = [];
        
        foreach ($newValues as $key => $value) {
            // Skip if the key doesn't exist in old values or if the values are identical
            if (!isset($oldValues[$key]) || $oldValues[$key] === $value) {
                continue;
            }
            
            $changes[$key] = [
                'old' => $oldValues[$key],
                'new' => $value
            ];
        }
        
        return $changes;
    }
} 