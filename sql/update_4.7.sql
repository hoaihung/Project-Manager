-- Patch to add completed_at column for task completion tracking
-- This script should be executed when upgrading from previous versions
-- It adds a new nullable DATETIME column `completed_at` to the `tasks` table
-- to record when a task is marked as done. The value will be set to the
-- timestamp when the task transitions into the 'done' status and cleared
-- if the task is moved back to another status.

ALTER TABLE `tasks` ADD COLUMN `completed_at` DATETIME NULL AFTER `created_at`;