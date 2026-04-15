<?php
// views/reports/index.php
$pageTitle = 'Reports';
require_once VIEW_PATH . 'layout/header.php';

// JSON for chart.js
$chartLabelsJson = json_encode($chartLabels);
$chartValuesJson = json_encode($chartValues);

// Monthly chart data
$monthlyLabels = array_column($monthlyChart, 'month_name');
$monthlyValues = array_map('intval', array_column($monthlyChart, 'total_boxes'));
$monthlyLabelsJson = json_encode($monthlyLabels);
$monthlyValuesJson = json_encode($monthlyValues);
?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1><i class="bi bi-bar-chart-line me-2 text-success"></i>Reports &amp; Analytics</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="index.php?page=dashboard" class="text-success">Dashboard</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol>
            </nav>
        </div>
        <!-- Print / Export -->
        <div class="d-flex gap-2">
            <?php
            $exportUrl = "index.php?page=reports&type=" . urlencode($reportType) .
                         "&period=" . urlencode($period) .
                         ($period === 'custom' ? "&date_from=" . urlencode($dateFrom) . "&date_to=" . urlencode($dateTo) : '') .
                         "&export=csv";
            ?>
            <a href="<?= $exportUrl ?>" class="btn btn-outline-success btn-sm" id="exportCsvBtn">
                <i class="bi bi-filetype-csv me-1"></i>Export CSV
            </a>
            <button class="btn btn-outline-secondary btn-sm" onclick="window.print()" id="printReportBtn">
                <i class="bi bi-printer me-1"></i>Print
            </button>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="index.php" class="row g-3 align-items-end" id="reportFilterForm">
                <input type="hidden" name="page" value="reports">

                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Report Type</label>
                    <select name="type" class="form-select form-select-sm" id="reportType">
                        <option value="production" <?= $reportType === 'production' ? 'selected' : '' ?>>📦 Production Report</option>
                        <option value="payroll"    <?= $reportType === 'payroll'    ? 'selected' : '' ?>>💰 Payroll Report</option>
                        <option value="inventory"  <?= $reportType === 'inventory'  ? 'selected' : '' ?>>📦 Inventory Usage</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Period</label>
                    <select name="period" class="form-select form-select-sm" id="reportPeriod">
                        <?php foreach (REPORT_PERIODS as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= $period === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 custom-range-field" <?= $period !== 'custom' ? 'style="display:none"' : '' ?>>
                    <label class="form-label fw-semibold small">Date From</label>
                    <input type="date" name="date_from" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($_GET['date_from'] ?? $dateFrom) ?>" id="dateFrom">
                </div>

                <div class="col-md-2 custom-range-field" <?= $period !== 'custom' ? 'style="display:none"' : '' ?>>
                    <label class="form-label fw-semibold small">Date To</label>
                    <input type="date" name="date_to" class="form-control form-control-sm"
                           value="<?= htmlspecialchars($_GET['date_to'] ?? $dateTo) ?>" id="dateTo">
                </div>

                <div class="col-md-auto ms-auto">
                    <button type="submit" class="btn btn-darbco btn-sm px-4" id="generateReportBtn">
                        <i class="bi bi-search me-1"></i>Generate
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ────────────────────────────────────────────────────
         PRODUCTION REPORT
    ──────────────────────────────────────────────────────── -->
    <?php if ($reportType === 'production'): ?>

    <!-- Summary Stat Row -->
    <?php
    $totalBoxes   = array_sum(array_column($reportData, 'total_boxes'));
    $totalDays    = count($reportData);
    $avgPerDay    = $totalDays ? round($totalBoxes / $totalDays) : 0;
    ?>
    <div class="row g-4 mb-4">
        <div class="col-sm-4">
            <div class="stat-card stat-green">
                <div class="stat-icon"><i class="bi bi-boxes"></i></div>
                <div>
                    <div class="stat-value"><?= number_format($totalBoxes) ?></div>
                    <div class="stat-label">Total Boxes</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card stat-blue">
                <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                <div>
                    <div class="stat-value"><?= $totalDays ?></div>
                    <div class="stat-label">Harvest Days</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="stat-card stat-yellow">
                <div class="stat-icon"><i class="bi bi-graph-up"></i></div>
                <div>
                    <div class="stat-value"><?= number_format($avgPerDay) ?></div>
                    <div class="stat-label">Avg Boxes / Day</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Daily Production Chart -->
        <div class="col-lg-8">
            <div class="chart-card h-100">
                <div class="chart-title">
                    <i class="bi bi-bar-chart-fill text-success"></i>
                    Daily Production — <?= htmlspecialchars(date('M j', strtotime($dateFrom))) ?> to <?= date('M j Y', strtotime($dateTo)) ?>
                </div>
                <div class="chart-container" style="height:280px;">
                    <canvas id="dailyProductionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Overview Chart -->
        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="chart-title">
                    <i class="bi bi-calendar3 text-primary"></i>
                    Monthly Overview <?= date('Y') ?>
                </div>
                <div class="chart-container" style="height:280px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Top Workers -->
        <div class="col-lg-5">
            <div class="table-card h-100">
                <div class="card-header"><i class="bi bi-trophy me-2 text-warning"></i>Top Workers</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>#</th><th>Worker</th><th>Total Boxes</th><th>Days</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($topWorkers as $i => $w): ?>
                        <tr>
                            <td>
                                <?php if ($i === 0): ?>
                                    <span class="badge bg-warning text-dark">🥇</span>
                                <?php elseif ($i === 1): ?>
                                    <span class="badge bg-secondary">🥈</span>
                                <?php elseif ($i === 2): ?>
                                    <span class="badge" style="background:#cd7f32">🥉</span>
                                <?php else: ?>
                                    <?= $i + 1 ?>
                                <?php endif; ?>
                            </td>
                            <td class="fw-600"><?= htmlspecialchars($w['worker_name']) ?></td>
                            <td><?= number_format($w['total_boxes']) ?></td>
                            <td class="text-muted"><?= $w['harvest_days'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($topWorkers)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No data in this period.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Daily Production Table -->
        <div class="col-lg-7">
            <div class="table-card h-100">
                <div class="card-header"><i class="bi bi-table me-2"></i>Daily Breakdown</div>
                <div class="table-responsive p-2">
                    <table class="table table-hover darbco-table w-100" id="productionReportTable">
                        <thead><tr><th>Date</th><th>Total Boxes</th><th>Records</th></tr></thead>
                        <tbody>
                        <?php foreach ($reportData as $r): ?>
                        <tr>
                            <td><?= date('l, M j Y', strtotime($r['harvest_date'])) ?></td>
                            <td><strong><?= number_format($r['total_boxes']) ?></strong></td>
                            <td><?= $r['record_count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js init -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const labels = <?= $chartLabelsJson ?>;
        const values = <?= $chartValuesJson ?>;

        new Chart(document.getElementById('dailyProductionChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Boxes Produced',
                    data: values,
                    backgroundColor: 'rgba(26,127,75,.75)',
                    borderColor: '#1a7f4b',
                    borderWidth: 1.5,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });

        const mLabels = <?= $monthlyLabelsJson ?>;
        const mVals   = <?= $monthlyValuesJson ?>;
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: mLabels,
                datasets: [{
                    label: 'Boxes',
                    data: mVals,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,.1)',
                    tension: .4,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#0d6efd'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
    </script>

    <!-- ────────────────────────────────────────────────────
         PAYROLL REPORT
    ──────────────────────────────────────────────────────── -->
    <?php elseif ($reportType === 'payroll'): ?>

    <!-- Summary boxes -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="report-summary-box">
                <div class="label">Total Gross Pay</div>
                <div class="amount">₱<?= number_format((float)($payrollSummary['total_gross'] ?? 0), 2) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="report-summary-box" style="background:linear-gradient(135deg,#c04040,#9b1818);">
                <div class="label">Total Deductions</div>
                <div class="amount">₱<?= number_format((float)($payrollSummary['total_deductions'] ?? 0), 2) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="report-summary-box" style="background:linear-gradient(135deg,#0d5c88,#0d3f6e);">
                <div class="label">Total Net Pay</div>
                <div class="amount">₱<?= number_format((float)($payrollSummary['total_net'] ?? 0), 2) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-pie-chart text-success"></i>Status Distribution</div>
                <div class="chart-container" style="height:130px;">
                    <canvas id="payrollStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll Detail Table -->
    <div class="table-card">
        <div class="card-header">Payroll Records — <?= htmlspecialchars(date('M j', strtotime($dateFrom))) ?> to <?= date('M j Y', strtotime($dateTo)) ?></div>
        <div class="table-responsive p-2">
            <table class="table table-hover darbco-table w-100" id="payrollReportTable">
                <thead>
                    <tr>
                        <th>#</th><th>Worker</th><th>Date</th><th>Boxes</th>
                        <th>Rate</th><th>Gross</th><th>Deductions</th><th>Net Pay</th><th>Status</th><th>Approved By</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($reportData as $p): ?>
                <?php
                $statusMap = [
                    'pending_review' => ['badge-pending',  'Pending'],
                    'reviewed'       => ['badge-reviewed', 'Reviewed'],
                    'approved'       => ['badge-approved', 'Approved'],
                ];
                [$cls, $lbl] = $statusMap[$p['status']] ?? ['bg-secondary text-white', $p['status']];
                ?>
                <tr>
                    <td><?= $p['payroll_id'] ?></td>
                    <td class="fw-600"><?= htmlspecialchars($p['worker_name']) ?></td>
                    <td><?= $p['harvest_date'] ?></td>
                    <td><?= number_format($p['boxes_produced']) ?></td>
                    <td>₱<?= number_format($p['rate_per_box'], 2) ?></td>
                    <td>₱<?= number_format($p['gross_pay'], 2) ?></td>
                    <td>₱<?= number_format($p['deductions'], 2) ?></td>
                    <td class="fw-600 text-success">₱<?= number_format($p['net_pay'], 2) ?></td>
                    <td><span class="badge <?= $cls ?>"><?= $lbl ?></span></td>
                    <td class="text-muted small"><?= htmlspecialchars($p['approved_by_name'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        new Chart(document.getElementById('payrollStatusChart'), {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Reviewed', 'Approved'],
                datasets: [{
                    data: [
                        <?= (int)($payrollSummary['count_pending']  ?? 0) ?>,
                        <?= (int)($payrollSummary['count_reviewed'] ?? 0) ?>,
                        <?= (int)($payrollSummary['count_approved'] ?? 0) ?>
                    ],
                    backgroundColor: ['#f5a623','#0dcaf0','#1a7f4b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } },
                cutout: '60%'
            }
        });
    });
    </script>

    <!-- ────────────────────────────────────────────────────
         INVENTORY USAGE REPORT
    ──────────────────────────────────────────────────────── -->
    <?php elseif ($reportType === 'inventory'): ?>

    <?php $totalUsageValue = 0; foreach ($inventoryUsage as $u) $totalUsageValue += $u['total_used']; ?>
    <div class="row g-4 mb-4">
        <div class="col-sm-4">
            <div class="stat-card stat-blue">
                <div class="stat-icon"><i class="bi bi-archive"></i></div>
                <div>
                    <div class="stat-value"><?= count($inventoryUsage) ?></div>
                    <div class="stat-label">Items Used</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Usage Table -->
        <div class="col-lg-7">
            <div class="table-card">
                <div class="card-header"><i class="bi bi-table me-2"></i>Material Usage Breakdown</div>
                <div class="table-responsive p-2">
                    <table class="table table-hover darbco-table w-100" id="inventoryReportTable">
                        <thead>
                            <tr><th>Item</th><th>Category</th><th>Total Used</th><th>Unit</th><th>Current Stock</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($inventoryUsage as $u): ?>
                        <?php $isLow = $u['current_stock'] <= $u['reorder_level']; ?>
                        <tr>
                            <td class="fw-600"><?= htmlspecialchars($u['item_name']) ?></td>
                            <td><?= htmlspecialchars($u['category']) ?></td>
                            <td><?= number_format($u['total_used'], 2) ?></td>
                            <td><?= $u['unit'] ?></td>
                            <td><?= number_format($u['current_stock'], 2) ?></td>
                            <td>
                                <?php if ($isLow): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Low</span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success">OK</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($inventoryUsage)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No inventory usage recorded in this period.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Horizontal bar chart -->
        <div class="col-lg-5">
            <div class="chart-card h-100">
                <div class="chart-title"><i class="bi bi-bar-chart-horizontal text-primary"></i>Usage by Item</div>
                <div class="chart-container" style="min-height:250px;">
                    <canvas id="inventoryUsageChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const labels = <?= json_encode(array_column($inventoryUsage, 'item_name')) ?>;
        const values = <?= json_encode(array_map('floatval', array_column($inventoryUsage, 'total_used'))) ?>;
        new Chart(document.getElementById('inventoryUsageChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Qty Used',
                    data: values,
                    backgroundColor: 'rgba(13,110,253,.7)',
                    borderRadius: 6
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.05)' } },
                    y: { grid: { display: false } }
                }
            }
        });
    });
    </script>

    <?php endif; ?>

</main>
</div>

<script>
// Show/hide custom date range fields
document.getElementById('reportPeriod')?.addEventListener('change', function () {
    document.querySelectorAll('.custom-range-field').forEach(el => {
        el.style.display = this.value === 'custom' ? '' : 'none';
    });
});
</script>

<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
