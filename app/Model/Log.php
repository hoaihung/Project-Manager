<?php
namespace app\Model;

use app\Core\Model;

/**
 * Class Log
 *
 * Handles audit logging. All key operations should be recorded here
 * to help administrators review changes and actions performed by users.
 */
class Log extends Model
{
    /**
     * Add a new log entry.
     *
     * @param array $data
     */
    public function create(array $data): void
    {
        $this->query(
            "INSERT INTO logs (user_id, action, details, created_at) VALUES (:user_id, :action, :details, NOW())",
            [
                'user_id' => $data['user_id'],
                'action' => $data['action'],
                'details' => $data['details'],
            ]
        );
    }

    /**
     * Get logs.
     *
     * @param int $limit
     * @return array
     */
    public function getAll(int $limit = 50): array
    {
        // When using a LIMIT clause, binding the value as a parameter will
        // cause PDO to quote the integer which results in invalid SQL like
        // `LIMIT '100'`. To avoid the syntax error, cast the limit to an
        // integer and interpolate it directly into the SQL string. Because
        // the value is cast to an integer, it cannot contain arbitrary
        // characters and is safe from injection.
        $limit = (int) $limit;
        $sql = "SELECT l.*, u.username FROM logs l " .
               "LEFT JOIN users u ON l.user_id = u.id " .
               "ORDER BY l.created_at DESC LIMIT $limit";
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Delete all log entries.  Administrators can call this to clear the
     * audit log.  Use with caution as this operation is irreversible.
     */
    public function deleteAll(): void
    {
        // Use a plain DELETE instead of TRUNCATE to respect foreign
        // key constraints, if any.  Where constraints exist, TRUNCATE
        // would fail.  No parameters are bound.
        $this->query("DELETE FROM logs");
    }

    /**
     * Get log entries related to a specific task. This method searches the
     * details column for occurrences of the task identifier (e.g. "task #3").
     * It is a simple pattern match and will return recent logs first.
     *
     * @param int $taskId The task ID to filter logs by
     * @param int $limit Maximum number of log entries to return
     * @return array
     */
    public function getByTask(int $taskId, int $limit = 50): array
    {
        $limit = (int) $limit;
        // Use LIKE to find logs mentioning the task; this is a broad match
        // but sufficient for activity feed. Ensure integer interpolation for limit.
        $sql = "SELECT l.*, u.username FROM logs l " .
               "LEFT JOIN users u ON l.user_id = u.id " .
               "WHERE l.details LIKE :pattern " .
               "ORDER BY l.created_at DESC LIMIT $limit";
        $stmt = $this->query($sql, ['pattern' => '%task #' . $taskId . '%']);
        return $stmt->fetchAll();
    }
}