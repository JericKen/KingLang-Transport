<?php
require_once __DIR__ . "/../../../config/database.php";

class TestimonialManagementModel {
    private $conn;

    public function __construct() {
        global $pdo;
        $this->conn = $pdo;
    }

    /**
     * Get all testimonials with user and booking details
     */
    public function getAllTestimonials($status = 'all', $limit = 50, $offset = 0) {
        try {
            $whereClause = '';
            $params = [];

            if ($status !== 'all') {
                switch ($status) {
                    case 'pending':
                        $whereClause = 'WHERE t.is_approved = 0';
                        break;
                    case 'approved':
                        $whereClause = 'WHERE t.is_approved = 1';
                        break;
                    case 'featured':
                        $whereClause = 'WHERE t.is_featured = 1';
                        break;
                }
            }

            $stmt = $this->conn->prepare("
                SELECT t.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as client_name,
                       u.email,
                       u.company_name,
                       b.destination,
                       b.date_of_tour,
                       b.end_of_tour
                FROM testimonials t
                JOIN users u ON t.user_id = u.user_id
                JOIN bookings b ON t.booking_id = b.booking_id
                {$whereClause}
                ORDER BY t.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting testimonials: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get testimonial statistics
     */
    public function getTestimonialStats() {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    COUNT(*) as total_testimonials,
                    SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending_testimonials,
                    SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved_testimonials,
                    SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured_testimonials,
                    ROUND(AVG(rating), 1) as average_rating
                FROM testimonials
            ");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting testimonial stats: " . $e->getMessage());
            return [
                'total_testimonials' => 0,
                'pending_testimonials' => 0,
                'approved_testimonials' => 0,
                'featured_testimonials' => 0,
                'average_rating' => 0
            ];
        }
    }

    /**
     * Approve a testimonial
     */
    public function approveTestimonial($testimonial_id) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE testimonials 
                SET is_approved = 1, updated_at = CURRENT_TIMESTAMP 
                WHERE testimonial_id = :testimonial_id
            ");
            return $stmt->execute([':testimonial_id' => $testimonial_id]);
        } catch (PDOException $e) {
            error_log("Error approving testimonial: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject/Unapprove a testimonial
     */
    public function rejectTestimonial($testimonial_id) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE testimonials 
                SET is_approved = 0, is_featured = 0, updated_at = CURRENT_TIMESTAMP 
                WHERE testimonial_id = :testimonial_id
            ");
            return $stmt->execute([':testimonial_id' => $testimonial_id]);
        } catch (PDOException $e) {
            error_log("Error rejecting testimonial: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured($testimonial_id) {
        try {
            // First check current status
            $checkStmt = $this->conn->prepare("
                SELECT is_featured, is_approved FROM testimonials 
                WHERE testimonial_id = :testimonial_id
            ");
            $checkStmt->execute([':testimonial_id' => $testimonial_id]);
            $current = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$current) {
                return false;
            }

            // Can't feature if not approved
            if (!$current['is_approved']) {
                return false;
            }

            $newFeaturedStatus = !$current['is_featured'] ? 1 : 0;
            
            $stmt = $this->conn->prepare("
                UPDATE testimonials 
                SET is_featured = :featured, updated_at = CURRENT_TIMESTAMP 
                WHERE testimonial_id = :testimonial_id
            ");
            return $stmt->execute([
                ':featured' => $newFeaturedStatus,
                ':testimonial_id' => $testimonial_id
            ]);
        } catch (PDOException $e) {
            error_log("Error toggling featured status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a testimonial
     */
    public function deleteTestimonial($testimonial_id) {
        try {
            $stmt = $this->conn->prepare("
                DELETE FROM testimonials WHERE testimonial_id = :testimonial_id
            ");
            return $stmt->execute([':testimonial_id' => $testimonial_id]);
        } catch (PDOException $e) {
            error_log("Error deleting testimonial: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get single testimonial details
     */
    public function getTestimonialById($testimonial_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT t.*, 
                       CONCAT(u.first_name, ' ', u.last_name) as client_name,
                       u.email,
                       u.company_name,
                       b.destination,
                       b.date_of_tour,
                       b.end_of_tour
                FROM testimonials t
                JOIN users u ON t.user_id = u.user_id
                JOIN bookings b ON t.booking_id = b.booking_id
                WHERE t.testimonial_id = :testimonial_id
            ");
            $stmt->execute([':testimonial_id' => $testimonial_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting testimonial by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk approve testimonials
     */
    public function bulkApprove($testimonial_ids) {
        try {
            if (empty($testimonial_ids)) {
                return false;
            }

            $placeholders = str_repeat('?,', count($testimonial_ids) - 1) . '?';
            $stmt = $this->conn->prepare("
                UPDATE testimonials 
                SET is_approved = 1, updated_at = CURRENT_TIMESTAMP 
                WHERE testimonial_id IN ($placeholders)
            ");
            return $stmt->execute($testimonial_ids);
        } catch (PDOException $e) {
            error_log("Error bulk approving testimonials: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Bulk reject testimonials
     */
    public function bulkReject($testimonial_ids) {
        try {
            if (empty($testimonial_ids)) {
                return false;
            }

            $placeholders = str_repeat('?,', count($testimonial_ids) - 1) . '?';
            $stmt = $this->conn->prepare("
                UPDATE testimonials 
                SET is_approved = 0, is_featured = 0, updated_at = CURRENT_TIMESTAMP 
                WHERE testimonial_id IN ($placeholders)
            ");
            return $stmt->execute($testimonial_ids);
        } catch (PDOException $e) {
            error_log("Error bulk rejecting testimonials: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent testimonials for dashboard
     */
    public function getRecentTestimonials($limit = 5) {
        try {
            $stmt = $this->conn->prepare("
                SELECT t.testimonial_id, t.title, t.rating, t.created_at, t.is_approved,
                       CONCAT(u.first_name, ' ', u.last_name) as client_name,
                       b.destination
                FROM testimonials t
                JOIN users u ON t.user_id = u.user_id
                JOIN bookings b ON t.booking_id = b.booking_id
                ORDER BY t.created_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting recent testimonials: " . $e->getMessage());
            return [];
        }
    }
}
?>