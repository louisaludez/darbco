<?php
// views/production/index.php
$pageTitle = 'Production Records';
require_once VIEW_PATH . 'layout/header.php';
$role = $_SESSION[SESS_ROLE];
?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">

    <div class="page-header">
        <div>
            <h1><i class="bi bi-boxes me-2 text-success"></i>Production Records</h1>
        </div>
        <?php if ($role === ROLE_PRODUCTION): ?>
        <button class="btn btn-darbco" data-bs-toggle="modal" data-bs-target="#addProductionModal" id="addProductionBtn">
            <i class="bi bi-plus-circle me-2"></i>New Record
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

    <!-- Records Table -->
    <div class="table-card">
        <div class="card-header">Daily Harvest Log</div>
        <div class="table-responsive p-2">
            <table class="table table-hover darbco-table w-100" id="productionTable">
                <thead>
                    <tr>
                        <th>#</th><th>Date</th><th>Sub Code</th><th>Worker</th>
                        <th>Group</th><th>Block</th><th>Stems Cut</th><th>Total Boxes</th>
                        <th>Week</th><th>Recorded By</th><th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ($records as $r): ?>
                    <tr>
                        <td><?= $r['production_id'] ?></td>
                        <td><?= htmlspecialchars($r['harvest_date']) ?></td>
                        <td><span class="badge bg-dark font-monospace"><?= htmlspecialchars($r['sub_code'] ?? '—') ?></span></td>
                        <td><?= htmlspecialchars($r['worker_name']) ?></td>
                        <td><?= $r['group_number'] ? 'Group ' . $r['group_number'] : '—' ?></td>
                        <td><?= htmlspecialchars($r['block_number'] ?? '—') ?></td>
                        <td><?= number_format((int)($r['stems_cut'] ?? 0)) ?></td>
                        <td><strong><?= number_format($r['boxes_produced']) ?></strong></td>
                        <td><?= htmlspecialchars($r['week_number'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['recorded_by_name']) ?></td>
                        <td class="text-muted small"><?= date('M j Y', strtotime($r['created_at'])) ?></td>
                        <td class="text-end">
                            <?php if ($role === ROLE_PRODUCTION): ?>
                            <button class="btn btn-sm btn-outline-primary btn-edit-prod"
                                    data-bs-toggle="modal" data-bs-target="#editProductionModal"
                                    data-id="<?= $r['production_id'] ?>"
                                    data-date="<?= htmlspecialchars($r['harvest_date']) ?>"
                                    data-worker-id="<?= $r['worker_id'] ?>"
                                    data-boxes="<?= $r['boxes_produced'] ?>"
                                    data-stems="<?= $r['stems_cut'] ?? 0 ?>"
                                    data-group="<?= $r['group_number'] ?? '' ?>"
                                    data-block="<?= htmlspecialchars($r['block_number'] ?? '') ?>"
                                    data-carrier="<?= htmlspecialchars($r['carrier_name'] ?? '') ?>"
                                    data-arrival="<?= $r['arrival_time'] ?? '' ?>"
                                    data-fbo="<?= $r['first_box_out'] ?? '' ?>"
                                    data-lbo="<?= $r['last_box_out'] ?? '' ?>"
                                    data-week="<?= htmlspecialchars($r['week_number'] ?? '') ?>"
                                    data-cycle="<?= htmlspecialchars($r['cycle_code'] ?? '') ?>"
                                    data-loc="<?= htmlspecialchars($r['field_location'] ?? '') ?>"
                                    data-notes="<?= htmlspecialchars($r['notes'] ?? '') ?>"
                                    data-hands="<?= $r['hands'] ?? 0 ?>"
                                    data-small-hands="<?= $r['small_hands'] ?? 0 ?>"
                                    data-class-a-fp="<?= $r['class_a_fp'] ?? 0 ?>"
                                    data-class-b-h="<?= $r['class_b_h'] ?? 0 ?>"
                                    data-class-b-id="<?= $r['class_b_id'] ?? 0 ?>"
                                    data-class-b-cl-b="<?= $r['class_b_cl_b'] ?? 0 ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <?php endif; ?>
                            <?php if ($role === ROLE_ADMIN): ?>
                            <form method="POST" action="index.php?page=production&action=delete" class="d-inline"
                                  onsubmit="return confirm('WARNING: Deleting this record will delete its payroll history AND restore used materials to inventory. This cannot be undone. Proceed?');">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="production_id" value="<?= $r['production_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<!-- Add Production Modal -->
    <div class="modal fade" id="addProductionModal" tabindex="-1" aria-labelledby="addProductionLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="index.php?page=production&action=store">
                    <?= Csrf::field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductionLabel"><i class="bi bi-plus-circle me-2"></i>New Production Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body bg-light">
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body row g-3">
                                <h6 class="text-primary border-bottom pb-2 mb-3">General Information</h6>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Packing Date <span class="text-danger">*</span></label>
                                    <input type="date" name="harvest_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Worker / ARB <span class="text-danger">*</span></label>
                                    <select name="worker_id" class="form-select" required>
                                        <option value="">-- Select ARB --</option>
                                        <?php foreach ($activeWorkers as $w): ?>
                                        <option value="<?= $w['worker_id'] ?>">
                                            <?= htmlspecialchars(($w['sub_code'] ? '[' . $w['sub_code'] . '] ' : '') . $w['first_name'] . ' ' . $w['last_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Group</label>
                                    <select name="group_number" class="form-select">
                                        <option value="">-- Group --</option>
                                        <option value="1">Group 1</option>
                                        <option value="3">Group 3</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Block No.</label>
                                    <input type="text" name="block_number" class="form-control" placeholder="e.g. 10MAY">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Stems Cut</label>
                                    <input type="number" name="stems_cut" class="form-control" min="0" placeholder="0">
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body row g-3">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Harvest Sheet Fields</h6>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Carrier Name</label>
                                    <input type="text" name="carrier_name" class="form-control" placeholder="Name Carrero">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Arrival Time</label>
                                    <input type="time" name="arrival_time" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">1st Box Out</label>
                                    <input type="time" name="first_box_out" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Last Box Out</label>
                                    <input type="time" name="last_box_out" class="form-control">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label fw-semibold">Week</label>
                                    <input type="text" name="week_number" class="form-control" placeholder="Z">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Cycle</label>
                                    <input type="text" name="cycle_code" class="form-control" placeholder="e.g. C28">
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Total Boxes <span class="text-danger">*</span></label>
                                    <input type="number" name="boxes_produced" class="form-control" min="0" required placeholder="auto or manual">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Field Location</label>
                                    <input type="text" name="field_location" class="form-control" placeholder="e.g. Block A, Farm 2">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Notes</label>
                                    <input type="text" name="notes" class="form-control" placeholder="Optional notes...">
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="card-body">
                                        <h6 class="text-success border-bottom pb-2 mb-3"><i class="bi bi-grid-1x2 me-1"></i>Stem Counts Per Row</h6>
                                        <div class="row g-2 mb-4">
                                            <div class="col-md-6"><div class="input-group input-group-sm"><span class="input-group-text">Row 11</span><input type="number" name="stem_row_11" class="form-control" min="0" placeholder="0"></div></div>
                                            <div class="col-md-6"><div class="input-group input-group-sm"><span class="input-group-text">Row 12</span><input type="number" name="stem_row_12" class="form-control" min="0" placeholder="0"></div></div>
                                            <div class="col-md-6"><div class="input-group input-group-sm"><span class="input-group-text">Row 13</span><input type="number" name="stem_row_13" class="form-control" min="0" placeholder="0"></div></div>
                                            <div class="col-md-6"><div class="input-group input-group-sm"><span class="input-group-text">Row 14</span><input type="number" name="stem_row_14" class="form-control" min="0" placeholder="0"></div></div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mb-2 mt-4 border-top pt-3">
                                            <h6 class="text-primary mb-0"><i class="bi bi-list-check me-1"></i>Production Form Data</h6>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-md-4"><label class="form-label mb-0 small">Hands</label><input type="number" name="hands" class="form-control form-control-sm" min="0" placeholder="0"></div>
                                            <div class="col-md-4"><label class="form-label mb-0 small">SH (Small Hands)</label><input type="number" name="small_hands" class="form-control form-control-sm" min="0" placeholder="0"></div>
                                            <div class="col-md-4"><label class="form-label mb-0 small text-success">CLASS A: F.P</label><input type="number" name="class_a_fp" class="form-control form-control-sm border-success" min="0" placeholder="0"></div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-4"><label class="form-label mb-0 small text-warning">CLASS B: H</label><input type="number" name="class_b_h" class="form-control form-control-sm border-warning" min="0" placeholder="0"></div>
                                            <div class="col-md-4"><label class="form-label mb-0 small text-warning">CLASS B: ID</label><input type="number" name="class_b_id" class="form-control form-control-sm border-warning" min="0" placeholder="0"></div>
                                            <div class="col-md-4"><label class="form-label mb-0 small text-warning">CLASS B: CL-B</label><input type="number" name="class_b_cl_b" class="form-control form-control-sm border-warning" min="0" placeholder="0"></div>
                                        </div>
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
                                        <div class="table-responsive">
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
                                                        <td><input type="number" name="defect_w8[]" class="form-control form-control-sm defect-val" min="0"></td>
                                                        <td><input type="number" name="defect_w9[]" class="form-control form-control-sm defect-val" min="0"></td>
                                                        <td><input type="number" name="defect_w10[]" class="form-control form-control-sm defect-val" min="0"></td>
                                                        <td><input type="number" name="defect_w11[]" class="form-control form-control-sm defect-val" min="0"></td>
                                                        <td><input type="number" name="defect_total[]" class="form-control form-control-sm bg-light fw-bold defect-total" readonly></td>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-darbco" id="saveProductionBtn"><i class="bi bi-save me-2"></i>Save Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<!-- Edit Production Modal -->
    <div class="modal fade" id="editProductionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="index.php?page=production&action=update">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="production_id" id="edit_prod_id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Production Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body bg-light">
                        <div class="alert alert-info py-2 small"><i class="bi bi-info-circle me-1"></i>Box breakdown &amp; defects cannot be edited after creation.</div>
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body row g-3">
                                <h6 class="text-primary border-bottom pb-2 mb-3">General Information</h6>
                                <div class="col-md-3"><label class="form-label fw-semibold">Harvest Date *</label><input type="date" name="harvest_date" id="edit_harvest_date" class="form-control" required></div>
                                <div class="col-md-3"><label class="form-label fw-semibold">Worker / ARB *</label>
                                    <select name="worker_id" id="edit_worker_id" class="form-select" required>
                                        <option value="">-- Select ARB --</option>
                                        <?php foreach ($activeWorkers as $w): ?>
                                        <option value="<?= $w['worker_id'] ?>"><?= htmlspecialchars(($w['sub_code'] ? '[' . $w['sub_code'] . '] ' : '') . $w['first_name'] . ' ' . $w['last_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2"><label class="form-label fw-semibold">Group</label>
                                    <select name="group_number" id="edit_group_number" class="form-select"><option value="">-- Group --</option><option value="1">Group 1</option><option value="3">Group 3</option></select>
                                </div>
                                <div class="col-md-2"><label class="form-label fw-semibold">Block No.</label><input type="text" name="block_number" id="edit_block_number" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label fw-semibold">Stems Cut</label><input type="number" name="stems_cut" id="edit_stems_cut" class="form-control" min="0"></div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body row g-3">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Production Form Data</h6>
                                <div class="col-md-2"><label class="form-label fw-semibold">Hands</label><input type="number" name="hands" id="edit_hands" class="form-control" min="0"></div>
                                <div class="col-md-2"><label class="form-label fw-semibold">Small Hands</label><input type="number" name="small_hands" id="edit_small_hands" class="form-control" min="0"></div>
                                <div class="col-md-2"><label class="form-label fw-semibold text-success">CLASS A: F.P</label><input type="number" name="class_a_fp" id="edit_class_a_fp" class="form-control border-success" min="0"></div>
                                <div class="col-md-2"><label class="form-label fw-semibold text-warning">CLASS B: H</label><input type="number" name="class_b_h" id="edit_class_b_h" class="form-control border-warning" min="0"></div>
                                <div class="col-md-2"><label class="form-label fw-semibold text-warning">CLASS B: ID</label><input type="number" name="class_b_id" id="edit_class_b_id" class="form-control border-warning" min="0"></div>
                                <div class="col-md-2"><label class="form-label fw-semibold text-warning">CLASS B: CL-B</label><input type="number" name="class_b_cl_b" id="edit_class_b_cl_b" class="form-control border-warning" min="0"></div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body row g-3">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Harvest Sheet Fields</h6>
                                <div class="col-md-3"><label class="form-label fw-semibold">Carrier</label><input type="text" name="carrier_name" id="edit_carrier_name" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label fw-semibold">Arrival</label><input type="time" name="arrival_time" id="edit_arrival_time" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label fw-semibold">1st Box Out</label><input type="time" name="first_box_out" id="edit_first_box_out" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label fw-semibold">Last Box Out</label><input type="time" name="last_box_out" id="edit_last_box_out" class="form-control"></div>
                                <div class="col-md-1"><label class="form-label fw-semibold">Week</label><input type="text" name="week_number" id="edit_week_number" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label fw-semibold">Cycle</label><input type="text" name="cycle_code" id="edit_cycle_code" class="form-control"></div>
                                
                                <div class="col-md-2"><label class="form-label fw-semibold">Total Boxes *</label><input type="number" name="boxes_produced" id="edit_boxes_produced" class="form-control" min="0" required></div>
                                <div class="col-md-5"><label class="form-label fw-semibold">Field Location</label><input type="text" name="field_location" id="edit_field_location" class="form-control"></div>
                                <div class="col-md-5"><label class="form-label fw-semibold">Notes</label><input type="text" name="notes" id="edit_notes" class="form-control"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
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
            <td><input type="number" name="defect_w8[]" class="form-control form-control-sm defect-val" min="0"></td>
            <td><input type="number" name="defect_w9[]" class="form-control form-control-sm defect-val" min="0"></td>
            <td><input type="number" name="defect_w10[]" class="form-control form-control-sm defect-val" min="0"></td>
            <td><input type="number" name="defect_w11[]" class="form-control form-control-sm defect-val" min="0"></td>
            <td><input type="number" name="defect_total[]" class="form-control form-control-sm bg-light fw-bold defect-total" readonly></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-defect"><i class="bi bi-x"></i></button></td>
        </tr>`);
    });
    
    const defectTable = document.getElementById('defectTable');
    defectTable?.addEventListener('click', e => {
        if (e.target.closest('.remove-defect')) e.target.closest('tr').remove();
    });
    
    defectTable?.addEventListener('input', e => {
        if (e.target.classList.contains('defect-val')) {
            const row = e.target.closest('tr');
            let total = 0;
            row.querySelectorAll('.defect-val').forEach(input => {
                const val = parseFloat(input.value);
                if (!isNaN(val)) total += val;
            });
            const totalInput = row.querySelector('.defect-total');
            if (totalInput) totalInput.value = total > 0 ? total : '';
        }
    });

    // Edit modal population
    document.querySelectorAll('.btn-edit-prod').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const b = e.currentTarget;
            document.getElementById('edit_prod_id').value = b.dataset.id;
            document.getElementById('edit_harvest_date').value = b.dataset.date;
            document.getElementById('edit_worker_id').value = b.dataset.workerId;
            document.getElementById('edit_boxes_produced').value = b.dataset.boxes;
            document.getElementById('edit_stems_cut').value = b.dataset.stems || '';
            document.getElementById('edit_group_number').value = b.dataset.group || '';
            document.getElementById('edit_block_number').value = b.dataset.block || '';
            document.getElementById('edit_carrier_name').value = b.dataset.carrier || '';
            document.getElementById('edit_arrival_time').value = b.dataset.arrival || '';
            document.getElementById('edit_first_box_out').value = b.dataset.fbo || '';
            document.getElementById('edit_last_box_out').value = b.dataset.lbo || '';
            document.getElementById('edit_week_number').value = b.dataset.week || '';
            document.getElementById('edit_cycle_code').value = b.dataset.cycle || '';
            document.getElementById('edit_field_location').value = b.dataset.loc;
            document.getElementById('edit_notes').value = b.dataset.notes;
            
            // New fields
            document.getElementById('edit_hands').value = b.dataset.hands || '';
            document.getElementById('edit_small_hands').value = b.dataset.smallHands || '';
            document.getElementById('edit_class_a_fp').value = b.dataset.classAFp || '';
            document.getElementById('edit_class_b_h').value = b.dataset.classBH || '';
            document.getElementById('edit_class_b_id').value = b.dataset.classBId || '';
            document.getElementById('edit_class_b_cl_b').value = b.dataset.classBClB || '';
        });
    });
});
</script>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
