<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/admin/PastTripsModel.php';
require_once __DIR__ . "/../AuditTrailTrait.php";

class PastTripsController {
    use AuditTrailTrait;
    private $model;

    public function __construct() {
        require_admin_auth();
        $this->model = new PastTripsModel();
    }

    public function list() {
        header("Content-Type: application/json");
        $images = $this->model->getAllImages();
        $stats = $this->model->getStats();
        echo json_encode(['success' => true, 'images' => $images, 'stats' => $stats]);
    }

    public function upload() {
        header("Content-Type: application/json");
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No image uploaded or upload error']);
            return;
        }
        $file = $_FILES['image'];
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.']);
            return;
        }
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File size too large. Maximum size is 5MB.']);
            return;
        }
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'pasttrip_' . time() . '_' . uniqid() . '.' . $extension;
        $uploadDir = __DIR__ . '/../../../public/images/past-trips/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $filepath = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
            return;
        }
        $imageData = [
            'filename' => $filename,
            'original_filename' => $file['name'],
            'title' => $title,
            'description' => $description,
            'display_order' => $displayOrder,
            'is_active' => 1,
            'created_by' => $_SESSION['user_id'] ?? null
        ];
        $imageId = $this->model->addImage($imageData);
        if ($imageId) {
            $this->logAudit('CREATE', 'Past Trip Image', $imageId, null, $imageData);
            echo json_encode(['success' => true, 'message' => 'Image uploaded successfully', 'image_id' => $imageId]);
        } else {
            if (file_exists($filepath)) { unlink($filepath); }
            echo json_encode(['success' => false, 'message' => 'Failed to save image information to database']);
        }
    }

    public function update() {
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
        $success = $this->model->updateImage($imageId, $updateData);
        if ($success) {
            $this->logAudit('UPDATE', 'Past Trip Image', $imageId, null, $updateData);
            echo json_encode(['success' => true, 'message' => 'Image updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update image']);
        }
    }

    public function delete() {
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
        $success = $this->model->deleteImage($imageId);
        if ($success) {
            $this->logAudit('DELETE', 'Past Trip Image', $imageId, null, null);
            echo json_encode(['success' => true, 'message' => 'Image deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete image']);
        }
    }

    public function toggleStatus() {
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
        $success = $this->model->toggleStatus($imageId);
        if ($success) {
            $this->logAudit('UPDATE', 'Past Trip Image', $imageId, null, ['toggle' => 'is_active']);
            echo json_encode(['success' => true, 'message' => 'Image status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update image status']);
        }
    }

    public function updateOrder() {
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
        $success = $this->model->updateDisplayOrder($data['orders']);
        if ($success) {
            $this->logAudit('UPDATE', 'Past Trip Images', 0, null, $data['orders']);
            echo json_encode(['success' => true, 'message' => 'Display order updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update display order']);
        }
    }
}
?>


