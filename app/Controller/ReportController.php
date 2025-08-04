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
        // Collect tasks due within the next 7 days (including today)
        $dueSoonByDate = [];
        $today = strtotime(date('Y-m-d'));
        $sevenDays = strtotime('+6 days', $today);
        foreach ($tasks as $task) {
            $status = $task['status'] ?? 'todo';
            if (isset($statusSummary[$status])) {
                $statusSummary[$status]++;
            }
            // Count due dates for the next 7 days
            $due = $task['due_date'];
            if (!empty($due)) {
                $dueTs = strtotime($due);
                if ($dueTs !== false && $dueTs >= $today && $dueTs <= $sevenDays) {
                    $key = date('Y-m-d', $dueTs);
                    if (!isset($dueSoonByDate[$key])) {
                        $dueSoonByDate[$key] = 0;
                    }
                    $dueSoonByDate[$key]++;
                }
            }

            // We no longer classify completed tasks relative to their due date.
        }
        // Ensure all dates in the next 7 day range exist in the array, even
        // if count is zero, so the chart axes remain consistent
        for ($i = 0; $i < 7; $i++) {
            $d = date('Y-m-d', strtotime('+' . $i . ' days', $today));
            if (!isset($dueSoonByDate[$d])) {
                $dueSoonByDate[$d] = 0;
            }
        }
        ksort($dueSoonByDate);
        // Render report view with additional datasets for charts
        $this->render('report/workload', [
            'reports' => $stats,
            'statusSummary' => $statusSummary,
            'dueSoonByDate' => $dueSoonByDate,
        ]);
    }
}