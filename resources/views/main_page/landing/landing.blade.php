@extends('main_page.layout.app')

@section('title', 'Beranda')
@section('description', 'CAMAR - Carbon Market Indonesia, Platform Perdagangan Karbon untuk Masa Depan Berkelanjutan')


@push('styles')
<link rel="stylesheet" href="{{ asset('css/landing.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
@endpush

@section('content')

{{-- ================================================================
     KONDISI A - LOGIN SEBAGAI BUYER
     ================================================================ --}}
@if(Auth::check() && (Auth::user()->isBuyer() || Auth::user()->isSeller()))
    @if(Auth::user()->isBuyer())
    {{-- HERO BUYER --}}
    <section class="hero-section hero-user" id="home">
        <div class="hero-background">
            <img src="{{ asset('images/landing.png') }}" alt="Background">
            <div class="hero-user-overlay"></div>
        </div>

        <div class="container">
            <div class="hero-user-inner">
                {{-- Panel Kiri --}}
                <div class="hero-user-left animate-fade-in">
                    @if(isset($lastTransaction) && $lastTransaction && in_array($lastTransaction->status, ['pending','paid','verified']))
                    <div class="floating-notif">
                        <div class="notif-dot"></div>
                        <span>
                            <strong>Transaksi Aktif:</strong>
                            {{ Str::limit($lastTransaction->project->name ?? '-', 28) }}
                            — <em>{{ $lastTransaction->status_label }}</em>
                        </span>
                        <a href="{{ route('dashboard') }}">Cek →</a>
                    </div>
                    @endif

                    <p class="hero-user-tag">
                        <i class="fas fa-leaf"></i>
                        {{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}
                    </p>
                    <h1 class="hero-user-title">
                        Selamat Datang,<br>
                        <span class="hero-user-name">{{ $greetingName }}!</span>
                    </h1>
                    <p class="hero-user-sub">
                        Setiap ton CO₂ yang Anda offset adalah langkah nyata menuju
                        Indonesia yang lebih hijau dan berkelanjutan.
                    </p>
                    <div class="hero-user-cta">
                        <a href="{{ route('projects.index') }}" class="btn-hero-primary">
                            <i class="fas fa-search"></i> Cari Proyek Baru
                        </a>
                        <a href="{{ route('calculator') }}" class="btn-hero-secondary">
                            <i class="fas fa-calculator"></i> Hitung Emisi
                        </a>
                    </div>
                </div>

                {{-- Panel Kanan --}}
                <div class="hero-user-right animate-fade-in-delay">
                    <div class="impact-snapshot">
                        <div class="impact-snapshot-header">
                            <span class="impact-snapshot-label">
                                <i class="fas fa-globe-asia"></i> Impact Anda
                            </span>
                            <a href="{{ route('dashboard') }}" class="impact-snapshot-more">Detail →</a>
                        </div>

                        <div class="impact-big-number">
                            <span class="impact-big-unit">setara</span>
                            <span class="impact-big-val">{{ number_format($treeEquivalent ?? 0) }}</span>
                            <span class="impact-big-unit">🌳 pohon</span>
                        </div>
                        <p class="impact-big-desc">
                            Kontribusi total:
                            <strong>{{ number_format($totalOffsetKg ?? 0, 1) }} kg CO₂</strong> ter-offset
                        </p>

                        <div class="impact-progress-wrap">
                            <div class="impact-progress-label">
                                <span>Progress Offset</span>
                                <strong>{{ round($offsetPercentage ?? 0, 1) }}%</strong>
                            </div>
                            <div class="impact-progress-track">
                                <div class="impact-progress-fill"
                                     style="width:{{ min(100, $offsetPercentage ?? 0) }} %"></div>
                            </div>
                        </div>

                        @if($user->isPending())
                        <div class="impact-status pending">
                            <i class="fas fa-hourglass-half"></i> Akun dalam proses verifikasi
                        </div>
                        @elseif($user->isVerified())
                        <div class="impact-status verified">
                            <i class="fas fa-shield-alt"></i> Akun Terverifikasi — Akses Penuh
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SMART MATCHING --}}
    <section class="projects-section py-5" id="smart-match"> 
        <div class="container">
            
            <div class="row mb-4">
                <div class="col-lg-8">
                    <span class="section-chip" style="margin-bottom: 0.55rem; display: inline-flex;"><i class="fas fa-magic"></i> Smart Matching</span>
                    <h2 class="section-title text-start">
                        Proyek yang Cocok untuk 
                        @if($user->industry)
                            Industri <span class="industry-highlight">{{ ucfirst($user->industry) }}</span> Anda
                        @else
                            Anda
                        @endif
                    </h2>
                </div>
            </div>

            <div class="row g-4">
                @forelse($recommendedProjects ?? [] as $index => $project)
                    <div class="col-md-6 col-lg-4">
                        <div class="project-card {{ $index === 0 ? 'featured' : '' }}"style="position: relative;">
                        
                            @if($index === 0)
                                <span class="project-badge-label populer">Populer</span>
                            @elseif($index === 1)
                                <span class="project-badge-label baru">Baru</span>
                            @endif
                            
                            <div class="project-card__image">
                                <img src="{{ asset('images/' . $project->image) }}" alt="{{ $project->name }}">
                            </div>

                            <div class="project-card__body">
                                <div class="project-card__category">{{ $project->category }}</div>

                                <p class="project-card__company">{{ $project->company_name }}</p>

                                <h3 class="project-card__title" line-clamp-2>{{ $project->name }}</h3>
                                
                                <div class="project-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $project->location ?? '-' }}
                                </div>
                                
                                <p class="project-card__description">{{ $project->description }}</p>
                                
                                <div class="project-card__meta">
                                    <div class="project-duration-wrapper">
                                        <span class="duration-icon">📅</span>
                                        <span class="project-duration">{{ $project->duration_months ?? '0' }} Bulan</span>
                                    </div>
                                    <span class="project-card__capacity">Stok: {{ number_format($project->stock_available) }} ton</span>
                                </div>

                                <div class="project-card__meta">
                                    <span class="project-card__price">Rp {{ number_format($project->price_per_ton, 0, ',', '.') }} / ton</span>
                                </div>
                                
                                <div class="project-card__footer">
                                    <a href="{{ route('projects.show', $project->id) }}" class="btn-action btn-action--primary">Pilih Paket</a>
                                </div>
                            </div>
                        </div>
                    </div> 
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="projects-empty-state">
                            <i class="fas fa-seedling"></i>
                            <p>Proyek belum tersedia. Seller sedang menambahkan paket baru.</p>
                        </div>
                    </div>
                @endforelse
            </div> 

            <div class="row mt-5">
                <div class="col-12 text-center">
                    <a href="{{ route('projects.index') }}" class="btn-view-all">
                        Lihat Semua Proyek
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                </div>
            </div>

        </div>
    </section> 

    {{-- KABAR KARBON (Buyer) --}}
    <section class="carbon-news-section">
        <div class="container">
            <div class="section-header-flex">
                <div>
                    <span class="section-chip dark"><i class="fas fa-newspaper"></i> Kabar Terkini</span>
                    <h2 class="section-title">Update Kabar Karbon Indonesia</h2>
                    <p class="section-subtitle">Info tren & regulasi karbon terbaru untuk Anda</p>
                </div>
                <a href="{{ route('edukasi') }}" class="btn-section-link">Semua Artikel →</a>
            </div>
            <div class="news-grid">
                <div class="news-card featured-news">
                    <div class="news-image">
                        <img src="{{ asset('images/mangrove0.png') }}" alt="News">
                        <span class="news-tag">Regulasi</span>
                    </div>
                    <div class="news-body">
                        <h3>Komitmen Indonesia: Net Zero 2060 dan Jalur Menuju 2030</h3>
                        <p>Indonesia berkomitmen mengurangi emisi 29-41% pada 2030. Carbon offset menjadi instrumen kunci yang diatur dalam Perpres 98/2021.</p>
                        <a href="{{ route('edukasi') }}" class="news-read-more">Baca Selengkapnya <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="news-side">
                    <div class="news-small">
                        <span class="news-tag-sm">Pasar Karbon</span>
                        <h4>Harga Karbon di IDX Carbon Capai Rp 82.000/ton</h4>
                        <p>Tren harga kredit karbon Indonesia terus menguat seiring meningkatnya permintaan dari sektor industri.</p>
                        <a href="{{ route('edukasi') }}" class="news-link">Baca →</a>
                    </div>
                    <div class="news-small">
                        <span class="news-tag-sm">Tips</span>
                        <h4>5 Cara Memilih Proyek Carbon Offset yang Tepat</h4>
                        <p>Panduan memilih proyek berstandar internasional seperti Verra VCS dan Gold Standard untuk bisnis Anda.</p>
                        <a href="{{ route('edukasi') }}" class="news-link">Baca →</a>
                    </div>
                    <div class="news-small">
                        <span class="news-tag-sm">GHG Protocol</span>
                        <h4>Menghitung Emisi Scope 1, 2, dan 3 dengan Benar</h4>
                        <p>Panduan praktis menggunakan metodologi GHG Protocol sesuai standar pelaporan emisi nasional.</p>
                        <a href="{{ route('edukasi') }}" class="news-link">Baca →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

