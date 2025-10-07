<?php
require_once __DIR__ . "/../../models/admin/ReportModel.php";

class ReportController {
    private $reportModel;
    
    public function __construct() {
        $this->reportModel = new ReportModel();
        
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
    
    /**
     * Display the main reports page
     */
    public function index() {
        require_once __DIR__ . "/../../views/admin/reports.php";
    }
    
    /**
     * Generate booking summary report
     */
    public function getBookingSummary() {
        try {
            // Read JSON input from POST request
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true);
            
            $startDate = isset($data['start_date']) ? $data['start_date'] : null;
            $endDate = isset($data['end_date']) ? $data['end_date'] : null;
            
            $result = $this->reportModel->getBookingSummary($startDate, $endDate);
            
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Generate monthly booking trend report
     */
    public function getMonthlyBookingTrend() {
        try {
            // Read JSON input from POST request
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true);
            
            $startDate = isset($data['start_date']) ? $data['start_date'] : null;
            $endDate = isset($data['end_date']) ? $data['end_date'] : null;
            
            $result = $this->reportModel->getMonthlyBookingTrend($startDate, $endDate);
            
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Generate top destinations report
     */
    public function getTopDestinations() {
        try {
            // Read JSON input from POST request
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true);
            
            $limit = isset($data['limit']) ? intval($data['limit']) : 10;
            $startDate = isset($data['start_date']) ? $data['start_date'] : null;
            $endDate = isset($data['end_date']) ? $data['end_date'] : null;
            
            $result = $this->reportModel->getTopDestinations($limit, $startDate, $endDate);
            
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Generate payment method distribution report
     */
    public function getPaymentMethodDistribution() {
        try {
            // Read JSON input from POST request
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true);
            
            $startDate = isset($data['start_date']) ? $data['start_date'] : null;
            $endDate = isset($data['end_date']) ? $data['end_date'] : null;
            
            $result = $this->reportModel->getPaymentMethodDistribution($startDate, $endDate);
            
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Generate cancellation report
     */
    public function getCancellationReport() {
        try {
            // Read JSON input from POST request
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true);
            
            $startDate = isset($data['start_date']) ? $data['start_date'] : null;
            $endDate = isset($data['end_date']) ? $data['end_date'] : null;
            
            $result = $this->reportModel->getCancellationReport($startDate, $endDate);
            
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Generate client booking history report
     */
    public function getClientBookingHistory() {
        try {
            // Read JSON input from POST request
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true);
            
            $userId = isset($data['user_id']) ? intval($data['user_id']) : null;
            $startDate = isset($data['start_date']) ? $data['start_date'] : null;
            $endDate = isset($data['end_date']) ? $data['end_date'] : null;
            
            if (!$userId) {
                throw new Exception("User ID is required");
            }
            
            $result = $this->reportModel->getClientBookingHistory($userId, $startDate, $endDate);
            
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Generate detailed booking list
     */
    public function getDetailedBookingList() {
        try {
            // Read JSON input from POST request
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true);
            
            $filters = [
                'start_date' => isset($data['start_date']) ? $data['start_date'] : null,
                'end_date' => isset($data['end_date']) ? $data['end_date'] : null,
                'status' => isset($data['status']) ? $data['status'] : null,
                'payment_status' => isset($data['payment_status']) ? $data['payment_status'] : null,
                'search' => isset($data['search']) ? $data['search'] : null
            ];
            
            $page = isset($data['page']) ? intval($data['page']) : 1;
            $limit = isset($data['limit']) ? intval($data['limit']) : 20;
            
            $result = $this->reportModel->getDetailedBookingList($filters, $page, $limit);
            
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Generate financial summary report
     */
    public function getFinancialSummary() {
        try {
            // Read JSON input from POST request
            $jsonData = file_get_contents('php://input');
            $data = json_decode($jsonData, true);
            
            $startDate = isset($data['start_date']) ? $data['start_date'] : null;
            $endDate = isset($data['end_date']) ? $data['end_date'] : null;
            
            $result = $this->reportModel->getFinancialSummary($startDate, $endDate);
            
            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
} 