<?php $pageTitle = 'Dashboard'; require_once VIEW_PATH . 'layout/header.php'; ?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">
    <div class="page-header">
        <h1><i class="bi bi-speedometer2 me-2 text-success"></i>Production Dashboard</h1>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-sm-6">
            <div class="stat-card stat-green">
                <div class="stat-icon"><i class="bi bi-boxes"></i></div>
                <div><div class="stat-value"><?= number_format($widgets['boxes_today']) ?></div>
                     <div class="stat-label">Boxes Today</div></div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="stat-card stat-blue">
                <div class="stat-icon"><i class="bi bi-calendar3"></i></div>
                <div><div class="stat-value"><?= $widgets['monthly_records'] ?></div>
                     <div class="stat-label">Records This Month</div></div>
            </div>
        </div>
    </div>
    <div class="text-center mt-5">
        <a href="index.php?page=production" class="btn btn-darbco btn-lg">
            <i class="bi bi-plus-circle me-2"></i>Enter Production Record
        </a>
    </div>
</main>
</div>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
