<?php
require_once __DIR__ . '/../../models/admin/SlideshowManagementModel.php';

class SlideshowController {
    private $slideshowModel;
    
    public function __construct() {
        $this->slideshowModel = new SlideshowManagementModel();
    }
    
    /**
     * Get active slideshow images for frontend
     */
    public function getActiveSlideshowImages() {
        try {
            // Set JSON content type
            header('Content-Type: application/json');
            
            // Get active slideshow images
            $images = $this->slideshowModel->getActiveSlideshowImages();
            
            if ($images === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to fetch slideshow images']);
                return;
            }
            
            // Return the images as JSON
            echo json_encode([
                'success' => true,
                'images' => $images
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal server error']);
        }
    }
}
?>
