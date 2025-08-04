<?php
namespace app\Core;

/**
 * Class Autoloader
 *
 * A simple PSR-4 compliant autoloader for our application classes.
 * When a class is referenced PHP will call this autoloader to locate
 * and require the appropriate file based on the namespace and class name.
 */
class Autoloader
{
    /**
     * Register the autoloader with PHP.
     */
    public static function register(): void
    {
        spl_autoload_register(function ($class) {
            // Only autoload classes within the 'app' namespace
            if (strpos($class, 'app\\') === 0) {
                // Remove the base namespace prefix
                $relativeClass = substr($class, 4);
                // Convert namespace separators to directory separators
                $file = __DIR__ . '/../' . str_replace('\\', '/', $relativeClass) . '.php';
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        });
    }
}