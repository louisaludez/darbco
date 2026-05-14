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
                                    data-notes="<?= htmlspecialchars($r['notes'] ?? '') ?>">
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
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Core Fields -->
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

                            <!-- Harvest Sheet Fields -->
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

                            <!-- Per-Row Stem Counts -->
                            <div class="col-12">
                                <hr>
                                <label class="form-label fw-semibold mb-2"><i class="bi bi-grid-1x2 me-1 text-primary"></i>Stem Counts Per Row</label>
                                <div class="row g-2">
                                    <div class="col-md-3"><div class="input-group input-group-sm"><span class="input-group-text">Row 11</span><input type="number" name="stem_row_11" class="form-control" min="0" placeholder="0"></div></div>
                                    <div class="col-md-3"><div class="input-group input-group-sm"><span class="input-group-text">Row 12</span><input type="number" name="stem_row_12" class="form-control" min="0" placeholder="0"></div></div>
                                    <div class="col-md-3"><div class="input-group input-group-sm"><span class="input-group-text">Row 13</span><input type="number" name="stem_row_13" class="form-control" min="0" placeholder="0"></div></div>
                                    <div class="col-md-3"><div class="input-group input-group-sm"><span class="input-group-text">Row 14</span><input type="number" name="stem_row_14" class="form-control" min="0" placeholder="0"></div></div>
                                </div>
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

                            <!-- Box Breakdown -->
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-semibold mb-0"><i class="bi bi-grid-3x2-gap me-1 text-success"></i>Box Breakdown (Class A &amp; B)</label>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-success" id="addClassARow"><i class="bi bi-plus"></i> Class A</button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" id="addClassBRow"><i class="bi bi-plus"></i> Class B</button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered" id="breakdownTable">
                                        <thead class="table-dark">
                                            <tr><th style="width:90px">Class</th><th>Box Spec</th><th style="width:100px">Tally</th><th style="width:100px">Adj.</th><th style="width:110px">Should Be</th><th style="width:40px"></th></tr>
                                        </thead>
                                        <tbody id="breakdownRows"></tbody>
                                    </table>
                                </div>
                                <div class="text-muted small"><i class="bi bi-info-circle me-1"></i>Leave blank to skip breakdown.</div>
                            </div>

                            <!-- Materials Used -->
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-semibold mb-0">Materials Used</label>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="addMaterialRow"><i class="bi bi-plus"></i> Add Material</button>
                                </div>
                                <div id="materialRows">
                                    <div class="material-row row g-2 mb-2">
                                        <div class="col-6">
                                            <select name="item_id[]" class="form-select form-select-sm">
                                                <option value="">-- Select Material --</option>
                                                <?php foreach ($inventoryList as $inv): ?>
                                                <option value="<?= $inv['item_id'] ?>"><?= htmlspecialchars($inv['item_name']) ?> (<?= number_format($inv['quantity_on_hand'], 1) ?> <?= $inv['unit'] ?> avail.)</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-3"><input type="number" name="quantity_used[]" class="form-control form-control-sm" placeholder="Qty used" step="0.01" min="0"></div>
                                        <div class="col-2"><input type="number" name="price[]" class="form-control form-control-sm" placeholder="Price" step="0.01" min="0"></div>
                                        <div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger remove-material-row d-none"><i class="bi bi-trash"></i></button></div>
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
                    <div class="modal-body">
                        <div class="alert alert-info py-2 small"><i class="bi bi-info-circle me-1"></i>Box breakdown &amp; materials cannot be edited after creation.</div>
                        <div class="row g-3">
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
// ── Box spec presets (updated to match paper forms) ──────────
const CLASS_A_SPECS = ['4 Hands','5 Hands','6 Hands','7 Hands','8 Hands','9 Hands','4.7k','7.2k','BCP','SH','F.P','CL-B'];
const CLASS_B_SPECS = ['4/5/6 Hands','Small H / Clusters','F.P','D'];

function buildSpecOptions(specs) {
    return specs.map(s => `<option value="${s}">${s}</option>`).join('');
}
function buildBreakdownRow(cls, specs) {
    const clsLabel = cls === 'A' ? '<span class="badge bg-success">Class A</span>' : '<span class="badge bg-warning text-dark">Class B</span>';
    return `<tr>
        <td>${clsLabel}<input type="hidden" name="bk_class[]" value="${cls}"></td>
        <td><select name="bk_spec[]" class="form-select form-select-sm"><option value="">-- Spec --</option>${buildSpecOptions(specs)}</select></td>
        <td><input type="number" name="bk_tally[]" class="form-control form-control-sm" min="0" placeholder="0"></td>
        <td><input type="number" name="bk_adj[]" class="form-control form-control-sm" min="0" placeholder="0"></td>
        <td><input type="number" name="bk_should[]" class="form-control form-control-sm" min="0" placeholder="0"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-breakdown-row"><i class="bi bi-x"></i></button></td>
    </tr>`;
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('addClassARow')?.addEventListener('click', () => {
        document.getElementById('breakdownRows').insertAdjacentHTML('beforeend', buildBreakdownRow('A', CLASS_A_SPECS));
    });
    document.getElementById('addClassBRow')?.addEventListener('click', () => {
        document.getElementById('breakdownRows').insertAdjacentHTML('beforeend', buildBreakdownRow('B', CLASS_B_SPECS));
    });
    document.getElementById('breakdownRows')?.addEventListener('click', (e) => {
        if (e.target.closest('.remove-breakdown-row')) e.target.closest('tr').remove();
    });

    const matContainer = document.getElementById('materialRows');
    const addMaterialBtn = document.getElementById('addMaterialRow');
    const firstRow = matContainer?.querySelector('.material-row');
    const firstSelect = firstRow?.querySelector('select')?.outerHTML || '';
    addMaterialBtn?.addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'material-row row g-2 mb-2';
        row.innerHTML = `<div class="col-6">${firstSelect}</div><div class="col-3"><input type="number" name="quantity_used[]" class="form-control form-control-sm" placeholder="Qty used" step="0.01" min="0"></div><div class="col-2"><input type="number" name="price[]" class="form-control form-control-sm" placeholder="Price" step="0.01" min="0"></div><div class="col-1"><button type="button" class="btn btn-sm btn-outline-danger remove-material-row"><i class="bi bi-trash"></i></button></div>`;
        matContainer.appendChild(row);
    });
    matContainer?.addEventListener('click', e => {
        if (e.target.closest('.remove-material-row')) e.target.closest('.material-row').remove();
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
        });
    });
});
</script>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
