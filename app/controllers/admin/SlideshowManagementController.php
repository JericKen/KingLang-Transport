<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/admin/SlideshowManagementModel.php';
require_once __DIR__ . "/../AuditTrailTrait.php";

class SlideshowManagementController {
    use AuditTrailTrait;
    private $slideshowModel;
    
    public function __construct() {
        require_admin_auth();
        $this->slideshowModel = new SlideshowManagementModel();
    }
    
    /**
     * Display slideshow management page
     */
    public function slideshowManagement() {
        $stats = $this->slideshowModel->getSlideshowStats();
        require_once __DIR__ . "/../../views/admin/slideshow_management.php";
    }
    
    /**
     * Get slideshow images data via AJAX
     */
    public function getSlideshowImages() {
        header("Content-Type: application/json");
        
        $images = $this->slideshowModel->getAllSlideshowImages();
        $stats = $this->slideshowModel->getSlideshowStats();
        
        echo json_encode([
            'success' => true,
            'images' => $images,
            'stats' => $stats
        ]);
    }
    
    /**
     * Upload new slideshow image
     */
    public function uploadSlideshowImage() {
        header("Content-Type: application/json");
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        try {
            // Check if file was uploaded
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'No image uploaded or upload error']);
                return;
            }
            
            $file = $_FILES['image'];
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $displayOrder = (int)($_POST['display_order'] ?? 0);
            
            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
                return;
            }
            
            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                echo json_encode(['success' => false, 'message' => 'File size too large. Maximum size is 5MB.']);
                return;
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'slideshow_' . time() . '_' . uniqid() . '.' . $extension;
            
            // Upload directory
            $uploadDir = __DIR__ . '/../../../public/images/slideshow/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $filepath = $uploadDir . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
                return;
            }
            
            // Save to database
            $imageData = [
                'filename' => $filename,
                'original_filename' => $file['name'],
                'title' => $title,
                'description' => $description,
                'display_order' => $displayOrder,
                'is_active' => 1,
                'created_by' => $_SESSION['admin_id'] ?? null
            ];
            
            $imageId = $this->slideshowModel->addSlideshowImage($imageData);
            
            if ($imageId) {
                // Log audit trail
                $this->logAudit(
                    'CREATE',
                    'Slideshow Image',
                    $imageId,
                    null,
                    $imageData,
                    $_SESSION['admin_id'] ?? null
                );
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Image uploaded successfully',
                    'image_id' => $imageId
                ]);
            } else {
                // Remove uploaded file if database save failed
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
                echo json_encode(['success' => false, 'message' => 'Failed to save image information to database']);
            }
            
        } catch (Exception $e) {
            error_log("Error uploading slideshow image: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred while uploading the image']);
        }
    }
    
    /**
     * Update slideshow image
     */
    public function updateSlideshowImage() {
        header("Content-Type: application/json");
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'Image ID is required']);
            return;
        }
        
        $imageId = (int)$data['id'];
        $updateData = [
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? '',
            'display_order' => (int)($data['display_order'] ?? 0),
            'is_active' => (int)($data['is_active'] ?? 1)
        ];
        
        $success = $this->slideshowModel->updateSlideshowImage($imageId, $updateData);
        
        if ($success) {
            // Log audit trail
            $this->logAudit(
                'UPDATE',
                'Slideshow Image',
                $imageId,
                null,
                $updateData,
                $_SESSION['admin_id'] ?? null
            );
            
            echo json_encode(['success' => true, 'message' => 'Image updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update image']);
        }
    }
    
    /**
     * Delete slideshow image
     */
    public function deleteSlideshowImage() {
        header("Content-Type: application/json");
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'Image ID is required']);
            return;
        }
        
        $imageId = (int)$data['id'];
        
        // Get image info before deletion for audit trail
        $image = $this->slideshowModel->getSlideshowImageById($imageId);
        
        $success = $this->slideshowModel->deleteSlideshowImage($imageId);
        
        if ($success) {
            // Log audit trail
            $this->logAudit(
                'DELETE',
                'Slideshow Image',
                $imageId,
                $image,
                null,
                $_SESSION['admin_id'] ?? null
            );
            
            echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete image']);
        }
    }
    
    /**
     * Toggle slideshow image status
     */
    public function toggleSlideshowImageStatus() {
        header("Content-Type: application/json");
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'Image ID is required']);
            return;
        }
        
        $imageId = (int)$data['id'];
        
        $success = $this->slideshowModel->toggleSlideshowImageStatus($imageId);
        
        if ($success) {
            // Get updated image info for audit trail
            $image = $this->slideshowModel->getSlideshowImageById($imageId);
            $status = $image['is_active'] ? 'activated' : 'deactivated';
            
            // Log audit trail
            $this->logAudit(
                'UPDATE',
                'Slideshow Image',
                $imageId,
                null,
                $image,
                $_SESSION['admin_id'] ?? null
            );
            
            echo json_encode(['success' => true, 'message' => 'Image status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update image status']);
        }
    }
    
    /**
     * Update display order
     */
    public function updateDisplayOrder() {
        header("Content-Type: application/json");
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['orders']) || !is_array($data['orders'])) {
            echo json_encode(['success' => false, 'message' => 'Display orders are required']);
            return;
        }
        
        $success = $this->slideshowModel->updateDisplayOrder($data['orders']);
        
        if ($success) {
            // Log audit trail
            $this->logAudit(
                'UPDATE',
                'Slideshow Images',
                0,
                null,
                $data['orders'],
                $_SESSION['admin_id'] ?? null
            );
            
            echo json_encode(['success' => true, 'message' => 'Display order updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update display order']);
        }
    }
    
    /**
     * Get slideshow statistics
     */
    public function getSlideshowStats() {
        header("Content-Type: application/json");
        
        $stats = $this->slideshowModel->getSlideshowStats();
        echo json_encode(['success' => true, 'stats' => $stats]);
    }
}
?>
