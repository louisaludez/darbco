<?php
$content = file_get_contents('views/production/daily_reports.php');

$classASpecs = "['4 Hands', '5 Hands', '6 Hands', '7 Hands', '8 Hands', '9 Hands', '7.2 K', '4.7 K', '100L', '33', '30', 'TRIO 30', '24', '28', 'BCP 24', '25', '27']";
$classBSpecs = "['4 Hands', '5 Hands', '6 Hands', '7 Hands', '8 Hands', '9 Hands', 'FP']";

$newModalBody = <<<HTML
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
                                        <div class="col-md-9">
                                            <div class="row g-3">
                                                <div class="col-md-3"><label class="form-label">Date *</label><input type="date" name="report_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
                                                <div class="col-md-3"><label class="form-label">Week No.</label><input type="text" name="week_no" class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Brand Name</label><input type="text" name="brand_name" class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Crew Size</label><input type="number" name="crew_size" class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Per Pack Plan</label><input type="text" name="per_pack_plan" class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">First Fruit In</label><input type="time" name="first_fruit_in" class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Last Fruit In</label><input type="time" name="last_fruit_in" class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">First Box Out</label><input type="time" name="first_box_out" class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Last Box Out (Top)</label><input type="time" name="last_box_out" class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">Vol Stems Cut</label><input type="number" name="volume_stems_cut" class="form-control"></div>
                                                <div class="col-md-3"><label class="form-label">B/S Ratio</label><input type="text" name="bs_ratio" class="form-control"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <h6 class="text-secondary fw-bold" style="font-size:0.9rem;">EFFICIENCY PERFORMANCE</h6>
                                            <table class="table table-sm table-bordered text-center align-middle">
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
                                                            <th colspan="3">GRP 1</th>
                                                            <th colspan="3">GRP 3</th>
                                                            <th colspan="3">HMLND</th>
                                                            <th rowspan="2" class="align-middle" style="width:80px; font-size: 0.8rem;">TOTAL BOXES<br>PROD.</th>
                                                        </tr>
                                                        <tr>
                                                            <th style="width:60px; font-size: 0.8rem;">TALLY</th>
                                                            <th style="width:60px; font-size: 0.8rem;">ADJ.</th>
                                                            <th style="width:60px; font-size: 0.8rem;">SHOULD<br>BE</th>
                                                            <th style="width:60px; font-size: 0.8rem;">TALLY</th>
                                                            <th style="width:60px; font-size: 0.8rem;">ADJ.</th>
                                                            <th style="width:60px; font-size: 0.8rem;">SHOULD<br>BE</th>
                                                            <th style="width:60px; font-size: 0.8rem;">TALLY</th>
                                                            <th style="width:60px; font-size: 0.8rem;">ADJ.</th>
                                                            <th style="width:60px; font-size: 0.8rem;">SHOULD<br>BE</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php \$classASpecs = {$classASpecs}; ?>
                                                        <?php foreach (\$classASpecs as \$s): ?>
                                                        <tr>
                                                            <td class="text-start fw-semibold"><?= \$s ?></td>
                                                            <!-- GRP 1 -->
                                                            <td><input type="hidden" name="box_class[]" value="A"><input type="hidden" name="box_spec[]" value="<?= \$s ?>"><input type="hidden" name="box_group[]" value="GROUP 1"><input type="number" name="box_tally[]" class="form-control form-control-sm grp1-tally"></td>
                                                            <td><input type="number" name="box_adj[]" class="form-control form-control-sm grp1-adj"></td>
                                                            <td><input type="number" name="box_should[]" class="form-control form-control-sm grp1-should"></td>
                                                            <!-- GRP 3 -->
                                                            <td><input type="hidden" name="box_class[]" value="A"><input type="hidden" name="box_spec[]" value="<?= \$s ?>"><input type="hidden" name="box_group[]" value="GROUP 3"><input type="number" name="box_tally[]" class="form-control form-control-sm grp3-tally"></td>
                                                            <td><input type="number" name="box_adj[]" class="form-control form-control-sm grp3-adj"></td>
                                                            <td><input type="number" name="box_should[]" class="form-control form-control-sm grp3-should"></td>
                                                            <!-- HMLND -->
                                                            <td><input type="hidden" name="box_class[]" value="A"><input type="hidden" name="box_spec[]" value="<?= \$s ?>"><input type="hidden" name="box_group[]" value="HMLND"><input type="number" name="box_tally[]" class="form-control form-control-sm hmlnd-tally"></td>
                                                            <td><input type="number" name="box_adj[]" class="form-control form-control-sm hmlnd-adj"></td>
                                                            <td><input type="number" name="box_should[]" class="form-control form-control-sm hmlnd-should"></td>
                                                            <!-- TOTAL -->
                                                            <td><input type="number" class="form-control form-control-sm bg-light row-total-a fw-bold" readonly></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        <tr class="fw-bold bg-light">
                                                            <td class="text-end">TOTAL=</td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g1-tally" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g1-adj" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g1-should" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g3-tally" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g3-adj" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g3-should" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-hm-tally" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-hm-adj" readonly></td>
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
                                                        <?php foreach (\$classASpecs as \$s): ?>
                                                        <tr>
                                                            <td class="text-start"><?= \$s ?></td>
                                                            <td><input type="hidden" name="box_class[]" value="A"><input type="hidden" name="box_spec[]" value="<?= \$s ?>"><input type="hidden" name="box_group[]" value="BRAND"><input type="hidden" name="box_adj[]" value="0"><input type="hidden" name="box_should[]" value="0"><input type="number" name="box_tally[]" class="form-control form-control-sm"></td>
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
                                                            <th colspan="3">GRP 1</th>
                                                            <th colspan="3">GRP 3</th>
                                                            <th colspan="3">HMLND</th>
                                                            <th rowspan="2" class="align-middle" style="width:80px; font-size: 0.8rem;">TOTAL BOXES<br>PROD.</th>
                                                        </tr>
                                                        <tr>
                                                            <th style="width:60px; font-size: 0.8rem;">TALLY</th>
                                                            <th style="width:60px; font-size: 0.8rem;">ADJ.</th>
                                                            <th style="width:60px; font-size: 0.8rem;">SHOULD<br>BE</th>
                                                            <th style="width:60px; font-size: 0.8rem;">TALLY</th>
                                                            <th style="width:60px; font-size: 0.8rem;">ADJ.</th>
                                                            <th style="width:60px; font-size: 0.8rem;">SHOULD<br>BE</th>
                                                            <th style="width:60px; font-size: 0.8rem;">TALLY</th>
                                                            <th style="width:60px; font-size: 0.8rem;">ADJ.</th>
                                                            <th style="width:60px; font-size: 0.8rem;">SHOULD<br>BE</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php \$classBSpecs = {$classBSpecs}; ?>
                                                        <?php foreach (\$classBSpecs as \$s): ?>
                                                        <tr>
                                                            <td class="text-start fw-semibold"><?= \$s ?></td>
                                                            <!-- GRP 1 -->
                                                            <td><input type="hidden" name="box_class[]" value="B"><input type="hidden" name="box_spec[]" value="<?= \$s ?>"><input type="hidden" name="box_group[]" value="GROUP 1"><input type="number" name="box_tally[]" class="form-control form-control-sm grp1-tally"></td>
                                                            <td><input type="number" name="box_adj[]" class="form-control form-control-sm grp1-adj"></td>
                                                            <td><input type="number" name="box_should[]" class="form-control form-control-sm grp1-should"></td>
                                                            <!-- GRP 3 -->
                                                            <td><input type="hidden" name="box_class[]" value="B"><input type="hidden" name="box_spec[]" value="<?= \$s ?>"><input type="hidden" name="box_group[]" value="GROUP 3"><input type="number" name="box_tally[]" class="form-control form-control-sm grp3-tally"></td>
                                                            <td><input type="number" name="box_adj[]" class="form-control form-control-sm grp3-adj"></td>
                                                            <td><input type="number" name="box_should[]" class="form-control form-control-sm grp3-should"></td>
                                                            <!-- HMLND -->
                                                            <td><input type="hidden" name="box_class[]" value="B"><input type="hidden" name="box_spec[]" value="<?= \$s ?>"><input type="hidden" name="box_group[]" value="HMLND"><input type="number" name="box_tally[]" class="form-control form-control-sm hmlnd-tally"></td>
                                                            <td><input type="number" name="box_adj[]" class="form-control form-control-sm hmlnd-adj"></td>
                                                            <td><input type="number" name="box_should[]" class="form-control form-control-sm hmlnd-should"></td>
                                                            <!-- TOTAL -->
                                                            <td><input type="number" class="form-control form-control-sm bg-light row-total-b fw-bold" readonly></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        <tr class="fw-bold bg-light">
                                                            <td class="text-end">TOTAL=</td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g1-tally" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g1-adj" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g1-should" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g3-tally" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g3-adj" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-g3-should" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-hm-tally" readonly></td>
                                                            <td><input type="number" class="form-control form-control-sm bg-light fw-bold total-hm-adj" readonly></td>
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
                                                        <?php foreach (\$classBSpecs as \$s): ?>
                                                        <tr>
                                                            <td class="text-start"><?= \$s ?></td>
                                                            <td><input type="hidden" name="box_class[]" value="B"><input type="hidden" name="box_spec[]" value="<?= \$s ?>"><input type="hidden" name="box_group[]" value="BRAND"><input type="hidden" name="box_adj[]" value="0"><input type="hidden" name="box_should[]" value="0"><input type="number" name="box_tally[]" class="form-control form-control-sm"></td>
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
                                        <div class="col-md-6">
                                            <table class="table table-sm table-bordered text-center align-middle h-100">
                                                <tbody>
                                                    <tr>
                                                        <td rowspan="5" class="align-middle fw-bold bg-light" style="width:20%">Hands</td>
                                                        <td class="text-start">Small Hands</td>
                                                        <td><input type="number" name="summary_small_hands" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start">7 & 9</td>
                                                        <td><input type="number" name="summary_7_9" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start">7.2 K</td>
                                                        <td><input type="number" name="summary_72k" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start">4.7 K</td>
                                                        <td><input type="number" name="summary_47k" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start fw-bold bg-light">TOTAL</td>
                                                        <td><input type="number" name="summary_hands_total" class="form-control form-control-sm bg-light fw-bold" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold bg-light">SP / CP</td>
                                                        <td class="text-start"></td>
                                                        <td><input type="number" name="summary_sp_cp" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold bg-light" style="font-size:0.8rem">CLASS B</td>
                                                        <td class="text-start" style="font-size:0.8rem">Hands<br>CL / SML<br>TOTAL</td>
                                                        <td class="align-middle">
                                                            <input type="number" name="summary_class_b_hands" class="form-control form-control-sm mb-1">
                                                            <input type="number" name="summary_class_b_sml" class="form-control form-control-sm mb-1">
                                                            <input type="number" name="summary_class_b_total" class="form-control form-control-sm bg-light fw-bold" readonly>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-bordered text-center align-middle h-100">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-start fw-bold bg-light fs-6" style="width: 55%;">BOXES ALL - IN</td>
                                                        <td><input type="number" name="summary_all_in" class="form-control form-control-sm fs-6"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start fw-bold bg-light" style="font-size:0.85rem">HANDS, SP & SML H =</td>
                                                        <td><input type="number" name="summary_hands_sp_sml" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start fw-bold bg-light">CLASS B =</td>
                                                        <td><input type="number" name="summary_class_b_grand" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start fw-bold bg-light fs-4 text-danger">TOTAL</td>
                                                        <td><input type="number" name="summary_grand_total" class="form-control form-control-sm bg-light fw-bold fs-4 text-danger" readonly></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
HTML;

$content = preg_replace('/<div class="modal-body bg-light">.*?<\/div>\s*<div class="modal-footer">/s', $newModalBody . "\n" . '                    <div class="modal-footer">', $content);

file_put_contents('views/production/daily_reports.php', $content);
echo "done";
?>
