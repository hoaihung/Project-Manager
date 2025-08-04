<?php
namespace app\Model;

use app\Core\Model;

/**
 * Class Task
 *
 * Handles CRUD operations for tasks, including subtasks and dependencies.
 */
class Task extends Model
{
    /**
     * Get all tasks for a project grouped by status.
     *
     * @param int $projectId
     * @return array
     */
    public function getByProject(int $projectId): array
    {
        // Fetch all tasks for the project along with assignee name and attachment/comment counts.
        // Exclude tasks marked as deleted. We order by sort_order so that tasks
        // appear in the same order within their status columns as stored.
        $stmt = $this->query(
            "SELECT t.*, u.full_name AS assignee, " .
            "COUNT(DISTINCT f.id) AS attachment_count, " .
            "COUNT(DISTINCT c.id) AS comment_count " .
            "FROM tasks t " .
            "LEFT JOIN users u ON t.assigned_to = u.id " .
            "LEFT JOIN files f ON f.task_id = t.id " .
            "LEFT JOIN comments c ON c.task_id = t.id " .
            "WHERE t.project_id = :project_id AND t.status <> 'deleted' " .
            "GROUP BY t.id ORDER BY t.sort_order ASC",
            ['project_id' => $projectId]
        );
        $rows = $stmt->fetchAll();
        // Build a map of tasks keyed by ID so we can easily assign subtasks to parents.
        $tasks = [];
        foreach ($rows as $row) {
            $row['subtasks'] = [];
            $row['subtask_total'] = 0;
            $row['subtask_done'] = 0;
            $tasks[$row['id']] = $row;
        }
        // Assign each task to its parent if parent_id is set. We compute subtasks
        // progress counters on the parent while doing so.
        foreach ($tasks as $id => $task) {
            if (!empty($task['parent_id']) && isset($tasks[$task['parent_id']])) {
                $parentId = $task['parent_id'];
                $tasks[$parentId]['subtasks'][] = $task;
                $tasks[$parentId]['subtask_total']++;
                if ($task['status'] === 'done') {
                    $tasks[$parentId]['subtask_done']++;
                }
            }
        }
        // Group top‑level tasks (those without a parent) by their status. Subtasks
        // will not appear in the top‑level lists since they live inside their parent.
        $grouped = [];
        foreach ($tasks as $id => $task) {
            if (empty($task['parent_id'])) {
                $grouped[$task['status']][] = $task;
            }
        }
        return $grouped;
    }

    /**
     * Get a single task by ID.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->query("SELECT t.*, u.full_name AS assignee FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id WHERE t.id = :id", ['id' => $id]);
        $task = $stmt->fetch();
        return $task ?: null;
    }

    /**
     * Retrieve all tasks across projects with assignee info. This is used
     * for reporting and notifications when we need to see tasks grouped
     * by user regardless of project. Subtasks are returned alongside
     * top‑level tasks (parent_id may be non‑null).
     *
     * @return array
     */
    public function getAllWithUsers(): array
    {
        $stmt = $this->query("SELECT t.*, u.full_name AS assignee FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id WHERE t.status <> 'deleted'", []);
        return $stmt->fetchAll();
    }

