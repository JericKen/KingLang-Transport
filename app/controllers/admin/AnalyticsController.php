<?php
require_once __DIR__ . '/../../models/admin/AnalyticsModel.php';

class AnalyticsController {
    private $analyticsModel;
    
    public function __construct() {
        $this->analyticsModel = new AnalyticsModel();
    }
    
    // Get daily revenue trends
    public function getDailyRevenue($startDate, $endDate) {
        try {
            $data = $this->analyticsModel->getDailyRevenue($startDate, $endDate);
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Get most used buses
    public function getMostUsedBuses($startDate, $endDate) {
        try {
            $data = $this->analyticsModel->getMostUsedBuses($startDate, $endDate);
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Get busiest booking days
    public function getBusiestDays($startDate, $endDate) {
        try {
            $data = $this->analyticsModel->getBusiestDays($startDate, $endDate);
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Get maintenance alerts
    public function getMaintenanceAlerts() {
        try {
            $data = $this->analyticsModel->getMaintenanceAlerts();
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Get customer feedback analysis
    public function getFeedbackAnalysis($startDate, $endDate) {
        try {
            $data = $this->analyticsModel->getFeedbackAnalysis($startDate, $endDate);
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Get booking forecast (simplified version)
    public function getBookingForecast() {
        try {
            $data = $this->analyticsModel->getBookingForecast();
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Get customer behavior analysis
    public function getCustomerBehavior($startDate, $endDate) {
        try {
            $data = $this->analyticsModel->getCustomerBehavior($startDate, $endDate);
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    // Get marketing analytics
    public function getMarketingAnalytics($startDate, $endDate) {
        try {
            $data = $this->analyticsModel->getMarketingAnalytics($startDate, $endDate);
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>
