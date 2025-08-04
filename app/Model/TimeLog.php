<?php
namespace app\Model;

use app\Core\Model;

/**
 * Class TimeLog
 *
 * Represents time logs that users can record on tasks. Each log has a start
 * and end time and an optional description. Logs belong to both a task and
 * a user.
 */
class TimeLog extends Model
{
    /**
     * Create a new time log entry.
     *
     * @param array $data
     */
    public function create(array $data): void
    {
        $this->query(
            "INSERT INTO time_logs (task_id, user_id, start_time, end_time, description) " .
            "VALUES (:task_id, :user_id, :start_time, :end_time, :description)",
            [
                'task_id' => $data['task_id'],
                'user_id' => $data['user_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'description' => $data['description'] ?? null,
            ]
        );
    }

    /**
     * Retrieve logs for a specific task.
     *
     * @param int $taskId
     * @return array
     */
    public function getByTask(int $taskId): array
    {
        $stmt = $this->query(
            "SELECT tl.*, u.full_name AS user_name FROM time_logs tl " .
            "JOIN users u ON tl.user_id = u.id WHERE tl.task_id = :task_id ORDER BY tl.start_time ASC",
            ['task_id' => $taskId]
        );
        return $stmt->fetchAll();
    }
}