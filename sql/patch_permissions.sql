-- Patch script to initialise per-user permission settings
--
-- This script populates the `settings` table with default permissions
-- for each user.  Admin users implicitly have all rights in the
-- application and therefore their permission record will be empty
-- (an empty JSON object).  Non‑admin users are assigned a
-- permissions object with all capabilities disabled and no project
-- access by default.  Feel free to modify the JSON object to set
-- different defaults.  The script uses INSERT ... SELECT to create
-- a row per user if one does not already exist.

-- Insert or update admin users
REPLACE INTO settings (`key`, `value`)
SELECT CONCAT('permissions_', u.id) AS `key`, '{}' AS `value`
FROM users u
WHERE u.role_id = 1;

-- Insert or update non-admin users with default disabled permissions
REPLACE INTO settings (`key`, `value`)
SELECT CONCAT('permissions_', u.id) AS `key`,
       '{"create_project":0,"edit_project":0,"delete_project":0,"edit_task":0,"delete_task":0,"access_projects":[]}' AS `value`
FROM users u
WHERE u.role_id <> 1;

-- Notes:
--   * REPLACE will overwrite existing permission records.  If you want
--     to preserve existing permissions, use INSERT ... ON DUPLICATE KEY UPDATE
--     with appropriate logic.
--   * After running this script, you can customise permissions for
--     individual users via the admin interface.