<?php
// ============================================================
//  Model: Payroll
//  File : models/Payroll.php
//  Handles: Payroll computation, approval workflow
// ============================================================

declare(strict_types=1);

class Payroll
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * All payroll records with actor names.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT p.*,
                    c.full_name AS computed_by_name,
                    r.full_name AS reviewed_by_name,
                    a.full_name AS approved_by_name,
                    CONCAT(w.first_name, \' \', COALESCE(w.last_name, \'\')) AS worker_name
               FROM payroll_data p
               JOIN users c ON c.user_id = p.computed_by
          LEFT JOIN users r ON r.user_id = p.reviewed_by
          LEFT JOIN users a ON a.user_id = p.approved_by
          LEFT JOIN workers w ON w.worker_id = p.worker_id
              ORDER BY p.created_at DESC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Single payroll record by ID with all actor names.
     */
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT p.*,
                    c.full_name AS computed_by_name,
                    r.full_name AS reviewed_by_name,
                    a.full_name AS approved_by_name,
                    CONCAT(w.first_name, \' \', COALESCE(w.last_name, \'\')) AS worker_name
               FROM payroll_data p
               JOIN users c ON c.user_id = p.computed_by
          LEFT JOIN users r ON r.user_id = p.reviewed_by
          LEFT JOIN users a ON a.user_id = p.approved_by
          LEFT JOIN workers w ON w.worker_id = p.worker_id
              WHERE p.payroll_id = :id
              LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Pending review (Finance Officer queue).
     */
    public function getPending(): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, CONCAT(w.first_name, ' ', COALESCE(w.last_name, '')) AS worker_name 
               FROM payroll_data p
          LEFT JOIN workers w ON w.worker_id = p.worker_id
              WHERE p.status = 'pending_review'
              ORDER BY p.harvest_date ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Compute and insert payroll from a production record.
     *
     * @param int   $productionId
     * @param float $ratePerBox
     * @param float $deductions
     * @param int   $computedBy    user_id of Payroll Personnel
     * @param array $period        ['start'=>'YYYY-MM-DD','end'=>'YYYY-MM-DD']
     */
    public function compute(
        int   $productionId,
        float $ratePerBox,
        float $deductions,
        int   $computedBy,
        array $period
    ): int {
        // Fetch production row
        $pStmt = $this->db->prepare(
            'SELECT worker_id, boxes_produced, harvest_date
               FROM production_data WHERE production_id = :id LIMIT 1'
        );
        $pStmt->execute([':id' => $productionId]);
        $prod = $pStmt->fetch();

        if (!$prod) {
            throw new RuntimeException("Production record #{$productionId} not found.");
        }

        $grossPay = $prod['boxes_produced'] * $ratePerBox;
        $netPay   = max(0.0, $grossPay - $deductions);

        $stmt = $this->db->prepare(
            'INSERT INTO payroll_data
                (production_id, worker_id, harvest_date, boxes_produced,
                 rate_per_box, gross_pay, deductions, net_pay,
                 period_start, period_end, computed_by)
             VALUES
                (:pid, :worker_id, :hdate, :boxes,
                 :rate, :gross, :ded, :net,
                 :ps, :pe, :cb)'
        );
        $stmt->execute([
            ':pid'       => $productionId,
            ':worker_id' => $prod['worker_id'],
            ':hdate'     => $prod['harvest_date'],
            ':boxes'  => $prod['boxes_produced'],
            ':rate'   => $ratePerBox,
            ':gross'  => $grossPay,
            ':ded'    => $deductions,
            ':net'    => $netPay,
            ':ps'     => $period['start'],
            ':pe'     => $period['end'],
            ':cb'     => $computedBy,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Finance Officer review.
     */
    public function review(int $payrollId, int $reviewedBy, string $remarks = ''): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE payroll_data
                SET status = 'reviewed',
                    reviewed_by = :rb,
                    reviewed_at = NOW(),
                    remarks = :remarks
              WHERE payroll_id = :id
                AND status = 'pending_review'"
        );
        return $stmt->execute([':rb' => $reviewedBy, ':remarks' => $remarks, ':id' => $payrollId]);
    }

    /**
     * Admin / Manager final approval.
     */
    public function approve(int $payrollId, int $approvedBy): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE payroll_data
                SET status = 'approved',
                    approved_by = :ab,
                    approved_at = NOW()
              WHERE payroll_id = :id
                AND status = 'reviewed'"
        );
        return $stmt->execute([':ab' => $approvedBy, ':id' => $payrollId]);
    }

    /**
     * Pending payroll count (dashboard widget).
     */
    public function getPendingCount(): int
    {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM payroll_data WHERE status = 'pending_review'"
        );
        return (int) $stmt->fetchColumn();
    }
}
