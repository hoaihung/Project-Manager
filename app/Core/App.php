<?php
namespace app\Core;

use Exception;

/**
 * Class App
 *
 * The front controller for the MVC application. It interprets the URL
 * parameters to determine which controller and action should handle
 * the request. It supports simple routing via query string parameters
 * `controller` and `action`. Additional segments are passed as
 * parameters to the action method.
 */
class App
{
    /**
     * Run the application.
     */
    public function run(): void
    {
        // Determine controller and action from query string
        $controllerName = $_GET['controller'] ?? 'dashboard';
        $actionName = $_GET['action'] ?? 'index';

        // Normalize names
        $controllerClass = 'app\\Controller\\' . ucfirst($controllerName) . 'Controller';
        $actionMethod = $actionName;

        // Attempt to instantiate the controller
        if (!class_exists($controllerClass)) {
            // Fallback to dashboard
            $controllerClass = 'app\\Controller\\DashboardController';
            $actionMethod = 'index';
        }
        $controller = new $controllerClass();
        // Call the action
        if (!method_exists($controller, $actionMethod)) {
            throw new Exception("Action $actionMethod not found in $controllerClass");
        }
        call_user_func([$controller, $actionMethod]);
    }
}