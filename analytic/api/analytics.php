<?php
// ==========================================
// WORKING API FOR DASHBOARD DATA
// ==========================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// For now, return sample data (replace with BigQuery later)
function getSampleData($endpoint) {
    switch ($endpoint) {
        case 'revenue-daily':
            return [
                ['revenue_date' => '2025-01-01', 'daily_revenue' => 15000, 'booking_count' => 25],
                ['revenue_date' => '2025-01-02', 'daily_revenue' => 18500, 'booking_count' => 32],
                ['revenue_date' => '2025-01-03', 'daily_revenue' => 12300, 'booking_count' => 18],
                ['revenue_date' => '2025-01-04', 'daily_revenue' => 22100, 'booking_count' => 35],
                ['revenue_date' => '2025-01-05', 'daily_revenue' => 19800, 'booking_count' => 28],
                ['revenue_date' => '2025-01-06', 'daily_revenue' => 25400, 'booking_count' => 42],
                ['revenue_date' => '2025-01-07', 'daily_revenue' => 21200, 'booking_count' => 38]
            ];
            
        case 'buses-most-used':
            return [
                ['bus_id' => 1, 'bus_name' => 'Bus Alpha', 'booking_count' => 145, 'total_revenue' => 2900000],
                ['bus_id' => 2, 'bus_name' => 'Bus Beta', 'booking_count' => 132, 'total_revenue' => 2640000],
                ['bus_id' => 3, 'bus_name' => 'Bus Gamma', 'booking_count' => 128, 'total_revenue' => 2560000],
                ['bus_id' => 4, 'bus_name' => 'Bus Delta', 'booking_count' => 115, 'total_revenue' => 2300000],
                ['bus_id' => 5, 'bus_name' => 'Bus Epsilon', 'booking_count' => 98, 'total_revenue' => 1960000]
            ];
            
        case 'forecast-bookings':
            return [
                ['forecast_date' => '2025-01-08', 'predicted_bookings' => 115, 'lower_bound' => 105, 'upper_bound' => 125],
                ['forecast_date' => '2025-01-09', 'predicted_bookings' => 118, 'lower_bound' => 104, 'upper_bound' => 132],
                ['forecast_date' => '2025-01-10', 'predicted_bookings' => 122, 'lower_bound' => 106, 'upper_bound' => 138],
                ['forecast_date' => '2025-01-11', 'predicted_bookings' => 125, 'lower_bound' => 108, 'upper_bound' => 142],
                ['forecast_date' => '2025-01-12', 'predicted_bookings' => 130, 'lower_bound' => 112, 'upper_bound' => 148]
            ];
            
        case 'maintenance-alerts':
            return [
                ['bus_id' => 5, 'bus_name' => 'Bus Echo', 'mileage_since_maintenance' => 12000, 'days_since_maintenance' => 195, 'status' => 'URGENT'],
                ['bus_id' => 8, 'bus_name' => 'Bus Hotel', 'mileage_since_maintenance' => 9500, 'days_since_maintenance' => 210, 'status' => 'URGENT'],
                ['bus_id' => 3, 'bus_name' => 'Bus Gamma', 'mileage_since_maintenance' => 8200, 'days_since_maintenance' => 165, 'status' => 'WARNING']
            ];
            
        case 'feedback-summary':
            return [
                ['bus_id' => 1, 'bus_name' => 'Bus Alpha', 'avg_rating' => 4.7, 'feedback_count' => 45, 'complaint_count' => 2],
                ['bus_id' => 2, 'bus_name' => 'Bus Beta', 'avg_rating' => 4.5, 'feedback_count' => 38, 'complaint_count' => 3],
                ['bus_id' => 3, 'bus_name' => 'Bus Gamma', 'avg_rating' => 4.3, 'feedback_count' => 42, 'complaint_count' => 5],
                ['bus_id' => 4, 'bus_name' => 'Bus Delta', 'avg_rating' => 4.2, 'feedback_count' => 35, 'complaint_count' => 4]
            ];
            
        case 'forecast-bookings':
            return [
                ['forecast_date' => '2025-01-08', 'predicted_bookings' => 115, 'lower_bound' => 105, 'upper_bound' => 125],
                ['forecast_date' => '2025-01-09', 'predicted_bookings' => 118, 'lower_bound' => 104, 'upper_bound' => 132],
                ['forecast_date' => '2025-01-10', 'predicted_bookings' => 122, 'lower_bound' => 106, 'upper_bound' => 138],
                ['forecast_date' => '2025-01-11', 'predicted_bookings' => 125, 'lower_bound' => 108, 'upper_bound' => 142],
                ['forecast_date' => '2025-01-12', 'predicted_bookings' => 130, 'lower_bound' => 112, 'upper_bound' => 148],
                ['forecast_date' => '2025-01-13', 'predicted_bookings' => 128, 'lower_bound' => 110, 'upper_bound' => 146],
                ['forecast_date' => '2025-01-14', 'predicted_bookings' => 135, 'lower_bound' => 115, 'upper_bound' => 155],
                ['forecast_date' => '2025-01-15', 'predicted_bookings' => 140, 'lower_bound' => 120, 'upper_bound' => 160],
                ['forecast_date' => '2025-01-16', 'predicted_bookings' => 138, 'lower_bound' => 118, 'upper_bound' => 158],
                ['forecast_date' => '2025-01-17', 'predicted_bookings' => 145, 'lower_bound' => 125, 'upper_bound' => 165]
            ];
            
        case 'destinations-top':
            return [
                ['destination' => 'Manila', 'booking_count' => 245, 'total_revenue' => 4900000],
                ['destination' => 'Baguio', 'booking_count' => 198, 'total_revenue' => 3960000],
                ['destination' => 'Tagaytay', 'booking_count' => 165, 'total_revenue' => 3300000],
                ['destination' => 'Batangas', 'booking_count' => 142, 'total_revenue' => 2840000],
                ['destination' => 'Subic', 'booking_count' => 128, 'total_revenue' => 2560000]
            ];
            
        case 'busiest-days':
            return [
                ['day_of_week' => 'Monday', 'booking_count' => 145, 'avg_revenue' => 2900000],
                ['day_of_week' => 'Tuesday', 'booking_count' => 132, 'avg_revenue' => 2640000],
                ['day_of_week' => 'Wednesday', 'booking_count' => 128, 'avg_revenue' => 2560000],
                ['day_of_week' => 'Thursday', 'booking_count' => 156, 'avg_revenue' => 3120000],
                ['day_of_week' => 'Friday', 'booking_count' => 189, 'avg_revenue' => 3780000],
                ['day_of_week' => 'Saturday', 'booking_count' => 201, 'avg_revenue' => 4020000],
                ['day_of_week' => 'Sunday', 'booking_count' => 165, 'avg_revenue' => 3300000]
            ];
            
        case 'bus-ratings':
            return [
                ['bus_name' => 'Bus Alpha', 'avg_rating' => 4.7, 'total_ratings' => 145],
                ['bus_name' => 'Bus Beta', 'avg_rating' => 4.5, 'total_ratings' => 132],
                ['bus_name' => 'Bus Gamma', 'avg_rating' => 4.3, 'total_ratings' => 128],
                ['bus_name' => 'Bus Delta', 'avg_rating' => 4.6, 'total_ratings' => 156],
                ['bus_name' => 'Bus Epsilon', 'avg_rating' => 4.2, 'total_ratings' => 98]
            ];
            
        case 'feedback-keywords':
            return [
                ['keyword' => 'Clean', 'frequency' => 245],
                ['keyword' => 'Comfortable', 'frequency' => 198],
                ['keyword' => 'On-time', 'frequency' => 165],
                ['keyword' => 'Friendly', 'frequency' => 142],
                ['keyword' => 'Safe', 'frequency' => 128],
                ['keyword' => 'Spacious', 'frequency' => 98],
                ['keyword' => 'Professional', 'frequency' => 87]
            ];
            
        case 'monthly-income':
            return [
                ['month' => '2024-07', 'total_income' => 2800000, 'booking_count' => 145],
                ['month' => '2024-08', 'total_income' => 3200000, 'booking_count' => 168],
                ['month' => '2024-09', 'total_income' => 2900000, 'booking_count' => 152],
                ['month' => '2024-10', 'total_income' => 3500000, 'booking_count' => 189],
                ['month' => '2024-11', 'total_income' => 3800000, 'booking_count' => 205],
                ['month' => '2024-12', 'total_income' => 4200000, 'booking_count' => 225],
                ['month' => '2025-01', 'total_income' => 3900000, 'booking_count' => 198]
            ];
            
        case 'booking-status':
            return [
                ['status' => 'Completed', 'count' => 1048, 'percentage' => 84.2],
                ['status' => 'Cancelled', 'count' => 142, 'percentage' => 11.4],
                ['status' => 'Pending', 'count' => 55, 'percentage' => 4.4]
            ];
            
        default:
            return ['error' => 'Endpoint not found'];
    }
}

// Get the endpoint from URL parameter
$endpoint = $_GET['endpoint'] ?? 'revenue-daily';

// Return sample data
$data = getSampleData($endpoint);

// Return JSON response
echo json_encode($data);
?>
