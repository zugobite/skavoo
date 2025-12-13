-- Migration to add actor_id and reference_id to notifications table
-- Run this if you already have the notifications table and don't want to drop everything

-- Check if actor_id column exists, add if not
SET @dbname = DATABASE();
SET @tablename = 'notifications';

-- Add actor_id column if it doesn't exist
SELECT IF(
    EXISTS(
        SELECT 1 FROM information_schema.columns 
        WHERE table_schema = @dbname 
        AND table_name = @tablename 
        AND column_name = 'actor_id'
    ),
    'SELECT ''actor_id already exists''',
    'ALTER TABLE notifications ADD COLUMN actor_id INT DEFAULT NULL AFTER user_id, ADD FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add reference_id column if it doesn't exist
SELECT IF(
    EXISTS(
        SELECT 1 FROM information_schema.columns 
        WHERE table_schema = @dbname 
        AND table_name = @tablename 
        AND column_name = 'reference_id'
    ),
    'SELECT ''reference_id already exists''',
    'ALTER TABLE notifications ADD COLUMN reference_id INT DEFAULT NULL AFTER content'
) INTO @sql;
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
