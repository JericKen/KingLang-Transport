-- Add cancellation_reason_category field to canceled_trips table
-- This allows us to categorize cancellation reasons for better analytics

ALTER TABLE `canceled_trips` 
ADD COLUMN `cancellation_reason_category` varchar(100) DEFAULT NULL AFTER `reason`,
ADD COLUMN `custom_reason` text DEFAULT NULL AFTER `cancellation_reason_category`;

-- Add index for better query performance
ALTER TABLE `canceled_trips` 
ADD INDEX `idx_cancellation_reason_category` (`cancellation_reason_category`);
