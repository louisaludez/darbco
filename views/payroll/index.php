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
        <?php if (in_array($role, [ROLE_PAYROLL, ROLE_ADMIN], true)): ?>
        <button class="btn btn-darbco" data-bs-toggle="modal" data-bs-target="#computePayrollModal" id="computePayrollBtn">
            <i class="bi bi-calculator me-2"></i>Compute Payroll
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
                        <th>#</th><th>Worker</th><th>Date</th><th>Boxes</th>
                        <th>Rate/Box</th><th>Gross</th><th>Deductions</th>
                        <th>Net Pay</th><th>Status</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($records as $p): ?>
                <tr>
                    <td><?= $p['payroll_id'] ?></td>
                    <td><?= htmlspecialchars($p['worker_name']) ?></td>
                    <td><?= $p['harvest_date'] ?></td>
                    <td><?= number_format($p['boxes_produced']) ?></td>
                    <td>₱<?= number_format($p['rate_per_box'], 2) ?></td>
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
                        <?php if ($p['status'] === 'pending_review' && in_array($role, [ROLE_FINANCE, ROLE_ADMIN], true)): ?>
                        <button class="btn btn-sm btn-outline-info review-btn"
                                data-bs-toggle="modal" data-bs-target="#reviewModal"
                                data-id="<?= $p['payroll_id'] ?>"
                                data-worker="<?= htmlspecialchars($p['worker_name']) ?>"
                                data-net="<?= number_format($p['net_pay'], 2) ?>">
                            <i class="bi bi-clipboard-check"></i> Review
                        </button>
                        <?php endif; ?>
                        <?php if ($p['status'] === 'reviewed' && $role === ROLE_ADMIN): ?>
                        <form method="POST" action="index.php?page=payroll&action=approve" class="d-inline">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="payroll_id" value="<?= $p['payroll_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-darbco">
                                <i class="bi bi-check-circle"></i> Approve
                            </button>
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

    <!-- Compute Payroll Modal -->
    <?php if (in_array($role, [ROLE_PAYROLL, ROLE_ADMIN], true)): ?>
    <div class="modal fade" id="computePayrollModal" tabindex="-1" aria-labelledby="computePayrollLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="index.php?page=payroll&action=compute">
                    <?= Csrf::field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="computePayrollLabel">
                            <i class="bi bi-calculator me-2"></i>Compute Payroll
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Production Record <span class="text-danger">*</span></label>
                                <select name="production_id" id="production_id" class="form-select" required>
                                    <option value="">-- Select Production Record --</option>
                                    <?php foreach ($productionList as $prod): ?>
                                    <option value="<?= $prod['production_id'] ?>">
                                        #<?= $prod['production_id'] ?> — <?= htmlspecialchars($prod['worker_name']) ?>
                                        (<?= $prod['harvest_date'] ?>, <?= number_format($prod['boxes_produced']) ?> boxes)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Rate per Box (₱)</label>
                                <input type="number" name="rate_per_box" id="rate_per_box" class="form-control"
                                       value="<?= DEFAULT_RATE_PER_BOX ?>" step="0.01" min="0" id="gross_pay_preview">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Deductions (₱)</label>
                                <input type="number" name="deductions" id="deductions" class="form-control"
                                       value="0" step="0.01" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Period Start</label>
                                <input type="date" name="period_start" id="period_start" class="form-control" required value="<?= date('Y-m-01') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Period End</label>
                                <input type="date" name="period_end" id="period_end" class="form-control" required value="<?= date('Y-m-t') ?>">
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info mb-0 py-2">
                                    <small>Est. Net Pay: <strong id="net_pay_preview">₱ 0.00</strong></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-darbco" id="computeBtn"><i class="bi bi-calculator me-2"></i>Compute</button>
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
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-clipboard-check me-2"></i>Review Payroll</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2"><strong>Worker:</strong> <span id="review_worker_name"></span></p>
                        <p class="mb-3"><strong>Net Pay:</strong> <span id="review_net_pay" class="text-success fw-bold"></span></p>
                        
                        <label class="form-label fw-semibold">Remarks / Comments</label>
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
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.review-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const b = e.currentTarget;
            document.getElementById('review_payroll_id').value = b.dataset.id;
            document.getElementById('review_worker_name').textContent = b.dataset.worker;
            document.getElementById('review_net_pay').textContent = '₱' + b.dataset.net;
        });
    });
});
</script>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
