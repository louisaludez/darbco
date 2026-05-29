<?php
// ============================================================
//  Controller: Production
//  File      : controllers/ProductionController.php
//  Handles   : CRUD for production records + materials + harvest sheet
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
require_once MODEL_PATH . 'HarvestParameter.php';
require_once MODEL_PATH . 'DailyReport.php';

$productionModel = new Production();
$inventoryModel  = new Inventory();
$workerModel     = new Worker();
$logger          = new TransactionLog();
$hpModel         = new HarvestParameter();
$drModel         = new DailyReport();

$role    = $_SESSION[SESS_ROLE];
$action  = $_GET['action']  ?? 'list';
$tab     = $_GET['tab'] ?? 'daily_log'; // tabs: daily_log, harvest_parameters, daily_reports
$message = '';
$error   = '';

// ── CREATE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'store' && $role === ROLE_PRODUCTION) {
    Csrf::verify();
    try {
        // Collect defects array from form
        $defects = [];
        if (!empty($_POST['defect_name'])) {
            foreach ($_POST['defect_name'] as $i => $name) {
                if (trim($name)) {
                    $defects[] = [
                        'name'  => trim($name),
                        'w8'    => $_POST['defect_w8'][$i] ?? null,
                        'w9'    => $_POST['defect_w9'][$i] ?? null,
                        'w10'   => $_POST['defect_w10'][$i] ?? null,
                        'w11'   => $_POST['defect_w11'][$i] ?? null,
                        'total' => $_POST['defect_total'][$i] ?? null,
                    ];
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

        // Collect per-row stem details (rows 11, 12, 13, 14)
        $stemDetails = [];
        foreach ([11, 12, 13, 14] as $rowNum) {
            $count = (int) ($_POST["stem_row_{$rowNum}"] ?? 0);
            if ($count > 0) {
                $stemDetails[] = ['row_number' => $rowNum, 'stem_count' => $count];
            }
        }

        $data = [
            'harvest_date'   => $_POST['harvest_date'],
            'worker_id'      => (int) $_POST['worker_id'],
            'boxes_produced' => (int) $_POST['boxes_produced'],
            'stems_cut'      => (int) ($_POST['stems_cut'] ?? 0),
            'hands'          => (int) ($_POST['hands'] ?? 0),
            'small_hands'    => (int) ($_POST['small_hands'] ?? 0),
            'class_a_fp'     => (int) ($_POST['class_a_fp'] ?? 0),
            'class_b_h'      => (int) ($_POST['class_b_h'] ?? 0),
            'class_b_id'     => (int) ($_POST['class_b_id'] ?? 0),
            'class_b_cl_b'   => (int) ($_POST['class_b_cl_b'] ?? 0),
            'group_number'   => !empty($_POST['group_number']) ? (int) $_POST['group_number'] : null,
            'block_number'   => trim($_POST['block_number'] ?? ''),
            'carrier_name'   => trim($_POST['carrier_name'] ?? ''),
            'arrival_time'   => $_POST['arrival_time'] ?? null,
            'first_box_out'  => $_POST['first_box_out'] ?? null,
            'last_box_out'   => $_POST['last_box_out'] ?? null,
            'week_number'    => trim($_POST['week_number'] ?? ''),
            'cycle_code'     => trim($_POST['cycle_code'] ?? ''),
            'field_location' => trim($_POST['field_location'] ?? ''),
            'notes'          => trim($_POST['notes'] ?? ''),
            'recorded_by'    => (int) $_SESSION[SESS_USER_ID],
        ];

        $newId = $productionModel->create($data, $defects, [], $stemDetails);
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update' && $role === ROLE_PRODUCTION) {
    Csrf::verify();
    try {
        $prodId = (int) $_POST['production_id'];
        $data = [
            'harvest_date'   => $_POST['harvest_date'],
            'worker_id'      => (int) $_POST['worker_id'],
            'boxes_produced' => (int) $_POST['boxes_produced'],
            'stems_cut'      => (int) ($_POST['stems_cut'] ?? 0),
            'hands'          => (int) ($_POST['hands'] ?? 0),
            'small_hands'    => (int) ($_POST['small_hands'] ?? 0),
            'class_a_fp'     => (int) ($_POST['class_a_fp'] ?? 0),
            'class_b_h'      => (int) ($_POST['class_b_h'] ?? 0),
            'class_b_id'     => (int) ($_POST['class_b_id'] ?? 0),
            'class_b_cl_b'   => (int) ($_POST['class_b_cl_b'] ?? 0),
            'group_number'   => !empty($_POST['group_number']) ? (int) $_POST['group_number'] : null,
            'block_number'   => trim($_POST['block_number'] ?? ''),
            'carrier_name'   => trim($_POST['carrier_name'] ?? ''),
            'arrival_time'   => $_POST['arrival_time'] ?? null,
            'first_box_out'  => $_POST['first_box_out'] ?? null,
            'last_box_out'   => $_POST['last_box_out'] ?? null,
            'week_number'    => trim($_POST['week_number'] ?? ''),
            'cycle_code'     => trim($_POST['cycle_code'] ?? ''),
            'field_location' => trim($_POST['field_location'] ?? ''),
            'notes'          => trim($_POST['notes'] ?? ''),
        ];
        $productionModel->update($prodId, $data);

        $logger->log(
            (int) $_SESSION[SESS_USER_ID],
            'production_update',
            "Updated production record #{$prodId}.",
            'production_data',
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
            'production_delete',
            "Deleted production record #{$prodId}. Associated materials restored to inventory.",
            'production_data',
            $prodId
        );
        $message = 'Production record deleted successfully. Inventory restored.';
    } catch (PDOException $e) {
        $error = 'Error deleting record: ' . $e->getMessage();
    }
}

// ── DATA FOR VIEW ────────────────────────────────────────────
if ($tab === 'harvest_parameters') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'store_hp' && $role === ROLE_PRODUCTION) {
        Csrf::verify();
        try {
            $data = $_POST;
            
            // Format defects
            if (!empty($data['defect_name'])) {
                $data['defects'] = [];
                foreach ($data['defect_name'] as $i => $name) {
                    if (trim($name)) {
                        $data['defects'][] = [
                            'name' => trim($name),
                            'w8' => $data['defect_w8'][$i] ?? null,
                            'w9' => $data['defect_w9'][$i] ?? null,
                            'w10' => $data['defect_w10'][$i] ?? null,
                            'w11' => $data['defect_w11'][$i] ?? null,
                            'total' => $data['defect_total'][$i] ?? null,
                        ];
                    }
                }
            }
            
            // Format calibrations
            $data['calibrations'] = [];
            if (!empty($data['cal_11'])) {
                $data['calibrations']['CALIBRATION'] = [
                    'week_11' => $data['cal_11'], 'week_12' => $data['cal_12'] ?? null, 
                    'week_13' => $data['cal_13'] ?? null, 'week_14' => $data['cal_14'] ?? null
                ];
            }
            if (!empty($data['col_11'])) {
                $data['calibrations']['COLOR_CODE'] = [
                    'week_11' => $data['col_11'], 'week_12' => $data['col_12'] ?? null, 
                    'week_13' => $data['col_13'] ?? null, 'week_14' => $data['col_14'] ?? null
                ];
            }

            // Format farm rejects
            if (!empty($data['rej_11'])) {
                $data['farm_rejects'] = [
                    'code_11' => $data['rej_11'], 'code_12' => $data['rej_12'] ?? null,
                    'code_13' => $data['rej_13'] ?? null, 'code_14' => $data['rej_14'] ?? null,
                    'total' => $data['rej_total'] ?? null
                ];
            }

            $hpModel->create($data, (int) $_SESSION[SESS_USER_ID]);
            $message = "Harvest Parameter record saved successfully.";
        } catch (\Exception $e) {
            $error = 'Failed to save Harvest Parameter: ' . $e->getMessage();
        }
    }

    $hpRecords = $hpModel->getAll();
    require_once VIEW_PATH . 'production/harvest_parameters.php';
} elseif ($tab === 'daily_reports') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'store_dr' && $role === ROLE_PRODUCTION) {
        Csrf::verify();
        try {
            $data = $_POST;
            $data['boxes'] = [];
            
            if (!empty($data['box_class'])) {
                foreach ($data['box_class'] as $i => $cls) {
                    $data['boxes'][] = [
                        'class' => $cls,
                        'group' => $data['box_group'][$i] ?? '',
                        'spec' => $data['box_spec'][$i] ?? '',
                        'tally' => $data['box_tally'][$i] ?? 0,
                        'adj' => $data['box_adj'][$i] ?? 0,
                        'should' => $data['box_should'][$i] ?? 0
                    ];
                }
            }
            
            $drModel->create($data, (int) $_SESSION[SESS_USER_ID]);
            $message = "Daily Production Report saved successfully.";
        } catch (\Exception $e) {
            $error = 'Failed to save Daily Report: ' . $e->getMessage();
        }
    }

    $drRecords = $drModel->getAll();
    require_once VIEW_PATH . 'production/daily_reports.php';
} else {
    // Default tab: daily_log
    $records       = $productionModel->getAll();
    $activeWorkers = $workerModel->getActive();

    require_once VIEW_PATH . 'production/index.php';
}
