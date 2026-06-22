@extends('main_page.layout.app')

@section('title', 'Kontak - CAMAR')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/project.details.css') }}">
@endpush

@section('content')
<main class="detail-container">
<div class="container">
    <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Proyek</li>
                <li class="breadcrumb-item active">{{ $project->category }}</li>
            </ol>
        </nav>
    <div class="project-detail-layout">
        <div class="left-column">
            <div class="project-hero" id="heroImage">
                <img src="{{asset ('images/mangrove5.jpg') }}" alt="{{ $project->name }}" id="heroImg">
                <div class="hero-overlay"></div>

                <div class="hero-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>

                <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="value">{{ number_format($project->area_ha) }} ha</div>
                            <div class="label">Luas Restorasi</div>
                        </div>

                        <div class="hero-stat">
                            <div class="value">{{ $project->co2_k }} ton</div>
                            <div class="label">CO₂ Diserap/tahun</div>
                        </div>

                        <div class="hero-stat">
                            <div class="value">{{ number_format($project->families_impacted) }}</div>
                            <div class="label">Keluarga Terdampak</div>
                        </div>
                </div>
            </div>

            <div class="thumbnail-strip">
                <div class="thumb-item active">
                   <img src="{{asset ('images/mangrove2.jpg') }}" alt="{{ $project->name }}" id="">
                </div>
                <div class="thumb-item">
                     <img src="{{asset ('images/mangrove4.jpg') }}" alt="{{ $project->name }}" id="">
                </div>
                <div class="thumb-item">
                    <img src="{{asset ('images/mangrove3.jpg') }}" alt="{{ $project->name }}" id="">
                </div>
                <div class="thumb-item">
                   <img src="{{asset ('images/mangrove.jpg') }}" alt="{{ $project->name }}" id="">
                </div>
            </div>
            <div class="project-tabs" role="tablist">
                <button class="tab-btn active" role="tab"
                        data-tab="deskripsi" aria-selected="true">
                    Deskripsi
                </button>
                <button class="tab-btn" role="tab"
                        data-tab="detail" aria-selected="false">
                    Detail Proyek
                </button>
                <button class="tab-btn" role="tab"
                        data-tab="metodologi" aria-selected="false">
                    Metodologi
                </button>
            </div>

            <div class="tab-panel active" id="tab-deskripsi" role="tabpanel">
                <h2 class="section-title">Tentang Proyek</h2>

                <div class="project-description">
                   
                        @php
                            $desc      = $project->description;
                            $shortDesc = Str::limit($desc, 500);
                            $hasMore   = strlen($desc) > 500;
                        @endphp

                        <p>{{ $shortDesc }}</p>

                        @if($hasMore)
                            <p id="descExtra" style="display:none">
                                {{ substr($desc, 500) }}
                            </p>
                            <button class="read-more-btn" id="readMoreBtn">
                                Baca Selengkapnya
                                <svg viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2.5"
                                     style="width:14px;height:14px">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </button>
                        @endif
                        <p> Restorasi ekosistem mangrove seluas 500 hektar di pesisir utara Jawa untuk mitigasi abrasi dan penyerapan karbon.</p>
                  
                </div>

                {{-- Impact Grid --}}
                <div class="impact-grid">
                    @if($project->area_ha)
                        <div class="impact-card">
                            <div class="ic-icon"></div>
                            <div class="ic-value">{{ number_format($project->area_ha) }} ha</div>
                            <div class="ic-label">Luas Restorasi</div>
                        </div>
                    @endif

                    @if($project->co2_per_year)
                        <div class="impact-card">
                            <div class="ic-icon"></div>
                            <div class="ic-value">{{ $project->co2_k }} ton</div>
                            <div class="ic-label">CO₂ Diserap/tahun</div>
                        </div>
                    @endif

                    @if($project->families_impacted)
                        <div class="impact-card">
                            <div class="ic-icon"></div>
                            <div class="ic-value">{{ number_format($project->families_impacted) }}</div>
                            <div class="ic-label">Keluarga Terdampak</div>
                        </div>
                    @endif

                    @if($project->duration_months)
                        <div class="impact-card">
                            <div class="ic-icon"></div>
                            <div class="ic-value">{{ $project->duration_months }} Bulan</div>
                            <div class="ic-label">Durasi Proyek</div>
                        </div>
                    @endif

                    @if($project->standard)
                        <div class="impact-card">
                            <div class="ic-icon"></div>
                            <div class="ic-value">{{ $project->standard }}</div>
                            <div class="ic-label">Standar Sertifikasi</div>
                        </div>
                    @endif

                    @if($project->stock_available)
                        <div class="impact-card">
                            <div class="ic-icon"></div>
                            <div class="ic-value">{{ number_format($project->stock_available) }} ton</div>
                            <div class="ic-label">Stok Tersedia</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── Tab: Detail Proyek ── --}}
            <div class="tab-panel" id="tab-detail" role="tabpanel">
                <h2 class="section-title">Detail Proyek</h2>
                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="di-label">Nama Proyek</div>
                        <div class="di-value">{{ $project->name }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="di-label">Perusahaan</div>
                        <div class="di-value">{{ $project->company_name ?? '-' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="di-label">Kategori</div>
                        <div class="di-value">{{ $project->category ?? '-' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="di-label">Standar</div>
                        <div class="di-value">{{ $project->standard ?? '-' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="di-label">Durasi Proyek</div>
                        <div class="di-value">
                            {{ $project->duration_months ? $project->duration_months . ' Bulan' : '-' }}
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="di-label">Lokasi</div>
                        <div class="di-value">{{ $project->location ?? '-' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="di-label">Stok Tersedia</div>
                        <div class="di-value" style="color:var(--green-500)">
                            {{ $project->stock_available ? number_format($project->stock_available) . ' ton' : '-' }}
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="di-label">Tahun Verifikasi</div>
                        <div class="di-value">{{ $project->verified_year ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <div class="tab-panel" id="tab-metodologi" role="tabpanel">
                <h2 class="section-title">Metodologi</h2>
                <div class="project-description">
                   
                        <p>{{ $project->methodology }}</p>
                        <p>Informasi metodologi belum tersedia.</p>
                </div>
            </div>

        </div>

        <aside>
            <div class="purchase-card">

                <div class="pc-header">
                    <div class="pc-company">
                        {{ strtoupper($project->company_name ?? 'PT KONSERVASI HIJAU') }}
                    </div>
                    <div class="pc-title">{{ $project->name ?? 'Rehabilitasi Mangrove Pesisir Jawa' }}</div>
                </div>

                <div class="pc-meta">
                    <div class="pc-meta-row">
                        <span class="key"><span></span> Durasi Proyek</span>
                        <span class="val">{{ $project->duration_months ?? '24 Bulan' }} Bulan</span>
                    </div>
                    <div class="pc-meta-row">
                        <span class="key"><span></span> Kategori</span>
                        <span class="val">{{ $project->category ?? 'Blue Carbon' }}</span>
                    </div>
                    <div class="pc-meta-row">
                        <span class="key"><span></span> Standar</span>
                        <span class="val">{{ $project->standard ?? '-' }}</span>
                    </div>
                    <div class="pc-meta-row">
                        <span class="key"><span></span> Stok Tersedia</span>
                        <span class="val stock">
                            {{ $project->stock_available
                                ? number_format($project->stock_available) . ' ton'
                                : 'Habis' }}
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
                        <span class="eq-icon"></span>
                        <span>Setara dengan emisi dari
                            <strong id="equivText">2× PP Perjalanan Jakarta – Surabaya </strong>
                        </span>
                    </div>
                </div>

                <div class="pc-actions">
                    {{-- Buy Now --}}
                    <form action="{{ route('orders.checkout') }}" method="POST" id="buyNowForm">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                        <input type="hidden" name="quantity" id="buyQty" value="1">
                        <button type="submit" class="btn btn-primary" style="width:100%">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                            Beli &amp; Offset Sekarang
                        </button>
                    </form>

                    {{-- Add to Cart --}}
                    <button class="btn btn-secondary" id="addToCartBtn"
                            data-project-id="{{ $project->id }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"/>
                            <circle cx="20" cy="21" r="1"/>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                        </svg>
                        Tambah ke Keranjang
                    </button>

                    {{-- Watchlist --}}
                    <button class="btn btn-ghost" id="watchlistBtn"
                            data-project-id="{{ $project->id }}"
                            data-watched="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             id="watchlistIcon"
                             style="width:16px;height:16px;display:inline;margin-right:6px;vertical-align:middle">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                        <span id="watchlistLabel">Simpan ke Watchlist</span>
                    </button>
                </div>

            </div>{{-- /purchase-card --}}
        </aside>

    </div>{{-- /project-detail-layout --}}

</div>{{-- /container --}}

{{-- Toast Notification --}}
<div class="toast" id="toast">
    <span class="toast-icon" id="toastIcon">✓</span>
    <span id="toastMsg">Berhasil!</span>
</div>
</main>

@endsection

@push('scripts')
<script>
    const PROJECT_ID    = {{ $project->id }};
    const PRICE_PER_TON = {{ $project->price_per_ton }};
    const STOCK         = {{ $project->stock_available ?? 9999 }};
    const ADD_CART_URL  = "{{ route('cart.add') }}";
    const WATCHLIST_URL = "{{ route('watchlist.toggle') }}";
    const CSRF_TOKEN    = "{{ csrf_token() }}";
</script>
<script src="{{ asset('js/project.details.js') }}"></script>
@endpush