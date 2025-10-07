<?php
// Test Analytics Integration
require_once __DIR__ . '/app/controllers/admin/AnalyticsController.php';

echo "<h1>Analytics Integration Test</h1>";

try {
    $analyticsController = new AnalyticsController();
    
    echo "<h2>Testing Analytics Controller</h2>";
    echo "<p>✅ AnalyticsController instantiated successfully</p>";
    
    // Test daily revenue
    echo "<h3>Testing Daily Revenue</h3>";
    $result = $analyticsController->getDailyRevenue('2024-01-01', '2024-12-31');
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
    
    // Test most used buses
    echo "<h3>Testing Most Used Buses</h3>";
    $result = $analyticsController->getMostUsedBuses('2024-01-01', '2024-12-31');
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
    
    // Test maintenance alerts
    echo "<h3>Testing Maintenance Alerts</h3>";
    $result = $analyticsController->getMaintenanceAlerts();
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
    
    echo "<h2>✅ All tests completed successfully!</h2>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
