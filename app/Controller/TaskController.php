<?php
namespace app\Controller;

use app\Core\Controller;

// Include permission helper functions
require_once __DIR__ . '/../helpers.php';

/**
 * Class TaskController
 *
 * Handles task operations such as listing tasks on a project (in
 * Kanban, calendar or list views), creating, updating, deleting and
 * reordering tasks.
 */
class TaskController extends Controller
{
    /**
     * Display tasks for a given project. Supports multiple views via
     * query string parameter `view` (kanban, calendar, list).
     */
    public function index(): void
    {
        $this->requireLogin();
        $projectId = (int)($_GET['project_id'] ?? 0);
        if ($projectId < 1) {
            redirect('index.php?controller=project');
        }
        $projectModel = $this->loadModel('Project');
        $project = $projectModel->findById($projectId);
        if (!$project) {
            redirect('index.php?controller=project');
        }
        // Permission check: user must be allowed to access this project
        if (!user_can('access_project', $projectId)) {
            $this->render('errors/no_permission');
            return;
        }
        $taskModel = $this->loadModel('Task');
        $tasks = $taskModel->getByProject($projectId);

        // Apply filters (tag, user, priority, date range) if present in query string
        $tagFilter = isset($_GET['tag_filter']) ? trim($_GET['tag_filter']) : '';
        $userFilter = isset($_GET['user_filter']) && $_GET['user_filter'] !== '' ? (int)$_GET['user_filter'] : 0;
        $priorityFilter = isset($_GET['priority_filter']) ? trim($_GET['priority_filter']) : '';
        $startFilter = $_GET['start_filter'] ?? '';
        $endFilter = $_GET['end_filter'] ?? '';
        if ($tagFilter !== '' || $userFilter || $priorityFilter !== '' || $startFilter !== '' || $endFilter !== '') {
            $matches = function ($task) use ($tagFilter, $userFilter, $priorityFilter, $startFilter, $endFilter) {
                // Tag filter
                if ($tagFilter !== '') {
                    $tags = $task['tags'] ?? '';
                    if (stripos($tags, $tagFilter) === false) {
                        return false;
                    }
                }
                // User filter (assignee). If userFilter specified, ensure the selected user is among assignee_ids
                if ($userFilter) {
                    $ids = $task['assignee_ids'] ?? [];
                    // allow fallback to single assigned_to if no assignee_ids defined
                    if (empty($ids) && isset($task['assigned_to'])) {
                        $ids = [(int)$task['assigned_to']];
                    }
                    if (!in_array($userFilter, $ids)) {
                        return false;
                    }
                }
                // Priority filter
                if ($priorityFilter !== '' && (!isset($task['priority']) || $task['priority'] !== $priorityFilter)) {
                    return false;
                }
                // Date range filter (due_date)
                if ($startFilter !== '' || $endFilter !== '') {
                    $due = $task['due_date'] ?? '';
                    if ($due === '') {
                        return false;
                    }
                    if ($startFilter !== '' && $due < $startFilter) {
                        return false;
                    }
                    if ($endFilter !== '' && $due > $endFilter) {
                        return false;
                    }
                }
                return true;
            };
            // Filter tasks grouped by status
            foreach ($tasks as $statusKey => $items) {
                $filteredItems = [];
                foreach ($items as $task) {
                    // Filter subtasks
                    if (!empty($task['subtasks'])) {
                        $newSubs = [];
                        foreach ($task['subtasks'] as $sub) {
                            if ($matches($sub)) {
                                $newSubs[] = $sub;
                            }
                        }
                        $task['subtasks'] = $newSubs;
                    }
                    if ($matches($task) || (!empty($task['subtasks']))) {
                        $filteredItems[] = $task;
                    }
                }
                $tasks[$statusKey] = $filteredItems;
            }
        }
        $view = $_GET['view'] ?? 'kanban';

        // Determine grouping mode for subtasks.  If the user explicitly passes a
        // `group` query parameter we persist that choice into the session so
        // subsequent navigations default to the user’s last selection.  When
        // there is no query parameter, fall back to the value stored in
        // $_SESSION or default to 'nested'.
        $groupParam = $_GET['group'] ?? null;
        if ($groupParam !== null) {
            $_SESSION['task_group_mode'] = $groupParam;
        }
        $groupMode = $_SESSION['task_group_mode'] ?? 'nested';

        // Optionally flatten subtasks into their own statuses.  In flat mode,
        // each subtask is treated as a separate item in the column matching
        // its own status.
        if ($view === 'kanban' && $groupMode === 'flat') {
            // When the user chooses to display subtasks separately (flat mode),
            // produce a flattened list of tasks keyed by status. Each top‑level
            // task is still included but its nested subtasks array is cleared to
            // avoid rendering nested lists. We retain the original subtask
            // counters on the parent so that the UI can display how many
            // subtasks exist and the completion progress. Subtasks themselves
            // become standalone items and carry a flag via `is_subtask` so the
            // view can style them appropriately. They also keep their
            // parent_id property so that operations like creating additional
            // subtasks still target the correct parent.
            $flatTasks = [
                'todo' => [],
                'in_progress' => [],
                'bug_review' => [],
                'done' => [],
            ];
            foreach ($tasks as $status => $items) {
                foreach ($items as $t) {
                    // Clone the top‑level task, strip nested subtasks but retain
                    // the counts. Mark it explicitly as not a subtask. The
                    // parent_name is cleared for consistency.
                    $clone = $t;
                    $clone['subtasks'] = [];
                    $clone['is_subtask'] = false;
                    $clone['parent_name'] = null;
                    $flatTasks[$clone['status']][] = $clone;
                    // Flatten each direct subtask into its own row. They retain
                    // their parent_id field so the application still knows
                    // which parent they belong to.  We clear their own
                    // subtasks and reset subtask counters since subtasks do
                    // not have nested children in this implementation. We also
                    // record the parent name for display in the UI.
                    if (!empty($t['subtasks'])) {
                        foreach ($t['subtasks'] as $sub) {
                            $subClone = $sub;
                            $subClone['is_subtask'] = true;
                            $subClone['parent_name'] = $t['name'];
                            $subClone['subtasks'] = [];
                            // subtasks should not display progress of their own subtasks
                            $subClone['subtask_total'] = 0;
                            $subClone['subtask_done'] = 0;
                            $flatTasks[$subClone['status']][] = $subClone;
                        }
                    }
                }
            }
            $tasks = $flatTasks;
        }
        // Load all users for potential assignment in some views
        $userModel = $this->loadModel('User');
        $users = $userModel->all();
        // Retrieve all projects to provide quick navigation between projects
        $projectModel = $this->loadModel('Project');
        $allProjects = $projectModel->all();
        // Load dependencies for flow view
        $depModel = $this->loadModel('TaskDependency');
        $dependencies = $depModel->getByProject($projectId);
        $this->render('tasks/index', [
            'project' => $project,
            'tasks' => $tasks,
            'view' => $view,
            'users' => $users,
            'allProjects' => $allProjects,
            'dependencies' => $dependencies,
        ]);
    }

