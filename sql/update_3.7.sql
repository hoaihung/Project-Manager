-- Update script for version 3.7
--
-- This script adds the tables required for the notes module, task
-- links and checklists if they do not already exist.  It also
-- augments the permission JSON stored in the `settings` table to
-- include the new `view_any_note` permission flag.  Running this
-- script on an existing v3.5 database will bring it up to date with
-- the data structures expected by the v3.7 codebase.

-- Create notes table
CREATE TABLE IF NOT EXISTS `notes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT UNSIGNED NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `content` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  CONSTRAINT `fk_notes_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create pivot table linking notes to tasks
CREATE TABLE IF NOT EXISTS `note_task` (
  `note_id` INT UNSIGNED NOT NULL,
  `task_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`note_id`, `task_id`),
  CONSTRAINT `fk_note_task_note` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_note_task_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create task links table for storing external document references
CREATE TABLE IF NOT EXISTS `task_links` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `task_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(255) DEFAULT NULL,
  `url` VARCHAR(1024) NOT NULL,
  `created_at` DATETIME NOT NULL,
  CONSTRAINT `fk_task_links_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create checklist items table to support per‑task checklists
CREATE TABLE IF NOT EXISTS `checklist_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `task_id` INT UNSIGNED NOT NULL,
  `content` VARCHAR(255) NOT NULL,
  `is_done` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 1,
  CONSTRAINT `fk_checklist_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Augment existing permission JSON to include the view_any_note flag.
-- This update will append '"view_any_note":0' immediately after the
-- '"access_projects":[]' key for all non-admin users.  Admin
-- permissions remain an empty JSON object.
UPDATE `settings`
SET `value` = REPLACE(`value`, '"access_projects":[]', '"access_projects":[],"view_any_note":0')
WHERE `key` LIKE 'permissions_%' AND `value` NOT LIKE '%"view_any_note"%';

-- End of update 3.7