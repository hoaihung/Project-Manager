<?php
namespace app\Model;

use app\Core\Model;

/**
 * Class Comment
 *
 * Handles task comments.
 */
class Comment extends Model
{
    /**
     * Get comments for a task.
     *
     * @param int $taskId
     * @return array
     */
    public function getByTask(int $taskId): array
    {
        $stmt = $this->query(
            "SELECT c.*, u.full_name AS author FROM comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.task_id = :task_id ORDER BY c.created_at ASC",
            ['task_id' => $taskId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Add a comment to a task.
     *
     * @param array $data
     */
    public function create(array $data): void
    {
        $this->query(
            "INSERT INTO comments (task_id, user_id, comment, created_at) VALUES (:task_id, :user_id, :comment, NOW())",
            [
                'task_id' => $data['task_id'],
                'user_id' => $data['user_id'],
                'comment' => $data['comment'],
            ]
        );
    }

    /**
     * Get comments authored by a specific user across all tasks.
     *
     * @param int $userId
     * @return array
     */
    public function getByUser(int $userId): array
    {
        $stmt = $this->query(
            "SELECT c.*, t.name AS task_name, p.name AS project_name FROM comments c
             JOIN tasks t ON c.task_id = t.id
             JOIN projects p ON t.project_id = p.id
             WHERE c.user_id = :uid ORDER BY c.created_at DESC",
            ['uid' => $userId]
        );
        return $stmt->fetchAll();
    }
}