'use strict';

/* ═══════════════════════════════════════════════════════════════
 |  cart.js — Carbon Market
 |  Perubahan utama:
 |  1. updateCartBadge()  → fetch /cart/count lalu update SEMUA badge di navbar
 |  2. addToCartAjax()    → update badge dari response (tanpa perlu fetch lagi)
 |  3. goToCheckout()     → gunakan Midtrans Snap jika snap_token tersedia,
 |                          fallback ke redirect_url biasa
 ═══════════════════════════════════════════════════════════════ */

const CART = (() => {

    /* ===================== HELPERS ===================== */
    const csrf = () =>
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const formatRp = (num) =>
        'Rp ' + Math.round(num).toLocaleString('id-ID');

    const el = (id) => document.getElementById(id);

    let items    = {};
    let selected = new Set();

    const TAX_RATE = 0.11;

    /* ===================== BADGE NAVBAR ===================== */

    /**
     * Update SEMUA elemen dengan id="cartBadge" atau class="cart-badge" di halaman.
     * Dipanggil setelah add/remove/clear.
     */
    function updateCartBadge(count) {
        // Tangani berbagai selektor badge
        const badges = [
            ...document.querySelectorAll('#cartBadge'),
            ...document.querySelectorAll('.cart-badge'),
            ...document.querySelectorAll('[data-cart-badge]'),
        ];
        badges.forEach(b => {
            b.textContent = count;
            // Tampilkan/sembunyikan badge jika count 0
            if (count > 0) {
                b.style.display = '';
            } else {
                // Tetap tampilkan "0" agar user tahu keranjang kosong
                b.style.display = '';
            }
        });
    }

    /** Fetch jumlah item keranjang dari server dan update badge */
    function refreshBadge() {
        fetch('/cart/count', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => updateCartBadge(data.count ?? 0))
            .catch(() => {}); // silent fail
    }

    /* ===================== INIT ===================== */

    function init() {
        document.querySelectorAll('.cart-card').forEach(card => {
            const id        = card.dataset.itemId;
            const qtyInput  = el(`qty-${id}`);
            const subtotalEl = el(`subtotal-${id}`);

            if (!qtyInput) return;

            const qty          = parseInt(qtyInput.value, 10) || 1;
            const subtotalText = subtotalEl?.textContent.replace(/[^\d]/g, '') || '0';
            const subtotalVal  = parseInt(subtotalText, 10);
            const pricePerTon  = qty > 0 ? subtotalVal / qty : 0;
            const name         = card.querySelector('.card-title')?.textContent.trim() || 'Proyek';
            const img          = card.querySelector('img')?.src || '';

            items[id] = { price: pricePerTon, qty, name, img };
        });

        initCheckboxes();
        updateSummary();
        updateCheckoutBtn();
        // Refresh badge saat halaman cart dibuka juga
        refreshBadge();
    }

    /* ===================== CHECKBOX LOGIC ===================== */

    function initCheckboxes() {
        const selectAllEl = el('selectAll');
        if (selectAllEl) {
            selectAllEl.addEventListener('change', () => {
                const checked = selectAllEl.checked;
                document.querySelectorAll('.item-checkbox').forEach(cb => {
                    cb.checked = checked;
                    const id = cb.dataset.itemId;
                    if (checked) { selected.add(id); _highlightCard(id, true); }
                    else         { selected.delete(id); _highlightCard(id, false); }
                });
                updateSummary();
                updateCheckoutBtn();
                _updateSelectedCount();
            });
        }

        document.querySelectorAll('.item-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const id = cb.dataset.itemId;
                if (cb.checked) { selected.add(id); _highlightCard(id, true); }
                else            { selected.delete(id); _highlightCard(id, false); }
                _syncSelectAll();
                updateSummary();
                updateCheckoutBtn();
                _updateSelectedCount();
            });
        });
    }

    function _highlightCard(id, active) {
        const card = document.querySelector(`.cart-card[data-item-id="${id}"]`);
        if (!card) return;
        card.style.borderColor = active ? '#1a6b3c' : '#e2e8f0';
        card.style.boxShadow   = active
            ? '0 0 0 2px rgba(26,107,60,.12), 0 2px 8px rgba(0,0,0,.05)'
            : '0 2px 8px rgba(0,0,0,.05)';
    }

    function _syncSelectAll() {
        const all      = document.querySelectorAll('.item-checkbox');
        const checkedN = document.querySelectorAll('.item-checkbox:checked').length;
        const sAll     = el('selectAll');
        if (!sAll) return;
        sAll.checked       = checkedN === all.length && all.length > 0;
        sAll.indeterminate = checkedN > 0 && checkedN < all.length;
    }

    function _updateSelectedCount() {
        const countEl = el('selectedCount');
        if (countEl) countEl.textContent = `${selected.size} dipilih`;
        const btnRemSel = el('btnRemoveSelected');
        if (btnRemSel) {
            btnRemSel.disabled      = selected.size === 0;
            btnRemSel.style.opacity = selected.size > 0 ? '1' : '.4';
            btnRemSel.style.cursor  = selected.size > 0 ? 'pointer' : 'default';
        }
    }

    /* ===================== QTY MANAGEMENT ===================== */

    function changeQty(id, delta) {
        if (!items[id]) return;
        const input  = el(`qty-${id}`);
        if (!input) return;
        const min    = parseInt(input.min, 10) || 1;
        const max    = parseInt(input.max, 10) || 99999;
        const newQty = Math.min(Math.max(items[id].qty + delta, min), max);
        input.value  = newQty;
        _applyQtyChange(id, newQty);
    }

    function updateQty(id, value) {
        if (!items[id]) return;
        const input  = el(`qty-${id}`);
        const min    = parseInt(input?.min, 10) || 1;
        const max    = parseInt(input?.max, 10) || 99999;
        const newQty = Math.min(Math.max(parseInt(value, 10) || min, min), max);
        if (input) input.value = newQty;
        _applyQtyChange(id, newQty);
    }

    function _applyQtyChange(id, newQty) {
        items[id].qty = newQty;
        const subtotal   = items[id].price * newQty;
        const subtotalEl = el(`subtotal-${id}`);
        if (subtotalEl) subtotalEl.textContent = formatRp(subtotal);
        updateSummary();
        _syncToServer(id, newQty);
    }

    /* ===================== REMOVE ===================== */

    function removeItem(id) {
        const card = document.querySelector(`.cart-card[data-item-id="${id}"]`);
        if (!card) return;

        card.style.transition = 'opacity .25s, transform .25s';
        card.style.opacity    = '0';
        card.style.transform  = 'translateX(20px)';

        setTimeout(() => {
            card.remove();
            selected.delete(id);
            delete items[id];
            _syncSelectAll();
            updateSummary();
            updateCheckoutBtn();
            _updateSelectedCount();
            _checkEmpty();
            // Update badge setelah hapus
            updateCartBadge(Object.keys(items).length);
        }, 280);

        fetch('/cart/remove', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
            body:    JSON.stringify({ id }),
        }).catch(console.error);
    }

    function removeSelected() {
        if (selected.size === 0) return;
        if (!confirm(`Hapus ${selected.size} item yang dipilih?`)) return;
        [...selected].forEach(id => removeItem(id));
    }

    function clearAll() {
        if (!confirm('Hapus semua item dari keranjang?')) return;

        document.querySelectorAll('.cart-card').forEach(card => {
            card.style.transition = 'opacity .25s';
            card.style.opacity    = '0';
        });
        setTimeout(() => {
            document.querySelectorAll('.cart-card').forEach(c => c.remove());
            items    = {};
            selected = new Set();
            updateSummary();
            updateCartBadge(0);
            _checkEmpty();
        }, 300);

        fetch('/cart/clear', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
        }).catch(console.error);
    }

    /* ===================== SUMMARY ===================== */

    function updateSummary() {
        let subtotal  = 0;
        let totalTons = 0;

        selected.forEach(id => {
            if (!items[id]) return;
            subtotal  += items[id].price * items[id].qty;
            totalTons += items[id].qty;
        });

        const tax   = subtotal * TAX_RATE;
        const total = subtotal + tax;

        _setText('subtotalDisplay', formatRp(subtotal));
        _setText('taxDisplay',      formatRp(tax));
        _setText('totalDisplay',    formatRp(total));
        _setText('totalTon',        `${totalTons} ton`);

        _renderSelectedItemsList();

        // Update badge total item (bukan hanya selected)
        updateCartBadge(Object.keys(items).length);

        return { subtotal, tax, total, totalTons };
    }

    function _renderSelectedItemsList() {
        const listEl  = el('selectedItemsList');
        const noSelEl = el('noSelectionMsg');
        if (!listEl || !noSelEl) return;

        if (selected.size === 0) {
            listEl.style.display  = 'none';
            noSelEl.style.display = 'block';
            return;
        }

        noSelEl.style.display = 'none';
        listEl.style.display  = 'flex';
        listEl.innerHTML      = '';

        selected.forEach(id => {
            if (!items[id]) return;
            const item     = items[id];
            const subtotal = item.price * item.qty;
            const div      = document.createElement('div');
            div.style.cssText = 'display:flex; justify-content:space-between; align-items:center; font-size:.78rem;';
            div.innerHTML = `
                <span style="color:#444; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:140px;">
                    ${item.name || 'Proyek'}
                </span>
                <span style="font-weight:600; color:#1a6b3c; flex-shrink:0; margin-left:8px;">
                    ${formatRp(subtotal)}
                </span>`;
            listEl.appendChild(div);
        });
    }

    function _setText(id, value) {
        const e = el(id);
        if (e) e.textContent = value;
    }

    /* ===================== CHECKOUT BUTTON STATE ===================== */

    function updateCheckoutBtn() {
        const btn = el('btnCheckout');
        if (!btn) return;
        const hasSelection = selected.size > 0;
        btn.disabled      = !hasSelection;
        btn.style.opacity = hasSelection ? '1' : '.45';
        btn.style.cursor  = hasSelection ? 'pointer' : 'not-allowed';
    }

    /* ===================== EMPTY STATE ===================== */

    function _checkEmpty() {
        if (Object.keys(items).length === 0) {
            const wrapper = document.querySelector('.container > div:last-child');
            if (wrapper) {
                wrapper.innerHTML = `
                    <div style="text-align:center;padding:80px 20px;background:#fff;border-radius:16px;border:1px solid #e2e8f0;">
                        <div style="font-size:4rem;margin-bottom:16px;">🛒</div>
                        <h3 style="margin:0 0 8px;color:#333;">Keranjang kamu kosong</h3>
                        <p style="color:#888;margin-bottom:24px;">Tambahkan kredit karbon dari proyek-proyek pilihan kami</p>
                        <a href="/proyek" style="display:inline-block;padding:12px 28px;background:linear-gradient(135deg,#1a6b3c,#2d9c5f);color:#fff;border-radius:10px;text-decoration:none;font-weight:600;">
                            Jelajahi Proyek
                        </a>
                    </div>`;
            }
        }
    }

    /* ===================== SERVER SYNC ===================== */

    let _syncTimers = {};
    function _syncToServer(id, qty) {
        clearTimeout(_syncTimers[id]);
        _syncTimers[id] = setTimeout(() => {
            fetch('/cart/update', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body:    JSON.stringify({ id, quantity: qty }),
            }).catch(console.error);
        }, 600);
    }

    /* ===================== GO TO CHECKOUT (MIDTRANS) ===================== */

    function goToCheckout() {
        if (selected.size === 0) {
            alert('Pilih minimal 1 produk untuk di-checkout!');
            return;
        }

        const btn = el('btnCheckout');
        if (btn) {
            btn.disabled  = true;
            btn.innerHTML = '⏳ Memproses...';
        }

        const storeUrl = document.querySelector('meta[name="orders-store-url"]')?.content
                      || '/orders/store';

        const selectedItems = [...selected].map(id => ({
            project_id: id,
            quantity:   items[id].qty,
            price:      items[id].price,
        }));

        fetch(storeUrl, {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'Accept':       'application/json',
            },
            body: JSON.stringify({ items: selectedItems }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
            }

            if (!data.success) {
                alert(data.message || 'Gagal membuat pesanan.');
                _resetCheckoutBtn(btn);
                return;
            }

            // ── Jika ada snap_token → buka Midtrans Snap popup ──
            if (data.snap_token && window.snap) {
                window.snap.pay(data.snap_token, {
                    onSuccess: (result) => {
                        console.log('Midtrans success', result);
                        _showToast('Pembayaran Berhasil!', 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect_url;
                        }, 1000);
                    },
                    onPending: (result) => {
                        console.log('Midtrans pending', result);
                        window.location.href = data.redirect_url + '&status=pending';
                    },
                    onError: (result) => {
                        console.error('Midtrans error', result);
                        alert('Pembayaran gagal. Silakan coba lagi.');
                        _resetCheckoutBtn(btn);
                    },
                    onClose: () => {
                        // User menutup popup tanpa bayar
                        _resetCheckoutBtn(btn);
                    },
                });
            } else {
                // Fallback: redirect ke halaman konfirmasi biasa
                window.location.href = data.redirect_url;
            }
        })
        .catch(() => {
            alert('Koneksi gagal. Coba lagi.');
            _resetCheckoutBtn(btn);
        });
    }

    function _resetCheckoutBtn(btn) {
        if (!btn) return;
        btn.disabled  = false;
        btn.innerHTML = 'Lanjut Bayar <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>';
    }

    /* ===================== ADD TO CART (dari halaman proyek) ===================== */

    function addToCartAjax(projectId, quantity = 1) {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch('/cart/add', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept':       'application/json',
            },
            body: JSON.stringify({ project_id: projectId, quantity }),
        })
        .then(r => r.json())
        .then(data => {
            // ── Update badge langsung dari response (fix bug #1) ──
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
            }

            if (typeof data.cart_count !== 'undefined') {
                updateCartBadge(data.cart_count);
            }
            // Toast / alert
            if (data.success) {
                _showToast(data.message || 'Ditambahkan ke keranjang!', 'success');
            } else {
                _showToast(data.message || 'Gagal menambahkan.', 'error');
            }
        })
        .catch(console.error);
    }

    /* ===================== SIMPLE TOAST ===================== */

    let _toastEl    = null;
    let _toastTimer = null;

    function _showToast(message, type = 'success') {
        if (!_toastEl) {
            _toastEl = document.createElement('div');
            _toastEl.style.cssText = `
                position:fixed; bottom:24px; right:24px; z-index:9999;
                padding:12px 20px; border-radius:10px; color:#fff;
                font-size:.88rem; font-weight:600;
                box-shadow:0 4px 16px rgba(0,0,0,.18);
                transition:opacity .3s, transform .3s;
                opacity:0; transform:translateY(8px);
            `;
            document.body.appendChild(_toastEl);
        }
        _toastEl.textContent  = (type === 'success' ? '✅ ' : '❌ ') + message;
        _toastEl.style.background = type === 'success' ? '#1a6b3c' : '#e74c3c';

        clearTimeout(_toastTimer);
        // Tampilkan
        requestAnimationFrame(() => {
            _toastEl.style.opacity   = '1';
            _toastEl.style.transform = 'translateY(0)';
        });
        _toastTimer = setTimeout(() => {
            _toastEl.style.opacity   = '0';
            _toastEl.style.transform = 'translateY(8px)';
        }, 2800);
    }

    /* ===================== BIND EVENTS ===================== */

    document.addEventListener('DOMContentLoaded', () => {
        init();
        el('btnClearAll')?.addEventListener('click', clearAll);
        el('btnCheckout')?.addEventListener('click', goToCheckout);
        el('btnRemoveSelected')?.addEventListener('click', removeSelected);
    });

    /* ===================== PUBLIC API ===================== */

    return {
        changeQty,
        updateQty,
        removeItem,
        removeSelected,
        clearAll,
        addToCartAjax,
        goToCheckout,
        refreshBadge,
        updateCartBadge,
    };

})();
