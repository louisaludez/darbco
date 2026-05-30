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


    <!-- Records Table -->
    <div class="table-card">
        <div class="card-header">Daily Harvest Log</div>
        <div class="table-responsive p-2">
            <table class="table table-hover darbco-table w-100" id="productionTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>BENEFICIARY</th>
                        <th>Blk No.</th>
                        <th>Name Carerro</th>
                        <th>Time Arrival</th>
                        <th>11</th>
                        <th>12</th>
                        <th>13</th>
                        <th>14</th>
                        <th>Total</th>
                        <th>Running tt1</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ($records as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['harvest_date']) ?></td>
                        <td><?= htmlspecialchars($r['worker_name']) ?></td>
                        <td><?= htmlspecialchars($r['block_number'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['carrier_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['arrival_time'] ?? '—') ?></td>
                        <td><?= number_format((int)($r['stem_11'] ?? 0)) ?></td>
                        <td><?= number_format((int)($r['stem_12'] ?? 0)) ?></td>
                        <td><?= number_format((int)($r['stem_13'] ?? 0)) ?></td>
                        <td><?= number_format((int)($r['stem_14'] ?? 0)) ?></td>
                        <td><?= number_format((int)($r['stems_cut'] ?? 0)) ?></td>
                        <td><strong><?= number_format((int)($r['running_total'] ?? 0)) ?></strong></td>
                        <td class="text-end">
                            <?php if ($role === ROLE_PRODUCTION): ?>
                                    <button class="btn btn-sm btn-outline-primary btn-edit-prod"
                                            data-bs-toggle="modal" data-bs-target="#editProductionModal"
                                            data-id="<?= $r['production_id'] ?>"
                                            data-date="<?= htmlspecialchars($r['harvest_date']) ?>"
                                            data-beneficiary="<?= htmlspecialchars($r['worker_name']) ?>"
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
                            <div class="card-body">
                                <div class="row mb-3 align-items-center">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold mb-1">Packing Date <span class="text-danger">*</span></label>
                                        <input type="date" name="harvest_date" class="form-control form-control-sm" required value="<?= date('Y-m-d') ?>">
                                    </div>
                                    <div class="col-md-9 text-end mt-3 mt-md-0">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="addArbRow"><i class="bi bi-plus"></i> Add Row</button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered text-center" id="arbTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="align-middle text-start" style="min-width: 200px;">BENEFICIARY</th>
                                                <th class="align-middle" style="min-width: 100px;">Blk No.</th>
                                                <th class="align-middle" style="min-width: 150px;">Name Carerro</th>
                                                <th class="align-middle" style="min-width: 120px;">Time Arrival</th>
                                                <th class="align-middle" style="min-width: 70px;">11</th>
                                                <th class="align-middle" style="min-width: 70px;">12</th>
                                                <th class="align-middle" style="min-width: 70px;">13</th>
                                                <th class="align-middle" style="min-width: 70px;">14</th>
                                                <th class="align-middle" style="min-width: 80px;">Total</th>
                                                <th class="align-middle" style="min-width: 100px;">Running tt1</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody class="arb-group border-bottom border-dark border-2">
                                            <tr class="parent-row bg-white">
                                                <td>
                                                    <input type="text" name="beneficiary_name[]" class="form-control form-control-sm parent-beneficiary" placeholder="Enter Beneficiary" required>
                                                </td>
                                                <td><input type="text" name="block_number[]" class="form-control form-control-sm parent-block"></td>
                                                <td><input type="text" name="carrier_name[]" class="form-control form-control-sm"></td>
                                                <td><input type="time" name="arrival_time[]" class="form-control form-control-sm"></td>
                                                <td><input type="number" name="stem_row_11[]" class="form-control form-control-sm arb-stem-val" min="0"></td>
                                                <td><input type="number" name="stem_row_12[]" class="form-control form-control-sm arb-stem-val" min="0"></td>
                                                <td><input type="number" name="stem_row_13[]" class="form-control form-control-sm arb-stem-val" min="0"></td>
                                                <td><input type="number" name="stem_row_14[]" class="form-control form-control-sm arb-stem-val" min="0"></td>
                                                <td><input type="number" name="stems_cut[]" class="form-control form-control-sm bg-light fw-bold arb-stem-total" readonly></td>
                                                <td><input type="number" name="running_tt1[]" class="form-control form-control-sm text-primary fw-bold" min="0"></td>
                                                <td>
                                                    <div class="d-flex gap-1 actions-cell">
                                                        <button type="button" class="btn btn-sm btn-outline-success clone-arb" tabindex="-1" title="Add another Carerro for this Beneficiary"><i class="bi bi-plus-square"></i></button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-arb" tabindex="-1" title="Remove Row"><i class="bi bi-x"></i></button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
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
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Packing Date <span class="text-danger">*</span></label>
                                    <input type="date" name="harvest_date" id="edit_harvest_date" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">BENEFICIARY / ARB <span class="text-danger">*</span></label>
                                    <input type="text" name="beneficiary_name" id="edit_beneficiary_name" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Blk No.</label>
                                    <input type="text" name="block_number" id="edit_block_number" class="form-control">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Name Carerro</label>
                                    <input type="text" name="carrier_name" id="edit_carrier_name" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Time Arrival</label>
                                    <input type="time" name="arrival_time" id="edit_arrival_time" class="form-control">
                                </div>

                                <div class="col-12 mt-4">
                                    <h6 class="text-success border-bottom pb-2 mb-3"><i class="bi bi-grid-1x2 me-1"></i>Stem Counts Per Row</h6>
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">11</label>
                                    <input type="number" name="stem_row_11" id="edit_stem_row_11" class="form-control" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">12</label>
                                    <input type="number" name="stem_row_12" id="edit_stem_row_12" class="form-control" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">13</label>
                                    <input type="number" name="stem_row_13" id="edit_stem_row_13" class="form-control" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">14</label>
                                    <input type="number" name="stem_row_14" id="edit_stem_row_14" class="form-control" min="0">
                                </div>

                                <div class="col-md-6 mt-4">
                                    <label class="form-label fw-semibold text-primary">Total</label>
                                    <input type="number" name="stems_cut" id="edit_stems_cut" class="form-control border-primary" min="0">
                                </div>
                                <div class="col-md-6 mt-4">
                                    <label class="form-label fw-semibold text-primary">Running tt1</label>
                                    <input type="number" name="running_tt1" id="edit_running_tt1" class="form-control border-primary" min="0">
                                </div>
                                
                                <!-- Hidden field to satisfy backend constraints -->
                                <input type="hidden" name="boxes_produced" id="edit_boxes_produced" value="0">
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

    // ARB Table Bulk Entry Logic
    document.getElementById('addArbRow')?.addEventListener('click', () => {
        const firstGroup = document.querySelector('.arb-group');
        if (firstGroup) {
            const newGroup = firstGroup.cloneNode(true);
            // Remove any child rows that were copied
            newGroup.querySelectorAll('.child-row').forEach(r => r.remove());
            // Clear inputs in the parent row
            newGroup.querySelectorAll('input').forEach(input => input.value = '');
            // keep the same select options but reset value
            newGroup.querySelectorAll('select').forEach(select => select.value = '');
            document.getElementById('arbTable').appendChild(newGroup);
        }
    });

    const arbTable = document.getElementById('arbTable');
    arbTable?.addEventListener('click', e => {
        if (e.target.closest('.remove-arb')) {
            const row = e.target.closest('tr');
            const tbody = row.closest('.arb-group');
            // If it's a parent row and there are other groups, remove the whole group
            if (row.classList.contains('parent-row')) {
                const groups = document.querySelectorAll('.arb-group');
                if (groups.length > 1) {
                    tbody.remove();
                } else {
                    alert('You must have at least one ARB group.');
                }
            } else {
                // If it's a child row, just remove the row
                row.remove();
            }
        } else if (e.target.closest('.clone-arb')) {
            const row = e.target.closest('tr');
            const tbody = row.closest('.arb-group');
            const newRow = row.cloneNode(true);
            
            newRow.classList.remove('parent-row', 'bg-white');
            newRow.classList.add('child-row', 'bg-light');
            
            // Remove the plus button
            const cloneBtn = newRow.querySelector('.clone-arb');
            if (cloneBtn) cloneBtn.remove();
            
            // Make beneficiary and block readonly
            const benInput = newRow.querySelector('.parent-beneficiary');
            if (benInput) { benInput.readOnly = true; benInput.classList.remove('parent-beneficiary'); }
            
            const blkInput = newRow.querySelector('.parent-block');
            if (blkInput) { blkInput.readOnly = true; blkInput.classList.remove('parent-block'); }
            
            const keepNames = ['beneficiary_name[]', 'block_number[]'];
            newRow.querySelectorAll('input').forEach(input => {
                if (!keepNames.includes(input.name)) {
                    input.value = '';
                }
            });
            tbody.appendChild(newRow);
        }
    });

    arbTable?.addEventListener('input', e => {
        // Sync parent beneficiary and block to children
        if (e.target.classList.contains('parent-beneficiary') || e.target.classList.contains('parent-block')) {
            const tbody = e.target.closest('.arb-group');
            const targetName = e.target.name;
            const targetVal = e.target.value;
            tbody.querySelectorAll(`.child-row input[name="${targetName}"]`).forEach(input => {
                input.value = targetVal;
            });
        }
    });

    arbTable?.addEventListener('input', e => {
        if (e.target.classList.contains('arb-stem-val')) {
            const row = e.target.closest('tr');
            let total = 0;
            row.querySelectorAll('.arb-stem-val').forEach(input => {
                const val = parseFloat(input.value);
                if (!isNaN(val)) total += val;
            });
            const totalInput = row.querySelector('.arb-stem-total');
            if (totalInput) totalInput.value = total > 0 ? total : '';
        }
    });

    // Edit modal population
    document.querySelectorAll('.btn-edit-prod').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const b = e.currentTarget;
            document.getElementById('edit_prod_id').value = b.dataset.id;
            document.getElementById('edit_harvest_date').value = b.dataset.date;
            document.getElementById('edit_beneficiary_name').value = b.dataset.beneficiary || '';
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
