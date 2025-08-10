<?php
namespace app\Controller;

use app\Core\Controller;
use app\Model\Task;
use app\Model\User;

/**
 * ReportController
 *
 * Handles reporting functionality such as workload reports.
 */
class ReportController extends Controller
{
    /**
     * Display workload report per user. Shows total tasks assigned to each
     * user, counts by status and number of tasks due soon (within 3 days).
     */
    public function workload()
    {
        $taskModel = new Task();
        $userModel = new User();
        $tasks = $taskModel->getAllWithUsers();
        // Build statistics per user
        $stats = [];
        foreach ($tasks as $task) {
            $uid = $task['assigned_to'];
            if (empty($uid)) continue;
            if (!isset($stats[$uid])) {
                $user = $userModel->findById((int)$uid);
                $stats[$uid] = [
                    'user' => $user,
                    'total' => 0,
                    'todo' => 0,
                    'in_progress' => 0,
                    'bug_review' => 0,
                    'done' => 0,
                    'due_soon' => 0,
                ];
            }
            $stats[$uid]['total']++;
            $status = $task['status'] ?? 'todo';
            if (isset($stats[$uid][$status])) {
                $stats[$uid][$status]++;
            }
            // Check due soon: tasks with due date within 3 days
            $dueDate = $task['due_date'];
            if (!empty($dueDate)) {
                $dueTime = strtotime($dueDate);
                if ($dueTime && $dueTime <= strtotime('+3 days')) {
                    $stats[$uid]['due_soon']++;
                }
            }
        }
        // Compute overall status distribution across all users. This can be
        // used to render additional charts in the report view.
        $statusSummary = [
            'todo' => 0,
            'in_progress' => 0,
            'bug_review' => 0,
            'done' => 0,
        ];

        // Removed analysis of completed tasks relative to due date.  Previously, tasks
        // marked as 'done' were classified into early, ontime, or late categories
        // depending on whether their due dates were in the future, present or past.
        // This logic has been removed per new requirements.
        // ---------------------------------------------------------------------
        // Additional time‑based statistics
        //
        // In addition to the workload summary, we generate statistics for
        // tasks within the current month and week.  The timeframes always
        // start from the first day of the month/week up to the current day.
        // For each project accessible to the user (or all projects if admin),
        // we compute the following metrics:
        //   a. Tasks that started within the timeframe (start_date between
        //      timeframe start and today).
        //   b. Number of tasks completed on time (completed_at <= due_date)
        //      versus completed late (completed_at > due_date) for tasks whose
        //      due_date falls within the timeframe and are in the done state.
        //   c. Percentage of tasks done out of all tasks with due_date in
        //      timeframe.
        //   d. Percentage of tasks not done out of all tasks with due_date in
        //      timeframe.
        //   e. Tasks not done where due_date is earlier than today (overdue)
        //      and due_date falls within the timeframe.
        // The results are grouped by project.  Administrators also receive
        // breakdowns by user within each project.  A summary across all
        // projects is also computed.

        $currentUser = $_SESSION['user'] ?? null;
        $userId = $currentUser['id'] ?? 0;
        $isAdmin = isset($currentUser['role_id']) && (int)$currentUser['role_id'] === 1;
        // Determine projects the user can access
        $accessibleProjects = [];
        if (!$isAdmin && $userId > 0) {
            $perms = get_user_permissions((int)$userId);
            if (isset($perms['access_projects']) && is_array($perms['access_projects'])) {
                $accessibleProjects = array_map('intval', $perms['access_projects']);
            }
        }
        /*
         * -------------------
         * Time‑based reporting with filters
         *
         * The report page allows users to filter statistics by time range
         * (week, month or year), by project and for admins by user.  This
         * section calculates a single timeframe (selectedRange) rather than
         * aggregating both week and month as before.  The results are
         * grouped by project and optionally by user for admin.
         */
        // Read filter parameters from query string
        $range = $_GET['range'] ?? 'month';
        $validRanges = ['week', 'month', 'year', 'custom'];
        if (!in_array($range, $validRanges)) {
            $range = 'month';
        }
        $projectFilter = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
        $userFilter = 0;
        if ($isAdmin) {
            $userFilter = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
        }
        // Determine timeframe boundaries.  If custom start_date and end_date
        // parameters are provided and valid, use them instead of the predefined range.
        $todayDate = date('Y-m-d');
        $customStart = $_GET['start_date'] ?? null;
        $customEnd   = $_GET['end_date'] ?? null;
        $startDate = null;
        $endDate   = null;
        $label     = '';
        if ($customStart && $customEnd && preg_match('/^\d{4}-\d{2}-\d{2}$/', $customStart) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $customEnd)) {
            // Validate that end >= start
            if (strtotime($customEnd) >= strtotime($customStart)) {
                $startDate = $customStart;
                // Use the provided end date but do not allow a future date beyond today
                $endDate   = $customEnd > $todayDate ? $todayDate : $customEnd;
                $label     = __('custom_range') ?: 'Khoảng tùy chỉnh';
                $range     = 'custom';
            }
        }
        // If custom dates are not set, derive start and end based on the selected range
        if (!$startDate) {
            switch ($range) {
                case 'week':
                    $startDate = date('Y-m-d', strtotime('monday this week'));
                    $label     = __('week_current') ?: 'Tuần hiện tại';
                    break;
                case 'year':
                    $startDate = date('Y-01-01');
                    $label     = __('year_current') ?: 'Năm hiện tại';
                    break;
                case 'month':
                default:
                    $startDate = date('Y-m-01');
                    $label     = __('month_current') ?: 'Tháng hiện tại';
                    break;
            }
            $endDate = $todayDate;
        }
        // Prepare project and user options for filters
        $projectModel = new \app\Model\Project();
        $projectOptions = [];
        if ($isAdmin) {
            // Admin sees all projects
            $allProjects = $projectModel->all();
            foreach ($allProjects as $proj) {
                $projectOptions[] = $proj;
            }
        } else {
            // Non-admin: only list projects user can access
            foreach ($accessibleProjects as $pid) {
                $proj = $projectModel->findById($pid);
                if ($proj) {
                    $projectOptions[] = $proj;
                }
            }
        }
        // Prepare user options (admin only)
        $userOptions = [];
        if ($isAdmin) {
            $allUsers = $userModel->all();
            foreach ($allUsers as $u) {
                $userOptions[] = $u;
            }
        }
        // Initialize the stat structure
        // Besides counts, store lists of tasks for each category so they can be
        // displayed in the report.  done_ontime_tasks stores tasks completed
        // before or on their due date; done_late_tasks stores tasks completed
        // after their due date; not_done_tasks stores tasks still in progress
        // whose due date falls within the timeframe; overdue_tasks stores
        // tasks not done whose due date is earlier than today.
        $timeStat = [
            'label' => $label,
            'start' => $startDate,
            'end'   => $endDate,
            'projects' => [],
            'summary' => [
                'started_count'    => 0,
                'started_tasks'    => [],
                'done_ontime'      => 0,
                'done_ontime_tasks'=> [],
                'done_late'        => 0,
                'done_late_tasks'  => [],
                'due_total'        => 0,
                'done_total'       => 0,
                'not_done_total'   => 0,
                'not_done_tasks'   => [],
                'overdue_count'    => 0,
                'overdue_tasks'    => [],
                // Tasks that are not done and not overdue (in progress)
                'in_progress'      => 0,
                'in_progress_tasks'=> [],
            ],
        ];
        // Caches for project and user
        $projectCache = [];
        $userCache = [];
        $getProject = function($pid) use (&$projectCache, $projectModel) {
            if (!isset($projectCache[$pid])) {
                $projectCache[$pid] = $projectModel->findById((int)$pid);
            }
            return $projectCache[$pid] ?? null;
        };
        $getUser = function($uid) use (&$userCache, $userModel) {
            if (!isset($userCache[$uid])) {
                $userCache[$uid] = $userModel->findById((int)$uid);
            }
            return $userCache[$uid] ?? null;
        };
        // Iterate tasks and accumulate stats for the selected timeframe
        foreach ($tasks as $task) {
            $projId = (int)($task['project_id'] ?? 0);
            if ($projId <= 0) continue;
            // Permission check: same as above
            if (!$isAdmin) {
                $assignedTo = isset($task['assigned_to']) ? (int)$task['assigned_to'] : null;
                $hasAccessProject = in_array($projId, $accessibleProjects);
                $isAssigned = ($assignedTo !== null && $assignedTo === $userId);
                if (!$hasAccessProject && !$isAssigned) {
                    continue;
                }
            }
            // Apply project filter
            if ($projectFilter > 0 && $projectFilter !== $projId) {
                continue;
            }
            // Apply user filter (admin only)
            if ($isAdmin && $userFilter > 0) {
                $assignedTo = isset($task['assigned_to']) ? (int)$task['assigned_to'] : 0;
                if ($assignedTo !== $userFilter) {
                    continue;
                }
            }
            // Prepare project entry
            if (!isset($timeStat['projects'][$projId])) {
                $timeStat['projects'][$projId] = [
                    'project'            => $getProject($projId),
                    'started_count'      => 0,
                    'started_tasks'      => [],
                    'done_ontime'        => 0,
                    'done_ontime_tasks'  => [],
                    'done_late'          => 0,
                    'done_late_tasks'    => [],
                    'due_total'          => 0,
                    'done_total'         => 0,
                    'not_done_total'     => 0,
                    'not_done_tasks'     => [],
                    'overdue_count'      => 0,
                    'overdue_tasks'      => [],
                    // In-progress tasks (not done and not overdue)
                    'in_progress'        => 0,
                    'in_progress_tasks'  => [],
                    'users'              => [],
                ];
            }
            $projStats =& $timeStat['projects'][$projId];
            // Check start_date within timeframe
            if (!empty($task['start_date']) && $task['start_date'] >= $startDate && $task['start_date'] <= $endDate) {
                $projStats['started_count']++;
                $projStats['started_tasks'][] = $task;
                $timeStat['summary']['started_count']++;
                $timeStat['summary']['started_tasks'][] = $task;
            }
            // Check due_date within timeframe
            if (!empty($task['due_date']) && $task['due_date'] >= $startDate && $task['due_date'] <= $endDate) {
                $projStats['due_total']++;
                $timeStat['summary']['due_total']++;
                $isDone = ($task['status'] === 'done');
                if ($isDone) {
                    // Completed tasks
                    $projStats['done_total']++;
                    $timeStat['summary']['done_total']++;
                    $dueDateTime   = strtotime($task['due_date'] . ' 23:59:59');
                    $completedAt   = !empty($task['completed_at']) ? strtotime($task['completed_at']) : null;
                    if ($completedAt !== null && $completedAt <= $dueDateTime) {
                        // Completed on time
                        $projStats['done_ontime']++;
                        $projStats['done_ontime_tasks'][] = $task;
                        $timeStat['summary']['done_ontime']++;
                        $timeStat['summary']['done_ontime_tasks'][] = $task;
                    } else {
                        // Completed late
                        $projStats['done_late']++;
                        $projStats['done_late_tasks'][] = $task;
                        $timeStat['summary']['done_late']++;
                        $timeStat['summary']['done_late_tasks'][] = $task;
                    }
                } else {
                    // Not completed tasks
                    $projStats['not_done_total']++;
                    $projStats['not_done_tasks'][] = $task;
                    $timeStat['summary']['not_done_total']++;
                    $timeStat['summary']['not_done_tasks'][] = $task;
                    // Overdue check: due date earlier than today (not timeframe end)
                    if ($task['due_date'] < $todayDate) {
                        $projStats['overdue_count']++;
                        $projStats['overdue_tasks'][] = $task;
                        $timeStat['summary']['overdue_count']++;
                        $timeStat['summary']['overdue_tasks'][] = $task;
                    } else {
                        // Tasks still within due date (in progress)
                        $projStats['in_progress']     = ($projStats['in_progress'] ?? 0) + 1;
                        $projStats['in_progress_tasks'][] = $task;
                        $timeStat['summary']['in_progress'] = ($timeStat['summary']['in_progress'] ?? 0) + 1;
                        $timeStat['summary']['in_progress_tasks'][] = $task;
                    }
                }
            }
            // Admin breakdown by user
            if ($isAdmin) {
                $assigneeId = isset($task['assigned_to']) ? (int)$task['assigned_to'] : 0;
                if ($assigneeId > 0) {
                    if (!isset($projStats['users'][$assigneeId])) {
                        $projStats['users'][$assigneeId] = [
                            'user'              => $getUser($assigneeId),
                            'started_count'      => 0,
                            'started_tasks'      => [],
                            'done_ontime'        => 0,
                            'done_ontime_tasks'  => [],
                            'done_late'          => 0,
                            'done_late_tasks'    => [],
                            'due_total'          => 0,
                            'done_total'         => 0,
                            'not_done_total'     => 0,
                            'not_done_tasks'     => [],
                            'overdue_count'      => 0,
                            'overdue_tasks'      => [],
                            'in_progress'        => 0,
                            'in_progress_tasks'  => [],
                        ];
                    }
                    $uStats =& $projStats['users'][$assigneeId];
                    // Start date stats per user
                    if (!empty($task['start_date']) && $task['start_date'] >= $startDate && $task['start_date'] <= $todayDate) {
                        $uStats['started_count']++;
                        $uStats['started_tasks'][] = $task;
                    }
                    // Due date stats per user
                    if (!empty($task['due_date']) && $task['due_date'] >= $startDate && $task['due_date'] <= $endDate) {
                        $uStats['due_total']++;
                            if ($task['status'] === 'done') {
                                $uStats['done_total']++;
                                $completedAt = !empty($task['completed_at']) ? strtotime($task['completed_at']) : null;
                                $dueDateTime = strtotime($task['due_date'] . ' 23:59:59');
                                if ($completedAt !== null && $completedAt <= $dueDateTime) {
                                    $uStats['done_ontime']++;
                                    $uStats['done_ontime_tasks'][] = $task;
                                } else {
                                    $uStats['done_late']++;
                                    $uStats['done_late_tasks'][] = $task;
                                }
                            } else {
                                $uStats['not_done_total']++;
                                $uStats['not_done_tasks'][] = $task;
                                if ($task['due_date'] < $todayDate) {
                                    $uStats['overdue_count']++;
                                    $uStats['overdue_tasks'][] = $task;
                                } else {
                                    $uStats['in_progress']++;
                                    $uStats['in_progress_tasks'][] = $task;
                                }
                            }
                    }
                }
            }
        }
        // Compute percentages
        foreach ($timeStat['projects'] as &$projStats) {
            $dueTotal = $projStats['due_total'];
            $projStats['percent_done'] = ($dueTotal > 0) ? round($projStats['done_total'] / $dueTotal * 100, 2) : 0;
            $projStats['percent_not_done'] = ($dueTotal > 0) ? round($projStats['not_done_total'] / $dueTotal * 100, 2) : 0;
            if ($isAdmin) {
                foreach ($projStats['users'] as &$uStat) {
                    $uDue = $uStat['due_total'];
                    $uStat['percent_done'] = ($uDue > 0) ? round($uStat['done_total'] / $uDue * 100, 2) : 0;
                    $uStat['percent_not_done'] = ($uDue > 0) ? round($uStat['not_done_total'] / $uDue * 100, 2) : 0;
                }
                unset($uStat);
            }
        }
        unset($projStats);
        $sumDue = $timeStat['summary']['due_total'];
        $timeStat['summary']['percent_done'] = ($sumDue > 0) ? round($timeStat['summary']['done_total'] / $sumDue * 100, 2) : 0;
        $timeStat['summary']['percent_not_done'] = ($sumDue > 0) ? round($timeStat['summary']['not_done_total'] / $sumDue * 100, 2) : 0;

        // Render report view with additional datasets and filters
        $this->render('report/workload', [
            'reports' => $stats,
            'statusSummary' => $statusSummary,
            'timeStat' => $timeStat,
            'isAdmin' => $isAdmin,
            'projectOptions' => $projectOptions,
            'userOptions' => $userOptions,
            'selectedRange' => $range,
            'selectedProjectId' => $projectFilter,
            'selectedUserId' => $userFilter,
        ]);
    }
}