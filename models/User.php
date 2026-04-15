<?php
// ============================================================
//  Model: User
//  File : models/User.php
//  Handles: Authentication queries, user CRUD
// ============================================================

declare(strict_types=1);

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find a single active user by username.
     */
    public function findByUsername(string $username): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT user_id, full_name, username, email, password_hash, role
               FROM users
              WHERE username  = :username
                AND is_active = 1
              LIMIT 1'
        );
        $stmt->execute([':username' => $username]);
        return $stmt->fetch();
    }

    /**
     * Find a user by their primary key.
     */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT user_id, full_name, username, email, role, is_active, created_at
               FROM users WHERE user_id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Return all users (admin view).
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT user_id, full_name, username, email, role, is_active, created_at
               FROM users ORDER BY created_at DESC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Insert a new user.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (full_name, username, email, password_hash, role)
             VALUES (:full_name, :username, :email, :password_hash, :role)'
        );
        $stmt->execute([
            ':full_name'     => $data['full_name'],
            ':username'      => $data['username'],
            ':email'         => $data['email'],
            ':password_hash' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            ':role'          => $data['role'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Toggle active status.
     */
    public function setActive(int $id, bool $status): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET is_active = :s WHERE user_id = :id'
        );
        return $stmt->execute([':s' => (int) $status, ':id' => $id]);
    }

    /**
     * Update a user's bcrypt password hash.
     */
    public function updatePassword(int $id, string $newHash): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET password_hash = :hash WHERE user_id = :id'
        );
        return $stmt->execute([':hash' => $newHash, ':id' => $id]);
    }
}
