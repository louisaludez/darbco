<?php
$content = file_get_contents('views/production/daily_reports.php');

$newJs = <<<HTML
<script>
document.addEventListener('DOMContentLoaded', () => {
    function calculateMatrixTotals(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const calcRow = (row) => {
            // Group 1
            const g1TallyVal = row.querySelector('.grp1-tally')?.value;
            const g1AdjVal = row.querySelector('.grp1-adj')?.value;
            const g1ShouldInput = row.querySelector('.grp1-should');
            
            let g1Should = 0;
            if (g1TallyVal !== '' || g1AdjVal !== '') {
                g1Should = (parseInt(g1TallyVal) || 0) + (parseInt(g1AdjVal) || 0);
                if (g1ShouldInput) g1ShouldInput.value = g1Should;
            } else {
                if (g1ShouldInput) g1ShouldInput.value = '';
            }

            // Group 3
            const g3TallyVal = row.querySelector('.grp3-tally')?.value;
            const g3AdjVal = row.querySelector('.grp3-adj')?.value;
            const g3ShouldInput = row.querySelector('.grp3-should');
            
            let g3Should = 0;
            if (g3TallyVal !== '' || g3AdjVal !== '') {
                g3Should = (parseInt(g3TallyVal) || 0) + (parseInt(g3AdjVal) || 0);
                if (g3ShouldInput) g3ShouldInput.value = g3Should;
            } else {
                if (g3ShouldInput) g3ShouldInput.value = '';
            }

            // HMLND
            const hmTallyVal = row.querySelector('.hmlnd-tally')?.value;
            const hmAdjVal = row.querySelector('.hmlnd-adj')?.value;
            const hmShouldInput = row.querySelector('.hmlnd-should');
            
            let hmShould = 0;
            if (hmTallyVal !== '' || hmAdjVal !== '') {
                hmShould = (parseInt(hmTallyVal) || 0) + (parseInt(hmAdjVal) || 0);
                if (hmShouldInput) hmShouldInput.value = hmShould;
            } else {
                if (hmShouldInput) hmShouldInput.value = '';
            }

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
            
            let totalG1Tally = 0, totalG1Adj = 0, totalG1Should = 0;
            let totalG3Tally = 0, totalG3Adj = 0, totalG3Should = 0;
            let totalHmTally = 0, totalHmAdj = 0, totalHmShould = 0;
            let grandTotal = 0;

            rows.forEach(row => {
                calcRow(row);
                
                totalG1Tally += parseInt(row.querySelector('.grp1-tally')?.value || 0);
                totalG1Adj += parseInt(row.querySelector('.grp1-adj')?.value || 0);
                totalG1Should += parseInt(row.querySelector('.grp1-should')?.value || 0);
                
                totalG3Tally += parseInt(row.querySelector('.grp3-tally')?.value || 0);
                totalG3Adj += parseInt(row.querySelector('.grp3-adj')?.value || 0);
                totalG3Should += parseInt(row.querySelector('.grp3-should')?.value || 0);
                
                totalHmTally += parseInt(row.querySelector('.hmlnd-tally')?.value || 0);
                totalHmAdj += parseInt(row.querySelector('.hmlnd-adj')?.value || 0);
                totalHmShould += parseInt(row.querySelector('.hmlnd-should')?.value || 0);

                grandTotal += parseInt(row.querySelector('[class*="row-total"]')?.value || 0);
            });
            
            const setTotal = (selector, val) => {
                const el = table.querySelector(selector);
                if (el) el.value = val || '';
            };

            setTotal('.total-g1-tally', totalG1Tally);
            setTotal('.total-g1-adj', totalG1Adj);
            setTotal('.total-g1-should', totalG1Should);
            setTotal('.total-g3-tally', totalG3Tally);
            setTotal('.total-g3-adj', totalG3Adj);
            setTotal('.total-g3-should', totalG3Should);
            setTotal('.total-hm-tally', totalHmTally);
            setTotal('.total-hm-adj', totalHmAdj);
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
        const getVal = (name) => parseInt(document.querySelector(`input[name="\${name}"]`)?.value || 0);
        const setVal = (name, val) => {
            const el = document.querySelector(`input[name="\${name}"]`);
            if (el) el.value = val || '';
        };

        const calcSummary = () => {
            // Left Table Totals
            const handsTotal = getVal('summary_small_hands') + getVal('summary_7_9') + getVal('summary_72k') + getVal('summary_47k');
            setVal('summary_hands_total', handsTotal);
            
            const classBTotal = getVal('summary_class_b_hands') + getVal('summary_class_b_sml');
            setVal('summary_class_b_total', classBTotal);
            
            // Right Table Total
            const grandTotal = getVal('summary_all_in') + getVal('summary_hands_sp_sml') + getVal('summary_class_b_grand');
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
HTML;

$content = preg_replace('/<script>.*?<\/script>/s', $newJs, $content);

file_put_contents('views/production/daily_reports.php', $content);
echo "done";
?>
