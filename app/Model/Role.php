<?php
namespace app\Model;

use app\Core\Model;

/**
 * Class Role
 *
 * Simple model to retrieve available roles.
 */
class Role extends Model
{
    /**
     * Get all roles.
     *
     * @return array
     */
    public function all(): array
    {
        $stmt = $this->query("SELECT * FROM roles ORDER BY id ASC");
        return $stmt->fetchAll();
    }
}