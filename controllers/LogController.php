<?php
// ============================================================
//  Controller: Transaction Logs
//  File      : controllers/LogController.php
//  Access    : admin only
// ============================================================

declare(strict_types=1);

if (!isset($_SESSION[SESS_USER_ID]) || $_SESSION[SESS_ROLE] !== ROLE_ADMIN) {
    require_once VIEW_PATH . 'errors/403.php';
    exit;
}

require_once MODEL_PATH . 'TransactionLog.php';

$logger  = new TransactionLog();
$filter  = $_GET['filter'] ?? '';
$records = $filter
    ? $logger->getByAction($filter)
    : $logger->getRecent(200);

require_once VIEW_PATH . 'logs/index.php';
