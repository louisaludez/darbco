<?php $pageTitle = 'Dashboard'; require_once VIEW_PATH . 'layout/header.php'; ?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">
    <div class="page-header">
        <h1><i class="bi bi-shield-check me-2 text-success"></i>Finance Officer Dashboard</h1>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-sm-6">
            <div class="stat-card stat-red">
                <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                <div><div class="stat-value"><?= $widgets['pending_payroll'] ?></div>
                     <div class="stat-label">Pending Review</div></div>
            </div>
        </div>
    </div>
    <div class="text-center mt-5">
        <a href="index.php?page=payroll" class="btn btn-darbco btn-lg">
            <i class="bi bi-clipboard-check me-2"></i>Review Payroll Records
        </a>
    </div>
</main>
</div>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
