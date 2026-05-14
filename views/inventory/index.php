<?php
// views/inventory/index.php
$pageTitle = 'Inventory';
require_once VIEW_PATH . 'layout/header.php';
$role = $_SESSION[SESS_ROLE];
?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">

    <div class="page-header">
        <h1><i class="bi bi-archive me-2 text-success"></i>Inventory Management</h1>
        <?php if ($role === ROLE_BOOKKEEPER): ?>
        <button class="btn btn-darbco" data-bs-toggle="modal" data-bs-target="#addItemModal" id="addItemBtn">
            <i class="bi bi-plus-circle me-2"></i>Add Item
        </button>
        <?php endif; ?>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-success alert-auto-dismiss"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger alert-auto-dismiss"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="table-card">
        <div class="card-header">Stock Registry</div>
        <div class="table-responsive p-2">
            <table class="table table-hover darbco-table w-100" id="inventoryTable">
                <thead>
                    <tr>
                        <th>#</th><th>Item</th><th>Category</th>
                        <th>On Hand</th><th>Unit</th><th>Reorder At</th>
                        <th>Unit Cost</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <?php $isLow = $item['quantity_on_hand'] <= $item['reorder_level']; ?>
                    <tr<?= $isLow ? ' class="low-stock-row"' : '' ?>>
                        <td><?= $item['item_id'] ?></td>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= htmlspecialchars($item['category']) ?></td>
                        <td><strong><?= number_format($item['quantity_on_hand'], 2) ?></strong></td>
                        <td><?= $item['unit'] ?></td>
                        <td><?= number_format($item['reorder_level'], 2) ?></td>
                        <td>₱<?= number_format($item['unit_cost'], 2) ?></td>
                        <td>
                            <?php if ($isLow): ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Low</span>
                            <?php else: ?>
                                <span class="badge bg-success-subtle text-success">OK</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($role === ROLE_BOOKKEEPER): ?>
                            <button class="btn btn-sm btn-outline-primary restock-btn"
                                    data-id="<?= $item['item_id'] ?>"
                                    data-name="<?= htmlspecialchars($item['item_name']) ?>"
                                    data-bs-toggle="modal" data-bs-target="#restockModal">
                                <i class="bi bi-plus-square"></i> Restock
                            </button>
                            <button class="btn btn-sm btn-outline-secondary edit-item-btn"
                                    data-bs-toggle="modal" data-bs-target="#editItemModal"
                                    data-id="<?= $item['item_id'] ?>"
                                    data-name="<?= htmlspecialchars($item['item_name']) ?>"
                                    data-cat="<?= htmlspecialchars($item['category']) ?>"
                                    data-unit="<?= $item['unit'] ?>"
                                    data-reorder="<?= $item['reorder_level'] ?>"
                                    data-cost="<?= $item['unit_cost'] ?>"
                                    data-desc="<?= htmlspecialchars($item['description'] ?? '') ?>">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="index.php?page=inventory&action=store">
                    <?= Csrf::field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="addItemLabel"><i class="bi bi-plus-circle me-2"></i>Add Inventory Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                                <input type="text" name="item_name" class="form-control" required id="new_item_name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category</label>
                                <input type="text" name="category" class="form-control" placeholder="e.g. Fertilizer" id="new_item_category">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Unit</label>
                                <input type="text" name="unit" class="form-control" placeholder="kg / pcs / roll" id="new_item_unit">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Initial Qty</label>
                                <input type="number" name="quantity_on_hand" class="form-control" min="0" step="0.01" id="new_item_qty">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Reorder Level</label>
                                <input type="number" name="reorder_level" class="form-control" min="0" step="0.01" id="new_item_reorder">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Unit Cost (₱)</label>
                                <input type="number" name="unit_cost" class="form-control" min="0" step="0.01" id="new_item_cost">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="2" id="new_item_desc"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-darbco" id="saveItemBtn"><i class="bi bi-save me-2"></i>Save Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Restock Modal -->
    <div class="modal fade" id="restockModal" tabindex="-1" aria-labelledby="restockLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form method="POST" action="index.php?page=inventory&action=restock">
                    <input type="hidden" name="item_id" id="restock_item_id">
                    <?= Csrf::field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="restockLabel">Restock Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="fw-semibold" id="restock_item_name"></p>
                        <label class="form-label">Quantity to Add <span class="text-danger">*</span></label>
                        <input type="number" name="qty_add" id="qty_add" class="form-control" min="0.01" step="0.01" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-darbco" id="confirmRestockBtn">Confirm Restock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Item Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="index.php?page=inventory&action=update">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="item_id" id="edit_item_id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Inventory Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Item Name *</label>
                                <input type="text" name="item_name" id="edit_item_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Category</label>
                                <input type="text" name="category" id="edit_item_category" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Unit</label>
                                <input type="text" name="unit" id="edit_item_unit" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Reorder Level</label>
                                <input type="number" name="reorder_level" id="edit_item_reorder" class="form-control" min="0" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Unit Cost (₱)</label>
                                <input type="number" name="unit_cost" id="edit_item_cost" class="form-control" min="0" step="0.01">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" id="edit_item_desc" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-darbco"><i class="bi bi-save me-2"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.querySelectorAll('.restock-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('restock_item_id').value   = btn.dataset.id;
            document.getElementById('restock_item_name').textContent = btn.dataset.name;
        });
    });

    document.querySelectorAll('.edit-item-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const b = e.currentTarget;
            document.getElementById('edit_item_id').value = b.dataset.id;
            document.getElementById('edit_item_name').value = b.dataset.name;
            document.getElementById('edit_item_category').value = b.dataset.cat;
            document.getElementById('edit_item_unit').value = b.dataset.unit;
            document.getElementById('edit_item_reorder').value = b.dataset.reorder;
            document.getElementById('edit_item_cost').value = b.dataset.cost;
            document.getElementById('edit_item_desc').value = b.dataset.desc;
        });
    });
    </script>

</main>
</div>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
