<?php
// ============================================================
//  Controller: Dashboard
//  File      : controllers/DashboardController.php
//  Handles   : Role-based dashboard data aggregation & routing
// ============================================================

declare(strict_types=1);

// Enforce authentication
if (!isset($_SESSION[SESS_USER_ID])) {
    header('Location: index.php?page=login');
    exit;
}

require_once MODEL_PATH . 'Production.php';
require_once MODEL_PATH . 'Inventory.php';
require_once MODEL_PATH . 'Payroll.php';
require_once MODEL_PATH . 'TransactionLog.php';

$role = $_SESSION[SESS_ROLE];

// Build summary widgets available to all roles
$production  = new Production();
$inventory   = new Inventory();
$payroll     = new Payroll();
$logger      = new TransactionLog();

$widgets = [
    'boxes_today'     => $production->getTotalBoxesToday(),
    'monthly_records' => $production->getMonthlyCount(),
    'low_stock_count' => $inventory->getLowStockCount(),
    'pending_payroll' => $payroll->getPendingCount(),
    'recent_logs'     => $logger->getRecent(10),
    'low_stock_items' => $inventory->getLowStock(),
];

// Route to role-specific dashboard view
$viewMap = [
    ROLE_ADMIN      => VIEW_PATH . 'dashboard/admin.php',
    ROLE_PRODUCTION => VIEW_PATH . 'dashboard/production.php',
    ROLE_PAYROLL    => VIEW_PATH . 'dashboard/payroll.php',
    ROLE_FINANCE    => VIEW_PATH . 'dashboard/finance.php',
    ROLE_BOOKKEEPER => VIEW_PATH . 'dashboard/bookkeeper.php',
];

$view = $viewMap[$role] ?? VIEW_PATH . 'errors/403.php';
require_once $view;
