<?php
namespace app\Controller;

use app\Core\Controller;

/**
 * Class LogController
 *
 * Displays audit logs. Admin only.
 */
class LogController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $logModel = $this->loadModel('Log');
        // Determine date filter range.  Admins can filter logs by a
        // simple preset range such as today, last7 or this_month.  If no
        // filter is specified (or set to 'all') then all logs are
        // displayed.  Supported values: all, today, last7, this_month.
        $range = $_GET['date_range'] ?? 'all';
        // Fetch a generous number of logs to allow filtering in memory.
        $allLogs = $logModel->getAll(2000);
        $filtered = [];
        if ($range === 'all') {
            $filtered = $allLogs;
        } else {
            // Compute date boundaries for filtering.  created_at is in
            // YYYY-MM-DD HH:MM:SS format so we can compare substrings.
            $today = date('Y-m-d');
            if ($range === 'today') {
                foreach ($allLogs as $l) {
                    if (substr($l['created_at'], 0, 10) === $today) {
                        $filtered[] = $l;
                    }
                }
            } elseif ($range === 'last7') {
                $start = date('Y-m-d', strtotime('-6 days'));
                foreach ($allLogs as $l) {
                    $d = substr($l['created_at'], 0, 10);
                    if ($d >= $start && $d <= $today) {
                        $filtered[] = $l;
                    }
                }
            } elseif ($range === 'this_month') {
                $monthStart = date('Y-m-01');
                $monthEnd   = date('Y-m-t');
                foreach ($allLogs as $l) {
                    $d = substr($l['created_at'], 0, 10);
                    if ($d >= $monthStart && $d <= $monthEnd) {
                        $filtered[] = $l;
                    }
                }
            } else {
                // Unknown filter: fallback to all
                $filtered = $allLogs;
            }
        }
        // Pagination
        $perPage = 25;
        $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $totalRows = count($filtered);
        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        $offset = ($currentPage - 1) * $perPage;
        $logs = array_slice($filtered, $offset, $perPage);
        $this->render('logs/index', [
            'logs' => $logs,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
        ]);
    }

    /**
     * Clear all logs.  Only admin users are permitted to perform this
     * action.  After deleting the entries, redirect back to the logs
     * index.  This action should be invoked via GET or POST using
     * index.php?controller=log&action=clear.
     */
    public function clear(): void
    {
        $this->requireAdmin();
        $logModel = $this->loadModel('Log');
        $logModel->deleteAll();
        redirect('index.php?controller=log');
    }
}