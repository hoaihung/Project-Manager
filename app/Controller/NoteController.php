<?php
namespace app\Controller;

use app\Core\Controller;
use app\Model\Note;
use app\Model\Task;
use app\Model\User;
use app\Model\Project;

// Include helpers for translations and permissions
require_once __DIR__ . '/../helpers.php';

/**
 * Class NoteController
 *
 * Handles CRUD operations for notes.  Notes can be created
 * independently, associated with projects and linked to multiple
 * tasks.  Markdown formatting is supported within note content.
 */
class NoteController extends Controller
{
    /**
     * Determine if a given note can be viewed by the specified user based on
     * project membership, note author and custom permissions.
     *
     * A note is viewable if:
     *  - the user is an admin (role_name = 'admin'), or
     *  - the user created the note, or
     *  - the note belongs to a project the user can access (access_project permission), or
     *  - the user has the special 'view_any_note' permission.
     * Global notes (project_id null) are only visible to their author, admins or
     * users with 'view_any_note'.
     *
     * @param array $note
     * @param int $userId
     * @return bool
     */
    private function canViewNote(array $note, int $userId): bool
    {
        // Load current user
        $userModel = $this->loadModel('User');
        $user = $userModel->findById($userId);
        if (!$user) {
            return false;
        }
        // Admins always can view
        if ($user['role_name'] === 'admin') {
            return true;
        }
        // Author can view
        if ((int)$note['user_id'] === $userId) {
            return true;
        }
        // Check special permission to view any note
        if (user_can('view_any_note')) {
            return true;
        }
        $projectId = $note['project_id'] ? (int)$note['project_id'] : null;
        // If the note belongs to a project and the user can access that project
        if ($projectId && user_can('access_project', $projectId)) {
            return true;
        }
        // If note is associated with tasks, allow users assigned to those tasks to view
        // This covers notes attached to tasks even when project permission is not granted.
        // Load tasks for this note and check assignment
        $noteModel = $this->loadModel('Note');
        $taskUserModel = $this->loadModel('TaskUser');
        $tasks = $noteModel->getTasks($note['id']);
        if (!empty($tasks)) {
            foreach ($tasks as $task) {
                // Check if user is assigned to the task
                $assignees = $taskUserModel->getUsersByTask((int)$task['id']);
                foreach ($assignees as $assignee) {
                    if ((int)$assignee['id'] === $userId) {
                        return true;
                    }
                }
                // Additionally, if the user can access the task's project, permit view
                if (user_can('access_project', (int)$task['project_id'])) {
                    return true;
                }
            }
        }
        // At this point, the note is either global or belongs to a project the user can't access and isn't assigned to a task
        return false;
    }
    /**
     * List notes.  Optionally filter by project ID (passed as
     * `project_id` in the query string).  If a project is provided,
     * both project notes and global notes (project_id is NULL) will
     * be shown.  Requires user to be logged in.
     */
    public function index(): void
    {
        $this->requireLogin();
        // Treat empty project_id ("" or null) as null (no filter).  Casting an empty string to
        // int yields 0, which incorrectly filters only non‑existent project ID 0.  So we
        // explicitly check for empty value before casting.
        $projectId = null;
        if (isset($_GET['project_id']) && $_GET['project_id'] !== '') {
            $projectId = (int)$_GET['project_id'];
            // If the cast yields 0, treat as null (all projects) because no project has ID 0
            if ($projectId <= 0) {
                $projectId = null;
            }
        }
        $noteModel = $this->loadModel('Note');
        $notes = $noteModel->getAll($projectId);
        // Filter notes based on view permissions
        $filteredNotes = [];
        $currentUserId = $_SESSION['user_id'];
        foreach ($notes as $n) {
            if ($this->canViewNote($n, $currentUserId)) {
                $filteredNotes[] = $n;
            }
        }
        // For project filter dropdown, load all projects to allow switching
        $projectModel = $this->loadModel('Project');
        $projects = $projectModel->all();
        $this->render('notes/index', [
            'notes'   => $filteredNotes,
            'projects' => $projects,
            'currentProjectId' => $projectId,
        ]);
    }

