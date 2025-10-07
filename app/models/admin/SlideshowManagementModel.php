<?php
require_once __DIR__ . '/../../../config/database.php';

class SlideshowManagementModel {
    private $conn;
    
    public function __construct() {
        global $pdo;
        $this->conn = $pdo;
    }
    
    /**
     * Get all slideshow images
     */
    public function getAllSlideshowImages() {
        try {
            $query = "SELECT * FROM slideshow_images ORDER BY display_order ASC, created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting slideshow images: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get active slideshow images only
     */
    public function getActiveSlideshowImages() {
        try {
            $query = "SELECT * FROM slideshow_images WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting active slideshow images: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get slideshow image by ID
     */
    public function getSlideshowImageById($id) {
        try {
            $query = "SELECT * FROM slideshow_images WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting slideshow image by ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add new slideshow image
     */
    public function addSlideshowImage($data) {
        try {
            $query = "INSERT INTO slideshow_images (filename, original_filename, title, description, display_order, is_active, created_by) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['filename'],
                $data['original_filename'],
                $data['title'] ?? null,
                $data['description'] ?? null,
                $data['display_order'] ?? 0,
                $data['is_active'] ?? 1,
                $data['created_by'] ?? null
            ]);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error adding slideshow image: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update slideshow image
     */
    public function updateSlideshowImage($id, $data) {
        try {
            $query = "UPDATE slideshow_images SET 
                      title = ?, description = ?, display_order = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP 
                      WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                $data['title'] ?? null,
                $data['description'] ?? null,
                $data['display_order'] ?? 0,
                $data['is_active'] ?? 1,
                $id
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating slideshow image: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete slideshow image
     */
    public function deleteSlideshowImage($id) {
        try {
            // Get filename before deletion to remove file from server
            $image = $this->getSlideshowImageById($id);
            if ($image) {
                $filepath = __DIR__ . '/../../../public/images/slideshow/' . $image['filename'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }
            
            $query = "DELETE FROM slideshow_images WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error deleting slideshow image: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Toggle slideshow image active status
     */
    public function toggleSlideshowImageStatus($id) {
        try {
            $query = "UPDATE slideshow_images SET is_active = NOT is_active, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error toggling slideshow image status: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update display order of slideshow images
     */
    public function updateDisplayOrder($orders) {
        try {
            $this->conn->beginTransaction();
            
            foreach ($orders as $id => $order) {
                $query = "UPDATE slideshow_images SET display_order = ? WHERE id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([$order, $id]);
            }
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error updating display order: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get slideshow statistics
     */
    public function getSlideshowStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_images,
                        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_images,
                        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_images
                      FROM slideshow_images";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting slideshow stats: " . $e->getMessage());
            return false;
        }
    }
}
?>