{{-- ================================================================
     KONDISI B — SELLER SUDAH LOGIN
     ================================================================ --}}
    @elseif(Auth::user()->isSeller())

    {{-- HERO SELLER --}}
    <section class="hero-section hero-seller" id="home">
        <div class="hero-background">
            <img src="{{ asset('images/landing.png') }}" alt="Background">
            <div class="hero-seller-overlay"></div>
        </div>
        <div class="container">
            <div class="hero-user-inner">

                {{-- Kiri --}}
                <div class="hero-user-left animate-fade-in">
                    @if(($pendingCount ?? 0) > 0)
                    <div class="floating-notif warning">
                        <div class="notif-dot warning"></div>
                        <span>
                            <strong>{{ $pendingCount }} transaksi</strong> menunggu konfirmasi pembayaran
                        </span>
                        <a href="{{ route('seller.dashboard') }}">Tinjau →</a>
                    </div>
                    @endif

                    <p class="hero-user-tag seller-tag">
                        <i class="fas fa-store"></i> Seller Dashboard
                    </p>
                    <h1 class="hero-user-title">
                        Halo, 
                        <span class="hero-user-name">{{ $greetingName }}</span>!
                        <span class="hero-seller-sub-title">{{ $activeProjects ?? 0 }} Proyek Aktif di Pasar</span>
                    </h1>
                    <p class="hero-user-sub">
                        Pantau performa proyek karbon Anda dan maksimalkan dampak
                        konservasi bersama buyer dari seluruh Indonesia.
                    </p>
                    <div class="hero-user-cta">
                        <a href="{{ route('seller.dashboard') }}" class="btn-hero-primary seller">
                            <i class="fas fa-tachometer-alt"></i> Lihat Laporan Penjualan
                        </a>
                        <a href="{{ route('seller.dashboard') }}#upload-proyek" class="btn-hero-secondary seller">
                            <i class="fas fa-plus"></i> Update Kuota Karbon
                        </a>
                    </div>
                </div>

                {{-- Kanan: Performance Snapshot --}}
                <div class="hero-user-right animate-fade-in-delay">
                    <div class="seller-snapshot">
                        <div class="impact-snapshot-header">
                            <span class="impact-snapshot-label">
                                <i class="fas fa-chart-line"></i> Ringkasan Performa
                            </span>
                            <a href="{{ route('seller.dashboard') }}" class="impact-snapshot-more">Detail →</a>
                        </div>

                        <div class="seller-snap-numbers">
                            <span class="seller-snap-val">{{ number_format($totalCarbonSold ?? 0, 1) }}</span>
                            <span class="seller-snap-unit">ton CO₂ terjual</span>
                        </div>

                        <div class="seller-snap-grid">
                            <div class="seller-snap-item">
                                <i class="fas fa-seedling"></i>
                                <span class="ssi-val">{{ $activeProjects ?? 0 }}</span>
                                <span class="ssi-lbl">Proyek Aktif</span>
                            </div>
                            <div class="seller-snap-item">
                                <i class="fas fa-boxes"></i>
                                <span class="ssi-val">{{ number_format($totalStock ?? 0, 0) }}</span>
                                <span class="ssi-lbl">Sisa Stok (ton)</span>
                            </div>
                            <div class="seller-snap-item">
                                <i class="fas fa-wallet"></i>
                                <span class="ssi-val">Rp {{ number_format(($totalRevenue ?? 0)/1e6, 1) }}jt</span>
                                <span class="ssi-lbl">Total Pendapatan</span>
                            </div>
                            <div class="seller-snap-item {{ ($pendingCount ?? 0) > 0 ? 'alert' : '' }}">
                                <i class="fas fa-bell"></i>
                                <span class="ssi-val">{{ $pendingCount ?? 0 }}</span>
                                <span class="ssi-lbl">Perlu Dikonfirmasi</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- PROYEK SELLER DI MARKETPLACE --}}
    <section class="projects-section py-5" id="inventaris-seller"> 
        <div class="container">
        
        <div class="row mb-4">
            <div class="col-lg-8">
                <span class="section-chip" style="margin-bottom: 0.55rem; display: inline-flex;"><i class="fas fa-layer-group"></i> Inventaris</span>
                <h2 class="section-title text-start" style="margin-top: 0;">Proyek Anda di Marketplace</h2>
            </div>
        </div>

        <div class="row g-4">
            @forelse($sellerProjects ?? [] as $project)
                <div class="col-md-6 col-lg-4">
                    <div class="project-card">
                        
                        <div class="project-card__image">
                            <img src="{{ $project->image ? asset('images/' . $project->image) : asset('images/placeholder-project.jpg') }}" alt="{{ $project->name }}">
                            @if($project->stock_available < 10)
                                <span class="stock-warning-badge">
                                    <i class="fas fa-exclamation-triangle"></i> Stok Menipis
                                </span>
                            @endif
                            @if($project->stock_available < 10)
                                <span class="stock-warning-badge">
                                    <i class="fas fa-exclamation-triangle"></i> Stok Menipis
                                </span>
                            @endif
                        </div>

                            <!-- <img src="{{ asset('images/' . $project->image) }}" alt="{{ $project->name }}"> -->
                
                        <div class="project-card__body">
                            <div class="project-card__category">{{ $project->category }}</div>
                            
                            <h3 class="project-card__title">{{ $project->name }}</h3>
                            
                            <div class="project-card__company">{{ $project->company_name }}</div>
                            
                            <div class="project-location">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $project->location ?? '-' }}
                            </div>
                            
                            <p class="project-card__description">{{ $project->description }}</p>
                            
                            <div class="project-card__meta">
                                <div class="project-duration-wrapper">
                                    <span class="duration-icon">📅</span>
                                    <span class="project-duration">{{ $project->duration_months }} Bulan</span>
                                </div>
                                <span class="project-card__capacity">Stok: {{ number_format($project->stock_available) }} ton</span>
                            </div>
                            <div class="pcu-stats" style="margin-bottom: 0.875rem;">
                                <div class="project-location">
                                    <i class="fas fa-boxes"></i>
                                    Stok: {{ $project->stock_available ? number_format($project->stock_available) . ' ton' : '-' }}
                                </div>
                            </div>
                            
                            <div class="project-card__meta">
                                <span class="project-card__price">Rp {{ number_format($project->price_per_ton, 0, ',', '.') }} / ton</span>
                            </div>
                            
                            <div class="project-card__footer">
                                <a href="{{ route('seller.dashboard', $project->id) }}" class="btn-action btn-action--view">Kelola Proyek</a>
                            </div>
                        </div> 
                    </div> 
                </div>
                
            @empty
                {{-- Tampilan kosong disesuaikan dengan grid Bootstrap --}}
                <div class="col-12 text-center py-5">
                    <div class="projects-empty-state">
                        <i class="fas fa-seedling" style="font-size: 2.8rem; display: block; margin-bottom: 0.8rem; opacity: 0.4;"></i>
                        <p>Belum ada proyek.</p> 
                        <a href="{{ route('seller.dashboard') }}#upload-proyek" style="color: var(--color-teal); font-weight: 600; text-decoration: none;">
                            <i class="fas fa-plus-circle"></i> Tambah proyek pertama
                        </a>
                    </div>
                </div>
            @endforelse
        </div> {{-- Menutup .row g-4 --}}

        {{-- Tombol Lihat Semua di Bagian Bawah --}}
        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="{{ route('projects.index') }}" class="btn-view-all">
                    Lihat Semua Proyek
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>
        </div>

        </div>
    </section>

    {{-- MARKET INSIGHT (Seller) --}}
    <section class="carbon-news-section">
        <div class="container">
            <div class="section-header-flex">
                <div>
                    <span class="section-chip dark"><i class="fas fa-chart-bar"></i> Market Insight</span>
                    <h2 class="section-title">Tren Pasar Karbon Indonesia</h2>
                    <p class="section-subtitle">Info terbaru untuk membantu Anda optimalkan proyek</p>
                </div>
                <a href="{{ route('edukasi') }}" class="btn-section-link">Semua Artikel →</a>
            </div>
            <div class="news-grid">
                <div class="news-card featured-news">
                    <div class="news-image">
                        <img src="{{ asset('images/mangrove0.png') }}" alt="Market">
                        <span class="news-tag">Harga Karbon</span>
                    </div>
                    <div class="news-body">
                        <h3>Harga Kredit Karbon IDX Carbon Menguat di 2024</h3>
                        <p>Permintaan kredit karbon dari sektor manufaktur dan energi terus meningkat. Ini peluang bagi seller untuk memperluas kapasitas proyek.</p>
                        <a href="{{ route('edukasi') }}" class="news-read-more">Baca Selengkapnya <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                <div class="news-side">
                    <div class="news-small">
                        <span class="news-tag-sm">Tips Seller</span>
                        <h4>Cara Meningkatkan Daya Tarik Proyek bagi Buyer</h4>
                        <p>Lengkapi dokumen metodologi dan tambahkan foto lapangan untuk meningkatkan konversi penjualan hingga 3x.</p>
                        <a href="{{ route('edukasi') }}" class="news-link">Baca →</a>
                    </div>
                    <div class="news-small">
                        <span class="news-tag-sm">Regulasi</span>
                        <h4>Update Perpres 98/2021: Kewajiban Seller di CAMAR</h4>
                        <p>Panduan memenuhi persyaratan dokumen verifikasi sesuai regulasi terbaru pemerintah Indonesia.</p>
                        <a href="{{ route('edukasi') }}" class="news-link">Baca →</a>
                    </div>
                    <div class="news-small">
                        <span class="news-tag-sm">Sertifikasi</span>
                        <h4>Verra VCS vs Gold Standard untuk Proyek Anda</h4>
                        <p>Memilih standar sertifikasi yang tepat dapat meningkatkan nilai jual kredit karbon proyek mangrove.</p>
                        <a href="{{ route('edukasi') }}" class="news-link">Baca →</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @endif {{-- end buyer / seller --}}

