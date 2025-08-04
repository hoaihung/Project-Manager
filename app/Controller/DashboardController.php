<?php
namespace app\Controller;

use app\Core\Controller;

/**
 * Class DashboardController
 *
 * Shows an overview of projects and tasks for the currently logged in user.
 */
class DashboardController extends Controller
{
    /**
     * Display dashboard with project summaries and upcoming tasks.
     */
    public function index(): void
    {
        $this->requireLogin();
        $projectModel = $this->loadModel('Project');
        $taskModel = $this->loadModel('Task');
        $projects = $projectModel->all();
        // Determine if a project filter is applied. 'project' param can be 'all' or a numeric id.
        $projectFilter = $_GET['project'] ?? 'all';

        // Basic upcoming tasks: tasks due in next 7 days
        $upcomingTasks = $taskModel->getUpcomingTasks(7);
        // Upcoming projects approaching their end date
        $upcomingProjects = $projectModel->getUpcomingProjects(7);
        // Task statistics: if a specific project is selected, compute stats only for that project
        if ($projectFilter !== 'all' && ctype_digit($projectFilter)) {
            $pId = (int)$projectFilter;
            $taskStats = $taskModel->getStatsForProject($pId);
            $priorityCounts = $taskModel->getPriorityCountsForProject($pId);
            // Filter upcoming tasks and projects by the selected project
            $upcomingTasks = array_filter($upcomingTasks, function ($t) use ($pId) {
                return (int)$t['project_id'] === $pId;
            });
            $upcomingProjects = array_filter($upcomingProjects, function ($p) use ($pId) {
                return (int)$p['id'] === $pId;
            });
        } else {
            $taskStats = $taskModel->getStats();
            $priorityCounts = $taskModel->getPriorityCounts();
        }

        // Tasks due today and tomorrow; split into tasks assigned to current user and those not assigned to current user (others)
        $allToday = $taskModel->getTasksDueOnDate(date('Y-m-d'));
        $allTomorrow = $taskModel->getTasksDueOnDate(date('Y-m-d', strtotime('+1 day')));
        $todayTasksMy = [];
        $todayTasksOthers = [];
        $tomorrowTasksMy = [];
        $tomorrowTasksOthers = [];
        $uid = $_SESSION['user_id'] ?? 0;
        foreach ($allToday as $t) {
            if (!empty($t['assigned_to']) && (int)$t['assigned_to'] === (int)$uid) {
                $todayTasksMy[] = $t;
            } else {
                $todayTasksOthers[] = $t;
            }
        }
        foreach ($allTomorrow as $t) {
            if (!empty($t['assigned_to']) && (int)$t['assigned_to'] === (int)$uid) {
                $tomorrowTasksMy[] = $t;
            } else {
                $tomorrowTasksOthers[] = $t;
            }
        }

        $this->render('dashboard/index', [
            'projects' => $projects,
            'upcomingTasks' => $upcomingTasks,
            'upcomingProjects' => $upcomingProjects,
            'taskStats' => $taskStats,
            'priorityCounts' => $priorityCounts,
            'todayTasksMy' => $todayTasksMy,
            'todayTasksOthers' => $todayTasksOthers,
            'tomorrowTasksMy' => $tomorrowTasksMy,
            'tomorrowTasksOthers' => $tomorrowTasksOthers,
            'projectFilter' => $projectFilter,
        ]);
    }
}