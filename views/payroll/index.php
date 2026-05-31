<?php
// views/payroll/index.php
$pageTitle = 'Payroll';
require_once VIEW_PATH . 'layout/header.php';
$role = $_SESSION[SESS_ROLE];
?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">

    <div class="page-header">
        <h1><i class="bi bi-cash-stack me-2 text-success"></i>Payroll Management</h1>
        <?php if ($role === ROLE_PAYROLL): ?>
        <button class="btn btn-darbco" data-bs-toggle="modal" data-bs-target="#computePayrollModal" id="computePayrollBtn">
            <i class="bi bi-calculator me-2"></i>Compute Harvest Proceeds
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
        <div class="card-header">Payroll Records</div>
        <div class="table-responsive p-2">
            <table class="table table-hover darbco-table w-100" id="payrollTable">
                <thead>
                    <tr>
                        <th>#</th><th>Worker</th><th>Date</th><th>Week</th><th>Boxes</th>
                        <th>Gross</th><th>Deductions</th><th>Net Pay</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($records as $p): ?>
                <tr>
                    <td><?= $p['payroll_id'] ?></td>
                    <td><?= htmlspecialchars($p['worker_name']) ?></td>
                    <td><?= $p['harvest_date'] ?></td>
                    <td><?= htmlspecialchars($p['week_number'] ?? '—') ?></td>
                    <td><?= number_format($p['boxes_produced']) ?></td>
                    <td>₱<?= number_format($p['gross_pay'], 2) ?></td>
                    <td>₱<?= number_format($p['deductions'], 2) ?></td>
                    <td><strong>₱<?= number_format($p['net_pay'], 2) ?></strong></td>
                    <td>
                        <?php
                        $statusMap = [
                            'pending_review' => ['badge-pending',  'Pending Review'],
                            'reviewed'       => ['badge-reviewed', 'Reviewed'],
                            'approved'       => ['badge-approved', 'Approved'],
                        ];
                        [$cls, $lbl] = $statusMap[$p['status']] ?? ['bg-secondary', $p['status']];
                        ?>
                        <span class="badge <?= $cls ?>"><?= $lbl ?></span>
                    </td>
                    <td>
                        <?php if ($p['status'] === 'pending_review' && $role === ROLE_FINANCE): ?>
                        <button class="btn btn-sm btn-outline-info review-btn" data-bs-toggle="modal" data-bs-target="#reviewModal"
                                data-id="<?= $p['payroll_id'] ?>" data-worker="<?= htmlspecialchars($p['worker_name']) ?>"
                                data-net="<?= number_format($p['net_pay'], 2) ?>">
                            <i class="bi bi-clipboard-check"></i> Review
                        </button>
                        <?php endif; ?>
                        <?php if ($p['status'] === 'reviewed' && $role === ROLE_ADMIN): ?>
                        <form method="POST" action="index.php?page=payroll&action=approve" class="d-inline">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="payroll_id" value="<?= $p['payroll_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-darbco"><i class="bi bi-check-circle"></i> Approve</button>
                        </form>
                        <?php endif; ?>
                        <?php if ($p['status'] === 'approved'): ?>
                        <a href="index.php?page=payroll&action=print&id=<?= $p['payroll_id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-printer"></i> Slip
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Compute Payroll Modal (Full Harvest Proceeds) -->
    <?php if ($role === ROLE_PAYROLL): ?>
    <div class="modal fade" id="computePayrollModal" tabindex="-1" aria-labelledby="computePayrollLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form method="POST" action="index.php?page=payroll&action=compute">
                    <?= Csrf::field() ?>
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="computePayrollLabel"><i class="bi bi-calculator me-2"></i>Compute Harvest Proceeds</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="max-height:75vh; overflow-y:auto;">
                        <div class="accordion" id="payrollAccordion">
                            
                            <!-- Section 1: General Info -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingInfo">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseInfo">
                                        <i class="bi bi-person-lines-fill me-2"></i> 1. General Information
                                    </button>
                                </h2>
                                <div id="collapseInfo" class="accordion-collapse collapse show" data-bs-parent="#payrollAccordion">
                                    <div class="accordion-body bg-light p-3">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Worker / ARB Name <span class="text-danger">*</span></label>
                                                <select name="worker_id" class="form-select" required>
                                                    <option value="">-- Select --</option>
                                                    <?php foreach ($activeWorkers as $w): ?>
                                                    <option value="<?= $w['worker_id'] ?>"><?= htmlspecialchars(($w['sub_code'] ? '[' . $w['sub_code'] . '] ' : '') . $w['first_name'] . ' ' . $w['last_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Area</label>
                                                <input type="text" name="area" class="form-control" placeholder="Farm area">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Harvest Date <span class="text-danger">*</span></label>
                                                <input type="date" name="harvest_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                                            </div>
                                        </div>
                                        <div class="row g-3 mt-1">
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Week</label>
                                                <input type="text" name="week_number" class="form-control" placeholder="7">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Cycle</label>
                                                <input type="text" name="cycle_code" class="form-control" placeholder="R2-2 C28">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Forex Rate</label>
                                                <input type="number" name="forex_rate" class="form-control" step="0.0001" value="1.00">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Rate/Box (₱)</label>
                                                <input type="number" name="rate_per_box" class="form-control" step="0.01" value="<?= defined('DEFAULT_RATE_PER_BOX') ? DEFAULT_RATE_PER_BOX : 0 ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Prod. Record</label>
                                                <select name="production_id" class="form-select">
                                                    <option value="">-- Optional link --</option>
                                                    <?php foreach ($productionList as $prod): ?>
                                                    <option value="<?= $prod['production_id'] ?>">#<?= $prod['production_id'] ?> — <?= htmlspecialchars($prod['worker_name']) ?> (<?= $prod['harvest_date'] ?>)</option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row g-3 mt-1">
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Period Start <span class="text-danger">*</span></label>
                                                <input type="date" name="period_start" class="form-control" required value="<?= date('Y-m-01') ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Period End <span class="text-danger">*</span></label>
                                                <input type="date" name="period_end" class="form-control" required value="<?= date('Y-m-t') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Box Specs -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingBox">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBox">
                                        <i class="bi bi-box-seam me-2"></i> 2. Box Specs & Gross Proceeds
                                    </button>
                                </h2>
                                <div id="collapseBox" class="accordion-collapse collapse" data-bs-parent="#payrollAccordion">
                                    <div class="accordion-body bg-light p-2">
                                        <div class="d-flex justify-content-end mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-success bg-white" onclick="addBoxSpecRow()"><i class="bi bi-plus"></i> Add Spec</button>
                                        </div>
                                        <table class="table table-sm table-bordered bg-white mb-2" id="boxSpecTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:120px">Week</th>
                                                    <th>Box Spec</th>
                                                    <th style="width:100px">Boxes (phy.)</th>
                                                    <th style="width:100px">Price (₱)</th>
                                                    <th style="width:100px">Forex</th>
                                                    <th style="width:130px">Amount</th>
                                                    <th style="width:40px"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="boxSpecRows"></tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="text-end fw-bold">Total Gross Proceeds:</td>
                                                    <td class="fw-bold text-success fs-6" id="grossTotal">₱ 0.00</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        <div id="weekSubtotals" class="mt-2 p-2 border rounded bg-white" style="display:none;">
                                            <h6 class="fw-bold mb-1">Subtotal per Week</h6>
                                            <div id="weekSubtotalsContainer" class="row g-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Materials Withdrawal -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingMat">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMat">
                                        <i class="bi bi-tools me-2"></i> 3. Materials Withdrawal
                                    </button>
                                </h2>
                                <div id="collapseMat" class="accordion-collapse collapse" data-bs-parent="#payrollAccordion">
                                    <div class="accordion-body bg-light p-2">
                                        <div class="d-flex justify-content-end mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary bg-white" onclick="addDedRow('matRows', 'material')"><i class="bi bi-plus"></i> Add Material</button>
                                        </div>
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:40px" class="text-center">#</th>
                                                    <th>Materials Withdrawal</th>
                                                    <th style="width:80px">Qty.</th>
                                                    <th style="width:80px">Unit</th>
                                                    <th style="width:100px">Price (₱)</th>
                                                    <th style="width:120px">Amount (₱)</th>
                                                    <th style="width:40px"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="matRows" class="ded-group-rows numbered-rows"></tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="text-end fw-bold">Total - Materials:</td>
                                                    <td class="fw-bold text-danger" id="matTotal">₱ 0.00</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4: A. Contributions -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingA">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseA">
                                        <i class="bi bi-piggy-bank me-2"></i> 4. A. Contributions
                                    </button>
                                </h2>
                                <div id="collapseA" class="accordion-collapse collapse" data-bs-parent="#payrollAccordion">
                                    <div class="accordion-body bg-light p-2">
                                        <div class="d-flex justify-content-end mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-info bg-white" onclick="addContribRow()"><i class="bi bi-plus"></i> Add Contribution</button>
                                        </div>
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:40px" class="text-center">#</th>
                                                    <th>A. Contributions</th>
                                                    <th style="width:120px">Prev. Amt. (₱)</th>
                                                    <th style="width:120px">Current (₱)</th>
                                                    <th style="width:120px">R-Total (₱)</th>
                                                    <th style="width:40px"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="contribRows" class="numbered-rows"></tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3" class="text-end fw-bold">Total - Contributions (Current):</td>
                                                    <td class="fw-bold text-danger" id="contribTotal">₱ 0.00</td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 5: B. Direct Labor Cost -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingB">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseB">
                                        <i class="bi bi-people me-2"></i> 5. B. Direct Labor Cost
                                    </button>
                                </h2>
                                <div id="collapseB" class="accordion-collapse collapse" data-bs-parent="#payrollAccordion">
                                    <div class="accordion-body bg-light p-2">
                                        <div class="d-flex justify-content-end mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary bg-white" onclick="addDedRow('laborRows', 'labor')"><i class="bi bi-plus"></i> Add Labor</button>
                                        </div>
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:40px" class="text-center">#</th>
                                                    <th>B. Direct Labor Cost</th>
                                                    <th style="width:80px">Qty</th>
                                                    <th style="width:80px">Unit</th>
                                                    <th style="width:100px">U-Cost (₱)</th>
                                                    <th style="width:120px">Amount (₱)</th>
                                                    <th style="width:40px"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="laborRows" class="ded-group-rows numbered-rows"></tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="text-end fw-bold">Total - Direct Labor:</td>
                                                    <td class="fw-bold text-danger" id="laborTotal">₱ 0.00</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 6: C. Personal Account -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingC">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseC">
                                        <i class="bi bi-person-badge me-2"></i> 6. C. Personal Account
                                    </button>
                                </h2>
                                <div id="collapseC" class="accordion-collapse collapse" data-bs-parent="#payrollAccordion">
                                    <div class="accordion-body bg-light p-2">
                                        <div class="d-flex justify-content-end mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark bg-white" onclick="addPersonalRow()"><i class="bi bi-plus"></i> Add Account</button>
                                        </div>
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:40px" class="text-center">#</th>
                                                    <th>C. Personal Account</th>
                                                    <th style="width:100px">Previous</th>
                                                    <th style="width:100px">Current</th>
                                                    <th style="width:100px">Total</th>
                                                    <th style="width:100px">Deduction</th>
                                                    <th style="width:100px">End. Balance</th>
                                                    <th style="width:40px"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="personalRows" class="numbered-rows"></tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="text-end fw-bold">Total - Personal Account (Deduction):</td>
                                                    <td class="fw-bold text-danger" id="personalTotal">₱ 0.00</td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 7: D. Other Deductions -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingD">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseD">
                                        <i class="bi bi-dash-circle me-2"></i> 7. D. Other Deductions (Charge to Guaranteed Income)
                                    </button>
                                </h2>
                                <div id="collapseD" class="accordion-collapse collapse" data-bs-parent="#payrollAccordion">
                                    <div class="accordion-body bg-light p-2">
                                        <div class="d-flex justify-content-end mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger bg-white" onclick="addOtherDedRow()"><i class="bi bi-plus"></i> Add Deduction</button>
                                        </div>
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:40px" class="text-center">#</th>
                                                    <th>D. Other Deductions</th>
                                                    <th style="width:150px">Amount (₱)</th>
                                                    <th style="width:40px"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="otherRows" class="numbered-rows"></tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="2" class="text-end fw-bold">Total - Other Deductions:</td>
                                                    <td class="fw-bold text-danger" id="otherTotal">₱ 0.00</td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 8: Production Data -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingProd">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProd">
                                        <i class="bi bi-bar-chart-fill me-2"></i> 8. Production Data
                                    </button>
                                </h2>
                                <div id="collapseProd" class="accordion-collapse collapse" data-bs-parent="#payrollAccordion">
                                    <div class="accordion-body bg-light p-2">
                                        <div class="table-responsive bg-white border rounded">
                                            <table class="table table-sm table-bordered mb-0 align-middle text-center">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th class="text-start" colspan="4">Production Data</th>
                                                    </tr>
                                                    <tr>
                                                        <th class="text-start" style="width:30%">Boxes</th>
                                                        <th><input type="text" class="form-control form-control-sm text-center fw-bold" placeholder="Week 1 Label (e.g. Week 7)" id="prod_week1_lbl" oninput="updateProdLabels()"></th>
                                                        <th><input type="text" class="form-control form-control-sm text-center fw-bold" placeholder="Week 2 Label (e.g. Week 8)" id="prod_week2_lbl" oninput="updateProdLabels()"></th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="table-secondary"><td class="text-start fw-bold" colspan="4">CLASS A (13.5k)</td></tr>
                                                    <tr>
                                                        <td class="text-start ps-4">Big hands</td>
                                                        <td><input type="number" name="bh_w1" class="form-control form-control-sm text-center prod-input" value="0" min="0"></td>
                                                        <td><input type="number" name="bh_w2" class="form-control form-control-sm text-center prod-input" value="0" min="0"></td>
                                                        <td><input type="number" id="bh_tot" class="form-control form-control-sm text-center bg-light" readonly value="0"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start ps-4">Small hands</td>
                                                        <td><input type="number" name="sh_w1" class="form-control form-control-sm text-center prod-input" value="0" min="0"></td>
                                                        <td><input type="number" name="sh_w2" class="form-control form-control-sm text-center prod-input" value="0" min="0"></td>
                                                        <td><input type="number" id="sh_tot" class="form-control form-control-sm text-center bg-light" readonly value="0"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start ps-4">CP's</td>
                                                        <td><input type="number" name="cp_w1" class="form-control form-control-sm text-center prod-input" value="0" min="0"></td>
                                                        <td><input type="number" name="cp_w2" class="form-control form-control-sm text-center prod-input" value="0" min="0"></td>
                                                        <td><input type="number" id="cp_tot" class="form-control form-control-sm text-center bg-light" readonly value="0"></td>
                                                    </tr>
                                                    <tr class="fw-bold">
                                                        <td class="text-start ps-4">Total Class A</td>
                                                        <td><input type="number" id="cl_a_w1" class="form-control form-control-sm text-center bg-light" readonly value="0"></td>
                                                        <td><input type="number" id="cl_a_w2" class="form-control form-control-sm text-center bg-light" readonly value="0"></td>
                                                        <td><input type="number" id="cl_a_tot" class="form-control form-control-sm text-center bg-light" readonly value="0"></td>
                                                    </tr>
                                                    <tr class="table-secondary"><td class="text-start fw-bold" colspan="4">Class B (13.5k)</td></tr>
                                                    <tr>
                                                        <td class="text-start ps-4">Class B</td>
                                                        <td><input type="number" name="cb_w1" class="form-control form-control-sm text-center prod-input" value="0" min="0"></td>
                                                        <td><input type="number" name="cb_w2" class="form-control form-control-sm text-center prod-input" value="0" min="0"></td>
                                                        <td><input type="number" id="cb_tot" class="form-control form-control-sm text-center bg-light" readonly value="0"></td>
                                                    </tr>
                                                    <tr class="table-warning fw-bold">
                                                        <td class="text-start">Total Boxes</td>
                                                        <td><input type="number" id="box_w1" class="form-control form-control-sm text-center bg-light fw-bold" readonly value="0"></td>
                                                        <td><input type="number" id="box_w2" class="form-control form-control-sm text-center bg-light fw-bold" readonly value="0"></td>
                                                        <td><input type="number" id="box_tot" class="form-control form-control-sm text-center bg-light fw-bold" readonly value="0"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start">Stems Cut</td>
                                                        <td><input type="number" name="stems_w1" class="form-control form-control-sm text-center prod-input" value="0" min="0"></td>
                                                        <td><input type="number" name="stems_w2" class="form-control form-control-sm text-center prod-input" value="0" min="0"></td>
                                                        <td><input type="number" id="stems_tot" class="form-control form-control-sm text-center bg-light" readonly value="0"></td>
                                                    </tr>
                                                    <tr class="fw-bold">
                                                        <td class="text-start">BS Ratio</td>
                                                        <td><input type="text" id="bs_w1" class="form-control form-control-sm text-center bg-light" readonly value="0.00"></td>
                                                        <td><input type="text" id="bs_w2" class="form-control form-control-sm text-center bg-light" readonly value="0.00"></td>
                                                        <td><input type="text" id="bs_tot" class="form-control form-control-sm text-center bg-light" readonly value="0.00"></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <!-- Hidden Inputs for Controller Compatibility -->
                                        <input type="hidden" name="class_a_big_hands" id="hidden_bh" value="0">
                                        <input type="hidden" name="class_a_small_hands" id="hidden_sh" value="0">
                                        <input type="hidden" name="class_a_cps" id="hidden_cp" value="0">
                                        <input type="hidden" name="class_b" id="hidden_cb" value="0">
                                        <input type="hidden" name="boxes_produced" id="hidden_boxes" value="0">
                                        <input type="hidden" name="stems_cut" id="hidden_stems" value="0">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section 9: Summary -->
                            <div class="accordion-item border-success">
                                <h2 class="accordion-header" id="headingSum">
                                    <button class="accordion-button bg-success text-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSum">
                                        <i class="bi bi-file-earmark-text me-2"></i> 9. Final Payroll Summary
                                    </button>
                                </h2>
                                <div id="collapseSum" class="accordion-collapse collapse show" data-bs-parent="#payrollAccordion">
                                    <div class="accordion-body bg-light p-3">
                                        <div class="row">
                                            <div class="col-md-7 mx-auto">
                                                <table class="table table-sm table-borderless fs-6">
                                                    <tbody>
                                                        <tr><td class="fw-bold">GROSS PROCEEDS</td><td class="text-end fw-bold text-success" id="sum_gross">₱ 0.00</td></tr>
                                                        <tr><td class="fw-bold text-decoration-underline" colspan="2">DEDUCTIONS:</td></tr>
                                                        <tr><td class="ps-4">A. CONTRIBUTION</td><td class="text-end text-danger" id="sum_contrib">₱ 0.00</td></tr>
                                                        <tr><td class="ps-4">B. DIRECT LABOR COST</td><td class="text-end text-danger" id="sum_labor">₱ 0.00</td></tr>
                                                        <tr><td class="ps-4">C. PERSONAL ACCOUNT</td><td class="text-end text-danger" id="sum_personal">₱ 0.00</td></tr>
                                                        <tr><td class="ps-4">MATERIALS WITHDRAWAL</td><td class="text-end text-danger" id="sum_mat">₱ 0.00</td></tr>
                                                        <tr class="border-top border-dark"><td class="fw-bold text-end">Total Deductions</td><td class="text-end fw-bold text-danger" id="sum_total_ded">₱ 0.00</td></tr>
                                                        
                                                        <tr class="border-top border-dark mt-2 pt-2"><td class="fw-bold fs-5">NET PROCEEDS</td><td class="text-end fw-bold fs-5" id="sum_net_proceeds">₱ 0.00</td></tr>
                                                        
                                                        <tr>
                                                            <td class="fw-bold pt-3 align-middle">CASH ADVANCE</td>
                                                            <td class="pt-3"><input type="number" name="cash_advance" class="form-control form-control-sm text-end text-danger" step="0.01" value="0" oninput="recalcAll()"></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-bold align-middle">GUARANTEED INCOME</td>
                                                            <td><input type="number" name="guaranteed_income" class="form-control form-control-sm text-end text-success" step="0.01" value="0" oninput="recalcAll()"></td>
                                                        </tr>
                                                        <tr><td class="fw-bold pt-2">OTHER DEDUCTION</td><td class="text-end text-danger pt-2" id="sum_other_ded">₱ 0.00</td></tr>
                                                        
                                                        <tr class="border-top border-dark"><td class="fw-bold">NET - After other deduction</td><td class="text-end fw-bold" id="sum_net_after_other">₱ 0.00</td></tr>
                                                        
                                                        <tr class="border-top border-2 border-dark table-success mt-2">
                                                            <td class="fw-bold fs-4 py-2">TAKE HOME PAY</td>
                                                            <td class="text-end fw-bold fs-4 py-2 text-success" id="sum_take_home">₱ 0.00</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-darbco" id="computeBtn"><i class="bi bi-calculator me-2"></i>Save Payroll</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form method="POST" action="index.php?page=payroll&action=review">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="payroll_id" id="review_payroll_id">
                    <div class="modal-header"><h5 class="modal-title"><i class="bi bi-clipboard-check me-2"></i>Review Payroll</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <p class="mb-2"><strong>Worker:</strong> <span id="review_worker_name"></span></p>
                        <p class="mb-3"><strong>Net Pay:</strong> <span id="review_net_pay" class="text-success fw-bold"></span></p>
                        <label class="form-label fw-semibold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info text-white">Mark as Reviewed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</main>
</div>
<script>
const BOX_SPECS = ['456H','CB HP','CS FD','4.7K','7.2K','BCP','SH','F.P','CL-B','Sm1 H','Clusters'];
function specOptions(){return BOX_SPECS.map(s=>`<option value="${s}">${s}</option>`).join('');}

function updateRowNumbers(tbodyId) {
    const rows = document.querySelectorAll(`#${tbodyId} tr`);
    rows.forEach((row, idx) => {
        const td = row.querySelector('td:first-child');
        if (td) td.textContent = idx + 1;
    });
}

function recalcAll() {
    // 1. Box Specs & Gross
    let gross = 0;
    let weekSubtotals = {};
    document.querySelectorAll('#boxSpecRows tr').forEach(r => {
        const w = r.querySelector('[name="bd_week[]"]')?.value.trim() || 'Unspecified';
        const q = +(r.querySelector('[name="bd_qty[]"]')?.value || 0);
        const p = +(r.querySelector('[name="bd_price[]"]')?.value || 0);
        const f = +(r.querySelector('[name="bd_forex[]"]')?.value || 1);
        const a = q * p * f;
        const af = r.querySelector('.bd-amt'); if(af) af.textContent = a.toFixed(2);
        gross += a;
        if (!weekSubtotals[w]) weekSubtotals[w] = 0;
        weekSubtotals[w] += a;
    });
    document.getElementById('grossTotal').textContent = '₱ ' + gross.toFixed(2);
    
    // Update Subtotals block
    const subContainer = document.getElementById('weekSubtotalsContainer');
    subContainer.innerHTML = '';
    let hasWeeks = false;
    for (const [w, amt] of Object.entries(weekSubtotals)) {
        if(amt > 0) {
            hasWeeks = true;
            subContainer.insertAdjacentHTML('beforeend', `<div class="col-auto"><span class="badge bg-secondary">Week ${w}: ₱ ${amt.toFixed(2)}</span></div>`);
        }
    }
    document.getElementById('weekSubtotals').style.display = hasWeeks ? 'block' : 'none';

    // 2. Materials
    let mat = 0;
    document.querySelectorAll('#matRows tr').forEach(r => {
        const q = +(r.querySelector('[name="ded_qty[]"]')?.value || 0);
        const p = +(r.querySelector('[name="ded_ucost[]"]')?.value || 0);
        const a = q * p;
        const amtInput = r.querySelector('[name="ded_amt[]"]');
        if(amtInput) amtInput.value = a.toFixed(2);
        mat += a;
    });
    document.getElementById('matTotal').textContent = '₱ ' + mat.toFixed(2);

    // 3. Contributions (Current only is deducted usually, let's sum current)
    let contrib = 0;
    document.querySelectorAll('#contribRows tr').forEach(r => {
        const p = +(r.querySelector('[name="contrib_prev[]"]')?.value || 0);
        const c = +(r.querySelector('[name="contrib_curr[]"]')?.value || 0);
        const rt = p + c;
        const rtInput = r.querySelector('[name="contrib_rtotal[]"]');
        if(rtInput) rtInput.value = rt.toFixed(2);
        contrib += c;
    });
    document.getElementById('contribTotal').textContent = '₱ ' + contrib.toFixed(2);

    // 4. Labor
    let labor = 0;
    document.querySelectorAll('#laborRows tr').forEach(r => {
        const q = +(r.querySelector('[name="ded_qty[]"]')?.value || 0);
        const p = +(r.querySelector('[name="ded_ucost[]"]')?.value || 0);
        const a = q * p;
        const amtInput = r.querySelector('[name="ded_amt[]"]');
        if(amtInput) amtInput.value = a.toFixed(2);
        labor += a;
    });
    document.getElementById('laborTotal').textContent = '₱ ' + labor.toFixed(2);

    // 5. Personal Account
    let personal = 0;
    document.querySelectorAll('#personalRows tr').forEach(r => {
        const prev = +(r.querySelector('[name="pa_prev[]"]')?.value || 0);
        const curr = +(r.querySelector('[name="pa_curr[]"]')?.value || 0);
        const tot = prev + curr;
        const totInput = r.querySelector('[name="pa_total[]"]');
        if(totInput) totInput.value = tot.toFixed(2);

        const ded = +(r.querySelector('[name="pa_ded[]"]')?.value || 0);
        const end = tot - ded;
        const endInput = r.querySelector('[name="pa_end[]"]');
        if(endInput) endInput.value = end.toFixed(2);

        personal += ded;
    });
    document.getElementById('personalTotal').textContent = '₱ ' + personal.toFixed(2);

    // 6. Other Deductions
    let other = 0;
    document.querySelectorAll('#otherRows tr').forEach(r => {
        other += +(r.querySelector('[name="other_amt[]"]')?.value || 0);
    });
    document.getElementById('otherTotal').textContent = '₱ ' + other.toFixed(2);

    // 7. Update Summary
    document.getElementById('sum_gross').textContent = '₱ ' + gross.toFixed(2);
    document.getElementById('sum_contrib').textContent = '₱ ' + contrib.toFixed(2);
    document.getElementById('sum_labor').textContent = '₱ ' + labor.toFixed(2);
    document.getElementById('sum_personal').textContent = '₱ ' + personal.toFixed(2);
    document.getElementById('sum_mat').textContent = '₱ ' + mat.toFixed(2);

    const totalDed = contrib + labor + personal + mat;
    document.getElementById('sum_total_ded').textContent = '₱ ' + totalDed.toFixed(2);

    const netProceeds = gross - totalDed;
    document.getElementById('sum_net_proceeds').textContent = '₱ ' + netProceeds.toFixed(2);

    const cashAdv = +(document.querySelector('[name="cash_advance"]')?.value || 0);
    const guaranteed = +(document.querySelector('[name="guaranteed_income"]')?.value || 0);
    document.getElementById('sum_other_ded').textContent = '₱ ' + other.toFixed(2);

    const netAfterOther = netProceeds - cashAdv - other;
    document.getElementById('sum_net_after_other').textContent = '₱ ' + netAfterOther.toFixed(2);

    const takeHome = netAfterOther + guaranteed;
    document.getElementById('sum_take_home').textContent = '₱ ' + Math.max(0, takeHome).toFixed(2);
}

// Production Data
function calcProdTotals() {
    const fields = ['bh', 'sh', 'cp', 'cb', 'stems'];
    let w1Boxes = 0, w2Boxes = 0;

    fields.forEach(f => {
        const w1 = +(document.querySelector(`[name="${f}_w1"]`)?.value || 0);
        const w2 = +(document.querySelector(`[name="${f}_w2"]`)?.value || 0);
        const t = w1 + w2;
        const totEl = document.getElementById(`${f}_tot`);
        if(totEl) totEl.value = t;

        if (f !== 'stems' && f !== 'cb') {
            w1Boxes += w1; w2Boxes += w2;
        }
    });

    const bhW1 = +(document.querySelector('[name="bh_w1"]')?.value || 0);
    const shW1 = +(document.querySelector('[name="sh_w1"]')?.value || 0);
    const cpW1 = +(document.querySelector('[name="cp_w1"]')?.value || 0);
    const claW1 = bhW1 + shW1 + cpW1;

    const bhW2 = +(document.querySelector('[name="bh_w2"]')?.value || 0);
    const shW2 = +(document.querySelector('[name="sh_w2"]')?.value || 0);
    const cpW2 = +(document.querySelector('[name="cp_w2"]')?.value || 0);
    const claW2 = bhW2 + shW2 + cpW2;

    document.getElementById('cl_a_w1').value = claW1;
    document.getElementById('cl_a_w2').value = claW2;
    document.getElementById('cl_a_tot').value = claW1 + claW2;

    const cbW1 = +(document.querySelector('[name="cb_w1"]')?.value || 0);
    const cbW2 = +(document.querySelector('[name="cb_w2"]')?.value || 0);

    const totW1 = claW1 + cbW1;
    const totW2 = claW2 + cbW2;
    const totBoxes = totW1 + totW2;

    document.getElementById('box_w1').value = totW1;
    document.getElementById('box_w2').value = totW2;
    document.getElementById('box_tot').value = totBoxes;

    const stemsW1 = +(document.querySelector('[name="stems_w1"]')?.value || 0);
    const stemsW2 = +(document.querySelector('[name="stems_w2"]')?.value || 0);
    const stemsTot = stemsW1 + stemsW2;

    document.getElementById('bs_w1').value = stemsW1 > 0 ? (totW1 / stemsW1).toFixed(3) : '0.000';
    document.getElementById('bs_w2').value = stemsW2 > 0 ? (totW2 / stemsW2).toFixed(3) : '0.000';
    document.getElementById('bs_tot').value = stemsTot > 0 ? (totBoxes / stemsTot).toFixed(3) : '0.000';
    
    // Update Hidden inputs for backend
    document.getElementById('hidden_bh').value = bhW1 + bhW2;
    document.getElementById('hidden_sh').value = shW1 + shW2;
    document.getElementById('hidden_cp').value = cpW1 + cpW2;
    document.getElementById('hidden_cb').value = cbW1 + cbW2;
    document.getElementById('hidden_boxes').value = totBoxes;
    document.getElementById('hidden_stems').value = stemsTot;
}

function updateProdLabels() {}

// Add Rows Functions
function addBoxSpecRow() {
    document.getElementById('boxSpecRows').insertAdjacentHTML('beforeend',`<tr>
        <td><input type="text" name="bd_week[]" class="form-control form-control-sm" placeholder="e.g. 7" oninput="recalcAll()"></td>
        <td><select name="bd_spec[]" class="form-select form-select-sm"><option value="">--</option>${specOptions()}</select></td>
        <td><input type="number" name="bd_qty[]" class="form-control form-control-sm text-end" min="0" value="0" oninput="recalcAll()"></td>
        <td><input type="number" name="bd_price[]" class="form-control form-control-sm text-end" step="0.01" min="0" value="0" oninput="recalcAll()"></td>
        <td><input type="number" name="bd_forex[]" class="form-control form-control-sm text-end" step="0.0001" value="1.00" oninput="recalcAll()"></td>
        <td class="bd-amt text-end fw-semibold pt-2">0.00</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();recalcAll()"><i class="bi bi-x"></i></button></td>
    </tr>`);
}

function addDedRow(tbodyId, catValue) {
    document.getElementById(tbodyId).insertAdjacentHTML('beforeend', `<tr>
        <td class="pt-2 text-center text-muted"></td>
        <td>
            <input type="hidden" name="ded_cat[]" value="${catValue}">
            <input type="text" name="ded_desc[]" class="form-control form-control-sm" placeholder="Description">
        </td>
        <td><input type="number" name="ded_qty[]" class="form-control form-control-sm text-end" step="0.01" min="0" value="0" oninput="recalcAll()"></td>
        <td><input type="text" name="ded_unit[]" class="form-control form-control-sm" placeholder="Unit"></td>
        <td><input type="number" name="ded_ucost[]" class="form-control form-control-sm text-end" step="0.01" min="0" value="0" oninput="recalcAll()"></td>
        <td><input type="number" name="ded_amt[]" class="form-control form-control-sm text-end bg-light" readonly step="0.01" value="0"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();updateRowNumbers('${tbodyId}');recalcAll()"><i class="bi bi-x"></i></button></td>
    </tr>`);
    updateRowNumbers(tbodyId);
}

function addContribRow() {
    document.getElementById('contribRows').insertAdjacentHTML('beforeend', `<tr>
        <td class="pt-2 text-center text-muted"></td>
        <td><input type="text" name="contrib_desc[]" class="form-control form-control-sm" placeholder="Description"></td>
        <td><input type="number" name="contrib_prev[]" class="form-control form-control-sm text-end" step="0.01" value="0" oninput="recalcAll()"></td>
        <td><input type="number" name="contrib_curr[]" class="form-control form-control-sm text-end" step="0.01" value="0" oninput="recalcAll()"></td>
        <td><input type="number" name="contrib_rtotal[]" class="form-control form-control-sm text-end bg-light" readonly step="0.01" value="0"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();updateRowNumbers('contribRows');recalcAll()"><i class="bi bi-x"></i></button></td>
    </tr>`);
    updateRowNumbers('contribRows');
}

function addPersonalRow() {
    document.getElementById('personalRows').insertAdjacentHTML('beforeend', `<tr>
        <td class="pt-2 text-center text-muted"></td>
        <td><input type="text" name="pa_desc[]" class="form-control form-control-sm" placeholder="Description"></td>
        <td><input type="number" name="pa_prev[]" class="form-control form-control-sm text-end" step="0.01" value="0" oninput="recalcAll()"></td>
        <td><input type="number" name="pa_curr[]" class="form-control form-control-sm text-end" step="0.01" value="0" oninput="recalcAll()"></td>
        <td><input type="number" name="pa_total[]" class="form-control form-control-sm text-end bg-light" readonly step="0.01" value="0"></td>
        <td><input type="number" name="pa_ded[]" class="form-control form-control-sm text-end" step="0.01" value="0" oninput="recalcAll()"></td>
        <td><input type="number" name="pa_end[]" class="form-control form-control-sm text-end bg-light" readonly step="0.01" value="0"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();updateRowNumbers('personalRows');recalcAll()"><i class="bi bi-x"></i></button></td>
    </tr>`);
    updateRowNumbers('personalRows');
}

function addOtherDedRow() {
    document.getElementById('otherRows').insertAdjacentHTML('beforeend', `<tr>
        <td class="pt-2 text-center text-muted"></td>
        <td><input type="text" name="other_desc[]" class="form-control form-control-sm" placeholder="Description"></td>
        <td><input type="number" name="other_amt[]" class="form-control form-control-sm text-end" step="0.01" value="0" oninput="recalcAll()"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();updateRowNumbers('otherRows');recalcAll()"><i class="bi bi-x"></i></button></td>
    </tr>`);
    updateRowNumbers('otherRows');
}

document.addEventListener('DOMContentLoaded', () => {
    // Add event listeners to production inputs
    document.querySelectorAll('.prod-input').forEach(input => {
        input.addEventListener('input', calcProdTotals);
    });

    // Auto-expand accordion if a required field inside it is invalid
    document.querySelector('#computePayrollModal form')?.addEventListener('invalid', function(e) {
        const accordionCollapse = e.target.closest('.accordion-collapse');
        if (accordionCollapse && !accordionCollapse.classList.contains('show')) {
            if (typeof bootstrap !== 'undefined') {
                new bootstrap.Collapse(accordionCollapse, { toggle: false }).show();
            } else {
                accordionCollapse.classList.add('show');
            }
        }
    }, true);

    // Review modal listeners
    document.querySelectorAll('.review-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            const b = e.currentTarget;
            document.getElementById('review_payroll_id').value = b.dataset.id;
            document.getElementById('review_worker_name').textContent = b.dataset.worker;
            document.getElementById('review_net_pay').textContent = '₱' + b.dataset.net;
        });
    });
});
</script>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
