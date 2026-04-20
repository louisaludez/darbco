<?php
// ============================================================
//  Controller: Production
//  File      : controllers/ProductionController.php
//  Handles   : CRUD for production records + materials
//  Access    : production_clerk, admin
// ============================================================

declare(strict_types=1);

// Auth + RBAC guard
if (!isset($_SESSION[SESS_USER_ID])) {
    header('Location: index.php?page=login');
    exit;
}
$allowedRoles = [ROLE_ADMIN, ROLE_PRODUCTION];
if (!in_array($_SESSION[SESS_ROLE], $allowedRoles, true)) {
    require_once VIEW_PATH . 'errors/403.php';
    exit;
}

require_once MODEL_PATH . 'Production.php';
require_once MODEL_PATH . 'Inventory.php';
require_once MODEL_PATH . 'Worker.php';
require_once MODEL_PATH . 'TransactionLog.php';

$productionModel = new Production();
$inventoryModel  = new Inventory();
$workerModel     = new Worker();
$logger          = new TransactionLog();

$action  = $_GET['action']  ?? 'list';
$message = '';
$error   = '';

// ── CREATE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'store') {
    Csrf::verify();
    try {
        // Collect materials array from form (item_id[] qty_used[])
        $materials = [];
        if (!empty($_POST['item_id'])) {
            foreach ($_POST['item_id'] as $i => $itemId) {
                $qty = (float) ($_POST['quantity_used'][$i] ?? 0);
                if ($itemId && $qty > 0) {
                    $materials[] = ['item_id' => (int) $itemId, 'quantity_used' => $qty];
                }
            }
        }

        // Collect box breakdown from form (box_class[] box_spec[] tally[] adj[] should[])
        $boxBreakdown = [];
        if (!empty($_POST['bk_spec'])) {
            foreach ($_POST['bk_spec'] as $i => $spec) {
                $boxBreakdown[] = [
                    'box_class' => $_POST['bk_class'][$i]  ?? '',
                    'box_spec'  => trim($spec),
                    'tally'     => (int) ($_POST['bk_tally'][$i]  ?? 0),
                    'adj'       => (int) ($_POST['bk_adj'][$i]    ?? 0),
                    'should'    => (int) ($_POST['bk_should'][$i] ?? 0),
                ];
            }
        }

        $data = [
            'harvest_date'   => $_POST['harvest_date'],
            'worker_id'      => (int) $_POST['worker_id'],
            'boxes_produced' => (int) $_POST['boxes_produced'],
            'stems_cut'      => (int) ($_POST['stems_cut'] ?? 0),
            'group_number'   => !empty($_POST['group_number']) ? (int) $_POST['group_number'] : null,
            'field_location' => trim($_POST['field_location'] ?? ''),
            'notes'          => trim($_POST['notes'] ?? ''),
            'recorded_by'    => (int) $_SESSION[SESS_USER_ID],
        ];

        $newId = $productionModel->create($data, $materials, $boxBreakdown);
        $logger->log(
            (int) $_SESSION[SESS_USER_ID],
            'production_insert',
            "Production record #{$newId} created for worker ID {$data['worker_id']}.",
            'production_data',
            $newId
        );
        $message = "Production record #{$newId} saved successfully.";
    } catch (\Throwable $e) {
        error_log('[ProductionCtrl] ' . $e->getMessage());
        $error = 'Failed to save production record. Please try again.';
    }
}

// ── UPDATE (Edit Core Fields) ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update') {
    Csrf::verify();
    try {
        $prodId = (int) $_POST['production_id'];
        $data = [
            'harvest_date'   => $_POST['harvest_date'],
            'worker_id'      => (int) $_POST['worker_id'],
            'boxes_produced' => (int) $_POST['boxes_produced'],
            'stems_cut'      => (int) ($_POST['stems_cut'] ?? 0),
            'group_number'   => !empty($_POST['group_number']) ? (int) $_POST['group_number'] : null,
            'field_location' => trim($_POST['field_location'] ?? ''),
            'notes'          => trim($_POST['notes'] ?? '')
        ];
        $productionModel->update($prodId, $data);

        $logger->log(
            (int) $_SESSION[SESS_USER_ID],
            'edit_production',
            "Updated production record #{$prodId}.",
            'production',
            $prodId
        );
        $message = 'Production record updated successfully.';
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// ── DELETE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete' && $role === ROLE_ADMIN) {
    Csrf::verify();
    try {
        $prodId = (int) $_POST['production_id'];
        $productionModel->delete($prodId);

        $logger->log(
            (int) $_SESSION[SESS_USER_ID],
            'delete_production',
            "Deleted production record #{$prodId}. Associated materials restored to inventory.",
            'production',
            $prodId
        );
        $message = 'Production record deleted successfully. Inventory restored.';
    } catch (PDOException $e) {
        $error = 'Error deleting record: ' . $e->getMessage();
    }
}

// ── DATA FOR VIEW ────────────────────────────────────────────
$records       = $productionModel->getAll();
$inventoryList = $inventoryModel->getAll();  // for material dropdown
$activeWorkers = $workerModel->getActive();

require_once VIEW_PATH . 'production/index.php';