{{-- ================================================================
     KONDISI C — GUEST (BELUM LOGIN)
     ================================================================ --}}
@else

    <section class="hero-section" id="home">
        <div class="hero-background">
            <img src="{{ asset('images/landing.png') }}" alt="Landing Background">
        </div>
        <div class="container">
            <div class="row align-items-end min-vh-100">
                <div class="col-lg-7 mx-auto hero-content">
                    <h1 class="hero-title animate-fade-in">
                        Wujudkan Masa Depan Hijau <br>
                        <span class="highlight">Bersama Indonesia</span>
                    </h1>
                    <p class="hero-description animate-fade-in-delay">
                        CAMAR (Carbon Market Indonesia) adalah platform perdagangan karbon yang menghubungkan
                        pelaku industri dengan proyek konservasi untuk menciptakan ekosistem berkelanjutan.
                        Platform ini khusus dirancang untuk memfasilitasi penebusan karbon perusahaan-perusahaan
                        di Indonesia, mendukung komitmen nasional terhadap pengurangan emisi dan ekonomi hijau.
                    </p>
                    <div class="hero-stats animate-fade-in-delay-2">
                        <div class="stat-item">
                            <h3 class="stat-number">10K+</h3>
                            <p class="stat-labels">Ton CO₂ Offset</p>
                        </div>
                        <div class="stat-item">
                            <h3 class="stat-number">150+</h3>
                            <p class="stat-labels">Proyek Hijau</p>
                        </div>
                        <div class="stat-item">
                            <h3 class="stat-number">500+</h3>
                            <p class="stat-labels">Mitra Bergabung</p>
                        </div>
                    </div>
                    <div class="hero-cta animate-fade-in-delay-3">
                        <a href="{{ route('login') }}" class="btn btn-outline-dark btn-lg">Mulai Sekarang</a>
                        <a href="#studi" class="btn btn-outline-dark btn-lg">Kenapa Carbon Offset?</a>
                        <a href="#tentang" class="btn btn-outline-dark btn-lg">Tentang Kami</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="scroll-indicator">
            <div class="mouse"><div class="wheel"></div></div>
            <p>Scroll untuk menjelajah</p>
        </div>
    </section>

    <div class="section-divider-90"></div>

    <section class="projects-section py-5" id="proyek">
        <div class="container">
            <div class="row text-center mb-4">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title">Proyek Terbaru</h2>
                    <p class="section-subtitle">Dukung proyek-proyek konservasi dan offset karbon terbaik di Indonesia</p>
                </div>
            </div>
            <div class="row g-4">
                @foreach($project as $item)
                <div class="col-md-6 col-lg-4">
                    <div class="project-card">

                        <div class="project-card__image">
                            <img src="{{ asset('images/' . ($item->image)) }}" alt="{{ $item->name ?? 'Proyek CAMAR' }}">
                        </div>

                        <div class="project-card__body">
                            <div class="project-card__category">{{ $item->category }}</div>

                            <p class="project-card__company">{{ $item->company_name ?? 'Seller' }}</p>

                            <h3 class="project-card__title" line-clamp-2>{{ $item->name ?? 'Nama Proyek' }}</h3>

                            <div class="project-location">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $item->location ?? '-' }}
                            </div>

                            <p class="project-card__description">{{ Str::limit($item->description, 100) }}</p>

                            <div class="project-card__meta">
                                <div class="project-duration-wrapper">
                                    <span class="duration-icon">📅</span>
                                    <span class="project-duration">{{ $item->duration_months ?? '0' }} Bulan</span>
                                </div>
                                <span class="project-card__capacity">Stok: {{ number_format($item->stock_available) }} ton</span>
                            </div>
                            
                            <div class="project-card__meta">
                                <span class="project-card__price">Rp {{ number_format($item->price_per_ton, 0, ',', '.') }} / ton</span>
                            </div>

                            <div class="project-card__footer">
                                <button class="btn-action btn-action--manage" onclick="window.location.href='{{ route('login') }}'">Detail</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach

                <div class="row mt-5">
                    <div class="col-12 text-center">
                        <a href="{{ route('projects.index') }}" class="btn-view-all">
                            Lihat Semua Proyek
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider-90"></div>

    <section class="why-carbon-section py-5" id="studi">
        <div class="container">
            <div class="row mb-4">
                <div class="col-lg-10 mx-auto text-center">
                    <h2 class="section-title-alt">Mengapa Carbon Offset?</h2>
                    <p class="section-subtitle-alt">Memahami urgensi dan solusi sistematis untuk krisis iklim</p>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-lg-6">
                    <div class="problem-card">
                        <div class="problem-icon-wrapper">
                            <svg class="problem-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10s10-4.48,10-10S17.52,2,12,2z M12,20c-4.41,0-8-3.59-8-8s3.59-8,8-8s8,3.59,8,8S16.41,20,12,20z M12,6c-3.31,0-6,2.69-6,6s2.69,6,6,6s6-2.69,6-6S15.31,6,12,6z"/></svg>
                        </div>
                        <h3 class="problem-title">Krisis Iklim Global</h3>
                        <p class="problem-text">Emisi CO₂ global mencapai 36.8 miliar ton/tahun, menyebabkan peningkatan suhu 1.1°C. Indonesia berkontribusi 615 juta ton CO₂e per tahun.</p>
                        <div class="problem-stat">
                            <span class="stat-big">615 Jt</span>
                            <span class="stat-label">Ton CO₂e/tahun</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="problem-card">
                        <div class="problem-icon-wrapper">
                            <svg class="problem-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12,2l-5.5,9h11L12,2z M12,5.84L13.93,9h-3.87L12,5.84z M17.5,13c-2.49,0-4.5,2.01-4.5,4.5s2.01,4.5,4.5,4.5s4.5-2.01,4.5-4.5S19.99,13,17.5,13z M17.5,20c-1.38,0-2.5-1.12-2.5-2.5s1.12-2.5,2.5-2.5s2.5,1.12,2.5,2.5S18.88,20,17.5,20z M3,21.5h8v-8H3V21.5z M5,15.5h4v4H5V15.5z"/></svg>
                        </div>
                        <h3 class="problem-title">Sektor Industri & Energi</h3>
                        <p class="problem-text">Sektor energi dan industri menyumbang 35% dari total emisi nasional. Pembangkit fosil dan manufaktur menjadi kontributor utama.</p>
                        <div class="problem-stat">
                            <span class="stat-big">35%</span>
                            <span class="stat-label">Kontribusi industri</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="solution-container">
                <div class="solution-header">
                    <div class="solution-badge">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M9,21c0,0.55,0.45,1,1,1h4c0.55,0,1-0.45,1-1v-1H9V21z M12,2C8.14,2,5,5.14,5,9c0,2.38,1.19,4.47,3,5.74V17c0,0.55,0.45,1,1,1h6c0.55,0,1-0.45,1-1v-2.26c1.81-1.27,3-3.36,3-5.74C19,5.14,15.86,2,12,2z"/></svg>
                        Solusi
                    </div>
                    <h3 class="solution-title">Carbon Offset: Jembatan Menuju Net Zero</h3>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="solution-step">
                            <div class="step-number">01</div>
                            <h4 class="step-title">Hitung Emisi</h4>
                            <p class="step-text">Perusahaan menghitung jejak karbon menggunakan metodologi GHG Protocol terstandarisasi.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="solution-step">
                            <div class="step-number">02</div>
                            <h4 class="step-title">Reduksi Internal</h4>
                            <p class="step-text">Implementasi efisiensi energi dan peralihan ke energi terbarukan untuk reduksi langsung.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="solution-step">
                            <div class="step-number">03</div>
                            <h4 class="step-title">Offset Residu</h4>
                            <p class="step-text">Emisi residual dioffset melalui kredit karbon dari proyek verified reforestasi dan energi bersih.</p>
                        </div>
                    </div>
                </div>
                <div class="impact-showcase">
                    <div class="impact-item"><div class="impact-value">2.6M</div><div class="impact-label">Hektar hutan</div></div>
                    <div class="impact-divider"></div>
                    <div class="impact-item"><div class="impact-value">450K</div><div class="impact-label">Ton CO₂</div></div>
                    <div class="impact-divider"></div>
                    <div class="impact-item"><div class="impact-value">150+</div><div class="impact-label">Perusahaan</div></div>
                </div>
            </div>
            <div class="commitment-box">
                <div class="commitment-icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10s10-4.48,10-10S17.52,2,12,2z M10,17l-5-5l1.41-1.41L10,14.17l7.59-7.59L19,8L10,17z"/></svg>
                </div>
                <div class="commitment-content">
                    <h4 class="commitment-title">Komitmen Indonesia: Net Zero 2060</h4>
                    <p class="commitment-text">Indonesia berkomitmen mencapai net zero emission pada 2060 dan mengurangi emisi 29-41% pada 2030. Carbon offset menjadi instrumen kunci mencapai target ini.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider-90"></div>

    <section class="services-section py-5" id="tentang">
        <div class="container">
            <div class="row text-center mb-4">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title">Layanan Kami</h2>
                    <p class="section-subtitle">Solusi lengkap untuk pengelolaan karbon kredit perusahaan Anda</p>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon-wrapper"><svg class="service-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19,3H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h14c1.1,0,2-0.9,2-2V5C21,3.9,20.1,3,19,3z M9,17H7v-7h2V17z M13,17h-2V7h2V17z M17,17h-2v-4h2V17z"/></svg></div>
                        <h3 class="service-title">Trading Platform</h3>
                        <p class="service-text">Platform trading karbon kredit yang aman, transparan, dan terintegrasi</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon-wrapper"><svg class="service-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10s10-4.48,10-10S17.52,2,12,2z M10,17l-5-5l1.41-1.41L10,14.17l7.59-7.59L19,8L10,17z"/></svg></div>
                        <h3 class="service-title">Verifikasi Proyek</h3>
                        <p class="service-text">Sistem verifikasi proyek karbon dengan standar internasional</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon-wrapper"><svg class="service-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M16,6l2.29,2.29l-4.88,4.88l-4-4L2,16.59L3.41,18l6-6l4,4l6.3-6.29L22,12V6H16z"/></svg></div>
                        <h3 class="service-title">Monitoring Real-time</h3>
                        <p class="service-text">Pantau dampak pengurangan emisi karbon secara real-time</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon-wrapper"><svg class="service-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19,3H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h14c1.1,0,2-0.9,2-2V5C21,3.9,20.1,3,19,3z M14,17H7v-2h7V17z M17,13H7v-2h10V13z M17,9H7V7h10V9z"/></svg></div>
                        <h3 class="service-title">Laporan & Analitik</h3>
                        <p class="service-text">Dashboard analitik komprehensif untuk pengambilan keputusan</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endif

@endsection

@push('scripts')
<script src="{{ asset('js/landing.js') }}"></script>
@endpush
