<?php
// views/production/index.php
$pageTitle = 'Production Records';
require_once VIEW_PATH . 'layout/header.php';
?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">

    <div class="page-header">
        <div>
            <h1><i class="bi bi-boxes me-2 text-success"></i>Production Records</h1>
        </div>
        <button class="btn btn-darbco" data-bs-toggle="modal" data-bs-target="#addProductionModal" id="addProductionBtn">
            <i class="bi bi-plus-circle me-2"></i>New Record
        </button>
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
                        <th>#</th>
                        <th>Date</th>
                        <th>Worker</th>
                        <th>Boxes</th>
                        <th>Location</th>
                        <th>Recorded By</th>
                        <th>Created</th>
                        <?php if ($role === ROLE_ADMIN): ?>
                        <th class="text-end">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($records as $r): ?>
                    <tr>
                        <td><?= $r['production_id'] ?></td>
                        <td><?= htmlspecialchars($r['harvest_date']) ?></td>
                        <td><?= htmlspecialchars($r['worker_name']) ?></td>
                        <td><strong><?= number_format($r['boxes_produced']) ?></strong></td>
                        <td><?= htmlspecialchars($r['field_location'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['recorded_by_name']) ?></td>
                        <td class="text-muted small"><?= date('M j Y', strtotime($r['created_at'])) ?></td>
                        <?php if ($role === ROLE_ADMIN): ?>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary btn-edit-prod"
                                    data-bs-toggle="modal" data-bs-target="#editProductionModal"
                                    data-id="<?= $r['production_id'] ?>"
                                    data-date="<?= htmlspecialchars($r['harvest_date']) ?>"
                                    data-worker-id="<?= $r['worker_id'] ?>"
                                    data-boxes="<?= $r['boxes_produced'] ?>"
                                    data-loc="<?= htmlspecialchars($r['field_location'] ?? '') ?>"
                                    data-notes="<?= htmlspecialchars($r['notes'] ?? '') ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="index.php?page=production&action=delete" class="d-inline"
                                  onsubmit="return confirm('WARNING: Deleting this record will delete its payroll history AND restore used materials to inventory. This cannot be undone. Proceed?');">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="production_id" value="<?= $r['production_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Production Modal -->
    <div class="modal fade" id="addProductionModal" tabindex="-1" aria-labelledby="addProductionLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="index.php?page=production&action=store">
                    <?= Csrf::field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductionLabel">
                            <i class="bi bi-plus-circle me-2"></i>New Production Record
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Harvest Date <span class="text-danger">*</span></label>
                                <input type="date" name="harvest_date" id="harvest_date" class="form-control" required
                                       value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Boxes Produced <span class="text-danger">*</span></label>
                                <input type="number" name="boxes_produced" id="boxes_produced" class="form-control"
                                       min="0" required placeholder="e.g. 120">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Worker / Team <span class="text-danger">*</span></label>
                                <select name="worker_id" id="worker_id" class="form-select" required>
                                    <option value="">-- Select Worker --</option>
                                    <?php foreach ($activeWorkers as $w): ?>
                                    <option value="<?= $w['worker_id'] ?>">
                                        <?= htmlspecialchars($w['first_name'] . ' ' . $w['last_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Field Location</label>
                                <input type="text" name="field_location" id="field_location" class="form-control"
                                       placeholder="e.g. Block A, Farm 2">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea name="notes" id="notes" class="form-control" rows="2"
                                          placeholder="Optional notes..."></textarea>
                            </div>

                            <!-- Materials Used -->
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-semibold mb-0">Materials Used</label>
                                    <button type="button" class="btn btn-sm btn-outline-success" id="addMaterialRow">
                                        <i class="bi bi-plus"></i> Add Material
                                    </button>
                                </div>
                                <div id="materialRows">
                                    <div class="material-row row g-2 mb-2">
                                        <div class="col-7">
                                            <select name="item_id[]" class="form-select form-select-sm">
                                                <option value="">-- Select Material --</option>
                                                <?php foreach ($inventoryList as $inv): ?>
                                                <option value="<?= $inv['item_id'] ?>">
                                                    <?= htmlspecialchars($inv['item_name']) ?>
                                                    (<?= number_format($inv['quantity_on_hand'], 1) ?> <?= $inv['unit'] ?> avail.)
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-4">
                                            <input type="number" name="quantity_used[]" class="form-control form-control-sm"
                                                   placeholder="Qty used" step="0.01" min="0">
                                        </div>
                                        <div class="col-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-material-row d-none">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-darbco" id="saveProductionBtn">
                            <i class="bi bi-save me-2"></i>Save Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Production Modal -->
    <div class="modal fade" id="editProductionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="index.php?page=production&action=update">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="production_id" id="edit_prod_id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Production Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2 small">
                            <i class="bi bi-info-circle me-1"></i>Materials cannot be edited after creation. To change materials, delete this record and recreate it.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Harvest Date *</label>
                                <input type="date" name="harvest_date" id="edit_harvest_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Boxes Produced *</label>
                                <input type="number" name="boxes_produced" id="edit_boxes_produced" class="form-control" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Worker / Team *</label>
                                <select name="worker_id" id="edit_worker_id" class="form-select" required>
                                    <option value="">-- Select Worker --</option>
                                    <?php foreach ($activeWorkers as $w): ?>
                                    <option value="<?= $w['worker_id'] ?>">
                                        <?= htmlspecialchars($w['first_name'] . ' ' . $w['last_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                    <!-- Inactive workers will need an option here if they own a past record. For now, active only. -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Field Location</label>
                                <input type="text" name="field_location" id="edit_field_location" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
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
    document.querySelectorAll('.btn-edit-prod').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const b = e.currentTarget;
            document.getElementById('edit_prod_id').value = b.dataset.id;
            document.getElementById('edit_harvest_date').value = b.dataset.date;
            document.getElementById('edit_worker_id').value = b.dataset.workerId;
            document.getElementById('edit_boxes_produced').value = b.dataset.boxes;
            document.getElementById('edit_field_location').value = b.dataset.loc;
            document.getElementById('edit_notes').value = b.dataset.notes;
        });
    });
});
</script>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
