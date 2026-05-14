<?php
// ============================================================
//  Controller: Inventory
//  File      : controllers/InventoryController.php
//  Access    : bookkeeper, admin
// ============================================================

declare(strict_types=1);

if (!isset($_SESSION[SESS_USER_ID])) {
    header('Location: index.php?page=login');
    exit;
}
$allowedRoles = [ROLE_ADMIN, ROLE_BOOKKEEPER];
if (!in_array($_SESSION[SESS_ROLE], $allowedRoles, true)) {
    require_once VIEW_PATH . 'errors/403.php';
    exit;
}

require_once MODEL_PATH . 'Inventory.php';
require_once MODEL_PATH . 'TransactionLog.php';

$inventoryModel = new Inventory();
$logger         = new TransactionLog();
$role           = $_SESSION[SESS_ROLE];
$action         = $_GET['action'] ?? 'list';
$message = '';
$error   = '';

// ── ADD ITEM ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'store' && $role === ROLE_BOOKKEEPER) {
    Csrf::verify();
    try {
        $data = [
            'item_name'        => trim($_POST['item_name']),
            'category'         => trim($_POST['category']),
            'unit'             => trim($_POST['unit']),
            'quantity_on_hand' => (float) $_POST['quantity_on_hand'],
            'reorder_level'    => (float) $_POST['reorder_level'],
            'unit_cost'        => (float) $_POST['unit_cost'],
            'description'      => trim($_POST['description'] ?? ''),
            'created_by'       => (int) $_SESSION[SESS_USER_ID],
        ];
        $newId = $inventoryModel->create($data);
        $logger->log(
            (int) $_SESSION[SESS_USER_ID],
            'inventory_insert',
            "Inventory item '{$data['item_name']}' (id:{$newId}) added.",
            'inventory_data',
            $newId
        );
        $message = "Item '{$data['item_name']}' added to inventory.";
    } catch (\Throwable $e) {
        error_log('[InventoryCtrl] ' . $e->getMessage());
        $error = 'Failed to add inventory item.';
    }
}

// ── UPDATE ITEM ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update' && $role === ROLE_BOOKKEEPER) {
    Csrf::verify();
    try {
        $itemId = (int) $_POST['item_id'];
        $data = [
            'item_name'     => trim($_POST['item_name']),
            'category'      => trim($_POST['category']),
            'unit'          => trim($_POST['unit']),
            'reorder_level' => (float) $_POST['reorder_level'],
            'unit_cost'     => (float) $_POST['unit_cost'],
            'description'   => trim($_POST['description'] ?? ''),
        ];
        $inventoryModel->update($itemId, $data);
        $logger->log(
            (int) $_SESSION[SESS_USER_ID],
            'inventory_update',
            "Updated inventory item '{$data['item_name']}' (id:{$itemId}).",
            'inventory_data',
            $itemId
        );
        $message = "Item '{$data['item_name']}' updated successfully.";
    } catch (\Throwable $e) {
        error_log('[InventoryCtrl] ' . $e->getMessage());
        $error = 'Failed to update inventory item.';
    }
}

// ── RESTOCK ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'restock' && $role === ROLE_BOOKKEEPER) {
    Csrf::verify();
    $itemId = (int) ($_POST['item_id'] ?? 0);
    $qty    = (float) ($_POST['qty_add'] ?? 0);
    if ($itemId > 0 && $qty > 0) {
        $inventoryModel->restock($itemId, $qty);
        $logger->log(
            (int) $_SESSION[SESS_USER_ID],
            'inventory_update',
            "Restocked item id:{$itemId} by {$qty} units.",
            'inventory_data',
            $itemId
        );
        $message = "Restock successful.";
    }
}

$items = $inventoryModel->getAll();
require_once VIEW_PATH . 'inventory/index.php';
