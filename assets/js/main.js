/* =================================================================
   DARBCO System — Main JavaScript  v2
   File : assets/js/main.js
   ================================================================= */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

    /* ── DataTables init ────────────────────────────────────── */
    document.querySelectorAll('.darbco-table').forEach(table => {
        if ($.fn.dataTable.isDataTable(table)) return; // prevent double-init
        $(table).DataTable({
            pageLength: 15,
            responsive: true,
            dom: "<'row align-items-center'<'col-sm-6'l><'col-sm-6 text-end'f>>" +
                 "<'row'<'col-12'tr>>" +
                 "<'row align-items-center mt-2'<'col-sm-5'i><'col-sm-7 text-end'p>>",
            language: {
                search:            '',
                searchPlaceholder: 'Search records…',
                lengthMenu:        'Show _MENU_',
                info:              'Showing _START_–_END_ of _TOTAL_',
                paginate: {
                    previous: '<i class="bi bi-chevron-left"></i>',
                    next:     '<i class="bi bi-chevron-right"></i>',
                },
                emptyTable:    'No records found.',
                zeroRecords:   'No matching records.',
            }
        });
    });

    /* ── Auto-dismiss alerts (4 s) ──────────────────────────── */
    document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
        setTimeout(() => {
            try { new bootstrap.Alert(el).close(); } catch(_) {}
        }, 4000);
    });

    /* ── Mobile sidebar toggle ──────────────────────────────── */
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar   = document.getElementById('sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', () => sidebar.classList.toggle('show'));

        // Close sidebar when clicking outside
        document.addEventListener('click', e => {
            if (sidebar.classList.contains('show') &&
                !sidebar.contains(e.target) &&
                !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        });
    }

    /* ── Confirm-before-submit buttons ─────────────────────── */
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', e => {
            if (!confirm(btn.dataset.confirm)) e.preventDefault();
        });
    });

    /* ── Dynamic material rows (Production form) ────────────── */
    const addMaterialBtn = document.getElementById('addMaterialRow');
    const materialBody   = document.getElementById('materialRows');

    if (addMaterialBtn && materialBody) {
        addMaterialBtn.addEventListener('click', () => {
            const firstRow = materialBody.querySelector('.material-row');
            if (!firstRow) return;

            const clone = firstRow.cloneNode(true);
            clone.querySelectorAll('select, input').forEach(el => { el.value = ''; });

            // Show remove button on clones
            const removeBtn = clone.querySelector('.remove-material-row');
            if (removeBtn) removeBtn.classList.remove('d-none');

            materialBody.appendChild(clone);
        });

        materialBody.addEventListener('click', e => {
            const btn = e.target.closest('.remove-material-row');
            if (btn) {
                const rows = materialBody.querySelectorAll('.material-row');
                if (rows.length > 1) {
                    btn.closest('.material-row').remove();
                }
            }
        });
    }

    /* ── Payroll live net-pay preview ───────────────────────── */
    const rateInput   = document.getElementById('rate_per_box');
    const deductInput = document.getElementById('deductions');
    const netDisplay  = document.getElementById('net_pay_preview');
    const prodSelect  = document.getElementById('production_id');

    // Grab boxes value from the selected production option text
    function getSelectedBoxes() {
        if (!prodSelect) return 0;
        const opt = prodSelect.options[prodSelect.selectedIndex];
        const m   = opt?.text.match(/(\d[\d,]*)\s*box/i);
        return m ? parseInt(m[1].replace(/,/g,''), 10) : 0;
    }

    function recalcNet() {
        if (!netDisplay) return;
        const boxes  = getSelectedBoxes();
        const rate   = parseFloat(rateInput?.value)   || 0;
        const deduct = parseFloat(deductInput?.value)  || 0;
        const gross  = boxes * rate;
        const net    = Math.max(0, gross - deduct);
        netDisplay.textContent = '₱ ' + net.toLocaleString('en-PH', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }

    prodSelect?.addEventListener('change', recalcNet);
    rateInput?.addEventListener('input', recalcNet);
    deductInput?.addEventListener('input', recalcNet);

    /* ── Restock modal: populate item_id and name ───────────── */
    document.querySelectorAll('.restock-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const idField   = document.getElementById('restock_item_id');
            const nameField = document.getElementById('restock_item_name');
            if (idField)   idField.value       = btn.dataset.id;
            if (nameField) nameField.textContent = btn.dataset.name;
        });
    });

    /* ── Session idle warning (25 min = 5 min before timeout) ─ */
    const WARN_AFTER_MS = 25 * 60 * 1000;
    let idleTimer;

    function resetIdleTimer() {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(() => {
            const ok = confirm(
                '⚠️  Your session will expire in 5 minutes due to inactivity.\n' +
                'Click OK to stay logged in, or Cancel to log out now.'
            );
            if (!ok) window.location.href = 'index.php?page=logout';
        }, WARN_AFTER_MS);
    }

    ['mousemove','keydown','click','touchstart'].forEach(ev => {
        document.addEventListener(ev, resetIdleTimer, { passive: true });
    });
    resetIdleTimer();

    /* ── Report page: show/hide custom date fields ──────────── */
    const reportPeriod = document.getElementById('reportPeriod');
    if (reportPeriod) {
        reportPeriod.addEventListener('change', function () {
            document.querySelectorAll('.custom-range-field').forEach(el => {
                el.style.display = this.value === 'custom' ? '' : 'none';
            });
        });
    }

    /* ── Chart.js global defaults ───────────────────────────── */
    if (typeof Chart !== 'undefined') {
        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
        Chart.defaults.font.size   = 12;
        Chart.defaults.color       = '#6c757d';
        Chart.defaults.animation   = { duration: 600, easing: 'easeInOutQuart' };
    }
});
