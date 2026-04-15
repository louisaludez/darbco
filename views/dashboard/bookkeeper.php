<?php $pageTitle = 'Dashboard'; require_once VIEW_PATH . 'layout/header.php'; ?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">
    <div class="page-header">
        <h1><i class="bi bi-archive me-2 text-success"></i>Bookkeeper Dashboard</h1>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-sm-6">
            <div class="stat-card stat-yellow">
                <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                <div><div class="stat-value"><?= $widgets['low_stock_count'] ?></div>
                     <div class="stat-label">Low Stock Alerts</div></div>
            </div>
        </div>
    </div>
    <div class="text-center mt-5">
        <a href="index.php?page=inventory" class="btn btn-darbco btn-lg">
            <i class="bi bi-archive me-2"></i>Manage Inventory
        </a>
    </div>
</main>
</div>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
