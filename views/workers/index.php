<?php
// views/workers/index.php
$pageTitle = 'Workers Registry';
require_once VIEW_PATH . 'layout/header.php';
$role = $_SESSION[SESS_ROLE];
?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">

    <div class="page-header">
        <h1><i class="bi bi-people-fill me-2 text-success"></i>Worker Registry</h1>
        <?php if ($role === ROLE_ADMIN): ?>
        <button class="btn btn-darbco" data-bs-toggle="modal" data-bs-target="#addWorkerModal">
            <i class="bi bi-person-plus me-2"></i>Register Worker
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
        <div class="card-header">Farm Workers & Harvest Teams</div>
        <div class="table-responsive p-2">
            <table class="table table-hover darbco-table w-100" id="workersTable">
                <thead>
                    <tr>
                        <th>Sub Code</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <?php if ($role === ROLE_ADMIN): ?>
                        <th class="text-end">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($workers as $w): ?>
                    <tr>
                        <td><span class="badge bg-dark font-monospace"><?= htmlspecialchars($w['sub_code'] ?? '—') ?></span></td>
                        <td class="fw-600"><?= htmlspecialchars($w['first_name']) ?></td>
                        <td class="fw-600"><?= htmlspecialchars($w['last_name']) ?></td>
                        <td><?= htmlspecialchars($w['contact_number'] ?? '—') ?></td>
                        <td>
                            <?php if ($w['is_active']): ?>
                                <span class="badge bg-success-subtle text-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($role === ROLE_ADMIN): ?>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary btn-edit-worker"
                                    data-bs-toggle="modal" data-bs-target="#editWorkerModal"
                                    data-id="<?= $w['worker_id'] ?>"
                                    data-sub="<?= htmlspecialchars($w['sub_code'] ?? '') ?>"
                                    data-first="<?= htmlspecialchars($w['first_name']) ?>"
                                    data-last="<?= htmlspecialchars($w['last_name']) ?>"
                                    data-contact="<?= htmlspecialchars($w['contact_number']) ?>"
                                    data-active="<?= $w['is_active'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="index.php?page=workers&action=toggle" class="d-inline">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="worker_id" value="<?= $w['worker_id'] ?>">
                                <button type="submit" class="btn btn-sm <?= $w['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" title="Toggle Status">
                                    <i class="bi <?= $w['is_active'] ? 'bi-person-dash' : 'bi-person-check' ?>"></i>
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

    <!-- Add Worker Modal -->
    <?php if ($role === ROLE_ADMIN): ?>
    <div class="modal fade" id="addWorkerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="index.php?page=workers&action=store">
                    <?= Csrf::field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Register Worker</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">ARB Sub Code</label>
                                <input type="text" name="sub_code" class="form-control font-monospace"
                                       placeholder="e.g. 042" maxlength="20">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">First Name *</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Last Name *</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" placeholder="e.g. 09123456789">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-darbco"><i class="bi bi-save me-2"></i>Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Worker Modal -->
    <div class="modal fade" id="editWorkerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="index.php?page=workers&action=update">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="worker_id" id="edit_worker_id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Worker</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">ARB Sub Code</label>
                                <input type="text" name="sub_code" id="edit_worker_sub" class="form-control font-monospace"
                                       maxlength="20" placeholder="e.g. 042">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">First Name *</label>
                                <input type="text" name="first_name" id="edit_worker_first" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Last Name *</label>
                                <input type="text" name="last_name" id="edit_worker_last" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Contact Number</label>
                                <input type="text" name="contact_number" id="edit_worker_contact" class="form-control">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="edit_worker_active" value="1">
                                    <label class="form-check-label fw-semibold" for="edit_worker_active">Active Worker (Can be assigned to new productions)</label>
                                </div>
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
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.btn-edit-worker').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const b = e.currentTarget;
                document.getElementById('edit_worker_id').value      = b.dataset.id;
                document.getElementById('edit_worker_sub').value     = b.dataset.sub;
                document.getElementById('edit_worker_first').value   = b.dataset.first;
                document.getElementById('edit_worker_last').value    = b.dataset.last;
                document.getElementById('edit_worker_contact').value = b.dataset.contact;
                document.getElementById('edit_worker_active').checked = b.dataset.active == '1';
            });
        });
    });
    </script>
    <?php endif; ?>

</main>
</div>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
