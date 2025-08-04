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
        // Pagination: fetch a large number of logs then slice in PHP for simplicity
        $allLogs = $logModel->getAll(1000);
        $perPage = 25;
        $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $totalRows = count($allLogs);
        $totalPages = max(1, (int)ceil($totalRows / $perPage));
        $offset = ($currentPage - 1) * $perPage;
        $logs = array_slice($allLogs, $offset, $perPage);
        $this->render('logs/index', [
            'logs' => $logs,
            'totalPages' => $totalPages,
            'currentPage' => $currentPage,
        ]);
    }
}