<?php
namespace app\Model;

use app\Core\Model;

/**
 * Class ChecklistItem
 *
 * Provides CRUD operations for task checklists.  Each checklist item
 * belongs to a single task and has a textual description along with
 * a completion flag and sort order.  When a task is deleted, its
 * checklist items will be removed automatically via foreign key
 * cascade.
 */
class ChecklistItem extends Model
{
    /**
     * Fetch all checklist items for a task ordered by sort_order.
     *
     * @param int $taskId
     * @return array
     */
    public function getByTask(int $taskId): array
    {
        $stmt = $this->query(
            "SELECT * FROM checklist_items WHERE task_id = :tid ORDER BY sort_order ASC, id ASC",
            ['tid' => $taskId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Create a checklist item.
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        $this->query(
            "INSERT INTO checklist_items (task_id, content, is_done, sort_order) VALUES (:task_id, :content, :is_done, :sort_order)",
            [
                'task_id'   => $data['task_id'],
                'content'   => $data['content'],
                'is_done'   => $data['is_done'] ?? 0,
                'sort_order' => $data['sort_order'] ?? 1,
            ]
        );
        return (int)$this->getConnection()->lastInsertId();
    }

    /**
     * Update a checklist item.
     *
     * @param int $id
     * @param array $data
     */
    public function update(int $id, array $data): void
    {
        $this->query(
            "UPDATE checklist_items SET content = :content, is_done = :is_done, sort_order = :sort_order WHERE id = :id",
            [
                'id'        => $id,
                'content'   => $data['content'],
                'is_done'   => $data['is_done'] ?? 0,
                'sort_order' => $data['sort_order'] ?? 1,
            ]
        );
    }

    /**
     * Delete a checklist item by ID.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        $this->query("DELETE FROM checklist_items WHERE id = :id", ['id' => $id]);
    }

    /**
     * Replace all checklist items for a task.  Existing items will be
     * removed and replaced with the provided array of items.  Each
     * item should contain keys 'content', 'is_done' and optionally
     * 'sort_order'.
     *
     * @param int $taskId
     * @param array $items
     */
    public function replaceForTask(int $taskId, array $items): void
    {
        // Remove existing items
        $this->query("DELETE FROM checklist_items WHERE task_id = :tid", ['tid' => $taskId]);
        $order = 1;
        foreach ($items as $item) {
            if (isset($item['content']) && trim($item['content']) !== '') {
                $this->create([
                    'task_id'    => $taskId,
                    'content'    => $item['content'],
                    'is_done'    => $item['is_done'] ?? 0,
                    'sort_order' => $item['sort_order'] ?? $order,
                ]);
                $order++;
            }
        }
    }
}