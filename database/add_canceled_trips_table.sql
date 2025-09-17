-- Add missing canceled_trips table
-- This table is referenced in the code but missing from the main database.sql file

DROP TABLE IF EXISTS `canceled_trips`;

CREATE TABLE `canceled_trips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reason` text NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount_refunded` decimal(10,2) DEFAULT 0.00,
  `canceled_by` varchar(50) NOT NULL,
  `canceled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_canceled_at` (`canceled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
