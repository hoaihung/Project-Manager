<?php
namespace app\Controller;

use app\Core\Controller;

/**
 * Class ProfileController
 *
 * Provides a profile page for the currently logged-in user where they can view
 * their personal information, tasks assigned to them, comments they've made
 * and simple activity statistics. In the future this could allow updating
 * profile details or password.
 */
class ProfileController extends Controller
{
    /**
     * Display the user's profile page with tasks and activity summary.
     */
    public function index(): void
    {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];
        $userModel = $this->loadModel('User');
        $taskModel = $this->loadModel('Task');
        $commentModel = $this->loadModel('Comment');

        $user = $userModel->findById($userId);
        // Tasks assigned to user (including parent and subtasks). Use model method to retrieve tasks assigned to this user
        $myTasks = $taskModel->getTasksByAssignedUser($userId);
        // Attachments for tasks assigned to this user
        $fileModel = $this->loadModel('File');
        foreach ($myTasks as &$t) {
            $t['files'] = $fileModel->getByTask($t['id']);
        }
        unset($t);
        // Comments made by user
        $comments = $commentModel->getByUser($userId);
        // Build notifications (overdue, due soon, overlapping tasks) similar to notifications() method
        $notifications = [
            'overdue' => [],
            'due_soon' => [],
            'overlapGroups' => [],
        ];
        $allTasks = $myTasks;
        $nowTs = strtotime(date('Y-m-d'));
        $ranges = [];
        foreach ($allTasks as $t) {
            if (($t['status'] ?? '') === 'done') {
                continue;
            }
            $start = !empty($t['start_date']) ? strtotime($t['start_date']) : null;
            $end   = !empty($t['due_date']) ? strtotime($t['due_date']) : null;
            if ($end && $end < $nowTs) {
                $notifications['overdue'][] = $t;
            } elseif ($end && $end <= strtotime('+3 days')) {
                $notifications['due_soon'][] = $t;
            }
            if ($start || $end) {
                $s = $start ?? $end;
                $e = $end ?? $start;
                if ($s > $e) { $tmp = $s; $s = $e; $e = $tmp; }
                $ranges[] = ['index' => count($ranges), 'task' => $t, 's' => $s, 'e' => $e];
            }
        }
        // Build adjacency for overlap groups
        $n = count($ranges);
        $adj = array_fill(0, $n, []);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                if ($ranges[$i]['e'] >= $ranges[$j]['s'] && $ranges[$j]['e'] >= $ranges[$i]['s']) {
                    $adj[$i][] = $j;
                    $adj[$j][] = $i;
                }
            }
        }
        $visited = array_fill(0, $n, false);
        for ($i = 0; $i < $n; $i++) {
            if (!$visited[$i]) {
                $stack = [$i];
                $visited[$i] = true;
                $componentIndices = [];
                $compStart = $ranges[$i]['s'];
                $compEnd   = $ranges[$i]['e'];
                while ($stack) {
                    $cur = array_pop($stack);
                    $componentIndices[] = $cur;
                    if ($ranges[$cur]['s'] > $compStart) { $compStart = $ranges[$cur]['s']; }
                    if ($ranges[$cur]['e'] < $compEnd) { $compEnd = $ranges[$cur]['e']; }
                    foreach ($adj[$cur] as $nbr) {
                        if (!$visited[$nbr]) {
                            $visited[$nbr] = true;
                            $stack[] = $nbr;
                        }
                    }
                }
                if (count($componentIndices) > 1) {
                    $groupTasks = [];
                    foreach ($componentIndices as $idx) {
                        $groupTasks[] = $ranges[$idx]['task'];
                    }
                    $overlapStart = date('Y-m-d', $compStart);
                    $overlapEnd   = date('Y-m-d', $compEnd);
                    $notifications['overlapGroups'][] = [
                        'tasks' => $groupTasks,
                        'range' => [$overlapStart, $overlapEnd],
                    ];
                }
            }
        }
        $this->render('profile/index', [
            'user' => $user,
            'tasks' => $myTasks,
            'comments' => $comments,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Display notifications for the current user. These include tasks assigned to
     * the user that are overdue, due within the next 3 days, or overlapping in
     * their schedules (i.e. tasks whose date ranges intersect).
     */
    public function notifications(): void
    {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];
        // Load required models
        $taskModel    = $this->loadModel('Task');
        $fileModel    = $this->loadModel('File');
        $commentModel = $this->loadModel('Comment');
        // Fetch all tasks assigned to the current user.  Each task will be
        // enriched with additional metadata (file count, comment count,
        // subtask counts) to aid the notifications view.
        $allTasks = $taskModel->getTasksByAssignedUser($userId);
        $notifications = [
            'overdue'       => [],
            'due_today'     => [],
            'due_soon'      => [],
            'high_priority' => [],
            'overlapGroups' => [],
        ];
        $nowDate = date('Y-m-d');
        $nowTs   = strtotime($nowDate);
        $ranges  = [];
        foreach ($allTasks as $t) {
            if (($t['status'] ?? '') === 'done') {
                continue;
            }
            // Compute attachment and comment counts
            $files    = $fileModel->getByTask($t['id']);
            $comments = $commentModel->getByTask($t['id']);
            $t['file_count']    = count($files);
            $t['comment_count'] = count($comments);
            // Compute subtask counts
            $subtasks            = $taskModel->getSubtasks($t['id']);
            $t['subtask_total'] = count($subtasks);
            $t['subtask_done']  = 0;
            foreach ($subtasks as $sub) {
                if (($sub['status'] ?? '') === 'done') {
                    $t['subtask_done']++;
                }
            }
            // Categorise by due date
            $due = $t['due_date'] ?? null;
            if (!empty($due)) {
                $dueTs = strtotime($due);
                if ($dueTs < $nowTs) {
                    $notifications['overdue'][] = $t;
                } elseif ($dueTs == $nowTs) {
                    $notifications['due_today'][] = $t;
                } elseif ($dueTs <= strtotime('+3 days', $nowTs)) {
                    $notifications['due_soon'][] = $t;
                }
            }
            // Categorise by priority
            $priority = $t['priority'] ?? '';
            if (in_array($priority, ['urgent', 'high'])) {
                $notifications['high_priority'][] = $t;
            }
            // Build date range for overlap detection.  Use either the start
            // date or the due date (whichever exists) for both ends of the
            // range when only one is provided.  If both exist, ensure s <= e.
            $startTs = !empty($t['start_date']) ? strtotime($t['start_date']) : null;
            $endTs   = !empty($t['due_date']) ? strtotime($t['due_date']) : null;
            if ($startTs || $endTs) {
                $s = $startTs ?? $endTs;
                $e = $endTs   ?? $startTs;
                if ($s > $e) { $tmp = $s; $s = $e; $e = $tmp; }
                $ranges[] = ['index' => count($ranges), 'task' => $t, 's' => $s, 'e' => $e];
            }
        }
        // Build adjacency list for overlap groups
        $n = count($ranges);
        $adj = array_fill(0, $n, []);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                // Two tasks overlap if their date ranges intersect
                if ($ranges[$i]['e'] >= $ranges[$j]['s'] && $ranges[$j]['e'] >= $ranges[$i]['s']) {
                    $adj[$i][] = $j;
                    $adj[$j][] = $i;
                }
            }
        }
        // Find connected components via DFS
        $visited = array_fill(0, $n, false);
        for ($i = 0; $i < $n; $i++) {
            if (!$visited[$i]) {
                $stack = [$i];
                $visited[$i] = true;
                $componentIndices = [];
                $compStart = $ranges[$i]['s'];
                $compEnd   = $ranges[$i]['e'];
                while ($stack) {
                    $cur = array_pop($stack);
                    $componentIndices[] = $cur;
                    // Update the intersection range boundaries
                    if ($ranges[$cur]['s'] > $compStart) { $compStart = $ranges[$cur]['s']; }
                    if ($ranges[$cur]['e'] < $compEnd) { $compEnd = $ranges[$cur]['e']; }
                    foreach ($adj[$cur] as $nbr) {
                        if (!$visited[$nbr]) {
                            $visited[$nbr] = true;
                            $stack[] = $nbr;
                        }
                    }
                }
                // Only consider overlapping groups when there are at least two tasks
                if (count($componentIndices) > 1) {
                    $groupTasks = [];
                    foreach ($componentIndices as $idx) {
                        $groupTasks[] = $ranges[$idx]['task'];
                    }
                    $overlapStart = date('Y-m-d', $compStart);
                    $overlapEnd   = date('Y-m-d', $compEnd);
                    $notifications['overlapGroups'][] = [
                        'tasks' => $groupTasks,
                        'range' => [$overlapStart, $overlapEnd],
                    ];
                }
            }
        }
        $this->render('profile/notifications', ['notifications' => $notifications]);
    }

    /**
     * Change the user's password.
     */
    public function changePassword(): void
    {
        $this->requireLogin();
        $userId = $_SESSION['user_id'];
        $userModel = $this->loadModel('User');
        $user = $userModel->findById($userId);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPass = $_POST['password'] ?? '';
            if (!empty($newPass)) {
                $userModel->update($userId, ['password' => $newPass]);
                redirect('index.php?controller=profile');
            }
        }
        $this->render('profile/change_password', ['user' => $user]);
    }
}