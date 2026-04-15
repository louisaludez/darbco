<?php
// views/users/index.php
$pageTitle = 'User Management';
require_once VIEW_PATH . 'layout/header.php';
$roleLabels = [
    'admin'               => ['Admin / Manager',    'bg-danger'],
    'production_clerk'    => ['Production Clerk',   'bg-success'],
    'payroll_personnel'   => ['Payroll Personnel',  'bg-primary'],
    'finance_officer'     => ['Finance Officer',    'bg-info text-dark'],
    'bookkeeper'          => ['Bookkeeper',          'bg-secondary'],
];
?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">

    <div class="page-header">
        <h1><i class="bi bi-people me-2 text-success"></i>User Management</h1>
        <button class="btn btn-darbco" data-bs-toggle="modal" data-bs-target="#addUserModal" id="addUserBtn">
            <i class="bi bi-person-plus me-2"></i>Add User
        </button>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-success alert-auto-dismiss"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-danger alert-auto-dismiss"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="table-card">
        <div class="card-header">System Users</div>
        <div class="table-responsive p-2">
            <table class="table table-hover darbco-table w-100" id="usersTable">
                <thead>
                    <tr>
                        <th>#</th><th>Full Name</th><th>Username</th>
                        <th>Email</th><th>Role</th><th>Status</th>
                        <th>Created</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['user_id'] ?></td>
                    <td><?= htmlspecialchars($u['full_name']) ?></td>
                    <td><?= htmlspecialchars($u['username']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <?php [$lbl, $cls] = $roleLabels[$u['role']] ?? [$u['role'], 'bg-secondary']; ?>
                        <span class="badge <?= $cls ?>"><?= $lbl ?></span>
                    </td>
                    <td>
                        <?php if ($u['is_active']): ?>
                            <span class="badge bg-success-subtle text-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= date('M j Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php if ($u['user_id'] != $_SESSION[SESS_USER_ID]): ?>
                        <form method="POST" action="index.php?page=users&action=toggle" class="d-inline">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="user_id"   value="<?= $u['user_id'] ?>">
                            <input type="hidden" name="is_active" value="<?= $u['is_active'] ? 0 : 1 ?>">
                            <button type="submit" class="btn btn-sm btn-outline-<?= $u['is_active'] ? 'warning' : 'success' ?>"
                                    data-confirm="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?> this user?">
                                <i class="bi bi-<?= $u['is_active'] ? 'person-dash' : 'person-check' ?>"></i>
                                <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </form>
                        <?php else: ?>
                        <span class="text-muted small">(you)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="index.php?page=users&action=store">
                    <?= Csrf::field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserLabel">
                            <i class="bi bi-person-plus me-2"></i>Create New User
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control" required id="new_full_name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control" required id="new_username">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required id="new_email">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required minlength="8" id="new_password">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                                <select name="role" class="form-select" required id="new_role">
                                    <option value="production_clerk">Production Clerk</option>
                                    <option value="payroll_personnel">Payroll Personnel</option>
                                    <option value="finance_officer">Finance Officer</option>
                                    <option value="bookkeeper">Bookkeeper</option>
                                    <option value="admin">Admin / Manager</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-darbco" id="saveUserBtn"><i class="bi bi-person-plus me-2"></i>Create User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</main>
</div>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
