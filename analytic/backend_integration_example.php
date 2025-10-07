<?php
// ==========================================
// BACKEND API INTEGRATION EXAMPLE (PHP)
// ==========================================

header('Content-Type: application/json');
require_once 'vendor/autoload.php';

use Google\Cloud\BigQuery\BigQueryClient;

class BusAnalyticsAPI {
    private $bigQuery;
    private $projectId;
    private $datasetId;
    
    public function __construct() {
        $this->projectId = 'your-project-id';
        $this->datasetId = 'your-dataset';
        $this->bigQuery = new BigQueryClient([
            'projectId' => $this->projectId,
            'keyFilePath' => 'path/to/service-account-key.json'
        ]);
    }
    
    // 1. GET DAILY REVENUE
    public function getDailyRevenue($days = 30) {
        $query = "
            SELECT 
                DATE(booking_date) as revenue_date,
                SUM(total_amount) as daily_revenue,
                COUNT(*) as booking_count
            FROM `{$this->projectId}.{$this->datasetId}.bookings`
            WHERE 
                status != 'cancelled'
                AND DATE(booking_date) >= DATE_SUB(CURRENT_DATE(), INTERVAL {$days} DAY)
            GROUP BY revenue_date
            ORDER BY revenue_date DESC
        ";
        
        return $this->executeQuery($query);
    }
    
    // 2. GET MOST-USED BUSES
    public function getMostUsedBuses($limit = 10) {
        $query = "
            SELECT 
                b.bus_id,
                bus.bus_name,
                COUNT(*) as booking_count,
                SUM(b.total_amount) as total_revenue,
                RANK() OVER (ORDER BY COUNT(*) DESC) as rank
            FROM `{$this->projectId}.{$this->datasetId}.bookings` b
            LEFT JOIN `{$this->projectId}.{$this->datasetId}.buses` bus 
                ON b.bus_id = bus.id
            WHERE b.status != 'cancelled'
            GROUP BY b.bus_id, bus.bus_name
            ORDER BY booking_count DESC
            LIMIT {$limit}
        ";
        
        return $this->executeQuery($query);
    }
    
    // 3. GET BOOKING FORECAST (from pre-trained ARIMA+ model)
    public function getBookingForecast($horizon = 30) {
        $query = "
            SELECT
                forecast_timestamp as forecast_date,
                forecast_value as predicted_bookings,
                prediction_interval_lower_bound as lower_bound,
                prediction_interval_upper_bound as upper_bound
            FROM ML.FORECAST(
                MODEL `{$this->projectId}.{$this->datasetId}.bookings_forecast_model`,
                STRUCT({$horizon} AS horizon, 0.95 AS confidence_level)
            )
            ORDER BY forecast_date
        ";
        
        return $this->executeQuery($query);
    }
    
    // 4. GET MAINTENANCE ALERTS
    public function getMaintenanceAlerts() {
        $query = "
            SELECT 
                bus.id,
                bus.bus_name,
                bus.plate_number,
                bus.current_mileage - bus.last_maintenance_mileage as mileage_since_maintenance,
                DATE_DIFF(CURRENT_DATE(), DATE(bus.last_maintenance_date), DAY) as days_since_maintenance,
                CASE 
                    WHEN bus.current_mileage - bus.last_maintenance_mileage > 10000 THEN 'URGENT'
                    WHEN DATE_DIFF(CURRENT_DATE(), DATE(bus.last_maintenance_date), DAY) > 180 THEN 'URGENT'
                    WHEN bus.current_mileage - bus.last_maintenance_mileage > 8000 THEN 'WARNING'
                    ELSE 'OK'
                END as status
            FROM `{$this->projectId}.{$this->datasetId}.buses` bus
            WHERE 
                bus.current_mileage - bus.last_maintenance_mileage > 8000
                OR DATE_DIFF(CURRENT_DATE(), DATE(bus.last_maintenance_date), DAY) > 150
            ORDER BY 
                CASE 
                    WHEN bus.current_mileage - bus.last_maintenance_mileage > 10000 THEN 1
                    WHEN DATE_DIFF(CURRENT_DATE(), DATE(bus.last_maintenance_date), DAY) > 180 THEN 1
                    ELSE 2
                END
        ";
        
        return $this->executeQuery($query);
    }
    
