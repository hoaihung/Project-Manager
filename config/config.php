<?php
/**
 * Global configuration file.
 *
 * Define your application configuration values here.
 * These values are used throughout the application for things like
 * database connections, base URL calculation and debugging.
 */

// Define helper functions only once to prevent redeclaration errors.
if (!function_exists('env')) {
    /**
     * Helper to fetch values from environment variables with a default.
     * This tiny helper makes it easier to work with a .env style configuration
     * without pulling in an entire environment library. It simply checks for
     * a defined variable and returns its value or a default if not set.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, $default = null) {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
}

return [
    // Base URL of the application. Adjust this if your app lives in a subfolder.
    'base_url' => env('APP_URL', 'http://localhost'),

    // Database settings. Fill in your own credentials here.
    'db' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'name' => env('DB_NAME', 'project_manager'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
    ],

    // Default locale used for translations. Additional languages can be
    // provided in the config/localization.php file.
    'locale' => env('APP_LOCALE', 'vi'),

    // Enable or disable application level debugging. When true, errors
    // will be displayed on screen. Disable in production.
    'debug' => env('APP_DEBUG', true),
];