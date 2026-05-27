<?php
// models/DailyReport.php

class DailyReport {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT dr.*, u.full_name as recorded_by_name
            FROM daily_production_reports dr
            JOIN users u ON dr.recorded_by = u.user_id
            ORDER BY dr.report_date DESC, dr.dpr_id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT dr.*, u.full_name as recorded_by_name
            FROM daily_production_reports dr
            JOIN users u ON dr.recorded_by = u.user_id
            WHERE dr.dpr_id = ?
        ");
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$record) return null;

        // Get boxes
        $stmtBoxes = $this->db->prepare("SELECT * FROM dpr_boxes WHERE dpr_id = ?");
        $stmtBoxes->execute([$id]);
        $record['boxes'] = $stmtBoxes->fetchAll(PDO::FETCH_ASSOC);

        return $record;
    }

    public function create(array $data, int $userId): int {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO daily_production_reports (
                    report_date, week_no, brand_name, crew_size,
                    first_fruit_in, last_box_out, first_box_out,
                    volume_stems_cut, bs_ratio, per_pack_plan, recorded_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['report_date'],
                $data['week_no'] ?? null,
                $data['brand_name'] ?? null,
                $data['crew_size'] ?? 0,
                !empty($data['first_fruit_in']) ? $data['first_fruit_in'] : null,
                !empty($data['last_box_out']) ? $data['last_box_out'] : null,
                !empty($data['first_box_out']) ? $data['first_box_out'] : null,
                $data['volume_stems_cut'] ?? 0,
                $data['bs_ratio'] ?? null,
                $data['per_pack_plan'] ?? null,
                $userId
            ]);
            $dprId = (int)$this->db->lastInsertId();

            // Insert boxes
            if (!empty($data['boxes'])) {
                $stmtBox = $this->db->prepare("
                    INSERT INTO dpr_boxes (dpr_id, box_class, group_name, box_spec, box_count)
                    VALUES (?, ?, ?, ?, ?)
                ");
                foreach ($data['boxes'] as $b) {
                    if (!empty($b['spec']) && isset($b['count']) && $b['count'] !== '') {
                        $stmtBox->execute([
                            $dprId,
                            $b['class'],     // 'A' or 'B'
                            $b['group'],     // 'GRP 1', 'GRP 3', 'HMLND', 'TOTAL'
                            $b['spec'],      // e.g. '4 Hands'
                            (int)$b['count']
                        ]);
                    }
                }
            }

            $this->db->commit();
            return $dprId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM daily_production_reports WHERE dpr_id = ?");
        return $stmt->execute([$id]);
    }
}
