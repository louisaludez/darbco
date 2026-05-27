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
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body row g-3">
                                <h6 class="text-primary border-bottom pb-2 mb-3">General Information</h6>
                                <div class="col-md-3"><label class="form-label">Date *</label><input type="date" name="harvest_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
                                <div class="col-md-3"><label class="form-label">Cutting Grp.</label><input type="text" name="cutting_group" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">Crew Size</label><input type="number" name="crew_size" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">Manhours</label><input type="number" step="0.01" name="manhours" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">Stem Cut</label><input type="number" name="stem_cut" class="form-control"></div>
                                
                                <div class="col-md-2"><label class="form-label">Farm Rejects</label><input type="number" name="farm_rejects_total" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">Ave. Fingerlength</label><input type="number" step="0.01" name="ave_fingerlength" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">Ave. Handclass</label><input type="number" step="0.01" name="ave_handclass" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">Ave. Stem Weight</label><input type="number" step="0.01" name="ave_stem_weight" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">% Area Covered</label><input type="number" step="0.01" name="percent_area_covered" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">Ave. Calibration</label><input type="number" step="0.01" name="ave_calibration" class="form-control"></div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="card-body">
                                        <h6 class="text-primary border-bottom pb-2 mb-3">Calibration & Color Code By Week</h6>
                                        <table class="table table-sm table-bordered text-center">
                                            <thead class="table-light"><tr><th></th><th>11 WOF</th><th>12 WOF</th><th>13 WOF</th><th>14 WOF</th></tr></thead>
                                            <tbody>
                                                <tr><th class="text-start">CALIBRATION</th>
                                                    <td><input type="text" name="cal_11" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="cal_12" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="cal_13" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="cal_14" class="form-control form-control-sm"></td>
                                                </tr>
                                                <tr><th class="text-start">COLOR CODE</th>
                                                    <td><input type="text" name="col_11" class="form-control form-control-sm" placeholder="YW"></td>
                                                    <td><input type="text" name="col_12" class="form-control form-control-sm" placeholder="DG"></td>
                                                    <td><input type="text" name="col_13" class="form-control form-control-sm" placeholder="BLL"></td>
                                                    <td><input type="text" name="col_14" class="form-control form-control-sm" placeholder="DB"></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <h6 class="text-primary border-bottom pb-2 mt-4 mb-3">Farm Rejects (By Age)</h6>
                                        <table class="table table-sm table-bordered text-center">
                                            <thead class="table-light"><tr><th>Code 11</th><th>12</th><th>13</th><th>14</th><th>Total</th></tr></thead>
                                            <tbody>
                                                <tr>
                                                    <td><input type="text" name="rej_11" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="rej_12" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="rej_13" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="rej_14" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="rej_total" class="form-control form-control-sm"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                            <h6 class="text-primary mb-0">Defects Matrix</h6>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="addDefectRow"><i class="bi bi-plus"></i> Add Row</button>
                                        </div>
                                        <table class="table table-sm table-bordered text-center" id="defectTable">
                                            <thead class="table-light">
                                                <tr><th rowspan="2" class="align-middle text-start">DEFECTS</th><th>8 wks</th><th>9 wks</th><th>10 wks</th><th>11 wks</th><th rowspan="2" class="align-middle">TOTAL</th><th rowspan="2"></th></tr>
                                                <tr><th class="small text-muted">YW</th><th class="small text-muted">DG</th><th class="small text-muted">BLL</th><th class="small text-muted">DB</th></tr>
                                            </thead>
                                            <tbody id="defectRows">
                                                <?php $defaultDefects = ['Aurora','Tutor','Casa','Tagotongan','Magolenio','Garado M','Casulad']; ?>
                                                <?php foreach ($defaultDefects as $d): ?>
                                                <tr>
                                                    <td><input type="text" name="defect_name[]" class="form-control form-control-sm text-start" value="<?= $d ?>"></td>
                                                    <td><input type="text" name="defect_w8[]" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="defect_w9[]" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="defect_w10[]" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="defect_w11[]" class="form-control form-control-sm"></td>
                                                    <td><input type="text" name="defect_total[]" class="form-control form-control-sm"></td>
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
    document.getElementById('addDefectRow')?.addEventListener('click', () => {
        document.getElementById('defectRows').insertAdjacentHTML('beforeend', `<tr>
            <td><input type="text" name="defect_name[]" class="form-control form-control-sm text-start" placeholder="New Defect"></td>
            <td><input type="text" name="defect_w8[]" class="form-control form-control-sm"></td>
            <td><input type="text" name="defect_w9[]" class="form-control form-control-sm"></td>
            <td><input type="text" name="defect_w10[]" class="form-control form-control-sm"></td>
            <td><input type="text" name="defect_w11[]" class="form-control form-control-sm"></td>
            <td><input type="text" name="defect_total[]" class="form-control form-control-sm"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-defect"><i class="bi bi-x"></i></button></td>
        </tr>`);
    });
    document.getElementById('defectTable')?.addEventListener('click', e => {
        if (e.target.closest('.remove-defect')) e.target.closest('tr').remove();
    });
});
</script>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
