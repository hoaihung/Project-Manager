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