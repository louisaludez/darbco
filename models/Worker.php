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
        $stmt = $this->db->query("SELECT * FROM workers ORDER BY sub_code ASC, first_name ASC, last_name ASC");
        return $stmt->fetchAll();
    }

    public function getActive(): array
    {
        $stmt = $this->db->query("SELECT * FROM workers WHERE is_active = 1 ORDER BY sub_code ASC, first_name ASC, last_name ASC");
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO workers (sub_code, first_name, last_name, contact_number, area, is_active)
             VALUES (:sc, :f, :l, :c, :area, :a)'
        );
        $stmt->execute([
            ':sc'   => $data['sub_code']       ?? null,
            ':f'    => $data['first_name'],
            ':l'    => $data['last_name'],
            ':c'    => $data['contact_number'] ?? null,
            ':area' => $data['area']            ?? null,
            ':a'    => $data['is_active']      ?? 1
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE workers
                SET sub_code       = :sc,
                    first_name     = :f,
                    last_name      = :l,
                    contact_number = :c,
                    area           = :area,
                    is_active      = :a
              WHERE worker_id      = :id'
        );
        return $stmt->execute([
            ':sc'   => $data['sub_code']       ?? null,
            ':f'    => $data['first_name'],
            ':l'    => $data['last_name'],
            ':c'    => $data['contact_number'] ?? null,
            ':area' => $data['area']            ?? null,
            ':a'    => $data['is_active']      ?? 1,
            ':id'   => $id
        ]);
    }

    public function toggleStatus(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE workers SET is_active = NOT is_active WHERE worker_id = :id');
        return $stmt->execute([':id' => $id]);
    }
}
