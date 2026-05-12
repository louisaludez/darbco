<?php
// ============================================================
//  Model: Payroll
//  File : models/Payroll.php
//  Handles: Payroll computation with full itemized deductions,
//           box-spec pricing, contributions, approval workflow
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
                    CONCAT(w.first_name, \' \', COALESCE(w.last_name, \'\')) AS worker_name,
                    w.sub_code, w.area AS worker_area
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
                    CONCAT(w.first_name, \' \', COALESCE(w.last_name, \'\')) AS worker_name,
                    w.sub_code, w.area AS worker_area
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
     * Now supports full itemized deductions, box-spec pricing,
     * and contributions as per DARBCO Harvest Proceeds form.
     *
     * @param array $data  All payroll form data
     * @param int   $computedBy  user_id of Payroll Personnel
     */
    public function computeFull(array $data, int $computedBy): int
    {
        $this->db->beginTransaction();
        try {
            // Calculate totals from deduction items
            $totalMaterial     = 0;
            $totalLabor        = 0;
            $totalPersonal     = 0;
            $totalCashAdvance  = 0;
            $totalContribution = 0;
            $totalOther        = 0;

            if (!empty($data['deductions'])) {
                foreach ($data['deductions'] as $ded) {
                    $amt = (float) ($ded['amount'] ?? 0);
                    switch ($ded['category'] ?? 'other') {
                        case 'material':     $totalMaterial     += $amt; break;
                        case 'labor':        $totalLabor        += $amt; break;
                        case 'personal':     $totalPersonal     += $amt; break;
                        case 'cash_advance': $totalCashAdvance  += $amt; break;
                        case 'contribution': $totalContribution += $amt; break;
                        default:             $totalOther        += $amt; break;
                    }
                }
            }

            // Calculate gross from box details or flat rate
            $grossPay = 0;
            if (!empty($data['box_details'])) {
                foreach ($data['box_details'] as $bd) {
                    $grossPay += (float) ($bd['amount'] ?? 0);
                }
            } else {
                $grossPay = (int) ($data['boxes_produced'] ?? 0) * (float) ($data['rate_per_box'] ?? DEFAULT_RATE_PER_BOX);
            }

            $totalDeductions = $totalMaterial + $totalLabor + $totalPersonal + $totalCashAdvance + $totalContribution + $totalOther;
            $guaranteedIncome = (float) ($data['guaranteed_income'] ?? 0);
            $netPay = max(0.0, $grossPay - $totalDeductions + $guaranteedIncome);

            // Stems / boxes ratio
            $stemsCut = (int) ($data['stems_cut'] ?? 0);
            $totalBoxes = (int) ($data['boxes_produced'] ?? 0);
            $bsRatio = ($stemsCut > 0 && $totalBoxes > 0) ? round($totalBoxes / $stemsCut, 3) : null;

            // Insert main payroll record
            $stmt = $this->db->prepare(
                'INSERT INTO payroll_data
                    (production_id, worker_id, area, week_number, cycle_code,
                     harvest_date, boxes_produced, rate_per_box, forex_rate,
                     gross_pay, deductions,
                     total_material_cost, total_labor_cost, total_personal,
                     cash_advance, guaranteed_income, other_deductions, total_contributions,
                     bs_ratio, stems_cut_payroll, net_pay,
                     period_start, period_end, computed_by)
                 VALUES
                    (:pid, :worker_id, :area, :week, :cycle,
                     :hdate, :boxes, :rate, :forex,
                     :gross, :ded_total,
                     :mat, :labor, :personal,
                     :cash, :guaranteed, :other, :contrib,
                     :bsratio, :stems, :net,
                     :ps, :pe, :cb)'
            );
            $stmt->execute([
                ':pid'        => !empty($data['production_id']) ? (int) $data['production_id'] : null,
                ':worker_id'  => (int) $data['worker_id'],
                ':area'       => $data['area'] ?? null,
                ':week'       => $data['week_number'] ?? null,
                ':cycle'      => $data['cycle_code'] ?? null,
                ':hdate'      => $data['harvest_date'],
                ':boxes'      => $totalBoxes,
                ':rate'       => (float) ($data['rate_per_box'] ?? DEFAULT_RATE_PER_BOX),
                ':forex'      => (float) ($data['forex_rate'] ?? 1.0),
                ':gross'      => $grossPay,
                ':ded_total'  => $totalDeductions,
                ':mat'        => $totalMaterial,
                ':labor'      => $totalLabor,
                ':personal'   => $totalPersonal,
                ':cash'       => $totalCashAdvance,
                ':guaranteed' => $guaranteedIncome,
                ':other'      => $totalOther,
                ':contrib'    => $totalContribution,
                ':bsratio'    => $bsRatio,
                ':stems'      => $stemsCut,
                ':net'        => $netPay,
                ':ps'         => $data['period_start'],
                ':pe'         => $data['period_end'],
                ':cb'         => $computedBy,
            ]);
            $payrollId = (int) $this->db->lastInsertId();

            // Insert box spec details
            if (!empty($data['box_details'])) {
                $bdStmt = $this->db->prepare(
                    'INSERT INTO payroll_box_details
                        (payroll_id, box_spec, quantity, price_per_box, forex_rate, amount)
                     VALUES (:pid, :spec, :qty, :price, :forex, :amt)'
                );
                foreach ($data['box_details'] as $bd) {
                    if (!empty($bd['box_spec']) && ((int)($bd['quantity'] ?? 0) > 0)) {
                        $bdStmt->execute([
                            ':pid'   => $payrollId,
                            ':spec'  => $bd['box_spec'],
                            ':qty'   => (int) $bd['quantity'],
                            ':price' => (float) ($bd['price_per_box'] ?? 0),
                            ':forex' => (float) ($bd['forex_rate'] ?? 1.0),
                            ':amt'   => (float) ($bd['amount'] ?? 0),
                        ]);
                    }
                }
            }

            // Insert itemized deductions
            if (!empty($data['deductions'])) {
                $dedStmt = $this->db->prepare(
                    'INSERT INTO payroll_deductions
                        (payroll_id, category, description, quantity, unit_cost, amount)
                     VALUES (:pid, :cat, :desc, :qty, :ucost, :amt)'
                );
                foreach ($data['deductions'] as $ded) {
                    if (!empty($ded['description']) && (float)($ded['amount'] ?? 0) > 0) {
                        $dedStmt->execute([
                            ':pid'   => $payrollId,
                            ':cat'   => $ded['category'] ?? 'other',
                            ':desc'  => $ded['description'],
                            ':qty'   => !empty($ded['quantity']) ? (float) $ded['quantity'] : null,
                            ':ucost' => !empty($ded['unit_cost']) ? (float) $ded['unit_cost'] : null,
                            ':amt'   => (float) $ded['amount'],
                        ]);
                    }
                }
            }

            // Insert contributions
            if (!empty($data['contributions'])) {
                $cStmt = $this->db->prepare(
                    'INSERT INTO payroll_contributions
                        (payroll_id, contribution_type, previous_amount, current_amount, running_total)
                     VALUES (:pid, :type, :prev, :curr, :total)'
                );
                foreach ($data['contributions'] as $c) {
                    if (!empty($c['contribution_type'])) {
                        $curr = (float) ($c['current_amount'] ?? 0);
                        $prev = (float) ($c['previous_amount'] ?? 0);
                        $cStmt->execute([
                            ':pid'   => $payrollId,
                            ':type'  => $c['contribution_type'],
                            ':prev'  => $prev,
                            ':curr'  => $curr,
                            ':total' => $prev + $curr,
                        ]);
                    }
                }
            }

            $this->db->commit();
            return $payrollId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Legacy simple compute — kept for backward compatibility.
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

    /**
     * Get box spec details for a payroll record.
     */
    public function getBoxDetails(int $payrollId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM payroll_box_details WHERE payroll_id = :pid ORDER BY detail_id ASC'
        );
        $stmt->execute([':pid' => $payrollId]);
        return $stmt->fetchAll();
    }

    /**
     * Get itemized deductions for a payroll record.
     */
    public function getDeductions(int $payrollId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM payroll_deductions WHERE payroll_id = :pid ORDER BY category ASC, deduction_id ASC'
        );
        $stmt->execute([':pid' => $payrollId]);
        return $stmt->fetchAll();
    }

    /**
     * Get contributions for a payroll record.
     */
    public function getContributions(int $payrollId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM payroll_contributions WHERE payroll_id = :pid ORDER BY contribution_id ASC'
        );
        $stmt->execute([':pid' => $payrollId]);
        return $stmt->fetchAll();
    }
}
