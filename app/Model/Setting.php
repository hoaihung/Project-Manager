<?php
namespace app\Model;

use app\Core\Model;

/**
 * Class Setting
 *
 * Provides CRUD operations for application settings stored in the `settings` table.
 */
class Setting extends Model
{
    /**
     * Get a setting value by key. Returns null if not found.
     *
     * @param string $key
     * @return string|null
     */
    public function get(string $key): ?string
    {
        $stmt = $this->query("SELECT `value` FROM settings WHERE `key` = :key", ['key' => $key]);
        $row = $stmt->fetch();
        return $row ? $row['value'] : null;
    }

    /**
     * Set/update a setting value by key. Creates the setting if it does not exist.
     *
     * @param string $key
     * @param string $value
     */
    public function set(string $key, string $value): void
    {
        // Try update first
        $stmt = $this->query("UPDATE settings SET `value` = :value WHERE `key` = :key", [
            'key' => $key,
            'value' => $value,
        ]);
        if ($stmt->rowCount() === 0) {
            // Insert if no rows updated
            $this->query("INSERT INTO settings (`key`, `value`) VALUES (:key, :value)", [
                'key' => $key,
                'value' => $value,
            ]);
        }
    }

    /**
     * Retrieve all settings as an associative array.
     *
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->query("SELECT * FROM settings");
        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = $row['value'];
        }
        return $result;
    }
}