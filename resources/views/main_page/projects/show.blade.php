@extends('main_page.layout.app')

@section('title', 'Proyek Carbon Offset')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/show.css') }}">
@endpush

@section('content')
<main class="detail-container">
<div class="container">
    {{-- Breadcrumb Navigation --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Proyek</a></li>
            <li class="breadcrumb-item active">{{ $project->category }}</li>
        </ol>
    </nav>
    
    <div class="project-detail-layout">
        {{-- ==========================================================================
           KELOMPOK KIRI: MENGIKUTI DESAIN CODE 2
           ========================================================================== --}}
        <div class="left-column">
            <div class="project-header">
                <h1 class="project-title">{{ $project->name }}</h1>
            </div>

            <div class="project-hero" id="heroImage">
                <img src="{{ asset('images/' . $project->image) }}" alt="{{ $project->name }}" id="heroImg">
                <div class="hero-overlay"></div>

                <div class="hero-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                    </svg>
                    <span class="project-category">{{ $project->category }}</span>
                </div>
            </div>

            <div class="project-gallery">
                <div class="thumb-item active"><img src="{{ asset('images/' . $project->image) }}" alt="Thumbnail 1"></div>
                @if(!empty($project->gallery_images))
                    @foreach(json_decode($project->gallery_images) as $index => $img)
                        <div class="thumb-item"><img src="{{ asset('images/' . $img) }}" alt="Thumbnail {{ $index + 2 }}"></div>
                    @endforeach
                @endif
            </div>

            {{-- KONDISIONAL: Jika user adalah BUYER, Kotak Detail Proyek TETAP berada di Sebelah Kiri --}}
            @if(Auth::check() && Auth::user()->role === 'buyer')
                <div class="tabs-wrapper left-tabs" style="margin-top: 32px;">
                    <div class="tabs-nav">
                        <button class="tab-btn active" data-tab="deskripsi">Deskripsi</button>
                        <button class="tab-btn" data-tab="metodologi">Metodologi</button>
                        <button class="tab-btn" data-tab="dampak">Dampak</button>
                        <button class="tab-btn" data-tab="location">Lokasi</button>
                    </div>
                    
                    <div class="tabs-content">
                        <div class="tab-pane active" id="deskripsi">
                            <h3>Tentang Proyek</h3>
                            <p>{!! nl2br(e($project->description)) !!}</p>
                        </div>
                        
                        <div class="tab-pane" id="metodologi">
                            <h3>Standar & Verifikasi</h3>
                            <p>{{ $project->methodology ?? 'Proyek ini diverifikasi menggunakan standar komoditas karbon nasional dengan transparansi penuh.' }}</p>
                        </div>
                        
                        <div class="tab-pane" id="dampak">
                            <h3>Contribusi Keberlanjutan</h3>
                            <p>Mereduksi emisi CO₂e sekaligus mengalokasikan pendanaan langsung untuk pemberdayaan masyarakat sekitar tapak proyek.</p>
                        </div>
                        <div class="tab-pane" id="location">
                            <p class="project-location">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; stroke: var(--green-600); vertical-align: middle; margin-right: 4px;">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                {{ $project->location ?? 'Indonesia' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ==========================================================================
           KELOMPOK KANAN: SIDEBAR PEMBELIAN / DETIL SIDEBAR ADAPTIF
           ========================================================================== --}}
        <aside class="right-column">
            <div class="purchase-card">
                <div class="pc-header">
                    <div class="pc-company">
                        {{ strtoupper($project->company_name ?? 'PT KONSERVASI HIJAU') }}
                    </div>
                    <div class="pc-title">{{ $project->name }}</div>
                </div>

                <div class="pc-meta">
                    <div class="pc-meta-row">
                        <span class="key">Durasi Proyek</span>
                        <span class="val">{{ $project->duration_months ?? '24' }} Bulan</span>
                    </div>
                    <div class="pc-meta-row">
                        <span class="key">Kategori</span>
                        <span class="val">{{ $project->category }}</span>
                    </div>
                    <div class="pc-meta-row">
                        <span class="key">Standar</span>
                        <span class="val">{{ $project->standard ?? '-' }}</span>
                    </div>
                    <div class="pc-meta-row">
                        <span class="key">Stok Tersedia</span>
                        <span class="val stock">
                            {{ $project->stock_available ? number_format($project->stock_available) . ' ton' : 'Habis' }}
                        </span>
                    </div>
                </div>

                <div class="pc-pricing">
                    <div class="price-label">Harga per Kredit Karbon</div>
                    <div class="price-main">
                        <span class="price-currency">Rp</span>
                        <span class="price-amount">
                            {{ number_format($project->price_per_ton, 0, ',', '.') }}
                        </span>
                        <span class="price-unit">/ton CO₂</span>
                    </div>
                    <div class="price-note">Termasuk sertifikat &amp; biaya registry</div>
                </div>

                {{-- KONDISIONAL: Jika user adalah SELLER, Kotak Detail Proyek disisipkan di Kolom Kanan --}}
                @if(Auth::check() && Auth::user()->role === 'seller')
                    <div class="tabs-wrapper right-tabs" style="margin-top: 16px; margin-bottom: 20px;">
                        <div class="tabs-nav">
                            <button class="tab-btn active" data-tab="deskripsi">Deskripsi</button>
                            <button class="tab-btn" data-tab="metodologi">Metodologi</button>
                            <button class="tab-btn" data-tab="dampak">Dampak</button>
                            <button class="tab-btn" data-tab="location">Lokasi</button>
                        </div>
                        
                        <div class="tabs-content">
                            <div class="tab-pane active" id="deskripsi">
                                <h3>Tentang Proyek</h3>
                                <p>{!! nl2br(e($project->description)) !!}</p>
                            </div>
                            
                            <div class="tab-pane" id="metodologi">
                                <h3>Standar & Verifikasi</h3>
                                <p>{{ $project->methodology ?? 'Proyek ini diverifikasi menggunakan standar komoditas karbon nasional dengan transparansi penuh.' }}</p>
                            </div>
                            
                            <div class="tab-pane" id="dampak">
                                <h3>Kontribusi Keberlanjutan</h3>
                                <p>Mereduksi emisi CO₂e sekaligus mengalokasikan pendanaan langsung untuk pemberdayaan masyarakat sekitar tapak proyek.</p>
                            </div>
                            <div class="tab-pane" id="location">
                                <p class="project-location">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; stroke: var(--green-600); vertical-align: middle; margin-right: 4px;">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    {{ $project->location ?? 'Indonesia' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- HANYA AKUN BUYER YANG DAPAT MELIHAT SELECTION JUMLAH & TOMBOL AKSI BELI --}}
                @if(Auth::check() && Auth::user()->role === 'buyer')
                    <div class="qty-wrapper">
                        <div class="qty-label">Jumlah ton CO₂ yang ingin di-offset</div>
                        <div class="qty-row">
                            <div class="qty-stepper">
                                <button class="qty-btn" id="qtyMinus" aria-label="Kurangi">−</button>
                                <input type="number" class="qty-input" id="qtyInput"
                                       value="1" min="1"
                                       max="{{ $project->stock_available ?? 9999 }}"
                                       aria-label="Jumlah ton">
                                <button class="qty-btn" id="qtyPlus" aria-label="Tambah">+</button>
                            </div>
                            <span class="qty-total" id="qtyTotal">
                                = Rp {{ number_format($project->price_per_ton, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="emission-equiv" id="emissionEquiv">
                            <span class="eq-icon">🍃</span>
                            <span>Setara dengan emisi dari 
                                <strong id="equivText">2× PP Perjalanan Jakarta – Surabaya </strong>
                            </span>
                        </div>
                    </div>

                    <div class="pc-actions">
                        <div class="buy-now-wrapper">
                            <input type="hidden" id="buyProjectId" value="{{ $project->id }}">
                            <input type="hidden" id="buyQty" value="1"> 
                            
                            <button type="button" id="btnBuyNowInstant" onclick="handleBuyNowInstant()" class="btn btn-primary" style="width:100%">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px; margin-right:5px; display:inline-block; vertical-align:middle;">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                </svg>
                                Beli &amp; Offset Sekarang
                            </button>
                        </div>

                        <button class="btn btn-secondary" id="addToCartBtn" data-project-id="{{ $project->id }}">
                            Tambah ke Keranjang
                        </button>
                    </div>
                @else
                    {{-- TAMPILAN INFORMASI UNTUK SELLER ATAU TAMU --}}
                    <div class="info-only-block" style="margin-top: 10px; padding: 15px; background: #f8f9fa; border: 1px dashed #ced4da; border-radius: var(--radius-lg); text-align: center;">
                        @if(Auth::check() && Auth::user()->role === 'seller')
                            <p style="margin:0; font-size:14px; color:#5a6b5c;">ℹ️ Anda masuk sebagai <strong>Penjual (Seller)</strong>. Fitur transaksi pembelian dinonaktifkan untuk akun toko.</p>
                        @else
                            <p style="margin:0; font-size:14px; color:#5a6b5c;">🔑 Silakan <a href="{{ route('login') }}" style="color:#0066cc; text-decoration:underline; font-weight:600;">Login sebagai Buyer</a> untuk melakukan pembelian kredit karbon.</p>
                        @endif
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>
</main>

<div class="toast-box" id="toast">
    <span id="toastMsg">Berhasil!</span>
</div>
@endsection

@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/v1/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script src="{{ asset('js/show.js') }}"></script>
<script>
    const PRICE_PER_TON = {{ $project->price_per_ton }};
    const STOCK = {{ $project->stock_available ?? 9999 }};

    document.addEventListener('DOMContentLoaded', () => {
        const qtyInput = document.getElementById('qtyInput');
        const qtyMinus = document.getElementById('qtyMinus');
        const qtyPlus  = document.getElementById('qtyPlus');
        const qtyTotal = document.getElementById('qtyTotal');
        const buyQty   = document.getElementById('buyQty');

        function formatRp(num) {
            return 'Rp ' + Math.round(num).toLocaleString('id-ID');
        }

        function updatePricing(qty) {
            if(!qtyTotal) return;
            const total = qty * PRICE_PER_TON;
            qtyTotal.textContent = '= ' + formatRp(total);
            if (buyQty) buyQty.value = qty;
        }

        if (qtyMinus && qtyInput) {
            qtyMinus.addEventListener('click', () => {
                let currentQty = parseInt(qtyInput.value) || 1;
                if (currentQty > 1) {
                    currentQty--;
                    qtyInput.value = currentQty;
                    updatePricing(currentQty);
                }
            });
        }

        if (qtyPlus && qtyInput) {
            qtyPlus.addEventListener('click', () => {
                let currentQty = parseInt(qtyInput.value) || 1;
                if (currentQty < STOCK) {
                    currentQty++;
                    qtyInput.value = currentQty;
                    updatePricing(currentQty);
                }
            });
        }

        if (qtyInput) {
            qtyInput.addEventListener('input', function() {
                let currentQty = parseInt(this.value) || 1;
                if (currentQty < 1) currentQty = 1;
                if (currentQty > STOCK) currentQty = STOCK;
                this.value = currentQty;
                updatePricing(currentQty);
            });
        }

        // Fungsionalitas Switch Tab Modular (Mendukung letak kiri maupun kanan)
        const tabs = document.querySelectorAll('.tab-btn');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const wrapper = tab.closest('.tabs-wrapper');
                wrapper.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
                wrapper.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
                
                tab.classList.add('active');
                const paneId = tab.getAttribute('data-tab');
                wrapper.querySelector('#' + paneId)?.classList.add('active');
            });
        });
    });

    function handleBuyNowInstant() {
        const btn = document.getElementById('btnBuyNowInstant');
        const projectId = document.getElementById('buyProjectId').value;
        const qty = document.getElementById('buyQty').value;

        if(!btn) return;
        btn.disabled = true;
        btn.innerHTML = '⏳ Memproses Pesanan...';

        const payload = {
            items: [{
                project_id: projectId,
                quantity: parseInt(qty) || 1,
                price: Math.round(PRICE_PER_TON)
            }]
        };

        fetch('/orders/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
            }

            if (!data.success) {
                alert(data.message || 'Gagal memproses pesanan.');
                btn.disabled = false;
                btn.innerHTML = 'Beli & Offset Sekarang';
                return;
            }

            if (data.snap_token && window.snap) {
                window.snap.pay(data.snap_token, {
                    onSuccess: function(result) { window.location.href = data.redirect_url; },
                    onPending: function(result) { window.location.href = data.redirect_url; },
                    onError: function(result) {
                        alert('Pembayaran gagal. Silakan coba kembali.');
                        btn.disabled = false;
                        btn.innerHTML = 'Beli & Offset Sekarang';
                    },
                    onClose: function() {
                        btn.disabled = false;
                        btn.innerHTML = 'Beli & Offset Sekarang';
                    }
                });
            } else {
                window.location.href = data.redirect_url;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi hambatan koneksi jaringan.');
            btn.disabled = false;
            btn.innerHTML = 'Beli & Offset Sekarang';
        });
    }

    document.getElementById('addToCartBtn')?.addEventListener('click', function() {
        const projectId = this.getAttribute('data-project-id');
        const qty = document.getElementById('qtyInput') ? document.getElementById('qtyInput').value : 1;

        fetch('{{ route("cart.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ project_id: projectId, quantity: qty })
        })
        .then(response => response.json())
        .then(data => {
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
            }

            if (data.success) {
                const badge = document.getElementById('cartBadge');
                if (badge) badge.innerText = data.cart_count;
                alert(data.message);
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menambahkan ke keranjang');
        });
    });
</script>
@endpush
