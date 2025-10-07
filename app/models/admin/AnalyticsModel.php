<?php
require_once __DIR__ . '/../../../config/database.php';

class AnalyticsModel {
    private $conn;
    
    public function __construct() {
        global $pdo;
        $this->conn = $pdo;
    }
        
    // Get daily revenue trends
    public function getDailyRevenue($startDate, $endDate) {
        $query = "
            SELECT 
                DATE(b.booked_at) as revenue_date,
                COUNT(b.booking_id) as booking_count,
                COALESCE(SUM(bc.total_cost), 0) as daily_revenue
            FROM bookings b
            LEFT JOIN booking_costs bc ON b.booking_id = bc.booking_id
            WHERE b.status IN ('Completed', 'Confirmed')
                AND DATE(b.booked_at) BETWEEN ? AND ?
            GROUP BY DATE(b.booked_at)
            ORDER BY revenue_date DESC
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$startDate, $endDate]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [];
        foreach ($result as $row) {
            $data[] = [
                'revenue_date' => $row['revenue_date'],
                'booking_count' => (int)$row['booking_count'],
                'daily_revenue' => (float)$row['daily_revenue']
            ];
        }
        
        return $data;
    }
    
    // Get most used buses
    public function getMostUsedBuses($startDate, $endDate) {
        $query = "
            SELECT 
                bus.bus_id,
                bus.name as bus_name,
                COUNT(bb.booking_id) as booking_count,
                COALESCE(SUM(bc.total_cost), 0) as total_revenue
            FROM buses bus
            LEFT JOIN booking_buses bb ON bus.bus_id = bb.bus_id
            LEFT JOIN bookings b ON bb.booking_id = b.booking_id
            LEFT JOIN booking_costs bc ON b.booking_id = bc.booking_id
            WHERE b.status IN ('Completed', 'Confirmed')
                AND DATE(b.booked_at) BETWEEN ? AND ?
            GROUP BY bus.bus_id, bus.name
            ORDER BY booking_count DESC
            LIMIT 10
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$startDate, $endDate]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [];
        foreach ($result as $row) {
            $data[] = [
                'bus_id' => (int)$row['bus_id'],
                'bus_name' => $row['bus_name'],
                'booking_count' => (int)$row['booking_count'],
                'total_revenue' => (float)$row['total_revenue']
            ];
        }
        
        return $data;
    }
    
    // Get busiest booking days
    public function getBusiestDays($startDate, $endDate) {
        $query = "
            SELECT 
                DAYNAME(b.booked_at) as day_of_week,
                COUNT(b.booking_id) as booking_count,
                COALESCE(AVG(bc.total_cost), 0) as avg_revenue
            FROM bookings b
            LEFT JOIN booking_costs bc ON b.booking_id = bc.booking_id
            WHERE b.status IN ('Completed', 'Confirmed')
                AND DATE(b.booked_at) BETWEEN ? AND ?
            GROUP BY DAYNAME(b.booked_at), DAYOFWEEK(b.booked_at)
            ORDER BY DAYOFWEEK(b.booked_at)
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$startDate, $endDate]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [];
        foreach ($result as $row) {
            $data[] = [
                'day_of_week' => $row['day_of_week'],
                'booking_count' => (int)$row['booking_count'],
                'avg_revenue' => (float)$row['avg_revenue']
            ];
        }
        
        return $data;
    }
    
    // Get maintenance alerts
    public function getMaintenanceAlerts() {
        $query = "
            SELECT 
                bus_id,
                name as bus_name,
                last_maintenance,
                DATEDIFF(CURDATE(), last_maintenance) as days_since_maintenance,
                CASE
                    WHEN last_maintenance IS NULL OR DATEDIFF(CURDATE(), last_maintenance) > 180
                    THEN 'URGENT'
                    WHEN DATEDIFF(CURDATE(), last_maintenance) > 150
                    THEN 'WARNING'
                    ELSE 'OK'
                END as status
            FROM buses
            WHERE status = 'Active'
                AND (last_maintenance IS NULL OR DATEDIFF(CURDATE(), last_maintenance) > 150)
            ORDER BY days_since_maintenance DESC
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [];
        foreach ($result as $row) {
            $data[] = [
                'bus_id' => (int)$row['bus_id'],
                'bus_name' => $row['bus_name'],
                'last_maintenance' => $row['last_maintenance'],
                'days_since_maintenance' => (int)$row['days_since_maintenance'],
                'status' => $row['status']
            ];
        }
        
        return $data;
    }
    
    // Get customer feedback analysis (using testimonials table)
    public function getFeedbackAnalysis($startDate, $endDate) {
        // Check if testimonials table exists and has data
        $checkTable = "SHOW TABLES LIKE 'testimonials'";
        $stmt = $this->conn->prepare($checkTable);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($result) == 0) {
            // Return sample data if no testimonials table
            return [
                'avg_rating' => 4.2,
                'total_feedback' => 0,
                'low_ratings' => 0,
                'keywords' => []
            ];
        }
        
        // Get testimonials data with actual rating field
        $query = "
            SELECT 
                COUNT(*) as total_feedback,
                AVG(rating) as avg_rating,
                SUM(CASE WHEN rating <= 2 THEN 1 ELSE 0 END) as low_ratings
            FROM testimonials
            WHERE is_approved = 1
                AND created_at BETWEEN ? AND ?
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$startDate, $endDate]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'avg_rating' => (float)($row['avg_rating'] ?? 4.2),
            'total_feedback' => (int)($row['total_feedback'] ?? 0),
            'low_ratings' => (int)($row['low_ratings'] ?? 0),
            'keywords' => []
        ];
    }
    
    // Get booking forecast (improved trend analysis)
    public function getBookingForecast() {
        // Get last 30 days of booking data
        $query = "
            SELECT 
                DATE(b.booked_at) as booking_date,
                COUNT(b.booking_id) as daily_bookings
            FROM bookings b
            WHERE b.status IN ('Completed', 'Confirmed')
                AND DATE(b.booked_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(b.booked_at)
            ORDER BY booking_date
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $historicalData = [];
        foreach ($result as $row) {
            $historicalData[] = [
                'date' => $row['booking_date'],
                'bookings' => (int)$row['daily_bookings']
            ];
        }
        
        // Calculate trend and statistics
        $avgBookings = 0;
        $trend = 'stable';
        $volatility = 0;
        $slope = 0; // Initialize slope variable
        
        if (!empty($historicalData)) {
            $totalBookings = array_sum(array_column($historicalData, 'bookings'));
            $avgBookings = $totalBookings / count($historicalData);
            
            // Calculate trend (simple linear regression)
            $n = count($historicalData);
            $sumX = 0;
            $sumY = 0;
            $sumXY = 0;
            $sumXX = 0;
            
            for ($i = 0; $i < $n; $i++) {
                $x = $i;
                $y = $historicalData[$i]['bookings'];
                $sumX += $x;
                $sumY += $y;
                $sumXY += $x * $y;
                $sumXX += $x * $x;
            }
            
            $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
            $trend = $slope > 0.1 ? 'increasing' : ($slope < -0.1 ? 'decreasing' : 'stable');
            
            // Calculate volatility (standard deviation)
            $variance = 0;
            foreach ($historicalData as $data) {
                $variance += pow($data['bookings'] - $avgBookings, 2);
            }
            $volatility = sqrt($variance / $n);
        }
        
        // Generate forecast for next 7 days with better logic
        $forecast = [];
        for ($i = 1; $i <= 7; $i++) {
            $forecastDate = date('Y-m-d', strtotime("+$i days"));
            
            // Apply trend factor
            $trendFactor = 1 + ($slope * $i * 0.1); // Gradual trend application
            
            // Apply day-of-week pattern (weekends typically different)
            $dayOfWeek = date('N', strtotime($forecastDate));
            $dayFactor = 1.0;
            if ($dayOfWeek == 6 || $dayOfWeek == 7) { // Weekend
                $dayFactor = 0.8; // Slightly lower on weekends
            } elseif ($dayOfWeek == 1) { // Monday
                $dayFactor = 1.2; // Higher on Monday
            }
            
            // Calculate prediction with trend and day factors
            $basePrediction = $avgBookings * $trendFactor * $dayFactor;
            
            // Add some realistic variation
            $variation = $volatility * 0.5;
            $prediction = max(0, round($basePrediction + (rand(-100, 100) / 100) * $variation));
            
            // Calculate confidence bounds
            $confidence = $volatility * 1.5; // 1.5 standard deviations
            $lowerBound = max(0, round($prediction - $confidence));
            $upperBound = round($prediction + $confidence);
            
            $forecast[] = [
                'forecast_date' => $forecastDate,
                'predicted_bookings' => $prediction,
                'lower_bound' => $lowerBound,
                'upper_bound' => $upperBound,
                'confidence' => round((1 - ($confidence / max($prediction, 1))) * 100, 1)
            ];
        }
        
        return [
            'historical' => $historicalData,
            'forecast' => $forecast,
            'statistics' => [
                'avg_daily_bookings' => round($avgBookings, 1),
                'trend' => $trend,
                'volatility' => round($volatility, 1),
                'total_historical_days' => count($historicalData)
            ]
        ];
    }
    
    // Get customer behavior analysis
    public function getCustomerBehavior($startDate, $endDate) {
        // Peak booking hours
        $query = "
            SELECT 
                HOUR(b.booked_at) as booking_hour,
                COUNT(b.booking_id) as booking_count
            FROM bookings b
            WHERE b.status IN ('Completed', 'Confirmed')
                AND DATE(b.booked_at) BETWEEN ? AND ?
            GROUP BY HOUR(b.booked_at)
            ORDER BY booking_hour
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$startDate, $endDate]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $peakHours = [];
        foreach ($result as $row) {
            $peakHours[] = [
                'hour' => (int)$row['booking_hour'],
                'count' => (int)$row['booking_count']
            ];
        }
        
        // Top destinations
        $query2 = "
            SELECT 
                b.destination,
                COUNT(b.booking_id) as booking_count,
                COALESCE(SUM(bc.total_cost), 0) as total_revenue
            FROM bookings b
            LEFT JOIN booking_costs bc ON b.booking_id = bc.booking_id
            WHERE b.status IN ('Completed', 'Confirmed')
                AND DATE(b.booked_at) BETWEEN ? AND ?
            GROUP BY b.destination
            ORDER BY booking_count DESC
            LIMIT 10
        ";
        
        $stmt2 = $this->conn->prepare($query2);
        $stmt2->execute([$startDate, $endDate]);
        $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        $topDestinations = [];
        foreach ($result2 as $row) {
            $topDestinations[] = [
                'destination' => $row['destination'],
                'booking_count' => (int)$row['booking_count'],
                'total_revenue' => (float)$row['total_revenue']
            ];
        }
        
        return [
            'peak_hours' => $peakHours,
            'top_destinations' => $topDestinations
        ];
    }
    
    // Get marketing analytics
    public function getMarketingAnalytics($startDate, $endDate) {
        // Client type analysis
        $query = "
            SELECT 
                b.created_by as client_type,
                COUNT(b.booking_id) as booking_count,
                COALESCE(SUM(bc.total_cost), 0) as total_revenue
            FROM bookings b
            LEFT JOIN booking_costs bc ON b.booking_id = bc.booking_id
            WHERE b.status IN ('Completed', 'Confirmed')
                AND DATE(b.booked_at) BETWEEN ? AND ?
            GROUP BY b.created_by
        ";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$startDate, $endDate]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $clientTypes = [];
        foreach ($result as $row) {
            $clientTypes[] = [
                'client_type' => $row['client_type'],
                'booking_count' => (int)$row['booking_count'],
                'total_revenue' => (float)$row['total_revenue']
            ];
        }
        
        return [
            'client_types' => $clientTypes
        ];
    }
}
?>
