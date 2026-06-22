/**
 * show.js
 * Interactivity for KarbonNusa project detail page
 */

(function () {
  'use strict';

  /* ──────────────────────────────────────────
     Helpers
  ────────────────────────────────────────── */
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];

  function formatRupiah(amount) {
    return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
  }

  /* ──────────────────────────────────────────
     Toast Notification
  ────────────────────────────────────────── */
  let toastTimer = null;

  function showToast(message, icon = '✓', duration = 3000) {
    const toast   = $('#toast');
    const msgEl   = $('#toastMsg');
    const iconEl  = $('#toastIcon');
    if (!toast) return;

    clearTimeout(toastTimer);
    msgEl.textContent  = message;
    iconEl.textContent = icon;
    toast.classList.add('show');
    toastTimer = setTimeout(() => toast.classList.remove('show'), duration);
  }

  /* ──────────────────────────────────────────
     Image Gallery
  ────────────────────────────────────────── */
  function initGallery() {
    const heroImg  = $('#heroImg');
    const thumbs   = $$('.thumb-item');
    if (!heroImg || !thumbs.length) return;

    thumbs.forEach(thumb => {
      thumb.addEventListener('click', function () {
        // Hapus kelas aktif dari semua thumbnail
        thumbs.forEach(t => t.classList.remove('active'));
        // Tambahkan kelas aktif ke yang diklik
        this.classList.add('active');
        
        // Ubah sumber gambar utama (Hero)
        const newSrc = $('img', this).getAttribute('src');
        heroImg.setAttribute('src', newSrc);
        
        // Animasi fade-in halus pada gambar utama
        heroImg.style.opacity = '0.3';
        setTimeout(() => {
          heroImg.style.opacity = '1';
        }, 50);
      });
    });
  }

  /* ──────────────────────────────────────────
     Tab System Navigation
  ────────────────────────────────────────── */
  function initTabs() {
    const tabBtns  = $$('.tab-btn');
    const tabPanes = $$('.tab-pane');
    if (!tabBtns.length || !tabPanes.length) return;

    tabBtns.forEach(btn => {
      btn.addEventListener('click', function () {
        const targetTab = this.getAttribute('data-tab');

        // Reset semua tombol & panel konten tab
        tabBtns.forEach(b => b.classList.remove('active'));
        tabPanes.forEach(p => p.classList.remove('active'));

        // Aktifkan tab yang dipilih
        this.classList.add('active');
        const targetPane = $('#' + targetTab);
        if (targetPane) {
          targetPane.classList.add('active');
        }
      });
    });
  }

  /* ──────────────────────────────────────────
     Sticky Floating Action Menu (Mobile view Only)
     PROTEKSI ROLE: Hanya berjalan untuk Buyer
  ────────────────────────────────────────── */
  function initStickyCard() {
    // 1. Cek apakah tombol beli bawaan utama ada di halaman
    // Jika tidak ada (karena user adalah Seller atau belum login), langsung batalkan fungsi ini
    const mainCartBtn = $('#addToCartBtn');
    if (!mainCartBtn) return; 

    const card = $('.purchase-card');
    if (!card) return;

    // Jika ukuran layar di bawah 900px, buat menu aksi melayang di bawah layar handphone
    if (window.innerWidth < 900) {
      const floatBar = document.createElement('div');
      floatBar.id = 'mobileStickyPurchaseBar';
      floatBar.innerHTML = `
        <div style="
          position:fixed;bottom:0;left:0;right:0;
          background:#fff;border-top:1px solid var(--border);
          padding:12px 20px;display:flex;gap:10px;z-index:200;
          box-shadow:0 -4px 20px rgba(13,46,26,.12);
        ">
          <button class="btn btn-secondary" style="flex:1" id="floatCartBtn">
            🛒 Keranjang
          </button>
          <button class="btn btn-primary" style="flex:2" id="floatBuyBtn">
            🔥 Beli Sekarang
          </button>
        </div>`;
      document.body.appendChild(floatBar);

      // Teruskan aksi klik tombol melayang ke tombol asli bawaan sistem
      $('#floatCartBtn')?.addEventListener('click', () => {
        $('#addToCartBtn')?.click();
      });

      $('#floatBuyBtn')?.addEventListener('click', () => {
        $('#buyNowForm')?.submit();
      });
    }
  }

  /* ──────────────────────────────────────────
     Initialization Engine
  ────────────────────────────────────────── */
  function init() {
    initGallery();
    initTabs();
    initStickyCard();
  }

  // Jalankan ketika struktur DOM sudah siap dimuat browser
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();