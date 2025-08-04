<?php
namespace app\Controller;

use app\Core\Controller;

/**
 * Class AdminController
 *
 * Provides administration functions such as managing site settings. Only users
 * with the admin role can access these actions.
 */
class AdminController extends Controller
{
    /**
     * Display settings page and handle updates.
     */
    public function settings(): void
    {
        $this->requireLogin();
        $this->requireAdmin();
        $settingModel = $this->loadModel('Setting');
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Expected keys: color_primary, color_secondary, color_success, color_warning, color_danger, overload_threshold
            foreach (['color_primary','color_secondary','color_success','color_warning','color_danger','overload_threshold'] as $key) {
                if (isset($_POST[$key])) {
                    $value = trim($_POST[$key]);
                    $settingModel->set($key, $value);
                }
            }
            redirect('index.php?controller=admin&action=settings');
        }
        $settings = $settingModel->getAll();
        $this->render('admin/settings', [
            'settings' => $settings,
        ]);
    }
}