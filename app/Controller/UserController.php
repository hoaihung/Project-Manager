<?php
namespace app\Controller;

use app\Core\Controller;

// Include permission helper functions
require_once __DIR__ . '/../helpers.php';

/**
 * Class UserController
 *
 * Admin only interface to manage users. Allows listing, creating, editing
 * and deleting user accounts. Roles can also be assigned here.
 */
class UserController extends Controller
{
    /**
     * List all users.
     */
    public function index(): void
    {
        $this->requireAdmin();
        $userModel = $this->loadModel('User');
        $users = $userModel->all();
        $this->render('users/index', ['users' => $users]);
    }

    /**
     * Show create user form and handle submission.
     */
    public function create(): void
    {
        $this->requireAdmin();
        $roleModel = $this->loadModel('Role');
        $roles = $roleModel->all();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'full_name' => $_POST['full_name'],
                'email' => $_POST['email'],
                'role_id' => $_POST['role_id'],
            ];
            $userModel = $this->loadModel('User');
            // Create user and retrieve ID
            $newId = $userModel->create($data);
            // If the new user is not an admin (role_id != 1), save selected permissions
            if ((int)$data['role_id'] !== 1) {
                // Collect boolean permissions from POST.  Checkbox values are
                // 'on' when checked; convert to boolean true.
                $perms = [];
                $perms['create_project'] = !empty($_POST['perm_create_project']);
                $perms['edit_project'] = !empty($_POST['perm_edit_project']);
                // Delete project permission is reserved for administrators; omit for regular users
                $perms['delete_project'] = false;
                // Task permissions have been removed; default to false
                $perms['edit_task'] = false;
                $perms['delete_task'] = false;
                // Access project IDs come from a multi-select; ensure ints
                $projects = $_POST['access_projects'] ?? [];
                $accessList = [];
                foreach ($projects as $pid) {
                    $pidInt = (int)$pid;
                    if ($pidInt > 0) {
                        $accessList[] = $pidInt;
                    }
                }
                $perms['access_projects'] = $accessList;
                set_user_permissions($newId, $perms);
            }
            // For admin users, clear any permissions previously set
            else {
                set_user_permissions($newId, []);
            }
            redirect('index.php?controller=user');
        }
        // Provide list of projects for access selection
        $projectModel = $this->loadModel('Project');
        $projects = $projectModel->all();
        $this->render('users/create', ['roles' => $roles, 'projects' => $projects]);
    }

    /**
     * Show edit form and handle update.
     */
    public function edit(): void
    {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $userModel = $this->loadModel('User');
        $user = $userModel->findById($id);
        if (!$user) {
            redirect('index.php?controller=user');
        }
        $roleModel = $this->loadModel('Role');
        $roles = $roleModel->all();
        // Load list of projects for access selection
        $projectModel = $this->loadModel('Project');
        $projects = $projectModel->all();
        // Determine current permissions for this user (non-admin users only)
        $currentPerms = [];
        if ((int)$user['role_id'] !== 1) {
            $currentPerms = get_user_permissions($id);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'full_name' => $_POST['full_name'],
                'email' => $_POST['email'],
                'role_id' => $_POST['role_id'],
            ];
            if (!empty($_POST['password'])) {
                $data['password'] = $_POST['password'];
            }
            $userModel->update($id, $data);
            // Handle permissions update for non-admins
            if ((int)$data['role_id'] !== 1) {
                $perms = [];
                $perms['create_project'] = !empty($_POST['perm_create_project']);
                $perms['edit_project'] = !empty($_POST['perm_edit_project']);
                // For non-admin users, delete project permission is disabled
                $perms['delete_project'] = false;
                // Task permissions have been removed; default to false
                $perms['edit_task'] = false;
                $perms['delete_task'] = false;
                $projList = $_POST['access_projects'] ?? [];
                $accessList = [];
                foreach ($projList as $pid) {
                    $pidInt = (int)$pid;
                    if ($pidInt > 0) {
                        $accessList[] = $pidInt;
                    }
                }
                $perms['access_projects'] = $accessList;
                set_user_permissions($id, $perms);
            } else {
                // Clear permissions if user is admin
                set_user_permissions($id, []);
            }
            redirect('index.php?controller=user');
        }
        $this->render('users/edit', [
            'user' => $user,
            'roles' => $roles,
            'projects' => $projects,
            'currentPerms' => $currentPerms,
        ]);
    }

    /**
     * Delete a user.
     */
    public function delete(): void
    {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $userModel = $this->loadModel('User');
        $userModel->delete($id);
        redirect('index.php?controller=user');
    }
}