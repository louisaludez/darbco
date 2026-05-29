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
                        <h5 class="modal-title" id="computePayrollLabel"><i class="bi bi-calculator me-2"></i>DARBCO IFS — Harvest Proceeds</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="max-height:75vh; overflow-y:auto;">
                        <!-- Header Info -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Worker / ARB <span class="text-danger">*</span></label>
                                <select name="worker_id" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach ($activeWorkers as $w): ?>
                                    <option value="<?= $w['worker_id'] ?>"><?= htmlspecialchars(($w['sub_code'] ? '[' . $w['sub_code'] . '] ' : '') . $w['first_name'] . ' ' . $w['last_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2"><label class="form-label fw-semibold">Area</label><input type="text" name="area" class="form-control" placeholder="Farm area"></div>
                            <div class="col-md-2"><label class="form-label fw-semibold">Harvest Date <span class="text-danger">*</span></label><input type="date" name="harvest_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
                            <div class="col-md-1"><label class="form-label fw-semibold">Week</label><input type="text" name="week_number" class="form-control" placeholder="7"></div>
                            <div class="col-md-2"><label class="form-label fw-semibold">Cycle</label><input type="text" name="cycle_code" class="form-control" placeholder="R2-2 C28"></div>
                            <div class="col-md-2"><label class="form-label fw-semibold">Forex Rate</label><input type="number" name="forex_rate" class="form-control" step="0.0001" value="1.00"></div>
                        </div>

                        <!-- Box Spec Pricing -->
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center py-2">
                                <span class="fw-bold"><i class="bi bi-box-seam me-1"></i>Box Specs & Pricing</span>
                                <button type="button" class="btn btn-sm btn-outline-success" id="addBoxSpecRow"><i class="bi bi-plus"></i> Add Spec</button>
                            </div>
                            <div class="card-body p-2">
                                <table class="table table-sm table-bordered mb-0" id="boxSpecTable">
                                    <thead class="table-light"><tr><th>Box Spec</th><th style="width:80px">Qty</th><th style="width:100px">Price (₱)</th><th style="width:100px">Forex</th><th style="width:110px">Amount</th><th style="width:40px"></th></tr></thead>
                                    <tbody id="boxSpecRows"></tbody>
                                    <tfoot><tr><td colspan="4" class="text-end fw-bold">Total Gross Proceeds:</td><td class="fw-bold" id="grossTotal">₱ 0.00</td><td></td></tr></tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Production Data -->
                        <div class="card mb-3">
                            <div class="card-header py-2 fw-bold"><i class="bi bi-bar-chart-fill me-1"></i>Production Data</div>
                            <div class="card-body p-2">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="border p-2 rounded h-100">
                                            <h6 class="fw-bold mb-2">CLASS A (13.5 K)</h6>
                                            <div class="row g-2 align-items-center mb-1">
                                                <div class="col-6"><label class="form-label mb-0">Big Hands</label></div>
                                                <div class="col-6"><input type="number" name="class_a_big_hands" class="form-control form-control-sm" min="0" value="0" oninput="recalcProd()"></div>
                                            </div>
                                            <div class="row g-2 align-items-center mb-1">
                                                <div class="col-6"><label class="form-label mb-0">Small Hands</label></div>
                                                <div class="col-6"><input type="number" name="class_a_small_hands" class="form-control form-control-sm" min="0" value="0" oninput="recalcProd()"></div>
                                            </div>
                                            <div class="row g-2 align-items-center mb-1">
                                                <div class="col-6"><label class="form-label mb-0">CPs</label></div>
                                                <div class="col-6"><input type="number" name="class_a_cps" class="form-control form-control-sm" min="0" value="0" oninput="recalcProd()"></div>
                                            </div>
                                            <div class="row g-2 align-items-center mt-2 border-top pt-2">
                                                <div class="col-6"><label class="form-label fw-bold mb-0">Total Class A</label></div>
                                                <div class="col-6"><input type="number" class="form-control form-control-sm bg-light" id="class_a_total" readonly value="0"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border p-2 rounded h-100">
                                            <div class="row g-2 align-items-center mb-2">
                                                <div class="col-6"><label class="form-label fw-bold mb-0">CLASS B (13.5 K)</label></div>
                                                <div class="col-6"><input type="number" name="class_b" class="form-control form-control-sm" min="0" value="0" oninput="recalcProd()"></div>
                                            </div>
                                            <div class="row g-2 align-items-center mb-2">
                                                <div class="col-6"><label class="form-label fw-bold text-success mb-0">TOTAL BOXES</label></div>
                                                <div class="col-6"><input type="number" name="boxes_produced" id="boxes_produced" class="form-control form-control-sm bg-light" readonly value="0"></div>
                                            </div>
                                            <div class="row g-2 align-items-center mb-2">
                                                <div class="col-6"><label class="form-label fw-bold mb-0">STEMS CUT</label></div>
                                                <div class="col-6"><input type="number" name="stems_cut" id="stems_cut" class="form-control form-control-sm" min="0" value="0" oninput="recalcProd()"></div>
                                            </div>
                                            <div class="row g-2 align-items-center">
                                                <div class="col-6"><label class="form-label fw-bold mb-0">BS RATIO</label></div>
                                                <div class="col-6"><input type="text" id="bs_ratio_display" class="form-control form-control-sm bg-light" readonly value="0.00"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6"><label class="form-label fw-semibold">Rate/Box (₱)</label><input type="number" name="rate_per_box" class="form-control" step="0.01" value="<?= DEFAULT_RATE_PER_BOX ?>"></div>
                                    <div class="col-md-6"><label class="form-label fw-semibold">Prod. Record</label>
                                        <select name="production_id" class="form-select">
                                            <option value="">-- Optional link --</option>
                                            <?php foreach ($productionList as $prod): ?>
                                            <option value="<?= $prod['production_id'] ?>">#<?= $prod['production_id'] ?> — <?= htmlspecialchars($prod['worker_name']) ?> (<?= $prod['harvest_date'] ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Deductions Accordion -->
                        <div class="accordion mb-3 shadow-sm" id="deductionsAccordion">
                            <!-- A. CONTRIBUTIONS -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingA">
                                    <button class="accordion-button collapsed fw-bold text-info" type="button" data-bs-toggle="collapse" data-bs-target="#collapseA">
                                        <i class="bi bi-piggy-bank me-2"></i>A. CONTRIBUTIONS
                                    </button>
                                </h2>
                                <div id="collapseA" class="accordion-collapse collapse" data-bs-parent="#deductionsAccordion">
                                    <div class="accordion-body p-2 bg-light">
                                        <div class="d-flex justify-content-end mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-info bg-white" id="addContribRow"><i class="bi bi-plus"></i> Add</button>
                                        </div>
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead class="table-light"><tr><th>Type</th><th style="width:120px">Previous (₱)</th><th style="width:120px">Current (₱)</th><th style="width:40px"></th></tr></thead>
                                            <tbody id="contribRows"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- B. DIRECT LABOR COST -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingB">
                                    <button class="accordion-button collapsed fw-bold text-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseB">
                                        <i class="bi bi-people me-2"></i>B. DIRECT LABOR COST
                                    </button>
                                </h2>
                                <div id="collapseB" class="accordion-collapse collapse" data-bs-parent="#deductionsAccordion">
                                    <div class="accordion-body p-2 bg-light">
                                        <div class="d-flex justify-content-end mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger bg-white" onclick="addDedRow('laborRows', 'labor')"><i class="bi bi-plus"></i> Add</button>
                                        </div>
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead class="table-light"><tr><th>Description</th><th style="width:80px">Qty</th><th style="width:100px">Unit Cost</th><th style="width:110px">Amount (₱)</th><th style="width:40px"></th></tr></thead>
                                            <tbody id="laborRows" class="ded-group-rows"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- C. PERSONAL ACCOUNT -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingC">
                                    <button class="accordion-button collapsed fw-bold text-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseC">
                                        <i class="bi bi-person-badge me-2"></i>C. PERSONAL ACCOUNT
                                    </button>
                                </h2>
                                <div id="collapseC" class="accordion-collapse collapse" data-bs-parent="#deductionsAccordion">
                                    <div class="accordion-body p-2 bg-light">
                                        <div class="d-flex justify-content-end mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger bg-white" onclick="addDedRow('personalRows', 'personal')"><i class="bi bi-plus"></i> Add</button>
                                        </div>
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead class="table-light"><tr><th>Description</th><th style="width:80px">Qty</th><th style="width:100px">Unit Cost</th><th style="width:110px">Amount (₱)</th><th style="width:40px"></th></tr></thead>
                                            <tbody id="personalRows" class="ded-group-rows"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- D. OTHER DEDUCTIONS -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingD">
                                    <button class="accordion-button collapsed fw-bold text-danger" type="button" data-bs-toggle="collapse" data-bs-target="#collapseD">
                                        <i class="bi bi-dash-circle me-2"></i>D. OTHER DEDUCTIONS - Charge to Guaranteed Income
                                    </button>
                                </h2>
                                <div id="collapseD" class="accordion-collapse collapse" data-bs-parent="#deductionsAccordion">
                                    <div class="accordion-body p-2 bg-light">
                                        <div class="d-flex justify-content-end mb-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger bg-white" onclick="addDedRow('otherRows', 'other')"><i class="bi bi-plus"></i> Add</button>
                                        </div>
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead class="table-light"><tr><th>Description</th><th style="width:80px">Qty</th><th style="width:100px">Unit Cost</th><th style="width:110px">Amount (₱)</th><th style="width:40px"></th></tr></thead>
                                            <tbody id="otherRows" class="ded-group-rows"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-end fw-bold text-danger mb-3 px-2">Total Deductions: <span id="dedTotal">₱ 0.00</span></div>

                        <!-- Other fields -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><label class="form-label fw-semibold">Guaranteed Income (₱)</label><input type="number" name="guaranteed_income" class="form-control" step="0.01" value="0"></div>
                            <div class="col-md-4"><label class="form-label fw-semibold">Period Start</label><input type="date" name="period_start" class="form-control" required value="<?= date('Y-m-01') ?>"></div>
                            <div class="col-md-4"><label class="form-label fw-semibold">Period End</label><input type="date" name="period_end" class="form-control" required value="<?= date('Y-m-t') ?>"></div>
                        </div>

                        <div class="alert alert-success py-3 text-center mb-0">
                            <span class="fs-5 fw-bold">Estimated Take-Home Pay: <span id="netPayPreview">₱ 0.00</span></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-darbco" id="computeBtn"><i class="bi bi-calculator me-2"></i>Compute & Save</button>
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
const DED_CATEGORIES = [
    {v:'material',l:'Material'},{v:'labor',l:'Direct Labor'},{v:'personal',l:'Personal Account'},
    {v:'cash_advance',l:'Cash Advance'},{v:'contribution',l:'Contribution'},{v:'other',l:'Other'}
];
const CONTRIB_PRESETS = ['Dale Capital Share','CEFUAPCO CBU','Blocking Credit(Fertilizer)','Bio-Organic/Credit','SSS Mandatory(Materia)'];

function catOptions(){return DED_CATEGORIES.map(c=>`<option value="${c.v}">${c.l}</option>`).join('');}
function specOptions(){return BOX_SPECS.map(s=>`<option value="${s}">${s}</option>`).join('');}
function contribOptions(){return CONTRIB_PRESETS.map(s=>`<option value="${s}">${s}</option>`).join('');}

function recalc(){
    let gross=0;
    document.querySelectorAll('#boxSpecRows tr').forEach(r=>{
        const q=+(r.querySelector('[name="bd_qty[]"]')?.value||0);
        const p=+(r.querySelector('[name="bd_price[]"]')?.value||0);
        const f=+(r.querySelector('[name="bd_forex[]"]')?.value||1);
        const a=q*p*f;
        const af=r.querySelector('.bd-amt');if(af)af.textContent='₱ '+a.toFixed(2);
        gross+=a;
    });
    document.getElementById('grossTotal').textContent='₱ '+gross.toFixed(2);
    let ded=0;
    document.querySelectorAll('.ded-group-rows tr').forEach(r=>{
        ded+=+(r.querySelector('[name="ded_amt[]"]')?.value||0);
    });
    document.getElementById('dedTotal').textContent='₱ '+ded.toFixed(2);
    const gi=+(document.querySelector('[name="guaranteed_income"]')?.value||0);
    document.getElementById('netPayPreview').textContent='₱ '+Math.max(0,gross-ded+gi).toFixed(2);
}

function recalcProd() {
    const bh = +(document.querySelector('[name="class_a_big_hands"]')?.value || 0);
    const sh = +(document.querySelector('[name="class_a_small_hands"]')?.value || 0);
    const cp = +(document.querySelector('[name="class_a_cps"]')?.value || 0);
    const aTotal = bh + sh + cp;
    document.getElementById('class_a_total').value = aTotal;
    
    const b = +(document.querySelector('[name="class_b"]')?.value || 0);
    const totalBoxes = aTotal + b;
    document.getElementById('boxes_produced').value = totalBoxes;
    
    const stems = +(document.getElementById('stems_cut')?.value || 0);
    const bs = (stems > 0) ? (totalBoxes / stems).toFixed(3) : '0.000';
    document.getElementById('bs_ratio_display').value = bs;
}

function addDedRow(tbodyId, catValue) {
    document.getElementById(tbodyId).insertAdjacentHTML('beforeend', `<tr>
        <input type="hidden" name="ded_cat[]" value="${catValue}">
        <td><input type="text" name="ded_desc[]" class="form-control form-control-sm" placeholder="Description"></td>
        <td><input type="number" name="ded_qty[]" class="form-control form-control-sm" step="0.01" min="0"></td>
        <td><input type="number" name="ded_ucost[]" class="form-control form-control-sm" step="0.01" min="0"></td>
        <td><input type="number" name="ded_amt[]" class="form-control form-control-sm" step="0.01" min="0" value="0" oninput="recalc()"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();recalc()"><i class="bi bi-x"></i></button></td>
    </tr>`);
}

document.addEventListener('DOMContentLoaded',()=>{
    // Box spec rows
    document.getElementById('addBoxSpecRow')?.addEventListener('click',()=>{
        document.getElementById('boxSpecRows').insertAdjacentHTML('beforeend',`<tr>
            <td><select name="bd_spec[]" class="form-select form-select-sm"><option value="">--</option>${specOptions()}</select></td>
            <td><input type="number" name="bd_qty[]" class="form-control form-control-sm" min="0" value="0" oninput="recalc()"></td>
            <td><input type="number" name="bd_price[]" class="form-control form-control-sm" step="0.01" min="0" value="0" oninput="recalc()"></td>
            <td><input type="number" name="bd_forex[]" class="form-control form-control-sm" step="0.0001" value="1.00" oninput="recalc()"></td>
            <td class="bd-amt fw-semibold">₱ 0.00</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove();recalc()"><i class="bi bi-x"></i></button></td>
        </tr>`);
    });
    // Contribution rows
    document.getElementById('addContribRow')?.addEventListener('click',()=>{
        document.getElementById('contribRows').insertAdjacentHTML('beforeend',`<tr>
            <td><select name="contrib_type[]" class="form-select form-select-sm"><option value="">--</option>${contribOptions()}</select></td>
            <td><input type="number" name="contrib_prev[]" class="form-control form-control-sm" step="0.01" value="0"></td>
            <td><input type="number" name="contrib_curr[]" class="form-control form-control-sm" step="0.01" value="0"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="bi bi-x"></i></button></td>
        </tr>`);
    });
    // Review modal
    document.querySelectorAll('.review-btn').forEach(btn=>{
        btn.addEventListener('click',e=>{
            const b=e.currentTarget;
            document.getElementById('review_payroll_id').value=b.dataset.id;
            document.getElementById('review_worker_name').textContent=b.dataset.worker;
            document.getElementById('review_net_pay').textContent='₱'+b.dataset.net;
        });
    });
    // Listen for guaranteed_income changes
    document.querySelector('[name="guaranteed_income"]')?.addEventListener('input',recalc);
});
</script>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
