<?php
// views/dashboard/admin.php — Admin / Manager dashboard with charts
$pageTitle = 'Admin Dashboard';
require_once VIEW_PATH . 'layout/header.php';

// Extra data for admin charts
require_once MODEL_PATH . 'Report.php';
$reportModel   = new Report();
$last30Days    = $reportModel->getLastNDaysProduction(30);
$payrollPieData = $reportModel->getPayrollStatusDistribution();

// Build chart arrays
$chart30Labels = array_map(fn($r) => date('M j', strtotime($r['harvest_date'])), $last30Days);
$chart30Values = array_map(fn($r) => (int)$r['total_boxes'], $last30Days);
$pieLabels     = array_column($payrollPieData, 'status');
$pieValues     = array_map('intval', array_column($payrollPieData, 'count'));
$pieColors     = ['#f5a623', '#0dcaf0', '#1a7f4b'];
?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1><i class="bi bi-speedometer2 me-2 text-success"></i>Admin Dashboard</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item active">
                        Welcome back, <?= htmlspecialchars($_SESSION[SESS_FULL_NAME]) ?>
                    </li>
                </ol>
            </nav>
        </div>
        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold fs-6">
            <i class="bi bi-calendar3 me-1"></i><?= date('l, F j Y') ?>
        </span>
    </div>

    <!-- ── Stat Cards ─────────────────────────────────────── -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-green">
                <div class="stat-icon"><i class="bi bi-boxes"></i></div>
                <div>
                    <div class="stat-value"><?= number_format($widgets['boxes_today']) ?></div>
                    <div class="stat-label">Boxes Today</div>
                    <div class="stat-trend text-success"><i class="bi bi-arrow-up-short"></i>Today's harvest</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-yellow">
                <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div>
                    <div class="stat-value"><?= $widgets['low_stock_count'] ?></div>
                    <div class="stat-label">Low Stock Alerts</div>
                    <div class="stat-trend <?= $widgets['low_stock_count'] > 0 ? 'text-warning' : 'text-success' ?>">
                        <?= $widgets['low_stock_count'] > 0 ? 'Action needed' : 'All levels OK' ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-red">
                <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                <div>
                    <div class="stat-value"><?= $widgets['pending_payroll'] ?></div>
                    <div class="stat-label">Pending Payroll</div>
                    <div class="stat-trend <?= $widgets['pending_payroll'] > 0 ? 'text-danger' : 'text-success' ?>">
                        <?= $widgets['pending_payroll'] > 0 ? 'Needs approval' : 'All clear' ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="stat-card stat-blue">
                <div class="stat-icon"><i class="bi bi-calendar3"></i></div>
                <div>
                    <div class="stat-value"><?= $widgets['monthly_records'] ?></div>
                    <div class="stat-label">Records This Month</div>
                    <div class="stat-trend text-primary"><?= date('F Y') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Charts Row ─────────────────────────────────────── -->
    <div class="row g-4 mb-4">
        <!-- 30-Day Production Bar Chart -->
        <div class="col-lg-8">
            <div class="chart-card h-100">
                <div class="chart-title">
                    <i class="bi bi-bar-chart-fill text-success"></i>
                    Production — Last 30 Days
                    <span class="ms-auto">
                        <a href="index.php?page=reports&type=production" class="btn btn-sm btn-outline-success btn-sm" id="viewProductionReportBtn">
                            Full Report <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </span>
                </div>
                <div class="chart-container" style="height:260px;">
                    <canvas id="adminProductionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Payroll Status Pie -->
        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="chart-title">
                    <i class="bi bi-pie-chart-fill text-warning"></i>
                    Payroll Status
                    <span class="ms-auto">
                        <a href="index.php?page=reports&type=payroll" class="btn btn-sm btn-outline-warning btn-sm" id="viewPayrollReportBtn">
                            View <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </span>
                </div>
                <div class="chart-container d-flex align-items-center justify-content-center" style="height:260px;">
                    <?php if (!empty($payrollPieData)): ?>
                    <canvas id="payrollPieChart"></canvas>
                    <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="bi bi-pie-chart fs-2 d-block mb-2"></i>
                        No payroll data yet
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Bottom Row ─────────────────────────────────────── -->
    <div class="row g-4">

        <!-- Recent Activity -->
        <div class="col-lg-7">
            <div class="table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-journal-text me-2 text-primary"></i>Recent Activity</span>
                    <a href="index.php?page=logs" class="btn btn-outline-secondary btn-sm" id="viewAllLogsBtn">
                        View All
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($widgets['recent_logs'] as $log): ?>
                            <tr>
                                <td class="text-muted small" style="white-space:nowrap;">
                                    <?= date('H:i', strtotime($log['created_at'])) ?>
                                </td>
                                <td class="fw-600 small"><?= htmlspecialchars($log['full_name']) ?></td>
                                <td>
                                    <span class="badge bg-secondary text-white" style="font-size:.7rem;">
                                        <?= str_replace('_', ' ', $log['action_type']) ?>
                                    </span>
                                </td>
                                <td class="small text-muted"><?= htmlspecialchars(mb_strimwidth($log['description'], 0, 55, '…')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($widgets['recent_logs'])): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-2"></i>No activity yet
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="col-lg-5">
            <div class="table-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="bi bi-exclamation-circle me-2 text-warning"></i>Low Stock Items
                    </span>
                    <a href="index.php?page=inventory" class="btn btn-outline-warning btn-sm" id="manageInventoryBtn">
                        Manage
                    </a>
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($widgets['low_stock_items'] as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-600 small"><?= htmlspecialchars($item['item_name']) ?></div>
                            <div class="text-muted" style="font-size:.72rem;"><?= $item['category'] ?></div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-warning text-dark d-block mb-1">
                                <?= number_format($item['quantity_on_hand'], 1) ?> <?= $item['unit'] ?>
                            </span>
                            <span style="font-size:.68rem;" class="text-muted">
                                Reorder: <?= number_format($item['reorder_level'], 1) ?>
                            </span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($widgets['low_stock_items'])): ?>
                    <li class="list-group-item text-center text-muted py-5">
                        <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
                        All stock levels are OK
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

    </div><!-- /.row -->

</main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 30-day production bar chart
    const prod30Labels = <?= json_encode($chart30Labels) ?>;
    const prod30Values = <?= json_encode($chart30Values) ?>;

    if (document.getElementById('adminProductionChart')) {
        new Chart(document.getElementById('adminProductionChart'), {
            type: 'bar',
            data: {
                labels: prod30Labels,
                datasets: [{
                    label: 'Boxes Produced',
                    data: prod30Values,
                    backgroundColor: 'rgba(26,127,75,.7)',
                    borderColor: '#1a7f4b',
                    borderWidth: 1.5,
                    borderRadius: 5,
                    hoverBackgroundColor: '#1a7f4b'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' }, ticks: { font: { size: 11 } } },
                    x: { grid: { display: false }, ticks: { maxRotation: 45, font: { size: 10 } } }
                }
            }
        });
    }

    // Payroll status pie chart
    if (document.getElementById('payrollPieChart')) {
        const pieLabels = <?= json_encode(array_map(fn($s) => ucwords(str_replace('_',' ',$s)), $pieLabels)) ?>;
        const pieValues = <?= json_encode($pieValues) ?>;
        new Chart(document.getElementById('payrollPieChart'), {
            type: 'doughnut',
            data: {
                labels: pieLabels,
                datasets: [{
                    data: pieValues,
                    backgroundColor: <?= json_encode($pieColors) ?>,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 11 }, padding: 12 }
                    }
                },
                cutout: '60%'
            }
        });
    }
});
</script>

<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
