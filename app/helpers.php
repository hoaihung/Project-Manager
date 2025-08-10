<?php
/**
 * Application helper functions.
 *
 * This file contains global helper functions that can be used throughout
 * the controllers and views. Keeping helpers here makes them easy to
 * locate and prevents cluttering other core classes with utility logic.
 */

use app\Core\Container;

/**
 * Translation helper.
 *
 * Fetches a translated string based on a key and optional replacements.
 * If the key does not exist in the current locale the key itself is
 * returned. The current locale is configured in config/config.php.
 *
 * @param string $key
 * @param array $replace
 * @return string
 */
if (!function_exists('__')) {
    /**
     * Translation helper.
     *
     * Fetches a translated string based on a key and optional replacements.
     * If the key does not exist in the current locale the key itself is
     * returned. The current locale is configured in config/config.php.
     *
     * @param string $key
     * @param array $replace
     * @return string
     */
    function __(string $key, array $replace = []): string
    {
        static $translations;
        if (!$translations) {
            $lang = require_once __DIR__ . '/../config/localization.php';
            $config = require_once __DIR__ . '/../config/config.php';
            // Allow runtime locale override via session variable
            $locale = $_SESSION['locale'] ?? ($config['locale'] ?? 'vi');
            $translations = $lang[$locale] ?? [];
        }
        $text = $translations[$key] ?? $key;
        foreach ($replace as $search => $value) {
            $text = str_replace(':' . $search, $value, $text);
        }
        return $text;
    }
}

// -----------------------------------------------------------------------------
// Permission helpers
//
// These helpers provide a simple mechanism to persist and retrieve per-user
// permissions as JSON in the settings table. Permissions govern whether a
// user may create, edit or delete projects and tasks, as well as which
// specific projects they may access. Administrators (role_id = 1) bypass
// permission checks.

use app\Model\Setting;

if (!function_exists('get_user_permissions')) {
    /**
     * Retrieve the permission array for a given user.
     *
     * Permissions are stored in the settings table under the key
     * `permissions_{userId}` as a JSON string. If no permissions are set
     * for the user an empty array is returned.  Admin users do not require
     * explicit permissions.
     *
     * @param int $userId
     * @return array
     */
    function get_user_permissions(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }
        $settingModel = new Setting();
        $key = 'permissions_' . $userId;
        $json = $settingModel->get($key);
        if ($json) {
            $perms = json_decode($json, true);
            return is_array($perms) ? $perms : [];
        }
        return [];
    }
}

if (!function_exists('set_user_permissions')) {
    /**
     * Persist permissions for a given user.  The permissions array will be
     * JSON-encoded and saved under `permissions_{userId}` in the settings table.
     *
     * @param int $userId
     * @param array $perms
     */
    function set_user_permissions(int $userId, array $perms): void
    {
        $settingModel = new Setting();
        $key = 'permissions_' . $userId;
        $json = json_encode($perms);
        $settingModel->set($key, $json);
    }
}

if (!function_exists('user_can')) {
    /**
     * Determine whether the currently logged in user has a specific permission.
     *
     * Permissions are scoped by keys such as 'create_project', 'edit_project',
     * 'delete_project', 'edit_task', 'delete_task', and 'access_project'.  For
     * 'access_project' the optional $resource parameter should be the project
     * identifier to check against the user's access list.  Admin users always
     * return true.
     *
     * @param string $permission
     * @param mixed $resource
     * @return bool
     */
    function user_can(string $permission, $resource = null): bool
    {
        $currentUser = $_SESSION['user'] ?? null;
        if (!$currentUser) {
            return false;
        }
        // Admins bypass permission checks
        if (isset($currentUser['role_id']) && (int)$currentUser['role_id'] === 1) {
            return true;
        }
        $perms = get_user_permissions((int)$currentUser['id']);
        // Determine by permission key
        switch ($permission) {
            case 'view_any_note':
                return !empty($perms['view_any_note']);
            case 'create_project':
                return !empty($perms['create_project']);
            case 'edit_project':
                return !empty($perms['edit_project']);
            case 'edit_task':
            case 'delete_task':
                // All members of a project may manage tasks if they can access the project
                return true;
            case 'access_project':
                if (!is_array($perms['access_projects'] ?? null)) {
                    return false;
                }
                return in_array($resource, $perms['access_projects']);
            default:
                return false;
        }
    }
}

// -----------------------------------------------------------------------------
// Note permission helper
//
// This helper evaluates whether a user can view a given note record.  It
// mirrors the logic used in NoteController::canViewNote() to ensure
// consistent access control across controllers and views.  The function
// accepts a note associative array containing at least `id`, `user_id` and
// `project_id`, as well as the ID of the user performing the check.  It
// returns true if the user may view the note and false otherwise.  See
// documentation in docs/permissions.md for details.

