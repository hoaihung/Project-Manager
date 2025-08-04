<?php
namespace app\Controller;

use app\Core\Controller;
// Include helper functions for permissions
require_once __DIR__ . '/../helpers.php';

/**
 * Class ProjectController
 *
 * Manages project CRUD operations.
 */
class ProjectController extends Controller
{
    /**
     * List all projects.
     */
    public function index(): void
    {
        $this->requireLogin();
        $projectModel = $this->loadModel('Project');
        $projects = $projectModel->all();
        // Filter projects based on access permissions.  Admin users will
        // always pass this check because user_can() returns true for them.
        $accessible = [];
        // Load all users once for member count calculation
        $userModel = $this->loadModel('User');
        $allUsers = $userModel->all();
        foreach ($projects as $p) {
            if (user_can('access_project', $p['id'])) {
                // Compute number of members: count users whose access_projects includes this project or whose role is admin
                $count = 0;
                foreach ($allUsers as $u) {
                    // Admins implicitly have access to all projects
                    if ((int)$u['role_id'] === 1) {
                        $count++;
                        continue;
                    }
                    $perms = get_user_permissions((int)$u['id']);
                    $access = $perms['access_projects'] ?? [];
                    if (is_array($access) && in_array($p['id'], $access)) {
                        $count++;
                    }
                }
                // Attach member_count to the project array for display in the view
                $p['member_count'] = $count;
                $accessible[] = $p;
            }
        }
        $this->render('projects/index', ['projects' => $accessible]);
    }

    /**
     * Show create project form and handle submission.
     */
    public function create(): void
    {
        $this->requireLogin();
        // Ensure the user has permission to create projects.  If not,
        // render a permission error page.
        if (!user_can('create_project')) {
            $this->render('errors/no_permission');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Default start_date to today if not provided
            $start = $_POST['start_date'];
            if (empty($start)) {
                $start = date('Y-m-d');
            }
            // Validate date order: ensure end_date is not before start_date
            $endParam = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
            if ($endParam && $start && $endParam < $start) {
                // If end date precedes start, align end date to start
                $endParam = $start;
            }
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'] ?? 'new',
                'start_date' => $start,
                'end_date' => $endParam,
            ];
            $projectModel = $this->loadModel('Project');
            // Create project and retrieve ID
            $projectId = $projectModel->create($data);
            // Handle assignment of members: only non-admin users are recorded.
            // The form sends an array of user IDs under the key 'members'.  It may
            // also include a special value 'all' to indicate all non-admin users.
            $memberIds = $_POST['members'] ?? [];
            // Load user model to iterate over all users
            $userModel = $this->loadModel('User');
            $allUsers = $userModel->all();
            // If 'all' selected, build list of all non-admin users
            if (in_array('all', $memberIds)) {
                $memberIds = [];
                foreach ($allUsers as $u) {
                    if ((int)$u['role_id'] !== 1) {
                        $memberIds[] = (string)$u['id'];
                    }
                }
            }
            // Update each user's access_projects list
            foreach ($allUsers as $u) {
                // Skip admin - they implicitly have access to all projects
                if ((int)$u['role_id'] === 1) {
                    continue;
                }
                $uid = (int)$u['id'];
                $perms = get_user_permissions($uid);
                $access = $perms['access_projects'] ?? [];
                // Determine whether this user should have access
                $shouldHave = in_array((string)$uid, $memberIds);
                $has = in_array($projectId, $access);
                if ($shouldHave && !$has) {
                    // Add project id
                    $access[] = $projectId;
                } elseif (!$shouldHave && $has) {
                    // Remove project id
                    $access = array_values(array_diff($access, [$projectId]));
                }
                $perms['access_projects'] = $access;
                // Persist back only if role is non-admin
                set_user_permissions($uid, $perms);
            }
            redirect('index.php?controller=project');
        }
        // For GET request, load users for membership selection.  Only list non-admin
        $userModel = $this->loadModel('User');
        $users = $userModel->all();
        $nonAdmins = [];
        foreach ($users as $u) {
            if ((int)$u['role_id'] !== 1) {
                $nonAdmins[] = $u;
            }
        }
        $this->render('projects/create', ['users' => $nonAdmins]);
    }

    /**
     * Show edit form and handle update.
     */
    public function edit(): void
    {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        // Check access to this project and edit permission.  If the user lacks
        // permission, show an error page.
        if (!user_can('access_project', $id) || !user_can('edit_project')) {
            $this->render('errors/no_permission');
            return;
        }
        $projectModel = $this->loadModel('Project');
        $project = $projectModel->findById($id);
        if (!$project) {
            redirect('index.php?controller=project');
        }
        // Load user list for membership selection (non-admins)
        $userModel = $this->loadModel('User');
        $allUsers = $userModel->all();
        $nonAdmins = [];
        foreach ($allUsers as $u) {
            if ((int)$u['role_id'] !== 1) {
                $nonAdmins[] = $u;
            }
        }
        // Determine currently selected members for this project (non-admin users only)
        $currentMembers = [];
        foreach ($nonAdmins as $u) {
            $perms = get_user_permissions((int)$u['id']);
            $access = $perms['access_projects'] ?? [];
            if (in_array($id, $access)) {
                $currentMembers[] = (int)$u['id'];
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Use existing start_date or set to today if empty
            $start = $_POST['start_date'];
            if (empty($start)) {
                $start = $project['start_date'] ?: date('Y-m-d');
            }
            // Validate date order
            $endParam = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
            if ($endParam && $start && $endParam < $start) {
                $endParam = $start;
            }
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'] ?? 'new',
                'start_date' => $start,
                'end_date' => $endParam,
            ];
            $projectModel->update($id, $data);
            // Update members list
            $memberIds = $_POST['members'] ?? [];
            // If 'all' selected, set to all non-admin user ids
            if (in_array('all', $memberIds)) {
                $memberIds = [];
                foreach ($nonAdmins as $u) {
                    $memberIds[] = (string)$u['id'];
                }
            }
            // Iterate over non-admin users and update their access lists
            foreach ($nonAdmins as $u) {
                $uid = (int)$u['id'];
                $perms = get_user_permissions($uid);
                $access = $perms['access_projects'] ?? [];
                $shouldHave = in_array((string)$uid, $memberIds);
                $has = in_array($id, $access);
                if ($shouldHave && !$has) {
                    $access[] = $id;
                } elseif (!$shouldHave && $has) {
                    $access = array_values(array_diff($access, [$id]));
                }
                $perms['access_projects'] = $access;
                set_user_permissions($uid, $perms);
            }
            redirect('index.php?controller=project');
        }
        $this->render('projects/edit', [
            'project' => $project,
            'users' => $nonAdmins,
            'currentMembers' => $currentMembers,
        ]);
    }

    /**
     * Delete a project.
     */
    public function delete(): void
    {
        $this->requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        if (!user_can('access_project', $id) || !user_can('delete_project')) {
            $this->render('errors/no_permission');
            return;
        }
        $projectModel = $this->loadModel('Project');
        $projectModel->delete($id);
        // Remove this project from users' access lists
        $userModel = $this->loadModel('User');
        $allUsers = $userModel->all();
        foreach ($allUsers as $u) {
            if ((int)$u['role_id'] === 1) {
                continue;
            }
            $uid = (int)$u['id'];
            $perms = get_user_permissions($uid);
            $access = $perms['access_projects'] ?? [];
            if (is_array($access) && in_array($id, $access)) {
                $access = array_values(array_diff($access, [$id]));
                $perms['access_projects'] = $access;
                set_user_permissions($uid, $perms);
            }
        }
        redirect('index.php?controller=project');
    }
}