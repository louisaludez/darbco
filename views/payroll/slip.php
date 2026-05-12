<?php
// views/payroll/slip.php
// Full Harvest Proceeds slip matching DARBCO paper form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Harvest Proceeds — <?= htmlspecialchars($slipData['worker_name']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background:#fff; color:#000; font-family:'Inter',sans-serif; font-size:12px; }
        .slip { max-width:900px; margin:30px auto; border:1px solid #999; padding:25px; }
        .slip-title { text-align:center; font-weight:800; font-size:16px; letter-spacing:1px; border-bottom:2px solid #000; padding-bottom:8px; margin-bottom:15px; }
        .slip-subtitle { text-align:center; font-size:11px; color:#555; margin-top:-10px; margin-bottom:12px; }
        .info-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px 20px; margin-bottom:15px; }
        .info-grid .lbl { font-size:10px; text-transform:uppercase; color:#666; font-weight:600; }
        .info-grid .val { font-weight:700; font-size:13px; border-bottom:1px solid #ccc; padding-bottom:2px; }
        .sec-title { background:#f0f0f0; font-weight:700; font-size:11px; text-transform:uppercase; padding:5px 8px; margin:12px 0 6px; border:1px solid #ccc; }
        table.data-tbl { width:100%; border-collapse:collapse; font-size:11px; margin-bottom:10px; }
        table.data-tbl th, table.data-tbl td { border:1px solid #bbb; padding:4px 6px; }
        table.data-tbl th { background:#f5f5f5; font-weight:600; text-transform:uppercase; font-size:10px; }
        table.data-tbl .num { text-align:right; }
        .total-row { background:#e8f5ee; font-weight:800; }
        .total-row td { font-size:13px !important; }
        .ded-row td { color:#c00; }
        .net-row { background:#1a7f4b; color:#fff; }
        .net-row td { font-size:15px !important; font-weight:800; }
        .sig-area { display:flex; justify-content:space-between; margin-top:40px; }
        .sig-box { border-top:1px solid #000; width:180px; text-align:center; padding-top:4px; font-size:11px; }
        .footer-note { text-align:center; margin-top:30px; font-size:9px; color:#888; }
        @media print {
            body { background:#fff; } .slip { border:none; padding:0; margin:0; max-width:100%; } .no-print { display:none !important; }
        }
    </style>
</head>
<body>
<div class="slip">
    <div class="text-end mb-2 no-print">
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">Print</button>
        <button class="btn btn-sm btn-outline-danger ms-1" onclick="window.close()">Close</button>
    </div>

    <div class="slip-title">DARBCO IFS — HARVEST PROCEEDS</div>
    <div class="slip-subtitle"><?= htmlspecialchars($slipData['cycle_code'] ?? '') ?></div>

    <!-- Header Info -->
    <div class="info-grid">
        <div><div class="lbl">Name</div><div class="val"><?= htmlspecialchars($slipData['worker_name']) ?></div></div>
        <div><div class="lbl">Area</div><div class="val"><?= htmlspecialchars($slipData['area'] ?? $slipData['worker_area'] ?? '—') ?></div></div>
        <div><div class="lbl">Week</div><div class="val"><?= htmlspecialchars($slipData['week_number'] ?? '—') ?></div></div>
        <div><div class="lbl">Period</div><div class="val"><?= date('M j', strtotime($slipData['period_start'])) ?> — <?= date('M j, Y', strtotime($slipData['period_end'])) ?></div></div>
        <div><div class="lbl">Slip No.</div><div class="val">#<?= str_pad((string)$slipData['payroll_id'], 6, '0', STR_PAD_LEFT) ?></div></div>
        <div><div class="lbl">Forex</div><div class="val"><?= number_format((float)($slipData['forex_rate'] ?? 1), 4) ?></div></div>
    </div>

    <!-- Box Specs & Pricing -->
    <?php if (!empty($slipBoxDetails)): ?>
    <div class="sec-title"><i class="bi bi-box-seam me-1"></i>Box Specs</div>
    <table class="data-tbl">
        <thead><tr><th>Spec</th><th>Qty</th><th>Price (₱)</th><th>Forex</th><th>Amount (₱)</th></tr></thead>
        <tbody>
        <?php $subTotal = 0; foreach ($slipBoxDetails as $bd): $subTotal += (float)$bd['amount']; ?>
        <tr>
            <td><?= htmlspecialchars($bd['box_spec']) ?></td>
            <td class="num"><?= number_format((int)$bd['quantity']) ?></td>
            <td class="num"><?= number_format((float)$bd['price_per_box'], 2) ?></td>
            <td class="num"><?= number_format((float)$bd['forex_rate'], 4) ?></td>
            <td class="num"><?= number_format((float)$bd['amount'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="total-row"><td colspan="4" class="num">TOTAL GROSS PROCEEDS</td><td class="num">₱ <?= number_format($subTotal, 2) ?></td></tr>
        </tbody>
    </table>
    <?php else: ?>
    <div class="sec-title">Gross Proceeds</div>
    <table class="data-tbl">
        <tr><td>Harvest Production (<?= number_format($slipData['boxes_produced']) ?> boxes × ₱<?= number_format((float)$slipData['rate_per_box'], 2) ?>)</td><td class="num fw-bold">₱ <?= number_format((float)$slipData['gross_pay'], 2) ?></td></tr>
    </table>
    <?php endif; ?>

    <!-- Production Data -->
    <div class="sec-title">Production Data</div>
    <table class="data-tbl">
        <tr><td>Total Boxes</td><td class="num"><?= number_format($slipData['boxes_produced']) ?></td><td>Stems Cut</td><td class="num"><?= number_format((int)($slipData['stems_cut_payroll'] ?? 0)) ?></td><td>BS Ratio</td><td class="num"><?= $slipData['bs_ratio'] ? number_format((float)$slipData['bs_ratio'], 3) : '—' ?></td></tr>
    </table>

    <!-- Itemized Deductions -->
    <?php if (!empty($slipDeductions)): ?>
    <?php
    $grouped = [];
    foreach ($slipDeductions as $d) { $grouped[$d['category']][] = $d; }
    $catLabels = ['material'=>'Materials Withdrawal','labor'=>'Direct Labor Cost','personal'=>'Personal Account','cash_advance'=>'Cash Advance','contribution'=>'Contributions','other'=>'Other Deductions'];
    ?>
    <?php foreach ($grouped as $cat => $items): ?>
    <div class="sec-title"><?= $catLabels[$cat] ?? ucfirst($cat) ?></div>
    <table class="data-tbl">
        <thead><tr><th>#</th><th>Description</th><th>Qty</th><th>Unit Cost</th><th>Amount (₱)</th></tr></thead>
        <tbody>
        <?php $catTotal = 0; foreach ($items as $idx => $d): $catTotal += (float)$d['amount']; ?>
        <tr class="ded-row">
            <td><?= $idx + 1 ?></td>
            <td><?= htmlspecialchars($d['description']) ?></td>
            <td class="num"><?= $d['quantity'] ? number_format((float)$d['quantity'], 2) : '' ?></td>
            <td class="num"><?= $d['unit_cost'] ? number_format((float)$d['unit_cost'], 2) : '' ?></td>
            <td class="num"><?= number_format((float)$d['amount'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr style="font-weight:700;"><td colspan="4" class="num">Subtotal</td><td class="num">(<?= number_format($catTotal, 2) ?>)</td></tr>
        </tbody>
    </table>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Contributions -->
    <?php if (!empty($slipContributions)): ?>
    <div class="sec-title">Contributions</div>
    <table class="data-tbl">
        <thead><tr><th>#</th><th>Type</th><th>Previous (₱)</th><th>Current (₱)</th><th>Running Total (₱)</th></tr></thead>
        <tbody>
        <?php foreach ($slipContributions as $idx => $c): ?>
        <tr>
            <td><?= $idx + 1 ?></td>
            <td><?= htmlspecialchars($c['contribution_type']) ?></td>
            <td class="num"><?= number_format((float)$c['previous_amount'], 2) ?></td>
            <td class="num"><?= number_format((float)$c['current_amount'], 2) ?></td>
            <td class="num"><?= number_format((float)$c['running_total'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Summary -->
    <table class="data-tbl" style="margin-top:15px;">
        <tr class="total-row"><td class="num">GROSS PROCEEDS</td><td class="num">₱ <?= number_format((float)$slipData['gross_pay'], 2) ?></td></tr>
        <tr class="ded-row"><td class="num">Total Deductions</td><td class="num">(₱ <?= number_format((float)$slipData['deductions'], 2) ?>)</td></tr>
        <?php if ((float)($slipData['guaranteed_income'] ?? 0) > 0): ?>
        <tr><td class="num">Guaranteed Income</td><td class="num">₱ <?= number_format((float)$slipData['guaranteed_income'], 2) ?></td></tr>
        <?php endif; ?>
        <tr class="net-row"><td class="num">TAKE-HOME PAY</td><td class="num">₱ <?= number_format((float)$slipData['net_pay'], 2) ?></td></tr>
    </table>

    <!-- Signatures -->
    <div class="sig-area">
        <div class="sig-box"><strong>Computed By</strong><br><?= htmlspecialchars($slipData['computed_by_name'] ?? 'System') ?></div>
        <div class="sig-box"><strong>Approved By</strong><br><?= htmlspecialchars($slipData['approved_by_name'] ?? 'Admin') ?></div>
        <div class="sig-box"><strong>Received By (Worker)</strong><br><span style="font-size:9px;color:#888;">Signature over printed name</span></div>
    </div>

    <div class="footer-note">
        DARBCO IFS — System-generated Harvest Proceeds Slip<br>
        Transaction ID: <?= md5($slipData['payroll_id'] . $slipData['created_at']) ?>
    </div>
</div>
<script>window.addEventListener('load',()=>{setTimeout(()=>{window.print();},500);});</script>
</body>
</html>
