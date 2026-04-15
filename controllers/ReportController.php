<?php
// ============================================================
//  Controller: Report
//  File      : controllers/ReportController.php
//  Access    : admin, finance_officer, payroll_personnel
// ============================================================

declare(strict_types=1);

if (!isset($_SESSION[SESS_USER_ID])) {
    header('Location: index.php?page=login');
    exit;
}

$allowedRoles = [ROLE_ADMIN, ROLE_FINANCE, ROLE_PAYROLL, ROLE_PRODUCTION];
if (!in_array($_SESSION[SESS_ROLE], $allowedRoles, true)) {
    require_once VIEW_PATH . 'errors/403.php';
    exit;
}

require_once MODEL_PATH . 'Report.php';

$reportModel = new Report();
$reportType  = $_GET['type']   ?? 'production';
$period      = $_GET['period'] ?? 'this_month';

// ── Resolve date range from period ───────────────────────────
$today     = date('Y-m-d');
$dateFrom  = $_GET['date_from'] ?? '';
$dateTo    = $_GET['date_to']   ?? '';

if ($period !== 'custom' || !$dateFrom || !$dateTo) {
    switch ($period) {
        case 'last_month':
            $dateFrom = date('Y-m-01', strtotime('first day of last month'));
            $dateTo   = date('Y-m-t',  strtotime('last day of last month'));
            break;
        case 'this_quarter':
            $q        = ceil((int)date('n') / 3);
            $dateFrom = date('Y-' . str_pad((($q - 1) * 3 + 1), 2, '0', STR_PAD_LEFT) . '-01');
            $dateTo   = $today;
            break;
        case 'this_year':
            $dateFrom = date('Y-01-01');
            $dateTo   = $today;
            break;
        case 'this_month':
        default:
            $dateFrom = date('Y-m-01');
            $dateTo   = $today;
            break;
    }
}

// ── Fetch data based on report type ──────────────────────────
$reportData      = [];
$payrollSummary  = [];
$topWorkers      = [];
$inventoryUsage  = [];
$monthlyChart    = [];
$chartLabels     = [];
$chartValues     = [];

switch ($reportType) {
    case 'payroll':
        $reportData     = $reportModel->getPayrollDetail($dateFrom, $dateTo);
        $payrollSummary = $reportModel->getPayrollSummary($dateFrom, $dateTo);
        break;

    case 'inventory':
        $inventoryUsage = $reportModel->getInventoryUsage($dateFrom, $dateTo);
        break;

    case 'production':
    default:
        $reportType = 'production';
        $reportData  = $reportModel->getDailyProduction($dateFrom, $dateTo);
        $topWorkers  = $reportModel->getTopWorkers($dateFrom, $dateTo, 10);
        $monthlyChart = $reportModel->getMonthlyProductionThisYear();

        // Build chart arrays for JSON export to JS
        foreach ($reportData as $row) {
            $chartLabels[] = date('M j', strtotime($row['harvest_date']));
            $chartValues[] = (int) $row['total_boxes'];
        }
        break;
}

// ── EXPORT TO CSV ──────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    ob_end_clean(); // Clear any previous output buffers
    $filename = "DARBCO_{$reportType}_Report_{$dateFrom}_to_{$dateTo}.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
    
    if ($reportType === 'production') {
        fputcsv($output, ['Harvest Date', 'Total Boxes', 'Recorded By / Notes']);
        foreach ($reportData as $row) {
            fputcsv($output, [$row['harvest_date'], $row['total_boxes'], $row['record_count'] . ' records']);
        }
    } elseif ($reportType === 'payroll') {
        fputcsv($output, ['Payroll ID', 'Date', 'Worker Name', 'Boxes', 'Gross Pay', 'Deductions', 'Net Pay', 'Status']);
        foreach ($reportData as $row) {
            fputcsv($output, [
                $row['payroll_id'],
                $row['harvest_date'],
                $row['worker_name'],
                $row['boxes_produced'],
                $row['gross_pay'],
                $row['deductions'],
                $row['net_pay'],
                $row['status']
            ]);
        }
    } elseif ($reportType === 'inventory') {
        fputcsv($output, ['Item Name', 'Category', 'Quantity Used', 'Current Stock', 'Unit']);
        foreach ($inventoryUsage as $row) {
            fputcsv($output, [
                $row['item_name'],
                $row['category'],
                $row['total_used'],
                $row['current_stock'],
                $row['unit']
            ]);
        }
    }
    
    fclose($output);
    exit;
}

require_once VIEW_PATH . 'reports/index.php';
