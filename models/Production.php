<?php
// ============================================================
//  Model: Production
//  File : models/Production.php
//  Handles: Daily harvest records, materials used
// ============================================================

declare(strict_types=1);

class Production
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Return all production records with recorder name.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT p.*, u.full_name AS recorded_by_name, 
                    CONCAT(w.first_name, \' \', COALESCE(w.last_name, \'\')) AS worker_name
               FROM production_data p
               JOIN users u ON u.user_id = p.recorded_by
               LEFT JOIN workers w ON w.worker_id = p.worker_id
              ORDER BY p.harvest_date DESC, p.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Return a single production record and its materials.
     */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, u.full_name AS recorded_by_name,
                    CONCAT(w.first_name, \' \', COALESCE(w.last_name, \'\')) AS worker_name
               FROM production_data p
               JOIN users u ON u.user_id = p.recorded_by
               LEFT JOIN workers w ON w.worker_id = p.worker_id
              WHERE p.production_id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Insert a production record plus its materials list.
     * Wraps in a transaction — inventory deduction is handled
     * by the trg_deduct_inventory MySQL trigger automatically.
     *
     * @param array $data       Production fields
     * @param array $materials  [['item_id'=>X,'qty'=>Y], ...]
     */
    public function create(array $data, array $materials): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO production_data
                    (harvest_date, worker_id, boxes_produced, field_location, notes, recorded_by)
                 VALUES
                    (:harvest_date, :worker_id, :boxes_produced, :field_location, :notes, :recorded_by)'
            );
            $stmt->execute([
                ':harvest_date'   => $data['harvest_date'],
                ':worker_id'      => (int) $data['worker_id'],
                ':boxes_produced' => (int) $data['boxes_produced'],
                ':field_location' => $data['field_location'] ?? null,
                ':notes'          => $data['notes']          ?? null,
                ':recorded_by'    => (int) $data['recorded_by'],
            ]);
            $productionId = (int) $this->db->lastInsertId();

            // Insert materials — trigger fires per row
            $matStmt = $this->db->prepare(
                'INSERT INTO production_materials (production_id, item_id, quantity_used)
                 VALUES (:pid, :iid, :qty)'
            );
            foreach ($materials as $mat) {
                $matStmt->execute([
                    ':pid' => $productionId,
                    ':iid' => (int)   $mat['item_id'],
                    ':qty' => (float) $mat['quantity_used'],
                ]);
            }

            $this->db->commit();
            return $productionId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Dashboard widget: total boxes produced today.
     */
    public function getTotalBoxesToday(): int
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(boxes_produced), 0) AS total
               FROM production_data
              WHERE harvest_date = CURDATE()'
        );
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    /**
     * Dashboard widget: records this month.
     */
    public function getMonthlyCount(): int
    {
        $stmt = $this->db->query(
            'SELECT COUNT(*) FROM production_data
              WHERE MONTH(harvest_date) = MONTH(CURDATE())
                AND YEAR(harvest_date)  = YEAR(CURDATE())'
        );
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get materials used for a specific production record.
     */
    /**
     * Update core harvest fields (does NOT touch materials/inventory).
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE production_data
                SET harvest_date   = :harvest_date,
                    worker_id      = :worker_id,
                    boxes_produced = :boxes_produced,
                    field_location = :field_location,
                    notes          = :notes
              WHERE production_id  = :id'
        );
        return $stmt->execute([
            ':harvest_date'   => $data['harvest_date'],
            ':worker_id'      => (int) $data['worker_id'],
            ':boxes_produced' => (int) $data['boxes_produced'],
            ':field_location' => $data['field_location'] ?? null,
            ':notes'          => $data['notes']          ?? null,
            ':id'             => $id,
        ]);
    }

    /**
     * Delete a production record.
     * The trg_restore_inventory trigger fires automatically per
     * production_materials row deleted (via CASCADE), restoring stock.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM production_data WHERE production_id = :id'
        );
        return $stmt->execute([':id' => $id]);
    }

    public function getMaterials(int $productionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pm.quantity_used, i.item_name, i.unit
               FROM production_materials pm
               JOIN inventory_data i ON i.item_id = pm.item_id
              WHERE pm.production_id = :pid'
        );
        $stmt->execute([':pid' => $productionId]);
        return $stmt->fetchAll();
    }
}
