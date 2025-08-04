<?php
namespace app\Core;

use PDO;
use PDOException;

/**
 * Class Model
 *
 * Base model providing a shared PDO connection and helper methods for
 * executing queries. All of your models should extend this base class
 * to gain access to the database connection.
 */
abstract class Model
{
    /**
     * @var PDO|null
     */
    protected static ?PDO $pdo = null;

    /**
     * Get PDO connection instance.
     *
     * @return PDO
     */
    protected function getConnection(): PDO
    {
        if (static::$pdo === null) {
            $config = require __DIR__ . '/../../config/config.php';
            $db = $config['db'];
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $db['host'], $db['name'], $db['charset']);
            try {
                static::$pdo = new PDO($dsn, $db['user'], $db['pass']);
                static::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                static::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }
        }
        return static::$pdo;
    }

    /**
     * Execute a query with optional parameters and return a PDOStatement.
     *
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    protected function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}