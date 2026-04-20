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
                    COALESCE(w.sub_code, \'—\') AS sub_code,
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
    public function create(array $data, array $materials, array $boxBreakdown = []): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO production_data
                    (harvest_date, worker_id, boxes_produced, stems_cut, group_number, field_location, notes, recorded_by)
                 VALUES
                    (:harvest_date, :worker_id, :boxes_produced, :stems_cut, :group_number, :field_location, :notes, :recorded_by)'
            );
            $stmt->execute([
                ':harvest_date'   => $data['harvest_date'],
                ':worker_id'      => (int) $data['worker_id'],
                ':boxes_produced' => (int) $data['boxes_produced'],
                ':stems_cut'      => (int) ($data['stems_cut'] ?? 0),
                ':group_number'   => !empty($data['group_number']) ? (int) $data['group_number'] : null,
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

            // Insert box breakdown rows
            if (!empty($boxBreakdown)) {
                $bkStmt = $this->db->prepare(
                    'INSERT INTO production_box_breakdown
                        (production_id, box_class, box_spec, tally_count, adjusted_count, should_be_count)
                     VALUES (:pid, :cls, :spec, :tally, :adj, :should)'
                );
                foreach ($boxBreakdown as $row) {
                    $tally  = (int) ($row['tally']  ?? 0);
                    $adj    = (int) ($row['adj']    ?? 0);
                    $should = (int) ($row['should'] ?? 0);
                    if ($row['box_class'] && $row['box_spec'] && ($tally || $adj || $should)) {
                        $bkStmt->execute([
                            ':pid'    => $productionId,
                            ':cls'    => $row['box_class'],
                            ':spec'   => $row['box_spec'],
                            ':tally'  => $tally,
                            ':adj'    => $adj,
                            ':should' => $should,
                        ]);
                    }
                }
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
                    stems_cut      = :stems_cut,
                    group_number   = :group_number,
                    field_location = :field_location,
                    notes          = :notes
              WHERE production_id  = :id'
        );
        return $stmt->execute([
            ':harvest_date'   => $data['harvest_date'],
            ':worker_id'      => (int) $data['worker_id'],
            ':boxes_produced' => (int) $data['boxes_produced'],
            ':stems_cut'      => (int) ($data['stems_cut'] ?? 0),
            ':group_number'   => !empty($data['group_number']) ? (int) $data['group_number'] : null,
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

    /**
     * Get box breakdown rows for a production record.
     */
    public function getBreakdown(int $productionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM production_box_breakdown
              WHERE production_id = :pid
              ORDER BY box_class ASC, box_spec ASC'
        );
        $stmt->execute([':pid' => $productionId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all production records with box breakdowns for a date (for reports).
     */
    public function getDailyBreakdownByDate(string $date): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.production_id, p.harvest_date, p.stems_cut, p.group_number,
                    p.boxes_produced,
                    COALESCE(w.sub_code, \'—\') AS sub_code,
                    CONCAT(w.first_name, \' \', COALESCE(w.last_name, \'\')) AS worker_name,
                    b.box_class, b.box_spec, b.tally_count, b.adjusted_count, b.should_be_count
               FROM production_data p
               LEFT JOIN workers w ON w.worker_id = p.worker_id
               LEFT JOIN production_box_breakdown b ON b.production_id = p.production_id
              WHERE p.harvest_date = :date
              ORDER BY w.sub_code ASC, b.box_class ASC, b.box_spec ASC'
        );
        $stmt->execute([':date' => $date]);
        return $stmt->fetchAll();
    }
}
