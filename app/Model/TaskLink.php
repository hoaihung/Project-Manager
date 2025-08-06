<?php
namespace app\Model;

use app\Core\Model;

/**
 * Class TaskLink
 *
 * Encapsulates access to the task_links table.  Each record in
 * task_links stores a reference to an external document (e.g. a
 * spreadsheet or Google Docs) associated with a task.  Multiple
 * links may be attached to a single task.  The controller should
 * validate that URLs are well‑formed before saving.
 */
class TaskLink extends Model
{
    /**
     * Retrieve all links for a given task.
     *
     * @param int $taskId
     * @return array
     */
    public function getByTask(int $taskId): array
    {
        $stmt = $this->query(
            "SELECT * FROM task_links WHERE task_id = :tid ORDER BY id ASC",
            ['tid' => $taskId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Create a new link for a task.
     *
     * @param array $data
     * @return int ID of new link
     */
    public function create(array $data): int
    {
        $this->query(
            "INSERT INTO task_links (task_id, name, url, created_at) VALUES (:task_id, :name, :url, NOW())",
            [
                'task_id' => $data['task_id'],
                'name'    => $data['name'] ?? null,
                'url'     => $data['url'],
            ]
        );
        return (int)$this->getConnection()->lastInsertId();
    }

    /**
     * Delete a link by ID.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        $this->query("DELETE FROM task_links WHERE id = :id", ['id' => $id]);
    }

    /**
     * Replace all links for a task.  Removes existing links and inserts
     * new ones.  Expects an array of associative arrays with name and url.
     *
     * @param int $taskId
     * @param array $links
     */
    public function replaceForTask(int $taskId, array $links): void
    {
        // Remove existing links
        $this->query("DELETE FROM task_links WHERE task_id = :tid", ['tid' => $taskId]);
        foreach ($links as $link) {
            if (!empty($link['url'])) {
                $this->create([
                    'task_id' => $taskId,
                    'name'    => $link['name'] ?? null,
                    'url'     => $link['url'],
                ]);
            }
        }
    }
}