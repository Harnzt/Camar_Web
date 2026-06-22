@extends('main_page.layout.app')

@section('title', 'Konfirmasi Pembayaran')
@section('description', 'Konfirmasi dan selesaikan pembayaran kredit karbon Anda')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/order.css') }}">
{{-- Midtrans Snap.js: otomatis pilih sandbox/production via config --}}
<script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="pay-page">
    <div class="pay-container">

        {{-- Breadcrumb Navigation --}}
        <nav class="breadcrumb">
            <a href="{{ route('projects.index') }}">Proyek</a>
            <span class="breadcrumb-sep">›</span>
            <a href="{{ route('cart.index') }}">Keranjang</a>
            <span class="breadcrumb-sep">›</span>
            <span>Pembayaran</span>
        </nav>

        {{-- Step Progress Indicator --}}
        <div class="steps">
            <div class="step done">
                <div class="step-dot">1</div>
                <span class="step-label">Keranjang</span>
            </div>
            <div class="step-line done"></div>
            <div class="step active">
                <div class="step-dot">2</div>
                <span class="step-label">Pembayaran</span>
            </div>
            <div class="step-line"></div>
            <div class="step next">
                <div class="step-dot">3</div>
                <span class="step-label">Selesai</span>
            </div>
        </div>

        <h1 class="pay-title">Konfirmasi Pembayaran</h1>
        <p class="pay-sub">Periksa pesanan kamu sebelum melanjutkan</p>

        {{-- Alert Notification System --}}
        @if(session('error'))
            <div style="background:#fee2e2; border:1px solid #fca5a5; color:#dc2626; padding:14px 18px; border-radius:12px; margin-bottom:24px; font-size:.88rem; font-weight:600;">
                <i class="fas fa-exclamation-circle" style="margin-right:6px;"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Main Flexible Grid --}}
        <div class="pay-grid">

            {{-- ── SISI KIRI: DATA TRANSAKSI & PEMBELI ── --}}
            <div style="display: flex; flex-direction: column; gap: 24px;">

                {{-- 1. Daftar Item Kredit Karbon --}}
                <div class="card">
                    <div class="card-title">
                        <div class="icon" style="width:32px; height:32px; border-radius:10px; background:var(--green-pale); display:flex; align-items:center; justify-content:center; color:var(--green-mid);"><i class="fas fa-leaf"></i></div>
                        <span>Kredit Karbon yang Dipesan</span>
                    </div>
                    
                    <div id="orderItemsContainer">
                        @php
                            $extractVal = function($data, $key, $fallback = null) {
                                if (is_object($data)) { return $data->{$key} ?? $fallback; }
                                return is_array($data) ? ($data[$key] ?? $fallback) : $fallback;
                            };
                        @endphp

                        @forelse($transactions ?? [] as $tx)
                            @php
                                $isObj  = is_object($tx);
                                $pName  = $isObj ? ($tx->name ?? ($tx->project->name ?? 'Proyek Karbon')) : ($tx['name'] ?? 'Proyek Karbon');
                                $pImg   = $isObj ? ($tx->image ?? ($tx->project->image ?? 'default.jpg')) : ($tx['image'] ?? 'default.jpg');
                                $pPrice = $isObj ? ($tx->price_per_ton ?? ($tx->price ?? 0)) : ($tx['price_per_ton'] ?? ($tx['price'] ?? 0));
                                $pQty   = $isObj ? ($tx->quantity ?? 1) : ($tx['quantity'] ?? 1);
                                $pTotal = $isObj ? ($tx->total ?? ($tx->total_price ?? ($pPrice * $pQty))) : ($tx['total'] ?? ($pPrice * $pQty));
                            @endphp
                            <div class="order-item">
                                <img src="{{ asset('images/' . $pImg) }}" alt="{{ $pName }}" class="item-img" onerror="this.src='https://placehold.co/72x68/1a6b3c/white?text=🌿'">
                                <div class="item-info">
                                    <p class="item-name">{{ $pName }}</p>
                                    <p class="item-meta">Rp {{ number_format($pPrice, 0, ',', '.') }} / ton CO₂</p>
                                    <span class="item-badge">{{ $pQty }} ton CO₂</span>
                                </div>
                                <div class="item-price">
                                    Rp {{ number_format($pTotal, 0, ',', '.') }}
                                </div>
                            </div>
                        @empty
                            @foreach(session('cart', []) as $id => $item)
                                <div class="order-item">
                                    <img src="{{ asset('images/' . ($item['image'] ?? 'default.jpg')) }}" alt="{{ $item['name'] ?? 'Proyek' }}" class="item-img" onerror="this.src='https://placehold.co/72x68/1a6b3c/white?text=🌿'">
                                    <div class="item-info">
                                        <p class="item-name">{{ $item['name'] ?? 'Proyek Karbon' }}</p>
                                        <p class="item-meta">Rp {{ number_format(($item['price'] ?? 0), 0, ',', '.') }} / ton CO₂</p>
                                        <span class="item-badge">{{ $item['quantity'] ?? 1 }} ton CO₂</span>
                                    </div>
                                    <div class="item-price">
                                        Rp {{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        @endforelse
                    </div>
                </div>

                {{-- 2. Profil Akun Pembeli --}}
                <div class="card">
                    <div class="card-title">
                        <div class="icon" style="width:32px; height:32px; border-radius:10px; background:var(--green-pale); display:flex; align-items:center; justify-content:center; color:var(--green-mid);"><i class="fas fa-user"></i></div>
                        <span>Informasi Pembeli</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nama Lengkap</span>
                        <span class="info-value">{{ Auth::user()->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Alamat Email</span>
                        <span class="info-value">{{ Auth::user()->email }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">No. Telepon</span>
                        <span class="info-value">{{ Auth::user()->phone ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tanggal Pesanan</span>
                        <span class="info-value">{{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
                    </div>
                </div>

            </div>

            {{-- ── SISI KANAN: SUMMARY DAN TOMBOL UTAMA ── --}}
            <div class="summary-card">
                <div class="card">
                    <div class="card-title">
                        <div class="icon" style="width:32px; height:32px; border-radius:10px; background:var(--green-pale); display:flex; align-items:center; justify-content:center; color:var(--green-mid);"><i class="fas fa-receipt"></i></div>
                        <span>Ringkasan Biaya</span>
                    </div>

                    @php
                        $subtotalAll = 0;
                        $txs = $transactions ?? collect(session('cart', []));
                        foreach ($txs as $tx) {
                            $harga  = $extractVal($tx, 'price_per_ton') ?? ($extractVal($tx, 'price') ?? 0);
                            $jumlah = $extractVal($tx, 'quantity') ?? 1;
                            $subtotalAll += $harga * $jumlah;
                        }
                        $tax   = round($subtotalAll * 0.11);
                        $total = $subtotalAll + $tax;

                        $totalTon = 0;
                        foreach ($txs as $tx) {
                            $totalTon += $extractVal($tx, 'quantity') ?? 1;
                        }

                        $txIds = collect($transactions ?? [])->pluck('id')->filter()->values()->toArray();
                    @endphp

                    <div class="summary-row">
                        <span class="label">Jumlah Ton</span>
                        <span class="value">{{ $totalTon }} ton CO₂</span>
                    </div>
                    <div class="summary-row">
                        <span class="label">Subtotal</span>
                        <span class="value">Rp {{ number_format($subtotalAll, 0, ',', '.') }}</span>
                    </div>
                    <div class="summary-row">
                        <span class="label">PPN 11%</span>
                        <span class="value">Rp {{ number_format($tax, 0, ',', '.') }}</span>
                    </div>

                    <hr class="summary-divider">

                    <div class="summary-total">
                        <span class="label">Total Bayar</span>
                        <span class="amount">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>

                    {{-- Data input parameter token pendukung midtrans --}}
                    <input type="hidden" id="snapToken" value="{{ $snapToken ?? '' }}">
                    <input type="hidden" id="redirectUrl" value="{{ route('orders.midtrans.finish') }}">
                    <input type="hidden" id="selectedPaymentMethod" value="transfer">

                    {{-- Gateway Form Fallback --}}
                    <form id="paymentForm" action="{{ route('orders.checkout.confirm.submit') }}" method="POST" style="display:none;">
                        @csrf
                        @foreach($txIds as $txId)
                            <input type="hidden" name="transaction_ids[]" value="{{ $txId }}">
                        @endforeach
                        <input type="hidden" name="payment_method" value="midtrans">
                    </form>

                    {{-- Trigger Launch Button --}}
                    <button id="btnPay" type="button" class="btn-pay" onclick="launchMidtrans()">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                        <span>Bayar Sekarang</span>
                    </button>

                    @if(!$snapToken)
                        <p style="font-size:.72rem; color:#e67e22; text-align:center; margin-top:10px; line-height:1.4;">
                            <i class="fas fa-exclamation-triangle"></i> Token Midtrans kosong. Sistem dialihkan otomatis menuju konfirmasi manual sebagai cadangan.
                        </p>
                    @endif

                    <div class="security-row">
                        <span>SSL Encrypted</span>
                        <span class="dot">•</span>
                        <span>Midtrans Secured</span>
                        <span class="dot">•</span>
                        <span>PCI DSS</span>
                    </div>

                    <a href="{{ route('cart.index') }}" style="display:block; text-align:center; margin-top:16px; color:var(--muted); font-size:.82rem; text-decoration:none; font-weight:600; transition:color .2s;" onmouseover="this.style.color='var(--green-mid)'" onmouseout="this.style.color='var(--muted)'">
                        ← Kembali ke Keranjang
                    </a>
                </div>

                {{-- Eco Impact Banner --}}
                <div class="eco-notice">
                    <i class="fas fa-seedling" style="margin-right:4px;"></i> <strong>Dampak Nyata:</strong> {{ $totalTon }} ton CO₂ yang kamu beli setara dengan pertumbuhan berkala sekitar <strong>{{ $totalTon * 45 }} pohon</strong> selama 10 tahun demi masa depan bumi.
                </div>
            </div>

        </div> {{-- /pay-grid --}}
    </div> {{-- /pay-container --}}
</div> {{-- /pay-page --}}

{{-- Processing Screen Blocker Block --}}
<div id="payOverlay">
    <div class="ov-spinner"></div>
    <p id="overlayMsg">Membuka jendela pembayaran...</p>
</div>

@push('scripts')
<script>
    function selectMethod(el, method) {
        document.querySelectorAll('.method-pill').forEach(p => p.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('selectedPaymentMethod').value = method;
    }

    function launchMidtrans() {
        const snapToken   = document.getElementById('snapToken').value.trim();
        const redirectUrl = document.getElementById('redirectUrl').value;
        const btn         = document.getElementById('btnPay');

        if (!snapToken) {
            console.warn('[Midtrans] snapToken kosong → fallback ke form manual.');
            showOverlay('Memproses pembayaran...');
            document.getElementById('paymentForm').submit();
            return;
        }

        if (typeof window.snap === 'undefined') {
            showToast('Snap.js Midtrans gagal dimuat. Periksa koneksi Anda.', '#e74c3c');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<div class="spinner" style="width:16px; height:16px; border:2px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; animation:spin .75s linear infinite; display:inline-block; vertical-align:middle; margin-right:6px;"></div> Membuka pembayaran...';
        showOverlay('Membuka jendela pembayaran...');

        setTimeout(() => {
            hideOverlay();
            window.snap.pay(snapToken, {
                onSuccess: function(result) {
                    console.log('[Midtrans] onSuccess', result);
                    showOverlay('Pembayaran berhasil! Mengalihkan...');
                    const ids = "{{ implode(',', session('pending_transaction_ids', [])) }}";
                    setTimeout(() => {
                        window.location.href = redirectUrl + (ids ? '?ids=' + ids : '');
                    }, 1000);
                },
                onPending: function(result) {
                    console.log('[Midtrans] onPending', result);
                    showOverlay('Menunggu konfirmasi pembayaran...');
                    const ids = "{{ implode(',', session('pending_transaction_ids', [])) }}";
                    setTimeout(() => {
                        window.location.href = redirectUrl + (ids ? '?ids=' + ids + '&status=pending' : '?status=pending');
                    }, 1500);
                },
                onError: function(result) {
                    console.error('[Midtrans] onError', result);
                    hideOverlay();
                    resetBtn();
                    showToast('Pembayaran gagal. Silakan coba lagi.', '#e74c3c');
                },
                onClose: function() {
                    console.log('[Midtrans] popup ditutup oleh user');
                    hideOverlay();
                    resetBtn();
                    showToast('Jendela pembayaran ditutup.', '#6b7280');
                }
            });
        }, 400);
    }

    function showOverlay(msg) {
        document.getElementById('overlayMsg').textContent = msg || 'Memproses...';
        document.getElementById('payOverlay').classList.add('show');
    }

    function hideOverlay() {
        document.getElementById('payOverlay').classList.remove('show');
    }

    function resetBtn() {
        const btn = document.getElementById('btnPay');
        btn.disabled = false;
        btn.innerHTML = `
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                <line x1="1" y1="10" x2="23" y2="10"/>
            </svg>
            <span>Bayar Sekarang</span>`;
    }

    function showToast(msg, color) {
        const t = document.createElement('div');
        t.style.cssText = `
            position:fixed; bottom:24px; right:24px; z-index:99999;
            padding:12px 20px; border-radius:10px; background:${color || '#1a6b3c'}; color:#fff;
            font-size:.88rem; font-weight:600; box-shadow:0 4px 16px rgba(0,0,0,.15);
            animation:fadeInUp .3s ease;
        `;
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 4000);
    }

    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(12px); }
            to   { opacity:1; transform:translateY(0); }
        }
    `;
    document.head.appendChild(style);
</script>
@endpush
@endsection