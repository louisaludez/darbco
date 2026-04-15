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
require_once MODEL_PATH . 'TransactionLog.php';

$payrollModel    = new Payroll();
$productionModel = new Production();
$logger          = new TransactionLog();
$action          = $_GET['action'] ?? 'list';
$message = '';
$error   = '';
$role    = $_SESSION[SESS_ROLE];

// ── COMPUTE PAYROLL (Payroll Personnel) ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'compute'
    && in_array($role, [ROLE_PAYROLL, ROLE_ADMIN], true)) {
    Csrf::verify();
    try {
        $newId = $payrollModel->compute(
            (int)   $_POST['production_id'],
            (float) ($_POST['rate_per_box'] ?? DEFAULT_RATE_PER_BOX),
            (float) ($_POST['deductions']   ?? 0),
            (int)   $_SESSION[SESS_USER_ID],
            ['start' => $_POST['period_start'], 'end' => $_POST['period_end']]
        );
        $logger->log(
            (int) $_SESSION[SESS_USER_ID],
            'payroll_compute',
            "Payroll #{$newId} computed.",
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
    && in_array($role, [ROLE_FINANCE, ROLE_ADMIN], true)) {
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
    require_once VIEW_PATH . 'payroll/slip.php';
    exit;
}

$records        = $payrollModel->getAll();
$productionList = $productionModel->getAll();  // for compute form dropdown

require_once VIEW_PATH . 'payroll/index.php';
