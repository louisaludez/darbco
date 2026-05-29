<?php
// ============================================================
//  Model: Production
//  File : models/Production.php
//  Handles: Daily harvest records, materials used, stem details
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
     * Insert a production record plus its materials list and stem details.
     * Wraps in a transaction — inventory deduction is handled
     * by the trg_deduct_inventory MySQL trigger automatically.
     *
     * @param array $data          Production fields
     * @param array $defects       Defect matrix [['name'=>'A','w8'=>'1',...], ...]
     * @param array $boxBreakdown  Box class/spec breakdown rows
     * @param array $stemDetails   Per-row stem counts [['row_number'=>11,'stem_count'=>5], ...]
     */
    public function create(array $data, array $defects = [], array $boxBreakdown = [], array $stemDetails = []): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO production_data
                    (harvest_date, worker_id, boxes_produced, stems_cut, hands, small_hands, class_a_fp, class_b_h, class_b_id, class_b_cl_b, group_number,
                     block_number, carrier_name, arrival_time, first_box_out, last_box_out,
                     week_number, cycle_code, field_location, notes, recorded_by)
                 VALUES
                    (:harvest_date, :worker_id, :boxes_produced, :stems_cut, :hands, :small_hands, :class_a_fp, :class_b_h, :class_b_id, :class_b_cl_b, :group_number,
                     :block_number, :carrier_name, :arrival_time, :first_box_out, :last_box_out,
                     :week_number, :cycle_code, :field_location, :notes, :recorded_by)'
            );
            $stmt->execute([
                ':harvest_date'   => $data['harvest_date'],
                ':worker_id'      => (int) $data['worker_id'],
                ':boxes_produced' => (int) $data['boxes_produced'],
                ':stems_cut'      => (int) ($data['stems_cut'] ?? 0),
                ':hands'          => (int) ($data['hands'] ?? 0),
                ':small_hands'    => (int) ($data['small_hands'] ?? 0),
                ':class_a_fp'     => (int) ($data['class_a_fp'] ?? 0),
                ':class_b_h'      => (int) ($data['class_b_h'] ?? 0),
                ':class_b_id'     => (int) ($data['class_b_id'] ?? 0),
                ':class_b_cl_b'   => (int) ($data['class_b_cl_b'] ?? 0),
                ':group_number'   => !empty($data['group_number']) ? (int) $data['group_number'] : null,
                ':block_number'   => $data['block_number']   ?? null,
                ':carrier_name'   => $data['carrier_name']   ?? null,
                ':arrival_time'   => !empty($data['arrival_time'])   ? $data['arrival_time']   : null,
                ':first_box_out'  => !empty($data['first_box_out'])  ? $data['first_box_out']  : null,
                ':last_box_out'   => !empty($data['last_box_out'])   ? $data['last_box_out']   : null,
                ':week_number'    => $data['week_number']    ?? null,
                ':cycle_code'     => $data['cycle_code']     ?? null,
                ':field_location' => $data['field_location'] ?? null,
                ':notes'          => $data['notes']          ?? null,
                ':recorded_by'    => (int) $data['recorded_by'],
            ]);
            $productionId = (int) $this->db->lastInsertId();

            // Insert defects
            if (!empty($defects)) {
                $defStmt = $this->db->prepare(
                    'INSERT INTO production_defects
                        (production_id, defect_name, age_8_wks, age_9_wks, age_10_wks, age_11_wks, total)
                     VALUES (:pid, :name, :w8, :w9, :w10, :w11, :total)'
                );
                foreach ($defects as $def) {
                    if (trim($def['name'])) {
                        $defStmt->execute([
                            ':pid'   => $productionId,
                            ':name'  => $def['name'],
                            ':w8'    => $def['w8'] ?? null,
                            ':w9'    => $def['w9'] ?? null,
                            ':w10'   => $def['w10'] ?? null,
                            ':w11'   => $def['w11'] ?? null,
                            ':total' => $def['total'] ?? null,
                        ]);
                    }
                }
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

            // Insert per-row stem details (rows 11, 12, 13, 14 from harvest sheet)
            if (!empty($stemDetails)) {
                $sdStmt = $this->db->prepare(
                    'INSERT INTO production_stem_details (production_id, row_number, stem_count)
                     VALUES (:pid, :rn, :sc)'
                );
                foreach ($stemDetails as $sd) {
                    if (!empty($sd['stem_count'])) {
                        $sdStmt->execute([
                            ':pid' => $productionId,
                            ':rn'  => (int) $sd['row_number'],
                            ':sc'  => (int) $sd['stem_count'],
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
                    hands          = :hands,
                    small_hands    = :small_hands,
                    class_a_fp     = :class_a_fp,
                    class_b_h      = :class_b_h,
                    class_b_id     = :class_b_id,
                    class_b_cl_b   = :class_b_cl_b,
                    group_number   = :group_number,
                    block_number   = :block_number,
                    carrier_name   = :carrier_name,
                    arrival_time   = :arrival_time,
                    first_box_out  = :first_box_out,
                    last_box_out   = :last_box_out,
                    week_number    = :week_number,
                    cycle_code     = :cycle_code,
                    field_location = :field_location,
                    notes          = :notes
              WHERE production_id  = :id'
        );
        return $stmt->execute([
            ':harvest_date'   => $data['harvest_date'],
            ':worker_id'      => (int) $data['worker_id'],
            ':boxes_produced' => (int) $data['boxes_produced'],
            ':stems_cut'      => (int) ($data['stems_cut'] ?? 0),
            ':hands'          => (int) ($data['hands'] ?? 0),
            ':small_hands'    => (int) ($data['small_hands'] ?? 0),
            ':class_a_fp'     => (int) ($data['class_a_fp'] ?? 0),
            ':class_b_h'      => (int) ($data['class_b_h'] ?? 0),
            ':class_b_id'     => (int) ($data['class_b_id'] ?? 0),
            ':class_b_cl_b'   => (int) ($data['class_b_cl_b'] ?? 0),
            ':group_number'   => !empty($data['group_number']) ? (int) $data['group_number'] : null,
            ':block_number'   => $data['block_number']   ?? null,
            ':carrier_name'   => $data['carrier_name']   ?? null,
            ':arrival_time'   => !empty($data['arrival_time'])   ? $data['arrival_time']   : null,
            ':first_box_out'  => !empty($data['first_box_out'])  ? $data['first_box_out']  : null,
            ':last_box_out'   => !empty($data['last_box_out'])   ? $data['last_box_out']   : null,
            ':week_number'    => $data['week_number']    ?? null,
            ':cycle_code'     => $data['cycle_code']     ?? null,
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

    /**
     * Get defects matrix for a specific production record.
     */
    public function getDefects(int $productionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM production_defects
              WHERE production_id = :pid
              ORDER BY id ASC'
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
     * Get per-row stem details for a production record.
     */
    public function getStemDetails(int $productionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM production_stem_details
              WHERE production_id = :pid
              ORDER BY row_number ASC'
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
                    p.boxes_produced, p.block_number, p.carrier_name, p.arrival_time,
                    p.first_box_out, p.last_box_out, p.week_number, p.cycle_code,
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

    /**
     * Calculate running total harvest for a worker up to a given date.
     */
    public function getRunningTotal(int $workerId, string $upToDate): int
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(stems_cut), 0)
               FROM production_data
              WHERE worker_id = :wid AND harvest_date <= :dt'
        );
        $stmt->execute([':wid' => $workerId, ':dt' => $upToDate]);
        return (int) $stmt->fetchColumn();
    }
}
