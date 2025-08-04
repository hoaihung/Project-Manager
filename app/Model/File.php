<?php
namespace app\Model;

use app\Core\Model;

/**
 * Class File
 *
 * Handles attachments uploaded to tasks.
 */
class File extends Model
{
    /**
     * Get files by task.
     *
     * @param int $taskId
     * @return array
     */
    public function getByTask(int $taskId): array
    {
        $stmt = $this->query("SELECT * FROM files WHERE task_id = :task_id", ['task_id' => $taskId]);
        return $stmt->fetchAll();
    }

    /**
     * Save uploaded file record.
     *
     * @param array $data
     */
    public function create(array $data): void
    {
        $this->query(
            "INSERT INTO files (task_id, file_name, file_path, uploaded_at) VALUES (:task_id, :file_name, :file_path, NOW())",
            [
                'task_id' => $data['task_id'],
                'file_name' => $data['file_name'],
                'file_path' => $data['file_path'],
            ]
        );
    }

    /**
     * Find a file by its ID.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->query("SELECT * FROM files WHERE id = :id", ['id' => $id]);
        $file = $stmt->fetch();
        return $file ? $file : null;
    }

    /**
     * Delete a file record by its ID.
     *
     * @param int $id
     */
    public function deleteById(int $id): void
    {
        $this->query("DELETE FROM files WHERE id = :id", ['id' => $id]);
    }
}