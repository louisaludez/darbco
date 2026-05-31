<?php
$content = file_get_contents('views/production/daily_reports.php');

$badHtml = <<<HTML
<div class="col-md-7">
                                            <table class="table table-sm table-bordered text-center align-middle mb-0">
                                                <tbody>
                                                    <tr>
                                                        <td rowspan="6" class="align-middle fw-bold bg-light" style="width: 25%; font-size: 1.1rem;">Hands</td>
                                                        <td rowspan="6" class="align-middle" style="width: 25%;">
                                                            <input type="number" name="summary_hands_main" class="form-control form-control-sm text-center fs-5">
                                                        </td>
                                                        <td colspan="2" class="text-center fw-bold bg-light" style="width: 50%;">Small Hands</td>
                                                    </tr>
                                                   
                                                    <tr>
                                                        <td class="text-start bg-light">789</td>
                                                        <td><input type="number" name="summary_789" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start bg-light">7.2 k</td>
                                                        <td><input type="number" name="summary_72k" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start bg-light">4.7 k</td>
                                                        <td><input type="number" name="summary_47k" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold bg-light">SP / CP</td>
                                                        <td><input type="number" name="summary_sp_cp" class="form-control form-control-sm"></td>
                                                        <td class="fw-bold bg-light text-start">TOTAL</td>
                                                        <td><input type="number" name="summary_hands_total" class="form-control form-control-sm bg-light fw-bold" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="2" class="fw-bold bg-light text-start">CLASS B</td>
                                                        <td colspan="2" rowspan="4" class="border-0"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="bg-light fw-bold text-start" style="padding-left: 20px;">Hands</td>
                                                        <td><input type="number" name="summary_class_b_hands" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="bg-light fw-bold text-start" style="padding-left: 20px;">CL/SML</td>
                                                        <td><input type="number" name="summary_class_b_sml" class="form-control form-control-sm"></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold bg-light text-start" style="padding-left: 20px;">TOTAL</td>
                                                        <td><input type="number" name="summary_class_b_total" class="form-control form-control-sm bg-light fw-bold" readonly></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="col-md-5">
                                            <table class="table table-sm table-bordered text-center align-middle mb-0">
                                                <tbody>
                                                    <tr>
                                                        <td colspan="2" class="text-center fw-bold bg-light fs-6">BOXES ALL - IN</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start fw-bold bg-light" style="font-size: 0.85rem;">HANDS, SP & SML H =</td>
                                                        <td><input type="number" name="summary_hands_sp_sml" class="form-control form-control-sm fw-bold bg-light" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start fw-bold bg-light">CLASS B =</td>
                                                        <td><input type="number" name="summary_class_b_grand" class="form-control form-control-sm fw-bold bg-light" readonly></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-start fw-bold bg-light fs-4 text-danger">TOTAL</td>
                                                        <td><input type="number" name="summary_grand_total" class="form-control form-control-sm bg-light fw-bold fs-4 text-danger" readonly></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
HTML;

$cleanHtml = <<<HTML
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
                                                        <td class="bg-light fw-bold text-start" style="width: 50%;">QTY</td>
                                                        <td style="width: 50%;"><input type="number" name="summary_small_hands" class="form-control form-control-sm text-center"></td>
                                                    </tr>
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
HTML;

$content = str_replace($badHtml, ltrim($cleanHtml), $content);
file_put_contents('views/production/daily_reports.php', $content);
echo "done";
?>
