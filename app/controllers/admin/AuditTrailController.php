<?php

class AuditTrailController {

    private $auditTrailModel;

    

    public function __construct() {

        require_once __DIR__ . '/../../models/admin/AuditTrailModel.php';

        $this->auditTrailModel = new AuditTrailModel();

        

        // Check if user is authenticated as admin

        require_admin_auth();

    }

    

    /**

     * Display the audit trail management page

     */

    public function index() {

        require_once __DIR__ . '/../../views/admin/audit_trail.php';

    }

    

    /**

     * Get paginated and filtered audit trails

     */

    public function getAuditTrails() {

        // Get filter and pagination parameters

        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

        $perPage = isset($_POST['per_page']) ? (int)$_POST['per_page'] : 20;

        

        // Build filters from request

        $filters = [

            'user_id' => $_POST['user_id'] ?? null,

            'action' => $_POST['action'] ?? null,

            'entity_type' => $_POST['entity_type'] ?? null,

            'entity_id' => $_POST['entity_id'] ?? null,

            'date_from' => $_POST['date_from'] ?? null,

            'date_to' => $_POST['date_to'] ?? null,

            'search' => $_POST['search'] ?? null,

        ];

        

        // Remove empty filters

        $filters = array_filter($filters);

        

        // Get audit trails based on filters

        $result = $this->auditTrailModel->getAuditTrails($filters, $page, $perPage);

        

        // Process and format data for display if needed

        foreach ($result['records'] as &$record) {

            // Convert JSON strings to arrays for display

            if (!empty($record['old_values'])) {

                $record['old_values'] = json_decode($record['old_values'], true);

            }

            if (!empty($record['new_values'])) {

                $record['new_values'] = json_decode($record['new_values'], true);

            }

            

            // Format date assuming the DB timestamp is already in Asia/Manila
            $record['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($record['created_at']));

        }

        

        // Return as JSON

        header('Content-Type: application/json');

        echo json_encode($result);

    }

    

    /**

     * Get details for a specific audit trail entry

     */

    public function getAuditDetails() {

        $auditId = isset($_POST['audit_id']) ? (int)$_POST['audit_id'] : 0;

        

        if ($auditId <= 0) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Invalid audit ID']);

            return;

        }

        

        $auditDetails = $this->auditTrailModel->getAuditTrailById($auditId);

        

        if ($auditDetails) {

            // Convert JSON strings to arrays for display

            if (!empty($auditDetails['old_values'])) {

                $auditDetails['old_values'] = json_decode($auditDetails['old_values'], true);

            }

            if (!empty($auditDetails['new_values'])) {

                $auditDetails['new_values'] = json_decode($auditDetails['new_values'], true);

            }

            

            // Format date assuming the DB timestamp is already in Asia/Manila
            $auditDetails['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($auditDetails['created_at']));

        }

        

        // Return as JSON

        header('Content-Type: application/json');

        echo json_encode($auditDetails ? $auditDetails : ['error' => 'Audit trail not found']);

    }

    

    /**

     * Get audit history for a specific entity

     */

    public function getEntityHistory() {

        $entityType = $_POST['entity_type'] ?? '';

        $entityId = isset($_POST['entity_id']) ? (int)$_POST['entity_id'] : 0;

        

        if (empty($entityType) || $entityId <= 0) {

            header('Content-Type: application/json');

            echo json_encode(['error' => 'Invalid entity type or ID']);

            return;

        }

        

        $history = $this->auditTrailModel->getEntityHistory($entityType, $entityId);

        

        // Process and format data for display

        foreach ($history as &$record) {

            // Convert JSON strings to arrays for display

            if (!empty($record['old_values'])) {

                $record['old_values'] = json_decode($record['old_values'], true);

            }

            if (!empty($record['new_values'])) {

                $record['new_values'] = json_decode($record['new_values'], true);

            }

            

            // Format date

            $record['created_at_formatted'] = date('Y-m-d H:i:s', strtotime($record['created_at']));

        }

        

        // Return as JSON

        header('Content-Type: application/json');

        echo json_encode($history);

    }

    

    /**

     * Export audit trails to CSV

     */

    public function exportAuditTrails() {

        // Build filters from request

        $filters = [

            'user_id' => $_GET['user_id'] ?? null,

            'action' => $_GET['action'] ?? null,

            'entity_type' => $_GET['entity_type'] ?? null,

            'entity_id' => $_GET['entity_id'] ?? null,

            'date_from' => $_GET['date_from'] ?? null,

            'date_to' => $_GET['date_to'] ?? null,

        ];

        

        // Remove empty filters

        $filters = array_filter($filters);

        

        // Get all audit trails for export (no pagination)

        $result = $this->auditTrailModel->getAuditTrails($filters, 1, 10000);

        $records = $result['records'];

        

        // Set up CSV headers

        header('Content-Type: text/csv');

        header('Content-Disposition: attachment; filename="audit_trails_export_' . date('Y-m-d') . '.csv"');

        

        // Create CSV file

        $output = fopen('php://output', 'w');

        

        // Add CSV headers

        fputcsv($output, [

            'Audit ID', 'User ID', 'Username', 'User Role', 'Action', 

            'Entity Type', 'Entity ID', 'IP Address', 'Date/Time'

        ]);

        

        // Add data rows

        foreach ($records as $record) {

            fputcsv($output, [

                $record['audit_id'],

                $record['user_id'],

                $record['username'],

                $record['user_role'],

                $record['action'],

                $record['entity_type'],

                $record['entity_id'],

                $record['ip_address'],

                date('Y-m-d H:i:s', strtotime($record['created_at']))

            ]);

        }

        

        fclose($output);

        exit;

    }

} 