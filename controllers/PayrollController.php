<?php
// ============================================================
//  Controller: Payroll
//  File      : controllers/PayrollController.php
//  Access    : payroll_personnel (compute), finance_officer (review),
//              admin (approve)
// ============================================================

declare(strict_types=1);

if (!isset($_SESSION[SESS_USER_ID])) {
    header('Location: index.php?page=login');
    exit;
}
$allowedRoles = [ROLE_ADMIN, ROLE_PAYROLL, ROLE_FINANCE];
if (!in_array($_SESSION[SESS_ROLE], $allowedRoles, true)) {
    require_once VIEW_PATH . 'errors/403.php';
    exit;
}

require_once MODEL_PATH . 'Payroll.php';
require_once MODEL_PATH . 'Production.php';
require_once MODEL_PATH . 'Worker.php';
require_once MODEL_PATH . 'TransactionLog.php';

$payrollModel    = new Payroll();
$productionModel = new Production();
$workerModel     = new Worker();
$logger          = new TransactionLog();
$action          = $_GET['action'] ?? 'list';
$message = '';
$error   = '';
$role    = $_SESSION[SESS_ROLE];

// ── COMPUTE PAYROLL (Full Harvest Proceeds) ──────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'compute'
    && $role === ROLE_PAYROLL) {
    Csrf::verify();
    try {
        // Collect box spec details
        $boxDetails = [];
        if (!empty($_POST['bd_spec'])) {
            foreach ($_POST['bd_spec'] as $i => $spec) {
                $qty   = (int)   ($_POST['bd_qty'][$i]   ?? 0);
                $price = (float) ($_POST['bd_price'][$i] ?? 0);
                $forex = (float) ($_POST['bd_forex'][$i] ?? 1.0);
                $amt   = $qty * $price * $forex;
                $boxDetails[] = [
                    'box_spec'     => trim($spec),
                    'quantity'     => $qty,
                    'price_per_box'=> $price,
                    'forex_rate'   => $forex,
                    'amount'       => $amt,
                ];
            }
        }

        // Collect itemized deductions
        $deductions = [];
        if (!empty($_POST['ded_desc'])) {
            foreach ($_POST['ded_desc'] as $i => $desc) {
                $deductions[] = [
                    'category'    => $_POST['ded_cat'][$i]   ?? 'other',
                    'description' => trim($desc),
                    'quantity'    => (float) ($_POST['ded_qty'][$i]   ?? 0),
                    'unit_cost'   => (float) ($_POST['ded_ucost'][$i] ?? 0),
                    'amount'      => (float) ($_POST['ded_amt'][$i]   ?? 0),
                ];
            }
        }

        // Collect contributions
        $contributions = [];
        if (!empty($_POST['contrib_type'])) {
            foreach ($_POST['contrib_type'] as $i => $type) {
                $contributions[] = [
                    'contribution_type' => trim($type),
                    'previous_amount'   => (float) ($_POST['contrib_prev'][$i] ?? 0),
                    'current_amount'    => (float) ($_POST['contrib_curr'][$i] ?? 0),
                ];
            }
        }

        $payrollData = [
            'production_id'    => !empty($_POST['production_id']) ? (int) $_POST['production_id'] : null,
            'worker_id'        => (int) $_POST['worker_id'],
            'area'             => trim($_POST['area'] ?? ''),
            'week_number'      => trim($_POST['week_number'] ?? ''),
            'cycle_code'       => trim($_POST['cycle_code'] ?? ''),
            'harvest_date'     => $_POST['harvest_date'],
            'boxes_produced'   => (int) ($_POST['boxes_produced'] ?? 0),
            'class_a_big_hands'   => (int) ($_POST['class_a_big_hands'] ?? 0),
            'class_a_small_hands' => (int) ($_POST['class_a_small_hands'] ?? 0),
            'class_a_cps'         => (int) ($_POST['class_a_cps'] ?? 0),
            'class_b'             => (int) ($_POST['class_b'] ?? 0),
            'stems_cut'        => (int) ($_POST['stems_cut'] ?? 0),
            'rate_per_box'     => (float) ($_POST['rate_per_box'] ?? DEFAULT_RATE_PER_BOX),
            'forex_rate'       => (float) ($_POST['forex_rate'] ?? 1.0),
            'guaranteed_income'=> (float) ($_POST['guaranteed_income'] ?? 0),
            'period_start'     => $_POST['period_start'],
            'period_end'       => $_POST['period_end'],
            'box_details'      => $boxDetails,
            'deductions'       => $deductions,
            'contributions'    => $contributions,
        ];

        $newId = $payrollModel->computeFull($payrollData, (int) $_SESSION[SESS_USER_ID]);
        $logger->log(
            (int) $_SESSION[SESS_USER_ID],
            'payroll_compute',
            "Payroll #{$newId} computed (full proceeds).",
            'payroll_data',
            $newId
        );
        $message = "Payroll record #{$newId} computed successfully.";
    } catch (\Throwable $e) {
        error_log('[PayrollCtrl] ' . $e->getMessage());
        $error = $e->getMessage();
    }
}

// ── REVIEW (Finance Officer) ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'review'
    && $role === ROLE_FINANCE) {
    Csrf::verify();
    $payrollId = (int) $_POST['payroll_id'];
    $remarks   = trim($_POST['remarks'] ?? '');
    $payrollModel->review($payrollId, (int) $_SESSION[SESS_USER_ID], $remarks);
    $logger->log(
        (int) $_SESSION[SESS_USER_ID],
        'payroll_review',
        "Payroll #{$payrollId} reviewed.",
        'payroll_data',
        $payrollId
    );
    $message = "Payroll #{$payrollId} marked as Reviewed.";
}

// ── APPROVE (Admin) ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'approve'
    && $role === ROLE_ADMIN) {
    Csrf::verify();
    $payrollId = (int) $_POST['payroll_id'];
    $payrollModel->approve($payrollId, (int) $_SESSION[SESS_USER_ID]);
    $logger->log(
        (int) $_SESSION[SESS_USER_ID],
        'payroll_approve',
        "Payroll #{$payrollId} approved by admin.",
        'payroll_data',
        $payrollId
    );
    $message = "Payroll #{$payrollId} Approved.";
}

// ── PRINT PAYROLL SLIP ───────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'print') {
    $payrollId = (int) ($_GET['id'] ?? 0);
    $slipData = $payrollModel->findById($payrollId);
    if (!$slipData || $slipData['status'] !== 'approved') {
        die('Invalid or unapproved payroll slip.');
    }
    $slipBoxDetails    = $payrollModel->getBoxDetails($payrollId);
    $slipDeductions    = $payrollModel->getDeductions($payrollId);
    $slipContributions = $payrollModel->getContributions($payrollId);
    require_once VIEW_PATH . 'payroll/slip.php';
    exit;
}

$records        = $payrollModel->getAll();
$productionList = $productionModel->getAll();
$activeWorkers  = $workerModel->getActive();

require_once VIEW_PATH . 'payroll/index.php';
