<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/client/TestimonialModel.php';
require_once __DIR__ . "/../AuditTrailTrait.php";

class TestimonialController {
    use AuditTrailTrait;
    private $testimonialModel;

    public function __construct() {
        $this->testimonialModel = new TestimonialModel();
    }

    /**
     * Display testimonial submission form
     */
    public function testimonialForm() {
        require_client_auth();
        
        $user_id = $_SESSION['user_id'];
        $eligibleBookings = $this->testimonialModel->getBookingsEligibleForTestimonial($user_id);
        $userTestimonials = $this->testimonialModel->getUserTestimonials($user_id);
        
        require_once __DIR__ . "/../../views/client/testimonial_form.php";
    }

    /**
     * Handle testimonial submission via AJAX
     */
    public function submitTestimonial() {
        header("Content-Type: application/json");
        
        // Check authentication
        if (!is_client_authenticated()) {
            echo json_encode(["success" => false, "message" => "Authentication required."]);
            return;
        }

        // Validate request method
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            echo json_encode(["success" => false, "message" => "Invalid request method."]);
            return;
        }

        // Get and validate input data
        $data = json_decode(file_get_contents("php://input"), true);
        
        $booking_id = isset($data['booking_id']) ? (int)$data['booking_id'] : 0;
        $rating = isset($data['rating']) ? (int)$data['rating'] : 0;
        $title = isset($data['title']) ? trim($data['title']) : '';
        $content = isset($data['content']) ? trim($data['content']) : '';
        $user_id = $_SESSION['user_id'];

        // Validate required fields
        if (empty($booking_id) || empty($rating) || empty($title) || empty($content)) {
            echo json_encode(["success" => false, "message" => "All fields are required."]);
            return;
        }

        // Validate rating range
        if ($rating < 1 || $rating > 5) {
            echo json_encode(["success" => false, "message" => "Rating must be between 1 and 5."]);
            return;
        }

        // Validate title length
        if (strlen($title) > 100) {
            echo json_encode(["success" => false, "message" => "Title must be 100 characters or less."]);
            return;
        }

        // Validate content length
        if (strlen($content) > 1000) {
            echo json_encode(["success" => false, "message" => "Review content must be 1000 characters or less."]);
            return;
        }

        // Submit testimonial
        $result = $this->testimonialModel->submitTestimonial($user_id, $booking_id, $rating, $title, $content);

        // Log testimonial submission to audit trail
        if ($result['success']) {
            $testimonialData = [
                'booking_id' => $booking_id,
                'rating' => $rating,
                'title' => $title,
                'content_length' => strlen($content),
                'submission_time' => date('Y-m-d H:i:s')
            ];
            $this->logAudit('testimonial_submitted', 'testimonial', $user_id, null, $testimonialData, $user_id);
        }

        echo json_encode($result);
    }

    /**
     * API endpoint to get eligible bookings for testimonials
     */
    public function getEligibleBookings() {
        header("Content-Type: application/json");
        
        if (!is_client_authenticated()) {
            echo json_encode(["success" => false, "message" => "Authentication required."]);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $bookings = $this->testimonialModel->getBookingsEligibleForTestimonial($user_id);
        
        echo json_encode(["success" => true, "bookings" => $bookings]);
    }

    /**
     * API endpoint to get user's submitted testimonials
     */
    public function getUserTestimonials() {
        header("Content-Type: application/json");
        
        if (!is_client_authenticated()) {
            echo json_encode(["success" => false, "message" => "Authentication required."]);
            return;
        }

        $user_id = $_SESSION['user_id'];
        $testimonials = $this->testimonialModel->getUserTestimonials($user_id);
        
        echo json_encode(["success" => true, "testimonials" => $testimonials]);
    }

    /**
     * API endpoint to get approved testimonials for public display
     */
    public function getApprovedTestimonials() {
        header("Content-Type: application/json");
        
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $featured_first = isset($_GET['featured']) ? (bool)$_GET['featured'] : true;
        
        $testimonials = $this->testimonialModel->getApprovedTestimonials($limit, $featured_first);
        $ratings = $this->testimonialModel->getAverageRating();
        
        echo json_encode([
            "success" => true, 
            "testimonials" => $testimonials,
            "average_rating" => $ratings['average'],
            "total_reviews" => $ratings['total']
        ]);
    }
}
?>