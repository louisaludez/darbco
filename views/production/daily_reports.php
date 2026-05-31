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
        <li class="nav-item">
            <a class="nav-link <?= isset($_GET['tab']) && $_GET['tab'] === 'daily_boxes_per_group' ? 'active fw-bold' : '' ?>" href="index.php?page=production&tab=daily_boxes_per_group">
                <i class="bi bi-box-seam me-1"></i> Daily Boxes Per Group
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isset($_GET['tab']) && $_GET['tab'] === 'daily_production_per_beneficiary' ? 'active fw-bold' : '' ?>" href="index.php?page=production&tab=daily_production_per_beneficiary">
                <i class="bi bi-person-badge me-1"></i> Daily Production Per Beneficiary
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
                        <div class="accordion" id="drAccordion">
                        
                            <!-- GROUP 1: General Information -->
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button fw-bold text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGenInfo">
                                        <i class="bi bi-info-circle me-2"></i> General Information
                                    </button>
                                </h2>
                                <div id="collapseGenInfo" class="accordion-collapse collapse show" data-bs-parent="#drAccordion">
                                    <div class="accordion-body bg-light row g-3">
                                        <div class="col-xl-7 col-lg-6">
                                            <div class="row g-3">
                                                <div class="col-md-4 col-sm-6"><label class="form-label">Date *</label><input type="date" name="report_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
                                                <div class="col-md-4 col-sm-6"><label class="form-label">Week No.</label><input type="text" name="week_no" class="form-control"></div>
                                                <div class="col-md-4 col-sm-6"><label class="form-label">Brand Name</label><input type="text" name="brand_name" class="form-control"></div>
                                                <div class="col-md-4 col-sm-6"><label class="form-label">Crew Size</label><input type="number" name="crew_size" class="form-control"></div>
                                                <div class="col-md-4 col-sm-6"><label class="form-label">Per Pack Plan</label><input type="text" name="per_pack_plan" class="form-control"></div>
                                                <div class="col-md-4 col-sm-6"><label class="form-label">First Fruit In</label><input type="time" name="first_fruit_in" class="form-control"></div>
                                                <div class="col-md-4 col-sm-6"><label class="form-label">Last Fruit In</label><input type="time" name="last_fruit_in" class="form-control"></div>
                                                <div class="col-md-4 col-sm-6"><label class="form-label">First Box Out</label><input type="time" name="first_box_out" class="form-control"></div>
                                                <div class="col-md-4 col-sm-6"><label class="form-label">Last Box Out (Top)</label><input type="time" name="last_box_out" class="form-control"></div>
                                                <div class="col-md-4 col-sm-6"><label class="form-label">Vol Stems Cut</label><input type="number" name="volume_stems_cut" class="form-control"></div>
                                                <div class="col-md-4 col-sm-6"><label class="form-label">B/S Ratio</label><input type="text" name="bs_ratio" class="form-control"></div>
                                            </div>
                                        </div>
                                        <div class="col-xl-5 col-lg-6">
                                            <h6 class="text-secondary fw-bold" style="font-size:0.9rem;">EFFICIENCY PERFORMANCE</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered text-center align-middle" style="min-width: 350px;">
                                                    <thead class="table-light"><tr><th>NO.</th><th>IN</th><th>OUT</th><th>MANHOURS</th></tr></thead>
                                                    <tbody>
                                                        <tr>
                                                        <td><input type="text" class="form-control form-control-sm"></td>
                                                        <td><input type="time" class="form-control form-control-sm"></td>
                                                        <td><input type="time" class="form-control form-control-sm"></td>
                                                        <td><input type="number" step="0.1" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control form-control-sm"></td>
                                                        <td><input type="time" class="form-control form-control-sm"></td>
                                                        <td><input type="time" class="form-control form-control-sm"></td>
                                                        <td><input type="number" step="0.1" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control form-control-sm"></td>
                                                        <td><input type="time" class="form-control form-control-sm"></td>
                                                        <td><input type="time" class="form-control form-control-sm"></td>
                                                        <td><input type="number" step="0.1" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td><input type="text" class="form-control form-control-sm"></td>
                                                        <td><input type="time" class="form-control form-control-sm"></td>
                                                        <td><input type="time" class="form-control form-control-sm"></td>
                                                        <td><input type="number" step="0.1" class="form-control form-control-sm"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- GROUP 2: CLASS A -->
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold text-success" type="button" data-bs-toggle="collapse" data-bs-target="#collapseClassA">
                                        CLASS A BOXES PRODUCED
                                    </button>
                                </h2>
                                <div id="collapseClassA" class="accordion-collapse collapse" data-bs-parent="#drAccordion">
                                    <div class="accordion-body bg-light row g-3">
                                        <div class="col-md-10">
                                            <h6 class="text-secondary mb-2" style="font-size:0.9rem;">BOXES PRODUCED PER GROUP</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered text-center align-middle" id="classATable">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th rowspan="2" class="text-start align-middle" style="min-width: 100px;">Box Spec</th>
                                                            <th>GRP 1</th>
                                                            <th>GRP 3</th>
                                                            <th>HMLND</th>
                                                            <th rowspan="2" class="align-middle" style="width:80px; font-size: 0.8rem;">TOTAL BOXES<br>PROD.</th>
                                                        </tr>
                                                        
                                                    </thead>
                                                    <tbody>
                                                        <?php $classASpecs = ['4 Hands', '5 Hands', '6 Hands', '7 Hands', '8 Hands', '9 Hands', '7.2 K', '4.7 K', '100L', '33', '30', 'TRIO 30', '24', '28', 'BCP 24', '25', '27']; ?>
                                                        <?php foreach ($classASpecs as $s): ?>
                                                        <tr>
                                                            <td class="text-start fw-semibold"><?= $s ?></td>
                                                            <!-- GRP 1 -->
                                                            <td><input type="hidden" name="box_class[]" value="A"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="GROUP 1"><input type="hidden" name="box_tally[]" value="0"><input type="hidden" name="box_adj[]" value="0"><input type="number" name="box_should[]" class="form-control form-control-sm grp1-should"></td>
                                                            <!-- GRP 3 -->
                                                            <td><input type="hidden" name="box_class[]" value="A"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="GROUP 3"><input type="hidden" name="box_tally[]" value="0"><input type="hidden" name="box_adj[]" value="0"><input type="number" name="box_should[]" class="form-control form-control-sm grp3-should"></td>
                                                            <!-- HMLND -->
                                                            <td><input type="hidden" name="box_class[]" value="A"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="HMLND"><input type="hidden" name="box_tally[]" value="0"><input type="hidden" name="box_adj[]" value="0"><input type="number" name="box_should[]" class="form-control form-control-sm hmlnd-should"></td>
                                                            <!-- TOTAL -->
                                                            <td><input type="number" class="form-control form-control-sm bg-light row-total-a fw-bold" readonly></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        <tr class="fw-bold bg-light">
                                                            <td class="text-end">TOTAL=</td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g1-should" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g3-should" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-hm-should" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold text-danger grand-total-a" readonly></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <h6 class="text-secondary mb-2" style="font-size:0.9rem;">BRAND / MARKET</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered text-center align-middle">
                                                    <thead class="table-light"><tr><th class="text-start" style="height: 57px;">Box Spec</th><th>TOTAL=</th></tr></thead>
                                                    <tbody>
                                                        <?php foreach ($classASpecs as $s): ?>
                                                        <tr>
                                                            <td class="text-start"><?= $s ?></td>
                                                            <td><input type="hidden" name="box_class[]" value="A"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="BRAND"><input type="hidden" name="box_adj[]" value="0"><input type="hidden" name="box_should[]" value="0"><input type="number" name="box_tally[]" class="form-control form-control-sm"></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        <tr class="fw-bold bg-light">
                                                            <td class="text-end">TOTAL=</td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold text-danger" readonly></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- GROUP 3: CLASS B -->
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold text-warning text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseClassB">
                                        CLASS B BOXES PRODUCED
                                    </button>
                                </h2>
                                <div id="collapseClassB" class="accordion-collapse collapse" data-bs-parent="#drAccordion">
                                    <div class="accordion-body bg-light row g-3">
                                        <div class="col-md-10">
                                            <h6 class="text-secondary mb-2" style="font-size:0.9rem;">BOXES PRODUCED PER GROUP</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered text-center align-middle" id="classBTable">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th rowspan="2" class="text-start align-middle" style="min-width: 100px;">Box Spec</th>
                                                            <th>GRP 1</th>
                                                            <th>GRP 3</th>
                                                            <th>HMLND</th>
                                                            <th rowspan="2" class="align-middle" style="width:80px; font-size: 0.8rem;">TOTAL BOXES<br>PROD.</th>
                                                        </tr>
                                                        
                                                    </thead>
                                                    <tbody>
                                                        <?php $classBSpecs = ['4 Hands', '5 Hands', '6 Hands', '7 Hands', '8 Hands', '9 Hands', 'FP']; ?>
                                                        <?php foreach ($classBSpecs as $s): ?>
                                                        <tr>
                                                            <td class="text-start fw-semibold"><?= $s ?></td>
                                                            <!-- GRP 1 -->
                                                            <td><input type="hidden" name="box_class[]" value="B"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="GROUP 1"><input type="hidden" name="box_tally[]" value="0"><input type="hidden" name="box_adj[]" value="0"><input type="number" name="box_should[]" class="form-control form-control-sm grp1-should"></td>
                                                            <!-- GRP 3 -->
                                                            <td><input type="hidden" name="box_class[]" value="B"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="GROUP 3"><input type="hidden" name="box_tally[]" value="0"><input type="hidden" name="box_adj[]" value="0"><input type="number" name="box_should[]" class="form-control form-control-sm grp3-should"></td>
                                                            <!-- HMLND -->
                                                            <td><input type="hidden" name="box_class[]" value="B"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="HMLND"><input type="hidden" name="box_tally[]" value="0"><input type="hidden" name="box_adj[]" value="0"><input type="number" name="box_should[]" class="form-control form-control-sm hmlnd-should"></td>
                                                            <!-- TOTAL -->
                                                            <td><input type="number" class="form-control form-control-sm bg-light row-total-b fw-bold" readonly></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        <tr class="fw-bold bg-light">
                                                            <td class="text-end">TOTAL=</td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g1-should" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g3-should" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-hm-should" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold text-danger grand-total-b" readonly></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <h6 class="text-secondary mb-2" style="font-size:0.9rem;">BRAND / MARKET</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered text-center align-middle">
                                                    <thead class="table-light"><tr><th class="text-start" style="height: 57px;">Box Spec</th><th>TOTAL=</th></tr></thead>
                                                    <tbody>
                                                        <?php foreach ($classBSpecs as $s): ?>
                                                        <tr>
                                                            <td class="text-start"><?= $s ?></td>
                                                            <td><input type="hidden" name="box_class[]" value="B"><input type="hidden" name="box_spec[]" value="<?= $s ?>"><input type="hidden" name="box_group[]" value="BRAND"><input type="hidden" name="box_adj[]" value="0"><input type="hidden" name="box_should[]" value="0"><input type="number" name="box_tally[]" class="form-control form-control-sm"></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        <tr class="fw-bold bg-light">
                                                            <td class="text-end">TOTAL=</td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold text-danger" readonly></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- GROUP 4: SUMMARY PACKING SPEC -->
                            <div class="accordion-item border-0 shadow-sm mb-3">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed fw-bold text-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSummary">
                                        SUMMARY : PACKING SPEC & BOXES ALL-IN
                                    </button>
                                </h2>
                                <div id="collapseSummary" class="accordion-collapse collapse" data-bs-parent="#drAccordion">
                                    <div class="accordion-body bg-light row g-3">