    /**
     * Create a new task.
     *
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        // Determine the next sort order within the status column for this project.
        $stmt = $this->query(
            "SELECT COALESCE(MAX(sort_order),0) + 1 AS next_sort FROM tasks WHERE project_id=:project_id AND status=:status",
            [
                'project_id' => $data['project_id'],
                'status' => $data['status'] ?? 'todo',
            ]
        );
        $row = $stmt->fetch();
        $sortOrder = $row['next_sort'] ?? 1;
        // Insert new task including priority and tags. Tags are stored as a
        // comma‑separated string. Priority defaults to 'normal'.
        $this->query(
            "INSERT INTO tasks (project_id, name, description, status, start_date, due_date, assigned_to, parent_id, priority, tags, sort_order, created_at) " .
            "VALUES (:project_id, :name, :description, :status, :start_date, :due_date, :assigned_to, :parent_id, :priority, :tags, :sort_order, NOW())",
            [
                'project_id' => $data['project_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'status' => $data['status'] ?? 'todo',
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'priority' => $data['priority'] ?? 'normal',
                'tags' => $data['tags'] ?? null,
                'sort_order' => $sortOrder,
            ]
        );
        return (int)$this->getConnection()->lastInsertId();
    }

    /**
     * Update a task.
     *
     * @param int $id
     * @param array $data
     */
    public function update(int $id, array $data): void
    {
        // Update task fields including priority and tags. Only fields provided
        // in $data will be updated; defaults are applied where necessary.
        $this->query(
            "UPDATE tasks SET name=:name, description=:description, status=:status, start_date=:start_date, due_date=:due_date, assigned_to=:assigned_to, parent_id=:parent_id, priority=:priority, tags=:tags WHERE id=:id",
            [
                'id' => $id,
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'status' => $data['status'],
                'start_date' => $data['start_date'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'priority' => $data['priority'] ?? 'normal',
                'tags' => $data['tags'] ?? null,
            ]
        );
    }

    /**
     * Delete a task and its subtasks.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        // Delete subtasks
        $this->query("DELETE FROM tasks WHERE parent_id = :id", ['id' => $id]);
        $this->query("DELETE FROM tasks WHERE id = :id", ['id' => $id]);
    }

    /**
     * Update tasks order and status for Kanban drag and drop.
     *
     * @param array $orders associative array status => [taskId1, taskId2,...]
     */
    public function updateOrder(array $orders): void
    {
        foreach ($orders as $status => $taskIds) {
            $order = 1;
            foreach ($taskIds as $taskId) {
                $this->query("UPDATE tasks SET status=:status, sort_order=:sort_order WHERE id=:id", [
                    'status' => $status,
                    'sort_order' => $order,
                    'id' => $taskId,
                ]);
                $order++;
            }
        }
    }

    /**
     * Get tasks for calendar (events) with due dates.
     *
     * @param int $projectId
     * @return array
     */
    public function getCalendarEvents(int $projectId): array
    {
        $stmt = $this->query(
            "SELECT id, name AS title, due_date AS start, due_date AS end, status FROM tasks WHERE project_id = :project_id AND due_date IS NOT NULL AND status <> 'deleted'",
            ['project_id' => $projectId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Get upcoming tasks due within a number of days across all projects.
     *
     * @param int $days
     * @return array
     */
    public function getUpcomingTasks(int $days = 7): array
    {
        $days = (int)$days;
        $sql = "SELECT t.*, p.name AS project_name FROM tasks t " .
               "LEFT JOIN projects p ON t.project_id = p.id " .
               "WHERE t.due_date IS NOT NULL AND t.due_date >= CURDATE() " .
               "AND t.due_date <= DATE_ADD(CURDATE(), INTERVAL {$days} DAY) " .
               "AND t.status <> 'deleted' " .
               "ORDER BY t.due_date ASC";
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Retrieve aggregated statistics about tasks across all projects. Useful for
     * dashboards to display counts by status and overdue tasks.
     *
     * @return array An associative array with keys: total, todo, in_progress, done, overdue
     */
    public function getStats(): array
    {
        // Count by status
        $stmt = $this->query("SELECT status, COUNT(*) AS cnt FROM tasks WHERE status <> 'deleted' GROUP BY status");
        $rows = $stmt->fetchAll();
        $stats = ['total' => 0, 'todo' => 0, 'in_progress' => 0, 'bug_review' => 0, 'done' => 0, 'overdue' => 0];
        foreach ($rows as $row) {
            $stats['total'] += (int)$row['cnt'];
            $status = $row['status'];
            if (!isset($stats[$status])) {
                $stats[$status] = 0;
            }
            $stats[$status] = (int)$row['cnt'];
        }
        // Overdue tasks: due date before today and not done
        $stmt = $this->query("SELECT COUNT(*) AS overdue_count FROM tasks WHERE due_date < CURDATE() AND status <> 'done' AND status <> 'deleted'");
        $row = $stmt->fetch();
        $stats['overdue'] = (int)$row['overdue_count'];
        return $stats;
    }

    /**
     * Retrieve counts of tasks by priority across all projects. Useful for
     * dashboards to visualise how many tasks are high, normal or low priority.
     *
     * @return array Associative array keyed by priority (high, normal, low)
     */
    public function getPriorityCounts(): array
    {
        $stmt = $this->query("SELECT priority, COUNT(*) AS cnt FROM tasks WHERE status <> 'deleted' GROUP BY priority");
        $rows = $stmt->fetchAll();
        $counts = ['high' => 0, 'normal' => 0, 'low' => 0];
        foreach ($rows as $row) {
            $priority = $row['priority'] ?? 'normal';
            $counts[$priority] = (int)$row['cnt'];
        }
        return $counts;
    }

    /**
     * Get status counts for a single project. Similar to getStats() but limited to a specific project.
     *
     * @param int $projectId
     * @return array
     */
    public function getStatsForProject(int $projectId): array
    {
        $stmt = $this->query(
            "SELECT status, COUNT(*) AS cnt FROM tasks WHERE project_id = :pid AND status <> 'deleted' GROUP BY status",
            ['pid' => $projectId]
        );
        $rows = $stmt->fetchAll();
        $stats = ['total' => 0, 'todo' => 0, 'in_progress' => 0, 'bug_review' => 0, 'done' => 0, 'overdue' => 0];
        foreach ($rows as $row) {
            $stats['total'] += (int)$row['cnt'];
            $status = $row['status'];
            if (!isset($stats[$status])) {
                $stats[$status] = 0;
            }
            $stats[$status] = (int)$row['cnt'];
        }
        // Overdue tasks for this project
        $ovStmt = $this->query(
            "SELECT COUNT(*) AS overdue_count FROM tasks WHERE project_id = :pid AND due_date < CURDATE() AND status <> 'done' AND status <> 'deleted'",
            ['pid' => $projectId]
        );
        $ovRow = $ovStmt->fetch();
        $stats['overdue'] = (int)$ovRow['overdue_count'];
        return $stats;
    }

    /**
     * Get priority counts for a single project. Returns counts of tasks by priority (high, normal, low).
     *
     * @param int $projectId
     * @return array
     */
    public function getPriorityCountsForProject(int $projectId): array
    {
        $stmt = $this->query(
            "SELECT priority, COUNT(*) AS cnt FROM tasks WHERE project_id = :pid AND status <> 'deleted' GROUP BY priority",
            ['pid' => $projectId]
        );
        $rows = $stmt->fetchAll();
        $counts = ['high' => 0, 'normal' => 0, 'low' => 0];
        foreach ($rows as $row) {
            $priority = $row['priority'] ?? 'normal';
            $counts[$priority] = (int)$row['cnt'];
        }
        return $counts;
    }

    /**
     * Retrieve a list of all unique tags across tasks along with counts.
     *
     * @return array Array of ['tag' => string, 'count' => int]
     */
    public function getAllTags(): array
    {
        $stmt = $this->query("SELECT tags FROM tasks WHERE tags IS NOT NULL AND tags <> '' AND status <> 'deleted'");
        $tagCounts = [];
        while ($row = $stmt->fetch()) {
            $tags = explode(',', $row['tags']);
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if ($tag === '') continue;
                $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
            }
        }
        $result = [];
        foreach ($tagCounts as $tag => $count) {
            $result[] = ['tag' => $tag, 'count' => $count];
        }
        // Sort alphabetically
        usort($result, function ($a, $b) {
            return strcmp($a['tag'], $b['tag']);
        });
        return $result;
    }

    /**
     * Retrieve tasks due on a specific date (YYYY-MM-DD). Includes subtasks.
     *
     * @param string $date
     * @return array
     */
    public function getTasksDueOnDate(string $date): array
    {
        // Return tasks that fall on a specific date. A task is considered due on
        // the given date if its start_date is null or before the date and its
        // due_date is null or after the date. This captures tasks spanning
        // multiple days (e.g. start=Aug 20, due=Aug 25) so they appear in
        // today/tomorrow lists throughout the duration.
        $stmt = $this->query(
            "SELECT t.*, p.name AS project_name, u.full_name AS assignee, parent.name AS parent_name
             FROM tasks t
             JOIN projects p ON t.project_id = p.id
             LEFT JOIN users u ON t.assigned_to = u.id
             LEFT JOIN tasks parent ON t.parent_id = parent.id
             WHERE (t.start_date IS NULL OR t.start_date <= :date)
               AND (t.due_date IS NULL OR t.due_date >= :date)
               AND t.status <> 'deleted'",
            ['date' => $date]
        );
        return $stmt->fetchAll();
    }

    /**
     * Retrieve tasks associated with a specific tag across all projects. The
     * search is case-insensitive and matches tags separated by commas.
     *
     * @param string $tag
     * @return array
     */
    public function getTasksByTag(string $tag): array
    {
        $tag = trim($tag);
        if ($tag === '') {
            return [];
        }
        // Use FIND_IN_SET to match tags separated by commas. To perform case-insensitive search
        // convert both sides to lower case. Avoid full table scan by using proper indexes in production.
        $stmt = $this->query(
            "SELECT t.*, p.name AS project_name, u.full_name AS assignee FROM tasks t " .
            "JOIN projects p ON t.project_id = p.id " .
            "LEFT JOIN users u ON t.assigned_to = u.id " .
            "WHERE (LOWER(tags) LIKE :like OR FIND_IN_SET(:tag, REPLACE(LOWER(tags), ' ', ''))) " .
            "AND t.status <> 'deleted'",
            [
                'like' => '%' . strtolower($tag) . '%',
                'tag' => strtolower($tag),
            ]
        );
        return $stmt->fetchAll();
    }

    /**
     * Get tasks assigned to a specific user across all projects. Each task is
     * joined with its project name. Used in the profile page.
     *
     * @param int $userId
     * @return array
     */
    public function getTasksByAssignedUser(int $userId): array
    {
        // Return tasks assigned to a user along with project name and number of attachments
        $stmt = $this->query(
            "SELECT t.*, p.name AS project_name, COUNT(f.id) AS attachment_count " .
            "FROM tasks t " .
            "JOIN projects p ON t.project_id = p.id " .
            "LEFT JOIN files f ON f.task_id = t.id " .
            "WHERE t.assigned_to = :uid AND t.status <> 'deleted' " .
            "GROUP BY t.id",
            ['uid' => $userId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Get subtasks of a specific task ordered by sort_order ascending.
     *
     * @param int $parentId
     * @return array
     */
    public function getSubtasks(int $parentId): array
    {
        $stmt = $this->query(
            "SELECT t.*, u.full_name AS assignee, COUNT(f.id) AS attachment_count FROM tasks t " .
            "LEFT JOIN users u ON t.assigned_to = u.id " .
            "LEFT JOIN files f ON f.task_id = t.id " .
            "WHERE t.parent_id = :pid AND t.status <> 'deleted' GROUP BY t.id ORDER BY t.sort_order ASC, t.id ASC",
            ['pid' => $parentId]
        );
        return $stmt->fetchAll();
    }

    /**
     * Update the sort order of a task (used for reordering subtasks).
     *
     * @param int $taskId
     * @param int $order
     */
    public function updateSortOrder(int $taskId, int $order): void
    {
        $this->query("UPDATE tasks SET sort_order = :sort WHERE id = :id", [
            'sort' => $order,
            'id' => $taskId,
        ]);
    }

    /**
     * Promote all direct subtasks of a given task to top‑level by setting their parent_id to NULL.
     * This method is used when a parent task is deleted but subtasks should be retained.
     *
     * @param int $taskId The parent task ID whose subtasks should be promoted
     */
    public function promoteSubtasks(int $taskId): void
    {
        // Update subtasks to have no parent. Keep their status intact.
        $this->query("UPDATE tasks SET parent_id = NULL WHERE parent_id = :pid", ['pid' => $taskId]);
    }

    /**
     * Recursively mark subtasks as deleted. This helper is used by softDelete() when
     * the caller requests deletion of subtasks. Each child task and its own
     * descendants will be marked as deleted.
     *
     * @param int $taskId The parent task ID
     */
    public function deleteSubtasks(int $taskId): void
    {
        // Fetch all direct subtasks
        $stmt = $this->query("SELECT id FROM tasks WHERE parent_id = :pid", ['pid' => $taskId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $childId = (int)$row['id'];
            // Recursively delete grandchildren
            $this->deleteSubtasks($childId);
            // Mark the child as deleted
            $this->query("UPDATE tasks SET status = 'deleted' WHERE id = :cid", ['cid' => $childId]);
        }
    }

    /**
     * Soft delete a task. Instead of removing the record, its status is set to 'deleted'.
     * If $deleteSubtasks is true, all subtasks will be recursively marked as deleted.
     * Otherwise subtasks will be promoted to top‑level tasks (parent_id set to NULL).
     *
     * @param int  $taskId
     * @param bool $deleteSubtasks
     */
    public function softDelete(int $taskId, bool $deleteSubtasks = false): void
    {
        if ($deleteSubtasks) {
            // Recursively mark all descendants as deleted
            $this->deleteSubtasks($taskId);
        } else {
            // Promote direct subtasks to top level
            $this->promoteSubtasks($taskId);
        }
        // Mark the parent task as deleted
        $this->query("UPDATE tasks SET status = 'deleted' WHERE id = :id", ['id' => $taskId]);
    }

    /**
     * Restore a previously soft deleted task by setting its status back to 'todo'.
     * Subtasks remain as they are. This is a basic implementation; in a more
     * sophisticated system you might store the prior status in the log details
     * and restore to that value.
     *
     * @param int $taskId
     */
    public function restore(int $taskId): void
    {
        $this->query("UPDATE tasks SET status = 'todo' WHERE id = :id", ['id' => $taskId]);
    }

    /**
     * Permanently delete a task and its subtasks. This will remove records from
     * the database. Use with caution as this action cannot be undone. This
     * simply calls the existing delete() method.
     *
     * @param int $taskId
     */
    public function forceDelete(int $taskId): void
    {
        $this->delete($taskId);
    }

    /**
     * Retrieve all tasks that have been soft deleted. Deleted tasks are those with
     * status set to 'deleted'. We join the project name for display purposes.
     *
     * @return array
     */
    public function getDeletedTasks(): array
    {
        // Backwards compatibility: delegate to the more comprehensive method
        return $this->getDeletedTasksWithInfo();
    }

    /**
     * Retrieve all soft deleted tasks along with deletion metadata.
     * This method joins to the logs table to find the most recent delete_task log
     * for each task. The returned rows include the ID of the user who
     * performed the deletion and the timestamp. If no delete log is found,
     * deleted_by_user and deleted_at will be null. The raw details string
     * from the log (JSON prefaced by 'task #id ') is returned as delete_details
     * for downstream parsing to restore relationships.
     *
     * @return array
     */
    public function getDeletedTasksWithInfo(): array
    {
        $sql = "SELECT t.*, p.name AS project_name, l.user_id AS deleted_by_user, l.created_at AS deleted_at, l.details AS delete_details
                FROM tasks t
                JOIN projects p ON t.project_id = p.id
                LEFT JOIN logs l ON l.id = (
                    SELECT ll.id FROM logs ll
                    WHERE ll.action = 'delete_task'
                      AND ll.details LIKE CONCAT('task #', t.id, '%')
                    ORDER BY ll.created_at DESC LIMIT 1
                )
                WHERE t.status = 'deleted'";
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }
}