    /**
     * Show form to create a new task and handle submission.
     */
    public function create(): void
    {
        $this->requireLogin();
        $projectId = (int)($_GET['project_id'] ?? 0);
        if ($projectId < 1) {
            redirect('index.php?controller=project');
        }
        $taskModel = $this->loadModel('Task');
        $userModel = $this->loadModel('User');
        $allUsers = $userModel->all();
        // Filter assignment options: only users with access to this project or admins
        $users = [];
        foreach ($allUsers as $u) {
            if ((int)$u['role_id'] === 1) {
                // Always include admins
                $users[] = $u;
            } else {
                $perms = get_user_permissions((int)$u['id']);
                $access = $perms['access_projects'] ?? [];
                if (is_array($access) && in_array($projectId, $access)) {
                    $users[] = $u;
                }
            }
        }
        $parentTaskId = $_GET['parent_id'] ?? null;
        // If status is provided via query (e.g. from Kanban column button), use it to preselect status in form
        $statusParam = $_GET['status'] ?? 'todo';

        // Permission check: ensure user can access this project and edit tasks
        if (!user_can('access_project', $projectId) || !user_can('edit_task')) {
            $this->render('errors/no_permission');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Build start and due dates ensuring start <= due
            $sd = !empty($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d');
            $dd = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
            if ($dd && $sd && $dd < $sd) {
                $dd = $sd;
            }
            // Collect assignees (multi-select). Determine primary assigned_to as the first selected user for
            // backwards compatibility. If no assignees selected, assigned_to remains null.
            $assignees = isset($_POST['assignees']) && is_array($_POST['assignees']) ? array_filter($_POST['assignees']) : [];
            $primaryAssignee = null;
            if (!empty($assignees)) {
                // Ensure numeric values and pick the first one
                $clean = array_values(array_filter(array_map('intval', $assignees), function ($v) { return $v > 0; }));
                if (!empty($clean)) {
                    $primaryAssignee = $clean[0];
                    $assignees = $clean;
                } else {
                    $assignees = [];
                }
            }
            $data = [
                'project_id' => $projectId,
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'] ?? 'todo',
                'start_date' => $sd,
                'due_date' => $dd,
                'assigned_to' => $primaryAssignee,
                'parent_id' => (function() use ($taskModel, $projectId) {
                    if (empty($_POST['parent_id'])) return null;
                    $pid = (int)$_POST['parent_id'];
                    $parent = $taskModel->findById($pid);
                    if ($parent && $parent['project_id'] == $projectId) {
                        return $pid;
                    }
                    return null;
                })(),
                'priority' => $_POST['priority'] ?? 'normal',
                'tags' => !empty($_POST['tags']) ? $_POST['tags'] : null,
            ];
            $newId = $taskModel->create($data);
            // Persist multi assignee relationships
            if (!empty($assignees)) {
                $taskModel->setAssignees($newId, $assignees);
            }
            // Handle file attachments if any
            if (!empty($_FILES['attachments']['name'][0])) {
                $fileModel = $this->loadModel('File');
                $uploadDir = 'assets/uploads/';
                // Ensure directory exists
                if (!is_dir(__DIR__ . '/../../public/' . $uploadDir)) {
                    mkdir(__DIR__ . '/../../public/' . $uploadDir, 0755, true);
                }
                foreach ($_FILES['attachments']['name'] as $index => $name) {
                    if ($_FILES['attachments']['error'][$index] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['attachments']['tmp_name'][$index];
                        $safeName = uniqid() . '_' . basename($name);
                        $destPath = __DIR__ . '/../../public/' . $uploadDir . $safeName;
                        if (move_uploaded_file($tmpName, $destPath)) {
                            $fileModel->create([
                                'task_id' => $newId,
                                'file_name' => $name,
                                'file_path' => $uploadDir . $safeName,
                            ]);
                        }
                    }
                }
            }
            // Handle external links associated with this task
            if (!empty($_POST['link_urls']) && is_array($_POST['link_urls'])) {
                $linkModel = $this->loadModel('TaskLink');
                $linksData = [];
                $names = $_POST['link_names'] ?? [];
                foreach ($_POST['link_urls'] as $i => $url) {
                    $url = trim($url);
                    if ($url !== '') {
                        $name = isset($names[$i]) ? trim($names[$i]) : null;
                        $linksData[] = [
                            'name' => $name,
                            'url'  => $url,
                        ];
                    }
                }
                if (!empty($linksData)) {
                    $linkModel->replaceForTask($newId, $linksData);
                }
            }
            // Handle checklist items for this task
            if (isset($_POST['checklist_content']) && is_array($_POST['checklist_content'])) {
                $checklistModel = $this->loadModel('ChecklistItem');
                $items = [];
                foreach ($_POST['checklist_content'] as $i => $content) {
                    $content = trim($content);
                    if ($content !== '') {
                        $done = isset($_POST['checklist_done'][$i]) ? 1 : 0;
                        $items[] = [
                            'content'    => $content,
                            'is_done'    => $done,
                            'sort_order' => $i + 1,
                        ];
                    }
                }
                if (!empty($items)) {
                    $checklistModel->replaceForTask($newId, $items);
                }

            // Attach any selected existing notes to this new task
            if (!empty($_POST['existing_notes']) && is_array($_POST['existing_notes'])) {
                $noteIds = array_map('intval', $_POST['existing_notes']);
                $noteModel = $this->loadModel('Note');
                foreach ($noteIds as $noteId) {
                    if ($noteId > 0) {
                        $note = $noteModel->findById($noteId);
                        if ($note && note_can_view($note, $_SESSION['user_id'])) {
                            $currentTasks = $noteModel->getTasks($noteId);
                            $ids = [];
                            foreach ($currentTasks as $t) {
                                $ids[] = (int)$t['id'];
                            }
                            if (!in_array($newId, $ids)) {
                                $ids[] = $newId;
                                $noteModel->setTasks($noteId, $ids);
                            }
                        }
                    }
                }
            }
            }
            // Write to audit log
            $logModel = $this->loadModel('Log');
            $logModel->create([
                'user_id' => $_SESSION['user_id'],
                'action' => 'create_task',
                'details' => 'Created task #' . $newId,
            ]);
            // After creation, redirect to edit page of the new task so attachments and comments can be added
            redirect('index.php?controller=task&action=edit&id=' . $newId);
        }
        // Build parent options for selecting a parent task (top‑level tasks only)
        $parentOptions = [];
        $projTasks = $taskModel->getByProject($projectId);
        foreach ($projTasks as $st => $items) {
            foreach ($items as $t) {
                if (empty($t['parent_id'])) {
                    $parentOptions[] = $t;
                }
            }
        }
        // Determine available notes that the user can attach to this new task
        $availableNotes = [];
        try {
            $noteModel = $this->loadModel('Note');
            $notesAll  = $noteModel->getAll($projectId);
            $currentUserId = $_SESSION['user_id'];
            foreach ($notesAll as $n) {
                if (note_can_view($n, $currentUserId)) {
                    $availableNotes[] = $n;
                }
            }
        } catch (\Throwable $e) {
            $availableNotes = [];
        }
        // Provide empty arrays for links and checklist items when creating a new task
        $this->render('tasks/create', [
            'project_id'      => $projectId,
            'users'           => $users,
            'parent_id'       => $parentTaskId,
            'statusParam'     => $statusParam,
            'parentOptions'   => $parentOptions,
            'links'           => [],
            'checklistItems'  => [],
            'availableNotes'  => $availableNotes,
        ]);
    }

    /**
     * Show form to edit an existing task and handle update.
     */
    public function edit(): void
    {
        $this->requireLogin();
        $taskId = (int)($_GET['id'] ?? 0);
        $taskModel = $this->loadModel('Task');
        $task = $taskModel->findById($taskId);
        if (!$task) {
            redirect('index.php');
        }
        // Extract assigned user IDs for prechecking checkboxes in the view. findById() now populates assignee_ids.
        $assignedUserIds = $task['assignee_ids'] ?? [];
        $projectId = $task['project_id'];
        // Permission check: ensure user can access project and edit tasks
        if (!user_can('access_project', $projectId) || !user_can('edit_task')) {
            $this->render('errors/no_permission');
            return;
        }
        $userModel = $this->loadModel('User');
        $allUsers = $userModel->all();
        // Filter assignment options: include admins and users assigned to this project
        $users = [];
        foreach ($allUsers as $u) {
            if ((int)$u['role_id'] === 1) {
                $users[] = $u;
            } else {
                $perms = get_user_permissions((int)$u['id']);
                $access = $perms['access_projects'] ?? [];
                if (is_array($access) && in_array($projectId, $access)) {
                    $users[] = $u;
                }
            }
        }
        // Prepare options for parent task selection (other tasks in the same project)
        $tasksForSelect = [];
        $projectTasks = $taskModel->getByProject($projectId);
        foreach ($projectTasks as $status => $items) {
            foreach ($items as $t) {
                // Only allow selecting a top‑level task (no parent) as parent
                if ($t['id'] != $taskId && empty($t['parent_id'])) {
                    $tasksForSelect[] = $t;
                }
            }
        }
        // Fetch subtasks of this task for ordering UI
        $subtasks = $taskModel->getSubtasks($taskId);
        $hasSubtasks = !empty($subtasks);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Build start and due dates ensuring start <= due
            $sd = !empty($_POST['start_date']) ? $_POST['start_date'] : ($task['start_date'] ?? date('Y-m-d'));
            $dd = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
            if ($dd && $sd && $dd < $sd) {
                $dd = $sd;
            }
            // Collect multi assignees and determine primary
            $assignees = isset($_POST['assignees']) && is_array($_POST['assignees']) ? array_filter($_POST['assignees']) : [];
            $primaryAssignee = null;
            if (!empty($assignees)) {
                $clean = array_values(array_filter(array_map('intval', $assignees), function ($v) { return $v > 0; }));
                if (!empty($clean)) {
                    $primaryAssignee = $clean[0];
                    $assignees = $clean;
                } else {
                    $assignees = [];
                }
            }
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'] ?? '',
                'status' => $_POST['status'],
                'start_date' => $sd,
                'due_date' => $dd,
                'assigned_to' => $primaryAssignee,
                // Only allow changing parent_id if the task currently has no subtasks
                'parent_id' => (function() use ($taskModel, $projectId, $taskId, $hasSubtasks) {
                    // If there are subtasks, disallow becoming a subtask
                    if ($hasSubtasks) {
                        return null;
                    }
                    if (empty($_POST['parent_id'])) return null;
                    $pid = (int)$_POST['parent_id'];
                    if ($pid == $taskId) {
                        return null;
                    }
                    $parent = $taskModel->findById($pid);
                    if ($parent && $parent['project_id'] == $projectId) {
                        return $pid;
                    }
                    return null;
                })(),
                'priority' => $_POST['priority'] ?? ($task['priority'] ?? 'normal'),
                'tags' => !empty($_POST['tags']) ? $_POST['tags'] : null,
            ];
            $taskModel->update($taskId, $data);
            // Update pivot assignments
            $taskModel->setAssignees($taskId, $assignees);
            // Handle new file attachments
            if (!empty($_FILES['attachments']['name'][0])) {
                $fileModel = $this->loadModel('File');
                $uploadDir = 'assets/uploads/';
                if (!is_dir(__DIR__ . '/../../public/' . $uploadDir)) {
                    mkdir(__DIR__ . '/../../public/' . $uploadDir, 0755, true);
                }
                foreach ($_FILES['attachments']['name'] as $index => $name) {
                    if ($_FILES['attachments']['error'][$index] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['attachments']['tmp_name'][$index];
                        $safeName = uniqid() . '_' . basename($name);
                        $destPath = __DIR__ . '/../../public/' . $uploadDir . $safeName;
                        if (move_uploaded_file($tmpName, $destPath)) {
                            $fileModel->create([
                                'task_id' => $taskId,
                                'file_name' => $name,
                                'file_path' => $uploadDir . $safeName,
                            ]);
                        }
                    }
                }
            }
            // Handle external links update for this task.  Replace existing
            // links with the submitted ones.  Fields are provided as
            // link_urls[] and link_names[].
            if (!empty($_POST['link_urls']) && is_array($_POST['link_urls'])) {
                $linkModel = $this->loadModel('TaskLink');
                $linksData = [];
                $names = $_POST['link_names'] ?? [];
                foreach ($_POST['link_urls'] as $i => $url) {
                    $url = trim($url);
                    if ($url !== '') {
                        $name = isset($names[$i]) ? trim($names[$i]) : null;
                        $linksData[] = [
                            'name' => $name,
                            'url'  => $url,
                        ];
                    }
                }
                $linkModel->replaceForTask($taskId, $linksData);
            }
            // Handle checklist update for this task.  Replace existing
            // checklist items with the submitted values.  Content and done
            // flags are keyed by index in the form.
            if (isset($_POST['checklist_content']) && is_array($_POST['checklist_content'])) {
                $checklistModel = $this->loadModel('ChecklistItem');
                $items = [];
                foreach ($_POST['checklist_content'] as $i => $content) {
                    $content = trim($content);
                    if ($content !== '') {
                        $done = isset($_POST['checklist_done'][$i]) ? 1 : 0;
                        $items[] = [
                            'content'    => $content,
                            'is_done'    => $done,
                            'sort_order' => $i + 1,
                        ];
                    }
                }
                $checklistModel->replaceForTask($taskId, $items);
            }
            // Write to audit log
            $logModel = $this->loadModel('Log');
            $logModel->create([
                'user_id' => $_SESSION['user_id'],
                'action' => 'update_task',
                'details' => 'Updated task #' . $taskId,
            ]);
            // If user requested to add a subtask, redirect to the subtask creation page after saving
            if (!empty($_POST['redirect_to_subtask']) && $_POST['redirect_to_subtask'] == '1') {
                // Determine return view from query
                $rv = $_GET['view'] ?? 'kanban';
                redirect('index.php?controller=task&action=create&project_id=' . $projectId . '&parent_id=' . $taskId . '&view=' . $rv);
            }
            // Otherwise stay on edit page after update
            redirect('index.php?controller=task&action=edit&id=' . $taskId);
        }
        // Load links and checklist items for this task so the view can display them
        $linkModel = $this->loadModel('TaskLink');
        $links = $linkModel->getByTask($taskId);
        $checklistModel = $this->loadModel('ChecklistItem');
        $checklistItems = $checklistModel->getByTask($taskId);
        // Load notes associated with this task to show in the side panel
        $noteModel = $this->loadModel('Note');
        try {
            $notesForTask = $noteModel->getByTask($taskId);
        } catch (\Throwable $e) {
            $notesForTask = [];
        }
        // Determine additional notes available to attach to this task.  Only
        // include notes the user has permission to view and which are not
        // already linked to this task.
        $availableNotes = [];
        try {
            $allNotes = $noteModel->getAll($projectId);
            $existingIds = array_map(function ($n) { return (int)$n['id']; }, $notesForTask);
            $currentUserId = $_SESSION['user_id'];
            foreach ($allNotes as $n) {
                $nid = (int)$n['id'];
                if (in_array($nid, $existingIds)) {
                    continue;
                }
                if (note_can_view($n, $currentUserId)) {
                    $availableNotes[] = $n;
                }
            }
        } catch (\Throwable $e) {
            $availableNotes = [];
        }
        $this->render('tasks/edit', [
            'task'            => $task,
            'users'           => $users,
            'parentOptions'   => $tasksForSelect,
            'subtasks'        => $subtasks,
            'hasSubtasks'     => $hasSubtasks,
            'assignedUserIds' => $assignedUserIds,
            'links'           => $links,
            'checklistItems'  => $checklistItems,
            'notesForTask'    => $notesForTask,
            'availableNotes'  => $availableNotes,
        ]);
    }

    /**
     * Delete a task.
     */
    public function delete(): void
    {
        $this->requireLogin();
        // Accept ID from GET or POST for deletion
        $taskId = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);
        if ($taskId < 1) {
            redirect('index.php');
        }
        $taskModel = $this->loadModel('Task');
        $task = $taskModel->findById($taskId);
        if (!$task) {
            redirect('index.php');
        }
        $projectId = (int)$task['project_id'];
        // Check access to the project. All project members may delete tasks; we no longer check delete_task permission.
        if (!user_can('access_project', $projectId)) {
            $this->render('errors/no_permission');
            return;
        }
        // If a GET request carries a delete_subtasks flag, treat it as a confirmed deletion (used by modal)
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_subtasks'])) {
            $deleteSubtasks = (int)$_GET['delete_subtasks'] === 1;
            // Gather original subtasks before performing deletion (only when we are not deleting them)
            $subtasksOriginal = [];
            if (!$deleteSubtasks) {
                $children = $taskModel->getSubtasks($taskId);
                foreach ($children as $child) {
                    $subtasksOriginal[] = $child['id'];
                }
            }
            // Perform soft delete: mark task (and optionally subtasks) as deleted
            $taskModel->softDelete($taskId, $deleteSubtasks);
            // Record deletion in logs
            $logModel = $this->loadModel('Log');
            $currentUserId = $_SESSION['user']['id'] ?? ($_SESSION['user_id'] ?? 0);
            $detailsArr = [
                'task_id' => $taskId,
                'task_name' => $task['name'],
                'project_id' => $projectId,
                'deleted_by' => $currentUserId,
                'delete_subtasks' => $deleteSubtasks,
                'original_subtasks' => $subtasksOriginal,
                // record the prior status of the task so it can be restored correctly
                'task_status' => $task['status'],
            ];
            $detailsString = 'task #' . $taskId . ' ' . json_encode($detailsArr);
            $logModel->create([
                'user_id' => $currentUserId,
                'action' => 'delete_task',
                'details' => $detailsString,
            ]);
            redirect('index.php?controller=task&project_id=' . $projectId);
            return;
        }
        // Handle POST: perform deletion when user has confirmed (legacy confirmation form)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $deleteSubtasks = !empty($_POST['delete_subtasks']);
            $confirmed = !empty($_POST['confirm_delete']);
            if (!$confirmed) {
                $_SESSION['error'] = __('delete_not_confirmed');
                redirect('index.php?controller=task&action=delete&id=' . $taskId);
            }
            // Gather original subtasks before deletion
            $subtasksOriginal = [];
            if (!$deleteSubtasks) {
                $children = $taskModel->getSubtasks($taskId);
                foreach ($children as $child) {
                    $subtasksOriginal[] = $child['id'];
                }
            }
            $taskModel->softDelete($taskId, $deleteSubtasks);
            $logModel = $this->loadModel('Log');
            $currentUserId = $_SESSION['user']['id'] ?? ($_SESSION['user_id'] ?? 0);
            $detailsArr = [
                'task_id' => $taskId,
                'task_name' => $task['name'],
                'project_id' => $projectId,
                'deleted_by' => $currentUserId,
                'delete_subtasks' => $deleteSubtasks,
                'original_subtasks' => $subtasksOriginal,
                'task_status' => $task['status'],
            ];
            $detailsString = 'task #' . $taskId . ' ' . json_encode($detailsArr);
            $logModel->create([
                'user_id' => $currentUserId,
                'action' => 'delete_task',
                'details' => $detailsString,
            ]);
            redirect('index.php?controller=task&project_id=' . $projectId);
            return;
        }
        // GET request: show deletion confirmation form
        $isSubtask = !empty($task['parent_id']);
        $this->render('tasks/confirm_delete', [
            'task' => $task,
            'isSubtask' => $isSubtask,
        ]);
    }

    /**
     * Update order and status of tasks via AJAX.
     */
    public function order(): void
    {
        $this->requireLogin();
        // Ensure the user has permission to reorder tasks (edit tasks).  Without
        // this check, a user could manipulate task ordering by sending
        // crafted AJAX requests.
        if (!user_can('edit_task')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => __('no_permission')]);
            return;
        }
        // Expect JSON: {status: [id,id,...], ...}
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        if (is_array($data)) {
            $taskModel = $this->loadModel('Task');
            $taskModel->updateOrder($data);
            // Log reorder
            $logModel = $this->loadModel('Log');
            $logModel->create([
                'user_id' => $_SESSION['user_id'],
                'action' => 'reorder_tasks',
                'details' => 'Updated task order via Kanban drag and drop',
            ]);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    /**
     * Provide events for calendar view (JSON)
     */
    public function events(): void
    {
        $this->requireLogin();
        $projectId = (int)($_GET['project_id'] ?? 0);
        $taskModel = $this->loadModel('Task');
        $events = $taskModel->getCalendarEvents($projectId);
        header('Content-Type: application/json');
        echo json_encode($events);
    }

    /**
     * Add a comment to a task.
     */
    public function comment(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php');
        }
        $taskId = (int)($_POST['task_id'] ?? 0);
        $commentText = trim($_POST['comment'] ?? '');
        if ($taskId > 0 && $commentText !== '') {
            $commentModel = $this->loadModel('Comment');
            $commentModel->create([
                'task_id' => $taskId,
                'user_id' => $_SESSION['user_id'],
                'comment' => $commentText,
            ]);
            // Log comment
            $logModel = $this->loadModel('Log');
            $logModel->create([
                'user_id' => $_SESSION['user_id'],
                'action' => 'add_comment',
                'details' => 'Added comment on task #' . $taskId,
            ]);
        }
        redirect('index.php?controller=task&action=edit&id=' . $taskId);
    }

    /**
     * Delete an attachment from a task.
     *
     * URL params: id = file id, task_id = id of the task to return to
     */
    public function deleteFile(): void
    {
        $this->requireLogin();
        $fileId = (int)($_GET['id'] ?? 0);
        $taskId = (int)($_GET['task_id'] ?? 0);
        if ($fileId > 0) {
            $fileModel = $this->loadModel('File');
            $file = $fileModel->findById($fileId);
            if ($file) {
                // Remove physical file
                $filePath = __DIR__ . '/../../public/' . $file['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $fileModel->deleteById($fileId);
                // Log deletion
                $logModel = $this->loadModel('Log');
                $logModel->create([
                    'user_id' => $_SESSION['user_id'],
                    'action' => 'delete_file',
                    'details' => 'Deleted attachment #' . $fileId . ' from task #' . $file['task_id'],
                ]);
                $taskId = $file['task_id'];
            }
        }
        if ($taskId > 0) {
            redirect('index.php?controller=task&action=edit&id=' . $taskId);
        } else {
            redirect('index.php');
        }
    }

    /**
     * Show the flow view for the current project by delegating to index()
     * with the appropriate view parameter. This method allows routing via
     * ?controller=task&action=flow&project_id=... without throwing a view
     * not found error.
     */
    public function flow(): void
    {
        // Preserve existing query parameters and override view
        $_GET['view'] = 'flow';
        $this->index();
    }

    /**
     * Show the Gantt view for the current project. See flow() for details.
     */
    public function gantt(): void
    {
        $_GET['view'] = 'gantt';
        $this->index();
    }

    /**
     * Handle AJAX request to reorder subtasks. Accepts JSON with keys:
     * parent_id: the parent task ID and order: array of subtask IDs in desired order.
     * Updates the sort_order field for each subtask. Responds with JSON.
     */
    public function reorderSubtasks(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $parentId = isset($input['parent_id']) ? (int)$input['parent_id'] : 0;
        $order    = isset($input['order']) ? $input['order'] : [];
        if ($parentId <= 0 || !is_array($order)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid data']);
            return;
        }
        $taskModel = $this->loadModel('Task');
        $pos = 1;
        foreach ($order as $tid) {
            $taskModel->updateSortOrder((int)$tid, $pos++);
        }
        echo json_encode(['status' => 'success']);
    }

    /**
     * AJAX endpoint to add a link to a task.
     * Accepts POST parameters `task_id`, `name` and `url`.
     * Returns JSON with updated list of links.
     */
    public function addLinkAjax(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }
        $taskId = (int)($_POST['task_id'] ?? 0);
        $name   = trim($_POST['name'] ?? '');
        $url    = trim($_POST['url'] ?? '');
        if ($taskId < 1 || ($name === '' && $url === '')) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid parameters']);
            return;
        }
        $taskModel = $this->loadModel('Task');
        $task = $taskModel->findById($taskId);
        if (!$task) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Task not found']);
            return;
        }
        // Check permission: user must be allowed to access the project
        if (!user_can('access_project', $task['project_id'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Forbidden']);
            return;
        }
        $linkModel = $this->loadModel('TaskLink');
        $linkModel->create($taskId, $name !== '' ? $name : null, $url);
        // Retrieve updated links
        $links = $linkModel->getByTask($taskId);
        $result = [];
        foreach ($links as $l) {
            $icon = 'link';
            if (!empty($l['url'])) {
                if (strpos($l['url'], 'docs.google') !== false) {
                    $icon = 'google-doc';
                } elseif (strpos($l['url'], 'sheets.google') !== false) {
                    $icon = 'google-sheet';
                }
            }
            $result[] = [
                'id'   => $l['id'],
                'name' => $l['name'],
                'url'  => $l['url'],
                'icon' => $icon,
            ];
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'links' => $result]);
    }

    /**
     * AJAX endpoint to delete a link from a task.
     * Accepts POST parameters `task_id` and `link_id`.
     */
    public function deleteLinkAjax(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }
        $taskId = (int)($_POST['task_id'] ?? 0);
        $linkId = (int)($_POST['link_id'] ?? 0);
        if ($taskId < 1 || $linkId < 1) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid parameters']);
            return;
        }
        $taskModel = $this->loadModel('Task');
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
        $linkModel = $this->loadModel('TaskLink');
        $belongs = false;
        foreach ($linkModel->getByTask($taskId) as $l) {
            if ((int)$l['id'] === $linkId) {
                $belongs = true;
                break;
            }
        }
        if (!$belongs) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Link does not belong to this task']);
            return;
        }
        $linkModel->delete($linkId);
        // Return updated links
        $links = $linkModel->getByTask($taskId);
        $result = [];
        foreach ($links as $l) {
            $icon = 'link';
            if (!empty($l['url'])) {
                if (strpos($l['url'], 'docs.google') !== false) {
                    $icon = 'google-doc';
                } elseif (strpos($l['url'], 'sheets.google') !== false) {
                    $icon = 'google-sheet';
                }
            }
            $result[] = [
                'id'   => $l['id'],
                'name' => $l['name'],
                'url'  => $l['url'],
                'icon' => $icon,
            ];
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'links' => $result]);
    }

    /**
     * AJAX endpoint to attach an existing note to a task.
     * Accepts POST parameters `task_id` and `note_id`.
     */
    public function addNoteToTaskAjax(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }
        $taskId = (int)($_POST['task_id'] ?? 0);
        $noteId = (int)($_POST['note_id'] ?? 0);
        if ($taskId < 1 || $noteId < 1) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid parameters']);
            return;
        }
        $taskModel = $this->loadModel('Task');
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
        $noteModel = $this->loadModel('Note');
        $note = $noteModel->findById($noteId);
        if (!$note) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Note not found']);
            return;
        }
        // Ensure current user can view this note
        $currentUserId = $_SESSION['user_id'] ?? 0;
        if (!note_can_view($note, (int)$currentUserId)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Cannot view note']);
            return;
        }
        // Attach this task to the note if not already
        $existingTasks = $noteModel->getTasks($noteId);
        $taskIds = array_map(function ($t) { return (int)$t['id']; }, $existingTasks);
        if (!in_array($taskId, $taskIds)) {
            $taskIds[] = $taskId;
            $noteModel->setTasks($noteId, $taskIds);
        }
        // Return updated notes for this task
        $notesForTask = $noteModel->getByTask($taskId);
        $result = [];
        foreach ($notesForTask as $n) {
            $title = $n['title'] ?: (mb_substr(strip_tags($n['content']), 0, 30) . '…');
            $result[] = [
                'id'    => $n['id'],
                'title' => $title,
            ];
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'notes' => $result]);
    }

    /**
     * AJAX endpoint to detach a note from a task.
     * Accepts POST parameters `task_id` and `note_id`.
     */
    public function removeNoteFromTaskAjax(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }
        $taskId = (int)($_POST['task_id'] ?? 0);
        $noteId = (int)($_POST['note_id'] ?? 0);
        if ($taskId < 1 || $noteId < 1) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid parameters']);
            return;
        }
        $taskModel = $this->loadModel('Task');
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
        $noteModel = $this->loadModel('Note');
        $note = $noteModel->findById($noteId);
        if (!$note) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Note not found']);
            return;
        }
        $noteModel->removeTask($noteId, $taskId);
        // Return updated notes
        $notesForTask = $noteModel->getByTask($taskId);
        $result = [];
        foreach ($notesForTask as $n) {
            $title = $n['title'] ?: (mb_substr(strip_tags($n['content']), 0, 30) . '…');
            $result[] = [
                'id'    => $n['id'],
                'title' => $title,
            ];
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'notes' => $result]);
    }

    /**
     * Attach an existing note to a task via GET request.
     *
     * This action expects `id` (task ID), `note_id` (note ID) and an optional
     * `view` parameter in the query string.  It ensures the user has
     * permission to access the task's project and edit tasks, and that the
     * note is visible to the current user via note_can_view().  If the
     * note is not already linked to the task, the relation is created.
     * Afterwards the user is redirected back to the task edit page with
     * the same view mode.  A log entry is created for auditing.
     */
    public function addNoteToTask(): void
    {
        $this->requireLogin();
        $taskId    = (int)($_GET['id'] ?? 0);
        $noteId    = (int)($_GET['note_id'] ?? 0);
        $returnView = $_GET['view'] ?? 'kanban';
        if ($taskId < 1 || $noteId < 1) {
            redirect('index.php');
        }
        $taskModel = $this->loadModel('Task');
        $noteModel = $this->loadModel('Note');
        $task = $taskModel->findById($taskId);
        if (!$task) {
            redirect('index.php');
        }
        $projectId = (int)$task['project_id'];
        // Only allow attaching if user can access the project and edit tasks
        if (!user_can('access_project', $projectId) || !user_can('edit_task')) {
            $this->render('errors/no_permission');
            return;
        }
        $note = $noteModel->findById($noteId);
        if (!$note) {
            redirect('index.php?controller=task&action=edit&id=' . $taskId . '&view=' . $returnView);
        }
        $currentUserId = $_SESSION['user_id'];
        // Check if user has permission to view the note
        if (!note_can_view($note, $currentUserId)) {
            $this->render('errors/no_permission');
            return;
        }
        // Merge task IDs for this note
        $existing = $noteModel->getTasks($noteId);
        $taskIds = [];
        foreach ($existing as $t) {
            $taskIds[] = (int)$t['id'];
        }
        if (!in_array($taskId, $taskIds)) {
            $taskIds[] = $taskId;
            $noteModel->setTasks($noteId, $taskIds);
            // Log the linking
            $logModel = $this->loadModel('Log');
            $logModel->create([
                'user_id' => $currentUserId,
                'action'  => 'attach_note',
                'details' => 'Attached note #' . $noteId . ' to task #' . $taskId,
            ]);
        }
        redirect('index.php?controller=task&action=edit&id=' . $taskId . '&view=' . $returnView);
    }

    /**
     * Detach a note from a task.  Expects `id` (task ID) and `note_id` in
     * the query string.  User must have permission to edit the task and
     * access its project.  After unlinking the note, redirects back to
     * the edit page preserving the current view mode.
     */
    public function removeNoteFromTask(): void
    {
        $this->requireLogin();
        $taskId = (int)($_GET['id'] ?? 0);
        $noteId = (int)($_GET['note_id'] ?? 0);
        $returnView = $_GET['view'] ?? 'kanban';
        if ($taskId < 1 || $noteId < 1) {
            redirect('index.php');
        }
        $taskModel = $this->loadModel('Task');
        $noteModel = $this->loadModel('Note');
        $task = $taskModel->findById($taskId);
        if (!$task) {
            redirect('index.php');
        }
        $projectId = (int)$task['project_id'];
        // Ensure user can access project and edit tasks
        if (!user_can('access_project', $projectId) || !user_can('edit_task')) {
            $this->render('errors/no_permission');
            return;
        }
        $note = $noteModel->findById($noteId);
        if (!$note) {
            redirect('index.php?controller=task&action=edit&id=' . $taskId . '&view=' . $returnView);
        }
        // Only unlink if note is attached to this task
        $noteModel->removeTask($noteId, $taskId);
        // Log
        $logModel = $this->loadModel('Log');
        $logModel->create([
            'user_id' => $_SESSION['user_id'],
            'action'  => 'detach_note',
            'details' => 'Detached note #' . $noteId . ' from task #' . $taskId,
        ]);
        redirect('index.php?controller=task&action=edit&id=' . $taskId . '&view=' . $returnView);
    }

    /**
     * Display list of tasks that have been soft deleted. Only administrators
     * can access this view. Shows who deleted the task and offers options
     * to restore or permanently delete each task.
     */
    public function trash(): void
    {
        $this->requireLogin();
        $currentUser = $_SESSION['user'] ?? null;
        if (!$currentUser || (int)($currentUser['role_id'] ?? 0) !== 1) {
            $this->render('errors/no_permission');
            return;
        }
        $taskModel = $this->loadModel('Task');
        $logModel  = $this->loadModel('Log');
        $userModel = $this->loadModel('User');
        // Retrieve deleted tasks with metadata. Each row may include deleted_by_user and deleted_at
        $tasks = $taskModel->getDeletedTasksWithInfo();
        // Prepare deletionInfo and user name resolution
        $deletionInfo = [];
        $deletedByNames = [];
        foreach ($tasks as &$t) {
            // If metadata from the joined logs exists, populate deletion info
            $deletedByUser = $t['deleted_by_user'] ?? null;
            $deletedAt     = $t['deleted_at'] ?? null;
            $detailsStr    = $t['delete_details'] ?? null;
            $originalSubs  = [];
            $deleteSubs    = false;
            if ($detailsStr) {
                // detailsStr begins with prefix like "task #ID {json}". Strip the prefix to get JSON
                $jsonPart = $detailsStr;
                if (preg_match('/^task #\d+\s+(.+)/', $detailsStr, $m)) {
                    $jsonPart = $m[1];
                }
                $details = json_decode($jsonPart, true);
                if (is_array($details)) {
                    $originalSubs = $details['original_subtasks'] ?? [];
                    $deleteSubs   = $details['delete_subtasks'] ?? false;
                    // In case deleted_by is recorded inside details (older logs), prefer that value
                    if (isset($details['deleted_by'])) {
                        $deletedByUser = $details['deleted_by'];
                    }
                    if (isset($details['task_status']) && !isset($t['deleted_prev_status'])) {
                        // Store original status for restore
                        $t['deleted_prev_status'] = $details['task_status'];
                    }
                }
            }
            $deletionInfo[$t['id']] = [
                'deleted_by' => $deletedByUser,
                'timestamp'  => $deletedAt,
                'original_subtasks' => $originalSubs,
                'delete_subtasks' => $deleteSubs,
            ];
            // Resolve user name
            if ($deletedByUser && !isset($deletedByNames[$deletedByUser])) {
                $user = $userModel->findById((int)$deletedByUser);
                $deletedByNames[$deletedByUser] = $user ? $user['full_name'] : '';
            }
        }
        $this->render('tasks/trash', [
            'tasks'          => $tasks,
            'deletionInfo'   => $deletionInfo,
            'deletedByNames' => $deletedByNames,
        ]);
    }

    /**
     * Restore a soft deleted task. Admins only.
     */
    public function restore(): void
    {
        $this->requireLogin();
        $taskId = (int)($_GET['id'] ?? 0);
        $currentUser = $_SESSION['user'] ?? null;
        if ($taskId < 1 || !$currentUser || (int)($currentUser['role_id'] ?? 0) !== 1) {
            $this->render('errors/no_permission');
            return;
        }
        $taskModel = $this->loadModel('Task');
        $logModel  = $this->loadModel('Log');
        // Restore the task to its previous status. Default to 'todo' if status not recorded.
        $prevStatus = 'todo';
        // Retrieve deletion log to get stored status and original subtasks
        $logs = $logModel->getByTask($taskId);
        $deletionDetails = null;
        foreach ($logs as $log) {
            if ($log['action'] === 'delete_task') {
                $detailStr = $log['details'];
                // Strip prefix "task #ID " to extract JSON portion
                $jsonPart = $detailStr;
                if (preg_match('/^task #\d+\s+(.+)/', $detailStr, $m)) {
                    $jsonPart = $m[1];
                }
                $details = json_decode($jsonPart, true);
                if (is_array($details) && isset($details['task_id']) && (int)$details['task_id'] === $taskId) {
                    $deletionDetails = $details;
                    break;
                }
            }
        }
        if ($deletionDetails && isset($deletionDetails['task_status'])) {
            $prevStatus = $deletionDetails['task_status'];
        }
        // Set the task back to its original status
        $taskModel->update($taskId, [
            'name' => $taskModel->findById($taskId)['name'],
            'description' => $taskModel->findById($taskId)['description'],
            'status' => $prevStatus,
            'start_date' => $taskModel->findById($taskId)['start_date'],
            'due_date' => $taskModel->findById($taskId)['due_date'],
            'assigned_to' => $taskModel->findById($taskId)['assigned_to'],
            'parent_id' => $taskModel->findById($taskId)['parent_id'],
            'priority' => $taskModel->findById($taskId)['priority'],
            'tags' => $taskModel->findById($taskId)['tags'],
        ]);
        // If there are promoted subtasks, reassign them back to this restored task
        if ($deletionDetails) {
            $deleteSub = $deletionDetails['delete_subtasks'] ?? false;
            $origSubs  = $deletionDetails['original_subtasks'] ?? [];
            if (!$deleteSub && !empty($origSubs)) {
                foreach ($origSubs as $subId) {
                    // Check if subtask exists and is not deleted
                    $sub = $taskModel->findById($subId);
                    if ($sub && $sub['status'] !== 'deleted') {
                        $taskModel->update($subId, [
                            'name' => $sub['name'],
                            'description' => $sub['description'],
                            'status' => $sub['status'],
                            'start_date' => $sub['start_date'],
                            'due_date' => $sub['due_date'],
                            'assigned_to' => $sub['assigned_to'],
                            'parent_id' => $taskId,
                            'priority' => $sub['priority'],
                            'tags' => $sub['tags'],
                        ]);
                    }
                }
            }
        }
        // Log restoration
        $logModel->create([
            'user_id' => $currentUser['id'],
            'action'  => 'restore_task',
            'details' => json_encode(['task_id' => $taskId]),
        ]);
        redirect('index.php?controller=task&action=trash');
    }

    /**
     * Permanently remove a task that has been soft deleted. Admins only.
     */
    public function forceDelete(): void
    {
        $this->requireLogin();
        $taskId = (int)($_GET['id'] ?? 0);
        $currentUser = $_SESSION['user'] ?? null;
        if ($taskId < 1 || !$currentUser || (int)($currentUser['role_id'] ?? 0) !== 1) {
            $this->render('errors/no_permission');
            return;
        }
        $taskModel = $this->loadModel('Task');
        $taskModel->forceDelete($taskId);
        $logModel = $this->loadModel('Log');
        $logModel->create([
            'user_id' => $currentUser['id'],
            'action'  => 'force_delete_task',
            'details' => json_encode(['task_id' => $taskId]),
        ]);
        redirect('index.php?controller=task&action=trash');
    }
}