<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

class DailyBox
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new Daily Box record with its child items
     */
    public function create(array $data, int $userId): int
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO daily_boxes (packing_date, first_box_out, last_box_out, recorded_by)
                VALUES (:packing_date, :first_box_out, :last_box_out, :recorded_by)
            ");
            $stmt->execute([
                ':packing_date'  => $data['db_date'],
                ':first_box_out' => $data['db_first_time'] ?: null,
                ':last_box_out'  => $data['db_last_time'] ?: null,
                ':recorded_by'   => $userId
            ]);

            $dailyBoxId = (int) $this->db->lastInsertId();

            $itemStmt = $this->db->prepare("
                INSERT INTO daily_boxes_items 
                (daily_box_id, class, row_label, group_num, tally, adj, should_be)
                VALUES 
                (:daily_box_id, :class, :row_label, :group_num, :tally, :adj, :should_be)
            ");

            // Process Class A Items
            if (isset($data['class_a'])) {
                foreach ($data['class_a'] as $item) {
                    $itemStmt->execute([
                        ':daily_box_id' => $dailyBoxId,
                        ':class'        => 'A',
                        ':row_label'    => $item['row_label'],
                        ':group_num'    => $item['group_num'],
                        ':tally'        => $item['tally'] ?: 0,
                        ':adj'          => $item['adj'] ?: 0,
                        ':should_be'    => $item['should_be'] ?: 0
                    ]);
                }
            }
            
            // Process Class B Items
            if (isset($data['class_b'])) {
                foreach ($data['class_b'] as $item) {
                    $itemStmt->execute([
                        ':daily_box_id' => $dailyBoxId,
                        ':class'        => 'B',
                        ':row_label'    => $item['row_label'],
                        ':group_num'    => $item['group_num'],
                        ':tally'        => $item['tally'] ?: 0,
                        ':adj'          => $item['adj'] ?: 0,
                        ':should_be'    => $item['should_be'] ?: 0
                    ]);
                }
            }

            $this->db->commit();
            return $dailyBoxId;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('DailyBox::create Error: ' . $e->getMessage());
            throw new RuntimeException('Failed to create Daily Box record.');
        }
    }

    /**
     * Get all Daily Box records with computed totals
     */
    public function getAll(): array
    {
        $sql = "
            SELECT 
                db.id,
                db.packing_date,
                db.first_box_out,
                db.last_box_out,
                db.created_at,
                (SELECT SUM(tally + adj) FROM daily_boxes_items WHERE daily_box_id = db.id AND class = 'A') as total_class_a,
                (SELECT SUM(tally + adj) FROM daily_boxes_items WHERE daily_box_id = db.id AND class = 'B') as total_class_b,
                (SELECT SUM(tally + adj) FROM daily_boxes_items WHERE daily_box_id = db.id) as total_boxes
            FROM daily_boxes db
            ORDER BY db.created_at DESC
        ";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get a specific Daily Box record by ID including items
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM daily_boxes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $record = $stmt->fetch();

        if (!$record) return null;

        $itemStmt = $this->db->prepare("SELECT * FROM daily_boxes_items WHERE daily_box_id = :id");
        $itemStmt->execute([':id' => $id]);
        $record['items'] = $itemStmt->fetchAll();

        return $record;
    }
}