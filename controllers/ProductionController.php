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
require_once MODEL_PATH . 'DailyBox.php';

$productionModel = new Production();
$inventoryModel  = new Inventory();
$workerModel     = new Worker();
$logger          = new TransactionLog();
$hpModel         = new HarvestParameter();
$drModel         = new DailyReport();
$dbModel         = new DailyBox();

$role    = $_SESSION[SESS_ROLE];
$action  = $_GET['action']  ?? 'list';
$tab     = $_GET['tab'] ?? 'daily_log'; // tabs: daily_log, harvest_parameters, daily_reports
$message = '';
$error   = '';

// ── CREATE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'store' && $role === ROLE_PRODUCTION) {
    Csrf::verify();
    try {
        // Collect global date
        $harvestDate = $_POST['harvest_date'];
        $recordedBy = (int) $_SESSION[SESS_USER_ID];

        $beneficiaries = (array) $_POST['beneficiary_name'];
        $count = count($beneficiaries);
        $insertedCount = 0;

        for ($i = 0; $i < $count; $i++) {
            $beneficiary = trim($beneficiaries[$i] ?? '');
            if (empty($beneficiary)) continue;

            // Per-row stem details
            $stemDetails = [];
            foreach ([11, 12, 13, 14] as $rowNum) {
                $c = (int) ($_POST["stem_row_{$rowNum}"][$i] ?? 0);
                if ($c > 0) {
                    $stemDetails[] = ['row_number' => $rowNum, 'stem_count' => $c];
                }
            }

            $data = [
                'harvest_date'     => $harvestDate,
                'worker_id'        => null,
                'beneficiary_name' => $beneficiary,
                'boxes_produced'   => 0,
                'stems_cut'        => (int) ($_POST['stems_cut'][$i] ?? 0),
                'hands'            => 0,
                'small_hands'      => 0,
                'class_a_fp'       => 0,
                'class_b_h'        => 0,
                'class_b_id'       => 0,
                'class_b_cl_b'     => 0,
                'group_number'     => null,
                'block_number'     => trim($_POST['block_number'][$i] ?? ''),
                'carrier_name'     => trim($_POST['carrier_name'][$i] ?? ''),
                'arrival_time'     => !empty($_POST['arrival_time'][$i]) ? $_POST['arrival_time'][$i] : null,
                'first_box_out'    => null,
                'last_box_out'     => null,
                'week_number'      => '',
                'cycle_code'       => '',
                'field_location'   => '',
                'notes'            => trim($_POST['running_tt1'][$i] ?? ''),
                'recorded_by'      => $recordedBy,
            ];

            $newId = $productionModel->create($data, [], [], $stemDetails);
            $logger->log(
                $recordedBy,
                'production_insert',
                "Production record #{$newId} created for beneficiary {$beneficiary}.",
                'production_data',
                $newId
            );
            $insertedCount++;
        }

        $message = "{$insertedCount} Production record(s) saved successfully.";
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
            'harvest_date'     => $_POST['harvest_date'],
            'worker_id'        => null,
            'beneficiary_name' => trim($_POST['beneficiary_name'] ?? ''),
            'boxes_produced'   => (int) $_POST['boxes_produced'],
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
            $data['farm_rejects'] = [];
            if (!empty($data['rej_code'])) {
                foreach ($data['rej_code'] as $i => $code) {
                    if (trim($code)) {
                        $data['farm_rejects'][] = [
                            'reject_code' => trim($code),
                            'code_11' => $data['rej_11'][$i] ?? null,
                            'code_12' => $data['rej_12'][$i] ?? null,
                            'code_13' => $data['rej_13'][$i] ?? null,
                            'code_14' => $data['rej_14'][$i] ?? null,
                            'total'   => $data['rej_total'][$i] ?? null
                        ];
                    }
                }
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
} elseif ($tab === 'daily_boxes_per_group') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'store_db' && $role === ROLE_PRODUCTION) {
        Csrf::verify();
        try {
            $data = $_POST;
            $formattedData = [
                'db_date'       => $data['db_date'],
                'db_first_time' => $data['db_first_time'] ?? null,
                'db_last_time'  => $data['db_last_time'] ?? null,
                'class_a'       => [],
                'class_b'       => []
            ];

            // Parse Class A
            $classARows = ['4/5/6 Hands', '7/8/9 Hands', '4.7 k', '7.2 k', 'BCP', 'BCP', 'BCP', 'BCP'];
            foreach ($classARows as $idx => $rowLabel) {
                // Group 1
                if (!empty($data['g1_tally_a'][$idx]) || !empty($data['g1_adj_a'][$idx]) || !empty($data['g1_should_a'][$idx])) {
                    $formattedData['class_a'][] = [
                        'row_label' => $rowLabel,
                        'group_num' => 1,
                        'tally'     => $data['g1_tally_a'][$idx] ?? 0,
                        'adj'       => $data['g1_adj_a'][$idx] ?? 0,
                        'should_be' => $data['g1_should_a'][$idx] ?? 0,
                    ];
                }
                // Group 3
                if (!empty($data['g3_tally_a'][$idx]) || !empty($data['g3_adj_a'][$idx]) || !empty($data['g3_should_a'][$idx])) {
                    $formattedData['class_a'][] = [
                        'row_label' => $rowLabel,
                        'group_num' => 3,
                        'tally'     => $data['g3_tally_a'][$idx] ?? 0,
                        'adj'       => $data['g3_adj_a'][$idx] ?? 0,
                        'should_be' => $data['g3_should_a'][$idx] ?? 0,
                    ];
                }
            }

            // Parse Class B
            $classBRows = ['4/5/6 Hands', 'Sml H / Clusters', 'F. P'];
            foreach ($classBRows as $idx => $rowLabel) {
                // Group 1
                if (!empty($data['g1_tally_b'][$idx]) || !empty($data['g1_adj_b'][$idx]) || !empty($data['g1_should_b'][$idx])) {
                    $formattedData['class_b'][] = [
                        'row_label' => $rowLabel,
                        'group_num' => 1,
                        'tally'     => $data['g1_tally_b'][$idx] ?? 0,
                        'adj'       => $data['g1_adj_b'][$idx] ?? 0,
                        'should_be' => $data['g1_should_b'][$idx] ?? 0,
                    ];
                }
                // Group 3
                if (!empty($data['g3_tally_b'][$idx]) || !empty($data['g3_adj_b'][$idx]) || !empty($data['g3_should_b'][$idx])) {
                    $formattedData['class_b'][] = [
                        'row_label' => $rowLabel,
                        'group_num' => 3,
                        'tally'     => $data['g3_tally_b'][$idx] ?? 0,
                        'adj'       => $data['g3_adj_b'][$idx] ?? 0,
                        'should_be' => $data['g3_should_b'][$idx] ?? 0,
                    ];
                }
            }

            $dbModel->create($formattedData, (int) $_SESSION[SESS_USER_ID]);
            $message = "Daily Boxes record submitted successfully!";
        } catch (\Exception $e) {
            $error = 'Failed to save Daily Boxes: ' . $e->getMessage();
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_db_record' && isset($_GET['id'])) {
        // AJAX endpoint for viewing a record
        header('Content-Type: application/json');
        $record = $dbModel->getById((int) $_GET['id']);
        if ($record) {
            echo json_encode(['success' => true, 'data' => $record]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Record not found']);
        }
        exit;
    }
    
    $dbRecords = $dbModel->getAll();
    require_once VIEW_PATH . 'production/daily_boxes_per_group.php';
} elseif ($tab === 'daily_production_per_beneficiary') {
    require_once __DIR__ . '/../models/DailyProdBeneficiary.php';
    $dpbModel = new DailyProdBeneficiary();
    $message = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'store_dpb') {
        Csrf::verify();
        try {
            $data = $_POST;
            $items = [];
            
            // Reconstruct array of items from POST arrays
            if (!empty($data['sub_code']) && is_array($data['sub_code'])) {
                foreach ($data['sub_code'] as $index => $subCode) {
                    // Only process row if at least one field has data
                    if (
                        !empty($subCode) || 
                        !empty($data['arb_name'][$index]) || 
                        !empty($data['stems_cut'][$index])
                    ) {
                        $items[] = [
                            'sub_code'       => $subCode,
                            'arb_name'       => $data['arb_name'][$index] ?? '',
                            'stems_cut'      => $data['stems_cut'][$index] ?? '',
                            'class_a_hands'  => $data['class_a_hands'][$index] ?? '',
                            'class_a_sh'     => $data['class_a_sh'][$index] ?? '',
                            'class_a_blank1' => $data['class_a_blank1'][$index] ?? '',
                            'class_a_fp'     => $data['class_a_fp'][$index] ?? '',
                            'class_a_blank2' => $data['class_a_blank2'][$index] ?? '',
                            'class_a_blank3' => $data['class_a_blank3'][$index] ?? '',
                            'class_a_cl_b'   => $data['class_a_cl_b'][$index] ?? '',
                            'class_b_h'      => $data['class_b_h'][$index] ?? '',
                            'class_b_id'     => $data['class_b_id'][$index] ?? '',
                        ];
                    }
                }
            }

            $formattedData = [
                'packing_date' => $data['packing_date'],
                'items'        => $items
            ];

            $dpbModel->create($formattedData, (int) $_SESSION[SESS_USER_ID]);
            $message = "Daily Production Per Beneficiary record submitted successfully!";
        } catch (\Exception $e) {
            $error = 'Failed to save record: ' . $e->getMessage();
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_dpb_record' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        $record = $dpbModel->getById((int) $_GET['id']);
        if ($record) {
            echo json_encode(['success' => true, 'data' => $record]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Record not found']);
        }
        exit;
    }

    $dpbRecords = $dpbModel->getAll();
    require_once VIEW_PATH . 'production/daily_production_per_beneficiary.php';
} else {
    // Default tab: daily_log
    $records       = $productionModel->getAll();
    $activeWorkers = $workerModel->getActive();

    require_once VIEW_PATH . 'production/index.php';
}
