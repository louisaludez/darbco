<?php
// models/HarvestParameter.php

class HarvestParameter {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT hp.*, u.full_name as recorded_by_name
            FROM harvest_parameters hp
            JOIN users u ON hp.recorded_by = u.user_id
            ORDER BY hp.harvest_date DESC, hp.hp_id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT hp.*, u.full_name as recorded_by_name
            FROM harvest_parameters hp
            JOIN users u ON hp.recorded_by = u.user_id
            WHERE hp.hp_id = ?
        ");
        $stmt->execute([$id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$record) return null;

        // Get calibrations
        $stmtCal = $this->db->prepare("SELECT * FROM hp_calibrations WHERE hp_id = ?");
        $stmtCal->execute([$id]);
        $record['calibrations'] = $stmtCal->fetchAll(PDO::FETCH_ASSOC);

        // Get farm rejects
        $stmtRej = $this->db->prepare("SELECT * FROM hp_farm_rejects WHERE hp_id = ?");
        $stmtRej->execute([$id]);
        $record['farm_rejects'] = $stmtRej->fetch(PDO::FETCH_ASSOC);

        // Get defects
        $stmtDef = $this->db->prepare("SELECT * FROM hp_defects WHERE hp_id = ?");
        $stmtDef->execute([$id]);
        $record['defects'] = $stmtDef->fetchAll(PDO::FETCH_ASSOC);

        return $record;
    }

    public function create(array $data, int $userId): int {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO harvest_parameters (
                    harvest_date, cutting_group, crew_size, manhours, stem_cut,
                    farm_rejects_total, ave_fingerlength, ave_handclass,
                    ave_stem_weight, percent_area_covered, ave_calibration, recorded_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['harvest_date'],
                $data['cutting_group'] ?? null,
                $data['crew_size'] ?? 0,
                $data['manhours'] ?? 0,
                $data['stem_cut'] ?? 0,
                $data['farm_rejects_total'] ?? 0,
                $data['ave_fingerlength'] ?? 0,
                $data['ave_handclass'] ?? 0,
                $data['ave_stem_weight'] ?? 0,
                $data['percent_area_covered'] ?? 0,
                $data['ave_calibration'] ?? 0,
                $userId
            ]);
            $hpId = (int)$this->db->lastInsertId();

            // Insert calibrations
            if (!empty($data['calibrations'])) {
                $stmtCal = $this->db->prepare("
                    INSERT INTO hp_calibrations (hp_id, data_type, week_11, week_12, week_13, week_14)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                foreach ($data['calibrations'] as $type => $c) {
                    if ($type === 'CALIBRATION' || $type === 'COLOR_CODE') {
                        $stmtCal->execute([
                            $hpId, $type,
                            $c['week_11'] ?? null,
                            $c['week_12'] ?? null,
                            $c['week_13'] ?? null,
                            $c['week_14'] ?? null
                        ]);
                    }
                }
            }

            // Insert farm rejects
            if (!empty($data['farm_rejects'])) {
                $fr = $data['farm_rejects'];
                $stmtRej = $this->db->prepare("
                    INSERT INTO hp_farm_rejects (hp_id, code_11, code_12, code_13, code_14, total)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmtRej->execute([
                    $hpId,
                    $fr['code_11'] ?? null,
                    $fr['code_12'] ?? null,
                    $fr['code_13'] ?? null,
                    $fr['code_14'] ?? null,
                    $fr['total'] ?? null
                ]);
            }

            // Insert defects
            if (!empty($data['defects'])) {
                $stmtDef = $this->db->prepare("
                    INSERT INTO hp_defects (hp_id, defect_name, age_8_wks, age_9_wks, age_10_wks, age_11_wks, total)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                foreach ($data['defects'] as $def) {
                    if (!empty($def['name'])) {
                        $stmtDef->execute([
                            $hpId,
                            $def['name'],
                            $def['w8'] ?? null,
                            $def['w9'] ?? null,
                            $def['w10'] ?? null,
                            $def['w11'] ?? null,
                            $def['total'] ?? null
                        ]);
                    }
                }
            }

            $this->db->commit();
            return $hpId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM harvest_parameters WHERE hp_id = ?");
        return $stmt->execute([$id]);
    }
}
