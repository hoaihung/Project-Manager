<?php
namespace app\Controller;

use app\Core\Controller;

/**
 * Class AuthController
 *
 * Handles user authentication such as login and logout. Only admin can
 * create user accounts; there is no public registration form.
 */
class AuthController extends Controller
{
    /**
     * Display login form and handle login submission.
     */
    public function login(): void
    {
        // If already logged in redirect to dashboard
        if (!empty($_SESSION['user_id'])) {
            redirect('index.php');
        }
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $userModel = $this->loadModel('User');
            $user = $userModel->authenticate($username, $password);
            if ($user) {
                // Persist both the user_id and the full user record into the session.
                // user_can() relies on $_SESSION['user'] to determine role and id.
                $_SESSION['user_id'] = $user['id'];
                // Store the user record (id, username, role_id, etc.) for permission checks
                $_SESSION['user'] = $user;
                redirect('index.php');
            } else {
                $error = __('Invalid credentials');
            }
        }
        $this->render('auth/login', ['error' => $error]);
    }

    /**
     * Logout the current user.
     */
    public function logout(): void
    {
        // Clear authentication state. Remove both user_id and user entry.
        unset($_SESSION['user_id']);
        unset($_SESSION['user']);
        // Destroy the entire session for security
        session_destroy();
        redirect('index.php?controller=auth&action=login');
    }
}