<div class="col-md-4">
                                            <table class="table table-sm table-bordered text-center align-middle mb-2">
                                                <tbody>
                                                    <tr>
                                                        <td class="fw-bold bg-light text-start" style="width: 50%;">Hands</td>
                                                        <td style="width: 50%;"><input type="number" name="summary_hands_main" class="form-control form-control-sm text-center"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold bg-light text-start">SP / CP</td>
                                                        <td><input type="number" name="summary_sp_cp" class="form-control form-control-sm text-center"></td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                            <table class="table table-sm table-bordered text-center align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr><th colspan="2" class="text-start">CLASS B</th></tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="fw-bold bg-light text-start" style="width: 50%; padding-left: 15px;">Hands</td>
                                                        <td style="width: 50%;"><input type="number" name="summary_class_b_hands" class="form-control form-control-sm text-center"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold bg-light text-start" style="padding-left: 15px;">CL/SML</td>
                                                        <td><input type="number" name="summary_class_b_sml" class="form-control form-control-sm text-center"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold bg-light text-start" style="padding-left: 15px;">TOTAL</td>
                                                        <td><input type="number" name="summary_class_b_total" class="form-control form-control-sm bg-light fw-bold text-center" readonly></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="col-md-4">
                                            <table class="table table-sm table-bordered text-center align-middle mb-0 h-100">
                                                <thead class="table-light">
                                                    <tr><th colspan="2">Small Hands</th></tr>
                                                </thead>
                                                <tbody>

                                                    <tr>
                                                        <td class="bg-light text-start">789</td>
                                                        <td><input type="number" name="summary_789" class="form-control form-control-sm text-center"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="bg-light text-start">7.2 k</td>
                                                        <td><input type="number" name="summary_72k" class="form-control form-control-sm text-center"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="bg-light text-start">4.7 k</td>
                                                        <td><input type="number" name="summary_47k" class="form-control form-control-sm text-center"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold bg-light text-start">TOTAL</td>
                                                        <td><input type="number" name="summary_hands_total" class="form-control form-control-sm bg-light fw-bold text-center" readonly></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="col-md-4">
                                            <table class="table table-sm table-bordered text-center align-middle mb-0 h-100">
                                                <thead class="table-light">
                                                    <tr><th colspan="2">BOXES ALL - IN</th></tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="text-start fw-bold bg-light" style="font-size: 0.85rem; width: 60%;">HANDS, SP & SML H =</td>
                                                        <td style="width: 40%;"><input type="number" name="summary_hands_sp_sml" class="form-control form-control-sm fw-bold bg-light text-center" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start fw-bold bg-light">CLASS B =</td>
                                                        <td><input type="number" name="summary_class_b_grand" class="form-control form-control-sm fw-bold bg-light text-center" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start fw-bold bg-light text-danger">TOTAL</td>
                                                        <td><input type="number" name="summary_grand_total" class="form-control form-control-sm bg-light fw-bold text-danger text-center" readonly></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    function calculateMatrixTotals(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const calcRow = (row) => {
            const g1ShouldInput = row.querySelector('.grp1-should');
            const g3ShouldInput = row.querySelector('.grp3-should');
            const hmShouldInput = row.querySelector('.hmlnd-should');
            
            const g1Should = parseInt(g1ShouldInput?.value || 0);
            const g3Should = parseInt(g3ShouldInput?.value || 0);
            const hmShould = parseInt(hmShouldInput?.value || 0);

            // Total Boxes Produced
            const rowTotalInput = row.querySelector('[class*="row-total"]');
            if (rowTotalInput) {
                if (g1ShouldInput?.value !== '' || g3ShouldInput?.value !== '' || hmShouldInput?.value !== '') {
                    rowTotalInput.value = g1Should + g3Should + hmShould;
                } else {
                    rowTotalInput.value = '';
                }
            }
        };

        const calcAll = () => {
            const rows = table.querySelectorAll('tbody tr:not(.fw-bold)');
            
            let totalG1Should = 0;
            let totalG3Should = 0;
            let totalHmShould = 0;
            let grandTotal = 0;

            rows.forEach(row => {
                calcRow(row);
                
                totalG1Should += parseInt(row.querySelector('.grp1-should')?.value || 0);
                totalG3Should += parseInt(row.querySelector('.grp3-should')?.value || 0);
                totalHmShould += parseInt(row.querySelector('.hmlnd-should')?.value || 0);
                grandTotal += parseInt(row.querySelector('[class*="row-total"]')?.value || 0);
            });
            
            const setTotal = (selector, val) => {
                const el = table.querySelector(selector);
                if (el) el.value = val || '';
            };

            setTotal('.total-g1-should', totalG1Should);
            setTotal('.total-g3-should', totalG3Should);
            setTotal('.total-hm-should', totalHmShould);
            setTotal('[class*="grand-total"]', grandTotal);
        };

        table.addEventListener('input', (e) => {
            if (e.target.tagName === 'INPUT' && e.target.type === 'number') {
                calcAll();
            }
        });
        
        calcAll();
    }

    calculateMatrixTotals('classATable');
    calculateMatrixTotals('classBTable');
    
    function calculateBrandTotals(containerSelector) {
        const containers = document.querySelectorAll(containerSelector);
        containers.forEach(container => {
            const calcAll = () => {
                let grandTotal = 0;
                const rows = container.querySelectorAll('tbody tr:not(.fw-bold)');
                rows.forEach(row => {
                    const val = parseInt(row.querySelector('input[name="box_tally[]"]')?.value || 0);
                    grandTotal += val;
                });
                const totalInput = container.querySelector('tbody tr.fw-bold input');
                if (totalInput) {
                    totalInput.value = grandTotal || '';
                }
            };
            
            container.addEventListener('input', (e) => {
                if (e.target.tagName === 'INPUT') {
                    calcAll();
                }
            });
        });
    }
    
    // Updated selector to match the new col-md-2 table
    calculateBrandTotals('.col-md-2 table');

    function calculatePackingSummary() {
        const getVal = (name) => parseInt(document.querySelector(`input[name="${name}"]`)?.value || 0);
        const setVal = (name, val) => {
            const el = document.querySelector(`input[name="${name}"]`);
            if (el) el.value = val || '';
        };

        const calcSummary = () => {
            // Left Table Totals
            const handsTotal = getVal('summary_small_hands') + getVal('summary_789') + getVal('summary_72k') + getVal('summary_47k');
            setVal('summary_hands_total', handsTotal);
            
            const classBTotal = getVal('summary_class_b_hands') + getVal('summary_class_b_sml');
            setVal('summary_class_b_total', classBTotal);
            
            // Right Table Total
            const handsSpSml = getVal('summary_hands_main') + handsTotal + getVal('summary_sp_cp');
            setVal('summary_hands_sp_sml', handsSpSml);

            const bGrand = classBTotal;
            setVal('summary_class_b_grand', bGrand);
            
            const grandTotal = handsSpSml + bGrand;
            setVal('summary_grand_total', grandTotal);
        };

        const inputs = document.querySelectorAll('input[name^="summary_"]');
        inputs.forEach(input => {
            input.addEventListener('input', calcSummary);
        });
        
        calcSummary();
    }
    
    calculatePackingSummary();
});
</script>
</div>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
