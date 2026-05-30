<?php
// views/production/daily_production_per_beneficiary.php
$pageTitle = 'Daily Production Per Beneficiary';
require_once VIEW_PATH . 'layout/header.php';
$role = $_SESSION[SESS_ROLE];
?>
<div class="app-shell">
<?php require_once VIEW_PATH . 'layout/sidebar.php'; ?>
<main class="main-content">
    <div class="page-header">
        <div>
            <h1><i class="bi bi-person-lines-fill me-2 text-success"></i>Daily Production Per Beneficiary</h1>
        </div>
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

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"></h4>
        <?php if ($role === ROLE_PRODUCTION): ?>
            <button class="btn btn-darbco" data-bs-toggle="modal" data-bs-target="#addDPBModal">
                <i class="bi bi-plus-circle me-2"></i>New Record
            </button>
        <?php endif; ?>
    </div>

    <!-- Records Table -->
    <div class="table-card mb-4">
        <div class="card-header">Daily Production Per Beneficiary Records</div>
        <div class="table-responsive p-2">
            <table class="table table-hover darbco-table w-100">
                <thead>
                    <tr>
                        <th>Packing Date</th>
                        <th>Total Beneficiaries</th>
                        <th>Date Recorded</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dpbRecords)): ?>
                        <?php foreach ($dpbRecords as $record): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($record['packing_date'])) ?></td>
                                <td><span class="badge bg-secondary"><?= $record['total_beneficiaries'] ?></span></td>
                                <td><?= date('M d, Y h:i A', strtotime($record['created_at'])) ?></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary view-dpb-record" data-id="<?= $record['id'] ?>" title="View"><i class="bi bi-eye"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewDPBModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" style="max-width: 95%;">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="bi bi-eye me-2"></i>View Daily Production Per Beneficiary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">
                    <div id="viewDPBContent" class="text-center">
                        <div class="spinner-border text-primary my-4" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Record Modal -->
    <div class="modal fade" id="addDPBModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" style="max-width: 95%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>New Daily Production Per Beneficiary</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="dpbForm" method="POST" action="index.php?page=production&tab=daily_production_per_beneficiary&action=store_dpb">
                    <?= Csrf::field() ?>
                    <div class="modal-body bg-light">
                        <div class="card shadow-sm border-0 mb-3">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">Packing Date *</label>
                                        <input type="date" class="form-control" name="packing_date" id="packing_date" required value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm text-center align-middle" id="dpbTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th colspan="3" class="border-bottom-0"></th>
                                                <th colspan="7" class="fw-bold text-success border-bottom-0">CLASS A</th>
                                                <th colspan="2" class="fw-bold text-warning border-bottom-0 text-dark">CLASS B</th>
                                                <th rowspan="2" style="width: 50px;"></th>
                                            </tr>
                                            <tr>
                                                <th style="min-width: 80px;">SUB<br>CODE</th>
                                                <th style="min-width: 150px;">ARB's Name</th>
                                                <th style="min-width: 80px;">Stems<br>Cut</th>
                                                <th style="min-width: 70px;">Hands</th>
                                                <th style="min-width: 70px;">SH</th>
                                                <th style="min-width: 70px;">(Blank)</th>
                                                <th style="min-width: 70px;">F.P</th>
                                                <th style="min-width: 70px;">(Blank)</th>
                                                <th style="min-width: 70px;">(Blank)</th>
                                                <th style="min-width: 70px;">CL-B</th>
                                                <th style="min-width: 70px;">H</th>
                                                <th style="min-width: 70px;">I/D</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dpbTableBody">
                                            <!-- Rows will be added dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="addRowBtn">
                                    <i class="bi bi-plus"></i> Add Row
                                </button>
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

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const tbody = document.getElementById('dpbTableBody');
        const addRowBtn = document.getElementById('addRowBtn');

        function createRow() {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" name="sub_code[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="arb_name[]" class="form-control form-control-sm text-start"></td>
                <td><input type="text" name="stems_cut[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="class_a_hands[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="class_a_sh[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="class_a_blank1[]" class="form-control form-control-sm bg-light border-dashed"></td>
                <td><input type="text" name="class_a_fp[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="class_a_blank2[]" class="form-control form-control-sm bg-light border-dashed"></td>
                <td><input type="text" name="class_a_blank3[]" class="form-control form-control-sm bg-light border-dashed"></td>
                <td><input type="text" name="class_a_cl_b[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="class_b_h[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="class_b_id[]" class="form-control form-control-sm"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button></td>
            `;
            return tr;
        }

        // Add initial 5 rows
        for(let i=0; i<5; i++) {
            tbody.appendChild(createRow());
        }

        addRowBtn.addEventListener('click', () => {
            tbody.appendChild(createRow());
        });

        tbody.addEventListener('click', (e) => {
            if(e.target.closest('.remove-row')) {
                e.target.closest('tr').remove();
                if(tbody.children.length === 0) {
                    tbody.appendChild(createRow());
                }
            }
        });

        // Handle View Record
        document.querySelectorAll('.view-dpb-record').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.getAttribute('data-id');
                const modal = new bootstrap.Modal(document.getElementById('viewDPBModal'));
                const contentDiv = document.getElementById('viewDPBContent');
                
                contentDiv.innerHTML = '<div class="spinner-border text-primary my-4" role="status"></div>';
                modal.show();
                
                try {
                    const response = await fetch(`index.php?page=production&tab=daily_production_per_beneficiary&action=get_dpb_record&id=${id}`);
                    const res = await response.json();
                    
                    if (res.success) {
                        const data = res.data;
                        let html = `<div class="card shadow-sm border-0 mb-3"><div class="card-body">
                            <div class="row text-start mb-3">
                                <div class="col-md-4"><strong>Packing Date:</strong> ${data.packing_date}</div>
                            </div>
                            <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th colspan="3"></th>
                                        <th colspan="7" class="fw-bold text-success">CLASS A</th>
                                        <th colspan="2" class="fw-bold text-warning text-dark">CLASS B</th>
                                    </tr>
                                    <tr>
                                        <th>SUB CODE</th><th>ARB's Name</th><th>Stems Cut</th>
                                        <th>Hands</th><th>SH</th><th>(Blank)</th><th>F.P</th><th>(Blank)</th><th>(Blank)</th><th>CL-B</th>
                                        <th>H</th><th>I/D</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                        
                        if (data.items && data.items.length > 0) {
                            data.items.forEach(item => {
                                html += `<tr>
                                    <td>${item.sub_code || ''}</td>
                                    <td class="text-start">${item.arb_name || ''}</td>
                                    <td>${item.stems_cut || ''}</td>
                                    <td>${item.class_a_hands || ''}</td>
                                    <td>${item.class_a_sh || ''}</td>
                                    <td class="bg-light">${item.class_a_blank1 || ''}</td>
                                    <td>${item.class_a_fp || ''}</td>
                                    <td class="bg-light">${item.class_a_blank2 || ''}</td>
                                    <td class="bg-light">${item.class_a_blank3 || ''}</td>
                                    <td>${item.class_a_cl_b || ''}</td>
                                    <td>${item.class_b_h || ''}</td>
                                    <td>${item.class_b_id || ''}</td>
                                </tr>`;
                            });
                        } else {
                            html += `<tr><td colspan="12">No beneficiaries recorded.</td></tr>`;
                        }
                        html += `</tbody></table></div></div></div>`;
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
    });
    </script>
    
    <style>
        .border-dashed { border: 1px dashed #dee2e6 !important; }
    </style>
</main>
</div>
<?php require_once VIEW_PATH . 'layout/footer.php'; ?>
