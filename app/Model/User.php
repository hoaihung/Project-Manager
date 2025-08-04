<?php
namespace app\Model;

use app\Core\Model;
use PDO;

/**
 * Class User
 *
 * Handles CRUD operations for users and authentication.
 */
class User extends Model
{
    /**
     * Find user by username.
     *
     * @param string $username
     * @return array|null
     */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->query("SELECT u.*, r.name AS role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE username = :username LIMIT 1", ['username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Find user by ID.
     *
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->query("SELECT u.*, r.name AS role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = :id", ['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Retrieve all users.
     *
     * @return array
     */
    public function all(): array
    {
        $stmt = $this->query("SELECT u.*, r.name AS role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.username ASC");
        return $stmt->fetchAll();
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return int Inserted user ID
     */
    public function create(array $data): int
    {
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->query("INSERT INTO users (username, password, full_name, email, role_id, created_at) VALUES (:username, :password, :full_name, :email, :role_id, NOW())", [
            'username' => $data['username'],
            'password' => $passwordHash,
            'full_name' => $data['full_name'] ?? '',
            'email' => $data['email'] ?? '',
            'role_id' => $data['role_id'],
        ]);
        return (int)$this->getConnection()->lastInsertId();
    }

    /**
     * Update an existing user.
     *
     * @param int $id
     * @param array $data
     */
    public function update(int $id, array $data): void
    {
        $params = [
            'id' => $id,
            'full_name' => $data['full_name'] ?? '',
            'email' => $data['email'] ?? '',
            'role_id' => $data['role_id'],
        ];
        $sql = "UPDATE users SET full_name = :full_name, email = :email, role_id = :role_id";
        if (!empty($data['password'])) {
            $sql .= ", password = :password";
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $sql .= " WHERE id = :id";
        $this->query($sql, $params);
    }

    /**
     * Delete a user.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        $this->query("DELETE FROM users WHERE id = :id", ['id' => $id]);
    }

    /**
     * Verify user credentials for login.
     *
     * @param string $username
     * @param string $password
     * @return array|null The user record if authentication succeeds
     */
    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);
        if ($user) {
            // Prefer password_verify for hashed passwords
            if (password_verify($password, $user['password'])) {
                return $user;
            }
            // Fallback: allow plain text match for demonstration purposes
            if ($password === $user['password']) {
                return $user;
            }
        }
        return null;
    }
}