<?php
require_once __DIR__ . "/../../../config/database.php";

class TestimonialModel {
    private $conn;

    public function __construct() {
        global $pdo;
        $this->conn = $pdo;
    }

    /**
     * Submit a new testimonial
     */
    public function submitTestimonial($user_id, $booking_id, $rating, $title, $content) {
        try {
            // Check if user already submitted testimonial for this booking
            if ($this->hasUserSubmittedTestimonial($user_id, $booking_id)) {
                return ["success" => false, "message" => "You have already submitted a testimonial for this trip."];
            }

            // Verify the booking belongs to the user and is completed
            if (!$this->isValidBookingForTestimonial($user_id, $booking_id)) {
                return ["success" => false, "message" => "Invalid booking or trip not completed yet."];
            }

            $stmt = $this->conn->prepare("
                INSERT INTO testimonials (user_id, booking_id, rating, title, content) 
                VALUES (:user_id, :booking_id, :rating, :title, :content)
            ");
            
            $result = $stmt->execute([
                ':user_id' => $user_id,
                ':booking_id' => $booking_id,
                ':rating' => $rating,
                ':title' => $title,
                ':content' => $content
            ]);

            if ($result) {
                return ["success" => true, "message" => "Thank you for your feedback! Your testimonial is pending approval."];
            } else {
                return ["success" => false, "message" => "Failed to submit testimonial."];
            }
        } catch (PDOException $e) {
            error_log("Error submitting testimonial: " . $e->getMessage());
            return ["success" => false, "message" => "Database error occurred."];
        }
    }

    /**
     * Check if user has already submitted testimonial for a booking
     */
    public function hasUserSubmittedTestimonial($user_id, $booking_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) FROM testimonials 
                WHERE user_id = :user_id AND booking_id = :booking_id
            ");
            $stmt->execute([':user_id' => $user_id, ':booking_id' => $booking_id]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error checking testimonial existence: " . $e->getMessage());
            return true; // Return true to prevent duplicate attempts on error
        }
    }

    /**
     * Verify booking is valid for testimonial (completed and belongs to user)
     */
    public function isValidBookingForTestimonial($user_id, $booking_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) FROM bookings 
                WHERE booking_id = :booking_id 
                AND user_id = :user_id 
                AND status = 'Completed'
            ");
            $stmt->execute([':booking_id' => $booking_id, ':user_id' => $user_id]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error validating booking for testimonial: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's completed bookings that don't have testimonials yet
     */
    public function getBookingsEligibleForTestimonial($user_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT b.booking_id, b.destination, b.date_of_tour, b.end_of_tour, b.completed_at
                FROM bookings b
                LEFT JOIN testimonials t ON b.booking_id = t.booking_id AND t.user_id = :user_id
                WHERE b.user_id = :user_id 
                AND b.status = 'Completed'
                AND t.testimonial_id IS NULL
                ORDER BY b.completed_at DESC
            ");
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting eligible bookings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get approved testimonials for public display
     */
    public function getApprovedTestimonials($limit = 10, $featured_first = true) {
        try {
            $order_clause = $featured_first ? 
                "ORDER BY t.is_featured DESC, t.created_at DESC" : 
                "ORDER BY t.created_at DESC";

            $stmt = $this->conn->prepare("
                SELECT t.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as client_name,
                       u.company_name,
                       b.destination,
                       b.date_of_tour
                FROM testimonials t
                JOIN users u ON t.user_id = u.user_id
                JOIN bookings b ON t.booking_id = b.booking_id
                WHERE t.is_approved = 1
                AND t.is_featured = 1
                {$order_clause}
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting approved testimonials: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get user's submitted testimonials
     */
    public function getUserTestimonials($user_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT t.*, b.destination, b.date_of_tour
                FROM testimonials t
                JOIN bookings b ON t.booking_id = b.booking_id
                WHERE t.user_id = :user_id
                ORDER BY t.created_at DESC
            ");
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user testimonials: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get average rating from approved testimonials
     */
    public function getAverageRating() {
        try {
            $stmt = $this->conn->prepare("
                SELECT ROUND(AVG(rating), 1) as avg_rating, COUNT(*) as total_reviews
                FROM testimonials 
                WHERE is_approved = 1
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'average' => $result['avg_rating'] ?: 0,
                'total' => $result['total_reviews'] ?: 0
            ];
        } catch (PDOException $e) {
            error_log("Error getting average rating: " . $e->getMessage());
            return ['average' => 0, 'total' => 0];
        }
    }
}
?>