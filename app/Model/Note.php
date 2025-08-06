<?php
namespace app\Model;

use app\Core\Model;

/**
 * Class Note
 *
 * Provides CRUD operations for notes.  Notes are free‑form pieces of
 * markdown content that may be attached to zero or more tasks.  Notes
 * optionally belong to a project; when project_id is NULL the note is
 * considered global (system level).  The note_task pivot table stores
 * relationships between notes and tasks.  See sql/schema.sql for the
 * schema definitions.
 */
class Note extends Model
{
    /**
     * Retrieve all notes optionally filtered by project.
     *
     * @param int|null $projectId
     * @return array
     */
    public function getAll(?int $projectId = null): array
    {
        if ($projectId === null) {
            $stmt = $this->query(
                "SELECT n.*, u.full_name AS author, p.name AS project_name
                 FROM notes n
                 LEFT JOIN users u ON n.user_id = u.id
                 LEFT JOIN projects p ON n.project_id = p.id
                 ORDER BY n.created_at DESC"
            );
            return $stmt->fetchAll();
        }
        $stmt = $this->query(
            "SELECT n.*, u.full_name AS author, p.name AS project_name
             FROM notes n
             LEFT JOIN users u ON n.user_id = u.id
             LEFT JOIN projects p ON n.project_id = p.id
             WHERE n.project_id = :pid OR n.project_id IS NULL
             ORDER BY n.created_at DESC",
            ['pid' => $projectId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Find a note by its ID.  Includes author and project name.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->query(
            "SELECT n.*, u.full_name AS author, p.name AS project_name
             FROM notes n
             LEFT JOIN users u ON n.user_id = u.id
             LEFT JOIN projects p ON n.project_id = p.id
             WHERE n.id = :id",
            ['id' => $id]
        );
        $note = $stmt->fetch();
        return $note ?: null;
    }

    /**
     * Create a new note.  Returns the ID of the newly inserted record.
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        $this->query(
            "INSERT INTO notes (project_id, user_id, title, content, created_at, updated_at)
             VALUES (:project_id, :user_id, :title, :content, NOW(), NOW())",
            [
                'project_id' => $data['project_id'] ?? null,
                'user_id'    => $data['user_id'],
                'title'      => $data['title'] ?? null,
                'content'    => $data['content'],
            ]
        );
        return (int)$this->getConnection()->lastInsertId();
    }

    /**
     * Update an existing note.  Only provided fields will be updated.
     *
     * @param int $id
     * @param array $data
     */
    public function update(int $id, array $data): void
    {
        $this->query(
            "UPDATE notes
             SET project_id = :project_id, title = :title, content = :content, updated_at = NOW()
             WHERE id = :id",
            [
                'id'         => $id,
                'project_id' => $data['project_id'] ?? null,
                'title'      => $data['title'] ?? null,
                'content'    => $data['content'],
            ]
        );
    }

    /**
     * Delete a note and its relationships.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        // Cascading delete on note_task due to FK constraints
        $this->query("DELETE FROM notes WHERE id = :id", ['id' => $id]);
    }

    /**
     * Associate a note with a set of tasks.  Existing relations will be
     * cleared and replaced with the provided list of task IDs.
     *
     * @param int $noteId
     * @param array $taskIds
     */
    public function setTasks(int $noteId, array $taskIds): void
    {
        // Remove existing relations
        $this->query("DELETE FROM note_task WHERE note_id = :nid", ['nid' => $noteId]);
        // Insert new relations
        foreach ($taskIds as $tid) {
            $tid = (int)$tid;
            if ($tid > 0) {
                $this->query(
                    "INSERT INTO note_task (note_id, task_id) VALUES (:nid, :tid)",
                    ['nid' => $noteId, 'tid' => $tid]
                );
            }
        }
    }

    /**
     * Remove a relationship between a given note and a specific task. This
     * allows unlinking a note from a single task without clearing all
     * associations. If no matching row exists the operation is a no‑op.
     *
     * @param int $noteId
     * @param int $taskId
     */
    public function removeTask(int $noteId, int $taskId): void
    {
        $this->query(
            "DELETE FROM note_task WHERE note_id = :nid AND task_id = :tid",
            ['nid' => $noteId, 'tid' => $taskId]
        );
    }

    /**
     * Get all tasks associated with a given note.  Returns an array of
     * task records including project name for context.
     *
     * @param int $noteId
     * @return array
     */
    public function getTasks(int $noteId): array
    {
        $stmt = $this->query(
            "SELECT t.*, p.name AS project_name
             FROM note_task nt
             JOIN tasks t ON nt.task_id = t.id
             JOIN projects p ON t.project_id = p.id
             WHERE nt.note_id = :nid",
            ['nid' => $noteId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Get all notes associated with a particular task.  Useful for displaying
     * notes within a task detail page.
     *
     * @param int $taskId
     * @return array
     */
    public function getByTask(int $taskId): array
    {
        $stmt = $this->query(
            "SELECT n.*, u.full_name AS author, p.name AS project_name
             FROM note_task nt
             JOIN notes n ON nt.note_id = n.id
             LEFT JOIN users u ON n.user_id = u.id
             LEFT JOIN projects p ON n.project_id = p.id
             WHERE nt.task_id = :tid
             ORDER BY n.created_at DESC",
            ['tid' => $taskId]
        );
        return $stmt->fetchAll();
    }
}