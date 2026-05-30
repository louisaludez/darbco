<?php
// views/production/harvest_parameters.php
$pageTitle = 'Harvest Parameters';
require_once VIEW_PATH . 'layout/header.php';
$role = $_SESSION[SESS_ROLE];
?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">

    <div class="page-header">
        <div>
            <h1><i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i>Production Records</h1>
        </div>
        <?php if ($role === ROLE_PRODUCTION): ?>
        <button class="btn btn-darbco" data-bs-toggle="modal" data-bs-target="#addHPModal">
            <i class="bi bi-plus-circle me-2"></i>New Harvest Parameter
        </button>
        <?php endif; ?>
    </div>

    <!-- Production Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?= !isset($_GET['tab']) || $_GET['tab'] === 'daily_log' ? 'active fw-bold' : '' ?>" href="index.php?page=production&tab=daily_log">
                <i class="bi bi-person-lines-fill me-1"></i> Individual ARB Logs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isset($_GET['tab']) && $_GET['tab'] === 'harvest_parameters' ? 'active fw-bold' : '' ?>" href="index.php?page=production&tab=harvest_parameters">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Harvest Parameters
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isset($_GET['tab']) && $_GET['tab'] === 'daily_reports' ? 'active fw-bold' : '' ?>" href="index.php?page=production&tab=daily_reports">
                <i class="bi bi-graph-up me-1"></i> Daily Reports
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isset($_GET['tab']) && $_GET['tab'] === 'daily_boxes_per_group' ? 'active fw-bold' : '' ?>" href="index.php?page=production&tab=daily_boxes_per_group">
                <i class="bi bi-box-seam me-1"></i> Daily Boxes Per Group
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isset($_GET['tab']) && $_GET['tab'] === 'daily_production_per_beneficiary' ? 'active fw-bold' : '' ?>" href="index.php?page=production&tab=daily_production_per_beneficiary">
                <i class="bi bi-person-badge me-1"></i> Daily Production Per Beneficiary
            </a>
        </li>
    </ul>

    <?php if ($message): ?>
    <div class="alert alert-success alert-auto-dismiss d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger alert-auto-dismiss d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <div class="table-card">
        <div class="card-header">Harvest Parameters List</div>
        <div class="table-responsive p-2">
            <table class="table table-hover darbco-table w-100">
                <thead>
                    <tr>
                        <th>#</th><th>Date</th><th>Cutting Grp</th><th>Crew Size</th>
                        <th>Stem Cut</th><th>Farm Rejects</th><th>Calibration</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($hpRecords as $r): ?>
                    <tr>
                        <td><?= $r['hp_id'] ?></td>
                        <td><?= htmlspecialchars($r['harvest_date']) ?></td>
                        <td><?= htmlspecialchars($r['cutting_group'] ?? '—') ?></td>
                        <td><?= $r['crew_size'] ?></td>
                        <td><?= $r['stem_cut'] ?></td>
                        <td><?= $r['farm_rejects_total'] ?></td>
                        <td><?= $r['ave_calibration'] ?></td>
                        <td><?= htmlspecialchars($r['recorded_by_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addHPModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="index.php?page=production&tab=harvest_parameters&action=store_hp">
                    <?= Csrf::field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>New Harvest Parameter Form</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body bg-light">
                        <div class="row mb-3 px-2">
                            <div class="col-md-4 ms-auto text-end">
                                <div class="input-group input-group-sm shadow-sm">
                                    <span class="input-group-text bg-white border-end-0 fw-bold"><i class="bi bi-calendar3 me-2 text-primary"></i>Date:</span>
                                    <input type="date" name="harvest_date" class="form-control border-start-0 fw-bold text-primary" required value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="accordion" id="hpAccordion">
                            <div class="accordion-item shadow-sm border-0 mb-3">
                                <h2 class="accordion-header" id="headingHP">
                                    <button class="accordion-button bg-white text-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHP" aria-expanded="true" aria-controls="collapseHP">
                                        <i class="bi bi-table me-2 text-primary"></i> Harvest Parameter Matrix
                                    </button>
                                </h2>
                                <div id="collapseHP" class="accordion-collapse collapse show" aria-labelledby="headingHP">
                                    <div class="accordion-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm align-middle text-center mb-0 hp-matrix-table" id="hpMatrixTable" style="font-size: 0.875rem;">
                                                <tbody>
                                                    <!-- Row 1 -->
                                                    <tr class="bg-light">
                                                        <th class="text-start" style="width:18%;">CUTTING GRP.</th>
                                                        <td style="width:12%;"><input type="text" name="cutting_group" class="form-control form-control-sm text-center fw-bold text-primary"></td>
                                                        <th colspan="6" class="bg-secondary text-white text-uppercase" style="letter-spacing: 1px;">FARM REJECTS (BY AGE)</th>
                                                    </tr>
                                                    <!-- Row 2 -->
                                                    <tr class="bg-light">
                                                        <th class="text-start">CREW SIZE</th>
                                                        <td><input type="number" name="crew_size" class="form-control form-control-sm text-center fw-bold text-primary"></td>
                                                        <th style="width:12%;">CODE</th>
                                                        <th style="width:11%;">11</th>
                                                        <th style="width:11%;">12</th>
                                                        <th style="width:11%;">13</th>
                                                        <th style="width:11%;">14</th>
                                                        <th style="width:14%;">TOTAL</th>
                                                    </tr>
                                                    <!-- Row 3 -->
                                                    <tr>
                                                        <th class="text-start bg-light">MANHOURS</th>
                                                        <td><input type="number" step="0.01" name="manhours" class="form-control form-control-sm text-center fw-bold text-primary"></td>
                                                        <td class="bg-light"></td>
                                                        <th class="bg-light text-muted small">YW</th>
                                                        <th class="bg-light text-muted small">DG</th>
                                                        <th class="bg-light text-muted small">BLL</th>
                                                        <th class="bg-light text-muted small">DB</th>
                                                        <td class="bg-light"></td>
                                                    </tr>
                                                    
                                                    <!-- Rows 4 to 20 -->
                                                    <?php
                                                    $leftLabels = [
                                                        4 => ['STEM CUT', 'stem_cut', 'number'],
                                                        5 => ['FARM REJECTS', 'farm_rejects_total', 'number'],
                                                        6 => ['AVE. FINGERLENGTH', 'ave_fingerlength', 'number', '0.01'],
                                                        7 => ['AVE. HANDCLASS', 'ave_handclass', 'number', '0.01'],
                                                        8 => ['AVE. STEM WEIGHT', 'ave_stem_weight', 'number', '0.01'],
                                                        9 => ['% AREA COVERED', 'percent_area_covered', 'number', '0.01'],
                                                        10 => ['AVE. CALIBRATION', 'ave_calibration', 'number', '0.01'],
                                                        11 => ['CALIBRATION BY WEEK', null, null],
                                                        12 => ['11 WOF', 'cal_11', 'text'],
                                                        13 => ['12 WOF', 'cal_12', 'text'],
                                                        14 => ['13 WOF', 'cal_13', 'text'],
                                                        15 => ['14 WOF', 'cal_14', 'text'],
                                                        16 => ['COLOR CODE', null, null],
                                                        17 => ['11 WOF', 'col_11', 'text'],
                                                        18 => ['12 WOF', 'col_12', 'text'],
                                                        19 => ['13 WOF', 'col_13', 'text'],
                                                        20 => ['14 WOF', 'col_14', 'text'],
                                                    ];

                                                    for ($i = 4; $i <= 20; $i++):
                                                        $label = $leftLabels[$i][0];
                                                        $name = $leftLabels[$i][1];
                                                        $type = $leftLabels[$i][2] ?? 'text';
                                                        $step = $leftLabels[$i][3] ?? '';
                                                    ?>
                                                    <tr>
                                                        <th class="text-start bg-light"><?= $label ?></th>
                                                        <td>
                                                            <?php if ($name): ?>
                                                                <input type="<?= $type ?>" <?= $step ? 'step="'.$step.'"' : '' ?> name="<?= $name ?>" class="form-control form-control-sm text-center fw-bold text-primary">
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><input type="text" name="rej_code[]" class="form-control form-control-sm text-center text-uppercase fw-bold text-danger"></td>
                                                        <td><input type="number" name="rej_11[]" class="form-control form-control-sm text-center rej-val"></td>
                                                        <td><input type="number" name="rej_12[]" class="form-control form-control-sm text-center rej-val"></td>
                                                        <td><input type="number" name="rej_13[]" class="form-control form-control-sm text-center rej-val"></td>
                                                        <td><input type="number" name="rej_14[]" class="form-control form-control-sm text-center rej-val"></td>
                                                        <td><input type="number" name="rej_total[]" class="form-control form-control-sm text-center rej-total fw-bold bg-light" readonly></td>
                                                    </tr>
                                                    <?php endfor; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Defects Matrix -->
                            <div class="accordion-item shadow-sm border-0 mb-3">
                                <h2 class="accordion-header" id="headingDefects">
                                    <button class="accordion-button collapsed bg-white text-dark fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDefects" aria-expanded="false" aria-controls="collapseDefects">
                                        <i class="bi bi-grid-3x3-gap me-2 text-primary"></i> Defects Matrix
                                    </button>
                                </h2>
                                <div id="collapseDefects" class="accordion-collapse collapse" aria-labelledby="headingDefects" data-bs-parent="#hpAccordion">
                                    <div class="accordion-body p-0">
                                        <div class="card border-0">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                                    <h6 class="text-primary mb-0 fw-bold">Defects Entry</h6>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addDefectRow"><i class="bi bi-plus"></i> Add Row</button>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered text-center align-middle" id="defectTable" style="font-size: 0.875rem;">
                                                        <thead class="table-light">
                                                            <tr><th rowspan="2" class="align-middle text-start" style="width:25%;">DEFECTS</th><th>8 wks</th><th>9 wks</th><th>10 wks</th><th>11 wks</th><th rowspan="2" class="align-middle" style="width:12%;">TOTAL</th><th rowspan="2" style="width:5%;"></th></tr>
                                                            <tr><th class="small text-muted">YW</th><th class="small text-muted">DG</th><th class="small text-muted">BLL</th><th class="small text-muted">DB</th></tr>
                                                        </thead>
                                                        <tbody id="defectRows">
                                                            <?php $defaultDefects = ['Aurora','Tutor','Casa','Tagotongan','Magolenio','Garado M','Casulad']; ?>
                                                            <?php foreach ($defaultDefects as $d): ?>
                                                            <tr>
                                                                <td><input type="text" name="defect_name[]" class="form-control form-control-sm text-start fw-bold" value="<?= $d ?>"></td>
                                                                <td><input type="number" name="defect_w8[]" class="form-control form-control-sm text-center defect-val"></td>
                                                                <td><input type="number" name="defect_w9[]" class="form-control form-control-sm text-center defect-val"></td>
                                                                <td><input type="number" name="defect_w10[]" class="form-control form-control-sm text-center defect-val"></td>
                                                                <td><input type="number" name="defect_w11[]" class="form-control form-control-sm text-center defect-val"></td>
                                                                <td><input type="number" name="defect_total[]" class="form-control form-control-sm text-center defect-total fw-bold bg-light" readonly></td>
                                                                <td><button type="button" class="btn btn-sm btn-outline-danger remove-defect"><i class="bi bi-x"></i></button></td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-darbco"><i class="bi bi-save me-2"></i>Save Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Defects Matrix Row Addition
    document.getElementById('addDefectRow')?.addEventListener('click', () => {
        document.getElementById('defectRows').insertAdjacentHTML('beforeend', `<tr>
            <td><input type="text" name="defect_name[]" class="form-control form-control-sm text-start" placeholder="New Defect"></td>
            <td><input type="number" name="defect_w8[]" class="form-control form-control-sm defect-val"></td>
            <td><input type="number" name="defect_w9[]" class="form-control form-control-sm defect-val"></td>
            <td><input type="number" name="defect_w10[]" class="form-control form-control-sm defect-val"></td>
            <td><input type="number" name="defect_w11[]" class="form-control form-control-sm defect-val"></td>
            <td><input type="number" name="defect_total[]" class="form-control form-control-sm defect-total" readonly></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-defect"><i class="bi bi-x"></i></button></td>
        </tr>`);
    });

    document.getElementById('defectTable')?.addEventListener('click', e => {
        if (e.target.closest('.remove-defect')) e.target.closest('tr').remove();
    });

    // Auto-calculate Farm Rejects Total
    const hpMatrixTable = document.getElementById('hpMatrixTable');
    if (hpMatrixTable) {
        hpMatrixTable.addEventListener('input', (e) => {
            if (e.target.classList.contains('rej-val')) {
                const row = e.target.closest('tr');
                if (!row) return;
                
                const r11 = parseInt(row.querySelector('input[name="rej_11[]"]')?.value || 0);
                const r12 = parseInt(row.querySelector('input[name="rej_12[]"]')?.value || 0);
                const r13 = parseInt(row.querySelector('input[name="rej_13[]"]')?.value || 0);
                const r14 = parseInt(row.querySelector('input[name="rej_14[]"]')?.value || 0);
                
                const totalInput = row.querySelector('input[name="rej_total[]"]');
                if (totalInput) {
                    const sum = r11 + r12 + r13 + r14;
                    totalInput.value = sum > 0 ? sum : '';
                }
            }
        });
    }

    // Auto-calculate Defects Matrix Totals
    const defectTable = document.getElementById('defectTable');
    if (defectTable) {
        defectTable.addEventListener('input', (e) => {
            if (e.target.tagName === 'INPUT' && e.target.name !== 'defect_name[]') {
                const row = e.target.closest('tr');
                if (!row) return;
                
                const w8 = parseInt(row.querySelector('input[name="defect_w8[]"]')?.value || 0);
                const w9 = parseInt(row.querySelector('input[name="defect_w9[]"]')?.value || 0);
                const w10 = parseInt(row.querySelector('input[name="defect_w10[]"]')?.value || 0);
                const w11 = parseInt(row.querySelector('input[name="defect_w11[]"]')?.value || 0);
                
                const totalInput = row.querySelector('input[name="defect_total[]"]');
                if (totalInput) {
                    const sum = w8 + w9 + w10 + w11;
                    totalInput.value = sum > 0 ? sum : '';
                }
            }
        });
    }
});
</script>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
