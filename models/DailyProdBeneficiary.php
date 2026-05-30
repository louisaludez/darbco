<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

class DailyProdBeneficiary
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new Daily Production Per Beneficiary record
     */
    public function create(array $data, int $userId): int
    {
        try {
            $this->db->beginTransaction();

            // Insert parent record
            $stmt = $this->db->prepare("
                INSERT INTO daily_prod_beneficiary (packing_date, recorded_by)
                VALUES (:packing_date, :recorded_by)
            ");
            $stmt->execute([
                ':packing_date' => $data['packing_date'],
                ':recorded_by'  => $userId
            ]);

            $parentId = (int) $this->db->lastInsertId();

            // Insert items
            $itemStmt = $this->db->prepare("
                INSERT INTO daily_prod_beneficiary_items (
                    parent_id, sub_code, arb_name, stems_cut, 
                    class_a_hands, class_a_sh, class_a_blank1, class_a_fp, class_a_blank2, class_a_blank3, class_a_cl_b,
                    class_b_h, class_b_id
                ) VALUES (
                    :parent_id, :sub_code, :arb_name, :stems_cut,
                    :class_a_hands, :class_a_sh, :class_a_blank1, :class_a_fp, :class_a_blank2, :class_a_blank3, :class_a_cl_b,
                    :class_b_h, :class_b_id
                )
            ");

            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $itemStmt->execute([
                        ':parent_id'      => $parentId,
                        ':sub_code'       => $item['sub_code'] ?? null,
                        ':arb_name'       => $item['arb_name'] ?? null,
                        ':stems_cut'      => $item['stems_cut'] ?? null,
                        ':class_a_hands'  => $item['class_a_hands'] ?? null,
                        ':class_a_sh'     => $item['class_a_sh'] ?? null,
                        ':class_a_blank1' => $item['class_a_blank1'] ?? null,
                        ':class_a_fp'     => $item['class_a_fp'] ?? null,
                        ':class_a_blank2' => $item['class_a_blank2'] ?? null,
                        ':class_a_blank3' => $item['class_a_blank3'] ?? null,
                        ':class_a_cl_b'   => $item['class_a_cl_b'] ?? null,
                        ':class_b_h'      => $item['class_b_h'] ?? null,
                        ':class_b_id'     => $item['class_b_id'] ?? null,
                    ]);
                }
            }

            $this->db->commit();
            return $parentId;

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('DailyProdBeneficiary::create Error: ' . $e->getMessage());
            throw new RuntimeException('Failed to create Daily Production Per Beneficiary record.');
        }
    }

    /**
     * Get all parent records with total beneficiary count
     */
    public function getAll(): array
    {
        $sql = "
            SELECT 
                dpb.id,
                dpb.packing_date,
                dpb.created_at,
                (SELECT COUNT(*) FROM daily_prod_beneficiary_items WHERE parent_id = dpb.id) as total_beneficiaries
            FROM daily_prod_beneficiary dpb
            ORDER BY dpb.packing_date DESC, dpb.created_at DESC
        ";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Get a specific record by ID including its items
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM daily_prod_beneficiary WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $record = $stmt->fetch();

        if (!$record) return null;

        $itemStmt = $this->db->prepare("SELECT * FROM daily_prod_beneficiary_items WHERE parent_id = :id");
        $itemStmt->execute([':id' => $id]);
        $record['items'] = $itemStmt->fetchAll();

        return $record;
    }
}
