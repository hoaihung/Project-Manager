<?php
namespace app\Model;

use app\Core\Model;

/**
 * Class Project
 *
 * Handles CRUD operations for projects.
 */
class Project extends Model
{
    /**
     * Get all projects with associated statistics (e.g. task counts).
     *
     * @return array
     */
    public function all(): array
    {
        $stmt = $this->query(
            "SELECT p.*, 
                (SELECT COUNT(*) FROM tasks WHERE project_id = p.id) AS task_count,
                (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND status = 'done') AS done_count,
                (SELECT COUNT(*) FROM tasks WHERE project_id = p.id AND due_date < CURDATE() AND status <> 'done') AS overdue_count
             FROM projects p ORDER BY p.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Find a project by ID.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->query("SELECT * FROM projects WHERE id = :id", ['id' => $id]);
        $project = $stmt->fetch();
        return $project ?: null;
    }

    /**
     * Create a new project.
     *
     * @param array $data
     * @return int Inserted ID
     */
    public function create(array $data): int
    {
        $stmt = $this->query(
            "INSERT INTO projects (name, description, status, start_date, end_date, created_at) VALUES (:name, :description, :status, :start_date, :end_date, NOW())",
            [
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'status' => $data['status'] ?? 'new',
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
            ]
        );
        return (int)$this->getConnection()->lastInsertId();
    }

    /**
     * Update a project.
     *
     * @param int $id
     * @param array $data
     */
    public function update(int $id, array $data): void
    {
        $this->query(
            "UPDATE projects SET name=:name, description=:description, status=:status, start_date=:start_date, end_date=:end_date WHERE id=:id",
            [
                'id' => $id,
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'status' => $data['status'] ?? 'new',
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
            ]
        );
    }

    /**
     * Delete a project and cascade delete tasks.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        // Delete tasks first (foreign key cascade can also do this)
        $this->query("DELETE FROM tasks WHERE project_id = :id", ['id' => $id]);
        $this->query("DELETE FROM projects WHERE id = :id", ['id' => $id]);
    }

    /**
     * Get projects whose end date is within the next X days.
     *
     * This helper is used on the dashboard to highlight projects
     * approaching their end dates. Projects without an `end_date` are
     * excluded.
     *
     * @param int $days
     * @return array
     */
    public function getUpcomingProjects(int $days = 7): array
    {
        $days = (int) $days;
        // Build SQL using integer interpolation for the interval. Casting
        // ensures no arbitrary content can be injected.
        $sql = "SELECT p.*, " .
               "(SELECT COUNT(*) FROM tasks WHERE project_id = p.id) AS task_count " .
               "FROM projects p " .
               "WHERE p.end_date IS NOT NULL " .
               "AND p.end_date >= CURDATE() " .
               "AND p.end_date <= DATE_ADD(CURDATE(), INTERVAL {$days} DAY) " .
               "ORDER BY p.end_date ASC";
        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }
}