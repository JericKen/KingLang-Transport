-- Migration to add visitor support to conversations table
-- This allows non-authenticated users to use the chat system

-- Add session_id column for visitor conversations
ALTER TABLE `conversations` 
ADD COLUMN `session_id` VARCHAR(255) NULL AFTER `client_id`;

-- Modify client_id to allow NULL values for visitor conversations  
ALTER TABLE `conversations` 
MODIFY COLUMN `client_id` int(11) NULL;

-- Add index for session_id
ALTER TABLE `conversations` 
ADD INDEX `idx_session_id` (`session_id`);

-- Add index for session_id and status combination
ALTER TABLE `conversations` 
ADD INDEX `idx_conversations_session_status` (`session_id`, `status`);

-- Update the foreign key constraint to handle NULL client_id
ALTER TABLE `conversations` 
DROP FOREIGN KEY `fk_conversations_client`;

ALTER TABLE `conversations` 
ADD CONSTRAINT `fk_conversations_client` 
FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`) 
ON DELETE CASCADE;
