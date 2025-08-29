<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/admin/TestimonialManagementModel.php';
require_once __DIR__ . "/../AuditTrailTrait.php";

class TestimonialManagementController {
    use AuditTrailTrait;
    private $testimonialModel;

    public function __construct() {
        require_admin_auth();
        $this->testimonialModel = new TestimonialManagementModel();
    }

    /**
     * Display testimonial management page
     */
    public function testimonialManagement() {
        require_once __DIR__ . "/../../views/admin/testimonial_management.php";
    }

    /**
     * Get testimonials data via AJAX
     */
    public function getTestimonials() {
        header("Content-Type: application/json");

        $status = $_GET['status'] ?? 'all';
        $limit = (int)($_GET['limit'] ?? 50);
        $offset = (int)($_GET['offset'] ?? 0);

        $testimonials = $this->testimonialModel->getAllTestimonials($status, $limit, $offset);
        $stats = $this->testimonialModel->getTestimonialStats();

        echo json_encode([
            'success' => true,
            'testimonials' => $testimonials,
            'stats' => $stats
        ]);
    }

    /**
     * Get testimonial statistics
     */
    public function getStats() {
        header("Content-Type: application/json");
        
        $stats = $this->testimonialModel->getTestimonialStats();
        echo json_encode(['success' => true, 'stats' => $stats]);
    }

    /**
     * Approve a testimonial
     */
    public function approveTestimonial() {
        header("Content-Type: application/json");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $testimonial_id = $data['testimonial_id'] ?? null;

        if (!$testimonial_id) {
            echo json_encode(['success' => false, 'message' => 'Testimonial ID required']);
            return;
        }

        // Get testimonial details for audit log
        $testimonial = $this->testimonialModel->getTestimonialById($testimonial_id);
        
        $result = $this->testimonialModel->approveTestimonial($testimonial_id);

        if ($result) {
            // Log testimonial approval to audit trail
            $auditData = [
                'testimonial_id' => $testimonial_id,
                'client_name' => $testimonial['client_name'] ?? 'Unknown',
                'title' => $testimonial['title'] ?? '',
                'action_time' => date('Y-m-d H:i:s')
            ];
            $this->logAudit('testimonial_approved', 'testimonial', $testimonial_id, null, $auditData, $_SESSION['admin_id']);

            echo json_encode(['success' => true, 'message' => 'Testimonial approved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to approve testimonial']);
        }
    }

    /**
     * Reject a testimonial
     */
    public function rejectTestimonial() {
        header("Content-Type: application/json");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $testimonial_id = $data['testimonial_id'] ?? null;

        if (!$testimonial_id) {
            echo json_encode(['success' => false, 'message' => 'Testimonial ID required']);
            return;
        }

        // Get testimonial details for audit log
        $testimonial = $this->testimonialModel->getTestimonialById($testimonial_id);
        
        $result = $this->testimonialModel->rejectTestimonial($testimonial_id);

        if ($result) {
            // Log testimonial rejection to audit trail
            $auditData = [
                'testimonial_id' => $testimonial_id,
                'client_name' => $testimonial['client_name'] ?? 'Unknown',
                'title' => $testimonial['title'] ?? '',
                'action_time' => date('Y-m-d H:i:s')
            ];
            $this->logAudit('testimonial_rejected', 'testimonial', $testimonial_id, null, $auditData, $_SESSION['admin_id']);

            echo json_encode(['success' => true, 'message' => 'Testimonial rejected successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to reject testimonial']);
        }
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured() {
        header("Content-Type: application/json");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $testimonial_id = $data['testimonial_id'] ?? null;

        if (!$testimonial_id) {
            echo json_encode(['success' => false, 'message' => 'Testimonial ID required']);
            return;
        }

        // Get testimonial details for audit log
        $testimonial = $this->testimonialModel->getTestimonialById($testimonial_id);
        
        $result = $this->testimonialModel->toggleFeatured($testimonial_id);

        if ($result) {
            // Log featured toggle to audit trail
            $auditData = [
                'testimonial_id' => $testimonial_id,
                'client_name' => $testimonial['client_name'] ?? 'Unknown',
                'title' => $testimonial['title'] ?? '',
                'action_time' => date('Y-m-d H:i:s')
            ];
            $this->logAudit('testimonial_featured_toggled', 'testimonial', $testimonial_id, null, $auditData, $_SESSION['admin_id']);

            echo json_encode(['success' => true, 'message' => 'Featured status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update featured status. Make sure testimonial is approved first.']);
        }
    }

    /**
     * Delete a testimonial
     */
    public function deleteTestimonial() {
        header("Content-Type: application/json");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $testimonial_id = $data['testimonial_id'] ?? null;

        if (!$testimonial_id) {
            echo json_encode(['success' => false, 'message' => 'Testimonial ID required']);
            return;
        }

        // Get testimonial details for audit log before deletion
        $testimonial = $this->testimonialModel->getTestimonialById($testimonial_id);
        
        $result = $this->testimonialModel->deleteTestimonial($testimonial_id);

        if ($result) {
            // Log testimonial deletion to audit trail
            $auditData = [
                'testimonial_id' => $testimonial_id,
                'client_name' => $testimonial['client_name'] ?? 'Unknown',
                'title' => $testimonial['title'] ?? '',
                'content' => substr($testimonial['content'] ?? '', 0, 100),
                'action_time' => date('Y-m-d H:i:s')
            ];
            $this->logAudit('testimonial_deleted', 'testimonial', $testimonial_id, null, $auditData, $_SESSION['user_id']);

            echo json_encode(['success' => true, 'message' => 'Testimonial deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete testimonial']);
        }
    }

    /**
     * Bulk actions for testimonials
     */
    public function bulkAction() {
        header("Content-Type: application/json");

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $action = $data['action'] ?? null;
        $testimonial_ids = $data['testimonial_ids'] ?? [];

        if (!$action || empty($testimonial_ids)) {
            echo json_encode(['success' => false, 'message' => 'Action and testimonial IDs required']);
            return;
        }

        $result = false;
        $message = '';

        switch ($action) {
            case 'approve':
                $result = $this->testimonialModel->bulkApprove($testimonial_ids);
                $message = $result ? 'Testimonials approved successfully' : 'Failed to approve testimonials';
                $auditAction = 'testimonials_bulk_approved';
                break;
            case 'reject':
                $result = $this->testimonialModel->bulkReject($testimonial_ids);
                $message = $result ? 'Testimonials rejected successfully' : 'Failed to reject testimonials';
                $auditAction = 'testimonials_bulk_rejected';
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                return;
        }

        if ($result) {
            // Log bulk action to audit trail
            $auditData = [
                'action' => $action,
                'testimonial_count' => count($testimonial_ids),
                'testimonial_ids' => implode(',', $testimonial_ids),
                'action_time' => date('Y-m-d H:i:s')
            ];
            $this->logAudit($auditAction, 'testimonial', null, null, $auditData, $_SESSION['admin_id']);
        }

        echo json_encode(['success' => $result, 'message' => $message]);
    }

    /**
     * Get testimonial details for modal view
     */
    public function getTestimonialDetails() {
        header("Content-Type: application/json");

        $testimonial_id = $_GET['id'] ?? null;

        if (!$testimonial_id) {
            echo json_encode(['success' => false, 'message' => 'Testimonial ID required']);
            return;
        }

        $testimonial = $this->testimonialModel->getTestimonialById($testimonial_id);

        if ($testimonial) {
            echo json_encode(['success' => true, 'testimonial' => $testimonial]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Testimonial not found']);
        }
    }
}
?>