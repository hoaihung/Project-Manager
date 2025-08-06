<?php
namespace app\Controller;

use app\Core\Controller;

/**
 * Class UiController
 *
 * Handles various user interface AJAX actions such as toggling the
 * sidebar collapsed state.  These actions are invoked via fetch
 * requests from the front‑end and update session variables to
 * persist UI preferences across page loads.
 */
class UiController extends Controller
{
    /**
     * Persist the collapsed state of the sidebar.  Expects a POST
     * parameter `state` with value `collapsed` or `expanded`.  When
     * collapsed, the sidebar will remain hidden on subsequent
     * navigations until toggled again.
     */
    public function setSidebar(): void
    {
        $this->requireLogin();
        $state = $_POST['state'] ?? '';
        if ($state === 'collapsed') {
            $_SESSION['sidebar_collapsed'] = true;
        } else {
            unset($_SESSION['sidebar_collapsed']);
        }
        header('Content-Type: application/json');
        echo json_encode(['status' => 'ok']);
    }
}