<?php
// models/Worker.php

declare(strict_types=1);

class Worker
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM workers ORDER BY first_name ASC, last_name ASC");
        return $stmt->fetchAll();
    }

    public function getActive(): array
    {
        $stmt = $this->db->query("SELECT * FROM workers WHERE is_active = 1 ORDER BY first_name ASC, last_name ASC");
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO workers (first_name, last_name, contact_number, is_active)
             VALUES (:f, :l, :c, :a)'
        );
        $stmt->execute([
            ':f' => $data['first_name'],
            ':l' => $data['last_name'],
            ':c' => $data['contact_number'] ?? null,
            ':a' => $data['is_active'] ?? 1
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE workers
                SET first_name     = :f,
                    last_name      = :l,
                    contact_number = :c,
                    is_active      = :a
              WHERE worker_id      = :id'
        );
        return $stmt->execute([
            ':f'  => $data['first_name'],
            ':l'  => $data['last_name'],
            ':c'  => $data['contact_number'] ?? null,
            ':a'  => $data['is_active'] ?? 1,
            ':id' => $id
        ]);
    }

    public function toggleStatus(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE workers SET is_active = NOT is_active WHERE worker_id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
