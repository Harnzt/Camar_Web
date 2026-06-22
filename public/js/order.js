
'use strict';
document.addEventListener('DOMContentLoaded', () => {
    OrderShow.init();
});

const OrderShow = (() => {

    let _countdownInterval = null;

    function initCountdown() {
        const data = window.ORDER_DATA || {};
        if (data.status !== 'pending' || !data.expiredAt) return;

        const el = document.getElementById('countdown');
        if (!el) return;

        const expiry = new Date(data.expiredAt);

        function tick() {
            const diff = expiry - Date.now();

            if (diff <= 0) {
                el.textContent = '(Kedaluwarsa)';
                el.style.color = '#e74c3c';
                clearInterval(_countdownInterval);

                setTimeout(() => window.location.reload(), 3000);
                return;
            }

            const h = String(Math.floor(diff / 3_600_000)).padStart(2, '0');
            const m = String(Math.floor((diff % 3_600_000) / 60_000)).padStart(2, '0');
            const s = String(Math.floor((diff % 60_000) / 1_000)).padStart(2, '0');

            el.textContent = `(${h}:${m}:${s})`;

            if (diff < 30 * 60 * 1_000) {
                el.style.color = '#e74c3c';
                el.style.fontWeight = '800';
            }
        }

        tick();
        _countdownInterval = setInterval(tick, 1_000);
    }

    let _toastEl = null;
    let _toastTimer = null;

    function showToast(message, type = 'success') {
        if (!_toastEl) {
            _toastEl = document.createElement('div');
            _toastEl.className = 'tx-toast';
            document.body.appendChild(_toastEl);
        }

        const icons = { success: '✅', error: '❌', info: 'ℹ️' };
        _toastEl.textContent = `${icons[type] ?? ''} ${message}`;

        if (type === 'error') {
            _toastEl.style.background = '#e74c3c';
        } else if (type === 'info') {
            _toastEl.style.background = '#3b82f6';
        } else {
            _toastEl.style.background = '#1a6b3c';
        }

        clearTimeout(_toastTimer);
        _toastEl.classList.add('show');
        _toastTimer = setTimeout(() => _toastEl.classList.remove('show'), 2_500);
    }

    function initCopyButtons() {
        document.querySelectorAll('.copy-btn[data-copy]').forEach(btn => {
            btn.addEventListener('click', () => {
                const text = btn.dataset.copy;

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text)
                        .then(() => showToast(`Disalin: ${text}`))
                        .catch(() => fallbackCopy(text));
                } else {
                    fallbackCopy(text);
                }
            });
        });
    }

    function fallbackCopy(text) {
        const ta = document.createElement('textarea');
        ta.value = text;
        ta.style.cssText = 'position:fixed;opacity:0;';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            showToast(`Disalin: ${text}`);
        } catch {
            showToast('Gagal menyalin teks', 'error');
        }
        document.body.removeChild(ta);
    }

    /* ── CANCEL ORDER CONFIRM ───────────────────────────────────── */
    function initCancelForm() {
        const forms = document.querySelectorAll('form[data-confirm]');
        forms.forEach(form => {
            form.addEventListener('submit', e => {
                const msg = form.dataset.confirm || 'Yakin ingin melanjutkan?';
                if (!confirm(msg)) e.preventDefault();
            });
        });
    }

    /* ── PRINT HELPER ───────────────────────────────────────────── */
    function initPrintButton() {
        window.addEventListener('beforeprint', () => {
            const order = window.ORDER_DATA || {};
            document.title = `Transaksi ${order.orderNumber || ''} — Carbon Credit`;
        });

        window.addEventListener('afterprint', () => {
            document.title = document.querySelector('title')?.textContent || 'Carbon Credit';
        });
    }

    /* ── AUTO-DISMISS ALERTS ────────────────────────────────────── */
    function initAlerts() {
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity .4s, transform .4s';
                alert.style.opacity    = '0';
                alert.style.transform  = 'translateY(-8px)';
                setTimeout(() => alert.remove(), 400);
            }, 5_000);
        });
    }

    /* ── SMOOTH SCROLL ANCHORS ──────────────────────────────────── */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const target = document.querySelector(a.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    /* ── PUBLIC API ─────────────────────────────────────────────── */
    function init() {
        initCountdown();
        initCopyButtons();
        initCancelForm();
        initPrintButton();
        initAlerts();
        initSmoothScroll();
    }

    return { init, showToast };

})();