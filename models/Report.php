<?php
// ============================================================
//  Model: Report
//  File : models/Report.php
//  Handles: Aggregated queries for all report types
// ============================================================

declare(strict_types=1);

class Report
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ─────────────────────────────────────────────────────────
    //  PRODUCTION REPORTS
    // ─────────────────────────────────────────────────────────

    /**
     * Daily production totals for a date range (chart + table).
     * Returns rows: harvest_date, total_boxes, record_count
     */
    public function getDailyProduction(string $dateFrom, string $dateTo): array
    {
        $stmt = $this->db->prepare(
            'SELECT harvest_date,
                    SUM(boxes_produced) AS total_boxes,
                    COUNT(*)            AS record_count
               FROM production_data
              WHERE harvest_date BETWEEN :from AND :to
              GROUP BY harvest_date
              ORDER BY harvest_date ASC'
        );
        $stmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
        return $stmt->fetchAll();
    }

    /**
     * Top workers by total boxes in date range.
     */
    public function getTopWorkers(string $dateFrom, string $dateTo, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT CONCAT(w.first_name, \' \', COALESCE(w.last_name, \'\')) AS worker_name,
                    SUM(p.boxes_produced) AS total_boxes,
                    COUNT(*)            AS harvest_days
               FROM production_data p
               JOIN workers w ON w.worker_id = p.worker_id
              WHERE p.harvest_date BETWEEN :from AND :to
              GROUP BY p.worker_id, w.first_name, w.last_name
              ORDER BY total_boxes DESC
              LIMIT :lim'
        );
        $stmt->bindValue(':from', $dateFrom);
        $stmt->bindValue(':to',   $dateTo);
        $stmt->bindValue(':lim',  $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Monthly production totals for the current year (chart).
     */
    public function getMonthlyProductionThisYear(): array
    {
        $year = date('Y');
        $stmt = $this->db->prepare(
            "SELECT MONTH(harvest_date)    AS month_num,
                    MONTHNAME(harvest_date) AS month_name,
                    SUM(boxes_produced)     AS total_boxes
               FROM production_data
              WHERE YEAR(harvest_date) = :year
              GROUP BY MONTH(harvest_date), MONTHNAME(harvest_date)
              ORDER BY MONTH(harvest_date) ASC"
        );
        $stmt->execute([':year' => $year]);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────────────────
    //  PAYROLL REPORTS
    // ─────────────────────────────────────────────────────────

    /**
     * Payroll summary for a date range.
     * Returns total gross, total deductions, total net, count by status.
     */
    public function getPayrollSummary(string $dateFrom, string $dateTo): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                COUNT(*)              AS total_records,
                SUM(gross_pay)        AS total_gross,
                SUM(deductions)       AS total_deductions,
                SUM(net_pay)          AS total_net,
                SUM(CASE WHEN status = "pending_review" THEN 1 ELSE 0 END) AS count_pending,
                SUM(CASE WHEN status = "reviewed"       THEN 1 ELSE 0 END) AS count_reviewed,
                SUM(CASE WHEN status = "approved"       THEN 1 ELSE 0 END) AS count_approved
              FROM payroll_data
             WHERE harvest_date BETWEEN :from AND :to'
        );
        $stmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Payroll records detail for a date range.
     */
    public function getPayrollDetail(string $dateFrom, string $dateTo): array
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
              WHERE p.harvest_date BETWEEN :from AND :to
              ORDER BY p.harvest_date ASC'
        );
        $stmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────────────────
    //  INVENTORY REPORTS
    // ─────────────────────────────────────────────────────────

    /**
     * Inventory usage summary: how much of each item was used
     * in production over a date range.
     */
    public function getInventoryUsage(string $dateFrom, string $dateTo): array
    {
        $stmt = $this->db->prepare(
            'SELECT i.item_id, i.item_name, i.category, i.unit,
                    SUM(pm.quantity_used) AS total_used,
                    i.quantity_on_hand    AS current_stock,
                    i.reorder_level
               FROM production_materials pm
               JOIN inventory_data i  ON i.item_id  = pm.item_id
               JOIN production_data p ON p.production_id = pm.production_id
              WHERE p.harvest_date BETWEEN :from AND :to
              GROUP BY i.item_id, i.item_name, i.category, i.unit,
                       i.quantity_on_hand, i.reorder_level
              ORDER BY total_used DESC'
        );
        $stmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────────────────
    //  DASHBOARD CHART DATA
    // ─────────────────────────────────────────────────────────

    /**
     * Last N days production — for dashboard sparkline/chart.
     */
    public function getLastNDaysProduction(int $days = 30): array
    {
        $stmt = $this->db->prepare(
            'SELECT harvest_date,
                    COALESCE(SUM(boxes_produced), 0) AS total_boxes
               FROM production_data
              WHERE harvest_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
              GROUP BY harvest_date
              ORDER BY harvest_date ASC'
        );
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Payroll status distribution for pie chart.
     */
    public function getPayrollStatusDistribution(): array
    {
        $stmt = $this->db->query(
            'SELECT status, COUNT(*) AS count FROM payroll_data GROUP BY status'
        );
        return $stmt->fetchAll();
    }

    // ─────────────────────────────────────────────────────────
    //  PHYSICAL FORM REPORTS
    // ─────────────────────────────────────────────────────────

    /**
     * Daily Boxes Per Group report.
     * Aggregates tally/adj/should counts by date, group, class, and spec.
     */
    public function getDailyBoxesPerGroup(string $dateFrom, string $dateTo): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.harvest_date,
                    p.group_number,
                    b.box_class,
                    b.box_spec,
                    SUM(b.tally_count)     AS total_tally,
                    SUM(b.adjusted_count)  AS total_adj,
                    SUM(b.should_be_count) AS total_should,
                    SUM(b.tally_count + b.adjusted_count) AS total_boxes_produced
               FROM production_box_breakdown b
               JOIN production_data p ON p.production_id = b.production_id
              WHERE p.harvest_date BETWEEN :from AND :to
              GROUP BY p.harvest_date, p.group_number, b.box_class, b.box_spec
              ORDER BY p.harvest_date ASC, b.box_class ASC, p.group_number ASC, b.box_spec ASC'
        );
        $stmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
        return $stmt->fetchAll();
    }

    /**
     * Daily Production Per Beneficiary report.
     * Returns per-worker per-day totals with stems cut and box breakdown.
     */
    public function getDailyProductionPerBeneficiary(string $dateFrom, string $dateTo): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.harvest_date,
                    p.production_id,
                    p.stems_cut,
                    p.group_number,
                    COALESCE(w.sub_code, \'\u2014\') AS sub_code,
                    CONCAT(w.first_name, \' \', COALESCE(w.last_name, \'\')) AS worker_name,
                    b.box_class,
                    b.box_spec,
                    b.tally_count,
                    b.adjusted_count,
                    b.should_be_count,
                    (b.tally_count + b.adjusted_count) AS boxes_for_spec
               FROM production_data p
               LEFT JOIN workers w ON w.worker_id = p.worker_id
               LEFT JOIN production_box_breakdown b ON b.production_id = p.production_id
              WHERE p.harvest_date BETWEEN :from AND :to
              ORDER BY p.harvest_date ASC, w.sub_code ASC, b.box_class ASC, b.box_spec ASC'
        );
        $stmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
        return $stmt->fetchAll();
    }

    /**
     * Summary totals for Efficiency Performance report.
     * Groups by date: total stems cut, total boxes, workers, groups.
     */
    public function getEfficiencySummary(string $dateFrom, string $dateTo): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.harvest_date,
                    COUNT(DISTINCT p.production_id)    AS crew_size,
                    SUM(p.stems_cut)                   AS total_stems,
                    SUM(p.boxes_produced)              AS total_boxes,
                    GROUP_CONCAT(DISTINCT p.group_number ORDER BY p.group_number) AS groups_active,
                    SUM(CASE WHEN b.box_class = \'A\' THEN (b.tally_count + b.adjusted_count) ELSE 0 END) AS class_a_boxes,
                    SUM(CASE WHEN b.box_class = \'B\' THEN (b.tally_count + b.adjusted_count) ELSE 0 END) AS class_b_boxes
               FROM production_data p
               LEFT JOIN production_box_breakdown b ON b.production_id = p.production_id
              WHERE p.harvest_date BETWEEN :from AND :to
              GROUP BY p.harvest_date
              ORDER BY p.harvest_date ASC'
        );
        $stmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
        return $stmt->fetchAll();
    }
}
