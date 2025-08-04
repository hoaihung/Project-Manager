<?php
namespace app\Model;

use app\Core\Model;

/**
 * Class TaskUser
 *
 * Handles many-to-many relationship between tasks and users. Allows assigning
 * multiple users to a single task.
 */
class TaskUser extends Model
{
    /**
     * Assign a user to a task. If the assignment already exists, the
     * operation will be ignored due to the primary key constraint.
     *
     * @param int $taskId
     * @param int $userId
     */
    public function assign(int $taskId, int $userId): void
    {
        $this->query(
            "INSERT IGNORE INTO task_user (task_id, user_id) VALUES (:task_id, :user_id)",
            [
                'task_id' => $taskId,
                'user_id' => $userId,
            ]
        );
    }

    /**
     * Remove a user assignment from a task.
     *
     * @param int $taskId
     * @param int $userId
     */
    public function unassign(int $taskId, int $userId): void
    {
        $this->query(
            "DELETE FROM task_user WHERE task_id = :task_id AND user_id = :user_id",
            [
                'task_id' => $taskId,
                'user_id' => $userId,
            ]
        );
    }

    /**
     * Get all users assigned to a task.
     *
     * @param int $taskId
     * @return array
     */
    public function getUsersByTask(int $taskId): array
    {
        $stmt = $this->query(
            "SELECT u.* FROM task_user tu JOIN users u ON tu.user_id = u.id WHERE tu.task_id = :task_id",
            ['task_id' => $taskId]
        );
        return $stmt->fetchAll();
    }
}