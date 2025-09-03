<?php
require_once __DIR__ . '/../../../config/database.php';

class PastTripsModel {
    private $conn;

    public function __construct() {
        global $pdo;
        $this->conn = $pdo;
    }

    public function getAllImages() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM past_trip_images ORDER BY display_order ASC, created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error getting past trip images: ' . $e->getMessage());
            return false;
        }
    }

    public function getActiveImages() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM past_trip_images WHERE is_active = 1 ORDER BY display_order ASC, created_at DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error getting active past trip images: ' . $e->getMessage());
            return false;
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM past_trip_images WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error getting past trip image by ID: ' . $e->getMessage());
            return false;
        }
    }

    public function addImage($data) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO past_trip_images (filename, original_filename, title, description, display_order, is_active, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
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
            error_log('Error adding past trip image: ' . $e->getMessage());
            return false;
        }
    }

    public function updateImage($id, $data) {
        try {
            $stmt = $this->conn->prepare("UPDATE past_trip_images SET title = ?, description = ?, display_order = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([
                $data['title'] ?? null,
                $data['description'] ?? null,
                $data['display_order'] ?? 0,
                $data['is_active'] ?? 1,
                $id
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Error updating past trip image: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteImage($id) {
        try {
            $image = $this->getById($id);
            if ($image) {
                $filepath = __DIR__ . '/../../../public/images/past-trips/' . $image['filename'];
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
            }

            $stmt = $this->conn->prepare("DELETE FROM past_trip_images WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Error deleting past trip image: ' . $e->getMessage());
            return false;
        }
    }

    public function toggleStatus($id) {
        try {
            $stmt = $this->conn->prepare("UPDATE past_trip_images SET is_active = NOT is_active, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('Error toggling past trip image status: ' . $e->getMessage());
            return false;
        }
    }

    public function updateDisplayOrder($orders) {
        try {
            $this->conn->beginTransaction();
            foreach ($orders as $id => $order) {
                $stmt = $this->conn->prepare("UPDATE past_trip_images SET display_order = ? WHERE id = ?");
                $stmt->execute([$order, $id]);
            }
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log('Error updating past trip display order: ' . $e->getMessage());
            return false;
        }
    }

    public function getStats() {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total_images, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_images, SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_images FROM past_trip_images");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error getting past trip stats: ' . $e->getMessage());
            return false;
        }
    }
}
?>


