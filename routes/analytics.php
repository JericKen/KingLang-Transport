<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/admin/AnalyticsController.php';

// Analytics API Routes
$analyticsController = new AnalyticsController();

// Get daily revenue trends
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'daily-revenue') {
    if (!headers_sent()) {
        if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $startDate = $input['start_date'] ?? date('Y-01-01');
    $endDate = $input['end_date'] ?? date('Y-m-d');
    
    $result = $analyticsController->getDailyRevenue($startDate, $endDate);
    echo json_encode($result);
    exit;
}

// Get most used buses
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'most-used-buses') {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $startDate = $input['start_date'] ?? date('Y-01-01');
    $endDate = $input['end_date'] ?? date('Y-m-d');
    
    $result = $analyticsController->getMostUsedBuses($startDate, $endDate);
    echo json_encode($result);
    exit;
}

// Get busiest booking days
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'busiest-days') {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $startDate = $input['start_date'] ?? date('Y-01-01');
    $endDate = $input['end_date'] ?? date('Y-m-d');
    
    $result = $analyticsController->getBusiestDays($startDate, $endDate);
    echo json_encode($result);
    exit;
}

// Get maintenance alerts
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'maintenance-alerts') {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    
    $result = $analyticsController->getMaintenanceAlerts();
    echo json_encode($result);
    exit;
}

// Get feedback analysis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'feedback-analysis') {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $startDate = $input['start_date'] ?? date('Y-01-01');
    $endDate = $input['end_date'] ?? date('Y-m-d');
    
    $result = $analyticsController->getFeedbackAnalysis($startDate, $endDate);
    echo json_encode($result);
    exit;
}

// Get booking forecast
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'booking-forecast') {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    
    $result = $analyticsController->getBookingForecast();
    echo json_encode($result);
    exit;
}

// Get customer behavior
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'customer-behavior') {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $startDate = $input['start_date'] ?? date('Y-01-01');
    $endDate = $input['end_date'] ?? date('Y-m-d');
    
    $result = $analyticsController->getCustomerBehavior($startDate, $endDate);
    echo json_encode($result);
    exit;
}

// Get marketing analytics
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'marketing-analytics') {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $startDate = $input['start_date'] ?? date('Y-01-01');
    $endDate = $input['end_date'] ?? date('Y-m-d');
    
    $result = $analyticsController->getMarketingAnalytics($startDate, $endDate);
    echo json_encode($result);
    exit;
}
?>
