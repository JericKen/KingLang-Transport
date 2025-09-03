<?php
require_once __DIR__ . '/../../models/admin/PastTripsModel.php';

class PastTripsController {
    private $model;

    public function __construct() {
        $this->model = new PastTripsModel();
    }

    public function getActivePastTrips() {
        header('Content-Type: application/json');
        try {
            $images = $this->model->getActiveImages();
            if ($images === false) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to fetch past trip images']);
                return;
            }
            echo json_encode(['success' => true, 'images' => $images]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Internal server error']);
        }
    }
}
?>