if (!function_exists('note_can_view')) {
    /**
     * Determine whether a user can view a specific note.
     *
     * The rules are:
     *  - Administrators can view all notes.
     *  - The author of the note can view it.
     *  - Users with the special permission 'view_any_note' can view all notes.
     *  - If the note is associated with a project, users with access to that
     *    project may view it.
     *  - If the note is attached to tasks, users assigned to those tasks or
     *    users with access to the tasks' projects may view it.
     *  - Global notes (project_id is NULL and no task links) are only
     *    visible to their author, administrators or those with
     *    'view_any_note'.
     *
     * @param array $note The note record to check.
     * @param int $userId The ID of the user performing the check.
     * @return bool True if the user may view the note, false otherwise.
     */
    function note_can_view(array $note, int $userId): bool
    {
        // Ensure there is a current user session
        if ($userId <= 0) {
            return false;
        }
        // Load user model to determine role
        $userModel = new \app\Model\User();
        $user      = $userModel->findById($userId);
        if (!$user) {
            return false;
        }
        // Admins can view all notes
        if (isset($user['role_id']) && (int)$user['role_id'] === 1) {
            return true;
        }
        // Author of the note can view
        if (isset($note['user_id']) && (int)$note['user_id'] === $userId) {
            return true;
        }
        // Check special permission view_any_note
        if (user_can('view_any_note')) {
            return true;
        }
        // Check project association
        $projectId = !empty($note['project_id']) ? (int)$note['project_id'] : null;
        if ($projectId && user_can('access_project', $projectId)) {
            return true;
        }
        // Check tasks associated with this note: if user is assigned to any
        // task or has access to that task's project, allow view.
        $noteModel = new \app\Model\Note();
        $tasks     = $noteModel->getTasks($note['id']);
        if (!empty($tasks)) {
            $taskUserModel = new \app\Model\TaskUser();
            foreach ($tasks as $task) {
                $taskId    = (int)$task['id'];
                $projId    = (int)($task['project_id'] ?? 0);
                // Check if user is assigned to this task
                $assignees = $taskUserModel->getUsersByTask($taskId);
                foreach ($assignees as $assignee) {
                    if ((int)$assignee['id'] === $userId) {
                        return true;
                    }
                }
                // Or user can access the project of the task
                if ($projId && user_can('access_project', $projId)) {
                    return true;
                }
            }
        }
        // Otherwise not permitted
        return false;
    }
}

/**
 * Escape output for HTML.
 *
 * Always escape variables before outputting them into the HTML context to
 * avoid XSS attacks. This helper uses PHP's built in htmlspecialchars
 * function with sensible defaults.
 *
 * @param string|null $string
 * @return string
 */
if (!function_exists('e')) {
    /**
     * Escape output for HTML.
     *
     * Always escape variables before outputting them into the HTML context to
     * avoid XSS attacks. This helper uses PHP's built in htmlspecialchars
     * function with sensible defaults.
     *
     * @param string|null $string
     * @return string
     */
    function e(?string $string): string
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Convert a plain text or markdown string into safe HTML.  This helper
 * supports a small subset of Markdown syntax (bold, italics, links and
 * simple checklists) and automatically turns bare URLs into clickable
 * anchors.  Use this when rendering user‑generated note content or
 * descriptions/comments that may contain links.  The implementation
 * escapes all HTML before applying regex replacements to avoid XSS.
 *
 * @param string $text
 * @return string
 */
if (!function_exists('markdown_to_html')) {
    function markdown_to_html(string $text): string
    {
        // Escape HTML characters first
        $html = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        // Bold **text**
        $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);
        // Italic *text*
        $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html);
        // Markdown links [text](url)
        $html = preg_replace('/\[(.+?)\]\((https?:\/\/[^\s]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $html);
        // Bare URLs
        $html = preg_replace('/(https?:\/\/[^\s<]+)/', '<a href="$1" target="_blank" rel="noopener">$1</a>', $html);
        // Checklist items: - [ ] item or - [x] item
        $html = preg_replace_callback('/^- \[( |x)\] (.*)$/m', function ($matches) {
            $checked = trim($matches[1]) === 'x' ? 'checked' : '';
            $content = $matches[2];
            return '<label style="display:block;"><input type="checkbox" disabled ' . $checked . '> ' . $content . '</label>';
        }, $html);
        // Convert newlines to <br> for simple line breaks
        $html = nl2br($html);
        return $html;
    }
}

/**
 * Convert plain text URLs into clickable links.  This helper is used
 * when rendering comments and descriptions that may contain raw URLs.
 * It does not support full markdown but will escape HTML and
 * substitute anchors for http/https links.
 *
 * @param string|null $text
 * @return string
 */
if (!function_exists('linkify')) {
    function linkify(?string $text): string
    {
        // Convert plain URLs in the given text into clickable links with a custom
        // label.  The anchor text will be rendered as "(liên kết) [tên miền]"
        // where the domain is extracted from the URL.  For example, the URL
        // "https://www.example.com/path" becomes "(liên kết) [www.example.com]".
        $text = $text ?? '';
        $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        return preg_replace_callback('/(https?:\/\/[^\s<]+)/', function ($matches) {
            $url = $matches[1];
            // Remove protocol and extract domain before first slash
            $domain = preg_replace('#^https?://#', '', $url);
            $domain = explode('/', $domain)[0];
            return '<a href="' . $url . '" target="_blank" rel="noopener">(liên kết) [' . $domain . ']</a>';
        }, $escaped);
    }
}

/**
 * Redirect helper.
 *
 * Send an HTTP redirect header and optionally stop script execution.
 *
 * @param string $url
 * @param bool $end
 */
if (!function_exists('redirect')) {
    /**
     * Redirect helper.
     *
     * Send an HTTP redirect header and optionally stop script execution.
     *
     * @param string $url
     * @param bool $end
     */
    function redirect(string $url, bool $end = true): void
    {
        header('Location: ' . $url);
        if ($end) {
            exit;
        }
    }
}