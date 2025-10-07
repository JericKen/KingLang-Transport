-- Slideshow Management Tables
-- This file adds slideshow management functionality to the KingLang Transport system

-- Table for storing slideshow images
CREATE TABLE IF NOT EXISTS `slideshow_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_display_order` (`display_order`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert some default slideshow images (optional)
INSERT INTO `slideshow_images` (`filename`, `original_filename`, `title`, `description`, `display_order`, `is_active`) VALUES
('slide2.jpg', 'slide2.jpg', 'Experience Comfort and Luxury', 'Luxury bus transportation for your comfort', 1, 1),
('slide3.jpg', 'slide3.jpg', 'Travel with Style and Safety', 'Safe and stylish travel experience', 2, 1),
('slide4.jpg', 'slide4.jpg', 'Your On-The-Go Tourist Bus Rental', 'Professional tourist bus rental services', 3, 1),
('slide5.jpg', 'slide5.jpg', 'Professional Transportation Services', 'Reliable and professional transportation', 4, 1);
