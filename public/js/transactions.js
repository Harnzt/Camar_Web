/**
 * transactions.js — Riwayat Transaksi
 * Handles: search, filter, sort, tabs, modal detail, export CSV
 */

(function () {
    'use strict';

    /* ── State ── */
    const state = {
        search: '',
        status: '',
        dateFrom: '',
        dateTo: '',
        sortCol: 'created_at',
        sortDir: 'desc',
        debounceTimer: null,
    };

    /* ── DOM refs ── */
    const searchInput   = document.getElementById('searchInput');
    const filterStatus  = document.getElementById('filterStatus');
    const filterDateFrom= document.getElementById('filterDateFrom');
    const filterDateTo  = document.getElementById('filterDateTo');
    const btnReset      = document.getElementById('btnReset');
    const btnExportCSV  = document.getElementById('btnExportCSV');
    const tabBtns       = document.querySelectorAll('.tab-btn');
    const sortableThs   = document.querySelectorAll('th.sortable');
    const loadingOverlay= document.getElementById('loadingOverlay');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const modalClose    = document.getElementById('modalClose');
    const modalBody     = document.getElementById('modalBody');

    /* ── Init: read URL params ── */
    function init() {
        const params = new URLSearchParams(window.location.search);
        state.search   = params.get('search')    || '';
        state.status   = params.get('status')    || '';
        state.dateFrom = params.get('date_from') || '';
        state.dateTo   = params.get('date_to')   || '';
        state.sortCol  = params.get('sort')      || 'created_at';
        state.sortDir  = params.get('dir')       || 'desc';

        // Sync UI to state
        if (searchInput)    searchInput.value   = state.search;
        if (filterStatus)   filterStatus.value  = state.status;
        if (filterDateFrom) filterDateFrom.value = state.dateFrom;
        if (filterDateTo)   filterDateTo.value   = state.dateTo;

        syncTabActive();
        syncSortIndicators();
        bindEvents();
    }

    /* ── Bind Events ── */
    function bindEvents() {
        // Search with debounce
        searchInput?.addEventListener('input', (e) => {
            state.search = e.target.value;
            debounceApply();
        });

        // Filters: immediate apply
        filterStatus?.addEventListener('change', (e) => {
            state.status = e.target.value;
            syncTabActive();
            applyFilters();
        });
        filterDateFrom?.addEventListener('change', (e) => {
            state.dateFrom = e.target.value;
            applyFilters();
        });
        filterDateTo?.addEventListener('change', (e) => {
            state.dateTo = e.target.value;
            applyFilters();
        });

        // Tab buttons
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                state.status = btn.dataset.status;
                if (filterStatus) filterStatus.value = state.status;
                syncTabActive();
                applyFilters();
            });
        });

        // Sort headers
        sortableThs.forEach(th => {
            th.addEventListener('click', () => {
                const col = th.dataset.col;
                if (state.sortCol === col) {
                    state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    state.sortCol = col;
                    state.sortDir = 'desc';
                }
                syncSortIndicators();
                applyFilters();
            });
        });

        // Reset
        btnReset?.addEventListener('click', resetFilters);

        // Export CSV
        btnExportCSV?.addEventListener('click', exportCSV);

        // Modal: view buttons (delegated)
        document.getElementById('txBody')?.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-view');
            if (btn) openModal(btn.dataset.id);
        });

        // Modal close
        modalClose?.addEventListener('click', closeModal);
        modalBackdrop?.addEventListener('click', (e) => {
            if (e.target === modalBackdrop) closeModal();
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeModal();
        });
    }

    /* ── Apply Filters → navigate ── */
    function applyFilters(page = 1) {
        const params = new URLSearchParams();
        if (state.search)   params.set('search',    state.search);
        if (state.status)   params.set('status',    state.status);
        if (state.dateFrom) params.set('date_from', state.dateFrom);
        if (state.dateTo)   params.set('date_to',   state.dateTo);
        if (state.sortCol)  params.set('sort',      state.sortCol);
        if (state.sortDir)  params.set('dir',       state.sortDir);
        if (page > 1)       params.set('page',      page);

        showLoading();
        window.location.href = window.transactionRoutes.index + '?' + params.toString();
    }

    function debounceApply() {
        clearTimeout(state.debounceTimer);
        state.debounceTimer = setTimeout(() => applyFilters(), 400);
    }

    /* ── Reset ── */
    function resetFilters() {
        state.search = state.status = state.dateFrom = state.dateTo = '';
        state.sortCol = 'created_at'; state.sortDir = 'desc';

        if (searchInput)    searchInput.value    = '';
        if (filterStatus)   filterStatus.value   = '';
        if (filterDateFrom) filterDateFrom.value = '';
        if (filterDateTo)   filterDateTo.value   = '';

        syncTabActive();
        syncSortIndicators();
        applyFilters();
    }

    /* ── Sync Tab Active ── */
    function syncTabActive() {
        tabBtns.forEach(btn => {
            btn.classList.toggle('active', btn.dataset.status === state.status);
        });
    }

    /* ── Sync Sort Indicators ── */
    function syncSortIndicators() {
        sortableThs.forEach(th => {
            th.classList.remove('th-sort-active');
            const icon = th.querySelector('.sort-icon');
            if (th.dataset.col === state.sortCol) {
                th.classList.add('th-sort-active');
                if (icon) {
                    icon.innerHTML = state.sortDir === 'asc'
                        ? '<path d="M7 15l5 5 5-5"/><line x1="12" y1="3" x2="12" y2="20"/>'
                        : '<path d="M7 9l5-5 5 5"/><line x1="12" y1="21" x2="12" y2="4"/>';
                }
            }
        });
    }

    /* ── Loading ── */
    function showLoading() {
        loadingOverlay?.classList.remove('hidden');
    }

    /* ── Export CSV ── */
    function exportCSV() {
        const params = new URLSearchParams();
        if (state.search)   params.set('search',    state.search);
        if (state.status)   params.set('status',    state.status);
        if (state.dateFrom) params.set('date_from', state.dateFrom);
        if (state.dateTo)   params.set('date_to',   state.dateTo);

        window.location.href = window.transactionRoutes.exportCsv + '?' + params.toString();
    }

    /* ── Modal ── */
    function openModal(id) {
        modalBackdrop.classList.remove('hidden');
        modalBody.innerHTML = '<div class="modal-loading"><div class="spinner"></div></div>';
        document.body.style.overflow = 'hidden';

        fetch(window.transactionRoutes.show + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => renderModalContent(data))
        .catch(() => {
            modalBody.innerHTML = `
                <div style="text-align:center; padding:2rem; color:#991B1B;">
                    <p>Gagal memuat data transaksi.</p>
                </div>`;
        });
    }

    function closeModal() {
        modalBackdrop.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function renderModalContent(tx) {
        const statusClass = {
            success: 'status-success', pending: 'status-pending',
            failed: 'status-failed', refunded: 'status-refunded'
        }[tx.status] || '';

        const statusLabel = {
            success: 'Berhasil', pending: 'Menunggu',
            failed: 'Gagal', refunded: 'Refund'
        }[tx.status] || tx.status;

        modalBody.innerHTML = `
            <div class="modal-detail-grid">
                <div class="detail-item full-width">
                    <span class="detail-label">ID Transaksi</span>
                    <span class="detail-value mono">${escHtml(tx.transaction_id)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Tanggal</span>
                    <span class="detail-value">${formatDate(tx.created_at)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="status-badge ${statusClass}">${statusLabel}</span>
                </div>
            </div>

            <hr class="modal-divider">

            <div class="modal-detail-grid">
                <div class="detail-item full-width">
                    <span class="detail-label">Pelanggan</span>
                    <span class="detail-value">${escHtml(tx.customer_name)}</span>
                    <span class="detail-value" style="font-size:0.8rem;color:#64748B;">${escHtml(tx.customer_email)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Metode Pembayaran</span>
                    <span class="detail-value">${escHtml(tx.payment_method)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Nominal</span>
                    <span class="detail-value amount">Rp ${formatRupiah(tx.amount)}</span>
                </div>
                ${tx.notes ? `
                <div class="detail-item full-width">
                    <span class="detail-label">Catatan</span>
                    <span class="detail-value">${escHtml(tx.notes)}</span>
                </div>` : ''}
            </div>

            ${tx.items && tx.items.length ? `
            <hr class="modal-divider">
            <p class="detail-label" style="margin-bottom:0.75rem;">Item Pesanan</p>
            <table style="width:100%; font-size:0.8rem; border-collapse:collapse;">
                <thead>
                    <tr style="background:#F8F9FB;">
                        <th style="padding:6px 10px; text-align:left; color:#64748B; font-weight:600; font-size:0.7rem; border:1px solid #E8ECF0;">Produk</th>
                        <th style="padding:6px 10px; text-align:center; color:#64748B; font-weight:600; font-size:0.7rem; border:1px solid #E8ECF0;">Qty</th>
                        <th style="padding:6px 10px; text-align:right; color:#64748B; font-weight:600; font-size:0.7rem; border:1px solid #E8ECF0;">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    ${tx.items.map(item => `
                    <tr>
                        <td style="padding:8px 10px; border:1px solid #E8ECF0;">${escHtml(item.name)}</td>
                        <td style="padding:8px 10px; text-align:center; border:1px solid #E8ECF0;">${item.qty}</td>
                        <td style="padding:8px 10px; text-align:right; border:1px solid #E8ECF0; font-weight:600;">Rp ${formatRupiah(item.price)}</td>
                    </tr>`).join('')}
                </tbody>
            </table>` : ''}
        `;
    }

    /* ── Helpers ── */
    function escHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function formatRupiah(num) {
        return Number(num).toLocaleString('id-ID');
    }

    function formatDate(str) {
        if (!str) return '-';
        const d = new Date(str);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' })
            + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }

    /* ── Boot ── */
    document.addEventListener('DOMContentLoaded', init);

})();