    // 5. GET CUSTOMER FEEDBACK SUMMARY
    public function getFeedbackSummary() {
        $query = "
            SELECT 
                fb.bus_id,
                bus.bus_name,
                AVG(fb.rating) as avg_rating,
                COUNT(fb.id) as feedback_count,
                COUNT(CASE WHEN fb.rating <= 2 THEN 1 END) as complaint_count
            FROM `{$this->projectId}.{$this->datasetId}.feedback` fb
            LEFT JOIN `{$this->projectId}.{$this->datasetId}.buses` bus ON fb.bus_id = bus.id
            GROUP BY fb.bus_id, bus.bus_name
            ORDER BY avg_rating DESC
        ";
        
        return $this->executeQuery($query);
    }
    
    // 6. GET TOP DESTINATIONS
    public function getTopDestinations($limit = 10) {
        $query = "
            SELECT 
                destination,
                COUNT(*) as booking_count,
                SUM(total_amount) as total_revenue
            FROM `{$this->projectId}.{$this->datasetId}.bookings`
            WHERE status != 'cancelled'
            GROUP BY destination
            ORDER BY booking_count DESC
            LIMIT {$limit}
        ";
        
        return $this->executeQuery($query);
    }
    
    // 7. GET PROMO EFFECTIVENESS
    public function getPromoEffectiveness() {
        $query = "
            SELECT 
                promo_code,
                COUNT(*) as usage_count,
                SUM(discount_amount) as total_discount,
                SUM(total_amount) as total_revenue,
                ROUND(SUM(total_amount) / NULLIF(SUM(discount_amount), 0), 2) as roi_ratio
            FROM `{$this->projectId}.{$this->datasetId}.bookings`
            WHERE promo_code IS NOT NULL AND status != 'cancelled'
            GROUP BY promo_code
            ORDER BY usage_count DESC
        ";
        
        return $this->executeQuery($query);
    }
    
    // Helper: Execute BigQuery
    private function executeQuery($query) {
        $jobConfig = $this->bigQuery->query($query);
        $queryResults = $this->bigQuery->runQuery($jobConfig);
        
        $results = [];
        foreach ($queryResults as $row) {
            $results[] = $row;
        }
        
        return $results;
    }
}

// ==========================================
// API ROUTES
// ==========================================

$api = new BusAnalyticsAPI();

$endpoint = $_GET['endpoint'] ?? 'revenue-daily';

switch ($endpoint) {
    case 'revenue-daily':
        $days = $_GET['days'] ?? 30;
        echo json_encode($api->getDailyRevenue($days));
        break;
        
    case 'buses-most-used':
        echo json_encode($api->getMostUsedBuses());
        break;
        
    case 'forecast-bookings':
        $horizon = $_GET['horizon'] ?? 30;
        echo json_encode($api->getBookingForecast($horizon));
        break;
        
    case 'maintenance-alerts':
        echo json_encode($api->getMaintenanceAlerts());
        break;
        
    case 'feedback-summary':
        echo json_encode($api->getFeedbackSummary());
        break;
        
    case 'destinations-top':
        echo json_encode($api->getTopDestinations());
        break;
        
    case 'promo-effectiveness':
        echo json_encode($api->getPromoEffectiveness());
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
}

// ==========================================
// USAGE EXAMPLES
// ==========================================
/*
GET /api/analytics.php?endpoint=revenue-daily&days=30
GET /api/analytics.php?endpoint=buses-most-used
GET /api/analytics.php?endpoint=forecast-bookings&horizon=30
GET /api/analytics.php?endpoint=maintenance-alerts
GET /api/analytics.php?endpoint=feedback-summary
GET /api/analytics.php?endpoint=destinations-top
GET /api/analytics.php?endpoint=promo-effectiveness
*/
?>

