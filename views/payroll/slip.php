<?php
// views/payroll/slip.php
// A clean, print-friendly layout for a worker's payroll slip.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payroll Slip #<?= $slipData['payroll_id'] ?> - <?= htmlspecialchars($slipData['worker_name']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #fff; color: #000; font-family: 'Inter', sans-serif; font-size: 14px; }
        .slip-container { max-width: 800px; margin: 40px auto; border: 1px solid #ccc; padding: 40px; }
        .slip-header { border-bottom: 2px solid #1a7f4b; padding-bottom: 15px; margin-bottom: 30px; }
        .brand-text { color: #1a7f4b; font-weight: 800; font-size: 24px; letter-spacing: -0.5px; }
        .meta-label { color: #666; font-size: 12px; text-transform: uppercase; font-weight: 600; margin-bottom: 3px; }
        .meta-val { font-size: 15px; font-weight: 700; }
        .calc-table th { background: #f8f9fa; color: #444; font-weight: 600; font-size: 13px; text-transform: uppercase; }
        .calc-table td, .calc-table th { padding: 12px; border: 1px solid #dee2e6; }
        .total-row td { font-size: 18px; font-weight: 800; background: #e8f5ee; color: #1a7f4b; }
        .signatures { margin-top: 50px; }
        .sig-box { border-top: 1px solid #000; padding-top: 5px; width: 200px; text-align: center; font-size: 13px; }
        @media print {
            body { background: #fff; }
            .slip-container { border: none; padding: 0; margin: 0; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="slip-container">
    <div class="text-end mb-3 no-print">
        <button class="btn btn-outline-secondary" onclick="window.print()">Print Slip</button>
        <button class="btn btn-outline-danger ms-2" onclick="window.close()">Close</button>
    </div>

    <!-- Header -->
    <div class="slip-header d-flex justify-content-between align-items-center">
        <div>
            <div class="brand-text">DARBCO</div>
            <div style="font-size: 13px; color: #555;">Banana Production &amp; Export Management</div>
        </div>
        <div class="text-end">
            <h4 class="mb-0 fw-bold text-uppercase" style="color: #444; letter-spacing: 1px;">Payment Voucher</h4>
            <div class="text-muted" style="font-size: 13px;">Slip No: <strong>#<?= str_pad((string)$slipData['payroll_id'], 6, '0', STR_PAD_LEFT) ?></strong></div>
        </div>
    </div>

    <!-- Worker & Period Info -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <div class="meta-label">Worker / Team Name</div>
            <div class="meta-val"><?= htmlspecialchars($slipData['worker_name']) ?></div>
        </div>
        <div class="col-sm-6 text-end">
            <div class="meta-label">Harvest Date</div>
            <div class="meta-val"><?= date('F j, Y', strtotime($slipData['harvest_date'])) ?></div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-sm-6">
            <div class="meta-label">Payment Period</div>
            <div class="meta-val">
                <?= date('M j', strtotime($slipData['period_start'])) ?> — <?= date('M j, Y', strtotime($slipData['period_end'])) ?>
            </div>
        </div>
        <div class="col-sm-6 text-end">
            <div class="meta-label">Issue Date</div>
            <div class="meta-val"><?= date('F j, Y') ?></div>
        </div>
    </div>

    <!-- Calculations Table -->
    <table class="table calc-table mb-5">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-center">Quantity</th>
                <th class="text-end">Rate (₱)</th>
                <th class="text-end">Amount (₱)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Harvest Production (Boxes)</td>
                <td class="text-center"><?= number_format($slipData['boxes_produced']) ?></td>
                <td class="text-end"><?= number_format($slipData['rate_per_box'], 2) ?></td>
                <td class="text-end fw-bold"><?= number_format($slipData['gross_pay'], 2) ?></td>
            </tr>
            <tr>
                <td colspan="3" class="text-end text-danger" style="padding-right: 20px;">Less: Deductions (Advances/Supplies)</td>
                <td class="text-end text-danger">- <?= number_format($slipData['deductions'], 2) ?></td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="text-end" style="border-right: none;">NET PAY</td>
                <td class="text-end">₱ <?= number_format($slipData['net_pay'], 2) ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Audit / Signatures -->
    <div class="row signatures">
        <div class="col-4">
            <div class="sig-box">
                <strong>Computed By</strong><br>
                <?= htmlspecialchars($slipData['computed_by_name'] ?? 'System') ?>
            </div>
        </div>
        <div class="col-4 d-flex justify-content-center">
            <div class="sig-box">
                <strong>Approved By</strong><br>
                <?= htmlspecialchars($slipData['approved_by_name'] ?? 'Admin') ?>
            </div>
        </div>
        <div class="col-4 d-flex justify-content-end">
            <div class="sig-box">
                <strong>Received By (Worker)</strong><br>
                <span class="text-muted" style="font-size:10px;">Signature over printed name</span>
            </div>
        </div>
    </div>

    <div class="text-center mt-5" style="font-size: 11px; color: #777;">
        This document is system-generated and approved via DARBCO Workflow Application.<br>
        Transaction Log ID: <?= md5($slipData['payroll_id'] . $slipData['created_at']) ?>
    </div>
</div>

<script>
// Auto-print prompt on load
window.addEventListener('load', () => { setTimeout(() => { window.print(); }, 500); });
</script>
</body>
</html>
