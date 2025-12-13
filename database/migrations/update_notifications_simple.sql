-- Simple migration to update notifications table
-- Run this SQL directly in MySQL if your database already has the notifications table

-- Add actor_id column
ALTER TABLE notifications 
ADD COLUMN actor_id INT DEFAULT NULL AFTER user_id,
ADD CONSTRAINT fk_notification_actor FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL;

-- Add reference_id column  
ALTER TABLE notifications 
ADD COLUMN reference_id INT DEFAULT NULL AFTER content;
