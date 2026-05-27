<?php
// views/production/daily_reports.php
$pageTitle = 'Daily Production Reports';
require_once VIEW_PATH . 'layout/header.php';
$role = $_SESSION[SESS_ROLE];
?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">

    <div class="page-header">
        <div>
            <h1><i class="bi bi-graph-up me-2 text-success"></i>Production Records</h1>
        </div>
        <?php if ($role === ROLE_PRODUCTION): ?>
        <button class="btn btn-darbco" data-bs-toggle="modal" data-bs-target="#addDRModal">
            <i class="bi bi-plus-circle me-2"></i>New Daily Report
        </button>
        <?php endif; ?>
    </div>

    <!-- Production Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?= !isset($_GET['tab']) || $_GET['tab'] === 'daily_log' ? 'active fw-bold' : '' ?>" href="index.php?page=production&tab=daily_log">
                <i class="bi bi-person-lines-fill me-1"></i> Individual ARB Logs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isset($_GET['tab']) && $_GET['tab'] === 'harvest_parameters' ? 'active fw-bold' : '' ?>" href="index.php?page=production&tab=harvest_parameters">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Harvest Parameters
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isset($_GET['tab']) && $_GET['tab'] === 'daily_reports' ? 'active fw-bold' : '' ?>" href="index.php?page=production&tab=daily_reports">
                <i class="bi bi-graph-up me-1"></i> Daily Reports
            </a>
        </li>
    </ul>

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

    <div class="table-card">
        <div class="card-header">Daily Production Reports (Efficiency Performance)</div>
        <div class="table-responsive p-2">
            <table class="table table-hover darbco-table w-100">
                <thead>
                    <tr>
                        <th>#</th><th>Date</th><th>Week No</th><th>Brand Name</th>
                        <th>Crew Size</th><th>Vol Stems Cut</th><th>B/S</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($drRecords as $r): ?>
                    <tr>
                        <td><?= $r['dpr_id'] ?></td>
                        <td><?= htmlspecialchars($r['report_date']) ?></td>
                        <td><?= htmlspecialchars($r['week_no'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['brand_name'] ?? '—') ?></td>
                        <td><?= $r['crew_size'] ?></td>
                        <td><?= $r['volume_stems_cut'] ?></td>
                        <td><?= htmlspecialchars($r['bs_ratio'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['recorded_by_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addDRModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="index.php?page=production&tab=daily_reports&action=store_dr">
                    <?= Csrf::field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>New Production Report (Efficiency)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body bg-light">
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body row g-3">
                                <h6 class="text-primary border-bottom pb-2 mb-3">General Information</h6>
                                <div class="col-md-3"><label class="form-label">Date *</label><input type="date" name="report_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
                                <div class="col-md-2"><label class="form-label">Week No.</label><input type="text" name="week_no" class="form-control"></div>
                                <div class="col-md-3"><label class="form-label">Brand Name</label><input type="text" name="brand_name" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">Crew Size</label><input type="number" name="crew_size" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">Per Pack Plan</label><input type="text" name="per_pack_plan" class="form-control"></div>
                                
                                <div class="col-md-2"><label class="form-label">First Fruit In</label><input type="time" name="first_fruit_in" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">Last Box Out (Top)</label><input type="time" name="last_box_out" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">First Box Out</label><input type="time" name="first_box_out" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">Vol Stems Cut</label><input type="number" name="volume_stems_cut" class="form-control"></div>
                                <div class="col-md-2"><label class="form-label">B/S Ratio</label><input type="text" name="bs_ratio" class="form-control"></div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="card-body">
                                        <h6 class="text-success border-bottom pb-2 mb-3">CLASS A BOXES PRODUCED PER GROUP</h6>
                                        <table class="table table-sm table-bordered text-center">
                                            <thead class="table-light"><tr><th class="text-start">Box Spec</th><th>GRP 1</th><th>GRP 3</th><th>HMLND</th><th>TOTAL</th></tr></thead>
                                            <tbody>
                                                <?php $classASpecs = ['4 Hands','5 Hands','6 Hands','7 Hands','8 Hands','9 Hands','7.2 K','4.7 K','100L','TRIO 30','BCP 24']; ?>
                                                <?php foreach ($classASpecs as $s): ?>
                                                <tr>
                                                    <td class="text-start"><?= $s ?></td>
                                                    <td><input type="number" name="box_count[]" class="form-control form-control-sm"><input type="hidden" name="box_class[]" value="A"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="GRP 1"></td>
                                                    <td><input type="number" name="box_count[]" class="form-control form-control-sm"><input type="hidden" name="box_class[]" value="A"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="GRP 3"></td>
                                                    <td><input type="number" name="box_count[]" class="form-control form-control-sm"><input type="hidden" name="box_class[]" value="A"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="HMLND"></td>
                                                    <td><input type="number" name="box_count[]" class="form-control form-control-sm bg-light"><input type="hidden" name="box_class[]" value="A"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="TOTAL"></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card shadow-sm border-0 h-100">
                                    <div class="card-body">
                                        <h6 class="text-warning border-bottom pb-2 mb-3 text-dark">CLASS B BOXES PRODUCED PER GROUP</h6>
                                        <table class="table table-sm table-bordered text-center mb-4">
                                            <thead class="table-light"><tr><th class="text-start">Box Spec</th><th>GRP 1</th><th>GRP 3</th><th>HMLND</th><th>TOTAL</th></tr></thead>
                                            <tbody>
                                                <?php $classBSpecs = ['4 Hands','5 Hands','6 Hands','7 Hands','8 Hands','9 Hands','FP']; ?>
                                                <?php foreach ($classBSpecs as $s): ?>
                                                <tr>
                                                    <td class="text-start"><?= $s ?></td>
                                                    <td><input type="number" name="box_count[]" class="form-control form-control-sm"><input type="hidden" name="box_class[]" value="B"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="GRP 1"></td>
                                                    <td><input type="number" name="box_count[]" class="form-control form-control-sm"><input type="hidden" name="box_class[]" value="B"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="GRP 3"></td>
                                                    <td><input type="number" name="box_count[]" class="form-control form-control-sm"><input type="hidden" name="box_class[]" value="B"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="HMLND"></td>
                                                    <td><input type="number" name="box_count[]" class="form-control form-control-sm bg-light"><input type="hidden" name="box_class[]" value="B"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="TOTAL"></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        
                                        <h6 class="text-primary border-bottom pb-2 mb-3">BRAND / MARKET</h6>
                                        <table class="table table-sm table-bordered text-center">
                                            <thead class="table-light"><tr><th class="text-start">Market/Spec</th><th>CLASS A</th><th>CLASS B</th></tr></thead>
                                            <tbody>
                                                <?php foreach ($classBSpecs as $s): ?>
                                                <tr>
                                                    <td class="text-start"><?= $s ?></td>
                                                    <td><input type="number" name="box_count[]" class="form-control form-control-sm"><input type="hidden" name="box_class[]" value="A"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="BRAND"></td>
                                                    <td><input type="number" name="box_count[]" class="form-control form-control-sm"><input type="hidden" name="box_class[]" value="B"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="BRAND"></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-darbco"><i class="bi bi-save me-2"></i>Save Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
</div>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
