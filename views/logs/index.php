<?php
// views/logs/index.php
$pageTitle = 'Audit Logs';
require_once VIEW_PATH . 'layout/header.php';
?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">

    <div class="page-header">
        <h1><i class="bi bi-journal-text me-2 text-success"></i>Audit / Transaction Logs</h1>
    </div>

    <div class="table-card">
        <div class="card-header d-flex gap-3 align-items-center flex-wrap">
            <span>System Activity Log</span>
            <form method="GET" action="index.php" class="d-flex gap-2 ms-auto">
                <input type="hidden" name="page" value="logs">
                <select name="filter" class="form-select form-select-sm" id="logFilter">
                    <option value="">All Actions</option>
                    <option <?= $filter === 'production_insert' ? 'selected' : '' ?> value="production_insert">Production Insert</option>
                    <option <?= $filter === 'inventory_insert'  ? 'selected' : '' ?> value="inventory_insert">Inventory Insert</option>
                    <option <?= $filter === 'inventory_deduction' ? 'selected' : '' ?> value="inventory_deduction">Inventory Deduction</option>
                    <option <?= $filter === 'payroll_compute'   ? 'selected' : '' ?> value="payroll_compute">Payroll Compute</option>
                    <option <?= $filter === 'payroll_review'    ? 'selected' : '' ?> value="payroll_review">Payroll Review</option>
                    <option <?= $filter === 'payroll_approve'   ? 'selected' : '' ?> value="payroll_approve">Payroll Approve</option>
                    <option <?= $filter === 'user_login'        ? 'selected' : '' ?> value="user_login">User Login</option>
                    <option <?= $filter === 'user_create'       ? 'selected' : '' ?> value="user_create">User Create</option>
                </select>
                <button type="submit" class="btn btn-sm btn-darbco" id="filterLogsBtn">Filter</button>
            </form>
        </div>
        <div class="table-responsive p-2">
            <table class="table table-hover darbco-table w-100" id="logsTable">
                <thead>
                    <tr>
                        <th>#</th><th>Timestamp</th><th>User</th>
                        <th>Action</th><th>Reference</th><th>Description</th><th>IP</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($records as $log): ?>
                <tr>
                    <td><?= $log['log_id'] ?></td>
                    <td class="small text-muted"><?= date('M j Y H:i:s', strtotime($log['created_at'])) ?></td>
                    <td><?= htmlspecialchars($log['full_name']) ?></td>
                    <td><span class="badge bg-secondary"><?= str_replace('_', ' ', $log['action_type']) ?></span></td>
                    <td class="small text-muted">
                        <?= $log['reference_table'] ? htmlspecialchars($log['reference_table']) . ' #' . $log['reference_id'] : '—' ?>
                    </td>
                    <td><?= htmlspecialchars($log['description']) ?></td>
                    <td class="small text-muted"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>
</div>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