    /**
     * Show a single note.  Displays its content rendered from markdown
     * into HTML.  Requires login.
     */
    public function view(): void
    {
        $this->requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id < 1) {
            redirect('index.php?controller=note');
        }
        $noteModel = $this->loadModel('Note');
        $note = $noteModel->findById($id);
        if (!$note) {
            redirect('index.php?controller=note');
        }
        // Authorisation check: ensure current user can view
        $currentUserId = $_SESSION['user_id'];
        if (!$this->canViewNote($note, $currentUserId)) {
            $this->render('errors/no_permission');
            return;
        }
        // Get tasks associated with this note
        $tasks = $noteModel->getTasks($id);
        $this->render('notes/view', [
            'note'  => $note,
            'tasks' => $tasks,
        ]);
    }

    /**
     * Create a new note.  GET displays the form; POST handles saving.
     * The form allows selecting a project, entering title/content and
     * choosing tasks to link.  Requires login.
     */
    public function create(): void
    {
        $this->requireLogin();
        $noteModel = $this->loadModel('Note');
        $taskModel = $this->loadModel('Task');
        $projectModel = $this->loadModel('Project');
        $projects = $projectModel->all();
        // Determine project context for filtering tasks list.  A project ID may
        // be explicitly supplied via query parameter or implicitly via
        // task_id if creating a note from a task.  If task_id is provided,
        // load the task to determine its project and preselect it.
        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : null;
        $preSelectedTaskIds = [];
        if (isset($_GET['task_id'])) {
            $tid = (int)$_GET['task_id'];
            if ($tid > 0) {
                $task = $taskModel->findById($tid);
                if ($task) {
                    $projectId = $projectId ?: (int)($task['project_id'] ?? null);
                    $preSelectedTaskIds[] = $tid;
                    // Ensure user has access to the project of this task
                    if (!user_can('access_project', (int)$task['project_id'])) {
                        $this->render('errors/no_permission');
                        return;
                    }
                }
            }
        }
        $tasksForProject = [];
        if ($projectId) {
            // Ensure user can access selected project
            if (!user_can('access_project', $projectId)) {
                $this->render('errors/no_permission');
                return;
            }
            $grouped = $taskModel->getByProject($projectId);
            // Flatten tasks list for selection (include subtasks)
            foreach ($grouped as $statusList) {
                foreach ($statusList as $t) {
                    $tasksForProject[] = $t;
                    if (!empty($t['subtasks'])) {
                        foreach ($t['subtasks'] as $st) {
                            $tasksForProject[] = $st;
                        }
                    }
                }
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = trim($_POST['content'] ?? '');
            if ($content === '') {
                $_SESSION['error'] = __('content_required') ?: 'Content is required.';
            } else {
                $projIdInput = $_POST['project_id'] ? (int)$_POST['project_id'] : null;
                // Check user has access to chosen project if set
                if ($projIdInput && !user_can('access_project', $projIdInput)) {
                    $this->render('errors/no_permission');
                    return;
                }
                // Check tasks selected belong to accessible project(s)
                $selectedTasks = $_POST['task_ids'] ?? [];
                if (is_array($selectedTasks)) {
                    foreach ($selectedTasks as $tidSel) {
                        $taskSel = $taskModel->findById((int)$tidSel);
                        if ($taskSel && !user_can('access_project', (int)$taskSel['project_id'])) {
                            $this->render('errors/no_permission');
                            return;
                        }
                    }
                }
                $noteData = [
                    'project_id' => $projIdInput,
                    'user_id'    => $_SESSION['user_id'],
                    'title'      => $_POST['title'] ?? null,
                    'content'    => $content,
                ];
                $noteId = $noteModel->create($noteData);
                // Save task links
                if (is_array($selectedTasks)) {
                    $noteModel->setTasks($noteId, array_map('intval', $selectedTasks));
                }
                redirect('index.php?controller=note&action=view&id=' . $noteId);
                return;
            }
        }
        $this->render('notes/create', [
            'projects'          => $projects,
            'projectId'         => $projectId,
            'tasks'             => $tasksForProject,
            'preSelectedTaskIds' => $preSelectedTaskIds,
        ]);
    }

    /**
     * AJAX endpoint to create a new note associated with an optional project
     * and/or task. Accepts POST parameters:
     * - title (optional)
     * - content (required if title blank)
     * - project_id (optional)
     * - task_id (optional)
     * Returns JSON with note info on success.
     */
    public function createAjax(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $title   = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $projectId = isset($_POST['project_id']) && $_POST['project_id'] !== '' ? (int)$_POST['project_id'] : null;
        $taskId = isset($_POST['task_id']) && $_POST['task_id'] !== '' ? (int)$_POST['task_id'] : null;
        if ($title === '' && $content === '') {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Content is required']);
            return;
        }
        // Validate project
        if ($projectId) {
            if (!user_can('access_project', $projectId)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Forbidden']);
                return;
            }
        }
        // Validate task
        $taskModel = $this->loadModel('Task');
        if ($taskId) {
            $task = $taskModel->findById($taskId);
            if (!$task) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Task not found']);
                return;
            }
            if (!user_can('access_project', $task['project_id'])) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Forbidden']);
                return;
            }
            // If projectId not given, default to task's project
            if (!$projectId) {
                $projectId = (int)$task['project_id'];
            }
        }
        $noteModel = $this->loadModel('Note');
        $noteId = $noteModel->create([
            'project_id' => $projectId,
            'user_id'    => $currentUserId,
            'title'      => $title !== '' ? $title : null,
            'content'    => $content,
        ]);
        // Attach note to task if provided
        if ($taskId) {
            $noteModel->setTasks($noteId, [$taskId]);
        }
        $noteTitle = $title !== '' ? $title : mb_substr(strip_tags($content), 0, 30) . '…';
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'note' => ['id' => $noteId, 'title' => $noteTitle]]);
    }

    /**
     * Edit an existing note.  GET displays the form; POST saves changes.
     * Requires login.  Only the author or an admin may edit the note.
     */
    public function edit(): void
    {
        $this->requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id < 1) {
            redirect('index.php?controller=note');
        }
        $noteModel = $this->loadModel('Note');
        $note = $noteModel->findById($id);
        if (!$note) {
            redirect('index.php?controller=note');
        }
        // Check permissions: only author or admin can edit
        $userModel = $this->loadModel('User');
        $currentUser = $userModel->findById($_SESSION['user_id']);
        if ($note['user_id'] != $_SESSION['user_id'] && $currentUser['role_name'] !== 'admin') {
            $this->render('errors/no_permission');
            return;
        }
        $projectModel = $this->loadModel('Project');
        $projects = $projectModel->all();
        $taskModel = $this->loadModel('Task');
        // Determine tasks list for current (or selected) project
        $tasksForProject = [];
        $projectId = $note['project_id'];
        if ($projectId) {
            $grouped = $taskModel->getByProject($projectId);
            foreach ($grouped as $statusList) {
                foreach ($statusList as $t) {
                    $tasksForProject[] = $t;
                    if (!empty($t['subtasks'])) {
                        foreach ($t['subtasks'] as $st) {
                            $tasksForProject[] = $st;
                        }
                    }
                }
            }
        }
        // Get existing linked tasks IDs
        $linkedTasks = $noteModel->getTasks($id);
        $linkedIds = array_map(function ($t) {
            return $t['id'];
        }, $linkedTasks);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = trim($_POST['content'] ?? '');
            if ($content === '') {
                $_SESSION['error'] = __('content_required') ?: 'Content is required.';
            } else {
                $updateData = [
                    'project_id' => $_POST['project_id'] ? (int)$_POST['project_id'] : null,
                    'title'      => $_POST['title'] ?? null,
                    'content'    => $content,
                ];
                $noteModel->update($id, $updateData);
                // Update linked tasks
                $selected = $_POST['task_ids'] ?? [];
                if (is_array($selected)) {
                    $noteModel->setTasks($id, array_map('intval', $selected));
                }
                redirect('index.php?controller=note&action=view&id=' . $id);
                return;
            }
        }
        $this->render('notes/edit', [
            'note'       => $note,
            'projects'   => $projects,
            'tasks'      => $tasksForProject,
            'linkedIds'  => $linkedIds,
        ]);
    }

    /**
     * Delete a note.  Requires login and appropriate permission (author
     * or admin).  After deletion, redirects back to the notes index.
     */
    public function delete(): void
    {
        $this->requireLogin();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id < 1) {
            redirect('index.php?controller=note');
        }
        $noteModel = $this->loadModel('Note');
        $note = $noteModel->findById($id);
        if (!$note) {
            redirect('index.php?controller=note');
        }
        $userModel = $this->loadModel('User');
        $currentUser = $userModel->findById($_SESSION['user_id']);
        if ($note['user_id'] != $_SESSION['user_id'] && $currentUser['role_name'] !== 'admin') {
            $this->render('errors/no_permission');
            return;
        }
        $noteModel->delete($id);
        redirect('index.php?controller=note');
    }

    /**
     * AJAX endpoint: return a flat list of tasks for a given project as JSON.
     * Only accessible to logged‑in users. If project_id is missing or user
     * cannot access the project, an empty array is returned. Each task
     * record includes id and name. This endpoint is used by the note
     * create/edit forms to populate the task selection dynamically when
     * choosing a project without reloading the page.
     */
    public function tasks(): void
    {
        $this->requireLogin();
        header('Content-Type: application/json');
        $projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
        if ($projectId < 1 || !user_can('access_project', $projectId)) {
            echo json_encode([]);
            return;
        }
        $taskModel = $this->loadModel('Task');
        // Use getByProject to get tasks grouped by status, then flatten
        $grouped = $taskModel->getByProject($projectId);
        $tasks = [];
        foreach ($grouped as $statusList) {
            foreach ($statusList as $t) {
                $tasks[] = ['id' => $t['id'], 'name' => $t['name']];
                if (!empty($t['subtasks'])) {
                    foreach ($t['subtasks'] as $st) {
                        $tasks[] = ['id' => $st['id'], 'name' => $st['name']];
                    }
                }
            }
        }
        echo json_encode($tasks);
    }
}