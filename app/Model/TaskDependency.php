<?php
namespace app\Model;

use app\Core\Model;

/**
 * Class TaskDependency
 *
 * Manages dependencies between tasks. A dependency indicates that a task
 * depends on another task to be completed first.
 */
class TaskDependency extends Model
{
    /**
     * Create a dependency record between two tasks.
     *
     * @param int $taskId
     * @param int $dependsOnId
     */
    public function create(int $taskId, int $dependsOnId): void
    {
        $this->query(
            "INSERT INTO task_dependencies (task_id, depends_on_id) VALUES (:task_id, :depends_on_id)",
            [
                'task_id' => $taskId,
                'depends_on_id' => $dependsOnId,
            ]
        );
    }

    /**
     * Get dependencies for a given task.
     *
     * @param int $taskId
     * @return array
     */
    public function getDependencies(int $taskId): array
    {
        $stmt = $this->query(
            "SELECT td.depends_on_id, t.name FROM task_dependencies td " .
            "JOIN tasks t ON td.depends_on_id = t.id WHERE td.task_id = :task_id",
            ['task_id' => $taskId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Get all dependencies for tasks within a specific project.  Returns
     * an array of edges with keys `task_id` and `depends_on_id`.
     *
     * @param int $projectId
     * @return array
     */
    public function getByProject(int $projectId): array
    {
        $stmt = $this->query(
            "SELECT td.task_id, td.depends_on_id " .
            "FROM task_dependencies td " .
            "JOIN tasks t1 ON td.task_id = t1.id " .
            "JOIN tasks t2 ON td.depends_on_id = t2.id " .
            "WHERE t1.project_id = :project_id AND t2.project_id = :project_id",
            ['project_id' => $projectId]
        );
        return $stmt->fetchAll();
    }
}