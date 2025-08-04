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
        // Tasks assigned to user (including parent and subtasks)
        // Use model method to retrieve tasks assigned to this user
        $myTasks = $taskModel->getTasksByAssignedUser($userId);
        // Attachments for tasks assigned to this user
        $fileModel = $this->loadModel('File');
        foreach ($myTasks as &$t) {
            $t['files'] = $fileModel->getByTask($t['id']);
        }
        unset($t);
        // Comments made by user
        $comments = $commentModel->getByUser($userId);
        $this->render('profile/index', [
            'user' => $user,
            'tasks' => $myTasks,
            'comments' => $comments,
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
        /** @var \app\Model\Task $taskModel */
        $taskModel = $this->loadModel('Task');
        $allTasks = $taskModel->getTasksByAssignedUser($userId);
        $notifications = [
            'overdue' => [],
            'due_soon' => [],
            // for overlapping tasks we will compute groups instead of a flat list
            'overlapGroups' => [],
        ];
        // Determine tasks with date ranges and overdue/due soon categorisation
        $ranges = [];
        $nowTs = strtotime(date('Y-m-d'));
        foreach ($allTasks as $t) {
            $start = !empty($t['start_date']) ? strtotime($t['start_date']) : null;
            $end   = !empty($t['due_date']) ? strtotime($t['due_date']) : null;
            if ($end && $end < $nowTs) {
                $notifications['overdue'][] = $t;
            } elseif ($end && $end <= strtotime('+3 days')) {
                $notifications['due_soon'][] = $t;
            }
            // Build ranges for overlap check (use start or end date if one is missing)
            if ($start || $end) {
                $s = $start ?? $end;
                $e = $end ?? $start;
                if ($s > $e) { $tmp = $s; $s = $e; $e = $tmp; }
                $ranges[] = ['index' => count($ranges), 'task' => $t, 's' => $s, 'e' => $e];
            }
        }
        // Build adjacency list for overlap groups
        $n = count($ranges);
        $adj = array_fill(0, $n, []);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                if ($ranges[$i]['e'] >= $ranges[$j]['s'] && $ranges[$j]['e'] >= $ranges[$i]['s']) {
                    // Overlap
                    $adj[$i][] = $j;
                    $adj[$j][] = $i;
                }
            }
        }
        // Find connected components via DFS
        $visited = array_fill(0, $n, false);
        for ($i = 0; $i < $n; $i++) {
            if (!$visited[$i]) {
                // Start new component
                $stack = [$i];
                $visited[$i] = true;
                $componentIndices = [];
                $compStart = $ranges[$i]['s'];
                $compEnd   = $ranges[$i]['e'];
                while ($stack) {
                    $cur = array_pop($stack);
                    $componentIndices[] = $cur;
                    // update intersection range for overlap (max of start, min of end)
                    if ($ranges[$cur]['s'] > $compStart) { $compStart = $ranges[$cur]['s']; }
                    if ($ranges[$cur]['e'] < $compEnd) { $compEnd = $ranges[$cur]['e']; }
                    foreach ($adj[$cur] as $nbr) {
                        if (!$visited[$nbr]) {
                            $visited[$nbr] = true;
                            $stack[] = $nbr;
                        }
                    }
                }
                // Only consider components with more than one task as overlapping groups
                if (count($componentIndices) > 1) {
                    $groupTasks = [];
                    foreach ($componentIndices as $idx) {
                        $groupTasks[] = $ranges[$idx]['task'];
                    }
                    // Format range back to Y-m-d
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