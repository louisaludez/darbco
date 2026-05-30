<?php
// views/production/daily_boxes_per_group.php
$pageTitle = 'Daily Boxes Per Group';
require_once VIEW_PATH . 'layout/header.php';
$role = $_SESSION[SESS_ROLE];
?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">
    <div class="page-header">
        <div>
            <h1><i class="bi bi-box-seam me-2 text-success"></i>Daily Boxes Per Group</h1>
        </div>
        <?php if ($role === ROLE_PRODUCTION): ?>
        <button class="btn btn-darbco" data-bs-toggle="modal" data-bs-target="#addDailyBoxesModal">
            <i class="bi bi-plus-circle me-2"></i>New Record
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

    <?php if (!empty($message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <!-- Records Table -->
    <div class="table-card mb-4">
        <div class="card-header">Daily Boxes Records</div>
        <div class="table-responsive p-2">
            <table class="table table-hover darbco-table w-100">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>First Box Out</th>
                        <th>Last Box Out</th>
                        <th>Total Class A</th>
                        <th>Total Class B</th>
                        <th>Total Boxes</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dbRecords)): ?>
                        <?php foreach ($dbRecords as $record): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($record['packing_date'])) ?></td>
                                <td><?= $record['first_box_out'] ? date('h:i A', strtotime($record['first_box_out'])) : '-' ?></td>
                                <td><?= $record['last_box_out'] ? date('h:i A', strtotime($record['last_box_out'])) : '-' ?></td>
                                <td><?= number_format((float) $record['total_class_a'], 0) ?></td>
                                <td><?= number_format((float) $record['total_class_b'], 0) ?></td>
                                <td><strong class="text-primary"><?= number_format((float) $record['total_boxes'], 0) ?></strong></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary view-db-record" data-id="<?= $record['id'] ?>" title="View"><i class="bi bi-eye"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

        <!-- View Daily Boxes Modal -->
    <div class="modal fade" id="viewDailyBoxesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-eye me-2"></i>View Daily Boxes Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">
                    <div id="viewDailyBoxesContent" class="text-center">
                        <div class="spinner-border text-primary my-4" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Daily Boxes Modal -->
    <div class="modal fade" id="addDailyBoxesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 pt-0">
            <h4 class="text-center fw-bold mb-0">DAILY BOXES PER GROUP</h4>
            <p class="text-center text-muted mb-4">DARBCO-IFS (Main PP)</p>
            
            <form id="dailyBoxesForm" method="POST" action="index.php?page=production&tab=daily_boxes_per_group&action=store_db">
                <?= Csrf::field() ?>
                <div class="row mb-3 px-2">
                    <div class="col-md-3">
                        <div class="input-group input-group-sm fw-bold">
                            <span class="input-group-text bg-white border-0 fw-bold">DATE:</span>
                            <input type="date" name="db_date" class="form-control border-0 border-bottom border-dark rounded-0 fw-bold" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group input-group-sm fw-bold">
                            <span class="input-group-text bg-white border-0 fw-bold">First box out Time:</span>
                            <input type="time" name="db_first_time" class="form-control border-0 border-bottom border-dark rounded-0 fw-bold">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group input-group-sm fw-bold">
                            <span class="input-group-text bg-white border-0 fw-bold">Last box out Time:</span>
                            <input type="time" name="db_last_time" class="form-control border-0 border-bottom border-dark rounded-0 fw-bold">
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center align-middle border-dark" style="font-size: 0.85rem;" id="dailyBoxesTable">
                        <thead class="align-middle">
                            <tr>
                                <th rowspan="2" style="width: 16%;" class="fs-6 fw-bold">CLASS - A Boxes</th>
                                <th colspan="3" class="fs-6 fw-bold">GROUP 1</th>
                                <th colspan="3" class="fs-6 fw-bold">GROUP 3</th>
                                <th rowspan="2" style="width: 14%;" class="fs-6 fw-bold">TOTAL BOXES<br>PRODUCED</th>
                            </tr>
                            <tr>
                                <th style="width: 10%;">TALLY</th>
                                <th style="width: 10%;">ADJ.</th>
                                <th style="width: 10%;">SHOULD BE</th>
                                <th style="width: 10%;">TALLY</th>
                                <th style="width: 10%;">ADJ.</th>
                                <th style="width: 10%;">SHOULD BE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $classARows = [
                                '4/5/6 Hands',
                                '7/8/9 Hands',
                                '4.7 k',
                                '7.2 k',
                                'BCP',
                                'BCP',
                                'BCP',
                                'BCP'
                            ];
                            foreach ($classARows as $idx => $rowLabel):
                            ?>
                            <tr>
                                <th class="text-center fw-normal"><?= $rowLabel ?></th>
                                <td class="p-0"><input type="number" name="g1_tally_a[]" class="form-control form-control-sm text-center border-0 rounded-0 h-100 a-tally"></td>
                                <td class="p-0"><input type="number" name="g1_adj_a[]" class="form-control form-control-sm text-center border-0 rounded-0 h-100 a-adj"></td>
                                <td class="p-0"><input type="number" name="g1_should_a[]" class="form-control form-control-sm text-center border-0 rounded-0 h-100 a-should"></td>
                                <td class="p-0"><input type="number" name="g3_tally_a[]" class="form-control form-control-sm text-center border-0 rounded-0 h-100 a-tally"></td>
                                <td class="p-0"><input type="number" name="g3_adj_a[]" class="form-control form-control-sm text-center border-0 rounded-0 h-100 a-adj"></td>
                                <td class="p-0"><input type="number" name="g3_should_a[]" class="form-control form-control-sm text-center border-0 rounded-0 h-100 a-should"></td>
                                <td class="p-0 bg-light"><input type="number" name="total_a[]" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold bg-transparent row-total-a" readonly></td>
                            </tr>
                            <?php endforeach; ?>
                            <!-- Empty / Black Row -->
                            <tr class="bg-dark">
                                <td colspan="8" style="height: 15px;"></td>
                            </tr>
                            <!-- Class A Total -->
                            <tr>
                                <th class="text-center fs-6 fw-bold">TOTAL</th>
                                <td class="p-0 bg-light"><input type="number" id="tot_g1_tally_a" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold bg-transparent" readonly></td>
                                <td class="p-0 bg-light"><input type="number" id="tot_g1_adj_a" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold bg-transparent" readonly></td>
                                <td class="p-0 bg-light"><input type="number" id="tot_g1_should_a" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold bg-transparent" readonly></td>
                                <td class="p-0 bg-light"><input type="number" id="tot_g3_tally_a" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold bg-transparent" readonly></td>
                                <td class="p-0 bg-light"><input type="number" id="tot_g3_adj_a" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold bg-transparent" readonly></td>
                                <td class="p-0 bg-light"><input type="number" id="tot_g3_should_a" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold bg-transparent" readonly></td>
                                <td class="p-0 bg-light"><input type="number" id="grand_tot_a" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold fs-6 bg-transparent text-primary" readonly></td>
                            </tr>
                            
                            <!-- CLASS B SECTION -->
                            <tr>
                                <th class="text-center fs-6 fw-bold border-top-0 border-bottom-0">CLASS- B Boxes</th>
                                <th class="fw-bold">TALLY</th>
                                <th class="fw-bold">ADJ.</th>
                                <th class="fw-bold">SHOULD BE</th>
                                <th class="fw-bold">TALLY</th>
                                <th class="fw-bold">ADJ.</th>
                                <th class="fw-bold">SHOULD BE</th>
                                <th class="fs-6 fw-bold">TOTAL BOXES</th>
                            </tr>
                            <?php 
                            $classBRows = [
                                '4/5/6 Hands',
                                'Sml H / Clusters',
                                'F. P'
                            ];
                            foreach ($classBRows as $idx => $rowLabel):
                            ?>
                            <tr>
                                <th class="text-center fw-normal"><?= $rowLabel ?></th>
                                <td class="p-0"><input type="number" name="g1_tally_b[]" class="form-control form-control-sm text-center border-0 rounded-0 h-100 b-tally"></td>
                                <td class="p-0"><input type="number" name="g1_adj_b[]" class="form-control form-control-sm text-center border-0 rounded-0 h-100 b-adj"></td>
                                <td class="p-0"><input type="number" name="g1_should_b[]" class="form-control form-control-sm text-center border-0 rounded-0 h-100 b-should"></td>
                                <td class="p-0"><input type="number" name="g3_tally_b[]" class="form-control form-control-sm text-center border-0 rounded-0 h-100 b-tally"></td>
                                <td class="p-0"><input type="number" name="g3_adj_b[]" class="form-control form-control-sm text-center border-0 rounded-0 h-100 b-adj"></td>
                                <td class="p-0"><input type="number" name="g3_should_b[]" class="form-control form-control-sm text-center border-0 rounded-0 h-100 b-should"></td>
                                <td class="p-0 bg-light"><input type="number" name="total_b[]" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold bg-transparent row-total-b" readonly></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <!-- Class B Total -->
                            <tr>
                                <th class="text-center fs-6 fw-bold">TOTAL</th>
                                <td class="p-0 bg-light"><input type="number" id="tot_g1_tally_b" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold bg-transparent" readonly></td>
                                <td class="p-0 bg-light"><input type="number" id="tot_g1_adj_b" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold bg-transparent" readonly></td>
                                <td class="p-0 bg-light"><input type="number" id="tot_g1_should_b" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold bg-transparent" readonly></td>
                                <td class="p-0 bg-light"><input type="number" id="tot_g3_tally_b" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold bg-transparent" readonly></td>
                                <td class="p-0 bg-light"><input type="number" id="tot_g3_adj_b" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold bg-transparent" readonly></td>
                                <td class="p-0 bg-light"><input type="number" id="tot_g3_should_b" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold bg-transparent" readonly></td>
                                <td class="p-0 bg-light"><input type="number" id="grand_tot_b" class="form-control form-control-sm text-center border-0 rounded-0 h-100 fw-bold fs-6 bg-transparent text-primary" readonly></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-end mt-3">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-darbco"><i class="bi bi-save me-2"></i>Save Daily Boxes</button>
                </div>
            </form>
        </div>
    </div>
    </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const table = document.getElementById('dailyBoxesTable');
        
        function calculateSums() {
            // Class A
            const aTally1Inputs = document.querySelectorAll('input[name="g1_tally_a[]"]');
            const aAdj1Inputs = document.querySelectorAll('input[name="g1_adj_a[]"]');
            const aShould1Inputs = document.querySelectorAll('input[name="g1_should_a[]"]');
            
            const aTally3Inputs = document.querySelectorAll('input[name="g3_tally_a[]"]');
            const aAdj3Inputs = document.querySelectorAll('input[name="g3_adj_a[]"]');
            const aShould3Inputs = document.querySelectorAll('input[name="g3_should_a[]"]');
            
            const aRowTotals = document.querySelectorAll('.row-total-a');
            
            let g1TallyATot = 0, g1AdjATot = 0, g1ShouldATot = 0;
            let g3TallyATot = 0, g3AdjATot = 0, g3ShouldATot = 0;
            let grandTotA = 0;
            
            for (let i = 0; i < aTally1Inputs.length; i++) {
                const t1 = parseFloat(aTally1Inputs[i].value) || 0;
                const a1 = parseFloat(aAdj1Inputs[i].value) || 0;
                const s1 = parseFloat(aShould1Inputs[i].value) || 0;
                
                const t3 = parseFloat(aTally3Inputs[i].value) || 0;
                const a3 = parseFloat(aAdj3Inputs[i].value) || 0;
                const s3 = parseFloat(aShould3Inputs[i].value) || 0;
                
                // Usually "TOTAL BOXES PRODUCED" is the sum of SHOULD BE from both groups? Or sum of Tally + Adj?
                // The user requested to NOT include the SHOULD BE values in the total.
                const rowTot = t1 + a1 + t3 + a3; 
                aRowTotals[i].value = rowTot > 0 ? rowTot : '';
                
                g1TallyATot += t1; g1AdjATot += a1; g1ShouldATot += s1;
                g3TallyATot += t3; g3AdjATot += a3; g3ShouldATot += s3;
                grandTotA += rowTot;
            }
            
            document.getElementById('tot_g1_tally_a').value = g1TallyATot > 0 ? g1TallyATot : '';
            document.getElementById('tot_g1_adj_a').value = g1AdjATot > 0 ? g1AdjATot : '';
            document.getElementById('tot_g1_should_a').value = g1ShouldATot > 0 ? g1ShouldATot : '';
            document.getElementById('tot_g3_tally_a').value = g3TallyATot > 0 ? g3TallyATot : '';
            document.getElementById('tot_g3_adj_a').value = g3AdjATot > 0 ? g3AdjATot : '';
            document.getElementById('tot_g3_should_a').value = g3ShouldATot > 0 ? g3ShouldATot : '';
            document.getElementById('grand_tot_a').value = grandTotA > 0 ? grandTotA : '';
            
            
            // Class B
            const bTally1Inputs = document.querySelectorAll('input[name="g1_tally_b[]"]');
            const bAdj1Inputs = document.querySelectorAll('input[name="g1_adj_b[]"]');
            const bShould1Inputs = document.querySelectorAll('input[name="g1_should_b[]"]');
            
            const bTally3Inputs = document.querySelectorAll('input[name="g3_tally_b[]"]');
            const bAdj3Inputs = document.querySelectorAll('input[name="g3_adj_b[]"]');
            const bShould3Inputs = document.querySelectorAll('input[name="g3_should_b[]"]');
            
            const bRowTotals = document.querySelectorAll('.row-total-b');
            
            let g1TallyBTot = 0, g1AdjBTot = 0, g1ShouldBTot = 0;
            let g3TallyBTot = 0, g3AdjBTot = 0, g3ShouldBTot = 0;
            let grandTotB = 0;
            
            for (let i = 0; i < bTally1Inputs.length; i++) {
                const t1 = parseFloat(bTally1Inputs[i].value) || 0;
                const a1 = parseFloat(bAdj1Inputs[i].value) || 0;
                const s1 = parseFloat(bShould1Inputs[i].value) || 0;
                
                const t3 = parseFloat(bTally3Inputs[i].value) || 0;
                const a3 = parseFloat(bAdj3Inputs[i].value) || 0;
                const s3 = parseFloat(bShould3Inputs[i].value) || 0;
                
                const rowTot = t1 + a1 + t3 + a3; 
                bRowTotals[i].value = rowTot > 0 ? rowTot : '';
                
                g1TallyBTot += t1; g1AdjBTot += a1; g1ShouldBTot += s1;
                g3TallyBTot += t3; g3AdjBTot += a3; g3ShouldBTot += s3;
                grandTotB += rowTot;
            }
            
            document.getElementById('tot_g1_tally_b').value = g1TallyBTot > 0 ? g1TallyBTot : '';
            document.getElementById('tot_g1_adj_b').value = g1AdjBTot > 0 ? g1AdjBTot : '';
            document.getElementById('tot_g1_should_b').value = g1ShouldBTot > 0 ? g1ShouldBTot : '';
            document.getElementById('tot_g3_tally_b').value = g3TallyBTot > 0 ? g3TallyBTot : '';
            document.getElementById('tot_g3_adj_b').value = g3AdjBTot > 0 ? g3AdjBTot : '';
            document.getElementById('tot_g3_should_b').value = g3ShouldBTot > 0 ? g3ShouldBTot : '';
            document.getElementById('grand_tot_b').value = grandTotB > 0 ? grandTotB : '';
        }
        
        table.addEventListener('input', calculateSums);
    });

    // Handle View Record
    document.querySelectorAll('.view-db-record').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.getAttribute('data-id');
            const modal = new bootstrap.Modal(document.getElementById('viewDailyBoxesModal'));
            const contentDiv = document.getElementById('viewDailyBoxesContent');
            
            contentDiv.innerHTML = '<div class="spinner-border text-primary my-4" role="status"></div>';
            modal.show();
            
            try {
                const response = await fetch(`index.php?page=production&tab=daily_boxes_per_group&action=get_db_record&id=${id}`);
                const res = await response.json();
                
                if (res.success) {
                    const data = res.data;
                    let html = `<div class="card shadow-sm border-0 mb-3"><div class="card-body">
                        <div class="row text-start mb-3">
                            <div class="col-md-4"><strong>Packing Date:</strong> ${data.packing_date}</div>
                            <div class="col-md-4"><strong>First Box Out:</strong> ${data.first_box_out || '-'}</div>
                            <div class="col-md-4"><strong>Last Box Out:</strong> ${data.last_box_out || '-'}</div>
                        </div>
                        <table class="table table-bordered table-sm text-center">
                            <thead class="table-light">
                                <tr>
                                    <th>Class</th><th>Row / Spec</th><th>Group</th><th>Tally</th><th>Adj</th><th>Should Be</th>
                                </tr>
                            </thead>
                            <tbody>`;
                    
                    if (data.items && data.items.length > 0) {
                        data.items.forEach(item => {
                            html += `<tr>
                                <td>${item.class}</td>
                                <td>${item.row_label}</td>
                                <td>Group ${item.group_num}</td>
                                <td>${item.tally}</td>
                                <td>${item.adj}</td>
                                <td>${item.should_be}</td>
                            </tr>`;
                        });
                    } else {
                        html += `<tr><td colspan="6">No detailed matrix data available.</td></tr>`;
                    }
                    html += `</tbody></table></div></div>`;
                    contentDiv.innerHTML = html;
                } else {
                    contentDiv.innerHTML = `<div class="alert alert-danger">${res.message}</div>`;
                }
            } catch (err) {
                console.error(err);
                contentDiv.innerHTML = `<div class="alert alert-danger">Failed to load record details.</div>`;
            }
        });
    });
    </script>
</main>
</div>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
