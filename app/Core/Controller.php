<?php
namespace app\Core;

use app\Model\User;
use Exception;

/**
 * Class Controller
 *
 * Base controller all other controllers extend. Provides convenience
 * methods for loading models, rendering views and enforcing basic
 * authentication and authorization rules.
 */
abstract class Controller
{
    // Note: controllers can access query parameters directly via $_GET. The
    // $params property has been removed to prevent protected property access
    // issues when the router tries to assign values. If you need to pass
    // custom parameters to actions, consider adding a public setter on
    // specific controllers.

    /**
     * Load a model by class name.
     *
     * @param string $model
     * @return object
     */
    protected function loadModel(string $model): object
    {
        $class = 'app\\Model\\' . $model;
        return new $class();
    }

    /**
     * Render a view template located in app/View.
     *
     * @param string $view
     * @param array $data
     * @throws Exception
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        $config = require __DIR__ . '/../../config/config.php';
        $baseUrl = $config['base_url'];
        // Provide access to helper functions in the view scope
        require_once __DIR__ . '/../helpers.php';
        // Include header
        $header = __DIR__ . '/../View/layouts/header.php';
        if (file_exists($header)) {
            include $header;
        }
        $file = __DIR__ . '/../View/' . $view . '.php';
        if (!file_exists($file)) {
            throw new Exception("View $view not found");
        }
        include $file;
        // Include footer
        $footer = __DIR__ . '/../View/layouts/footer.php';
        if (file_exists($footer)) {
            include $footer;
        }
    }

    /**
     * Ensure the user is authenticated. Redirect to login page if not.
     */
    protected function requireLogin(): void
    {
        if (empty($_SESSION['user_id'])) {
            redirect('index.php?controller=auth&action=login');
        }
    }

    /**
     * Ensure the user has admin privileges. Redirect to dashboard if not.
     */
    protected function requireAdmin(): void
    {
        $this->requireLogin();
        $userModel = $this->loadModel('User');
        $user = $userModel->findById($_SESSION['user_id']);
        if (!$user || $user['role_name'] !== 'admin') {
            redirect('index.php');
        }
    }
}